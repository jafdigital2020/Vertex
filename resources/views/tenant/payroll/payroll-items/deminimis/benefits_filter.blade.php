  @foreach ($deMinimis as $dmb)
        <tr>
            <td>
                <div class="form-check form-check-md">
                    <input class="form-check-input" type="checkbox">
                </div>
            </td>
            <td>
                <h6 class="fs-14 fw-medium text-gray-9">
                    {{ ucwords(str_replace('_', ' ', $dmb->name)) }}</h6>
            </td>
            <td class="text-center">{{ number_format($dmb->maximum_amount, 2) }}</td>
            <td class="text-center">
                {{ ucfirst($dmb->frequency) }}
            </td>
            <td class="text-center"> 
            </td>
        </tr>
    @endforeach