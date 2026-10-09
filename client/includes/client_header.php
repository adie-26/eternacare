<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!-- EternaCare Client Navigation -->

<header class="client-navbar">

    <!-- Logo -->

    <a href="dashboard.php" class="client-brand">

        <div class="client-brand-mark">
            EC
        </div>

        <div class="client-brand-text">

            <h2>EternaCare</h2>

            <span>FUNERAL SERVICE MANAGEMENT</span>

        </div>

    </a>


    <!-- Navigation -->

    <nav class="client-nav">

        <a href="dashboard.php">
            HOME
        </a>


        <!-- SERVICES -->

        <div class="client-dropdown">

            <a href="#" class="client-dropdown-link">
                SERVICES ▾
            </a>

            <div class="client-dropdown-menu">

                <a href="packages.php">
                    Funeral Packages
                </a>

                <a href="optional_services.php">
                    Optional Services
                </a>

                <a href="request_service.php">
                    Request Funeral Service
                </a>

            </div>

        </div>


        <!-- MY SERVICES -->

        <div class="client-dropdown">

            <a href="#" class="client-dropdown-link">
                MY SERVICES ▾
            </a>

            <div class="client-dropdown-menu">

                <a href="my_services.php">
                    My Funeral Services
                </a>

                <a href="preparation_status.php">
                    Preparation Status
                </a>

                <a href="service_history.php">
                    Service History
                </a>

            </div>

        </div>


        <!-- MY ACCOUNT -->

        <div class="client-dropdown">

            <a href="#" class="client-dropdown-link">
                MY ACCOUNT ▾
            </a>

            <div class="client-dropdown-menu">

                <a href="profile.php">
                    My Profile
                </a>

                <a href="deceased.php">
                    Deceased Information
                </a>

                <a href="payments.php">
                    My Payments
                </a>

                <a href="balance.php">
                    My Balance
                </a>

                <a href="notifications.php">
                    Notifications
                </a>

            </div>

        </div>


        <!-- LOGOUT -->

        <a href="../logout.php" class="logout-link">
            LOGOUT
        </a>

    </nav>

</header>