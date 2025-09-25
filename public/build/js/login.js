document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");

    loginForm.addEventListener("submit", async function (event) {
        event.preventDefault(); // Prevent default form submission

        let companyCode = document.getElementById("companyCode").value.trim();
        let login = document.getElementById("login").value.trim();
        let password = document.getElementById("password").value.trim();
        let remember = document.getElementById("remember_me").checked;

        if (login === "" || password === "") {
            toastr.warning("Please enter your email/username and password.");
            return;
        }

        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

        let requestBody = { login, password, remember };
        if (companyCode) {
            requestBody.companyCode = companyCode;
        }

        try {
            let response = await fetch("/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken // Include CSRF Token
                },
                body: JSON.stringify(requestBody)
            });

            let data = await response.json();

            if (response.ok) {
                toastr.success("Login successful!");

                // Store token for API authentication
                localStorage.setItem("token", data.token);

                // Determine redirect path based on user role
                let redirectUrl = "/employee-dashboard"; // Default for users

                if (data.user.role === "super_admin") {
                    redirectUrl = "/superadmin-dashboard";
                } else if (data.user.role === "tenant_admin") {
                    redirectUrl = "/admin-dashboard";
                }

                // Redirect user
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1500);
            } else {
                toastr.error(data.message || "Invalid credentials");
            }
        } catch (error) {
            toastr.error("An error occurred. Please try again.");
            console.error("Error:", error);
        }
    });
});
