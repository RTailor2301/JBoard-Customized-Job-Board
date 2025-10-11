<?php
require_once(__DIR__ . "/../../../lib/db.php"); ?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"]; //hint: must be a valid MySQL date format
    $assigned = $_GET["assigned"]; // Must be "self" or a valid format (not empty or equivalent)

    $is_valid = true;
    // TODO Validate the incoming data for correct format based on the SQL table definition.
    // When not valid, provide a user-friendly message of what specifically was wrong and set $is_valid to false.
    // Assigned should check for "self" if a valid format/value isn't provided.
    // Start validations
    // can edit here
    
    // rt524 10/11/25 
    // check if due is 8 chars long (already in DATE format) and if assigned is empty/blank
    if (strlen($due) != 10) {
        echo "Date is not valid. Please provide a valid date.";
        $is_valid = false;
    }
    if (empty(trim($assigned))) {
        echo "Assigned is not valid. Defaulting to 'self'";
        $assigned = "self";
        $is_valid = false;
    }
    // End validations
    
    if ($is_valid) {
        /*
        Design a query to insert the incoming data to the proper columns.
        Ensure valid and proper PDO named placeholders are used.
        https://phpdelusions.net/pdo

        rt524 10/11/25 use INSERT INTO tablename (col1, col2, col3) VALUES (task, due, assignment) for query
        in params, define the associative array
        */
        $query = "INSERT INTO M4_Todos (task, due, assigned) VALUES (:task, :due, :assigned)"; // edit this
        $params = [
            ":task" => $task,
            ":due" => $due,
            ":assigned" => $assigned
        ]; // Apply the proper PDO placeholder to variable mapping here
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);
            if ($r) {
                echo "Inserted new Todo with id " . $db->lastInsertId();
            } else {
                echo "Failed to insert";
            }
        } catch (PDOException $e) {
            // extra credit
            // check if the exception was related to a unique constraint
            // provide an appropriate user-friendly message for this scenario
            // Otherwise show the default message below
            // rt524 10/11/25 
            echo "There was an error inserting the record; check the logs (terminal)";
            echo $e;
            error_log("Insert Error: " . var_export($e, true)); // shows in the terminal
        }
    } else {
        error_log("Creation input wasn't valid");
    }
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Create ToDo </h2>
        <form>
            <!-- design the form with proper labels and input fields with the correct types based on the SQL table.
             Wrap each label/input pair in a div tag.
             For "Assigned" ensure the default value is "self". -->
            <!-- rt524 10/11/25 Create a form based on the task, due, and assigned column names
             Ensure that task's input is text, due is a calendar, and assigned value is "self"--> 
            <div>
                <label for="task">Task</label>
                <input type="text" id="task" name="task" required>
            </div>
            <div>
                <label for="due">Due</label>
                <input type="date" id="due" name="due" required>
            </div>
            <div>
                <label for="assigned">Assigned</label>
                <input type="text" id="assigned" name="assigned" value="self" required>
            </div>
            <div>
                <input type="submit" />
            </div>
        </form>
    </section>
</body>
</body>

</html>