<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SwitchEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:switch-env {mode? : Environment mode to switch to (dev|prod)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch application environment and database between DEV (SQLite) and PROD (MySQL / Laravel Cloud)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->error('The .env file was not found at ' . $envPath);
            return Command::FAILURE;
        }

        $envContent = File::get($envPath);
        $currentEnv = env('APP_ENV', 'local');
        $currentDb = env('DB_CONNECTION', 'sqlite');

        $targetMode = strtolower($this->argument('mode') ?? '');

        // If no target provided, toggle between dev and prod
        if (empty($targetMode)) {
            $targetMode = ($currentEnv === 'production' || $currentDb === 'mysql') ? 'dev' : 'prod';
        }

        if (in_array($targetMode, ['dev', 'development', 'local', 'sqlite'])) {
            $this->switchToDev($envPath, $envContent);
        } elseif (in_array($targetMode, ['prod', 'production', 'cloud', 'mysql'])) {
            $this->switchToProd($envPath, $envContent);
        } else {
            $this->error("Invalid mode '{$targetMode}'. Use 'dev' (SQLite) or 'prod' (MySQL).");
            return Command::INVALID;
        }

        // Clear all Laravel runtime caches
        $this->info('Clearing configuration and application caches...');
        try {
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        } catch (\Throwable $e) {
            // Ignore cache clear exceptions during environment transition
        }

        return Command::SUCCESS;
    }

    /**
     * Switch to DEV mode (SQLite, Local, Debug ON)
     */
    protected function switchToDev(string $envPath, string $envContent): void
    {
        $this->info('===========================================================');
        $this->info('  SWITCHING TO DEVELOPMENT MODE (Local + SQLite)');
        $this->info('===========================================================');

        $replacements = [
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'true',
            'DB_CONNECTION' => 'sqlite',
            'LOG_LEVEL' => 'debug',
        ];

        $newContent = $this->updateEnvKeys($envContent, $replacements);

        // Ensure DB_DATABASE is set for sqlite
        if (!preg_match('/^DB_DATABASE=.*/m', $newContent)) {
            $newContent = preg_replace('/(DB_CONNECTION=sqlite)/', "$1\nDB_DATABASE=database/database.sqlite", $newContent);
        } else {
            $newContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=database/database.sqlite', $newContent);
        }

        File::put($envPath, $newContent);

        $this->table(
            ['Setting', 'Configured Value', 'Notes'],
            [
                ['APP_ENV', 'local', 'Development mode'],
                ['APP_DEBUG', 'true', 'Verbose error traces enabled'],
                ['DB_CONNECTION', 'sqlite', 'Lightweight local database'],
                ['DB_DATABASE', 'database/database.sqlite', 'Zero server dependency'],
                ['LOG_LEVEL', 'debug', 'Full logging output'],
            ]
        );

        $this->info("\n[SUCCESS] Environment switched to DEV (SQLite) successfully!");
    }

    /**
     * Switch to PROD mode (MySQL / Laravel Cloud, Production, Debug OFF)
     */
    protected function switchToProd(string $envPath, string $envContent): void
    {
        $this->info('===========================================================');
        $this->info('  SWITCHING TO PRODUCTION MODE (MySQL / Laravel Cloud)');
        $this->info('===========================================================');

        $replacements = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_CONNECTION' => 'mysql',
            'LOG_LEVEL' => 'error',
        ];

        $newContent = $this->updateEnvKeys($envContent, $replacements);

        // Ensure MySQL settings exist
        if (!preg_match('/^DB_HOST=.*/m', $newContent)) {
            $mysqlBlock = "\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=paddle_field_sports\nDB_USERNAME=root\nDB_PASSWORD=";
            $newContent = preg_replace('/(DB_CONNECTION=mysql)/', "$1" . $mysqlBlock, $newContent);
        } else {
            $newContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=paddle_field_sports', $newContent);
        }

        File::put($envPath, $newContent);

        $this->table(
            ['Setting', 'Configured Value', 'Notes'],
            [
                ['APP_ENV', 'production', 'Optimized production mode'],
                ['APP_DEBUG', 'false', 'Security safe (no sensitive leak)'],
                ['DB_CONNECTION', 'mysql', 'MySQL / Laravel Cloud Managed DB'],
                ['DB_DATABASE', 'paddle_field_sports', 'Production schema'],
                ['LOG_LEVEL', 'error', 'Production log filtering'],
            ]
        );

        $this->info("\n[SUCCESS] Environment switched to PROD (MySQL) successfully!");
        $this->comment("Reminder: When deploying to Laravel Cloud, also configure these in your Cloud Environment variables dashboard.");
    }

    /**
     * Replace or append key-value pairs in .env content
     */
    protected function updateEnvKeys(string $content, array $pairs): string
    {
        foreach ($pairs as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        return $content;
    }
}
