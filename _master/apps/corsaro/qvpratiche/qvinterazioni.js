/****************************************************************************
* Name:            qvinterazioni.js                                         *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvinterazioni(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    // VARIABILI PRATICA
    var currpraticaid="";
    var currattoreid="";
    var curruserid="";
    var currroleid="";
    var currchiusa=0;
    var currupdating=0;
    var currprocessoname="";    // Parametro in ingresso
    var currprocessoid="";      // Parametro dedotto
    var currusointerno=true;    // Parametro dedotto
    var currprivacy=1;          // Parametro dedotto
    var motiverichiesta=RYQUE.formatid("0MOTATTRICH0");
    var motivenota=RYQUE.formatid("0MOTATTANNOT");
    
    // APERTURA AUTOMATICA ATTIVITA
    var openprocessoid="";
    var openpraticaid="";
    var openattivid="";
    var operrichiedente="";
    var newpraticaid="";
    var newattivid="";
    
    // VARIABILI ATTIVITA'
    var currattivid="";
    var currbowid="";
    var currconsistency=0;
    var flagattivnuovo=false;
    
     // VARIABILI DETTAGLIO
    var previewX=0;
    var previewY=0;
    
   // VARIABILI CONTESTO
    var context="";
    var context_attivita="";
    
    // VARIABILI DI MASCHERA
    var prefix="#"+formid;
    var flagopen=false;
    var flagopenD=false;
    var flagsuspend=false;
    var loadedpraticaAid="";
    var loadedpraticaDid="";
    
    var tabselezione=1;
    var tabattivita=2;
    var tabdettaglio=3;
    var taballegati=4;
    
    // DETERMINO IL PROCESSO
    if(_isset(settings["processo"])){
        currprocessoname=settings["processo"].toUpperCase();
        currusointerno=false;
        currprivacy=2;
    }
    // DETERMINO LA PRATICA
    if(_isset(settings["pratica"])){
        openpraticaid=settings["pratica"];
    }
    // DETERMINO APERTURA DA ATTIVITA
    if(_isset(settings["attivita"])){
        openattivid=settings["attivita"];
        if(_isset(settings["form"])){
            operrichiedente=settings["form"];
        }
    }

    $(prefix+"LB_PROCESSO").addClass("rybox-title").css({"left":20, "top":10});
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    offsety+=30;

    var lbf_processo=$(prefix+"lbf_processo").rylabel({left:20, top:offsety, caption:"Processo*"});
    var txf_processo=$(prefix+"txf_processo").ryhelper({left:100, top:offsety, width:300, 
        formid:formid, table:"QW_PROCESSI", title:"Processi", multiple:false,
        open:function(o){
            o.where("");
            o.orderby("DESCRIPTION");
        },
        assigned: function(){
            currprocessoid=txf_processo.value();
            $.cookie(_sessioninfo.environ+"_interazioni_processo", currprocessoid, {expires:10000});
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
    if(!currusointerno){
        lbf_processo.visible(0);
        txf_processo.visible(0);
    }
    offsety+=30;

    $(prefix+"lbf_datemin").rylabel({left:20, top:offsety, caption:"Data min"});
    var txf_datemin=$(prefix+"txf_datemin").rydate({left:100, top:offsety,  width:100, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    $(prefix+"lbf_datemax").rylabel({left:230, top:offsety, caption:"Data max"});
    var txf_datemax=$(prefix+"txf_datemax").rydate({left:300, top:offsety,  width:100, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    
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
                var t=_likeescapize(txf_search.value());
                var processoid=currprocessoid;
                var datamin=txf_datemin.text();
                var datamax=txf_datemax.text();
                
                if(currusointerno){
                    q="STATUS=0";
                    q+=" AND '"+currattoreid+"' IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currprocessoid+"')";
                }
                else{
                    q="STATUS<=1";
                    q+=" AND RICHIEDENTEID='"+currattoreid+"'";
                }
                if(t!="")
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                if(processoid!="")
                    q+=" AND PROCESSOID='"+processoid+"'";
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
                        if(openprocessoid!=""){
                            // MI POSIZIONO SULLA PRATICA DI OPEN
                            automazione(1);
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
        maxwidth:-1,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_PRATICHEJOIN",
        orderby:"STATUS, AUXTIME",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:300},
            {id:"ATTORE", caption:"Proprietario", width:150},
            {id:"DATAINIZIO", caption:"Inizio pratica", type:"/", width:100},
            {id:"DATAFINE", caption:"Fine pratica", type:"/", width:100},
            {id:"STATUS", caption:"C", width:40, type:"?"},
            {id:"STATODESCR", caption:"Stato", width:200},
            {id:"STATUSTIME", caption:"Cambio Stato", type:":", width:140}
        ],
        changerow:function(o,i){
            currpraticaid="";
            loadedpraticaAid="";
            loadedpraticaDid="";
            currchiusa=0;
            objtabs.enabled(tabattivita, false);
            objtabs.enabled(tabdettaglio, false);
            objtabs.enabled(taballegati, false);
            context="";
            context_attivita="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currpraticaid=d;
            objtabs.enabled(tabattivita, true);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(tabattivita);
            }
        },
        enter:function(){
            objtabs.currtab(tabattivita);
        }
    });
    offsety=470;
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        caption:"Nuova pratica",
        button:true,
        click:function(o){
            // CHIEDO LA DESCRIZIONE DELLA NUOVA PRATICA
            // DEFINIZIONE DELLA DIALOGBOX
            var dlg=winzDialogGet(formid);
            var hangerid=dlg.hanger;
            var h="";
            var vK=[];
            winzDialogParams(dlg, {
                width:500,
                height:200,
                open:function(){
                    castFocus(formid+"dialog_text");
                },
                close:function(){
                    winzDisposeCtrl(formid, vK);
                    winzDialogFree(dlg);
                }
            });
            // CONTENUTO
            h+="<div class='winz_msgbox'>";
            h+="Inserire l'oggetto della richiesta:";
            h+="</div>";
            h+=winzAppendCtrl(vK, formid+"dialog_text");
            h+=winzAppendCtrl(vK, formid+"dialog_ok");
            $("#"+hangerid).html(h);
            // DEFINIZIONE CAMPI
            $("#"+formid+"dialog_text").rytext({left:20, top:100, width:400, maxlen:50, formid:formid});
            $("#"+formid+"dialog_ok").rylabel({
                left:20,
                top:dlg.height-40,
                width:80,
                caption:"Genera",
                button:true,
                formid:formid,
                click:function(o){
                    var t=globalobjs[formid+"dialog_text"].value();
                    if(t==""){
                        t="Pratica [!SYSID] - [!RICHIEDENTE]";
                    }
                    winzDialogClose(dlg);
                    // TENTO DI APRIRE UNA NUOVA PRATICA
                    winzProgress(formid);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "program":[
                                {
                                    "function":"pratiche_insert",
                                    "data":{
                                        "PROCESSOID":currprocessoid,
                                        "RICHIEDENTEID":currattoreid,
                                        "DESCRIPTION":t
                                    },
                                    "return":{"PRATICAID":"#PRATICAID"},
                                    "pipe":{"PRATICAID":"#PRATICAID"}
                                },
                                {
                                    "function":"attivita_insert",
                                    "data":{
                                        "OPERATION":"INSERT",
                                        "MOTIVEID":motiverichiesta,
                                        "BOWID":currattoreid
                                    },
                                    "return":{"RICHIESTAID":"#ARROWID"}
                                }
                            ]
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    newpraticaid=v.infos["PRATICAID"];
                                    newattivid=v.infos["RICHIESTAID"];
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
            // MOSTRO LA DIALOGBOX
            winzDialogOpen(dlg);
        }
    });
    oper_new.enabled(0);
    if(currusointerno){
        oper_new.visible(0);
    }
    
    // DEFINIZIONE TAB ATTIVITA'
    offsety=80;
    var lb_attivita_context=$(prefix+"attivita_context").rylabel({left:20, top:50, caption:""});

    offsety=80;
    $(prefix+"lba_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txa_search=$(prefix+"txa_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            setTimeout(
                function(){
                    opera_refresh.engage();
                }, 100
            );
        }
    });
    offsety+=30;
    
    var lbf_pendenti=$(prefix+"lbf_pendenti").rylabel({left:430, top:offsety, caption:"Solo pendenti"});
    var chk_pendenti=$(prefix+"chk_pendenti").rycheck({left:532, top:offsety,
        assigned:function(){
            setTimeout(function(){opera_refresh.engage();}, 100);
        }
    });
    chk_pendenti.value(0);
    
    var opera_refresh=$(prefix+"opera_refresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            gridattivita.clear()
            var q="";
            var t=_likeescapize(txa_search.value());

            q+="PRATICAID='"+currpraticaid+"' AND AVAILABILITY=0";
            if(currusointerno){
                // GESTIONE SCOPE
                switch(gridattivita.provider()){
                case "sqlite":
                    q+=" AND (SCOPE=0 OR (SCOPE=1 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"' OR SELECTEDID='"+currattoreid+"' )) OR (SCOPE=2 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"')))";
                    break;
                default:
                    q+=" AND (SCOPE=0 OR (SCOPE=1 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"' OR '"+currattoreid+"' IN (SELECT QVSELECTIONS.SELECTEDID FROM QVSELECTIONS WHERE QVSELECTIONS.PARENTID=QW_ATTIVITABROWSER.SETCONOSCENZA) )) OR (SCOPE=2 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"')))";
                }
            }
            else{
                q+=" AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"')";
            }
            // GESTIONE CONSISTENZA
            q+=" AND ( CONSISTENCY=0 OR (CONSISTENCY=2 AND BOWID='"+currattoreid+"') )";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(chk_pendenti.value())
                q+=" AND STATUS=0";

            gridattivita.where(q);
            gridattivita.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });
    offsety+=35;
    
    var gridattivita=$(prefix+"gridattivita").ryque({
        left:20,
        top:offsety,
        width:700,
        height:400,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_ATTIVITABROWSER",
        orderby:"TIMEINSERT,SYSID",
        columns:[
            {id:"ALLEGATI", caption:"A", width:23},
            {id:"IMPORTANZA", caption:"I", width:23},
            {id:"CONSISTENCY", caption:"", width:0},
            {id:"SYSID", caption:"", width:0},
            {id:"RISPOSTE", caption:"", width:0},
            {id:"REFARROWID", caption:"R", width:23},
            {id:"DESCRIPTION", caption:"Descrizione", width:210},
            {id:"AUXTIME", caption:"Data/Ora", width:130, type:":"},
            {id:"STATUS", caption:"C", width:20, type:"?"},
            {id:"BOW", caption:"Origine", width:120},
            {id:"TARGET", caption:"Destinatario", width:120},
            {id:"AMOUNT", caption:"Impiego", width:70, type:"0"},
            {id:"BOWTIME", caption:"Inizio", width:100, type:"/"},
            {id:"TARGETTIME", caption:"Fine", width:100, type:"/"},
            {id:"REFERENCE", caption:"Protocollo", width:100}
        ],
        changerow:function(o, i){
            currattivid="";
            currbowid="";
            currconsistency=0;
            currupdating=0;
            opera_rispondi.enabled(0);
            opera_print.enabled(0);
            opera_elimina.enabled(0);
            objtabs.enabled(tabdettaglio, false);
            objtabs.enabled(taballegati, false);
            caricaanteprima(false);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o, d){
            currattivid=d;
            RYQUE.query({
                sql:"SELECT BOWID,CONSISTENCY FROM QW_ATTIVITA WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        currconsistency=parseInt(v[0]["CONSISTENCY"]);
                        currbowid=v[0]["BOWID"];
                        if(currconsistency!=1){
                            opera_rispondi.enabled(1);
                            opera_print.enabled(1);
                            opera_elimina.enabled(currconsistency==2);
                            solalettura();
                            objtabs.enabled(tabdettaglio, true);
                            objtabs.enabled(taballegati, true);
                            if(flagopenD){
                                flagopenD=false;
                                // EVITO CHE RIATTIVI AUTOMATISMI
                                openprocessoid="";
                                newpraticaid="";
                                objtabs.currtab(tabdettaglio);
                            }
                            if(flagattivnuovo){
                                flagattivnuovo=false;
                                objtabs.currtab(tabdettaglio);
                                castFocus(prefix+"D_DESCRIPTION");
                            }
                            setTimeout(
                                function(){
                                    caricaanteprima(true);
                                }, 100
                            );
                        }
                    }catch(e){}
                } 
            });
        },
        enter:function(){
            objtabs.currtab(tabdettaglio);
        },
        ready:function(){
            if(openprocessoid!=""){
                // MI POSIZIONO SULLA ATTIVITA' DI OPEN
                automazione(2);
            }
            else if(newpraticaid!=""){
                // MI POSIZIONO SULLA ATTIVITA' DI NEW
                automazione(3);
            }
        },
        before:function(o, d){
            for(var i in d){
                // COLONNA ALLEGATI
                if(d[i]["ALLEGATI"]=="1")
                    d[i]["ALLEGATI"]=_iconAttachment();
                else
                    d[i]["ALLEGATI"]="";
                // COLONNA IMPORTANZA
                if(d[i]["CONSISTENCY"]=="2"){
                    d[i]["IMPORTANZA"]=_iconPencil();
                }
                else{
                    switch(d[i]["IMPORTANZA"]){
                    case "0":
                        d[i]["IMPORTANZA"]=_iconLow();
                        break;
                    case "1":
                        d[i]["IMPORTANZA"]="";
                        break;
                    case "2":
                        d[i]["IMPORTANZA"]=_iconHigh()
                        break;
                    }
                }
                // COLONNA DIALOGO
                if(d[i]["REFARROWID"]!=d[i]["SYSID"]){
                    d[i]["REFARROWID"]=_iconAnswer();
                }
                else{
                    if(d[i]["RISPOSTE"]=="1")
                        d[i]["REFARROWID"]=_iconReplied();
                    else
                        d[i]["REFARROWID"]="";
                }
                // COLONNA STATUS
                var fd=o.screenrow(i);
                if(d[i]["STATUS"]<"2")
                    $(fd).css({"color":"black"});
                else
                    $(fd).css({"color":"gray"});
            }
        }
    });
    
    offsety=550;
    var opera_nuova=$(prefix+"opera_nuova").rylabel({
        left:20,
        top:offsety,
        caption:"Nuova",
        button:true,
        click:function(o){
            qv_idrequest(formid, {
                table:"QW_MOTIVIATTIVITA", 
                where:"(SYSID='"+motiverichiesta+"' OR SYSID='"+motivenota+"')",
                orderby:"ORDINATORE,DESCRIPTION",
                title:"Scelta Motivo",
                multiple:false,
                onselect:function(d){
                    var motiveid=d["SYSID"];
                    var stats=[];
                    var istr=0;
                    stats[istr++]={
                        "function":"attivita_insert",
                        "data":{
                            "OPERATION":"INSERT",
                            "PRATICAID":currpraticaid,
                            "MOTIVEID":motiveid,
                            "BOWID":currattoreid
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
                                    // REPERISCO IL SYSID DELLA NUOVA FRECCIA
                                    var newid=v.params["ARROWID"];
                                    // POPOLO IL GRID COLLA NUOVA ATTIVITA'
                                    flagattivnuovo=true;
                                    gridattivita.splice(0, 0, newid);
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

    var opera_rispondi=$(prefix+"opera_rispondi").rylabel({
        left:100,
        top:offsety,
        caption:"Rispondi",
        button:true,
        click:function(o){
            var stats=[];
            var istr=0;
            stats[istr++]={
                "function":"attivita_insert",
                "data":{
                    "OPERATION":"ANSWER",
                    "PRATICAID":currpraticaid,
                    "REFARROWID":currattivid,
                    "BOWID":currattoreid
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
                            // REPERISCO IL SYSID DELLA NUOVA FRECCIA
                            var newid=v.params["ARROWID"];
                            // POPOLO IL GRID COLLA NUOVA ATTIVITA'
                            flagattivnuovo=true;
                            gridattivita.splice(0, 0, newid);
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
    
    var opera_print=$(prefix+"opera_print").rylabel({
        left:550,
        top:offsety,
        caption:"Stampa",
        button:true,
        click:function(o){
            qv_print(formid+"preview");
        }
    });
    
    var opera_elimina=$(prefix+"opera_elimina").rylabel({
        left:650,
        top:offsety,
        caption:"Elimina",
        button:true,
        click:function(o){
            if(currconsistency==2){
                winzMessageBox(formid, {
                    message:"Eliminare l'attivit&agrave; selezionata?",
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
                                            "QUIVERID":currpraticaid,
                                            "ARROWID":currattivid
                                        }
                                    },
                                    {
                                        "function":"arrows_delete",
                                        "data":{
                                            "SYSID":currattivid
                                        }
                                    }
                                ]
                            }, 
                            function(d){
                                try{
                                    var v=$.parseJSON(d);
                                    if(v.success>0){
                                        gridattivita.refresh();
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
        }
    });
    
    offsety+=50;
    previewX=20;
    previewY=offsety;
    
    $(prefix+"preview").css({"position":"absolute", "left":20, "top":offsety, "width":700});
    
   // DEFINIZIONE TAB DETTAGLIO
    offsety=100;
    var lb_dett_context=$(prefix+"dett_context").rylabel({left:20, top:50, caption:""});

    $(prefix+"LBD_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var txd_descr=$(prefix+"D_DESCRIPTION").rytext({left:80, top:offsety, width:520, datum:"D", tag:"DESCRIPTION"});
    offsety+=30;

    $(prefix+"LBD_IMPORTANZA").rylabel({left:390, top:offsety, caption:"Priorit&agrave;"});
    $(prefix+"D_IMPORTANZA").rylist({left:450, top:offsety, width:150, datum:"D", tag:"IMPORTANZA"})
    .additem({caption:"Bassa", key:0})
    .additem({caption:"Media", key:1})
    .additem({caption:"Alta", key:2});
    offsety+=30;

    var lbd_status=$(prefix+"LBD_STATUS").rylabel({left:390, top:offsety, caption:"Stato"});
    var txd_status=$(prefix+"D_STATUS").rylist({left:450, top:offsety, width:150})
    .additem({caption:"In attesa...", key:0})
    .additem({caption:"25% completato", key:1})
    .additem({caption:"50% completato", key:2})
    .additem({caption:"75% completato", key:3})
    .additem({caption:"Completo", key:4});
    if(!currusointerno){
        lbd_status.visible(0);
        txd_status.visible(0);
    }
    offsety+=50;
    
    var txd_registry=$(prefix+"D_REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"D", tag:"REGISTRY"});

    // SALVATAGGIO ATTIVITA'
    var operd_salva=$(prefix+"operd_salva").rylabel({
        left:680,
        top:100,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context_attivita=txd_descr.value();
            var data=qv_mask2object(formid, "D", currattivid);
            data["PRATICAID"]=currpraticaid;
            impostastatus(data);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "function":"attivita_update",
                    "env":_sessioninfo.environ,
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            RYWINZ.modified(formid, 0);
                            if(txd_status.value()<5 || currconsistency==2){
                                gridattivita.dataload(
                                    function(){
                                        caricaanteprima(true);
                                    }
                                );
                            }
                            else{
                                inviamessaggio();
                            }
                        }
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
    
    var operd_invia=$(prefix+"operd_invia").rylabel({
        left:680,
        top:130,
        caption:"Invia&nbsp;",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context_attivita=txd_descr.value();
            var data=qv_mask2object(formid, "D", currattivid);
            data["PRATICAID"]=currpraticaid;
            data["CONSISTENCY"]=0;
            impostastatus(data);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "function":"attivita_update",
                    "env":_sessioninfo.environ,
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            inviamessaggio();
                        }
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

    offsety+=390;
    var operd_print=$(prefix+"operd_print").rylabel({
        left:580,
        top:offsety,
        caption:"Stampa documento",
        button:true,
        click:function(o){
            qv_printText(txd_registry.value());
        }
    });

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVARROWS");
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:40,position:"relative",
        tabs:[
            {title:"Selezione"},
            {title:"Attivit&agrave;"},
            {title:"Dettaglio"},
            {title:"Allegati"}
        ],
        select:function(i,p){
            if(p==tabdettaglio){
                // PROVENGO DAL DETTAGLIO
                if(operd_salva.enabled()){
                    flagsuspend=qv_changemanagement(formid, objtabs, operd_salva, {
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
                loadedpraticaAid="";
                loadedpraticaDid="";
            }
            else if(i==tabattivita){
                if(currpraticaid==loadedpraticaAid){
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
                    objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO ATTIVITA
                    lb_attivita_context.caption("Contesto: "+context);
                    caricapratica(
                        function(){
                            lb_attivita_context.caption("Contesto: "+context);
                            loadedpraticaAid=currpraticaid;
                            setTimeout(function(){opera_refresh.engage()}, 100);
                        }
                    );
                    break;
                case 3:
                    // CARICAMENTO DETTAGLIO
                    lb_dett_context.caption("Contesto: "+context);
                    caricapratica(
                        function(){
                            lb_dett_context.caption("Contesto: "+context);
                            caricaattivita();
                        }
                    );
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currattivid, "Contesto: "+context+" - "+context_attivita);
                    caricaattivita(
                        function(){
                            qv_contextmanagement(context, {sysid:currattivid, table:"QVARROWS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                                done:function(d){
                                    context=d;
                                    filemanager.caption("Contesto: "+context+" - "+context_attivita);
                                }
                            });
                        }
                    );
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(tabselezione);
    objtabs.enabled(tabattivita, false);
    objtabs.enabled(tabdettaglio, false);
    objtabs.enabled(taballegati, false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            qv_queuequery[formid+"_0"]={
                "table":"QVUSERS",
                "select":"SYSID",
                "where":"EGOID='"+_sessioninfo.userid+"'",
                "back":function(v){
                    curruserid=v[0]["SYSID"];
                }
            };
            qv_queuequery[formid+"_1"]={
                "table":"QVROLES",
                "select":"SYSID",
                "where":"EGOID='"+_sessioninfo.roleid+"'",
                "back":function(v){
                    currroleid=v[0]["SYSID"];
                }
            };
            if(currprocessoname!=""){
                qv_queuequery[formid+"_2"]={
                    "sql":"SELECT SYSID FROM QW_PROCESSI WHERE [:UPPER(NAME)]='"+currprocessoname+"'",
                    "back":function(v){
                        if(v.length>0){
                            currprocessoid=v[0]["SYSID"];
                        }
                    }
                };
            }
            else if(openattivid!=""){
                qv_queuequery[formid+"_3"]={
                    "sql":"SELECT PRATICAID FROM QW_ATTIVITAJOIN WHERE SYSID='"+openattivid+"'",
                    "back":function(v){
                        if(v.length>0){
                            openpraticaid=v[0]["PRATICAID"];
                        }
                    }
                };
            }
            qv_queuequery[formid+"_5"]={
                "sql":"SELECT SYSID FROM QW_ATTORIJOIN WHERE EGOUTENTEID='"+_sessioninfo.userid+"'",
                "back":function(v){
                    if(v.length>0){
                        currattoreid=v[0]["SYSID"];
                    }
                    if(currprocessoname!="" && currprocessoid==""){
                        winzMessageBox(formid, 
                            {
                                message:"Processo {1} inesistente",
                                args:[currprocessoname]
                            }
                        );
                    }
                    else if(currattoreid==""){
                        winzMessageBox(formid, "Utente non incluso tra gli attori");
                    }
                    else{
                        if(openpraticaid!=""){
                            qv_queuequery[formid+"_4"]={
                                "sql":"SELECT PROCESSOID FROM QW_PRATICHE WHERE SYSID='"+openpraticaid+"'",
                                "back":function(v){
                                    winzClearMess(formid);
                                    if(v.length>0){
                                        openprocessoid=v[0]["PROCESSOID"];
                                        txf_processo.value(openprocessoid, true);
                                    }
                                }
                            };
                            qv_queuemanager();
                        }
                        else{
                            winzClearMess(formid);
                            txf_search.focus();
                            if(currusointerno)
                                txf_processo.value($.cookie(_sessioninfo.environ+"_interazioni_processo"), true);
                            else
                                setTimeout(function(){oper_refresh.engage()}, 100);
                        }
                    }
                }
            };
            qv_queuemanager();
        }
    );
    function caricapratica(after){
        if(context==""){
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currpraticaid)}
            RYQUE.query({
                sql:"SELECT DESCRIPTION,STATUS FROM QW_PRATICHE WHERE SYSID='"+currpraticaid+"'",
                ready:function(v){
                    context=v[0]["DESCRIPTION"];
                    currchiusa=_bool(v[0]["STATUS"]);
                    solalettura();
                    after();
                }
            });
        }
        else{
            after();
        }
    }
    function caricaattivita(after, missing){
        RYQUE.query({
            sql:"SELECT * FROM QW_ATTIVITAJOIN WHERE SYSID='"+currattivid+"'",
            ready:function(v){
                // ASSEGNAMENTO CAMPI
                qv_object2mask(formid, "D", v[0]);
                mascherastatus(v[0]);
                // REGOLE DI UPDATING: ABILITAZIONE BOTTONE "SALVA" E "INVIA"
                if(_getinteger(v[0]["CONSISTENCY"])==2){
                    currconsistency=2;
                    currupdating=1;
                    operd_invia.enabled(1);
                }
                else if(parseInt(v[0]["STATUS"])==0){
                    currconsistency=0;
                    if(currusointerno){
                        currupdating=0;
                        switch(_getinteger(v[0]["UPDATING"])){
                        case 0:
                            currupdating=1;
                            break;
                        case 1:
                            // LO POSSONO MODIFICARE SOLO GLI INTERLOCUTORI
                            if(v[0]["BOWID"]==currattoreid || v[0]["TARGETID"]==currattoreid){
                                currupdating=1;
                            }
                            break;
                        case 2:
                            // LO PUO' MODIFICARE SOLO IL RICHIEDENTE
                            if(v[0]["BOWID"]==currattoreid){
                                currupdating=1;
                            }
                            break;
                        }
                    }
                    else{
                        currupdating=0;
                    }
                    operd_invia.enabled(0);
                }
                else{
                    currconsistency=0;
                    currupdating=0;
                    operd_invia.enabled(0);
                }
                context_attivita=v[0]["DESCRIPTION"];
                lb_dett_context.caption("Contesto: "+context);
                RYWINZ.modified(formid, 0);
                solalettura();
                winzClearMess(formid);
                castFocus(formid+"D_DESCRIPTION");
                if(after!=missing){after()}
            }
        });
    }
    function caricaanteprima(flag, after){
        if(flag){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"attivita_preview",
                    "data":{
                        "QUIVERID":currpraticaid,
                        "ARROWID":currattivid,
                        "privacy":currprivacy
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var h=v.params["PREVIEW"];
                            h=h.replace(/&lt;/ig, "<");
                            h=h.replace(/&gt;/ig, ">");
                            h=h.replace(/&quot;/ig, "\"");
                            h=h.replace(/&amp;/ig, "&");
                            $(prefix+"preview").html(h);
                        }
                        winzClearMess(formid);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    if(after!=missing){
                        after();
                    }
                }
            );
        }
        else{
            $(prefix+"preview").html("");
            if(after!=missing){
                after();
            }
        }
    }
    function automazione(fase){
        switch(fase){
        case 1:
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
            break;
        case 2:
            gridattivita.search({
                    "where": _ajaxescapize("SYSID='"+openattivid+"'")
                },
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        var ind=v[0];
                        if(ind>0){
                            flagopenD=true;
                            gridattivita.index(ind);
                        }
                    }
                    catch(e){
                        alert(d);
                    }
                }
            );
            break;
        case 3:
            gridattivita.search({
                    "where": _ajaxescapize("SYSID='"+newattivid+"'")
                },
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        var ind=v[0];
                        if(ind>0){
                            flagopenD=true;
                            gridattivita.index(ind);
                        }
                    }
                    catch(e){
                        alert(d);
                    }
                }
            );
            break;
        }
    }
    function solalettura(){
        var flaga=(currchiusa==0);
        var flagd=(currchiusa==0) && currupdating;
        
        globalobjs[formid+"opera_nuova"].enabled(flaga);
        globalobjs[formid+"opera_rispondi"].visible(flaga);
        globalobjs[formid+"opera_elimina"].visible(flaga);
        
        globalobjs[formid+"operd_salva"].visible(flagd);
        globalobjs[formid+"operd_invia"].visible(flagd);
        qv_maskenabled(formid, "D", flagd);
        txd_status.enabled(flagd);
        filemanager.enabled(flagd);
    }
    function impostastatus(data){
        //if(txd_status.visible()){
            switch(_getinteger(txd_status.value())){
            case 1:
                data["STATUS"]=0;
                data["PERCENTUALE"]=0;
                break;
            case 2:
                data["STATUS"]=0;
                data["PERCENTUALE"]=1;
                break;
            case 3:
                data["STATUS"]=0;
                data["PERCENTUALE"]=2;
                break;
            case 4:
                data["STATUS"]=0;
                data["PERCENTUALE"]=3;
                break;
            case 5:
                data["STATUS"]=1;
                data["PERCENTUALE"]=0;
                break;
            }
        //}
    }
    function mascherastatus(v){
        if(currusointerno){
            switch(_getinteger(v["STATUS"])){
            case 0:
                switch(_getinteger(v["PERCENTUALE"])){
                case 1:txd_status.value(2);break;
                case 2:txd_status.value(3);break;
                case 3:txd_status.value(4);break;
                default:txd_status.value(0);
                }
                //lbd_status.visible(1);
                //txd_status.visible(1);
                break;
            default:
                //txd_status.value(0);
                //lbd_status.visible(0);
                //txd_status.visible(0);
            }
        }
    }
    function inviamessaggio(){
        currconsistency=0;
        opera_elimina.enabled(0);
        gridattivita.dataload(
            function(){
                caricaattivita(
                    function(){
                        objtabs.currtab(tabattivita);
                        caricaanteprima(true,
                            function(){
                                if(operrichiedente!=""){
                                    if(_isset(globalobjs[operrichiedente+"gridsel"])){
                                        globalobjs[operrichiedente+"gridsel"].refresh();
                                    }
                                }
                            }
                        );
                    }
                );
            }
        );
    }
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new} );
    this._resize=function(){
        if( $("#window_"+formid).width()>1400 )
            $(prefix+"preview").css({left:740, top:80, width:660});
        else
            $(prefix+"preview").css({left:previewX, top:previewY, width:700});
    }
}

