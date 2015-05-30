<?php 
/****************************************************************************
* Name:            rymirror.php                                             *
* Project:         Cambusa/ryMirror                                         *
* Version:         1.69                                                     *
* Description:     Code Editor                                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../sysconfig.php";
include_once $path_applications."cacheversion.php";

// DETERMINAZIONE DELLA SESSIONE
if(isset($_GET["sessionid"])){
    $sessionid=$_GET["sessionid"];
}
elseif(isset($_POST["sessionid"])){
    $sessionid=$_POST["sessionid"];
}
else{
    $sessionid="";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
<meta name="description" content="ryMirror - Script Management">
<meta name="framework" content="Cambusa <?php print $cambusa_version ?>">
<meta name="license" content="GNU LGPL v3">
<meta name="repository" content="https://github.com/cambusa/">
<title>Mirror - Manutenzione Script</title>

<style>
.mirror-conteiner{position:relative;display:none;}
.mirror-tab{position:absolute;top:100px;left:20px;display:none;}
.mirror-title{font-size:18px;height:25px;}
.mirror-tabtitle{font-size:18px;height:40px;}
.mirror-button{font-size:12px}
.mirror-label{font-size:12px}
.mirror-selected{font-weight:bold;}
.mirror-list{width:150px;}
.mirror-count{width:15px;}
.mirror-result,td{white-space:nowrap}

body{font-family:verdana,sans-serif; font-size:10px;}
table{font-family:verdana,sans-serif; font-size:10px;border-collapse:collapse;}
td, th{padding-left:5px;padding-right:5px;width:80px;overflow:hidden;}
th{text-align:left;}
a{text-decoration:none;color:maroon;}
.tabname{font-size:14px;}
.dx{text-align:right;}
.sx{text-align:left;}
hr{color:silver;}

a.button-disabled{color:gray;cursor:default;}
a.anchor_ryfamily{color:black;}

#winz-iframe{
position:absolute;
visibility:hidden;
}

</style>

<link type='text/css' href='../rybox/rybox.css?ver=<?php print $cacheversion ?>' rel='stylesheet' />
<link type='text/css' href='../ryque/ryque.css?ver=<?php print $cacheversion ?>' rel='stylesheet' />

<script type='text/javascript' src='../jquery/jquery.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.datepicker.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.contextmenu.js?ver=<?php print $cacheversion ?>' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.widget.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.button.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.draggable.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mousewheel.js'></script>
<script type='text/javascript' src='../jquery/jquery.cookie.js' ></script>
<script type='text/javascript' src='../rygeneral/rygeneral.js?ver=<?php print $cacheversion ?>' ></script>
<script type='text/javascript' src='../ryego/ryego.js?ver=<?php print $cacheversion ?>' ></script>
<script type='text/javascript' src='../rybox/rybox.js?ver=<?php print $cacheversion ?>' ></script>
<script type='text/javascript' src='../ryque/ryque.js?ver=<?php print $cacheversion ?>' ></script>

<style>
input,select,a:focus{outline:none;border:none;}
.contextMenu{position:absolute;display:none;}
.contextMenu>ul>li{font-family:verdana;font-size:12px;text-align:left;}
.contextMenu>ul>li>a{color:black;}
.contextMenu>ul>li>a:focus{outline:1px dotted;color:black;}
.contextDisabled>a{color:silver !important;}
</style>

<link type='text/css' href='../jqtreeview/jquery.treeview.ry.css?ver=<?php print $cacheversion ?>' rel='stylesheet' />
<script type='text/javascript' src='../jqtreeview/jquery.treeview.ry.js?ver=<?php print $cacheversion ?>' ></script>
<script language='javascript'>
    _systeminfo.relative.cambusa='<?php print $url_cambusa ?>';
    _systeminfo.relative.customize='<?php print $url_customize ?>';
</script>
<script type='text/javascript' src='../ryfamily/ryfamily.js?ver=<?php print $cacheversion ?>' ></script>

<link rel="stylesheet" href="../codemirror/lib/codemirror.css">
<link rel="stylesheet" href="../codemirror/addon/fold/foldgutter.css">
<link rel="stylesheet" href="../codemirror/addon/dialog/dialog.css">
<link rel="stylesheet" href="../codemirror/theme/monokai.css">
<script src="../codemirror/lib/codemirror.js"></script>
<script src="../codemirror/addon/search/searchcursor.js"></script>
<script src="../codemirror/addon/search/search.js"></script>
<script src="../codemirror/addon/dialog/dialog.js"></script>
<script src="../codemirror/addon/edit/matchbrackets.js"></script>
<script src="../codemirror/addon/edit/closebrackets.js"></script>
<script src="../codemirror/addon/comment/comment.js"></script>
<script src="../codemirror/addon/wrap/hardwrap.js"></script>
<script src="../codemirror/addon/fold/foldcode.js"></script>
<script src="../codemirror/addon/fold/brace-fold.js"></script>
<script src="../codemirror/mode/xml/xml.js"></script>
<script src="../codemirror/mode/javascript/javascript.js"></script>
<script src="../codemirror/mode/css/css.js"></script>
<script src="../codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="../codemirror/mode/clike/clike.js"></script>
<script src="../codemirror/mode/php/php.js"></script>
<script src="../codemirror/keymap/sublime.js"></script>

<style type="text/css">
.CodeMirror {border-top: 1px solid #eee; border-bottom: 1px solid #eee; line-height: 1.3; height:600px;}
.CodeMirror-linenumbers { padding: 0 8px; }
.cm-s-monokai span{font-family:monospace; font-size:16px}
</style>

<link type='text/css' href='../ryupload/fileuploader.ry.css?ver=<?php print $cacheversion ?>' rel='stylesheet'/>
<script type='text/javascript' src='../ryupload/fileuploader.ry.js?ver=<?php print $cacheversion ?>'></script>
<script type='text/javascript' src='../ryupload/ryupload.js?ver=<?php print $cacheversion ?>'></script>

<script>
_sessioninfo.sessionid="<?php  print $sessionid ?>";

var objfamily;
var objmirror;
var currenv="";
var currpath="";
var currdirid="";
var rootid="";

var menuid="";
var menutype="";
var menupath="";
var menuclip="";
var menutitle="";

var firstedit=true;

var savedhistory=0;

var imgwaiting = new Image();
imgwaiting.src="images/progress.gif";
$(document).ready(function(){
    activation('dummy');
    RYEGO.go({
        crossdomain:"",
        appname:"mirror",
        apptitle:"Mirror",
        config:function(d){
            _sessioninfo=d;
            $(".mirror-conteiner").show();
            activation("navigator");
            currenv=_sessioninfo.environ;
            makeeditor();
        }
    });
});
function activation(id){
    if(id!="editor" || firstedit==false){
        $("#tab-navigator").hide();
        $("#button-navigator").removeClass("mirror-selected");
        $("#tab-editor").hide();
        $("#button-editor").removeClass("mirror-selected");
    
        $("#tab-"+id).show();
        $("#button-"+id).addClass("mirror-selected");
    }
    
    switch(id){
    case "editor":
        objmirror.focus();
        objmirror.refresh();
        break;
    }
}
function makeeditor(missing){
    try{
        objfamily=$("#family").ryfamily({
            left:0, 
            top:60, 
            width:500, 
            height:500, 
            scroll:false, 
            border:true,
            selectiontype:"folder",
            expand:function(o, trig){
                setcurrentfolder(trig.id);
                loadbranch(trig.id);
            },
            collapse:function(o, trig){
                setcurrentfolder(trig.id);
                o.clear(trig.id);
            },
            context:function(o, trig){
                if(trig.type=="folder"){
                    menuid=trig.id;
                    menutype="folder";
                    menupath=buildpath(menuid);
                    menutitle=$(trig.selector+"_text").html();
                }
                else{
                    menuid=trig.id;
                    menutype="file";
                    menupath=$(trig.selector+" a").attr("href").replace(/^javascript:editorload\("/, "").replace(/"\)/, "");
                    menutitle=$(trig.selector+" a").attr("title");
                }
            },
            outofcontext:function(o){
                menuid="";
                menutype="";
                menupath="";
                menutitle="";
            }
        });
    
        rootid=objfamily.addfolder({title:_sessioninfo.envdescr, open:true});

        $("#uploader").ryupload({
            left:530,
            top:60,
            width:300,
            environ:_sessioninfo.temporary,
            sessionid:_sessioninfo.sessionid,
            complete:function(id, name, ret){
                var path=buildpath(currdirid);
                $.post(
                    "../rymirror/mirror_upload.php", 
                    {
                        "env":currenv,
                        "sessionid":_sessioninfo.sessionid,
                        "import":name,
                        "path":path
                    },
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success==1){
                                loadbranch(currdirid);
                            }
                        }
                        catch(e){
                            if(window.console){console.log(d)}
                        }
                    }
                );
            }
        });
        
        objmirror=CodeMirror(document.getElementById("codescript"), {
            lineNumbers:false,
            mode:"javascript",
            indentUnit:4,
            indentWithTabs:true,
            tabSize:4,
            keyMap:"sublime",
            autoCloseBrackets:true,
            matchBrackets:true,
            showCursorWhenSelecting:true,
            theme:"monokai"
        });
        bodyresize();
        objmirror.setValue("");
        objmirror.on("change",
            function(){
                var h=objmirror.doc.historySize();
                if(savedhistory==h.undo)
                    $("#editor-pathfile").css({"color":"black"});
                else
                    $("#editor-pathfile").css({"color":"red"});
                
                if(h.undo==0)
                    $("#button-undo").css({"color":"gray","cursor":"default"});
                else
                    $("#button-undo").css({"color":"maroon","cursor":"pointer"});

                if(h.redo==0)
                    $("#button-redo").css({"color":"gray","cursor":"default"});
                else
                    $("#button-redo").css({"color":"maroon","cursor":"pointer"});
            }
        );

        $("#family").contextMenu("familymenu", {
            bindings: {
                'family_newfile': function(t) {
                    family_newfile();
                },
                'family_newfolder': function(t) {
                    family_newfolder();
                },
                'family_rename': function(t) {
                    family_rename();
                },
                'family_saveas': function(t) {
                    family_saveas();
                },
                'family_copy': function(t) {
                    menuclip=menupath;
                },
                'family_paste': function(t) {
                    family_paste();
                },
                'family_download': function(t) {
                    family_download();
                },
                'family_delete': function(t) {
                    family_delete();
                }
            },
            onContextMenu:
                function(e) {
                    return (menutype!="");
                },
            onShowMenu: 
                function(e, menu) {
                    if(menuclip=="")
                        $('#family_paste', menu).remove();
                    if(menutype=="folder"){
                        $('#family_saveas', menu).remove();
                        $('#family_download', menu).remove();
                    }
                    else{
                        $('#family_newfile', menu).remove();
                        $('#family_newfolder', menu).remove();
                    }
                    return menu;
                }
        });
    }
    catch(e){
        if(window.console){console.log(e.message)}
    }
}
function loadbranch(id){
    objfamily.clear(id);
    objfamily.loading(id, true);
    path=buildpath(id);
    $.post(
        "../rymirror/mirror_files.php", 
        {
            "env":currenv,
            "sub":path,
            "sessionid":_sessioninfo.sessionid
        },
        function(d){
            try{
                objfamily.loading(id, false);
                objfamily.clear(id);
                var v=$.parseJSON(d);
                if(v.success==1){
                    var i,nf,tp,h;
                    var p=v.path;
                    //setcurrentfolder(id);
                    for(i in v.content){
                        nf=v.content[i].name;
                        tp=v.content[i].type;
                        if(tp=="folder"){
                            objfamily.addfolder({parent:id, info:nf, title:nf});
                        }
                        else{
                            h="javascript:editorload("+$.stringify(p+nf)+")";
                            objfamily.additem({parent:id, title:"<a href='"+h+"' class='anchor_ryfamily' title='"+nf+"'>"+nf+"</a>"});
                        }
                    }
                }
            }
            catch(e){
                if(window.console){console.log(d)}
            }
        }
    );
}
function editorload(path){
    var ext=path.match(/\.\w+$/);
    if(ext)
        ext=ext[0].toLowerCase();
    else
        ext=".";
    if("|.pdf|.zip|.jpg|.jpeg|.gif|.png|.ico|.mp3|.mp4|.wav|.avi|.odf|.ods|.odt|.odp|.doc|.docx|.xls|.xlsx|".indexOf("|"+ext+"|")>0){
        menupath=path;
        family_download();
    }
    else{
        $.post(
            "../rymirror/mirror_load.php", 
            {
                "env":currenv,
                "path":path,
                "sessionid":_sessioninfo.sessionid
            },
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success){
                        currpath=path;
                        if(currpath.match(/.php$/i))
                            objmirror.setOption("mode", "php");
                        else
                            objmirror.setOption("mode", "javascript");
                        objmirror.refresh();
                        objmirror.setValue(v.content);
                        objmirror.doc.clearHistory();
                        savedhistory=0;
                        $("#button-undo").css({"color":"gray","cursor":"default"});
                        $("#editor-pathfile").css({"color":"black"});
                        $("#editor-pathfile").html(currenv+"/"+currpath);
                        if(firstedit){
                            firstedit=false;
                            $("#button-editor").removeClass("button-disabled");
                        }
                        activation("editor");
                    }
                    else{
                        if(window.console){console.log(v.description)}
                    }
                }
                catch(e){
                    if(window.console){console.log(d)}
                }
            }
        );
    }
}
function editorsave(){
    syswaiting();
    var buff=objmirror.getValue();
    $.post(
        "../rymirror/mirror_save.php", 
        {
            "env":currenv,
            "path":currpath,
            "content":buff,
            "sessionid":_sessioninfo.sessionid
        },
        function(d){
            try{
                var v=$.parseJSON(d);
                if(v.success){
                    $("#editor-pathfile").css({"color":"black"});
                    var h=objmirror.doc.historySize();
                    savedhistory=h.undo;
                    sysmessage("Salvataggio effettuato", 1);
                }
                else{
                    sysmessage(v.description, 0);
                    if(window.console){console.log(v.description)}
                }
            }
            catch(e){
                sysmessage(e.message, 0);
                if(window.console){console.log(d)}
            }
        }
    );
}
function editorundo(){
    var h=objmirror.doc.historySize();
    if(h.undo>0){
        objmirror.doc.undo();
    }
}
function editorredo(){
    var h=objmirror.doc.historySize();
    if(h.redo>0){
        objmirror.doc.redo();
    }
}
function bodyresize(){
    var w=$("body").width()-50;
    if(w<700)
        w=700;
    $("#codescript").width(w);
}
function buildpath(menuid){
    var path=objfamily.getpath(menuid).join("/");
    path+="/";
    return path; 
}
function setcurrentfolder(id){
    currdirid=id;
}
function family_newfile(){
    $.post(
        "../rymirror/mirror_oper.php", 
        {
            "env":currenv,
            "path":menupath,
            "sessionid":_sessioninfo.sessionid,
            "action":"newfile"
        },
        function(d){
            try{
                var v=$.parseJSON(d);
                if(v.success){
                    loadbranch(menuid);
                }
                else{
                    alert(v.description);
                }
            }
            catch(e){
                if(window.console){console.log(d)}
            }
            menuid="";
            menutype="";
            menupath="";
            menuclip="";
            menutitle="";
        }
    );
}
function family_newfolder(){
    $.post(
        "../rymirror/mirror_oper.php", 
        {
            "env":currenv,
            "path":menupath,
            "sessionid":_sessioninfo.sessionid,
            "action":"newfolder"
        },
        function(d){
            try{
                var v=$.parseJSON(d);
                if(v.success){
                    loadbranch(menuid);
                }
                else{
                    alert(v.description);
                }
            }
            catch(e){
                if(window.console){console.log(d)}
            }
            menuid="";
            menutype="";
            menupath="";
            menuclip="";
            menutitle="";
        }
    );
}
function family_rename(){
    var newname=prompt("Nuovo nome:", menutitle);
    if(newname){
        $.post(
            "../rymirror/mirror_oper.php", 
            {
                "env":currenv,
                "path":menupath,
                "newname":newname,
                "sessionid":_sessioninfo.sessionid,
                "action":"rename"
            },
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success){
                        if(menutype=="folder"){
                            $("#family_"+menuid).prop("info", newname);
                            $("#family_"+menuid+"_text").html(newname);
                            if($("#family_"+menuid).hasClass("collapsable")){
                                loadbranch(menuid);
                            }
                        }
                        else{
                            var p=menupath.lastIndexOf("/");
                            var h=menupath.substring(0, p+1)+newname;
                            h="javascript:editorload("+$.stringify(h)+")";
                            $("#family_"+menuid+"_text a")
                                .attr("href", h)
                                .attr("title", newname)
                                .html(newname);
                        }
                    }
                    else{
                        alert(v.description);
                    }
                }
                catch(e){
                    if(window.console){console.log(d)}
                }
                menuid="";
                menutype="";
                menupath="";
                menuclip="";
                menutitle="";
            }
        );
    }
}
function family_saveas(){
    var newname=prompt("Salva con nome:", menutitle);
    if(newname){
        $.post(
            "../rymirror/mirror_oper.php", 
            {
                "env":currenv,
                "path":menupath,
                "newname":newname,
                "sessionid":_sessioninfo.sessionid,
                "action":"saveas"
            },
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success){
                        parid=$("#family_"+menuid+"_text").attr("super");
                        loadbranch(parid);
                    }
                    else{
                        alert(v.description);
                    }
                }
                catch(e){
                    if(window.console){console.log(d)}
                }
                menuid="";
                menutype="";
                menupath="";
                menuclip="";
                menutitle="";
            }
        );
    }
}
function family_paste(){
    var target;
    var parid=$("#family_"+menuid+"_text").attr("super");
    if(menutype=="folder")
        target=menupath;
    else
        target=buildpath(menuid);
    $.post(
        "../rymirror/mirror_oper.php", 
        {
            "env":currenv,
            "path":menuclip,
            "target":target,
            "sessionid":_sessioninfo.sessionid,
            "action":"copy"
        },
        function(d){
            try{
                var v=$.parseJSON(d);
                if(v.success){
                    if(menutype=="folder")
                        loadbranch(menuid);
                    else
                        loadbranch(parid);
                }
                else{
                    alert(v.description);
                }
            }
            catch(e){
                if(window.console){console.log(d)}
            }
            menuid="";
            menutype="";
            menupath="";
            menuclip="";
            menutitle="";
        }
    );
}
function family_download(){
    try{
        var h="../rymirror/mirror_download.php?sessionid="+_sessioninfo.sessionid+"&env="+currenv+"&file="+menupath;
        $("#winz-iframe").prop("src", h);
    }
    catch(e){
        if(window.console){console.log(e.message)}
    }
}
function family_delete(){
    var ok;
    if(menutype=="folder")
        ok=confirm("Eliminare la directory '"+menupath+"'?")
    else
        ok=confirm("Eliminare il file '"+menupath+"'?")
    if(ok){
        $.post(
            "../rymirror/mirror_oper.php", 
            {
                "env":currenv,
                "path":menupath,
                "sessionid":_sessioninfo.sessionid,
                "action":"delete"
            },
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success){
                        parid=$("#family_"+menuid+"_text").attr("super");
                        loadbranch(parid);
                    }
                    else{
                        alert(v.description);
                    }
                }
                catch(e){
                    if(window.console){console.log(d)}
                }
                menuid="";
                menutype="";
                menupath="";
                menuclip="";
                menutitle="";
            }
        );
    }
}
function mirrorlogout(){
    RYEGO.logout();
}
// MESSAGGISTICA
var hmesstimer="";
function syswaiting(){
    if(hmesstimer!=""){
        clearInterval(hmesstimer);
        hmesstimer="";
    }
	$("#messbar").html("<img src='images/progress.gif'>").show();
}
function sysmessage(t,s){
    if(hmesstimer!=""){
        clearInterval(hmesstimer);
        hmesstimer="";
    }
	var c="red";
	if(s==1)
		c="green";
	$("#messbar").html(t).css({color:c}).show();
	hmesstimer=setTimeout("sysmessagehide()",4000);
}
function sysmessagehide(){
    hmesstimer="";
	$("#messbar").html("").hide("slow");
}
</script>

</head>

<body onresize="bodyresize()">

<div class="mirror-conteiner">

<div class="mirror-title">RY-MIRROR</div>
<div>MANUTENZIONE SCRIPT</div>
<br/>

<div style="border:1px solid silver;background-color:#F0F0F0;">
&nbsp;<a class="mirror-button" id="button-navigator" href="javascript:activation('navigator')">Navigatore</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="mirror-button button-disabled" id="button-editor" href="javascript:activation('editor')">Editor</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="mirror-button" id="button-logout" href="javascript:mirrorlogout()">Logout</a>
</div>

<!-- INIZIO NAVIGATOR -->
<div class="mirror-tab" id="tab-navigator">

    <div class="mirror-tabtitle">NAVIGATORE</div>

    <div id="family"></div>
    
    <div id="uploader"></div>
    
</div>
<!-- FINE EDITOR -->


<!-- INIZIO EDITOR -->
<div class="mirror-tab" id="tab-editor">

    <div class="mirror-tabtitle">EDITOR</div>

    <a class="mirror-button" id="button-undo" href="javascript:editorundo()" style="font-size:18px;">⤾</a>&nbsp;&nbsp;&nbsp;
    <a class="mirror-button" id="button-redo" href="javascript:editorredo()" style="font-size:18px;">⤿</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a class="mirror-button" id="button-save" href="javascript:editorsave()">Salva</a>&nbsp;&nbsp;&nbsp;<span id="editor-pathfile" style="font-size:12px;"></span>
    
    <br/><br/>
    
    <div id="codescript"></div>
    
    <br/><br/>

    <div id="messbar" style="display:none;white-space:nowrap;font-size:16px;"></div>
    
    <br/><br/>
    
</div>
<!-- FINE EDITOR -->

</div>

<div id='familymenu' class='contextMenu'>
<ul>
<li id='family_newfile'>Nuovo file</li>
<li id='family_newfolder'>Crea cartella</li>
<li id='family_rename'>Rinomina...</li>
<li id='family_saveas'>Salva come...</li>
<li id='family_copy'>Copia</li>
<li id='family_paste'>Incolla</li>
<li id='family_download'>Download</li>
<li class="contextSeparator"></li>
<li id='family_delete'>Elimina</li>
</ul>
</div>

<iframe id="winz-iframe"></iframe>

</body>
</html>
