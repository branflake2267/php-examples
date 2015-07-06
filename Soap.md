# PHP Soap Example #



# Soap Discovery #
> Discover what soap (data) services are available on the soap server.
```
/**
* figure out what soap functions and types are available
* @param object $client Soap Client
* @returns string $Response soap result
*/
public function fun_NWMLS_getSoapInfo()
{
	$WSDL = $this->WSDL; //class field, 'URL'

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
```


# Get Soap Response #
> How I get the NWMLS data

```
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

```