<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Code - {{ $document->document_number }}</title>
    <style>
        /* Minimal Thermal Printer 58mm Format - Save Paper */
        @page {
            size: 58mm auto;
            margin: 1mm;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            width: 58mm;
            margin: 0 auto;
            padding: 1mm;
            background: white;
        }
        
        .print-content {
            background: white;
            padding: 2mm;
            text-align: center;
        }
        
        .doc-number {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2mm;
            letter-spacing: 0.5px;
        }
        
        .qr-code {
            margin: 0;
        }
        
        .qr-code img {
            width: 45mm;
            height: 45mm;
            display: block;
            margin: 0 auto;
        }
        
        /* Print Preview Buttons */
        .no-print {
            margin-top: 15px;
            padding: 10px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .no-print .btn-print {
            background: #0d6efd;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            min-width: 100px;
            display: inline-block;
        }
        
        .no-print .btn-print:hover {
            background: #0b5ed7;
        }
        
        .no-print .btn-close {
            background: #6c757d;
            min-width: 100px;
        }
        
        .no-print .btn-close:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="print-content">
        <!-- Document Number -->
        <div class="doc-number">{{ $document->document_number }}</div>

        <!-- QR Code -->
        <div class="qr-code">
            @if($document->qr_code_path)
            <img src="{{ asset($document->qr_code_path) }}" alt="QR Code">
            @else
            <p style="font-size: 8pt;">QR Code not available</p>
            @endif
        </div>

        <!-- Print Preview Buttons -->
        <div class="no-print">
            <button onclick="window.print()" class="btn-print">🖨️ Print</button>
            <button onclick="window.close()" class="btn-print btn-close">✖️ Close</button>
        </div>
    </div>

    <script>
        // Auto-print when page loads - goes directly to printing
        window.onload = function() { 
            setTimeout(function() { window.print(); }, 500);
        }
    </script>
</body>
</html>

