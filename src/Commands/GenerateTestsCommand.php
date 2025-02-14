<?php

namespace RenatoMarinho\LaravelWaveTest\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class GenerateTestsCommand extends Command
{
    protected $signature = 'generate:tests';
    protected $description = 'Generate automatic tests for the application';

    public function handle()
    {
        $this->info('Generating tests...');

        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $this->processRoute($route);
        }
        $this->info('Tests generated successfully!');
    }

    private function processRoute($route)
    {
        $routeName = $this->getRouteName($route);

        if (empty($routeName)) {
            $this->warn("Route without name: " . $route->uri());
            return;
        }

        $controllerData = $this->getControllerAndMethod($route);

        if (!is_array($controllerData) || count($controllerData) !== 2) {
            $this->warn("Invalid controller data for route: $routeName");
            return;
        }

        [$controllerClass, $method] = $controllerData;

        $this->generateTest($routeName, $controllerClass, $method);
    }

    private function getRouteName($route): ?string
    {
        return $route->getName();
    }

    private function getControllerAndMethod($route): ?array
    {
        $action = $route->getAction();
        $controller = isset($action['controller']) ? $action['controller'] : null;

        if (!$controller) {
            return null;
        }

        if (strpos($controller, '@') === false) {
            return null;
        }

        return explode('@', $controller);
    }

    private function generateTest(string $routeName, string $controllerClass, string $method)
    {
        $testFileInfo = $this->prepareTestFileInfo($routeName);
        if (!$testFileInfo) {
            return;
        }

        [$folderName, $fileName] = $testFileInfo;

        $testPath = $this->createTestFilePath($folderName, $fileName);
        if (file_exists($testPath)) {
            $this->comment("Test already exists: $fileName in folder $folderName");
            return;
        }

        $parameters = $this->getRouteParameters($routeName);
        $testContent = $this->buildTestFileContent($folderName, $fileName, $routeName, $parameters);

        file_put_contents($testPath, $testContent);
        $this->info("Test created: $fileName in folder $folderName");
    }

    private function prepareTestFileInfo(string $routeName): ?array
    {
        $parts = explode('.', $routeName);
        if (count($parts) < 2) {
            $this->warn("Invalid route name format: $routeName");
            return null;
        }

        $folderName = ucfirst($parts[0]);
        $baseFileName = preg_replace_callback(
            '/\.(.)/',
            function ($match) {
                return strtoupper($match[1]);
            },
            implode('.', array_slice($parts, 1))
        );

        $fileName = str_replace('.', '', $baseFileName) . 'Test';
        return [$folderName, $fileName];
    }

    private function createTestFilePath(string $folderName, string $fileName): string
    {
        $testPath = base_path("tests/Feature/$folderName/$fileName.php");
        $directory = dirname($testPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $testPath;
    }

    private function getRouteParameters(string $routeName): array
    {
        $route = Route::getRoutes()->getByName($routeName);
        if (!$route) {
            return [];
        }

        $parameters = [];
        $uri = $route->uri();

        preg_match_all('/\{(.*?)\}/', $uri, $matches);
        if (isset($matches[1])) {
            $parameters = $matches[1];
        }

        return $parameters;
    }

    private function buildTestFileContent(string $folderName, string $fileName, string $routeName, array $parameters): string
    {
        $testContent = "<?php\n\n";
        $testContent .= "namespace Tests\\Feature\\$folderName;\n\n"; // Atualiza o namespace
        $testContent .= "use Tests\TestCase;\n";
        $testContent .= "use Illuminate\Foundation\Testing\WithFaker;\n";
        $testContent .= "use Illuminate\Foundation\Testing\RefreshDatabase;\n\n";

        $testContent .= "class $fileName extends TestCase\n";
        $testContent .= "{\n";
        $testContent .= "    use RefreshDatabase;\n\n";

        $testContent .= "    protected \$faker;\n\n";
        $testContent .= "    protected function setUp(): void\n";
        $testContent .= "    {\n";
        $testContent .= "        parent::setUp();\n";
        $testContent .= "        \$this->faker = \\Faker\\Factory::create();\n";
        $testContent .= "    }\n\n";

        $testContent .= "    /**\n";
        $testContent .= "     * Test $fileName endpoint.\n";
        $testContent .= "     *\n";
        $testContent .= "     * @return void\n";
        $testContent .= "     */\n";
        $testContent .= "    public function test_$fileName()\n";
        $testContent .= "    {\n";

        if (!empty($parameters)) {
            $testContent .= "        \$parameters = [\n";
            foreach ($parameters as $parameter) {
                $testContent .= "            '$parameter' => \$this->faker->word,\n";
            }
            $testContent .= "        ];\n\n";
            $testContent .= "        \$response = \$this->get(route('$routeName', \$parameters));\n\n";
        } else {
            $testContent .= "        \$response = \$this->get(route('$routeName'));\n\n";
        }

        $testContent .= "        \$response->assertStatus(200);\n";
        $testContent .= "    }\n";
        $testContent .= "}\n";

        return $testContent;
    }
}
