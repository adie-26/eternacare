<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$sql = "SELECT
            fs.funeral_service_id,
            c.full_name AS client_name,
            d.full_name AS deceased_name,
            p.package_name,
            fs.service_start,
            fs.service_end,
            fs.location,
            fs.total_cost,
            fs.status
        FROM funeral_services fs

        INNER JOIN clients c
            ON fs.client_id = c.client_id

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        INNER JOIN packages p
            ON fs.package_id = p.package_id

        ORDER BY fs.service_start DESC";

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

    <title>EternaCare - Funeral Schedules</title>

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
                <a href="funeral_schedules.php">
                    Funeral Schedules
                </a>
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

            <h1>Funeral Schedules</h1>

            <p>
                Manage funeral service schedules and appointments.
            </p>

        </section>


        <section class="content-section">


            <div style="margin-bottom: 20px;">

                <a
                    href="add_funeral_schedule.php"
                    class="btn btn-primary"
                >
                    + Add Funeral Schedule
                </a>

            </div>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Client</th>

                            <th>Deceased</th>

                            <th>Package</th>

                            <th>Start</th>

                            <th>End</th>

                            <th>Location</th>

                            <th>Total Cost</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($schedule = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["funeral_service_id"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["client_name"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["deceased_name"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["package_name"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["service_start"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["service_end"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["location"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        ₱<?php
                                        echo number_format(
                                            $schedule["total_cost"],
                                            2
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $schedule["status"]
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <a
                                            href="view_funeral_schedule.php?id=<?php echo $schedule["funeral_service_id"]; ?>"
                                            class="btn"
                                        >
                                            View
                                        </a>


                                        <a
                                            href="edit_funeral_schedule.php?id=<?php echo $schedule["funeral_service_id"]; ?>"
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
                                    colspan="10"
                                    style="text-align: center;"
                                >
                                    No funeral schedules found.
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