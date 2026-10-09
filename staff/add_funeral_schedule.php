<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";


/* Get clients */

$clients_sql = "SELECT
                    client_id,
                    full_name
                FROM clients
                ORDER BY full_name ASC";

$clients_result = $conn->query($clients_sql);


/* Get deceased records */

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
    $notes = trim($_POST["notes"] ?? "");


    if (
        empty($client_id) ||
        empty($deceased_id) ||
        empty($package_id) ||
        empty($service_start) ||
        empty($service_end) ||
        empty($location)
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

    } else {

        $client_id = (int) $client_id;
        $deceased_id = (int) $deceased_id;
        $package_id = (int) $package_id;


        /*
         * Verify that the selected deceased belongs
         * to the selected client.
         */

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


            /*
             * Get package price.
             */

            $package_sql = "SELECT price
                            FROM packages
                            WHERE package_id = ?
                            AND status = 'Available'
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

                $error = "The selected funeral package is not available.";

            } else {

                $package = $package_result->fetch_assoc();

                $total_cost = (float) $package["price"];


                /*
                 * Check for schedule conflicts.
                 *
                 * A conflict exists when the new schedule
                 * overlaps an existing non-cancelled schedule.
                 */

                $conflict_sql = "SELECT funeral_service_id
                                 FROM funeral_services
                                 WHERE status != 'Cancelled'
                                 AND service_start < ?
                                 AND service_end > ?
                                 LIMIT 1";

                $conflict_stmt = $conn->prepare($conflict_sql);

                $conflict_stmt->bind_param(
                    "ss",
                    $service_end,
                    $service_start
                );

                $conflict_stmt->execute();

                $conflict_result = $conflict_stmt->get_result();

                $conflict_stmt->close();


                if ($conflict_result->num_rows > 0) {

                    $error = "Schedule conflict detected. The selected time overlaps with an existing funeral schedule.";

                } else {


                    /*
                     * Insert funeral schedule.
                     */

                    $insert_sql = "INSERT INTO funeral_services
                                   (
                                       client_id,
                                       deceased_id,
                                       package_id,
                                       service_start,
                                       service_end,
                                       location,
                                       total_cost,
                                       status,
                                       notes
                                   )
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled', ?)";

                    $insert_stmt = $conn->prepare($insert_sql);

                    $insert_stmt->bind_param(
                        "iiisssds",
                        $client_id,
                        $deceased_id,
                        $package_id,
                        $service_start,
                        $service_end,
                        $location,
                        $total_cost,
                        $notes
                    );


                    if ($insert_stmt->execute()) {

                        $new_id = $insert_stmt->insert_id;

                        $insert_stmt->close();

                        header(
                            "Location: view_funeral_schedule.php?id="
                            . $new_id
                        );

                        exit();

                    } else {

                        $error = "Error adding funeral schedule: "
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

    <title>EternaCare - Add Funeral Schedule</title>

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

            <h1>Add Funeral Schedule</h1>

            <p>
                Create a new funeral service schedule.
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
                                    (
                                        $_POST["client_id"]
                                        ?? ""
                                    ) == $client["client_id"]
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
                                    (
                                        $_POST["deceased_id"]
                                        ?? ""
                                    ) == $deceased["deceased_id"]
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


                <!-- Funeral Package -->

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
                                    (
                                        $_POST["package_id"]
                                        ?? ""
                                    ) == $package["package_id"]
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
                                $_POST["service_start"] ?? ""
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
                                $_POST["service_end"] ?? ""
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
                                $_POST["location"] ?? ""
                            );
                        ?>"
                        placeholder="Enter funeral service location"
                        required
                    >

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
                        placeholder="Enter additional notes"
                    ><?php
                        echo htmlspecialchars(
                            $_POST["notes"] ?? ""
                        );
                    ?></textarea>

                </div>


                <br>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Funeral Schedule
                </button>


                <a
                    href="funeral_schedules.php"
                    class="btn"
                >
                    Cancel
                </a>


            </form>


        </section>


    </main>


</body>

</html>