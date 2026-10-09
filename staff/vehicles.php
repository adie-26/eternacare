<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get all vehicles.
 */

$sql = "SELECT
            vehicle_id,
            vehicle_name,
            vehicle_type,
            plate_number,
            availability_status,
            created_at

        FROM vehicles

        ORDER BY vehicle_id DESC";

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

    <title>
        EternaCare - Vehicles
    </title>

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

            <p>
                Funeral Service Management
            </p>

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
                Vehicles
            </h1>

            <p>
                Manage funeral service vehicles.
            </p>

        </section>


        <!-- Add Vehicle Button -->

        <section class="content-section">

            <a
                href="add_vehicle.php"
                class="btn btn-primary"
            >
                + Add Vehicle
            </a>

        </section>


        <br>


        <!-- Vehicle List -->

        <section class="content-section">

            <h2>
                Vehicle List
            </h2>


            <?php if ($result->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Vehicle Name
                            </th>

                            <th>
                                Vehicle Type
                            </th>

                            <th>
                                Plate Number
                            </th>

                            <th>
                                Availability
                            </th>

                            <th>
                                Created At
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while (
                            $vehicle =
                            $result->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle["vehicle_id"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle["vehicle_name"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle["vehicle_type"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle["plate_number"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle[
                                            "availability_status"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            "F d, Y",
                                            strtotime(
                                                $vehicle[
                                                    "created_at"
                                                ]
                                            )
                                        )
                                    );
                                    ?>
                                </td>


                                <td>

                                    <a
                                        href="view_vehicle.php?id=<?php echo $vehicle["vehicle_id"]; ?>"
                                        class="btn"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="edit_vehicle.php?id=<?php echo $vehicle["vehicle_id"]; ?>"
                                        class="btn"
                                    >
                                        Edit
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <p>
                    No vehicles found.
                </p>

                <p>
                    Click "Add Vehicle" to create your first vehicle record.
                </p>

            <?php endif; ?>

        </section>


    </main>


</body>

</html>