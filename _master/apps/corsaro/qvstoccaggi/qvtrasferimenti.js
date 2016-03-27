/****************************************************************************
* Name:            qvtrasferimenti.js                                       *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvtrasferimenti(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currproprietarioid="";
    var currmagazzinoid="";
    var currsysid="";
    var currtypologyid=RYQUE.formatid("0TRASFERIMEN");
    var currgenretypeid=RYQUE.formatid("0ARTICOLI000");
    var currmotivetypeid=RYQUE.formatid("0MOTIVITRASF");
    var currcollocazioneid="";
    var currrounding=2;
    var currgenreid="";
    var currgenredescr="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var sospendirefresh=false;

    // FUORI TABS
    $(prefix+"lbf_proprietario").rylabel({left:20, top:10, caption:"Proprietario"});
    $(prefix+"txf_proprietario").ryhelper({left:110, top:10, width:145, 
        formid:formid, table:"QW_ATTORI", title:"Aziende", multiple:false,
        open:function(o){
            o.where("(AZIENDAID<>'' OR PROPRIETAID<>'' OR RESPONSABILEID<>'')");
        },
        select:"AZIENDAID,PROPRIETAID",
        onselect:function(o, d){
            if(d["AZIENDAID"]!="")
                currproprietarioid=d["AZIENDAID"];
            else if(d["PROPRIETAID"]!="")
                currproprietarioid=d["PROPRIETAID"]
            else
                currproprietarioid=d["RESPONSABILEID"]
        },
        clear:function(){
            currproprietarioid="";
        }
    });
    $(prefix+"lbf_magazzino").rylabel({left:300, top:10, caption:"Magazzino"});
    var txf_magazzino=$(prefix+"txf_magazzino").ryhelper({left:380, top:10, width:145, 
        formid:formid, table:"QW_UFFICI", title:"Magazzini", multiple:false,
        open:function(o){
            var q="MAGAZZINO=1";
            if(currproprietarioid!=""){
                q+=" AND (AZIENDAID='"+currproprietarioid+"' OR PROPRIETAID='"+currproprietarioid+"')";
            }
            o.where(q);
        },
        assigned: function(o){
            currmagazzinoid=o.value();
        },
        clear:function(){
            currmagazzinoid="";
        }
    });

    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    // RICERCA TRASFERMIENTI
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:500, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_genre").rylabel({left:20, top:offsety, caption:"Articolo*"});
    var txf_genre=$(prefix+"txf_genre").ryhelper({left:100, top:offsety, width:150, 
        formid:formid, table:"QW_ARTICOLI", title:"Articoli", multiple:false,
        open:function(o){
            o.where("");
        },
        onselect:function(o, d){
            currgenredescr=d["DESCRIPTION"];
        },
        assigned:function(){
            currgenreid=txf_genre.value();
            refreshselection();
        },
        clear:function(){
            currgenreid="";
            currgenredescr="";
            refreshselection();
        }
    });
    var oper_genre=$(prefix+"oper_genre").rylabel({
        left:260,
        top:offsety,
        caption:"Ricerca...",
        button:true,
        click:function(o){
            cercaarticolo.show({
                open:function(){
                    $("#"+formid+"tabs").hide();
                    globalobjs[formid+"lbf_proprietario"].visible(0);
                    globalobjs[formid+"lbf_proprietario"].visible(0);
                    globalobjs[formid+"txf_proprietario"].visible(0);
                    globalobjs[formid+"lbf_magazzino"].visible(0);
                    globalobjs[formid+"txf_magazzino"].visible(0);
                },
                close:function(){
                    $("#"+formid+"tabs").show();
                    globalobjs[formid+"lbf_proprietario"].visible(1);
                    globalobjs[formid+"txf_proprietario"].visible(1);
                    globalobjs[formid+"lbf_magazzino"].visible(1);
                    globalobjs[formid+"txf_magazzino"].visible(1);
                },
                assigned:function(genre){
                    txf_genre.value(genre, true);
                }
            });
        }
    });
    var cercaarticolo=new corsaro_browserstuff(formid, "browser_genre");

    $(prefix+"lbf_motives").rylabel({left:350, top:offsety, caption:"Motivi*"});
    var txf_motives=$(prefix+"txf_motives").ryhelper({left:450, top:offsety, width:150, 
        formid:formid, table:"QVMOTIVES", title:"Motivi", multiple:true,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currmotivetypeid});
        },
        assigned: function(){
            refreshselection();
        }
    });offsety+=30;
    
    $(prefix+"lbf_collocazione").rylabel({left:20, top:offsety, caption:"Collocaz."});
    var txf_collocazione=$(prefix+"txf_collocazione").ryhelper({left:100, top:offsety, width:150, 
        formid:formid, table:"QW_COLLOCAZIONIJOIN", title:"Collocazioni", multiple:false,
        open:function(o){
            var q="";
            if(currmagazzinoid!=""){
                q+="MAGAZZINOID='"+currmagazzinoid+"'";
            }
            else if(currproprietarioid!=""){
                q="PROPRIETARIOID='"+currproprietarioid+"'";
            }
            if(currgenreid!=""){
                if(q!=""){q+=" AND "}
                q+="REFGENREID='"+currgenreid+"'";
            }
            o.where(q);
        },
        assigned: function(o){
            currcollocazioneid=o.value();
            refreshselection();
        },
        clear: function(o){
            currcollocazioneid="";
            refreshselection();
        }
    });
    $(prefix+"lbf_amount").rylabel({left:350, top:offsety, caption:"Quantit&agrave; &plusmn;5%"});
    var txf_amount=$(prefix+"txf_amount").rynumber({left:450, top:offsety,  width:150, numdec:0, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_datemin").rylabel({left:20, top:offsety, caption:"Data min"});
    var txf_datemin=$(prefix+"txf_datemin").rydate({left:100, top:offsety,  width:150, 
        assigned:function(){
            refreshselection();
        }
    });
    $(prefix+"lbf_datemax").rylabel({left:350, top:offsety, caption:"Data max"});
    var txf_datemax=$(prefix+"txf_datemax").rydate({left:450, top:offsety,  width:150, 
        assigned:function(){
            refreshselection();
        }
    });
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            objgridsel.clear()
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            var genreid=currgenreid;
            var motiveid=txf_motives.value();
            var collocazioneid=txf_collocazione.value();
            var datamin=txf_datemin.text();
            var datamax=txf_datemax.text();
            var amount=txf_amount.value();

            oper_new.enabled( genreid!="" && motiveid!="" );
            
            q="TYPOLOGYID='"+currtypologyid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(genreid!="")
                q+=" AND GENREID='"+genreid+"'";
            if(motiveid!="")
                q+=" AND MOTIVEID IN ('"+motiveid.replace("|", "','")+"')";
            if(collocazioneid!="")
                q+=" AND (BOWID='"+collocazioneid+"' OR TARGETID='"+collocazioneid+"')";
            if(datamin!="")
                q+=" AND AUXTIME>=[:TIME("+datamin+"000000)]";
            if(datamax!="")
                q+=" AND AUXTIME<=[:TIME("+datamax+"235959)]";
            if(amount>0)
                q+=" AND (AMOUNT>="+(amount*0.95)+" AND AMOUNT<="+(amount*1.05)+")";

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
        left:650,
        top:170,
        caption:"Pulisci",
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_genre.clear();
            txf_motives.clear();
            txf_collocazione.clear();
            txf_datemin.clear();
            txf_datemax.clear();
            txf_amount.clear();
            sospendirefresh=false;
            refreshselection();
        }
    });
    offsety+=35;
    
    var offsetx=20;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_TRASFERIMENTIJOIN",
        orderby:"AUXTIME DESC",
        limit:10000,
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:168},
            {id:"MOTIVE",caption:"Motivo",width:150},
            {id:"AUXTIME",caption:"Data Reg.",width:90,type:"/"},
            {id:"BOWTIME",caption:"Data Evento",width:90,type:"/"},
            {id:"TARGETTIME",caption:"",width:0,type:"/"},
            {id:"BOWID",caption:"",width:0},
            {id:"AMOUNT",caption:"Quantit&agrave",width:120,type:"2"},
            {id:"GENRE",caption:"Articolo",width:90},
            {id:"STATUS",caption:"Stato",width:50, type:"?", formula:"[:BOOL(STATUS>0)]"},
            {id:"PHASE",caption:"Fase",width:50, type:"?", formula:"[:BOOL(PHASE>0)]"}
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
        changesel:function(o){
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
        before:function(o,d){
            if(currcollocazioneid!=""){
                for(var i in d){
                    if(d[i]["BOWID"]==currcollocazioneid)
                        d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                    else
                        d[i]["BOWTIME"]=d[i]["TARGETTIME"];
                }
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    
    offsety=500;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var motivoid=txf_motives.value().substr(0, RYQUE.lenid());
            var data = new Object();
            data["DESCRIPTION"]=currgenredescr;
            data["TYPOLOGYID"]=currtypologyid;
            data["GENREID"]=currgenreid;
            data["MOTIVEID"]=motivoid;
            data["STATUS"]=-1;
            if(currcollocazioneid!=""){
                data["REFERENCEID"]=currcollocazioneid;
            }
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
        left:400,
        top:offsety,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "@customize/corsaro/reporting/rep_arrows.php")
        }
    });
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:590,
        top:offsety,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "arrows");
        }
    });
    offsety+=40;

    $(prefix+"lb_warning").rylabel({left:20, top:offsety, caption:"* Campi obbligatori per abilitare l'inserimento"});

    // DEFINIZIONE TAB CONTESTO
    offsetx=340;
    offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=50;

    $(prefix+"LB_BOW").rylabel({left:120, top:offsety, caption:"<b>Partenza</b>"});
    $(prefix+"LB_TARGET").rylabel({left:offsetx+100, top:offsety, caption:"<b>Arrivo</b>"});
    offsety+=30;
    
    $(prefix+"LB_OBJECTID").rylabel({left:20, top:offsety, caption:"Collocaz."});
    var txbow=$(prefix+"BOWID").ryhelper({
        left:120, top:offsety, width:180, datum:"C", tag:"BOWID", formid:formid, table:"QW_COLLOCAZIONIJOIN", title:"Collocazioni",
        open:function(o){
            var q="";
            if(currmagazzinoid!=""){
                q+="MAGAZZINOID='"+currmagazzinoid+"'";
            }
            else if(currproprietarioid!=""){
                q="PROPRIETARIOID='"+currproprietarioid+"'";
            }
            if(currgenreid!=""){
                if(q!=""){q+=" AND "}
                q+="REFGENREID='"+currgenreid+"'";
            }
            o.where(q);
        }
    });
    var txtarget=$(prefix+"TARGETID").ryhelper({
        left:offsetx+100, top:offsety, width:180, datum:"C", tag:"TARGETID", formid:formid, table:"QW_COLLOCAZIONIJOIN", title:"Collocazioni",
        open:function(o){
            var q="";
            if(currmagazzinoid!=""){
                q+="MAGAZZINOID='"+currmagazzinoid+"'";
            }
            else if(currproprietarioid!=""){
                q="PROPRIETARIOID='"+currproprietarioid+"'";
            }
            if(currgenreid!=""){
                if(q!=""){q+=" AND "}
                q+="REFGENREID='"+currgenreid+"'";
            }
            o.where(q);
        }
    });
    offsety+=30;
    
    $(prefix+"LB_TIME").rylabel({left:20, top:offsety, caption:"Data Evento"});
    var txbowtime=$(prefix+"BOWDATE").rydate({left:120, top:offsety, datum:"C", tag:"BOWTIME"});
    txbowtime.link(
        $(prefix+"BOWTIME").rytime({left:250, top:offsety})
    );
    var txtargettime=$(prefix+"TARGETDATE").rydate({left:offsetx+100, top:offsety, datum:"C", tag:"TARGETTIME"});
    txtargettime.link(
        $(prefix+"TARGETTIME").rytime({left:offsetx+230, top:offsety})
    );
    offsety+=30;
    
    $(prefix+"LB_GIACENZA").rylabel({left:20, top:offsety, caption:"Giacenza"});
    var txbowgiacenza=$(prefix+"BOWGIACENZA").rynumber({left:120, top:offsety, numdec:2});
    var txtargetgiacenza=$(prefix+"TARGETGIACENZA").rynumber({left:offsetx+100, top:offsety, numdec:2});
    offsety+=30;
    
    $(prefix+"LB_DISPO").rylabel({left:20, top:offsety, caption:"Disponibilit&agrave;"});
    var txbowdispo=$(prefix+"BOWDISPO").rynumber({left:120, top:offsety, numdec:2});
    var txtargetdispo=$(prefix+"TARGETDISPO").rynumber({left:offsetx+100, top:offsety, numdec:2});
    offsety+=50;
    
    txbowgiacenza.enabled(0);
    txtargetgiacenza.enabled(0);
    txbowdispo.enabled(0);
    txtargetdispo.enabled(0);

    $(prefix+"LB_AUXTIME").rylabel({left:20, top:offsety, caption:"Data Reg."});
    $(prefix+"AUXTIME").rydate({left:120, top:offsety, datum:"C", tag:"AUXTIME"});
    offsety+=30;
    
    $(prefix+"LB_MOTIVEID").rylabel({left:20, top:offsety, caption:"Motivo"});
    $(prefix+"MOTIVEID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"MOTIVEID", formid:formid, table:"QVMOTIVES", title:"Causali",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currmotivetypeid});
        }
    });offsety+=30;
    
    $(prefix+"LB_GENREID").rylabel({left:20, top:offsety, caption:"Articolo"});
    var tx_genreid=$(prefix+"GENREID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"GENREID", formid:formid, table:"QVGENRES", title:"Articoli",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        select:"ROUNDING",
        onselect:function(o, d){
            tx_amount.numdec( parseInt(d["ROUNDING"]) );
        }
    });
    tx_genreid.enabled(0);
    offsety+=30;
    
    $(prefix+"LB_AMOUNT").rylabel({left:20, top:offsety, caption:"Quantit&agrave"});
    var tx_amount=$(prefix+"AMOUNT").rynumber({left:120, top:offsety, width:200, numdec:2, minvalue:0, datum:"C", tag:"AMOUNT"});offsety+=30;
    
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Riferimento"});
    $(prefix+"REFERENCE").rytext({left:120, top:offsety, width:200, datum:"C", tag:"REFERENCE"});offsety+=30;
    
    $(prefix+"LB_CONSISTENCY").rylabel({left:20, top:offsety, caption:"Concretezza"});
    $(prefix+"CONSISTENCY").rylist({left:120, top:offsety, width:200, datum:"C", tag:"CONSISTENCY"})
        .additem({caption:"", key:""})
        .additem({caption:"Effettiva", key:0})
        .additem({caption:"Equivalente", key:1})
        .additem({caption:"Simulata", key:2})
        .additem({caption:"Astratta", key:3});offsety+=30;

    $(prefix+"LB_STATUS").rylabel({left:20, top:offsety, caption:"Stato"});
    $(prefix+"STATUS").rylist({left:120, top:offsety, width:200, datum:"C", tag:"STATUS"})
        .additem({caption:"", key:""})
        .additem({caption:"Prenotato", key:0})
        .additem({caption:"In transito", key:1})
        .additem({caption:"Arrivato", key:2})
        .additem({caption:"Processato", key:3});

    $(prefix+"LB_STATUSTIME").rylabel({left:offsetx+20, top:offsety, caption:"Data"});
    $(prefix+"STATUSTIME").rydate({left:offsetx+100, top:offsety, datum:"C", tag:"STATUSTIME"});
    offsety+=30;

    $(prefix+"LB_PHASE").rylabel({left:20, top:offsety, caption:"Fase"}).enabled(false);
    $(prefix+"PHASE").rylist({left:120, top:offsety, width:200})
        .additem({caption:"Non inviato", key:0})
        .additem({caption:"Inviato", key:1})
        .additem({caption:"Accettato", key:2})
        .additem({caption:"Rifiutato", key:3})
        .enabled(false);
    $(prefix+"LB_PHASENOTE").rylabel({left:offsetx+20, top:offsety, caption:"Note"}).enabled(false);
    $(prefix+"PHASENOTE").rytext({left:offsetx+100, top:offsety, width:300}).enabled(false);
    offsety+=30;

    $(prefix+"LB_PROVIDER").rylabel({left:20, top:offsety, caption:"Origine"}).enabled(false);
    $(prefix+"PROVIDER").rytext({left:120, top:offsety, width:200}).enabled(false);
    $(prefix+"LB_PARCEL").rylabel({left:offsetx+20, top:offsety, caption:"Lotto"}).enabled(false);
    $(prefix+"PARCEL").rytext({left:offsetx+100, top:offsety, width:200}).enabled(false);
    offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    offsety+=30;
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
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
                    "function":"arrows_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            if($.isset(v.params["STATUSTIME"])){
                                globalobjs[formid+"STATUSTIME"].value(v.params["STATUSTIME"]);
                            }
                            if($.isset(v.params["STATUS"])){
                                globalobjs[formid+"STATUS"].setkey(v.params["STATUS"]);
                            }
                            // RICALCOLO GIACENZA E DISPONIBILITA'
                            setTimeout(
                                function(){
                                    calcologiacenza();
                                }, 500
                            );
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

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVARROWS", "QW_TRASFERIMENTI");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:50,
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
                    RYQUE.query({
                        sql:"SELECT * FROM QW_TRASFERIMENTI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            currgenreid=v[0]["GENREID"];
                            loadedsysid=currsysid;
                            castFocus(prefix+"DESCRIPTION");
                            // RICALCOLO GIACENZA E DISPONIBILITA'
                            setTimeout(
                                function(){
                                    calcologiacenza();
                                }, 500
                            );
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
            refreshselection();
            txf_search.focus();
        }
    );
    function refreshselection(){
        if(!sospendirefresh){
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
    }
    function calcologiacenza(){
        txbowgiacenza.value(0);
        txtargetgiacenza.value(0);
        txbowdispo.value(0);
        txtargetdispo.value(0);
        var bowid=txbow.value();
        var targetid=txtarget.value();
        var ids=bowid+"|"+targetid;
        var evs=tempolasco( txbowtime.text() )+"|"+tempolasco( txtargettime.text() );
        RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"stuff_balance",
                "data":{
                    "SYSID":ids,
                    "EVENTS":evs
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var balance=v["params"]["BALANCE"];
                        if(bowid!=""){
                            txbowgiacenza.value(balance[bowid]["GIACENZA"][0]);
                            txbowdispo.value(balance[bowid]["DISPO"][0]);
                        }
                        if(targetid!=""){
                            txtargetgiacenza.value(balance[targetid]["GIACENZA"][1]);
                            txtargetdispo.value(balance[targetid]["DISPO"][1]);
                        }
                    }
                }
                catch(e){
                    alert(d);
                }
            }
        );
    }
    function tempolasco(t){
        var h=t.substr(0,10);
        var m=t.substr(10,2).actualInteger()+4;
        m=("00"+m).subright(2);
        return h+m+"59";
    }
}

