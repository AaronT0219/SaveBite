<?php
session_start();

// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
// echo "</div>";

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
                    <button class="btn btn-primary">Change Password</button>
                </div>
            </div>

        </div>
    </div>
    <div class="mt-4 px-4"> 
        <h3 class="fw-bold mb-3">Privacy & Security</h3>
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3 form-check form-switch">
                    <label class="form-check-label" for="switchCheckDefault">Enable Two-Factor Authentication</label>
                    <input type="checkbox" class="form-check-input" role="switch" id="switchCheckDefault">
                </div>
            </div>
        </div>
    </div>
</div>