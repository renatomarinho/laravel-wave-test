<?php

namespace RenatoMarinho\LaravelWaveTest\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ExecuteTestsCommand extends Command
{
    protected $signature = 'execute:tests';
    protected $description = 'Execute all tests inside the Feature directory and its subdirectories recursively';

    public function handle()
    {
        $this->info('Executing tests in the Feature directory...');

        $featurePath = base_path('tests/Feature');

        if (!is_dir($featurePath)) {
            $this->error('The Feature directory does not exist.');
            return;
        }

        $folders = $this->getFoldersRecursively($featurePath);

        if (empty($folders)) {
            $this->warn('No folders found in the Feature directory.');
            return;
        }

        foreach ($folders as $folder) {
            $this->info("Running tests for folder: $folder");

            $folderPath = "$featurePath/$folder";

            $allFiles = $this->listAllPhpFiles($folderPath);
            if (empty($allFiles)) {
                $this->warn("No PHP files found in folder: $folder");
                continue;
            }


            //$this->info("All PHP files in folder: $folder");
            //foreach ($allFiles as $file) {
            //    $this->line(" - $file");
            //}

            $testFiles = array_filter($allFiles, function ($file) {
                return str_ends_with($file, 'Test.php');
            });

            if (empty($testFiles)) {
                $this->warn("No test files found in folder: $folder");
                continue;
            }

            $process = new Process(['vendor/bin/phpunit', '--no-configuration', $folderPath]);
            $process->run(function ($type, $buffer) use ($folder) {
                if ($type === Process::ERR) {
                    $this->error($buffer);
                } else {
                    $this->line($buffer);
                }
            });

            if (!$process->isSuccessful()) {
                $this->error("Tests failed for folder: $folder");
            } else {
                $this->info("Tests passed for folder: $folder");
            }
        }

        $this->info('All tests executed successfully!');
    }

    private function getFoldersRecursively(string $path): array
    {
        $folders = [];
        $iterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($recursiveIterator as $file) {
            if ($file->isDir()) {
                $relativePath = substr($file->getPathname(), strlen($path) + 1);
                $folders[] = $relativePath;
            }
        }

        return array_unique($folders);
    }

    private function listAllPhpFiles(string $path): array
    {
        $files = [];
        if (is_dir($path)) {
            $iterator = new \DirectoryIterator($path);
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getFilename();
                }
            }
        }
        return $files;
    }
}
