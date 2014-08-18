/****************************************************************************
* Name:            qvquivers.js                                             *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvquivers(settings,missing){
    var formid=RYWINZ.addform(this);
    var currsysid="";
    var currtypologyid="";
    var currviewname="";
    
    var qgenretypeid="";
    var qobjecttypeid="";
    var qmotivetypeid="";
    var qarrowtypeid="";
    var qquivertypeid="";
    
    var typedescr="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var cacheext={};
    var flagrefresh=false;
    var loadedsysid="";
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:380,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWQUIVERS",
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
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});
    offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, assigned:function(){oper_refresh.engage()}});
    offsety+=30;

    var lbf_typology=$(prefix+"lbf_typology").rylabel({left:430, top:offsety, caption:"Tipologia*"});
    offsety+=20;
    $(prefix+"txf_typology").ryhelper({
        left:430, top:offsety, width:300, formid:formid, table:"QVQUIVERTYPES", title:"Tipologie quiver",
        open:function(o){
            o.where("SIMPLE=1");
        },
        select:"VIEWNAME,GENRETYPEID,OBJECTTYPEID,MOTIVETYPEID,ARROWTYPEID,QUIVERTYPEID",
        onselect:function(o,d){
            typedescr=d["DESCRIPTION"];
            currviewname=_fittingvalue(d["VIEWNAME"]);
            qgenretypeid=_fittingvalue(d["GENRETYPEID"]);
            qobjecttypeid=_fittingvalue(d["OBJECTTYPEID"]);
            qmotivetypeid=_fittingvalue(d["MOTIVETYPEID"]);
            qarrowtypeid=_fittingvalue(d["ARROWTYPEID"]);
            qquivertypeid=_fittingvalue(d["QUIVERTYPEID"]);
            setTimeout(function(){oper_refresh.engage()},100);
        },
        assigned:function(o){
            currtypologyid=o.value();
            typedescr="";
            currviewname="";
            qgenretypeid="";
            qobjecttypeid="";
            qmotivetypeid="";
            qarrowtypeid="";
            qquivertypeid="";
        },
        clear:function(){
            oper_new.enabled(0);
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    var lbf_status=$(prefix+"lbf_status").rylabel({left:430, top:offsety, caption:"Stato"});
    offsety+=20;
    var txf_status=$(prefix+"txf_status").rylist({left:430, top:offsety, width:300,
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    txf_status
    .additem({caption:"Tutti", key:-1})
    .additem({caption:"Provvisorio", key:0})
    .additem({caption:"Completo", key:1})
    .additem({caption:"Verificato", key:2})
    .additem({caption:"Processato", key:3});
    offsety+=40;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            objgridsel.clear();
            if(currtypologyid!=""){
                var q="";
                var t=_likeescapize(txf_search.value());
                var status=txf_status.key();
                
                oper_new.enabled(1);

                q="TYPOLOGYID='"+currtypologyid+"'";
                if(t!="")
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                if(status>=0)
                    q+=" AND STATUS="+status;

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
        top:330,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuovo quiver)";
            data["TYPOLOGYID"]=currtypologyid;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"quivers_insert",
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
        top:380,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_quivers.php")
        }
    });
    oper_print.enabled(0);

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:428,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "quivers");
        }
    });
    oper_delete.enabled(0);

    $(prefix+"lb_warning").rylabel({left:20, top:470, caption:"* Campi obbligatori per abilitare l'inserimento"});

    // DEFINIZIONE TAB CONTESTO
    var offsetx=340;
    var offsety=50;
    var lb_context_context=$(prefix+"context_context").rylabel({left:20, top:offsety, caption:""});
    offsety+=40;

    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Nome"});
    $(prefix+"NAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NAME"});offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:100, datum:"C", tag:"DESCRIPTION"});offsety+=30;
    
    $(prefix+"LB_AUXTIME").rylabel({left:20, top:offsety, caption:"Registrazione"});
    $(prefix+"AUXDATE").rydate({left:120, top:offsety, datum:"C", tag:"AUXTIME"})
    .link(
        $(prefix+"AUXTIME").rytime({left:250, top:offsety})
    );
    offsety+=30;

    $(prefix+"LB_AUXAMOUNT").rylabel({left:20, top:offsety, caption:"Quantit"+_utf8("a")});
    $(prefix+"AUXAMOUNT").rynumber({left:120, top:offsety, width:200, numdec:2, minvalue:0, datum:"C", tag:"AUXAMOUNT"});
    offsety+=30;
    
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Riferimento"});
    $(prefix+"REFERENCE").rytext({left:120, top:offsety, width:200, datum:"C", tag:"REFERENCE"});offsety+=30;
    
    $(prefix+"LB_CONSISTENCY").rylabel({left:20, top:offsety, caption:"Concretezza"});
    $(prefix+"CONSISTENCY").rylist({left:120, top:offsety, width:200, datum:"C", tag:"CONSISTENCY"})
        .additem({caption:"", key:""})
        .additem({caption:"Effettiva", key:0})
        .additem({caption:"Equivalente", key:1})
        .additem({caption:"Simulata", key:2})
        .additem({caption:"Astratta", key:3});offsety+=30;

    $(prefix+"LB_AVAILABILITY").rylabel({left:20, top:offsety, caption:"Disponibilit"+_utf8("a")});
    $(prefix+"AVAILABILITY").rylist({left:120, top:offsety, width:200, datum:"C", tag:"AVAILABILITY"})
        .additem({caption:"", key:""})
        .additem({caption:"Disponibile", key:0})
        .additem({caption:"Bloccato", key:1})
        .additem({caption:"Archiviato", key:2});offsety+=30;
        
    $(prefix+"LB_SCOPE").rylabel({left:20, top:offsety, caption:"Visibilit"+_utf8("a")});
    $(prefix+"SCOPE").rylist({left:120, top:offsety, width:200, datum:"C", tag:"SCOPE"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});offsety+=30;

    $(prefix+"LB_UPDATING").rylabel({left:20, top:offsety, caption:"Modificabilit"+_utf8("a")});
    $(prefix+"UPDATING").rylist({left:120, top:offsety, width:200, datum:"C", tag:"UPDATING"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});offsety+=30;

    $(prefix+"LB_DELETING").rylabel({left:20, top:offsety, caption:"Cancellabilit"+_utf8("a")});
    $(prefix+"DELETING").rylist({left:120, top:offsety, width:200, datum:"C", tag:"DELETING"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});offsety+=30;

    $(prefix+"LB_STATUS").rylabel({left:20, top:offsety, caption:"Stato"});
    $(prefix+"STATUS").rylist({left:120, top:offsety, width:200, datum:"C", tag:"STATUS",
        assigned:function(){
            flagrefresh=true;
        }
    })
        .additem({caption:"", key:""})
        .additem({caption:"Provvisorio", key:0})
        .additem({caption:"Completo", key:1})
        .additem({caption:"Verificato", key:2})
        .additem({caption:"Processato", key:3});

    $(prefix+"LB_STATUSTIME").rylabel({left:offsetx+20, top:offsety, caption:"Data"});
    $(prefix+"STATUSDATE").rydate({left:offsetx+100, top:offsety, datum:"C", tag:"STATUSTIME"})
    .link(
        $(prefix+"STATUSTIME").rytime({left:offsetx+230, top:offsety})
    );
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

    $(prefix+"LB_REFGENREID").rylabel({left:20, top:offsety, caption:"Genere"});
    $(prefix+"REFGENREID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFGENREID", formid:formid, table:"QVGENRES", title:"Generi",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":qgenretypeid});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_REFOBJECTID").rylabel({left:20, top:offsety, caption:"Oggetto"});
    $(prefix+"REFOBJECTID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFOBJECTID", formid:formid, table:"QVOBJECTS", title:"Oggetti",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":qobjecttypeid});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_REFMOTIVEID").rylabel({left:20, top:offsety, caption:"Motivo"});
    $(prefix+"REFMOTIVEID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFMOTIVEID", formid:formid, table:"QVMOTIVES", title:"Motivi",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":qmotivetypeid});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_REFARROWID").rylabel({left:20, top:offsety, caption:"Freccia"});
    $(prefix+"REFARROWID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFARROWID", formid:formid, table:"QVARROWS", title:"Freccia",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":qarrowtypeid});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_REFQUIVERID").rylabel({left:20, top:offsety, caption:"Quiver"});
    $(prefix+"REFQUIVERID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFQUIVERID", formid:formid, table:"QVQUIVERS", title:"Quiver",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":qquivertypeid});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    
    offsety+=30;
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Registro"});
    $(prefix+"REGISTRY").ryedit({left:120, top:offsety, width:612, height:200, flat:1, datum:"C", tag:"REGISTRY"});
    
    offsety+=215;
    $(prefix+"LB_MOREDATA").rylabel({left:20, top:offsety, caption:"JSON"});
    $(prefix+"MOREDATA").ryedit({left:120, top:offsety, width:612, height:200, flat:1, datum:"C", tag:"MOREDATA"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:90,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            // AGGIORNO LE INFO SUL CONTESTO
            context=txdescr.value();
            lb_details_context.caption("Contesto: "+typedescr+" / "+context);
            // CREO UN CONTENITORE CON I DATI AGGIORNATI
            var data=qv_mask2object(formid, "C", currsysid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"quivers_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            if(!_ismissing(v.params["STATUSTIME"])){
                                globalobjs[formid+"STATUSDATE"].value(v.params["STATUSTIME"]);
                            }
                            RYWINZ.modified(formid, 0);
                        }
                        if(flagrefresh)
                            setTimeout(function(){oper_refresh.engage();}, 100);
                        else
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
    var currarrowtypeid="";
    var curroparentid="";
    var currobjectid="";
    var currgenreid="";
    var currgenretypeid="";
    var currmotivetypeid="";
    var currbowtypeid="";
    var currtargettypeid="";
    
    offsetx=480;
    offsety=110;
    var lb_details_context=$(prefix+"details_context").rylabel({left:20, top:50, caption:""});
    var griddetails=$(prefix+"griddetails").ryque({
        left:20,
        top:offsety,
        width:450,
        height:400,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWQUIVERBROWSER",
        limit:10000,
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:140},
            {id:"INCLUSIONS",caption:"",width:20,type:"?"},
            {id:"AUXTIME",caption:"Data",width:90,type:"/"},
            {id:"BOWID",caption:"",width:0},
            {id:"AMOUNT",caption:"Quantit"+_utf8("a"),width:110,type:"2"}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                oper_add.enabled(o.isselected());
                oper_remove.enabled(o.isselected());
            }
        },
        selchange:function(o, i){
            oper_add.enabled(o.isselected());
            oper_remove.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_add.enabled(1);
            oper_remove.enabled(1);
        },
        before:function(o,d){
            if(currobjectid!=""){
                for(var i in d){
                    if(d[i]["BOWID"]==currobjectid){
                        d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                    }
                }
            }
        }
    });
    $(prefix+"lbfx_search").rylabel({left:offsetx, top:offsety, caption:"Ricerca"});offsety+=20;
    var txfx_search=$(prefix+"txfx_search").rytext({left:offsetx, top:offsety, width:300, 
        assigned:function(){
            setTimeout(function(){operx_refresh.engage();}, 100);
        }
    });offsety+=30;
    
    $(prefix+"lbfx_typology").rylabel({left:offsetx, top:offsety, caption:"Tipologia*"});offsety+=20;
    $(prefix+"txfx_typology").ryhelper({
        left:offsetx, top:offsety, width:300, formid:formid, table:"QVARROWTYPES", title:"Tipologie freccia",
        open:function(o){
            o.where("");
        },
        select:"GENRETYPEID,MOTIVETYPEID,BOWTYPEID,TARGETTYPEID",
        onselect:function(o,d){
            typedescr=d["DESCRIPTION"];
            currgenretypeid=d["GENRETYPEID"];
            currmotivetypeid=d["MOTIVETYPEID"];
            currbowtypeid=d["BOWTYPEID"];
            currtargettypeid=d["TARGETTYPEID"];
            setTimeout(function(){operx_refresh.engage();}, 100);
        },
        assigned:function(o){
            currarrowtypeid=o.value();
        },
        clear:function(){
            setTimeout(function(){operx_refresh.engage();}, 100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbfx_genre").rylabel({left:offsetx, top:offsety, caption:"Genere"});offsety+=20;
    var txfx_genre=$(prefix+"txfx_genre").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVGENRES", title:"Generi", multiple:false,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        assigned: function(o){
            currgenreid=o.value();
            refresh_summary();
        }
    });offsety+=30;
    
    $(prefix+"lbfx_motives").rylabel({left:offsetx, top:offsety, caption:"Motivi"});offsety+=20;
    var txfx_motives=$(prefix+"txfx_motives").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVMOTIVES", title:"Motivi", multiple:true,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currmotivetypeid});
        },
        assigned: function(){
            setTimeout(function(){operx_refresh.engage();}, 100);
        }
    });offsety+=30;
    
    $(prefix+"lbfx_parent").rylabel({left:offsetx, top:offsety, caption:"Genitore"});
    $(prefix+"lbfx_object").rylabel({left:offsetx+155, top:offsety, caption:"Oggetto"});offsety+=20;
    var txfx_parent=$(prefix+"txfx_parent").ryhelper({left:offsetx, top:offsety, width:145, 
        formid:formid, table:"QWINCLPARENTS", title:"Oggetti", multiple:false,
        open:function(o){
            o.where("( TYPOLOGYID='[=BOWTYPEID]' OR TYPOLOGYID='[=TARGETTYPEID]' )");
            o.args({"BOWTYPEID":currbowtypeid, "TARGETTYPEID":currtargettypeid});
        },
        assigned: function(o){
            curroparentid=o.value();
            txfx_object.clear();
        }
    });
    var txfx_object=$(prefix+"txfx_object").ryhelper({left:offsetx+155, top:offsety, width:145, 
        formid:formid, table:"", title:"Oggetti", multiple:false,
        open:function(o){
            if(curroparentid!=""){
                o.table("QWINCLCHILDREN");
                o.where("( TYPOLOGYID='[=BOWTYPEID]' OR TYPOLOGYID='[=TARGETTYPEID]' ) AND PARENTID='"+curroparentid+"'");
                o.args({"BOWTYPEID":currbowtypeid, "TARGETTYPEID":currtargettypeid});
            }
            else{
                o.table("QWOBJECTS");
                o.where("( TYPOLOGYID='[=BOWTYPEID]' OR TYPOLOGYID='[=TARGETTYPEID]' )");
                o.args({"BOWTYPEID":currbowtypeid, "TARGETTYPEID":currtargettypeid});
            }
        },
        assigned: function(o){
            currobjectid=o.value();
            refresh_summary();
        }
    });
    offsety+=30;
    
    $(prefix+"lbfx_date").rylabel({left:offsetx, top:offsety, caption:"Data massima"});
    $(prefix+"lbfx_amount").rylabel({left:offsetx+155, top:offsety, caption:"Quantit"+_utf8("a")+" &plusmn;5%"});
    offsety+=20;
    var txfx_date=$(prefix+"txfx_date").rydate({left:offsetx, top:offsety, width:145, 
        assigned:function(){
            setTimeout(function(){operx_refresh.engage();}, 100);
        }
    });
    var txfx_amount=$(prefix+"txfx_amount").rynumber({left:offsetx+155, top:offsety, width:145, numdec:0, 
        assigned:function(){
            setTimeout(function(){operx_refresh.engage();}, 100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbfx_yesno").rylabel({left:offsetx, top:offsety, caption:"Seleziona..."});offsety+=20;
    var txfx_yesno=$(prefix+"txfx_yesno").rylist({left:offsetx, top:offsety, width:200,
        assigned:function(){
            setTimeout(function(){operx_refresh.engage();}, 100);
        }
    })
    .additem({caption:"Disponibili", key:0})
    .additem({caption:"Incluse", key:1})
    .additem({caption:"Libere", key:2})
    .additem({caption:"Tutte", key:3});
    offsety+=50;
    
    var operx_refresh=$(prefix+"operx_refresh").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            griddetails.clear();
            if((curroparentid!="" && currobjectid=="") || currarrowtypeid==""){
                return false;
            }
            var q="";
            var t=_likeescapize(txfx_search.value());
            
            var genreid=txfx_genre.value();
            var motiveid=txfx_motives.value();
            var objectid=txfx_object.value();
            var dataval=txfx_date.text();
            var amount=txfx_amount.value();
            var yesno=parseInt(txfx_yesno.key());
            
            //q="TYPOLOGYID='"+currarrowtypeid+"' AND QUIVERID='"+currsysid+"'";
            q="TYPOLOGYID='"+currarrowtypeid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(genreid!="")
                q+=" AND GENREID='"+genreid+"'";
            if(motiveid!="")
                q+=" AND MOTIVEID IN ('"+motiveid.replace("|", "','")+"')";
            if(objectid!="")
                q+=" AND (BOWID='"+objectid+"' OR TARGETID='"+objectid+"')";
            if(dataval!="")
                q+=" AND AUXTIME<=[:TIME("+dataval+"235959)]";
            if(amount>0)
                q+=" AND (AMOUNT>="+(amount*0.95)+" AND AMOUNT<="+(amount*1.05)+")";
            switch(yesno){
                case 0: // Disponibili: Inclusa=True o Inclusioni=0
                    q+=" AND (QUIVERID='"+currsysid+"' OR INCLUSIONS=0)";
                    break;
                case 1: // Incluse: Inclusa=True
                    q+=" AND QUIVERID='"+currsysid+"'";
                    break;
                case 2: // Libere: Inclusioni=0
                    q+=" AND INCLUSIONS=0";
                    break;
            }

            griddetails.where(q);
            //griddetails.clause({"QUIVERID":currsysid});
            griddetails.query({
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

    var oper_add=$(prefix+"oper_add").rylabel({
        left:20,
        top:80,
        caption:"Includi selezione",
        button:true,
        click:function(o){
            winzProgress(formid);
            griddetails.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    s=s.split("|");
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di inserimento
                        stats[i]={
                            "function":"quivers_add",
                            "data":{
                                "QUIVERID":currsysid,
                                "ARROWID":s[i]
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
                                griddetails.dataload(
                                    function(){
                                        refresh_summary();
                                    }
                                );
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
    oper_add.enabled(0);
    
    var oper_remove=$(prefix+"oper_remove").rylabel({
        left:200,
        top:80,
        caption:"Escludi selezione",
        button:true,
        click:function(o){
            winzProgress(formid);
            griddetails.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    s=s.split("|");
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di inserimento
                        stats[i]={
                            "function":"quivers_remove",
                            "data":{
                                "QUIVERID":currsysid,
                                "ARROWID":s[i]
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
                                griddetails.dataload(
                                    function(){
                                        refresh_summary();
                                    }
                                );
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
    })
    oper_remove.enabled(0);
    
    $("#"+formid+"details_summary").css({position:"absolute", left:20, top:520, width:450, height:200});

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVQUIVERS", "QVQUIVERS");

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
                    objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO DEL CONTESTO
                    if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currsysid)}
                    lb_context_context.caption("Contesto: "+typedescr);
                    qv_autoconfigure(formid, currviewname, "QVQUIVER", currtypologyid, 1030, cacheext, 
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
                    griddetails.clear();
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVQUIVERS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_details_context.caption("Contesto: "+typedescr+" / "+context);
                            setTimeout( 
                                function(){
                                    operx_refresh.engage(
                                        function(){
                                            castFocus(prefix+"txfx_search");
                                        }
                                    );
                               }, 100 
                            );
                        }
                    });
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+typedescr+" / "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVQUIVERS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
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

    function refresh_summary(){
        if(currgenreid!="" && currobjectid!=""){
            RYQUE.query({
                sql:"SELECT QVGENRES.SYSID AS GENREID, QVGENRES.DESCRIPTION AS GENREDESCR, QWQUIVERIN.AMOUNT AS AMOUNT FROM QWQUIVERIN INNER JOIN QVGENRES ON QVGENRES.SYSID=QWQUIVERIN.GENREID WHERE QWQUIVERIN.QUIVERID='"+currsysid+"' AND QWQUIVERIN.GENREID='"+currgenreid+"' AND QWQUIVERIN.OBJECTID='"+currobjectid+"'",
                ready:function(v){
                    var sumgenres={};
                    for(var i in v){
                        var genreid=v[i]["GENREID"];
                        sumgenres[genreid]={};
                        sumgenres[genreid]["GENREDESCR"]=v[i]["GENREDESCR"];
                        sumgenres[genreid]["AMOUNT"]=parseFloat(v[i]["AMOUNT"]);
                    }
                    RYQUE.query({
                        sql:"SELECT QVGENRES.DESCRIPTION AS GENREDESCR, QWQUIVEROUT.AMOUNT AS AMOUNT FROM QWQUIVEROUT INNER JOIN QVGENRES ON QVGENRES.SYSID=QWQUIVEROUT.GENREID WHERE QWQUIVEROUT.QUIVERID='"+currsysid+"' AND QWQUIVEROUT.GENREID='"+currgenreid+"' AND QWQUIVEROUT.OBJECTID='"+currobjectid+"'",
                        ready:function(v){
                            for(var i in v){
                                var genreid=v[i]["GENREID"];
                                sumgenres[genreid]={};
                                sumgenres[genreid]["GENREDESCR"]=v[i]["GENREDESCR"];
                                if(_isset(sumgenres[genreid]["AMOUNT"]))
                                    sumgenres[genreid]["AMOUNT"]-=parseFloat(v[i]["AMOUNT"]);
                                else
                                    sumgenres[genreid]["AMOUNT"]=-parseFloat(v[i]["AMOUNT"]);
                            }
                            var t="";
                            t+="<table>";
                            t+="<tr>";
                            t+="<th style='width:200px;font-weight:bold;'>Genere</th><th style='width:120px;font-weight:bold;text-align:right'>Totale</th>";
                            t+="</tr>";
                            for(var i in sumgenres){
                                t+="<tr>";
                                t+="<td>"+sumgenres[i]["GENREDESCR"]+"</td><td style='text-align:right'>"+_nformat(sumgenres[i]["AMOUNT"], 2)+"</td>";
                                t+="</tr>";
                            }
                            t+="</table>";
                            $("#"+formid+"details_summary").html(t);
                            setTimeout(function(){operx_refresh.engage();}, 100);
                            
                        }
                    });
                }
            });
        }
        else{
            $("#"+formid+"details_summary").html("");
            setTimeout(function(){operx_refresh.engage();}, 100);
        }
    }
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xfocus:"NAME", xengage:oper_contextengage, details:3, files:4} );
}

