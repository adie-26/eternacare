<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$id = $_GET["id"] ?? "";

if (!is_numeric($id)) {
    die("Invalid funeral schedule ID.");
}

$id = (int) $id;

$sql = "SELECT
            fs.funeral_service_id,
            fs.service_start,
            fs.service_end,
            fs.location,
            fs.total_cost,
            fs.status,
            fs.notes,
            fs.created_at,

            c.full_name AS client_name,
            c.contact_number,
            c.email,
            c.address AS client_address,

            d.full_name AS deceased_name,
            d.date_of_birth,
            d.date_of_death,
            d.age,
            d.cause_of_death,

            p.package_name,
            p.description AS package_description,
            p.price AS package_price

        FROM funeral_services fs

        INNER JOIN clients c
            ON fs.client_id = c.client_id

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        INNER JOIN packages p
            ON fs.package_id = p.package_id

        WHERE fs.funeral_service_id = ?

        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Funeral schedule not found.");
}

$schedule = $result->fetch_assoc();

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

    <title>
        EternaCare - View Funeral Schedule
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
                Funeral Schedule Details
            </h1>

            <p>
                View the complete funeral service schedule information.
            </p>

        </section>


        <section class="content-section">


            <h2>Schedule Information</h2>

            <table>

                <tr>
                    <th>Schedule ID</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["funeral_service_id"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Service Start</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            date(
                                "F d, Y h:i A",
                                strtotime(
                                    $schedule["service_start"]
                                )
                            )
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Service End</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            date(
                                "F d, Y h:i A",
                                strtotime(
                                    $schedule["service_end"]
                                )
                            )
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Location</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["location"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Status</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["status"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Total Cost</th>

                    <td>
                        ₱<?php
                        echo number_format(
                            $schedule["total_cost"],
                            2
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Created At</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            date(
                                "F d, Y h:i A",
                                strtotime(
                                    $schedule["created_at"]
                                )
                            )
                        );
                        ?>
                    </td>
                </tr>

            </table>


            <br>


            <h2>Client Information</h2>

            <table>

                <tr>
                    <th>Client Name</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["client_name"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Contact Number</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["contact_number"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Email</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["email"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Address</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["client_address"]
                        );
                        ?>
                    </td>
                </tr>

            </table>


            <br>


            <h2>Deceased Information</h2>

            <table>

                <tr>
                    <th>Deceased Name</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["deceased_name"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Date of Birth</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["date_of_birth"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Date of Death</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["date_of_death"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Age</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["age"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Cause of Death</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["cause_of_death"]
                        );
                        ?>
                    </td>
                </tr>

            </table>


            <br>


            <h2>Funeral Package</h2>

            <table>

                <tr>
                    <th>Package Name</th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $schedule["package_name"]
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Package Description</th>

                    <td>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $schedule["package_description"]
                            )
                        );
                        ?>
                    </td>
                </tr>


                <tr>
                    <th>Package Price</th>

                    <td>
                        ₱<?php
                        echo number_format(
                            $schedule["package_price"],
                            2
                        );
                        ?>
                    </td>
                </tr>

            </table>


            <br>


            <h2>Notes</h2>

            <p>

                <?php

                if (!empty($schedule["notes"])) {

                    echo nl2br(
                        htmlspecialchars(
                            $schedule["notes"]
                        )
                    );

                } else {

                    echo "No additional notes.";

                }

                ?>

            </p>


            <br>


            <a
                href="edit_funeral_schedule.php?id=<?php echo $id; ?>"
                class="btn btn-primary"
            >
                Edit Schedule
            </a>


            <a
                href="funeral_schedules.php"
                class="btn"
            >
                Back to Funeral Schedules
            </a>


        </section>


    </main>


</body>

</html>