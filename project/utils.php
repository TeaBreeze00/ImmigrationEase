<?php
include "../personalconfig.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db_conn = NULL;
$success = true;
$show_debug_alert_messages = False;

	function debugAlertMessage($message)
	{
		global $show_debug_alert_messages;

		if ($show_debug_alert_messages) {
			echo "<script type='text/javascript'>alert('" . $message . "');</script>";
		}
	}

	function executePlainSQL($cmdstr)
	{ //takes a plain (no bound variables) SQL command and executes it
		//echo "<br>running ".$cmdstr."<br>";
		global $db_conn, $success;

		$statement = oci_parse($db_conn, $cmdstr);
		//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
			echo htmlentities($e['message']);
			$success = False;
		}

		$r = oci_execute($statement, OCI_COMMIT_ON_SUCCESS);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = oci_error($statement); // For oci_execute errors pass the statementhandle
			echo htmlentities($e['message']);
			$success = False;
		}

		return $statement;
	}

    function connectToDB()
	{
		global $db_conn;
		global $DB_USER;
		global $DB_PASS;
		global $DB_HOST;

		$db_conn = oci_connect($DB_USER, $DB_PASS, $DB_HOST);

		if ($db_conn) {
			logMessage("Connected to Database");
			return true;
		} else {
			debugAlertMessage("Cannot connect to Database");
			$e = OCI_Error(); // For oci_connect errors pass no handle
			echo htmlentities($e['message']);
			return false;
		}
	}

    function disconnectFromDB()
    {
		global $db_conn;
		
		logMessage("Disconnected from Database");
		logMessage("==========================");
        debugAlertMessage("Disconnect from Database");
        oci_close($db_conn);
    }

	function executeBoundSQL($cmdstr, $list)
	{
		/* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

		global $db_conn, $success;
		$statement = oci_parse($db_conn, $cmdstr);

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn);
			echo htmlentities($e['message']);
			$success = False;
		}

		foreach ($list as $tuple) {
			foreach ($tuple as $bind => $val) {
				//echo $val;
				//echo "<br>".$bind."<br>";
				oci_bind_by_name($statement, $bind, $val);
				unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
			}

			$r = oci_execute($statement, OCI_DEFAULT);
			if (!$r) {
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				$e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
				echo htmlentities($e['message']);
				echo "<br>";
				$success = False;
			}
		}
	}

	function printQueryResult($result, $header = "") {
		// Check if the result has at least one row
		$firstRow = oci_fetch_assoc($result);
		if (!$firstRow) {
			logMessage("No data found.");
			return 0;
		}

		if ($header) {
			echo "<h1>". $header . "</h1>";
		}
	
		// Start the table and print headers
		echo "<table border='1'>";
		echo "<tr>";
		foreach (array_keys($firstRow) as $header) {
			echo "<th>" . htmlspecialchars($header) . "</th>";
		}
		echo "</tr>";
	
		// Print the first row of data
		echo "<tr>";
		foreach ($firstRow as $cell) {
			echo "<td>" . htmlspecialchars($cell) . "</td>";
		}
		echo "</tr>";
	
		// Print the rest of the rows
		while ($row = oci_fetch_assoc($result)) {
			echo "<tr>";
			foreach ($row as $cell) {
				echo "<td>" . htmlspecialchars($cell) . "</td>";
			}
			echo "</tr>";
		}
	
		echo "</table>";
		return 1;
	}

	function readSQLFile($path)
	{// Reads the given file for SQL statements and rerturns an array of SQL sttements
		$SQLarr = array();
		$line = "";
		
		$myfile = fopen($path, "r") or die("Unable to open SQL file!");
		
		while(!feof($myfile)) {
			$char = fread($myfile,1);
			if ($char === ";") {
				array_push($SQLarr,$line);
				$line = "";
			} else if ($char != "\n") {
				$line .= $char;
			}
		}
		
		fclose($myfile);
		return $SQLarr;
	}

	function executeSQLFile($path)
	{
		global $db_conn;
		$arr = readSQLFile($path);
		
		foreach ($arr as $index => $statement) {
			executePlainSQL($statement);
			oci_commit($db_conn);
		}
	}
	
	function generateRandomString($length) {
		$characters = '0123456789';
		$charactersLength = strlen($characters);
		$randomString = '';
		
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		
		return $randomString;
	}
	
    function check_data_exists($query)
    { // returns TRUE if the following query returns any result, else false
        global $db_conn;
        $statement = executePlainSQL($query);
        oci_commit($db_conn);
        
        $row = oci_fetch_assoc($statement);
        if ($row) {
            logMessage("FOUND DATA!");
            logMessage(implode(', ', $row));
            return true;
        } else {
            logMessage("NO DATA FOUND!");
            return false;
        }
    }

	/**
	 * Logs a message to a specified file within the logs/ directory.
	 *
	 * @param string $message The message to log.
	 * @param string $logFile The log file name. Defaults to 'application.log'.
	 */
	function logMessage($message, $logFile = 'application.log') {
		$logDirectory = __DIR__ . '/logs';

		// Check if the logs directory exists, if not, create it.
		if (!file_exists($logDirectory)) {
			mkdir($logDirectory, 0755, true);
		}

		$filePath = $logDirectory . '/' . $logFile;
		
		// Format the message with a timestamp.
		$formattedMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

		// Append the message to the log file.
		file_put_contents($filePath, $formattedMessage, FILE_APPEND);
	}

	function separateString($string, $separator) {
		// Use PHP explode function to split the string
		$parts = explode($separator, $string);
		return $parts;
	}

	function sanitizeString($input) {
		$charactersToRemove = array(";", "'","WHERE","SELECT","FROM","GROUP","BY","IN","DROP","CREATE","TYPE");
		$sanitizedInput = str_replace($charactersToRemove, "", $input);	
		return $sanitizedInput;
	}
	function reducedSanitizeString($input) {
		$charactersToRemove = array(";", "'");
		$sanitizedInput = str_replace($charactersToRemove, "", $input);	
		return $sanitizedInput;
	}
?>