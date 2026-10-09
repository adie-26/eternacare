<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Client");


// Get the logged-in client's information

$user_id = $_SESSION["user_id"];

$sql = "SELECT
            u.client_id,
            c.full_name,
            c.contact_number,
            c.email
        FROM users u

        LEFT JOIN clients c
            ON u.client_id = c.client_id

        WHERE u.user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$client = $result->fetch_assoc();

$stmt->close();


// Get the client's service count

$total_services = 0;
$active_services = 0;
$total_payments = 0;
$total_balance = 0;

if ($client && $client["client_id"]) {

    $client_id = $client["client_id"];


    // Total funeral services

    $service_sql = "SELECT
                        COUNT(*) AS total_services,

                        SUM(
                            CASE
                                WHEN status NOT IN
                                ('Completed', 'Cancelled')
                                THEN 1
                                ELSE 0
                            END
                        ) AS active_services

                    FROM funeral_services

                    WHERE client_id = ?";

    $service_stmt = $conn->prepare($service_sql);

    $service_stmt->bind_param(
        "i",
        $client_id
    );

    $service_stmt->execute();

    $service_result = $service_stmt->get_result();

    $service_data = $service_result->fetch_assoc();

    $total_services =
        $service_data["total_services"] ?? 0;

    $active_services =
        $service_data["active_services"] ?? 0;

    $service_stmt->close();


    // Total payments

    $payment_sql = "SELECT
                        COALESCE(
                            SUM(p.amount),
                            0
                        ) AS total_payments

                    FROM payments p

                    INNER JOIN funeral_services fs
                        ON p.funeral_service_id =
                           fs.funeral_service_id

                    WHERE fs.client_id = ?";

    $payment_stmt = $conn->prepare($payment_sql);

    $payment_stmt->bind_param(
        "i",
        $client_id
    );

    $payment_stmt->execute();

    $payment_result = $payment_stmt->get_result();

    $payment_data = $payment_result->fetch_assoc();

    $total_payments =
        $payment_data["total_payments"] ?? 0;

    $payment_stmt->close();


    // Get total balance

    $balance_sql = "SELECT
                        COALESCE(
                            SUM(
                                fs.total_cost
                                -
                                COALESCE(
                                    (
                                        SELECT SUM(p.amount)

                                        FROM payments p

                                        WHERE
                                            p.funeral_service_id =
                                            fs.funeral_service_id
                                    ),
                                    0
                                )
                            ),
                            0
                        ) AS total_balance

                    FROM funeral_services fs

                    WHERE
                        fs.client_id = ?

                    AND fs.status != 'Cancelled'";

    $balance_stmt = $conn->prepare($balance_sql);

    $balance_stmt->bind_param(
        "i",
        $client_id
    );

    $balance_stmt->execute();

    $balance_result = $balance_stmt->get_result();

    $balance_data = $balance_result->fetch_assoc();

    $total_balance =
        $balance_data["total_balance"] ?? 0;

    $balance_stmt->close();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EternaCare - Client Dashboard</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body class="client-page">

    <?php include "includes/client_header.php"; ?>


    <!-- =========================
         HERO SECTION
         ========================= -->

    <section class="client-hero">

        <div class="client-hero-content">

            <span class="client-hero-label">
                GARDEN OF EDEN FUNERAL CHAPEL & SERVICES
            </span>

            <h1>
                Compassionate Care,
                <br>
                Every Step of the Way
            </h1>

            <p>
                We provide dignified and compassionate funeral services
                to help families during one of life's most difficult moments.
            </p>

            <div class="client-hero-actions">

                <a href="request_service.php"
                   class="client-primary-btn">

                    REQUEST A SERVICE

                </a>

                <a href="packages.php"
                   class="client-outline-btn">

                    VIEW SERVICES

                </a>

            </div>

        </div>

    </section>


    <!-- =========================
         CLIENT DASHBOARD
         ========================= -->

    <main class="client-dashboard">


        <!-- Welcome -->

        <section class="client-welcome">

            <span>
                WELCOME TO ETERNACARE
            </span>

            <h2>

                Welcome,
                <?php
                echo htmlspecialchars(
                    $_SESSION["full_name"]
                );
                ?>!

            </h2>

            <p>
                Manage your funeral service information,
                requests, payments, and records in one place.
            </p>

        </section>


        <!-- =========================
             SUMMARY CARDS
             ========================= -->

        <section class="client-stats">


            <div class="client-stat-card">

                <div class="client-stat-icon">
                    +
                </div>

                <div>

                    <span>
                        TOTAL SERVICES
                    </span>

                    <strong>
                        <?php
                        echo $total_services;
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
                        ACTIVE SERVICES
                    </span>

                    <strong>
                        <?php
                        echo $active_services;
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
                        TOTAL PAYMENTS
                    </span>

                    <strong>
                        ₱<?php
                        echo number_format(
                            $total_payments,
                            2
                        );
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
                        REMAINING BALANCE
                    </span>

                    <strong>
                        ₱<?php
                        echo number_format(
                            $total_balance,
                            2
                        );
                        ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- =========================
             INFORMATION + ACTIONS
             ========================= -->

        <section class="client-content-grid">


            <!-- My Information -->

            <div class="client-panel">

                <div class="client-panel-heading">

                    <div>

                        <span>
                            CLIENT PROFILE
                        </span>

                        <h2>
                            My Information
                        </h2>

                    </div>

                    <a href="profile.php">
                        VIEW PROFILE
                    </a>

                </div>


                <?php if ($client): ?>

                    <div class="client-information">


                        <div class="information-row">

                            <span>
                                FULL NAME
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $client["full_name"]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="information-row">

                            <span>
                                CONTACT NUMBER
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $client["contact_number"]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="information-row">

                            <span>
                                EMAIL ADDRESS
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $client["email"]
                                );
                                ?>
                            </strong>

                        </div>


                    </div>

                <?php else: ?>

                    <p class="empty-message">
                        Client information is not available.
                    </p>

                <?php endif; ?>

            </div>


            <!-- Quick Actions -->

            <div class="client-panel">

                <div class="client-panel-heading">

                    <div>

                        <span>
                            QUICK ACCESS
                        </span>

                        <h2>
                            What would you like to do?
                        </h2>

                    </div>

                </div>


                <div class="client-actions">


                    <a href="request_service.php"
                       class="client-action primary">

                        <strong>
                            Request Funeral Service
                        </strong>

                        <span>
                            Start a new service request
                        </span>

                    </a>


                    <a href="packages.php"
                       class="client-action">

                        <strong>
                            View Funeral Packages
                        </strong>

                        <span>
                            Explore available packages
                        </span>

                    </a>


                    <a href="payments.php"
                       class="client-action">

                        <strong>
                            View My Payments
                        </strong>

                        <span>
                            Check your payment records
                        </span>

                    </a>


                </div>

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