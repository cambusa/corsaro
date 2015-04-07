/****************************************************************************
* Name:            rywshared.js                                             *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var RYWINZ;
var RYQUEAUX=new ryQue();
var _openingid="";
var _openingname="";
var _openingparams="({})";
var _winzprogrid=0;
var _globalforms=new Object();
var _dialogcount=0;

// Preload avanzamento
var _preloadProgress=new Image();
_preloadProgress.src=_cambusaURL+"rybox/images/progress.gif";

function raiseLoad(n){
    try{
        if(RYWINZ.forms(n)._load){
            RYWINZ.forms(n)._load();
        }
    }catch(e){}
}
function raiseUnload(n){
    var ok=true;
    if(RYWINZ.modified(n) || RYWINZ.busy(n))
        ok=false;
    if(ok==false)
        ok=confirm(RYBOX.babels("MSG_CONFIRMEXIT"));
    if(ok){
        try{
            var f=RYWINZ.forms(n);
            var r=true;
            if(f._unload){
                if(r=f._unload())
                    winzAbort(n);
            }
            if(r){
                // TOLGO DA CONTEGGIO TOTALE QUELLO DELLE DIALOG APERTE DEL FORM
                _dialogcount-=f.opens;
            }
            return r;
        }catch(e){return true}
    }
    else{
        return false;
    }
}
function raiseResize(n){
    try{
        var m=$("#window_"+n);
        var w=m.width();
        var h=m.height();
        //$("#hanger_"+n).height( h-55 );
        if(RYWINZ.forms(n)._kresize){
            RYWINZ.forms(n)._kresize(w,h);
        }
        if(RYWINZ.forms(n)._resize){
            RYWINZ.forms(n)._resize(w,h);
        }
    }catch(e){}
}
function raiseControlKey(k){
    var n="",fn="";
    //if($.browser.opera || $.browser.chrome ? k.ctrlKey : k.altKey){
    if(k.altKey){
        n=winzActiveForm();
        if(n!=""){
            switch(k.which){
                case 220:    // Alt-Backslash
                    fn="_tool_selection";
                    break;
                case 49:    // Alt-1
                    fn="_tool_context";
                    break;
                case 50:    // Alt-2
                    break;
                case 51:    // Alt-3
                    fn="_tool_gotorudder";
                    break;
                case 52:    // Alt-4
                    fn="_tool_formclose";
                    break;
                case 53:    // Alt-5
                    fn="_tool_refresh";
                    break;
                case 54:    // Alt-6
                    fn="_tool_tabprevious";
                    break;
                case 55:    // Alt-7
                    fn="_tool_tabnext";
                    break;
                case 56:    // Alt-8
                    fn="_tool_formnext";
                    break;
                case 57:    // Alt-9
                    fn="_tool_new";
                    break;
                case 48:    // Alt-0
                    fn="_tool_engage";
                    break;
            }
            if(fn!=""){
                var f=RYWINZ.forms(n);
                if(_isset( f[fn] )){
                    try{
                        f[fn]();
                    }catch(e){}
                }
            }
        }
        return (fn=="");
    }
    else
        return true;
}
function winzKeyTools(formid, tabs, settings, missing){
    var frm=RYWINZ.forms(formid);
    var tabselection=1, tabcontext=2, tabnew=1, tabengage=2;
    /*********************************
    | VALORI IN SETTINGS
    | tabselection
    |   xbrowser
    |   xrefresh
    | tabcontext
    |   xfocus
    | tabnew
    |   xnew
    | tabengage
    |   xengage
    *******************************/
    
    if(settings==missing){
        settings={};
    }
    if(settings.tabselection!=missing){tabselection=settings.tabselection}
    if(settings.tabcontext!=missing){tabcontext=settings.tabcontext}
    if(settings.tabnew!=missing){tabnew=settings.tabnew}
    if(settings.tabengage!=missing){tabengage=settings.tabengage}

    // AGGIUNGO LE FUNZIONI DI SPOSTAMENTO SUL TABS
    frm._tool_tabnext=function(){
        if(tabs!=missing)
            tabs.next();
    }
    frm._tool_tabprevious=function(){
        if(tabs!=missing)
            tabs.previous();
    }
    frm._tool_formclose=function(){
        setTimeout(function(){
            RYWINZ.formclose(formid);
        }, 100);
    }
    frm._tool_formnext=function(){
        var n="";
        var f=RYWINZ.forms();
        var t=false;
        var p="";
        for(var i in f){
            if(p=="")
                p=f[i].id;
            if(t){
                n=f[i].id;
                break
            }
            if(f[i].id==formid){
                t=true;
            }
        }
        if(t && n==""){
            n=p;
        }
        if(n!=""){
            RYWINZ.BringToFront(n);
        }
    }
    frm._tool_gotorudder=function(){
        RYWINZ.BringToFront("rudder");
    }
    if(tabs!=missing){
        if(tabselection>0){
            frm._tool_selection=function(){
                try{
                    if( tabs.currtab()!=tabselection ){
                        tabs.currtab(tabselection);
                        if(settings.xbrowser!=missing){
                            if(typeof settings.xbrowser==="string")
                                castFocus(formid+settings.xbrowser);
                            else
                                castFocus(settings.xbrowser.id);
                        }
                    }
                }catch(e){}
            }
            frm._tool_refresh=function(){
                try{
                    if( tabs.currtab()==tabselection ){
                        if(settings.xrefresh!=missing){
                            settings.xrefresh.engage();
                        }
                    }
                }catch(e){}
            }
        }
        if(tabcontext>0){
            frm._tool_context=function(){
                try{
                    if( tabs.currtab()!=tabcontext ){
                        tabs.currtab(tabcontext);
                        if(settings.xfocus!=missing){
                            if(typeof settings.xfocus==="string")
                                castFocus(formid+settings.xfocus);
                            else
                                castFocus(settings.xfocus.id);
                        }
                    }
                }catch(e){}
            }
        }
        if(tabnew>0){
            if(settings.xnew!=missing){
                frm._tool_new=function(){
                    try{
                        if( tabs.currtab()==tabnew ){
                            settings.xnew.engage();
                        }
                    }catch(e){}
                }
            }
        }
        if(tabengage>0){
            if(settings.xengage!=missing){
                frm._tool_engage=function(){
                    try{
                        if( tabs.currtab()==tabengage ){
                            settings.xengage.engage();
                        }
                    }catch(e){}
                }
            }
        }
    }
}
function winzActiveForm(){
    var m=-1,i=-1,n="";
    for(var id in _globalforms){
        i=parseInt($("#window_"+id).css('z-index'));
        if(i>m){
            m=i;
            n=id;
        }
    }
    return n;
}
function winzProgress(formid){
    var f=_globalforms[formid];
    if(f.timeid!==false){
        clearTimeout(f.timeid);
        f.timeid=false;
    }
    $("#message_"+formid).html("<img style='margin:3px;' src='"+_cambusaURL+"rybox/images/progress.gif'>");
    winzDither(formid, true);
}
function winzTimeoutMess(formid, type, mess, data, milly, missing){
    var f=_globalforms[formid];
    f.jqxhr=false;
    if(f.timeid!==false){
        clearTimeout(f.timeid);
        f.timeid=false;
    }
    if(type!=missing)
        type=parseInt(type);
    else
        type=2;
    if(mess==missing){mess="Done";}
    if(milly==missing){milly=4000;}
    var m=$("#message_"+formid);
    switch(type){
        case 0:
            winzMessageBox(formid, mess);
            m.css({"color":"red"});break;
        case 1:
            m.css({"color":"green"});break;
        default:
            m.css({"color":"black"});break;
    }
    winzDither(formid, false);
    m.html(mess);
    $("#stop_"+formid).hide();
    f.timeid=setTimeout(
        function(){
            winzClearMess(formid);
        }, milly
    );
    if(type!=0 && data!=missing){
        if(window.console&&_sessioninfo.debugmode){console.log(data)}
    }
}
function winzClearMess(formid, data){
    if(formid in _globalforms){
        var f=_globalforms[formid];
        f.jqxhr=false;
        if(f.timeid!==false){
            clearTimeout(f.timeid);
            f.timeid=false;
        }
        $("#message_"+formid).html("");
        $("#stop_"+formid).hide();
        winzDither(formid, false);
        if(_isset(data)){
            data=_strip_tags(data);
            if(window.console&&_sessioninfo.debugmode){console.log(data)}
            alert(data);
        }
    }
}
function winzStoppable(formid, jqxhr){
    _globalforms[formid].jqxhr=jqxhr;
    $("#stop_"+formid).show();
}
function winzAbort(formid){
    var f=_globalforms[formid];
    var m=0;
    if(_isobject(f.jqxhr)){
        m=100;
        try{
            f.jqxhr.abort();
            setTimeout(
                function(){
                    f.jqxhr=false;
                }, m
            );
        }catch(e){}
    }
    $("#message_"+formid).html("");
    $("#stop_"+formid).hide();
    if(RYWINZ.busy(formid)){
        // SI PUO' MIGLIORARE GESTENDOLO MASCHERA PER MASCHERA
        TAIL.abort();
    }
    winzDither(formid, false);
    if(m>0){_pause(m)}
}
function winzMessageBox(formid, params, missing){
    var dlg=winzDialogGet(formid);
    var hangerid=dlg.hanger;
    var actualid=formid+dlg.instanceid;
    var width=500;
    var height=180;
    var message="Loading...";
    var babelcode="";
    var confirm=false;
    var cancel=false;
    var onclose=false;
    var capOK=RYBOX.babels("BUTTON_OK");
    var codeOK="";
    var args={};
    if(_isobject(params)){
        if(params.message!=missing){message=params.message}
        if(params.code!=missing){babelcode=params.code}
        if(params.confirm!=missing){confirm=params.confirm}
        if(params.cancel!=missing){cancel=params.cancel}
        if(params.close!=missing){onclose=params.close}
        if(params.args!=missing){args=params.args}
        if(params.width!=missing){width=params.width}
        if(params.height!=missing){height=params.height}
        if(params.ok!=missing){capOK=params.ok}
        if(params.codeok!=missing){codeOK=params.codeok}
    }
    else{
        message=params;
    }
    var i=0;
    for(var a in args){
        i+=1;
        message=message.replace("{"+a+"}", args[a]).replace("{"+i+"}", args[a]);
    }
    if(message.indexOf("<textarea")==-1)
        message=message.replace(/\n/gi, "<br/>");
    message=message.replace(/[']/gi, "&acute;");
    winzDialogParams(dlg, {
        width:width, 
        height:height,
        open:function(){
            castFocus(actualid+"_msg_ok");
        },
        close:function(){
            delete globalobjs[actualid+"_msg_ok"];
            delete _globalforms[formid].controls[actualid+"_msg_ok"];
            if(confirm!==false){
                delete globalobjs[actualid+"_msg_cancel"];
                delete _globalforms[formid].controls[actualid+"_msg_cancel"];
            }
            winzDialogFree(dlg);
            if(onclose!==false){onclose()}
        }
    });
    // DEFINIZIONE DEL CONTENUTO
    var t="";
    t+="<div class='winz_msgbox' style='height:"+(height-105)+"px;width:"+(width-42)+"px;'>"+message+"</div>";
    t+="<div id='"+actualid+"_msg_ok' notab='1'></div>";
    if(confirm!==false){
        t+="<div id='"+actualid+"_msg_cancel' notab='1'></div>";
    }
    $("#"+hangerid).html(t);
    $("#"+hangerid+" a").each(function(i){
        $(this).attr("target","_blank");
        $(this).css({"cursor":"pointer", "color":"navy"});
    });
    $("#"+actualid+"_msg_ok").rylabel({
        left:20,
        top:height-40,
        width:80,
        caption:capOK,
        code:codeOK,
        button:true,
        formid:formid,
        click:function(o){
            winzDialogClose(dlg);
            if(confirm!==false){confirm()}
        }
    });
    if(confirm!==false){
        $("#"+actualid+"_msg_cancel").rylabel({
            left:120,
            top:height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_CANCEL"),
            button:true,
            formid:formid,
            click:function(o){
                winzDialogClose(dlg);
                if(cancel!==false){cancel()}
            }
        });
    }
    // MOSTRO LA DIALOGBOX
    winzDialogOpen(dlg);
    // GESTIONE MULTILINGUA
    if(babelcode!=""||codeOK!=""){
        $.post(_cambusaURL+"rybabel/rybabel.php", {"lang":_sessioninfo.language,"codes":babelcode+"|"+codeOK},
            function(d){
                try{
                    var v=$.parseJSON(d);
                    message=v[babelcode];
                    if(message!=""){
                        var i=0;
                        for(var a in args){
                            i+=1;
                            message=message.replace("{"+a+"}", args[a]).replace("{"+i+"}", args[a]);
                        }
                        if(message.indexOf("<textarea")==-1)
                            message=message.replace(/\n/gi, "<br/>");
                        message=message.replace(/[']/gi, "&acute;");
                        $("#"+hangerid+" .winz_msgbox").html(message);
                    }
                    capOK=v[codeOK];
                    if(capOK!=""){
                        globalobjs[actualid+"_msg_ok"].caption(capOK);
                    }
                }
                catch(e){}
            }
        );
    }
}
function winzDisposeGrid(grids, done){
    var id=false;
    for(id in grids){break;}
    if(id!==false){
        globalobjs[grids[id]].dispose(
            function(){
                delete globalobjs[grids[id]];
                delete grids[id];
                winzDisposeGrid(grids, done);
            }
        );
    }
    else{
        done();
    }
}
function winzAppendCtrl(vK, id){
    vK.push(id);
    return "<div id='"+id+"' notab='1'></div>";
}
function winzDisposeCtrl(formid, vK){
    for(var i in vK){
        delete globalobjs[vK[i]];
        delete _globalforms[formid].controls[vK[i]];
    }
}
function winzRemoveAll(done){
    var id=false;
    for(id in _globalforms){break}
    if(id!==false){
        RYWINZ.removeform( id, function(){winzRemoveAll(done)} );
    }
    else{
        if(done){done()}
    }
}
function winzDither(formid, bValue){
    var f=_globalforms[formid];
    if(_isset(f)){
        if(bValue){
            RYWINZ.busy(formid, 1);
            $("#dither_"+formid).show();
            if(!f.options.statusbar)
                $("#message_"+formid).css({"display":"block"});
        }
        else{
            $("#dither_"+formid).hide();
            RYWINZ.busy(formid, 0);
            if(!f.options.statusbar)
                $("#message_"+formid).css({"display":"none"});
        }
    }
}
function winzDialogGet(formid){
    var progrid=0;
    if(window.console&&_sessioninfo.debugmode)console.log("Oggetti in apertura dialog: "+_objectlength(globalobjs));
    while($("#dialogout_"+formid+progrid).length>0){progrid+=1}
    var r="dialogdither_"+formid+progrid;
    var o="dialogout_"+formid+progrid;
    var d="dialogframe_"+formid+progrid;
    var h="dialog_"+formid+progrid;
    $("#window_"+formid+" .window_inner").append("<div id='"+r+"' class='winz_dither'></div><div id='"+o+"' class='winz_dialog_outer'><div id='"+d+"' class='winz_dialog'><div id='"+h+"'></div><div class='winz_close'>X</div></div></div>");
    var dlg={formid:formid, progrid:progrid, instanceid:"_"+progrid+"_", dither:r, outer:o, frame:d, hanger:h, width:600, height:500};
    $("#"+dlg.frame).css({width:dlg.width, height:dlg.height});
    $("#"+d).keydown(
        function(k){
            if(k.which==27){
                winzDialogClose(dlg);
                if(dlg.cancel){dlg.cancel()}
            }
        }
    );
    $("#"+d+" .winz_close").click(
        function(){
            winzDialogClose(dlg);
            if(dlg.cancel){dlg.cancel()}
        }
    );
    return dlg;
}
function winzDialogParams(dlg, params){
    $.extend(dlg, params);
    $("#"+dlg.frame).css({width:dlg.width, height:dlg.height});
}
function winzDialogOpen(dlg){
    _dialogcount+=1;
    _globalforms[dlg.formid].opens+=1;
    _criticalactivities+=1;
    if(window.console&&_sessioninfo.debugmode){console.log("Dialog aperte: "+_dialogcount)}
    $("#"+dlg.dither).show();
    $("#"+dlg.outer).show();
    if(dlg.open){
        setTimeout(
            function(){
                dlg.open();
            },200
        );
    }
}
function winzDialogClose(dlg){
    _dialogcount-=1;
    _globalforms[dlg.formid].opens-=1;
    _criticalactivities-=1;
    if(window.console&&_sessioninfo.debugmode){console.log("Dialog aperte: "+_dialogcount)}
    $("#"+dlg.outer).hide();
    $("#"+dlg.dither).hide();
    if(dlg.close){
        //setTimeout(
        //    function(){
                dlg.close();
        //    },200
        //);
    }
}
function winzDialogFree(dlg){
    $("#"+dlg.hanger).html("");
    $("#"+dlg.outer).remove();
    $("#"+dlg.dither).remove();
    if(window.console&&_sessioninfo.debugmode)console.log("Oggetti in chiusura dialog: "+_objectlength(globalobjs));
}
function winzPost(url, params, success, fail){
    _criticalactivities+=1;
    $.post(url, params,
        function(d){
            _criticalactivities-=1;
            success(d);
        }
    )
    .fail(
        function(){
            _criticalactivities-=1;
            if(fail)
                fail();
            else
                success({success:0, messsage:"Call failed!"});
        }
    );
}
function winzPostProgress(settings, missing){
    var proptotal=-1;
    var propblock=100;
    var propurl=_cambusaURL+"ryquiver/quiver.php";
    var propenabled=1;
    var propdata={};
    var propofunction="";
    if(settings.url!=missing){propurl=settings.url}
    if(settings.enabled!=missing){propenabled=settings.enabled}
    if(settings["function"]!=missing){propofunction=settings["function"]}
    if(settings.block!=missing){propblock=settings.block}
    if(settings.data!=missing){propdata=settings.data}
    propdata["PROGRESS"]=propenabled;
    var jqxhr=$.ajax({
        xhr: function(){
            var xhr=null;
            if(window.XMLHttpRequest){
                xhr=new window.XMLHttpRequest();
                //Download progress
                xhr.addEventListener("progress", function(evt){
                    manageprogress(xhr, evt);
                }, false);
            } 
            else{ 
                try{  
                    xhr=new ActiveXObject("MSXML2.XMLHTTP");
                    //Download progress
                    xhr.attachEvent("progress", function(evt){
                        manageprogress(xhr, evt);
                    });
                } 
                catch(e){} 
            }                        
            return xhr;
        },
        type:"POST",
        url:propurl,
        data:{
            "sessionid":_sessionid,
            "env":_sessioninfo.environ,
            "function":propofunction,
            "data":propdata
        },
        success: function(d){
            try{
                if(propenabled){
                    d=d.substr(d.indexOf("Y")+1);
                }
                if(settings.success!=missing){
                    settings.success(d);
                }
            }
            catch(e){
                if(settings.error!=missing){
                    settings.error(d);
                }
            }
        },
        error: function(d){
            if(settings.error!=missing){
                settings.error(d);
            }
        }
    });
    manageprogress=function(xhr, evt){
        try{
            var perc=0;
            var loaded=-1;
            if(proptotal>=0){
                loaded=Math.round(evt.loaded/propblock)-1;
                if(proptotal>0){
                    if(loaded>proptotal){loaded=proptotal}
                    perc=Math.round(loaded/proptotal);
                }
                xhr.responseText="";
            }
            else{
                if(window.console&&_sessioninfo.debugmode){console.log(xhr.responseText)}
                if(xhr.responseText.length>=propblock){
                    proptotal=_getinteger(xhr.responseText.substr(0,18));
                    loaded=0;
                    xhr.responseText="";
                }
            }
            if(propenabled && loaded>=0){
                if(settings.progress!=missing){
                    settings.progress(loaded, proptotal, perc);
                }
            }
        }catch(e){}
    }
    return jqxhr;
}
function winzBringToFront(formid){
    if(!$("#window_"+formid).hasClass('window_stack')){     // Rudyz
        JQD.util.window_flat();
        $("#window_"+formid).addClass('window_stack');
    }
}
function winzMereMessage(formid, mess, col, missing){
    if(col==missing){col="black"}
    $("#message_"+formid).html(mess).css({"color":col});
}
function winzConfirmAbandon(formid, options, missing){
    var ok=true;
    if(RYWINZ.modified(formid)){
        ok=false;
        var dlg=winzDialogGet(formid);
        var hangerid=dlg.hanger;
        var actualid=formid+dlg.instanceid;
        var h="";
        var vK=[];
        var title=RYBOX.babels("MSG_DATANOTSAVE");
        if(options==missing){ options={} }
        if(options.title!=missing){ title=options.title }
        winzDialogParams(dlg, {
            width:500,
            height:180,
            open:function(){
                castFocus(actualid+"__save");
            },
            close:function(){
                winzDisposeCtrl(formid, vK);
                winzDialogFree(dlg);
            }
        });
        // DEFINIZIONE DEL CONTENUTO
        h+="<div class='winz_msgbox'>";
        h+=title;
        h+="</div>";
        h+=winzAppendCtrl(vK, actualid+"__save");
        h+=winzAppendCtrl(vK, actualid+"__abandon");
        h+=winzAppendCtrl(vK, actualid+"__cancel");
        $("#"+hangerid).html(h);
        $("#"+actualid+"__save").rylabel({
            left:20,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_SAVE"),
            button:true,
            formid:formid,
            click:function(o){
                winzDialogClose(dlg);
                if(options.save)
                    options.save();
            }
        });
        $("#"+actualid+"__abandon").rylabel({
            left:120,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_ABANDON"),
            button:true,
            formid:formid,
            click:function(o){
                RYWINZ.modified(formid, 0);
                winzDialogClose(dlg);
                if(options.abandon)
                    options.abandon();
            }
        });
        var _bt_cancel=$("#"+actualid+"__cancel").rylabel({
            left:220,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_CANCEL"),
            button:true,
            formid:formid,
            click:function(o){
                winzDialogClose(dlg);
                if(options.cancel)
                    options.cancel();
            }
        });
        // MOSTRO LA DIALOGBOX
        winzDialogOpen(dlg);
    }
    return ok;
}
function winzToObject(formid, datalot, sysid){
    var data = new Object();
    var o=_globalforms[formid];
    if(_isset(sysid))
        data["SYSID"]=sysid;
    for(var k in o.controls){   // Ciclo sui controlli di maschera
        var datum=$("#"+k).prop("datum");   // Leggo la proprietà datum
        if( !_ismissing(datum) ){   // Controllo che datum sia definito
            if(datum==datalot){  // Controllo che si un campo del lotto che voglio travasare
                var c=globalobjs[k];
                if(c.modified() && c.tag){
                    switch(c.type){
                    case "date":
                        data[c.tag]=c.text();break;
                    case "number":
                        data[c.tag]=c.value().toString();break;
                    case "check":
                        data[c.tag]=c.value();break;
                    case "list":
                        data[c.tag]=_ajaxescapize( c.key(c.value()) );break;
                    default:
                        data[c.tag]=_ajaxescapize( c.value() );break;
                    }
                }
            }
        }
    }
    if(window.console&&_sessioninfo.debugmode){console.log(data)}
    return data;
}
function winzMaskClear(formid, datalot){
    var o=_globalforms[formid];
    for(var k in o.controls){   // Ciclo sui controlli di maschera
        var datum=$("#"+k).prop("datum");   // Leggo la proprietà datum
        if( !_ismissing(datum) ){   // Controllo che datum sia definito
            if(datum==datalot){  // Controllo che si un campo del lotto che voglio ripulire
                var c=globalobjs[k];
                if(c.type=="list")
                    c.value(1);
                else
                    c.clear();  // Se è definito pulisco qualunque sia il valore di datum
            }
        }
    }
    RYWINZ.modified(formid, 0);
}
function winzToMask(formid, datalot, data){
    var o=_globalforms[formid];
    for(var k in o.controls){   // Ciclo sui controlli di maschera
        var datum=$("#"+k).prop("datum");   // Leggo la proprietà datum
        if( !_ismissing(datum) ){   // Controllo che datum sia definito
            if(datum==datalot){  // Controllo che si un campo del lotto che voglio travasare
                var c=globalobjs[k];
                if(c.tag){
                    var d=_fittingvalue(data[c.tag]);
                    switch(c.type){
                    case "date":
                        c.value(d);break;
                    case "number":
                        c.value(d);break;
                    case "check":
                        c.value( _bool( d ) );break;
                    case "list":
                        c.value( c.index( d ) );break;
                        break;
                    default:
                        if(c.tag=="NAME"){
                            if(d.substr(0,2)!="__")
                                c.value(d);
                            else
                                c.value("");
                        }
                        else{
                            c.value(d);
                        }
                        break;
                    }
                }
            }
        }
    }
    RYWINZ.modified(formid, 0);
}
function winzMaskEnabled(formid, datalot, flag){
    var o=_globalforms[formid];
    for(var k in o.controls){   // Ciclo sui controlli di maschera
        var datum=$("#"+k).prop("datum");   // Leggo la proprietà datum
        if(_isset(datum)){   // Controllo che datum sia definito
            if(datum==datalot){  // Controllo che si un campo del lotto che voglio ripulire
                globalobjs[k].enabled(flag);
            }
        }
    }
}
$(document).ready(function(){
    $("body").keydown(
        function(k){
            return raiseControlKey(k);
        }
    );
    RYWINZ=new ryWinz();
    RYBOX.babels({
        "MSG_DATANOTSAVE":"I dati sono stati modificati. Salvare?",
        "BUTTON_SAVE":"Salva",
        "BUTTON_ABANDON":"Abbandona",
        "BUTTON_OK":"OK",
        "BUTTON_CANCEL":"Annulla",
        "MSG_CONFIRMEXIT":"Un'attività è in corso o il documento non è stato salvato.\n\nUscire comunque?",
        "MSG_QUITPAGE":"Richiesta di abbandono della pagina!",
        "MSG_QUITMODIFIEDPAGE":"Alcune attività sono in corso o qualche documento non è stato salvato.",
        "MSG_CONFIRMQUIT":"Alcune attività sono in corso o qualche documento non è stato salvato.\n\nUscire comunque?"
    });
});
