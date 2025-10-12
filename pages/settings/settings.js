// init function for page loader to run
function initSettingsPage() {
    init2FAToggle();
}

function init2FAToggle() {
    const twoFactorSwitch = document.getElementById("twoFactorSwitch");
    const twoFactorStatus = document.getElementById("twoFactorStatus");

    if (!twoFactorSwitch) {
        return;
    }

    // Get the current state from the switch (which is set by PHP based on session)
    let current2FAState = twoFactorSwitch.checked;

    // Add handlers for confirmation buttons
    const confirmButton = document.getElementById("confirmDisable2FA");
    const cancelButton = document.getElementById("cancelDisable2FA");

    if (confirmButton) {
        confirmButton.addEventListener("click", function () {
            hide2FAConfirmation();
            performDisable2FA(twoFactorSwitch);
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener("click", function () {
            hide2FAConfirmation();
            twoFactorSwitch.checked = true; // Revert switch
        });
    }

    twoFactorSwitch.addEventListener("change", async function (e) {
        const isEnabled = e.target.checked;

        if (isEnabled && !current2FAState) {
            // User wants to enable 2FA
            await handle2FAEnable(e.target);
        } else if (!isEnabled && current2FAState) {
            // User wants to disable 2FA
            await handle2FADisable(e.target);
        }
    });
}

async function handle2FAEnable(switchElement) {
    try {
        // Get user email from the email input
        const emailInput = document.getElementById("email");
        const userEmail = emailInput ? emailInput.value : "";

        if (!userEmail) {
            show2FANotification(
                "Email not found. Please refresh the page.",
                "error"
            );
            switchElement.checked = false;
            return;
        }

        // Show loading state
        switchElement.disabled = true;
        updateStatusText(false, "Setting up Two-Factor Authentication...");

        // Request 2FA setup - fix the path to be relative to the project root
        const response = await fetch("../pages/settings/2fa_handler.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "request_2fa_setup",
                email: userEmail,
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Reset switch state and show success message
            switchElement.checked = false;
            updateStatusText(
                false,
                "Check your email for the 2FA setup instructions."
            );
            show2FANotification(
                "2FA setup email sent! Please check your email and click the link to complete setup.",
                "success"
            );
        } else {
            switchElement.checked = false;
            updateStatusText(false);
            show2FANotification(
                data.error || "Failed to start 2FA setup",
                "error"
            );
        }
    } catch (error) {
        switchElement.checked = false;
        updateStatusText(false);
        show2FANotification("An error occurred: " + error.message, "error");
    } finally {
        switchElement.disabled = false;
    }
}

async function handle2FADisable(switchElement) {
    // Show custom confirmation dialog
    show2FAConfirmation(
        "Are you sure you want to disable Two-Factor Authentication? This will make your account less secure."
    );
}

async function performDisable2FA(switchElement) {
    try {
        // Get user email from the email input
        const emailInput = document.getElementById("email");
        const userEmail = emailInput ? emailInput.value : "";

        if (!userEmail) {
            show2FANotification(
                "Email not found. Please refresh the page.",
                "error"
            );
            switchElement.checked = true;
            return;
        }

        // Show loading state
        switchElement.disabled = true;
        updateStatusText(true, "Disabling Two-Factor Authentication...");

        // Disable 2FA - fix the path to be relative to the project root
        const response = await fetch("../pages/settings/2fa_handler.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "disable_2fa",
                email: userEmail,
            }),
        });

        const data = await response.json();

        if (data.success) {
            switchElement.checked = false;
            updateStatusText(false);
            show2FANotification(
                "Two-Factor Authentication disabled successfully.",
                "success"
            );

            // Update the current state locally
            current2FAState = false;

            // Refresh page to update session data after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            switchElement.checked = true;
            updateStatusText(true);
            show2FANotification(data.error || "Failed to disable 2FA", "error");
        }
    } catch (error) {
        switchElement.checked = true;
        updateStatusText(true);
        show2FANotification("An error occurred. Please try again.", "error");
    } finally {
        switchElement.disabled = false;
    }
}

function updateStatusText(isEnabled, customMessage = null) {
    const statusElement = document.getElementById("twoFactorStatus");
    if (!statusElement) return;

    if (customMessage) {
        statusElement.textContent = customMessage;
        statusElement.className = "text-info";
    } else if (isEnabled) {
        statusElement.textContent =
            "Your account is protected with Two-Factor Authentication.";
        statusElement.className = "text-success";
    } else {
        statusElement.textContent =
            "Add an extra layer of security to your account.";
        statusElement.className = "text-muted";
    }
}

function show2FANotification(message, type = "info") {
    const notificationArea = document.getElementById("twoFactorNotification");
    const messageElement = document.getElementById(
        "twoFactorNotificationMessage"
    );
    const alertElement = notificationArea.querySelector(".alert");

    if (!notificationArea || !messageElement || !alertElement) {
        console.error("Notification elements not found");
        return;
    }

    // Set the message
    messageElement.textContent = message;

    // Update alert styling based on type
    alertElement.className = `alert mb-0 alert-${
        type === "error" ? "danger" : type
    }`;

    // Show the notification
    notificationArea.style.display = "block";

    // Auto hide after 6 seconds (same as disable message)
    setTimeout(() => {
        hide2FANotification();
    }, 6000);
}

function hide2FANotification() {
    const notificationArea = document.getElementById("twoFactorNotification");

    if (!notificationArea) return;

    // Simple hide without animation
    notificationArea.style.display = "none";
}

function show2FAConfirmation(message) {
    const confirmationArea = document.getElementById("twoFactorConfirmation");
    const messageElement = document.getElementById(
        "twoFactorConfirmationMessage"
    );

    if (!confirmationArea || !messageElement) {
        console.error("Confirmation elements not found");
        return;
    }

    // Hide any existing notifications first
    hide2FANotification();

    // Set the message
    messageElement.textContent = message;

    // Show the confirmation
    confirmationArea.style.display = "block";
}

function hide2FAConfirmation() {
    const confirmationArea = document.getElementById("twoFactorConfirmation");

    if (!confirmationArea) return;

    // Simple hide without animation
    confirmationArea.style.display = "none";
}

window.initSettingsPage = initSettingsPage;
