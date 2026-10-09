<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get vehicle ID from URL.
 */

$vehicle_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


/*
 * Validate vehicle ID.
 */

if ($vehicle_id <= 0) {

    header("Location: vehicles.php");
    exit();

}


/*
 * Get vehicle information.
 */

$sql = "SELECT
            vehicle_id,
            vehicle_name,
            vehicle_type,
            plate_number,
            availability_status,
            created_at

        FROM vehicles

        WHERE vehicle_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $vehicle_id
);

$stmt->execute();

$result = $stmt->get_result();

$vehicle = $result->fetch_assoc();

$stmt->close();


/*
 * If vehicle does not exist,
 * return to vehicle list.
 */

if (!$vehicle) {

    header("Location: vehicles.php");
    exit();

}

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
        EternaCare - View Vehicle
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
                <a href="vehicles.php">
                    Vehicles
                </a>
            </li>

            <li>
                <a href="#">
                    Preparation Tasks
                </a>
            </li>

            <li>
                <a href="#">
                    Payments
                </a>
            </li>

            <li>
                <a href="#">
                    Service History
                </a>
            </li>

            <li>
                <a href="#">
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
                Vehicle Details
            </h1>

            <p>
                View vehicle information.
            </p>

        </section>


        <!-- Vehicle Information -->

        <section class="content-section">

            <h2>
                Vehicle Information
            </h2>


            <table>

                <tr>

                    <th>
                        Vehicle ID
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $vehicle["vehicle_id"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Vehicle Name
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $vehicle["vehicle_name"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Vehicle Type
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $vehicle["vehicle_type"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Plate Number
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $vehicle["plate_number"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Availability Status
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $vehicle[
                                "availability_status"
                            ]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Created At
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            date(
                                "F d, Y h:i A",
                                strtotime(
                                    $vehicle["created_at"]
                                )
                            )
                        );
                        ?>
                    </td>

                </tr>

            </table>


            <br>


            <a
                href="edit_vehicle.php?id=<?php echo $vehicle["vehicle_id"]; ?>"
                class="btn btn-primary"
            >
                Edit Vehicle
            </a>


            <a
                href="vehicles.php"
                class="btn"
            >
                Back to Vehicles
            </a>

        </section>


    </main>


</body>

</html>