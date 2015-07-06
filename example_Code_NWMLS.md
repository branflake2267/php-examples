# What is it? #

> I created this code to download the North West Multiple Listing Service broker listing data. This script does a soap request to the NWMLS server then parses the data and sticks it into mysql.


> # Info #
> Not all the dependencies are included. This can give you an idea how to do it.

> # Code #
> [nwmls class](http://code.google.com/p/php-examples/source/browse/trunk/php-examples/src/class_nwmls.php) - more code in the repository.

> How I start the nwmls script.
```
<?php
//created by brandon donnelson oct 2007
//used by cron to import nwmls data on a regular basis


include("/srv/hosting_scripts/global/global.php");


ini_set("memory_limit","100000M");
set_time_limit(28800); //8hrs




echo "<pre>\n";

//log start 
$ThisService 	= 1252;
$Success 	= 0;
$Name 		= "NWMLS Data Download";
$Description 	= "";
$LogID = fun_Log($LogID, $ThisService, $Success, $Name, $Description);


$obj = new class_getNWMLSData();
$obj->UserName = "user";
$obj->Password = "password";

/***********************************************************/
//LISTIN DATA
$obj->fun_NWMLS_Auto_Process_GoBackDays($Type="listing", $GoBackDays=1, $StartBackDays=0); 
/***********************************************************/

/***********************************************************/
//IMAGES DATA
$obj->fun_NWMLS_Auto_Process_GoBackDays($Type="image", $GoBackDays=1, $StartBackDays=0);
/***********************************************************/

/***********************************************************/
//FTP IMAGES
$obj = new nwmls_Image;
$obj->fun_Process(0);
/***********************************************************/

//clean listings expireds and cancelds out
$obj->fun_NWMLS_Auto_Process_Clean($Type="listing");

//log end 
$ThisService 	= 1252;
$Success 	= 1;
$Name 		= "NWMLS Data Download";
$Description 	= "";
$LogID = fun_Log($LogID, $ThisService, $Success, $Name, $Description);


echo "</pre>\n";

?>
END

```