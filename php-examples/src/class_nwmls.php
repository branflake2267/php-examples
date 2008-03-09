<?php
//created by brandon donnelson thu sept 20, 2007
//nwmls classes

/**
 * generate web content for nwmls stuff
 *
 */
class NWMLS_web
{

	//main method
	function NWMLS_web()
	{
	}


	/**
	* show listing image(s)
	* @param $LN string for what listing
	* @param $Num integer how many images to show
	*/
	function Display_Listing_Image($LN, $X, $Y, $Display="")
	{
		$strHTML = "";
		$id 	 = "NWMLS_Image";

		if ($Display == "First") {
			
			$strLimit = " LIMIT 0,1"; //there are only 15 images max with nwmls 
			
		} else if ($Display ==  "TheRest") {
			
			$strLimit = " LIMIT 0,16";
			
		} else {
			
			$strLimit = " LIMIT 0,1";
			
		}


		$query = "SELECT * FROM `nwmls`.`image_index` WHERE (LN = '$LN') ORDER BY Name $strLimit";
		$result = fun_SQLQuery($query, $WantRC=1, $WantLID=0, $rtnRC, $rtnLID="", $OtherDBConn="", $arVar="");
		while($row = mysqli_fetch_object($result)) 	{
			$arNames[] = $row->Name;
		}

		
		if ($arNames) {	
			
			$Count = 0;
			
			foreach ($arNames as $Name)	{	

				$id = " id=\"{$id}_{$Count}\" ";
				
				if ($Display == "First") {
					
					if ($X > 300) { //on thumbs don't need to change image
						$div1 = "<div id=\"NWMLS_Image_Main\" class=\"NWMLS_Image_Main\">";
						$div2 = "</div>";
					}					
				} else if ($Display ==  "TheRest") 	{
					
					$div1 = "<div class=\"NWMLS_Image_Others\" onclick=\"javUpdateMainImage('$Name')\">";
					$div2 = "</div>";
				}

				//get size of thumbnail - returns by reference
				$this->getImageThumbSize($Name, $X, $Y);
				$image = "/intranet/images/view_file.php?NWMLSFile=$Name&X=$X&Y=$Y";
				//Put it together
				$strHTML .= "$div1<img id=\"NWMLS_Image$Count\" src=\"$image\" width=\"$X\" height=\"$Y\" $id>$div2\n";
				$Count++;
			}
		} else { //NO PHOTO
			$image = "/intranet/images/view_file.php?NWMLSFile=No_Photo&X=$X&Y=$Y";
			$strHTML = "<img src=\"$image\" width=\"$X\" id=\"$id\" $id>\n";
		}

		return $strHTML;
	}//end Display_Listing_Image

	/**
	 * Clean weird stuff out of var
	 *
	 * @param string $Var
	 */
	function CleanVar($Var)
	{
		if (is_array($Var))
		{
			foreach ($Var as $Key => $Value)
			{
				$Var[$Key] = $this->CleanVar($Value);
			}
		}
		else 
		{
			$Var = ereg_replace("[\"\r\n\t]", "", $Var);
			$Var = ereg_replace("!", "", $Var);
			$Var = ereg_replace("@", "", $Var);
			$Var = ereg_replace("#", "", $Var);
			$Var = ereg_replace("\$", "", $Var);
			$Var = ereg_replace("^", "", $Var);
			$Var = ereg_replace("\*", "", $Var);
			$Var = ereg_replace("\\\\", "", $Var);
			$Var = ereg_replace("\)", "", $Var);
			$Var = ereg_replace("\(", "", $Var);
			$Var = ereg_replace("\+", "", $Var);
			$Var = ereg_replace("=", "", $Var);
			$Var = ereg_replace("~", "", $Var);
			$Var = ereg_replace("`", "", $Var);
			$Var = ereg_replace("{", "", $Var);
			$Var = ereg_replace("}", "", $Var);
			$Var = ereg_replace("\[", "", $Var);
			$Var = ereg_replace("\]", "", $Var);
			$Var = ereg_replace("\|", "", $Var);
			$Var = ereg_replace(",", "", $Var);
			$Var = ereg_replace("<", "", $Var);
			$Var = ereg_replace(">", "", $Var);
			$Var = ereg_replace("\?", "", $Var);
			$Var = ereg_replace("&", "", $Var);
			$Var = ereg_replace("\/", "", $Var);
			$Var = ereg_replace("%", "per", $Var);
			//$Var = ereg_replace("[\.]", "", $Var);
			//$Var = ereg_replace("-", "", $Var);
			
			$Var = ereg_replace("\'", "", $Var);
			$Var = ereg_replace("\"", "", $Var);
			$Var = ereg_replace("\:", "", $Var);
			$Var = ereg_replace("\;", "", $Var);
		}
		
		return $Var;
	}

	
	
	function getImageSize($NWMLSFile, &$rtnWidth, &$rtnHeight)
	{
		global $conn;
		
		$query = "SELECT Path, Name FROM nwmls.image_index WHERE (Name='$NWMLSFile')";
		$result = mysqli_query($conn,$query) or die("Select Query failed : " . mysqli_error() . " :<br>\r\n $query");
		while($row = mysqli_fetch_object($result))
		{
			$File = strtolower($row->Path);
			$File = ereg_replace("/mnt/sas","",$File);
		}
		
		if (!$File)
		{
			$File = "/srv/hosting_files/nwmls/no_photo.jpg";
		}
		
		//echo "$file_save_location\\$SubDir\\$ImageName<br>";
		if (is_file($File))
		{
			//get image width and height
			$imagedata 	= GetImageSize($File);
			$rtnWidth 	= $imagedata[0];
			$rtnHeight 	= $imagedata[1];
		}
		//returns reference
	}//end functiong getimagesize
	
	
	
	
	function getImageThumbSize($NWMLSFile, &$rtnWidth, &$rtnHeight)
	{
	
		if ($rtnWidth)
		{
			$tw = $rtnWidth;
			$flag = "WantHeight";
		}
		else if ($rtnHeight)
		{
			$th = $rtnHeight;
			$flag = "WantWidth";
		}
	
		$rtnWidth = "";
		$rtnHeight = "";
		$this->getImageSize($NWMLSFile, $rtnWidth, $rtnHeight);
	
		//get ratio of original
		if ($rtnWidth > 0 and $rtnHeight > 0)
		{
			$ratio = $rtnWidth / $rtnHeight;
	
			if ($flag == "WantWidth")
			{
				$rtnWidth = round($th / $ratio);
				$rtnHeight = $th;
				
			}
			
			if ($flag == "WantHeight")
			{
				$rtnHeight = round($tw / $ratio);
				$rtnWidth = $tw;
			}
	
			//debug
			//echo "tw:$tw, th:$th, r:$ratio, rtnW:$rtnWidth, rtnH:$rtnHeight";
			
		}
		//returns references
	
		return "$rtnWidth,$rtnHeight";
	}
	
	
	
