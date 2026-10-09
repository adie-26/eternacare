<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get funeral service ID from URL.
 */

$funeral_service_id = isset($_GET["funeral_service_id"])
    ? intval($_GET["funeral_service_id"])
    : 0;


/*
 * Make sure a valid funeral service was selected.
 */

if ($funeral_service_id <= 0) {

    header("Location: vehicle_assignments.php");
    exit();

}


/*
 * Get funeral service information.
 */

$service_sql = "SELECT
                    fs.funeral_service_id,
                    fs.service_start,
                    fs.service_end,
                    fs.location,
                    fs.status,

                    c.full_name AS client_name,

                    d.full_name AS deceased_name

                FROM funeral_services fs

                INNER JOIN clients c
                    ON fs.client_id = c.client_id

                INNER JOIN deceased d
                    ON fs.deceased_id = d.deceased_id

                WHERE fs.funeral_service_id = ?";

$service_stmt = $conn->prepare($service_sql);

$service_stmt->bind_param(
    "i",
    $funeral_service_id
);

$service_stmt->execute();

$service_result = $service_stmt->get_result();

$service = $service_result->fetch_assoc();

$service_stmt->close();


/*
 * If the funeral service does not exist,
 * return to vehicle assignments.
 */

if (!$service) {

    header("Location: vehicle_assignments.php");
    exit();

}


/*
 * Get available vehicles.
 */

$vehicles_sql = "SELECT
                     vehicle_id,
                     vehicle_name,
                     vehicle_type,
                     plate_number,
                     availability_status

                 FROM vehicles

                 WHERE availability_status = 'Available'

                 ORDER BY vehicle_name ASC";

$vehicles_result = $conn->query($vehicles_sql);


