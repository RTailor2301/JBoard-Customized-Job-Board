<?php
// note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

// changed id logic around a bit
$id = se($_GET, "id", -1, false);
if ($id <= 0) {
    flash("Invalid id passed", "danger");
    die(header("Location: " . get_url("admin/list_jobs.php")));
}

// rt524 11/23 selects all items in all tables found so far
// fetch current stuff
$db = getDB();
$job = [];
try {
    $query = "SELECT id, job_id, country, job_title, employer_name, job_publisher,
               job_employment_type, job_apply_link, job_location, job_city,
               job_state, job_description, job_is_remote, job_posted_at_datetime_utc, is_api
                FROM IT202_F25_Jsearch
                WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) {
        flash("Job not found", "danger");
        die(header("Location: " . get_url("admin/list_jobs.php")));
    }

    // qualifications
    $stmtQ = $db->prepare("SELECT qualification_text FROM IT202_F25_Jsearch_Qualifications WHERE job_id = :job_id");
    $stmtQ->execute([":job_id" => $job["job_id"]]);
    $quals = $stmtQ->fetchAll(PDO::FETCH_COLUMN);
    if (!$quals) {
        $quals = [];
    } 

    // responsibilities
    $stmtR = $db->prepare("SELECT responsibility_text FROM IT202_F25_Jsearch_Responsibilities WHERE job_id = :job_id");
    $stmtR->execute([":job_id" => $job["job_id"]]);
    $resps = $stmtR->fetchAll(PDO::FETCH_COLUMN);
    if (!$resps) {
        $resps = [];
    }

} catch (PDOException $e) {
    error_log("Edit job error: " . $e->getMessage());
    flash("Error occurred fetching job", "danger");
    die(header("Location: " . get_url("admin/list_jobs.php")));
}

// rt524 11/23 after form submit, updates valid data in all tables, special handing for quals and resps
// update columns
if (isset($_POST["action"]) && $_POST["action"] === "update") {
    $allowed = [
        "country", "job_title", "employer_name", "job_publisher",
        "job_employment_type", "job_apply_link", "job_location", "job_city",
        "job_state", "job_description", "job_is_remote", "job_posted_at_datetime_utc"
    ];

    $updateData = [];
    foreach ($allowed as $col) {
        $updateData[$col] = $_POST[$col] ?? null;
    }

    try {
        $db->beginTransaction();

        $setParts = [];
        $params = [];
        foreach ($updateData as $col => $val) {
            $setParts[] = "`$col` = :$col";
            $params[":$col"] = $val;
        }
        $params[":id"] = $id;

        $updateQuery = "UPDATE IT202_F25_Jsearch SET " . implode(", ", $setParts) . " WHERE id = :id";
        $stmtU = $db->prepare($updateQuery);
        $stmtU->execute($params);

        $jobId = $job["job_id"];

        // Qualifications handling
        $stmtDelQ = $db->prepare("DELETE FROM IT202_F25_Jsearch_Qualifications WHERE job_id = :job_id");
        $stmtDelQ->execute([":job_id" => $jobId]);

        $newQuals = $_POST["qualifications"] ?? [];
        $newQuals = array_values(array_filter(array_map(fn($s) => trim($s), $newQuals), fn($v) => $v !== ""));
        if ($newQuals) {
            $stmtInsQ = $db->prepare("INSERT INTO IT202_F25_Jsearch_Qualifications (job_id, qualification_text) VALUES (:job_id, :text)");
            // add each qual in the updated ones
            foreach ($newQuals as $q) {
                $stmtInsQ->execute([":job_id" => $jobId, ":text" => $q]);
            }
        }

        // Responsibilities handling
        $stmtDelR = $db->prepare("DELETE FROM IT202_F25_Jsearch_Responsibilities WHERE job_id = :job_id");
        $stmtDelR->execute([":job_id" => $jobId]);

        $newResps = $_POST["responsibilities"] ?? [];
        $newResps = array_values(array_filter(array_map(fn($s) => trim($s), $newResps), fn($v) => $v !== ""));
        if ($newResps) {
            $stmtInsR = $db->prepare("INSERT INTO IT202_F25_Jsearch_Responsibilities (job_id, responsibility_text) VALUES (:job_id, :text)");
            // add each responsibility
            foreach ($newResps as $r) {
                $stmtInsR->execute([":job_id" => $jobId, ":text" => $r]);
            }
        }

        $db->commit();
        flash("Job updated successfully", "success");
        header("Location: " . get_url("admin/edit_job.php") . "?id=" . $id);
        exit;

    } catch (PDOException $e) {
        $db->rollBack();
        flash("Error updating job: " . $e->getMessage(), "danger");
        $quals = $_POST["qualifications"] ?? $quals;
        $resps = $_POST["responsibilities"] ?? $resps;
    }
}
?>

