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
	oper_read.enabled(0);

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
	oper_unread.enabled(0);

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
	oper_delete.enabled(0);

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:150,
        top:offsety,
        width:110,
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
    
    offsety+=35;
    
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
            oper_unread.enabled(o.ischecked());
            oper_read.enabled(o.ischecked());
            oper_delete.enabled(o.ischecked());
        },
        enter:function(o,i){
            if(i>0){
				currsysid=o.cells(i, "SYSID");
				RYQUE.query({
					sql:"SELECT DESCRIPTION,REGISTRY FROM QVMESSAGES WHERE SYSID='"+currsysid+"'",
					ready:function(v){
						if(v.length>0){
							oper_unread.enabled(1);
							oper_read.enabled(1);
							oper_delete.enabled(1);
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
                    $(fd).css({"color":"gray"});
				}
            }
        }
    });
	
    $(prefix+"gridsel").contextMenu("mnpostman_popup", {
        bindings: {
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
                return true;
            },
        onShowMenu: 
            function(e, menu) {
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
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
			TAIL.enqueue(function(){
				if( $("#postman_popup").length==0 ){
					var h="";
					h+="<div id='mnpostman_popup' class='contextMenu'>";
					h+="   <ul>";
					h+="       <li id='mnpostman_read'><a href='javascript:'>Letto</a></li>";
					h+="       <li id='mnpostman_unread'><a href='javascript:'>Ripristina</a></li>";
					h+="       <li class='contextSeparator'></li>";
					h+="       <li id='mnpostman_delete'><a href='javascript:'>Elimina</a></li>";
					h+="   </ul>";
					h+="</div>";
					$("body").append(h);    
				}
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
