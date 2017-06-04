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
//var chksetup=false;
var flagsetup=false;
var nosetup=false;
$(document).ready(function(){
    var offsety=100;
    $("#lbalias").rylabel({left:60,top:offsety,caption:"Utente",width:120,align:"right"});
    user=$("#txalias").rytext({left:190,top:offsety,width:200,maxlen:30});
    user.value(_egouser);
    
    offsety+=30;
    $("#lbpwd").rylabel({left:60,top:offsety,caption:"Password",width:120,align:"right"});
    pwd=$("#txpwd").rytext({ 
        left:190,
        top:offsety, 
        password:true,
        maxlen:16,
        enter:function(o){
			syswaitinglogin();
            var u=user.value();
            var m=encryptString( pwd.value() );
            var b="";
            if(_validator!="ego"){
                b=bareString( pwd.value() );
            }
            $.post("ego_begin.php", {"user":u, "pwd":m, "barepwd":b, "app":_appname, "env":_castenv},
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success){
<?php 
    if($appname!=""){
?>
                            if(v.expiry==2){
                                //if(chksetup.tag=="nosetup"){
								if(nosetup){
                                    sysmessage(RYBOX.babels("EGO_MSG_SETCHANGEPWD"), 0);
                                    return;
                                }
                            }
                            if(v.expiry>0)
								flagsetup=true;
                            //    chksetup.value(1);
                            //if(chksetup.value()){   // Applicazione esterna con setup
							if(flagsetup){			// Applicazione esterna con setup
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
    offsety+=50;
    $("#lblogin").rylabel({
        left:190,
        top:offsety,
		width:60,
        caption:"Login",
        button:true,
        click:function(o){
            pwd.engage();
        }
    });
<?php 
    if($appname!=""){
?>
    if($("#lbsetup").length>0){
        // POTREBBE NON ESISTERE IL DIV IN CASO DI EGOEMBED
        $("#lbsetup").rylabel({
			left:270,
			top:offsety,caption:"Setup",
			width:60,
			button:true,
			//flat:true,
			click:function(o){
				flagsetup=true;
				pwd.engage();
			}
		});
		/*
        chksetup=$("#chksetup").rycheck({
            left:190,
            top:offsety,
            assigned:function(o){
                if(o.value()){
                    pwd.engage();
                }
            }
        });
		*/
        if(_setuponly){
            //chksetup.value(1);
            //chksetup.enabled(0);
			flagsetup=true;
        }
    }
    else{
		nosetup=true;
		/*
        // CREO UN OGGETTO FAKE CON FUNZIONE VALUE
        chksetup={
            "value":function(v){
                return 0;
            },
            "tag":"nosetup"
        };
		*/
    }
<?php 
    }
?>
    offsety+=40;
    $("#lbreset").rylabel({
        left:190,
        top:offsety,
        caption:"Password dimenticata?",
        button:true,
        flat:true,
        click:function(o){
            var u=user.value();
            if(u!=""){
                if(confirm(RYBOX.getbabel("lbsendpwd", [u]))){
                    $.post("egorequest_reset.php", {"user":u},
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
        "EGO_MSG_SETCHANGEPWD":"Cambiare password con la funzione di Setup!",
        "EGO_COOKIEPOLICY":"Questo sito fa uso di cookie tecnici non finalizzati alla raccolta di dati personali. Puoi approfondire leggendo la {1}policy sui cookie{2}. {3}Ho letto{4}"
    });
    RYBOX.localize(_egolanguage, missing,
        function(){
			/*
            if(supportsCookies()&&_egocontext!="embed"){
                if(!$.cookie("EGOCOOKIE")){
                    var cp=RYBOX.babels("EGO_COOKIEPOLICY", ["<a href='ego_privacy.php' target='_blank'>", "</a>", "<span>", "</span>"]);
                    $("body").append("<div id='filibuster-privacycookie'>"+cp+"</div>");
                    $("#filibuster-privacycookie span").click(
                        function(){
                            removePrivacyCookie();
                        }
                    );
                }
            }
			*/
            if(_egolanguage!="default"){
                try{
                    var t=RYBOX.getbabel("lbauthenticationservice");
                    $("#egotitle").html(t);
                    $("title").html(t);
                }catch(e){}
            }
            if(_egocontext=="default")
                $("#txalias_anchor").focus();
        }
    );
}
function egoterminate(lout){
    //
}
function egoautomatic(u, p){

    /* UTILIZZO DA VISUAL STUDIO
    Private Sub WebBrowser1_DocumentCompleted(sender As Object, e As WebBrowserDocumentCompletedEventArgs) Handles WebBrowser1.DocumentCompleted

        Dim args() As String = New String() {"demiurge", "bustro"}

        WebBrowser1.Document.InvokeScript("egoautomatic", args)


    End Sub
	*/

	try{
		$("#progressmask").addClass("progressmaskopaque");
		user.value(u);
		pwd.value(p);
		setTimeout(function(){
			pwd.engage();
		});
	}
	catch(e){
		sysmessage(e.message); 
	}
}
</script>