	/**
	* Build NWMLS listing Query
	*
	*
	* @return string where query
	*/
	public function fun_NWMLS_Search_Query_Listing($arVar) 	{
		
		$Count 			= $this->CleanVar($arVar['Count']); //count listings instead of show them
		$Status			= $this->CleanVar($arVar['Status']);
		$PropertyType 	= $this->CleanVar($arVar['PropertyType']); //array
		$LNs 			= $this->CleanVar($arVar['LNs']); //array
		$Street 		= $this->CleanVar($arVar['Street']);
		$City 			= $this->CleanVar($arVar['City']);
		$State			= $this->CleanVar($arVar['State']);
		$Zip			= $this->CleanVar($arVar['Zip']);
		$PriceFrom		= $this->CleanVar($arVar['PriceFrom']);
		$PriceTo		= $this->CleanVar($arVar['PriceTo']);
		$HomeSizeFrom	= $this->CleanVar($arVar['HomeSizeFrom']);
		$HomeSizeTo		= $this->CleanVar($arVar['HomeSizeTo']);
		$LandSizeFrom	= $this->CleanVar($arVar['LandSizeFrom']);
		$LandSizeTo		= $this->CleanVar($arVar['LandSizeTo']);
		$Bed			= $this->CleanVar($arVar['Bed']);
		$Bath			= $this->CleanVar($arVar['Bath']);
		$AgentID		= $this->CleanVar($arVar['AgentID']);
		$SearchID		= $this->CleanVar($arVar['SearchID']); //used for saved queries, 1=featured
		
		$query = "";
	
		
		/* debug 
		foreach ($arVar as $Key => $Value)
		{
			if ($Value)
			{
				echo "Key($Key) Val($Value)<br>\n";
			}
		}*/
		//print_r($arVar);
		
		
		//ST
		if (is_array($Status))
		{
			foreach ($Status as $strStatus)
			{ 
				if ($strStatus)
				{
					$arST[] = "(ST='$strStatus')";
				}
			}
			
			$strStatus = fun_MySQL_Query_CombineWhere($Type="OR", $arST);
			if ($strStatus)
			{
				$arQueryA[] = "($strStatus)";
			}
		}
		elseif ($Status)
		{
			$arQueryA[] = "(ST='$Status')";	
		}
		
		
		//LN
		if (is_array($LNs))
		{
			foreach ($LNs as $LN)
			{ 
				if ($LN)
				{
					$arLN[] = "(LN='$LN')";
				}
			}
			$strLN = fun_MySQL_Query_CombineWhere($Type="OR", $arLN);
			if ($strLN)
			{
				$arQueryA[] = "($strLN)";
			}
		}
		elseif ($LNs)
		{
			$arQueryA[] = "(LN='$LNs')";
			
		}
		
		
		//PTYP
		if (is_array($PropertyType))
		{
			foreach ($PropertyType as $pType)
			{
				$arPTYP[] = "(PTYP='$pType')";
			}
			$strPTYP = fun_MySQL_Query_CombineWhere($Type="OR", $arPTYP);
			if ($strPTYP)
			{
				$arQueryA[] = "($strPTYP)";
			}
		}
		elseif ($PropertyType)
		{
			$arQueryA[] = "(PTYP='$PropertyType')";
		}
		
	
	
	
	
		//ADDRESS
		if ($Street) //HSN DRP STR GSUP DRS UNT are other address fields in NWMLS
		{
			$arQueryA[] = "(STR like '%$Street%')";
		}
	
		if ($City)
		{
			$arQueryA[] = "(CIT like '$City')";
		}
	
		if ($Zip) //AND NWMLS has PL4 - the other 4
		{
			$arQueryA[] = "(ZIP like '$Zip%')";
		}
	

		if ($PriceFrom or $PriceTo) //take out any letters
		{
			if ($PriceFrom)
			{
				$arPrice[] = "(LP >= '$PriceFrom')";
			}
			
			if ($PriceTo)
			{
				$arPrice[] = "(LP <= '$PriceTo')";
			}
			if ($arPrice)
			$strPrice = implode(" AND ", $arPrice);
			$arQueryA[] = "($strPrice)";
		}
	
	
		//ASF
		if ($HomeSizeFrom or $HomeSizeTo) //take out any letters
		{
			if ($HomeSizeFrom)
			{
				$arHS[] = "(ASF >= '$HomeSizeFrom')";
			}
			
			if ($HomeSizeTo)
			{
				$arHS[] = "(ASF <= '$HomeSizeTo')";
			}
			if ($arHS)
			$strHS = implode(" AND ", $arHS);
			$arQueryA[] = "($strHS)";
		}
	
		
		//LSF
		if ($LandSizeFrom or $LandSizeTo) //take out any letters
		{
			//acres
			$LandSizeFrom = $LandSizeFrom * 43560;
			$LandSizeTo = $LandSizeTo * 43560;
	
	
			if ($LandSizeFrom)
			{
				$arLS[] = "(LSF >= '$LandSizeFrom')";
			}
			
			if ($LandSizeTo)
			{
				$arLS[] = "(LSF <= '$LandSizeTo')";
			}
			if ($arLS)
			$strLS = implode(" AND ", $arLS);
			$arQueryA[] = "($strLS)";
		}
	
		//BR
		if ($Bed)
		{
			$arQueryA[] = "(BR >= '$Bed')";
		}
	
		//BTH
		if ($Bath)
		{
			$arQueryA[] = "(BTH >= '$Bath')";
		}

		//LAG, CLA
		if ($AgentID)
		{
			$arQueryA[] = "(LAG IN ($AgentID) OR CLA IN ($AgentID))";
		}
	
		
		
		//end of logic
		///////////////////////////////
		///////////////////////////////
	
	
		
		
		
		if ($Count)
		{
			$strField = " COUNT(ListingID) AS Total "; //count the listings in query
		}
		else
		{
			$strField = " * "; //select all
		}
	
		//print_r($arQueryA);


		//Saved Searches
		if ($arVar['SearchID']) {
			$strSearch = $this->fun_NWMLS_Search_Query_SearchID($SearchID);
			
			if ($strSearch) {
				$arQueryA[] = $strSearch;
			}
		}

	
		//print_r($arQueryA);
		


		if ($arQueryA)
		{
			$strQueryA = fun_MySQL_Query_CombineWhere($Type="AND", $arQueryA);
			$query .= " $strQueryA";
			
		}
	
		//debug
		//$query .= " LIMIT 0,1000"; //Just in case
		//echo "class_nwmls.php $query<br><br>";
		
		
		return $query;
	}
	
	



	/**
	* Build NWMLS listing Query by saved search id;
	*
	*/
	public function fun_NWMLS_Search_Query_SearchID($SearchID)
	{
		global $WhereQuery;

		//debug
		//echo "SearchID($SearchID)<br>";
		
		
		if ($SearchID == "1")
		{
			//agent ids (lag)
			$query2 = "SELECT Var FROM service_var WHERE $WhereQuery AND ServiceID='1059' AND Name = '1059_Featured_Lag'";
			//echo "$query2";
			$field = "Var";
			$csvLags = fun_SQLQuery_CSV($query2, $field, $OtherDBConn="");

			//listing numbers (LN)
			$query2 = "SELECT Var FROM service_var WHERE $WhereQuery AND ServiceID='1059' AND Name = '1059_Featured_LN'";
			//echo "$query2";
			$field = "Var";
			$csvLNs = fun_SQLQuery_CSV($query2, $field, $OtherDBConn="");

			if ($csvLags)
			{
				$arQuery[] = "(LAG IN ($csvLags) OR CLA IN ($csvLags))";
			}
			
			if ($csvLNs)
			{
				$arQuery[] = "(LN IN ($csvLNs))";
			}
			
			if ($arQuery)
			{
				$query = implode(" OR ", $arQuery);
			}

			//not sure what to do with this, b/c it adds one in the wherequery too
			//if ($query)
			//{
				//$query = "(ST='A') AND ($query)";
			//}
		}
		else if ($SearchID > 1) //saved searches
		{
			$query = "";
		}
		else //no SearchID 
		{
			$query = "";
		}
		
		
		
		if ($query) //error check
		{
			return $query;
		}
		else
		{
			return false;
		}
	}//end fun_NWMLS_Search_Query_SearchID



}//end class_NWMLS_web










/**
* get nwmls data
* @param string $Login['UserName']
* @param string $Login['Password']
* @param string
* @param string
*
* @author Brandon Donnelson
*/
class class_getNWMLSData
{
	private $Debug = 0; //0|1

	//Login Keys
	public $UserName = "";
	public $Password = "";
		
	//Where - Soap Request URL
	private $WSDL = "http://evernet.nwmls.com/evernetqueryservice/evernetquery.asmx?WSDL";

	//tmp file for cacheing data
	private $tmp_filename 		= "/srv/hosting_files/tmp/nwmls/soap_client_cache.xml";
	private $tmp_filename_forMysql 	= ""; //used for sending the random number into mysql import
	private $tmp_ProgressCount = ""; //used for sending the random number into the mysql import
	private $arDelfilenames = ""; //delete filenames at end
	private $arDeltmpTables = ""; //delete these tables at end



	//tmp mysql table to import the data into - then process it from there
	private $tmp_DB 	= "nwmls";
	private $tmp_Table	= "nwmls_tmp_import";
	private $tmp_Table_ID 	= "ImportID";
	private $My_DB		= "nwmls";
	private $BeginDate_period = "-1 day"; //default how far to go back -2
	