<div class="container-fluid">
    <h3>Edit Job</h3>

    <form method="POST">
        <input type="hidden" name="action" value="update">

        <div class="mb-3">
            <label for="job_id">Job ID (readonly)</label>
            <input class="form-control" id="job_id" name="job_id" type="text" value="<?php se($job, 'job_id'); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="country">Country</label>
            <input class="form-control" id="country" name="country" type="text" value="<?php se($job, 'country'); ?>">
        </div>
        <div class="mb-3">
            <label for="job_title">Job Title</label>
            <input class="form-control" id="job_title" name="job_title" type="text" value="<?php se($job, 'job_title'); ?>">
        </div>
        <div class="mb-3">
            <label for="employer_name">Employer Name</label>
            <input class="form-control" id="employer_name" name="employer_name" type="text" value="<?php se($job, 'employer_name'); ?>">
        </div>
        <div class="mb-3">
            <label for="job_publisher">Publisher</label>
            <input class="form-control" id="job_publisher" name="job_publisher" type="text" value="<?php se($job, 'job_publisher'); ?>">
        </div>
        <div class="mb-3">
            <label for="job_employment_type">Employment Type</label>
            <input class="form-control" id="job_employment_type" name="job_employment_type" type="text" value="<?php se($job, 'job_employment_type'); ?>">
        </div>
        <div class="mb-3">
            <label for="job_apply_link">Apply Link</label>
            <input class="form-control" id="job_apply_link" name="job_apply_link" type="url" value="<?php se($job, 'job_apply_link'); ?>">
        </div>
        <div class="mb-3">
            <label for="job_location">Full Job Location</label>
            <input class="form-control" id="job_location" name="job_location" type="text" value="<?php se($job, 'job_location'); ?>">
        </div>
        <div class="mb-3">
            <label for="job_city">City</label>
            <input class="form-control" id="job_city" name="job_city" type="text" value="<?php se($job, 'job_city'); ?>">
        </div>
        <div class="mb-3">
            <label for="job_state">State</label>
            <input class="form-control" id="job_state" name="job_state" type="text" value="<?php se($job, 'job_state'); ?>">
        </div>

        <div class="mb-3">
            <label for="job_description">Description</label>
            <textarea class="form-control" id="job_description" name="job_description" rows="5"><?php se($job, 'job_description'); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="job_is_remote">Remote?</label>
            <select class="form-control" id="job_is_remote" name="job_is_remote">
                <option value="0" <?= (se($job, 'job_is_remote', 0, false) == 0) ? 'selected' : '' ?>>No</option>
                <option value="1" <?= (se($job, 'job_is_remote', 0, false) == 1) ? 'selected' : '' ?>>Yes</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="job_posted_at_datetime_utc">Posted At (UTC)</label>
            <input class="form-control" id="job_posted_at_datetime_utc" name="job_posted_at_datetime_utc" type="text"
                value="<?php se($job, 'job_posted_at_datetime_utc'); ?>">
        </div>
        <div class="mb-3">
            <label for="is_api">is_api</label>
            <input class="form-control" id="is_api" name="is_api" type="text" value="<?php se($job, 'is_api'); ?>" readonly>
        </div>

        <!-- quals  -->
        <div class="mb-3">
            <label>Qualifications</label>
            <div id="qualifications">
                <?php foreach ($quals as $q): ?>
                    <div>
                        <input class="form-control" name="qualifications[]" value="<?= htmlspecialchars($q) ?>">
                        <button type="button" onclick="removeField(this)">Remove</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addQualification()">Add Qualification</button>
        </div>

        <!-- resps -->
        <div class="mb-3">
            <label>Responsibilities</label>
            <div id="responsibilities">
                <?php foreach ($resps as $r): ?>
                    <div>
                        <input class="form-control" name="responsibilities[]" value="<?= htmlspecialchars($r) ?>">
                        <button type="button" onclick="removeField(this)">Remove</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addResponsibility()">Add Responsibility</button>
        </div>

        <button type="submit">Save Changes</button>
        <a href="<?= get_url("admin/list_jobs.php") ?>">Back</a>
    </form>
</div>

<script>

// rt524 11/23 JS functions that allows for adding and removing boxes for array fields
// uses appendChild and remove parent element

function addQualification() {
    const field = document.getElementById("qualifications");
    const row = document.createElement("div");

    row.innerHTML = `
        <input class="form-control" name="qualifications[]" type="text">
        <button type="button" onclick="removeField(this)">Remove</button>
    `;
    field.appendChild(row);
}

function addResponsibility() {
    const field = document.getElementById("responsibilities");
    const row = document.createElement("div");

    row.innerHTML = `
        <input class="form-control" name="responsibilities[]" type="text">
        <button type="button" onclick="removeField(this)">Remove</button>
    `;
    field.appendChild(row);
}

function removeField(btn) {
    btn.parentElement.remove();
}
</script>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>