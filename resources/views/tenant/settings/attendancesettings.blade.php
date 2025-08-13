<?php $page = 'attendance-settings'; ?>
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
                                    class="d-inline-flex align-items-center rounded active py-2 px-3">Attendance
                                    Settings</a>
                                <a href="{{ route('approval-steps') }}"
                                    class="d-inline-flex align-items-center rounded py-2 px-3">Approval Settings</a>
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
                                <h4>Attendance Settings</h4>
                            </div>
                            <div>
                                <form id="attendanceSettingsForm">
                                    {{-- Geotagging --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Geotagging
                                            </h5>
                                            <p>Enable Geotagging</p>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-md form-switch me-2">
                                                <input class="form-check-input me-2" name="geotagging_enabled"
                                                    id="geotaggingEnabled" type="checkbox" role="switch">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Geofencing --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Geofencing
                                            </h5>
                                            <p>Enable Geofencing</p>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-md form-switch me-2">
                                                <input class="form-check-input me-2" name="geofencing_enabled"
                                                    id="geofencingEnabled" type="checkbox" role="switch">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Geofence Radius and Locaions --}}
                                    <div id="geofencingRadiusSection" class="d-none">
                                        <!-- Geofence Buffer -->
                                        <div
                                            class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                            <div class="mb-3">
                                                <h5 class="fw-medium d-flex align-items-center mb-1">Geofence Buffer</h5>
                                                <p>This will add a temporary radius to the geofence if the accuracy is not
                                                    working properly.</p>
                                            </div>
                                            <div class="mb-3">
                                              <input type="text" class="form-control" name="geofence_buffer" id="geofenceBuffer" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                            </div>
                                        </div>

                                        <!-- Geofence Allowing Geotagging -->
                                        <div
                                            class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                            <div class="mb-3">
                                                <h5 class="fw-medium d-flex align-items-center mb-1">Geofence allowing
                                                    Geotagging</h5>
                                                <p>Enabling this option allows geotagging when the geofencing location
                                                    detection has a weak signal.</p>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-check-md form-switch me-2">
                                                    <input class="form-check-input me-2" name="geofence_allowed_geotagging"
                                                        id="geofenceAllowedGeotagging" type="checkbox" role="switch">
                                                </div>
                                            </div>
                                        </div>


                                        <!-- Location-->
                                        <div
                                            class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                            <div class="mb-3">
                                                <h5 class="fw-medium d-flex align-items-center mb-1">Geofence Location</h5>
                                                <p>Create and view geofence locations.</p>
                                            </div>
                                            <div class="mb-3">
                                                <a href="{{ route('geofence-settings') }}"
                                                    class="btn btn-dark">Manage</a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Allow Multiple Clock-ins --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Allow Multiple Clock-ins
                                            </h5>
                                            <p>Enable multiple clock-ins per day.</p>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-md form-switch me-2">
                                                <input class="form-check-input me-2" name="allow_multiple_clock_ins"
                                                    id="allowMultipleClockIns" type="checkbox" role="switch">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Require Photo clock-in --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Require Photo Capture
                                            </h5>
                                            <p>This process will require the capture of photo at both the time of clock-in
                                                and
                                                clock-out.</p>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-md form-switch me-2">
                                                <input class="form-check-input me-2" name="require_photo_capture"
                                                    id="requirePhotoCapture" type="checkbox" role="switch">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Enable Break Hours Button --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">Break Hours</h5>
                                            <p>Enable break hours.</p>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-md form-switch me-2">
                                                <input class="form-check-input me-2" name="enable_break_hour_buttons"
                                                    id="enableBreakHourButtons" type="checkbox" role="switch">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Break Hours Lunch and Coffee --}}
                                    <div id="breakOptionsSection" class="d-none">
                                        <!-- Lunch Break -->
                                        <div
                                            class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                            <div class="mb-3">
                                                <h5 class="fw-medium d-flex align-items-center mb-1">Lunch Break (In
                                                    Minutes)
                                                </h5>
                                                <p>Lunch break in minutes.</p>
                                            </div>
                                            <div class="mb-3">
                                               <input type="text" class="form-control" name="lunch_break_limit"
                                                 id="lunchBreakLimit" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                            </div>
                                        </div>

                                        <!-- Coffee Break -->
                                        <div
                                            class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                            <div class="mb-3">
                                                <h5 class="fw-medium d-flex align-items-center mb-1">Coffee Break (In
                                                    Minutes)
                                                </h5>
                                                <p>Coffee break in minutes.</p>
                                            </div>
                                            <div class="mb-3">
                                               <input type="text" class="form-control" name="coffee_break_limit"
                                                id="coffeeBreakLimit"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                                            </div>
                                        </div>
                                    </div>

                                    {{-- Allowed Clocked In Rest Day --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Allow Clock-in Rest Day
                                            </h5>
                                            <p>Enable clock-in on rest day.</p>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-md form-switch me-2">
                                                <input class="form-check-input me-2" name="rest_day_time_in_allowed"
                                                    id="restDayTimeInAllowed" type="checkbox" role="switch">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Grace Period --}}
                                    {{-- <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Grace Period
                                            </h5>
                                            <p>Grace period in minutes.</p>
                                        </div>
                                        <div class="mb-3">
                                            <input type="text" class="form-control" name="grace_period"
                                                id="gracePeriod">
                                        </div>
                                    </div> --}}

                                    {{-- Maxium Allowed Hours --}}
                                    {{-- <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Maximum Allowed Hours
                                            </h5>
                                            <p>The maximum number of work hours allowed per day.</p>
                                        </div>
                                        <div class="mb-3">
                                            <input type="text" class="form-control" name="maximum_allowed_hours"
                                                id="maximumAllowedHours">
                                        </div>
                                    </div> --}}

                                    {{-- Late Status Box --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Late Status Box
                                            </h5>
                                            <p>Each late entry will trigger a popup to insert a reason for being late.</p>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-md form-switch me-2">
                                                <input class="form-check-input me-2" name="enable_late_status_box"
                                                    id="enableLateStatusBox" type="checkbox" role="switch">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Time Display Format --}}
                                    <div
                                        class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
                                        <div class="mb-3">
                                            <h5 class="fw-medium d-flex align-items-center mb-1">
                                                Time Display Format
                                            </h5>
                                            <p>Choose a time display format.</p>
                                        </div>
                                        <div class="mb-3">
                                            <select name="time_display_format" id="timeDisplayFormat"
                                                class="form-select">
                                                <option value="24">24hr format</option>
                                                <option value="12">12hr format</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('geofencingEnabled');
            const radiusSection = document.getElementById('geofencingRadiusSection');

            function updateVisibility() {
                if (toggle.checked) {
                    radiusSection.classList.remove('d-none');
                } else {
                    radiusSection.classList.add('d-none');
                }
            }

            updateVisibility();
            toggle.addEventListener('change', updateVisibility);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const breakToggle = document.getElementById('enableBreakHourButtons');
            const breakOptions = document.getElementById('breakOptionsSection');

            function updateBreakVisibility() {
                if (breakToggle.checked) {
                    breakOptions.classList.remove('d-none');
                } else {
                    breakOptions.classList.add('d-none');
                }
            }

            updateBreakVisibility();
            breakToggle.addEventListener('change', updateBreakVisibility);
        });
    </script>

    {{-- Form Submission --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            const authToken = localStorage.getItem("token");

            const apiGetRoute = '/api/settings/attendance-settings/';
            const apiUpdateRoute = '/api/settings/attendance-settings/update';

            const inputs = document.querySelectorAll(
                "#attendanceSettingsForm input, #attendanceSettingsForm select");

            const geofencingToggle = document.getElementById("geofencingEnabled");
            const geofencingSection = document.getElementById("geofencingRadiusSection");
            const geofenceBufferInput = document.getElementById("geofenceBuffer");
            const geofenceAllowedGeotaggingInput = document.getElementById("geofenceAllowedGeotagging");
            const geotaggingToggle = document.getElementById("geotaggingEnabled");

            const breakToggle = document.getElementById("enableBreakHourButtons");
            const breakSection = document.getElementById("breakOptionsSection");
            const lunchBreakInput = document.getElementById("lunchBreakLimit");
            const coffeeBreakInput = document.getElementById("coffeeBreakLimit");

            const fieldNameMap = {
                geotagging_enabled: "Geotagging",
                geofencing_enabled: "Geofencing",
                geofence_buffer: "Geofencing Tolerance Buffer",
                geofence_allowed_geotagging: "Geofence Allowed Geotagging",
                geofence_radius: "Geofence Radius",
                allow_multiple_clock_ins: "Multiple Clock-Ins",
                require_photo_capture: "Photo Capture",
                enable_break_hour_buttons: "Break Hours",
                lunch_break_limit: "Lunch Break",
                coffee_break_limit: "Coffee Break",
                rest_day_time_in_allowed: "Clock-in on Rest Day",
                enable_late_status_box: "Late Reason Prompt",
                maximum_allowed_hours: "Max Allowed Hours",
                time_display_format: "Time Format",
                grace_period: "Grace Period",
            };

            // 🔄 Show/hide or enable/disable the Allowed Geotagging input
            function toggleGeotaggingInput() {
                const ok = geotaggingToggle.checked;
                geofenceAllowedGeotaggingInput.disabled = !ok;
                geofenceAllowedGeotaggingInput.required = ok;
            }

            // 🧠 Function: Load settings from API and populate form
            async function populateSettings() {
                try {
                    const response = await fetch(apiGetRoute, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${authToken}`
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        toastr.error("Failed to load attendance settings.");
                        return;
                    }

                    // Populate inputs
                    for (let name in data) {
                        const input = document.querySelector(`[name="${name}"]`);
                        if (!input) continue;

                        if (input.type === "checkbox") {
                            input.checked = Boolean(parseInt(data[name]));
                        } else {
                            input.value = data[name];
                        }
                    }

                    toggleGeofencingSection();
                    toggleBreakOptionsSection();
                    toggleGeotaggingInput();

                } catch (err) {
                    console.error("Error loading settings:", err);
                    toastr.error("Error loading attendance settings.");
                }
            }

            // ✅ Save setting on change
            async function saveSetting(name, value) {
                try {
                    const response = await fetch(apiUpdateRoute, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            "Authorization": `Bearer ${authToken}`,
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            [name]: value
                        })
                    });

                    const data = await response.json();
                    const label = fieldNameMap[name] || name.replace(/_/g, ' ');

                    if (!response.ok) {
                        console.error("Failed to save setting:", data);
                        toastr.error(data.message || `Failed to update ${label}`);

                        if (data.message && data.message.toLowerCase().includes('permission')) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }

                    } else {
                        toastr.success(`${label} has been successfully updated.`);
                    }
                } catch (err) {
                    console.error("Error saving setting:", err);
                    toastr.error("An error occurred while saving settings.");
                }
            }

            // 🔄 Handle change on all inputs
            inputs.forEach(input => {
                input.addEventListener("change", (e) => {
                    const name = e.target.name;
                    let value;

                    if (e.target.type === "checkbox") {
                        value = e.target.checked ? 1 : 0;
                    } else {
                        value = e.target.value;
                    }

                    saveSetting(name, value);
                    if (name === "geofencing_enabled") toggleGeofencingSection();
                    if (name === "enable_break_hour_buttons") toggleBreakOptionsSection();
                    if (name === "geotagging_enabled")   toggleGeotaggingInput();
                });
            });

            // 🔄 Show/hide geofencing section
            function toggleGeofencingSection() {
                const isEnabled = geofencingToggle.checked;
                geofencingSection.classList.toggle("d-none", !isEnabled);
                geofenceBufferInput.required = isEnabled;
                geofenceAllowedGeotaggingInput.required = isEnabled;
            }

            // 🔄 Show/hide break section
            function toggleBreakOptionsSection() {
                const isEnabled = breakToggle.checked;
                breakSection.classList.toggle("d-none", !isEnabled);
                lunchBreakInput.required = isEnabled;
                coffeeBreakInput.required = isEnabled;
            }

            // 🚀 Initialize
            populateSettings();
        });
    </script>
@endpush
