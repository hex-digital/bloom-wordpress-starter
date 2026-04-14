<?php

declare(strict_types=1);

namespace Bloom\Helpers;

use function Bloom\Helpers\get_post_type;
use function Bloom\Helpers\get_post_type_object;
use function Bloom\Helpers\get_the_ID;

class PostHelper
{
    public static function getPostName($id): bool|string
    {
        $page_id = get_the_ID();
        $post_type = get_post_type($page_id);

        if ($post_type) {
            $post_type_obj = get_post_type_object($post_type);

            if ($post_type_obj) {
                $singular_label = $post_type_obj->labels->singular_name;

                return $singular_label;
            }
        }

        return false;
    }
}
