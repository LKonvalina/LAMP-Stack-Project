<?php

	$inData = getRequestInfo();
	$searchResults = "";
	$searchCount = 0;
	// Connection details
    	$user = 'webapi';
    	$password = 'cis4004!webapi';
    	$dbName = 'CIS4004';
    	$host = '34.23.202.55';
    	$port = 3306;

	$conn = new mysqli($host, $user, $password, $dbname, $port);
	if ($conn->connect_error)
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("select ID,FullName, FirstName, LastName from Contacts where FullName like ? and UserID=?");
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
			$searchResults .= '"' . $row["FullName"] . '"';
		}
		if( $searchCount == 0 )
		{
			returnWithError( "No Records Found" );
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
		$retValue = '{"id":0,"FullName","FirstName":"","LastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>
