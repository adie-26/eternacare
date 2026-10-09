<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Staff");


// Check task ID

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: preparation_tasks.php");
    exit();
}

$task_id = (int) $_GET["id"];


// Get current preparation task

$sql = "SELECT
            pt.task_id,
            pt.funeral_service_id,
            pt.task_name,
            pt.description,
            pt.status,

            fs.service_start,
            fs.service_end,

            c.full_name AS client_name,

            d.full_name AS deceased_name

        FROM preparation_tasks pt

        INNER JOIN funeral_services fs
            ON pt.funeral_service_id =
               fs.funeral_service_id

        INNER JOIN clients c
            ON fs.client_id = c.client_id

        INNER JOIN deceased d
            ON fs.deceased_id = d.deceased_id

        WHERE pt.task_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $task_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();

    header("Location: preparation_tasks.php");
    exit();
}

$task = $result->fetch_assoc();

$stmt->close();


// Process update

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $task_name = trim($_POST["task_name"]);
    $description = trim($_POST["description"]);
    $status = $_POST["status"];


    // Validate task name

    if ($task_name === "") {

        $error = "Task name is required.";

    }


    // Validate status

    elseif (
        !in_array(
            $status,
            [
                "Pending",
                "In Progress",
                "Completed"
            ]
        )
    ) {

        $error = "Invalid task status.";

    }


    // Update task

    else {

        $update_sql = "UPDATE preparation_tasks
                       SET
                           task_name = ?,
                           description = ?,
                           status = ?
                       WHERE task_id = ?";

        $update_stmt =
            $conn->prepare($update_sql);

        $update_stmt->bind_param(
            "sssi",
            $task_name,
            $description,
            $status,
            $task_id
        );

        if ($update_stmt->execute()) {

            $update_stmt->close();

            header(
                "Location: preparation_tasks.php"
            );

            exit();

        } else {

            $error =
                "Error updating preparation task: "
                . $update_stmt->error;

            $update_stmt->close();

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
        EternaCare - Edit Preparation Task
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
                Edit Preparation Task
            </h1>

            <p>
                Update the preparation task information.
            </p>

        </section>


        <section class="content-section">


            <?php if (isset($error)): ?>

                <div class="error-message">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <!-- Funeral Service Information -->

            <h2>
                Funeral Service Information
            </h2>

            <p>
                <strong>
                    Service ID:
                </strong>

                #<?php
                echo htmlspecialchars(
                    $task["funeral_service_id"]
                );
                ?>
            </p>

            <p>
                <strong>
                    Client:
                </strong>

                <?php
                echo htmlspecialchars(
                    $task["client_name"]
                );
                ?>
            </p>

            <p>
                <strong>
                    Deceased:
                </strong>

                <?php
                echo htmlspecialchars(
                    $task["deceased_name"]
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
                        "M d, Y h:i A",
                        strtotime(
                            $task["service_start"]
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
                        "M d, Y h:i A",
                        strtotime(
                            $task["service_end"]
                        )
                    )
                );
                ?>
            </p>


            <br>


            <!-- Edit Form -->

            <h2>
                Task Details
            </h2>


            <form
                method="POST"
                action=""
            >


                <div class="form-group">

                    <label for="task_name">
                        Task Name
                    </label>

                    <input
                        type="text"
                        id="task_name"
                        name="task_name"
                        value="<?php
                            echo htmlspecialchars(
                                $task["task_name"]
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
                        id="description"
                        name="description"
                        rows="5"
                    ><?php
                        echo htmlspecialchars(
                            $task["description"]
                        );
                    ?></textarea>

                </div>


                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="Pending"
                            <?php
                            if (
                                $task["status"]
                                === "Pending"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Pending
                        </option>

                        <option
                            value="In Progress"
                            <?php
                            if (
                                $task["status"]
                                === "In Progress"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            In Progress
                        </option>

                        <option
                            value="Completed"
                            <?php
                            if (
                                $task["status"]
                                === "Completed"
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            Completed
                        </option>

                    </select>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Preparation Task
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