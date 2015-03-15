<?php 
/****************************************************************************
* Name:            rymirror.php                                             *
* Project:         Cambusa/ryMirror                                         *
* Version:         1.69                                                     *
* Description:     Code Editor                                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include("../sysconfig.php");

// DETERMINAZIONE DELLA SESSIONE
if(isset($_GET["sessionid"])){
    $sessionid=$_GET["sessionid"];
    $egomethod="GET";
}
elseif(isset($_POST["sessionid"])){
    $sessionid=$_POST["sessionid"];
    $egomethod="POST";
}
else{
    $sessionid="";
    $egomethod="POST";
}

$direnvirons=$path_databases."_environs/";

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<title>Mirror - Manutenzione Script</title>
</head>

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
.mirror-currfolder{border:1px dashed silver;}

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

<link type='text/css' href='../rybox/rybox.css' rel='stylesheet' />
<link type='text/css' href='../ryque/ryque.css' rel='stylesheet' />

<script type='text/javascript' src="../jquery/jquery.js"></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.datepicker.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.contextmenu.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.widget.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.button.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.draggable.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mousewheel.js'></script>
<script type='text/javascript' src='../jquery/jquery.cookie.js' ></script>
<script type='text/javascript' src='../rygeneral/rygeneral.js' ></script>
<script type='text/javascript' src='../ryego/ryego.js' ></script>
<script type='text/javascript' src='../rybox/rybox.js' ></script>
<script type='text/javascript' src='../ryque/ryque.js' ></script>

<style>
.ry-contextMenu{font-family:verdana;font-size:12px;}
</style>

<link type='text/css' href='../jqtreeview/jquery.treeview.ry.css' rel='stylesheet' />
<script type='text/javascript' src='../jqtreeview/jquery.treeview.ry.js' ></script>
<script language='javascript'>
    _cambusaURL='<?php print $url_cambusa ?>';
    _customizeURL='<?php print $url_customize ?>';
</script>
<script type='text/javascript' src='../ryfamily/ryfamily.js' ></script>

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

<link type='text/css' href='../ryupload/fileuploader.ry.css' rel='stylesheet'/>
<script type='text/javascript' src='../ryupload/fileuploader.ry.js'></script>
<script type='text/javascript' src='../ryupload/ryupload.js'></script>

<script>
_sessionid="<?php  print $sessionid ?>";
var _sessioninfo;

var progrid=0;
var objfamily;
var objmirror;
var currenv="";
var currpath="";
var currdirid="";

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
        objfamily=$("#family").ryfamily({left:0, top:60, width:500, height:500, scroll:0, border:1});
    
        objfamily.addfolder({id:"k0", title:_sessioninfo.envdescr, open:true});
        setcurrentfolder("k0");
        $.post(
            "../rymirror/mirror_files.php", 
            {
                "env":currenv,
                "sub":"",
                "sessionid":_sessioninfo.sessionid
            },
            function(d){
                var v=$.parseJSON(d);
                var i,nf,tp,p,h;
                if(v.success==1){
                    var i,nf,tp,h;
                    var p=v.path;
                    for(var i in v.content){
                        nf=v.content[i].name;
                        tp=v.content[i].type;
                        progrid+=1;
                        if(tp=="folder"){
                            objfamily.addfolder({parent:"k0", id:"k"+progrid, info:nf, title:nf});
                        }
                        else{
                            h="javascript:editorload("+_stringify(p+nf)+")";
                            objfamily.additem({parent:"k0", id:"k"+progrid, title:"<a href='"+h+"' class='anchor_ryfamily' title='"+nf+"'>"+nf+"</a>"});
                        }
                    }
                }
            }
        );
        
        $("#family").bind("click",
            function(evt){
                if(evt.target.className.indexOf("folder")>=0 || 
                    evt.target.className.indexOf("hitarea")>=0){
                    var id=$(evt.target).attr("rif");
                    if($("#family_"+id).hasClass("collapsable")){ // Il nodo si apre: refresh
                        loadbranch(id);
                    }
                }
            }
        );
        
        $("#uploader").ryupload({
            left:530,
            top:60,
            width:300,
            environ:_tempenviron,
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
                    if(e.target.className.indexOf("folder")>=0 || 
                        e.target.className.indexOf("hitarea")>=0){
                        menuid=$(e.target).attr("rif");
                        menutype="folder";
                        menupath=buildpath(menuid);
                        menutitle=$(e.target).html();
                        return true;
                    }
                    else if(e.target.className.indexOf("anchor")>=0 || 
                        e.target.className.indexOf("hitarea")>=0){
                        menuid=$(e.target).parent().attr("rif");
                        menutype="file";
                        menupath=$(e.target).attr("href").replace(/^javascript:editorload\("/, "").replace(/"\)/, "");
                        menutitle=$(e.target).attr("title");
                        return true;
                    }
                    else{
                        return false;
                    }
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
    objfamily.remove(id);
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
                var v=$.parseJSON(d);
                if(v.success==1){
                    var i,nf,tp,h;
                    var p=v.path;
                    setcurrentfolder(id);
                    for(i in v.content){
                        nf=v.content[i].name;
                        tp=v.content[i].type;
                        progrid+=1;
                        if(tp=="folder"){
                            objfamily.addfolder({parent:id, id:"k"+progrid, info:nf, title:nf});
                        }
                        else{
                            h="javascript:editorload("+_stringify(p+nf)+")";
                            objfamily.additem({parent:id, id:"k"+progrid, title:"<a href='"+h+"' class='anchor_ryfamily' title='"+nf+"'>"+nf+"</a>"});
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
function buildpath(parid){
    try{
        var path="";
        while(parid.substr(0,1)=="k"){
            if(parid!="k0")
                path=$("#family_"+parid+"_text").attr("info")+"/"+path;
            parid=$("#family_"+parid+"_text").attr("super");
        }
        return path; 
    }
    catch(e){
        return "";
    }
}
function setcurrentfolder(id){
    currdirid=id;
    $(".folder").removeClass("mirror-currfolder");
    $("#family_"+id+"_text").addClass("mirror-currfolder");
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
                            $("#family_"+menuid+"_text")
                                .attr("info", newname)
                                .html(newname);
                            if($("#family_"+menuid).hasClass("collapsable")){
                                loadbranch(menuid);
                            }
                        }
                        else{
                            var p=menupath.lastIndexOf("/");
                            var h=menupath.substring(0, p+1)+newname;
                            h="javascript:editorload("+_stringify(h)+")";
                            $("#family_"+menuid+"_text a")
                                .attr("href", newname)
                                .html("<a href='"+h+"' class='anchor_ryfamily' title='"+newname+"'>"+newname+"</a>");
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
        target=buildpath(parid);
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

<body onresize="bodyresize()">

<div class="mirror-conteiner">

<div class="mirror-title">MANUTENZIONE SCRIPT</div>
<div>ryMirror &copy; 2015 Rodolfo Calzetti - Licenza GNU LGPL</div>
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

<div id='familymenu' style='position:absolute;visibility:hidden;'>
<ul>
<li class='ry-contextMenu' id='family_newfile'>Nuovo file</li>
<li class='ry-contextMenu' id='family_newfolder'>Crea cartella</li>
<li class='ry-contextMenu' id='family_rename'>Rinomina...</li>
<li class='ry-contextMenu' id='family_saveas'>Salva come...</li>
<li class='ry-contextMenu' id='family_copy'>Copia</li>
<li class='ry-contextMenu' id='family_paste'>Incolla</li>
<li class='ry-contextMenu' id='family_download'>Download</li>
<li class='ry-contextMenu' ><hr/></li>
<li class='ry-contextMenu' id='family_delete'>Elimina</li>
</ul>
</div>

<iframe id="winz-iframe"></iframe>

</body>
</html>
