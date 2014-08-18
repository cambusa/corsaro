/****************************************************************************
* Name:            corsaro.js                                               *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
_logoutcall=function(done){
    $.post(_cambusaURL+"ryquiver/quiver.php", 
        {
            "sessionid":_sessionid,
            "env":_sessioninfo.environ,
            "function":"system_logout",
            "data":{
                "SESSIONID":_sessionid
            }
        }, 
        function(d){
            if(done){done()}
        }
    );
}
function corsaro_browserstuff(formid, hanger, missing){
    var prefix="#"+formid;
    var currsysid="";
    var currproduttoreid="";
    var functassigned=null;
    var functopen=null;
    var functclose=null;
    $(prefix+hanger).css({"position":"absolute","left":30,"top":30,"display":"none","visibility":"visible"});
    var h="";
    h+='<div id="'+formid+'stuff_lbf_search"></div><div id="'+formid+'stuff_txf_search"></div>';
    h+='<div id="'+formid+'stuff_lbf_produttore"></div><div id="'+formid+'stuff_txf_produttore"></div>';
    h+='<div id="'+formid+'stuff_lbf_classe"></div><div id="'+formid+'stuff_txf_classe"></div>';
    h+='<div id="'+formid+'stuff_oper_refresh"></div>';
    h+='<div id="'+formid+'stuff_gridarticoli"></div>';
    h+='<div id="'+formid+'stuff_oper_ok"></div>';
    h+='<div id="'+formid+'stuff_oper_cancel"></div>';
    h+='<div id="'+formid+'stuff_preview"></div>';
    $("#"+formid+hanger).html(h);
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=30;
    
    // RICERCA
    $(prefix+"stuff_lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca", formid:formid});
    var txf_search=$(prefix+"stuff_txf_search").rytext({left:100, top:offsety, width:450, formid:formid, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;

    $(prefix+"stuff_lbf_produttore").rylabel({left:20, top:offsety, caption:"Produttore", formid:formid});
    var txf_produttore=$(prefix+"stuff_txf_produttore").ryhelper({left:100, top:offsety, width:150, formid:formid, 
        formid:formid, table:"QW_ATTORI", title:"Produttori", multiple:false,
        open:function(o){
            o.where("AZIENDAID<>'' OR PROPRIETAID<>''");
        },
        assigned: function(){
            currproduttoreid=txf_produttore.value();
            setTimeout( function(){ oper_refresh.engage() }, 100);
        },
        clear:function(){
            currproduttoreid="";
            setTimeout( function(){ oper_refresh.engage() }, 100);
        }
    });

    $(prefix+"stuff_lbf_classe").rylabel({left:340, top:offsety, caption:"Classe", formid:formid});
    var txf_classe=$(prefix+"stuff_txf_classe").ryhelper({left:400, top:offsety, width:150, formid:formid, 
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

    var oper_refresh=$(prefix+"stuff_oper_refresh").rylabel({
        left:650,
        top:30,
        caption:"Aggiorna",
        button:true,
        formid:formid,
        click:function(o){
            objgrid.clear();
            var q="";
            var t=_likeescapize(txf_search.value());
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
                
            objgrid.where(q);
            objgrid.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });
    
    var objgrid=$(prefix+"stuff_gridarticoli").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:true,
        checkable:true,
        formid:formid,
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
            oper_ok.enabled(0);
            caricaanteprima(false);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currsysid=d;
            oper_ok.enabled(1);
            caricaanteprima(true);
        },
        enter:function(){
            oper_ok.engage();
        }
    });

    offsety=390;
    var oper_ok=$(prefix+"stuff_oper_ok").rylabel({
        left:20,
        top:offsety,
        caption:"Conferma",
        button:true,
        formid:formid,
        click:function(o){
            functassigned(currsysid);
            $("#"+formid+hanger).hide();
            functclose();
        }
    });
    var oper_cancel=$(prefix+"stuff_oper_cancel").rylabel({
        left:120,
        top:offsety,
        caption:"Annulla",
        button:true,
        formid:formid,
        click:function(o){
            $("#"+formid+hanger).hide();
            functclose();
        }
    });
    
    $(prefix+"stuff_preview").css({"position":"absolute", "left":730, "top":30, "width":"180mm"});

    this.show=function(params){
        functassigned=params.assigned;
        functopen=params.open;
        functclose=params.close;
        functopen();
        $("#"+formid+hanger).show();
        $("#"+formid+hanger).css({"visibility":"visible"});
        objectFocus(formid+"stuff_txf_search");
        setTimeout(function(){oper_refresh.engage()},100);
    }
    function caricaanteprima(flag){
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
                        $(prefix+"stuff_preview").html(h);
                    }
                    catch(e){
                        alert(d);
                    }
                }
            });
        }
        else{
            $(prefix+"stuff_preview").html("");
        }
    }
    return this;
}
function corsaro_clustersearch(formid, missing){
    var prefix="#"+formid;
    var functconfirm=null;
    var functclose=null;
    var propsigna=[];
    var propgauge=0;
    var propvisible=false;
    var propobj=this;
    var propleft=300;
    var proptop=80;
    
    var dlg=winzDialogGet(formid);
    var hangerid=dlg.hanger;
        
    winzDialogParams(dlg, {
        width:500,
        height:330,
        open:function(){
            castFocus(prefix+"search_amount");
        },
        close:function(){
            if(functclose){functclose()}
        }
    });
    
    var h="";
    h+='<div id="'+formid+'search_lbamount"></div><div id="'+formid+'search_amount" notab="1"></div>';
    h+='<div id="'+formid+'search_lbtolerance"></div><div id="'+formid+'search_tolerance" notab="1"></div>';
    h+='<div id="'+formid+'search_type" notab="1"></div>';
    h+='<div id="'+formid+'search_lbmin"></div><div id="'+formid+'search_lbmax"></div>';
    h+='<div id="'+formid+'search_lbdate"></div><div id="'+formid+'search_datemin" notab="1"></div><div id="'+formid+'search_datemax" notab="1"></div>';
    h+='<div id="'+formid+'search_lbreg"></div><div id="'+formid+'search_regmin" notab="1"></div><div id="'+formid+'search_regmax" notab="1"></div>';
    h+='<div id="'+formid+'search_lbsmart"></div><div id="'+formid+'search_smart" notab="1"></div>';
    h+='<div id="'+formid+'search_lbambito"></div><div id="'+formid+'search_ambito" notab="1"></div>';
    h+='<div id="'+formid+'search_execute" notab="1"></div>';
    $("#"+hangerid).html(h);
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=40;
    
    $(prefix+"search_lbamount").rylabel({left:20, top:offsety, caption:"Totale", formid:formid});
    $(prefix+"search_amount").rynumber({left:100, top:offsety,  width:160, numdec:2, minvalue:-999999999999, formid:formid,
        enter:function(){
            search_execute.engage();
        },
        assigned:function(){
            var signum=propsigna[ search_ambito.key() ];
            propgauge=signum*globalobjs[formid+"search_amount"].value();
        }
    });
    $(prefix+"search_lbtolerance").rylabel({left:20, top:offsety+30, caption:"Tolleranza", formid:formid});
    $(prefix+"search_tolerance").rynumber({left:100, top:offsety+30,  width:160, numdec:2, minvalue:-999999999999, formid:formid,
        enter:function(){
            search_execute.engage();
        }
    });
    
    $(prefix+"search_type").rylist({left:300, top:offsety,  width:160, formid:formid,
        assigned:function(o){
            var en=o.value()!=3;
            globalobjs[formid+"search_amount"].enabled(en);
            globalobjs[formid+"search_tolerance"].enabled(en);
        }
    })
    .additem({caption:"Ricerca semplice", key:"S"})
    .additem({caption:"Algoritmo statistico", key:"M"})
    .additem({caption:"Senza importo", key:"N"});
    
    offsety+=60;
    $(prefix+"search_lbmin").rylabel({left:100, top:offsety, caption:"Minimo", formid:formid});
    $(prefix+"search_lbmax").rylabel({left:300, top:offsety, caption:"Massimo", formid:formid});
    
    offsety+=25;
    $(prefix+"search_lbdate").rylabel({left:20, top:offsety, caption:"Data trasf.", formid:formid});
    $(prefix+"search_datemin").rydate({left:100, top:offsety,  width:160, defaultvalue:"19000101", formid:formid});
    $(prefix+"search_datemax").rydate({left:300, top:offsety,  width:160, defaultvalue:"99991231", formid:formid});
    
    offsety+=30;
    $(prefix+"search_lbreg").rylabel({left:20, top:offsety, caption:"Data reg.", formid:formid});
    $(prefix+"search_regmin").rydate({left:100, top:offsety,  width:160, defaultvalue:"19000101", formid:formid});
    $(prefix+"search_regmax").rydate({left:300, top:offsety,  width:160, defaultvalue:"99991231", formid:formid});
    
    offsety+=40;
    $(prefix+"search_lbsmart").rylabel({left:20, top:offsety, caption:"Testo", formid:formid});
    $(prefix+"search_smart").rytext({left:100, top:offsety,  width:360, formid:formid,
        enter:function(){
            search_execute.engage();
        }
    });
    
    offsety+=30;
    $(prefix+"search_lbambito").rylabel({left:20, top:offsety, caption:"Ambito", formid:formid});
    var search_ambito=$(prefix+"search_ambito").rylist({left:100, top:offsety,  width:360, formid:formid,
        assigned:function(){
            var signum=propsigna[ search_ambito.key() ];
            globalobjs[formid+"search_amount"].value(signum*propgauge);
        }
    });
    
    offsety+=60;
    var search_execute=$(prefix+"search_execute").rylabel({
        left:20,
        top:offsety,
        width:100,
        caption:"Ricerca",
        button:true,
        formid:formid,
        click:function(o){
            winzDialogClose(dlg);
            if(functconfirm){
                var signum=propsigna[ search_ambito.key() ];
                functconfirm({
                    "AMOUNT":signum*globalobjs[formid+"search_amount"].value(),
                    "TOLERANCE":globalobjs[formid+"search_tolerance"].value(),
                    "TYPE":globalobjs[formid+"search_type"].key(),
                    "DATEMIN":globalobjs[formid+"search_datemin"].text(),
                    "DATEMAX":globalobjs[formid+"search_datemax"].text(),
                    "REGMIN":globalobjs[formid+"search_regmin"].text(),
                    "REGMAX":globalobjs[formid+"search_regmax"].text(),
                    "SMART":globalobjs[formid+"search_smart"].value(),
                    "RANGE":search_ambito.key()
                });
            }
        }
    });
    this.prepare=function(params){
        search_ambito.clear();
        propsigna=[];
        for(var i in params){
            search_ambito.additem({caption:params[i].caption, key:i});
            propsigna[i]=params[i].signum;
            if(propsigna[i]==0){
                propsigna[i]=1;
            }
        }
    }
    this.show=function(params, missing){
        if(params.confirm!=missing){functconfirm=params.confirm}
        if(params.close!=missing){functclose=params.close}
        if(params.open!=missing){
            params.open();
        }
        winzDialogOpen(dlg);
    }
    this.settings=function(params, missing){
        if(params.amount!=missing){
            var signum=propsigna[ search_ambito.key() ];
            if(params.amount!=0)
                propgauge=params.amount;
            globalobjs[formid+"search_amount"].value(signum*propgauge);
        }
    }
    return this;
}

function qv_geography(formid, settings, missing){
    var objgrid;
    var prophelperid="";
    var proptitle="Ricerca Citt&agrave;";
    if(settings.title!=missing){
        proptitle=settings.title;
    };
    var proptype="comuni";
    var proptable="";
    var singsql="";
    var likesql="";
    var classwhere="";
    var cols=[];
    var propclasstable="";
    var classdescr="";
    if(settings.type!=missing){
        proptype=settings.type;
    };
    switch(proptype){
    case "comuni":
        proptable="GEO_COMUNI";
        singsql="SELECT * FROM GEO_COMUNI WHERE SYSID='[=SYSID]'"
        likesql="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR CAP LIKE '[=TAG]%' )";
        classwhere="PROVINCIAID='[=CLASSID]'";
        cols=[
                {id:"CAP",caption:"C.A.P.",width:55},
                {id:"DESCRIPTION",caption:"Descrizione",width:190},
                {id:"SIGLA",caption:"Pr.",width:35}
        ];
        propclasstable="SELECT SYSID,DESCRIPTION FROM GEOPROVINCE ORDER BY [:UPPER(DESCRIPTION)]";
        classdescr="Provincia";
        break;
    }
    var proporderby="DESCRIPTION";
    if(settings.orderby!=missing){
        proporderby=settings.orderby;
    };
    var propsubid="";
    if(settings.subid!=missing){
        propsubid=settings.subid
    }
    var actualid=formid+propsubid;
    var dlg=winzDialogGet(formid);
    var hangerid=dlg.hanger;
    var h="";
    var vK=[];
    winzDialogParams(dlg, {
        width:600,
        height:440,
        open:function(){
            castFocus(actualid+"helpersearch");
            // INIZIALIZZAZIONE
            setTimeout(
                function(){
                    objrefresh.engage();
                }, 100
            );
        },
        close:function(){
            objgrid.dispose(
                function(){
                    winzDisposeCtrl(formid, vK);
                    winzDialogFree(dlg);
                }
            );
        }
    });
    // DEFINIZIONE DEL CONTENUTO
    h+="<div class='winz_dialog_title'>";
    h+=proptitle;
    h+="</div>";
    h+=winzAppendCtrl(vK, actualid+"helpergrid");
    h+=winzAppendCtrl(vK, actualid+"helperlbsearch");
    h+=winzAppendCtrl(vK, actualid+"helpersearch");
    if(propclasstable!=""){
        h+=winzAppendCtrl(vK, actualid+"helperlbclass");
        h+=winzAppendCtrl(vK, actualid+"helperclass");
    }
    h+=winzAppendCtrl(vK, actualid+"helperrefresh");
    h+=winzAppendCtrl(vK, actualid+"helperreset");
    h+=winzAppendCtrl(vK, actualid+"__ok");
    h+=winzAppendCtrl(vK, actualid+"__cancel");
    $("#"+hangerid).html(h);
    
    prophelperid="";
    var offsety=80;
    objgrid=$("#"+actualid+"helpergrid").ryque({
        left:20,
        top:offsety,
        width:300,
        height:300,
        formid:formid,
        numbered:false,
        checkable:false,
        environ:"rygeography",
        from:proptable,
        orderby:proporderby,
        columns:cols,
        changerow:function(o,i){
            prophelperid="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            prophelperid=d;
        },
        enter:function(o){
            if(prophelperid!=""){
                selectmanage(o, prophelperid);
            }
        },
        ready:function(o){
            if(o.count()==1){
                o.index(1);
                castFocus(actualid+"helpergrid");
            }
        },
        initialized:function(o){
            if(propclasstable!=""){
                popolatelist(o, propclasstable, objclass);
            }
        }
    });
    $("#"+actualid+"helperlbsearch").rylabel({left:330, top:offsety, caption:"Ricerca", formid:formid});
    offsety+=20;
    var objsearch=$("#"+actualid+"helpersearch").rytext({
        left:330, top:offsety, width:250, formid:formid,
        timerize:function(){
            objrefresh.engage()
        }
    });
    if(propclasstable!=""){
        offsety+=30;
        $("#"+actualid+"helperlbclass").rylabel({left:330, top:offsety, caption:classdescr, formid:formid});
        offsety+=20;
        var objclass=$("#"+actualid+"helperclass").rylist({left:330, top:offsety, width:250, formid:formid, 
            assigned:function(o){
                objrefresh.engage()
            }
        });
        objclass.additem({caption:"", key:""});
    }
    offsety+=40;
    var objrefresh=$("#"+actualid+"helperrefresh").rylabel({
        left:330,
        top:offsety,
        caption:"Aggiorna",
        formid:formid,
        button:true,
        click:function(o){
            var q="";
            var arg={};
            var c="";
            var t=_likeescapize(objsearch.value());
            if(propclasstable!=""){c=objclass.key()}
            if(t!=""){
                q=likesql;
                arg["DESCRIPTION"]=t;
                arg["TAG"]=t;
                
            }
            if(c!=""){
                if(q!=""){q+=" AND "}
                q+=classwhere;
                arg["CLASSID"]=c;
            }
            objgrid.where(q);
            objgrid.query({
                args:arg,
                ready:function(){
                    objsearch.timerizefree();
                }
            });
        }
    });
    var objreset=$("#"+actualid+"helperreset").rylabel({
        left:500,
        top:offsety,
        width:70,
        caption:"Pulisci",
        formid:formid,
        button:true,
        click:function(o){
            objsearch.value("");
            if(propclasstable!=""){
                objclass.value("")
            }
            objrefresh.engage();
        }
    });
    $("#"+actualid+"__ok").rylabel({
        left:20,
        top:dlg.height-40,
        width:80,
        caption:"OK",
        button:true,
        formid:formid,
        click:function(o){
            if(prophelperid!="")
                selectmanage(objgrid, prophelperid);
            else
                winzMessageBox(formid, "Nessun elemento selezionato!");
        }
    });
    $("#"+actualid+"__cancel").rylabel({
        left:120,
        top:dlg.height-40,
        width:80,
        caption:"Annulla",
        button:true,
        formid:formid,
        click:function(o){
            winzDialogClose(dlg);
        }
    });
    // MOSTRO LA DIALOGBOX
    winzDialogOpen(dlg);

    function selectmanage(grid, id){
        grid.extract({
            sql:singsql,
            args:{"SYSID":id},
            ready:function(d){
                try{
                    // ELIMINO I NULL
                    for(var i in d[0]){
                        d[0][i]=_fittingvalue(d[0][i]);
                    }
                    winzDialogClose(dlg);
                    setTimeout(
                        function(){
                            if(settings.onselect!=missing){
                                settings.onselect(d[0]);
                            }
                        }, 100
                    );
                }catch(e){
                    alert(d);
                }
            }
        });
    }
    function popolatelist(grid, sql, list){
        grid.extract({
            "sql":sql,
            "ready":function(v){
                for(var i in v){
                    list.additem({caption:v[i]["DESCRIPTION"], key:v[i]["SYSID"]});
                }
            }
        });
    }
}