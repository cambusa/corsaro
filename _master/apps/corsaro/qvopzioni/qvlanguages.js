/****************************************************************************
* Name:            qvlanguages.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvlanguages(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    var prefix="#"+formid;
    var currsysid="";
    var languagefrom="italiano";
    var languageto="english";
    var Languagefrom="Italiano";
    var Languageto="English";
    var checkingh=false;
    var RYQUELANG=new ryQue();
    
    if($.isset(settings["languagefrom"])){
        languagefrom=settings["languagefrom"];
        Languagefrom=languagefrom.substr(0,1).toUpperCase()+languagefrom.substr(1);
    }
    if($.isset(settings["languageto"])){
        languageto=settings["languageto"];
        Languageto=languageto.substr(0,1).toUpperCase()+languageto.substr(1);
    }

    // DEFINIZIONE TAB SELEZIONE
    
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:80, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:80, width:400, 
        assigned:function(){
            oper_refresh.engage()
        }
    });

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:600,
        top:80,
        width:100,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="";
            var t=qv_forlikeclause(txf_search.value());

            if(t!="")
                q+="( [:UPPER(CAPTION)] LIKE '%[=CAPTION]%' OR [:UPPER(NAME)] LIKE '%[=NAME]%' )";

            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "CAPTION":t,
                    "NAME":t
                }
            });
        }
    });
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:110,
        width:700,
        maxwidth:-1,
        height:300,
        numbered:false,
        checkable:false,
        environ:languagefrom,
        from:"BABELITEMS",
        orderby:"NAME",
        columns:[
            {id:"DUMMY", caption:"", width:35, formula:"''"},
            {id:"NAME", caption:"Name", width:300},
            {id:"CAPTION", caption:"Caption", width:800}
        ],
        changerow:function(o, i){
            currsysid="";
            enabledata(0);
            oper_save.enabled(0);
            oper_unsaved.visible(0);
            oper_delete.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o, d){
            currsysid=d;
            RYWINZ.Post(_systeminfo.relative.cambusa+"rybabel/babel_action.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "action":"select",
                    "default":languagefrom,
                    "SYSID":currsysid,
                    "languages":[languagefrom, languageto]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            enabledata(1);
                            txname.value(v["NAME"]);
                            txita.value(v[languagefrom]);
                            txeng.value(v[languageto]);
                            oper_save.enabled(1);
                            oper_unsaved.visible(0);
                            oper_delete.enabled(1);
                        }
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        },
        before:function(o, d, r){
            var i;
            for(i=0; i<r; i++){
                $( o.screencell(i, 1) ).css({"background":"transparent", "color":"black"});
            }
            var checkingset="";
            for(i in d){
                if(checkingset!="")
                    checkingset+=",";
                checkingset+="'"+d[i]["NAME"].replace(/'/g, "''")+"'";
            }
            if(checkingh){
                clearTimeout(checkingh);
            }
            if(checkingset!=""){
                checkingh=setTimeout(function(){
                    checkingh=false;
                    RYQUELANG.query({
                        sql:"SELECT NAME,CAPTION FROM BABELITEMS WHERE NAME IN ("+checkingset+")",
                        ready:function(v){
                            try{
                                var index=[];
                                for(i in v){
                                    index[i]=v[i]["NAME"];
                                }
                                for(i in d){
                                    var k=index.indexOf( d[i]["NAME"] );
                                    if(k>=0){
                                        if(v[k]["CAPTION"]==""){
                                            $( o.screencell(i, 1) ).css({"background":"red", "color":"white"});
                                        }
                                    }
                                    else{
                                        $( o.screencell(i, 1) ).css({"background":"red", "color":"white"});
                                    }
                                }
                            }catch(e){}
                        } 
                    });
                }, 300);
            }
        }
    });

    var offsety=400;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        width:100,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var functnew=function(){
                var data = new Object();
                data["DESCRIPTION"]="(nuova opzione)";
                RYWINZ.Post(_systeminfo.relative.cambusa+"rybabel/babel_action.php", 
                    {
                        "sessionid":_sessioninfo.sessionid,
                        "action":"new",
                        "default":languagefrom,
                        "languages":[languagefrom, languageto]
                    }, 
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            if(v.success>0){
                                var newid=v.SYSID;
                                objgridsel.splice(0, 0, newid);
                                setTimeout(function(){
                                    castFocus(formid+"NAME");
                                }, 200);
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
            if(oper_unsaved.visible())
                oper_save.engage(functnew);
            else
                functnew();
        }
    });
    
    var oper_save=$(prefix+"oper_save").rylabel({
        left:180,
        top:offsety,
        width:100,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            RYWINZ.Post(_systeminfo.relative.cambusa+"rybabel/babel_action.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "action":"update",
                    "default":languagefrom,
                    "SYSID":currsysid,
                    "NAME":txname.value(),
                    "languages":[languagefrom, languageto],
                    "captions":[txita.value(), txeng.value()]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            objgridsel.dataload();
                            oper_unsaved.visible(0);
                            if(done)
                                done();
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
    
    var oper_unsaved=$(prefix+"oper_unsaved").rylabel({left:300, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    oper_unsaved.visible(0);
   
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:600,
        top:offsety,
        width:100,
        caption:"Elimina",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare la voce selezionata?",
                confirm:function(){
                    RYWINZ.Post(_systeminfo.relative.cambusa+"rybabel/babel_action.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "action":"delete",
                            "default":languagefrom,
                            "SYSID":currsysid,
                            "languages":[languagefrom, languageto]
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
            });
        }
    });
    
    offsety+=50;
    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Nome"});
    var txname=$(prefix+"NAME").rytext({left:100, top:offsety, width:300,
        changed:function(){
            oper_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    $(prefix+"LB_LANGFROM").rylabel({left:20, top:offsety, caption:Languagefrom});
    var txita=$(prefix+"LANGFROM").rytext({left:100, top:offsety, width:700, maxlen:1000,
        changed:function(){
            oper_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    $(prefix+"LB_LANGTO").rylabel({left:20, top:offsety, caption:Languageto});
    var txeng=$(prefix+"LANGTO").rytext({left:100, top:offsety, width:700, maxlen:1000,
        changed:function(){
            oper_unsaved.visible(1);
        }
    });

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Dizionario", code:"LANG_DICTIONARY"}
        ]
    });
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            RYQUELANG.request({
                environ:languageto,
                ready:function(){
                    objgridsel.where("");
                    objgridsel.query();
                }
            });
        }
    );
    function enabledata(f){
        if(!f){
            txname.clear();
            txita.clear();
            txeng.clear();
        }
        txname.enabled(f);
        txita.enabled(f);
        txeng.enabled(f);
    }
}

