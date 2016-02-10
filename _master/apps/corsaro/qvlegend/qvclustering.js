/****************************************************************************
* Name:            qvclustering.js                                          *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvclustering(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currconfigid="";
    var currengageid="";
    var currpraticaid="";
    var currprocessoid="";
    var currgenreid="";
    var currcontoid="";
    var currarrowid="";
    var typelegend=RYQUE.formatid("0LEGEND00000");
    var typequery=RYQUE.formatid("0LEGENDQUERY");
    var context="";
    var prefix="#"+formid;
    var flagsuspend=false;
    var loadedsysidP="";
    var loadedsysidK="";
    var loadedsysidD="";
    var cachelegend={};
    var cachefields={};
    var cachegrids=[];
    var cachewhere={};
    var cachequeries=[];
    var cachetables=[];
    var cacheviews=[];
    var cachebags=[];
    var cacheregs=[];
    var cachestats=[];
    var cachedetails={};
    var sospendistatistiche=false;
    var timerstatistiche=false;
    var defaultsearch=0;
    var currgaugeid="";
    var gaugelist=[];
    var refreshdettagli=false;
    var refreshstats=false;
    var sospendikrefresh=false;
    
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
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Configurazione",width:200}
        ],
        changerow:function(o,i){
            currconfigid="";
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            objtabs.enabled(4,false);
            context="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currconfigid=d;
            objtabs.enabled(2, true);
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
    $(prefix+"lbf_processo").rylabel({left:430, top:offsety, caption:"Processo"});
    offsety+=20;
    var txf_processo=$(prefix+"txf_processo").ryhelper({left:430, top:offsety, width:300, formid:formid, table:"QW_PROCESSI", title:"Processi", multiple:false,
        open:function(o){
            o.where("");
            o.orderby("DESCRIPTION");
        },
        onselect:function(o, d){
            setTimeout(function(){oper_refresh.engage()}, 100);
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
            var procid=txf_processo.value();
            
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            if(procid!=""){
                if(q!=""){q+=" AND "}
                q+="PROCESSOID='"+procid+"'";
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
    
    // DEFINIZIONE TAB PARAMETRI
    var offsety=90;
    var lb_params_context=$(prefix+"params_context").rylabel({left:20, top:50, caption:""});

    $(prefix+"paramshandle").css({position:"absolute", left:0, top:offsety});
    
    var operp_refresh=$(prefix+"operp_refresh").rylabel({
        left:550,
        top:100,
        caption:"Estrai",
        button:true,
        click:function(o){
            winzProgress(formid);
            for(var g in cachegrids){
                TAIL.enqueue(eseguiquery, g);
            }
            TAIL.enqueue(aftertail, 1);
            TAIL.wriggle();
            objtabs.currtab(3);
        }
    });

    // DEFINIZIONE TAB DETTAGLI
    offsety=80;
    var lb_arrow_context=$(prefix+"arrow_context").rylabel({left:20, top:50, caption:""});

    $(prefix+"arrowhandle").css({position:"absolute", left:0, top:offsety});

    $(prefix+"toolbox").css({position:"absolute", left:710, top:30});
    var gridscript=$(prefix+"gridscript").ryque({
        left:0,
        top:20,
        width:230,
        height:160,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_LEGENDSCRIPT",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Azione",width:200}
        ],
        changerow:function(o,i){
            currengageid="";
            opera_engage.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currengageid=d;
            opera_engage.enabled(1);
        }
    });
    
    offsety=175;
    var opera_refresh=$(prefix+"opera_refresh").rylabel({
        left:0,
        top:offsety,
        width:100,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            if(gridscript.reqid()!=""){
                gridscript.where("SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currconfigid+"')");
                gridscript.query();
            }
            else{
                setTimeout(
                    function(){
                        opera_refresh.engage();
                    }, 300
                );
           }
        }
    });
    var opera_engage=$(prefix+"opera_engage").rylabel({
        left:116,
        top:offsety,
        width:100,
        caption:"Esegui",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eseguire l'azione selezionata?",
                confirm:function(){
                    eseguiscript(currengageid);
                }
            });
        }
    });
    offsety+=40;
    var opera_search=$(prefix+"opera_search").rylabel({
        left:0,
        top:offsety,
        width:65,
        caption:"Ricerca...",
        button:true,
        click:function(o){
            clustersearch.show({
                open:function(){
                    clustersearch.settings({"amount":defaultsearch});
                },
                confirm:function(opt){
                    iniziaricerca(opt);
                },
                close:function(){
                    opera_continue.enabled(0);
                    castFocus(prefix+"opera_search");
                }
            });
        }
    });

    var opera_continue=$(prefix+"opera_continue").rylabel({
        left:75,
        top:offsety,
        width:65,
        caption:"Continua",
        button:true,
        click:function(o){
            continuaricerca();
        }
    });
    opera_continue.enabled(0);
    
    var opera_highlight=$(prefix+"opera_highlight").rylabel({
        left:150,
        top:offsety,
        width:65,
        caption:"Evidenzia",
        button:true,
        click:function(o){
            evidenziasel();
        }
    });
    opera_highlight.enabled(0);
    
    offsety+=40;
    $(prefix+"lba_deselect").rylabel({left:0, top:offsety, caption:"Deselez."});

    var opera_deselmov=$(prefix+"opera_deselmov").rylabel({
        left:60,
        top:offsety,
        width:70,
        caption:"Movimenti",
        button:true,
        click:function(o){
            sospendistatistiche=true;
            for(var i in cachegrids){
                var n=cachegrids[i];
                globalobjs[n].checkall(0);
                globalobjs[n].index(0);
            }
            sospendistatistiche=false;
            aggiornastatistiche();
        }
    });
    opera_deselmov.enabled(0);
    
    var opera_deselpratiche=$(prefix+"opera_deselpratiche").rylabel({
        left:145,
        top:offsety,
        width:70,
        caption:"Pratiche",
        button:true,
        click:function(o){
            gridpratiche.checkall(0);
            gridpratiche.index(0);
            setTimeout(
                function(){
                    aggiornastatistiche();
                }, 300
            );
        }
    });
    opera_deselpratiche.enabled(0);
    
    offsety+=30;
    $(prefix+"lba_cluster").rylabel({left:0, top:offsety, caption:"Cluster"});

    var opera_clusterize=$(prefix+"opera_clusterize").rylabel({
        left:60,
        top:offsety,
        width:70,
        caption:"Crea",
        button:true,
        click:function(o){
            clusterize(1);
        }
    });
    opera_clusterize.enabled(0);

    var opera_free=$(prefix+"opera_free").rylabel({
        left:145,
        top:offsety,
        width:70,
        caption:"Rimuovi",
        button:true,
        click:function(o){
            clusterize(0);
        }
    });
    opera_free.enabled(0);

    offsety+=30;
    $(prefix+"lba_pratiche").rylabel({left:0, top:offsety, caption:"Pratiche"});

    var opera_new=$(prefix+"opera_new").rylabel({
        left:60,
        top:offsety,
        width:70,
        caption:"Crea",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Creare una nuova pratica con i movimenti selezionati?",
                confirm:function(){
                    praticanew();
                }
            });
        }
    });
    opera_new.enabled(0);

    var opera_add=$(prefix+"opera_add").rylabel({
        left:145,
        top:offsety,
        width:70,
        caption:"Aggiungi",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Inserire i movimenti selezionati nella pratica corrente?",
                confirm:function(){
                    praticaadd();
                }
            });
        }
    });
    opera_add.enabled(0);

    offsety+=40;
    var opera_consolidate=$(prefix+"opera_consolidate").rylabel({
        left:40,
        top:offsety,
        width:150,
        caption:"Pratiche da cluster",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Generare pratiche dai cluster?",
                confirm:function(){
                    consolidate();
                }
            });
        }
    });
    
    var clustersearch=new corsaro_clustersearch(formid);
    
    $("#window_"+formid+" .window_content").scroll(
        function(){
            $(prefix+"toolbox").css({top:30+$(this).scrollTop()});
        }
    );
    
    // DEFINIZIONE TAB CLUSTER
    offsety=80;
    var lb_cluster_context=$(prefix+"cluster_context").rylabel({left:20, top:50, caption:""});

    $(prefix+"lbk_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txk_search=$(prefix+"txk_search").rytext({left:100, top:offsety, width:415, 
        assigned:function(){
            setTimeout(function(){operk_refresh.engage()}, 100);
        }
    });
    offsety+=30;

    $(prefix+"lbk_datemin").rylabel({left:20, top:offsety, caption:"Data min"});
    var txk_datemin=$(prefix+"txk_datemin").rydate({left:100, top:offsety,  width:100, 
        assigned:function(){
            setTimeout(function(){operk_refresh.engage()}, 100);
        }
    });
    $(prefix+"lbk_datemax").rylabel({left:230, top:offsety, caption:"Data max"});
    var txk_datemax=$(prefix+"txk_datemax").rydate({left:300, top:offsety,  width:100, 
        assigned:function(){
            setTimeout(function(){operk_refresh.engage()}, 100);
        }
    });
    
    var operk_refresh=$(prefix+"operk_refresh").rylabel({
        left:450,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            if(sospendikrefresh==false){
                gridpratiche.clear();
                var q="";
                var t=qv_forlikeclause(txk_search.value());

                var processoid=cachelegend["PROCESSOID"];
                var datamin=txk_datemin.text();
                var datamax=txk_datemax.text();
                
                q="PROCESSOID='"+processoid+"' AND CONSISTENCY=0 AND STATUS=0";
                if(t!="")
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(REFERENCE)] LIKE '%[=REFERENCE]%' )";
                if(datamin!="")
                    q+=" AND DATAINIZIO>=[:TIME("+datamin+"000000)]";
                if(datamax!="")
                    q+=" AND DATAINIZIO<=[:TIME("+datamax+"235959)]";

                gridpratiche.where(q);
                gridpratiche.query({
                    args:{
                        "DESCRIPTION":t,
                        "REFERENCE":t
                    },
                    ready:function(){
                        if(done!=missing){done()}
                    }
                });
            }
        }
    });
    offsety+=35;
    
    var gridpratiche=$(prefix+"gridpratiche").ryque({
        left:20,
        top:offsety,
        width:500,
        height:400,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_PRATICHE",
        orderby:"DESCRIPTION",
        limit:100000,
        columns:[
            {id:"DESCRIPTION", caption:"Pratica",width:250},
            {id:"AUXAMOUNT", caption:"Totale", type:"2", width:110},
            {id:"DATAINIZIO", caption:"Inizio", type:"/", width:90}
        ],
        changerow:function(o,i){
            currpraticaid="";
            refreshstats=true;
            gridarrows.clear();
            operk_complete.enabled(o.isselected());
            operk_dispose.enabled(o.isselected());
            operk_inter.enabled(o.isselected());
            operk_fitamount.enabled(o.isselected());
            operk_deselect.enabled(o.isselected());
            opera_deselpratiche.enabled(o.isselected());
            opera_add.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        changesel:function(o){
            operk_complete.enabled(o.isselected());
            operk_dispose.enabled(o.isselected());
            operk_inter.enabled(o.isselected());
            operk_fitamount.enabled(o.isselected());
            operk_deselect.enabled(o.isselected());
            opera_deselpratiche.enabled(o.isselected());
        },
        solveid:function(o,d){
            currpraticaid=d;
            operk_complete.enabled(1);
            operk_dispose.enabled(1);
            operk_inter.enabled(1);
            operk_fitamount.enabled(1);
            operk_deselect.enabled(1);
            opera_deselpratiche.enabled(1);
            if(qualcheselezionato()==2){
                opera_add.enabled(1);
            }
            gridarrows_refresh();
        }
    });
    
    var gridarrows=$(prefix+"gridarrows").ryque({
        left:530,
        top:offsety,
        width:700,
        height:400,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWARROWS",
        orderby:"CONSISTENCY DESC,SYSID",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione",width:360},
            {id:"BOWID",caption:"",width:0},
            {id:"TARGETTIME", caption:"", type:"/", width:0},
            {id:"AMOUNT", caption:"Importo", type:"2", width:120},
            {id:"AUXTIME", caption:"Data Reg.", type:"/", width:85},
            {id:"BOWTIME", caption:"Data Val.", type:"/", width:85}
        ],
        changerow:function(o,i){
            currarrowid="";
            operk_remove.enabled(o.isselected());
            $(prefix+"arrow_preview").html("").css({"display":"none"});
            if(i>0){
                o.solveid(i);
            }
        },
        changesel:function(o){
            operk_remove.enabled(o.isselected());
        },
        solveid:function(o, d){
            currarrowid=d;
            operk_remove.enabled(1);
            anteprimamovimento();
        },
        before:function(o, d){
            if(currcontoid!=""){
                for(var i in d){
                    if(d[i]["BOWID"]==currcontoid)
                        d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                    else
                        d[i]["BOWTIME"]=d[i]["TARGETTIME"];
                }
            }
        }
    });

    // CAMBIO DI STATO DELLE PRATICHE: CHIUSURA
    offsety=540;
    var operk_complete=$(prefix+"operk_complete").rylabel({
        left:20,
        top:offsety,
        width:130,
        caption:"Chiudi pratiche",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Chiudere le pratiche selezionate?",
                confirm:function(){
                    $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"legend_complete",
                            "data":{
                                "LEGENDID":currconfigid,
                                "INPUT":"RYQUE",
                                "REQUESTID":gridpratiche.reqid(),
                                "CURRENT":gridpratiche.index(),
                                "SELECTION":gridpratiche.checked(false),
                                "INVERT":gridpratiche.selinvert().stringBoolean()
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    gridpratiche.refresh();
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
    operk_complete.enabled(0);

    var operk_dispose=$(prefix+"operk_dispose").rylabel({
        left:170,
        top:offsety,
        width:130,
        caption:"Rimuovi pratiche",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Rimuovere (quando possibile) le pratiche selezionate?<br/>I movimenti della pratica saranno conservati.",
                confirm:function(){
                    winzProgress(formid);
                    gridpratiche.selengage(
                        function(o, s){
                            winzPostProgress({
                                "function":"legend_dispose",
                                "data":{
                                    "PRATICHE":s,
                                    "PROGRESS":1
                                },
                                "block":1000,
                                "progress":function(l, c, p){
                                    $("#message_"+formid).html("Pratiche rimosse "+l+" di "+c);
                                },
                                "success":function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0){ 
                                            gridpratiche.query({
                                                ready:function(){
                                                    refreshdettagli=true;
                                                }
                                            });
                                        }
                                        winzTimeoutMess(formid, v.success, v.message);
                                    }
                                    catch(e){
                                        winzClearMess(formid);
                                        alert(d);
                                    }
                                },
                                "error":function(){
                                    winzClearMess(formid);
                                    alert("Rimozione fallita");
                                }
                            });
                        }
                    );
                }
            });
        }
    });
    operk_dispose.enabled(0);

    var operk_inter=$(prefix+"operk_inter").rylabel({
        left:380,
        width:130,
        top:offsety,
        caption:"Interazione",
        button:true,
        click:function(o){
            gridpratiche.solveid(gridpratiche.index(),
                function(g,id){
                    _openingparams="({environ:\""+_appname+"_"+_sessioninfo.role+"\",root:\""+_sessioninfo.roledescr+"\",pratica:\""+currpraticaid+"\"})";
                    RYWINZ.newform({
                        name:"qvinterazioni",
                        path:_systeminfo.web.cambusa+"../apps/corsaro/qvpratiche/",
                        title:"Interazioni"
                    });
                }
            );
        }
    });
    operk_inter.enabled(0);

    var operk_remove=$(prefix+"operk_remove").rylabel({
        left:530,
        top:offsety,
        width:130,
        caption:"Togli dalla pratica",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Togliere i movimenti selezionati dalla pratica?",
                confirm:function(){
                    gridarrows.selengage(
                        function(o, s){
                            $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessioninfo.sessionid,
                                    "env":_sessioninfo.environ,
                                    "function":"legend_freearrow",
                                    "data":{
                                        "LEGENDID":currconfigid,
                                        "PRATICAID":currpraticaid,
                                        "ARROWS":s
                                    }
                                }, 
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0){ 
                                            gridarrows_refresh(
                                                function(){
                                                    gridpratiche.dataload(
                                                        function(){
                                                            refreshdettagli=true;
                                                        }
                                                    );
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
                    );
                }
            });
        }
    });
    operk_remove.enabled(0);

    offsety+=40;
    var operk_fitamount=$(prefix+"operk_fitamount").rylabel({
        left:20,
        top:offsety,
        width:130,
        caption:"Ricalcola",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Ricalcolare il saldo della pratiche selezionate?",
                confirm:function(){
                    gridpratiche.selengage(
                        function(o, s){
                            winzProgress(formid);
                            s=s.split("|");
                            var stats=[];
                            for(var i in s){
                                stats[i]={
                                    "function":"pratiche_audit",
                                    "data":{
                                        "PRATICAID":s[i],
                                        "CONTOID":currcontoid,
                                        "GENREID":currgenreid
                                    }
                                };
                            }
                            $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessioninfo.sessionid,
                                    "env":_sessioninfo.environ,
                                    "program":stats
                                }, 
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0){ 
                                            gridpratiche.dataload();
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
        }
    });
    operk_fitamount.enabled(0);

    var operk_deselect=$(prefix+"operk_deselect").rylabel({
        left:170,
        top:offsety,
        width:130,
        caption:"Deseleziona",
        button:true,
        click:function(o){
            gridpratiche.checkall(0);
            gridpratiche.index(0);
            refreshstats=true;
        }
    });
    operk_deselect.enabled(0);
    
    $(prefix+"arrow_preview").css({
        "position":"absolute", 
        "left":530, 
        "top":offsety, 
        "width":694, 
        "height":300, 
        "padding":2, 
        "overflow":"auto", 
        "background-color":"white", 
        "border":"1px solid silver",
        "display":"none"});
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Parametri"},
            {title:"Dettagli"},
            {title:"Pratiche"}
        ],
        select:function(i,p){
            if(i==1){
                loadedsysidP="";
                loadedsysidK="";
                loadedsysidD="";
            }
            else if(i==2){
                if(currconfigid==loadedsysidP){
                    flagsuspend=true;
                }
            }
            else if(i==3){
                if(currconfigid==loadedsysidD){
                    flagsuspend=true;
                }
                if(p==4){
                    if(refreshdettagli){
                        refreshdettagli=false;
                        refreshstats=false;
                        for(var g in cachegrids){
                            TAIL.enqueue(eseguiquery, g);
                        }
                        TAIL.enqueue(aftertail, 0);
                        TAIL.wriggle();
                    }
                    if(refreshstats){
                        setTimeout(
                            function(){
                                aggiornastatistiche();
                            }, 200
                        );
                    }
                }
            }
            else if(i==4){
                if(currconfigid==loadedsysidK){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 1:
                    objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO DEI PARAMETRI
                    lb_params_context.caption("Contesto: "+context);
                    $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"legend_infoconfig",
                            "data":{
                                "LEGENDID":currconfigid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    if(window.console&&_sessioninfo.debugmode){console.log(v)}
                                    context=v.params["DESCRIPTION"];
                                    lb_params_context.caption("Contesto: "+context);
                                    loadedsysidP=currconfigid;
                                    cachelegend=v.params;
                                    currprocessoid=cachelegend["PROCESSOID"];
                                    currgenreid=cachelegend["GENREID"];
                                    currcontoid=cachelegend["CONTOID"];
                                    configuraparametri();
                                    objtabs.enabled(3, true);
                                    objtabs.enabled(4, true);
                                }
                                winzTimeoutMess(formid, v.success, v.message);
                            }
                            catch(e){
                                winzClearMess(formid);
                                alert(d);
                            }
                        }
                    );
                    break;
                case 3:
                    // CARICAMENTO DETTAGLI
                    lb_arrow_context.caption("Contesto: "+context);
                    loadedsysidD=currconfigid;
                    configuradettagli();
                    break;
                case 4:
                    // CARICAMENTO CLUSTER
                    lb_cluster_context.caption("Contesto: "+context);
                    loadedsysidK=currconfigid;
                    setTimeout(function(){operk_refresh.engage()}, 100);
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    objtabs.enabled(4,false);
    
    function configuraparametri(){
        var y=0;
        // DISTRUGGO GLI EVENTUALI CAMPI AUTOCONFIGURATI
        for(var n in cachefields){
            delete globalobjs[n];
            delete _globalforms[formid].controls[n];
        }
        cachefields={};
        // CANCELLO LA VECCHIA CONFIGURAZIONE HTML
        $(prefix+"paramshandle").html("");
        var v=cachelegend["QUERIES"];
        var prepare={};
        for(var q in v){
            var bagname=v[q]["BAGNAME"];
            if(bagname!=""){
                var queryid=v[q]["SYSID"];
                var signum=__(v[q]["SIGNUM"]).actualInteger();
                prepare[queryid]={"caption":v[q]["DESCRIPTION"], "signum":signum};
                var queryname=formid+"_set"+q;
                // RISOLUZIONE PARAMETRI
                var params=v[q]["LEGENDPARAMS"];
                params=params.replace(/&quot;/g, "\"");
                try{
                    params=eval("("+params+")");
                }catch(e){
                    if(window.console){console.log(params)}
                    params={};
                }
                var h=$.objectsize(params)*30;
                
                $(prefix+"paramshandle").append("<fieldset id='"+queryname+"' style='padding-left:10px;'><legend>"+v[q]["DESCRIPTION"]+"</legend></fieldset>");
                $("#"+queryname).css({position:"absolute", left:20, top:y, width:500, height:h+50, border:"1px solid silver"});
                
                // CONFIGURO I CAMPI
                var subh=30;
                
                if($.objectsize(params)>0){
                    for(var f in params){
                        var id="";
                        var tp="";
                        var cap="";
                        var table="QW_CAUSALI";
                        if($.isset(params[f]["id"]))
                            id=""+params[f]["id"];
                        if($.isset(params[f]["type"]))
                            tp=""+params[f]["type"];
                        if($.isset(params[f]["caption"]))
                            cap=params[f]["caption"];
                        if($.isset(params[f]["table"]))
                            table=params[f]["table"];
                        var lb=queryname+"_lb"+"_"+id;
                        var tx=queryname+"_tx"+"_"+id;
                        $("#"+queryname).append("<div id='"+lb+"'></div>");
                        $("#"+queryname).append("<div id='"+tx+"'></div>");
                        $("#"+lb).rylabel({left:20, top:subh, caption:cap, formid:formid});
                        switch(tp){
                        case "0":
                        case "1":
                        case "2":
                        case "3":
                            $("#"+tx).rynumber({left:120, top:subh, width:150, numdec:parseInt(tp), formid:formid,
                                changed:function(){
                                    loadedsysidD="";
                                }
                            });
                            break;
                        case "/":
                            $("#"+tx).rydate({left:120, top:subh, formid:formid,
                                changed:function(){
                                    loadedsysidD="";
                                }
                            });
                            break;
                        case "?":
                            $("#"+tx).rycheck({left:120, top:subh, formid:formid,
                                assigned:function(){
                                    loadedsysidD="";
                                }
                            });
                            break;
                        default:
                            if(tp.indexOf("SYSID")>=0){
                                $("#"+tx).ryhelper({left:120, top:subh, width:200, 
                                    formid:formid, table:table, title:"Scelta "+cap, multiple:true,
                                    open:function(o){
                                        o.where("");
                                        o.orderby("DESCRIPTION");
                                    },
                                    changed:function(){
                                        loadedsysidD="";
                                    }
                                });
                            }
                            else{
                                $("#"+tx).rytext({left:120, top:subh, width:350, formid:formid,
                                    changed:function(){
                                        loadedsysidD="";
                                    }
                                });
                            }
                        }
                        cachefields[lb]=0;
                        cachefields[tx]=0;
                        subh+=30;
                    }
                }
                else{
                    $("#"+queryname).append("<div style='text-align:center;'><i>(parametri non previsti per questa estrazione)</i><div>");
                    subh+=30;
                }
                // INCREMENTO L'OFFSET VERTICALE
                y+=h+70;
            }
        }
        clustersearch.prepare(prepare);
    }
    function configuradettagli(){
        var y=0;
        winzProgress(formid);
        sospendistatistiche=true;
        // DISTRUGGO GLI EVENTUALI CAMPI AUTOCONFIGURATI
        for(var i in cachegrids){
            var n=cachegrids[i];
            delete globalobjs[n];
            delete _globalforms[formid].controls[n];
        }
        for(var n in cachedetails){
            delete globalobjs[n];
            delete _globalforms[formid].controls[n];
        }
        cachegrids=[];
        cachewhere={};
        cachequeries=[];
        cachetables=[];
        cacheviews=[];
        cachebags=[];
        cacheregs=[];
        cachestats=[];
        cachedetails={};
        // CANCELLO LA VECCHIA CONFIGURAZIONE HTML
        $(prefix+"arrowhandle").html("");
        var v=cachelegend["QUERIES"];
        for(var q in v){
            var bagname=v[q]["BAGNAME"];
            if(bagname!=""){
                var queryid=v[q]["SYSID"];
                var table=v[q]["VIEWNAME"];
                var tableext=v[q]["TABLENAME"];
                var gridname=formid+"_grid"+q;
                var regname=formid+"_reg"+q;
                var statname=formid+"_stat"+q;
                var lb=formid+"_lb"+q;
                var cap="<span class='rybox-title'>"+v[q]["DESCRIPTION"]+"</span>";
                // RISOLUZIONE COLONNE
                var columns=v[q]["LEGENDCOLUMNS"];
                columns=columns.replace(/&quot;/g, "\"");
                try{
                    columns=eval("("+columns+")");
                }catch(e){
                    if(window.console){console.log(columns)}
                    columns={};
                }
                // RISOLVO I CAMPI DA DECODIFICARE
                for(var k in columns){
                    if($.isset(columns[k].type)){
                        var t=columns[k].type;
                        if(t.substr(0,5)=="SYSID"){
                            var w=t.substr(6, t.length-7);
                            columns[k].type="";
                            columns[k].formula="SELECT DESCRIPTION FROM "+w+" WHERE "+w+".SYSID="+table+"."+columns[k].id;
                        }
                    }
                }
                columns.unshift({id:"BOWID", caption:"", width:0});
                columns.unshift({id:"CLUSTERID", caption:"Cluster", width:70});
                
                $(prefix+"arrowhandle").append("<div id='"+lb+"'></div>");
                $(prefix+"arrowhandle").append("<div id='"+gridname+"'></div>");
                $(prefix+"arrowhandle").append("<div id='"+regname+"'></div>");
                $(prefix+"arrowhandle").append("<div id='"+statname+"'></div>");
                $("#"+lb).rylabel({left:20, top:y, caption:cap, formid:formid});
                y+=25;
                var gd=$("#"+gridname).ryque({
                    left:5,
                    top:y,
                    width:700,
                    height:400,
                    formid:formid,
                    numbered:false,
                    checkable:true,
                    environ:_sessioninfo.environ,
                    from:table,
                    orderby:"CLUSTERID DESC,DESCRIPTION",
                    limit:100000,
                    columns:columns,
                    ready:function(){
                        opera_continue.enabled(0);
                    },
                    changerow:function(o,i){
                        if(i>0){
                            o.solveid(i);
                        }
                    },
                    changesel:function(o){
                        opera_continue.enabled(0);
                        var qualche=qualcheselezionato();
                        if(qualche==2){
                            opera_highlight.enabled(1);
                            opera_clusterize.enabled(1);
                            opera_free.enabled(1);
                            opera_new.enabled(1);
                            if(gridpratiche.index()>0 && currpraticaid!="")
                                opera_add.enabled(1);
                            else    
                                opera_add.enabled(0);
                        }
                        else{
                            opera_highlight.enabled(0);
                            opera_clusterize.enabled(0);
                            opera_free.enabled(0);
                            opera_new.enabled(0);
                            opera_add.enabled(0);
                        }
                        if(qualche>0)
                            opera_deselmov.enabled(1);
                        else
                            opera_deselmov.enabled(0);
                        aggiornastatistiche();
                    },
                    solveid:function(o, d){
                        opera_deselmov.enabled(1);
                        aggiornastatistiche();
                    },
                    before:function(o, d){
                        if(currcontoid!=""){
                            for(var i in d){
                                if(d[i]["BOWID"]==currcontoid)
                                    d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                                else
                                    d[i]["BOWTIME"]=d[i]["TARGETTIME"];
                            }
                        }
                        if(o.lastorderby().indexOf("CLUSTERID")>=0){
                            var clustid="";
                            var previd="";
                            var color=new Array("red", "blue");
                            var toggle=0;
                            for(var i in d){
                                var fd=o.screenrow(i);
                                clustid=d[i]["CLUSTERID"];
                                if(clustid!=""){
                                    if(clustid!=previd){
                                        toggle=1-toggle;
                                    }
                                    $(fd).css({"color":color[toggle]});
                                }
                                else{
                                    $(fd).css({"color":"#444444"});
                                }
                                previd=clustid;
                            }
                        }
                        else{
                            for(var i in d){
                                var fd=o.screenrow(i);
                                $(fd).css({"color":"black"});
                            }
                        }
                        for(var i in d){
                            if(d[i]["CLUSTERID"]){
                                d[i]["CLUSTERID"]=d[i]["CLUSTERID"].substr(-6);
                            }
                        }
                    },
                    enter:function(o){
                        aggiornastatistiche(o.tag);
                    },
                    beforequery:function(p){
                        if(p.orderby=="AMOUNT")
                            p.orderby="CASE WHEN BOWID='"+currcontoid+"' THEN -AMOUNT ELSE AMOUNT END";
                        else if(p.orderby=="(AMOUNT) DESC")
                            p.orderby="CASE WHEN BOWID='"+currcontoid+"' THEN AMOUNT ELSE -AMOUNT END";
                    }
                });
                gd.tag=queryid;
                gd.enabled(0);
                $("#"+regname).css({"position":"absolute", "left":945, "top":y, "width":300, "height":200, "padding":2, "overflow":"auto", "background":"white", "border":"1px solid silver"});
                $("#"+statname).css({"position":"absolute", "left":945, "top":y+210, "width":300, "height":190, "padding":2, "overflow":"hidden", "background":"white", "border":"1px solid silver"});
                $("#"+statname).html(
                statistiche({
                     "COUNTSEL":"0", 
                     "TOTCOUNTSEL":"0", 
                     "SELECTION":"0", 
                     "TOTSEL":"0", 
                     "COUNTCLUST":"0", 
                     "TOTCOUNTCLUST":"0", 
                     "CLUSTER":"0", 
                     "TOTCLUST":"0", 
                     "PRATICATOT":"0", 
                     "PRATICADESCR":""
                }));
                cachegrids.push(gridname);
                cachewhere[gridname]=costruisciquery(q);
                cachequeries.push(queryid);
                cachetables.push(tableext);
                cacheviews.push(table);
                cachebags.push(bagname);
                cacheregs.push(regname);
                cachestats.push(statname);
                cachedetails[lb]=0;
                cachedetails[regname]=0;
                y+=420;
            }
        }
        for(var g in cachegrids){
            TAIL.enqueue(eseguiquery, g);
        }
        TAIL.enqueue(aftertail, 1);
        TAIL.wriggle();
    }
    function aftertail(r){
        if(r){
            winzClearMess(formid);
            sospendistatistiche=false;
            setTimeout(
                function(){
                    opera_refresh.engage();
                }, 300
            );
        }
        else{
            winzClearMess(formid);
            sospendistatistiche=false;
        }
        TAIL.free();
    }
    function eseguiquery(g){
        var n=cachegrids[g];
        var gd=globalobjs[n];
        if(gd.reqid()!=""){
            gd.enabled(1);
            gd.where( cachewhere[n] );
            gd.query({
                ready:function(){
                    TAIL.free();
                }
            });
        }
        else{
            TAIL.free();
        }
    }
    function eseguiquerychecked(n ,by){
        var gd=globalobjs[n];
        if(by!=gd.lastorderby()){
            gd.query({
                orderby:by,
                selpreserve:true,
                ready:function(o){
                    TAIL.enqueue(
                        function(){
                            if( o.index()!=1 )
                                o.index(1);
                            TAIL.free();
                        }
                    );
                    TAIL.free();
                }
            });
        }
        else{
            TAIL.free();
        }
    }
    function aftertail_check(){
        TAIL.free();
        if(currgaugeid!=""){
            opera_continue.enabled(1);
        }
        setTimeout(
            function(){
                sospendistatistiche=false;
                aggiornastatistiche();
            }, 200
        );
    }
    function eseguidataload(g){
        var n=cachegrids[g];
        var gd=globalobjs[n];
        gd.dataload(
            function(){
                TAIL.free();
            }
        );
    }
    function aftertail_load(){
        TAIL.free();
        aggiornastatistiche();
    }
    function costruisciquery(q){
        var v=cachelegend["QUERIES"];
        var rows=v[q]["LEGENDROWS"];
        rows=rows.replace(/&lt;/g, "<");
        rows=rows.replace(/&gt;/g, ">");
        var params=v[q]["LEGENDPARAMS"].replace(/&quot;/g, "\"");
        try{
            params=eval("("+params+")");
        }catch(e){
            params={};
        }
        var queryname=formid+"_set"+q;
        for(var f in params){
            var id="";
            if($.isset(params[f]["id"]))
                id=""+params[f]["id"];
            var tp="";
            if($.isset(params[f]["type"]))
                tp=""+params[f]["type"];
            var tx=queryname+"_tx"+"_"+id;
            var vl=globalobjs[tx];
            switch(tp){
            case "0":
            case "1":
            case "2":
            case "3":
                vl=vl.value();
                break;
            case "/":
                vl="[:DATE("+vl.text()+")]";
                break;
            case "?":
                vl=vl.value();
                break;
            default:
                vl=vl.value();
                if(tp.indexOf("SYSID")>=0)
                    vl=vl.replace(/[|]/g, "','");
                else
                    vl=vl.replace(/'/g, "''");
                vl="'"+vl+"'";
            }
            rows=rows.replace("[!"+id+"]", vl);
        }
        if(rows!=""){
            rows="("+rows+") AND ";
        }
        rows+="SYSID NOT IN (SELECT QVQUIVERARROW.ARROWID FROM QVQUIVERARROW INNER JOIN QUIVERS_PRATICHE ON QUIVERS_PRATICHE.SYSID=QVQUIVERARROW.QUIVERID WHERE QUIVERS_PRATICHE.PROCESSOID='"+currprocessoid+"') AND (BOWID='"+currcontoid+"' OR TARGETID='"+currcontoid+"') AND (GENREID='"+currgenreid+"')";
        return rows;
    }
    function clusterize(flag){
        winzProgress(formid);
        var data=[];
        for(var i in cachegrids){
            var n=cachegrids[i];
            data.push({
                "REQUESTID":globalobjs[n].reqid(),
                "CURRENT":globalobjs[n].index(),
                "SELECTION":globalobjs[n].checked(false),
                "INVERT":globalobjs[n].selinvert().stringBoolean(),
                "TABLE":cachetables[i]
            });
        }
        $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"legend_clusterize",
                "data":{
                    "LEGENDID":currconfigid,
                    "FLAG":flag,
                    "QUERIES":data
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        for(var g in cachegrids){
                            TAIL.enqueue(eseguidataload, g);
                        }
                        TAIL.enqueue(aftertail_load);
                        TAIL.wriggle();
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
    function praticanew(){
        winzProgress(formid);
        var data=[];
        for(var i in cachegrids){
            var n=cachegrids[i];
            data.push({
                "REQUESTID":globalobjs[n].reqid(),
                "CURRENT":globalobjs[n].index(),
                "SELECTION":globalobjs[n].checked(false),
                "INVERT":globalobjs[n].selinvert().stringBoolean(),
                "QUERYID":cachequeries[i]
            });
        }
        $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"legend_praticanew",
                "data":{
                    "LEGENDID":currconfigid,
                    "QUERIES":data
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        var praticaid=v.params["PRATICAID"];
                        // RESETTO I FILTRI PER ESSERE SICURO DI BECCARE LA NUOVA PRATICA
                        sospendikrefresh=true;
                        txk_search.clear();
                        txk_datemin.clear();
                        txk_datemax.clear();
                        sospendikrefresh=false;
                        // ESEGUO LA QUERY SULLE PRATICHE
                        operk_refresh.engage(
                            function(){
                                gridpratiche.search({
                                        "where": "SYSID='"+praticaid+"'"
                                    },
                                    function(d){
                                        try{
                                            var v=$.parseJSON(d);
                                            var ind=v[0];
                                            gridpratiche.index(ind);
                                            if(loadedsysidK!=currconfigid){
                                                lb_cluster_context.caption("Contesto: "+context);
                                                loadedsysidK=currconfigid;
                                            }
                                            setTimeout(
                                                function(){
                                                    for(var g in cachegrids){
                                                        TAIL.enqueue(eseguiquery, g);
                                                    }
                                                    TAIL.enqueue(aftertail, 0);
                                                    TAIL.wriggle();
                                                }, 300
                                            );
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
                    else{
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                }
                catch(e){
                    winzClearMess(formid);
                    alert(d);
                }
            }
        );
    }
    function praticaadd(){
        var data=[];
        for(var i in cachegrids){
            var n=cachegrids[i];
            data.push({
                "REQUESTID":globalobjs[n].reqid(),
                "CURRENT":globalobjs[n].index(),
                "SELECTION":globalobjs[n].checked(false),
                "INVERT":globalobjs[n].selinvert().stringBoolean(),
                "QUERYID":cachequeries[i]
            });
        }
        $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"legend_praticaadd",
                "data":{
                    "LEGENDID":currconfigid,
                    "PRATICAID":currpraticaid,
                    "QUERIES":data
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        gridpratiche.dataload(
                            function(){
                                gridarrows_refresh(
                                    function(){
                                        for(var g in cachegrids){
                                            TAIL.enqueue(eseguiquery, g);
                                        }
                                        TAIL.enqueue(aftertail, 0);
                                        TAIL.wriggle();
                                    }
                                );
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
    function consolidate(){
        winzProgress(formid);
        var data=[];
        for(var i in cachegrids){
            var n=cachegrids[i];
            data.push({
                "REQUESTID":globalobjs[n].reqid(),
                "WHERE":cachewhere[n],
                "QUERYID":cachequeries[i]
            });
        }
        winzPostProgress({
            "function":"legend_consolidate",
            "data":{
                "LEGENDID":currconfigid,
                "PROGRESS":1,
                "QUERIES":data
            },
            "enabled":1,
            "block":1000,
            "progress":function(l, c, p){
                $("#message_"+formid).html("Pratiche generate "+l+" di "+c);
            },
            "success":function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        gridpratiche.query({
                            ready:function(){
                                for(var g in cachegrids){
                                    TAIL.enqueue(eseguiquery, g);
                                }
                                TAIL.enqueue(aftertail, 0);
                                TAIL.wriggle();
                            }
                        });
                    }
                    winzTimeoutMess(formid, v.success, v.message);
                }
                catch(e){
                    winzClearMess(formid);
                    alert(d);
                }
            },
            "error":function(){
                winzClearMess(formid);
                alert("Generazione fallita");
            }
        });
    }
    function aggiornastatistiche(synchroid){
        if(!sospendistatistiche){
            if(timerstatistiche){
                clearTimeout(timerstatistiche);
                timerstatistiche=false;
            }
            timerstatistiche=setTimeout(function(){
                TAIL.enqueue(function(synchroid){
                    for(var i in cacheregs){
                        $("#"+cacheregs[i]).html("");
                        $("#"+cachestats[i]).html("");
                    }
                    var data=[];
                    synchroid=__(synchroid);
                    for(var i in cachegrids){
                        var n=cachegrids[i];
                        var qid=cachequeries[i];
                        data.push({
                            "REQUESTID":globalobjs[n].reqid(),
                            "CURRENT":globalobjs[n].index(),
                            "SELECTION":globalobjs[n].checked(false),
                            "INVERT":globalobjs[n].selinvert().stringBoolean(),
                            "QUERYID":qid
                        });
                    }
                    $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"legend_statistics",
                            "data":{
                                "LEGENDID":currconfigid,
                                "PRATICAID":currpraticaid,
                                "QUERIES":data,
                                "SYNCHROID":synchroid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    var rf=false;
                                    for(var i in cachegrids){
                                        var n=cachegrids[i];
                                        var qid=cachequeries[i];
                                        var qelem=v["params"]["QUERIES"][qid];
                                        $("#"+cachestats[i]).html(
                                        statistiche({
                                             "COUNTSEL":qelem["COUNTSEL"], 
                                             "TOTCOUNTSEL":qelem["TOTCOUNTSEL"], 
                                             "SELECTION":qelem["SELECTION"], 
                                             "TOTSEL":qelem["TOTSEL"], 
                                             "COUNTCLUST":qelem["COUNTCLUST"], 
                                             "TOTCOUNTCLUST":qelem["TOTCOUNTCLUST"], 
                                             "CLUSTER":qelem["CLUSTER"], 
                                             "TOTCLUST":qelem["TOTCLUST"], 
                                             "PRATICATOT":qelem["PRATICATOT"], 
                                             "PRATICADESCR":qelem["PRATICADESCR"]
                                        }));
                                        var r=qelem["REGISTRY"];
                                        r=r.replace(/&lt;([^<>&]+)&gt;/ig, "<$1>");
                                        $("#"+cacheregs[i]).html(r);
                                        
                                        // SINCRONIZZAZIONE LISTE
                                        if(synchroid!="" && synchroid!=qid){
                                            var firstid=parseInt(qelem["FIRSTID"]);
                                            if( firstid!=globalobjs[n].index() ){
                                                globalobjs[n].index(firstid);
                                                rf=true;
                                            }
                                        }

                                        // IMPORTO DI DEFAULT PER LA RICERCA MANUALE
                                        if(currpraticaid!="")
                                            defaultsearch=-parseFloat(qelem["PRATICATOT"]);
                                        else
                                            defaultsearch=-parseFloat(qelem["TOTSEL"]);
                                    }
                                    if(rf)
                                        aggiornastatistiche();
                                }
                                else{
                                    if(window.console){console.log(d)}
                                }
                            }
                            catch(e){
                                if(window.console){console.log(d)}
                            }
                            TAIL.free();
                        }
                    );
                }, synchroid);
                TAIL.wriggle();
            }, 300);
        }
    }
    function statistiche(stats){
        var h="";
        h+="<table>";
        h+="<tr>";
        h+="    <td><div style='width:60px;'>&nbsp;</div></td>";
        h+="    <td><div style='width:100px;'>&nbsp;</div></td>";
        h+="    <td><div>&nbsp;</div></td>";
        h+="    <td><div style='width:100px;'>&nbsp;</div></td>";
        h+="    <td><div>&nbsp;</div></td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td style='color:navy;' >Selez.:</td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["COUNTSEL"]).formatNumber(0)+"</td>";
        h+="    <td style='color:navy;padding-left:20px;padding-right:5px;'>di</td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["TOTCOUNTSEL"]).formatNumber(0)+"</td>";
        h+="    <td>&nbsp;</td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td style='color:navy;' ></td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["SELECTION"]).formatNumber(2)+"</td>";
        h+="    <td style='color:navy;padding-left:20px;padding-right:5px;'>di</td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["TOTSEL"]).formatNumber(2)+"</td>";
        h+="    <td>&nbsp;</td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td colspan='5'>&nbsp;</td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td style='color:navy;' >Cluster:</td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["COUNTCLUST"]).formatNumber(0)+"</td>";
        h+="    <td style='color:navy;padding-left:20px;padding-right:5px;'>di</td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["TOTCOUNTCLUST"]).formatNumber(0)+"</td>";
        h+="    <td>&nbsp;</td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td style='color:navy;' ></td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["CLUSTER"]).formatNumber(2)+"</td>";
        h+="    <td style='color:navy;padding-left:20px;padding-right:5px;'>di</td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["TOTCLUST"]).formatNumber(2)+"</td>";
        h+="    <td>&nbsp;</td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td colspan='5'>&nbsp;</td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td style='color:navy;' >Pratica:</td>";
        h+="    <td style='text-align:right;white-space:nowrap;'>"+__(stats["PRATICATOT"]).formatNumber(2)+"</td>";
        h+="    <td colspan='3'>&nbsp;</td>";
        h+="</tr>";
        h+="<tr>";
        h+="    <td colspan='5' style='white-space:nowrap;'>"+__(stats["PRATICADESCR"])+"</td>";
        h+="</tr>";
        h+="</table>";
        return h;
    }
    function eseguiscript(engid){
        winzProgress(formid);
        var data=[];
        for(var i in cachegrids){
            var n=cachegrids[i];
            data.push({
                "REQUESTID":globalobjs[n].reqid(),
                "CURRENT":globalobjs[n].index(),
                "SELECTION":globalobjs[n].checked(false),
                "INVERT":globalobjs[n].selinvert().stringBoolean(),
                "WHERE":cachewhere[n],
                "QUERYID":cachequeries[i]
            });
        }
        var jqxhr=winzPostProgress({
            "function":"legend_execute",
            "data":{
                "LEGENDID":currconfigid,
                "SCRIPTID":engid,
                "PRATICAID":currpraticaid,
                "QUERIES":data
            },
            "block":1000,
            "progress":function(l, c, p){
                if(c>0)
                    $("#message_"+formid).html("Cicli "+l+" di "+c);
                else
                    $("#message_"+formid).html("Cicli "+l);
            },
            "success":function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        gridarrows_refresh(
                            function(){
                                for(var g in cachegrids){
                                    TAIL.enqueue(eseguiquery, g);
                                }
                                TAIL.enqueue(aftertail, 0);
                                TAIL.wriggle();
                            }
                        );
                    }
                    winzTimeoutMess(formid, v.success, v.message);
                }
                catch(e){
                    winzClearMess(formid);
                    alert(d);
                }
            },
            "error":function(){
                winzClearMess(formid);
                alert("Esecuzione fallita");
            }
        });
        winzStoppable(formid, jqxhr);
    }
    function qualcheselezionato(){
        var esiste=0;
        for(var i in cachegrids){
            var n=cachegrids[i];
            if(globalobjs[n].ischecked()){
                esiste=2;
                break;
            }
            else if(globalobjs[n].isselected()){
                esiste=1;
            }
        }
        return esiste;
    }
    function gridarrows_refresh(after){
        if(currpraticaid!=""){
            gridarrows.where("GENREID='"+currgenreid+"' AND SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='"+currpraticaid+"')");
            gridarrows.query({
                ready:function(){
                    if(after!=missing){
                        after();
                    }
                }
            });
        }
        else if(after!=missing){
            after();
        }
    }
    function iniziaricerca(opt){
        winzProgress(formid);
        var data=[];
        for(var i in cachegrids){
            var n=cachegrids[i];
            data.push({
                "REQUESTID":globalobjs[n].reqid(),
                "CURRENT":globalobjs[n].index(),
                "SELECTION":globalobjs[n].checked(false),
                "INVERT":globalobjs[n].selinvert().stringBoolean(),
                "WHERE":cachewhere[n],
                "QUERYID":cachequeries[i]
            });
        }
        var jqxhr=$.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"legend_search",
                "data":{
                    "LEGENDID":currconfigid,
                    "OPTIONS":opt,
                    "QUERIES":data
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var result=v["params"]["RESULT"];
                        if($.objectsize(result)>0){
                            var orderby=v["params"]["ORDERBY"];
                            currgaugeid=v["params"]["GAUGEID"];
                            gaugelist.push(currgaugeid);
                            sospendistatistiche=true;
                            var gridorderby=[];
                            for(var i in cachegrids){
                                var n=cachegrids[i];
                                var sel=result[ cachequeries[i] ];
                                globalobjs[n].setchecked(sel);
                                var by=orderby[ cachequeries[i] ];
                                if(by!="SYSID")
                                    gridorderby[i]=by;
                                else
                                    gridorderby[i]=globalobjs[n].lastorderby();
                            }
                            for(var g in cachegrids){
                                TAIL.enqueue(eseguiquerychecked, cachegrids[g], gridorderby[g]);
                            }
                            TAIL.enqueue(aftertail_check);
                            TAIL.wriggle();
                        }
                        else{
                            opera_continue.enabled(0);
                            currgaugeid="";
                            winzMessageBox(formid, "Nessun movimento trovato!");
                        }
                    }
                    winzTimeoutMess(formid, v.success, v.message);
                }
                catch(e){
                    sospendistatistiche=false;
                    winzClearMess(formid);
                    alert(d);
                }
            }
        );
        winzStoppable(formid, jqxhr);
    }
    function continuaricerca(){
        if(currgaugeid==""){
            opera_continue.enabled(0);
            return;
        }
        winzProgress(formid);
        var jqxhr=$.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"legend_search",
                "data":{
                    "LEGENDID":currconfigid,
                    "GAUGEID":currgaugeid
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var result=v["params"]["RESULT"];
                        if($.objectsize(result)>0){
                            var orderby=v["params"]["ORDERBY"];
                            var selexists=false;
                            sospendistatistiche=true;
                            var gridorderby=[];
                            for(var i in cachegrids){
                                var n=cachegrids[i];
                                var sel=result[ cachequeries[i] ];
                                if(sel!=""){selexists=true}
                                globalobjs[n].setchecked(sel);
                                var by=orderby[ cachequeries[i] ];
                                if(by!="SYSID")
                                    gridorderby[i]=orderby[ cachequeries[i] ];
                                else
                                    gridorderby[i]=globalobjs[n].lastorderby();
                            }
                            if(selexists){
                                for(var g in cachegrids){
                                    TAIL.enqueue(eseguiquerychecked, cachegrids[g], gridorderby[g]);
                                }
                                TAIL.enqueue(aftertail_check);
                                TAIL.wriggle();
                            }
                            else{
                                sospendistatistiche=false;
                            }
                        }
                        else{
                            opera_continue.enabled(0);
                            currgaugeid="";
                        }
                    }
                    winzTimeoutMess(formid, v.success, v.message);
                }
                catch(e){
                    sospendistatistiche=false;
                    winzClearMess(formid);
                    alert(d);
                }
            }
        );
        winzStoppable(formid, jqxhr);
    }
    function gaugedispose(){
        if(gaugelist.length>0){
            setTimeout(
                function(){
                    $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"legend_gauge",
                            "data":{
                                "GAUGELIST":gaugelist.join("|")
                            }
                        }, 
                        function(d){
                            gaugelist=[];
                        }
                    );
                }, 200
            );
        }
    }
    function anteprimamovimento(){
        $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"movimenti_preview",
                "data":{
                    "ARROWID":currarrowid,
                    "CONTOID":currcontoid
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        var prev=v.params["PREVIEW"];
                        prev=prev.replace(/&lt;([^<>&]+)&gt;/ig, "<$1>");
                        prev=prev.replace(/&amp;/ig, "&");
                        $(prefix+"arrow_preview").html(prev).css({"display":"block"});
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
    function evidenziasel(){
        var data=[];
        for(var i in cachegrids){
            var n=cachegrids[i];
            data.push({
                "REQUESTID":globalobjs[n].reqid(),
                "SELECTION":globalobjs[n].checked(false),
                "QUERYID":cachequeries[i]
            });
        }
        $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"legend_highlight",
                "data":{
                    "QUERIES":data
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var orderby=v["params"]["ORDERBY"];
                        sospendistatistiche=true;
                        var gridorderby=[];
                        for(var i in cachegrids){
                            var n=cachegrids[i];
                            var by=orderby[ cachequeries[i] ];
                            if(by!="SYSID")
                                gridorderby[i]=by;
                            else
                                gridorderby[i]=globalobjs[n].lastorderby();
                            
                        }
                        for(var g in cachegrids){
                            TAIL.enqueue(eseguiquerychecked, cachegrids[g], gridorderby[g]);
                        }
                        TAIL.enqueue(aftertail_check);
                        TAIL.wriggle();
                    }
                    winzTimeoutMess(formid, v.success, v.message);
                }
                catch(e){
                    sospendistatistiche=false;
                    winzClearMess(formid);
                    alert(d);
                }
            }
        );
    }
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
    this._unload=function(){
        gaugedispose();
        $.pause(200);
    }
}

