<?
//created by brandon donnelson  jan 21,2007, finished feb 6,2007, wow hard one...
//to optimize mysql columns in specified table












//TODO
//on Date format, its limiited to %d/%m/%Y for now, need change possibly later
//Update or Make New table out of optimized data OR Update Fields in another table with the same column name
//maybe improve integertype selection depending on how big the numbers are in the column - would need the highest value for the column ->would be good for tinyint too 0,1



//DataTypes
//1. Numbers [DECIMAL (has period) ,INTEGER] - become integer or decimal
//2. Date [(has "00/00/00" | "00-00-00")] - become integer
//3. Text [VARCHAR (< 255)|Text (>255)]

//Mysql Var Layout
// $arRow[$i] 
//	=> $arColumn['MYSQL_COLUMN_NAME']= $DataType 
//	=> $arColumn_Lenth['MYSQL_COLUMN_NAME'] = $DataLength
//	=> $arColumn_Lenth_Decimal['MYSQL_COLUMN_NAME'] = $Chars_After_Decimal



//NOTE; pick up the slack and alter field in mysql insert data


function fun_Mysql_Sample_Field_Data_Type($Data, &$rtnDataLength, &$rtnZeroFill, &$rtnLenth_After_Decimal)
{
	$DataLength = strlen($Data);

	//(getting error) where there is text in the data //and (preg_match("/([a-z]+)/i", $Data) == false)//general, should catch most

	//Decimal - 
	if (preg_match("/^(\d+\.\d+|\.\d+)/", $Data) == true and (preg_match("/([a-z]+)/i", $Data) == false))
	{
		$DataType = "Decimal";
		
		$arDec = split("\.", $Data);
		$Lenth_After_Decimal = strlen($arDec[1]);
	}


	//Integer and is it a ZeroFill Integer??
	if (preg_match("/(\D)/", $Data) == false and (preg_match("/([a-z]+)/i", $Data) == false)) //within mysql range -> -2147483648 to +2147483647 values if it is unsigned
	{
		$DataType = "Integer";
		
		//does the integer start with 0?? -> THEN NEED TO ZEROFILL
		if (preg_match("/(^0)/", $Data) == true)
		{
			$ZeroFill = 1;
		}
		else
		{
			$ZeroFill = 0; //just to make sure this is clear
		}
	}//if int


	//Date
	$regDate = "/([0-9]{1,2}[-\/][0-9]{1,2}[-\/][0-9]{2,4})/";
	if (preg_match($regDate, $Data) == true)
	{
		$DataType = "Date";
	}

	//text: Varchar < 255,  Blob|Text=255 and 65535 characters.
	if (!$DataType)
	{
		if ($DataLength < 255)
		{
			$DataType = "Varchar";
		}
		else
		{
			$DataType = "Text";
		}
	}

	$rtnDataLength		= $DataLength;
	$rtnLenth_After_Decimal = $Lenth_After_Decimal; //return by reference
 	$rtnZeroFill 		= $ZeroFill; 		//return by reference
	return $DataType;
}//end function



