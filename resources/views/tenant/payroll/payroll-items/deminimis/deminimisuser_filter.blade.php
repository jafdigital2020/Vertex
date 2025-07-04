  @foreach ($userDeminimis as $deminimis)
    <tr>
        <td>
            <div class="form-check form-check-md">
                <input class="form-check-input" type="checkbox">
            </div>
        </td>
        <td>{{ $deminimis->user->personalInformation->first_name }}
            {{ $deminimis->user->personalInformation->last_name }}</td>
        <td class="text-center">{{ ucwords(str_replace('_', ' ', $deminimis->deminimisBenefit->name)) }}</td>
        <td  class="text-center">{{ $deminimis->amount }}</td>
        <td  class="text-center">{{ $deminimis->benefit_date }}</td>
        <td  class="text-center">{{ $deminimis->taxable_excess }}</td>
        <td  class="text-center">
            <span
                class="badge d-inline-flex align-items-center badge-xs
                {{ $deminimis->status === 'inactive' ? 'badge-danger' : 'badge-success' }}">
                <i class="ti ti-point-filled me-1"></i>{{ ucfirst($deminimis->status) }}
            </span>
        </td>
        <td  class="text-center">{{ $deminimis->creator_name }}</td>
        <td  class="text-center">{{ $deminimis->updater_name }}</td>
            @if(in_array('Update',$permission) || in_array('Delete',$permission))
        <td  class="text-center">
            <div class="action-icon d-inline-flex">
                @if(in_array('Update',$permission))
                <a href="#" data-bs-toggle="modal" data-id="{{ $deminimis->id }}"
                    data-deminimis-id="{{ $deminimis->deminimis_benefit_id }}"
                    data-amount="{{ $deminimis->amount }}"
                    data-benefit-date="{{ $deminimis->benefit_date }}"
                    data-taxable-excess="{{ $deminimis->taxable_excess }}"
                    data-status="{{ $deminimis->status }}"
                    data-bs-target="#edit_deminimis_user">
                    <i class="ti ti-edit" title="Edit"></i>
                </a>
                @endif
                    @if(in_array('Delete',$permission))
                <a href="#" class="btn-delete" data-bs-toggle="modal"
                    data-id="{{ $deminimis->id }}"
                    data-deminimis-name="{{ $deminimis->deminimisBenefit->name }}"
                    data-bs-target="#delete_deminimis_user">
                    <i class="ti ti-trash" title="Delete"></i>
                </a>
                @endif
            </div>
        </td>
        @endif
    </tr>
@endforeach