/****************************************************************************
* Name:            postman/postman.js                                       *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_postman(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    
    winzProgress(formid);

    var currsysid="";
    var prefix="#"+formid;
    var curraction="";
    
    // DEFINIZIONE TAB SELEZIONE

    var offsety=60;

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:20,
        top:offsety,
        width:110,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="RECEIVERID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"' AND ARCHIVED=0) AND STATUS<3 AND [:DATE(SENDINGTIME,1MONTH)]>[:TODAY()]";
            objgridsel.where(q);
            objgridsel.query({
                ready:function(){
                    // SEGNO LE NOTIFICHE COME RICEVUTE
                    var data = new Object();
                    data["ACTION"]="RECEIVED";
                    data["EGOID"]=_sessioninfo.userid;
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"messages_status",
                            "data":data
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    $("#winz-notifications").html("").hide();
                                    $("head>title").html(_apptitle);
                                }
                                else{
                                    if(window.console)console.log(v);
                                }
                            }
                            catch(e){
                                if(window.console)console.log(d);
                            }
                        }
                    );
                }
            });
        }
    });
    
    var oper_engage=$(prefix+"oper_engage").rylabel({
        left:160,
        top:offsety,
        width:110,
        caption:"Attiva",
        button:true,
        click:function(o){
            try{
                var params=$.parseJSON(curraction);
                RYWINZ.shell( params );
            }catch(e){
                alert(e.message);
            }
        }
    });

    var oper_unread=$(prefix+"oper_unread").rylabel({
        left:300,
        top:offsety,
        width:110,
        caption:"Ripristina",
        button:true,
        click:function(o){
            objgridsel.selengage(   // Elenco dei SYSID selezionati
                function(o,s){        
                    // SEGNO LE NOTIFICHE COME NON LETTE
                    var data = new Object();
                    data["ACTION"]="UNREAD";
                    data["LIST"]=s;
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"messages_status",
                            "data":data
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0)
                                    objgridsel.dataload();
                                else
                                    if(window.console)console.log(v);
                            }
                            catch(e){
                                if(window.console)console.log(d);
                            }
                        }
                    );
                }
            );
        }
    });

    var oper_read=$(prefix+"oper_read").rylabel({
        left:440,
        top:offsety,
        width:110,
        caption:"Letto",
        button:true,
        click:function(o){
            objgridsel.selengage(   // Elenco dei SYSID selezionati
                function(o,s){        
                    // SEGNO LE NOTIFICHE COME LETTE
                    var data = new Object();
                    data["ACTION"]="READ";
                    data["LIST"]=s;
                    $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"messages_status",
                            "data":data
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0)
                                    objgridsel.dataload();
                                else
                                    if(window.console)console.log(v);
                            }
                            catch(e){
                                if(window.console)console.log(d);
                            }
                        }
                    );
                }
            );
        }
    });

    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:580,
        top:offsety,
        width:110,
        caption:"Elimina",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare i messaggi selezionati?",
                ok:"Elimina",
                confirm:function(){
                    objgridsel.selengage(   // Elenco dei SYSID selezionati
                        function(o,s){        
                            // SEGNO LE NOTIFICHE COME CANCELLATE
                            var data = new Object();
                            data["ACTION"]="DELETE";
                            data["LIST"]=s;
                            $.post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessioninfo.sessionid,
                                    "env":_sessioninfo.environ,
                                    "function":"messages_status",
                                    "data":data
                                }, 
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0)
                                            objgridsel.query();
                                        else
                                            if(window.console)console.log(v);
                                    }
                                    catch(e){
                                        if(window.console)console.log(d);
                                    }
                                }
                            );
                        }
                    );
                }
            });
        }
    });
    offsety+=35;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:703,
        height:200,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QVMESSAGES",
        orderby:"SENDINGTIME DESC",
        columns:[
            {id:"PRIORITY", caption:"", width:25},
            {id:"ENGAGEPARAMS", caption:"", width:25},
            {id:"STATUS", caption:"", width:0},
            {id:"SENDINGTIME", caption:"Invio", width:130, type:":", code:"POSTMAN_SENDING"},
            {id:"DESCRIPTION", caption:"Descrizione", width:700, code:"DESCRIPTION"}
        ],
        changerow:function(o,i){
            currsysid="";
            curraction="";
            $(prefix+"REGISTRY").hide();
            oper_engage.enabled(0);
            oper_unread.enabled(o.isselected());
            oper_read.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
            if(i>0){
                o.solveid(i);
            }
        },
        changesel:function(o){
            oper_unread.enabled(o.isselected());
            oper_read.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currsysid=d;
            RYQUE.query({
                sql:"SELECT REGISTRY,ENGAGEPARAMS FROM QVMESSAGES WHERE SYSID='"+currsysid+"'",
                ready:function(v){
                    if(v.length>0){
                        curraction=v[0]["ENGAGEPARAMS"];
                        if(curraction!=""){
                            oper_engage.enabled(1);
                        }
                        oper_unread.enabled(1);
                        oper_read.enabled(1);
                        oper_delete.enabled(1);
                        $(prefix+"REGISTRY").html(v[0]["REGISTRY"]);
                        $(prefix+"REGISTRY").show();
                    }
                }
            });
            
        },
        enter:function(o,d){
            if(oper_engage.enabled()){
                oper_engage.engage();
            }
        },
        before:function(o, d){
            for(var i in d){
                // COLONNA PRIORITY
                switch(d[i]["PRIORITY"]){
                case "0":
                    d[i]["PRIORITY"]=GALLERY.Low();
                    break;
                case "1":
                    d[i]["PRIORITY"]=GALLERY.Medium();
                    break;
                case "2":
                    d[i]["PRIORITY"]=GALLERY.High()
                    break;
                }
                // COLONNA ENGAGEPARAMS
                if(d[i]["ENGAGEPARAMS"]!=""){
                    d[i]["ENGAGEPARAMS"]=GALLERY.Action();
                }
                // COLONNA STATUS
                var fd=o.screenrow(i);
                if(d[i]["STATUS"]<"2")
                    $(fd).css({"color":"black"});
                else
                    $(fd).css({"color":"gray"});
            }
        }
    });

    offsety=300;
    $(prefix+"REGISTRY").css({position:"absolute", left:20, top:offsety, width:695, height:400, scroll:"auto", background:"white", border:"1px solid silver", "padding":3, "display":"none"});
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Notifiche", code:"POSTMAN_NOTIFICATIONS"}
        ],
        select:function(i,p){

        }
    });
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            TAIL.enqueue(function(){
                oper_refresh.engage();
                TAIL.free();
            });
            TAIL.enqueue(function(){
                winzClearMess(formid);
                TAIL.free();
            });
            TAIL.wriggle();
        }
    );
    this.refresh=function(){
        oper_refresh.engage();
    }
}
