<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * Backup lokal harian: database.sqlite + unggahan (foto properti, bukti bayar)
 * ke storage/app/backups. Offsite (mis. S3) butuh kredensial cloud terpisah.
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--keep=14 : Jumlah backup terakhir yang disimpan}';

    protected $description = 'Backup database sqlite dan berkas unggahan ke storage/app/backups';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $zipPath = $backupDir.'/kost-antik-'.now()->format('Y-m-d_His').'.zip';

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);

        $dbPath = database_path('database.sqlite');
        if (File::exists($dbPath)) {
            $zip->addFile($dbPath, 'database.sqlite');
        }

        $uploadsPath = storage_path('app/public');
        if (File::isDirectory($uploadsPath)) {
            foreach (File::allFiles($uploadsPath) as $file) {
                $zip->addFile($file->getPathname(), 'uploads/'.$file->getRelativePathname());
            }
        }

        $zip->close();

        $this->info('Backup dibuat: '.basename($zipPath));

        $this->pruneOldBackups($backupDir, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $dir, int $keep): void
    {
        $old = collect(File::glob($dir.'/kost-antik-*.zip'))
            ->sortByDesc(fn (string $path) => filemtime($path))
            ->slice($keep);

        $old->each(fn (string $path) => File::delete($path));

        if ($old->isNotEmpty()) {
            $this->info($old->count().' backup lama dihapus.');
        }
    }
}
