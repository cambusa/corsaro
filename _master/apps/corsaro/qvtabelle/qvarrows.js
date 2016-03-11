/****************************************************************************
* Name:            qvarrows.js                                              *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvarrows(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    var currsysid="";
    var currtypologyid="";
    var currviewname="";
    var currgenretypeid="";
    var currmotivetypeid="";
    var currrounding=2;
    var currbowunit="";
    var currtargetunit="";
    var currbowtypeid="";
    var currtargettypeid="";
    var curroparentid="";
    var currobjectid="";
    var typedescr="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var cacheext={};
    var loadedsysid="";
    var loadedsysidx="";
    
    // DEFINIZIONE TAB SELEZIONE
    var offsetx=480;
    var offsety=80;
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:450,
        height:500,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWARROWS",
        orderby:"DESCRIPTION",
        limit:10000,
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:150},
            {id:"AUXTIME",caption:"Data",width:90,type:"/"},
            {id:"BOWID",caption:"",width:0},
            {id:"AMOUNT",caption:"Quantità",width:120,type:"2"}
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
        before:function(o,d){
            if(currobjectid!=""){
                for(var i in d){
                    if(d[i]["BOWID"]==currobjectid){
                        d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                    }
                }
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    $(prefix+"lbf_search").rylabel({left:offsetx, top:offsety, caption:"Ricerca"});
    offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:offsetx, top:offsety, width:300, assigned:function(){oper_refresh.engage()}});
    offsety+=30;
    
    $(prefix+"lbf_typology").rylabel({left:offsetx, top:offsety, caption:"Tipologia*"});
    offsety+=20;
    $(prefix+"txf_typology").ryhelper({
        left:offsetx, top:offsety, width:300, formid:formid, table:"QVARROWTYPES", title:"Tipologie freccia",
        open:function(o){
            o.where("SIMPLE=1");
        },
        select:"VIEWNAME,GENRETYPEID,MOTIVETYPEID,BOWTYPEID,TARGETTYPEID",
        onselect:function(o,d){
            typedescr=d["DESCRIPTION"];
            currviewname=__(d["VIEWNAME"]);
            currgenretypeid=__(d["GENRETYPEID"]);
            currmotivetypeid=__(d["MOTIVETYPEID"]);
            currbowtypeid=__(d["BOWTYPEID"]);
            currtargettypeid=__(d["TARGETTYPEID"]);
            RYQUE.query({
                sql:"SELECT BTYPES.TIMEUNIT AS BOWUNIT, TTYPES.TIMEUNIT AS TARGETUNIT FROM QVARROWTYPES LEFT JOIN QVOBJECTTYPES BTYPES ON BTYPES.SYSID=QVARROWTYPES.BOWTYPEID LEFT JOIN QVOBJECTTYPES TTYPES ON TTYPES.SYSID=QVARROWTYPES.TARGETTYPEID WHERE QVARROWTYPES.SYSID='"+currtypologyid+"'",
                ready:function(v){
                    currbowunit=__(v[0]["BOWUNIT"]);
                    currtargetunit=__(v[0]["TARGETUNIT"]);
                    oper_new.enabled(currtypologyid!="");
                    setTimeout(function(){oper_refresh.engage()},100);
                }
            });
        },
        assigned:function(o){
            currtypologyid=o.value();
            typedescr="";
            currviewname="";
            currgenretypeid="";
            currmotivetypeid="";
            currbowtypeid="";
            currtargettypeid="";
            currbowunit="";
            currtargetunit="";
        },
        clear:function(){
            oper_new.enabled(0);
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_genre").rylabel({left:offsetx, top:offsety, caption:"Genere*"});
    offsety+=20;
    var txf_genre=$(prefix+"txf_genre").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVGENRES", title:"Generi", multiple:false,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        onselect:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        },
        clear:function(){
            oper_new.enabled(0);
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_motives").rylabel({left:offsetx, top:offsety, caption:"Motivi*"});
    offsety+=20;
    var txf_motives=$(prefix+"txf_motives").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVMOTIVES", title:"Motivi", multiple:true,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currmotivetypeid});
        },
        onselect:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        },
        clear:function(){
            oper_new.enabled(0);
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_parent").rylabel({left:offsetx, top:offsety, caption:"Genitore"});
    $(prefix+"lbf_object").rylabel({left:offsetx+155, top:offsety, caption:"Oggetto"});
    offsety+=20;
    var txf_parent=$(prefix+"txf_parent").ryhelper({left:offsetx, top:offsety, width:145, 
        formid:formid, table:"QWINCLPARENTS", title:"Oggetti", multiple:false,
        open:function(o){
            o.where("( TYPOLOGYID='[=BOWTYPEID]' OR TYPOLOGYID='[=TARGETTYPEID]' )");
            o.args({"BOWTYPEID":currbowtypeid, "TARGETTYPEID":currtargettypeid});
        },
        assigned: function(o){
            curroparentid=o.value();
            txf_object.clear();
        }
    });
    var txf_object=$(prefix+"txf_object").ryhelper({left:offsetx+155, top:offsety, width:145, 
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
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_date").rylabel({left:offsetx, top:offsety, caption:"Data massima"});
    $(prefix+"lbf_amount").rylabel({left:offsetx+155, top:offsety, caption:"Quantità"+" &plusmn;5%"});
    offsety+=20;
    var txf_date=$(prefix+"txf_date").rydate({left:offsetx, top:offsety,  width:145, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    var txf_amount=$(prefix+"txf_amount").rynumber({left:offsetx+155, top:offsety,  width:145, numdec:0, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=38;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            objgridsel.clear()
            if((curroparentid!="" && currobjectid=="") || currtypologyid==""){
                return;
            }
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            var genreid=txf_genre.value();
            var motiveid=txf_motives.value();
            var objectid=txf_object.value();
            var dataval=txf_date.text();
            var amount=txf_amount.value();
            
            oper_new.enabled( currtypologyid!="" && genreid!="" && motiveid!="");

            q="TYPOLOGYID='"+currtypologyid+"'";
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

            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });
    offsety+=50;
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var motivoid=txf_motives.value().substr(0, RYQUE.lenid());
            var data = new Object();
            data["DESCRIPTION"]="(nuova freccia)";
            data["TYPOLOGYID"]=currtypologyid;
            data["GENREID"]=txf_genre.value();
            data["MOTIVEID"]=motivoid;
            data["STATUS"]=-1;
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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
    oper_new.enabled(0);
    offsety+=50;
    
    var oper_print=$(prefix+"oper_print").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "@customize/corsaro/reporting/rep_arrows.php")
        }
    });
    oper_print.enabled(0);
    offsety+=50;

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "arrows");
        }
    });
    oper_delete.enabled(0);
    offsety+=40;
    
    $(prefix+"lb_warning").rylabel({left:20, top:offsety, caption:"* Campi obbligatori per abilitare l'inserimento"});

    // DEFINIZIONE TAB CONTESTO
    offsetx=340;
    offsety=50;
    var lb_context_context=$(prefix+"context_context").rylabel({left:20, top:offsety, caption:""});
    offsety+=40;
    
    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Nome"});
    $(prefix+"NAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NAME"});offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    
    offsety+=40;

    $(prefix+"LB_BOW").rylabel({left:120, top:offsety, caption:"<b>Partenza</b>"});
    $(prefix+"LB_TARGET").rylabel({left:offsetx+100, top:offsety, caption:"<b>Arrivo</b>"});
    
    offsety+=30;
    
    $(prefix+"LB_OBJECTID").rylabel({left:20, top:offsety, caption:"Oggetto"});
    $(prefix+"BOWID").ryhelper({
        left:120, top:offsety, width:180, datum:"C", tag:"BOWID", formid:formid, table:"QWOBJECTS", title:"Oggetto",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currbowtypeid});
        }
    });
    $(prefix+"TARGETID").ryhelper({
        left:offsetx+100, top:offsety, width:180, datum:"C", tag:"TARGETID", formid:formid, table:"QWOBJECTS", title:"Oggetto",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currtargettypeid});
        }
    });
    offsety+=30;
    
    $(prefix+"LB_TIME").rylabel({left:20, top:offsety, caption:"Data"});
    $(prefix+"BOWDATE").rydate({left:120, top:offsety, datum:"C", tag:"BOWTIME"})
    .link(
        $(prefix+"BOWTIME").rytime({left:250, top:offsety})
    );
    $(prefix+"TARGETDATE").rydate({left:offsetx+100, top:offsety, datum:"C", tag:"TARGETTIME"})
    .link(
        $(prefix+"TARGETTIME").rytime({left:offsetx+230, top:offsety})
    );
    offsety+=50;

    $(prefix+"LB_AUXTIME").rylabel({left:20, top:offsety, caption:"Registrazione"});
    $(prefix+"AUXDATE").rydate({left:120, top:offsety, datum:"C", tag:"AUXTIME"})
    .link(
        $(prefix+"AUXTIME").rytime({left:250, top:offsety})
    );
    offsety+=30;
    
    $(prefix+"LB_MOTIVEID").rylabel({left:20, top:offsety, caption:"Motivo"});
    $(prefix+"MOTIVEID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"MOTIVEID", formid:formid, table:"QVMOTIVES", title:"Motivi",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currmotivetypeid});
        }
    });offsety+=30;
    
    $(prefix+"LB_GENREID").rylabel({left:20, top:offsety, caption:"Genere"});
    $(prefix+"GENREID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"GENREID", formid:formid, table:"QVGENRES", title:"Generi",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        select:"ROUNDING",
        onselect:function(o, d){
            tx_amount.numdec( parseInt(d["ROUNDING"]) );
        },
        assigned:function(o){
            if(o.value()=="")
                tx_amount.numdec(2);
        }
    });offsety+=30;
    
    $(prefix+"LB_AMOUNT").rylabel({left:20, top:offsety, caption:"Quantità"});
    var tx_amount=$(prefix+"AMOUNT").rynumber({left:120, top:offsety, width:200, numdec:2, minvalue:0, datum:"C", tag:"AMOUNT"});offsety+=30;
    
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Riferimento"});
    $(prefix+"REFERENCE").rytext({left:120, top:offsety, width:200, datum:"C", tag:"REFERENCE"});offsety+=30;
    
    $(prefix+"LB_REFARROWID").rylabel({left:20, top:offsety, caption:"Madre"});
    $(prefix+"REFARROWID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"REFARROWID", formid:formid, table:"QVARROWS", title:"Frecce",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currtypologyid});
        }
    });offsety+=30;
    
    $(prefix+"LB_CONSISTENCY").rylabel({left:20, top:offsety, caption:"Concretezza"});
    $(prefix+"CONSISTENCY").rylist({left:120, top:offsety, width:200, datum:"C", tag:"CONSISTENCY"})
        .additem({caption:"", key:""})
        .additem({caption:"Effettiva", key:0})
        .additem({caption:"Equivalente", key:1})
        .additem({caption:"Simulata", key:2})
        .additem({caption:"Astratta", key:3});offsety+=30;

    $(prefix+"LB_AVAILABILITY").rylabel({left:20, top:offsety, caption:"Disponibilità"});
    $(prefix+"AVAILABILITY").rylist({left:120, top:offsety, width:200, datum:"C", tag:"AVAILABILITY"})
        .additem({caption:"", key:""})
        .additem({caption:"Disponibile", key:0})
        .additem({caption:"Bloccato", key:1})
        .additem({caption:"Archiviato", key:2});offsety+=30;
        
    $(prefix+"LB_SCOPE").rylabel({left:20, top:offsety, caption:"Visibilità"});
    $(prefix+"SCOPE").rylist({left:120, top:offsety, width:200, datum:"C", tag:"SCOPE"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});offsety+=30;

    $(prefix+"LB_UPDATING").rylabel({left:20, top:offsety, caption:"Modificabilità"});
    $(prefix+"UPDATING").rylist({left:120, top:offsety, width:200, datum:"C", tag:"UPDATING"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});offsety+=30;

    $(prefix+"LB_DELETING").rylabel({left:20, top:offsety, caption:"Cancellabilità"});
    $(prefix+"DELETING").rylist({left:120, top:offsety, width:200, datum:"C", tag:"DELETING"})
        .additem({caption:"", key:""})
        .additem({caption:"Pubblico", key:0})
        .additem({caption:"Protetto", key:1})
        .additem({caption:"Privato", key:2});offsety+=30;

    $(prefix+"LB_STATUS").rylabel({left:20, top:offsety, caption:"Stato"});
    $(prefix+"STATUS").rylist({left:120, top:offsety, width:200, datum:"C", tag:"STATUS"})
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

    $(prefix+"LB_PROVIDER").rylabel({left:20, top:offsety, caption:"Origine"}).enabled(false);
    $(prefix+"PROVIDER").rytext({left:120, top:offsety, width:200}).enabled(false);
    $(prefix+"LB_PARCEL").rylabel({left:offsetx+20, top:offsety, caption:"Lotto"}).enabled(false);
    $(prefix+"PARCEL").rytext({left:offsetx+100, top:offsety, width:200}).enabled(false);
    offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    
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
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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

    // DEFINIZIONE TAB DETTAGLI
    var lb_details_context=$(prefix+"details_context").rylabel({left:20, top:50, caption:""});
    var oper_detailsengage=$(prefix+"oper_detailsengage").rylabel({
        left:20,
        top:90,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "X", currsysid);
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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
    $(prefix+"REGISTRY").ryedit({left:20, top:120, width:700, height:400, flat:1, datum:"X", tag:"REGISTRY"});

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVARROWS", "QVARROWS");

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
            else if(p==3){
                // PROVENGO DAI DETTAGLI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_detailsengage, {
                    abandon:function(){
                        loadedsysidx="";
                    }
                });
            }
            if(i==1){
                loadedsysid="";
                loadedsysidx="";
            }
            else if(i==2){
                if(currsysid==loadedsysid){
                    flagsuspend=true;
                }
            }
            else if(i==3){
                if(currsysid==loadedsysidx){
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
                    managetimeunit();
                    qv_autoconfigure(formid, currviewname, "QVARROW", currtypologyid, offsety, cacheext, 
                        function(t, y){
                            RYQUE.query({
                                sql:"SELECT * FROM "+t+" WHERE SYSID='"+currsysid+"'",
                                ready:function(v){
                                    RYWINZ.ToMask(formid, "C", v[0]);
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
                    RYWINZ.MaskClear(formid, "X");
                    RYQUE.query({
                        sql:"SELECT DESCRIPTION,REGISTRY FROM QVARROWS WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "X", v[0]);
                            context=v[0]["DESCRIPTION"];
                            lb_details_context.caption("Contesto: "+typedescr+" / "+context);
                            loadedsysidx=currsysid;
                        }
                    });
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+typedescr+" / "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVARROWS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
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
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    objtabs.enabled(4,false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid);
    function managetimeunit(){
        var v=(currbowunit=="S" || currtargetunit=="S");
        globalobjs[formid+"BOWTIME"].visible(v);
        globalobjs[formid+"TARGETTIME"].visible(v);
        globalobjs[formid+"AUXTIME"].visible(v);
        globalobjs[formid+"STATUSTIME"].visible(v);
    }
}

