
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

$name = "";
$phone = "";
$email = "";
$address = "";

$isEditing = false;
$editId = 0;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get the form action
    $action = $_POST["action"] ?? "add";

    // Delete customer
    if ($action == "delete") {

        // Get customer ID
        $customerId = (int)($_POST["customer_id"] ?? 0);

        if ($customerId > 0) {

            // Delete customer using prepared statement
            $sql = "DELETE FROM customers WHERE customer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $customerId);

            // Execute delete query
            if ($stmt->execute()) {

                if ($stmt->affected_rows > 0) {
                    $message = "Customer deleted successfully!";
                    $messageType = "success";
                } else {
                    $message = "Customer not found.";
                    $messageType = "danger";
                }

            } else {

                $message = "Error deleting customer.";
                $messageType = "danger";
            }

            $stmt->close();

        } else {

            $message = "Invalid customer ID.";
            $messageType = "danger";
        }

    } else {

        // Get form values
        $name = trim($_POST["name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $address = trim($_POST["address"] ?? "");
        $editId = (int)($_POST["customer_id"] ?? 0);

        // Check if we are editing
        if ($action == "update") {
            $isEditing = true;
        }

        // Check empty fields
        if (
            $name == "" ||
            $phone == "" ||
            $email == "" ||
            $address == ""
        ) {

            $message = "Please fill in all fields.";
            $messageType = "danger";

        // Validate email
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $message = "Please enter a valid email address.";
            $messageType = "danger";

        } else {

            // Update customer
            if ($action == "update") {

                if ($editId > 0) {

                    $sql = "UPDATE customers
                            SET name = ?, phone = ?, email = ?, address = ?
                            WHERE customer_id = ?";

                    // Prepare SQL statement
                    $stmt = $conn->prepare($sql);

                    // Bind form values
                    $stmt->bind_param(
                        "ssssi",
                        $name,
                        $phone,
                        $email,
                        $address,
                        $editId
                    );

                    // Execute update query
                    if ($stmt->execute()) {

                        $message = "Customer updated successfully!";
                        $messageType = "success";

                    } else {

                        $message = "Error updating customer.";
                        $messageType = "danger";
                    }

                    $stmt->close();

                } else {

                    $message = "Invalid customer ID.";
                    $messageType = "danger";
                }

            } else {

                // Add new customer
                $sql = "INSERT INTO customers
                        (name, phone, email, address)
                        VALUES (?, ?, ?, ?)";

                // Prepare SQL statement
                $stmt = $conn->prepare($sql);

                // Bind form values
                $stmt->bind_param(
                    "ssss",
                    $name,
                    $phone,
                    $email,
                    $address
                );

                // Execute insert query
                if ($stmt->execute()) {

                    $message = "Customer added successfully!";
                    $messageType = "success";

                    // Clear form after successful addition
                    $name = "";
                    $phone = "";
                    $email = "";
                    $address = "";

                } else {

                    $message = "Error adding customer.";
                    $messageType = "danger";
                }

                $stmt->close();
            }
        }
    }
}

// Get customer information for editing
if (
    $_SERVER["REQUEST_METHOD"] != "POST" &&
    isset($_GET["edit"])
) {

    // Get customer ID
    $editId = (int)$_GET["edit"];

    if ($editId > 0) {

        // Select customer information
        $sql = "SELECT * FROM customers WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $editId);
        $stmt->execute();

        $resultEdit = $stmt->get_result();

        // Check if customer exists
        if ($resultEdit->num_rows == 1) {

            $customerEdit = $resultEdit->fetch_assoc();

            // Put current information in the form
            $name = $customerEdit["name"];
            $phone = $customerEdit["phone"];
            $email = $customerEdit["email"];
            $address = $customerEdit["address"];

            $isEditing = true;

        } else {

            $message = "Customer not found.";
            $messageType = "danger";
        }

        $stmt->close();

    } else {

        $message = "Invalid customer ID.";
        $messageType = "danger";
    }
}

// Get search value
$search = trim($_GET["search"] ?? "");

// Prepare search value
$searchTerm = "%" . $search . "%";

// Search customers by name, phone, email, or address
$sqlCustomers = "SELECT * FROM customers
                 WHERE name LIKE ?
                 OR phone LIKE ?
                 OR email LIKE ?
                 OR address LIKE ?
                 ORDER BY customer_id DESC";

// Prepare SQL statement
$stmtCustomers = $conn->prepare($sqlCustomers);

// Bind search values
$stmtCustomers->bind_param(
    "ssss",
    $searchTerm,
    $searchTerm,
    $searchTerm,
    $searchTerm
);

// Execute search query
$stmtCustomers->execute();

// Get search results
$resultCustomers = $stmtCustomers->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Management</title>

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
                        <i class="bi bi-people"></i>
                        Customer Management
                    </h2>

                    <p class="text-muted">
                        Add and manage your company customers.
                    </p>

                </div>

                <!-- Customer Form Card -->
                <div class="card shadow-sm border-0 p-4">

                    <!-- Form Heading -->
                    <h4 class="mb-4">

                        <?php if ($isEditing) { ?>

                            <i class="bi bi-pencil-square"></i>
                            Edit Customer

                        <?php } else { ?>

                            <i class="bi bi-person-plus"></i>
                            Add New Customer

                        <?php } ?>

                    </h4>

                    <!-- Display Message -->
                    <?php if ($message != "") { ?>

                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>

                    <?php } ?>

                    <!-- Customer Form -->
                    <form
                        method="POST"
                        action="<?php
                            echo $isEditing
                                ? 'customers.php?edit=' . $editId
                                : 'customers.php';
                        ?>"
                    >

                        <!-- Form Action -->
                        <input
                            type="hidden"
                            name="action"
                            value="<?php echo $isEditing ? 'update' : 'add'; ?>"
                        >

                        <!-- Customer ID for Editing -->
                        <?php if ($isEditing) { ?>

                            <input
                                type="hidden"
                                name="customer_id"
                                value="<?php echo $editId; ?>"
                            >

                        <?php } ?>

                        <div class="row g-3">

                            <!-- Customer Name -->
                            <div class="col-12 col-md-6">

                                <label for="name" class="form-label">
                                    <i class="bi bi-person"></i>
                                    Customer Name
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter customer name"
                                    maxlength="100"
                                    value="<?php echo htmlspecialchars($name); ?>"
                                    required
                                >

                            </div>

                            <!-- Phone -->
                            <div class="col-12 col-md-6">

                                <label for="phone" class="form-label">
                                    <i class="bi bi-telephone"></i>
                                    Phone
                                </label>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    placeholder="Enter phone number"
                                    maxlength="30"
                                    value="<?php echo htmlspecialchars($phone); ?>"
                                    required
                                >

                            </div>

                            <!-- Email -->
                            <div class="col-12 col-md-6">

                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i>
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter email address"
                                    maxlength="150"
                                    value="<?php echo htmlspecialchars($email); ?>"
                                    required
                                >

                            </div>

                            <!-- Address -->
                            <div class="col-12 col-md-6">

                                <label for="address" class="form-label">
                                    <i class="bi bi-geo-alt"></i>
                                    Address
                                </label>

                                <input
                                    type="text"
                                    id="address"
                                    name="address"
                                    class="form-control"
                                    placeholder="Enter customer address"
                                    maxlength="255"
                                    value="<?php echo htmlspecialchars($address); ?>"
                                    required
                                >

                            </div>

                        </div>

                        <!-- Form Buttons -->
                        <div class="mt-4">

                            <?php if ($isEditing) { ?>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i>
                                    Update Customer
                                </button>

                                <a href="customers.php"
                                   class="btn btn-outline-secondary">

                                    <i class="bi bi-x-circle"></i>
                                    Cancel

                                </a>

                            <?php } else { ?>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i>
                                    Add Customer
                                </button>

                            <?php } ?>

                        </div>

                    </form>

                </div>

                <!-- Customers Table Card -->
                <div class="card shadow-sm border-0 p-4 mt-4">

                    <!-- Table Heading -->
                    <h4 class="section-title mb-3">
                        <i class="bi bi-people"></i>
                        Customers List
                    </h4>

                    <!-- Search Form -->
                    <form method="GET" action="customers.php" class="mb-3">

                        <div class="row g-2">

                            <div class="col-12 col-md-8">

                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by name, phone, email, or address"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >

                            </div>

                            <div class="col-6 col-md-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100"
                                >

                                    <i class="bi bi-search"></i>
                                    Search

                                </button>

                            </div>

                            <div class="col-6 col-md-2">

                                <a
                                    href="customers.php"
                                    class="btn btn-outline-secondary w-100"
                                >

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
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if ($resultCustomers->num_rows > 0) { ?>

                                    <?php while ($customer = $resultCustomers->fetch_assoc()) { ?>

                                        <tr>

                                            <td>
                                                <?php echo $customer["customer_id"]; ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($customer["name"]); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($customer["phone"]); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($customer["email"]); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($customer["address"]); ?>
                                            </td>

                                            <!-- Edit and Delete Buttons -->
                                            <td class="text-nowrap">

                                                <!-- Edit Button - Green -->
                                                <a
                                                    href="customers.php?edit=<?php echo $customer["customer_id"]; ?>"
                                                    class="btn btn-sm btn-success"
                                                    title="Edit Customer"
                                                >

                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit

                                                </a>

                                                <!-- Delete Button - Red -->
                                                <form
                                                    method="POST"
                                                    action="customers.php"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Delete this customer? Related orders and order items may also be deleted. Continue?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="delete"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="customer_id"
                                                        value="<?php echo $customer["customer_id"]; ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-danger"
                                                        title="Delete Customer"
                                                    >

                                                        <i class="bi bi-trash"></i>
                                                        Delete

                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>

                                        <td colspan="6" class="text-center text-muted">
                                            No customers found.
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
// Close search statement
$stmtCustomers->close();

// Close database connection
$conn->close();
?>
