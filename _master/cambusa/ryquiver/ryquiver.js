/****************************************************************************
* Name:            ryquiver.php                                             *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var qv_handletemp=false;
var qv_queuelist={};
var qv_queuehelper={};
var qv_queuequery={};
var qv_queuebusy=false;
var RYQUEAUX=new ryQue();
function qv_mask2object(formid, datalot, sysid){
    var data = new Object();
    var o=_globalforms[formid];
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
    if(window.console&&_sessioninfo.debugmode&&_sessioninfo.debugmode){console.log(data)}
    return data;
}
function qv_maskclear(formid, datalot){
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
function qv_maskenabled(formid, datalot, flag){
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
function qv_object2mask(formid, datalot, data){
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
function qv_printselected(formid, objgrid, template, options){
    var dlg=winzDialogGet(formid);
    var hangerid=dlg.hanger;
    var h="";
    var vK=[];
    winzDialogParams(dlg, {
        width:500,
        height:180,
        open:function(){
            castFocus(formid+"__html");
        },
        close:function(){
            winzDisposeCtrl(formid, vK);
            winzDialogFree(dlg);
        }
    });
    // DEFINIZIONE DEL CONTENUTO
    h+="<div class='winz_msgbox'>";
    h+="Quale formato utilizzare per la stampa?";
    h+="</div>";
    h+=winzAppendCtrl(vK, formid+"__html");
    h+=winzAppendCtrl(vK, formid+"__pdf");
    $("#"+hangerid).html(h);
    $("#"+formid+"__html").rylabel({
        left:20,
        top:dlg.height-40,
        width:80,
        caption:"HTML",
        button:true,
        formid:formid,
        click:function(o){
            winzDialogClose(dlg);
            qv_printcall(formid, objgrid, template, 0, options)
        }
    });
    $("#"+formid+"__pdf").rylabel({
        left:120,
        top:dlg.height-40,
        width:80,
        caption:"PDF",
        button:true,
        formid:formid,
        click:function(o){
            winzDialogClose(dlg);
            qv_printcall(formid, objgrid, template, 1, options)
        }
    });
    // MOSTRO LA DIALOGBOX
    winzDialogOpen(dlg);
}
function qv_printcall(formid, objgrid, template, pdf, options, missing){
    var checkall=false;
    var params={};
    if(options!=missing){
        if(options.checkall!=missing){checkall=options.checkall}
        if(options.params!=missing){params=options.params}
    }
    if(checkall){
        if(!objgrid.ischecked()){
            objgrid.checkall();
        }
    }
    objgrid.selengage(   // Elenco dei SYSID selezionati
        function(o,s){
            winzProgress(formid);
            s=s.split("|");
            $.post(_customizeURL+_sessioninfo.app+"/reporting/"+template, 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "keys":s,
                    "pdf":pdf,
                    "params":params
                }, 
                function(d){
                    try{
                        if(window.console&&_sessioninfo.debugmode){console.log("Risposta da reporting: "+d)}
                        var h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+d;
                        $("#winz-iframe").prop("src", h);
                        winzClearMess(formid);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        },
        function(){
            alert("Nessun elemento selezionato");
        }
    );
}
function qv_bulkdelete(formid, objgrid, prefix){
    winzMessageBox(formid, {
        message:"Eliminare le righe selezionate?",
        code:"MSG_DELETESELROW",
        ok:RYBOX.babels("BUTTON_DELETE"),
        confirm:function(){
            objgrid.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    winzProgress(formid);
                    s=s.split("|");
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di cancellazione
                        stats[i]={
                            "function":prefix+"_delete",
                            "data":{
                                "SYSID":s[i]
                            }
                        };
                    }
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "program":stats
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                winzTimeoutMess(formid, parseInt(v.success), v.message);
                            }
                            catch(e){
                                winzClearMess(formid);
                                alert(d);
                            }
                            setTimeout(function(){objgrid.refresh()}, 200);
                        }
                    );
                }
            );
        }
    });
}
function qv_filedelete(formid, objgrid){
    winzMessageBox(formid, {
        message:"Eliminare le righe selezionate?",
        ok:RYBOX.babels("BUTTON_DELETE"),
        confirm:function(){
            objgrid.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    winzProgress(formid);
                    s=s.split("|");
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di cancellazione
                        stats[2*i]={
                            "function":"files_detach",
                            "data":{
                                "SYSID":s[i]
                            },
                            "pipe":{
                                "SYSID":"#FILEID"
                            }
                        };
                        stats[2*i+1]={
                            "function":"files_delete",
                            "data":{}
                        };
                    }
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "program":stats
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                winzTimeoutMess(formid, parseInt(v.success), v.message);
                            }
                            catch(e){
                                winzClearMess(formid);
                                alert(d);
                            }
                            objgrid.refresh();
                        }
                    );
                }
            );
        }
    });
}
function qv_filedownload(formid, objgrid, params, missing){
    var ind=objgrid.index();
    var mergetable=missing;
    var mergeid="";
    var signature=0;
    if(params!=missing){
        if(params.mergetable){mergetable=params.mergetable}
        if(params.mergeid){mergeid=params.mergeid}
        if(params.signature){signature=params.signature}
    }
    if(ind>0){
        objgrid.solveid( ind,
            function(o, id){
                RYQUEAUX.query({
                    sql:"SELECT FILEID,RECORDID FROM QVTABLEFILE WHERE SYSID='"+id+"'",
                    ready:function(v){
                        var data={};
                        data["SYSID"]=v[0]["FILEID"];
                        if(mergetable!=missing){
                            data["MERGE"]={"_CONTEXT":"SINGLETON", "TABLE":mergetable, "SYSID":mergeid};
                        }
                        if(signature){
                            data["SIGNATURE"]=1;
                        }
                        $.post(_cambusaURL+"ryquiver/quiver.php", 
                            {
                                "sessionid":_sessionid,
                                "env":_sessioninfo.environ,
                                "function":"files_export",
                                "data":data
                            }, 
                            function(d){
                                try{
                                    var v=$.parseJSON(d);
                                    var n=v["params"]["EXPORT"];
                                    var h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+_temporaryURL+n;
                                    if(window.console&&_sessioninfo.debugmode){console.log("Download:"+h)}
                                    $("#winz-iframe").prop("src", h);
                                    winzTimeoutMess(formid, parseInt(v.success), v.message);
                                    // GESTIONE FILE OBSOLETI
                                    if(qv_handletemp!==false)
                                        clearTimeout(qv_handletemp);
                                    qv_handletemp=setTimeout("qv_managetemp()", 10000);
                                }
                                catch(e){
                                    winzClearMess(formid);
                                    alert(d);
                                }
                            }
                        );
                    } 
                });
            }
        );
    }
    else{
        alert("Nessuna riga selezionata");
    }
}
function qv_filemanager(objform, formid, tablename, params, missing){
    var prefix="#"+formid;
    var currsysid="";
    var loadedsysid="";
    var filecrossid="";
    var filesysid="";
    var merge=missing;
    var paramchangerow=missing;
    var paramsolveid=missing;
    var propenabled=true;
    if(params!=missing){
        if(typeof(params)==="string"){
            merge=params;
        }
        else{
            if(params.merge){merge=params.merge}
            if(params.changerow){paramchangerow=params.changerow}
            if(params.solveid){paramsolveid=params.solveid}
        }
    }
    var h="";
    h+='<div id="'+formid+'docs_context"></div>';
    h+='<div id="'+formid+'griddocs"></div>';
    h+='<div id="'+formid+'oper_fileinsert"></div>';
    h+='<div id="'+formid+'oper_filerefresh" babelcode="FILE_REFRESH"></div>';
    h+='<div id="'+formid+'oper_fileunsaved" babelcode="FILE_UNSAVED"></div>';
    h+='<div id="'+formid+'lb_filedescription" babelcode="FILE_DESCRIPTION"></div>';
    h+='<div id="'+formid+'tx_filedescription"></div>';
    h+='<div id="'+formid+'lb_filedate" babelcode="FILE_DATE"></div>';
    h+='<div id="'+formid+'lb_filesorter" babelcode="FILE_SORTER"></div>';
    h+='<div id="'+formid+'tx_filedate"></div>';
    h+='<div id="'+formid+'tx_filesorter"></div>';
    h+='<div id="'+formid+'oper_fileupdate" babelcode="FILE_UPDATE"></div>';
    h+='<div id="'+formid+'oper_filedownload" babelcode="FILE_DOWNLOAD"></div>';
    h+='<div id="'+formid+'oper_filedelete" babelcode="MULTIDELETE"></div>';
    if($("#"+formid+"filemanager").html(h).length>0){
        var offsety=180;
        var lb_docs_context=$(prefix+"docs_context").rylabel({left:20, top:50, caption:"", formid:formid});

        var objgriddocs=$(prefix+"griddocs").ryque({
            left:20,
            top:80,
            width:400,
            height:300,
            formid:formid,
            numbered:false,
            checkable:true,
            environ:_sessioninfo.environ,
            from:"QWFILES",
            columns:[
                {id:"DESCRIPTION", caption:"Descrizione", width:230, code:"FILE_DESCRIPTION"},
                {id:"AUXTIME", caption:"Data", width:90, type:"/", code:"FILE_DATE"},
                {id:"SORTER", caption:"", width:38, type:0}
            ],
            orderby: "SORTER,AUXTIME DESC,FILEID DESC",
            changerow:function(o,i){
                filecrossid="";
                filesysid="";
                tx_filedescription.clear();
                tx_filedate.clear();
                tx_filesorter.clear();
                tx_filedescription.enabled(0);
                tx_filedate.enabled(0);
                tx_filesorter.enabled(0);
                oper_fileupdate.enabled(0);
                oper_filedownload.enabled(0);
                oper_filedelete.enabled(0);
                oper_fileunsaved.visible(0);
                if(paramchangerow!=missing){
                    paramchangerow();
                }
                if(i>0){
                    o.solveid(i);
                }
            },
            selchange:function(o, i){
                if(propenabled)
                    oper_filedelete.enabled(o.isselected());
            },
            ready:function(){
                loadedsysid=currsysid;
            },
            solveid:function(o, d){
                RYQUEAUX.query({
                    sql:"SELECT * FROM QWFILES WHERE SYSID='"+d+"'",
                    ready:function(v){
                        try{
                            filecrossid=v[0]["SYSID"];
                            filesysid=v[0]["FILEID"];
                            tx_filedescription.enabled(1);
                            tx_filedate.enabled(1);
                            tx_filesorter.enabled(1);
                            tx_filedescription.value(v[0]["DESCRIPTION"]);
                            tx_filedate.value(v[0]["AUXTIME"]);
                            tx_filesorter.value(v[0]["SORTER"]);
                            oper_fileupdate.enabled(1);
                            oper_filedownload.enabled(1);
                            oper_fileunsaved.visible(0);
                            if(propenabled){
                                oper_filedelete.enabled(1);
                            }
                            if(paramsolveid!=missing){
                                paramsolveid(filesysid, v[0]);
                            }
                        }catch(e){}
                    } 
                });
            },
            enter:function(o, r){
                qv_filedownload(formid, o, {"mergetable":merge, "mergeid":currsysid});
            }
        });
        
        $(prefix+"oper_fileinsert").ryupload({
            left:430,
            top:90,
            width:300,
            formid:formid,
            environ:_tempenviron,
            complete:function(id, name, ret){
                //$(prefix+"oper_fileinsert .qq-upload-success , .qq-upload-fail").remove();
                $.post(_cambusaURL+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "program":[
                            {
                                "function":"files_insert",
                                "data":{
                                    "IMPORTNAME":name,
                                    "SUBPATH":strRight(currsysid, 2)
                                },
                                "pipe":{
                                    "FILEID":"SYSID"
                                }
                            },
                            {
                                "function":"files_attach",
                                "data":{
                                    "TABLENAME": tablename,
                                    "RECORDID":currsysid
                                }
                            }
                        ]
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success){
                                // POSIZIONAMENTO SUL NUOVO DOCUMENTO
                                var newid=v.SYSID;
                                objgriddocs.query({
                                    ready:function(v){
                                        objgriddocs.search({
                                                "where": _ajaxescapize("SYSID='"+newid+"'")
                                            },
                                            function(d){
                                                var ind=0;
                                                try{
                                                    var v=$.parseJSON(d);
                                                    ind=v[0];
                                                    
                                                }
                                                catch(e){
                                                    alert(d);
                                                }
                                                objgriddocs.index(ind);
                                            }
                                        );
                                    }
                                });
                            }
                            winzTimeoutMess(formid, parseInt(v.success), v.message);
                        }
                        catch(e){
                            winzClearMess(formid);
                            alert(d);
                        }
                    }
                );
            }
        });

        var oper_filerefresh=$(prefix+"oper_filerefresh").rylabel({
            left:670,
            top:90,
            width:70,
            caption:"Aggiorna",
            formid:formid,
            button:true,
            click:function(o){
                objgriddocs.query();
            }
        });
        
        var oper_fileunsaved=$(prefix+"oper_fileunsaved").rylabel({left:430, top:140, caption:"<span style='color:red;'>Modificato - Non salvato<span>", formid:formid});
        oper_fileunsaved.visible(0);
        
        var lb_filedescription=$(prefix+"lb_filedescription").rylabel({left:430, top:offsety, caption:"Descrizione", formid:formid});
        offsety+=20;

        var tx_filedescription=$(prefix+"tx_filedescription").rytext({left:430, top:offsety, width:320, formid:formid,
            changed:function(){
                oper_fileunsaved.visible(1);
            }
        });
        offsety+=30;

        var lb_filedate=$(prefix+"lb_filedate").rylabel({left:430, top:offsety, caption:"Data", formid:formid});
        var lb_filesorter=$(prefix+"lb_filesorter").rylabel({left:560, top:offsety, caption:"Ordine", formid:formid});
        offsety+=20;

        var tx_filedate=$(prefix+"tx_filedate").rydate({left:430, top:offsety, width:110, formid:formid,
            changed:function(){
                oper_fileunsaved.visible(1);
            }
        });
        var tx_filesorter=$(prefix+"tx_filesorter").rynumber({left:560, top:offsety, width:90, numdec:0, minvalue:-99999, maxvalue:99999, formid:formid,
            changed:function(){
                oper_fileunsaved.visible(1);
            }
        });

        var oper_fileupdate=$(prefix+"oper_fileupdate").rylabel({
            left:670,
            top:offsety,
            width:70,
            caption:"Salva",
            formid:formid,
            button:true,
            click:function(o){
                $.post(_cambusaURL+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "function":"files_update",
                        "data":{
                            "SYSID":filesysid,
                            "DESCRIPTION": _ajaxescapize(tx_filedescription.value()),
                            "AUXTIME": _ajaxescapize(tx_filedate.text()),
                            "CROSSID":filecrossid,
                            "SORTER":tx_filesorter.value()
                        }
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            objgriddocs.dataload();
                            oper_fileunsaved.visible(0);
                            winzTimeoutMess(formid, parseInt(v.success), v.message);
                        }
                        catch(e){
                            winzClearMess(formid);
                            alert(d);
                        }
                    }
                );
            }
        });

        var oper_filedownload=$(prefix+"oper_filedownload").rylabel({
            left:430,
            top:300,
            caption:"Download",
            formid:formid,
            button:true,
            click:function(o){
                qv_filedownload(formid, objgriddocs, {"mergetable":merge, "mergeid":currsysid});
            }
        });

        var oper_filedelete=$(prefix+"oper_filedelete").rylabel({
            left:430,
            top:340,
            caption:"Elimina selezione",
            formid:formid,
            button:true,
            click:function(o){
                qv_filedelete(formid, objgriddocs);
            }
        });
    }
    this.initialize=function(sysid, context, typologyid, missing){
        if(lb_docs_context){
            lb_docs_context.caption(context);
            if(sysid!=loadedsysid){
                if(window.console&&_sessioninfo.debugmode){console.log("Caricamento documenti "+sysid)}
                currsysid=sysid;
                filecrossid="";
                filesysid="";
                objgriddocs.clear();
                if(typologyid==missing)
                    objgriddocs.where("RECORDID='"+currsysid+"'");
                else
                    objgriddocs.where("RECORDID='"+currsysid+"' OR RECORDID='"+typologyid+"'");
                objgriddocs.query();
            }
        }
    }
    this.clear=function(){
        loadedsysid="";
        objgriddocs.clear();
    }
    this.caption=function(context){
        if(lb_docs_context)
            lb_docs_context.caption(context);
    }
    this.enabled=function(v){
        if(v!=missing){
            propenabled=v;
            if(v){
                $(prefix+"oper_fileinsert").css({"display":"block"});
                oper_filedelete.enabled(objgriddocs.isselected());
            }
            else{
                $(prefix+"oper_fileinsert").css({"display":"none"});
                oper_filedelete.enabled(0);
            }
            tx_filedescription.enabled(v);
            tx_filedate.enabled(v);
            tx_filesorter.enabled(v);
        }
        return propenabled;
    }
    return this;
}
function qv_managetemp(){
    // LANCIO LA FUNZIONE DI MANUTENZIONE DEI FILE OBSOLETI
    try{
        qv_handletemp=false;
        $.post(_cambusaURL+"ryquiver/quiver.php", 
            {
                "sessionid":_sessionid,
                "env":_sessioninfo.environ,
                "function":"managetemp"
            }
        );
    }catch(e){}
}

function qv_autoconfigure(formid, viewname, tableprefix, typologyid, offsety, cacheext, action, missing){
    // DISTRUGGO I VECCHI CAMPI DELLA TIPOLOGIA
    var prefix="#"+formid;
    var tableviews=tableprefix+"VIEWS";
    var table=tableprefix+"S";
    var flagload=false;     // DEVO LEGGERE LA CONFIGURAZIONE DEI CAMPI?
    var flagdispose=false;  // DEVO ELIMINARE I VECCHI CAMPI?
    if(_ismissing(cacheext["_PREVTYPOLOGYID"])){
        // LA FUNZIONE E' CHIAMATA PER LA PRIMA VOLTA
        cacheext["_PREVTYPOLOGYID"]=typologyid;
        cacheext["_CURRCONFIG"]={};
        flagload=true;
    }
    else{
        if(cacheext["_PREVTYPOLOGYID"]!=typologyid){
            flagload=_ismissing(cacheext[typologyid]);
            flagdispose=true;
            cacheext["_PREVTYPOLOGYID"]=typologyid;
        }
    }
    if(flagdispose){
        // TOLGO I VECCHI CAMPI AGGIUNTIVI
        for(var n in cacheext["_CURRCONFIG"]){
            delete globalobjs[n];
            delete _globalforms[formid].controls[n];
        }
        cacheext["_CURRCONFIG"]={};
        //$(prefix+"extension").empty();
        $(prefix+"extension").html("");
    }
    qv_maskclear(formid, "C");
    if(viewname!=""){
        // LA VISTA E' DEFINITA
        if(flagload){
            // CARICAMENTO CAMPI ESTESI
            RYQUEAUX.query({
                sql:"SELECT * FROM "+tableviews+" WHERE TYPOLOGYID='"+typologyid+"'",
                ready:function(f){
                    cacheext[typologyid]=[];
                    for(n in f){
                        var nm=f[n]["FIELDNAME"];
                        var tp=f[n]["FIELDTYPE"];
                        var lb=f[n]["CAPTION"];
                        cacheext[typologyid][n]={"name":nm, "type":tp, "caption":lb};
                        cacheext["_CURRCONFIG"][formid+"lb_"+nm]=0;
                        cacheext["_CURRCONFIG"][formid+"tx_"+nm]=0;
                    }
                    offsety=qv_autoconfigurecall(formid, prefix, cacheext[typologyid], offsety);
                    if(action!=missing){
                        action(viewname, offsety);
                    }
                }
            });
        }
        else{
            if(flagdispose)
                offsety=qv_autoconfigurecall(formid, prefix, cacheext[typologyid], offsety);
            else
                offsety+=(30*cacheext[typologyid].length);
            if(action!=missing){
                action(viewname, offsety);
            }
        }
    }
    else{
        // LA VISTA NON E' DEFINITA: USO LA TABELLA DIRETTAMENTE
        if(action!=missing){
            action(table, offsety);
        }
    }
}

function qv_autoconfigurecall(formid, prefix, config, offsety){
    var n,nm,tp,lb,dec,ref;
    for(n in config){
        nm=config[n]["name"];
        tp=config[n]["type"].toUpperCase();
        lb=config[n]["caption"];
        var lb_nm="lb_"+nm;
        var tx_nm="tx_"+nm;
        offsety+=30;
        $(prefix+"extension").append("<div id='"+formid+lb_nm+"'></div>");
        $(prefix+lb_nm).rylabel({left:20, top:offsety, caption: lb, formid:formid});
        $(prefix+"extension").append("<div id='"+formid+tx_nm+"'></div>");
        
        if(tp.indexOf("INTEGER")>=0){
            tp="NUMBER";
            dec=0;
        }
        else if(tp.indexOf("RATIONAL")>=0){
            var d=tp.replace(/[^0-9]/g, "");
            tp="NUMBER";
            if(d!="")
                dec=_getinteger(d);
            else
                dec=2;
        }
        else if(tp.indexOf("CHAR")>=0){
            var d=tp.replace(/[^0-9]/g, "");
            if(_getinteger(d)>300)
                tp="GLOB";
            else
                tp="TEXT";
        }
        else if(tp.indexOf("SYSID")>=0){
            var d=tp.replace(/SYSID/g, "").replace(/[#()]/g, "");
            if(d!=""){
                tp="SYSID";
                ref=d
            }
            else{
                tp="TEXT";
                ref="";
            }
        }
        else if(tp.indexOf("TIME")>=0 || tp.indexOf("DATE")>=0){
            tp="DATE";
        }
        else if(tp.indexOf("BOOL")>=0){
            tp="BOOLEAN";
        }
        else if(tp.indexOf("TEXT")>=0){
            tp="GLOB";
        }
        else if(tp.indexOf("JSON")>=0){
            tp="GLOB";
        }
        else{
            tp="TEXT";
        }
        switch(tp){
        case "DATE":
            $(prefix+tx_nm).rydate({left:120, top:offsety, datum:"C", tag:nm, formid:formid});
            break;
        case "NUMBER":
            $(prefix+tx_nm).rynumber({left:120, top:offsety, width:200, numdec:dec, datum:"C", tag:nm, formid:formid});
            break;
        case "BOOLEAN":
            $(prefix+tx_nm).rycheck({left:120, top:offsety, datum:"C", tag:nm, formid:formid});
            break;
        case "SYSID":
            $(prefix+tx_nm).ryhelper({
                left:120, top:offsety, width:300, datum:"C", tag:nm, formid:formid, table:ref, title:lb,
                open:function(o){
                    o.where("");
                }
            });
            break;
        case "GLOB":
            $(prefix+tx_nm).ryedit({left:120, top:offsety, width:612, height:200, flat:1, datum:"C", tag:nm, formid:formid});
            offsety+=185;
            break;
        default:
            $(prefix+tx_nm).rytext({left:120, top:offsety, width:300, datum:"C", tag:nm, formid:formid});
        }
    }
    qv_queuebusy=false;
    setTimeout(function(){qv_queuemanager()});
    return offsety;
}

function qv_idrequest(formid, settings, missing){
    var objgrid;
    var prophelperid="";
    
    var proptitle="Selezione";
    if(settings.title!=missing){proptitle=settings.title}
    if(settings.titlecode!=missing){
        if(settings.titlecode!="")
            proptitle=RYBOX.babels(settings.titlecode)
    }
    
    var proptable=""; if(settings.table!=missing){proptable=settings.table}
    var propwhere=""; if(settings.where!=missing){propwhere=settings.where}
    var propclause=""; if(settings.clause!=missing){propclause=settings.clause}
    var propmultiple=false; if(settings.multiple!=missing){propmultiple=settings.multiple}
    var propargs=""; if(settings.args!=missing){propargs=settings.args}
    var proporderby="DESCRIPTION"; if(settings.orderby!=missing){proporderby=settings.orderby}
    var propselect=""; if(settings.select!=missing){propselect=settings.select}
    var proppreselect=""; if(settings.preselect!=missing){proppreselect=settings.preselect}
    var propclasstable=""; if(settings.classtable!=missing){propclasstable=settings.classtable}
    var propsubid=""; if(settings.subid!=missing){propsubid=settings.subid}
    var propmandatory=true; if(settings.mandatory!=missing){propmandatory=settings.mandatory}
    var propclose=false; if(settings.close!=missing){propclose=settings.close}

    var prophelpwidth=600;
    var prophelpheight=410;
    var propinit=false;
    var proprequestid="";
    var propprovider="";
    if(propsubid==""){
        if(_dialogcount==0){
            // INTERROGAZIONE DI PRIMO LIVELLO
            proprequestid=RYQUE.reqid();
            propprovider=RYQUE.provider();
        }
    }
    else{
        // INTERROGAZIONE DI SECONDO LIVELLO
        // AUMENTO LE DIMENSIONI PER AVERE UN FEEDBACK
        prophelpwidth=610;
        prophelpheight=420;
        if(_dialogcount==1){
            proprequestid=RYQUEAUX.reqid();
            propprovider=RYQUEAUX.provider();
        }
    }
    var cookiename=_sessioninfo.environ+"_HELP_"+_globalforms[formid].classform+"_"+propclasstable;
    
    // CREAZIONE DIALOGBOX
    var dlg=winzDialogGet(formid);
    var hangerid=dlg.hanger;
    var h="";
    var vK=[];
    winzDialogParams(dlg, {
        width:prophelpwidth,
        height:prophelpheight,
        open:function(){
            castFocus(actualid+"helpersearch");
        },
        close:function(){
            objgrid.dispose(
                function(){
                    winzDisposeCtrl(formid, vK);
                    winzDialogFree(dlg);
                    if(propclose!==false){propclose()}
                }
            );
        }
    });
    // DEFINIZIONE DEL CONTENUTO
    var actualid=formid+propsubid;
    h+="<div class='winz_dialog_title'>";
    h+=proptitle;
    h+="</div>";
    h+=winzAppendCtrl(vK, actualid+"helpergrid");
    h+=winzAppendCtrl(vK, actualid+"helperlbsearch");
    h+=winzAppendCtrl(vK, actualid+"helpersearch");
    if(propclasstable!=""){
        h+=winzAppendCtrl(vK, actualid+"helperlbclass");
        h+=winzAppendCtrl(vK, actualid+"helperclass");
    }
    h+=winzAppendCtrl(vK, actualid+"helperrefresh");
    h+=winzAppendCtrl(vK, actualid+"helperreset");
    h+=winzAppendCtrl(vK, actualid+"helperok");
    h+=winzAppendCtrl(vK, actualid+"helpercancel");
    $("#"+hangerid).html(h);
    
    switch(proptable){
        case "QVGENRES":
        case "QVOBJECTS":
        case "QVMOTIVES":
        case "QVARROWS":
        case "QVQUIVERS":
        case "QVFILES":
            if( !propwhere.match(/ DELETED/i) ){
                if(propwhere!="")
                    propwhere+=" AND ";
                propwhere+="DELETED=0";
            }
    }
    if(window.console&&_sessioninfo.debugmode){console.log("WHERE:"+propwhere)}
    var offsety=60;
    objgrid=$("#"+actualid+"helpergrid").ryque({
        left:20,
        top:offsety,
        width:300,
        height:300,
        formid:formid,
        requestid:proprequestid,
        provider:propprovider,
        numbered:propmultiple,
        checkable:propmultiple,
        environ:_sessioninfo.environ,
        from:proptable,
        clause:propclause,
        args:propargs,
        orderby:proporderby,
        columns:[
            {id:"DESCRIPTION", caption:RYBOX.babels("DESCRIPTION"), width:200}
        ],
        changerow:function(o,i){
            prophelperid="";
            if(i>0)
                o.solveid(i);
        },
        solveid:function(o,d){
            prophelperid=d;
        },
        enter:function(){
            if(prophelperid!=""){
                selectmanage(prophelperid);
            }
        },
        ready:function(o){
            if(o.count()==1){
                setTimeout(
                    function(){
                        o.index(1);
                        castFocus(actualid+"helpergrid");
                    }
                );
            }
        },
        initialized:function(o){
            // INIZIALIZZAZIONE LA METTO FUORI (LA REQUESTID E' GIA' RISOLTA)
            //if(propclasstable!="")
            //    objclass.value($.cookie(cookiename), true);
            //else
            //    setTimeout(function(){objrefresh.engage()}, 100);
        }
    });
    $("#"+actualid+"helperlbsearch").rylabel({left:330, top:offsety, caption:RYBOX.babels("SEARCH"), formid:formid});
    offsety+=20;
    var objsearch=$("#"+actualid+"helpersearch").rytext({
        left:330, top:offsety, width:250, formid:formid,
        assigned:function(){
            objrefresh.engage()
        }
    });
    if(propclasstable!=""){
        offsety+=30;
        $("#"+actualid+"helperlbclass").rylabel({left:330, top:offsety, caption:RYBOX.babels("CLASS"), formid:formid});
        offsety+=20;
        var objclass=$("#"+actualid+"helperclass").ryhelper({
            left:330, top:offsety, width:250, formid:formid, subid:"aux", table:propclasstable, title:RYBOX.babels("HLP_SELCLASS"), 
            open:function(o){
                o.where("");
            },
            assigned:function(o){
                $.cookie(cookiename, o.value(), {expires:10000});
                objrefresh.engage()
            },
            clear:function(o){
                $.cookie(cookiename, "", {expires:10000});
            }
        });
    }
    offsety+=40;
    var objrefresh=$("#"+actualid+"helperrefresh").rylabel({
        left:330,
        top:offsety,
        caption:RYBOX.babels("BUTTON_REFRESH"),
        formid:formid,
        button:true,
        click:function(o){
            var q=propwhere;
            var arg;
            if(_isobject(propargs))
                arg=propargs;
            else
                arg={};
            var c="";
            var t=_likeescapize(objsearch.value());
            if(propclasstable!=""){c=objclass.value()}

            if(t!=""){
                if(q!=""){q+=" AND "}
                q="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                arg["DESCRIPTION"]=t;
                arg["TAG"]=t;
            }
            if(c!=""){
                if(q!=""){q+=" AND "}
                q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+c+"')"
            }
            objgrid.where(q);
            objgrid.query({
                args:arg,
                ready:function(o){
                    if(proppreselect!="" && !propinit){
                        propinit=true;
                        o.selbyid(proppreselect);
                    }
                }
            });
        }
    });
    var objreset=$("#"+actualid+"helperreset").rylabel({
        left:500,
        top:offsety,
        width:70,
        caption:RYBOX.babels("BUTTON_RESET"),
        formid:formid,
        button:true,
        click:function(o){
            objsearch.value("");
            if(propclasstable!=""){
                objclass.value("")
                $.cookie(cookiename, "", {expires:10000});
            }
            objrefresh.engage();
        }
    });
    $("#"+actualid+"helperok").rylabel({
        left:20,
        top:dlg.height-40,
        width:80,
        caption:RYBOX.babels("BUTTON_OK"),
        button:true,
        formid:formid,
        click:function(o){
            if(propmultiple){
                objgrid.selengage(
                    function(o, d){
                        selectmanage(d);
                    },
                    function(){
                        winzMessageBox(formid, "Nessun elemento selezionato!");
                    }
                )
            }
            else{
                if(prophelperid!=""){
                    selectmanage(prophelperid);
                }
                else if(!propmandatory){
                    winzDialogClose(dlg);
                    if(settings.onselect!=missing){
                        var data={"SYSID":""};
                        setTimeout(
                            function(){
                                settings.onselect(data);
                            }, 100
                        );
                    }
                }
                else{
                    winzMessageBox(formid, "Nessun elemento selezionato!");
                }
            }
        }
    });
    $("#"+actualid+"helpercancel").rylabel({
        left:120,
        top:dlg.height-40,
        width:80,
        caption:RYBOX.babels("BUTTON_CANCEL"),
        button:true,
        formid:formid,
        click:function(o){
            winzDialogClose(dlg);
        }
    });
    // INIZIALIZZAZIONE (LA REQUESTID E' GIA' RISOLTA)
    if(propclasstable!="")
        objclass.value($.cookie(cookiename), true);
    else
        setTimeout(function(){objrefresh.engage()}, 100);

    // MOSTRO LA DIALOGBOX
    winzDialogOpen(dlg);

    function selectmanage(id){
        if(propselect!="" && propmultiple==false){
            RYQUEAUX.query({
                sql:"SELECT SYSID,"+propselect+" FROM "+proptable+" WHERE SYSID='"+id+"'",
                ready:function(d){
                    try{
                        // ELIMINO I NULL
                        for(var i in d[0]){
                            d[0][i]=_fittingvalue(d[0][i]);
                        }
                        winzDialogClose(dlg);
                        setTimeout(
                            function(){
                                if(settings.onselect!=missing){
                                    settings.onselect(d[0]);
                                }
                            }, 100
                        );
                    }catch(e){
                        alert(d);
                    }
                }
            });
        }
        else{
            winzDialogClose(dlg);
            if(settings.onselect!=missing){
                var data={"SYSID":id};
                setTimeout(
                    function(){
                        settings.onselect(data);
                    }, 100
                );
            }
        }
    }
}

(function($,missing) {
    $.extend(true,$.fn, {
        ryhelper:function(settings){
 			var propleft=20;
			var proptop=20;
			var propwidth=120;
			var propheight=22;
			var propfocusout=true;
			var propctrl=false;
			var propshift=false;
			var propobj=this;
			var propenabled=true;
			var propvisible=true;
            var propmultiple=false;
			var propformid="";
            var proptable="";
            var propwhere="";
            var propclause="";
            var propargs="";
            var propselect="";
            this.onselect=null;
            this.notfound=null;
            var proporderby="DESCRIPTION";
            var propclasstable="";
            var propsubid="";
            var propsysid=""; 
            var proptitle=_sessioninfo.appdescr;
            var proptitlecode="";
            var prophelpwidth=600;
            var prophelpheight=400;
            
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="helper";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.table!=missing){proptable=settings.table}
            if(settings.clause!=missing){propclause=settings.clause}
            if(settings.orderby!=missing){proporderby=settings.orderby}
            if(settings.classtable!=missing){propclasstable=settings.classtable}
            if(settings.subid!=missing){propsubid=settings.subid}
            if(settings.title!=missing){proptitle=settings.title}
            if(settings.titlecode!=missing){proptitlecode=settings.titlecode}
            if(settings.multiple!=missing){propmultiple=settings.multiple}
            if(settings.select!=missing){propselect=settings.select}
            if(settings.onselect!=missing){this.onselect=settings.onselect}
            if(settings.notfound!=missing){this.notfound=settings.notfound}
            
            if(propsubid!=""){
                // INTERROGAZIONE DI SECONDO LIVELLO
                // AUMENTO LE DIMENSIONI PER AVERE UN FEEDBACK
                prophelpwidth=610;
                prophelpheight=410;
            }

            if(settings.formid!=missing){
                propformid=settings.formid;
                if(_ismissing( $("#"+propname).prop("parentid") )){
                    // Aggancio alla maschera per quando i campi sono dinamici
                    $("#"+propname).prop("parentid", propformid);
                    _globalforms[propformid].controls[propname]=propname.substr(propformid.length);
                }
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            };
            if(settings.tag!=missing){this.tag=settings.tag};

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("ryhelper")
            .css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight,"background-color":"silver","font-family":"verdana,sans-serif","font-size":"13px","line-height":"normal"})
            .html("<a href='javascript:' id='"+propname+"_anchor'></a>");
            $("#"+propname+"_anchor").css({"position":"absolute","width":propwidth,"height":propheight,"text-decoration":"none","color":"transparent","background-color":"transparent","cursor":"default"});
            $("#"+propname+"_anchor").html("<div id='"+propname+"_internal'></div><div id='"+propname+"_button'></div>");
            $("#"+propname+"_internal").css({"position":"absolute","left":1,"top":1,"width":propwidth-2,"height":propheight-2,"color":"#000000","background-color":"#FFFFFF","overflow":"hidden"});
            $("#"+propname+"_internal").html("<div id='"+propname+"_text'></div>");
            $("#"+propname+"_text").css({"position":"absolute","cursor":"text","left":2,"top":1,"width":propwidth-20,"height":propheight-4,"overflow":"hidden","white-space":"nowrap"});
            $("#"+propname+"_button").css({"position":"absolute","cursor":"pointer","left":propwidth-20,"top":2,"width":18,"height":18,"background":"url("+_cambusaURL+"ryquiver/images/helper.png)"});
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_internal").css({"background-color":globalcolorfocus});
            			if(propfocusout){
            				propfocusout=false;
            			}
                        propobj.raisegotfocus();
            		}
            	}
            );
            $("#"+propname+"_anchor").focusout(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_internal").css({"background-color":"#FFFFFF"});
                        propobj.raiselostfocus();
                        propfocusout=true;
            		}
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
            		if(propenabled){
            			propctrl=k.ctrlKey; // da usare nella press
            			propshift=k.shiftKey;
            			if(k.which==46){ // delete
            				if(propctrl){
            					propobj.clear();
            				}
            			}
            			else if(k.which==113){ // F2
                            k.preventDefault();
            				propobj.showhelper();
            			}
                        else if(k.which==8){
                            return false;
                        }
                        else if(k.which==9){
                            return nextFocus(propname, propshift);
                        }
            		}
            	}
            );
            $("#"+propname).mousedown(
            	function(evt){
            		if(propenabled){
            			castFocus(propname);
            		}
            	}
            );
            $("#"+propname+"_button").click(
            	function(){
            		if(propenabled){
            			propobj.showhelper();
            		}
            	}
            );
             // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth});
            }
			this.showhelper=function(){
                if(settings.open!=missing){
                    settings.open(propobj);
                };
                qv_idrequest(propformid, {
                    subid:propsubid,
                    table:proptable, 
                    where:propwhere,
                    args:propargs,
                    select:propselect,
                    preselect:propsysid,
                    orderby:proporderby,
                    clause:propclause,
                    classtable:propclasstable,
                    title:proptitle,
                    titlecode:proptitlecode,
                    multiple:propmultiple,
                    onselect:function(d){
                        propobj.value(d["SYSID"], true);
                    },
                    close:function(){
                        propobj.focus();
                    }
                });
			}
			this.value=function(v, a){
				if(v===missing){
                    return propsysid;
				}
				else{
                    var single=false;
                    var caption="";
                    propsysid=_fittingvalue(v);
                    // Gestione modifica
                    propobj.modified(1);
                    _modifiedState(propname, true);
                    propobj.raisechanged();
                    if(a)
                        propobj.raiseassigned();
                    // Gestione visualizzazione
                    if(propmultiple){
                        if(propsysid.indexOf("|")>=0)
                            caption="<span style='color:silver;font-style:italic;'>(valori multipli)</span>";
                        else if(propsysid!="")
                            single=true;
                    }
                    else{
                        if(propsysid!="")
                            single=true;
                    }
                    // Visualizzazione
                    if(single){
                        $("#"+propname+"_text").html("<span style='color:silver;font-style:italic;'>Caricamento...</span>");
                        qv_queuehelper[propname]={"table":proptable, "sysid":propsysid, "select":propselect};
                        qv_queuehelpercall();
                    }
                    else{
                        $("#"+propname+"_text").html(caption);
                    }
				}
			}
			this.name=function(){
				return propname;
			}
			this.title=function(v){
				if(v==missing)
					return proptitle;
				else
					proptitle=v;
			}
			this.table=function(v){
				if(v==missing)
					return proptable;
				else
					proptable=v;
			}
			this.where=function(v){
				if(v==missing)
					return propwhere;
				else
					propwhere=v;
			}
			this.args=function(v){
				if(v==missing)
					return propargs;
				else
					propargs=v;
			}
			this.orderby=function(v){
				if(v==missing)
					return proporderby;
				else
					proporderby=v;
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v;
					if(v){
						$("#"+propname+"_anchor").removeAttr("disabled");
						$("#"+propname+"_text").css({"color":"#000000","cursor":"text"});
						$("#"+propname+"_button").css({"cursor":"pointer"});
					}
					else{
						$("#"+propname+"_anchor").attr("disabled",true);
						$("#"+propname+"_text").css({"color":"gray","cursor":"default"});
						$("#"+propname+"_button").css({"cursor":"default"});
					}
				}
			}
			this.visible=function(v){
				if(v==missing){
					return propvisible;
				}
				else{
					propvisible=v;
					if(v)
						$("#"+propname).css({"visibility":"visible"});
					else
						$("#"+propname).css({"visibility":"hidden"});
				}
			}
			this.modified=function(v){
				if(v==missing)
					return _bool( $("#"+propname).prop("modified") );
				else
					$("#"+propname).prop("modified", _bool(v) );
			}
			this.clear=function(){
                propsysid="";
                $("#"+propname+"_text").html("");
                propobj.modified(1);
                _modifiedState(propname, true);
                propobj.raisechanged();
                propobj.raiseassigned();
                propobj.raiseclear();
			}
			this.focus=function(){
				objectFocus(propname);
			}
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)};
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)};
            }
            this.raisechanged=function(){
                if(settings.changed!=missing){settings.changed(propobj)};
            }
            this.raiseassigned=function(){
                if(settings.assigned!=missing){settings.assigned(propobj)};
            }
            this.raiseclear=function(){
                if(settings.clear!=missing){settings.clear(propobj)};
            }
			return this;
		},
        ryselections:function(settings){
 			var propleft=20;
			var proptop=20;
            var propwidth=250;
            var propheight=200;
			var propobj=this;
            var proptitle="";
            var proptitlecode="";
			var propformid="";
            var propsubid="";
            var proptable="";
            var prophelptable="";
            var propwhere="";
            var propclause="";
            var propclausewhere="";
            var prophelpclause="";
            var proporderby="DESCRIPTION";
            var propparenttable="";
            var propparentfield="SYSID";
            var propselectedtable="";
            var propclasstable="";
            var propupward=0;
            var propchangerow=false;
            var propsolveid=false;
            var propparentid=""; 
			var propname=$(this).attr("id");

			if(settings.left!=missing){propleft=settings.left};
			if(settings.top!=missing){proptop=settings.top};
			if(settings.width!=missing){propwidth=settings.width};
			if(settings.height!=missing){propheight=settings.height};
            if(settings.title!=missing){proptitle=settings.title};
            if(settings.titlecode!=missing){proptitlecode=settings.titlecode};
            if(settings.formid!=missing){propformid=settings.formid};
            if(settings.subid!=missing){propsubid=settings.subid};
            if(settings.table!=missing){proptable=settings.table;prophelptable=proptable};
            if(settings.helptable!=missing){prophelptable=settings.helptable};
            if(settings.where!=missing){propwhere=settings.where};
            if(settings.orderby!=missing){proporderby=settings.orderby};
            if(settings.parenttable!=missing){propparenttable=settings.parenttable};
            if(settings.parentfield!=missing){propparentfield=settings.parentfield};
            if(settings.selectedtable!=missing){propselectedtable=settings.selectedtable};
            if(settings.classtable!=missing){propclasstable=settings.classtable}
            if(settings.upward!=missing){propupward=settings.upward}
            if(settings.changerow!=missing){propchangerow=settings.changerow};
            if(settings.solveid!=missing){propsolveid=settings.solveid};
            
            if(propwhere!=""){
                propwhere=" AND "+propwhere;
            }
            
            var actualid=propformid+"_"+propsubid;
            var prefix="#"+actualid;
            var h="";
            h+="<div id='"+actualid+"_oper_add' babelcode='REL_ADD'></div>";
            h+="<div id='"+actualid+"_oper_remove' babelcode='REL_REMOVE'></div>";
            h+="<div id='"+actualid+"_oper_empty' babelcode='REL_EMPTY'></div>";
            h+="<div id='"+actualid+"_gridsel'></div>";

            $("#"+propname)
            .css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight})
            .html(h);
            
            // AGGIUNGI SELEZIONE
            var oper_add=$(prefix+"_oper_add").rylabel({
                left:20,
                top:0,
                width:60,
                caption:"Aggiungi",
                formid:propformid,
                button:true,
                click:function(o){
                    qv_idrequest(propformid, {
                        table:prophelptable, 
                        where:"SYSID NOT IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+propparentid+"')"+propwhere,
                        clause:prophelpclause,
                        classtable:propclasstable,
                        title:proptitle,
                        multiple:true,
                        onselect:function(d){
                            var ids=d["SYSID"];
                            $.post(_cambusaURL+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessionid,
                                    "env":_sessioninfo.environ,
                                    "function":"selections_add",
                                    "data":{
                                        "UPWARD":propupward,
                                        "PARENTTABLE":propparenttable,
                                        "PARENTFIELD":propparentfield,
                                        "PARENTID":propparentid,
                                        "SELECTEDTABLE":propselectedtable,
                                        "SELECTION":ids
                                    }
                                }, 
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0){
                                            gridsel.refresh();
                                        }
                                        winzTimeoutMess(propformid, v.success, v.message);
                                    }
                                    catch(e){
                                        winzClearMess(propformid);
                                        alert(d);
                                    }
                                }
                            );
                        }
                    });
                }
            });
            // RIMUOVI SELEZIONE
            var oper_remove=$(prefix+"_oper_remove").rylabel({
                left:100,
                top:0,
                width:60,
                caption:"Rimuovi",
                formid:propformid,
                button:true,
                click:function(o){
                    gridsel.selengage(
                        function(o, s){
                            $.post(_cambusaURL+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessionid,
                                    "env":_sessioninfo.environ,
                                    "function":"selections_remove",
                                    "data":{
                                        "PARENTID":propparentid,
                                        "SELECTION":s
                                    }
                                }, 
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0){
                                            gridsel.refresh();
                                        }
                                        winzTimeoutMess(propformid, v.success, v.message);
                                    }
                                    catch(e){
                                        winzClearMess(propformid);
                                        alert(d);
                                    }
                                }
                            );
                        }
                    );
                }
            });
            // SVUOTA SELEZIONE
            var oper_empty=$(prefix+"_oper_empty").rylabel({
                left:180,
                top:0,
                width:60,
                caption:"Svuota",
                formid:propformid,
                button:true,
                click:function(o){
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_remove",
                            "data":{
                                "PARENTID":propparentid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    gridsel.refresh();
                                }
                                winzTimeoutMess(propformid, v.success, v.message);
                            }
                            catch(e){
                                winzClearMess(propformid);
                                alert(d);
                            }
                        }
                    );
                }
            });
            var gridsel=$(prefix+"_gridsel").ryque({
                left:0,
                top:30,
                width:propwidth,
                height:propheight,
                formid:propformid,
                numbered:false,
                checkable:true,
                environ:_sessioninfo.environ,
                from:proptable,
                orderby:proporderby,
                columns:[
                    {id:"DESCRIPTION", caption:proptitle, width:200, code:proptitlecode}
                ],
                changerow:function(o,i){
                    oper_remove.enabled(o.isselected());
                    oper_empty.enabled(o.count()>0);
                    if(propchangerow!==false){
                        propchangerow();
                    }
                    if(i>0){
                        o.solveid(i);
                    }
                },
                selchange:function(o, i){
                    oper_remove.enabled(o.isselected());
                },
                solveid:function(o, d){
                    oper_remove.enabled(1);
                    if(propsolveid!==false){
                        propsolveid(d);
                    }
                }
            });
			this.parentid=function(v, after){
                propparentid=v;
                oper_add.enabled(0);
                oper_remove.enabled(0);
                oper_empty.enabled(0);
                gridsel.clear();
                gridsel.enabled(0);
                if(v!=""){
                    gridsel.clause(propclause)
                    gridsel.where("SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+propparentid+"')"+propclausewhere);
                    gridsel.query({
                        ready:function(){
                            gridsel.enabled(1);
                            oper_add.enabled(1);
                            if(after!=missing)
                                after();
                        }
                    });
                }
                else{
                    if(after!=missing)
                        after();
                }
			}
			this.clear=function(){
                gridsel.clear();
			}
            this.setid=function(id){
                gridsel.search({
                        "where": _ajaxescapize("SYSID='"+id+"'")
                    },
                    function(d){
                        var ind=0;
                        try{
                            var v=$.parseJSON(d);
                            ind=v[0];
                        }
                        catch(e){}
                        gridsel.index(ind);
                    }
                );
            }
            this.where=function(q){
                propwhere=q;
                if(propwhere!=""){
                    propwhere=" AND "+propwhere;
                }
            }
            this.clause=function(jclause){
                propclause=jclause;
                propclausewhere="";
                if(typeof(propclause)=="object"){
                    for(n in propclause){
                        propclausewhere+=(" AND "+n+"='"+propclause[n]+"'");
                    }
                }
            }
			return this;
		}       
	});
})(jQuery);

function qv_queuemanager(){
    if(!qv_queuebusy){
        if(_objectlength(qv_queuelist)>0){
            qv_queuelistcall();
        }
        else if(_objectlength(qv_queuehelper)>0){
            qv_queuehelpercall();
        }
        else if(_objectlength(qv_queuequery)>0){
            qv_queuequerycall();
        }
    }
}

function qv_queuelistcall(){
    var id=false;
    for(id in qv_queuelist){break;}
    if(id!==false){
        qv_queuebusy=true;
        var table=qv_queuelist[id]["table"];
        delete qv_queuelist[id];
        try{
            var o=globalobjs[id];
            if(table.substr(0,1)=="#"){
                o.additem({caption:"", key:""});
                table=table.substr(1);
            }
            if(window.console&&_sessioninfo.debugmode){console.log("LIST: SELECT SYSID,DESCRIPTION FROM "+table+" ORDER BY DESCRIPTION");}
            RYQUEAUX.query({
                sql:"SELECT SYSID,DESCRIPTION FROM "+table+" ORDER BY DESCRIPTION",
                ready:function(v){
                    try{
                        for(var i in v){
                            o.additem({caption:v[i]["DESCRIPTION"], key:v[i]["SYSID"]});
                        }
                        qv_queuebusy=false;
                        setTimeout(function(){qv_queuemanager()});
                    }catch(e){
                        if(window.console){console.log(e.message)}
                        qv_queuebusy=false;
                        setTimeout(function(){qv_queuemanager()});
                    }
                }
            });
        }catch(e){
            if(window.console){console.log(e.message)}
            qv_queuebusy=false;
            setTimeout(function(){qv_queuemanager()});
        }
    }
}

function qv_queuehelpercall(){
    var id=false;
    for(id in qv_queuehelper){break;}
    if(id!==false){
        qv_queuebusy=true;
        var table=qv_queuehelper[id]["table"];
        var sysid=qv_queuehelper[id]["sysid"];
        var select=qv_queuehelper[id]["select"];
        var more="";
        delete qv_queuehelper[id];
        try{
            if(globalobjs[id].onselect){
                if(select!="")
                    more+=","+select;
            }
            if(window.console&&_sessioninfo.debugmode){console.log("HELPER: SELECT DESCRIPTION "+more+" FROM "+table+" WHERE SYSID='"+sysid+"'");}
            RYQUEAUX.query({
                sql:"SELECT SYSID,DESCRIPTION "+more+" FROM "+table+" WHERE SYSID='"+sysid+"'",
                ready:function(d){
                    try{
                        if(d.length>0){
                            // ELIMINO I NULL
                            for(var i in d[0]){
                                d[0][i]=_fittingvalue(d[0][i]);
                            }
                            $("#"+id+"_text").html(d[0]["DESCRIPTION"]);
                            if(globalobjs[id].onselect){
                                globalobjs[id].onselect(globalobjs[id], d[0]);
                            }
                        }
                        else{
                            $("#"+id+"_text").html("<span style='color:silver;font-style:italic;'>(not found)</span>");
                            if(globalobjs[id].notfound){
                                globalobjs[id].notfound(globalobjs[id]);
                            }
                        }
                        qv_queuebusy=false;
                        setTimeout(function(){qv_queuemanager()});
                    }catch(e){
                        if(window.console){console.log(e.message)}
                        qv_queuebusy=false;
                        setTimeout(function(){qv_queuemanager()});
                    }
                }
            });
        }catch(e){
            if(window.console){console.log(e.message)}
            qv_queuebusy=false;
            setTimeout(function(){qv_queuemanager()});
        }
    }
}

function qv_queuequerycall(){
    var id=false;
    // PRENDO IL PRIMO ELEMENTO DELLA CODA
    for(id in qv_queuequery){break;}
    if(id!==false){
        qv_queuebusy=true;
        try{
            var sql;
            // DETERMINO LA QUERY
            if(_isset(qv_queuequery[id]["sql"])){
                sql=qv_queuequery[id]["sql"];
            }
            else{
                var table=qv_queuequery[id]["table"];
                var select=qv_queuequery[id]["select"];
                var whe=qv_queuequery[id]["where"];
                sql="SELECT "+select+" FROM "+table+" WHERE "+whe;
            }
            // DETERMINO L'AZIONE (SINCRONA O TERMINALE) DA INTRAPRENDERE SUCCESSIVAMENTE
            var back=qv_queuequery[id]["back"];
            // TOLGO L'ISTRUZIONE DALLA CODA
            delete qv_queuequery[id];
            if(window.console&&_sessioninfo.debugmode){console.log("QUERY: "+sql);}
            RYQUEAUX.query({
                sql: sql,
                ready:function(d){
                    try{
                        back(d);
                    }catch(e){
                        if(window.console){
                            console.log(e.message);
                            console.log(d);
                        }
                    }
                    qv_queuebusy=false;
                    setTimeout(function(){qv_queuemanager()});
                }
            });
        }catch(e){
            if(window.console){console.log(e.message)}
            qv_queuebusy=false;
            setTimeout(function(){qv_queuemanager()});
        }
    }
}

function qv_changemanagement(formid, objtabs, lblengage, options, missing){
    if(RYWINZ.modified(formid)){
        objtabs.suspend(true);
        newtab=objtabs.currtab();
        prevtab=objtabs.prevtab();
        var dlg=winzDialogGet(formid);
        var hangerid=dlg.hanger;
        var h="";
        var vK=[];
        winzDialogParams(dlg, {
            width:500,
            height:180,
            open:function(){
                castFocus(formid+"__save");
            },
            close:function(){
                winzDisposeCtrl(formid, vK);
                winzDialogFree(dlg);
            },
            cancel:function(){
                objtabs.currtab(prevtab, true);
            }
        });
        // DEFINIZIONE DEL CONTENUTO
        h+="<div class='winz_msgbox'>";
        h+=RYBOX.babels("MSG_DATANOTSAVE");
        h+="</div>";
        h+=winzAppendCtrl(vK, formid+"__save");
        h+=winzAppendCtrl(vK, formid+"__abandon");
        h+=winzAppendCtrl(vK, formid+"__cancel");
        $("#"+hangerid).html(h);
        $("#"+formid+"__save").rylabel({
            left:20,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_SAVE"),
            button:true,
            formid:formid,
            click:function(o){
                objtabs.currtab(prevtab, true);
                lblengage.engage(
                    function(){
                        objtabs.currtab(newtab);
                        winzDialogClose(dlg);
                    }
                );
            }
        });
        $("#"+formid+"__abandon").rylabel({
            left:120,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_ABANDON"),
            button:true,
            formid:formid,
            click:function(o){
                if(options!=missing){
                    if(options.abandon!=missing)
                        options.abandon();
                }
                RYWINZ.modified(formid, 0);
                objtabs.currtab(prevtab, true);
                objtabs.currtab(newtab);
                winzDialogClose(dlg);
            }
        });
        var _bt_cancel=$("#"+formid+"__cancel").rylabel({
            left:220,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_CANCEL"),
            button:true,
            formid:formid,
            click:function(o){
                winzDialogClose(dlg);
                dlg.cancel();
            }
        });
        // MOSTRO LA DIALOGBOX
        winzDialogOpen(dlg);
    }
    return objtabs.suspend();
}
function qv_changerowmanagement(formid, objgrid, newindex){
    var cancel=false;
    if(RYWINZ.modified(formid)){
        cancel=true;
        var dlg=winzDialogGet(formid);
        var hangerid=dlg.hanger;
        var h="";
        var vK=[];
        winzDialogParams(dlg, {
            width:500,
            height:180,
            open:function(){
                castFocus(formid+"__abandon");
            },
            close:function(){
                winzDisposeCtrl(formid, vK);
                winzDialogFree(dlg);
            }
        });
        // DEFINIZIONE DEL CONTENUTO
        h+="<div class='winz_msgbox'>";
        h+=RYBOX.babels("MSG_ROWNOTSAVE");
        h+="</div>";
        h+=winzAppendCtrl(vK, formid+"__abandon");
        h+=winzAppendCtrl(vK, formid+"__cancel");
        $("#"+hangerid).html(h);
        $("#"+formid+"__abandon").rylabel({
            left:20,
            top:dlg.height-40,
            width:80,
            caption:"Abbandona",
            button:true,
            formid:formid,
            click:function(o){
                RYWINZ.modified(formid, 0);
                objgrid.index(newindex);
                winzDialogClose(dlg);
            }
        });
        var _bt_cancel=$("#"+formid+"__cancel").rylabel({
            left:120,
            top:dlg.height-40,
            width:80,
            caption:"Annulla",
            button:true,
            formid:formid,
            click:function(o){
                winzDialogClose(dlg);
            }
        });
        // MOSTRO LA DIALOGBOX
        winzDialogOpen(dlg);
    }
    return cancel;
}
function qv_contextmanagement(context, params, missing){
    if(context==""){
        RYQUEAUX.query({
            sql:"SELECT "+params.select+" FROM "+params.table+" WHERE SYSID='"+params.sysid+"'",
            ready:function(v){
                try{
                    context=params.formula;
                    var fields=params.select.split(",");
                    for(var i in fields)
                        context=context.replace("[="+fields[i]+"]", v[0][ fields[i] ]);
                }catch(e){}
                if(params.done!=missing){
                    params.done(context);
                }
            } 
        });
    }
    else if(params.done!=missing){
        params.done(context);
    }
}
function qv_print(sourceid, option, missing){
    var h=$("#"+sourceid).html();
    $("#winz-printing").html(h);
    $("#winz-printing").printThis({importCSS:false});
}
function qv_printText(htext, option, missing){
    htext=htext.replace(/\[PAGEBREAK\]/ig, "<p style='page-break-before:always'></p>");
    htext="<div style='font-family:sans-serif;'>"+htext+"</div>";
    $("#winz-printing").html(htext);
    $("#winz-printing").printThis({importCSS:false});
}
$(document).ready(function(){
    RYBOX.babels({
        "MSG_DATANOTSAVE":"I dati sono stati modificati. Salvare?",
        "MSG_ROWNOTSAVE":"I dati sono stati modificati. Abbandonare la riga?",
        "BUTTON_SAVE":"Salva",
        "BUTTON_ABANDON":"Abbandona",
        "BUTTON_CANCEL":"Annulla",
        "BUTTON_REFRESH":"Aggiorna",
        "BUTTON_RESET":"Pulisci",
        "BUTTON_OK":"OK",
        "BUTTON_DELETE":"Elimina",
        "DESCRIPTION":"Descrizione",
        "SEARCH":"Ricerca",
        "CLASS":"Classe",
        "HLP_SELCLASS":"Selezione classe"
    });
});
