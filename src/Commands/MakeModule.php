<?php

declare(strict_types=1);

namespace HexDigital\Bloom\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\text;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bloom:module-new
                            {name? : The name of the module to import}
                            {--K|composer : Flag to add a composer}
                            {--C|component : Flag to add a component}
                            {--B|block : Flag to add a block}
                            {--A|all : Flag to add a block, composer and component}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module from a template';

    protected string $modulesTemplate;

    protected string $destination;

    protected array $reservedNames = [
        'template',
        'Template',
    ];

    protected array $bloomDirMap = [
        'Block' => 'Blocks',
        'Component' => 'Components',
        'Composer' => 'Composers',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->modulesTemplate = base_path('vendor/hex-digital/bloom-modules/templates');
        $this->destination = base_path('Bloom/');

        $filesystem = new Filesystem;
        $moduleName = trim($this->argument('name') ?? '');
        $moduleTypes = [
            'Block' => $this->option('block') || $this->option('all'),
            'Component' => $this->option('component') || $this->option('all'),
            'Composer' => $this->option('composer') || $this->option('all'),
        ];

        if (! $filesystem->exists($this->modulesTemplate)) {
            $this->error('hex-digital/bloom-modules missing. Please add the package: https://github.com/hex-digital/bloom-modules.git');

            return;
        }

        if (! $moduleName) {
            $moduleName = text(
                label: 'Please provide a name for your module:',
                placeholder: 'E.g. "Portfolio Tile" or "Gallery"',
            );
        }

        if (in_array($moduleName, $this->reservedNames)) {
            error("$moduleName is a reserved word. Please try a different name");

            return;
        }

        if (! $moduleTypes['Block'] && ! $moduleTypes['Component'] && ! $moduleTypes['Composer']) {
            $moduleTypesSelect = multiselect(
                'Select which you would like to add:',
                array_keys($moduleTypes)
            );

            $moduleTypes = $this->updateModuleTypes($moduleTypes, $moduleTypesSelect);
        }

        $moduleDirName = $this->toUpperCamelCase($moduleName);

        intro($moduleDirName);

        foreach ($moduleTypes as $moduleType => $moduleTypeValue) {
            if (! $moduleTypeValue) {
                continue;
            }

            $moduleDirPath = $this->destination.$this->bloomDirMap[$moduleType].'/'.$moduleDirName;

            if ($filesystem->exists($moduleDirPath)) {
                error("\"$moduleDirName\" $moduleType already exists.");

                continue;
            }

            $filesystem->copyDirectory($this->modulesTemplate.'/'.$moduleType, $moduleDirPath);

            $this->handleRenaming($moduleName, $moduleDirPath);

            if ($filesystem->exists($moduleDirPath)) {
                info("$moduleType has been added.");
            } else {
                error("$moduleType has not been added.");
            }
        }
    }

    protected function replaceContent($filePath, $moduleName)
    {
        $content = file_get_contents($filePath);
        $content = str_replace([
            'TemplateUpperCamelCase',
            'templateVariable',
            'template-kebab',
            'template_snake',
            'TemplateName',
        ], [
            $this->toUpperCamelCase($moduleName),
            $this->toCamelCase($moduleName),
            $this->toKebabCase($moduleName),
            $this->toSnakeCase($moduleName),
            $moduleName,
        ], $content);
        file_put_contents($filePath, $content);
    }

    protected function handleRenaming($moduleName, $moduleDirPath)
    {
        $filesystem = new Filesystem;

        $files = $filesystem->allFiles($moduleDirPath);
        foreach ($files as $file) {
            $this->replaceContent($file->getPathname(), $moduleName);
            $fileName = $file->getFilename();
            $newFileName = str_replace([
                'TemplateUpperCamelCase',
                'template-kebab',
                'template_snake',
                'TemplateName',
            ], [
                $this->toUpperCamelCase($moduleName),
                $this->toKebabCase($moduleName),
                $this->toSnakeCase($moduleName),
                $moduleName,
            ], $fileName);
            $filesystem->move($file->getPathname(), $file->getPath().'/'.$newFileName);
        }
    }

    protected function toUpperCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    protected function toKebabCase($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', str_replace(' ', '-', $string)));
    }

    protected function toSnakeCase($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace(' ', '_', $string)));
    }

    protected function toCamelCase($string)
    {
        $words = preg_split('/[\s_-]+/', $string);
        $camelCaseString = strtolower(array_shift($words));
        $camelCaseString .= implode('', array_map('ucwords', $words));

        return $camelCaseString;
    }

    public function updateModuleTypes($moduleTypes, $moduleTypesSelect)
    {
        foreach ($moduleTypesSelect as $selected) {
            if (array_key_exists($selected, $moduleTypes)) {
                $moduleTypes[$selected] = true;
            }
        }

        return $moduleTypes;
    }
}
