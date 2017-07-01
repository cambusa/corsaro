/****************************************************************************
* Name:            rytable.js                                               *
* Project:         Cambusa/ryQue                                            *
* Version:         1.68                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
        rytable:function(settings){
            var propleft=10;
			var propright=10;
            var proptop=10;
			var propbottom=10;
            var propwidth=false;
            var propheight=false;
            
            var propenabled=true;
			var propvisible=true;
            var propautocoding=false;
            
            var propcols=[];
            var proptits=[];
            var propdims=[];
            var proptyps=[];
            var propcodes=[];
            
            var propsels={};
            var propselinvert=false;
            
            var propnumbered=false;
            var propcheckable=false;

            var propordcol=-1;
            var propordasc=true;
            var propordcol2=-1;
            var propordasc2=true;
            var propsortable=true;

            var propname=$(this).attr("id");
            var propcount=0;
            var propindex=0;
            var proprowh=24;
            
            var proploading=false;
            var propopacity=0;
            var propdelta=0;
			var propsuspendchange=false;
            var timeoutrow=false;
            var timeoutsel=false;
            
            var propobj=this;
            
            this.type="grid";
            this.matrix=[];
            
            if(settings.left!=missing){propleft=settings.left}
			if(settings.right!=missing){propleft=settings.right}
            if(settings.top!=missing){proptop=settings.top}
			if(settings.bottom!=missing){propbottom=settings.bottom}
            if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.numbered!=missing){setnumbered(settings.numbered)}
            if(settings.checkable!=missing){setcheckable(settings.checkable)}
            if(settings.sortable!=missing){propsortable=settings.sortable.actualBoolean()}
            if(settings.autocoding!=missing){propautocoding=settings.autocoding.actualBoolean()}
            if(settings.columns!=missing){
                var cols=settings.columns;
                for(var i=0;i<cols.length;i++){
                    addcolumn(cols[i]);
                }
            }
            if(settings.selchange!=missing){settings.selchange=settings.selchange}
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            
            var _origcols=propdims.slice(0);

            // FUNZIONI PUBBLICHE
            this.create=function(){
                var t="<input type='button' id='"+propname+"_anchor'>";
                
                t+="<div class='rytable' id='"+propname+"_outgrid'></div>"; // Outer Griglia
                
                $("#"+propname).html(t)
				.addClass("ryque-border");				

				$("#"+propname+"_outgrid").html("<table><thead></thead><tbody></tbody></table>");
				
				$("#"+propname+"_outgrid thead").css({"left":0,"right":0});
				$("#"+propname+"_outgrid tbody").css({"left":0,"right":0});
				
				$("#"+propname+"_outgrid thead").append("<tr id='"+propname+"_0'></tr>");

				if(propnumbered){
					fd=propname+"_0_ORDER";
					$("#"+propname+"_0").append("<td><div class='rytable-order-head' id='"+fd+"'></div></td>");
					$("#"+fd).html("&#x2116;");
				}

				if(propcheckable){
					fd=propname+"_0_CHECKED";
					$("#"+propname+"_0").append("<td><div class='rytable-checked-head' id='"+fd+"'></div></td>");
					$("#"+fd).html("&#x2714;");
				}

				for(c=1;c<=propcols.length;c++){
					fd=propname+"_0_"+c;
					$("#"+propname+"_0").append("<th><div id='"+fd+"'></div></th>");
					$("#"+fd).css({width:propdims[c-1]}).html( proptits[c-1] );
				}
				
				fd=propname+"_0_FILLER";
				$("#"+propname+"_0").append("<th style='width:100%;'><div id='"+fd+"'></div></th>");
				$("#"+fd).html("&nbsp;");

				setTimeout(function(){
					setstyle();
				});

				$("#"+propname+"_outgrid tbody").scroll(
					function(evt){
						$("#"+propname+"_outgrid thead").css({"left":-$("#"+propname+"_outgrid tbody").scrollLeft()});
					}
				);
				
                $("#"+propname+"_anchor").focus(
                    function(){
						$("#"+propname).addClass("ryque-focus");
                    }
                );
                $("#"+propname+"_anchor").focusout(
                    function(){
						$("#"+propname).removeClass("ryque-focus");
                    }
                );

                $("#"+propname).mousedown(
                    function(evt){
                        evt.preventDefault();
                        evt.stopPropagation();
                        if(!propenabled){return}
                        propmousebutton=true;
                        var tid=evt.target.id;
                        var r,c;
						
						if(tid.match(/(\d+)_ORDER$/)){
							r=parseInt(tid.match(/(\d+)_ORDER$/)[1]);
							if(propcheckable){
								if(r>0){
									propobj.seltoggle(r);
								}
								else{
									if($.objectsize(propsels)==propcount && !propselinvert){
										propobj.checkall(false);
									}
									else if($.objectsize(propsels)>0 && propselinvert){
										propobj.checkall(false);
									}
									else{
										propselinvert=!propselinvert;
										propobj.selrefresh();
									}
									propobj.raisechangesel();
								}
							}
							else{
								propobj.index(r);
							}
						}
						else if(tid.match(/(\d+)_CHECKED$/)){
							r=parseInt(tid.match(/(\d+)_CHECKED$/)[1]);
							if(r>0){
								propobj.seltoggle(r);
							}
							else{
								if($.objectsize(propsels)==propcount && !propselinvert){
									propobj.checkall(false);
								}
								else if($.objectsize(propsels)>0 && propselinvert){
									propobj.checkall(false);
								}
								else{
									propselinvert=!propselinvert;
									propobj.selrefresh();
								}
								propobj.raisechangesel();
							}
						}
						else if(tid.match(/_(\d+)_(\d+)$/)){
							var c=tid.match(/_(\d+)_(\d+)$/);
							r=parseInt(c[1]);
							c=parseInt(c[2]);
							propobj.index(r);
							
                            if(r==0&&c>0&&propsortable){
                                // GESTIONE ORDER BY CON MEMORIA DEL PRECEDENTE
                                var col1,type1,asc1,col2,type2,asc2;

                                // Prima chiave di ordinamento
                                col1=c;
                                type1=proptyps[col1-1];
                                if(propordcol!=c)
                                    asc1=true;
                                else
                                    asc1=!propordasc;

                                // Seconda chiave di ordinamento
                                col2=-1;
                                type2="";
                                asc2=true;
                                if(propordcol!=c){
                                    if(propordcol>=0){
                                        col2=propordcol;
                                        type2=proptyps[col2-1];
                                        asc2=propordasc;
                                    }
                                }
                                else if(propordcol2>=0){
                                    col2=propordcol2;
                                    type2=proptyps[col2-1];
                                    asc2=propordasc2;
                                }
                                
                                // Riordimento
                                if(col2>=0)
                                    sorting(col1, asc1, col2, asc2);
                                else
                                    sorting(col1, asc1);
                                
                                // Memorizzazioni
                                if(propordcol!=c){
                                    propordcol2=col2;
                                    propordasc2=asc2;
                                }
                                propordcol=col1;
                                propordasc=asc1;
                            }
						}
						
						if(r>0)
							setfocusable(r);

                        if(RYBOX)
                            castFocus(propname);
                        else
                            document.getElementById(propname+"_anchor").focus();
                    }
                );
                $("#"+propname).mouseup(
                    function(evt){
                        propmousebutton=false;
                        propmouseprev=0;
                        if(propsuspendchange){
                            propsuspendchange=false;
                            propobj.raisechangerow();
                            propobj.raisechangesel();
                        }
                    }
                );
				$("#"+propname).bind('contextmenu', 
					function(evt) {
                        evt.preventDefault();
                        evt.stopPropagation();
                        if(!propenabled){return}

                        var tid=evt.target.id;
                        var r,c;
						
						if(tid.match(/_(\d+)_(\d+)$/)){
							var c=tid.match(/_(\d+)_(\d+)$/);
							r=parseInt(c[1]);
							c=parseInt(c[2]);
							propobj.index(r);
							
						}
						
                        if(RYBOX)
                            castFocus(propname);
                        else
                            document.getElementById(propname+"_anchor").focus();
						return false;
					}
				);
                $("#"+propname).dblclick(
                    function(evt){
                        if(!propenabled){return}
                        var tid=evt.target.id;
                        var r=0,c=0;
						if(tid.match(/(\d+)_ORDER$/)){
							r=parseInt(tid.match(/(\d+)_ORDER$/)[1]);
						}
						else if(tid.match(/(\d+)_CHECKED$/)){
							r=parseInt(tid.match(/(\d+)_CHECKED$/)[1]);
						}
						else if(tid.match(/_(\d+)_(\d+)$/)){
							var c=tid.match(/_(\d+)_(\d+)$/);
							r=parseInt(c[1]);
							c=parseInt(c[2]);
							if(r>0 && c>0){
                                if(settings.cellclick!=missing){
                                    setTimeout(function(){settings.cellclick(propobj, r ,c)}, 50);
                                }
							}
						}
						
						if(r>0){
							if(settings.enter!=missing){
								setTimeout(function(){settings.enter(propobj, r)}, 200);
							}
						}
                    }
                );
				$(window).resize(
					function(){
						setTimeout(function(){
							setstyle();
						});
					}
				);
			}
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
				if(params.right!=missing){propleft=params.right}
                if(params.top!=missing){proptop=params.top}
				if(params.bottom!=missing){proptop=params.bottom}
                if(params.width!=missing){propwidth=params.width}
                if(params.height!=missing){propheight=params.height}
                
				setfocusable();
                setstyle();
            }
            this.refresh=function(){
                propobj.dataload();
            }
            this.dataload=function(){
                try{
                    var r,c,fd,vl;
                    var dy,dm,dd,dh,dn,cc;
                    var v=this.matrix.slice(0);
                    var nums=[];
                    var decs=[];
                    if(settings.before!=missing){
                        settings.before(propobj, v, propcount);
                    }
                    for(c=1; c<=propcols.length; c++){
                        if($.isNumeric(proptyps[c-1])){
                            nums[c]=true;
                            decs[c]=parseInt(proptyps[c-1]);
                        }
                        else{
                            nums[c]=false;
                        }
                    }
					
					$("#"+propname+"_outgrid tbody").html("");
					
                    for(r=1;r<=propcount;r++){
						if(propnumbered){
							// mosca
							fd="#"+propname+"_"+r+"_0";
							$(fd).html(r);
						}
						$("#"+propname+"_outgrid tbody").append("<tr id='"+propname+"_"+r+"'></tr>");

						if(propnumbered){
							fd=propname+"_"+r+"_ORDER";
							$("#"+propname+"_"+r).append("<td><div class='rytable-order' id='"+fd+"'></div></td>");
							$("#"+fd).html(r);
						}

						if(propcheckable){
							fd=propname+"_"+r+"_CHECKED";
							$("#"+propname+"_"+r).append("<td><div class='rytable-checked' id='"+fd+"'></div></td>");
							if( ( $.isset(propsels[i]) != propselinvert ).booleanNumber() )
								$("#"+fd).html("&#x2714;");
							else
								$("#"+fd).html("-");
						}

						for(c=1;c<=propcols.length;c++){
							cc="";
							vl=v[r-1][propcols[c-1]];
							if(typeof vl!="string"){
								vl=$.actualString(vl);
							}
							try{
								switch(proptyps[c-1]){
								case "?":
									if(vl.substr(0,1)!="'"){
										if(vl!=0)
											vl="&#x2714;";
										else
											vl="-";
									}
									else
										vl=vl.substr(1);
									break;
								case "/":
									if(vl.substr(0,1)!="'"){
										vl=vl.replace(/[^\d]/gi, "").substr(0,8);
										if(vl.length==8){
											dy=vl.substr(0,4);dm=vl.substr(4,2);dd=vl.substr(6,2);
											if(dy<="1900" || dy>="9999")
												vl="";
											else if(_sessioninfo.dateformat==1)
												vl=dm+"/"+dd+"/"+dy;
											else
												vl=dd+"/"+dm+"/"+dy;
										}
										else
											vl="";
									}
									else
										vl=vl.substr(1);
									break;
								case ":":
									if(vl.substr(0,1)!="'"){
										vl=(vl+"000000").replace(/[^\d]/gi, "").substr(0,14);
										if(vl.length==14){
											dy=vl.substr(0,4);dm=vl.substr(4,2);dd=vl.substr(6,2);dh=vl.substr(8,2);dn=vl.substr(10,2);
											if(dy<="1900" || dy>="9999")
												vl="";
											else if(_sessioninfo.dateformat==1)
												vl=dm+"/"+dd+"/"+dy+" "+dh+":"+dn;
											else
												vl=dd+"/"+dm+"/"+dy+" "+dh+":"+dn;
										}
										else
											vl="";
									}
									else
										vl=vl.substr(1);
									break;
								default:
									if(nums[c]){
										cc="rytable-right";
										if(vl.substr(0,1)!="'")
											vl=__formatNumber(vl, decs[c]);
										else
											vl=vl.substr(1);
									}
								}
							}
							catch(e){
								vl=e.message;
							}
							fd=propname+"_"+r+"_"+c;
							$("#"+propname+"_"+r).append("<td><div class='"+cc+"' id='"+fd+"'></div></td>");
							$("#"+fd).css({width:propdims[c-1]}).html(vl);
						}
						fd=propname+"_"+r+"_FILLER";
						$("#"+propname+"_"+r).append("<td style='width:100%;'><div id='"+fd+"'></div></td>");
						$("#"+fd).html("&nbsp;");
                    }
                    if(settings.after!=missing){
                        settings.after(propobj, v, propcount);
                    }
                }
                catch(e){
                    $.console(e.message);
                }
            }
            this.colbyname=function(n){
                var c=0;
                for(var k=0;k<propcols.length;k++){
                    if(propcols[k]==n){
                        c=k+1;
                        break;
                    }
                }
                return c;
            }
            this.colname=function(i){
				if(i>0 && i<=propcols.length)
					return propcols[i-1];
				else
					return "";
            }
            this.screencell=function(r, c){
                return "#"+propname+"_"+(parseInt(r)+1)+"_"+c;
            }
            this.screenrow=function(r){
                return "#"+propname+"_"+(parseInt(r)+1);
            }
            this.clear=function(){
                if(raisebeforechange(0)){return}
                var r,c,fd,reff;
                propcount=0;
                propindex=0;
                previndex=-1;
                propselinvert=false;
                propsels={};

				$("#"+propname+"_outgrid tbody").html("");

                propobj.raisechangerow();
                propobj.raisechangesel();
            }
            this.selinvert=function(){
                return propselinvert;
            }
            this.selengage=function(back, noselection){
                var k=propobj.checked();
                if(k.length==0){
                    if(propindex>0)
                        k=[propindex];
                }
                if(k.length>0){
                    if(back!=missing)
                        back(propobj, k);
                    else
                        return k;
                }
                else{
                    if(noselection!=missing)
                        noselection(propobj);
                    else
                        return k;
                }
            }
            this.setchecked=function(list){
                propselinvert=false;
                propsels={};
                for(var i in list)
                    propsels[list[i]]=true;
                propobj.raisechangesel();
            }
            this.checked=function(actual){
                var i,r=0,m=[];
                if(actual==missing)
                    actual=true;
                if(actual&&propselinvert){
                    for(i=1; i<=propcount; i++){
                        if(typeof propsels[i]=="undefined")
                            m[r++]=i;
                    }
                }
                else{
                    for(i in propsels)
                        m[r++]=parseInt(i);
                }
                return m;
            }
            this.ischecked=function(i){
                if(i==missing)
                    return ( $.objectsize(propsels)>0 || propselinvert ).booleanNumber();
                else
                    return ( $.isset(propsels[i]) != propselinvert ).booleanNumber();
            }
            this.isselected=function(i){
                if(i==missing)
                    return ( $.objectsize(propsels)>0 || propselinvert || propindex>0).booleanNumber();
                else
                    return ( ($.isset(propsels[i]) != propselinvert) || i==propindex).booleanNumber();
            }
            this.selrefresh=function(){
				if(propselinvert){
					for(var r=1; r<=propcount; r++){
						fd=propname+"_"+r+"_CHECKED";
						if($.isset(propsels[r]))
							$("#"+fd).html("-");
						else
							$("#"+fd).html("&#x2714;");
						
					}
				}
				else{
					for(var r=1; r<=propcount; r++){
						fd=propname+"_"+r+"_CHECKED";
						if($.isset(propsels[r]))
							$("#"+fd).html("&#x2714;");
						else
							$("#"+fd).html("-");
						
					}
				}
            }
            this.checkall=function(f){
                if(f==missing){f=true}
                propsels={};
                propselinvert=(f ? true : false);
				for(var r=1; r<=propcount; r++){
					fd=propname+"_"+r+"_CHECKED";
					if(f)
						$("#"+fd).html("&#x2714;");
					else
						$("#"+fd).html("-");
					
				}
                propobj.raisechangesel();
            }
            this.seltoggle=function(r){
				if(r in propsels)
					delete propsels[r];
				else
					propsels[r]=true;
				
				var fd=propname+"_"+r+"_CHECKED";
				if( ( $.isset(propsels[r]) != propselinvert ).booleanNumber() )
					$("#"+fd).html("&#x2714;");
				else
					$("#"+fd).html("-");
				
				propobj.raisechangesel();
            }
            this.dispose=function(done){
                if(done!=missing){setTimeout(function(){done()})}
            }
            this.name=function(){
                return propname;
            }
            this.count=function(i){
                return propcount;
            }
            this.setmatrix=function(v, selpreserve, ind, sels, selinv){
                try{
                    var changerow=false, changesel=false;
                    this.matrix=v;

                    if(selpreserve==missing){
                        selpreserve=false;
                    }
                    
                    previndex=-1;

                    if(!selpreserve){
                        propordcol=-1;
                        propordasc=true;
                        propordcol2=-1;
                        propordasc2=true;
                        //propobj.rowhome();
                    }
                    // Gestione nuovo indice
                    if(ind==missing){
                        if(selpreserve){
                            ind=propindex;
                        }
                        else{
                            ind=0;
                            changerow=true;
                        }
                    }
                    else{
                        if(!selpreserve)
                            changerow=true;
                    }
                    // Gestione nuova selezione
                    if(sels==missing){
                        if(!selpreserve){
                            propsels={};
                            changesel=true;
                        }
                    }
                    else{
                        propsels=sels;
                        if(!selpreserve)
                            changesel=true;
                    }
                    // Gestione nuova inversione
                    if(selinv==missing){
                        if(!selpreserve){
                            propselinvert=false;
                            changesel=true;
                        }
                    }
                    else{
                        propselinvert=selinv;
                        if(!selpreserve)
                            changesel=true;
                    }
                    propcount=this.matrix.length;
                    
                    if(ind>propcount){ // Controllo di sicurezza
                        ind=propcount;
                        changerow=true;
                    }

                    if(propindex!=ind){ // Gestione nuovo index
                        propindex=ind;
                    }

					setfocusable();
                    propobj.dataload();
                    
                    // Ripristino i titoli
                    for(i=1; i<=proptits.length; i++){
                        $("#"+propname+"_0_"+i).html( proptits[i-1] );
                    }

                    // Gestione eventi e callback
                    if(settings.ready!=missing){settings.ready(propobj, true)}
                    if(changerow){
                        propobj.raisechangerow();
                    }
                    if(changesel){
                        propobj.raisechangesel();
                    }
                    
                    // Inizializzazione ordinamento
                    propordcol=-1;
                    propordasc=true;
                    propordcol2=-1;
                    propordasc2=true;
                }
                catch(e){
                    $.console(e.message);
                }
            }
            this.index=function(i){
                if(i!=missing){
                    if(i>propcount)
                        i=propcount;
                    if(raisebeforechange(i)){return propindex}
                    if(i<=0){
                        propindex=0;
                        propobj.raisechangerow();
                        return 0;
                    }
                    if(1<=i && i<=propcount){
                        propindex=i;
                        propobj.raisechangerow();
                    }
                    else{
                        propindex=i;
						setfocusable();
						propobj.raisechangerow();
                    }
                }
                return propindex;
            }
            this.selected=function(r){
                var e=false;
                if(propcheckable){
                    if(1<=r && r<=propcount){
                        if((r in propsels)!=propselinvert)
                            e=true;
                    }
                }
                return e;
            }
            this.columns=function(){
                return propcols.length;
            }
			this.babelcode=function(i){
				return propcodes[i-1];
			}
			this.caption=function(i, t){
                if(0<i && i <=proptits.length){
                    if(t==missing){
                        return proptits[i-1];
                    }
                    else{
                        proptits[i-1]=t;
                        $("#"+propname+"_0_"+i).html(t);
                    }
                }
			}
            this.raisechangerow=function(){
				$("#"+propname+" tbody tr").removeClass("rytable-selected");
				$("#"+propname+"_"+propindex).addClass("rytable-selected");
                setfocusable();
                if(!propsuspendchange){
					if(settings.changerow!=missing){
						if(timeoutrow!==false)
							clearTimeout(timeoutrow);
						timeoutrow=setTimeout(function(){
							timeoutrow=false;
							settings.changerow(propobj, propindex);
						}, 100);
					}
                }
            }
            this.raisechangesel=function(){
                if(!propsuspendchange){
                    if(settings.changesel!=missing){
                        if(timeoutsel!==false)
                            clearTimeout(timeoutsel);
                        timeoutsel=setTimeout(function(){
                            timeoutsel=false;
                            settings.changesel(propobj);
                        }, 100);
                    }
                }
            }
			this.enabled=function(v){
				if(v!=missing)
					propenabled=v.actualBoolean();
                return propenabled;
			}
			this.visible=function(v){
				if(v!=missing){
					propvisible=v.actualBoolean();
					if(v)
						$("#"+propname).css({"visibility":"visible"});
					else
						$("#"+propname).css({"visibility":"hidden"});
				}
                return propvisible;
			}  
			this.sortable=function(v){
				if(v!=missing)
					propsortable=v.actualBoolean();
                return propsortable;
			}
            this.pulsating=function(v){
                if(v)
                    startloading();
                else
                    stoploading();
            }
            this.sort=function(){
                var args=[];
				if(arguments[0] instanceof Array){
					args=arguments[0];
				}
				else{
					for(var a=0; a<arguments.length; a++)
						args[a]=arguments[a];
				}
                sorting(args);
            }
            this.cells=function(r,n,v){
                if($.isNumeric(n)){
                    n=propcols[n-1];
                }
                if(v==missing){
                    v=this.matrix[r-1][n];
                }
                else{
                    if(v==null)
                        v="";
                    switch(typeof v){
                    case "undefined":
                        v="";
                        break;
                    case "boolean":
                        v=(v ? "1" : "0");
                        break;
                    case "string":
                        break;
                    case "number":
                        v=v.toString();
                        break;
                    default:
                        if(v instanceof Date)
                            v=v.getFullYear()+("00"+(v.getMonth()+1)).subright(2)+("00"+v.getDate()).subright(2)+("00"+v.getHours()).subright(2)+("00"+v.getMinutes()).subright(2)+("00"+v.getSeconds()).subright(2);
                        else
                            v="";
                    }
                    this.matrix[r-1][n]=v;
                }
                return v;
            }
            this.getrow=function(r){
                return this.matrix.slice(r-1, r)[0];
            }
            this.relocate=function(from, to){
                var f=this.getrow(from);
                this.remove(from);
                this.insert(f, to);
            }
            this.insert=function(d, r){
                if(r==missing)
                    r=propindex;
                if(r==0){
                    propobj.matrix.push(d);
                    r=propcount+1;
                }
                else{
                    propobj.matrix.splice(r-1, 0, d);
                }
                var sels={};
                for(var i in propsels){
                    if(i<r)
                        sels[i]=true;
                    else
                        sels[parseInt(i)+1]=true;
                }
                propobj.setmatrix(propobj.matrix, false, r, sels, propobj.selinvert());
            }
            this.remove=function(r){
                var i,s;
                if(r==missing || r==0)
                    r=[propindex];
                else if(!$.isArray(r))
                    r=[r];
                r.sort(function(a,b){return a-b});
                var sels={};
                var map=[];
                for(i=0; i<propobj.matrix.length; i++)
                    map[i]=i;
                for(i=r.length-1; i>=0; i--){
                    propobj.matrix.splice(r[i]-1, 1);
                    map.splice(r[i]-1, 1);
                }
                for(var i in propsels){
                    s=map.indexOf(parseInt(i)-1);
                    if(s>=0)
                        sels[s+1]=true;
                }
                propobj.setmatrix(propobj.matrix, false, propindex, sels);
            }
            this.rows=function(){
                return propcount;
            }
			this.enter=function(){
				if(settings.enter){
					setTimeout(function(){settings.enter(propobj, propindex)}, 200);
				}
			}
            this.focus=function(){
                if(RYBOX)
                    castFocus(propname);
                else
                    document.getElementById(propname+"_anchor").focus();
            }
            // CHIAMATA ALLA GENERAZIONE EFFETTIVA
            try{ this.create() }catch(e){ $.console(e.message) }
            try{if(RYBOX){RYBOX.addobject(propobj);}}catch(e){}  // Lo aggiungo a RYBOX per il multilingua

            // FUNZIONI PRIVATE
            function setstyle(){
				var h=0;
				var t=0;
				
                $("#"+propname)
                    .addClass("ryobject")
                    .addClass("ryque")
					.css({"position":"absolute","left":propleft,"top":proptop,"font-family":"verdana,sans-serif"});
                    
				if(propwidth)
					$("#"+propname).width(propwidth);
				else
					$("#"+propname).css({"right":propright});
				
				if(propheight){
					$("#"+propname).css({"height":propheight});
				}
				else{
					$.each( $("#"+propname).parents(), 
						function(key, value){
							if($(value).hasClass("window_hanger")){
								h=$(value).height()-t-proptop-propbottom;
							}
							else{
								t+=$(value).position().top;
							}
						}
					);
					
					$("#"+propname).css({"height":h});
				}
                    
				$("#"+propname+"_outgrid").css({"left":0, "right":0, "top":0, "bottom":0});
				
				h=$("#"+propname+"_outgrid").height();
				$("#"+propname+"_outgrid tbody").css({"height":h-36});

                $("#"+propname+"_anchor").css({"position":"absolute","left":-2,top:0,"width":2,"height":proprowh,"cursor":"default","text-decoration":"none","background-color":"transparent"});
                
            }
            function addcolumn(params){
                var l=propcols.length;
                var colid="",tit="",dim=100,typ="",code="";
                if(params.id!=missing){colid=params.id}
                if(params.caption!=missing){tit=params.caption}
                if(params.width!=missing){dim=params.width}
                if(params.type!=missing){typ=params.type}
                if(params.code!=missing)
                    code=params.code;
                else if(propautocoding && tit!="")
                    code="COL_"+tit.replace(/[^\w]/ig, "").toUpperCase().substr(0,50);
                if (0<dim && dim<10)
                    dim=10;
                propcols[l]=colid;
                proptits[l]=tit;
                proptyps[l]=typ;
                propdims[l]=dim;
                propcodes[l]=code;
                return dim;
            }
            function sorting(){
                try{
                    startloading();
                    if(propobj.matrix.length>0 && arguments.length>0){
                        var args=[];
                        var nams=[];
                        var cols=[];
                        var typs=[];    // 0 (string) - 1 (number) - 2 (boolean)
                        var ords=[];
                        var i=0;
                        if(arguments[0] instanceof Array){
                            args=arguments[0];
                        }
                        else{
                            args=[];
                            for(var a=0; a<arguments.length; a++)
                                args[a]=arguments[a];
                        }
                        for(var a=0; a<args.length; a+=2){
                            cols[i]=args[a];
                            if(typeof cols[i]=="string"){
                                // NOME DI COLONNA: CERCO L'INDICE
                                nams[i]=cols[i];
                                cols[i]=0;
                                for(var c=0; c<propcols.length; c++){
                                    if(propcols[c]==nams[i]){
                                        cols[i]=c+1;
                                        break;
                                    }
                                }
                            }
                            else if(cols[i]==0){
                                // SELEZIONE IN COLONNA ZERO
                                nams[i]="";
                            }
                            else{
                                // INDICE DI COLONNA
                                nams[i]=propcols[cols[i]-1];
                            }
                            // NORMALIZZAZIONE DEL TIPO
                            if(cols[i]==0){
                                typs[i]=0;
                            }
                            else{
                                var t=proptyps[cols[i]-1];
                                switch(t){
                                case "?":
                                    t=2;
                                    break;
                                case "/":
                                case ":":
                                    t=3;
                                    break;
                                default:
                                    t=($.isNumeric(t) ? 1 : 0);
                                }
                                typs[i]=t;                      
                            }
                            // TIPO ORDINAMENTO
                            ords[i]=args[a+1].booleanNumber();
                            i+=1;
                        }
                        var map=[];
                        var newbag=[];
                        var vl;
                        for(i in propobj.matrix){
                            map[i]=[i];
                            for(var b=0; b<cols.length; b++){
                                if(cols[b]>0){
                                    vl=propobj.matrix[i][ nams[b] ];
                                    if(typeof vl!="string")
                                        vl=$.actualString(vl);
                                    switch(typs[b]){
                                    case 1:
                                        vl=parseFloat(vl);
                                        if(isNaN(vl))
                                            vl=0;
                                        break;
                                    case 2:
                                        vl=parseInt(vl) ? "0": "1";
                                        break;
                                    case 3:
                                        vl=vl.replace(/[^\d]/gi, "");
                                        break;
                                    default:
                                        vl=vl.toLowerCase();
                                    }
                                }
                                else{
                                    vl=($.isset(propsels[parseInt(i)+1]) != propselinvert) ? "1" : "0";
                                }
                                map[i][b+1]=vl;
                            }
                        }
                        map.sort(
                            function(a, b){
                                var ret=0;
                                for(var k=1; k<a.length; k++){
                                    if(a[k]>b[k]){
                                        if(ords[k-1])
                                            ret=1;
                                        else
                                            ret=-1;
                                        break;
                                    }
                                    else if(a[k]<b[k]){
                                        if(ords[k-1])
                                            ret=-1;
                                        else
                                            ret=1;
                                        break;
                                    }
                                }
                                return ret;
                            }
                        );
                        var ind=0, sels={}, checked=$.objectsize(propsels), newi;
                        for(i in map){
                            newi=map[i][0];
                            newbag[i]=propobj.matrix[newi];
                            
                            // Determinazione nuovo index
                            if(propindex>0){
                                if(newi==propindex-1){
                                    ind=parseInt(i)+1;
                                }
                            }
                            // Determinazione nuova selezione 
                            if(checked){
                                if(propsels[parseInt(newi)+1]){
                                    sels[parseInt(i)+1]=true;
                                }
                            }
                        }
                        propobj.setmatrix(newbag, true, ind, sels);
                        // RIASSEGNO LE INTESTAZIONI
                        if(cols[0]>0){
                            var h=proptits[cols[0]-1];
                            if(ords[0])
                                h+="&nbsp;&#8593;";
                            else
                                h+="&nbsp;&#8595;";
                            $("#"+propname+"_0_"+cols[0]).html(h);
                        }
                    }
                    stoploading();
                }
                catch(e){
                    $.console(e.message);
                    stoploading();
                }
            }
            function setnumbered(f){
                propnumbered=f;
                propfirstcol=(propnumbered || propcheckable);
            }
            function setcheckable(f){
                propcheckable=f;
                propfirstcol=(propnumbered || propcheckable);
            }
            function startloading(){
                if(proploading===false){
                    propopacity=.7;
                    propdelta=-.04;
                    proploading=setInterval(
                        function(){
                            $("#"+propname).css({opacity:propopacity});
                            propopacity+=propdelta;
                            if(propopacity<.4){
                                propdelta=.04;
                            }
                            else if(propopacity>.7){
                                propdelta=-.04;
                            }
                        },
                        100
                    );
                }
            }
            function stoploading(){
                if(proploading!==false){
                    clearInterval(proploading);
                    proploading=false;
                    $("#"+propname).css({opacity:1});
                }
            }
            function selectrow(r, ev, chk){
                if(1<=r && r<=propcount){
                    if(chk==missing)
                        chk=true;
                    if(propselinvert==chk)
                        delete propsels[r];
                    else
                        propsels[r]=true;
                    if(ev){
                        propobj.raisechangesel();
                    }
                }
            }
            function raisebeforechange(newindex){
                var e=false;
                if(propindex>0){
                    if(settings.beforechange!=missing){
                        if(settings.beforechange(propobj, propindex, newindex)===false){
                            e=true;
                        }
                    }
                }
                return e;
            }
            function setfocusable(r){
                if(r==missing){
                    r=propindex;
                    if(r<0)
                        r=0;
                    else if(r>propcount)
                        r=0;
                }
                $("#"+propname+"_anchor").css({top:proprowh*r});
            }
			return this;
		}
	});
})(jQuery);
