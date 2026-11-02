<!-- Sidebar -->
<aside class="app-sidebar">

    <!-- Project Name -->
    <div class="sidebar-brand">
        <i class="bi bi-building"></i>
        Small Business
    </div>

    <!-- Logged-in User -->
    <div class="sidebar-user">

        <i class="bi bi-person-circle"></i>

        <div>
            <strong>
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </strong>

            <small>
                <?php echo htmlspecialchars(ucfirst($_SESSION["role"])); ?>
            </small>
        </div>

    </div>

    <!-- Navigation Links -->
    <nav class="nav flex-column">

        <!-- Dashboard -->
        <a href="dashboard.php" class="nav-link">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        <!-- Admin Links -->
        <?php if ($_SESSION["role"] == "admin") { ?>

            <a href="customers.php" class="nav-link">
                <i class="bi bi-people"></i>
                Customers
            </a>

            <a href="products.php" class="nav-link">
                <i class="bi bi-box-seam"></i>
                Products
            </a>

            <a href="orders.php" class="nav-link">
                <i class="bi bi-cart-check"></i>
                Orders
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-person-badge"></i>
                Employees
            </a>

            <!-- Add New Admin -->
            <a href="add_admin.php" class="nav-link add-admin-link">
                <i class="bi bi-person-plus"></i>
                Add New Admin
            </a>

        <?php } ?>

    </nav>

    <!-- Logout -->
    <div class="sidebar-bottom">

        <a href="logout.php" class="nav-link logout-link">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>

    </div>

</aside>