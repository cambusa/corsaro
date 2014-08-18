<?php
/****************************************************************************
* Name:            egoform_login.php                                        *
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
var user;
var pwd;
var chksetup;
var _egouser="<?php print $egouser ?>";
var returnurl="<?php  print $returnurl ?>";
var _appname="<?php  print $appname ?>";
$(document).ready(function(){
    $("#lbalias").rylabel({left:120,top:100,caption:"User"});
    user=$("#txalias").rytext({left:200,top:100,maxlen:30});
    user.value(_egouser);
    
    $("#lbpwd").rylabel({left:120,top:130,caption:"Password"});
    pwd=$("#txpwd").rytext({ 
        left:200,
        top:130, 
        password:true,
        maxlen:16,
        enter:function(o){
            var u=user.value();
            var m=encryptString( pwd.value() );
            $.post("ego_begin.php", {"user":_ajaxescapize(u),"pwd":m,"app":_ajaxescapize(_appname)},
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success){
<?php 
    if($appname!=""){
?>
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
                                $("#nextaction").attr({action:"ryego.php"});
                                $("#app").val(_appname);
                                $("#url").val(returnurl);
                                $("#method").val("<?php  print $egomethod ?>");
                                $("#sessionid").val(v.sessionid);
                                $("#appid").val(v.appid);
                                $("#userid").val(v.userid);
                                $("#aliasid").val(v.aliasid);
                                $("#msk").val("setup");
                                $("#expiry").val(v.expiry);
                                $("#nextaction").submit();
                            }
                            else{   // Applicazione esterna senza setup
                                $("body").html("<form id='nextaction' method='<?php  print $egomethod ?>' action=''></form>");
                                $("#nextaction").append("<input type='hidden' id='sessionid' name='sessionid'>");
                                $("#nextaction").attr({action:returnurl});
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
                            $("#nextaction").append("<input type='hidden' id='msk' name='msk'>");
                            $("#nextaction").attr({action:"ryego.php"});
                            $("#app").val("");
                            $("#sessionid").val(v.sessionid);
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
        top:130,
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
    
    $("#lbsetup").rylabel({left:120,top:160,caption:"Setup"});
    chksetup=$("#chksetup").rycheck({
        left:200,
        top:160,
        assigned:function(o){
            if(o.value()){
                pwd.engage();
            }
        }
    });
<?php 
    }
?>
    $("#lbreset").rylabel({
        left:200,
        top:190,
        caption:"Password dimenticata?",
        button:true,
        flat:true,
        click:function(o){
            var u=user.value();
            if(u!=""){
                if(confirm("Reimpostare la password dell'utente "+u+"?")){
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
                sysmessage("Inserire un nome utente o alias",0);
            }
        }
    });
    
    $("#txalias_anchor").focus();
});
function egoterminate(lout){
    //
}
</script>
