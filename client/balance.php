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
            fs.total_cost,
            fs.status,
            d.full_name AS deceased_name,
            p.package_name
        FROM funeral_services fs
        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id
        INNER JOIN packages p
            ON fs.package_id = p.package_id
        WHERE fs.client_id = ?
        AND fs.status != 'Cancelled'
        ORDER BY fs.service_start DESC";

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
   CALCULATE BALANCE
========================= */

$grand_total_cost = 0;
$grand_total_paid = 0;

foreach ($services as &$service) {

    $service_id = $service["funeral_service_id"];

    $service_total = (float) $service["total_cost"];


    /* Get payments for this service */

    $sql = "SELECT
                COALESCE(SUM(amount), 0) AS total_paid
            FROM payments
            WHERE funeral_service_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();

    $payment_result = $stmt->get_result();
    $payment_row = $payment_result->fetch_assoc();

    $stmt->close();

    $total_paid = (float) ($payment_row["total_paid"] ?? 0);

    $balance = $service_total - $total_paid;

    if ($balance < 0) {
        $balance = 0;
    }

    $service["total_paid"] = $total_paid;
    $service["balance"] = $balance;

    $grand_total_cost += $service_total;
    $grand_total_paid += $total_paid;
}

unset($service);

$grand_total_balance = $grand_total_cost - $grand_total_paid;

if ($grand_total_balance < 0) {
    $grand_total_balance = 0;
}


/* =========================
   DETERMINE OVERALL STATUS
========================= */

if ($grand_total_cost <= 0) {

    $payment_status = "No Balance";

} elseif ($grand_total_balance <= 0) {

    $payment_status = "Fully Paid";

} elseif ($grand_total_paid > 0) {

    $payment_status = "Partially Paid";

} else {

    $payment_status = "Unpaid";

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

    <title>EternaCare - My Balance</title>

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
                My Balance
            </h2>

            <p>
                View your total service cost, payments,
                and remaining balance.
            </p>

        </section>


        <!-- =========================
             BALANCE SUMMARY
             ========================= -->

        <section class="client-stats">


            <!-- Total Service Cost -->

            <div class="client-stat-card">

                <div class="client-stat-icon">
                    ₱
                </div>

                <div>

                    <span>
                        TOTAL SERVICE COST
                    </span>

                    <strong>
                        ₱<?php
                        echo number_format(
                            $grand_total_cost,
                            2
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- Total Payments -->

            <div class="client-stat-card">

                <div class="client-stat-icon">
                    ₱
                </div>

                <div>

                    <span>
                        TOTAL PAYMENTS
                    </span>

                    <strong>
                        ₱<?php
                        echo number_format(
                            $grand_total_paid,
                            2
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- Remaining Balance -->

            <div class="client-stat-card">

                <div class="client-stat-icon">
                    ₱
                </div>

                <div>

                    <span>
                        REMAINING BALANCE
                    </span>

                    <strong>
                        ₱<?php
                        echo number_format(
                            $grand_total_balance,
                            2
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- Payment Status -->

            <div class="client-stat-card">

                <div class="client-stat-icon">
                    ✓
                </div>

                <div>

                    <span>
                        PAYMENT STATUS
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $payment_status
                        );
                        ?>
                    </strong>

                </div>

            </div>


        </section>


        <!-- =========================
             BALANCE BREAKDOWN
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        SERVICE PAYMENT DETAILS
                    </span>

                    <h2>
                        Balance Breakdown
                    </h2>

                </div>

                <a href="payments.php">
                    VIEW PAYMENTS
                </a>

            </div>


            <?php if (count($services) === 0): ?>


                <!-- No Services -->

                <div class="client-information">

                    <div class="information-row">

                        <span>
                            STATUS
                        </span>

                        <strong>
                            No Active Funeral Services
                        </strong>

                    </div>

                    <p class="empty-message">
                        You currently have no active funeral services
                        with an outstanding balance.
                    </p>

                </div>


            <?php else: ?>


                <!-- Service Table -->

                <div class="client-table-wrapper">

                    <table class="client-table">

                        <thead>

                            <tr>

                                <th>
                                    SERVICE
                                </th>

                                <th>
                                    DECEASED
                                </th>

                                <th>
                                    PACKAGE
                                </th>

                                <th>
                                    STATUS
                                </th>

                                <th>
                                    TOTAL COST
                                </th>

                                <th>
                                    TOTAL PAID
                                </th>

                                <th>
                                    BALANCE
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($services as $service): ?>

                                <tr>


                                    <!-- Funeral Service -->

                                    <td>

                                        <strong>
                                            #<?php
                                            echo htmlspecialchars(
                                                $service[
                                                    "funeral_service_id"
                                                ]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <!-- Deceased -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $service[
                                                "deceased_name"
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <!-- Package -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $service[
                                                "package_name"
                                            ]
                                        );
                                        ?>

                                    </td>


                                    <!-- Status -->

                                    <td>

                                        <span class="client-status">

                                            <?php
                                            echo htmlspecialchars(
                                                $service[
                                                    "status"
                                                ]
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- Total Cost -->

                                    <td>

                                        ₱<?php
                                        echo number_format(
                                            $service[
                                                "total_cost"
                                            ],
                                            2
                                        );
                                        ?>

                                    </td>


                                    <!-- Total Paid -->

                                    <td>

                                        ₱<?php
                                        echo number_format(
                                            $service[
                                                "total_paid"
                                            ],
                                            2
                                        );
                                        ?>

                                    </td>


                                    <!-- Balance -->

                                    <td>

                                        <strong>

                                            ₱<?php
                                            echo number_format(
                                                $service[
                                                    "balance"
                                                ],
                                                2
                                            );
                                            ?>

                                        </strong>

                                    </td>


                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </section>


        <!-- =========================
             ACTIONS
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        ACCOUNT FINANCIAL RECORDS
                    </span>

                    <h2>
                        Manage Your Payments
                    </h2>

                </div>

            </div>


            <div class="client-actions">


                <a
                    href="payments.php"
                    class="client-action primary"
                >

                    <strong>
                        View My Payments
                    </strong>

                    <span>
                        View your payment records and transaction details.
                    </span>

                </a>


                <a
                    href="request_service.php"
                    class="client-action"
                >

                    <strong>
                        Request Funeral Service
                    </strong>

                    <span>
                        Submit a new funeral service request.
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