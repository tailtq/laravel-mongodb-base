<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:generate {moduleName} {--sourceDirectory=app/Templates} {--destinationDirectory=modules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new module.
                        Ex: `php artisan module:generate Process
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
        $sourceDirectory = $this->option('sourceDirectory');
        $destinationDirectory = $this->option('destinationDirectory') . '/' . $moduleName;

        $content = $this->addModuleName("{$sourceDirectory}/Controller.txt", $moduleName);
        $this->mkdirMkFileSaveContent("{$destinationDirectory}/Controllers/{$moduleName}Controller.php", $content);

        $content = $this->addModuleName("{$sourceDirectory}/CreateRequest.txt", $moduleName);
        $this->mkdirMkFileSaveContent("{$destinationDirectory}/Requests/Create{$moduleName}Request.php", $content);

        $content = $this->addModuleName("{$sourceDirectory}/Model.txt", $moduleName);
        $this->mkdirMkFileSaveContent("{$destinationDirectory}/Models/{$moduleName}.php", $content);

        $content = $this->addModuleName("{$sourceDirectory}/Repository.txt", $moduleName);
        $this->mkdirMkFileSaveContent("{$destinationDirectory}/Repositories/{$moduleName}Repository.php", $content);

        $content = $this->addModuleName("{$sourceDirectory}/Service.txt", $moduleName);
        $this->mkdirMkFileSaveContent("{$destinationDirectory}/Services/{$moduleName}Service.php", $content);

        $content = $this->addModuleName("{$sourceDirectory}/routes.txt", $moduleName);
        $this->mkdirMkFileSaveContent("{$destinationDirectory}/routes.php", $content);
    }

    /**
     * Parse a template file and pass module name into the content
     * @param $filePath
     * @param $moduleName
     * @return string
     */
    private function addModuleName($filePath, $moduleName)
    {
        $file = fopen($filePath, 'r');
        $content = fread($file, filesize($filePath));
        fclose($file);

        return str_replace('{{moduleName}}', $moduleName, $content);
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
