    <?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    function requireLogin()
    {
        if (!isLoggedIn()) {
            header("Location: ../login.php");
            exit();
        }
    }

    function requireRole($role)
    {
        requireLogin();

        // Super Admin can access Staff pages
        if ($role === "Staff" && $_SESSION['role'] === "Admin") {
            return;
        }

        // Normal role checking
        if ($_SESSION['role'] !== $role) {
            header("Location: ../login.php");
            exit();
        }
    }