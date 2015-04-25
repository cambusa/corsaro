<?php 
/****************************************************************************
* Name:            filibuster.php                                           *
* Project:         Corsaro                                                  *
* Module:          Filibuster                                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

$cacheversion=24;

$filibuster_host="";            // DATI (IMPOSTARE SE I DATI SONO REMOTI)
$filibuster_environ="";         // AMBIENTE PREDEFINITO
$filibuster_sizeHQ=2500;        // DIMENSIONE MASSIMA DOCUMENTO PER SINTESI VOCALE AD ALTA QUALITA'
$mathjax_path="http://www.rudyz.net/cambusa/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML";

if(is_file("../../customize/_filibuster.php")){
    include_once "../../customize/_filibuster.php";
}

if($filibuster_host==""){
    $filibuster_remote=false;
    $filibuster_host=installationURL();
    $request_container=$filibuster_host."food4container.php";
    $request_search=$filibuster_host."food4search.php";
    $request_mail=$filibuster_host."food4mail.php";
    $request_statistics=$filibuster_host."food4statistics.php";
    $request_voice=$filibuster_host."food4voice.php";
    $gethost="";
    $ajaxmethod="POST";
}
else{
    $filibuster_remote=true;
    $request_container="_remote/remote_container.php";
    $request_search="_remote/remote_search.php";
    $request_mail="_remote/remote_mail.php";
    $request_statistics="_remote/remote_statistics.php";
    $request_voice="_remote/remote_voice.php";
    $gethost=$filibuster_host;
    $ajaxmethod="GET";
}
if(isset($_GET["env"])){
    $filibuster_environ=$_GET["env"];
}
if(isset($_GET["site"])){
    $filibuster_site=$_GET["site"];
}
if(isset($_GET["id"])){
    $filibuster_id=$_GET["id"];
}
else{
    $filibuster_id="";
}
$lensysid=14;
$buff="";
$buff=@file_get_contents($filibuster_host."food4browser.php?env=$filibuster_environ&site=$filibuster_site&id=$filibuster_id");
if($buff!=""){
    $FLB=false;
    $FLB=@unserialize($buff);
    if(is_array($FLB)){
        if(intval($FLB["success"])){
            $lensysid=intval($FLB["lenid"]);
            $sheet_width_normal=$FLB["site"]["NORMALWIDTH"];
            $sheet_width_narrow=$FLB["site"]["NARROWWIDTH"];
            $TITLESITE=strip_tags($FLB["site"]["DESCRIPTION"]." - ".$FLB["content"]);
            $DEFAULTID=$FLB["site"]["DEFAULTID"];
            $GLOBALSTYLE=$FLB["site"]["GLOBALSTYLE"];
            $GLOBALSCRIPT=$FLB["site"]["GLOBALSCRIPT"];
            $GLOBALHEAD=$FLB["site"]["GLOBALHEAD"];
            $metakeys=$FLB["metakeys"];
            $metadescr=$FLB["metadescr"];
            $favicon=$FLB["favicon"];
            $PROTECTED=$FLB["protected"];
            $STRIPPEDCONTENTS=$FLB["bot"];
            $SPECIALS=$FLB["specials"];
            $VOICELANG=$FLB["lang"];
            $VOICEGENDER=$FLB["gender"];
        }
        else{
            print $FLB["err"];
            exit;
        }
    }
    else{
        print $buff;
        exit;
    }
}
else{
    print "Servizio non disponibile!";
    exit;
}
// PERCORSO CAMBUSA
$p=strrpos($filibuster_host, "/apps/");
if($p!==false)
    $instllroot=substr($filibuster_host, 0, $p+1);
else
    $instllroot="../../";
$filibuster_cambusa=$instllroot."cambusa";

// AUTENTICAZIONE EGO
if($filibuster_remote)
    $filibuster_hostego=$instllroot;
else
    $filibuster_hostego="";

$sessionid="";
if($PROTECTED){
    session_start();
    if(isset($_GET["sessionid"])){
        $sessionid=$_GET["sessionid"];
        $_SESSION["sessionid"]=$sessionid;
    }
    elseif(isset($_POST["sessionid"])){
        $sessionid=$_POST["sessionid"];
        $_SESSION["sessionid"]=$sessionid;
    }
    elseif(isset($_SESSION["sessionid"])){
        $sessionid=$_SESSION["sessionid"];
    }
}

// DETERMINO L'EFFETTIVO ID DEL CONTENUTO
if($filibuster_id!="")
    $actualid=$filibuster_id;
else
    $actualid=$DEFAULTID;
// DETERMINO L'URL DELLA VOCE
$urlvoice="../../customize/_voice/$filibuster_environ-$actualid.mp3";
// DETERMINO LA STRUTTURA DELLA PAGINA
$STATEMENTS="";
$STRUCT=$FLB["structure"];
$PARENTS=array();
$ALIASES=array();
$CURRENTPAGE="";
$filibusterbody="filibusterbody";
// DETERMINO ALIASES, PARENTS E CAPOSTIPITE
for($i=0; $i<count($STRUCT); $i++){
    $FUNCTIONNAME=$STRUCT[$i]["FUNCTIONNAME"];
    $CONTAINERID="K".$STRUCT[$i]["SYSID"];
    $PARENT=$STRUCT[$i]["PARENT"];
    if($PARENT!=""){
        $PARENT="K".$PARENT;
    }
    else{
        if($FUNCTIONNAME!="")
            $filibusterbody=$FUNCTIONNAME;
        else
            $filibusterbody=$CONTAINERID;
    }
    if($FUNCTIONNAME!="")
        $ALIASES[$CONTAINERID]=$FUNCTIONNAME;
    else
        $ALIASES[$CONTAINERID]=$CONTAINERID;
    $PARENTS[$CONTAINERID]=$PARENT;
}
// RISOLVO GLI ALIAS DEI PARENTS
foreach($PARENTS as $CONTAINERID => $PARENT){
    if(isset($ALIASES[$PARENT])){
        $PARENTS[$CONTAINERID]=$ALIASES[$PARENT];
    }
}
for($i=0; $i<count($STRUCT); $i++){
    $JSON=trim($STRUCT[$i]["FRAMESTYLE"]);
    if($JSON==""){
        $JSON="{}";
    }
    $SCRIPT=trim($STRUCT[$i]["FRAMESCRIPT"]);
    $ORIGID="K".$STRUCT[$i]["SYSID"];
    $CONTAINERID=$ALIASES[$ORIGID];
    $PARENT=$PARENTS[$ORIGID];
    $CONTENTID="";
    if(intval($STRUCT[$i]["CURRENTPAGE"])==1){
        $CURRENTPAGE=$CONTAINERID;
    }
    if($STRUCT[$i]["CONTENTID"]!=""){
        $CONTENTID=$STRUCT[$i]["CONTENTID"];
    }
    else{
        if(intval($STRUCT[$i]["CURRENTPAGE"])==1){
            if($filibuster_id!="")
                $CONTENTID=$filibuster_id;
            else
                $CONTENTID=$DEFAULTID;
        }
    }
    $CLASSES=$STRUCT[$i]["CLASSES"];
    $STATEMENTS.="create_container('$CONTAINERID', '$PARENT', '$CONTENTID', '$CLASSES', $JSON, '$SCRIPT');\n";
}
// CERCO DI CAPIRE SE IL BROWSER E' SU DISPOSITIVO MOBILE
if(isset($_SERVER['HTTP_USER_AGENT']))
    $AGENT=$_SERVER['HTTP_USER_AGENT'];
else
    $AGENT="";
if(preg_match("/android|blackberry|iphone|ipad|ipod|mini|mobile/i", $AGENT))
    $DETECTMOBILE="true";
else
    $DETECTMOBILE="false";
?><!DOCTYPE html>
<html lang="<?php print $VOICELANG ?>">
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1" />
<meta name="keywords" content="<?php print $metakeys ?>"/>
<meta name="description" content="<?php print $metadescr ?>">
<meta name="engine" content="Filibuster">
<meta name="framework" content="Cambusa">
<meta name="copyright" content="Rodolfo Calzetti">
<meta name="license" content="GNU LGPL v3">
<meta name="repository" content="https://github.com/cambusa/">
<?php  print $GLOBALHEAD ?>

<title><?php print $TITLESITE ?></title>
<link rel='icon' href='<?php print $favicon ?>' type='image/x-icon'/>
<link type='text/css' href='_css/filibuster.css' rel='stylesheet' />
<link href="_css/dropdown.css" rel="stylesheet" />

<style>
/* CUSTOM STYLE */
<?php print $GLOBALSTYLE ?>

