/****************************************************************************
* Name:            qvadmincrediti.js                                        *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvadmincrediti(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0ACCREDITI00");
    var currgenretypeid=RYQUE.formatid("0PURENUMBER0");
    var currgenreid=RYQUE.formatid("0CREDITS0000");
    var currmotiveid=RYQUE.formatid("0MOTCREDFREQ");
    var currcorsitype=RYQUE.formatid("0CORSIFORMAT");
    var curraziendetype=RYQUE.formatid("0AZIENDE0000");
    var currcorsoid="";
    var curraziendaid="";
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
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:510, 
        assigned:function(){
            refreshselection();
        }
    });

    offsety+=30;
    $(prefix+"lbf_persone").rylabel({left:20, top:offsety, caption:"Profess."});
    var txf_persone=$(prefix+"txf_persone").ryhelper({left:100, top:offsety, width:200, 
        formid:formid, table:"QW_PERSONE", title:"Scelta professionisti", multiple:false,
        open:function(o){
            o.where("");
        },
        assigned: function(){
            refreshselection();
        }
    });
    $(prefix+"lbf_enti").rylabel({left:330, top:offsety, caption:"Ente"});
    var txf_enti=$(prefix+"txf_enti").ryhelper({left:410, top:offsety, width:200, 
        formid:formid, table:"QW_AZIENDE", title:"Scelta referente", multiple:false,
        open:function(o){
            o.where("");
        },
        assigned: function(){
            refreshselection();
        }
    });

    offsety+=30;
    $(prefix+"lbf_datemin").rylabel({left:20, top:offsety, caption:"Data min", title:"Filtro sulla data inizio del corso"});
    var txf_datemin=$(prefix+"txf_datemin").rydate({left:100, top:offsety,  width:120, 
        assigned:function(){
            refreshselection();
        }
    });
    $(prefix+"lbf_datemax").rylabel({left:330, top:offsety, caption:"Data max", title:"Filtro sulla data fine del corso"});
    var txf_datemax=$(prefix+"txf_datemax").rydate({left:410, top:offsety,  width:120, 
        assigned:function(){
            refreshselection();
        }
    });
    
    offsety+=30;
    var lbf_pendenti=$(prefix+"lbf_pendenti").rylabel({left:20, top:offsety, caption:"Pendenti", title:"Visualizza soltanto i crediti pendenti"});
    var chk_pendenti=$(prefix+"chk_pendenti").rycheck({left:100, top:offsety,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    chk_pendenti.value(1);
    
    var lbf_nocorso=$(prefix+"lbf_nocorso").rylabel({left:330, top:offsety, caption:"Incompleti", title:"Visualizza soltanto i crediti senza corso in tabella\n(i dati sono stati inseriti manualmente dall'utente)"});
    var chk_nocorso=$(prefix+"chk_nocorso").rycheck({left:410, top:offsety,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    chk_nocorso.value(0);

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
            var personaid=txf_persone.value();
            var aziendaid=txf_enti.value();
            var datamin=txf_datemin.text();
            var datamax=txf_datemax.text();
            var pendenti=chk_pendenti.value();
            var nocorso=chk_nocorso.value();

            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="[:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%'";
            }
            if(personaid!=""){
                if(q!=""){q+=" AND "}
                q+="TARGETID='"+personaid+"'";
            }
            if(aziendaid!=""){
                if(q!=""){q+=" AND "}
                q+="AZIENDAID='"+aziendaid+"'";
            }
            if(datamin!=""){
                if(q!=""){q+=" AND "}
                q+="TARGETTIME>=[:DATE("+datamin+")]";
            }
            if(datamax!=""){
                if(q!=""){q+=" AND "}
                q+="TARGETTIME<=[:DATE("+datamax+")]";
            }
            if(pendenti){
                if(q!=""){q+=" AND "}
                q+="STATUS=0 AND CONSISTENCY=0";
            }
            if(nocorso){
                if(q!=""){q+=" AND "}
                q+="FLAGCORSO=0";
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
            txf_persone.clear();
            txf_enti.clear();
            txf_datemin.clear();
            txf_datemax.clear();
            chk_pendenti.clear();
            chk_nocorso.clear();
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
        from:"QW_ACCREDITIJOIN",
        orderby:"TIMEINSERT",
        limit:10000,
        columns:[
            {id:"TARGET",caption:"Professionista",width:180},
            {id:"DESCRIPTION",caption:"Descrizione",width:300},
            {id:"TARGETTIME",caption:"Data",width:90,type:"/"},
            {id:"STATUS",caption:"Stato",width:60,type:"?"},
            {id:"CONSISTENCY",caption:"",width:0},
            {id:"FLAGCORSO",caption:"Corso",width:60,type:"?"},
            {id:"AMOUNT",caption:"Crediti",width:60,type:"0"},
            {id:"TIMEINSERT",caption:"Inserimento",width:90,type:"/"}
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
    
    offsety=500;
    var oper_print=$(prefix+"oper_print").rylabel({
        left:20,
        top:offsety,
        width:120,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_arrows.php")
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
    var lb_crediti_context=$(prefix+"crediti_context").rylabel({left:20, top:50, caption:""});
    
    offsety=90;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var tx_descr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:500, maxlen:200, datum:"C", tag:"DESCRIPTION"});

    offsety+=30;
    $(prefix+"LB_REFERENTE").rylabel({left:20, top:offsety, caption:"Ente"});
    var tx_referente=$(prefix+"REFERENTE").rytext({left:120, top:offsety, width:200, maxlen:100, datum:"C", tag:"REFERENTE"});
    
    var oper_cercareferente=$(prefix+"oper_cercareferente").rylabel({
        left:330,
        top:offsety,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            RYQUIVER.RequestID(formid, {
                table:"QW_AZIENDE", 
                select:"DESCRIPTION",
                where:"",
                title:"Scelta ente",
                multiple:false,
                onselect:function(d){
                    curraziendaid=d["SYSID"];
                    tx_referente.value(d["DESCRIPTION"]);
                    oper_creareferente.enabled(0);
                }
            });
        }
    });

    var oper_creareferente=$(prefix+"oper_creareferente").rylabel({
        left:420,
        top:offsety,
        width:100,
        caption:"Inserisci ente",
        button:true,
        click:function(o){
            if(tx_referente.value()==""){
                winzMessageBox(formid, "Ente non specificato!");
            }
            else{
                var stats=[];
                var istr=0;

                stats[istr++]={
                    "function":"objects_insert",
                    "data":{
                        "TYPOLOGYID":curraziendetype,
                        "DESCRIPTION":tx_referente.value()
                    },
                    "pipe":{"AZIENDAID":"SYSID"},
                    "return":{"AZIENDAID":"SYSID"}
                };
                if(currcorsoid!=""){
                    stats[istr++]={
                        "function":"objects_update",
                        "data":{
                            "SYSID":currcorsoid
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
                                curraziendaid=v.infos["AZIENDAID"];
                                oper_creareferente.enabled(0);
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
        }
    });

    offsety+=30;
    $(prefix+"LB_CODICE").rylabel({left:20, top:offsety, caption:"Codice"});
    var tx_codice=$(prefix+"CODICE").rytext({left:120, top:offsety, width:200, maxlen:100, datum:"C", tag:"REFERENCE"});

    var oper_cercacorso=$(prefix+"oper_cercacorso").rylabel({
        left:330,
        top:offsety,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            qv_helpcourses(formid, {
                onselect:function(d){
                    currcorsoid=d["SYSID"];
                    curraziendaid=d["AZIENDAID"];

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

    var oper_creacorso=$(prefix+"oper_creacorso").rylabel({
        left:420,
        top:offsety,
        width:100,
        caption:"Inserisci corso",
        button:true,
        click:function(o){
            if(tx_amount.value()==0){
                winzMessageBox(formid, "Inserire i crediti!");
            }
            else if(curraziendaid==""){
                winzMessageBox(formid, "Inserire l'ente!");
            }
            else{
                winzProgress(formid);
                var stats=[];
                var istr=0;

                var data={};
                data["DESCRIPTION"]=tx_descr.value();
                data["TYPOLOGYID"]=currcorsitype;
                data["REFGENREID"]=currgenreid;
                data["REFERENCE"]=tx_codice.value();
                data["REFERENTE"]=tx_referente.value();
                data["TIPOCORSO"]=tx_tipo.value();
                data["LUOGO"]=tx_luogo.value();
                data["BEGINTIME"]=tx_begintime.text();
                data["ENDTIME"]=tx_endtime.text();
                data["AUXAMOUNT"]=tx_amount.value();
                data["AZIENDAID"]=curraziendaid;
                stats[istr++]={
                    "function":"objects_insert",
                    "data":data,
                    "pipe":{"CORSOID":"SYSID"},
                    "return":{"CORSOID":"SYSID"}
                };
                stats[istr++]={
                    "function":"arrows_update",
                    "data":{
                        "SYSID":currsysid
                    }
                };
                
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
                                currcorsoid=v.infos["CORSOID"];
                                
                                // INFO CORSO
                                currnotecorso=food4info(tx_descr.value(), "");
                                oper_info.title(currnotecorso);
                                oper_info.enabled( currnotecorso!="" );
                                
                                oper_creacorso.enabled(0);
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
        }
    });

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
    
    $(prefix+"LB_STATUS").rylabel({left:350, top:offsety, caption:"Stato"});
    var tx_status=$(prefix+"STATUS").rylist({left:420, top:offsety, width:120,
        assigned:function(){
            RYWINZ.modified(formid, 1);
        }
    })
    
    tx_status
        .additem({caption:"", key:0})
        .additem({caption:"Pendente", key:1})
        .additem({caption:"Accettato", key:2})
        .additem({caption:"Rifiutato", key:3});
    
    offsety+=30;
    $(prefix+"LB_PHASENOTE").rylabel({left:20, top:offsety, caption:"Motivazione"});
    var tx_phasenote=$(prefix+"PHASENOTE").rytext({left:120, top:offsety, width:500, maxlen:100, datum:"C", tag:"PHASENOTE"});

    offsety+=30;
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:650,
        top:90,
        width:70,
        caption:"Salva",
        button:true,
        click:function(o, done){
            var ok=false;
            if(tx_status.key()=="2"){
                if(tx_amount.value()==0)
                    winzMessageBox(formid, "Inserire i crediti!");
                else if(currcorsoid=="")
                    winzMessageBox(formid, "Inserire il corso!");
                else
                    ok=true;
            }
            else{
                ok=true;
            }
            if(ok){
                winzProgress(formid);
                context=tx_descr.value();
                var data=RYWINZ.ToObject(formid, "C", currsysid);
                data["CORSOID"]=currcorsoid;
                switch(tx_status.key()){
                case "3":
                    data["STATUS"]="0";
                    data["CONSISTENCY"]="1";
                    break;
                case "2":
                    data["STATUS"]="1";
                    data["CONSISTENCY"]="0";
                    break;
                default:
                    data["STATUS"]="0";
                    data["CONSISTENCY"]="0";
                    break;
                }
                $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
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
        }
    });

    var oper_info=$(prefix+"oper_info").rylabel({
        left:650,
        top:270,
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
                    lb_crediti_context.caption("");
                    currcorsoid="";
                    curraziendaid="";

                    // INFO CORSO
                    currnotecorso="";
                    oper_info.title(currnotecorso);
                    oper_info.enabled(0);
                    
                    oper_creacorso.enabled(0);
                    oper_creareferente.enabled(0);
                    RYQUE.query({
                        sql:"SELECT * FROM QW_ACCREDITIJOIN WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            currcorsoid=v[0]["CORSOID"];
                            curraziendaid=v[0]["AZIENDAID"];
                            lb_crediti_context.caption("<b>"+v[0]["TARGETNOME"]+" "+v[0]["TARGETCOGNOME"]+" - "+v[0]["TARGETEMAIL"]+"</b>");
                            
                            // INFO CORSO
                            currnotecorso=food4info(v[0]["CORSO"], v[0]["CORSONOTE"]);
                            oper_info.title(currnotecorso);
                            oper_info.enabled( currnotecorso!="" );
                            
                            oper_creacorso.enabled( currcorsoid=="" );
                            oper_creareferente.enabled( curraziendaid=="" );
                            
                            if(__(v[0]["CONSISTENCY"]).actualInteger()>=1)
                                tx_status.setkey(3);
                            else if(__(v[0]["STATUS"]).actualInteger()==0)
                                tx_status.setkey(1);
                            else
                                tx_status.setkey(2);
                            RYWINZ.modified(formid, 0);

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
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            RYWINZ.loadmodule("corsi.js", _systeminfo.web.apps+"corsaro/_javascript/corsi.js",
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
