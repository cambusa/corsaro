/****************************************************************************
* Name:            corsi.js                                                 *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_helpcourses(formid, settings, missing){
    var objgrid;
    var sospendirefresh=false;
    var prophelperid="";
    var proptitle="Ricerca corsi";
    if(settings.title!=missing){
        proptitle=settings.title;
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
    h+=winzAppendCtrl(vK, formid+"helperlbtipo");
    h+=winzAppendCtrl(vK, formid+"helpertipo");
    h+=winzAppendCtrl(vK, formid+"helperlbluogo");
    h+=winzAppendCtrl(vK, formid+"helperluogo");
    h+=winzAppendCtrl(vK, formid+"helperlbinizio");
    h+=winzAppendCtrl(vK, formid+"helperinizio");
    h+=winzAppendCtrl(vK, formid+"helperlbfine");
    h+=winzAppendCtrl(vK, formid+"helperfine");
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
    $("#"+formid+"helperlbtipo").rylabel({left:20, top:offsety, caption:"Tipo", formid:formid});
    var objtipo=$("#"+formid+"helpertipo").rytext({
        left:100, top:offsety, width:150, formid:formid,
        assigned:function(){
            objrefresh.engage()
        }
    });
    $("#"+formid+"helperlbluogo").rylabel({left:290, top:offsety, caption:"Luogo", formid:formid});
    var objluogo=$("#"+formid+"helperluogo").rytext({
        left:360, top:offsety, width:150, formid:formid,
        assigned:function(){
            objrefresh.engage()
        }
    });

    offsety+=30;
    $("#"+formid+"helperlbinizio").rylabel({left:20, top:offsety, caption:"Data min"});
    var objdatemin=$("#"+formid+"helperinizio").rydate({left:100, top:offsety,  width:150, 
        assigned:function(){
            objrefresh.engage()
        }
    });
    $("#"+formid+"helperlbfine").rylabel({left:290, top:offsety, caption:"Data max"});
    var objdatemax=$("#"+formid+"helperfine").rydate({left:360, top:offsety,  width:150, 
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
                var t=qv_forlikeclause(objsearch.value());
                var p=qv_forlikeclause(objtipo.value());
                var l=qv_forlikeclause(objluogo.value());
                var datamin=objdatemin.text();
                var datamax=objdatemax.text();
                if(datamin=="")
                    datamin=datamax;
                if(datamax=="")
                    datamax=datamin;
                
                q="CONSISTENCY=0";
                if(t!=""){
                    if(q!=""){q+=" AND "}
                    q+="[:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%'";
                    arg["DESCRIPTION"]=t;
                }
                if(p!=""){
                    if(q!=""){q+=" AND "}
                    q+="[:UPPER(TIPOCORSO)] LIKE '%[=TIPOCORSO]%'";
                    arg["TIPOCORSO"]=p;
                }
                if(l!=""){
                    if(q!=""){q+=" AND "}
                    q+="[:UPPER(LUOGO)] LIKE '%[=LUOGO]%'";
                    arg["LUOGO"]=l;
                }
                if(datamin!=""){
                    if(q!=""){q+=" AND "}
                    q+="ENDTIME>=[:DATE("+datamin+")] AND BEGINTIME<=[:DATE("+datamax+")]";
                }

                objgrid.where(q);
                objgrid.query({
                    args:arg
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
            objtipo.value("");
            objluogo.value("");
            objdatemin.value("");
            objdatemax.value("");
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
        environ:_sessioninfo.environ,
        from:"QW_CORSI",
        orderby:"BEGINTIME DESC,DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:320},
            {id:"BEGINTIME", caption:"Inizio", width:100, type:"/"},
            {id:"LUOGO", caption:"Luogo", width:180}
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
            sql:"SELECT * FROM QW_CORSIJOIN WHERE SYSID='[=SYSID]'",
            args:{"SYSID":id},
            ready:function(d){
                try{
                    // ELIMINO I NULL
                    for(var i in d[0]){
                        d[0][i]=__(d[0][i]);
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
