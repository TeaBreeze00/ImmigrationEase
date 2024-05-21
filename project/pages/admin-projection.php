<?php
include "admin-pageselect.php";
?>

<?php
session_start();

include "../utils.php";

$table_name = $column_names = "";
$table_err = "";

if (isset($_SESSION['USERTYPE']) && $_SESSION['USERTYPE'] == 'A') {
	$passport_number = $_SESSION['PASSPORTNUMBER'];
	logMessage("Admin logged in with passport number " . $passport_number);
} else {
	header("Location: login.php", true, 301);
	exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {

}

function combine_string($arr) {
	$out = "";
	
	$arrayLength = count($arr);
	for ($i = 0; $i < $arrayLength - 1; $i++) {
		$out .= " {$arr[$i]}, ";
	}
	
	// Handle the last element outside the loop
	if ($arrayLength > 0) {
		$lastIndex = $arrayLength - 1;
		$out .= " {$arr[$lastIndex]} ";
	}
	return $out;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (isset($_POST["table_name"]) && isset($_POST["column_names"])) {

		if (connectToDB()) {

			$table_name = $_POST["table_name"];
			$column_names = $_POST["column_names"];
			$column_formatted_names = combine_string(separateString($column_names, " "));

			$query = "SELECT {$column_formatted_names} FROM {$table_name}";
			logMessage("Admin-projection.php->POST | {$query}");
			
			$result = executePlainSQL($query);
			oci_commit($db_conn);

			$exists = printQueryResult($result, "Projected table:");
			if ($exists) echo "<hr>";
			if (!$exists) logMessage("No data meeting criteria found");


			disconnectFromDB();
		} else {
			$table_err = "Cannot connect to server";
		}
	} else {
		$table_err = "Enter all details";
	}
}



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Projection</title>
</head>
<body>
    Admin Page 2!

	<h4>Projection</h4>
	<form action="admin-projection.php" method="post">	
		Table name: <input type="text" name="table_name" placeholder="Enter name of table to view" value="<?php echo $table_name; ?>"> <br>
		Column Names: <input type="text" name="column_names" placeholder="Enter space separated column names to project" value="<?php echo $column_names; ?>">
		<span class="error"><?php echo $table_err; ?></span><br>
		<button type="submit">Project table</button>
	</form>

</body>
</html>