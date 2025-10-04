<div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Food Inventory</h1>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Your Food Items</h5>
                    <button class="btn btn-primary">
                        <i data-lucide="plus" class="me-2"></i>Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Expiry Date</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Apples</td>
                                    <td>Fruits</td>
                                    <td>2025-10-15</td>
                                    <td>5</td>
                                    <td><span class="badge bg-success">Fresh</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1">
                                            <i data-lucide="edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Milk</td>
                                    <td>Dairy</td>
                                    <td>2025-10-08</td>
                                    <td>1</td>
                                    <td><span class="badge bg-warning">Expiring Soon</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1">
                                            <i data-lucide="edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>