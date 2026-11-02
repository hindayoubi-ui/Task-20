
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

// Default form values
$customerId = "";
$orderDate = date("Y-m-d");
$status = "Pending";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values
    $customerId = (int)($_POST["customer_id"] ?? 0);
    $orderDate = $_POST["order_date"] ?? "";
    $status = $_POST["status"] ?? "";

    $productIds = $_POST["product_id"] ?? [];
    $quantities = $_POST["quantity"] ?? [];

    $orderItems = [];
    $total = 0;
    $canInsert = true;

    // Check customer and order information
    if (
        $customerId <= 0 ||
        $orderDate == "" ||
        !in_array($status, ["Pending", "Processing", "Completed", "Cancelled"], true)
    ) {

        $message = "Please complete all order information.";
        $messageType = "danger";
        $canInsert = false;
    }

    // Validate order date
    $dateCheck = DateTime::createFromFormat("Y-m-d", $orderDate);

    if (
        $canInsert &&
        (!$dateCheck || $dateCheck->format("Y-m-d") != $orderDate)
    ) {

        $message = "Please enter a valid order date.";
        $messageType = "danger";
        $canInsert = false;
    }

    // Check if customer exists
    if ($canInsert) {

        $sql = "SELECT customer_id FROM customers WHERE customer_id = ?";

        $stmtCustomer = $conn->prepare($sql);
        $stmtCustomer->bind_param("i", $customerId);
        $stmtCustomer->execute();

        $resultCustomer = $stmtCustomer->get_result();

        if ($resultCustomer->num_rows == 0) {

            $message = "Please select a valid customer.";
            $messageType = "danger";
            $canInsert = false;
        }

        $stmtCustomer->close();
    }

    // Check if products were selected
    if (
        $canInsert &&
        (
            !is_array($productIds) ||
            !is_array($quantities) ||
            count($productIds) == 0 ||
            count($productIds) != count($quantities)
        )
    ) {

        $message = "Please select at least one product.";
        $messageType = "danger";
        $canInsert = false;
    }

    // Get product prices from database
    if ($canInsert) {

        foreach ($productIds as $key => $productId) {

            // Get product ID and quantity
            $productId = (int)$productId;

            $quantity = filter_var(
                $quantities[$key] ?? null,
                FILTER_VALIDATE_INT
            );

            // Check product and quantity
            if ($productId <= 0 || $quantity === false || $quantity < 1) {

                $message = "Please select valid products and quantities.";
                $messageType = "danger";
                $canInsert = false;
                break;
            }

            // Get product price
            $sql = "SELECT price FROM products WHERE product_id = ?";

            $stmtProduct = $conn->prepare($sql);
            $stmtProduct->bind_param("i", $productId);
            $stmtProduct->execute();

            $resultProduct = $stmtProduct->get_result();

            if ($resultProduct->num_rows == 1) {

                $product = $resultProduct->fetch_assoc();

                $productPrice = (float)$product["price"];

                // Calculate item total
                $itemTotal = $productPrice * $quantity;

                // Calculate order total
                $total += $itemTotal;

                // Save item information
                $orderItems[] = [
                    "product_id" => $productId,
                    "quantity" => $quantity,
                    "price" => $productPrice
                ];

            } else {

                $message = "One of the selected products was not found.";
                $messageType = "danger";
                $canInsert = false;
            }

            $stmtProduct->close();

            if (!$canInsert) {
                break;
            }
        }
    }

    // Save order and order items
    if ($canInsert) {

        // Start transaction
        $conn->begin_transaction();

        // Insert order
        $sql = "INSERT INTO orders
                (customer_id, order_date, status, total)
                VALUES (?, ?, ?, ?)";

        $stmtOrder = $conn->prepare($sql);

        $stmtOrder->bind_param(
            "issd",
            $customerId,
            $orderDate,
            $status,
            $total
        );

        // Execute order query
        if ($stmtOrder->execute()) {

            // Get new order ID
            $orderId = $conn->insert_id;

            // Insert order items
            $sqlItem = "INSERT INTO order_items
                        (order_id, product_id, quantity, price)
                        VALUES (?, ?, ?, ?)";

            $stmtItem = $conn->prepare($sqlItem);

            $itemsSaved = true;

            // Insert every selected product
            foreach ($orderItems as $item) {

                $productId = $item["product_id"];
                $quantity = $item["quantity"];
                $price = $item["price"];

                $stmtItem->bind_param(
                    "iiid",
                    $orderId,
                    $productId,
                    $quantity,
                    $price
                );

                if (!$stmtItem->execute()) {

                    $itemsSaved = false;
                    break;
                }
            }

            // Check if all items were saved
            if ($itemsSaved) {

                // Save all changes
                $conn->commit();

                // Close statements
                $stmtItem->close();
                $stmtOrder->close();
                $conn->close();

                // Return to orders page
                header("Location: orders.php");
                exit;

            } else {

                // Cancel all changes
                $conn->rollback();

                $message = "Error adding order items.";
                $messageType = "danger";

                $stmtItem->close();
            }

        } else {

            // Cancel transaction
            $conn->rollback();

            $message = "Error creating order.";
            $messageType = "danger";
        }

        // Close statement
        $stmtOrder->close();
    }
}

