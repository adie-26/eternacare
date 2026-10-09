<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";


/* =========================================================
   VALIDATE PACKAGE ID
   ========================================================= */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: packages.php");
    exit();
}

$package_id = (int) $_GET["id"];


/* =========================================================
   GET PACKAGE
   ========================================================= */

$sql = "SELECT
            package_id,
            package_name,
            image
        FROM packages
        WHERE package_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $package_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: packages.php");
    exit();
}


$package = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   DELETE PACKAGE
   ========================================================= */

$delete_sql = "DELETE FROM packages
               WHERE package_id = ?";

$delete_stmt = $conn->prepare($delete_sql);

$delete_stmt->bind_param(
    "i",
    $package_id
);


if ($delete_stmt->execute()) {

    $delete_stmt->close();


    /* =====================================================
       DELETE PACKAGE IMAGE
       ===================================================== */

    if (!empty($package["image"])) {

        $image_path =
            "../uploads/packages/" .
            $package["image"];


        if (file_exists($image_path)) {

            unlink($image_path);

        }

    }


    /* =====================================================
       RETURN TO PACKAGE LIST
       ===================================================== */

    header("Location: packages.php");
    exit();


} else {

    $error =
        "Unable to delete this package. It may already be used by another record.";

    $delete_stmt->close();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        EternaCare - Delete Package
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>


    <aside class="sidebar">

        <div class="logo">

            <h2>
                EternaCare
            </h2>

            <p>
                Funeral Service Management
            </p>

        </div>


        <ul>

            <li>

                <?php if ($_SESSION['role'] === 'Admin'): ?>

                    <a href="../admin/dashboard.php">
                        Dashboard
                    </a>

                <?php else: ?>

                    <a href="dashboard.php">
                        Dashboard
                    </a>

                <?php endif; ?>

            </li>

            <li>
                <a href="clients.php">
                    Clients
                </a>
            </li>

            <li>
                <a href="deceased.php">
                    Deceased Records
                </a>
            </li>

            <li>
                <a href="packages.php">
                    Funeral Packages
                </a>
            </li>

            <li>
                <a href="optional_services.php">
                    Optional Services
                </a>
            </li>

            <li>
                <a href="funeral_schedules.php">
                    Funeral Schedules
                </a>
            </li>

            <li>
                <a href="staff_assignments.php">
                    Staff Assignments
                </a>
            </li>

            <li>
                <a href="vehicle_assignments.php">
                    Vehicle Assignments
                </a>
            </li>

            <li>
                <a href="preparation_tasks.php">
                    Preparation Tasks
                </a>
            </li>

            <li>
                <a href="payments.php">
                    Payments
                </a>
            </li>

            <li>
                <a href="service_history.php">
                    Service History
                </a>
            </li>

            <li>
                <a href="reports.php">
                    Reports
                </a>
            </li>

            <li>
                <a href="../logout.php">
                    Logout
                </a>
            </li>

        </ul>

    </aside>


    <main class="main-content">

        <section class="top-header">

            <h1>
                Delete Funeral Package
            </h1>

            <p>
                Unable to delete the selected package.
            </p>

        </section>


        <section class="content-section">

            <div
                style="
                    background: #fff3f3;
                    color: #b33a3a;
                    border: 1px solid #efcaca;
                    border-radius: 8px;
                    padding: 15px;
                    margin-bottom: 20px;
                "
            >

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>


            <a
                href="packages.php"
                class="btn btn-primary"
            >
                Back to Funeral Packages
            </a>

        </section>

    </main>


</body>

</html>