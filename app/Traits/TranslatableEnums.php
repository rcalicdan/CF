<?php

namespace App\Traits;

use App\ActionService\EnumTranslationService;

trait TranslatableEnums
{
    /**
     * Get the translation key for this enum case
     */
    public function getTranslationKey(): string
    {
        $className = class_basename(static::class);
        $enumKey = strtolower($className);

        return "enums.{$enumKey}.{$this->value}";
    }

    /**
     * Get the translated label for this enum case
     */
    public function label(): string
    {
        $translationKey = $this->getTranslationKey();

        $translated = __($translationKey);

        if ($translated === $translationKey) {
            return $this->getFormattedValue();
        }

        return $translated;
    }

    /**
     * Get a formatted version of the enum value (fallback)
     */
    public function getFormattedValue(): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $this->value));
    }

    public function getLabel(): string
    {
        return EnumTranslationService::translate($this);
    }

    public static function getLabels(): array
    {
        $labels = [];
        foreach (static::cases() as $case) {
            $labels[$case->value] = $case->getLabel();
        }
        return $labels;
    }

    /**
     * Get all enum cases with their labels
     */
    public static function options(): array
    {
        $options = [];
        foreach (static::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Get enum case by value with translation
     */
    public static function fromValue(string $value): ?static
    {
        foreach (static::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        return null;
    }
}
