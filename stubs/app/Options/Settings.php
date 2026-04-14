<?php

declare(strict_types=1);

namespace Bloom\app\Options;

use Log1x\AcfComposer\Options as Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Settings extends Field
{
    /**
     * The option page menu name.
     *
     * @var string
     */
    public $name = 'Site Options';

    /**
     * The option page document title.
     *
     * @var string
     */
    public $title = 'Site Options';

    /**
     * The option page field group.
     *
     * @return array
     */
    public function fields()
    {
        $siteOptions = new FieldsBuilder('site_options');

        $siteOptions
            ->addTab('Header', [
                'placement' => 'left',
            ])

            ->addTab('Social Media', [
                'placement' => 'left',
            ])
            ->addUrl('Facebook', [
            ])
            ->addUrl('Twitter', [
            ])
            ->addUrl('LinkedIn', [
            ])
            ->addUrl('Instagram', [
            ])
            ->addUrl('Youtube', [
            ])
            ->addUrl('TikTok', [
            ])

            ->addTab('Footer', [
                'placement' => 'left',
            ])
            ->addText('copyright_start', [
                'label' => 'Copyright start year',
                'placeholder' => 'E.G. 2024',
            ])
            ->addText('copyright', [
                'label' => 'Copyright text',
            ]);

        return $siteOptions->build();
    }
}
