<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get payment ID from URL.
 */

$payment_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


/*
 * Validate payment ID.
 */

if ($payment_id <= 0) {

    header("Location: payments.php");
    exit();

}


/*
 * Get payment information.
 */

$sql = "SELECT
            p.payment_id,
            p.funeral_service_id,
            p.amount,
            p.payment_method,
            p.reference_number,
            p.payment_date,
            p.notes,

            fs.total_cost,
            fs.service_start,
            fs.service_end,
            fs.location,
            fs.status AS service_status,

            c.full_name AS client_name,
            c.contact_number AS client_contact,
            c.email AS client_email,

            d.full_name AS deceased_name

        FROM payments p

        INNER JOIN funeral_services fs
            ON p.funeral_service_id = fs.funeral_service_id

        INNER JOIN clients c
            ON fs.client_id = c.client_id

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        WHERE p.payment_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $payment_id
);

$stmt->execute();

$result = $stmt->get_result();

$payment = $result->fetch_assoc();

$stmt->close();


/*
 * If payment does not exist,
 * return to payments page.
 */

if (!$payment) {

    header("Location: payments.php");
    exit();

}


/*
 * Calculate total amount already paid
 * for this funeral service.
 */

$paid_sql = "SELECT
                 COALESCE(SUM(amount), 0) AS total_paid

             FROM payments

             WHERE funeral_service_id = ?";

$paid_stmt = $conn->prepare($paid_sql);

$paid_stmt->bind_param(
    "i",
    $payment["funeral_service_id"]
);

$paid_stmt->execute();

$paid_result = $paid_stmt->get_result();

$paid_data = $paid_result->fetch_assoc();

$paid_stmt->close();


$total_paid = floatval(
    $paid_data["total_paid"]
);


/*
 * Calculate remaining balance.
 */

$total_cost = floatval(
    $payment["total_cost"]
);

$remaining_balance = $total_cost - $total_paid;

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
        EternaCare - View Payment
    </title>

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
                <a href="vehicles.php">
                    Vehicles
                </a>
            </li>

            <li>
                <a href="preparation_tasks.php">
                    Preparation Tasks
                </a>
            </li>

            <li>
                <a href="payments.php">
                    Payments
                </a>
            </li>

            <li>
                <a href="#">
                    Service History
                </a>
            </li>

            <li>
                <a href="#">
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


    <!-- Main Content -->

    <main class="main-content">


        <section class="top-header">

            <h1>
                Payment Details
            </h1>

            <p>
                View complete payment information.
            </p>

        </section>


        <!-- Payment Information -->

        <section class="content-section">

            <h2>
                Payment Information
            </h2>


            <p>

                <strong>
                    Payment ID:
                </strong>

                #<?php
                echo htmlspecialchars(
                    $payment["payment_id"]
                );
                ?>

            </p>


            <p>

                <strong>
                    Funeral Service:
                </strong>

                #<?php
                echo htmlspecialchars(
                    $payment[
                        "funeral_service_id"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Payment Amount:
                </strong>

                ₱<?php
                echo number_format(
                    $payment["amount"],
                    2
                );
                ?>

            </p>


            <p>

                <strong>
                    Payment Method:
                </strong>

                <?php
                echo htmlspecialchars(
                    $payment[
                        "payment_method"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Reference Number:
                </strong>

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

                    echo "None";

                }

                ?>

            </p>


            <p>

                <strong>
                    Payment Date:
                </strong>

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

            </p>


            <p>

                <strong>
                    Notes:
                </strong>

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

                    echo "None";

                }

                ?>

            </p>

        </section>


        <br>


        <!-- Client Information -->

        <section class="content-section">

            <h2>
                Client Information
            </h2>


            <p>

                <strong>
                    Client Name:
                </strong>

                <?php
                echo htmlspecialchars(
                    $payment[
                        "client_name"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Contact Number:
                </strong>

                <?php
                echo htmlspecialchars(
                    $payment[
                        "client_contact"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Email:
                </strong>

                <?php
                echo htmlspecialchars(
                    $payment[
                        "client_email"
                    ]
                );
                ?>

            </p>

        </section>


        <br>


        <!-- Deceased Information -->

        <section class="content-section">

            <h2>
                Deceased Information
            </h2>


            <p>

                <strong>
                    Deceased Name:
                </strong>

                <?php
                echo htmlspecialchars(
                    $payment[
                        "deceased_name"
                    ]
                );
                ?>

            </p>

        </section>


        <br>


        <!-- Funeral Service Information -->

        <section class="content-section">

            <h2>
                Funeral Service Information
            </h2>


            <p>

                <strong>
                    Service Start:
                </strong>

                <?php
                echo htmlspecialchars(
                    date(
                        "F d, Y h:i A",
                        strtotime(
                            $payment[
                                "service_start"
                            ]
                        )
                    )
                );
                ?>

            </p>


            <p>

                <strong>
                    Service End:
                </strong>

                <?php
                echo htmlspecialchars(
                    date(
                        "F d, Y h:i A",
                        strtotime(
                            $payment[
                                "service_end"
                            ]
                        )
                    )
                );
                ?>

            </p>


            <p>

                <strong>
                    Location:
                </strong>

                <?php
                echo htmlspecialchars(
                    $payment[
                        "location"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Service Status:
                </strong>

                <?php
                echo htmlspecialchars(
                    $payment[
                        "service_status"
                    ]
                );
                ?>

            </p>

        </section>


        <br>


        <!-- Payment Summary -->

        <section class="content-section">

            <h2>
                Payment Summary
            </h2>


            <p>

                <strong>
                    Total Service Cost:
                </strong>

                ₱<?php
                echo number_format(
                    $total_cost,
                    2
                );
                ?>

            </p>


            <p>

                <strong>
                    Total Amount Paid:
                </strong>

                ₱<?php
                echo number_format(
                    $total_paid,
                    2
                );
                ?>

            </p>


            <p>

                <strong>
                    Remaining Balance:
                </strong>

                ₱<?php
                echo number_format(
                    max(
                        0,
                        $remaining_balance
                    ),
                    2
                );
                ?>

            </p>

        </section>


        <br>


        <a
            href="payments.php"
            class="btn"
        >
            Back to Payments
        </a>


    </main>


</body>

</html>