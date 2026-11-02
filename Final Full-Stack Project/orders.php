
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

$message = "";
$messageType = "";

// Check status update message
if (isset($_GET["status_updated"])) {

    $message = "Order status updated successfully!";
    $messageType = "success";

} elseif (isset($_GET["error"])) {

    $message = "Error updating order status.";
    $messageType = "danger";
}

// Get search value
$search = trim($_GET["search"] ?? "");

// Prepare search value
$searchTerm = "%" . $search . "%";

// Get all orders with customer names
$sqlOrders = "SELECT
                orders.order_id,
                orders.order_date,
                orders.total,
                orders.status,
                customers.name AS customer_name
              FROM orders
              LEFT JOIN customers
                ON orders.customer_id = customers.customer_id
              WHERE customers.name LIKE ?
              OR orders.status LIKE ?
              ORDER BY orders.order_id DESC";

// Prepare SQL statement
$stmtOrders = $conn->prepare($sqlOrders);

// Bind search values
$stmtOrders->bind_param(
    "ss",
    $searchTerm,
    $searchTerm
);

// Execute query
$stmtOrders->execute();

// Get search results
$resultOrders = $stmtOrders->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Orders Management</title>

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
                <div class="d-flex flex-column flex-md-row
                            justify-content-between align-items-md-center mb-4">

                    <div>

                        <h2 class="section-title">
                            <i class="bi bi-cart-check"></i>
                            Orders Management
                        </h2>

                        <p class="text-muted">
                            View and manage your company orders.
                        </p>

                    </div>

                    <!-- Add Order Button -->
                    <div class="mt-3 mt-md-0">

                        <a href="add_order.php" class="btn btn-primary">

                            <i class="bi bi-plus-circle"></i>
                            Add Order

                        </a>

                    </div>

                </div>

                <!-- Display Message -->
                <?php if ($message != "") { ?>

                    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">

                        <?php echo htmlspecialchars($message); ?>

                    </div>

                <?php } ?>

                <!-- Orders Table Card -->
                <div class="card shadow-sm border-0 p-4">

                    <!-- Table Heading -->
                    <h4 class="section-title mb-3">

                        <i class="bi bi-list-ul"></i>
                        Orders List

                    </h4>

                    <!-- Search Form -->
                    <form method="GET" action="orders.php" class="mb-3">

                        <div class="row g-2">

                            <!-- Search Input -->
                            <div class="col-12 col-md-8">

                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by customer name or status"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >

                            </div>

                            <!-- Search Button -->
                            <div class="col-6 col-md-2">

                                <button type="submit" class="btn btn-primary w-100">

                                    <i class="bi bi-search"></i>
                                    Search

                                </button>

                            </div>

                            <!-- Reset Button -->
                            <div class="col-6 col-md-2">

                                <a href="orders.php"
                                   class="btn btn-outline-secondary w-100">

                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    Reset

                                </a>

                            </div>

                        </div>

                    </form>

                    <!-- Responsive Table -->
                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Order Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if ($resultOrders->num_rows > 0) { ?>

                                    <?php while ($order = $resultOrders->fetch_assoc()) { ?>

                                        <tr>

                                            <!-- Order ID -->
                                            <td>
                                                <?php echo $order["order_id"]; ?>
                                            </td>

                                            <!-- Customer Name -->
                                            <td>
                                                <?php echo htmlspecialchars(
                                                    $order["customer_name"] ?? "Unknown"
                                                ); ?>
                                            </td>

                                            <!-- Order Date -->
                                            <td>
                                                <?php echo htmlspecialchars($order["order_date"]); ?>
                                            </td>

                                            <!-- Total -->
                                            <td>
                                                $<?php echo number_format(
                                                    (float)$order["total"],
                                                    2
                                                ); ?>
                                            </td>

                                            <!-- Update Order Status -->
                                            <td>

                                                <form
                                                    method="POST"
                                                    action="update_order_status.php"
                                                    class="d-flex flex-column flex-sm-row gap-2"
                                                >

                                                    <!-- Order ID -->
                                                    <input
                                                        type="hidden"
                                                        name="order_id"
                                                        value="<?php echo $order["order_id"]; ?>"
                                                    >

                                                    <!-- Status Options -->
                                                    <select
                                                        name="status"
                                                        class="form-select form-select-sm"
                                                        required
                                                    >

                                                        <option value="Pending"
                                                            <?php echo ($order["status"] == "Pending") ? "selected" : ""; ?>>
                                                            Pending
                                                        </option>

                                                        <option value="Processing"
                                                            <?php echo ($order["status"] == "Processing") ? "selected" : ""; ?>>
                                                            Processing
                                                        </option>

                                                        <option value="Completed"
                                                            <?php echo ($order["status"] == "Completed") ? "selected" : ""; ?>>
                                                            Completed
                                                        </option>

                                                        <option value="Cancelled"
                                                            <?php echo ($order["status"] == "Cancelled") ? "selected" : ""; ?>>
                                                            Cancelled
                                                        </option>

                                                    </select>

                                                    <!-- Update Button -->
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-primary text-nowrap"
                                                    >

                                                        <i class="bi bi-check-circle"></i>
                                                        Update

                                                    </button>

                                                </form>

                                            </td>

                                            <!-- Actions -->
                                            <td>

                                                <!-- View Button -->
                                                <a
                                                    href="view_order.php?id=<?php echo $order["order_id"]; ?>"
                                                    class="btn btn-sm btn-primary"
                                                    title="View Order"
                                                >

                                                    <i class="bi bi-eye"></i>
                                                    View

                                                </a>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>

                                        <td colspan="6"
                                            class="text-center text-muted">

                                            <i class="bi bi-inbox"></i>
                                            No orders found.

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!-- Footer -->
            <?php include("common/footer.php"); ?>

        </main>

    </div>

</body>
</html>

<?php
// Close statement
$stmtOrders->close();

// Close database connection
$conn->close();
?>
