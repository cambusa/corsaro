/****************************************************************************
* Name:            ryquiverbase.js                                          *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function ryQuiver(missing){
    var handletemp=false;
    /******************
    | RICHIESTA SYSID |
    ******************/
    this.RequestID=function(formid, settings){
        var objgrid;
        var prophelperid="";
        
        var proptitle="Selezione";
        if(settings.title!=missing){proptitle=settings.title}
        if(settings.titlecode!=missing){
            if(settings.titlecode!="")
                proptitle=RYBOX.babels(settings.titlecode)
        }
        
        var proptable=""; if(settings.table!=missing){proptable=settings.table}
        var propwhere=""; if(settings.where!=missing){propwhere=settings.where}
        var propclause=""; if(settings.clause!=missing){propclause=settings.clause}
        var propmultiple=false; if(settings.multiple!=missing){propmultiple=settings.multiple}
        var propargs=""; if(settings.args!=missing){propargs=settings.args}
        var proporderby="DESCRIPTION"; if(settings.orderby!=missing){proporderby=settings.orderby}
        var propselect=""; if(settings.select!=missing){propselect=settings.select}
        var proppreselect=""; if(settings.preselect!=missing){proppreselect=settings.preselect}
        var propclasstable=""; if(settings.classtable!=missing){propclasstable=settings.classtable}
        var propsubid=""; if(settings.subid!=missing){propsubid=settings.subid}
        var propmandatory=true; if(settings.mandatory!=missing){propmandatory=settings.mandatory}
        var propclose=false; if(settings.close!=missing){propclose=settings.close}

        var prophelpwidth=600;
        var prophelpheight=410;
        var propinit=false;
        var proprequestid="";
        var propprovider="";
        if(propsubid==""){
            if(_dialogcount==0){
                // INTERROGAZIONE DI PRIMO LIVELLO
                proprequestid=RYQUE.reqid();
                propprovider=RYQUE.provider();
            }
        }
        else{
            // INTERROGAZIONE DI SECONDO LIVELLO
            // AUMENTO LE DIMENSIONI PER AVERE UN FEEDBACK
            prophelpwidth=610;
            prophelpheight=420;
            if(_dialogcount==1){
                proprequestid=RYQUEAUX.reqid();
                propprovider=RYQUEAUX.provider();
            }
        }
        var cookiename=_sessioninfo.environ+"_HELP_"+_globalforms[formid].classform+"_"+propclasstable;
        
        // CREAZIONE DIALOGBOX
        var dlg=winzDialogGet(formid);
        var hangerid=dlg.hanger;
        var h="";
        var vK=[];
        winzDialogParams(dlg, {
            width:prophelpwidth,
            height:prophelpheight,
            open:function(){
                castFocus(actualid+"helpersearch");
            },
            close:function(){
                objgrid.dispose(
                    function(){
                        winzDisposeCtrl(formid, vK);
                        winzDialogFree(dlg);
                        if(propclose!==false){propclose()}
                    }
                );
            }
        });
        // DEFINIZIONE DEL CONTENUTO
        var actualid=formid+propsubid;
        h+="<div class='winz_dialog_title'>";
        h+=proptitle;
        h+="</div>";
        h+=winzAppendCtrl(vK, actualid+"helpergrid");
        h+=winzAppendCtrl(vK, actualid+"helperlbsearch");
        h+=winzAppendCtrl(vK, actualid+"helpersearch");
        if(propclasstable!=""){
            h+=winzAppendCtrl(vK, actualid+"helperlbclass");
            h+=winzAppendCtrl(vK, actualid+"helperclass");
        }
        h+=winzAppendCtrl(vK, actualid+"helperrefresh");
        h+=winzAppendCtrl(vK, actualid+"helperreset");
        h+=winzAppendCtrl(vK, actualid+"helperok");
        h+=winzAppendCtrl(vK, actualid+"helpercancel");
        $("#"+hangerid).html(h);
        
        switch(proptable){
            case "QVGENRES":
            case "QVOBJECTS":
            case "QVMOTIVES":
            case "QVARROWS":
            case "QVQUIVERS":
            case "QVFILES":
                if( !propwhere.match(/ DELETED/i) ){
                    if(propwhere!="")
                        propwhere+=" AND ";
                    propwhere+="DELETED=0";
                }
        }
        if(window.console&&_sessioninfo.debugmode){console.log("WHERE:"+propwhere)}
        var offsety=60;
        objgrid=$("#"+actualid+"helpergrid").ryque({
            left:20,
            top:offsety,
            width:300,
            height:300,
            formid:formid,
            requestid:proprequestid,
            provider:propprovider,
            numbered:propmultiple,
            checkable:propmultiple,
            environ:_sessioninfo.environ,
            from:proptable,
            clause:propclause,
            args:propargs,
            orderby:proporderby,
            columns:[
                {id:"DESCRIPTION", caption:RYBOX.babels("DESCRIPTION"), width:200}
            ],
            changerow:function(o,i){
                prophelperid="";
                if(i>0)
                    o.solveid(i);
            },
            solveid:function(o,d){
                prophelperid=d;
            },
            enter:function(){
                if(prophelperid!=""){
                    selectmanage(prophelperid);
                }
            },
            ready:function(o){
                if(o.count()==1){
                    setTimeout(
                        function(){
                            o.index(1);
                            castFocus(actualid+"helpergrid");
                        }
                    );
                }
            },
            initialized:function(o){
                // INIZIALIZZAZIONE LA METTO FUORI (LA REQUESTID E' GIA' RISOLTA)
                //if(propclasstable!="")
                //    objclass.value($.cookie(cookiename), true);
                //else
                //    setTimeout(function(){objrefresh.engage()}, 100);
            }
        });
        $("#"+actualid+"helperlbsearch").rylabel({left:330, top:offsety, caption:RYBOX.babels("SEARCH"), formid:formid});
        offsety+=20;
        var objsearch=$("#"+actualid+"helpersearch").rytext({
            left:330, top:offsety, width:250, formid:formid,
            assigned:function(){
                objrefresh.engage()
            }
        });
        if(propclasstable!=""){
            offsety+=30;
            $("#"+actualid+"helperlbclass").rylabel({left:330, top:offsety, caption:RYBOX.babels("CLASS"), formid:formid});
            offsety+=20;
            var objclass=$("#"+actualid+"helperclass").ryhelper({
                left:330, top:offsety, width:250, formid:formid, subid:"aux", table:propclasstable, title:RYBOX.babels("HLP_SELCLASS"), 
                open:function(o){
                    o.where("");
                },
                assigned:function(o){
                    $.cookie(cookiename, o.value(), {expires:10000});
                    objrefresh.engage()
                },
                clear:function(o){
                    $.cookie(cookiename, "", {expires:10000});
                }
            });
        }
        offsety+=40;
        var objrefresh=$("#"+actualid+"helperrefresh").rylabel({
            left:330,
            top:offsety,
            caption:RYBOX.babels("BUTTON_REFRESH"),
            formid:formid,
            button:true,
            click:function(o){
                var q=propwhere;
                var arg;
                if(typeof propargs=="object")
                    arg=propargs;
                else
                    arg={};
                var c="";
                var t=qv_forlikeclause(objsearch.value());
                if(propclasstable!=""){c=objclass.value()}

                if(t!=""){
                    if(q!=""){q+=" AND "}
                    q="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                    arg["DESCRIPTION"]=t;
                    arg["TAG"]=t;
                }
                if(c!=""){
                    if(q!=""){q+=" AND "}
                    q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+c+"')"
                }
                objgrid.where(q);
                objgrid.query({
                    args:arg,
                    ready:function(o){
                        if(proppreselect!="" && !propinit){
                            propinit=true;
                            o.selbyid(proppreselect);
                        }
                    }
                });
            }
        });
        var objreset=$("#"+actualid+"helperreset").rylabel({
            left:500,
            top:offsety,
            width:70,
            caption:RYBOX.babels("BUTTON_RESET"),
            formid:formid,
            button:true,
            click:function(o){
                objsearch.value("");
                if(propclasstable!=""){
                    objclass.value("")
                    $.cookie(cookiename, "", {expires:10000});
                }
                objrefresh.engage();
            }
        });
        $("#"+actualid+"helperok").rylabel({
            left:20,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_OK"),
            button:true,
            formid:formid,
            click:function(o){
                if(propmultiple){
                    objgrid.selengage(
                        function(o, d){
                            selectmanage(d);
                        },
                        function(){
                            winzMessageBox(formid, RYBOX.babels("MSG_NOSELECTION"));
                        }
                    )
                }
                else{
                    if(prophelperid!=""){
                        selectmanage(prophelperid);
                    }
                    else if(!propmandatory){
                        winzDialogClose(dlg);
                        if(settings.onselect!=missing){
                            var data={"SYSID":""};
                            setTimeout(
                                function(){
                                    settings.onselect(data);
                                }, 100
                            );
                        }
                    }
                    else{
                        winzMessageBox(formid, RYBOX.babels("MSG_NOSELECTION"));
                    }
                }
            }
        });
        $("#"+actualid+"helpercancel").rylabel({
            left:120,
            top:dlg.height-40,
            width:80,
            caption:RYBOX.babels("BUTTON_CANCEL"),
            button:true,
            formid:formid,
            click:function(o){
                winzDialogClose(dlg);
            }
        });
        // INIZIALIZZAZIONE (LA REQUESTID E' GIA' RISOLTA)
        if(propclasstable!="")
            objclass.value($.cookie(cookiename), true);
        else
            setTimeout(function(){objrefresh.engage()}, 100);

        // MOSTRO LA DIALOGBOX
        winzDialogOpen(dlg);

        function selectmanage(id){
            if(propselect!="" && propmultiple==false){
                RYQUEAUX.query({
                    sql:"SELECT SYSID,"+propselect+" FROM "+proptable+" WHERE SYSID='"+id+"'",
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
            else{
                winzDialogClose(dlg);
                if(settings.onselect!=missing){
                    var data={"SYSID":id};
                    setTimeout(
                        function(){
                            settings.onselect(data);
                        }, 100
                    );
                }
            }
        }
    },
    /*****************************
    | MANUTENZIONE FILE OBSOLETI |
    *****************************/
    this.ManageTemp=function(){
        if(handletemp!==false)
            clearTimeout(handletemp);
        handletemp=setTimeout(function(){
            try{
                handletemp=false;
                $.post(_cambusaURL+"ryquiver/quiver.php", 
                    {
                        "sessionid":_sessionid,
                        "env":_sessioninfo.environ,
                        "function":"managetemp"
                    }
                );
            }catch(e){}
        }, 10000);
    }
    /***********************
    | STAMPA ELEMENTO HTML |
    ***********************/
    this.PrintElement=function(sourceid, option){
        var h=$("#"+sourceid).html();
        $("#winz-printing").html(h);
        $("#winz-printing").printThis({importCSS:false});
    }
    /***************
    | STAMPA TESTO |
    ***************/
    this.PrintText=function(htext, option){
        htext=htext.replace(/\[PAGEBREAK\]/ig, "<p style='page-break-before:always'></p>");
        htext="<div style='font-family:sans-serif;'>"+htext+"</div>";
        $("#winz-printing").html(htext);
        $("#winz-printing").printThis({importCSS:false});
    }
};
var QVR=new ryQuiver();
(function($,missing) {
    $.extend(true,$.fn, {
        ryhelper:function(settings){
 			var propleft=20;
			var proptop=20;
			var propwidth=120;
			var propheight=22;
			var propfocusout=true;
			var propctrl=false;
			var propshift=false;
            var propalt=false;
			var propobj=this;
			var propenabled=true;
			var propvisible=true;
            var propmultiple=false;
			var propformid="";
            var proptable="";
            var propwhere="";
            var propclause="";
            var propargs="";
            var propselect="";
            this.onselect=null;
            this.notfound=null;
            var proporderby="DESCRIPTION";
            var propclasstable="";
            var propsubid="";
            var propsysid=""; 
            var proptitle=_sessioninfo.appdescr;
            var proptitlecode="";
            var prophelpwidth=600;
            var prophelpheight=400;
            
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="helper";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.table!=missing){proptable=settings.table}
            if(settings.clause!=missing){propclause=settings.clause}
            if(settings.orderby!=missing){proporderby=settings.orderby}
            if(settings.classtable!=missing){propclasstable=settings.classtable}
            if(settings.subid!=missing){propsubid=settings.subid}
            if(settings.title!=missing){proptitle=settings.title}
            if(settings.titlecode!=missing){proptitlecode=settings.titlecode}
            if(settings.multiple!=missing){propmultiple=settings.multiple}
            if(settings.select!=missing){propselect=settings.select}
            if(settings.onselect!=missing){this.onselect=settings.onselect}
            if(settings.notfound!=missing){this.notfound=settings.notfound}
            
            if(propsubid!=""){
                // INTERROGAZIONE DI SECONDO LIVELLO
                // AUMENTO LE DIMENSIONI PER AVERE UN FEEDBACK
                prophelpwidth=610;
                prophelpheight=410;
            }

            if(settings.formid!=missing){
                propformid=settings.formid;
                if($("#"+propname).prop("parentid")==missing){
                    // Aggancio alla maschera per quando i campi sono dinamici
                    $("#"+propname).prop("parentid", propformid);
                    _globalforms[propformid].controls[propname]=propname.substr(propformid.length);
                }
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            };
            if(settings.tag!=missing){this.tag=settings.tag};

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("ryhelper")
            .css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight,"background-color":"silver","font-family":"verdana,sans-serif","font-size":"13px","line-height":"normal"})
            .html("<a href='javascript:' id='"+propname+"_anchor'></a>");
            $("#"+propname+"_anchor").css({"position":"absolute","width":propwidth,"height":propheight,"text-decoration":"none","color":"transparent","background-color":"transparent","cursor":"default"});
            $("#"+propname+"_anchor").html("<div id='"+propname+"_internal'></div><div id='"+propname+"_button'></div><div id='"+propname+"_clear'></div>");
            $("#"+propname+"_internal").css({"position":"absolute","left":1,"top":1,"width":propwidth-2,"height":propheight-2,"color":"#000000","background-color":"#FFFFFF","overflow":"hidden"});
            $("#"+propname+"_internal").html("<div id='"+propname+"_text'></div>");
            $("#"+propname+"_text").css({"position":"absolute","cursor":"text","left":2,"top":1,"width":propwidth-20,"height":propheight-4,"overflow":"hidden","white-space":"nowrap"});
            $("#"+propname+"_button").css({"position":"absolute","cursor":"pointer","left":propwidth-20,"top":2,"width":18,"height":18,"background":"url("+_cambusaURL+"ryquiver/images/helper.png)"});
            $("#"+propname+"_clear").css({"position":"absolute","z-index":10000,"cursor":"pointer","left":propwidth,"top":2,"width":18,"height":18,"display":"none","background":"url("+_cambusaURL+"ryquiver/images/clear.png)"});
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_internal").css({"background-color":globalcolorfocus});
            			if(propfocusout){
            				propfocusout=false;
            			}
                        propobj.raisegotfocus();
            		}
            	}
            );
            $("#"+propname+"_anchor").focusout(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_internal").css({"background-color":"#FFFFFF"});
                        propobj.raiselostfocus();
                        propfocusout=true;
            		}
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
            		if(propenabled){
            			propctrl=k.ctrlKey; // da usare nella press
            			propshift=k.shiftKey;
                        propalt=k.altKey;
            			if(k.which==46){ // delete
            				if(propctrl){
            					propobj.clear();
            				}
            			}
            			else if(k.which==113 || (propalt && k.which==50)){ // F2  Alt+2
                            k.preventDefault();
            				propobj.showhelper();
            			}
                        else if(k.which==8){
                            return false;
                        }
                        else if(k.which==9){
                            return nextFocus(propname, propshift);
                        }
            		}
            	}
            );
            $("#"+propname).mousedown(
            	function(evt){
            		if(propenabled){
            			castFocus(propname);
            		}
            	}
            );
            $("#"+propname+"_button").mouseover(
            	function(evt){
                    if(propsysid!="" && propenabled)
                        $("#"+propname+"_clear").show();
            	}
            );
            $("#"+propname+"_clear").click(
            	function(evt){
                    propobj.clear();
                    $("#"+propname+"_clear").hide();
            	}
            );
            $("#"+propname).mouseleave(
            	function(evt){
                    $("#"+propname+"_clear").hide();
            	}
            );
            $("#"+propname+"_button").click(
            	function(){
            		if(propenabled){
            			propobj.showhelper();
            		}
            	}
            );
             // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth});
            }
			this.showhelper=function(){
                if(settings.open!=missing){
                    settings.open(propobj);
                };
                QVR.RequestID(propformid, {
                    subid:propsubid,
                    table:proptable, 
                    where:propwhere,
                    args:propargs,
                    select:propselect,
                    preselect:propsysid,
                    orderby:proporderby,
                    clause:propclause,
                    classtable:propclasstable,
                    title:proptitle,
                    titlecode:proptitlecode,
                    multiple:propmultiple,
                    onselect:function(d){
                        propobj.value(d["SYSID"], true);
                    },
                    close:function(){
                        propobj.focus();
                    }
                });
			}
			this.value=function(v, a){
				if(v===missing){
                    return propsysid;
				}
				else{
                    var single=false;
                    var caption="";
                    propsysid=__(v);
                    // Gestione modifica
                    propobj.modified(1);
                    _modifiedState(propname, true);
                    propobj.raisechanged();
                    if(a)
                        propobj.raiseassigned();
                    // Gestione visualizzazione
                    if(propmultiple){
                        if(propsysid.indexOf("|")>=0)
                            caption="<span style='color:silver;font-style:italic;'>(valori multipli)</span>";
                        else if(propsysid!="")
                            single=true;
                    }
                    else{
                        if(propsysid!="")
                            single=true;
                    }
                    // Visualizzazione
                    if(single){
                        $("#"+propname+"_text").html("<span style='color:silver;font-style:italic;'>Caricamento...</span>");
                        TAIL.enqueue(qv_queuehelpercall, {"id":propname, "table":proptable, "sysid":propsysid, "select":propselect, "assigned":a});
                        TAIL.wriggle();
                    }
                    else{
                        $("#"+propname+"_text").html(caption);
                    }
				}
			}
			this.name=function(){
				return propname;
			}
			this.title=function(v){
				if(v==missing)
					return proptitle;
				else
					proptitle=v;
			}
			this.table=function(v){
				if(v==missing)
					return proptable;
				else
					proptable=v;
			}
			this.where=function(v){
				if(v==missing)
					return propwhere;
				else
					propwhere=v;
			}
			this.args=function(v){
				if(v==missing)
					return propargs;
				else
					propargs=v;
			}
			this.orderby=function(v){
				if(v==missing)
					return proporderby;
				else
					proporderby=v;
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v;
					if(v){
						$("#"+propname+"_anchor").removeAttr("disabled");
						$("#"+propname+"_text").css({"color":"#000000","cursor":"text"});
						$("#"+propname+"_button").css({"cursor":"pointer"});
					}
					else{
						$("#"+propname+"_anchor").attr("disabled",true);
						$("#"+propname+"_text").css({"color":"gray","cursor":"default"});
						$("#"+propname+"_button").css({"cursor":"default"});
					}
				}
			}
			this.visible=function(v){
				if(v==missing){
					return propvisible;
				}
				else{
					propvisible=v;
					if(v)
						$("#"+propname).css({"visibility":"visible"});
					else
						$("#"+propname).css({"visibility":"hidden"});
				}
			}
			this.modified=function(v){
				if(v==missing)
					return __($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
			this.clear=function(){
                propsysid="";
                $("#"+propname+"_text").html("");
                propobj.modified(1);
                _modifiedState(propname, true);
                propobj.raisechanged();
                propobj.raiseassigned();
                propobj.raiseclear();
			}
			this.focus=function(){
				objectFocus(propname);
			}
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)};
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)};
            }
            this.raisechanged=function(){
                if(settings.changed!=missing){settings.changed(propobj)};
            }
            this.raiseassigned=function(){
                if(settings.assigned!=missing){settings.assigned(propobj)};
            }
            this.raiseclear=function(){
                if(settings.clear!=missing){settings.clear(propobj)};
            }
			return this;
		},
        ryselections:function(settings){
 			var propleft=20;
			var proptop=20;
            var propwidth=250;
            var propheight=200;
			var propobj=this;
            var proptitle="";
            var proptitlecode="";
			var propformid="";
            var propsubid="";
            var proptable="";
            var prophelptable="";
            var propwhere="";
            var propclause="";
            var propclausewhere="";
            var prophelpclause="";
            var proporderby="DESCRIPTION";
            var propparenttable="";
            var propparentfield="SYSID";
            var propselectedtable="";
            var propclasstable="";
            var propupward=0;
            var propchangerow=false;
            var propsolveid=false;
            var propparentid=""; 
			var propname=$(this).attr("id");

			if(settings.left!=missing){propleft=settings.left};
			if(settings.top!=missing){proptop=settings.top};
			if(settings.width!=missing){propwidth=settings.width};
			if(settings.height!=missing){propheight=settings.height};
            if(settings.title!=missing){proptitle=settings.title};
            if(settings.titlecode!=missing){proptitlecode=settings.titlecode};
            if(settings.formid!=missing){propformid=settings.formid};
            if(settings.subid!=missing){propsubid=settings.subid};
            if(settings.table!=missing){proptable=settings.table;prophelptable=proptable};
            if(settings.helptable!=missing){prophelptable=settings.helptable};
            if(settings.where!=missing){propwhere=settings.where};
            if(settings.orderby!=missing){proporderby=settings.orderby};
            if(settings.parenttable!=missing){propparenttable=settings.parenttable};
            if(settings.parentfield!=missing){propparentfield=settings.parentfield};
            if(settings.selectedtable!=missing){propselectedtable=settings.selectedtable};
            if(settings.classtable!=missing){propclasstable=settings.classtable}
            if(settings.upward!=missing){propupward=settings.upward}
            if(settings.changerow!=missing){propchangerow=settings.changerow};
            if(settings.solveid!=missing){propsolveid=settings.solveid};
            
            if(propwhere!=""){
                propwhere=" AND "+propwhere;
            }
            
            var actualid=propformid+"_"+propsubid;
            var prefix="#"+actualid;
            var h="";
            h+="<div id='"+actualid+"_oper_add' babelcode='REL_ADD'></div>";
            h+="<div id='"+actualid+"_oper_remove' babelcode='REL_REMOVE'></div>";
            h+="<div id='"+actualid+"_oper_empty' babelcode='REL_EMPTY'></div>";
            h+="<div id='"+actualid+"_gridsel'></div>";

            $("#"+propname)
            .css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight})
            .html(h);
            
            // AGGIUNGI SELEZIONE
            var oper_add=$(prefix+"_oper_add").rylabel({
                left:20,
                top:0,
                width:60,
                caption:"Aggiungi",
                formid:propformid,
                button:true,
                click:function(o){
                    QVR.RequestID(propformid, {
                        table:prophelptable, 
                        where:"SYSID NOT IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+propparentid+"')"+propwhere,
                        clause:prophelpclause,
                        classtable:propclasstable,
                        title:proptitle,
                        multiple:true,
                        onselect:function(d){
                            var ids=d["SYSID"];
                            $.post(_cambusaURL+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessionid,
                                    "env":_sessioninfo.environ,
                                    "function":"selections_add",
                                    "data":{
                                        "UPWARD":propupward,
                                        "PARENTTABLE":propparenttable,
                                        "PARENTFIELD":propparentfield,
                                        "PARENTID":propparentid,
                                        "SELECTEDTABLE":propselectedtable,
                                        "SELECTION":ids
                                    }
                                }, 
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0){
                                            gridsel.refresh();
                                        }
                                        winzTimeoutMess(propformid, v.success, v.message);
                                    }
                                    catch(e){
                                        winzClearMess(propformid);
                                        alert(d);
                                    }
                                }
                            );
                        }
                    });
                }
            });
            // RIMUOVI SELEZIONE
            var oper_remove=$(prefix+"_oper_remove").rylabel({
                left:100,
                top:0,
                width:60,
                caption:"Rimuovi",
                formid:propformid,
                button:true,
                click:function(o){
                    gridsel.selengage(
                        function(o, s){
                            $.post(_cambusaURL+"ryquiver/quiver.php", 
                                {
                                    "sessionid":_sessionid,
                                    "env":_sessioninfo.environ,
                                    "function":"selections_remove",
                                    "data":{
                                        "PARENTID":propparentid,
                                        "SELECTION":s
                                    }
                                }, 
                                function(d){
                                    try{
                                        var v=$.parseJSON(d);
                                        if(v.success>0){
                                            gridsel.refresh();
                                        }
                                        winzTimeoutMess(propformid, v.success, v.message);
                                    }
                                    catch(e){
                                        winzClearMess(propformid);
                                        alert(d);
                                    }
                                }
                            );
                        }
                    );
                }
            });
            // SVUOTA SELEZIONE
            var oper_empty=$(prefix+"_oper_empty").rylabel({
                left:180,
                top:0,
                width:60,
                caption:"Svuota",
                formid:propformid,
                button:true,
                click:function(o){
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_remove",
                            "data":{
                                "PARENTID":propparentid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    gridsel.refresh();
                                }
                                winzTimeoutMess(propformid, v.success, v.message);
                            }
                            catch(e){
                                winzClearMess(propformid);
                                alert(d);
                            }
                        }
                    );
                }
            });
            var gridsel=$(prefix+"_gridsel").ryque({
                left:0,
                top:30,
                width:propwidth,
                height:propheight,
                formid:propformid,
                numbered:false,
                checkable:true,
                environ:_sessioninfo.environ,
                from:proptable,
                orderby:proporderby,
                columns:[
                    {id:"DESCRIPTION", caption:proptitle, width:200, code:proptitlecode}
                ],
                changerow:function(o,i){
                    oper_remove.enabled(o.isselected());
                    oper_empty.enabled(o.count()>0);
                    if(propchangerow!==false){
                        propchangerow();
                    }
                    if(i>0){
                        o.solveid(i);
                    }
                },
                selchange:function(o, i){
                    oper_remove.enabled(o.isselected());
                },
                solveid:function(o, d){
                    oper_remove.enabled(1);
                    if(propsolveid!==false){
                        propsolveid(d);
                    }
                }
            });
			this.parentid=function(v, after){
                propparentid=v;
                oper_add.enabled(0);
                oper_remove.enabled(0);
                oper_empty.enabled(0);
                gridsel.clear();
                gridsel.enabled(0);
                if(v!=""){
                    gridsel.clause(propclause)
                    gridsel.where("SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+propparentid+"')"+propclausewhere);
                    gridsel.query({
                        ready:function(){
                            gridsel.enabled(1);
                            oper_add.enabled(1);
                            if(after!=missing)
                                after();
                        }
                    });
                }
                else{
                    if(after!=missing)
                        after();
                }
			}
			this.clear=function(){
                gridsel.clear();
			}
            this.setid=function(id){
                gridsel.search({
                        "where":("SYSID='"+id+"'")
                    },
                    function(d){
                        var ind=0;
                        try{
                            var v=$.parseJSON(d);
                            ind=v[0];
                        }
                        catch(e){}
                        gridsel.index(ind);
                    }
                );
            }
            this.where=function(q){
                propwhere=q;
                if(propwhere!=""){
                    propwhere=" AND "+propwhere;
                }
            }
            this.clause=function(jclause){
                propclause=jclause;
                propclausewhere="";
                if(typeof(propclause)=="object"){
                    for(n in propclause){
                        propclausewhere+=(" AND "+n+"='"+propclause[n]+"'");
                    }
                }
            }
			return this;
		}       
	});
})(jQuery);
$(document).ready(function(){
    RYWINZ.logoutcalls.push(function(done){
        $.post(_cambusaURL+"ryquiver/quiver.php", 
            {
                "sessionid":_sessionid,
                "env":_sessioninfo.environ,
                "function":"system_logout",
                "data":{
                    "SESSIONID":_sessionid
                }
            }, 
            function(d){
                if(done){done()}
            }
        );
    });
    RYBOX.babels({
        "MSG_NOSELECTION":"Nessun elemento selezionato",
        "BUTTON_CANCEL":"Annulla",
        "BUTTON_REFRESH":"Aggiorna",
        "BUTTON_RESET":"Pulisci",
        "BUTTON_OK":"OK",
        "DESCRIPTION":"Descrizione",
        "SEARCH":"Ricerca",
        "CLASS":"Classe",
        "HLP_SELCLASS":"Selezione classe"
    });
});
