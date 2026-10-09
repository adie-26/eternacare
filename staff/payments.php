<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


$message = "";
$error = "";


/* =========================
   APPROVE / REJECT PAYMENT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_id = (int) ($_POST["payment_id"] ?? 0);

    if ($payment_id <= 0) {

        $error = "Invalid payment record.";

    } elseif (isset($_POST["approve_payment"])) {

        $sql = "UPDATE payments
                SET payment_status = 'Approved'
                WHERE payment_id = ?
                AND payment_status = 'Pending'";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "i",
            $payment_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message = "Payment approved successfully.";

            } else {

                $error =
                    "This payment has already been processed.";

            }

        } else {

            $error = "Unable to approve payment.";

        }

        $stmt->close();


    } elseif (isset($_POST["reject_payment"])) {

        $sql = "UPDATE payments
                SET payment_status = 'Rejected'
                WHERE payment_id = ?
                AND payment_status = 'Pending'";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "i",
            $payment_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                $message = "Payment rejected successfully.";

            } else {

                $error =
                    "This payment has already been processed.";

            }

        } else {

            $error = "Unable to reject payment.";

        }

        $stmt->close();
    }
}


/* =========================
   GET PAYMENT RECORDS
========================= */

$sql = "SELECT
            p.payment_id,
            p.funeral_service_id,
            p.amount,
            p.payment_method,
            p.reference_number,
            p.payment_date,
            p.payment_status,
            p.notes,

            c.full_name AS client_name,

            d.full_name AS deceased_name,

            fs.total_cost

        FROM payments p

        INNER JOIN funeral_services fs
            ON p.funeral_service_id =
               fs.funeral_service_id

        INNER JOIN clients c
            ON fs.client_id = c.client_id

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        ORDER BY
            CASE
                WHEN p.payment_status = 'Pending'
                THEN 1
                WHEN p.payment_status = 'Approved'
                THEN 2
                ELSE 3
            END,

            p.payment_date DESC,
            p.payment_id DESC";

$result = $conn->query($sql);


/* =========================
   GET APPROVED TOTAL PAYMENTS
========================= */

$total_sql = "SELECT
                  COALESCE(SUM(amount), 0)
              AS total_payments

              FROM payments

              WHERE payment_status = 'Approved'";

$total_result = $conn->query($total_sql);

$total_row = $total_result->fetch_assoc();

$total_payments =
    (float) $total_row["total_payments"];


/* =========================
   GET PENDING COUNT
========================= */

$pending_sql = "SELECT
                    COUNT(*) AS pending_count

                FROM payments

                WHERE payment_status = 'Pending'";

$pending_result = $conn->query($pending_sql);

$pending_row =
    $pending_result->fetch_assoc();

$pending_count =
    (int) $pending_row["pending_count"];

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
        EternaCare - Payments
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="logo">

        <h2>
            EternaCare
        </h2>

        <p>
            Funeral Service Management
        </p>

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
            <a href="clients.php">
                Clients
            </a>
        </li>

        <li>
            <a href="deceased.php">
                Deceased Records
            </a>
        </li>

        <li>
            <a href="packages.php">
                Funeral Packages
            </a>
        </li>

        <li>
            <a href="optional_services.php">
                Optional Services
            </a>
        </li>

        <li>
            <a href="funeral_schedules.php">
                Funeral Schedules
            </a>
        </li>

        <li>
            <a href="staff_assignments.php">
                Staff Assignments
            </a>
        </li>

        <li>
            <a href="vehicle_assignments.php">
                Vehicle Assignments
            </a>
        </li>

        <li>
            <a href="preparation_tasks.php">
                Preparation Tasks
            </a>
        </li>

        <li>
            <a
                href="payments.php"
            >
                Payments
            </a>
        </li>

        <li>
            <a href="service_history.php">
                Service History
            </a>
        </li>

        <li>
            <a href="reports.php">
                Reports
            </a>
        </li>

        <li>
            <a href="../logout.php">
                Logout
            </a>
        </li>

    </ul>

