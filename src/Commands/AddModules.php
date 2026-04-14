<?php

declare(strict_types=1);

namespace HexDigital\Bloom\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;

class AddModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bloom:module-add {module?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add bloom modules to your project';

    protected string $modulesDir;

    protected string $destinationDir;

    protected array $configFiles = [
        'components.php',
        'livewire.php',
        'composers.php',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $module = $this->argument('module');

        $this->modulesDir = base_path('vendor/hex-digital/bloom-modules/src');
        $this->destinationDir = base_path('Bloom');
        $filesystem = new Filesystem;

        if (! $filesystem->exists($this->modulesDir)) {
            error('hex-digital/bloom-modules missing. Please add the package: https://github.com/hex-digital/bloom-modules.git');

            return;
        }

        $finder = new Finder;
        $finder->directories()->in($this->modulesDir)->depth(0);

        $bloomModules = [];
        foreach ($finder as $dir) {
            $bloomModules[] = $dir->getFilename();
        }

        if (empty($bloomModules)) {
            error('No modules found.');

            return;
        }

        if ($module && ! in_array($module, $bloomModules)) {
            error("Module doesn't exist in hex-digital/bloom-modules package. Did you use the right name?");

            return;
        }

        if ($module) {
            $this->handleImportModules([$module]);
        } else {
            $this->handleSelect($bloomModules);
        }
    }

    public function handleSelect($modules): void
    {
        $selectedModules = multiselect(
            'Select which modules you would like to copy.',
            $modules
        );

        $this->handleImportModules($selectedModules);
    }

    public function handleImportModules($modules): void
    {
        $filesystem = new Filesystem;

        $destinationPaths = [
            'Blocks' => 'Bloom/Blocks',
            'Components' => 'Bloom/Components',
            'Composers' => 'Bloom/Composers',
            'Livewire' => 'Bloom/Livewire',
        ];

        foreach ($modules as $module) {
            $status = [
                'module' => $module,
                'errors' => false,
                'config' => null,
                'imported' => [],
            ];

            $moduleName = basename($module);

            foreach ($destinationPaths as $dirType => $destinationPath) {
                $srcDir = $this->modulesDir.'/'.$module.'/'.$dirType;

                if ($filesystem->exists($srcDir)) {
                    $destinationDir = base_path($destinationPath).'/'.$moduleName;

                    if ($filesystem->exists($destinationDir)) {
                        $status['errors'][] = "{$module} already exists in {$destinationPath}";
                    } else {
                        $dirCopied = $filesystem->copyDirectory($srcDir, $destinationDir);

                        $configAdded = $this->handleConfig($this->modulesDir.'/'.$module);

                        if ($configAdded) {
                            $status['config'] = true;
                        } else {
                            $status['config'] = false;
                            $status['errors'][] = "Issue with $dirType config";
                        }

                        if ($dirCopied) {
                            $status['imported'][] = $dirType;
                        }
                    }
                }
            }

            $this->handleOutputMessage($status);
        }
    }

    public function handleConfig($srcModule)
    {
        $filesystem = new Filesystem;
        $moduleConfigPath = $srcModule.'/config/';

        $configExists = $filesystem->isDirectory($moduleConfigPath);

        if ($configExists) {
            $localConfigPath = base_path('Bloom/config/');

            foreach ($this->configFiles as $file) {
                if (! file_exists($moduleConfigPath.$file)) {
                    continue;
                }

                $localConfig = require $localConfigPath.$file;

                $moduleConfig = require $moduleConfigPath.$file;

                $mergedConfig = $localConfig + $moduleConfig;

                $content = "<?php return [\n";
                foreach ($mergedConfig as $key => $value) {
                    if (str_starts_with($value, 'Bloom')) {
                        $content .= "    '$key' => $value::class,\n";
                    } else {
                        $content .= "    '$key' => '$value',\n";
                    }
                }
                $content .= "];\n";

                file_put_contents($localConfigPath.$file, $content);
            }
        } else {
            return false;
        }

        return true;
    }

    public function handleOutputMessage($status)
    {
        intro("{$status['module']}");

        if ($status['errors'] || is_array($status['errors'])) {
            if (is_array($status['errors'])) {
                foreach ($status['errors'] as $errorMsg) {
                    error($errorMsg);
                }
            } else {
                error($status['errors']);
            }
        }

        if (count($status['imported']) > 0) {
            foreach ($status['imported'] as $item) {
                info("{$item} imported.");
            }
        }

        if ($status['config']) {
            info('Configs imported.');
        }
    }
}
