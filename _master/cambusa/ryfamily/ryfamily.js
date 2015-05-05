/****************************************************************************
* Name:            ryfamily.js                                              *
* Project:         Cambusa/ryFamily                                         *
* Version:         1.69                                                     *
* Description:     Treeview                                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
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
            var propborder=-1;
            var propobj=this;
            var propname=$(this).attr("id");
            var propselectedid="";
            var propselectiontype="all";
            
            if(settings.left!=missing){propleft=settings.left}
            if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.scroll!=missing){propscroll=settings.scroll}
            if(settings.border!=missing){propborder=settings.border}
            if(settings.selectiontype!=missing){propselectiontype=settings.selectiontype}
            
            var sc="visible";
            var bd="none";
            if(propscroll){
                sc="scroll";
                if(propborder==-1)
                    bd="1px solid silver";
            }
            if(propborder==1){
                if(sc=="visible")
                    sc="auto";
                bd="1px solid silver";
            }
            
            $("#"+propname).css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight,"font-family":"verdana,sans-serif","font-size":"13px","line-height":"18px","overflow":sc,"border":bd});
            $("#"+propname).html("<ul id='"+propname+"_root' class='filetree treeview-famfamfam'></ul>");
    
            $("#"+propname+"_root").treeview();

            $("#"+propname).click(
                function(evt){
                    var trig=createtrigger(evt, "ryclick");
                    if(trig){
                        propobj.selectedid(trig.id);
                        if(settings.click){
                            settings.click(propobj, trig);
                        }
                        if(trig.type=="folder" && !(trig.hitfolder && trig.hitnode)){
                            if(trig.open){
                                if(settings.expand){
                                    settings.expand(propobj, trig);
                                }
                            }
                            else{
                                if(settings.collapse){
                                    settings.collapse(propobj, trig);
                                }
                            }
                        }
                    }
                }
            );
            $("#"+propname).contextmenu(
                function(evt){
                    var trig=createtrigger(evt, "rycontext");
                    if(trig){
                        propobj.selectedid(trig.id);
                        if(settings.context){
                            settings.context(propobj, trig);
                        }
                    }
                    else{
                        if(settings.outofcontext){
                            settings.outofcontext(propobj);
                        }
                    }
                }
            );
            
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
                $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth,"height":propheight});
            }
            this.addfolder=function(params){
                var c="closed";
                var parid;
                var info="";
                params.parent=__(params.parent);
                if(params.parent=="")
                    parid=propname+"_root";
                else
                    parid=propname+"_"+params.parent+"_root";
                    
                if(params.id==missing){
                    var lastid=$("#"+parid).prop("lastid");
                    if(lastid==missing)
                        lastid=1;
                    else
                        lastid+=1;
                    $("#"+parid).prop("lastid", lastid);
                    params.id=params.parent+"k"+lastid;
                }
                    
                var id=propname+"_"+params.id;
                if(params.open!=missing){
                    if(params.open)
                        {c="open"};
                }
                if(params.info!=missing)
                    info=params.info;
                var branches = $("<li id='"+id+"' class='"+c+"'><span id='"+id+"_text' rif='"+params.id+"' super='"+params.parent+"' info='"+info+"' class='folder'>"+params.title+"</span><ul id='"+id+"_root'></ul></li>")
                .prop("info", info)
                .appendTo("#"+parid);
                $("#"+parid).treeview({
                    add: branches,
                    rif:params.id
                });
                $("#"+propname+"_"+params.id).click(function(evt){
                    if(evt.screenX-$(this).offset().left<30){
                        evt.stopPropagation();
                        $("#"+propname+"_"+params.id+"_text").click();
                    }
                });
                if(c=="open"){
                    if(settings.expand){
                        var trig=propobj.getinfo(params.id);
                        settings.expand(propobj, trig);
                    }
                }
                return params.id;
            }
            this.additem=function(params){
                var parid;
                var info="";
                params.parent=__(params.parent);
                if(params.parent=="")
                    parid=propname+"_root";
                else
                    parid=propname+"_"+params.parent+"_root";

                if(params.id==missing){
                    var lastid=$("#"+parid).prop("lastid");
                    if(lastid==missing)
                        lastid=1;
                    else
                        lastid+=1;
                    $("#"+parid).prop("lastid", lastid);
                    params.id=params.parent+"k"+lastid;
                }
                    
                var id=propname+"_"+params.id;
                if(params.info!=missing)
                    info=params.info;
                var branches = $("<li id='"+id+"'><span id='"+id+"_text' rif='"+params.id+"' super='"+params.parent+"' class='file'>"+params.title+"</span></li>")
                .prop("info", info)
                .appendTo("#"+parid);
                $("#"+parid).treeview({
                    add: branches,
                    rif:params.id
                });
                return params.id;
            }
            this.remove=function(id){
                id=propname+"_"+id+"_root";
                $("#"+id).prop("lastid", 0);
                $("#"+id).html("");
            }
            this.clear=function(){
                $("#"+propname+"_root").prop("lastid", 0);
                $("#"+propname+"_root").html("");
            }
            this.name=function(){
                return propname;
            }
            this.setinfo=function(nodeid, options){
                var selector="#"+propname+"_"+nodeid;
                if(options.info!=missing)
                    $(selector).prop("info", options.info);
                if(options.text!=missing)
                    $(selector+"_text").html(options.text);
            }
            this.getinfo=function(nodeid){
                var id,open,info,type,text,parent,selector;
                id=$("#"+propname+"_"+nodeid+"_text").attr("rif");
                selector="#"+propname+"_"+id;
                parent=$(selector+"_text").attr("super");
                if($(selector+"_text").hasClass("folder") || $(selector+"_text").hasClass("hitarea")){
                    open=$(selector).hasClass("collapsable");
                    type="folder";
                }
                else{
                    open==false;
                    type="file";
                }
                info=$(selector).prop("info");
                text=__($(selector+"_text").html()).stripTags();
                return {id:id, info:info, open:open, parent:parent, selector:selector, text:text, type:type, hitnode:false, hitfolder:false};
            }
            this.getpath=function(nodeid){
                var v=[];
                try{
                    while(nodeid.substr(0,1)=="k"){
                        if(nodeid!="k1")
                            v.push($("#"+propname+"_"+nodeid).prop("info"));
                        nodeid=$("#"+propname+"_"+nodeid+"_text").attr("super");
                    }
                }
                catch(e){}
                v.reverse();
                return v;
            }
            this.nextchild=function(parentid){
                if(parentid!=""){
                    var parid=propname+"_"+parentid+"_root";
                    var lastid=$("#"+parid).prop("lastid");
                    if(lastid==missing)
                        lastid=1;
                    else
                        lastid+=1;
                    return parentid+"k"+lastid;
                }
                else{
                    return "k1";
                }
            }
            this.selectedid=function(nodeid){
                if(nodeid!=missing){
                    $(".folder,.file").removeClass("ryfamily-current");
                    var node=$("#"+propname+"_"+nodeid+"_text");
                    if(nodeid!="" && node.length>0){
                        var f=(node.hasClass("folder") || node.hasClass("hitarea"));
                        var s=false;
                        switch(propselectiontype){
                        case "all":
                            s=true;
                            break;
                        case "folder":
                            if(f){
                                s=true;
                            }
                            else{
                                var parent=node.attr("super");
                                if(parent){
                                    nodeid=parent;
                                    s=true;
                                }
                            }
                            break;
                        case "file":
                            if(!f)
                                s=true;
                            break;
                        }
                        if(s){
                            propselectedid=nodeid;
                            $("#"+propname+"_"+nodeid+"_text").addClass("ryfamily-current");
                        }
                    }
                    else{
                        propselectedid="";
                    }
                }
                return propselectedid;
            }
            this.expand=function(nodeid){
                $("#"+propname+"_"+nodeid)
                .removeClass("closed expandable lastExpandable")
                .addClass("open collapsable lastCollapsable");
                $("#"+propname+"_"+nodeid+"_root").show();
            }
            this.collapse=function(nodeid){
                $("#"+propname+"_"+nodeid)
                .removeClass("open collapsable lastCollapsable")
                .addClass("closed expandable lastExpandable");
                $("#"+propname+"_"+nodeid+"_root").hide();
            }
            function createtrigger(evt, name){
                var id,open,info,type,text,parent,selector;
                var trig=false;
                if($(evt.target).hasClass("folder") || $(evt.target).hasClass("hitarea")){
                    id=$(evt.target).attr("rif");
                    parent=$(evt.target).attr("super");
                    selector="#"+propname+"_"+id;
                    open=$(selector).hasClass("collapsable");
                    info=$(selector).prop("info");
                    text=__($(selector+"_text").html()).stripTags();
                    type="folder";
                }
                else{
                    id=$(evt.target).attr("rif");
                    parent=$(evt.target).attr("super");
                    if(id==missing){
                        $.each( $(evt.target).parents(), 
                            function(key, value){
                                id=$(value).attr("rif");
                                parent=$(value).attr("super");
                                if(id!=missing)
                                    return false;
                            }
                        );
                    }
                    if(id!=missing){
                        selector="#"+propname+"_"+id;
                        open=false;
                        info=$(selector).prop("info");
                        text=__($(selector+"_text").html()).stripTags();
                        type="file";
                    }
                }
                var hitnode=(evt.isTrigger!=missing);
                var hitfolder=$(evt.target).hasClass("hover");
                if(id!=missing){
                    trig={id:id, info:info, open:open, parent:parent, selector:selector, text:text, type:type, hitnode:hitnode, hitfolder:hitfolder};
                    $("#"+propname).trigger(name, trig);
                }
                return trig;
            }
			return this;
		}
	});
})(jQuery);
