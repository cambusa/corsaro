/****************************************************************************
* Name:            rycode.js                                                *
* Project:         Cambusa/ryBox                                            *
* Version:         1.69                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var clipcode=null;
var _globalcodeinsert=_$($.cookie("codeinsert"), 1).booleanNumber();
(function($,missing) {
    $.extend(true,$.fn, {
        rycode:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=120;
			var propheight=22;
            var propmaxlen=50;
			var propcode="";
            var propmode="free";
            var propuppercase=false;
            var proplock=0;
            var propinsert=parseInt(_globalcodeinsert);
			var propstart=0;
            var propcharleft=0;
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
            var propmousedown=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="code";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.maxlen!=missing){propmaxlen=settings.maxlen}
            if(settings.mode!=missing){propmode=settings.mode}
            if(settings.uppercase!=missing){propuppercase=settings.uppercase}
            if(settings.lock!=missing){proplock=settings.lock.booleanNumber()}
            if(settings.enabled!=missing){propenabled=settings.enabled}
            if(settings.visible!=missing){propvisible=settings.visible}
            if(settings.helper!=missing){prophelper=settings.helper.booleanNumber()}

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
            .addClass("rycode")
            .css({
                "position":"absolute",
                "left":propleft,
                "top":proptop,
                "width":propwidth,
                "height":propheight,
                "color":"transparent",
                "background-color":"silver",
                "font-family":"verdana,sans-serif",
                "font-size":"13px",
                "line-height":"17px",
                "cursor":"default"
            })
            .html("<input type='text' id='"+propname+"_anchor'><div id='"+propname+"_internal'></div><div id='"+propname+"_button'></div>");

            $("#"+propname+"_internal")
            .css({"position":"absolute","left":1,"top":1,"width":propwidth-2,"height":propheight-2,"color":"#000000","background-color":"#FFFFFF","overflow":"hidden"})
            .html("<div id='"+propname+"_text'></div><div id='"+propname+"_cursor'></div><span id='"+propname+"_span'></span>");

            $("#"+propname+"_cursor").css({"position":"absolute","left":1,"top":1,"width":1,"height":propheight-4,"background-color":"#000000","visibility":"hidden"});
            $("#"+propname+"_span").css({"position":"absolute","visibility":"hidden"});
            $("#"+propname+"_text").css({"position":"absolute","cursor":"text","left":2,"top":1,"height":propheight-4,"overflow":"hidden"});
            $("#"+propname+"_button").css({"position":"absolute","cursor":"pointer","left":propwidth-20,"top":2,"width":18,"height":18,"background":"url("+_systeminfo.relative.cambusa+"ryquiver/images/helper.png)"});
            
            if(prophelper){
                $("#"+propname+"_text").css({"width":propwidth-25});
                $("#"+propname+"_button").css({"display":"block"});
            }
            else{
                $("#"+propname+"_text").css({"width":propwidth-5});
                $("#"+propname+"_button").css({"display":"none"});
            }

            $("#"+propname+"_anchor").focus(
            	function(){
            		if(propenabled){
            			$("#"+propname+"_cursor").css({"visibility":"visible"});
            			$("#"+propname+"_internal").css({"background-color":globalcolorfocus});
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
            		if(propenabled && !proplock){
            			propctrl=k.ctrlKey; // da usare anche nella press
            			propshift=k.shiftKey;
                        propalt=k.altKey;
                        // GESTIONE CLIPBOARD
                        if(propctrl){
                            switch(k.keyCode){
                            case 88:
            					clipcode=propobj.value();
            					propobj.value("");
                                k.preventDefault();
                                return false;
                            case 67:
            					var v=propobj.value();
            					if(v)
            						clipcode=v;
                                k.preventDefault();
                                return false;
                            case 86:
            					propobj.value(clipcode);
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
            				else if(propstart<propcode.length){
                                if(propctrl){
                                    var m=propcode.substr(propstart).match(/\W+/);
                                    if(m)
                                        propstart+=m.index+m[0].length;
                                    else
                                        propstart=propcode.length;
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
                                    var m=propcode.substr(0, propstart).reverse().match(/(\w+|\W+\w+)/);
                                    if(m)
                                        propstart-=(m.index+m[0].length);
                                    else
                                        propstart=0;
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
            				else if(propstart<propcode.length){
            					propstart=propcode.length;
                                propobj.refreshcursor();
            				}
            			}
            			else if(k.which==46){ // delete
            				if(propctrl){
            					clipcode=propobj.value();
            					propobj.clear();
            				}
            				else{
                                if(propselected){
                                    propobj.clear();
                                    propobj.selected(false);
                                }
                                if(propstart<propcode.length){
                                    propcode=propcode.substr(0,propstart)+propcode.substr(propstart+1);
                                }
            				}
            				propobj.refreshcursor();
                            propobj.raisechanged();
            			}
            			else if(k.which==45){ // ins
            				if(propctrl){
            					var v=propobj.value();
            					if(v)
            						clipcode=v;
            				}
            				else if(propshift){
            					propobj.value(clipcode);                    
            				}
                            else{
                                propobj.insert(!propinsert);
                            }
            			}
            			else if(k.which==113 || (propalt && k.which==50)){ // F2  Alt+2
            				propobj.showdialog();
            			}
            			else if(k.which==13){ // INVIO
                            propobj.selected(false);
                            propobj.completion();
            				propstart=0;
            				propobj.refreshcursor();
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
                            
                            if(propstart>0){
                                propstart-=1;
                                propcode=propcode.substr(0,propstart)+propcode.substr(propstart+1);
                            }
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
            		if(propenabled && !proplock){
                        if(k.which>0){
                            var n=String.fromCharCode(k.which);
                            var u=n.toUpperCase();
                            if(propselected){
                                propobj.clear();
                                propobj.selected(false);
                            }
                            if(propstart<propmaxlen){
                                var ok=false;
                                if(!propinsert || propcode.length<propmaxlen ){
                                    switch(propmode){
                                    case "filled":
                                        ok=("0"<=u && u<="9");
                                        break;
                                    case "system":
                                        ok=("0"<=u && u<="9") || ("A"<=u && u<="Z");
                                        n=u;
                                        break;
                                    case "free":
                                        ok=true;
                                        break;
                                    default:
                                        ok=("0"<=u && u<="9") || ("A"<=u && u<="Z") || n=="_";
                                    }
                                }
                                if(ok){
                                    if( propstart<propmaxlen ){
                                        if(propuppercase)
                                            n=u;
                                        if(propinsert)
                                            propcode=propcode.substr(0,propstart)+n+propcode.substr(propstart);
                                        else
                                            propcode=propcode.substr(0,propstart)+n+propcode.substr(propstart+1);
                                        propstart+=1;
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
            		if(propenabled && !proplock){
            			var p=evt.pageX-propleft;
            			var l,i;
            			propstart=propcode.length;
            			for(i=propcharleft; i<=propcode.length-1; i++){
            				l=propobj.textwidth(propcode.substr(propcharleft, i-propcharleft+1));
            				if(l>p+3){
                                propstart=i;
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
            $("#"+propname+"_button").click(
            	function(){
            		if(propenabled){
            			propobj.showdialog();
            		}
            	}
            );
            $("#"+propname+"_text").contextMenu("rybox_popup", {
            	bindings: {
            		'rybox_cut': function(t) {
            			clipcode=propobj.value();
            			propobj.value("");
            		},
            		'rybox_copy': function(t) {
            			var v=propobj.value();
            			if(v)
            				clipcode=v;
            		},
            		'rybox_paste': function(t) {
            			propobj.value(clipcode);
            		}
            	},
            	onContextMenu:
            		function(e) {
            			if((clipcode==null && propobj.value()=="") || !propenabled)
            				return false;
            			else 
            				return true;
            		},
            	onShowMenu: 
            		function(e, menu) {
            			if(propobj.value()==""){
            				$('#rybox_copy', menu).remove();
            			}
            			if(propobj.value()=="" || proplock){
            				$('#rybox_cut',menu).remove();
            			}
            			if(!clipcode || proplock){
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
			this.maxlen=function(l){
				if(l==missing)
					return propmaxlen;
				else
					propmaxlen=l;
			}
			this.showdialog=function(r){
                if(prophelper){
                    if(settings.dialog!=missing){
                        settings.dialog(propobj);
                    }
                }
			}
			this.refreshcursor=function(){
				var s,x;
                var w=prophelper ? propwidth-24 : propwidth-4;
                if(propcharleft>propstart)
                    propcharleft=propstart;
                if(propcharleft<propstart){
                    s=propcode.substr(propcharleft, propstart-propcharleft);
                    x=propobj.textwidth(s)+1;
                }
                else{
                    x=1;
                }
                if(x>w){
                    do{
                        propcharleft+=1;
                        s=propcode.substr(propcharleft, propstart-propcharleft);
                        x=propobj.textwidth(s)+1;
                    }while(x>w)
                }
                $("#"+propname+"_text").html(propcode.substr(propcharleft).replace(/ /g, "&nbsp;"));
				$("#"+propname+"_cursor").css({"left":x})
			}
			this.textwidth=function(s){
				$("#"+propname+"_span").html(s.replace(/ /g, "&nbsp;"));
				return $("#"+propname+"_span").width();
			}
			this.completion=function(){
                var c=propcode, t;
                switch(propmode){
                case "filled":
                    if(propcode!="" && propcode.length!=propmaxlen){
                        t="00000000000000000000"+propcode;
                        propcode=t.substr(t.length-propmaxlen);
                    }
                    break;
                default:
                    if(propcode.length>propmaxlen){
                        propcode=propcode.substr(0, propmaxlen);
                    }
                }
                if(propcode!=c)
                    propobj.raisechanged();
                propobj.refreshcursor();
			}
			this.value=function(v,a){
				if(v==missing){
					propobj.completion();
                    return propcode;
				}
				else{
                    propobj.raisechanged();
                    propchanged=false;
					try{
						if(v!=""){
                            propcode=v;
                            propobj.completion();
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
					propstart=0;
					propobj.refreshcursor();
				}
			}
			this.text=function(){
				return propcode;
			}
			this.name=function(){
				return propname;
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v.booleanNumber();
					if(propenabled){
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
					propvisible=v.booleanNumber();
					if(propvisible)
						$("#"+propname).css({"visibility":"visible"});
					else
						$("#"+propname).css({"visibility":"hidden"});
				}
			}
			this.mode=function(v){
				if(v==missing){
					return propmode;
				}
				else{
					propmode=v.booleanNumber();
				}
			}
			this.lock=function(v){
				if(v==missing){
					return proplock;
				}
				else{
					proplock=v.booleanNumber();
				}
			}
			this.helper=function(v){
				if(v==missing){
					return prophelper;
				}
				else{
					prophelper=v.booleanNumber();
                    if(prophelper){
                        $("#"+propname+"_text").css({"width":propwidth-25});
                        $("#"+propname+"_button").css({"display":"block"});
                    }
                    else{
                        $("#"+propname+"_text").css({"width":propwidth-5});
                        $("#"+propname+"_button").css({"display":"none"});
                    }
				}
			}
			this.insert=function(v){
				if(v==missing){
					return propinsert;
				}
				else{
					propinsert=v.booleanNumber();
                    $.cookie("codeinsert", propinsert, {expires:10000});
				}
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
                if(propcode=="")
                    propselected=false;
                if(propselected)
                    $("#"+propname+"_text").css({"background-color":"#87CEFA", "color":"white"});
                else
                    $("#"+propname+"_text").css({"background-color":"transparent", "color":"black"});
            }
			this.clear=function(){
				propstart=0;
                propcode="";
                propobj.refreshcursor();
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
            if(!propenabled)
                propobj.enabled(0);
            if(!propvisible)
                propobj.visible(0);
			return this;
		}
	});
})(jQuery);
