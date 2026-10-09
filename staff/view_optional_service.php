<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


// Validate ID

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: optional_services.php");
    exit();
}

$service_id = (int) $_GET["id"];


// Get optional service

$sql = "SELECT
            optional_service_id,
            service_name,
            description,
            price,
            status,
            created_at
        FROM optional_services
        WHERE optional_service_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $service_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: optional_services.php");
    exit();
}

$service = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>EternaCare - View Optional Service</title>

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
</li>s

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

            <h1>View Optional Service</h1>

            <p>
                View the details of this optional service.
            </p>

        </section>


        <section class="content-section">


            <div class="card">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $service["service_name"]
                    );
                    ?>
                </h2>


                <p>
                    <strong>Service ID:</strong>
                    <?php
                    echo htmlspecialchars(
                        $service["optional_service_id"]
                    );
                    ?>
                </p>


                <p>
                    <strong>Service Name:</strong>
                    <?php
                    echo htmlspecialchars(
                        $service["service_name"]
                    );
                    ?>
                </p>


                <p>
                    <strong>Description:</strong>
                    <?php

                    if (
                        !empty($service["description"])
                    ) {

                        echo nl2br(
                            htmlspecialchars(
                                $service["description"]
                            )
                        );

                    } else {

                        echo "No description provided.";

                    }

                    ?>
                </p>


                <p>
                    <strong>Price:</strong>
                    ₱<?php
                    echo number_format(
                        $service["price"],
                        2
                    );
                    ?>
                </p>


                <p>
                    <strong>Status:</strong>
                    <?php
                    echo htmlspecialchars(
                        $service["status"]
                    );
                    ?>
                </p>


                <p>
                    <strong>Created At:</strong>
                    <?php
                    echo htmlspecialchars(
                        $service["created_at"]
                    );
                    ?>
                </p>


                <br>


                <a
                    href="edit_optional_service.php?id=<?php echo $service_id; ?>"
                    class="btn btn-primary"
                >
                    Edit Optional Service
                </a>


                <a
                    href="optional_services.php"
                    class="btn"
                >
                    Back to Optional Services
                </a>


            </div>


        </section>


    </main>


</body>

</html>