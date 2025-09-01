<!DOCTYPE html>
<html>
<head>
    <title>Leave Request Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 20px;">
            <h1 style="color: #333333; margin: 0; font-size: 24px;">Leave Request Update</h1>
        </div>

        <p style="color: #333333; margin-bottom: 20px;">
            Dear {{ $requester->personalInformation->full_name ?? '-' }},
        </p>

        <p style="color: #555555; margin-bottom: 25px;">
            Your leave request has been processed. Please review the updated status and details below:
        </p>

        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 25px 0; border-left: 4px solid #007bff;">
            <h3 style="color: #333333; margin-top: 0; margin-bottom: 15px;">Leave Request Details</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold; width: 30%;">Leave Type:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($leave->leaveType)->name ?? 'Not specified' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">Start Date:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ \Illuminate\Support\Carbon::parse($leave->start_date)->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">End Date:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ \Illuminate\Support\Carbon::parse($leave->end_date)->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">Status:</td>
                    <td style="padding: 8px 0;">
                        <span style="display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; color: #fff; font-weight: bold; text-transform: uppercase;
                            @if($status == 'approved') background-color: #16a34a;
                            @elseif($status == 'rejected') background-color: #dc2626;
                            @else background-color: #f59e0b; @endif">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">Processed by:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ $actedBy->personalInformation->full_name ?? '-' }}</td>
                </tr>
            </table>

            @if(!empty($comment))
            <h3 style="color: #333333; margin-top: 25px; margin-bottom: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">Approver's Comment</h3>
            <p style="color: #333333; margin: 0; padding: 10px; background-color: #ffffff; border-radius: 4px; border: 1px solid #e9ecef;">
                {{ $comment }}
            </p>
            @endif
        </div>

        <p style="color: #666666; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; font-size: 14px;">
            For any questions or concerns about your leave request, please contact the HR department.
        </p>

        <div style="margin-top: 30px; color: #666666; font-size: 14px;">
            <p style="margin: 0;">Best regards,</p>
        </div>
    </div>
</body>
</html>
