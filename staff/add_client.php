<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";
$success = "";

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

        $check = $conn->prepare(
            "SELECT client_id
             FROM clients
             WHERE email = ?
             LIMIT 1"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "A client with this email already exists.";

        } else {

            $sql = "INSERT INTO clients
                    (
                        full_name,
                        contact_number,
                        email,
                        address,
                        relationship_to_deceased
                    )
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sssss",
                $full_name,
                $contact_number,
                $email,
                $address,
                $relationship
            );

            if ($stmt->execute()) {

                $success = "Client added successfully.";

            } else {

                $error = "Failed to add client.";
            }

            $stmt->close();
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

    <title>EternaCare - Add Client</title>

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

            <h1>Add Client</h1>

            <p>
                Add a new client record to EternaCare.
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

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
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
                    ></textarea>

                </div>


                <div class="form-group">

                    <label for="relationship">
                        Relationship to Deceased
                    </label>

                    <input
                        type="text"
                        id="relationship"
                        name="relationship"
                        placeholder="Example: Son, Daughter, Spouse"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Client
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