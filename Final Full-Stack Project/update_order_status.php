
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

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values
    $orderId = (int)($_POST["order_id"] ?? 0);
    $status = $_POST["status"] ?? "";

    // Available order statuses
    $allowedStatuses = [
        "Pending",
        "Processing",
        "Completed",
        "Cancelled"
    ];

    // Validate order ID and status
    if (
        $orderId <= 0 ||
        !in_array($status, $allowedStatuses, true)
    ) {

        $conn->close();

        header("Location: orders.php?error=invalid");
        exit;

    } else {

        // Update order status
        $sql = "UPDATE orders
                SET status = ?
                WHERE order_id = ?";

        // Prepare SQL statement
        $stmt = $conn->prepare($sql);

        // Bind form values
        $stmt->bind_param(
            "si",
            $status,
            $orderId
        );

        // Execute query
        if ($stmt->execute()) {

            if ($stmt->affected_rows >= 0) {

                $stmt->close();
                $conn->close();

                // Return to orders page
                header("Location: orders.php?status_updated=1");
                exit;

            }

        } else {

            $stmt->close();
            $conn->close();

            header("Location: orders.php?error=update");
            exit;
        }

        $stmt->close();
    }
    
} else {

    // Prevent direct access without POST
    $conn->close();

    header("Location: orders.php");
    exit;
}

$conn->close();
?>
