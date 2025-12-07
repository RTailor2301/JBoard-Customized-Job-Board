<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("landing.php");
}

// rt524 12/5 mainly the same as all user assoc but with landing.php filters applied for easier search
// in query, use WHERE NOT EXISTS to find the job ids where a user does not have it active

$db = getDB();

$stmt = $db->prepare("SELECT COUNT(*) AS total
    FROM IT202_F25_Jsearch j
    WHERE NOT EXISTS (
        SELECT 1
        FROM IT202_F25_User_Jobs uj
        WHERE uj.job_id = j.id AND uj.is_active = 1
    )
");
$stmt->execute();
$total_jobs_without_users = $stmt->fetchColumn();

$allowed_columns = [
    "job_title",
    "employer_name",
    "job_city",
    "job_state",
    "job_posted_at_datetime_utc",
    "country"
];

$sort = ["asc", "desc"];
$cols = [];
foreach ($allowed_columns as $col) {
    $cols[] = [$col => $col];
}


$order_options = [
    ["asc" => "Ascending"],
    ["desc" => "Descending"]
];


$params = [];

$base_query = "SELECT 
        j.id AS job_id,
        j.job_title,
        j.employer_name,
        j.job_location,
        j.job_city,
        j.job_state,
        j.country,
        j.job_description,
        j.job_posted_at_datetime_utc,
        j.created,
        (
            SELECT COUNT(*) 
            FROM IT202_F25_User_Jobs uj
            WHERE uj.job_id = j.id AND uj.is_active = 1
        ) AS assoc_count
    FROM IT202_F25_Jsearch j
    WHERE NOT EXISTS (
        SELECT 1 
        FROM IT202_F25_User_Jobs uj2
        WHERE uj2.job_id = j.id AND uj2.is_active = 1
    )
    AND 1=1
";

// landing.php filtering
if (count($_GET) > 0) {

    // job_title
    $job_title = se($_GET, "job_title", "", false);
    if (!empty($job_title)) {
        $base_query .= " AND j.job_title LIKE :job_title";
        $params[":job_title"] = "%$job_title%";
    }

    // employer
    $employer_name = se($_GET, "employer_name", "", false);
    if (!empty($employer_name)) {
        $base_query .= " AND j.employer_name LIKE :employer_name";
        $params[":employer_name"] = "%$employer_name%";
    }

    // city
    $city = se($_GET, "job_city", "", false);
    if (!empty($city)) {
        $base_query .= " AND j.job_city LIKE :city";
        $params[":city"] = "%$city%";
    }

    // remote
    $remote = se($_GET, "job_is_remote", "", false);
    if ($remote !== "") {
        $base_query .= " AND j.job_is_remote = :remote";
        $params[":remote"] = $remote;
    }

    // posted
    $posted_after = se($_GET, "posted_after", "", false);
    if (!empty($posted_after)) {
        $base_query .= " AND j.job_posted_at_datetime_utc >= :posted_after";
        $params[":posted_after"] = $posted_after;
    }

    // sorting
    $column = se($_GET, "column", "", false);
    if (empty($column) || !in_array($column, $allowed_columns)) {
        $column = "job_posted_at_datetime_utc";
    }

    $order = se($_GET, "order", "", false);
    if (empty($order) || !in_array($order, $sort)) {
        $order = "desc";
    }

    $base_query .= " ORDER BY $column $order";

    // limit
    $limit = se($_GET, "limit", 10, false);
    if (!empty($limit) && is_numeric($limit)) {
        if ($limit < 1 || $limit > 100) $limit = 10;
        $base_query .= " LIMIT :limit";
        $params[":limit"] = $limit;
    }

} else {
    // added a default
    $base_query .= " ORDER BY job_posted_at_datetime_utc DESC LIMIT 10";
}

$stmt = $db->prepare($base_query);

foreach ($params as $key => $v) {
    $type = match (true) {
        is_numeric($v) => PDO::PARAM_INT,
        is_bool($v) => PDO::PARAM_BOOL,
        is_null($v) => PDO::PARAM_NULL,
        default => PDO::PARAM_STR,
    };
    $stmt->bindValue($key, $v, $type);
}

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_items = count($results);
$form = [
    [
        "type" => "text",
        "id" => "job_title",
        "name" => "job_title",
        "label" => "Job Title",
        "value" => se($_GET, "job_title", "", false),
    ],
    [
        "type" => "text",
        "id" => "employer_name",
        "name" => "employer_name",
        "label" => "Employer",
        "value" => se($_GET, "employer_name", "", false),
    ],
    [
        "type" => "text",
        "id" => "job_city",
        "name" => "job_city",
        "label" => "City",
        "value" => se($_GET, "job_city", "", false),
    ],
    [
        "type" => "select",
        "id" => "job_is_remote",
        "name" => "job_is_remote",
        "label" => "Remote",
        "options" => [
            ["" => "Any"],
            ["1" => "Remote"],
            ["0" => "Not Remote"],
        ],
        "value" => se($_GET, "job_is_remote", "", false),
    ],
    [
        "type" => "date",
        "id" => "posted_after",
        "name" => "posted_after",
        "label" => "Posted After",
        "value" => se($_GET, "posted_after", "", false),
    ],
    [
        "type" => "select",
        "id" => "column",
        "name" => "column",
        "label" => "Sort Column",
        "options" => $cols,
        "value" => se($_GET, "column", "", false),
    ],
    [
        "type" => "select",
        "id" => "order",
        "name" => "order",
        "label" => "Order",
        "options" => $order_options,
        "value" => se($_GET, "order", "", false),
    ],
    [
        "type" => "number",
        "id" => "limit",
        "name" => "limit",
        "label" => "Limit",
        "value" => se($_GET, "limit", 10, false),
        "rules" => ["min"=>1, "max"=>100]
    ]
];
?>
<div class="container-fluid">
    <h1>
        Jobs Without User Associations
        <h2>
            <strong>Total jobs with 0 users: </strong><?= $total_jobs_without_users ?><br>
            <strong>Total shown on page: </strong><?= $total_items ?>
        </h2>
    </h1>
    
    <!-- IDENTICAL FILTER FORM FROM landing.php -->
    <form>
        <div>
            <label>Job Title</label>
            <input type="text" class="form-control" name="job_title"
                   value="<?= se($_GET, "job_title", "", false) ?>">
        </div>

        <div>
            <label>Employer</label>
            <input type="text" class="form-control" name="employer_name"
                   value="<?= se($_GET, "employer_name", "", false) ?>">
        </div>

        <div>
            <label>City</label>
            <input type="text" class="form-control" name="job_city"
                   value="<?= se($_GET, "job_city", "", false) ?>">
        </div>

        <div>
            <label>Remote?</label>
            <select class="form-select" name="job_is_remote">
                <option value="">Any</option>
                <option value="1" <?= se($_GET, "job_is_remote", "", false) === "1" ? "selected" : "" ?>>Remote</option>
                <option value="0" <?= se($_GET, "job_is_remote", "", false) === "0" ? "selected" : "" ?>>Not Remote</option>
            </select>
        </div>

        <div>
            <label>Posted After</label>
            <input type="date" class="form-control" name="posted_after"
                   value="<?= se($_GET, "posted_after", "", false) ?>">
        </div>

        <div>
            <label>Sort Column</label>
            <select class="form-select" name="column">
                <option value="">Select Column</option>
                <?php foreach ($allowed_columns as $col): ?>
                    <option value="<?= $col ?>" <?= ($col === se($_GET, "column", "", false)) ? "selected" : "" ?>>
                        <?= $col ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Order</label>
            <select class="form-select" name="order">
                <option value="">Select Order</option>
                <option value="asc" <?= se($_GET, "order", "", false) === "asc" ? "selected" : "" ?>>ASC</option>
                <option value="desc" <?= se($_GET, "order", "", false) === "desc" ? "selected" : "" ?>>DESC</option>
            </select>
        </div>

        <div>
            <label>Limit</label>
            <input type="number" class="form-control" name="limit" min="1" max="100"
                   value="<?= se($_GET, "limit", 10, false) ?>">
        </div>

        <div>
            <button class="btn btn-primary w-100">Search</button>
        </div>

        <div>
            <a href="?" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>

    <hr>

    <!-- results table, similar type of structure to landing -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Job ID</th>
                    <th>Job Title</th>
                    <th>Employer</th>
                    <th>Location</th>
                    <th>Created</th>
                    <th>Total Users</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr><td colspan="7" class="text-center">No jobs found</td></tr>
                <?php else: ?>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?= se($r['job_id']) ?></td>
                            <td><?= se($r['job_title']) ?></td>
                            <td><?= se($r['employer_name']) ?></td>
                            <td><?= se($r['job_city']) ?>, <?= se($r['job_state']) ?></td>
                            <td><?= se($r['created']) ?></td>
                            <td>0</td>
                            <td>
                                <a class="btn btn-info btn-sm"
                                   href="<?= get_url("job_details.php?id=" . $r['job_id']) ?>">
                                   View Job
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
