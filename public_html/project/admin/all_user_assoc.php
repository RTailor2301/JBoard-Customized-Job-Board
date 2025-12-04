<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

// rt524 12/4 show total rows

$db = getDB();
$stmt = $db->prepare("SELECT COUNT(*) FROM IT202_F25_User_Jobs");
$stmt->execute();
$total_saved = $stmt->fetchColumn();

// rt524 12/4 show all associations and the number of users who saved that job
// added timestamp of when it was saved

// username filtering with partial match
$filter_username = trim($_GET["username"] ?? "");
$filter_clause = "";
$params = [];

if ($filter_username) {
    $filter_clause = " AND u.username LIKE :uname ";
    $params[":uname"] = "%$filter_username%";
}

$total = $db->prepare("SELECT COUNT(*) FROM IT202_F25_User_Jobs WHERE is_active = 1");
$total->execute();
$total_associations = $total->fetchColumn();

$query = "SELECT 
    uj.id AS assoc_id,
    j.id as jid,
    uj.user_id,
    uj.job_id,
    uj.created AS saved_at,
    u.username,
    j.job_title,
    j.employer_name,
    j.job_location,
    uj.is_active,
    (
        SELECT COUNT(*)
        FROM IT202_F25_User_Jobs uj2
        WHERE uj2.job_id = uj.job_id
    ) AS total_users
FROM IT202_F25_User_Jobs uj
JOIN Users u ON u.id = uj.user_id
JOIN IT202_F25_Jsearch j ON j.job_id = uj.job_id
WHERE 1=1 AND uj.is_active = 1
$filter_clause
ORDER BY uj.created DESC
LIMIT 500;
";

$stmt = $db->prepare($query);
$results = [];

try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching associations " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

$filtered_count = count($results);

?>

<div class="container-fluid">
    <h1>
        User-Job Associations
        <h2>
            <strong>Total:</strong> <?= $total_associations ?> <br>
            <strong>Showing:</strong> <?= $filtered_count ?> job(s)
        </h2>
    </h1>

    <form method="GET" class="row mb-3">
        <div class="col-auto">
            <label class="form-label">Filter by Username (partial match):</label>
            <input type="text" name="username" class="form-control" placeholder="enter username" value="<?php se($filter_username); ?>">
        </div>
        <div class="col-auto align-self-end">
            <button class="btn btn-primary">Apply Filter</button>
        </div>

        <!-- rt524 12/4 appends the filter to url for deleting -->
        <?php if ($filter_username): ?>
            <div class="col-auto align-self-end">
                <a href="<?php echo get_url('admin/delete_associations_by_user.php?username=' . urlencode($filter_username)); ?>"
                   class="btn btn-danger"
                   onclick="return confirm('Delete ALL associations for all matching users?');">
                    Delete All for Matching Users
                </a>
            </div>
        <?php endif; ?>

    </form>


    <?php if ($filtered_count == 0) : ?>
        <p>No associations found.</p>
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
                        <a href="<?php echo get_url('profile.php?id=' . $record['user_id']); ?>">
                            View User
                        </a>
                        |
                        <a href="<?php echo get_url('job_details.php?id=' . $record['jid']); ?>">
                            View Job
                        </a>
                        |
                        <a href="<?php echo get_url('admin/delete_association.php?assoc_id=' . $record['assoc_id']); ?>"
                           class="text-danger"
                           onclick="return confirm('Remove this association?');">
                            Delete
                        </a>
                    </td>
                </tr>

            <?php endforeach; ?>
        </table>

    <?php endif; ?>
</div>


<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
