/****************************************************************************
* Name:            qvinventario.js                                          *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvinventario(settings,missing){
    var formid=RYWINZ.addform(this, settings);
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
            {id:"DESCRIPTION",caption:"Magazzino",width:200}
        ],
        changerow:function(o,i){
            currsysid="";
            loadedsysid="";
            loadedsysid2="";
            objtabs.enabled(2,false);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currsysid=d;
            objtabs.enabled(2,true);
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
            var t=qv_forlikeclause(txf_search.value());

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
                var t=qv_forlikeclause(txm_search.value());
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
        checkable:false,
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
        changerow:function(o,i){
            txgiacenza.value(0);
            txdispo.value(0);
            txamount.value(0);
            oper_inventario.enabled(0);
            operi_refresh.enabled(0);
            currcollid="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currcollid=d;
            oper_inventario.enabled(1);
            operi_refresh.enabled(1);
            calcologiacenza();
        }
    });
    offsety=490;
    
    $(prefix+"LB_VALTIME").rylabel({left:20, top:offsety, caption:"Data/Ora"});
    var txtime=$(prefix+"VALDATE").rydate({left:120, top:offsety});
    txtime.link(
        $(prefix+"VALTIME").rytime({left:250, top:offsety})
    );
    var operi_refresh=$(prefix+"operi_refresh").rylabel({
        left:380,
        top:offsety,
        width:100,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            calcologiacenza();
        }
    });
    offsety+=30;
    
    $(prefix+"LB_GIACENZA").rylabel({left:20, top:offsety, caption:"Giacenza"});
    var txgiacenza=$(prefix+"GIACENZA").rynumber({left:120, top:offsety, width:120, numdec:2});
    offsety+=30;
    
    $(prefix+"LB_DISPO").rylabel({left:20, top:offsety, caption:"Disponibilit&agrave;"});
    var txdispo=$(prefix+"DISPO").rynumber({left:120, top:offsety, width:120, numdec:2});
    offsety+=40;
    
    txgiacenza.enabled(0);
    txdispo.enabled(0);

    $(prefix+"LB_AMOUNT").rylabel({left:20, top:offsety, caption:"Quantit&agrave"});
    var txamount=$(prefix+"AMOUNT").rynumber({left:120, top:offsety, width:120, numdec:2, minvalue:0});
    
    var oper_inventario=$(prefix+"oper_inventario").rylabel({
        left:380,
        top:offsety,
        width:100,
        caption:"Registra",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data={};
            data["COLLID"]=currcollid;
            data["MOMENTO"]=txtime.text();
            data["AMOUNT"]=txamount.value();
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"stuff_inventario",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            calcologiacenza();
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
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Collocazioni"}
        ],
        select:function(i,p){
            if(i==1){
                loadedsysid="";
                loadedsysid2="";
            }
            else if(i==2){
                if(currsysid==loadedsysid){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 2:
                    // CARICAMENTO COLLOCAZIONI
                    lb_collocazioni_context.caption("Contesto: "+context);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_collocazioni_context.caption("Contesto: "+context);
                            loadedsysid2=currsysid;
                            txtime.value(Date.stringNow());
                            setTimeout(function(){operm_refresh.engage()}, 100);
                        }
                    });
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    
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
    function calcologiacenza(){
        var momento=tempolasco( txtime.text() );
        $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"stuff_balance",
                "data":{
                    "SYSID":currcollid,
                    "EVENTS":momento
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var balance=v["params"]["BALANCE"];
                        txgiacenza.value(balance[currcollid]["GIACENZA"][0]);
                        txdispo.value(balance[currcollid]["DISPO"][0]);
                    }
                }
                catch(e){
                    alert(d);
                }
            }
        );
    }
    function tempolasco(t){
        var h=t.substr(0,10);
        var m=t.substr(10,2).actualInteger()+4;
        m=("00"+m).subright(2);
        return h+m+"59";
    }
}

