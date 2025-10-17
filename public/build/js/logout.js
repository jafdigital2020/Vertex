function clearLaravelCookies() {
    ['laravel_session', 'XSRF-TOKEN'].forEach(name => {
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;`;
    });
}

async function logout() {
    try {
        const token = localStorage.getItem('token');

        if (token) {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
        }

        // 🔥 Critical: Clear everything
        localStorage.clear();
        sessionStorage.clear();
        clearLaravelCookies(); // Prevent session-based auth conflicts

        if (typeof toastr !== 'undefined') {
            toastr.success('Logged out successfully');
        }

        setTimeout(() => {
            window.location.href = '/login';
        }, 1000);
    } catch (error) {
        console.error('Logout error:', error);
        localStorage.clear();
        sessionStorage.clear();
        clearLaravelCookies();
        window.location.href = '/login';
    }
}

// Token verification
async function verifyToken() {
    const currentPath = window.location.pathname;
    const publicPaths = ['/login', '/register', '/forgot-password'];

    if (publicPaths.includes(currentPath)) return;

    // Prevent infinite redirect loop
    const redirectCount = parseInt(sessionStorage.getItem('redirectCount') || '0');
    if (redirectCount > 2) {
        console.warn('Too many redirects. Force-clearing auth.');
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = '/login';
        return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
        sessionStorage.setItem('redirectCount', redirectCount + 1);
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/verify-token', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            sessionStorage.setItem('redirectCount', redirectCount + 1);
            localStorage.clear();
            window.location.href = '/login';
        } else {
            // Reset counter on success
            sessionStorage.removeItem('redirectCount');
        }
    } catch (error) {
        console.error('Token verification failed:', error);
        sessionStorage.setItem('redirectCount', redirectCount + 1);
        localStorage.clear();
        window.location.href = '/login';
    }
}

// Initialize auth functions
document.addEventListener('DOMContentLoaded', function () {
    // Verify token on page load
    verifyToken();

    // Add logout event listeners
    const logoutButtons = document.querySelectorAll('[data-logout], .logout-btn, #logout-btn, .logout-link');
    logoutButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            logout();
        });
    });
});
