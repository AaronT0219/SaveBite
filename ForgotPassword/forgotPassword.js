// Multi-step form handling
document.addEventListener("DOMContentLoaded", function () {
    let currentStep = 1;
    let userEmail = "";

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

    // Back icon handling
    const backIcon = document.querySelector(".back-icon");
    const stepBackIcons = document.querySelectorAll(".step-back");

    // Initialize
    showStep(1);

    // Step 1: Email form submission
    if (emailForm) {
        emailForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const emailInput = document.getElementById("email");
            const email = emailInput.value.trim();

            if (validateEmail(email)) {
                userEmail = email;
                // Simulate sending verification code
                console.log("Sending verification code to:", email);

                // Show loading state briefly
                const submitBtn = emailForm.querySelector(
                    'button[type="submit"]'
                );
                const originalText = submitBtn.textContent;
                submitBtn.textContent = "Sending...";
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    showStep(2);
                }, 1500);
            }
        });
    }

    // Step 2: Verification code form submission
    if (codeForm) {
        codeForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const digitInputs = document.querySelectorAll(
                ".verification-digit"
            );
            const code = Array.from(digitInputs)
                .map((input) => input.value)
                .join("");

            if (validateCode(code)) {
                // Simulate code verification
                console.log("Verifying code:", code);

                // Show loading state briefly
                const submitBtn = codeForm.querySelector(
                    'button[type="submit"]'
                );
                const originalText = submitBtn.textContent;
                submitBtn.textContent = "Verifying...";
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    showStep(3);
                }, 1500);
            } else {
                // Show error state
                digitInputs.forEach((input) => {
                    input.classList.add("error");
                });

                const errorMessage = document.querySelector(
                    "#step2 .error-message"
                );
                errorMessage.style.display = "block";

                setTimeout(() => {
                    digitInputs.forEach((input) => {
                        input.classList.remove("error");
                    });
                    errorMessage.style.display = "none";
                }, 3000);
            }
        });
    }

    // Step 3: Password form submission
    if (passwordForm) {
        passwordForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const newPasswordInput = document.getElementById("newPassword");
            const confirmPasswordInput =
                document.getElementById("confirmPassword");
            const newPassword = newPasswordInput.value.trim();
            const confirmPassword = confirmPasswordInput.value.trim();

            if (validatePasswords(newPassword, confirmPassword)) {
                // Simulate password reset
                console.log("Resetting password for:", userEmail);

                // Show loading state briefly
                const submitBtn = passwordForm.querySelector(
                    'button[type="submit"]'
                );
                const originalText = submitBtn.textContent;
                submitBtn.textContent = "Resetting...";
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    showStep("success");
                }, 1500);
            }
        });
    }

    // Resend code functionality
    if (resendCodeLink) {
        resendCodeLink.addEventListener("click", function (e) {
            e.preventDefault();

            const originalText = resendCodeLink.textContent;
            resendCodeLink.textContent = "Sending...";
            resendCodeLink.style.pointerEvents = "none";

            setTimeout(() => {
                resendCodeLink.textContent = "Code sent! Check your email";

                setTimeout(() => {
                    resendCodeLink.textContent = originalText;
                    resendCodeLink.style.pointerEvents = "auto";
                }, 3000);
            }, 1500);
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
            showStep(currentStep - 1);
        } else {
            window.location.href = "../Login/login.php";
        }
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
                        codeForm.dispatchEvent(new Event("submit"));
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
                    codeForm.dispatchEvent(new Event("submit"));
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
});
