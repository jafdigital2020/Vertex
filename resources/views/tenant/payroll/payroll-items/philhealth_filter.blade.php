@foreach ($philHealthContributions as $philhealth)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td class="text-center">{{ number_format($philhealth->min_salary, 2) }}</td>
        <td class="text-center">{{ number_format($philhealth->max_salary, 2) }}</td>
        <td class="text-center">{{ number_format($philhealth->monthly_premium, 2) }}</td>
        <td class="text-center">{{ number_format($philhealth->employee_share, 2) }}</td>
        <td class="text-center">{{ number_format($philhealth->employer_share, 2) }}</td>
        <td class="text-center"></td>
    </tr>
@endforeach