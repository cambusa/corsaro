/****************************************************************************
* Name:            ryscript.js                                              *
* Project:         Cambusa/ryBox                                            *
* Version:         1.70                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2016  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
        ryscript:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=600;
			var propheight=400;
			var propfocusout=true;
            var propchanged=false;
			var propenabled=1;
			var propvisible=true;
            var propobj=this;
            var proploaded=false;
            
            var propmode="javascript";
            var propindent=4;
            var propintellisense=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="script";
            
            var pendingvalue="";
            var pendingmode="";
            var pendingindent=-1;
            var pendingintellisense=false;

			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.mode!=missing){propmode=settings.mode}
            if(settings.indent!=missing){propindent=settings.indent}

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
            .addClass("ryscript")
            .css({
                "position":"absolute",
                "left":propleft,
                "top":proptop,
                "width":propwidth,
                "height":propheight,
                "overflow":"hidden",
                "color":"transparent",
                "background-color":"white",
                "font-family":"verdana,sans-serif",
                "font-size":"13px",
                "line-height":"17px",
                "cursor":"default"
            })
            .html("<iframe id='"+propname+"_frame' src='' width='"+(propwidth-2)+"px' height='"+(propheight-2)+"px' frameborder='0'></iframe>");
            
            $("#"+propname+"_frame").css({position:"absolute", left:1, top:1});
            
			this.value=function(v,a){
				if(v==missing){
                    return document.getElementById(propname+"_frame").contentWindow.getvalue();
				}
				else{
                    if(proploaded){
                        propchanged=false;
                        document.getElementById(propname+"_frame").contentWindow.setvalue(v);
                        if(a==missing){a=false}
                        if(a){propobj.raiseassigned()}
                    }
                    else{
                        pendingvalue=v;
                    }
				}
			}
			this.mode=function(v){
				if(v==missing){
                    return propmode;
				}
				else{
                    if(proploaded){
                        propmode=v;
                        document.getElementById(propname+"_frame").contentWindow.setmode(v);
                    }
                    else{
                        pendingmode=v;
                    }
				}
			}
			this.indent=function(v){
				if(v==missing){
                    return propindent;
				}
				else{
                    if(proploaded){
                        propindent=v;
                        document.getElementById(propname+"_frame").contentWindow.setindent(v);
                    }
                    else{
                        pendingindent=v;
                    }
				}
			}
			this.intellisense=function(v){
                if(proploaded){
                    propintellisense=v;
                    document.getElementById(propname+"_frame").contentWindow.setintellisense(v);
                }
                else{
                    pendingintellisense=v;
                }
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
                    document.getElementById(propname+"_frame").contentWindow.setenabled(propenabled);
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
            
			}
			this.focus=function(){
                document.getElementById(propname+"_frame").contentWindow.setfocus();
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
            this.raiseload=function(){
                proploaded=true;
                if(pendingvalue!=""){
                    propobj.value(pendingvalue);
                }
                if(pendingmode!=""){
                    propobj.mode(pendingmode);
                }
                if(pendingindent>=0){
                    propobj.indent(pendingindent);
                }
                if(pendingintellisense!=false){
                    propobj.intellisense(pendingintellisense);
                }
                pendingvalue="";
                pendingmode="";
                pendingindent=-1;
                pendingintellisense=false;
                TAIL.free();
                if(settings.onload)
                    settings.onload();
            }
            TAIL.enqueue(function(){
                $("#"+propname+"_frame").attr("src", _systeminfo.relative.cambusa+"rybox/ryscript.php?mode="+propmode+"&indent="+propindent+"&name="+propname+"&var="+(new Date().getTime()));
            });
            TAIL.wriggle();
            
			return this;
		}
	});
})(jQuery);
