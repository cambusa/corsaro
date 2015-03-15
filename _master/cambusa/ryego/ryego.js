/****************************************************************************
* Name:            ryego.js                                                 *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var RYEGO;
function ryEgo(missing){
    var propappname="";
    var propapptitle="";
    var propappenv="";
    var propcrossdomain="";
    var propformlogin="ryego.php";
    var propconfig=function(){};
    this.appname=function(){
        return propappname;
    }
    this.apptitle=function(){
        return propapptitle;
    }
    this.appenv=function(){
        return propappenv;
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
        if(settings.apptitle!=missing){propapptitle=settings.apptitle}
        if(settings.appenv!=missing){propappenv=settings.appenv}
        if(settings.crossdomain!=missing){propcrossdomain=settings.crossdomain}
        if(settings.formlogin!=missing){propformlogin=settings.formlogin}
        if(settings.config!=missing){propconfig=settings.config}
        if(_sessionid!=""){
            if(propcrossdomain==""){
                var bs="\\";
                $.post(_cambusaURL+"ryego/ego_infosession.php",{sessionid:_sessionid,app:propappname,backslash:bs},
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success){
                                egoconfig(v);
                            }
                            else{
                                $.cookie("egosessionid", "");
                                location.replace(location.pathname);
                            }
                        }
                        catch(e){
                            $.cookie("egosessionid", "");
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
        $.cookie("egosessionid", "");
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
        if(v.success){
            egoconfig(v);
        }
        else{
            $.cookie("egosessionid", "");
            location.replace(location.pathname);
        }
    }
    catch(e){
        $.cookie("egosessionid", "");
        location.replace(location.pathname);
    }
}
function egoconfig(v){
    if(_sessionid==""){
        var xdom=RYEGO.crossdomain();
        $("body").html("<form id='egologon'><input type='hidden' id='app' name='app'><input type='hidden' id='title' name='title'><input type='hidden' id='url' name='url'><input type='hidden' id='env' name='env'></form>");
        if(xdom=="")
            $("#egologon").attr({method:"POST", action:_cambusaURL+"ryego/"+RYEGO.formlogin()});
        else
            $("#egologon").attr({method:"GET", action:xdom+"cambusa/ryego/"+RYEGO.formlogin()});
        $("#app").val(RYEGO.appname());
        $("#title").val(RYEGO.apptitle());
        $("#url").val(location.href);
        $("#env").val(RYEGO.appenv());
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
