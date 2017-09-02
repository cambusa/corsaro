/****************************************************************************
* Name:            ryframe.js                                               *
* Project:         Cambusa/ryBox                                            *
* Version:         v2.0                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2017  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
        ryframe:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=400;
			var propheight=300;
            var propmaxlen=50;
			var propbabelcode=__($(this).attr("babelcode"));
			var propcaption="";
			var propobj=this;
			var propenabled=true;
			var propvisible=true;
			var propautocoding=false;
			var propcontrols=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="frame";
			
			globalobjs[propname]=this;
			
			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
			if(settings.height!=missing){propheight=settings.height}
			if(settings.caption!=missing){propcaption=settings.caption}
			if(settings.code!=missing){propbabelcode=settings.code}
            if(settings.enabled!=missing){propenabled=settings.enabled}
            if(settings.visible!=missing){propvisible=settings.visible}
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
            if(settings.tag!=missing){this.tag=settings.tag}
			
			$("#"+propname)
            //.addClass("ryobject")
            .addClass("ryframe")
            .css({
                "left":propleft,
                "top":proptop,
                "width":propwidth,
                "height":propheight
            })
			.append("<div class='ryframe-caption'>"+propcaption+"</div>");
			
			if(propcaption=="")
				$("#"+propname+" .ryframe-caption").hide();
			
            // FUNZIONI PUBBLICHE
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
				if(params.height!=missing){propheight=params.height}
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth,"height":propheight});
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
			this.caption=function(v){
				if(v==missing){
					return propcaption;
				}
				else{
					propcaption=v;
					$("#"+propname+" .ryframe-caption").html(v);
					if(v!="")
						$("#"+propname+" .ryframe-caption").show();
					else
						$("#"+propname+" .ryframe-caption").hide();
				}
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					var c=propobj.controls();
					propenabled=v.booleanNumber();
					if(propenabled){
						$("#"+propname+" .ryframe-caption").css({"color":"black"});
						for(var k in c){
							if(c[k].prop("original_enabled"))
								c[k].enabled(1);
						}
					}
					else{
						$("#"+propname+" .ryframe-caption").css({"color":"gray"});
						for(var k in c){
							c[k].prop("original_enabled", c[k].enabled());
							c[k].enabled(0);
						}
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
			this.controls=function(){
				if(propcontrols===false){
					propcontrols=[];
					$("#"+propname).find(".ryobject").each(
						function(k){
							var id;
							if(id=this.id){
								var o=globalobjs[id];
								if(o){
									switch(o.type){
										case "button":
										case "label":
										case "frame":
										case "list":
										case "grid":
										case "text":
										case "date":
										case "time":
										case "number":
										case "check":
										case "code":
										case "helper":
										propcontrols.push(o);
									}
								}
							}
						}
					);
				}
				return propcontrols;
			}
            if(!propenabled)
                propobj.enabled(0);
            if(!propvisible)
                propobj.visible(0);
			return this;
		},
        ryquadrants:function(settings){
			var propleft=5;
			var proptop=31;
			var propright=5;
			var propbottom=5;
			var propratiox=0.5;
			var propratioy=0.5;
			var proporientation=0;
			var propformid="";

			var propobj=this;
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="quadrants";
			
			globalobjs[propname]=this;
			
			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.right!=missing){propright=settings.right}
			if(settings.bottom!=missing){propbottom=settings.bottom}
			if(settings.ratiox!=missing){propratiox=settings.ratiox}
			if(settings.ratioy!=missing){propratioy=settings.ratioy}
			if(settings.orientation!=missing){proporientation=settings.orientation}
			
            if(settings.formid!=missing){
				propformid=settings.formid;
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.tag!=missing){this.tag=settings.tag}
			
			var m=$("#window_"+propformid);
			var height=m.height()-56-proptop-propbottom;
			
			
			$("#"+propname)
            .addClass("ryquadrants")
            .css({
                "left":propleft,
                "top":proptop,
                "right":propright,
                "height":height
            })
			.append("<div class='ryquadrants-hbar'></div>")
			.append("<div class='ryquadrants-vbar'></div>")
			.append("<div class='ryquadrants-windrose'></div>");
			
            $("#"+propname+" > div").each(
                function(i){
					var suffix="";
					switch(i){
						case 0:
							$(this).attr({id:(propname+"-II")}).css({});
							break;
						case 1:
							$(this).attr({id:(propname+"-I")});
							break;
						case 2:
							$(this).attr({id:(propname+"-III")});
							break;
						case 3:
							$(this).attr({id:(propname+"-IV")});
							break;
					}
                }
            );
			
			
            // FUNZIONI PUBBLICHE
			this.name=function(){
				return propname;
			}
			return this;
		}
	});
})(jQuery);
