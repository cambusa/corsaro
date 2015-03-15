<?php
/****************************************************************************
* Name:            egoform_setupapp.php                                     *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
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
    $("#"+n).show();
    switch(n){
    case "changepassword":
        setTimeout(function(){$("#txcurrpwd_anchor").focus()}, 100);
        break
    }
}

// Internet Explorer gestisce l'evento in un modo impossibile
if(!$.browser.msie){
    window.onbeforeunload=function(){
        egoterminate(false);
        _pause(1000);
    };
}

$(document).ready(function(){
    activation('dummy');
    if(_sessionid!=""){
         $.post(_cambusaURL+"ryego/ego_infosession.php",{"sessionid":_sessionid,"app":_ajaxescapize(_appname)}, 
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
    var offsety=70;
    $("#lbenviron").rylabel({left:20, top:offsety, caption:"Ambiente"});
    lstenviron=$("#lstenviron").rylist({
        left:180,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_last.php", 
                {"sessionid":_sessionid,"appid":_appid,"aliasid":_aliasid,"environid":obj.key(obj.value())}, 
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
    $("#lbrole").rylabel({left:20, top:offsety, caption:"Ruolo"});
    lstrole=$("#lstrole").rylist({
        left:180,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_last.php", 
                {"sessionid":_sessionid,"appid":_appid,"aliasid":_aliasid,"roleid":obj.key(obj.value())}, 
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
    $("#lblanguage").rylabel({left:20, top:offsety, caption:"Lingua"});
    lstlanguage=$("#lstlanguage").rylist({
        left:180,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_last.php", 
                {"sessionid":_sessionid,"appid":_appid,"aliasid":_aliasid,"languageid":obj.key(obj.value())}, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        // localizzazione form
                        RYBOX.localize(obj.gettag(obj.value()), missing,
                            function(){
                                postlocalize();
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
    $("#lbcountry").rylabel({left:20, top:offsety, caption:"Paese"});
    lstcountry=$("#lstcountry").rylist({
        left:180,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_last.php", 
                {"sessionid":_sessionid,"appid":_appid,"aliasid":_aliasid,"countrycode":obj.key(obj.value())}, 
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
    $("#lbdebugmode").rylabel({left:20, top:offsety, caption:"Modalità"});
    lstdebugmode=$("#lstdebugmode").rylist({
        left:180,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_last.php", 
                {"sessionid":_sessionid,"appid":_appid,"aliasid":_aliasid,"debugmode":obj.key(obj.value())}, 
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
    $("#lbemail").rylabel({left:20, top:offsety, caption:"Email"});
    txemail=$("#txemail").rytext({
        left:180,
        top:offsety,
		assigned:function(obj){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_last.php", 
                {"sessionid":_sessionid,"appid":_appid,"aliasid":_aliasid,"email":obj.value()}, 
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

    $("#lbcurrpwd").rylabel({left:20, top:70, caption:"Password attuale"});
    objcurrpwd=$("#txcurrpwd").rytext({ 
        left:180,
        top:70, 
        password:true,
        maxlen:16
    });
    $("#lbnewpwd").rylabel({left:20, top:100, caption:"Nuova password"});
    objnewpwd=$("#txnewpwd").rytext({ 
        left:180,
        top:100, 
        password:true,
        maxlen:16
    });
    $("#lbrepeatpwd").rylabel({left:20, top:130, caption:"Ripeti password"});
    objrepeatpwd=$("#txrepeatpwd").rytext({ 
        left:180,
        top:130, 
        password:true,
        maxlen:16
    });

    // INIZIO AZIONI
    
    // Registra nuova password
    $("#actionPassword").rylabel({
        left:400,
        top:130,
        caption:"Conferma",
        button:true,
        flat:true,
        click:function(o){
            var n=objnewpwd.value();
            var ld=0,ul=0;
            if(n.replace(/[0-9]/,"")!=n && n.replace(/[A-Za-z]/,"")!=n)
                ld=1;
            if(n.replace(/[A-Z]/,"")!=n && n.replace(/[a-z]/,"")!=n)
                ul=1;
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_password.php", 
                {
                    sessionid:_sessionid,
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
    
    $("#lbdeactivation").css({position:"absolute", left:20, top:70, width:580, "font-size":"16px"});

    // Disattivazione
    $("#actionDeactivation").rylabel({
        left:20,
        top:160,
        caption:"Disattivazione utente",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdeactivate"))){
                $.post(_cambusaURL+"ryego/egoaction_users.php", 
                    {
                        action:"activate",
                        sessionid:_sessionid,
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

	// Vai all'applicazione
	objgo=$("#lbgo2app").rylabel({
        left:20,
        top:250,
        caption:"Vai all'applicazione",
        button:true,
        flat:true,
        enabled:(_expiry<2),
        click:function(o){
            $("body").html("<form id='nextaction' method='<?php  print $egomethod ?>' action=''></form>");
            $("#nextaction").append("<input type='hidden' id='sessionid' name='sessionid'>");
            $("#nextaction").attr({action:"<?php print $returnurl ?>"});
            $("#sessionid").val(_sessionid);
            $("#nextaction").submit();
        }
    });
    if(_returnURL==""){
        objgo.visible(0);
    }
	
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
    $("#settings .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_changepassword");
    $("#side_changepassword").html(t);
    $("#changepassword .form-title").html(t.toUpperCase());

    t=RYBOX.getbabel("lbside_deactivation");
    $("#side_deactivation").html(t);
    $("#deactivation .form-title").html(t.toUpperCase());

    t=RYBOX.getbabel("lbauthenticationservice");
    $("title").html(t);
    $("#egotitle").html(t);
    
    t=RYBOX.babels("EGO_WARNINGDEACTIVATE");
    $("#lbdeactivation").html(t);
}
// CARICAMENTO MASCHERA
function loading(missing){
    $.post(_cambusaURL+"ryego/ego_infosetup.php", 
        {
            sessionid:_sessionid,
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
