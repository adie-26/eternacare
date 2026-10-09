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
   GET SERVICE HISTORY
========================= */

$sql = "SELECT
            fs.funeral_service_id,
            fs.service_start,
            fs.service_end,
            fs.location,
            fs.total_cost,
            fs.status,
            fs.notes,
            d.full_name AS deceased_name,
            p.package_name
        FROM funeral_services fs
        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id
        INNER JOIN packages p
            ON fs.package_id = p.package_id
        WHERE fs.client_id = ?
        AND fs.status IN ('Completed', 'Cancelled')
        ORDER BY fs.service_start DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$result = $stmt->get_result();

$history = [];

while ($row = $result->fetch_assoc()) {
    $history[] = $row;
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

    <title>
        EternaCare - Service History
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="client-page">


    <?php include "includes/client_header.php"; ?>


    <main class="client-dashboard">


        <!-- PAGE HEADER -->

        <section class="client-welcome">

            <div>

                <span class="client-eyebrow">
                    MY SERVICES
                </span>

                <h1>
                    Service History
                </h1>

                <p>
                    View your completed and cancelled
                    funeral services.
                </p>

            </div>

        </section>


        <?php if (count($history) === 0): ?>


            <!-- EMPTY HISTORY -->

            <section class="client-panel">


                <div class="client-section-title">

                    <span>
                        SERVICE HISTORY
                    </span>

                    <h2>
                        No Service History
                    </h2>

                    <p>
                        You currently have no completed or
                        cancelled funeral services.
                    </p>

                </div>


                <div class="client-empty-box">

                    <p>
                        Completed and cancelled funeral
                        services will appear here.
                    </p>

                    <a
                        href="my_services.php"
                        class="client-action-btn"
                    >
                        My Funeral Services
                    </a>

                </div>


            </section>


        <?php else: ?>


            <!-- HISTORY SUMMARY -->

            <section class="client-stats">


                <div class="client-stat-card">

                    <span>
                        TOTAL HISTORY
                    </span>

                    <strong>
                        <?php echo count($history); ?>
                    </strong>

                    <small>
                        Recorded services
                    </small>

                </div>


                <div class="client-stat-card">

                    <span>
                        COMPLETED
                    </span>

                    <strong>

                        <?php

                        $completed_count = 0;

                        foreach ($history as $service) {

                            if (
                                $service["status"] ===
                                "Completed"
                            ) {
                                $completed_count++;
                            }

                        }

                        echo $completed_count;

                        ?>

                    </strong>

                    <small>
                        Completed services
                    </small>

                </div>


                <div class="client-stat-card">

                    <span>
                        CANCELLED
                    </span>

                    <strong>

                        <?php

                        $cancelled_count = 0;

                        foreach ($history as $service) {

                            if (
                                $service["status"] ===
                                "Cancelled"
                            ) {
                                $cancelled_count++;
                            }

                        }

                        echo $cancelled_count;

                        ?>

                    </strong>

                    <small>
                        Cancelled services
                    </small>

                </div>


            </section>


            <!-- SERVICE RECORDS -->

            <?php foreach ($history as $service): ?>


                <section class="client-panel service-record-panel">


                    <!-- RECORD HEADER -->

                    <div class="client-record-header">


                        <div>

                            <span>
                                FUNERAL SERVICE
                            </span>

                            <h2>
                                Service #<?php
                                echo htmlspecialchars(
                                    $service[
                                        "funeral_service_id"
                                    ]
                                );
                                ?>
                            </h2>

                        </div>


                        <span
                            class="client-status
                            <?php
                            echo (
                                $service["status"] ===
                                "Completed"
                            )
                                ? "status-approved"
                                : "status-rejected";
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $service["status"]
                            );
                            ?>

                        </span>


                    </div>


                    <!-- SERVICE INFORMATION -->

                    <div class="client-record-grid">


                        <div class="client-record-card">

                            <span>
                                DECEASED
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $service[
                                        "deceased_name"
                                    ]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="client-record-card">

                            <span>
                                FUNERAL PACKAGE
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $service[
                                        "package_name"
                                    ]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="client-record-card">

                            <span>
                                SERVICE START
                            </span>

                            <strong>

                                <?php
                                echo date(
                                    "F d, Y h:i A",
                                    strtotime(
                                        $service[
                                            "service_start"
                                        ]
                                    )
                                );
                                ?>

                            </strong>

                        </div>


                        <div class="client-record-card">

                            <span>
                                SERVICE END
                            </span>

                            <strong>

                                <?php
                                echo date(
                                    "F d, Y h:i A",
                                    strtotime(
                                        $service[
                                            "service_end"
                                        ]
                                    )
                                );
                                ?>

                            </strong>

                        </div>


                        <div class="client-record-card">

                            <span>
                                LOCATION
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $service[
                                        "location"
                                    ]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="client-record-card">

                            <span>
                                TOTAL COST
                            </span>

                            <strong>
                                ₱<?php
                                echo number_format(
                                    $service[
                                        "total_cost"
                                    ],
                                    2
                                );
                                ?>
                            </strong>

                        </div>


                    </div>


                    <!-- NOTES -->

                    <?php if (
                        !empty($service["notes"])
                    ): ?>

                        <div class="client-service-section">

                            <span>
                                ADDITIONAL NOTES
                            </span>

                            <p class="client-service-notes">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $service["notes"]
                                    )
                                );
                                ?>

                            </p>

                        </div>

                    <?php endif; ?>


                </section>


            <?php endforeach; ?>


        <?php endif; ?>


        <!-- PAGE ACTIONS -->

        <div class="client-actions">

            <a
                href="my_services.php"
                class="client-action-btn"
            >
                My Funeral Services
            </a>

            <a
                href="dashboard.php"
                class="client-action-btn secondary"
            >
                Back to Dashboard
            </a>

        </div>


    </main>


    <!-- FOOTER -->

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
            © <?php echo date("Y"); ?> EternaCare.
            All rights reserved.
        </p>

    </footer>


</body>

</html>