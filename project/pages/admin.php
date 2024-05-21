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

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "GET") {

	if (isset($_GET['bank_with_max_applicants']) || isset($_GET['total_applications_per_branch']) || isset($_GET['all_school_request']) || isset($_GET['number'])) {

		echo "get is working fine now, inside post as it should";

		if (connectToDB()) {

		if(isset($_GET['total_applications_per_branch'])) {

				$result = executePlainSQL("SELECT B.BranchID, B.Location AS BranchLocation, COUNT(*) AS NumberOfApplications
				FROM Applies A
				JOIN BIDToLocation B ON A.BranchID = B.BranchID
				GROUP BY B.BranchID, B.Location");
                oci_commit($db_conn);
				$exists = printQueryResult($result, "Total Applications Per Branch:");
		if ($exists) echo "<hr>";
		} 
		else{
			 $error = "Error while processing the query: Total Application per branch";
		}

		if(isset($_GET['bank_with_max_applicants'])) {
				$result = executePlainSQL("SELECT B.DFI, COUNT(*) AS NumAccounts
				FROM Bank B
				INNER JOIN HasAccount HA ON B.DFI = HA.DFI
				GROUP BY B.DFI
				HAVING COUNT(*) = (
					SELECT MAX(InnerCounts.NumAccounts)
					FROM (
						SELECT COUNT(*) AS NumAccounts
						FROM HasAccount HA2
						GROUP BY HA2.DFI, HA2.Branch
					) InnerCounts
				)
				");
			oci_commit($db_conn);
			$exists = printQueryResult($result, "Total Applications Per Branch:");
		if ($exists) echo "<hr>";
		if (!$exists) echo "You have 0 branches with more than one applicant";
		} 
		else {
			 $error = "Error while processing the query: Total Application per branch";
		}

		/*This is the begining of the second query, for now, it shows applicants who have graduated from all schools
        */
		if(isset($_GET['all_school_request'])) {
			if(isset($_GET['all_school'])) {
				$allSchoolInput = $_GET['all_school'];
				$schools = array_map('trim', explode(',', $allSchoolInput)); // Trim whitespace and split by comma
		
				// Construct the SQL query dynamically based on user-defined schools
				$sqlQuery = "SELECT i.*
							 FROM Immigrant i
							 WHERE i.PassportNumber IN (
								 SELECT g.PassportNumber
								 FROM GraduatedFrom g
								 WHERE g.Name IN ('" . implode("','", $schools) . "') 
								 GROUP BY g.PassportNumber
								 HAVING COUNT(DISTINCT g.Name) = " . count($schools) . "
							 )";

							 
							 $result = executePlainSQL($sqlQuery);
							 oci_commit($db_conn);
					 
							 $exists = printQueryResult($result, "Applicants who have graduated from all specified schools:");
							 if (!$exists) {
								 echo "<hr>";
								 echo "There are 0 applicants who have graduated from all specified schools.";
							 }
		
			} else {
				$error = "Error: No input provided for schools.";
			}
		} else {
			$error = "Error: Request not initiated properly.";
		}

    
	/*this is the begining of processing of the third button, group by having queries
	*/
	    if(isset($_GET['number'])) {

		echo "inside the number method!!";

		if(isset($_GET['numero'])) {

			$number = sanitizeString($_GET['numero']);


			$result = executePlainSQL("SELECT DateOfApplication, COUNT(*) AS ApplicationCount
			FROM Applies
			GROUP BY DateOfApplication
			HAVING COUNT(*) >= '".$number."'");
			oci_commit($db_conn);
			$exists = printQueryResult($result, "Applicants who are above the threshold:");
			if ($exists) echo "<hr>";
			if(!$exists) echo "You have 0 applicants who are above the threshold";

	    }else{
		$error = "Please provide a number";
	    }



    }else{
	$error = "Error while processing the query: Getting the appplicants meeting a spefecific threshold";
    }
    



		disconnectFromDB();
	} else {
		$error = "Get not working properly";
	}

	}
}




if ($_SERVER["REQUEST_METHOD"] == "POST") {


	if (isset($_POST['delete']) ) {

		if (connectToDB()) {
			if(isset($_POST['passport_number'])) {
				$passport_number_inp = sanitizeString($_POST['passport_number']);
				if (!check_data_exists("SELECT * FROM IMMIGRANT WHERE PASSPORTNUMBER = '{$passport_number_inp}'")) {
					$error = "Please provide a valid passport number";
				} else {
					$query = "DELETE FROM Immigrant WHERE PassportNumber = '{$passport_number_inp}'";
					$result = executePlainSQL($query);
					
					oci_commit($db_conn);
	
					if (check_data_exists("SELECT * FROM IMMIGRANT WHERE PASSPORTNUMBER = '{$passport_number_inp}'")) {
						$error = "Data not deleted";
					} else {
						$error = "Data deleted!";
					}
				}
			}else{
				$error = "Please provide a valid passport number";
			}		
		disconnectFromDB();
		}

		} else {
			$error = "Unexpected error";
		}
	
}



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>	
<span class="error"><?php echo $error; ?></span>
		<h2>Display Total Applications Per Branch (Group By)</h2>
	    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<input type="hidden" id="total_applications_per_branch_request" name="total_applications_per_branch_request">
		<input type="submit" name="total_applications_per_branch"></p>
	    </form>

		<h2>Display Banks with Most Applicants (Nested Aggregation)</h2>
	    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<input type="hidden" id="bank_with_max_applicants_request" name="bank_with_max_applicants_request">
		<input type="submit" name="bank_with_max_applicants"></p>
	    </form>

		<h2>Display Applicants Who Have Graduated from all of the selected Schools (Division)</h2>
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="text"  name="all_school" placeholder="Select any school" required>
        <input type="submit" name="all_school_request"></p>
        </form>
        
		<h2>Display the dates which received more than the given applications (Having)</h2>
        <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
        <input type="number" name="numero" placeholder="Any Integer" required>
        <input type = "submit" value="Get the Number of Applicants per Branch" name = "number" >
        </form>
        
		<h2>Type a Passport Number to Delete It (Cascade Delete)</h2>
        <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <input type="number" name="passport_number" placeholder="00000000" required>
        <input type = "submit" value="Delete the application from the server, once and for all" name="delete">
        </form>
        
	    

</body>
</html>
