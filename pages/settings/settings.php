<?php
session_start();
?>

<link rel="stylesheet" href="../pages/settings/settings.css">

<div class="container-fluid p-4">
    <div class="d-flex mb-2 py-3 px-4 bg-light rounded shadow">
        <h1 class="fw-bold">Settings</h1>
    </div>

    <div class="mt-4 px-4"> 
        <h3 class="fw-bold mb-3">Profile</h3>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-4 input-box">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" placeholder="Enter your username" required value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>">
                    <div class="error-message">Please enter a username</div>
                </div>
                <div class="mb-4 input-box">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Enter your email" required value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
                    <div class="error-message">Please enter a valid email address</div>
                </div>
                <div class="mb-4 input-box">
                    <label for="household_size" class="form-label">Household Size</label>
                    <input type="number" class="form-control" id="household_size" placeholder="Enter your household size" required value="<?php echo isset($_SESSION['household_size']) ? htmlspecialchars($_SESSION['household_size']) : ''; ?>">
                    <div class="error-message">Please enter a valid number (minimum 0)</div>
                </div>
                <div class="mb-4 input-box">
                    <button class="btn btn-password">Change Password</button>
                </div>
            </div>

        </div>
    </div>
    <div class="mt-4 px-4"> 
        <h3 class="fw-bold mb-3">Privacy & Security</h3>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3 form-check form-switch">
                    <label class="form-check-label" for="twoFactorSwitch">Enable Two-Factor Authentication</label>
                    <input type="checkbox" class="form-check-input" role="switch" id="twoFactorSwitch" <?php echo (isset($_SESSION['isAuthActive']) && $_SESSION['isAuthActive'] == 1) ? 'checked' : ''; ?>>
                    <div class="mt-2">
                        <small class="text-muted" id="twoFactorStatus">
                            <?php 
                                if (isset($_SESSION['isAuthActive']) && $_SESSION['isAuthActive'] == 1) {
                                    echo "Your account is protected with Two-Factor Authentication.";
                                } else {
                                    echo "Add an extra layer of security to your account.";
                                }
                            ?>
                        </small>
                    </div>
                    <!-- 2FA Notification Area -->
                    <div id="twoFactorNotification" class="mt-2" style="display: none;">
                        <div class="alert mb-0" role="alert">
                            <div class="d-flex justify-content-between align-items-center">
                                <span id="twoFactorNotificationMessage"></span>
                            </div>
                        </div>
                    </div>
                    <!-- 2FA Confirmation Area -->
                    <div id="twoFactorConfirmation" class="mt-2" style="display: none;">
                        <div class="alert alert-warning mb-0" role="alert">
                            <div class="d-flex justify-content-between align-items-center">
                                <span id="twoFactorConfirmationMessage"></span>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm me-2" id="confirmDisable2FA">Yes, Disable</button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="cancelDisable2FA">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>