<?php
/****************************************************************************
* Name:            rep_postman.php                                          *
* Project:         Corsaro - Reporting                                      *
* Version:         2.0.5                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2017 Rodolfo Calzetti                                    *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "_config.php";
include_once $tocambusa."sysconfig.php";
include_once $tocambusa."ryquiver/quiversex.php";

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
else
    $sessionid="";

if(isset($_POST["env"]))
    $env=$_POST["env"];
else
    $env="";

if(isset($_POST["format"]))
    $format=strtolower($_POST["format"]);
else
    $format="html";
    
if(isset($_POST["contents"]))
    $contents=$_POST["contents"];
else
    $contents="";
	
// APRO IL DATABASE
$maestro=maestro_opendb($env, false);

if(qv_validatesession($maestro, $sessionid, "quiver")){

	$env_strconn="";
	$envtemporary=qv_setting($maestro, "_TEMPENVIRON", "temporary");
	include($path_databases."_environs/".$envtemporary.".php");
	$temporary=$env_strconn;
	
	$tempid=qv_createsysid($maestro);
	
	if($format=="pdf"){
        $namefile="postman$tempid.pdf";
		$pathfile=$temporary . $namefile;
		try{
			require_once $path_cambusa."html2pdf/html2pdf.class.php";
			$objpdf=new HTML2PDF( 
				"P", 
				array( 210, 297), 
				"en", 
				true, 
				"UTF-8", 
				array(20, 31, 20, 31)
			);

			$objpdf->WriteHTML($contents);
			$objpdf->Output($pathfile, "F");
		}
		catch(HTML2PDF_exception $e) {
			$fp=fopen($pathfile, "w");
			fwrite($fp, $e);
			fclose($fp);
		}
	}
	else{
        $namefile="postman$tempid.htm";
		$pathfile=$temporary . $namefile;
		file_put_contents($pathfile, $contents);
	}

    // RESTITUISCO IL PERCORSO DEL DOCUMENTO
    print $namefile;
}

// CHIUDO IL DATABASE
maestro_closedb($maestro);
?>