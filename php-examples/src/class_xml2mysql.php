<?
//created by brandon donnelson april 4, 2007
//xml 2 mysql



/**
* Parse XML and import it into Mysql database
* @param string $CVSNode insert data inbetween this node
* @param string $DB mysql database
* @param string $New_Table insert data into table
* @param string $New_Table_ID make this as the unique id
* @param string $path xml file to process
* @author Brandon Donnelson
* uses functions, (dependencies) fun_Mysql_Create_Table, fun_Mysql_Optimize_Columns, fun_MySQL_Insert_Data

example
$obj = new class_xml2mysql();
$obj->New_Table = "nwmls_test";
$obj->New_Table_ID = "ImportID";
$obj->DB = "import";
$obj->CVSNode = "LN";
$obj->path = "/srv/hosting_files/tmp/nwmls/rent.xml";
$obj->parse();

*/
class class_xml2mysql 
{
	var $Debug 	= 1;
	var $xml_obj 	= null;
	var $output 	= array();
	var $attrs;
	var $Count 	 = 0;
	var $CountNodes = 0;

	var $Test_HowMany_Rows = 1000; //need to change this 

	public $CVSNode	= ""; //nodes that we want the cvs data from

	//mysql vars
	public $DB 		= "import";
	public $New_Table 	= "test_import";
	public $New_Table_ID 	= "ImportID";
	public $path 		= "";
	public $AddCustomField	= ""; //this will mark a custom field for referencing after the update is done
	#I think I should add a import cycle number which would be easier, or make timestamp consistent throughout the entire update

	function class_xml2mysql()
	{
		$this->xml_obj = xml_parser_create();
		xml_set_object($this->xml_obj,$this);
		xml_set_character_data_handler($this->xml_obj, 'dataHandler');
		xml_set_element_handler($this->xml_obj, "startHandler", "endHandler");	
	}
	

	function parse()
	{
		global $Count, $Name, $CountNodes;

		echo "CVSNode:{$this->CVSNode}\n";

		//create table
		fun_Mysql_Create_Table($this->New_Table, $this->New_Table_ID, $this->DB); //has drop now

		if (!($fp = fopen($this->path, "r"))) 
		{
			die("Cannot open XML data file: {$this->path}");
			return false;
		}
		echo "Reading ({$this->path})\n";
	
		while ($data = fread($fp, 4096)) {
			if (!xml_parse($this->xml_obj, $data, feof($fp))) 
			{
				die(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->xml_obj)),
				xml_get_current_line_number($this->xml_obj)));
				xml_parser_free($this->xml_obj);
			}
		}
	

		//optimize the data
		if ($CountNodes > 1)
		{
			$bolResult = fun_Mysql_Optimize_Columns($this->DB, $this->New_Table, $this->Test_HowMany_Rows);
		}
		else
		{
			echo "SKIPPED optimizing records CountNodes:($CountNodes) > 1\n";
		}


		//debug
		echo "\nEND class_xml2mysql: New_Table:({$this->New_Table}), Path:({$this->path}), Nodes=$CountNodes\n\n";


		return $CountNodes;
	}
	

	function startHandler($parser, $name, $attribs)
	{
		global $Count, $Name, $CountNodes;	
		$Count ++;

		$Node = $this->CVSNode;
		if (preg_match("/\b$Node\b/i", $name))
		{
			$Count = 1; //reset the node count (for cvs)
			echo "\n($CountNodes)Class_xml2mysql:COLUMNS FOR:(CurrentNode:($Node),MyNode:($name)";
		}
	
		$Name = $name;
		//echo "$Count.$name,";
	}
	

	function dataHandler($parser, $data)
	{
		global $Count, $arData, $Name, $CountNodes;

		$data = ereg_replace("\r", "", $data);
		$data = ereg_replace("\n", "", $data);

		if ($data != "")
		{
			//debug
			//echo " $Count.Data:$data: ";

			$arData[$Count][$Name] = $data;

			//debug
			//echo "\narData[$Count][$Name] = $data;\n";
		}
	}
	

	function endHandler($parser, $name)
	{
		global $Count, $arData, $Name,$CountNodes;

		//debug
		//echo "$Count.$name\n";

		$Node = $this->CVSNode;
 		if (preg_match("/\b$Node\b/i", $name))
		{
			echo "\n($CountNodes)Class_xml2mysql:(CurrentNode:($Node),MyNode:($name)-> Inserting:[[";
			
			//insert mysql data
			$LastID = fun_MySQL_Insert_Data($this->DB, $this->New_Table, $arData);
			$arData = ""; //reset this var for new node


			//add custom field on insert of data, so to mark it for other uses
			if ($this->AddCustomField)
			{
				$AddCustomField = $this->AddCustomField;
				$ID_Name = fun_Mysql_getPrimaryKeyColumn($this->DB, $this->New_Table, $OtherDBConn="");
			
				$arData[$ID_Name] 	 = "$LastID";
				$arData[$AddCustomField] = "1";
				fun_Mysql_Update_Data($this->DB, $this->New_Table, $arData, $ID_Name="", $rtnError="");
				$arData = "";
			}
 

			echo "]]\n";
			$CountNodes++;
		}
	}
} //end class_xmlParser2CSV



?>