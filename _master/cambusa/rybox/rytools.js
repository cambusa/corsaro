/****************************************************************************
* Name:            rytools.js                                                *
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
		rytools:function(settings){
			var propleft=0;
			var proptop=0;
			var propwidth="100%";
			var propheight=20;
            
            var proptools="";
            var proplist=["new","open","engage","refresh","cut","copy","paste","clone","print","delete","stop"];
            var propballoon=["New (Alt-1)","Open (Alt-2)","Engage (Alt-3)","Refresh (Alt-5)","Cut","Copy","Paste","Clone (Alt-6)","Print (Alt-8)","Delete (Alt-9)","Stop (Alt-0)"];
            var propenabled={"new":1,"open":1,"engage":0,"refresh":1,"cut":0,"copy":0,"paste":0,"clone":0,"print":0,"delete":0,"stop":0};
            var propsavestate={"new":1,"open":1,"engage":0,"refresh":1,"cut":0,"copy":0,"paste":0,"clone":0,"print":0,"delete":0,"stop":0};
            var propsuccess=1;
            var proptoolleft=2;
            var proploaded=0;
			var propobj=this;

			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.type="tools";
            var propformid=$("#"+propname).prop("parentid");
            
            globalobjs[propname]=this;
			
            if(settings.tools!=missing){proptools=settings.tools.toLowerCase()}
			
            $("#"+propname).addClass("rytools");
            $("#"+propname).css({"position":"absolute","left":propleft,"top":proptop,"width":propwidth,"height":propheight,"border-bottom":"1px solid silver","background-color":"#EEE"});
            
            var t="",cn="",i,tl,bl;
            for(i=0;i<proplist.length;i++){
                tl=proplist[i];
                bl=propballoon[i];
                if(proptools.indexOf(tl)>=0){
                    if(propenabled[tl])
                        cn="box-tool-"+tl;
                    else
                        cn="box-tool-"+tl+"-gray";
                    t+="<div id='"+propname+"_tool_"+tl+"' class='"+cn+"' style='left:"+proptoolleft+"px;' title='"+bl+"'></div>";
                    proptoolleft+=20;
                }
            }
            $("#"+propname).html(t);

            for(i=0;i<proplist.length;i++){
                tl=proplist[i];
                if(proptools.indexOf(tl)>=0){
                    if(settings[tl]!=missing){
                        $("#"+propname+"_tool_"+tl).click(function(){
                            var tl=this.id.substr(propname.length+6);
                            propobj.action(tl);
                        });
                    }
                }
            }
            // FUNZIONI PUBBLICHE
			this.name=function(){
				return propname;
			}
			this.enabled=function(tool,v){
                tool=tool.toLowerCase();
                if(proptools.indexOf(tool)>=0){
                    if(v==missing){
                        return propenabled[tool];
                    }
                    else{
                        var cn;
                        propenabled[tool]=_bool(v);
                        cn="box-tool-"+tool;
                        if(!v)
                            cn+="-gray";
                        $("#"+propname+"_tool_"+tool).attr({"class":cn})
                        if(tool=="stop")
                            _busyState(propname,propenabled[tool]);
                    }
                }
                else if(v==missing)
                    return 0;
			}
            this.loaded=function(v){
                if(v==missing){
                    return proploaded;
                }
                else{
                    proploaded=_bool(v);
                    propobj.enabled("engage",proploaded);
                    propobj.enabled("cut",proploaded);
                    propobj.enabled("copy",proploaded);
                    propobj.enabled("clone",proploaded);
                    propobj.enabled("print",proploaded);
                    propobj.enabled("delete",proploaded);
                }
            }
            this.defined=function(tl){
                return (proptools.indexOf(tl)>=0);
            }
            this.action=function(tl){
                if(propenabled[tl]){
                    try{
                        propsuccess=1;
                        if(tl=="stop"){
                            // Eseguo il comando
                            propsuccess=settings[tl](propobj);
                        }
                        else if(!RYWINZ.busy(propformid)){
                            propobj.enabled("stop",1);
                            $("#message_"+propformid).html("<img style='margin:3px;' src='"+_cambusaURL+"rybox/images/progress.gif'>");
                            // Salvo lo stato
                            for(var v in propenabled){
                                if(v!="stop"){
                                    propsavestate[v]=propenabled[v];
                                    propobj.enabled(v,0);
                                }
                            }
                            // Eseguo il comando
                            propsuccess=settings[tl](propobj);
                        }
                    }
                    catch(e){}
                }
            }
            this.done=function(e,d){
                if(e==missing)
                    e=1;
                else
                    e=_bool(e);
                if(d==missing)
                    d="Done";
                // Ripristino lo stato
                for(var v in propenabled){
                    if(v!="stop")
                        propobj.enabled(v,propsavestate[v]);
                }
                if(e){
                    switch(tl){
                        case "new":
                        case "cut":
                        case "delete":
                            propobj.loaded(0);
                            _modifiedState(propname,0);
                            break;
                        case "open":
                        case "refresh":
                            propobj.loaded(1);
                            _modifiedState(propname,0);
                            break;
                        case "engage":
                            _modifiedState(propname,0);
                            break;
                        case "clone":
                            propobj.loaded(0);
                            _modifiedState(propname,1);
                            break;
                    }
                }
                // Fine esecuzione comando
                propobj.enabled("stop",0);
                winzTimeoutMess(propformid,e,d);
                return e;
            }
			return this;
		}
	});
})(jQuery);
