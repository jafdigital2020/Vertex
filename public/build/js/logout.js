function clearLaravelCookies() {
    [
        "laravel_session",
        "XSRF-TOKEN",
        "remember_web",
        "remember_global",
    ].forEach((name) => {
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=${window.location.hostname}`;
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;`;
    });
}

async function logout() {
    try {
        const token = localStorage.getItem("token");

        if (token) {
            await fetch("/api/logout", {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${token}`,
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content"),
                    Accept: "application/json",
                },
            });
        }

        // Clear everything
        localStorage.clear();
        sessionStorage.clear();
        clearLaravelCookies();

        if (typeof toastr !== "undefined") {
            toastr.success("Logged out successfully");
        }

        // Immediate redirect without setTimeout
        window.location.replace("/login");
    } catch (error) {
        console.error("Logout error:", error);
        localStorage.clear();
        sessionStorage.clear();
        clearLaravelCookies();
        window.location.replace("/login");
    }
}

// Simplified token verification
async function verifyToken() {
    const currentPath = window.location.pathname;
    const publicPaths = ["/login", "/register", "/forgot-password"];

    if (publicPaths.includes(currentPath)) return;

    const token = localStorage.getItem("token");
    if (!token) {
        window.location.replace("/login");
        return;
    }

    try {
        const response = await fetch("/api/verify-token", {
            method: "GET",
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
            },
        });

        if (!response.ok) {
            localStorage.clear();
            sessionStorage.clear();
            window.location.replace("/login");
        }
    } catch (error) {
        console.error("Token verification failed:", error);
        localStorage.clear();
        sessionStorage.clear();
        window.location.replace("/login");
    }
}

// Initialize auth functions
document.addEventListener("DOMContentLoaded", function () {
    // Only verify token if not on login page
    if (!window.location.pathname.includes("/login")) {
        verifyToken();
    }

    // Add logout event listeners
    const logoutButtons = document.querySelectorAll(
        "[data-logout], .logout-btn, #logout-btn, .logout-link"
    );
    logoutButtons.forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            logout();
        });
    });
});
