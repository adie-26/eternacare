<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/* Get all deceased records */

$sql = "SELECT
            d.deceased_id,
            d.full_name,
            d.date_of_birth,
            d.date_of_death,
            d.age,
            d.address,
            d.cause_of_death,
            c.full_name AS client_name
        FROM deceased d
        LEFT JOIN clients c
            ON d.client_id = c.client_id
        ORDER BY d.deceased_id DESC";

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

    <title>EternaCare - Deceased Records</title>

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

            <h1>Deceased Records</h1>

            <p>
                Manage records of deceased individuals.
            </p>

        </section>


        <section class="content-section">


            <div class="section-header">

                <h2>Deceased Records List</h2>

                <a
                    href="add_deceased.php"
                    class="btn btn-primary"
                >
                    Add Deceased Record
                </a>

            </div>


            <br>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Deceased Name</th>

                            <th>Client</th>

                            <th>Date of Birth</th>

                            <th>Date of Death</th>

                            <th>Age</th>

                            <th>Cause of Death</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($deceased = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $deceased["deceased_id"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $deceased["full_name"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $deceased["client_name"] ?? "-"
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $deceased["date_of_birth"] ?? "-"
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $deceased["date_of_death"] ?? "-"
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $deceased["age"] ?? "-"
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $deceased["cause_of_death"] ?? "-"
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <a
                                            href="view_deceased.php?id=<?php echo $deceased["deceased_id"]; ?>"
                                            class="btn btn-primary"
                                        >
                                            View
                                        </a>


                                        <a
                                            href="edit_deceased.php?id=<?php echo $deceased["deceased_id"]; ?>"
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
                                    colspan="8"
                                    style="text-align: center;"
                                >
                                    No deceased records found.
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