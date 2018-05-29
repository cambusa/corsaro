/****************************************************************************
* Name:            ryquiver.js                                              *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
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
    h+=RYBOX.babels("MSG_PRINTFORMAT");
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
    if(template.substr(0, 11)=="@customize/")
        template=template.replace("@customize/", _systeminfo.relative.customize);
    else
        template=_systeminfo.relative.customize+_sessioninfo.app+"/reporting/"+template;
    objgrid.selengage(   // Elenco dei SYSID selezionati
        function(o,s){
            winzProgress(formid);
            s=s.split("|");
            $.engage(template, 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "keys":s,
                    "pdf":pdf,
                    "params":params
                }, 
                function(d){
                    try{
                        if(window.console&&_sessioninfo.debugmode){console.log("Risposta da reporting: "+d)}
                        var h=_systeminfo.relative.cambusa+"rysource/source_download.php?file="+d;
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
            alert(RYBOX.babels("MSG_NOSELECTION"));
        }
    );
}
function qv_titlebar(objtabs, settings){
    // SE NON E' VISIBILE LA BARRA DEI CONTROLLI, METTE IL TITOLO SULLA BARRA DEI TABS
    // mosca
    // RESO INUTILE DAL MECCANISMO CHE PORTA IL TITOLO SULL'MDI
    /*
    if($.isset(settings.controls) && !settings.controls.actualBoolean() && $.isset(settings.title)){
        var handler=objtabs.customleft();
        $("#"+handler)
        .css({"margin":"3px 25px"})
        .html("<span id='"+handler+"__title' style='cursor:default;padding-right:50px;color:#006699;'></span>");
        $("#"+handler+"__title").css({"font-size":"18px", "line-height":"18px"}).html(settings.title);
    }
    */
}
function qv_bulkdelete(formid, objgrid, prefix){
    winzMessageBox(formid, {
        message:RYBOX.babels("MSG_DELETESELROWS"),
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
                    $.engage(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
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
function qv_filedelete(formid, objgrid, excludedid, after, missing){
    winzMessageBox(formid, {
        message:RYBOX.babels("MSG_DELETESELROWS"),
        ok:RYBOX.babels("BUTTON_DELETE"),
        confirm:function(){
            objgrid.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    winzProgress(formid);
                    s=s.split("|");
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di cancellazione
                        stats.push({
                            "function":"files_detach",
                            "data":{
                                "SYSID":s[i],
                                "EXCLUDEDID": excludedid
                            },
                            "pipe":{
                                "SYSID":"#FILEID"
                            }
                        });
                        stats.push({
                            "function":"files_delete",
                            "data":{}
                        });
                    }
                    $.engage(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "program":stats
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    if(after!=missing){
                                        after();
                                    }
                                }
                                winzTimeoutMess(formid, v.success, v.message);
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
                        $.engage(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                            {
                                "sessionid":_sessioninfo.sessionid,
                                "env":_sessioninfo.environ,
                                "function":"files_export",
                                "data":data
                            }, 
                            function(d){
                                try{
                                    var v=$.parseJSON(d);
                                    if(v.success>0){
                                        var env=v["params"]["ENVIRON"];
                                        var n=v["params"]["EXPORT"];
                                        var h=_systeminfo.relative.cambusa+"rysource/source_download.php?env="+env+"&sessionid="+_sessioninfo.sessionid+"&file="+n;
                                        if(window.console&&_sessioninfo.debugmode){console.log("Download:"+h)}
                                        $("#winz-iframe").prop("src", h);
                                        // GESTIONE FILE OBSOLETI
                                        RYQUIVER.ManageTemp();
                                    }
                                    winzTimeoutMess(formid, v.success, v.message);
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
    var tipologyid="";
    var propobj=this; 
    var merge=missing;
    var paramchangerow=missing;
    var paramsolveid=missing;
    var paramupdate=missing;
    var propenabled=true;
	var curr_importname="";
	var curr_subpath="";
	var curr_ext="";
	var envattachments="";
    var bufferdetails="";
    if(params!=missing){
        if(typeof(params)==="string"){
            merge=params;
        }
        else{
            if(params.merge){merge=params.merge}
            if(params.changerow){paramchangerow=params.changerow}
            if(params.solveid){paramsolveid=params.solveid}
            if(params.update){paramupdate=params.update}
        }
    }
    var h="";
    h+='<div id="'+formid+'docs_context"></div>';
    h+='<div id="'+formid+'griddocs"></div>';
    h+='<div id="'+formid+'oper_fileinsert"></div>';
    h+='<div id="'+formid+'oper_fileaddnote" babelcode="FILE_ADDNOTE"></div>';
    h+='<div id="'+formid+'oper_filerefresh" babelcode="FILE_REFRESH"></div>';
    h+='<div id="'+formid+'oper_fileunsaved" babelcode="BABEL_UNSAVED"></div>';
    h+='<div id="'+formid+'lb_filedescription" babelcode="FILE_DESCRIPTION"></div>';
    h+='<div id="'+formid+'tx_filedescription"></div>';
    h+='<div id="'+formid+'lb_filedate" babelcode="FILE_DATE"></div>';
    h+='<div id="'+formid+'lb_filesorter" babelcode="FILE_SORTER"></div>';
    h+='<div id="'+formid+'tx_filedate"></div>';
    h+='<div id="'+formid+'tx_filesorter"></div>';
    h+='<div id="'+formid+'oper_fileupdate" babelcode="FILE_UPDATE"></div>';
    h+='<div id="'+formid+'oper_filedetails" babelcode="FILE_DETAILS"></div>';
    h+='<div id="'+formid+'oper_filedownload" babelcode="FILE_DOWNLOAD"></div>';
	h+='<div id="'+formid+'oper_filepreview" babelcode="FILE_PREVIEW"></div>';
    h+='<div id="'+formid+'oper_filedelete" babelcode="BUTTON_SELDELETE"></div>';
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
                {id:"SORTER", caption:"", width:38, type:0},
                {id:"RECORDID", caption:"", width:0, type:""}
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
				curr_importname="";
				curr_subpath="";
				curr_ext="";
                bufferdetails="";
                oper_fileupdate.enabled(0);
                oper_filedetails.enabled(0);
                oper_filedownload.enabled(0);
				oper_filepreview.enabled(0);
                oper_filedelete.enabled(0);
                oper_fileunsaved.visible(0);
                if(paramchangerow!=missing){
                    paramchangerow();
                }
                if(i>0){
                    o.solveid(i);
                }
            },
            changesel:function(o){
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
                            bufferdetails=v[0]["REGISTRY"];
							curr_importname=__(v[0]["IMPORTNAME"]);
							curr_subpath=__(v[0]["SUBPATH"]);

							//curr_ext=__(v[0]["EXTENSION"]);
							if(curr_importname.match(/[^\.]+$/))
								curr_ext=curr_importname.match(/[^\.]+$/)[0];
							else
								curr_ext="";

                            oper_filedetails.title(bufferdetails);
                            oper_fileupdate.enabled(1);
                            oper_filedetails.enabled(1);
                            if(curr_importname!=""){
                                oper_filedownload.enabled(1);
								oper_filepreview.enabled(1);
							}
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
                //qv_filedownload(formid, o, {"mergetable":merge, "mergeid":currsysid});
				oper_filepreview.engage();
            }
        });
        
        $(prefix+"oper_fileinsert").ryupload({
            left:430,
            top:90,
            width:300,
            formid:formid,
            environ:_sessioninfo.temporary,
            complete:function(id, name, ret){
                //$(prefix+"oper_fileinsert .qq-upload-success , .qq-upload-fail").remove();
                $.engage(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessioninfo.sessionid,
                        "env":_sessioninfo.environ,
                        "program":[
                            {
                                "function":"files_insert",
                                "data":{
                                    "IMPORTNAME":name,
                                    "SUBPATH":currsysid.subright(2)
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
                            if(v.success>0){
                                // POSIZIONAMENTO SUL NUOVO DOCUMENTO
                                objgriddocs.splice(0, 0, v.SYSID,
                                    function(){
                                        if(paramupdate!=missing){
                                            setTimeout(function(){paramupdate(v.SYSID)}, 100);
                                        }
                                    }
                                );
                            }
                            winzTimeoutMess(formid, v.success, v.message);
                        }
                        catch(e){
                            winzClearMess(formid);
                            alert(d);
                        }
                    }
                );
            }
        });

        var oper_fileaddnote=$(prefix+"oper_fileaddnote").rylabel({
            left:540,
            top:90,
            width:100,
            caption:"Inserisci nota",
            formid:formid,
            button:true,
            click:function(o){
                $.engage(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessioninfo.sessionid,
                        "env":_sessioninfo.environ,
                        "program":[
                            {
                                "function":"files_insert",
                                "data":{
                                    "DESCRIPTION":"(nuova nota)"
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
                            if(v.success>0){
                                // POSIZIONAMENTO SUL NUOVO DOCUMENTO
                                objgriddocs.splice(0, 0, v.SYSID,
                                    function(){
                                        if(paramupdate!=missing){
                                            setTimeout(function(){paramupdate(v.SYSID)}, 100);
                                        }
                                    }
                                );
                            }
                            winzTimeoutMess(formid, v.success, v.message);
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
                $.engage(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessioninfo.sessionid,
                        "env":_sessioninfo.environ,
                        "function":"files_update",
                        "data":{
                            "SYSID":filesysid,
                            "DESCRIPTION": tx_filedescription.value(),
                            "AUXTIME": tx_filedate.text(),
                            "CROSSID":filecrossid,
                            "SORTER":tx_filesorter.value()
                        }
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success>0){ 
                                objgriddocs.dataload(
                                    function(){
                                        if(paramupdate!=missing){
                                            setTimeout(function(){paramupdate(filesysid)}, 100);
                                        }
                                    }
                                );
                                oper_fileunsaved.visible(0);
                            }
                            winzTimeoutMess(formid, v.success, v.message);
                        }
                        catch(e){
                            winzClearMess(formid);
                            alert(d);
                        }
                    }
                );
            }
        });
        
        var oper_filedetails=$(prefix+"oper_filedetails").rylabel({
            left:650,
            top:300,
            width:90,
            caption:"Dettagli",
            formid:formid,
            button:true,
            click:function(o){
                // DEFINIZIONE DELLA DIALOGBOX
                var dlg=winzDialogGet(formid);
                var hangerid=dlg.hanger;
                var h="";
                var vK=[];
                winzDialogParams(dlg, {
                    width:750,
                    height:530,
                    open:function(){
                        castFocus(formid+"file_dlgdetails");
                    },
                    close:function(){
                        winzDisposeCtrl(formid, vK);
                        winzDialogFree(dlg);
                    }
                });
                // CONTENUTO
                h+=winzAppendCtrl(vK, formid+"file_dlgdetails");
                h+=winzAppendCtrl(vK, formid+"file_dlgok");
                h+=winzAppendCtrl(vK, formid+"file_dlgcancel");
                $("#"+hangerid).html(h);
                // DEFINIZIONE CAMPI
                var tx_dlgdetails=$("#"+formid+"file_dlgdetails").ryedit({left:20, top:80, width:700, height:400});
                tx_dlgdetails.value(bufferdetails);
                $("#"+formid+"file_dlgok").rylabel({
                    left:20,
                    top:dlg.height-40,
                    width:80,
                    caption:RYBOX.babels("BUTTON_OK"),
                    button:true,
                    formid:formid,
                    click:function(o){
                        bufferdetails=tx_dlgdetails.value();
                        oper_filedetails.title(bufferdetails);
                        $.engage(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                            {
                                "sessionid":_sessioninfo.sessionid,
                                "env":_sessioninfo.environ,
                                "function":"files_update",
                                "data":{
                                    "SYSID":filesysid,
                                    "DESCRIPTION": tx_filedescription.value(),
                                    "AUXTIME": tx_filedate.text(),
                                    "CROSSID":filecrossid,
                                    "SORTER":tx_filesorter.value(),
                                    "REGISTRY":bufferdetails
                                }
                            }, 
                            function(d){
                                try{
                                    var v=$.parseJSON(d);
                                    if(v.success>0){ 
                                        objgriddocs.dataload(
                                            function(){
                                                if(paramupdate!=missing){
                                                    setTimeout(function(){paramupdate(filesysid)}, 100);
                                                }
                                            }
                                        );
                                        oper_fileunsaved.visible(0);
                                    }
                                    winzTimeoutMess(formid, v.success, v.message);
                                }
                                catch(e){
                                    winzClearMess(formid);
                                    alert(d);
                                }
                                winzDialogClose(dlg);
                            }
                        );
                    }
                });
                $("#"+formid+"file_dlgcancel").rylabel({
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
                
                // MOSTRO LA DIALOGBOX
                winzDialogOpen(dlg);
            }
        });

        var oper_filedownload=$(prefix+"oper_filedownload").rylabel({
            left:430,
            top:300,
			width:90,
            caption:"Download",
            formid:formid,
            button:true,
            click:function(o){
                qv_filedownload(formid, objgriddocs, {"mergetable":merge, "mergeid":currsysid});
            }
        });

        var oper_filepreview=$(prefix+"oper_filepreview").rylabel({
            left:540,
            top:300,
			width:90,
            caption:"Anteprima",
            formid:formid,
            button:true,
            click:function(o){
				RYWINZ.AttachPreview(formid, curr_subpath+filesysid+"."+curr_ext);
            }
        });

        var oper_filedelete=$(prefix+"oper_filedelete").rylabel({
            left:430,
            top:340,
			width:120,
            caption:"Elimina selezionati",
            formid:formid,
            button:true,
            click:function(o){
                qv_filedelete(formid, objgriddocs, propobj.tipologyid, 
                    function(){
                        if(paramupdate!=missing){
                            setTimeout(function(){paramupdate("")}, 100);
                        }
                    }
                );
            }
        });
    }
    this.initialize=function(sysid, context, typologyid, missing){
        if(lb_docs_context){
            lb_docs_context.caption(context);
            propobj.tipologyid=typologyid;
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
                oper_fileaddnote.visible(objgriddocs.isselected());
                oper_filedelete.enabled(objgriddocs.isselected());
            }
            else{
                $(prefix+"oper_fileinsert").css({"display":"none"});
                oper_fileaddnote.visible(0);
                oper_filedelete.enabled(0);
            }
            tx_filedescription.enabled(v);
            tx_filedate.enabled(v);
            tx_filesorter.enabled(v);
            oper_filedetails.enabled(v);
        }
        return propenabled;
    }
    return this;
}
function qv_autoconfigure(formid, viewname, tableprefix, typologyid, offsety, cacheext, action, missing){
    // DISTRUGGO I VECCHI CAMPI DELLA TIPOLOGIA
    var prefix="#"+formid;
    var tableviews=tableprefix+"VIEWS";
    var table=tableprefix+"S";
    var flagload=false;     // DEVO LEGGERE LA CONFIGURAZIONE DEI CAMPI?
    var flagdispose=false;  // DEVO ELIMINARE I VECCHI CAMPI?
    if(cacheext["_PREVTYPOLOGYID"]==missing){
        // LA FUNZIONE E' CHIAMATA PER LA PRIMA VOLTA
        cacheext["_PREVTYPOLOGYID"]=typologyid;
        cacheext["_CURRCONFIG"]={};
        flagload=true;
    }
    else{
        if(cacheext["_PREVTYPOLOGYID"]!=typologyid){
            flagload=(cacheext[typologyid]==missing);
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
    RYWINZ.MaskClear(formid, "C");
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
                dec=d.actualInteger();
            else
                dec=2;
        }
        else if(tp.indexOf("CHAR")>=0){
            var d=tp.replace(/[^0-9]/g, "");
            if(d.actualInteger()>300)
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
    setTimeout(function(){TAIL.wriggle});
    return offsety;
}
function qv_queuelistcall(params){
    try{
        var id=params.id;
        var table=params.table;
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
                }catch(e){
                    if(window.console){console.log(e.message)}
                }
                TAIL.free();
            }
        });
    }catch(e){
        if(window.console){console.log(e.message)}
        TAIL.free();
    }
}

function qv_queuehelpercall(params){
    try{
        var id=params.id;
        var table=params.table;
        var sysid=params.sysid;
        var select=params.select;
        var assigned=params.assigned;
        var more="";
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
                            d[0][i]=__(d[0][i]);
                        }
                        $("#"+id+"_text").html(d[0]["DESCRIPTION"]);
                        if(globalobjs[id].onselect){
                            globalobjs[id].onselect(globalobjs[id], d[0], assigned);
                        }
                    }
                    else{
                        $("#"+id+"_text").html("<span style='color:silver;font-style:italic;'>(not found)</span>");
                        if(globalobjs[id].notfound){
                            globalobjs[id].notfound(globalobjs[id]);
                        }
                    }
                }catch(e){
                    if(window.console){console.log(e.message)}
                }
                TAIL.free();
            }
        });
    }catch(e){
        if(window.console){console.log(e.message)}
        TAIL.free();
    }
}

