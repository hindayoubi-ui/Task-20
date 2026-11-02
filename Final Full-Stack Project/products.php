
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

// Check success messages
if (isset($_GET["success"])) {

    $message = "Product added successfully!";
    $messageType = "success";

} elseif (isset($_GET["updated"])) {

    $message = "Product updated successfully!";
    $messageType = "success";

} elseif (isset($_GET["deleted"])) {

    $message = "Product deleted successfully!";
    $messageType = "success";

} elseif (isset($_GET["error"])) {

    $message = "An error occurred. Please try again.";
    $messageType = "danger";
}

// Get search value
$search = trim($_GET["search"] ?? "");

// Get selected category
$category = trim($_GET["category"] ?? "");

// Prepare search value
$searchTerm = "%" . $search . "%";

// Get all categories
$sqlCategories = "SELECT DISTINCT category
                  FROM products
                  WHERE category != ''
                  ORDER BY category ASC";

// Execute category query
$resultCategories = $conn->query($sqlCategories);

// Search and filter products
$sqlProducts = "SELECT * FROM products
                WHERE (
                    name LIKE ?
                    OR category LIKE ?
                    OR description LIKE ?
                )
                AND (? = '' OR category = ?)
                ORDER BY product_id DESC";

// Prepare SQL statement
$stmtProducts = $conn->prepare($sqlProducts);

// Bind search and category values
$stmtProducts->bind_param(
    "sssss",
    $searchTerm,
    $searchTerm,
    $searchTerm,
    $category,
    $category
);

// Execute query
$stmtProducts->execute();

// Get search results
$resultProducts = $stmtProducts->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Product Management</title>

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
                <div class="d-flex flex-column flex-md-row
                            justify-content-between align-items-md-center mb-4">

                    <div>

                        <h2 class="section-title">
                            <i class="bi bi-box-seam"></i>
                            Products Management
                        </h2>

                        <p class="text-muted">
                            View and manage your company products.
                        </p>

                    </div>

                    <!-- Add Product Button -->
                    <div class="mt-3 mt-md-0">

                        <a href="add_product.php" class="btn btn-primary">

                            <i class="bi bi-plus-circle"></i>
                            Add Product

                        </a>

                    </div>

                </div>

                <!-- Display Message -->
                <?php if ($message != "") { ?>

                    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">

                        <i class="bi bi-info-circle"></i>
                        <?php echo htmlspecialchars($message); ?>

                    </div>

                <?php } ?>

                <!-- Products Table Card -->
                <div class="card shadow-sm border-0 p-4">

                    <!-- Table Heading -->
                    <h4 class="section-title mb-3">

                        <i class="bi bi-list-ul"></i>
                        Products List

                    </h4>

                    <!-- Search and Filter Form -->
                    <form method="GET" action="products.php" class="mb-3">

                        <div class="row g-2">

                            <!-- Search Input -->
                            <div class="col-12 col-lg-5">

                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by product name or description"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >

                            </div>

                            <!-- Category Filter -->
                            <div class="col-12 col-lg-3">

                                <select
                                    name="category"
                                    class="form-select"
                                >

                                    <option value="">
                                        All Categories
                                    </option>

                                    <?php if ($resultCategories) { ?>

                                        <?php while ($cat = $resultCategories->fetch_assoc()) { ?>

                                            <option
                                                value="<?php echo htmlspecialchars($cat["category"]); ?>"
                                                <?php echo ($category == $cat["category"]) ? "selected" : ""; ?>
                                            >

                                                <?php echo htmlspecialchars($cat["category"]); ?>

                                            </option>

                                        <?php } ?>

                                    <?php } ?>

                                </select>

                            </div>

                            <!-- Search Button -->
                            <div class="col-6 col-lg-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100"
                                >

                                    <i class="bi bi-search"></i>
                                    Search

                                </button>

                            </div>

                            <!-- Reset Button -->
                            <div class="col-6 col-lg-2">

                                <a
                                    href="products.php"
                                    class="btn btn-outline-secondary w-100"
                                >

                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    Reset

                                </a>

                            </div>

                        </div>

                    </form>

                    <!-- Responsive Table -->
                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if ($resultProducts && $resultProducts->num_rows > 0) { ?>

                                    <?php while ($product = $resultProducts->fetch_assoc()) { ?>

                                        <tr>

                                            <!-- Product ID -->
                                            <td>
                                                <?php echo $product["product_id"]; ?>
                                            </td>

                                            <!-- Product Image -->
                                            <td>

                                                <?php if (!empty($product["products_image"])) { ?>

                                                    <img
                                                        src="uploads/products/<?php echo htmlspecialchars(basename($product["products_image"])); ?>"
                                                        alt="Product Image"
                                                        width="65"
                                                        height="65"
                                                        style="object-fit: cover; border-radius: 8px;"
                                                    >

                                                <?php } else { ?>

                                                    <span class="text-muted">
                                                        No Image
                                                    </span>

                                                <?php } ?>

                                            </td>

                                            <!-- Product Name -->
                                            <td>
                                                <?php echo htmlspecialchars($product["name"]); ?>
                                            </td>

                                            <!-- Category -->
                                            <td>
                                                <?php echo htmlspecialchars($product["category"]); ?>
                                            </td>

                                            <!-- Price -->
                                            <td>
                                                $<?php echo number_format(
                                                    (float)$product["price"],
                                                    2
                                                ); ?>
                                            </td>

                                            <!-- Quantity -->
                                            <td>
                                                <?php echo $product["quantity"]; ?>
                                            </td>

                                            <!-- Description -->
                                            <td>
                                                <?php echo htmlspecialchars(
                                                    $product["description"] ?? ""
                                                ); ?>
                                            </td>

                                            <!-- Actions -->
                                            <td class="text-nowrap">

                                                <!-- Edit Button -->
                                                <a
                                                    href="edit_product.php?id=<?php echo $product["product_id"]; ?>"
                                                    class="btn btn-sm btn-success"
                                                    title="Edit Product"
                                                >

                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit

                                                </a>

                                                <!-- Delete Button -->
                                                <form
                                                    method="POST"
                                                    action="delete_product.php"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this product? Related order items may also be deleted. Continue?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="product_id"
                                                        value="<?php echo $product["product_id"]; ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-danger"
                                                        title="Delete Product"
                                                    >

                                                        <i class="bi bi-trash"></i>
                                                        Delete

                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>

                                        <td colspan="8"
                                            class="text-center text-muted">

                                            <i class="bi bi-inbox"></i>
                                            No products found.

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!-- Footer -->
            <?php include("common/footer.php"); ?>

        </main>

    </div>

</body>
</html>

<?php
// Close statement
$stmtProducts->close();

// Close database connection
$conn->close();
?>
