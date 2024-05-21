<?php
include "admin-pageselect.php";
?>

<?php
	session_start();
	include "../utils.php";

	// if (isset($_SESSION['USERTYPE']) && $_SESSION['USERTYPE'] == 'A') {
	// 	$passport_number = $_SESSION['PASSPORTNUMBER'];
	// 	logMessage("Admin logged in with passport number " . $passport_number);
	// } else {
	// 	header("Location: login.php", true, 301);
	// 	exit();
	// }

	function outputTables() {
		global $db_conn;
		// For sample output
		echo "<h1>Entities:</h1>";

		echo "<h3>Immigrants:</h3>";
		$result = executePlainSQL("SELECT * FROM Immigrant");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Students:</h3>";
		$result = executePlainSQL("SELECT * FROM Immigrant, Student WHERE Immigrant.PassportNumber = Student.PassportNumber");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Workers:</h3>";
		$result = executePlainSQL("SELECT * FROM Immigrant, Worker WHERE Immigrant.PassportNumber = Worker.PassportNumber");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Workplaces:</h3>";
		$result = executePlainSQL("SELECT * FROM Workplace");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Schools:</h3>";
		$result = executePlainSQL("SELECT * FROM School");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>International Schools:</h3>";
		$result = executePlainSQL("SELECT * FROM InternationalSchool");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Histories:</h3>";
		$result = executePlainSQL("SELECT * FROM History");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Health Histories:</h3>";
		$result = executePlainSQL("SELECT * FROM History, HealthHistory WHERE History.FileNumber = HealthHistory.FileNumber");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Criminal Histories:</h3>";
		$result = executePlainSQL("SELECT * FROM History, CriminalHistory WHERE History.FileNumber = CriminalHistory.FileNumber");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Houses:</h3>";
		$result = executePlainSQL("SELECT * FROM House");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Banks:</h3>";
		$result = executePlainSQL("SELECT * FROM Bank");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>IRCC:</h3>";
		$result = executePlainSQL("SELECT * FROM IRCC");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>BIDToLocation:</h3>";
		$result = executePlainSQL("SELECT * FROM BIDToLocation");
		oci_commit($db_conn);
		printQueryResult($result);


		echo "<h1>Relationships:</h1>";
		
		echo "<h3>Applies:</h3>";
		$result = executePlainSQL("SELECT * FROM Immigrant, Applies, IRCC WHERE Immigrant.PassportNumber = Applies.PassportNumber AND Applies.BranchID = IRCC.BranchID");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>HasAccount:</h3>";
		$result = executePlainSQL("SELECT * FROM Immigrant, HasAccount, Bank WHERE Immigrant.PassportNumber = HasAccount.PassportNumber AND HasAccount.DFI = Bank.DFI AND HasAccount.Branch = Bank.Branch");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>Has:</h3>";
		$result = executePlainSQL("SELECT * FROM Immigrant, Has, History WHERE Immigrant.PassportNumber = Has.PassportNumber AND Has.FileNumber = History.FileNumber");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>WillWorkAt:</h3>";
		$result = executePlainSQL("SELECT * FROM Worker, WillWorkAt, Workplace WHERE Worker.PassportNumber = WillWorkAt.PassportNumber AND WillWorkAt.BusinessNumber = Workplace.BusinessNumber");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>GraduatedFrom:</h3>";
		$result = executePlainSQL("SELECT * FROM Student, GraduatedFrom, InternationalSchool WHERE Student.PassportNumber = GraduatedFrom.PassportNumber AND GraduatedFrom.Name = InternationalSchool.Name");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>WillStudyAt:</h3>";
		$result = executePlainSQL("SELECT * FROM Student, WillStudyAt, School WHERE Student.PassportNumber = WillStudyAt.PassportNumber AND WillStudyAt.DLI = School.DLI");
		oci_commit($db_conn);
		printQueryResult($result);

		echo "<h3>WillLiveAt:</h3>";
		$result = executePlainSQL("SELECT * FROM Immigrant, WillLiveAt, House WHERE Immigrant.PassportNumber = WillLiveAt.PassportNumber AND WillLiveAt.Location = House.Location");
		oci_commit($db_conn);
		printQueryResult($result);
	}

    if ($_SERVER["REQUEST_METHOD"] == "POST")  {
        connectToDB();
		executeSQLFile("../sql/DatabaseSetup.sql");
		outputTables();
        disconnectFromDB();
    }

	if ($_SERVER["REQUEST_METHOD"] == "GET")  {
        connectToDB();
		outputTables();
        disconnectFromDB();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats</title>
</head>
<body>
    Stats: View and Regenerate Tables <br>
    <form action="stats.php" method="post"> <!-- simplified this down -->
        <input type="submit" value="Regenerate Tables">
    </form>
</body>
</html>

