<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");

$error = "";


/* =========================================================
   VALIDATE ID
   ========================================================= */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: packages.php");
    exit();
}

$package_id = (int) $_GET["id"];


/* =========================================================
   GET EXISTING PACKAGE
   ========================================================= */

$sql = "SELECT
            package_id,
            package_name,
            description,
            price,
            image,
            status
        FROM packages
        WHERE package_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $package_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: packages.php");
    exit();
}


$package = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   PROCESS FORM
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $package_name = trim(
        $_POST["package_name"] ?? ""
    );

    $description = trim(
        $_POST["description"] ?? ""
    );

    $price = $_POST["price"] ?? "";

    $status = $_POST["status"] ?? "";


    /* =====================================================
       BASIC VALIDATION
       ===================================================== */

    if (
        empty($package_name) ||
        $price === ""
    ) {

        $error =
            "Please fill in all required fields.";

    } elseif (
        !is_numeric($price) ||
        $price < 0
    ) {

        $error =
            "Please enter a valid price.";

    } elseif (
        $status !== "Available" &&
        $status !== "Unavailable"
    ) {

        $error =
            "Invalid package status.";

    } else {

        $price = (float) $price;


        /* =================================================
           CHECK DUPLICATE PACKAGE NAME
           ================================================= */

        $check_sql = "SELECT package_id
                      FROM packages
                      WHERE package_name = ?
                      AND package_id != ?
                      LIMIT 1";

        $check_stmt =
            $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "si",
            $package_name,
            $package_id
        );

        $check_stmt->execute();

        $check_result =
            $check_stmt->get_result();


        if ($check_result->num_rows > 0) {

            $error =
                "Another package with this name already exists.";

            $check_stmt->close();

        } else {

            $check_stmt->close();


            /* =================================================
               IMAGE VARIABLES
               ================================================= */

            $old_image =
                $package["image"] ?? "";

            $new_image =
                $old_image;

            $uploaded_new_image = "";


            /* =================================================
               CHECK FOR NEW IMAGE
               ================================================= */

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"]
                    !== UPLOAD_ERR_NO_FILE
            ) {


                /* =============================================
                   UPLOAD ERROR
                   ============================================= */

                if (
                    $_FILES["image"]["error"]
                    !== UPLOAD_ERR_OK
                ) {

                    $error =
                        "There was an error uploading the package image.";

                } else {


                    $image_tmp =
                        $_FILES["image"]["tmp_name"];

                    $image_size =
                        $_FILES["image"]["size"];


                    /* =========================================
                       MAXIMUM FILE SIZE: 5 MB
                       ========================================= */

                    if (
                        $image_size >
                        5 * 1024 * 1024
                    ) {

                        $error =
                            "Package image must not exceed 5 MB.";

                    } else {


                        /* =====================================
                           CHECK IF ACTUAL IMAGE
                           ===================================== */

                        $image_info =
                            getimagesize($image_tmp);


                        if ($image_info === false) {

                            $error =
                                "Please upload a valid image.";

                        } else {


                            /* =================================
                               ALLOWED IMAGE TYPES
                               ================================= */

                            $allowed_types = [

                                IMAGETYPE_JPEG,
                                IMAGETYPE_PNG,
                                IMAGETYPE_WEBP

                            ];


                            if (
                                !in_array(
                                    $image_info[2],
                                    $allowed_types,
                                    true
                                )
                            ) {

                                $error =
                                    "Only JPG, PNG, and WEBP images are allowed.";

                            } else {


                                /* =============================
                                   DETERMINE EXTENSION
                                   ============================= */

                                switch (
                                    $image_info[2]
                                ) {

                                    case IMAGETYPE_JPEG:

                                        $extension =
                                            "jpg";

                                        break;


                                    case IMAGETYPE_PNG:

                                        $extension =
                                            "png";

                                        break;


                                    case IMAGETYPE_WEBP:

                                        $extension =
                                            "webp";

                                        break;


                                    default:

                                        $extension =
                                            "";

                                }


                                /* =============================
                                   GENERATE UNIQUE FILENAME
                                   ============================= */

                                $uploaded_new_image =
                                    "package_" .
                                    uniqid(
                                        "",
                                        true
                                    ) .
                                    "." .
                                    $extension;


                                $new_image =
                                    $uploaded_new_image;


                                /* =============================
                                   UPLOAD DIRECTORY
                                   ============================= */

                                $upload_directory =
                                    "../uploads/packages/";


                                /* =============================
                                   CREATE DIRECTORY IF NEEDED
                                   ============================= */

                                if (
                                    !is_dir(
                                        $upload_directory
                                    )
                                ) {

                                    if (
                                        !mkdir(
                                            $upload_directory,
                                            0755,
                                            true
                                        )
                                    ) {

                                        $error =
                                            "Unable to create the image upload folder.";

                                    }

                                }


                                /* =============================
                                   MOVE NEW IMAGE
                                   ============================= */

                                if (
                                    empty($error)
                                ) {

                                    $new_image_path =
                                        $upload_directory .
                                        $uploaded_new_image;


                                    if (
                                        !move_uploaded_file(
                                            $image_tmp,
                                            $new_image_path
                                        )
                                    ) {

                                        $error =
                                            "Unable to save the package image.";

                                        $new_image = $old_image;

                                        $uploaded_new_image = "";

                                    }

                                }

                            }

                        }

                    }

                }

            }


            /* =================================================
               UPDATE DATABASE
               ================================================= */

            if (empty($error)) {

                $update_sql = "UPDATE packages
                               SET
                                   package_name = ?,
                                   description = ?,
                                   price = ?,
                                   image = ?,
                                   status = ?
                               WHERE package_id = ?";

                $update_stmt =
                    $conn->prepare(
                        $update_sql
                    );

                $update_stmt->bind_param(
                    "ssdssi",
                    $package_name,
                    $description,
                    $price,
                    $new_image,
                    $status,
                    $package_id
                );


                if (
                    $update_stmt->execute()
                ) {

                    $update_stmt->close();


                    /* =========================================
                       DELETE OLD IMAGE
                       ========================================= */

                    if (
                        !empty($old_image) &&
                        !empty($uploaded_new_image) &&
                        $old_image !== $new_image
                    ) {

                        $old_image_path =
                            "../uploads/packages/" .
                            $old_image;


                        if (
                            file_exists(
                                $old_image_path
                            )
                        ) {

                            unlink(
                                $old_image_path
                            );

                        }

                    }


                    /* =========================================
                       REDIRECT
                       ========================================= */

                    header(
                        "Location: view_package.php?id=" .
                        $package_id
                    );

                    exit();


                } else {

                    $error =
                        "Error updating package: " .
                        $update_stmt->error;

                    $update_stmt->close();


                    /* =========================================
                       DELETE NEW IMAGE IF DATABASE UPDATE FAILS
                       ========================================= */

                    if (
                        !empty($uploaded_new_image)
                    ) {

                        $new_image_path =
                            "../uploads/packages/" .
                            $uploaded_new_image;


                        if (
                            file_exists(
                                $new_image_path
                            )
                        ) {

                            unlink(
                                $new_image_path
                            );

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

    <title>
        EternaCare - Edit Funeral Package
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>


    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="logo">

            <h2>
                EternaCare
            </h2>

            <p>
                Funeral Service Management
            </p>

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
                Edit Funeral Package
            </h1>

            <p>
                Update the information of this funeral package.
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
                    echo htmlspecialchars(
                        $error
                    );
                    ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

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
                                $_POST["package_name"]
                                ?? $package["package_name"]
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
                            $_POST["description"]
                            ?? $package["description"]
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
                                $_POST["price"]
                                ?? $package["price"]
                            );
                        ?>"
                        required
                    >

                </div>


                <br>


                <!-- CURRENT IMAGE -->

                <div class="form-group">

                    <label>
                        Current Package Image
                    </label>


                    <?php if (!empty($package["image"])): ?>

                        <div style="margin-top: 10px;">

                            <img
                                src="../uploads/packages/<?php echo htmlspecialchars($package["image"]); ?>"
                                alt="Current Package Image"
                                style="
                                    width: 220px;
                                    height: 150px;
                                    object-fit: cover;
                                    border-radius: 10px;
                                    border: 1px solid #ddd;
                                    display: block;
                                "
                            >

                        </div>

                    <?php else: ?>

                        <p
                            style="
                                color: #777;
                                margin-top: 8px;
                            "
                        >
                            No package image uploaded.
                        </p>

                    <?php endif; ?>

                </div>


                <br>


                <!-- NEW IMAGE -->

                <div class="form-group">

                    <label for="image">
                        Replace Package Image
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
                        JPG, PNG, or WEBP only.
                        Maximum file size: 5 MB.
                        Leave this empty to keep the current image.
                    </small>

                </div>


                <br>


                <!-- NEW IMAGE PREVIEW -->

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
                        New Image Preview
                    </label>


                    <img
                        id="image-preview"
                        src=""
                        alt="New Package Image Preview"
                        style="
                            width: 220px;
                            height: 150px;
                            object-fit: cover;
                            border-radius: 10px;
                            border: 1px solid #ddd;
                        "
                    >

                </div>


                <br>


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
                                (
                                    $_POST["status"]
                                    ?? $package["status"]
                                ) === "Available"
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
                                (
                                    $_POST["status"]
                                    ?? $package["status"]
                                ) === "Unavailable"
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


                <!-- UPDATE BUTTON -->

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Funeral Package
                </button>


                <!-- CANCEL -->

                <a
                    href="view_package.php?id=<?php echo $package_id; ?>"
                    class="btn"
                >
                    Cancel
                </a>


            </form>


        </section>


    </main>


    <!-- IMAGE PREVIEW SCRIPT -->

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

                const file =
                    this.files[0];


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


                /* Show image preview */

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