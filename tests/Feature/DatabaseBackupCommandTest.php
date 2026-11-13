<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseBackupCommandTest extends TestCase
{
    protected string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupDir = storage_path('app/backups');

        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up created test backups
        if (File::isDirectory($this->backupDir)) {
            $files = File::files($this->backupDir);
            foreach ($files as $file) {
                if (str_starts_with($file->getFilename(), 'maruti_backup_test_')) {
                    File::delete($file->getPathname());
                }
            }
        }

        parent::tearDown();
    }

    public function test_backup_command_creates_backup_outside_public_folder(): void
    {
        $backupDir = storage_path('app' . DIRECTORY_SEPARATOR . 'backups');
        $this->artisan('app:backup-database')->assertExitCode(0);

        $this->assertDirectoryExists($backupDir);
        $this->assertStringNotContainsString('public', $backupDir);

        $files = File::files($backupDir);
        $this->assertNotEmpty($files, "No files found in {$backupDir}");

        $hasMarutiBackup = false;
        foreach ($files as $file) {
            if (str_starts_with($file->getFilename(), 'maruti_backup_')) {
                $hasMarutiBackup = true;
                $this->assertGreaterThan(0, $file->getSize());
                break;
            }
        }

        $this->assertTrue($hasMarutiBackup, 'Database backup file was not created.');
    }

    public function test_backup_command_prunes_old_backups_beyond_retention_limit(): void
    {
        // Create 18 dummy backup files
        for ($i = 1; $i <= 18; $i++) {
            $filename = sprintf('maruti_backup_test_%02d.sqlite', $i);
            $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($path, "TEST_BACKUP_CONTENT_{$i}");
            touch($path, time() - (20 - $i) * 3600); // Set staggered modification timestamps
        }

        // Run backup command keeping last 14
        $this->artisan('app:backup-database', ['--keep' => 14])
            ->assertExitCode(0);

        // Count remaining test files
        $remainingTestFiles = array_filter(File::files($this->backupDir), function ($file) {
            return str_starts_with($file->getFilename(), 'maruti_backup_test_');
        });

        // The 18 test files should have been pruned to at most 13 or 14
        $this->assertLessThanOrEqual(14, count($remainingTestFiles));
    }
}
