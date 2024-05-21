<?php
include "pageselect.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Homepage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<div class="home_container">
    <h2>User Homepage</h2>
    <?php if (isset($error)) { ?>
        <div style="color: red;"><?php echo $error; ?></div>
    <?php } ?>

    <div class="button_container">
        <h1>Welcome to the User Homepage</h1>


<?php
session_start();
include "../utils.php";

$inter_school_loc = $inter_school_name = $email_field_1 = $email_field_2 = $password_field_1 = $password_field_2 = "";
$inter_school_err  = $email_field_err = $password_field_err = "";
$student_id = $student_id_err = "";

if (isset($_SESSION['PASSPORTNUMBER'])) {
    $passport_number = $_SESSION['PASSPORTNUMBER'];
    logMessage("User with passport number " . $passport_number . " is logged in");
} else {
    header("Location: login.php", true, 301);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_SESSION['PASSPORTNUMBER'])) {
        $passport_number = $_SESSION['PASSPORTNUMBER'];
    } else {
        header("Location: login.php", true, 301);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['PASSPORTNUMBER'])) {
        $passport_number = $_SESSION['PASSPORTNUMBER'];
    } else {
        header("Location: login.php", true, 301);
        exit();
    }

    if (!connectToDB()) {
        logMessage("Error connection to db");
        echo "Error connection to db";
        exit();
    }
    
    if (isset($_POST['add_school_submitted'])) {
        logMessage("Enetered Add School");
        
        $inter_school_name = trim($_POST["inter_school_name"]);
        $inter_school_loc = trim($_POST["inter_school_loc"]);
        
        // Check that inter school name exists in INternational school,
        // if yes then add entry in GraduatedFrom
        // if no then add entry in InternationalSchool and GraduatedFrom
        if (!empty($inter_school_name) && !empty($inter_school_loc)) {
            if (!check_data_exists("SELECT * FROM InternationalSchool WHERE NAME = '{$inter_school_name}'")) {
                // Entry does not exist, add to international school and Graduated from
                
                $query = "INSERT INTO InternationalSchool (Name, Location) VALUES ";
                $query .= "('{$inter_school_name}','{$inter_school_loc}')";
                logMessage("Home.php->POST | {$query}");
                
                $result = executePlainSQL($query);
                oci_commit($db_conn);
            }
            // Entry exists, add only to Graduated from
            
            $query = "INSERT INTO GraduatedFrom (PassportNumber, Name) VALUES ";
            $query .= "('{$passport_number}','{$inter_school_name}')";
            logMessage("Home.php->POST | {$query}");
            
            $result = executePlainSQL($query);
            oci_commit($db_conn);
        } else {
            $inter_school_err = "Enter all details";
        }
        
    } elseif (isset($_POST['update_email_submitted'])) {
        $email_field_1 = trim($_POST["email_field_1"]);
        $email_field_2 = trim($_POST["email_field_2"]);
        
        if (empty($email_field_1) || empty($email_field_2)) {
            $email_field_err = "Enter email";
        } else if (($email_field_1 != $email_field_2)) {
            $email_field_err = "Entered emails don't match";
        } else {
            // All good update the table
            
            $query = "UPDATE IMMIGRANT SET EMAIL = '{$email_field_1}' WHERE PASSPORTNUMBER = '{$passport_number}' ";
            logMessage("Home.php->POST | {$query}");
            
            $result = executePlainSQL($query);
            oci_commit($db_conn);
        }
    } elseif (isset($_POST['update_password_submitted'])) {
        $password_field_1 = $_POST["password_field_1"];
        $password_field_2 = $_POST["password_field_2"];
        
        if (empty($password_field_1) || empty($password_field_2)) {
            $password_field_err = "Enter Password";
        } else if (($password_field_1 != $password_field_2)) {
            $password_field_err = "Entered passwords don't match";
        } else {
            // All good update the password
            
            $query = "UPDATE IMMIGRANT SET PASSCODE = '{$password_field_1}' WHERE PASSPORTNUMBER = '{$passport_number}' ";
            logMessage("Home.php->POST | {$query}");
            
            $result = executePlainSQL($query);
            oci_commit($db_conn);
        }
    } elseif (isset($_POST['update_student_id_submitted'])) {
        
        $student_id = $_POST["student_id"];
        if (strlen($student_id) != 5) {
            $student_id_err = "Enter Student ID of length 5";
        } else {
                
            $query = "UPDATE STUDENT SET STUDENTNUMBER = '{$student_id}' WHERE PASSPORTNUMBER = '{$passport_number}' ";
            logMessage("Home.php->POST | {$query}");
            
            $result = executePlainSQL($query);
            oci_commit($db_conn);
        }


    }

    disconnectFromDB();

}

printImmigrantInfo($passport_number);

