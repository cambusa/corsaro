/****************************************************************************
* Name:            qvinclusions.js                                          *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvinclusions(settings,missing){
    var formid=RYWINZ.addform(this);
    var currparentid="";
    var currparentdescr="";
    var currparenttype="";
    var currparenttypedescr="";
    var currparentunit="";
    var currchildid="";
    var currchilddescr="";
    var currchildtype="";
    var currchildtypedescr="";
    var currchildunit="";
    var prefix="#"+formid;
    var requisites=0;
    var cacheext={};
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var offsetx=470;
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:440,
        height:300,
        numbered:true,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QWOBJECTS",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:180},
            {id:"BEGINTIME",caption:"Inizio",width:90,type:"/"},
            {id:"ENDTIME",caption:"Fine",width:90,type:"/"}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                if(currparentid!=""){
                    objtabs.enabled(2,false);
                    objtabs.enabled(3,false);
                }
                currparentid="";
            }
        },
        solveid:function(o,d){
            if(currparentid==""){
                currparentid=d;
                objtabs.enabled(2, true);
                objtabs.enabled(3, false);
            }
            else{
                currparentid=d;
            }
            RYQUE.query({
                sql:"SELECT DESCRIPTION FROM QVOBJECTS WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        currparentdescr=v[0]["DESCRIPTION"];
                        lb_context.caption("Contesto: "+currparentdescr+" ("+currparenttypedescr+")");
                    }catch(e){}
                } 
            });
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    var lbf_search=$(prefix+"lbf_search").rylabel({left:offsetx, top:80, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:offsetx, top:100, width:300, assigned:function(){oper_refresh.engage()}});
    var lbf_typology=$(prefix+"lbf_typology").rylabel({left:offsetx, top:130, caption:"Tipologia"});
    var txf_typology=$(prefix+"txf_typology").rylist({left:offsetx, top:150, width:300,
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
        }
    });
    
    this.selrefresh=function(){
        if(requisites==1){
            oper_refresh.engage();
        }
    }

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:offsetx,
        top:180,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="";
            var t=qv_forlikeclause(txf_search.value());

            currparenttype=txf_typology.key();
            var tag=txf_typology.tag["K"+currparenttype];
            currparentunit=tag["timeunit"];
            currparenttypedescr=tag["description"];

            q="TYPOLOGYID='"+currparenttype+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });
    
    // DEFINIZIONE TAB CONTESTO
    offsetx=470;
    var offsety=50;
    var lb_context=$(prefix+"context").rylabel({left:20, top:offsety, caption:""});offsety+=30;
    var objgridcontext=$(prefix+"gridcontext").ryque({
        left:20,
        top:offsety,
        width:440,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWINCLBROWSER",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:160},
            {id:"INCLUSIONS",caption:"",width:20,type:"?"},
            {id:"BEGINTIME",caption:"Inizio",width:90,type:"/"},
            {id:"ENDTIME",caption:"Fine",width:90,type:"/"}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                objtabs.enabled(3,false);
                oper_insert.enabled(o.isselected());
            }
        },
        selchange:function(o, i){
            oper_insert.enabled(o.isselected());
        },
        solveid:function(o, d){
            currchildid=d;
            objtabs.enabled(3,true);
            oper_insert.enabled(1);
            RYQUE.query({
                sql:"SELECT DESCRIPTION FROM QVOBJECTS WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        currchilddescr=v[0]["DESCRIPTION"];
                        lb_contextdetails.caption("Contesto: "+currparentdescr+" ("+currparenttypedescr+") &#x2283; "+currchilddescr+" ("+currchildtypedescr+")");
                    }catch(e){}
                } 
            });
        }
    });
    var lbfc_search=$(prefix+"lbfc_search").rylabel({left:offsetx, top:offsety, caption:"Ricerca"});offsety+=20;
    var txfc_search=$(prefix+"txfc_search").rytext({left:offsetx, top:offsety, width:300, assigned:function(){operc_refresh.engage()}});offsety+=30;
    var lbfc_typology=$(prefix+"lbfc_typology").rylabel({left:offsetx, top:offsety, caption:"Tipologia"});offsety+=20;
    var txfc_typology=$(prefix+"txfc_typology").rylist({left:offsetx, top:offsety, width:300,
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].xrefresh()", 100);
        }
    });
    offsety+=30;
    
    this.xrefresh=function(){
        operc_refresh.engage();
    }

    var operc_refresh=$(prefix+"operc_refresh").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="";
            var t=qv_forlikeclause(txfc_search.value());
            
            currchildtype=txfc_typology.key();
            var tag=txf_typology.tag["K"+currchildtype];
            currchildunit=tag["timeunit"];
            currchildtypedescr=tag["description"];
            
            var v=(currparentunit=="S" || currchildunit=="S");
            globalobjs[formid+"BEGINTIME"].visible(v);
            globalobjs[formid+"ENDTIME"].visible(v);
            
            q="TYPOLOGYID='"+currchildtype+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

            objgridcontext.where(q);
            objgridcontext.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });
    offsety+=40;
    
    $(prefix+"LB_BEGINTIME").rylabel({left:offsetx, top:offsety, caption:"Inizio"});offsety+=20;
    var tx_bt=$(prefix+"BEGINDATE").rydate({left:offsetx, top:offsety, datum:"C", tag:"BEGINTIME"});
    tx_bt.link(
        $(prefix+"BEGINTIME").rytime({left:offsetx+130, top:offsety})
    );
    offsety+=30;
    
    $(prefix+"LB_ENDTIME").rylabel({left:offsetx, top:offsety, caption:"Fine"});offsety+=20;
    var tx_et=$(prefix+"ENDDATE").rydate({left:offsetx, top:offsety, defaultvalue:"99991231", datum:"C", tag:"ENDTIME"});
    tx_et.link(
        $(prefix+"ENDTIME").rytime({left:offsetx+130, top:offsety})
    );
    offsety+=50;

    var oper_insert=$(prefix+"oper_insert").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Includi selezione",
        button:true,
        click:function(o){
            objgridcontext.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    winzProgress(formid);
                    s=s.split("|");
                    var begin=tx_bt.text();
                    var end=tx_et.text();
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di inserimento
                        stats[i]={
                            "function":"inclusions_insert",
                            "data":{
                                "PARENTID":currparentid,
                                "OBJECTID":s[i],
                                "BEGINTIME":begin,
                                "ENDTIME":end
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
                                winzTimeoutMess(formid, v.success, v.message);
                            }
                            catch(e){
                                winzClearMess(formid);
                                alert(d);
                            }
                            objgridcontext.dataload();
                        }
                    );
                }
            );
        }
    });
    
    // DEFINIZIONE TAB DETTAGLIO
    offsety=50;
    var lb_contextdetails=$(prefix+"contextdetails").rylabel({left:20, top:offsety, caption:""});offsety+=30;
    var griddetails=$(prefix+"griddetails").ryque({
        left:20,
        top:offsety,
        width:440,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWINCLUSIONS",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:180},
            {id:"BEGINTIME",caption:"Inizio",width:90,type:"/"},
            {id:"ENDTIME",caption:"Fine",width:90,type:"/"}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                RYWINZ.MaskClear(formid, "X");
                enabledetails(o.isselected());
            }
        },
        selchange:function(o, i){
            enabledetails(o.isselected());
        },
        solveid:function(o, d){
            RYQUE.query({
                sql:"SELECT * FROM QVINCLUSIONS WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        RYWINZ.ToMask(formid, "X", v[0])
                        enabledetails(1);
                    }catch(e){}
                } 
            });
        }
    });

    $(prefix+"LBD_BEGINTIME").rylabel({left:offsetx, top:offsety, caption:"Inizio"});offsety+=20;
    var txd_bt=$(prefix+"DBEGINDATE").rydate({left:offsetx, top:offsety, datum:"X", tag:"BEGINTIME"});
    txd_bt.link(
        $(prefix+"DBEGINTIME").rytime({left:offsetx+130, top:offsety})
    );
    offsety+=30;
    
    $(prefix+"LBD_ENDTIME").rylabel({left:offsetx, top:offsety, caption:"Fine"});offsety+=20;
    var txd_et=$(prefix+"DENDDATE").rydate({left:offsetx, top:offsety, defaultvalue:"99991231", datum:"X", tag:"ENDTIME"});
    txd_et.link(
        $(prefix+"DENDTIME").rytime({left:offsetx+130, top:offsety})
    );
    offsety+=30;

    var oper_update=$(prefix+"oper_update").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Salva selezione",
        button:true,
        click:function(o){
            griddetails.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    winzProgress(formid);
                    s=s.split("|");
                    var begin=txd_bt.text();
                    var end=txd_et.text();
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di inserimento
                        stats[i]={
                            "function":"inclusions_update",
                            "data":{
                                "SYSID":s[i],
                                "BEGINTIME":begin,
                                "ENDTIME":end
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
                                griddetails.dataload();
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
    offsety+=160;

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Escludi selezione",
        button:true,
        click:function(o){
            griddetails.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    winzProgress(formid);
                    s=s.split("|");
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di inserimento
                        stats[i]={
                            "function":"inclusions_delete",
                            "data":{
                                "SYSID":s[i]
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
                                griddetails.refresh();
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

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Inclusioni"},
            {title:"Manutenzione"}
        ],
        select:function(i,p){
            switch(i){
            case 2:
                // CARICAMENTO DEL CONTESTO
                operc_refresh.engage();
                break;
            case 3:
                // CARICAMENTO DEL DETTAGLIO
                var v=(currparentunit=="S" || currchildunit=="S");
                globalobjs[formid+"DBEGINTIME"].visible(v);
                globalobjs[formid+"DENDTIME"].visible(v);
                griddetails.where("PARENTID='"+currparentid+"' AND OBJECTID='"+currchildid+"'");
                griddetails.query();
            }
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            RYQUE.query({
                sql:"SELECT SYSID,DESCRIPTION,GENRETYPEID,TIMEUNIT FROM QVOBJECTTYPES ORDER BY DESCRIPTION",
                ready:function(v){
                    var t={};
                    for(var i in v){
                        txf_typology.additem({caption:v[i]["DESCRIPTION"], key:v[i]["SYSID"]});
                        txfc_typology.additem({caption:v[i]["DESCRIPTION"], key:v[i]["SYSID"]});
                        t["K"+v[i]["SYSID"]]={
                            "description":v[i]["DESCRIPTION"],
                            "genretypeid":v[i]["GENRETYPEID"],
                            "timeunit":v[i]["TIMEUNIT"]
                        };
                    }
                    txf_typology.tag=t;
                    requisites+=1;
                    setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
                }
            });
        }
    );
    function enabledetails(v){
        globalobjs[formid+"DBEGINDATE"].enabled(v);
        globalobjs[formid+"DBEGINTIME"].enabled(v);
        globalobjs[formid+"DENDDATE"].enabled(v);
        globalobjs[formid+"DENDTIME"].enabled(v);
        oper_update.enabled(v);
        oper_delete.enabled(v);
    }
}

