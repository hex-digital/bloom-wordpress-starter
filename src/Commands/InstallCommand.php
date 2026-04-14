<?php

declare(strict_types=1);

namespace HexDigital\Bloom\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected $signature = 'bloom:install
                            {--force : Overwrite existing scaffold files}
                            {--diff : Show what changed in stubs since last install}';

    protected $description = 'Scaffold Bloom files into your Sage theme';

    protected Filesystem $files;

    protected string $stubsPath;

    protected ?string $themeName = null;

    protected array $stubRootDestinations = [
        'bloom' => 'Bloom',
        'app' => 'app',
        'resources' => 'resources',
        'root' => '',
    ];

    public function handle(): int
    {
        $this->files = new Filesystem;
        $this->stubsPath = $this->getStubsPath();

        if ($this->option('diff')) {
            return $this->handleDiff();
        }

        $this->components->info('Bloom Installer');
        $this->newLine();

        $this->copyStubRoots();
        $this->patchComposerAutoload();
        $this->patchAppCss();
        $this->patchViteConfig();

        $this->newLine();
        $this->components->info('Done! Run: composer dump-autoload && npm install && npm run build');

        return self::SUCCESS;
    }

    protected function getStubsPath(): string
    {
        // Find the package stubs directory
        $paths = [
            base_path('vendor/hex-digital/bloom/stubs'),
            base_path('packages/bloom/stubs'),
            dirname(__DIR__, 2).'/stubs',
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return dirname(__DIR__, 2).'/stubs';
    }

    protected function copyStubRoots(): void
    {
        foreach ($this->stubRootDestinations as $stubRoot => $destinationRoot) {
            $sourcePath = "{$this->stubsPath}/{$stubRoot}";
            if (! $this->files->isDirectory($sourcePath)) {
                continue;
            }

            $destinationPath = $destinationRoot === '' ? base_path() : base_path($destinationRoot);
            $this->copyDirectory($sourcePath, $destinationPath);

            $label = $destinationRoot === '' ? './' : "{$destinationRoot}/";
            $this->components->twoColumnDetail("Copied stubs/{$stubRoot} → {$label}", '<fg=green;options=bold>DONE</>');
        }
    }

    protected function patchAppCss(): void
    {
        $appCssPath = base_path('resources/css/app.css');

        if (! $this->files->exists($appCssPath)) {
            $this->printManualInstructions('app.css');

            return;
        }

        $content = $this->files->get($appCssPath);
        $requiredLines = [
            '@import "./bloom-base.css";',
            '@source "../../Bloom/";',
        ];

        $missingLines = array_values(array_filter(
            $requiredLines,
            fn (string $line): bool => ! str_contains($content, $line)
        ));

        if ($missingLines === []) {
            $this->components->twoColumnDetail('resources/css/app.css', '<fg=yellow;options=bold>ALREADY PATCHED</>');

            return;
        }

        $insertionBlock = implode("\n", $missingLines);

        if (str_contains($content, '@import "tailwindcss";')) {
            $content = str_replace(
                '@import "tailwindcss";',
                "@import \"tailwindcss\";\n{$insertionBlock}",
                $content
            );
        } elseif (str_contains($content, '@tailwind base;')) {
            $content = str_replace(
                '@tailwind base;',
                "@tailwind base;\n{$insertionBlock}",
                $content
            );
        } else {
            $content = $insertionBlock."\n\n".ltrim($content);
        }

        $this->files->put($appCssPath, $content);
        $this->components->twoColumnDetail('Patched resources/css/app.css', '<fg=green;options=bold>DONE</>');
    }

    protected function patchViteConfig(): void
    {
        $vitePath = base_path('vite.config.js');

        if (! $this->files->exists($vitePath)) {
            $vitePath = base_path('vite.config.ts');
        }

        if (! $this->files->exists($vitePath)) {
            $this->printManualViteInstructions();

            return;
        }

        $content = $this->files->get($vitePath);
        $originalContent = $content;

        $content = $this->patchViteAlias($content);
        $content = $this->patchViteInputContent($content);
        $content = $this->patchViteBasePath($content);

        if ($content === $originalContent) {
            $this->components->twoColumnDetail(basename($vitePath), '<fg=yellow;options=bold>ALREADY PATCHED</>');

            return;
        }

        $this->files->put($vitePath, $content);
        $this->components->twoColumnDetail('Patched '.basename($vitePath).' (Bloom alias, input, and base)', '<fg=green;options=bold>DONE</>');
    }

    protected function patchComposerAutoload(): void
    {
        $composerJsonPath = base_path('composer.json');

        if (! $this->files->exists($composerJsonPath)) {
            $this->components->warn('Could not find composer.json. Please add `Bloom\\\\` => `Bloom/` manually.');

            return;
        }

        $decoded = json_decode($this->files->get($composerJsonPath), true);

        if (! is_array($decoded)) {
            $this->components->warn('Could not parse composer.json. Please add `Bloom\\\\` => `Bloom/` manually.');

            return;
        }

        $decoded['autoload'] ??= [];
        $decoded['autoload']['psr-4'] ??= [];

        if (
            isset($decoded['autoload']['psr-4']['Bloom\\'])
            && $decoded['autoload']['psr-4']['Bloom\\'] === 'Bloom/'
        ) {
            $this->components->twoColumnDetail('composer.json autoload Bloom\\', '<fg=yellow;options=bold>ALREADY PATCHED</>');

            return;
        }

        $decoded['autoload']['psr-4']['Bloom\\'] = 'Bloom/';
        ksort($decoded['autoload']['psr-4']);

        $encoded = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (! is_string($encoded)) {
            $this->components->warn('Could not encode composer.json. Please add `Bloom\\\\` => `Bloom/` manually.');

            return;
        }

        $this->files->put($composerJsonPath, $encoded."\n");
        $this->components->twoColumnDetail('Patched composer.json (Bloom\\ autoload)', '<fg=green;options=bold>DONE</>');
    }

    protected function patchViteAlias(string $content): string
    {
        if (str_contains($content, "'@bloom': '/Bloom'") || str_contains($content, '"@bloom": "/Bloom"')) {
            return $content;
        }

        // Insert within existing resolve.alias object when available.
        if (preg_match('/(alias\s*:\s*\{)(.*?)(\n\s*\})/s', $content)) {
            return (string) preg_replace(
                '/(alias\s*:\s*\{)(.*?)(\n\s*\})/s',
                "$1$2\n      '@bloom': '/Bloom',$3",
                $content,
                1
            );
        }

        return $content;
    }

    protected function patchViteInputContent(string $content): string
    {
        if (! preg_match('/input\s*:\s*\[(.*?)\]/s', $content, $matches)) {
            return $content;
        }

        $inputBlock = $matches[1];
        $entriesToAdd = [];

        if (! str_contains($inputBlock, 'resources/css/editor.css')) {
            $entriesToAdd[] = "        'resources/css/editor.css',";
        }

        if (! str_contains($inputBlock, 'resources/css/admin.css')) {
            $entriesToAdd[] = "        'resources/css/admin.css',";
        }

        if ($entriesToAdd === []) {
            return $content;
        }

        $insertion = "\n".implode("\n", $entriesToAdd);

        return (string) preg_replace(
            '/(input\s*:\s*\[)(.*?)(\n\s*\])/s',
            "$1$2{$insertion}$3",
            $content,
            1
        );
    }

    protected function patchViteBasePath(string $content): string
    {
        $themeName = $this->resolveThemeName();
        $basePath = "/wp-content/themes/{$themeName}/public/build/";

        if (preg_match('/base\s*:\s*[\'"][^\'"]*[\'"]\s*,/', $content)) {
            return (string) preg_replace(
                '/base\s*:\s*[\'"][^\'"]*[\'"]\s*,/',
                "base: '{$basePath}',",
                $content,
                1
            );
        }

        if (preg_match('/defineConfig\s*\(\s*\{/', $content)) {
            return (string) preg_replace(
                '/defineConfig\s*\(\s*\{/',
                "defineConfig({\n  base: '{$basePath}',",
                $content,
                1
            );
        }

        return $content;
    }

    protected function resolveThemeName(): string
    {
        if ($this->themeName !== null) {
            return $this->themeName;
        }

        $currentPath = str_replace('\\', '/', base_path());

        if (preg_match('#/(?:wp-content|app)/themes/([^/]+)$#', $currentPath, $matches)) {
            $this->themeName = $matches[1];

            return $this->themeName;
        }

        $this->themeName = basename(base_path());

        return $this->themeName;
    }

    protected function copyDirectory(string $src, string $dest): void
    {
        $files = $this->files->allFiles($src);

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $destPath = $dest.'/'.$relativePath;

            // Skip .gitkeep files
            if ($file->getFilename() === '.gitkeep') {
                continue;
            }

            $destDir = dirname($destPath);
            if (! $this->files->isDirectory($destDir)) {
                $this->files->makeDirectory($destDir, 0755, true);
            }

            if (! $this->files->exists($destPath) || $this->option('force')) {
                $this->files->copy($file->getPathname(), $destPath);
            }
        }
    }

    protected function handleDiff(): int
    {
        $this->components->info('Bloom Stub Diff');
        $this->newLine();

        $mappings = [
            'resources/css/bloom-tokens.css' => 'resources/css/bloom-tokens.css',
            'resources/css/bloom-base.css' => 'resources/css/bloom-base.css',
            'resources/css/editor.css' => 'resources/css/editor.css',
            'resources/css/admin.css' => 'resources/css/admin.css',
        ];

        $hasChanges = false;

        foreach ($mappings as $stub => $dest) {
            $stubPath = $this->stubsPath.'/'.$stub;
            $destPath = base_path($dest);

            if (! $this->files->exists($stubPath)) {
                continue;
            }

            if (! $this->files->exists($destPath)) {
                $this->components->twoColumnDetail($dest, '<fg=yellow;options=bold>NEW (not yet installed)</>');
                $hasChanges = true;

                continue;
            }

            $stubContent = $this->files->get($stubPath);
            $destContent = $this->files->get($destPath);

            if ($stubContent !== $destContent) {
                $this->components->twoColumnDetail($dest, '<fg=yellow;options=bold>CHANGED</>');
                $hasChanges = true;
            } else {
                $this->components->twoColumnDetail($dest, '<fg=green;options=bold>UP TO DATE</>');
            }
        }

        if (! $hasChanges) {
            $this->newLine();
            $this->components->info('All scaffold files are up to date.');
        } else {
            $this->newLine();
            $this->components->warn('Run bloom:install --force to overwrite changed files.');
        }

        return self::SUCCESS;
    }

    protected function printManualCssInstructions(): void
    {
        $this->components->warn('Could not auto-patch resources/css/app.css (file appears customized).');
        $this->newLine();
        $this->line('  Please add the following to your app.css:');
        $this->newLine();
        $this->line('    <fg=cyan>@import "./bloom-tokens.css";</>');
        $this->line('    <fg=cyan>@import "./bloom-base.css";</>');
        $this->line('    <fg=cyan>@source "../../Bloom/";</>');
        $this->newLine();
    }

    protected function printManualViteInstructions(): void
    {
        $this->components->warn('Could not auto-patch vite.config.js.');
        $this->newLine();
        $this->line('  Please add to resolve.alias:');
        $this->newLine();
        $this->line("    <fg=cyan>'@bloom': '/Bloom',</>");
        $this->newLine();
        $this->line('  And add to input array:');
        $this->newLine();
        $this->line("    <fg=cyan>'resources/css/editor.css',</>");
        $this->line("    <fg=cyan>'resources/css/admin.css',</>");
        $this->newLine();
        $this->line('  And set the base path for non-Bedrock Sage themes:');
        $this->newLine();
        $this->line("    <fg=cyan>base: '/wp-content/themes/{theme-name}/public/build/',</>");
        $this->newLine();
    }

    protected function printManualInstructions(string $file): void
    {
        $this->components->warn("Could not find {$file}. Please create it manually.");
    }
}