/////////////////////////////////////////////
//make Mysql column for alter
//@NewDataType
//@NewDataLength
//@NewZeroFill
//@NewCharsADec
//@NewDataLengthTimes 	- (FUTURE) if we want to use CHAR in future - shows if there are any changes in the summary of times the char gets bigger
//@Tolerance		- (FUTURE) this is activated by default, adding a little cushin of room for each set of vars for those who flow bigger
function fun_Mysql_Query_Column_Setup($NewDataType, $NewDataLength, $NewZeroFill, $NewCharsADec, $NewDataLengthTimes, $Tolerance = 1)
{

	//make the datalength bigger by x
	$DataLengthBigger = .2;
	
	if ($NewDataLength == "")
	{
		$NewDataLength = "100";
	}


	if ($NewDataType == "Date")////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	{
		$ColumnType = "Date"; ///// This has to be changed by row, can't be altered into integer
	}

	if ($NewDataType == "Decimal")
	{
		$ColumnType = "DECIMAL($NewDataLength, $NewCharsADec) NOT NULL DEFAULT '0.0'";
	}

	if ($NewDataType == "Text")
	{
		$ColumnType = "TEXT NOT NULL DEFAULT ''";
	}

	if ($NewDataType == "Integer")
	{
		$NumOfZeros = 0;
		for($i=1; $i < $NewDataLength; $i++)
		{
			$NumOfZeros .= "0";
		}

		if ($NewZeroFill) // NOT NULL DEFAULT 0000000000000000
		{
			$AddInt = "($NewDataLength) UNSIGNED ZEROFILL NOT NULL DEFAULT $NumOfZeros";
		}
		else
		{
			$AddInt = " NOT NULL DEFAULT 0";
		}

		//this is very aproximate for now
		if ($NewDataLength > 9) //any thing over 9 digits, even thogh integer can do bigger numbers, rounding down a little here
		{
			$IntergerType = "BIGINT";
		}
		else
		{
			$IntergerType = "INTEGER";
		}

		$ColumnType = "$IntergerType$AddInt";
	}


	if ($NewDataType == "Varchar")
	{
		if ($Tolerance)
		{
			$NewDataLength = $NewDataLength + ceil($NewDataLength * $DataLengthBigger);
		}

		if ($NewDataLength < 255)
		{
			$ColumnType = "VARCHAR($NewDataLength) NOT NULL DEFAULT ''";
		}
		else
		{
			$ColumnType = "TEXT NOT NULL DEFAULT ''";
		}
	}

	return $ColumnType;
}//end fun_column_summary







/**
* transform date into unix timestamp and update the mysql field
* @author Brandon Donnelson sept 4 2007
*
* @param string $DB
* @param string $Sample_Table mysql table
* @param string $New_Table_ID 
*
* @returns bol true
*/
function fun_Mysql_Optimize_Date($DB, $Sample_Table, $New_Table_ID, $Column, $OtherDBConn="")
{

	if (!$New_Table_ID)
	{
		$New_Table_ID = fun_Mysql_getPrimaryKeyColumn($DB, $Sample_Table, $OtherDBConn);
	}

	$query = "SELECT $New_Table_ID, $Column FROM `$DB`.`$Sample_Table`";
	$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");

	while($row = mysqli_fetch_object($result))
	{
		$rsID 		= $row->$New_Table_ID;
		$rsColumn 	= $row->$Column;

		$rsColumn = strtotime($rsColumn);

		$query = "UPDATE `$DB`.`$Sample_Table` SET $Column = '$rsColumn' WHERE ($New_Table_ID = '$rsID')";
		echo "DatesToInteger:$rsColumn -> $query\n";
		fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
	}

	return TRUE;
}//end fun_Mysql_Optimize_Date









