/****************************************************************************
* Name:            rytabs.js                                                *
* Project:         Cambusa/ryBox                                            *
* Version:         1.00                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

(function($,missing) {
    $.extend(true,$.fn, {
		rytabs:function(settings){
			var propleft=0;
			var proptop=0;
			var propwidth=0;
			var propheight=0;
            var proptabs=false;
            var propprevtab=-1;
            var propcurrtab=0;
            var propsuspend=false;
			var propobj=this;
			var propvisible=true;
            var propposition="absolute";

			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="tabs";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left};
			if(settings.top!=missing){proptop=settings.top};
			if(settings.width!=missing){propwidth=settings.width};
            if(settings.height!=missing){propheight=settings.height};
            if(settings.tabs!=missing){proptabs=settings.tabs}
            if(settings.position!=missing){propposition=settings.position}
			
            $("#"+propname).addClass("rytabs");
            $("#"+propname).css({position:propposition, left:propleft, top:proptop});
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
            
            if(proptabs){
                for(var i=0;i<proptabs.length;i++)
                    $("#"+propname+"_ul").append("<li><a id='"+propname+"_caption_"+(i+1)+"' href='#"+propname+"-"+(i+1)+"'>"+proptabs[i].title+"</a></li>");
            }

            $("#"+propname).tabs();
            $("#"+propname).tabs("select",0);
            $("#"+propname).tabs({
                select:function(ev,ui){
                    propprevtab=propcurrtab;
                    propcurrtab=ui.index;
                    if(settings.select!=missing){
                        if(!propsuspend)
                            settings.select(propcurrtab+1, propprevtab+1);
                        // In ogni caso lo pongo a false poiché potrebbe essere posto a true dentro la select
                        propsuspend=false;
                    }
                }
            });
            if(settings.select!=missing){
                settings.select(1,0);
            }
            
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
            this.currtab=function(t, s){
                if(t==missing){
                    return propcurrtab+1;
                }
                else{
                    if(s!=missing)
                        propsuspend=s;
                    $("#"+propname).tabs("select", t-1);
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
                    var b=$("#"+propname).tabs("option","disabled");
                    var r=1;
                    for(var i=0;i<b.length;i++){
                        if(b[i]==t-1)
                            r=0;
                    }
					return r;
				}
				else{
                    if(v)
                        $("#"+propname).tabs("enable", t-1);
                    else
                        $("#"+propname).tabs("disable", t-1);
				}
			}
			this.visible=function(v){
				if(v==missing){
					return propvisible;
				}
				else{
					propvisible=v;
					if(v)
						$("#"+propname).show();
					else
						$("#"+propname).hide();
				}
			}
			this.suspend=function(v){
				if(v!=missing)
					propsuspend=v;
                return propsuspend;
			}
			return this;
		}
	});
})(jQuery);
