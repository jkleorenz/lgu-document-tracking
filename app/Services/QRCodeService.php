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
     * @return string - Path to the generated QR code image
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
        
        // Ensure directory exists
        if (!file_exists(public_path('qrcodes'))) {
            mkdir(public_path('qrcodes'), 0755, true);
        }
        
        // Save QR code to public directory
        file_put_contents(public_path($path), $qrCode);
        
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
        
        // Ensure directory exists
        if (!file_exists(public_path('qrcodes'))) {
            mkdir(public_path('qrcodes'), 0755, true);
        }
        
        file_put_contents(public_path($path), $qrCode);
        
        return $path;
    }

    /**
     * Delete QR code file
     * 
     * @param string $path - Path to the QR code file
     * @return bool
     */
    public function deleteQRCode($path)
    {
        $fullPath = public_path($path);
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
}

