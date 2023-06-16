<?php

namespace Insurance\Openimis\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openimis:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the commands necessary to prepare openimis for use';

    public function handle()
    {
        $this->configureSets();
    }

    /*
     * Execute the console command.
     *
     * @return void
     */

     protected function configureSets()
     {
         $this->replaceInFile(config_path('openimis.php'));
         $this->replaceInDir(database_path('migrations/openimis/'), '2023_04_26_444000_create_openimis_patient_table.php');
        //  $this->replaceInDir(database_path('migrations/openimis/'), '2023_04_26_444001_create_openimis_claim_logs_table.php');
         $this->replaceInDir(database_path('migrations/openimis/'), '2023_06_12_444002_create_openimis_logs_table.php');

     }

     protected function replaceInFile($path)
     {
         $pathConfig = __DIR__.'/../../config/openimis.php';

         if (file_exists($path)) {
             File::delete($path);
         }
         File::copy($pathConfig, $path);
     }

     protected function replaceInDir($path, $file)
     {
         $pathOfDatabaseMigration = $path;

         if (!File::isDirectory($pathOfDatabaseMigration)) {
             //  File::deleteDirectory($pathOfDatabaseMigration);
             File::makeDirectory($pathOfDatabaseMigration, 0777, true, true);
         }

         if (file_exists($pathOfDatabaseMigration.$file)) {
             File::delete($pathOfDatabaseMigration.$file);
         }
         File::copy(__DIR__.'/../../database/migrations/'.$file, $pathOfDatabaseMigration.$file);
     }
}
