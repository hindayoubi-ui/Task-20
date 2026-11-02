
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
if (isset($_GET["added"])) {

    $message = "Employee added successfully!";
    $messageType = "success";

} elseif (isset($_GET["updated"])) {

    $message = "Employee updated successfully!";
    $messageType = "success";

} elseif (isset($_GET["disabled"])) {

    $message = "Employee disabled successfully!";
    $messageType = "success";

} elseif (isset($_GET["error"])) {

    $message = "An error occurred. Please try again.";
    $messageType = "danger";
}

// Get search value
$search = trim($_GET["search"] ?? "");

// Prepare search value
$searchTerm = "%" . $search . "%";

// Get all employees
$sqlEmployees = "SELECT *
                 FROM users
                 WHERE role = 'employee'
                 AND (
                    name LIKE ?
                    OR email LIKE ?
                    OR phone LIKE ?
                    OR position LIKE ?
                 )
                 ORDER BY users_id DESC";

// Prepare SQL statement
$stmtEmployees = $conn->prepare($sqlEmployees);

// Bind search values
$stmtEmployees->bind_param(
    "ssss",
    $searchTerm,
    $searchTerm,
    $searchTerm,
    $searchTerm
);

// Execute query
$stmtEmployees->execute();

// Get search results
$resultEmployees = $stmtEmployees->get_result();



if (isset($_GET["added"])) {

    $message = "Employee added successfully!";
    $messageType = "success";

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!-- Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Employee Management</title>

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
                            <i class="bi bi-person-badge"></i>
                            Employee Management
                        </h2>

                        <p class="text-muted">
                            Add and manage your company employees.
                        </p>

                    </div>

                    <!-- Add Employee Button -->
                    <div class="mt-3 mt-md-0">

                        <a href="add_employee.php" class="btn btn-primary">

                            <i class="bi bi-person-plus"></i>
                            Add Employee

                        </a>

                    </div>

                </div>

                <!-- Display Message -->
                <?php if ($message != "") { ?>

                    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">

                        <?php echo htmlspecialchars($message); ?>

                    </div>

                <?php } ?>

                <!-- Employees Table Card -->
                <div class="card shadow-sm border-0 p-4">

                    <!-- Table Heading -->
                    <h4 class="section-title mb-3">

                        <i class="bi bi-people"></i>
                        Employees List

                    </h4>

                    <!-- Search Form -->
                    <form method="GET" action="employees.php" class="mb-3">

                        <div class="row g-2">

                            <!-- Search Input -->
                            <div class="col-12 col-md-8">

                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by name, email, phone, or position"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >

                            </div>

                            <!-- Search Button -->
                            <div class="col-6 col-md-2">

                                <button type="submit" class="btn btn-primary w-100">

                                    <i class="bi bi-search"></i>
                                    Search

                                </button>

                            </div>

                            <!-- Reset Button -->
                            <div class="col-6 col-md-2">

                                <a href="employees.php"
                                   class="btn btn-outline-secondary w-100">

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
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if ($resultEmployees->num_rows > 0) { ?>

                                    <?php while ($employee = $resultEmployees->fetch_assoc()) { ?>

                                        <tr>

                                            <!-- Employee ID -->
                                            <td>
                                                <?php echo $employee["users_id"]; ?>
                                            </td>

                                            <!-- Employee Name -->
                                            <td>
                                                <?php echo htmlspecialchars($employee["name"]); ?>
                                            </td>

                                            <!-- Email -->
                                            <td>
                                                <?php echo htmlspecialchars($employee["email"]); ?>
                                            </td>

                                            <!-- Phone -->
                                            <td>
                                                <?php echo htmlspecialchars($employee["phone"]); ?>
                                            </td>

                                            <!-- Position -->
                                            <td>
                                                <?php echo htmlspecialchars($employee["position"]); ?>
                                            </td>

                                            <!-- Status -->
                                            <td>

                                                <?php if ($employee["status"] == "active") { ?>

                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>

                                                <?php } else { ?>

                                                    <span class="badge bg-secondary">
                                                        Disabled
                                                    </span>

                                                <?php } ?>

                                            </td>

                                            <!-- Actions -->
                                            <td class="text-nowrap">

                                                <!-- Edit Button -->
                                                <a
                                                    href="edit_employee.php?id=<?php echo $employee["users_id"]; ?>"
                                                    class="btn btn-sm btn-success"
                                                    title="Edit Employee"
                                                >

                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit

                                                </a>

                                                <!-- Disable Button -->
                                                <?php if ($employee["status"] == "active") { ?>

                                                    <form
                                                        method="POST"
                                                        action="disable_employee.php"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to disable this employee?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="user_id"
                                                            value="<?php echo $employee["users_id"]; ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            title="Disable Employee"
                                                        >

                                                            <i class="bi bi-person-x"></i>
                                                            Disable

                                                        </button>

                                                    </form>

                                                <?php } ?>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>

                                        <td colspan="7" class="text-center text-muted">

                                            <i class="bi bi-inbox"></i>
                                            No employees found.

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
$stmtEmployees->close();

// Close database connection
$conn->close();
?>
