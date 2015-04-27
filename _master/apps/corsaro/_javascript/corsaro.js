/****************************************************************************
* Name:            corsaro.js                                               *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
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
        RYBOX.setfocus(formid+"stuff_txf_search");
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
        changed:function(o){
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

function qv_importODS(formid, settings, missing){
    var matrice=[];
    var proptitle="Importazione ODS";
    if(settings.title!=missing){
        proptitle=settings.title;
    };
    var dlg=winzDialogGet(formid);
    var hangerid=dlg.hanger;
    var h="";
    var vK=[];
    winzDialogParams(dlg, {
        width:800,
        height:440,
        open:function(){
            castFocus(formid+"ods_upload");
        },
        close:function(){
            winzDisposeCtrl(formid, vK);
            winzDialogFree(dlg);
        }
    });
    // DEFINIZIONE DEL CONTENUTO
    h+="<div class='winz_dialog_title'>";
    h+=proptitle;
    h+="</div>";
    h+=winzAppendCtrl(vK, formid+"ods_upload");
    h+="<div id='"+formid+"ods_grid' style='position:absolute;left:20px;top:130px;width:750px;height:250px;border:1px solid gray;overflow:scroll;'></div>";
    h+=winzAppendCtrl(vK, formid+"__ok");
    h+=winzAppendCtrl(vK, formid+"__cancel");
    $("#"+hangerid).html(h);

    $("#"+formid+"ods_upload").ryupload({
        left:20,
        top:70,
        width:300,
        formid:formid,
        environ:_tempenviron,
        complete:function(id, name, ret){
            var u=ret["url"];
            var p=u.indexOf("customize/");
            if(p>=0){
                var path=_customizeURL+u.substr(p+10);
                $("#"+formid+"ods_grid").html("");
                matrice=[];
                $.post(_cambusaURL+"rygeneral/ods2array.php", 
                    {
                        "ods":path
                    }, 
                    function(d){
                        try{
                            var s="", h="", v=$.parseJSON(d);
                            matrice=v;
                            
                            s+="<select style='width:100%;min-width:80px;'><option>---</option>";
                            if(settings.columns!=missing){
                                for(var c in settings.columns){
                                    s+="<option value='"+settings.columns[c]["id"]+"'>"+settings.columns[c]["caption"]+"</option>";
                                }
                            }
                            s+="</select>";
                            
                            if(v.length>0){
                                h+="<table>";
                                
                                var m=0;
                                for(var r=0; r<v.length; r++){
                                    if(m<v[r].length)
                                        m=v[r].length;
                                }
                                
                                h+="<tr>";
                                h+="<td style='padding:1px 5px;border:1px solid silver;'><input id='"+formid+"ods_checkall' type='checkbox' checked></td>";
                                for(var c=0; c<m; c++){
                                    h+="<td style='padding:1px 5px;border:1px solid silver;white-space:nowrap;'>";
                                    h+=s;
                                    h+="</td>";
                                }
                                h+="</tr>";

                                for(var r=0; r<v.length; r++){
                                    h+="<tr>";
                                    h+="<td style='padding:1px 5px;border-right:1px dashed silver;'><input type='checkbox' checked></td>";
                                    for(var c=0; c<v[r].length; c++){
                                        h+="<td style='padding:1px 5px;border-right:1px dashed silver;white-space:nowrap;'>";
                                        if(v[r][c].length<=20)
                                            h+=v[r][c];
                                        else
                                            h+=v[r][c].substr(0,20)+"...";
                                        h+="</td>";
                                    }
                                    h+="</tr>";
                                }
                                
                                h+="</table>";
                                
                                $("#"+formid+"ods_grid").html(h);
                                
                                // COMPORTAMENTO DEL CHECK ALL
                                $("#"+formid+"ods_checkall").click(
                                    function(){
                                        $("#"+formid+"ods_checkall").attr("disabled","1");
                                        var f=$(this).is(':checked');
                                        $("#"+formid+"ods_grid tr td>input").each(
                                            function(i){
                                                if(i>0){
                                                    if(f)
                                                        $(this).attr("checked","1");
                                                    else
                                                        $(this).removeAttr("checked");
                                                }
                                            }
                                        );
                                        $("#"+formid+"ods_checkall").removeAttr("disabled");
                                    }
                                );
                            }
                        }
                        catch(e){
                            alert(d);
                        }
                    }
                );
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
            setTimeout(
                function(){
                    if(settings.ready!=missing && settings.columns!=missing){
                        var d=[];
                        var mapc=[];
                        var full=false;
                        var copia=matrice.slice(0);
                        $("#"+formid+"ods_grid tr:first-child td>select").each(
                            function(i){
                                mapc[i]=$(this).val();
                            }
                        );
                        // CONSERVO SOLO I SELEZIONATI
                        var t=0, s=[], value;
                        $("#"+formid+"ods_grid tr td>input").each(
                            function(i){
                                if(i>0){
                                    if(!$(this).is(':checked')){
                                        s[t++]=i-1;
                                    }
                                }
                            }
                        );
                        for(var i=s.length-1; i>=0; i--){
                            copia.splice(s[i], 1);
                        }
                        for(var r=0; r<copia.length; r++){
                            d[r]={};
                            for(var c in mapc){
                                if(mapc[c]!="---"){
                                    if(typeof d[r][mapc[c]]!=="undefined")
                                        value=d[r][mapc[c]];
                                    else
                                        value="";
                                    switch(mapc[c]){
                                    case "REGISTRY":
                                        if(value!=""){value+="<br/>"}
                                        value+=__(copia[r][c]);
                                        break;    
                                    case "TAG":
                                        if(value!=""){value+=", "}
                                        value+=__(copia[r][c]);
                                        break;
                                    default:
                                        value=__(copia[r][c]);
                                        if(mapc[c].substr(mapc[c].length-4)=="TIME"){
                                            var m=value.match(/\d+/g);
                                            if(m.length==3){
                                                value=("0000"+m[2]).subright(4)+("00"+m[1]).subright(2)+("00"+m[0]).subright(2);
                                            }
                                        }
                                    }
                                    d[r][mapc[c]]=value;
                                    full=true;
                                }
                            }
                        }
                        if(full){
                            winzDialogClose(dlg);
                            settings.ready(d);
                        }
                    }
                }, 100
            );
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
}

$(document).ready(function(){
    RYBOX.babels({
        "BABEL_CONTEXT":"Contesto: {1}",
    });
});
