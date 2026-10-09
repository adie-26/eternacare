<?php

require_once "../includes/auth.php";

requireRole("Admin");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EternaCare - Super Admin Dashboard</title>

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
                <a href="staff.php">Staff Accounts</a>
            </li>

            <li>
                <a href="../staff/clients.php">Clients</a>
            </li>

            <li>
                <a href="../staff/deceased.php">Deceased Records</a>
            </li>

            <li>
                <a href="../staff/packages.php">Funeral Packages</a>
            </li>

            <li>
                <a href="../staff/optional_services.php">Optional Services</a>
            </li>

            <li>
                <a href="../staff/funeral_schedules.php">Funeral Schedules</a>
            </li>

            <li>
                <a href="../staff/staff_assignments.php">Staff Assignments</a>
            </li>

            <li>
                <a href="../staff/vehicle_assignments.php">Vehicle Assignments</a>
            </li>

          
            <li>
                <a href="../staff/preparation_tasks.php">Preparation Tasks</a>
            </li>

            <li>
                <a href="../staff/payments.php">Payments</a>
            </li>

            <li>
                <a href="../staff/service_history.php">Service History</a>
            </li>

            <li>
                <a href="../staff/reports.php">Reports</a>
            </li>

            <li>
                <a href="../logout.php">Logout</a>
            </li>

        </ul>

    </aside>


    <!-- Main Content -->

    <main class="main-content">

        <section class="top-header">

            <h1>Super Admin Dashboard</h1>

            <p>
                Welcome,
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!
            </p>

        </section>


        <!-- Dashboard Cards -->

        <section class="dashboard-cards">

            <div class="card">

                <h3>Total Staff</h3>

                <div class="number">
                    0
                </div>

            </div>


            <div class="card">

                <h3>Total Clients</h3>

                <div class="number">
                    0
                </div>

            </div>


            <div class="card">

                <h3>Funeral Services</h3>

                <div class="number">
                    0
                </div>

            </div>


            <div class="card">

                <h3>Pending Payments</h3>

                <div class="number">
                    0
                </div>

            </div>

        </section>


        <!-- System Overview -->

        <section class="content-section">

            <h2>System Overview</h2>

            <p>
                Manage EternaCare users, clients, funeral services,
                packages, schedules, assignments, payments, and reports
                from this dashboard.
            </p>

        </section>


        <!-- Quick Actions -->

        <section class="content-section">

            <h2>Quick Actions</h2>

            <br>

            <a href="add_staff.php" class="btn btn-primary">
                Add Staff
            </a>

            <a href="../staff/add_package.php" class="btn btn-primary">
                Add Funeral Package
            </a>

            <a href="../staff/funeral_schedules.php" class="btn btn-primary">
                View Schedules
            </a>

        </section>

    </main>

</body>

</html>