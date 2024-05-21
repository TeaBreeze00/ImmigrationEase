<?php
include "admin-pageselect.php";
?>

<?php
	session_start();
	include "../utils.php";

    $join_choice = "";

	if (isset($_SESSION['USERTYPE']) && $_SESSION['USERTYPE'] == 'A') {
		$passport_number = $_SESSION['PASSPORTNUMBER'];
		logMessage("Admin logged in with passport number " . $passport_number);
	} else {
		header("Location: login.php", true, 301);
		exit();
	}

	function outputTables() {
		global $db_conn;
        global $join_choice;
        if ($join_choice == '') {
            echo "<h3>Please Select an Option:</h3>";
        }

        if ($join_choice == 'Applications') {
            echo "<h3>Applications:</h3>";
            $result = executePlainSQL("SELECT * FROM Immigrant, Applies, IRCC WHERE Immigrant.PassportNumber = Applies.PassportNumber AND Applies.BranchID = IRCC.BranchID");
            oci_commit($db_conn);
            printQueryResult($result);
        }

        if ($join_choice == 'Accounts') {
            echo "<h3>Accounts:</h3>";
            $result = executePlainSQL("SELECT * FROM Immigrant, HasAccount, Bank WHERE Immigrant.PassportNumber = HasAccount.PassportNumber AND HasAccount.DFI = Bank.DFI AND HasAccount.Branch = Bank.Branch");
            oci_commit($db_conn);
            printQueryResult($result);
        }


        if ($join_choice == 'Files') {
            echo "<h3>Files:</h3>";
            $result = executePlainSQL("SELECT * FROM Immigrant, Has, History WHERE Immigrant.PassportNumber = Has.PassportNumber AND Has.FileNumber = History.FileNumber");
            oci_commit($db_conn);
            printQueryResult($result);
        }

        if ($join_choice == 'Worker Info') {
            echo "<h3>Worker Info:</h3>";
            $result = executePlainSQL("SELECT * FROM Worker, WillWorkAt, Workplace WHERE Worker.PassportNumber = WillWorkAt.PassportNumber AND WillWorkAt.BusinessNumber = Workplace.BusinessNumber");
            oci_commit($db_conn);
            printQueryResult($result);    
        }

        if ($join_choice == 'Student Info') {
            echo "<h3>WillStudyAt:</h3>";
            $result = executePlainSQL("SELECT * FROM Student, WillStudyAt, School WHERE Student.PassportNumber = WillStudyAt.PassportNumber AND WillStudyAt.DLI = School.DLI");
            oci_commit($db_conn);
            printQueryResult($result);
        }

        if ($join_choice == 'Residences') {
            echo "<h3>WillLiveAt:</h3>";
            $result = executePlainSQL("SELECT * FROM Immigrant, WillLiveAt, House WHERE Immigrant.PassportNumber = WillLiveAt.PassportNumber AND WillLiveAt.Location = House.Location");
            oci_commit($db_conn);
            printQueryResult($result);
        }
	}

    if ($_SERVER["REQUEST_METHOD"] == "POST")  {
        connectToDB();
        if (empty(trim($_POST["join_choice"]))) {
            // $branch_id_err = "Please select a branch";
            // $all_valid = false;
        } else {
            $join_choice = trim($_POST["join_choice"]);
        }    
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
    <title>Join</title>
</head>
<body>
    Join: View Immigrant Tables <br>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <select name="join_choice">
                <option value="" <?php echo ($join_choice == '') ? 'selected' : ''; ?>>
                    Select Option
                </option>
                <option value="Applications" <?php echo ($join_choice == 'Applications') ? 'selected' : ''; ?>>
                    Applications
                </option>
                <option value="Accounts" <?php echo ($join_choice == 'Accounts') ? 'selected' : ''; ?>>
                    Accounts
                </option>
                <option value="Files" <?php echo ($join_choice == 'Files') ? 'selected' : ''; ?>>
                    Files
                </option>
                <option value="Worker Info" <?php echo ($join_choice == 'Worker Info') ? 'selected' : ''; ?>>
                    Worker Info
                </option>
                <option value="Student Info" <?php echo ($join_choice == 'Student Info') ? 'selected' : ''; ?>>
                    Student Info
                </option>
                <option value="Residences" <?php echo ($join_choice == 'Residences') ? 'selected' : ''; ?>>
                    Residences
                </option>
    </select>
    <input type="submit" value="Search">
    </form>
</body>
</html>

