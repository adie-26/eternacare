<?php

session_start();

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {

        $error = "Please enter your username and password.";

    } else {

        $sql = "SELECT
                    user_id,
                    client_id,
                    full_name,
                    username,
                    password,
                    role,
                    status
                FROM users
                WHERE username = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if ($user["status"] !== "Active") {

                $error = "Your account is inactive.";

            } elseif ($user["role"] !== "Client") {

                $error = "This login is for Client accounts only.";

            } elseif (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["client_id"] = $user["client_id"];
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                header("Location: client/dashboard.php");
                exit();

            } else {

                $error = "Invalid username or password.";

            }

        } else {

            $error = "Invalid username or password.";

        }

        $stmt->close();
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

    <title>EternaCare - Client Login</title>

    <style>

        /* =========================================================
           EternaCare Login Design
        ========================================================= */

        :root {
            --navy: #17232b;
            --navy-light: #22343e;

            --gold: #c7a646;
            --gold-dark: #a98b35;

            --background: #f5f6f8;

            --white: #ffffff;

            --text: #26343b;
            --muted: #737d83;

            --border: #e4e7ea;

            --danger: #b33a3a;
        }


        /* =========================================================
           RESET
        ========================================================= */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        /* =========================================================
           BODY
        ========================================================= */

        body {
            font-family: Arial, Helvetica, sans-serif;

            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 25px;

            color: var(--text);

            background:
                radial-gradient(
                    circle at top left,
                    rgba(199, 166, 70, 0.10),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #f7f5ef,
                    #eef1f3
                );
        }


        /* =========================================================
           LOGIN CONTAINER
        ========================================================= */

        .login-container {
            width: 100%;
            max-width: 430px;
        }


        /* =========================================================
           BRAND
        ========================================================= */

       .brand {
    text-align: center;
    margin-bottom: 26px;
}

.brand-mark {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #22343e;
    color: #c7a646;

    border: 2px solid #c7a646;
    border-radius: 17px;

    font-size: 23px;
    font-weight: 700;
    letter-spacing: 0;

    box-shadow: 0 5px 15px rgba(23, 35, 43, 0.18);

    box-sizing: border-box;
}

.brand h1 {
    margin: 0 0 6px;

    color: #17232b;

    font-size: 32px;
    font-weight: 700;
    letter-spacing: 0;
}

.brand p {
    margin: 0;

    color: #737d83;

    font-size: 14px;
    font-weight: 400;
}

        /* =========================================================
           LOGIN CARD
        ========================================================= */

        .login-card {
            position: relative;

            background: var(--white);

            border-radius: 18px;

            padding: 36px;

            border: 1px solid var(--border);

            box-shadow:
                0 18px 45px rgba(23, 35, 43, 0.10),
                0 3px 10px rgba(23, 35, 43, 0.04);

            overflow: hidden;
        }


        .login-card::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;
            right: 0;

            height: 4px;

            background:
                linear-gradient(
                    90deg,
                    var(--navy),
                    var(--gold),
                    var(--navy)
                );
        }


        .login-card h2 {
            text-align: center;

            color: var(--navy);

            font-size: 24px;

            margin-bottom: 8px;
        }


        .login-subtitle {
            text-align: center;

            color: var(--muted);

            font-size: 14px;

            margin-bottom: 28px;

            line-height: 1.5;
        }


        /* =========================================================
           FORM
        ========================================================= */

        .form-group {
            margin-bottom: 20px;
        }


        .form-group label {
            display: block;

            font-size: 13px;

            font-weight: 700;

            margin-bottom: 8px;

            color: #34434b;
        }


        .form-group input {
            width: 100%;

            padding: 13px 14px;

            border: 1px solid #dce1e4;

            border-radius: 9px;

            background: #fafbfb;

            font-size: 15px;

            color: var(--text);

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        .form-group input::placeholder {
            color: #a1a8ac;
        }


        .form-group input:hover {
            border-color: #c7a646;
        }


        .form-group input:focus {
            background: #ffffff;

            border-color: var(--gold);

            box-shadow:
                0 0 0 3px rgba(199, 166, 70, 0.13);
        }


        /* =========================================================
           ERROR MESSAGE
        ========================================================= */

        .error-message {
            background: #fff5f5;

            color: var(--danger);

            border: 1px solid #f0cccc;

            border-left: 4px solid var(--danger);

            border-radius: 8px;

            padding: 12px 14px;

            margin-bottom: 20px;

            font-size: 13px;

            line-height: 1.4;
        }


        /* =========================================================
           LOGIN BUTTON
        ========================================================= */

        .login-button {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 9px;

            background:
                linear-gradient(
                    135deg,
                    var(--navy),
                    var(--navy-light)
                );

            color: #ffffff;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            transition:
                all 0.2s ease;

            box-shadow:
                0 7px 16px rgba(23, 35, 43, 0.16);
        }


        .login-button:hover {
            background:
                linear-gradient(
                    135deg,
                    var(--gold-dark),
                    var(--gold)
                );

            transform: translateY(-1px);

            box-shadow:
                0 9px 20px rgba(169, 139, 53, 0.25);
        }


        .login-button:active {
            transform: translateY(0);
        }


        /* =========================================================
           REGISTER LINK
        ========================================================= */

        .register-link {
            text-align: center;

            margin-top: 24px;

            font-size: 14px;

            color: var(--muted);
        }


        .register-link a {
            color: var(--gold-dark);

            text-decoration: none;

            font-weight: 700;

            margin-left: 4px;
        }


        .register-link a:hover {
            color: var(--navy);

            text-decoration: underline;
        }


        /* =========================================================
           ADMIN / STAFF LINK
        ========================================================= */

        .staff-link {
            text-align: center;

            margin-top: 20px;

            padding-top: 20px;

            border-top: 1px solid #eeeeee;

            font-size: 13px;

            color: var(--muted);

            line-height: 1.5;
        }


        .staff-link a {
            display: inline-block;

            margin-top: 3px;

            color: var(--navy);

            text-decoration: none;

            font-weight: 700;
        }


        .staff-link a:hover {
            color: var(--gold-dark);

            text-decoration: underline;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .footer-text {
            text-align: center;

            margin-top: 20px;

            color: #8b9499;

            font-size: 12px;
        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 480px) {

            body {
                padding: 18px;
            }


            .login-card {
                padding: 30px 22px;

                border-radius: 15px;
            }


            .brand-mark {
                width: 56px;
                height: 56px;

                font-size: 23px;
            }


            .brand h1 {
                font-size: 28px;
            }


            .brand p {
                font-size: 13px;
            }


            .login-card h2 {
                font-size: 21px;
            }

        }

    </style>

</head>


<body>

    <div class="login-container">


        <!-- EternaCare Brand -->

        <div class="brand">

            <div class="brand-mark">
                EC
            </div>

            <h1>EternaCare</h1>

            <p>Funeral Service Management System</p>

        </div>


        <!-- Login Card -->

        <div class="login-card">

            <h2>Client Login</h2>

            <p class="login-subtitle">
                Sign in to manage your funeral service requests
            </p>


            <?php if (!empty($error)): ?>

                <div class="error-message">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <form method="POST" action="">


                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>


            </form>


            <div class="register-link">

                Don't have an account?

                <a href="register.php">
                    Register
                </a>

            </div>


            <div class="staff-link">

                Are you an administrator or staff member?

                <a href="login.php">
                    Admin / Staff Login
                </a>

            </div>


        </div>


        <p class="footer-text">
            EternaCare &copy; 2026
        </p>


    </div>

</body>

</html>