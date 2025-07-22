@foreach ($deductionTypes as $deduction)
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-md">
                                                <input class="form-check-input" type="checkbox">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fs-14 fw-medium text-gray-9">{{ $deduction->name }}</h6>
                                        </td>
                                        <td class="text-center">{{ ucfirst($deduction->calculation_method) }}</td>
                                        <td class="text-center">{{ number_format($deduction->default_amount, 2) }}</td>
                                        <td class="text-center">{{ $deduction->is_taxable ? 'Yes' : 'No' }}</td>
                                        <td class="text-center">{{ $deduction->creator_name }}</td>
                                        <td class="text-center">{{ $deduction->updater_name }}</td>
                                        @if(in_array('Update',$permission) || in_array('Delete',$permission))
                                        <td class="text-center">
                                             @if(in_array('Update',$permission))
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#edit_deduction"
                                                    data-id="{{ $deduction->id }}" data-name="{{ $deduction->name }}"
                                                    data-calculation-method="{{ $deduction->calculation_method }}"
                                                    data-default-amount="{{ $deduction->default_amount }}"
                                                    data-is-taxable="{{ $deduction->is_taxable ? '1' : '0' }}"
                                                    data-all-employees="{{ $deduction->apply_to_all_employees ? '1' : '0' }}"
                                                    data-description="{{ $deduction->description }}">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                @endif
                                                 @if( in_array('Delete',$permission))
                                                <a href="#" class="btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#delete_deduction" data-id="{{ $deduction->id }}"
                                                    data-name="{{ $deduction->name }}"><i class="ti ti-trash"></i></a>
                                                    @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach