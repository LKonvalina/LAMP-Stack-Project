<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$inData = getRequestInfo();

//DB connection info
$user = 'webapi';
$password = 'cis4004!webapi';
$dbName = 'CIS4004';
$host = '34.23.202.55';
$port = 3306;

//Attemps DB connection
$conn = new mysqli($host, $user, $password, $dbName, $port);

if ($conn->connect_error) {
    returnWithError($conn->connect_error);
}
else {
    //Checks if the user entered something in all the fields
    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $login = $inData["login"];
    $userPassword = $inData["password"];

    if ($firstName === "" || $lastName === "" || $login === "" || $userPassword === "") {
        returnWithError("Missing required fields");
    }
    else {
        //Checks if username exists
        $stmt = $conn->prepare("SELECT ID FROM Users WHERE Login=?");

        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            returnWithError("Username already exists");
        }
        else {
            //Hashes and salts the password if user doesn't exist
            $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

            $stmtInsert = $conn->prepare("INSERT INTO Users(firstName, lastName, Login, Password) VALUES (?, ?, ?, ?)");

            $stmtInsert->bind_param("ssss", $firstName, $lastName, $login, $hashedPassword);

            if ($stmtInsert->execute()) {
                returnWithSuccess();
            }
            else {
                returnWithError("Unable to create account");
            }
            $stmtInsert->close();
        }
        $stmt->close();
    }
    $conn->close();
}

//Takes register information
function getRequestInfo() {
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj) {
    header('Content-Type: application/json');
    echo $obj;
}

function returnWithError($err) {
    $retValue = '{"error":"' .$err .'"}';
    sendResultInfoAsJson($retValue);
}

function returnWithSuccess() {
    $retValue = '{"error":""}';
    sendResultInfoAsJson($retValue);
}

?>
