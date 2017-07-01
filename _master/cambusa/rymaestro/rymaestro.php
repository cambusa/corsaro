<?php 
/****************************************************************************
* Name:            rymaestro.php                                            *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.69                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../sysconfig.php";
include_once "../rygeneral/json_loader.php";
include_once $path_applications."cacheversion.php";

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
$dirmaestro=$path_databases."_maestro/";
$direnvirons=$path_databases."_environs/";
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="ryMaestro - Database Management" />
<meta name="framework" content="Cambusa <?php print $cambusa_version ?>" />
<meta name="license" content="GNU LGPL v3" />
<meta name="repository" content="https://github.com/cambusa/" />
<title>Maestro - Modellazione database</title>

<style>
.maestro-conteiner{position:relative;width:100%;height:100%;display:none;}
.maestro-tab{position:absolute;top:100px;left:20px;display:none;}
.maestro-title{font-size:18px;height:25px;}
.maestro-tabtitle{font-size:18px;height:40px;}
.maestro-button{font-size:12px}
.maestro-label{font-size:12px}
.maestro-selected{font-weight:bold;}
.maestro-list{width:150px;}
.maestro-count{width:15px;}
.maestro-nocollat{color:red;background-color:#EEE;}
</style>

<style>
body{font-family:verdana,sans-serif; font-size:10px;}
table{font-family:verdana,sans-serif; font-size:10px;border-collapse:collapse;}
td, th{padding-left:5px;padding-right:5px;width:80px;overflow:hidden;}
th{text-align:left;}
a{text-decoration:none;color:maroon;}
a.disabled{text-decoration:none;color:gray;cursor:default;}
.tabname{font-size:14px;}
.dx{text-align:right;}
.sx{text-align:left;}
</style>

<link type='text/css' href='../ryque/ryque.css' rel='stylesheet' />
<style>
a{text-decoration:none;}
.contextMenu{position:absolute;display:none;}
.contextMenu>ul>li{font-family:verdana;font-size:12px;text-align:left;}
.contextMenu>ul>li>a{color:black;}
.contextMenu>ul>li>a:focus{outline:1px dotted;color:black;}
.contextDisabled>a{color:silver !important;}
</style>

<script type='text/javascript' src='../jquery/jquery.js'></script>
<script type='text/javascript' src='../jquery/jquery.cookie.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.widget.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.draggable.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mousewheel.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.position.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.contextmenu.ry.js' ></script>
<script type='text/javascript' src='../rygeneral/rygeneral.js?ver=<?php print $cacheversion ?>' ></script>
<script type='text/javascript' src='../ryego/ryego.js?ver=<?php print $cacheversion ?>' ></script>
<script type='text/javascript' src='../ryque/ryunbound.js?ver=<?php print $cacheversion ?>' ></script>

<script>
_sessioninfo.sessionid="<?php  print $sessionid ?>";
_systeminfo.relative.root="<?php print $relative_base ?>";
_systeminfo.relative.apps=_systeminfo.relative.root+"apps/";
_systeminfo.relative.cambusa=_systeminfo.relative.root+"cambusa/";
_systeminfo.relative.customize=_systeminfo.relative.root+"customize/";

var envjson=false;
var dbprovider="";
var objresult;
$(document).ready(function(){
    activation('dummy');
    resizebody();
    RYEGO.go({
        crossdomain:"",
        appname:"maestro",
        apptitle:"Maestro",
        config:function(d){
            _sessioninfo=d;
            $(".maestro-conteiner").show();
            activation("report");
            if(_firstjson!=""){
                document.getElementById("list-json").selectedIndex=0;
                makereport(false);
                loadenvirons();
                // GRID VUOTO DEI RISULTATI
                objresult=$("#maestro-result").ryunbound({
                    left:0,
                    top:30,
                    width:$("body").width()-50,
                    height:350,
                    maxwidth:-1,
                    numbered:true,
                    checkable:false,
                    sortable:true,
                    columns:[
                        {id:"DUMMY", caption:"(empty)", width:120, type:""}
                    ]
                });
                $("#explode-row")
                .width(600)
                .height(400)
                .focusout(function(){
                    $("#explode-row").hide();
                })
                .keypress(function(k){
                    if(k.keyCode==27)
                        $("#explode-row").hide();
                });
            }
        }
    });
});
function activation(id){
    $("#tab-report").hide();
    $("#button-report").removeClass("maestro-selected");
    $("#tab-collation").hide();
    $("#button-collation").removeClass("maestro-selected");
    $("#tab-upgrade").hide();
    $("#button-upgrade").removeClass("maestro-selected");
    $("#tab-sql").hide();
    $("#button-sql").removeClass("maestro-selected");
    
    $("#tab-"+id).show();
    $("#button-"+id).addClass("maestro-selected");
}
function maestrochange(){
    makereport(false);
    loadenvirons();
}
function makereport(collat,missing){
    $("#contents-report").html("");
    var docjson=$("#list-json").val();
    docjson=docjson+".json";
    if(envjson==false){
        collat=false
    }
    $.post(
        "../rygeneral/json_remote.php", 
        {base:"<?php print $dirmaestro ?>", json:docjson},
        function(d){
            var infobase=$.parseJSON(d);
            var attributes=["key", "type", "size", "unique", "notnull", "default", "ref", "label", "code"];
            var alignes=["dx", "sx", "dx", "dx", "dx", "dx", "sx", "sx", "sx"];
            var t="",i;
            for(var tablekey in infobase){
                table=infobase[tablekey];
                tablekey=tablekey.toUpperCase();
                if(table.type=="database"){
                    t+="<div class='tabname'>"+tablekey+"</div>";
                    t+="<table border='1' cellpadding='0' cellspacing='0'>";

                    t+="<tr>";
                    t+="<th class='sx'>name</th>";
                    for(i=0; i<attributes.length; i++)
                        t+="<th class='"+alignes[i]+"'>"+attributes[i]+"</th>";
                    t+="</tr>";

                    for(var fieldkey in table.fields){
                        var field=table.fields[fieldkey];
                        fieldkey=fieldkey.toUpperCase();
                        t+="<tr>";
                        t+="<td>"+fieldkey+"</td>";
                        var k=false;
                        for(i=0; i<attributes.length; i++){
                            t+="<td class='"+alignes[i]+"'>";
                            var f=false;
                            if(field[attributes[i]]!=missing){
                                switch(attributes[i]){
                                    case "key":
                                        k=true;
                                    case "unique":
                                    case "notnull":
                                        b=true;
                                        if(field[attributes[i]])
                                            t+="&#x2714;";
                                            f=true;
                                        break;
                                    default:
                                        t+=field[attributes[i]];
                                }
                            }
                            switch(attributes[i]){
                                case "key":
                                case "unique":
                                case "notnull":
                                    if(f==false && k==true){
                                        t+="&#x2714;";
                                    }
                            }
                            t+="</td>";
                        }
                        if(collat){
                            try{
                                var fdb=envjson[tablekey].fields[fieldkey];

                                if(fdb==missing){
                                    t+="<td class='maestro-nocollat'>DOESN'T EXIST</td>";
                                }
                                else if(field.type=="JSON"){
                                    if(fdb.type=="VARCHAR" && field.size!=fdb.size){
                                        t+="<td class='maestro-nocollat'>"+fdb.type+"("+fdb.size+")"+"</td>";
                                    }
                                    else if(fdb.type=="TEXT" && $.isset(fdb.size)){
                                        t+="<td class='maestro-nocollat'>"+fdb.type+"("+fdb.size+")"+"</td>";
                                    }
                                }
                                else if(field.type!="SYSID" && (field.type!=fdb.type || field.size!=fdb.size)){
                                    if(fdb.size==missing)
                                        t+="<td class='maestro-nocollat'>"+fdb.type+"</td>";
                                    else
                                        t+="<td class='maestro-nocollat'>"+fdb.type+"("+fdb.size+")"+"</td>";
                                }
                            }
                            catch(e){
                                t+="<td class='maestro-nocollat'>DOESN'T EXIST</td>";
                            }
                        }
                        t+="</tr>";
                    }
                    t+="</table>";
                    t+="<br>";
                }
            }
            $("#contents-report").html(t);
        }
    );
}
function loadenvirons(){
    $("#list-environs").html("");
    $("#contents-report").html("");
    var docjson=$("#list-json").val();
    docjson=docjson+".json";
    $.post(
        "maestro_environs.php", 
        {maestro:docjson},
        function(d){
            var v=$.parseJSON(d);
            for(var i in v){
                $("#list-environs").append("<option>"+v[i]+"</option>")
            }
        }
    );
}
function envanalyze(){
    $("#action-collation").addClass('disabled');
    envjson=false;
    dbprovider="";
    var env=$("#list-environs").val();
    if(env!=""){
        $.post(
            "maestro_analyze_test.php", 
            {"sessionid":_sessioninfo.sessionid, "env":env},
            function(d){
                try{
                    envjson=$.parseJSON(d);
                    dbprovider=envjson["__INFOS"]["provider"];
                    makereport(true);
                }
                catch(e){
                    alert(e.message);
                    envjson=false;
                }
                $("#action-collation").removeClass('disabled');
            }
        );
    }
}
function envcollation(){
    if ($('action-collation').hasClass('disabled')){
        return false;
    }
    else{
        envanalyze();
    }
}
function envupgrade(){
    $("#upgrade-message").html("Aggiornamento in corso...");
    var envname=$("#list-upgrade").val();
    var logonly=($("input[name=chklogonly]").is(':checked')).booleanNumber();
    $("#engage-upgrade").addClass('disabled');
    $.post(
        "maestro_upgrade.php", 
        {"sessionid":_sessioninfo.sessionid, "env":envname, "logonly":logonly},
        function(d){
            try{
                d=d.replace(/^ +/g, "");
                var v=$.parseJSON(d);
                if(v.success){
                    $("#upgrade-message").html(v.description);
                    setTimeout("blankmessage()",5000);
                }
                else{
                    $("#upgrade-message").html("Errore:"+v.description);
                }
            }
            catch(e){
                $("#upgrade-message").html("Errore:"+d);
            }
            $("#engage-upgrade").removeClass('disabled');
        }
    );
}
function blankmessage(){
    $("#upgrade-message").html("");
}
function executesql(){
    var env=$("#list-sql").val();
    var sql=$("#maestro-sql").val();
    var res="";
    objresult.pulsating(true);
    $.post(
        "maestro_execute.php", 
        {"sessionid":_sessioninfo.sessionid, "env":env, "sql":sql},
        function(d){
            objresult.pulsating(false);
            objresult=null;
            try{
                var v=$.parseJSON(d);
                $("#maestro-result").html("");
                if(v.length>0){
                    var cols=[];
                    for(var i in v[0]){
                        cols.push({id:i, caption:i, width:120, type:""});
                    }
                    objresult=$("#maestro-result").ryunbound({
                        left:0,
                        top:30,
                        width:$("body").width()-50,
                        height:350,
                        numbered:true,
                        checkable:false,
                        sortable:true,
                        columns:cols,
                        enter:function(){
                            var r,c,h;
                            $("#explode-row").val("");
                            r=objresult.index();
                            h="";
                            for(c in v[r-1]){
                                h+=c+" = "+v[r-1][c]+"\n";
                            }
                            $("#explode-row").val(h);
                            $("#explode-row").show();
                            $("#explode-row").focus();
                        }
                    });
                    objresult.setmatrix(v);
                    objresult.autofit();
                    $("#maestro-result").contextMenu("popup", {
                        menuStyle:{
                            width:"200px"
                        },
                        bindings: {
                            'explode': function(t) {
                                var r,c,h;
                                $("#explode-row").val("");
                                r=objresult.index();
                                h="";
                                for(c in v[r-1]){
                                    h+=c+" = "+v[r-1][c]+"\n";
                                }
                                $("#explode-row").val(h);
                                $("#explode-row").show();
                                $("#explode-row").focus();
                            }
                        },
                        onContextMenu:
                            function(e) {
                            
                                return true;
                            },
                        onShowMenu: 
                            function(e, menu) {
                            
                                return menu;
                            }
                    });
                }
                else{
                    objresult=$("#maestro-result").ryunbound({
                        left:0,
                        top:30,
                        width:$("body").width()-50,
                        height:350,
                        numbered:true,
                        checkable:false,
                        sortable:true,
                        columns:[
                            {id:"DUMMY", caption:"(empty)", width:120, type:""}
                        ]
                    });
                }
            }
            catch(e){
                if(window.console){console.log(d)}
                objresult=$("#maestro-result").ryunbound({
                    left:0,
                    top:30,
                    width:$("body").width()-50,
                    height:350,
                    numbered:true,
                    checkable:false,
                    sortable:true,
                    columns:[
                        {id:"MESSAGE", caption:"MESSAGE", width:120, type:""},
                        {id:"RESULT", caption:"RESULT", width:120, type:""}
                    ]
                });
                objresult.setmatrix([{"MESSAGE":e.message, "RESULT":d}]);
                objresult.autofit();
            }
        }
    );
}
function resizebody(){
    w=$("body").width()-50;
    if(w<500)
        w=500;
    $("#maestro-sql").width(w);
    if(objresult)
        objresult.move({"width":w});
}
</script>

</head>

<body onresize="resizebody()" spellcheck="false">

<div class="maestro-conteiner">

<div class="maestro-title">RY-MAESTRO</div>
<div>MODELLAZIONE E MANUTENZIONE DATABASE</div>
<br/>

<div style="border:1px solid silver;background-color:#F0F0F0;">
&nbsp;<a class="maestro-button" id="button-report" href="javascript:activation('report')">Report</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="maestro-button" id="button-upgrade" href="javascript:activation('upgrade')">Upgrade</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="maestro-button" id="button-sql" href="javascript:activation('sql')">SQL</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="maestro-button" id="button-logout" href="javascript:RYEGO.logout()">Logout</a>
</div>

<!-- INIZIO REPORT -->
<div class="maestro-tab" id="tab-report">

<div class="maestro-tabtitle">REPORT</div>

<a class="maestro-button" id="action-report" href="javascript:makereport(false)">Aggiorna</a>&nbsp;&nbsp;&nbsp;
<select id="list-json" class="maestro-list" onchange="maestrochange()">
<?php
$firstjson="";
$m=glob($dirmaestro."*.json");
for($i=0;$i<count($m);$i++){
    $b=basename($m[$i]);
    $b=substr($b,0,strlen($b)-5);
    if($i==0){
        $firstjson=$b;
        print "<option selected='selected'>$b</option>";
    }
    else{
        print "<option>$b</option>";
    }
}
?>
</select>
<script>
var _firstjson="<?php  print $firstjson ?>";
</script>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="maestro-button" id="action-collation" href="javascript:envcollation()">Confronta ambiente</a>&nbsp;&nbsp;&nbsp;
<select id="list-environs" class="maestro-list">
</select>
<br/>
<br/>
<br/>
<div id="contents-report"></div>
<br/>
<br/>
<br/>
</div>
<!-- FINE REPORT -->

<!-- INIZIO AGGIORNAMENTO -->
<div class="maestro-tab" id="tab-upgrade">

<div class="maestro-tabtitle">AGGIORNAMENTO</div>

<span class="maestro-label">Ambiente da creare/aggiornare</span>&nbsp;&nbsp;&nbsp;
<select id="list-upgrade" class="maestro-list">
<?php
$n=-1;
$m=glob($direnvirons."*.php");
for($i=0;$i<count($m);$i++){
    $b=basename($m[$i]);
    $b=substr($b,0,strlen($b)-4);
    $env_maestro="";
    $env_provider="";
    include($m[$i]);
    if(strpos("|sqlite|access|mysql|oracle|sqlserver|mssql|db2odbc|", "|".$env_provider."|")!==false){
        if($env_maestro!=""){
            $n+=1;
            if($n==0)
                print "<option selected='selected'>$b</option>";
            else
                print "<option>$b</option>";
        }
    }
}
?>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span class="maestro-label">Solo log&nbsp;&nbsp;</span><input type="checkbox" name="chklogonly" value="0">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="maestro-button" id="engage-upgrade" href="javascript:envupgrade()">Create/Update</a>
<br/>
<br/>
<br/>
<br/>
<span id="upgrade-message" class="maestro-label" onclick="blankmessage()"></span>
</div>
<!-- FINE AGGIORNAMENTO -->

<!-- INIZIO SQL -->
<div class="maestro-tab" id="tab-sql">

<div class="maestro-tabtitle">SQL</div>
<span class="maestro-label">Ambiente</span>&nbsp;&nbsp;&nbsp;
<select id="list-sql" class="maestro-list">
<?php
$n=-1;
$m=glob($direnvirons."*.php");
for($i=0;$i<count($m);$i++){
    $b=basename($m[$i]);
    $b=substr($b,0,strlen($b)-4);
    $env_maestro="";
    $env_provider="";
    include($m[$i]);
    if(strpos("|sqlite|access|mysql|oracle|sqlserver|mssql|db2odbc|", "|".$env_provider."|")!==false){
        $n+=1;
        if($n==0)
            print "<option selected='selected'>$b</option>";
        else
            print "<option>$b</option>";
    }
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<a class="maestro-button" id="engage-sql" href="javascript:executesql()">Esegui</a>
<br/>
<br/>
<textarea id="maestro-sql" style="width:600;height:100;border:1px solid silver;"></textarea>
<br/>
<br/>
<div style="position:relative;">
<div id="maestro-result"></div>
</div>
</div>
<!-- FINE SQL -->

</div>

<div id='popup' class='contextMenu'>
    <ul>
        <li id='explode'><a href='javascript:'>Explode</a></li>
    </ul>
</div>

<textarea id="explode-row" style="position:absolute;width:600;height:400;border:1px solid silver;display:none;"></textarea>

</body>
</html>
