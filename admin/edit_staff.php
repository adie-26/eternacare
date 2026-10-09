<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Admin");

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: staff.php");
    exit();
}

$user_id = (int) $_GET["id"];

$error = "";
$success = "";


/* Get staff information */

$sql = "SELECT
            u.user_id,
            u.full_name,
            u.username,
            u.status,
            s.staff_id,
            s.contact_number,
            s.position,
            s.availability_status
        FROM users u
        LEFT JOIN staff s
            ON u.user_id = s.user_id
        WHERE u.user_id = ?
        AND u.role = 'Staff'
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: staff.php");
    exit();
}

$staff = $result->fetch_assoc();

$stmt->close();


/* Update staff information */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $username = trim($_POST["username"]);
    $contact_number = trim($_POST["contact_number"]);
    $position = trim($_POST["position"]);
    $status = $_POST["status"];
    $availability_status = $_POST["availability_status"];

    if (
        empty($full_name) ||
        empty($username) ||
        empty($contact_number) ||
        empty($position)
    ) {

        $error = "Please complete all fields.";

    } else {

        /* Check username */

        $check = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE username = ?
             AND user_id != ?
             LIMIT 1"
        );

        $check->bind_param(
            "si",
            $username,
            $user_id
        );

        $check->execute();

        $username_result = $check->get_result();

        if ($username_result->num_rows > 0) {

            $error = "Username is already taken.";

        } else {

            $conn->begin_transaction();

            try {

                /* Update users table */

                $user_sql = "UPDATE users
                             SET full_name = ?,
                                 username = ?,
                                 status = ?
                             WHERE user_id = ?
                             AND role = 'Staff'";

                $user_stmt = $conn->prepare($user_sql);

                $user_stmt->bind_param(
                    "sssi",
                    $full_name,
                    $username,
                    $status,
                    $user_id
                );

                $user_stmt->execute();


                /* Update staff table */

                $staff_sql = "UPDATE staff
                              SET full_name = ?,
                                  contact_number = ?,
                                  position = ?,
                                  availability_status = ?
                              WHERE user_id = ?";

                $staff_stmt = $conn->prepare($staff_sql);

                $staff_stmt->bind_param(
                    "ssssi",
                    $full_name,
                    $contact_number,
                    $position,
                    $availability_status,
                    $user_id
                );

                $staff_stmt->execute();

                $conn->commit();

                $success = "Staff account updated successfully.";

                $staff["full_name"] = $full_name;
                $staff["username"] = $username;
                $staff["contact_number"] = $contact_number;
                $staff["position"] = $position;
                $staff["status"] = $status;
                $staff["availability_status"] = $availability_status;

                $user_stmt->close();
                $staff_stmt->close();

            } catch (Exception $e) {

                $conn->rollback();

                $error = "Failed to update staff account.";
            }
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EternaCare - Edit Staff</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

  <aside class="sidebar">

    <div class="logo">

        <h2>EternaCare</h2>

        <p>
            Funeral Service Management
        </p>

    </div>


    <ul>

        <li>
            <a href="dashboard.php">
                Dashboard
            </a>
        </li>

        <li>
            <a href="staff.php">
                Staff Accounts
            </a>
        </li>

        <li>
            <a href="../staff/clients.php">
                Clients
            </a>
        </li>

        <li>
            <a href="../staff/deceased.php">
                Deceased Records
            </a>
        </li>

        <li>
            <a href="../staff/packages.php">
                Funeral Packages
            </a>
        </li>

        <li>
            <a href="../staff/optional_services.php">
                Optional Services
            </a>
        </li>

        <li>
            <a href="../staff/funeral_schedules.php">
                Funeral Schedules
            </a>
        </li>

        <li>
            <a href="../staff/staff_assignments.php">
                Staff Assignments
            </a>
        </li>

        <li>
            <a href="../staff/vehicle_assignments.php">
                Vehicle Assignments
            </a>
        </li>


        <li>
            <a href="../staff/preparation_tasks.php">
                Preparation Tasks
            </a>
        </li>

        <li>
            <a href="../staff/payments.php">
                Payments
            </a>
        </li>

        <li>
            <a href="../staff/service_history.php">
                Service History
            </a>
        </li>

        <li>
            <a href="../staff/reports.php">
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


    <main class="main-content">

        <section class="top-header">

            <h1>Edit Staff Account</h1>

            <p>
                Update staff account information.
            </p>

        </section>


        <section class="content-section">

            <?php if (!empty($error)): ?>

                <p style="color: red;">
                    <?php echo htmlspecialchars($error); ?>
                </p>

                <br>

            <?php endif; ?>


            <?php if (!empty($success)): ?>

                <p style="color: green;">
                    <?php echo htmlspecialchars($success); ?>
                </p>

                <br>

            <?php endif; ?>


            <form method="POST" action="">


                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php echo htmlspecialchars($staff["full_name"]); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($staff["username"]); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="contact_number">
                        Contact Number
                    </label>

                    <input
                        type="text"
                        id="contact_number"
                        name="contact_number"
                        value="<?php echo htmlspecialchars($staff["contact_number"] ?? ""); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="position">
                        Position
                    </label>

                    <input
                        type="text"
                        id="position"
                        name="position"
                        value="<?php echo htmlspecialchars($staff["position"] ?? ""); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="status">
                        Account Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="Active"
                            <?php echo $staff["status"] === "Active" ? "selected" : ""; ?>
                        >
                            Active
                        </option>

                        <option
                            value="Inactive"
                            <?php echo $staff["status"] === "Inactive" ? "selected" : ""; ?>
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="availability_status">
                        Staff Availability
                    </label>

                    <select
                        id="availability_status"
                        name="availability_status"
                        required
                    >

                        <option
                            value="Available"
                            <?php echo $staff["availability_status"] === "Available" ? "selected" : ""; ?>
                        >
                            Available
                        </option>

                        <option
                            value="Unavailable"
                            <?php echo $staff["availability_status"] === "Unavailable" ? "selected" : ""; ?>
                        >
                            Unavailable
                        </option>

                    </select>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>


                <a
                    href="staff.php"
                    class="btn"
                >
                    Cancel
                </a>

            </form>

        </section>

    </main>

</body>

</html>