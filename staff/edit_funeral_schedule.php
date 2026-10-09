<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$id = $_GET["id"] ?? "";

if (!is_numeric($id)) {
    die("Invalid funeral schedule ID.");
}

$id = (int) $id;

$error = "";


/* Get existing schedule */

$sql = "SELECT *
        FROM funeral_services
        WHERE funeral_service_id = ?
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


/* Get clients */

$clients_sql = "SELECT
                    client_id,
                    full_name
                FROM clients
                ORDER BY full_name ASC";

$clients_result = $conn->query($clients_sql);


/* Get deceased */

$deceased_sql = "SELECT
                     deceased_id,
                     client_id,
                     full_name
                 FROM deceased
                 ORDER BY full_name ASC";

$deceased_result = $conn->query($deceased_sql);


/* Get available packages */

$packages_sql = "SELECT
                     package_id,
                     package_name,
                     price
                 FROM packages
                 WHERE status = 'Available'
                    OR package_id = " . (int) $schedule["package_id"] . "
                 ORDER BY package_name ASC";

$packages_result = $conn->query($packages_sql);


/* Process form */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $client_id = $_POST["client_id"] ?? "";
    $deceased_id = $_POST["deceased_id"] ?? "";
    $package_id = $_POST["package_id"] ?? "";
    $service_start = $_POST["service_start"] ?? "";
    $service_end = $_POST["service_end"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $status = $_POST["status"] ?? "";
    $notes = trim($_POST["notes"] ?? "");


    if (
        empty($client_id) ||
        empty($deceased_id) ||
        empty($package_id) ||
        empty($service_start) ||
        empty($service_end) ||
        empty($location) ||
        empty($status)
    ) {

        $error = "Please fill in all required fields.";

    } elseif (
        !is_numeric($client_id) ||
        !is_numeric($deceased_id) ||
        !is_numeric($package_id)
    ) {

        $error = "Invalid client, deceased, or package selected.";

    } elseif (
        strtotime($service_start) === false ||
        strtotime($service_end) === false
    ) {

        $error = "Please enter valid schedule dates and times.";

    } elseif (
        strtotime($service_end) <= strtotime($service_start)
    ) {

        $error = "The end date and time must be later than the start date and time.";

    } elseif (
        !in_array(
            $status,
            [
                "Scheduled",
                "Preparing",
                "Ongoing",
                "Completed",
                "Cancelled"
            ]
        )
    ) {

        $error = "Invalid schedule status.";

    } else {

        $client_id = (int) $client_id;
        $deceased_id = (int) $deceased_id;
        $package_id = (int) $package_id;


        /* Verify deceased belongs to client */

        $verify_sql = "SELECT deceased_id
                       FROM deceased
                       WHERE deceased_id = ?
                       AND client_id = ?
                       LIMIT 1";

        $verify_stmt = $conn->prepare($verify_sql);

        $verify_stmt->bind_param(
            "ii",
            $deceased_id,
            $client_id
        );

        $verify_stmt->execute();

        $verify_result = $verify_stmt->get_result();

        $verify_stmt->close();


        if ($verify_result->num_rows !== 1) {

            $error = "The selected deceased record does not belong to the selected client.";

        } else {


            /* Get package price */

            $package_sql = "SELECT price
                            FROM packages
                            WHERE package_id = ?
                            LIMIT 1";

            $package_stmt = $conn->prepare($package_sql);

            $package_stmt->bind_param(
                "i",
                $package_id
            );

            $package_stmt->execute();

            $package_result = $package_stmt->get_result();

            $package_stmt->close();


            if ($package_result->num_rows !== 1) {

                $error = "The selected funeral package was not found.";

            } else {

                $package = $package_result->fetch_assoc();

                $total_cost = (float) $package["price"];


                /* Check schedule conflicts */

                $conflict_sql = "SELECT funeral_service_id
                                 FROM funeral_services
                                 WHERE funeral_service_id != ?
                                 AND status != 'Cancelled'
                                 AND service_start < ?
                                 AND service_end > ?
                                 LIMIT 1";

                $conflict_stmt = $conn->prepare($conflict_sql);

                $conflict_stmt->bind_param(
                    "iss",
                    $id,
                    $service_end,
                    $service_start
                );

                $conflict_stmt->execute();

                $conflict_result = $conflict_stmt->get_result();

                $conflict_stmt->close();


                if ($conflict_result->num_rows > 0) {

                    $error = "Schedule conflict detected. The selected time overlaps with another funeral schedule.";

                } else {


                    /* Update schedule */

                    $update_sql = "UPDATE funeral_services
                                   SET
                                       client_id = ?,
                                       deceased_id = ?,
                                       package_id = ?,
                                       service_start = ?,
                                       service_end = ?,
                                       location = ?,
                                       total_cost = ?,
                                       status = ?,
                                       notes = ?
                                   WHERE funeral_service_id = ?";

                    $update_stmt = $conn->prepare($update_sql);

                    $update_stmt->bind_param(
                        "iiisssdssi",
                        $client_id,
                        $deceased_id,
                        $package_id,
                        $service_start,
                        $service_end,
                        $location,
                        $total_cost,
                        $status,
                        $notes,
                        $id
                    );


                    if ($update_stmt->execute()) {

                        $update_stmt->close();

                        header(
                            "Location: view_funeral_schedule.php?id="
                            . $id
                        );

                        exit();

                    } else {

                        $error = "Error updating funeral schedule: "
                            . $update_stmt->error;

                        $update_stmt->close();
                    }
                }
            }
        }
    }


    /* Keep submitted values on error */

    $schedule["client_id"] = $client_id;
    $schedule["deceased_id"] = $deceased_id;
    $schedule["package_id"] = $package_id;
    $schedule["service_start"] = $service_start;
    $schedule["service_end"] = $service_end;
    $schedule["location"] = $location;
    $schedule["status"] = $status;
    $schedule["notes"] = $notes;
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
        EternaCare - Edit Funeral Schedule
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
                <a href="clients.php">Clients</a>
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
                Edit Funeral Schedule
            </h1>

            <p>
                Update the funeral service schedule information.
            </p>

        </section>


        <section class="content-section">


            <?php if (!empty($error)): ?>

                <p style="color: red;">

                    <?php echo htmlspecialchars($error); ?>

                </p>

            <?php endif; ?>


            <form method="POST" action="">


                <!-- Client -->

                <div class="form-group">

                    <label for="client_id">
                        Client *
                    </label>

                    <select
                        id="client_id"
                        name="client_id"
                        required
                    >

                        <option value="">
                            Select Client
                        </option>

                        <?php while ($client = $clients_result->fetch_assoc()): ?>

                            <option
                                value="<?php echo $client["client_id"]; ?>"
                                <?php
                                if (
                                    $schedule["client_id"]
                                    == $client["client_id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $client["full_name"]
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <br>


                <!-- Deceased -->

                <div class="form-group">

                    <label for="deceased_id">
                        Deceased *
                    </label>

                    <select
                        id="deceased_id"
                        name="deceased_id"
                        required
                    >

                        <option value="">
                            Select Deceased
                        </option>

                        <?php while ($deceased = $deceased_result->fetch_assoc()): ?>

                            <option
                                value="<?php echo $deceased["deceased_id"]; ?>"
                                <?php
                                if (
                                    $schedule["deceased_id"]
                                    == $deceased["deceased_id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $deceased["full_name"]
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <br>


                <!-- Package -->

                <div class="form-group">

                    <label for="package_id">
                        Funeral Package *
                    </label>

                    <select
                        id="package_id"
                        name="package_id"
                        required
                    >

                        <option value="">
                            Select Funeral Package
                        </option>

                        <?php while ($package = $packages_result->fetch_assoc()): ?>

                            <option
                                value="<?php echo $package["package_id"]; ?>"
                                <?php
                                if (
                                    $schedule["package_id"]
                                    == $package["package_id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $package["package_name"]
                                );
                                ?>

                                - ₱<?php
                                echo number_format(
                                    $package["price"],
                                    2
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <br>


                <!-- Start -->

                <div class="form-group">

                    <label for="service_start">
                        Service Start *
                    </label>

                    <input
                        type="datetime-local"
                        id="service_start"
                        name="service_start"
                        value="<?php
                            echo htmlspecialchars(
                                date(
                                    "Y-m-d\TH:i",
                                    strtotime(
                                        $schedule["service_start"]
                                    )
                                )
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- End -->

                <div class="form-group">

                    <label for="service_end">
                        Service End *
                    </label>

                    <input
                        type="datetime-local"
                        id="service_end"
                        name="service_end"
                        value="<?php
                            echo htmlspecialchars(
                                date(
                                    "Y-m-d\TH:i",
                                    strtotime(
                                        $schedule["service_end"]
                                    )
                                )
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- Location -->

                <div class="form-group">

                    <label for="location">
                        Location *
                    </label>

                    <input
                        type="text"
                        id="location"
                        name="location"
                        value="<?php
                            echo htmlspecialchars(
                                $schedule["location"]
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- Status -->

                <div class="form-group">

                    <label for="status">
                        Status *
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="Scheduled"
                            <?php
                            if (
                                $schedule["status"]
                                === "Scheduled"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Scheduled
                        </option>

                        <option
                            value="Preparing"
                            <?php
                            if (
                                $schedule["status"]
                                === "Preparing"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Preparing
                        </option>

                        <option
                            value="Ongoing"
                            <?php
                            if (
                                $schedule["status"]
                                === "Ongoing"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Ongoing
                        </option>

                        <option
                            value="Completed"
                            <?php
                            if (
                                $schedule["status"]
                                === "Completed"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Completed
                        </option>

                        <option
                            value="Cancelled"
                            <?php
                            if (
                                $schedule["status"]
                                === "Cancelled"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Cancelled
                        </option>

                    </select>

                </div>


                <br>


                <!-- Notes -->

                <div class="form-group">

                    <label for="notes">
                        Notes
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="5"
                    ><?php
                        echo htmlspecialchars(
                            $schedule["notes"] ?? ""
                        );
                    ?></textarea>

                </div>


                <br>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>


                <a
                    href="view_funeral_schedule.php?id=<?php echo $id; ?>"
                    class="btn"
                >
                    Cancel
                </a>


            </form>


        </section>


    </main>


</body>

</html>