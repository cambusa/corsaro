/****************************************************************************
* Name:            rudder/rudder.js                                         *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
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
        scroll:false,
        border:false,
        startup:function(par){
            try{
                RYWINZ.Shell(par);
            }
            catch(e){}
        },
        sessionid:_sessioninfo.sessionid,
        dbenv:_sessioninfo.environ
    });
    RYWINZ.KeyTools(formid);
}
