<!-- jQuery -->
<script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>

<!-- Bootstrap Core JS -->
<script src="{{ URL::asset('build/js/bootstrap.bundle.min.js') }}"></script>

<!-- Feather Icon JS -->
<script src="{{ URL::asset('build/js/feather.min.js') }}"></script>

<!-- Slimscroll JS -->
<script src="{{ URL::asset('build/js/jquery.slimscroll.min.js') }}"></script>

<!-- Summernote JS -->
<script src="{{ URL::asset('build/plugins/summernote/summernote-lite.min.js') }}"></script>

<!-- Color Picker JS -->
<script src="{{ URL::asset('build/js/plyr-js.js') }}"></script>
<script src="{{ URL::asset('build/plugins/@simonwep/pickr/pickr.es5.min.js') }}"></script>

<!-- Datatable JS -->
<script src="{{ URL::asset('build/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/js/dataTables.bootstrap5.min.js') }}"></script>

<!-- Bootstrap Tagsinput JS -->
<script src="{{ URL::asset('build/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>

<!-- Owl Carousel -->
<script src="{{ URL::asset('build/plugins/owlcarousel/owl.carousel.min.js') }}"></script>

<!-- Daterangepikcer JS -->
<script src="{{ URL::asset('build/js/moment.js') }}"></script>
<script src="{{ URL::asset('build/plugins/daterangepicker/daterangepicker.js') }}"></script>

@if (Route::is(['ui-rangeslider']))
    <!-- Rangeslider JS -->
    <script src="{{ URL::asset('build/plugins/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/ion-rangeslider/js/custom-rangeslider.js') }}"></script>
@endif

<!-- Fullcalendar JS -->
<script src="{{ URL::asset('build/plugins/fullcalendar/index.global.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/fullcalendar/calendar-data.js') }}"></script>

<!-- Datetimepicker JS -->
<script src="{{ URL::asset('build/js/bootstrap-datetimepicker.min.js') }}"></script>

<!-- Select2 JS -->
<script src="{{ URL::asset('build/plugins/select2/js/select2.min.js') }}"></script>

<!-- Theiastickysidebar JS -->
<script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/theia-sticky-sidebar.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/ResizeSensor.min.js') }}"></script>

<!-- Owl Carousel JS -->
<script src="{{ URL::asset('build/js/owl.carousel.min.js') }}"></script>

@if (Route::is(['ui-clipboard']))
    <!-- Clipboard JS -->
    <script src="{{ URL::asset('build/plugins/clipboard/clipboard.min.js') }}"></script>
@endif

@if (Route::is(['maps-vector']))
    <script src="{{ URL::asset('build/plugins/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <!-- JSVector Maps MapsJS -->
    <script src="{{ URL::asset('build/plugins/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ URL::asset('build/js/us-merc-en.js') }}"></script>
    <script src="{{ URL::asset('build/js/russia.js') }}"></script>
    <script src="{{ URL::asset('build/js/spain.js') }}"></script>
    <script src="{{ URL::asset('build/js/canada.js') }}"></script>
    <script src="{{ URL::asset('build/js/jsvectormap.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/@simonwep/pickr/pickr.min.js') }}"></script>
@endif

@if (Route::is(['maps-leaflet']))
    <script src="{{ URL::asset('build/plugins/leaflet/leaflet.js') }}"></script>
    <script src="{{ URL::asset('build/js/leaflet.js') }}"></script>
@endif

@if (Route::is(['ui-drag-drop']))
    <!-- Dragula JS -->
    <script src="{{ URL::asset('build/plugins/dragula/js/dragula.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/dragula/js/drag-drop.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/dragula/js/draggable-cards.js') }}"></script>
@endif

@if (Route::is(['ui-sweetalerts', 'ui-ribbon']))
    <!-- Sweetalert 2 -->
    <script src="{{ URL::asset('build/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/sweetalert/sweetalerts.min.js') }}"></script>
@endif

