/****************************************************************************
* Name:            qvsilverlight.js                                         *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvsilverlight(settings,missing){
    var formid=RYWINZ.addform(this);
    var sl_source="";
    var sl_width=730;
    var sl_height=400;
    
    if($.isset(settings["width"])){
        sl_width=settings["width"].actualInteger();
    }
    
    if($.isset(settings["height"])){
        sl_height=settings["height"].actualInteger();
    }
    
    // DETERMINO IL PERCORSO DELL'APPLICAZIONE
    if($.isset(settings["source"])){
        sl_source=settings["source"];
        sl_source=sl_source.replace(/@cambusa\//gi, _systeminfo.relative.cambusa);
        sl_source=sl_source.replace(/@apps\//gi, _systeminfo.relative.apps);
        sl_source=sl_source.replace(/@customize\//gi, _systeminfo.relative.customize);
        sl_source=_systeminfo.relative.cambusa+"rygeneral/sl_wrapper.php?source="+sl_source+"&formid="+formid+"&sessionid="+_sessioninfo.sessionid+"&env="+_sessioninfo.environ+"&userid="+_sessioninfo.userid+"&root="+_systeminfo.web.root;
        if(window.console&&_sessioninfo.debugmode){console.log("Silverlight source: "+sl_source)}
        $("#"+formid+"iframe iframe").attr({width:sl_width}).attr({height:sl_height}).attr("src", sl_source);
    }
}
