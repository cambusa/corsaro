/****************************************************************************
* Name:            qvprezzario.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvprezzario(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currgenreid="";
    var currgenredescr="";
    var typelistini=RYQUE.formatid("0LISTINI0000");
    var typetrasf=RYQUE.formatid("0TRASFERIMEN");
    var typemov=RYQUE.formatid("0FLUSSI00000");
    var typemoney=RYQUE.formatid("0MONEY000000");
    var typeeuro=RYQUE.formatid("0MONEYEURO00");
    var motivoeqid=RYQUE.formatid("0FLUSSMERCE0");
    var currmotivoid=RYQUE.formatid("0MOTTRASFVEN");
    var currlistinoid=RYQUE.formatid("0LISTVENDITE");
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysidC="";
    var sospendirefresh=false;
    
    // DETERMINO IL LISTINO
    if(_isset(settings["listinoid"])){
        currlistinoid=settings["listinoid"];
    }

    // DETERMINO IL MOTIVO
    if(_isset(settings["motivoid"])){
        currmotivoid=settings["motivoid"];
    }

    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    // RICERCA
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:500, 
        assigned:function(){
            setTimeout( function(){ oper_refresh.engage() }, 100)
        }
    });
    
    offsety+=30;
    $(prefix+"lbf_genre").rylabel({left:20, top:offsety, caption:"Articolo*"});
    var txf_genre=$(prefix+"txf_genre").ryhelper({left:100, top:offsety, width:150, 
        formid:formid, table:"QW_ARTICOLI", title:"Articoli", multiple:false,
        open:function(o){
            o.where("");
        },
        onselect:function(o, d){
            currgenredescr=d["DESCRIPTION"];
        },
        assigned:function(){
            currgenreid=txf_genre.value();
            refreshselection();
        },
        clear:function(){
            currgenreid="";
            currgenredescr="";
            refreshselection();
        }
    });
    
    var oper_genre=$(prefix+"oper_genre").rylabel({
        left:260,
        top:offsety,
        caption:"Ricerca...",
        button:true,
        click:function(o){
            cercaarticolo.show({
                open:function(){
                    $("#"+formid+"tabs").hide();
                },
                close:function(){
                    $("#"+formid+"tabs").show();
                },
                assigned:function(genre){
                    txf_genre.value(genre, true);
                }
            });
        }
    });
    var cercaarticolo=new corsaro_browserstuff(formid, "browser_genre");
    
    $(prefix+"lbf_datemin").rylabel({left:370, top:offsety, caption:"Data min"});
    var txf_datemin=$(prefix+"txf_datemin").rydate({left:450, top:offsety,  width:150, 
        assigned:function(){
            refreshselection();
        }
    });
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:650,
        top:offsety-30,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=_likeescapize(txf_search.value());
            var datamin=txf_datemin.text();
            
            oper_new.enabled( currgenreid!="" );
            
            q+="REFMOTIVEID='"+currmotivoid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(currgenreid!="")
                q+=" AND REFGENREID='"+currgenreid+"'";
            if(datamin!="")
                q+=" AND AUXTIME>=[:TIME("+datamin+"000000)]";
            
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
    offsety+=30;

    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_LISTINIJOIN",
        orderby:"DESCRIPTION,AUXTIME",
        columns:[
            {id:"DESCRIPTION", caption:"Articolo", width:300},
            {id:"AUXTIME", caption:"Data", width:100, type:"/"},
            {id:"REFAMOUNT", caption:"Qt Min", width:100, type:"0"},
            {id:"EQAMOUNT", caption:"Prezzo", width:120, type:"2"},
            {id:"EQGENRE", caption:"Divisa", width:120}
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
        selchange:function(o, i){
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
    
    offsety=440;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data={};
            data["LISTINOID"]=currlistinoid;
            data["REFTYPOLOGYID"]=typetrasf;
            data["REFGENREID"]=currgenreid;
            data["REFMOTIVEID"]=currmotivoid;
            data["EQTYPOLOGYID"]=typemov;
            data["EQGENREID"]=typeeuro;
            data["EQMOTIVEID"]=motivoeqid;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"listini_insert",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.SYSID;
                            flagopen=true;
                            objgridsel.splice(0, 0, newid);
                            objtabs.enabled(2,true);
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
        left:590,
        top:offsety,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare gli elementi selezionati?",
                ok:"Elimina",
                confirm:function(){
                    objgridsel.selengage(   // Elenco dei SYSID selezionati
                        function(o, sel){
                            winzProgress(formid);
                            $.post(_cambusaURL+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessionid,
                                    "env":_sessioninfo.environ,
                                    "function":"listini_delete",
                                    "data":{
                                        "LISTINOID":currlistinoid,
                                        "EQUIVALENCES":sel
                                    }
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
                    );
                }
            });
        }
    });
    
    offsety+=40;
    $(prefix+"lb_warning").rylabel({left:20, top:offsety, caption:"* Campi obbligatori per abilitare l'inserimento"});

    // DEFINIZIONE TAB CONTESTO
    offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:500, maxlen:200, datum:"C", tag:"DESCRIPTION"});

    offsety+=30;
    $(prefix+"LB_AUXTIME").rylabel({left:20, top:offsety, caption:"Validità"});
    $(prefix+"AUXTIME").rydate({left:120, top:offsety, datum:"C", tag:"AUXTIME"});

    offsety+=30;
    $(prefix+"LB_REFAMOUNT").rylabel({left:20, top:offsety, caption:"Qt min"});
    $(prefix+"REFAMOUNT").rynumber({left:120, top:offsety, width:200, numdec:0, minvalue:0, datum:"C", tag:"REFAMOUNT"});
    
    offsety+=30;
    $(prefix+"LB_EQAMOUNT").rylabel({left:20, top:offsety, caption:"Importo"});
    var tx_eqamount=$(prefix+"EQAMOUNT").rynumber({left:120, top:offsety, width:200, numdec:2, minvalue:0, datum:"C", tag:"EQAMOUNT"});

    offsety+=30;
    $(prefix+"LB_EQGENREID").rylabel({left:20, top:offsety, caption:"Divisa"});
    $(prefix+"EQGENREID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"EQGENREID", formid:formid, table:"QVGENRES", title:"Divise",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":typemoney});
        },
        select:"ROUNDING",
        onselect:function(o, d){
            tx_eqamount.numdec( parseInt(d["ROUNDING"]) );
        }
    });
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=qv_mask2object(formid, "C", currsysid);
            data["EQUIVALENCEID"]=currsysid;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"listini_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                        }
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

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
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
                    qv_maskclear(formid, "C");
                    RYQUE.query({
                        sql:"SELECT * FROM QW_LISTINIJOIN WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            qv_object2mask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysidC=currsysid;
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
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            refreshselection(
                function(){
                    winzClearMess(formid);
                    txf_search.focus();
                }
            );
        }
    );
    function refreshselection(after){
        if(!sospendirefresh){
            setTimeout(
                function(){
                    oper_refresh.engage(after);
                }
            , 100);
        }
    }
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xfocus:"DESCRIPTION", xengage:oper_contextengage} );
}

