<?php 
/****************************************************************************
* Name:            ryq_splice.php                                           *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../sysconfig.php";
//include_once "../rygeneral/writelog.php";

$reqid=$_POST['reqid'];
$start=intval($_POST['start']);
$length=intval($_POST['length']);
$adding=$_POST['adding'];

// REPERIMENTO AMBIENTE
$env_name=file_get_contents("requests/".$reqid.".req");

// REPERIMENTO LUNGHEZZA SYSID
$env_lenid=12;
include_once $path_databases."_environs/".$env_name.".php";

// REPERIMENTO FILE INDICE
$indexes=file_get_contents("requests/".$reqid.".ndx");

// SOSTITUISCO LA SEQUENZA CON LA NUOVA
if($indexes!="")
    $count=(strlen($indexes)+1)/$env_lenid;
else
    $count=0;

if($start>1)
    $before=substr($indexes, 0 , ($start-1)*($env_lenid+1) );
else
    $before="";

if($start<$count)
    $after=substr($indexes, ($start+$length-1)*($env_lenid+1) );
else
    $after="";

if($adding!=""){
    if($after!="")
        $adding.="|";
    elseif($before!="")
        $adding="|".$adding;
}

$indexes=$before.$adding.$after;

// RISCRIVO IL FILE INDICE
$fp=fopen("requests/".$reqid.".ndx","w");
fwrite($fp, $indexes);
fclose($fp);

print "1";
?>