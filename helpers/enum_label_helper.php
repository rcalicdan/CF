<?php 

if (!function_exists('enum_label')) {
    function enum_label($enum): string
    {
        if (method_exists($enum, 'label')) {
            return $enum->label();
        }
        
        return ucfirst(str_replace(['_', '-'], ' ', $enum->value));
    }
}