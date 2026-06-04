
<?php

        // Allow requests from any origin (for development/cross domain support)
        // For better security in production, replace '*' with your web server's actual URL/IP
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

	// Handle the browser's "preflight" OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit;
        }

	// Debug Optional: Uncomment these two lines below temporarily
	// to view PHP errors directly in the browser network tab
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	

	$inData = getRequestInfo();

	$id = 0;
	$firstName = "";
	$lastName = "";
	
	// Require this config file in a secure location outside the web root
   	require_once('/var/www/db_config.php');

    	// Attempt connection to GCP Cloud SQL with secured credentials
    	$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("SELECT ID,firstName,lastName, password FROM Users WHERE Login=?");

        	//Debug: Catch prepare errors (e.g., wrong table name or field issue)
        	if (!$stmt) {
            	returnWithError("Debug: Prepare failed (Select): " . $conn->error);
        	}

		//Hash and salts the password before passing to DB bind param
		$userPassword = $inData["password"];
		$stmt->bind_param("s", $inData["login"]);
		$stmt->execute();
		$result = $stmt->get_result();

		if( $row = $result->fetch_assoc()  )
		{
			if(password_verify($userPassword, $row['password'])) {
			//login successful so get the remaining columns		
			returnWithInfo( $row['firstName'], $row['lastName'], $row['ID'] );
			} else {
        		// Entered Password didn't match!
        		returnWithError("Invalid Login or Password");
    			}
		}
		else
		{
			returnWithError("No User Record Found");
		}

		$stmt->close();
		$conn->close();
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
		exit();//stop the script immediately on error
	}

	function returnWithInfo( $firstName, $lastName, $id )
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
		exit();//stop the script execution
	}

?>
