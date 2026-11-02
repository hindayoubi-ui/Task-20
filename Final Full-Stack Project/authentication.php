
<?php

// Start session if it is not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {

    // Redirect to login page
    header("Location: login.php");
    exit;
}

// Connect to database
$authConn = new mysqli("localhost", "root", "", "task 20");

// Check database connection
if ($authConn->connect_error) {
    die("Connection failed: " . $authConn->connect_error);
}

// Check user account status
$sql = "SELECT status FROM users WHERE users_id = ?";

$stmt = $authConn->prepare($sql);

$stmt->bind_param("i", $_SESSION["user_id"]);

$stmt->execute();

$result = $stmt->get_result();

// Check if account exists and is active
if ($result->num_rows != 1) {

    $stmt->close();
    $authConn->close();

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;

}

$user = $result->fetch_assoc();

// Close statement and connection
$stmt->close();
$authConn->close();

// Check account status
if ($user["status"] != "active") {

    session_unset();
    session_destroy();

    header("Location: login.php?disabled=1");
    exit;
}

?>
