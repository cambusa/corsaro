/****************************************************************************
* Name:            rudder/rudder.js                                         *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.00                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_rudder(settings,missing){
    var formid=RYWINZ.addform(this);
    var propenviron="default";
    var proproot="";
    
    if(settings.environ!=missing){propenviron=settings.environ}
    if(settings.root!=missing){proproot=settings.root}
    if(proproot=="")
        proproot=propenviron;
    
    $("#"+formid+"menu").rysource({
        environ:propenviron,
        root:proproot,
        left:10,
        top:20,
        width:"98%",
        height:"90%",
        scroll:0,
        startup:"rudder_startup",
        sessionid:_sessioninfo.sessionid,
        dbenv:_sessioninfo.environ
    });
}

function rudder_startup(par){
    try{
        _openingparams=_stringify(par);;
        RYWINZ.newform(par);
    }
    catch(e){}
}

