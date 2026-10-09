<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $service_name = trim($_POST["service_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = $_POST["price"] ?? "";
    $status = $_POST["status"] ?? "";


    if (empty($service_name) || $price === "") {

        $error = "Please fill in all required fields.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Please enter a valid price.";

    } elseif (
        $status !== "Available" &&
        $status !== "Unavailable"
    ) {

        $error = "Invalid service status.";

    } else {

        $price = (float) $price;


        /* Check duplicate service name */

        $check_sql = "SELECT optional_service_id
                      FROM optional_services
                      WHERE service_name = ?
                      LIMIT 1";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "s",
            $service_name
        );

        $check_stmt->execute();

        $check_result = $check_stmt->get_result();


        if ($check_result->num_rows > 0) {

            $error = "An optional service with this name already exists.";

            $check_stmt->close();

        } else {

            $check_stmt->close();


            /* Insert optional service */

            $sql = "INSERT INTO optional_services
                    (
                        service_name,
                        description,
                        price,
                        status
                    )
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ssds",
                $service_name,
                $description,
                $price,
                $status
            );


            if ($stmt->execute()) {

                $new_id = $stmt->insert_id;

                $stmt->close();

                header(
                    "Location: view_optional_service.php?id="
                    . $new_id
                );

                exit();

            } else {

                $error = "Error adding optional service: "
                    . $stmt->error;

                $stmt->close();
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

    <title>EternaCare - Add Optional Service</title>

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

            <h1>Add Optional Service</h1>

            <p>
                Add an additional service that can be included
                in a funeral package.
            </p>

        </section>


        <section class="content-section">


            <?php if (!empty($error)): ?>

                <p style="color: red;">

                    <?php echo htmlspecialchars($error); ?>

                </p>

            <?php endif; ?>


            <form method="POST" action="">


                <!-- Service Name -->

                <div class="form-group">

                    <label for="service_name">
                        Service Name *
                    </label>

                    <input
                        type="text"
                        id="service_name"
                        name="service_name"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["service_name"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- Description -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                    ><?php
                        echo htmlspecialchars(
                            $_POST["description"] ?? ""
                        );
                    ?></textarea>

                </div>


                <br>


                <!-- Price -->

                <div class="form-group">

                    <label for="price">
                        Price *
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["price"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- Status -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option value="Available">
                            Available
                        </option>

                        <option value="Unavailable">
                            Unavailable
                        </option>

                    </select>

                </div>


                <br>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Optional Service
                </button>


                <a
                    href="optional_services.php"
                    class="btn"
                >
                    Cancel
                </a>


            </form>


        </section>


    </main>


</body>

</html>