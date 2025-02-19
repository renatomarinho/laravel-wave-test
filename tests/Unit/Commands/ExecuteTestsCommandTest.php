<?php

namespace Tests\Unit\Commands;

use PHPUnit\Framework\TestCase;
use RenatoMarinho\LaravelWaveTest\Commands\ExecuteTestsCommand;
use ReflectionClass;

class ExecuteTestsCommandTest extends TestCase
{
    private ExecuteTestsCommand $command;
    private ReflectionClass $reflector;
    private string $testPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new ExecuteTestsCommand();
        $this->reflector = new ReflectionClass(ExecuteTestsCommand::class);
        $this->testPath = sys_get_temp_dir() . '/test_feature';
        $this->createTestDirectory();
    }

    protected function tearDown(): void
    {
        $this->removeTestDirectory();
        parent::tearDown();
    }

    private function createTestDirectory(): void
    {
        mkdir($this->testPath, 0777, true);
        mkdir($this->testPath . '/Folder1', 0777, true);
        mkdir($this->testPath . '/Folder2', 0777, true);

        // Create test files
        file_put_contents($this->testPath . '/Folder1/Test1Test.php', '<?php');
        file_put_contents($this->testPath . '/Folder1/Helper.php', '<?php');
        file_put_contents($this->testPath . '/Folder2/Test2Test.php', '<?php');
    }

    private function removeTestDirectory(): void
    {
        if (is_dir($this->testPath)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->testPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($this->testPath);
        }
    }

    public function test_get_folders_recursively_returns_all_subfolders()
    {
        $method = $this->reflector->getMethod('getFoldersRecursively');
        $method->setAccessible(true);

        $folders = $method->invoke($this->command, $this->testPath);

        $this->assertIsArray($folders);
        $this->assertCount(2, $folders);
        $this->assertContains('Folder1', $folders);
        $this->assertContains('Folder2', $folders);
    }

    public function test_list_all_php_files_returns_only_php_files()
    {
        $method = $this->reflector->getMethod('listAllPhpFiles');
        $method->setAccessible(true);

        $files = $method->invoke($this->command, $this->testPath . '/Folder1');

        $this->assertIsArray($files);
        $this->assertCount(2, $files);
        $this->assertContains('Test1Test.php', $files);
        $this->assertContains('Helper.php', $files);
    }

    public function test_list_all_php_files_returns_empty_array_for_invalid_directory()
    {
        $method = $this->reflector->getMethod('listAllPhpFiles');
        $method->setAccessible(true);

        $files = $method->invoke($this->command, $this->testPath . '/NonExistentFolder');

        $this->assertIsArray($files);
        $this->assertEmpty($files);
    }

    public function test_command_signature_and_description_are_set()
    {
        $this->assertEquals('execute:tests', $this->command->getName());
        $this->assertNotEmpty($this->command->getDescription());
    }
}
