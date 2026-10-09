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
   DELETE DECEASED
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_deceased"])
) {

    $deceased_id = (int) $_POST["deceased_id"];

    if ($deceased_id <= 0) {

        $error = "Invalid deceased record.";

    } else {

        /*
         * Check if the deceased record belongs
         * to the logged-in client.
         */

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

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $stmt->close();


            /*
             * Delete only the client's own record.
             */

            $sql = "DELETE FROM deceased
                    WHERE deceased_id = ?
                    AND client_id = ?";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ii",
                $deceased_id,
                $client_id
            );

            if ($stmt->execute()) {

                $message =
                    "Deceased information deleted successfully.";

            } else {

                $error =
                    "Unable to delete deceased information.";

            }

            $stmt->close();

        } else {

            $stmt->close();

            $error =
                "You are not allowed to delete this deceased record.";
        }
    }
}


/* =========================
   ADD DECEASED
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_deceased"])
) {

    $full_name = trim(
        $_POST["full_name"] ?? ""
    );

    $date_of_birth =
        $_POST["date_of_birth"] ?? "";

    $date_of_death =
        $_POST["date_of_death"] ?? "";

    $age =
        trim($_POST["age"] ?? "");

    $address =
        trim($_POST["address"] ?? "");

    $cause_of_death =
        trim($_POST["cause_of_death"] ?? "");


    if (
        $full_name === ""
        || $date_of_death === ""
    ) {

        $error =
            "Please fill in the required fields.";

    } else {

        $sql = "INSERT INTO deceased
                (
                    client_id,
                    full_name,
                    date_of_birth,
                    date_of_death,
                    age,
                    address,
                    cause_of_death
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $age_value =
            $age === "" ? null : (int) $age;

        $stmt->bind_param(
            "isssiss",
            $client_id,
            $full_name,
            $date_of_birth,
            $date_of_death,
            $age_value,
            $address,
            $cause_of_death
        );

        if ($stmt->execute()) {

            $message =
                "Deceased information added successfully.";

        } else {

            $error =
                "Unable to add deceased information.";

        }

        $stmt->close();
    }
}


/* =========================
   EDIT DECEASED
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["edit_deceased"])
) {

    $deceased_id =
        (int) ($_POST["deceased_id"] ?? 0);

    $full_name =
        trim($_POST["full_name"] ?? "");

    $date_of_birth =
        $_POST["date_of_birth"] ?? "";

    $date_of_death =
        $_POST["date_of_death"] ?? "";

    $age =
        trim($_POST["age"] ?? "");

    $address =
        trim($_POST["address"] ?? "");

    $cause_of_death =
        trim($_POST["cause_of_death"] ?? "");


    if (
        $deceased_id <= 0
        || $full_name === ""
        || $date_of_death === ""
    ) {

        $error =
            "Please fill in the required fields.";

    } else {

        $sql = "UPDATE deceased
                SET full_name = ?,
                    date_of_birth = ?,
                    date_of_death = ?,
                    age = ?,
                    address = ?,
                    cause_of_death = ?
                WHERE deceased_id = ?
                AND client_id = ?";

        $stmt = $conn->prepare($sql);

        $age_value =
            $age === "" ? null : (int) $age;

        $stmt->bind_param(
            "sssissii",
            $full_name,
            $date_of_birth,
            $date_of_death,
            $age_value,
            $address,
            $cause_of_death,
            $deceased_id,
            $client_id
        );

        if ($stmt->execute()) {

            $message =
                "Deceased information updated successfully.";

        } else {

            $error =
                "Unable to update deceased information.";

        }

        $stmt->close();
    }
}


/* =========================
   GET CLIENT'S DECEASED
========================= */

$sql = "SELECT *
        FROM deceased
        WHERE client_id = ?
        ORDER BY deceased_id DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $client_id
);

$stmt->execute();

$result = $stmt->get_result();

$deceased_records = [];

