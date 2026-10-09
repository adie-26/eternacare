<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


$error = "";


/*
 * Get funeral service ID from URL.
 */

$funeral_service_id = isset($_GET["funeral_service_id"])
    ? intval($_GET["funeral_service_id"])
    : 0;


/*
 * Get all funeral services.
 */

$services_sql = "SELECT
                    fs.funeral_service_id,
                    fs.total_cost,
                    fs.service_start,

                    c.full_name AS client_name,

                    d.full_name AS deceased_name

                 FROM funeral_services fs

                 INNER JOIN clients c
                    ON fs.client_id = c.client_id

                 INNER JOIN deceased d
                    ON fs.deceased_id = d.deceased_id

                 ORDER BY fs.service_start DESC";

$services_result = $conn->query($services_sql);


/*
 * Get selected funeral service information.
 */

$selected_service = null;

if ($funeral_service_id > 0) {

    $service_sql = "SELECT
                        fs.funeral_service_id,
                        fs.total_cost,
                        fs.service_start,

                        c.full_name AS client_name,

                        d.full_name AS deceased_name

                    FROM funeral_services fs

                    INNER JOIN clients c
                        ON fs.client_id = c.client_id

                    INNER JOIN deceased d
                        ON fs.deceased_id = d.deceased_id

                    WHERE fs.funeral_service_id = ?";

    $service_stmt = $conn->prepare($service_sql);

    $service_stmt->bind_param(
        "i",
        $funeral_service_id
    );

    $service_stmt->execute();

    $service_result = $service_stmt->get_result();

    $selected_service =
        $service_result->fetch_assoc();

    $service_stmt->close();

}


/*
 * Handle form submission.
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $funeral_service_id =
        intval(
            $_POST["funeral_service_id"] ?? 0
        );

    $amount =
        trim(
            $_POST["amount"] ?? ""
        );

    $payment_method =
        trim(
            $_POST["payment_method"] ?? ""
        );

    $reference_number =
        trim(
            $_POST["reference_number"] ?? ""
        );

    $payment_date =
        $_POST["payment_date"] ?? date("Y-m-d");

    $notes =
        trim(
            $_POST["notes"] ?? ""
        );


    /*
     * Validate funeral service.
     */

    if ($funeral_service_id <= 0) {

        $error =
            "Please select a funeral service.";

    } elseif ($amount === "" || !is_numeric($amount)) {

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
         * Get funeral service total cost.
         */

        $check_sql = "SELECT
                          total_cost

                      FROM funeral_services

                      WHERE funeral_service_id = ?";

        $check_stmt =
            $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "i",
            $funeral_service_id
        );

        $check_stmt->execute();

        $check_result =
            $check_stmt->get_result();

        $service_data =
            $check_result->fetch_assoc();

        $check_stmt->close();


        if (!$service_data) {

            $error =
                "The selected funeral service does not exist.";

        } else {

            $total_cost =
                floatval(
                    $service_data["total_cost"]
                );


            /*
             * Calculate previous payments.
             */

            $paid_sql = "SELECT
                             COALESCE(
                                 SUM(amount),
                                 0
                             ) AS total_paid

                         FROM payments

                         WHERE funeral_service_id = ?";

            $paid_stmt =
                $conn->prepare($paid_sql);

            $paid_stmt->bind_param(
                "i",
                $funeral_service_id
            );

            $paid_stmt->execute();

            $paid_result =
                $paid_stmt->get_result();

            $paid_data =
                $paid_result->fetch_assoc();

            $paid_stmt->close();


            $total_paid =
                floatval(
                    $paid_data["total_paid"]
                );


            /*
             * Calculate remaining balance.
             */

            $remaining_balance =
                $total_cost - $total_paid;


            /*
             * Prevent overpayment.
             */

            if (floatval($amount) > $remaining_balance) {

                $error =
                    "Payment amount cannot be greater than the remaining balance of ₱"
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
                 * Insert payment.
                 */

                $insert_sql =
                    "INSERT INTO payments
                    (
                        funeral_service_id,
                        amount,
                        payment_method,
                        reference_number,
                        payment_date,
                        notes
                    )

                    VALUES (?, ?, ?, ?, ?, ?)";

                $insert_stmt =
                    $conn->prepare(
                        $insert_sql
                    );

                $amount_value =
                    floatval($amount);

                $insert_stmt->bind_param(
                    "idssss",
                    $funeral_service_id,
                    $amount_value,
                    $payment_method,
                    $reference_number,
                    $payment_date,
                    $notes
                );


                if ($insert_stmt->execute()) {

                    $insert_stmt->close();

                    header(
                        "Location: payments.php"
                    );

                    exit();

                } else {

                    $error =
                        "Error adding payment: "
                        . $insert_stmt->error;

                    $insert_stmt->close();

                }

            }

        }

    }

}


