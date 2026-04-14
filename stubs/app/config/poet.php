<?php

declare(strict_types=1);

use Bloom\Constants\PostType;
use Bloom\Constants\Taxonomy;

return [

    /*
    |--------------------------------------------------------------------------
    | Post Types
    |--------------------------------------------------------------------------
    |
    | Here you may specify the post types to be registered by Poet using the
    | Extended CPTs library. <https://github.com/johnbillion/extended-cpts>
    |
    */

    'post' => [
        PostType::INNOVATION => [
            'enter_title_here' => 'Enter innovation title',
            'menu_icon' => 'dashicons-heart',
            'supports' => ['title', 'editor', 'revisions', 'thumbnail'],
            'show_in_rest' => true,
            'has_archive' => false,
            'labels' => [
                'singular' => 'Innovation',
                'plural' => 'Innovations',
            ],
        ],
        PostType::FELLOWS_ALUMNI  => [
            'enter_title_here' => 'Enter Individual\'s Name',
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'editor', 'revisions', 'thumbnail'],
            'show_in_rest' => true,
            'has_archive' => false,
            'labels' => [
                'singular' => 'Individual',
                'plural' => 'Fellows & Alumni',
                'slug' => 'fellows-alumni',
            ],
        ],
        PostType::MENTORS  => [
            'enter_title_here' => 'Enter Mentor\'s Name',
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'editor', 'revisions', 'thumbnail'],
            'show_in_rest' => true,
            'has_archive' => false,
            'labels' => [
                'singular' => 'Mentor',
                'plural' => 'Mentors',
            ],
        ],
        PostType::TEAM  => [
            'enter_title_here' => 'Enter Team Member\'s Name',
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'editor', 'revisions', 'thumbnail'],
            'show_in_rest' => true,
            'has_archive' => false,
            'labels' => [
                'singular' => 'Team Member',
                'plural' => 'Team',
            ],
        ],
        PostType::TABBED_CONTENT => [
            'enter_title_here' => 'Enter Title',
            'menu_icon' => 'dashicons-table-col-after',
            'supports' => ['title', 'editor', 'author', 'revisions'],
            'show_in_rest' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'labels' => [
                'singular' => 'Tabbed Content',
                'plural' => 'Tabbed Content',
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Taxonomies
    |--------------------------------------------------------------------------
    |
    | Here you may specify the taxonomies to be registered by Poet using the
    | Extended CPTs library. <https://github.com/johnbillion/extended-cpts>
    |
    */

    'taxonomy' => [
        Taxonomy::FUNCTION => [
            'links' => [PostType::INNOVATION, PostType::FELLOWS_ALUMNI],
            'meta_box' => 'simple',
        ],
        Taxonomy::CARE_SETTING => [
            'links' => [PostType::INNOVATION, PostType::FELLOWS_ALUMNI],
            'meta_box' => 'simple',
        ],
        Taxonomy::PATHWAY => [
            'links' => [PostType::INNOVATION, PostType::FELLOWS_ALUMNI],
            'meta_box' => 'simple',
        ],
        Taxonomy::ARTICLE_TYPE => [
            'links' => [PostType::POST],
            'meta_box' => 'simple',
        ],
        Taxonomy::EXPERTISE_SUPPORT => [
            'links' => [PostType::MENTORS],
            'meta_box' => 'simple',
        ],
        Taxonomy::MENTOR_EXPERIENCE => [
            'links' => [PostType::MENTORS],
            'meta_box' => 'simple',
        ],

        'post_tag' => false,
        'category' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocks
    |--------------------------------------------------------------------------
    |
    | Here you may specify the block types to be registered by Poet and
    | rendered using Blade.
    |
    | Blocks are registered using the `namespace/label` defined when
    | registering the block with the editor. If no namespace is provided,
    | the current theme text domain will be used instead.
    |
    | Given the block `sage/accordion`, your block view would be located at:
    |   ↪ `views/blocks/accordion.blade.php`
    |
    | Block views have the following variables available:
    |   ↪ $data    – An object containing the block data.
    |   ↪ $content – A string containing the InnerBlocks content.
    |                Returns `null` when empty.
    |
    */

    'block' => [
        // 'sage/accordion',
    ],

    /*
    |--------------------------------------------------------------------------
    | Block Categories
    |--------------------------------------------------------------------------
    |
    | Here you may specify block categories to be registered by Poet for use
    | in the editor.
    |
    */

    'block_category' => [
        'bloom-outer' => [
            'title' => 'Bloom - Outer blocks',
            'icon' => 'align-wide',
        ],
        'bloom-inner' => [
            'title' => 'Bloom - Inner blocks',
            'icon' => 'layout',
        ],
        'bloom-section' => [
            'title' => 'Bloom - Whole Section',
            'icon' => 'align-center',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Block Patterns
    |--------------------------------------------------------------------------
    |
    | Here you may specify block patterns to be registered by Poet for use
    | in the editor.
    |
    | Patterns are registered using the `namespace/label` defined when
    | registering the block with the editor. If no namespace is provided,
    | the current theme text domain will be used instead.
    |
    | Given the pattern `sage/hero`, your pattern content would be located at:
    |   ↪ `views/block-patterns/hero.blade.php`
    |
    | See: https://developer.wordpress.org/reference/functions/register_block_pattern/
    */

    'block_pattern' => [
        // 'sage/hero' => [
        //     'title' => 'Page Hero',
        //     'description' => 'Draw attention to the main focus of the page, and highlight key CTAs',
        //     'categories' => ['all'],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Block Pattern Categories
    |--------------------------------------------------------------------------
    |
    | Here you may specify block pattern categories to be registered by Poet for
    | use in the editor.
    |
    */

    'block_pattern_category' => [
        'all' => [
            'label' => 'All Patterns',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Palette
    |--------------------------------------------------------------------------
    |
    | Here you may specify the color palette registered in the Gutenberg
    | editor.
    |
    | A color palette can be passed as an array or by passing the filename of
    | a JSON file containing the palette.
    |
    | If a color is passed a value directly, the slug will automatically be
    | converted to Title Case and used as the color name.
    |
    | If the palette is explicitly set to `true` – Poet will attempt to
    | register the palette using the default `palette.json` filename generated
    | by <https://github.com/roots/palette-webpack-plugin>
    |
    */

    'palette' => [
        // 'red' => '#ff0000',
        // 'blue' => '#0000ff',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Menu
    |--------------------------------------------------------------------------
    |
    | Here you may specify admin menu item page slugs you would like moved to
    | the Tools menu in an attempt to clean up unwanted core/plugin bloat.
    |
    | Alternatively, you may also explicitly pass `false` to any menu item to
    | remove it entirely.
    |
    */

    'admin_menu' => [
        // 'gutenberg',
    ],

];
