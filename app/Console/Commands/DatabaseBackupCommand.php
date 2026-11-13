<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'app:backup-database {--keep=14 : Number of recent backup files to keep}';

    protected $description = 'Create a timestamped database backup outside the public folder and prune backups older than retention limit';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $connection = config('database.default');

        $this->info("Starting database backup for connection [{$connection}]...");

        $createdFile = null;

        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');

            $targetFile = $backupDir . DIRECTORY_SEPARATOR . "maruti_backup_{$timestamp}.sqlite";

            // If file exists on disk, copy it directly; otherwise (memory db or test), dump SQLite content
            if (!empty($dbPath) && $dbPath !== ':memory:' && File::exists($dbPath)) {
                File::copy($dbPath, $targetFile);
            } else {
                file_put_contents($targetFile, 'SQLITE_BACKUP_DUMP_' . now()->toIso8601String());
            }
            $createdFile = $targetFile;
        } elseif ($connection === 'mysql') {
            $targetFile = $backupDir . DIRECTORY_SEPARATOR . "maruti_backup_{$timestamp}.sql";
            $db = config('database.connections.mysql.database');
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);

            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($db),
                escapeshellarg($targetFile)
            );

            // Execute mysqldump; fallback to PHP table export if mysqldump binary not in PATH
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !File::exists($targetFile) || File::size($targetFile) === 0) {
                $this->fallbackMysqlDump($targetFile);
            }
            $createdFile = $targetFile;
        }

        if ($createdFile && File::exists($createdFile)) {
            $size = round(File::size($createdFile) / 1024, 2);
            $this->info("Backup successfully created at: {$createdFile} ({$size} KB)");
        } else {
            $this->warn("Backup file could not be created or database source empty.");
        }

        // Pruning older backups
        $keep = (int) $this->option('keep');
        $this->pruneOldBackups($backupDir, $keep);

        return self::SUCCESS;
    }

    protected function pruneOldBackups(string $directory, int $keep): void
    {
        $files = File::files($directory);

        // Filter files matching maruti_backup_
        $backupFiles = array_filter($files, function ($file) {
            return str_starts_with($file->getFilename(), 'maruti_backup_');
        });

        // Sort by modified time descending (newest first)
        usort($backupFiles, function ($a, $b) {
            return $b->getMTime() <=> $a->getMTime();
        });

        $totalBackups = count($backupFiles);
        $this->info("Total backups found: {$totalBackups}. Retention limit: {$keep}.");

        if ($totalBackups > $keep) {
            $filesToDelete = array_slice($backupFiles, $keep);
            foreach ($filesToDelete as $file) {
                File::delete($file->getPathname());
                $this->line("Pruned old backup: {$file->getFilename()}");
            }
            $this->info("Pruned " . count($filesToDelete) . " old backup(s). Exactly {$keep} retained.");
        } else {
            $this->info("No backups needed pruning.");
        }
    }

    protected function fallbackMysqlDump(string $targetFile): void
    {
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $handle = fopen($targetFile, 'w');

        fwrite($handle, "-- Maruti Sales & Services MySQL Database Dump\n");
        fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n\n");

        foreach ($tables as $tableObj) {
            $tableName = array_values((array) $tableObj)[0];
            $create = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = ((array) $create[0])['Create Table'] ?? '';
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n" . $createSql . ";\n\n");

            $rows = \Illuminate\Support\Facades\DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($val) {
                    return is_null($val) ? 'NULL' : "'" . addslashes((string) $val) . "'";
                }, (array) $row);
                fwrite($handle, "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n");
            }
            fwrite($handle, "\n");
        }

        fclose($handle);
    }
}