while ($row = $result->fetch_assoc()) {

    $deceased_records[] = $row;
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
        EternaCare - Deceased Information
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
                Deceased Information
            </h2>

            <p>
                Manage the information of your deceased family member.
            </p>

        </section>


        <!-- =========================
             SUCCESS MESSAGE
             ========================= -->

        <?php if ($message !== ""): ?>

            <div class="client-alert client-alert-success">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             ERROR MESSAGE
             ========================= -->

        <?php if ($error !== ""): ?>

            <div class="client-alert client-alert-error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             ADD DECEASED
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        FAMILY RECORDS
                    </span>

                    <h2>
                        Add Deceased Information
                    </h2>

                </div>

            </div>


            <form
                method="POST"
                class="client-form"
            >


                <div class="client-form-grid">


                    <!-- Full Name -->

                    <div class="client-form-group">

                        <label>
                            Full Name *
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            placeholder="Enter full name"
                            required
                        >

                    </div>


                    <!-- Date of Birth -->

                    <div class="client-form-group">

                        <label>
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            name="date_of_birth"
                        >

                    </div>


                    <!-- Date of Death -->

                    <div class="client-form-group">

                        <label>
                            Date of Death *
                        </label>

                        <input
                            type="date"
                            name="date_of_death"
                            required
                        >

                    </div>


                    <!-- Age -->

                    <div class="client-form-group">

                        <label>
                            Age
                        </label>

                        <input
                            type="number"
                            name="age"
                            min="0"
                            placeholder="Enter age"
                        >

                    </div>


                </div>


                <!-- Address -->

                <div class="client-form-group">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                        rows="3"
                        placeholder="Enter address"
                    ></textarea>

                </div>


                <!-- Cause of Death -->

                <div class="client-form-group">

                    <label>
                        Cause of Death
                    </label>

                    <textarea
                        name="cause_of_death"
                        rows="3"
                        placeholder="Enter cause of death"
                    ></textarea>

                </div>


                <button
                    type="submit"
                    name="add_deceased"
                    class="client-primary-btn client-form-button"
                >
                    ADD DECEASED INFORMATION
                </button>


            </form>

        </section>


        <!-- =========================
             DECEASED RECORDS
             ========================= -->

        <section class="client-panel">


            <div class="client-panel-heading">

                <div>

                    <span>
                        SAVED RECORDS
                    </span>

                    <h2>
                        My Deceased Records
                    </h2>

                </div>

                <span>
                    <?php
                    echo count($deceased_records);
                    ?>
                    RECORD(S)
                </span>

            </div>


            <?php if (count($deceased_records) === 0): ?>


                <div class="client-information">

                    <div class="information-row">

                        <span>
                            RECORD STATUS
                        </span>

                        <strong>
                            No Records
                        </strong>

                    </div>

                    <p class="empty-message">
                        No deceased information has been added yet.
                    </p>

                </div>


            <?php else: ?>


                <div class="client-record-grid">


                    <?php foreach (
                        $deceased_records as $record
                    ): ?>


                        <article class="client-record-card">


                            <!-- Record Header -->

                            <div class="client-record-header">

                                <div>

                                    <span>
                                        DECEASED RECORD
                                    </span>

                                    <h3>

                                        <?php
                                        echo htmlspecialchars(
                                            $record["full_name"]
                                        );
                                        ?>

                                    </h3>

                                </div>

                            </div>


                            <!-- Record Information -->

                            <div class="client-information">


                                <div class="information-row">

                                    <span>
                                        DATE OF BIRTH
                                    </span>

                                    <strong>

                                        <?php

                                        echo !empty(
                                            $record["date_of_birth"]
                                        )
                                            ? htmlspecialchars(
                                                $record[
                                                    "date_of_birth"
                                                ]
                                            )
                                            : "Not provided";

                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        DATE OF DEATH
                                    </span>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $record[
                                                "date_of_death"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        AGE
                                    </span>

                                    <strong>

                                        <?php

                                        echo $record["age"] !== null
                                            && $record["age"] !== ""
                                            ? htmlspecialchars(
                                                $record["age"]
                                            )
                                            : "Not provided";

                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        ADDRESS
                                    </span>

                                    <strong>

                                        <?php

                                        echo !empty(
                                            $record["address"]
                                        )
                                            ? htmlspecialchars(
                                                $record["address"]
                                            )
                                            : "Not provided";

                                        ?>

                                    </strong>

                                </div>


                                <div class="information-row">

                                    <span>
                                        CAUSE OF DEATH
                                    </span>

                                    <strong>

                                        <?php

                                        echo !empty(
                                            $record["cause_of_death"]
                                        )
                                            ? htmlspecialchars(
                                                $record[
                                                    "cause_of_death"
                                                ]
                                            )
                                            : "Not provided";

                                        ?>

                                    </strong>

                                </div>


                            </div>


                            <!-- =========================
                                 EDIT INFORMATION
                                 ========================= -->

                            <div class="client-record-edit">

                                <div class="client-section-title">

                                    <span>
                                        MANAGE RECORD
                                    </span>

                                    <h3>
                                        Edit Information
                                    </h3>

                                </div>


                                <form method="POST">


                                    <input
                                        type="hidden"
                                        name="deceased_id"
                                        value="<?php
                                            echo (int)
                                                $record[
                                                    "deceased_id"
                                                ];
                                        ?>"
                                    >


                                    <!-- Full Name -->

                                    <div class="client-form-group">

                                        <label>
                                            Full Name *
                                        </label>

                                        <input
                                            type="text"
                                            name="full_name"
                                            value="<?php
                                                echo htmlspecialchars(
                                                    $record[
                                                        "full_name"
                                                    ]
                                                );
                                            ?>"
                                            required
                                        >

                                    </div>


                                    <!-- Date of Birth -->

                                    <div class="client-form-group">

                                        <label>
                                            Date of Birth
                                        </label>

                                        <input
                                            type="date"
                                            name="date_of_birth"
                                            value="<?php
                                                echo htmlspecialchars(
                                                    $record[
                                                        "date_of_birth"
                                                    ] ?? ""
                                                );
                                            ?>"
                                        >

                                    </div>


                                    <!-- Date of Death -->

                                    <div class="client-form-group">

                                        <label>
                                            Date of Death *
                                        </label>

                                        <input
                                            type="date"
                                            name="date_of_death"
                                            value="<?php
                                                echo htmlspecialchars(
                                                    $record[
                                                        "date_of_death"
                                                    ]
                                                );
                                            ?>"
                                            required
                                        >

                                    </div>


                                    <!-- Age -->

                                    <div class="client-form-group">

                                        <label>
                                            Age
                                        </label>

                                        <input
                                            type="number"
                                            name="age"
                                            min="0"
                                            value="<?php
                                                echo htmlspecialchars(
                                                    $record[
                                                        "age"
                                                    ] ?? ""
                                                );
                                            ?>"
                                        >

                                    </div>


                                    <!-- Address -->

                                    <div class="client-form-group">

                                        <label>
                                            Address
                                        </label>

                                        <textarea
                                            name="address"
                                            rows="3"
                                        ><?php
                                            echo htmlspecialchars(
                                                $record[
                                                    "address"
                                                ] ?? ""
                                            );
                                        ?></textarea>

                                    </div>


                                    <!-- Cause of Death -->

                                    <div class="client-form-group">

                                        <label>
                                            Cause of Death
                                        </label>

                                        <textarea
                                            name="cause_of_death"
                                            rows="3"
                                        ><?php
                                            echo htmlspecialchars(
                                                $record[
                                                    "cause_of_death"
                                                ] ?? ""
                                            );
                                        ?></textarea>

                                    </div>


                                    <!-- Update Button -->

                                    <button
                                        type="submit"
                                        name="edit_deceased"
                                        class="client-primary-btn client-form-button"
                                    >
                                        UPDATE INFORMATION
                                    </button>


                                </form>


                                <!-- =========================
                                     DELETE RECORD
                                     ========================= -->

                                <form
                                    method="POST"
                                    class="client-delete-form"
                                    onsubmit="return confirm('Are you sure you want to delete this deceased record? This action cannot be undone.');"
                                >

                                    <input
                                        type="hidden"
                                        name="deceased_id"
                                        value="<?php
                                            echo (int)
                                                $record[
                                                    "deceased_id"
                                                ];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_deceased"
                                        class="client-delete-btn"
                                    >
                                        DELETE RECORD
                                    </button>

                                </form>


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
                        Continue Managing Your Account
                    </h2>

                </div>

            </div>


            <div class="client-actions">


                <a
                    href="request_service.php"
                    class="client-action primary"
                >

                    <strong>
                        Request Funeral Service
                    </strong>

                    <span>
                        Start a new funeral service request.
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