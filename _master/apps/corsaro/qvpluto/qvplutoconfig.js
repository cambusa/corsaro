/****************************************************************************
* Name:            qvplutoconfig.js                                         *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvplutoconfig(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var typepluto=RYQUE.formatid("0FINCONFIG00");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var flagfocus=false;
    var loadedsysidC="";
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_FINCONFIG",
        orderby:"DESCRIPTION,SYSID",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:200}
        ],
        changerow:function(o,i){
            currsysid="";
            objtabs.enabled(2,false);
            oper_delete.enabled(o.isselected());
            context="";
            if(i>0){
                o.solveid(i);
            }
        },
        changesel:function(o){
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currsysid=d;
            objtabs.enabled(2,true);
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
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});
    offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
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
            var t=qv_forlikeclause(txf_search.value());
            
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
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
        top:210,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data={};
            data["DESCRIPTION"]="(nuova configurazione)";
            data["TYPOLOGYID"]=typepluto;
            data["SEGNO"]=1;
            data["DIVIDENDO"]=365;
            data["DIVISORE"]=365;
            data["PARAMETRI"]="{}";
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:340,
        caption:"Elimina riga selezionata",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare la configurazione selezionata?",
                ok:"Elimina",
                confirm:function(){
                    winzProgress(formid);
                    var stats=[];
                    var istr=0;
                    stats[istr++]={
                        "function":"selections_remove",
                        "data":{
                            "PARENTID":currsysid
                        }
                    };
                    stats[istr++]={
                        "function":"objects_delete",
                        "data":{
                            "SYSID":currsysid
                        }
                    };
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "program":stats
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    objgridsel.refresh();
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
        }
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;

    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:100, top:offsety, width:350, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    
    offsety+=30;
    $(prefix+"LB_PROCESSOID").rylabel({left:20, top:offsety, caption:"Processo"});
    $(prefix+"PROCESSOID").ryhelper({
        left:100, top:offsety, width:350, datum:"C", tag:"PROCESSOID", formid:formid, table:"QW_PROCESSI", title:"Scelta processo",
        open:function(o){
            o.where("");
        }
    });
    
    offsety+=30;
    $(prefix+"LB_CONTOID").rylabel({left:20, top:offsety, caption:"Conto fin."});
    $(prefix+"CONTOID").ryhelper({
        left:100, top:offsety, width:350, datum:"C", tag:"CONTOID", formid:formid, table:"QW_CONTI", title:"Scelta conto finanziamento",
        open:function(o){
            o.where("");
        }
    });
    
    offsety+=30;
    $(prefix+"LB_CONTROID").rylabel({left:20, top:offsety, caption:"Conto C\\C"});
    $(prefix+"CONTROID").ryhelper({
        left:100, top:offsety, width:350, datum:"C", tag:"CONTROID", formid:formid, table:"QW_CONTI", title:"Scelta conto controparte",
        open:function(o){
            o.where("");
        }
    });
    
    offsety+=30;
    $(prefix+"LB_SEGNO").rylabel({left:20, top:offsety, caption:"Segno"});
    $(prefix+"SEGNO").rylist({left:100, top:offsety, width:350, datum:"C", tag:"SEGNO"})
        .additem({caption:"Attivo (il conto eroga o paga variabile)", key:1})
        .additem({caption:"Passivo (il conto beneficia o paga fisso)", key:-1});

    offsety+=30;
    $(prefix+"LB_DIVIDENDO").rylabel({left:20, top:offsety, caption:"Dividendo"});
    $(prefix+"DIVIDENDO").rylist({left:100, top:offsety, width:100, datum:"C", tag:"DIVIDENDO"})
        .additem({caption:"365", key:365})
        .additem({caption:"360", key:360});

    $(prefix+"LB_DIVISORE").rylabel({left:280, top:offsety, caption:"Divisore"});
    $(prefix+"DIVISORE").rylist({left:350, top:offsety, width:100, datum:"C", tag:"DIVISORE"})
        .additem({caption:"365", key:365})
        .additem({caption:"360", key:360});

    offsety+=30;
    $(prefix+"LB_PARAMETRI").rylabel({left:20, top:offsety, caption:"Parametri"});
    offsety+=25;
    $(prefix+"PARAMETRI").ryedit({left:20, top:offsety, width:700, height:200, flat:true, datum:"C", tag:"PARAMETRI"});
    
    offsety+=240;
    var objscript=$(prefix+"DEVELOPER").ryselections({"left":20, "top":offsety, "width":500, "height":300, 
        "title":"Script",
        "formid":formid, 
        "subid":"C",
        "table":"QW_FINSCRIPT",
        "where":"",
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
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedsysidC="";
                    }
                });
            }
            if(i==1){
                loadedsysidC="";
            }
            else if(i==2){
                if(currsysid==loadedsysidC){
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
                    // RESET MASCHERA
                    RYWINZ.MaskClear(formid, "C");
                    objscript.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_FINCONFIG WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysidC=currsysid;
                            objscript.parentid(currsysid,
                                function(){
                                    castFocus(prefix+"DESCRIPTION");
                                }
                            );
                        }
                    });
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2, false);
    
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

