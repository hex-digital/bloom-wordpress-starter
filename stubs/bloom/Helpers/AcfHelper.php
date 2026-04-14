<?php

namespace Bloom\Helpers;

use function Bloom\Helpers\add_query_arg;
use function Bloom\Helpers\get_field;

class AcfHelper
{
    public static function anyFieldData(array $fieldNames, $fieldPost = null)
    {
        foreach ($fieldNames as $fieldName) {
            if ($fieldPost) {
                if (get_field($fieldName, $fieldPost)) {
                    return true;
                }
            } else {
                if (get_field($fieldName)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function allFieldData(array $fieldNames)
    {
        foreach ($fieldNames as $fieldName) {
            if (! get_field($fieldName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * From an acf oEmbed object and a set of options, add those options to the embed URL.
     */
    public static function acfOembedWithOptions(?string $oembed = '', array $args = []): string
    {
        if (! $oembed) {
            return '';
        }

        // Use preg_match to find iframe src.
        preg_match('/src="(.+?)"/', $oembed, $matches);

        if (! is_array($matches) || count($matches) === 0) {
            return $oembed;
        }

        $src = $matches[1];

        // Add extra parameters to src and replace HTML.
        $new_src = add_query_arg($args, $src);
        $iframe = str_replace($src, $new_src, $oembed);

        // Add extra attributes to iframe HTML.
        $attributes = 'frameborder="0"';
        $iframe = str_replace('></iframe>', ' '.$attributes.'></iframe>', $iframe);

        // Display customized HTML.
        return $iframe;
    }

    /**
     * Add background options to an ACF oEmbed, plus others.
     */
    public static function acfOembedBackgroundWithOptions(?string $oembed = '', array $args = []): string
    {
        $merged_args = array_merge([
            'controls' => 0,
            'autohide' => 1,
            'autoplay' => 1,
            'background' => 1,
            'hd' => 1,
            'muted' => 1,
        ], $args);

        return self::acfOembedWithOptions($oembed, $merged_args);
    }

    /**
     * Check that data from repeater is valid for use
     *
     * Use strict search if repeater has a minimum requirement of 1 and all fields are required
     */
    public static function validateRepeater($variable, $strict = false): false|array
    {
        if (! is_array($variable)) {
            return false; // Not an array
        }

        if (count($variable) > 0) {
            if ($strict) {
                foreach ($variable[0] as $key => $value) {
                    if ($value === '') {
                        return false; // Found an empty string
                    }
                }
            } else {
                return $variable;
            }
        } else {
            return false;
        }

        return $variable;
    }
}
