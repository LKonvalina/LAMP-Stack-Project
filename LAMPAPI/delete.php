<?php


$inData = getRequestInfo();

$user = 'webapi';
$password = 'cis4004!webapi';
$dbName = 'CIS4004';
$host = '34.23.202.55';
$port = 3306;

// Attempt DB connection
$conn = new mysqli($host, $user, $password, $dbName, $port);

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
    $email = $inData["email"];v

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