/*
 * If there is a selected service,
 * refresh its information for the form.
 */

if ($funeral_service_id > 0) {

    $service_sql = "SELECT
                        fs.funeral_service_id,
                        fs.total_cost,
                        fs.service_start,

                        c.full_name AS client_name,

                        d.full_name AS deceased_name

                    FROM funeral_services fs

                    INNER JOIN clients c
                        ON fs.client_id = c.client_id

                    INNER JOIN deceased d
                        ON fs.deceased_id = d.deceased_id

                    WHERE fs.funeral_service_id = ?";

    $service_stmt =
        $conn->prepare($service_sql);

    $service_stmt->bind_param(
        "i",
        $funeral_service_id
    );

    $service_stmt->execute();

    $service_result =
        $service_stmt->get_result();

    $selected_service =
        $service_result->fetch_assoc();

    $service_stmt->close();

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
        EternaCare - Add Payment
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
                Add Payment
            </h1>

            <p>
                Record a payment for a funeral service.
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


            <?php if ($services_result->num_rows > 0): ?>


                <form method="POST">


                    <div class="form-group">

                        <label for="funeral_service_id">
                            Funeral Service *
                        </label>

                        <select
                            name="funeral_service_id"
                            id="funeral_service_id"
                            required
                            onchange="this.form.submit()"
                        >

                            <option value="">
                                Select Funeral Service
                            </option>


                            <?php while (
                                $service =
                                $services_result->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?php
                                    echo $service[
                                        "funeral_service_id"
                                    ];
                                    ?>"
                                    <?php
                                    echo (
                                        $funeral_service_id
                                        ==
                                        $service[
                                            "funeral_service_id"
                                        ]
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >

                                    <?php
                                    echo "#"
                                        . htmlspecialchars(
                                            $service[
                                                "funeral_service_id"
                                            ]
                                        )
                                        . " - "
                                        . htmlspecialchars(
                                            $service[
                                                "client_name"
                                            ]
                                        )
                                        . " - "
                                        . htmlspecialchars(
                                            $service[
                                                "deceased_name"
                                            ]
                                        );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <?php if ($selected_service): ?>

                        <div
                            class="form-group"
                            style="
                                background: #f5f5f5;
                                padding: 15px;
                                border-radius: 8px;
                            "
                        >

                            <p>

                                <strong>
                                    Client:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $selected_service[
                                        "client_name"
                                    ]
                                );
                                ?>

                            </p>


                            <p>

                                <strong>
                                    Deceased:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $selected_service[
                                        "deceased_name"
                                    ]
                                );
                                ?>

                            </p>


                            <p>

                                <strong>
                                    Total Cost:
                                </strong>

                                ₱<?php
                                echo number_format(
                                    $selected_service[
                                        "total_cost"
                                    ],
                                    2
                                );
                                ?>

                            </p>

                        </div>

                    <?php endif; ?>


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
                            placeholder="Enter payment amount"
                            value="<?php
                            echo htmlspecialchars(
                                $_POST[
                                    "amount"
                                ] ?? ""
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
                                    ($_POST[
                                        "payment_method"
                                    ] ?? "")
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
                                    ($_POST[
                                        "payment_method"
                                    ] ?? "")
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
                                    ($_POST[
                                        "payment_method"
                                    ] ?? "")
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
                                    ($_POST[
                                        "payment_method"
                                    ] ?? "")
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
                                ] ?? ""
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
                                ?? date("Y-m-d")
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
                            $_POST[
                                "notes"
                            ] ?? ""
                        );
                        ?></textarea>

                    </div>


                    <br>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add Payment
                    </button>


                    <a
                        href="payments.php"
                        class="btn"
                    >
                        Cancel
                    </a>


                </form>


            <?php else: ?>

                <p>
                    No funeral services are available.
                </p>

                <p>
                    Create a funeral schedule first before
                    adding a payment.
                </p>

                <br>

                <a
                    href="funeral_schedules.php"
                    class="btn btn-primary"
                >
                    Go to Funeral Schedules
                </a>

            <?php endif; ?>


        </section>


    </main>


</body>

</html>