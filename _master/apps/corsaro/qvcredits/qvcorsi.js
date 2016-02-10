/****************************************************************************
* Name:            qvcorsi.js                                               *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvcorsi(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0CORSIFORMAT");
    var currgenreid=RYQUE.formatid("0CREDITS0000");
    var curraziendetype=RYQUE.formatid("0AZIENDE0000");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var flagclick=false;
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_CORSI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:200},
            {id:"BEGINTIME", caption:"Inizio", width:200, type:"/"},
            {id:"LUOGO", caption:"Luogo", width:200}
        ],
        changerow:function(o,i){
            currsysid="";
            context="";
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
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
        }
    });
    var offsety=80;
    $(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            oper_refresh.engage()
        }
    });
    
    offsety+=30;
    $(prefix+"lbf_consistency").rylabel({left:430, top:offsety, caption:"Solo attivi"});
    var chk_consistency=$(prefix+"chk_consistency").rycheck({left:510, top:offsety,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    chk_consistency.value(1);
    
    offsety+=30;
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_search.value());

            q="TYPOLOGYID='"+currtypologyid+"'";
            if(t!=""){
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            if(chk_consistency.value()){
                q+=" AND CONSISTENCY=0";
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
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:430,
        top:240,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuovo corso)";
            data["TYPOLOGYID"]=currtypologyid;
            data["REFGENREID"]=currgenreid;
            $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
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
        left:430,
        top:290,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_objects.php")
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:340,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "objects");
        }
    });

    offsety+=50;
    var oper_import=$(prefix+"oper_import").rylabel({
        left:20,
        top:400,
        width:120,
        caption:"Importa ODS",
        button:true,
        click:function(o){
            qv_importODS(formid,
                {
                    "columns":[
                        {"id":"DESCRIPTION", "caption":"Titolo"},
                        {"id":"REFERENCE", "caption":"Codice"},
                        {"id":"REFERENTE", "caption":"Ente"},
                        {"id":"AUXAMOUNT", "caption":"Crediti"},
                        {"id":"LUOGO", "caption":"Luogo"},
                        {"id":"TIPOCORSO", "caption":"Tipo corso"},
                        {"id":"BEGINTIME", "caption":"Inizio"},
                        {"id":"ENDTIME", "caption":"Fine"},
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
                                var inizio="";
                                var fine="";
                                data["TYPOLOGYID"]=currtypologyid;
                                data["CONFLICT"]="SKIP";
                                for(var c in d[i]){
                                    data[c]=d[i][c];
                                    if(c=="DESCRIPTION")
                                        descr=data[c];
                                    else if(c=="BEGINTIME")
                                        inizio=data[c];
                                    else if(c=="ENDTIME")
                                        fine=data[c];
                                }

                                // GESTIONE DESCRIZIONE
                                if(descr==""){
                                    descr="(nuovo corso)";
                                }
                                data["DESCRIPTION"]=descr;
                                
                                // GESTIONE DATE
                                if(fine==""){
                                    fine=inizio;
                                }
                                data["BEGINTIME"]=inizio;
                                data["ENDTIME"]=fine;

                                // INSERIMENTO ISTRUZIONE
                                stats[i]={
                                    "function":"objects_insert",
                                    "data":data
                                };
                            }
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
    //oper_import.visible(_sessioninfo.admin);

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:500, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;
    
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Codice"});
    $(prefix+"REFERENCE").rytext({left:120, top:offsety, width:300, maxlen:50, datum:"C", tag:"REFERENCE"});
    offsety+=30;

    $(prefix+"LB_TIPOCORSO").rylabel({left:20, top:offsety, caption:"Tipo"});
    $(prefix+"TIPOCORSO").rytext({left:120, top:offsety, width:300, maxlen:50, datum:"C", tag:"TIPOCORSO"});
    offsety+=30;
    
    $(prefix+"LB_LUOGO").rylabel({left:20, top:offsety, caption:"Luogo"});
    $(prefix+"LUOGO").rytext({left:120, top:offsety, width:300, datum:"C", tag:"LUOGO"});
    offsety+=30;
    
    $(prefix+"LB_REFERENTE").rylabel({left:20, top:offsety, caption:"Ente (libero)"});
    var tx_referente=$(prefix+"REFERENTE").rytext({left:120, top:offsety, width:300, datum:"C", tag:"REFERENTE"});
    var oper_creareferente=$(prefix+"oper_creareferente").rylabel({
        left:430,
        top:offsety,
        width:100,
        caption:"Inserisci ente",
        button:true,
        click:function(o){
            if(tx_referente.value()==""){
                winzMessageBox(formid, "Ente (libero) non specificato!");
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
                var data=RYWINZ.ToObject(formid, "C", currsysid);
                stats[istr++]={
                    "function":"objects_update",
                    "data":data
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
                                tx_aziendaid.value( v.infos["AZIENDAID"] );
                                RYWINZ.modified(formid, 0);
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
    
    $(prefix+"LB_AZIENDAID").rylabel({left:20, top:offsety, caption:"Ente (tabella)"});
    var tx_aziendaid=$(prefix+"AZIENDAID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"AZIENDAID", formid:formid, table:"QW_AZIENDE", title:"Enti",
        open:function(o){
            o.where("");
            flagclick=true;
        },
        select:"REFERENCE",
        onselect:function(o, d){
            if(flagclick){
                flagclick=false;
                tx_referente.value(d["DESCRIPTION"]);
            }
        },
        clear:function(){
            oper_creareferente.enabled(1);
        }
    });
    offsety+=30;

    $(prefix+"LB_BEGINTIME").rylabel({left:20, top:offsety, caption:"Inizio"});
    $(prefix+"BEGINTIME").rydate({left:120, top:offsety, width:120, datum:"C", tag:"BEGINTIME"});
    $(prefix+"LB_ENDTIME").rylabel({left:260, top:offsety, caption:"Fine"});
    $(prefix+"ENDTIME").rydate({left:300, top:offsety, width:120, defaultvalue:"99991231", datum:"C", tag:"ENDTIME"});
    offsety+=30;
    
    $(prefix+"LB_CREDITI").rylabel({left:20, top:offsety, caption:"Credito"});
    var tx_amount=$(prefix+"CREDITI").rynumber({left:120, top:offsety, width:120, numdec:0, minvalue:0, datum:"C", tag:"AUXAMOUNT"});
    
    $(prefix+"LB_CONSISTENCY").rylabel({left:260, top:offsety, caption:"Stato"});
    var tx_consistency=$(prefix+"CONSISTENCY").rylist({left:300, top:offsety, width:120, datum:"C", tag:"CONSISTENCY"});
    tx_consistency
        .additem({caption:"Attivo", key:0})
        .additem({caption:"Archiviato", key:1});
    
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
            data["REFGENREID"]=currgenreid;
            $.post(_systeminfo.web.cambusa+"ryquiver/quiver.php", 
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
                            objgridsel.dataload();
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

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_CORSI");

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
                    oper_creareferente.enabled(0);
                    RYQUE.query({
                        sql:"SELECT * FROM QW_CORSI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            
                            oper_creareferente.enabled( tx_aziendaid.value()=="" );
                            
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            castFocus(prefix+"DESCRIPTION");
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
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
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

