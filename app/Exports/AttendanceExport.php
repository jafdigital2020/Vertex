<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Http\Controllers\DataAccessController;

class AttendanceExport
{
    protected $authUser;
    protected $filters;

    public function __construct($authUser, $filters = [])
    {
        $this->authUser = $authUser;
        $this->filters = $filters;
    }

    /**
     * Convert minutes to hours and minutes format
     */
    private function formatMinutesToHours($minutes)
    {
        if (!$minutes) return '0 hrs 0 mins';

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . ' hrs ' . $remainingMinutes . ' mins';
    }

    public function getData()
    {
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($this->authUser);
        $tenantId = $this->authUser->tenant_id ?? null;

        $query = $accessData['attendances'];

        // Apply filters
        if (!empty($this->filters['dateRange'])) {
            try {
                [$start, $end] = explode(' - ', $this->filters['dateRange']);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();
                $query->whereBetween('attendance_date', [$start, $end]);
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        }

        if (!empty($this->filters['branch'])) {
            $query->whereHas('user.employmentDetail', function ($q) {
                $q->where('branch_id', $this->filters['branch']);
            });
        }

        if (!empty($this->filters['department'])) {
            $query->whereHas('user.employmentDetail', function ($q) {
                $q->where('department_id', $this->filters['department']);
            });
        }

        if (!empty($this->filters['designation'])) {
            $query->whereHas('user.employmentDetail', function ($q) {
                $q->where('designation_id', $this->filters['designation']);
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->with([
            'user.personalInformation',
            'user.employmentDetail.branch',
            'user.employmentDetail.department',
            'user.employmentDetail.designation',
            'shift'
        ])->orderBy('attendance_date', 'desc')->get();
    }

    public function getHeaders()
    {
        return [
            'No.',
            'Employee ID',
            'Employee Name',
            'Branch',
            'Department',
            'Designation',
            'Date',
            'Day',
            'Shift',
            'Status',
            'Clock In',
            'Clock Out',
            'Break In',
            'Break Out',
            'Late (mins)',
            'Work Hours',
            'Night Diff Hours',
            'Undertime (mins)',
            'OT Hours',
        ];
    }

    public function formatRow($attendance, $index)
    {
        $user = $attendance->user;
        $personalInfo = $user->personalInformation;
        $employmentDetail = $user->employmentDetail;

        return [
            $index + 1,
            $employmentDetail->employee_id ?? 'N/A',
            ($personalInfo->last_name ?? '') .
            ($personalInfo->suffix ? ' ' . $personalInfo->suffix : '') .
            ', ' . ($personalInfo->first_name ?? '') .
            ' ' . ($personalInfo->middle_name ?? ''),
            $employmentDetail->branch->name ?? 'N/A',
            $employmentDetail->department->department_name ?? 'N/A',
            $employmentDetail->designation->designation_name ?? 'N/A',
            $attendance->attendance_date ?
                Carbon::parse($attendance->attendance_date)->format('M d, Y') : 'N/A',
            $attendance->attendance_date ?
                Carbon::parse($attendance->attendance_date)->format('l') : 'N/A',
            $attendance->shift->name ?? 'N/A',
            ucfirst($attendance->status ?? 'N/A'),
            $attendance->time_only ?? '-',
            $attendance->time_out_only ?? '-',
            $attendance->break_in_only ?? '-',
            $attendance->break_out_only ?? '-',
            $attendance->total_late_minutes ?? 0,
            $this->formatMinutesToHours($attendance->total_work_minutes ?? 0),
            $this->formatMinutesToHours($attendance->total_night_diff_minutes ?? 0),
            $attendance->total_undertime_minutes ?? 0,
            $this->formatMinutesToHours($attendance->total_overtime_minutes ?? 0),
        ];
    }

    /**
     * Get summary totals
     */
    public function getSummaryTotals($attendances)
    {
        return [
            'total_records' => $attendances->count(),
            'total_present' => $attendances->where('status', 'present')->count(),
            'total_late' => $attendances->where('status', 'late')->count(),
            'total_absent' => $attendances->where('status', 'absent')->count(),
            'total_work_minutes' => $attendances->sum('total_work_minutes'),
            'total_late_minutes' => $attendances->sum('total_late_minutes'),
            'total_undertime_minutes' => $attendances->sum('total_undertime_minutes'),
            'total_overtime_minutes' => $attendances->sum('total_overtime_minutes'),
            'total_night_diff_minutes' => $attendances->sum('total_night_diff_minutes'),
            // Formatted versions
            'total_work_hours_formatted' => $this->formatMinutesToHours($attendances->sum('total_work_minutes')),
            'total_late_hours_formatted' => $this->formatMinutesToHours($attendances->sum('total_late_minutes')),
            'total_undertime_hours_formatted' => $this->formatMinutesToHours($attendances->sum('total_undertime_minutes')),
            'total_overtime_hours_formatted' => $this->formatMinutesToHours($attendances->sum('total_overtime_minutes')),
            'total_night_diff_hours_formatted' => $this->formatMinutesToHours($attendances->sum('total_night_diff_minutes')),
        ];
    }
}
