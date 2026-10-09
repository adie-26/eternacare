<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Client");

$sql = "SELECT optional_service_id, service_name, description, price, status
        FROM optional_services
        WHERE status = 'Available'
        ORDER BY service_name ASC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EternaCare - Optional Services</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body class="client-page">

<?php include "includes/client_header.php"; ?>


<!-- =========================================================
     OPTIONAL SERVICES PAGE
     ========================================================= -->

<main class="client-dashboard">

    <!-- Page Header -->

    <section class="client-welcome">

        <div>

            <span class="client-section-label">
                ETERNACARE SERVICES
            </span>

            <h1>
                Optional Services
            </h1>

            <p>
                Explore additional services that may be added
                to your funeral service arrangement.
            </p>

        </div>

    </section>


    <!-- Available Services -->

    <section class="client-panel">

        <div class="client-panel-header">

            <div>

                <span class="client-section-label">
                    AVAILABLE OPTIONS
                </span>

                <h2>
                    Optional Services
                </h2>

            </div>

        </div>


        <?php if ($result->num_rows === 0): ?>

            <div class="client-empty-box">

                <h3>
                    No Optional Services Available
                </h3>

                <p>
                    There are currently no optional services available.
                    Please check again later.
                </p>

            </div>

        <?php else: ?>

            <div class="client-table-wrapper">

                <table class="client-table">

                    <thead>

                        <tr>

                            <th>
                                Service
                            </th>

                            <th>
                                Description
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($service = $result->fetch_assoc()): ?>

                            <tr>

                                <td>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $service["service_name"]
                                        );
                                        ?>
                                    </strong>

                                </td>


                                <td>

                                    <?php

                                    echo !empty($service["description"])

                                        ? nl2br(
                                            htmlspecialchars(
                                                $service["description"]
                                            )
                                        )

                                        : "No description provided.";

                                    ?>

                                </td>


                                <td>

                                    <strong>
                                        ₱<?php
                                        echo number_format(
                                            $service["price"],
                                            2
                                        );
                                        ?>
                                    </strong>

                                </td>


                                <td>

                                    <span class="client-status status-approved">
                                        <?php
                                        echo htmlspecialchars(
                                            $service["status"]
                                        );
                                        ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>


    <!-- Customize Service -->

    <section class="client-panel">

        <div class="client-information">

            <span class="client-section-label">
                CUSTOMIZE YOUR SERVICE
            </span>

            <h2>
                Create a Personalized Funeral Service
            </h2>

            <p>
                You can select available optional services when
                requesting a funeral service. Choose the services
                that best fit your needs and preferences.
            </p>

        </div>


        <div class="client-actions">

            <a href="request_service.php" class="client-action-btn">
                Request Funeral Service
            </a>

            <a href="dashboard.php" class="client-action-btn secondary">
                Back to Dashboard
            </a>

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
        Providing organized and compassionate funeral service management.
    </p>

</footer>


</body>

</html>