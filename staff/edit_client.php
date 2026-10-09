<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: clients.php");
    exit();
}

$client_id = (int) $_GET["id"];

$error = "";
$success = "";


/* Get client information */

$sql = "SELECT
            client_id,
            full_name,
            contact_number,
            email,
            address,
            relationship_to_deceased
        FROM clients
        WHERE client_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $client_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: clients.php");
    exit();
}

$client = $result->fetch_assoc();

$stmt->close();


/* Update client information */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $contact_number = trim($_POST["contact_number"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);
    $relationship = trim($_POST["relationship"]);

    if (
        empty($full_name) ||
        empty($contact_number) ||
        empty($email) ||
        empty($address) ||
        empty($relationship)
    ) {

        $error = "Please complete all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* Check if another client already uses the email */

        $check = $conn->prepare(
            "SELECT client_id
             FROM clients
             WHERE email = ?
             AND client_id != ?
             LIMIT 1"
        );

        $check->bind_param(
            "si",
            $email,
            $client_id
        );

        $check->execute();

        $email_result = $check->get_result();

        if ($email_result->num_rows > 0) {

            $error = "Another client is already using this email.";

        } else {

            $update_sql = "UPDATE clients
                           SET full_name = ?,
                               contact_number = ?,
                               email = ?,
                               address = ?,
                               relationship_to_deceased = ?
                           WHERE client_id = ?";

            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "sssssi",
                $full_name,
                $contact_number,
                $email,
                $address,
                $relationship,
                $client_id
            );

            if ($update_stmt->execute()) {

                $success = "Client information updated successfully.";

                $client["full_name"] = $full_name;
                $client["contact_number"] = $contact_number;
                $client["email"] = $email;
                $client["address"] = $address;
                $client["relationship_to_deceased"] = $relationship;

            } else {

                $error = "Failed to update client information.";
            }

            $update_stmt->close();
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

    <title>EternaCare - Edit Client</title>

    <link rel="stylesheet" href="../assets/css/style.css">

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
                <a href="optional_services.php">Optional Services</a>
            </li>

            <li>
                <a href="funeral_schedules.php">Funeral Schedules</a>
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

            <h1>Edit Client</h1>

            <p>
                Update client information.
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
                        value="<?php echo htmlspecialchars($client["full_name"]); ?>"
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
                        value="<?php echo htmlspecialchars($client["contact_number"] ?? ""); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($client["email"] ?? ""); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="address">
                        Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="4"
                        required
                    ><?php echo htmlspecialchars($client["address"] ?? ""); ?></textarea>

                </div>


                <div class="form-group">

                    <label for="relationship">
                        Relationship to Deceased
                    </label>

                    <input
                        type="text"
                        id="relationship"
                        name="relationship"
                        value="<?php echo htmlspecialchars($client["relationship_to_deceased"] ?? ""); ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>


                <a
                    href="clients.php"
                    class="btn"
                >
                    Cancel
                </a>


            </form>


        </section>


    </main>


</body>

</html>