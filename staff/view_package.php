    <?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/* Validate ID */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: packages.php");
    exit();
}

$package_id = (int) $_GET["id"];


/* Get package information */

$sql = "SELECT
            package_id,
            package_name,
            description,
            price,
            status,
            created_at
        FROM packages
        WHERE package_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $package_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: packages.php");
    exit();
}


$package = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>EternaCare - Package Details</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>


    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="logo">

            <h2>EternaCare</h2>

            <p>Funeral Service Management</p>

        </div>


        <ul>

           <li>
    <?php if ($_SESSION['role'] === 'Admin'): ?>
        <a href="../admin/dashboard.php">Dashboard</a>
    <?php else: ?>
        <a href="dashboard.php">Dashboard</a>
    <?php endif; ?>
</li>

            <li>
                <a href="clients.php">Clients</a>
            </li>

            <li>
                <a href="deceased.php">Deceased Records</a>
            </li>

            <li>
                <a href="packages.php">Funeral Packages</a>
            </li>

            <li>
                <a href="optional_services.php">Optional Services</a>
            </li>

            <li>
                <a href="funeral_schedules.php">Funeral Schedules</a>
            </li>

            <li>
                <a href="staff_assignments.php">Staff Assignments</a>
            </li>

            <li>
                <a href="vehicle_assignments.php">Vehicle Assignments</a>
            </li>

            <li>
                <a href="preparation_tasks.php">Preparation Tasks</a>
            </li>

            <li>
                <a href="payments.php">Payments</a>
            </li>

            <li>
                <a href="service_history.php">Service History</a>
            </li>

            <li>
                <a href="reports.php">Reports</a>
            </li>

            <li>
                <a href="../logout.php">Logout</a>
            </li>

        </ul>

    </aside>


    <!-- Main Content -->

    <main class="main-content">


        <section class="top-header">

            <h1>Funeral Package Details</h1>

            <p>
                View complete information about this funeral package.
            </p>

        </section>


        <section class="content-section">

            <h2>
                <?php
                echo htmlspecialchars(
                    $package["package_name"]
                );
                ?>
            </h2>

            <br>


            <table>

                <tr>

                    <th>Package ID</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $package["package_id"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Package Name</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $package["package_name"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Description</th>

                    <td>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $package["description"] ?? "-"
                            )
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Price</th>

                    <td>
                        ₱<?php
                        echo number_format(
                            (float) $package["price"],
                            2
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Status</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $package["status"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Date Created</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $package["created_at"]
                        );
                        ?>
                    </td>

                </tr>

            </table>


            <br>


            <a
                href="edit_package.php?id=<?php echo $package["package_id"]; ?>"
                class="btn btn-primary"
            >
                Edit Funeral Package
            </a>


            <a
                href="packages.php"
                class="btn"
            >
                Back to Funeral Packages
            </a>

        </section>


    </main>


</body>

</html>