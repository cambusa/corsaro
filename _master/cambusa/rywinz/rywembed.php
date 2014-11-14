<?php 
/****************************************************************************
* Name:            rywembed.php                                             *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.61                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(isset($_POST["sessionid"])){
    $sessionid=$_POST["sessionid"];
    setcookie("egosessionid", $sessionid, time()+24*60*60);
}
elseif(isset($_GET["sessionid"])){
    $sessionid=$_GET["sessionid"];
    setcookie("egosessionid", $sessionid, time()+24*60*60);
}
else{
    if(isset($_COOKIE["egosessionid"]))
        $sessionid=$_COOKIE["egosessionid"];
    else
        $sessionid="";
}
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
<title><?php print $winz_apptitle ?></title>
<link rel='shortcut icon' href='_images/favicon.ico' type='image/x-icon'/>
<?php
CambusaLibrary("ryBox");
CambusaLibrary("ryQue");
CambusaLibrary("rySource");
CambusaLibrary("ryWembed");
CambusaLibrary("ryDraw");

if(is_file($winz_loadmodules)){
    include_once $winz_loadmodules;
}

?>
<!--[if lt IE 9]>
<link rel="stylesheet" href="<?php print $url_cambusa ?>jqdesktop/assets/css/ie.css" />
<![endif]-->
<script>
_baseURL="<?php  print rywinzHost() ?>";
_sessionid="<?php  print $sessionid ?>";
var _appname="<?php  print $winz_appname ?>";
var _apptitle="<?php  print $winz_apptitle ?>";
$(document).ready(function(){
    RYEGO.go({
        crossdomain:"",
        appname:_appname,
        formlogin:"ryegoembed.php",
        config:function(d){
            _sessioninfo=d;
            if(window.console)console.log(_sessioninfo);
            $("body").css("display", "block");
            RYQUE.request({
                environ:_sessioninfo.environ,
                ready:function(){
                    RYQUEAUX.request({
                        environ:_sessioninfo.environ,
                        ready:function(){
                            mdiconfig();
                        }
                    });
                }
            });
        }
    });
});
function mdiconfig(){
    _openingparams="({environ:\""+_appname+"_"+_sessioninfo.role+"\",root:\""+_sessioninfo.roledescr+"\"})";
    RYWINZ.newform({
        id:"<?php  print $winz_functionname ?>",
        name:"<?php  print $winz_functionname ?>",
        path:"<?php  print $winz_functionpath ?>",
        title:"<?php  print $winz_functiontitle ?>",
    });
}
function winz_logout(){
    winzRemoveAll(  // Rimozione di tuttu i form
        function(){
            RYQUE.dispose(  // Rimozione RYQUE principale
                function(){
                    RYQUEAUX.dispose(   // Rimozione RYQUE ausiliario
                        function(){
                            ego_logout();
                        }
                    );
                }
            );
        }
    );
    function ego_logout(){
        if(_logoutcall!==false){    // Logout personalizzato
            _logoutcall(
                function(){
                    RYEGO.logout();
                }
            );
        }
        else{   // Logout standard
            RYEGO.logout();
        }
    }
    _pause(1000);
}
</script>
</head>
<body spellcheck="false">

<div style="position:absolute;top:0px;left:0px;right:0px;height:12px;padding-right:5px;font-size:10px;text-align:right;background:black;">
<a href="javascript:winz_logout()" style="color:#F9F9F9;cursor:pointer;">Logout</a>
</div>

<div style="position:absolute;top:12px;left:0px;right:0px;bottom:0px;">
<div id="desktop"></div>
</div>

<div id="winz-dialog"></div>
<div id="winz-printing"></div>
<iframe id="winz-iframe"></iframe>
    
</body>
</html>
<?php
function rywinzHost(){
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
    $p=strpos($pageURL, "/apps");
    if($p!==false){
        $pageURL=substr($pageURL, 0, $p+1);
    }
    return $pageURL;
}
?>