	/**
	* Main Method
	* init reference vars for use
	*/
	function class_getNWMLSData()
	{
		global $XmlQueryRef;


		//VARS FOR REFERENCE - put the vars in this function so I could define them in this manner


		#Schema Name Type - Default:StandardXML
		$XmlQueryRef['SchemaName'][0] = "EverNetAmentityXML";
		$XmlQueryRef['SchemaName'][1] = "EvernetAreaCommunityXML";
		$XmlQueryRef['SchemaName'][2] = "EverNetImageXML";
		$XmlQueryRef['SchemaName'][3] = "EverNetMemberXML";
		$XmlQueryRef['SchemaName'][4] = "EverNetOfficeXML";
		$XmlQueryRef['SchemaName'][5] = "EverNetQueryXML";
		$XmlQueryRef['SchemaName'][6] = "EverNetSchemaDefinitionXML";
		$XmlQueryRef['SchemaName'][7] = "NWMLSPremiumXML";
		$XmlQueryRef['SchemaName'][8] = "NWMLSRETSXML";
		$XmlQueryRef['SchemaName'][9] = "NWMLSStandardXML"; //this is what the nodes come from below
		$XmlQueryRef['SchemaName'][10] = "StandardXML";
		
		#DataTypes - Default:RetrieveListingData, Soap Functions/Methods -Use this to get them; var_dump($client->__getFunctions()); //var_dump($client->__getTypes());
		$XmlQueryRef['DataType'][0] = "RetrieveAmenityData"; 
		$XmlQueryRef['DataType'][1] = "RetrieveAreaCommunityData"; 
		$XmlQueryRef['DataType'][2] = "RetrieveImageData"; //image data
		$XmlQueryRef['DataType'][3] = "RetrieveListingData"; //listing data
		$XmlQueryRef['DataType'][4] = "RetrieveMemberData"; //members data
		$XmlQueryRef['DataType'][5] = "RetrieveOfficeData"; //offices data
		$XmlQueryRef['DataType'][6] = "RetrieveSchoolData"; //school abrev and Name

		#property type - this is the types that can be selected
		$XmlQueryRef['PropertyType'][0] = "BUSO";
		$XmlQueryRef['PropertyType'][1] = "COMI";
		$XmlQueryRef['PropertyType'][2] = "COND";
		$XmlQueryRef['PropertyType'][3] = "FARM";
		$XmlQueryRef['PropertyType'][4] = "MANU";
		$XmlQueryRef['PropertyType'][5] = "MULT";
		$XmlQueryRef['PropertyType'][6] = "RENT";
		$XmlQueryRef['PropertyType'][7] = "RESI"; //this is default (node:residential)
		$XmlQueryRef['PropertyType'][8] = "VACL";
		
		#XML division Nodes in returned results
		$XmlQueryRef['Node']['RetrieveAmenityData'] 	  = "amenity";
		$XmlQueryRef['Node']['RetrieveAreaCommunityData'] = "areacommunity";
		$XmlQueryRef['Node']['RetrieveImageData'] 	  = "image";	
		$XmlQueryRef['Node']['RetrieveMemberData'] 	  = "member";
		$XmlQueryRef['Node']['RetrieveOfficeData'] 	  = "office";
		$XmlQueryRef['Node']['RetrieveSchoolData'] 	  = "school";
		$XmlQueryRef['Node']['BUSO'] = "businessopportunity";
		$XmlQueryRef['Node']['COMI'] = "commercialindustrial";
		$XmlQueryRef['Node']['COND'] = "condominium";
		$XmlQueryRef['Node']['FARM'] = "farmranch";
		$XmlQueryRef['Node']['MANU'] = "manufactured";
		$XmlQueryRef['Node']['MULT'] = "multifamily";
		$XmlQueryRef['Node']['RENT'] = "rental";
		$XmlQueryRef['Node']['RESI'] = "residential";
		$XmlQueryRef['Node']['VACL'] = "vacantland";

		#status
		$XmlQueryRef['Status'][0] = "A";
		$XmlQueryRef['Status'][1] = "D";
		$XmlQueryRef['Status'][2] = "E";
		$XmlQueryRef['Status'][3] = "P";
		$XmlQueryRef['Status'][4] = "S";
		$XmlQueryRef['Status'][5] = "X";


		#my db info
		#unique ids for DataTypes
		$XmlQueryRef['ID']['RetrieveAmenityData'] 	= "";
		$XmlQueryRef['ID']['RetrieveAreaCommunityData'] = "";
		$XmlQueryRef['ID']['RetrieveImageData'] 	= "PICTUREFILENAME"; //pictures file name is unique
		$XmlQueryRef['ID']['RetrieveListingData'] 	= "LN";
		$XmlQueryRef['ID']['RetrieveMemberData'] 	= "MEMBERMLSID";
		$XmlQueryRef['ID']['RetrieveOfficeData'] 	= "OFFICEMLSID";
		$XmlQueryRef['ID']['RetrieveSchoolData'] 	= "";

		#my mysql tables for these datatypes
		$XmlQueryRef['MyTable']['RetrieveAmenityData'] 		= "";
		$XmlQueryRef['MyTable']['RetrieveAreaCommunityData'] 	= "";
		$XmlQueryRef['MyTable']['RetrieveImageData'] 		= "image"; //pictures file name is unique
		$XmlQueryRef['MyTable']['RetrieveListingData'] 		= "listing";
		$XmlQueryRef['MyTable']['RetrieveMemberData'] 		= "member";
		$XmlQueryRef['MyTable']['RetrieveOfficeData'] 		= "office";
		$XmlQueryRef['MyTable']['RetrieveSchoolData'] 		= "";

		return $XmlQueryRef;
	}//end class_getNWMLSData
	




	//auto process the nwmls query
	/**
	* process all the functions in the right order
	* @param array $arVar
	* @todo add logging
	* @returns void but could return $Response, but the dataset gets big at times
	*/
	public function fun_NWMLS_Auto_Process($arVar)
	{
		global $XmlQueryRef;


		echo "fun_NWMLS_Auto_Process:[[";

		
		$DataType = $arVar['DataType'];
		$Node 	= $this->fun_NWMLS_getNode($arVar); //get the xml division node


		//log event
		$ThisService 	= 1059;
		$Success 	= 0;
		$Name 		= "NWMLS Download $DataType";
		$Description 	= "";
		$LogID = fun_Log($LogID, $ThisService, $Success, $Name, $Description);


		if (!$DataType)
		{
			$str = "error in fun_NWMLS_Auto_Process: No DataType:($DataType)\n";
			die($str);
		}
		
		//debug
		//echo "arVars[[";
		//print_r($arVar);
		//echo "]]arVars end\n";

		$XMLQuery = $this->fun_NWMLS_BuildXMLQuery($arVar); //make up the xml query for soap request
		$Response = $this->fun_NWMLS_getSoapResponse($XMLQuery, $DataType); //submit the xml query and get data

		//debug
		//$Response = 1;		

		if ($Response)
		{
			$bolResult = $this->fun_NWMLS_SaveData($Response); //save the data to tmp file


			if ($arVar['Filter'])//if filter is set on, then add custom field for tracking the fields we update
			{
				$AddCustomField = "Clean";
			}
			$HowManyRowsImported = $this->fun_NWMLS_ImportToMysql($Node, $AddCustomField); //import into mysql - tmp table
			
			echo "Imported rs#($HowManyRowsImported) ";
			$strLog = "Imported rs#($HowManyRowsImported)";

			if ($HowManyRowsImported)
			{
				echo "Process:[[";				
				$bolResult = $this->fun_NWMLS_Process_NewRecords($DataType);
				echo "]]:Process";
			}
			else
			{
				echo "Skipped Processing new Records None\n";
				//die("\n\ndie early check\n\n");
			}
		}
		else
		{
			//debug
			echo "\nNO RESPONSE\n";
			die("\n\nHELP\n\n");
		}

		//return $Response; //not good b/c this can bring back a huge amount of data
		//return a result as true if xml looks right

		echo "]]:fun_NWMLS_Auto_Process\n";


		//log event
		$ThisService 	= 1059;
		$Success 	= 1;
		$Name 		= "NWMLS Download $DataType";
		$Description 	= $strLog;
		$LogID = fun_Log($LogID, $ThisService, $Success, $Name, $Description);

		
		//delete mysql tmp tables
		$this->deltmpTables();
		
	} //end fun_NWMLS_Auto_Process



	/**
	* test your xml query with out all the processing afterword
	* @param array $arVar - xml query parameters
	* @returns string $Response - this can get big and usually is
	*/
	public function fun_NWMLS_Auto_Process_Test($arVar)
	{
		global $XmlQueryRef;

		$XMLQuery = $this->fun_NWMLS_BuildXMLQuery($arVar);
		$Response = $this->fun_NWMLS_getSoapResponse($XMLQuery,$arVar['DataType']);

		if ($Response)  //preg match for xml??
		{
			$bolResult = $this->fun_NWMLS_SaveData($Response); //save the data to tmp file
		}

		echo $Response; //can bring back huge amount of data
	}//end fun_NWMLS_Auto_Process_Test



	/**
	* auto process for cron
	* @param string $Type - [listing|image|member|office] what type of job for cron
	* @param bol $Period - [0|1] get all or by period of time, that is preset (doesn't apply to listings)
	*/
	public function fun_NWMLS_Auto_Process_Cron($Type, $Period=1)
	{
		global $XmlQueryRef;

		if ($Period)
		{
			$arVar['BeginDate']= $this->BeginDate_period;
			$arVar['EndDate']  = "now";
		}
		else //get all
		{
			$arVar['BeginDate']= ""; 
			$arVar['EndDate']  = "";
		}

		if ($Type == 'listing' or $Type == 'image')
		{
			//get each property types data
			foreach ($XmlQueryRef['PropertyType'] as $PropertyType)
			{
				if ($Type == "listing") //get new listings
				{
					$arVar['ListingNumber'] = "";
					$arVar['PropertyType'] 	= $PropertyType;
					$arVar['AgentId'] 	= "";
					$arVar['DataType'] 	= "RetrieveListingData";
				}
				elseif ($Type == "image")
				{
					$arVar['ListingNumber'] = "";
					$arVar['PropertyType'] 	= $PropertyType;
					$arVar['AgentId'] 	= "";
					$arVar['DataType'] 	= "RetrieveImageData";
				}
				$this->fun_NWMLS_Auto_Process($arVar);
			}//end foreach ($XmlQueryRef['PropertyType'] as $PropertyType)
		}
		elseif ($Type == "member") //don't need property type for this query
		{
			$arVar['ListingNumber'] = "";
			$arVar['PropertyType'] 	= "";
			$arVar['AgentId'] 	= "";
			$arVar['DataType'] 	= "RetrieveMemberData";
			$this->fun_NWMLS_Auto_Process($arVar);
		}
		elseif ($Type == "office") //get members
		{
			$arVar['ListingNumber'] = "";
			$arVar['PropertyType'] 	= $PropertyType;
			$arVar['AgentId'] 	= "";
			$arVar['DataType'] 	= "RetrieveOfficeData";
			$this->fun_NWMLS_Auto_Process($arVar);
		}

		//report success??

	}//end fun_NWMLS_Auto_Process_Cron




