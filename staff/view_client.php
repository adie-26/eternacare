<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: clients.php");
    exit();
}

$client_id = (int) $_GET["id"];


/* Get client information */

$sql = "SELECT
            client_id,
            full_name,
            contact_number,
            email,
            address,
            relationship_to_deceased,
            created_at
        FROM clients
        WHERE client_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $client_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: clients.php");
    exit();
}

$client = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EternaCare - Client Details</title>

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

            <h1>Client Details</h1>

            <p>
                View complete client information.
            </p>

        </section>


        <section class="content-section">

            <h2>
                <?php echo htmlspecialchars($client["full_name"]); ?>
            </h2>

            <br>


            <table>

                <tr>

                    <th>Client ID</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $client["client_id"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Full Name</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $client["full_name"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Contact Number</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $client["contact_number"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Email</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $client["email"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Address</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $client["address"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Relationship to Deceased</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $client[
                                "relationship_to_deceased"
                            ] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Registered Date</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $client["created_at"]
                        );
                        ?>
                    </td>

                </tr>

            </table>


            <br>


            <a
                href="edit_client.php?id=<?php echo $client["client_id"]; ?>"
                class="btn btn-primary"
            >
                Edit Client
            </a>


            <a
                href="clients.php"
                class="btn"
            >
                Back to Clients
            </a>

        </section>


    </main>


</body>

</html>