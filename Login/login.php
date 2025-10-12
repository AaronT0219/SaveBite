<?php

function showError($error) {
        return !empty($error) ? "<p class='alert alert-danger text-center' role='alert'>$error</p>" : '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="login.css">

</head>
<body>
    <div class="logreg-box d-flex align-items-center vh-100 ms-auto" style="width: 500px; max-width: 100%;">

        <!-- Login Form -->
        <div class="form-box login rounded shadow" style="width: 480px; max-width: 100%;">
            <form id="loginForm" action="log_reg.php" method="post" class="p-5 d-flex flex-column justify-content-center h-100">
                <h1 class="mb-5 fw-bold text-center">Log In</h1>
                <div id="loginError" class="mb-4"></div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="mail"></i>
                    <input type="email" name="email" class="py-2 pe-4" placeholder=" " required>
                    <label for="email">Email</label>
                    <div class="error-message">Please enter a valid email address</div>
                </div>
                <div class="mb-3 input-box">
                    <i class="icon" data-lucide="lock"></i>
                    <input type="password" name="password" class="py-2 pe-4" placeholder=" " required>
                    <label for="password">Password</label>
                    <div class="error-message">Password is required</div>
                </div>
                <div class="mb-3">
                    <!-- <div>
                        <label>
                            <input type="checkbox" value="" id="" /> Remember me
                        </label>
                    </div> -->
                    <p><a href="../ForgotPassword/forgotPassword.php" class="link fw-semibold forgot-link">Forgot password?</a></p>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2">Log In</button>
                </div>
                <div>
                    <p class="text-center">Don't have an account? <a class="link fw-semibold register-link">Sign up</a></p>
                </div>
            </form>
        </div>

        <!-- Register Form -->
        <div class="form-box register rounded shadow" style="width: 480px; max-width: 100%;">
            <form id="registerForm" action="log_reg.php" method="post" class="p-5 d-flex flex-column justify-content-center h-100">
                <h1 class="mb-5 fw-bold text-center">Sign Up</h1>
                <div id="registerError" class="mb-4"></div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="user"></i>
                    <input type="text" name="fullname" class="py-2 pe-4" placeholder=" " required>
                    <label for="fullname">Full Name</label>
                    <div class="error-message">Full name is required</div>
                </div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="mail"></i>
                    <input type="email" name="email" class="py-2 pe-4" placeholder=" " required>
                    <label for="email">Email</label>
                    <div class="error-message">Please enter a valid email address</div>
                </div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="lock"></i>
                    <input type="password" name="password" class="py-2 pe-4" placeholder=" " minlength="8" required>
                    <label for="password">Password</label>
                    <div class="error-message">Password must be at least 8 characters long</div>
                </div>
                <div class="mb-5 input-box">
                    <i class="icon" data-lucide="house"></i>
                    <input type="number" name="household_size" min="0" class="py-2 pe-4" placeholder=" " value="0" required>
                    <label for="household_size">Household Size <span>(optional)</span></label>
                    <div class="error-message">Please enter a valid number (minimum 0)</div>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <button type="submit" name="register" class="btn btn-primary w-100 py-2">Sign Up</button>
                </div>
                <div>
                    <p class="text-center">Already have an account? <a class="link fw-semibold login-link">Log in</a></p>
                </div>
            </form>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
    lucide.createIcons();
    </script>
    <script src="login.js"></script>
</body>
</html>