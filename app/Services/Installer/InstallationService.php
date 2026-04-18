<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallationService
{
    /**
     * Check server requirements.
     */
    public function checkRequirements(): array
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version (>= 8.3)',
                'check' => version_compare(PHP_VERSION, '8.3.0', '>='),
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'check' => extension_loaded('openssl'),
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'check' => extension_loaded('pdo'),
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'check' => extension_loaded('mbstring'),
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'check' => extension_loaded('tokenizer'),
            ],
            'xml' => [
                'name' => 'XML Extension',
                'check' => extension_loaded('xml'),
            ],
            'ctype' => [
                'name' => 'Ctype Extension',
                'check' => extension_loaded('ctype'),
            ],
            'json' => [
                'name' => 'JSON Extension',
                'check' => extension_loaded('json'),
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'check' => extension_loaded('curl'),
            ],
            'zip' => [
                'name' => 'ZIP Extension',
                'check' => extension_loaded('zip'),
            ],
            'bcmath' => [
                'name' => 'BCMath Extension',
                'check' => extension_loaded('bcmath'),
            ],
            'gd' => [
                'name' => 'GD Extension',
                'check' => extension_loaded('gd'),
            ],
            'storage_writable' => [
                'name' => 'Storage Directory Writable',
                'check' => is_writable(storage_path()),
            ],
            'bootstrap_cache_writable' => [
                'name' => 'Bootstrap Cache Directory Writable',
                'check' => is_writable(base_path('bootstrap/cache')),
            ],
            'env_writable' => [
                'name' => '.env File Writable (if exists)',
                'check' => File::exists(base_path('.env')) ? is_writable(base_path('.env')) : is_writable(base_path()),
            ],
        ];

        return $requirements;
    }

    /**
     * Test database connection.
     */
    public function testDatabaseConnection(array $config): bool
    {
        try {
            config(['database.connections.installer_test' => [
                'driver' => 'mysql',
                'host' => $config['host'],
                'port' => $config['port'] ?? '3306',
                'database' => $config['database'],
                'username' => $config['username'],
                'password' => $config['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]]);

            DB::connection('installer_test')->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update .env file.
     */
    public function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        if (!File::exists($envPath)) {
            File::copy($envExamplePath, $envPath);
        }

        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            // Handle values with spaces
            if (strpos($value, ' ') !== false) {
                $value = '"' . $value . '"';
            }

            if (strpos($content, "{$key}=") !== false) {
                $content = preg_replace("/{$key}=.*/", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $content);
        Artisan::call('config:clear');
    }

    /**
     * Run migrations and seeds.
     */
    public function setupDatabase(): array
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            return ['status' => true, 'message' => 'Database migrations and seeding completed successfully.'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create Admin user.
     */
    public function createAdmin(array $data): bool
    {
        try {
            $admin = \App\Models\Admin::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'is_active' => true,
                ]
            );

            $role = \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => 'super_admin',
                'guard_name' => 'admin'
            ]);
            
            if (!$admin->hasRole('super_admin')) {
                $admin->assignRole($role);
            }

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Installer Admin Creation Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finalize installation.
     */
    public function finalizeInstallation(): void
    {
        Artisan::call('key:generate', ['--force' => true]);
        Artisan::call('storage:link', ['--force' => true]);
        Artisan::call('scribe:generate', ['--force' => true]);

        // Generate JWT Secret if not provided or if it's the placeholder
        $jwtSecret = env('JWT_SECRET');
        if (!$jwtSecret || $jwtSecret === 'your_jwt_token' || strlen($jwtSecret) < 32) {
            Artisan::call('jwt:secret', ['--force' => true]);
        }

        // Send installation finished email
        try {
            $frontendUrl = config('app.frontend_url');
            $adminUrl = config('app.admin_url');
            \Illuminate\Support\Facades\Mail::to('shiplu2111@gmail.com')->send(new \App\Mail\InstallationFinishedMail($frontendUrl, $adminUrl));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send installation finished email: ' . $e->getMessage());
        }

        File::put(storage_path('installed'), json_encode([
            'date' => date('Y-m-d H:i:s'),
            'version' => config('app.version', '1.0.0'),
        ]));
    }
}
