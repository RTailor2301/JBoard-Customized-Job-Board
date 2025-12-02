<?php
if (!isset($data)) {
    error_log("Using job card partial without data");
    flash("Dev Alert: Job card called without data", "danger");
}
?>
<?php if (isset($data)) : ?>

    <?php
    // rr524 12/2
    // check if job saved already
    $is_saved = false;
    if (is_logged_in()) {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                SELECT 1 FROM IT202_F25_User_Jobs 
                WHERE user_id = :uid 
                  AND job_id = :jid 
                  AND is_active = 1
            ");
            $stmt->execute([
                ":uid" => get_user_id(),
                ":jid" => $data["job_id"]
            ]);
            $is_saved = $stmt->fetch() ? true : false;
        } catch (Exception $e) {
            error_log("Error checking saved job: " . var_export($e, true));
        }
    }
    ?>

    <div class="card mx-auto my-3" style="width: 20rem;">

        <!-- Icon area -->
        <div class="ratio ratio-1x1 d-flex justify-content-center" style="height:64px">
            <img src="https://www.iconpacks.net/icons/2/free-search-icon-2903-thumb.png"
                 class="img-fluid object-fit-contain"
                 alt="Job Icon">
        </div>

        <div class="card-body">
            <h5 class="card-title">
                <?php se($data, "job_title", "Job Title"); ?>
            </h5>

            <div class="card-text">
                <ul class="list-group list-group-flush">

                    <li class="list-group-item">
                        Employer:
                        <?php se($data, "employer_name", "N/A"); ?>
                    </li>

                    <li class="list-group-item">
                        Publisher:
                        <?php se($data, "job_publisher", "N/A"); ?>
                    </li>

                    <li class="list-group-item">
                        Type:
                        <?php se($data, "job_employment_type", "N/A"); ?>
                    </li>

                    <li class="list-group-item">
                        Location:
                        <?php se($data, "job_city", "N/A"); ?>,
                        <?php se($data, "job_state", "N/A"); ?>
                    </li>

                    <li class="list-group-item">
                        Country:
                        <?php se($data, "country", "N/A"); ?>
                    </li>

                    <li class="list-group-item">
                        Remote:
                        <?php echo (($data["job_is_remote"] ?? 0) == 1) ? "Yes" : "No"; ?>
                    </li>

                    <li class="list-group-item">
                        Posted:
                        <?php se($data, "job_posted_at_datetime_utc", "N/A"); ?>
                    </li>
                </ul>
            </div>

            <?php if (!empty($data["job_apply_link"])) : ?>
                <a href="<?php se($data, "job_apply_link"); ?>" target="_blank" class="btn btn-primary mt-3">
                    Apply Now
                </a>
            <?php endif; ?>

            <!-- Details button added -->
            <a href="<?php echo get_url("job_details.php"); ?>?id=<?php se($data, "id"); ?>" 
               class="btn btn-secondary mt-2">
                Details
            </a>

            <!-- Save jobs button, only interactable if not saved -->
            <?php if (is_logged_in()) : ?>
                <?php if ($is_saved): ?>
                    <button class="btn btn-success mt-2" disabled>Saved</button>
                <?php else: ?>
                    <form method="POST" action="<?php echo get_url('save_job.php'); ?>">
                        <input type="hidden" name="job_id" value="<?php se($data, 'job_id'); ?>">
                        <button type="submit" class="btn btn-success mt-2">Save Job</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
