<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Client");


// Get logged-in user ID
$user_id = $_SESSION["user_id"];


// Get client information
$sql = "SELECT
            u.client_id,
            c.full_name,
            c.contact_number,
            c.email,
            c.address,
            c.relationship_to_deceased
        FROM users u
        INNER JOIN clients c
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


// Make sure client information exists
if (!$client) {

    die("Client information not found.");

}


// Update profile
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name =
        trim($_POST["full_name"]);

    $contact_number =
        trim($_POST["contact_number"]);

    $email =
        trim($_POST["email"]);

    $address =
        trim($_POST["address"]);

    $relationship =
        trim($_POST["relationship_to_deceased"]);


    // Validate required fields
    if (
        $full_name === "" ||
        $contact_number === "" ||
        $email === "" ||
        $address === "" ||
        $relationship === ""
    ) {

        $error =
            "Please fill in all fields.";

    }

    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error =
            "Please enter a valid email address.";

    }

    else {

        // Check if another client is using the email
        $check_sql = "SELECT client_id
                      FROM clients
                      WHERE email = ?
                      AND client_id != ?";

        $check_stmt =
            $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "si",
            $email,
            $client["client_id"]
        );

        $check_stmt->execute();

        $check_result =
            $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $error =
                "That email address is already being used.";

            $check_stmt->close();

        } else {

            $check_stmt->close();


            // Update client information
            $update_sql = "UPDATE clients
                           SET
                               full_name = ?,
                               contact_number = ?,
                               email = ?,
                               address = ?,
                               relationship_to_deceased = ?
                           WHERE client_id = ?";

            $update_stmt =
                $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "sssssi",
                $full_name,
                $contact_number,
                $email,
                $address,
                $relationship,
                $client["client_id"]
            );


            if ($update_stmt->execute()) {

                $update_stmt->close();


                // Update session name
                $_SESSION["full_name"] =
                    $full_name;


                header(
                    "Location: profile.php?updated=1"
                );

                exit();

            } else {

                $error =
                    "Error updating profile: "
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
        EternaCare - My Profile
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body class="client-page">


    <?php include "includes/client_header.php"; ?>


    <main class="client-dashboard">


        <!-- Page Header -->

        <section class="client-welcome">

            <div>

                <span class="client-eyebrow">
                    MY ACCOUNT
                </span>

                <h1>
                    My Profile
                </h1>

                <p>
                    View and update your personal information.
                </p>

            </div>

        </section>


        <!-- Profile Content -->

        <section class="client-panel">


            <div class="client-section-title">

                <span>
                    PERSONAL INFORMATION
                </span>

                <h2>
                    Your Profile
                </h2>

                <p>
                    Keep your contact information updated
                    so EternaCare can properly assist you.
                </p>

            </div>


            <!-- Success Message -->

            <?php if (isset($_GET["updated"])): ?>

                <div class="client-alert client-alert-success">

                    <strong>
                        Profile updated successfully!
                    </strong>

                    <span>
                        Your personal information has been saved.
                    </span>

                </div>

            <?php endif; ?>


            <!-- Error Message -->

            <?php if (isset($error)): ?>

                <div class="client-alert client-alert-error">

                    <strong>
                        Unable to update profile
                    </strong>

                    <span>
                        <?php
                        echo htmlspecialchars($error);
                        ?>
                    </span>

                </div>

            <?php endif; ?>


            <!-- Profile Form -->

            <form
                method="POST"
                action=""
                class="client-form"
            >


                <div class="client-form-grid">


                    <!-- Full Name -->

                    <div class="client-form-group">

                        <label for="full_name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="<?php
                                echo htmlspecialchars(
                                    $client["full_name"]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- Contact Number -->

                    <div class="client-form-group">

                        <label for="contact_number">
                            Contact Number
                        </label>

                        <input
                            type="text"
                            id="contact_number"
                            name="contact_number"
                            value="<?php
                                echo htmlspecialchars(
                                    $client["contact_number"]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="client-form-group">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php
                                echo htmlspecialchars(
                                    $client["email"]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- Relationship -->

                    <div class="client-form-group">

                        <label for="relationship_to_deceased">
                            Relationship to Deceased
                        </label>

                        <input
                            type="text"
                            id="relationship_to_deceased"
                            name="relationship_to_deceased"
                            value="<?php
                                echo htmlspecialchars(
                                    $client[
                                        "relationship_to_deceased"
                                    ]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <!-- Address -->

                    <div class="client-form-group client-form-full">

                        <label for="address">
                            Address
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            rows="5"
                            required
                        ><?php
                            echo htmlspecialchars(
                                $client["address"]
                            );
                        ?></textarea>

                    </div>


                </div>


                <!-- Form Actions -->

                <div class="client-actions">


                    <button
                        type="submit"
                        class="client-action-btn"
                    >
                        Update Profile
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


        <!-- Account Information -->

        <section class="client-content-grid">


            <div class="client-panel">

                <div class="client-section-title">

                    <span>
                        ACCOUNT
                    </span>

                    <h2>
                        Profile Information
                    </h2>

                </div>


                <div class="client-record-grid">


                    <div class="client-record-card">

                        <span>
                            CLIENT ID
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $client["client_id"]
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="client-record-card">

                        <span>
                            EMAIL
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

            </div>


            <div class="client-panel">

                <div class="client-section-title">

                    <span>
                        ETERNACARE
                    </span>

                    <h2>
                        Keep Your Information Updated
                    </h2>

                </div>

                <p class="client-service-notes">

                    Make sure your contact information is
                    accurate so the funeral service team can
                    contact you regarding service requests,
                    schedules, payments, and other important
                    updates.

                </p>

            </div>


        </section>


    </main>


    <!-- Footer -->

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


</body>

</html>