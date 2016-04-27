/****************************************************************************
* Name:            rybox.js                                                 *
* Project:         Cambusa/ryBox                                            *
* Version:         1.69                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var clipdate=null;
var clipnumber=null;
var globaledittext=false;
var globalpropid=0;
var globalcontainer="body";
var globalcastfocus=false;
var globalobjs=new Object();
var globalcolorfocus="#FFF4E6";

(function($,missing) {
    $.extend(true,$.fn, {
        rydate:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=120;
			var propheight=22;
			var propday="__";
			var propmonth="__";
			var propyear="____";
			var propstart=0;
			var propfocusout=true;
            var propselected=false;
			var propctrl=false;
			var propshift=false;
            var propalt=false;
			var propobj=this;
            var propchanged=false;
			var propenabled=true;
			var propvisible=true;
            var prophelper=true;
            var proplink=null;
            var propdefault="";
            var propmousedown=false;
            var flaghelp=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="date";
            
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.defaultvalue!=missing){propdefault=settings.defaultvalue}
            if(settings.enabled!=missing){propenabled=settings.enabled}
            if(settings.visible!=missing){propvisible=settings.visible}
            if(settings.helper!=missing){prophelper=settings.helper}

            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            }
            if(settings.tag!=missing){this.tag=settings.tag}

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("rydate")
            .css({
                "position":"absolute",
                "left":propleft,
                "top":proptop,
                "width":propwidth,
                "height":propheight,
                "color":"transparent",
                "background-color":"silver",
                "border":"none",
                "font-family":"verdana,sans-serif",
                "font-size":"13px",
                "line-height":"17px",
                "cursor":"default"
            })
            .html("<input type='text' id='"+propname+"_anchor'><div id='"+propname+"_internal'></div><div id='"+propname+"_button'></div><div id='"+propname+"_calendar'></div><div id='"+propname+"_clear'></div>");

            $("#"+propname+"_internal")
            .css({"position":"absolute","left":1,"top":1,"width":propwidth-2,"height":propheight-2,"color":"#000000","background-color":"#FFFFFF","overflow":"hidden"})
            .html("<div id='"+propname+"_text'></div><div id='"+propname+"_cursor'></div><span id='"+propname+"_span'></span>");

            $("#"+propname+"_cursor").css({"position":"absolute","left":1,"top":1,"width":1,"height":propheight-4,"background-color":"#000000","visibility":"hidden"});
            $("#"+propname+"_text").css({"position":"absolute","cursor":"text","left":1,"top":1,"width":propwidth-23,"height":propheight-4,"overflow":"hidden"});
            $("#"+propname+"_span").css({"position":"absolute","visibility":"hidden"});
            $("#"+propname+"_button").css({"position":"absolute","cursor":"pointer","left":propwidth-20,"top":2,"width":18,"height":18,"background":"url("+_systeminfo.relative.cambusa+"rybox/images/helper.png)"});
            $("#"+propname+"_calendar").css({"position":"absolute","visibility":"hidden","left":0,"top":propheight});
            $("#"+propname+"_clear").css({"position":"absolute","z-index":10000,"cursor":"pointer","left":propwidth,"top":2,"width":18,"height":18,"display":"none","background":"url("+_systeminfo.relative.cambusa+"rybox/images/clear.png)"});
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_cursor").css({"visibility":"visible"});
            			$("#"+propname+"_internal").css({"background-color":globalcolorfocus});
            			if($("#"+propname+"_text").html()=="")
            				$("#"+propname+"_text").html("__/__/____");
                        propfocusout=false;
                        propchanged=false;
                        propobj.selected(true);
                        propstart=0;
                        propobj.refreshcursor();
                        propobj.raisegotfocus();
            		}
            	}
            );
            $("#"+propname+"_anchor").focusout(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_cursor").css({"visibility":"hidden"});
            			$("#"+propname+"_internal").css({"background-color":"#FFFFFF"});
            			propobj.completion();
            			if($("#"+propname+"_text").html()=="__/__/____")
            				$("#"+propname+"_text").html("");
                        propobj.selected(false);
            			if(propchanged)
                            propobj.raiseassigned();
                        propobj.raiselostfocus();
                        propfocusout=true;
            		}
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
                    if(_navigateKeys(k))  // Tasti usati in navigazione tabs
                        return true;
            		if(propenabled){
            			propctrl=k.ctrlKey; // da usare anche nella press
            			propshift=k.shiftKey;
                        propalt=k.altKey;
                        // GESTIONE CLIPBOARD
                        if(propctrl){
                            switch(k.keyCode){
                            case 88:
            					clipdate=propobj.value();
            					propobj.value("");
                                k.preventDefault();
                                return false;
                            case 67:
            					var v=propobj.value();
            					if(v)
            						clipdate=v;
                                k.preventDefault();
                                return false;
                            case 86:
            					propobj.value(clipdate);
                                k.preventDefault();
                                return false;
                            }
                        }
                        // GESTIONE ALTRI TASTI
            			if(k.which==39){ // right
                            if(propshift){
            					propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
            				else if(propstart<8){
            					if(propctrl){
            						switch(propstart){
            							case 0:case 1:
            								propstart=2;break;
            							case 2:case 3:
            								propstart=4;break;
            							default:
            								propstart=0;break;
            						}
            					}
            					else{
            						propstart+=1;
            					}
            					propobj.refreshcursor();
            				}
            			}
            			else if(k.which==37){ // left
                            if(propshift){
            					propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
            				else if(propstart>0){
            					if(propctrl){
            						switch(propstart){
            							case 0:case 1:
            								propstart=4;break;
            							case 2:case 3:
            								propstart=0;break;
            							default:
            								propstart=2;break;
            						}
            					}
            					else{
            						propstart-=1;
            					}
            					propobj.refreshcursor();
            				}
            			}
            			else if(k.which==36){ // home
                            if(propshift){
            					propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
            				else if(propstart>0){
            					propstart=0;
            					propobj.refreshcursor();
            				}
            			}
            			else if(k.which==35){ // end
                            if(propshift){
            					propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
            				else if(propstart<8){
            					propstart=8;
                                propobj.refreshcursor();
            				}
            			}
            			else if(k.which==46){ // delete
            				if(propctrl){
            					clipdate=propobj.value();
            					propobj.clear();
            				}
            				else{
                                if(propselected){
                                    propobj.clear();
                                    propobj.selected(false);
                                }
                                if(_sessioninfo.dateformat==1){
                                    // FORMATO US MM/DD/YYYY
                                    switch(propstart){
                                        case 0:case 1:
                                            propmonth="__";propstart=0;break;
                                        case 2:case 3:
                                            propday="__";propstart=2;break;
                                        default:
                                            propyear="____";propstart=4;break;
                                    }
                                }
                                else{
                                    // FORMATO PREDEFINITO DD/MM/YYYY
                                    switch(propstart){
                                        case 0:case 1:
                                            propday="__";propstart=0;break;
                                        case 2:case 3:
                                            propmonth="__";propstart=2;break;
                                        default:
                                            propyear="____";propstart=4;break;
                                    }
                                }
            				}
            				$("#"+propname+"_text").html(propobj.formatted());
            				propobj.refreshcursor();
                            propobj.raisechanged();
            			}
            			else if(k.which==45){ // ins
            				if(propctrl){
            					var v=propobj.value();
            					if(v)
            						clipdate=v;
            				}
            				else if(propshift){
            					propobj.value(clipdate);                    
            				}
                            else{
                                propobj.value(Date.stringToday());
                            }
            			}
            			else if(k.which==113 || (propalt && k.which==50)){ // F2  Alt+2
            				propobj.showcalendar();
            			}
            			else if(k.which==13){ // INVIO
            				propstart=0;
            				propobj.refreshcursor();
                            if(settings.changed!=missing){settings.changed(propobj)}
                            propchanged=false;
                            propobj.raiseassigned();
                            if(settings.enter!=missing){
                                settings.enter(propobj);
                            }
            			}
            			else if(k.which==27){ // ESCAPE
                            if(settings.escape!=missing){settings.escape(propobj)}
            			}
            			if(k.which==8){
                            if(propselected){
                                propobj.clear();
                                propobj.selected(false);
                            }
                            if(_sessioninfo.dateformat==1){
                                // FORMATO US MM/DD/YYYY
                                switch(propstart){
                                    case 0:case 1:case 2:
                                        propmonth="__";propstart=0;break;
                                    case 3:case 4:
                                        propday="__";propstart=2;break;
                                    default:
                                        propyear="____";propstart=4;break;
                                }
                            }
                            else{
                                // FORMATO PREDEFINITO DD/MM/YYYY
                                switch(propstart){
                                    case 0:case 1:case 2:
                                        propday="__";propstart=0;break;
                                    case 3:case 4:
                                        propmonth="__";propstart=2;break;
                                    default:
                                        propyear="____";propstart=4;break;
                                }
                            }
            				$("#"+propname+"_text").html(propobj.formatted());
            				propobj.refreshcursor();
                            propobj.raisechanged();
            			}
            		}
                    if(k.which>=35 && k.which<=39 && !propshift){
                        if(propselected){
                            propobj.selected(false);
                        }
                    }
            		if(k.which==8 || k.which==35 || k.which==36){
            			return false;
            		}
                    else if(k.which==9){
                        return nextFocus(propname, propshift);
                    }
            	}
            );
            $("#"+propname+"_anchor").keypress(
            	function(k){
                    if(_navigateKeys(k))  // Tasti usati in navigazione tabs
                        return true;
                    if(propalt)
                        return true;
            		if(propenabled){
            			var n=String.fromCharCode(k.which).toUpperCase();
            			if(propstart<8){
            				if("0"<=n && n<="9"){
                                if(propselected){
                                    propobj.clear();
                                    propobj.selected(false);
                                }
                                if(_sessioninfo.dateformat==1){
                                    // FORMATO US MM/DD/YYYY
                                    if( (propstart==2 && n<=3) || 
                                        (propstart==3 && ( (n<=1 && propday.substr(0,1)=="3") || propday.substr(0,1)!="3") ) || 
                                        (propstart==0 && n<=1) || 
                                        (propstart==1 && ( (n<=2 && propmonth.substr(0,1)=="1") || propmonth.substr(0,1)!="1") ) || 
                                        propstart>=4){
                                        switch(propstart){
                                            case 0:case 1:
                                                propmonth=propmonth.substr(0,propstart)+n+propmonth.substr(propstart+1);break;
                                            case 2:case 3:
                                                propday=propday.substr(0,propstart-2)+n+propday.substr(propstart-1);break;
                                            case 4:case 5:case 6:case 7:
                                                propyear=propyear.substr(0,propstart-4)+n+propyear.substr(propstart-3);break;
                                        }
                                        
                                        propstart+=1;
                                        
                                        $("#"+propname+"_text").html(propobj.formatted());
                                        propobj.refreshcursor();
                                        propobj.raisechanged();
                                    }
                                }
                                else{
                                    // FORMATO PREDEFINITO DD/MM/YYYY
                                    if( (propstart==0 && n<=3) || 
                                        (propstart==1 && ( (n<=1 && propday.substr(0,1)=="3") || propday.substr(0,1)!="3") ) || 
                                        (propstart==2 && n<=1) || 
                                        (propstart==3 && ( (n<=2 && propmonth.substr(0,1)=="1") || propmonth.substr(0,1)!="1") ) || 
                                        propstart>=4){
                                        switch(propstart){
                                            case 0:case 1:
                                                propday=propday.substr(0,propstart)+n+propday.substr(propstart+1);break;
                                            case 2:case 3:
                                                propmonth=propmonth.substr(0,propstart-2)+n+propmonth.substr(propstart-1);break;
                                            case 4:case 5:case 6:case 7:
                                                propyear=propyear.substr(0,propstart-4)+n+propyear.substr(propstart-3);break;
                                        }
                                        
                                        propstart+=1;
                                        
                                        $("#"+propname+"_text").html(propobj.formatted());
                                        propobj.refreshcursor();
                                        propobj.raisechanged();
                                    }
                                }
            				}
            			}
            		}
            	}
            );
            $("#"+propname+"_anchor").keyup(
            	function(k){
                    if(k.which!=9 && k.which!=16 && !( k.which>=35 && k.which<=39 && propshift)){
                        if(propselected){
                            propobj.selected(false);
                        }
                    }
                    // MANTENGO PULITO INPUT
                    $("#"+propname+"_anchor").val("");
                }
            );
            $("#"+propname+"_text").dblclick(
                function(){
                    if(propenabled)
                        propobj.selected(true);
                }
            );
            $("#"+propname+"_text").mousedown(
            	function(evt){
                    if(propselected){
                        propobj.selected(false);
                    }
            		if(propenabled){
            			var p=evt.pageX-propleft;
            			var l,i;
            			var t=propobj.formatted();
            			propstart=8;
            			for(i=1;i<=10;i++){
            				l=propobj.textwidth(t.substr(0,i));
            				if(l>p+3){
            					if(i>=7)
            						propstart=i-3;
            					else if(i>=4)
            						propstart=i-2;
            					else
            						propstart=i-1;
            					break;
            				}
            			}
            			propobj.refreshcursor();
            		}
            	}
            );
            $("#"+propname).mousedown(
            	function(evt){
            		if(propenabled){
                        propmousedown=true;
                        if(!propselected)
                            castFocus(propname);
            		}
            	}
            );
            $("#"+propname).mousemove(
            	function(evt){
            		if(propenabled){
                        if(propmousedown)
                            propobj.selected(true);
            		}
            	}
            );
            $("#"+propname).mouseup(
            	function(evt){
                    propmousedown=false;
            	}
            );
            $("#"+propname+"_button").mouseover(
            	function(evt){
                    if(!propobj.isempty() && propenabled)
                        $("#"+propname+"_clear").show();
            	}
            );
            $("#"+propname+"_clear").click(
            	function(evt){
                    propobj.value("", true);
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
                        if(!flaghelp){
                            propobj.showcalendar();
                            flaghelp=true;
                        }
                        else{
                            if($("#"+propname+"_text").html()=="__/__/____")
                                propobj.value(Date.stringToday(), true);
                            flaghelp=false;
                        }
            		}
            	}
            );
            $("#"+propname+"_text").contextMenu("rybox_popup", {
            	bindings: {
            		'rybox_cut': function(t) {
            			clipdate=propobj.value();
            			propobj.value("", true);
            		},
            		'rybox_copy': function(t) {
            			var v=propobj.value();
            			if(v)
            				clipdate=v;
            		},
            		'rybox_paste': function(t) {
            			propobj.value(clipdate, true);
            		}
            	},
            	onContextMenu:
            		function(e) {
            			if((clipdate==null && propobj.value()==null) || !propenabled)
            				return false;
            			else 
            				return true;
            		},
            	onShowMenu: 
            		function(e, menu) {
            			if(propobj.value()==null){
            				$('#rybox_cut', menu).remove();
            				$('#rybox_copy', menu).remove();
            			}
            			if(!clipdate){
            				$('#rybox_paste', menu).remove();
            			}
            			return menu;
            		}
            });
            // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth});
                $("#"+propname+"_internal").css({"width":propwidth-2});
                $("#"+propname+"_text").css({"width": (prophelper ? propwidth-25 : propwidth-5) });
                $("#"+propname+"_button").css({"position":"absolute","left":propwidth-20,"top":2});
            }
			this.showcalendar=function(r){
                if(prophelper){
                    var p=$("#"+propname).offset();
                    $("#"+propname+"_calendar").datepicker("dialog",propobj.value(),
                        function(dateText, inst){ 
                            propobj.value(new Date(dateText), true);
                            objectFocus(propname);
                        },
                        {
                            onClose:function(){
                                objectFocus(propname)
                                flaghelp=true;
                                setTimeout(function(){
                                    flaghelp=false;
                                }, 500);
                            }
                        },
                        [p.left,p.top+propheight]
                    );
                }
			}
			this.refreshcursor=function(){
				var t=propobj.formatted();
				var i=propstart;
				if(propstart>=4)
					i+=2;
				else if(propstart>=2)
					i+=1;
				var s=t.substr(0,i);
				$("#"+propname+"_cursor").css({"left":propobj.textwidth(s)+1})
			}
			this.textwidth=function(s){
				$("#"+propname+"_span").html(s);
				return $("#"+propname+"_span").width();
			}
			this.formatted=function(){
                if(_sessioninfo.dateformat==1){
                    // FORMATO US MM/DD/YYYY
                    return ("__"+propmonth).subright(2)+"/"+("__"+propday).subright(2)+"/"+("____"+propyear).subright(4);
                }
                else{
                    // FORMATO PREDEFINITO DD/MM/YYYY
                    return ("__"+propday).subright(2)+"/"+("__"+propmonth).subright(2)+"/"+("____"+propyear).subright(4);
                }
			}
			this.completion=function(){
				if(propday!="__" || propmonth!="__" || propyear!="____"){
					var d=propday.replace(/_/g,"");
					var m=propmonth.replace(/_/g,"");
					var y=propyear.replace(/_/g,"");
					if(d.length!=2||m.length!=2||y.length!=4)
                        propobj.raisechanged();
					
					var cd=new Date();
						
					if(d.length==0)
						d=cd.getDate();
					propday=("00"+d).subright(2);
					
					if(m.length==0)
						m=cd.getMonth()+1;
					propmonth=("00"+m).subright(2);
					
					switch(y.length){
						case 0:
							y=""+cd.getFullYear();break;
						case 1:
							y="200"+y;break;
						case 2:
							y="20"+y;break;
						case 3:
							y="2"+y;break;
					}
					propyear=y;

					if(!validateDate(propday,propmonth,propyear)){
						propobj.clear();
                        propobj.raiseexception();
					}
					$("#"+propname+"_text").html(propobj.formatted());
				}
			}
			this.value=function(v,a){
				if(v==missing){
					propobj.completion();
					if(propday!="__"){
                        if(proplink)
                            return (new Date( propyear, propmonth-1, propday, proplink.hours(), proplink.minutes() ));
                        else
                            return (new Date( propyear, propmonth-1, propday ));
                    }
					else{
						return null;
                    }
				}
				else{
					try{
						if(v!=""){
                            if(typeof v=="string"){
                                v=v.replace(/[-T :]/g, "");
                                propday=v.substr(6,2);
                                propmonth=v.substr(4,2);
                                propyear=v.substr(0,4);
                                if(proplink){
                                    proplink.value(v);
                                }
                                if( (propday=="01" && propmonth=="01" && propyear=="1900") || (propday=="31" && propmonth=="12" && propyear=="9999") ){
                                    propobj.clear();
                                }
                            }
                            else{
                                propday=("00"+v.getDate()).subright(2);
                                propmonth=("00"+(v.getMonth()+1)).subright(2);
                                propyear=("0000"+v.getFullYear()).subright(4);
                                if(proplink){
                                    proplink.value(v);
                                }
                            }
						}
						else{
							propobj.clear();
						}
                        propobj.raisechanged();
                        propchanged=false;
                        if(a==missing){a=false}
                        if(a){propobj.raiseassigned()}
					}
					catch(e){
						propobj.clear();
					}
					$("#"+propname+"_text").html(propobj.formatted());
					propstart=0;
					propobj.refreshcursor();
				}
			}
			this.text=function(def){
                propobj.completion();
                if(propday!="__"){
                    var r=propyear+propmonth+propday;
                    if(proplink){
                        if(proplink.visible()){
                            r+=proplink.text();
                        }
                    }
                    return r;
                }
                else{
                    if(def==missing)
                        return propdefault;
                    else
                        return def;
                }
            }
            this.isempty=function(){
                return (propday=="__" && propmonth=="__" && propyear=="____");
            }
			this.name=function(){
				return propname;
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
						if(propfocusout==false){
							$("#"+propname+"_cursor").css({"visibility":"visible"});
							propobj.refreshcursor();
						}
					}
					else{
						$("#"+propname+"_anchor").attr("disabled",true);
						$("#"+propname+"_text").css({"color":"gray","cursor":"default"});
						$("#"+propname+"_button").css({"cursor":"default"});
						$("#"+propname+"_cursor").css({"visibility":"hidden"});
					}
                    if(proplink){
                        proplink.enabled(v);
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
                    if(proplink){
                        proplink.visible(v);
                    }
				}
			}
            this.helper=function(v){
                if(v!=missing){
                    prophelper=v.actualBoolean();
                    if(prophelper){
                        $("#"+propname+"_text").css({"width":propwidth-25});
                        $("#"+propname+"_button").css({"display":"block"});
                    }
                    else{
                        $("#"+propname+"_text").css({"width":propwidth-5});
                        $("#"+propname+"_button").css({"display":"none"});
                    }
                }
                return prophelper;
            }
			this.changed=function(v){
				if(v==missing)
					return propchanged;
				else
					propchanged=v;
			}
			this.modified=function(v){
				if(v==missing)
					return ($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
            this.selected=function(v){
                propselected=v;
                propstart=0;
                propobj.refreshcursor();
                if($("#"+propname+"_text").html()=="__/__/____")
                    propselected=false;
                if(propselected)
                    $("#"+propname+"_text").css({"background-color":"#87CEFA", "color":"white"});
                else
                    $("#"+propname+"_text").css({"background-color":"transparent", "color":"black"});
            }
			this.clear=function(){
				propstart=0;
				propday="__";
				propmonth="__";
				propyear="____";
                $("#"+propname+"_text").html(propobj.formatted());
                propobj.refreshcursor();
                if(proplink){
                    proplink.clear();
                }
                propobj.raisechanged();
			}
			this.focus=function(){
				objectFocus(propname);
			}
            this.link=function(objtime){
                if(proplink){
                    proplink.link(null);    // Tolgo il vecchio legame inverso
                }
                proplink=objtime;
                proplink.link(propobj);
            }
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)}
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)}
            }
            this.raisechanged=function(){
                propchanged=true;
                propobj.modified(1);
                if(settings.changed!=missing){settings.changed(propobj)}
                _modifiedState(propname,true);
            }
            this.raiseassigned=function(){
                propobj.modified(1);
                if(settings.assigned!=missing){settings.assigned(propobj)}
                propchanged=false;
            }
            this.raiseexception=function(){
                if(settings.exception!=missing){settings.exception(propobj)}
            }
            if(!propenabled)
                propobj.enabled(0);
            if(!propvisible)
                propobj.visible(0);
            if(!prophelper)
                propobj.helper(0);
			return this;
		}
	},{
		rynumber:function(settings){
			var propleft=200;
			var proptop=200;
			var propwidth=120;
			var propheight=22;
			var propinteger="0";
			var propdecimal="00";
			var propsignum="";
			var propnumdec=2;
			var propstart=0;
			var propfocusout=true;
            var propselected=false;
			var propctrl=false;
			var propshift=false;
            var propalt=false;
			var propminvalue=0;
			var propmaxvalue=9999999999999.99;
			var propobj=this;
			var propchanged=false;
			var propenabled=true;
			var propvisible=true;
            var prophelper=true;
            var propincremental=true;
            var propmousedown=false;
            var flaghelp=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="number";
            
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.width!=missing){propwidth=settings.width}
            if(settings.numdec!=missing){propnumdec=settings.numdec}
            if(settings.minvalue!=missing){propminvalue=settings.minvalue}
            if(settings.maxvalue!=missing){propmaxvalue=settings.maxvalue}
            if(settings.enabled!=missing){propenabled=settings.enabled}
            if(settings.visible!=missing){propvisible=settings.visible}
            if(settings.helper!=missing){prophelper=settings.helper}
            if(settings.incremental!=missing){propincremental=settings.incremental}
			
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            }
            if(settings.tag!=missing){this.tag=settings.tag}
            
            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("rynumber")
            .css({
                "position":"absolute",
                "left":propleft,
                "top":proptop,
                "width":propwidth,
                "height":propheight,
                "color":"transparent",
                "background-color":"silver",
                "border":"none",
                "font-family":"verdana,sans-serif",
                "font-size":"13px",
                "line-height":"17px",
                "cursor":"default"
            })
            .html("<input type='text' id='"+propname+"_anchor'><div id='"+propname+"_internal'></div><div id='"+propname+"_button'></div><div id='"+propname+"_clear'></div>");
            
            $("#"+propname+"_anchor").css({"position":"absolute","left":1,"top":1,"width":1,"height":1,"color":"#000000","background-color":"#FFFFFF","overflow":"hidden"});
            $("#"+propname+"_internal").css({"position":"absolute","left":1,"top":1,"width":propwidth-2,"height":propheight-2,"color":"#000000","background-color":"#FFFFFF","overflow":"hidden"});
            $("#"+propname+"_internal").html("<div id='"+propname+"_text'></div><div id='"+propname+"_cursor'></div><span id='"+propname+"_span'></span>");
            $("#"+propname+"_cursor").css({"position":"absolute","left":1,"top":1,"width":1,"height":propheight-4,"background-color":"#000000","visibility":"hidden"});
            $("#"+propname+"_text").css({"position":"absolute","cursor":"text","left":1,"top":1,"width":propwidth-24,"height":propheight-4,"text-align":"right","padding-right":1,"overflow":"hidden"});
            $("#"+propname+"_span").css({"position":"absolute","visibility":"hidden"});
            $("#"+propname+"_button").css({"position":"absolute","cursor":"pointer","left":propwidth-20,"top":2,"width":18,"height":18,"background":"url("+_systeminfo.relative.cambusa+"rybox/images/helper.png)"});
            $("#"+propname+"_clear").css({"position":"absolute","z-index":10000,"cursor":"pointer","left":propwidth,"top":2,"width":18,"height":18,"display":"none","background":"url("+_systeminfo.relative.cambusa+"rybox/images/clear.png)"});
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_cursor").css({"visibility":"visible"});
            			$("#"+propname+"_internal").css({"background-color":globalcolorfocus});
            			if($("#"+propname+"_text").html()=="")
            				$("#"+propname+"_text").html(propobj.formatted());
                        propfocusout=false;
                        propchanged=false;
                        propobj.selected(true);
                        propstart=0;
                        propobj.refreshcursor();
                        propobj.raisegotfocus();
            		}
            	}
            );
            $("#"+propname+"_anchor").focusout(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_cursor").css({"visibility":"hidden"});
            			$("#"+propname+"_internal").css({"background-color":"#FFFFFF"});
            			propobj.completion();
            			if(propobj.value()==0)
            				$("#"+propname+"_text").html("");
                        propobj.selected(false);
            			if(propchanged)
                            propobj.raiseassigned();
                        propobj.raiselostfocus();
                        propfocusout=true;
            		}
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
                    if(_navigateKeys(k))  // Tasti usati in navigazione tabs
                        return true;
            		if(propenabled){
            			propctrl=k.ctrlKey; // da usare anche nella press
            			propshift=k.shiftKey;
                        propalt=k.altKey;
                        // GESTIONE CLIPBOARD
                        if(propctrl){
                            switch(k.keyCode){
                            case 88:
                                clipnumber=propobj.value();
                                propobj.value(0);
                                k.preventDefault();
                                return false;
                            case 67:
                                var v=propobj.value();
                                if(v)
                                    clipnumber=v;
                                k.preventDefault();
                                return false;
                            case 86:
                                propobj.value(clipnumber);
                                k.preventDefault();
                                return false;
                            }
                        }
                        // GESTIONE ALTRI TASTI
            			if(k.which==39){ // right
                            if(propshift){
                                propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
            				else if( (propnumdec>0 && propstart<=propnumdec) || propstart<0 ){
            					propstart+=1;
            					propobj.refreshcursor();
            				}
            			}
            			else if(k.which==37){ // left
                            if(propshift){
                                propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
            				else if(propstart>0){
            					if(propctrl)
            						propstart=0;
            					else
            						propstart-=1;
            					propobj.refreshcursor();
            				}
                            else{
                                if(propinteger.length>-propstart){
                                    propstart-=1;
                                    propobj.refreshcursor();
                                }
                            }
            			}
                        else if(k.which==38){ // up
                            if(propincremental){
                                if(propstart==0){
                                    var u=parseFloat(propobj.value());
                                    if(u+1<=propmaxvalue)
                                        propobj.value(u+1);
                                }
                                else if(propstart>0){
                                    var u=parseInt(propdecimal.substr(propstart-1, 1));
                                    if(u<9){
                                        propdecimal=propdecimal.substr(0,propstart-1)+(u+1)+propdecimal.substr(propstart);
                                    }
                                }
                                else{
                                    var u=parseInt(propinteger.substr(propinteger.length+propstart,1));
                                    if(u<9){
                                        propinteger=propinteger.substr(0,propinteger.length+propstart)+(u+1)+propinteger.substr(propinteger.length+propstart+1);
                                    }
                                }
                                propobj.refresh();
                                propobj.raisechanged();
                            }
                        }
                        else if(k.which==40){ // down
                            if(propincremental){
                                if(propstart==0){
                                    var u=parseFloat(propobj.value());
                                    if(u-1>=propminvalue)
                                        propobj.value(u-1);
                                }
                                else if(propstart>0){
                                    var u=parseInt(propdecimal.substr(propstart-1, 1));
                                    if(u>0){
                                        propdecimal=propdecimal.substr(0,propstart-1)+(u-1)+propdecimal.substr(propstart);
                                    }
                                }
                                else{
                                    var u=parseInt(propinteger.substr(propinteger.length+propstart,1));
                                    if( (u>0 && -propstart<propinteger.length) || (u>1) ){
                                        propinteger=propinteger.substr(0,propinteger.length+propstart)+(u-1)+propinteger.substr(propinteger.length+propstart+1);
                                    }
                                }
                                propobj.refresh();
                                propobj.raisechanged();
                            }
                        }
            			else if(k.which==36){ // home
                             if(propshift){
            					propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
                            else if(propctrl){
                                propstart=0;
                                propobj.refreshcursor();
                            }
                            else{
                                if(propstart<=0){
                                    if(propinteger!="0"){
                                        propstart=-propinteger.length;
                                        propobj.refreshcursor();
                                    }
                                }
                                else if(propnumdec>0){
                                    propstart=1;
                                    propobj.refreshcursor();
                                }
                            }
            			}
            			else if(k.which==35){ // end
                            if(propshift){
            					propstart=0;
                                propobj.selected(true);
                                propobj.refreshcursor();
                            }
                            else if(propstart<=0){
                                propstart=0;
                                propobj.refreshcursor();
                            }
            				else if(propnumdec>0 && propstart<=propnumdec){
            					propstart=propnumdec+1;
                                propobj.refreshcursor();
            				}
            			}
            			else if(k.which==46){ // delete
            				if(propctrl){
            					clipnumber=propobj.value();
            					propobj.clear();
            					propobj.refresh();
            				}
            				else{
                                if(propselected){
                                    propobj.clear();
                                    propobj.selected(false);
                                }
                                if(propstart<0)
                                    propstart+=1;
            					propobj.delmanage();
            				}
            			}
            			else if(k.which==45){ // ins
            				if(propctrl){
            					var v=propobj.value();
            					if(v)
            						clipnumber=v;
            				}
            				else if(propshift){
            					propobj.value(clipnumber);                    
            				}
            			}
            			else if(k.which==113 || (propalt && k.which==50)){ // F2  Alt+2
            				propobj.showcalculator();
            			}
            			else if(k.which==13){ // INVIO
            				propobj.completion();
                            propobj.raiseassigned();
                            propobj.raiseenter();
            			}
            			else if(k.which==27){ // ESCAPE
                            if(settings.escape!=missing){settings.escape(propobj)}
            			}
            			else if(k.which==8){
                            if(propselected){
                                propobj.clear();
                                propobj.selected(false);
                            }
            				propobj.delmanage();
            			}
            		}
                    if(k.which>=35 && k.which<=39 && !propshift){
                        if(propselected){
                            propobj.selected(false);
                        }
                    }
            		if(k.which==8 || k.which==35 || k.which==36){
            			return false;
            		}
                    else if(k.which==9){
                        return nextFocus(propname, propshift);
                    }
            	}
            );
            $("#"+propname+"_anchor").keypress(
            	function(k){
                    if(_navigateKeys(k))  // Tasti usati in navigazione tabs
                        return true;
                    if(propalt)
                        return true;
            		if(propenabled){
            			var n=String.fromCharCode(k.which).toUpperCase();
            			if(propstart<=propnumdec){
            				if("0"<=n && n<="9"){
                                if(propselected){
                                    propobj.clear();
                                    propobj.selected(false);
                                }
            					if(propstart>0){
            						propdecimal=propdecimal.substr(0,propstart-1)+n+propdecimal.substr(propstart)
            						propstart+=1;
            					}
            					else{
            						if(propinteger=="0"){
            							propinteger=n;
                                    }
            						else if(propinteger.length<=12){
                                        if(propstart==0){
                                            propinteger+=n;
                                        }
                                        else{
                                            propinteger=propinteger.substr(0,propinteger.length+propstart)+n+propinteger.substr(propinteger.length+propstart);
                                            propstart-=1;
                                        }
                                    }
            					}
            					propobj.refresh();
            					propobj.raisechanged();
            				}
            			}
            			if(n=="-" && propminvalue<0){
            				if(propsignum=="")
            					propsignum="-";
            				else
            					propsignum="";
            				propobj.refresh();
            			}
            			else if(n=="." || n==","){
            				if(propnumdec>0){
            					if(propstart>0)
            						propstart=0;
            					else
            						propstart=1;
            					propobj.refreshcursor();
            				}
            			}
            		}
            	}
            );
            $("#"+propname+"_anchor").keyup(
            	function(k){
                    if(k.which!=9 && k.which!=16 && !( k.which>=35 && k.which<=39 && propshift)){
                        if(propselected){
                            propobj.selected(false);
                        }
                    }
                    // MANTENGO PULITO INPUT
                    $("#"+propname+"_anchor").val("");
                }
            );
            $("#"+propname+"_text").dblclick(
                function(){
                    if(propenabled)
                        propobj.selected(true);
                }
            );
            $("#"+propname+"_text").mousedown(
            	function(evt){
                    if(propselected){
                        propobj.selected(false);
                    }
            		if(propenabled){
            			propstart=0;
            			var p=evt.pageX-propleft;
            			var l,i;
                        var t=propobj.formatted();
                        t=t.replace(/&#x02D9;/g, ".");
                        var x=propwidth-propobj.textwidth(t)-23;
                        var j=-propinteger.length;
                        for(i=1;i<=t.length;i++){
                            l=propobj.textwidth(t.substr(0,i));
                            if(l+x>p+3){
                                propstart=j;
                                break;
                            }
                            switch(t.substr(i,1)){
                            case ".":case "-":
                                break;
                            default:
                                j+=1;
                            }
                        }
                        propobj.refreshcursor();
            		}
            	}
            );
            $("#"+propname).mousedown(
            	function(evt){
            		if(propenabled){
                        propmousedown=true;
                        if(!propselected || flaghelp)
                            castFocus(propname);
            		}
            	}
            );
            $("#"+propname).mousemove(
            	function(evt){
            		if(propenabled){
                        if(propmousedown)
                            propobj.selected(true);
            		}
            	}
            );
            $("#"+propname).mouseup(
            	function(evt){
                    propmousedown=false;
            	}
            );
            $("#"+propname+"_button").mouseover(
            	function(evt){
                    if(!propobj.isempty() && propenabled)
                        $("#"+propname+"_clear").show();
            	}
            );
            $("#"+propname+"_clear").click(
            	function(evt){
                    propobj.value(0, true);
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
                        if(!flaghelp){
                            propobj.showcalculator();
                            flaghelp=true;
                        }
                        else{
                            acceptvalue();
                            flaghelp=false;
                        }
            		}
            	}
            );
            $("#"+propname+"_text").contextMenu("rybox_popup", {
            	bindings: {
            		'rybox_cut': function(t) {
            			clipnumber=propobj.value();
            			propobj.value(0, true);
            		},
            		'rybox_copy': function(t) {
            			var v=propobj.value();
            			if(v)
            				clipnumber=v;
            		},
            		'rybox_paste': function(t) {
            			propobj.value(clipnumber, true);
            		}
            	},
            	onContextMenu:
            		function(e) {
            			if((clipnumber==null && propobj.value()==0) || !propenabled)
            				return false;
            			else 
            				return true;
            		},
            	onShowMenu: 
            		function(e, menu) {
            			if(propobj.value()==0){
            				$('#rybox_cut', menu).remove();
            				$('#rybox_copy', menu).remove();
            			}
            			if(!clipnumber){
            				$('#rybox_paste', menu).remove();
            			}
            			return menu;
            		}
            });
            // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth});
                $("#"+propname+"_internal").css({"width":propwidth-2});
                $("#"+propname+"_text").css({"width": (prophelper ? propwidth-25 : propwidth-5) });
                $("#"+propname+"_button").css({"position":"absolute","left":propwidth-20,"top":2});
            }
			this.showcalculator=function(r){
                if(prophelper){
                    if($.browser.mobile){
                        var v=prompt("Inserire un valore o una formula");
                        flaghelp=false;
                        if(typeof(v)=="string"){
                            v=v.replace(",", ".");
                            v=v.replace(/[^0-9.+\-*\/\(\)]/g, "");
                            v=eval( v );
                            v=__(v).stringNumber();
                            propobj.value(v, true);
                        }
                    }
                    else{
                        var p=$("#"+propname).offset();
                        var v=propobj.value();
                        if(v==0)
                            v="";
                        var code=0;
                        $("#rybox_calculator").html("<input id='rybox_calculator_input' type='text' value='"+v+"' placeholder='23*(42-7)'>");
                        $("#rybox_calculator").css({border:"1px solid silver", left:p.left, top:p.top+propheight, width:200, "zIndex":10000});
                        $("#rybox_calculator_input").css({width:196,"font-family":"verdana,sans-serif"});
                        $("#rybox_calculator_input").focusin(
                            function(){
                                globaledittext=true;
                            }
                        );
                        $("#rybox_calculator_input").focusout(
                            function(){
                                globaledittext=false;
                                $("#rybox_calculator").hide();
                                $("#rybox_calculator").empty();
                                flaghelp=true;
                                setTimeout(function(){
                                    flaghelp=false;
                                }, 500);
                            }
                        );
                        $("#rybox_calculator_input").keydown(
                            function(k){
                                code=k.which;
                                propctrl=k.ctrlKey;
                                var n=String.fromCharCode(k.which).toUpperCase();
                                if(k.which==13){ // INVIO
                                    acceptvalue();
                                }
                                else if(k.which==27){ // ESC
                                    objectFocus(propname);
                                }
                            }
                        );
                        $("#rybox_calculator_input").keypress(
                            function(k){
                                var n=String.fromCharCode(k.which).toUpperCase();
                                switch(n){
                                case "0":case "1":case "2":case "3":case "4":case "5":case "6":case "7":case "8":case "9":
                                case ".":case "(":case ")":case "+":case "-":case "*":case "/":
                                    break;
                                case "X":case "C":case "V":
                                    if(!propctrl)
                                        return false;
                                    break;
                                default:
                                    switch(code){
                                    case 35:case 36:case 37:case 39:case 45:case 46:case 8:
                                        break;
                                    default:
                                        return false;
                                    }
                                }
                            }
                        );
                        $("#rybox_calculator").show();
                        setTimeout(function(){ 
                            var o=document.getElementById("rybox_calculator_input");
                            o.focus();
                            o.select(); 
                        }, 100);                
                    }
                }
			}
			this.minvalue=function(v){
				if(v==missing)
					return propminvalue;
				else
					propminvalue=v;
			}
			this.maxvalue=function(v){
				if(v==missing)
					return propmaxvalue;
				else
					propmaxvalue=v;
			}
			this.numdec=function(d){
				if(d==missing)
					return propnumdec;
				else{
					propnumdec=d;
					propdecimal+=propobj.zerofill();
					propdecimal=propdecimal.substr(0,propnumdec);
                    propobj.refresh();
				}
			}
			this.delmanage=function(){
				if(propstart==0){
					if(propinteger!="0"){
						propinteger=propinteger.substr(0,propinteger.length-1);
						if(propinteger==""){
							propsignum="";
							propinteger="0";
						}
					}
				}
                else if(propstart<0){
                    propinteger=propinteger.substr(0,propinteger.length+propstart-1)+propinteger.substr(propinteger.length+propstart);
                    if(-propstart>propinteger.length)
                        propstart+=1;
                }
				else{
					propdecimal=propobj.zerofill();
					propstart=1;
				}
				propobj.refresh();
				propobj.raisechanged();
			}
			this.refreshcursor=function(){
				var s;
                var d="";
                var r=(prophelper ? 25 : 5);
                if(propstart<0){
                    for(var i=0; i<-propstart; i++){
                        d+=propinteger.substr(propinteger.length-i-1,1);
                        if((i%3)==0 && i>0)
                            d+="&#x02D9;";
                    }
                }
				if(propstart>0)
					s=propdecimal.substr(propstart-1);
				else if(propnumdec>0)
					s=d+","+propdecimal;
				else
					s=d;
                $("#"+propname+"_cursor").css({"left":(propwidth-propobj.textwidth(s)-r)})
			}
			this.refresh=function(){
				$("#"+propname+"_text").html(propobj.formatted());
				propobj.refreshcursor();
			}
			this.textwidth=function(s){
				$("#"+propname+"_span").html(s);
				return $("#"+propname+"_span").width();
			}
			this.formatted=function(){
				var f,p;
				var s=propinteger;
				if(propnumdec>0){
					s+="."+propdecimal;
                    f=s.formatNumber(propnumdec);
				}
				else{
					f=s;
					p=f.length;
				}
					
				for(var i=p-3;i>0;i-=3)
					f=f.substr(0,i)+"&#x02D9;"+f.substr(i);
				
				return propsignum+f;
			}
			this.completion=function(){
				var s=propsignum+propinteger;
				if(propnumdec>0)
					s+="."+propdecimal;
                var v=s.actualNumber();
                if(v<propminvalue || v>propmaxvalue){
					propobj.value(v, true);
                    propobj.raiseexception();
				}    
			}
			this.value=function(v,a){
				if(v==missing){
					propobj.completion();
					var s=propsignum+propinteger;
					if(propnumdec>0)
						s+="."+propdecimal;
                    // mosca: prima era stringa
                    return parseFloat(s);
				}
				else{
                    v=__(v).actualNumber();
					if(v<propminvalue)
						v=propminvalue;
					else if(v>propmaxvalue)
						v=propmaxvalue;
					
					if(v<0)
						propsignum="-";
					else
						propsignum="";

					v=Math.abs(v);
					propinteger=Math.floor(v).toString();
					propdecimal=(Math.round((Math.pow(10,propnumdec))*(v%1) ).toString()).substr(0,propnumdec);
					propdecimal=(propobj.zerofill()+propdecimal).subright(propnumdec);
					propstart=0;
					propobj.refresh();
					propobj.raisechanged();
                    propchanged=false;
                    if(a==missing){a=false}
                    if(a){propobj.raiseassigned()}
				}
			}
            this.text=function(){
                propobj.completion();
                var s=propsignum+propinteger;
                if(propnumdec>0)
                    s+="."+propdecimal;
                return s;
            }
            this.isempty=function(){
                return (propinteger.actualInteger()==0 && propdecimal.actualInteger()==0);
            }
			this.name=function(){
				return propname;
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
						if(propfocusout==false){
							$("#"+propname+"_cursor").css({"visibility":"visible"});
							propobj.refreshcursor();
						}
					}
					else{
						$("#"+propname+"_anchor").attr("disabled",true);
						$("#"+propname+"_text").css({"color":"gray","cursor":"default"});
                        $("#"+propname+"_button").css({"cursor":"default"});
						$("#"+propname+"_cursor").css({"visibility":"hidden"});
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
            this.helper=function(v){
                if(v!=missing){
                    prophelper=v.actualBoolean();
                    if(prophelper){
                        $("#"+propname+"_text").css({"width":propwidth-25});
                        $("#"+propname+"_button").css({"display":"block"});
                    }
                    else{
                        $("#"+propname+"_text").css({"width":propwidth-5});
                        $("#"+propname+"_button").css({"display":"none"});
                    }
                }
                return prophelper;
            }
			this.incremental=function(v){
				if(v!=missing)
					propincremental=v;
                return propincremental;
			}
			this.changed=function(v){
				if(v==missing)
					return propchanged;
				else
					propchanged=v;
			}
			this.modified=function(v){
				if(v==missing)
					return ($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
            this.selected=function(v){
                propselected=v;
                propstart=0;
                propobj.refreshcursor();
                if(propselected)
                    $("#"+propname+"_text").css({"background-color":"#87CEFA", "color":"white"});
                else
                    $("#"+propname+"_text").css({"background-color":"transparent", "color":"black"});
            }
			this.clear=function(){
				propstart=0;
				propsignum="";
				propinteger="0";
				propdecimal=propobj.zerofill();
                propobj.refresh();
				propobj.raisechanged();
			}
			this.zerofill=function(){
				var i,r="";
				for(i=1;i<=propnumdec;i++)
					r+="0";
				return r;
			}
			this.focus=function(){
				objectFocus(propname);
			}
            this.raisegotfocus=function(){
                propchanged=false;
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)}
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)}
            }
            this.raisechanged=function(){
                propchanged=true;
                propobj.modified(1);
                if(settings.changed!=missing){settings.changed(propobj)}
                _modifiedState(propname,true);
            }
            this.raiseassigned=function(){
                propobj.modified(1);
                if(settings.assigned!=missing){settings.assigned(propobj)}
                propchanged=false;
            }
            this.raiseenter=function(){
                if(settings.enter!=missing){settings.enter(propobj)}
            }
            this.raiseexception=function(){
                if(settings.exception!=missing){settings.exception(propobj)}
            }
            function acceptvalue(){
                var v=0;
                try{
                    v=$("#rybox_calculator_input").val();
                    if($.isset(v)){
                        v=v.replace(",", ".");
                        v=v.replace(/[^0-9.+\-*\/\(\)]/g, "");
                        v=eval( v );
                        v=__(v).stringNumber();
                    }
                }catch(e){
                    if(window.console)console.log(e.message);
                    v=0;
                }
                propobj.value(v, true);
                objectFocus(propname);
            }
            if(!propenabled)
                propobj.enabled(0);
            if(!propvisible)
                propobj.visible(0);
            if(!prophelper)
                propobj.helper(0);
            if(propnumdec!=2)
                propdecimal=propobj.zerofill();
			return this;
		}
	},{
		rytext:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=200;
			var propheight=22;
			var propmaxlen=255;
            var propinput="text";
            var propfilter="";
            var propfocusout=true;
			var propobj=this;
			var propchanged=false;
            var propchangedfalse=false; // Comando per abbassare changed dopo INVIO
			var propenabled=true;
			var propvisible=true;
            
            var firstup=true;
            
            var timerhandle=false;
            var timerbusy=false;
            var timertry=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="text";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.width!=missing){propwidth=settings.width}
            if(settings.maxlen!=missing){propmaxlen=settings.maxlen}
            if(settings.password){propinput="password"}
            if(settings.enabled!=missing){propenabled=settings.enabled}
            if(settings.visible!=missing){propvisible=settings.visible}

            if(settings.filter!=missing){
                propfilter=settings.filter.toUpperCase();
                propfilter=propfilter.replace(/A-Z/,"ABCDEFGHIJKMNOPQRSTUVWXYZ");
                propfilter=propfilter.replace(/0-9/,"0123456789");
            }
            
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            }
            if(settings.tag!=missing){this.tag=settings.tag}

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("rytext")
            .css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth-2,"height":propheight-2,"background-color":"white","border":"1px solid silver","overflow":"hidden"})
            .html("<input id='"+propname+"_anchor' type='"+propinput+"' maxlength='"+propmaxlen+"'>");
            $("#"+propname+"_anchor").css({"cursor":"text"});
            
            var t=0;
            if($.browser.HTML5)
                t=-1;
            $("#"+propname+"_anchor").css({"position":"absolute","left":1,"top":t,"width":propwidth-4,"height":propheight-2,"border":"none","background-color":"#FFFFFF","font-family":"verdana,sans-serif","font-size":"13px","outline":"none"});
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		globaledittext=true;
            		$("#"+propname+"_anchor").css({"background-color":globalcolorfocus});
                    $("#"+propname+"_anchor").select();
                    propchanged=false;
                    propfocusout=false;
                    timerbusy=false;
                    timertry=false;
                    firstup=true;
                    propobj.raisegotfocus();
            	}
            );
            $("#"+propname+"_anchor").mouseup(function(){
                if(firstup){
                    firstup=false;
                    return false;
                }
            });
            $("#"+propname+"_anchor").focusout(
            	function(){
            		globaledittext=false;
            		$("#"+propname+"_anchor").css({"background-color":"#FFFFFF"});
                    if(propchanged)
                        propobj.raiseassigned();
                    propobj.raiselostfocus();
                    propobj.raisetimerize(true);
                    propfocusout=true;
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
                    if(_navigateKeys(k))  // Tasti usati in navigazione tabs
                        return true;
            		if(k.which==13){ // INVIO
                        propobj.raiseassigned();
                        propobj.raiseenter();
                        propchangedfalse=true;
            		}
                    else if(k.which==27){ // ESCAPE
                        if(settings.escape!=missing){settings.escape(propobj)}
                    }
                    else if(k.which==46){ // delete
                        if(k.ctrlKey){
                            k.preventDefault();
                            propobj.clear();
                            return false;
                        }
                        else{
                            propobj.raisechanged();
                        }
                        propobj.raisetimerize(false);
                    }
                    else if(k.which==8 || (k.shiftKey && k.which==45)){
                        propobj.raisechanged();
                        propobj.raisetimerize(false);
            		}
                    else if(k.which==9){
                        return nextFocus(propname, k.shiftKey, k);
                    }
            	}
            );
            $("#"+propname+"_anchor").keypress(
            	function(k){
                    if(_navigateKeys(k))
                        return true;
                    var n=String.fromCharCode(k.which).toUpperCase();
                    if(propfilter==""){
                        if(n>=" " && !(k.ctrlKey && n=="C")){
                            propobj.raisechanged();
                            propobj.raisetimerize(false);
                        }
                    }
                    else{
                        var e=true;
                        if(n>=" " && !(k.ctrlKey && n=="C")){e=(propfilter.indexOf(n)>=0)}
                        if(e){
    						propobj.raisechanged();
                            propobj.raisetimerize(false);
                        }
                        else{return false}
                    }
            	}
            );
            $("#"+propname+"_anchor").keyup(
            	function(k){
                    if(_navigateKeys(k))  // Tasti usati in navigazione tabs
                        return true;
                    if(propchangedfalse){
                        // COMANDO DOPO INVIO: ABBASSO IL FLAG CHANGED
                        propchanged=false;
                        propchangedfalse=false;
                    }
            	}
            );
            $("#"+propname+"_anchor").change(
                function(){
                    propobj.raisechanged();
                }
            );
            // FUNZIONI PUBBLICHE
            this.engage=function(){
                if(propenabled)
                    propobj.raiseenter();
            }
			this.maxlen=function(l){
				if(l==missing)
					return propmaxlen;
				else
					propmaxlen=l;
				$("#"+propname+"_anchor").attr({"maxlength":propmaxlen});
			}
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth});
                $("#"+propname+"_anchor").css({"width":propwidth-2});
            }
			this.value=function(v,a){
				if(v==missing){
					return $("#"+propname+"_anchor").val();
				}
				else{
					if(v.length>propmaxlen)
						v=v.substr(0,propmaxlen);
					$("#"+propname+"_anchor").val(v);
                    propobj.raisechanged();
                    propchanged=false;
                    if(a==missing){a=false}
                    if(a){propobj.raiseassigned()}
				}
			}
			this.text=function(){
				return propobj.value();
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v;
					if(v)
						$("#"+propname+"_anchor").removeAttr("disabled");
					else
						$("#"+propname+"_anchor").attr("disabled",true);
				}
			}
			this.readonly=function(v){
				if(v==missing){
					return $.isset($("#"+propname+"_anchor").attr("readonly"));
				}
				else{
					if(v)
						$("#"+propname+"_anchor").attr("readonly", "");
					else
						$("#"+propname+"_anchor").removeAttr("readonly");
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
			this.name=function(){
				return propname;
			}
			this.changed=function(v){
				if(v==missing)
					return propchanged;
				else
					propchanged=v;
			}
			this.modified=function(v){
				if(v==missing)
					return ($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
			this.clear=function(){
				$("#"+propname+"_anchor").val("");
				propobj.raisechanged();
			}
			this.focus=function(){
				objectFocus(propname);
			}
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)}
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)}
            }
            this.raisechanged=function(){
                propchanged=true;
                propobj.modified(1);
                if(settings.changed!=missing){settings.changed(propobj)}
                _modifiedState(propname,true);
            }
            this.raiseassigned=function(){
                propobj.modified(1);
                if(settings.assigned!=missing){settings.assigned(propobj)}
                propchanged=false;
            }
            this.raiseenter=function(){
                if(settings.enter!=missing){settings.enter(propobj)}
            }
            this.raisetimerize=function(f){
                if(settings.timerize!=missing){
                    if(f){
                        if(timerhandle!==false){
                            clearTimeout(timerhandle);
                            timerhandle=false;
                        }
                    }
                    else{
                        if(timerbusy||(timerhandle!==false)){
                            if(!timertry){
                                timertry=true;
                                setTimeout(
                                    function(){
                                        propobj.raisetimerize(false)
                                    }, 300
                                );
                            }
                        }
                        else{
                            timertry=false;
                            timerhandle=setTimeout(
                                function(){
                                    timerhandle=false;
                                    timerbusy=true;
                                    settings.timerize(propobj)
                                }, 300
                            );
                        }
                    }
                }
            }
            this.timerizefree=function(){
                timerbusy=false;
            }
            this.raiseexception=function(){
                //$("#exception").trigger("exception",propname,propinput,0);
                if(settings.exception!=missing){settings.exception(propobj)}
            }
            if(!propenabled)
                propobj.enabled(0);
            if(!propvisible)
                propobj.visible(0);
		    return this;
		}
	},{
		rylabel:function(settings){
			var propleft=20;
			var proptop=20;
            var propwidth=0;
			var propcaption="";
            var proptitle="";
			var propbabelcode=__($(this).attr("babelcode"));
			var propobj=this;
			var propenabled=true;
			var propvisible=true;
            var propbutton=false;
            var propflat=false;
            var propalign="left";
            var propcolor="black";
            var propautocoding=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="label";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.width!=missing){propwidth=settings.width}
			if(settings.caption!=missing){propcaption=settings.caption}else{propcaption=propname}
            if(settings.title!=missing){proptitle=settings.title}
			if(settings.code!=missing){propbabelcode=settings.code}
            if(settings.button!=missing){propbutton=settings.button}
            if(settings.flat!=missing){propflat=settings.flat}
            if(settings.align!=missing){propalign=settings.align}
            if(settings.color!=missing){propcolor=settings.color}
            if(settings.autocoding!=missing){propautocoding=settings.autocoding}
            if(propautocoding){
                if(propbabelcode=="")
                    propbabelcode="LBL_"+propcaption.replace(/[^\w]/ig, "").toUpperCase().substr(0,50);
            }
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }

            if(propbutton)
                this.type="button";

            $("#"+propname).addClass("ryobject");
            $("#"+propname).addClass("rylabel");
            $("#"+propname).css({"position":"absolute", "left":propleft, "top":proptop, "background-color":"transparent", "line-height":"18px"});
            if(proptitle!=""){
                $("#"+propname).attr({"title":proptitle});
            }
            if(propbutton){
                $("#"+propname).addClass("rybutton");
                $("#"+propname).html("<input type='textbox' id='"+propname+"_anchor'><span id='"+propname+"_caption'>"+propcaption+"</span>");
                $("#"+propname+"_anchor").css({"position":"absolute","font-size":"2px","left":"6px","top":"6px","width":"1px","height":"1px","border":"none","text-indent":"-10px","overflow":"hidden"});
                if(propflat)
                    $("#"+propname+"_caption").addClass("rybutton-caption-flat");
                else
                    $("#"+propname+"_caption").addClass("rybutton-caption");
                if(propwidth>0){
                    $("#"+propname+"_caption").css({"width":propwidth, "text-align":"center"});
                }
            }
            else{
                $("#"+propname).html("<div id='"+propname+"_caption'>"+propcaption+"</div>");
                $("#"+propname+"_caption").addClass("rylabel-caption");
                if(propwidth>0){
                    $("#"+propname).css({"width":propwidth, "height":24, "overflow-x":"hidden"});
                }
                if(propalign=="right"){
                    $("#"+propname+"_caption").css({"position":"absolute", "left":"auto", "right":0});
                }
                if(propcolor!="black")
                    $("#"+propname+"_caption").css({"color":propcolor});
            }

            if(settings.enabled!=missing){setenabled(settings.enabled)}
            if(settings.visible!=missing){setvisible(settings.visible)}
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
                        if(propflat)
                            $("#"+propname+"_caption").addClass("rybutton-flat-focus");
                        else
                            $("#"+propname+"_caption").addClass("rybutton-focus");
            		}
            	}
            );
            $("#"+propname+"_anchor").focusout(
            	function(){
                    if(propflat)
                        $("#"+propname+"_caption").removeClass("rybutton-flat-focus");
                    else
                        $("#"+propname+"_caption").removeClass("rybutton-focus");
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
            		if(k.which==13){ // INVIO
                        if(propbutton)
                            $("#"+propname).click();
            		}
                    else if(k.which==9){
                        if(propbutton)
                            return nextFocus(propname, k.shiftKey);
                    }
            	}
            );
            $("#"+propname+"_anchor").keyup(
            	function(k){
                    // MANTENGO PULITO INPUT
                    $("#"+propname+"_anchor").val("");
                }
            );
            $("#"+propname).click(
                function(evt){
                    if(propenabled){
                        if(propbutton)
                            objectFocus(propname);
                        if(settings.click!=missing){
                            if(propbutton){
                                if(propflat){
                                    $("#"+propname+"_caption").addClass("rybutton-flat-click");
                                    setTimeout(function(){
                                        $("#"+propname+"_caption").removeClass("rybutton-flat-click");
                                    }, 500);
                                }
                                else{
                                    $("#"+propname+"_caption").addClass("rybutton-click");
                                    setTimeout(function(){
                                        $("#"+propname+"_caption").removeClass("rybutton-click");
                                    }, 500);
                                }
                            }
                            setTimeout(function(){
                                settings.click(propobj);
                            });
                        }
                    }
                }
            );
            // FUNZIONI PUBBLICHE
            this.engage=function(done){
                if(propenabled){
                    if(settings.click!=missing){
                        settings.click(propobj, done);
                    }
                }
            }
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                $("#"+propname).css({"left":propleft,"top":proptop});
            }
			this.caption=function(v){
				if(v==missing){
					return propcaption;
				}
				else{
					propcaption=v;
					$("#"+propname+"_caption").html(v);
				}
			}
			this.title=function(v){
				if(v!=missing){
                    v=v.replace(/<[bh]r *\/?>/gi,"\n");
                    v=v.replace(/<\/p>/gi,"\n");
                    v=v.replace(/<[^<>]*>/gi,"");
                    v=v.replace(/[\r\n]+/gi,"\n");
                    v=v.replace(/'"/gi,"’");
					proptitle=v.htmlDecod();
                    if(proptitle.length>1000){
                        proptitle=proptitle.substr(0,1000)+"...";
                    }
                    $("#"+propname).attr({"title":proptitle});
				}
                return proptitle;
			}
			this.enabled=function(v){
				if(v==missing)
					return propenabled;
				else
                    setenabled(v);
			}
			this.visible=function(v){
				if(v==missing)
					return propvisible;
				else
                    setvisible(v);
			}
			this.color=function(v){
				if(v!=missing){
                    propcolor=v;
                    $("#"+propname+"_caption").css({"color":propcolor});
                }
                return propcolor;
			}
			this.name=function(){
				return propname;
			}
			this.babelcode=function(v){
				if(v==missing)
					return propbabelcode;
				else
					propbabelcode=v;
			}
            // FUNZIONI PRIVATE
            function setenabled(v){
                propenabled=v.booleanNumber();
                if(propenabled)
                    $("#"+propname+"_caption").removeClass("rybutton-disabled");
                else
                    $("#"+propname+"_caption").addClass("rybutton-disabled");
            }
            function setvisible(v){
                if(propvisible=v.booleanNumber())
                    $("#"+propname).css({"visibility":"visible"});
                else
                    $("#"+propname).css({"visibility":"hidden"});
            }
			return this;
		}
	},{
		rycheck:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=22;
			var propheight=22;
			var propvalue=0;
			var propobj=this;
			var propenabled=true;
			var propvisible=true;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="check";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.enabled!=missing){propenabled=settings.enabled}
            if(settings.visible!=missing){propvisible=settings.visible}
			
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            }
            if(settings.tag!=missing){this.tag=settings.tag}

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("rycheck")
            .css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight,"background-color":"transparent","font-family":"verdana,sans-serif","font-size":"13px","overflow":"hidden"})
            .html("<input type='text' id='"+propname+"_anchor'><div id='"+propname+"_border'></div>");
            $("#"+propname+"_border").css({"position":"absolute","left":0,"top":2,"width":propwidth-4,"height":propheight-4,"background-color":"silver"});
            $("#"+propname+"_border").html("<div id='"+propname+"_internal'></div>");
            $("#"+propname+"_internal").css({"position":"absolute","left":1,"top":1,"width":propwidth-6,"height":propheight-6,"color":"#000000","background-color":"#FFFFFF","overflow":"hidden"});
            $("#"+propname+"_internal").html("<div id='"+propname+"_text'></div>");
            $("#"+propname+"_text").css({"position":"absolute","left":3,"top":-1,"line-height":"19px","cursor":"default"});
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_internal").css({"background-color":globalcolorfocus});
                        propobj.raisegotfocus();
            		}
            	}
            );
            
            $("#"+propname+"_anchor").focusout(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_internal").css({"background-color":"#FFFFFF"});
                        propobj.raiselostfocus();
            		}
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
            		if(k.which==13){ // INVIO
                        propobj.raiseassigned();
                        propobj.raiseenter();
            		}
                    else if(k.which==27){ // ESCAPE
                        if(settings.escape!=missing){settings.escape(propobj)}
                    }
                    if(k.which==9){
                        return nextFocus(propname, k.shiftKey);
                    }
            	}
            );
            $("#"+propname+"_anchor").keypress(
            	function(k){
            		if(propenabled){
            			if(k.which==32){
                            if(propvalue)
                                propobj.value(0,true);
                            else
                                propobj.value(1,true);
                        }
            		}
            	}
            );
            $("#"+propname+"_anchor").keyup(
            	function(k){
                    // MANTENGO PULITO INPUT
                    $("#"+propname+"_anchor").val("");
            	}
            );
            $("#"+propname).mousedown(
            	function(evt){
            		if(propenabled){
            			castFocus(propname);
                        if(propvalue)
                            propobj.value(0,true);
                        else
                            propobj.value(1,true);
            		}
            	}
            );
            // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                $("#"+propname).css({"left":propleft,"top":proptop});
            }
			this.value=function(v,a){
				if(v==missing){
					return propvalue;
				}
				else{
                    propvalue=v.booleanNumber();
                    if(propvalue)
						$("#"+propname+"_text").html("&#x2714;");
					else
						$("#"+propname+"_text").html("");
                    if(a==missing){a=false}
                    if(a){propobj.raiseassigned()}
				}
			}
            this.text=function(){
                return (propvalue ? "1" : "0");
            }
			this.name=function(){
				return propname;
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v;
					if(v){
						$("#"+propname+"_anchor").removeAttr("disabled");
						$("#"+propname+"_internal").css({"color":"#000000","background-color":"#FFFFFF"});
					}
					else{
						$("#"+propname+"_anchor").attr("disabled",true);
						$("#"+propname+"_internal").css({"color":"gray","background-color":"#F0F0F0"});
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
			this.clear=function(){
                propobj.value(0);
			}
			this.focus=function(){
				objectFocus(propname);
			}
			this.modified=function(v){
				if(v==missing)
					return ($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)}
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)}
            }
            this.raiseassigned=function(){
                propobj.modified(1);
                if(settings.assigned!=missing){settings.assigned(propobj)}
                _modifiedState(propname,true);
            }
            this.raiseenter=function(){
                if(settings.enter!=missing){settings.enter(propobj)}
            }
            this.raiseexception=function(){
                //$("#exception").trigger("exception",propname,propinput,0);
                if(settings.exception!=missing){settings.exception(propobj)}
            }
            if(!propenabled)
                propobj.enabled(0);
            if(!propvisible)
                propobj.visible(0);
			return this;
		}
	},{
		rylist:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=200;
			var propheight=22;
			var propmaxopt=0;
            var propchanged=false;
			var propobj=this;
			var propenabled=true;
			var propvisible=true;
            var proplink=null;
            var propautocoding=false;

			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="list";
            
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.width!=missing){propwidth=settings.width}
            if(settings.enabled!=missing){propenabled=settings.enabled}
            if(settings.visible!=missing){propvisible=settings.visible}
            if(settings.autocoding!=missing){propautocoding=settings.autocoding}
			
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            }
            if(settings.tag!=missing){this.tag=settings.tag}

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("rylist")
            .css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight,"background-color":"silver","border":"none"})
            .html("<select id='"+propname+"_anchor' size='1'></select>");
            $("#"+propname+"_anchor").css({"position":"absolute","left":1,"top":1,"width":propwidth-2,"height":propheight-2,"border":"none","background-color":"#FFFFFF","font-family":"verdana,sans-serif","font-size":"13px","outline":"none"});
            $("#"+propname+"_anchor option").css({"background-color":"#FFFFFF"});
            
            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_anchor").css({"background-color":globalcolorfocus});
                        propobj.raisegotfocus();
                        propchanged=false;
            		}
            	}
            );
            $("#"+propname+"_anchor").focusout(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_anchor").css({"background-color":"#FFFFFF"});
            			if(propchanged)
                            propobj.raiseassigned();
                        propobj.raiselostfocus();
            		}
            	}
            );
            $("#"+propname+"_anchor").keydown(
            	function(k){
                    if(k.which==9)
                        return nextFocus(propname, k.shiftKey);
                    else if(32<=k.which && k.which<=40){
                        propobj.raisechanged();
                    }
                    else if(k.which==13){
                        k.preventDefault();
                        propobj.raiseassigned();
                        propobj.raiseenter();
                    }
                    else if(k.which==27){ // ESCAPE
                        if(settings.escape!=missing){settings.escape(propobj)}
                    }
            	}
            );
            $("#"+propname+"_anchor").change(
            	function(){
                    propobj.raisechanged();
            	}
            );
            // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth});
                $("#"+propname+"_anchor").css({"width":propwidth-2});
            }
			this.value=function(k,a){
				if(k==missing){
                    return _$( $("#"+propname+"_anchor").val(), 0 ).actualInteger();
				}
				else{
					$("#"+propname+"_anchor").val(k);
                    if($.browser.msie){
                        $("#"+propname+"_anchor").css({display:"block"});
                    }
                    propobj.raisechanged();
                    if(a==missing){a=false}
                    if(a){propobj.raiseassigned()}
				}
			}
            this.setkey=function(k,a){
                for(var i=1;i<=propobj.count();i++){
                    if(propobj.key(i)==k){
                        $("#"+propname+"_anchor").val(i);
                        if($.browser.msie){
                            $("#"+propname+"_anchor").css({display:"block"});
                        }
                        propobj.raisechanged();
                        if(a==missing){a=false}
                        if(a){propobj.raiseassigned()}
                        break;
                    }
                }
            }
			this.text=function(){
				var k=$("#"+propname+"_anchor").val();
				return $("#"+propname+"_anchor option[value='"+k+"']").html();
			}
			this.caption=function(k,c){
				if(c==missing){
					return $("#"+propname+"_anchor option[value='"+k+"']").html();
				}
				else{
					$("#"+propname+"_anchor option[value='"+k+"']").html(c);
				}
			}
			this.count=function(){
				return $("#"+propname+"_anchor option").length;
			}
			this.babelcode=function(k){
				return $("#"+propname+"_anchor option[value='"+k+"']").attr("babelcode");
			}
			this.additem=function(item){
				propmaxopt++;
				var i=propmaxopt;
				var d="",k=i,b="",t="";
				if(item.caption!=missing){d=item.caption}
				if(item.key!=missing){k=item.key}
                if(item.code!=missing)
                    b=item.code;
                else if(propautocoding && d!="")
                    b="LST_"+d.replace(/[^\w]/ig, "").toUpperCase().substr(0,50);
                if(item.tag!=missing){t=item.tag}
                $("#"+propname+"_anchor").append("<option value='"+i+"' key='"+k+"' babelcode='"+b+"' tag='"+t+"'>"+d+"</option>");
                $("#"+propname+"_anchor option").css({"background-color":"#FFFFFF"});
                return propobj;
			}
			this.removeitem=function(k){
				$("#"+propname+"_anchor option[value='"+k+"']").remove();
			}
			this.name=function(){
				return propname;
			}
			this.key=function(i){
                if(i==missing)
                    i=this.value();
				return $("#"+propname+"_anchor option[value='"+i+"']").attr("key");
			}
			this.index=function(k){
				return $("#"+propname+"_anchor option[key='"+k+"']").attr("value");
			}
			this.gettag=function(i){
                if(i==missing)
                    i=this.value();
				return $("#"+propname+"_anchor option[value='"+i+"']").attr("tag");
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v;
					if(v){
						$("#"+propname+"_anchor").removeAttr("disabled");
						$("#"+propname+"_anchor").css({"color":"#000000"});
					}
					else{
						$("#"+propname+"_anchor").attr("disabled",true);
						$("#"+propname+"_anchor").css({"color":"gray"});
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
			this.clear=function(){
				propmaxopt=0;
			    $("#"+propname+"_anchor").html("");
			}
			this.modified=function(v){
				if(v==missing)
					return ($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
			this.focus=function(){
				objectFocus(propname);
			}
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)}
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)}
            }
            this.raisechanged=function(){
                propchanged=true;
                propobj.modified(1);
                if(settings.changed!=missing){settings.changed(propobj)}
                _modifiedState(propname, true);
            }
            this.raiseassigned=function(){
                propobj.modified(1);
                if(settings.assigned!=missing){settings.assigned(propobj)}
                propchanged=false;
            }
            this.raiseenter=function(){
                if(settings.enter!=missing){settings.enter(propobj)}
            }
            this.raiseexception=function(){
                //$("#exception").trigger("exception",propname,propinput,0);
                if(settings.exception!=missing){settings.exception(propobj)}
            }
            if(!propenabled)
                propobj.enabled(0);
            if(!propvisible)
                propobj.visible(0);
			return this;
		}
	},{
		rytime:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=200;
			var propheight=22;
			var propobj=this;
			var propenabled=true;
			var propvisible=true;
            var prophours=null;
            var propminutes=null;
            var proplink=null;

			var propname=$(this).attr("id");
            var propnameh=propname+"_hours";
            var propnamem=propname+"_minutes";
			this.id="#"+propname;
			this.tag=null;
			this.type="time";
            
            globalobjs[propname]=this;
			
			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.tag!=missing){this.tag=settings.tag}

            var formid=$("#"+propname).prop("parentid");
            if(settings.formid!=missing){
                formid=settings.formid;
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", formid);
                _globalforms[formid].controls[propname]=propname.substr(formid.length);
            }

            $("#"+propname)
            .addClass("ryobject")
            .addClass("rytime")
            .css({"position":"absolute","left":propleft,"top":proptop})
            .html("<div id='"+propnameh+"' class='rytime'></div><div id='"+propname+"_separator'>:</div><div id='"+propnamem+"' class='rytime'></div>");
            
            if($.isset(formid)){
                $("#"+propnameh).prop("parentid", formid);
                _globalforms[formid].controls[propnameh]=propnameh.substr(formid.length);
                $("#"+propnamem).prop("parentid", formid);
                _globalforms[formid].controls[propnamem]=propnamem.substr(formid.length);
            }
            
            if(settings.datum!=missing){
                prophours=$("#"+propnameh).rylist({left:0, top:0, width:50, datum:settings.datum,
                    assigned:function(){
                        propobj.raiseassigned();
                    }
                });
            }
            else{
                prophours=$("#"+propnameh).rylist({left:0, top:0, width:50,
                    assigned:function(){
                        propobj.raiseassigned();
                    }
                });
            }
            for(var i=0;i<=23;i++){
                var t="00"+i;
                t=t.substr(t.length-2,2);
                prophours.additem({caption:t, key:t});
            }
            $("#"+propname+"_separator").css({position:"absolute", left:57, top:0});
            if(settings.datum!=missing){
                propminutes=$("#"+propnamem).rylist({left:70, top:0, width:50, datum:settings.datum,
                    assigned:function(){
                        propobj.raiseassigned();
                    }
                });
            }
            else{
                propminutes=$("#"+propnamem).rylist({left:70, top:0, width:50,
                    assigned:function(){
                        propobj.raiseassigned();
                    }
                });
            }
            for(var i=0;i<=59;i+=5){
                var t="00"+i;
                t=t.substr(t.length-2,2);
                propminutes.additem({caption:t, key:t});
            }
            // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                $("#"+propname).css({"left":propleft,"top":proptop});
            }
			this.value=function(v,a){
				if(v==missing){
					return (new Date(0, 0, 0, prophours.key().actualInteger(), propminutes.key().actualInteger()));
				}
				else{
					try{
						if(v!=""){
                            var h,m;
                            if(typeof v=="string"){
                                v=v.replace(/[-T :]/g, "");
                                if(v.length>4)
                                    v=v.substr(8,4);
                                h=v.substr(0,2);
                                m=v.substr(2,2);
                            }
                            else{
                                h=("00"+v.getHours()).subright(2);
                                m=("00"+v.getMinutes()).subright(2);
                            }
                            prophours.setkey(h);
                            m=m.actualInteger();
                            if(m<55)
                                m=("00"+(5*Math.round(m/5))).subright(2);
                            else
                                m="55";
                            propminutes.setkey(m);
                            if(a==missing){a=false}
                            if(a){propobj.raiseassigned()}
						}
						else{
							propobj.clear();
						}
					}
					catch(e){
						propobj.clear();
					}
				}
			}
			this.text=function(){
                if(propvisible)
                    return prophours.key()+propminutes.key();
                else
                    return "";
			}
			this.hours=function(){
                if(propvisible)
                    return prophours.key().actualInteger();
                else
                    return 0;
			}
			this.minutes=function(){
                if(propvisible)
                    return propminutes.key().actualInteger();
                else
                    return 0;
			}
			this.name=function(){
				return propname;
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v;
                    prophours.enabled(v);
                    propminutes.enabled(v);
				}
			}
			this.visible=function(v){
				if(v==missing){
					return propvisible;
				}
				else{
					propvisible=v;
					if(v)
						$("#"+propname).css({"display":"block"});
					else
						$("#"+propname).css({"display":"none"});
				}
			}
			this.clear=function(){
                prophours.setkey("00");
                propminutes.setkey("00");
			}
            this.link=function(objdate){
                proplink=objdate;
            }
            this.raiseassigned=function(){
                if(settings.assigned!=missing){settings.assigned(propobj)}
                if(proplink){
                    proplink.modified(1);
                    _modifiedState(proplink.name(), true);
                    proplink.raiseassigned();
                }
            }
			return this;
		}
    });
})(jQuery);
		
function ryBox(missing){
    var propbabelcodes={};
    var propbabelcache={};
    var proplanguage="";
    this.container=function(c){
        if(c==missing)
            return globalcontainer;
        else
            globalcontainer=c;
    }
    this.createstandard=function(){
        $(globalcontainer).append("<div id='rybox_popup' class='contextMenu' style='position:absolute;visibility:hidden;'><ul><li id='rybox_cut'><img src='"+_systeminfo.relative.cambusa+"rybox/images/menu-cut.png'>Cut</li><li id='rybox_copy'><img src='"+_systeminfo.relative.cambusa+"rybox/images/menu-copy.png'>Copy</li><li id='rybox_paste'><img src='"+_systeminfo.relative.cambusa+"rybox/images/menu-paste.png'>Paste</li></ul></div>");
        $(globalcontainer).append("<div id='rybox_calculator' style='position:absolute;display:none;'></div>");
        $(globalcontainer).append("<div id='ryque_popup' class='contextMenu' style='position:absolute;visibility:hidden;'><ul><li id='ryque_use'><img src='"+_systeminfo.relative.cambusa+"rybox/images/menu-use.png'><a href='javascript:'>Use</a></li><li id='ryque_sheet'><img src='"+_systeminfo.relative.cambusa+"rybox/images/menu-export.png'><a href='javascript:'>Export</a></li></ul></div>");
        $(document).bind("contextmenu",function(e){ return globaledittext; });
        $(document).keydown(
            function(k){
                if(k.which==8){
                    return globaledittext;
                }
                else if(k.which==116){ // F5
                    return false;
                }
            }
        );
    }
    this.addobject=function(o){
        globalobjs[o.name()]=o;
    }
    this.localize=function(lang, parentid, action, missing){
        var selflearnig=false;
        TAIL.enqueue(function(lang, parentid){
            if(_systeminfo.relative.cambusa!="" && lang!="default"){
                var i,c,t,j,k="";
                var collection=globalobjs;
                var fields={};
                if(proplanguage!=lang){
                    proplanguage=lang;
                    propbabelcache={};
                    for(c in propbabelcodes)
                        propbabelcodes[c].virgin=true;
                }
                if(parentid!=missing)
                    collection=_globalforms[parentid].controls;
                for(i in collection){
                    var o=globalobjs[i];
                    if(o){
                        switch(o.type){
                            case "label":
                            case "button":
                                if((c=o.babelcode())>""){
                                    if($.isset(propbabelcache[c])){
                                        t=propbabelcache[c];
                                        if(t.length>0)
                                            o.caption(t);
                                    }
                                    else{
                                        if(k!=""){k+="|"}
                                        k+=c;
                                        fields[i]=o;
                                        propbabelcache[c]="";
                                    }
                                }
                                break;
                            case "list":
                                for(j=1;j<=o.count();j++){
                                    if((c=o.babelcode(j))>""){
                                        if($.isset(propbabelcache[c])){
                                            t=propbabelcache[c];
                                            if(t.length>0)
                                                o.caption(j,t);
                                        }
                                        else{
                                            if(k!=""){k+="|"}
                                            k+=c;
                                            fields[i]=o;
                                            propbabelcache[c]="";
                                        }
                                    }
                                }
                                break;
                            case "grid":
                                for(j=1;j<=o.columns();j++){
                                    if((c=o.babelcode(j))>""){
                                        if($.isset(propbabelcache[c])){
                                            t=propbabelcache[c];
                                            if(t.length>0)
                                                o.caption(j,t);
                                        }
                                        else{
                                            if(k!=""){k+="|"}
                                            k+=c;
                                            fields[i]=o;
                                            propbabelcache[c]="";
                                        }
                                    }
                                }
                                break;
                            case "tabs":
                                for(j=1;j<=o.tabs();j++){
                                    if((c=o.babelcode(j))>""){
                                        if($.isset(propbabelcache[c])){
                                            t=propbabelcache[c];
                                            if(t.length>0)
                                                o.caption(j,t);
                                        }
                                        else{
                                            if(k!=""){k+="|"}
                                            k+=c;
                                            fields[i]=o;
                                            propbabelcache[c]="";
                                        }
                                    }
                                }
                                break;
                        }
                    }
                }
                for(c in propbabelcodes){
                    var o=propbabelcodes[c];
                    if(o.virgin){
                        if(k!=""){k+="|"}
                            k+=c;
                    }
                }
                if(window.console&&_sessioninfo.debugmode){console.log("Babelcodes:"+k)}
                if(k!=""){
                    $.engage(_systeminfo.relative.cambusa+"rybabel/rybabel.php", {"lang":lang,"codes":k},
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                for(i in fields){
                                    var o=globalobjs[i];
                                    switch(o.type){
                                        case "label":
                                        case "button":
                                            if((c=o.babelcode())>""){
                                                if($.isset(v[c])){
                                                    t=v[c];
                                                    if(t.length>0)
                                                        o.caption(t);
                                                    propbabelcache[c]=t;
                                                }
                                            }
                                            break;
                                        case "list":
                                            for(j=1;j<=o.count();j++){
                                                if((c=o.babelcode(j))>""){
                                                    if($.isset(v[c])){
                                                        t=v[c];
                                                        if(t.length>0)
                                                            o.caption(j,t);
                                                        propbabelcache[c]=t;
                                                    }
                                                }
                                            }
                                            break;
                                        case "grid":
                                            for(j=1;j<=o.columns();j++){
                                                if((c=o.babelcode(j))>""){
                                                    if($.isset(v[c])){
                                                        t=v[c];
                                                        if(t.length>0)
                                                            o.caption(j,t);
                                                        propbabelcache[c]=t;
                                                    }
                                                }
                                            }
                                            break;
                                        case "tabs":
                                            for(j=1;j<=o.tabs();j++){
                                                if((c=o.babelcode(j))>""){
                                                    if($.isset(v[c])){
                                                        t=v[c];
                                                        if(t.length>0)
                                                            o.caption(j,t);
                                                        propbabelcache[c]=t;
                                                    }
                                                }
                                            }
                                            break;
                                    }
                                }
                                for(c in propbabelcodes){
                                    var o=propbabelcodes[c];
                                    if(o.virgin){
                                        if($.isset(v[c])){
                                            t=v[c];
                                            if(t.length>0)
                                                o.caption=t;
                                        }
                                        o.virgin=false;
                                    }
                                }
                                selflearnig=$.isset(v["___SELFLEARNING"]);
                                if(window.console&&_sessioninfo.debugmode){console.log("selflearnig (attivazione):"+selflearnig)}
                                if(selflearnig){
                                    var l=v["___SELFLEARNING"].split("|");
                                    var selfcodes=[];
                                    for(i in fields){
                                        var o=globalobjs[i];
                                        switch(o.type){
                                            case "label":
                                            case "button":
                                                if((c=o.babelcode())>""){
                                                    if(l.indexOf(c)>=0)
                                                        selfcodes.push({code:c,caption:o.caption()});
                                                }
                                                break;
                                            case "list":
                                                for(j=1;j<=o.count();j++){
                                                    if((c=o.babelcode(j))>""){
                                                        if(l.indexOf(c)>=0)
                                                            selfcodes.push({code:c,caption:o.caption(j)});
                                                    }
                                                }
                                                break;
                                            case "grid":
                                                for(j=1;j<=o.columns();j++){
                                                    if((c=o.babelcode(j))>""){
                                                        if(l.indexOf(c)>=0)
                                                            selfcodes.push({code:c,caption:o.caption(j)});
                                                    }
                                                }
                                                break;
                                            case "tabs":
                                                for(j=1;j<=o.tabs();j++){
                                                    if((c=o.babelcode(j))>""){
                                                        if(l.indexOf(c)>=0)
                                                            selfcodes.push({code:c,caption:o.caption(j)});
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                    for(c in propbabelcodes){
                                        if(l.indexOf(c)>=0)
                                            selfcodes.push({code:c,caption:propbabelcodes[c].caption});
                                    }
                                    if(window.console&&_sessioninfo.debugmode){console.log(selfcodes)}
                                    setTimeout(function(){$.engage(_systeminfo.relative.cambusa+"rybabel/rybabel.php", {"env":_sessioninfo.environ, "sessionid":_sessioninfo.sessionid, "codes":selfcodes}, 
                                        function(d){
                                            if(window.console&&_sessioninfo.debugmode){console.log("selflearnig (esito):"+d)}
                                        }, {failure:"0"});
                                    }, 2000);
                                }
                            }
                            catch(e){
                                if(window.console&&_sessioninfo.debugmode){console.log(e.message)}
                            }
                            TAIL.free();
                            if(action!=missing){
                                setTimeout(action);
                            }
                        }
                    );
                }
                else{
                    TAIL.free();
                    if(action!=missing){
                        setTimeout(action);
                    }
                }
            }
            else{
                TAIL.free();
                if(action!=missing){
                    setTimeout(action);
                }
            }
        }, lang, parentid);
        TAIL.wriggle();
    }
    this.babels=function(codes, args, missing){
        if(typeof(codes)=="object"){
            for(var b in codes){
                if(!$.isset(propbabelcodes[b]))
                    propbabelcodes[b]={caption:codes[b], virgin:true};
            }
        }
        else{
            try{
                var b=propbabelcodes[codes].caption;
                if(args!=missing){
                    if(typeof(args)=="object"){
                        var i=0;
                        for(var a in args){
                            i+=1;
                            b=b.replace("{"+a+"}", args[a]).replace("{"+i+"}", args[a]);
                        }
                    }
                    else{
                        b=b.replace("{1}", args);
                    }
                }
                return b.replace(/\\n/g, String.fromCharCode(10));
            }
            catch(er){
                if(window.console)console.log("["+codes+"] not defined");
                if(args!=missing){
                    if(typeof(args)=="string")
                        return args;
                }
                return codes;
            }
        }
    }
    this.babelexists=function(code){
        return $.isset(propbabelcodes[code]);
    }
    this.getbabel=function(n, args, missing){
        var b=$("#"+n+" .rylabel-caption").html();
        if(b===null){
            if(window.console)console.log("Label ["+n+"] doesn't exist!");
            b="";
        }
        else if(args!=missing){
            if(typeof(args)=="object"){
                var i=0;
                for(var a in args){
                    i+=1;
                    b=b.replace("{"+a+"}", args[a]).replace("{"+i+"}", args[a]);
                }
            }
            else{
                b=b.replace("{1}", args);
            }
        }
        b=b.replace(/\\n/g, String.fromCharCode(10));
        return b;
    }
    this.setfocus=function(n){
        castFocus(n);
    }
    this.controls=function(n){
        if(n!=missing)
            return globalobjs[n];
        else
            return globalobjs;
    }
    this.menucontext=function(divid, m){
        if( $("#"+divid).length==0 ){
            var h="";
            h+="<div id='"+divid+"' class='contextMenu'><ul>";
            
            for(var n in m){
                var autocoding=false;
                var id="";
                var caption="";
                var code="";
                if(m[n].id!=missing){id=m[n].id}
                if(m[n].caption!=missing){caption=m[n].caption}
                if(m[n].autocoding!=missing){autocoding=m[n].autocoding}
                
                if(m[n].code!=missing){code=m[n].code}
                if(code=="" && autocoding)
                    code="POP_"+caption.replace(/[^\w]/ig, "").toUpperCase();
                if(code!="")
                    caption=RYBOX.babels(code);
                
                if(caption=="-"){
                    if(id=="")
                        h+="<li class='contextSeparator'></li>";
                    else
                        h+="<li class='contextSeparator' id='"+id+"'></li>";
                }
                else{
                    h+="<li class='"+id+"' id='"+id+"'><a href='javascript:'>"+caption+"</a></li>";
                }
            }
            
            h+="</ul></div>";
            $("body").append(h);    
        }
    }
    this.menudisable=function(divid){
        $("."+divid).addClass("contextDisabled");
    }
    this.menuhidden=function(menu, divid){
        $("."+divid, menu).remove();
    }
    this.createstandard();
    // FUNZIONI PRIVATE
    function solveparent(o,parentid,missing){
        var attr;
        var range=true;
        try{
            if(parentid!=missing){
                attr=$(o.id).prop("parentid");
                if(attr!=missing){
                    if(attr!=parentid)
                        range=false;
                }
            }
        }
        catch(e){}
        return range;
    }
}

function validateDate(d,m,y){
    var r,n;
    try{
        n=new Date(y,m-1,d);
        r=(n.getDate()==d && n.getMonth()==(m-1) && n.getFullYear()==y);
    }
    catch(e){
        r=false;
    }
    return r;
}
function objectFocus(n){
    try{
        if(n.substr(0,1)=="#")
            n=n.substr(1);
        if(globalobjs[n].type=="edit")
            CKEDITOR.instances[n+"_anchor"].focus();
        else if(globalobjs[n].type=="script")
            globalobjs[n].focus();
        else
            document.getElementById(n+"_anchor").focus();
    }
    catch(e){}
}
function castFocus(n){
    if(globalcastfocus!==false){
        clearTimeout(globalcastfocus);
        globalcastfocus=false;
    }
    globalcastfocus=setTimeout(
        function(){
            objectFocus(n);
        }, 200
    );
}
function nextFocus(nm,sh,k,missing){
    try{
        var notab=$.isset($("#"+nm).attr("notab"));
        if($.isset(k)){k.preventDefault()}
        var st=0;    // Stato 0 iniziale, 1 incontrato formid, 2 azione terminata, 3 prendi l'ultimo
        var fs="";   // primo
        var pr="";   // precedente
        var ls="";   // ultimo
        var ts="date|number|text|check|list|grid|button|helper|area|edit|code|tree|script";
        var formid=$("#"+nm).prop("parentid");
        var coll=new Object();
        if(formid==missing){
            for(var i in globalobjs){
                if($.isset($("#"+i).attr("notab"))==notab){
                    if(_visibleobject(i)){
                        var o=globalobjs[i];
                        if(ts.indexOf(o.type)>=0){
                            if(o.visible() && o.enabled())
                                coll[i]=i;
                        }
                    }
                }
            }
        }
        else{
            for(var i in globalobjs){
                if($("#"+i).prop("parentid")==formid){
                    if($.isset($("#"+i).attr("notab"))==notab){
                        if(_visibleobject(i)){
                            var o=globalobjs[i];
                            if(ts.indexOf(o.type)>=0){
                                if(o.visible() && o.enabled())
                                    coll[i]=i;
                            }
                        }
                    }
                }
            }
        }
        for(var i in coll){
            if(fs=="")  // Imposto il primo
                fs=i;
            if(st==1){  // Nel giro precedente ho incontrato nm
                if(sh==1){  // Shift premuto
                    if(pr!=""){
                        st=2;
                        objectFocus(pr);    // Imposto il precedente
                        break;
                    }
                    else{
                        st=3;              // devo impostare l'ultimo
                    }
                }
                else{
                    st=2;
                    objectFocus(i); // Imposto il corrente
                    break;
                }
            }
            if(i==nm)
                st=1;
            else
                pr=i;
            ls=i;
        }
        if(st==3 && ls!="")
            objectFocus(ls);
        else if(st==1 && fs!=""){
            if(sh==1){  // Shift premuto
                if(pr!=""){objectFocus(pr)}
            }
            else
                objectFocus(fs);
        }
    }catch(er){
        if(window.console)console.log(er);
    }
    return false;
}
function _modifiedState(id,v,missing){
    if(RYWINZ){
        var formid=$("#"+id).prop("parentid");
        var datum=$("#"+id).prop("datum");
        if(formid!=missing && datum!=missing){
            RYWINZ.modified(formid, __(v).booleanNumber());
        }
    }
}
function _busyState(id,v,missing){
    if(RYWINZ){
        var formid=$("#"+id).prop("parentid");
        if(formid!=missing){
            RYWINZ.busy(formid, __(v).booleanNumber());
        }
    }
}
function _navigateKeys(k){
    /*
    if($.browser.opera){
        switch(k.which){
        case 39:// right
        case 37:// left
        case 46:// delete
        case 45:// ins
            return false;
        defult:
            return (k.ctrlKey && k.which!=50);
        }
    }
    else{
    */
        return (k.altKey && k.which!=50);
    //}
}
function _visibleobject(id){
    var o=$("#"+id);
    var d=o.css("display");
    var v=o.css("visibility");
    var r=!(d=="none" || v=="hidden");
    if(r){
        $.each( $("#"+id).parents(), 
            function(key, value){
                if(o=$(value)){
                    d=o.css("display");
                    v=o.css("visibility");
                    if( d=="none" || v=="hidden" ){r=0}
                }
            }
        );
    }
    return r;
}
$(document).ready(function(){
    RYBOX=new ryBox();
});
