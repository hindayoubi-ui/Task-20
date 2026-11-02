
<?php
// Start session
session_start();

// Include database connection
include("conn.php");

$message = "";

// Keep the entered values
$email = "";
$selectedRole = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $selectedRole = $_POST["role"] ?? "";

    // Check empty fields
    if ($email == "" || $password == "" || $selectedRole == "") {

        $message = "Please fill in all fields.";

    } elseif ($selectedRole != "admin" && $selectedRole != "employee") {

        $message = "Please select a valid role.";

    } else {

        // Find user by email using prepared statement
        $sql = "SELECT * FROM users WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Get user information
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user["password"])) {

                // Check if account is active
                if ($user["status"] == "active") {

                    // Check if selected role matches database role
                    if ($selectedRole == $user["role"]) {

                        // Save user information in session
                        session_regenerate_id(true);

                        $_SESSION["user_id"] = $user["users_id"];
                        $_SESSION["name"] = $user["name"];
                        $_SESSION["role"] = $user["role"];

                        // Redirect to dashboard
                        header("Location: dashboard.php");
                        exit;

                    } else {

                        $message = "Selected role does not match your account.";
                    }

                } else {

                    $message = "Your account is disabled.";
                }

            } else {

                $message = "Incorrect email or password.";
            }

        } else {

            $message = "Incorrect email or password.";
        }

        // Close statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <!-- Bootstrap CSS -->
    <link href="bootstrap.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

    <!-- Login Container -->
    <div class="login-container">

        <!-- Login Card -->
        <div class="card login-card">

            <!-- Company Icon -->
            <div class="text-center mb-3">
                <i class="bi bi-building"
                   style="font-size: 45px; color: var(--forest-teal);">
                </i>
            </div>

            <!-- Page Title -->
            <h2 class="login-title">
                Small Business
            </h2>

            <p class="login-subtitle">
                Management System
            </p>

            <!-- Display Message -->
            <?php if ($message != "") { ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php } ?>

            <!-- Login Form -->
            <form method="POST" action="login.php" autocomplete="off">

                <!-- Email -->
                <div class="mb-3">

                    <label for="email" class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required>

                </div>

                <!-- Password -->
                <div class="mb-3">

                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="new-password"
                        required>

                </div>

                <!-- Select Role -->
                <div class="mb-3">

                    <label for="role" class="form-label">
                        Login As
                    </label>

                    <select
                        id="role"
                        name="role"
                        class="form-select"
                        required>

                        <option value="">Select your role</option>

                        <option value="admin"
                            <?php if ($selectedRole == "admin") echo "selected"; ?>>
                            Admin
                        </option>

                        <option value="employee"
                            <?php if ($selectedRole == "employee") echo "selected"; ?>>
                            Employee
                        </option>

                    </select>

                </div>

                <!-- Login Button -->
                <button type="submit" class="btn login-button">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login
                </button>

            </form>

            <!-- Registration Link -->
            <p class="text-center mt-4 mb-0">
                Don't have an account?
                <a href="register.php">Register</a>
            </p>

        </div>

    </div>

</body>
</html>
