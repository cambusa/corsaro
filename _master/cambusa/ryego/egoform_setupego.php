<?php
/****************************************************************************
* Name:            egoform_setupego.php                                     *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<script>
_sessionid="<?php print $sessionid ?>";
var _aliasid="<?php print $aliasid ?>";
var flaginit=true;
var objgridusers;
var objgridapplications;
var objgridenvirons;
var objgridenvuser;
var objgridenvusersel;
var objgridroles;
var objgridroleuser;
var objgridroleusersel;
var objgridlanguages;
var objgridsessions;

// SETTINGS
var objoptduration;
var objoptwarning;
var objoptminlen;
var objoptdefault;
var objoptupperlower;
var objoptletterdigit;
var objoptsaveuser;
var objoptemailreset;

// USERS 
var curralias="";
var objusr_filter;
var objusr_only;
var objusr_refresh;
var objusr_user;
var objusr_alias;
var objusr_email;
var objusr_demiurge;
var objusr_admin;

// APPLICATION
var objapp_status;
var currapp="";
var currappid="";
var objapp_refresh;
var objapp_name;
var objapp_descr;

// ENVIRONS
var currenv="";
var currenvid="";
var objenv_refresh;
var objenv_name;
var objenv_descr;

var objenvusr_refresh;
var objenvusr_filter;

// ROLES
var currrole="";
var currroleid="";
var objrole_refresh;
var objrole_name;
var objrole_descr;

var objroleusr_refresh;
var objroleusr_filter;

// LANGUAGES
var currlang="";
var currlangid="";
var objlng_refresh;
var objlng_lang;
var objlng_descr;

// SESSIONS
var objses_only;
var objses_refresh;
var objses_filter;

// PASSWORD
var objcurrpwd;
var objnewpwd;
var objrepeatpwd;

function activation(n){
    $("#settings").hide();
    $("#users").hide();
    $("#applications").hide();
    $("#languages").hide();
    $("#sessions").hide();
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
         $.post(_cambusaURL+"ryego/ego_infosession.php",{sessionid:_sessionid,app:""}, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success){
                        config();
                        $("#titlename").html(v.alias);
                    }
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
    $("#lbselectsession").rylabel({caption:"Selezionare una sessione"});
    $("#lbconfirmdelsessions").rylabel({caption:"Eliminare tutte le sessioni scadute?"});
    $("#lbconfirmresetpwd").rylabel({caption:"Ripristinare la password predefinita per l'utente selezionato?"});
    $("#lbconfirmdelalias").rylabel({caption:"Eliminare l'alias selezionato?"});
    $("#lbconfirmdelusers").rylabel({caption:"Eliminare tutti gli utenti disattivati?"});
    $("#lbconfirmdelapp").rylabel({caption:"Eliminare l'applicazione selezionata?"});
    $("#lbconfirmdelenviron").rylabel({caption:"Eliminare l'ambiente selezionato?"});
    $("#lbconfirmdelrole").rylabel({caption:"Eliminare il ruolo selezionato?"});
    $("#lbconfirmdellang").rylabel({caption:"Eliminare la lingua selezionata?"});
    $("#lbside_settings").rylabel({caption:"Opzioni"});
    $("#lbside_users").rylabel({caption:"Utenti"});
    $("#lbside_applications").rylabel({caption:"Applicazioni"});
    $("#lbside_languages").rylabel({caption:"Lingue"});
    $("#lbside_sessions").rylabel({caption:"Sessioni"});
    $("#lbside_changepassword").rylabel({caption:"Cambio password"});
    $("#lbauthenticationservice").rylabel({caption:"Servizio di autenticazione"});
    $("#lbtabapplications").rylabel({caption:"Applicazioni"});
    $("#lbtabenvirons").rylabel({caption:"Ambienti"});
    $("#lbtabenvusers").rylabel({caption:"Ambiente/Utenti"});
    $("#lbtabroles").rylabel({caption:"Ruoli"});
    $("#lbtabroleusers").rylabel({caption:"Ruolo/Utenti"});
    
    activation('<?php print $active ?>');

    // INIZIO FORM OPZIONI
    $("#lboptduration").rylabel({left:20,top:60,caption:"Durata gg"});
    objoptduration=$("#txoptduration").rynumber({ 
        left:180,
        top:60,
        numdec:0,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    duration:objoptduration.value()
                }, 
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
    $("#lboptwarning").rylabel({left:20,top:90,caption:"Preavviso gg"});
    objoptwarning=$("#txoptwarning").rynumber({ 
        left:180,
        top:90,
        numdec:0,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    warning:objoptwarning.value()
                }, 
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
    $("#lboptminlen").rylabel({left:20,top:120,caption:"Lunghezza minima"});
    objoptminlen=$("#txoptminlen").rynumber({ 
        left:180,
        top:120,
        numdec:0,
        minvalue:4,
        maxvalue:16,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    minlen:objoptminlen.value()
                }, 
                function(d){
                    var v=$.parseJSON(d);
                    sysmessage(v.description, v.success);
                }
            );
        }
    });
    $("#lboptdefault").rylabel({left:20,top:150,caption:"Predefinita"});
    objoptdefault=$("#txoptdefault").rytext({ 
        left:180,
        top:150,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    "default":_ajaxescapize(objoptdefault.value())
                }, 
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
    $("#lboptupperlower").rylabel({left:20,top:180,caption:"Maiuscolo/minuscolo"});
    objoptupperlower=$("#txoptupperlower").rycheck({ 
        left:180,
        top:180,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    upperlower:objoptupperlower.value()
                }, 
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
    $("#lboptletterdigit").rylabel({left:20,top:210,caption:"Cifre/lettere"});
    objoptletterdigit=$("#txoptletterdigit").rycheck({ 
        left:180,
        top:210,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    letterdigit:objoptletterdigit.value()
                }, 
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
    $("#lboptsaveuser").rylabel({left:20,top:240,caption:"Ricorda utente"});
    objoptsaveuser=$("#txoptsaveuser").rycheck({ 
        left:180,
        top:240,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    saveuser:objoptsaveuser.value()
                }, 
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
    $("#lboptemailreset").rylabel({left:20,top:270,caption:"Reset via email"});
    objoptemailreset=$("#txoptemailreset").rycheck({ 
        left:180,
        top:270,
        assigned:function(){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_settings.php", 
                {
                    sessionid:_sessionid,
                    emailreset:objoptemailreset.value()
                }, 
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

    // INIZIO FORM USERS
    objgridusers=$("#gridusers").ryque({
        left:0,
        top:40,
        width:400,
        height:350,
        numbered:false,
        checkable:false,
        environ:"ryego",
        from:"EGOVIEWUSERS",
        orderby:"USERNAME,ALIASNAME",
        columns:[
            {id:"ALIASNAME", caption:"Alias", width:180, code:"EGO_GRID_ALIAS"},
            {id:"USERNAME", caption:"Utente", width:180, code:"EGO_GRID_USER"},
            {id:"ACTIVE", caption:"", width:20, type:"?"}
        ],
        changerow:function(o,i){
            objusr_user.caption("");
            objusr_alias.clear();
            objusr_email.clear();
            objusr_demiurge.value(false);
            objusr_admin.value(false);
            curralias="";
            if(i>0)
                objgridusers.solveid(i);
        },
        solveid:function(o,d){
            RYQUE.query({
                sql:"SELECT ALIASNAME,USERNAME,EMAIL,DEMIURGE,ADMINISTRATOR FROM EGOVIEWUSERS WHERE SYSID='"+d+"'",
                ready:function(v){
                    curralias=v[0].ALIASNAME;
                    objusr_user.caption(v[0].USERNAME);
                    objusr_alias.value(v[0].ALIASNAME);
                    objusr_email.value(v[0].EMAIL);
                    objusr_demiurge.value(v[0].DEMIURGE);
                    objusr_admin.value(v[0].ADMINISTRATOR);
                }
            });
        }
    });

    $("#lbusr_filter").rylabel({left:420,top:36,caption:"Filtro"});
    objusr_filter=$("#txusr_filter").rytext({left:490,top:36,width:160,maxlen:30,
        assigned:function(){
            objusr_refresh.engage();
        }
    });

    $("#lbusr_only").rylabel({left:420,top:60,caption:"Solo attivi"});
    objusr_only=$("#chkusr_only").rycheck({
        left:490,
        top:60,
        assigned:function(o){
            objusr_refresh.engage();
        }
    });
    objusr_only.value(true);
    objusr_refresh=$("#lbusr_refresh").rylabel({
        left:540,
        top:60,
        caption:"Aggiorna/Pulisci",
        button:true,
        flat:true,
        click:function(o){
            var q="";
            var t=objusr_filter.value().toUpperCase();
            t=t.replace(" ", "%");
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(USERNAME)] LIKE '%[=USERNAME]%' OR [:UPPER(ALIASNAME)] LIKE '%[=ALIASNAME]%' )";
            }
            if(objusr_only.value()){
                if(q!=""){q+=" AND "}
                q+="ACTIVE=1";
            }
            objgridusers.where(q);
            objgridusers.query({
                args:{
                    "USERNAME":_ajaxescapize( t ),
                    "ALIASNAME":_ajaxescapize( t )
                },
                orderby:"USERNAME,ALIASNAME",
                ready:function(){
                    objusr_alias.focus();
                    objenvusr_refresh.engage();
                }
            });
        }
    });
    $("#lbusr_use").rylabel({left:420,top:90,caption:"Usa:"});
    objusr_user=$("#lbusr_user").rylabel({left:490,top:90,caption:""});
    
    $("#lbusr_alias").rylabel({left:420,top:118,caption:"Alias"});
    objusr_alias=$("#txusr_alias").rytext({left:490,top:118,width:160,maxlen:30});
    
    $("#lbusr_email").rylabel({left:420,top:142,caption:"Email"});
    objusr_email=$("#txusr_email").rytext({left:490,top:142,width:160,maxlen:50});
    
    $("#lbusr_demiurge").rylabel({left:420,top:166,caption:"Demiurgo"});
    objusr_demiurge=$("#chkusr_demiurge").rycheck({left:490,top:166});
    
    $("#lbusr_admin").rylabel({left:550,top:166,caption:"Amministr."});
    objusr_admin=$("#chkusr_admin").rycheck({left:630,top:166});
    
    $("#lbusr_as").rylabel({left:420,top:196,caption:"Come:"});
    $("#lbusr_action_newuser").rylabel({
        left:420,
        top:216,
        caption:"Nuovo utente",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_users.php", 
                {
                    action:"newuser",
                    sessionid:_sessionid,
                    user:_ajaxescapize(objusr_alias.value()), 
                    email:_ajaxescapize(objusr_email.value()),
                    demiurge:objusr_demiurge.value(),
                    admin:objusr_admin.value()
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbusr_action_newalias").rylabel({
        left:420,
        top:236,
        caption:"Nuovo alias",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_users.php", 
                {
                    action:"newalias",
                    sessionid:_sessionid,
                    user:_ajaxescapize(objusr_user.caption()),
                    alias:_ajaxescapize(objusr_alias.value()), 
                    email:_ajaxescapize(objusr_email.value()),
                    demiurge:objusr_demiurge.value(),
                    admin:objusr_admin.value()
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbusr_action_updateuser").rylabel({
        left:420,
        top:256,
        caption:"Modifica selezionato",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_users.php", 
                {
                    action:"update",
                    sessionid:_sessionid,
                    alias:_ajaxescapize(curralias), 
                    aliasnew:_ajaxescapize(objusr_alias.value()),
                    email:_ajaxescapize(objusr_email.value()),
                    demiurge:objusr_demiurge.value(),
                    admin:objusr_admin.value()
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbusr_or").rylabel({left:420,top:286,caption:"Oppure:"});
    $("#lbusr_action_reset").rylabel({
        left:420,
        top:306,
        caption:"Resetta password",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmresetpwd"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_users.php", 
                    {
                        action:"reset",
                        sessionid:_sessionid,
                        user:_ajaxescapize(objusr_user.caption())
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objusr_refresh.engage();
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
    $("#lbusr_action_activate").rylabel({
        left:420,
        top:326,
        caption:"Attiva/Disattiva utente",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_users.php", 
                {
                    action:"activate",
                    sessionid:_sessionid,
                    user:_ajaxescapize(objusr_user.caption())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbusr_action_deletealias").rylabel({
        left:420,
        top:346,
        caption:"Elimina alias",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdelalias"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_users.php", 
                    {
                        action:"delete",
                        sessionid:_sessionid,
                        alias:_ajaxescapize(curralias)
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objusr_refresh.engage();
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
    $("#lbusr_action_deleteall").rylabel({
        left:420,
        top:366,
        caption:"Elimina tutti i disattivati",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdelusers"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_users.php", 
                    {
                        action:"deleteall",
                        sessionid:_sessionid
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objusr_refresh.engage();
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
    // FINE FORM USERS
    
    // INIZIO FORM APPLICAZIONI
    $( "#tabs" ).tabs();

    // INIZIO TAB APPLICAZIONI
    objapp_status=$("#lbapp_status").rylabel({
        left:300,
        top:20,
        caption:""
    });
    objgridapplications=$("#gridapplications").ryque({
        left:10,
        top:55,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:"ryego",
        from:"EGOAPPLICATIONS",
        columns:[
            {id:"NAME", caption:"Applicazione", width:200, code:"EGO_GRID_APP"}
        ],
        changerow:function(o,i){
            objapp_name.clear();
            objapp_descr.clear();
            currapp="";
            currappid="";
            currenv="";
            currenvid="";
            currrole="";
            currroleid="";
            objapp_status.caption("");
            objgridenvirons.clear();
            objgridenvuser.clear();
            objgridenvusersel.clear();
            objgridroles.clear();
            objgridroleuser.clear();
            objgridroleusersel.clear();
            $( "#tabs" ).tabs("disable",1);
            $( "#tabs" ).tabs("disable",2);
            $( "#tabs" ).tabs("disable",3);
            $( "#tabs" ).tabs("disable",4);
            if(i>0)
                objgridapplications.solveid(i);
        },
        solveid:function(o,d){
            currappid=d;
            RYQUE.query({
                sql:"SELECT NAME,DESCRIPTION FROM EGOAPPLICATIONS WHERE SYSID='"+currappid+"'",
                ready:function(v){
                    currapp=v[0].NAME;
                    objapp_name.value(v[0].NAME);
                    objapp_descr.value(v[0].DESCRIPTION);
                    objapp_status.caption(currapp);
                    objgridenvirons.where("APPID='"+currappid+"'");
                    objgridenvirons.query({
                        ready:function(v){
                            objgridroles.where("APPID='"+currappid+"'");
                            objgridroles.query({
                                ready:function(){
                                    $( "#tabs" ).tabs("enable",1);
                                    $( "#tabs" ).tabs("enable",3);
                                }
                            });
                        }
                    });
                }
            });
        }
    });
    objapp_refresh=$("#lbapp_refresh").rylabel({
        left:420,
        top:60,
        caption:"Aggiorna/Pulisci",
        button:true,
        flat:true,
        click:function(o){
            objgridapplications.query({
                where:"",orderby:"SYSID",
                ready:function(){
                    objapp_name.focus();
                }
            });
        }
    });

    $("#lbapp_use").rylabel({left:420,top:94,caption:"Usa:"});
    
    $("#lbapp_name").rylabel({left:420,top:122,caption:"Applicaz."});
    objapp_name=$("#txapp_name").rytext({left:490,top:122,width:130,maxlen:30});
    
    $("#lbapp_descr").rylabel({left:420,top:146,caption:"Descr."});
    objapp_descr=$("#txapp_descr").rytext({left:490,top:146,width:130,maxlen:50});
    
    $("#lbapp_as").rylabel({left:420,top:200,caption:"Come:"});
    
    $("#lbapp_action_insert").rylabel({
        left:420,
        top:220,
        caption:"Nuova applicazione",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_apps.php", 
                {
                    action:"insert",
                    sessionid:_sessionid,
                    app:_ajaxescapize(objapp_name.value()),
                    descr:_ajaxescapize(objapp_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objapp_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbapp_action_update").rylabel({
        left:420,
        top:240,
        caption:"Modifica selezionata",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_apps.php", 
                {
                    action:"update",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    appnew:_ajaxescapize(objapp_name.value()),
                    descr:_ajaxescapize(objapp_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objapp_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbapp_or").rylabel({left:420,top:270,caption:"Oppure:"});
    $("#lbapp_action_delete").rylabel({
        left:420,
        top:290,
        caption:"Elimina applicazione",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdelapp"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_apps.php", 
                    {
                        action:"delete",
                        sessionid:_sessionid,
                        app:_ajaxescapize(currapp)
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objapp_refresh.engage();
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
    // FINE TAB APPLICAZIONI
    
    // INIZIO TAB AMBIENTI
    objgridenvirons=$("#gridenvirons").ryque({
        left:10,
        top:55,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:"ryego",
        from:"EGOENVIRONS",
        columns:[
            {id:"NAME", caption:"Nome", width:200, code:"EGO_GRID_NAME"},
            {id:"DESCRIPTION", caption:"Descrizione", width:200, code:"EGO_GRID_DESCR"}
        ],
        changerow:function(o,i){
            objenv_name.clear();
            objenv_descr.clear();
            currenv="";
            currenvid="";
            if(currrole!="")
                objapp_status.caption(currapp+"/("+currrole+")");
            else
                objapp_status.caption(currapp);
            objgridenvuser.clear();
            objgridenvusersel.clear();
            $( "#tabs" ).tabs("disable", 2);
            if(i>0)
                objgridenvirons.solveid(i);
        },
        solveid:function(o,d){
            currenvid=d;
            $( "#tabs" ).tabs("enable", 2);
            RYQUE.query({
                sql:"SELECT NAME,DESCRIPTION FROM EGOENVIRONS WHERE SYSID='"+currenvid+"'",
                ready:function(v){
                    currenv=v[0].NAME;
                    objenv_name.value(v[0].NAME);
                    objenv_descr.value(v[0].DESCRIPTION);
                    if(currrole!="")
                        objapp_status.caption(currapp+"/"+currenv+"("+currrole+")");
                    else
                        objapp_status.caption(currapp+"/"+currenv);
                    objusr_refresh.engage();
                }
            });
        }
    });
    objenv_refresh=$("#lbenv_refresh").rylabel({
        left:420,
        top:60,
        caption:"Aggiorna/Pulisci",
        button:true,
        flat:true,
        click:function(o){
            objgridenvirons.where("APPID='"+currappid+"'");
            objgridenvirons.query({
                orderby:"SYSID",
                ready:function(){
                    objenv_name.focus();
                }
            });
        }
    });

    $("#lbenv_use").rylabel({left:420,top:94,caption:"Usa:"});
    
    $("#lbenv_name").rylabel({left:420,top:122,caption:"Ambiente"});
    objenv_name=$("#txenv_name").rytext({left:490,top:122,width:130,maxlen:30});
    
    $("#lbenv_descr").rylabel({left:420,top:146,caption:"Descr."});
    objenv_descr=$("#txenv_descr").rytext({left:490,top:146,width:130,maxlen:50});
    
    $("#lbenv_as").rylabel({left:420,top:200,caption:"Come:"});
    
    $("#lbenv_action_insert").rylabel({
        left:420,
        top:220,
        caption:"Nuovo ambiente",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_environs.php", 
                {
                    action:"insert",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    env:_ajaxescapize(objenv_name.value()),
                    descr:_ajaxescapize(objenv_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objenv_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbenv_action_update").rylabel({
        left:420,
        top:240,
        caption:"Modifica selezionato",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_environs.php", 
                {
                    action:"update",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    env:_ajaxescapize(currenv),
                    envnew:_ajaxescapize(objenv_name.value()),
                    descr:_ajaxescapize(objenv_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objenv_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbenv_or").rylabel({left:420,top:270,caption:"Oppure:"});
    $("#lbenv_action_delete").rylabel({
        left:420,
        top:290,
        caption:"Elimina ambiente",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdelenviron"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_environs.php", 
                    {
                        action:"delete",
                        sessionid:_sessionid,
                        app:_ajaxescapize(currapp),
                        env:_ajaxescapize(currenv)
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objenv_refresh.engage();
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
    // FINE TAB AMBIENTI
    
    // INIZIO TAB AMBIENTE-UTENTE
    objgridenvuser=$("#gridenvuser").ryque({
        left:10,
        top:55,
        width:250,
        height:300,
        numbered:false,
        checkable:true,
        environ:"ryego",
        from:"EGOVIEWUSERS",
        columns:[
            {id:"USERNAME", caption:"Utenti fuori ambiente", width:200, code:"EGO_GRID_OUTENV"}
        ],
        solveid:function(o,y){
            // Sto aggiungendo
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_envuser.php", 
                {
                    action:"add",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    env:_ajaxescapize(currenv),
                    users:_ajaxescapize(y)
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objenvusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    objgridenvusersel=$("#gridenvusersel").ryque({
        left:375,
        top:55,
        width:250,
        height:300,
        numbered:false,
        checkable:true,
        environ:"ryego",
        from:"EGOVIEWENVIRONUSER",
        columns:[
            {id:"USERNAME", caption:"Utenti in ambiente", width:200, code:"EGO_GRID_INENV"}
        ],
        solveid:function(o,y){
            // Sto togliendo
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_envuser.php", 
                {
                    action:"remove",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    env:_ajaxescapize(currenv),
                    users:_ajaxescapize(y)
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objenvusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    objenvusr_refresh=$("#lbenvusr_refresh").rylabel({
        left:290,
        top:60,
        caption:"Aggiorna",
        button:true,
        flat:true,
        click:function(o){
            if(currapp!="" && currenv!=""){
                var q="";
                var t=objusr_filter.value();
                if(t.length<=6)
                    objenvusr_filter.caption(t+"*");
                else
                    objenvusr_filter.caption(t.substr(0,6)+"...");
                t=t.toUpperCase().replace(" ", "%");
                q="EGOVIEWUSERS.ACTIVE=1 AND EGOVIEWUSERS.ALIASNAME=EGOVIEWUSERS.USERNAME AND NOT EGOVIEWUSERS.USERID IN (SELECT EGOENVIRONUSER.USERID FROM EGOENVIRONUSER WHERE EGOENVIRONUSER.ENVIRONID='"+currenvid+"')";
                if(t!=""){
                    q+="AND ( [:UPPER(USERNAME)] LIKE '[=USERNAME]%' )";
                }
                objgridenvuser.where(q);
                objgridenvuser.query({
                    args:{
                        "USERNAME":_ajaxescapize( t )
                    },
                    orderby:"USERNAME",
                    ready:function(){
                        q="ENVIRONID='"+currenvid+"'";
                        if(t!=""){
                            q+="AND ( [:UPPER(USERNAME)] LIKE '[=USERNAME]%' )";
                        }
                        objgridenvusersel.where(q);
                        objgridenvusersel.query({
                            args:{
                                "USERNAME":_ajaxescapize( t )
                            },
                            orderby:"USERNAME"
                        });
                    }
                });
            }
        }
    });
    objenvusr_filter=$("#lbenvusr_filter").rylabel({left:290, top:90, caption:""}); 
    $("#lbenvusr_action_add").rylabel({
        left:295,
        top:120,
        caption:"<img src='"+_cambusaURL+"ryego/images/arrow_right.png' style='position:absolute;top:0px;border:none;'>",
        button:true,
        flat:true,
        click:function(o){
            objgridenvuser.selengage();
        }
    });
    $("#lbenvusr_action_remove").rylabel({
        left:295,
        top:160,
        caption:"<img src='"+_cambusaURL+"ryego/images/arrow_left.png' style='position:absolute;top:0px;border:none;'>",
        button:true,
        flat:true,
        click:function(o){
            objgridenvusersel.selengage();
        }
    });
    // FINE TAB AMBIENTE-UTENTE

    // INIZIO TAB RUOLI
    objgridroles=$("#gridroles").ryque({
        left:10,
        top:55,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:"ryego",
        from:"EGOROLES",
        columns:[
            {id:"NAME", caption:"Nome", width:200, code:"EGO_GRID_NAME"},
            {id:"DESCRIPTION", caption:"Descrizione", width:200, code:"EGO_GRID_DESCR"}
        ],
        changerow:function(o,i){
            objrole_name.clear();
            objrole_descr.clear();
            currrole="";
            currroleid="";
            if(currenv!="")
                objapp_status.caption(currapp+"/"+currenv);
            else
                objapp_status.caption(currapp);
            objgridroleuser.clear();
            objgridroleusersel.clear();
            $( "#tabs" ).tabs("disable", 4);
            if(i>0)
                objgridroles.solveid(i);
        },
        solveid:function(o,d){
            currroleid=d;
            $( "#tabs" ).tabs("enable", 4);
            RYQUE.query({
                sql:"SELECT NAME,DESCRIPTION FROM EGOROLES WHERE SYSID='"+currroleid+"'",
                ready:function(v){
                    currrole=v[0].NAME;
                    objrole_name.value(v[0].NAME);
                    objrole_descr.value(v[0].DESCRIPTION);
                    if(currenv!="")
                        objapp_status.caption(currapp+"/"+currenv+"("+currrole+")");
                    else
                        objapp_status.caption(currapp+"/("+currrole+")");
                    objroleusr_refresh.engage();
                }
            });
        }
    });
    objrole_refresh=$("#lbrole_refresh").rylabel({
        left:420,
        top:60,
        caption:"Aggiorna/Pulisci",
        button:true,
        flat:true,
        click:function(o){
            objgridroles.where("APPID='"+currappid+"'");
            objgridroles.query({
                orderby:"SYSID",
                ready:function(){
                    objrole_name.focus();
                }
            });
        }
    });

    $("#lbrole_use").rylabel({left:420,top:94,caption:"Usa:"});
    
    $("#lbrole_name").rylabel({left:420,top:122,caption:"Ruolo"});
    objrole_name=$("#txrole_name").rytext({left:490,top:122,width:130,maxlen:30});
    
    $("#lbrole_descr").rylabel({left:420,top:146,caption:"Descr."});
    objrole_descr=$("#txrole_descr").rytext({left:490,top:146,width:130,maxlen:50});
    
    $("#lbrole_as").rylabel({left:420,top:200,caption:"Come:"});
    
    $("#lbrole_action_insert").rylabel({
        left:420,
        top:220,
        caption:"Nuovo ruolo",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_roles.php", 
                {
                    action:"insert",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    role:_ajaxescapize(objrole_name.value()),
                    descr:_ajaxescapize(objrole_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objrole_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbrole_action_update").rylabel({
        left:420,
        top:240,
        caption:"Modifica selezionato",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_roles.php", 
                {
                    action:"update",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    role:_ajaxescapize(currrole),
                    rolenew:_ajaxescapize(objrole_name.value()),
                    descr:_ajaxescapize(objrole_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objrole_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lbrole_or").rylabel({left:420,top:270,caption:"Oppure:"});
    $("#lbrole_action_delete").rylabel({
        left:420,
        top:290,
        caption:"Elimina ruolo",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdelrole"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_roles.php", 
                    {
                        action:"delete",
                        sessionid:_sessionid,
                        app:_ajaxescapize(currapp),
                        role:_ajaxescapize(currrole)
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objrole_refresh.engage();
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
    // FINE TAB RUOLI
    
    // INIZIO TAB RUOLO-UTENTE
    objgridroleuser=$("#gridroleuser").ryque({
        left:10,
        top:55,
        width:250,
        height:300,
        numbered:false,
        checkable:true,
        environ:"ryego",
        from:"EGOVIEWUSERS",
        columns:[
            {id:"USERNAME", caption:"Utenti senza ruolo", width:200, code:"EGO_GRID_OUTROLE"}
        ],
        solveid:function(o,y){
            // Sto aggiungendo
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_roleuser.php", 
                {
                    action:"add",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    role:_ajaxescapize(currrole),
                    users:_ajaxescapize(y)
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objroleusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    objgridroleusersel=$("#gridroleusersel").ryque({
        left:375,
        top:55,
        width:250,
        height:300,
        numbered:false,
        checkable:true,
        environ:"ryego",
        from:"EGOVIEWROLEUSER",
        columns:[
            {id:"USERNAME", caption:"Utenti con ruolo", width:200, code:"EGO_GRID_INROLE"}
        ],
        solveid:function(o,y){
            // Sto togliendo
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_roleuser.php", 
                {
                    action:"remove",
                    sessionid:_sessionid,
                    app:_ajaxescapize(currapp),
                    role:_ajaxescapize(currrole),
                    users:_ajaxescapize(y)
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objroleusr_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    objroleusr_refresh=$("#lbroleusr_refresh").rylabel({
        left:290,
        top:60,
        caption:"Aggiorna",
        button:true,
        flat:true,
        click:function(o){
            if(currapp!="" && currrole!=""){
                var q="";
                var t=objusr_filter.value();
                if(t.length<=6)
                    objroleusr_filter.caption(t+"*");
                else
                    objroleusr_filter.caption(t.substr(0,6)+"...");
                t=t.toUpperCase().replace(" ", "%");
                q="EGOVIEWUSERS.ACTIVE=1 AND EGOVIEWUSERS.ALIASNAME=EGOVIEWUSERS.USERNAME AND NOT EGOVIEWUSERS.USERID IN (SELECT EGOROLEUSER.USERID FROM EGOROLEUSER WHERE EGOROLEUSER.ROLEID='"+currroleid+"')";
                if(t!=""){
                    q+="AND ( [:UPPER(USERNAME)] LIKE '[=USERNAME]%' )";
                }
                objgridroleuser.where(q);
                objgridroleuser.query({
                    args:{
                        "USERNAME":_ajaxescapize( t )
                    },
                    orderby:"USERNAME",
                    ready:function(){
                        q="ROLEID='"+currroleid+"'";
                        if(t!=""){
                            q+="AND ( [:UPPER(USERNAME)] LIKE '[=USERNAME]%' )";
                        }
                        objgridroleusersel.where(q);
                        objgridroleusersel.query({
                            args:{
                                "USERNAME":_ajaxescapize( t )
                            },
                            orderby:"USERNAME"
                        });
                    }
                });
                
            }
        }
    });
    objroleusr_filter=$("#lbroleusr_filter").rylabel({left:290, top:90, caption:""}); 
    $("#lbroleusr_action_add").rylabel({
        left:295,
        top:120,
        caption:"<img src='"+_cambusaURL+"ryego/images/arrow_right.png' style='position:absolute;top:0px;border:none;'>",
        button:true,
        flat:true,
        click:function(o){
            objgridroleuser.selengage();
        }
    });
    $("#lbroleusr_action_remove").rylabel({
        left:295,
        top:160,
        caption:"<img src='"+_cambusaURL+"ryego/images/arrow_left.png' style='position:absolute;top:0px;border:none;'>",
        button:true,
        flat:true,
        click:function(o){
            objgridroleusersel.selengage();
        }
    });
    // FINE TAB RUOLO-UTENTE
    // FINE FORM APPLICAZIONI
    
    // INIZIO FORM LANGUAGES
    objgridlanguages=$("#gridlanguages").ryque({
        left:0,
        top:40,
        width:400,
        height:350,
        numbered:false,
        checkable:false,
        environ:"ryego",
        from:"EGOLANGUAGES",
        columns:[
            {id:"DESCRIPTION", caption:"Lingua", width:200, code:"EGO_GRID_LANG"}
        ],
        changerow:function(o,i){
            objlng_lang.clear();
            objlng_descr.clear();
            currlang="";
            currlangid="";
            if(i>0)
                objgridlanguages.solveid(i);
        },
        solveid:function(o,d){
            RYQUE.query({
                sql:"SELECT NAME,DESCRIPTION FROM EGOLANGUAGES WHERE SYSID='"+d+"'",
                ready:function(v){
                    currlang=v[0].NAME;
                    currlangid=d;
                    objlng_lang.value(v[0].NAME);
                    objlng_descr.value(v[0].DESCRIPTION);
                    if(flaginit){
                        flaginit=false;
                        RYBOX.localize(currlang, missing,
                            function(){
                                postlocalize();
                            }
                        );
                    }
                }
            });
        }
    });
    objlng_refresh=$("#lblng_refresh").rylabel({
        left:420,
        top:40,
        caption:"Aggiorna/Pulisci",
        button:true,
        flat:true,
        click:function(o){
            objgridlanguages.where("");
            objgridlanguages.query({
                orderby:"SYSID",
                ready:function(){
                    objlng_lang.focus();
                }
            });
        }
    });

    $("#lblng_use").rylabel({left:420,top:94,caption:"Usa:"});
    
    $("#lblng_lang").rylabel({left:420,top:122,caption:"Lingua"});
    objlng_lang=$("#txlng_lang").rytext({left:490,top:122,width:160,maxlen:30});
    
    $("#lblng_descr").rylabel({left:420,top:146,caption:"Descr."});
    objlng_descr=$("#txlng_descr").rytext({left:490,top:146,width:160,maxlen:50});
    
    $("#lblng_as").rylabel({left:420,top:200,caption:"Come:"});
    
    $("#lblng_action_insert").rylabel({
        left:420,
        top:220,
        caption:"Nuova lingua",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_langs.php", 
                {
                    action:"insert",
                    sessionid:_sessionid,
                    lang:_ajaxescapize(objlng_lang.value()),
                    descr:_ajaxescapize(objlng_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objlng_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lblng_action_update").rylabel({
        left:420,
        top:240,
        caption:"Modifica selezionata",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            $.post(_cambusaURL+"ryego/egoaction_langs.php", 
                {
                    action:"update",
                    sessionid:_sessionid,
                    lang:_ajaxescapize(currlang),
                    langnew:_ajaxescapize(objlng_lang.value()),
                    descr:_ajaxescapize(objlng_descr.value())
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        sysmessage(v.description, v.success);
                        if(v.success)
                            objlng_refresh.engage();
                    }
                    catch(e){
                        sysmessagehide();
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lblng_action_apply").rylabel({
        left:420,
        top:260,
        caption:"Applica",
        button:true,
        flat:true,
        click:function(o){
            syswaiting();
            RYBOX.localize(currlang, missing,
                function(){
                    $.post(_cambusaURL+"ryego/egoaction_last.php", 
                        {"sessionid":_sessionid,"appid":"","aliasid":_aliasid,"languageid":currlangid}, 
                        function(){
                            postlocalize();
                            sysmessagehide();
                        }
                    );
                }
            );
        }
    });
    $("#lblng_or").rylabel({left:420,top:290,caption:"Oppure:"});
    $("#lblng_action_delete").rylabel({
        left:420,
        top:310,
        caption:"Elimina lingua",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdellang"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_langs.php", 
                    {
                        action:"delete",
                        sessionid:_sessionid,
                        lang:_ajaxescapize(currlang)
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objlng_refresh.engage();
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
    // FINE FORM LANGUAGES
    
    // INIZIO FORM SESSIONI
    objgridsessions=$("#gridsessions").ryque({
        left:0,
        top:40,
        width:400,
        height:350,
        numbered:false,
        checkable:false,
        environ:"ryego",
        from:"EGOVIEWSESSIONS",
        columns:[
            {id:"USERNAME", caption:"Utente", width:180, code:"EGO_GRID_USER"},
            {id:"BEGINTIME", caption:"Inizio", width:180, type:":", code:"EGO_GRID_BEGIN"},
            {id:"ACTIVE", caption:"", width:20, type:"?"}
        ]
    });
    $("#lbses_only").rylabel({left:420,top:40,caption:"Solo attive"});
    objses_only=$("#chkses_only").rycheck({
        left:500,
        top:40,
        assigned:function(o){
            objses_refresh.engage();
        }
    });
    objses_only.value(true);
    objses_filter=$("#lbses_filter").rylabel({left:540, top:40, caption:""}); 
    objses_refresh=$("#lbses_refresh").rylabel({
        left:420,
        top:60,
        caption:"Aggiorna",
        button:true,
        flat:true,
        click:function(o, done){
            var q="";
            var t=objusr_filter.value();
            if(t.length<=6)
                objses_filter.caption(t+"*");
            else
                objses_filter.caption(t.substr(0,6)+"...");
            t=t.toUpperCase().replace(" ", "%");
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(USERNAME)] LIKE '[=USERNAME]%' )";
            }
            if(objses_only.value()){
                if(q!=""){q+=" AND "}
                q+="ENDTIME IS NULL AND [:DATE(RENEWALTIME, 1DAYS)]>[:TODAY()]";
            }
            objgridsessions.where(q);
            objgridsessions.query({
                args:{
                    "USERNAME":_ajaxescapize( t )
                },
                orderby:"SYSID",
                ready:function(){
                    if(done!=missing){done()}
                }
            });
        }
    });
    $("#lbses_action_close").rylabel({
        left:420,
        top:120,
        caption:"Chiudi sessione selezionata",
        button:true,
        flat:true,
        click:function(o){
            var ind=objgridsessions.index();
            if(ind>0){
                syswaiting();
                objgridsessions.solveid(ind, function(o,sid){
                    $.post(_cambusaURL+"ryego/ego_logout.php", 
                        {
                            sessionid:sid
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                sysmessage(v.description, v.success);
                                if(v.success)
                                    objses_refresh.engage();
                            }
                            catch(e){
                                sysmessagehide();
                                alert(d);
                            }
                        }
                    );
                });
            }
            else{
                sysmessage(RYBOX.getbabel("lbselectsession"), 0);
            }
        }
    });
    $("#lbses_action_deleteall").rylabel({
        left:420,
        top:150,
        caption:"Elimina sessioni scadute",
        button:true,
        flat:true,
        click:function(o){
            if(confirm(RYBOX.getbabel("lbconfirmdelsessions"))){
                syswaiting();
                $.post(_cambusaURL+"ryego/egoaction_sessions.php", 
                    {
                        action:"deleteall",
                        sessionid:_sessionid
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description, v.success);
                            if(v.success)
                                objses_refresh.engage();
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
    // FINE FORM SESSIONI
    
    // INIZIO FORM PASSWORD
    $("#lbcurrpwd").rylabel({left:20, top:100, caption:"Password attuale"});
    objcurrpwd=$("#txcurrpwd").rytext({ 
        left:180,
        top:100, 
        password:true,
        maxlen:16
    });
    $("#lbnewpwd").rylabel({left:20, top:130, caption:"Nuova password"});
    objnewpwd=$("#txnewpwd").rytext({ 
        left:180,
        top:130, 
        password:true,
        maxlen:16
    });
    $("#lbrepeatpwd").rylabel({left:20, top:160, caption:"Ripeti password"});
    objrepeatpwd=$("#txrepeatpwd").rytext({ 
        left:180,
        top:160, 
        password:true,
        maxlen:16
    });
    $("#actionPassword").rylabel({
        left:400,
        top:160,
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
    // FINE FORM PASSWORD

    // CARICAMENTO DATI
    syswaiting();
    setTimeout("loading()",500);
}
function postlocalize(){
    var t;
    
    t=RYBOX.getbabel("lbside_settings");
    $("#side_settings").html(t);
    $("#settings .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_users");
    $("#side_users").html(t);
    $("#users .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_applications");
    $("#side_applications").html(t);
    $("#applications .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_languages");
    $("#side_languages").html(RYBOX.getbabel("lbside_languages"));
    $("#languages .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_sessions");
    $("#side_sessions").html(RYBOX.getbabel("lbside_sessions"));
    $("#sessions .form-title").html(t.toUpperCase());
    
    t=RYBOX.getbabel("lbside_changepassword");
    $("#side_changepassword").html(RYBOX.getbabel("lbside_changepassword"));
    $("#changepassword .form-title").html(t.toUpperCase());

    t=RYBOX.getbabel("lbauthenticationservice");
    $("title").html(t);
    $("#egotitle").html(t);
    
    $("#tabapplications").html(RYBOX.getbabel("lbtabapplications"));
    $("#tabenvirons").html(RYBOX.getbabel("lbtabenvirons"));
    $("#tabenvusers").html(RYBOX.getbabel("lbtabenvusers"));
    $("#tabroles").html(RYBOX.getbabel("lbtabroles"));
    $("#tabroleusers").html(RYBOX.getbabel("lbtabroleusers"));
}
// CARICAMENTO MASCHERA
function loading(){
    RYQUE.request({
        environ:"ryego",
        ready:function(id){
            RYQUE.query({   // Caricamento Settings
                sql:"SELECT NAME,VALUE FROM EGOSETTINGS",
                ready:function(v){
                    for(var i in v){
                        switch(v[i].NAME){
                            case "duration":
                                objoptduration.value(v[i].VALUE);
                                break;
                            case "warning":
                                objoptwarning.value(v[i].VALUE);
                                break;
                            case "minlen":
                                objoptminlen.value(v[i].VALUE);
                                break;
                            case "default":
                                objoptdefault.value(v[i].VALUE);
                                break;
                            case "upperlower":
                                objoptupperlower.value(v[i].VALUE);
                                break;
                            case "letterdigit":
                                objoptletterdigit.value(v[i].VALUE);
                                break;
                            case "saveuser":
                                objoptsaveuser.value(v[i].VALUE);
                                break;
                            case "emailreset":
                                objoptemailreset.value(v[i].VALUE);
                                break;
                        }
                    }
                    objgridusers.query({    // Caricamento Users
                        where:"ACTIVE=1",
                        ready:function(v){
                            objgridapplications.query({     // Caricamento Applications
                                where:"",                        
                                ready:function(v){
                                    objgridlanguages.query({     // Caricamento Languages
                                        where:"",                        
                                        ready:function(v){
                                            objses_refresh.engage(
                                                function(){
                                                    RYQUE.query({   // Caricamento Setup per la lingua
                                                        sql:"SELECT LANGUAGEID FROM EGOSETUP WHERE APPID='' AND ALIASID='"+_aliasid+"'",
                                                        ready:function(v){
                                                            if(v.length>0){
                                                                objgridlanguages.selbyid(v[0]["LANGUAGEID"], true,
                                                                    function(){
                                                                        sysmessagehide();
                                                                    }
                                                                );
                                                            }
                                                            else{
                                                                sysmessagehide();
                                                            }
                                                        }
                                                    });
                                                }
                                            );
                                        }
                                    });
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
        objgridusers.dispose(
            function(){
                objgridapplications.dispose(
                    function(){
                        objgridenvirons.dispose(
                            function(){
                                objgridenvuser.dispose(
                                    function(){
                                        objgridenvusersel.dispose(
                                            function(){
                                                objgridroles.dispose(
                                                    function(){
                                                        objgridroleuser.dispose(
                                                            function(){
                                                                objgridroleusersel.dispose(
                                                                    function(){
                                                                        objgridlanguages.dispose(
                                                                            function(){
                                                                                objgridsessions.dispose(
                                                                                    function(){
                                                                                        objgridsessions.dispose(
                                                                                            function(){
                                                                                                RYQUE.dispose(
                                                                                                    function(){
                                                                                                        if(lout){logout()}
                                                                                                    }
                                                                                                );
                                                                                            }
                                                                                        );
                                                                                    }
                                                                                );
                                                                            }
                                                                        );
                                                                    }
                                                                );
                                                            }
                                                        );
                                                    }
                                                );
                                            }
                                        );
                                    }
                                );
                            }
                        );
                    }
                );
            }
        );
    }
    catch(e){}
}
</script>