	/**
	* auto process cleaning database of cancelled and deleted records
	* @param string $Type - [listing|image|member|office] what type of job for cron
	*/
	public function fun_NWMLS_Auto_Process_Clean($Type)
	{
		global $XmlQueryRef;

		if ($Type == 'listing' or $Type == 'image')
		{ 

			// just in case the previous session doesn't finish
			$this->fun_NWMLS_clean();

			//$PropertyType = "RESI";
			//get each property types data
			foreach ($XmlQueryRef['PropertyType'] as $PropertyType)
			{
				if ($Type == "listing") //get new listings
				{
					$arVar['PropertyType'] 	= $PropertyType;
					$arVar['DataType'] 	= "RetrieveListingData";
					$arVar['Filter']	= "LN,UD";
				}
				elseif ($Type == "image")
				{
					$arVar['PropertyType'] 	= $PropertyType;
					$arVar['DataType'] 	= "RetrieveImageData";
					$arVar['Filter']	= "LN,UD";
				}

				$yearBegin = date("Y");
				$yearEnd = 1999;
				for ($i = $yearBegin; $i >= 1999; $i--) //go back days in chunks in this property type
				{
					$yearBegin = $i;
					$yearEnd = $i+1;

					if ($i == 2007)
					{
						$stryearEnd = "now";
					}
					else
					{
						$stryearEnd = "01/01/$yearEnd";
					}
					$stryearBegin = "01/01/$yearBegin";

					echo "i:($i).PropertyType:($PropertyType) yearBegin:($stryearBegin) yearEnd:($stryearEnd)\n";
					$arVar['BeginDate']	= $stryearBegin;
					$arVar['EndDate'] 	= $stryearEnd;
					print_r($arVar);
					$this->fun_NWMLS_Auto_Process($arVar);
				}

			}//end foreach ($XmlQueryRef['PropertyType'] as $PropertyType)

			//mark listings cancelled if not done
			$this->fun_NWMLS_clean();
		}
	}


	/**
	* clean db - (after imported data)
	* TODO do this for image too
	*/
	private function fun_NWMLS_clean() {

		//update table clean 0 to X(cancelled)
		$query = "UPDATE nwmls.listing SET ST='X' WHERE (Clean='0');";
		$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");


		// UPDATE nwmls.listing SET Clean='0
		$query = "UPDATE nwmls.listing SET Clean='0';";
		$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
	}


	public function fun_NWMLS_Auto_Process_GoBackDays($Type, $GoBackDays, $StartBackDays=0)
	{
		global $XmlQueryRef;

		if (!$GoBackDays)
		{
			$GoBackDays = 1; 
		}

		if ($Type == "listing")
		{
			$DataType = "RetrieveListingData";
		}
		else if ($Type == "image")
		{
			$DataType = "RetrieveImageData";
		}
		else
		{
			$DataType = "RetrieveListingData";
		}


		echo "Going to chunk up days, and go back GoBackDays:($GoBackDays) days From (-$StartBackDays) for Type:($Type)\n";

		if ($Type == "listing" or $Type == "image") //get lots of listings back a while
		{
			foreach ($XmlQueryRef['PropertyType'] as $PropertyType)
			{
				$dayEnd = -$StartBackDays;
				$GoTo = $dayEnd - $GoBackDays;
				echo "DayEnd($dayEnd), GoTo($GoTo)\n";
				for ($i = $dayEnd; $i > $GoTo; $i--) //go back days in chunks in this property type
				{
					$dayBegin = $i - 1;
					$dayEnd = $i;

					if ($dayEnd == 0)
					{
						$strdayEnd = "now";
					}
					else
					{
						$strdayEnd = "$dayEnd day";
					}
					$strdayBegin = "$dayBegin day";


					echo "i:($i).PropertyType:($PropertyType) dayBegin:($strdayBegin) dayEnd:($strdayEnd)\n";
					
					$arVar['ListingNumber'] = "";
					$arVar['PropertyType'] 	= $PropertyType;
					$arVar['AgentId'] 	= "";
					$arVar['BeginDate']	= $strdayBegin;
					$arVar['EndDate'] 	= $strdayEnd;
					$arVar['DataType'] 	= $DataType;
					$this->fun_NWMLS_Auto_Process($arVar);
				}
			}
		}//end elseif type

	}//end function fun_NWMLS_Auto_Process_GoBackDays



	/** 
	* this will get the image information that I don't have by LN
	*/
	public function fun_NWMLS_Auto_Process_GetImagesByName()
	{
		echo "Starting - fun_NWMLS_Auto_Process_GetImagesByName:[[\n";

		$query = "SELECT DISTINCT LN FROM nwmls.image_index WHERE Name NOT IN (SELECT PICTUREFILENAME FROM nwmls.image)";
		$result = fun_SQLQuery($query, $WantRC=1, $WantLID=0, $rtnRC, $rtnLID="", $OtherDBConn="", $arVar="");

		echo "Downloading RowCount($rtnRC)\n\n";
		$Count = $rtnRC;
		while($row = mysqli_fetch_object($result))
		{
			$LN = $row->LN;
			echo "$Count.LN:($LN)[[";

			$arVar['ListingNumber'] = $LN;
			$arVar['PropertyType'] 	= "";
			$arVar['AgentId'] 	= "";
			$arVar['BeginDate']	= "";
			$arVar['EndDate'] 	= "";
			$arVar['DataType'] 	= "RetrieveImageData";
			$this->fun_NWMLS_Auto_Process($arVar);

			//die("\n\nearly death\n\n");
			echo "]]$Count.LN:($LN) END\n ";
			$Count--;
		}
	}//end fun_NWMLS_Auto_Process_GetImagesByName




	/**
	* Build XMLQuery - for requesting data via soap client
	* @author brandon donnelson aug 27 2007
	* 
	* @param string $arVar['UserName']
	* @param string $arVar['Password']
	* @param string $arVar['SchemaName'] - default:StandardXML
	* @param string $arVar['ListingNumber'] - ListingNumber (if None then NEED dates??)
	* @param string $arVar['PropertyType'] - default:RESI
	* @param string $arVar['Status']
	* @param string $arVar['County']
	* @param string $arVar['Area']
	* @param string $arVar['City']
	* @param string $arVar['BeginDate']
	* @param string $arVar['EndDate']
	* @param string $arVar['OfficeId']
	* @param string $arVar['AgentId']
	* @param string $arVar['Bedrooms']
	* @param string $arVar['Bathrooms']
	* @param string $arVar['']
	* @param string $arVar['']
	* @param string $arVar['']
	* @param string $arVar['']
	*
	* @returns string $XMLQuery xml query used for soap client
	*/
	public function fun_NWMLS_BuildXMLQuery($arVar)
	{
		global $XmlQueryRef;
	
		$UserName = $this->UserName;
		$Password = $this->Password;
	
		//ADD more schema names???
		if ($arVar['DataType'] == "RetrieveListingData")
		{
			$SchemaName = "StandardXML";
		}
		elseif ($arVar['DataType'] == "RetrieveImageData")
		{
			//$SchemaName = "StandardXML"; //doesn't work
			//$SchemaName = "EverNetImageXML"; //doesn't work
			$SchemaName = "NWMLSStandardXML";
		}
		elseif ($arVar['DataType'] == "RetrieveMemberData")
		{
			$SchemaName = "EverNetMemberXML";
		}
		elseif ($arVar['DataType'] == "RetrieveOfficeData")
		{
			$SchemaName = "EverNetOfficeXML";
		}
		else
		{
			$SchemaName = "StandardXML"; //default it to this for now, may change
		}
	

		if ($arVar['ListingNumber']) //Listing Number
		{
			$ListingNumber = "<ListingNumber>{$arVar['ListingNumber']}</ListingNumber>\n";

			//get property type for this listing number
			$arVar['PropertyType'] = $this->fun_PropertyType($arVar['ListingNumber']);
		}
	
		if ($arVar['PropertyType'])
		{
			$PropertyType = "<PropertyType>{$arVar['PropertyType']}</PropertyType>\n";
		}
		else
		{
			$PropertyType = "<PropertyType>RESI</PropertyType>\n";
		}
	
		if ($arVar['Status'])
		{
			$Status = "<Status>{$arVar['Status']}</Status>\n";
		}
		
		if ($arVar['County'])
		{
			$County = "<County>{$arVar['County']}</County>\n";
		}
	
		if ($arVar['Area'])
		{
			$Area = "<Area>{$arVar['Area']}</Area>\n";
		}
	
		if ($arVar['City'])
		{
			$City = "<City>{$arVar['Area']}</City>\n";
		}
	
		if ($arVar['BeginDate']) //date modified from
		{
			$BeginDate = $this->fun_NWMLS_TransformDate($arVar['BeginDate']);
			$BeginDate = "<BeginDate>$BeginDate</BeginDate>\n";
		}
	
		if ($arVar['EndDate']) //date modified to
		{
			$EndDate = $this->fun_NWMLS_TransformDate($arVar['EndDate']);
			$EndDate = "<EndDate>$EndDate</EndDate>\n";
		}
	
		if ($arVar['OfficeId'])
		{
			$OfficeId = "<OfficeId>{$arVar['EndDate']}</OfficeId>\n";
		}
	
		if ($arVar['AgentId'])
		{
			$AgentId = "<AgentId>{$arVar['AgentId']}</AgentId>\n";
		}
	
		if ($arVar['Bedrooms'])
		{
			$Bedrooms = "<Bedrooms>{$arVar['Bedrooms']}</Bedrooms>\n";
		}
	
		if ($arVar['Bathrooms'])
		{
			$Bathrooms = "<Bathrooms>{$arVar['Bathrooms']}</Bathrooms>\n";
		}

		if ($arVar['Filter'])	
		{
			$Filter = "<Filter>{$arVar['Filter']}</Filter>";
		}
		else
		{
			$Filter = "<Filter />";
		}

	
		// <Filter>column</Filter> -> this will only give the fields that you want, Like "<filter>LN,UD</filter>" gives Listing Number and Last Update Time

	
		$XMLQuery = "";
		$XMLQuery .= "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"no\"?>
		<EverNetQuerySpecification xmlns=\"urn:www.nwmls.com/Schemas/General/EverNetQueryXML.xsd\">
		<Message>
		<Head>
		<UserId>$UserName</UserId>
		<Password>$Password</Password>
		<SchemaName>$SchemaName</SchemaName>
		</Head>
		<Body>
		<Query>
		<MLS>nwmls</MLS>\n";
		$XMLQuery .= $ListingNumber;
		$XMLQuery .= $PropertyType;
		$XMLQuery .= $Status;
		$XMLQuery .= $County;
		$XMLQuery .= $Area;
		$XMLQuery .= $City;
		$XMLQuery .= $BeginDate;
		$XMLQuery .= $EndDate;
		$XMLQuery .= $OfficeId;
		$XMLQuery .= $AgentId;
		$XMLQuery .= $Bedrooms;
		$XMLQuery .= $Bathrooms;
		$XMLQuery .= "</Query>";
		$XMLQuery .= $Filter;
		
		$XMLQuery .= "
		</Body>
		</Message>
		</EverNetQuerySpecification> 
		";
	
		//debug 
		//print_r($arVar);
		
		$intBeginDate = strtotime($arVar['BeginDate']); 
		$intEndDate = strtotime($arVar['EndDate']);

		if ($intBeginDate >= $intEndDate)
		{
			$str = "\n\nerror in fun_NWMLS_BuildXMLQuery: BeginDate:($intBeginDate) EndDate:($intEndDate)\n";
			$str .= "BeginDate:($BeginDate) EndDate:($EndDate)\n\n";
			//die($str);
		}

		return $XMLQuery;
	}//end fun_NWMLS_BuildXMLQuery
	
	


	

