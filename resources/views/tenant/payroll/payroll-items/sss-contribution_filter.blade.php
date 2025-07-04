 @foreach ($sssContributions as $contribution)
        <tr>
            <td>
                <div class="form-check form-check-md">
                    <input class="form-check-input" type="checkbox">
                </div>
            </td>
            <td class="text-center">{{ number_format($contribution->range_from, 2) }} -
                {{ number_format($contribution->range_to, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->monthly_salary_credit, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->employer_regular_ss, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->employer_mpf, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->employer_ec, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->employer_total, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->employee_regular_ss, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->employee_mpf, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->employee_total, 2) }}</td>
            <td class="text-center">{{ number_format($contribution->total_contribution, 2) }}</td>
            <td class="text-center"></td>
        </tr>
    @endforeach