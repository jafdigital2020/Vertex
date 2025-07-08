  @foreach ($earningTypes as $earning)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td>
            <h6 class="fs-14 fw-medium text-gray-9">{{ $earning->name }}</h6>
        </td>
        <td class="text-center">{{ ucfirst($earning->calculation_method) }}</td>
        <td class="text-center">{{ number_format($earning->default_amount, 2) }}</td>
        <td class="text-center">{{ $earning->is_taxable ? 'Yes' : 'No' }}</td>
        <td class="text-center">{{ $earning->creator_name }}</td>
        <td class="text-center">{{ $earning->updater_name }}</td>
        @if(in_array('Update',$permission) || in_array('Delete',$permission))
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if(in_array('Update',$permission))
                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_earning"
                    data-id="{{ $earning->id }}" data-name="{{ $earning->name }}"
                    data-calculation-method="{{ $earning->calculation_method }}"
                    data-default-amount="{{ $earning->default_amount }}"
                    data-is-taxable="{{ $earning->is_taxable ? '1' : '0' }}"
                    data-all-employees="{{ $earning->apply_to_all_employees ? '1' : '0' }}"
                    data-description="{{ $earning->description }}">
                    <i class="ti ti-edit"></i>
                </a>
                @endif
                @if(in_array('Delete',$permission))
                <a href="#" class="btn-delete" data-bs-toggle="modal"
                    data-bs-target="#delete_earning" data-id="{{ $earning->id }}"
                    data-name="{{ $earning->name }}"><i class="ti ti-trash"></i></a>
                @endif
            </div>
        </td>
        @endif
    </tr>
@endforeach