<?php $page = 'approval-steps'; ?>
@extends('layout.mainlayout')
@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Settings</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Administration
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                </div>
                <div class="head-icons ms-2">
                    <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
                {{-- <li class="nav-item">
                    <a class="nav-link " href="{{ url('profile-settings') }}"><i class="ti ti-settings me-2"></i>General
                        Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('bussiness-settings') }}"><i class="ti ti-world-cog me-2"></i>Website
                        Settings</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link active" href="{{ url('salary-settings') }}"><i
                            class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('email-settings') }}"><i class="ti ti-server-cog me-2"></i>System
                        Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('payment-gateways') }}"><i
                            class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
                </li> --}}
                {{-- <li class="nav-item">
                    <a class="nav-link" href="{{ url('custom-css') }}"><i class="ti ti-settings-2 me-2"></i>Other
                        Settings</a>
                </li> --}}
            </ul>
            <div class="row">
                <div class="col-xl-3 theiaStickySidebar">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column list-group settings-list">
                                <a href="{{ route('attendance-settings') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Attendance
                                    Settings</a>
                                <a href="{{ route('approval-steps') }}"
                                    class="d-inline-flex align-items-center rounded active py-2 px-3">Approval Settings</a>
                                <a href="{{ route('leave-type') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Leave Type</a>
                               <a href="{{ route('custom-fields') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Custom Fields</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="border-bottom mb-3 pb-3">
                                <h4>Approval Steps</h4>
                            </div>
                            <!-- Branch Selector -->
                            <div class="mb-4">
                                <label for="branchSelect" class="form-label">Select Branch</label>
                                <select id="branchSelect" class="form-select">
                                    <option value="">Global (All Branches)</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Existing Approval Steps Display -->
                            <div id="existingStepsSection" class="mb-4 d-none">
                                <h4 class="mb-3">Existing Approval Steps</h4>
                                <div id="existingStepsContainer" class="d-flex flex-wrap gap-3"></div>
                            </div>

                            <!-- Steps Section (hidden until branch selected) -->
                            <div id="stepsSection" class="d-none">
                                <form id="approvalStepsForm" action="" method="POST">
                                    <div id="stepsAccordion" class="accordion mb-3"></div>
                                    <div class="d-flex gap-2">
                                        <button type="button" id="addStep" class="btn btn-outline-primary">Add
                                            Step</button>
                                        <button type="submit" class="btn btn-primary">Save Steps</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Hidden Template for Accordion Item -->
                            <template id="stepTemplate">
                                <div class="accordion-item" data-level="__LEVEL__">
                                    <h2 class="accordion-header" id="heading-__LEVEL__">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-__LEVEL__" aria-expanded="false"
                                            aria-controls="collapse-__LEVEL__">
                                            Level __LEVEL__
                                        </button>
                                    </h2>
                                    <div id="collapse-__LEVEL__" class="accordion-collapse collapse"
                                        aria-labelledby="heading-__LEVEL__" data-bs-parent="#stepsAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                <label class="form-label">Approver Type</label>
                                                <select name="steps[__LEVEL__][approver_kind]"
                                                    class="form-select approver-kind">
                                                    <option value="department_head">Department Head</option>
                                                    <option value="user">User</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 approver-user-wrapper d-none">
                                                <label class="form-label">Select User</label>
                                                <select name="steps[__LEVEL__][approver_user_id]"
                                                    class="form-select user-select">
                                                    <option value="">-- Select User --</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger remove-step">Remove
                                                Step</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      @include('layout.partials.footer-company')
    </div>
    <!-- /Page Wrapper -->
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const branchSelect = document.getElementById('branchSelect');
            const existingSection = document.getElementById('existingStepsSection');
            const existingContainer = document.getElementById('existingStepsContainer');
            const stepsSection = document.getElementById('stepsSection');
            const stepsAccordion = document.getElementById('stepsAccordion');
            const addStepBtn = document.getElementById('addStep');
            const templateEl = document.getElementById('stepTemplate');
            const form = document.getElementById('approvalStepsForm');
            let branchUsers = [];
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const authToken = localStorage.getItem('token');

            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: '3000'
            };

            function renderFormStep(step) {
                const level = step.level;
                const clone = document.importNode(templateEl.content, true);
                let html = clone.firstElementChild.outerHTML.replace(/__LEVEL__/g, level);
                stepsAccordion.insertAdjacentHTML('beforeend', html);
                const item = stepsAccordion.querySelector(`.accordion-item[data-level="${level}"]`);
                const kindSelect = item.querySelector('.approver-kind');
                const userWrapper = item.querySelector('.approver-user-wrapper');
                const userSelect = item.querySelector('.user-select');
                kindSelect.value = step.approver_kind;
                userWrapper.classList.toggle('d-none', step.approver_kind !== 'user');
                populateUserSelect(userSelect);
                if (step.approver_kind === 'user' && step.approver_user) {
                    userSelect.value = step.approver_user.id;
                }
            }

            function populateUserSelect(select) {
                select.innerHTML = '<option value="">-- Select User --</option>';
                branchUsers.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    select.appendChild(opt);
                });
            }

            function addStep() {
                const lvl = stepsAccordion.children.length + 1;
                const clone = document.importNode(templateEl.content, true);
                let html = clone.firstElementChild.outerHTML.replace(/__LEVEL__/g, lvl);
                stepsAccordion.insertAdjacentHTML('beforeend', html);
                populateUserSelect(stepsAccordion.lastElementChild.querySelector('.user-select'));
            }

            function renderExistingSteps(steps) {
                if (!steps.length) {
                    existingContainer.innerHTML = '<p>No approval steps defined for this selection.</p>';
                    return;
                }
                existingContainer.innerHTML = steps.map(s => {
                    const type = s.approver_kind === 'user' ? 'User' : 'Department Head';
                    const userInfo = s.approver_user ?
                        `<p class="mb-1"><strong>User:</strong> ${s.approver_user.name}</p>` :
                        '';
                    return `
        <div class="card p-3" style="min-width:200px">
          <span class="badge bg-secondary mb-2">Level ${s.level}</span>
          <p class="mb-1"><strong>Type:</strong> ${type}</p>
          ${userInfo}
        </div>`;
                }).join('');
            }

            branchSelect.addEventListener('change', async () => {
                const branchId = branchSelect.value;
                existingContainer.innerHTML = '';
                stepsAccordion.innerHTML = '';
                existingSection.classList.add('d-none');
                stepsSection.classList.add('d-none');

                try {
                    // 1) fetch users
                    let res = await fetch(
                        `/api/settings/approval-steps/users?branch_id=${encodeURIComponent(branchId)}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );
                    if (!res.ok) throw new Error('Users fetch failed');
                    ({
                        users: branchUsers
                    } = await res.json());

                    // 2) fetch steps
                    res = await fetch(
                        `/api/settings/approval-steps/steps?branch_id=${encodeURIComponent(branchId)}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        }
                    );
                    if (!res.ok) throw new Error('Steps fetch failed');
                    let {
                        steps
                    } = await res.json();

                    // **FILTER OUT GLOBAL WHEN A BRANCH IS SELECTED**
                    if (branchId) {
                        steps = steps.filter(s => !s.is_global);
                    }

                    // 3) render summary
                    renderExistingSteps(steps);
                    existingSection.classList.remove('d-none');

                    // 4) populate form
                    if (steps.length) {
                        steps.forEach(s => renderFormStep(s));
                    } else {
                        addStep();
                    }
                    stepsSection.classList.remove('d-none');
                } catch (err) {
                    console.error(err);
                    toastr.error('Failed to load data.');
                }
            });

            addStepBtn.addEventListener('click', addStep);

            form.addEventListener('submit', async e => {
                e.preventDefault();
                const branchId = branchSelect.value || null;
                const steps = Array.from(stepsAccordion.querySelectorAll('.accordion-item')).map(item =>
                    ({
                        level: +item.dataset.level,
                        approver_kind: item.querySelector('.approver-kind').value,
                        approver_user_id: item.querySelector('.approver-kind').value ===
                            'user' ?
                            item.querySelector('.user-select').value :
                            null
                    }));

                for (let s of steps) {
                    if (s.approver_kind === 'user' && !s.approver_user_id) {
                        toastr.warning(`Please choose a user for Level ${s.level}.`);
                        return;
                    }
                }

                try {
                    const res2 = await fetch('/api/settings/approval-steps/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Authorization': `Bearer ${authToken}`
                        },
                        body: JSON.stringify({
                            branch_id: branchId,
                            steps
                        })
                    });
                    if (!res2.ok) {
                        const err = await res2.json();
                        throw new Error(err.message || 'Save failed');
                    }
                    const json = await res2.json();
                    toastr.success(json.message || 'Saved!');
                    setTimeout(() => window.location.reload(), 800);
                } catch (err) {
                    console.error(err);
                    toastr.error(err.message || 'Unexpected error.');
                }
            });

            stepsAccordion.addEventListener('click', e => {
                if (!e.target.matches('.remove-step')) return;
                e.target.closest('.accordion-item').remove();
                Array.from(stepsAccordion.children).forEach((item, i) => {
                    const lvl = i + 1;
                    item.dataset.level = lvl;
                    item.querySelector('.accordion-header').id = `heading-${lvl}`;
                    const btn = item.querySelector('.accordion-button');
                    btn.dataset.bsTarget = `#collapse-${lvl}`;
                    btn.setAttribute('aria-controls', `collapse-${lvl}`);
                    btn.textContent = `Level ${lvl}`;
                    const collapse = item.querySelector('.accordion-collapse');
                    collapse.id = `collapse-${lvl}`;
                    collapse.setAttribute('aria-labelledby', `heading-${lvl}`);
                    item.querySelector('.approver-kind').name = `steps[${lvl}][approver_kind]`;
                    item.querySelector('.user-select').name = `steps[${lvl}][approver_user_id]`;
                    populateUserSelect(item.querySelector('.user-select'));
                });
            });

            stepsAccordion.addEventListener('change', e => {
                if (!e.target.matches('.approver-kind')) return;
                const wrapper = e.target.closest('.accordion-body')
                    .querySelector('.approver-user-wrapper');
                wrapper.classList.toggle('d-none', e.target.value !== 'user');
            });
        });
    </script>
@endpush