</style>

<noscript>
<style>
body{margin:10px;}
#filibuster-food4bot{position:static;display:block;}
</style>
</noscript>

<script type='text/javascript' src='_javascript/jquery.js'></script>
<script type='text/javascript' src='_javascript/printThis.js'></script>
<script type='text/javascript' src='_javascript/jquery.cookie.js'></script>
<script type='text/javascript' src='_javascript/jquery.ui.core.js'></script>
<script type='text/javascript' src='_javascript/jquery.ui.widget.js'></script>
<script type='text/javascript' src='_javascript/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='_javascript/jquery.ui.draggable.js'></script>
<?php
    print "<script type='text/javascript' src='_javascript/filibuster.js?ver=$cacheversion'></script>";
    if($PROTECTED){
        print "<script type='text/javascript' src='$filibuster_cambusa/rygeneral/rygeneral.js?ver=$cacheversion' ></script>";
        print "<script type='text/javascript' src='$filibuster_cambusa/ryego/ryego.js?ver=$cacheversion' ></script>";
    }
    if(strpos($SPECIALS, "|math|")!==false && $mathjax_path!=""){
        print "<script type='text/javascript' src='".$mathjax_path."'></script>";
    }
    if(strpos($SPECIALS, "|svg|")!==false){
        print "<script type='text/javascript' src='_javascript/snapsvg.js'></script>";
    }
    if($DETECTMOBILE=="true"){
        print "<script type='text/javascript' src='_javascript/jquery.mobile.js'></script>";
    }
