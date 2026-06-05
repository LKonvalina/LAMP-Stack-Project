<?php

	$inData = getRequestInfo();
	$searchResults = "";
	$searchCount = 0;

	// Require this config file in a secure location outside the web root
   	require_once('/var/www/db_config.php');

    	// Attempt connection to GCP Cloud SQL with secured credentials
    	$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);


	if ($conn->connect_error)
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("select ID,FullName, FirstName, LastName, Email, Phone from Contacts where FullName like ? and UserID=?");
		$FullName = "%" . $inData["search"] . "%";
		$stmt->bind_param("ss", $FullName, $inData["userId"]);
		$stmt->execute();
		$result = $stmt->get_result();
		while($row = $result->fetch_assoc())
		{
			if( $searchCount > 0 )
			{
				$searchResults .= ",";
			}
			$searchCount++;
			$searchResults .= '{"ID":' . $row["ID"] . ',"FirstName":"' . $row["FirstName"] . '","LastName":"' . $row["LastName"] . '","Email":"' . $row["Email"] . '","Phone":"' . $row["Phone"] . '"}';
		}
		if( $searchCount == 0 )
		{
			returnWithInfo( $searchResults );
		}
		else
		{
			returnWithInfo( $searchResults );
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
		$retValue = '{"id":0,"FullName":"","FirstName":"","LastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>
