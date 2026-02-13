<?php

declare(strict_types=1);

namespace Bloom\Commands;

use Bloom\Constants\PostType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;

class BloomInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bloom:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise Bloom - config, plugins, demo content';

    protected array $initActions = [
        'setup' => 'General Setup',
        'plugins' => 'Install Starter Plugins',
        'commonPages' => 'Create Common Pages',
        'content' => 'Generate Demo Content',
        'nav' => 'Create Demo Nav',
        'footer' => 'Create Demo Footer',
    ];

    protected array $pageTitles = [
        'frontPageId' => 'Front Page',
        'aboutPageId' => 'About Us',
        'contactPageId' => 'Contact Us',
        'cookiePageId' => 'Cookie Policy',
        'policyPageId' => 'Privacy Policy',
    ];

    protected array $createdPages = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $actions = multiselect(
            label: 'Select which actions you would like to complete:',
            options: $this->initActions,
        );

        if (in_array('setup', $actions)) {
            intro('Basic setup');
            $this->handleSetup();
        }

        if (in_array('plugins', $actions)) {
            intro('Installing plugins');
            $this->handlePlugins();
        }

        if (in_array('commonPages', $actions)) {
            intro('Creating common pages');
            $this->handleCommonPages();
        }

        if (in_array('nav', $actions)) {
            intro('Creating main nav');
            $this->handleNav();
        }

        if (in_array('footer', $actions)) {
            intro('Creating footer nav');
            $this->handleFooter();
        }

        if (in_array('content', $actions)) {
            intro('Generating content');
            $this->handleContentGeneration();
        }
    }

    private function handleSetup()
    {
        $this->discourageSearchEngines();
    }

    private function handlePlugins()
    {
        $this->installPlugins();
        $this->installPremiumPlugins();
    }

    private function handleCommonPages()
    {
        $this->setupCommonPages();
        $this->setFrontPage();
    }

    private function handleNav()
    {
        if (empty($this->createdPages)) {
            $this->fetchPageIds();

            if (count($this->createdPages) <= 1) {
                $this->newLine();
                $this->error('Unable to find all pages. Please run the "Create Common Pages" action first.');

                return;
            }
        }

        $this->createPrimaryNavigation();
    }

    private function handleFooter()
    {
        if (empty($this->createdPages)) {
            $this->fetchPageIds();

            if (count($this->createdPages) <= 1) {
                $this->error('Unable to find all pages. Please run the "Create Common Pages" action first.');

                return;
            }
        }

        $this->createFooterNavigation();
        $this->createFooterLegal();
    }

    private function handleContentGeneration(): void
    {
        $postTypes = PostType::getConstants();

        $this->info('Available Post Types:');
        foreach ($postTypes as $key => $value) {
            $this->line("- $value");
        }
        $this->newLine();

        $postType = $this->choice(
            'Select the post type to generate content for:',
            array_values($postTypes)
        );

        $count = $this->ask('How many posts would you like to generate?', '5');

        if (! is_numeric($count) || $count < 1) {
            error('Please enter a valid number greater than 0');

            return;
        }

        $this->newLine();
        $this->info("Generating $count posts for post type '$postType'...");
        $this->newLine();

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $result = Process::command([
                'wp',
                'post',
                'create',
                '--post_type='.$postType,
                '--post_title=Demo '.$postType.' '.($i + 1),
                '--post_status=publish',
                '--post_content="This is a demo post for '.$postType.' '.($i + 1).'."',
            ])->run();

            if ($result->failed()) {
                error('Failed to create post '.($i + 1));
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();
        $this->info("Generated $count posts for '$postType'");
    }

    private function discourageSearchEngines(): void
    {
        $result = Process::command(['wp', 'option', 'update', 'blog_public', '0'])
            ->run();

        if ($result->successful()) {
            info('Discourage search engines: done');
        } else {
            error('Failed to discourage search engines');
        }
    }

    private function setupCommonPages(): void
    {
        foreach ($this->pageTitles as $key => $title) {
            if ($key === 'policyPageId') {
                $this->createdPages[$key] = Process::command([
                    'wp',
                    'post',
                    'list',
                    '--post_type=page',
                    '--title='.$title,
                    '--format=ids',
                ])->run()->output();

                $this->info("Found existing $title");
            } else {
                $this->createdPages[$key] = Process::command([
                    'wp',
                    'post',
                    'create',
                    '--post_type=page',
                    '--post_title='.$title,
                    '--post_status=publish',
                    '--porcelain',
                ])->run()->output();

                $this->info("Created $title");
            }
        }
    }

    private function setFrontPage(): void
    {
        $setFrontPage = Process::command([
            'wp',
            'option',
            'update',
            'page_on_front',
            $this->createdPages['frontPageId'],
        ])->run();

        $setStaticFrontPage = Process::command([
            'wp',
            'option',
            'update',
            'show_on_front',
            'page',
        ])->run();

        if ($setFrontPage->successful() && $setStaticFrontPage->successful()) {
            info('Front Page set');
        } else {
            error('Unable to set Front Page');
        }
    }

    private function createPrimaryNavigation(): void
    {
        $createMenu = Process::command([
            'wp',
            'menu',
            'create',
            'Primary Navigation',
        ])->run();

        if ($createMenu->failed()) {
            error('Unable to create Primary Navigation. Is it already created?');

            return;
        }

        $assignMenuToLocation = Process::command([
            'wp',
            'menu',
            'location',
            'assign',
            'Primary Navigation',
            'primary_navigation',
        ])->run();

        if ($assignMenuToLocation->failed()) {
            error('Unable to assign Primary Navigation');
        }

        $pagesToAdd = [
            $this->createdPages['frontPageId'],
            $this->createdPages['aboutPageId'],
            $this->createdPages['contactPageId'],
        ];

        foreach ($pagesToAdd as $page) {
            $action = Process::command([
                'wp',
                'menu',
                'item',
                'add-post',
                'Primary Navigation',
                $page,
            ])->run();

            if ($action->failed()) {
                error('Unable to add item to Primary Navigation');

                return;
            }
        }

        info('Primary Navigation created');
    }

    private function createFooterNavigation(): void
    {
        $createMenu = Process::command([
            'wp',
            'menu',
            'create',
            'Footer Navigation',
        ])->run();

        if ($createMenu->failed()) {
            error('Unable to create Footer Navigation. Is it already created?');

            return;
        }

        $assignMenuToLocation = Process::command([
            'wp',
            'menu',
            'location',
            'assign',
            'Footer Navigation',
            'footer_navigation',
        ])->run();

        if ($assignMenuToLocation->failed()) {
            error('Unable to assign Footer Navigation');
        }

        $pagesToAdd = [
            $this->createdPages['frontPageId'],
            $this->createdPages['aboutPageId'],
            $this->createdPages['contactPageId'],
        ];

        foreach ($pagesToAdd as $page) {
            $action = Process::command([
                'wp',
                'menu',
                'item',
                'add-post',
                'Footer Navigation',
                $page,
            ])->run();

            if ($action->failed()) {
                error('Unable to add item to Footer Navigation');

                return;
            }
        }

        info('Footer Navigation created');
    }

    private function createFooterLegal(): void
    {
        $createMenu = Process::command([
            'wp',
            'menu',
            'create',
            'Footer Legal',
        ])->run();

        if ($createMenu->failed()) {
            error('Unable to create Footer Legal. Is it already created?');

            return;
        }

        $assignMenuToLocation = Process::command([
            'wp',
            'menu',
            'location',
            'assign',
            'Footer Legal',
            'footer_legal',
        ])->run();

        if ($assignMenuToLocation->failed()) {
            error('Unable to assign Footer Legal');
        }

        $pagesToAdd = [
            $this->createdPages['cookiePageId'],
            $this->createdPages['policyPageId'],
        ];

        foreach ($pagesToAdd as $page) {
            $action = Process::command([
                'wp',
                'menu',
                'item',
                'add-post',
                'Footer Legal',
                $page,
            ])->run();

            if ($action->failed()) {
                error('Unable to add item to Footer Legal');

                return;
            }
        }

        info('Footer Legal created');
    }

    private function installPlugins(): void
    {
        $plugins = [
            'Redirection' => 'redirection',
            'Cookiebot' => 'cookiebot',
            'Yoast' => 'wordpress-seo',
            'Yoast - ACF Content Analysis' => 'acf-content-analysis-for-yoast-seo',
            'ManageWP Worker' => 'worker',
            'Wordfence' => 'wordfence',
            'Accessibility Checker' => 'accessibility-checker',
        ];

        $this->newLine();
        $this->info('Installing required plugins...');
        $this->newLine();

        $bar = $this->output->createProgressBar(count($plugins));
        $bar->start();

        foreach ($plugins as $pluginName => $pluginSlug) {
            $result = Process::command([
                'wp',
                'plugin',
                'install',
                $pluginSlug,
            ])->run();

            if ($result->successful()) {
                info("Installed: $pluginName");
            } else {
                error("Failed to install: $pluginName");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();

        info('Install Required Plugins: done');
    }

    private function installPremiumPlugins(): void
    {
        $pluginDir = trim(Process::command([
            'wp',
            'plugin',
            'path',
        ])->run()->output());

        if (empty($pluginDir)) {
            error('Unable to determine plugin directory path');

            return;
        }

        $tempDir = $pluginDir.'/temp';

        $this->newLine();
        $this->info('Installing premium plugins...');
        $this->newLine();

        $bar = $this->output->createProgressBar(4);
        $bar->start();

        $cloneResult = Process::command([
            'git',
            'clone',
            'git@github.com:hex-digital/starter-wordpress-plugins.git',
            $tempDir,
        ])->run();

        if ($cloneResult->failed()) {
            error('Failed to clone premium plugins repository');

            return;
        }

        $bar->advance();

        $findResult = Process::command([
            'find',
            $tempDir,
            '-name',
            '*.zip',
            '-type',
            'f',
        ])->run();

        if ($findResult->successful() && ! empty($findResult->output())) {
            $zipFiles = explode("\n", trim($findResult->output()));

            foreach ($zipFiles as $zipFile) {
                Process::command([
                    'unzip',
                    '-o',
                    $zipFile,
                    '-d',
                    $pluginDir,
                ])->run();
            }
        }

        $bar->advance();

        Process::command([
            'rm',
            '-rf',
            $tempDir,
        ])->run();

        $bar->advance();
        $bar->finish();
        $this->newLine();
        $this->newLine();

        info('Install Premium Plugins: done');
    }

    private function fetchPageIds(): void
    {
        $this->info('Fetching page IDs...');
        $this->newLine();
        foreach ($this->pageTitles as $key => $title) {
            $result = Process::command([
                'wp',
                'post',
                'list',
                '--post_type=page',
                '--title='.$title,
                '--format=ids',
            ])->run();

            if ($result->successful() && ! empty($result->output())) {
                $this->createdPages[$key] = trim($result->output());
                $this->info("Found $title");
            } else {
                error("Could not find $title");
            }
        }
    }
}
