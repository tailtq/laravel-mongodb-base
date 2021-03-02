<?php

namespace Infrastructure\Commands;

use Illuminate\Console\Command;

class GenerateModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:generate {moduleName} {moduleNamePlural}
                                            {--sourceDirectory=app/Templates}
                                            {--destinationDirectory=modules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new module.
                        Ex: `php artisan module:generate Process Processes
                                --sourceDirectory=app/Templates
                                --destinationDirectory=modules`';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create module
     */
    public function handle()
    {
        $moduleName = $this->argument('moduleName');
        $moduleNamePlural = strtolower($this->argument('moduleNamePlural'));
        $sourceDirectory = $this->option('sourceDirectory');
        $destinationDirectory = $this->option('destinationDirectory') . '/' . $moduleName;

        $files = [
            ["{$sourceDirectory}/Controller.txt", "{$destinationDirectory}/Controllers/{$moduleName}Controller.php"],
            ["{$sourceDirectory}/CreateRequest.txt", "{$destinationDirectory}/Requests/Create{$moduleName}Request.php"],
            ["{$sourceDirectory}/Model.txt", "{$destinationDirectory}/Models/{$moduleName}.php"],
            ["{$sourceDirectory}/Repository.txt", "{$destinationDirectory}/Repositories/{$moduleName}Repository.php"],
            ["{$sourceDirectory}/Service.txt", "{$destinationDirectory}/Services/{$moduleName}Service.php"],
            ["{$sourceDirectory}/routes.txt", "{$destinationDirectory}/routes.php"],
            ["{$sourceDirectory}/ServiceProvider.txt", "{$destinationDirectory}/{$moduleName}ServiceProvider.php"],
        ];

        foreach ($files as $pair) {
            $source = $pair[0];
            $destination = $pair[1];
            $content = $this->addModuleName($source, $moduleName, $moduleNamePlural);
            $this->mkdirMkFileSaveContent($destination, $content);
        }
    }

    /**
     * Parse a template file and pass module name into the content
     * @param $filePath
     * @param $moduleName
     * @param $moduleNamePlural
     * @return string
     */
    private function addModuleName($filePath, $moduleName, $moduleNamePlural)
    {
        $file = fopen($filePath, 'r');
        $content = fread($file, filesize($filePath));
        fclose($file);

        $content = str_replace('{{moduleName}}', $moduleName, $content);
        $content = str_replace('{{moduleNamePlural}}', $moduleNamePlural, $content);

        return $content;
    }

    /**
     * Make directories recursively if not exists, then save file
     * @param $filePath
     * @param $content
     */
    private function mkdirMkFileSaveContent($filePath, $content)
    {
        $paths = explode('/', $filePath);
        unset($paths[count($paths) - 1]);
        $directoryPath = implode('/', $paths);

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);
        }
        $this->saveFile($filePath, $content);
    }

    /**
     * @param $filePath
     * @param $content
     */
    private function saveFile(string $filePath, string $content)
    {
        $file = fopen($filePath, 'w+');
        fwrite($file, $content);
        fclose($file);
    }
}
