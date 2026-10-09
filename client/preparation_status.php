<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Client");

$user_id = $_SESSION["user_id"] ?? 0;

if (!$user_id) {
    die("User is not logged in.");
}


/* =========================
   GET CLIENT ID
========================= */

$sql = "SELECT client_id
        FROM users
        WHERE user_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

$client_id = $user["client_id"] ?? 0;

if (!$client_id) {
    die("This Client account is not linked to a client record.");
}


/* =========================
   GET PREPARATION TASKS
========================= */

$sql = "SELECT
            pt.task_id,
            pt.task_name,
            pt.description,
            pt.status,
            fs.funeral_service_id,
            fs.service_start,
            fs.service_end,
            fs.location,
            d.full_name AS deceased_name,
            p.package_name
        FROM preparation_tasks pt
        INNER JOIN funeral_services fs
            ON pt.funeral_service_id = fs.funeral_service_id
        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id
        INNER JOIN packages p
            ON fs.package_id = p.package_id
        WHERE fs.client_id = ?
        ORDER BY fs.service_start DESC, pt.task_id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$result = $stmt->get_result();

$tasks_by_service = [];

while ($row = $result->fetch_assoc()) {

    $service_id = $row["funeral_service_id"];

    if (!isset($tasks_by_service[$service_id])) {

        $tasks_by_service[$service_id] = [
            "deceased_name" => $row["deceased_name"],
            "package_name" => $row["package_name"],
            "service_start" => $row["service_start"],
            "service_end" => $row["service_end"],
            "location" => $row["location"],
            "tasks" => []
        ];
    }

    $tasks_by_service[$service_id]["tasks"][] = [
        "task_id" => $row["task_id"],
        "task_name" => $row["task_name"],
        "description" => $row["description"],
        "status" => $row["status"]
    ];
}

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

    <title>EternaCare - Preparation Status</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body class="client-page">

<?php include "includes/client_header.php"; ?>


<!-- =========================================================
     PREPARATION STATUS
     ========================================================= -->

<main class="client-dashboard">


    <!-- PAGE HEADER -->

    <section class="client-welcome">

        <div>

            <span class="client-section-label">
                MY FUNERAL SERVICES
            </span>

            <h1>
                Preparation Status
            </h1>

            <p>
                Monitor the preparation tasks and their current
                progress for your funeral services.
            </p>

        </div>

    </section>


    <!-- =====================================================
         NO TASKS
         ===================================================== -->

    <?php if (count($tasks_by_service) === 0): ?>

        <section class="client-panel">

            <div class="client-empty-box">

                <h3>
                    No Preparation Tasks
                </h3>

                <p>
                    There are currently no preparation tasks
                    available for your funeral services.
                </p>

                <div class="client-actions">

                    <a
                        href="my_services.php"
                        class="client-action-btn"
                    >
                        View My Funeral Services
                    </a>

                </div>

            </div>

        </section>


    <?php else: ?>


        <!-- =================================================
             SERVICES
             ================================================= -->

        <?php foreach ($tasks_by_service as $service_id => $service): ?>


            <section class="client-panel service-record-panel">


                <!-- SERVICE HEADER -->

                <div class="client-panel-header">

                    <div>

                        <span class="client-section-label">
                            FUNERAL SERVICE
                        </span>

                        <h2>
                            Service #<?php
                            echo htmlspecialchars($service_id);
                            ?>
                        </h2>

                    </div>

                </div>


                <!-- SERVICE INFORMATION -->

                <div class="client-record-grid">


                    <div class="client-record-card">

                        <span>
                            Deceased
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $service["deceased_name"]
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="client-record-card">

                        <span>
                            Funeral Package
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $service["package_name"]
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="client-record-card">

                        <span>
                            Service Start
                        </span>

                        <strong>

                            <?php

                            if (!empty($service["service_start"])) {

                                echo date(
                                    "F d, Y h:i A",
                                    strtotime(
                                        $service["service_start"]
                                    )
                                );

                            } else {

                                echo "Not scheduled";

                            }

                            ?>

                        </strong>

                    </div>


                    <div class="client-record-card">

                        <span>
                            Service End
                        </span>

                        <strong>

                            <?php

                            if (!empty($service["service_end"])) {

                                echo date(
                                    "F d, Y h:i A",
                                    strtotime(
                                        $service["service_end"]
                                    )
                                );

                            } else {

                                echo "Not scheduled";

                            }

                            ?>

                        </strong>

                    </div>


                    <div class="client-record-card">

                        <span>
                            Location
                        </span>

                        <strong>

                            <?php

                            echo !empty($service["location"])

                                ? htmlspecialchars(
                                    $service["location"]
                                )

                                : "Not specified";

                            ?>

                        </strong>

                    </div>


                </div>


                <!-- =================================================
                     PREPARATION TASKS
                     ================================================= -->

                <div class="client-section-title">

                    <span class="client-section-label">
                        PREPARATION
                    </span>

                    <h3>
                        Preparation Tasks
                    </h3>

                </div>


                <div class="client-table-wrapper">

                    <table class="client-table">

                        <thead>

                            <tr>

                                <th>
                                    Task
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($service["tasks"] as $task): ?>

                                <tr>

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $task["task_name"]
                                            );
                                            ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $task["description"]
                                            )
                                        ) {

                                            echo nl2br(
                                                htmlspecialchars(
                                                    $task["description"]
                                                )
                                            );

                                        } else {

                                            echo "No description.";

                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        $task_status =
                                            $task["status"];

                                        ?>


                                        <?php if (
                                            $task_status === "Completed"
                                        ): ?>

                                            <span class="client-status status-approved">
                                                Completed
                                            </span>


                                        <?php elseif (
                                            $task_status === "Pending"
                                        ): ?>

                                            <span class="client-status status-pending">
                                                Pending
                                            </span>


                                        <?php else: ?>

                                            <span class="client-status">
                                                <?php
                                                echo htmlspecialchars(
                                                    $task_status
                                                );
                                                ?>
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


            </section>


        <?php endforeach; ?>


    <?php endif; ?>


    <!-- =====================================================
         PAGE ACTIONS
         ===================================================== -->

    <section class="client-panel">

        <div class="client-actions">

            <a
                href="my_services.php"
                class="client-action-btn"
            >
                Back to My Funeral Services
            </a>

            <a
                href="dashboard.php"
                class="client-action-btn secondary"
            >
                Back to Dashboard
            </a>

        </div>

    </section>


</main>


<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="client-footer">

    <div>

        <strong>
            EternaCare
        </strong>

        <span>
            Funeral Service Management
        </span>

    </div>

    <p>
        Providing organized and compassionate funeral service management.
    </p>

</footer>


</body>

</html>