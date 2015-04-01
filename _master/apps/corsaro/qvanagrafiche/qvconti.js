/****************************************************************************
* Name:            qvconti.js                                               *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvconti(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid=RYQUE.formatid("0CONTI000000");
    var currmoneyid=RYQUE.formatid("0MONEY000000");
    var curreuroid=RYQUE.formatid("0MONEYEURO00");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
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

    $(prefix+"lbf_classe").rylabel({left:430, top:offsety, caption:"Classe"});
    offsety+=20;
    var txf_classe=$(prefix+"txf_classe").ryhelper({left:430, top:offsety, width:300, 
        formid:formid, table:"QW_CLASSICONTO", title:"Classi", multiple:false,
        open:function(o){
            o.where("");
        },
        onselect:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        },
        clear:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=_likeescapize(txf_search.value());
            var classeid=txf_classe.value();

            q="TYPOLOGYID='"+currtypologyid+"'";
            if(t!=""){
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
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
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:430,
        top:240,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuovo conto)";
            data["TYPOLOGYID"]=currtypologyid;
            data["REFGENREID"]=curreuroid;
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
                            /*
                            objgridsel.query({
                                where:"SYSID='"+newid+"'",
                                ready:function(){
                                    flagopen=true;
                                    objgridsel.index(1);
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
    
    $(prefix+"LB_NUMCONTO").rylabel({left:20, top:offsety, caption:"Numero Conto"});
    $(prefix+"NUMCONTO").rytext({left:120, top:offsety, width:300, datum:"C", tag:"NUMCONTO"});
    offsety+=30;
    
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"CO.GE."});
    $(prefix+"REFERENCE").rytext({left:120, top:offsety, width:300, datum:"C", tag:"REFERENCE"});
    offsety+=30;
    
    $(prefix+"LB_REFOBJECTID").rylabel({left:20, top:offsety, caption:"Conto Padre"});
    $(prefix+"REFOBJECTID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFOBJECTID", formid:formid, table:"QW_CONTI", title:"Conti",
        open:function(o){
            o.where("SYSID<>'"+currsysid+"'");
        }
    });offsety+=30;
    
    $(prefix+"LB_TITOLAREID").rylabel({left:20, top:offsety, caption:"Titolare"});
    $(prefix+"TITOLAREID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"TITOLAREID", formid:formid, table:"QW_ATTORI", title:"Attori",
        open:function(o){
            o.where("");
        }
    });offsety+=30;
    
    $(prefix+"LB_BEGINTIME").rylabel({left:20, top:offsety, caption:"Inizio Rapp."});
    $(prefix+"BEGINTIME").rydate({left:120, top:offsety, datum:"C", tag:"BEGINTIME"});
    $(prefix+"LB_ENDTIME").rylabel({left:260, top:offsety, caption:"Fine"});
    $(prefix+"ENDTIME").rydate({left:300, top:offsety, defaultvalue:"99991231", datum:"C", tag:"ENDTIME"});
    offsety+=30;
    
    $(prefix+"LB_REFGENREID").rylabel({left:20, top:offsety, caption:"Divisa"});
    $(prefix+"REFGENREID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"REFGENREID", formid:formid, table:"QVGENRES", title:"Divise",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currmoneyid});
        }
    });offsety+=30;
    
    $(prefix+"LB_BANCAID").rylabel({left:20, top:offsety, caption:"Banca"});
    var tx_banca=$(prefix+"BANCAID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"BANCAID", formid:formid, table:"QW_AZIENDE", title:"Banche",
        open:function(o){
            o.where("BANCA=1");
        },
        assigned:function(o){
            if(tx_banca.value()!=""){
                $(prefix+"FRAME_BANCA").show();
                $(prefix+"FRAME_VUOTO").hide();
            }
            else{
                $(prefix+"FRAME_BANCA").hide();
                $(prefix+"FRAME_VUOTO").show();
            }
        }
    });offsety+=30;

    $(prefix+"LB_CIN").rylabel({left:20, top:offsety, caption:"CIN"});
    $(prefix+"CIN").rytext({left:120, top:offsety, width:70, maxlen:1, datum:"C", tag:"CIN"});

    $(prefix+"LB_EUROCIN").rylabel({left:270, top:offsety, caption:"EUROCIN"});
    $(prefix+"EUROCIN").rytext({left:350, top:offsety, width:70, maxlen:2, datum:"C", tag:"EUROCIN"});
    offsety+=30;

    $(prefix+"LB_BIC").rylabel({left:20, top:offsety, caption:"BIC"});
    $(prefix+"BIC").rytext({left:120, top:offsety, width:300, maxlen:20, datum:"C", tag:"BIC"});
    offsety+=30;

    $(prefix+"LB_BBAN").rylabel({left:20, top:offsety, caption:"BBAN"});
    $(prefix+"BBAN").rytext({left:120, top:offsety, width:300, maxlen:50, datum:"C", tag:"BBAN"});
    offsety+=30;

    $(prefix+"LB_IBAN").rylabel({left:20, top:offsety, caption:"IBAN"});
    $(prefix+"IBAN").rytext({left:120, top:offsety, width:300, maxlen:50, datum:"C", tag:"IBAN"});
    offsety+=30;

    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    offsety+=30;
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var objclassi=$(prefix+"CLASSI").ryselections({"left":470, "top":110, "height":140, 
        "title":"Classi di appartenenza",
        "formid":formid, 
        "table":"QW_CLASSICONTO", 
        "where":"",
        "upward":1,
        "parenttable":"QVOBJECTS", 
        "parentfield":"SYSID",
        "selectedtable":"QVOBJECTS"
    });
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            if(tx_banca.value()==""){
                if(_isset(data["BANCAID"])){
                    data["CIN"]="";
                    data["EUROCIN"]="";
                    data["BIC"]="";
                    data["IBAN"]="";
                    data["BBAN"]="";
                }
            }
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
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_CONTI");

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
                    RYWINZ.MaskClear(formid, "C");
                    objclassi.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_CONTI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            if(tx_banca.value()!=""){
                                $(prefix+"FRAME_BANCA").show();
                                $(prefix+"FRAME_VUOTO").hide();
                            }
                            else{
                                $(prefix+"FRAME_BANCA").hide();
                                $(prefix+"FRAME_VUOTO").show();
                            }
                            objclassi.parentid(currsysid,
                                function(){
                                    castFocus(prefix+"DESCRIPTION");
                                }
                            );
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