//going to become a function shortly
//MAIN FUNCTION
//@DB
//@Sample_Table - Table that we are working on to optimize the mysql column type
//@Test_HowMany_Rows - how many rows to sample
//@New_Table	- not used (Future for changing a different table)
//@New_Table_ID - not used
function fun_Mysql_Optimize_Columns($DB, $Sample_Table, $Test_HowMany_Rows=500, $New_Table="", $New_Table_ID="")	
{
	global $conn;



	echo "\n\n<pre>\n";
	echo "\n\n\n************************************* Starting Mysql Optimize *************************************\n\n";



	//these didn't work, b/c sometimes I need it to skip and others dont need it
	//1. problem 1 - no records to optimize - fixed
	//2. problem 2 - not enough records to optimize correctly, will fix in mysql insert
	// 	if ($Test_HowMany_Rows < 100)
	// 	{
	// 		$Test_HowMany_Rows = 300;
	// 	}
		
	
	
	// 	if ($rsTotal < $Test_HowMany_Rows)
	// 	{
	// 		$str = "Skipped rsTotal < TestHowManyRows($Test_HowMany_Rows)\n";
	// 		echo "$str";
	// 		return $str;
	// 	}
	
	//got to possible problems with this, on optimizing a small table that will possibly change the fields to being to small or the wrong type


	//skip if no records to optimize
	
	$query = "SELECT COUNT(*) as Total FROM `$DB`.`$Sample_Table`;";
	$field = "Total";
	$rsTotal = fun_SQLQuery2($query, $field);

	echo "Enough rows to optimize???? - rowsTotal=($rsTotal)\n";
	echo "sample table query: $query\n";

	if ($rsTotal < 1)
	{
		$str = "Skipping Optimize\n****  Optimize END  **** \n";
		echo "$str";
		return FALSE;
	}
	

	//debug
	//die("\n\nin opto\n\n");



	
	//get fields from db - SHOW COLUMNS FROM mytable FROM mydb;
	$query = "SHOW COLUMNS FROM `$DB`.`$Sample_Table` FROM `$DB`;";
	echo "$query\n";
	$field = "Field";
	$arColumns = fun_SQLQuery_Array($query, $field);
	


	//get mysql info
	$query = "SELECT * FROM `$DB` . `$Sample_Table` ORDER BY RAND() LIMIT 0,$Test_HowMany_Rows";
	$result = mysqli_query($conn, $query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query\n");
	
	$i=0;
	while($row = mysqli_fetch_object($result))
	{
		
		foreach ($arColumns as $Column)
		{
			$rsData = $row->$Column;
		
			//save the vars to array
			if ($rsData)
			{
				$arRow[$i][$Column]['Data'] 		= $rsData;
				$arRow[$i][$Column]['DataType'] 	= fun_Mysql_Sample_Field_Data_Type($rsData, $rtnDataLength, $rtnZeroFill, $rtnLenth_After_Decimal);
				$arRow[$i][$Column]['DataLenth'] 	= $rtnDataLength;
			}
	
			if ($rtnZeroFill != "")
			{
				$arRow[$i][$Column]['ZeroFill']	= $rtnZeroFill;
			}
	
			if ($arRow[$i][$Column]['DataType'] == "Decimal")
			{
				$arRow[$i][$Column]['Chars_After_Dec']	= $rtnLenth_After_Decimal;
			}
		}
	
		$i++;
	}//end queryable rs
	
	
	//debug
	//print_r($arRow);
	
	
	
	
	//move through columns
	$NewDataType = "";
	$NewDataLength = 0;
	foreach ($arColumns as $Column)
	{
		echo "\n\n\nColumn:$Column\n";
		//move through all the test rows
		$NewDataLengthTimes = 0;
		for ($i=0; $i < count($arRow); $i++)
		{
			$cData 		= $arRow[$i][$Column]['Data'];	
			$cDataType 	= $arRow[$i][$Column]['DataType'];	
			$cDataLenth 	= $arRow[$i][$Column]['DataLenth'];
			$cZeroFill	= $arRow[$i][$Column]['ZeroFill'];
			$cCharsADec	= $arRow[$i][$Column]['Chars_After_Dec'];
			
	
			if ($cDataType and $NewDataType != "Varchar")
			{
				$NewDataType = $cDataType;
			}
			
			if ($NewDataType == "Varchar") //default is varchar
			{
				$NewDataType = "Varchar";
			}
	
			if ($NewDataType == "Decimal") //change fron integer to decimal
			{
				$NewDataType = "Decimal";
			}
	
			if (($NewCharsADec < $cCharsADec) && ($cCharsADec != "")) //greatest # after decimal
			{
				$NewCharsADec = $cCharsADec;
			}
		
			if (($cDataType == "Integer") && ($cZeroFill == 1)) //if one has zerfill and is integer, then all do
			{
				$NewZeroFill = 1;
			}
	
	
			if ($NewDataLength < $cDataLenth)
			{
				$NewDataLength = $cDataLenth;
				$NewDataLengthTimes = $NewDataLengthTimes + 1; //singal that it could be a character(DataLength)
			}
	
	
		
			echo "$i:DataType:$cDataType DataLength:$cDataLenth ZeroFill:$cZeroFill CharAfterDec:$cCharsADec Char:$NewDataLengthTimes  $cData\n";
		}//for each row for that column
	
	
	
	
		if (($NewDataType == "Integer") && ($NewCharsADec > 0)) //set it to decimal data type if there are decimals
		{
			$NewDataType = "Decimal";
		}
	
		if (($NewDataType == "Varchar") && ($NewDataLength > 250)) //using some tolerance
		{
			$NewDataType = "Text";
		}
	
		if ($NewDataType == "") //if there is nothing to optimize
		{
			$NewDataType  = "Varchar";
		}
	
		$ColumnType = fun_Mysql_Query_Column_Setup($NewDataType, $NewDataLength, $NewZeroFill, $NewCharsADec, $NewDataLengthTimes);
		
		//$arSummary[$Column]['Data']		= "Summary";
		//$arSummary[$Column]['DataType'] 	= $NewDataType;
		//$arSummary[$Column]['DataLenth']	= $NewDataLength;
		//$arSummary[$Column]['ZeroFill']		= $NewZeroFill;
		//$arSummary[$Column]['Chars_After_Dec']	= $NewCharsADec;
		$arSummary[$Column] = $ColumnType;
		
		echo "00:DataType:$NewDataType DataLength:$NewDataLength ZeroFill:$NewZeroFill CharAfterDec:$NewCharsADec Char:$NewDataLengthTimes - $ColumnType\n";
	
	
	
		$NewDataType 	= "";
		$NewDataLength 	= "";
		$NewZeroFill 	= "";
		$NewCharsADec 	= "";
	
		
	}//end for each columns
	

	echo "\n\n";
	echo "Optimization Summary\n";
	
	//Mysql Column Summary of what to do and how to optimize them
	print_r($arSummary);




	//1. Get primary key for skip
	$query = "DESCRIBE `$DB`.`$Sample_Table`";
	$result = mysqli_query($conn, $query) or die("Select Query failed : $query<br>\n" . mysqli_error() . " :<br>\n");
	while($row = mysqli_fetch_object($result))
	{
		$rsField = $row->Field;
		$rsKey 	 = $row->Key;

		if (preg_match("/pri/i",$rsKey))
		{
			//get rid of the primary key
			unset($arSummary[$rsField]);
		}
	}





	//2.DATE -> UPDATE hostdb.import_sc_sales SET test=UNIX_TIMESTAMP(STR_TO_DATE(test, "%m/%d/%Y")) //2007-08-15 15:00:16
	//update date first to integer format -before altering to mysql integer column type
	foreach ($arSummary as $Column => $ColumnType)
	{
		if (preg_match("/Date/", $ColumnType))
		{
			//$query = "UPDATE `$DB`.`$Sample_Table` SET $Column=UNIX_TIMESTAMP(STR_TO_DATE($Column, '%m/%d/%Y'))";
			//echo "DatesToInteger:$Column -> $query\n";
			//$result = mysqli_query($conn, $query) or die("Select Query failed : $query<br>\n" . mysqli_error() . " :<br>\n");

			//using php to transform string to unixtimestamp
			fun_Mysql_Optimize_Date($DB, $Sample_Table, $New_Table_ID, $Column, $OtherDBConn);

			$arSummary[$Column] = "INTEGER NOT NULL DEFAULT 0";
		}
	}


	//3.Setup Query
	foreach ($arSummary as $field => $ColumnType)
	{
		$arMysql[] = "MODIFY COLUMN `$field` $ColumnType";
	}
	
	print_r($arMysql);






// 	//are we working on current table or figuring out what table to make or fix
// 	if ($New_Table)
// 	{
// 		$Check_Table = $New_Table;
// 	}
// 	else
// 	{
// 		$Check_Table = $Sample_Table; //working same table as we are optimizing	
// 	}
// 
// 	//Are we working on a live table??
// 	$query = "SHOW TABLE STATUS FROM $DB WHERE (Name = '$Check_Table')";
// 	$field = "Name";
// 	$IsTable = fun_SQLQuery2($query, $field);
// 
// 	if (!$IsTable) //make table if need be
// 	{
// 		//make table
// 		$query = "CREATE TABLE `$DB`.`$New_Table` ( `$New_Table_ID` int  NOT NULL AUTO_INCREMENT, PRIMARY KEY(`$New_Table_ID`)) ENGINE = MYISAM;";
// 		echo "$query\n";
// 		mysqli_query($conn1, $query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query"); 
// 	}


	//setup mysql query for altering current table
	$csvColumns = implode(",", $arMysql);
	echo "csvColumns: $csvColumns\n\n\n";

	$query = "ALTER TABLE `$DB`.`$Sample_Table` $csvColumns";
	echo "Modify:$query\n";
	$result = mysqli_query($conn, $query) or die("Select Query failed : $query<br>\n" . mysqli_error() . " :<br>\n");










	//print_r($arRow);
	echo "************************************* The End - Mysql Optimize *************************************";
	echo "</pre>\n";

	//DEBUG
	//die("\n die in opto \n");

	return TRUE;

}//end MAIN function
?>