function qv_queuequerycall(params){
    try{
        var sql;
        // DETERMINO LA QUERY
        if($.isset(params["sql"])){
            sql=params["sql"];
        }
        else if($.isset(params["fsql"])){
            sql=params["fsql"]();
        }
        else{
            var table=params["table"];
            var select=params["select"];
            var whe=params["where"];
            sql="SELECT "+select+" FROM "+table+" WHERE "+whe;
        }
        // DETERMINO L'AZIONE (SINCRONA O TERMINALE) DA INTRAPRENDERE SUCCESSIVAMENTE
        var back=params["back"];
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
                TAIL.free();
            }
        });
    }catch(e){
        if(window.console){console.log(e.message)}
        TAIL.free();
    }
}

function qv_changemanagement(formid, objtabs, lblengage, options, missing){
    var cancel=false;
    if(RYWINZ.modified(formid)){
        cancel=true;
        newtab=objtabs.currtab();
        prevtab=objtabs.prevtab();
        objtabs.currtab(prevtab, true);
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
                winzDialogClose(dlg);
                lblengage.engage(
                    function(issue){
                        if(issue!==false){
                            objtabs.currtab(newtab);
                        }
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
                winzDialogClose(dlg);
                RYWINZ.modified(formid, 0);
                objtabs.currtab(newtab);
                if(options!=missing){
                    if(options.abandon!=missing)
                        options.abandon();
                }
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
            }
        });
        // MOSTRO LA DIALOGBOX
        winzDialogOpen(dlg);
    }
    return cancel;
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
function qv_forlikeclause(t){
    return t.toUpperCase().replace(/ /g,"%").replace(/[^A-Z0-9]/g,"%");
}
$(document).ready(function(){
    RYBOX.babels({
        "MSG_DATANOTSAVE":"I dati sono stati modificati. Salvare?",
        "MSG_ROWNOTSAVE":"I dati sono stati modificati. Abbandonare la riga?",
        "MSG_PRINTFORMAT":"Quale formato utilizzare per la stampa?",
        "MSG_DELETESELROWS":"Eliminare le righe selezionate?",
        "MSG_NOSELECTION":"Nessun elemento selezionato",
        "BUTTON_SAVE":"Salva",
        "BUTTON_ABANDON":"Abbandona",
        "BUTTON_CANCEL":"Annulla",
        "BUTTON_OK":"OK",
        "BUTTON_DELETE":"Elimina",
        "DESCRIPTION":"Descrizione",
        "SEARCH":"Ricerca",
        "CLASS":"Classe"
    });
});
