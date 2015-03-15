<?php 
/****************************************************************************
* Name:            food4search.php                                          *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tocambusa="../../cambusa/";
include_once $tocambusa."rymaestro/maestro_execlib.php";

$food="";

$hostlink="";
$env="";
$site="";
$TOOLID="";
$PAGEID="";
$container_width=600;
$searchtext="";

if(isset($_POST["host"]))
    $hostlink=$_POST["host"];
elseif(isset($_GET["host"]))
    $hostlink=$_GET["host"];

if(isset($_POST["env"]))
    $env=$_POST["env"];
elseif(isset($_GET["env"]))
    $env=$_GET["env"];

if(isset($_POST["site"]))
    $site=flb_escapize($_POST["site"]);
elseif(isset($_GET["site"]))
    $site=flb_escapize($_GET["site"]);

if(isset($_POST["toolid"]))
    $TOOLID=flb_escapize($_POST["toolid"]);
elseif(isset($_GET["toolid"]))
    $TOOLID=flb_escapize($_GET["toolid"]);

if(isset($_POST["pageid"]))
    $PAGEID=flb_escapize($_POST["pageid"]);
elseif(isset($_GET["pageid"]))
    $PAGEID=flb_escapize($_GET["pageid"]);

if(isset($_POST["width"]))
    $container_width=intval($_POST["width"]);
elseif(isset($_GET["width"]))
    $container_width=intval($_GET["width"]);

if(isset($_POST["search"]))
    $searchtext=flb_escapize($_POST["search"]);
elseif(isset($_GET["search"]))
    $searchtext=flb_escapize($_GET["search"]);

if($env!="" && $site!=""){
    // APRO IL DATABASE
    $maestro=maestro_opendb($env, false);
    
    $food.="<div class='filibuster-found'>";
    
    // DETERMINO GLI ATTRIBUTI DEL SITO
    maestro_query($maestro, "SELECT SYSID,DEFAULTID FROM QW_WEBSITES WHERE [:UPPER(NAME)]='".strtoupper($site)."'", $s);
    if(count($s)==1){
        $SITEID=$s[0]["SYSID"];
        $DEFAULTID=$s[0]["DEFAULTID"];
        $url=food4containerCorsaro()."filibuster.php";

        // DETERMINAZIONE PARAMETRI DI RICERCA
        $MAXITEMS=100;
        $OPT_ITEMDETAILS=1;
        maestro_query($maestro, "SELECT ITEMDETAILS,SEARCHITEMS FROM QW_WEBCONTENTS WHERE SYSID='$TOOLID'", $c);
        if(count($c)==1){
            $MAXITEMS=intval($c[0]["SEARCHITEMS"]);
            $OPT_ITEMDETAILS=intval($c[0]["ITEMDETAILS"]);
        }
        
        $food.="<div class='filibuster-found-back'>";
        $food.="<a href='$url?env=".$maestro->environ."&site=$site&id=$PAGEID'>Torna alla pagina</a>";
        $food.="</div>";
    
        $SEARCHING=strtoupper(str_replace(" ", "%", $searchtext));
        $SEARCHINGE=htmlentities(utf8Decode($SEARCHING));
        $sql="";
        $sql.="SELECT ";
        $sql.="{AS:TOP $MAXITEMS} ";
        $sql.="SYSID,DESCRIPTION,ABSTRACT,ICON ";
        $sql.="FROM ";
        $sql.="QW_WEBCONTENTS ";
        $sql.="WHERE (";
        if($maestro->provider!="sqlserver"){
            $sql.="[:UPPER(ABSTRACT)] LIKE '%$SEARCHING%' OR ";
            $sql.="[:UPPER(REGISTRY)] LIKE '%$SEARCHING%' OR ";
            $sql.="[:UPPER(ABSTRACT)] LIKE '%$SEARCHINGE%' OR ";
            $sql.="[:UPPER(REGISTRY)] LIKE '%$SEARCHINGE%' OR ";
        }
        $sql.="[:UPPER(DESCRIPTION)] LIKE '%$SEARCHING%' OR ";
        $sql.="[:UPPER(TAG)] LIKE '%$SEARCHING%' OR ";
        $sql.="[:UPPER(DESCRIPTION)] LIKE '%$SEARCHINGE%' OR ";
        $sql.="[:UPPER(TAG)] LIKE '%$SEARCHINGE%'";
        $sql.=") AND ";
        $sql.="(SITEID='' OR SITEID='$SITEID') AND ";
        $sql.="SCOPE=0 ";
        $sql.="{O: AND ROWNUM=$MAXITEMS} ";
        $sql.="ORDER BY TIMEUPDATE DESC ";
        $sql.="{LM:LIMIT $MAXITEMS}{D:FETCH FIRST $MAXITEMS ROWS ONLY}";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            for($i=0; $i<count($r); $i++){
                $food.=flb_createitem(
                    $maestro, 
                    $url, 
                    $site, 
                    $r[$i]["SYSID"], 
                    $r[$i]["DESCRIPTION"], 
                    $r[$i]["ICON"], 
                    $r[$i]["ABSTRACT"],
                    $PAGEID
                );
            }
        }
        else{
            $food.="<div class='filibuster-found-notfound'>";
            $food.="Nessun risultato!";
            $food.="</div>";
        }

        $food.="<div class='filibuster-found-back'>";
        $food.="<a href='$url?env=".$maestro->environ."&site=$site&id=$PAGEID'>Torna alla pagina</a>";
        $food.="</div>";
    }
    else{
        $food.="<div class='filibuster-error'>";
        $food.="Sito non disponibile!";
        $food.="</div>";
    }
    
    $food.="</div>";
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
else{
    $food.="<div class='filibuster-error'>";
    $food.="Ambiente/sito non specificati!";
    $food.="</div>";
}

print $food;

function food4containerCorsaro(){
    global $hostlink;
    return "";
    /*
    if($hostlink=="@"){
        return "";
    }
    else{
        $s=food4containerURL();
        $p=strpos($s, "/ryquiver");
        if($p!==false){
            $s=substr($s, 0, $p-5);
        }
        $s.="/apps/corsaro/";
        return $s;
    }
    */
}
function food4containerURL(){
    $pageURL='http';
    if(isset($_SERVER["HTTPS"])){
        if($_SERVER["HTTPS"]=="on"){
            $pageURL.="s";
        }
    }
    $pageURL.="://";
    if($_SERVER["SERVER_PORT"]!="80"){
        $pageURL.=$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }
    else{
        $pageURL.=$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
function flb_plaintext($descr){
    $descr=preg_replace("/\<BR[\/]?\>/i", "@@@@@@", $descr);
    $descr=strip_tags($descr);
    $descr=preg_replace("/@@@@@@/i", "<br/>", $descr);
    return $descr;
}
function flb_createitem($maestro, $url, $site, $SYSID, $DESCRIPTION, $ICON, $ABSTRACT, $selected="", $category=""){
    global $OPT_ITEMDETAILS;
    $food="";
    $DESCRIPTION=flb_plaintext($DESCRIPTION);
    $ABSTRACT=flb_plaintext($ABSTRACT);
    if($SYSID==$selected){
        $category.=" filibuster-selected";
    }
    $food.="<div class='filibuster-item $category'>";
    
    if($OPT_ITEMDETAILS)
        $title="";
    else
        $title=flb_titleformat($DESCRIPTION, $ABSTRACT);
    
    $food.="<a href='$url?env=".$maestro->environ."&site=$site&id=$SYSID' title='$title'>";
    
    $food.="<div class='filibuster-item-title'>";
    $food.=$DESCRIPTION;
    $food.="</div>";

    if($OPT_ITEMDETAILS){
        $food.="<div class='filibuster-item-icon'>";
        if($ICON!=""){
            $food.="<img src='data:image/jpeg;base64,$ICON' border='0' />";
        }
        $food.="</div>";
        
        $food.="<div class='filibuster-item-abstract'>";
        $food.=$ABSTRACT;
        $food.="</div>";
    }

    $food.="</a>";
    $food.="</div>";
    $food.="<div class='filibuster-skip'>&nbsp;</div>";

    return $food;
}
function flb_titleformat($title, $abstract){
    if ($abstract!="")
        $title.=" - ".$abstract;
    $title=preg_replace("/\<BR\>/i", " ", $title);
    $title=preg_replace("/\<BR[\/]\>/i", " ", $title);
    $title=strtr($title, array("\"" => "&acute;", "'" => "&acute;"));
    $title=strip_tags($title);
    return $title;
}
function flb_escapize($var){
    return str_replace("'", "''", strtr(trim($var), array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\")));
}
?>