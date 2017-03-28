/****************************************************************************
* Name:            qvprogetti.js                                            *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvprogetti(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0PROGETTI000");
    var motivotrans=RYQUE.formatid("0MOTATTTRANS");
    var genereore=RYQUE.formatid("0TIMEHOURS00");
    var generegiorni=RYQUE.formatid("0TIMEDAYS000");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var loadedsys3id="";
    var gantt_attivwidth=250;
    var gantt_ratio=1;
    var gantt_inizio=null;
    var gantt_fine=null;
    var gantt_rowh=20;
    var gantt_width=700;
    var gantt_prat={};
    var gantt_iniziodate="9999-12-31 23:59";
    var gantt_finedate="1900-01-01 00:00";
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    // RICERCA PROGETTI
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:80, top:offsety, width:340, 
        assigned:function(){
            oper_refresh.engage();
        }
    });
    // REFRESH RICERCA
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:640,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            
            q="USERINSERTID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"')";
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
    offsety+=35;

    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_PROGETTI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:190}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                if(currsysid!=""){
                    objtabs.enabled(2, false);
                    objtabs.enabled(3, false);
                    objtabs.enabled(4, false);
                }
                currsysid="";
                oper_print.enabled(o.isselected());
                oper_delete.enabled(o.isselected());
            }
            context="";
        },
        changesel:function(o){
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_print.enabled(1);
            oper_delete.enabled(1);
            if(currsysid==""){
                currsysid=d;
                objtabs.enabled(2, true);
                objtabs.enabled(3, true);
                objtabs.enabled(4, true);
            }
            else{
                currsysid=d;
            }
            if(flagopen){
                flagopen=false;
                objtabs.currtab(2);
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    
    offsety=410;
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuovo progetto)";
            data["TYPOLOGYID"]=currtypologyid;
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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

    var oper_print=$(prefix+"oper_print").rylabel({
        left:400,
        top:offsety,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "@customize/corsaro/reporting/rep_objects.php")
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:590,
        top:offsety,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "objects");
        }
    });

    // DEFINIZIONE TAB CONTESTO
    offsety=60;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:110, top:offsety, width:310, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;

    /*
    $(prefix+"LB_MANAGERID").rylabel({left:20, top:offsety, caption:"Manager"});
    $(prefix+"MANAGERID").ryhelper({
        left:110, top:offsety, width:310, datum:"C", tag:"MANAGERID", formid:formid, table:"QW_ATTORI", title:"Attori",
        open:function(o){
            o.where("(EGOUTENTEID<>'' OR EGORUOLOID<>'')");
        }
    });
    offsety+=30;
    */
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    var context_registry=$(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    offsety+=410;
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:660,
        top:70,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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
    
    var offsetx=20;
    
    // AGGIUNGI PRATICHE
    var oper_prjadd=$(prefix+"oper_prjadd").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Aggiungi",
        button:true,
        click:function(o, done){
            RYQUIVER.RequestID(formid, {
                table:"QW_PRATICHE", 
                where:"GANTT=1 AND STATUS<2 AND SYSID NOT IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currsysid+"')",
                title:"Scelta pratiche",
                multiple:true,
                onselect:function(d){
                    var ids=d["SYSID"];
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_add",
                            "data":{
                                "UPWARD":1,
                                "PARENTTABLE":"QVOBJECTS",
                                "PARENTID":currsysid,
                                "SELECTEDTABLE":"QVQUIVERS",
                                "SELECTION":ids
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    loadedsys3id="";
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
    // RIMUOVI PRATICHE
    var oper_prjremove=$(prefix+"oper_prjremove").rylabel({
        left:offsetx+100,
        top:offsety,
        caption:"Rimuovi",
        button:true,
        click:function(o, done){
            gridpratiche.selengage(
                function(o, s){
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_remove",
                            "data":{
                                "PARENTID":currsysid,
                                "SELECTION":s
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    loadedsys3id="";
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
            );
        }
    });
    // SVUOTA PRATICHE
    var oper_prjempty=$(prefix+"oper_prjempty").rylabel({
        left:offsetx+200,
        top:offsety,
        caption:"Svuota",
        button:true,
        click:function(o, done){
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_remove",
                    "data":{
                        "PARENTID":currsysid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            loadedsys3id="";
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
    offsety+=30;
    
    gridpratiche=$(prefix+"gridpratiche").ryque({
        left:offsetx,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_PRATICHE",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Pratiche seguite",width:200}
        ],
        changerow:function(o,i){
            oper_prjremove.enabled(o.isselected());
            if(i>0){
                o.solveid(i);
            }
        },
        changesel:function(o){
            oper_prjremove.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_prjremove.enabled(1);
        }
    });
    offsety+=350;
    $(prefix+"contestobottom").css({"position":"absolute", "left":0, "top":offsety});
    
    // DEFINIZIONE TAB GANTT
    var gantt_context=$(prefix+"gantt_context").rylabel({left:20, top:50, caption:""});
    offsety=80;
    var oper_ganttrefresh=$(prefix+"oper_ganttrefresh").rylabel({
        left:20,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            tracciaGantt();
        }
    });

    $(prefix+"lb_gantt_zoom").rylabel({left:130, top:offsety, caption:"Zoom"});
    var gantt_zoom=$(prefix+"gantt_zoom").rylist({left:170, top:offsety, width:70,
        changed:function(){
            gantt_ratio=gantt_zoom.key()/100;
            refreshGantt();
        }
    });
    gantt_zoom
    .additem({caption:"25%", key:25})
    .additem({caption:"50%", key:50})
    .additem({caption:"100%", key:100})
    .additem({caption:"200%", key:200})
    .additem({caption:"400%", key:400})
    .additem({caption:"800%", key:800})
    .additem({caption:"1600%", key:1600})
    .value(3);

    var operg_print=$(prefix+"operg_print").rylabel({
        left:400,
        top:offsety,
        caption:"Stampa",
        button:true,
        click:function(o){
            RYQUIVER.PrintElement(formid+"GANTT");
        }
    });
    
    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_PROGETTI");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione", csize:800},
            {title:"Contesto", csize:800},
            {title:"Gantt"},
            {title:"Documenti", csize:800}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedsysid="";
                        loadedsys3id="";
                    }
                });
            }
            if(i==1){
                loadedsysid="";
                loadedsys3id="";
            }
            else if(i==2){
                if(currsysid==loadedsysid){
                    flagsuspend=true;
                }
            }
            else if(i==3){
                if(currsysid==loadedsys3id){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 1:
                    break;
                case 2:
                    // CARICAMENTO DEL CONTESTO
                    if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currsysid)}
                    RYWINZ.MaskClear(formid, "C");
                    RYQUE.query({
                        sql:"SELECT * FROM QW_PROGETTI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            gridpratiche.where("SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currsysid+"')");
                            gridpratiche.query({
                                ready:function(){
                                    castFocus(prefix+"DESCRIPTION");
                                }
                            });
                        }
                    });
                    break;
                case 3:
                    // TRACCIAMENTO GANTT
                    gantt_context.caption("Contesto: "+context);
                    loadedsys3id=currsysid;
                    tracciaGantt();
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            filemanager.caption("Contesto: "+context);
                        }
                    });
                }
            }
            flagsuspend=false;
        }
    });
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    objtabs.enabled(4,false);
    
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
    function tracciaGantt(){
        // REPERSICO LE PRATICHE SELEZIONATE
        RYQUE.query({
            sql:"SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTTABLE='QVOBJECTS' AND PARENTID='"+currsysid+"' AND SELECTEDTABLE='QVQUIVERS'",
            ready:function(v){
                var p=[];
                for(var i in v)
                    p[i]=v[i]["SELECTEDID"];
                var pratiche=p.join("','");
                // REPERISCO LE ATTIVITA' DELLE PRATICHE SELEZIONATE
                var sql=buildquery(pratiche);
                RYQUE.query({
                    sql:sql,
                    ready:function(v){
                        var arrayatt=v;
                        RYQUE.query({
                            sql:"SELECT SYSID,DESCRIPTION,DATAINIZIO,DATAFINE FROM QW_PRATICHE WHERE SYSID IN ('"+pratiche+"')",
                            ready:function(v){
                                var arrayprat={};
                                for(var i in v){
                                    arrayprat[v[i]["SYSID"]]={};
                                    arrayprat[v[i]["SYSID"]]["PRATICA"]=v[i]["DESCRIPTION"];
                                    arrayprat[v[i]["SYSID"]]["PRATICAINIZIO"]=v[i]["DATAINIZIO"];
                                    arrayprat[v[i]["SYSID"]]["PRATICAFINE"]=v[i]["DATAFINE"];
                                }
                                gantt_prat={};
                                gantt_iniziodate="9999-12-31 23:59";
                                gantt_finedate="1900-01-01 00:00";
                                for(var i in arrayatt){
                                    var pid=arrayatt[i]["PRATICAID"];
                                    if( !$.isset(gantt_prat[pid]) ){
                                        gantt_prat[pid]={};
                                        gantt_prat[pid]["INDEX"]=0;
                                        gantt_prat[pid]["NUMATT"]=0;
                                        gantt_prat[pid]["DATA"]=[];
                                        gantt_prat[pid]["PRATICAID"]=arrayatt[i]["PRATICAID"];
                                        gantt_prat[pid]["PRATICA"]=arrayprat[pid]["PRATICA"];
                                        gantt_prat[pid]["PRATICAINIZIO"]=arrayprat[pid]["PRATICAINIZIO"];
                                        gantt_prat[pid]["PRATICAFINE"]=arrayprat[pid]["PRATICAFINE"];

                                        if(gantt_iniziodate>gantt_prat[pid]["PRATICAINIZIO"] && gantt_prat[pid]["PRATICAINIZIO"].substr(0,4)!="1900" )
                                            gantt_iniziodate=gantt_prat[pid]["PRATICAINIZIO"]
                                        if(gantt_finedate<gantt_prat[pid]["PRATICAFINE"] && gantt_prat[pid]["PRATICAFINE"].substr(0,4)!="1900")
                                            gantt_finedate=gantt_prat[pid]["PRATICAFINE"]
                                    }
                                    var c=gantt_prat[pid]["INDEX"];
                                    if(arrayatt[i]["MOTIVEID"]!=motivotrans){
                                        gantt_prat[pid]["NUMATT"]+=1;
                                    }
                                    gantt_prat[pid]["DATA"][c]={};
                                    gantt_prat[pid]["DATA"][c]["SYSID"]=arrayatt[i]["SYSID"];
                                    gantt_prat[pid]["DATA"][c]["DESCRIPTION"]=arrayatt[i]["DESCRIPTION"];
                                    gantt_prat[pid]["DATA"][c]["BOW"]=arrayatt[i]["BOW"];
                                    gantt_prat[pid]["DATA"][c]["TARGET"]=arrayatt[i]["TARGET"];
                                    gantt_prat[pid]["DATA"][c]["OWNER"]=arrayatt[i]["OWNER"];
                                    gantt_prat[pid]["DATA"][c]["BOWTIME"]=arrayatt[i]["BOWTIME"];
                                    gantt_prat[pid]["DATA"][c]["TARGETTIME"]=arrayatt[i]["TARGETTIME"];
                                    gantt_prat[pid]["DATA"][c]["GENREID"]=arrayatt[i]["GENREID"];
                                    gantt_prat[pid]["DATA"][c]["AMOUNT"]=arrayatt[i]["AMOUNT"];
                                    gantt_prat[pid]["DATA"][c]["STATOID"]=arrayatt[i]["STATOID"];
                                    gantt_prat[pid]["DATA"][c]["STATODESCR"]=arrayatt[i]["STATODESCR"];
                                    gantt_prat[pid]["DATA"][c]["STATUS"]=arrayatt[i]["STATUS"];
                                    gantt_prat[pid]["DATA"][c]["PERCENTUALE"]=arrayatt[i]["PERCENTUALE"];
                                    gantt_prat[pid]["DATA"][c]["MOTIVEID"]=arrayatt[i]["MOTIVEID"];
                                    gantt_prat[pid]["DATA"][c]["MOTIVE"]=arrayatt[i]["MOTIVE"];
                                    gantt_prat[pid]["INDEX"]+=1;
                                    
                                    if(gantt_iniziodate>arrayatt[i]["BOWTIME"] && arrayatt[i]["BOWTIME"].substr(0,4)!="1900" )
                                        gantt_iniziodate=arrayatt[i]["BOWTIME"]
                                    if(gantt_finedate<arrayatt[i]["TARGETTIME"] && arrayatt[i]["TARGETTIME"].substr(0,4)!="1900" && arrayatt[i]["TARGETTIME"].substr(0,4)!="9999")
                                        gantt_finedate=arrayatt[i]["TARGETTIME"]
                                }
                                refreshGantt();
                            }
                        });
                    }
                });
            }
        });
    }
    function refreshGantt(){
        try{
            $(prefix+"GANTT").html("");
            gantt_inizio=absoluteCoord(gantt_iniziodate);
            gantt_fine=absoluteCoord(gantt_finedate);
            gantt_width=relativeCoord(gantt_fine);
            var height=0;
            
            // VALUTO L'ALTEZZA DEL GANTT
            for(var p in gantt_prat){
                var att=gantt_prat[p]["DATA"];
                var l=gantt_prat[p]["NUMATT"];
                if(l>0)
                    height+=gantt_rowh*(l+2);
            }
            height-=gantt_rowh;
            
            $(prefix+"GANTT").css({"position":"absolute", "left":20, "top":170, "width":gantt_width, "height":height});

            // RETTANGOLI PRATICHE
            coordy=0;
            for(var p in gantt_prat){
                var att=gantt_prat[p]["DATA"];
                var l=gantt_prat[p]["NUMATT"];
                if(l>0){
                    var n=gantt_prat[p]["PRATICA"];
                    var id=gantt_prat[p]["PRATICAID"];
                    
                    // TITOLO PRATICA
                    $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"'>"+n+"</div>");
                    $(prefix+"GANTT_"+id).css({position:"absolute", background:"#4169E1", color:"white", top:coordy, width:gantt_attivwidth, height:gantt_rowh, overflow:"hidden", "font-weight":"bold", "padding-left":1, "text-align":"center"});
                    
                    // RETTANGOLO PRATICA
                    var pini=gantt_prat[p]["PRATICAINIZIO"];
                    var pfin=gantt_prat[p]["PRATICAFINE"];
                    var d;
                    var es=risolviEstremi(pini,pfin);
                    var prevdatax=0;
                    var lastdatax=0;
                    if(es.inizio<es.fine){
                        $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_P'></div>");
                        $(prefix+"GANTT_"+id+"_P").css({position:"absolute", background:"#ddd", color:"white", left:es.inizio, top:coordy+gantt_rowh, width:es.fine-es.inizio, height:gantt_rowh*l-1, overflow:"hidden", "border-left":"1px dashed silver", "border-right":"1px dashed silver"});
                        
                        // LABEL INIZIO PRATICA
                        d=absoluteCoord(pini);
                        d=d.getDate()+"/"+(d.getMonth() + 1)+"/"+d.getFullYear();
                        $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_I'>"+d+"</div>");
                        $(prefix+"GANTT_"+id+"_I").css({position:"absolute", left:es.inizio, top:coordy-gantt_rowh, width:70, height:gantt_rowh , "border-left":"1px dashed silver"});
                        
                        // LABEL FINE PRATICA
                        d=absoluteCoord(pfin);
                        d=d.getDate()+"/"+(d.getMonth() + 1)+"/"+d.getFullYear();
                        $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_F'>"+d+"</div>");
                        $(prefix+"GANTT_"+id+"_F").css({position:"absolute", left:es.fine+1, top:coordy-gantt_rowh, width:70, height:gantt_rowh , "border-left":"1px dashed silver"});
                        
                        // POSIZIONI PER NON FAR ACCAVALLARE LE DATE DI INIZIO ATTIVITA'
                        prevdatax=es.inizio;
                        lastdatax=es.fine+1;
                    }
                    
                    // RETTANGOLI STATI
                    var flaginit=false;
                    var stati=[];
                    var statoc=0;
                    stati[statoc]={};
                    stati[statoc]["DESCRIPTION"]=att[0]["STATODESCR"];
                    stati[statoc]["OWNER"]=att[0]["OWNER"];
                    stati[statoc]["INIZIO"]=gantt_prat[p]["PRATICAINIZIO"];
                    stati[statoc]["FINE"]=gantt_prat[p]["PRATICAFINE"];
                    for(var a in att){
                        if(att[a]["MOTIVEID"]==motivotrans){
                            // SPLITTO L'ULTIMO STATO
                            stati[statoc]["FINE"]=att[a]["BOWTIME"];
                            statoc+=1;
                            stati[statoc]={};
                            stati[statoc]["DESCRIPTION"]=att[a]["STATODESCR"];
                            stati[statoc]["OWNER"]=att[a]["OWNER"];
                            stati[statoc]["INIZIO"]=att[a]["BOWTIME"];
                            stati[statoc]["FINE"]=gantt_prat[p]["PRATICAFINE"];
                        }
                    }
                    var col="";
                    for(var s in stati){
                        if((s % 2)==0)
                            col="#87CEEB";
                        else
                            col="#B0E0E6";
                        var es=risolviEstremi(stati[s]["INIZIO"], stati[s]["FINE"]);
                        if(es.inizio<es.fine){
                            $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_"+s+"_ST'>"+stati[s]["DESCRIPTION"]+" ("+stati[s]["OWNER"]+")"+"</div>");
                            $(prefix+"GANTT_"+id+"_"+s+"_ST").css({position:"absolute", background:col, color:"black", left:es.inizio, top:coordy, width:es.fine-es.inizio-2, height:gantt_rowh-1, overflow:"hidden","padding-left":2, "border-left":"1px dashed silver", "border-right":"1px dashed silver"});
                        }
                    }
                    
                    // RETTANGOLI ATTIVITA'
                    var j=1;
                    for(var a in att){
                        if(att[a]["MOTIVEID"]!=motivotrans){
                            var aid=att[a]["SYSID"];
                            var an=att[a]["DESCRIPTION"];
                            var gg=__(att[a]["AMOUNT"]).actualInteger();
                            if(att[a]["GENREID"]==genereore)
                                gg/=8;
                            var tit=an+":\n   "+att[a]["TARGET"]+"\n   da "+humandate(att[a]["BOWTIME"])+"\n   a "+humandate(att[a]["TARGETTIME"])+"\n   giorni effettivi "+gg;
                            col="#0C0";  // Verde
                            if(__(att[a]["STATUS"]).actualInteger()==0){
                                switch(__(att[a]["PERCENTUALE"]).actualInteger()){
                                case 1:
                                    col="#C60";break
                                case 2:
                                    col="#A80";break
                                case 3:
                                    col="#8A0";break
                                default:
                                    col="#F00";  // Rosso
                                }
                            }
                            $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_"+aid+"'>"+an+"</div>");
                            $(prefix+"GANTT_"+id+"_"+aid).css({position:"absolute", background:"#87CEEB", color:"black", top:coordy+gantt_rowh*j, width:gantt_attivwidth, height:gantt_rowh-1, overflow:"hidden", "white-space":"nowrap", "padding-left":1})
                            .attr({title:tit});
                            var es=risolviEstremi(att[a]["BOWTIME"], att[a]["TARGETTIME"]);
                            if(es.inizio<es.fine){
                                $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_"+aid+"_S'>"+att[a]["TARGET"]+"</div>");
                                $(prefix+"GANTT_"+id+"_"+aid+"_S").css({position:"absolute", background:col, color:"white", left:es.inizio, top:coordy+gantt_rowh*j, width:es.fine-es.inizio, height:gantt_rowh-1, overflow:"hidden", "white-space":"nowrap"})
                                .attr({title:tit});
                            }
                            // DATE INIZIO ATTIVITA'
                            if(es.inizio>prevdatax+100 && es.inizio<lastdatax-100){
                                d=absoluteCoord(att[a]["BOWTIME"]);
                                d=d.getDate()+"/"+(d.getMonth() + 1)+"/"+d.getFullYear();
                                $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_I"+aid+"'>"+d+"</div>");
                                $(prefix+"GANTT_"+id+"_I"+aid).css({position:"absolute", left:es.inizio, top:coordy-gantt_rowh, width:70, height:gantt_rowh*(j+2), "border-left":"1px dashed silver"});
                                prevdatax=es.inizio;
                            }
                            // LINEA GUIDA
                            $(prefix+"GANTT").append("<div id='"+formid+"GANTT_"+id+"_L"+aid+"'></div>");
                            $(prefix+"GANTT_"+id+"_L"+aid).css({position:"absolute", left:gantt_attivwidth, top:gantt_rowh*(j+1)-3, width:es.inizio-gantt_attivwidth, height:1, "border-bottom":"1px dashed silver"});

                            // AVANZAMENTO RIGA
                            j+=1;
                        }
                    }
                    coordy+=gantt_rowh*(l+2);
                }
            }
            // OGGI
            var c=relativeCoord(new Date());
            if(c>gantt_attivwidth && c<gantt_width){
                $(prefix+"GANTT").append("<div id='"+formid+"GANTT_TODAY'>"+humandate(new Date())+"</div>");
                $(prefix+"GANTT_TODAY").css({position:"absolute", left:c+2, top:-40, height:gantt_rowh, overflow:"hidden", "font-weight":"bold"});
                $(prefix+"GANTT").append("<div id='"+formid+"GANTT_TODAYLINE'></div>");
                $(prefix+"GANTT_TODAYLINE").css({position:"absolute", left:c, top:-40, width:2, height:height+3*gantt_rowh, overflow:"hidden", "border-left":"1px dashed silver"});
            }
        }
        catch(er){
            alert(er.message);
        }
    }
    function absoluteCoord(dt){
        var y=dt.substr(0, 4).actualInteger();
        var m=dt.substr(5, 2).actualInteger()-1;
        var d=dt.substr(8, 2).actualInteger();
        return new Date(y, m, d);
    }
    function relativeCoord(dt){
        return gantt_attivwidth+1+Math.round((dt-gantt_inizio)/1000/60/60/6*gantt_ratio, 0);
    }
    function risolviEstremi(bt, tt){
        var rbt=relativeCoord(absoluteCoord(bt));
        var rtt=relativeCoord(new Date(absoluteCoord(tt).valueOf()+1000*60*60*24));
        if(rbt<gantt_attivwidth+1)
            rbt=gantt_attivwidth+1;
        if(rtt>gantt_width)
            rtt=gantt_width;
        return {inizio:rbt, fine:rtt};
    }
    function humandate(sql){
        var a=sql;
        if((typeof sql)=="string")
            a=absoluteCoord(sql);
        return a.getDate()+"/"+(a.getMonth() + 1)+"/"+a.getFullYear();
    }
    function buildquery(pratiche){
        var sql="";
        sql+="SELECT ";
        sql+="SYSID,";
        sql+="DESCRIPTION,";
        sql+="BOW,";
        sql+="TARGET,";
        sql+="OWNER,";
        sql+="BOWTIME,";
        sql+="TARGETTIME,";
        sql+="GENREID,";
        sql+="AMOUNT,";
        sql+="STATOID,";
        sql+="STATODESCR,";
        sql+="STATUS,";
        sql+="MOTIVEID,";
        sql+="MOTIVE,";
        sql+="PERCENTUALE,";
        sql+="PRATICAID ";
        sql+="FROM QW_ATTIVITAJOIN ";
        sql+="WHERE AVAILABILITY<2 AND ";
        sql+="(GANTT=1 OR MOTIVEID='"+motivotrans+"') AND ";
        sql+="PRATICAID IN ('"+pratiche+"') ";
        sql+="ORDER BY BOWTIME";
        return sql;
    }
    this._timer=function(){
        if(objtabs.currtab()==3){
            tracciaGantt();
        }
    }
}

