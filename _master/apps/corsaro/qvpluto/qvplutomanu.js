/****************************************************************************
* Name:            qvplutomanu.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvplutomanu(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    // VARIABILI PRATICA
    var currpraticaid="";
    var currchiusa=0;
    var currprocessoname="";    // Parametro in ingresso
    var currprocessoid="";      // Parametro dedotto
    var currflussoid="";
    var currswap=0;
    var currsegno=0;
    
    // APERTURA AUTOMATICA
    var openprocessoid="";
    var openpraticaid="";
    
   // VARIABILI CONTESTO
    var context="";
    
    // VARIABILI DI MASCHERA
    var prefix="#"+formid;
    var flagopen=false;
    var flagopenD=false;
    var flagsuspend=false;
    var loadedpraticaDid="";
    
    // DETERMINO IL PROCESSO
    if($.isset(settings["processo"])){
        currprocessoname=settings["processo"].toUpperCase();
    }
    // DETERMINO LA PRATICA
    if($.isset(settings["pratica"])){
        openpraticaid=settings["pratica"];
    }

    $(prefix+"LB_PROCESSO").addClass("rybox-title").css({"left":20, "top":10});
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:200, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });

    var lbf_processo=$(prefix+"lbf_processo").rylabel({left:340, top:offsety, caption:"Processo*"});
    var txf_processo=$(prefix+"txf_processo").ryhelper({left:430, top:offsety, width:200, 
        formid:formid, table:"QW_PROCESSI", title:"Processi", multiple:false,
        open:function(o){
            o.where("");
            o.orderby("DESCRIPTION");
        },
        assigned: function(){
            currprocessoid=txf_processo.value();
            $.cookie(_sessioninfo.environ+"_pluto_processo", currprocessoid, {expires:10000});
            setTimeout(function(){oper_refresh.engage()}, 100);
        },
        onselect:function(o, d){
            $(prefix+"LB_PROCESSO").html("Processo: "+d["DESCRIPTION"]);
        },
        clear:function(){
            currprocessoid="";
            $(prefix+"LB_PROCESSO").html("");
        }
    });

    offsety+=30;
    
    $(prefix+"lbf_richiedente").rylabel({left:340, top:offsety, caption:"Richiedente"});
    var txf_richiedente=$(prefix+"txf_richiedente").ryhelper({left:430, top:offsety, width:200, 
        formid:formid, table:"QW_ATTORI", title:"Attori", multiple:false,
        open:function(o){
            o.where("");
            o.orderby("DESCRIPTION");
        },
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });

    $(prefix+"lbf_datemin").rylabel({left:20, top:offsety, caption:"Data min"});
    var txf_datemin=$(prefix+"txf_datemin").rydate({left:100, top:offsety,  width:100, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    
    offsety+=30;
    
    $(prefix+"lbf_datemax").rylabel({left:20, top:offsety, caption:"Data max"});
    var txf_datemax=$(prefix+"txf_datemax").rydate({left:100, top:offsety,  width:100, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    
    var lbf_aperte=$(prefix+"lbf_aperte").rylabel({left:340, top:offsety, caption:"Solo aperte"});
    var chk_aperte=$(prefix+"chk_aperte").rycheck({left:430, top:offsety,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    chk_aperte.value(1);
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            objgridsel.clear();
            if(currprocessoid!=""){
                var q="";
                var t=qv_forlikeclause(txf_search.value());
                var richid=txf_richiedente.value();
                var aperte=chk_aperte.value();
                var datamin=txf_datemin.text();
                var datamax=txf_datemax.text();
                
                q="PROCESSOID='"+currprocessoid+"'";
                
                if(t!="")
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                if(richid!="")
                    q+=" AND RICHIEDENTEID='"+richid+"'";
                if(datamin!="")
                    q+=" AND DATAINIZIO>=[:TIME("+datamin+"000000)]";
                if(datamax!="")
                    q+=" AND DATAINIZIO<=[:TIME("+datamax+"235959)]";
                if(aperte)
                    q+=" AND STATUS=0";

                objgridsel.where(q);
                objgridsel.query({
                    args:{
                        "DESCRIPTION":t,
                        "TAG":t
                    },
                    ready:function(){
                        if(done!=missing){done()}
                        if(openprocessoid!=""){
                            // MI POSIZIONO SULLA PRATICA DI OPEN
                            automazione();
                        }
                    }
                });
            }
            else{
                oper_new.enabled(0);
            }
        }
    });
    offsety+=35;

    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_PRATICHEJOIN",
        orderby:"STATUS, AUXTIME DESC",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:300},
            {id:"STATUS", caption:"C", width:40, type:"?"},
            {id:"RICHIEDENTE", caption:"Richiedente", width:180},
            {id:"DATAINIZIO", caption:"Inizio pratica", type:"/", width:100}
        ],
        changerow:function(o,i){
            currpraticaid="";
            currswap=0;
            currsegno=0;
            loadedpraticaDid="";
            currchiusa=0;
            objtabs.enabled(2, false);
            objtabs.enabled(3, false);
            context="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currpraticaid=d;
            objtabs.enabled(2, true);
            objtabs.enabled(3, true);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(2);
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    offsety=470;
    
    // DEFINIZIONE TAB DETTAGLIO
    offsety=80;
    var lb_dettaglio_context=$(prefix+"dettaglio_context").rylabel({left:20, top:50, caption:""});

    var operd_refresh=$(prefix+"operd_refresh").rylabel({
        left:630,
        top:80,
        width:80,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="PRATICAID='"+currpraticaid+"'";
            gridflussi.clear()
            gridflussi.where(q);
            gridflussi.query();
        }
    });
    offsety+=35;
    
    var gridflussi=$(prefix+"gridflussi").ryque({
        left:20,
        top:offsety,
        width:700,
        height:400,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_FINGROUP",
        orderby:"DATAVAL",
        columns:[
            {id:"DATAVAL", caption:"Data", width:100, type:"/"},
            {id:"CAPITALE", caption:"Capitale", width:110, type:"2"},
            {id:"INTINC", caption:"Int. inc.", width:110, type:"2"},
            {id:"TASSOINC", caption:"Tasso. inc.", width:110, type:"4"},
            {id:"INTPAG", caption:"Int. pag.", width:110, type:"2"},
            {id:"TASSOPAG", caption:"Tasso. pag.", width:110, type:"4"}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o, i){
            currflussoid="";
            operd_ricalcola.enabled(0);
            operd_update.enabled(0);
            flussopulisci();
            operd_unsaved.visible(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o, d){
            currflussoid=d;
            flussocarica();
        },
        before:function(o, d){
            for(var i in d){
                if(parseFloat(d[i]["CAPITALE"])==0)
                    d[i]["CAPITALE"]="";
                if(parseFloat(d[i]["INTINC"])==0)
                    d[i]["INTINC"]="";
                if(parseFloat(d[i]["INTPAG"])==0)
                    d[i]["INTPAG"]="";
                if(parseFloat(d[i]["TASSOINC"])==0)
                    d[i]["TASSOINC"]="";
                if(parseFloat(d[i]["TASSOPAG"])==0)
                    d[i]["TASSOPAG"]="";
            }
        }
    });
    
    offsety+=400;
    var operd_add=$(prefix+"operd_add").rylabel({
        left:20,
        top:offsety,
        width:80,
        caption:"Aggiungi",
        button:true,
        click:function(o){
            currflussoid="";
            gridflussi.index(0);
            txd_data.enabled(1);
            chk_capitale.enabled(1);
            if(currswap==1 || currsegno>0){
                chk_intinc.enabled(1);
                chk_tassoinc.enabled(1);
                chk_spreadinc.enabled(1);
                chk_comminc.enabled(1);
            }
            if(currswap==1 || currsegno<0){
                chk_intpag.enabled(1);
                chk_spreadpag.enabled(1);
                chk_tassopag.enabled(1);
                chk_commpag.enabled(1);
            }
            operd_update.enabled(1);
        }
    });

    var operd_unsaved=$(prefix+"operd_unsaved").rylabel({left:280, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    operd_unsaved.visible(0);
    
    var operd_ricalcola=$(prefix+"operd_ricalcola").rylabel({
        left:630,
        top:offsety,
        width:80,
        caption:"Ricalcola",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Ricalcolare gli interessi dal flusso selezionato?",
                confirm:function(){
                    operd_unsaved.visible(0);
                    RYWINZ.modified(formid, 0);
                    winzProgress(formid);
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"pluto_ricalcola",
                            "data":{
                                "PRATICAID":currpraticaid,
                                "FLUSSOID":currflussoid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    gridflussi.query({
                                        ready:function(){
                                            RYWINZ.modified(formid, 0);
                                        }
                                    });
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

    var operd_update=$(prefix+"operd_update").rylabel({
        left:20,
        top:offsety,
        width:80,
        caption:"Salva",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data={};
            var dataflusso=txd_data.text();
            data["PRATICAID"]=currpraticaid;
            data["FLUSSOID"]=currflussoid;
            data["DATA"]=dataflusso;
            if(chk_capitale.value()){
                data["_CAPITALE"]=1;
                if(txd_segno.value()==1)
                    data["CAPITALE"]=-txd_capitale.value();
                else
                    data["CAPITALE"]=txd_capitale.value();
            }
            else{
                data["_CAPITALE"]=0;
            }
            impostaupdate(data, chk_intinc, txd_intinc, "INTINC")
            impostaupdate(data, chk_intpag, txd_intpag, "INTPAG")
            impostaupdate(data, chk_tassoinc, txd_tassoinc, "TASSOINC")
            impostaupdate(data, chk_tassopag, txd_tassopag, "TASSOPAG")
            impostaupdate(data, chk_spreadinc, txd_spreadinc, "SPREADINC")
            impostaupdate(data, chk_spreadpag, txd_spreadpag, "SPREADPAG")
            impostaupdate(data, chk_comminc, txd_comminc, "COMMINC")
            impostaupdate(data, chk_commpag, txd_commpag, "COMMPAG")
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pluto_modifica",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        operd_unsaved.visible(0);
                        RYWINZ.modified(formid, 0);
                        if(v.success>0){
                            if( __(v.params["RELOAD"]).actualInteger() ){
                                gridflussi.query({
                                    ready:function(){
                                        gridflussi.search({
                                                "where": "DATAVAL=[:DATE("+dataflusso+")]"
                                            },
                                            function(d){
                                                try{
                                                    var v=$.parseJSON(d);
                                                    var ind=v[0];
                                                    if(ind>0){
                                                        gridflussi.index(ind);
                                                    }
                                                }
                                                catch(e){
                                                    alert(d);
                                                }
                                            }
                                        );
                                    }
                                });
                            }
                            else{
                                gridflussi.dataload();
                            }
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
    var txd_data=$(prefix+"LB_DATA").rylabel({left:20, top:offsety, caption:"Data"});
    var txd_data=$(prefix+"DATA").rydate({left:100, top:offsety,  width:140, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var txd_segno=$(prefix+"LB_CAPITALE").rylist({left:390, top:offsety, width:100, datum:"D",
        changed:function(o){
            operd_unsaved.visible(1);
        }
    })
    txd_segno.additem({caption:"Erogazione", key:-1})
    txd_segno.additem({caption:"Rimborso", key:1});
    
    var txd_capitale=$(prefix+"CAPITALE").rynumber({left:500, top:offsety, width:140, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_capitale=$(prefix+"CHK_CAPITALE").rycheck({left:660, top:offsety, datum:"D",
        assigned:function(o){
            txd_segno.enabled(o.value());
            txd_capitale.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    $(prefix+"LB_INCASSATO").rylabel({left:100, top:offsety, caption:"Incassato"});
    $(prefix+"LB_PAGATO").rylabel({left:500, top:offsety, caption:"Pagato"});
    
    offsety+=25;
    $(prefix+"LB_INTERESSI").rylabel({left:20, top:offsety, caption:"Interessi"});
    var txd_intinc=$(prefix+"INTINC").rynumber({left:100, top:offsety, width:140, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_intinc=$(prefix+"CHK_INTINC").rycheck({left:260, top:offsety, datum:"D",
        assigned:function(o){
            txd_intinc.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });
    var txd_intpag=$(prefix+"INTPAG").rynumber({left:500, top:offsety, width:140, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_intpag=$(prefix+"CHK_INTPAG").rycheck({left:660, top:offsety, datum:"D",
        assigned:function(o){
            txd_intpag.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    $(prefix+"LB_TASSO").rylabel({left:20, top:offsety, caption:"Tasso"});
    var txd_tassoinc=$(prefix+"TASSOINC").rynumber({left:100, top:offsety, width:140, numdec:4, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_tassoinc=$(prefix+"CHK_TASSOINC").rycheck({left:260, top:offsety, datum:"D",
        assigned:function(o){
            txd_tassoinc.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });
    var txd_tassopag=$(prefix+"TASSOPAG").rynumber({left:500, top:offsety, width:140, numdec:4, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_tassopag=$(prefix+"CHK_TASSOPAG").rycheck({left:660, top:offsety, datum:"D",
        assigned:function(o){
            txd_tassopag.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    $(prefix+"LB_SPREAD").rylabel({left:20, top:offsety, caption:"Spread"});
    var txd_spreadinc=$(prefix+"SPREADINC").rynumber({left:100, top:offsety, width:140, numdec:4, minvalue:-100, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_spreadinc=$(prefix+"CHK_SPREADINC").rycheck({left:260, top:offsety, datum:"D",
        assigned:function(o){
            txd_spreadinc.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });
    var txd_spreadpag=$(prefix+"SPREADPAG").rynumber({left:500, top:offsety, width:140, numdec:4, minvalue:-100, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_spreadpag=$(prefix+"CHK_SPREADPAG").rycheck({left:660, top:offsety, datum:"D",
        assigned:function(o){
            txd_spreadpag.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });

    offsety+=30;
    $(prefix+"LB_COMMISSIONI").rylabel({left:20, top:offsety, caption:"Commiss."});
    var txd_comminc=$(prefix+"COMMINC").rynumber({left:100, top:offsety, width:140, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_comminc=$(prefix+"CHK_COMMINC").rycheck({left:260, top:offsety, datum:"D",
        assigned:function(o){
            txd_comminc.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });
    var txd_commpag=$(prefix+"COMMPAG").rynumber({left:500, top:offsety, width:140, datum:"D",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });
    var chk_commpag=$(prefix+"CHK_COMMPAG").rycheck({left:660, top:offsety, datum:"D",
        assigned:function(o){
            txd_commpag.enabled(o.value());
            operd_unsaved.visible(1);
        }
    });

   // DEFINIZIONE TAB ANTEPRIMA
    offsety=100;
    var lb_preview_context=$(prefix+"preview_context").rylabel({left:20, top:50, caption:""});
    
    $(prefix+"arrowhandle").css({position:"absolute", left:20, top:offsety});

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:50,
        tabs:[
            {title:"Selezione", csize:800},
            {title:"Flussi", csize:800},
            {title:"Anteprima", csize:800}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                RYWINZ.modified(formid, 0);
            }
            if(i==1){
                loadedpraticaDid="";
            }
            else if(i==2){
                if(currpraticaid==loadedpraticaDid){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 1:
                    objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO DETTAGLIO
                    lb_dettaglio_context.caption("Contesto: "+context);
                    RYWINZ.modified(formid, 0);
                    gridflussi.clear();
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"pluto_info",
                            "data":{
                                "PRATICAID":currpraticaid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    currswap=__(v.params["SWAP"]).actualInteger();
                                    currsegno=__(v.params["SEGNO"]).actualInteger();
                                    context=v.params["DESCRIPTION"];
                                    lb_dettaglio_context.caption("Contesto: "+context);
                                    loadedpraticaDid=currpraticaid;
                                    operd_refresh.engage();
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
                    // CARICAMENTO ANTEPRIMA
                    lb_preview_context.caption("Contesto: "+context);
                    qv_contextmanagement(context, {sysid:currpraticaid, table:"QVQUIVERS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_preview_context.caption("Contesto: "+context);
                            setTimeout(
                                function(){
                                    generaanteprima();
                                }, 100
                            );
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
    objtabs.enabled(2, false);
    objtabs.enabled(3, false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            if(currprocessoname!=""){
                RYQUE.query({
                    sql:"SELECT SYSID FROM QW_PROCESSI WHERE [:UPPER(NAME)]='"+currprocessoname+"'",
                    ready:function(v){
                        if(v.length>0){
                            currprocessoid=v[0]["SYSID"];
                            txf_processo.value(currprocessoid);
                            winzClearMess(formid);
                            txf_search.focus();
                        }
                    }
                });
            }
            else{
                txf_processo.value($.cookie(_sessioninfo.environ+"_pluto_processo"), true);
                winzClearMess(formid);
                txf_search.focus();
            }
        }
    );
    function automazione(){
        // MI POSIZIONO SULLA PRATICA DI OPEN
        objgridsel.search({
                "where": "SYSID='"+openpraticaid+"'"
            },
            function(d){
                try{
                    var v=$.parseJSON(d);
                    var ind=v[0];
                    if(ind>0){
                        flagopen=true;
                        objgridsel.index(ind);
                    }
                }
                catch(e){
                    alert(d);
                }
            }
        );
    }
    function generaanteprima(){
        $(prefix+"arrowhandle").html("<i>(generazione in corso...)</i>");
        RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"pluto_preview",
                "data":{
                    "PRATICAID":currpraticaid
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var svil=v.params["PREVIEW"];
                        svil=svil.replace(/&lt;([^<>&]+)&gt;/ig, "<$1>");
                        svil=svil.replace(/&amp;/ig, "&");
                        $(prefix+"arrowhandle").html(svil);
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
    function flussopulisci(){
        // VALORE
        txd_capitale.value(0);
        txd_comminc.value(0);
        txd_commpag.value(0);
        txd_data.value("");
        txd_intinc.value(0);
        txd_intpag.value(0);
        txd_segno.value(1);
        txd_spreadinc.value(0);
        txd_spreadpag.value(0);
        txd_tassoinc.value(0);
        txd_tassopag.value(0);
        chk_capitale.value(0);
        chk_comminc.value(0);
        chk_commpag.value(0);
        chk_intinc.value(0);
        chk_intpag.value(0);
        chk_spreadinc.value(0);
        chk_spreadpag.value(0);
        chk_tassoinc.value(0);
        chk_tassopag.value(0);
        abilitacampi(0);
        operd_unsaved.visible(0);
        RYWINZ.modified(formid, 0);
    }
    function flussocarica(){
        RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"pluto_carica",
                "data":{
                    "PRATICAID":currpraticaid,
                    "FLUSSOID":currflussoid
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var par=v.params;
                        //abilitacampi(1);
                        // DATA VALUTA
                        txd_data.value(par["DATA"]);
                        txd_data.enabled(1);
                        // CAPITALE
                        chk_capitale.enabled(1);
                        if(__(par["_CAPITALE"]).actualInteger()){
                            var cap=parseFloat(par["CAPITALE"]);
                            if( (currswap==0 && cap<0) || (currswap==1 && cap>0) )
                                txd_segno.value(1);
                            else
                                txd_segno.value(2);
                            txd_capitale.value(Math.abs(cap));
                            chk_capitale.value(1, true);
                        }
                        // INTERESSI INCASSATI
                        if(__(par["#INTINC"]).actualInteger()){
                            chk_intinc.enabled(1);
                        }
                        if(__(par["_INTINC"]).actualInteger()){
                            txd_intinc.value(parseFloat(par["INTINC"]));
                            chk_intinc.value(1, true);
                        }
                        // INTERESSI PAGATI
                        if(__(par["#INTPAG"]).actualInteger()){
                            chk_intpag.enabled(1);
                        }
                        if(__(par["_INTPAG"]).actualInteger()){
                            txd_intpag.value(parseFloat(par["INTPAG"]));
                            chk_intpag.value(1, true);
                        }
                        
                        // TASSO INCASSATO
                        if(__(par["#TASSOINC"]).actualInteger()){
                            chk_tassoinc.enabled(1);
                        }
                        if(__(par["_TASSOINC"]).actualInteger()){
                            txd_tassoinc.value(parseFloat(par["TASSOINC"]));
                            chk_tassoinc.value(1, true);
                        }
                        // TASSO PAGATO
                        if(__(par["#TASSOPAG"]).actualInteger()){
                            chk_tassopag.enabled(1);
                        }
                        if(__(par["_TASSOPAG"]).actualInteger()){
                            txd_tassopag.value(parseFloat(par["TASSOPAG"]));
                            chk_tassopag.value(1, true);
                        }

                        // SPREAD INCASSATO
                        if(__(par["#SPREADINC"]).actualInteger()){
                            chk_spreadinc.enabled(1);
                        }
                        if(__(par["_SPREADINC"]).actualInteger()){
                            txd_spreadinc.value(parseFloat(par["SPREADINC"]));
                            chk_spreadinc.value(1, true);
                        }
                        // SPREAD PAGATO
                        if(__(par["#SPREADPAG"]).actualInteger()){
                            chk_spreadpag.enabled(1);
                        }
                        if(__(par["_SPREADPAG"]).actualInteger()){
                            txd_spreadpag.value(parseFloat(par["SPREADPAG"]));
                            chk_spreadpag.value(1, true);
                        }

                        // COMMISSIONI INCASSATE
                        if(__(par["#COMMINC"]).actualInteger()){
                            chk_comminc.enabled(1);
                        }
                        if(__(par["_COMMINC"]).actualInteger()){
                            txd_comminc.value(parseFloat(par["COMMINC"]));
                            chk_comminc.value(1, true);
                        }
                        // COMMISSIONI PAGATE
                        if(__(par["#COMMPAG"]).actualInteger()){
                            chk_commpag.enabled(1);
                        }
                        if(__(par["_COMMPAG"]).actualInteger()){
                            txd_commpag.value(parseFloat(par["COMMPAG"]));
                            chk_commpag.value(1, true);
                        }
                        
                        // ABILITO I BOTTONI
                        operd_ricalcola.enabled(1);
                        operd_update.enabled(1);
                        operd_unsaved.visible(0);
                        RYWINZ.modified(formid, 0);
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
    function abilitacampi(f){
        // ABILITAZIONE
        txd_capitale.enabled(f);
        txd_comminc.enabled(f);
        txd_commpag.enabled(f);
        txd_data.enabled(f);
        txd_intinc.enabled(f);
        txd_intpag.enabled(f);
        txd_segno.enabled(f);
        txd_spreadinc.enabled(f);
        txd_spreadpag.enabled(f);
        txd_tassoinc.enabled(f);
        txd_tassopag.enabled(f);
        chk_capitale.enabled(f);
        chk_comminc.enabled(f);
        chk_commpag.enabled(f);
        chk_intinc.enabled(f);
        chk_intpag.enabled(f);
        chk_spreadinc.enabled(f);
        chk_spreadpag.enabled(f);
        chk_tassoinc.enabled(f);
        chk_tassopag.enabled(f);
    }
    function impostaupdate(data, chk, txd, name){
        if(chk.value()){
            data["_"+name]=1;
            data[name]=txd.value();
        }
        else{
            data["_"+name]=0;
        }
    }
}

