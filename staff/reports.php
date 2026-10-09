<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get total clients.
 */

$clients_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM clients"
);

$total_clients =
    $clients_result->fetch_assoc()["total"];


/*
 * Get total deceased records.
 */

$deceased_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM deceased"
);

$total_deceased =
    $deceased_result->fetch_assoc()["total"];


/*
 * Get total funeral services.
 */

$services_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services"
);

$total_services =
    $services_result->fetch_assoc()["total"];


/*
 * Get service status counts.
 */

$scheduled_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services
     WHERE status = 'Scheduled'"
);

$total_scheduled =
    $scheduled_result->fetch_assoc()["total"];


$preparing_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services
     WHERE status = 'Preparing'"
);

$total_preparing =
    $preparing_result->fetch_assoc()["total"];


$ongoing_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services
     WHERE status = 'Ongoing'"
);

$total_ongoing =
    $ongoing_result->fetch_assoc()["total"];


$completed_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services
     WHERE status = 'Completed'"
);

$total_completed =
    $completed_result->fetch_assoc()["total"];


$cancelled_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services
     WHERE status = 'Cancelled'"
);

$total_cancelled =
    $cancelled_result->fetch_assoc()["total"];


/*
 * Get total payments.
 */

$payments_result = $conn->query(
    "SELECT
        COALESCE(SUM(amount), 0) AS total
     FROM payments"
);

$total_payments =
    $payments_result->fetch_assoc()["total"];


/*
 * Get total preparation tasks.
 */

$tasks_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM preparation_tasks"
);

$total_tasks =
    $tasks_result->fetch_assoc()["total"];


/*
 * Get preparation task status counts.
 */

$pending_tasks_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM preparation_tasks
     WHERE status = 'Pending'"
);

$total_pending_tasks =
    $pending_tasks_result->fetch_assoc()["total"];


$in_progress_tasks_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM preparation_tasks
     WHERE status = 'In Progress'"
);

$total_in_progress_tasks =
    $in_progress_tasks_result->fetch_assoc()["total"];


$completed_tasks_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM preparation_tasks
     WHERE status = 'Completed'"
);

$total_completed_tasks =
    $completed_tasks_result->fetch_assoc()["total"];

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
        EternaCare - Reports
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
                Reports
            </h1>

            <p>
                View a summary of EternaCare records and activities.
            </p>

        </section>


        <!-- General Summary -->

        <section class="content-section">

            <h2>
                General Summary
            </h2>


            <div class="dashboard-cards">


                <div class="card">

                    <h3>
                        Total Clients
                    </h3>

                    <p>
                        <?php
                        echo $total_clients;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Total Deceased
                    </h3>

                    <p>
                        <?php
                        echo $total_deceased;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Total Funeral Services
                    </h3>

                    <p>
                        <?php
                        echo $total_services;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Total Payments
                    </h3>

                    <p>
                        ₱<?php
                        echo number_format(
                            $total_payments,
                            2
                        );
                        ?>
                    </p>

                </div>


            </div>

        </section>


        <br>


        <!-- Funeral Service Report -->

        <section class="content-section">

            <h2>
                Funeral Service Report
            </h2>


            <div class="dashboard-cards">


                <div class="card">

                    <h3>
                        Scheduled
                    </h3>

                    <p>
                        <?php
                        echo $total_scheduled;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Preparing
                    </h3>

                    <p>
                        <?php
                        echo $total_preparing;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Ongoing
                    </h3>

                    <p>
                        <?php
                        echo $total_ongoing;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Completed
                    </h3>

                    <p>
                        <?php
                        echo $total_completed;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Cancelled
                    </h3>

                    <p>
                        <?php
                        echo $total_cancelled;
                        ?>
                    </p>

                </div>


            </div>

        </section>


        <br>


        <!-- Preparation Task Report -->

        <section class="content-section">

            <h2>
                Preparation Task Report
            </h2>


            <div class="dashboard-cards">


                <div class="card">

                    <h3>
                        Total Tasks
                    </h3>

                    <p>
                        <?php
                        echo $total_tasks;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Pending
                    </h3>

                    <p>
                        <?php
                        echo $total_pending_tasks;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        In Progress
                    </h3>

                    <p>
                        <?php
                        echo $total_in_progress_tasks;
                        ?>
                    </p>

                </div>


                <div class="card">

                    <h3>
                        Completed
                    </h3>

                    <p>
                        <?php
                        echo $total_completed_tasks;
                        ?>
                    </p>

                </div>


            </div>

        </section>


    </main>


</body>

</html>