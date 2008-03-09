<?
//created by brandon donnelson tue oct 16, 2007
//seperate fun_viewbycategory, and the query building its doing
//Build an sql query, using WhereQuery, Limit




/**
 * Build a sql query - quick limits, quick order by
 *
 * @param int $ThisService Service/Module
 * @param string $Table sql table
 * @param string $CustomWhere sql where
 * @param string $OrderBy sql orderby
 * @param mixed $arVar misc vars
 * @return string query put together
 */
function fun_SQLQuery_Builder($ThisService, $Table, $ID_Name, $ID, $CustomWhere, $OrderBy, $arVar)
{
	//init vars
	global $WhereQuery;
	global $LayID; //Comes From view.php,edit.php,... is ServiceID - makes for unique var for each service that is laid out on page
	
	
	//Fix up order by so something can't be snuck in there
	//Fix up Limit ints to, so something can't be snuck in there.
	

	/************************************************************************/
	/************************************************************************/
	//WhereQuery Setup - By Account - or do I need to skip
	if (preg_match("/listing/i", $Table))// This is important to keep!!!!!! So I either skip exactly what I want, or by default always search by account
	{
		//skip on purpose - b/c its not narrowed down by hostid
	}
	elseif (preg_match("/gv_todo/i", $Table))
	{
		
	}
	else
	{
		//Always narrow down by HostID - or error
		$arQueryAnd[] = $WhereQuery;
	}
	
	
	//ID - Narrow down by ID
	if ($ID)
	{
		$arQueryAnd[] = "($ID_Name='{$ID}')";
	}

	//CustomWhere - drilldown by
	if ($CustomWhere)
	{
		$arQueryAnd[] = $CustomWhere;
	}
	
	
	
	///////////////////////////////////////
	//Put them together
	//And Queries
	if ($arQueryAnd)
	{
		$arQueryCombine[] = fun_MySQL_Query_CombineWhere($Type="AND", $arQueryAnd);
	}
		
	//Or Queries
	if ($arQueryOr)
	{
		$arQueryCombine[] = fun_MySQL_Query_CombineWhere($Type="OR", $arQueryOr);
	}
	
	//Combine Where Queries
	if ($arQueryCombine)
	{
		$strQueryWhereCombine = fun_MySQL_Query_CombineWhere($Type="AND", $arQueryCombine);
	}
	/************************************************************************/
	/************************************************************************/
	
	
	
	/************************************************************************/
	/************************************************************************/
	//LIMIT Query - Each service/query can be limited differently
	$strS1 = "S$LayID"; 
	$strL1 = "L$LayID";

	if ($_SESSION[$strS1])
	{
		$S1 = $_SESSION[$strS1];
	}
	if ($_SESSION[$strL1])
	{
		$L1 = $_SESSION[$strL1];
	}
	if ($_REQUEST[$strS1] == 0 or $_REQUEST[$strS1] != "")
	{
		$S1 = $_REQUEST[$strS1];
		$_SESSION[$strS1]= $S1;
	}
	if ($_REQUEST[$strL1])
	{
		$L1 = $_REQUEST[$strL1];
		$_SESSION[$strL1]= $L1;
	}
	
	//LIMIT: set up the query part  -> LIMIT RowStart, HowMany
	if ($S1 or $L1)
	{
		if (!$S1)
		{
			$S1 = 0;
		}
		if (!$L1)
		{
			$L1 = 25;
		}

		//make sure they are ints
		settype($L1, "integer");
		settype($S1, "integer");
		
		$HowMany  = $L1 - $S1;
		$strLimit = " LIMIT $S1,$HowMany; ";
	}
	/************************************************************************/
	/************************************************************************/
	

	
	/************************************************************************/
	/************************************************************************/
	//Build Entire Query
	$strQuery = "SELECT * FROM $Table";
	
	//Where
	if ($strQueryWhereCombine)
	{
		$strQuery .= " WHERE $strQueryWhereCombine ";
	}

	//Order By
	if ($OrderBy)
	{
		$strQuery .= " $OrderBy ";
	}
	
	//Limit
	if ($strLimit)
	{
		$strQuery .= " $strLimit ";
	}
	/************************************************************************/
	/************************************************************************/
	
	
	unset($arVar);

	//debug
	//echo $rtnQuery;
	
	return $strQuery;
	
}//end fun

?>