/****************************************************************************
* Name:            rysource.js                                              *
* Project:         Cambusa/rySource                                         *
* Version:         1.69                                                     *
* Description:     Remote file system browser                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
        rysource:function(settings){
            var propleft=20;
            var proptop=20;
            var propwidth=200;
            var propheight=400;
            var propenviron="admin";
            var propscroll=1;
            var propstartup="";
            var propsessionid="";
            var propdbenv="";
            var proproot="";
            var propmnemonic=true;
            var propobj=this;
            var propname=$(this).attr("id");
            var objfamily;
            
            if(settings.left!=missing){propleft=settings.left}
            if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.environ!=missing){propenviron=settings.environ}
            if(settings.scroll!=missing){propscroll=settings.scroll}
            if(settings.startup!=missing){propstartup=settings.startup}
            if(settings.sessionid!=missing){propsessionid=settings.sessionid}
            if(settings.dbenv!=missing){propdbenv=settings.dbenv}
            if(settings.root!=missing){proproot=settings.root}
            if(settings.mnemonic!=missing){propmnemonic=settings.mnemonic}
            
            if(proproot=="")
                proproot=propenviron;
            
            objfamily=$("#"+propname).ryfamily({
                left:propleft,
                top:proptop,
                width:propwidth,
                height:propheight,
                scroll:propscroll,
                expand:function(o, trig){
                    var path=o.getpath(trig.id).join("/");
                    path+="/";
                    openbranch(path, trig.id);
                },
                collapse:function(o, trig){
                    o.remove(trig.id);
                    if(propmnemonic){
                        $.cookie("rysource_"+propenviron+"_"+trig.id, 0, {expires:100000});
                    }
                }
            });
            if(propenviron!=""){
                objfamily.addfolder({title:proproot, open:true});
            }
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                if(params.height!=missing){propheight=params.height}
                objfamily.move({left:propleft,top:proptop,width:propwidth,height:propheight});
            }
            this.name=function(){
                return propname;
            }
            function createlink(id,path,nf,tl,tp,par){
                var h;
                var t=nf.replace(/[']/gi, "&acute;");
                tl=tl.replace(/[']/gi, "&acute;");
                if(tp!="file"){
                    if(propstartup!=""){
                        try{
                            h="javascript:"+propstartup+"("+$.stringify(par)+")";
                            objfamily.additem({parent:id,title:"<a href='"+h+"' class='anchor_rysource' title='"+tl+"'>"+tl+"</a>"});
                        }
                        catch(e){}
                    }
                }
                else{
                    h=encodeURIComponent(path+nf);
                    h=h.replace(/[']/gi, "%27");
                    h=h.replace(/\%26(#|\%23)x([0-9A-F]{2})\%3B/gi, "%$2");
                    h=_systeminfo.relative.cambusa+"rysource/source_download.php?env="+propenviron+"&sessionid="+_sessioninfo.sessionid+"&file="+h;
                    objfamily.additem({parent:id,title:"<a href='"+h+"' class='anchor_rysource' target='_blank' title='"+tl+"'>"+tl+"</a>"});
                }
            }
            function openbranch(path, parentid){
                objfamily.remove(parentid);
                TAIL.enqueue(function(arg_path){
                    $.post(_systeminfo.relative.cambusa+"rysource/rysource.php", {"env":propenviron, "sub":arg_path, "sessionid":propsessionid, "dbenv":propdbenv},
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                var p=v.path;
                                var i,nf,tl,tp,par;
                                for(i in v.content){
                                    nf=v.content[i].name;
                                    tl=v.content[i].title;
                                    tp=v.content[i].type;
                                    par=v.content[i].params;
                                    if(tp=="folder"){
                                        var status=0;
                                        var childid=objfamily.nextchild(parentid);
                                        if(propmnemonic){
                                            status=__($.cookie("rysource_"+propenviron+"_"+childid)).actualInteger();
                                        }
                                        objfamily.addfolder({parent:parentid, info:nf, title:tl, open:status});
                                    }
                                    else{
                                        createlink(parentid,p,nf,tl,tp,par);
                                    }
                                }
                                if(propmnemonic){
                                    $.cookie("rysource_"+propenviron+"_"+parentid, 1, {expires:100000});
                                }
                            }
                            catch(e){
                                alert(d);
                            }
                            TAIL.free();
                        }
                    );
                }, path);
                TAIL.wriggle();
            }
			return this;
		}
	});
})(jQuery);
