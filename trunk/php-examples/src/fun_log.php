<?
//created by brandon donnelson aug 29 2007
//log functions


/**
* log Import events for generic
* @param string $ThisService
* @param boolean $Success [1|0] pass or fail
* @param string $Name - name of activiy or event
* @param string $Description - description of activity
* @returns int $LogID
*/
function fun_Log($LogID, $ThisService, $Success, $Name, $Description)
{
	global $conn, $intTimeStamp;

	$HostID 	= 1;
	$Name 		= $Name;
	$Description 	= $Description;
	$CategoryID	= $ThisService;
	$Success	= $Success; //1=yes, 0=no
	$DateCreated	= $intTimeStamp;
	$LastUpdated	= $intTimeStamp;

	if ($LogID) //update
	{
		$query = "UPDATE monitor SET LastUpdated = '$LastUpdated', Success='$Success', CategoryID='$CategoryID' WHERE (MonitorID='$LogID'); ";
	}
	else //insert
	{
		$query = "INSERT INTO monitor (DateCreated, HostID, Name, Description, Success, CategoryID) 
			  VALUES ('$DateCreated', '$HostID', '$Name', '$Description', '$Success', '$CategoryID');";
	}

	//run the query 
	if ($query)
	{
		fun_SQLQuery($query, $WantRC=0, $WantLID=1, $rtnRC="", $rtnLID="", $arVar="");
		$LogID = $rtnLID;
	}

	return $LogID;

}//end fun_Log_Import




/**
* log Import events for importing files
* @param string $csv_file
* @param boolean $Pass
* @param string $Description
* @param string $ImportFlag
*/
function fun_Log_Import($csv_file, $Pass, $Description, $ImportFlag=0)
{
	global $conn, $intTimeStamp;

	preg_match("/ftp:\/\/(.*)/i", $Site, $arMatch); //just get the URL
	$Site = $arMatch[1];

	//record to log to mysql for web page monitoring
	$HostID 	= 1;
	$Name 		= "Import $csv_file";
	$Description 	= "$Description";
	$CategoryID	= 1033;
	$Success	= $Pass; //1=yes, 0=no
	$DateCreated	= $intTimeStamp;
	$LastUpdated	= $intTimeStamp;
	//$ImportFlag	= 1; //this is to notify the next stage that we are ready to move data


	//check to see if we need to insert or update?
	$query = "SELECT MonitorID FROM monitor WHERE (HostID='$HostID') AND (Name = '$Name')";
	$field = "MonitorID";
	$rsMonitorID = fun_SQLQuery2($query, $field);	

	if ($rsMonitorID) //update
	{
		$query = "UPDATE monitor SET LastUpdated = '$LastUpdated', Success='$Success', ImportFlag='$ImportFlag', CategoryID='$CategoryID'
			 WHERE (MonitorID='$rsMonitorID'); ";
	}
	else //insert
	{
		$query = "INSERT INTO monitor (DateCreated, HostID, Name, Description, Success, CategoryID) 
			  VALUES ('$DateCreated', '$HostID', '$Name', '$Description', '$Success', '$CategoryID');";
	}

	//run the query 
	if ($query)
	{
		fun_SQLQuery($query, $WantRC=0, $WantLID=1, $rtnRC="", $rtnLID="", $arVar="");
		$ImportID = $rtnLID;
	}

	return $ImportID;

}//end fun_Log_Import




?>