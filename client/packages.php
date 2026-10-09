<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Client");


/* =========================================================
   GET AVAILABLE FUNERAL PACKAGES
   ========================================================= */

$sql = "SELECT
            package_id,
            package_name,
            description,
            price,
            image,
            status
        FROM packages
        WHERE status = 'Available'
        ORDER BY package_name ASC";

$result = $conn->query($sql);

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
        EternaCare - Funeral Packages
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        /* =====================================================
           FUNERAL PACKAGE CARDS
           ===================================================== */

        .package-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(280px, 1fr)
                );

            gap: 24px;

            margin-top: 10px;

        }


        .package-card {

            background: #ffffff;

            border: 1px solid #e2e6e8;

            border-radius: 14px;

            overflow: hidden;

            box-shadow:
                0 8px 22px
                rgba(23, 35, 43, 0.08);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .package-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 14px 30px
                rgba(23, 35, 43, 0.13);

        }


        /* =====================================================
           PACKAGE IMAGE
           ===================================================== */

        .package-image {

            width: 100%;

            height: 210px;

            overflow: hidden;

            background: #f1f3f4;

        }


        .package-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

            transition:
                transform 0.3s ease;

        }


        .package-card:hover
        .package-image img {

            transform: scale(1.03);

        }


        .package-no-image {

            width: 100%;

            height: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #f1f3f4;

            color: #8a9499;

            font-size: 14px;

        }


        /* =====================================================
           PACKAGE CONTENT
           ===================================================== */

        .package-content {

            padding: 22px;

        }


        .package-content h3 {

            margin: 0 0 10px;

            color: #17232b;

            font-size: 19px;

            font-weight: 700;

        }


        .package-description {

            min-height: 55px;

            margin-bottom: 18px;

            color: #737d83;

            font-size: 14px;

            line-height: 1.6;

        }


        /* =====================================================
           PACKAGE FOOTER
           ===================================================== */

        .package-footer {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding-top: 16px;

            border-top: 1px solid #e8ebed;

        }


        .package-price {

            color: #a98b35;

            font-size: 19px;

            font-weight: 700;

        }


        .package-status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            background: #eef7f1;

            color: #3f7d5a;

            font-size: 12px;

            font-weight: 700;

        }


        /* =====================================================
           EMPTY STATE
           ===================================================== */

        .client-empty-box {

            text-align: center;

            padding: 45px 20px;

        }


        .client-empty-box h3 {

            margin-bottom: 8px;

            color: #17232b;

        }


        .client-empty-box p {

            color: #737d83;

        }


        /* =====================================================
           MOBILE
           ===================================================== */

        @media (max-width: 600px) {

            .package-grid {

                grid-template-columns: 1fr;

            }


            .package-image {

                height: 190px;

            }

        }

    </style>

</head>

<body class="client-page">


<?php include "includes/client_header.php"; ?>


<!-- =========================================================
     FUNERAL PACKAGES PAGE
     ========================================================= -->

<main class="client-dashboard">


    <!-- PAGE HEADER -->

    <section class="client-welcome">

        <div>

            <span class="client-section-label">
                ETERNACARE SERVICES
            </span>

            <h1>
                Funeral Packages
            </h1>

            <p>
                Explore the available funeral packages offered
                by EternaCare and choose the service that best
                fits your needs.
            </p>

        </div>

    </section>


    <!-- =====================================================
         AVAILABLE PACKAGES
         ===================================================== -->

    <section class="client-panel">


        <div class="client-panel-header">

            <div>

                <span class="client-section-label">
                    AVAILABLE PACKAGES
                </span>

                <h2>
                    Funeral Packages
                </h2>

            </div>

        </div>


        <?php if ($result->num_rows === 0): ?>


            <!-- EMPTY STATE -->

            <div class="client-empty-box">

                <h3>
                    No Funeral Packages Available
                </h3>

                <p>
                    There are currently no funeral packages available.
                    Please check again later.
                </p>

            </div>


        <?php else: ?>


            <!-- =================================================
                 PACKAGE CARDS
                 ================================================= -->

            <div class="package-grid">


                <?php while ($package = $result->fetch_assoc()): ?>


                    <div class="package-card">


                        <!-- PACKAGE IMAGE -->

                        <div class="package-image">


                            <?php if (!empty($package["image"])): ?>


                                <img
                                    src="../uploads/packages/<?php echo htmlspecialchars($package["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($package["package_name"]); ?>"
                                >


                            <?php else: ?>


                                <div class="package-no-image">

                                    No Image Available

                                </div>


                            <?php endif; ?>


                        </div>


                        <!-- PACKAGE INFORMATION -->

                        <div class="package-content">


                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $package["package_name"]
                                );
                                ?>

                            </h3>


                            <div class="package-description">

                                <?php

                                echo !empty(
                                    $package["description"]
                                )

                                    ? nl2br(
                                        htmlspecialchars(
                                            $package["description"]
                                        )
                                    )

                                    : "No description provided.";

                                ?>

                            </div>


                            <!-- PACKAGE FOOTER -->

                            <div class="package-footer">


                                <span class="package-price">

                                    ₱<?php

                                    echo number_format(
                                        (float) $package["price"],
                                        2
                                    );

                                    ?>

                                </span>


                                <span class="package-status">

                                    <?php
                                    echo htmlspecialchars(
                                        $package["status"]
                                    );
                                    ?>

                                </span>


                            </div>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php endif; ?>


    </section>


    <!-- =====================================================
         WHAT HAPPENS NEXT
         ===================================================== -->

    <section class="client-panel">


        <div class="client-information">

            <span class="client-section-label">
                NEXT STEP
            </span>

            <h2>
                What Happens Next?
            </h2>

            <p>
                After reviewing the available funeral packages,
                you can proceed to request a funeral service.
                You may also select additional optional services
                during the request process.
            </p>

        </div>


        <div class="client-actions">

            <a
                href="request_service.php"
                class="client-action-btn"
            >
                Request Funeral Service
            </a>


            <a
                href="optional_services.php"
                class="client-action-btn secondary"
            >
                View Optional Services
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