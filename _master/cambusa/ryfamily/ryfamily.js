/****************************************************************************
* Name:            ryfamily.js                                              *
* Project:         Cambusa/ryFamily                                         *
* Version:         1.00                                                     *
* Description:     Treeview                                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
        ryfamily:function(settings){
            var propleft=20;
            var proptop=20;
            var propwidth=200;
            var propheight=400;
            var propscroll=1;
            var propobj=this;
            var propcreated=false;
            var propname=$(this).attr("id");
            
            if(settings.left!=missing){propleft=settings.left}
            if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.scroll!=missing){propscroll=settings.scroll}
            
            this.left=function(l){
                if(l==missing)
                    return propleft;
                else
                    propleft=l;
                propobj.refreshattr();
            }
            this.top=function(t){
                if(t==missing)
                    return proptop;
                else
                    proptop=t;
                propobj.refreshattr();
            }
            this.width=function(w){
                if(w==missing)
                    return propwidth;
                else
                    propwidth=w;
                propobj.refreshattr();
            }
            this.height=function(h){
                if(h==missing)
                    return propheight;
                else
                    propmaxlen=l;
                propobj.refreshattr();
            }
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                if(params.height!=missing){propheight=params.height}
                propobj.refreshattr();
            }
            this.refreshattr=function(){
                if(propcreated){
                    $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth,"height":propheight});
                }    
            }
            if(!propcreated){
                var sc="visible";
                var bd="none";
                if(propscroll){
                    sc="scroll";
                    bd="1px solid silver";
                }
                
                $("#"+propname).css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight,"font-family":"verdana,helvetica,arial,sans-serif","font-size":"13px","overflow":sc,"border":bd});
                $("#"+propname).html("<ul id='"+propname+"_root' class='filetree treeview-famfamfam'></ul>");
        
        		$("#"+propname+"_root").treeview();
                
                propcreated=true;
            }
            this.addfolder=function(params){
                var c="closed";
                var parid;
                var info="";
                if(params.parent=="" || params.parent==missing)
                    parid=propname+"_root";
                else
                    parid=propname+"_"+params.parent+"_root";
                id=propname+"_"+params.id;
                if(params.open!=missing){
                    if(params.open)
                        {c="open"};
                }
                if(params.info!=missing)
                    info=params.info;
                var branches = $("<li id='"+id+"' class='"+c+"'><span id='"+id+"_text' rif='"+params.id+"' super='"+params.parent+"' info='"+info+"' class='folder'>"+params.title+"</span><ul id='"+id+"_root'></ul></li>")
                .appendTo("#"+parid);
                $("#"+parid).treeview({
                    add: branches,
                    rif:params.id
                });
            }
            this.additem=function(params){
                var parid;
                if(params.parent=="" || params.parent==missing)
                    parid=propname+"_root";
                else
                    parid=propname+"_"+params.parent+"_root";
                id=propname+"_"+params.id;
                var branches = $("<li id='"+id+"'><span id='"+id+"_text' rif='"+params.id+"' super='"+params.parent+"' class='file'>"+params.title+"</span></li>")
                .appendTo("#"+parid);
                $("#"+parid).treeview({
                    add: branches,
                    rif:params.id
                });        
            }
            this.remove=function(id){
                $()
                id=propname+"_"+id;
                $("#"+id+"_root").html("");
            }
            this.name=function(){
                return propname;
            }
			return this;
		}
	});
})(jQuery);
