<?php
/****************************************************************************
* Name:            egoform_login.php                                        *
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
var _egolanguage="<?php print $egolanguage ?>";
var _egouser="<?php print $egouser ?>";
var user;
var pwd;
var chksetup;
$(document).ready(function(){
    var offsety=100;
    $("#lbalias").rylabel({left:120,top:offsety,caption:"Utente"});
    user=$("#txalias").rytext({left:200,top:offsety,maxlen:30});
    user.value(_egouser);
    
    offsety+=30;
    $("#lbpwd").rylabel({left:120,top:offsety,caption:"Password"});
    pwd=$("#txpwd").rytext({ 
        left:200,
        top:offsety, 
        password:true,
        maxlen:16,
        enter:function(o){
            var u=user.value();
            var m=encryptString( pwd.value() );
            $.post("ego_begin.php", {"user":_ajaxescapize(u), "pwd":m, "app":_ajaxescapize(_appname), "env":_ajaxescapize(_castenv)},
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success){
<?php 
    if($appname!=""){
?>
                            if(v.expiry==2){
                                if(chksetup.tag=="nosetup"){
                                    sysmessage(RYBOX.babels("EGO_MSG_SETCHANGEPWD"), 0);
                                    return;
                                }
                            }
                            if(v.expiry>0)
                                chksetup.value(1);
                            if(chksetup.value()){   // Applicazione esterna con setup
                                $("body").html("<form id='nextaction' method='POST' action=''></form>");
                                $("#nextaction").append("<input type='hidden' id='app' name='app'>");
                                $("#nextaction").append("<input type='hidden' id='url' name='url'>");
                                $("#nextaction").append("<input type='hidden' id='method' name='method'>");
                                $("#nextaction").append("<input type='hidden' id='sessionid' name='sessionid'>");
                                $("#nextaction").append("<input type='hidden' id='appid' name='appid'>");
                                $("#nextaction").append("<input type='hidden' id='userid' name='userid'>");
                                $("#nextaction").append("<input type='hidden' id='aliasid' name='aliasid'>");
                                $("#nextaction").append("<input type='hidden' id='msk' name='msk'>");
                                $("#nextaction").append("<input type='hidden' id='expiry' name='expiry'>");
                                $("#nextaction").append("<input type='hidden' id='setuponly' name='setuponly'>");
                                $("#nextaction").attr({action:"ryego.php"});
                                $("#app").val(_appname);
                                $("#url").val(_returnURL);
                                $("#method").val(_egomethod);
                                $("#sessionid").val(v.sessionid);
                                $("#appid").val(v.appid);
                                $("#userid").val(v.userid);
                                $("#aliasid").val(v.aliasid);
                                $("#msk").val("setup");
                                $("#expiry").val(v.expiry);
                                $("#setuponly").val(_setuponly);
                                $("#nextaction").submit();
                            }
                            else{   // Applicazione esterna senza setup
                                $("body").html("<form id='nextaction' method='"+_egomethod+"' action=''></form>");
                                $("#nextaction").append("<input type='hidden' id='sessionid' name='sessionid'>");
                                $("#nextaction").attr({action:_returnURL});
                                $("#sessionid").val(v.sessionid);
                                $("#nextaction").submit();
                            }
<?php
    }
    else{ // Setup di Ego
?>
                            $("body").html("<form id='nextaction' method='POST' action=''></form>");
                            $("#nextaction").append("<input type='hidden' id='app' name='app'>");
                            $("#nextaction").append("<input type='hidden' id='sessionid' name='sessionid'>");
                            $("#nextaction").append("<input type='hidden' id='aliasid' name='aliasid'>");
                            $("#nextaction").append("<input type='hidden' id='msk' name='msk'>");
                            $("#nextaction").attr({action:"ryego.php"});
                            $("#app").val("");
                            $("#sessionid").val(v.sessionid);
                            $("#aliasid").val(v.aliasid);
                            $("#msk").val("setup");
                            $("#nextaction").submit();
<?php 
    } 
?>
                        }
                        else{
                            sysmessage(v.description,0);
                        }
                    }
                    catch(e){
                        alert(d);
                    }
                }
            );
        }
    });
    $("#lblogin").rylabel({
        left:420,
        top:offsety+2,
        caption:"Login",
        button:true,
        flat:true,
        click:function(o){
            pwd.engage();
        }
    });
<?php 
    if($appname!=""){
?>
    if($("#lbsetup").length>0){
        // POTREBBE NON ESISTERE IL DIV IN CASO DI EGOEMBED
        offsety+=30;
        $("#lbsetup").rylabel({left:120,top:offsety,caption:"Setup"});
        chksetup=$("#chksetup").rycheck({
            left:200,
            top:offsety,
            assigned:function(o){
                if(o.value()){
                    pwd.engage();
                }
            }
        });
        if(_setuponly){
            chksetup.value(1);
            chksetup.enabled(0);
        }
    }
    else{
        // CREO UN OGGETTO FAKE CON FUNZIONE VALUE
        chksetup={
            "value":function(v){
                return 0;
            },
            "tag":"nosetup"
        };
    }
<?php 
    }
?>
    offsety+=30;
    $("#lbreset").rylabel({
        left:200,
        top:offsety,
        caption:"Password dimenticata?",
        button:true,
        flat:true,
        click:function(o){
            var u=user.value();
            if(u!=""){
                if(confirm(RYBOX.getbabel("lbsendpwd", [u]))){
                    $.post("egorequest_reset.php", {"user":_ajaxescapize(u)},
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                sysmessage(v.description,v.success);
                            }
                            catch(e){
                                alert(d);
                            }
                        }
                    );
                }
            }
            else{
                sysmessage(RYBOX.getbabel("lbmandatoryuser"), 0);
            }
        }
    });
    egoinitialize();
});
function egoinitialize(missing){
    $("#lbauthenticationservice").rylabel({caption:"Servizio di autenticazione"});
    $("#lbsendpwd").rylabel({caption:"Reimpostare la password dell'utente {1}?"});
    $("#lbmandatoryuser").rylabel({caption:"Inserire un nome utente o alias"});
    RYBOX.babels({
        "EGO_NEWACCOUNT":"Registrare un nuovo account con i dati immessi?",
        "EGO_MSG_SETCHANGEPWD":"Cambiare password con la funzione di Setup!"
    });
    if(_egolanguage!="default"){
        RYBOX.localize(_egolanguage, missing,
            function(){
                var t=RYBOX.getbabel("lbauthenticationservice");
                $("title").html(t);
                $("#egotitle").html(t);
                if(_egocontext=="default")
                    $("#txalias_anchor").focus();
            }
        );
    }
    else{
        if(_egocontext=="default")
            $("#txalias_anchor").focus();
    }
}
function egoterminate(lout){
    //
}
</script>
