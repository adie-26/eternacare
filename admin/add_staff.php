<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Admin");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $contact_number = trim($_POST["contact_number"]);
    $position = trim($_POST["position"]);

    if (
        empty($full_name) ||
        empty($username) ||
        empty($password) ||
        empty($contact_number) ||
        empty($position)
    ) {

        $error = "Please complete all fields.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } else {

        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE username = ? LIMIT 1"
        );

        $check->bind_param("s", $username);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "Username is already taken.";

        } else {

            $conn->begin_transaction();

            try {

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $user_sql = "INSERT INTO users
                    (
                        full_name,
                        username,
                        password,
                        role,
                        status
                    )
                    VALUES (?, ?, ?, 'Staff', 'Active')";

                $user_stmt = $conn->prepare($user_sql);

                $user_stmt->bind_param(
                    "sss",
                    $full_name,
                    $username,
                    $hashed_password
                );

                $user_stmt->execute();

                $user_id = $conn->insert_id;

                $staff_sql = "INSERT INTO staff
                    (
                        user_id,
                        full_name,
                        contact_number,
                        position,
                        availability_status
                    )
                    VALUES (?, ?, ?, ?, 'Available')";

                $staff_stmt = $conn->prepare($staff_sql);

                $staff_stmt->bind_param(
                    "isss",
                    $user_id,
                    $full_name,
                    $contact_number,
                    $position
                );

                $staff_stmt->execute();

                $conn->commit();

                $success = "Staff account created successfully.";

                $user_stmt->close();
                $staff_stmt->close();

            } catch (Exception $e) {

                $conn->rollback();

                $error = "Failed to create staff account.";
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

    <title>EternaCare - Add Staff</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

    <aside class="sidebar">

        <div class="logo">

            <h2>EternaCare</h2>

            <p>Funeral Service Management</p>

        </div>

        <ul>

            <li>
                <a href="dashboard.php">Dashboard</a>
            </li>

            <li>
                <a href="staff.php">Staff Accounts</a>
            </li>

            <li>
                <a href="../staff/clients.php">Clients</a>
            </li>

            <li>
                <a href="../staff/deceased.php">Deceased Records</a>
            </li>

            <li>
                <a href="../staff/packages.php">Funeral Packages</a>
            </li>

            <li>
                <a href="../staff/optional_services.php">Optional Services</a>
            </li>

            <li>
                <a href="../staff/funeral_schedules.php">Funeral Schedules</a>
            </li>

            <li>
                <a href="../staff/staff_assignments.php">Staff Assignments</a>
            </li>

            <li>
                <a href="../staff/vehicle_assignments.php">Vehicle Assignments</a>
            </li>
            

            <li>
                <a href="../staff/preparation_tasks.php">Preparation Tasks</a>
            </li>

            <li>
                <a href="../staff/payments.php">Payments</a>
            </li>

            <li>
                <a href="../staff/service_history.php">Service History</a>
            </li>

            <li>
                <a href="../staff/reports.php">Reports</a>
            </li>

            <li>
                <a href="../logout.php">Logout</a>
            </li>

        </ul>

    </aside>


    <main class="main-content">

        <section class="top-header">

            <h1>Add Staff Account</h1>

            <p>
                Create a new staff account for EternaCare.
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
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
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
                        placeholder="Example: Funeral Service Staff"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Create Staff Account
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