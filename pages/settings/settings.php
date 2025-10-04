<link rel="stylesheet" href="../pages/settings/settings.css">

<div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Settings</h1>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Profile Settings</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" value="user@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="fullName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="fullName" value="John Doe">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="john@example.com">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Account Actions</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-outline-warning w-100 mb-2">
                                <i data-lucide="key" class="me-2"></i>Change Password
                            </button>
                            <button class="btn btn-outline-info w-100 mb-2">
                                <i data-lucide="download" class="me-2"></i>Export Data
                            </button>
                            <button class="btn btn-outline-danger w-100">
                                <i data-lucide="trash-2" class="me-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>