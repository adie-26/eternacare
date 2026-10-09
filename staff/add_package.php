<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";


/* =========================================================
   PROCESS FORM
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $package_name = trim($_POST["package_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = $_POST["price"] ?? "";
    $status = $_POST["status"] ?? "Available";


    /* =====================================================
       BASIC VALIDATION
       ===================================================== */

    if (empty($package_name) || $price === "") {

        $error = "Please fill in all required fields.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Please enter a valid price.";

    } elseif (
        $status !== "Available" &&
        $status !== "Unavailable"
    ) {

        $error = "Invalid package status.";

    } else {

        $price = (float) $price;


        /* =================================================
           IMAGE VALIDATION
           ================================================= */

        $image_name = "";

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $error = "There was an error uploading the package image.";

            } else {

                $image_tmp = $_FILES["image"]["tmp_name"];
                $image_size = $_FILES["image"]["size"];


                /* Maximum image size: 5 MB */

                if ($image_size > 5 * 1024 * 1024) {

                    $error = "Package image must not exceed 5 MB.";

                } else {

                    /* Check if uploaded file is actually an image */

                    $image_info = getimagesize($image_tmp);

                    if ($image_info === false) {

                        $error = "Please upload a valid image.";

                    } else {

                        /* Allowed image types */

                        $allowed_types = [
                            IMAGETYPE_JPEG,
                            IMAGETYPE_PNG,
                            IMAGETYPE_WEBP
                        ];


                        if (!in_array($image_info[2], $allowed_types, true)) {

                            $error = "Only JPG, PNG, and WEBP images are allowed.";

                        } else {

                            /* Determine extension */

                            switch ($image_info[2]) {

                                case IMAGETYPE_JPEG:
                                    $extension = "jpg";
                                    break;

                                case IMAGETYPE_PNG:
                                    $extension = "png";
                                    break;

                                case IMAGETYPE_WEBP:
                                    $extension = "webp";
                                    break;

                                default:
                                    $extension = "";
                            }


                            /* Generate unique filename */

                            $image_name =
                                "package_" .
                                uniqid("", true) .
                                "." .
                                $extension;


                            /* Upload directory */

                            $upload_directory =
                                "../uploads/packages/";


                            /* Create folder if it does not exist */

                            if (!is_dir($upload_directory)) {

                                if (!mkdir(
                                    $upload_directory,
                                    0755,
                                    true
                                )) {

                                    $error = "Unable to create the image upload folder.";
                                }
                            }


                            /* Move uploaded image */

                            if (empty($error)) {

                                $image_path =
                                    $upload_directory .
                                    $image_name;


                                if (
                                    !move_uploaded_file(
                                        $image_tmp,
                                        $image_path
                                    )
                                ) {

                                    $error =
                                        "Unable to save the package image.";

                                }

                            }

                        }

                    }

                }

            }

        }


        /* =================================================
           CONTINUE ONLY IF IMAGE VALIDATION PASSED
           ================================================= */

        if (empty($error)) {


            /* =================================================
               CHECK DUPLICATE PACKAGE NAME
               ================================================= */

            $check_sql = "SELECT package_id
                          FROM packages
                          WHERE package_name = ?
                          LIMIT 1";

            $check_stmt = $conn->prepare($check_sql);

            $check_stmt->bind_param(
                "s",
                $package_name
            );

            $check_stmt->execute();

            $check_result = $check_stmt->get_result();


            if ($check_result->num_rows > 0) {

                $error =
                    "A package with this name already exists.";

                $check_stmt->close();


                /* Delete uploaded image if package was duplicate */

                if (!empty($image_name)) {

                    $uploaded_file =
                        "../uploads/packages/" .
                        $image_name;

                    if (file_exists($uploaded_file)) {

                        unlink($uploaded_file);
                    }
                }

            } else {

                $check_stmt->close();


                /* =================================================
                   INSERT PACKAGE
                   ================================================= */

                $sql = "INSERT INTO packages
                        (
                            package_name,
                            description,
                            price,
                            image,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "ssdss",
                    $package_name,
                    $description,
                    $price,
                    $image_name,
                    $status
                );


                if ($stmt->execute()) {

                    $stmt->close();

                    header("Location: packages.php");

                    exit();

                } else {

                    $error =
                        "Error adding package: " .
                        $stmt->error;

                    $stmt->close();


                    /* Delete uploaded image if database insert failed */

                    if (!empty($image_name)) {

                        $uploaded_file =
                            "../uploads/packages/" .
                            $image_name;

                        if (file_exists($uploaded_file)) {

                            unlink($uploaded_file);
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

    <title>EternaCare - Add Funeral Package</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>


    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="logo">

            <h2>EternaCare</h2>

            <p>Funeral Service Management</p>

        </div>


        <ul>

            <li>

                <?php if ($_SESSION['role'] === 'Admin'): ?>

                    <a href="../admin/dashboard.php">
                        Dashboard
                    </a>

                <?php else: ?>

                    <a href="dashboard.php">
                        Dashboard
                    </a>

                <?php endif; ?>

            </li>


            <li>
                <a href="clients.php">
                    Clients
                </a>
            </li>


            <li>
                <a href="deceased.php">
                    Deceased Records
                </a>
            </li>


            <li>
                <a href="packages.php">
                    Funeral Packages
                </a>
            </li>


            <li>
                <a href="optional_services.php">
                    Optional Services
                </a>
            </li>


            <li>
                <a href="funeral_schedules.php">
                    Funeral Schedules
                </a>
            </li>


            <li>
                <a href="staff_assignments.php">
                    Staff Assignments
                </a>
            </li>


            <li>
                <a href="vehicle_assignments.php">
                    Vehicle Assignments
                </a>
            </li>


            <li>
                <a href="preparation_tasks.php">
                    Preparation Tasks
                </a>
            </li>


            <li>
                <a href="payments.php">
                    Payments
                </a>
            </li>


            <li>
                <a href="service_history.php">
                    Service History
                </a>
            </li>


            <li>
                <a href="reports.php">
                    Reports
                </a>
            </li>


            <li>
                <a href="../logout.php">
                    Logout
                </a>
            </li>

        </ul>

    </aside>


    <!-- Main Content -->

    <main class="main-content">


        <section class="top-header">

            <h1>
                Add Funeral Package
            </h1>

            <p>
                Create a new funeral service package.
            </p>

        </section>


        <section class="content-section">


            <!-- ERROR MESSAGE -->

            <?php if (!empty($error)): ?>

                <div
                    style="
                        background: #fff3f3;
                        color: #b33a3a;
                        border: 1px solid #efcaca;
                        border-radius: 8px;
                        padding: 12px 15px;
                        margin-bottom: 20px;
                    "
                >

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORM
                 ================================================= -->

            <form
                method="POST"
                action=""
                enctype="multipart/form-data"
            >


                <!-- PACKAGE NAME -->

                <div class="form-group">

                    <label for="package_name">
                        Package Name *
                    </label>

                    <input
                        type="text"
                        id="package_name"
                        name="package_name"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["package_name"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                    ><?php
                        echo htmlspecialchars(
                            $_POST["description"] ?? ""
                        );
                    ?></textarea>

                </div>


                <br>


                <!-- PRICE -->

                <div class="form-group">

                    <label for="price">
                        Price *
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?php
                            echo htmlspecialchars(
                                $_POST["price"] ?? ""
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- PACKAGE IMAGE -->

                <div class="form-group">

                    <label for="image">
                        Package Image
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >

                    <small
                        style="
                            display: block;
                            margin-top: 7px;
                            color: #777;
                        "
                    >
                        JPG, PNG, or WEBP only. Maximum file size: 5 MB.
                    </small>

                </div>


                <br>


                <!-- IMAGE PREVIEW -->

                <div
                    id="image-preview-container"
                    style="
                        display: none;
                        margin-bottom: 20px;
                    "
                >

                    <label
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                        "
                    >
                        Image Preview
                    </label>


                    <img
                        id="image-preview"
                        src=""
                        alt="Package Image Preview"
                        style="
                            width: 180px;
                            height: 130px;
                            object-fit: cover;
                            border-radius: 8px;
                            border: 1px solid #ddd;
                        "
                    >

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option
                            value="Available"
                            <?php
                            if (
                                ($_POST["status"] ?? "Available")
                                === "Available"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Available
                        </option>


                        <option
                            value="Unavailable"
                            <?php
                            if (
                                ($_POST["status"] ?? "")
                                === "Unavailable"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Unavailable
                        </option>

                    </select>

                </div>


                <br>


                <!-- SAVE -->

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Funeral Package
                </button>


                <!-- CANCEL -->

                <a
                    href="packages.php"
                    class="btn"
                >
                    Cancel
                </a>


            </form>


        </section>


    </main>


    <!-- =========================================================
         IMAGE PREVIEW SCRIPT
         ========================================================= -->

    <script>

        const imageInput =
            document.getElementById("image");

        const imagePreview =
            document.getElementById("image-preview");

        const imagePreviewContainer =
            document.getElementById(
                "image-preview-container"
            );


        imageInput.addEventListener(
            "change",
            function () {

                const file = this.files[0];


                if (!file) {

                    imagePreviewContainer.style.display =
                        "none";

                    imagePreview.src = "";

                    return;
                }


                /* Check file size */

                if (
                    file.size >
                    5 * 1024 * 1024
                ) {

                    alert(
                        "The selected image is larger than 5 MB."
                    );

                    this.value = "";

                    imagePreviewContainer.style.display =
                        "none";

                    imagePreview.src = "";

                    return;
                }


                /* Show preview */

                const reader =
                    new FileReader();


                reader.onload =
                    function (event) {

                        imagePreview.src =
                            event.target.result;

                        imagePreviewContainer.style.display =
                            "block";
                    };


                reader.readAsDataURL(file);

            }
        );

    </script>


</body>

</html>