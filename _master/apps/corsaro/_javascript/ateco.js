/****************************************************************************
* Name:            ateco.js                                                 *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_helpateco(formid, settings, missing){
    var objgrid;
    var sospendirefresh=false;
    var prophelperid="";
    var proptitle="Ricerca AT.ECO.";
    if(settings.title!=missing){
        proptitle=settings.title;
    };
    var propsezione="";
    if(settings.sezione!=missing){
        propsezione=settings.sezione;
    };
    var propcodice="";
    if(settings.codice!=missing){
        propcodice=settings.codice;
    };
    var dlg=winzDialogGet(formid);
    var hangerid=dlg.hanger;
    var h="";
    var vK=[];
    winzDialogParams(dlg, {
        width:700,
        height:500,
        open:function(){
            castFocus(formid+"helpersearch");
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
    h+=winzAppendCtrl(vK, formid+"helperlbsearch");
    h+=winzAppendCtrl(vK, formid+"helpersearch");
    h+=winzAppendCtrl(vK, formid+"helperlbsezione");
    h+=winzAppendCtrl(vK, formid+"helpersezione");
    h+=winzAppendCtrl(vK, formid+"helperlbcodice");
    h+=winzAppendCtrl(vK, formid+"helpercodice");
    h+=winzAppendCtrl(vK, formid+"helperrefresh");
    h+=winzAppendCtrl(vK, formid+"helperreset");
    h+=winzAppendCtrl(vK, formid+"helpergrid");
    h+=winzAppendCtrl(vK, formid+"__ok");
    h+=winzAppendCtrl(vK, formid+"__cancel");
    $("#"+hangerid).html(h);
    
    prophelperid="";
    var offsety=60;
    $("#"+formid+"helperlbsearch").rylabel({left:20, top:offsety, caption:"Ricerca", formid:formid});
    var objsearch=$("#"+formid+"helpersearch").rytext({
        left:100, top:offsety, width:410, formid:formid,
        assigned:function(){
            objrefresh.engage()
        }
    });
    
    offsety+=30;
    $("#"+formid+"helperlbsezione").rylabel({left:20, top:offsety, maxlen:1, caption:"Sezione", formid:formid});
    var objsezione=$("#"+formid+"helpersezione").rytext({
        left:100, top:offsety, width:50, formid:formid,
        assigned:function(){
            objrefresh.engage()
        }
    });
    $("#"+formid+"helperlbcodice").rylabel({left:240, top:offsety, caption:"Codice", formid:formid});
    var objcodice=$("#"+formid+"helpercodice").rytext({
        left:310, top:offsety, width:200, formid:formid,
        assigned:function(){
            objrefresh.engage()
        }
    });

    var objrefresh=$("#"+formid+"helperrefresh").rylabel({
        left:550,
        top:60,
        width:70,
        caption:"Aggiorna",
        formid:formid,
        button:true,
        click:function(o){
            if(sospendirefresh==false){
                var q="";
                var arg={};
                var t=_likeescapize(objsearch.value());
                var s=objsezione.value().toUpperCase();
                var c=objcodice.value();
                
                if(t!=""){
                    if(q!=""){q+=" AND "}
                    q+="[:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%'";
                    arg["DESCRIPTION"]=t;
                }
                if(s!=""){
                    if(q!=""){q+=" AND "}
                    q+="SEZIONE='[=SEZIONE]'";
                    arg["SEZIONE"]=s;
                }
                if(c!=""){
                    if(q!=""){q+=" AND "}
                    q+="CODICE LIKE '[=CODICE]%'";
                    arg["CODICE"]=c;
                }

                objgrid.where(q);
                objgrid.query({
                    args:arg,
                    ready:function(){
                        if(propsezione!="" && propcodice!=""){
                            objgrid.search(
                                {
                                    "where":"SEZIONE='"+propsezione+"' AND CODICE='"+propcodice+"'"
                                },
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.length>0)
                                            objgrid.index(v[0]);
                                    }
                                    catch(e){}
                                    winzClearMess(formid);
                                    propsezione="";
                                    propcodice="";
                                }
                            );
                        }
                    }
                });
            }
        }
    });
    var objreset=$("#"+formid+"helperreset").rylabel({
        left:550,
        top:90,
        width:70,
        caption:"Pulisci",
        formid:formid,
        button:true,
        click:function(o){
            sospendirefresh=true;
            objsearch.value("");
            objsezione.value("");
            objcodice.value("");
            sospendirefresh=false;
            objrefresh.engage();
        }
    });

    offsety+=30;
    objgrid=$("#"+formid+"helpergrid").ryque({
        left:20,
        top:offsety,
        width:650,
        height:300,
        formid:formid,
        numbered:false,
        checkable:false,
        environ:"ryateco",
        from:"ATECOCODICI",
        orderby:"NAME",
        columns:[
            {id:"SEZIONE", caption:"Sez.", width:60},
            {id:"CODICE", caption:"Codice", width:100},
            {id:"DESCRIPTION", caption:"Descrizione", width:300}
        ],
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
                castFocus(formid+"helpergrid");
            }
        }
    });
    $("#"+formid+"__ok").rylabel({
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
    $("#"+formid+"__cancel").rylabel({
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
            sql:"SELECT * FROM ATECOCODICI WHERE SYSID='[=SYSID]'",
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
}
