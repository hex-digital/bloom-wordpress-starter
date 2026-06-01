<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Allow JSON uploads (e.g. Lottie files) in media library.
 *
 * @param  array<string, string>  $mimes
 * @return array<string, string>
 */
add_filter('upload_mimes', function (array $mimes): array {
    $mimes['json'] = 'application/json';

    return $mimes;
});

/**
 * Ensure JSON filetype is correctly detected during upload validation.
 *
 * @param  array<string, mixed>  $data
 * @param  string  $file
 * @param  string  $filename
 * @param  array<string, string>|null  $mimes
 * @return array<string, mixed>
 */
add_filter('wp_check_filetype_and_ext', function (array $data, string $file, string $filename, ?array $mimes = null): array {
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'json') {
        return $data;
    }

    return [
        'ext' => 'json',
        'type' => 'application/json',
        'proper_filename' => $filename,
    ];
}, 10, 4);
