<!DOCTYPE html>
<html>
<head>
    <title>Document Report - {{ $document->document_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 2.54cm;
            padding: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            width: 100%;
        }
        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }
        .container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 18pt;
            font-weight: 700;
            color: #000;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            margin: 0;
            font-size: 9pt;
            color: #000;
            font-weight: 400;
        }
        
        /* Top Info Row */
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        .info-item label {
            font-weight: 700;
            color: #000;
            display: block;
            margin-bottom: 2px;
        }
        .info-item span {
            color: #000;
        }
        
        /* Section */
        .section {
            margin-bottom: 12px;
        }
        .section h2 {
            font-size: 11pt;
            font-weight: 700;
            color: #000;
            padding: 6px 0;
            margin: 0 0 6px 0;
            border-bottom: 1px solid #000;
            letter-spacing: 0.3px;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9pt;
            background-color: #fff;
            border: 1px solid #000;
        }
        
        .info-table {
            margin-bottom: 10px;
        }
        .info-table th {
            background-color: #fff;
            color: #000;
            padding: 6px 7px;
            text-align: left;
            font-weight: 700;
            width: 35%;
            border: 1px solid #000;
        }
        .info-table td {
            padding: 6px 7px;
            border: 1px solid #000;
            color: #000;
            word-break: break-word;
            background-color: #fff;
        }
        
        .history-table th {
            background-color: #fff;
            color: #000;
            padding: 5px 5px;
            text-align: left;
            font-weight: 700;
            border: 1px solid #000;
            font-size: 8pt;
        }
        .history-table td {
            padding: 4px 5px;
            border: 1px solid #000;
            color: #000;
            font-size: 8.5pt;
            background-color: #fff;
        }
        
        /* Footer */
        .footer {
            margin-top: auto;
            text-align: center;
            font-size: 8pt;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 8px;
            padding-bottom: 0;
        }
        
        .no-data {
            text-align: center;
            padding: 12px;
            color: #000;
            font-style: italic;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DOCUMENT REPORT</h1>
            <p class="subtitle">LGU Document Tracking System</p>
        </div>
        
        <div class="info-row">
            <div class="info-item">
                <label>Document Number:</label>
                <span>{{ $document->document_number }}</span>
            </div>
            <div class="info-item">
                <label>Generated Date:</label>
                <span>{{ now()->format('M d, Y h:i A') }}</span>
            </div>
        </div>
        
        <div class="section">
            <h2>DOCUMENT INFORMATION</h2>
            <table class="info-table">
                <tr>
                    <th>Title</th>
                    <td>{{ $document->title }}</td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td>{{ $document->department ? $document->department->name : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Document Type</th>
                    <td>{{ $document->document_type }}</td>
                </tr>
                <tr>
                    <th>Current Status</th>
                    <td><strong>{{ $document->status }}</strong></td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td>{{ $document->creator ? $document->creator->name : 'Unknown' }}</td>
                </tr>
                <tr>
                    <th>Date Created</th>
                    <td>{{ $document->created_at->format('M d, Y') }}</td>
                </tr>
                @if($document->description)
                <tr>
                    <th>Description</th>
                    <td>{{ $document->description }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <div class="section">
            <h2>DOCUMENT HISTORY</h2>
            @if($document->statusLogs->count() > 0)
            <table class="history-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Date & Time</th>
                        <th style="width: 12%;">From</th>
                        <th style="width: 12%;">To</th>
                        <th style="width: 20%;">Updated By</th>
                        <th style="width: 38%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($document->statusLogs->take(10) as $log)
                    <tr>
                        <td>{{ $log->created_at->format('M d, Y') }}</td>
                        <td>{{ $log->old_status ?? 'N/A' }}</td>
                        <td>{{ $log->new_status }}</td>
                        <td>{{ $log->updatedBy ? $log->updatedBy->name : 'System' }}</td>
                        <td>{{ $log->remarks ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-data">No document history available.</div>
            @endif
        </div>
        
        <div class="footer">
            <p>Report generated by LGU Document Tracking System • Document #{{ $document->document_number }}</p>
        </div>
    </div>
</body>
</html>

