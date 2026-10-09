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


/* =========================
   GET FUNERAL SERVICES
========================= */

$sql = "SELECT
            fs.funeral_service_id,
            fs.service_start,
            fs.service_end,
            fs.location,
            fs.total_cost,
            fs.status,
            fs.notes,
            d.full_name AS deceased_name,
            p.package_name
        FROM funeral_services fs
        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id
        INNER JOIN packages p
            ON fs.package_id = p.package_id
        WHERE fs.client_id = ?
        ORDER BY fs.service_start DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();

$result = $stmt->get_result();

$services = [];

while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

$stmt->close();


/* =========================
   GET OPTIONAL SERVICES
========================= */

$optional_services = [];

foreach ($services as $service) {

    $service_id = $service["funeral_service_id"];

    $sql = "SELECT
                os.service_name,
                sos.quantity,
                sos.price
            FROM service_optional_services sos
            INNER JOIN optional_services os
                ON sos.optional_service_id = os.optional_service_id
            WHERE sos.funeral_service_id = ?
            ORDER BY os.service_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();

    $optional_result = $stmt->get_result();

    $items = [];

    while ($item = $optional_result->fetch_assoc()) {
        $items[] = $item;
    }

    $optional_services[$service_id] = $items;

    $stmt->close();
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
        EternaCare - My Funeral Services
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
                MY SERVICES
            </span>

            <h2>
                My Funeral Services
            </h2>

            <p>
                View your submitted funeral service requests
                and their current status.
            </p>

        </section>


        <!-- =========================
             REQUEST SUCCESS MESSAGE
             ========================= -->

        <?php if (isset($_GET["requested"])): ?>

            <div class="client-alert client-alert-success">

                Your funeral service request was
                submitted successfully.

            </div>

        <?php endif; ?>


        <!-- =========================
             NO SERVICES
             ========================= -->

        <?php if (count($services) === 0): ?>


            <section class="client-panel">

                <div class="client-panel-heading">

                    <div>

                        <span>
                            SERVICE RECORDS
                        </span>

                        <h2>
                            No Funeral Services
                        </h2>

                    </div>

                </div>


                <div class="client-information">

                    <div class="information-row">

                        <span>
                            SERVICE STATUS
                        </span>

                        <strong>
                            No Requests Yet
                        </strong>

                    </div>

                    <p class="empty-message">

                        You have not submitted any
                        funeral service requests yet.

                    </p>

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

                </div>

            </section>


        <?php else: ?>


            <!-- =========================
                 SERVICE LIST
                 ========================= -->

            <?php foreach ($services as $service): ?>

                <section class="client-panel service-record-panel">


                    <!-- =========================
                         SERVICE HEADER
                         ========================= -->

                    <div class="client-panel-heading">

                        <div>

                            <span>
                                FUNERAL SERVICE
                            </span>

                            <h2>

                                Service #<?php
                                echo htmlspecialchars(
                                    $service[
                                        "funeral_service_id"
                                    ]
                                );
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


                    <!-- =========================
                         SERVICE INFORMATION
                         ========================= -->

                    <div class="client-information">


                        <!-- Deceased -->

                        <div class="information-row">

                            <span>
                                DECEASED
                            </span>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $service[
                                        "deceased_name"
                                    ]
                                );
                                ?>

                            </strong>

                        </div>


                        <!-- Funeral Package -->

                        <div class="information-row">

                            <span>
                                FUNERAL PACKAGE
                            </span>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $service[
                                        "package_name"
                                    ]
                                );
                                ?>

                            </strong>

                        </div>


                        <!-- Service Start -->

                        <div class="information-row">

                            <span>
                                SERVICE START
                            </span>

                            <strong>

                                <?php

                                if (!empty(
                                    $service["service_start"]
                                )) {

                                    echo date(
                                        "F d, Y h:i A",
                                        strtotime(
                                            $service[
                                                "service_start"
                                            ]
                                        )
                                    );

                                } else {

                                    echo "Not scheduled";

                                }

                                ?>

                            </strong>

                        </div>


                        <!-- Service End -->

                        <div class="information-row">

                            <span>
                                SERVICE END
                            </span>

                            <strong>

                                <?php

                                if (!empty(
                                    $service["service_end"]
                                )) {

                                    echo date(
                                        "F d, Y h:i A",
                                        strtotime(
                                            $service[
                                                "service_end"
                                            ]
                                        )
                                    );

                                } else {

                                    echo "Not scheduled";

                                }

                                ?>

                            </strong>

                        </div>


                        <!-- Location -->

                        <div class="information-row">

                            <span>
                                LOCATION
                            </span>

                            <strong>

                                <?php
                                echo !empty(
                                    $service["location"]
                                )
                                    ? htmlspecialchars(
                                        $service[
                                            "location"
                                        ]
                                    )
                                    : "Not provided";
                                ?>

                            </strong>

                        </div>


                        <!-- Total Cost -->

                        <div class="information-row">

                            <span>
                                TOTAL COST
                            </span>

                            <strong>

                                ₱<?php
                                echo number_format(
                                    $service["total_cost"],
                                    2
                                );
                                ?>

                            </strong>

                        </div>


                    </div>


                    <!-- =========================
                         NOTES
                         ========================= -->

                    <?php if (!empty($service["notes"])): ?>

                        <div class="client-service-notes">

                            <span>
                                SERVICE NOTES
                            </span>

                            <p>

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $service["notes"]
                                    )
                                );
                                ?>

                            </p>

                        </div>

                    <?php endif; ?>


                    <!-- =========================
                         OPTIONAL SERVICES
                         ========================= -->

                    <div class="client-service-section">

                        <div class="client-section-title">

                            <span>
                                ADDITIONAL SERVICES
                            </span>

                            <h3>
                                Optional Services
                            </h3>

                        </div>


                        <?php

                        $service_id =
                            $service[
                                "funeral_service_id"
                            ];

                        $items =
                            $optional_services[
                                $service_id
                            ] ?? [];

                        ?>


                        <?php if (count($items) === 0): ?>


                            <div class="client-empty-box">

                                No optional services
                                selected for this service.

                            </div>


                        <?php else: ?>


                            <div class="client-table-wrapper">

                                <table class="client-table">

                                    <thead>

                                        <tr>

                                            <th>
                                                SERVICE
                                            </th>

                                            <th>
                                                QUANTITY
                                            </th>

                                            <th>
                                                PRICE
                                            </th>

                                            <th>
                                                SUBTOTAL
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach (
                                            $items as $item
                                        ): ?>

                                            <tr>

                                                <td>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $item[
                                                            "service_name"
                                                        ]
                                                    );
                                                    ?>

                                                </td>


                                                <td>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $item[
                                                            "quantity"
                                                        ]
                                                    );
                                                    ?>

                                                </td>


                                                <td>

                                                    ₱<?php
                                                    echo number_format(
                                                        $item[
                                                            "price"
                                                        ],
                                                        2
                                                    );
                                                    ?>

                                                </td>


                                                <td>

                                                    <strong>

                                                        ₱<?php
                                                        echo number_format(
                                                            $item[
                                                                "price"
                                                            ]
                                                            *
                                                            $item[
                                                                "quantity"
                                                            ],
                                                            2
                                                        );
                                                        ?>

                                                    </strong>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>


                        <?php endif; ?>


                    </div>


                </section>

            <?php endforeach; ?>


        <?php endif; ?>


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
                        Manage Your Funeral Services
                    </h2>

                </div>

            </div>


            <div class="client-actions">


                <a
                    href="request_service.php"
                    class="client-action primary"
                >

                    <strong>
                        Request Another Funeral Service
                    </strong>

                    <span>
                        Submit a new funeral service request.
                    </span>

                </a>


                <a
                    href="preparation_status.php"
                    class="client-action"
                >

                    <strong>
                        View Preparation Status
                    </strong>

                    <span>
                        Check the current preparation progress.
                    </span>

                </a>


                <a
                    href="payments.php"
                    class="client-action"
                >

                    <strong>
                        View My Payments
                    </strong>

                    <span>
                        Check your payment records.
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