<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR code for a document
     * 
     * @param string $documentNumber - The unique document identifier
     * @param int $documentId - The document ID
     * @return string - Path to the generated QR code image (relative to storage/app)
     */
    public function generateDocumentQRCode($documentNumber, $documentId)
    {
        // Create QR code data (URL to scan page with document info)
        $qrData = url('/scan?document=' . $documentNumber);
        
        // Generate QR code image using SVG (no ImageMagick required)
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrData);
        
        // Create filename
        $filename = 'qrcode_' . $documentNumber . '.svg';
        $path = 'qrcodes/' . $filename;
        
        // Ensure directory exists in storage
        $storagePath = storage_path('app/' . $path);
        $dir = dirname($storagePath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Save QR code to storage (not public)
        file_put_contents($storagePath, $qrCode);
        
        return $path;
    }

    /**
     * Generate printable QR code with document information
     * 
     * @param object $document - The document model instance
     * @return string - Path to the printable QR code
     */
    public function generatePrintableQRCode($document)
    {
        $qrData = url('/scan?document=' . $document->document_number);
        
        // Generate larger QR code for printing using SVG (no ImageMagick required)
        $qrCode = QrCode::format('svg')
            ->size(400)
            ->margin(3)
            ->errorCorrection('H')
            ->generate($qrData);
        
        $filename = 'printable_qrcode_' . $document->document_number . '.svg';
        $path = 'qrcodes/' . $filename;
        
        // Ensure directory exists in storage
        $storagePath = storage_path('app/' . $path);
        $dir = dirname($storagePath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($storagePath, $qrCode);
        
        return $path;
    }

    /**
     * Delete QR code file
     * 
     * @param string $path - Path to the QR code file (relative to storage/app)
     * @return bool
     */
    public function deleteQRCode($path)
    {
        $fullPath = storage_path('app/' . $path);

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
}

