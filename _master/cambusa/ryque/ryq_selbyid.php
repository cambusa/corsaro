<?php 
/****************************************************************************
* Name:            ryq_selbyid.php                                          *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$reqid=$_POST['reqid'];
$listid=$_POST['listid'];
$lenkey=12;

// REPERIMENTO LUNGHEZZA SYSID
$env_name=file_get_contents("requests/".$reqid.".req");
include("../sysconfig.php");
include($path_databases."_environs/".$env_name.".php");
if(isset($env_lenid))
    $lenkey=$env_lenid;

$s=array();
$buff=file_get_contents("requests/".$reqid.".ndx");
$v=explode("|", $listid);
foreach($v as $i){
    if($i!=""){
        $p=strpos($buff, $i);
        if($p!==false)
            $s[]=round($p/($lenkey+1))+1;
    }
}

print implode("|",$s);
?>