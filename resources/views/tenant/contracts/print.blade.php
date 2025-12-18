<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract - {{ $contract->user->personalInformation->first_name ?? '' }}
        {{ $contract->user->personalInformation->last_name ?? '' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            margin: 40px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .contract-content {
            text-align: justify;
            white-space: pre-wrap;
            margin: 30px 0;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-box {
            margin-top: 40px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin-top: 50px;
        }

        table {
            width: 100%;
            margin-top: 20px;
        }

        .info-table td {
            padding: 5px;
        }

        @media print {
            body {
                margin: 0;
                padding: 20px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px;">
            Print Contract
        </button>
        <button onclick="window.close()"
            style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 4px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>{{ strtoupper($contract->contract_type) }} EMPLOYMENT CONTRACT</h1>
    </div>

    <table class="info-table">
        <tr>
            <td width="30%"><strong>Employee Name:</strong></td>
            <td>{{ $contract->user->personalInformation->first_name ?? '' }}
                {{ $contract->user->personalInformation->middle_name ?? '' }}
                {{ $contract->user->personalInformation->last_name ?? $contract->user->username }}</td>
        </tr>
        <tr>
            <td><strong>Employee ID:</strong></td>
            <td>{{ $contract->user->employmentDetail->employee_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Position:</strong></td>
            <td>{{ $contract->user->designation->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Department:</strong></td>
            <td>{{ $contract->user->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Contract Type:</strong></td>
            <td>{{ $contract->contract_type }}</td>
        </tr>
        <tr>
            <td><strong>Start Date:</strong></td>
            <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('F d, Y') }}</td>
        </tr>
        @if($contract->end_date)
            <tr>
                <td><strong>End Date:</strong></td>
                <td>{{ \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') }}</td>
            </tr>
        @endif
    </table>

    @if($contract->template && $contract->template->isPdfTemplate())
        <!-- Redirect to PDF for PDF templates -->
        <script>
            window.location.href = "{{ $contract->template->getPdfUrl() }}";
        </script>
        <div style="text-align: center; margin: 50px;">
            <p>Redirecting to PDF template...</p>
            <p>If you are not redirected, <a href="{{ $contract->template->getPdfUrl() }}" target="_blank">click here</a>.
            </p>
        </div>
    @else
        <!-- Display text content for non-PDF templates -->
        <div class="contract-content">
            {{ $contract->content }}
        </div>

        <div class="signature-section">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; text-align: center;">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <p style="margin-top: 5px;">
                                <strong>Employee Signature</strong><br>
                                {{ $contract->user->personalInformation->first_name ?? '' }}
                                {{ $contract->user->personalInformation->last_name ?? $contract->user->username }}<br>
                                Date: _______________
                            </p>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center;">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <p style="margin-top: 5px;">
                                <strong>Authorized Representative</strong><br>
                                @if($contract->signedBy)
                                    {{ $contract->signedBy->personalInformation->first_name ?? '' }}
                                    {{ $contract->signedBy->personalInformation->last_name ?? $contract->signedBy->username }}<br>
                                    @if($contract->signed_date)
                                        Date: {{ \Carbon\Carbon::parse($contract->signed_date)->format('F d, Y') }}
                                    @else
                                        Date: _______________
                                    @endif
                                @else
                                    Date: _______________
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 50px; font-size: 12px; text-align: center; color: #666;">
            <p>This is a legally binding contract. Please read carefully before signing.</p>
        </div>
    @endif