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
var _openingid="";
var _openingname="";
var _openingpath="";
var _openingparams="({})";
var _winzprogrid=0;
var _globalforms=new Object();
var _dialogcount=0;
var _envattachment="";

// Preload avanzamento
var _preloadProgress=new Image();
_preloadProgress.src=_systeminfo.relative.cambusa+"rybox/images/progress.gif";

function raiseCloseDialogs(){
	$("#winz-preview").hide();
	$("#winz-preview>iframe").prop("src", "");
	
	// Elimino l'eventuale documento iniettato nell'iframe
	var d=function(x){
		return x.contentDocument || x.contentWindow.document;
	}( $("#winz-preview>iframe").get()[0] );
	
	$(d).find("body").html("");
	
	$("#winz-about-dither").hide();
	$("#winz-about").hide();
}
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
		$("#window_"+n).trigger( "formresize" );
        var metrics={
            window:{
                width:m.width(),
                height:m.height()
            }
        };
        if(RYWINZ.forms(n)._kresize){
            RYWINZ.forms(n)._kresize(metrics);
        }
        if(RYWINZ.forms(n)._resize){
            RYWINZ.forms(n)._resize(metrics);
        }
    }catch(e){
        if(window.console){console.log(e.message)}
    }
}
function raiseControlKey(k){
    var n="",fn="";
	//$.debug("raiseControlKey:"+k.which);
    //if($.browser.opera || $.browser.chrome ? k.ctrlKey : k.altKey){
    if(k.altKey){
        n=RYWINZ.ActiveForm();
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
                case 87:    // Alt-W
                    fn="_tool_dump";
                    break;
            }
            if(fn!=""){
                var f=RYWINZ.forms(n);
                if($.isset( f[fn] )){
                    try{
                        f[fn]();
                    }catch(e){}
                }
            }
        }
        return (fn=="");
    }
	if(k.which==27){
		// Chiudo eventuli dialogbox globali aperte
		raiseCloseDialogs();
	}
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
    frm._tool_dump=function(){
		// Reperisco il documento dell'iframe
		var d=function(x){
			return x.contentDocument || x.contentWindow.document;
		}( $("#winz-preview>iframe").get()[0] );
		
		// Costruisco il contenuto
		var h=winzFormDump(formid);
		
		$(d).find("body").html(h);
		$("#winz-preview").show();
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
function winzFormDump(formid){
	var h="", v="";
	var left=0;
	var top=0;
	var width=0;
	var height=0;
	h+="<style>.ryque-head .ryque-cell{position:absolute;top:0px;left:0px;font-weight:bold;} .ryque-cell{top:0px;line-height:21px;padding:0px 6px;box-sizing:border-box;overflow:hidden;}</style>";
	try{
		var collection=_globalforms[formid].controls;
		for(var i in collection){
			var o=globalobjs[i];
			if(o && _visibleobject(i)){
				left=$(o).css("left");
				top=$(o).css("top");
				width=$(o).width();
				if(width==0)
					width=150;
				height=$(o).height();
				if(height==0)
					height=50;
				v="";
				switch(o.type){
					case "label":
						v="<b>"+o.caption()+"</b>";
						break;
					case "grid":
						v=$("#"+o.name()+"_outgrid").html();
						v=v.replace(/(\d{2})\/(\d{2})\/(\d{4})/g, "$3$2$1");
						v=v.replace(/˙/g, "").replace(/(\d+),(\d+)/g, "$1.$2");
						break;
					case "tabs":
						break;
					case "text":
					case "number":
					case "check":
						v=o.value();
						break;
					case "date":
						v=o.text();
						break;
					case "list":
						v="["+o.value()+"] "+o.caption(o.value());
						break;
					case "helper":
					case "area":
					case "edit":
					case "code":
					case "script":
						v=o.value();
						break;
				}
				h+="<div style='position:absolute;left:"+left+";top:"+top+";width:"+width+";height:"+height+";overflow:hidden;white-space:nowrap;'>"+v+"</div>";
			}
		}
	}
	catch(e){
		h+="<b>"+e.message+"</b>";
	}
	return h;
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
    $("#message_"+formid).html("<img style='margin:3px;' src='"+_systeminfo.relative.cambusa+"rybox/images/progress.gif'>");
    winzDither(formid, true);
}
function winzTitle(formid, title){
    $(".title_"+formid).html(title);
}
function winzBarMessage(formid, mess){
    $("#message_"+formid).html(mess);
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
        if($.isset(data)){
            data=__(data).stripTags();
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
    if(typeof f.jqxhr=="object"){
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
    if(m>0){$.pause(m)}
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
    if(typeof params=="object"){
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
        $.engage(_systeminfo.relative.cambusa+"rybabel/rybabel.php", {"lang":_sessioninfo.language,"codes":babelcode+"|"+codeOK},
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
    if($.isset(f)){
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
function winzDialogGet(formid, hangerid, missing){
    var progrid=0;
    var saveh="";
    if(hangerid!=missing){
        if(hangerid.indexOf(formid)!=0)
            hangerid=formid+hangerid;
        saveh=$("#"+hangerid).html();
        $("#"+hangerid).html("");
    }
    else{
        hangerid="window_"+formid+" .window_inner";
    }
    if(window.console&&_sessioninfo.debugmode)console.log("Objects before dialog: "+$.objectsize(globalobjs));
    while($("#dialogout_"+formid+progrid).length>0){progrid+=1}
    var r="dialogdither_"+formid+progrid;
    var o="dialogout_"+formid+progrid;
    var d="dialogframe_"+formid+progrid;
    var h=formid+"dialog_"+progrid;
    $("#"+hangerid).append("<div id='"+r+"' class='winz_dither'></div><div id='"+o+"' class='winz_dialog_outer'><div id='"+d+"' class='winz_dialog'><div id='"+h+"'>"+saveh+"</div><div class='winz_close'>X</div></div></div>");
    if(!RYWINZ.Forms(formid).options.controls)
        $("#"+r).css({"top":0});
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
    _systeminfo.activities+=1;
    if(window.console&&_sessioninfo.debugmode){console.log("Open dialogs: "+_dialogcount)}
    $("#"+dlg.outer+" .ryobject").attr("notab", 1);
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
    _systeminfo.activities-=1;
    if(window.console&&_sessioninfo.debugmode){console.log("Open dialogs: "+_dialogcount)}
    $("#"+dlg.outer).hide();
    $("#"+dlg.dither).hide();
    if(dlg.close){
        dlg.close();
    }
}
function winzDialogFree(dlg){
    $("#"+dlg.hanger).html("");
    $("#"+dlg.outer).remove();
    $("#"+dlg.dither).remove();
    if(window.console&&_sessioninfo.debugmode)console.log("Objects after dialog: "+$.objectsize(globalobjs));
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
function winzShowInfo(formid, infos, missing){
    if(infos!=missing)
        $("#status_"+formid).html(infos);
    $("#status_"+formid).show();
}
function winzHideInfo(formid, infos, missing){
    $("#status_"+formid).hide();
}
function winzPathName(formid){
    return _globalforms[formid].pathname;
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
function winzToObject(formid, datalot, sysid, missing){
    var data = new Object();
    var o=_globalforms[formid];
    if(sysid!=missing)
        data["SYSID"]=sysid;
    for(var k in o.controls){   // Ciclo sui controlli di maschera
        var datum=$("#"+k).prop("datum");   // Leggo la proprietà datum
        if(datum!=missing){   // Controllo che datum sia definito
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
                        data[c.tag]=c.key(c.value());break;
                    default:
                        data[c.tag]=c.value();break;
                    }
                }
            }
        }
    }
    if(window.console&&_sessioninfo.debugmode){console.log(data)}
    return data;
}
function winzMaskClear(formid, datalot, missing){
    var o=_globalforms[formid];
    for(var k in o.controls){   // Ciclo sui controlli di maschera
        var datum=$("#"+k).prop("datum");   // Leggo la proprietà datum
        if(datum!=missing){   // Controllo che datum sia definito
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
function winzToMask(formid, datalot, data, missing){
    var o=_globalforms[formid];
    for(var k in o.controls){   // Ciclo sui controlli di maschera
        var datum=$("#"+k).prop("datum");   // Leggo la proprietà datum
        if(datum!=missing){   // Controllo che datum sia definito
            if(datum==datalot){  // Controllo che si un campo del lotto che voglio travasare
                var c=globalobjs[k];
                if(c.tag){
                    var d=__(data[c.tag]);
                    switch(c.type){
                    case "date":
                        c.value(d);break;
                    case "number":
                        c.value(d);break;
                    case "check":
                        c.value( d.booleanNumber() );break;
                    case "list":
                        c.value( c.index( d ) );break;
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
        if($.isset(datum)){   // Controllo che datum sia definito
            if(datum==datalot){  // Controllo che si un campo del lotto che voglio ripulire
                globalobjs[k].enabled(flag);
            }
        }
    }
}
function winzAttachPreview(formid, pathfile, env, missing){
	if(_envattachment==""){
		TAIL.enqueue(function(){
			RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
				{
					"sessionid":_sessioninfo.sessionid,
					"env":_sessioninfo.environ,
					"function":"files_info",
					"data":{}
				}, 
				function(d){
					try{
						var v=$.parseJSON(d);
						_envattachment=v.params["ENVATTACH"];
					}catch(e){}
					TAIL.free();
				}
			);
		}, 1);
	}
	TAIL.enqueue(function(){
		if(env==missing)
			env=_envattachment;
		RYWINZ.Post(_systeminfo.relative.cambusa+"rysource/source_temporary.php", 
			{
				"sessionid":_sessioninfo.sessionid,
				"envdb":_sessioninfo.environ,
				"envfs":env,
				"file":pathfile
			}, 
			function(d){
				var v=$.parseJSON(d);
				if(v.success>0){
					$("#winz-preview>iframe").prop("src", "");
					$("#winz-preview>iframe").prop("src", v.path);
					$("#winz-preview").show();
					// ELIMINO IL FILE TEMPORANEO
					setTimeout(function(){
						RYWINZ.Post(_systeminfo.relative.cambusa+"rysource/source_deletetemp.php", 
							{
								"file":v.path
							}, 
							function(d){}
						);
					}, 10000);
				}
				else{
					RYWINZ.MessageBox(formid, v.message);
				}
				TAIL.free();
			}
		);
	}, 1);
	TAIL.wriggle();
}
$(document).ready(function(){
    $("body").keydown(
        function(k){
            return raiseControlKey(k);
        }
    );
    RYQUEAUX=new ryQue();
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