@if (Route::is(['ui-stickynote', 'kanban-view', 'task-board', 'deals-grid', 'leads-grid', 'candidates-kanban']))
    <!-- Stickynote JS -->
    <script src="{{ URL::asset('build/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/jquery.ui.touch-punch.min.js') }}"></script>
@endif

@if (Route::is(['plugin', 'ui-stickynote']))
    <script src="{{ URL::asset('build/plugins/stickynote/sticky.js') }}"></script>
@endif

@if (
    Route::is([
        'chart-apex',
        'index',
        'employee-dashboard',
        'deals-dashboard',
        'leads-dashboard',
        'file-manager',
        'dashboard',
        'companies',
        'packages',
        'layout-horizontal',
        'layout-detached',
        'layout-modern',
        'layout-horizontal-overlay',
        'layout-two-column',
        'layout-hovered',
        'layout-box',
        'layout-horizontal-single',
        'layout-horizontal-box',
        'layout-horizontal-sidemenu',
        'layout-vertical-transparent',
        'layout-without-header',
        'layout-rtl',
        'layout-dark',
        'analytics',
        'expenses-report',
        'invoice-report',
        'payment-report',
        'project-report',
        'task-report',
        'user-report',
        'employee-report',
        'payslip-report',
        'attendance-report',
        'leave-report',
        'daily-report',
        'superadmin-dashboard',
        'superadmin-tenants',
        'admin-dashboard',
    ])
)
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/apexchart/chart-data.js') }}"></script>
@endif

