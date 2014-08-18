<?php 
/****************************************************************************
* Name:            ryq_index.php                                            *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_macro.php";

$tr=Array();
$tr["\'"]="'";
$tr["\\\""]="\"";
$tr["\\\\"]="\\";

$reqid=$_POST['reqid'];
$select=strtr($_POST['select'],$tr);
$from=$_POST['from'];
$where=strtr($_POST['where'],$tr);
$orderby=strtr($_POST['orderby'],$tr);
$limit=$_POST["limit"];
$index=(integer)$_POST['index'];
$sels=$_POST['sels'];

if(isset($_POST['args'])){
    $args=$_POST['args'];
    if(is_array($args)){
        foreach($args as $key => $value)
            $where=str_replace("[=$key]", str_replace("'", "''", strtr(trim($value), $tr)), $where);
    }
}

$lenkey=12;
$env_quiver=false;
$env_name=file_get_contents("requests/".$reqid.".req");
include($path_databases."_environs/".$env_name.".php");

if(isset($env_lenid))
    $lenkey=$env_lenid;

if($index>0 || $sels!=""){
    $fp=fopen("requests/".$reqid.".ndx","r");

    if($index>0){
        fseek($fp, ($lenkey+1)*($index-1));
        $indexid=fread($fp,$lenkey);
    }
    
    if($sels!=""){
        $s=array();
        $elenco=explode("|",$sels);
        foreach($elenco as $i){
            fseek($fp, ($lenkey+1)*((integer)$i-1));
            $s[]=fread($fp,$lenkey);
        }
    }

    fclose($fp);
}
// SOSTITUZIONE DELLE MACRO
$maestro=new Maestro();
$maestro->provider=$env_provider;
$maestro->lenid=$lenkey;
$where=maestro_macro($maestro, $where);
$orderby=maestro_macro($maestro, $orderby);
unset($maestro);

// VALIDAZIONE DELLE QUERY PER DATABASE QUIVER
if($env_quiver){
    $sql="SELECT SYSID FROM ".$from." WHERE ".$where;
    $inc=$path_applications."ryque/appvalidatequery.php";
    if(is_file($inc)){
        include_once $inc;
        $funct="appvalidatequery";
        if(function_exists($funct)){
            if(!$funct($sql)){
                $where="0=1";
            }
        }
    }
}

// SE ERA STATA FATTA UNA RICERCA CON L'ALGORITMO ZERO TOLGO I FILE
if(is_file("requests/".$reqid.".sts")){
    @unlink("requests/".$reqid.".sts");
    @unlink("requests/".$reqid.".sto");
    @unlink("requests/".$reqid.".err");
}

switch($env_provider){
case "sqlite":
    $conn=sqlite_open($env_strconn);
    if(trim($where)!="")
        $q="SELECT SYSID FROM ".$from." WHERE ".$where. " ORDER BY ".$orderby." LIMIT $limit";
    else
        $q="SELECT SYSID FROM ".$from. " ORDER BY ".$orderby." LIMIT $limit";
    $c=sqlite_array_query($conn, $q, SQLITE_NUM);
    sqlite_close($conn);
	preg_match_all("/([0-9A-Z]{".$lenkey."})/",serialize($c),$m);
    break;
case "mysql":
    $conn=mysqli_connect($env_host, $env_user, $env_password, $env_strconn);
    if(trim($where)!="")
        $q="SELECT SYSID FROM ".$from." WHERE ".$where. " ORDER BY ".$orderby." LIMIT $limit";
    else
        $q="SELECT SYSID FROM ".$from. " ORDER BY ".$orderby." LIMIT $limit";
    $res=mysqli_query($conn, $q);
    $c=array();
    while ($riga=mysqli_fetch_assoc($res))
        $c[]=$riga;
    mysqli_free_result($res);
    mysqli_close($conn);
    preg_match_all("/([0-9A-Z]{".$lenkey."})/",serialize($c),$m);
    break;
case "oracle":
    $conn=oci_connect($env_user, $env_password, $env_strconn);
    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS\".000Z\"'"));
    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS.FF3\"Z\"'"));
    oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'"));
    if(trim($where)!="")
        $q="SELECT SYSID FROM ".$from." WHERE ".$where. " AND ROWNUM<=$limit ORDER BY ".$orderby." ";
    else
        $q="SELECT SYSID FROM ".$from. " WHERE ROWNUM<=$limit ORDER BY ".$orderby." ";
    $res=oci_parse($conn,$q);
    oci_execute($res);
    oci_fetch_all($res,$c, null, null, OCI_FETCHSTATEMENT_BY_COLUMN);
    oci_free_statement($res);
    oci_close($conn);
	preg_match_all("/([0-9A-Z]{".$lenkey."})/",serialize($c),$m);
    break;
default:
	$postdata = array(
		'reqid' => $reqid,
        'provider' => $env_provider,
		'from' => $from,
		'where' => $where,
		'orderby' => $orderby,
        'limit' => $limit
	);
	$c=do_post_request($url_cambusa."ryque/ryq_indexodbc.php", $postdata);
    preg_match_all("/<td>([^>]*)<\/td>/",$c,$m);
}

$buff=join("|",$m[1]);
$fp=fopen("requests/".$reqid.".ndx","w");
fwrite($fp,$buff);
fclose($fp);
    
if($index>0){
    $p=strpos($buff,$indexid);
    if($p!==false)
        $index=round($p/($lenkey+1))+1;
    else
        $index=0;
}

if($sels!=""){
    $sels="";
    foreach($s as $sysid){
        $p=strpos($buff,$sysid);
        if($p!==false){
            $i=round($p/($lenkey+1))+1;
            if($sels!="")
                $sels.="|";
            $sels.=$i;
        }
    }
}

$buff=$select;
$fp=fopen("requests/".$reqid.".slt","w");
fwrite($fp,$buff);
fclose($fp);

$buff=$from;
$fp=fopen("requests/".$reqid.".tbl","w");
fwrite($fp,$buff);
fclose($fp);

$r=array();
$r["count"]=count($m[1]);
$r["index"]=$index;
$r["sels"]=$sels;

print json_encode($r);

function do_post_request($url, $postdata)
{
    $data = "";
    $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10);
      
    //Collect Postdata
    foreach($postdata as $key => $val){
        $data .= "--$boundary\n";
        $data .= "Content-Disposition: form-data; name=\"".$key."\"\n\n".$val."\n";
    }
    
    $data .= "--$boundary\n";
   
    $params = array('http' => array(
           'method' => 'POST',
           'header' => 'Content-Type: multipart/form-data; boundary='.$boundary,
           'content' => $data
        ));

   $ctx = stream_context_create($params);
   $fp = fopen($url, 'rb', false, $ctx);
  
   if (!$fp) {
      throw new Exception("Problem with $url, $php_errormsg");
   }
 
   $response = @stream_get_contents($fp);
   if ($response === false) {
      throw new Exception("Problem reading data from $url, $php_errormsg");
   }
   return $response;
}
?>