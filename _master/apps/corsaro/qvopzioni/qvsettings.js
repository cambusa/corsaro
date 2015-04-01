/****************************************************************************
* Name:            qvsettings.js                                            *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvsettings(settings,missing){
    var formid=RYWINZ.addform(this);
    var currsysid="";
    var prefix="#"+formid;

    var listbackup=[];
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:360,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QVSETTINGS",
        orderby:"TAG",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:200,code:"DESCRIPTION"}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                currsysid="";
                RYWINZ.MaskClear(formid, "C");
                enabledata(0);
                oper_delete.enabled(o.isselected());
            }
        },
        selchange:function(o, i){
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currsysid=d;
            RYQUE.query({
                sql:"SELECT * FROM QVSETTINGS WHERE SYSID='"+d+"'",
                ready:function(v){
                    try{
                        RYWINZ.ToMask(formid, "C", v[0])
                        enabledata(1);
                        oper_delete.enabled(1);
                    }catch(e){}
                } 
            });
        }
    });
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:80, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:100, width:300, assigned:function(){oper_refresh.engage()}});

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:130,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="";
            var t=_likeescapize(txf_search.value());

            if(t!="")
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                }
            });
        }
    });
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:430,
        top:160,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuova opzione)";
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"settings_insert",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.SYSID;
                            flagopen=true;
                            objgridsel.splice(0, 0, newid);
                            /*
                            objgridsel.query({
                                where:"SYSID='"+newid+"'",
                                ready:function(){
                                    objgridsel.index(1);
                                }
                            });
                            */
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
    
    var offsety=200;
    $(prefix+"LB_NAME").rylabel({left:430, top:offsety, caption:"Nome"});
    $(prefix+"NAME").rytext({left:510, top:offsety, width:220, datum:"C", tag:"NAME"});offsety+=30;
    $(prefix+"LB_DESCRIPTION").rylabel({left:430, top:offsety, caption:"Descrizione"});
    $(prefix+"DESCRIPTION").rytext({left:510, top:offsety, width:220, maxlen:200, datum:"C", tag:"DESCRIPTION"});offsety+=30;
    $(prefix+"LB_DATAVALUE").rylabel({left:430, top:offsety, caption:"Valore"});
    $(prefix+"DATAVALUE").rytext({left:510, top:offsety, width:220, datum:"C", tag:"DATAVALUE"});offsety+=30;
    $(prefix+"LB_DATATYPE").rylabel({left:430, top:offsety, caption:"Tipo"});
    $(prefix+"DATATYPE").rylist({left:510, top:offsety, width:220, datum:"C", tag:"DATATYPE"})
        .additem({caption:"", key:""})
        .additem({caption:"Testo", key:"STRING"})
        .additem({caption:"Intero", key:"INTEGER"})
        .additem({caption:"Razionale", key:"RATIONAL"})
        .additem({caption:"Booleano", key:"BOOLEAN"})
        .additem({caption:"Data/Ora", key:"TIMESTAMP"});
    offsety+=30;
    $(prefix+"LB_TAG").rylabel({left:430, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:510, top:offsety, width:220, datum:"C", tag:"TAG"});offsety+=30;

    var oper_engage=$(prefix+"oper_engage").rylabel({
        left:430,
        top:350,
        caption:"Salva",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"settings_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ RYWINZ.modified(formid, 0) }
                        objgridsel.dataload();
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
    
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:405,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "settings");
        }
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=80;
    adminengage("LB_REFRESHVIEWS", 20, offsety, 250, "refresh_views", "Aggiornamento di tutte le viste", {});offsety+=40;
    importego();offsety+=50;
    $(prefix+"LB_ELIMINAZIONE").rylabel({left:20, top:offsety, caption:"<b>Eliminazione definitiva record cancellati:</b>"});offsety+=30;
    adminengage("LB_EMPTYGENRES", 20, offsety, 90, "entities_empty", "Generi", {"TABLENAME":"QVGENRES"});
    adminengage("LB_EMPTYOBJECTS", 130, offsety, 90, "entities_empty", "Oggetti", {"TABLENAME":"QVOBJECTS"});
    adminengage("LB_EMPTYMOTIVES", 240, offsety, 90, "entities_empty", "Motivi", {"TABLENAME":"QVMOTIVES"});
    adminengage("LB_EMPTYARROWS", 350, offsety, 90, "entities_empty", "Frecce", {"TABLENAME":"QVARROWS"});
    adminengage("LB_EMPTYQUIVERS", 460, offsety, 90, "entities_empty", "Quiver", {"TABLENAME":"QVQUIVERS"});offsety+=30;
    adminengage("LB_EMPTYFILES", 20, offsety, 90, "files_empty", "Documenti", {});
    adminengage("LB_EMPTYMESSAGES", 130, offsety, 90, "messages_empty", "Messaggi", {});
    
    offsety+=50;
    adminengage("LB_EMPTYHISTORY", 20, offsety, 200, "history_empty", "Elimina vecchio storico", {});offsety+=40;
    adminengage("LB_EMPTYTEMPORARY", 20, offsety, 200, "managetemp", "Pulizia directory temporanea", {});offsety+=40;
    adminengage("LB_EMPTYSELECTIONS", 20, offsety, 200, "selections_empty", "Pulizia selezionati orfani", {});offsety+=40;
    adminengage("LB_EMPTYLOG", 20, offsety, 200, "managelog", "Eliminazione journal", {});
    
    // DEFINIZIONE TAB IMPORTAZIONI
    var oper_import=$(prefix+"oper_import").ryupload({
        left:100,
        top:120,
        width:300,
        environ:_tempenviron,
        complete:function(id, name, ret){
            winzProgress(formid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":[
                        {
                            "function":"entities_import",
                            "data":{
                                "PATHFILE":name,
                            }
                        }
                    ]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            winzMessageBox(formid, "Il file "+_utf8("e")+" stato processato");
                        }
                        winzTimeoutMess(formid, parseInt(v.success), v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });
    
    // DEFINIZIONE TAB BACKUP
    var oper_backup=$(prefix+"oper_backup").rylabel({
        left:20,
        top:120,
        caption:"Backup",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eseguire il backup di dati e documenti?",
                confirm:function(){
                    winzProgress(formid);
                    var counter=0;
                    var jqxhr=$.ajax({
                        xhr: function(){
                            var xhr=null;
                            if(window.XMLHttpRequest){
                                xhr=new window.XMLHttpRequest();
                                //Download progress
                                xhr.addEventListener("progress", function(evt){
                                    if(counter>0){
                                        var perc=Math.round(evt.loaded/counter);
                                        if(perc>100){perc=100}
                                        $("#message_"+formid).html(perc+"%");
                                    }
                                    else{
                                        counter=_getinteger(xhr.responseText.substr(0,18));
                                    }
                                }, false);
                            } 
                            else{ 
                                try{  
                                    xhr=new ActiveXObject("MSXML2.XMLHTTP");
                                    //Download progress
                                    xhr.attachEvent("progress", function(evt) {
                                        try{
                                            if(counter>0){
                                                var perc=Math.round(evt.loaded/counter);
                                                if(perc>100){perc=100}
                                                $("#message_"+formid).html(perc+"%");
                                            }
                                            else{
                                                counter=_getinteger(xhr.responseText.substr(0,18));
                                            }
                                        } 
                                        catch(e){} 
                                    });
                                } 
                                catch(e){} 
                            }                        
                            return xhr;
                        },
                        type:"POST",
                        url:_cambusaURL+"ryquiver/quiver.php",
                        data:{
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"system_backup",
                            "data":{}
                        },
                        success: function(d){
                            winzClearMess(formid);
                            try{
                                var d=d.substr(d.indexOf("Y")+1);
                                var v=$.parseJSON(d);
                                if(v.success>0)
                                    winzMessageBox(formid, "Creato il file di backup:<br>"+v.params["BACKUP"]);
                                else
                                    winzMessageBox(formid, "Backup fallito:<br>"+v.message);
                            }
                            catch(e){
                                winzClearMess(formid);
                                alert(d);
                            }
                        },
                        error: function(d){
                            winzClearMess(formid);
                            winzMessageBox(formid, "Backup fallito");
                        }
                    });
                    winzStoppable(formid, jqxhr);
                }
            });
        }
    });
    
    // DEFINIZIONE TAB RESTORE
    $(prefix+"LB_RESTORE").css({"position":"absolute", "left":20, "top":120});
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Settaggi", code:"SET_TAB_SETTINGS"},
            {title:"Manutenzione", code:"SET_TAB_MAINTENANCE"},
            {title:"Importazioni", code:"SET_TAB_IMPORTS"},
            {title:"Backup", code:"SET_TAB_BACKUP"},
            {title:"Restore", code:"SET_TAB_RESTORE"}
        ],
        select:function(i, p){
            switch(i){
            case 5:
                listqbk();
                break;
            }
        }
    });
    
    objtabs.currtab(1);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            objgridsel.where("");
            objgridsel.query();
        }
    );
    function adminengage(lb, left, top, width, fn, cp, dt){
        $(prefix+lb).rylabel({
            left:left,
            top:top,
            width:width,
            caption:cp,
            button:true,
            click:function(o){
                winzProgress(formid);
                $.post(_cambusaURL+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "function":fn,
                        "data":dt
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
                    }
                );
            }
        });
    }
    
    function importego(){
        $(prefix+"LB_IMPORTEGO").rylabel({
            left:20,
            top:offsety,
            width:250,
            caption:"Importazione utenti e ruoli da Ego",
            button:true,
            click:function(o){
                winzProgress(formid);
                $.post(_cambusaURL+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "function":"importego",
                        "data":{
                            "APPID":_sessioninfo.appid,
                            "ENVID":_sessioninfo.envid
                        }
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
                    }
                );
            }
        });
    }
    function listqbk(){
        $.post(_cambusaURL+"ryquiver/quiver.php", 
            {
                "sessionid":_sessionid,
                "env":_sessioninfo.environ,
                "function":"system_listqbk",
                "data":{}
            }, 
            function(d){
                try{
                    var v=$.parseJSON(d);
                    if(v.success>0){ 
                        listbackup=v.params["LIST"];
                        var h="";
                        h+="<table>";
                        h+="<tr>";
                        h+="<th style='padding-right:20px;padding-bottom:7px;'><b>Ambiente</b></th><th style='padding-right:20px;'><b>Data/Ora</b></th><th></th>";
                        h+="</tr>";
                        for(var i in listbackup){
                            h+="<tr>";
                            h+="<td style='padding-right:20px;padding-bottom:7px;'>"+listbackup[i]["ENV"]+"</td><td style='padding-right:20px;'>"+listbackup[i]["TIME"]+"</td><td><div id='"+formid+"_restore"+i+"' index='"+i+"' class='winz-rounded' style='border:1px solid silver;cursor:pointer;padding:1px;'>Ripristina</div></td>";
                            h+="</tr>";
                        }
                        h+="</table>";
                        $(prefix+"LB_RESTORE").html(h);
                        for(var i in listbackup){
                            $(prefix+"_restore"+i).on({
                                "click":function(){
                                    dbrestore($(this).attr("id"));
                                }
                            });
                        }
                    }
                    winzTimeoutMess(formid, parseInt(v.success), v.message);
                }
                catch(e){
                    winzClearMess(formid);
                    alert(d);
                }
            }
        );
    }
    function dbrestore(id){
        var i=_getinteger($("#"+id)).attr("index");
        var n=listbackup[i]["NAME"];
        winzMessageBox(formid, {
            height:230,
            message:"Effettuare il ripristino del database:<br>"+n+"?<br><br>I dati attuali verranno perduti!",
            confirm:function(){
                winzProgress(formid);
                var counter=0;
                $.ajax({
                    xhr: function(){
                        var xhr=null;
                        if(window.XMLHttpRequest){
                            xhr=new window.XMLHttpRequest();
                            //Download progress
                            xhr.addEventListener("progress", function(evt){
                                if(counter>0){
                                    var perc=Math.round(evt.loaded/counter);
                                    if(perc>100){perc=100}
                                    $("#message_"+formid).html(perc+"%");
                                }
                                else{
                                    counter=_getinteger(xhr.responseText.substr(0,18));
                                }
                            }, false);
                        } 
                        else{ 
                            try{  
                                xhr=new ActiveXObject("MSXML2.XMLHTTP");
                                //Download progress
                                xhr.attachEvent("progress", function(evt) {
                                    try{
                                        if(counter>0){
                                            var perc=Math.round(evt.loaded/counter);
                                            if(perc>100){perc=100}
                                            $("#message_"+formid).html(perc+"%");
                                        }
                                        else{
                                            counter=_getinteger(xhr.responseText.substr(0,18));
                                        }
                                    } 
                                    catch(e){} 
                                });
                            } 
                            catch(e){} 
                        }                        
                        return xhr;
                    },
                    type:"POST",
                    url:_cambusaURL+"ryquiver/quiver.php",
                    data:{
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "function":"system_restore",
                        "data":{
                            "BACKUP":n
                        }
                    },
                    success: function(d){
                        winzClearMess(formid);
                        winzMessageBox(formid, "I dati sono stati ripristinati");
                    },
                    error: function(d){
                        winzClearMess(formid);
                        winzMessageBox(formid, "Ripristino fallito");
                    }
                });
            }
        });
    }

    function enabledata(v){
        globalobjs[formid+"NAME"].enabled(v);
        globalobjs[formid+"DESCRIPTION"].enabled(v);
        globalobjs[formid+"DATAVALUE"].enabled(v);
        globalobjs[formid+"DATATYPE"].enabled(v);
        globalobjs[formid+"TAG"].enabled(v);
        oper_engage.enabled(v);
    }
}

