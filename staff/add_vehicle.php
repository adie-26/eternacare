<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";


/*
 * Handle form submission.
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $vehicle_name = trim($_POST["vehicle_name"] ?? "");
    $vehicle_type = trim($_POST["vehicle_type"] ?? "");
    $plate_number = trim($_POST["plate_number"] ?? "");
    $availability_status =
        $_POST["availability_status"] ?? "Available";


    /*
     * Validate required fields.
     */

    if (
        $vehicle_name === "" ||
        $vehicle_type === "" ||
        $plate_number === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (
        !in_array(
            $availability_status,
            ["Available", "Unavailable"]
        )
    ) {

        $error = "Invalid availability status.";

    } else {


        /*
         * Check if the plate number already exists.
         */

        $check_sql = "SELECT vehicle_id
                      FROM vehicles
                      WHERE plate_number = ?
                      LIMIT 1";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "s",
            $plate_number
        );

        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        $existing_vehicle =
            $check_result->fetch_assoc();

        $check_stmt->close();


        if ($existing_vehicle) {

            $error =
                "A vehicle with this plate number already exists.";

        } else {


            /*
             * Insert the vehicle.
             */

            $insert_sql = "INSERT INTO vehicles
                           (
                               vehicle_name,
                               vehicle_type,
                               plate_number,
                               availability_status
                           )

                           VALUES (?, ?, ?, ?)";

            $insert_stmt =
                $conn->prepare($insert_sql);

            $insert_stmt->bind_param(
                "ssss",
                $vehicle_name,
                $vehicle_type,
                $plate_number,
                $availability_status
            );


            if ($insert_stmt->execute()) {

                $vehicle_id =
                    $insert_stmt->insert_id;

                $insert_stmt->close();

                header(
                    "Location: view_vehicle.php?id="
                    . $vehicle_id
                );

                exit();

            } else {

                $error =
                    "Error adding vehicle: "
                    . $insert_stmt->error;

                $insert_stmt->close();

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
        EternaCare - Add Vehicle
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
                Add Vehicle
            </h1>

            <p>
                Add a vehicle for funeral service operations.
            </p>

        </section>


        <!-- Form -->

        <section class="content-section">

            <?php if ($error !== ""): ?>

                <p style="color: red;">
                    <?php
                    echo htmlspecialchars($error);
                    ?>
                </p>

            <?php endif; ?>


            <form method="POST">


                <div class="form-group">

                    <label for="vehicle_name">
                        Vehicle Name *
                    </label>

                    <input
                        type="text"
                        name="vehicle_name"
                        id="vehicle_name"
                        placeholder="Example: Funeral Hearse 01"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST["vehicle_name"] ?? ""
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="vehicle_type">
                        Vehicle Type *
                    </label>

                    <input
                        type="text"
                        name="vehicle_type"
                        id="vehicle_type"
                        placeholder="Example: Hearse"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST["vehicle_type"] ?? ""
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="plate_number">
                        Plate Number *
                    </label>

                    <input
                        type="text"
                        name="plate_number"
                        id="plate_number"
                        placeholder="Example: ABC 1234"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST["plate_number"] ?? ""
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="availability_status">
                        Availability Status
                    </label>

                    <select
                        name="availability_status"
                        id="availability_status"
                    >

                        <option
                            value="Available"
                            <?php
                            echo (
                                ($_POST[
                                    "availability_status"
                                ] ?? "Available")
                                === "Available"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Available
                        </option>

                        <option
                            value="Unavailable"
                            <?php
                            echo (
                                ($_POST[
                                    "availability_status"
                                ] ?? "")
                                === "Unavailable"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Unavailable
                        </option>

                    </select>

                </div>


                <br>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Vehicle
                </button>


                <a
                    href="vehicles.php"
                    class="btn"
                >
                    Cancel
                </a>


            </form>

        </section>


    </main>


</body>

</html>