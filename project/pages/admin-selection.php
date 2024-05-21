<?php
// Include necessary files and start session
include "admin-pageselect.php";
include "../utils.php";
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (connectToDB()) {

        // Retrieve user-defined conditions from form
    $logicalOperators = $_POST['logical'];
    $columnNames = $_POST['column'];
    $comparisonOperators = $_POST['operator'];
    $values = $_POST['value'];

    // Construct the SQL query dynamically based on user-defined conditions
    $sqlQuery = "SELECT * FROM Applies WHERE ";

    // Loop through each condition and append it to the query
    for ($i = 0; $i < count($logicalOperators); $i++) {
        // Append logical operator
        if ($i > 0) {
            $sqlQuery .= " " . $logicalOperators[$i] . " ";
        }

        // Append condition (column, operator, value)
        $sqlQuery .= $columnNames[$i] . " " . $comparisonOperators[$i] . " '" . $values[$i] . "'";
    }

    // Execute the constructed SQL query
    $result = executePlainSQL($sqlQuery);
    oci_commit($db_conn);
	$exists = printQueryResult($result, "Here are your Selection:");
	if ($exists) echo "<hr>";
	if(!$exists) echo "You have 0 applicants who meets the criteria";



        disconnectFromDB();
	} else {
		$error = "Get not working properly";
	}
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Query Builder with Applies</title>
</head>
<body>
    <h1>Dynamic Query Builder with Applies</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div id="conditions">
            <div class="condition">
                <select name="logical[]">
                    <option value="AND">AND</option>
                    <option value="OR">OR</option>
                </select>
                <input type="text" name="column[]" placeholder="Column Name">
                <select name="operator[]">
                    <option value="=">Equal</option>
                    <option value="<">Less Than</option>
                    <!-- Add more comparison operators as needed -->
                </select>
                <input type="text" name="value[]" placeholder="Value">
            </div>
        </div>
        <button type="button" id="addCondition">Add Condition</button>
        <input type="submit" name="submit" value="Submit">
    </form>

    <script>
        document.getElementById('addCondition').addEventListener('click', function() {
            var conditionDiv = document.createElement('div');
            conditionDiv.classList.add('condition');
            conditionDiv.innerHTML = `
                <select name="logical[]">
                    <option value="AND">AND</option>
                    <option value="OR">OR</option>
                </select>
                <input type="text" name="column[]" placeholder="Column Name">
                <select name="operator[]">
                    <option value="=">Equal</option>
                    <option value="<">Less Than</option>
                    <!-- Add more comparison operators as needed -->
                </select>
                <input type="text" name="value[]" placeholder="Value">
            `;
            document.getElementById('conditions').appendChild(conditionDiv);
        });
    </script>
</body>
</html>