@if (Route::is(['chart-c3']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/c3-chart/d3.v5.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/c3-chart/c3.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/c3-chart/chart-data.js') }}"></script>
@endif

@if (
    Route::is([
        'chart-js',
        'index',
        'deals-dashboard',
        'dashboard',
        'companies',
        'layout-horizontal',
        'layout-detached',
        'layout-modern',
        'layout-horizontal-overlay',
        'layout-two-column',
        'layout-hovered',
        'layout-box',
        'layout-horizontal-single',
        'layout-horizontal-box',
        'layout-horizontal-sidemenu',
        'layout-vertical-transparent',
        'layout-without-header',
        'layout-rtl',
        'layout-dark',
        'analytics',
        'superadmin-dashboard',
        'superadmin-tenant',
    ])
)
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/chartjs/chart-data.js') }}"></script>
@endif

@if (Route::is(['chart-morris']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/morris/raphael-min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/morris/morris.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/morris/chart-data.js') }}"></script>
@endif

@if (
    Route::is([
        'chart-peity',
        'deals-dashboard',
        'leads-dashboard',
        'dashboard',
        'companies',
        'subscription',
        'tickets-grid',
        'tickets',
        'task-report',
        'superadmin-dashboard',
        'superadmin-subscription',
        'superadmin-tenants',
    ])
)
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/peity/jquery.peity.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/peity/chart-data.js') }}"></script>
@endif

@if (Route::is(['chart-flot']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/flot/jquery.flot.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/flot/jquery.flot.fillbetween.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/flot/jquery.flot.pie.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/flot/chart-data.js') }}"></script>
@endif

<!-- Slimscroll JS -->
<script src="{{ URL::asset('build/js/jquery.slimscroll.min.js') }}"></script>

@if (Route::is(['ui-rating']))
    <!-- Rater JS -->
    <script src="{{ URL::asset('build/plugins/rater-js/index.js') }}"></script>

    <!-- Internal Ratings JS -->
    <script src="{{ URL::asset('build/js/ratings.js') }}"></script>
@endif

@if (Route::is(['ui-toasts']))
    <!-- Chart JS -->
    <script src="{{ URL::asset('build/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/toastr/toastr.js') }}"></script>
@endif

@if (Route::is(['ui-counter']))
    <!-- Stickynote JS -->
    <script src="{{ URL::asset('build/plugins/countup/jquery.counterup.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/countup/jquery.waypoints.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/countup/jquery.missofis-countdown.js') }}"></script>
@endif

@if (Route::is(['ui-lightbox']))
    <!-- Alertify JS -->
    <script src="{{ URL::asset('build/plugins/lightbox/glightbox.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/lightbox/lightbox.js') }}"></script>
@endif

@if (Route::is(['form-wizard']))
    <!-- Wizard JS -->
    <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/prettify.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/twitter-bootstrap-wizard/form-wizard.js') }}"></script>
@endif

@if (Route::is(['form-mask']))
    <!-- Mask JS -->
    <script src="{{ URL::asset('build/js/jquery.maskedinput.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/mask.js') }}"></script>
@endif

<!-- Sticky Sidebar JS -->
<script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/ResizeSensor.js') }}"></script>
<script src="{{ URL::asset('build/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}"></script>

@if (Route::is(['reset-password', 'reset-password-2', 'reset-password-3']))
    <!-- Validation-->
    <script src="{{ URL::asset('build/js/validation.js') }}"></script>
@endif

@if (
    Route::is([
        'email-verification',
        'email-verification-2',
        'email-verification-3',
        'two-step-verification',
        'two-step-verification-2',
        'two-step-verification-3',
    ])
)
    <script src="{{ URL::asset('build/js/otp.js') }}"></script>
@endif



@if (Route::is(['form-fileupload']))
    <!-- Fileupload JS -->
    <script src="{{ URL::asset('build/plugins/fileupload/fileupload.min.js') }}"></script>
@endif

@if (Route::is(['employee-salary']))
    <script src="{{ URL::asset('build/js/employee-salary.js') }}"></script>
@endif

<!-- Fancybox JS -->
<script src="{{ URL::asset('build/plugins/fancybox/jquery.fancybox.min.js') }}"></script>

<!-- Chart JS -->
<script src="{{ URL::asset('build/plugins/chartjs/chart.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/chartjs/chart-data.js') }}"></script>


<script src="{{ URL::asset('build/plugins/flatpickr/flatpickr.js') }}"></script>
<script src="{{ URL::asset('build/plugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ URL::asset('build/plugins/jquery-timepicker/jquery-timepicker.js') }}"></script>
<script src="{{ URL::asset('build/plugins/pickr/pickr.js') }}"></script>

<!-- Page JS -->
<script src="{{ URL::asset('build/js/forms-pickers.js') }}"></script>



@if (Route::is(['coming-soon']))
    <script src="{{ URL::asset('build/js/coming-soon.js') }}"></script>
@endif

<script src="{{ URL::asset('build/js/email.js') }}"></script>
<script src="{{ URL::asset('build/js/kanban.js') }}"></script>
<script src="{{ URL::asset('build/js/invoice.js') }}"></script>
<script src="{{ URL::asset('build/js/projects.js') }}"></script>
<script src="{{ URL::asset('build/js/add-comments.js') }}"></script>
<script src="{{ URL::asset('build/js/file-manager.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap4-duallistbox/4.0.2/jquery.bootstrap-duallistbox.min.js">
</script>

<!-- Custom JS -->
<script src="{{ URL::asset('build/js/todo.js') }}"></script>
<script src="{{ URL::asset('build/js/theme-colorpicker.js') }}"></script>
<script src="{{ URL::asset('build/js/script.js') }}"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Google Map API -->
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCoZSVkyGR645u4B_OOFmepLzrRBB8Hgmc&libraries=places&callback=initMap"
    async defer></script>



<!-- Addon Modal Script -->
@if(session()->has('addon_redirect'))
    <script>
        // Show page for 1.5 seconds before showing modal
        setTimeout(function() {
            var errorView = @json(session('addon_redirect'));
            var isAddon = errorView === 'errors.addonrequired';

            // Create backdrop overlay
            var backdrop = document.createElement('div');
            backdrop.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);opacity:0;transition:opacity 0.5s;z-index:99998;backdrop-filter:blur(3px);';
            document.body.appendChild(backdrop);

            // Create modal container
            var modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);width:90%;max-width:580px;background:linear-gradient(135deg, #12515D 0%, #0f3d47 25%, #1a4b56 50%, #008080 100%);border-radius:28px;padding:48px 40px;box-shadow:0 40px 80px rgba(0,0,0,0.25);z-index:99999;opacity:0;transition:all 0.5s cubic-bezier(0.16, 1, 0.3, 1);overflow-y:auto;max-height:90vh;';

            // Content HTML
            var dashboardUrl = '{{ (\App\Helpers\PermissionHelper::get(1)) ? route("admin-dashboard") : route("employee-dashboard") }}';

            if (isAddon) {
                modal.innerHTML = `
                    <div style="text-align:center;color:#fff;">
                        <div style="width:80px;height:80px;margin:0 auto 32px;background:linear-gradient(135deg, #FFB400, #ed7464);border-radius:20px;display:flex;align-items:center;justify-content:center;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                            </svg>
                        </div>
                        <div style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg, #FFB400, #ed7464);color:#fff;padding:8px 16px;border-radius:16px;font-size:13px;font-weight:600;margin-bottom:24px;text-transform:uppercase;">
                            <div style="width:8px;height:8px;background:#fff;border-radius:50%;"></div>
                            Add-on Purchase Required
                        </div>
                        <h1 style="color:#fff;font-size:32px;font-weight:700;margin-bottom:16px;">Add-on Required</h1>
                        <p style="color:rgba(255,255,255,0.9);font-size:16px;margin-bottom:40px;line-height:1.6;">
                            This feature requires a paid add-on. Browse our marketplace to unlock additional HR & payroll capabilities.
                        </p>
                        <div style="display:flex;flex-direction:column;gap:16px;align-items:center;">
                            <a href="/addons" style="display:inline-flex;align-items:center;gap:8px;padding:16px 32px;background:linear-gradient(135deg, #FFB400, #ed7464);color:#fff;text-decoration:none;border-radius:12px;font-weight:600;font-size:15px;">
                                <span>Browse Add-ons</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="${dashboardUrl}" style="color:rgba(255,255,255,0.9);text-decoration:none;font-size:14px;font-weight:500;padding:8px 16px;display:inline-flex;align-items:center;gap:6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/>
                                </svg>
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                `;
            } else {
                modal.innerHTML = `
                    <div style="text-align:center;color:#fff;">
                        <div style="width:80px;height:80px;margin:0 auto 32px;background:linear-gradient(135deg, #008080, #12515D);border-radius:20px;display:flex;align-items:center;justify-content:center;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <circle cx="12" cy="16" r="1"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg, #b53654, #d1477a);color:#fff;padding:8px 16px;border-radius:16px;font-size:13px;font-weight:600;margin-bottom:24px;text-transform:uppercase;">
                            <div style="width:8px;height:8px;background:#fff;border-radius:50%;"></div>
                            Premium Access Required
                        </div>
                        <h1 style="color:#fff;font-size:32px;font-weight:700;margin-bottom:16px;">Feature Access Required</h1>
                        <p style="color:rgba(255,255,255,0.9);font-size:16px;margin-bottom:40px;line-height:1.6;">
                            Unlock advanced HR & payroll capabilities. Choose the perfect plan to scale your business operations.
                        </p>
                        <div style="display:flex;flex-direction:column;gap:16px;align-items:center;">
                            <a href="https://timora.ph/#pricing" style="display:inline-flex;align-items:center;gap:8px;padding:16px 32px;background:linear-gradient(135deg, #008080, #12515D);color:#fff;text-decoration:none;border-radius:12px;font-weight:600;font-size:15px;">
                                <span>Upgrade Now</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="${dashboardUrl}" style="color:rgba(255,255,255,0.9);text-decoration:none;font-size:14px;font-weight:500;padding:8px 16px;display:inline-flex;align-items:center;gap:6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/>
                                </svg>
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                `;
            }

            document.body.appendChild(modal);

            // Fade in backdrop and modal
            setTimeout(function() {
                backdrop.style.opacity = '1';
                modal.style.opacity = '1';
                modal.style.transform = 'translate(-50%,-50%) scale(1)';
            }, 10);
        }, 1500);
    </script>
@endif

<!-- Subscription Expired Modal Script -->
@if(session()->has('subscription_expired'))
    <script>
        // Show page for 1.5 seconds before showing modal
        setTimeout(function() {
            var message = @json(session('subscription_expired'));

            // Create backdrop overlay
            var backdrop = document.createElement('div');
            backdrop.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);opacity:0;transition:opacity 0.5s;z-index:99998;backdrop-filter:blur(3px);';
            document.body.appendChild(backdrop);

            // Create modal container
            var modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);width:90%;max-width:580px;background:linear-gradient(135deg, #12515D 0%, #0f3d47 25%, #1a4b56 50%, #008080 100%);border-radius:28px;padding:48px 40px;box-shadow:0 40px 80px rgba(0,0,0,0.25);z-index:99999;opacity:0;transition:all 0.5s cubic-bezier(0.16, 1, 0.3, 1);overflow-y:auto;max-height:90vh;';

            // Content HTML
            var dashboardUrl = '{{ (\App\Helpers\PermissionHelper::get(1)) ? route("admin-dashboard") : route("employee-dashboard") }}';

            modal.innerHTML = `
                <div style="text-align:center;color:#fff;">
                    <div style="width:80px;height:80px;margin:0 auto 32px;background:linear-gradient(135deg, #b53654, #ed7464);border-radius:20px;display:flex;align-items:center;justify-content:center;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg, #b53654, #d1477a);color:#fff;padding:8px 16px;border-radius:16px;font-size:13px;font-weight:600;margin-bottom:24px;text-transform:uppercase;">
                        <div style="width:8px;height:8px;background:#fff;border-radius:50%;animation:pulse 2s infinite;"></div>
                        Subscription Expired
                    </div>
                    <h1 style="color:#fff;font-size:32px;font-weight:700;margin-bottom:16px;">Subscription Has Expired</h1>
                    <p style="color:rgba(255,255,255,0.9);font-size:16px;margin-bottom:24px;line-height:1.6;">
                        Your subscription has ended and access to system features is currently restricted.
                    </p>
                    <div style="background:linear-gradient(135deg, #FFF5E1, #FFE8CC);border:1px solid #FFB400;border-radius:16px;padding:20px;margin-bottom:32px;">
                        <p style="color:#171717;font-size:15px;line-height:1.6;margin:0;">
                            <strong style="color:#b53654;">Action Required:</strong> ${message}
                        </p>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:16px;align-items:center;">
                        <a href="${dashboardUrl}" style="display:inline-flex;align-items:center;gap:8px;padding:16px 32px;background:linear-gradient(135deg, #008080, #12515D);color:#fff;text-decoration:none;border-radius:12px;font-weight:600;font-size:15px;">
                            <span>Return to Dashboard</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <button onclick="document.getElementById('logout-form').submit();" style="background:none;border:none;color:rgba(255,255,255,0.9);text-decoration:none;font-size:14px;font-weight:500;padding:8px 16px;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Sign Out
                        </button>
                    </div>
                </div>
            `;

            // Add logout form if it doesn't exist
            if (!document.getElementById('logout-form')) {
                var logoutForm = document.createElement('form');
                logoutForm.id = 'logout-form';
                logoutForm.action = '{{ route("logout") }}';
                logoutForm.method = 'POST';
                logoutForm.style.display = 'none';
                logoutForm.innerHTML = '@csrf';
                document.body.appendChild(logoutForm);
            }

            document.body.appendChild(modal);

            // Add pulse animation
            var style = document.createElement('style');
            style.textContent = '@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }';
            document.head.appendChild(style);

            // Fade in backdrop and modal
            setTimeout(function() {
                backdrop.style.opacity = '1';
                modal.style.opacity = '1';
                modal.style.transform = 'translate(-50%,-50%) scale(1)';
            }, 10);
        }, 1500);
    </script>
@endif

@stack('scripts')
