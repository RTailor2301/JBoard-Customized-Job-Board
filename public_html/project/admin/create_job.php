<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("landing.php");
}
?>

<?php

//TODO handle stock fetch
// rt524 11/22
// iterate over the values given from "result" after running the query
// insert using filteredJob to ensure the arrays in quals and reqs don't get passed through
// insert quals and reqs separately into their own table with the corresponding job_id
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $jsearch_query =  (se($_POST, "jsearch_query", "", false));
    $jobs = [];

    if ($action === "fetch") {
        if ($jsearch_query) {
            $result = fetch_jobs($jsearch_query);

            error_log("Data from API" . var_export($result, true));
            if ($result) {
                $jobs = $result; // helper function already sets "is_api"
            } else {
                flash("No result from API", "warning");
            }
        } else {
            flash("You must provide a jsearch_query", "warning");
        }
    } else if ($action === "create") {
        $hasError = false;

        // check the required fields
        $job_id = se($_POST, "job_id", "", false);
        $job_title = se($_POST, "job_title", "", false);
        $job_posted_at = se($_POST, "job_posted_at_datetime_utc", "", false);

        if (empty($job_id)) {
            flash("Job ID must not be empty.", "danger");
            $hasError = true;
        }

        if (empty($job_title)) {
            flash("Job Title must not be empty.", "danger");
            $hasError = true;
        }

        if (empty($job_posted_at)) {
            flash("Posted At date must not be empty.", "danger");
            $hasError = true;
        }

        if (!empty($_POST["job_id"]) && strlen($_POST["job_id"]) > 255) {
            flash("Job ID must be 255 characters or less.", "danger");
            $hasError = true;
        }

        if (!empty($_POST["job_title"]) && strlen($_POST["job_title"]) < 2) {
            flash("Job Title must be at least 2 characters long.", "danger");
            $hasError = true;
        }

        if (!empty($_POST["employer_name"]) && strlen($_POST["employer_name"]) > 255) {
            flash("Employer Name must be 255 characters or less.", "danger");
            $hasError = true;
        }

        if (!empty($_POST["country"]) && (strlen($_POST["country"]) < 2 || strlen($_POST["country"]) > 100)) {
            flash("Country must be between 2 and 100 characters.", "danger");
            $hasError = true;
        }

        if (isset($_POST["job_is_remote"]) && !in_array($_POST["job_is_remote"], ["0", "1"])) {
            flash("Remote must be either 'Yes' or 'No'.", "danger");
            $hasError = true;
        }

        if (!empty($_POST["job_posted_at_datetime_utc"])) {
            $dt = date_create($_POST["job_posted_at_datetime_utc"]);
            if (!$dt) {
                flash("Posted At date is invalid.", "danger");
                $hasError = true;
            }
        }
        if ($hasError) {
            return; // stop execution before DB insert
        }
        $jobData = [];
        foreach ($_POST as $k => $v) {
            // remove keys that aren't part of your data
            // this is both for security and for our dynamic DB logic to work correctly
            // the keys must match the column names of your table

            if ($k === "qualifications" || $k === "responsibilities") {
                if (is_array($v)) {
                    $jobData[$k] = array_filter($v);
                }
                continue;
            }

            if (in_array($k, [
                "job_id", "country", "job_title", "employer_name", "job_publisher",
                "job_employment_type", "job_apply_link", "job_location", "job_city",
                "job_state", "job_description", "job_is_remote",
                "job_posted_at_datetime_utc"
            ])) {
                $jobData[$k] = $v;
            }
        }
        $jobData["is_api"] = 0;
        $jobs = [$jobData]; 
        error_log("Cleaned up POST: " . var_export($jobs, true));
    }

    //insert data - Below should only really need the table name changes
    // the query building should work for all regular inserts
    if (count($jobs) > 0) {
        $db = getDB();

        foreach ($jobs as $job) {
            // Filter only columns that exist in main table
            $validColumns = [
                "job_id", "country", "job_title", "employer_name", "job_publisher",
                "job_employment_type", "job_apply_link", "job_location", "job_city",
                "job_state", "job_description", "job_is_remote", "job_posted_at_datetime_utc", "is_api"
            ];

            $filteredJob = [];
            foreach ($job as $k => $v) {
                if (in_array($k, $validColumns)) {
                    
                    if (is_array($v)) $v = json_encode($v);

                    // conversion to date and time
                    if ($k === "job_posted_at_datetime_utc" && !empty($v)) {
                        $v = date('Y-m-d H:i:s', strtotime($v));
                    }

                    // cast the boolean to int
                    if ($k === "job_is_remote") {
                        $v = (int)$v;
                    }

                    $filteredJob[$k] = $v;
                }
            }
            // insertion into jobs table
            $query = "INSERT INTO `IT202_F25_Jsearch`";
            $columns = [];
            $params =[];
            foreach ($filteredJob as $col => $val) {
                $columns[] = "`" . $col . "`";
                $params[]  = ":" . $col;
            }
            $query .= "(" . join(",", $columns) . ")";
            $query .= "VALUES (" . join(",", $params) . ")";

            error_log("Query: " . $query);
            error_log("Params: " . var_export($filteredJob, true));

            // add to the Qualifications and Responsibilities table for each job fetched
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($filteredJob);
                flash("Inserted job " . $job["job_id"], "success");

                // Insert qualifications
                if (!empty($job["qualifications"]) && is_array($job["qualifications"])) {
                    foreach ($job["qualifications"] as $q) {
                        $stmtQualifications = $db->prepare(
                            "INSERT INTO IT202_F25_Jsearch_Qualifications (job_id, qualification_text) 
                            VALUES (:job_id, :text)"
                        );
                        $stmtQualifications->execute([":job_id" => $job["job_id"], ":text" => $q]);
                    }
                }

                // Insert responsibilities
                if (!empty($job["responsibilities"]) && is_array($job["responsibilities"])) {
                    foreach ($job["responsibilities"] as $r) {
                        $stmtResponsibilities = $db->prepare(
                            "INSERT INTO IT202_F25_Jsearch_Responsibilities (job_id, responsibility_text) 
                            VALUES (:job_id, :text)"
                        );
                        $stmtResponsibilities->execute([":job_id" => $job["job_id"], ":text" => $r]);
                    }
                }

            } catch (PDOException $e) {
                error_log("Insert error: " . $e->getMessage());
                // catches duplicate error
                if ($e->getCode() == 23000) {
                    flash("Error inserting job {$job['job_id']}: Duplicate", "danger");
                }
                else {
                    flash("Error inserting job {$job['job_id']}: " . $e->getMessage(), "danger");
                }
                
            }
        }
    } else {
        flash("No jobs fetched or provided", "warning");
    }
}

