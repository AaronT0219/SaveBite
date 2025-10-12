// Get URL parameters
const urlParams = new URLSearchParams(window.location.search);
const email = urlParams.get("email");
const tempCode = urlParams.get("code");

// Set email in the display
if (email) {
    document.getElementById("userEmail").textContent = email;
}

// Timer functionality - will be set after fetching actual remaining time
let timeLeft = 60; // Default to 1 minute for 2FA
const timerText = document.querySelector(".timer-text");

// Fetch actual remaining time from server
async function fetchRemainingTime() {
    if (!email) return;

    try {
        const response = await fetch("2fa_get_remaining_time.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "get_remaining_time",
                email: email,
            }),
        });

        const data = await response.json();

        if (data.success) {
            if (data.alreadyEnabled) {
                // 2FA is already enabled, show appropriate message
                showAlreadyEnabledStep();
                return;
            }

            timeLeft = data.remainingTime;
            if (data.expired) {
                showError(
                    "codeError",
                    "Verification code has expired. Please request a new one."
                );
                document.getElementById("resendCode").style.display = "inline";
            }
        }
    } catch (error) {
        console.log("Could not fetch remaining time, using default");
    }

    // Start timer after setting the correct time
    updateTimer();

    // Disable resend code link initially if timer is running
    if (timeLeft > 0) {
        const resendCodeLink = document.getElementById("resendCode");
        if (resendCodeLink) {
            resendCodeLink.style.pointerEvents = "none";
            resendCodeLink.style.opacity = "0.5";
        }
    }
}

// Initialize timer with actual remaining time
fetchRemainingTime();

// Function to show already enabled step
function showAlreadyEnabledStep() {
    // Hide all other steps
    document.getElementById("step1").classList.add("d-none");
    document.getElementById("successStep").classList.add("d-none");

    // Show already enabled step
    document.getElementById("alreadyEnabledStep").classList.remove("d-none");

    // Start redirect countdown for enabled account
    startRedirectCountdownEnabled();
}

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    timerText.textContent = `${minutes}:${seconds.toString().padStart(2, "0")}`;

    if (timeLeft <= 0) {
        // Timer expired
        showError(
            "codeError",
            "Verification code has expired. Please request a new one."
        );
        // Re-enable resend code link when timer expires
        const resendCodeLink = document.getElementById("resendCode");
        if (resendCodeLink) {
            resendCodeLink.style.pointerEvents = "auto";
            resendCodeLink.style.opacity = "1";
        }
        return;
    }

    timeLeft--;
    setTimeout(updateTimer, 1000);
}

// Verification digit input handling (same as original)
const verificationDigits = document.querySelectorAll(".verification-digit");

