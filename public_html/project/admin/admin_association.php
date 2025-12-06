<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();

// rt524 12/5 same logic as assign_roles to assign jobs to users
if (isset($_POST["jobs"], $_POST["users"])) {
    $job_ids = $_POST["jobs"];
    $user_ids = $_POST["users"];

    if (empty($job_ids) || empty($user_ids)) {
        flash("You must select at least one job and one user", "warning");
    } else {
        $stmt = $db->prepare("INSERT INTO IT202_F25_User_Jobs (user_id, job_id, is_active)
            VALUES (:uid, :jid, 1)
            ON DUPLICATE KEY UPDATE is_active = !is_active
        ");

        // for all possible matches
        foreach ($user_ids as $uid) {
            foreach ($job_ids as $jid) {
                try {
                    $stmt->execute([":uid" => $uid, ":jid" => $jid]);
                    if ($stmt->rowCount() > 0) {
                        flash("Toggled association for user $uid and job $jid", "success");
                    } else {
                        flash("No changes made for user $uid and job $jid", "warning");
                    }
                } catch (PDOException $e) {
                    flash("There was an error toggling the job, please try again later", "danger");
                    error_log("Error toggling association for user $uid and job $jid: " . var_export($e->errorInfo, true));
                }
            }
        }
    }
}

$searched_job = "";
$searched_user = "";
$jobs = [];
$users = [];

if (isset($_POST["action"]) && $_POST["action"] === "search") {
    $searched_job = trim(se($_POST, "job_title", "", false));
    $searched_user = trim(se($_POST, "username", "", false));

    // Search Jobs
    if (!empty($searched_job)) {
        $stmt = $db->prepare("SELECT job_id, job_title, employer_name
            FROM IT202_F25_Jsearch
            WHERE job_title LIKE :jt
            LIMIT 25
        ");
        try {
            $stmt->execute([":jt" => "%$searched_job%"]);
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }

    // Search Users
    if (!empty($searched_user)) {
        $stmt = $db->prepare("SELECT id, username
            FROM Users
            WHERE username LIKE :username
            LIMIT 25
        ");
        try {
            $stmt->execute([":username" => "%$searched_user%"]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
    if (empty($jobs) && empty($users)) {
        flash("No matching jobs or users found", "warning");
    }
}

?>

<h3>Associate Jobs with Users</h3>

<!-- search form -->
<form method="POST">
    <input type="hidden" name="action" value="search">

    <?php render_input([
        "type" => "text",
        "name" => "job_title",
        "label" => "Job Title (partial match)"
    ]); ?>

    <?php render_input([
        "type" => "text",
        "name" => "username",
        "label" => "Username (partial match)"
    ]); ?>

    <?php render_button(["text" => "Search", "type" => "submit"]); ?>
</form>

<!-- rt524 12/5 toggle form
 adjusted to fit entire section into form to fix button bug -->
<form id="toggleForm" method="POST">
    <input type="hidden" name="action" value="toggle">

    <table class="table table-bordered mt-4">
        <thead>
            <th>Jobs (max 25)</th>
            <th>Users (max 25)</th>
        </thead>
        <tbody>
            <tr>
                <td>
                    <table class="table">
                        <?php foreach ($jobs as $j) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           name="jobs[]"
                                           value="<?= $j['job_id'] ?>">
                                    <label>
                                        <?= $j['job_title'] ?> (<?= $j['job_id'] ?>)<br>
                                        <small><?= $j['employer_name'] ?></small>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </td>

                <td>
                    <table class="table">
                        <?php foreach ($users as $u) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           name="users[]"
                                           value="<?= $u['id'] ?>">
                                    <label>
                                        <?= $u['username'] ?> (<?= $u['id'] ?>)
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <button type="submit" class="btn btn-primary">Apply Associations</button>
</form>


<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
