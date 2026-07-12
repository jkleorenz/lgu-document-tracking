<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\QRCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateQrCodes extends Command
{
    protected $signature = 'qr:regenerate {--force : Regenerate all QR codes, even if file exists} {--dry-run : Show what would be done without making changes}';
    protected $description = 'Regenerate QR codes for documents missing files in storage/app/qrcodes/';

    public function handle(QRCodeService $qrCodeService): int
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $this->info('Scanning documents for missing QR codes...');
        $this->newLine();

        $documents = Document::withTrashed()
            ->select('id', 'document_number', 'qr_code_path')
            ->orderBy('document_number')
            ->get();

        $total = $documents->count();
        $missing = 0;
        $regenerated = 0;
        $skipped = 0;
        $errors = 0;

        $this->line("Total documents: {$total}");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($documents as $document) {
            $storagePath = storage_path('app/qrcodes/qrcode_' . $document->document_number . '.svg');
            $fileExists = file_exists($storagePath);

            if ($fileExists && !$force) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $missing++;

            if ($dryRun) {
                $bar->advance();
                continue;
            }

            try {
                $qrCodePath = $qrCodeService->generateDocumentQRCode(
                    $document->document_number,
                    $document->id
                );

                $document->update(['qr_code_path' => $qrCodePath]);
                $regenerated++;
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Failed for {$document->document_number}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Summary ===');
        $this->line("Total documents:        {$total}");
        $this->line("Already have QR file:   {$skipped}");
        $this->line("Missing QR file:        {$missing}");

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN - No changes were made. Run without --dry-run to regenerate.');
        } else {
            $this->line("Regenerated:            {$regenerated}");
            if ($errors > 0) {
                $this->error("Errors:                 {$errors}");
            }
        }

        // Cleanup: remove old QR files from public/qrcodes/
        $publicQrDir = public_path('qrcodes');
        if (is_dir($publicQrDir)) {
            $oldFiles = array_diff(scandir($publicQrDir), ['.', '..']);
            $oldCount = count($oldFiles);

            if ($oldCount > 0 && !$dryRun) {
                $this->newLine();
                $this->info("Cleaning up {$oldCount} old QR files from public/qrcodes/...");

                foreach ($oldFiles as $file) {
                    unlink($publicQrDir . '/' . $file);
                }

                rmdir($publicQrDir);
                $this->info('Cleanup complete.');
            } elseif ($oldCount > 0 && $dryRun) {
                $this->newLine();
                $this->warn("Found {$oldCount} old files in public/qrcodes/ (would be removed).");
            }
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
