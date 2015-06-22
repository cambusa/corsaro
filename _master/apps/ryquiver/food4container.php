<?php 
/****************************************************************************
* Name:            food4container.php                                       *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tocambusa="../../cambusa/";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once "food4_library.php";

$food="";
$jfood=array();
$TYPEFOOD="[C]";

$hostlink="";
$env="";
$site="";
$CONTENTID="";
$PAGEID="";
$container_width=600;
$PROTECTED=false;

if(isset($_POST["host"]))
    $hostlink=$_POST["host"];
elseif(isset($_GET["host"]))
    $hostlink=$_GET["host"];

if(isset($_POST["env"]))
    $env=$_POST["env"];
elseif(isset($_GET["env"]))
    $env=$_GET["env"];

if(isset($_POST["site"]))
    $site=ryqEscapize($_POST["site"]);
elseif(isset($_GET["site"]))
    $site=ryqEscapize($_GET["site"]);

if(isset($_POST["id"]))
    $CONTENTID=ryqEscapize($_POST["id"]);
elseif(isset($_GET["id"]))
    $CONTENTID=ryqEscapize($_GET["id"]);
    
if(isset($_POST["pageid"]))
    $PAGEID=ryqEscapize($_POST["pageid"]);
elseif(isset($_GET["pageid"]))
    $PAGEID=ryqEscapize($_GET["pageid"]);

if(isset($_POST["width"]))
    $container_width=intval($_POST["width"]);
elseif(isset($_GET["width"]))
    $container_width=intval($_GET["width"]);

if($env!="" && $site!="" && $CONTENTID!=""){
    // APRO IL DATABASE
    $maestro=maestro_opendb($env, false);
    
    // DETERMINO GLI ATTRIBUTI DEL SITO
    maestro_query($maestro, "SELECT SYSID,DEFAULTID,PROTECTED FROM QW_WEBSITES WHERE [:UPPER(NAME)]='".strtoupper($site)."'", $s);
    if(count($s)==1){
        $SITEID=$s[0]["SYSID"];
        $DEFAULTID=$s[0]["DEFAULTID"];
        $PROTECTED=intval($s[0]["PROTECTED"]);
    
        maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SYSID='$CONTENTID'", $r);
        if(count($r)==1){
            $DESCRIPTION=$r[0]["DESCRIPTION"];
            $ABSTRACT=$r[0]["ABSTRACT"];
            $CONTENTTYPE=strtolower($r[0]["CONTENTTYPE"]);
            $ICON=$r[0]["ICON"];
            $OPT_ITEMDETAILS=intval($r[0]["ITEMDETAILS"]);
            $OPT_NAVHOME=intval($r[0]["NAVHOME"]);
            $OPT_NAVPRIMARY=intval($r[0]["NAVPRIMARY"]);
            $OPT_NAVPARENTS=intval($r[0]["NAVPARENTS"]);
            $OPT_NAVSIBLINGS=intval($r[0]["NAVSIBLINGS"]);
            $OPT_NAVRELATED=intval($r[0]["NAVRELATED"]);
            $OPT_NAVTOOL=intval($r[0]["NAVTOOL"]);
            $OPT_NAVSORTING=intval($r[0]["NAVSORTING"]);
            // CASISTICA SPECIALS
            $SPECIALS=strtolower($r[0]["SPECIALS"]);
            if($SPECIALS!="")
                $specials="filibuster-specials-$SPECIALS";
            else
                $specials="";
            $elementid="element_$CONTENTID";
            // CASISTICA CONTENTTYPE
            switch($CONTENTTYPE){
            case "wysiwyg":
                $food.="<div class='filibuster-wysiwyg $specials' id='$elementid'>";
                $food.="<div class='filibuster-description'>";
                $food.=$DESCRIPTION;
                $food.="</div>";
                $food.="<div class='filibuster-date'>";
                $food.=flb_formatdate($r[0]["AUXTIME"]);
                $food.="</div>";
                if($ABSTRACT!=""){
                    $food.="<div class='filibuster-abstract'>";
                    $food.=$ABSTRACT;
                    $food.="</div>";
                }
                $food.="<div class='filibuster-content'>";
                $food.=$r[0]["REGISTRY"];
                $food.="</div>";
                $food.="</div>";
                break;
            case "html":
                $food.="<div class='filibuster-html $specials' id='$elementid'>";
                if($OPT_ITEMDETAILS){
                    $food.="<div class='filibuster-description'>";
                    $food.=$DESCRIPTION;
                    $food.="</div>";
                    $food.="<div class='filibuster-date'>";
                    $food.=flb_formatdate($r[0]["AUXTIME"]);
                    $food.="</div>";
                    if($ABSTRACT!=""){
                        $food.="<div class='filibuster-abstract'>";
                        $food.=$ABSTRACT;
                        $food.="</div>";
                    }
                }
                $food.="<div class='filibuster-content'>";
                $food.=$r[0]["REGISTRY"];
                $food.="</div>";
                $food.="</div>";
                break;
            case "multimedia":
                $food.="<div class='filibuster-multimedia $specials' id='$elementid'>";
                $food.=solvemultimedia($maestro, $DESCRIPTION, $ABSTRACT, $r[0]["REGISTRY"], flb_formatdate($r[0]["AUXTIME"]), $r[0]["CONTENTURL"]);
                $food.="</div>";
                break;
            case "wikipedia":
                include_once $tocambusa."rygeneral/unicode.php";
                $source=$r[0]["CONTENTURL"];
                
                // PERCORSO DOCUMENTO STAMPABILE
                preg_match("§([^/]+:\/\/[^/]+).*\/([^/]*)($)§i", $source, $matches);
                $percorso=$matches[1]."/w/index.php?title=".$matches[2]."&printable=yes";
                
                // CONTENUTO
                $contenuto=@file_get_contents($percorso);
                if(!mb_check_encoding($contenuto, "UTF-8")){
                    // CI SONO CARATTERI NON UNICODE
                    $value=utf8_encode($contenuto);
                }
                // PRENDO SOLO I CONTENUTI
                $i = strpos($contenuto,"<body ");
                if($i!==false){
                    $f=strpos($contenuto,"</body>");
                    if($f!==false){
                        $contenuto=substr($contenuto,$i,$f-$i+7);
                    }
                }
                // SOSTITUISCO IL TAG BODY
                $contenuto=preg_replace("/<body/i", "<div", $contenuto);
                $contenuto=preg_replace("/<\/body>/i", "</div>", $contenuto);
                // ELIMINO IL WARNING UNICODE
                $contenuto=preg_replace("/<div id=\"mw-indicator-unicode\".[^>]*>[^\\x00]*?<\/div>/i", "", $contenuto);
                // SFRONDO LA CODA
                $i = strpos($contenuto,"<div id=\"mw-navigation\">");
                if($i!==false){
                    $contenuto=substr($contenuto,0, $i);
                    $contenuto.="</div>";
                }
                // ELIMINO I COLLEGAMENTI
                $contenuto=preg_replace("/<a.*?>/i", "", $contenuto);
                $contenuto=preg_replace("/<\/a.*?>/i", "", $contenuto);
                // ELIMINO GLI SCRIPT
                $contenuto=preg_replace("/<script[^\\x00]*?<\/script>/i", "", $contenuto);
                
                // COSTRUISCO IL CONTENUTO
                $food.="<div class='filibuster-description'>";
                $food.=$DESCRIPTION;
                $food.="</div>";
                $food.="<div class='filibuster-abstract'>";
                $food.="<i>Source: <a href='".$source."' target='_blank'>".$source."</a></i>";
                $food.="</div>";
                $food.="<div class='filibuster-content'>";
                $food.=$contenuto;
                $food.="</div>";
                break;
            case "attachment":
                $food.="<div class='filibuster-attachment'>";

                $food.="<div class='filibuster-description'>";
                $food.=$DESCRIPTION;
                $food.="</div>";
                $food.="<div class='filibuster-date'>";
                $food.=flb_formatdate($r[0]["AUXTIME"]);
                $food.="</div>";
                if($ABSTRACT!=""){
                    $food.="<div class='filibuster-abstract'>";
                    $food.=$ABSTRACT;
                    $food.="</div>";
                }
                $food.=solveattachment($maestro, $CONTENTID);
                $food.="<div class='filibuster-content'>";
                $food.=$r[0]["REGISTRY"];
                $food.="</div>";

                $food.="</div>";
                break;
            case "gallery":
                include_once "food4_gallery.php";
                $food.="<div class='filibuster-description'>";
                $food.=$DESCRIPTION;
                $food.="</div>";
                $food.="<div class='filibuster-date'>";
                $food.=flb_formatdate($r[0]["AUXTIME"]);
                $food.="</div>";
                if($ABSTRACT!=""){
                    $food.="<div class='filibuster-abstract'>";
                    $food.=$ABSTRACT;
                    $food.="</div>";
                }
                $food.=solvegallery($maestro, $CONTENTID);
                break;
            case "frames":
                $TYPEFOOD="[F]";
                solvecontainers($maestro, $CONTENTID, $jfood);
                break;
            case "url":
                $percorso=$r[0]["CONTENTURL"];
                switch(substr($percorso,0,1)){
                case "@":
                case "#":
                    $percorso=substr($percorso,1);
                }
                $food.="<iframe class='filibuster-resizable filibuster-fittable' src='$percorso' frameborder='0' width='600' height='810'></iframe>";
                break;
            case "embedding":
                $CONTENTURL=$r[0]["CONTENTURL"];
                if(substr($CONTENTURL,-1)!="/"){
                    $CONTENTURL.="/";
                }
                $ENVIRON=$r[0]["ENVIRON"];
                $EMBEDSITE=$r[0]["INCLUDEFILE"];
                $EMBEDID=$r[0]["EMBEDID"];
                $food=file_get_contents($CONTENTURL."food4container.php?env=$ENVIRON&site=$EMBEDSITE&id=$EMBEDID&width=$container_width");
                if(strlen($food)>=3){
                    $food=substr($food,3);
                }
                $food.="<div class='filibuster-source'>";
                $food.="Source:<br/>".$CONTENTURL."food4container.php?env=$ENVIRON&site=$EMBEDSITE&id=$EMBEDID";
                $food.="</div>";
                break;
            case "marquee":
                $food.=solvemarquee($maestro, $CONTENTID, $DESCRIPTION, intval($r[0]["MARQUEETYPE"]));
                break;
            case "tools":
                $food.=solvetools($maestro, $CONTENTID);
                break;
            case "homelink":
                $food.=solvehomelink($maestro, $ICON, $ABSTRACT);
                break;
            case "summary":
                if($r[0]["PARENTID"]!="")
                    $REFID=$r[0]["PARENTID"];
                elseif($PAGEID!="")
                    $REFID=$PAGEID;
                else
                    $REFID=$DEFAULTID;
                $food.=solvesummary($maestro, $REFID, $DESCRIPTION, $ABSTRACT);
                break;
            case "navigator":
                include_once "food4_navigator.php";
                if($PAGEID!="")
                    $REFID=$PAGEID;
                else
                    $REFID=$DEFAULTID;
                $food.=solvenavigator($maestro, $REFID);
                break;
            case "mailus":
                $food.=solvemailus($maestro, $CONTENTID, $DESCRIPTION, $ABSTRACT, $r[0]["REGISTRY"]);
                break;
            case "include":
                $INCLUDEFILE=$r[0]["INCLUDEFILE"];
                $INCLUDEFILE=str_replace("@customize/", "../../customize/", $INCLUDEFILE);
                $INCLUDEFILE=str_replace("@corsaro/", "../corsaro/", $INCLUDEFILE);
                if(file_exists($INCLUDEFILE)){
                    include_once $INCLUDEFILE;
                    $funct="filibuster_include";
                    if(function_exists($funct)){
                        $food.=$funct();
                    }
                }
                break;
            case "forum":
                if($PAGEID!="")
                    $REFID=$PAGEID;
                else
                    $REFID=$DEFAULTID;
                include_once "food4_forum.php";
                $food.=forumtree($maestro, $REFID);
                break;
            case "copyright":
                $AUTHOR=$r[0]["AUTHOR"];
                $DEALER=$r[0]["DEALER"];
                $food.="<div class='filibuster-copyright'>";
                $food.="Filibuster v1.0 - Technology <a href='http://www.rudyz.net/' target='_blank'>Le Cose di Rudy</a>";
                $food.=" - Speech synthesis <a href='http://www.fromtexttospeech.com/' target='_blank'>TextToSpeech</a> and <a href='http://vozme.com/' target='_blank'>vozMe</a>";
                if($DEALER!=""){
                    $food.=" - Dealer $DEALER";
                }
                if($AUTHOR!=""){
                    $food.=" - Contents $AUTHOR";
                }
                $food.="</div>";
                break;
            }
        }
        else{
            $food="Pagina non trovata!";
        }
    }
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
else{
    $food="Ambiente/sito non specificati!";
}

if($PROTECTED){
    session_start();
    if(!isset($_SESSION["sessionid"])){
        $TYPEFOOD=="[C]";
        $food="Contenuto protetto";
    }
}

if($TYPEFOOD=="[C]"){
    print $TYPEFOOD.$food;
}
else{
    array_walk_recursive($jfood, "maestro_escapize");
    print $TYPEFOOD.json_encode($jfood);
}

// FUNZIONI DI DETERMINAZIONE CONTENUTI
function solveattachment($maestro, $CONTENTID){
    global $site, $OPT_ITEMDETAILS, $container_width;
    $food="";
    // DETERMINO GLI ALLEGATI
    maestro_query($maestro, "SELECT * FROM QWFILES WHERE RECORDID='$CONTENTID' AND IMPORTNAME<>'' ORDER BY SORTER,AUXTIME DESC,FILEID DESC", $f);
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
            
        flb_dirattachment($maestro, $dirattachment, $urlattachment);
        $pathfile=$dirattachment.$SUBPATH.$SYSID.$ext;
        $urltfile=$urlattachment.$SUBPATH.$SYSID.$ext;
        
        $food.="<a name='anchor_$SYSID'></a>";
        $food.="<div id='anchor_$SYSID' class='filibuster-subtitle'>";
        if($OPT_ITEMDETAILS)
            $food.=$DESCRIPTION;
        else
            $food.="&nbsp;";
        $food.="</div>";
        
        $food.="<div class='filibuster-content'>";
        switch(strtolower($ext)){
        case ".gif":
        case ".jpg":
        case ".jpeg":
        case ".png":
        case ".svg":
            list($w, $h, $t, $a)=getimagesize($pathfile);
            $width=$w;
            $height=$h;
            $class="";
            $threshold=$container_width*0.4;
            if($w>$threshold){
                $height=round($threshold*$height/$width);
                $width=$threshold;
                $class="class='filibuster-resizable'";
            }
            $food.="<a href='flb_viewer.php?env=".$maestro->environ."&site=$site&id=$CONTENTID&file=$urltfile'><img $class src='$urltfile' width='$width' height='$height' border='0'></a>";
            break;
        case ".zip":
            if($OPT_ITEMDETAILS)
                $ANCHOR="download";
            else
                $ANCHOR=$DESCRIPTION;
            $downloader=flb_urlquiver()."food4download.php";
            $food.="<a class='filibuster-download' href='$downloader?env=".$maestro->environ."&site=$site&id=$SYSID' target='_blank'>$ANCHOR</a>";
            break;
        case ".mp3":
        case ".wav":
            $food.="<audio class='filibuster-audio filibuster-stretchable' controls>";
            $food.="<source src='$urltfile' type='audio/mpeg' >";
            $food.="</audio>";
            break;
        case ".mp4":
            $food.="<video class='filibuster-video filibuster-stretchable' controls>";
            $food.="<source src='$urltfile' type='video/mp4' >";
            $food.="</video>";
            break;
        default:
            $food.="<iframe class='filibuster-resizable' src='$urltfile' frameborder='0' width='600' height='810'></iframe>";
        }
        $food.="</div>";
    }
    return $food;
}
function solvecontainers($maestro, $CONTENTID, &$jfood){
    $frames=0;
    
    $sql="";
    $sql.="SELECT ";
    $sql.="  QW_WEBCONTAINERS.SYSID AS SYSID,";
    $sql.="  QW_WEBCONTAINERS.CURRENTPAGE AS CURRENTPAGE,";
    $sql.="  QW_WEBCONTAINERS.CONTENTID AS CONTENTID,";
    $sql.="  QW_WEBCONTAINERS.FUNCTIONNAME AS FUNCTIONNAME,";
    $sql.="  QW_WEBCONTAINERS.FRAMESTYLE AS FRAMESTYLE,";
    $sql.="  QW_WEBCONTAINERS.CLASSES AS CLASSES,";
    $sql.="  QW_WEBCONTAINERS.FRAMESCRIPT AS FRAMESCRIPT ";
    $sql.="FROM QW_WEBCONTAINERS ";
    $sql.="INNER JOIN QW_WEBCONTENTS PARENT ON ";
    $sql.="  PARENT.SYSID='$CONTENTID' ";
    $sql.="INNER JOIN QVSELECTIONS ON ";
    $sql.="  QVSELECTIONS.PARENTID=PARENT.SETFRAMES ";
    $sql.="WHERE ";
    $sql.="  QW_WEBCONTAINERS.SYSID=QVSELECTIONS.SELECTEDID AND ";
    $sql.="  (QW_WEBCONTAINERS.ENABLED=1 OR QW_WEBCONTAINERS.ENABLED IS NULL) ";
    $sql.="ORDER BY QVSELECTIONS.SORTER";
    
    maestro_query($maestro, $sql, $r);
    for($i=0; $i<count($r); $i++){
        $FUNCTIONNAME=$r[$i]["FUNCTIONNAME"];
        if($FUNCTIONNAME!="")
            $CONTAINERID=$FUNCTIONNAME;
        else
            $CONTAINERID="K".$r[$i]["SYSID"];
        $FRAMESTYLE=trim($r[$i]["FRAMESTYLE"]);
        if($FRAMESTYLE==""){
            $FRAMESTYLE="{}";
        }
        $jfood[$frames]=array();
        $jfood[$frames]["containerid"]=$CONTAINERID;
        $jfood[$frames]["contentid"]=$r[$i]["CONTENTID"];
        $jfood[$frames]["classes"]=$r[$i]["CLASSES"];
        $jfood[$frames]["style"]=$FRAMESTYLE;
        $jfood[$frames]["script"]=trim($r[$i]["FRAMESCRIPT"]);
        $frames+=1;
    }
}

function solvesummary($maestro, $CONTENTID, $DESCRIPTION, $ABSTRACT){
    global $site, $OPT_ITEMDETAILS;
    $url=food4containerCorsaro()."filibuster.php";
    $food="<div class='filibuster-summary'>";
    
    $food.="<div class='filibuster-description'>";
    $food.=$DESCRIPTION;
    $food.="</div>";

    if($ABSTRACT!=""){
        $food.="<div class='filibuster-abstract'>";
        $food.=$ABSTRACT;
        $food.="</div>";
    }
    
    $r=_getrelated($maestro, $CONTENTID);
    
    // CREO GLI ITEM
    for($i=0; $i<count($r); $i++){
        $actualurl=$url;
        if($r[$i]["CONTENTTYPE"]=="url"){
            $link=$r[$i]["CONTENTURL"];
            switch(substr($link,0,1)){
            case "@":
            case "#":
                $actualurl=$link;
            }
        }
        $food.=flb_createitem(
            $maestro, 
            $actualurl, 
            $site, 
            $r[$i]["SYSID"], 
            $r[$i]["DESCRIPTION"], 
            $r[$i]["ICON"], 
            $r[$i]["ABSTRACT"]
        );
    }
    
    $food.="</div>";
    return $food;
}

function solvemarquee($maestro, $CONTENTID, $DESCRIPTION, $MARQUEETYPE){
    global $site, $container_width;
    global $SITEID;
    $food="";
    $url=food4containerCorsaro()."filibuster.php";
    $food.="<div id='MARQUEE_$CONTENTID' class='filibuster-marquee'>";
    
    $food.="<div class='filibuster-description'>";
    $food.=$DESCRIPTION;
    $food.="</div>";

    $subfood="";
    $contenttypes="";
    if($MARQUEETYPE>0){
        // RECENTI
        maestro_query($maestro, "SELECT {AS:TOP $MARQUEETYPE} * FROM QW_WEBCONTENTS WHERE SCOPE=0 AND (SITEID='' OR SITEID='$SITEID') {O: AND ROWNUM=$MARQUEETYPE} ORDER BY TIMEINSERT DESC {LM:LIMIT $MARQUEETYPE}{D:FETCH FIRST $MARQUEETYPE ROWS ONLY}", $r);
    }
    else{
        $r=_getrelated($maestro, $CONTENTID);
    }
    for($i=0; $i<count($r); $i++){
        $subfood.=flb_createitem(
            $maestro, 
            $url, 
            $site, 
            $r[$i]["SYSID"], 
            $r[$i]["DESCRIPTION"], 
            $r[$i]["ICON"], 
            $r[$i]["ABSTRACT"]
        );
    }
    $food.="<div class='filibuster-marquee-container'>";
    
        $food.="<div id='MARQUEE1_$CONTENTID' class='filibuster-marquee-sub'>";
        $food.=$subfood;
        $food.="</div>";
        
        $food.="<div id='MARQUEE2_$CONTENTID' class='filibuster-marquee-sub filibuster-marquee-clone'>";
        $food.=$subfood;
        $food.="</div>";
    
    $food.="</div>";
    
    $food.="</div>"; // Chiudo Marquee
    return $food;
}

function solvemultimedia($maestro, $DESCRIPTION, $ABSTRACT, $REGISTRY, $AUXTIME, $CONTENTURL){
    $food="";           

    $food.="<div class='filibuster-description'>";
    $food.=$DESCRIPTION;
    $food.="</div>";
    $food.="<div class='filibuster-date'>";
    $food.=$AUXTIME;
    $food.="</div>";
    if($ABSTRACT!=""){
        $food.="<div class='filibuster-abstract'>";
        $food.=$ABSTRACT;
        $food.="</div>";
    }
    $food.="<div class='filibuster-screen'>";

    if(strpos($CONTENTURL, "youtube")!==false){
        // AGGIUSTO IL PERCORSO
        $videopath=$CONTENTURL;
        $param=strpos($videopath,"?v=");
        $videopath=substr($videopath,$param+3);
        $param=strpos($videopath,"&");
        if($param!==false){
            $videopath=substr($videopath,0,$param);
        }
        $videopath="http://www.youtube.com/v/".$videopath;
        $food.="<embed class='filibuster-stretchable' src='".$videopath."' type='application/x-shockwave-flash' allowfullscreen='true' width='480' height='380' wmode='transparent'></embed>";
    }
    else{
        $food.="<div class='filibuster-subtitle'>";
        $food.="&nbsp;";
        $food.="</div>";
    
        $ext=strtolower(pathinfo($CONTENTURL, PATHINFO_EXTENSION));
        switch($ext){
        case "mp3":
        case "wav":
            $food.="<audio class='filibuster-audio filibuster-stretchable' controls>";
            $food.="<source src='$CONTENTURL' type='audio/mpeg' >";
            $food.="</audio>";
            break;
        case "mp4":
            $food.="<video class='filibuster-video filibuster-stretchable' controls>";
            $food.="<source src='$CONTENTURL' type='video/mp4' >";
            $food.="</video>";
            break;
        }
    }
    
    $food.="</div>";
    $food.="<div class='filibuster-content'>";
    $food.=$REGISTRY;
    $food.="</div>";
    
    return $food;
}

function solvetools($maestro, $CONTENTID){
    global $site;
    $food="";
    $url=food4containerCorsaro()."filibuster.php";
    
    $searchid="SEARCH_$CONTENTID";
    $food.="<div id='$searchid' class='filibuster-search'>";
    $food.="<input class='filibuster-search-text' type='text' placeholder='Search'>";
    $food.="<div class='filibuster-search-button'>";
    $food.="<a href='javascript:void(0)' title='Search'>&#x1f50d;</a>";
    $food.="</div>";
    $food.="</div>";
    
    $voiceid="VOICE_$CONTENTID";
    $food.="<div id='$voiceid' class='filibuster-voice'>";
    $food.="<div class='filibuster-voice-button'>";
    $food.="<a href='javascript:void(0)' title='Speech (CTRL-<)'>&#x1f50a;</a>";
    $food.="</div>";
    $food.="</div>";

    $printid="PRINT_$CONTENTID";
    $food.="<div id='$printid' class='filibuster-print'>";
    $food.="<div class='filibuster-print-button'>";
    $food.="<a href='javascript:void(0)' title='Print'>&#x2399;</a>";
    $food.="</div>";
    $food.="</div>";

    return $food;
}

function solvehomelink($maestro, $ICON, $ABSTRACT){
    global $site;
    $url=food4containerCorsaro()."filibuster.php";
    
    $food="";
    $food.="<div class='filibuster-homelink'>";
    $food.="<a href='$url?env=".$maestro->environ."&site=$site'>";
    
    if($ICON!=""){
        $food.="<div class='filibuster-home-icon'>";
        $food.="<img src='data:image/jpeg;base64,$ICON' border='0' />";
        $food.="</div>";
    }
    $food.="<div class='filibuster-home-abstract'>";
    $food.=$ABSTRACT;
    $food.="</div>";
    
    $food.="</a>";
    $food.="</div>";
    return $food;
}

function solvemailus($maestro, $CONTENTID, $DESCRIPTION, $ABSTRACT, $REGISTRY){
    global $elementid;
    $food="";

    $food.="<div class='filibuster-mailus' id='$elementid'>";
    
    $food.="<div class='filibuster-description'>";
    $food.=$DESCRIPTION;
    $food.="</div>";
    
    if($ABSTRACT!=""){
        $food.="<div class='filibuster-abstract'>";
        $food.=$ABSTRACT;
        $food.="</div>";
    }
    
    $food.="<div class='filibuster-mailus-caption'>";
    $food.="E-mail";
    $food.="</div>";
    
    $food.="<div class='filibuster-mailus-email'>";
    $food.="<input type='text'>";
    $food.="</div>";
    
    $food.="<div class='filibuster-mailus-caption'>";
    $food.="Messaggio";
    $food.="</div>";
    
    $food.="<div class='filibuster-mailus-body'>";
    $food.="<textarea rows='7'></textarea>";
    $food.="</div>";
    
    $food.="<div class='filibuster-mailus-button'>Invia</div>";
    $food.="<div class='filibuster-mailus-message'>&nbsp;</div>";
    
    $food.="</div>";
    $food.="<div class='filibuster-content'>";
    $food.=$REGISTRY;
    $food.="</div>";
    
    return $food;
}
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
function flb_plaintext($descr){
    $descr=preg_replace("/\<BR[\/]?\>/i", "@@@@@@", $descr);
    $descr=strip_tags($descr);
    $descr=preg_replace("/@@@@@@/i", "<br/>", $descr);
    return $descr;
}
function flb_createitem($maestro, $url, $site, $SYSID, $DESCRIPTION, $ICON, $ABSTRACT, $selected="", $category="", $level=""){
    global $OPT_ITEMDETAILS;
    $food="";
    $DESCRIPTION=flb_plaintext($DESCRIPTION);
    $ABSTRACT=flb_plaintext($ABSTRACT);
    if($SYSID==$selected){
        $category.=" filibuster-selected";
    }
    if($level!=""){
        $category.=" ".$level;
    }
    $food.="<div class='filibuster-item $category'>";
    
    if($OPT_ITEMDETAILS)
        $title="";
    else
        $title=flb_titleformat($DESCRIPTION, $ABSTRACT);
    
    switch(substr($url,0,1)){
    case "@":
        $url=substr($url,1);
        $food.="<a href='$url' target='_blank'>";
        break;
    case "#":
        $url=substr($url,1);
        $food.="<a href='$url' target='_self'>";
        break;
    default:
        $food.="<a href='$url?env=".$maestro->environ."&site=$site&id=$SYSID' title='$title'>";
    }
    
    $food.="<div class='filibuster-item-title'>";
    $food.=$DESCRIPTION;
    $food.="</div>";

    if($OPT_ITEMDETAILS){
        if($ICON!=""){
            $food.="<div class='filibuster-item-icon'>";
            $food.="<img src='data:image/jpeg;base64,$ICON' border='0' />";
            $food.="</div>";
        }
        if($ABSTRACT!=""){
            $food.="<div class='filibuster-item-abstract'>";
            $food.=$ABSTRACT;
            $food.="</div>";
        }
    }

    $food.="</a>";
    $food.="</div>";
    $food.="<div class='filibuster-skip'>&nbsp;</div>";

    return $food;
}
function _getrelated($maestro, $CONTENTID){
    global $SITEID;
    $sql="";
    $sql.="SELECT ";
    $sql.="  QW_WEBCONTENTS.SYSID AS SYSID,";
    $sql.="  QW_WEBCONTENTS.DESCRIPTION AS DESCRIPTION,";
    $sql.="  QW_WEBCONTENTS.ABSTRACT AS ABSTRACT,";
    $sql.="  QW_WEBCONTENTS.ICON AS ICON,";
    $sql.="  QW_WEBCONTENTS.SETRELATED AS SETRELATED,";
    $sql.="  QW_WEBCONTENTS.CONTENTTYPE AS CONTENTTYPE,";
    $sql.="  QW_WEBCONTENTS.CONTENTURL AS CONTENTURL ";
    $sql.="FROM QW_WEBCONTENTS ";
    $sql.="INNER JOIN QW_WEBCONTENTS PARENT ON ";
    $sql.="  PARENT.SYSID='$CONTENTID' ";
    $sql.="INNER JOIN QVSELECTIONS ON ";
    $sql.="  QVSELECTIONS.PARENTFIELD='SETRELATED' AND ";
    $sql.="  QVSELECTIONS.PARENTID=PARENT.SETRELATED ";
    $sql.="WHERE ";
    $sql.="  QW_WEBCONTENTS.SYSID=QVSELECTIONS.SELECTEDID AND ";
    $sql.="  QW_WEBCONTENTS.SCOPE=0 AND ";
    $sql.="  (QW_WEBCONTENTS.SITEID='' OR QW_WEBCONTENTS.SITEID='$SITEID') ";
    $sql.="ORDER BY QVSELECTIONS.SORTER";

    maestro_query($maestro, $sql, $related);
    return $related;
}

function flb_strtime($sqltime){
    $sqltime=strtr( $sqltime, array("-" => "", ":" => "", "T" => "", " " => "", "'" => "") );
    return substr($sqltime."000000", 0, 14);
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
function flb_formatdate($d){
    $d=flb_strtime($d);
    return substr($d, 6, 2)."/".substr($d, 4, 2)."/".substr($d, 0, 4);
}
?>