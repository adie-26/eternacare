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


$message = "";
$error = "";


/* =========================
   SUBMIT PAYMENT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["submit_payment"])
) {

    $funeral_service_id = (int) ($_POST["funeral_service_id"] ?? 0);
    $amount = (float) ($_POST["amount"] ?? 0);
    $payment_method = trim($_POST["payment_method"] ?? "");
    $reference_number = trim($_POST["reference_number"] ?? "");
    $notes = trim($_POST["notes"] ?? "");


    /* =========================
       VALIDATE INPUT
    ========================= */

    if ($funeral_service_id <= 0) {

        $error = "Invalid funeral service.";

    } elseif ($amount <= 0) {

        $error = "Payment amount must be greater than zero.";

    } elseif (
        !in_array(
            $payment_method,
            ["Cash", "GCash", "Bank Transfer", "Other"],
            true
        )
    ) {

        $error = "Please select a valid payment method.";

    } else {


        /* =========================
           CHECK SERVICE OWNERSHIP
        ========================= */

        $sql = "SELECT
                    funeral_service_id,
                    total_cost,
                    status
                FROM funeral_services
                WHERE funeral_service_id = ?
                AND client_id = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ii",
            $funeral_service_id,
            $client_id
        );

        $stmt->execute();

        $service_result = $stmt->get_result();
        $service = $service_result->fetch_assoc();

        $stmt->close();


        if (!$service) {

            $error =
                "You are not allowed to make a payment for this service.";

        } elseif ($service["status"] === "Cancelled") {

            $error =
                "You cannot make a payment for a cancelled funeral service.";

        } else {


            /* =========================
               GET APPROVED PAYMENTS
            ========================= */

            $sql = "SELECT
                        COALESCE(SUM(amount), 0) AS approved_amount
                    FROM payments
                    WHERE funeral_service_id = ?
                    AND payment_status = 'Approved'";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "i",
                $funeral_service_id
            );

            $stmt->execute();

            $payment_result = $stmt->get_result();
            $payment_data = $payment_result->fetch_assoc();

            $stmt->close();


            $approved_amount = (float) (
                $payment_data["approved_amount"] ?? 0
            );

            $total_cost = (float) $service["total_cost"];

            $remaining_balance =
                $total_cost - $approved_amount;


            if ($remaining_balance < 0) {
                $remaining_balance = 0;
            }


            /* =========================
               CHECK PAYMENT AMOUNT
            ========================= */

            if ($remaining_balance <= 0) {

                $error =
                    "This funeral service has already been fully paid.";

            } elseif ($amount > $remaining_balance) {

                $error =
                    "Payment amount cannot be greater than the remaining balance of ₱"
                    . number_format($remaining_balance, 2)
                    . ".";

            } else {


                /* =========================
                   INSERT PAYMENT
                ========================= */

                $sql = "INSERT INTO payments
                        (
                            funeral_service_id,
                            amount,
                            payment_method,
                            reference_number,
                            payment_status,
                            notes
                        )
                        VALUES (?, ?, ?, ?, 'Pending', ?)";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "idsss",
                    $funeral_service_id,
                    $amount,
                    $payment_method,
                    $reference_number,
                    $notes
                );


                if ($stmt->execute()) {

                    $message =
                        "Payment submitted successfully. "
                        . "It is now pending verification.";

                } else {

                    $error =
                        "Unable to submit payment.";

                }

                $stmt->close();
            }
        }
    }
}


/* =========================
   GET CLIENT FUNERAL SERVICES
========================= */

$sql = "SELECT
            fs.funeral_service_id,
            fs.total_cost,
            fs.status,
            fs.service_start,
            fs.service_end,
            fs.location,
            d.full_name AS deceased_name,
            p.package_name

        FROM funeral_services fs

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        INNER JOIN packages p
            ON fs.package_id = p.package_id

        WHERE fs.client_id = ?

        ORDER BY fs.funeral_service_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$services_result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>EternaCare - My Payments</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="client-page">

<?php include "includes/client_header.php"; ?>


<!-- =========================================================
     MY PAYMENTS
     ========================================================= -->

