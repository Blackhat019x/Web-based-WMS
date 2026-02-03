<div class="sidebar">
    <div class="sidebar-header">
        <h2>🏭 WMS</h2>
        <p><?php echo htmlspecialchars($_SESSION['role']); ?></p>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <i>📊</i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="modules/receiving.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'receiving.php' ? 'active' : ''; ?>">
                <i>📥</i>
                Receiving
            </a>
        </li>
        <li class="nav-item">
            <a href="modules/inventory.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'inventory.php' ? 'active' : ''; ?>">
                <i>📦</i>
                Inventory
            </a>
        </li>
        <li class="nav-item">
            <a href="modules/binning.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'binning.php' ? 'active' : ''; ?>">
                <i>🏢</i>
                Binning
            </a>
        </li>
        <li class="nav-item">
            <a href="modules/releasing.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'releasing.php' ? 'active' : ''; ?>">
                <i>📤</i>
                Releasing
            </a>
        </li>
        <li class="nav-item">
            <a href="modules/products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">
                <i>🔧</i>
                Products
            </a>
        </li>
        <li class="nav-item">
            <a href="modules/bins.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bins.php' ? 'active' : ''; ?>">
                <i>📍</i>
                Bins
            </a>
        </li>
    </ul>
</div>
