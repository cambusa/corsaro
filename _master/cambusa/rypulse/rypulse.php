<?php 
/****************************************************************************
* Name:            rypulse.php                                              *
* Project:         Cambusa/ryPulse                                          *
* Version:         1.69                                                     *
* Description:     Scheduler                                                *
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

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="ryPulse - Scheduling Management" />
<meta name="framework" content="Cambusa <?php print $cambusa_version ?>" />
<meta name="license" content="GNU LGPL v3" />
<meta name="repository" content="https://github.com/cambusa/" />
<title>Pulse - Gestore di azioni schedulate</title>

<style>
.pulse-conteiner{position:relative;display:none;}
.pulse-tab{position:absolute;top:100px;left:20px;display:none;}
.pulse-title{font-size:18px;height:25px;}
.pulse-tabtitle{font-size:18px;height:40px;}
.pulse-button{font-size:12px}
.pulse-label{font-size:12px}
.pulse-selected{font-weight:bold;}
.pulse-list{width:150px;}
.pulse-count{width:15px;}
.pulse-result , td{white-space:nowrap}
</style>

<style>
body{font-family:verdana,sans-serif; font-size:10px;}
table{font-family:verdana,sans-serif; font-size:10px;border-collapse:collapse;}
td, th{padding-left:5px;padding-right:5px;width:80px;overflow:hidden;}
th{text-align:left;}
a{text-decoration:none;color:maroon;}
.tabname{font-size:14px;}
.dx{text-align:right;}
.sx{text-align:left;}
</style>

<link type='text/css' href='../rybox/rybox.css?ver=<?php print $cacheversion ?>' rel='stylesheet' />
<link type='text/css' href='../ryque/ryque.css?ver=<?php print $cacheversion ?>' rel='stylesheet' />

<script type='text/javascript' src="../jquery/jquery.js"></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.datepicker.ry.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.contextmenu.ry.js?ver=<?php print $cacheversion ?>' ></script>
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

<script>
_sessioninfo.sessionid="<?php  print $sessionid ?>";
_systeminfo.relative.root="<?php print $relative_base ?>";
_systeminfo.relative.apps=_systeminfo.relative.root+"apps/";
_systeminfo.relative.cambusa=_systeminfo.relative.root+"cambusa/";
_systeminfo.relative.customize=_systeminfo.relative.root+"customize/";

var objpulseenabled;
var objstatus;
var objmonitor;
var handletimer=0;
var runcounter=0;

var sysid="";
var obj_name;
var obj_description;
var obj_engage;
var obj_notify;
var obj_params;
var obj_enabled;
var obj_once;
var obj_tolerance;
var obj_latency;
var obj_minutes;
var obj_hours;
var obj_days;
var obj_week;
var obj_months;
var obj_businessday;

var obj_save;
var obj_remove;
var obj_cast;
var imgwaiting = new Image();
imgwaiting.src="images/progress.gif";
$(document).ready(function(){
    activation('dummy');
    RYEGO.go({
        crossdomain:"",
        appname:"pulse",
        apptitle:"Pulse",
        config:function(d){
            _sessioninfo=d;
            $(".pulse-conteiner").show();
            activation("monitor");
            RYQUE.request({
                environ:"rypulse",
                ready:function(){
                    makemonitor();
                }
            });
        }
    });
});
function activation(id){
    $("#tab-monitor").hide();
    $("#button-monitor").removeClass("pulse-selected");
    
    $("#tab-"+id).show();
    $("#button-"+id).addClass("pulse-selected");
}
function makemonitor(missing){
    $("#lbpulseenabled").rylabel({left:10,top:40,caption:"Abilita schedulazione"});
    objpulseenabled=$("#chkpulseenabled").rycheck({ 
        left:160,
        top:40,
        assigned:function(){
            if(handletimer){
                clearInterval(handletimer);
                handletimer=0;
            }
            if(objpulseenabled.value()){
                handletimer=setInterval("heartfunct()",5000);
                objstatus.caption("Pulse enabled");
            }
            else{
                objstatus.caption("Pulse not enabled");
            }
        }
    });
    objstatus=$("#imgpulse").rylabel({left:550,top:40,caption:"Pulse not enabled"});
    objmonitor=$("#gridmonitor").ryque({
        left:10,
        top:80,
        width:760,
        height:400,
        numbered:false,
        checkable:false,
        environ:"rypulse",
        from:"ENGAGES",
        orderby:"NAME",
        columns:[
            {id:"SYSID",caption:"",width:0},
            {id:"UNATANTUM",caption:"",width:0,type:"?"},
            {id:"NAME",caption:"Nome",width:120},
            {id:"DESCRIPTION",caption:"Descrizione",width:200},
            {id:"LASTENGAGE",caption:"Ultima",width:160,type:":"},
            {id:"NEXTENGAGE",caption:"Prossima",width:160,type:":"},
            {id:"ENABLED",caption:"Abil.",width:40,type:"?"},
            {id:"RUNNING",caption:"Esec.",width:40,type:"?"}
        ],
        changerow:function(o,i){
            if(_sessioninfo.admin){
                sysid="";
                //obj_save.caption("Inserisci");
                obj_remove.visible(0);
                obj_cast.visible(0);
                obj_name.value("");
                obj_description.value("");
                obj_params.value("");
                obj_tolerance.value("");
                obj_latency.value("");
                obj_minutes.value("");
                obj_hours.value("");
                obj_days.value("");
                obj_week.value("");
                obj_months.value("");
                obj_businessday.setkey(0);
                obj_engage.value("");
                obj_notify.value("");
                obj_enabled.value(true);
                obj_once.value(false);
                if(i>0)
                    objmonitor.solveid(i);
            }
        },
        solveid:function(o,d){
            sysid=d;
            //obj_save.caption("Modifica");
            obj_remove.visible(1);
            obj_cast.visible(1);
            RYQUE.query({
                sql:"SELECT * FROM ENGAGES WHERE SYSID='"+sysid+"'",
                ready:function(v){
                    try{
                        obj_name.value(v[0].NAME);
                        obj_description.value(v[0].DESCRIPTION);
                        obj_params.value(v[0].PARAMS);
                        obj_tolerance.value(v[0].TOLERANCE);
                        obj_latency.value(v[0].LATENCY);
                        obj_minutes.value(v[0].MINUTES);
                        obj_hours.value(v[0].HOURS);
                        obj_days.value(v[0].DAYS);
                        obj_week.value(v[0].WEEK);
                        obj_months.value(v[0].MONTHS);
                        obj_businessday.setkey(parseInt(v[0].BUSINESSDAY));
                        obj_engage.value(v[0].ENGAGE);
                        obj_notify.value(v[0].NOTIFY);
                        obj_enabled.value(parseInt(v[0].ENABLED));
                        obj_once.value(parseInt(v[0].UNATANTUM));
                    }
                    catch(e){
                    }
                }
            });
        },
        before:function(o, d){
            if(sysid!=""){
                for(var i in d){
                    if(d[i]["SYSID"]==sysid){
                        var v=__(d[i]["ENABLED"]).actualInteger();
                        var u=__(d[i]["UNATANTUM"]).actualInteger();
                        if(u==1 && v!=obj_enabled.value()){
                            obj_enabled.value(v);
                        }
                        break;
                    }
                }
            }
        }
    });
    // CONSOLE DI AMMINISTRAZIONE
    if(_sessioninfo.admin){
        $("#lb_name").rylabel({                      left:0,  top:10,caption:"Nome",title:"Nome breve"});
        obj_name=$("#tx_name").rytext({              left:80, top:10,width:300,maxlen:30});

        $("#lb_description").rylabel({               left:0,  top:35,caption:"Descrizione",title:"Descrizione lunga"});
        obj_description=$("#tx_description").rytext({left:80, top:35,width:300,maxlen:200});

        $("#lb_engage").rylabel({                    left:0,  top:60,caption:"Script",title:"Script PHP la lanciare"});
        obj_engage=$("#tx_engage").rytext({          left:80, top:60,width:300,maxlen:200});

        $("#lb_notify").rylabel({                    left:0,  top:85,caption:"Notifiche",title:"Elenco degli utenti a cui inviare la notifica"});
        obj_notify=$("#tx_notify").rytext({          left:80, top:85,width:300,maxlen:1000});

        $("#lb_params").rylabel({                    left:0,  top:110,caption:"Parametri",title:"Documento JSON passato allo script\nEsempio: {\"env\":\"acme\"}"});
        obj_params=$("#tx_params").rytext({          left:80, top:110,width:300,maxlen:1000});

        $("#lb_enabled").rylabel({                   left:0,  top:135,caption:"Abilitato",title:"Abilita l'azione schedulata"});
        obj_enabled=$("#tx_enabled").rycheck({       left:80, top:135});
        
        $("#lb_once").rylabel({                      left:280, top:135,caption:"Una tantum",title:"Dopo l'esecuzione l'azione viene disabilitata"});
        obj_once=$("#tx_once").rycheck({             left:362, top:135});
        
        $("#lb_tolerance").rylabel({                 left:0,  top:160,caption:"Tolleranza",title:"Periodo, a partire dall'istante di 'prossima esecuzione', entro il quale l'azione può essere lanciata\nEsempio: 15MINUTES"});
        obj_tolerance=$("#tx_tolerance").rytext({    left:80, top:160,width:300,maxlen:10});

        $("#lb_latency").rylabel({                   left:0,  top:185,caption:"Latenza",title:"Periodo, a partire dall'istante di 'ultima esecuzione', oltre il quale l'azione può essere lanciata\nEsempio: 1HOURS"});
        obj_latency=$("#tx_latency").rytext({        left:80, top:185,width:300,maxlen:10});
        
        $("#lb_minutes").rylabel({                   left:430,top:10,caption:"Minuti",title:"Filtro minuti\nEsempio: 00,08,27"});
        obj_minutes=$("#tx_minutes").rytext({        left:510,top:10,width:250,maxlen:180});

        $("#lb_hours").rylabel({                     left:430,top:35,caption:"Ore",title:"Filtro ore\nEsempio: 00,09,14,22"});
        obj_hours=$("#tx_hours").rytext({            left:510,top:35,width:250,maxlen:1000});

        $("#lb_days").rylabel({                      left:430,top:60,caption:"Giorni",title:"Filtro giorni del mese ('00' indica la fine mese)\nEsempio: 01,15,00"});
        obj_days=$("#tx_days").rytext({              left:510,top:60,width:250,maxlen:100});

        $("#lb_week").rylabel({                      left:430,top:85,caption:"Settimana",title:"Filtro giorni della settimana\nEsempio: MO,TH\nOppure 01,04"});
        obj_week=$("#tx_week").rytext({              left:510,top:85,width:250,maxlen:20});

        $("#lb_months").rylabel({                    left:430,top:110,caption:"Mesi",title:"Filtro mesi\nEsempio: 01:35,06:00,12:15"});
        obj_months=$("#tx_months").rytext({          left:510,top:110,width:250,maxlen:50});

        $("#lb_businessday").rylabel({               left:430,top:135,caption:"Lav./Fest.",title:"Filtro giorni lavorativ/festivi"});
        obj_businessday=$("#tx_businessday").rylist({left:510,top:135,width:250});
        obj_businessday.additem({caption:"Tutti i giorni",key:"0"});
        obj_businessday.additem({caption:"Solo lavorativi",key:"1"});
        obj_businessday.additem({caption:"Solo festivi",key:"2"});

        $("#lb_clear").rylabel({
            left:0,
            top:-20,
            caption:"Nuovo",
            title:"Pulisce i campi predisponendosi all'inserimento",
            button:true,
            click:function(o){
                objmonitor.index(0);
            }
        });
        
        obj_save=$("#lb_save").rylabel({
            left:100,
            top:-20,
            caption:"Salva",
            title:"Inserisce/modifica l'azione schedulata",
            button:true,
            click:function(o){
                syswaiting();
                $.post("pulseaction_engage.php", 
                    {
                        "sessionid":_sessioninfo.sessionid,
                        "SYSID":sysid,
                        "NAME":obj_name.value(), 
                        "DESCRIPTION":obj_description.value(),
                        "PARAMS":obj_params.value(),
                        "TOLERANCE":obj_tolerance.value(),
                        "LATENCY":obj_latency.value(),
                        "MINUTES":obj_minutes.value(),
                        "HOURS":obj_hours.value(),
                        "DAYS":obj_days.value(),
                        "WEEK":obj_week.value(),
                        "MONTHS":obj_months.value(),
                        "BUSINESSDAY":obj_businessday.key(),
                        "ENGAGE":obj_engage.value(),
                        "NOTIFY":obj_notify.value(),
                        "ENABLED":obj_enabled.value(),
                        "UNATANTUM":obj_once.value()
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description,v.success);
                            if(v.success){
                                if(sysid=="")
                                    objmonitor.refresh();
                                else
                                    objmonitor.dataload();
                                heartfunct(0);
                            }
                        }
                        catch(e){
                            sysmessagehide();
                            alert(d);
                        }
                    }
                );
            }
        });

        obj_remove=$("#lb_remove").rylabel({
            left:200,
            top:-20,
            caption:"Elimina",
            title:"Elimina l'azione schedulata",
            button:true,
            click:function(o){
                if(confirm("Eliminare l'azione selezionata?")){
                    syswaiting();
                    $.post("pulseaction_remove.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "SYSID":sysid
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                objmonitor.refresh();
                                sysmessage(v.description,v.success);
                            }
                            catch(e){
                                sysmessagehide();
                                alert(d);
                            }
                        }
                    );
                }
            }
        });
        
        obj_cast=$("#lb_cast").rylabel({
            left:300,
            top:-20,
            caption:"Forza",
            title:"Forza l'esecuzione dello script selezionato",
            button:true,
            click:function(o){
                if(confirm("Forzare l'esecuzione dell'azione selezionata?")){
                    syswaiting();
                    $.post("pulseaction_cast.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "SYSID":sysid
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                sysmessage(v.description,v.success);
                            }
                            catch(e){
                                sysmessagehide();
                                alert(d);
                            }
                        }
                    );
                }
            }
        });
        
        $("#frameadmin").show();
    }
    setTimeout(
        function(){
            objmonitor.where("");
            objmonitor.query({
                ready:function(){
                    setInterval(
                        function(){
                            monrefresh();
                        }, 2000
                    );
                }
            });
        }, 100
    );
}
function heartfunct(exec,missing){
    try{
        if(exec==missing)   // Anche lancio script, altrimenti solo ricalcolo scaduti
            exec=1;
        var dt=new Date();
        objstatus.caption("Last pulse: "+dt.getDate()+"/"+dt.getMonth()+"/"+dt.getFullYear()+" "+("00"+dt.getHours()).subright(2)+":"+("00"+dt.getMinutes()).subright(2)+":"+("00"+dt.getSeconds()).subright(2) );
        runcounter+=1;
        $.post("pulse_heart.php", {"sessionid":_sessioninfo.sessionid, "exec":exec},
            function(d){
                runcounter-=1;
            }
        );
    }
    catch(e){}
}
function monrefresh(){
    try{
        objmonitor.dataload();
    }
    catch(e){}
}
function pulselogout(){
    var ok=true;
    if(runcounter>0){
        ok=confirm("Alcune azioni sono in esecuzione.\nL'uscita comporterà la loro interruzione.\nUscire comunque?");
    }
    if(ok){
        if(_sessioninfo.admin){
            RYQUE.dispose();
            objmonitor.dispose();
        }
        $.post("pulseaction_logout.php", 
            {
                "sessionid":_sessioninfo.sessionid
            }, 
            function(d){}
        );
        $.pause(100);
        RYEGO.logout();
    }
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

<body>

<div class="pulse-conteiner">

<div class="pulse-title">RY-PULSE</div>
<div>GESTORE DI AZIONI SCHEDULATE</div>
<br/>

<div style="border:1px solid silver;background-color:#F0F0F0;">
&nbsp;<a class="pulse-button" id="button-monitor" href="javascript:activation('monitor')">Monitor</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="pulse-button" id="button-logout" href="javascript:pulselogout()">Logout</a>
</div>

<!-- INIZIO MONITOR -->
<div class="pulse-tab" id="tab-monitor">

<div class="pulse-tabtitle">MONITOR</div>

<div id="lbpulseenabled"></div><div id="chkpulseenabled"></div>
<div id="imgpulse"></div>
<div id="gridmonitor"></div>

<div id="frameadmin" style="position:absolute;top:500px;left:10px;height:220px;width:760;display:none;font-size:13px;">

<div id="lb_clear"></div>
<div id="lb_name"></div><div id="tx_name"></div>
<div id="lb_description"></div><div id="tx_description"></div>
<div id="lb_engage"></div><div id="tx_engage"></div>
<div id="lb_notify"></div><div id="tx_notify"></div>
<div id="lb_params"></div><div id="tx_params"></div>
<div id="lb_enabled"></div><div id="tx_enabled"></div><div id="lb_once"></div><div id="tx_once"></div>
<div id="lb_tolerance"></div><div id="tx_tolerance"></div>
<div id="lb_latency"></div><div id="tx_latency"></div>
<div id="lb_minutes"></div><div id="tx_minutes"></div>
<div id="lb_hours"></div><div id="tx_hours"></div>
<div id="lb_days"></div><div id="tx_days"></div>
<div id="lb_week"></div><div id="tx_week"></div>
<div id="lb_months"></div><div id="tx_months"></div>
<div id="lb_businessday"></div><div id="tx_businessday"></div>
<div id="lb_save"></div>
<div id="lb_remove"></div>
<div id="lb_cast"></div>
<div id="messbar" style="display:none;position:absolute;left:0;top:225px;white-space:nowrap;"></div>

</div>

</div>
<!-- FINE MONITOR -->

</div>
</body>
</html>
