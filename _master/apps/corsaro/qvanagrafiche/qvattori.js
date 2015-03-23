/****************************************************************************
* Name:            qvattori.js                                              *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvattori(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0ATTORI00000");
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
        from:"QWOBJECTS",
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

            q="TYPOLOGYID='"+currtypologyid+"' AND SYSID<>[:SYSID(0ATTJOLLYRIC)]";
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
            data["DESCRIPTION"]="(nuovo attore)";
            data["TYPOLOGYID"]=currtypologyid;
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

    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Identificatore"});
    $(prefix+"NAME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NAME"});
    offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200});
    //txdescr.enabled(0);
    offsety+=30;
    
    $(prefix+"LB_EMAIL").rylabel({left:20, top:offsety, caption:"Email"});
    var txemail=$(prefix+"EMAIL").rytext({left:120, top:offsety, width:300, maxlen:50});
    //txemail.enabled(0);
    offsety+=50;
    
    $(prefix+"LB_IDENTITA").rylabel({left:20, top:offsety, caption:"<b>Identit&agrave; Anagrafica</b>"});
    offsety+=30;
    
    $(prefix+"LB_AZIENDAID").rylabel({left:20, top:offsety, caption:"Azienda"});
    var txaziendaid=$(prefix+"AZIENDAID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"AZIENDAID", formid:formid, table:"QW_AZIENDE", title:"Aziende",
        open:function(o){
            flagclick=true;
            o.where("");
        },
        select:"EMAIL",
        onselect:function(o, d){
            if(flagclick){
                txdescr.value(d["DESCRIPTION"]);
                if(d["EMAIL"]!="")
                    txemail.value(d["EMAIL"]);
                flagclick=false;
            }
        },
        assigned:function(o){
            if(o.value()!=""){
                txproprietaid.clear();
                txufficioid.clear();
                txpersonaid.clear();
            }
        }
    });offsety+=30;
    
    $(prefix+"LB_PROPRIETAID").rylabel({left:20, top:offsety, caption:"Propriet&agrave;"});
    var txproprietaid=$(prefix+"PROPRIETAID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"PROPRIETAID", formid:formid, table:"QW_PROPRIETA", title:"Propriet&agrave;",
        open:function(o){
            flagclick=true;
            o.where("");
        },
        select:"EMAIL",
        onselect:function(o, d){
            if(flagclick){
                txdescr.value(d["DESCRIPTION"]);
                if(d["EMAIL"]!="")
                    txemail.value(d["EMAIL"]);
                flagclick=false;
            }
        },
        assigned:function(o){
            if(o.value()!=""){
                txaziendaid.clear();
                txufficioid.clear();
                txpersonaid.clear();
            }
        }
    });offsety+=30;
    
    $(prefix+"LB_UFFICIOID").rylabel({left:20, top:offsety, caption:"Ufficio"});
    var txufficioid=$(prefix+"UFFICIOID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"UFFICIOID", formid:formid, table:"QW_UFFICI", title:"Uffici",
        open:function(o){
            flagclick=true;
            o.where("");
        },
        select:"EMAIL",
        onselect:function(o, d){
            if(flagclick){
                txdescr.value(d["DESCRIPTION"]);
                if(d["EMAIL"]!="")
                    txemail.value(d["EMAIL"]);
                flagclick=false;
            }
        },
        assigned:function(o){
            if(o.value()!=""){
                txaziendaid.clear();
                txproprietaid.clear();
                txpersonaid.clear();
            }
        }
    });offsety+=30;
    
    $(prefix+"LB_PERSONAID").rylabel({left:20, top:offsety, caption:"Persona"});
    var txpersonaid=$(prefix+"PERSONAID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"PERSONAID", formid:formid, table:"QW_PERSONE", classtable:"QW_CLASSIPERSONA", title:"Persone",
        open:function(o){
            flagclick=true;
            o.where("");
        },
        select:"EMAIL",
        onselect:function(o, d){
            if(flagclick){
                txdescr.value(d["DESCRIPTION"]);
                if(d["EMAIL"]!="")
                    txemail.value(d["EMAIL"]);
                flagclick=false;
            }
        },
        assigned:function(o){
            if(o.value()!=""){
                txaziendaid.clear();
                txproprietaid.clear();
                txufficioid.clear();
            }
        }
    });offsety+=50;
    
    $(prefix+"LB_AUTENTICAZIONE").rylabel({left:20, top:offsety, caption:"<b>Autenticazione Ego</b>"});
    offsety+=30;
    
    $(prefix+"LB_UTENTEID").rylabel({left:20, top:offsety, caption:"Utente"});
    var txutenteid=$(prefix+"UTENTEID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"UTENTEID", formid:formid, table:"QWUSERS", title:"Utenti",
        open:function(o){
            flagclick=true;
            o.where("");
        },
        select:"EMAIL",
        onselect:function(o, d){
            if(
                txaziendaid.value()=="" &&
                txproprietaid.value()=="" &&
                txufficioid.value()=="" &&
                txpersonaid.value()==""
            ){
                if(flagclick){
                    txdescr.value(d["DESCRIPTION"]);
                    txemail.value(d["EMAIL"]);
                }
            }
            // SE L'EMAIL E' RIMASTA VUOTA PREDO QUELLA DI EGO
            if(txemail.value()==""){
                if(flagclick){
                    txemail.value(d["EMAIL"]);
                }
            }
            flagclick=false;
        },
        assigned:function(o){
            if(o.value()!=""){
                txruoloid.clear();
            }
        }
    });offsety+=30;
    
    $(prefix+"LB_RUOLOID").rylabel({left:20, top:offsety, caption:"Ruolo"});
    var txruoloid=$(prefix+"RUOLOID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"RUOLOID", formid:formid, table:"QWROLES", title:"Ruoli",
        open:function(o){
            flagclick=true;
            o.where("");
        },
        onselect:function(o, d){
            if(
                txaziendaid.value()=="" &&
                txproprietaid.value()=="" &&
                txufficioid.value()=="" &&
                txpersonaid.value()==""
            ){
                if(flagclick){
                    txdescr.value(d["DESCRIPTION"]);
                }
            }
            flagclick=false;
        },
        assigned:function(o){
            if(o.value()!=""){
                txutenteid.clear();
            }
        }
    });offsety+=50;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    offsety+=30;
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=qv_mask2object(formid, "C", currsysid);
            data["DESCRIPTION"]=txdescr.value();
            data["EMAIL"]=txemail.value();
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
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_ATTORIJOIN");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Documenti"}
        ],
        select:function(i, p){
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
                    qv_maskclear(formid, "C");
                    txdescr.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_ATTORI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            txdescr.value(v[0]["DESCRIPTION"]);
                            txemail.value(v[0]["EMAIL"]);
                            qv_object2mask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            castFocus(prefix+"AZIENDAID");
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
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xengage:oper_contextengage, files:3} );
}