</aside>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main-content">


    <section class="top-header">

        <h1>
            Payments
        </h1>

        <p>
            Manage and verify funeral service payment records.
        </p>

    </section>


    <!-- =========================
         MESSAGES
    ========================= -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-success">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         PAYMENT SUMMARY
    ========================= -->

    <section class="dashboard-cards">


        <div class="card">

            <h3>
                Approved Payments
            </h3>

            <p>

                ₱<?php
                echo number_format(
                    $total_payments,
                    2
                );
                ?>

            </p>

        </div>


        <div class="card">

            <h3>
                Pending Payments
            </h3>

            <p>

                <?php
                echo $pending_count;
                ?>

            </p>

        </div>


    </section>


    <br>


    <!-- =========================
         PAYMENT RECORDS
    ========================= -->

    <section class="content-section">


        <div
            style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            "
        >

            <h2>
                Payment Records
            </h2>

        </div>


        <?php if ($result->num_rows > 0): ?>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Payment ID
                            </th>

                            <th>
                                Funeral Service
                            </th>

                            <th>
                                Client
                            </th>

                            <th>
                                Deceased
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Payment Method
                            </th>

                            <th>
                                Reference Number
                            </th>

                            <th>
                                Payment Date
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while (
                            $payment =
                            $result->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- PAYMENT ID -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment["payment_id"]
                                    );
                                    ?>

                                </td>


                                <!-- FUNERAL SERVICE -->

                                <td>

                                    #<?php
                                    echo htmlspecialchars(
                                        $payment[
                                            "funeral_service_id"
                                        ]
                                    );
                                    ?>

                                </td>


                                <!-- CLIENT -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment[
                                            "client_name"
                                        ]
                                    );
                                    ?>

                                </td>


                                <!-- DECEASED -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment[
                                            "deceased_name"
                                        ]
                                    );
                                    ?>

                                </td>


                                <!-- AMOUNT -->

                                <td>

                                    ₱<?php
                                    echo number_format(
                                        $payment["amount"],
                                        2
                                    );
                                    ?>

                                </td>


                                <!-- PAYMENT METHOD -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment[
                                            "payment_method"
                                        ]
                                    );
                                    ?>

                                </td>


                                <!-- REFERENCE NUMBER -->

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


                                <!-- PAYMENT DATE -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            "F d, Y",
                                            strtotime(
                                                $payment[
                                                    "payment_date"
                                                ]
                                            )
                                        )
                                    );
                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>


                                    <?php

                                    $status =
                                        $payment[
                                            "payment_status"
                                        ];

                                    ?>


                                    <?php if (
                                        $status === "Approved"
                                    ): ?>

                                        <span
                                            class="status-badge status-approved"
                                        >
                                            Approved
                                        </span>


                                    <?php elseif (
                                        $status === "Rejected"
                                    ): ?>

                                        <span
                                            class="status-badge status-rejected"
                                        >
                                            Rejected
                                        </span>


                                    <?php else: ?>

                                        <span
                                            class="status-badge status-pending"
                                        >
                                            Pending
                                        </span>

                                    <?php endif; ?>


                                </td>


                                <!-- ACTION -->

                                <td>


                                    <?php if (
                                        $status === "Pending"
                                    ): ?>


                                        <form
                                            method="POST"
                                            style="
                                                display: inline-block;
                                                margin-right: 5px;
                                            "
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to approve this payment?'
                                                );
                                            "
                                        >

                                            <input
                                                type="hidden"
                                                name="payment_id"
                                                value="<?php
                                                echo $payment[
                                                    "payment_id"
                                                ];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="approve_payment"
                                                class="btn btn-success"
                                            >
                                                Approve
                                            </button>

                                        </form>


                                        <form
                                            method="POST"
                                            style="
                                                display: inline-block;
                                            "
                                            onsubmit="
                                                return confirm(
                                                    'Are you sure you want to reject this payment?'
                                                );
                                            "
                                        >

                                            <input
                                                type="hidden"
                                                name="payment_id"
                                                value="<?php
                                                echo $payment[
                                                    "payment_id"
                                                ];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="reject_payment"
                                                class="btn btn-danger"
                                            >
                                                Reject
                                            </button>

                                        </form>


                                    <?php else: ?>


                                        <span
                                            style="
                                                color: #737d83;
                                                font-size: 14px;
                                            "
                                        >
                                            No action
                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <p>
                No payment records found.
            </p>

            <p>
                Payment records will appear here after
                a client submits a payment.
            </p>


        <?php endif; ?>


    </section>


</main>


</body>

</html>