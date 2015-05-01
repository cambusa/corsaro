/****************************************************************************
* Name:            svgedit/svgedit.js                                       *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_svgedit(settings,missing){
    var formid=RYWINZ.addform(this);
    $("#"+formid+"iframe iframe").attr("src", _systeminfo.relative.cambusa+"svgedit/svg-editor.html");
    this._resize=function(metrics){
        $("#"+formid+"iframe iframe").width(metrics.window.width-30).height(metrics.window.height-80);
    }
}
