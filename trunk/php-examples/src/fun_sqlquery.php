<? 
//quick way to look up info
//made by brandon donnelson Arpil 26, 2004


//TODO
//add third field for different connection, for other permission sets -> check this for example fun_SQLQuery_Array($query, $field, $OtherDBConn="")

/**
* raw mysql query
* @param $WantRC - if 1
* @param $WantLID - if 1
* @param $rtnRC - return row count
* @param $rtnLID - return Last ID
* @returns result
*
* example
fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
*/
function fun_SQLQuery($query, $WantRC=0, $WantLID=0, &$rtnRC="", &$rtnLID="", $OtherDBConn="", $arVar="")
{
	global $conn;

	//debug
	//echo "fun_SQLQuery:($query)\n";

	if ($query)
	{
// 		if (($results = $conn->query($query,MYSQLI_USE_RESULT)) === false)
// 		{
// 			printf("Error: %s : $query\n", $conn->error);
// 		}

		$result = mysqli_query($conn,$query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");
		
		if ($WantLID == 1)
		{
			//$rtnLID = $conn->insert_id;
			 $rtnLID = mysqli_insert_id($conn);
		}
		else
		{
			$rtnLID = "";
		}

		if ($WantRC == 1)
		{
			//$rtnRC = $result->num_rows;
			$rtnRC = mysqli_num_rows($result);
		}
		else
		{
			$rtnRC = "";
		}
		
		unset($arVar);

		//debug
		//var_dump($result);

		

		return $result;
	}

	unset($arVar);
	return "fun_SQLQuery: No Query\n";
}//end fun_SQLQuery



//gets one fieldname value
//@query - only one value to get like where id=1
//@field - name of the field of what data
function fun_SQLQuery1($query, $field, $OtherDBConn="")
{
	global $conn;

	if ($OtherDBConn)
	{
		$DBConn = $OtherDBConn;
	}
	else
	{
		$DBConn = $conn;
	}

	if ($query)
	{
		$result = mysqli_query($DBConn,$query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");
		$row = mysqli_fetch_object($result);
		echo $row->$field;
	}
	else
	{
		echo "Error: No Query for fun_SQLQuery2($query,$field);<br>\n";
	}
}//end function


//////////////////////////////////////////////////////
//return var from sql query
//@query - sql query
//@field - field to return
//@OtherDBConn - use other db connection
/*
 * @Example
$rtnData = fun_SQLQuery2($query, $field, $OtherDBConn="");
*/
//returns query
function fun_SQLQuery2($query, $field, $OtherDBConn="")
{
	global $conn;
	
	//debug
	//echo "$query<br>\n";

	if ($OtherDBConn)
	{
		$DBConn = $OtherDBConn;
	}
	else
	{
		$DBConn = $conn;
	}
	
	
	if ($query)
	{
		$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
		$row = mysqli_fetch_object($result);
		return $row->$field;
	}
	else
	{
		echo "Error: No Query for fun_SQLQuery2($query,$field);<br>\n";
	}

}//end function


//return var in the function
function fun_SQLQuery_CSV($query, $field, $OtherDBConn="")
{
	global $conn;


	if ($OtherDBConn)
	{
		$DBConn = $OtherDBConn;
	}
	else
	{
		$DBConn = $conn;
	}


	if ($query)
	{
		//get sql query
		$result = mysqli_query($DBConn,$query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");
		
		while($row = mysqli_fetch_object($result))
		{
			$arReturn[] = $row->$field;
		}//end while(rs)
		
	}//if query	
	
	//check if more than one string - if array that way
	if (is_array($arReturn))
	{
		$strReturn = implode(",", $arReturn);
	}
	else
	{
		$strReturn = $arReturn;
	}
	
	if (!$strReturn)
	{
		$strReturn = 0;
	}
	
	return $strReturn;
}//end function


//return fields as array
//@query 	- what we want to query
//@field 	- what field to return
//@OtherDBConn 	- another permission set from mysql, or object connect
function fun_SQLQuery_Array($query, $field, $OtherDBConn="")
{
	global $conn;

	if ($OtherDBConn)
	{
		$DBConn = $OtherDBConn;
		$test = "Other";
	}
	else
	{
		$DBConn = $conn;
		$test = "normal conn";
	}

	//echo "test:$test";

	if ($query)
	{
		//get sql query
		$result = mysqli_query($DBConn, $query) or die("Select Query failed : " . mysqli_error() . " :<br>\nQuery:$query <br>\n");
		//echo "q:$query\n";
		while($row = mysqli_fetch_object($result))
		{
			$arReturn[] = $row->$field;
		}//end while(rs)
		
	}//if query	
	
	
	//die("\ndie early\n");
	
	return $arReturn;
}//end function


/**
* get a record like arData[field] = value
* @param string $DB
* @param stinrg $Table
* @param string $ID
*
* @returns array $arData  like arData[field] = value
*/
function fun_SQLQuery_InArray($DB, $Table, $ID, $OtherDBConn="", $OtherIDName="")
{
	global $conn;

	//debug
	//echo "\nfun_SQLQuery_InArray[[Table($Table)";

	if ($Table)
	{
		if (!$OtherIDName)
		{
			$PriKeyName = fun_Mysql_getPrimaryKeyColumn($DB, $Table, $OtherDBConn); //get primary id name
		}
		else
		{
			$PriKeyName = $OtherIDName;
		}
		$query = "SELECT * FROM `$DB`.`$Table` WHERE ($PriKeyName = '$ID')";
			
		//debug
		//echo "query:($query)";

		$arColumns = fun_Mysql_getColumns($Table, $DB); //get columns

		$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn, $arVar="");
		while($row = mysqli_fetch_object($result))
		{
			foreach ($arColumns as $field)
			{
				$arData[$field] = $row->$field;
			}
		}//end while(rs)
	}//if query	
	
	//debug
	//echo "]]fun_SQLQuery_InArray\n";
	
	return $arData;
}//end function




//return var in the function
//@query - 
//@returns count
function fun_SQLQuery_CountRows($query)
{
	global $conn;
	$result = mysqli_query($conn,$query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query"); 
	return mysqli_num_rows($result);
}//end function




//return fields as array
//@query 	- what we want to query
//@field 	- what field to return
//@OtherDBConn 	- another permission set from mysql, or object connect
function fun_SQLQuery_Array_PHPLot($query, $arfields, $OtherDBConn="")
{
	global $conn;

	if ($OtherDBConn)
	{
		$DBConn = $OtherDBConn;
	}
	else
	{
		$DBConn = $conn;
	}

	if ($query)
	{
		//get sql query
		$result = mysqli_query($DBConn, $query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");
		
		while($row = mysqli_fetch_object($result))
		{
			$arReturn[] = $row->$field;
		}//end while(rs)
		
	}//if query	
	
	
	return $arReturn;
}//end function


//not in use yet
function fun_SQLQuery_Limit($DB, $Table, $LS, $LE, $CustomWhere, $OrderBy, $arVar, $rtnRC)
{

	//Paging - limit start to limit end - need to change these vars bac,
	if ($LS or $LE)
	{
		if (!$LS)
		{
			$LS = 0;
		}

		if (!$LE)
		{
			$LE = 25;
		}
		$HowMany  = $LE - $LS;
		$strLimit = " LIMIT $LS,$HowMany; ";
		//echo "rtnQ: $rtnQuery\n";
	}

	$strQuery = "SELECT * ";
	$strQuery .= "FROM `$DB`.`$Table` ";
	$strQuery .= "WHERE $CustomWhere ";

	//build query
	$rtnQuery .= $strQuery . $OrderBy . $strLimit;

	return $rtnQuery;
}

?>
