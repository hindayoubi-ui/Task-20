
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

    // Get product ID
    $productId = (int)($_POST["product_id"] ?? 0);

    if ($productId > 0) {

        // Get product image
        $sql = "SELECT products_image FROM products
                WHERE product_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $product = $result->fetch_assoc();
            $imageName = $product["products_image"];

            $stmt->close();

            // Delete product from database
            $sql = "DELETE FROM products WHERE product_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {

                // Delete product image
                if (
                    !empty($imageName) &&
                    is_file("uploads/products/" . basename($imageName))
                ) {

                    unlink("uploads/products/" . basename($imageName));
                }

                // Close statement
                $stmt->close();

                // Redirect to products page
                $conn->close();

                header("Location: products.php?deleted=1");
                exit;

            } else {

                $stmt->close();

                $message = "Error deleting product.";
            }

        } else {

            $stmt->close();

            $message = "Product not found.";
        }

    } else {

        $message = "Invalid product ID.";
    }

} else {

    // Prevent direct access without POST
    header("Location: products.php");
    exit;
}

// Display error message
if (isset($message)) {

    echo htmlspecialchars($message);

}

// Close database connection
$conn->close();

?>
