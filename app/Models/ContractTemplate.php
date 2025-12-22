<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contract_type',
        'content',
        'html_template_path',
        'html_content',
        'is_active',
        'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all contracts using this template
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'template_id');
    }

    /**
     * Get the full path to the PDF template
     */
    public function getPdfTemplatePath()
    {
        if (!$this->html_template_path) {
            return null;
        }

        return public_path($this->html_template_path);
    }

    /**
     * Get the data to be filled in the contract
     */
    public function getContractData($employee, $additionalData = [])
    {
        // Get employee full name
        $fullName = $employee->personalInformation
            ? trim($employee->personalInformation->first_name . ' ' .
                ($employee->personalInformation->middle_name ? $employee->personalInformation->middle_name . ' ' : '') .
                $employee->personalInformation->last_name)
            : $employee->username;

        // Get employment details
        $dateHired = $employee->employmentDetail->date_hired ?? now()->format('Y-m-d');

        // Calculate probationary end date (6 months from date hired)
        $probationaryEndDate = null;
        if ($this->contract_type === 'Probationary' && $dateHired) {
            $probationaryEndDate = \Carbon\Carbon::parse($dateHired)->addMonths(6)->format('F d, Y');
        }

        // Prepare contract data
        $data = [
            'employee_full_name' => $fullName,
            'party_name' => $fullName,
            'date_hired' => \Carbon\Carbon::parse($dateHired)->format('F d, Y'),
            'start_date' => \Carbon\Carbon::parse($dateHired)->format('F d, Y'),
            'probationary_end_date' => $probationaryEndDate ?? '',
            'end_date' => $probationaryEndDate ?? '',
            'employee_id' => $employee->employmentDetail->employee_id ?? '',
            'position' => $employee->designation->name ?? '',
            'department' => $employee->department->name ?? '',
            'current_date' => now()->format('F d, Y'),
        ];

        // Merge with additional data
        return array_merge($data, $additionalData);
    }

    /**
     * Replace template placeholders with actual employee data
     */
    public function generateContract($employee, $additionalData = [])
    {
        // For PDF templates with fillable content, use that
        if ($this->html_template_path && $this->html_content) {
            $content = $this->html_content;
        } elseif ($this->html_template_path) {
            // PDF without fillable content returns null
            return null;
        } else {
            // Text template
            $content = $this->content;
        }

        // Get contract data
        $data = $this->getContractData($employee, $additionalData);

        // Replace placeholders in content
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Check if this template uses a PDF file
     */
    public function isPdfTemplate()
    {
        return !empty($this->html_template_path);
    }

    /**
     * Get the URL to the PDF template
     */
    public function getPdfUrl()
    {
        if (!$this->html_template_path) {
            return null;
        }

        return asset($this->html_template_path);
    }
}
