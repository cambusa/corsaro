/****************************************************************************
* Name:            qvobjecttypes.js                                         *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvobjecttypes(settings,missing){
    var formid=RYWINZ.addform(this);
    var currsysid="";
    var currdetailid="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var base=0;
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
        from:"QVOBJECTTYPES",
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

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:130,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="";
            var t=_likeescapize(txf_search.value());

            if(t!="")
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
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
            data["DESCRIPTION"]="(nuova tipologia)";
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objecttypes_insert",
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
            qv_printselected(formid, objgridsel, "rep_objecttypes.php")
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:340,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "objecttypes");
        }
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Nome"});
    $(prefix+"NAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NAME"});offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});offsety+=30;
    
    $(prefix+"LB_TIMEUNIT").rylabel({left:20, top:offsety, caption:"Unit&agrave; tempo"});
    $(prefix+"TIMEUNIT").rylist({left:120, top:offsety, width:300, datum:"C", tag:"TIMEUNIT"})
        .additem({caption:"", key:""})
        .additem({caption:"Giorno", key:"D"})
        .additem({caption:"Secondo", key:"S"});offsety+=30;
    
    $(prefix+"LB_GENRETYPEID").rylabel({left:20, top:offsety, caption:"Tipo genere"});
    $(prefix+"GENRETYPEID").rylist({left:120, top:offsety, width:300, datum:"C", tag:"GENRETYPEID"});offsety+=30;
    
    $(prefix+"LB_QUIVERTYPEID").rylabel({left:20, top:offsety, caption:"Tipo quiver"});
    $(prefix+"QUIVERTYPEID").rylist({left:120, top:offsety, width:300, datum:"C", tag:"QUIVERTYPEID"});offsety+=30;

    $(prefix+"LB_VIEWNAME").rylabel({left:20, top:offsety, caption:"Vista"});
    $(prefix+"VIEWNAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"VIEWNAME"});offsety+=30;
    
    $(prefix+"LB_TABLENAME").rylabel({left:20, top:offsety, caption:"Estensione"});
    $(prefix+"TABLENAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TABLENAME"});offsety+=30;
    
    $(prefix+"LB_DELETABLE").rylabel({left:20, top:offsety, caption:"Gestibile"});
    $(prefix+"DELETABLE").rycheck({left:120, top:offsety, datum:"C", tag:"DELETABLE"});offsety+=30;
    
    $(prefix+"LB_SIMPLE").rylabel({left:20, top:offsety, caption:"Semplice"});
    $(prefix+"SIMPLE").rycheck({left:120, top:offsety, datum:"C", tag:"SIMPLE"});offsety+=30;
    
    $(prefix+"LB_VIRTUALDELETE").rylabel({left:20, top:offsety, caption:"Canc. virtuale"});
    $(prefix+"VIRTUALDELETE").rycheck({left:120, top:offsety, datum:"C", tag:"VIRTUALDELETE"});offsety+=30;
    
    $(prefix+"LB_HISTORICIZING").rylabel({left:20, top:offsety, caption:"Storicizza"});
    $(prefix+"HISTORICIZING").rycheck({left:120, top:offsety, datum:"C", tag:"HISTORICIZING"});offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:450,
        top:60,
        width:80,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objecttypes_update",
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
    
    // GRID DETTAGLI
    var objgriddetails=$(prefix+"griddetails").ryque({
        left:20,
        top:80,
        width:400,
        height:390,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QVOBJECTVIEWS",
        columns:[
            {id:"FIELDNAME",caption:"Campo",width:200}
        ],
        changerow:function(o,i){
            currdetailid="";
            if(i>0){
                o.solveid(i);
            }
            else{
                RYWINZ.MaskClear(formid, "X");
                enabledetails(0);
                oper_detailsdelete.enabled(o.isselected());
            }
        },
        selchange:function(o, i){
            oper_detailsdelete.enabled(o.isselected());
        },
        solveid:function(o, d){
            currdetailid=d;
            RYQUE.query({
                sql:"SELECT * FROM QVOBJECTVIEWS WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        RYWINZ.ToMask(formid, "X", v[0])
                        enabledetails(1);
                        oper_detailsdelete.enabled(1);
                    }catch(e){}
                } 
            });
        }
    });
    $(prefix+"oper_detailsnew").rylabel({
        left:430,
        top:80,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["TYPOLOGYID"]=currsysid;
            data["FIELDNAME"]="NomeCampo";
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objectviews_insert",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.SYSID;
                            objgriddetails.splice(0, 0, newid);
                            /*
                            objgriddetails.query({
                                ready:function(v){
                                    objgriddetails.search({
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
                                            objgriddetails.index(ind);
                                        }
                                    );
                                }
                            });
                            */
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
    base=100;
    $(prefix+"LB_FIELDNAME").rylabel({  left:430, top:base+30,  caption:"Campo"});
    $(prefix+"FIELDNAME").rytext({      left:430, top:base+50, width:300, datum:"X", tag:"FIELDNAME"});
    $(prefix+"LB_FIELDTYPE").rylabel({  left:430, top:base+80, caption:"Tipo"});
    $(prefix+"FIELDTYPE").rytext({      left:430, top:base+100, width:300, datum:"X", tag:"FIELDTYPE"});
    $(prefix+"LB_FORMULA").rylabel({    left:430, top:base+130, caption:"Formula"});
    $(prefix+"FORMULA").rytext({        left:430, top:base+150, width:300, datum:"X", tag:"FORMULA"});
    $(prefix+"LB_CAPTION").rylabel({    left:430, top:base+180, caption:"Etichetta"});
    $(prefix+"CAPTION").rytext({        left:430, top:base+200, width:300, datum:"X", tag:"CAPTION"});
    $(prefix+"LB_WRITABLE").rylabel({   left:430, top:base+230, caption:"Gestibile"});
    $(prefix+"WRITABLE").rycheck({      left:430, top:base+250, datum:"X", tag:"WRITABLE"});

    var oper_detailsengage=$(prefix+"oper_detailsengage").rylabel({
        left:430,
        top:base+280,
        caption:"Salva",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "X", currdetailid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objectviews_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        RYWINZ.modified(formid, 0);
                        objgriddetails.dataload();
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
    var oper_detailsdelete=$(prefix+"oper_detailsdelete").rylabel({
        left:430,
        top:base+330,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgriddetails, "objectviews");
        }
    });

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTTYPES");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
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
                    objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO DEL CONTESTO
                    if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currsysid)}
                    RYWINZ.MaskClear(formid, "C");
                    RYQUE.query({
                        sql:"SELECT * FROM QVOBJECTTYPES WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            castFocus(prefix+"DESCRIPTION");
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DETTAGLI
                    lb_details_context.caption("Contesto: "+context);
                    objgriddetails.clear();
                    objgriddetails.where("TYPOLOGYID='"+currsysid+"'");
                    objgriddetails.query({
                        ready:function(){
                            qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTTYPES", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                                done:function(d){
                                    context=d;
                                    lb_details_context.caption("Contesto: "+context);
                                }
                            });
                        }
                    });
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+context);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTTYPES", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
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
    objtabs.enabled(4,false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            objgridsel.where("");
            objgridsel.query({
                ready:function(){
                    TAIL.enqueue(qv_queuelistcall, {"id": formid+"GENRETYPEID", "table":"#QVGENRETYPES"});
                    TAIL.enqueue(qv_queuelistcall, {"id": formid+"QUIVERTYPEID", "table":"#QVQUIVERTYPES"});
                    TAIL.wriggle();
                }
            });
        }
    );
    
    function enabledetails(v){
        globalobjs[formid+"FIELDNAME"].enabled(v);
        globalobjs[formid+"FIELDTYPE"].enabled(v);
        globalobjs[formid+"FORMULA"].enabled(v);
        globalobjs[formid+"CAPTION"].enabled(v);
        globalobjs[formid+"WRITABLE"].enabled(v);
        oper_detailsengage.enabled(v);
    }
}

