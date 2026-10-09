<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$funeral_service_id = $_GET["funeral_service_id"] ?? "";

if (!is_numeric($funeral_service_id)) {
    die("Invalid funeral service ID.");
}

$funeral_service_id = (int) $funeral_service_id;

$error = "";
$success = "";


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

                WHERE fs.funeral_service_id = ?

                LIMIT 1";

$service_stmt = $conn->prepare($service_sql);

$service_stmt->bind_param(
    "i",
    $funeral_service_id
);

$service_stmt->execute();

$service_result = $service_stmt->get_result();

if ($service_result->num_rows !== 1) {
    $service_stmt->close();
    die("Funeral service not found.");
}

$service = $service_result->fetch_assoc();

$service_stmt->close();


/*
 * Get available staff.
 */

$staff_sql = "SELECT
                  staff_id,
                  full_name,
                  position

              FROM staff

              WHERE availability_status = 'Available'

              ORDER BY full_name ASC";

$staff_result = $conn->query($staff_sql);


/*
 * Process assignment.
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $staff_id = $_POST["staff_id"] ?? "";

    if (empty($staff_id) || !is_numeric($staff_id)) {

        $error = "Please select a staff member.";

    } else {

        $staff_id = (int) $staff_id;


        /*
         * Check whether the staff member exists
         * and is available.
         */

        $check_staff_sql = "SELECT staff_id
                             FROM staff
                             WHERE staff_id = ?
                             AND availability_status = 'Available'
                             LIMIT 1";

        $check_staff_stmt = $conn->prepare(
            $check_staff_sql
        );

        $check_staff_stmt->bind_param(
            "i",
            $staff_id
        );

        $check_staff_stmt->execute();

        $check_staff_result =
            $check_staff_stmt->get_result();

        $check_staff_stmt->close();


        if ($check_staff_result->num_rows !== 1) {

            $error = "The selected staff member is not available.";

        } else {


            /*
             * Check if the staff member is already
             * assigned to this funeral service.
             */

            $duplicate_sql = "SELECT assignment_id
                              FROM staff_assignments
                              WHERE funeral_service_id = ?
                              AND staff_id = ?
                              LIMIT 1";

            $duplicate_stmt = $conn->prepare(
                $duplicate_sql
            );

            $duplicate_stmt->bind_param(
                "ii",
                $funeral_service_id,
                $staff_id
            );

            $duplicate_stmt->execute();

            $duplicate_result =
                $duplicate_stmt->get_result();

            $duplicate_stmt->close();


            if ($duplicate_result->num_rows > 0) {

                $error =
                    "This staff member is already assigned to this funeral service.";

            } else {


                /*
                 * Insert assignment.
                 */

                $insert_sql = "INSERT INTO staff_assignments
                               (
                                   funeral_service_id,
                                   staff_id
                               )
                               VALUES (?, ?)";

                $insert_stmt = $conn->prepare(
                    $insert_sql
                );

                $insert_stmt->bind_param(
                    "ii",
                    $funeral_service_id,
                    $staff_id
                );


                if ($insert_stmt->execute()) {

                    $insert_stmt->close();

                    header(
                        "Location: staff_assignments.php"
                    );

                    exit();

                } else {

                    $error =
                        "Error assigning staff: "
                        . $insert_stmt->error;

                    $insert_stmt->close();
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
        EternaCare - Assign Staff
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
                Assign Staff
            </h1>

            <p>
                Assign an available staff member to this funeral service.
            </p>

        </section>


        <section class="content-section">


            <?php if (!empty($error)): ?>

                <p style="color: red;">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </p>

            <?php endif; ?>


            <h2>
                Funeral Service Information
            </h2>


            <table>

                <tr>

                    <th>
                        Funeral Service ID
                    </th>

                    <td>
                        #<?php
                        echo htmlspecialchars(
                            $service["funeral_service_id"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Client
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $service["client_name"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Deceased
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $service["deceased_name"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Service Start
                    </th>

                    <td>
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
                    </td>

                </tr>


                <tr>

                    <th>
                        Service End
                    </th>

                    <td>
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
                    </td>

                </tr>


                <tr>

                    <th>
                        Location
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $service["location"]
                        );
                        ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Status
                    </th>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $service["status"]
                        );
                        ?>
                    </td>

                </tr>

            </table>


            <br>


            <h2>
                Select Staff Member
            </h2>


            <?php if ($staff_result->num_rows > 0): ?>

                <form
                    method="POST"
                    action=""
                >


                    <div class="form-group">

                        <label for="staff_id">
                            Staff Member *
                        </label>


                        <select
                            id="staff_id"
                            name="staff_id"
                            required
                        >

                            <option value="">
                                Select Staff Member
                            </option>


                            <?php while (
                                $staff =
                                $staff_result->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?php
                                    echo $staff["staff_id"];
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $staff["full_name"]
                                    );
                                    ?>

                                    -
                                    <?php
                                    echo htmlspecialchars(
                                        $staff["position"]
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
                        Assign Staff
                    </button>


                    <a
                        href="staff_assignments.php"
                        class="btn"
                    >
                        Cancel
                    </a>


                </form>


            <?php else: ?>

                <p>
                    No available staff members found.
                </p>


                <a
                    href="staff_assignments.php"
                    class="btn"
                >
                    Back to Staff Assignments
                </a>

            <?php endif; ?>


        </section>


    </main>


</body>

</html>