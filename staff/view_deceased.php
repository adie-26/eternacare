<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/* Validate ID */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: deceased.php");
    exit();
}

$deceased_id = (int) $_GET["id"];


/* Get deceased information */

$sql = "SELECT
            d.deceased_id,
            d.full_name,
            d.date_of_birth,
            d.date_of_death,
            d.age,
            d.address,
            d.cause_of_death,
            c.client_id,
            c.full_name AS client_name,
            c.contact_number AS client_contact,
            c.email AS client_email,
            c.relationship_to_deceased
        FROM deceased d
        LEFT JOIN clients c
            ON d.client_id = c.client_id
        WHERE d.deceased_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $deceased_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: deceased.php");
    exit();
}


$deceased = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>EternaCare - Deceased Details</title>

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

            <h1>Deceased Details</h1>

            <p>
                View complete information about the deceased individual.
            </p>

        </section>


        <!-- Deceased Information -->

        <section class="content-section">

            <h2>Deceased Information</h2>

            <br>


            <table>

                <tr>

                    <th>Deceased ID</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["deceased_id"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Full Name</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["full_name"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Date of Birth</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["date_of_birth"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Date of Death</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["date_of_death"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Age</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["age"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Address</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["address"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Cause of Death</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["cause_of_death"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>

            </table>

        </section>


        <br>


        <!-- Client Information -->

        <section class="content-section">

            <h2>Related Client</h2>

            <br>


            <table>

                <tr>

                    <th>Client ID</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["client_id"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Client Name</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["client_name"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Contact Number</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["client_contact"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Email</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["client_email"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>Relationship to Deceased</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $deceased["relationship_to_deceased"] ?? "-"
                        );
                        ?>
                    </td>

                </tr>

            </table>

        </section>


        <br>


        <!-- Buttons -->

        <section class="content-section">

            <a
                href="edit_deceased.php?id=<?php echo $deceased["deceased_id"]; ?>"
                class="btn btn-primary"
            >
                Edit Deceased Record
            </a>


            <a
                href="deceased.php"
                class="btn"
            >
                Back to Deceased Records
            </a>

        </section>


    </main>


</body>

</html>