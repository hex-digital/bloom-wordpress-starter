<?php

declare(strict_types=1);

namespace Bloom\Helpers;

use WP_Post;

class GutenbergHelper
{
    /**
     * Get Gutenberg block content
     *
     * @return string
     */
    public static function parseBlockContent($id)
    {
        $post = get_post($id);
        $content = '';

        if (has_blocks($post->post_content)) {
            $blocks = parse_blocks($post->post_content);
            foreach ($blocks as $block) {
                $content .= render_block($block);
            }

            return $content;
        } else {
            return null;
        }
    }

    public static function getExcerpt(WP_Post $post, $maxCharacters = 20): ?string
    {
        $excerpt = '';

        $blocks = parse_blocks($post->post_content);

        if (get_the_excerpt($post->ID)) {
            $excerpt = get_the_excerpt($post->ID);
        } else {
            foreach ($blocks as $block) {
                if ($block['blockName'] === 'core/paragraph') {
                    $excerpt = trim(strip_tags($block['innerHTML']));

                    if (mb_strlen($excerpt)) {
                        break;
                    }
                }
            }
        }

        return wp_trim_words($excerpt, $maxCharacters, '...');
    }
}
