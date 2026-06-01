<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class App extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*',
    ];

    public function with()
    {
        return [
            'address' => get_field('address', 'option'),
            'email' => get_field('email', 'option'),
            'socials' => [
                'facebook' => [
                    'label' => 'Facebook',
                    'url' => get_field('facebook', 'option'),
                ],
                'x' => [
                    'label' => 'X',
                    'url' => get_field('x', 'option'),
                ],
                'linkedin' => [
                    'label' => 'Linkedin',
                    'url' => get_field('linkedin', 'option'),
                ],
                'instagram' => [
                    'label' => 'Instagram',
                    'url' => get_field('instagram', 'option'),
                ],
                'youtube' => [
                    'label' => 'YouTube',
                    'url' => get_field('youtube', 'option'),
                ],
                'tiktok' => [
                    'label' => 'TikTok',
                    'url' => get_field('tiktok', 'option'),
                ],
                'bluesky' => [
                    'label' => 'Bluesky',
                    'url' => get_field('bluesky', 'option'),
                ],
            ],
        ];
    }

    /**
     * Retrieve the site name.
     */
    public function siteName(): string
    {
        return get_bloginfo('name', 'display');
    }
}
