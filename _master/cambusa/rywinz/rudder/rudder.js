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
function class_rudder(settings, missing){
    var formid=RYWINZ.addform(this, settings);
    var propenviron="default";
    var proproot="";
    
    if(settings.environ!=missing){propenviron=settings.environ}
    if(settings.root!=missing){proproot=settings.root}
    if(proproot=="")
        proproot=propenviron;
    
    var objtabs=$( "#"+formid+"tabs" ).rytabs({});
    
    var offset=20;
    if( objtabs.closable() )
        offset=40;
    else
        objtabs.visible(0);
    
    $("#"+formid+"menu").rysource({
        environ:propenviron,
        root:proproot,
        left:10,
        top:offset,
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
