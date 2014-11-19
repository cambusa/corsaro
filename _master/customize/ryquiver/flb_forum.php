<?php 
$winz_appname="corsaro";
$winz_apptitle="Corsaro";
$winz_loadmodules="../../apps/corsaro/library.php";
$winz_functionname="qvforum";
$winz_functionpath="../../apps/corsaro/qvcontenuti/";
$winz_functiontitle="Crediti Formativi";

if(isset($_GET["sitename"]))
    $sitename=$_GET["sitename"];
else
    $sitename="";
if(isset($_GET["pageid"]))
    $pageid=$_GET["pageid"];
else
    $pageid="";
$winz_moremodules=<<<MOREMODULES
<script>
var _filibustersitename="{$sitename}";
var _filibusterpageid="{$pageid}";
</script>
MOREMODULES;

include_once "../../apps/appconfig.php";
include_once "../../apps/corsaro/config.php";
include_once "../../cambusa/rywinz/rywembed.php";
?>
