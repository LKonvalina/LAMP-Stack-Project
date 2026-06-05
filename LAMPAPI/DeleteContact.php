<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

 if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit;
    }

$inData = getRequestInfo();


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
    $contactID = $inData["contactId"];
    $userID = $inData["userId"];


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

        $stmt->bind_param("ii", $contactID, $userID);

        //Execution of the delete command
        if ($stmt->execute())
        {
            //Check to see if the contact was actually deleted
            if ($stmt->affected_rows > 0)
            {
                returnWithSuccess();
            }
            else
            {
                returnWithError("Contact not found");
            }
        }
        else
        {
            returnWithError("Unable to delete contact");
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
