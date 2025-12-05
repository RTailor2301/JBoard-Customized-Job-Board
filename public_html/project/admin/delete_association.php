<?php
require(__DIR__ . "/../../../partials/nav.php");


if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

// rt524 12/4 gets the assoc_id from the all users assoc page 
// and changes all of the matching user ids and connected job ids to be = 0

$assoc_id = se($_GET, "assoc_id", "", false);
if ((int)$assoc_id <= 0) {
    flash("Invalid association id provided", "danger");
    die(header("Location:" . get_url("admin/all_user_assoc.php")));
}

$db = getDB();

try {
    $stmt = $db->prepare("SELECT id, user_id, job_id, is_active FROM IT202_F25_User_Jobs WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $assoc_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        flash("Association not found", "warning");
        die(header("Location: " . get_url("admin/all_user_assoc.php")));
    }

    // is active to 0
    $update = $db->prepare("UPDATE IT202_F25_User_Jobs SET is_active = 0, WHERE id = :id");
    $update->execute([":id" => $assoc_id]);

    flash("Association removed for user_id {$row['user_id']} and job_id {$row['job_id']}", "success");

} catch (PDOException $e) {
    error_log("Delete association error: " . $e->getMessage());
    flash("Error removing association: " . $e->getMessage(), "danger");
    
}
die(header("Location: " . get_url("admin/all_user_assoc.php")));
