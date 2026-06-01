<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Roots\Acorn\View\Composer;

/**
 * Return the Vite manifest as an associative array.
 *
 * @return array<string, array<string, mixed>>
 */
function vite_manifest(): array
{
    $manifestPath = public_path('build/manifest.json');
    if (! is_readable($manifestPath)) {
        return [];
    }

    $manifest = json_decode((string) file_get_contents($manifestPath), true);

    return is_array($manifest) ? $manifest : [];
}

/**
 * Return compiled CSS file contents for a Vite manifest entry's css array.
 *
 * @return array<int, string>
 */
function vite_entry_css_contents(string $entry): array
{
    $manifest = vite_manifest();
    $cssFiles = $manifest[$entry]['css'] ?? [];

    if (! is_array($cssFiles)) {
        return [];
    }

    $contents = [];

    foreach ($cssFiles as $buildFile) {
        if (! is_string($buildFile)) {
            continue;
        }

        $buildPath = public_path('build/'.$buildFile);

        if (! is_readable($buildPath)) {
            continue;
        }

        $contents[] = (string) file_get_contents($buildPath);
    }

    return $contents;
}

/**
 * Return compiled CSS for a manifest entry and its imported chunks (e.g. shared Bloom CSS).
 *
 * @param  array<string, true>  $visited
 * @return array<int, string>
 */
function vite_entry_css_contents_recursive(string $entry, array &$visited = []): array
{
    if (isset($visited[$entry])) {
        return [];
    }

    $visited[$entry] = true;

    $manifest = vite_manifest();
    $entryData = $manifest[$entry] ?? null;

    if (! is_array($entryData)) {
        return [];
    }

    $contents = vite_entry_css_contents($entry);

    foreach ($entryData['imports'] ?? [] as $import) {
        if (! is_string($import)) {
            continue;
        }

        $contents = array_merge(
            $contents,
            vite_entry_css_contents_recursive($import, $visited)
        );
    }

    return $contents;
}

/**
 * Return Bloom CSS entries from Vite manifest in deterministic order.
 *
 * @return array{non_editor: array<int, string>, editor: array<int, string>}
 */
function bloom_manifest_css_entries(): array
{
    $nonEditor = [];
    $editor = [];

    foreach (vite_manifest() as $entry) {
        if (! is_array($entry)) {
            continue;
        }

        $source = $entry['src'] ?? null;
        if (! is_string($source) || ! str_ends_with($source, '.css') || ! str_starts_with($source, 'Bloom/')) {
            continue;
        }

        if (str_contains($source, '/editor-')) {
            $editor[] = $source;

            continue;
        }

        $nonEditor[] = $source;
    }

    sort($nonEditor);
    sort($editor);

    return [
        'non_editor' => $nonEditor,
        'editor' => $editor,
    ];
}

/**
 * Return Bloom CSS files for hot mode from theme source.
 *
 * @return array{non_editor: array<int, string>, editor: array<int, string>}
 */
