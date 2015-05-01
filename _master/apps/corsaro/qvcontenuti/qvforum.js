/****************************************************************************
* Name:            qvforum.js                                               *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvforum(settings,missing){
    var formid=RYWINZ.addform(this);
    var objform=this;
    window.parent.FLB.forum.putInfo({"formid":formid});
    
    // SE SONO IN FASE DI LOGIN COMUNICO DI NASCONDERE I PROCESSI SUCCESSIVI
    if(window.parent.FLB.forum.action=="login"){
        window.parent.flb_forumCancel();
    }
    
    var prefix="#"+formid;
    var currsiteid="";
    var currpersonaid="";
    var currutente="";
    var currpageid="";
    var curraction="";
    
    winzDither(formid, true);
    
    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var tx_descr=$(prefix+"DESCRIPTION").rytext({left:70, top:offsety, width:650, maxlen:200, datum:"C"});

    offsety+=40;
    var tx_wysiwyg=$(prefix+"WYSIWYG").ryedit({left:20, top:offsety, width:700, height:450, datum:"C"});

    offsety+=430;
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:20,
        top:offsety,
        width:80,
        caption:"Invia",
        button:true,
        click:function(o){
            var title=tx_descr.value();
            var contenuto=tx_wysiwyg.value();
            if(title.replace(/ /g, "")!="" && contenuto.replace(/ /g, "")!=""){
                switch(curraction){
                case "insert":
                    winzProgress(formid);
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"forum_insert",
                            "data":{
                                "SITEID":currsiteid,
                                "PARENTID":currpageid,
                                "DESCRIPTION":title,
                                "REGISTRY":contenuto
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    tx_descr.clear();
                                    tx_wysiwyg.clear();
                                    RYWINZ.modified(formid, 0);
                                    setTimeout(
                                        function(){
                                            window.parent.FLB.gotoPage(currpageid);
                                        }
                                    );
                                }
                                winzTimeoutMess(formid, v.success, v.message);
                            }
                            catch(e){
                                if(window.console){console.log(d)}
                                winzClearMess(formid);
                            }
                        }
                    );
                    break;
                case "update":
                    winzProgress(formid);
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"arrows_update",
                            "data":{
                                "SYSID":currpageid,
                                "DESCRIPTION":title,
                                "REGISTRY":contenuto,
                                "_AUTOTAGS":"1"
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    tx_descr.clear();
                                    tx_wysiwyg.clear();
                                    RYWINZ.modified(formid, 0);
                                    setTimeout(
                                        function(){
                                            window.parent.FLB.gotoPage(currpageid);
                                        }
                                    );
                                }
                                winzTimeoutMess(formid, v.success, v.message);
                            }
                            catch(e){
                                if(window.console){console.log(d)}
                                winzClearMess(formid);
                            }
                        }
                    );
                    break;
                }
            }
            else{
                winzMessageBox(formid, "Inserire titolo e contenuto.");
            }
        }
    });
    
    $(prefix+"label_utente").css({"position":"absolute", "left":300, "top":offsety, "width":420, "text-align":"right"});

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Messaggio", code:"MESSAGE"}
        ],
        select:function(i,p){
            switch(i){
            case 1:
                // RESET MASCHERA
                RYWINZ.MaskClear(formid, "C");
                break;
            }
        }
    });
    objtabs.currtab(1);

    RYWINZ.logoutcalls.push(function(done){
        try{window.parent.FLB.forum.showLogin()}catch(e){}
        if(done){done()}
    });
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            var stats=[];
            var istr=0;
            
            stats[istr++]={
                "function":"singleton",
                "data":{
                    "select":"SYSID FROM QW_WEBSITES",
                    "where":"[:UPPER(NAME)]='"+_filibustersitename.toUpperCase()+"'"
                },
                "return":{"SITEID":"#SYSID"}
            };
            stats[istr++]={
                "function":"singleton",
                "data":{
                    "select":"SYSID,NOME,COGNOME,UTENTEID FROM QW_PERSONE",
                    "where":"UTENTEID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"')"
                },
                "return":{"PERSONAID":"#SYSID", "PERSONANOME":"#NOME", "PERSONACOGNOME":"#COGNOME", "UTENTEID":"#UTENTEID"}
            };
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "program":stats
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            // SITEID
                            currsiteid=v.infos["SITEID"];
                            
                            // PERSONAID
                            currpersonaid=v.infos["PERSONAID"];
                            
                            // NOME+COGNOME UTENTE
                            currutente=v.infos["PERSONANOME"]+" "+v.infos["PERSONACOGNOME"];
                            $(prefix+"label_utente").html(currutente);
                            
                            // IMPOSTO INFORAZIONI UTILI ALLA PAGINA WEB
                            try{
                                window.parent.FLB.forum.putInfo(
                                    {
                                        "userid":v.infos["UTENTEID"],
                                        "username":v.infos["PERSONANOME"]
                                    }
                                );
                                // CONTROLLO SE CI SONO AZIONI DA INTRAPRENDERE
                                switch(window.parent.FLB.forum.action){
                                case "insert":
                                    window.parent.FLB.forum.action="";
                                    objform._forumInsert(window.parent.FLB.forum.postid);
                                    break;
                                case "update":
                                    window.parent.FLB.forum.action="";
                                    objform._forumEdit(window.parent.FLB.forum.postid);
                                    break;
                                }
                                if(window.parent.FLB.forum.action!="login"){
                                    winzDither(formid, false);
                                }
                            }
                            catch(e){
                                if(window.console){console.log(e.message)}
                            }
                        }
                        else{
                            if(window.console){console.log(v)}
                            var m="Errore interno; impossibile procedere!";
                            switch(__(v.step).actualInteger()){
                            case 1:
                                m="Non trovato sito ["+_filibustersitename+ "] nell'ambiente ["+_sessioninfo.environ+"]";
                                break;
                            case 2:
                                m="Non trovato utente ["+_sessioninfo.userid+"]";
                                break;
                            }
                            if(window.console){console.log(m)}
                            alert(m);
                            RYWINZ.modified(formid, 0);
                            oper_contextengage.enabled(0);
                            winzDither(formid, false);
                            setTimeout(winz_logout);
                        }
                    }
                    catch(e){
                        if(window.console){console.log(d)}
                    }
                    // TENTO DI RENDERE VISIBILE LA LABEL DI LOGOUT SULLA PAGINA WEB
                    try{
                        window.parent.FLB.forum.showLogout();
                    }
                    catch(e){
                        if(window.console){console.log(e.message)}
                    }
                }
            );
        }
    );

    this._forumInsert=function(pageid){
        try{
            winzDither(formid, true);
            currpageid=pageid;
            curraction="insert";
            tx_descr.clear();
            tx_wysiwyg.clear();
            RYQUE.query({
                sql:"SELECT DESCRIPTION FROM QW_WEBCONTENTS WHERE SYSID='"+pageid+"'",
                ready:function(v){
                    try{
                        // TITOLO PREDEFINITO
                        var t=v[0]["DESCRIPTION"];
                        if(t.substr(0,3)!="Re:")
                            t="Re: "+t;
                        tx_descr.value(t);
                        RYWINZ.modified(formid, 0);
                        winzDither(formid, false);
                    }
                    catch(e){
                        if(window.console){console.log(d)}
                    }
                }
            });
        }
        catch(e){
            if(window.console){console.log(e.message)}
        }
    }

    this._forumEdit=function(pageid){
        try{
            winzDither(formid, true);
            currpageid=pageid;
            curraction="update";
            tx_descr.clear();
            tx_wysiwyg.clear();
            RYQUE.query({
                sql:"SELECT DESCRIPTION,REGISTRY FROM QW_WEBCONTENTS WHERE SYSID='"+pageid+"'",
                ready:function(v){
                    try{
                        tx_descr.value(v[0]["DESCRIPTION"]);
                        tx_wysiwyg.value(v[0]["REGISTRY"]);
                        RYWINZ.modified(formid, 0);
                        winzDither(formid, false);
                    }
                    catch(e){
                        if(window.console){console.log(d)}
                    }
                }
            });
        }
        catch(e){
            if(window.console){console.log(e.message)}
        }
    }

    this._forumDelete=function(pageid, parentid){
        try{
            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"arrows_update",
                    "data":{
                        "SYSID":pageid,
                        "SCOPE":"2"
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            window.parent.FLB.gotoPage(parentid);
                        }
                        else{
                            if(window.console){console.log(v.message)}
                        }
                    }
                    catch(e){
                        if(window.console){console.log(d)}
                    }
                }
            );
        }
        catch(e){
            if(window.console){console.log(e.message)}
        }
    }
}
function _forumLogout(){
    try{
        winz_logout();
        window.parent.FLB.forum.showLogin();
    }
    catch(e){}
}