	//Transform Date into the right form for soap query
	//@Date - Date String like 2/27/06, 2-14-07, now, etc. - refer to php strtotime 
	private function fun_NWMLS_TransformDate($Date)
	{
		if ($Date)
		{
			$intDate = strtotime($Date); //unix timestamp
		}
		else
		{
			$intDate = strtotime("now");
		}
	
		#format like 2007-08-27T19:32:00
		$Date = date('c', $intDate); //only in php5
		
		return $Date;
	}//end fun_NWMLS_TransformDate
	
	



	/**
	* get NWMLS Data
	* @param string $WSDL - soap url
	* @param string $XMLQuery - query to send the nwmls
	* @returns 
	*/
	private function fun_NWMLS_getSoapResponse($XMLQuery, $DataType = "RetrieveListingData")
	{
		global $XmlQueryRef, $client, $intTimeStamp;

		$WSDL = $this->WSDL;
		$this->DataType = $DataType; //used in mysql import
	
		//debug
		echo "DataType:($DataType):XMLQuery\n";
		echo "XMLQuery:($XMLQuery):XMLQuery\n";
		echo "Soap:[[";
		
		if (!$XMLQuery)
		{
			$str = "\n error in fun_NWMLS_getSoapResponse: No XMLQuery($XMLQuery)\n";
			die($str);
		}

		if (!$DataType) //this is parameter no xml query
		{
			$str = "\n error in fun_NWMLS_getSoapResponse: No DataType($DataType)\n";
			die($str);
		}

		//Start the soap connection
		if (!$client)
		{
			$client = new SoapClient($WSDL, array('trace' => 1) ); //trace for getLastResponse();
			echo "Conn:YES";
		}
		else
		{
			echo "Conn:NO";
		}

		//nwmls xml query stirng		
		$params = array ('v_strXmlQuery' => $XMLQuery); //setup the parameters of the function/method that we are going to request arresponse to
	
		echo "Params:";	

		if (in_array($DataType,$XmlQueryRef['DataType']))
		{
			try 
			{
				echo "try func:TimeStamp1:($intTimeStamp)";
				$result = $client->$DataType($params); //submit function type with xml query as param
			}
			catch (SoapFault $soapFault) 
			{
				if ($soapFault)
				{
					echo "\n\nSoap Fail:TimeStamp2:($intTimeStamp)\n";
					//var_dump($soapFault);
					echo "\n\nRequest :\n", $client->__getLastRequest(), "\n\n";
					echo "Response :\n", $client->__getLastResponse(), "\n\n";
					//die("\n\nearly death in soap request\n\n");
				}
				else
				{
					echo "Soap Success:Returning Response:TimeStamp2:($intTimeStamp)\n";
				}
			}
		}
	
		//this will prolly error on its on if fault is thrown
		$Response = $client->__getLastResponse();
	
		//debug
		//echo "REQUEST:\n" . $client->__getLastRequest() . "\n"; //Shows query just sent
		//echo "\nResponse:$Response\n\n\n\n";
		//die("\n\nearly death -> soap Response\n\n");
			
		//debug
		//die("\n\nwhat the\n\n");
		if (!$Response)
		{
			echo "\nSoap:No Rresponse\n";
		}

		echo "]]:Soap";

		return $Response;
	}//fun_NWMLS_getSoapResponse

	
	




	/**
	* save response to tmp file
	* @param string $Response soap client response from nwmls
	*
	* @returns bol true|false
	*/
	private function fun_NWMLS_SaveData($Response)
	{
		global $arDelfilenames, $intProgressCount;

		//this makes each session unique - change to random???
		$intProgressCount++;
		$this->tmp_ProgressCount = $intProgressCount;

		$filename = $this->tmp_filename;
		$filename .= "_$intProgressCount"; //change the file name to make it individual

		$this->tmp_filename_forMysql = $filename;
		$arDelfilenames[] = $filename; //make array of filenames
		

		//fix the string
		$Response = html_entity_decode($Response);

		if (!$handle = fopen($filename, 'w')) 
		{
			echo "Cannot open file ($filename)\n";
			return FALSE;
		}
		
		// Write $somecontent to our opened file.
		if (fwrite($handle, $Response) === FALSE) 
		{
			echo "Cannot write to file ($filename)\n";
			return FALSE;
		}
		
		echo "Success, wrote xml to file ($filename)\n";
		
		fclose($handle);
		unset($Response); //get this out of memory especially if its big

		return TRUE;
	}//end fun_NWMLS_SaveData



	/**
	* import the tmp data into mysql for processing there
	* @param string $Node node that divides the records
	* 
	*/
	private function fun_NWMLS_ImportToMysql($Node, $AddCustomField)
	{
		global $XmlQueryRef, $arDeltmpTables, $intProgressCount;

		$filename = $this->tmp_filename_forMysql;
		$tmp_Table = $this->tmp_Table;
		$tmp_Table .= "_" . $this->tmp_ProgressCount; 
		$this->tmp_Table_forProcess = $tmp_Table;
		$arDeltmpTables[] = $tmp_Table;


		//error check for node
		if (!$Node)
		{
			$str = "\nerror in fun_NWMLS_ImportToMysql: no Node:($Node)\n";
			die($str);
		}


		$obj2 = new class_xml2mysql();
		$obj2->New_Table = $tmp_Table;
		$obj2->New_Table_ID = $this->tmp_Table_ID;
		if ($AddCustomField)
		{
			$obj2->AddCustomField = $AddCustomField;
		}
		$obj2->DB = $this->tmp_DB;
		$obj2->CVSNode = $Node; //this is picky, must be like "image" or "LN" or it prolly won't work right
		$obj2->path = $filename;

		$HowManyRowsImported = $obj2->parse();

		unset($obj2);

		//debug
		//die("\n\ndie in fun_NWMLS_ImportToMysql\n\n");

		return $HowManyRowsImported;
	}//end fun_NWMLS_ImportToMysql



	/**
	* get the xml node that divides the records
	* @param array $arVar need DataType and PropertyType
	* @returns string $Node
	*/
	private function fun_NWMLS_getNode($arVar)
	{
		global $XmlQueryRef;

		$DataType = $arVar['DataType'];
		if ($DataType == "RetrieveListingData")
		{
			$PropertyType = $arVar['PropertyType'];
			if ($PropertyType)
			{
				$Node = $XmlQueryRef['Node'][$PropertyType];
			}
			else
			{
				$Node = "RESI";
			}
		}
		else //other retrieve types
		{
			$Node = $XmlQueryRef['Node'][$DataType];
		}

		if (!$Node)
		{
			die("\nerror in fun_NWMLS_getNode: No Node($Node)\n");
		}

		return $Node;
	}//end fun_NWMLS_getNode





