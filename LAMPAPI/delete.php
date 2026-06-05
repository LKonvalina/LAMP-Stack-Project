<?php


$inData = getRequestInfo();


require_once('/var/www/db_config.php');
// Attempt connection to GCP Cloud SQL with secured credentials
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error)
{
    returnWithError($conn->connect_error);
}
else
{
    $contactID = $inData["contactId"];
    $userID = $inData["userId"];
    $firstName = $inData["firstName"];
    $phone = $inData["phone"];
    $email = $inData["email"];

    if ($contactID == "" || $userID == "")
    {
        returnWithError("Missing required fields");
    }
    else
    {
        $stmt = $conn->prepare(
            "DELETE FROM Contacts
             WHERE ID = ? AND UserID = ?"
        );

        $stmt->bind_param("ii", $contactID, $userID);

        if ($stmt->execute())
        {
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