function printImmigrantInfo($passport_number) {
    global $db_conn;
    if (connectToDB()) {
        // immigrant info
        $result = executePlainSQL("SELECT PassportNumber, Name, DOB, Gender, Email FROM Immigrant WHERE PassportNumber = '".$passport_number."'");
        oci_commit($db_conn);
        printQueryResult($result, "Info:");
        echo "<hr>";


        // application info
        $result = executePlainSQL("
            SELECT a.VisaNumber, a.DateOfApplication, a.BranchID, i.BranchName, b.Location 
            FROM Immigrant i, Applies a, IRCC i, BIDToLocation b
            WHERE i.PassportNumber = a.PassportNumber 
                AND a.BranchID = i.BranchID
                AND i.BranchID = b.BranchID
                AND i.PassportNumber = '" . $passport_number . "'
        ");
        oci_commit($db_conn);
		$exists = printQueryResult($result, "Application Info:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No application info found for ". $passport_number);


        // student info
        $result = executePlainSQL("
            SELECT Student.StudentNumber, School.Name, School.Location
            FROM Student, WillStudyAt, School 
            WHERE Student.PassportNumber = WillStudyAt.PassportNumber 
                AND WillStudyAt.DLI = School.DLI
                AND Student.PassportNumber = '" . $passport_number . "'    
        ");
        oci_commit($db_conn);
        $exists = printQueryResult($result, "Student Info:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No student info found for ". $passport_number);

        // graduation info
        $result = executePlainSQL("
            SELECT GraduatedFrom.Name, InternationalSchool.Location
            FROM Student, GraduatedFrom, InternationalSchool 
            WHERE Student.PassportNumber = GraduatedFrom.PassportNumber 
                AND GraduatedFrom.Name = InternationalSchool.Name
                AND Student.PassportNumber = '" . $passport_number . "'
        ");
        oci_commit($db_conn);
        $exists = printQueryResult($result, "Graduated From:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No graduation info found for ". $passport_number);


        // worker info
		$result = executePlainSQL("
            SELECT Workplace.Name, Workplace.Location, WillWorkAt.ContractType 
            FROM Worker, WillWorkAt, Workplace 
            WHERE Worker.PassportNumber = WillWorkAt.PassportNumber 
            AND WillWorkAt.BusinessNumber = Workplace.BusinessNumber
            AND Worker.PassportNumber = '" . $passport_number . "'
        ");
        oci_commit($db_conn);
        $exists = printQueryResult($result, "Worker Info:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No worker info found for ". $passport_number);


        // health history
        $result = executePlainSQL("
            SELECT h.FileNumber, hi.DateOfEvent, hi.ExpiryDate, hi.CertifyingInstitution, hh.Type, hh.Description
            FROM Immigrant i, Has h, History hi, HealthHistory hh
            WHERE i.PassportNumber = h.PassportNumber
                AND h.FileNumber = hi.FileNumber
                AND hi.FileNumber = hh.FileNumber
                AND i.PassportNumber = '" . $passport_number . "'
        ");
        oci_commit($db_conn);
		$exists = printQueryResult($result, "Health History:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No health history found for ". $passport_number);


        // criminal history
        $result = executePlainSQL("
            SELECT h.FileNumber, hi.DateOfEvent, hi.ExpiryDate, hi.CertifyingInstitution, ch.Crime, ch.PrisonTime
            FROM Immigrant i, Has h, History hi, CriminalHistory ch
            WHERE i.PassportNumber = h.PassportNumber
                AND h.FileNumber = hi.FileNumber
                AND hi.FileNumber = ch.FileNumber
                AND i.PassportNumber = '" . $passport_number . "'
        ");
        oci_commit($db_conn);
		$exists = printQueryResult($result, "Criminal History:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No criminal history found for ". $passport_number);


        // financial information 
		$result = executePlainSQL("
            SELECT Bank.Name, Bank.Branch, Bank.Location, HasAccount.AccountNumber, HasAccount.Balance, HasAccount.CreditScore
            FROM Immigrant, HasAccount, Bank 
            WHERE Immigrant.PassportNumber = HasAccount.PassportNumber 
                AND HasAccount.DFI = Bank.DFI 
                AND HasAccount.Branch = Bank.Branch
                AND Immigrant.PassportNumber = '" . $passport_number . "'
        ");
        oci_commit($db_conn);
        $exists = printQueryResult($result, "Financials:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No financial info found for ". $passport_number);


        // house info
        $result = executePlainSQL("
            SELECT h.Location, h.Type 
            FROM Immigrant i, WillLiveAt w, House h
            WHERE i.PassportNumber = w.PassportNumber 
                AND w.Location = h.Location
                AND i.PassportNumber = '" . $passport_number . "'
        ");
        oci_commit($db_conn);
		$exists = printQueryResult($result, "Living Arrangements:");
        if ($exists) echo "<hr>";
        if (!$exists) logMessage("No house info found for ". $passport_number);


        disconnectFromDB();
    }
}
?>

        
	    <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" name="add_school">
            
            <H4>International School info </H4>
            <input type="text" name="inter_school_name" placeholder="Name of international school" value="<?php echo $inter_school_name; ?>">
            <input type="text" name="inter_school_loc" placeholder="Address of international school" value="<?php echo $inter_school_loc; ?>">
            <span class="error"><?php echo $inter_school_err; ?></span>
            <br>

		    <input type = "submit" name="add_school_submitted" value="Submit school information">
	    </form>

        <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" name="update_email">
            
            <H4>Update Email </H4>
            <input type="email" name="email_field_1" placeholder="New Email" value="<?php echo $email_field_1; ?>">
            <input type="email" name="email_field_2" placeholder="Retype New Email" value="<?php echo $email_field_2; ?>">
            <span class="error"><?php echo $email_field_err; ?></span>
            <br>

		    <input type = "submit" name="update_email_submitted" value="Update email">
	    </form>
        
        <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" name="update_password">
            
            <H4>Update Password </H4>
            <input type="password" name="password_field_1" placeholder="New password" value="<?php echo $password_field_1; ?>">
            <input type="password" name="password_field_2" placeholder="Retype new password" value="<?php echo $password_field_2; ?>">
            <span class="error"><?php echo $password_field_err; ?></span>
            <br>

		    <input type = "submit" name="update_password_submitted" value="Update password">
	    </form>
        <form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" name="update_student_id">
            
            <H4>Update Student ID </H4>
            <input type="number" name="student_id" placeholder="12300" value="<?php echo $student_id; ?>">
            <span class="error"><?php echo $student_id_err; ?></span>
            <br>

		    <input type = "submit" name="update_student_id_submitted" value="Update Student ID">
	    </form>

    </div>
</div>
<hr>
</body>
</html>