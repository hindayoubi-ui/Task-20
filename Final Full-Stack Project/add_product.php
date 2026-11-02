
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

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form values
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $description = trim($_POST["description"] ?? "");

    $imageName = null;
    $canInsert = true;

    // Check empty required fields
    if (
        $name == "" ||
        $category == "" ||
        $price == "" ||
        $quantity == ""
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "danger";
        $canInsert = false;

    // Validate price and quantity
    } elseif (
        !is_numeric($price) ||
        $price <= 0 ||
        filter_var($quantity, FILTER_VALIDATE_INT) === false ||
        $quantity < 0
    ) {

        $message = "Please enter a valid price and quantity.";
        $messageType = "danger";
        $canInsert = false;
    }

    // Check if an image was uploaded
    if ($canInsert && isset($_FILES["products_image"])) {

        if ($_FILES["products_image"]["error"] != UPLOAD_ERR_NO_FILE) {

            // Check upload error
            if ($_FILES["products_image"]["error"] != UPLOAD_ERR_OK) {

                $message = "Error uploading image.";
                $messageType = "danger";
                $canInsert = false;

            // Check image size
            } elseif ($_FILES["products_image"]["size"] > 2097152) {

                $message = "Image size must not exceed 2 MB.";
                $messageType = "danger";
                $canInsert = false;

            } else {

                // Check uploaded image
                $imageInfo = getimagesize(
                    $_FILES["products_image"]["tmp_name"]
                );

                if ($imageInfo === false) {

                    $message = "Please upload a valid image.";
                    $messageType = "danger";
                    $canInsert = false;

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
                        $canInsert = false;
                    }

                    // Save image
                    if ($canInsert) {

                        $uploadFolder = "uploads/products/";

                        $imageName = uniqid("product_") . "." . $imageExtension;

                        if (!move_uploaded_file(
                            $_FILES["products_image"]["tmp_name"],
                            $uploadFolder . $imageName
                        )) {

                            $message = "Unable to save product image.";
                            $messageType = "danger";
                            $canInsert = false;
                        }
                    }
                }
            }
        }
    }

    // Insert product into database
    if ($canInsert) {

        $sql = "INSERT INTO products
                (name, category, price, quantity, description, products_image)
                VALUES (?, ?, ?, ?, ?, ?)";

        // Prepare SQL statement
        $stmt = $conn->prepare($sql);

        // Bind form values
        $stmt->bind_param(
            "ssdiss",
            $name,
            $category,
            $price,
            $quantity,
            $description,
            $imageName
        );

        // Execute query
        if ($stmt->execute()) {

            // Redirect to products page
            $stmt->close();
            $conn->close();

            header("Location: products.php?success=1");
            exit;

        } else {

            // Remove uploaded image if insert fails
            if ($imageName != null && file_exists("uploads/products/" . $imageName)) {
                unlink("uploads/products/" . $imageName);
            }

            $message = "Error adding product.";
            $messageType = "danger";
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

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Product</title>

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
                        <i class="bi bi-box-seam"></i>
                        Add New Product
                    </h2>

                    <p class="text-muted">
                        Enter the product information below.
                    </p>

                </div>

                <!-- Product Form Card -->
                <div class="card shadow-sm border-0 p-4">

                    <h4 class="mb-4">
                        <i class="bi bi-plus-square"></i>
                        Product Information
                    </h4>

                    <!-- Display Message -->
                    <?php if ($message != "") { ?>

                        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>

                    <?php } ?>

                    <!-- Product Form -->
                    <form method="POST"
                          action="add_product.php"
                          enctype="multipart/form-data">

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
                                    placeholder="Enter product name"
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
                                    placeholder="Enter product category"
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
                                    placeholder="Enter product price"
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
                                    placeholder="Enter product quantity"
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
                                    placeholder="Enter product description"
                                ></textarea>

                            </div>

                            <!-- Product Image -->
                            <div class="col-12">

                                <label for="products_image" class="form-label">
                                    <i class="bi bi-image"></i>
                                    Product Image
                                </label>

                                <input
                                    type="file"
                                    id="products_image"
                                    name="products_image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <small class="text-muted">
                                    JPG, PNG or WEBP. Maximum size: 2 MB.
                                </small>

                            </div>

                        </div>

                        <!-- Form Buttons -->
                        <div class="mt-4">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i>
                                Add Product
                            </button>

                            <a href="products.php" class="btn btn-outline-secondary">
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
