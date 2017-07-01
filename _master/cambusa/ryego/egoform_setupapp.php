<?php
/****************************************************************************
* Name:            egoform_setupapp.php                                     *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<script>
var _appname="<?php print $appname ?>";
var _userid="<?php print $userid ?>";
var _aliasid="<?php print $aliasid ?>";
var _appid="<?php print $appid ?>";
var _expiry=<?php print $expiry ?>;
var lstenviron;
var lstrole;
var lstlanguage;
var lstcountry;
var lastenvironid="";
var lastroleid="";
var lastlanguageid="";
var lastcountrycode="";
var lastdebugmode=false;

var objcurrpwd;
var objnewpwd;
var objrepeatpwd;
var objgo;

function activation(n){
    $("#settings").hide();
    $("#changepassword").hide();
    $("#deactivation").hide();
	$("li.ego-menu a").removeClass("ego-menu-selected");
    $("#"+n).show();
    switch(n){
	case "settings":
		$("#side_settings").addClass("ego-menu-selected");
		break;
    case "changepassword":
		$("#side_changepassword").addClass("ego-menu-selected");
        setTimeout(function(){$("#txcurrpwd_anchor").focus()}, 100);
        break;
	case "deactivation":
		$("#side_deactivation").addClass("ego-menu-selected");
		break;
    }
}

// Internet Explorer gestisce l'evento in un modo impossibile
if(!$.browser.msie){
    window.onbeforeunload=function(){
        egoterminate(false);
        $.pause(1000);
    };
}

$(document).ready(function(){
    activation('dummy');
    if(_sessioninfo.sessionid!=""){
        $.post(_systeminfo.relative.cambusa+"ryego/ego_infosession.php",{"sessionid":_sessioninfo.sessionid,"app":_appname}, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success)
                        config();
                    else
                        location.replace("ryego.php");
                }
                catch(e){
                    location.replace("ryego.php");
                }
            }
        );
    }
    else{
        location.replace("ryego.php");
    }
});

function config(missing){
    // BABEL MESSAGES
    $("#lbexpiredpwd").rylabel({caption:"Password scaduta: provvedere al cambio password per accedere all'applicazione"});
    $("#lbexpiringpwd").rylabel({caption:"Password in scadenza"});
    $("#lbside_settings").rylabel({caption:"Opzioni"});
    $("#lbside_changepassword").rylabel({caption:"Cambio password"});
    $("#lbside_deactivation").rylabel({caption:"Disattivazione"});
    $("#lbauthenticationservice").rylabel({caption:"Servizio di autenticazione"});
    $("#lbconfirmdeactivate").rylabel({caption:"Disattivare l'account?\n\nNon sarà più possibile accedere \nse non con l'intervento di un amministratore."});
    
    activation('settings');
    
    // INIZIO FORM OPZIONI
    var offsety=20;

    $("#lbemail").rylabel({left:-50, top:offsety, width:120, caption:"Email", align:"right"});
    txemail=$("#txemail").rytext({
        left:80,
		width:200,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_systeminfo.relative.cambusa+"ryego/egoaction_last.php", 
                {"sessionid":_sessioninfo.sessionid,"appid":_appid,"aliasid":_aliasid,"email":obj.value()}, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
		}
    });

    offsety+=25;
    $("#lbenviron").rylabel({left:-50, top:offsety, width:120, caption:"Ambiente", align:"right"});
    lstenviron=$("#lstenviron").rylist({
        left:80,
		width:200,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_systeminfo.relative.cambusa+"ryego/egoaction_last.php", 
                {"sessionid":_sessioninfo.sessionid,"appid":_appid,"aliasid":_aliasid,"environid":obj.key(obj.value())}, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
		}
    });

    offsety+=25;
    $("#lbrole").rylabel({left:-50, top:offsety, width:120, caption:"Ruolo", align:"right"});
    lstrole=$("#lstrole").rylist({
        left:80,
		width:200,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_systeminfo.relative.cambusa+"ryego/egoaction_last.php", 
                {"sessionid":_sessioninfo.sessionid,"appid":_appid,"aliasid":_aliasid,"roleid":obj.key(obj.value())}, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
		}
    });
    
    offsety+=25;
    $("#lblanguage").rylabel({left:-50, top:offsety, width:120, caption:"Lingua", align:"right"});
    lstlanguage=$("#lstlanguage").rylist({
        left:80,
		width:200,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_systeminfo.relative.cambusa+"ryego/egoaction_last.php", 
                {"sessionid":_sessioninfo.sessionid,"appid":_appid,"aliasid":_aliasid,"languageid":obj.key(obj.value())}, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        // localizzazione form
                        var l=obj.gettag(obj.value());
                        RYBOX.localize(l, missing,
                            function(){
                                postlocalize();
                                $.cookie("_egolanguage", l, { expires : 10000 });
                            }
                        );
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
		}
    });
    
    offsety+=25;
    $("#lbcountry").rylabel({left:-50, top:offsety, width:120, caption:"Paese", align:"right"});
    lstcountry=$("#lstcountry").rylist({
        left:80,
		width:200,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_systeminfo.relative.cambusa+"ryego/egoaction_last.php", 
                {"sessionid":_sessioninfo.sessionid,"appid":_appid,"aliasid":_aliasid,"countrycode":obj.key(obj.value())}, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
		}
    });
    
    offsety+=25;
    $("#lbdebugmode").rylabel({left:-50, top:offsety, width:120, caption:"Modalità", align:"right"});
    lstdebugmode=$("#lstdebugmode").rylist({
        left:80,
		width:200,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_systeminfo.relative.cambusa+"ryego/egoaction_last.php", 
                {"sessionid":_sessioninfo.sessionid,"appid":_appid,"aliasid":_aliasid,"debugmode":obj.key(obj.value())}, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
		}
    });
    
    // FINE FORM OPZIONI

	offsety=20;
    $("#lbcurrpwd").rylabel({left:0, top:offsety, width:120, caption:"Password attuale", align:"right"});
    objcurrpwd=$("#txcurrpwd").rytext({ 
        left:130,
		width:180,
        top:offsety, 
        password:true,
        maxlen:16
    });
	
	offsety+=30;
    $("#lbnewpwd").rylabel({left:0, top:offsety, width:120, caption:"Nuova password", align:"right"});
    objnewpwd=$("#txnewpwd").rytext({ 
        left:130,
		width:180,
        top:offsety, 
        password:true,
        maxlen:16
    });
	
	offsety+=30;
    $("#lbrepeatpwd").rylabel({left:0, top:offsety, width:120, caption:"Ripeti password", align:"right"});
    objrepeatpwd=$("#txrepeatpwd").rytext({ 
        left:130,
		width:180,
        top:offsety, 
        password:true,
        maxlen:16
    });

    // INIZIO AZIONI
    
    // Registra nuova password
	offsety+=40;
    $("#actionPassword").rylabel({
        left:130,
        top:offsety,
		width:170,
        caption:"Conferma",
        button:true,
        flat:false,
        click:function(o){
            var n=objnewpwd.value();
            var ld=0,ul=0;
            if(n.replace(/[0-9]/,"")!=n && n.replace(/[A-Za-z]/,"")!=n)
                ld=1;
            if(n.replace(/[A-Z]/,"")!=n && n.replace(/[a-z]/,"")!=n)
                ul=1;
            syswaiting();
            $.post(_systeminfo.relative.cambusa+"ryego/egoaction_password.php", 
                {
                    sessionid:_sessioninfo.sessionid,
                    currpwd:encryptString( objcurrpwd.value() ),
                    newpwd:encryptString( n ), 
                    lenpwd:n.length,
                    ldpwd:ld,
                    ulpwd:ul,
                    repeatpwd:encryptString( objrepeatpwd.value() )
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success){
                            objcurrpwd.clear();
                            objnewpwd.clear();
                            objrepeatpwd.clear();
                            objcurrpwd.focus();
                            objgo.enabled(1);
                        }
                        else{
                            switch(v.field){
                                case 1: objcurrpwd.clear(); objcurrpwd.focus(); break;
                                case 2: objnewpwd.clear(); objrepeatpwd.clear(); objnewpwd.focus(); break;
                                case 3: objrepeatpwd.clear(); objrepeatpwd.focus(); break;
                            }
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
    
    // Disattivazione
    $("#actionDeactivation").rylabel({
        left:0,
        top:20,
        caption:"Disattivazione utente",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdeactivate"))){
                $.post(_systeminfo.relative.cambusa+"ryego/egoaction_users.php", 
                    {
                        action:"activate",
                        sessionid:_sessioninfo.sessionid,
                        userid:_userid,
                        active:0
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success){
                                egoterminate(true)
                            }
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

	offsety=50;
    $("#lbdeactivation").css({position:"absolute", left:0, top:offsety, "font-size":"14px"});

	// Vai all'applicazione
	objgo=$("#lbgo2app").rylabel({
        left:10,
        top:350,
        caption:"Vai all'applicazione",
        button:true,
        flat:true,
        enabled:(_expiry<2),
        click:function(o){
            $("body").html("<form id='nextaction' method='<?php  print $egomethod ?>' action=''></form>");
            $("#nextaction").append("<input type='hidden' id='sessionid' name='sessionid'>");
            $("#nextaction").attr({action:"<?php print $returnurl ?>"});
            $("#sessionid").val(_sessioninfo.sessionid);
            $("#nextaction").submit();
        }
    });
    if(_returnURL==""){
        objgo.visible(0);
    }
	
	// Torna alla login
	objlog=$("#lbgo2log").rylabel({
        left:10,
        top:390,
        caption:"Torna alla login",
        button:true,
        flat:true,
        click:function(o){
			egoterminate(true)
        }
    });
	
	// FINE AZIONI
    // FINE FORM PASSWORD
    // CARICAMENTO DATI
    if(_expiry==0)
        syswaiting();
    loading();
}
function postlocalize(){
    var t;
    
    t=RYBOX.getbabel("lbside_settings");
    $("#side_settings").html(t);
    //$("#settings .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_changepassword");
    $("#side_changepassword").html(t);
    //$("#changepassword .form-title").html(t.toUpperCase());

    t=RYBOX.getbabel("lbside_deactivation");
    $("#side_deactivation").html(t);
    //$("#deactivation .form-title").html(t.toUpperCase());

    t=RYBOX.getbabel("lbauthenticationservice");
    $("title").html(t);
    $("#egotitle").html(t);
    
    t=RYBOX.babels("EGO_WARNINGDEACTIVATE");
    $("#lbdeactivation").html(t);
}
// CARICAMENTO MASCHERA
function loading(missing){
    $.post(_systeminfo.relative.cambusa+"ryego/ego_infosetup.php", 
        {
            sessionid:_sessioninfo.sessionid,
            appid:_appid,
            aliasid:_aliasid
        }, 
        function(d){
            try{
                var x, v=$.parseJSON(d);
                
                // SETUP
                lastenvironid=v["lastenvironid"];
                lastroleid=v["lastroleid"];
                lastlanguageid=v["lastlanguageid"];
                lastcountrycode=v["lastcountrycode"];
                lastdebugmode=v["lastdebugmode"].toString();

                // LISTA AMBIENTI
                x=v["lstenviron"];
                lstenviron.additem({caption:"", key:""});
                for(var i in x){
                    lstenviron.additem({caption:x[i]["caption"], key:x[i]["key"]});
                }
                lstenviron.setkey(lastenvironid);
                
                // LISTA RUOLI
                x=v["lstrole"];
                lstrole.additem({caption:"", key:""});
                for(var i in x){
                    lstrole.additem({caption:x[i]["caption"], key:x[i]["key"]});
                }
                lstrole.setkey(lastroleid);
                
                // LISTA LINGUE
                x=v["lstlanguage"];
                lstlanguage.additem({caption:"", key:"", tag:"default"});
                for(var i in x){
                    lstlanguage.additem({caption:x[i]["caption"], key:x[i]["key"], tag:x[i]["tag"]});
                }
                lstlanguage.setkey(lastlanguageid);
                
                // LISTA COUNTRY CODE
                x=v["lstcc"];
                lstcountry.additem({caption:"", key:""});
                for(var i in x){
                    lstcountry.additem({caption:x[i]["caption"], key:x[i]["key"]});
                }
                lstcountry.setkey(lastcountrycode);
                
                // LISTA DEBUG MODE
                lstdebugmode.additem({caption:"", key:""});
                lstdebugmode.additem({caption:"Normale", key:"0"});
                lstdebugmode.additem({caption:"Debugging", key:"1"});
                lstdebugmode.setkey(lastdebugmode);
                
                // EMAIL
                txemail.value(v["email"]);
                
                // ELEMINO L'ATTESA
                if(_expiry==0)
                    sysmessagehide();
                
                RYBOX.babels({
                    "EGO_WARNINGDEACTIVATE":$("#lbdeactivation").html()
                });

                // LOCALIZZAZIONE FORM
                RYBOX.localize(lstlanguage.gettag(lstlanguage.value()), missing,
                    function(){
                        postlocalize();
                        switch(_expiry){
                            case 1:
                                activation('changepassword');
                                sysmessage(RYBOX.getbabel("lbexpiringpwd"), 0);
                                break;
                            case 2:
                                activation('changepassword');
                                sysmessage(RYBOX.getbabel("lbexpiredpwd"), 0)
                                break;
                        }
                    }
                );
            }
            catch(e){
                alert(d);
            }
        }
    );
}
function egoterminate(lout){
    try{
        if(lout){
            logout()
        }
    }
    catch(e){}
}
</script>
