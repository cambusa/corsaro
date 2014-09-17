/****************************************************************************
* Name:            qvpersone.js                                             *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvpersone(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0PERSONE0000");
    var context="";
    var bbl_context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var sospendirefresh=false;
    
    // DEFINIZIONE TAB SELEZIONE

    var offsety=80;
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_classe").rylabel({left:20, top:offsety, caption:"Classe"});
    var txf_classe=$(prefix+"txf_classe").ryhelper({left:100, top:offsety, width:200, 
        formid:formid, table:"QW_CLASSIPERSONA", title:"Classi", multiple:false,
        open:function(o){
            o.where("");
        },
        onselect:function(){
            refreshselection();
        },
        clear:function(){
            refreshselection();
        }
    });
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:630,
        top:80,
        width:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=_likeescapize(txf_search.value());
            var classeid=txf_classe.value();

            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            if(classeid!=""){
                if(q!=""){q+=" AND "}
                q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+classeid+"')";
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
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_classe.clear();
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
        from:"QW_PERSONE",
        orderby:"COGNOME",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:250, code:"DESCRIPTION"},
            {id:"INDIRIZZO", caption:"Indirizzo", width:250, code:"ADDRESS"},
            {id:"CAP", caption:"CAP", width:90, code:"ZIPCODE"},
            {id:"CITTA", caption:"Citt&agrave;", width:200, code:"CITY"},
            {id:"PROVINCIA", caption:"Pr", width:40, code:"ABBR_PROVINCE"},
            {id:"TELEFONO", caption:"Telefono", width:120, code:"PHONE"},
            {id:"CELLULARE", caption:"Cellulare", width:120, code:"MOBILEPHONE"},
            {id:"EMAIL", caption:"Email", width:250, code:"EMAIL"}
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
            data["DESCRIPTION"]="(nuova persona)";
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
        left:220,
        top:offsety,
        width:120,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_objects.php")
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:420,
        top:offsety,
        width:120,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "objects");
        }
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_NOME").rylabel({left:20, top:offsety, caption:"Nome"});
    var txnome=$(prefix+"NOME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NOME"});
    offsety+=30;

    $(prefix+"LB_COGNOME").rylabel({left:20, top:offsety, caption:"Cognome"});
    var txcognome=$(prefix+"COGNOME").rytext({left:120, top:offsety, width:300, datum:"C", tag:"COGNOME"});
    offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Intestazione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:100, datum:"C", tag:"DESCRIPTION"});
    offsety+=50;
    
    $(prefix+"LB_SESSO").rylabel({left:20, top:offsety, caption:"Sesso"});
    $(prefix+"SESSO").rylist({left:120, top:offsety, width:200, datum:"C", tag:"SESSO"})
        .additem({caption:"", key:""})
        .additem({caption:"Maschio", key:"M", code:"MALE"})
        .additem({caption:"Femmina", key:"F", code:"FEMALE"});
    offsety+=30;
    
    $(prefix+"LB_CODFISC").rylabel({left:20, top:offsety, caption:"Cod. Fisc."});
    $(prefix+"CODFISC").rytext({left:120, top:offsety, width:300, datum:"C", tag:"CODFISC"});
    offsety+=30;
    
    $(prefix+"LB_PIVA").rylabel({left:20, top:offsety, caption:"Partita IVA"});
    $(prefix+"PIVA").rytext({left:120, top:offsety, width:300, maxlen:30, datum:"C", tag:"PIVA"});
    offsety+=30;
    
    $(prefix+"LB_INDIRIZZO").rylabel({left:20, top:offsety, caption:"Indirizzo"});
    $(prefix+"INDIRIZZO").rytext({left:120, top:offsety, width:240, maxlen:100, datum:"C", tag:"INDIRIZZO"});
    $(prefix+"CIVICO").rytext({left:380, top:offsety, width:40, datum:"C", tag:"CIVICO"});
    offsety+=30;
    
    $(prefix+"LB_CAP").rylabel({left:20, top:offsety, caption:"C.A.P."});
    $(prefix+"CAP").rytext({left:120, top:offsety, width:60, maxlen:20, datum:"C", tag:"CAP"});
    
    $(prefix+"LB_CITTA").rylabel({left:200, top:offsety, caption:"Citt&agrave;"});
    $(prefix+"CITTA").rytext({left:240, top:offsety, width:250, maxlen:50, datum:"C", tag:"CITTA"});
    
    $(prefix+"LB_PROVINCIA").rylabel({left:520, top:offsety, caption:"Provincia"});
    $(prefix+"PROVINCIA").rytext({left:590, top:offsety, width:40, maxlen:30, datum:"C", tag:"PROVINCIA"});

    $(prefix+"oper_cerca").rylabel({
        left:640,
        top:offsety,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            qv_geography(formid,
                {
                    "type":"comuni",
                    "onselect":function(d){
                        globalobjs[formid+"CAP"].value(d["CAP"], true);
                        globalobjs[formid+"CITTA"].value(d["DESCRIPTION"], true);
                        globalobjs[formid+"PROVINCIA"].value(d["SIGLA"], true);
                    }
                }
            );
        }
    });

    offsety+=30;
    $(prefix+"LB_NAZIONE").rylabel({left:20, top:offsety, caption:"Nazione"});
    $(prefix+"NAZIONE").rytext({left:120, top:offsety, width:200, datum:"C", tag:"NAZIONE"});

    $(prefix+"LB_CITTADINANZA").rylabel({left:340, top:offsety, caption:"Cittadinanza"});
    $(prefix+"CITTADINANZA").rytext({left:430, top:offsety, width:200, datum:"C", tag:"CITTADINANZA"});
    offsety+=30;

    $(prefix+"LB_BEGIN").rylabel({left:120, top:offsety, caption:"Nascita"});
    $(prefix+"LB_END").rylabel({left:430, top:offsety, caption:"Morte"});
    offsety+=20;
    
    $(prefix+"LB_LUOGO").rylabel({left:20, top:offsety, caption:"Luogo"});
    $(prefix+"LUOGONASCITA").rytext({left:120, top:offsety, width:200, datum:"C", tag:"LUOGONASCITA"});
    $(prefix+"LUOGOMORTE").rytext({left:430, top:offsety, width:200, datum:"C", tag:"LUOGOMORTE"});
    offsety+=25;
    
    $(prefix+"LB_DATA").rylabel({left:20, top:offsety, caption:"Data"});
    $(prefix+"BEGINTIME").rydate({left:120, top:offsety, datum:"C", tag:"BEGINTIME"});
    $(prefix+"ENDTIME").rydate({left:430, top:offsety, defaultvalue:"99991231", datum:"C", tag:"ENDTIME"});
    offsety+=40;

    $(prefix+"LB_TELEFONO").rylabel({left:20, top:offsety, caption:"Telefono"});
    $(prefix+"TELEFONO").rytext({left:120, top:offsety, width:200, maxlen:30, datum:"C", tag:"TELEFONO"});
    $(prefix+"LB_CELLULARE").rylabel({left:370, top:offsety, caption:"Cellulare"});
    $(prefix+"CELLULARE").rytext({left:430, top:offsety, width:200, maxlen:30, datum:"C", tag:"CELLULARE"});
    offsety+=30;

    $(prefix+"LB_DOCUMENTO").rylabel({left:20, top:offsety, caption:"Documento"});
    $(prefix+"DOCUMENTO").rytext({left:120, top:offsety, width:200, maxlen:30, datum:"C", tag:"DOCUMENTO"});
    $(prefix+"LB_TESSERA").rylabel({left:370, top:offsety, caption:"Tessera"});
    $(prefix+"TESSERA").rytext({left:430, top:offsety, width:200, maxlen:30, datum:"C", tag:"TESSERA"});
    offsety+=30;

    $(prefix+"LB_EMAIL").rylabel({left:20, top:offsety, caption:"Email"});
    $(prefix+"EMAIL").rytext({left:120, top:offsety, width:300, maxlen:50, datum:"C", tag:"EMAIL"});
    offsety+=30;
    
    $(prefix+"LB_CONTODEFAULTID").rylabel({left:20, top:offsety, caption:"Conto predef."});
    $(prefix+"CONTODEFAULTID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"CONTODEFAULTID", formid:formid, table:"QW_CONTI", title:"Scelta conto predefinito",
        open:function(o){
            o.where("SYSID IN (SELECT SYSID FROM QW_CONTI WHERE TITOLAREID='"+currsysid+"')");
        }
    });offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    offsety+=30;
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var objclassi=$(prefix+"CLASSI").ryselections({"left":470, "top":110, "height":140, 
        "title":"Classi di appartenenza",
        "titlecode":"BELONGING_CLASS",
        "formid":formid, 
        "table":"QW_CLASSIPERSONA", 
        "where":"",
        "upward":1,
        "parenttable":"QVOBJECTS", 
        "parentfield":"SYSID",
        "selectedtable":"QVOBJECTS"
    });
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:650,
        top:60,
        width:60,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            if(txdescr.value()=="(nuova persona)" || txdescr.value()==""){
                if(txnome.value()!="" || txcognome.value()!=""){
                    txdescr.value( txnome.value() + " " + txcognome.value() );
                }
            }
            context=txdescr.value();
            var data=qv_mask2object(formid, "C", currsysid);
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
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_PERSONE");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
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
                    qv_maskclear(formid, "C");
                    objclassi.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_PERSONE WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            qv_object2mask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            objclassi.parentid(currsysid,
                                function(){
                                    castFocus(prefix+"NOME");
                                }
                            );
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, bbl_context.replace("{1}", context), currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            filemanager.caption(bbl_context.replace("{1}", context));
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
            bbl_context=RYBOX.babels("BABEL_CONTEXT");
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
    function refreshselection(){
        if(!sospendirefresh){
            setTimeout(
                function(){
                    oper_refresh.engage();
                }
            , 100);
        }
    }
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xfocus:"NOME", xengage:oper_contextengage, files:3} );
}

