
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

$productId = 0;
$name = "";
$category = "";
$price = "";
$quantity = "";
$description = "";
$imageName = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get product ID
    $productId = (int)($_POST["product_id"] ?? 0);

    // Get form values
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $description = trim($_POST["description"] ?? "");

    // Get current product image
    $sql = "SELECT products_image FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();

    $resultOld = $stmt->get_result();

    if ($resultOld->num_rows == 1) {

        $oldProduct = $resultOld->fetch_assoc();
        $oldImage = $oldProduct["products_image"];
        $imageName = $oldImage;

        $canUpdate = true;
        $newImageUploaded = false;

        // Check empty required fields
        if (
            $name == "" ||
            $category == "" ||
            $price == "" ||
            $quantity == ""
        ) {

            $message = "Please fill in all required fields.";
            $messageType = "danger";
            $canUpdate = false;

        // Validate price and quantity
        } elseif (
            !is_numeric($price) ||
            $price <= 0 ||
            filter_var($quantity, FILTER_VALIDATE_INT) === false ||
            $quantity < 0
        ) {

            $message = "Please enter a valid price and quantity.";
            $messageType = "danger";
            $canUpdate = false;
        }

        // Check if a new image was uploaded
        if (
            $canUpdate &&
            isset($_FILES["products_image"]) &&
            $_FILES["products_image"]["error"] != UPLOAD_ERR_NO_FILE
        ) {

            // Check upload error
            if ($_FILES["products_image"]["error"] != UPLOAD_ERR_OK) {

                $message = "Error uploading image.";
                $messageType = "danger";
                $canUpdate = false;

            // Check image size
            } elseif ($_FILES["products_image"]["size"] > 2097152) {

                $message = "Image size must not exceed 2 MB.";
                $messageType = "danger";
                $canUpdate = false;

            } else {

                // Check uploaded image
                $imageInfo = getimagesize(
                    $_FILES["products_image"]["tmp_name"]
                );

                if ($imageInfo === false) {

                    $message = "Please upload a valid image.";
                    $messageType = "danger";
                    $canUpdate = false;

                } else {

                    // Get image type
                    $imageType = $imageInfo["mime"];

                    // Allow JPG, PNG and WEBP
                    if ($imageType == "image/jpeg") {
                        $imageExtension = "jpg";
                    } elseif ($imageType == "image/png") {
                        $imageExtension = "png";
                    } elseif ($imageType == "image/webp") {
                        $imageExtension = "webp";
                    } else {

                        $message = "Only JPG, PNG and WEBP are allowed.";
                        $messageType = "danger";
                        $canUpdate = false;
                    }

                    // Save new image
                    if ($canUpdate) {

                        $uploadFolder = "uploads/products/";

                        $newImageName = uniqid("product_") . "." . $imageExtension;

                        if (move_uploaded_file(
                            $_FILES["products_image"]["tmp_name"],
                            $uploadFolder . $newImageName
                        )) {

                            $imageName = $newImageName;
                            $newImageUploaded = true;

                        } else {

                            $message = "Unable to save product image.";
                            $messageType = "danger";
                            $canUpdate = false;
                        }
                    }
                }
            }
        }

        // Update product information
        if ($canUpdate) {

            $sql = "UPDATE products
                    SET name = ?,
                        category = ?,
                        price = ?,
                        quantity = ?,
                        description = ?,
                        products_image = ?
                    WHERE product_id = ?";

            // Prepare SQL statement
            $stmtUpdate = $conn->prepare($sql);

            // Bind form values
            $stmtUpdate->bind_param(
                "ssdissi",
                $name,
                $category,
                $price,
                $quantity,
                $description,
                $imageName,
                $productId
            );

            // Execute update query
            if ($stmtUpdate->execute()) {

                $message = "Product updated successfully!";
                $messageType = "success";

                // Delete old image after successful update
                if (
                    $newImageUploaded &&
                    !empty($oldImage) &&
                    is_file("uploads/products/" . basename($oldImage))
                ) {

                    unlink("uploads/products/" . basename($oldImage));
                }

            } else {

                $message = "Error updating product.";
                $messageType = "danger";

                // Remove new image if update failed
                if ($newImageUploaded) {

                    unlink("uploads/products/" . $newImageName);
                }
            }

            $stmtUpdate->close();
        }

    } else {

        header("Location: products.php");
        exit;
    }

    $stmt->close();

} else {

    // Get product ID from URL
    $productId = (int)($_GET["id"] ?? 0);

    if ($productId <= 0) {
        header("Location: products.php");
        exit;
    }

    // Get product information
    $sql = "SELECT * FROM products WHERE product_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();

    $resultEdit = $stmt->get_result();

    // Check if product exists
    if ($resultEdit->num_rows == 1) {

        $product = $resultEdit->fetch_assoc();

        // Display current product information
        $name = $product["name"];
        $category = $product["category"];
        $price = $product["price"];
        $quantity = $product["quantity"];
        $description = $product["description"] ?? "";
        $imageName = $product["products_image"];

    } else {

        header("Location: products.php");
        exit;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Product</title>

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
                        Edit Product
                    </h2>

                    <p class="text-muted">
                        Update product information.
                    </p>

                </div>

                <!-- Edit Product Form -->
                <div class="card shadow-sm border-0 p-4">

                    <h4 class="mb-4">
                        <i class="bi bi-box-seam"></i>
                        Product Information
                    </h4>

                    <!-- Display Message -->
                    <?php if ($message != "") { ?>

                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>

                    <?php } ?>

                    <!-- Product Form -->
                    <form
                        method="POST"
                        action="edit_product.php?id=<?php echo $productId; ?>"
                        enctype="multipart/form-data"
                    >

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?php echo $productId; ?>"
                        >

                        <div class="row g-3">

                            <!-- Product Name -->
                            <div class="col-12 col-md-6">

                                <label for="name" class="form-label">
                                    <i class="bi bi-tag"></i>
                                    Product Name
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($name); ?>"
                                    maxlength="100"
                                    required
                                >

                            </div>

                            <!-- Category -->
                            <div class="col-12 col-md-6">

                                <label for="category" class="form-label">
                                    <i class="bi bi-grid"></i>
                                    Category
                                </label>

                                <input
                                    type="text"
                                    id="category"
                                    name="category"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($category); ?>"
                                    maxlength="100"
                                    required
                                >

                            </div>

                            <!-- Price -->
                            <div class="col-12 col-md-6">

                                <label for="price" class="form-label">
                                    <i class="bi bi-currency-dollar"></i>
                                    Price
                                </label>

                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($price); ?>"
                                    min="0.01"
                                    step="0.01"
                                    required
                                >

                            </div>

                            <!-- Quantity -->
                            <div class="col-12 col-md-6">

                                <label for="quantity" class="form-label">
                                    <i class="bi bi-boxes"></i>
                                    Quantity
                                </label>

                                <input
                                    type="number"
                                    id="quantity"
                                    name="quantity"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($quantity); ?>"
                                    min="0"
                                    step="1"
                                    required
                                >

                            </div>

                            <!-- Description -->
                            <div class="col-12">

                                <label for="description" class="form-label">
                                    <i class="bi bi-card-text"></i>
                                    Description
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    class="form-control"
                                    rows="3"
                                ><?php echo htmlspecialchars($description); ?></textarea>

                            </div>

                            <!-- Current Image -->
                            <div class="col-12">

                                <label class="form-label">
                                    <i class="bi bi-image"></i>
                                    Current Product Image
                                </label>

                                <div class="mb-3">

                                    <?php if (!empty($imageName)) { ?>

                                        <img
                                            src="uploads/products/<?php echo htmlspecialchars($imageName); ?>"
                                            alt="Product Image"
                                            width="120"
                                            height="120"
                                            style="object-fit: cover; border-radius: 8px;"
                                        >

                                    <?php } else { ?>

                                        <p class="text-muted">
                                            No image available.
                                        </p>

                                    <?php } ?>

                                </div>

                                <!-- New Image -->
                                <label for="products_image" class="form-label">
                                    Upload New Image
                                </label>

                                <input
                                    type="file"
                                    id="products_image"
                                    name="products_image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <small class="text-muted">
                                    Leave empty to keep the current image.
                                    JPG, PNG or WEBP. Maximum 2 MB.
                                </small>

                            </div>

                        </div>

                        <!-- Form Buttons -->
                        <div class="mt-4">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                Update Product
                            </button>

                            <a href="products.php"
                               class="btn btn-outline-secondary">

                                <i class="bi bi-arrow-left"></i>
                                Back to Products

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
