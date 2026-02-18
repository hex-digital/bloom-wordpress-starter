<?php

declare(strict_types=1);

namespace Bloom\Helpers;

use WP_Term;

use function get_the_terms;

class TermHelper
{
    /**
     * @return WP_Term|false
     */
    public static function getFirstTerm($id, $taxonomy): WP_Term|bool
    {
        $terms = get_the_terms($id, $taxonomy);
        if ($terms && is_array($terms) && count($terms)) {
            return $terms[0];
        }

        return false;
    }

    /**
     * Returns true if $terms only contains terms from $hasTerms
     */
    public static function hasOnlyTerms(array $terms, array $hasTerms): bool
    {
        $hasTerms = collect($hasTerms)->unique();
        $terms =
            collect($terms)
                ->map(function ($term) {
                    if (is_a($term, WP_Term::class)) {
                        return $term->slug;
                    }

                    return $term;
                })
                ->unique();

        return count($terms->diff($hasTerms)) === 0;
    }

    /**
     * Returns the all term slugs of a post
     */
    public static function getAllTermSlugsOfPost(int $id, string $taxonomy): array|bool
    {
        $terms = get_the_terms($id, $taxonomy);
        if ($terms && is_array($terms) && count($terms)) {
            $termSlugs =
                collect($terms)
                    ->map(fn ($term) => $term->slug);

            return $termSlugs->toArray();
        }

        return [];
    }
}
