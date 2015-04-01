<?PHP
/****************************************************************************
* Name:            ryq_indexodbc.php                                        *
* Project:         Cambusa/ryQue                                            *
* Version:         1.69                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tr=Array();
$tr["\'"]="'";
$tr["\\\""]="\"";
$tr["\\\\"]="\\";

$reqid=$_POST["reqid"];
$provider=$_POST["provider"];
$from=$_POST["from"];
$where=html_entity_decode(strtr($_POST["where"],$tr));
$orderby=$_POST["orderby"];
$limit=$_POST["limit"];

$env_name=file_get_contents("requests/".$reqid.".req");

include("../sysconfig.php");
include($path_databases."_environs/".$env_name.".php");

if(isset($env_lenid))
    $lenkey=$env_lenid;

$conn=odbc_connect($env_strconn, $env_user, $env_password, 1);

switch($provider){
case "db2odbc":
    if(trim($where)!="")
        $q="SELECT SYSID FROM ".$from." WHERE ".$where. " ORDER BY ".$orderby." FETCH FIRST $limit ROWS ONLY";
    else
        $q="SELECT SYSID FROM ".$from. " ORDER BY ".$orderby." FETCH FIRST $limit ROWS ONLY";
    break;
default:
    if(trim($where)!="")
        $q="SELECT TOP $limit SYSID FROM ".$from." WHERE ".$where. " ORDER BY ".$orderby;
    else
        $q="SELECT TOP $limit SYSID FROM ".$from. " ORDER BY ".$orderby;
}
if(@$res=odbc_exec($conn, $q)){
    odbc_longreadlen($res, 100000000);
    odbc_result_all($res);
}
else{
    include_once "../rygeneral/writelog.php";
    writelog($q . ";\r\n--->" . odbc_errormsg($conn));
    print "<h2>Error</h2>";
}
odbc_free_result($res);
odbc_close($conn);
?>
