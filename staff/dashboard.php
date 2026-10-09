<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/* Total Clients */

$client_result = $conn->query(
    "SELECT COUNT(*) AS total FROM clients"
);

$total_clients = $client_result->fetch_assoc()["total"];


/* Total Deceased Records */

$deceased_result = $conn->query(
    "SELECT COUNT(*) AS total FROM deceased"
);

$total_deceased = $deceased_result->fetch_assoc()["total"];


/* Scheduled Funeral Services */

$scheduled_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services
     WHERE status = 'Scheduled'"
);

$total_scheduled = $scheduled_result->fetch_assoc()["total"];


/* Ongoing Funeral Services */

$ongoing_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funeral_services
     WHERE status = 'Ongoing'"
);

$total_ongoing = $ongoing_result->fetch_assoc()["total"];


/* Pending Preparation Tasks */

$task_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM preparation_tasks
     WHERE status != 'Completed'"
);

$total_pending_tasks = $task_result->fetch_assoc()["total"];


/* Total Payments */

$payment_result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments"
);

$total_payments = $payment_result->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EternaCare - Staff Dashboard</title>

    <link rel="stylesheet" href="../assets/css/style.css">

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
                <a href="dashboard.php">Dashboard</a>
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


        <!-- Header -->

        <section class="top-header">

            <h1>Staff Dashboard</h1>

            <p>
                Welcome,
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!
            </p>

        </section>


        <!-- Dashboard Cards -->

        <section class="dashboard-cards">


            <div class="card">

                <h3>Total Clients</h3>

                <div class="number">

                    <?php echo $total_clients; ?>

                </div>

            </div>


            <div class="card">

                <h3>Deceased Records</h3>

                <div class="number">

                    <?php echo $total_deceased; ?>

                </div>

            </div>


            <div class="card">

                <h3>Scheduled Services</h3>

                <div class="number">

                    <?php echo $total_scheduled; ?>

                </div>

            </div>


            <div class="card">

                <h3>Ongoing Services</h3>

                <div class="number">

                    <?php echo $total_ongoing; ?>

                </div>

            </div>


            <div class="card">

                <h3>Pending Tasks</h3>

                <div class="number">

                    <?php echo $total_pending_tasks; ?>

                </div>

            </div>


            <div class="card">

                <h3>Total Payments</h3>

                <div class="number">

                    ₱<?php echo number_format($total_payments, 2); ?>

                </div>

            </div>


        </section>


        <!-- System Overview -->

        <section class="content-section">

            <h2>Staff Functions</h2>

            <p>
                Use the sidebar to manage clients, deceased records,
                funeral services, schedules, preparation tasks,
                assignments, and payments.
            </p>

        </section>


        <!-- Quick Actions -->

        <section class="content-section">

            <h2>Quick Actions</h2>

            <br>

            <a href="clients.php" class="btn btn-primary">
                Add Client
            </a>

            <a href="packages.php" class="btn btn-primary">
                Add Funeral Package
            </a>

            <a href="funeral_schedules.php" class="btn btn-primary">
                View Schedules
            </a>

        </section>


    </main>


</body>

</html>