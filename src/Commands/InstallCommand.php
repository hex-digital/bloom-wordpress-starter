<?php

declare(strict_types=1);

namespace Bloom\Commands;

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

    public function handle(): int
    {
        $this->files = new Filesystem;
        $this->stubsPath = $this->getStubsPath();

        if ($this->option('diff')) {
            return $this->handleDiff();
        }

        $this->components->info('Bloom Installer');
        $this->newLine();

        $this->scaffoldBloomDirectory();
        $this->copyCSS();
        $this->copyViews();
        $this->copyFonts();
        $this->copyImages();
        $this->copyData();
        $this->copyHelpers();
        $this->copyBloomConfig();
        $this->patchAppCss();
        $this->patchViteConfig();

        $this->newLine();
        $this->components->info('Done! Run: npm install && npm run build');

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

    protected function scaffoldBloomDirectory(): void
    {
        $bloomDir = base_path('Bloom');

        $dirs = [
            'Blocks',
            'Components',
            'Composers',
            'Livewire',
        ];

        foreach ($dirs as $dir) {
            $path = $bloomDir.'/'.$dir;
            if (! $this->files->isDirectory($path)) {
                $this->files->makeDirectory($path, 0755, true);
                $this->files->put($path.'/.gitkeep', '');
            }
        }

        $this->components->twoColumnDetail('Created Bloom/ directory', '<fg=green;options=bold>DONE</>');
    }

    protected function copyCSS(): void
    {
        $cssFiles = [
            'css/bloom-tokens.css' => 'resources/css/bloom-tokens.css',
            'css/bloom-base.css' => 'resources/css/bloom-base.css',
            'css/editor.css' => 'resources/css/editor.css',
            'css/admin.css' => 'resources/css/admin.css',
        ];

        // Ensure resources/css directory exists
        $cssDir = base_path('resources/css');
        if (! $this->files->isDirectory($cssDir)) {
            $this->files->makeDirectory($cssDir, 0755, true);
        }

        foreach ($cssFiles as $stub => $dest) {
            $this->copyStubFile($stub, $dest);
            $this->components->twoColumnDetail(
                'Copied '.basename($dest).' → '.$dest,
                '<fg=green;options=bold>DONE</>'
            );
        }
    }

    protected function copyViews(): void
    {
        $viewsStubDir = $this->stubsPath.'/views';
        $viewsDest = base_path('resources/views');

        if (! $this->files->isDirectory($viewsStubDir)) {
            return;
        }

        $this->copyDirectory($viewsStubDir, $viewsDest);

        $this->components->twoColumnDetail('Copied starter views → resources/views/', '<fg=green;options=bold>DONE</>');
    }

    protected function copyFonts(): void
    {
        $fontsStubDir = $this->stubsPath.'/fonts';
        $fontsDest = base_path('resources/fonts');

        if (! $this->files->isDirectory($fontsStubDir)) {
            return;
        }

        if (! $this->files->isDirectory($fontsDest)) {
            $this->files->makeDirectory($fontsDest, 0755, true);
        }

        $this->copyDirectory($fontsStubDir, $fontsDest);

        $this->components->twoColumnDetail('Copied fonts → resources/fonts/', '<fg=green;options=bold>DONE</>');
    }

    protected function copyImages(): void
    {
        $imagesStubDir = $this->stubsPath.'/images';
        $imagesDest = base_path('resources/images');

        if (! $this->files->isDirectory($imagesStubDir)) {
            return;
        }

        if (! $this->files->isDirectory($imagesDest)) {
            $this->files->makeDirectory($imagesDest, 0755, true);
        }

        $this->copyDirectory($imagesStubDir, $imagesDest);

        $this->components->twoColumnDetail('Copied images → resources/images/', '<fg=green;options=bold>DONE</>');
    }

    protected function copyData(): void
    {
        $dataStubDir = $this->stubsPath.'/Data';
        $dataDest = base_path('Bloom/Data');

        if (! $this->files->isDirectory($dataStubDir)) {
            return;
        }

        if (! $this->files->isDirectory($dataDest)) {
            $this->files->makeDirectory($dataDest, 0755, true);
        }

        $this->copyDirectory($dataStubDir, $dataDest);

        $this->components->twoColumnDetail('Copied Data → Bloom/Data/', '<fg=green;options=bold>DONE</>');
    }

    protected function copyHelpers(): void
    {
        $helpersStubDir = $this->stubsPath.'/Helpers';
        $helpersDest = base_path('Bloom/Helpers');

        if (! $this->files->isDirectory($helpersStubDir)) {
            return;
        }

        if (! $this->files->isDirectory($helpersDest)) {
            $this->files->makeDirectory($helpersDest, 0755, true);
        }

        $this->copyDirectory($helpersStubDir, $helpersDest);

        $this->components->twoColumnDetail('Copied Helpers → Bloom/Helpers/', '<fg=green;options=bold>DONE</>');
    }

    protected function copyBloomConfig(): void
    {
        $configSrc = dirname(__DIR__, 2).'/config';
        $configDest = base_path('Bloom/config');

        if (! $this->files->isDirectory($configDest)) {
            $this->files->makeDirectory($configDest, 0755, true);
        }

        $configFiles = ['commands.php', 'components.php', 'composers.php', 'livewire.php'];

        foreach ($configFiles as $file) {
            $src = $configSrc.'/'.$file;
            $dest = $configDest.'/'.$file;

            if ($this->files->exists($src)) {
                if (! $this->files->exists($dest) || $this->option('force')) {
                    $this->files->copy($src, $dest);
                }
            }
        }

        $this->components->twoColumnDetail('Copied Bloom config → Bloom/config/', '<fg=green;options=bold>DONE</>');
    }

    protected function patchAppCss(): void
    {
        $appCssPath = base_path('resources/css/app.css');

        if (! $this->files->exists($appCssPath)) {
            $this->printManualInstructions('app.css');

            return;
        }

        $content = $this->files->get($appCssPath);

        // Check if already patched
        if (str_contains($content, 'bloom-tokens.css')) {
            $this->components->twoColumnDetail('resources/css/app.css', '<fg=yellow;options=bold>ALREADY PATCHED</>');

            return;
        }

        // Detect fresh Sage default (TW4 style: starts with @import "tailwindcss")
        $bloomImports = <<<'CSS'
@import "./bloom-tokens.css";
@import "./bloom-base.css";
CSS;

        $bloomSource = '@source "../../Bloom/";';

        if (str_contains($content, '@import "tailwindcss"')) {
            // TW4 Sage default — insert bloom imports after tailwindcss import
            $content = str_replace(
                '@import "tailwindcss";',
                "@import \"tailwindcss\";\n\n{$bloomImports}\n{$bloomSource}",
                $content
            );

            $this->files->put($appCssPath, $content);
            $this->components->twoColumnDetail('Patched resources/css/app.css', '<fg=green;options=bold>DONE</>');
        } elseif (str_contains($content, '@tailwind base')) {
            // TW3 Sage default — insert bloom imports after @tailwind base
            $content = str_replace(
                '@tailwind base;',
                "@tailwind base;\n\n{$bloomImports}",
                $content
            );

            $this->files->put($appCssPath, $content);
            $this->components->twoColumnDetail('Patched resources/css/app.css', '<fg=green;options=bold>DONE</>');
        } else {
            // Customized file — print manual instructions
            $this->printManualCssInstructions();
        }
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
        $themeName = basename(base_path());
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

    protected function copyStubFile(string $stubRelative, string $destRelative): void
    {
        $src = $this->stubsPath.'/'.$stubRelative;
        $dest = base_path($destRelative);

        if (! $this->files->exists($src)) {
            return;
        }

        $destDir = dirname($dest);
        if (! $this->files->isDirectory($destDir)) {
            $this->files->makeDirectory($destDir, 0755, true);
        }

        if (! $this->files->exists($dest) || $this->option('force')) {
            $this->files->copy($src, $dest);
        }
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
            'css/bloom-tokens.css' => 'resources/css/bloom-tokens.css',
            'css/bloom-base.css' => 'resources/css/bloom-base.css',
            'css/editor.css' => 'resources/css/editor.css',
            'css/admin.css' => 'resources/css/admin.css',
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
