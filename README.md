# Laravel Test Wave

**Laravel Test Wave** is a tool designed to streamline the process of creating and running tests in Laravel projects. This package provides two main commands: `generate:tests` to automatically generate test files based on your application's routes, and `execute:tests` to run all tests within the `Feature` directory and its subdirectories.

> **Note:** This package is intended to accelerate the initial creation of tests. The generated `*Test.php` files may require manual adjustments to properly fit the specific requirements of your project.

---

## Installation

1. **Install the package via Composer:**

   ```bash
   composer require renatomarinho/laravel-test-wave
   ```

2. **Verify the ServiceProvider Registration:**
   The package includes a `ServiceProvider` that will be registered automatically in Laravel 5.5+ (thanks to [Package Auto-Discovery](https://laravel.com/docs/8.x/packages#auto-discovery)).

   If you're using an older version of Laravel or auto-discovery is disabled, add the following provider to the `providers` array in the `config/app.php` file:

   ```php
   RenatoMarinho\LaravelWaveTest\TestGeneratorServiceProvider::class,
   ```

3. **Publish Optional Configurations (if needed):**

   ```bash
   php artisan vendor:publish --provider="RenatoMarinho\LaravelWaveTest\TestGeneratorServiceProvider"
   ```

---

## Available Commands

### 1. `php artisan generate:tests`

This command scans all registered routes in your Laravel project and generates corresponding test files in the `tests/Feature` directory. Each route will be converted into a test file, organized into folders based on the route name.

#### Features:
- Organizes tests into folders based on the first segment of the route name.
- Converts route names like `example.update` into `ExampleUpdateTest.php`.
- Automatically mocks route parameters using Faker.

#### Example Generated Structure:

```
tests/
└── Feature/
    ├── Example/
    │   ├── IndexTest.php
    │   ├── StoreTest.php
    │   ├── ShowTest.php
    │   ├── UpdateTest.php
    │   └── DestroyTest.php
    └── AnotherFolder/
        └── AnotherTest.php
```

#### Usage:

```bash
php artisan generate:tests
```

---

### 2. `php artisan execute:tests`

This command lists all folders inside `tests/Feature` and runs tests in those folders using PHPUnit. It simplifies the process of executing all tests organized in subfolders.

#### Usage:

```bash
php artisan execute:tests
```

#### Example Output:

```
Executing tests in the Feature directory...
Running tests for folder: Example
PHPUnit 11.5.7 by Sebastian Bergmann and contributors.

..
Time: 00:01.234, Memory: 20.00 MB

OK (2 tests, 2 assertions)
Tests passed for folder: Example
All tests executed successfully!
```

---

## How to Use

1. **Generate Automatic Tests:**
   Run the following command to generate test files based on your application's routes:

   ```bash
   php artisan generate:tests
   ```

   > **Tip:** Ensure your routes are named appropriately (e.g., `example.index`, `example.store`) to ensure proper test generation.

2. **Adjust the Generated Tests:**
   After generation, review the created `*Test.php` files and make necessary adjustments to align them with your project's specific requirements. For example:
   - Change HTTP request methods (`get`, `post`, `delete`, etc.) as needed.
   - Customize mocked values to match the data expected by your controllers.

3. **Run All Tests:**
   Use the following command to execute all tests within the `Feature` directory and its subdirectories:

   ```bash
   php artisan execute:tests
   ```

---

## Limitations

- The generated tests serve as a starting point and may need adjustments to function correctly within the context of your project.
- Currently, the package does not support generating complex integration tests or tests involving multiple scenarios.
- The mocking logic uses default values from Faker. You may need to customize these values to meet your application's validation requirements.

---

## Roadmap

- **Add Artificial Intelligence (AI) Integration:** We plan to incorporate AI to improve the quality of generated tests, making them more robust and aligned with best practices.
- **Support for Integration Tests:** Expand the package to generate integration tests in addition to functional tests.
- **Enhanced Parameter Mocking:** Implement advanced logic to detect parameter types and generate more accurate mock values.
- **Support for Other Laravel Features:** Add support for testing events, queues, jobs, and other Laravel features.

---

## Contribution

Contributions are welcome! If you encounter bugs or have suggestions for improvements, please open an issue or submit a pull request to the official repository.

---

## License

This package is distributed under the [Apache 2.0](LICENSE). Feel free to use, modify, and distribute it according to the terms of the license.

---

We hope this package helps you save time and effort while ensuring your Laravel application remains well-tested. Happy coding! 🚀