?>

<script>
var _containers={};
var _loading={};
var _scripting={};
var flaghierarchy=false;
var _lenid=<?php print $lensysid ?>;
var _host="<?php print $filibuster_host ?>";
var _hostego="<?php print $filibuster_hostego ?>";
var _ajaxmethod="<?php print $ajaxmethod ?>";
var _gethost="<?php print $gethost ?>";
var _requestContainer="<?php print $request_container ?>";
var _requestSearch="<?php print $request_search ?>";
var _requestMail="<?php print $request_mail ?>";
var _requestStatistics="<?php print $request_statistics ?>";
var _requestVoice="<?php print $request_voice ?>";
var _environ="<?php print $filibuster_environ ?>";
var _site="<?php print $filibuster_site ?>";
var _pageid="<?php print $filibuster_id ?>";
var _actualid="<?php print $actualid ?>";
var _filibusterbody="<?php print $filibusterbody ?>";
var _currentpage="<?php print $CURRENTPAGE ?>";
var _voicelang="<?php print $VOICELANG ?>";
var _voicegender="<?php print $VOICEGENDER ?>";
var _flagstats=false;
var sheet_width=<?php print $sheet_width_normal ?>;
var sheet_width_orig=sheet_width;
var sheet_width_narrow=<?php print $sheet_width_narrow ?>;
var _mathurl="<?php print $mathjax_path ?>";
// Gestione Voice
var _currVoice=false;
$.browser.chrome=(navigator.userAgent.match(/Chrom(e|ium)/i)!==null);
// Swipe
var _swipedirection=0;
var _swipestartX=0;
var _swipestartY=0;
var _swipemoveX=0;
var _swipemoveY=0;
// AUTENTICAZIONE EGO
_sessionid="<?php  print $sessionid ?>";
// OGGETTO PUBBLICO
var FLB={};
FLB.actualid=_actualid;
FLB.supports={};
FLB.supports.svg=true;
FLB.supports.unicode=true;
FLB.metrics={};
FLB.metrics.width=800;
FLB.metrics.density=96;
FLB.detected={};
FLB.detected.mobile=<?php print $DETECTMOBILE ?>;
FLB.refresh=function(){
    containers_locate();
}
FLB.gallery=function(options){
    flb_gallery(options);
}
FLB.dropdown=function(options){
    flb_dropdown(options);
}
// GESTIONE FORUM
FLB.pause=function(millis){
    var date=new Date();
    var curDate=null;
    do{curDate=new Date();}
    while(curDate-date<millis);
};
FLB.gotoPage=function(pageid){
    var h=location.href;
    h=h.replace(/site=[^&]+/, "site="+_site);
    h=h.replace(/id=[^&]+/, "id="+pageid);
    location.href=h;
};
FLB.forum={
    formid:"", 
    userid:"", 
    username:"",
    postid:"",
    parentid:"",
    action:"",
    header:false,
    putInfo:function(info, missing){
        if(info.formid!=missing){FLB.forum.formid=info.formid}
        if(info.userid!=missing){FLB.forum.userid=info.userid}
        if(info.username!=missing){FLB.forum.username=info.username}
    },
    getInfo:function(obj){
        var ret={};
        ret.frame=$(obj).parents(".filibuster-forum");
        ret.post=$(obj).parents(".filibuster-forum-post");
        ret.corsaro=ret.frame.find(".filibuster-forum-iframe")[0];
        ret.iframe=$(ret.corsaro).find("iframe")[0];
        ret.postid="";
        ret.parentid="";
        if(ret.post.length>0){
            // SYSID DEL POST E DEL PARENT
            ret.postid=ret.post.attr("_sysid");
            ret.parentid=ret.post.attr("_parentid");
            // TOP RELATIVO DEL POST
            ret.relTop=ret.post.position().top;
            // LEFT ASSOLUTO DEL POST
            ret.absLeft=ret.post.offset().left;
            // TOP ASSOLUTO DEL POST
            ret.absTop=ret.post.offset().top;
            // MARGINE TOP DEL POST
            ret.margTop=parseInt($(ret.post).css("margin-top"));
            // DATI TOOLS
            ret.tools=$(ret.post).find(".filibuster-forum-tools")[0];
            ret.toolsTop=$(ret.tools).position().top; 
            ret.toolsHeight=$(ret.tools).height();
            // TOP E BOTTOM ASSOLUTI DEI MARGINI VISIBILI DELLA FINESTRA
            ret.scrollTop=$(window).scrollTop();
            ret.scrollBottom=ret.scrollTop+$(window).height();
            // DATI FINESTRA IMMERSA
            ret.corsaroWidth=$(ret.corsaro).width();
            ret.corsaroHeight=$(ret.corsaro).height();
            // LEFT OTTIMA
            ret.fitLeft=ret.absLeft;
            if(ret.fitLeft>ret.corsaroWidth)
                ret.fitLeft=ret.corsaroWidth-50;
            else
                ret.fitLeft-=50;
            ret.fitLeft=-ret.fitLeft;
            // TOP OTTIMO
            ret.fitTop=ret.relTop+ret.toolsTop+ret.toolsHeight+ret.margTop+5;
            if(ret.fitTop<ret.scrollTop-ret.absTop+30){ // controllo se il margine superiore del post è nascosto per via dello scroll
                ret.fitTop=ret.scrollTop-ret.absTop+30;
            }
            if(ret.absTop-ret.relTop+ret.fitTop+ret.corsaroHeight>ret.scrollBottom){
                ret.fitTop=ret.scrollBottom-ret.absTop+ret.relTop-ret.corsaroHeight
            }
            if(ret.fitTop<ret.scrollTop+30){
                ret.fitTop=ret.scrollTop+30;
            }
        }
        return ret;
    },
    showLogout:function(){
        $(FLB.forum.header).find("span").html("Welcome <b>"+FLB.forum.username+"</b>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;");
        $(FLB.forum.header).find("span,a").hide();
        $(FLB.forum.header).find("span,.filibuster-forum-logout").show();
        $(".filibuster-forum a[_userid="+FLB.forum.userid+"]").each(
            function(index){
                $(this).removeClass("filibuster-forum-disabled");
            }
        );
    },
    showLogin:function(){
        $(FLB.forum.header).find("span").html("");
        $(FLB.forum.header).find("span,a").hide();
        $(FLB.forum.header).find("span,.filibuster-forum-login").show();
        $(".filibuster-forum a[_userid="+FLB.forum.userid+"]").each(
            function(index){
                $(this).addClass("filibuster-forum-disabled");
            }
        );
    }
};
<?php
    if($GLOBALSCRIPT!=""){
        print $GLOBALSCRIPT."\n";
    }
?>

$(document).ready(function(){
<?php
    print $STATEMENTS;
    if($PROTECTED){
?>
RYEGO.go({
    crossdomain:_hostego,
    appname:"filibuster",
    config:function(d){
        flb_initialize();
    }
});
<?php
    }
    else{
?>
flb_initialize();
<?php
    }
?>
});
</script>

</head>

<body id="<?php print $filibusterbody ?>" spellcheck="false" onresize="containers_locate()">

<div class='filibuster-div' id="<?php print $filibusterbody ?>_border"><div class='filibuster-div' id="<?php print $filibusterbody ?>_inner"></div></div>

<div id="filibuster-printing"></div>
<div id="filibuster-chartest" style="position:absolute;visibility:hidden;font-family:sans-serif;font-size:48px;"></div>
<div id="filibuster-resoltest" style="position:absolute;visibility:hidden;width:1in;"></div>
<div id="filibuster-food4bot">
<?php print $STRIPPEDCONTENTS ?>

</div>
</body>
</html>
<?php 
function installationURL(){
    $s=currPageURL();
    $p=strrpos($s, "/corsaro");
    if($p!==false){
        $s=substr($s, 0, $p);
    }
    $s.="/ryquiver/";
    return $s;
}
function currPageURL(){
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
?>