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
   GET CLIENT'S DECEASED
========================= */

$sql = "SELECT deceased_id, full_name, date_of_death
        FROM deceased
        WHERE client_id = ?
        ORDER BY full_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$deceased_result = $stmt->get_result();

$deceased_records = [];

while ($row = $deceased_result->fetch_assoc()) {
    $deceased_records[] = $row;
}

$stmt->close();


/* =========================
   GET AVAILABLE PACKAGES
========================= */

$sql = "SELECT package_id, package_name, description, price
        FROM packages
        WHERE status = 'Available'
        ORDER BY package_name ASC";

$package_result = $conn->query($sql);

$packages = [];

while ($row = $package_result->fetch_assoc()) {
    $packages[] = $row;
}


/* =========================
   GET AVAILABLE OPTIONAL SERVICES
========================= */

$sql = "SELECT optional_service_id, service_name, description, price
        FROM optional_services
        WHERE status = 'Available'
        ORDER BY service_name ASC";

$optional_result = $conn->query($sql);

$optional_services = [];

while ($row = $optional_result->fetch_assoc()) {
    $optional_services[] = $row;
}


/* =========================
   SUBMIT SERVICE REQUEST
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $deceased_id = (int) ($_POST["deceased_id"] ?? 0);
    $package_id = (int) ($_POST["package_id"] ?? 0);

    $service_start_raw =
        trim($_POST["service_start"] ?? "");

    $service_end_raw =
        trim($_POST["service_end"] ?? "");

    $location =
        trim($_POST["location"] ?? "");

    $notes =
        trim($_POST["notes"] ?? "");

    $selected_optional_services =
        $_POST["optional_services"] ?? [];


    if (!is_array($selected_optional_services)) {
        $selected_optional_services = [];
    }


    /* =========================
       BASIC VALIDATION
    ========================= */

    if (
        $deceased_id <= 0 ||
        $package_id <= 0 ||
        $service_start_raw === "" ||
        $service_end_raw === "" ||
        $location === ""
    ) {

        $error =
            "Please fill in all required fields.";

    } else {


        /* =========================
           VALIDATE DATE AND TIME
        ========================= */

        $start_date = DateTime::createFromFormat(
            "Y-m-d\TH:i",
            $service_start_raw
        );

        $end_date = DateTime::createFromFormat(
            "Y-m-d\TH:i",
            $service_end_raw
        );

        $start_errors = DateTime::getLastErrors();

        $end_errors = DateTime::getLastErrors();


        $start_valid =
            $start_date !== false &&
            (
                $start_errors === false ||
                (
                    $start_errors["warning_count"] === 0 &&
                    $start_errors["error_count"] === 0
                )
            );


        $end_valid =
            $end_date !== false &&
            (
                $end_errors === false ||
                (
                    $end_errors["warning_count"] === 0 &&
                    $end_errors["error_count"] === 0
                )
            );


        if (!$start_valid || !$end_valid) {

            $error =
                "Please enter a valid service start and end date and time.";

        } elseif ($end_date <= $start_date) {

            $error =
                "The end date and time must be later than the start date and time.";

        } else {


            /* =========================
               CONVERT TO MYSQL DATETIME
            ========================= */

            $service_start =
                $start_date->format("Y-m-d H:i:s");

            $service_end =
                $end_date->format("Y-m-d H:i:s");


            /* =========================
               VERIFY DECEASED
            ========================= */

            $sql = "SELECT deceased_id
                    FROM deceased
                    WHERE deceased_id = ?
                    AND client_id = ?
                    LIMIT 1";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ii",
                $deceased_id,
                $client_id
            );

            $stmt->execute();

            $deceased_check =
                $stmt->get_result();

            $stmt->close();


            if ($deceased_check->num_rows !== 1) {

                $error =
                    "Invalid deceased record.";

            } else {


                /* =========================
                   GET PACKAGE
                ========================= */

                $sql = "SELECT package_id, price
                        FROM packages
                        WHERE package_id = ?
                        AND status = 'Available'
                        LIMIT 1";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "i",
                    $package_id
                );

                $stmt->execute();

                $package_check =
                    $stmt->get_result();

                $package =
                    $package_check->fetch_assoc();

                $stmt->close();


                if (!$package) {

                    $error =
                        "The selected funeral package is not available.";

                } else {


                    /* =========================
                       CHECK SCHEDULE CONFLICT
                    ========================= */

                    /*
                     * A schedule conflicts when:
                     *
                     * Existing Start < New End
                     *
                     * AND
                     *
                     * Existing End > New Start
                     *
                     * This correctly detects overlapping
                     * funeral service schedules.
                     */

                    $sql = "SELECT funeral_service_id
                            FROM funeral_services
                            WHERE status <> 'Cancelled'
                            AND service_start < ?
                            AND service_end > ?
                            LIMIT 1";

                    $stmt =
                        $conn->prepare($sql);

                    $stmt->bind_param(
                        "ss",
                        $service_end,
                        $service_start
                    );

                    $stmt->execute();

                    $conflict_result =
                        $stmt->get_result();

                    $stmt->close();


                    if ($conflict_result->num_rows > 0) {

                        $error =
                            "The selected schedule conflicts with an existing funeral service. Please choose another date or time.";

                    } else {


                        /* =========================
                           CALCULATE TOTAL COST
                        ========================= */

                        $total_cost =
                            (float) $package["price"];

                        $optional_items = [];


                        foreach (
                            $selected_optional_services
                            as $optional_service_id
                        ) {

                            $optional_service_id =
                                (int) $optional_service_id;


                            if ($optional_service_id <= 0) {
                                continue;
                            }


                            /* =========================
                               VERIFY OPTIONAL SERVICE
                            ========================= */

                            $sql = "SELECT
                                        optional_service_id,
                                        service_name,
                                        price
                                    FROM optional_services
                                    WHERE optional_service_id = ?
                                    AND status = 'Available'
                                    LIMIT 1";

                            $stmt =
                                $conn->prepare($sql);

                            $stmt->bind_param(
                                "i",
                                $optional_service_id
                            );

                            $stmt->execute();

                            $optional_check =
                                $stmt->get_result();

                            $optional =
                                $optional_check->fetch_assoc();

                            $stmt->close();


                            if ($optional) {

                                $total_cost +=
                                    (float) $optional["price"];


                                $optional_items[] = [
                                    "id" =>
                                        $optional[
                                            "optional_service_id"
                                        ],

                                    "price" =>
                                        $optional["price"]
                                ];
                            }
                        }


                        /* =========================
                           INSERT FUNERAL SERVICE
                        ========================= */

                        $conn->begin_transaction();


                        try {

                            $status =
                                "Scheduled";


                            $sql = "INSERT INTO funeral_services
                                    (
                                        client_id,
                                        deceased_id,
                                        package_id,
                                        service_start,
                                        service_end,
                                        location,
                                        total_cost,
                                        status,
                                        notes
                                    )
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";


                            $stmt =
                                $conn->prepare($sql);


                            $stmt->bind_param(
                                "iiisssdss",
                                $client_id,
                                $deceased_id,
                                $package_id,
                                $service_start,
                                $service_end,
                                $location,
                                $total_cost,
                                $status,
                                $notes
                            );


                            if (!$stmt->execute()) {

                                throw new Exception(
                                    "Unable to create funeral service."
                                );
                            }


                            $funeral_service_id =
                                $stmt->insert_id;


                            $stmt->close();


                            /* =========================
                               INSERT OPTIONAL SERVICES
                            ========================= */

                            foreach (
                                $optional_items as $item
                            ) {

                                $optional_service_id =
                                    $item["id"];

                                $price =
                                    $item["price"];

                                $quantity =
                                    1;


                                $sql =
                                    "INSERT INTO service_optional_services
                                    (
                                        funeral_service_id,
                                        optional_service_id,
                                        quantity,
                                        price
                                    )
                                    VALUES (?, ?, ?, ?)";


                                $stmt =
                                    $conn->prepare($sql);


                                $stmt->bind_param(
                                    "iiid",
                                    $funeral_service_id,
                                    $optional_service_id,
                                    $quantity,
                                    $price
                                );


                                if (!$stmt->execute()) {

                                    throw new Exception(
                                        "Unable to save optional services."
                                    );
                                }


                                $stmt->close();
                            }


                            /* =========================
                               COMPLETE TRANSACTION
                            ========================= */

                            $conn->commit();


                            header(
                                "Location: my_services.php?requested=1"
                            );

                            exit();


                        } catch (Exception $e) {

                            $conn->rollback();

                            $error =
                                $e->getMessage();
                        }
                    }
                }
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
        EternaCare - Request Funeral Service
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
                    SERVICES
                </span>

                <h1>
                    Request Funeral Service
                </h1>

                <p>
                    Submit a funeral service request by selecting
                    the deceased, funeral package, optional services,
                    schedule, and location.
                </p>

            </div>

        </section>


        <!-- ERROR MESSAGE -->

        <?php if ($error !== ""): ?>

            <div class="client-alert client-alert-error">

                <strong>
                    Request Could Not Be Submitted
                </strong>

                <span>
                    <?php
                    echo htmlspecialchars($error);
                    ?>
                </span>

            </div>

        <?php endif; ?>


        <?php if (count($deceased_records) === 0): ?>


            <!-- NO DECEASED -->

            <section class="client-panel">

                <div class="client-section-title">

                    <span>
                        REQUIRED INFORMATION
                    </span>

                    <h2>
                        No Deceased Record
                    </h2>

                    <p>
                        You need to add deceased information
                        before requesting a funeral service.
                    </p>

                </div>


                <div class="client-empty-box">

                    <p>
                        No deceased information is currently
                        associated with your account.
                    </p>

                    <a
                        href="deceased.php"
                        class="client-action-btn"
                    >
                        Add Deceased Information
                    </a>

                </div>

            </section>


        <?php elseif (count($packages) === 0): ?>


            <!-- NO PACKAGES -->

            <section class="client-panel">

                <div class="client-section-title">

                    <span>
                        FUNERAL PACKAGES
                    </span>

                    <h2>
                        No Funeral Packages Available
                    </h2>

                    <p>
                        There are currently no available
                        funeral packages.
                    </p>

                </div>


                <div class="client-empty-box">

                    <p>
                        Please check again later or contact
                        the EternaCare staff for assistance.
                    </p>

                </div>

            </section>


        <?php else: ?>


            <!-- SERVICE REQUEST FORM -->

            <section class="client-panel">


                <div class="client-section-title">

                    <span>
                        SERVICE REQUEST
                    </span>

                    <h2>
                        Funeral Service Details
                    </h2>

                    <p>
                        Provide the information below to
                        schedule your requested funeral service.
                    </p>

                </div>


                <form
                    method="POST"
                    class="client-form"
                    id="serviceRequestForm"
                >


                    <div class="client-form-grid">


                        <!-- DECEASED -->

                        <div class="client-form-group">

                            <label for="deceased_id">
                                Deceased *
                            </label>

                            <select
                                name="deceased_id"
                                id="deceased_id"
                                required
                            >

                                <option value="">
                                    Select Deceased
                                </option>

                                <?php foreach (
                                    $deceased_records
                                    as $record
                                ): ?>

                                    <option
                                        value="<?php
                                            echo $record["deceased_id"];
                                        ?>"
                                        <?php

                                        echo (
                                            isset(
                                                $_POST["deceased_id"]
                                            ) &&
                                            $_POST["deceased_id"] ==
                                            $record["deceased_id"]
                                        )
                                            ? "selected"
                                            : "";

                                        ?>
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $record["full_name"]
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- FUNERAL PACKAGE -->

                        <div class="client-form-group">

                            <label for="package_id">
                                Funeral Package *
                            </label>

                            <select
                                name="package_id"
                                id="package_id"
                                required
                            >

                                <option value="">
                                    Select Funeral Package
                                </option>

                                <?php foreach (
                                    $packages
                                    as $package
                                ): ?>

                                    <option
                                        value="<?php
                                            echo $package["package_id"];
                                        ?>"
                                        <?php

                                        echo (
                                            isset(
                                                $_POST["package_id"]
                                            ) &&
                                            $_POST["package_id"] ==
                                            $package["package_id"]
                                        )
                                            ? "selected"
                                            : "";

                                        ?>
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $package["package_name"]
                                        );
                                        ?>

                                        -
                                        ₱<?php
                                        echo number_format(
                                            $package["price"],
                                            2
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- START DATE -->

                        <div class="client-form-group">

                            <label for="service_start">
                                Service Start *
                            </label>

                            <input
                                type="datetime-local"
                                name="service_start"
                                id="service_start"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["service_start"]
                                        ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- END DATE -->

                        <div class="client-form-group">

                            <label for="service_end">
                                Service End *
                            </label>

                            <input
                                type="datetime-local"
                                name="service_end"
                                id="service_end"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["service_end"]
                                        ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- LOCATION -->

                        <div
                            class="client-form-group client-form-full"
                        >

                            <label for="location">
                                Service Location *
                            </label>

                            <input
                                type="text"
                                name="location"
                                id="location"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["location"]
                                        ?? ""
                                    );
                                ?>"
                                placeholder="Enter service location"
                                required
                            >

                        </div>


                        <!-- OPTIONAL SERVICES -->

                        <div
                            class="client-form-group client-form-full"
                        >

                            <label>
                                Optional Services
                            </label>


                            <?php if (
                                count($optional_services) === 0
                            ): ?>

                                <div class="client-empty-box">

                                    <p>
                                        No optional services are
                                        currently available.
                                    </p>

                                </div>

                            <?php else: ?>


                                <div class="client-option-list">

                                    <?php foreach (
                                        $optional_services
                                        as $service
                                    ): ?>


                                        <label
                                            class="client-option-item"
                                        >

                                            <input
                                                type="checkbox"
                                                name="optional_services[]"
                                                value="<?php
                                                    echo $service[
                                                        "optional_service_id"
                                                    ];
                                                ?>"
                                                <?php

                                                if (
                                                    isset(
                                                        $_POST[
                                                            "optional_services"
                                                        ]
                                                    ) &&
                                                    is_array(
                                                        $_POST[
                                                            "optional_services"
                                                        ]
                                                    ) &&
                                                    in_array(
                                                        $service[
                                                            "optional_service_id"
                                                        ],
                                                        $_POST[
                                                            "optional_services"
                                                        ]
                                                    )
                                                ) {

                                                    echo "checked";
                                                }

                                                ?>
                                            >

                                            <span>

                                                <strong>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $service[
                                                            "service_name"
                                                        ]
                                                    );
                                                    ?>

                                                </strong>

                                                <small>

                                                    ₱<?php
                                                    echo number_format(
                                                        $service["price"],
                                                        2
                                                    );
                                                    ?>

                                                </small>

                                            </span>

                                        </label>


                                    <?php endforeach; ?>

                                </div>


                            <?php endif; ?>

                        </div>


                        <!-- NOTES -->

                        <div
                            class="client-form-group client-form-full"
                        >

                            <label for="notes">
                                Additional Notes
                            </label>

                            <textarea
                                name="notes"
                                id="notes"
                                rows="5"
                                placeholder="Enter any additional information"
                            ><?php
                            echo htmlspecialchars(
                                $_POST["notes"] ?? ""
                            );
                            ?></textarea>

                        </div>


                    </div>


                    <!-- SUBMIT -->

                    <div class="client-actions">

                        <button
                            type="submit"
                            class="client-action-btn"
                        >
                            Submit Funeral Service Request
                        </button>

                        <a
                            href="dashboard.php"
                            class="client-action-btn secondary"
                        >
                            Back to Dashboard
                        </a>

                    </div>


                </form>


            </section>


            <!-- BEFORE YOU SUBMIT -->

            <section class="client-panel">


                <div class="client-section-title">

                    <span>
                        IMPORTANT
                    </span>

                    <h2>
                        Before You Submit
                    </h2>

                    <p>
                        Please review the following reminders
                        before submitting your request.
                    </p>

                </div>


                <div class="client-information">


                    <div class="client-information-item">

                        <span class="client-information-number">
                            01
                        </span>

                        <p>
                            Make sure the correct deceased
                            person is selected.
                        </p>

                    </div>


                    <div class="client-information-item">

                        <span class="client-information-number">
                            02
                        </span>

                        <p>
                            Select an available funeral package.
                        </p>

                    </div>


                    <div class="client-information-item">

                        <span class="client-information-number">
                            03
                        </span>

                        <p>
                            Optional services can be selected
                            if needed.
                        </p>

                    </div>


                    <div class="client-information-item">

                        <span class="client-information-number">
                            04
                        </span>

                        <p>
                            The service end date and time must
                            be later than the start date and time.
                        </p>

                    </div>


                    <div class="client-information-item">

                        <span class="client-information-number">
                            05
                        </span>

                        <p>
                            The system will check for schedule
                            conflicts.
                        </p>

                    </div>


                    <div class="client-information-item">

                        <span class="client-information-number">
                            06
                        </span>

                        <p>
                            The estimated total cost will be
                            calculated automatically.
                        </p>

                    </div>


                </div>


            </section>


        <?php endif; ?>


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


    <!-- DATE VALIDATION -->

    <script>

    document.addEventListener(
        "DOMContentLoaded",
        function () {

            const startInput =
                document.getElementById("service_start");

            const endInput =
                document.getElementById("service_end");

            const form =
                document.getElementById("serviceRequestForm");


            if (
                !startInput ||
                !endInput ||
                !form
            ) {
                return;
            }


            function updateEndMinimum() {

                if (startInput.value) {

                    endInput.min =
                        startInput.value;
                }
            }


            startInput.addEventListener(
                "change",
                updateEndMinimum
            );


            startInput.addEventListener(
                "input",
                updateEndMinimum
            );


            endInput.addEventListener(
                "change",
                function () {

                    if (
                        startInput.value &&
                        endInput.value &&
                        endInput.value <=
                        startInput.value
                    ) {

                        alert(
                            "The service end date and time must be later than the service start date and time."
                        );

                        endInput.value = "";
                    }

                }
            );


            form.addEventListener(
                "submit",
                function (event) {

                    if (
                        startInput.value &&
                        endInput.value &&
                        endInput.value <=
                        startInput.value
                    ) {

                        event.preventDefault();

                        alert(
                            "Please select an end date and time later than the service start date and time."
                        );

                        endInput.focus();
                    }

                }
            );


            updateEndMinimum();

        }
    );

    </script>


</body>

</html>