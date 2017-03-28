/****************************************************************************
* Name:            qvplutosimula.js                                         *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvplutosimula(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currconfigid="";
    var currengageid="";
    var context="";
    var prefix="#"+formid;
    var flagsuspend=false;
    var loadedsysidC="";
    var cachefields={};
    var cacheoptions={};
    var cacheparams={};
    var loadedengageid="";
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_FINCONFIG",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Configurazione",width:200}
        ],
        changerow:function(o,i){
            currconfigid="";
            objtabs.enabled(2,false);
            context="";
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currconfigid=d;
            objtabs.enabled(2, true);
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });

    var offsety=80;
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});
    offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    
    offsety+=30;
    $(prefix+"lbf_processo").rylabel({left:430, top:offsety, caption:"Processo"});
    offsety+=20;
    var txf_processo=$(prefix+"txf_processo").ryhelper({left:430, top:offsety, width:300, formid:formid, table:"QW_PROCESSI", title:"Processi", multiple:false,
        open:function(o){
            o.where("");
            o.orderby("DESCRIPTION");
        },
        onselect:function(o, d){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });

    offsety+=30;
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_search.value());
            var procid=txf_processo.value();
            
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            if(procid!=""){
                if(q!=""){q+=" AND "}
                q+="PROCESSOID='"+procid+"'";
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
    
    // DEFINIZIONE TAB CONTESTO
    var lb_arrow_context=$(prefix+"arrow_context").rylabel({left:20, top:50, caption:""});

    $(prefix+"arrowhandle").css({position:"absolute", left:300, top:80});
    $(prefix+"scripthandle").css({position:"absolute", left:20, top:80});

    offsety=0;
    var gridscript=$(prefix+"gridscript").ryque({
        left:0,
        top:offsety,
        width:230,
        height:160,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_FINSCRIPT",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Sviluppo",width:200}
        ],
        changerow:function(o,i){
            currengageid="";
            loadedengageid="";
            opera_engage.enabled(0);
            opera_create.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currengageid=d;
            opera_engage.enabled(1);
        }
    });
    
    offsety=155;
    var opera_refresh=$(prefix+"opera_refresh").rylabel({
        left:0,
        top:offsety,
        width:100,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            if(gridscript.reqid()!=""){
                gridscript.where("SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currconfigid+"')");
                gridscript.query();
            }
            else{
                setTimeout(
                    function(){
                        opera_refresh.engage();
                    }, 300
                );
           }
        }
    });
    var opera_engage=$(prefix+"opera_engage").rylabel({
        left:116,
        top:offsety,
        width:100,
        caption:"Esegui",
        button:true,
        click:function(o){
            eseguiscript();
        }
    });
    
    offsety+=50;
    var opera_create=$(prefix+"opera_create").rylabel({
        left:0,
        top:offsety,
        width:216,
        caption:"Genera finanziamento",
        button:true,
        click:function(o){
            RYQUIVER.RequestID(formid, {
                table:"QW_ATTORI", 
                where:"",
                orderby:"DESCRIPTION",
                title:"Inserire il richiedente:",
                multiple:false,
                mandatory:false,
                onselect:function(d){
                    var richid=d["SYSID"];
                    generafin(richid);
                }
            });
        }
    });
    opera_create.enabled(0);
    
    var plutodialogo=new pluto_dialogopzioni();

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione", csize:800},
            {title:"Simulazione", csize:1000}
        ],
        select:function(i,p){
            if(i==1){
                loadedsysidC="";
            }
            else if(i==2){
                if(currconfigid==loadedsysidC){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 2:
                    // CARICAMENTO DEI PARAMETRI
                    lb_arrow_context.caption("Contesto: "+context);
                    plutodialogo.close();
                    opera_create.enabled(0);
                    qv_contextmanagement(context, {sysid:currconfigid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_arrow_context.caption("Contesto: "+context);
                            loadedsysidC=currconfigid;
                            $(prefix+"arrowhandle").html("<i>(nessuno sviluppo in corso)</i>");
                            opera_refresh.engage();
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
    
    function eseguiscript(){
        if(currengageid!=loadedengageid){
            // REPERISCO LA DEFINIZIONE DELLE OPZIONI
            RYQUE.query({
                sql:"SELECT OPZIONI FROM OBJECTS_FINSCRIPT WHERE SYSID='"+currengageid+"'",
                ready:function(v){
                    try{
                        var params=v[0]["OPZIONI"];
                        if(params=="")
                            params="[]";
                        params=params.replace(/\\"/g, "\"");
                        try{
                            params=eval("("+params+")");
                        }catch(e){
                            if(window.console){console.log(params)}
                            params=[];
                        }
                        cacheparams=params;
                        loadedengageid=currengageid;
                        mostradialogo(true);
                    }
                    catch(e){
                        alert(e.message);
                    }
                }
            });
        }
        else{
            mostradialogo(false);
        }
    }
    function mostradialogo(r){
        plutodialogo.show({
            open:function(){
                if(r)
                    plutodialogo.settings(cacheparams);
            },
            confirm:function(opt){
                winzProgress(formid);
                cacheoptions=opt;
                RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessioninfo.sessionid,
                        "env":_sessioninfo.environ,
                        "function":"pluto_execute",
                        "data":{
                            "PLUTOID":currconfigid,
                            "SCRIPTID":currengageid,
                            "OPZIONI":opt
                        }
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success>0){
                                var svil=v.params["SVILUPPO"];
                                svil=svil.replace(/&lt;([^<>&]+)&gt;/ig, "<$1>");
                                svil=svil.replace(/&amp;/ig, "&");
                                $(prefix+"arrowhandle").html(svil);
                                opera_create.enabled(1);
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
    }
    function generafin(richid){
        opera_create.enabled(0);
        winzProgress(formid);
        RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
            {
                "sessionid":_sessioninfo.sessionid,
                "env":_sessioninfo.environ,
                "function":"pluto_execute",
                "data":{
                    "PLUTOID":currconfigid,
                    "RICHIEDENTEID":richid,
                    "SCRIPTID":currengageid,
                    "OPZIONI":cacheoptions,
                    "EFFETTIVO":1
                }
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){
                        var svil=v.params["SVILUPPO"];
                        svil=svil.replace(/&lt;([^<>&]+)&gt;/ig, "<$1>");
                        svil=svil.replace(/&amp;/ig, "&");
                        $(prefix+"arrowhandle").html(svil);
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
    function pluto_dialogopzioni(){
        var functconfirm=null;
        var dlg=winzDialogGet(formid);
        var hangerid=dlg.hanger;
        var propobj=this;
        
        winzDialogParams(dlg, {
            open:function(){
                castFocus(prefix+"pluto_execute");
            },
            close:function(){
                castFocus(prefix+"opera_engage");
            }
        });
        
        this.show=function(params, missing){
            if(params.confirm!=missing){functconfirm=params.confirm}
            if(params.open!=missing){
                params.open();
            }
            winzDialogOpen(dlg);
        }
        this.settings=function(options){
            // DISTRUGGO LA PRECEDENTE STRUTTURA
            for(var n in cachefields){
                delete globalobjs[n];
                delete _globalforms[formid].controls[n];
            }
            cachefields={};
            $("#"+hangerid).html("");
            // CREO LA NUOVA STRUTTURA
            var l=$.objectsize(options);
            var height=l*30+120;
            winzDialogParams(dlg, {width:500, height:height});
            var subh=40;
            var optname=hangerid;
            if(l>0){
                for(var f in options){
                    var id="";
                    var tp="";
                    var cap="";
                    var dft=false;
                    if($.isset(options[f]["id"]))
                        id=options[f]["id"];
                    if($.isset(options[f]["type"]))
                        tp=""+options[f]["type"];
                    if($.isset(options[f]["caption"]))
                        cap=options[f]["caption"];
                    if($.isset(options[f]["default"]))
                        dft=options[f]["default"];
                    var lb=optname+"_lb"+"_"+id;
                    var tx=optname+"_tx"+"_"+id;
                    var o;
                    $("#"+optname).append("<div id='"+lb+"'></div>");
                    $("#"+optname).append("<div id='"+tx+"' notab='1'></div>");
                    $("#"+lb).rylabel({left:20, top:subh, caption:cap, formid:formid});
                    switch(tp){
                    case "0":case "1":case "2":case "3":case "4":case "5":case "6":case "7":
                        o=$("#"+tx).rynumber({left:120, top:subh, width:150, numdec:parseInt(tp), formid:formid});
                        o.tag=id;
                        if(dft!==false)
                            o.value(dft);
                        break;
                    case "/":
                        o=$("#"+tx).rydate({left:120, top:subh, width:150, formid:formid});
                        o.tag=id;
                        if(dft!==false)
                            o.value(dft);
                        else
                            o.value(new Date());
                        break;
                    case "?":
                        o=$("#"+tx).rycheck({left:120, top:subh, formid:formid});
                        o.tag=id;
                        if(dft!==false)
                            o.value(dft);
                        break;
                    case "_":
                        o=$("#"+tx).rylist({left:120, top:subh, width:150, formid:formid})
                        .additem({caption:"Mese", key:"1M"})
                        .additem({caption:"Bimestre", key:"2M"})
                        .additem({caption:"Trimestre", key:"3M"})
                        .additem({caption:"Quadrimestre", key:"4M"})
                        .additem({caption:"Semestre", key:"6M"})
                        .additem({caption:"Anno", key:"1Y"});
                        o.tag=id;
                        if(dft!==false)
                            o.setkey(dft);
                        break;
                    default:
                        o=$("#"+tx).rytext({left:120, top:subh, width:350, formid:formid});
                        o.tag=id;
                        if(dft!==false)
                            o.value(dft);
                    }
                    cachefields[lb]=0;
                    cachefields[tx]=0;
                    subh+=30;
                }
            }
            else{
                $("#"+optname).append("<div><br><br>&nbsp;&nbsp;&nbsp;<i>(non sono previsti parametri)</i></div>");
            }
            $("#"+optname).append("<div id='"+formid+"pluto_execute' notab='1'></div>");
            
            var pluto_execute=$(prefix+"pluto_execute").rylabel({
                left:20,
                top:height-40,
                width:120,
                caption:"Simulazione",
                button:true,
                formid:formid,
                click:function(o){
                    if(functconfirm!=missing){
                        var optname=hangerid;
                        var opt={};
                        var id="";
                        for(var f in cachefields){
                            var o=globalobjs[f];
                            switch(o.type){
                            case "date":
                                opt[o.tag]=o.text();
                                break;
                            case "list":
                                opt[o.tag]=o.key();
                                break;
                            case "label":
                                break;
                            default:
                                opt[o.tag]=o.value();
                            }
                        }
                        functconfirm(opt);
                    }
                    winzDialogClose(dlg);
                }
            });
        }
        this.close=function(){
            winzDialogClose(dlg);
        }
        return this;
    }
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
}
