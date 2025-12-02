<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

// rt524 12/2 after save job is clicked...
// get the job_id of the card and insert into user_jobs table

if (!isset($_POST["job_id"])) {
    flash("Invalid job selection", "warning");
    redirect("landing.php");
}

$job_id = $_POST["job_id"];
$user_id = get_user_id();

$db = getDB();

// added on duplicate key clause for adding from different places bug
$stmt = $db->prepare("INSERT INTO IT202_F25_User_Jobs (user_id, job_id)
    VALUES (:user_id, :job_id)
    ON DUPLICATE KEY UPDATE is_active = 1
");

try {
    $stmt->execute([
        ":user_id" => $user_id,
        ":job_id" => $job_id
    ]);
    flash("Job saved successfully!", "success");

} catch (PDOException $e) {
    error_log("Error saving job: " . var_export($e, true));
    flash("Error saving job", "danger");
}

// flash("Job saved successfully!", "success");
die(header("Location:" . get_url("landing.php")));
