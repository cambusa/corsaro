/****************************************************************************
* Name:            ryscript.js                                              *
* Project:         Cambusa/ryBox                                            *
* Version:         1.70                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2016  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
        ryscript:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=600;
			var propheight=400;
			var propfocusout=true;
            var propchanged=false;
			var propenabled=1;
			var propvisible=true;
            var propobj=this;
            
            var propmode="javascript";
            var propindent=4;
            var propintellisense=false;
            
            var objmirror;
			
			var propname=$(this).attr("id");
			this.id="#"+propname;
			this.tag=null;
			this.type="script";
            
			globalobjs[propname]=this;

			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.mode!=missing){propmode=settings.mode}
            if(settings.indent!=missing){propindent=settings.indent}

            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            if(settings.datum!=missing){
                // Le modifiche vengono segnalate alla maschera
                $("#"+propname).prop("datum", settings.datum);
            }
            if(settings.tag!=missing){this.tag=settings.tag}

            $("#"+propname).prop("modified", 0 )
            .addClass("ryobject")
            .addClass("ryscript")
            .css({
                "position":"absolute",
                "left":propleft,
                "top":proptop,
                "width":propwidth,
                "height":propheight,
                "overflow":"hidden",
                "color":"transparent",
                "background-color":"white",
                "font-family":"verdana,sans-serif",
                "font-size":"13px",
                "line-height":"17px",
                "cursor":"default",
                "z-index":0
            });
            objmirror=CodeMirror(document.getElementById(propname), {
                lineNumbers: false,
                indentUnit:propindent,
                indentWithTabs:true,
                tabSize:propindent,
                autoCloseBrackets:true,
                matchBrackets:true,
                showCursorWhenSelecting:true,
                theme:"monokai",
                extraKeys: {
                    "'.'": completeAfter,
                    "'('": completeAfter,
                    "Ctrl-Space": "autocomplete"
                },
                mode: {name: propmode, globalVars: true}
            });
            objmirror.setSize(propwidth, propheight);
            objmirror.on("change",
                function(){
                    propobj.raisechanged();
                }
            );
            objmirror.on("focus",
                function(){
                    if(propintellisense)
                        setintellisense(propintellisense);
                }
            );
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                if(params.height!=missing){propheight=params.height}
                $("#"+propname).css({"left":propleft, "top":proptop, "width":propwidth, "height":propheight});
                objmirror.setSize(propwidth, propheight);
            }
			this.value=function(v,a){
				if(v==missing){
                    return getvalue();
				}
				else{
                    propchanged=false;
                    setvalue(v);
                    if(a==missing){a=false}
                    if(a){propobj.raiseassigned()}
				}
			}
			this.mode=function(v){
				if(v==missing){
                    return propmode;
				}
				else{
                    propmode=v;
                    setmode(v);
				}
			}
			this.indent=function(v){
				if(v==missing){
                    return propindent;
				}
				else{
                    propindent=v;
                    setindent(v);
				}
			}
			this.intellisense=function(v){
                propintellisense=v;
			}
			this.name=function(){
				return propname;
			}
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=v.booleanNumber();
                    setenabled(propenabled);
				}
			}
			this.visible=function(v){
				if(v==missing){
					return propvisible;
				}
				else{
					propvisible=v.booleanNumber();
					if(propvisible)
						$("#"+propname).css({"visibility":"visible"});
					else
						$("#"+propname).css({"visibility":"hidden"});
				}
			}
			this.changed=function(v){
				if(v==missing)
					return propchanged;
				else
					propchanged=v;
			}
			this.modified=function(v){
				if(v==missing)
					return ($("#"+propname).prop("modified")).booleanNumber();
				else
					$("#"+propname).prop("modified", v.booleanNumber());
			}
			this.clear=function(){
            
			}
			this.focus=function(){
                setfocus();
			}
			this.refresh=function(){
                objmirror.refresh();
			}
            this.raisegotfocus=function(){
                if(settings.gotfocus!=missing){settings.gotfocus(propobj)}
            }
            this.raiselostfocus=function(){
                if(settings.lostfocus!=missing){settings.lostfocus(propobj)}
            }
            this.raisechanged=function(){
                propchanged=true;
                propobj.modified(1);
                if(settings.changed!=missing){settings.changed(propobj)}
                _modifiedState(propname,true);
            }
            this.raiseassigned=function(){
                propobj.modified(1);
                if(settings.assigned!=missing){settings.assigned(propobj)}
                propchanged=false;
            }
            function completeAfter(cm, pred) {
                var cur=cm.getCursor();
                if (!pred || pred()) setTimeout(function() {
                  if (!cm.state.completionActive)
                    cm.showHint({completeSingle: false});
                }, 100);
                return CodeMirror.Pass;
            }
            function getvalue(v){
                return objmirror.getValue().replace(/\t/g, "          ".subright(propindent));
            }
            function setvalue(v){
                objmirror.setValue(v);
                objmirror.refresh();
                objmirror.doc.clearHistory();
            }
            function setindent(v){
                propindent=v;
                objmirror.setOption("indentUnit", v);
                objmirror.setOption("tabSize", v);
                objmirror.refresh();
            }
            function setintellisense(v){
                CodeMirror.registerHelper("hint", propmode, function(cm, options){
                    var cur=cm.getCursor();
                    var curLine=cm.getLine(cur.line);
                    var end=cur.ch;
                    var start=end;
                    var line=cur.line;
                    
                    var ws=[];
                    var e=true;
                    var pos=end;
                    var w="";
                    var ok=true;
                    
                    do{
                        if(pos<=0){
                            e=false;
                            if(w!="")
                                ws.unshift([w, 0]);
                        }
                        else{
                            var k=curLine.charAt(pos - 1);
                            if(/\w/.test(k)){
                                // Carattere alpanumerico
                                w=k+w;
                                if(pos>1){
                                    // Controllo se il carettere che precede crea spazi
                                    k=curLine.charAt(pos - 2);
                                    if(/\s/.test(k)){
                                        // Aggiungo al percorso
                                        ws.unshift([w, pos-1]);
                                        w="";
                                    }
                                }
                            }
                            else if(/[.(]/.test(k)){
                                if(w!=""){
                                    ws.unshift([w, pos]);
                                    w="";
                                }
                            }
                            else if(/[\s]/.test(k)){
                                if(w!=""){
                                    ws.unshift([w, pos]);
                                    w="";
                                }
                                e=false;
                                break;
                            }
                        }
                        --pos;
                    }while(e)
                    
                    var list = [];
                    
                    if(ok){
                        var b=CopyLower(v);
                        var curWord="";
                        
                        for(var i in ws){
                            w=ws[i][0];
                            if($.isset(b[ w.toLowerCase()])){
                                b=b[w.toLowerCase()][0];
                            }
                            else{
                                curWord=w;
                                start=ws[i][1];
                                break;
                            }
                        }
                        if(typeof(b)=="object"){
                            for(var n in b){
                                if(!curWord || n.substr(0, curWord.length)==curWord.toLowerCase()){
                                    list.push(b[n][1]);
                                }
                            }
                        }
                        else if(b!=""){
                            list.push(b);
                        }
                    }

                    return {
                        list: list,
                        from: CodeMirror.Pos(line, start),
                        to: CodeMirror.Pos(line, end)
                    }
                });    
            }
            function CopyLower(v){
                var c={};
                for(var n in v){
                    if(typeof(v[n])=="object")
                        c[n.toLowerCase()]=[CopyLower(v[n]), n];
                    else
                        c[n.toLowerCase()]=[v[n], n];
                }
                return c;
            }
            function setenabled(v){
                if(v)
                    objmirror.setOption("readOnly", false);
                else
                    objmirror.setOption("readOnly", true);
            }
            function setfocus(v){
                objmirror.focus();
            }
			return this;
		}
	});
})(jQuery);