function bloom_hot_css_entries(): array
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(get_theme_file_path('Bloom'), RecursiveDirectoryIterator::SKIP_DOTS)
    );
    $matches = new RegexIterator($iterator, '/^.+\.css$/i');

    $nonEditor = [];
    $editor = [];

    /** @var \SplFileInfo $file */
    foreach ($matches as $file) {
        $absolutePath = $file->getPathname();
        $relativePath = ltrim(str_replace(get_theme_file_path().'/', '', $absolutePath), '/');

        if (! str_starts_with($relativePath, 'Bloom/')) {
            continue;
        }

        if (str_contains($relativePath, '/editor-')) {
            $editor[] = $relativePath;

            continue;
        }

        $nonEditor[] = $relativePath;
    }

    sort($nonEditor);
    sort($editor);

    return [
        'non_editor' => $nonEditor,
        'editor' => $editor,
    ];
}

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    // Hot mode: import source files by URL.
    if (Vite::isRunningHot()) {
        $settings['styles'][] = [
            'css' => sprintf("@import url('%s');", Vite::asset('resources/css/editor-base.css')),
        ];

        $hotEntries = bloom_hot_css_entries();

        $settings['styles'][] = [
            'css' => sprintf("@import url('%s');", Vite::asset('resources/css/editor.css')),
        ];

        foreach ($hotEntries['non_editor'] as $entry) {
            $settings['styles'][] = [
                'css' => sprintf("@import url('%s');", Vite::asset($entry)),
            ];
        }

        foreach ($hotEntries['editor'] as $entry) {
            $settings['styles'][] = [
                'css' => sprintf("@import url('%s');", Vite::asset($entry)),
            ];
        }

        return $settings;
    }

    // Build mode: inline compiled CSS contents to avoid URL import ordering drift.
    $manifest = vite_manifest();

    $editorBaseFile = $manifest['resources/css/editor-base.css']['file'] ?? null;
    if (is_string($editorBaseFile)) {
        $editorBasePath = public_path('build/'.$editorBaseFile);
        if (is_readable($editorBasePath)) {
            $settings['styles'][] = [
                'css' => (string) file_get_contents($editorBasePath),
            ];
        }
    }

    $editorFile = $manifest['resources/css/editor.css']['file'] ?? null;
    if (is_string($editorFile)) {
        $editorPath = public_path('build/'.$editorFile);
        if (is_readable($editorPath)) {
            $settings['styles'][] = [
                'css' => (string) file_get_contents($editorPath),
            ];
        }
    }

    // Bloom CSS is bundled on editor.js + shared import chunks (not separate Bloom/*.css keys).
    $visited = [];
    foreach (vite_entry_css_contents_recursive('resources/js/editor.js', $visited) as $css) {
        $settings['styles'][] = [
            'css' => $css,
        ];
    }

    // Fallback: legacy per-file Bloom CSS manifest entries, if present.
    $manifestEntries = bloom_manifest_css_entries();

    foreach ($manifestEntries['non_editor'] as $entry) {
        $buildFile = $manifest[$entry]['file'] ?? null;
        if (! is_string($buildFile)) {
            continue;
        }

        $buildPath = public_path('build/'.$buildFile);

        if (! is_readable($buildPath)) {
            continue;
        }

        $settings['styles'][] = [
            'css' => (string) file_get_contents($buildPath),
        ];
    }

    foreach ($manifestEntries['editor'] as $entry) {
        $buildFile = $manifest[$entry]['file'] ?? null;
        if (! is_string($buildFile)) {
            continue;
        }

        $buildPath = public_path('build/'.$buildFile);

        if (! is_readable($buildPath)) {
            continue;
        }

        $settings['styles'][] = [
            'css' => (string) file_get_contents($buildPath),
        ];
    }

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    // In build mode, enqueue editor script deps from manifest.
    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }

    // Always enqueue editor entrypoint (dev + build).
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});
/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'quick_navigation' => __('Quick Navigation', 'sage'),
        'support_navigation' => __('Support Navigation', 'sage'),
        'legal_navigation' => __('Legal Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**<?php
 *
 * namespace App\View\Composers;
 *
 * use Roots\Acorn\View\Composer;
 *
 * class App extends Composer
 * {
 * /**
 * List of views served by this composer.
 *
 * @var array
 * /
 * protected static $views = [
 * '*',
 * ];
 *
 * public function with()
 * {
 * return [
 * 'address' => get_field('address', 'option'),
 * 'email' => get_field('email', 'option'),
 * 'socials' => [
 * 'facebook' => [
 * 'label' => 'Facebook',
 * 'url' => get_field('facebook', 'option'),
 * ],
 * 'x' => [
 * 'label' => 'X',
 * 'url' => get_field('x', 'option'),
 * ],
 * 'linkedin' => [
 * 'label' => 'Linkedin',
 * 'url' => get_field('linkedin', 'option'),
 * ],
 * 'instagram' => [
 * 'label' => 'Instagram',
 * 'url' => get_field('instagram', 'option'),
 * ],
 * 'youtube' => [
 * 'label' => 'YouTube',
 * 'url' => get_field('youtube', 'option'),
 * ],
 * 'tiktok' => [
 * 'label' => 'TikTok',
 * 'url' => get_field('tiktok', 'option'),
 * ],
 * 'bluesky' => [
 * 'label' => 'Bluesky',
 * 'url' => get_field('bluesky', 'option'),
 * ],
 * ],
 * ];
 * }
 *
 * /**
 * Retrieve the site name.
 * /
 * public function siteName(): string
 * {
 * return get_bloginfo('name', 'display');
 * }
 * }
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
            'name' => __('Primary', 'sage'),
            'id' => 'sidebar-primary',
        ] + $config);

    register_sidebar([
            'name' => __('Footer', 'sage'),
            'id' => 'sidebar-footer',
        ] + $config);
});
