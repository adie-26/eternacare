<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get completed and cancelled funeral services.
 */

$sql = "SELECT
            fs.funeral_service_id,
            fs.service_start,
            fs.service_end,
            fs.location,
            fs.total_cost,
            fs.status,
            fs.notes,

            c.full_name AS client_name,

            d.full_name AS deceased_name,

            p.package_name

        FROM funeral_services fs

        INNER JOIN clients c
            ON fs.client_id = c.client_id

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        INNER JOIN packages p
            ON fs.package_id = p.package_id

        WHERE fs.status IN ('Completed', 'Cancelled')

        ORDER BY fs.service_end DESC";

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
        EternaCare - Service History
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
                Service History
            </h1>

            <p>
                View completed and cancelled funeral services.
            </p>

        </section>


        <!-- Service History -->

        <section class="content-section">

            <h2>
                Funeral Service History
            </h2>


            <?php if ($result->num_rows > 0): ?>

                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Service ID
                                </th>

                                <th>
                                    Client
                                </th>

                                <th>
                                    Deceased
                                </th>

                                <th>
                                    Package
                                </th>

                                <th>
                                    Service Start
                                </th>

                                <th>
                                    Service End
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Total Cost
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Notes
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while (
                                $service =
                                $result->fetch_assoc()
                            ): ?>

                                <tr>

                                    <td>

                                        #<?php
                                        echo htmlspecialchars(
                                            $service[
                                                "funeral_service_id"
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $service[
                                                "client_name"
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $service[
                                                "deceased_name"
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $service[
                                                "package_name"
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
                                                    $service[
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
                                                    $service[
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
                                            $service[
                                                "location"
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        ₱<?php
                                        echo number_format(
                                            $service[
                                                "total_cost"
                                            ],
                                            2
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $service[
                                                "status"
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $service[
                                                "notes"
                                            ] ?? ""
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <a
                                            href="view_funeral_schedule.php?id=<?php
                                            echo $service[
                                                "funeral_service_id"
                                            ];
                                            ?>"
                                            class="btn"
                                        >
                                            View
                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <p>
                    No service history found.
                </p>

                <p>
                    Completed or cancelled funeral services
                    will appear here.
                </p>

            <?php endif; ?>

        </section>


    </main>


</body>

</html>