<?
//created by brandon donnelson april 7th, 2007
//place for mysql functions that do things
//moved some of the functions from other mysql code (fun_mysql_import_csv.php)


/** 
* Combine where query parts "l='1' AND l='2' AND l='3'"
* @param string $Type [AND|OR|LIKE|(MYSQL where thingy)] mysql combine
* @param mixed $arQuery array or not of where query parts
* @returns string of combined query parts with implode if need be
*/
function fun_MySQL_Query_CombineWhere($Type, $arQuery)
{
	if (is_array($arQuery))
	{
		$strQuery = implode(" $Type ", $arQuery);
	}
	else
	{
		$strQuery = "$arQuery";
	}

	//want parenthesis?
	$strQuery = "$strQuery";	

	return $strQuery;
}//end fun_MySQL_Query_CombineWhere


/**
* insert data into mysql - append to table - appends(adds) columns
* @author Brandon Donnelson
*
* @param string $DB
* @param string $New_Table
* @param string $arData['key'] = $value;
* 
* @todo New_Table_ID -> skip it?
*/
function fun_MySQL_Insert_Data($DB, $New_Table, $arData, $New_Table_ID = "")
{
	global $conn1;

	//debug
	//echo "\nfun_MySQL_Insert_Data";
	//print_r($arData);

	
	$arKeys = array_keys($arData);
	if (!preg_match("/[a-zA-Z]/i", $arKeys[0]))
	{
		//print_r($arData);
		//echo "WOrky??";

		//i used this array formation in xml2mysql
		//extract fields and data from array
		foreach ($arData as $key => $str)
		{
			//echo "$i.";
			foreach ($arData[$key] as $field => $value)
			{
				//echo "$field:$value  ,  ";
				$arData2[$field] = $value;
			}
		}
	}
	else
	{
		//arData['column/field'] = $value/data;
		$arData2 = $arData;
	}

	//die("deathy early");

	//setup mysql insert
	$arInsert1 = "";
	$arInsert2 = "";
	foreach ($arData2 as $key => $value)
	{
		//fix var for db
		$value = fun_FixVar($value);

		$arInsert1[] = "$key";
		$arInsert3[] = "`$key`"; //used for inserting reserved words in mysql
		$arInsert2[] = "'$value'";
	}

	$strInsert1 = implode(",",$arInsert1);
	$strInsert2 = implode(",",$arInsert2);
	$strInsert3 = implode(",",$arInsert3);


	//check and insert columns if they do not exist in table
	if (!$New_Table_ID)
	{
		$New_Table_ID = "ImportID";
	}
	$NoFieldsInRow0 = ""; //no use in this function
	fun_Mysql_Create_Columns($arInsert1, $New_Table, $New_Table_ID, $DB, $NoFieldsInRow0);


	//insert data
	$query = "INSERT INTO `$DB`.`$New_Table` ($strInsert3) VALUES ($strInsert2);";
	//echo "InsertQuery:$query\n";
	echo " fun_MySQL_Insert_Data:Inserted ";
	fun_SQLQuery($query, $WantRC=0, $WantLID=1, $rtnRC="", $rtnLID, $OtherDBConn="", $arVar="");
	
	//return last ID on insert
	return $rtnLID;
}//end fun_MySQL_Insert_Data



/**
* update table data
* @param $DB
* @param $Table
* @param $arData
* @param $ID_Name - automated this, no longer needed
* @param $rtnError string returns error string on false
* @returns bol 
*/
function fun_Mysql_Update_Data($DB, $Table, $arData, $ID_Name, $rtnError)
{
	if (!$ID_Name)
	{
		$ID_Name = fun_Mysql_getPrimaryKeyColumn($DB, $Table, $OtherDBConn="");
	}


	foreach ($arData as $Field => $Value)
	{
		//debug
		//echo "\n Mysql_Update:[[arData:";
		//print_r($arData);
		//echo "]]:Mysql_Update\n";

		if ($Field == $ID_Name) //this is the unique record to update
		{
			$strWhere = " WHERE ($ID_Name = '$Value')";
		}
		else
		{
			$arUpdate[] = "`$Field` = '$Value'";
			$arFields[] = "$Field";
		}

	}//end loop through ids to update


	//create mysql table fields/columns if need be
	fun_Mysql_Create_Columns($arFields, $Table, $ID_Name, $DB, $NoFieldsInRow0 = "");


	//setup Update Query
	if ($arUpdate)
	{
		$strUpdate = implode(", ",$arUpdate);
	
		if ($strWhere)
		{
			$query = "UPDATE `$DB`.`$Table` SET $strUpdate $strWhere ;";
		}
		else
		{
			$rtnError = "No ID_Name in Data (No strWhere)";
			return FALSE;
		}
	
		if ($query)
		{
			echo "Updated:[[ ID_Name:($ID_Name) ]]";
			fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
			return TRUE;
		} 
	}
	else
	{
		$rtnError = "No Data to Update";
		return FALSE;
	}
}//end fun_Mysql_Update_Data



