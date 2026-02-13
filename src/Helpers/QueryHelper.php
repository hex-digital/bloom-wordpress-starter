<?php

declare(strict_types=1);

namespace Bloom\Helpers;

class QueryHelper
{
    /**
     * @return array
     */
    public static function getPostsGroupedByTaxonomy(string $postType, string $taxonomy, ?string $orderBy = 'date', ?string $order = 'ASC')
    {
        $taxonomiesWithPosts = get_terms($taxonomy);

        $postsByTaxonomies = [];

        foreach ($taxonomiesWithPosts as $tax) {
            $postsByTaxonomies[$tax->slug] = get_posts([
                'post_type' => $postType,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'order' => $order,
                'orderBy' => $orderBy,
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'slug',
                        'terms' => $tax->slug,
                    ],
                ],
            ]);
        }

        return $postsByTaxonomies;
    }

    /**
     * @return array
     */
    public static function getRecentPosts(string $postType = 'post', int $postCount = 3)
    {
        if (! $postType) {
            return false;
        }

        $recentPosts = get_posts([
            'post_type' => $postType,
            'post_status' => 'publish',
            'posts_per_page' => $postCount,
        ]);

        return $recentPosts;
    }

    /**
     * @return array
     */
    public static function getPostByID(?int $postID = null)
    {
        if (! $postID) {
            return false;
        }

        $recentPosts = get_posts([
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'p' => $postID,
        ]);

        return $recentPosts;
    }
}
