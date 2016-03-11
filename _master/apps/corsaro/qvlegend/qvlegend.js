/****************************************************************************
* Name:            qvlegend.js                                              *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvlegend(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var currqueryid="";
    var typelegend=RYQUE.formatid("0LEGEND00000");
    var typequery=RYQUE.formatid("0LEGENDQUERY");
    var typemov=RYQUE.formatid("0MOVIMENTI00");
    var currexported="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var flagfocus=false;
    var loadedsysidC="";
    var loadedsysidD="";
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_LEGEND",
        orderby:"DESCRIPTION,SYSID",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:200}
        ],
        changerow:function(o,i){
            currsysid="";
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            //oper_clone.enabled(0);
            //oper_export.enabled(0);
            //oper_download.visible(0);
            oper_delete.enabled(o.isselected());
            context="";
            if(i>0){
                o.solveid(i);
            }
        },
        changesel:function(o){
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currsysid=d;
            objtabs.enabled(2,true);
            objtabs.enabled(3,true);
            //oper_clone.enabled(1);
            //oper_export.enabled(1);
            oper_delete.enabled(1);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(2);
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    var offsety=80;
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});
    offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                },
                ready:function(){
                    if(done!=missing){done()}
                }
            });
        }
    });
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:430,
        top:210,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data={};
            data["DESCRIPTION"]="(nuova configurazione)";
            data["TYPOLOGYID"]=typelegend;
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_insert",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.SYSID;
                            flagopen=true;
                            objgridsel.splice(0, 0, newid);
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

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:340,
        caption:"Elimina riga selezionata",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare la configurazione selezionata?",
                ok:"Elimina",
                confirm:function(){
                    winzProgress(formid);
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"legend_delete",
                            "data":{
                                "LEGENDID":currsysid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    objgridsel.refresh();
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
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;

    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:100, top:offsety, width:350, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    
    offsety+=30;
    $(prefix+"LB_PROCESSOID").rylabel({left:20, top:offsety, caption:"Processo"});
    $(prefix+"PROCESSOID").ryhelper({
        left:100, top:offsety, width:350, datum:"C", tag:"PROCESSOID", formid:formid, table:"QW_PROCESSI", title:"Scelta processo",
        open:function(o){
            o.where("");
        }
    });

    offsety+=30;
    $(prefix+"LB_CONTOID").rylabel({left:20, top:offsety, caption:"Conto"});
    $(prefix+"CONTOID").ryhelper({
        left:100, top:offsety, width:350, datum:"C", tag:"CONTOID", formid:formid, table:"QW_CONTI", title:"Scelta conto",
        open:function(o){
            o.where("");
        }
    });

    offsety+=30;
    $(prefix+"LB_TOLERANCE").rylabel({left:20, top:offsety, caption:"Tolleranza"});
    $(prefix+"TOLERANCE").rynumber({left:100, top:offsety, width:150, numdec:2, datum:"C", tag:"TOLERANCE"});
    
    offsety+=50;
    var objscript=$(prefix+"SEEKER").ryselections({"left":20, "top":offsety, "width":500, "height":300, 
        "title":"Script",
        "formid":formid, 
        "subid":"C",
        "table":"QW_LEGENDSCRIPT",
        "where":"",
        "parenttable":"QVOBJECTS", 
        "parentfield":"SYSID",
        "selectedtable":"QVOBJECTS"
    });
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            RYWINZ.modified(formid, 0);
                            if(done!=missing){done()}
                        }
                        objgridsel.dataload();
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

    // DEFINIZIONE TAB DETTAGLI
    offsety=90;
    var lb_queries_context=$(prefix+"queries_context").rylabel({left:20, top:50, caption:""});

    var operq_refresh=$(prefix+"operq_refresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            gridqueries.where("LEGENDID='"+currsysid+"'");
            gridqueries.query();
        }
    });
    offsety+=25;
    
    gridqueries=$(prefix+"gridqueries").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_LEGENDQUERY",
        orderby:"SORTER,DESCRIPTION",
        columns:[
            {id:"SORTER", caption:"", width:30, type:"0"},
            {id:"DESCRIPTION", caption:"Descrizione",width:300}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            currqueryid="";
            RYWINZ.MaskClear(formid, "Q");
            RYWINZ.MaskEnabled(formid, "Q", 0);
            operq_update.enabled(0);
            operq_unsaved.visible(0);
            operq_remove.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currqueryid=d;
            operq_remove.enabled(1);
            RYQUE.query({
                sql:"SELECT * FROM QW_LEGENDQUERY WHERE SYSID='"+currqueryid+"'",
                ready:function(v){
                    // ABILITAZIONE TAB CONTENITORI
                    RYWINZ.MaskEnabled(formid, "Q", 1);
                    operq_update.enabled(1);
                    // CARICAMENTO TAB CONTENITORI
                    RYWINZ.ToMask(formid, "Q", v[0]);
                    operq_unsaved.visible(0);
                    loadedsysidD=currsysid;
                    if(flagfocus){
                        flagfocus=false;
                        castFocus(prefix+"Q_DESCRIPTION");
                    }
                }
            });
        },
        enter:function(){
            $("#window_"+formid+" .window_content").animate({ scrollTop: $(document).height() }, "slow");
            castFocus(prefix+"operq_top");
        }
    });
    offsety=410;

    var operq_add=$(prefix+"operq_add").rylabel({
        left:20,
        top:offsety,
        caption:"Aggiungi query",
        button:true,
        click:function(o){
            winzProgress(formid);
            var stats=[];
            var istr=0;
            if(RYWINZ.modified(formid)){
                // ISTRUZIONE DI SALVATAGGIO DEL CONTENITORE MODIFICATO
                var datasave=RYWINZ.ToObject(formid, "Q", currqueryid);
                stats[istr++]={
                    "function":"objects_update",
                    "data":datasave
                };
            }
            // ISTRUZIONE DI INSERIMENTO NUOVO CONTENITORE
            var data = new Object();
            data["DESCRIPTION"]="(nuova query)";
            data["TYPOLOGYID"]=typequery;
            data["LEGENDID"]=currsysid;
            data["ARROWTYPEID"]=typemov;
            data["SIGNUM"]="1";
            stats[istr++]={
                "function":"objects_insert",
                "data":data
            };
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "program":stats
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            // FORZO LA RILETTURA DELLE QUERY
                            var newid=v.SYSID;
                            RYWINZ.modified(formid, 0);
                            flagfocus=true;
                            gridqueries.splice(0, 0, newid);
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

    var operq_unsaved=$(prefix+"operq_unsaved").rylabel({left:300, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    operq_unsaved.visible(0);
    
    var operq_remove=$(prefix+"operq_remove").rylabel({
        left:610,
        top:offsety,
        caption:"Rimuovi query",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare la query selezionata?",
                confirm:function(){
                    winzProgress(formid);
                    RYWINZ.modified(formid, 0);
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"objects_delete",
                            "data":{
                                "SYSID":currqueryid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    // FORZO LA RILETTURA DELLE QUERY
                                    gridqueries.refresh();
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
    });
    offsety+=40;

    var operq_update=$(prefix+"operq_update").rylabel({
        left:20,
        top:offsety,
        caption:"Salva query",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "Q", currqueryid);
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            operq_unsaved.visible(0);
                            if(done!=missing){done()}
                            gridqueries.dataload();
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
    
    offsety+=40;
    $(prefix+"LBQ_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    $(prefix+"Q_DESCRIPTION").rytext({left:100, top:offsety, width:377, datum:"Q", tag:"DESCRIPTION",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });
    $(prefix+"LBQ_SORTER").rylabel({left:490, top:offsety, caption:"Ordine"});
    $(prefix+"Q_SORTER").rynumber({left:538, top:offsety, width:60, numdec:0, datum:"Q", tag:"SORTER",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });
    $(prefix+"LBQ_SIGNUM").rylabel({left:620, top:offsety, caption:"Segno"});
    $(prefix+"Q_SIGNUM").rylist({left:668, top:offsety, width:50, datum:"Q", tag:"SIGNUM",
        changed:function(){
            operq_unsaved.visible(1);
        }
    })
    .additem({caption:"+", key:1})
    .additem({caption:"-", key:-1})
    .additem({caption:"&empty;", key:0});
    
    offsety+=30;
    $(prefix+"LBQ_BAGNAME").rylabel({left:20, top:offsety, caption:"Id dati"});
    $(prefix+"Q_BAGNAME").rytext({left:100, top:offsety, width:140, datum:"Q", tag:"BAGNAME",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });
    $(prefix+"LBQ_ARROWTYPEID").rylabel({left:260, top:offsety, caption:"Tipologia"});
    $(prefix+"Q_ARROWTYPEID").ryhelper({left:325, top:offsety, width:150, datum:"Q", tag:"ARROWTYPEID", formid:formid, table:"QVARROWTYPES", title:"Scelta tipologia",
        open:function(o){
            o.where("GENRETYPEID=[:SYSID(0MONEY000000)]");
        },
        changed:function(){
            operq_unsaved.visible(1);
        }
    });
    $(prefix+"LBQ_LEGENDVIEW").rylabel({left:490, top:offsety, caption:"Vista"});
    $(prefix+"Q_LEGENDVIEW").rytext({left:538, top:offsety, width:180, datum:"Q", tag:"LEGENDVIEW",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });

    offsety+=30;
    $(prefix+"LBQ_LEGENDSELECT").rylabel({left:20, top:offsety, caption:"Select"});
    $(prefix+"Q_LEGENDSELECT").rytext({left:100, top:offsety, width:618, datum:"Q", tag:"LEGENDSELECT",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    $(prefix+"LBQ_LEGENDPARAMS").rylabel({left:20, top:offsety, caption:"Parametri (JSON)"});offsety+=24;
    $(prefix+"Q_LEGENDPARAMS").ryedit({left:20, top:offsety, width:692, height:200, flat:true, datum:"Q", tag:"LEGENDPARAMS",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });
    
    offsety+=220;
    $(prefix+"LBQ_LEGENDROWS").rylabel({left:20, top:offsety, caption:"Where (SQL)"});offsety+=24;
    $(prefix+"Q_LEGENDROWS").ryedit({left:20, top:offsety, width:692, height:200, flat:true, datum:"Q", tag:"LEGENDROWS",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });

    offsety+=220;
    $(prefix+"LBQ_LEGENDCOLUMNS").rylabel({left:20, top:offsety, caption:"Colonne (JSON)"});offsety+=24;
    $(prefix+"Q_LEGENDCOLUMNS").ryedit({left:20, top:offsety, width:692, height:200, flat:true, datum:"Q", tag:"LEGENDCOLUMNS",
        changed:function(){
            operq_unsaved.visible(1);
        }
    });
    
    offsety+=220;
    var operq_top=$(prefix+"operq_top").rylabel({
        left:20,
        top:offsety,
        caption:"Torna all'elenco",
        button:true,
        click:function(o){
            $("#window_"+formid+" .window_content").animate({ scrollTop:0}, "slow");
            castFocus(prefix+"gridqueries");
        }
    });
    offsety+=50;
    $(prefix+"queriesbottom").css({"position":"absolute", "left":0, "top":offsety});

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Dettagli"}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedsysidC="";
                    }
                });
            }
            else if(p==3){
                // PROVENGO DAI DETTAGLI
                flagsuspend=qv_changemanagement(formid, objtabs, operq_update, {
                    abandon:function(){
                        loadedsysidD="";
                    }
                });
            }
            if(i==1){
                loadedsysidC="";
                loadedsysidD="";
            }
            else if(i==2){
                if(currsysid==loadedsysidC){
                    flagsuspend=true;
                }
            }
            else if(i==3){
                if(currsysid==loadedsysidD){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 1:
                    objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO DEL CONTESTO
                    if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currsysid)}
                    // RESET MASCHERA
                    RYWINZ.MaskClear(formid, "C");
                    objscript.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_LEGEND WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysidC=currsysid;
                            objscript.parentid(currsysid,
                                function(){
                                    castFocus(prefix+"DESCRIPTION");
                                }
                            );
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DETTAGLI
                    lb_queries_context.caption("Contesto: "+context);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_queries_context.caption("Contesto: "+context);
                            loadedsysidC=currsysid;
                            operq_refresh.engage();
                        }
                    });
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            setTimeout( 
                function(){ 
                    oper_refresh.engage(
                        function(){
                            winzClearMess(formid);
                            txf_search.focus();
                        }
                    ) 
                }, 100
            );
        }
    );
}

