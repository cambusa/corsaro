/****************************************************************************
* Name:            qvaccrediti.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvaccrediti(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0ACCREDITI00");
    var currgenretypeid=RYQUE.formatid("0PURENUMBER0");
    var currgenreid=RYQUE.formatid("0CREDITS0000");
    var currmotiveid=RYQUE.formatid("0MOTCREDFREQ");
    var currcorsoid="";
    var currstatus=0;
    var currconsistency=0;
    var currpersonaid="";
    var currpersona="";
    var currnome="";
    var currcognome="";
    var currnotecorso="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var sospendirefresh=false;

    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    // RICERCA MOVIMENTI
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca", title:"Filtro sul titolo del corso"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_datemin").rylabel({left:20, top:offsety, caption:"Data min", title:"Filtro sulla data inizio del corso"});
    var txf_datemin=$(prefix+"txf_datemin").rydate({left:100, top:offsety,  width:150, 
        assigned:function(){
            refreshselection();
        }
    });
    $(prefix+"lbf_datemax").rylabel({left:300, top:offsety, caption:"Data max", title:"Filtro sulla data fine del corso"});
    var txf_datemax=$(prefix+"txf_datemax").rydate({left:400, top:offsety,  width:150, 
        assigned:function(){
            refreshselection();
        }
    });
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:630,
        top:80,
        width:80,
        caption:"Aggiorna",
        title:"Aggiorna la lista applicando i filtri",
        button:true,
        click:function(o, done){
            objgridsel.clear()
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            var datamin=txf_datemin.text();
            var datamax=txf_datemax.text();

            q="TYPOLOGYID='"+currtypologyid+"' AND TARGETID='"+currpersonaid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(datamin!="")
                q+=" AND TARGETTIME>=[:DATE("+datamin+")]";
            if(datamax!="")
                q+=" AND TARGETTIME<=[:DATE("+datamax+")]";

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
    var oper_reset=$(prefix+"oper_reset").rylabel({
        left:630,
        top:110,
        width:80,
        caption:"Pulisci",
        title:"Ripulisce i filtri",
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_datemin.clear();
            txf_datemax.clear();
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
        from:"QW_ACCREDITI",
        orderby:"TARGETTIME DESC",
        limit:10000,
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:400},
            {id:"TARGETTIME",caption:"Data",width:90,type:"/"},
            {id:"STATUS",caption:"Stato",width:60,type:"?"},
            {id:"CONSISTENCY",caption:"",width:0},
            {id:"AMOUNT",caption:"Crediti",width:60,type:"0"}
        ],
        changerow:function(o,i){
            currsysid="";
            context="";
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            if(i>0){
                o.solveid(i);
            }
        },
        changesel:function(o){
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currsysid=d;
            oper_print.enabled(1);
            oper_delete.enabled(1);
            objtabs.enabled(2,true);
            objtabs.enabled(3,true);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(2);
            }
        },
        enter:function(){
            objtabs.currtab(2);
        },
        ready:function(){
            solvecredits();
        },
        before:function(o, d){
            for(var i in d){
                // COLONNA CONSISTENCY
                var fd=o.screenrow(i);
                if(d[i]["CONSISTENCY"]<"1"){
                    if(d[i]["STATUS"]=="0")
                        $(fd).css({"color":"black"});
                    else
                        $(fd).css({"color":"green"});
                }
                else{
                    $(fd).css({"color":"red"});
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
            data["DESCRIPTION"]="(nuovo accredito)";
            data["TYPOLOGYID"]=currtypologyid;
            data["GENREID"]=currgenreid;
            data["MOTIVEID"]=currmotiveid;
            data["TARGETID"]=currpersonaid;
            data["BOWTIME"]="";
            data["TARGETTIME"]="";
            data["CORSOID"]="";
            data["STATUS"]="0";
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"arrows_insert",
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
            qv_printselected(formid, objgridsel, "@customize/corsaro/reporting/rep_arrows.php")
        }
    });
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:590,
        top:offsety,
        width:120,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "arrows");
        }
    });
    offsety+=40;

    var lb_credits=$(prefix+"label_credits").rylabel({left:20, top:offsety, caption:"(utente non individuato)"});
    
    // DEFINIZIONE TAB CONTESTO
    offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var tx_descr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:410, maxlen:200, datum:"C", tag:"DESCRIPTION"});

    var oper_cercacorso=$(prefix+"oper_cercacorso").rylabel({
        left:540,
        top:offsety,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            qv_helpcourses(formid, {
                onselect:function(d){
                    currcorsoid=d["SYSID"];
                    currstatus=0;
                    currconsistency=0;
                    
                    // INFO CORSO
                    currnotecorso=food4info(d["DESCRIPTION"], d["REGISTRY"]);
                    oper_info.title(currnotecorso);
                    oper_info.enabled( currnotecorso!="" );

                    abilitacampi();
                    tx_descr.value(d["DESCRIPTION"]);
                    tx_codice.value(d["REFERENCE"]);
                    tx_referente.value(d["REFERENTE"]);
                    tx_tipo.value(d["TIPOCORSO"]);
                    tx_luogo.value(d["LUOGO"]);
                    tx_begintime.value(d["BEGINTIME"]);
                    tx_endtime.value(d["ENDTIME"]);
                    tx_amount.value( __(d["AUXAMOUNT"]).actualInteger() );
                }
            });
        }
    });

    offsety+=30;
    $(prefix+"LB_CODICE").rylabel({left:20, top:offsety, caption:"Codice"});
    var tx_codice=$(prefix+"CODICE").rytext({left:120, top:offsety, width:200, maxlen:100, datum:"C", tag:"REFERENCE"});
    $(prefix+"LB_REFERENTE").rylabel({left:350, top:offsety, caption:"Ente"});
    var tx_referente=$(prefix+"REFERENTE").rytext({left:420, top:offsety, width:200, maxlen:100, datum:"C", tag:"REFERENTE"});
    
    offsety+=30;
    $(prefix+"LB_TIPOCORSO").rylabel({left:20, top:offsety, caption:"Tipo"});
    var tx_tipo=$(prefix+"TIPOCORSO").rytext({left:120, top:offsety, width:200, maxlen:100, datum:"C", tag:"TIPOCORSO"});
    $(prefix+"LB_LUOGO").rylabel({left:350, top:offsety, caption:"Luogo"});
    var tx_luogo=$(prefix+"LUOGO").rytext({left:420, top:offsety, width:200, maxlen:100, datum:"C", tag:"LUOGO"});
    
    offsety+=30;
    $(prefix+"LB_BOWTIME").rylabel({left:20, top:offsety, caption:"Inizio"});
    var tx_begintime=$(prefix+"BOWTIME").rydate({left:120, top:offsety, width:120 ,datum:"C", tag:"BOWTIME"});
    $(prefix+"LB_TARGETTIME").rylabel({left:350, top:offsety, caption:"Fine"});
    var tx_endtime=$(prefix+"TARGETTIME").rydate({left:420, top:offsety, width:120 ,datum:"C", tag:"TARGETTIME"});
    
    offsety+=30;
    $(prefix+"LB_AMOUNT").rylabel({left:20, top:offsety, caption:"Crediti"});
    var tx_amount=$(prefix+"AMOUNT").rynumber({left:120, top:offsety, width:120, numdec:0, minvalue:0, datum:"C", tag:"AMOUNT"});
    tx_amount.enabled(0);
    
    $(prefix+"LB_STATUS").rylabel({left:350, top:offsety, caption:"Stato"});
    var tx_status=$(prefix+"STATUS").rylist({left:420, top:offsety, width:120});
    
    tx_status
        .additem({caption:"", key:0})
        .additem({caption:"Pendente", key:1})
        .additem({caption:"Accettato", key:2})
        .additem({caption:"Rifiutato", key:3})
        .enabled(0);
    
    offsety+=30;
    var lb_phasenote=$(prefix+"LB_PHASENOTE").rylabel({left:20, top:offsety, caption:"Motivazione"});
    var tx_phasenote=$(prefix+"PHASENOTE").rytext({left:120, top:offsety, width:500, maxlen:100, datum:"C", tag:"PHASENOTE"});
    tx_phasenote.enabled(0);
    
    offsety+=30;
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var oper_pulisci=$(prefix+"oper_pulisci").rylabel({
        left:650,
        top:90,
        width:70,
        caption:"Pulisci",
        button:true,
        click:function(o){
            RYWINZ.MaskClear(formid, "C");
            currcorsoid="";
            currstatus=0;
            currconsistency=0;
            
            // INFO CORSO
            currnotecorso="";
            oper_info.title(currnotecorso);
            oper_info.enabled(0);

            abilitacampi();
        }
    });

    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:650,
        top:60,
        width:70,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=tx_descr.value();
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            data["CORSOID"]=currcorsoid;
            if(currcorsoid!="")
                data["STATUS"]=1;
            else
                data["STATUS"]=0;
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"arrows_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            currstatus=data["STATUS"];
                            settastatus();
                            RYWINZ.modified(formid, 0);
                            if(done!=missing){done()}
                            objgridsel.dataload(
                                function(){
                                    solvecredits();
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
    });
    
    var oper_info=$(prefix+"oper_info").rylabel({
        left:650,
        top:180,
        width:70,
        caption:"Info",
        button:true,
        click:function(){
            winzMessageBox(formid, 
            {
                message:currnotecorso,
                width:600,
                height:300
            });
        }
    });
    oper_info.enabled(0);

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVARROWS", "QW_ACCREDITI");

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
                    currcorsoid="";
                    currstatus=0;
                    currconsistency=0;

                    // INFO CORSO
                    currnotecorso="";
                    oper_info.title(currnotecorso);
                    oper_info.enabled(0);

                    abilitacampi();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_ACCREDITIJOIN WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            currcorsoid=v[0]["CORSOID"];
                            currstatus=__(v[0]["STATUS"]).actualInteger();
                            currconsistency=__(v[0]["CONSISTENCY"]).actualInteger();

                            // INFO CORSO
                            currnotecorso=food4info(v[0]["CORSO"], v[0]["CORSONOTE"]);
                            oper_info.title(currnotecorso);
                            oper_info.enabled( currnotecorso!="" );

                            abilitacampi();

                            // STATUS
                            settastatus();
                            
                            // ABILITAZIONE BOTTONI
                            oper_cercacorso.enabled( currstatus==0 && currconsistency==0 );
                            oper_pulisci.enabled( currstatus==0 && currconsistency==0 );
                            
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            castFocus(prefix+"DESCRIPTION");
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVARROWS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
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
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){ 
            RYWINZ.loadmodule("corsi.js", _systeminfo.relative.apps+"corsaro/_javascript/corsi.js",
                function(){
                    // REPERSICO AL PERSONA
                    RYQUE.query({
                        sql:"SELECT SYSID,DESCRIPTION,NOME,COGNOME FROM QW_PERSONE WHERE UTENTEID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"')",
                        ready:function(v){
                            if(v.length>0){
                                currpersonaid=v[0]["SYSID"];
                                currpersona=v[0]["DESCRIPTION"];
                                currnome=v[0]["NOME"];
                                currcognome=v[0]["COGNOME"];
                                lb_credits.caption("<b>Utente:</b> "+currnome+" "+currcognome);
                                solvecredits(
                                    function(){
                                        oper_refresh.engage(
                                            function(){
                                                winzClearMess(formid);
                                                txf_search.focus();
                                            }
                                        ) 
                                    }
                                );
                            }
                            else{
                                alert("Utente non abilitato");
                            }
                        }
                    });
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
    function solvecredits(after){
        RYQUE.query({
            sql:"SELECT BALANCE FROM QWCBALANCES WHERE SYSID='"+currpersonaid+"' ORDER BY EVENTTIME DESC",
            ready:function(v){
                if(v.length>0){
                    lb_credits.caption("<b>Utente:</b> "+currpersona+" - <b>Crediti:</b> "+v[0]["BALANCE"]);
                }
                if(after){after()}
            }
        });
    }
    function abilitacampi(){
        var f=(currcorsoid=="");
        tx_descr.enabled(f);
        tx_codice.enabled(f);
        tx_referente.enabled(f);
        tx_tipo.enabled(f);
        tx_luogo.enabled(f);
        tx_begintime.enabled(f);
        tx_endtime.enabled(f);
    }
    function settastatus(){
        if(currconsistency>=1){
            tx_status.setkey(3);
            lb_phasenote.visible(1);
            tx_phasenote.visible(1);
        }
        else if(currstatus==0){
            tx_status.setkey(1);
            lb_phasenote.visible(0);
            tx_phasenote.visible(0);
        }
        else{
            tx_status.setkey(2);
            lb_phasenote.visible(0);
            tx_phasenote.visible(0);
        }
    }
    function food4info(descr, memo){
        var r=descr;
        if(memo!=""){
            r+="<br/><br/>"+memo;
        }
        r=r.replace(/<p>/gi, "");
        r=r.replace(/<\/p>/gi, "<br/>");
        r=r.replace(/[\r\n]/gi, "");
        return r;
    }
}
