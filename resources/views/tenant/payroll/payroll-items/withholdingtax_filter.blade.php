    @foreach ($withholdingTaxes as $tax)
            <tr>
                <td>
                    <div class="form-check form-check-md">
                        <input class="form-check-input" type="checkbox">
                    </div>
                </td>
                <td class="text-center">{{ ucfirst($tax->frequency) }}</td>
                <td class="text-center">{{ number_format($tax->range_from, 2) }}</td>
                <td class="text-center">{{ $tax->range_to !== null ? number_format($tax->range_to, 2) : '—' }}</td>
                <td class="text-center">{{ number_format($tax->fix, 2) }}</td>
                <td class="text-center">{{ number_format($tax->rate, 2) }}%</td>
                <td class="text-center"></td>
            </tr>
        @endforeach