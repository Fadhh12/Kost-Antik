<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupDatabaseTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/backups'));

        parent::tearDown();
    }

    public function test_backup_command_creates_a_zip_containing_the_database(): void
    {
        $this->artisan('backup:run')->assertSuccessful();

        $backups = File::glob(storage_path('app/backups/kost-antik-*.zip'));
        $this->assertNotEmpty($backups);

        $zip = new ZipArchive;
        $zip->open(end($backups));
        $this->assertNotFalse($zip->locateName('database.sqlite'));
        $zip->close();
    }

    public function test_backup_command_prunes_old_backups_beyond_the_keep_limit(): void
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        foreach (range(1, 3) as $i) {
            $path = "{$dir}/kost-antik-manual-{$i}.zip";
            File::put($path, 'dummy');
            touch($path, time() - $i * 100);
        }

        $this->artisan('backup:run', ['--keep' => 2])->assertSuccessful();

        $this->assertCount(2, File::glob("{$dir}/kost-antik-*.zip"));
    }
}
