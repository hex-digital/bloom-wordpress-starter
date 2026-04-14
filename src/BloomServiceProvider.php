<?php

declare(strict_types=1);

namespace Bloom;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class BloomServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerBloomConfig();

        // Always register installer/module commands so fresh projects can bootstrap Bloom/config.
        $this->commands([
            \Bloom\Commands\AddModules::class,
            \Bloom\Commands\MakeModule::class,
            \Bloom\Commands\BloomInit::class,
            \Bloom\Commands\InstallCommand::class,
        ]);

        // Allow project-level Bloom/config/commands.php to append extra commands.
        $this->commands(config('bloom.commands', []));
    }

    protected function registerBloomConfig(): void
    {
        if (! function_exists('get_theme_file_path')) {
            return;
        }

        $themeConfigDir = get_theme_file_path('/Bloom/config');
        if (! is_dir($themeConfigDir)) {
            return;
        }

        $configMap = [
            'components' => 'bloom.components',
            'livewire' => 'bloom.livewire',
            'composers' => 'bloom.composers',
            'commands' => 'bloom.commands',
        ];

        foreach ($configMap as $fileName => $configKey) {
            $themeConfigFile = "{$themeConfigDir}/{$fileName}.php";
            if (! is_file($themeConfigFile)) {
                continue;
            }

            $themeConfig = require $themeConfigFile;
            if (! is_array($themeConfig)) {
                continue;
            }

            // Theme config should override package defaults.
            config([$configKey => array_replace_recursive(config($configKey, []), $themeConfig)]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->initBloomViews();
        $this->initBloomBlocks();
        $this->initBloomComponents();
        $this->initBloomComposers();
        $this->initBloomDirectives();
        $this->initBloomLivewireComponents();
        $this->initPublishables();
    }

    protected function initBloomViews(): void
    {
        // Package default views (bloom:: namespace)
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bloom');

        // Theme's Bloom/ directory for project-level overrides
        if (function_exists('get_theme_file_path')) {
            $bloomDir = get_theme_file_path('/Bloom');
            if (is_dir($bloomDir)) {
                View::addLocation($bloomDir);
            }
        }
    }

    protected function initBloomBlocks(): void
    {
        if (! function_exists('get_theme_file_path')) {
            return;
        }

        $bloomBlocksDir = get_theme_file_path('/Bloom/Blocks');

        if (! is_dir($bloomBlocksDir)) {
            return;
        }

        $app = app('AcfComposer');
        $app->registerPath($bloomBlocksDir, 'Bloom\\Blocks\\');
    }

    protected function initBloomComponents(): void
    {
        foreach (config('bloom.components', []) as $alias => $component) {
            Blade::component($component, $alias, 'bloom');
        }
    }

    protected function initBloomLivewireComponents(): void
    {
        foreach (config('bloom.livewire', []) as $alias => $component) {
            Livewire::component($alias, $component);
        }
    }

    protected function initBloomComposers(): void
    {
        foreach (config('bloom.composers', []) as $view => $composer) {
            View::composer($view, $composer);
        }
    }

    protected function initBloomDirectives(): void
    {
        Blade::directive('dump', fn ($expression) => "<?php var_dump({$expression}) ?>");

        Blade::directive('dd', fn ($expression) => "<?php var_dump({$expression}); die(); ?>");
    }

    protected function initPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../stubs/views' => resource_path('views'),
        ], 'bloom-views');

        $this->publishes([
            __DIR__.'/../stubs/css' => resource_path('css'),
        ], 'bloom-css');

        $this->publishes([
            __DIR__.'/../stubs/fonts' => resource_path('fonts'),
        ], 'bloom-fonts');

        $this->publishes([
            __DIR__.'/../stubs/images' => resource_path('images'),
        ], 'bloom-images');

        $this->publishes([
            __DIR__.'/../stubs/bloom' => base_path('Bloom'),
        ], 'bloom-scaffold');

        $this->publishes([
            __DIR__.'/../bloom-config' => base_path('Bloom/config'),
        ], 'bloom-config');

        $this->publishes([
            __DIR__.'/../app-config' => config_path(),
        ], 'bloom-app-config');
    }
}