<main class="client-dashboard">


    <!-- PAGE HEADER -->

    <section class="client-welcome">

        <div>

            <span class="client-section-label">
                MY ACCOUNT
            </span>

            <h1>
                My Payments
            </h1>

            <p>
                View your payment records, current balances,
                and submit payments for your funeral services.
            </p>

        </div>

    </section>


    <!-- MESSAGES -->

    <?php if (!empty($message)): ?>

        <div class="client-alert client-alert-success">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="client-alert client-alert-error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         NO SERVICES
         ===================================================== -->

    <?php if ($services_result->num_rows === 0): ?>

        <section class="client-panel">

            <div class="client-empty-box">

                <h3>
                    No Funeral Services
                </h3>

                <p>
                    You currently have no funeral services available
                    for payment.
                </p>

                <div class="client-actions">

                    <a
                        href="request_service.php"
                        class="client-action-btn"
                    >
                        Request Funeral Service
                    </a>

                </div>

            </div>

        </section>


    <?php else: ?>


        <!-- =================================================
             FUNERAL SERVICES
             ================================================= -->

        <?php while ($service = $services_result->fetch_assoc()): ?>


            <?php

            $service_id =
                (int) $service["funeral_service_id"];

            $total_cost =
                (float) $service["total_cost"];


            /* =========================
               APPROVED PAYMENTS
            ========================= */

            $sql = "SELECT
                        COALESCE(SUM(amount), 0) AS total_paid
                    FROM payments
                    WHERE funeral_service_id = ?
                    AND payment_status = 'Approved'";

            $payment_stmt = $conn->prepare($sql);

            $payment_stmt->bind_param(
                "i",
                $service_id
            );

            $payment_stmt->execute();

            $payment_result =
                $payment_stmt->get_result();

            $payment_summary =
                $payment_result->fetch_assoc();

            $payment_stmt->close();


            $total_paid =
                (float) (
                    $payment_summary["total_paid"] ?? 0
                );


            $remaining_balance =
                $total_cost - $total_paid;


            if ($remaining_balance < 0) {
                $remaining_balance = 0;
            }


            if ($total_paid <= 0) {

                $payment_status = "Unpaid";

            } elseif ($remaining_balance <= 0) {

                $payment_status = "Fully Paid";

            } else {

                $payment_status = "Partially Paid";

            }

            ?>


            <!-- =================================================
                 SERVICE INFORMATION
                 ================================================= -->

            <section class="client-panel service-record-panel">


                <div class="client-panel-header">

                    <div>

                        <span class="client-section-label">
                            FUNERAL SERVICE
                        </span>

                        <h2>
                            Service #<?php
                            echo $service_id;
                            ?>
                        </h2>

                    </div>


                    <span class="client-status">

                        <?php
                        echo htmlspecialchars(
                            $service["status"]
                        );
                        ?>

                    </span>

                </div>


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
                     PAYMENT SUMMARY
                     ================================================= -->

                <div class="client-section-title">

                    <span class="client-section-label">
                        PAYMENT SUMMARY
                    </span>

                    <h3>
                        Current Payment Status
                    </h3>

                </div>


                <div class="client-stats">


                    <div class="client-stat-card">

                        <span>
                            Total Service Cost
                        </span>

                        <strong>
                            ₱<?php
                            echo number_format(
                                $total_cost,
                                2
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="client-stat-card">

                        <span>
                            Amount Paid
                        </span>

                        <strong>
                            ₱<?php
                            echo number_format(
                                $total_paid,
                                2
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="client-stat-card">

                        <span>
                            Remaining Balance
                        </span>

                        <strong>
                            ₱<?php
                            echo number_format(
                                $remaining_balance,
                                2
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="client-stat-card">

                        <span>
                            Payment Status
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


                <!-- =================================================
                     SUBMIT PAYMENT
                     ================================================= -->

                <?php if ($remaining_balance > 0): ?>

                    <div class="client-panel client-inner-panel">

                        <div class="client-section-title">

                            <span class="client-section-label">
                                PAYMENT
                            </span>

                            <h3>
                                Submit a Payment
                            </h3>

                            <p>
                                Submit your payment information
                                for verification.
                            </p>

                        </div>


                        <form
                            method="POST"
                            class="client-form"
                        >


                            <input
                                type="hidden"
                                name="funeral_service_id"
                                value="<?php
                                echo $service_id;
                                ?>"
                            >


                            <div class="client-form-grid">


                                <!-- PAYMENT AMOUNT -->

                                <div class="client-form-group">

                                    <label>
                                        Payment Amount
                                    </label>

                                    <input
                                        type="number"
                                        name="amount"
                                        min="0.01"
                                        max="<?php
                                        echo $remaining_balance;
                                        ?>"
                                        step="0.01"
                                        placeholder="Enter payment amount"
                                        required
                                    >

                                    <small>
                                        Maximum payment:
                                        ₱<?php
                                        echo number_format(
                                            $remaining_balance,
                                            2
                                        );
                                        ?>
                                    </small>

                                </div>


                                <!-- PAYMENT METHOD -->

                                <div class="client-form-group">

                                    <label>
                                        Payment Method
                                    </label>

                                    <select
                                        name="payment_method"
                                        required
                                    >

                                        <option value="">
                                            Select payment method
                                        </option>

                                        <option value="Cash">
                                            Cash
                                        </option>

                                        <option value="GCash">
                                            GCash
                                        </option>

                                        <option value="Bank Transfer">
                                            Bank Transfer
                                        </option>

                                        <option value="Other">
                                            Other
                                        </option>

                                    </select>

                                </div>


                                <!-- REFERENCE NUMBER -->

                                <div class="client-form-group">

                                    <label>
                                        Reference Number
                                    </label>

                                    <input
                                        type="text"
                                        name="reference_number"
                                        maxlength="100"
                                        placeholder="Enter reference number if applicable"
                                    >

                                </div>


                                <!-- NOTES -->

                                <div class="client-form-group">

                                    <label>
                                        Notes
                                    </label>

                                    <textarea
                                        name="notes"
                                        rows="4"
                                        placeholder="Additional payment information (optional)"
                                    ></textarea>

                                </div>


                            </div>


                            <button
                                type="submit"
                                name="submit_payment"
                                class="client-action-btn"
                            >
                                Submit Payment
                            </button>


                        </form>

                    </div>

                <?php else: ?>

                    <div class="client-alert client-alert-success">

                        This funeral service has been fully paid.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     PAYMENT HISTORY
                     ================================================= -->

                <div class="client-panel client-inner-panel">

                    <div class="client-section-title">

                        <span class="client-section-label">
                            TRANSACTION HISTORY
                        </span>

                        <h3>
                            Payment Records
                        </h3>

                    </div>


                    <?php

                    $sql = "SELECT
                                payment_id,
                                amount,
                                payment_method,
                                reference_number,
                                payment_date,
                                payment_status,
                                notes
                            FROM payments
                            WHERE funeral_service_id = ?
                            ORDER BY payment_date DESC,
                                     payment_id DESC";

                    $history_stmt =
                        $conn->prepare($sql);

                    $history_stmt->bind_param(
                        "i",
                        $service_id
                    );

                    $history_stmt->execute();

                    $history_result =
                        $history_stmt->get_result();

                    ?>


                    <?php if ($history_result->num_rows === 0): ?>

                        <div class="client-empty-box">

                            <p>
                                No payment records are available yet.
                            </p>

                        </div>


                    <?php else: ?>


                        <div class="client-table-wrapper">

                            <table class="client-table">

                                <thead>

                                    <tr>

                                        <th>
                                            Payment ID
                                        </th>

                                        <th>
                                            Amount
                                        </th>

                                        <th>
                                            Method
                                        </th>

                                        <th>
                                            Reference
                                        </th>

                                        <th>
                                            Payment Date
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Notes
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php
                                    while (
                                        $payment =
                                        $history_result->fetch_assoc()
                                    ):
                                    ?>

                                        <tr>

                                            <td>

                                                <?php
                                                echo htmlspecialchars(
                                                    $payment["payment_id"]
                                                );
                                                ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    ₱<?php
                                                    echo number_format(
                                                        $payment["amount"],
                                                        2
                                                    );
                                                    ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?php
                                                echo htmlspecialchars(
                                                    $payment["payment_method"]
                                                );
                                                ?>

                                            </td>


                                            <td>

                                                <?php

                                                if (
                                                    !empty(
                                                        $payment[
                                                            "reference_number"
                                                        ]
                                                    )
                                                ) {

                                                    echo htmlspecialchars(
                                                        $payment[
                                                            "reference_number"
                                                        ]
                                                    );

                                                } else {

                                                    echo "N/A";

                                                }

                                                ?>

                                            </td>


                                            <td>

                                                <?php

                                                echo date(
                                                    "F d, Y h:i A",
                                                    strtotime(
                                                        $payment[
                                                            "payment_date"
                                                        ]
                                                    )
                                                );

                                                ?>

                                            </td>


                                            <td>

                                                <?php

                                                $status =
                                                    $payment[
                                                        "payment_status"
                                                    ];

                                                ?>


                                                <?php if ($status === "Approved"): ?>

                                                    <span class="client-status status-approved">
                                                        Approved
                                                    </span>


                                                <?php elseif ($status === "Rejected"): ?>

                                                    <span class="client-status status-rejected">
                                                        Rejected
                                                    </span>


                                                <?php else: ?>

                                                    <span class="client-status status-pending">
                                                        Pending
                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <td>

                                                <?php

                                                if (
                                                    !empty(
                                                        $payment["notes"]
                                                    )
                                                ) {

                                                    echo nl2br(
                                                        htmlspecialchars(
                                                            $payment["notes"]
                                                        )
                                                    );

                                                } else {

                                                    echo "N/A";

                                                }

                                                ?>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>


                    <?php endif; ?>


                    <?php
                    $history_stmt->close();
                    ?>


                </div>


            </section>


        <?php endwhile; ?>


    <?php endif; ?>


    <!-- =====================================================
         PAGE ACTIONS
         ===================================================== -->

    <section class="client-panel">

        <div class="client-actions">

            <a
                href="balance.php"
                class="client-action-btn"
            >
                View My Balance
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

<?php

$stmt->close();

?>