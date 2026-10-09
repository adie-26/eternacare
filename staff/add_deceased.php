<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";


/* Get clients for dropdown */

$clients_sql = "SELECT client_id, full_name
                FROM clients
                ORDER BY full_name ASC";

$clients_result = $conn->query($clients_sql);


/* Process form */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $client_id = $_POST["client_id"] ?? "";
    $full_name = trim($_POST["full_name"] ?? "");
    $date_of_birth = $_POST["date_of_birth"] ?? "";
    $date_of_death = $_POST["date_of_death"] ?? "";
    $age = $_POST["age"] ?? "";
    $address = trim($_POST["address"] ?? "");
    $cause_of_death = trim($_POST["cause_of_death"] ?? "");


    if (
        empty($client_id) ||
        empty($full_name) ||
        empty($date_of_death)
    ) {

        $error = "Please fill in all required fields.";

    } else {

        $sql = "INSERT INTO deceased
                (
                    client_id,
                    full_name,
                    date_of_birth,
                    date_of_death,
                    age,
                    address,
                    cause_of_death
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isssiss",
            $client_id,
            $full_name,
            $date_of_birth,
            $date_of_death,
            $age,
            $address,
            $cause_of_death
        );


        if ($stmt->execute()) {

            $stmt->close();

            header("Location: deceased.php");
            exit();

        } else {

            $error = "Error adding deceased record: " . $stmt->error;

        }

        $stmt->close();
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

    <title>EternaCare - Add Deceased Record</title>

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

            <h1>Add Deceased Record</h1>

            <p>
                Add information about the deceased individual.
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
                                    isset($_POST["client_id"]) &&
                                    $_POST["client_id"] == $client["client_id"]
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


                <!-- Full Name -->

                <div class="form-group">

                    <label for="full_name">
                        Deceased Full Name *
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php echo htmlspecialchars($_POST["full_name"] ?? ""); ?>"
                        required
                    >

                </div>


                <br>


                <!-- Date of Birth -->

                <div class="form-group">

                    <label for="date_of_birth">
                        Date of Birth
                    </label>

                    <input
                        type="date"
                        id="date_of_birth"
                        name="date_of_birth"
                        value="<?php echo htmlspecialchars($_POST["date_of_birth"] ?? ""); ?>"
                    >

                </div>


                <br>


                <!-- Date of Death -->

                <div class="form-group">

                    <label for="date_of_death">
                        Date of Death *
                    </label>

                    <input
                        type="date"
                        id="date_of_death"
                        name="date_of_death"
                        value="<?php echo htmlspecialchars($_POST["date_of_death"] ?? ""); ?>"
                        required
                    >

                </div>


                <br>


                <!-- Age -->

                <div class="form-group">

                    <label for="age">
                        Age
                    </label>

                    <input
                        type="number"
                        id="age"
                        name="age"
                        min="0"
                        value="<?php echo htmlspecialchars($_POST["age"] ?? ""); ?>"
                    >

                </div>


                <br>


                <!-- Address -->

                <div class="form-group">

                    <label for="address">
                        Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                    ><?php echo htmlspecialchars($_POST["address"] ?? ""); ?></textarea>

                </div>


                <br>


                <!-- Cause of Death -->

                <div class="form-group">

                    <label for="cause_of_death">
                        Cause of Death
                    </label>

                    <textarea
                        id="cause_of_death"
                        name="cause_of_death"
                        rows="3"
                    ><?php echo htmlspecialchars($_POST["cause_of_death"] ?? ""); ?></textarea>

                </div>


                <br>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Deceased Record
                </button>


                <a
                    href="deceased.php"
                    class="btn"
                >
                    Cancel
                </a>


            </form>


        </section>


    </main>


</body>

</html>