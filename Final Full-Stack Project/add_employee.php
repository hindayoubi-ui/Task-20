
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
$email = "";
$phone = "";
$position = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    // Check empty fields
    if (
        $name == "" ||
        $email == "" ||
        $phone == "" ||
        $position == "" ||
        $password == "" ||
        $confirmPassword == ""
    ) {

        $message = "Please fill in all fields.";
        $messageType = "danger";

    // Validate email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";

    // Check password length
    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";
        $messageType = "danger";

    // Check password confirmation
    } elseif ($password != $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "danger";

    } else {

        // Check if email already exists
        $sql = "SELECT users_id FROM users WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "This email already exists.";
            $messageType = "danger";

            $stmt->close();

        } else {

            $stmt->close();

            // Encrypt password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert new employee
            $sql = "INSERT INTO users
                    (name, email, password, phone, position, role, status)
                    VALUES (?, ?, ?, ?, ?, 'employee', 'active')";

            // Prepare SQL statement
            $stmt = $conn->prepare($sql);

            // Bind form values
            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $hashedPassword,
                $phone,
                $position
            );

            // Execute query
            if ($stmt->execute()) {

                // Close statement and connection
                $stmt->close();
                $conn->close();

                // Return to employees page
                header("Location: employees.php?added=1");
                exit;

            } else {

                $message = "Error adding employee.";
                $messageType = "danger";

                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Employee</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body class="register-page">

    <!-- Registration Container -->
    <div class="register-container">

        <!-- Employee Card -->
        <div class="card register-card">

            <!-- Company Icon -->
            <div class="text-center mb-3">

                <i class="bi bi-person-badge"
                   style="font-size: 45px; color: var(--forest-teal);"></i>

            </div>

            <!-- Page Title -->
            <h2 class="register-title">
                Add New Employee
            </h2>

            <p class="register-subtitle">
                Create an employee account
            </p>

            <!-- Display Message -->
            <?php if ($message != "") { ?>

                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php } ?>

            <!-- Employee Form -->
            <form method="POST"
                  action="add_employee.php"
                  autocomplete="off">

                <!-- Name -->
                <div class="mb-3">

                    <label for="name" class="form-label">
                        <i class="bi bi-person"></i>
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        placeholder="Enter employee name"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >

                </div>

                <!-- Email -->
                <div class="mb-3">

                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i>
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter employee email"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >

                </div>

                <!-- Phone -->
                <div class="mb-3">

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

                <!-- Position -->
                <div class="mb-3">

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

                <!-- Password -->
                <div class="mb-3">

                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i>
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter password"
                        minlength="8"
                        required
                    >

                </div>

                <!-- Confirm Password -->
                <div class="mb-3">

                    <label for="confirm_password" class="form-label">
                        <i class="bi bi-shield-lock"></i>
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Confirm password"
                        minlength="8"
                        required
                    >

                </div>

                <!-- Form Buttons -->
                <button type="submit" class="btn register-button">

                    <i class="bi bi-person-plus"></i>
                    Add Employee

                </button>

                <a href="employees.php"
                   class="btn btn-outline-secondary w-100 mt-2">

                    <i class="bi bi-arrow-left"></i>
                    Back to Employees

                </a>

            </form>

        </div>

    </div>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
