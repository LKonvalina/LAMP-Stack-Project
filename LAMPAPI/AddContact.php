<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$inData = getRequestInfo();

require_once('/var/www/db_config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
}
else {
    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $email = $inData["email"];
    $phone = $inData["phone"];
    $userId = $inData["userId"];

    if ($firstName === "" || $lastName === "" || $userId === "") {
        returnWithError("Missing required fields");
    }
    else {
        $fullName = $firstName . " " . $lastName;

        $stmt = $conn->prepare("INSERT INTO Contacts (FullName, FirstName, LastName, Phone, Email, UserID) VALUES (?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            returnWithError(
                "Debug: Prepare failed: " . $conn->error
            );
        }

        $stmt->bind_param("sssssi", $fullName, $firstName, $lastName, $phone, $email, $userId);

        if ($stmt->execute()) {
            returnWithSuccess();
        }
        else {
            returnWithError(
                "Unable to add contact"
            );
        }
        $stmt->close();
    }
    $conn->close();
}

function getRequestInfo() {
    return json_decode(
        file_get_contents('php://input'),
        true
    );
}

function sendResultInfoAsJson($obj) {
    header('Content-Type: application/json');
    echo $obj;
}

function returnWithError($err) {
    $retValue =
        '{"error":"' . $err . '"}';

    sendResultInfoAsJson($retValue);
    exit;
}

function returnWithSuccess() {
    $retValue =
        '{"error":""}';

    sendResultInfoAsJson($retValue);
    exit;
}

?>
