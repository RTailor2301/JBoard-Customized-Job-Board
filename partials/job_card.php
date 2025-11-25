<?php
if (!isset($data)) {
    error_log("Using job card partial without data");
    flash("Dev Alert: Job card called without data", "danger");
}
?>
<?php if (isset($data)) : ?>
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

                    <li class="list-group-item">
                        Description:
                        <br>
                        <?php se($data, "job_description", "N/A"); ?>
                    </li>

                    <li class="list-group-item">
                        Qualifications:
                        <br>
                        <?php
                        if (!empty($data["qualifications"])) {
                            foreach ($data["qualifications"] as $q) {
                                echo "- " . htmlspecialchars($q["qualification"]) . "<br>";
                            }
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </li>

                    <li class="list-group-item">
                        Responsibilities:
                        <br>
                        <?php
                        if (!empty($data["responsibilities"])) {
                            foreach ($data["responsibilities"] as $r) {
                                echo "- " . htmlspecialchars($r["responsibility"]) . "<br>";
                            }
                        } else {
                            echo "N/A";
                        }
                        ?>
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

        </div>
    </div>
<?php endif; ?>
