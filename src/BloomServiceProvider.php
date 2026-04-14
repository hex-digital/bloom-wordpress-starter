<?php

declare(strict_types=1);

namespace HexDigital\Bloom;

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
            \HexDigital\Bloom\Commands\AddModules::class,
            \HexDigital\Bloom\Commands\MakeModule::class,
            \HexDigital\Bloom\Commands\BloomInit::class,
            \HexDigital\Bloom\Commands\InstallCommand::class,
        ]);

        // Allow project-level Bloom/config/commands.php to register extra commands.
        $this->commands($this->normalizeCommandClasses(config('bloom.commands', [])));
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
     * Backward compatibility for older theme configs that still reference Bloom\Commands\*.
     *
     * @param  array<int, string>  $commands
     * @return array<int, string>
     */
    protected function normalizeCommandClasses(array $commands): array
    {
        $normalized = [];

        foreach ($commands as $command) {
            if (! is_string($command) || $command === '') {
                continue;
            }

            $mappedCommand = str_starts_with($command, 'Bloom\\Commands\\')
                ? str_replace('Bloom\\Commands\\', 'HexDigital\\Bloom\\Commands\\', $command)
                : $command;

            if (class_exists($mappedCommand)) {
                $normalized[] = $mappedCommand;
            }
        }

        return $normalized;
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
    }

    protected function initBloomViews(): void
    {
        if (! function_exists('get_theme_file_path')) {
            return;
        }

        // Register the theme-level Bloom views directory for composer/view resolution.
        $bloomViewsDir = get_theme_file_path('/Bloom/views');
        if (is_dir($bloomViewsDir)) {
            View::addLocation($bloomViewsDir);
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

}
