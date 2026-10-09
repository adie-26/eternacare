<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/* Get all clients */

$sql = "SELECT
            client_id,
            full_name,
            contact_number,
            email,
            address,
            relationship_to_deceased,
            created_at
        FROM clients
        ORDER BY client_id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EternaCare - Clients</title>

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

            <h1>Clients</h1>

            <p>
                View and manage registered EternaCare clients.
            </p>

        </section>


        <section class="content-section">

        <a
    href="add_client.php"
    class="btn btn-primary"
>
    Add Client
</a>

            <br>
            <br>


            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Full Name</th>

                        <th>Contact Number</th>

                        <th>Email</th>

                        <th>Address</th>

                        <th>Relationship</th>

                        <th>Registered</th>
                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($client = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $client["client_id"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $client["full_name"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $client["contact_number"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $client["email"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $client["address"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $client[
                                            "relationship_to_deceased"
                                        ] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $client["created_at"]
                                    );
                                    ?>
                                </td>

            <td>

    <a
        href="view_client.php?id=<?php echo $client["client_id"]; ?>"
        class="btn btn-primary"
    >
        View
    </a>

    <a
        href="edit_client.php?id=<?php echo $client["client_id"]; ?>"
        class="btn btn-primary"
    >
        Edit
    </a>

</td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8">
                                No clients found.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </section>


    </main>


</body>

</html>