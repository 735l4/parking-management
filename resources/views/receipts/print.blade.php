<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Receipt - {{ $ticket->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 10px;
            max-width: 300px;
            margin: 0 auto;
        }

        .receipt {
            border: 1px dashed #ccc;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .business-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .business-info {
            font-size: 11px;
            color: #333;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 5px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }

        .row .label {
            font-weight: normal;
        }

        .row .value {
            font-weight: bold;
            text-align: right;
        }

        .total {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            padding: 10px;
            background: #f5f5f5;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 10px;
            color: #666;
        }

        .rates-section {
            margin-top: 10px;
        }

        .rates-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .print-button {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 20px 0;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }

        .print-button:hover {
            background: #1d4ed8;
        }

        @media print {
            body {
                padding: 0;
            }

            .receipt {
                border: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Receipt
    </button>

    <div class="receipt">
        <div class="header">
            <div class="business-name">{{ $settings->business_name }}</div>
            @if($settings->address)
                <div class="business-info">{{ $settings->address }}</div>
            @endif
            @if($settings->phone_number)
                <div class="business-info">Tel: {{ $settings->phone_number }}</div>
            @endif
            @if($settings->pan_number)
                <div class="business-info">PAN: {{ $settings->pan_number }}</div>
            @endif
        </div>

        <div class="divider"></div>

        @if($ticket->hasExited())
            <div class="title">PARKING RECEIPT</div>
        @else
            <div class="title">PARKING TICKET</div>
        @endif

        <div class="divider"></div>

        <div class="row">
            <span class="label">Ticket No.</span>
            <span class="value">{{ $ticket->ticket_number }}</span>
        </div>

        <div class="row">
            <span class="label">Vehicle No.</span>
            <span class="value">{{ $ticket->vehicle_no }}</span>
        </div>

        <div class="row">
            <span class="label">Type</span>
            <span class="value">{{ $ticket->vehicleType->name }}</span>
        </div>

        @if($ticket->phone_number)
            <div class="row">
                <span class="label">Phone</span>
                <span class="value">{{ $ticket->phone_number }}</span>
            </div>
        @endif

        <div class="divider"></div>

        <div class="row">
            <span class="label">In Time</span>
            <span class="value">{{ \App\Helpers\NepaliHelper::formatBSDateTime($ticket->check_in) }}</span>
        </div>

        @if($ticket->check_out)
            <div class="row">
                <span class="label">Out Time</span>
                <span class="value">{{ \App\Helpers\NepaliHelper::formatBSDateTime($ticket->check_out) }}</span>
            </div>

            <div class="row">
                <span class="label">Duration</span>
                <span class="value">{{ $ticket->duration_for_display }}</span>
            </div>

            <div class="divider"></div>

            <div class="total">
                TOTAL: रु. {{ number_format($ticket->total_price, 2) }}
            </div>
        @else
            <div class="divider"></div>

            <div class="rates-section">
                <div class="rates-title">RATES</div>
                <div class="row">
                    <span class="label">Hourly</span>
                    <span class="value">रु. {{ number_format($ticket->vehicleType->hourly_rate, 2) }}</span>
                </div>
                <div class="row">
                    <span class="label">Minimum</span>
                    <span class="value">रु. {{ number_format($ticket->vehicleType->minimum_charge, 2) }}</span>
                </div>
            </div>
        @endif

        <div class="divider"></div>

        <div class="footer">
            @if($ticket->hasExited())
                Thank you for parking with us!
            @else
                Please keep this ticket safe<br>for vehicle release.
            @endif
            <br>
            {{ now()->format('Y-m-d H:i:s') }}
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional - comment out if not desired)
        window.onload = function() {
            // Uncomment the line below to auto-print
            // window.print();
        };
    </script>
</body>
</html>
