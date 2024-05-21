<?php
include "pageselect.php";
?>

<?php
session_start();
include "../utils.php";
$login_err = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are provided
    if (isset($_POST['passport_number']) 
        && isset($_POST['email'])
        && isset($_POST['password'])) {
        
        $passport_number = sanitizeString($_POST['passport_number']);
        $email = sanitizeString(($_POST['email']));
        $password = reducedSanitizeString($_POST['password']);

        if (connectToDB()) {
            $all_correct = true;

            $result= executePlainSQL("SELECT * FROM IMMIGRANT WHERE PASSPORTNUMBER = '{$passport_number}'");
            oci_commit($db_conn);

            while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
                if ($row["EMAIL"] !== $email) {
                    $all_correct = false;
                    $login_err = "Email Not Found";
                } else {
                    if ($row["PASSCODE"] !== $password) {
                        $all_correct = false;
                        $login_err = "Password Incorrect";
                    }
                }
                break;
            }
            if ($all_correct === true) {
            // echo "Logged in";
            $_SESSION['PASSPORTNUMBER'] = $passport_number;
            
            if (check_data_exists("SELECT * FROM IMMIGRANT WHERE PASSPORTNUMBER = '{$passport_number}' AND USERTYPE = 'A'")) {
                // Admin logged in
                $_SESSION['USERTYPE'] = 'A';

                header("Location: stats.php", true, 301);
                exit();
            }


            header("Location: home.php", true, 301);
            exit();
            } else {
                $error = "Invalid credentials";
            }

            disconnectFromDB();
        }
        
    } else {
        $error = "Please provide all required fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="text" name="passport_number" placeholder="00000000" required>
            <input type="text" name="email" placeholder="example@domain.com" required>
            <input type="password" name="password" placeholder="Password" required> <br>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account or applying for first time? <a href="apply.php">Apply here</a>.</p>
        <span class="error"><?php echo $login_err; ?></span>
    </div>
</body>
</html>