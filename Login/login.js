const logregBox = document.querySelector(".logreg-box");
const loginLink = document.querySelector(".login-link");
const registerLink = document.querySelector(".register-link");
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");
const loginError = document.getElementById("loginError");
const registerError = document.getElementById("registerError");

// Form switching functionality
registerLink.addEventListener("click", () => {
    logregBox.classList.add("active");
    clearErrors();
});

loginLink.addEventListener("click", () => {
    logregBox.classList.remove("active");
    clearErrors();
    registerForm.reset();
});

// Clear error messages
function clearErrors() {
    loginError.innerHTML = "";
    registerError.innerHTML = "";
}

// Show error message
function showError(container, message) {
    container.innerHTML = `<p class='alert alert-danger text-center' role='alert'>${message}</p>`;
}

// Show success message
function showSuccess(container, message) {
    container.innerHTML = `<p class='alert alert-success text-center' role='alert'>${message}</p>`;
}

// Handle login form submission
loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(loginForm);
    formData.append("login", "1");

    try {
        const response = await fetch("log_reg.php", {
            method: "POST",
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        } else {
            if (data.needsVerification) {
                // Show error with option to resend verification
                showError(
                    loginError,
                    data.error +
                        '<br><a href="verification.php?email=' +
                        encodeURIComponent(data.email) +
                        '" class="text-primary">Click here to verify your account</a>'
                );
            } else {
                showError(loginError, data.error);
            }
        }
    } catch (error) {
        showError(loginError, "An error occurred. Please try again.");
    }
});

// Handle register form submission
registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(registerForm);
    formData.append("register", "1");

    try {
        const response = await fetch("log_reg.php", {
            method: "POST",
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            if (data.showEmailMessage) {
                // Show success message and tell user to check email
                showSuccess(
                    registerError,
                    data.message +
                        '<br><small class="text-muted">Check your spam folder if you don\'t see the email within a few minutes.</small>'
                );
                registerForm.reset();
                // Switch to login form after showing message
                setTimeout(() => {
                    logregBox.classList.remove("active");
                    clearErrors();
                }, 5000); // Give user more time to read the message
            } else if (data.redirect) {
                // Fallback redirect (shouldn't happen now)
                window.location.href = data.redirect;
            } else {
                showSuccess(registerError, data.message);
            }
        } else {
            showError(registerError, data.error);
        }
    } catch (error) {
        showError(registerError, "An error occurred. Please try again.");
    }
});
