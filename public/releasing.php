<?php
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/public/login.php');
}

$currentPage = 'releasing';
$pageTitle = 'Releasing (Future Scope)';

ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Future Module</h5>
            <p class="mb-0">This module is scaffolded for future development. It will include:</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-clipboard-list"></i> Planned Features
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success"></i> 
                        Pick list generation
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success"></i> 
                        Barcode validation before release
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success"></i> 
                        FIFO/FEFO-ready logic
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success"></i> 
                        Release transaction logs
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success"></i> 
                        Inventory deduction
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success"></i> 
                        Shipping documentation
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-project-diagram"></i> Workflow Preview
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 border rounded">
                        <strong>1. Create Release Order</strong>
                        <p class="mb-0 small text-muted">Enter or scan release order number</p>
                    </div>
                    <div class="p-3 border rounded">
                        <strong>2. Generate Pick List</strong>
                        <p class="mb-0 small text-muted">Select parts and quantities to release (FIFO)</p>
                    </div>
                    <div class="p-3 border rounded">
                        <strong>3. Scan & Validate</strong>
                        <p class="mb-0 small text-muted">Scan each item before releasing</p>
                    </div>
                    <div class="p-3 border rounded">
                        <strong>4. Confirm Release</strong>
                        <p class="mb-0 small text-muted">Update inventory and log transaction</p>
                    </div>
                    <div class="p-3 border rounded">
                        <strong>5. Print Documents</strong>
                        <p class="mb-0 small text-muted">Generate shipping labels and documentation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-database"></i> Database Structure (Ready)
            </div>
            <div class="card-body">
                <p>The following tables are ready to support the releasing module:</p>
                <div class="row">
                    <div class="col-md-4">
                        <h6>Inventory Transactions</h6>
                        <ul class="small">
                            <li>Transaction tracking</li>
                            <li>Release type support</li>
                            <li>From/To location tracking</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Pallets & Items</h6>
                        <ul class="small">
                            <li>Pallet status management</li>
                            <li>Part number tracking</li>
                            <li>Quantity management</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Activity Logs</h6>
                        <ul class="small">
                            <li>Release activity tracking</li>
                            <li>User audit trail</li>
                            <li>Timestamp recording</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../app/views/layouts/main.php';
?>
