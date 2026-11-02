<?php
// Check login
include("authentication.php");

// Allow Admin only
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
include("conn.php");

$message = "";
$messageType = "";

// Display success message
if (isset($_GET["success"])) {
    $message = "Admin account added successfully!";
    $messageType = "success";
}

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
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";
    } elseif ($password != $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "danger";
    } else {

        // Check if email already exists
        $sqlCheck = "SELECT users_id FROM users WHERE email = ?";

        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $email);
        $stmtCheck->execute();

        // Get query result
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {

            $message = "This email already exists.";
            $messageType = "danger";
        } else {

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Add new Admin account
            $sql = "INSERT INTO users
                    (name, email, password, phone, position, role, status)
                    VALUES (?, ?, ?, ?, ?, 'admin', 'active')";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $hashedPassword,
                $phone,
                $position
            );

            // Execute INSERT query
            if ($stmt->execute()) {

                // Redirect after successful insertion
                header("Location: add_admin.php?success=1");
                exit;
            } else {

                $message = "Error adding Admin account.";
                $messageType = "danger";
            }

            // Close statement
            $stmt->close();
        }

        // Close check statement
        $stmtCheck->close();
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Admin</title>

    <!-- Bootstrap CSS -->
    <link href="bootstrap.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="style.css?v=4">
</head>

<body class="admin-form-page">

    <!-- Main Container -->
    <div class="container admin-form-container">

        <!-- Form Card -->
        <div class="card admin-form-card">

            <!-- Header -->
            <div class="admin-form-header">

                <!-- Admin Icon -->
                <div class="admin-form-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>

                <!-- Page Title -->
                <h2 class="admin-form-title">
                    Add Admin
                </h2>

                <p class="admin-form-subtitle">
                    Create a new Admin account.
                </p>

            </div>

            <!-- Form Body -->
            <div class="admin-form-body">

                <!-- Display Message -->
                <?php if ($message != "") { ?>

                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php } ?>

                <!-- Add Admin Form -->
                <form method="POST" action="add_admin.php">

                    <div class="row">

                        <!-- Name -->
                        <div class="col-md-6 mb-3">

                            <label for="name" class="form-label">
                                <i class="bi bi-person"></i>
                                Name
                            </label>

                            <div class="input-group">


                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter your Name"
                                    required>

                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">

                            <label for="email" class="form-label">
                                <i class="bi bi-envelope"></i>
                                Email
                            </label>

                            <div class="input-group">



                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter your Email"
                                    required>

                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">

                            <label for="phone" class="form-label">
                                <i class="bi bi-telephone"></i>
                                Phone
                            </label>

                            <div class="input-group">



                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    placeholder="Enter your phone number"
                                    required>

                            </div>
                        </div>

                        <!-- Position -->
                        <div class="col-md-6 mb-3">

                            <label for="position" class="form-label">
                                <i class="bi bi-briefcase"></i>
                                Position
                            </label>

                            <div class="input-group">



                                <input
                                    type="text"
                                    id="position"
                                    name="position"
                                    class="form-control"
                                    placeholder="Enter position"
                                    required>

                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6 mb-3">

                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i>
                                Password
                            </label>

                            <div class="input-group">


                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Your Password"
                                    required>

                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6 mb-3">

                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-shield-lock"></i>
                                Confirm Password
                            </label>

                            <div class="input-group">



                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="Confirm your password"
                                    required>

                            </div>
                        </div>

                        <!-- Role -->
                        <div class="col-12 mb-4">

                            <label class="form-label">
                                <i class="bi bi-person-badge"></i>
                                Role
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="Admin"
                                disabled>

                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="admin-form-actions d-flex gap-2">

                        <button type="submit" class="btn admin-submit-btn">
                            <i class="bi bi-person-plus-fill"></i>
                            Add Admin
                        </button>

                        <a href="dashboard.php" class="btn admin-back-btn">
                            <i class="bi bi-arrow-left"></i>
                            Back to Dashboard
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>