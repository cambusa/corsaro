/****************************************************************************
* Name:            ryego.js                                                 *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var RYEGO;
function ryEgo(missing){
    var propappname="";
    var propcrossdomain="";
    var propformlogin="ryego.php";
    var propconfig=function(){};
    this.appname=function(){
        return propappname;
    }
    this.crossdomain=function(){
        return propcrossdomain;
    }
    this.formlogin=function(){
        return propformlogin;
    }
    this.config=function(v){
        propconfig(v);
    }
    this.go=function(settings){
        if(settings.appname!=missing){propappname=settings.appname}
        if(settings.crossdomain!=missing){propcrossdomain=settings.crossdomain}
        if(settings.formlogin!=missing){propformlogin=settings.formlogin}
        if(settings.config!=missing){propconfig=settings.config}
        if(_sessionid!=""){
            if(propcrossdomain==""){
                $.post(_cambusaURL+"ryego/ego_infosession.php",{sessionid:_sessionid,app:propappname},
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success)
                                egoconfig(v);
                            else
                                location.replace(location.pathname);
                        }
                        catch(e){
                            location.replace(location.pathname);
                        }
                    }
                );
            }
            else{
                _jsonp(propcrossdomain+"cambusa/ryego/ego_infosession.php/?sessionid="+_sessionid+"&app="+propappname+"&padding=validatesession");
            }
        }
        else{
            egoconfig();
        }
    
    }
    this.logout=function(){
        if(_sessionid!=""){
            if(propcrossdomain==""){
                $.post(_cambusaURL+"ryego/ego_logout.php", {sessionid:_sessionid}, function(){
                    _sessionid="";
                    location.replace(location.pathname);
                });
            }
            else{
                $.ajax({
                    url: propcrossdomain+"cambusa/ryego/ego_logout.php/?sessionid="+_sessionid
                }).always(function(){
                    _sessionid="";
                    location.replace(location.pathname);
                });
            }
        }
    }
}
function validatesession(v){
    try{
        if(v.success)
            egoconfig(v);
        else
            location.replace(location.pathname);
    }
    catch(e){
        location.replace(location.pathname);
    }
}
function egoconfig(v){
    if(_sessionid==""){
        var xdom=RYEGO.crossdomain();
        if(xdom==""){
            $("body").html("<form id='egologon' method='POST' action=''><input type='hidden' id='app' name='app'><input type='hidden' id='url' name='url'></form>");
            $("#egologon").attr({action:_cambusaURL+"ryego/"+RYEGO.formlogin()});
        }
        else{
            $("body").html("<form id='egologon' method='GET' action=''><input type='hidden' id='app' name='app'><input type='hidden' id='url' name='url'></form>");
            $("#egologon").attr({action:xdom+"cambusa/ryego/"+RYEGO.formlogin()});
        }
        $("#app").val(RYEGO.appname());
        $("#url").val(location.href);
        $("#egologon").submit();
    }
    else{
        try{
            RYEGO.config(v);
        }
        catch(e){}
    }
}
$(document).ready(function(){
    RYEGO=new ryEgo();
});
