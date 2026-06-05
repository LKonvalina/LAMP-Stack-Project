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
$deleteResults = "";
$deleteCount = 0;


require_once('/var/www/db_config.php');
// Attempt connection to GCP Cloud SQL with secured credentials
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

//Verifying database connection 
if ($conn->connect_error)
{
    returnWithError($conn->connect_error);
}
else
{
    //Getting all needed information
    $contactID = $inData["id"] ?? "";
    $userID = $inData["userId"] ?? "";

	//fields below not needed for delete
	//    $firstName = $inData["firstName"];
	//    $phone = $inData["phone"];
	//    $email = $inData["email"];

    if ($contactID == "" || $userID == "")
    {
        returnWithError("Missing required fields");
    }
    else
    {
        //Deletes the contact if it belongs to the signed in user
        $stmt = $conn->prepare(
            "DELETE FROM Contacts
             WHERE ID = ? AND UserID = ?"
        );

	//Debug: Catch prepare errors (e.g., wrong table name or field issue)
       	if (!$stmt) {
         	returnWithError("Debug: Prepare failed (Delete): " . $conn->error);
       	}

        $stmt->bind_param("ii", $contactID, $userID);
	
        //Execution of the delete command
        if($stmt->execute())
        {
            //Check to see if the contact was actually deleted
            if ($stmt->affected_rows == 1)
            {
                returnWithSuccess();
            }
            else
            {
               // SQL ran fine, but no rows matched the ID + UserID combination
                returnWithError("Contact not found or unauthorized");
            }
        }
        else
        {
            returnWithError("Unable to execute delete command: " . $stmt->error);
        }

        $stmt->close();
    }

    $conn->close();
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-Type: application/json');
    echo $obj;
}

function returnWithSuccess()
{
    $retValue = '{"error":""}';
    sendResultInfoAsJson($retValue);
}

function returnWithError($err)
{
    $retValue = '{"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}


?>
