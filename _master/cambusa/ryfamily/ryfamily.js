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
            var propheight=false;
            var propright=false;
            var propbottom=false;
            var propscroll=true;
            var propborder=false;
            var propbackground="";
            var propclassname=false;
            var propobj=this;
            var propselectedid="";
            var propselectiontype="all";
            var propenabled=true;
            var propvisible=true;
            var propzoom="";

            var propname=$(this).attr("id");
            this.id="#"+propname;
            this.tag=null;
            this.type="tree";

            globalobjs[propname]=this;
            
            if(settings.left!=missing){propleft=settings.left}
            if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.right!=missing){propright=settings.right}
            if(settings.bottom!=missing){propbottom=settings.bottom}
            if(settings.scroll!=missing){propscroll=settings.scroll}
            if(settings.border!=missing){propborder=settings.border}
            if(settings.background!=missing){propbackground=settings.background}
            if(settings.classname!=missing){propclassname=settings.classname}
            if(settings.selectiontype!=missing){propselectiontype=settings.selectiontype}
            if(settings.zoom!=missing && settings.zoom!=""){propzoom="transform:scale("+settings.zoom+");transform-origin:0 0;"}
            
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.tag!=missing){this.tag=settings.tag}
            
            
            var sc="visible";
            var bd="none";
            if(propscroll){
                sc="scroll";
                if(!propborder)
                    bd="1px solid silver";
            }
            if(propborder){
                if(sc=="visible")
                    sc="auto";
                bd="1px solid silver";
            }
            if(propbackground!="" && sc=="visible" && propheight!==false)
                sc="auto";
            
            $("#"+propname)
            .addClass("ryobject")
            .addClass("ryfamily")
            .css({"position":"absolute","font-family":"verdana,sans-serif","font-size":"13px","line-height":"18px","overflow":sc,"border":bd});
            $("#"+propname).html("<div style='position:absolute;border:none;left:0px;top:0px;width:1px;height:1px;overflow:hidden;'><input type='button' id='"+propname+"_anchor' style='position:absolute;left:-100px;width:10px;'></div><ul id='"+propname+"_root' class='filetree treeview-famfamfam' style='"+propzoom+"'></ul>");
            refreshattr();
            
            if(propborder){
                $("#"+propname).addClass("rybox-border");
            }
            
            if(propbackground!=""){
                $("#"+propname).css({"background-color":propbackground});
                $("#"+propname+" .treeview li").css({"background-color":propbackground});
                $("#"+propname+" .treeview a.selected").css({"background-color":propbackground});
            }
            if(propclassname!==false){
                $("#"+propname).addClass(propclassname);
            }
            
            $("#"+propname+"_root").treeview();
            
            $("#"+propname+"_anchor").focus(function(){
                if(propborder)
                    $("#"+propname).addClass("rybox-focus");
                    //$("#"+propname).css({"border-left":"1px solid #3F75A2"});
            });
            $("#"+propname+"_anchor").focusout(function(){
                if(propborder)
                    $("#"+propname).removeClass("rybox-focus");
                    //$("#"+propname).css({"border-left":bd});
            });
            $("#"+propname+"_anchor").keydown(
                function(k){
                    if(propenabled){
                        // GESTIONE ALTRI TASTI
                        switch(k.which){
                        case 40:    // DOWN (sibling inferiore)
                            if(propselectedid==""){
                                propobj.selectedid("k1");
                            }
                            else{
                                var parid=$("#"+propname+"_"+propselectedid+"_text").attr("super");
                                parid=propname+"_"+parid+"_root";
                                var lastid=$("#"+parid).prop("lastid");
                                var m=propselectedid.match(/^(.*k)(\d+)$/);
                                var b=m[1];
                                var n=m[2].actualInteger();
                                for(var i=n+1; i<=lastid; i++){
                                    if($("#"+propname+"_"+b+i).length>0){
                                        propobj.selectedid(b+i);
                                        k.preventDefault();
                                        break;
                                    }
                                }
                            }
                            break;
                        case 38:    // UP (sibling superiore)
                            if(propselectedid==""){
                                propobj.selectedid("k1");
                            }
                            else{
                                var parid=$("#"+propname+"_"+propselectedid+"_text").attr("super");
                                parid=propname+"_"+parid+"_root";
                                var lastid=$("#"+parid).prop("lastid");
                                var m=propselectedid.match(/^(.*k)(\d+)$/);
                                var b=m[1];
                                var n=m[2].actualInteger();
                                for(var i=n-1; i>=1; i++){
                                    if($("#"+propname+"_"+b+i).length>0){
                                        propobj.selectedid(b+i);
                                        k.preventDefault();
                                        break;
                                    }
                                }
                            }
                            break;
                        case 39:    // RIGHT (figlio)
                            if(propselectedid==""){
                                propobj.selectedid("k1");
                            }
                            else{
                                var node=propname+"_"+propselectedid+"_root";
                                var lastid=$("#"+node).prop("lastid");
                                for(var i=1; i<=lastid; i++){
                                    if($("#"+propname+"_"+propselectedid+"k"+i).length>0){
                                        propobj.selectedid(propselectedid+"k"+i);
                                        k.preventDefault();
                                        break;
                                    }
                                }
                            }
                            break;
                        case 37:    // LEFT (padre)
                            if(propselectedid==""){
                                propobj.selectedid("k1");
                            }
                            else if(propselectedid!="k1"){
                                var parid=$("#"+propname+"_"+propselectedid+"_text").attr("super");
                                propobj.selectedid(parid);
                                k.preventDefault();
                            }
                            break;
                        case 13:    // INVIO
                            if(propselectedid!=""){
                                var trig=propobj.getinfo(propselectedid);
                                if(settings.click){
                                    settings.click(propobj, trig);
                                }
                                if(trig.folder){
                                    if(trig.open)
                                        propobj.collapse(propselectedid);
                                    else
                                        propobj.expand(propselectedid);
                                }
                            }
                            break;
                        }
                    }
                    if(k.which==8){
                        return false;
                    }
                    else if(k.which==9){
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
            $("#"+propname).click(function(evt){
				var trig=createtrigger(evt, "ryclick");
                
                if(trig){
					if($.isset(trig.id)){
						// sposto il focusable per evitare lo scroll all'inizio dandogli il fuoco
						if(propborder)
							$("#"+propname+"_anchor").parent().css({"top":$("#"+propname).scrollTop()});
						else
							$("#"+propname+"_anchor").parent().css({"top":$("#"+propname+"_"+trig.id).position().top});
						propobj.selectedid(trig.id);
						if(settings.click){
							settings.click(propobj, trig);
						}
                        if(trig.folder){
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
				$("#"+propname+"_anchor").focus();
            });
            $("#"+propname).contextmenu(function(evt){
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
            });
            this.refreshattr=function(){
                refreshattr();
            }
            this.left=function(v){
                if(v==missing)
                    return propleft;
                else
                    propleft=v;
                propobj.refreshattr();
            }
            this.top=function(v){
                if(v==missing)
                    return proptop;
                else
                    proptop=v;
                propobj.refreshattr();
            }
            this.width=function(v){
                if(v==missing)
                    return propwidth;
                else
                    propwidth=v;
                propobj.refreshattr();
            }
            this.height=function(v){
                if(v==missing)
                    return propheight;
                else
                    propheight=v;
                propobj.refreshattr();
            }
            this.right=function(v){
                if(v==missing)
                    return propright;
                else
                    propright=v;
                propobj.refreshattr();
            }
            this.bottom=function(v){
                if(v==missing)
                    return propbottom;
                else
                    propbottom=v;
                propobj.refreshattr();
            }
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                if(params.height!=missing){propheight=params.height}
                if(params.right!=missing){propright=params.right}
                if(params.bottom!=missing){propbottom=params.bottom}
                propobj.refreshattr();
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
                    
                // determinazione id
                var lastid=$("#"+parid).prop("lastid");
                if(lastid==missing)
                    lastid=1;
                else
                    lastid+=1;
                $("#"+parid).prop("lastid", lastid);
                params.id=params.parent+"k"+lastid;
                    
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
                    rif:params.id,
					toggle:function(){
                        /*
						setTimeout(function(){
							var trig=propobj.getinfo(propselectedid);
							if(trig.folder){
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
						}, 50);
                        */
					}
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
                $("#"+propname).find("li").css({"font-family":"verdana,sans-serif","font-size":"13px","line-height":"18px"});
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

                // determinazione id
                var lastid=$("#"+parid).prop("lastid");
                if(lastid==missing)
                    lastid=1;
                else
                    lastid+=1;
                $("#"+parid).prop("lastid", lastid);
                params.id=params.parent+"k"+lastid;
                    
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
                $("#"+propname).find("li").css({"font-family":"verdana,sans-serif","font-size":"13px","line-height":"18px"});
                return params.id;
            }
            this.remove=function(id){
                id=propname+"_"+id;
                $("#"+id).remove();
                if(id==propselectedid){
                    propobj.selectedid("");
                }
            }
            this.clear=function(id){
                if(id==missing)
                    id=propname+"_root";
                else
                    id=propname+"_"+id+"_root";
                $("#"+id).prop("lastid", 0);
                $("#"+id).html("");
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
                return {id:id, info:info, open:open, parent:parent, selector:selector, text:text, type:type, folder:(type=="folder"), hitnode:false, hitfolder:false};
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
                var trig=propobj.getinfo(nodeid);
                if(trig.folder){
                    $("#"+propname+"_"+nodeid)
                    .removeClass("closed expandable lastExpandable")
                    .addClass("open collapsable lastCollapsable");
                    $("#"+propname+"_"+nodeid+"_root").show();
                    if(settings.expand){
                        trig.open=true;
                        settings.expand(propobj, trig);
                    }
                }
            }
            this.collapse=function(nodeid){
                var trig=propobj.getinfo(nodeid);
                if(trig.folder){
                    $("#"+propname+"_"+nodeid)
                    .removeClass("open collapsable lastCollapsable")
                    .addClass("closed expandable lastExpandable");
                    $("#"+propname+"_"+nodeid+"_root").hide();
                    if(settings.collapse){
                        trig.open=false;
                        settings.collapse(propobj, trig);
                    }
                }
            }
            this.enabled=function(v){
                if(v!=missing){
                    propenabled=v;
                }
                return propenabled;
            }
            this.visible=function(v){
                if(v!=missing){
                    propvisible=v;
                    if(v)
                        $("#"+propname).css({"visibility":"visible"});
                    else
                        $("#"+propname).css({"visibility":"hidden"});
                }
                return propvisible;
            }
            this.loading=function(nodeid, b){
                if(b)
                    $("#"+propname+"_"+nodeid).addClass("nodeloading");
                else
                    $("#"+propname+"_"+nodeid).removeClass("nodeloading");
            }
            this.parent=function(nodeid){
                if(nodeid!="" && nodeid!="k1")
                    return $("#"+propname+"_"+nodeid+"_text").attr("super");
                else
                    return "";
            }
            this.children=function(nodeid){
                var c=[];
                if(nodeid!=""){
                    var node=propname+"_"+nodeid+"_root";
                    var lastid=$("#"+node).prop("lastid");
                    var n;
                    for(var i=1; i<=lastid; i++){
                        n=nodeid+"k"+i;
                        if($("#"+propname+"_"+n).length>0){
                            c.push(n);
                        }
                    }
                }
                return c;
            }
            this.rootid=function(){
                return "k1";
            }
            function refreshattr(){
                $("#"+propname).css({"left":propleft,"top":proptop});
                if(propright!==false)
                    $("#"+propname).css({"right":propright});
                else
                    $("#"+propname).css({"width":propwidth});
                if(propbottom!==false)
                    $("#"+propname).css({"bottom":propbottom});
                else if(propheight!==false)
                    $("#"+propname).css({"height":propheight});
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
                    trig={id:id, info:info, open:open, parent:parent, selector:selector, text:text, type:type, folder:(type=="folder"), hitnode:hitnode, hitfolder:hitfolder};
                    $("#"+propname).trigger(name, trig);
                }
                return trig;
            }
            return this;
        }
    });
})(jQuery);