	private function fun_NWMLS_Process_NewRecords($DataType)
	{

		//import db vars
		$tmp_DB    = $this->tmp_DB;
		$tmp_Table = $this->tmp_Table_forProcess; //$this->tmp_Table;
		$ID_Name   = $this->fun_NWMLS_getUniqueIDName($DataType); //unique id for tmp table for retrieve data


		//debug
		echo "fun_NWMLS_Process_NewRecords:tmp_DB($tmp_DB),tmp_Table($tmp_Table),ID_Name($ID_Name),DataType($DataType)\n";

		//get columns of tmp db
		$arColumns = fun_Mysql_getColumns($tmp_Table, $tmp_DB);

		//Go through the new records I just imported into mysql tmp table
		$query = "SELECT * FROM `$tmp_DB`.`$tmp_Table`";
		//echo "fun_NWMLS_Process_NewRecords:checking records $query\n";
		$result = fun_SQLQuery($query, $WantRC=1, $WantLID=0, $rtnRC, $rtnLID="", $OtherDBConn="", $arVar="");
		$Count = $rtnRC;
		while($row = mysqli_fetch_object($result))
		{
			$rsID = $row->$ID_Name; //get the new ids that i just imported
			
			echo "($Count of $rtnRC). Checking ID:($rsID)[[ ";
			//build list of what to do with each UniqueID
			$Modify = $this->fun_NWMLS_checkExistInMyTable($DataType, $rsID);

			if ($Modify == "Update")
			{
				$arUpdate[] = $rsID;
			}
			else
			{
				$arInsert[] = $rsID;
			}
			echo " ]]:Checking ID\n";
			$Count--;
		}
		

		
		//PROCESS RECORD NOW
		//insert record
		if ($arInsert)
		{
			echo "InsertDif::[[";
			$this->fun_NWMLS_InsertData($DataType, $arInsert);
			echo "]]::InsertDif";
		}

		

		//update record
		if ($arUpdate)
		{
			echo "UpdateDif::[[";
			$this->fun_NWMLS_UpdateData($DataType, $arUpdate);
			echo "]]::UpdateDif";
		}


		
		
		//debug
		//echo "arInsert\n";
		//print_r($arInsert);
		//echo "arUpdate\n";
		//print_r($arUpdate);


		$ctarInsert 	= count($arInsert);
		$ctarUpdate	= count($arUpdate);

		//debug
		//die("\n\nfun_NWMLS_Process_NewRecords: before optimize\n\n");

		//Won't always need this here!!
		//make sure we have an optimized list
		if (($ctarInsert > 0) or ($ctarUpdate > 0))
		{
			echo "\n\n Optimize::\n\n";
			$My_Table  = $this->fun_NWMLS_getMyTable($DataType);
			$My_DB	   = $this->My_DB;
			//fun_Mysql_Optimize_Columns($My_DB, $My_Table, $Test_HowMany_Rows=1500);
		}
		else
		{
			echo "\n\nSkipping optimizing My_Table($My_Table) ctarInsert($ctarInsert) ctarUpdate($ctarUpdate)\n\n ";
		}

	}//end fun_NWMLS_Process_NewRecords



	private function fun_NWMLS_checkExistInMyTable($DataType, $rsID)
	{
		//my db vars
		$My_Table  = $this->fun_NWMLS_getMyTable($DataType);
		$My_DB	   = $this->My_DB;
		$ID_Name   = $this->fun_NWMLS_getUniqueIDName($DataType); //unique id for tmp table for retrieve data


		//does this id exist in my table??
		$query = "SELECT * FROM `$My_DB`.`$My_Table` WHERE ($ID_Name='$rsID')";
		//echo "Checking MYTable($My_Table): $query\n";
		$field = $ID_Name;
		$rsExists = fun_SQLQuery2($query, $field, $OtherDBConn="");

		if ($rsExists)
		{
			echo " Exists? YES: rsExists(ID:$rsExists) Is in My_Table:($My_Table) ";
			$Modify = "Update";
		}
		else
		{
			echo " Exists? NO: Not in My_Table:($My_Table)";
			$Modify = "Insert";
		}

		return $Modify;
	}//end fun_NWMLS_checkExistInMyTable



	private function fun_NWMLS_getUniqueIDName($DataType)
	{
		global $XmlQueryRef;

		$ID_Name = $XmlQueryRef['ID'][$DataType];
		
		return $ID_Name;
	}


	private function fun_NWMLS_getMyTable($DataType)
	{
		global $XmlQueryRef;

		$My_Table = $XmlQueryRef['MyTable'][$DataType];
		
		return $My_Table;
	}


	private function fun_NWMLS_InsertData($DataType, $arInsert)
	{
		//import db vars
		$tmp_DB    = $this->tmp_DB;
		$tmp_Table = $this->tmp_Table_forProcess;
		$ID_Name   = $this->fun_NWMLS_getUniqueIDName($DataType); //unique id for tmp table for retrieve data

		//my db vars MY TABLE
		$My_Table  = $this->fun_NWMLS_getMyTable($DataType);
		$My_DB	   = $this->My_DB;
		

		$CountTotal = count($arInsert);
		$Count = $CountTotal;
		//insert records
		foreach ($arInsert as $ID)
		{
			echo "($Count of $CountTotal).Inserting[[ID:($ID)";

			//get the data to insert from the tmp table
			$arData = fun_SQLQuery_InArray($tmp_DB, $tmp_Table, $ID, $OtherDBConn="", $ID_Name);

			//add DateCreated
			$arData['DateCreated'] = strtotime("now");
			unset($arData['ImportID']); //don't need this field

			//debug
			//print_r($arData);

			//Insert the data NOW
			fun_MySQL_Insert_Data($My_DB, $My_Table, $arData, $New_Table_ID = "");

			echo "]]Inserting\n";
			$Count--;
		}

		return TRUE;
	}




	private function fun_NWMLS_UpdateData($DataType, $arUpdate)
	{
		//import db vars
		$tmp_DB    = $this->tmp_DB;
		$tmp_Table = $this->tmp_Table_forProcess;
		$ID_Name   = $this->fun_NWMLS_getUniqueIDName($DataType); //unique id for tmp table for retrieve data

		//my db vars MY TABLE
		$My_Table  = $this->fun_NWMLS_getMyTable($DataType);
		$My_DB	   = $this->My_DB;


		$CountTotal = count($arUpdate);
		$Count = $CountTotal;

		//update records
		foreach ($arUpdate as $ID)
		{
			echo "($Count of $CountTotal).Update:[[ID:($ID)";
			
			//only return differences
			//NWMLSDB DB1 
			//MyBD DB2
			$DB1 		= $tmp_DB;
			$Table1 	= $tmp_Table;
			$Table1ID 	= $ID;
			$ID_Name1	= $ID_Name;
	
			$DB2		= $My_DB;
			$Table2		= $My_Table;
			$Table2ID	= $ID;
			$ID_Name2	= $ID_Name;
			$arData = fun_Mysql_compareRecords($DB1, $Table1, $Table1ID, $ID_Name1, $DB2, $Table2, $Table2ID, $ID_Name2, $OtherDBConn="");

			unset($arData['ImportID']);

			//debug
			//echo "\nDiffVals:[[arData:\n";
			//print_r($arData);
			//echo "]]:DiffVals\n";


			if ($arData)
			{
				//Update only these records in my db
				$arData['LastUpdated'] = strtotime("now");
				$rsID = $this->fun_NWMLS_updateRecord($My_DB, $My_Table, $ID, $ID_Name, $arData); //Update record query
				unset($arData['LastUpdated']); //don't need this column

				//insert into history_table
				if ($rsID)
				{
					echo "History:[[";
					$this->fun_NWMLS_saveHistory($My_DB, $My_Table, $arData, $ID_Name, $rsID);
					echo "]]:History";
				}
			}

			echo "]]:Update\n\n";

			$Count--;

		}//end loop through ids to update

		unset($arData);
		unset($arUpdate);
	}


	private function fun_NWMLS_saveHistory($My_DB, $My_Table, $arData, $ID_Name, $rsID)
	{
		echo "Updating History[[";
		$My_Table = "history_$My_Table";
		$arData[$ID_Name] = $rsID; //ownerid of the history record

		unset($arData['ImportID']); //don't need this column
		unset($arData['LastUpdated']); //don't need this column
		
		if ($arData)
		{
			$arData['DateCreated'] = strtotime("now");

			fun_MySQL_Insert_Data($My_DB, $My_Table, $arData, $New_Table_ID = ""); 
	
			//make sure the history tables are optimized so they don't waste space
			//fun_Mysql_Optimize_Columns($My_DB, $My_Table, $Test_HowMany_Rows=1000);
		}
		echo "]]Updating History\n";
	}


