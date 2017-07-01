/****************************************************************************
* Name:            ryunbound.js                                             *
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
        ryunbound:function(settings){
            var propleft=20;
            var proptop=20;
            var propwidth=600;
            var proprows=15;
            
            var propminwidth=propwidth;
            var propmaxwidth=0;
            var t_resize=false;
            var propready=false;
        
            var propinit=false;
            var propusedparams=false;
            var propenabled=true;
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
            var propfirstcol=false;
            
            var propscrollsize=15;
            var proptracksize=30;
            var propname=$(this).attr("id");
            var proptoprow=1;
            var propmaxtoprow=1;
            var propcount=0;
            var propindex=0;
            var previndex=-1;
            var proprowh=22;
            var propleftcol=0;
            var propgridwidth=0;
            var propwinwidth=0;
            var propzerowidth=0;
            
            var propordcol=-1;
            var propordasc=true;
            var propordcol2=-1;
            var propordasc2=true;
            var propsortable=true;
            var propeditmode=false;
            var propcolumn=0;
            
            var proppageon=0;
            var proploadon=false;
            var propvisible=true;
            var propwheel=!$.browser.opera;
            var propmousebutton=false;
            var propmouseprev=0;
            var propsuspendchange=false;
            var propscrolling=false;
            var timeoutrow=false;
            var timeoutsel=false;
            
            var searchbuff="";
            var searchlast=0;
            
            // Eccezioni per Opera
            var propwhich=0;
            var propctrl=false;
            var propshift=false;
            var proprepeat=false;
            
            var proploading=false;
            var propopacity=0;
            var propdelta=0;
            
            var propobj=this;
            
            this.type="grid";
            this.matrix=[];
            
            var _down0=0;
            var _down1=0;
            
            if(settings.left!=missing){propleft=settings.left}
            if(settings.top!=missing){proptop=settings.top}
            if(settings.width!=missing){
                propwidth=settings.width;
                propminwidth=propwidth;
            }
            if(settings.maxwidth!=missing){
                propmaxwidth=settings.maxwidth;
            }
            if(settings.height!=missing){setheight(settings.height)}
            if(settings.numbered!=missing){setnumbered(settings.numbered)}
            if(settings.checkable!=missing){setcheckable(settings.checkable)}
            if(settings.sortable!=missing){propsortable=settings.sortable.actualBoolean()}
            if(settings.editmode!=missing){propeditmode=settings.editmode.actualBoolean()}
            if(settings.autocoding!=missing){propautocoding=settings.autocoding.actualBoolean()}
            if(settings.columns!=missing){
                var cols=settings.columns;
                var w=propscrollsize+5;
                if(propfirstcol){
                    w+=(propnumbered ? 60 : 22);
                }
                for(var i=0;i<cols.length;i++){
                    w+=(addcolumn(cols[i])+4);
                }
                if(propmaxwidth<0){
                    propmaxwidth=w;
                }
            }
            // Backward compatibility
            if(settings.selchange!=missing){settings.changesel=settings.selchange}
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }
            
            var _origcols=propdims.slice(0);

            // FUNZIONI PUBBLICHE
            this.create=function(){
                //var t="<a id='"+propname+"_anchor' href='javascript:'></a>";
                var t="<input type='button' id='"+propname+"_anchor'>";
                
                t+=createzero();
                t+="<div id='"+propname+"_outgrid'>"; // Outer Griglia
                    t+=creategrid();
                t+="</div>"; // Fine outer griglia
                t+="<div id='"+propname+"_rect' class='ryque-rect'><a style='cursor:default;line-height:20px;font-size:20px;'>&nbsp;&nbsp;</a></div>";  // prolungamento dell'header sopra vscroll
                t+="<div id='"+propname+"_vscroll'>"; // Scroll verticale
                    t+="<div id='"+propname+"_tooltip'>0-0</div><div id='"+propname+"_vtrack' class='ryque-vtrack'></div>";
                    t+="<div class='ryque-pageup'></div>";  // page up
                    t+="<div class='ryque-pagedown'></div>";  // page down
                t+="</div>";
                if($.browser.mobile){
                    t+="<div id='"+propname+"_mobivertback'></div><div id='"+propname+"_mobivertfore'></div>";
                }
                t+="<div id='"+propname+"_hscroll'>"; // Scroll orizzontale
                    t+="<div id='"+propname+"_htrack' class='ryque-htrack'></div>";
                    t+="<div class='ryque-pageleft'></div>";  // page left
                    t+="<div class='ryque-pageright'></div>";  // page right
                t+="</div>";
                if($.browser.mobile){
                    t+="<div id='"+propname+"_mobihoriback'></div><div id='"+propname+"_mobihorifore'></div>";
                }
                t+="<div id='"+propname+"_quad'></div>"; // prolungamento di hscroll
                t+="<div id='"+propname+"_lborder'></div>"; // bordo sinistro
                t+="<span id='"+propname+"_textwidth'></span>"; // elemento invisibile per valutare la larghezza dei testi
                
                $("#"+propname).html(t)
				.addClass("ryque-border");				
                setstyle();
                propobj.hscrefresh();
                statistics();

                $("#"+propname+"_anchor").focus(
                    function(){
                        //$("#"+propname+"_lborder").removeClass("ryque-focusout");
                        //$("#"+propname+"_lborder").addClass("ryque-focusin");
						$("#"+propname).addClass("ryque-focus");
                    }
                );
                $("#"+propname+"_anchor").focusout(
                    function(){
                        //$("#"+propname+"_lborder").removeClass("ryque-focusin");
                        //$("#"+propname+"_lborder").addClass("ryque-focusout");
						$("#"+propname).removeClass("ryque-focus");
                    }
                );
                $("#"+propname+"_anchor").keydown(
                    function(k){
                        if(!propenabled){return}
                        propwhich=k.which;
                        propctrl=k.ctrlKey;
                        propshift=k.shiftKey;
                        if(propwhich==9 || propwhich!=27){
                            k.preventDefault();
                        }
                        proprepeat=false;
                        switch(propwhich){
                        case 34:if(proppageon==0){proppageon=1}propobj.pagedown(1);break;
                        case 33:if(proppageon==0){proppageon=1}propobj.pageup(1);break;
                        case 36:
                            if(propctrl){
                                if(propshift){
                                    for(var i=2; i<=propindex; i++)
                                        selectrow(i, false);
                                    selectrow(1, true);
                                }
                                propobj.index(1);
                            }
                            else
                                propobj.rowhome();
                            break;
                        case 35:
                            if(propctrl){
                                if(propshift){
                                    for(var i=propindex; i<propcount; i++)
                                        selectrow(i, false);
                                    selectrow(propcount, true);
                                }
                                propobj.index(propcount);
                            }
                            else
                                propobj.rowend();
                            break;
                        case 40:
                            if(propshift){
                                selectrow(propindex, true);
                                propobj.rowdown();
                                selectrow(propindex, true);
                            }
                            else if(propctrl){
                                propobj.index(proptoprow+proprows-1)
                            }
                            else
                                propobj.rowdown();
                            break;
                        case 38:
                            if(propshift){
                                selectrow(propindex, true);
                                propobj.rowup();
                                selectrow(propindex, true);
                            }
                            else if(propctrl)
                                propobj.index(proptoprow);
                            else
                                propobj.rowup();
                            break;
                        case 39:if(propctrl){propobj.rowend()}else{propobj.rowright()}break;
                        case 37:if(propctrl){propobj.rowhome()}else{propobj.rowleft()}break;
                        case 32:propobj.seltoggle(0);break;
                        case 13:
                            if(propeditmode && propctrl){
                                if(settings.edit!=missing){
                                    var p0=$( "#"+propname+"_"+(propindex-proptoprow+1)+"_"+propcolumn ).position();
                                    var p1=$( "#"+propname+"_tr"+(propindex-proptoprow+1) ).position();
                                    var p2=$( "#"+propname+"_grid" ).position();
                                    var p3=$( "#"+propname+"_outgrid" ).position();
                                    var id=propcols[propcolumn-1];
                                    var t=proptyps[propcolumn-1];
                                    var worig=$( "#"+propname+"_"+(propindex-proptoprow+1)+"_"+propcolumn ).width();
                                    var w=worig+12;
                                    var l=propleft+p0.left+p1.left+p2.left+p3.left;
                                    var info={
                                        id:id, 
                                        row:propindex, 
                                        col:propcolumn, 
                                        width:w, 
                                        height:proprowh, 
                                        left:l, 
                                        top:proptop+p0.top+p1.top+p2.top+p3.top, 
                                        type:t, 
                                        value:__(propobj.matrix[propindex-1][id]),
                                        editor:false
                                    };
                                    settings.edit(propobj, info);
                                    if(info.editor){
                                        // Metodi in uscita
                                        info.back=function(v){
                                            if(v==missing){
                                                if(info.editor.type=="list")
                                                    v=info.editor.key();
                                                else
                                                    v=info.editor.value();
                                                if(v==null)
                                                    v="";
                                            }
                                            propobj.cells(propindex, id, v);
                                            propobj.refresh();
                                            propobj.focus();
                                        };
                                        info.abandon=function(){
                                            info.editor.visible(0);
                                            propobj.focus();
                                        };
                                        // Posizionamento dell'editor
                                        if(info.editor.type=="check")
                                            info.left+=Math.floor(worig/2);
                                        info.editor.move({"left":info.left, "top":info.top, "width":info.width});
                                        // Valorizzazione dell'editor
                                        if(info.editor.type=="list")
                                            info.editor.setkey(info.value);
                                        else
                                            info.editor.value(info.value);
                                        
                                        // Eventuale disabilitazione dell'helper
                                        //if(info.editor.type.match(/^(date|number|code)$/))
                                        //    info.editor.helper(0);
                                        
                                        // Se l'editor è numerico, disabilito l'incremento mediante frecce
                                        if(info.editor.type=="number")
                                            info.editor.incremental(0);
                                        // Show e focus
                                        info.editor.visible(1);
                                        RYBOX.setfocus(info.editor.name());
                                    }
                                }
                            }
                            else if(settings.enter!=missing){
                                settings.enter(propobj, propindex);
                            }
                            break;
                        case 46:    // CTRL-DEL: cancello la selezione
                            if(propctrl){
                                propsels={};
                                propselinvert=false;
                                for(r=1;r<=proprows;r++)
                                    propobj.rowdecor(r,true);
                                propobj.selrefresh();
                            }
                            break;
                        case 9:
                            if(RYBOX)
                                return nextFocus(propname, k.shiftKey);
                            break;
                        case 50:    // ALT-2: apro l'eventuale menù contestuale (è giusto che poi non ci sia il break;)
                            if(k.altKey)
                                $("#"+propname).contextmenu();
                        default:
                            setTimeout(function(){
                                searchmanagement( propwhich==173 ? "-" : String.fromCharCode(propwhich).toUpperCase() );
                            });
                        }
                        if(propshift)
                            return false;
                    }
                );
                $("#"+propname+"_anchor").keypress(
                    function(k){
                        if(!propenabled){return}
                        k.preventDefault();
                    }
                );
                $("#"+propname+"_anchor").keyup(
                    function(k){
                        if(!propenabled){return}
                        propwhich=0;
                        propctrl=false;
                        propshift=false;
                        proprepeat=false;
                        switch(k.which){
                            case 34:
                            case 33:
                                if(proppageon>2){
                                    propobj.tipdeactivate();
                                    propobj.dataload();
                                }
                                proppageon=0;
                                break;
                        }
                         // MANTENGO PULITO INPUT
                        $("#"+propname+"_anchor").val("");
                    }
                );
                if($.browser.opera){
                    $("#"+propname+"_anchor").keypress(
                        function(k){
                            if(!propenabled){return}
                            if(proprepeat){
                                switch(propwhich){
                                case 34:if(proppageon==0){proppageon=1}propobj.pagedown(1);break;
                                case 33:if(proppageon==0){proppageon=1}propobj.pageup(1);break;
                                case 40:
                                    if(propshift){
                                        selectrow(propindex, false);
                                        propobj.rowdown();
                                        selectrow(propindex, true);
                                        return false;
                                    }
                                    else
                                        propobj.rowdown();
                                    break;
                                case 38:
                                    if(propshift){
                                        selectrow(propindex, false);
                                        propobj.rowup();
                                        selectrow(propindex, true);
                                        return false;
                                    }
                                    else
                                        propobj.rowup();
                                    break;
                                case 39:if(propctrl){propobj.rowend()}else{propobj.rowright()}break;
                                case 37:if(propctrl){propobj.rowhome()}else{propobj.rowleft()}break;
                                }
                            }
                            proprepeat=true;
                            if(propshift)
                                return false;
                        }
                    );
                }
                $("#"+propname+"_vtrack").draggable({
                    axis:"y",
                    containment:"parent",
                	start:function(){
                        propobj.tipactivate();
                	},
                	drag:function(){
                        propobj.tipmove(0);
                	},
                	stop:function(){
                        propobj.tipdeactivate();
                        propobj.dataload();
                	}
                });
                $("#"+propname+"_vscroll").mouseover(
                    function(evt){
                        if(propcount>proprows && propenabled)
                            $("#"+propname+"_vscroll .ryque-pageup,#"+propname+"_vscroll .ryque-pagedown").show();
                    }
                );
                $("#"+propname+"_vscroll").mouseout(
                    function(evt){
                        $("#"+propname+"_vscroll .ryque-pageup,#"+propname+"_vscroll .ryque-pagedown").hide();
                    }
                );
                $("#"+propname+"_vscroll .ryque-pageup").click(
                    function(evt){
                        propobj.pageup(1);
                        propobj.dataload();
                    }
                );
                $("#"+propname+"_vscroll .ryque-pagedown").click(
                    function(evt){
                        propobj.pagedown(1);
                        propobj.dataload();
                    }
                );
                $("#"+propname+"_vscroll").mousedown(
                    function(evt){
                        if(!propenabled){return}
                        if(propcount>proprows){
                            var o=$("#"+propname+"_vtrack").offset();
                            if(evt.pageX>o.left){
                                if (evt.pageY>o.top+proptracksize){
                                    propobj.pagedown(1);
                                    propobj.dataload();
                                }
                                else if (evt.pageY<o.top){
                                    propobj.pageup(1);
                                    propobj.dataload();
                                }
                            }
                        }
                    }
                );
                
                $("#"+propname+"_htrack").draggable({
                    axis:"x",
                    containment:"parent",
                	start:function(){
                        propscrolling=true;
                	},
                	drag:function(){
                        var w=$("#"+propname+"_hscroll").width()-proptracksize;
                        var p=$(this).position().left;
                        propleftcol=Math.round((propgridwidth-propwinwidth)*p/w);
                        if(propleftcol>propgridwidth-propwinwidth)
                            propleftcol=propgridwidth-propwinwidth;
                        if (propleftcol<0)
                            propleftcol=0;
                        $("#"+propname+"_grid")
                            .css({"left":-propleftcol});
                	},
                	stop:function(){
                        propscrolling=false;
                	}
                });
                $("#"+propname+"_hscroll").mouseover(
                    function(evt){
                        if(propgridwidth>propwinwidth && propenabled)
                            $("#"+propname+"_hscroll .ryque-pageleft,#"+propname+"_hscroll .ryque-pageright").show();
                    }
                );
                $("#"+propname+"_hscroll").mouseout(
                    function(evt){
                        $("#"+propname+"_hscroll .ryque-pageleft,#"+propname+"_hscroll .ryque-pageright").hide();
                    }
                );
                $("#"+propname+"_hscroll .ryque-pageleft").click(
                    function(evt){
                        scrollLeft();
                    }
                );
                $("#"+propname+"_hscroll .ryque-pageright").click(
                    function(evt){
                        scrollRight();
                    }
                );

                draggablecolumns();
                
                $("#"+propname+"_hscroll").mousedown(
                    function(evt){
                        if(!propenabled){return}
                        if(propgridwidth>propwinwidth){
                            var o=$("#"+propname+"_htrack").offset();
                            if(evt.pageY>o.top){
                                if (evt.pageX>o.left+proptracksize)
                                    scrollRight();
                                else if (evt.pageX<o.left)
                                    scrollLeft();
                            }
                        }
                    }
                );
                if($.browser.mobile){
                    $("#"+propname+"_mobivertback").mousedown(
                        function(evt){
                            if(!propenabled){return}
                            propobj.pageup(1);
                            propobj.dataload();
                        }
                    );
                    $("#"+propname+"_mobivertfore").mousedown(
                        function(evt){
                            if(!propenabled){return}
                            propobj.pagedown(1);
                            propobj.dataload();
                        }
                    );
                    $("#"+propname+"_mobihoriback").mousedown(
                        function(evt){
                            if(!propenabled){return}
                            scrollLeft();
                        }
                    );
                    $("#"+propname+"_mobihorifore").mousedown(
                        function(evt){
                            if(!propenabled){return}
                            scrollRight();
                        }
                    );
                }
                $("#"+propname+"_rect").dblclick(
                    function(evt){
                        if(!propenabled){return}
                        propdims=_origcols.slice(0);
                        fitcolumns();
                    }
                );
                $("#"+propname).mousedown(
                    function(evt){
                        evt.preventDefault();
                        evt.stopPropagation();
                        if(!propenabled){return}
                        propmousebutton=true;
                        var tid=evt.target.id;
                        var r,c,reff;
                        if(tid.indexOf("_tr")>0){
                            r=parseInt(tid.replace(/^.*_tr(\d+)$/,"$1"));
                            c=-1;
                        }
                        else if(tid.indexOf("_zr")>0){
                            r=parseInt(tid.replace(/^.*_zr(\d+)$/,"$1"));
                            c=0;
                        }
                        else if(tid.indexOf("_selicon")>0){
                            r=0;
                            c=0;
                        }
                        else{
                            r=parseInt(tid.replace(/^.*_(\d+)_\d+$/,"$1"));
                            c=parseInt(tid.replace(/^.*_\d+_(\d+)$/,"$1"));
                        }
                        setfocusable(r);
                        if(r>0){
                            reff=proptoprow+r-1;
                            if(reff<=propcount){
                                propmouseprev=reff;
                                propsuspendchange=false;
                                if(c!=0){
                                    if(reff!=propindex)
                                        propobj.index(reff);
                                    if(propeditmode){
                                        resetcells();
                                        propcolumn=c;
                                        activecell();
                                    }
                                }
                                else if(c==0 && propcheckable){
                                    propobj.seltoggle(reff);
                                }
                            }
                        }
                        else{
                            if(c>0&&propsortable){
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
                            else if(c==0 && propcheckable){
                                if($.objectsize(propsels)==propcount && !propselinvert){
                                    propsels={};
                                }
                                else if($.objectsize(propsels)>0 && propselinvert){
                                    propsels={};
                                    propselinvert=false;
                                }
                                else{
                                    propselinvert=!propselinvert;
                                }
                                for(r=1;r<=proprows;r++)
                                    propobj.rowdecor(r,true);
                                propobj.selrefresh();
                                propobj.raisechangesel();
                            }
                        }
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
                $("#"+propname).hover(
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
                $("#"+propname).mousemove(
                    function(evt){
                        if(!propenabled){return}
                        if(propscrolling){return}
                        if(propcheckable&&propmousebutton){
                            var tid=evt.target.id;
                            var r,reff;
                            if(tid.indexOf("_tr")>0)
                                r=parseInt(tid.replace(/^.*_tr(\d+)$/,"$1"));
                            else if(tid.indexOf("_zr")>0)
                                r=parseInt(tid.replace(/^.*_zr(\d+)$/,"$1"));
                            else if(tid.indexOf("_selicon")>0)
                                r=0;
                            else
                                r=parseInt(tid.replace(/^.*_(\d+)_\d+$/,"$1"));
                            setfocusable(r);
                            if(r>0 && propmouseprev>0){
                                reff=proptoprow+r-1;
                                if(reff>propcount)
                                    reff=propcount;
                                if(reff>propmouseprev){
                                    for(var m=propmouseprev; m<=reff; m++){
                                        propsuspendchange=true;
                                        selectrow(m, true, !evt.shiftKey);
                                    }
                                    propmouseprev=reff;
                                }
                                else if(reff<propmouseprev){
                                    for(var m=propmouseprev; m>=reff; m--){
                                        propsuspendchange=true;
                                        selectrow(m, true, !evt.shiftKey);
                                    }
                                    propmouseprev=reff;
                                }
                                if(reff!=propindex){
                                    propsuspendchange=true;
                                    propobj.index(reff);
                                }
                            }
                        }
                    }
                );
                $("#"+propname).dblclick(
                    function(evt){
                        if(!propenabled){return}
                        var tid=evt.target.id;
                        var r,c;
                        if(tid.indexOf("_tr")>0){
                            r=parseInt(tid.replace(/^.*_tr(\d+)$/,"$1"));
                            c=-1;
                        }
                        else if(tid.indexOf("_zr")>0){
                            r=parseInt(tid.replace(/^.*_zr(\d+)$/,"$1"));
                            c=0;
                        }
                        else{
                            r=parseInt(tid.replace(/^.*_(\d+)_\d+$/,"$1"));
                            c=parseInt(tid.replace(/^.*_\d+_(\d+)$/,"$1"));
                            if(r<=propcount){
                                if(settings.cellclick!=missing){
									var reff=proptoprow+r-1; 
                                    setTimeout(function(){settings.cellclick(propobj, reff ,c)}, 50);
                                }
                            }
                        }
                        if(r>0 && ((c!=0 && propcheckable) || !propcheckable)){
                            r=proptoprow+r-1;
                            if(r<=propcount){
                                // Gestione eventi e callback
                                if(settings.enter!=missing){
                                    setTimeout(function(){settings.enter(propobj,r)}, 200);
                                }
                            }
                        }
                    }
                );
                $("#"+propname).mousewheel(function(event,delta){
                    if(!propenabled){return}
                    if(propcount>proprows)
                        event.preventDefault();
                    if(proploadon==false){
                        var e=false;
    					if(propwheel){
    						if(delta<0)
    							e=propobj.pagedown(3);
    						else
    							e=propobj.pageup(3);
                        }
    					else{
    						if(delta>0)
    							e=propobj.pagedown(3);
    						else
    							e=propobj.pageup(3);
                        }
                        if(e)
                            propobj.dataload();
                    }
                });
                if(propmaxwidth>propminwidth){
                    var par=$("#"+propname).parents(".window_main");
                    if(par.length>0){
                        var id=par[0]["id"];
                        RYWINZ.forms( id.substr(5) )._kresize=function(metrics){
                            propwidth=metrics.window.width-2*propleft-20;
                            if(propwidth<propminwidth)
                                propwidth=propminwidth;
                            else if(propwidth>propmaxwidth)
                                propwidth=propmaxwidth;
                            if(t_resize!==false){clearTimeout(t_resize)}
                            t_resize=setTimeout(function(){
                                t_resize=false;
                                propobj.move({"width":propwidth});
                            }, 500);
                        };
                    }
                }
            }
            this.move=function(params){
                if(params.left!=missing){propleft=params.left}
                if(params.top!=missing){proptop=params.top}
                if(params.width!=missing){propwidth=params.width}
                if(params.height!=missing){setheight(params.height)}
                
                $("#"+propname+"_zero").html(createzero());
                $("#"+propname+"_outgrid").html(creategrid());

                draggablecolumns();
                
                propmaxtoprow=propcount-proprows+1;
                if(propmaxtoprow<1)
                    propmaxtoprow=1;
                if(propleftcol>propgridwidth-propwinwidth)
                    propleftcol=propgridwidth-propwinwidth;
                    
                propobj.hscrefresh();
                propobj.fittoprow();
                setstyle();
                propobj.hscrefresh();
                propobj.dataload();
            }
            this.refresh=function(){
                propobj.dataload();
            }
            this.dataload=function(chain){
                try{
                    var r,c,fd,vl,reff;
                    var dy,dm,dd,dh,dn;
                    var v=[];
                    var nums=[];
                    var decs=[];
                    propobj.vscrefresh();
                    for(r=0; r<(proprows<=propcount ? proprows : propcount); r++){
                        v[r]={};
                        for(c=0; c<propcols.length; c++){
                            v[r][propcols[c]]=this.matrix[proptoprow+r-1][propcols[c]];
                        }
                    }
                    if(settings.before!=missing){
                        settings.before(propobj, v, proprows);
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
                    for(r=1;r<=proprows;r++){
                        reff=proptoprow+r-1
                        if (reff<=propcount){
                            if(propnumbered){
                                fd="#"+propname+"_"+r+"_0";
                                $(fd).html(reff);
                            }
                            for(c=1;c<=propcols.length;c++){
                                fd="#"+propname+"_"+r+"_"+c;
                                vl=v[r-1][propcols[c-1]];
                                if(typeof vl!="string"){
                                    vl=$.actualString(vl);
                                }
                                //else{
                                    try{
                                        switch(proptyps[c-1]){
                                        case "?":
                                            if(vl.substr(0,1)!="'"){
                                                if(vl!=0)
                                                    vl="&#x2714;";
                                                else
                                                    vl="&#x0020;";
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
                                                if(vl.substr(0,1)!="'")
                                                    vl=__formatNumber(vl, decs[c]);
                                                else
                                                    vl=vl.substr(1);
                                            }
                                            else{
                                                vl=vl.replace(/<[bh]r\/?>/gi," ").replace(/<\/?p>/gi, " ").replace(/ +$/, "");
                                                if(vl.length>20 && vl.substr(0,5)!="<img ")
                                                    $(fd).attr("title",vl);
                                                else
                                                    $(fd).attr("title","");
                                            }
                                        }
                                    }
                                    catch(e){
                                        vl=e.message;
                                    }
                                //}
                                $(fd).html(vl);
                            }
                        }
                        else{
                            for(c=0;c<=propcols.length;c++){
                                fd="#"+propname+"_"+r+"_"+c;
                                $(fd).html("&nbsp;");
                            }
                        }
                    }
                }
                catch(e){
                    if(window.console){console.log(e.message)}
                }
                propobj.decrefresh(true);
                proploadon=false;
                if(chain!=missing){
                    chain();
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
            this.screencell=function(r, c){
                return "#"+propname+"_"+(parseInt(r)+1)+"_"+c;
            }
            this.screenrow=function(r){
                return "#"+propname+"_tr"+(parseInt(r)+1);
            }
            this.clear=function(){
                if(raisebeforechange(0)){return}
                var r,c,fd,reff;
                proploadon=true;
                propcount=0;
                statistics();
                propindex=0;
                previndex=-1;
                propselinvert=false;
                propsels={};
                propobj.selrefresh();
                propobj.vscrefresh();
                for(r=1;r<=proprows;r++){
                    reff=proptoprow+r-1
                    for(c=0;c<=propcols.length;c++){
                        fd="#"+propname+"_"+r+"_"+c;
                        $(fd).html("&nbsp;");
                    }
                }
                propobj.decrefresh(true);
                proploadon=false;
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
                propobj.decrefresh(true);
                propobj.selrefresh();
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
            this.checkall=function(f){
                if(f==missing){f=true}
                propsels={};
                propselinvert=(f ? true : false);
                for(r=1;r<=proprows;r++)
                    propobj.rowdecor(r,true);
                propobj.selrefresh();
                propobj.raisechangesel();
            }
            this.dispose=function(done){
                if(done!=missing){setTimeout(function(){done()})}
            }
            this.pagedown=function(f){
                var e=false;
                if(proploadon==false){ // Nessun refresh e' in corso
                    if(proptoprow<propmaxtoprow){
                        if(f==1)
                            proptoprow+=proprows;
                        else
                            proptoprow+=Math.round(proprows/f)+1;
                        propobj.fittoprow();
                        propobj.flurry();
                        e=true;
                    }
                }
                return e;
            }
            this.pageup=function(f){
                var e=false;
                if(proploadon==false){ // Nessun refresh e' in corso
                    if(proptoprow>1){
                        if(f==1)
                            proptoprow-=proprows;
                        else
                            proptoprow-=Math.round(proprows/f)+1;
                        propobj.fittoprow();
                        propobj.flurry();
                        e=true;
                    }
                }
                return e;
            }
            this.flurry=function(){
                // Fasi per la gestione della pressione continua di PgDown e PgUp
                if(proppageon==2){
                    propobj.tipactivate();
                    proppageon=3;
                }
                if(proppageon==3){
                    propobj.vscrefresh();
                    propobj.tipmove(1);
                }
                if(proppageon==1){
                    propobj.dataload();
                    proppageon=2;
                }
            }
            this.rowdown=function(){
                if(propindex<propcount){
                    if(raisebeforechange(propindex+1)){return}
                    propindex+=1;
                    if(propindex<proptoprow || propindex>proptoprow+proprows-1){
                        proptoprow=propindex-Math.floor(proprows/2)+2;
                        propobj.fittoprow();
                        propobj.dataload();
                        statistics();
                    }
                    else{
                        propobj.decrefresh(true);
                        propobj.indrefresh();
                    }
                }
            }
            this.rowup=function(){
                if(propindex>1){
                    if(raisebeforechange(propindex-1)){return}
                    propindex-=1;
                    if(propindex<proptoprow || propindex>proptoprow+proprows-1){
                        proptoprow=propindex-Math.floor(proprows/2)-2;
                        propobj.fittoprow();
                        propobj.dataload();
                        statistics();
                    }
                    else{
                        propobj.decrefresh(true);
                        propobj.indrefresh();
                    }
                }
            }
            this.rowright=function(){
                if(propeditmode){
                    if(propcolumn<propcols.length){
                        resetcells();
                        propcolumn+=1;
                        activecell();
                    }
                }
                else{
                    scrollRight();
                }
            }
            function scrollRight(){
                propleftcol+=50;
                if(propleftcol>propgridwidth-propwinwidth)
                    propleftcol=propgridwidth-propwinwidth;
                $("#"+propname+"_grid").css({"left":-propleftcol});
                propobj.hscrefresh();
            }
            this.rowleft=function(){
                if(propeditmode){
                    if(propcolumn>1){
                        resetcells();
                        propcolumn-=1;
                        activecell();
                    }
                }
                else{
                    scrollLeft();
                }
            }
            function scrollLeft(){
                propleftcol-=50;
                if(propleftcol<0)
                    propleftcol=0;
                $("#"+propname+"_grid").css({"left":-propleftcol});
                propobj.hscrefresh();
            }
            this.rowhome=function(){
                if(propeditmode){
                    if(propcolumn>1){
                        resetcells();
                        propcolumn=1;
                        activecell();
                    }
                }
                propleftcol=0;
                $("#"+propname+"_grid").css({"left":-propleftcol});
                propobj.hscrefresh();
            }
            this.rowend=function(){
                if(propeditmode){
                    if(propcolumn<propcols.length){
                        resetcells();
                        propcolumn=propcols.length;
                        activecell();
                    }
                }
                propleftcol=propgridwidth-propwinwidth;
                if(propleftcol<0)
                    propleftcol=0;
                $("#"+propname+"_grid").css({"left":-propleftcol});
                propobj.hscrefresh();
            }
            this.vscrefresh=function(){
                var t=$("#"+propname+"_vtrack");
                if(propcount>proprows){
                    if(proptoprow==1)
                        h=0;
                    else{
                        var h=$("#"+propname+"_vscroll").height()-proptracksize;
                        h=Math.round(h*proptoprow/propmaxtoprow);
                    }
                    t.css({"top":h,"visibility":"visible"});
                    if($.browser.mobile){
                        mobilestate(0, "block");
                    }
                }
                else{
                    t.css({"top":0,"visibility":"hidden"});
                    if($.browser.mobile){
                        mobilestate(0, "none");
                    }
                }
            } 
            this.hscrefresh=function(){
                var l=0;
                var t=$("#"+propname+"_htrack");
                if (propgridwidth>propwinwidth){
                    var l=0;
                    if(propleftcol>propgridwidth-propwinwidth){
                        propleftcol=propgridwidth-propwinwidth;
                        $("#"+propname+"_grid").css({"position":"absolute","left":-propleftcol});
                    }
                    if (propleftcol>0){
                        var w=$("#"+propname+"_hscroll").width()-proptracksize;
                        l=(w*propleftcol)/(propgridwidth-propwinwidth);
                    }
                    t.css({"left":l,"visibility":"visible"});
                    if($.browser.mobile){
                        mobilestate(1, "block");
                    }
                }
                else{
                    propleftcol=0;
                    $("#"+propname+"_grid").css({"position":"absolute","left":-propleftcol});
                    t.css({"left":0,"visibility":"hidden"});
                    if($.browser.mobile){
                        mobilestate(1, "none");
                    }
                }
            }
            this.fittoprow=function(){
                if (proptoprow>propmaxtoprow)
                    proptoprow=propmaxtoprow;
                if (proptoprow<1)
                    proptoprow=1;
                setfocusable();
                statistics();
            }
            this.gotofirst=function(){
                proptoprow=1;
                setfocusable();
                propobj.dataload();
                statistics();
            }
            this.gotolast=function(){
                proptoprow=propmaxtoprow;
                setfocusable();
                propobj.dataload();
                statistics();
            }
            this.decrefresh=function(f){
                var r;
                for(r=1;r<=proprows;r++){
                    propobj.rowdecor(r,f);
                }
            }
            this.indrefresh=function(){
                var reff=propindex;
                var gr=reff-proptoprow+1;
                if(gr>=1 && gr<=proprows){
                    propobj.rowdecor(gr,true);
                }
                if(propindex!=previndex){
                    previndex=propindex;
                    propobj.raisechangerow();
                }
            }
            this.rowdecor=function(gr,f){
                var reff=proptoprow+gr-1;
                var fd;
                if(propfirstcol){
                    var s=false;
                    fd="#"+propname+"_zr"+gr;
                    $(fd).removeClass("ryque-row-even ryque-row-odd ryque-row-selected ryque-row-checked");
                    if(propcheckable && reff<=propcount && f){
                        if((reff in propsels)!=propselinvert){
                            s=true;
                            $(fd).addClass("ryque-row-checked");
                        }
                    }
                    if(!s){
                        if(reff==propindex && f){
                            $(fd).addClass("ryque-row-selected");
                        }
                        else{
                            if((gr%2)==0)
                                $(fd).addClass("ryque-row-even");
                            else
                                $(fd).addClass("ryque-row-odd");
                        }
                    }
                }
                // Selettore
                fd="#"+propname+"_tr"+gr;
                $(fd).removeClass("ryque-row-even ryque-row-odd ryque-row-selected ryque-row-checked-even ryque-row-checked-odd");
                if(reff==propindex && f){
                    $(fd).addClass("ryque-row-selected");
                }
                else{
                    var s=false;
                    if(propcheckable && reff<=propcount && f){
                        if((reff in propsels)!=propselinvert){
                            s=true;
                            if((gr%2)==0)
                                $(fd).addClass("ryque-row-checked-even");
                            else
                                $(fd).addClass("ryque-row-checked-odd");
                        }
                    }
                    if(!s){
                        if((gr%2)==0)
                            $(fd).addClass("ryque-row-even");
                        else
                            $(fd).addClass("ryque-row-odd");
                    }
                }
                if(propeditmode){
                    var c="#"+propname+"_"+gr+"_"+propcolumn;
                    if(reff==propindex && propcolumn>0)
                        $(c).addClass("ryque-active-cell");
                    else
                        $(c).removeClass("ryque-active-cell");
                }
            }
            this.selrefresh=function(){
                if(propcheckable){
                    var icon="ryque-check";
                    var sels=$.objectsize(propsels);
                    if(sels>0){
                        if(sels<propcount){
                            if(propselinvert)
                                icon="ryque-almostcheck";
                            else
                                icon="ryque-almostuncheck";
                        }
                        else{
                            if(!propselinvert)
                                icon="ryque-uncheck";
                        }
                    }        
                    else{
                        if(propselinvert)
                            icon="ryque-uncheck";
                    }
                    $("#"+propname+"_selicon").removeClass("ryque-check ryque-uncheck ryque-almostcheck ryque-almostuncheck").addClass(icon);
                }
            }
            this.seltoggle=function(reff){
                if(reff==0)
                    reff=propindex;
                var r=reff-proptoprow+1;
                if(reff<=propcount){
                    if(reff in propsels)
                        delete propsels[reff];
                    else
                        propsels[reff]=true;
                    propobj.rowdecor(r,true);
                    propobj.selrefresh();
                    propobj.raisechangesel();
                }
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
                        propobj.rowhome();
                    }
                    // Gestione nuovo indice
                    if(ind==missing){
                        if(selpreserve){
                            ind=propindex;
                        }
                        else{
                            ind=0;
                            proptoprow=1;
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
                    propmaxtoprow=propcount-proprows+1;
                    if(propmaxtoprow<1)
                        propmaxtoprow=1;
                    
                    if(ind>propcount){ // Controllo di sicurezza
                        ind=propcount;
                        changerow=true;
                    }

                    if(propindex!=ind){ // Gestione nuovo index
                        propindex=ind;
                        proptoprow=propindex-Math.floor(proprows/2)+2;
                    }
                    propobj.fittoprow();
                    propobj.selrefresh();
                    propready=true;
                    propobj.dataload();
                    statistics();
                    
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
                    
                    // Lista inizializzata
                    propinit=true;
                }
                catch(e){
                    if(window.console){console.log(e.message)}
                }
            }
            this.index=function(i){
                if(i!=missing){
                    if(i>propcount)
                        i=propcount;
                    if(raisebeforechange(i)){return propindex}
                    if(i<=0){
                        propindex=0;
                        propobj.decrefresh(true);
                        propobj.raisechangerow();
                        return 0;
                    }
                    if(proptoprow<=i && i<=proptoprow+proprows-1){
                        propindex=0;
                        propobj.decrefresh(true);
                        propindex=i;
                        propobj.rowdecor(i-proptoprow+1,true);
                        propobj.raisechangerow();
                    }
                    else{
                        propindex=i;
                        proptoprow=i-Math.floor(proprows/2);
                        propobj.fittoprow();
                        statistics();
                        propobj.dataload(
                            function(){
                                propobj.raisechangerow();
                            }
                        );
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
            this.tipactivate=function(){
                propscrolling=true;
                $("#"+propname+"_tooltip").css({"visibility":"visible","left":-2,"top":4});
                $("#"+propname+"_vscroll .ryque-pageup,#"+propname+"_vscroll .ryque-pagedown").hide();
            }
            this.tipmove=function(c){
                var p=$("#"+propname+"_vtrack").position().top;
                if(c==0){
                    var h=$("#"+propname+"_vscroll").height()-proptracksize;
                    proptoprow=Math.round(propmaxtoprow*p/h);
                    if (proptoprow>propmaxtoprow)
                        proptoprow=propmaxtoprow;
                    if (proptoprow<1)
                        proptoprow=1;
                    setfocusable();
                }
                var t=$("#"+propname+"_tooltip");
                t.html(proptoprow+"-"+(proptoprow+proprows-1));
                l=t.width()+2;
                t.css({"top":p+4,"left":-l});
            }
            this.tipdeactivate=function(){
                propscrolling=false;
                var t=$("#"+propname+"_tooltip");
                t.html("");
                t.css({"visibility":"hidden"});
                statistics();
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
                setfocusable();
                if(!propsuspendchange){
                    if(propinit){ // Qualcosa deve essere stato fatto prima
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
            this.autofit=function(){
                setTimeout(autofit);
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
                return proprows;
            }
			this.editmode=function(v){
				if(v!=missing)
					propeditmode=v.actualBoolean();
                return propeditmode;
			}
            this.focus=function(){
                if(RYBOX)
                    castFocus(propname);
                else
                    document.getElementById(propname+"_anchor").focus();
            }
            // CHIAMATA ALLA GENERAZIONE EFFETTIVA
            try{ this.create() }catch(e){ if(window.console){console.log(e.message)} }
            try{if(RYBOX){RYBOX.addobject(propobj);}}catch(e){}  // Lo aggiungo a RYBOX per il multilingua

            // FUNZIONI PRIVATE
            function createzero(){
                var t,r,cl;
                propzerowidth=0;
                t="";
                if(propfirstcol){
                    if(propnumbered)
                        propzerowidth=60;
                    else
                        propzerowidth=22;
                
                    t+="<div id='"+propname+"_zero'>"; // Colonna numerata
                    
                    for (r=0;r<=proprows;r++){
                        if (r==0)
                            cl="ryque-zhead";
                        else
                            cl="ryque-zrow";
                        t+="<div id='"+propname+"_zr"+r+"' class='"+cl+"' style='top:"+(proprowh*r)+"px;'>";  // Riga colonna zero
                        if(r==0){
                            t+="<div id='"+propname+"_"+r+"_0'><div id='"+propname+"_selicon'></div></div>";
                            t+="<div id='"+propname+"_sep0' class='ryque-sep0'></div>";
                        }
                        else{
                            t+="<div id='"+propname+"_"+r+"_0' class='ryque-cell column_0'></div>";
                        }
                        t+="</div>";
                    }
                    
                    t+="</div>";
                }
                propwinwidth=propwidth-propzerowidth-propscrollsize-1;
                return t;
            }
            function creategrid(){
                var t,r,c,cl,sty,tt;
                t="<div id='"+propname+"_grid'>"; // Griglia
                for (r=0;r<=proprows;r++){
                    if(r==0){
                        cl="ryque-head";
                        if(propsortable)
                            sty="cursor:pointer;";
                    }
                    else{
                        cl="ryque-row";
                        sty="";
                    }
                    t+="<div id='"+propname+"_tr"+r+"' class='"+cl+"' style='top:"+(proprowh*r)+"px;'>";  // Riga
                    for (c=1;c<=propcols.length;c++){
                        if(r==0)
                            tt=proptits[c-1];
                        else
                            tt="&nbsp;";
                        t+="<div id='"+propname+"_"+r+"_"+c+"' class='ryque-cell column_"+c+"' style='"+sty+"'>"+tt+"</div>";  // Colonna
                        if(r==0)
                            t+="<div id='"+propname+"_sep"+c+"' class='ryque-colsep'></div>";   // Separatore
                    }
                    t+="</div>";
                }
                t+="</div>"; // Fine griglia
                return t;
            }
            function setstyle(){
                $("#"+propname)
                    .addClass("ryobject")
                    .addClass("ryque")
                    .width(propwidth)
                    .height(proprowh*(proprows+1)+propscrollsize+1)
                    .css({"position":"absolute","left":propleft,"top":proptop,"font-family":"verdana,sans-serif","font-size":"13px","overflow":"hidden"});
                    
                $("#"+propname+" .column_0")
                    .width(propzerowidth)
                    .height(proprowh)
                    .css({"position":"absolute","left":0,"overflow":"hidden","text-align":"right","white-space":"nowrap","margin":0});
        
                $("#"+propname+"_grid")
                    .css({"position":"absolute","left":-propleftcol});
                    
                $("#"+propname+"_outgrid")
                    .css({"position":"absolute","left":propzerowidth,"width":propwinwidth,"height":(proprowh*(proprows+1)+propscrollsize+2),"overflow":"hidden"});
        
                $("#"+propname+"_sep0")
                    .width(4)
                    .height(proprowh)
                    .css({"left":propzerowidth-4, "cursor":"default"});
                
                fitcolumns();
                
                $("#"+propname+" .ryque-zrow")
                    .height(proprowh)
                    .width(propzerowidth)
                    .css({"position":"absolute"});
                    
                $("#"+propname+" .ryque-zhead")
                    .height(proprowh)
                    .width(propzerowidth);
        
                $("#"+propname+"_zero")
                    .width(propzerowidth)
                    .height(proprowh*(proprows+1))
                    .css({"position":"absolute","left":0,"top":0});
                $("#"+propname+"_anchor").css({"position":"absolute","left":-2,top:0,"width":2,"height":proprowh,"cursor":"default","text-decoration":"none","background-color":"transparent"});
                $("#"+propname+"_vscroll").css({"position":"absolute","background-color":"#E0E0E0","top":proprowh,"left":propwidth-propscrollsize,"width":propscrollsize,"height":proprowh*proprows+1});
                $("#"+propname+"_tooltip").css({"position":"absolute","visibility":"hidden","top":0,"left":0,"border":"1px solid silver","background-color":"#F5DEB3","white-space":"nowrap"});
                $("#"+propname+"_vtrack").css({"height":proptracksize, "width":propscrollsize, "cursor":"pointer"});
                if($.browser.mobile){
                    var semih=(proprowh*proprows+1)/2;
                    $("#"+propname+"_mobivertback").css({"position":"absolute","background-color":"#A0A0A0","top":proprowh,"left":propwidth-propscrollsize,"width":propscrollsize,"height":semih});
                    $("#"+propname+"_mobivertfore").css({"position":"absolute","background-color":"#C0C0C0","top":proprowh+semih,"left":propwidth-propscrollsize,"width":propscrollsize,"height":semih});
                }
                
                $("#"+propname+"_hscroll").css({"position":"absolute","background-color":"#E0E0E0","left":2,"width":propwidth-propscrollsize-2,"height":propscrollsize,"top":(proprowh*(proprows+1)+1)});
                $("#"+propname+"_htrack").css({"width":proptracksize, "cursor":"pointer"});
                if($.browser.mobile){
                    var semiw=(propwidth-propscrollsize-2)/2;
                    $("#"+propname+"_mobihoriback").css({"position":"absolute","background-color":"#A0A0A0","left":2,"width":semiw,"height":propscrollsize,"top":(proprowh*(proprows+1)+1)});
                    $("#"+propname+"_mobihorifore").css({"position":"absolute","background-color":"#C0C0C0","left":2+semiw,"width":semiw,"height":propscrollsize,"top":(proprowh*(proprows+1)+1)});
                }
                
                $("#"+propname+"_rect").css({"left":propwidth-propscrollsize-1, "width":propscrollsize+1, "height":proprowh});
                $("#"+propname+"_quad").css({"position":"absolute","background-color":"#E0E0E0","left":propwidth-propscrollsize,"top":(proprowh*(proprows+1)+1),"width":propscrollsize,"height":propscrollsize});
                $("#"+propname+"_lborder").css({"position":"absolute","left":0,"top":proprowh,"width":2,"height":proprowh*proprows+propscrollsize+1});
                $("#"+propname+"_lborder").addClass("ryque-focusout");
                $("#"+propname+"_textwidth").css({"position":"absolute","visibility":"hidden"});
                
                if(propcheckable){
                    var l=4;
                    $("#"+propname+" .ryque-zhead").css({"cursor":"pointer"});
                    if(propnumbered)
                        l=20;
                    $("#"+propname+"_selicon")
                    .removeClass("ryque-check ryque-uncheck ryque-almostcheck ryque-almostuncheck")
                    .addClass("ryque-check")
                    .css({"position":"absolute", "left":l, "top":2, "width":20, "height":20, "cursor":"pointer"});
                }
                propobj.selrefresh();
            }
            function fitcolumns(){
                var x=0,w,uc=propcols.length,k,c;
                for(c=1;c<=uc;c++){
                    w=propdims[c-1]
                    if(c==uc){
                        if(x+w+1<propwinwidth){  // x+w+1 sara' propgridwidth
                            w=propwinwidth-x-1;
                        }                
                    }
                    if($.isNumeric(proptyps[c-1])){k="right"}
                    else if(proptyps[c-1]=='?'){k="center"}
                    else{k="left"}
                    if(w>0){
                        $("#"+propname+" .column_"+c)
                            .width(w+1)
                            .height(proprowh)
                            .css({"position":"absolute","left":(x-1),"overflow":"hidden","text-align":k,"white-space":"nowrap","margin":0});
            
                        $("#"+propname+"_sep"+c)
                            .width(4)
                            .height(proprowh)
                            .css({"left":(x+w-3), "cursor":"col-resize"});
                    }    
                    else{
                        $("#"+propname+" .column_"+c).css({"position":"absolute","visibility":"hidden"});
                        $("#"+propname+"_sep"+c).css({"position":"absolute","visibility":"hidden"});
                    }
                    x+=w+1;
                }
                propgridwidth=x;
                
                $("#"+propname+" .ryque-row")
                    .height(proprowh)
                    .width(propgridwidth)
                    .css({"position":"absolute","left":0,"overflow":"hidden"});
                $("#"+propname+" .ryque-head")
                    .height(proprowh)
                    .width(propgridwidth)
                    .css({"cursor":"pointer"});
                propobj.hscrefresh();
                propobj.decrefresh(true);
            }
            function fitcolumns2(cc,s){
                var x=0,w,uc=propcols.length,c;
                for(c=1;c<=uc;c++){
                    if(c==cc){
                        w=s-x;
                        if(w<8)
                            w=8;
                        // SE SONO SULLA PENULTIMA COLONNA REGALO SPAZIO ALL'ULTIMA
                        if(c==uc-1 && w<propdims[c-1]){
                            propdims[c]+=propdims[c-1]-w;
                        }
                        propdims[c-1]=w;
                    }
                    else{
                        w=propdims[c-1];
                    }
                    if(w>0)
                        $("#"+propname+"_sep"+c).css({"left":(x+w-3)});
                    else
                        $("#"+propname+"_sep"+c).css({"visibility":"hidden"});
                    x+=w+1;
                }
            }
            function draggablecolumns(){
                $("#"+propname+" .ryque-colsep").dblclick(
                    function(evt){
                        if(!propenabled){return}
                        var c=parseInt(evt.target.id.replace(/^.*_sep(\d+)$/,"$1"));
                        autofit(c);
                    }
                )
                .draggable({
                    axis:"x",
                    containment:"parent",
                	drag:function(evt) {
                        var c=parseInt(evt.target.id.replace(/^.*_sep(\d+)$/,"$1"));
                        var p=$(this).position().left;
                        if(p<_down1){
                            if(p-_down0<8){
                                $(this).css({left:_down0+8});
                                fitcolumns2(c,8);
                                return false;
                            }
                        }
                        fitcolumns2(c,p);
                	},
                    start:function(evt){
                        if(!propenabled){return false}
                        propenabled=false;
                        var c=parseInt(evt.target.id.replace(/^.*_sep(\d+)$/,"$1"));
                        if(c>1)
                            _down0=$("#"+propname+"_sep"+(c-1)).position().left;
                        else
                            _down0=0;
                        _down1=$(this).position().left;
                        $("#"+propname+" .ryque-cell,.column_0").hide();
                    },
                    stop:function(){
                        setTimeout(
                            function(){
                                propenabled=true;
                            }, 500
                        );
                        $("#"+propname+" .ryque-cell,.column_0").show();
                        fitcolumns();
                    }
                });
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
            function autofit(col){
                try{
                    var cols=[],nums=[],typs=[],mins=[],r,c,i,h,m,w;
                    if(col!=missing){
                        cols.push(col-1);
                        typs.push( proptyps[col-1] );
                        nums.push( $.isNumeric(proptyps[col-1]) );
                        $("#"+propname+"_textwidth").html(proptits[col-1]);
                        w=$("#"+propname+"_textwidth").width()+10;
                        mins.push( w>20 ? w : 20 );
                    }
                    else{
                        for(c=1; c<=propcols.length; c++){
                            cols.push(c-1);
                            typs.push( proptyps[c-1] );
                            nums.push( $.isNumeric(proptyps[c-1]) );
                            $("#"+propname+"_textwidth").html(proptits[c-1]);
                            w=$("#"+propname+"_textwidth").width()+10;
                            mins.push( w>20 ? w : 20 );
                        }
                    }
                    // CALCOLO LA LARGHEZZA DEL CARATTERE 0
                    $("#"+propname+"_textwidth").html("XXXXXXXXXX");
                    var xl=$("#"+propname+"_textwidth").width()/10;
                        
                    for(i=0; i<cols.length; i++){
                        c=cols[i];
                        m=0;
                        switch(typs[i]){
                        case "?":
                            m=5*xl;
                            break;
                        case "/":
                            m=10*xl;
                            break;
                        case ":":
                            m=14*xl;
                            break;
                        default:
                            var ml=3;
                            for(r=0; r<propcount; r++){
                                var l=3;
                                h=propobj.matrix[r][propcols[c]];
                                if(typeof h!="string")
                                    h="";
                                else
                                    h=h.replace(/ +$/, "");
                                if(h.substr(0,5)!="<img "){
                                    l=h.length;
                                    if(nums[i]){
                                        l=Math.floor(l*1.2);
                                    }
                                }
                                if(ml<l)
                                    ml=l;
                            }
                            m=xl*ml;
                        }
                        if(m<mins[i])
                            m=mins[i];
                        else if(m>700 && cols.length>1)
                            m=700;
                        m+=8;
                        if(propdims[c]>0)
                            propdims[c]=m;
                    }
                    fitcolumns();
                }
                catch(e){
                    if(window.console){console.log(e.message)}
                }
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
                    if(window.console){console.log(e.message)}
                    stoploading();
                }
            }
            function setheight(h){
                proprows=Math.floor(((h-propscrollsize-2)/proprowh)-1);
            }
            function setnumbered(f){
                propnumbered=f;
                propfirstcol=(propnumbered || propcheckable);
            }
            function setcheckable(f){
                propcheckable=f;
                propfirstcol=(propnumbered || propcheckable);
            }
            function mobilestate(t, v){
                if(t==0){
                    $("#"+propname+"_mobivertback").css({"display":v});
                    $("#"+propname+"_mobivertfore").css({"display":v});
                }
                else{
                    $("#"+propname+"_mobihoriback").css({"display":v});
                    $("#"+propname+"_mobihorifore").css({"display":v});
                }
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
            function selectrow(reff, ev, chk){
                if(1<=reff && reff<=propcount){
                    if(chk==missing)
                        chk=true;
                    if(propselinvert==chk)
                        delete propsels[reff];
                    else
                        propsels[reff]=true;
                    if(ev){
                        propobj.rowdecor(reff-proptoprow+1,true);
                        propobj.selrefresh();
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
                    r=propindex-proptoprow+1;
                    if(r<0)
                        r=0;
                    else if(r>proprows)
                        r=0;
                }
                $("#"+propname+"_anchor").css({top:proprowh*r});
            }
            function statistics(){
                if(propcount>0){
                    var m=proptoprow+proprows-1;
                    if(m>propcount)
                        m=propcount;
                    $("#"+propname+"_rect>a").attr("title","Rows: "+propcount+" / Range: "+proptoprow+"-"+m);
                }
                else{
                    $("#"+propname+"_rect>a").attr("title","Empty");
                }
            }
            function searchmanagement(n){
                var r,b,c="",t=0,l,u=false,prefix="";
                if("ABCDEFGHIJKLMNOPQRSTUVWYXZ0123456789-".indexOf(n)>=0){
                    l=(new Date).getTime();
                    if( l-searchlast>1000 )
                        searchbuff=n;
                    else
                        searchbuff+=n;
                    searchlast=l;
                    if(propordcol>=0){
                        c=propcols[propordcol-1];
                        t=proptyps[propordcol-1];
                        switch(t){
                        case "?":
                            c="";
                            t=1;
                            break;
                        case "/":
                        case ":":
                            t=2;
                            prefix="20";
                            break;
                        default:
                            t=($.isNumeric(t) ? 1 : 0);
                        }
                    }
                    else{
                        for(var i in proptyps){
                            if(proptyps[i]==""){
                                c=propcols[i];
                                u=true;
                                break;
                            }
                        }
                    }
                    if(c!=""){
                        switch(t){
                        case 0:
                            var stone=searchbuff;
                            for(r=0; r<propcount; r++){
                                b=propobj.matrix[r][c];
                                if(typeof b!="undefined"){
                                    b=b.toUpperCase();
                                    if(b.substr(0,stone.length)==stone){
                                        propobj.index(r+1);
                                        break;
                                    }
                                }
                            }
                            break;
                        case 1:
                            var stone=parseFloat(searchbuff);
                            for(r=0; r<propcount; r++){
                                b=propobj.matrix[r][c];
                                if(typeof b!="undefined"){
                                    b=parseFloat(b);
                                    if( ( u&&b==stone) || (propordasc&&b>=stone) || (!propordasc&&b<=stone) ){
                                        propobj.index(r+1);
                                        break;
                                    }
                                }
                            }
                            break;
                        case 2:
                            var stone=prefix+searchbuff;
                            for(r=0; r<propcount; r++){
                                b=propobj.matrix[r][c];
                                if(typeof b!="undefined"){
                                    b=b.replace(/[^\d]/gi, "");
                                    if( (propordasc&&b>=stone) || (!propordasc&&b<=stone)){
                                        propobj.index(r+1);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            function resetcells(){
                if(propcolumn>0){
                    for(var r=1; r<=proprows; r++){
                        var c="#"+propname+"_"+r+"_"+propcolumn;
                        $(c).removeClass("ryque-active-cell");
                    }
                }
            }
            function activecell(){
                if(propcolumn>0 && propindex>0)
                    $( c="#"+propname+"_"+(propindex-proptoprow+1)+"_"+propcolumn ).addClass("ryque-active-cell");
                // Gestione scroll orizzontale
                var cng=false;
                if(propcolumn==1){
                    if(propleftcol>0){
                        propleftcol=0;
                        cng=true;
                    }
                }
                else if(propcolumn==propcols.length){
                    if(propleftcol<propgridwidth-propwinwidth){
                        propleftcol=propgridwidth-propwinwidth;
                        cng=true;
                    }
                }
                else{
                    var offset=-propleftcol;
                    for(var i=0; i<propcolumn-1; i++)
                        offset+=propdims[i];
                    if(offset<0){
                        propleftcol-=Math.abs(offset);
                        cng=true;
                    }
                    else if(offset+propdims[propcolumn-1]>propwinwidth){
                        propleftcol+=(offset+propdims[propcolumn-1]-propwinwidth);
                        cng=true;
                    }
                }
                if(cng){
                    $("#"+propname+"_grid").css({"left":-propleftcol});
                    propobj.hscrefresh();
                }
            }
			return this;
		}
	});
})(jQuery);
