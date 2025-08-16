<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class ExtractTranslations extends Command
{
    protected $signature = 'translations:extract 
                           {--locale=en : The locale to generate translations for}
                           {--merge : Merge with existing translations instead of overwriting}
                           {--default-locale=en : The default locale for JSON format}';

    protected $description = 'Extract __() translation calls from views and generate language files';

    public function handle()
    {
        $locale = $this->option('locale');
        $defaultLocale = $this->option('default-locale');
        $merge = $this->option('merge');
        
        $this->info("Extracting translations for locale: {$locale}");
        
        $translationKeys = $this->extractTranslationKeys();
        
        if (empty($translationKeys)) {
            $this->warn('No translation keys found.');
            return 0;
        }
        
        $this->info('Found ' . count($translationKeys) . ' translation keys.');
        
        if ($locale === $defaultLocale) {
            $this->generateJsonFile($translationKeys, $locale, $merge);
            $this->info("Translation file generated: lang/{$locale}.json");
        } else {
            $organizedKeys = $this->organizeTranslationKeys($translationKeys);
            $this->generatePhpFiles($organizedKeys, $locale, $merge);
            $this->info("Translation files generated in: lang/{$locale}/");
        }
        
        return 0;
    }

    private function extractTranslationKeys(): array
    {
        $keys = [];
        $viewsPath = resource_path('views');
        $appPath = app_path();
        
        $paths = [$viewsPath, $appPath];
        
        foreach ($paths as $path) {
            if (!File::isDirectory($path)) {
                continue;
            }
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);
            
            foreach ($phpFiles as $file) {
                $filePath = $file[0];
                $content = File::get($filePath);
                
                $patterns = [
                    '/\b__\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                    '/\b__\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[.*?\]\s*\)/',
                    '/\btrans\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $key) {
                            $keys[] = trim($key);
                        }
                    }
                }
            }
        }
        
        $keys = array_unique($keys);
        sort($keys);
        
        return $keys;
    }

    private function organizeTranslationKeys(array $keys): array
    {
        $organized = [];
        
        foreach ($keys as $key) {
            $fileName = $this->determineFileName($key);
            $nestedKey = $this->getNestedKey($key, $fileName);
            
            $organized[$fileName][] = [
                'full_key' => $key,
                'nested_key' => $nestedKey
            ];
        }
        
        return $organized;
    }

    private function determineFileName(string $key): string
    {
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $firstPart = $parts[0];
            
            if (in_array($firstPart, ['auth', 'validation', 'passwords', 'pagination'])) {
                return $firstPart;
            }
            
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $firstPart) && strlen($firstPart) <= 20) {
                return $firstPart;
            }
        }
        
        return 'messages';
    }

    private function getNestedKey(string $key, string $fileName): string
    {
        if ($fileName === 'messages') {
            return $key;
        }
        
        if (strpos($key, '.') !== false && strpos($key, $fileName . '.') === 0) {
            return substr($key, strlen($fileName) + 1);
        }
        
        return $key;
    }

    private function generateJsonFile(array $keys, string $locale, bool $merge): void
    {
        $langPath = lang_path();
        $filePath = $langPath . "/{$locale}.json";
        
        if (!File::isDirectory($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }
        
        $translations = [];
        
        if ($merge && File::exists($filePath)) {
            $existingContent = File::get($filePath);
            $existingTranslations = json_decode($existingContent, true) ?? [];
            $translations = $existingTranslations;
            $this->info('Merging with existing translations...');
        }
        
        $newKeysCount = 0;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $translations)) {
                $translations[$key] = $key;
                $newKeysCount++;
            }
        }
        
        ksort($translations);
        
        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        File::put($filePath, $json);
        
        if ($merge) {
            $this->info("Added {$newKeysCount} new translation keys.");
            $this->info('Total translations: ' . count($translations));
        } else {
            $this->info('Translation file created with ' . count($translations) . ' keys.');
        }
    }

    private function generatePhpFiles(array $organizedKeys, string $locale, bool $merge): void
    {
        $localePath = lang_path($locale);
        
        if (!File::isDirectory($localePath)) {
            File::makeDirectory($localePath, 0755, true);
        }
        
        $totalNewKeys = 0;
        $totalFiles = 0;
        
        foreach ($organizedKeys as $fileName => $keyData) {
            $filePath = $localePath . "/{$fileName}.php";
            $translations = [];
            
            if ($merge && File::exists($filePath)) {
                $existingTranslations = include $filePath;
                if (is_array($existingTranslations)) {
                    $translations = $existingTranslations;
                }
            }
            
            $newKeysInFile = 0;
            foreach ($keyData as $item) {
                $nestedKey = $item['nested_key'];
                
                if (!$this->hasNestedKey($translations, $nestedKey)) {
                    $this->setNestedKey($translations, $nestedKey, $item['full_key']);
                    $newKeysInFile++;
                }
            }
            
            $phpContent = $this->generatePhpFileContent($translations);
            File::put($filePath, $phpContent);
            
            $totalNewKeys += $newKeysInFile;
            $totalFiles++;
            
            $this->line("  - {$fileName}.php: " . ($merge ? "{$newKeysInFile} new keys" : count($keyData) . " keys"));
        }
        
        if ($merge) {
            $this->info("Added {$totalNewKeys} new translation keys across {$totalFiles} files.");
        } else {
            $this->info("Created {$totalFiles} translation files.");
        }
    }

    private function hasNestedKey(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $current = $array;
        
        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return false;
            }
            $current = $current[$k];
        }
        
        return true;
    }

    private function setNestedKey(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;
        
        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        
        $current = $value;
    }

    private function generatePhpFileContent(array $translations): string
    {
        $export = var_export($translations, true);
        
        $export = preg_replace('/^([ ]*)(.*)/m', '$1$2', $export);
        $export = preg_replace("/=> \n[ ]+array \(/", '=> [', $export);
        $export = preg_replace("/\),\n([ ]*)\)/", "],\n$1]", $export);
        $export = preg_replace("/array \([\n ]*\)/", '[]', $export);
        $export = str_replace('array (', '[', $export);
        $export = str_replace(')', ']', $export);
        
        return "<?php\n\nreturn {$export};\n";
    }
}