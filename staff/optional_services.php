<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$sql = "SELECT
            optional_service_id,
            service_name,
            description,
            price,
            status,
            created_at
        FROM optional_services
        ORDER BY optional_service_id DESC";

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

    <title>EternaCare - Optional Services</title>

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
                <a href="optional_services.php">
                    Optional Services
                </a>
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

            <h1>Optional Services</h1>

            <p>
                Manage additional services that clients can add
                to their funeral package.
            </p>

        </section>


        <section class="content-section">


            <div style="margin-bottom: 20px;">

                <a
                    href="add_optional_service.php"
                    class="btn btn-primary"
                >
                    + Add Optional Service
                </a>

            </div>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Service Name</th>

                            <th>Description</th>

                            <th>Price</th>

                            <th>Status</th>

                            <th>Created At</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($service = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $service["optional_service_id"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $service["service_name"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $service["description"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        ₱<?php
                                        echo number_format(
                                            $service["price"],
                                            2
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $service["status"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $service["created_at"]
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <a
                                            href="view_optional_service.php?id=<?php echo $service["optional_service_id"]; ?>"
                                            class="btn"
                                        >
                                            View
                                        </a>


                                        <a
                                            href="edit_optional_service.php?id=<?php echo $service["optional_service_id"]; ?>"
                                            class="btn btn-primary"
                                        >
                                            Edit
                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    style="text-align: center;"
                                >
                                    No optional services found.
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