
<?php
// Check if user is logged in
include("authentication.php");

// Include database connection
include("conn.php");

// Get total customers
$sql = "SELECT COUNT(*) AS total FROM customers";
$result = $conn->query($sql);
$customers = $result->fetch_assoc();
$totalCustomers = $customers["total"];

// Get total products
$sql = "SELECT COUNT(*) AS total FROM products";
$result = $conn->query($sql);
$products = $result->fetch_assoc();
$totalProducts = $products["total"];

// Get total orders
$sql = "SELECT COUNT(*) AS total FROM orders";
$result = $conn->query($sql);
$orders = $result->fetch_assoc();
$totalOrders = $orders["total"];

// Get total employees
$sql = "SELECT COUNT(*) AS total FROM users
        WHERE role = 'employee'";
$result = $conn->query($sql);
$employees = $result->fetch_assoc();
$totalEmployees = $employees["total"];

// Get total sales from completed orders
$sql = "SELECT SUM(total) AS total
        FROM orders
        WHERE status = 'Completed'";
$result = $conn->query($sql);
$sales = $result->fetch_assoc();
$totalSales = (float)($sales["total"] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Business Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Main Layout -->
    <div class="app-layout d-flex flex-column flex-md-row min-vh-100">

        <!-- Include Sidebar -->
        <?php include("common/sidebar.php"); ?>

        <!-- Main Content -->
        <main class="main-content">

            <div class="container-fluid py-4">

                <!-- Dashboard Heading -->
                <div class="mb-4">

                    <h2>Small Business Dashboard</h2>

                    <p class="text-muted mb-2">
                        Welcome,
                        <?php echo htmlspecialchars($_SESSION["name"]); ?>!
                    </p>

                    <span class="badge"
                          style="background-color: var(--forest-teal);">

                        <?php echo htmlspecialchars(ucfirst($_SESSION["role"])); ?>

                    </span>

                </div>

                <!-- Dashboard Statistics -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3
                            row-cols-xl-5 g-3">

                    <!-- Total Customers -->
                    <div class="col">
                        <div class="card shadow-sm border-0 h-100">

                            <div class="card-body">

                                <i class="bi bi-people fs-2"
                                   style="color: var(--forest-teal);"></i>

                                <h6 class="text-muted mt-2">
                                    Total Customers
                                </h6>

                                <h3><?php echo $totalCustomers; ?></h3>

                            </div>

                        </div>
                    </div>

                    <!-- Total Products -->
                    <div class="col">
                        <div class="card shadow-sm border-0 h-100">

                            <div class="card-body">

                                <i class="bi bi-box-seam fs-2"
                                   style="color: var(--soft-sage);"></i>

                                <h6 class="text-muted mt-2">
                                    Total Products
                                </h6>

                                <h3><?php echo $totalProducts; ?></h3>

                            </div>

                        </div>
                    </div>

                    <!-- Total Orders -->
                    <div class="col">
                        <div class="card shadow-sm border-0 h-100">

                            <div class="card-body">

                                <i class="bi bi-cart-check fs-2"
                                   style="color: var(--soft-gold);"></i>

                                <h6 class="text-muted mt-2">
                                    Total Orders
                                </h6>

                                <h3><?php echo $totalOrders; ?></h3>

                            </div>

                        </div>
                    </div>

                    <!-- Total Employees -->
                    <div class="col">
                        <div class="card shadow-sm border-0 h-100">

                            <div class="card-body">

                                <i class="bi bi-person-badge fs-2"
                                   style="color: var(--forest-teal);"></i>

                                <h6 class="text-muted mt-2">
                                    Total Employees
                                </h6>

                                <h3><?php echo $totalEmployees; ?></h3>

                            </div>

                        </div>
                    </div>

                    <!-- Total Sales -->
                    <div class="col">
                        <div class="card shadow-sm border-0 h-100">

                            <div class="card-body">

                                <i class="bi bi-currency-dollar fs-2"
                                   style="color: var(--deep-navy);"></i>

                                <h6 class="text-muted mt-2">
                                    Total Sales
                                </h6>

                                <h3>
                                    $<?php echo number_format($totalSales, 2); ?>
                                </h3>

                            </div>

                        </div>
                    </div>

                </div>

                <!-- Admin Management Links -->
                <?php if ($_SESSION["role"] == "admin") { ?>

                    <div class="card shadow-sm border-0 p-4 mt-4">

                        <h4 class="section-title mb-3">
                            Management
                        </h4>

                        <div class="d-flex flex-wrap gap-2">

                            <a href="customers.php" class="btn btn-primary">
                                <i class="bi bi-people"></i>
                                Customers
                            </a>

                            <a href="products.php" class="btn btn-primary">
                                <i class="bi bi-box-seam"></i>
                                Products
                            </a>

                            <a href="orders.php" class="btn btn-primary">
                                <i class="bi bi-cart-check"></i>
                                Orders
                            </a>

                            <a href="employees.php" class="btn btn-primary">
                                <i class="bi bi-person-badge"></i>
                                Employees
                            </a>

                        </div>

                    </div>

                <?php } else { ?>

                    <div class="alert alert-info mt-4">

                        You are logged in as an Employee.
                        Management pages are available to Admins only.

                    </div>

                <?php } ?>

            </div>

            <!-- Include Footer -->
            <?php include("common/footer.php"); ?>

        </main>

    </div>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
