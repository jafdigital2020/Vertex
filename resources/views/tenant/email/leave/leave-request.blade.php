<!DOCTYPE html>
<html>
<head>
    <title>New Leave Request Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 20px;">
            <h1 style="color: #333333; margin: 0; font-size: 24px;">Leave Request Notification</h1>
        </div>

        <p style="color: #333333; margin-bottom: 20px;">
            Dear {{ optional($approver)->personalInformation->full_name ?? 'Manager' }},
        </p>

        <p style="color: #555555; margin-bottom: 25px;">
            A new leave request has been submitted and requires your attention. Please review the details below:
        </p>

        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 25px 0; border-left: 4px solid #007bff;">
            <h3 style="color: #333333; margin-top: 0; margin-bottom: 15px;">Employee Information</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold; width: 30%;">Employee:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($employee)->personalInformation->full_name ?? 'Unknown Employee' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">Department:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($employee)->employmentDetail->department->department_name ?? 'Not specified' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">Designation:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($employee)->employmentDetail->designation->designation_name ?? 'Not specified' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">Branch:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($employee)->employmentDetail->branch->name ?? 'Not specified' }}</td>
                </tr>
            </table>

            <h3 style="color: #333333; margin-top: 25px; margin-bottom: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">Leave Request Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold; width: 30%;">Leave Type:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($leaveRequest)->leaveType->name ?? 'Not specified' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">Start Date:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($leaveRequest)->start_date ? date('F j, Y', strtotime($leaveRequest->start_date)) : 'Not specified' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold;">End Date:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($leaveRequest)->end_date ? date('F j, Y', strtotime($leaveRequest->end_date)) : 'Not specified' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #555555; font-weight: bold; vertical-align: top;">Reason:</td>
                    <td style="padding: 8px 0; color: #333333;">{{ optional($leaveRequest)->reason ?? 'No reason provided' }}</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/leave/leave-admin') }}" style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                Review Request
            </a>
        </div>

        <p style="color: #666666; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; font-size: 14px;">
            For any questions or technical issues, please contact the HR department.
        </p>

        <div style="margin-top: 30px; color: #666666; font-size: 14px;">
            <p style="margin: 0;">Best regards,</p>
        </div>
    </div>
</body>
</html>
