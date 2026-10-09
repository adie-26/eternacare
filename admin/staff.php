<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Admin");

$sql = "SELECT
            u.user_id,
            u.full_name,
            u.username,
            u.status,
            u.created_at,
            s.staff_id,
            s.contact_number,
            s.position,
            s.availability_status
        FROM users u
        LEFT JOIN staff s
            ON u.user_id = s.user_id
        WHERE u.role = 'Staff'
        ORDER BY u.user_id DESC";

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

    <title>EternaCare - Staff Accounts</title>

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
                <a href="dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="staff.php">
                    Staff Accounts
                </a>
            </li>

            <li>
                <a href="../staff/clients.php">
                    Clients
                </a>
            </li>

            <li>
                <a href="../staff/deceased.php">
                    Deceased Records
                </a>
            </li>

            <li>
                <a href="../staff/packages.php">
                    Funeral Packages
                </a>
            </li>

            <li>
                <a href="../staff/optional_services.php">
                    Optional Services
                </a>
            </li>

            <li>
                <a href="../staff/funeral_schedules.php">
                    Funeral Schedules
                </a>
            </li>

            <li>
                <a href="../staff/staff_assignments.php">
                    Staff Assignments
                </a>
            </li>

            <li>
                <a href="../staff/vehicle_assignments.php">
                    Vehicle Assignments
                </a>
            </li>


            <li>
                <a href="../staff/preparation_tasks.php">
                    Preparation Tasks
                </a>
            </li>

            <li>
                <a href="../staff/payments.php">
                    Payments
                </a>
            </li>

            <li>
                <a href="../staff/service_history.php">
                    Service History
                </a>
            </li>

            <li>
                <a href="../staff/reports.php">
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
                Staff Accounts
            </h1>

            <p>
                Manage EternaCare staff accounts and staff information.
            </p>

        </section>


        <section class="content-section">

            <a
                href="add_staff.php"
                class="btn btn-primary"
            >
                Add Staff Account
            </a>

            <br><br>


            <table>

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Full Name
                        </th>

                        <th>
                            Username
                        </th>

                        <th>
                            Contact Number
                        </th>

                        <th>
                            Position
                        </th>

                        <th>
                            Account Status
                        </th>

                        <th>
                            Availability
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($staff = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["staff_id"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["full_name"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["username"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["contact_number"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["position"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["status"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["availability_status"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>

                                    <a
                                        href="edit_staff.php?id=<?php echo $staff["user_id"]; ?>"
                                        class="btn btn-primary"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete_staff.php?id=<?php echo $staff["user_id"]; ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this staff account?');"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8">
                                No staff accounts found.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </section>

    </main>

</body>

</html>