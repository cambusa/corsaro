<?php 
/****************************************************************************
* Name:            food4browser.php                                         *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tocambusa="../../cambusa/";
include_once $tocambusa."rymaestro/maestro_execlib.php";

$food=array();
$food["success"]=1;

if(isset($_GET["env"]) && isset($_GET["site"])){
    try{
        $env=$_GET["env"];
        $site=ryqEscapize($_GET["site"]);
        if(isset($_GET["id"]))
            $PAGEID=ryqEscapize($_GET["id"]);
        else
            $PAGEID="";
            
        // APRO IL DATABASE
        $maestro=maestro_opendb($env, false);

        // DETERMINO IL SITO
        maestro_query($maestro, "SELECT * FROM QW_WEBSITES WHERE [:UPPER(NAME)]='".strtoupper($site)."'", $r);
        if(count($r)==1){
            $food["lenid"]=$maestro->lenid;
            $food["site"]=$r[0];
            $food["meta"]="";
            $TITLESITE=$r[0]["DESCRIPTION"];
            $SITEID=$r[0]["SYSID"];
            $HOMEPAGEID=$r[0]["HOMEPAGEID"];
            $DEFAULTID=$r[0]["DEFAULTID"];
            $FAVICON=$r[0]["FAVICON"];
            $PROTECTED=intval($r[0]["PROTECTED"]);
            $BOT="";
            $LINKBOT="";
            if($PAGEID==""){
                $PAGEID=$DEFAULTID;
            }
            if($FAVICON!="")
                $FAVICON="data:image/jpeg;base64,$FAVICON";
            else
                $FAVICON="_images/favicon.ico";
            $food["favicon"]=$FAVICON;
            $food["protected"]=$PROTECTED;
            $food["content"]="";
            $food["gender"]="fm";
            $food["lang"]="it";
            // DETERMINO LE CHIAVI PER MOTORI DI RICERCA
            maestro_query($maestro, "SELECT DESCRIPTION,REGISTRY,ABSTRACT,TAG,CONTENTTYPE,SETRELATED,REFERENCE FROM QW_WEBCONTENTS WHERE SYSID='$PAGEID'", $r);
            if(count($r)==1){
                $TITLECONTENT=$r[0]["DESCRIPTION"];
                $CONTENTTYPE=$r[0]["CONTENTTYPE"];
                $REFERENCE=$r[0]["REFERENCE"];
                if(strlen($REFERENCE)>=4){
                    $food["gender"]=substr($REFERENCE, 0, 2);
                    $food["lang"]=substr($REFERENCE, 2, 2);
                    if($food["lang"]=="##"){
                        $food["lang"]="";
                    }
                }
                $META=$r[0]["DESCRIPTION"]." ".$r[0]["ABSTRACT"]." ".$r[0]["TAG"];
                $META=strtolower($META);
                $META=preg_replace("/<[bh]r\/?>/i", " ", $META);
                $META=strip_tags($META);
                $META=htmlentities(utf8Decode($META));
                $META=preg_replace("/&([aeiou])(grave|acute);/", "$1", $META);
                @preg_match_all("/([A-Z]{4,})/i", $META, $m);
                if(isset($m[1]))
                    $v=$m[1];
                else
                    $v=Array();
                $META="";
                for($i=0;$i<count($v);$i++){
                    if($META!=""){
                        $META.=", ";
                    }
                    $META.=$v[$i];
                }
                $food["meta"]=$META;

                // TESTO PROVVISORIO PER I MOTORI DI RICERCA
                $BOT.="<h2><a href='filibuster.php?env=$env&amp;site=$site'>$TITLESITE</a></h2><br/>\n";
                $BOT.="<br/>\n";
                buildfood4bot($maestro, $r[0]);

                // Gli accodo i correlati
                $SETRELATED=$r[0]["SETRELATED"];
                $v=array();
                // Determino le chiavi dei correlati
                maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTFIELD='SETRELATED' AND PARENTID='$SETRELATED'", $d);
                for($i=0; $i<count($d); $i++){
                    $v[]=$d[$i]["SELECTEDID"];
                }
                $RELATED="'".implode($v, "','")."'";

                // Correlati
                maestro_query($maestro, "SELECT SYSID,DESCRIPTION FROM QW_WEBCONTENTS WHERE SYSID IN ($RELATED) AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID')", $d);
                for($i=0; $i<count($d); $i++){
                    $RELID=$d[$i]["SYSID"];
                    $DESCRIPTION=$d[$i]["DESCRIPTION"];
                    $LINKBOT.="<a href='filibuster.php?env=$env&site=$site&id=$RELID'>$DESCRIPTION</a><br/>\n";
                }

                // Allegati
                if($CONTENTTYPE=="attachment"){
                    // Sottocartella di "databases" dei documenti allegati
                    include_once "food4_library.php";
                    flb_dirattachment($maestro, $dirattachment, $urlattachment);
                    maestro_query($maestro, "SELECT * FROM QWFILES WHERE RECORDID='$PAGEID' AND IMPORTNAME<>'' ORDER BY SORTER,AUXTIME DESC,FILEID DESC", $f);
                    for($i=0; $i<count($f); $i++){
                        $SYSID=$f[$i]["FILEID"];
                        $NAME=$f[$i]["NAME"];
                        $DESCRIPTION=$f[$i]["DESCRIPTION"];
                        $SUBPATH=$f[$i]["SUBPATH"];
                        $IMPORTNAME=$f[$i]["IMPORTNAME"];
                        $path_parts=pathinfo($IMPORTNAME);
                        if(isset($path_parts["extension"]))
                            $ext="." . $path_parts["extension"];
                        else
                            $ext="";
                        $urltfile=$urlattachment.$SUBPATH.$SYSID.$ext;
                        $LINKBOT.="<br/>\n<img src='$urltfile' />";
                    }
                }
                $food["content"]=$TITLECONTENT;
            }
            // DETERMINO LA STRUTTURA
            $food["structure"]=array();
            $containers=0;
            $SPECIALS="|";
            maestro_query($maestro, "SELECT * FROM QW_WEBCONTAINERS WHERE SYSID='$HOMEPAGEID'", $r);
            if(count($r)==1){
                $food["structure"][$containers]=$r[0];
                $food["structure"][$containers]["PARENT"]="";
                $containers+=1;
                
                // DETERMINO RICORSIVAMENTE LA GERARCHIA DEI CONTENITORI
                solvecontainers($maestro, $food, $containers, $r[0]["SYSID"]);
                
                // DETERMINO I CONTENUTI SPECIALI
                solvespecials($maestro, $r[0], false);
            }
            // DETERMINO I CONTENUTI SPECIALI DELLE SOTTOSTRUTTURE
            $food["specials"]=$SPECIALS;
            $food["bot"]=$BOT.$LINKBOT;
        }
        else{
            $food["success"]=0;
            $food["err"]="Sito non trovato";
        }
        // CHIUDO IL DATABASE
        maestro_closedb($maestro);
    }
    catch(Exception $e){
        $food["success"]=0;
        $food["err"]=$message=$e->getMessage();
    }
}
else{
    $food["success"]=0;
    $food["err"]="Ambiente/sito non specificati";
}

print serialize($food);

function solvecontainers($maestro, &$food, &$containers, $PARENTID){
    global $SITEID;
    maestro_query($maestro, "SELECT * FROM QW_WEBCONTAINERS WHERE SITEID='$SITEID' AND REFOBJECTID='$PARENTID' ORDER BY ORDINATORE", $r);
    for($i=0; $i<count($r); $i++){
        $food["structure"][$containers]=$r[$i];
        $food["structure"][$containers]["PARENT"]=$PARENTID;
        $containers+=1;

        // DETERMINO RICORSIVAMENTE LA GERARCHIA DEI CONTENITORI
        solvecontainers($maestro, $food, $containers, $r[$i]["SYSID"]);
        
        // DETERMINO I CONTENUTI SPECIALI
        solvespecials($maestro, $r[$i], false);
    }
}
function solvespecials($maestro, $container, $flagbot){
    global $SITEID, $SPECIALS, $PAGEID, $DEFAULTID, $BOT;
    if(intval($container["CURRENTPAGE"])){
        $CONTENTID=$PAGEID;
        if($CONTENTID==""){
            $CONTENTID=$DEFAULTID;
        }
    }
    else{
        $CONTENTID=$container["CONTENTID"];
    }
    if($CONTENTID!=""){
        maestro_query($maestro, "SELECT SYSID,DESCRIPTION,ABSTRACT,REGISTRY,CONTENTTYPE,SPECIALS FROM QW_WEBCONTENTS WHERE SYSID='$CONTENTID'", $r);
        if(count($r)==1){
            $sp=$r[0]["SPECIALS"];
            if($sp!=""){
                if(strpos($SPECIALS, "|$sp|")===false){
                    $SPECIALS.="$sp|";
                }
            }
            if($flagbot){
                buildfood4bot($maestro, $r[0]);
            }
            $CONTENTTYPE=strtolower($r[0]["CONTENTTYPE"]);
            if($CONTENTTYPE=="frames"){
                solvecontainers2($maestro, $r[0]["SYSID"]);
            }
        }
    }
}
function solvecontainers2($maestro, $CONTENTID){
    global $SITEID;
    
    // DETERMINO IL PARENT DEI CONTENITORI
    maestro_query($maestro, "SELECT SETFRAMES FROM QW_WEBCONTENTS WHERE SYSID='$CONTENTID'", $c);
    if(count($c)==1){
        $SETFRAMES=$c[0]["SETFRAMES"];
        // DETERMINO I CONTENITORI
        maestro_query($maestro, "SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='$SETFRAMES' ORDER BY SORTER", $r);
        for($i=0; $i<count($r); $i++){
            $SELECTEDID=$r[$i]["SELECTEDID"];
            maestro_query($maestro, "SELECT CURRENTPAGE,CONTENTID FROM QW_WEBCONTAINERS WHERE SYSID='$SELECTEDID'", $s);
            if(count($s)==1){
                // DETERMINO I CONTENUTI SPECIALI
                solvespecials($maestro, $s[0], true);
            }
        }
    }
}
function buildfood4bot($maestro, $rec){
    global $env, $site, $BOT;
    $TITLECONTENT=htmlentities(utf8Decode($rec["DESCRIPTION"]));
    $ABSTRACT=htmlentities(utf8Decode($rec["ABSTRACT"]));
    $CONTENT=$rec["REGISTRY"];
    $CONTENT=preg_replace("/<script[^>]*>[^\\x00]*?<\\/script>/i", "", $CONTENT);
    $CONTENT=preg_replace("/ id=('|\")[^>]*?('|\")/i", "", $CONTENT);
    $CONTENT=preg_replace("/ href=('|\")([A-Z0-9]{".($maestro->lenid)."})('|\")/", " href=$1filibuster.php?env=$env&site=$site&id=$2$1", $CONTENT);
    
    $BOT.="<h3>$TITLECONTENT</h3><br/>\n";
    $BOT.="<br/>\n";
    if($ABSTRACT!=""){
        $BOT.=$ABSTRACT."<br/>\n";
        $BOT.="<br/>\n";
    }
    if($CONTENT!=""){
        $BOT.=$CONTENT."<br/>\n";
        $BOT.="<br/>\n";
    }
}
?>