<?php
/****************************************************************************
* Name:            egoform_setupapp.php                                     *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.58                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<script>
_sessionid="<?php print $sessionid ?>";
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

var cc=[];
<?php
    $tr=array("'" => "&acute;");
    // APERTURA DATABASE GEOGRAPHY
    $maestro=maestro_opendb("rygeography");

    if($maestro->conn!==false){
        maestro_query($maestro,"SELECT DESCRIPTION,ALFATRE FROM GEONAZIONI ORDER BY DESCRIPTION", $r);
        for($i=0;$i<count($r);$i++){
            print "cc['".$r[$i]["ALFATRE"]."']='". strtr( $r[$i]["DESCRIPTION"], $tr) . "';";
        }
    }

    // CHIUSURA DATABASE
    maestro_closedb($maestro);
?>

var objcurrpwd;
var objnewpwd;
var objrepeatpwd;
var objgo;

function activation(n){
    $("#settings").hide();
    $("#changepassword").hide();
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
    $("#lbauthenticationservice").rylabel({caption:"Servizio di autenticazione"});
    
    activation('settings');
    
    // INIZIO FORM OPZIONI
    $("#lbenviron").rylabel({left:20, top:70, caption:"Ambiente"});
    lstenviron=$("#lstenviron").rylist({
        left:180,
        top:70,
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
    $("#lbrole").rylabel({left:20, top:100, caption:"Ruolo"});
    lstrole=$("#lstrole").rylist({
        left:180,
        top:100,
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
    $("#lblanguage").rylabel({left:20, top:130, caption:"Lingua"});
    lstlanguage=$("#lstlanguage").rylist({
        left:180,
        top:130,
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
    $("#lbcountry").rylabel({left:20, top:160, caption:"Paese"});
    lstcountry=$("#lstcountry").rylist({
        left:180,
        top:160,
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
    $("#lbdebugmode").rylabel({left:20, top:190, caption:"Modalit&agrave;"});
    lstdebugmode=$("#lstdebugmode").rylist({
        left:180,
        top:190,
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

	// Vai all'applicazione
	objgo=$("#lbgo2app").rylabel({
        left:20,
        top:250,
        caption:"Vai all'applicazione",
        button:true,
        flat:true,
        enabled:(_expiry<2),
        click:function(o){
            RYQUE.dispose(
                function(){
                    $("body").html("<form id='nextaction' method='<?php  print $egomethod ?>' action=''></form>");
                    $("#nextaction").append("<input type='hidden' id='sessionid' name='sessionid'>");
                    $("#nextaction").attr({action:"<?php print $returnurl ?>"});
                    $("#sessionid").val(_sessionid);
                    $("#nextaction").submit();
                }
            );
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
    $("#settings .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_changepassword");
    $("#side_changepassword").html(RYBOX.getbabel("lbside_changepassword"));
    $("#changepassword .form-title").html(t.toUpperCase());

    t=RYBOX.getbabel("lbauthenticationservice");
    $("title").html(t);
    $("#egotitle").html(t);
}
// CARICAMENTO MASCHERA
function loading(missing){
    RYQUE.request({
        environ:"ryego",
        ready:function(id){
            // Reperisco ambiente, ruolo e lingua di setup
            RYQUE.query({
                sql:"SELECT ENVIRONID,ROLEID,LANGUAGEID,COUNTRYCODE,DEBUGMODE FROM EGOSETUP WHERE APPID='"+_appid+"' AND ALIASID='"+_aliasid+"'",
                ready:function(v){
                    lastenvironid=v[0].ENVIRONID;
                    lastroleid=v[0].ROLEID;
                    lastlanguageid=v[0].LANGUAGEID;
                    lastcountrycode=v[0].COUNTRYCODE;
                    lastdebugmode=v[0].DEBUGMODE;
                    // Precarico le liste ambienti, ruoli e lingue
                    RYQUE.query({   // ambienti
                        sql:"SELECT DESCRIPTION,ENVIRONID FROM EGOVIEWENVIRONUSER WHERE APPID='"+_appid+"' AND USERID='"+_userid+"'",
                        ready:function(v){
                            lstenviron.additem({caption:"", key:""});
                            for(var i in v){
                                lstenviron.additem({caption:v[i].DESCRIPTION, key:v[i].ENVIRONID});
                            }
                            lstenviron.setkey(lastenvironid);
                            RYQUE.query({   // ruoli
                                sql:"SELECT DESCRIPTION,ROLEID FROM EGOVIEWROLEUSER WHERE APPID='"+_appid+"' AND USERID='"+_userid+"'",
                                ready:function(v){
                                    lstrole.additem({caption:"", key:""});
                                    for(var i in v){
                                        lstrole.additem({caption:v[i].DESCRIPTION, key:v[i].ROLEID});
                                    }
                                    lstrole.setkey(lastroleid);
                                    RYQUE.query({   // lingue
                                        sql:"SELECT * FROM EGOLANGUAGES",
                                        ready:function(v){
                                            lstlanguage.additem({caption:"", key:"", tag:"default"});
                                            for(var i in v){
                                                lstlanguage.additem({caption:v[i].DESCRIPTION, key:v[i].SYSID, tag:v[i].NAME});
                                            }
                                            lstlanguage.setkey(lastlanguageid);
                                            // country code
                                            lstcountry.additem({caption:"", key:""});
                                            for(var i in cc){
                                                lstcountry.additem({caption:cc[i], key:i});
                                            }
                                            lstcountry.setkey(lastcountrycode);
                                            // debug mode
                                            lstdebugmode.additem({caption:"", key:""});
                                            lstdebugmode.additem({caption:"Normale", key:"0"});
                                            lstdebugmode.additem({caption:"Debugging", key:"1"});
                                            lstdebugmode.setkey(lastdebugmode);
                                            // localizzazione form
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
                                    });
                                    if(_expiry==0)
                                        sysmessagehide();
                                }
                            });
                        }
                    });
                }
            });
        }
    });
}
function egoterminate(lout){
    try{
        RYQUE.dispose(
            function(){
                if(lout){logout()}
            }
        );
    }
    catch(e){}
}
</script>
