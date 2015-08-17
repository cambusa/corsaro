/****************************************************************************
* Name:            qvbandi.js                                               *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvbandi(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0BANDI000000");
    var context="";
    var bbl_context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var sospendirefresh=false;
    
    var currsetforme="";
    var currsetcriteri="";
    var currsetdimen="";
    var currsetoggetti="";
    var currsetspese="";
    var currsetcontr="";
    
    // DEFINIZIONE TAB SELEZIONE

    var offsety=80;
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_tipologia").rylabel({left:20, top:offsety, caption:"Tipologia"});
    var txf_tipologia=$(prefix+"txf_tipologia").ryhelper({left:100, top:offsety, width:200, 
        formid:formid, table:"QW_BANDITIPOL", title:"Tipologia", multiple:false,
        open:function(o){
            o.where("");
        },
        onselect:function(){
            refreshselection();
        },
        clear:function(){
            refreshselection();
        }
    });
    
    $(prefix+"lbf_soloattivi").rylabel({left:450, top:offsety, caption:"Solo attivi"});
    var chk_soloattivi=$(prefix+"chk_soloattivi").rycheck({left:532, top:offsety,
        assigned:function(){
            refreshselection();
        }
    });
    chk_soloattivi.value(1);
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:630,
        top:80,
        width:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            var tipologiaid=txf_tipologia.value();
            var attivi=chk_soloattivi.value();

            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(REFERENCE)] LIKE '[=REFERENCE]%' )";
            }
            if(tipologiaid!=""){
                if(q!=""){q+=" AND "}
                q+="TIPOBANDOID='"+tipologiaid+"'";
            }
            if(attivi){
                if(q!=""){q+=" AND "}
                q+="STATO=0 AND ENDTIME>[:TODAY()]";
            }
            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "REFERENCE":t
                },
                ready:function(){
                    if(done!=missing){done()}
                }
            });
        }
    });
    var oper_reset=$(prefix+"oper_reset").rylabel({
        left:630,
        top:110,
        width:80,
        caption:"Pulisci",
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_tipologia.clear();
            chk_soloattivi.value(1);
            sospendirefresh=false;
            refreshselection();
        }
    });
    offsety+=35;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        maxwidth:-1,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_BANDI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:250},
            {id:"BEGINTIME", caption:"Apertura", width:100, type:"/"},
            {id:"ENDTIME", caption:"Chiusura", width:100, type:"/"},
            {id:"STATO", caption:"St.", width:30, type:"?"},
            {id:"CITTA", caption:"Comune", width:200},
            {id:"PROVINCIA", caption:"Pr", width:40},
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                if(currsysid!=""){
                    objtabs.enabled(2,false);
                    objtabs.enabled(3,false);
                }
                currsysid="";
                oper_delete.enabled(o.isselected());
            }
            context="";
        },
        changesel:function(o){
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_delete.enabled(1);
            if(currsysid==""){
                currsysid=d;
                objtabs.enabled(2,true);
                objtabs.enabled(3,true);
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

    offsety=440;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        width:120,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"bandi_insert",
                    "data":{}
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
        left:590,
        top:offsety,
        width:120,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "objects");
        }
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;

    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:100, top:offsety, width:500, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=40;

    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Codice"});
    $(prefix+"REFERENCE").rytext({left:100, top:offsety, width:150, maxlen:50, datum:"C", tag:"REFERENCE"});

    $(prefix+"LB_TIPOBANDOID").rylabel({left:280, top:offsety, caption:"Tipologia"});
    $(prefix+"TIPOBANDOID").ryhelper({
        left:350, top:offsety, width:250, datum:"C", tag:"TIPOBANDOID", formid:formid, table:"QW_BANDITIPOL", title:"Scelta tipologia",
        open:function(o){
            o.where("");
        }
    });
    
    offsety+=40;
    
    $(prefix+"LB_EMITTENTE").rylabel({left:20, top:offsety, caption:"Emittente"});
    $(prefix+"EMITTENTE").rytext({left:100, top:offsety, width:150, maxlen:50, datum:"C", tag:"EMITTENTE"});
    
    $(prefix+"LB_GESTORE").rylabel({left:280, top:offsety, caption:"Gestore"});
    $(prefix+"GESTORE").rytext({left:350, top:offsety, width:150, maxlen:50, datum:"C", tag:"GESTORE"});
    offsety+=40;

    $(prefix+"LB_BEGINTIME").rylabel({left:20, top:offsety, caption:"Apertura"});
    $(prefix+"BEGINTIME").rydate({left:100, top:offsety, width:150, datum:"C", tag:"BEGINTIME"});
    
    $(prefix+"LB_ENDTIME").rylabel({left:280, top:offsety, caption:"Chiusura"});
    $(prefix+"ENDTIME").rydate({left:350, top:offsety, width:150, defaultvalue:"99991231", datum:"C", tag:"ENDTIME"});
    
    $(prefix+"LB_STATO").rylabel({left:530, top:offsety, caption:"Stato"});
    $(prefix+"STATO").rylist({left:580, top:offsety, width:120, datum:"C", tag:"STATO"})
        .additem({caption:"Attivo", key:"0"})
        .additem({caption:"Chiuso", key:"1"});
    offsety+=40;
    
    $(prefix+"LB_ATECO").rylabel({left:20, top:offsety, caption:"AT.ECO."});
    var atecosez=$(prefix+"ATECOSEZIONE").rytext({left:100, top:offsety, width:30, maxlen:1, datum:"C", tag:"ATECOSEZIONE"});
    var atecocod=$(prefix+"ATECO").rytext({left:140, top:offsety, width:160, maxlen:30, datum:"C", tag:"ATECO"});
    $(prefix+"oper_cercaateco").rylabel({
        left:310,
        top:offsety,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            qv_helpateco(formid,
                {
                    "sezione":atecosez.value(),
                    "codice":atecocod.value(),
                    "onselect":function(d){
                        globalobjs[formid+"ATECOSEZIONE"].value(d["SEZIONE"], true);
                        globalobjs[formid+"ATECO"].value(d["CODICE"], true);
                    }
                }
            );
        }
    });
    offsety+=40;
    
    $(prefix+"LB_REGIONE").rylabel({left:20, top:offsety, caption:"Regione"});
    $(prefix+"REGIONE").rytext({left:100, top:offsety, width:150, maxlen:50, datum:"C", tag:"REGIONE"});
    
    $(prefix+"LB_PROVINCIA").rylabel({left:280, top:offsety, caption:"Pr."});
    $(prefix+"PROVINCIA").rytext({left:310, top:offsety, width:40, maxlen:30, datum:"C", tag:"PROVINCIA"});

    $(prefix+"LB_CITTA").rylabel({left:380, top:offsety, caption:"Comune"});
    $(prefix+"CITTA").rytext({left:440, top:offsety, width:160, maxlen:50, datum:"C", tag:"CITTA"});
    
    $(prefix+"oper_cerca").rylabel({
        left:610,
        top:offsety,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            winzGeography(formid,
                {
                    "type":"comuni",
                    "onselect":function(d){
                        globalobjs[formid+"REGIONE"].value(d["REGIONE"], true);
                        globalobjs[formid+"PROVINCIA"].value(d["SIGLA"], true);
                        globalobjs[formid+"CITTA"].value(d["DESCRIPTION"], true);
                    }
                }
            );
        }
    });
    offsety+=50;
    
    var objforme=$(prefix+"SETFORMAGIURIDICA").ryselections({"left":20, "top":offsety, "width":330, "height":140, 
        "title":"Forma giuridica",
        "formid":formid, 
        "subid":"F",
        "table":"QW_BANDIFORMA", 
        "where":"",
        "upward":1,
        "parenttable":"QW_BANDI", 
        "parentfield":"SETFORMAGIURIDICA",
        "selectedtable":"QVOBJECTS"
    });
    
    var objcriteri=$(prefix+"SETCRITERIESCLUSIVI").ryselections({"left":390, "top":offsety, "width":330, "height":140, 
        "title":"Criterio esclusivo",
        "formid":formid, 
        "subid":"C",
        "table":"QW_BANDICRIT", 
        "where":"",
        "upward":1,
        "parenttable":"QW_BANDI", 
        "parentfield":"SETCRITERIESCLUSIVI",
        "selectedtable":"QVOBJECTS"
    });
    
    offsety+=200;
    
    var objdimen=$(prefix+"SETDIMENSIONE").ryselections({"left":20, "top":offsety, "width":330, "height":140, 
        "title":"Deminsione azienda",
        "formid":formid, 
        "subid":"D",
        "table":"QW_AZIENDEDIM", 
        "where":"",
        "upward":1,
        "parenttable":"QW_BANDI", 
        "parentfield":"SETDIMENSIONE",
        "selectedtable":"QVOBJECTS"
    });
    
    var objoggetti=$(prefix+"SETOGGETTO").ryselections({"left":390, "top":offsety, "width":330, "height":140, 
        "title":"Oggetto",
        "formid":formid, 
        "subid":"O",
        "table":"QW_BANDIOGGET", 
        "where":"",
        "upward":1,
        "parenttable":"QW_BANDI", 
        "parentfield":"SETOGGETTO",
        "selectedtable":"QVOBJECTS"
    });
    
    offsety+=200;
    
    var objspese=$(prefix+"SETSPESEAMMISSIBILI").ryselections({"left":20, "top":offsety, "width":330, "height":140, 
        "title":"Spesa ammissibile",
        "formid":formid, 
        "subid":"S",
        "table":"QW_BANDISPESE", 
        "where":"",
        "upward":1,
        "parenttable":"QW_BANDI", 
        "parentfield":"SETSPESEAMMISSIBILI",
        "selectedtable":"QVOBJECTS"
    });
    
    var objcontr=$(prefix+"SETTIPICONTRIBUTO").ryselections({"left":390, "top":offsety, "width":330, "height":140, 
        "title":"Tipo contributo",
        "formid":formid, 
        "subid":"T",
        "table":"QW_BANDICONTR", 
        "where":"",
        "upward":1,
        "parenttable":"QW_BANDI", 
        "parentfield":"SETTIPICONTRIBUTO",
        "selectedtable":"QVOBJECTS"
    });
    
    offsety+=200;
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:640,
        top:60,
        width:70,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            if(txdescr.value()=="(nuova persona)" || txdescr.value()==""){
                if(txnome.value()!="" || txcognome.value()!=""){
                    txdescr.value( txnome.value() + " " + txcognome.value() );
                }
            }
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
    
    $(prefix+"previewinner").addClass("winz-zoom75");
    $(prefix+"preview").css({"position":"absolute", "left":740, "top":60, "width":600, "border-left":"1px solid red", "padding-left":8, "display":"none"});

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", 
        {
            "merge":"QW_BANDI",
            "update":function(){
                refreshpreview();
            }
        }
    );

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Documenti"}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedsysid="";
                    }
                });
            }
            if(i==1){
                loadedsysid="";
            }
            else if(i==2){
                if(currsysid==loadedsysid){
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
                    RYWINZ.MaskClear(formid, "C");
                    objforme.clear();
                    objcriteri.clear();
                    objdimen.clear();
                    objoggetti.clear();
                    objspese.clear();
                    objcontr.clear();
                    $(prefix+"previewinner").html("");
                    $(prefix+"preview").hide();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_BANDI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            
                            currsetforme=v[0]["SETFORMAGIURIDICA"];
                            currsetcriteri=v[0]["SETCRITERIESCLUSIVI"];
                            currsetdimen=v[0]["SETDIMENSIONE"];
                            currsetoggetti=v[0]["SETOGGETTO"];
                            currsetspese=v[0]["SETSPESEAMMISSIBILI"];
                            currsetcontr=v[0]["SETTIPICONTRIBUTO"];
                            
                            objforme.parentid(currsetforme,
                                function(){
                                    objcriteri.parentid(currsetcriteri,
                                        function(){
                                            objdimen.parentid(currsetdimen,
                                                function(){
                                                    objoggetti.parentid(currsetoggetti,
                                                        function(){
                                                            objspese.parentid(currsetspese,
                                                                function(){
                                                                    objcontr.parentid(currsetcontr,
                                                                        function(){
                                                                            refreshpreview();
                                                                            castFocus(prefix+"DESCRIPTION");
                                                                        }
                                                                    );
                                                                }
                                                            );
                                                        }
                                                    );
                                                }
                                            );
                                        }
                                    );
                                }
                            );
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, bbl_context.replace("{1}", context), currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            filemanager.caption(bbl_context.replace("{1}", context));
                        }
                    });
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            RYWINZ.loadmodule("ateco.js", _systeminfo.relative.apps+"corsaro/_javascript/ateco.js",
                function(){
                    oper_refresh.engage(
                        function(){
                            winzClearMess(formid);
                            txf_search.focus();
                        }
                    );
                }
            );
        }
    );
    function refreshselection(){
        if(!sospendirefresh){
            setTimeout(
                function(){
                    oper_refresh.engage();
                }
            , 100);
        }
    }
    function refreshpreview(){
        RYQUE.query({
            sql:"SELECT QVFILES.DESCRIPTION AS DESCRIPTION, QVFILES.REGISTRY AS REGISTRY FROM QVTABLEFILE INNER JOIN QVFILES ON QVFILES.SYSID=QVTABLEFILE.FILEID WHERE QVFILES.IMPORTNAME='' AND QVTABLEFILE.RECORDID='"+currsysid+"' ORDER BY QVTABLEFILE.SORTER",
            ready:function(v){
                var h="";
                if(v.length>0){
                    for(var i in v){
                        h+="<div style='margin-bottom:4px'>";
                        h+="<h2>"+v[i]["DESCRIPTION"]+"</h2>";
                        h+="</div>";
                        h+="<div style='margin-bottom:20px'>";
                        h+=v[i]["REGISTRY"];
                        h+="</div>";
                    }
                    h=h.replace(/<script[^\x00]+<\/script>/ig, "");
                    h=h.replace(/<iframe[^\x00]+<\/iframe>/ig, "");
                    $(prefix+"previewinner").html(h);
                    $(prefix+"preview").show();
                }
                else{
                    $(prefix+"previewinner").html(h);
                    $(prefix+"preview").hide();
                }
            }
        });
    }
}

