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
        width:$.browser.mobile ? 200 : 400,
        bottom:20,
        scroll:false,
		zoom:$.browser.mobile ? 1.50 : "",
        border:false,
        classname:"treeview-transparent",
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
    RYBOX.babels({
        "WINZ_PILOTA":$("#WINZ_PILOTA").html(),
        "WINZ_POSTMAN":$("#WINZ_POSTMAN").html(),
        "WINZ_LOGOUT":$("#WINZ_LOGOUT").html(),
        "WINZ_TOOLS":$("#WINZ_TOOLS").html()
    });
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            // TRADUZIONE PILOTA
            PILOTA.title=RYBOX.babels("WINZ_PILOTA");
            RYWINZ.Title(formid, PILOTA.title);
            $("#WINZ_PILOTA").html(PILOTA.title);
            // TRADUZIONE POSTMAN
            POSTMAN.title=RYBOX.babels("WINZ_POSTMAN");
            $("#WINZ_POSTMAN").html(POSTMAN.title);
            // TRADUZIONE LOGOUT
            $("#WINZ_LOGOUT").html(RYBOX.babels("WINZ_LOGOUT"));
            // TRADUZIONE STRUMENTI
            $("#WINZ_TOOLS").html(RYBOX.babels("WINZ_TOOLS"));
        }
    );
}