// Get all customers
$sqlCustomers = "SELECT customer_id, name FROM customers ORDER BY name ASC";

$resultCustomers = $conn->query($sqlCustomers);

// Get all products
$sqlProducts = "SELECT product_id, name, price
                FROM products
                ORDER BY name ASC";

$resultProducts = $conn->query($sqlProducts);

// Save results in arrays
$customers = $resultCustomers->fetch_all(MYSQLI_ASSOC);
$products = $resultProducts->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Order</title>

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
                        <i class="bi bi-cart-plus"></i>
                        Add New Order
                    </h2>

                    <p class="text-muted">
                        Create a new customer order.
                    </p>

                </div>

                <!-- Order Form Card -->
                <div class="card shadow-sm border-0 p-4">

                    <h4 class="mb-4">
                        <i class="bi bi-receipt"></i>
                        Order Information
                    </h4>

                    <!-- Display Message -->
                    <?php if ($message != "") { ?>

                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>

                    <?php } ?>

                    <?php if (count($customers) == 0 || count($products) == 0) { ?>

                        <div class="alert alert-warning">
                            Please add at least one customer and one product
                            before creating an order.
                        </div>

                    <?php } else { ?>

                        <!-- Order Form -->
                        <form method="POST" action="add_order.php">

                            <div class="row g-3">

                                <!-- Customer -->
                                <div class="col-12 col-md-6">

                                    <label for="customer_id" class="form-label">
                                        <i class="bi bi-person"></i>
                                        Select Customer
                                    </label>

                                    <select
                                        id="customer_id"
                                        name="customer_id"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">Select Customer</option>

                                        <?php foreach ($customers as $customer) { ?>

                                            <option
                                                value="<?php echo $customer["customer_id"]; ?>"
                                                <?php echo ($customerId == $customer["customer_id"]) ? "selected" : ""; ?>
                                            >

                                                <?php echo htmlspecialchars($customer["name"]); ?>

                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- Order Date -->
                                <div class="col-12 col-md-6">

                                    <label for="order_date" class="form-label">
                                        <i class="bi bi-calendar"></i>
                                        Order Date
                                    </label>

                                    <input
                                        type="date"
                                        id="order_date"
                                        name="order_date"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($orderDate); ?>"
                                        required
                                    >

                                </div>

                                <!-- Order Status -->
                                <div class="col-12 col-md-6">

                                    <label for="status" class="form-label">
                                        <i class="bi bi-clipboard-check"></i>
                                        Order Status
                                    </label>

                                    <select
                                        id="status"
                                        name="status"
                                        class="form-select"
                                        required
                                    >

                                        <option value="Pending"
                                            <?php echo ($status == "Pending") ? "selected" : ""; ?>>
                                            Pending
                                        </option>

                                        <option value="Processing"
                                            <?php echo ($status == "Processing") ? "selected" : ""; ?>>
                                            Processing
                                        </option>

                                        <option value="Completed"
                                            <?php echo ($status == "Completed") ? "selected" : ""; ?>>
                                            Completed
                                        </option>

                                        <option value="Cancelled"
                                            <?php echo ($status == "Cancelled") ? "selected" : ""; ?>>
                                            Cancelled
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <!-- Order Products -->
                            <div class="mt-4">

                                <h5 class="mb-3">
                                    <i class="bi bi-box-seam"></i>
                                    Order Products
                                </h5>

                                <!-- Product Rows -->
                                <div id="orderItems">

                                    <div class="row g-2 align-items-end mb-3 product-row">

                                        <!-- Product Selection -->
                                        <div class="col-12 col-md-7">

                                            <label class="form-label">
                                                Product
                                            </label>

                                            <select
                                                name="product_id[]"
                                                class="form-select product-select"
                                                required
                                            >

                                                <option value="">Select Product</option>

                                                <?php foreach ($products as $product) { ?>

                                                    <option
                                                        value="<?php echo $product["product_id"]; ?>"
                                                        data-price="<?php echo $product["price"]; ?>"
                                                    >

                                                        <?php
                                                        echo htmlspecialchars($product["name"]);
                                                        echo " - $" . number_format((float)$product["price"], 2);
                                                        ?>

                                                    </option>

                                                <?php } ?>

                                            </select>

                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-8 col-md-3">

                                            <label class="form-label">
                                                Quantity
                                            </label>

                                            <input
                                                type="number"
                                                name="quantity[]"
                                                class="form-control quantity-input"
                                                min="1"
                                                step="1"
                                                value="1"
                                                required
                                            >

                                        </div>

                                        <!-- Remove Button -->
                                        <div class="col-4 col-md-2">

                                            <button
                                                type="button"
                                                class="btn btn-danger remove-product w-100"
                                            >

                                                <i class="bi bi-trash"></i>
                                                Remove

                                            </button>

                                        </div>

                                    </div>

                                </div>

                                <!-- Add Another Product -->
                                <button
                                    type="button"
                                    id="addProductRow"
                                    class="btn btn-outline-success mt-2"
                                >

                                    <i class="bi bi-plus-circle"></i>
                                    Add Another Product

                                </button>

                            </div>

                            <!-- Total Amount -->
                            <div class="card mt-4 p-3">

                                <div class="d-flex justify-content-between align-items-center">

                                    <h5 class="mb-0">
                                        Total Amount
                                    </h5>

                                    <h4 class="mb-0" style="color: var(--forest-teal);">

                                        $<span id="orderTotal">0.00</span>

                                    </h4>

                                </div>

                            </div>

                            <!-- Form Buttons -->
                            <div class="mt-4">

                                <button type="submit" class="btn btn-primary">

                                    <i class="bi bi-check-circle"></i>
                                    Create Order

                                </button>

                                <a href="orders.php" class="btn btn-outline-secondary">

                                    <i class="bi bi-arrow-left"></i>
                                    Back to Orders

                                </a>

                            </div>

                        </form>

                    <?php } ?>

                </div>

            </div>

            <!-- Footer -->
            <?php include("common/footer.php"); ?>

        </main>

    </div>

    <!-- Product Row Template -->
    <template id="productRowTemplate">

        <div class="row g-2 align-items-end mb-3 product-row">

            <!-- Product Selection -->
            <div class="col-12 col-md-7">

                <label class="form-label">
                    Product
                </label>

                <select
                    name="product_id[]"
                    class="form-select product-select"
                    required
                >

                    <option value="">Select Product</option>

                    <?php foreach ($products as $product) { ?>

                        <option
                            value="<?php echo $product["product_id"]; ?>"
                            data-price="<?php echo $product["price"]; ?>"
                        >

                            <?php
                            echo htmlspecialchars($product["name"]);
                            echo " - $" . number_format((float)$product["price"], 2);
                            ?>

                        </option>

                    <?php } ?>

                </select>

            </div>

            <!-- Quantity -->
            <div class="col-8 col-md-3">

                <label class="form-label">
                    Quantity
                </label>

                <input
                    type="number"
                    name="quantity[]"
                    class="form-control quantity-input"
                    min="1"
                    step="1"
                    value="1"
                    required
                >

            </div>

            <!-- Remove Button -->
            <div class="col-4 col-md-2">

                <button
                    type="button"
                    class="btn btn-danger remove-product w-100"
                >

                    <i class="bi bi-trash"></i>
                    Remove

                </button>

            </div>

        </div>

    </template>

    <!-- JavaScript -->
    <script>

        // Get page elements
        const orderItems = document.getElementById("orderItems");

        const addProductRow = document.getElementById("addProductRow");

        const productRowTemplate = document.getElementById("productRowTemplate");

        // Add another product row
        addProductRow.addEventListener("click", function () {

            // Copy product row template
            const newRow = productRowTemplate.content.cloneNode(true);

            // Add row to the form
            orderItems.appendChild(newRow);

            // Recalculate total
            calculateTotal();

        });

        // Remove product row
        orderItems.addEventListener("click", function (event) {

            if (event.target.closest(".remove-product")) {

                const productRows = orderItems.querySelectorAll(".product-row");

                if (productRows.length > 1) {

                    event.target.closest(".product-row").remove();

                    // Recalculate total
                    calculateTotal();

                } else {

                    alert("At least one product is required.");

                }
            }

        });

        // Calculate order total
        function calculateTotal() {

            let total = 0;

            // Get all product rows
            const productRows = orderItems.querySelectorAll(".product-row");

            productRows.forEach(function (row) {

                // Get selected product
                const productSelect = row.querySelector(".product-select");

                // Get quantity
                const quantityInput = row.querySelector(".quantity-input");

                const selectedOption = productSelect.options[productSelect.selectedIndex];

                if (selectedOption && selectedOption.dataset.price) {

                    const price = parseFloat(selectedOption.dataset.price);

                    const quantity = parseInt(quantityInput.value) || 0;

                    total += price * quantity;

                }

            });

            // Display total
            document.getElementById("orderTotal").textContent = total.toFixed(2);

        }

        // Update total when product or quantity changes
        orderItems.addEventListener("change", calculateTotal);

        orderItems.addEventListener("input", calculateTotal);

    </script>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
