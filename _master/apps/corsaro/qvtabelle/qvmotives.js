/****************************************************************************
* Name:            qvmotives.js                                             *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvmotives(settings,missing){
    var formid=RYWINZ.addform(this);
    var currsysid="";
    var currtypologyid="";
    var currviewname="";
    var currobjecttype="";
    var typedescr="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var cacheext={};
    var loadedsysid="";
    
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
        from:"QWMOTIVES",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:200}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                if(currsysid!=""){
                    objtabs.enabled(2,false);
                    objtabs.enabled(3,false);
                    objtabs.enabled(4,false);
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
                objtabs.enabled(4,true);
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
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:80, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:100, width:300, assigned:function(){oper_refresh.engage()}});

    var lbf_typology=$(prefix+"lbf_typology").rylabel({left:430, top:130, caption:"Tipologia*"});
    $(prefix+"txf_typology").ryhelper({
        left:430, top:150, width:300, formid:formid, table:"QVMOTIVETYPES", title:"Tipologie motivo",
        open:function(o){
            o.where("SIMPLE=1");
        },
        select:"VIEWNAME,OBJECTTYPEID",
        onselect:function(o,d){
            typedescr=d["DESCRIPTION"];
            currviewname=_fittingvalue(d["VIEWNAME"]);
            currobjecttype=_fittingvalue(d["OBJECTTYPEID"]);
            setTimeout(function(){oper_refresh.engage()},100);
        },
        assigned:function(o){
            currtypologyid=o.value();
            typedescr="";
            currviewname="";
            currobjecttype="";
            if(currtypologyid!="")
                oper_new.enabled(1);
        },
        clear:function(){
            oper_new.enabled(0);
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:190,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            objgridsel.clear();
            if(currtypologyid!=""){
                var q="";
                var t=_likeescapize(txf_search.value());
                
                q="TYPOLOGYID='"+currtypologyid+"'";
                if(t!="")
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

                objgridsel.where(q);
                objgridsel.query({
                    args:{
                        "DESCRIPTION":t,
                        "TAG":t
                    }
                });
            }
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
            data["DESCRIPTION"]="(nuovo motivo)";
            data["TYPOLOGYID"]=currtypologyid;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"motives_insert",
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
    oper_new.enabled(0);
    
    var oper_print=$(prefix+"oper_print").rylabel({
        left:430,
        top:290,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_motives.php")
        }
    });
    oper_print.enabled(0);

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:340,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "motives");
        }
    });
    oper_delete.enabled(0);

    $(prefix+"lb_warning").rylabel({left:20, top:380, caption:"* Campi obbligatori per abilitare l'inserimento"});

    // DEFINIZIONE TAB CONTESTO
    var offsety=50;
    var lb_context_context=$(prefix+"context_context").rylabel({left:20, top:offsety, caption:""});
    offsety+=40;

    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Nome"});
    $(prefix+"NAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NAME"});
    offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;
    
    $(prefix+"LB_DIRECTION").rylabel({left:20, top:offsety, caption:"Direzione"});
    $(prefix+"DIRECTION").rylist({left:120, top:offsety, width:300, datum:"C", tag:"DIRECTION"})
        .additem({caption:"Da riferimento a controparte", key:0})
        .additem({caption:"Da controparte a riferimento", key:1});
    offsety+=30;
    
    $(prefix+"LB_REFERENCEID").rylabel({left:20, top:offsety, caption:"Riferimento"});
    $(prefix+"REFERENCEID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFERENCEID", formid:formid, table:"QVOBJECTS", title:"Scelta oggetto di riferimento",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currobjecttype});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_COUNTERPARTID").rylabel({left:20, top:offsety, caption:"Controparte"});
    $(prefix+"COUNTERPARTID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"COUNTERPARTID", formid:formid, table:"QVOBJECTS", title:"Scelta oggetto controparte",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currobjecttype});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_CONSISTENCY").rylabel({left:20, top:offsety, caption:"Concretezza"});
    $(prefix+"CONSISTENCY").rylist({left:120, top:offsety, width:200, datum:"C", tag:"CONSISTENCY"})
        .additem({caption:"", key:""})
        .additem({caption:"Effettiva", key:0})
        .additem({caption:"Equivalente", key:1})
        .additem({caption:"Simulata", key:2})
        .additem({caption:"Astratta", key:3});
    offsety+=30;

    $(prefix+"LB_SCOPE").rylabel({left:20, top:offsety, caption:"Visibilit"+_utf8("a")});
    $(prefix+"SCOPE").rylist({left:120, top:offsety, width:200, datum:"C", tag:"SCOPE"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});
    offsety+=30;

    $(prefix+"LB_UPDATING").rylabel({left:20, top:offsety, caption:"Modificabilit"+_utf8("a")});
    $(prefix+"UPDATING").rylist({left:120, top:offsety, width:200, datum:"C", tag:"UPDATING"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});
    offsety+=30;

    $(prefix+"LB_DELETING").rylabel({left:20, top:offsety, caption:"Cancellabilit"+_utf8("a")});
    $(prefix+"DELETING").rylist({left:120, top:offsety, width:200, datum:"C", tag:"DELETING"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});
    offsety+=30;

    $(prefix+"LB_STATUS").rylabel({left:20, top:offsety, caption:"Stato"});
    $(prefix+"STATUS").rylist({left:120, top:offsety, width:200, datum:"C", tag:"STATUS"})
        .additem({caption:"", key:""})
        .additem({caption:"Provvisorio", key:0})
        .additem({caption:"Completo", key:1})
        .additem({caption:"Verificato", key:2})
        .additem({caption:"Processato", key:3});
    offsety+=30;

    $(prefix+"LB_DISCHARGE").rylabel({left:20, top:offsety, caption:"Scarico"});
    $(prefix+"DISCHARGE").rylist({left:120, top:offsety, width:200, datum:"C", tag:"DISCHARGE"})
        .additem({caption:"", key:""})
        .additem({caption:"Nessuno", key:0})
        .additem({caption:"LIFO", key:1})
        .additem({caption:"FIFO", key:2})
        .additem({caption:"Ponderato", key:3});
    offsety+=30;

    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    offsety+=30;
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:450,
        top:90,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            // AGGIORNO LE INFO SUL CONTESTO
            context=txdescr.value();
            // CREO UN CONTENITORE CON I DATI AGGIORNATI
            var data=qv_mask2object(formid, "C", currsysid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"motives_update",
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

    // DEFINIZIONE TAB DETTAGLI
    var lb_details_context=$(prefix+"details_context").rylabel({left:20, top:50, caption:""});
    var oper_detailsengage=$(prefix+"oper_detailsengage").rylabel({
        left:20,
        top:90,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=qv_mask2object(formid, "X", currsysid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"motives_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
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
    $(prefix+"REGISTRY").ryedit({left:20, top:120, width:700, height:400, flat:1, datum:"X", tag:"REGISTRY"});

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVMOTIVES", "QVMOTIVES");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Dettagli"},
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
                    //objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO DEL CONTESTO
                    if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currsysid)}
                    lb_context_context.caption("Contesto: "+typedescr);
                    qv_autoconfigure(formid, currviewname, "QVMOTIVE", currtypologyid, 420, cacheext, 
                        function(t, y){
                            RYQUE.query({
                                sql:"SELECT * FROM "+t+" WHERE SYSID='"+currsysid+"'",
                                ready:function(v){
                                    qv_object2mask(formid, "C", v[0]);
                                    context=v[0]["DESCRIPTION"];
                                    loadedsysid=currsysid;
                                    // EVENTUALMENTE PORTO "SALVA" COME ULTIMO CAMPO
                                    // I NOMI DEI CAMPI DEL FORM
                                    var k=Object.keys(_globalforms[formid].controls);
                                    // L'ULTIMO CAMPO
                                    var l=k[k.length-1];
                                    var e=oper_contextengage.name();
                                    if(l!=e){
                                        // "SALVA" NON E' L'ULTIMO CAMPO: TOLGO E RIMETTO
                                        delete globalobjs[e];
                                        globalobjs[e]=oper_contextengage;
                                    }
                                    castFocus(prefix+"DESCRIPTION");
                                }
                            });
                        }
                    );
                    break;
                case 3:
                    // CARICAMENTO DETTAGLI
                    lb_details_context.caption("Contesto: "+typedescr+" / "+context);
                    qv_maskclear(formid, "X");
                    RYQUE.query({
                        sql:"SELECT DESCRIPTION,REGISTRY FROM QVMOTIVES WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            qv_object2mask(formid, "X", v[0]);
                            context=v[0]["DESCRIPTION"];
                            lb_details_context.caption("Contesto: "+typedescr+" / "+context);
                            loadedsysidx=currsysid;
                        }
                    });
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+typedescr+" / "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVMOTIVES", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            filemanager.caption("Contesto: "+typedescr+" / "+context);
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
    objtabs.enabled(4,false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid);
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xfocus:"NAME", xengage:oper_contextengage, files:3} );
}

