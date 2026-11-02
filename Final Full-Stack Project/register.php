<?php
// Include database connection
include("conn.php");

$message = "";
$messageType = "";

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
        $name == "" || $email == "" ||
        $phone == "" || $position == "" ||
        $password == "" || $confirmPassword == ""
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

            // Encrypt password
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Create Employee account
            $sql = "INSERT INTO users
                    (name, email, password, phone, position, role, status)
                    VALUES (?, ?, ?, ?, ?, 'employee', 'active')";

            $stmt->close();

            $stmt = $conn->prepare($sql);

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

                $message = "Registration successful! You can now log in.";
                $messageType = "success";
            } else {

                $message = "Error creating your account.";
                $messageType = "danger";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Employee Registration</title>

    <!-- Bootstrap CSS -->
    <link href="bootstrap.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>




<body class="register-page">

    <!-- Registration Container -->
    <div class="register-container">

        <!-- Registration Card -->
        <div class="card register-card">

            <!-- Company Icon -->
            <div class="text-center mb-3">
                <i class="bi bi-building"
                    style="font-size: 45px; color: var(--forest-teal);"></i>
            </div>

            <!-- Page Title -->
            <h2 class="register-title">
                Employee Registration
            </h2>

            <p class="register-subtitle">
                Join our business team
            </p>

            <!-- Display Message -->
            <?php if ($message != "") { ?>

                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php } ?>

            <!-- Registration Form -->
            <form method="POST" action="register.php" autocomplete="off">

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>

                    <input type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        placeholder="Enter your Name"
                        maxlength="100"
                        required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>

                    <input type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your Email"
                        maxlength="150"
                        required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>

                    <input type="text"
                        id="phone"
                        name="phone"
                        class="form-control"
                        maxlength="30"
                        placeholder="Enter your Number"
                        required>
                </div>

                <!-- Position -->
                <div class="mb-3">

                    <label for="position" class="form-label">
                        Position
                    </label>

                    <select
                        id="position"
                        name="position"
                        class="form-select"
                        required>

                           <option value="">Select Position</option>

        <option value="General Manager">General Manager - المدير العام</option>

        <option value="Operations Manager">Operations Manager - مدير العمليات</option>

        <option value="Sales Manager">Sales Manager - مدير المبيعات</option>

        <option value="Accountant">Accountant - محاسب</option>

        <option value="HR Manager">HR Manager - مدير الموارد البشرية</option>

        <option value="IT Assistant">IT Assistant - مساعد تقنية المعلومات</option>

        <option value="Inventory Manager">Inventory Manager - مدير المخزون</option>

        <option value="Sales Representative">Sales Representative - مندوب مبيعات</option>

        <option value="Customer Service Representative">Customer Service Representative - موظف خدمة العملاء</option>

        <option value="Cashier">Cashier - أمين صندوق</option>

                    </select>

                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>

                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter Your Password "
                        minlength="8"
                        required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">
                        Confirm Password
                    </label>

                    <input type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        minlength="8"
                        placeholder="Confirm Your Password"
                        required>
                </div>

                <!-- Register Button -->
                <button type="submit" class="btn register-button">
                    <i class="bi bi-person-plus"></i>
                    Register
                </button>

            </form>

            <!-- Login Link -->
            <p class="text-center mt-4 mb-0">
                Already have an account?
                <a href="login.php">Login</a>
            </p>

        </div>

    </div>

</body>

</html>