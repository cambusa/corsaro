<?php 
/****************************************************************************
* Name:            ryq_solve.php                                            *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$reqid=$_POST['reqid'];
$index=$_POST['index'];
$invert=$_POST['invert'];
$lenkey=12;

// REPERIMENTO LUNGHEZZA SYSID
$env_name=file_get_contents("requests/".$reqid.".req");
include("../sysconfig.php");
include($path_databases."_environs/".$env_name.".php");
if(isset($env_lenid))
    $lenkey=$env_lenid;

$s=array();
$fp=fopen("requests/".$reqid.".ndx","r");
if($invert){
    // Devo prendere quelli non selezionati
    $size=filesize("requests/".$reqid.".ndx");
    $size=round(($size+1)/($lenkey+1));
    $e="|".$index."|";
    for($i=1;$i<=$size;$i++){
        if(strpos($e,"|".$i."|")===false){
            fseek($fp, ($lenkey+1)*((integer)$i-1));
            $s[]=fread($fp,$lenkey);
        }
    }
}
elseif($index!=""){
    // Devo prendere quelli selezionati
    $v=explode("|",$index);
    foreach($v as $i){
        if($i>0){
            fseek($fp, ($lenkey+1)*((integer)$i-1));
            $s[]=fread($fp,$lenkey);
        }
    }
}
fclose($fp);
print implode("|",$s);
?>