/****************************************************************************
* Name:            qvforum.js                                               *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvforum(settings,missing){
    var formid=RYWINZ.addform(this);

    var prefix="#"+formid;
    var currsiteid="";
    var currpersonaid="";
    var currpagename="";
    var currutente="";
    
    winzDither(formid, true);
    
    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var tx_descr=$(prefix+"DESCRIPTION").rytext({left:70, top:offsety, width:580, maxlen:200, datum:"C"});

    offsety+=40;
    var tx_wysiwyg=$(prefix+"WYSIWYG").ryedit({left:20, top:offsety, width:880, height:550, datum:"C"});

    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:810,
        top:60,
        width:80,
        caption:"Rispondi",
        button:true,
        click:function(o){
            var title=tx_descr.value();
            var contenuto=tx_wysiwyg.value();
            if(title.replace(/ /g, "")!="" && contenuto.replace(/ /g, "")!=""){
                winzProgress(formid);
                $.post(_cambusaURL+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "function":"forum_insert",
                        "data":{
                            "SITEID":currsiteid,
                            "PARENTID":_filibusterpageid,
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
                                        var h=parent.location.href;
                                        h=h.replace(/site=[^&]+/, "site="+_filibustersitename);
                                        h=h.replace(/id=[^&]+/, "id="+v.params["PAGEID"]);
                                        parent.location.href=h;
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
            }
            else{
                winzMessageBox(formid, "Inserire titolo e contenuto.");
            }
        }
    });
    
    offsety+=530;
    var oper_hide=$(prefix+"oper_hide").rylabel({
        left:770,
        top:offsety,
        width:120,
        caption:"Nascondi post",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Nascondere il post corrente?",
                ok:"OK",
                confirm:function(){
                    winzProgress(formid);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"arrows_update",
                            "data":{
                                "SYSID":_filibusterpageid,
                                "SCOPE":"2"
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    var h=parent.location.href;
                                    h=h.replace(/site=[^&]+/, "site="+_filibustersitename);
                                    h=h.replace(/id=[^&]+/, "id=");
                                    parent.location.href=h;
                                }
                                winzTimeoutMess(formid, v.success, v.message);
                            }
                            catch(e){
                                if(window.console){console.log(d)}
                                winzClearMess(formid);
                            }
                        }
                    );
                }
            });
        }
    });
    oper_hide.visible(0);
    
    var lb_utente=$(prefix+"label_utente").rylabel({left:30, top:offsety, caption:"(utente non individuato)"});

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
        tabs:[
            {title:"Messaggio", code:"MESSAGE"}
        ],
        select:function(i,p){
            switch(i){
            case 1:
                // RESET MASCHERA
                qv_maskclear(formid, "C");
                break;
            }
        }
    });
    objtabs.currtab(1);
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            var stats=[];
            var istr=0;
            
            stats[istr++]={
                "function":"singleton",
                "data":{
                    "select":"SYSID FROM QW_WEBSITES",
                    "where":"NAME='"+_filibustersitename+"'"
                },
                "return":{"SITEID":"#SYSID"}
            };
            stats[istr++]={
                "function":"singleton",
                "data":{
                    "select":"NAME,DESCRIPTION,USERINSERTID FROM QW_WEBCONTENTS",
                    "where":"SYSID='"+_filibusterpageid+"'"
                },
                "return":{"PAGENAME":"#NAME", "PAGEDESCR":"#DESCRIPTION", "PAGEOWNERID":"#USERINSERTID"}
            };
            stats[istr++]={
                "function":"singleton",
                "data":{
                    "select":"SYSID,NOME,COGNOME,UTENTEID FROM QW_PERSONE",
                    "where":"UTENTEID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"')"
                },
                "return":{"PERSONAID":"#SYSID", "PERSONANOME":"#NOME", "PERSONACOGNOME":"#COGNOME", "UTENTEID":"#UTENTEID"}
            };
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":stats
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            // SITEID
                            currsiteid=v.infos["SITEID"];
                            
                            // PAGENAME
                            currpagename=v.infos["PAGENAME"];
                            
                            // TITOLO PREDEFINITO
                            var t=_decodehtml(v.infos["PAGEDESCR"]);
                            if(t.substr(0,3)!="Re:")
                                t="Re: "+t;
                            tx_descr.value(t);

                            // PERSONAID
                            currpersonaid=v.infos["PERSONAID"];
                            
                            // NOME+COGNOME UTENTE
                            currutente=v.infos["PERSONANOME"]+" "+v.infos["PERSONACOGNOME"];
                            lb_utente.caption("<b>"+currutente+"</b>");
                            
                            // UTENTE INSERITORE
                            if(v.infos["PAGEOWNERID"]==v.infos["UTENTEID"] || _sessioninfo.admin){
                                oper_hide.visible(1);
                            }
                            
                            if(currpagename.substr(0,2)=="__")
                                winzDither(formid, false);
                            else
                                lb_utente.caption("<b>"+currutente+"</b> - Posizionarsi su un argomento per rispondere.");
                        }
                        else{
                            if(window.console){console.log(v)}
                            switch(_getinteger(v.step)){
                            case 1:
                                if(window.console){console.log("Non trovato sito: "+_filibustersitename)}
                                break;
                            case 2:
                                if(window.console){console.log("Non trovata pagina: "+_filibusterpageid)}
                                break;
                            case 3:
                                if(window.console){console.log("Non trovato utente: "+_sessioninfo.userid)}
                                break;
                            }
                        }
                    }
                    catch(e){
                        if(window.console){console.log(d)}
                    }
                }
            );
        }
    );
}