verificationDigits.forEach((digit, index) => {
    // Handle input - check for empty previous inputs first
    digit.addEventListener("input", function (e) {
        this.value = this.value.replace(/\D/g, "");

        if (this.value) {
            // Check if there are any empty inputs before the current one
            let firstEmptyIndex = -1;
            for (let i = 0; i < verificationDigits.length; i++) {
                if (!verificationDigits[i].value) {
                    firstEmptyIndex = i;
                    break;
                }
            }

            // If there's an empty input before the current one
            if (firstEmptyIndex !== -1 && firstEmptyIndex < index) {
                // Move the digit to the first empty input
                verificationDigits[firstEmptyIndex].value = this.value;
                this.value = ""; // Clear the current input

                // Focus on the next input after the first empty one
                if (firstEmptyIndex < verificationDigits.length - 1) {
                    verificationDigits[firstEmptyIndex + 1].focus();
                }
            } else {
                // Normal flow - current input is the correct position or all previous are filled
                if (index < verificationDigits.length - 1) {
                    verificationDigits[index + 1].focus();
                }
            }

            // Auto-submit when all 6 digits are filled
            const allFilled = Array.from(verificationDigits).every(
                (input) => input.value
            );
            if (allFilled) {
                setTimeout(() => {
                    handleCodeSubmission();
                }, 300);
            }
        }
    });

    // Handle backspace - always delete from right to left
    digit.addEventListener("keydown", function (e) {
        if (e.key === "Backspace") {
            e.preventDefault(); // Prevent default backspace behavior

            // Find the rightmost filled input and clear it
            let rightmostFilledIndex = -1;
            for (let i = verificationDigits.length - 1; i >= 0; i--) {
                if (verificationDigits[i].value) {
                    rightmostFilledIndex = i;
                    break;
                }
            }

            if (rightmostFilledIndex >= 0) {
                // Clear the rightmost filled input
                verificationDigits[rightmostFilledIndex].value = "";
                // Focus on that input
                verificationDigits[rightmostFilledIndex].focus();
            } else if (index > 0) {
                // If no filled inputs, move focus to previous input
                verificationDigits[index - 1].focus();
            }
        }
    });

    // Handle paste - always paste from the first input regardless of current focus
    digit.addEventListener("paste", function (e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData("text");
        const numbers = paste.replace(/\D/g, "").slice(0, 6);

        // Clear all inputs first
        verificationDigits.forEach((input) => (input.value = ""));

        // Always start pasting from the first input (index 0)
        numbers.split("").forEach((digit, i) => {
            if (verificationDigits[i]) {
                verificationDigits[i].value = digit;
            }
        });

        // Focus on the next empty input after the pasted digits, or the last input if all are filled
        if (numbers.length < 6) {
            verificationDigits[numbers.length].focus();
        } else {
            verificationDigits[5].focus(); // Focus on last input if all 6 digits are pasted
        }

        // Check if all digits are filled after paste
        const allFilled = Array.from(verificationDigits).every(
            (input) => input.value
        );
        if (allFilled) {
            setTimeout(() => {
                handleCodeSubmission();
            }, 300);
        }
    });
});

// Show/hide error messages
function showError(containerId, message) {
    const container = document.getElementById(containerId);
    container.textContent = message;
    container.style.display = "block";
}

function hideError(containerId) {
    const container = document.getElementById(containerId);
    container.style.display = "none";
}

// Handle verification code submission
document.getElementById("codeForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    handleCodeSubmission();
});

// Separate function to handle code verification
async function handleCodeSubmission() {
    hideError("codeError");

    // Get verification code
    const code = Array.from(verificationDigits)
        .map((input) => input.value)
        .join("");

    if (code.length !== 6) {
        showError("codeError", "Please enter a complete 6-digit code");
        return;
    }

    const submitBtn = document.querySelector('#codeForm button[type="submit"]');
    const originalBtnText = submitBtn ? submitBtn.textContent : "";

    // Show loading state
    if (submitBtn) {
        submitBtn.textContent = "Enabling 2FA...";
        submitBtn.disabled = true;
    }

    try {
        const response = await fetch("2fa_handler.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "verify_2fa_code",
                email: email,
                code: code,
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Show success step
            document.getElementById("step1").classList.add("d-none");
            document.getElementById("successStep").classList.remove("d-none");

            // No auto redirect - wait for user to continue to password step
        } else {
            showError("codeError", data.error || "Invalid verification code");
        }
    } catch (error) {
        showError("codeError", "An error occurred. Please try again.");
    } finally {
        // Reset button state
        if (submitBtn) {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        }
    }
}

