<?php
include "pageselect.php";
?>

<?php
session_start();
include "../utils.php";

$submitted = $error_out = "";
$passport_number_err = $name_err = $dob_err = $gender_err = $email_err = $branch_id_err = "";
$application_type_err = $business_number_err = $contract_type_err = "";
$degree_type_err = $dli_number_err = $student_number_err = $inter_school_name_err = "";
$bank_dfi_err = $future_location_err = "";
$future_location_period_err = $crime_err = $prison_time_err = $health_desc_err = "";
$health_type_err = $password_err = $inter_school_loc_err = "";

$passport_number = $name = $dob = $gender = $email = $branch_id = $application_type = "";
$business_number = $contract_type = $degree_type = $dli_number = "";
$student_number = $inter_school_name = $inter_school_loc = "";
$bank_dfi = $future_location = $future_location_period = $crime = $prison_time = "";
$health_desc = $health_type = $password_1 = $password_2 = $passcode = "";

$all_valid = true;
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Immigrant relation
    if (empty(trim($_POST["passport_number"]))) {
        $passport_number_err = "Please enter a passport number";
        $all_valid = false;
    } else if (strlen(trim($_POST["passport_number"])) != 8) {
        $passport_number_err = "Please enter 8 character passport number";
        $all_valid = false;
    } else {
        $passport_number = trim($_POST["passport_number"]);
    }
    
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name";
        $all_valid = false;
    } else {
        $name = trim($_POST["name"]);
    }
    
    if (strlen(trim($_POST["dob"])) != 10) {
        $dob_err = "Please enter a Date";
        $all_valid = false;
    }
    $entered_date = new DateTime($dob);
    $dob = trim($_POST["dob"]);
    
    if (isset($_POST["gender"]))
    {
        if (empty(trim($_POST["gender"]))) {
            $gender_err = "Please enter gender";
            $all_valid = false;
        } else {
            $gender = trim($_POST["gender"]);
        }
    } else 
    {
        $gender_err = "Please enter gender";
        $all_valid = false;
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email";
        $all_valid = false;
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty($_POST["password_1"]) || empty($_POST["password_2"]) ) {
        $password_err = "Please enter password";
        $all_valid = false;
    } else if ($_POST["password_1"] != $_POST["password_2"]) {
        $password_err = "Entered passwords do not match";
        $all_valid = false;
    } else if (strlen($_POST["password_1"]) < 8) {
        $password_err = "Enter a strong password (8 or more characters)";
        $all_valid = false;
    } else {
        $passcode = $_POST["password_1"];

    }
    
    if (empty(trim($_POST["branch_id"]))) {
        $branch_id_err = "Please select a branch";
        $all_valid = false;
    } else {
        $branch_id = trim($_POST["branch_id"]);
    }
    
    if (isset($_POST["application_type"]))
    {
        if (empty(trim($_POST["application_type"]))) {
            $application_type_err = "Please select application type";
            $all_valid = false;
        } else {
            $application_type = trim($_POST["application_type"]);
        }
    } else
    {
        $application_type_err = "Please select application type";
        $all_valid = false;
    }
    
    switch ($application_type) {
        case 'S': // Student Application
            
            if (empty(trim($_POST["degree_type"]))) {
                $degree_type_err = "Please enter degree type";
                $all_valid = false;
            } else {
                $degree_type = trim($_POST["degree_type"]);
            }
            
            if (empty(trim($_POST["dli_number"]))) {
                $dli_number_err = "Please enter a DLI";
                $all_valid = false;
            } else if (strlen(trim($_POST["dli_number"])) != 10) {
                $dli_number_err = "Please enter 10 character DLI";
            } else {
                $dli_number = trim($_POST["dli_number"]);
            }
            
            if (empty(trim($_POST["student_number"]))) {
                $student_number_err = "Please enter a Student Number";
                $all_valid = false;
            } else if (strlen(trim($_POST["student_number"])) != 5) {
                $student_number_err = "Please enter 5 character Student Number";
                $all_valid = false;
            } else {
                $student_number = trim($_POST["student_number"]);
            }
            
            break;
        case 'W': // Worker Application
                
            if (empty(trim($_POST["business_number"]))) {
                $business_number_err = "Please enter a business number";
                $all_valid = false;
            } else if (strlen(trim($_POST["business_number"])) != 10) {
                $business_number_err = "Please enter 10 character business number";
                $all_valid = false;
            } else {
                $business_number = trim($_POST["business_number"]);
            }
            
            if (empty(trim($_POST["contract_type"]))) {
                $contract_type_err = "Please select contract type";
                $all_valid = false;
            } else {
                $contract_type = trim($_POST["contract_type"]);
            }
            
            break;    
            
        default:
            $application_type_err = "Invalid application type";
            $all_valid = false;
            break;
        }         
        
        if (!empty(trim($_POST["inter_school_loc"]))) {
            if (!empty(trim($_POST["inter_school_name"]))) {
                $inter_school_loc = $_POST["inter_school_loc"];
                $inter_school_name = $_POST["inter_school_name"];
            } else {
                $inter_school_name_err = "Enter school name";    
                $inter_school_loc = $_POST["inter_school_loc"];
                $all_valid = false;
            }
        }
        // if (isset($_POST["bank_dfi"])) {
        //     $bank_dfi = trim($_POST["bank_dfi"]);
        // }
        // if (isset($_POST["future_location"])) {
        //     $future_location = trim($_POST["future_location"]);
        // }


    function check_data_can_be_entered_to_db()
    { // Returns true if form statisfies db constraints

        global $passport_number;
        global $application_type;
        global $student_number;
        global $dli_number;
        global $business_number;

        $condition = !check_data_exists("SELECT * FROM IMMIGRANT WHERE PASSPORTNUMBER = '{$passport_number}'");
        logMessage("Apply.php->POST | SELECT * FROM IMMIGRANT WHERE PASSPORTNUMBER = '{$passport_number}' | Returned: {$condition}");
        if ($application_type === 'S')
        { // STUDENT Application
            $condition = $condition && !check_data_exists("SELECT * FROM STUDENT WHERE STUDENTNUMBER = '{$student_number}'");
            logMessage("Apply.php->POST | SELECT * FROM STUDENT WHERE STUDENTNUMBER = '{$student_number}'  | Returned: {$condition}");
            $condition = $condition && check_data_exists("SELECT * FROM SCHOOL WHERE DLI = '{$dli_number}'");
            logMessage("Apply.php->POST | SELECT * FROM SCHOOL WHERE DLI = '{$dli_number}' | Returned: {$condition}");
            
            //         // Should be able to submit the student application
        } else if ($application_type === 'W')
        { // WORKER application
            logMessage("Apply.php->POST | Worker | Returned: {$condition}");
            // $condition = $condition || !check_data_can_be_entered_to_db("SELECT * FROM WORKPLACE WHERE BusinessNumber = '{$business_number}'");
        }
        return $condition;
    }

    logMessage("apply.php->POST");
    if (!$all_valid) {
        $error_out = "<br>form not submitted<br>";
    } else {
        if (!connectToDB()) {
            $error_out = "<br>Database Not connected<br>";
        } else {
            if (!check_data_can_be_entered_to_db())
            {
                logMessage("EXISTS, cannot add to db");
            } else {
                logMessage("DOES NOT EXISTS, can add to db");
                // Add to DB
    
                $query = "INSERT INTO Immigrant (PassportNumber, Name, DOB, Gender, Email, Passcode, usertype) VALUES ";
                $query .= "('{$passport_number}','{$name}','{$dob}','{$gender}','{$email}','{$passcode}', 'R')";
                logMessage("Apply.php->POST | {$query}");
                
                $result = executePlainSQL($query);
                oci_commit($db_conn);
                
                logMessage("Apply.php->POST | Appplication Type: {$application_type}");
                if ($application_type === 'S')
                { // Student Application
                    
                    $query = "INSERT INTO Student (PassportNumber, StudentNumber) VALUES ";
                    $query .= "('{$passport_number}','{$student_number}')";
                    logMessage("Apply.php->POST | {$query}");
                    
                    $result = executePlainSQL($query);
                    oci_commit($db_conn);
                    
                    
                    $query = "INSERT INTO WILLSTUDYAT (PassportNumber, DLI, DegreeType) VALUES ";
                    $query .= "('{$passport_number}','{$dli_number}','{$degree_type}')";
                    logMessage("Apply.php->POST | {$query}");
                    
                    $result = executePlainSQL($query);
                    oci_commit($db_conn);
                    
                } else if ($application_type === 'W')
                { // Worker Application
    
                    $query = "INSERT INTO WORKER (PassportNumber) VALUES ";
                    $query .= "('{$passport_number}')";
                    logMessage("Apply.php->POST | {$query}");
                    
                    $result = executePlainSQL($query);
                    oci_commit($db_conn);
                    
                    $query = "INSERT INTO WillWorkAt (PassportNumber,ContractType,BusinessNumber) VALUES ";
                    $query .= "('{$passport_number}','{$contract_type}','{$business_number}')";
                    logMessage("Apply.php->POST | {$query}");
                    
                    $result = executePlainSQL($query);
                    oci_commit($db_conn);
                }
                
                $count = 0;
                $visa_number = generateRandomString(15);
                while (check_data_exists("SELECT * FROM APPLIES WHERE VISANUMBER = '{$visa_number}'")) {
                    if ($count >= 10) {
                        break;
                    }
                    $count += 1;
                }
                
                logMessage("Apply.php->POST | Visa Number generated: {$visa_number}");
                
                $current_date = date('Y-m-d');
                $query = "INSERT INTO Applies (PassportNumber, BranchID, DateOfApplication, VisaNumber) VALUES ";
                $query .= "('{$passport_number}', '{$branch_id}', '{$current_date}', '{$visa_number}')";
                logMessage("Apply.php->POST | {$query}");
                
                $result = executePlainSQL($query);
                oci_commit($db_conn);
                
                // Check that inter school name exists in INternational school,
                // if yes then add entry in GraduatedFrom
                // if no then add entry in InternationalSchool and GraduatedFrom
                if (!empty(trim($_POST["inter_school_name"]))) {
                    if (!check_data_exists("SELECT * FROM InternationalSchool WHERE NAME = '{$inter_school_name}'")) {
                        // Entry does not exist, add to international school and Graduated from
                        
                        $query = "INSERT INTO InternationalSchool (Name, Location) VALUES ";
                        $query .= "('{$inter_school_name}','{$inter_school_loc}')";
                        logMessage("Apply.php->POST | {$query}");
                        
                        $result = executePlainSQL($query);
                        oci_commit($db_conn);
                    }
                    // Entry exists, add only to Graduated from
                    
                    $query = "INSERT INTO GraduatedFrom (PassportNumber, Name) VALUES ";
                    $query .= "('{$passport_number}','{$inter_school_name}')";
                    logMessage("Apply.php->POST | {$query}");
                    
                    $result = executePlainSQL($query);
                    oci_commit($db_conn);
                }
                $error_out = "Application Submitted!";
                $submitted = '1';
            }
            disconnectFromDB();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply</title>
</head>
<body>
    <div class="container">
        <span><?php echo $error_out ?></span>
        <?php echo ($submitted == '1') ? '<h3><a href="home.php">next</a></h3>' : ''; ?>
        <h2>Sign Up</h2>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
            <!-- Immigrant relation -->
            <H4> Applicant info </H4>
            Passport Number: <input type="text" name="passport_number" placeholder="00000000" value="<?php echo $passport_number; ?>">
            <span class="error"><?php echo $passport_number_err; ?></span>
            <br>
            
            Name: <input type="text" name="name" placeholder="First Last" value="<?php echo $name; ?>">
            <span class="error"><?php echo $name_err; ?></span>
            <br>
            
            DOB: <input type="date" name="dob" value="<?php echo $dob; ?>">
            <span class="error"><?php echo $dob_err; ?></span>
            <br>
            
            Gender <br>
            Male<input type="radio" name="gender" value="M" <?php echo ($gender == 'M') ? 'checked' : ''; ?>>
            Female<input type="radio" name="gender" value="F" <?php echo ($gender == 'F') ? 'checked' : ''; ?>>
            Other<input type="radio" name="gender" value="O" <?php echo ($gender == 'O') ? 'checked' : ''; ?>>
            <span class="error"><?php echo $gender_err; ?></span>
            <br>

            Email: <input type="email" name="email" placeholder="email@domain.com" value="<?php echo $email; ?>">
            <span class="error"><?php echo $email_err; ?></span>
            <br>

            Enter Password Here <input type="password" name="password_1" placeholder=""> <br>
            Re Enter Password Here: <input type="password" name="password_2" placeholder="">
            <span class="error"><?php echo $password_err; ?></span>
            <br>
            
            
            <!-- IRCC -->
            <H4> Application Office </H4>
            <select name="branch_id">
                <option value="" <?php echo ($branch_id == '') ? 'selected' : ''; ?>>
                    Select Branch
                </option>
                <option value="2345" <?php echo ($branch_id == '2345') ? 'selected' : ''; ?>>
                    Edmonton
                </option>
                <option value="3456" <?php echo ($branch_id == '3456') ? 'selected' : ''; ?>>
                    Mississauga
                </option>
                <option value="4567" <?php echo ($branch_id == '4567') ? 'selected' : ''; ?>>
                    Sydney
                </option>
                <option value="3678" <?php echo ($branch_id == '3678') ? 'selected' : ''; ?>>
                    CPC Ottawa
                </option>
                <option value="5678" <?php echo ($branch_id == '5678') ? 'selected' : ''; ?>>
                    OSC Ottawa
                </option>
                <option value="6782" <?php echo ($branch_id == '6782') ? 'selected' : ''; ?>>
                    Calgary
                </option>
                <option value="1234" <?php echo ($branch_id == '1234') ? 'selected' : ''; ?>>
                    Vancouver
                </option>
                <option value="6789" <?php echo ($branch_id == '6789') ? 'selected' : ''; ?>>
                    Winnipeg
                </option>
                <option value="4569" <?php echo ($branch_id == '4569') ? 'selected' : ''; ?>>
                    Saskatoon
                </option>
            </select>
            <span class="error"><?php echo $branch_id_err; ?></span>
            <br>

            <!-- Application type -->
            <h4>Application type</h4>
            Student<input type="radio" name="application_type" value="S" <?php echo ($application_type == 'S') ? 'checked' : ''; ?>>
            Worker<input type="radio" name="application_type" value="W" <?php echo ($application_type == 'W') ? 'checked' : ''; ?>>
            <span class="error"><?php echo $application_type_err; ?></span>
            <br>

            <hr>
            Start of Worker application section
            <hr>

            <!-- workplace -->
            <!-- <H4> Workplace info </H4>
            <input type="text" name="workplace_name" placeholder="name of workplace" value="<?php //echo $workplace_name; ?>">
            <span class="error"><?php// echo $workplace_name_err; ?></span>
            <br> -->

            Business number<input type="number" name="business_number" placeholder="0000099999" value="<?php echo $business_number; ?>">
            <span class="error"><?php echo $business_number_err; ?></span>
            <br>
            

            <!-- Will work at -->
            <H4> Will work at </H4>
            <select name="contract_type">
                <option value="" <?php echo ($contract_type == '') ? 'selected' : ''; ?>>
                    Select contract type
                </option>
                <option value="Full Time" <?php echo ($contract_type == 'Full Time') ? 'selected' : ''; ?>>
                    Full Time
                </option>
                <option value="Part Time" <?php echo ($contract_type == 'Part Time') ? 'selected' : ''; ?>>
                    Part Time
                </option>
                <option value="Contract" <?php echo ($contract_type == 'Contract') ? 'selected' : ''; ?>>
                    Contract
                </option>
            </select>
            <span class="error"><?php echo $contract_type_err; ?></span>
            <br>

            <hr>
            Start of student application section
            <hr>
            
            <!-- Will Study at -->
            <H4> Will Study at </H4>
            <select name="degree_type">
                <option value="" <?php echo ($degree_type == '') ? 'selected' : ''; ?>>
                    Select Degree Type
                </option>
                <option value="Bachelor" <?php echo ($degree_type == 'Bachelor') ? 'selected' : ''; ?>>
                    Bachelor
                </option>
                <option value="Master" <?php echo ($degree_type == 'Master') ? 'selected' : ''; ?>>
                    Master
                </option>
                <option value="PhD" <?php echo ($degree_type == 'PhD') ? 'selected' : ''; ?>>
                    PhD
                </option>
            </select>
            <span class="error"><?php echo $degree_type_err; ?></span>
            <br>
            
            DLI number<input type="number" name="dli_number" placeholder="0000099999" value="<?php echo $dli_number; ?>">
            <span class="error"><?php echo $dli_number_err; ?></span>
            <br>
            
            <!-- Student -->
            <H4> Student info </H4>
            Student number<input type="number" name="student_number" placeholder="99999" value="<?php echo $student_number; ?>">
            <span class="error"><?php echo $student_number_err; ?></span>
            <br>
            
            <hr>
            General application section
            <hr>
            
            <!-- Internationl school -->
            <H4> international School info </H4>
            <input type="text" name="inter_school_name" placeholder="Name of international school" value="<?php echo $inter_school_name; ?>">
            <span class="error"><?php echo $inter_school_name_err; ?></span>
            <br>

            <input type="text" name="inter_school_loc" placeholder="Address of international school" value="<?php echo $inter_school_loc; ?>">
            <span class="error"><?php echo $inter_school_loc_err; ?></span>
            <br>

            <input type="submit" value="Sign Up">
        </form>
    </div>
</body>
</html>