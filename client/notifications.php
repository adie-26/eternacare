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
   GET FUNERAL SERVICES
========================= */

$sql = "SELECT
            fs.funeral_service_id,
            fs.service_start,
            fs.service_end,
            fs.location,
            fs.status,
            d.full_name AS deceased_name
        FROM funeral_services fs
        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id
        WHERE fs.client_id = ?
        ORDER BY fs.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$result = $stmt->get_result();

$services = [];

while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

$stmt->close();


/* =========================
   GET PREPARATION TASKS
========================= */

$sql = "SELECT
            pt.task_id,
            pt.task_name,
            pt.status,
            pt.funeral_service_id,
            fs.service_start,
            d.full_name AS deceased_name
        FROM preparation_tasks pt
        INNER JOIN funeral_services fs
            ON pt.funeral_service_id = fs.funeral_service_id
        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id
        WHERE fs.client_id = ?
        ORDER BY pt.task_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$result = $stmt->get_result();

$tasks = [];

while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

$stmt->close();


/* =========================
   GET PAYMENTS
========================= */

$sql = "SELECT
            pay.payment_id,
            pay.amount,
            pay.payment_method,
            pay.payment_date,
            pay.funeral_service_id,
            d.full_name AS deceased_name
        FROM payments pay
        INNER JOIN funeral_services fs
            ON pay.funeral_service_id = fs.funeral_service_id
        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id
        WHERE fs.client_id = ?
        ORDER BY pay.payment_date DESC, pay.payment_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$result = $stmt->get_result();

$payments = [];

