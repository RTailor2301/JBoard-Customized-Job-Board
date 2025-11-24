<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}


// rt524 11/22
// include all tables with a left join, use group concat with a separator to list all quals/respons
$query = "SELECT j.id, j.job_id, j.country, j.job_title, 
       j.employer_name, j.job_publisher, j.job_employment_type,
       j.job_apply_link, j.job_location, j.job_city, j.job_state,
       j.job_description, j.job_is_remote, j.job_posted_at_datetime_utc, j.is_api,

       GROUP_CONCAT(DISTINCT q.qualification_text SEPARATOR ' // ') AS qualifications,
       GROUP_CONCAT(DISTINCT r.responsibility_text SEPARATOR ' // ') AS responsibilities

        FROM IT202_F25_Jsearch AS j
        LEFT JOIN IT202_F25_Jsearch_Qualifications AS q 
            ON j.job_id = q.job_id
        LEFT JOIN IT202_F25_Jsearch_Responsibilities AS r 
            ON j.job_id = r.job_id

        GROUP BY j.id ORDER BY j.created DESC LIMIT 25";

$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute();
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching stocks " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}
?>
<div class="container-fluid">
    <h3>List Jobs</h3>
    <?php if (count($results) == 0) : ?>
    <p>No results to show</p>
<?php else : ?>
    <table class="table">
        <?php foreach ($results as $index => $record) : ?>
            <?php if ($index == 0) : ?>
                <thead>
                    <?php foreach ($record as $column => $value) : ?>
                        <th><?php se($column); ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </thead>
            <?php endif; ?>
            <tr>
                <?php foreach ($record as $column => $value) : ?>
                    <td><?php se($value, null, "N/A"); ?></td>
                <?php endforeach; ?>


                <td>
                    <a href="<?php echo get_url("admin/edit_job.php");?>?id=<?php se($record, "id"); ?>">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>