<?
//created by brandon donnelson jan 30 2007
//make a module for viewing the ping monitoring results sent to hostdb.monitor

//@HostID - What hosting account are we looking for monitoring for?
//@host - what host specificaly do we want? if none then  show all
function fun_Monitor($HostID)
{
	global $conn;

	
	//$query = "SELECT * FROM monitor WHERE HostID='$HostID' ORDER BY CategoryID;";
	$query = "SELECT * FROM monitor WHERE  HostID='1' AND  FROM_UNIXTIME(DateCreated, '%Y') = DATE_FORMAT(NOW(), '%Y') AND FROM_UNIXTIME(DateCreated, '%d') = DATE_FORMAT(NOW(), '%d') AND FROM_UNIXTIME(DateCreated, '%m') = DATE_FORMAT(NOW(), '%m') ORDER BY CategoryID";
	$result = mysqli_query($conn,$query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");
	
	echo "<table width=\"175\" border=\"1\" class=\"Table1\">\n";

	while($row = mysqli_fetch_object($result))
	{
		$rsMonitorID 	= $row->MonitorID;
		$rsHostID	= $row->HostID;
		$rsName		= $row->Name;
		$rsDescription	= $row->Description;
		$rsSuccess	= $row->Success;
		$rsOwnerTypeID	= $row->OwnerTypeID;
		$rsOwnerID	= $row->OwnerID;
		$rsCategoryID	= $row->CategoryID;
		$rsActive	= $row->Active;
		$rsLastUpdated	= $row->LastUpdated;
		$rsDateCreated 	= $row->DateCreated;
	
		
		$rsUnixTimeNow = strtotime("Now"); //make sure it isn't greater than ten minutes old
		$diff =  $rsUnixTimeNow - $rsLastUpdated;
	
		$strDate = date("m/d/Y H:i a", $rsLastUpdated); 
		
		//debug
		//echo "$rsLastUpdated::LastUpdated:<br>";
		//echo "$rsUnixTimeNow::Now\n<br>";
		//echo $rsUnixTimeNow - $rsLastUpdated ."::dif<br>";

		//mark time if it hasn't worked in 5 minutes
		if ($diff > 18000)
		{
			$rsDateColor = "RED";
			$strDate = "<font color=\"$rsDateColor\">on $rsLastUpdated<font><font>\n";
		}


		if ($rsSuccess == 1)
		{
			$imgPass = "/intranet/images/good.gif";
		}
		else
		{
			$imgPass = "/intranet/images/fail.gif";
		}

		if ($rsDateCreated)
		{
			$rsDescription = "<br>$rsDescription<br>";
		}
		
		echo "<tr>\n";
		echo "<td>\n";
		echo "<img src=\"$imgPass\"> <font size=\"-1\">$rsName $rsDescription $strDate\n";
		echo "</td>\n";
		echo "</tr>\n";
		
	}
	
	echo "</table>\n";

}//end function

?>