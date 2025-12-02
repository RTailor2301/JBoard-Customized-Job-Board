<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

if (!isset($_POST["job_id"])) {
    flash("Missing job id", "danger");
    die(header("Location: " . get_url("landing.php")));
}

$job_id = ($_POST["job_id"]);
$user_id = get_user_id();

$db = getDB();
$stmt = $db->prepare("UPDATE IT202_F25_User_Jobs
    SET is_active = 0
    WHERE user_id = :uid AND job_id = :jid
");
try {
    $stmt->execute([
        ":uid" => $user_id, 
        ":jid" => $job_id
    ]);
    flash("Job unsaved successfully!", "success");

} catch (PDOException $e) {
    error_log("Error unsaving job: " . var_export($e, true));
    flash("Error unsaving job", "danger");
}

die(header("Location:" . get_url("landing.php")));
