<?php

namespace App\ActionService;

class EnumTranslationService
{
    private static array $cache = [];

    public static function translate($enum): string
    {
        if (!is_object($enum) || !enum_exists($enum::class)) {
            return (string) $enum;
        }

        $cacheKey = $enum::class . '::' . $enum->value;

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $className = strtolower(class_basename($enum::class));
        $translationKey = "enums.{$className}.{$enum->value}";

        $translated = __($translationKey);
        if ($translated === $translationKey) {
            $translated = ucfirst(str_replace(['_', '-'], ' ', $enum->value));
        }

        self::$cache[$cacheKey] = $translated;
        return $translated;
    }

    public static function options(string $enumClass): array
    {
        if (!enum_exists($enumClass)) {
            return [];
        }

        $options = [];
        foreach ($enumClass::cases() as $case) {
            $options[$case->value] = self::translate($case);
        }

        return $options;
    }
}