while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
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
        EternaCare - Notifications
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="client-page">


    <!-- =========================
         CLIENT NAVIGATION
         ========================= -->

    <?php include "includes/client_header.php"; ?>


    <!-- =========================
         MAIN CONTENT
         ========================= -->

    <main class="client-dashboard">


        <!-- =========================
             PAGE INTRODUCTION
             ========================= -->

        <section class="client-welcome">

            <span>
                MY ACCOUNT
            </span>

            <h2>
                Notifications
            </h2>

            <p>
                View important updates related to your funeral
                services, preparation tasks, and payments.
            </p>

        </section>


        <!-- =========================
             NOTIFICATION SUMMARY
             ========================= -->

        <section class="client-stats">


            <div class="client-stat-card">

                <div class="client-stat-icon">
                    +
                </div>

                <div>

                    <span>
                        SERVICE UPDATES
                    </span>

                    <strong>
                        <?php
                        echo count($services);
                        ?>
                    </strong>

                </div>

            </div>


            <div class="client-stat-card">

                <div class="client-stat-icon">
                    ✓
                </div>

                <div>

                    <span>
                        PREPARATION UPDATES
                    </span>

                    <strong>
                        <?php
                        echo count($tasks);
                        ?>
                    </strong>

                </div>

            </div>


            <div class="client-stat-card">

                <div class="client-stat-icon">
                    ₱
                </div>

                <div>

                    <span>
                        PAYMENT UPDATES
                    </span>

                    <strong>
                        <?php
                        echo count($payments);
                        ?>
                    </strong>

                </div>

            </div>


        </section>


        <!-- =========================
             FUNERAL SERVICE UPDATES
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        SERVICE NOTIFICATIONS
                    </span>

                    <h2>
                        Funeral Service Updates
                    </h2>

                </div>

            </div>


            <?php if (count($services) === 0): ?>

                <div class="client-empty-box">

                    No funeral service updates available.

                </div>

            <?php else: ?>


                <div class="client-notification-list">


                    <?php foreach ($services as $service): ?>

                        <article class="client-notification-card">


                            <div class="client-notification-header">

                                <div>

                                    <span>
                                        FUNERAL SERVICE
                                    </span>

                                    <h3>

                                        Service #<?php
                                        echo htmlspecialchars(
                                            $service[
                                                "funeral_service_id"
                                            ]
                                        );
                                        ?>

                                    </h3>

                                </div>


                                <span class="client-status">

                                    <?php
                                    echo htmlspecialchars(
                                        $service["status"]
                                    );
                                    ?>

                                </span>

                            </div>


                            <div class="client-information">


                                <div class="information-row">

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


                                <div class="information-row">

                                    <span>
                                        SCHEDULE
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

                                        to

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


                                <div class="information-row">

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


                            </div>


                        </article>

                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


        <!-- =========================
             PREPARATION UPDATES
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        PREPARATION NOTIFICATIONS
                    </span>

                    <h2>
                        Preparation Updates
                    </h2>

                </div>

            </div>


            <?php if (count($tasks) === 0): ?>

                <div class="client-empty-box">

                    No preparation updates available.

                </div>

            <?php else: ?>


                <div class="client-notification-list">


                    <?php foreach ($tasks as $task): ?>

                        <article class="client-notification-card">


                            <div class="client-notification-header">

                                <div>

                                    <span>
                                        PREPARATION TASK
                                    </span>

                                    <h3>

                                        <?php
                                        echo htmlspecialchars(
                                            $task["task_name"]
                                        );
                                        ?>

                                    </h3>

                                </div>


                                <span class="client-status">

                                    <?php
                                    echo htmlspecialchars(
                                        $task["status"]
                                    );
                                    ?>

                                </span>

                            </div>


                            <div class="client-information">


                                <div class="information-row">

                                    <span>
                                        FUNERAL SERVICE
                                    </span>

                                    <strong>

                                        #<?php
                                        echo htmlspecialchars(
                                            $task[
                                                "funeral_service_id"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        DECEASED
                                    </span>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $task[
                                                "deceased_name"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        STATUS
                                    </span>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $task["status"]
                                        );
                                        ?>

                                    </strong>

                                </div>


                            </div>


                        </article>

                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


        <!-- =========================
             PAYMENT UPDATES
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        PAYMENT NOTIFICATIONS
                    </span>

                    <h2>
                        Payment Updates
                    </h2>

                </div>

            </div>


            <?php if (count($payments) === 0): ?>

                <div class="client-empty-box">

                    No payment updates available.

                </div>

            <?php else: ?>


                <div class="client-notification-list">


                    <?php foreach ($payments as $payment): ?>

                        <article class="client-notification-card">


                            <div class="client-notification-header">

                                <div>

                                    <span>
                                        PAYMENT RECORDED
                                    </span>

                                    <h3>

                                        ₱<?php
                                        echo number_format(
                                            $payment["amount"],
                                            2
                                        );
                                        ?>

                                    </h3>

                                </div>


                                <span class="client-status">

                                    RECORDED

                                </span>

                            </div>


                            <div class="client-information">


                                <div class="information-row">

                                    <span>
                                        FUNERAL SERVICE
                                    </span>

                                    <strong>

                                        #<?php
                                        echo htmlspecialchars(
                                            $payment[
                                                "funeral_service_id"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        DECEASED
                                    </span>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $payment[
                                                "deceased_name"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        PAYMENT METHOD
                                    </span>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $payment[
                                                "payment_method"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        PAYMENT DATE
                                    </span>

                                    <strong>

                                        <?php

                                        echo date(
                                            "F d, Y",
                                            strtotime(
                                                $payment[
                                                    "payment_date"
                                                ]
                                            )
                                        );

                                        ?>

                                    </strong>

                                </div>


                            </div>


                        </article>

                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


        <!-- =========================
             QUICK ACTIONS
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        QUICK ACCESS
                    </span>

                    <h2>
                        Manage Your Account
                    </h2>

                </div>

            </div>


            <div class="client-actions">


                <a
                    href="my_services.php"
                    class="client-action primary"
                >

                    <strong>
                        My Funeral Services
                    </strong>

                    <span>
                        View your funeral service requests.
                    </span>

                </a>


                <a
                    href="preparation_status.php"
                    class="client-action"
                >

                    <strong>
                        Preparation Status
                    </strong>

                    <span>
                        Check your preparation progress.
                    </span>

                </a>


                <a
                    href="payments.php"
                    class="client-action"
                >

                    <strong>
                        My Payments
                    </strong>

                    <span>
                        View your payment records.
                    </span>

                </a>


                <a
                    href="dashboard.php"
                    class="client-action"
                >

                    <strong>
                        Back to Dashboard
                    </strong>

                    <span>
                        Return to your EternaCare dashboard.
                    </span>

                </a>


            </div>


        </section>


        <!-- =========================
             FOOTER
             ========================= -->

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
                Providing care, dignity, and compassion for every family.
            </p>

        </footer>


    </main>


</body>

</html>