/*
 * Handle form submission.
 */

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $vehicle_id = isset($_POST["vehicle_id"])
        ? intval($_POST["vehicle_id"])
        : 0;


    /*
     * Validate vehicle selection.
     */

    if ($vehicle_id <= 0) {

        $error = "Please select a vehicle.";

    } else {


        /*
         * Check if the vehicle exists
         * and is currently available.
         */

        $vehicle_sql = "SELECT
                            vehicle_id,
                            vehicle_name,
                            availability_status

                        FROM vehicles

                        WHERE vehicle_id = ?

                        AND availability_status = 'Available'";

        $vehicle_stmt = $conn->prepare($vehicle_sql);

        $vehicle_stmt->bind_param(
            "i",
            $vehicle_id
        );

        $vehicle_stmt->execute();

        $vehicle_result = $vehicle_stmt->get_result();

        $vehicle = $vehicle_result->fetch_assoc();

        $vehicle_stmt->close();


        if (!$vehicle) {

            $error = "The selected vehicle is not available.";

        } else {


            /*
             * Check whether this vehicle is already
             * assigned to this funeral service.
             */

            $duplicate_sql = "SELECT
                                  vehicle_assignment_id

                              FROM vehicle_assignments

                              WHERE funeral_service_id = ?

                              AND vehicle_id = ?

                              LIMIT 1";

            $duplicate_stmt = $conn->prepare(
                $duplicate_sql
            );

            $duplicate_stmt->bind_param(
                "ii",
                $funeral_service_id,
                $vehicle_id
            );

            $duplicate_stmt->execute();

            $duplicate_result =
                $duplicate_stmt->get_result();

            $duplicate = $duplicate_result->fetch_assoc();

            $duplicate_stmt->close();


            if ($duplicate) {

                $error =
                    "This vehicle is already assigned to this funeral service.";

            } else {


                /*
                 * Check if the vehicle is already assigned
                 * to another active funeral service that
                 * overlaps with this schedule.
                 */

                $conflict_sql = "SELECT
                                     va.vehicle_assignment_id

                                 FROM vehicle_assignments va

                                 INNER JOIN funeral_services fs
                                     ON va.funeral_service_id =
                                        fs.funeral_service_id

                                 WHERE va.vehicle_id = ?

                                 AND va.funeral_service_id != ?

                                 AND fs.status != 'Cancelled'

                                 AND fs.service_start < ?

                                 AND fs.service_end > ?

                                 LIMIT 1";

                $conflict_stmt = $conn->prepare(
                    $conflict_sql
                );

                $conflict_stmt->bind_param(
                    "iiss",
                    $vehicle_id,
                    $funeral_service_id,
                    $service["service_end"],
                    $service["service_start"]
                );

                $conflict_stmt->execute();

                $conflict_result =
                    $conflict_stmt->get_result();

                $conflict = $conflict_result->fetch_assoc();

                $conflict_stmt->close();


                if ($conflict) {

                    $error =
                        "This vehicle is already assigned to another funeral service during the selected schedule.";

                } else {


                    /*
                     * Insert vehicle assignment.
                     */

                    $insert_sql = "INSERT INTO vehicle_assignments
                                   (
                                       funeral_service_id,
                                       vehicle_id
                                   )

                                   VALUES (?, ?)";

                    $insert_stmt =
                        $conn->prepare($insert_sql);

                    $insert_stmt->bind_param(
                        "ii",
                        $funeral_service_id,
                        $vehicle_id
                    );


                    if ($insert_stmt->execute()) {

                        $insert_stmt->close();

                        header(
                            "Location: vehicle_assignments.php"
                        );

                        exit();

                    } else {

                        $error =
                            "Error assigning vehicle: "
                            . $insert_stmt->error;

                        $insert_stmt->close();

                    }

                }

            }

        }

    }

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
        EternaCare - Assign Vehicle
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
                Assign Vehicle
            </h1>

            <p>
                Assign a vehicle to a funeral service.
            </p>

        </section>


        <!-- Funeral Service Information -->

        <section class="content-section">

            <h2>
                Funeral Service Information
            </h2>


            <p>
                <strong>Service ID:</strong>
                #<?php
                echo htmlspecialchars(
                    $service["funeral_service_id"]
                );
                ?>
            </p>


            <p>
                <strong>Client:</strong>
                <?php
                echo htmlspecialchars(
                    $service["client_name"]
                );
                ?>
            </p>


            <p>
                <strong>Deceased:</strong>
                <?php
                echo htmlspecialchars(
                    $service["deceased_name"]
                );
                ?>
            </p>


            <p>
                <strong>Start:</strong>
                <?php
                echo htmlspecialchars(
                    date(
                        "F d, Y h:i A",
                        strtotime(
                            $service["service_start"]
                        )
                    )
                );
                ?>
            </p>


            <p>
                <strong>End:</strong>
                <?php
                echo htmlspecialchars(
                    date(
                        "F d, Y h:i A",
                        strtotime(
                            $service["service_end"]
                        )
                    )
                );
                ?>
            </p>


            <p>
                <strong>Location:</strong>
                <?php
                echo htmlspecialchars(
                    $service["location"]
                );
                ?>
            </p>


            <p>
                <strong>Status:</strong>
                <?php
                echo htmlspecialchars(
                    $service["status"]
                );
                ?>
            </p>

        </section>


        <br>


        <!-- Assignment Form -->

        <section class="content-section">

            <h2>
                Select Vehicle
            </h2>


            <?php if ($error !== ""): ?>

                <p style="color: red;">
                    <?php
                    echo htmlspecialchars($error);
                    ?>
                </p>

            <?php endif; ?>


            <?php if ($vehicles_result->num_rows > 0): ?>

                <form method="POST">


                    <div class="form-group">

                        <label for="vehicle_id">
                            Vehicle
                        </label>


                        <select
                            name="vehicle_id"
                            id="vehicle_id"
                            required
                        >

                            <option value="">
                                -- Select Vehicle --
                            </option>


                            <?php while (
                                $vehicle =
                                $vehicles_result->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?php echo $vehicle["vehicle_id"]; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle["vehicle_name"]
                                    );
                                    ?>

                                    -
                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle["vehicle_type"]
                                    );
                                    ?>

                                    -
                                    <?php
                                    echo htmlspecialchars(
                                        $vehicle["plate_number"]
                                    );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <br>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Assign Vehicle
                    </button>


                    <a
                        href="vehicle_assignments.php"
                        class="btn"
                    >
                        Cancel
                    </a>


                </form>


            <?php else: ?>

                <p>
                    No available vehicles found.
                </p>

                <p>
                    Add a vehicle first or make an existing
                    vehicle available before assigning it.
                </p>


                <br>


                <a
                    href="vehicle_assignments.php"
                    class="btn"
                >
                    Back to Vehicle Assignments
                </a>

            <?php endif; ?>

        </section>


    </main>


</body>

</html>