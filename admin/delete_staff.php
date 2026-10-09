<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requireRole("Admin");

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: staff.php");
    exit();
}

$user_id = (int) $_GET["id"];


/* Make sure the account is a Staff account */

$check = $conn->prepare(
    "SELECT user_id
     FROM users
     WHERE user_id = ?
     AND role = 'Staff'
     LIMIT 1"
);

$check->bind_param("i", $user_id);
$check->execute();

$result = $check->get_result();

if ($result->num_rows !== 1) {

    $check->close();

    header("Location: staff.php");
    exit();
}

$check->close();


/* Delete the staff account */

$conn->begin_transaction();

try {

    /*
     * staff.user_id uses ON DELETE SET NULL,
     * so the staff record will remain.
     *
     * We remove the staff record first so
     * the complete staff account is deleted.
     */

    $staff_stmt = $conn->prepare(
        "DELETE FROM staff
         WHERE user_id = ?"
    );

    $staff_stmt->bind_param("i", $user_id);
    $staff_stmt->execute();

    $staff_stmt->close();


    /* Delete login account */

    $user_stmt = $conn->prepare(
        "DELETE FROM users
         WHERE user_id = ?
         AND role = 'Staff'"
    );

    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();

    $user_stmt->close();


    $conn->commit();

} catch (Exception $e) {

    $conn->rollback();
}

header("Location: staff.php");
exit();

?>