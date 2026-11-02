
<?php
// Check if user is logged in
include("authentication.php");

// Allow Admin only
if ($_SESSION["role"] != "admin") {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
include("conn.php");

// Get order ID from URL
$orderId = (int)($_GET["id"] ?? 0);

// Check order ID
if ($orderId <= 0) {
    header("Location: orders.php");
    exit;
}

// Get order information and customer name
$sql = "SELECT
            orders.order_id,
            orders.order_date,
            orders.total,
            orders.status,
            customers.name AS customer_name
        FROM orders
        LEFT JOIN customers
            ON orders.customer_id = customers.customer_id
        WHERE orders.order_id = ?";

// Prepare SQL statement
$stmtOrder = $conn->prepare($sql);

// Bind order ID
$stmtOrder->bind_param("i", $orderId);

// Execute query
$stmtOrder->execute();

// Get order result
$resultOrder = $stmtOrder->get_result();

// Check if order exists
if ($resultOrder->num_rows == 1) {

    $order = $resultOrder->fetch_assoc();

} else {

    $stmtOrder->close();
    $conn->close();

    header("Location: orders.php");
    exit;
}

// Close statement
$stmtOrder->close();

// Get order products
$sqlItems = "SELECT
                order_items.product_id,
                order_items.quantity,
                order_items.price,
                products.name AS product_name
             FROM order_items
             LEFT JOIN products
                ON order_items.product_id = products.product_id
             WHERE order_items.order_id = ?";

// Prepare SQL statement
$stmtItems = $conn->prepare($sqlItems);

// Bind order ID
$stmtItems->bind_param("i", $orderId);

// Execute query
$stmtItems->execute();

// Get order items
$resultItems = $stmtItems->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Order</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Main Layout -->
    <div class="app-layout d-flex flex-column flex-md-row min-vh-100">

        <!-- Sidebar -->
        <?php include("common/sidebar.php"); ?>

        <!-- Main Content -->
        <main class="main-content">

            <div class="container-fluid py-4">

                <!-- Page Heading -->
                <div class="mb-4">

                    <h2 class="section-title">
                        <i class="bi bi-receipt"></i>
                        Order Details
                    </h2>

                    <p class="text-muted">
                        View complete information about this order.
                    </p>

                </div>

                <!-- Order Information Card -->
                <div class="card shadow-sm border-0 p-4 mb-4">

                    <h4 class="mb-4">
                        <i class="bi bi-info-circle"></i>
                        Order Information
                    </h4>

                    <div class="row g-3">

                        <!-- Order ID -->
                        <div class="col-12 col-md-6">

                            <strong>Order ID:</strong>

                            <p>
                                #<?php echo $order["order_id"]; ?>
                            </p>

                        </div>

                        <!-- Customer -->
                        <div class="col-12 col-md-6">

                            <strong>Customer:</strong>

                            <p>
                                <?php echo htmlspecialchars(
                                    $order["customer_name"] ?? "Unknown"
                                ); ?>
                            </p>

                        </div>

                        <!-- Order Date -->
                        <div class="col-12 col-md-6">

                            <strong>Order Date:</strong>

                            <p>
                                <?php echo htmlspecialchars($order["order_date"]); ?>
                            </p>

                        </div>

                        <!-- Order Status -->
                        <div class="col-12 col-md-6">

                            <strong>Status:</strong>

                            <p>

                                <?php
                                $statusColors = [
                                    "Pending" => "warning",
                                    "Processing" => "info",
                                    "Completed" => "success",
                                    "Cancelled" => "danger"
                                ];

                                $statusColor = $statusColors[$order["status"]] ?? "secondary";
                                ?>

                                <span class="badge bg-<?php echo $statusColor; ?>">
                                    <?php echo htmlspecialchars($order["status"]); ?>
                                </span>

                            </p>

                        </div>

                    </div>

                </div>

                <!-- Order Products Card -->
                <div class="card shadow-sm border-0 p-4">

                    <h4 class="mb-4">
                        <i class="bi bi-box-seam"></i>
                        Order Products
                    </h4>

                    <!-- Responsive Table -->
                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if ($resultItems->num_rows > 0) { ?>

                                    <?php while ($item = $resultItems->fetch_assoc()) { ?>

                                        <tr>

                                            <!-- Product ID -->
                                            <td>
                                                <?php echo $item["product_id"]; ?>
                                            </td>

                                            <!-- Product Name -->
                                            <td>
                                                <?php echo htmlspecialchars(
                                                    $item["product_name"] ?? "Unknown Product"
                                                ); ?>
                                            </td>

                                            <!-- Price -->
                                            <td>
                                                $<?php echo number_format(
                                                    (float)$item["price"],
                                                    2
                                                ); ?>
                                            </td>

                                            <!-- Quantity -->
                                            <td>
                                                <?php echo $item["quantity"]; ?>
                                            </td>

                                            <!-- Subtotal -->
                                            <td>
                                                $<?php echo number_format(
                                                    (float)$item["price"] * (int)$item["quantity"],
                                                    2
                                                ); ?>
                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>

                                        <td colspan="5" class="text-center text-muted">
                                            No products found for this order.
                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                    <!-- Order Total -->
                    <div class="text-end mt-3">

                        <h4 style="color: var(--forest-teal);">

                            Total:
                            $<?php echo number_format((float)$order["total"], 2); ?>

                        </h4>

                    </div>

                </div>

                <!-- Back Button -->
                <div class="mt-4">

                    <a href="orders.php" class="btn btn-primary">

                        <i class="bi bi-arrow-left"></i>
                        Back to Orders

                    </a>

                </div>

            </div>

            <!-- Footer -->
            <?php include("common/footer.php"); ?>

        </main>

    </div>

</body>
</html>

<?php
// Close statements
$stmtItems->close();

// Close database connection
$conn->close();
?>
