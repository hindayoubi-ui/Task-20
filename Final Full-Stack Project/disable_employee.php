
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

    // Get employee ID
    $userId = (int)($_POST["user_id"] ?? 0);

    // Check employee ID
    if ($userId > 0) {

        // Check if employee exists
        $sql = "SELECT users_id
                FROM users
                WHERE users_id = ?
                AND role = 'employee'";

        // Prepare SQL statement
        $stmt = $conn->prepare($sql);

        // Bind employee ID
        $stmt->bind_param("i", $userId);

        // Execute query
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $stmt->close();

            // Disable employee account
            $sql = "UPDATE users
                    SET status = 'disabled'
                    WHERE users_id = ?
                    AND role = 'employee'";

            // Prepare SQL statement
            $stmt = $conn->prepare($sql);

            // Bind employee ID
            $stmt->bind_param("i", $userId);

            // Execute update query
            if ($stmt->execute()) {

                // Close statement and connection
                $stmt->close();
                $conn->close();

                // Return to employees page
                header("Location: employees.php?disabled=1");
                exit;

            } else {

                $stmt->close();
                $conn->close();

                header("Location: employees.php?error=1");
                exit;
            }

        } else {

            $stmt->close();
            $conn->close();

            header("Location: employees.php?error=1");
            exit;
        }

    } else {

        $conn->close();

        header("Location: employees.php?error=1");
        exit;
    }

} else {

    // Prevent direct access without POST
    $conn->close();

    header("Location: employees.php");
    exit;
}

?>
