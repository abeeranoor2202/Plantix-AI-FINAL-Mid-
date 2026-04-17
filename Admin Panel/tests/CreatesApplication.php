<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use PDO;
use Throwable;

trait CreatesApplication
{
    private static bool $isolatedTestDatabaseBootstrapped = false;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $this->bootstrapIsolatedTestingDatabase();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function bootstrapIsolatedTestingDatabase(): void
    {
        if (self::$isolatedTestDatabaseBootstrapped) {
            return;
        }

        if ((getenv('APP_ENV') ?: '') !== 'testing') {
            return;
        }

        if ((getenv('DB_CONNECTION') ?: '') !== 'mysql') {
            return;
        }

        $baseDatabase = getenv('DB_DATABASE') ?: 'plantix_ai_test';
        $token = getenv('TEST_TOKEN') ?: (string) getmypid();
        $isolatedDatabase = sprintf('%s_%s', $baseDatabase, preg_replace('/[^A-Za-z0-9_]/', '_', $token));

        $this->setEnv('DB_DATABASE', $isolatedDatabase);

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $safeName = str_replace('`', '``', $isolatedDatabase);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            // Fall back to the configured testing database when isolated DB bootstrap is unavailable.
            $this->setEnv('DB_DATABASE', $baseDatabase);
        }

        self::$isolatedTestDatabaseBootstrapped = true;
    }

    private function setEnv(string $key, string $value): void
    {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
