/****************************************************************************
* Name:            postman/postman_mobile.js                                *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_postman_mobile(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    
    winzProgress(formid);

    var currsysid="";
    var prefix="#"+formid;
    
    // DEFINIZIONE TAB SELEZIONE

    var offsety=60;

    var oper_read=$(prefix+"oper_read").rylabel({
        left:20,
        top:offsety,
        width:110,
        caption:"Letto",
        button:true,
        click:function(o){
            objgridsel.selengage(   // Elenco dei SYSID selezionati
                function(o,s){      
					y=[];
					for(var i in s)
						y.push(objgridsel.cells(s[i], "SYSID"));
					y=y.join("|");
                    // SEGNO LE NOTIFICHE COME LETTE
                    var data = new Object();
                    data["ACTION"]="READ";
                    data["LIST"]=y;
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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
                                    oper_refresh.engage();
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
	oper_read.visible(0);

	offsety+=30;
    var oper_unread=$(prefix+"oper_unread").rylabel({
        left:20,
        top:offsety,
        width:110,
        caption:"Ripristina",
        button:true,
        click:function(o){
            objgridsel.selengage(   // Elenco dei SYSID selezionati
                function(o,s){        
					y=[];
					for(var i in s)
						y.push(objgridsel.cells(s[i], "SYSID"));
					y=y.join("|");
                    // SEGNO LE NOTIFICHE COME NON LETTE
                    var data = new Object();
                    data["ACTION"]="UNREAD";
                    data["LIST"]=y;
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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
                                    oper_refresh.engage();
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
	oper_unread.visible(0);

	offsety+=30;
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:20,
        top:offsety,
        width:110,
        caption:"Elimina",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare i messaggi selezionati?",
                ok:"Elimina",
				width:300,
                confirm:function(){
                    objgridsel.selengage(   // Elenco dei SYSID selezionati
                        function(o,s){        
							y=[];
							for(var i in s)
								y.push(objgridsel.cells(s[i], "SYSID"));
							y=y.join("|");
                            // SEGNO LE NOTIFICHE COME CANCELLATE
                            var data = new Object();
                            data["ACTION"]="DELETE";
                            data["LIST"]=y;
                            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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
                                            oper_refresh.engage();
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
	oper_delete.visible(0);

    offsety=60;
	var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:10,
		right:10,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="SELECT * FROM QVMESSAGES WHERE RECEIVERID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='"+_sessioninfo.userid+"' AND ARCHIVED=0) AND STATUS<3 AND [:DATE(SENDINGTIME,1MONTH)]>[:TODAY()] ORDER BY SENDINGTIME DESC";
            RYQUE.query({
				sql:q,
                ready:function(v){
					objgridsel.setmatrix(v);
                    // SEGNO LE NOTIFICHE COME RICEVUTE
                    var data = new Object();
                    data["ACTION"]="RECEIVED";
                    data["EGOID"]=_sessioninfo.userid;
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
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
    
    offsety+=45;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").rytable({
        top:offsety,
        numbered:false,
        checkable:true,
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:300, code:"DESCRIPTION"},
            {id:"SENDINGTIME", caption:"Invio", width:150, type:":", code:"POSTMAN_SENDING"}
        ],
        changesel:function(o){
            //oper_unread.enabled(o.ischecked());
            //oper_read.enabled(o.ischecked());
            //oper_delete.enabled(o.ischecked());
        },
        enter:function(o,i){
            if(i>0){
				currsysid=o.cells(i, "SYSID");
				RYQUE.query({
					sql:"SELECT DESCRIPTION,REGISTRY FROM QVMESSAGES WHERE SYSID='"+currsysid+"'",
					ready:function(v){
						if(v.length>0){
							//oper_unread.enabled(1);
							//oper_read.enabled(1);
							//oper_delete.enabled(1);
							lb_context.caption("<b>"+v[0]["DESCRIPTION"]+"</b>");
							$(prefix+"CONTENTS").html(v[0]["REGISTRY"]);
							objtabs.enabled(2, true);
							objtabs.currtab(2);
						}
					}
				});
            }
        },
        after:function(o, d){
            for(var i in d){
                var fd=o.screenrow(i);
                if(d[i]["STATUS"].actualNumber()<2){
					switch(d[i]["PRIORITY"].actualNumber()){
					case 0:
						$(fd).css({"color":"green"});
						break;
					case 1:
						$(fd).css({"color":"black"});
						break;
					case 2:
						$(fd).css({"color":"red"});
						break;
					}
				}	
                else{
                    $(fd).css({"color":"silver"});
				}
            }
        }
    });
	
    $(prefix+"gridsel").contextMenu("postman_popup", {
        menuStyle:{
            "width":"200px",
			"font-size":"18px",
			"line-height":"30px"
        },
		eventPosX:"customX",
		eventPosY:"customY",
        bindings: {
            'mnpostman_open': function(t){
				objgridsel.enter();
            },
            'mnpostman_read': function(t){
				oper_read.engage();
            },
            'mnpostman_unread': function(t){
				oper_unread.engage();
            },
            'mnpostman_delete': function(t){
				oper_delete.engage();
            }
        },
        onContextMenu:
            function(e) {
				var e=false;
				
				if(objgridsel.index()>0){
					e=true;
				}
				else{
					RYBOX.menudisable("mnpostman_open");
				}
				
				if(objgridsel.ischecked()){
					e=true;
				}
				else{
					RYBOX.menudisable("mnpostman_read");
					RYBOX.menudisable("mnpostman_unread");
					RYBOX.menudisable("mnpostman_delete");
				}
				
                return e;
            },
        onShowMenu: 
            function(e, menu) {
				e["customX"]=20;
				e["customY"]=150;
                return menu;
            }
    });


	// TAB CONTESTO

    offsety=50;
	
	var lb_context=$(prefix+"context").rylabel({left:20, top:offsety, caption:""});

	offsety+=40;
    $(prefix+"REGISTRY").css({position:"absolute", left:10, top:offsety, right:10, background:"white", border:"1px solid silver", "padding":3, overflow:"auto"});
	$(prefix+"REGISTRY").addClass("ryselectable");
	$(prefix+"REGISTRY").html("<table><tr><td id='"+formid+"CONTENTS'></td></tr></table>");

	$(window).resize(
		function(){
			setTimeout(function(){
				setstyle();
			}, 100);
		}
	);

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Notifiche", code:"POSTMAN_NOTIFICATIONS"},
			{title:"Contesto", code:"POSTMAN_CONTEXT"}
        ],
        select:function(i,p){
			if(i==1)
				objtabs.enabled(2, false);
			else
				setstyle();
        }
    });
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
	objtabs.enabled(2, false);
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid);

    RYBOX.babels({
		"POP_POSTMAN_OPEN":"Visualizza messaggio",
        "POP_POSTMAN_READ":"Smarca selezionati",
        "POP_POSTMAN_UNREAD":"Ripristina selezionati",
        "POP_POSTMAN_DELETE":"Elimina selezionati..."
    });

    RYBOX.localize(_sessioninfo.language, formid,
        function(){
			TAIL.enqueue(function(){
				RYBOX.menucontext("postman_popup", [
					{id:"mnpostman_open", code:"POP_POSTMAN_OPEN"},
					{caption:"-"},
					{id:"mnpostman_read", code:"POP_POSTMAN_READ"},
					{id:"mnpostman_unread", code:"POP_POSTMAN_UNREAD"},
					{caption:"-"},
					{id:"mnpostman_delete", code:"POP_POSTMAN_DELETE"}
				]);
                TAIL.free();
            });
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
	function setstyle(){
		var h=0;
		var t=0;
		$.each( $(prefix+"REGISTRY").parents(), 
			function(key, value){
				if($(value).hasClass("window_hanger")){
					h=$(value).height()-t-$(prefix+"REGISTRY").position().top-10;
				}
				else{
					t+=$(value).position().top;
				}
			}
		);
		$(prefix+"REGISTRY").css({height:h});
	}
    this.refresh=function(){
        oper_refresh.engage();
    }
}
