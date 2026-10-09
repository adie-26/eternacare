<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


/*
 * Get funeral service ID from URL.
 */

$funeral_service_id = isset($_GET["funeral_service_id"])
    ? intval($_GET["funeral_service_id"])
    : 0;


/*
 * Validate funeral service ID.
 */

if ($funeral_service_id <= 0) {

    header("Location: preparation_tasks.php");
    exit();

}


/*
 * Get funeral service information.
 */

$service_sql = "SELECT
                    fs.funeral_service_id,
                    fs.service_start,
                    fs.service_end,
                    fs.location,
                    fs.status,

                    c.full_name AS client_name,

                    d.full_name AS deceased_name

                FROM funeral_services fs

                INNER JOIN clients c
                    ON fs.client_id = c.client_id

                INNER JOIN deceased d
                    ON fs.deceased_id = d.deceased_id

                WHERE fs.funeral_service_id = ?";

$service_stmt = $conn->prepare($service_sql);

$service_stmt->bind_param(
    "i",
    $funeral_service_id
);

$service_stmt->execute();

$service_result = $service_stmt->get_result();

$service = $service_result->fetch_assoc();

$service_stmt->close();


/*
 * If the funeral service does not exist,
 * return to preparation tasks.
 */

if (!$service) {

    header("Location: preparation_tasks.php");
    exit();

}


$error = "";


/*
 * Handle form submission.
 */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $task_name = trim(
        $_POST["task_name"] ?? ""
    );

    $description = trim(
        $_POST["description"] ?? ""
    );

    $status = $_POST["status"] ?? "Pending";


    /*
     * Validate task name.
     */

    if ($task_name === "") {

        $error = "Please enter a task name.";

    } elseif (
        !in_array(
            $status,
            ["Pending", "In Progress", "Completed"]
        )
    ) {

        $error = "Invalid task status.";

    } else {


        /*
         * Insert preparation task.
         */

        $insert_sql = "INSERT INTO preparation_tasks
                       (
                           funeral_service_id,
                           task_name,
                           description,
                           status
                       )

                       VALUES (?, ?, ?, ?)";

        $insert_stmt =
            $conn->prepare($insert_sql);

        $insert_stmt->bind_param(
            "isss",
            $funeral_service_id,
            $task_name,
            $description,
            $status
        );


        if ($insert_stmt->execute()) {

            $insert_stmt->close();

            header(
                "Location: preparation_tasks.php"
            );

            exit();

        } else {

            $error =
                "Error adding preparation task: "
                . $insert_stmt->error;

            $insert_stmt->close();

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
        EternaCare - Add Preparation Task
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

            <h2>EternaCare</h2>

            <p>
                Funeral Service Management
            </p>

        </div>


        <ul>

            <li>
    <?php if ($_SESSION['role'] === 'Admin'): ?>
        <a href="../admin/dashboard.php">Dashboard</a>
    <?php else: ?>
        <a href="dashboard.php">Dashboard</a>
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
                Add Preparation Task
            </h1>

            <p>
                Add a preparation task for a funeral service.
            </p>

        </section>


        <!-- Funeral Service Information -->

        <section class="content-section">

            <h2>
                Funeral Service Information
            </h2>


            <p>

                <strong>
                    Service ID:
                </strong>

                #<?php
                echo htmlspecialchars(
                    $service[
                        "funeral_service_id"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Client:
                </strong>

                <?php
                echo htmlspecialchars(
                    $service[
                        "client_name"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Deceased:
                </strong>

                <?php
                echo htmlspecialchars(
                    $service[
                        "deceased_name"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Service Start:
                </strong>

                <?php
                echo htmlspecialchars(
                    date(
                        "F d, Y h:i A",
                        strtotime(
                            $service[
                                "service_start"
                            ]
                        )
                    )
                );
                ?>

            </p>


            <p>

                <strong>
                    Service End:
                </strong>

                <?php
                echo htmlspecialchars(
                    date(
                        "F d, Y h:i A",
                        strtotime(
                            $service[
                                "service_end"
                            ]
                        )
                    )
                );
                ?>

            </p>


            <p>

                <strong>
                    Location:
                </strong>

                <?php
                echo htmlspecialchars(
                    $service[
                        "location"
                    ]
                );
                ?>

            </p>


            <p>

                <strong>
                    Service Status:
                </strong>

                <?php
                echo htmlspecialchars(
                    $service[
                        "status"
                    ]
                );
                ?>

            </p>

        </section>


        <br>


        <!-- Add Task Form -->

        <section class="content-section">

            <h2>
                Task Information
            </h2>


            <?php if ($error !== ""): ?>

                <p style="color: red;">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </p>

            <?php endif; ?>


            <form method="POST">


                <div class="form-group">

                    <label for="task_name">
                        Task Name *
                    </label>

                    <input
                        type="text"
                        name="task_name"
                        id="task_name"
                        placeholder="Example: Prepare Viewing Room"
                        value="<?php
                        echo htmlspecialchars(
                            $_POST[
                                "task_name"
                            ] ?? ""
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        rows="5"
                        placeholder="Enter task details..."
                    ><?php
                    echo htmlspecialchars(
                        $_POST[
                            "description"
                        ] ?? ""
                    );
                    ?></textarea>

                </div>


                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                    >

                        <option
                            value="Pending"
                            <?php
                            echo (
                                ($_POST[
                                    "status"
                                ] ?? "Pending")
                                === "Pending"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Pending
                        </option>

                        <option
                            value="In Progress"
                            <?php
                            echo (
                                ($_POST[
                                    "status"
                                ] ?? "")
                                === "In Progress"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            In Progress
                        </option>

                        <option
                            value="Completed"
                            <?php
                            echo (
                                ($_POST[
                                    "status"
                                ] ?? "")
                                === "Completed"
                            )
                                ? "selected"
                                : "";
                            ?>
                        >
                            Completed
                        </option>

                    </select>

                </div>


                <br>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Preparation Task
                </button>


                <a
                    href="preparation_tasks.php"
                    class="btn"
                >
                    Cancel
                </a>


            </form>

        </section>


    </main>


</body>

</html>