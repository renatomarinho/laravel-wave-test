<?php

namespace Tests\Unit\Commands;

use PHPUnit\Framework\TestCase;
use RenatoMarinho\LaravelWaveTest\Commands\GenerateTestsCommand;
use ReflectionClass;

class GenerateTestsCommandTest extends TestCase
{
    private GenerateTestsCommand $command;
    private ReflectionClass $reflector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new GenerateTestsCommand();
        $this->reflector = new ReflectionClass(GenerateTestsCommand::class);
    }

    public function test_to_pascal_case_converts_hyphenated_string()
    {
        $method = $this->reflector->getMethod('toPascalCase');
        $method->setAccessible(true);

        $testCases = [
            'user-profile' => 'UserProfile',
            'api-users-list' => 'ApiUsersList',
            'get-user-settings' => 'GetUserSettings',
            'simple' => 'Simple',
            'multi-word-string' => 'MultiWordString'
        ];

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->command, $input);
            $this->assertEquals($expected, $result);
        }
    }

    public function test_prepare_test_file_info_handles_hyphenated_routes()
    {
        $method = $this->reflector->getMethod('prepareTestFileInfo');
        $method->setAccessible(true);

        $testCases = [
            'api-users.create' => ['ApiUsers', 'CreateTest'],
            'user-profile.update-settings' => ['UserProfile', 'UpdateSettingsTest'],
            'admin-panel.user-management.list' => ['AdminPanel', 'UserManagementListTest']
        ];

        foreach ($testCases as $input => $expected) {
            $result = $method->invoke($this->command, $input);
            $this->assertEquals($expected, $result);
        }
    }

    public function test_prepare_test_file_info_returns_null_for_invalid_format()
    {
        $method = $this->reflector->getMethod('prepareTestFileInfo');
        $method->setAccessible(true);

        $invalidRoutes = [
            'invalid',
            'no-dot-here',
            '-invalid.format',
            '.invalid.format',
        ];

        foreach ($invalidRoutes as $route) {
            $result = $method->invoke($this->command, $route);
            $this->assertNull($result);
        }
    }

    public function test_build_test_file_content_generates_correct_structure()
    {
        $method = $this->reflector->getMethod('buildTestFileContent');
        $method->setAccessible(true);

        $result = $method->invoke(
            $this->command,
            'Users',
            'CreateTest',
            'users.create',
            ['id', 'name']
        );

        // Verify the generated content contains essential parts
        $this->assertStringContainsString('namespace Tests\\Feature\\Users;', $result);
        $this->assertStringContainsString('class CreateTest extends TestCase', $result);
        $this->assertStringContainsString('use RefreshDatabase;', $result);
        $this->assertStringContainsString('protected $faker;', $result);
        $this->assertStringContainsString("'id' => \$this->faker->word", $result);
        $this->assertStringContainsString("'name' => \$this->faker->word", $result);
    }
}
