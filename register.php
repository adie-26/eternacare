<?php

require_once "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $contact_number = trim($_POST["contact_number"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $relationship = trim($_POST["relationship"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    /* =========================================================
       SERVER-SIDE VALIDATION
       ========================================================= */

    if (
        empty($full_name) ||
        empty($contact_number) ||
        empty($email) ||
        empty($address) ||
        empty($relationship) ||
        empty($username) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please complete all fields.";

    } elseif (!preg_match('/^(09[0-9]{9}|639[0-9]{9})$/', $contact_number)) {

        $error = "Please enter a valid Philippine mobile number.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {

        $error = "Password must be at least 8 characters.";

    } elseif (
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ) {

        $error = "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {


        /* =========================================================
           CHECK USERNAME
           ========================================================= */

        $check_username = $conn->prepare(
            "SELECT user_id FROM users WHERE username = ? LIMIT 1"
        );

        $check_username->bind_param("s", $username);
        $check_username->execute();

        $username_result = $check_username->get_result();


        if ($username_result->num_rows > 0) {

            $error = "Username is already taken.";

        } else {


            /* =====================================================
               CHECK EMAIL
               ===================================================== */

            $check_email = $conn->prepare(
                "SELECT client_id FROM clients WHERE email = ? LIMIT 1"
            );

            $check_email->bind_param("s", $email);
            $check_email->execute();

            $email_result = $check_email->get_result();


            if ($email_result->num_rows > 0) {

                $error = "Email address is already registered.";

            } else {


                /* =================================================
                   START DATABASE TRANSACTION
                   ================================================= */

                $conn->begin_transaction();


                try {


                    /* =============================================
                       INSERT CLIENT
                       ============================================= */

                    $client_sql = "INSERT INTO clients
                        (
                            full_name,
                            contact_number,
                            email,
                            address,
                            relationship_to_deceased
                        )
                        VALUES (?, ?, ?, ?, ?)";

                    $client_stmt = $conn->prepare($client_sql);

                    $client_stmt->bind_param(
                        "sssss",
                        $full_name,
                        $contact_number,
                        $email,
                        $address,
                        $relationship
                    );

                    $client_stmt->execute();

                    $client_id = $conn->insert_id;


                    /* =============================================
                       HASH PASSWORD
                       ============================================= */

                    $hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                    /* =============================================
                       INSERT USER ACCOUNT
                       ============================================= */

                    $user_sql = "INSERT INTO users
                        (
                            client_id,
                            full_name,
                            username,
                            password,
                            role,
                            status
                        )
                        VALUES (?, ?, ?, ?, 'Client', 'Active')";

                    $user_stmt = $conn->prepare($user_sql);

                    $user_stmt->bind_param(
                        "isss",
                        $client_id,
                        $full_name,
                        $username,
                        $hashed_password
                    );

                    $user_stmt->execute();


                    /* =============================================
                       COMMIT TRANSACTION
                       ============================================= */

                    $conn->commit();

                    $success = "Registration successful! You can now log in.";


                    /* =============================================
                       CLOSE STATEMENTS
                       ============================================= */

                    $client_stmt->close();
                    $user_stmt->close();


                } catch (Exception $e) {

                    $conn->rollback();

                    $error = "Registration failed. Please try again.";
                }
            }

            $check_email->close();
        }

        $check_username->close();
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

    <title>EternaCare - Client Registration</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        :root {

            --navy: #17232b;
            --navy-light: #22343e;
            --navy-hover: #2b414d;

            --gold: #c7a646;
            --gold-dark: #a98b35;

            --background: #f5f6f8;
            --white: #ffffff;

            --text: #26343b;
            --muted: #737d83;

            --border: #e2e6e8;

            --danger: #b33a3a;
            --success: #3f7d5a;
        }


        /* =========================================================
           BODY
           ========================================================= */

        body {

            font-family: Arial, Helvetica, sans-serif;

            background: var(--background);

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 35px 20px;

            color: var(--text);
        }


        .register-container {

            width: 100%;

            max-width: 650px;
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

            box-shadow:
                0 5px 15px rgba(23, 35, 43, 0.18);
        }


        .brand h1 {

            margin: 0 0 6px;

            color: #17232b;

            font-size: 32px;

            font-weight: 700;
        }


        .brand p {

            margin: 0;

            color: #737d83;

            font-size: 14px;

            font-weight: 400;
        }


        /* =========================================================
           REGISTER CARD
           ========================================================= */

        .register-card {

            position: relative;

            background: #ffffff;

            border-radius: 18px;

            padding: 36px;

            border: 1px solid #e2e6e8;

            box-shadow:
                0 18px 45px rgba(23, 35, 43, 0.12);

            overflow: hidden;
        }


        .register-card::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            right: 0;

            height: 4px;

            background: linear-gradient(
                90deg,
                #17232b,
                #c7a646,
                #17232b
            );
        }


        .register-card h2 {

            text-align: center;

            color: #17232b;

            font-size: 24px;

            font-weight: 700;

            margin-top: 2px;

            margin-bottom: 8px;
        }


        .register-subtitle {

            text-align: center;

            color: #737d83;

            font-size: 14px;

            margin-bottom: 30px;
        }


        /* =========================================================
           FORM SECTIONS
           ========================================================= */

        .form-section {

            margin-bottom: 25px;
        }


        .section-title {

            font-size: 15px;

            font-weight: 700;

            color: #17232b;

            margin-bottom: 16px;

            padding-bottom: 9px;

            border-bottom: 1px solid #e2e6e8;
        }


        .form-group {

            margin-bottom: 18px;
        }


        .form-group label {

            display: block;

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 8px;

            color: #26343b;
        }


        .form-group input,
        .form-group textarea {

            width: 100%;

            padding: 13px 14px;

            border: 1px solid #d5dce0;

            border-radius: 9px;

            background: #ffffff;

            color: #26343b;

            font-size: 15px;

            font-family: Arial, Helvetica, sans-serif;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        .form-group input::placeholder,
        .form-group textarea::placeholder {

            color: #9aa5ab;
        }


        .form-group input:hover,
        .form-group textarea:hover {

            border-color: #bdc7cc;
        }


        .form-group input:focus,
        .form-group textarea:focus {

            border-color: #c7a646;

            box-shadow:
                0 0 0 3px rgba(199, 166, 70, 0.13);

            background: #fffef9;
        }


        .form-group textarea {

            resize: vertical;

            min-height: 90px;
        }


        .form-row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 18px;
        }


        /* =========================================================
           PASSWORD INPUT
           ========================================================= */

        .password-wrapper {

            position: relative;

            width: 100%;
        }


        .password-wrapper input {

            padding-right: 48px;
        }


        .toggle-password {

            position: absolute;

            top: 50%;

            right: 12px;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #737d83;

            cursor: pointer;

            padding: 5px;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .toggle-password:hover {

            color: #c7a646;
        }


        .toggle-password svg {

            width: 20px;

            height: 20px;

            fill: none;

            stroke: currentColor;

            stroke-width: 2;

            stroke-linecap: round;

            stroke-linejoin: round;
        }


        /* =========================================================
           VALIDATION
           ========================================================= */

        .validation-message {

            display: none;

            margin-top: 7px;

            font-size: 12px;

            line-height: 1.5;
        }


        .validation-message.show {

            display: block;
        }


        .validation-message.error {

            color: #b33a3a;
        }


        .validation-message.success {

            color: #3f7d5a;
        }


        .input-valid {

            border-color: #3f7d5a !important;
        }


        .input-invalid {

            border-color: #b33a3a !important;
        }


        /* =========================================================
           PASSWORD REQUIREMENTS
           ========================================================= */

        .password-requirements {

            margin-top: 8px;

            padding-left: 0;

            list-style: none;

            font-size: 12px;

            line-height: 1.7;
        }


        .password-requirements li {

            color: #737d83;
        }


        .password-requirements li.valid {

            color: #3f7d5a;
        }


        .password-requirements li.invalid {

            color: #b33a3a;
        }


        /* =========================================================
           ERROR MESSAGE
           ========================================================= */

        .error-message {

            background: #fff3f3;

            color: #b33a3a;

            border: 1px solid #efcaca;

            border-radius: 9px;

            padding: 12px 14px;

            margin-bottom: 20px;

            font-size: 14px;

            text-align: center;
        }


        /* =========================================================
           SUCCESS MESSAGE
           ========================================================= */

        .success-message {

            background: #f0f8f3;

            color: #3f7d5a;

            border: 1px solid #c4dfcd;

            border-radius: 9px;

            padding: 12px 14px;

            margin-bottom: 20px;

            font-size: 14px;

            text-align: center;
        }


        /* =========================================================
           CREATE CLIENT ACCOUNT BUTTON
           ========================================================= */

        .register-button {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 8px;

            background: #22343e;

            color: #ffffff;

            font-size: 15px;

            font-weight: 600;

            cursor: pointer;

            box-shadow:
                0 7px 16px rgba(23, 35, 43, 0.15);

            transition:
                background 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .register-button:hover {

            background: #c7a646;

            color: #ffffff;

            transform: translateY(-1px);

            box-shadow:
                0 8px 18px rgba(199, 166, 70, 0.25);
        }


        .register-button:focus {

            background: #c7a646;

            color: #ffffff;

            outline: none;
        }


        .register-button:active {

            background: #a98b35;

            color: #ffffff;

            transform: translateY(0);

            box-shadow:
                0 4px 10px rgba(199, 166, 70, 0.25);
        }


        /* =========================================================
           LOGIN LINK
           ========================================================= */

        .login-link {

            text-align: center;

            margin-top: 22px;

            padding-top: 20px;

            border-top: 1px solid #e2e6e8;

            font-size: 14px;

            color: #737d83;
        }


        .login-link a {

            color: #a98b35;

            text-decoration: none;

            font-weight: 700;

            margin-left: 3px;
        }


        .login-link a:hover {

            color: #c7a646;

            text-decoration: underline;
        }


        /* =========================================================
           FOOTER
           ========================================================= */

        .footer-text {

            text-align: center;

            margin-top: 20px;

            color: #8b969c;

            font-size: 12px;
        }


        /* =========================================================
           MOBILE
           ========================================================= */

        @media (max-width: 600px) {

            body {

                padding: 25px 15px;
            }


            .register-card {

                padding: 30px 22px;
            }


            .brand-mark {

                width: 60px;

                height: 60px;

                font-size: 22px;
            }


            .brand h1 {

                font-size: 28px;
            }


            .form-row {

                grid-template-columns: 1fr;

                gap: 0;
            }

        }

    </style>

</head>


<body>


<div class="register-container">


    <!-- =====================================================
         BRAND
         ===================================================== -->

    <div class="brand">

        <div class="brand-mark">
            EC
        </div>

        <h1>
            EternaCare
        </h1>

        <p>
            Funeral Service Management System
        </p>

    </div>



    <!-- =====================================================
         REGISTER CARD
         ===================================================== -->

    <div class="register-card">


        <h2>
            Client Registration
        </h2>


        <p class="register-subtitle">
            Create an account to access EternaCare services
        </p>



        <!-- ERROR MESSAGE -->

        <?php if (!empty($error)): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>



        <!-- SUCCESS MESSAGE -->

        <?php if (!empty($success)): ?>

            <div class="success-message">

                <?php echo htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             REGISTRATION FORM
             ================================================= -->

        <form
            method="POST"
            action=""
            id="registrationForm"
        >



            <!-- =================================================
                 PERSONAL INFORMATION
                 ================================================= -->

            <div class="form-section">


                <div class="section-title">
                    Personal Information
                </div>



                <!-- FULL NAME -->

                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                        required
                    >

                </div>



                <!-- CONTACT + EMAIL -->

                <div class="form-row">


                    <!-- CONTACT NUMBER -->

                    <div class="form-group">

                        <label for="contact_number">
                            Contact Number
                        </label>

                        <input
                            type="tel"
                            id="contact_number"
                            name="contact_number"
                            placeholder="09XXXXXXXXX or 639XXXXXXXXX"
                            pattern="(09[0-9]{9}|639[0-9]{9})"
                            maxlength="12"
                            inputmode="numeric"
                            autocomplete="tel"
                            value="<?php echo htmlspecialchars($contact_number ?? ''); ?>"
                            required
                        >

                        <div
                            id="contactValidation"
                            class="validation-message"
                        ></div>

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            autocomplete="email"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            required
                        >

                    </div>

                </div>



                <!-- ADDRESS -->

                <div class="form-group">

                    <label for="address">
                        Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        placeholder="Enter your complete address"
                        autocomplete="street-address"
                        required
                    ><?php echo htmlspecialchars($address ?? ''); ?></textarea>

                </div>



                <!-- RELATIONSHIP -->

                <div class="form-group">

                    <label for="relationship">
                        Relationship to Deceased
                    </label>

                    <input
                        type="text"
                        id="relationship"
                        name="relationship"
                        placeholder="Example: Son, Daughter, Spouse"
                        value="<?php echo htmlspecialchars($relationship ?? ''); ?>"
                        required
                    >

                </div>


            </div>



            <!-- =================================================
                 ACCOUNT INFORMATION
                 ================================================= -->

            <div class="form-section">


                <div class="section-title">
                    Account Information
                </div>



                <!-- USERNAME -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Choose a username"
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required
                    >

                </div>



                <!-- PASSWORD + CONFIRM PASSWORD -->

                <div class="form-row">


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>


                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                minlength="8"
                                autocomplete="new-password"
                                class="password-input"
                                required
                            >


                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword('password', this)"
                                aria-label="Show password"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >

                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    ></path>

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    ></circle>

                                </svg>

                            </button>

                        </div>



                        <!-- PASSWORD REQUIREMENTS -->

                        <ul
                            class="password-requirements"
                            id="passwordRequirements"
                        >

                            <li id="lengthRequirement">
                                ✗ At least 8 characters
                            </li>

                            <li id="uppercaseRequirement">
                                ✗ One uppercase letter
                            </li>

                            <li id="lowercaseRequirement">
                                ✗ One lowercase letter
                            </li>

                            <li id="numberRequirement">
                                ✗ One number
                            </li>

                            <li id="specialRequirement">
                                ✗ One special character
                            </li>

                        </ul>


                    </div>



                    <!-- CONFIRM PASSWORD -->

                    <div class="form-group">

                        <label for="confirm_password">
                            Confirm Password
                        </label>


                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Re-enter your password"
                                minlength="8"
                                autocomplete="new-password"
                                class="password-input"
                                required
                            >


                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword('confirm_password', this)"
                                aria-label="Show password"
                            >

                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >

                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    ></path>

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    ></circle>

                                </svg>

                            </button>

                        </div>



                        <!-- CONFIRM PASSWORD VALIDATION -->

                        <div
                            id="confirmValidation"
                            class="validation-message"
                        ></div>

                    </div>


                </div>

            </div>



            <!-- =================================================
                 CREATE CLIENT ACCOUNT BUTTON
                 ================================================= -->

            <button
                type="submit"
                class="register-button"
            >
                Create Client Account
            </button>


        </form>



        <!-- =================================================
             CLIENT LOGIN
             ================================================= -->

        <div class="login-link">

            Already have an account?

            <a href="client_login.php">
                Client Login
            </a>

        </div>


    </div>



    <!-- =====================================================
         FOOTER
         ===================================================== -->

    <p class="footer-text">

        EternaCare &copy; 2026

    </p>


</div>



<script>

/* =========================================================
   SHOW / HIDE PASSWORD
   ========================================================= */

function togglePassword(inputId, button) {

    const input = document.getElementById(inputId);

    if (input.type === "password") {

        input.type = "text";

        button.setAttribute(
            "aria-label",
            "Hide password"
        );

        button.innerHTML = `
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M3 3l18 18"></path>

                <path
                    d="M10.6 10.6a2 2 0 0 0 2.8 2.8"
                ></path>

                <path
                    d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a17.5 17.5 0 0 1-3.1 4.1"
                ></path>

                <path
                    d="M6.2 6.2C3.4 8.2 2 12 2 12s3.5 8 10 8c1.6 0 3-.4 4.2-1"
                ></path>
            </svg>
        `;

    } else {

        input.type = "password";

        button.setAttribute(
            "aria-label",
            "Show password"
        );

        button.innerHTML = `
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                ></path>

                <circle
                    cx="12"
                    cy="12"
                    r="3"
                ></circle>
            </svg>
        `;
    }
}



/* =========================================================
   CONTACT NUMBER VALIDATION
   ========================================================= */

const contactInput =
    document.getElementById("contact_number");

const contactValidation =
    document.getElementById("contactValidation");


contactInput.addEventListener("input", function () {

    /* Allow numbers only */

    this.value =
        this.value.replace(/[^0-9]/g, "");


    const value = this.value;


    /* Empty */

    if (value.length === 0) {

        contactValidation.className =
            "validation-message";

        contactValidation.textContent = "";

        this.classList.remove("input-valid");

        this.classList.remove("input-invalid");

        return;
    }


    /* Check valid formats */

    const valid =
        /^(09[0-9]{9}|639[0-9]{9})$/.test(value);


    if (valid) {

        contactValidation.className =
            "validation-message show success";

        contactValidation.textContent =
            "✓ Valid Philippine mobile number.";

        this.classList.remove("input-invalid");

        this.classList.add("input-valid");

    } else {

        contactValidation.className =
            "validation-message show error";


        if (value.startsWith("09")) {

            contactValidation.textContent =
                "Please enter 11 digits. Example: 09123456789";

        } else if (value.startsWith("639")) {

            contactValidation.textContent =
                "Please enter 12 digits. Example: 639123456789";

        } else {

            contactValidation.textContent =
                "Use 09XXXXXXXXX or 639XXXXXXXXX.";
        }


        this.classList.remove("input-valid");

        this.classList.add("input-invalid");
    }

});



/* =========================================================
   PASSWORD VALIDATION
   ========================================================= */

const passwordInput =
    document.getElementById("password");


const lengthRequirement =
    document.getElementById("lengthRequirement");

const uppercaseRequirement =
    document.getElementById("uppercaseRequirement");

const lowercaseRequirement =
    document.getElementById("lowercaseRequirement");

const numberRequirement =
    document.getElementById("numberRequirement");

const specialRequirement =
    document.getElementById("specialRequirement");


passwordInput.addEventListener(
    "input",
    validatePassword
);


function updateRequirement(
    element,
    valid,
    text
) {

    if (valid) {

        element.className = "valid";

        element.textContent =
            "✓ " + text;

    } else {

        element.className = "invalid";

        element.textContent =
            "✗ " + text;
    }
}


function validatePassword() {

    const password =
        passwordInput.value;


    const hasLength =
        password.length >= 8;

    const hasUppercase =
        /[A-Z]/.test(password);

    const hasLowercase =
        /[a-z]/.test(password);

    const hasNumber =
        /[0-9]/.test(password);

    const hasSpecial =
        /[^A-Za-z0-9]/.test(password);


    updateRequirement(
        lengthRequirement,
        hasLength,
        "At least 8 characters"
    );


    updateRequirement(
        uppercaseRequirement,
        hasUppercase,
        "One uppercase letter"
    );


    updateRequirement(
        lowercaseRequirement,
        hasLowercase,
        "One lowercase letter"
    );


    updateRequirement(
        numberRequirement,
        hasNumber,
        "One number"
    );


    updateRequirement(
        specialRequirement,
        hasSpecial,
        "One special character"
    );


    const valid =
        hasLength &&
        hasUppercase &&
        hasLowercase &&
        hasNumber &&
        hasSpecial;


    if (password.length === 0) {

        passwordInput.classList.remove(
            "input-valid",
            "input-invalid"
        );

    } else if (valid) {

        passwordInput.classList.remove(
            "input-invalid"
        );

        passwordInput.classList.add(
            "input-valid"
        );

    } else {

        passwordInput.classList.remove(
            "input-valid"
        );

        passwordInput.classList.add(
            "input-invalid"
        );
    }


    validateConfirmPassword();
}



/* =========================================================
   CONFIRM PASSWORD VALIDATION
   ========================================================= */

const confirmInput =
    document.getElementById("confirm_password");

const confirmValidation =
    document.getElementById("confirmValidation");


confirmInput.addEventListener(
    "input",
    validateConfirmPassword
);


function validateConfirmPassword() {

    const password =
        passwordInput.value;

    const confirmPassword =
        confirmInput.value;


    if (confirmPassword.length === 0) {

        confirmValidation.className =
            "validation-message";

        confirmValidation.textContent = "";

        confirmInput.classList.remove(
            "input-valid",
            "input-invalid"
        );

        return;
    }


    if (password === confirmPassword) {

        confirmValidation.className =
            "validation-message show success";

        confirmValidation.textContent =
            "✓ Passwords match.";

        confirmInput.classList.remove(
            "input-invalid"
        );

        confirmInput.classList.add(
            "input-valid"
        );

    } else {

        confirmValidation.className =
            "validation-message show error";

        confirmValidation.textContent =
            "✗ Passwords do not match.";

        confirmInput.classList.remove(
            "input-valid"
        );

        confirmInput.classList.add(
            "input-invalid"
        );
    }
}



/* =========================================================
   FORM SUBMISSION VALIDATION
   ========================================================= */

document
    .getElementById("registrationForm")
    .addEventListener(
        "submit",
        function(event) {


            const contactValid =
                /^(09[0-9]{9}|639[0-9]{9})$/
                .test(contactInput.value);


            const password =
                passwordInput.value;


            const passwordValid =
                password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[^A-Za-z0-9]/.test(password);


            const passwordsMatch =
                passwordInput.value ===
                confirmInput.value;


            validatePassword();

            validateConfirmPassword();


            if (!contactValid) {

                event.preventDefault();

                contactInput.focus();

                return;
            }


            if (!passwordValid) {

                event.preventDefault();

                passwordInput.focus();

                return;
            }


            if (!passwordsMatch) {

                event.preventDefault();

                confirmInput.focus();

                return;
            }

        }
    );

</script>


</body>

</html>
