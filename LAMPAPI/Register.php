<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// Debug Optional: Uncomment these two lines below temporarily
// to view PHP errors directly in the browser network tab
//ini_set('display_errors', 1);
//error_reporting(E_ALL);


$inData = getRequestInfo();

// Require this config file in a secure location outside the web root
require_once('/var/www/db_config.php');

// Attempt connection to GCP Cloud SQL with secured credentials
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

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
       
        //Debug: Catch prepare errors (e.g., wrong table name)
        if (!$stmt) {
            returnWithError("Debug: Prepare failed (Select): " . $conn->error);
        }

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
	   // Debug: Catch prepare errors on the insert statement
            if (!$stmtInsert) {
                returnWithError("Debug: Prepare failed (Insert): " . $conn->error);
            }


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
    exit; // Halt execution after an error is returned
}

function returnWithSuccess() {
    $retValue = '{"error":""}';
    sendResultInfoAsJson($retValue);
    exit; // Halt execution on success
}

?>
