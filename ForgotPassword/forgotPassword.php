<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="forgotPassword.css">

</head>
<body>
    <div class="logreg-box d-flex align-items-center vh-100 ms-auto" style="width: 500px;">

        <!-- Step 1: Email Form -->
        <div id="step1" class="form-box step-form rounded shadow" style="width: 500px;">
            <i class="back-icon" data-lucide="chevron-left"></i>
            <form class="p-5 d-flex flex-column justify-content-center h-100" id="emailForm">
                <h1 class="mb-5 fw-bold text-center">Reset Password</h1>
                <div class="mb-3">
                    <p class="text-center">Enter your email address below and we'll send you an email with 6 digit verification code to reset your password.</p>
                </div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="mail"></i>
                    <input type="email" id="email" class="py-2 pe-4" placeholder=" " required>
                    <label for="email">Email</label>
                    <div class="error-message">Please enter a valid email address</div>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary w-100 py-2">Send Code</button>
                </div>
            </form>
        </div>

        <!-- Step 2: Verification Code Form -->
        <div id="step2" class="form-box step-form rounded shadow d-none" style="width: 500px;">
            <i class="back-icon step-back" data-lucide="chevron-left"></i>
            <form class="p-5 d-flex flex-column justify-content-center h-100" id="codeForm">
                <h1 class="mb-5 fw-bold text-center">Verify Code</h1>
                <div class="mb-3">
                    <p class="text-center">We've sent a 6-digit verification code to your email. Code expires in <span class="timer-text fw-bold text-danger">1:00</span>.</p>
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
                    <div class="error-message text-center">Please enter a valid 6-digit code</div>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary w-100 py-2">Verify Code</button>
                </div>
                <div class="text-center">
                    <small><a href="#" id="resendCode" class="text-decoration-none fw-semibold resendCode">Didn't receive the code? Resend</a></small>
                </div>
            </form>
        </div>

        <!-- Step 3: New Password Form -->
        <div id="step3" class="form-box step-form rounded shadow d-none" style="width: 500px;">
            <i class="back-icon step-back" data-lucide="chevron-left"></i>
            <form class="p-5 d-flex flex-column justify-content-center h-100" id="passwordForm">
                <h1 class="mb-5 fw-bold text-center">New Password</h1>
                <div class="mb-3">
                    <p class="text-center">Please enter your new password below.</p>
                </div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="lock"></i>
                    <input type="password" id="newPassword" class="py-2 pe-4" placeholder=" " minlength="8" required>
                    <label for="newPassword">New Password</label>
                    <div class="error-message">Password must be at least 8 characters long</div>
                </div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="lock"></i>
                    <input type="password" id="confirmPassword" class="py-2 pe-4" placeholder=" " minlength="8" required>
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="error-message">Passwords do not match</div>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary w-100 py-2">Reset Password</button>
                </div>
            </form>
        </div>

        <!-- Success Message -->
        <div id="successStep" class="form-box step-form rounded shadow d-none" style="width: 500px;">
            <div class="p-5 d-flex flex-column justify-content-center h-100 text-center">
                <i data-lucide="check-circle" class="mx-auto mb-4" style="width: 64px; height: 64px; color: #37a98d;"></i>
                <h1 class="mb-4 fw-bold text-center text-success">Password Reset Successfully!</h1>
                <p class="mb-4">Your password has been successfully reset. You can now login with your new password.</p>
                <div class="d-flex justify-content-center">
                    <a href="../Login/login.php" id="loginRedirectBtn" class="btn btn-primary w-100 py-2 text-decoration-none">Go to Login</a>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted">Redirecting to login page in <span id="redirectTimer" class="fw-bold text-primary">3</span> seconds...</small>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
    lucide.createIcons();
    </script>
    <script src="forgotPassword.js"></script>
</body>
</html>