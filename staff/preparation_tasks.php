<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get all preparation tasks.
 */

$sql = "SELECT
            pt.task_id,
            pt.funeral_service_id,
            pt.task_name,
            pt.description,
            pt.status,

            fs.service_start,

            c.full_name AS client_name,

            d.full_name AS deceased_name

        FROM preparation_tasks pt

        INNER JOIN funeral_services fs
            ON pt.funeral_service_id =
               fs.funeral_service_id

        INNER JOIN clients c
            ON fs.client_id = c.client_id

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        ORDER BY pt.task_id DESC";

$result = $conn->query($sql);


/*
 * Get funeral schedules for adding tasks.
 */

$schedules_sql = "SELECT
                      fs.funeral_service_id,
                      fs.service_start,
                      fs.status,

                      c.full_name AS client_name,

                      d.full_name AS deceased_name

                  FROM funeral_services fs

                  INNER JOIN clients c
                      ON fs.client_id = c.client_id

                  INNER JOIN deceased d
                      ON fs.deceased_id = d.deceased_id

                  ORDER BY fs.service_start DESC";

$schedules_result =
    $conn->query($schedules_sql);

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
        EternaCare - Preparation Tasks
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
                Preparation Tasks
            </h1>

            <p>
                Manage funeral preparation tasks.
            </p>

        </section>


        <!-- Current Tasks -->

        <section class="content-section">

            <h2>
                Preparation Task List
            </h2>


            <?php if ($result->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Task ID
                            </th>

                            <th>
                                Funeral Service
                            </th>

                            <th>
                                Client
                            </th>

                            <th>
                                Deceased
                            </th>

                            <th>
                                Task
                            </th>

                            <th>
                                Description
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
                            $task =
                            $result->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $task["task_id"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    #<?php
                                    echo htmlspecialchars(
                                        $task[
                                            "funeral_service_id"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $task[
                                            "client_name"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $task[
                                            "deceased_name"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $task["task_name"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $task[
                                            "description"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $task["status"]
                                    );
                                    ?>
                                </td>


                                <td>

                                    <a
                                        href="edit_preparation_task.php?id=<?php echo $task["task_id"]; ?>"
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
                    No preparation tasks found.
                </p>

            <?php endif; ?>

        </section>


        <br>


        <!-- Funeral Schedules -->

        <section class="content-section">

            <h2>
                Funeral Schedules
            </h2>

            <p>
                Select a funeral service to add preparation tasks.
            </p>


            <?php if ($schedules_result->num_rows > 0): ?>

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
                                Service Date
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
                                    #<?php
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
                                        $schedule[
                                            "status"
                                        ]
                                    );
                                    ?>
                                </td>


                                <td>

                                    <a
                                        href="add_preparation_task.php?funeral_service_id=<?php echo $schedule["funeral_service_id"]; ?>"
                                        class="btn btn-primary"
                                    >
                                        Add Task
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <p>
                    No funeral schedules available.
                </p>

                <p>
                    Create a funeral schedule first before
                    adding preparation tasks.
                </p>

            <?php endif; ?>

        </section>


    </main>


</body>

</html>