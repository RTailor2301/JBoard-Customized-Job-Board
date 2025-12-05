<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission", "warning");
    die(header("Location: " . get_url("landing.php")));
}

// rt524 12/4 gets username and sets all is_active = 0 with partial matched username

$username = $_GET["username"];

if (!$username) {
    flash("No username filter provided", "danger");
    die(header("Location: " . get_url("admin/all_user_assoc.php")));
}

$db = getDB();

try {
    $update = $db->prepare("UPDATE IT202_F25_User_Jobs
        SET is_active = 0
        WHERE user_id IN (
            SELECT id FROM Users WHERE username LIKE :uname
        )
    ");

    $update->execute([":uname" => "%$username%"]);
    flash("All associations for users matching '$username' were removed.", "success");

} catch (PDOException $e) {
    error_log("Delete associations error: " . $e->getMessage());
    flash("Error removing associations", "danger");
}

die(header("Location: " . get_url("admin/all_user_assoc.php")));

