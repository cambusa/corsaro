/****************************************************************************
* Name:            qvarticoli.js                                            *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvarticoli(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currproduttoreid="";
    var currsysid="";
    var currtypologyid=RYQUE.formatid("0ARTICOLI000");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";

    // VARIABILI PREVIEW
    var previewX=0;
    var previewY=0;
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    // RICERCA
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            setTimeout( function(){ oper_refresh.engage() }, 100)
        }
    });
    offsety+=30;

    $(prefix+"lbf_produttore").rylabel({left:20, top:offsety, caption:"Produttore"});
    var txf_produttore=$(prefix+"txf_produttore").ryhelper({left:100, top:offsety, width:150, 
        formid:formid, table:"QW_ATTORI", title:"Produttori", multiple:false,
        open:function(o){
            o.where("AZIENDAID<>'' OR PROPRIETAID<>''");
        },
        assigned: function(){
            currproduttoreid=txf_produttore.value();
            setTimeout( function(){ oper_refresh.engage() }, 100)
        },
        clear:function(){
            currproduttoreid="";
            setTimeout( function(){ oper_refresh.engage() }, 100)
        }
    });

    $(prefix+"lbf_classe").rylabel({left:340, top:offsety, caption:"Classe"});
    var txf_classe=$(prefix+"txf_classe").ryhelper({left:400, top:offsety, width:150, 
        formid:formid, table:"QW_CLASSIARTICOLO", title:"Classi", multiple:false,
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
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            objgridsel.clear();
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            var classeid=txf_classe.value();

            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' OR [:UPPER(CODICE)] LIKE '%[=CODICE]%')";
            }
            if(classeid!=""){
                if(q!=""){q+=" AND "}
                q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+classeid+"')";
            }
            if(currproduttoreid!=""){
                if(q!=""){q+=" AND "}
                q+="PRODUTTOREID='"+currproduttoreid+"'";
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
    var offsetx=20;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_ARTICOLIJOIN",
        orderby:"CLASSE,PRODUTTORE,DESCRIPTION",
        limit:10000,
        columns:[
            {id:"CLASSE", caption:"Classe", width:200},
            {id:"PRODUTTORE", caption:"Produttore", width:120},
            {id:"DESCRIPTION", caption:"Descrizione", width:250},
            {id:"PRODOTTO", caption:"Prodotto", width:150},
            {id:"VARIANTE", caption:"Variante", width:150},
            {id:"CODICE", caption:"Codice", width:150}
        ],
        changerow:function(o,i){
            currsysid="";
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
            context="";
            caricaanteprima(false);
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
            oper_print.enabled(1);
            oper_delete.enabled(1);
            objtabs.enabled(2,true);
            objtabs.enabled(3,true);
            caricaanteprima(true,
                function(){
                    if(flagopen){
                        flagopen=false;
                        objtabs.currtab(2);
                    }
                }
            );
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });

    offsety=440;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuovo articolo)";
            data["TYPOLOGYID"]=currtypologyid;
            if(currproduttoreid!=""){
                data["PRODUTTOREID"]=currproduttoreid;
            }
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"genres_insert",
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
        left:400,
        top:offsety,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_genres.php")
        }
    });
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:590,
        top:offsety,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "genres");
        }
    });

    offsety+=50;
    previewX=20;
    previewY=offsety;
    
    $(prefix+"preview").css({"position":"absolute", "left":20, "top":offsety, "width":700});
    

    // DEFINIZIONE TAB CONTESTO
    offsety=60;
    
    $(prefix+"LB_PRODOTTO").rylabel({left:20, top:offsety, caption:"Prodotto"});
    var tx_prodotto=$(prefix+"PRODOTTO").rytext({left:120, top:offsety, width:300, datum:"C", tag:"PRODOTTO",
        assigned:function(o){
            if(txdescr.value()=="(nuovo articolo)" || txdescr.value()==""){
                txdescr.value(o.value());
            }
        }
    });
    offsety+=30;
    
    $(prefix+"LB_VARIANTE").rylabel({left:20, top:offsety, caption:"Variante"});
    $(prefix+"VARIANTE").rytext({left:120, top:offsety, width:300, datum:"C", tag:"VARIANTE",
        assigned:function(o){
            if(txdescr.value()==tx_prodotto.value()){
                txdescr.value(tx_prodotto.value()+" - "+o.value());
            }
        }
    });
    offsety+=30;
    
    $(prefix+"LB_PROCESSOID").rylabel({left:20, top:offsety, caption:"Processo"});
    $(prefix+"PROCESSOID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"PROCESSOID", formid:formid, table:"QW_PROCESSI", title:"Scelta processo",
        open:function(o){
            o.where("");
        },
        onselect:function(o, d){
            if(txdescr.value()=="(nuovo articolo)" || txdescr.value()==""){
                txdescr.value(d["DESCRIPTION"]);
            }
        }
    });
    offsety+=30;
    
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:600, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;

    $(prefix+"LB_CODICE").rylabel({left:20, top:offsety, caption:"Codice"});
    $(prefix+"CODICE").rytext({left:120, top:offsety, width:300, datum:"C", tag:"CODICE"});
    offsety+=30;

    $(prefix+"LB_BREVITY").rylabel({left:20, top:offsety, caption:"U.M."});
    $(prefix+"BREVITY").rytext({left:120, top:offsety, width:100, datum:"C", tag:"BREVITY"});
    offsety+=30;

    $(prefix+"LB_ROUNDING").rylabel({left:20, top:offsety, caption:"Decimali"});
    $(prefix+"ROUNDING").rynumber({left:120, top:offsety, width:100, numdec:0, minvalue:0, maxvalue:7, datum:"C", tag:"ROUNDING"});
    offsety+=30;
    
    $(prefix+"LB_PRODUTTOREID").rylabel({left:20, top:offsety, caption:"Produttore"});
    $(prefix+"PRODUTTOREID").ryhelper({
        left:120, top:offsety, width:300, datum:"C", tag:"PRODUTTOREID", formid:formid, table:"QW_ATTORI", title:"Produttori",
        open:function(o){
            o.where("AZIENDAID<>'' OR PROPRIETAID<>''");
        }
    });
    offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    offsety+=60;
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});

    var objclassi=$(prefix+"CLASSI").ryselections({"left":470, "top":210, "height":140, 
        "title":"Classi di appartenenza",
        "formid":formid, 
        "table":"QW_CLASSIARTICOLO", 
        "where":"",
        "upward":1,
        "parenttable":"QVGENRES", 
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
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"genres_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            if(done!=missing){done()}
                        }
                        objgridsel.dataload(
                            function(){
                                caricaanteprima(true);
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
    });

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVGENRES", "");

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
                    objclassi.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_ARTICOLI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            objclassi.parentid(currsysid,
                                function(){
                                    castFocus(prefix+"PRODOTTO");
                                }
                            );
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVGENRES", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
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
    function caricaanteprima(flag, after){
        if(flag){
            RYQUE.query({
                sql:"SELECT DESCRIPTION,REGISTRY,PRODUTTORE,PRODOTTO,VARIANTE FROM QW_ARTICOLIJOIN WHERE SYSID='"+currsysid+"'",
                ready:function(v){
                    try{
                        var h="";
                        var s="";
                        h+="<b>"+v[0]["DESCRIPTION"]+"</b><br>";

                        h+="<table style='font-size:10px;'>";
                        if(v[0]["PRODUTTORE"]!=""){
                            h+="<tr><td><b>Produttore:</b>&nbsp;</td><td>"+v[0]["PRODUTTORE"]+"</td></tr>";
                        }
                        if(v[0]["PRODOTTO"]!=""){
                            h+="<tr><td><b>Prodotto:</b>&nbsp;</td><td>"+v[0]["PRODOTTO"]+"</td></tr>";
                        }
                        if(v[0]["VARIANTE"]!=""){
                            h+="<tr><td><b>Variante:</b>&nbsp;</td><td>"+v[0]["VARIANTE"]+"</td></tr>";
                        }
                        h+="</table>";

                        h+="<br>";
                        h+=v[0]["REGISTRY"]+"<br>";
                        $(prefix+"preview").html(h);
                    }
                    catch(e){
                        alert(d);
                    }
                    if(after!=missing){
                        after();
                    }
                }
            });
        }
        else{
            $(prefix+"preview").html("");
            if(after!=missing){
                after();
            }
        }
    }
    this._resize=function(metrics){
        if( metrics.window.width>1420 )
            $(prefix+"preview").css({left:740, top:80});
        else
            $(prefix+"preview").css({left:previewX, top:previewY});
    }
}

