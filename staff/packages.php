<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/* Get all funeral packages */

$sql = "SELECT
            package_id,
            package_name,
            description,
            price,
            image,
            status,
            created_at
        FROM packages
        ORDER BY package_id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>EternaCare - Funeral Packages</title>

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


    <!-- Main Content -->

    <main class="main-content">


        <section class="top-header">

            <h1>
                Funeral Packages
            </h1>

            <p>
                Manage available funeral service packages.
            </p>

        </section>


        <section class="content-section">


            <div class="section-header">

                <h2>
                    Funeral Package List
                </h2>


                <a
                    href="add_package.php"
                    class="btn btn-primary"
                >
                    Add Funeral Package
                </a>

            </div>


            <br>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>


                            <th>
                                Image
                            </th>


                            <th>
                                Package Name
                            </th>


                            <th>
                                Description
                            </th>


                            <th>
                                Price
                            </th>


                            <th>
                                Status
                            </th>


                            <th>
                                Created
                            </th>


                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php if ($result->num_rows > 0): ?>


                            <?php while ($package = $result->fetch_assoc()): ?>


                                <tr>


                                    <!-- PACKAGE ID -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $package["package_id"]
                                        );
                                        ?>

                                    </td>


                                    <!-- PACKAGE IMAGE -->

                                    <td>

                                        <?php if (!empty($package["image"])): ?>

                                            <img
                                                src="../uploads/packages/<?php echo htmlspecialchars($package["image"]); ?>"
                                                alt="Funeral Package"
                                                style="
                                                    width: 90px;
                                                    height: 70px;
                                                    object-fit: cover;
                                                    border-radius: 8px;
                                                    border: 1px solid #ddd;
                                                    display: block;
                                                "
                                            >

                                        <?php else: ?>

                                            <div
                                                style="
                                                    width: 90px;
                                                    height: 70px;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    background: #f1f3f4;
                                                    color: #888;
                                                    border-radius: 8px;
                                                    border: 1px solid #ddd;
                                                    font-size: 12px;
                                                    text-align: center;
                                                "
                                            >
                                                No Image
                                            </div>

                                        <?php endif; ?>

                                    </td>


                                    <!-- PACKAGE NAME -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $package["package_name"]
                                        );
                                        ?>

                                    </td>


                                    <!-- DESCRIPTION -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $package["description"] ?? "-"
                                        );
                                        ?>

                                    </td>


                                    <!-- PRICE -->

                                    <td>

                                        ₱<?php

                                        echo number_format(
                                            (float) $package["price"],
                                            2
                                        );

                                        ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $package["status"]
                                        );
                                        ?>

                                    </td>


                                    <!-- CREATED -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $package["created_at"]
                                        );
                                        ?>

                                    </td>


                                    <!-- ACTION -->

                                    <td>


                                        <a
                                            href="view_package.php?id=<?php echo $package["package_id"]; ?>"
                                            class="btn btn-primary"
                                        >
                                            View
                                        </a>


                                        <a
                                            href="edit_package.php?id=<?php echo $package["package_id"]; ?>"
                                            class="btn btn-primary"
                                        >
                                            Edit
                                        </a>

                                        <a
    href="delete_package.php?id=<?php echo $package["package_id"]; ?>"
    class="btn"
    onclick="return confirm('Are you sure you want to delete this funeral package? This action cannot be undone.');"
>
    Delete
</a>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="8"
                                    style="text-align: center;"
                                >

                                    No funeral packages found.

                                </td>

                            </tr>


                        <?php endif; ?>


                    </tbody>

                </table>

            </div>


        </section>


    </main>


</body>

</html>