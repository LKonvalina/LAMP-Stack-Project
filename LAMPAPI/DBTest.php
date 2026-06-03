<?php
    // Allow requests from any origin (for development/cross domain support)
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    // Handle the browser's "preflight" OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit;
    }

    $inData = getRequestInfo();

    // Connection details
    $user = 'webapi';
    $password = 'cis4004!webapi';
    $dbName = 'CIS4004';
    $host = '34.23.202.55';
    $port = 3306;
    // Attempt connection to GCP Cloud SQL
    $conn = new mysqli($host, $user, $password, $dbName, $port);
    if( $conn->connect_error )
    {
        returnWithError( $conn->connect_error );
    }
    else
    {
        // FIX: Removed bind_param since there are no '?' placeholders in this query
        $stmt = $conn->prepare("SELECT count(ID) as numUsers FROM Users");
        $stmt->execute();
        $result = $stmt->get_result();

        if( $row = $result->fetch_assoc() )
        {
            // FIX: Passing the correct database value
            returnWithInfo( $row['numUsers'] );
        }
        else
        {
            returnWithError("No Records Found");
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
        $retValue = '{"numUsers":-1,"error":"' . $err . '"}';
        sendResultInfoAsJson( $retValue );
    }
    // FIX: Updated signature and properly integrated the variable scope
    function returnWithInfo( $numUsers )
    {
        $retValue = '{"Users":' . $numUsers . ',"error":""}';
        sendResultInfoAsJson( $retValue );
    }
?>
