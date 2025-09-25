async function logout() {
    try {
        const token = localStorage.getItem('token');

        if (token) {
            const response = await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                localStorage.removeItem('token');
                localStorage.clear();
                sessionStorage.clear();

                if (typeof toastr !== 'undefined') {
                    toastr.success('Logged out successfully');
                }

                setTimeout(() => {
                    window.location.href = '/login';
                }, 1000);
            }
        } else {
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Logout error:', error);
        localStorage.removeItem('token');
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = '/login';
    }
}

// Token verification
async function verifyToken() {
    try {
        const token = localStorage.getItem('token');
        const currentPath = window.location.pathname;
        const publicPaths = ['/login', '/register', '/forgot-password'];

        if (publicPaths.includes(currentPath)) {
            return;
        }

        if (!token) {
            window.location.href = '/login';
            return;
        }

        const response = await fetch('/api/verify-token', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            localStorage.removeItem('token');
            localStorage.clear();
            sessionStorage.clear();
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Token verification error:', error);
        localStorage.removeItem('token');
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
