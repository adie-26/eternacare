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

            c.full_name AS client_name,

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


$error = "";


/*
 * Handle form submission.
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $amount = trim(
        $_POST["amount"] ?? ""
    );

    $payment_method = trim(
        $_POST["payment_method"] ?? ""
    );

    $reference_number = trim(
        $_POST["reference_number"] ?? ""
    );

    $payment_date = $_POST["payment_date"] ?? "";

    $notes = trim(
        $_POST["notes"] ?? ""
    );


    /*
     * Validate amount.
     */

    if ($amount === "" || !is_numeric($amount)) {

        $error =
            "Please enter a valid payment amount.";

    } elseif (floatval($amount) <= 0) {

        $error =
            "Payment amount must be greater than zero.";

    } elseif ($payment_method === "") {

        $error =
            "Please select a payment method.";

    } elseif ($payment_date === "") {

        $error =
            "Please select a payment date.";

    } else {


        /*
         * Get total amount paid by other payments.
         *
         * The current payment is excluded because
         * we are editing it.
         */

        $paid_sql = "SELECT
                         COALESCE(SUM(amount), 0)
                         AS total_paid

                     FROM payments

                     WHERE funeral_service_id = ?

                     AND payment_id != ?";

        $paid_stmt =
            $conn->prepare($paid_sql);

        $paid_stmt->bind_param(
            "ii",
            $payment[
                "funeral_service_id"
            ],
            $payment_id
        );

        $paid_stmt->execute();

        $paid_result =
            $paid_stmt->get_result();

        $paid_data =
            $paid_result->fetch_assoc();

        $paid_stmt->close();


        $other_payments =
            floatval(
                $paid_data["total_paid"]
            );


        /*
         * Get total service cost.
         */

        $total_cost =
            floatval(
                $payment["total_cost"]
            );


        /*
         * Calculate remaining balance
         * excluding the payment being edited.
         */

        $remaining_balance =
            $total_cost - $other_payments;


        /*
         * Prevent overpayment.
         */

        if (
            floatval($amount)
            > $remaining_balance
        ) {

            $error =
                "Payment amount cannot be greater than "
                . "the remaining balance of ₱"
                . number_format(
                    max(
                        0,
                        $remaining_balance
                    ),
                    2
                )
                . ".";

        } else {


            /*
             * Update payment.
             */

            $update_sql =
                "UPDATE payments

                 SET
                     amount = ?,
                     payment_method = ?,
                     reference_number = ?,
                     payment_date = ?,
                     notes = ?

                 WHERE payment_id = ?";


            $update_stmt =
                $conn->prepare(
                    $update_sql
                );


            $amount_value =
                floatval($amount);


            $update_stmt->bind_param(
                "dssssi",
                $amount_value,
                $payment_method,
                $reference_number,
                $payment_date,
                $notes,
                $payment_id
            );


            if ($update_stmt->execute()) {

                $update_stmt->close();

                header(
                    "Location: view_payment.php?id="
                    . $payment_id
                );

                exit();

            } else {

                $error =
                    "Error updating payment: "
                    . $update_stmt->error;

                $update_stmt->close();

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

    <title>
        EternaCare - Edit Payment
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


    <!-- Main Content -->

    <main class="main-content">


        <section class="top-header">

            <h1>
                Edit Payment
            </h1>

            <p>
                Update payment information.
            </p>

        </section>


        <section class="content-section">

            <h2>
                Payment Information
            </h2>


            <?php if ($error !== ""): ?>

                <p style="color: red;">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </p>

            <?php endif; ?>


            <!-- Funeral Service Information -->

            <div
                style="
                    background: #f5f5f5;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                "
            >

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
                        Client:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $payment["client_name"]
                    );
                    ?>

                </p>


                <p>

                    <strong>
                        Deceased:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $payment[
                            "deceased_name"
                        ]
                    );
                    ?>

                </p>


                <p>

                    <strong>
                        Total Service Cost:
                    </strong>

                    ₱<?php
                    echo number_format(
                        $payment["total_cost"],
                        2
                    );
                    ?>

                </p>

            </div>


            <form method="POST">


                <div class="form-group">

                    <label for="amount">
                        Payment Amount *
                    </label>

                    <input
                        type="number"
                        name="amount"
                        id="amount"
                        step="0.01"
                        min="0.01"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST["amount"]
                            ?? $payment["amount"]
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="payment_method">
                        Payment Method *
                    </label>

                    <select
                        name="payment_method"
                        id="payment_method"
                        required
                    >

                        <option value="">
                            Select Payment Method
                        </option>

                        <option
                            value="Cash"
                            <?php
                            echo (
                                (
                                    $_POST[
                                        "payment_method"
                                    ]
                                    ?? $payment[
                                        "payment_method"
                                    ]
                                )
                                === "Cash"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Cash
                        </option>

                        <option
                            value="GCash"
                            <?php
                            echo (
                                (
                                    $_POST[
                                        "payment_method"
                                    ]
                                    ?? $payment[
                                        "payment_method"
                                    ]
                                )
                                === "GCash"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            GCash
                        </option>

                        <option
                            value="Bank Transfer"
                            <?php
                            echo (
                                (
                                    $_POST[
                                        "payment_method"
                                    ]
                                    ?? $payment[
                                        "payment_method"
                                    ]
                                )
                                === "Bank Transfer"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Bank Transfer
                        </option>

                        <option
                            value="Credit/Debit Card"
                            <?php
                            echo (
                                (
                                    $_POST[
                                        "payment_method"
                                    ]
                                    ?? $payment[
                                        "payment_method"
                                    ]
                                )
                                === "Credit/Debit Card"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Credit/Debit Card
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="reference_number">
                        Reference Number
                    </label>

                    <input
                        type="text"
                        name="reference_number"
                        id="reference_number"
                        placeholder="Optional reference number"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST[
                                "reference_number"
                            ]
                            ?? (
                                $payment[
                                    "reference_number"
                                ] ?? ""
                            )
                        );
                        ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="payment_date">
                        Payment Date *
                    </label>

                    <input
                        type="date"
                        name="payment_date"
                        id="payment_date"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST[
                                "payment_date"
                            ]
                            ?? $payment[
                                "payment_date"
                            ]
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="notes">
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="4"
                        placeholder="Optional payment notes..."
                    ><?php
                    echo htmlspecialchars(
                        $_POST["notes"]
                        ?? (
                            $payment["notes"]
                            ?? ""
                        )
                    );
                    ?></textarea>

                </div>


                <br>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>


                <a
                    href="view_payment.php?id=<?php
                    echo $payment_id;
                    ?>"
                    class="btn"
                >
                    Cancel
                </a>


            </form>

        </section>


    </main>


</body>

</html>