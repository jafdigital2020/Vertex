@foreach ($branches as $branch)
                    @php
                        $logoPath = $branch->branch_logo ?? null;
                        $logoUrl = $logoPath
                            ? asset('storage/' . $logoPath)
                            : asset('build/img/company/company-13.svg');
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="form-check form-check-md">
                                        
                                    </div>
                                    <div>
                                        <a href="#"
                                            class="avatar avatar-xl avatar-rounded online border rounded-circle">
                                            <img src="{{ $logoUrl }}" class="img-fluid h-auto w-auto" alt="Branch Logo"
                                                onerror="this.onerror=null; this.src='{{ asset('build/img/company/company-13.svg') }}';">
                                        </a>
                                    </div>
                                   @if (in_array('Update', $permission) ||in_array('Delete', $permission))
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-sm rounded-circle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end p-3">
                                             @if (in_array('Update', $permission))
                                            <li>
                                                <a class="dropdown-item rounded-1" href="javascript:void(0);"
                                                    data-bs-toggle="modal" data-bs-target="#edit_branch"
                                                    data-id="{{ $branch->id }}"
                                                    data-branch-logo="{{ $branch->branch_logo ?? '' }}"
                                                    data-name="{{ $branch->name }}"
                                                    data-branch-type="{{ $branch->branch_type }}"
                                                    data-sss-type="{{ $branch->sss_contribution_type }}"
                                                    data-philhealth-type="{{ $branch->philhealth_contribution_type }}"
                                                    data-pagibig-type="{{ $branch->pagibig_contribution_type }}"
                                                    data-withholding-type="{{ $branch->withholding_tax_type }}"
                                                    data-days-per-year="{{ $branch->worked_days_per_year }}"
                                                    data-custom-days="{{ $branch->custom_worked_days }}"
                                                    data-fixed-sss="{{ $branch->fixed_sss_amount }}"
                                                    data-fixed-philhealth="{{ $branch->fixed_philhealth_amount }}"
                                                    data-fixed-pagibig="{{ $branch->fixed_pagibig_amount }}"
                                                    data-fixed-withholding="{{ $branch->fixed_withholding_amount }}"
                                                    data-contact-number="{{ $branch->contact_number }}"
                                                    data-location="{{ $branch->location }}"
                                                    data-salary-type="{{ $branch->salary_type }}"
                                                    data-basic-salary="{{ $branch->basic_salary }}"
                                                    data-salary-computation-type="{{ $branch->salary_computation_type }}"><i
                                                        class="ti ti-edit me-1"></i>Edit</a>
                                            </li>
                                            @endif 
                                            @if (in_array('Delete', $permission))
                                            <li>
                                                <a class="dropdown-item rounded-1 btn-delete" href="javascript:void(0);"
                                                    data-bs-toggle="modal" data-bs-target="#delete_branch"
                                                    data-id="{{ $branch->id }}"
                                                    data-branch-name="{{ $branch->name }}"><i
                                                        class="ti ti-trash me-1"></i>Delete</a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                <div class="text-center mb-3">
                                    <h6 class="mb-1"><a href="{{ url('company-details') }}">{{ $branch->name }}</a>
                                    </h6>
                                </div>
                                <div class="d-flex flex-column">
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i
                                            class="{{ $branch->branch_type === 'main' ? 'ti ti-star-filled text-warning' : 'ti ti-star text-gray-5' }} me-2"></i>
                                        {{ ucfirst($branch->branch_type) }}
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i class="ti ti-phone text-gray-5 me-2"></i>
                                        {{ $branch->contact_number }}
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center">
                                        <i class="ti ti-map-pin text-gray-5 me-2"></i>
                                        {{ $branch->location }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
@endforeach