	private function fun_NWMLS_updateRecord($My_DB, $My_Table, $ID, $ID_Name, $arData)
	{
		unset($arData['ImportID']);
	
		//print_r($arData);

		$arUpdate[$ID_Name] = $ID;
 		
		foreach ($arData as $Field => $Value)
 		{
 			$arUpdate[$Field] = $Value;
 		}
// 		$strData = implode(",", $arUpdate);
// 		$query = "UPDATE `$My_DB`.`$My_Table` SET $strData WHERE ($ID_Name='$ID')";
// 		fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
// 		
		echo "\nfun_NWMLS_updateRecord:[[";
		//print_r($arUpdate);
		
		fun_Mysql_Update_Data($My_DB, $My_Table, $arUpdate, $ID_Name, $rtnError);

		echo "]]:fun_NWMLS_updateRecord\n";

		return $ID;
	}


	private function fun_PropertyType($LN)
	{
		$query = "SELECT PTYP FROM nwmls.listing WHERE (LN = '$LN')";
		$field = "PTYP";
		$rsPropertyType = fun_SQLQuery2($query, $field, $OtherDBConn="");

		return $rsPropertyType;
	}


	/**
	* figure out what soap functions and types are available
	* @param object $client Soap Client
	* @returns string $Response soap result
	*/
	public function fun_NWMLS_getSoapInfo()
	{
		$WSDL = $this->WSDL;

		$client = new SoapClient($WSDL, array('trace' => 1) ); //trace for getLastResponse();
	
		//debug (and figuring out what to do!)
		//get functions and params
		var_dump($client->__getFunctions());
		var_dump($client->__getTypes());

		$Response = $client->__getLastResponse();

		echo "\n\n\n";
		echo "Response:$Response\n";
		echo "\n\n\n";

		//debug
		//echo "REQUEST:\n" . $client->__getLastRequest() . "\n"; //Shows query just sent
		//echo "RESPONSE:\n" . $client->__getLastResponse() . "\n"; //gets the data

		return $Response;
	}//end fun_NWMLS_getSoapInfo
	
	
	/**
	 * delete tmp mysql dbs for importing
	 */
	function deltmpTables()
	{
		$db = $this->My_DB;
		$tmp_Table = $this->tmp_Table;
		
		//get tables to delete first
		$query = "SHOW TABLES FROM nwmls LIKE '{$tmp_Table}_%' ";
		$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
		while($arRowField = mysqli_fetch_array($result, MYSQLI_NUM))
		{
			$arTables[] = $arRowField[0];
		}
		
		//debug
		//print_r($arTables);
		//$strTables = implode(",", $arTables);
		//echo "Tables to delete:($strTables)\n";
		
		if ($arTables)
		{
			foreach($arTables as $Table)
			{
				if ($Table != "")
				{
					$query = "DROP TABLE IF EXISTS $db.$Table;";
					echo "dropping table tmp Table($query)\n";
					fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
				}
			}
		}
		
	}
	
}//end class_getNWMLSData








//get images from the nwmls
class nwmls_Image
{
	//define vars 
	var $ftp_user_name 	= "bdonnelson";
	var $ftp_user_pass 	= "83kg923m";
	var $nwmls_ftp_site 	= "ftp.nwmls.com";
	
	var $RemoteBaseDir 	= "/photos"; 
	var $LocalBaseDir 	= "/srv/hosting_files/nwmls/photos";
	
	//makes for the same date the entire download process
	var $DateCreated 	= "";
	var $LastUpdated 	= "";
	var $Tries 		= 0;
	var $Local_File_Tmp	= "/srv/hosting_files/tmp/nwmls/tmp.jpg";
	var $FTPTries		= 100; //how many times do we retry to connect, starts at 0
	var $DownloadTimeout	= 3; //how many seconds would trigger a timeout, starts at 0

	//main method
	function nwmls_Image()
	{
		//init vars
		$this->DateCreated = strtotime("now");
		$this->LastUpdated = strtotime("now");

	}//end nwmls_Image


	/**
	* figure out what images to download, then start the downloading
	*/
	function fun_Process($Check=0)
	{
		echo "Starting\n";

		//log event
		$ThisService 	= 1059;
		$Success 	= 0;
		$Name 		= "NWMLS FTP Images";
		$Description 	= "";
		$LogID = fun_Log($LogID, $ThisService, $Success, $Name, $Description);


		echo "\nNew Files [[";
		//get new files
		$arFileNames = $this->fun_getNewImagesQuery();
		$ctFileNames = count($arFileNames);

		echo "New Files :: Download FileNames($ctFileNames)\n";

		if ($ctFileNames > 0)
		{
			echo "fun_ftp_download_images:[[";
			$this->fun_Process_UntilComplete($arFileNames);
			echo "]]:fun_ftp_download_images\n";
		}
		else
		{
			echo "\n No New FileNames \n";
		}
		$arLog[] = "NewFiles:($ctFileNames)";

		echo "]]New Files\n\n\n ChangedFiels[[";

		//debug
		//echo "arFileNames:";
		//print_r($arFileNames);
		
		//get changed files
		$arFileNames = $this->fun_getDifferentModTimesQuery();
		$ctFileNames = count($arFileNames);

		echo "Changed Files :: Download FileNames($ctFileNames)\n";

	
		if ($ctFileNames > 0)
		{
			echo "fun_ftp_download_images:[[";
			$this->fun_Process_UntilComplete($arFileNames);
			echo "]]:fun_ftp_download_images\n";
		}
		else
		{
			echo "\n No Changed FileNames \n";
		}
		$arLog[] = "Changed Files:($ctFileNames)";
		echo "]]Changed Files\n\n";


		//log completed 
		$ThisService 	= 1059;
		$Success 	= 1;
		$Name 		= "NWMLS FTP Images";
		$Description = implode(", ", $arLog);
		$LogID = fun_Log($LogID, $ThisService, $Success, $Name, $Description);



		//delete filenames


		//delete mysql tmp tables


	}//end fun_Process



	private function fun_Process_UntilComplete($arFiles)
	{
		echo "\nDownloading\n";

		for ($i = 0; $i < $this->FTPTries; $i++)
		{
			$arFiles = $this->fun_ftp_download_images($arFiles);

			echo "\n\nFTP Tries($i) :: Trying Again \n\n";
		}//end for

		echo "\n\nDownloading END\n\n";
	}//end fun_Process_UntilComplete



	/** 
	* what images need to download? - check table `image` agianst my image_index
	*/
	public function fun_getNewImagesQuery()
	{
		$query = "SELECT PICTUREFILENAME FROM nwmls.image WHERE PICTUREFILENAME NOT IN (SELECT Name FROM nwmls.image_index)"; //gives me nwmls images that aren't in my index
		$field = "PICTUREFILENAME";
		$arFileNames = fun_SQLQuery_Array($query, $field, $OtherDBConn="");

		return $arFileNames;
	}//end fun_getNewImagesQuery



	/** 
	* what images have different mod times? - check images modtime agianst my modtime
	*/
	public function fun_getDifferentModTimesQuery()
	{
		//this one looks checks my files agiast the nwmls images and shows the nwmls files that are newer
		$query = "SELECT ID, LN, Name, FROM_UNIXTIME(modtime) as My_ModTime, 			
			if (i.modtime < (SELECT LASTMODIFIEDDATETIME FROM nwmls.image WHERE (PICTUREFILENAME = i.Name)), 'NEWER', 'OLDER') AS NWMLS_Age	
			FROM nwmls.image_index i
			HAVING NWMLS_Age = 'NEWER'
			ORDER BY ID DESC ";
		$field = "Name";
		$arFileNames = fun_SQLQuery_Array($query, $field, $OtherDBConn="");

		return $arFileNames;

		//good for figuring out the mod times 1
		$debugQuery = "SELECT PICTUREFILENAME, FROM_UNIXTIME(LASTMODIFIEDDATETIME) as nwmls_mt, 
				FROM_UNIXTIME((SELECT modtime FROM image_index WHERE image_index.Name = image.PICTUREFILENAME)) as my_mt 
				FROM image  WHERE LASTMODIFIEDDATETIME > (SELECT modtime FROM image_index WHERE Name = image.PICTUREFILENAME) ORDER BY LASTMODIFIEDDATETIME DESC limit 1000,1000";

		//good for figuring our the modtimes 1 - great for debugging the output of changed files
		$debugQuery2 = "SELECT ID, LN, Name, FROM_UNIXTIME(modtime) as My_ModTime, 
				FROM_UNIXTIME((SELECT UPLOADEDDATETIME FROM image WHERE (PICTUREFILENAME = i.Name))) AS NWMLS_UploadTime,
				FROM_UNIXTIME((SELECT LASTMODIFIEDDATETIME FROM image WHERE (PICTUREFILENAME = i.Name))) AS NWMLS_ModTime,	
				if (i.modtime < (SELECT LASTMODIFIEDDATETIME FROM image WHERE (PICTUREFILENAME = i.Name)), 'NEWER', 'OLDER') AS NWMLS_Age
				FROM image_index i 
				HAVING NWMLS_Age = 'NEWER'
				ORDER BY ID DESC ";
	}//end fun_getDifferentModTimesQuery



