<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get all funeral schedules.
 */

$schedules_sql = "SELECT
                      fs.funeral_service_id,
                      fs.service_start,
                      fs.service_end,
                      fs.location,
                      fs.status,
                      c.full_name AS client_name,
                      d.full_name AS deceased_name
                  FROM funeral_services fs

                  INNER JOIN clients c
                      ON fs.client_id = c.client_id

                  INNER JOIN deceased d
                      ON fs.deceased_id = d.deceased_id

                  ORDER BY fs.service_start DESC";

$schedules_result = $conn->query($schedules_sql);


/*
 * Get vehicle assignments.
 */

$assignments_sql = "SELECT
                        va.vehicle_assignment_id,
                        va.funeral_service_id,
                        va.assigned_at,

                        v.vehicle_id,
                        v.vehicle_name,
                        v.vehicle_type,
                        v.plate_number,

                        c.full_name AS client_name,
                        d.full_name AS deceased_name

                    FROM vehicle_assignments va

                    INNER JOIN vehicles v
                        ON va.vehicle_id = v.vehicle_id

                    INNER JOIN funeral_services fs
                        ON va.funeral_service_id =
                           fs.funeral_service_id

                    INNER JOIN clients c
                        ON fs.client_id = c.client_id

                    INNER JOIN deceased d
                        ON fs.deceased_id = d.deceased_id

                    ORDER BY va.assigned_at DESC";

$assignments_result = $conn->query($assignments_sql);

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
        EternaCare - Vehicle Assignments
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
    <a href="vehicles.php">
        &nbsp;&nbsp;⤷ Vehicles
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
                Vehicle Assignments
            </h1>

            <p>
                Assign vehicles to funeral services.
            </p>

        </section>


        <!-- Current Vehicle Assignments -->

        <section class="content-section">

            <h2>
                Current Vehicle Assignments
            </h2>


            <?php if ($assignments_result->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Assignment ID
                            </th>

                            <th>
                                Funeral Service
                            </th>

                            <th>
                                Deceased
                            </th>

                            <th>
                                Client
                            </th>

                            <th>
                                Vehicle
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Plate Number
                            </th>

                            <th>
                                Assigned At
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while (
                            $assignment =
                            $assignments_result->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $assignment[
                                            "vehicle_assignment_id"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    #<?php
                                    echo htmlspecialchars(
                                        $assignment[
                                            "funeral_service_id"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $assignment[
                                            "deceased_name"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $assignment[
                                            "client_name"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $assignment[
                                            "vehicle_name"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $assignment[
                                            "vehicle_type"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $assignment[
                                            "plate_number"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            "F d, Y h:i A",
                                            strtotime(
                                                $assignment[
                                                    "assigned_at"
                                                ]
                                            )
                                        )
                                    );
                                    ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <p>
                    No vehicle assignments found.
                </p>

            <?php endif; ?>

        </section>


        <br>


        <!-- Funeral Schedules -->

        <section class="content-section">

            <h2>
                Funeral Schedules
            </h2>


            <?php if ($schedules_result->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Schedule ID
                            </th>

                            <th>
                                Client
                            </th>

                            <th>
                                Deceased
                            </th>

                            <th>
                                Start
                            </th>

                            <th>
                                End
                            </th>

                            <th>
                                Location
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while (
                            $schedule =
                            $schedules_result->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $schedule[
                                            "funeral_service_id"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $schedule[
                                            "client_name"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $schedule[
                                            "deceased_name"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            "M d, Y h:i A",
                                            strtotime(
                                                $schedule[
                                                    "service_start"
                                                ]
                                            )
                                        )
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            "M d, Y h:i A",
                                            strtotime(
                                                $schedule[
                                                    "service_end"
                                                ]
                                            )
                                        )
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $schedule[
                                            "location"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $schedule[
                                            "status"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>

                                    <a
                                        href="add_vehicle_assignment.php?funeral_service_id=<?php echo $schedule["funeral_service_id"]; ?>"
                                        class="btn btn-primary"
                                    >
                                        Assign Vehicle
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <p>
                    No funeral schedules available for vehicle assignment.
                </p>

                <p>
                    Create a funeral schedule first before assigning a vehicle.
                </p>

            <?php endif; ?>

        </section>


    </main>


</body>

</html>