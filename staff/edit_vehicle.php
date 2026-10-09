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
 * Get current vehicle information.
 */

$sql = "SELECT
            vehicle_id,
            vehicle_name,
            vehicle_type,
            plate_number,
            availability_status

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


$error = "";


/*
 * Handle form submission.
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $vehicle_name = trim(
        $_POST["vehicle_name"] ?? ""
    );

    $vehicle_type = trim(
        $_POST["vehicle_type"] ?? ""
    );

    $plate_number = trim(
        $_POST["plate_number"] ?? ""
    );

    $availability_status =
        $_POST["availability_status"] ?? "";


    /*
     * Validate required fields.
     */

    if (
        $vehicle_name === "" ||
        $vehicle_type === "" ||
        $plate_number === ""
    ) {

        $error =
            "Please fill in all required fields.";

    } elseif (
        !in_array(
            $availability_status,
            ["Available", "Unavailable"]
        )
    ) {

        $error =
            "Invalid availability status.";

    } else {


        /*
         * Check if another vehicle already
         * uses the same plate number.
         */

        $check_sql = "SELECT
                          vehicle_id

                      FROM vehicles

                      WHERE plate_number = ?

                      AND vehicle_id != ?

                      LIMIT 1";

        $check_stmt =
            $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "si",
            $plate_number,
            $vehicle_id
        );

        $check_stmt->execute();

        $check_result =
            $check_stmt->get_result();

        $existing_vehicle =
            $check_result->fetch_assoc();

        $check_stmt->close();


        if ($existing_vehicle) {

            $error =
                "Another vehicle already uses this plate number.";

        } else {


            /*
             * Update vehicle information.
             */

            $update_sql = "UPDATE vehicles

                           SET
                               vehicle_name = ?,
                               vehicle_type = ?,
                               plate_number = ?,
                               availability_status = ?

                           WHERE vehicle_id = ?";

            $update_stmt =
                $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "ssssi",
                $vehicle_name,
                $vehicle_type,
                $plate_number,
                $availability_status,
                $vehicle_id
            );


            if ($update_stmt->execute()) {

                $update_stmt->close();

                header(
                    "Location: view_vehicle.php?id="
                    . $vehicle_id
                );

                exit();

            } else {

                $error =
                    "Error updating vehicle: "
                    . $update_stmt->error;

                $update_stmt->close();

            }

        }

    }


    /*
     * Keep entered values in the form
     * when there is an error.
     */

    $vehicle["vehicle_name"] =
        $vehicle_name;

    $vehicle["vehicle_type"] =
        $vehicle_type;

    $vehicle["plate_number"] =
        $plate_number;

    $vehicle["availability_status"] =
        $availability_status;

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
        EternaCare - Edit Vehicle
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
                Edit Vehicle
            </h1>

            <p>
                Update vehicle information.
            </p>

        </section>


        <!-- Edit Form -->

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
                        value="<?php
                        echo htmlspecialchars(
                            $vehicle[
                                "vehicle_name"
                            ]
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
                        value="<?php
                        echo htmlspecialchars(
                            $vehicle[
                                "vehicle_type"
                            ]
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
                        value="<?php
                        echo htmlspecialchars(
                            $vehicle[
                                "plate_number"
                            ]
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
                                $vehicle[
                                    "availability_status"
                                ] === "Available"
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
                                $vehicle[
                                    "availability_status"
                                ] === "Unavailable"
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
                    Save Changes
                </button>


                <a
                    href="view_vehicle.php?id=<?php echo $vehicle_id; ?>"
                    class="btn"
                >
                    Cancel
                </a>


            </form>

        </section>


    </main>


</body>

</html>