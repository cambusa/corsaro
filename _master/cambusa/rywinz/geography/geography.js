/****************************************************************************
* Name:            geography.js                                             *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function winzGeography(formid, settings, missing){
    var objgrid;
    var prophelperid="";
    var proptitle="Ricerca Città";
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
ryWinz.prototype.Geography=winzGeography;