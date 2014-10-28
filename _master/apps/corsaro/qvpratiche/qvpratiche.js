/****************************************************************************
* Name:            qvpratiche.js                                            *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvpratiche(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    // VARIABILI PRATICA
    var currprocessoid="";
    var currinterprocesso="";
    var currpraticaid="";
    var currstatoid="";
    var currattoreid="";
    var curruserid="";
    var currroleid="";
    var curriniziale=false;
    var currfinale=false;
    var currchiusa=0;
    var elencostati="";
    var curraggiuntivi="";
    var cachefields={};
    var sospendirefresh=false;
    
    // VARIABILI ATTIVITA'
    var currattivid="";
    var currbowid="";
    var currconsistency=0;
    var flagattivnuovo=false;
    var genereore=RYQUE.formatid("0TIMEHOURS00");
    var generegiorni=RYQUE.formatid("0TIMEDAYS000");
    
    // VARIABILI DETTAGLIO
    var previewX=0;
    var previewY=0;
    var currdettenabled=1;
    
    // VARIABILI DOCUMENTO
    var filestatoid="";
    var loadedfileid="";
    var fileattivid="";
    var fileattivdescr="";
    var fileattivpath="";
    
    // VARIABILI MOVIMENTO
    var currmovid="";
    var movimentitipo=RYQUE.formatid("0MOVIMENTI00");
    var movgeneretipo=RYQUE.formatid("0MONEY000000");
    var movcontoid="";
    var movgenereid="";
    
    // VARIABILI CONTESTO
    var context="";
    var context_attivita="";
    
    // VARIABILI DI MASCHERA
    var prefix="#"+formid;
    var flagopen=false;
    var descropen="";
    var flagopenD=false;
    var flagsuspend=false;
    var loadedpraticaCid="";
    var loadedpraticaAid="";
    var loadedpraticaDid="";
    var loadedpraticaMid="";
    var flagriapertura=false;
    var flagmovnuovo=false;
    
    var tabselezione=1;
    var tabcontesto=2;
    var tabattivita=3;
    var tabdettaglio=4;
    var taballegati=5;
    var tabmovimenti=6;

    $(prefix+"LB_PROCESSO").addClass("rybox-title").css({"left":20, "top":10});
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:200, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });

    $(prefix+"lbf_processo").rylabel({left:340, top:offsety, caption:"Processo*"});
    var txf_processo=$(prefix+"txf_processo").ryhelper({left:430, top:offsety, width:200, 
        formid:formid, table:"QW_PROCESSIJOIN", title:"Processi", multiple:false,
        open:function(o){
            o.where("");
            o.orderby("DESCRIPTION");
        },
        notfound:function(){
            currprocessoid="";
            currinterprocesso="";
            curraggiuntivi="";
            $(prefix+"LB_PROCESSO").html("");
        },
        select:"SETINTERPROCESSO,EGOUTENTEID,DATIAGGIUNTIVI",
        onselect:function(o, d){
            $(prefix+"LB_PROCESSO").html("Processo: "+d["DESCRIPTION"]);
            currprocessoid=d["SYSID"];

            // GESTIONE INTERPROCESSO
            currinterprocesso=d["SETINTERPROCESSO"];
            oper_processo.visible(0);

            // GESTIONE NUOVA PRATICA
            /*
            if(d["EGOUTENTEID"]==_sessioninfo.userid)
                oper_new.enabled(1);
            else
                oper_new.enabled(0);
            */
            oper_new.enabled(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pratiche_inserters",
                    "data":{
                        "PROCESSOID":currprocessoid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            if(v.params["INSERTERS"].indexOf(_sessioninfo.userid)>=0)
                                oper_new.enabled(1);
                        }
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
            
            
            $.cookie(_sessioninfo.environ+"_pratiche_processo", currprocessoid, {expires:10000});

            // GESTIONE DATI AGGIUNTIVI
            curraggiuntivi=d["DATIAGGIUNTIVI"];
            configuracustom();

            setTimeout(function(){oper_refresh.engage()}, 100);
        },
        clear:function(){
            currprocessoid="";
            currinterprocesso="";
            oper_new.enabled(0);
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
        left:640,
        top:80,
        width:70,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            if(sospendirefresh)
                return;
            objgridsel.clear();
            if(currprocessoid!=""){
                var q="";
                var t=_likeescapize(txf_search.value());
                var richid=txf_richiedente.value();
                var proprie=chk_proprie.value();
                var aperte=chk_aperte.value();
                var datamin=txf_datemin.text();
                var datamax=txf_datemax.text();
                
                q="CONSISTENCY=0 AND PROCESSOID='"+currprocessoid+"'";
                if(t!="")
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(REFERENCE)] LIKE '%[=REFERENCE]%' )";
                if(richid!="")
                    q+=" AND RICHIEDENTEID='"+richid+"'";
                if(proprie)
                    q+=" AND STATOID IN ("+elencostati+")";
                if(aperte)
                    q+=" AND STATUS=0";
                if(datamin!="")
                    q+=" AND DATAINIZIO>=[:DATE("+datamin+")]";
                if(datamax!="")
                    q+=" AND DATAINIZIO<=[:TIME("+datamax+"235959)]";

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
            else{
                oper_new.enabled(0);
            }
        }
    });
    var oper_reset=$(prefix+"oper_reset").rylabel({
        left:640,
        top:140,
        width:70,
        caption:"Pulisci",
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_datemin.clear();
            txf_datemax.clear();
            txf_richiedente.clear();
            chk_proprie.value(1);
            chk_aperte.value(1);
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
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_PRATICHEJOIN",
        orderby:"TIMEINSERT",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:300},
            {id:"STATUS", caption:"C.", type:"?", width:20},
            {id:"RICHIEDENTE", caption:"Richiedente", width:170},
            {id:"ATTORE", caption:"Proprietario", width:170},
            {id:"DATAINIZIO", caption:"Inizio pratica", type:"/", width:100},
            {id:"DATAFINE", caption:"Fine pratica", type:"/", width:100},
            {id:"STATODESCR", caption:"Stato", width:200},
            {id:"STATUSTIME", caption:"Cambio Stato", type:":", width:140},
            {id:"REFERENCE", caption:"Protocollo", width:150}
        ],
        changerow:function(o,i){
            if(!flagriapertura){
                currpraticaid="";
                loadedpraticaCid="";
                loadedpraticaAid="";
                loadedpraticaDid="";
                loadedpraticaMid="";
                currstatoid="";
                currattoreid="";
                filestatoid="";
                curriniziale=false;
                currfinale=false;
                currchiusa=0;
                objtabs.enabled(tabcontesto, false);
                objtabs.enabled(tabattivita, false);
                objtabs.enabled(tabdettaglio, false);
                objtabs.enabled(taballegati, false);
                objtabs.enabled(tabmovimenti, false);
                oper_print.enabled(o.isselected());
                oper_delete.enabled(o.isselected());
                context="";
                context_attivita="";
                if(i>0){
                    o.solveid(i);
                }
            }
            else{
                flagriapertura=false;
            }
        },
        selchange:function(o, i){
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
            solalettura();
        },
        solveid:function(o,d){
            currpraticaid=d;
            oper_print.enabled(1);
            oper_delete.enabled(1);
            solalettura();
            objtabs.enabled(tabcontesto, true);
            objtabs.enabled(tabattivita, true);
            // I movimenti solo se esiste il conto
            //objtabs.enabled(tabmovimenti, true);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(tabcontesto);
            }
        },
        enter:function(){
            objtabs.currtab(tabcontesto);
        }
    });
    offsety=470;
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        width:120,
        caption:"Nuova pratica",
        button:true,
        click:function(o){
            winzProgress(formid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pratiche_insert",
                    "data":{
                        "PROCESSOID":currprocessoid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            var newid=v.params["PRATICAID"];
                            currstatoid=v.params["STATOID"];
                            currattoreid=v.params["ATTOREID"];
                            flagopen=true;
                            descropen=v.message;
                            objgridsel.splice(0, 0, newid);
                            // VIENE FATTO ALLA OPEN
                            //winzTimeoutMess(formid, v.success, v.message);
                       }
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
    
    var oper_print=$(prefix+"oper_print").rylabel({
        left:220,
        top:offsety,
        width:120,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_pratiche.php")
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:420,
        top:offsety,
        width:120,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare le pratiche selezionate?",
                ok:"Elimina",
                confirm:function(){
                    objgridsel.selengage(   // Elenco dei SYSID selezionati
                        function(o,s){
                            winzProgress(formid);
                            s=s.split("|");
                            var stats=[];
                            for(var i in s){    // Carico le istruzioni di cancellazione
                                stats[i]={
                                    "function":"quivers_deepdelete",
                                    "data":{
                                        "SYSID":s[i]
                                    }
                                };
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
                    );
                }
            });
        }
    });
    offsety+=40;

    $(prefix+"lb_warning").rylabel({left:20, top:offsety, caption:"* Campi obbligatori per abilitare l'inserimento"});

    // DEFINIZIONE TAB CONTESTO
    offsety=60;
    
    $(prefix+"LB_TITOLOSTATO").rylabel({left:20, top:offsety, caption:"<span class='rybox-title'>Sezione stato</span>"});
    offsety+=30;
    
    $(prefix+"LB_STATODESCRIPTION").css({position:"absolute",left:20,top:offsety,"width":700, height:150, background:"#FFFFFF", border:"1px solid silver",overflow:"auto"});
    offsety+=170;

    var oper_trans=$(prefix+"oper_trans").rylabel({
        left:20,
        top:offsety,
        width:150,
        caption:"Transizione di stato",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Effettuare una transizione di stato?",
                confirm:function(){
                    qv_idrequest(formid, {
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

    var oper_processo=$(prefix+"oper_processo").rylabel({
        left:200,
        top:offsety,
        width:150,
        caption:"Cambia processo",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Effettuare un cambio di precesso?",
                confirm:function(){
                    qv_idrequest(formid, {
                        table:"QW_PROCESSI", 
                        where:"SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currinterprocesso+"')",
                        title:"Scelta processo",
                        multiple:false,
                        onselect:function(d){
                            // PASSO L'ID DEL NUOVO PROCESSO
                            cambioprocesso(d["SYSID"]);
                        }
                    });
                }
            });
        }
    });

    var oper_chiusura=$(prefix+"oper_chiusura").rylabel({
        left:560,
        top:offsety,
        width:150,
        caption:"Chiusura pratica",
        button:true,
        click:function(o){
            if(currchiusa){
                winzMessageBox(formid, {
                    message:"Riaprire la pratica?",
                    confirm:function(){
                        gestionechiusura(0);
                    }
                });
            }
            else{
                winzMessageBox(formid, {
                    message:"Chiudere la pratica?",
                    confirm:function(){
                        gestionechiusura(1);
                    }
                });
            }
        }
    });
    offsety+=50;
    
    $(prefix+"LB_TITOLOPRATICA").rylabel({left:20, top:offsety, caption:"<span class='rybox-title'>Sezione pratica</span>"});
    offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:110, top:offsety, width:510, maxlen:100, datum:"C", tag:"DESCRIPTION"});
    var savey=offsety;
    offsety+=30;

    $(prefix+"LB_DATAINIZIO").rylabel({left:20, top:offsety, caption:"Inizio"});
    var txdatainizio=$(prefix+"DATAINIZIO").rydate({left:110, top:offsety, datum:"C", tag:"DATAINIZIO"});
    
    $(prefix+"LB_DATAFINE").rylabel({left:320, top:offsety, caption:"Fine"});
    var txdatafine=$(prefix+"DATAFINE").rydate({left:360, top:offsety, datum:"C", tag:"DATAFINE"});
    
    var oper_datafine=$(prefix+"oper_datafine").rylabel({
        left:490,
        top:offsety,
        width:120,
        caption:"Calcola scadenza",
        button:true,
        click:function(o){
            var b=txdatainizio.text();
            if(b=="")
                txdatainizio.value(_today());
            b=txdatainizio.text();
            // DEFINIZIONE DELLA DIALOGBOX
            var dlg=winzDialogGet(formid);
            var hangerid=dlg.hanger;
            var h="";
            var vK=[];
            winzDialogParams(dlg, {
                width:500,
                height:200,
                open:function(){
                    castFocus(formid+"dialog_number");
                },
                close:function(){
                    winzDisposeCtrl(formid, vK);
                    winzDialogFree(dlg);
                }
            });
            // CONTENUTO
            h+="<div class='winz_msgbox'>";
            h+="Inserire il numero di giorni da sommare alla data inizio:";
            h+="</div>";
            h+=winzAppendCtrl(vK, formid+"dialog_number");
            h+=winzAppendCtrl(vK, formid+"dialog_solari");
            h+=winzAppendCtrl(vK, formid+"dialog_lavorativi");
            $("#"+hangerid).html(h);
            // DEFINIZIONE CAMPI
            $("#"+formid+"dialog_number").rynumber({left:20, top:100, width:100, numdec:0, minvalue:0, formid:formid});
            $("#"+formid+"dialog_solari").rylabel({
                left:20,
                top:dlg.height-40,
                width:80,
                caption:"Solari",
                button:true,
                formid:formid,
                click:function(o){
                    var d=globalobjs[formid+"dialog_number"].value();
                    calcolascadenza(b, d, 0, 
                        function(d){
                            winzDialogClose(dlg);
                            txdatafine.value(d);
                        }
                    );
                }
            });
            $("#"+formid+"dialog_lavorativi").rylabel({
                left:120,
                top:dlg.height-40,
                width:80,
                caption:"Lavorativi",
                button:true,
                formid:formid,
                click:function(o){
                    var d=globalobjs[formid+"dialog_number"].value();
                    calcolascadenza(b, d, 1, 
                        function(d){
                            winzDialogClose(dlg);
                            txdatafine.value(d);
                        }
                    );
                }
            });
            // MOSTRO LA DIALOGBOX
            winzDialogOpen(dlg);
        }
    });
    
    offsety+=30;
    $(prefix+"LB_RICHIEDENTEID").rylabel({left:20, top:offsety, caption:"Richiedente"});
    $(prefix+"RICHIEDENTEID").ryhelper({
        left:110, top:offsety, width:180, datum:"C", tag:"RICHIEDENTEID", formid:formid, table:"QW_ATTORI", title:"Attori",
        open:function(o){
            o.where("");
        }
    });
    
    $(prefix+"LB_MEDIATOREID").rylabel({left:20, top:offsety+30, caption:"Mediatore"});
    $(prefix+"MEDIATOREID").ryhelper({
        left:110, top:offsety+30, width:180, datum:"C", tag:"MEDIATOREID", formid:formid, table:"QW_ATTORI", title:"Attori",
        open:function(o){
            o.where("");
        }
    });
    
    $(prefix+"LB_GANTT").rylabel({left:410, top:offsety, caption:"Gantt"});
    $(prefix+"GANTT").rycheck({left:460, top:offsety, datum:"C", tag:"GANTT"});
    
    $(prefix+"LB_INVIOEMAIL").rylabel({left:410, top:offsety+30, caption:"Email", title:"La pratica genera notifiche"});
    $(prefix+"INVIOEMAIL").rycheck({left:460, top:offsety+30, datum:"C", tag:"INVIOEMAIL"});
    
    offsety+=30;
    $(prefix+"LB_REFERENCE").rylabel({left:500, top:offsety, caption:"Protocollo"});
    var tx_reference=$(prefix+"REFERENCE").rytext({left:570, top:offsety, width:150});
    tx_reference.readonly(1);
    
    offsety+=30;
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=25;
    var context_registry=$(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    // GESTIONE DATI AGGIUNTIVI
    $(prefix+"paramshandle").css({position:"absolute", left:20, top:offsety+400, width:500});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:640,
        top:savey,
        width:70,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=qv_mask2object(formid, "C", currpraticaid);
            travasacustom(data);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pratiche_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            RYWINZ.modified(formid, 0);
                            context=txdescr.value();
                            objgridsel.dataload();
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
    
    var lbf_pendenti=$(prefix+"lbf_pendenti").rylabel({left:250, top:offsety, caption:"Solo pendenti"});
    var chk_pendenti=$(prefix+"chk_pendenti").rycheck({left:350, top:offsety,
        assigned:function(){
            setTimeout(function(){opera_refresh.engage();}, 100);
        }
    });
    chk_pendenti.value(0);
    
    var lbf_transizioni=$(prefix+"lbf_transizioni").rylabel({left:430, top:offsety, caption:"Con transizioni"});
    var chk_transizioni=$(prefix+"chk_transizioni").rycheck({left:532, top:offsety,
        assigned:function(){
            setTimeout(function(){opera_refresh.engage();}, 100);
        }
    });
    chk_transizioni.value(0);
    
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
            // GESTIONE SCOPE
            switch(gridattivita.provider()){
            case "sqlite":
                q+=" AND (SCOPE=0 OR (SCOPE=1 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"' OR SELECTEDID='"+currattoreid+"' )) OR (SCOPE=2 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"')))";
                break;
            default:
                q+=" AND (SCOPE=0 OR (SCOPE=1 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"' OR '"+currattoreid+"' IN (SELECT QVSELECTIONS.SELECTEDID FROM QVSELECTIONS WHERE QVSELECTIONS.PARENTID=QW_ATTIVITABROWSER.SETCONOSCENZA) )) OR (SCOPE=2 AND (BOWID='"+currattoreid+"' OR TARGETID='"+currattoreid+"')))";
            }
            // GESTIONE CONSISTENZA
            q+=" AND ( CONSISTENCY<>2 OR (CONSISTENCY=2 AND BOWID='"+currattoreid+"') )"
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(chk_pendenti.value())
                q+=" AND STATUS=0";
            if(chk_transizioni.value()==0)
                q+=" AND CONSISTENCY<>1";
            if(window.console&&_sessioninfo.debugmode){console.log(q)}
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
            opera_rispondi.enabled(0);
            opera_clona.enabled(0);
            opera_print.enabled(0);
            opera_archivia.enabled(0);
            solalettura();
            opera_archivia.caption("Archivia");
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
                            opera_clona.enabled(1);
                            opera_print.enabled(1);
                            opera_archivia.enabled(1);
                            if(currconsistency==2)
                                opera_archivia.caption("Elimina");
                            else
                                opera_archivia.caption("Archivia");
                            objtabs.enabled(tabdettaglio, true);
                            objtabs.enabled(taballegati, true);
                            if(flagopenD){
                                flagopenD=false;
                                objtabs.currtab(tabdettaglio);
                            }
                            solalettura();
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
                where:"(PROCESSOID='"+currprocessoid+"' OR PROCESSOID='') AND CONSISTENCY<>1",
                orderby:"PROCESSOID DESC,ORDINATORE,DESCRIPTION",
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
                            "MOTIVEID":motiveid
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
                    "TARGETID":currbowid
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
    
    var opera_clona=$(prefix+"opera_clona").rylabel({
        left:190,
        top:offsety,
        caption:"Clona",
        button:true,
        click:function(o){
            var stats=[];
            var istr=0;
            stats[istr++]={
                "function":"attivita_insert",
                "data":{
                    "OPERATION":"CLONE",
                    "PRATICAID":currpraticaid,
                    "REFARROWID":currattivid
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
    
    var opera_archivia=$(prefix+"opera_archivia").rylabel({
        left:650,
        top:offsety,
        caption:"Archivia",
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
            else{
                winzMessageBox(formid, {
                    message:"Archiviare l'attivit&agrave; selezionata?<br>Un'attivit&agrave archiviata non &egrave; pi&ugrave; visibile nel sistema.",
                    confirm:function(){
                        winzProgress(formid);
                        RYWINZ.modified(formid, 0);
                        $.post(_cambusaURL+"ryquiver/quiver.php", 
                            {
                                "sessionid":_sessionid,
                                "env":_sessioninfo.environ,
                                "function":"arrows_update",
                                "data":{
                                    "SYSID":currattivid,
                                    "AVAILABILITY":2
                                }
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
    
    $(prefix+"preview").css({"position":"absolute", "left":20, "top":offsety, "width":"180mm"});
    
   // DEFINIZIONE TAB DETTAGLIO
    offsety=100;
    var lb_dett_context=$(prefix+"dett_context").rylabel({left:20, top:50, caption:""});

    $(prefix+"LBD_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var txd_descr=$(prefix+"D_DESCRIPTION").rytext({left:80, top:offsety, width:520, datum:"D", tag:"DESCRIPTION"});
    offsety+=30;

    $(prefix+"LBD_BOWID").rylabel({left:20, top:offsety, caption:"Committente"});
    var txd_bowid=$(prefix+"D_BOWID").ryhelper({
        left:110, top:offsety, width:220, datum:"D", tag:"BOWID", formid:formid, table:"QW_ATTORI", title:"Scelta committente",
        open:function(o){
            o.where("");
        }
    });
    $(prefix+"LBD_TARGETID").rylabel({left:20, top:offsety+30, caption:"Destinazione"});
    var txd_targetid=$(prefix+"D_TARGETID").ryhelper({
        left:110, top:offsety+30, width:220, datum:"D", tag:"TARGETID", formid:formid, table:"QW_ATTORI", title:"Scelta destinatario",
        open:function(o){
            o.where("");
        }
    });

    $(prefix+"LBD_REFERENCE").rylabel({left:370, top:offsety, caption:"Protocollo"});
    var txd_reference=$(prefix+"D_REFERENCE").rytext({left:450, top:offsety, width:150});
    txd_reference.readonly(1);

    $(prefix+"LBD_AMOUNT").rylabel({left:370, top:offsety+30, caption:"Impiego"});
    var txd_genreid=$(prefix+"D_GENREID").rylist({left:450, top:offsety+30, width:70, datum:"D", tag:"GENREID"})
        .additem({caption:"ore", key:genereore})
        .additem({caption:"giorni", key:generegiorni});
    var txd_amount=$(prefix+"D_AMOUNT").rynumber({left:530, top:offsety+30, width:70, numdec:0, minvalue:0, datum:"D", tag:"AMOUNT"});
    
    offsety+=60;
    
    $(prefix+"LBD_BEGINTIME").rylabel({left:20, top:offsety, caption:"Inizio"});
    var txd_begin=$(prefix+"D_BEGINDATE").rydate({left:80, top:offsety, datum:"D", tag:"BOWTIME",
        enter:function(o){
            if(o.text()!=""){
                var n=txd_amount.value();
                if(txd_genreid.value()==1){
                    n=Math.round(n/8, 0);
                }
                calcolascadenza(o.text(), n, 0, 
                    function(d){
                        txd_end.value(d);
                    }
                );
            }
        }
    });
    txd_begin.link(
        $(prefix+"D_BEGINTIME").rytime({left:210, top:offsety})
    );
    
    $(prefix+"LBD_ENDTIME").rylabel({left:20, top:offsety+30, caption:"Fine"});
    var txd_end=$(prefix+"D_ENDDATE").rydate({left:80, top:offsety+30, defaultvalue:"99991231", datum:"D", tag:"TARGETTIME"});
    txd_end.link(
        $(prefix+"D_ENDTIME").rytime({left:210, top:offsety+30})
    );

    $(prefix+"LBD_IMPORTANZA").rylabel({left:370, top:offsety, caption:"Priorit&agrave;"});
    $(prefix+"D_IMPORTANZA").rylist({left:450, top:offsety, width:150, datum:"D", tag:"IMPORTANZA"})
    .additem({caption:"Bassa", key:0})
    .additem({caption:"Media", key:1})
    .additem({caption:"Alta", key:2});

    $(prefix+"LBD_STATUS").rylabel({left:370, top:offsety+30, caption:"Stato"});
    var txd_status=$(prefix+"D_STATUS").rylist({left:450, top:offsety+30, width:150})
    .additem({caption:"In attesa...", key:0})
    .additem({caption:"25% completato", key:1})
    .additem({caption:"50% completato", key:2})
    .additem({caption:"75% completato", key:3})
    .additem({caption:"Completo", key:4})
    .additem({caption:"Verificato", key:5});
    //txd_status.enabled(0);
    
    offsety+=70;
    
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
                            gridattivita.dataload(
                                function(){
                                    caricaanteprima(true);
                                }
                            );
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
                            currconsistency=0;
                            opera_archivia.caption("Archivia");
                            gridattivita.dataload(
                                function(){
                                    caricaattivita(
                                        function(){
                                            objtabs.currtab(tabattivita);
                                            caricaanteprima(true);
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
                    if(done!=missing){done()}
                }
            );
        }
    });

    var operd_provvisoria=$(prefix+"operd_provvisoria").rylabel({
        left:620,
        top:160,
        caption:"St. provvisorio",
        button:true,
        click:function(o){
            winzProgress(formid);
            // ISTRUZIONE DI SALVATAGGIO DEL DETTAGLIO MODIFICATO
            var data=qv_mask2object(formid, "D", currattivid);
            data["PRATICAID"]=currpraticaid;
            data["STATUS"]=0;
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
                            txd_status.value(1);
                            RYWINZ.modified(formid, 0);
                            gridattivita.dataload();
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

    var operd_completa=$(prefix+"operd_completa").rylabel({
        left:620,
        top:190,
        caption:"St. completo&nbsp;&nbsp;",
        button:true,
        click:function(o){
            winzProgress(formid);
            // AGGIORNO LA DATA FINE
            txd_end.value(_today());
            if(txd_begin.value()>txd_end.value()){
                txd_begin.value(_today())
            }
            // ISTRUZIONE DI SALVATAGGIO DEL DETTAGLIO MODIFICATO
            var data=qv_mask2object(formid, "D", currattivid);
            data["PRATICAID"]=currpraticaid;
            data["STATUS"]=1;
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
                            txd_status.value(5);
                            RYWINZ.modified(formid, 0);
                            gridattivita.dataload();
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
    
    var operd_verificata=$(prefix+"operd_verificata").rylabel({
        left:620,
        top:220,
        caption:"St. verificato&nbsp;&nbsp;",
        button:true,
        click:function(o){
            winzProgress(formid);
            // ISTRUZIONE DI SALVATAGGIO DEL DETTAGLIO MODIFICATO
            var data=qv_mask2object(formid, "D", currattivid);
            data["PRATICAID"]=currpraticaid;
            data["STATUS"]=2;
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
                            txd_status.value(6);
                            RYWINZ.modified(formid, 0);
                            gridattivita.dataload();
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
    offsety=80;
    var lb_allegati_context=$(prefix+"allegati_context").rylabel({left:20, top:50, caption:""});

    $(prefix+"LB_DOCUMENTISTATO").rylabel({left:20, top:offsety, caption:"<b>Documenti di stato</b>"});
    offsety+=30;
    
    var griddocstato=$(prefix+"griddocstato").ryque({
        left:20,
        top:offsety,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QWFILES",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:230}
        ],
        orderby: "AUXTIME DESC, DESCRIPTION",
        changerow:function(o,i){
            filestatoid="";
            operf_compila.enabled(0);
            operf_allega.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o, d){
            RYQUE.query({
                sql:"SELECT * FROM QWFILES WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        filestatoid=v[0]["FILEID"];
                        operf_compila.enabled(1);
                        operf_allega.enabled(1);
                        solalettura();
                    }catch(e){}
                } 
            });
        },
        enter:function(o, r){
            qv_filedownload(formid, o);
        }
    });
    var operf_refresh=$(prefix+"operf_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            griddocstato.refresh();
        }
    });
    var operf_compila=$(prefix+"operf_compila").rylabel({
        left:430,
        top:offsety+50,
        caption:"Download compilato",
        button:true,
        click:function(o){
            documentocompila(false);
        }
    });
    var operf_allega=$(prefix+"operf_allega").rylabel({
        left:430,
        top:offsety+100,
        caption:"Allega diretto",
        button:true,
        click:function(o){
            documentocompila(true);
        }
    });
    offsety+=320;
    
    $(prefix+"LB_DOCUMENTIALLEGATI").rylabel({left:20, top:offsety, caption:"<b>Documenti allegati</b>"});
    offsety+=30;
    
    var griddocs=$(prefix+"griddocs").ryque({
        left:20,
        top:offsety,
        width:400,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWFILES",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:230}
        ],
        orderby: "DESCRIPTION",
        changerow:function(o,i){
            fileattivid="";
            fileattivdescr="";
            fileattivpath="";
            oper_filedownload.enabled(0);
            oper_filesignature.enabled(0);
            oper_filedelete.enabled(o.isselected());
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            oper_filedelete.enabled(o.isselected());
        },
        ready:function(){
            loadedfileid=currattivid;
        },
        solveid:function(o, d){
            RYQUE.query({
                sql:"SELECT * FROM QWFILES WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        fileattivid=v[0]["FILEID"];
                        fileattivdescr=v[0]["DESCRIPTION"];
                        fileattivpath=v[0]["SUBPATH"];
                        oper_filedownload.enabled(1);
                        oper_filesignature.enabled(1);
                        oper_filedelete.enabled(1);
                        solalettura();
                    }catch(e){}
                } 
            });
        },
        enter:function(o, r){
            qv_filedownload(formid, o)
        }
    });

    var oper_fileinsert=$(prefix+"oper_fileinsert").ryupload({
        left:430,
        top:offsety+20,
        width:300,
        environ:_tempenviron,
        complete:function(id, name, ret){
            //$(prefix+"oper_fileinsert .qq-upload-success , .qq-upload-fail").remove();
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":[
                        {
                            "function":"files_insert",
                            "data":{
                                "IMPORTNAME":name,
                                "SUBPATH":strRight(currattivid, 2)
                            },
                            "pipe":{
                                "FILEID":"SYSID"
                            }
                        },
                        {
                            "function":"files_attach",
                            "data":{
                                "TABLENAME":"QVARROWS",
                                "RECORDID":currattivid
                            }
                        }
                    ]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success){
                            // POSIZIONAMENTO SUL NUOVO DOCUMENTO
                            var newid=v.SYSID;
                            griddocs.splice(0, 0, newid,
                                function(){
                                    gridattivita.dataload();
                                }
                            );
                        }
                        winzTimeoutMess(formid, parseInt(v.success), v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });
    var oper_filedownload=$(prefix+"oper_filedownload").rylabel({
        left:430,
        top:offsety+160,
        caption:"Download",
        button:true,
        click:function(o){
            qv_filedownload(formid, griddocs);
        }
    });
    var oper_filesignature=$(prefix+"oper_filesignature").rylabel({
        left:430,
        top:offsety+210,
        caption:"Firma digitale",
        button:true,
        click:function(o){
            var descr=fileattivdescr+" (firmato)";
            descr=descr.substr(0,100);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":[
                        {
                            "function":"files_export",
                            "data":{"SYSID":fileattivid,"SIGNATURE":1},
                            "pipe":{"IMPORTNAME":"#EXPORT"}
                        },
                        {
                            "function":"files_insert",
                            "data":{
                                "DESCRIPTION":descr,
                                //"IMPORTNAME":name,
                                "SUBPATH":fileattivpath
                            },
                            "pipe":{
                                "FILEID":"SYSID"
                            }
                        },
                        {
                            "function":"files_attach",
                            "data":{
                                "TABLENAME":"QVARROWS",
                                "RECORDID":currattivid
                            }
                        }
                    ]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success){
                            griddocs.refresh();
                        }
                        winzTimeoutMess(formid, parseInt(v.success), v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });
    var oper_filedelete=$(prefix+"oper_filedelete").rylabel({
        left:430,
        top:offsety+260,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_filedelete(formid, griddocs);
        }
    });

    // DEFINIZIONE TAB MOVIMENTI
    var lb_movimenti_context=$(prefix+"movimenti_context").rylabel({left:20, top:50, caption:""});
    var gridmovimenti=$(prefix+"gridmovimenti").ryque({
        left:20,
        top:80,
        width:400,
        height:430,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_MOVIMENTI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:140},
            {id:"AMOUNT",caption:"Importo",width:110,type:"2"},
            {id:"BOWID",caption:"",width:0},
            {id:"AUXTIME",caption:"Data",width:90,type:"/"}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            qv_maskclear(formid, "M");
            tx_movauxtime.value(_today());
            qv_maskenabled(formid, "M", 0);
            tx_movauxtime.enabled(0);
            operm_unsaved.visible(0);
            operm_update.enabled(0);
            operm_delete.enabled(0);
            currmovid="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currmovid=d;
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento movimento: "+currmovid)}
            RYQUE.query({
                sql:"SELECT DESCRIPTION,BOWID,BOWTIME,TARGETID,TARGETTIME,AMOUNT,MOTIVEID FROM QW_MOVIMENTI WHERE SYSID='"+currmovid+"'",
                ready:function(v){
                    qv_maskenabled(formid, "M", 1);
                    tx_movauxtime.enabled(1);
                    operm_update.enabled(1);
                    operm_delete.enabled(1);
                    if(v[0]["BOWID"]==movcontoid)
                        tx_movauxtime.value(v[0]["BOWTIME"]);
                    else
                        tx_movauxtime.value(v[0]["TARGETTIME"]);
                    tx_movmotiveid.value(v[0]["MOTIVEID"]);
                    qv_object2mask(formid, "M", v[0]);
                    operm_unsaved.visible(0);
                    solalettura();
                    if(flagmovnuovo){
                        flagmovnuovo=false;
                        castFocus(prefix+"MOVDESCRIPTION");
                    }
                }
            });
        },
        before:function(o, d){
            if(movcontoid!=""){
                for(var i in d){
                    if(d[i]["BOWID"]==movcontoid){
                        d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                    }
                }
            }
        }
    });
    offsety=80;
    $(prefix+"lbm_search").rylabel({left:430, top:offsety, caption:"Ricerca"});offsety+=20;
    var txm_search=$(prefix+"txm_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            setTimeout(
                function(){
                    operm_refresh.engage();
                }, 100
            );
        }
    });offsety+=30;
    
    var operm_refresh=$(prefix+"operm_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            gridmovimenti.clear()
            var q="";
            var t=_likeescapize(txm_search.value());
            
            q+="SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='"+currpraticaid+"')";
            q+=" AND (BOWID='"+movcontoid+"' OR TARGETID='"+movcontoid+"')";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            
            gridmovimenti.where(q);
            gridmovimenti.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });offsety+=50;
    
    var operm_new=$(prefix+"operm_new").rylabel({
        left:430,
        top:offsety,
        caption:"Nuovo movimento",
        button:true,
        click:function(o){
            if(movcontoid==""){
                winzMessageBox(formid, "Conto non presente in anagrafica");
                return;
            }
            qv_idrequest(formid, {
                table:"QW_CAUSALI", 
                title:"Nuovo movimento - Scelta causale",
                where:"(REFERENCEID='' OR REFERENCEID='"+movcontoid+"')",
                orderby:"SOTTOTIPO,DESCRIPTION",
                onselect:function(d){
                    winzProgress(formid);
                    var motid=d["SYSID"];
                    var stats=[];
                    var istr=0;
                    if(RYWINZ.modified(formid)){
                        // ISTRUZIONE DI SALVATAGGIO DEL MOVIMENTO MODIFICATO
                        var datasave=qv_mask2object(formid, "M", currmovid);
                        var auxtime=tx_movauxtime.text()
                        datasave["BOWTIME"]=auxtime;
                        datasave["TARGETTIME"]=auxtime;
                        datasave["AUXTIME"]=auxtime;
                        datasave["MOTIVEID"]=tx_movmotiveid.value();
                        stats[istr++]={
                            "function":"arrows_update",
                            "data":datasave
                        };
                    }
                    // ISTRUZIONE DI INSERIMENTO NUOVO MOVIMENTO
                    var data = new Object();
                    data["DESCRIPTION"]="(nuovo movimento)";
                    data["TYPOLOGYID"]=movimentitipo;
                    data["GENREID"]=movgenereid;
                    data["MOTIVEID"]=motid;
                    data["REFERENCEID"]=movcontoid;
                    var auxtime=tx_movauxtime.text()
                    data["BOWTIME"]=auxtime;
                    data["TARGETTIME"]=auxtime;
                    data["AUXTIME"]=auxtime;
                    data["STATOID"]=currstatoid;
                    stats[istr++]={
                        "function":"arrows_insert",
                        "data":data,
                        "pipe":{"ARROWID":"SYSID"},
                        "return":{"ARROWID":"SYSID"}
                    };
                    // ISTRUZIONE DI AGGANCIO ALLA PRATICA
                    stats[istr++]={
                        "function":"quivers_add",
                        "data":{"QUIVERID":currpraticaid}
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
                                    // REPERISCO IL SYSID DELLA NUOVA FRECCIA
                                    var newid=v.infos["ARROWID"];
                                    // POPOLO IL GRID COL NUOVO MOVIMENTO
                                    flagmovnuovo=true;
                                    gridmovimenti.splice(0, 0, newid);
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
    });offsety+=50;

    $(prefix+"LB_MOVDESCRIPTION").rylabel({left:430, top:offsety, caption:"Descr."});
    var tx_movdescription=$(prefix+"MOVDESCRIPTION").rytext({left:490, top:offsety, width:240, datum:"M", tag:"DESCRIPTION",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=25;

    $(prefix+"LB_MOVAUXTIME").rylabel({left:430, top:offsety, caption:"Data"});
    var tx_movauxtime=$(prefix+"MOVAUXTIME").rydate({left:490, top:offsety,
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=25;
    
    $(prefix+"LB_MOVMOTIVEID").rylabel({left:430, top:offsety, caption:"Causale"});
    var tx_movmotiveid=$(prefix+"MOVMOTIVEID").ryhelper({
        left:490, top:offsety, width:200, formid:formid, table:"QW_CAUSALI", title:"Causali",
        open:function(o){
            o.where("(REFERENCEID='' OR REFERENCEID='[=REFERENCEID]')");
            o.orderby("SOTTOTIPO,DESCRIPTION");
            o.args({"REFERENCEID":movcontoid});
        },
        assigned:function(){
            operm_unsaved.visible(1);
        }
    });
    tx_movmotiveid.enabled(0);
    offsety+=25;
    
    $(prefix+"LB_MOVGENREID").rylabel({left:430, top:offsety, caption:"Divisa"});
    var tx_movgenreid=$(prefix+"MOVGENREID").ryhelper({
        left:490, top:offsety, width:200, formid:formid, table:"QVGENRES", title:"Divise",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":movgeneretipo});
        },
        select:"ROUNDING",
        onselect:function(o, d){
            tx_movamount.numdec( parseInt(d["ROUNDING"]) );
        }
    });
    tx_movgenreid.enabled(0);
    offsety+=25;
    
    $(prefix+"LB_MOVAMOUNT").rylabel({left:430, top:offsety, caption:"Importo"});
    var tx_movamount=$(prefix+"MOVAMOUNT").rynumber({left:490, top:offsety, width:200, numdec:2, minvalue:0, datum:"M", tag:"AMOUNT",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=35;

    var operm_update=$(prefix+"operm_update").rylabel({
        left:430,
        top:offsety,
        caption:"Salva movimento",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=qv_mask2object(formid, "M", currmovid);
            var auxtime=tx_movauxtime.text()
            data["BOWTIME"]=auxtime;
            data["TARGETTIME"]=auxtime;
            data["AUXTIME"]=auxtime;
            data["MOTIVEID"]=tx_movmotiveid.value();
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
                            gridmovimenti.dataload();
                            operm_unsaved.visible(0);
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
    var operm_unsaved=$(prefix+"operm_unsaved").rylabel({left:565, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    operm_unsaved.visible(0);
    
    var operm_delete=$(prefix+"operm_delete").rylabel({
        left:430,
        top:470,
        caption:"Elimina movimento",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare il movimento selezionato?",
                ok:"Elimina",
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
                                    "data":{"QUIVERID":currpraticaid, "ARROWID":currmovid}
                                },
                                {
                                    "function":"arrows_delete",
                                    "data":{"SYSID":currmovid, "PRATICAID":currpraticaid}
                                }
                            ]
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    gridmovimenti.refresh();
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
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:40,position:"relative",
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Attivit&agrave;"},
            {title:"Dettaglio"},
            {title:"Allegati"},
            {title:"Movimenti"}
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
            else if(p==tabdettaglio){
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
            else if(p==tabmovimenti){
                // PROVENGO DAI MOVIMENTI
                flagsuspend=qv_changemanagement(formid, objtabs, operm_update, {
                    abandon:function(){
                        loadedpraticaMid="";
                    }
                });
            }
            if(i==1){
                loadedpraticaCid="";
                loadedpraticaAid="";
                loadedpraticaDid="";
                loadedpraticaMid="";
                currstatoid="";
            }
            else if(i==tabcontesto){
                if(currpraticaid==loadedpraticaCid){
                    flagsuspend=true;
                }
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
            else if(i==tabmovimenti){
                if(currpraticaid==loadedpraticaMid){
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
                    qv_maskclear(formid, "C");
                    tx_reference.clear();
                    $(prefix+"LB_STATODESCRIPTION").html("");
                    currstatoid=""; // Forzo il caricamento dei dati
                    caricapratica(
                        function(d){
                            // ASSEGNAMENTO CAMPI
                            qv_object2mask(formid, "C", d);
                            tx_reference.value(d["REFERENCE"]);
                            loadedpraticaCid=currpraticaid;
                            // GESTIONE INTERPROCESSO
                            RYQUE.query({
                                sql:"SELECT SYSID FROM QW_PROCESSI WHERE SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currinterprocesso+"')",
                                ready:function(v){
                                    if(descropen!=""){
                                        // HO INSERITO UNA NUOVA PRATICA E SOLO ORA ABILITO LA MASCHERA
                                        winzTimeoutMess(formid, 1, descropen);
                                        descropen="";
                                    }
                                    else{
                                        winzClearMess(formid);
                                    }
                                    oper_processo.visible(v.length>0);
                                    castFocus(prefix+"DESCRIPTION");
                                }
                            });
                        }
                    );
                    break;
                case 3:
                    // CARICAMENTO ATTIVITA
                    lb_attivita_context.caption("Contesto: "+context);
                    caricapratica(
                        function(d){
                            lb_attivita_context.caption("Contesto: "+context);
                            loadedpraticaAid=currpraticaid;
                            winzClearMess(formid);
                            setTimeout(function(){opera_refresh.engage()}, 100);
                        }
                    );
                    break;
                case 4:
                    // CARICAMENTO DETTAGLIO
                    lb_dett_context.caption("Contesto: "+context);
                    caricaattivita();
                    break;
                case 5:
                    // CARICAMENTO DOCUMENTI
                    lb_allegati_context.caption("Contesto: "+context+" - "+context_attivita);
                    griddocstato.clear();
                    griddocs.clear();
                    caricaattivita(
                        function(){
                            griddocstato.where("RECORDID='"+currstatoid+"' AND TABLENAME='QVOBJECTS' AND IMPORTNAME<>''");
                            griddocstato.query({
                                ready:function(){
                                    griddocs.where("RECORDID='"+currattivid+"' AND TABLENAME='QVARROWS'");
                                    griddocs.query();
                                }
                            });
                        }
                    );
                    break;
                case 6:
                    // CARICAMENTO MOVIMENTI
                    lb_movimenti_context.caption("Contesto: "+context);
                    caricapratica(
                        function(d){
                            lb_movimenti_context.caption("Contesto: "+context);
                            loadedpraticaMid=currpraticaid;
                            setTimeout(function(){operm_refresh.engage()}, 100);
                        }
                    );
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(tabselezione);
    objtabs.enabled(tabcontesto, false);
    objtabs.enabled(tabattivita, false);
    objtabs.enabled(tabdettaglio, false);
    objtabs.enabled(taballegati, false);
    objtabs.enabled(tabmovimenti, false);
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
            qv_queuequery[formid+"_2"]={
                //"sql":buildstati(),
                "fsql":buildstati,
                "back":function(v){
                    elencostati="";
                    for(var i in v){
                        if(elencostati!="")
                            elencostati+=",";
                        elencostati+="'"+v[i]["STATOID"]+"'";
                    }
                    if(elencostati=="")
                        elencostati="''";
                    txf_processo.value($.cookie(_sessioninfo.environ+"_pratiche_processo"), true);
                    winzClearMess(formid);
                    txf_search.focus();
                }
            };
            qv_queuemanager();
        }
    );
    function transizionestato(transid){
        winzProgress(formid);
        var stats=[];
        var istr=0;
        // ISTRUZIONE DI SALVATAGGIO PRATICA
        var data=qv_mask2object(formid, "C", currpraticaid);
        travasacustom(data);
        stats[istr++]={
            "function":"pratiche_update",
            "data":data
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
                        var nuovostatoid=v.params["NUOVOSTATOID"];
                        var transattorebow=v.params["ATTOREBOWID"];
                        var transattoretarget=v.params["ATTORETARGETID"];
                        context=txdescr.value();
                        lb_attivita_context.caption("Contesto: "+context);
                        lb_dett_context.caption("Contesto: "+context);
                        lb_allegati_context.caption("Contesto: "+context);
                        lb_movimenti_context.caption("Contesto: "+context);
                        if(transattorebow!=transattoretarget){
                            // IL NUOVO ATTORE E' DIVERSO: 
                            // AGGIORNO IL GRID DI SELEZIONE E MI CI POSIZIONO
                            objgridsel.query({
                                ready:function(){
                                    currstatoid="";
                                    objtabs.currtab(tabselezione);
                                    objtabs.enabled(tabcontesto, false);
                                    objtabs.enabled(tabattivita, false);
                                    objtabs.enabled(tabdettaglio, false);
                                    objtabs.enabled(taballegati, false);
                                    objtabs.enabled(tabmovimenti, false);
                                    txf_search.focus();
                                }
                            });
                        }
                        else{
                            // L'ATTORE NON E' CAMBIATO (NOTIFICA A SE STESSO)
                            currstatoid=nuovostatoid;
                            objtabs.enabled(tabmovimenti, false);
                            objgridsel.dataload(
                                function(){
                                    letturastato();
                                }
                            );
                            // LE MASCHERE VANNO AGGIORNATE
                            loadedpraticaAid="";
                            loadedpraticaDid="";
                        }
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
    function cambioprocesso(procid){
        winzProgress(formid);
        var stats=[];
        var istr=0;
        // ISTRUZIONE DI SALVATAGGIO PRATICA
        var data=qv_mask2object(formid, "C", currpraticaid);
        travasacustom(data);
        stats[istr++]={
            "function":"pratiche_update",
            "data":data
        };
        // ISTRUZIONE DI CAMBIO PROCESSO
        stats[istr++]={
            "function":"pratiche_cambioproc",
            "data":{
                "PRATICAID":currpraticaid,
                "PROCESSOID":procid
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
                        RYWINZ.modified(formid, 0);
                        // AGGIORNO IL GRID DI SELEZIONE E MI CI POSIZIONO
                        objgridsel.query({
                            ready:function(){
                                objtabs.currtab(tabselezione);
                                objtabs.enabled(tabcontesto, false);
                                objtabs.enabled(tabattivita, false);
                                objtabs.enabled(tabdettaglio, false);
                                objtabs.enabled(taballegati, false);
                                objtabs.enabled(tabmovimenti, false);
                                txf_search.focus();
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
    function documentocompila(allega){
        var ind=griddocstato.index();
        if(ind>0){
            griddocstato.solveid( ind,
                function(o, id){
                    var templateid=id;
                    filemerging(allega, templateid);
                }
            );
        }
    }
    function filemerging(allega, templateid){
        RYQUE.query({
            // LEGGO LE CARATTERISTICHE DEL FILE ALLEGATO
            sql:"SELECT QVTABLEFILE.FILEID AS FILEID, QVFILES.IMPORTNAME AS IMPORTNAME FROM QVTABLEFILE INNER JOIN QVFILES ON QVFILES.SYSID=QVTABLEFILE.FILEID WHERE QVTABLEFILE.SYSID='"+templateid+"'",
            ready:function(v){
                var importname=v[0]["IMPORTNAME"];
                var fileid=v[0]["FILEID"];
                // ESEGUO UN EXPORT CON MERGING
                $.post(_cambusaURL+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "function":"files_export",
                        "data":{
                            "SYSID":fileid,
                            "MERGE":{
                                "_CONTEXT":"PRATICHE",
                                "PRATICAID":currpraticaid,
                                "ATTIVITAID":currattivid
                            }
                        }
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success>0){
                                var n=v["params"]["EXPORT"];
                                if(allega){
                                    // ALLEGO IL TEMPLATE COMPILATO DIRETTAMENTE ALLA ATTIVITA
                                    var pathid=strRight(currpraticaid, 2);
                                    file_attach(griddocs, n, importname, pathid, currattivid);
                                }
                                else{
                                    // ESEGUO UN DOWNLOAD DEL TEMPLATE COMPILATO
                                    if(window.console&&_sessioninfo.debugmode){console.log("Percorso file: "+_temporaryURL+n)}
                                    var h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+_temporaryURL+n;
                                    $("#winz-iframe").prop("src", h);
                                    winzTimeoutMess(formid, parseInt(v.success), v.message);
                                }
                            }
                            else{
                                if(window.console&&_sessioninfo.debugmode){console.log("Percorso file: "+_temporaryURL+n)}
                                winzTimeoutMess(formid, parseInt(v.success), v.message);
                            }
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
    function file_attach(objgrid, name, descr, pathid, recordid){
        $.post(_cambusaURL+"ryquiver/quiver.php", 
            {
                "sessionid":_sessionid,
                "env":_sessioninfo.environ,
                "program":[
                    {
                        "function":"files_insert",
                        "data":{
                            "DESCRIPTION":descr,
                            "IMPORTNAME":name,
                            "SUBPATH":pathid
                        },
                        "pipe":{
                            "FILEID":"SYSID"
                        }
                    },
                    {
                        "function":"files_attach",
                        "data":{
                            "TABLENAME":"QVARROWS",
                            "RECORDID":recordid
                        }
                    }
                ]
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success){
                        // POSIZIONAMENTO SUL NUOVO DOCUMENTO
                        var newid=v.SYSID;
                        objgrid.query({
                            ready:function(v){
                                objgrid.search({
                                        "where": _ajaxescapize("SYSID='"+newid+"'")
                                    },
                                    function(d){
                                        var ind=0;
                                        try{
                                            var v=$.parseJSON(d);
                                            ind=v[0];
                                            
                                        }
                                        catch(e){
                                            alert(d);
                                        }
                                        objgrid.index(ind);
                                    }
                                );
                            }
                        });
                    }
                    winzTimeoutMess(formid, parseInt(v.success), v.message);
                }
                catch(e){
                    winzClearMess(formid);
                    alert(d);
                }
            }
        );
    }
    function caricapratica(after){
        if(currstatoid==""){
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currpraticaid)}
            winzProgress(formid);
            RYQUE.query({
                sql:"SELECT * FROM QW_PRATICHE WHERE SYSID='"+currpraticaid+"'",
                ready:function(v){
                    // DETERMINAZIONE E LETTURA DELLO STATO
                    currstatoid=v[0]["STATOID"];
                    var moredata={};
                    if(v[0]["MOREDATA"]!=""){
                        moredata=$.parseJSON(v[0]["MOREDATA"]);
                    }
                    mascheracustom(moredata);
                    context=v[0]["DESCRIPTION"];
                    if(!_sessioninfo.admin){
                        if(elencostati.indexOf(currstatoid)==-1){
                            winzClearMess(formid);
                            objtabs.currtab(tabselezione);
                            objtabs.enabled(tabcontesto, false);
                            objtabs.enabled(tabattivita, false);
                            objtabs.enabled(tabdettaglio, false);
                            objtabs.enabled(taballegati, false);
                            objtabs.enabled(tabmovimenti, false);
                            alert("Non "+_utf8("e")+" consentito aprire la pratica ai non proprietari");
                            return;
                        }
                    }
                    // GESTIONE DELLO STATUS
                    currchiusa=_bool(v[0]["STATUS"]);
                    if(currchiusa)
                        oper_chiusura.caption("Riapertura pratica");
                    else
                        oper_chiusura.caption("Chiusura pratica");
                    // LETTURA DELLO STATO
                    letturastato(after, v[0]);
                }
            });
        }
        else{
            solalettura();
            after({});
        }
    }
    function gestionechiusura(newstatus){
        winzProgress(formid);
        var stats=[];
        var istr=0;
        if(newstatus==1){
            // SALVATAGGIO
            var data=qv_mask2object(formid, "C", currpraticaid);
            travasacustom(data);
            stats[istr++]={
                "function":"pratiche_update",
                "data":data
            };
        }
        // CAMBIO DI STATO
        stats[istr++]={
            "function":"pratiche_chiusura",
            "data":{
                "PRATICAID":currpraticaid,
                "STATUS":newstatus
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
                        context=txdescr.value();
                        loadedpraticaCid="";
                        loadedpraticaAid="";
                        loadedpraticaDid="";
                        loadedpraticaMid="";
                        RYWINZ.modified(formid, 0);
                        objgridsel.query({
                            ready:function(){
                                if(newstatus==1){
                                    objtabs.currtab(tabselezione);
                                    txf_search.focus();
                                }
                                else{
                                    currchiusa=0;
                                    oper_chiusura.caption("Chiusura pratica");
                                    flagriapertura=true;
                                    solalettura();
                                }
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
    function letturastato(after, dt){
        solalettura();
        RYQUE.query({
            sql:"SELECT * FROM QW_PROCSTATIJOIN WHERE SYSID='"+currstatoid+"'",
            ready:function(v){
                var t="<span style='font-size:16px;'>"+v[0]["DESCRIPTION"]+"</span>";
                t+="<br>";
                t+=v[0]["REGISTRY"];
                $(prefix+"LB_STATODESCRIPTION").html(t);
                curriniziale=_bool(v[0]["INIZIALE"]);
                currfinale=_bool(v[0]["FINALE"]);
                currattoreid=v[0]["ATTOREID"];
                movcontoid=v[0]["CONTOID"];
                movgenereid=v[0]["GENREID"];
                if(movcontoid!="" && movgenereid!=""){
                    objtabs.enabled(tabmovimenti, true);
                }
                oper_chiusura.visible(currfinale);
                // ASSEGNO UNA VOLTA SOLA LA DIVISA DEL CONTO E INIZIALIZZO LA DATA
                if(tx_movgenreid.value()==""){
                    tx_movgenreid.value(movgenereid);
                    tx_movauxtime.value(_today());
                }
                RYWINZ.modified(formid, 0);
                winzClearMess(formid);
                solalettura();
                if(after!=missing)
                    after(dt);
            }
        });
    }
    function solalettura(){
        var flag=!currchiusa;
        var flagd=currdettenabled && !currchiusa;
        qv_maskenabled(formid, "C", flag);
        globalobjs[formid+"oper_trans"].enabled(flag);
        globalobjs[formid+"oper_processo"].enabled(flag);
        globalobjs[formid+"oper_datafine"].enabled(flag);
        globalobjs[formid+"oper_contextengage"].enabled(flag);
        
        // ATTIVITA
        globalobjs[formid+"opera_nuova"].enabled(flag);
        globalobjs[formid+"opera_rispondi"].visible(flag);
        globalobjs[formid+"opera_clona"].visible(flag);
        //globalobjs[formid+"opera_print"].visible(flag); // lascio stampare
        globalobjs[formid+"opera_archivia"].visible(flag);
        
        // DETTAGLIO E ALLEGATI
        qv_maskenabled(formid, "D", flagd);
        txd_bowid.enabled(0);
        txd_status.enabled(flagd);
        globalobjs[formid+"operd_salva"].enabled(flagd);
        globalobjs[formid+"operd_invia"].enabled(flagd);
        globalobjs[formid+"operd_provvisoria"].enabled(flagd);
        globalobjs[formid+"operd_completa"].enabled(flagd);
        globalobjs[formid+"operd_verificata"].enabled(flagd);

        //globalobjs[formid+"operf_compila"].enabled(flagd);
        globalobjs[formid+"operf_allega"].visible(flagd);
        globalobjs[formid+"oper_filesignature"].visible(flagd);
        globalobjs[formid+"oper_filedelete"].visible(flagd);
        if(flagd)
            $(prefix+"oper_fileinsert").css({"display":"block"});
        else
            $(prefix+"oper_fileinsert").css({"display":"none"});
        
        // MOVIMENTI
        qv_maskenabled(formid, "M", flag);
        globalobjs[formid+"operm_new"].visible(flag);
        globalobjs[formid+"operm_update"].visible(flag);
        globalobjs[formid+"operm_delete"].visible(flag);
    }
    function caricaattivita(after, missing){
        qv_maskclear(formid, "D");
        RYQUE.query({
            sql:"SELECT * FROM QW_ATTIVITAJOIN WHERE SYSID='"+currattivid+"'",
            ready:function(v){
                // ASSEGNAMENTO CAMPI
                qv_object2mask(formid, "D", v[0]);
                mascherastatus(v[0]);
                if(txd_bowid.value()==""){
                    txd_bowid.value(currattoreid);
                }
                if(txd_begin.text()==""){
                    txd_begin.value(_time());
                }
                txd_reference.value(v[0]["REFERENCE"]);
                // REGOLE DI UPDATING: ABILITAZIONE BOTTONE "SALVA" E "INVIA"
                if(_getinteger(v[0]["CONSISTENCY"])==2){
                    // BOZZA
                    currdettenabled=1;
                    qv_maskenabled(formid, "D", 1);
                    txd_status.enabled(1);
                    globalobjs[formid+"operd_invia"].visible(1);
                }
                else{
                    var a=0;
                    switch(_getinteger(v[0]["UPDATING"])){
                    case 0:
                        a=1;
                        break;
                    case 1:
                        // LO POSSONO MODIFICARE SOLO GLI INTERLOCUTORI
                        if(v[0]["BOWID"]==currattoreid || v[0]["TARGETID"]==currattoreid){
                            a=1;
                        }
                        break;
                    case 2:
                        // LO PUO' MODIFICARE SOLO IL RICHIEDENTE
                        if(v[0]["BOWID"]==currattoreid){
                            a=1;
                        }
                        break;
                    }
                    currdettenabled=a;
                    qv_maskenabled(formid, "D", a);
                    txd_status.enabled(a);
                    globalobjs[formid+"operd_invia"].visible(0);
                }
                context_attivita=v[0]["DESCRIPTION"];
                lb_dett_context.caption("Contesto: "+context);
                RYWINZ.modified(formid, 0);
                solalettura();
                winzClearMess(formid);
                if(after!=missing){after()}
            }
        });
    }
    function caricaanteprima(flag){
        if(flag){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"attivita_preview",
                    "data":{
                        "QUIVERID":currpraticaid,
                        "ARROWID":currattivid,
                        "privacy":0
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
                            if(window.console&&_sessioninfo.debugmode){console.log(h)}
                            $(prefix+"preview").html(h);
                        }
                        winzClearMess(formid);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
        else{
            $(prefix+"preview").html("");
        }
    }
    function buildstati(){
        var sql="";
        sql+="SELECT QW_PROCSTATI.SYSID AS STATOID ";
        sql+="FROM QW_PROCSTATI ";
        sql+="INNER JOIN QW_ATTORI ATTORISTATO ON ATTORISTATO.SYSID=QW_PROCSTATI.ATTOREID ";
        sql+="WHERE ATTORISTATO.UTENTEID='"+curruserid+"' OR '"+curruserid+"' IN (SELECT UTENTEID FROM QW_ATTORI WHERE QW_ATTORI.UFFICIOID=ATTORISTATO.UFFICIOID)";
        return sql;
    }
    function calcolascadenza(begin, days, method, after){
        winzProgress(formid);
        $.post(_cambusaURL+"rygeneral/rydateadd.php", 
            {
                "begin":begin,
                "days":days,
                "method":method
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        after(v["NEXT"]);
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
    function impostastatus(data){
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
        case 6:
            data["STATUS"]=2;
            data["PERCENTUALE"]=0;
            break;
        }
    }
    function mascherastatus(v){
        switch(_getinteger(v["STATUS"])){
        case 1:txd_status.value(5);break;
        case 2:txd_status.value(6);break;
        default:
            switch(_getinteger(v["PERCENTUALE"])){
            case 1:txd_status.value(2);break;
            case 2:txd_status.value(3);break;
            case 3:txd_status.value(4);break;
            default:txd_status.value(1);
            }
        }
    }
    function configuracustom(){
        var subh=0;
        // DISTRUGGO GLI EVENTUALI CAMPI AUTOCONFIGURATI
        for(var n in cachefields){
            delete globalobjs[n];
            delete _globalforms[formid].controls[n];
        }
        cachefields={};
        // CANCELLO LA VECCHIA CONFIGURAZIONE HTML
        $(prefix+"paramshandle").html("");
        
        if(curraggiuntivi!=""){
            var fields=curraggiuntivi.split(",");
            for(var f in fields){
                var n=fields[f];
                var lb=formid+"lb_"+n;
                var tx=formid+"tx_"+n;
                $(prefix+"paramshandle").append("<div id='"+lb+"'></div>");
                $(prefix+"paramshandle").append("<div id='"+tx+"'></div>");
                $("#"+lb).rylabel({left:0, top:subh, caption:n, formid:formid});
                $("#"+tx).rytext({left:120, top:subh, width:200, formid:formid,
                    changed:function(){
                        RYWINZ.modified(formid, 1);
                    }
                });
                cachefields[lb]="";
                cachefields[tx]=n.toUpperCase();
                subh+=30;
            }
        }
        subh+=30;
        $(prefix+"paramshandle").height(subh);
    }
    function travasacustom(data){
        if(curraggiuntivi!=""){
            var moredata={};
            for(var f in cachefields){
                n=cachefields[f];
                if(n!=""){
                    moredata[n]=globalobjs[f].value();
                }
            }
            data["DATIAGGIUNTIVI"]=moredata;
        }
    }
    function mascheracustom(moredata){
        if(curraggiuntivi!=""){
            for(var f in cachefields){
                n=cachefields[f];
                if(n!=""){
                    if(_isset(moredata[n]))
                        globalobjs[f].value(moredata[n]);
                    else
                        globalobjs[f].value("");
                }
            }
        }
    }
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xengage:oper_contextengage} );
    this._resize=function(){
        if( $("#window_"+formid).width()>1400 )
            $(prefix+"preview").css({left:740, top:80, width:"180mm"}); // era 660
        else
            $(prefix+"preview").css({left:previewX, top:previewY, width:"180mm"});  // era 700
    }
}

