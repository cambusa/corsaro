/****************************************************************************
* Name:            qvequivalences.js                                        *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvequivalences(settings,missing){
    var formid=RYWINZ.addform(this);
    var referenceid="";
    var refdescr="";
    var refarrowtype="";
    var reftypedescr="";
    var refgenretype="";
    var refmotivetype="";
    
    var equidescr="";
    var equiarrowtype="";
    var equigenretype="";
    var equimotivetype="";

    var refbowtypeid="";
    var reftargettypeid="";
    var refparentid="";
    var refobjectid="";

    var prefix="#"+formid;
    
    // DEFINIZIONE TAB SELEZIONE
    var offsetx=480;
    var offsety=80;
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:450,
        height:400,
        numbered:true,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QWARROWS",
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
                if(referenceid!=""){
                    objtabs.enabled(2,false);
                }
                referenceid="";
            }
        },
        solveid:function(o,d){
            if(referenceid==""){
                referenceid=d;
                objtabs.enabled(2,true);
            }
            else{
                referenceid=d;
            }
            RYQUE.query({
                sql:"SELECT DESCRIPTION FROM QVARROWS WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        refdescr=v[0]["DESCRIPTION"];
                        lb_context.caption("Contesto: "+refdescr+" ("+reftypedescr+")");
                    }catch(e){}
                } 
            });
        },
        before:function(o,d){
            if(refobjectid!=""){
                for(var i in d){
                    if(d[i]["BOWID"]==refobjectid){
                        d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                    }
                }
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    $(prefix+"lbf_search").rylabel({left:offsetx, top:offsety, caption:"Ricerca"});offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:offsetx, top:offsety, width:300, 
        assigned:function(){
            oper_refresh.engage()
        }
    });offsety+=30;
    $(prefix+"lbf_typology").rylabel({left:offsetx, top:offsety, caption:"Tipologia"});offsety+=20;
    var txf_typology=$(prefix+"txf_typology").rylist({left:offsetx, top:offsety, width:300,
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
        }
    });offsety+=30;
    
    $(prefix+"lbf_genre").rylabel({left:offsetx, top:offsety, caption:"Genere"});offsety+=20;
    var txf_genre=$(prefix+"txf_genre").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVGENRES", title:"Generi", multiple:false,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":refgenretype});
        },
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
        }
    });offsety+=30;
    
    $(prefix+"lbf_motives").rylabel({left:offsetx, top:offsety, caption:"Motivi"});offsety+=20;
    var txf_motives=$(prefix+"txf_motives").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVMOTIVES", title:"Motivi", multiple:true,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":refmotivetype});
        },
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
        }
    });offsety+=30;
    
    $(prefix+"lbf_parent").rylabel({left:offsetx, top:offsety, caption:"Genitore"});
    $(prefix+"lbf_object").rylabel({left:offsetx+155, top:offsety, caption:"Oggetto"});offsety+=20;
    var txf_parent=$(prefix+"txf_parent").ryhelper({left:offsetx, top:offsety, width:145, 
        formid:formid, table:"QWINCLPARENTS", title:"Oggetti", multiple:false,
        open:function(o){
            o.where("( TYPOLOGYID='[=BOWTYPEID]' OR TYPOLOGYID='[=TARGETTYPEID]' )");
            o.args({"BOWTYPEID":refbowtypeid, "TARGETTYPEID":reftargettypeid});
        },
        assigned: function(o){
            refparentid=o.value();
            txf_object.clear();
        }
    });
    var txf_object=$(prefix+"txf_object").ryhelper({left:offsetx+155, top:offsety, width:145, 
        formid:formid, table:"", title:"Oggetti", multiple:false,
        open:function(o){
            if(refparentid!=""){
                o.table("QWINCLCHILDREN");
                o.where("( TYPOLOGYID='[=BOWTYPEID]' OR TYPOLOGYID='[=TARGETTYPEID]' ) AND PARENTID='"+refparentid+"'");
                o.args({"BOWTYPEID":refbowtypeid, "TARGETTYPEID":reftargettypeid});
            }
            else{
                o.table("QWOBJECTS");
                o.where("( TYPOLOGYID='[=BOWTYPEID]' OR TYPOLOGYID='[=TARGETTYPEID]' )");
                o.args({"BOWTYPEID":refbowtypeid, "TARGETTYPEID":reftargettypeid});
            }
        },
        assigned: function(o){
            refobjectid=o.value();
            setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_date").rylabel({left:offsetx, top:offsety, caption:"Data massima"});
    $(prefix+"lbf_amount").rylabel({left:offsetx+155, top:offsety, caption:"Quantità"+" &plusmn;5%"});
    offsety+=20;
    var txf_date=$(prefix+"txf_date").rydate({left:offsetx, top:offsety, width:145, 
        assigned:function(){
            setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
        }
    });
    var txf_amount=$(prefix+"txf_amount").rynumber({left:offsetx+155, top:offsety, width:145, numdec:0, 
        assigned:function(){
            setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
        }
    });
    offsety+=30;
    
    
    this.selrefresh=function(){
        oper_refresh.engage();
    }

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:offsetx,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            objgridsel.clear()
            if(refparentid!="" && refobjectid==""){
                return false;
            }
            var q="";
            var t=qv_forlikeclause(txf_search.value());

            refarrowtype=txf_typology.key();
            var tag=txf_typology.tag["K"+refarrowtype];
            reftypedescr=tag["typedescr"];
            refgenretype=tag["genretypeid"];
            refbowtypeid=tag["bowtypeid"];
            reftargettypeid=tag["targettypeid"];
            refmotivetype=tag["motivetypeid"];
            var genreid=txf_genre.value();
            var motiveid=txf_motives.value();
            var objectid=txf_object.value();
            var dataval=txf_date.text();
            var amount=txf_amount.value();

            q="TYPOLOGYID='"+refarrowtype+"'";
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
    offsety+=45;

    // DEFINIZIONE TAB CONTESTO
    var lb_context=$(prefix+"context").rylabel({left:20, top:50, caption:""});
    offsetx=480;
    offsety=110;
    var gridcontext=$(prefix+"gridcontext").ryque({
        left:20,
        top:offsety,
        width:450,
        height:400,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QWEQUIBROWSER",
        limit:10000,
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:140},
            {id:"EQUIVALENCE",caption:"",width:20,type:"?"},
            {id:"AUXTIME",caption:"Data",width:90,type:"/"},
            {id:"AMOUNT",caption:"Quantità",width:110,type:"2"}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                oper_insert.enabled(o.isselected());
                oper_delete.enabled(o.isselected());
            }
        },
        selchange:function(o, i){
            oper_insert.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_insert.enabled(1);
            oper_delete.enabled(1);
        }
    });
    $(prefix+"lbfc_search").rylabel({left:offsetx, top:offsety, caption:"Ricerca"});offsety+=20;
    var txfc_search=$(prefix+"txfc_search").rytext({left:offsetx, top:offsety, width:300, 
        assigned:function(){
            operc_refresh.engage()
        }
    });offsety+=30;
    $(prefix+"lbfc_typology").rylabel({left:offsetx, top:offsety, caption:"Tipologia"});offsety+=20;
    var txfc_typology=$(prefix+"txfc_typology").rylist({left:offsetx, top:offsety, width:300,
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].xrefresh()", 100);
        }
    });offsety+=30;
    
    $(prefix+"lbfc_genre").rylabel({left:offsetx, top:offsety, caption:"Genere"});offsety+=20;
    var txfc_genre=$(prefix+"txfc_genre").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVGENRES", title:"Generi", multiple:false,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":equigenretype});
        },
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].xrefresh()", 100);
        }
    });offsety+=30;
    
    $(prefix+"lbfc_motives").rylabel({left:offsetx, top:offsety, caption:"Motivi"});offsety+=20;
    var txfc_motives=$(prefix+"txfc_motives").ryhelper({left:offsetx, top:offsety, width:300, 
        formid:formid, table:"QVMOTIVES", title:"Motivi", multiple:true,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":equimotivetype});
        },
        assigned: function(){
            setTimeout("_globalforms['"+formid+"'].xrefresh()", 100);
        }
    });offsety+=30;
    
    $(prefix+"lbfc_data").rylabel({left:offsetx, top:offsety, caption:"Data massima"});offsety+=20;
    var txfc_data=$(prefix+"txfc_data").rydate({left:offsetx, top:offsety, 
        assigned:function(){
            setTimeout("_globalforms['"+formid+"'].xrefresh()", 100);
        }
    });offsety+=30;

    $(prefix+"lbfc_yesno").rylabel({left:offsetx, top:offsety, caption:"Seleziona..."});offsety+=20;
    var txfc_yesno=$(prefix+"txfc_yesno").rylist({left:offsetx, top:offsety, width:200,
        assigned:function(){
            setTimeout("_globalforms['"+formid+"'].xrefresh()", 100);
        }
    })
    .additem({caption:"Tutti", key:0})
    .additem({caption:"Liberi", key:1})
    .additem({caption:"Equivalenti", key:2});offsety+=30;
    
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

            equiarrowtype=txfc_typology.key();
            var tag=txf_typology.tag["K"+equiarrowtype];
            equigenretype=tag["genretypeid"];
            equimotivetype=tag["motivetypeid"];
            var genreid=txfc_genre.value();
            oper_insert.enabled( genreid!="" );
            var motiveid=txfc_motives.value();
            var dataval=txfc_data.text();
            var yesno=parseInt(txfc_yesno.key());

            q="SYSID<>'"+referenceid+"' AND TYPOLOGYID='"+equiarrowtype+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(genreid!="")
                q+=" AND GENREID='"+genreid+"'";
            if(motiveid!="")
                q+=" AND MOTIVEID IN ('"+motiveid.replace("|", "','")+"')";
            if(dataval!="")
                q+=" AND AUXTIME<=[:TIME("+dataval+"235959)]";
            if(yesno==1)
                q+=" AND EQUIVALENCE=0";
            else if(yesno==2)
                q+=" AND REFERENCEID='"+referenceid+"'";

            gridcontext.where(q);
            gridcontext.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });
    
    var oper_insert=$(prefix+"oper_insert").rylabel({
        left:20,
        top:80,
        caption:"Includi selezione",
        button:true,
        click:function(o){
            winzProgress(formid);
            gridcontext.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    s=s.split("|");
                    var stats=[];
                    for(var i in s){    // Carico le istruzioni di inserimento
                        stats[i]={
                            "function":"equivalences_add",
                            "data":{
                                "REFERENCEID":referenceid,
                                "EQUIVALENTID":s[i]
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
                                gridcontext.dataload();
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
    })

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:200,
        top:80,
        caption:"Escludi selezione",
        button:true,
        click:function(o){
            winzProgress(formid);
            gridcontext.selengage(   // Elenco dei SYSID selezionati
                function(o,s){
                    // Reperisco l'elenco delle equivalenze referenceid <---> equivalentid selezionati
                    var list="'"+s.replace("|", "','")+"'";
                    RYQUE.query({
                        sql:"SELECT SYSID FROM QVEQUIVALENCES WHERE REFERENCEID='"+referenceid+"' AND EQUIVALENTID IN ("+list+")",
                        ready:function(v){
                            var stats=[];
                            for(var i in v){
                                stats[i]={
                                    "function":"equivalences_remove",
                                    "data":{
                                        "SYSID":v[i]["SYSID"]
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
                                        gridcontext.dataload();
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
            );
        }
    });
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"},
            {title:"Equivalenze"}
        ],
        select:function(i,p){
            switch(i){
            case 2:
                // CARICAMENTO DEL CONTESTO
                setTimeout("_globalforms['"+formid+"'].xrefresh()", 100);
                break;
            }
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            RYQUE.query({
                sql:"SELECT SYSID,DESCRIPTION,GENRETYPEID,MOTIVETYPEID,BOWTYPEID,TARGETTYPEID FROM QVARROWTYPES ORDER BY DESCRIPTION",
                ready:function(v){
                    var t={};
                    for(var i in v){
                        txf_typology.additem({caption:v[i]["DESCRIPTION"], key:v[i]["SYSID"]});
                        txfc_typology.additem({caption:v[i]["DESCRIPTION"], key:v[i]["SYSID"]});
                        t["K"+v[i]["SYSID"]]={
                            "typedescr":v[i]["DESCRIPTION"],
                            "genretypeid":v[i]["GENRETYPEID"],
                            "motivetypeid":v[i]["MOTIVETYPEID"],
                            "bowtypeid":v[i]["BOWTYPEID"],
                            "targettypeid":v[i]["TARGETTYPEID"]
                        };
                    }
                    txf_typology.tag=t;
                    setTimeout("_globalforms['"+formid+"'].selrefresh()", 100);
                }
            });
        }
    );
}

