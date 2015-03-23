/****************************************************************************
* Name:            qvproprieta.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvproprieta(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0PROPRIETA00");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var sospendirefresh=false;
    
    // DEFINIZIONE TAB SELEZIONE
    
    var offsety=80;

    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            oper_refresh.engage()
        }
    });
    offsety+=30;

    $(prefix+"lbf_classe").rylabel({left:20, top:offsety, caption:"Classe"});
    var txf_classe=$(prefix+"txf_classe").ryhelper({left:100, top:offsety, width:200, 
        formid:formid, table:"QW_CLASSIPROPRIETA", title:"Classi", multiple:false,
        open:function(o){
            o.where("");
        },
        onselect:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        },
        clear:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:630,
        top:80,
        width:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            if(!sospendirefresh){
                var q="";
                var t=_likeescapize(txf_search.value());
                var classeid=txf_classe.value();

                if(t!=""){
                    if(q!=""){q+=" AND "}
                    q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                }
                if(classeid!=""){
                    if(q!=""){q+=" AND "}
                    q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+classeid+"')";
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
            txf_classe.clear();
            sospendirefresh=false;
            setTimeout(function(){oper_refresh.engage()}, 100);
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
        from:"QW_PROPRIETA",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Rag. soc.",width:200},
            {id:"CITTA", caption:"Comune", width:200},
            {id:"PROVINCIA", caption:"Prov.", width:40},
            {id:"INDIRIZZO", caption:"Indirizzo", width:200},
            {id:"CIVICO", caption:"Civ.", width:60},
            {id:"EMAIL", caption:"Email", width:200},
            {id:"TELEFONO", caption:"Telefono", width:110}
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
                oper_print.enabled(o.isselected());
                oper_delete.enabled(o.isselected());
            }
            context="";
        },
        selchange:function(o, i){
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_print.enabled(1);
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
        },
        before:function(o, d){
            for(var i in d){
                if(d[i]["EMAIL"]!=""){
                    d[i]["EMAIL"]="<a href='mailto:"+d[i]["EMAIL"]+"' style='cursor:pointer;color:navy;'>"+d[i]["EMAIL"]+"</a>";
                }
            }
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
            var data = new Object();
            data["DESCRIPTION"]="(nuova azienda)";
            data["TYPOLOGYID"]=currtypologyid;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
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
        left:220,
        top:offsety,
        width:120,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_objects.php")
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:420,
        top:offsety,
        width:120,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "objects");
        }
    });

    offsety+=50;
    var oper_import=$(prefix+"oper_import").rylabel({
        left:20,
        top:offsety,
        width:120,
        caption:"Importa ODS",
        button:true,
        click:function(o){
            qv_importODS(formid,
                {
                    "columns":[
                        {"id":"DESCRIPTION", "caption":"Rag. Soc."},
                        {"id":"INDIRIZZO", "caption":"Indirizzo"},
                        {"id":"CIVICO", "caption":"Civico"},
                        {"id":"CITTA", "caption":"Città"},
                        {"id":"CAP", "caption":"CAP"},
                        {"id":"PROVINCIA", "caption":"Provincia"},
                        {"id":"REGIONE", "caption":"Regione"},
                        {"id":"NAZIONE", "caption":"Nazione"},
                        {"id":"CODFISC", "caption":"C.FISC"},
                        {"id":"PIVA", "caption":"P.IVA"},
                        {"id":"EMAIL", "caption":"Email"},
                        {"id":"TELEFONO", "caption":"Telefono"},
                        {"id":"CELLULARE", "caption":"Cellulare"},
                        {"id":"INDIRIZZOOPER", "caption":"Indirizzo oper."},
                        {"id":"CIVICOOPER", "caption":"Civico oper."},
                        {"id":"CITTAOPER", "caption":"Città oper."},
                        {"id":"CAPOPER", "caption":"CAP oper."},
                        {"id":"PROVINCIAOPER", "caption":"Provincia oper."},
                        {"id":"REGIONEOPER", "caption":"Regione oper."},
                        {"id":"NAZIONEOPER", "caption":"Nazione oper."},
                        {"id":"CODFISC", "caption":"C.FISC"},
                        {"id":"PIVA", "caption":"P.IVA"},
                        {"id":"EMAIL", "caption":"Email"},
                        {"id":"TELEFONO", "caption":"Telefono"},
                        {"id":"CELLULARE", "caption":"Cellulare"},
                        {"id":"ATECOSEZIONE", "caption":"Sezione AT.ECO."},
                        {"id":"ATECO", "caption":"Codice AT.ECO."},
                        {"id":"CCIAA", "caption":"CCIAA"},
                        {"id":"TAG", "caption":"Marche"},
                        {"id":"REGISTRY", "caption":"Note"}
                    ],
                    "ready":function(d){
                        winzProgress(formid);
                        var stats=[];
                        for(var i in d){
                            // ANALIZZO I DATI PER VEDERE SE CE NE SONO DI IMPOSTATI
                            var ins=false;
                            for(var c in d[i]){
                                if(d[i][c]!=""){
                                    ins=true;
                                    break;
                                }
                            }
                            if(ins){
                                var data={};
                                var descr="";
                                var indirizzo="";
                                var civico="";
                                var indirizzooper="";
                                var civicooper="";
                                var atecosez="";
                                var atecocod="";
                                data["TYPOLOGYID"]=currtypologyid;
                                data["CONFLICT"]="SKIP";
                                for(var c in d[i]){
                                    data[c]=d[i][c];
                                    switch(c){
                                    case "DESCRIPTION":
                                        descr=data[c];
                                        break;
                                    case "INDIRIZZO":
                                        indirizzo=data[c];
                                        break;
                                    case "CIVICO":
                                        civico=data[c];
                                        break;
                                    case "INDIRIZZOOPER":
                                        indirizzooper=data[c];
                                        break;
                                    case "CIVICOOPER":
                                        civicooper=data[c];
                                        break;
                                    case "ATECOSEZIONE":
                                        atecosez=data[c].substr(0, 1);
                                        break;
                                    case "ATECO":
                                        atecocod=data[c];
                                        if(atecocod.substr(0, 1).match(/[A-Z]/i)){
                                            atecosez=atecocod.substr(0, 1);
                                            atecocod=atecocod.substr(1);
                                            if(atecocod.substr(0, 1)=="."){
                                                atecocod=atecocod.substr(1);
                                            }
                                        }
                                        break;
                                    }
                                }

                                // GESTIONE DESCRIZIONE
                                if(descr==""){
                                    descr="(nuova azienda)";
                                }
                                data["DESCRIPTION"]=descr;

                                // GESTIONE CIVICO
                                if(civico==""){
                                    if(civico=indirizzo.match(/\d+\w+/)){
                                        civico=civico[0];
                                        indirizzo=indirizzo.replace(civico, "");
                                        data["INDIRIZZO"]=indirizzo;
                                        data["CIVICO"]=civico;
                                    }
                                }
                                if(civicooper==""){
                                    if(civicooper=indirizzooper.match(/\d+\w+/)){
                                        civicooper=civicooper[0];
                                        indirizzooper=indirizzooper.replace(civico, "");
                                        data["INDIRIZZOOPER"]=indirizzooper;
                                        data["CIVICOOPER"]=civicooper;
                                    }
                                }
                                
                                // GESTIONE ATECO
                                data["ATECOSEZIONE"]=atecosez;
                                data["ATECO"]=atecocod;

                                // INSERIMENTO ISTRUZIONE
                                stats[i]={
                                    "function":"objects_insert",
                                    "data":data
                                };
                            }
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
                                    objgridsel.refresh();
                                    winzTimeoutMess(formid, v.success, v.message);
                                }
                                catch(e){
                                    winzClearMess(formid);
                                    alert(d);
                                }
                            }
                        );
                    }
                }
            );
        }
    });
    oper_import.visible(_sessioninfo.admin);

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Rag. Sociale"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=50;
    
    // SWITCH LEGALE-OPERATIVA
    
    $(prefix+"switch_legale").css({position:"absolute", left:2, top:offsety, width:200, height:24, "border-width":"1px", "border-style":"solid", "text-align":"center", "padding-top":"5px", "cursor":"pointer"});
    $(prefix+"switch_oper").css({position:"absolute", left:205, top:offsety, width:200, height:24, "border-width":"1px", "border-style":"solid", "text-align":"center", "padding-top":"5px", "cursor":"pointer"});
    
    $(prefix+"switch_legale").click(
        function(){
            switch_sedi(0);
        }
    );
    $(prefix+"switch_oper").click(
        function(){
            switch_sedi(1);
        }
    );
    offsety+=30;
    
    var rely=20;
    
    // INIZIO SEDE LEGALE
    $(prefix+"SEDE_LEGALE").css({position:"absolute", left:2, top:offsety, width:750, height:210, border:"1px solid silver"});
    
    $(prefix+"LB_INDIRIZZO").rylabel({left:20, top:rely, caption:"Indirizzo"});
    $(prefix+"INDIRIZZO").rytext({left:120, top:rely, width:240, maxlen:100, datum:"C", tag:"INDIRIZZO"});
    $(prefix+"CIVICO").rytext({left:380, top:rely, width:40, datum:"C", tag:"CIVICO"});
    rely+=30;
    
    $(prefix+"LB_CAP").rylabel({left:20, top:rely, caption:"C.A.P."});
    $(prefix+"CAP").rytext({left:120, top:rely, width:60, maxlen:20, datum:"C", tag:"CAP"});
    
    $(prefix+"LB_CITTA").rylabel({left:200, top:rely, caption:"Città"});
    $(prefix+"CITTA").rytext({left:240, top:rely, width:250, maxlen:50, datum:"C", tag:"CITTA"});
    
    $(prefix+"LB_PROVINCIA").rylabel({left:520, top:rely, caption:"Provincia"});
    $(prefix+"PROVINCIA").rytext({left:590, top:rely, width:40, maxlen:30, datum:"C", tag:"PROVINCIA"});
    
    $(prefix+"oper_cerca").rylabel({
        left:640,
        top:rely,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            qv_geography(formid,
                {
                    "type":"comuni",
                    "onselect":function(d){
                        globalobjs[formid+"CAP"].value(d["CAP"], true);
                        globalobjs[formid+"CITTA"].value(d["DESCRIPTION"], true);
                        globalobjs[formid+"PROVINCIA"].value(d["SIGLA"], true);
                        globalobjs[formid+"REGIONE"].value(d["REGIONE"], true);
                        globalobjs[formid+"NAZIONE"].value("Italia", true);
                    }
                }
            );
        }
    });
    rely+=30;
    
    $(prefix+"LB_REGIONE").rylabel({left:20, top:rely, caption:"Regione"});
    $(prefix+"REGIONE").rytext({left:120, top:rely, width:200, datum:"C", tag:"REGIONE"});
    $(prefix+"LB_NAZIONE").rylabel({left:360, top:rely, caption:"Nazione"});
    $(prefix+"NAZIONE").rytext({left:430, top:rely, width:200, datum:"C", tag:"NAZIONE"});
    rely+=30;
    
    $(prefix+"LB_TELEFONO").rylabel({left:20, top:rely, caption:"Telefono"});
    $(prefix+"TELEFONO").rytext({left:120, top:rely, width:200, maxlen:30, datum:"C", tag:"TELEFONO"});
    $(prefix+"LB_CELLULARE").rylabel({left:360, top:rely, caption:"Cellulare"});
    $(prefix+"CELLULARE").rytext({left:430, top:rely, width:200, maxlen:30, datum:"C", tag:"CELLULARE"});
    rely+=30;

    $(prefix+"LB_FAX").rylabel({left:20, top:rely, caption:"Fax"});
    $(prefix+"FAX").rytext({left:120, top:rely, width:200, maxlen:30, datum:"C", tag:"FAX"});
    $(prefix+"LB_EMAIL").rylabel({left:360, top:rely, caption:"Email"});
    $(prefix+"EMAIL").rytext({left:430, top:rely, width:200, maxlen:50, datum:"C", tag:"EMAIL"});
    rely+=30;
    
    $(prefix+"LB_CONTATTOID").rylabel({left:20, top:rely, caption:"Contatto"});
    $(prefix+"CONTATTOID").ryhelper({
        left:120, top:rely, width:200, datum:"C", tag:"CONTATTOID", formid:formid, table:"QW_PERSONE", title:"Scelta contatto",
        open:function(o){
            o.where("");
        }
    });
    rely+=30;
    
    // FINE SEDE LEGALE

    rely=20;
    
    // INIZIO SEDE OPERATIVA
    $(prefix+"SEDE_OPERATIVA").css({position:"absolute", left:2, top:offsety, width:750, height:210, border:"1px solid silver"});
    
    $(prefix+"LB_INDIRIZZOOPER").rylabel({left:20, top:rely, caption:"Indirizzo"});
    $(prefix+"INDIRIZZOOPER").rytext({left:120, top:rely, width:240, maxlen:100, datum:"C", tag:"INDIRIZZOOPER"});
    $(prefix+"CIVICOOPER").rytext({left:380, top:rely, width:40, datum:"C", tag:"CIVICOOPER"});
    rely+=30;
    
    $(prefix+"LB_CAPOPER").rylabel({left:20, top:rely, caption:"C.A.P."});
    $(prefix+"CAPOPER").rytext({left:120, top:rely, width:60, maxlen:20, datum:"C", tag:"CAPOPER"});
    
    $(prefix+"LB_CITTAOPER").rylabel({left:200, top:rely, caption:"Città"});
    $(prefix+"CITTAOPER").rytext({left:240, top:rely, width:250, maxlen:50, datum:"C", tag:"CITTAOPER"});
    
    $(prefix+"LB_PROVINCIAOPER").rylabel({left:520, top:rely, caption:"Provincia"});
    $(prefix+"PROVINCIAOPER").rytext({left:590, top:rely, width:40, maxlen:30, datum:"C", tag:"PROVINCIAOPER"});
    
    $(prefix+"oper_cercaoper").rylabel({
        left:640,
        top:rely,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            qv_geography(formid,
                {
                    "type":"comuni",
                    "onselect":function(d){
                        globalobjs[formid+"CAPOPER"].value(d["CAP"], true);
                        globalobjs[formid+"CITTAOPER"].value(d["DESCRIPTION"], true);
                        globalobjs[formid+"PROVINCIAOPER"].value(d["SIGLA"], true);
                        globalobjs[formid+"REGIONEOPER"].value(d["REGIONE"], true);
                        globalobjs[formid+"NAZIONEOPER"].value("Italia", true);
                    }
                }
            );
        }
    });
    rely+=30;
    
    $(prefix+"LB_REGIONEOPER").rylabel({left:20, top:rely, caption:"Regione"});
    $(prefix+"REGIONEOPER").rytext({left:120, top:rely, width:200, datum:"C", tag:"REGIONEOPER"});
    $(prefix+"LB_NAZIONEOPER").rylabel({left:360, top:rely, caption:"Nazione"});
    $(prefix+"NAZIONEOPER").rytext({left:430, top:rely, width:200, datum:"C", tag:"NAZIONEOPER"});
    rely+=30;
    
    $(prefix+"LB_TELEFONOOPER").rylabel({left:20, top:rely, caption:"Telefono"});
    $(prefix+"TELEFONOOPER").rytext({left:120, top:rely, width:200, maxlen:30, datum:"C", tag:"TELEFONOOPER"});
    $(prefix+"LB_CELLULAREOPER").rylabel({left:360, top:rely, caption:"Cellulare"});
    $(prefix+"CELLULAREOPER").rytext({left:430, top:rely, width:200, maxlen:30, datum:"C", tag:"CELLULAREOPER"});
    rely+=30;

    $(prefix+"LB_FAXOPER").rylabel({left:20, top:rely, caption:"Fax"});
    $(prefix+"FAXOPER").rytext({left:120, top:rely, width:200, maxlen:30, datum:"C", tag:"FAXOPER"});
    $(prefix+"LB_EMAILOPER").rylabel({left:360, top:rely, caption:"Email"});
    $(prefix+"EMAILOPER").rytext({left:430, top:rely, width:200, maxlen:50, datum:"C", tag:"EMAILOPER"});
    rely+=30;
    
    $(prefix+"LB_CONTATTOIDOPER").rylabel({left:20, top:rely, caption:"Contatto"});
    $(prefix+"CONTATTOIDOPER").ryhelper({
        left:120, top:rely, width:200, datum:"C", tag:"CONTATTOIDOPER", formid:formid, table:"QW_PERSONE", title:"Scelta contatto",
        open:function(o){
            o.where("");
        }
    });
    rely+=30;
    
    // FINE SEDE OPERATIVA
    
    switch_sedi(0);
    
    offsety+=240;
    var classiy=offsety;

    $(prefix+"LB_CODFISC").rylabel({left:20, top:offsety, caption:"Cod. Fisc."});
    $(prefix+"CODFISC").rytext({left:120, top:offsety, width:200, datum:"C", tag:"CODFISC"});
    offsety+=30;
    
    $(prefix+"LB_PIVA").rylabel({left:20, top:offsety, caption:"Partita IVA"});
    $(prefix+"PIVA").rytext({left:120, top:offsety, width:200, maxlen:30, datum:"C", tag:"PIVA"});
    offsety+=30;
    
    $(prefix+"LB_ATECO").rylabel({left:20, top:offsety, caption:"AT.ECO."});
    var atecosez=$(prefix+"ATECOSEZIONE").rytext({left:120, top:offsety, width:30, maxlen:1, datum:"C", tag:"ATECOSEZIONE"});
    var atecocod=$(prefix+"ATECO").rytext({left:160, top:offsety, width:160, maxlen:30, datum:"C", tag:"ATECO"});
    $(prefix+"oper_cercaateco").rylabel({
        left:330,
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
    offsety+=30;
    
    $(prefix+"LB_CCIAA").rylabel({left:20, top:offsety, caption:"C.C.I.A.A."});
    $(prefix+"CCIAA").rytext({left:120, top:offsety, width:200, datum:"C", tag:"CCIAA"});
    offsety+=30;

    $(prefix+"LB_BEGINTIME").rylabel({left:20, top:offsety, caption:"Iscrizione"});
    $(prefix+"BEGINTIME").rydate({left:120, top:offsety, width:110, datum:"C", tag:"BEGINTIME"});
    offsety+=30;
    
    $(prefix+"LB_ENDTIME").rylabel({left:20, top:offsety, caption:"Cessazione"});
    $(prefix+"ENDTIME").rydate({left:120, top:offsety, width:110, defaultvalue:"99991231", datum:"C", tag:"ENDTIME"});
    offsety+=30;
    
    $(prefix+"LB_CONTODEFAULTID").rylabel({left:20, top:offsety, caption:"Conto predef."});
    $(prefix+"CONTODEFAULTID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"CONTODEFAULTID", formid:formid, table:"QW_CONTI", title:"Scelta conto predefinito",
        open:function(o){
            o.where("SYSID IN (SELECT SYSID FROM QW_CONTI WHERE TITOLAREID='"+currsysid+"')");
        }
    });
    $(prefix+"LB_DIMENSIONE").rylabel({left:450, top:offsety, caption:"Dimensione"});
    var aziendedim=$(prefix+"DIMENSIONE").rylist({left:540, top:offsety, width:200, datum:"C", tag:"DIMENSIONE"});
    aziendedim
        .additem({caption:"", key:"0"})
        .additem({caption:"Piccola", key:"1"})
        .additem({caption:"Media", key:"2"})
        .additem({caption:"Grande", key:"3"});
    offsety+=30;
    
    $(prefix+"LB_TITOLAREID").rylabel({left:20, top:offsety, caption:"Titolare"});
    $(prefix+"TITOLAREID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"TITOLAREID", formid:formid, table:"QW_PERSONE", title:"Titolare",
        open:function(o){
            o.where("");
        }
    });
    $(prefix+"LB_CONTROLLANTEID").rylabel({left:450, top:offsety, caption:"Controllante"});
    $(prefix+"CONTROLLANTEID").ryhelper({
        left:540, top:offsety, width:200, datum:"C", tag:"CONTROLLANTEID", formid:formid, table:"QW_PROPRIETA", title:"Scelta controllante",
        open:function(o){
            o.where("SYSID<>'"+currsysid+"'");
        }
    });
    offsety+=30;
    
    var checky=offsety;
    
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Riferimento"});
    $(prefix+"REFERENCE").rytext({left:120, top:offsety, width:280, datum:"C", tag:"REFERENCE"});
    offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:280, datum:"C", tag:"TAG"});
    offsety+=30;

    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});
    offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:720, height:400, datum:"C", tag:"REGISTRY"});
    
    var objclassi=$(prefix+"CLASSI").ryselections({"left":500, "top":classiy, "height":140, 
        "title":"Classi di appartenenza",
        "titlecode":"BELONGING_CLASS",
        "formid":formid, 
        "table":"QW_CLASSIPROPRIETA", 
        "where":"",
        "upward":1,
        "parenttable":"QVOBJECTS", 
        "parentfield":"SYSID",
        "selectedtable":"QVOBJECTS"
    });
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:670,
        top:60,
        width:80,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=qv_mask2object(formid, "C", currsysid);
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
                        if(v.success>0){ RYWINZ.modified(formid, 0) }
                        objgridsel.dataload();
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    if(done!=missing){done()}
                }
            );
        }
    });

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_PROPRIETA");

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
                    qv_maskclear(formid, "C");
                    objclassi.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_PROPRIETA WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            qv_object2mask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            objclassi.parentid(currsysid,
                                function(){
                                    castFocus(prefix+"DESCRIPTION");
                                }
                            );
                        }
                    });
                    break;
                case 3:
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
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            RYWINZ.loadmodule("ateco.js", _appsURL+"corsaro/_javascript/ateco.js",
                function(){
                    RYQUE.query({
                        sql:"SELECT DESCRIPTION, AUXAMOUNT FROM QW_AZIENDEDIM ORDER BY AUXAMOUNT",
                        ready:function(v){
                            if(v.length>0){
                                aziendedim.clear();
                                aziendedim.additem({caption:"", key:"0"})
                                for(var i in v){
                                    aziendedim.additem({caption:v[i]["DESCRIPTION"], key:v[i]["AUXAMOUNT"]});
                                }
                            }
                            oper_refresh.engage(
                                function(){
                                    winzClearMess(formid);
                                    txf_search.focus();
                                }
                            ) 
                        }
                    });
                }
            );
        }
    );
    function switch_sedi(s){
        switch(s){
        case 0:
            $(prefix+"switch_legale").css({"border-color":"silver silver #F8F8F8 silver", "background":"#F8F8F8", "font-weight":"bold"});
            $(prefix+"switch_oper").css({"border-color":"silver silver silver silver", "background":"#F0F0F0", "font-weight":"normal"});
            $(prefix+"SEDE_LEGALE").css({"display":"block"});
            $(prefix+"SEDE_OPERATIVA").css({"display":"none"});
            break;
        case 1:
            $(prefix+"switch_legale").css({"border-color":"silver silver silver silver", "background":"#F0F0F0", "font-weight":"normal"});
            $(prefix+"switch_oper").css({"border-color":"silver silver #F8F8F8 silver", "background":"#F8F8F8", "font-weight":"bold"});
            $(prefix+"SEDE_LEGALE").css({"display":"none"});
            $(prefix+"SEDE_OPERATIVA").css({"display":"block"});
            break;
        }
    }
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xfocus:"DESCRIPTION", xengage:oper_contextengage, files:3} );
}

