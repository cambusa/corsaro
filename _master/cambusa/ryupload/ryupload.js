/****************************************************************************
* Name:            ryupolad.js                                              *
* Project:         Cambusa/ryUpload                                         *
* Version:         1.69                                                     *
* Description:     File uploader                                            *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
        ryupload:function(settings){
            var propleft=20;
            var proptop=20;
            var propwidth=500;
            var propobj=this;
            var propcreated=false;
            var propname=$(this).attr("id");
            var propcambusa=_systeminfo.relative.cambusa;
            var propenv="default";
            var propsessionid="";
            var propuploader=null;
            var propvisible=true;
            
            if(settings.left!=missing){propleft=settings.left}
            if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.environ!=missing){propenv=settings.environ}
            if(settings.sessionid!=missing){propsessionid=settings.sessionid}
            
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
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                propobj.refreshattr();
            }
            this.refreshattr=function(){
                if(propcreated){
                    $("#"+propname).css({"left":propleft,"top":proptop,"width":propwidth});
                }    
            }
            if(!propcreated){
                $("#"+propname).css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"background-color":"transparent","font-family":"verdana","font-size":12});
                $("#"+propname).html("<div id='"+propname+"_upload'></div>");
        
                propuploader=new qq.FileUploader({
                    element: document.getElementById(propname+'_upload'),
                    params:{env:propenv,sessionid:propsessionid},
                    action: propcambusa+'ryupload/ryupload.php',
                    multiple:false,
                    onComplete: function(id, name, ret){
                        $("#"+propname+" .qq-upload-success , .qq-upload-fail").remove();
                        if(settings.complete!=missing){
                            settings.complete(id, name, ret);
                        }
                    },
                    onCancel: function(id, name, ret){
                        if(settings.cancel!=missing){
                            settings.cancel(id, name);
                        }
                    },
                    debug: false
                });
                propcreated=true;
            }
            this.destroy=function(){
                $("#"+propname).html("");
                propuploader=null;
                propcreated=false;
            }
            this.show=function(){
                $("#"+propname).css({"visibility":"visible"});
            }
            this.hide=function(){
                $("#"+propname).css({"visibility":"hidden"});
            }
            this.visible=function(v){
				if(v==missing){
					return propvisible;
				}
				else{
					propvisible=v;
					if(v)
						$("#"+propname).css({"visibility":"visible"});
					else
						$("#"+propname).css({"visibility":"hidden"});
				}
            }
			return this;
		}
	});
})(jQuery);