//insert data into the columns
function fun_Mysql_Insert_Import_Data($arfields, $New_Table, $DB)
{
	global $conn1;

	//debug
	//print_r($fields);
	
	$Insert_fields = "";
	foreach ($arfields as $field)
	{

		//$field = ereg_replace("[\"\r\n\t\\]", "", $field); //take out this stuff in field -> possibly leave in quotes that are not at the ends

		$field = fun_FixVar($field);
		$field = ereg_replace("\\\\", " \\\\ ", $field); //stinking \ gets in the last char and makes havick with mysql insert
		$field = trim($field);
		$Insert_fields .= ", '$field'";
	}

	$query = "INSERT INTO `$DB`.`$New_Table`  VALUES('' $Insert_fields);"; //first '' is for ID, then it will offset its values correctly
	echo "$query\n";
	mysqli_query($conn1, $query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");

}//end fun_Insert_Data_Into_Fields




function fun_Mysql_Create_Table($New_Table, $New_Table_ID, $DB, $Drop=1)
{
	global $conn1;

	if ($New_Table_ID == "")
	{
		$New_Table_ID = "ID";
	}

	//debug
	echo "fun_Mysql_Create_Table[[";

	if ($Drop)
	{
		//drop table
		$query = "DROP TABLE IF EXISTS `$DB`.`$New_Table`";
		echo "fun_Mysql_Create_Table: Dropping Table:$query\n";
		mysqli_query($conn1, $query);// or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query"); 
	}

	

	//does table exist?
	$query = "SHOW TABLES FROM $DB like '$New_Table';";
	$result = fun_SQLQuery($query, $WantRC=1, $WantLID=0, $rtnRC, $rtnLID="", $OtherDBConn="", $arVar="");

	//debug
	echo "\nQQQ:$rtnRC: QQ$query\n";	

	if (!$rtnRC)
	{
		//make table
		//$query = "CREATE TABLE $New_Table ('ID' int  NOT NULL AUTO_INCREMENT, PRIMARY KEY('ID'))";
		$query = "CREATE TABLE `$DB`.`$New_Table` ( `$New_Table_ID` int  NOT NULL AUTO_INCREMENT, PRIMARY KEY(`$New_Table_ID`)) ENGINE = MYISAM;";
		echo "Create Table:$query\n";
		mysqli_query($conn1, $query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query"); 
	}
	else//does the id exists?? - i think i rely on import to put this in
	{
		
	}

	//debug
	echo "]]fun_Mysql_Create_Table";

}//end fun_Mysql_Create_Table




/**
* Make Mysql Columns/Fields
* @param $fields array - name of mysql columns to create
* @param $New_Table string - new table name
* @param $New_Table_ID string - New Table ID name
* @param $DB string - mysql database 
* @param $NoFieldsInRow0 integer - make generic column names for the amount of fields there are
* @returns array mysql fields
* field will error when there is a `field`
*/
function fun_Mysql_Create_Columns($arFields, $New_Table, $ID_Name, $DB, $NoFieldsInRow0 = "")
{
	global $conn, $conn1;

	if (preg_match("/\./",$New_Table))
	{
		die("\n\nError:Seperate the database (no period)\n\n");
	}

	//debug
	//echo "arFields:";
	//print_r($arFields);

	//query tables - check for existing tables
	$query = "SHOW TABLES FROM `$DB`;";
	$field = "Tables_in_$DB";
	$arExistingTables = fun_SQLQuery_Array($query, $field, $conn1);

	//query columns - check for existing columns
	if (in_array($New_Table, $arExistingTables))
	{
		$query = "SHOW COLUMNS FROM `$New_Table` FROM `$DB`;";
		$field = "Field";
		$arExistingColumns = fun_SQLQuery_Array($query, $field, $conn1);
	}

	//print_r($arExistingTables);

	//debug
	//var_dump($arFields);

	$fieldInMysql = false;
	foreach ($arFields as $key => $field)
	{
		if (is_array($arExistingColumns))
		{
			$fieldInMysql = in_array($field, $arExistingColumns);
		}


		//make the mysql column if its not in the table, and doesn't exist already
		if (($field != $ID_Name) and ($fieldInMysql == ""))
		{
			if ($NoFieldsInRow0 == 0)
			{
				$field = fun_Mysql_Check_TableorField_Name($field); //get rid of the weird stuff
			}
			else
			{
				$field = "Field{$key}";
			}

			//alter table
			$query = "ALTER TABLE `$DB`.`$New_Table` ADD COLUMN `$field` TEXT ";
			$result = mysqli_query($conn1, $query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");
	
			$arField[] = $field;
		}

		$fieldInMysql = false;

	}//end foreach ($fields as $key => $field)

	return $arField;
	
}//end function fun_Make_Mysql_Fields




//@TableorField - string - fix field so its acceptable for mysql db
function fun_Mysql_Check_TableorField_Name($TableorField)
{
	$TableorField = trim($TableorField);

	//debug
	//var_dump($TableorField);

	echo "Testing TableorField:($TableorField)";

	//echo "\n\nBefore $TableorField\n";
	$TableorField = ereg_replace("[\"\r\n\t]", "", $TableorField);
	$TableorField = ereg_replace("!", "", $TableorField);
	$TableorField = ereg_replace("@", "", $TableorField);
	$TableorField = ereg_replace("#", "", $TableorField);
	$TableorField = ereg_replace("\$", "", $TableorField);
	$TableorField = ereg_replace("^", "", $TableorField);
	$TableorField = ereg_replace("\*", "", $TableorField);
	$TableorField = ereg_replace("\\\\", "", $TableorField);
	$TableorField = ereg_replace("\)", "", $TableorField);
	$TableorField = ereg_replace("\(", "", $TableorField);
	$TableorField = ereg_replace("\+", "", $TableorField);
	$TableorField = ereg_replace("=", "", $TableorField);
	$TableorField = ereg_replace("~", "", $TableorField);
	$TableorField = ereg_replace("`", "", $TableorField);
	$TableorField = ereg_replace("{", "", $TableorField);
	$TableorField = ereg_replace("}", "", $TableorField);
	$TableorField = ereg_replace("\[", "", $TableorField);
	$TableorField = ereg_replace("\]", "", $TableorField);
	$TableorField = ereg_replace("\|", "", $TableorField);
	$TableorField = ereg_replace(",", "", $TableorField);
	$TableorField = ereg_replace("<", "", $TableorField);
	$TableorField = ereg_replace(">", "", $TableorField);
	$TableorField = ereg_replace("\?", "", $TableorField);
	$TableorField = ereg_replace("&", "", $TableorField);
	$TableorField = ereg_replace("\/", "", $TableorField);
	$TableorField = ereg_replace("%", "per", $TableorField);
	$TableorField = ereg_replace(" ", "_", $TableorField); //error finding "."
	$TableorField = ereg_replace("[\.]", "_", $TableorField); //error finding "."
	
	echo "After $TableorField\n\n";

	return $TableorField;
}//end fun_Mysql_Check_TableorField_Name($TableorField)









//$Copy_Table = "`import`.`nwmls_schooldistricts`";
//$New_Table = "`nwmls`.`ref_schooldistrict`";
//fun_Mysql_CopyTable($Copy_Table,$New_Table);

//copy table to new table
//@Copy_Table - What table to copy
//@New_Table - What table to do it to
function fun_Mysql_CopyTable($Copy_Table, $New_Table)
{
	//this add to only super users can do
	if (1==1)
	{
		$query = "CREATE TABLE $New_Table LIKE $Copy_Table;";
		echo "$query<br>\n";
		fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC, $rtnLID);
		
		$query = "INSERT $New_Table SELECT * FROM $Copy_Table;";
		echo "$query<br>\n";
		fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC, $rtnLID);

		echo "Copied Table $New_Table\n";
	}
}//end fun_Mysql_CopyTable



/**
* get columns from mysql table
* @param string $Table  get columns from this table
* @param string $DB  database
* @return mixed  array or not of columns from table
*/
function fun_Mysql_getColumns($Table, $DB)
{
	$query = "SHOW COLUMNS FROM `$Table` FROM `$DB`;";
	$field = "Field";
	$arColumns = fun_SQLQuery_Array($query, $field);

	return $arColumns;
}//end fun_Mysql_getColumns



/**
* Get primary key column name
* @author Brandon Donnelson sept 4 2007
* @param string $DB
* @param string $Table
* @param string $OtherDBConn future use
* @returns string $ID primary key column name
*/
function fun_Mysql_getPrimaryKeyColumn($DB, $Table, $OtherDBConn="")
{
	//echo "1.fun_Mysql_getPrimaryKeyColumn: $DB, $Table\n";


	//if table looks like this `db`.`table`
	preg_match("/(.*)\.(.*)/", $Table, $arMatch);
	if ($arMatch) {
		$strDBTa 	= $arMatch[0];
		$strDB 		= $arMatch[1];
		$strTable	= $arMatch[2];
		
		//print_r($arMatch);
	}
	
	if ($strDB)	{
		$DB 	= $strDB;
		$Table 	= $strTable;
	}
	
		
	if (!$DB) {
		$DB = "hostdb";
	}
		
	$query = "SHOW COLUMNS FROM $Table FROM $DB;";
	//echo "ColumnsQuery:$query\n";
	$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
	while($row = mysqli_fetch_object($result))
	{
		$rsField	= $row->Field;
		$rsKey		= $row->Key;

		if (preg_match("/pri/i",$rsKey))
		{
			//echo "\npass\n";
			return $rsField;
		}
	}

	return "no_key";
}//end fun_Mysql_getPrimaryKeyColumn





/**
* get 1 row of data by unique id in array
* @author Brandon Donnelson sept 4 2007 2:12am
* @param string $DB
* @param string $Table table to get row of data from
* @param string $ID unique id that designates the row
* @returns array $arData['key'] and $arData['value']
*
*/
function fun_Mysql_getRowData($DB, $Table, $ID)
{
	//debug
	//echo "fun_Mysql_getRowData: $DB, $Table, $ID\n";

	$Name_ID = fun_Mysql_getPrimaryKeyColumn($DB, $Table, $OtherDBConn);

	$arColumns = fun_Mysql_getColumns($Table,$DB);	

	$query = "SELECT * FROM `$DB`.`$Table` WHERE ($Name_ID = '$ID')";
	$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");

	while($row = mysqli_fetch_object($result))
	{
		foreach($arColumns as $Field)
		{
			$arData[$Field] = $row->$Field;
		}
	}

	return $arData;
}//end fun_Mysql_getRowData




/**
* compare records - compares Table1 against Table2 like NWMLS_tables to My_tables
* @param string $DB1
* @param string $Table1
* @param string $TableID
* @param string $DB2
* @param string $Table2
* @param string $Table2ID
* @param string $OtherDBConn
*
* @returns mixed $arData3 which is a list of arData[Field] = Value, these are differences of the records
*/
function fun_Mysql_compareRecords($DB1, $Table1, $Table1ID, $ID_Name1, $DB2, $Table2, $Table2ID, $ID_Name2, $OtherDBConn="")
{
	//debug
	//echo "\nfun_Mysql_compareRecords:[[DB1($DB1), Table1($Table1), Table1ID($Table1ID), IDName1($ID_Name1) - DB2($DB2), Table1($Table2), Table1ID($Table2ID), IDName2($ID_Name2)\n ";

	$arData1 = fun_SQLQuery_InArray($DB1, $Table1, $Table1ID, $OtherDBConn, $ID_Name1); //NwmlsData
	$arData2 = fun_SQLQuery_InArray($DB2, $Table2, $Table2ID, $OtherDBConn, $ID_Name2); //MyData

	//debug
	//print_r($arData1);
	//print_r($arData2);
	//die("\n\nWORKY????\n");

	foreach ($arData1 as $Field => $Value)
	{
		//debug
		//echo "checking arData2[$Field]({$arData2[$Field]}) = $Value\n";

		//is it different
		if ($arData2[$Field] != $Value) //value has changed in Table 2
		{
			$arData3[$Field] = $Value;
			
			//debug
			//echo "value1({$arData2[$Field]}) != value2($Value)\n";
		}


		unset($arData1[$Field]);
		unset($arData2[$Field]);
	}

	//?? (backwards) compare the keys to see if any exists in my table that don't exist in table1?

	//debug
	//die("\nfun_Mysql_compareRecords: early death\n");

	return $arData3;
}//end fun_Mysql_compareRecords



?>