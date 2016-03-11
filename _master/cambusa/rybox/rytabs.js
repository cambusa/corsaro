/****************************************************************************
* Name:            rytabs.js                                                *
* Project:         Cambusa/ryBox                                            *
* Version:         1.69                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

(function($,missing) {
    $.extend(true,$.fn, {
		rytabs:function(settings){
			var propleft=0;
			var proptop=0;
			var propwidth=0;
			var propheight=0;
            var proptabs=[];
            var propprevtab=-1;
            var propcurrtab=0;
            var propmintop=0;
            var propmaxtop=0;
			var propobj=this;
			var propvisible=true;
            var propcollapsible=false;
            var propcollapsed=false;
            var proplocked=false;
            var propposition="absolute";
            var propcolorsel="gray";
            var propformid="";
            var propclosable=false;

			var propname=$(this).attr("id");
            var propcustomleft=propname+"_customleft";
            var propcustomright=propname+"_customright";
            
			this.id="#"+propname;
			this.tag=null;
			this.type="tabs";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){
                proptop=settings.top;
                propmaxtop=proptop;
            }
            if(proptop>10)
                propcollapsible=true;
			if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.mintop!=missing){propmintop=settings.mintop}
            if(settings.maxtop!=missing){
                propmaxtop=settings.maxtop;
                if(propmaxtop>10)
                    propcollapsible=true;
            }
            if(settings.tabs!=missing){proptabs=settings.tabs}
            if(settings.collapsible!=missing){propcollapsible=settings.collapsible}
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            propformid=__($("#"+propname).prop("parentid"));
            if(propformid!=""){
                if(!RYWINZ.Forms(propformid).options.controls && propformid!="rudder")
                    propclosable=true;
            }
            if(settings.closable!=missing){propclosable=settings.closable}
            if(!RYWINZ.MDI()){
                propclosable=false;
            }
			
            $("#"+propname).addClass("rytabs");
            $("#"+propname).addClass("ryobject");
            $("#"+propname).css({position:propposition, left:propleft, top:proptop, width:"100%"});
            if(propwidth>0)
                $("#"+propname).css({width:propwidth});
            if(propheight>0)
                $("#"+propname).css({height:propheight});
            
            $("#"+propname+" > div").each(
                function(i){
                    $(this).attr({id:(propname+"-"+(i+1))}).css({padding:"0px",margin:"0px",overflow:"hidden"});
                    if(propwidth>0)
                        $(this).attr({id:(propname+"-"+(i+1))}).css({width:propwidth-10});
                    if(propheight>0)
                        $(this).attr({id:(propname+"-"+(i+1))}).css({height:propheight-50});
                }
            );
            
            $("#"+propname).prepend("<ul id='"+propname+"_ul'></ul>");
            $("#"+propname+"_ul").addClass("rytabs-bar");
            
            for(var i=0;i<proptabs.length;i++){
                $("#"+propname+"_ul").append("<li id='"+propname+"_caption_"+(i+1)+"'>"+proptabs[i].title+"</li>");
                $("#"+propname+"_caption_"+(i+1)).css({"float":"left", "padding":"2px 25px 2px", "top":5, "height":25, "cursor":"pointer", "border":"none", "color":"black", "white-space":"nowrap"})
                .prop("_index", i+1)
                .click(
                    function(){
                        propobj.currtab($(this).prop("_index"));
                    }
                );
                proptabs[i].enabled=1;
                proptabs[i].key="";
            }
            
            var r=10;
            if(propclosable){
                $("#"+propname+"_ul").append("<li id='"+propname+"_formclose' class='winz_close' style='position:absolute;width:30;height:25;right:10px;top:2px;cursor:pointer;font-size:11px;'>X</li>");
                $("#"+propname+"_formclose").click(function(){
                    RYWINZ.FormClose(propformid);
                })
                r+=30;
            }
            if(propcollapsible){
                $("#"+propname+"_ul").append("<li id='"+propname+"_collapse' style='position:absolute;width:30;height:25;right:"+r+"px;cursor:pointer;'>&#8593;&#8593;&#8593;</li>");
                $("#"+propname+"_collapse").click(function(){
                    if(!proplocked)
                        propobj.collapsed(!propcollapsed);
                })
                r+=50;
            }
            $("#"+propname+"_ul").append("<li style='float:left;padding:0px 25px 0px;'><div id='"+propname+"_customleft'></div></li>");
            $("#"+propname+"_ul").append("<li style='position:absolute;right:"+r+"px;'><div id='"+propname+"_customright'></div></li>");

            $("#"+propname+">div").each(
                function(index){
                    $(this).css({"position":"absolute", "left":0, "top":0, "width":"100%", "overflow":"visible", "display":(index==0?"block":"none")});
                }
            );
            
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
			this.customleft=function(){
				return propcustomleft;
			}
			this.customright=function(){
				return propcustomright;
			}
            this.currtab=function(t, s){
                if(t==missing){
                    return propcurrtab+1;
                }
                else if(proptabs.length>0){
                    var suspendselect=false;
                    if(proptabs[t-1].enabled){
                        var ok=true;
                        if(settings.before!=missing){
                            ok=settings.before(propcurrtab+1, t);
                        }
                        if(ok!==false){
                            if(s!=missing)
                                suspendselect=s;
                            for(var i=0;i<proptabs.length;i++){
                                var bg="transparent";
                                var fg="black";
                                if(!proptabs[i].enabled)
                                    fg="silver";
                                if(i==t-1){
                                    bg=propcolorsel;
                                    fg="white";
                                }
                                $("#"+propname+"_caption_"+(i+1)).css({"background-color":bg, "color":fg});
                                
                                if(i==t-1)
                                    $("#"+propname+"_caption_"+(i+1)).addClass("rytabs-selected");
                                else
                                    $("#"+propname+"_caption_"+(i+1)).removeClass("rytabs-selected");
                            }
                            $("#"+propname+">div").each(
                                function(index){
                                    $(this).css({"display":(index==t-1 ? "block" : "none")});
                                }
                            );
                            propprevtab=propcurrtab;
                            propcurrtab=t-1;
                            if(settings.select!=missing){
                                if(!suspendselect){
                                    setTimeout(function(){
                                        settings.select(propcurrtab+1, propprevtab+1);
                                    });
                                }
                            }
                        }
                    }
                }
            }
            this.prevtab=function(){
                return propprevtab+1;
            }
			this.tabs=function(){
                return proptabs.length;
			}
			this.caption=function(k,c){
				if(c==missing)
					return $("#"+propname+"_caption_"+k).html();
				else
					$("#"+propname+"_caption_"+k).html(c);
			}
			this.babelcode=function(k){
                if(proptabs){
                    if(proptabs[k-1].code==missing)
                        return "";
                    else
                        return proptabs[k-1].code;
                }
                else{
                    return "";
                }
			}
			this.enabled=function(t,v){
				if(v==missing){
					return proptabs[t-1].enabled;
				}
				else{
                    proptabs[t-1].enabled=v.booleanNumber();
                    $("#"+propname+"_caption_"+t).css({"color":(v ? (propcurrtab==t-1 ? "white" : "black" ) : "silver"), "cursor":(v ? "pointer" : "default")});

                    // Gestisco il caso di tab corrente che viene disabilitato
                    if(propcurrtab==t){
                        var f=0;
                        for(var i in proptabs){
                            if(proptabs[i].enabled && f==0){
                                f=i+1;
                            }
                        }
                        if(f==0){
                            f=1;
                            propobj.enabled(1, 1);
                        }
                        setTimeout(
                            function(){
                                propobj.currtab(f);
                            }
                        );
                    }
				}
			}
			this.visible=function(v){
				if(v==missing){
					return propvisible;
				}
				else{
					propvisible=v.actualBoolean();
					if(v)
						$("#"+propname).show();
					else
						$("#"+propname).hide();
				}
			}
			this.closable=function(){
                return propclosable;
			}
			this.collapsed=function(v){
				if(v==missing){
					return propcollapsed;
				}
				else{
					propcollapsed=v;
                    if( propcollapsed ){
                        $("#"+propname).css("top", propmintop);
                        $("#"+propname+"_collapse").html("&#8595;&#8595;&#8595;");
                    }
                    else{
                        $("#"+propname).css("top", propmaxtop);
                        $("#"+propname+"_collapse").html("&#8593;&#8593;&#8593;");
                    }
                    if(settings.toggle)
                        settings.toggle(propcollapsed, propcollapsed ? "none" : "block");
				}
			}
            this.locked=function(v){
				if(v==missing){
					return proplocked;
				}
				else{
                    proplocked=v;
                    if(proplocked)
                        $("#"+propname+"_collapse").css({"color":"silver", "cursor":"default"});
                    else
                        $("#"+propname+"_collapse").css({"color":"black", "cursor":"pointer"});
                }
            }
			this.keys=function(k){
                for(var i=1; i<arguments.length; i++){
                    proptabs[arguments[i]-1].key=k;
                }
			}
			this.clear=function(){
                for(var i=0; i<proptabs.length; i++){
                    proptabs[i].key="";
                }
			}
			this.key=function(k, t){
                return proptabs[t-1].key;
			}
			this.ifother=function(k, t){
                var v=true;
                try{
                    if(proptabs[t-1].key!="")
                        v=(proptabs[t-1].key!=k);
                }catch(e){
                    if(window.console){console.log(e.message)}
                }
                return v;
			}
            this.next=function(){
                var t=propcurrtab+1;
                do{
                    if(t<proptabs.length)
                        t+=1;
                    else
                        t=1;
                }while(!proptabs[t-1].enabled);
                propobj.currtab(t);
            }
            this.previous=function(){
                var t=propcurrtab+1;
                do{
                    if(t>1)
                        t-=1;
                    else
                        t=proptabs.length;
                }while(!proptabs[t-1].enabled);
                propobj.currtab(t);
            }
            propobj.currtab(1);
            if(settings.toggle){
                setTimeout(function(){
                    settings.toggle(propcollapsed, propcollapsed ? "none" : "block");
                });
            }
			return this;
		}
	});
})(jQuery);
