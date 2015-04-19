/****************************************************************************
* Name:            qvprocessi.js                                            *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvprocessi(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    // VARIABILI PROCESSO
    var processitype=RYQUE.formatid("0PROCESSI000");
    var attoritype=RYQUE.formatid("0ATTORI00000");
    var currprocessoid="";
    var currexported="";

    // VARIABILI MOTIVI
    var motiviattivitatype=RYQUE.formatid("0MOTIVIATTIV");
    var currmotiveid="";

    // VARIABILI STATO
    var statitype=RYQUE.formatid("0PROCSTATI00");
    var currstatoid="";

    // VARIABILI TRANSIZIONI
    var transizionitype=RYQUE.formatid("0TRANSIZIONI");
    var generetrans=RYQUE.formatid("0TIMEHOURS00");
    var motivotrans=RYQUE.formatid("0MOTIVOTRANS");
    var currtransid="";

    var context="";
    var contextstato="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var flagfocus=false;
    var loadedprocessoCid="";
    var loadedprocessoAid="";
    var loadedprocessoMid="";
    var loadedprocessoSid="";
    var loadedstatoVid="";
    var loadedstatoTid="";
    var tabselezione=1;
    var tabcontesto=2;
    var tabattori=3;
    var tabmotivi=4;
    var tabstati=5;
    var tabdocumenti=6;
    var tabvincoli=7;
    var tabtransizioni=8;
    var tabgrafo=9;
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:380,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWQUIVERS",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:200}
        ],
        changerow:function(o,i){
            objtabs.enabled(tabcontesto, false);
            objtabs.enabled(tabmotivi, false);
            objtabs.enabled(tabattori, false);
            objtabs.enabled(tabstati, false);
            objtabs.enabled(tabdocumenti, false);
            objtabs.enabled(tabvincoli, false);
            objtabs.enabled(tabtransizioni, false);
            objtabs.enabled(tabgrafo, false);
            currprocessoid="";
            currexported="";
            loadedprocessoCid="";
            loadedprocessoAid="";
            loadedprocessoMid="";
            loadedstatoVid="";
            loadedstatoTid="";
            oper_clone.enabled(0);
            oper_export.enabled(0);
            oper_download.visible(0);
            oper_print.enabled(o.isselected());
            oper_delete.enabled(0);
            context="";
            interprocesso.parentid("");
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            oper_print.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_clone.enabled(1);
            oper_export.enabled(1);
            oper_print.enabled(1);
            oper_delete.enabled(1);
            if(currprocessoid==""){
                currprocessoid=d;
                objtabs.enabled(tabcontesto, true);
                objtabs.enabled(tabmotivi, true);
                objtabs.enabled(tabattori, true);
                objtabs.enabled(tabstati, true);
                objtabs.enabled(tabgrafo, true);
            }
            else{
                currprocessoid=d;
            }
            if(flagopen){
                flagopen=false;
                objtabs.currtab(tabcontesto);
            }
        },
        enter:function(){
            objtabs.currtab(tabcontesto);
        }
    });
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            oper_refresh.engage();
        }
    });offsety+=30;

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            
            q="TYPOLOGYID='"+processitype+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

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
        top:270,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuovo processo)";
            data["TYPOLOGYID"]=processitype;
            data["SETINTERPROCESSO"]="[:SYSID]";
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"quivers_insert",
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
    
    var oper_clone=$(prefix+"oper_clone").rylabel({
        left:430,
        top:320,
        caption:"Clona&nbsp;",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["PROCESSOID"]=currprocessoid;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"processi_clone",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.params["PROCESSOID"];
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
    
    var oper_export=$(prefix+"oper_export").rylabel({
        left:495,
        top:320,
        caption:"Esporta",
        button:true,
        click:function(o){
            winzProgress(formid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"processi_export",
                    "data":{
                        "PROCESSOID":currprocessoid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            currexported=v.params["EXPORTED"];
                            oper_download.visible(1);
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
    
    var oper_download=$(prefix+"oper_download").rylabel({
        left:660,
        top:320,
        caption:"Scarica",
        button:true,
        click:function(o){
            var h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+_customizeURL+currexported;
            $("#winz-iframe").prop("src", h);
        }
    });
    
    var oper_print=$(prefix+"oper_print").rylabel({
        left:430,
        top:370,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_processi.php")
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:428,
        caption:"Elimina processo",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare il processo selezionato?",
                ok:"Elimina",
                confirm:function(){
                    winzProgress(formid);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"processi_delete",
                            "data":{
                                "PROCESSOID":currprocessoid
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
    offsety=60;

    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Identificatore"});
    $(prefix+"NAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NAME"});
    offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    offsety+=400;
    
    $(prefix+"LB_PROTSERIE").rylabel({left:20, top:offsety, caption:"Serie"});
    $(prefix+"PROTSERIE").rytext({left:70, top:offsety, width:140, datum:"C", tag:"PROTSERIE"});
    
    $(prefix+"LB_GANTT").rylabel({left:20, top:offsety+30, caption:"Gantt"});
    $(prefix+"GANTT").rycheck({left:70, top:offsety+30, datum:"C", tag:"GANTT"});
    
    $(prefix+"LB_INVIOEMAIL").rylabel({left:20, top:offsety+60, caption:"Email", title:"Il processo genera notifiche"});
    $(prefix+"INVIOEMAIL").rycheck({left:70, top:offsety+60, datum:"C", tag:"INVIOEMAIL"});
    
    $(prefix+"LB_DATIAGGIUNTIVI").rylabel({left:20, top:offsety+90, caption:"Dati aggiuntivi"});
    $(prefix+"DATIAGGIUNTIVI").rytext({left:20, top:offsety+115, width:400, maxlen:1000, datum:"C", tag:"DATIAGGIUNTIVI"});
    
    var interprocesso=$(prefix+"INTERPROCESSO").ryselections({"left":470, "top":offsety, "height":140, 
        "title":"Interprocesso",
        "formid":formid, 
        "subid":"C",
        "table":"QW_PROCESSI", 
        "where":"SYSID<>'"+currprocessoid+"'",
        "parenttable":"QW_PROCESSI", 
        "parentfield":"SETINTERPROCESSO",
        "selectedtable":"QVQUIVERS"
    });
    
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            lb_motivi_context.caption("Contesto: "+context);
            lb_attori_context.caption("Contesto: "+context);
            lb_stati_context.caption("Contesto: "+context);
            lb_grafo_context.caption("Contesto: "+context);
            var data=RYWINZ.ToObject(formid, "C", currprocessoid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"quivers_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            RYWINZ.modified(formid, 0);
                            if(done!=missing){done()}
                            objgridsel.dataload();
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

    // DEFINIZIONE TAB ATTORI
    offsety=90;
    var lb_attori_context=$(prefix+"attori_context").rylabel({left:20, top:50, caption:""});
    
    $(prefix+"lbattori_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txattori_search=$(prefix+"txattori_search").rytext({left:80, top:offsety, width:190, 
        assigned:function(){
            objattori_refresh.engage();
        }
    });

    objattori_refresh=$(prefix+"lbattori_refresh").rylabel({
        left:300,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        flat:true,
        click:function(o){
            var q=" AND [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%'";
            var t=qv_forlikeclause(txattori_search.value());

            gridattori.clear();
            gridattorisel.clear();
            gridattori.where("(UTENTEID<>'' OR RUOLOID<>'') AND NOT SYSID IN (SELECT SYSID FROM QW_PROCCOINVOLTI WHERE PROCESSOID='"+currprocessoid+"')"+q);
            gridattorisel.where("PROCESSOID='"+currprocessoid+"'"+q);
            gridattori.query({
                args:{
                    "DESCRIPTION":t
                },
                ready:function(){
                    gridattorisel.query({
                        args:{
                            "DESCRIPTION":t
                        }
                    });
                }
            });
        }
    });
    offsety+=40;

    gridattori=$(prefix+"gridattori").ryque({
        left:20,
        top:offsety,
        width:250,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_ATTORI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Attori non coinvolti",width:200}
        ],
        solveid:function(o, s){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_add",
                    "data":{
                        "PARENTTABLE":"QVQUIVERS",
                        "PARENTID":currprocessoid,
                        "SELECTEDTABLE":"QVOBJECTS",
                        "UPWARD":1,
                        "SELECTION":s
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            objattori_refresh.engage();
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
    var gridattorisel=$(prefix+"gridattorisel").ryque({
        left:385,
        top:offsety,
        width:250,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_PROCCOINVOLTI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Attori coinvolti",width:200}
        ],
        solveid:function(o, s){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_remove",
                    "data":{
                        "PARENTID":currprocessoid,
                        "SELECTION":s
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            objattori_refresh.engage();
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
    $(prefix+"lbattori_action_add").rylabel({
        left:305,
        top:offsety+35,
        caption:"<img src='"+_cambusaURL+"ryego/images/arrow_right.png' style='position:absolute;top:0px;border:none;cursor:pointer;'>",
        button:true,
        flat:true,
        click:function(o){
            gridattori.selengage();
        }
    });
    $(prefix+"lbattori_action_remove").rylabel({
        left:305,
        top:offsety+65,
        caption:"<img src='"+_cambusaURL+"ryego/images/arrow_left.png' style='position:absolute;top:0px;border:none;cursor:pointer;'>",
        button:true,
        flat:true,
        click:function(o){
            gridattorisel.selengage();
        }
    });

    // DEFINIZIONE TAB MOTIVI
    offsety=80;
    var lb_motivi_context=$(prefix+"motivi_context").rylabel({left:20, top:50, caption:""});
    
    var operm_refresh=$(prefix+"operm_refresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            gridmotivi.where("PROCESSOID='"+currprocessoid+"'");
            gridmotivi.query();
        }
    });
    offsety+=35;
    
    gridmotivi=$(prefix+"gridmotivi").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_MOTIVIATTIVITA",
        orderby:"ORDINATORE,DESCRIPTION",
        columns:[
            {id:"ORDINATORE", caption:"", width:30, type:"0"},
            {id:"DESCRIPTION", caption:"Descrizione",width:300}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            RYWINZ.MaskClear(formid, "M");
            txm_creazione.value(1);
            RYWINZ.MaskEnabled(formid, "M", 0);
            txm_creazione.enabled(0);
            operm_update.enabled(0);
            operm_unsaved.visible(0);
            operm_remove.enabled(0);
            loadedprocessoMid="";
            motivoconoscenza.parentid("");
            if(i>0){
                o.solveid(i);
            }
            else{
                currmotiveid="";
            }
        },
        solveid:function(o,d){
            currmotiveid=d;
            operm_remove.enabled(1);
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento motivo: "+currmotiveid)}
            RYQUE.query({
                sql:"SELECT * FROM QW_MOTIVIATTIVITA WHERE SYSID='"+currmotiveid+"'",
                ready:function(v){
                    // ABILITAZIONE TAB MOTIVI
                    RYWINZ.MaskEnabled(formid, "M", 1);
                    txm_creazione.enabled(1);
                    operm_update.enabled(1);
                    // CARICAMENTO TAB MOTIVI
                    RYWINZ.ToMask(formid, "M", v[0]);
                    if(v[0]["CONSISTENCY"]=="2"){
                        txm_creazione.value(1);
                    }
                    else{
                        if(v[0]["STATUS"]=="0")
                            txm_creazione.value(2);
                        else
                            txm_creazione.value(3);
                    }
                    motiviparzializza();
                    operm_unsaved.visible(0);
                    loadedprocessoMid=currprocessoid;
                    motivoconoscenza.parentid(v[0]["SETCONOSCENZA"],
                        function(){
                            if(flagfocus){
                                flagfocus=false;
                                castFocus(prefix+"M_DESCRIPTION");
                            }
                        }
                    );
                }
            });
        },
        enter:function(){
            $("#window_"+formid+" .window_content").animate({ scrollTop: $(document).height() }, "slow");
            castFocus(prefix+"operm_top");
        }
    });
    offsety=410;

    var operm_add=$(prefix+"operm_add").rylabel({
        left:20,
        top:offsety,
        caption:"Aggiungi motivo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var stats=[];
            var istr=0;
            if(RYWINZ.modified(formid)){
                // ISTRUZIONE DI SALVATAGGIO DEL MOTIVO MODIFICATO
                var datasave=RYWINZ.ToObject(formid, "M", currmotiveid);
                switch(parseInt(txm_creazione.value())){
                case 1:
                    datasave["CONSISTENCY"]=2;
                    datasave["STATUS"]=0;
                    break;
                case 2:
                    datasave["CONSISTENCY"]=0;
                    datasave["STATUS"]=0;
                    break;
                default:
                    datasave["CONSISTENCY"]=0;
                    datasave["STATUS"]=1;
                    break;
                }
                stats[istr++]={
                    "function":"motives_update",
                    "data":datasave
                };
            }
            // ISTRUZIONE DI INSERIMENTO NUOVO MOTIVO
            var data = new Object();
            data["DESCRIPTION"]="(nuovo motivo)";
            data["TYPOLOGYID"]=motiviattivitatype;
            data["PROCESSOID"]=currprocessoid;
            data["SETCONOSCENZA"]="[:SYSID]";
            data["ORDINATORE"]=gridmotivi.count()+1;
            stats[istr++]={
                "function":"motives_insert",
                "data":data
            };
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":stats
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            // FORZO LA RILETTURA DEI VINCOLI
                            loadedstatoVid="";
                            var newid=v.SYSID;
                            RYWINZ.modified(formid, 0);
                            flagfocus=true;
                            gridmotivi.splice(0, 0, newid);
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

    var operm_unsaved=$(prefix+"operm_unsaved").rylabel({left:280, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    operm_unsaved.visible(0);
    
    var operm_remove=$(prefix+"operm_remove").rylabel({
        left:620,
        top:offsety,
        caption:"Rimuovi motivo",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare il motivo selezionato?",
                confirm:function(){
                    winzProgress(formid);
                    RYWINZ.modified(formid, 0);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"motives_delete",
                            "data":{
                                "SYSID":currmotiveid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    // FORZO LA RILETTURA DEI VINCOLI
                                    loadedstatoVid="";
                                    gridmotivi.refresh();
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

    var operm_update=$(prefix+"operm_update").rylabel({
        left:20,
        top:offsety,
        caption:"Salva motivo",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "M", currmotiveid);
            switch(parseInt(txm_creazione.value())){
            case 1:
                data["CONSISTENCY"]=2;
                data["STATUS"]=0;
                break;
            case 2:
                data["CONSISTENCY"]=0;
                data["STATUS"]=0;
                break;
            default:
                data["CONSISTENCY"]=0;
                data["STATUS"]=1;
                break;
            }
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"motives_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            // FORZO LA RILETTURA DEI VINCOLI
                            loadedstatoVid="";
                            RYWINZ.modified(formid, 0);
                            operm_unsaved.visible(0);
                            if(done!=missing){done()}
                            gridmotivi.dataload();
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

    $(prefix+"LBM_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txm_description=$(prefix+"M_DESCRIPTION").rytext({left:120, top:offsety, width:400, datum:"M", tag:"DESCRIPTION",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });offsety+=35;
    
    $(prefix+"LBM_SCOPE").rylabel({left:20, top:offsety, caption:"Visibilità"});
    $(prefix+"M_SCOPE").rylist({left:120, top:offsety, width:200, datum:"M", tag:"SCOPE",
        assigned:function(){
            operm_unsaved.visible(1);
        }
    })
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});

    $(prefix+"LBM_UPDATING").rylabel({left:20, top:offsety+30, caption:"Modificabilità"});
    $(prefix+"M_UPDATING").rylist({left:120, top:offsety+30, width:200, datum:"M", tag:"UPDATING",
        assigned:function(){
            operm_unsaved.visible(1);
        }
    })
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});

    $(prefix+"LBM_COUNTERPARTID").rylabel({left:20, top:offsety+60, caption:"Destinatario"});
    $(prefix+"M_COUNTERPARTID").ryhelper({
        left:120, top:offsety+60, width:300, datum:"M", tag:"COUNTERPARTID", formid:formid, table:"QVOBJECTS", title:"Scelta destinatario predefinito",
        open:function(o){
            o.where("TYPOLOGYID='"+attoritype+"' AND (SYSID=[:SYSID(0ATTJOLLYRIC)] OR SYSID IN (SELECT SYSID FROM QW_PROCCOINVOLTI WHERE PROCESSOID='"+currprocessoid+"'))");
        },
        assigned:function(){
            operm_unsaved.visible(1);
        }
    });
    $(prefix+"LBM_CREAZIONE").rylabel({left:538, top:offsety, caption:"Creazione"});
    var txm_creazione=$(prefix+"M_CREAZIONE").rylist({left:608, top:offsety, width:110,
        assigned:function(){
            operm_unsaved.visible(1);
        }
    })
    .additem({caption:"Bozza", key:0})
    .additem({caption:"Provvisorio", key:1})
    .additem({caption:"Completo", key:2});
    
    $(prefix+"LBM_GANTT").rylabel({left:650, top:offsety+30, caption:"Gantt", title:"L'attività"+" viene riportata nei Gantt"});
    $(prefix+"M_GANTT").rycheck({left:700, top:offsety+30, datum:"M", tag:"GANTT",
        assigned:function(){
            operm_unsaved.visible(1);
        }
    });
    $(prefix+"LBM_INVIOEMAIL").rylabel({left:650, top:offsety+60, caption:"Email", title:"L'attività"+" genera notifiche"});
    $(prefix+"M_INVIOEMAIL").rycheck({left:700, top:offsety+60, datum:"M", tag:"INVIOEMAIL",
        assigned:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=120;
    var savey=offsety;
    
    offsety-=20;
    
    // LABEL INIZIO/FINE
    $(prefix+"LBM_INIZIO").rylabel({left:120, top:offsety, caption:"Inizio"});
    $(prefix+"LBM_FINE").rylabel({left:290, top:offsety, caption:"Fine"});
    offsety+=20;

    // RIFERMENTI
    $(prefix+"LBM_RIFERIMENTO").rylabel({left:20, top:offsety, caption:"Riferimento"});
    var txm_rifinizio=$(prefix+"M_RIFERIMENTOINIZIO").rylist({left:120, top:offsety, width:160, datum:"M", tag:"RIFERIMENTOINIZIO",
        assigned:function(){
            motiviparzializza();
            operm_unsaved.visible(1);
        }
    })
    .additem({caption:"Giorno creazione", key:0})
    .additem({caption:"Inizio settimana", key:1})
    .additem({caption:"Inizio mese", key:2})
    .additem({caption:"Inizio anno", key:3});
   
    var txm_riffine=$(prefix+"M_RIFERIMENTOFINE").rylist({left:290, top:offsety, width:160, datum:"M", tag:"RIFERIMENTOFINE",
        assigned:function(){
            motiviparzializza();
            operm_unsaved.visible(1);
        }
    })
    .additem({caption:"Data inizio", key:0})
    .additem({caption:"Inizio settimana", key:1})
    .additem({caption:"Inizio mese", key:2})
    .additem({caption:"Inizio anno", key:3});
    
    offsety+=30;
    
    // MESI
    $(prefix+"LBM_MESE").rylabel({left:20, top:offsety, caption:"Mese"});
    var txm_meseinizio=$(prefix+"M_MESEINIZIO").rynumber({left:120, top:offsety, width:70, numdec:0, datum:"M", tag:"MESEINIZIO",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    var txm_mesefine=$(prefix+"M_MESEFINE").rynumber({left:290, top:offsety, width:70, numdec:0, datum:"M", tag:"MESEFINE",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    
    offsety+=30;

    // GIORNI
    $(prefix+"LBM_GIORNO").rylabel({left:20, top:offsety, caption:"Giorno"});
    var txm_giornoinizio=$(prefix+"M_GIORNOINIZIO").rynumber({left:120, top:offsety, width:70, numdec:0, datum:"M", tag:"GIORNOINIZIO",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    var txm_giornofine=$(prefix+"M_GIORNOFINE").rynumber({left:290, top:offsety, width:70, numdec:0, datum:"M", tag:"GIORNOFINE",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    
    offsety+=30;

    // ORE
    $(prefix+"LBM_ORA").rylabel({left:20, top:offsety, caption:"Ora"});
    var txm_orainizio=$(prefix+"M_ORAINIZIO").rynumber({left:120, top:offsety, width:70, numdec:0, maxvalue:23, datum:"M", tag:"ORAINIZIO",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    var txm_orafine=$(prefix+"M_ORAFINE").rynumber({left:290, top:offsety, width:70, numdec:0, maxvalue:23, datum:"M", tag:"ORAFINE",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    
    offsety+=50;
    
    $(prefix+"LBM_CALCOLO").rylabel({left:20, top:offsety, caption:"Calcolo"});
    var txm_calcolo=$(prefix+"M_CALCOLO").rylist({left:120, top:offsety, width:100, datum:"M", tag:"CALCOLO",
        assigned:function(){
            motiviparzializza();
            operm_unsaved.visible(1);
        }
    })
    .additem({caption:"Nessuno", key:0})
    .additem({caption:"Solare", key:1})
    .additem({caption:"Lavorativo", key:2});
    
    $(prefix+"LBM_INTESTAZIONE").rylabel({left:290, top:offsety, caption:"Intestazione"});
    $(prefix+"M_INTESTAZIONE").rycheck({left:380, top:offsety, datum:"M", tag:"INTESTAZIONE",
        assigned:function(){
            operm_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    
    $(prefix+"LBM_PREAVVISO").rylabel({left:20, top:offsety, caption:"Preavviso"});
    $(prefix+"M_PREAVVISO").rynumber({left:120, top:offsety, width:70, numdec:0, datum:"M", tag:"PREAVVISO",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    
    $(prefix+"LBM_ORDINATORE").rylabel({left:290, top:offsety, caption:"Ordinatore"});
    $(prefix+"M_ORDINATORE").rynumber({left:380, top:offsety, width:70, numdec:0, datum:"M", tag:"ORDINATORE",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });

    offsety+=30;
    $(prefix+"LBM_ISTANZE").rylabel({left:290, top:offsety, caption:"Istanze"});
    $(prefix+"M_ISTANZE").rynumber({left:380, top:offsety, width:70, numdec:0, datum:"M", tag:"ISTANZE",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });

    offsety=savey;

    $(prefix+"LBM_NAME").rylabel({left:490, top:offsety-30, caption:"Identificatore"});
    $(prefix+"M_NAME").rytext({left:585, top:offsety-30, width:135, datum:"M", tag:"NAME",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    
    $(prefix+"LBM_PROTSERIE").rylabel({left:490, top:offsety, caption:"Serie"});
    $(prefix+"M_PROTSERIE").rytext({left:585, top:offsety, width:135, datum:"M", tag:"PROTSERIE",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    
    offsety+=65;
    
    var motivoconoscenza=$(prefix+"M_CONOSCENZA").ryselections({"left":470, "top":offsety, "height":140, 
        "title":"Per conoscenza",
        "formid":formid, 
        "subid":"M",
        "table":"QW_ATTORI", 
        "where":"EMAIL<>''", 
        "upward":1,
        "parenttable":"QW_MOTIVIATTIVITA", 
        "parentfield":"SETCONOSCENZA",
        "selectedtable":"QVOBJECTS"
    });
    offsety+=180;
    
    $(prefix+"M_REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"M", tag:"REGISTRY", 
        changed:function(){
            operm_unsaved.visible(1);
        }
    });offsety+=400;
    
    var operm_top=$(prefix+"operm_top").rylabel({
        left:20,
        top:offsety,
        caption:"Torna all'elenco",
        button:true,
        click:function(o){
            $("#window_"+formid+" .window_content").animate({ scrollTop:0}, "slow");
            castFocus(prefix+"gridmotivi");
        }
    });
    offsety+=50;
    $(prefix+"motivibottom").css({"position":"absolute", "left":0, "top":offsety});
    
    // DEFINIZIONE TAB STATI
    offsety=80;
    
    // RICERCA STATI
    var lbf_statisearch=$(prefix+"lbf_statisearch").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_statisearch=$(prefix+"txf_statisearch").rytext({left:80, top:offsety, width:340, 
        assigned:function(){
            oper_statirefresh.engage();
        }
    });
    // REFRESH RICERCA
    var oper_statirefresh=$(prefix+"oper_statirefresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_statisearch.value());
            
            q="PROCESSOID='"+currprocessoid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(ATTORE)] LIKE '%[=ATTORE]%' )";

            objgridstati.where(q);
            objgridstati.query({
                args:{
                    "DESCRIPTION":t,
                    "ATTORE":t
                },
                ready:function(){
                    if(done!=missing){done()}
                }
            });
        }
    });
    offsety+=35;

    // GRID DI SELEZIONE STATI
    var lb_stati_context=$(prefix+"stati_context").rylabel({left:20, top:50, caption:""});
    var objgridstati=$(prefix+"gridstati").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_PROCSTATIJOIN",
        orderby:"ORDINATORE,SYSID",
        columns:[
            {id:"ORDINATORE", caption:"", width:30, type:"0"},
            {id:"DESCRIPTION", caption:"Descrizione", width:300},
            {id:"ATTORE", caption:"Attore", width:140}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            RYWINZ.MaskClear(formid, "S");
            RYWINZ.MaskEnabled(formid, "S", 0);
            oper_statiupdate.enabled(0);
            statounsaved.visible(0);
            loadedstatoVid="";
            loadedstatoTid="";
            if(i>0){
                o.solveid(i);
            }
            else{
                if(currstatoid!=""){
                    objtabs.enabled(tabdocumenti, false);
                    objtabs.enabled(tabvincoli, false);
                    objtabs.enabled(tabtransizioni, false);
                }
                currstatoid="";
                oper_statiremove.enabled(o.isselected());
            }
            contextstato="";
        },
        solveid:function(o,d){
            oper_statiremove.enabled(1);
            if(currstatoid==""){
                objtabs.enabled(tabdocumenti, true);
                objtabs.enabled(tabvincoli, true);
                objtabs.enabled(tabtransizioni, true);
            }
            currstatoid=d;
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento stato: "+currstatoid)}
            RYQUE.query({
                sql:"SELECT * FROM QW_PROCSTATI WHERE SYSID='"+currstatoid+"'",
                ready:function(v){
                    RYWINZ.MaskEnabled(formid, "S", 1);
                    oper_statiupdate.enabled(1);
                    RYWINZ.ToMask(formid, "S", v[0]);
                    statounsaved.visible(0);
                    contextstato=tx_statodescription.value();
                    lb_vincoli_context.caption("Contesto: "+context+" - "+contextstato);
                    lb_trans_context.caption("Contesto: "+context+" - "+contextstato);
                    if(flagfocus){
                        flagfocus=false;
                        castFocus(prefix+"STATO_DESCRIPTION");
                    }
                }
            });
        },
        enter:function(){
            $("#window_"+formid+" .window_content").animate({ scrollTop: $(document).height() }, "slow");
            castFocus(prefix+"oper_statitop");
        }
    });
    offsety=410;
    
    var oper_statiadd=$(prefix+"oper_statiadd").rylabel({
        left:20,
        top:offsety,
        caption:"Aggiungi stato",
        button:true,
        click:function(o){
            winzProgress(formid);
            var stats=[];
            var istr=0;
            if(RYWINZ.modified(formid)){
                // ISTRUZIONE DI SALVATAGGIO DELLO STATO MODIFICATO
                var datasave=RYWINZ.ToObject(formid, "S", currstatoid);
                stats[istr++]={
                    "function":"objects_update",
                    "data":datasave
                };
            }
            // ISTRUZIONE DI INSERIMENTO NUOVO STATO
            var data = new Object();
            data["DESCRIPTION"]="(nuovo stato)";
            data["TYPOLOGYID"]=statitype;
            data["PROCESSOID"]=currprocessoid;
            data["ORDINATORE"]=objgridstati.count()+1;
            stats[istr++]={
                "function":"objects_insert",
                "data":data
            };
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":stats
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.SYSID;
                            RYWINZ.modified(formid, 0);
                            flagfocus=true;
                            objgridstati.splice(0, 0, newid);
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

    var statounsaved=$(prefix+"oper_statiunsaved").rylabel({left:280, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    statounsaved.visible(0);
    
    var oper_statiremove=$(prefix+"oper_statiremove").rylabel({
        left:620,
        top:offsety,
        caption:"Rimuovi stato",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare lo stato selezionato?",
                confirm:function(){
                    winzProgress(formid);
                    RYWINZ.modified(formid, 0);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"objects_delete",
                            "data":{
                                "SYSID":currstatoid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    objgridstati.refresh();
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

    var oper_statiupdate=$(prefix+"oper_statiupdate").rylabel({
        left:20,
        top:offsety,
        caption:"Salva stato",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "S", currstatoid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            statounsaved.visible(0);
                            if(done!=missing){done()}
                            objgridstati.dataload();
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
        
    $(prefix+"LB_STATO_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var tx_statodescription=$(prefix+"STATO_DESCRIPTION").rytext({left:100, top:offsety, width:400, datum:"S", tag:"DESCRIPTION",
        assigned:function(){
            contextstato=tx_statodescription.value();
        },
        changed:function(){
            statounsaved.visible(1);
        }
    });offsety+=35;
    
    var sy=offsety;
    $(prefix+"LB_STATO_ATTOREID").rylabel({left:20, top:offsety, caption:"Attore"});
    $(prefix+"STATO_ATTOREID").ryhelper({
        left:100, top:offsety, width:250, formid:formid, table:"QW_ATTORI", title:"Attori", datum:"S", tag:"ATTOREID", 
        open:function(o){
            o.where("(UTENTEID<>'' OR RUOLOID<>'') AND SYSID IN (SELECT SYSID FROM QW_PROCCOINVOLTI WHERE PROCESSOID='"+currprocessoid+"')");
        },
        assigned:function(){
            statounsaved.visible(1);
        }
    });
    
    offsety+=30;
    
    $(prefix+"LB_STATO_CONTOID").rylabel({left:20, top:offsety, caption:"Conto"});
    $(prefix+"STATO_CONTOID").ryhelper({
        left:100, top:offsety, width:250, formid:formid, table:"QW_CONTI", title:"Conti", datum:"S", tag:"CONTOID", 
        open:function(o){
            o.where("");
        },
        assigned:function(){
            statounsaved.visible(1);
        }
    });offsety+=30;
    
    offsety=sy;
    
    $(prefix+"LB_STATO_INIZIALE").rylabel({left:420, top:offsety, caption:"Iniziale"});
    $(prefix+"STATO_INIZIALE").rycheck({left:480, top:offsety, datum:"S", tag:"INIZIALE",
        assigned:function(){
            statounsaved.visible(1);
        }
    });
    offsety+=30;
    
    $(prefix+"LB_STATO_FINALE").rylabel({left:420, top:offsety, caption:"Finale"});
    $(prefix+"STATO_FINALE").rycheck({left:480, top:offsety, datum:"S", tag:"FINALE",
        assigned:function(){
            statounsaved.visible(1);
        }
    });
    
    $(prefix+"LB_STATO_ORDINATORE").rylabel({left:565, top:offsety, caption:"Ordinatore"});
    $(prefix+"STATO_ORDINATORE").rynumber({left:645, top:offsety, width:70, numdec:0, datum:"S", tag:"ORDINATORE",
        changed:function(){
            statounsaved.visible(1);
        }
    });
    
    offsety+=40;
    $(prefix+"STATO_REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"S", tag:"REGISTRY", 
        changed:function(){
            statounsaved.visible(1);
        }
    });

    offsety+=400;
    var statitop=$(prefix+"oper_statitop").rylabel({
        left:20,
        top:offsety,
        caption:"Torna all'elenco",
        button:true,
        click:function(o){
            $("#window_"+formid+" .window_content").animate({ scrollTop:0}, "slow");
            castFocus(prefix+"gridstati");
        }
    });
    offsety+=50;
    $(prefix+"statibottom").css({"position":"absolute", "left":0, "top":offsety});

   // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_PROCESSI");

    // DEFINIZIONE TAB VINCOLI
    var offsetx=30;
    offsety=100;
    var lb_vincoli_context=$(prefix+"vincoli_context").rylabel({left:20, top:50, caption:""});

    // AGGIUNGI VINCOLI
    var operv_add=$(prefix+"operv_add").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Aggiungi",
        button:true,
        click:function(o, done){
            QVR.RequestID(formid, {
                table:"QW_MOTIVIATTIVITA", 
                where:"PROCESSOID='"+currprocessoid+"' AND SYSID NOT IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currstatoid+"')",
                title:"Scelta motivo",
                multiple:true,
                onselect:function(d){
                    var ids=d["SYSID"];
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_add",
                            "data":{
                                "PARENTTABLE":"QVOBJECTS",
                                "PARENTID":currstatoid,
                                "SELECTEDTABLE":"QVMOTIVES",
                                "FLAG1":1,
                                "SELECTION":ids
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    gridvincoli.refresh();
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
    // RIMUOVI VINCOLI
    var operv_remove=$(prefix+"operv_remove").rylabel({
        left:offsetx+100,
        top:offsety,
        caption:"Rimuovi",
        button:true,
        click:function(o, done){
            gridvincoli.selengage(
                function(o, s){
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_remove",
                            "data":{
                                "PARENTID":currstatoid,
                                "SELECTION":s
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    gridvincoli.refresh();
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
            );
        }
    });
    // SVUOTA VINCOLI
    var operv_empty=$(prefix+"operv_empty").rylabel({
        left:offsetx+200,
        top:offsety,
        caption:"Svuota",
        button:true,
        click:function(o, done){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_remove",
                    "data":{
                        "PARENTID":currstatoid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            gridvincoli.refresh();
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
    // AGGIORNA VINCOLI
    var operv_refresh=$(prefix+"operv_refresh").rylabel({
        left:offsetx+630,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            gridvincoli.query();
        }
    });
    offsety+=40;
    
    gridvincoli=$(prefix+"gridvincoli").ryque({
        left:offsetx,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_MOTIVISTATO",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Attivit&agrave;",width :210},
            {id:"ENABLED", caption:"Attiva", width:110, type:"?"},
            {id:"AUTOMATICA", caption:"Automatica", width:110, type:"?"},
            {id:"INIZIATA", caption:"Iniziata", width:110, type:"?"},
            {id:"TERMINATA", caption:"Terminata", width:110, type:"?"}
        ],
        changerow:function(o,i){
            operv_remove.enabled(o.isselected());
            operv_empty.enabled(o.count()>0);
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            operv_remove.enabled(o.isselected());
        },
        solveid:function(o,d){
            operv_remove.enabled(1);
        },
        cellclick:function(o, r, c){
            if(c>=2){
                var nomef="";
                switch(c){
                case 2:nomef="ENABLED";break;
                case 3:nomef="FLAG1";break;
                case 4:nomef="FLAG2";break;
                case 5:nomef="FLAG3";break;
                }
                // IMPOSTO I DATI DI AGGIORNAMENTO
                var data={"PARENTID":currstatoid};
                o.solveid(r,
                    function(o, id){
                        // HO DEDOTTO IL MOTIVEID
                        data["SELECTION"]=id;
                        RYQUE.query({
                            sql:"SELECT "+nomef+" FROM QVSELECTIONS WHERE PARENTID='"+currstatoid+"' AND SELECTEDID='"+id+"'",
                            ready:function(v){
                                // HO DEDOTTO I FLAG
                                var f=parseInt(v[0][nomef]);
                                if(f==0)
                                    f=1;
                                else
                                    f=0;
                                data[nomef]=f;
                                $.post(_cambusaURL+"ryquiver/quiver.php", 
                                    {
                                        "sessionid":_sessionid,
                                        "env":_sessioninfo.environ,
                                        "function":"selections_flags",
                                        "data":data
                                    }, 
                                    function(d){
                                        try{
                                            var v=$.parseJSON(d);
                                            if(v.success>0){
                                                gridvincoli.dataload();
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
        }
    });
    
    // DEFINIZIONE TAB TRANSIZIONI
    offsety=80;
    var lb_trans_context=$(prefix+"trans_context").rylabel({left:20, top:50, caption:""});
    
    var lbf_transsearch=$(prefix+"lbf_transsearch").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_transsearch=$(prefix+"txf_transsearch").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            oper_transrefresh.engage();
        }
    });

    var oper_transrefresh=$(prefix+"oper_transrefresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_transsearch.value());
            
            q="TYPOLOGYID='"+transizionitype+"' AND BOWID='"+currstatoid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

            objgridtrans.where(q);
            objgridtrans.query({
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
    offsety+=35;
    
    // GRID DI SELEZIONE TRANSIZIONI
    var objgridtrans=$(prefix+"gridtrans").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:true,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_TRANSIZIONIJOIN",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:200},
            {id:"SVINCOLANTE", caption:"Svincolante", width:100, type:"?"},
            {id:"TARGETDESCR", caption:"Nuovo stato", width:200}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            RYWINZ.MaskClear(formid, "T");
            RYWINZ.MaskEnabled(formid, "T", 0);
            oper_transupdate.enabled(0);
            transunsaved.visible(0);
            if(i>0){
                o.solveid(i);
            }
            else{
                currtransid="";
                oper_transremove.enabled(o.isselected());
            }
        },
        selchange:function(o, i){
            oper_transremove.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_transremove.enabled(1);
            currtransid=d;
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento transizione: "+currtransid)}
            RYQUE.query({
                sql:"SELECT DESCRIPTION,TARGETID,SVINCOLANTE FROM QW_TRANSIZIONI WHERE SYSID='"+currtransid+"'",
                ready:function(v){
                    RYWINZ.MaskEnabled(formid, "T", 1);
                    oper_transupdate.enabled(1);
                    RYWINZ.ToMask(formid, "T", v[0]);
                    transunsaved.visible(0);
                    if(flagfocus){
                        flagfocus=false;
                        castFocus(prefix+"TRANS_DESCRIPTION");
                    }
                }
            });
        }
    });
    offsety=410;

     var oper_transadd=$(prefix+"oper_transadd").rylabel({
        left:20,
        top:offsety,
        caption:"Aggiungi transizione",
        button:true,
        click:function(o){
            winzProgress(formid);
            var stats=[];
            var istr=0;
            if(RYWINZ.modified(formid)){
                // ISTRUZIONE DI SALVATAGGIO DELLA TRANSIZIONE MODIFICATA
                var datasave=RYWINZ.ToObject(formid, "T", currtransid);
                stats[istr++]={
                    "function":"arrows_update",
                    "data":datasave
                };
            }
            // ISTRUZIONE DI INSERIMENTO NUOVA TRANSIZIONE
            var data = new Object();
            data["DESCRIPTION"]="(nuova transizione)";
            data["TYPOLOGYID"]=transizionitype;
            data["BOWID"]=currstatoid;
            data["GENREID"]=generetrans;
            data["MOTIVEID"]=motivotrans;
            stats[istr++]={
                "function":"arrows_insert",
                "data":data,
                "pipe":{"ARROWID":"SYSID"},
                "return":{"ARROWID":"SYSID"}
            };
            // ISTRUZIONE DI AGGANCIO AL QUIVER-PROCESSO
            stats[istr++]={
                "function":"quivers_add",
                "data":{"QUIVERID":currprocessoid}
            };
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":stats
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            var newid=v["infos"]["ARROWID"];
                            flagfocus=true;
                            objgridtrans.splice(0, 0, newid);
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
    
    var transunsaved=$(prefix+"oper_transunsaved").rylabel({left:280, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    transunsaved.visible(0);
    
    var oper_transremove=$(prefix+"oper_transremove").rylabel({
        left:590,
        top:offsety,
        caption:"Rimuovi transizione",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare la transizione selezionata?",
                confirm:function(){
                    winzProgress(formid);
                    RYWINZ.modified(formid, 0);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "program":[
                                {
                                    "function":"quivers_remove",
                                    "data":{
                                        "QUIVERID":currprocessoid,
                                        "ARROWID":currtransid
                                    }
                                },
                                {
                                    "function":"arrows_delete",
                                    "data":{
                                        "SYSID":currtransid
                                    }
                                }
                            ]
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    objgridtrans.refresh();
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
    offsety+=45;

    var oper_transupdate=$(prefix+"oper_transupdate").rylabel({
        left:20,
        top:offsety,
        caption:"Salva transizione",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "T", currtransid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"arrows_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            transunsaved.visible(0);
                            if(done!=missing){done()}
                            objgridtrans.dataload();
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
    offsety+=45;
    
    $(prefix+"LB_TRANS_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var tx_trans_descr=$(prefix+"TRANS_DESCRIPTION").rytext({left:120, top:offsety, width:300, datum:"T", tag:"DESCRIPTION",
        changed:function(){
            transunsaved.visible(1);
        }
    });
    offsety+=30;

    $(prefix+"LB_TRANS_STATO").rylabel({left:20, top:offsety, caption:"Stato"});
    $(prefix+"TRANS_STATO").ryhelper({
        left:120, top:offsety, width:300, formid:formid, table:"QW_PROCSTATI", title:"Stati", datum:"T", tag:"TARGETID",
        open:function(o){
            o.where("PROCESSOID='"+currprocessoid+"' AND SYSID<>'"+currstatoid+"' AND SYSID NOT IN (SELECT TARGETID FROM QW_TRANSIZIONI WHERE BOWID='"+currstatoid+"')");
            o.orderby("ORDINATORE,SYSID");
        },
        onselect:function(o, d){
            var ds=tx_trans_descr.value();
            if(ds.substr(0,1)=="(" || ds==""){
                tx_trans_descr.value(d["DESCRIPTION"]);
            }
        },
        assigned:function(){
            transunsaved.visible(1);
        }
    });
    offsety+=30;

    $(prefix+"LB_TRANS_SVINCOLANTE").rylabel({left:20, top:offsety, caption:"Svincolante"});
    $(prefix+"TRANS_SVINCOLANTE").rycheck({left:120, top:offsety, datum:"T", tag:"SVINCOLANTE",
        assigned:function(){
            transunsaved.visible(1);
        }
    });
    
    // DEFINIZIONE TAB GRAFO
    offsety=100;
    var lb_grafo_context=$(prefix+"grafo_context").rylabel({left:20, top:50, caption:""});
    
    var operg_print=$(prefix+"operg_print").rylabel({
        left:20,
        top:offsety,
        caption:"Stampa",
        button:true,
        click:function(o){
            QVR.PrintElement(formid+"grafoviewer");
        }
    });
    offsety+=30;
    
    $(prefix+"grafoviewer").css({position:"absolute", left:20, top:offsety});
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Attori"},
            {title:"Motivi"},
            {title:"Stati"},
            {title:"Documenti"},
            {title:"Vincoli"},
            {title:"Transizioni"},
            {title:"Grafo"}
        ],
        select:function(i,p){
            if(p==tabcontesto){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedprocessoCid="";
                        loadedprocessoAid="";
                        loadedprocessoMid="";
                        loadedprocessoSid="";
                        loadedstatoVid="";
                        loadedstatoTid="";
                    }
                });
            }
            else if(p==tabmotivi){
                // PROVENGO DAI MOTIVI
                flagsuspend=qv_changemanagement(formid, objtabs, operm_update, {
                    abandon:function(){
                        loadedprocessoMid="";
                    }
                });
            }
            else if(p==tabstati){
                // PROVENGO DAGLI STATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_statiupdate, {
                    abandon:function(){
                        loadedstatoVid="";
                        loadedstatoTid="";
                    }
                });
            }
            else if(p==tabtransizioni){
                // PROVENGO DALLE TRANSIZIONI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_transupdate, {
                    abandon:function(){
                        loadedstatoTid="";
                    }
                });
            }
            if(i==tabselezione){
                loadedprocessoCid="";
                loadedprocessoAid="";
                loadedprocessoMid="";
                loadedprocessoSid="";
                loadedstatoVid="";
                loadedstatoTid="";
            }
            else if(i==tabcontesto){
                if(currprocessoid==loadedprocessoCid){
                    flagsuspend=true;
                }
            }
            else if(i==tabattori){
                if(currprocessoid==loadedprocessoAid){
                    flagsuspend=true;
                }
            }
            else if(i==tabmotivi){
                if(currprocessoid==loadedprocessoMid){
                    flagsuspend=true;
                }
            }
            else if(i==tabstati){
                if(currprocessoid==loadedprocessoSid){
                    flagsuspend=true;
                }
            }
            else if(i==tabvincoli){
                if(currstatoid==loadedstatoVid){
                    flagsuspend=true;
                }
            }
            else if(i==tabtransizioni){
                if(currstatoid==loadedstatoTid){
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
                    if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currprocessoid)}
                    RYWINZ.MaskClear(formid, "C");
                    RYQUE.query({
                        sql:"SELECT * FROM QW_PROCESSI WHERE SYSID='"+currprocessoid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedprocessoCid=currprocessoid;
                            interprocesso.parentid(v[0]["SETINTERPROCESSO"]);
                            castFocus(prefix+"DESCRIPTION");
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO ATTORI
                    lb_attori_context.caption("Contesto: "+context);
                    qv_contextmanagement(context, {sysid:currprocessoid, table:"QVQUIVERS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_attori_context.caption("Contesto: "+context);
                            loadedprocessoAid=currprocessoid;
                            objattori_refresh.engage();
                        }
                    });
                    break;
                case 4:
                    // CARICAMENTO MOTIVI
                    lb_motivi_context.caption("Contesto: "+context);
                    qv_contextmanagement(context, {sysid:currprocessoid, table:"QVQUIVERS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_motivi_context.caption("Contesto: "+context);
                            loadedprocessoMid=currprocessoid;
                            operm_refresh.engage();
                        }
                    });
                    break;
                case 5:
                    // CARICAMENTO STATI
                    lb_stati_context.caption("Contesto: "+context);
                    objgridstati.clear();
                    qv_contextmanagement(context, {sysid:currprocessoid, table:"QVQUIVERS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_stati_context.caption("Contesto: "+context);
                            loadedprocessoSid=currprocessoid;
                            oper_statirefresh.engage(
                                function(){
                                    castFocus(prefix+"txf_statisearch");
                                }
                            );
                        }
                    });
                    break;
                case 6:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currstatoid, "Contesto: "+context+" - "+contextstato, processitype);
                    break;
                case 7:
                    // CARICAMENTO VINCOLI
                    lb_vincoli_context.caption("Contesto: "+context+" - "+contextstato);
                    gridvincoli.clear();
                    gridvincoli.where("STATOID='"+currstatoid+"'");
                    gridvincoli.clause({"STATOID":currstatoid});
                    gridvincoli.query({
                        ready:function(){
                            loadedstatoVid=currstatoid;
                        }
                    });
                    break;
                case 8:
                    // CARICAMENTO TRANSIZIONI
                    lb_trans_context.caption("Contesto: "+context+" - "+contextstato);
                    objgridtrans.clear();
                    oper_transrefresh.engage(
                        function(){
                            loadedstatoTid=currstatoid;
                            castFocus(prefix+"txf_transsearch");
                        }
                    );
                    break;
                case 9:
                    // GRAFO
                    lb_grafo_context.caption("Contesto: "+context);
                    tracciagrafo();
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(tabselezione);
    objtabs.enabled(tabcontesto, false);
    objtabs.enabled(tabmotivi, false);
    objtabs.enabled(tabattori, false);
    objtabs.enabled(tabstati, false);
    objtabs.enabled(tabdocumenti, false);
    objtabs.enabled(tabvincoli, false);
    objtabs.enabled(tabtransizioni, false);
    objtabs.enabled(tabgrafo, false);
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            winzClearMess(formid);
            oper_refresh.engage(
                function(){
                    txf_search.focus();
                }
            ) 
        }
    );
    function tracciagrafo(){
        $.post(_cambusaURL+"ryquiver/quiver.php", 
            {
                "sessionid":_sessionid,
                "env":_sessioninfo.environ,
                "function":"processi_grafo",
                "data":{
                    "PROCESSOID":currprocessoid
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var h=v.params["GRAFO"];
                        h=h.replace(/&lt;/ig, "<").replace(/&gt;/ig, ">").replace(/&quot;/ig, "\"");
                        $(prefix+"grafoviewer").html(h);
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
    function motiviparzializza(){
        if(txm_calcolo.key()=="0"){
            txm_rifinizio.enabled(0);
            txm_riffine.enabled(0);
            txm_meseinizio.enabled(0);
            txm_mesefine.enabled(0);
            txm_giornoinizio.enabled(0);
            txm_giornofine.enabled(0);
            txm_orainizio.enabled(0);
            txm_orafine.enabled(0);
        }
        else{
            txm_rifinizio.enabled(1);
            txm_riffine.enabled(1);
            txm_meseinizio.enabled(1);
            txm_mesefine.enabled(1);
            txm_giornoinizio.enabled(1);
            txm_giornofine.enabled(1);
            txm_orainizio.enabled(1);
            txm_orafine.enabled(1);
        }
    }
}