//TODO handle manual create stock
// rt524 11/22 changed values to match table, made query/required and other fields match table restrictions
?>
<div class="container-fluid">
    <h3>Create or Fetch Job</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('fetch')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-success" href="#" onclick="switchTab('create')">Create</a>
        </li>
    </ul>

    <div id="fetch" class="tab-target">
        <form method="POST" onsubmit="return validate(this)">
            <div>
                <label for="jsearch_query">JSearch Query</label>
                <input type="search" name="jsearch_query" id="jsearch_query" placeholder="Search jobs..." required>
            </div>
            <input type="hidden" name="action" value="fetch">
            <input type="submit" value="Fetch" class="btn btn-primary">
        </form>
    </div>
    <script>
        function validate(form) {
            let isValid = true;
            let input = form.jsearch_query.value;
            if (input.length < 2) {
                flash("Query should be longer than 1 character", "warning");
                isValid = false;
            }
            return isValid;
        }
    </script>
    <div id="create" style="display:none;" class="tab-target">
        <form method="POST" onsubmit="return validateCreate(this)">
            <div class="mb-3">
                <label for="job_id">Job ID (unique)</label>
                <input type="text" name="job_id" id="job_id" required maxlength="255">
            </div>
            <div class="mb-3">
                <label for="country">Country</label>
                <input type="text" name="country" id="country" minlength="2" maxlength = "100">
            </div>
            <div class="mb-3">
                <label for="job_title">Job Title</label>
                <input type="text" name="job_title" id="job_title" maxlength="255">
            </div>
            <div class="mb-3">
                <label for="employer_name">Employer Name</label>
                <input type="text" name="employer_name" id="employer_name" maxlength="255">
            </div>
            <div class="mb-3">
                <label for="job_publisher">Publisher</label>
                <input type="text" name="job_publisher" id="job_publisher" maxlength="255">
            </div>
            <div class="mb-3">
                <label for="job_employment_type">Employment Type</label>
                <input type="text" name="job_employment_type" id="job_employment_type" maxlength="100">
            </div>
            <div class="mb-3">
                <label for="job_apply_link">Apply Link</label>
                <input type="url" name="job_apply_link" id="job_apply_link">
            </div>
            <div class="mb-3">
                <label for="job_location">Full Job Location</label>
                <input type="text" name="job_location" id="job_location" maxlength="255">
            </div>
            <div class="mb-3">
                <label for="job_city">City</label>
                <input type="text" name="job_city" id="job_city" maxlength="128">
            </div>
            <div class="mb-3">
                <label for="job_state">State</label>
                <input type="text" name="job_state" id="job_state" maxlength="128">
            </div>
            <div class="mb-3">
                <label for="job_description">Description</label>
                <textarea name="job_description" id="job_description"></textarea>
            </div>
            <div class="mb-3">
                <label for="job_is_remote">Remote?</label>
                <select name="job_is_remote" id="job_is_remote">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Qualifications</label>
                <div id="qualifications" class="d-flex flex-column gap-2"> 
                    <input type="text" name="qualifications[]" placeholder="Enter qualification">
                </div>
                <button type="button" onclick="addQualification()" class="btn btn-secondary btn-sm">Add More</button>
            </div>
            <div class="mb-3">
                <label>Responsibilities</label>
                <div id="responsibilities" class="d-flex flex-column gap-2"> 
                    <input type="text" name="responsibilities[]" placeholder="Enter responsibility">
                </div>
                <button type="button" onclick="addResponsibility()" class="btn btn-secondary btn-sm">Add More</button>
            </div>
            <div class="mb-3">
                <label for="job_posted_at_datetime_utc">Posted At (UTC)</label>
                <input type="datetime-local" name="job_posted_at_datetime_utc" id="job_posted_at_datetime_utc" required>
            </div>
            <input type="hidden" name="action" value="create">
            <button type="submit" class="btn btn-primary">Create Job</button>
        </form>
        <script>
            // rt524 11/24 validation of form, checks req fields and html validations again
            function validateCreate(form) {
                let isValid = true;

                // Required: job_id and posted date
                if (form.job_id.value.trim().length === 0) {
                    flash("Job ID is required", "warning");
                    isValid = false;
                }

                if (form.job_posted_at_datetime_utc.value.trim().length === 0) {
                    flash("Posted At date is required", "warning");
                    isValid = false;
                }

                if (form.job_title.value.trim().length < 2) {
                    flash("Job Title should be at least 2 characters", "warning");
                    isValid = false;
                }

                let url = form.job_apply_link.value.trim();
                if (url.length > 0) {
                    try {
                        new URL(url); 
                    } catch (e) {
                        flash("Apply Link must be a valid URL", "warning");
                        isValid = false;
                    }
                }

                return isValid;
            }
            </script>
    </div>
</div>

<script>
// rt524 11/22
// appends more fields when the button is clicked, adds to array using name[]
function addQualification() {
    let field = document.getElementById("qualifications");
    let input = document.createElement("input");
    input.type = "text";
    input.name = "qualifications[]";
    field.appendChild(input);
}
function addResponsibility() {
    let field = document.getElementById("responsibilities");
    let input = document.createElement("input");
    input.type = "text";
    input.name = "responsibilities[]";
    field.appendChild(input);
}
function switchTab(tab) {
    let target = document.getElementById(tab);
    if (target) {
        let eles = document.getElementsByClassName("tab-target");
        for (let ele of eles) {
            ele.style.display = (ele.id === tab) ? "block" : "none";
        }
    }
}
</script>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
