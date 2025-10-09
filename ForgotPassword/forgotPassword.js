// Multi-step form handling
document.addEventListener("DOMContentLoaded", function () {
    let currentStep = 1;
    let userEmail = "";
    let countdownTimer = null;
    let timeRemaining = 60; // 1 minute in seconds
    let redirectTimer = null;
    let redirectTimeRemaining = 3; // 3 seconds for redirect

    const steps = {
        1: document.getElementById("step1"),
        2: document.getElementById("step2"),
        3: document.getElementById("step3"),
        success: document.getElementById("successStep"),
    };

    // Form elements
    const emailForm = document.getElementById("emailForm");
    const codeForm = document.getElementById("codeForm");
    const passwordForm = document.getElementById("passwordForm");
    const resendCodeLink = document.getElementById("resendCode");
    const loginRedirectBtn = document.getElementById("loginRedirectBtn");

    // Back icon handling
    const backIcon = document.querySelector(".back-icon");
    const stepBackIcons = document.querySelectorAll(".step-back");

    // Initialize
    showStep(1);

    // Step 1: Email form submission
    if (emailForm) {
        emailForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const emailInput = document.getElementById("email");
            const email = emailInput.value.trim();
            const submitBtn = emailForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;

            if (validateEmail(email)) {
                userEmail = email;

                // Show loading state
                submitBtn.textContent = "Sending...";
                submitBtn.disabled = true;

                try {
                    const response = await fetch("forgotPasswordHandler.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `action=send_code&email=${encodeURIComponent(
                            email
                        )}`,
                    });

                    const data = await response.json();

                    if (data.success) {
                        showStep(2);
                        startCountdown(); // Start the countdown when moving to step 2
                    } else {
                        showEmailError(data.error);
                    }
                } catch (error) {
                    showEmailError(
                        "Failed to send verification code. Please try again."
                    );
                } finally {
                    // Reset button state
                    submitBtn.textContent = originalBtnText;
                    submitBtn.disabled = false;
                }
            }
        });
    }

    // Step 2: Verification code form submission
    if (codeForm) {
        codeForm.addEventListener("submit", function (e) {
            e.preventDefault();
            handleCodeSubmission();
        });
    }

    // Separate function to handle code verification
    async function handleCodeSubmission() {
        const digitInputs = document.querySelectorAll(".verification-digit");
        const code = Array.from(digitInputs)
            .map((input) => input.value)
            .join("");

        if (validateCode(code)) {
            const submitBtn = codeForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;

            // Show loading state
            submitBtn.textContent = "Verifying...";
            submitBtn.disabled = true;

            try {
                const response = await fetch("forgotPasswordHandler.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `action=verify_code&code=${encodeURIComponent(code)}`,
                });

                const data = await response.json();

                if (data.success) {
                    showStep(3);
                } else {
                    showCodeError(data.error);
                }
            } catch (error) {
                showCodeError("Failed to verify code. Please try again.");
            } finally {
                // Reset button state
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            }
        } else {
            // Show error state for invalid format
            digitInputs.forEach((input) => {
                input.classList.add("error");
            });

            const errorMessage = document.querySelector(
                "#step2 .error-message"
            );
            errorMessage.style.display = "block";
            errorMessage.textContent = "Please enter a valid 6-digit code";

            setTimeout(() => {
                digitInputs.forEach((input) => {
                    input.classList.remove("error");
                });
                errorMessage.style.display = "none";
            }, 3000);
        }
    }

    // Step 3: Password form submission
    if (passwordForm) {
        passwordForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const newPasswordInput = document.getElementById("newPassword");
            const confirmPasswordInput =
                document.getElementById("confirmPassword");
            const newPassword = newPasswordInput.value.trim();
            const confirmPassword = confirmPasswordInput.value.trim();
            const submitBtn = passwordForm.querySelector(
                'button[type="submit"]'
            );
            const originalBtnText = submitBtn.textContent;

            if (validatePasswords(newPassword, confirmPassword)) {
                // Show loading state
                submitBtn.textContent = "Resetting...";
                submitBtn.disabled = true;

                try {
                    const response = await fetch("forgotPasswordHandler.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `action=reset_password&password=${encodeURIComponent(
                            newPassword
                        )}&confirm_password=${encodeURIComponent(
                            confirmPassword
                        )}`,
                    });

                    const data = await response.json();

                    if (data.success) {
                        showStep("success");
                    } else {
                        showPasswordError(data.error);
                    }
                } catch (error) {
                    showPasswordError(
                        "Failed to reset password. Please try again."
                    );
                } finally {
                    // Reset button state
                    submitBtn.textContent = originalBtnText;
                    submitBtn.disabled = false;
                }
            }
        });
    }

    // Resend code functionality
    if (resendCodeLink) {
        resendCodeLink.addEventListener("click", async function (e) {
            e.preventDefault();

            const originalText = resendCodeLink.textContent;
            resendCodeLink.textContent = "Sending...";

            try {
                const response = await fetch("forgotPasswordHandler.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `action=send_code&email=${encodeURIComponent(
                        userEmail
                    )}`,
                });

                const data = await response.json();

                if (data.success) {
                    resendCodeLink.textContent = "Code sent! Check your email";
                    // Restart countdown (this will also disable the resend link)
                    startCountdown();
                } else {
                    resendCodeLink.textContent = "Failed to send code";
                    showCodeError(data.error);
                }
            } catch (error) {
                resendCodeLink.textContent = "Failed to send code";
                showCodeError("Failed to resend code. Please try again.");
            }

            setTimeout(() => {
                resendCodeLink.textContent = originalText;
            }, 3000);
        });
    }

    // Back navigation
    if (backIcon) {
        backIcon.addEventListener("click", function () {
            window.location.href = "../Login/login.php";
        });
    }

    stepBackIcons.forEach((icon) => {
        icon.addEventListener("click", function () {
            goToPreviousStep();
        });
    });

    // Keyboard navigation
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            goToPreviousStep();
        }
    });

    // Redirect button handling
    if (loginRedirectBtn) {
        loginRedirectBtn.addEventListener("click", function (e) {
            e.preventDefault();
            stopRedirectTimer(); // Stop the auto-redirect timer
            window.location.href = "../Login/login.php";
        });
    }

    // Functions
    function showStep(stepNumber) {
        // Hide all steps
        Object.keys(steps).forEach((key) => {
            if (steps[key]) {
                steps[key].classList.add("d-none");
                steps[key].classList.remove("fade-in");
            }
        });

        // Show current step
        if (steps[stepNumber]) {
            steps[stepNumber].classList.remove("d-none");
            setTimeout(() => {
                steps[stepNumber].classList.add("fade-in");
            }, 50);
        }

        currentStep = stepNumber;

        // Start redirect timer if showing success step
        if (stepNumber === "success") {
            startRedirectTimer();
        }

        // Focus on first input of the step
        setTimeout(() => {
            const currentStepElement = steps[stepNumber];
            if (currentStepElement) {
                if (stepNumber === 2) {
                    // Focus on first verification digit input for step 2
                    const firstDigitInput = currentStepElement.querySelector(
                        ".verification-digit"
                    );
                    if (firstDigitInput) {
                        firstDigitInput.focus();
                    }
                } else {
                    const firstInput =
                        currentStepElement.querySelector("input");
                    if (firstInput) {
                        firstInput.focus();
                    }
                }
            }
        }, 300);
    }

    function goToPreviousStep() {
        if (currentStep > 1) {
            // Stop countdown when going back from step 2
            if (currentStep === 2) {
                stopCountdown();
            }

            const targetStep = currentStep - 1;

            // Reset all inputs when going back to step 1
            if (targetStep === 1) {
                resetAllInputs();
            }

            showStep(targetStep);
        } else {
            window.location.href = "../Login/login.php";
        }
    }

    function resetAllInputs() {
        // Reset step 2 verification code inputs
        const digitInputs = document.querySelectorAll(".verification-digit");
        digitInputs.forEach((input) => {
            input.value = "";
            input.classList.remove("error");
        });

        // Reset step 3 password inputs
        const newPasswordInput = document.getElementById("newPassword");
        const confirmPasswordInput = document.getElementById("confirmPassword");
        if (newPasswordInput) {
            newPasswordInput.value = "";
            newPasswordInput.style.borderBottomColor = "";
        }
        if (confirmPasswordInput) {
            confirmPasswordInput.value = "";
            confirmPasswordInput.style.borderBottomColor = "";
        }

        // Hide all error messages
        const errorElements = document.querySelectorAll(".error-message");
        errorElements.forEach((el) => {
            el.style.display = "none";
        });

        // Reset user email variable
        userEmail = "";
    }

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function validateCode(code) {
        return code.length === 6 && /^\d{6}$/.test(code);
    }

    function validatePasswords(password1, password2) {
        if (password1.length < 8) {
            showPasswordError("Password must be at least 8 characters long");
            return false;
        }

        if (password1 !== password2) {
            showPasswordError("Passwords do not match");
            return false;
        }

        return true;
    }

    function showPasswordError(message) {
        const errorElements = document.querySelectorAll(
            "#step3 .error-message"
        );
        errorElements.forEach((el) => {
            el.textContent = message;
            el.style.display = "block";
        });

        setTimeout(() => {
            errorElements.forEach((el) => {
                el.style.display = "none";
            });
        }, 3000);
    }

    // Countdown timer functions
    function startCountdown() {
        timeRemaining = 60; // Reset to 1 minute
        updateTimerDisplay();

        // Disable resend code link
        if (resendCodeLink) {
            resendCodeLink.style.pointerEvents = "none";
            resendCodeLink.style.opacity = "0.5";
        }

        countdownTimer = setInterval(() => {
            timeRemaining--;
            updateTimerDisplay();

            if (timeRemaining <= 0) {
                stopCountdown();
                // Re-enable resend code link when timer expires
                if (resendCodeLink) {
                    resendCodeLink.style.pointerEvents = "auto";
                    resendCodeLink.style.opacity = "1";
                }
            }
        }, 1000);
    }

    function stopCountdown() {
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    }

    function updateTimerDisplay() {
        const timerElements = document.querySelectorAll("#step2 .timer-text");
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        const timeString = `${minutes}:${seconds.toString().padStart(2, "0")}`;

        timerElements.forEach((element) => {
            element.textContent = timeString;
        });
    }

    // Redirect timer functions
    function startRedirectTimer() {
        redirectTimeRemaining = 3; // Reset to 3 seconds
        updateRedirectTimerDisplay();

        redirectTimer = setInterval(() => {
            redirectTimeRemaining--;
            updateRedirectTimerDisplay();

            if (redirectTimeRemaining <= 0) {
                stopRedirectTimer();
                // Redirect to login page
                window.location.href = "../Login/login.php";
            }
        }, 1000);
    }

    function stopRedirectTimer() {
        if (redirectTimer) {
            clearInterval(redirectTimer);
            redirectTimer = null;
        }
    }

    function updateRedirectTimerDisplay() {
        const timerElement = document.getElementById("redirectTimer");
        if (timerElement) {
            timerElement.textContent = redirectTimeRemaining;
        }
    }

    // Handle 6-digit verification code inputs
    const digitInputs = document.querySelectorAll(".verification-digit");

    digitInputs.forEach((input, index) => {
        // Handle input - check for empty previous inputs first
        input.addEventListener("input", function (e) {
            this.value = this.value.replace(/\D/g, "");

            if (this.value) {
                // Check if there are any empty inputs before the current one
                let firstEmptyIndex = -1;
                for (let i = 0; i < digitInputs.length; i++) {
                    if (!digitInputs[i].value) {
                        firstEmptyIndex = i;
                        break;
                    }
                }

                // If there's an empty input before the current one
                if (firstEmptyIndex !== -1 && firstEmptyIndex < index) {
                    // Move the digit to the first empty input
                    digitInputs[firstEmptyIndex].value = this.value;
                    this.value = ""; // Clear the current input

                    // Focus on the next input after the first empty one
                    if (firstEmptyIndex < digitInputs.length - 1) {
                        digitInputs[firstEmptyIndex + 1].focus();
                    }
                } else {
                    // Normal flow - current input is the correct position or all previous are filled
                    if (index < digitInputs.length - 1) {
                        digitInputs[index + 1].focus();
                    }
                }

                // Auto-submit when all 6 digits are filled
                const allFilled = Array.from(digitInputs).every(
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
        input.addEventListener("keydown", function (e) {
            if (e.key === "Backspace") {
                e.preventDefault(); // Prevent default backspace behavior

                // Find the rightmost filled input and clear it
                let rightmostFilledIndex = -1;
                for (let i = digitInputs.length - 1; i >= 0; i--) {
                    if (digitInputs[i].value) {
                        rightmostFilledIndex = i;
                        break;
                    }
                }

                if (rightmostFilledIndex >= 0) {
                    // Clear the rightmost filled input
                    digitInputs[rightmostFilledIndex].value = "";
                    // Focus on that input
                    digitInputs[rightmostFilledIndex].focus();
                } else if (index > 0) {
                    // If no filled inputs, move focus to previous input
                    digitInputs[index - 1].focus();
                }
            }
        });

        // Handle paste - always paste from the first input regardless of current focus
        input.addEventListener("paste", function (e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData(
                "text"
            );
            const numbers = paste.replace(/\D/g, "").slice(0, 6);

            // Clear all inputs first
            digitInputs.forEach((input) => (input.value = ""));

            // Always start pasting from the first input (index 0)
            numbers.split("").forEach((digit, i) => {
                if (digitInputs[i]) {
                    digitInputs[i].value = digit;
                }
            });

            // Focus on the next empty input after the pasted digits, or the last input if all are filled
            if (numbers.length < 6) {
                digitInputs[numbers.length].focus();
            } else {
                digitInputs[5].focus(); // Focus on last input if all 6 digits are pasted
            }

            // Check if all digits are filled after paste
            const allFilled = Array.from(digitInputs).every(
                (input) => input.value
            );
            if (allFilled) {
                setTimeout(() => {
                    handleCodeSubmission();
                }, 300);
            }
        });
    });

    // Add real-time password confirmation validation
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const newPasswordInput = document.getElementById("newPassword");

    if (confirmPasswordInput && newPasswordInput) {
        confirmPasswordInput.addEventListener("input", function () {
            const errorMessage =
                this.parentElement.querySelector(".error-message");

            if (
                this.value &&
                newPasswordInput.value &&
                this.value !== newPasswordInput.value
            ) {
                this.style.borderBottomColor = "#dc3545";
                errorMessage.style.display = "block";
                errorMessage.textContent = "Passwords do not match";
            } else {
                this.style.borderBottomColor = "";
                errorMessage.style.display = "none";
            }
        });
    }

    // Error handling functions
    function showEmailError(message) {
        const errorElement = document.querySelector("#step1 .error-message");
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = "block";
        }
    }

    function showCodeError(message) {
        const errorElement = document.querySelector("#step2 .error-message");
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = "block";
            errorElement.style.color = "#dc3545";
        }
    }
});
