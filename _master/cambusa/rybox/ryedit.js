/****************************************************************************
* Name:            ryedit.js                                                *
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
		ryedit:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=200;
			var propheight=22;
            var propfocusout=true;
			var propobj=this;
			var propchanged=false;
			var propenabled=true;
			var propvisible=true;
            var propflat=false;
            var tempvalue=false;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="edit";
			
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.flat!=missing){propflat=settings.flat}
            
            if($.browser.mobile){
                propflat=true;
                propwidth-=6;
                propheight-=42;
            }
            
            if(propflat)
                this.type="area";

            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            };
            if(settings.tag!=missing){this.tag=settings.tag};

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("ryedit")
            .css({"position":"absolute", "left":propleft, "top":proptop, "width":propwidth, "height":propheight});
            
            if(propflat){
                $("#"+propname).html("<textarea id='"+propname+"_anchor' style='resize:none;'></textarea>");
                $("#"+propname+"_anchor").css({"position":"absolute", "left":0, "top":0, "width":propwidth, "height":propheight});
                $("#"+propname+"_anchor").focus(
                    function(){
                        globaledittext=true;
                        propchanged=false;
                        propfocusout=false;
                        propobj.raisegotfocus();
                    }
                );
                $("#"+propname+"_anchor").focusout(
                    function(){
                        globaledittext=false;
                        if(propchanged)
                            propobj.raiseassigned();
                        propobj.raiselostfocus();
                        propfocusout=true;
                    }
                );
                $("#"+propname+"_anchor").keydown(
                    function(k){
                        if(k.which==9){
                            return nextFocus(propname, k.shiftKey);
                        }
                        else if(!propchanged){
                            if( k.which!=16 && 
                                k.which!=17 && 
                                k.which!=35 && 
                                k.which!=36 && 
                                k.which!=37 && 
                                k.which!=38 && 
                                k.which!=39 && 
                                k.which!=40 && 
                                (!k.ctrlKey || k.which!=45) &&
                                (!k.ctrlKey || k.which!=67) ){
                                propobj.modified(1);
                                propchanged=true;
                                propobj.raisechanged();
                            }
                        }
                    }
                );
            }
            else{
                $("#"+propname).html("<div id='"+propname+"_frame'><div id='"+propname+"_anchor'></div></div><div id='"+propname+"_alt'></div>");
                $("#"+propname+"_alt").css({"position":"absolute", "left":0, "top":0, "width":"100%", "min-height":"100%", "overflow":"visible", "display":"none", "color":"gray", "background":"white"});
                TAIL.enqueue(createeditor);
                TAIL.wriggle();
            }
            // FUNZIONI PUBBLICHE
            this.engage=function(){
                propobj.raiseenter();
            }
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                if(params.height!=missing){propheight=params.height}
                $("#"+propname).css({"left":propleft, "top":proptop, "width":propwidth, "height":propheight});
                $("#"+propname+"_anchor").css({"width":propwidth, "height":propheight});
            }
			this.value=function(v,a){
				if(v==missing){
                    var vl="";
                    if(propflat){
                        vl=$("#"+propname+"_anchor").val();
                    }
                    else{
                        try{
                            //vl=CKEDITOR.instances[propname+"_anchor"].getData();
                            if(tempvalue!==false)
                                vl=tempvalue;
                            else
                                vl=CKEDITOR.instances[propname+"_anchor"].getData();
                        }catch(e){}
                    }
                    return vl;
				}
				else{
                    if(propflat){
                        $("#"+propname+"_anchor").val(v);
                    }
                    else{
                        try{
                            tempvalue=v;
                            if(!propenabled){
                                // VISUALIZZAZIONE ALTERNATIVA DURANTE LA DISBILITAZIONE
                                $("#"+propname+"_alt").html(v);
                            }
                            TAIL.enqueue(function(n, v){
                                CKEDITOR.instances[n+"_anchor"].setData(v,
                                    function(){
                                        CKEDITOR.instances[n+"_anchor"].resetUndo();
                                        tempvalue=false;
                                        TAIL.free();
                                    }
                                );
                            }, propname, v);
                            TAIL.wriggle();
                        }catch(e){
                            if(window.console)console.log(e.message);
                        }
                    }
                    propobj.raisechanged();
                    propchanged=false;
                    if(a==missing){a=false}
                    if(a){propobj.raiseassigned()}
				}
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v.booleanNumber();
                    if(propflat){
                        if(propenabled){$("#"+propname+"_anchor").removeAttr("disabled")}
                        else{$("#"+propname+"_anchor").attr("disabled",true)}
                    }
                    else{
                        //CKEDITOR.instances[propname+"_anchor"].setReadOnly( propenabled==0 );
                        if(propenabled){
                            $("#"+propname).css({"border":"none", "height":propheight, "overflow":"visible"});
                            $("#"+propname+"_frame").css({"display":"block"});
                            $("#"+propname+"_alt").css({"display":"none"}).html("");
                        }
                        else{
                            $("#"+propname).css({"border":"1px solid silver", "height":propheight-35, "overflow":"auto"});
                            $("#"+propname+"_frame").css({"display":"none"});
                            if(tempvalue!==false)
                                $("#"+propname+"_alt").css({"display":"block"}).html(tempvalue);
                            else
                                $("#"+propname+"_alt").css({"display":"block"}).html(propobj.value());
                        }
                    }
				}
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
			this.name=function(){
				return propname;
			}
			this.modified=function(v){
				if(v==missing)
					return ($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
			this.clear=function(){
                if(propflat){
                    $("#"+propname+"_anchor").val("");
                }
                else{
                    try{
                        TAIL.enqueue(function(n){
                            CKEDITOR.instances[n+"_anchor"].setData("",
                                function(){
                                    CKEDITOR.instances[n+"_anchor"].resetUndo();
                                    TAIL.free();
                                }
                            );
                        }, propname);
                        TAIL.wriggle();
                    }catch(e){
                        if(window.console)console.log(e.message);
                    }
                }
				propobj.raisechanged();
                if(propfocusout)
                    propobj.raiseassigned();
			}
			this.focus=function(){
				objectFocus(propname);
			}
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)};
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)};
            }
            this.raisechanged=function(){
                propchanged=true;
                propobj.modified(1);
                if(settings.changed!=missing){settings.changed(propobj)};
                _modifiedState(propname,true);
            }
            this.raiseassigned=function(){
                propobj.modified(1);
                if(settings.assigned!=missing){settings.assigned(propobj)};
                propchanged=false;
            }
            function createeditor(){
                CKEDITOR.replace(propname+"_anchor", {width:propwidth, height:propheight-135})
                .on("instanceReady", function(){
                    this.on("focus", 
                        function(){
                            propchanged=false;
                            propfocusout=false;
                            propobj.raisegotfocus();
                        }
                    );
                    this.on("blur", 
                        function(){
                            if(propchanged)
                                propobj.raiseassigned();
                            propobj.raiselostfocus();
                            propfocusout=true;
                        }
                    );
                    this.on("key", 
                        function(event){
                            if ( event.data.keyCode == 9 ) {
                                event.cancel();
                                event.stop();
                                nextFocus(propname, false);
                            }
                            else if ( event.data.keyCode == CKEDITOR.SHIFT + 9 ) {
                                event.cancel();
                                event.stop();
                                nextFocus(propname, true);
                            }
                        }
                    );
                    this.on("change", 
                        function(event){
                            if(!propchanged){
                                propobj.modified(1);
                                propchanged=true;
                                propobj.raisechanged();
                            }
                        }
                    );
                    TAIL.free();
                });
            }
		    return this;
		}
    });
})(jQuery);
		