	private function fun_update_IndexImage($LN, $Name, $Path)
	{
		echo "indexing:";

		//check if record exists first
		$query = "SELECT ID FROM nwmls.image_index WHERE (Name='$Name')";
		$field = "ID";
		$rsID = fun_SQLQuery2($query, $field, $OtherDBConn="");
		$intModTime = filemtime($Path); //get mod time
		$DateCreated = $this->DateCreated;
		$LastUpdated = $this->LastUpdated;
	
		//echo "Path:($Path), intModTime:($intModTime)";
		
		if ($rsID)
		{
			$query = "UPDATE nwmls.image_index SET LN='$LN', Name='$Name', Path='$Path', modtime='$intModTime', LastUpdated='$LastUpdated' WHERE (ID = '$rsID')";
			echo " Updated Index";
		}
		else
		{
			$query = "INSERT INTO nwmls.image_index (LN, Name, Path, modtime, DateCreated) VALUES ('$LN', '$Name', '$Path', '$intModTime', '$DateCreated')";
			echo "AddedDB:";
		}
		
		//debug
		//echo "\n$query\n";
		
		fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
		
	}






	private function fun_ftp_download_images($arFiles)
	{
		//connection to ftp server
		$conn_id = $this->fun_ftp_Open($conn_id); //open ftp connection to nwmls

		$ctFiles = count($arFiles);
		echo "fun_download_images: count($ctFiles)[[";
		$i = $ctFiles;
		foreach ($arFiles as $key => $FileName)
		{
			echo "$i.FileName:($FileName):[[";
			$rtnFlag = $this->fun_ftp_download($conn_id, $FileName);
			if ($rtnFlag == "Success") //widdle down the filenames that we downloaded
			{
				unset($arFiles[$key]);
			}
			elseif ($rtnFlag == "FTPTimeout")
			{
				break; //break this loop, and the previous function will try agian
			}
			elseif ($rtnFlag == "File Doesn't Exist")
			{
				//keep on going anyway
			}

			echo "]]\n";

			$i--;
		}
		echo "]]:fun_download_images\n";

		$this->fun_Close_ftp($conn_id);//all done - close ftp

		return $arFiles;
	}//end fun_ftp_download_images



	private function fun_ftp_download($conn_id, $FileName)
	{

		$RemoteBaseDir 	= $this->RemoteBaseDir;
		$LocalBaseDir 	= $this->LocalBaseDir;
		$subDirL 	= $this->fun_getSubDir($FileName, $Remote=1);
		$subDirR 	= $this->fun_getSubDir($FileName, $Remote=0);
		$LN		= $this->fun_getLN($FileName);
		$Local_File_Tmp = $this->Local_File_Tmp;

		// path to remote file
		$remote_file 	= $RemoteBaseDir. $subDirR . "/$FileName";
		$local_file 	= $LocalBaseDir. $subDirL . "/$FileName";
		$Path 		= $LocalBaseDir. $subDirL . "/$FileName";
		
		//debug
		//echo "LN:($LN) lf:($local_file) rf:($remote_file)\n";
		echo "rf:($remote_file)";

		//die("\n\nDownload die\n\n");
		
		$handle = fopen($Local_File_Tmp, 'w'); // open some file to write to

		$intTime1 = strtotime("now");
		$Result = ftp_fget($conn_id, $handle, $remote_file, FTP_BINARY, 0); // try to download $remote_file and save it to $handle
		$intTime2 = strtotime("now");

		//how long did it take - verify it wasn't a connection loss
		$intTime3 =  $intTime2 - $intTime1;
		echo "\nDownloadTime: $intTime3 =  $intTime2 - $intTime1;\n";


		//**********check downloaded file size is it bigger than 0????


		if ($intTime3 > $this->DownloadTimeout)
		{
			echo "\nError:FTP TIMEOUT\n";
			$rtnFlag = "FTPTimeout";
		}
		else
		{
			if ($Result)
			{
				fclose($handle); //close Local_File_Tmp - renames local file
				
				//debug
				//echo "\nMove $Local_File_Tmp TO $local_file\n";
	
				if (rename($Local_File_Tmp, $local_file))
				{
					echo "Copied: ";
				}
				else
				{
					echo "!!!! Copy Error !!!!";
				}
	
				echo "Saved: ";
				$rtnFlag = "Success";
			} 
			else 
			{
				fclose($handle); //close Local_File_Tmp
	
				echo  "No Remote File: \n"; //this error may b/c of connection error, or it may b/c a file isn't present
				
				$this->fun_delete_ImageIndex($FileName); //get rid of the index, so not to repeat this on a non existent file
	
				$rtnFlag = "File Doesn't Exist";
			}
			
			if ($rtnFlag)
			{
				//index the image in my db
				$this->fun_update_IndexImage($LN, $FileName, $Path);
			}
		}

		//debug
		//die("\n\nDownload die\n\n");

		return $rtnFlag;
	}//end ftp_download_images



	private function fun_getListingStatus($LN)
	{
		$query = "SELECT ST FROM nwmls.listing WHERE (LN = '$LN')";
		$field = "LN";
		$rsStatus = fun_SQLQuery2($query, $field, $OtherDBConn="");

		return $rsStatus;
	}//end fun_getListingStatus

	
	private function fun_getLN($FileName)
	{
		preg_match("/^([0-9]+)/i", $FileName, $arMatch);
		$LN = $arMatch[1];

		return $LN;
	}//end fun_getLN



	/**
	* get the subdirectory for the listing .jpg
	* like 123465789_10.jpg or 12346879.jpg
	* @param string $FileName - file name 
	*/
	private function fun_getSubDir($FileName, $Remote=1)
	{
		$LN 		= $this->fun_getLN($FileName);
		$Status 	= $this->fun_getListingStatus($LN);

		preg_match("/([0-9]{3,3})_|([0-9]{3,3})\./X", $FileName, $arMatch);//last three digits designate file
		if ($arMatch[1])
		{
			$subDir = $arMatch[1];
		}
		elseif ($arMatch[2])
		{
			$subDir = $arMatch[2];
		}
		else
		{
			print_r($arMatch);//debug
			die("\n\nfun_getSubDir: No Sub Directory rtnDir($subDir)\n\n");
		}

		if ($Remote == 1)
		{
			if ($Status == "A")
			{
				$strDir = "/active/bigphoto";
			}
			elseif ($Status == "S")
			{
				$strDir = "/active/bigphoto"; //forgot to change this before I indexed
			}
			else
			{
				$strDir = "/active/bigphoto";
			}
	
			//return base directory ether in sold or active 
			$rtnDir = "$strDir/$subDir";
		}
		else
		{
			$rtnDir = "/bigphoto/$subDir";
		}

		return $rtnDir;
	}//end fun_getSubDir



	private function fun_delete_ImageIndex($FileName)
	{
		if ($FileName)
		{
			$query = "DELETE FROM nwmls.image WHERE PICTUREFILENAME = '$FileName';";
			echo "deleted from nwmls.image: ";
			fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");

			$query = "DELETE FROM nwmls.image_index WHERE Name = '$FileName';";
			echo "deleted from nwmls.index: ";
			fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
		}
	} //end fun_delete_ImageIndex



	private function fun_ftp_Open($conn_id)
	{
		$ftp_user_name 	= $this->ftp_user_name;
		$ftp_user_pass 	= $this->ftp_user_pass;
		$ftp_server 	= $this->nwmls_ftp_site;

		//close and then login agian
		if ($conn_id)
		{
			$this->fun_Close_ftp($conn_id);
		}

		//set up the ftp connection
		$conn_id = ftp_connect($ftp_server);
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); //login to ftp with username and password
		if ((!$conn_id) || (!$login_result)) //check connection
		{
			echo "FTP connection has failed!\n";
			echo "Attempted to connect to $ftp_server for user $ftp_user_name\n";
			exit;
		}
		else
		{
			echo "Connected to $ftp_server, for user $ftp_user_name\n";
		}
	
		return $conn_id;
	}//end fun_ftp_Open



	private function fun_Close_ftp($conn_id)
	{
		ftp_close($conn_id); // close the connection and the file handler
		echo "Closed ftp connection\n";
	}//end fun_Close_ftp



	private function fun_Move_Files_Status()
	{
		//these files are in both active and sold -> where do they need to go??
		$query = "SELECT DISTINCT LN, Name, COUNT(Name) AS ctName FROM image_index GROUP BY Name HAVING ctName > 1";
		$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
				while($row = mysqli_fetch_object($result))
		{
			$rsLN	= $row->LN;
		}
	}


	private function fun_Move_Files($LN)
	{
		//get status

	}


	public function fun_Merge_Status_Directory_Files()
	{
		//put all files in active
		$query = "SELECT * FROM nwmls.image_index WHERE Path like '%sold%'";
		$result = fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");
		while($row = mysqli_fetch_object($result))
		{
			$rsPath		= $row->Path;
			$FileName	= $row->Name;

			$rsPath = ereg_replace("/mnt/sas", "", $rsPath);

			//new directory to move to
			$LocalBaseDir 	= $this->LocalBaseDir;
			$subDirL 	= $this->fun_getSubDir($FileName, $Remote=1);
			$local_file 	= $LocalBaseDir. $subDirL . "/$FileName";

			//move file
			$cmd = "mv $rsPath $local_file";
			echo "$cmd\n";
			exec($cmd);

			//update path
			$query = "UPDATE nwmls.image_index SET Path = '$local_file' WHERE (Name = '$FileName') ";
			fun_SQLQuery($query, $WantRC=0, $WantLID=0, $rtnRC="", $rtnLID="", $OtherDBConn="", $arVar="");

		}

	}
}//end class
?>