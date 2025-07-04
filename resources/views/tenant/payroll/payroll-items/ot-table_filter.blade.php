 @foreach ($ots as $ot)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td >{{ ucwords(str_replace('_', ' ', $ot->type)) }}</td>
        <td class="text-center">{{ $ot->normal }}</td>
        <td class="text-center">{{ $ot->overtime }}</td>
        <td class="text-center">{{ $ot->night_differential }}</td>
        <td class="text-center">{{ $ot->night_differential_overtime }}</td>
        <td class="text-center"></td>
    </tr>
@endforeach