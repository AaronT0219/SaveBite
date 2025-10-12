<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Setup - SaveBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../../ForgotPassword/forgotPassword.css">
</head>
<body>
    <div class="logreg-box d-flex align-items-center vh-100 ms-auto" style="width: 500px;">

        <!-- Step 1: Verification Code Form -->
        <div id="step1" class="form-box step-form rounded shadow" style="width: 500px;">
            <i class="back-icon" data-lucide="chevron-left"></i>
            <form class="p-5 d-flex flex-column justify-content-center h-100" id="codeForm">
                <h1 class="mb-5 fw-bold text-center">Setup Two-Factor Authentication</h1>
                <div class="mb-3">
                    <p class="text-center">We've sent a 6-digit verification code to your email. Code expires in <span class="timer-text fw-bold text-danger">1:00</span>.</p>
                    <p class="text-center text-muted">Email: <span id="userEmail" class="fw-semibold"></span></p>
                </div>
                <div class="mb-4">
                    <div class="verification-code-container d-flex justify-content-center gap-3 mb-3">
                        <input type="text" class="verification-digit" data-index="0" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-digit" data-index="1" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-digit" data-index="2" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-digit" data-index="3" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-digit" data-index="4" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="verification-digit" data-index="5" maxlength="1" pattern="[0-9]" required>
                    </div>
                    <div class="error-message text-center" id="codeError"></div>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary w-100 py-2">Enable 2FA</button>
                </div>
                <div class="text-center">
                    <small><a href="#" id="resendCode" class="text-decoration-none fw-semibold resendCode">Didn't receive the code? Resend</a></small>
                </div>
            </form>
        </div>

        <!-- Success Message -->
        <div id="successStep" class="form-box step-form rounded shadow d-none" style="width: 500px;">
            <div class="p-5 d-flex flex-column justify-content-center h-100 text-center">
                <i data-lucide="shield-check" class="mx-auto mb-4" style="width: 64px; height: 64px; color: #37a98d;"></i>
                <h1 class="mb-4 fw-bold text-center text-success">Code Verified!</h1>
                <p class="mb-4">Your verification code is correct. To complete the 2FA setup, please set a new password for your account.</p>
                <div class="d-flex justify-content-center">
                    <button type="button" id="continueToPasswordBtn" class="btn btn-primary w-100 py-2">Continue to Password Setup</button>
                </div>
            </div>
        </div>

        <!-- Password Change Step -->
        <div id="passwordStep" class="form-box step-form rounded shadow d-none" style="width: 500px;">
            <form class="p-5 d-flex flex-column justify-content-center h-100" id="passwordForm">
                <h1 class="mb-4 fw-bold text-center">Set New Password</h1>
                <p class="text-center text-muted mb-4">Please create a new secure password for your account.</p>
                
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" required minlength="8">
                    <div class="form-text">Password must be at least 8 characters long.</div>
                </div>
                
                <div class="mb-4">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password" required>
                    <div class="error-message text-center" id="passwordError"></div>
                </div>
                
                <div class="mb-3 d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary w-100 py-2">Update Password</button>
                </div>
            </form>
        </div>

        <!-- Final Success Message -->
        <div id="finalSuccessStep" class="form-box step-form rounded shadow d-none" style="width: 500px;">
            <div class="p-5 d-flex flex-column justify-content-center h-100 text-center">
                <i data-lucide="check-circle" class="mx-auto mb-4" style="width: 64px; height: 64px; color: #28a745;"></i>
                <h1 class="mb-4 fw-bold text-center text-success">Two-Factor Authentication Enabled!</h1>
                <p class="mb-4">Congratulations! Your Two-Factor Authentication is now active and your password has been updated. Your SaveBite account is now more secure.</p>
                <div class="d-flex justify-content-center">
                    <a href="javascript:void(0)" id="backToSettingsBtn" class="btn btn-primary w-100 py-2 text-decoration-none">Back to Settings</a>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted">Redirecting to settings in <span id="redirectTimer" class="fw-bold text-primary">3</span> seconds...</small>
                </div>
            </div>
        </div>

        <!-- Already Enabled Message -->
        <div id="alreadyEnabledStep" class="form-box step-form rounded shadow d-none" style="width: 500px;">
            <div class="p-5 d-flex flex-column justify-content-center h-100 text-center">
                <i data-lucide="shield-check" class="mx-auto mb-4" style="width: 64px; height: 64px; color: #28a745;"></i>
                <h1 class="mb-4 fw-bold text-center text-success">2FA Already Enabled!</h1>
                <p class="mb-4">Two-Factor Authentication is already active on your account. Your SaveBite account is secure.</p>
                <div class="d-flex justify-content-center">
                    <a href="javascript:void(0)" id="backToSettingsBtnEnabled" class="btn btn-primary w-100 py-2 text-decoration-none">Back to Settings</a>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted">Redirecting to settings in <span id="redirectTimerEnabled" class="fw-bold text-primary">3</span> seconds...</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>lucide.createIcons();</script>
    <script src="2fa_verification.js"></script>
</body>
</html>