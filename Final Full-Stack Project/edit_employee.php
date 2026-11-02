
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

// Get employee ID
$employeeId = (int)($_GET["id"] ?? $_POST["user_id"] ?? 0);

// Check employee ID
if ($employeeId <= 0) {
    header("Location: employees.php");
    exit;
}

// Get employee information
$sql = "SELECT *
        FROM users
        WHERE users_id = ?
        AND role = 'employee'";

$stmt = $conn->prepare($sql);

// Bind employee ID
$stmt->bind_param("i", $employeeId);

// Execute query
$stmt->execute();

// Get employee result
$result = $stmt->get_result();

// Check if employee exists
if ($result->num_rows == 1) {

    $employee = $result->fetch_assoc();

} else {

    $stmt->close();
    $conn->close();

    header("Location: employees.php");
    exit;
}

// Close statement
$stmt->close();

// Get current employee information
$name = $employee["name"];
$email = $employee["email"];
$phone = $employee["phone"];
$position = $employee["position"];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $position = trim($_POST["position"] ?? "");

    // Check empty fields
    if (
        $name == "" ||
        $email == "" ||
        $phone == "" ||
        $position == ""
    ) {

        $message = "Please fill in all fields.";
        $messageType = "danger";

    // Validate email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";

    } else {

        // Check if email already exists
        $sql = "SELECT users_id
                FROM users
                WHERE email = ?
                AND users_id != ?";

        $stmt = $conn->prepare($sql);

        // Bind email and employee ID
        $stmt->bind_param("si", $email, $employeeId);

        // Execute query
        $stmt->execute();

        $resultEmail = $stmt->get_result();

        if ($resultEmail->num_rows > 0) {

            $message = "This email already exists.";
            $messageType = "danger";

        } else {

            // Update employee information
            $sql = "UPDATE users
                    SET name = ?,
                        email = ?,
                        phone = ?,
                        position = ?
                    WHERE users_id = ?
                    AND role = 'employee'";

            // Prepare SQL statement
            $stmtUpdate = $conn->prepare($sql);

            // Bind form values
            $stmtUpdate->bind_param(
                "ssssi",
                $name,
                $email,
                $phone,
                $position,
                $employeeId
            );

            // Execute update query
            if ($stmtUpdate->execute()) {

                // Close statements and connection
                $stmtUpdate->close();
                $stmt->close();
                $conn->close();

                // Return to employees page
                header("Location: employees.php?updated=1");
                exit;

            } else {

                $message = "Error updating employee.";
                $messageType = "danger";
            }

            // Close update statement
            $stmtUpdate->close();
        }

        // Close email statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Employee</title>

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
                        <i class="bi bi-pencil-square"></i>
                        Edit Employee
                    </h2>

                    <p class="text-muted">
                        Update employee information.
                    </p>

                </div>

                <!-- Edit Employee Card -->
                <div class="card shadow-sm border-0 p-4">

                    <h4 class="mb-4">
                        <i class="bi bi-person-gear"></i>
                        Employee Information
                    </h4>

                    <!-- Display Message -->
                    <?php if ($message != "") { ?>

                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">

                            <?php echo htmlspecialchars($message); ?>

                        </div>

                    <?php } ?>

                    <!-- Employee Form -->
                    <form
                        method="POST"
                        action="edit_employee.php?id=<?php echo $employeeId; ?>"
                    >

                        <!-- Employee ID -->
                        <input
                            type="hidden"
                            name="user_id"
                            value="<?php echo $employeeId; ?>"
                        >

                        <div class="row g-3">

                            <!-- Name -->
                            <div class="col-12 col-md-6">

                                <label for="name" class="form-label">
                                    <i class="bi bi-person"></i>
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?php echo htmlspecialchars($name); ?>"
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
                                    maxlength="150"
                                    value="<?php echo htmlspecialchars($email); ?>"
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
                                    maxlength="30"
                                    value="<?php echo htmlspecialchars($phone); ?>"
                                    required
                                >

                            </div>

                            <!-- Position -->
                            <div class="col-12 col-md-6">

                                <label for="position" class="form-label">
                                    <i class="bi bi-briefcase"></i>
                                    Position
                                </label>

                                <select
                                    id="position"
                                    name="position"
                                    class="form-select"
                                    required
                                >

                                    <option value="">Select Position</option>

                                    <option value="General Manager"
                                        <?php echo ($position == "General Manager") ? "selected" : ""; ?>>
                                        General Manager - المدير العام
                                    </option>

                                    <option value="Operations Manager"
                                        <?php echo ($position == "Operations Manager") ? "selected" : ""; ?>>
                                        Operations Manager - مدير العمليات
                                    </option>

                                    <option value="Sales Manager"
                                        <?php echo ($position == "Sales Manager") ? "selected" : ""; ?>>
                                        Sales Manager - مدير المبيعات
                                    </option>

                                    <option value="Accountant"
                                        <?php echo ($position == "Accountant") ? "selected" : ""; ?>>
                                        Accountant - محاسب
                                    </option>

                                    <option value="HR Manager"
                                        <?php echo ($position == "HR Manager") ? "selected" : ""; ?>>
                                        HR Manager - مدير الموارد البشرية
                                    </option>

                                    <option value="IT Assistant"
                                        <?php echo ($position == "IT Assistant") ? "selected" : ""; ?>>
                                        IT Assistant - مساعد تقنية المعلومات
                                    </option>

                                    <option value="Inventory Manager"
                                        <?php echo ($position == "Inventory Manager") ? "selected" : ""; ?>>
                                        Inventory Manager - مدير المخزون
                                    </option>

                                    <option value="Sales Representative"
                                        <?php echo ($position == "Sales Representative") ? "selected" : ""; ?>>
                                        Sales Representative - مندوب مبيعات
                                    </option>

                                    <option value="Customer Service Representative"
                                        <?php echo ($position == "Customer Service Representative") ? "selected" : ""; ?>>
                                        Customer Service Representative - موظف خدمة العملاء
                                    </option>

                                    <option value="Cashier"
                                        <?php echo ($position == "Cashier") ? "selected" : ""; ?>>
                                        Cashier - أمين صندوق
                                    </option>

                                </select>

                            </div>

                        </div>

                        <!-- Form Buttons -->
                        <div class="mt-4">

                            <button type="submit" class="btn btn-primary">

                                <i class="bi bi-check-circle"></i>
                                Update Employee

                            </button>

                            <a href="employees.php"
                               class="btn btn-outline-secondary">

                                <i class="bi bi-arrow-left"></i>
                                Back to Employees

                            </a>

                        </div>

                    </form>

                </div>

            </div>

            <!-- Footer -->
            <?php include("common/footer.php"); ?>

        </main>

    </div>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
