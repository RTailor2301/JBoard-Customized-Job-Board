<?php
require(__DIR__ . "/../../partials/nav.php");

$id = se($_GET, "id", "", false);
if (empty($id)) {
    flash("Job ID not provided", "warning");
    die(header("Location:" . get_url("admin/list_jobs.php")));
}
if ($id <= 0) {
    flash("Invalid id passed", "danger");
    die(header("Location: " . get_url("admin/list_jobs.php")));
}

$db = getDB();

// rt524 11/24 check if admin and add delete button
if (isset($_POST["delete"]) && has_role("Admin")) {
    try {
        $stmt = $db->prepare("DELETE FROM IT202_F25_Jsearch WHERE id = :id");
        $stmt->execute([":id" => $id]);
        flash("Job deleted successfully", "success");
        die(header("Location:" . get_url("landing.php")));
    } catch (PDOException $e) {
        error_log("Delete error: " . $e->getMessage());
        flash("Error deleting job", "danger");
    }
}

// query for job details
$query = "SELECT j.id, j.job_id, j.country, j.job_title, j.employer_name,
          j.job_publisher, j.job_employment_type, j.job_apply_link, j.job_location,
          j.job_city, j.job_state, j.job_description, j.job_is_remote, 
          j.job_posted_at_datetime_utc, j.is_api,
          GROUP_CONCAT(DISTINCT q.qualification_text SEPARATOR '||') AS qualifications,
          GROUP_CONCAT(DISTINCT r.responsibility_text SEPARATOR '||') AS responsibilities
          FROM IT202_F25_Jsearch AS j
          LEFT JOIN IT202_F25_Jsearch_Qualifications AS q ON j.job_id = q.job_id
          LEFT JOIN IT202_F25_Jsearch_Responsibilities AS r ON j.job_id = r.job_id
          WHERE j.id = :id
          GROUP BY j.id
          LIMIT 1";

$stmt = $db->prepare($query);
$stmt->execute([":id" => $id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    flash("Job not found", "warning");
    die(header("Location:" . get_url("admin/list_jobs.php")));
}

// same logic as job list
$quals = !empty($job["qualifications"]) ? explode("||", $job["qualifications"]) : [];
$resp = !empty($job["responsibilities"]) ? explode("||", $job["responsibilities"]) : [];
?>


<div class="container-fluid">
    <h3>Job Details</h3>
    <div class="card mx-auto my-3" style="width: 30rem;">
        <div class="card-body">
            <h5 class="card-title"><?php se($job, "job_title", "Job Title"); ?></h5>
            <p><strong>Employer:</strong> <?php se($job, "employer_name", "N/A"); ?></p>
            <p><strong>Publisher:</strong> <?php se($job, "job_publisher", "N/A"); ?></p>
            <p><strong>Type:</strong> <?php se($job, "job_employment_type", "N/A"); ?></p>
            <p><strong>Location:</strong> <?php se($job, "job_city", "N/A"); ?>, <?php se($job, "job_state", "N/A"); ?></p>
            <p><strong>Country:</strong> <?php se($job, "country", "N/A"); ?></p>
            <p><strong>Remote:</strong> <?php echo (($job["job_is_remote"] ?? 0) == 1) ? "Yes" : "No"; ?></p>
            <p><strong>Posted:</strong> <?php se($job, "job_posted_at_datetime_utc", "N/A"); ?></p>
            <p><strong>Description:</strong><br><?php se($job, "job_description", "N/A"); ?></p>
            <p><strong>Qualifications:</strong><br>
                <?php
                if (!empty($quals)) {
                    foreach ($quals as $q) {
                        echo "- " . htmlspecialchars($q) . "<br>";
                    }
                } else {
                    echo "N/A";
                }
                ?>
            </p>
            <p><strong>Responsibilities:</strong><br>
                <?php
                if (!empty($resp)) {
                    foreach ($resp as $r) {
                        echo "- " . htmlspecialchars($r) . "<br>";
                    }
                } else {
                    echo "N/A";
                }
                ?>
            </p>

            <?php if (!empty($job["job_apply_link"])) : ?>
                <a href="<?php se($job, "job_apply_link"); ?>" target="_blank" class="btn btn-primary mt-2">
                    Apply Now
                </a>
            <?php endif; ?>

            <?php if (has_role("Admin")) : ?>
                <a href="<?php echo get_url("admin/edit_job.php"); ?>?id=<?php se($job, "id"); ?>"
                    class="btn btn-warning mt-2">
                    Edit
                </a>

                </a>
                <form method="POST" style="display:inline-block;">
                    <button type="submit" name="delete" class="btn btn-danger mt-2"
                            onclick="return confirm('Are you sure you want to delete this job?');">
                        Delete
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>
