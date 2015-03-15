/****************************************************************************
* Name:            rysource.js                                              *
* Project:         Cambusa/rySource                                         *
* Version:         1.69                                                     *
* Description:     Remote file system browser                               *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
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
            var propobj=this;
            var propname=$(this).attr("id");
            var propprogr=0;
            var objfamily=null;
            
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
            
            if(proproot=="")
                proproot=propenviron;
            
            objfamily=$("#"+propname).ryfamily({left:propleft,top:proptop,width:propwidth,height:propheight,scroll:propscroll});
        
            $("#"+propname).bind("click",
                function(evt){
                    if(evt.target.className.indexOf("folder")>=0 || 
                       evt.target.className.indexOf("hitarea")>=0){
                        var id=$(evt.target).attr("rif");
                        if($("#"+propname+"_"+id).hasClass("collapsable")){ // Il nodo si apre: refresh
                            var parid=id;
                            var path="";
                            objfamily.remove(id);
                            while(parid.substr(0,1)=="k"){
                                if(parid!="k0")
                                    path=$("#"+propname+"_"+parid+"_text").attr("info")+"/"+path;
                                parid=$("#"+propname+"_"+parid+"_text").attr("super");
                            }
                            $.post(_cambusaURL+"rysource/rysource.php", {"env":propenviron,"sub":path,"sessionid":propsessionid,"dbenv":propdbenv},
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
                                            propprogr+=1;
                                            if(tp=="folder")
                                                objfamily.addfolder({parent:id, id:"k"+propprogr, info:nf, title:tl});
                                            else
                                                createlink(id,p,nf,tl,tp,par);
                                        }
                                    }
                                    catch(e){
                                        alert(d);
                                    }
                                }
                            );
                        }
                    }
                }
            );
            if(propenviron!=""){
                objfamily.addfolder({id:"k0",title:proproot,open:true});
                $.post(_cambusaURL+"rysource/rysource.php", {"env":propenviron,"sub":"","sessionid":propsessionid,"dbenv":propdbenv},
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
                                propprogr+=1;
                                if(tp=="folder")
                                    objfamily.addfolder({parent:"k0", id:"k"+propprogr, info:nf, title:tl});
                                else
                                    createlink("k0",p,nf,tl,tp,par);
                            }
                        }
                        catch(e){
                            alert(d);
                        }
                    }
                );
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
                            h="javascript:"+propstartup+"("+_stringify(par)+")";
                            objfamily.additem({parent:id,id:"k"+propprogr,title:"<a href='"+h+"' class='anchor_rysource' title='"+tl+"'>"+tl+"</a>"});
                        }
                        catch(e){}
                    }
                }
                else{
                    h=encodeURIComponent(path+nf);
                    h=h.replace(/[']/gi, "%27");
                    h=h.replace(/\%26(#|\%23)x([0-9A-F]{2})\%3B/gi, "%$2");
                    h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+h;
                    objfamily.additem({parent:id,id:"k"+propprogr,title:"<a href='"+h+"' class='anchor_rysource' target='_blank' title='"+tl+"'>"+tl+"</a>"});
                }
            }
			return this;
		}
	});
})(jQuery);
