/****************************************************************************
* Name:            qvmagazzini.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvmagazzini(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0UFFICI00000");
    var collocazionitype=RYQUE.formatid("0COLLOCAZ000");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var loadedsysid2="";
    var sospendirefresh=false;
    
    var currcollid="";
    var flagcollnuova=false;
    var lastzona="";
    var lastscaffale="";
    var lastripiano="";
    
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
        from:"QW_UFFICI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:200}
        ],
        changerow:function(o,i){
            currsysid="";
            loadedsysid="";
            loadedsysid2="";
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            objtabs.enabled(4,false);
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currsysid=d;
            objtabs.enabled(2,true);
            objtabs.enabled(3,true);
            objtabs.enabled(4,true);
            oper_print.enabled(1);
            oper_delete.enabled(1);
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
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            oper_refresh.engage()
        }
    });offsety+=30;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=_likeescapize(txf_search.value());

            q="TYPOLOGYID='"+currtypologyid+"' AND MAGAZZINO=1";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

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
            data["DESCRIPTION"]="(nuovo magazzino)";
            data["TYPOLOGYID"]=currtypologyid;
            data["MAGAZZINO"]="1";
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
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

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;
    
    $(prefix+"LB_COLLOCAZIONE").rylabel({left:20, top:offsety, caption:"Collocazione"});
    $(prefix+"COLLOCAZIONE").rytext({left:120, top:offsety, width:300, datum:"C", tag:"COLLOCAZIONE"});
    offsety+=30;
    
    $(prefix+"LB_TELEFONO").rylabel({left:20, top:offsety, caption:"Telefono"});
    $(prefix+"TELEFONO").rytext({left:120, top:offsety, width:200, maxlen:30, datum:"C", tag:"TELEFONO"});
    $(prefix+"LB_FAX").rylabel({left:385, top:offsety, caption:"Fax"});
    $(prefix+"FAX").rytext({left:430, top:offsety, width:200, maxlen:30, datum:"C", tag:"FAX"});
    offsety+=30;
    
    $(prefix+"LB_EMAIL").rylabel({left:20, top:offsety, caption:"Email"});
    $(prefix+"EMAIL").rytext({left:120, top:offsety, width:200, maxlen:50, datum:"C", tag:"EMAIL"});
    offsety+=30;

    $(prefix+"LB_AZIENDAID").rylabel({left:20, top:offsety, caption:"Azienda"});
    var txaziendaid=$(prefix+"AZIENDAID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"AZIENDAID", formid:formid, table:"QW_AZIENDE", title:"Aziende",
        open:function(o){
            o.where("");
        },
        assigned:function(o){
            if(o.value()!=""){
                txproprietaid.clear();
            }
        }
    });offsety+=30;
    
    $(prefix+"LB_PROPRIETAID").rylabel({left:20, top:offsety, caption:"Propriet&agrave;"});
    var txproprietaid=$(prefix+"PROPRIETAID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"PROPRIETAID", formid:formid, table:"QW_PROPRIETA", title:"Propriet&agrave;",
        open:function(o){
            o.where("");
        },
        assigned:function(o){
            if(o.value()!=""){
                txaziendaid.clear();
            }
        }
    });offsety+=30;
    
    $(prefix+"LB_CONTODEFAULTID").rylabel({left:20, top:offsety, caption:"Conto predef."});
    $(prefix+"CONTODEFAULTID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"CONTODEFAULTID", formid:formid, table:"QW_CONTI", title:"Scelta conto predefinito",
        open:function(o){
            o.where("SYSID IN (SELECT SYSID FROM QW_CONTI WHERE UFFICIOID='"+currsysid+"')");
        }
    });offsety+=30;
    
    $(prefix+"LB_RESPONSABILEID").rylabel({left:20, top:offsety, caption:"Responsabile"});
    $(prefix+"RESPONSABILEID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"RESPONSABILEID", formid:formid, table:"QW_PERSONE", title:"Responsabile",
        open:function(o){
            o.where("");
        }
    });offsety+=30;
    
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
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
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

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_UFFICI");

    // DEFINIZIONE TAB COLLOCAZIONI
    offsety=80;
    var lb_collocazioni_context=$(prefix+"collocazioni_context").rylabel({left:20, top:50, caption:""});
    
    $(prefix+"lbm_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txm_search=$(prefix+"txm_search").rytext({left:100, top:offsety, width:430, 
        assigned:function(){
            setTimeout(function(){operm_refresh.engage()}, 100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbm_articolo").rylabel({left:20, top:offsety, caption:"Articolo"});
    var txm_articolo=$(prefix+"txm_articolo").ryhelper({left:100, top:offsety, width:150, 
        formid:formid, table:"QW_ARTICOLI", title:"Articoli", multiple:false,
        open:function(o){
            o.where("");
        },
        assigned:function(){
            setTimeout(function(){operm_refresh.engage()}, 100);
        },
        clear:function(){
            setTimeout(function(){operm_refresh.engage()}, 100);
        }
    });
    $(prefix+"lbm_zona").rylabel({left:320, top:offsety, caption:"Zona"});
    var txm_zona=$(prefix+"txm_zona").rytext({left:380, top:offsety, width:150, 
        assigned:function(o){
            lastzona=o.value();
            setTimeout(function(){operm_refresh.engage()}, 100);
        },
        clear:function(){
            lastzona="";
            setTimeout(function(){operm_refresh.engage()}, 100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbm_scaffale").rylabel({left:20, top:offsety, caption:"Scaffale"});
    var txm_scaffale=$(prefix+"txm_scaffale").rytext({left:100, top:offsety, width:150, 
        assigned:function(o){
            lastscaffale=o.value();
            setTimeout(function(){operm_refresh.engage()}, 100);
        },
        clear:function(){
            lastscaffale="";
            setTimeout(function(){operm_refresh.engage()}, 100);
        }
    });
    $(prefix+"lbm_ripiano").rylabel({left:320, top:offsety, caption:"Ripiano"});
    var txm_ripiano=$(prefix+"txm_ripiano").rytext({left:380, top:offsety, width:150, 
        assigned:function(o){
            lastripiano=o.value();
            setTimeout(function(){operm_refresh.engage()}, 100);
        },
        clear:function(){
            lastripiano="";
            setTimeout(function(){operm_refresh.engage()}, 100);
        }
    });
    
    var operm_refresh=$(prefix+"operm_refresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            if(!sospendirefresh){
                gridcollocazioni.clear()
                var q="";
                var t=_likeescapize(txm_search.value());
                var articoloid=txm_articolo.value();
                
                q+="MAGAZZINOID='"+currsysid+"'";
                if(t!=""){
                    q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(ARTICOLO)] LIKE '%[=ARTICOLO]%' )";
                }
                if(articoloid!=""){
                    q+=" AND REFGENREID='"+articoloid+"'";
                }
                if(lastzona!=""){
                    q+=" AND [:UPPER(ZONA)]='"+lastzona.toUpperCase()+"'";
                }
                if(lastscaffale!=""){
                    q+=" AND [:UPPER(SCAFFALE)]='"+lastscaffale.toUpperCase()+"'";
                }
                if(lastripiano!=""){
                    q+=" AND [:UPPER(RIPIANO)]='"+lastripiano.toUpperCase()+"'";
                }
                gridcollocazioni.where(q);
                gridcollocazioni.query({
                    args:{
                        "DESCRIPTION":t,
                        "ARTICOLO":t
                    }
                });
            }
        }
    });
    var operm_reset=$(prefix+"operm_reset").rylabel({
        left:650,
        top:140,
        caption:"&nbsp;Pulisci&nbsp;&nbsp;",
        button:true,
        click:function(o){
            sospendirefresh=true;
            txm_search.clear();
            txm_articolo.clear();
            txm_zona.clear();
            txm_scaffale.clear();
            txm_ripiano.clear();
            sospendirefresh=false;
            setTimeout(function(){operm_refresh.engage()}, 100);
        }
    });
    offsety+=35;
    
    var gridcollocazioni=$(prefix+"gridcollocazioni").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_COLLOCAZIONIJOIN",
        orderby:"ZONA,SCAFFALE,RIPIANO,COORDINATA",
        columns:[
            {id:"ARTICOLO",caption:"Articolo",width:220},
            {id:"ZONA",caption:"Zona",width:100},
            {id:"SCAFFALE",caption:"Scaffale",width:100},
            {id:"RIPIANO",caption:"Ripiano",width:100},
            {id:"COORDINATA",caption:"Coordinata",width:100}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            RYWINZ.MaskClear(formid, "M");
            qv_maskenabled(formid, "M", 0);
            operm_unsaved.visible(0);
            operm_update.enabled(0);
            operm_delete.enabled(o.isselected());
            currcollid="";
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            operm_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currcollid=d;
            if(window.console&&_sessioninfo.debugmode){console.log("Caricamento collocazione: "+currcollid)}
            RYQUE.query({
                sql:"SELECT * FROM QW_COLLOCAZIONI WHERE SYSID='"+currcollid+"'",
                ready:function(v){
                    qv_maskenabled(formid, "M", 1);
                    operm_update.enabled(1);
                    operm_delete.enabled(1);
                    RYWINZ.ToMask(formid, "M", v[0]);
                    operm_unsaved.visible(0);
                    if(flagcollnuova){
                        flagcollnuova=false;
                        castFocus(prefix+"COLLZONA");
                    }
                }
            });
        }
    });
    offsety=470;

    var operm_new=$(prefix+"operm_new").rylabel({
        left:20,
        top:offsety,
        caption:"Nuova collocazione",
        button:true,
        click:function(o){
            winzProgress(formid);
            var stats=[];
            var istr=0;
            if(RYWINZ.modified(formid)){
                // ISTRUZIONE DI SALVATAGGIO DELLA COLLOCAZIONE MODIFICATA
                var datasave=RYWINZ.ToObject(formid, "M", currcollid);
                stats[istr++]={
                    "function":"objects_update",
                    "data":datasave
                };
            }
            // ISTRUZIONE DI INSERIMENTO NUOVO MOVIMENTO
            var data = new Object();
            data["DESCRIPTION"]="(nuova collocazione)";
            data["TYPOLOGYID"]=collocazionitype;
            data["MAGAZZINOID"]=currsysid;
            data["ZONA"]=lastzona;
            data["SCAFFALE"]=lastscaffale;
            data["RIPIANO"]=lastripiano;
            stats[istr++]={
                "function":"objects_insert",
                "data":data
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
                            var newid=v["SYSID"];
                            // POPOLO IL GRID COL NUOVO MOVIMENTO
                            flagcollnuova=true;
                            gridcollocazioni.splice(0, 0, newid);
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

    var operm_unsaved=$(prefix+"operm_unsaved").rylabel({left:280, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    operm_unsaved.visible(0);
    
    var operm_delete=$(prefix+"operm_delete").rylabel({
        left:580,
        top:offsety,
        caption:"Elimina collocazioni",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, gridcollocazioni, "objects");
        }
    });
    offsety+=40;

    var operm_update=$(prefix+"operm_update").rylabel({
        left:20,
        top:offsety,
        caption:"Salva collocazione",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "M", currcollid);
            var descr="";
            if(tx_collzona.value()){
                descr+=tx_collzona.value();
            }
            if(tx_collscaffale.value()){
                descr+="/"+tx_collscaffale.value();
            }
            if(tx_collripiano.value()){
                descr+="/"+tx_collripiano.value();
            }
            if(tx_collcoordinata.value()){
                descr+="/"+tx_collcoordinata.value();
            }
            data["DESCRIPTION"]=descr;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            gridcollocazioni.dataload();
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
    offsety+=40;
    
    $(prefix+"LB_COLLZONA").rylabel({left:20, top:offsety, caption:"Zona"});
    var tx_collzona=$(prefix+"COLLZONA").rytext({left:100, top:offsety, width:240, datum:"M", tag:"ZONA",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=30;

    $(prefix+"LB_COLLSCAFFALE").rylabel({left:20, top:offsety, caption:"Scaffale"});
    var tx_collscaffale=$(prefix+"COLLSCAFFALE").rytext({left:100, top:offsety, width:240, datum:"M", tag:"SCAFFALE",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=30;

    $(prefix+"LB_COLLRIPIANO").rylabel({left:20, top:offsety, caption:"Ripiano"});
    var tx_collripiano=$(prefix+"COLLRIPIANO").rytext({left:100, top:offsety, width:240, datum:"M", tag:"RIPIANO",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=30;

    $(prefix+"LB_COLLCOORDINATA").rylabel({left:20, top:offsety, caption:"Coord."});
    var tx_collcoordinata=$(prefix+"COLLCOORDINATA").rytext({left:100, top:offsety, width:240, datum:"M", tag:"COORDINATA",
        changed:function(){
            operm_unsaved.visible(1);
        }
    });
    offsety+=30;

    $(prefix+"LB_COLLARTICOLOID").rylabel({left:20, top:offsety, caption:"Articolo"});
    var tx_collarticoloid=$(prefix+"COLLARTICOLOID").ryhelper({
        left:100, top:offsety, width:240, datum:"M", tag:"REFGENREID", formid:formid, table:"QW_ARTICOLI", title:"Articoli",
        open:function(o){
            o.where("");
        },
        assigned:function(){
            operm_unsaved.visible(1);
        },
        clear:function(){
            operm_unsaved.visible(1);
        }
    });

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Documenti"},
            {title:"Collocazioni"}
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
                loadedsysid2="";
            }
            else if(i==2){
                if(currsysid==loadedsysid){
                    flagsuspend=true;
                }
            }
            else if(i==4){
                if(currsysid==loadedsysid2){
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
                        sql:"SELECT * FROM QW_UFFICI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
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
                    break;
                case 4:
                    // CARICAMENTO COLLOCAZIONI
                    lb_collocazioni_context.caption("Contesto: "+context);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_collocazioni_context.caption("Contesto: "+context);
                            loadedsysid2=currsysid;
                            setTimeout(function(){operm_refresh.engage()}, 100);
                        }
                    });
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    objtabs.enabled(4,false);
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
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
    );
}

