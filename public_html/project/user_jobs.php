<?php
require(__DIR__ . "/../../partials/nav.php");
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}

// rt524 12/2 applies the same filtering logic as landing.php
// query loads the user jobs table and matches it based on the current user's id

$allowed_columns = [
    "job_title",
    "employer_name",
    "job_city",
    "job_state",
    "job_posted_at_datetime_utc",
    "country",
    "created"
];

$sort = ["asc", "desc"];

$params = [];
// fetch saved user jobs
$query = "SELECT uj.job_id 
    FROM IT202_F25_User_Jobs uj
    WHERE uj.user_id = :user_id
    AND uj.is_active = 1";

$params[":user_id"] = get_user_id();

// Filtering logic
if (count($_GET) > 0) {

    $job_title = se($_GET, "job_title", "", false);
    if (!empty($job_title)) {
        $query .= " AND uj.job_id IN (
            SELECT job_id FROM IT202_F25_Jsearch
            WHERE job_title LIKE :job_title
        )";
        $params[":job_title"] = "%$job_title%";
    }

    $employer_name = se($_GET, "employer_name", "", false);
    if (!empty($employer_name)) {
        $query .= " AND uj.job_id IN (
            SELECT job_id FROM IT202_F25_Jsearch
            WHERE employer_name LIKE :employer_name
        )";
        $params[":employer_name"] = "%$employer_name%";
    }

    $city = se($_GET, "job_city", "", false);
    if (!empty($city)) {
        $query .= " AND uj.job_id IN (
            SELECT job_id FROM IT202_F25_Jsearch
            WHERE job_city LIKE :city
        )";
        $params[":city"] = "%$city%";
    }

    $remote = se($_GET, "job_is_remote", "", false);
    if ($remote !== "") {
        $query .= " AND uj.job_id IN (
            SELECT job_id FROM IT202_F25_Jsearch
            WHERE job_is_remote = :remote
        )";
        $params[":remote"] = $remote;
    }

    $posted_after = se($_GET, "posted_after", "", false);
    if (!empty($posted_after)) {
        $query .= " AND uj.job_id IN (
            SELECT job_id FROM IT202_F25_Jsearch
            WHERE job_posted_at_datetime_utc >= :posted_after
        )";
        $params[":posted_after"] = $posted_after;
    }

    // Sort logic
    $column = se($_GET, "column", "", false);
    if (empty($column) || !in_array($column, $allowed_columns)) {
        $column = "created";
    }

    $order = se($_GET, "order", "", false);
    if (empty($order) || !in_array($order, $sort)) {
        $order = "desc";
    }

    $query .= " ORDER BY $column $order";
}

// LIMIT
$limit = se($_GET, "limit", 10, false);
if (!empty($limit) && is_numeric($limit)) {
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    $query .= " LIMIT :limit";
    $params[":limit"] = $limit;
}

// Execute
$db = getDB();
$stmt = $db->prepare($query);
error_log("Params: " . var_export($params, true));

foreach ($params as $key => $val) {
    $type = match (true) {
        is_numeric($val) => PDO::PARAM_INT,
        is_bool($val) => PDO::PARAM_BOOL,
        is_null($val) => PDO::PARAM_NULL,
        default => PDO::PARAM_STR,
    };
    $stmt->bindValue($key, $val, $type);
}

$saved_ids = [];
try {
    $stmt->execute();
    $r = $stmt->fetchAll();
    if ($r) {
        $saved_ids = array_map(fn($row) => $row["job_id"], $r);
    }
} catch (PDOException $e) {
    error_log("Error fetching saved job IDs: " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

// Fetch full job records
$results = [];
if ($saved_ids) {
    $in = str_repeat('?,', count($saved_ids) - 1) . '?';

    $query = "
        SELECT *
        FROM IT202_F25_Jsearch
        WHERE job_id IN ($in)
    ";

    if (count($_GET) > 0) {
        // SAME SORT BLOCK AGAIN
        $column = se($_GET, "column", "", false);
        if (empty($column) || !in_array($column, $allowed_columns)) {
            $column = "created";
        }

        $order = se($_GET, "order", "", false);
        if (empty($order) || !in_array($order, $sort)) {
            $order = "desc";
        }

        $query .= " ORDER BY $column $order";
    }

    $stmt = $db->prepare($query);
    $stmt->execute($saved_ids);
    $results = $stmt->fetchAll();
}

// FORM
$cols = array_map(fn($col) => [$col => $col], $allowed_columns);
array_unshift($cols, ["" => "Select Column"]);

$orderOpts = array_map(fn($dir) => [$dir => $dir], $sort);
array_unshift($orderOpts, ["" => "Select Order"]);

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
        "label" => "Column",
        "options" => $cols,
        "value" => se($_GET, "column", "", false),
    ],
    [
        "type" => "select",
        "id" => "order",
        "name" => "order",
        "label" => "Order",
        "options" => $orderOpts,
        "value" => se($_GET, "order", "", false),
    ],
    [
        "type" => "number",
        "id" => "limit",
        "name" => "limit",
        "label" => "Limit",
        "value" => se($_GET, "limit", "10", false),
        "rules" => ["min" => 1, "max" => 100]
    ]
];
?>
<div class="container-fluid">
    <h1>My Saved Jobs</h1>
    <form>
        <div class="row">
            <?php foreach ($form as $field): ?>
                <div class="col">
                    <?php render_input($field); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
        <a href="?" class="btn btn-secondary">Reset</a>
    </form>

    <?php if (count($results) == 0): ?>
        <p>No saved jobs found</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($results as $entry): ?>
                <div class="col">
                    <?php render_job_card($entry); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


