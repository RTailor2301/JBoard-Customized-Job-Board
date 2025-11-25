<?php
require(__DIR__ . "/../../partials/nav.php");

// Allowed sortable columns
$allowed_columns = [
    "job_title",
    "employer_name",
    "job_city",
    "job_state",
    "job_posted_at_datetime_utc",
    "country"
];

$sort = ["asc", "desc"];

$params = [];
$query = "SELECT id, job_id, country, job_title, employer_name, job_publisher,
job_employment_type, job_apply_link, job_location, job_city, job_state,
job_description, job_is_remote, job_posted_at_datetime_utc, is_api
FROM `IT202_F25_Jsearch`
WHERE 1=1";

if (count($_GET) > 0) {

    // job title
    $job_title = se($_GET, "job_title", "", false);
    if (!empty($job_title)) {
        $query .= " AND job_title LIKE :job_title";
        $params[":job_title"] = "%$job_title%";
    }

    // employer
    $employer_name = se($_GET, "employer_name", "", false);
    if (!empty($employer_name)) {
        $query .= " AND employer_name LIKE :employer_name";
        $params[":employer_name"] = "%$employer_name%";
    }

    // city
    $city = se($_GET, "job_city", "", false);
    if (!empty($city)) {
        $query .= " AND job_city LIKE :city";
        $params[":city"] = "%$city%";
    }

    // remote or not remote
    $remote = se($_GET, "job_is_remote", "", false);
    if ($remote !== "") {
        $query .= " AND job_is_remote = :remote";
        $params[":remote"] = $remote;
    }

    // posted date
    $posted_after = se($_GET, "posted_after", "", false);
    if (!empty($posted_after)) {
        $query .= " AND job_posted_at_datetime_utc >= :posted_after";
        $params[":posted_after"] = $posted_after;
    }

    // sorts
    $column = se($_GET, "column", "", false);
    if (empty($column) || !in_array($column, $allowed_columns)) {
        $column = "job_posted_at_datetime_utc";
    }

    $order = se($_GET, "order", "", false);
    if (empty($order) || !in_array($order, $sort)) {
        $order = "desc";
    }

    $query .= " ORDER BY $column $order";

    // limits
    $limit = se($_GET, "limit", 10, false);
    if (!empty($limit) && is_numeric($limit)) {
        if ($limit < 1 || $limit > 100) {
            $limit = 10;
        }
        $query .= " LIMIT :limit";
        $params[":limit"] = $limit;
    }
}

$db = getDB();
$stmt = $db->prepare($query);

foreach ($params as $key => $v) {
    $type = match (true) {
        is_numeric($v) => PDO::PARAM_INT,
        is_bool($v) => PDO::PARAM_BOOL,
        is_null($v) => PDO::PARAM_NULL,
        default => PDO::PARAM_STR
    };
    $stmt->bindValue($key, $v, $type);
}

$results = [];
try {
    $stmt->execute();
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching jobs " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}

// options
$cols = array_map(fn($col) => [$col => $col], $allowed_columns);
array_unshift($cols, [""=>"Select Column"]);

$order_options = array_map(fn($col) => [$col => $col], $sort);
array_unshift($order_options, [""=>"Select Order"]);

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
    <h1>Browse Jobs</h1>
    <div>
        <form>
            <div class="row">
                <?php foreach ($form as $field): ?>
                    <div class="col">
                        <?php render_input($field); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php render_button(["text" => "Search", "type" => "submit"]); ?>
            <!-- Uses `?` to remove all query params (normal reset doesn't work here
             because a regular reset "resets" back to the values the form loaded in with.
             Sticky forms will "reset" to what was last applied) -->
            <a href="?" class="btn btn-secondary">Reset</a>
        </form>
    </div>
    <?php if (count($results) == 0): ?>
        <p>No jobs found.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($results as $job): ?>
                <div class="col">
                    <?php render_job_card($job); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>
