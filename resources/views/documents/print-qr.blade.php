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
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            padding: 15px 10px;
        }
        
        .btn-print {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #0d6efd;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            min-width: 100px;
        }
        
        .btn-print:hover {
            background: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn-print:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .btn-close {
            background: #6c757d;
        }
        
        .btn-close:hover {
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
        // Optional: Auto-print when page loads
        // window.onload = function() { 
        //     setTimeout(function() { window.print(); }, 500);
        // }
    </script>
</body>
</html>