// Handle resend code
document.getElementById("resendCode").addEventListener("click", async (e) => {
    e.preventDefault();

    const resendCodeLink = document.getElementById("resendCode");
    const originalText = resendCodeLink.textContent;
    resendCodeLink.textContent = "Sending...";

    try {
        const response = await fetch("2fa_handler.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "resend_2fa_code",
                email: email,
            }),
        });

        const data = await response.json();

        if (data.success) {
            resendCodeLink.textContent = "Code sent! Check your email";
            // Reset timer to 1 minute for new code and disable resend link
            timeLeft = 60;
            updateTimer();
            hideError("codeError");

            // Disable resend code link during countdown
            resendCodeLink.style.pointerEvents = "none";
            resendCodeLink.style.opacity = "0.5";

            // Clear verification inputs
            verificationDigits.forEach((input) => (input.value = ""));
            verificationDigits[0].focus();
        } else {
            resendCodeLink.textContent = "Failed to send code";
            showError("codeError", data.error || "Failed to resend code");
        }
    } catch (error) {
        resendCodeLink.textContent = "Failed to send code";
        showError("codeError", "An error occurred. Please try again.");
    }

    setTimeout(() => {
        resendCodeLink.textContent = originalText;
    }, 3000);
});

// Back navigation
const backIcon = document.querySelector(".back-icon");

if (backIcon) {
    backIcon.addEventListener("click", function () {
        // Redirect back to settings
        window.location.href = "../../templates/base.php?page=settings";
    });
}

// Redirect countdown for success page
function startRedirectCountdown() {
    let countdown = 3;
    const timer = document.getElementById("redirectTimer");

    const interval = setInterval(() => {
        countdown--;
        timer.textContent = countdown;

        if (countdown <= 0) {
            clearInterval(interval);
            // Redirect back to settings
            window.location.href = "../../templates/base.php?page=settings";
        }
    }, 1000);
}

// Redirect countdown for already enabled page
function startRedirectCountdownEnabled() {
    let countdown = 3;
    const timer = document.getElementById("redirectTimerEnabled");

    const interval = setInterval(() => {
        countdown--;
        timer.textContent = countdown;

        if (countdown <= 0) {
            clearInterval(interval);
            // Redirect back to settings
            window.location.href = "../../templates/base.php?page=settings";
        }
    }, 1000);
}

// Back to settings button clicks
document.getElementById("backToSettingsBtn").addEventListener("click", (e) => {
    e.preventDefault();
    window.location.href = "../../templates/base.php?page=settings";
});

document
    .getElementById("backToSettingsBtnEnabled")
    .addEventListener("click", (e) => {
        e.preventDefault();
        window.location.href = "../../templates/base.php?page=settings";
    });

// Handle continue to password step
document
    .getElementById("continueToPasswordBtn")
    .addEventListener("click", (e) => {
        e.preventDefault();
        document.getElementById("successStep").classList.add("d-none");
        document.getElementById("passwordStep").classList.remove("d-none");

        // Focus on password input
        document.getElementById("newPassword").focus();
    });

// Handle password form submission
document
    .getElementById("passwordForm")
    .addEventListener("submit", async (e) => {
        e.preventDefault();
        await handlePasswordUpdate();
    });

// Function to handle password update
async function handlePasswordUpdate() {
    hideError("passwordError");

    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    // Validation
    if (newPassword.length < 8) {
        showError(
            "passwordError",
            "Password must be at least 8 characters long"
        );
        return;
    }

    if (newPassword !== confirmPassword) {
        showError("passwordError", "Passwords do not match");
        return;
    }

    const submitBtn = document.querySelector(
        '#passwordForm button[type="submit"]'
    );
    const originalBtnText = submitBtn ? submitBtn.textContent : "";

    // Show loading state
    if (submitBtn) {
        submitBtn.textContent = "Updating Password...";
        submitBtn.disabled = true;
    }

    try {
        const response = await fetch("2fa_handler.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "update_password",
                email: email,
                newPassword: newPassword,
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Show final success step
            document.getElementById("passwordStep").classList.add("d-none");
            document
                .getElementById("finalSuccessStep")
                .classList.remove("d-none");

            // Start redirect countdown
            startRedirectCountdown();
        } else {
            showError(
                "passwordError",
                data.error || "Failed to update password"
            );
        }
    } catch (error) {
        showError("passwordError", "An error occurred. Please try again.");
    } finally {
        // Reset button state
        if (submitBtn) {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        }
    }
}
