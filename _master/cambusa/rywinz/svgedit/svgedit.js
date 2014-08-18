/****************************************************************************
* Name:            svgedit/svgedit.js                                       *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.00                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_svgedit(settings,missing){
    var formid=RYWINZ.addform(this);
    $("#"+formid+"iframe iframe").attr("src", _cambusaURL+"svgedit/svg-editor.html");
    this._resize=function(w,h){
        $("#"+formid+"iframe iframe").width(w-30).height(h-80);
    }
}
