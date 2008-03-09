<?
//created by brandon donnelson jan 19th and upgraded the 31st, 2007
//import csv file into mysql



//TODO







/**
* Import CSV2MySQL by File
* @param $DB string mysql database
* @param $Table string mysql table "import_sc_sales_2";
* @param $Table_ID_Name string  mysql unique id column/field "ID";
* @param $csv_file string file to import or sync "/srv/hosting_files/tmp/allsales.csv";
* @param $EndRowCount integer stop earlier than end of file
* @param $sync bol update|Drop and Insert current data if id exists in table
*
*/
function fun_Mysql_Import_CSV($DB, $Table, $Table_ID_Name, $csv_file, $EndRowCount, $Delimiter = ",", $Enclosure = "\"", $NoFieldsInRow0 = "", $Sync=0) //main function
{
	global $conn1;

	$Enclosure = trim($Enclosure); //had a problem of 'space"'

	echo "\n\n<pre>\n";
	echo "\n\n\nStarting CSV Importing *************************************************\n";
	echo "\tProcessing: Delimiter:($Delimiter)  Enclosure:($Enclosure)  File:($csv_file), Sync:($Sync)\n";
	echo "\tTable($Table) Table_ID_Name($Table_ID_Name)\n\n";


	if (!is_file($csv_file))
	{
		echo "\n\nERROR: No CSV File\n\n";
		return FALSE;	
	}


	if (!$Table) //make a new table name if none is named
	{
		$path_parts = pathinfo($csv_file);
		$Table = $path_parts['basename'];
		$Table = fun_Mysql_Check_TableorField_Name($Table); //fix it if need be
		$Table = "Import_$Table";
	}



	if (!$Table_ID_Name) //make a new id name if none is named
	{
		$Table_ID_Name = "ImportID";
	}
		
	
	//Make Table - don't drop it if we want to sync
	if ($Sync)
	{
		$Drop = 0;
	}
	fun_Mysql_Create_Table($Table, $Table_ID_Name, $DB, $Drop);
	


	//get fields from first line of csv
	$row = 0;
	$NewCount = 0;
	$handle = fopen($csv_file, "r"); //open csv file	
	while (($arData = fgetcsv($handle, 0, $Delimiter, $Enclosure)) !== FALSE) 
	{
		//debug
		echo "\ndump[arData:";
		var_dump($arData);

		//echo "\ntest2:";
		//$test[] = "Abcd6";
		//echo var_dump($test);


		if (count($arData) > $NewCount) //check arrary for proper length
		{
			$NewCount = count($arData);
		}

		if (count($arData) < $NewCount) //correct field count for those who end early for some reason or another
		{
			echo "Row Error Row:($row) RowFieldCount:($NewCount)\n";
		
			$theDiff = $NewCount - count($arData);

			//get by with this fix maybe for now (when something is wrong with row count of fields/data)
			for($i=0; $i < $theDiff; $i++) //for errors in csv export
			{
				$arData[] = "";
			}
		}


		//debug
		echo "Row:($row) Extracted fields Start DataFieldsCount:($NewCount) NoFieldsInRow0:($NoFieldsInRow0)\n";
		print_r($arData);
		echo "Row:($row) END\n";

	
		//echo row for seeing debug and progress
		echo "Row:($row) Processing[[";
	
		if ($row == 0) //row 0 create columns
		{
			$arColumns = fun_Mysql_Create_Columns($arData, $Table, $Table_ID_Name, $DB, $NoFieldsInRow0);
		}
		else //all other rows insert or update data
		{
			if ($Sync)//if updating the data
			{
				$i = 0;
				foreach ($arColumns as $Column) //setup the column names
				{
					$arUpdate[$Column] = $arData[$i];
					$i++;
				}

				fun_Mysql_Update_Data($DB, $Table, $arUpdate, $Table_ID_Name, $rtnError);
				if ($rtnError)
				{
					echo "\nERROR:$rtnError\n";
					unset($rtnError);
				}
			}
			else
			{
				fun_Mysql_Insert_Import_Data($arData, $Table, $DB);
			}

			//debug
			//die("death early\n");
		}
	
		echo "]]Row:($row) END Processing\n";
		
		$row++;
	
		//debug
		if ($EndRowCount != "" and $row == $EndRowCount)
		{
			exit("\n\n Done Early \n\n");
		}
	}
	fclose($handle);

	
	
	
	echo "\nThe End -> fun_mysql_import_csv.php *************************************************\n";
	echo "</pre>\n";



	$Pass = 1; //if we make it this far its good
	return $Pass;



}//end main function







?>