/****************************************************************************
* Name:            qvvendite.js                                             *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvvendite(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);
    
    // VARIABILI PRATICA
    var currpraticaid="";
    var currstatoid="";
    var currprocessoname="_PROCVENDITE";    // Parametro in ingresso
    var currprocessoid="";                  // Parametro dedotto
    var currordinename="_VENDITEORDINE";    // Parametro in ingresso
    var currmotiveordine="";                // Parametro dedotto
    var currordineid="";
    var currtrasfid="";
    var currprocservizioid="";
    var currservizioid="";
    var currgenretypeid=RYQUE.formatid("0ARTICOLI000");
    var articolojolly=RYQUE.formatid("0STUFFJOLLY0");
    var currchiusa=0;
    var elencostati="";
    
    // VARIABILI CONTESTO
    var context="";
    var processodescr="";
    var statodescr="";
    
    // VARIABILI DI MASCHERA
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var flagrefresh=false;
    var loadedpraticaCid="";
    var loadedpraticaDid="";
    
    var tabselezione=1;
    var tabcontesto=2;
    var tabdettaglio=3;
    var taballegati=4;
    
    // DETERMINO IL PROCESSO
    if(_isset(settings["processo"])){
        currprocessoname=settings["processo"].toUpperCase();
    }

    // DETERMINO IL MOTIVO ORDINE
    if(_isset(settings["ordine"])){
        currordinename=settings["ordine"].toUpperCase();
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
    
    var lbf_proprie=$(prefix+"lbf_proprie").rylabel({left:340, top:offsety, caption:"Solo proprie"});
    var chk_proprie=$(prefix+"chk_proprie").rycheck({left:430, top:offsety,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    chk_proprie.value(1);
    
    var lbf_aperte=$(prefix+"lbf_aperte").rylabel({left:520, top:offsety, caption:"Solo aperte"});
    var chk_aperte=$(prefix+"chk_aperte").rycheck({left:610, top:offsety,
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
                oper_new.enabled(1);
                var q="";
                var t=qv_forlikeclause(txf_search.value());
                var richiedenteid=txf_richiedente.value();
                var proprie=chk_proprie.value();
                var aperte=chk_aperte.value();
                var datamin=txf_datemin.text();
                var datamax=txf_datemax.text();
                
                q="PROCESSOID='"+currprocessoid+"'";
                if(t!="")
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                if(richiedenteid!="")
                    q+=" AND RICHIEDENTEID='"+richiedenteid+"'";
                if(proprie)
                    q+=" AND STATOID IN ("+elencostati+")";
                if(aperte)
                    q+=" AND STATUS=0";
                if(datamin!="")
                    q+=" AND DATAINIZIO>=[:TIME("+datamin+"000000)]";
                if(datamax!="")
                    q+=" AND DATAINIZIO<=[:TIME("+datamax+"235959)]";

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
            else{
                oper_new.enabled(0);
            }
        }
    });

    // GRID DI SELEZIONE
    offsety+=35;
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_PRATICHEJOIN",
        orderby:"AUXTIME",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:230},
            {id:"RICHIEDENTE", caption:"Richiedente", width:230},
            {id:"DATAINIZIO", caption:"Inizio pratica", type:"/", width:100},
            {id:"STATODESCR", caption:"Fase", width:120}
        ],
        changerow:function(o,i){
            currpraticaid="";
            currstatoid="";
            currordineid="";
            currchiusa=0;
            loadedpraticaCid="";
            loadedpraticaDid="";
            oper_delete.enabled(false);
            objtabs.enabled(tabcontesto, false);
            objtabs.enabled(tabdettaglio, false);
            objtabs.enabled(taballegati, false);
            context="";
            statodescr="";
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            solalettura();
        },
        solveid:function(o,d){
            currpraticaid=d;
            oper_delete.enabled(true);
            solalettura();
            objtabs.enabled(tabcontesto, true);
            objtabs.enabled(tabdettaglio, true);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(tabcontesto);
            }
        },
        enter:function(){
            objtabs.currtab(tabcontesto);
        }
    });
    offsety+=300;
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        caption:"Nuova pratica",
        button:true,
        click:function(o){
            winzProgress(formid);
            var richiedenteid=txf_richiedente.value();
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pratiche_insert",
                    "data":{
                        "PROCESSOID":currprocessoid,
                        "RICHIEDENTEID":richiedenteid,
                        "DESCRIPTION":"[!SYSID] - [!RICHIEDENTE]"
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            var newpraticaid=v.params["PRATICAID"];
                            flagopen=true;
                            objgridsel.splice(0, 0, newpraticaid);
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
    oper_new.enabled(0);

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:600,
        top:offsety,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare la pratica selezionata?",
                confirm:function(){
                    winzProgress(formid);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"quivers_deepdelete",
                            "data":{
                                "SYSID":currpraticaid
                            }
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
            });
        }
    });
    
    // DEFINIZIONE TAB CONTESTO
    offsety=60;
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Protocollo"});
    var tx_reference=$(prefix+"REFERENCE").rytext({left:120, top:offsety, width:150});
    tx_reference.readonly(1);
    
    offsety+=30;
    var flagdescr=false;
    $(prefix+"LB_RICHIEDENTEID").rylabel({left:20, top:offsety, caption:"Richiedente"});
    var txrichiedente=$(prefix+"RICHIEDENTEID").ryhelper({
        left:120, top:offsety, width:250, datum:"C", tag:"RICHIEDENTEID", formid:formid, table:"QW_ATTORI", title:"Attori",
        open:function(o){
            o.where("");
            flagdescr=true;
        },
        onselect:function(o, d){
            if(flagdescr){
                flagdescr=false;
                var descr=tx_descr.value();
                descr=descr.replace(/\[!RICHIEDENTE\]/, d["DESCRIPTION"]);
                tx_descr.value(descr, true);
            }
        }
    });

    offsety+=30;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var tx_descr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:400, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    
    offsety+=30;
    $(prefix+"LB_STATUS").rylabel({left:20, top:offsety, caption:"Stato"});
    var tx_status=$(prefix+"STATUS").rylist({left:120, top:offsety, width:120, datum:"C",
        assigned:function(){
            RYWINZ.modified(formid, 1);
        }
    });
    tx_status.additem({caption:"Allestimento", key:"0"})
             .additem({caption:"Spedito", key:"1"})
             .additem({caption:"Consegnato", key:"2"});
        
   offsety+=30;
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});
    offsety+=25;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:600,
        top:60,
        width:120,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=tx_descr.value();
            var data=RYWINZ.ToObject(formid, "C", currpraticaid);
            data["MAGAZZINOID"]=txf_magazzino.value();
            var st=tx_status.key();
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":[
                        {
                            "function":"quivers_update",
                            "data":data
                        },
                        {
                            "function":"attivita_update",
                            "data":{
                                "SYSID":currordineid,
                                "PRATICAID":currpraticaid,
                                "CONSISTENCY":"0",
                                "STATUS":st
                            },
                            "return":{"PROTSERIE":"#PROTSERIE","PROTPROGR":"#PROTPROGR"}
                        }
                    ]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            if(tx_reference.value()==""){
                                tx_reference.value(v.infos["PROTSERIE"]+v.infos["PROTPROGR"]);
                            }
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
    
    var oper_ddt=$(prefix+"oper_ddt").rylabel({
        left:600,
        top:90,
        width:120,
        caption:"Doc. trasp.",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Stampare il documento di trasporto?",
                confirm:function(){
                    winzProgress(formid);
                    $.post(_cambusaURL+"rygeneral/customize.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "path":"corsaro/backoffice/ddt.php",
                            "data":{
                                "praticaid":currpraticaid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    var f=v.params["PATH"];
                                    if(window.console&&_sessioninfo.debugmode){console.log("Risposta da backoffice: "+f)}
                                    var h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+f;
                                    $("#winz-iframe").prop("src", h);
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

    var oper_trans=$(prefix+"oper_trans").rylabel({
        left:600,
        top:120,
        width:120,
        caption:"Trans. stato",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Effettuare una transizione di stato?",
                confirm:function(){
                    RYQUIVER.RequestID(formid, {
                        table:"QW_TRANSIZIONIJOIN", 
                        select:"TARGETID,ATTOREBOWID,ATTORETARGETID",
                        where:"BOWID='"+currstatoid+"' AND TARGETID<>''",
                        title:"Scelta transizione",
                        multiple:false,
                        onselect:function(d){
                            // PASSO L'ID DELLA TRANSIZIONE
                            transizionestato(d["SYSID"]);
                        }
                    });
                }
            });
        }
    });

    var lb_fase=$(prefix+"LB_FASE").rylabel({left:600, top:150, caption:""});
    
    // DEFINIZIONE TAB DETTAGLIO
    var lb_details_context=$(prefix+"details_context").rylabel({left:20, top:50, caption:""});

    offsety=80;
    $(prefix+"LB_MAGAZZINO").rylabel({left:20, top:offsety, caption:"Magazzino"});
    var txf_magazzino=$(prefix+"MAGAZZINO").ryhelper({left:120, top:offsety, width:150, 
        formid:formid, table:"QW_UFFICI", title:"Magazzini", multiple:false,
        open:function(o){
            o.where("MAGAZZINO=1");
        },
        onselect:function(o){
            $.cookie(_sessioninfo.environ+"_ordini_magazzinoid", o.value(), {expires:10000});
        }
    });

    $(prefix+"LB_DATA").rylabel({left:350, top:offsety, caption:"Data trasf."});
    var tx_data=$(prefix+"DATA").rydate({left:430, top:offsety});
    tx_data.value(Date.stringToday());

    offsety+=40;
    var operd_refresh=$(prefix+"operd_refresh").rylabel({
        left:600,
        top:offsety,
        width:110,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            griddett.clear();
            q="SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='"+currpraticaid+"')";
            griddett.where(q);
            griddett.query({
                ready:function(){
                    if(done!=missing){done()}
                }
            });
        }
    });

    offsety+=30;
    griddett=$(prefix+"griddett").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_TRASFERIMENTIJOIN",
        orderby:"SYSID",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione",width:300},
            {id:"AMOUNT", caption:"Qt.", width:120, type:"2"},
            {id:"BOWTIME", caption:"Data", width:120, type:"/"}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            currtrasfid="";
            currprocservizioid="";
            currservizioid="";
            RYWINZ.MaskClear(formid, "D");
            //RYWINZ.MaskEnabled(formid, "D", 0);
            operd_update.enabled(0);
            operd_unsaved.visible(0);
            operd_remove.enabled(0);
            oper_genre.enabled(0);
            solalettura();
            loadedpraticaDid="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currtrasfid=d;
            operd_remove.enabled(1);
            RYQUE.query({
                sql:"SELECT * FROM QW_TRASFERIMENTIJOIN WHERE SYSID='"+currtrasfid+"'",
                ready:function(v){
                    currprocservizioid=v[0]["PROCESSOID"];
                    currservizioid=v[0]["SERVIZIOID"];
                    // ABILITAZIONE TAB TRASFERIMENTI
                    //RYWINZ.MaskEnabled(formid, "D", 1);
                    operd_update.enabled(1);
                    oper_genre.enabled(1);
                    // CARICAMENTO TAB TRASFERIMENTI
                    RYWINZ.ToMask(formid, "D", v[0]);
                    operd_unsaved.visible(0);
                    solalettura();
                }
            });
        }
    });
    offsety=450;

    var operd_add=$(prefix+"operd_add").rylabel({
        left:20,
        top:offsety,
        width:110,
        caption:"Aggiungi",
        button:true,
        click:function(o){
            winzProgress(formid);
            var stats=[];
            var istr=0;
            if(RYWINZ.modified(formid)){
                // ISTRUZIONE DI SALVATAGGIO DEL TRASFERIMENTO MODIFICATO
                var datasave={};
                datasave["PRATICAID"]=currpraticaid;
                datasave["TRASFID"]=currtrasfid;
                datasave["GENREID"]=tx_genreid.value();
                datasave["SERVIZIOID"]=tx_servizioid.value();
                datasave["DESCRIPTION"]=tx_articolo.value();
                datasave["AMOUNT"]=tx_amount.value();
                datasave["MAGAZZINOID"]=txf_magazzino.value();
                datasave["BOWTIME"]=tx_data.text();
                stats[istr++]={
                    "function":"ordini_update",
                    "data":datasave
                };
            }
            // ISTRUZIONE DI INSERIMENTO NUOVO TRASFERIMENTO
            var data = new Object();
            data["PRATICAID"]=currpraticaid;
            data["BOWTIME"]=tx_data.text();
            stats[istr++]={
                "function":"ordini_insert",
                "data":data,
                "return":{"ARROWID":"#TRASFID"}
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
                            var newid=v.infos["ARROWID"];
                            RYWINZ.modified(formid, 0);
                            tx_amount.numdec(2);
                            flagfocus=true;
                            griddett.splice(0, 0, newid);
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

    var operd_unsaved=$(prefix+"operd_unsaved").rylabel({left:280, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    operd_unsaved.visible(0);
    
    var operd_remove=$(prefix+"operd_remove").rylabel({
        left:600,
        top:offsety,
        width:110,
        caption:"Rimuovi",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare il trasferimento selezionato?",
                confirm:function(){
                    winzProgress(formid);
                    RYWINZ.modified(formid, 0);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"ordini_delete",
                            "data":{
                                "PRATICAID":currpraticaid,
                                "TRASFID":currtrasfid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
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
                }
            });
        }
    });
    offsety+=40;

    var operd_update=$(prefix+"operd_update").rylabel({
        left:20,
        top:offsety,
        width:110,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data={};
            data["PRATICAID"]=currpraticaid;
            data["TRASFID"]=currtrasfid;
            data["GENREID"]=tx_genreid.value();
            data["SERVIZIOID"]=tx_servizioid.value();
            data["DESCRIPTION"]=tx_articolo.value();
            data["AMOUNT"]=tx_amount.value();
            data["MAGAZZINOID"]=txf_magazzino.value();
            data["BOWTIME"]=tx_data.text();
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"ordini_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            operd_unsaved.visible(0);
                            if(done!=missing){done()}
                            griddett.dataload();
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
    var flaggenre=false;
    $(prefix+"LB_GENREID").rylabel({left:20, top:offsety, caption:"Articolo"});
    var tx_genreid=$(prefix+"GENREID").ryhelper({
        left:110, top:offsety, width:400, datum:"D", tag:"GENREID", formid:formid, table:"QW_ARTICOLI", title:"Articoli",
        open:function(o){
            o.where("");
            flaggenre=true;
        },
        select:"ROUNDING,PROCESSOID",
        onselect:function(o, d){
            currprocservizioid=d["PROCESSOID"];
            if(flaggenre){
                flaggenre=false;
                tx_articolo.value(d["DESCRIPTION"], true);
            }
            tx_amount.numdec( _getinteger(d["ROUNDING"]) );
            tx_servizioid.enabled(1);
        },
        clear:function(){
            currprocservizioid="";
            tx_servizioid.clear();
            tx_servizioid.enabled(0);
        }
    });
    tx_genreid.enabled(0);
    
    var oper_genre=$(prefix+"oper_genre").rylabel({
        left:600,
        top:offsety,
        width:110,
        caption:"Ricerca...",
        button:true,
        click:function(o){
            cercaarticolo.show({
                open:function(){
                    $("#"+formid+"tabs").hide();
                },
                close:function(){
                    $("#"+formid+"tabs").show();
                },
                assigned:function(genre){
                    flaggenre=true;
                    tx_genreid.value(genre, true);
                }
            });
        }
    });
    var cercaarticolo=new corsaro_browserstuff(formid, "browser_genre");

    offsety+=30;
    var flagpratica=false;
    $(prefix+"LB_SERVIZIOID").rylabel({left:20, top:offsety, caption:"Servizio"});
    var tx_servizioid=$(prefix+"SERVIZIOID").ryhelper({
        left:110, top:offsety, width:400, datum:"D", tag:"SERVIZIOID", formid:formid, table:"QW_PRATICHE", title:"Pratiche",
        open:function(o){
            o.where("PROCESSOID='"+currprocservizioid+"' AND ( SYSID='"+currservizioid+"' OR NOT SYSID IN (SELECT SERVIZIOID FROM QW_TRASFERIMENTI) )");
            flagpratica=true;
        },
        onselect:function(o, d){
            if(flagpratica){
                flagpratica=false;
                tx_articolo.value(d["DESCRIPTION"], true);
            }
        }
    });
    tx_servizioid.enabled(0);
    
    offsety+=30;
    $(prefix+"LB_ARTICOLO").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var tx_articolo=$(prefix+"ARTICOLO").rytext({left:110, top:offsety, width:400, maxlen:100, datum:"D", tag:"DESCRIPTION",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });

    offsety+=30;
    $(prefix+"LB_AMOUNT").rylabel({left:20, top:offsety, caption:"Quantit&agrave"});
    var tx_amount=$(prefix+"AMOUNT").rynumber({left:110, top:offsety, width:150, numdec:2, minvalue:0, datum:"D", tag:"AMOUNT",
        changed:function(){
            operd_unsaved.visible(1);
        }
    });

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVARROWS");
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:50,
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Dettaglio"},
            {title:"Allegati"}
        ],
        select:function(i,p){
            if(p==tabcontesto){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedpraticaCid="";
                    }
                });
            }
            if(p==tabdettaglio){
                // PROVENGO DAL DETTAGLIO
                if(operd_update.enabled()){
                    flagsuspend=qv_changemanagement(formid, objtabs, operd_update, {
                        abandon:function(){
                            loadedpraticaDid="";
                        }
                    });
                }
                else{
                    RYWINZ.modified(formid, 0);
                }
            }
            if(i==1){
                loadedpraticaCid="";
                loadedpraticaDid="";
            }
            else if(i==tabcontesto){
                if(currpraticaid==loadedpraticaCid){
                    flagsuspend=true;
                }
            }
            else if(i==tabdettaglio){
                if(currpraticaid==loadedpraticaDid){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 1:
                    if(flagrefresh){
                        flagrefresh=false;
                        oper_refresh.engage();
                    }
                    else{
                        objgridsel.dataload();
                    }
                    break;
                case 2:
                    // CARICAMENTO CONTESTO
                    caricapratica(
                        function(){
                            loadedpraticaCid=currpraticaid;
                        }
                    );
                    break;
                case 3:
                    // CARICAMENTO DETTAGLIO
                    winzProgress(formid);
                    lb_details_context.caption("Contesto: "+context+" ("+statodescr+")");
                    caricapratica(
                        function(){
                            lb_details_context.caption("Contesto: "+context+" ("+statodescr+")");
                            loadedpraticaDid=currpraticaid;
                            operd_refresh.engage(
                                function(){
                                    if(txf_magazzino.value()==""){
                                        txf_magazzino.value($.cookie(_sessioninfo.environ+"_ordini_magazzinoid"));
                                    }
                                    winzClearMess(formid);
                                }
                            );
                        }
                    );
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currordineid, "Contesto: "+context+" ("+statodescr+")");
                    qv_contextmanagement(context, {sysid:currpraticaid, table:"QVQUIVERS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            filemanager.caption("Contesto: "+context+" ("+statodescr+")");
                        }
                    });
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(tabselezione);
    objtabs.enabled(tabcontesto, false);
    objtabs.enabled(tabdettaglio, false);
    objtabs.enabled(taballegati, false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            TAIL.enqueue(qv_queuequerycall, {
                "sql":"SELECT SYSID,DESCRIPTION FROM QW_PROCESSI WHERE [:UPPER(NAME)]='"+currprocessoname+"'",
                "back":function(v){
                    if(v.length>0){
                        currprocessoid=v[0]["SYSID"];
                        processodescr=v[0]["DESCRIPTION"];
                        $(prefix+"LB_PROCESSO").html("Processo: "+processodescr);
                    }
                }
            });
            TAIL.enqueue(qv_queuequerycall, {
                "sql":"SELECT SYSID FROM QW_MOTIVIATTIVITA WHERE [:UPPER(NAME)]='"+currordinename+"'",
                "back":function(v){
                    if(v.length>0){
                        currmotiveordine=v[0]["SYSID"];
                    }
                    RYQUE.query({
                        sql:"SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+currmotiveordine+"'",
                        ready:function(v){
                            elencostati="";
                            for(var i in v){
                                if(elencostati!="")
                                    elencostati+=",";
                                elencostati+="'"+v[i]["PARENTID"]+"'";
                            }
                            if(elencostati=="")
                                elencostati="''";
                            // OPERAZIONI FINALI
                            if(currprocessoid==""){
                                winzMessageBox(formid, 
                                    {
                                        message:"Processo {1} inesistente",
                                        args:[currprocessoname]
                                    }
                                );
                            }
                            else if(currmotiveordine==""){
                                winzMessageBox(formid, 
                                    {
                                        message:"Motivo ordine {1} inesistente",
                                        args:[currordinename]
                                    }
                                );
                            }
                            else{
                                oper_new.enabled(1);
                            }
                            setTimeout(
                                function(){
                                    oper_refresh.engage(
                                        function(){
                                            winzClearMess(formid);
                                            txf_search.focus();
                                        }
                                    );
                                }, 200
                            );
                        }
                    });
                }
            });
            TAIL.wriggle();
        }
    );
    function caricapratica(after){
        if(currordineid==""){
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currpraticaid)}
            tx_reference.clear();
            lb_fase.caption("");
            tx_status.setkey("0");
            RYWINZ.MaskClear(formid, "C");
            RYQUE.query({
                sql:"SELECT * FROM QW_PRATICHEJOIN WHERE SYSID='"+currpraticaid+"'",
                ready:function(v){
                    RYQUE.query({
                        sql:"SELECT QVQUIVERARROW.ARROWID AS ARROWID, QVARROWS.REFERENCE AS REFERENCE, QVARROWS.STATUS AS STATUS FROM QVQUIVERARROW INNER JOIN QVARROWS ON QVARROWS.SYSID=QVQUIVERARROW.ARROWID WHERE QVQUIVERARROW.QUIVERID='"+currpraticaid+"' AND QVARROWS.MOTIVEID='"+currmotiveordine+"'",
                        ready:function(z){
                            currordineid=z[0]["ARROWID"];
                            currstatoid=v[0]["STATOID"];
                            statodescr=v[0]["STATODESCR"];
                            currchiusa=__(v[0]["STATUS"]).actualBoolean();
                            if(elencostati.indexOf(currstatoid)<0){
                                currchiusa=1;
                            }
                            tx_reference.value(z[0]["REFERENCE"]);
                            lb_fase.caption("("+statodescr+")");
                            tx_status.setkey(z[0]["STATUS"]);
                            RYWINZ.ToMask(formid, "C", v[0]);
                            txf_magazzino.value(v[0]["MAGAZZINOID"]);
                            context=v[0]["DESCRIPTION"];
                            solalettura();
                            objtabs.enabled(taballegati, true);
                            after();
                            RYWINZ.modified(formid, 0);
                        }
                    });
                }
            });
        }
        else{
            after();
            RYWINZ.modified(formid, 0);
        }
    }
    function transizionestato(transid){
        winzProgress(formid);
        var stats=[];
        var istr=0;
        // ISTRUZIONE DI SALVATAGGIO PRATICA
        var data=RYWINZ.ToObject(formid, "C", currpraticaid);
        data["MAGAZZINOID"]=txf_magazzino.value();
        var st=tx_status.key();
        stats[istr++]={
            "function":"quivers_update",
            "data":data
        };
        stats[istr++]={
            "function":"attivita_update",
            "data":{
                "SYSID":currordineid,
                "PRATICAID":currpraticaid,
                "CONSISTENCY":"0",
                "STATUS":st
            },
            "return":{"PROTSERIE":"#PROTSERIE","PROTPROGR":"#PROTPROGR"}
        };
        // ISTRUZIONE DI TRANSIZIONE DI STATO
        stats[istr++]={
            "function":"pratiche_trans",
            "data":{
                "PRATICAID":currpraticaid,
                "TRANSID":transid
            }
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
                        if(tx_reference.value()==""){
                            tx_reference.value(v.infos["PROTSERIE"]+v.infos["PROTPROGR"]);
                        }
                        currstatoid=v.params["NUOVOSTATOID"];
                        statodescr=v.params["NUOVOSTATODESCR"];
                        loadedpraticaCid="";
                        loadedpraticaDid="";
                        flagrefresh=true;
                        lb_fase.caption("("+statodescr+")");
                        RYWINZ.modified(formid, 0);
                        if(elencostati.indexOf(currstatoid)<0){
                            currchiusa=1;
                        }
                        solalettura();
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
    function solalettura(){
        var flag=!currchiusa;
        var flagd=currtrasfid!="" && !currchiusa;
        globalobjs[formid+"RICHIEDENTEID"].enabled(flag);
        globalobjs[formid+"DESCRIPTION"].enabled(flag);
        globalobjs[formid+"STATUS"].enabled(flag);
        globalobjs[formid+"REGISTRY"].enabled(flag);
        globalobjs[formid+"oper_contextengage"].enabled(flag);
        globalobjs[formid+"oper_trans"].enabled(flag);
        globalobjs[formid+"MAGAZZINO"].enabled(flag);
        globalobjs[formid+"DATA"].enabled(flag);
        globalobjs[formid+"operd_add"].enabled(flag);
        globalobjs[formid+"operd_remove"].enabled(flagd);
        globalobjs[formid+"operd_update"].enabled(flagd);
        globalobjs[formid+"GENREID"].enabled(flagd);
        globalobjs[formid+"SERVIZIOID"].enabled(flagd);
        globalobjs[formid+"oper_genre"].enabled(flagd);
        globalobjs[formid+"ARTICOLO"].enabled(flagd);
        globalobjs[formid+"AMOUNT"].enabled(flagd);
        
        // ALLEGATI
        if(flag)
            $(prefix+"oper_fileinsert").css({"display":"block"});
        else
            $(prefix+"oper_fileinsert").css({"display":"none"});
    }
}

