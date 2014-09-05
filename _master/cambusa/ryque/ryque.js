/****************************************************************************
* Name:            ryque.js                                                 *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var _ryquerequests=[];
var _ryquebusy=false;
(function($,missing) {
    $.extend(true,$.fn, {
        ryque:function(settings){
            var propleft=20;
            var proptop=20;
            var propwidth=600;
            var proprows=15;
            
            var propminwidth=propwidth;
            var propmaxwidth=0;
            var t_resize=false;
            var propready=false;
        
            var propenviron="default";
            var propselection="*";
            var propfrom="";
            var propwhere="#";
            var propprovider="";
            var propargs="";
            var propusedparams=false;
            var propclause="";
            var proplimit=500000;
            var propenabled=1;
        
            var propfolderryque=_cambusaURL+"ryque/";
            
            var propcols=new Array();
            var proptits=new Array();
            var propdims=new Array();
            var proptyps=new Array();
            var propfrms=new Array();
            var propmods=new Array();
            var propcodes=new Array();
            
            var propsels={};
            var propselinvert=false;
            
            var propnumbered=false;
            var propcheckable=false;
            var propfirstcol=false;
            
            var propscrollsize=15;
            var proptracksize=30;
            var propname=$(this).attr("id");
            var propreqid="";
            var propreqprivate=true;
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
            
            var propordcol=0;
            var propordlast="";
            var propordsave="";
            var proporddesc=false;
            var proporderby="SYSID";
            var proplastorderby=proporderby;
            
            var proppageon=0;
            var proploadon=false;
            var propvisible=true;
            var propwheel=!$.browser.opera;
            
            var solvetimeout=false;
            
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
            if(settings.environ!=missing){propenviron=settings.environ}
            if(settings.requestid!=missing && settings.provider!=missing){
                if(settings.requestid!="" && settings.provider!=""){
                    propreqid=settings.requestid;
                    propprovider=settings.provider;
                    propreqprivate=false;
                }
            }
            if(settings.selection!=missing){propselection=settings.selection}
            if(settings.from!=missing){propfrom=settings.from}
            if(settings.where!=missing){propwhere=settings.where}
            if(settings.orderby!=missing){
                proporderby=settings.orderby.replace(/(^|,| )DESCRIPTION($|,| )/ig, "$1[:UPPER(DESCRIPTION)]$2");
            }
            if(settings.args!=missing){propargs=settings.args}
            if(settings.clause!=missing){propclause=settings.clause}
            if(settings.limit!=missing){proplimit=settings.limit}
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
            if(settings.formid!=missing){
                // Aggancio alla maschera per quando i campi sono dinamici
                $("#"+propname).prop("parentid", settings.formid);
                _globalforms[settings.formid].controls[propname]=propname.substr(settings.formid.length);
            }

            // FUNZIONI PUBBLICHE
            this.create=function(){
                var t="<a id='"+propname+"_anchor' href='javascript:'></a>";
                
                t+=createzero();
                t+="<div id='"+propname+"_outgrid'>"; // Outer Griglia
                    t+=creategrid();
                t+="</div>"; // Fine outer griglia
                t+="<div id='"+propname+"_rect'></div>";  // prolungamento dell'header sopra vscroll
                t+="<div id='"+propname+"_vscroll'>"; // Scroll verticale
                    t+="<div id='"+propname+"_tooltip'>0-0</div><div id='"+propname+"_vtrack'></div>";
                t+="</div>";
                if(_mobiledetected){
                    t+="<div id='"+propname+"_mobivertback'></div><div id='"+propname+"_mobivertfore'></div>";
                }
                t+="<div id='"+propname+"_hscroll'>"; // Scroll orizzontale
                    t+="<div id='"+propname+"_htrack'></div>";
                t+="</div>";
                if(_mobiledetected){
                    t+="<div id='"+propname+"_mobihoriback'></div><div id='"+propname+"_mobihorifore'></div>";
                }
                t+="<div id='"+propname+"_quad'></div>"; // prolungamento di hscroll
                t+="<div id='"+propname+"_lborder'></div>"; // bordo sinistro
                
                $("#"+propname).html(t);
                setstyle();
                propobj.hscrefresh();

                $("#"+propname+"_anchor").focus(
                    function(){
                        $("#"+propname+"_lborder").removeClass("ryque-focusout");
                        $("#"+propname+"_lborder").addClass("ryque-focusin");
                    }
                );
                $("#"+propname+"_anchor").focusout(
                    function(){
                        $("#"+propname+"_lborder").removeClass("ryque-focusin");
                        $("#"+propname+"_lborder").addClass("ryque-focusout");
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
                            case 13:if(settings.enter!=missing){settings.enter(propobj,propindex)}break;
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
                	start: function() {
                        propobj.tipactivate();
                	},
                	drag: function() {
                        propobj.tipmove(0);
                	},
                	stop: function() {
                        propobj.tipdeactivate();
                        propobj.dataload();
                	}
                });
                $("#"+propname+"_vscroll").mousedown(
                    function(evt){
                        if(!propenabled){return}
                        if(propcount>proprows){
                            var h=$("#"+propname+"_vtrack").offset().top;
                            if (evt.pageY>h+proptracksize){
                                propobj.pagedown(1);
                                propobj.dataload();
                            }
                            else if (evt.pageY<h){
                                propobj.pageup(1);
                                propobj.dataload();
                            }
                        }
                    }
                );
                $("#"+propname+"_htrack").draggable({
                    axis:"x",
                    containment:"parent",
                	drag: function() {
                        var w=$("#"+propname+"_hscroll").width()-proptracksize;
                        var p=$(this).position().left;
                        propleftcol=Math.round((propgridwidth-propwinwidth)*p/w);
                        if(propleftcol>propgridwidth-propwinwidth)
                            propleftcol=propgridwidth-propwinwidth;
                        if (propleftcol<0)
                            propleftcol=0;
                        $("#"+propname+"_grid")
                            .css({"left":-propleftcol});
                	}
                });
                $("#"+propname+" .ryque-colsep").click(
                    function(evt){
                        if(!propenabled){return}
                        var c=parseInt(evt.target.id.replace(/^.*_sep(\d+)$/,"$1"));
                        if(propshift)
                            propobj.fitcolumns(c,2);
                        else
                            propobj.fitcolumns(c,1);
                    }
                );
                $("#"+propname+"_hscroll").mousedown(
                    function(evt){
                        if(!propenabled){return}
                        if(propgridwidth>propwinwidth){
                            var w=$("#"+propname+"_htrack").offset().left;
                            if (evt.pageX>w+proptracksize)
                                propobj.rowright();
                            else if (evt.pageX<w)
                                propobj.rowleft();
                        }
                    }
                );
                if(_mobiledetected){
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
                            propobj.rowleft();
                        }
                    );
                    $("#"+propname+"_mobihorifore").mousedown(
                        function(evt){
                            if(!propenabled){return}
                            propobj.rowright();
                        }
                    );
                }
                $("#"+propname+"_rect").dblclick(
                    function(evt){
                        if(!propenabled){return}
                        var m;
                        for(m in propmods)
                            propmods[m]=0;
                        propobj.fitcolumns(0,0);
                    }
                );
                $("#"+propname).mousedown(
                    function(evt){
                        if(!propenabled){return}
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
                                if(c!=0){
                                    if(reff!=propindex)
                                        propobj.index(reff);
                                }
                                else if(c==0 && propcheckable){
                                    propobj.seltoggle(reff);
                                }
                            }
                        }
                        else{
                            if(c>0){
                                var ord=propfrms[c-1];
                                if(ord==""){
                                    ord=propcols[c-1];
                                    if(proptyps[c-1]=="")
                                        ord="[:UPPER("+ord+")]"
                                }
                                if(propordcol!=c)
                                    proporddesc=false;
                                else
                                    proporddesc=!proporddesc;
                                if(proporddesc)
                                    ord="("+ord+") DESC";
                                // GESTIONE ORDER BY CON MEMORIA DEL PRECEDENTE
                                var lo=ord;
                                if(propordcol!=c){
                                    if(propordlast!="")
                                        ord=ord+","+propordlast;
                                    propordsave=propordlast;
                                }
                                else if(propordsave!=""){
                                    ord=ord+","+propordsave;
                                }
                                propordlast=lo;
                                // FINE GESTIONE
                                var args=propargs;
                                var lim=proplimit;
                                if(propusedparams!==false){
                                    args=propusedparams.args;
                                    lim=propusedparams.limit;
                                }
                                propobj.query({"orderby":ord, "args":args, "limit":lim, "selpreserve":true});
                                propordcol=c;
                            }
                            else if(c==0 && propcheckable){
                                if(_objectlength(propsels)==propcount && !propselinvert){
                                    propsels={};
                                }
                                else if(_objectlength(propsels)>0 && propselinvert){
                                    propsels={};
                                    propselinvert=false;
                                }
                                else{
                                    propselinvert=!propselinvert;
                                }
                                for(r=1;r<=proprows;r++)
                                    propobj.rowdecor(r,true);
                                propobj.selrefresh();
                                // Gestione eventi e callback
                                if(settings.selchange!=missing){settings.selchange(propobj,0)}
                            }
                        }
                        if(RYBOX)
                            castFocus(propname);
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
                                    setTimeout(function(){settings.cellclick(propobj, r ,c)}, 50);
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
                $("#"+propname).contextMenu("ryque_menu", {
                    bindings: {
                        'ryque_sheet': function(t) {
                            propobj.sheet({});
                        }
                    },
                    onContextMenu:
                        function(e) {
                            if(propcount==0 || $("#winz-iframe").length==0)
                                return false;
                            else 
                                return true;
                        },
                    onShowMenu: 
                        function(e, menu) {
                            return menu;
                        }
                });
                if(propmaxwidth>propminwidth){
                    var par=$("#"+propname).parents(".window_main");
                    if(par.length>0){
                        var id=par[0]["id"];
                        RYWINZ.forms( id.substr(5) )._kresize=function(w,h){
                            propwidth=w-2*propleft-20;
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

                $("#"+propname+" .ryque-colsep").click(
                    function(evt){
                        if(!propenabled){return}
                        var c=parseInt(evt.target.id.replace(/^.*_sep(\d+)$/,"$1"));
                        if(propshift)
                            propobj.fitcolumns(c,2);
                        else
                            propobj.fitcolumns(c,1);
                    }
                );
                
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
            this.where=function(w){
                propwhere=w;
            }
            this.clause=function(w){
                propclause=w;
            }
            this.limit=function(l){
                proplimit=l;
            }
            this.query=function(params){
                if(params==missing)
                    params={};
                queryaux(params);
            }
            this.refresh=function(){
                queryaux();
            }
            this.dataload=function(chain){
                if(propreqid=="" || propready==false){
                    return;
                }
                if(proploadon==true){
                    ryqueUnready("occupato");
                    setTimeout(function(){propobj.dataload(chain)}, 500);
                    return;
                }
                proploadon=true;
                propobj.vscrefresh();
                $.post(propfolderryque+"ryq_window.php", {"reqid":propreqid,"offset":proptoprow,"length":proprows,"clause":propclause},
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            var r,c,fd,vl,reff;
                            var dd,dm,dy;
                            if(settings.before!=missing){
                                settings.before(propobj, v);
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
                                        try{
                                            switch(proptyps[c-1]){
                                                case "?":
                                                    if (vl!=0)
                                                        $(fd).html("&#x2714;");
                                                    else
                                                        $(fd).html("&#x0020;");
                                                    break;
                                                case "0":case "1":case "2":case "3":case "4":case "5":
                                                    vl=_nformat(vl,proptyps[c-1]);
                                                    if(vl==="NaN"){vl=""}
                                                    $(fd).html(vl);break;
                                                case "/":
                                                    if(vl.length>=10){
                                                        dy=vl.substr(0,4);dm=vl.substr(5,2);dd=vl.substr(8,2);
                                                        if( (dd=="01" && dm=="01" && dy=="1900") || (dd=="31" && dm=="12" && dy=="9999") )
                                                            $(fd).html("");
                                                        else
                                                            $(fd).html(dd+"/"+dm+"/"+dy);
                                                    }
                                                    else{
                                                        $(fd).html("");
                                                    }
                                                    break;
                                                case ":":
                                                    if(vl.length>=10){
                                                        dy=vl.substr(0,4);dm=vl.substr(5,2);dd=vl.substr(8,2);
                                                        if( (dd=="01" && dm=="01" && dy=="1900") || (dd=="31" && dm=="12" && dy=="9999") )
                                                            $(fd).html("");
                                                        else
                                                            $(fd).html(vl.substr(8,2)+"/"+vl.substr(5,2)+"/"+vl.substr(0,4)+" "+vl.substr(11,2)+":"+vl.substr(14,2));
                                                    }
                                                    else{
                                                        $(fd).html("");
                                                    }
                                                    break;
                                                default:
                                                    vl=vl.replace(/<[bh]r\/?>/gi," ");
                                                    $(fd).html(vl);
                                                    if(vl.length>20 && vl.substr(0,5)!="<img ")
                                                        $(fd).attr("title",vl);
                                                    else
                                                        $(fd).attr("title","");
                                            }
                                        }
                                        catch(e){
                                            $(fd).html("");
                                        }
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
                            alert(d);
                        }
                        propobj.decrefresh(true);
                        proploadon=false;
                        if(chain!=missing){
                            chain();
                        }
                    }
                )
                .fail(
                    function(){
                        ryqueFail("dataload");
                        proploadon=false;
                        setTimeout(function(){propobj.dataload(chain)}, 100);
                    }
                );
           }
            this.screencell=function(r,c){
                return "#"+propname+"_"+(parseInt(r)+1)+"_"+c;
            }
            this.screenrow=function(r,c){
                return "#"+propname+"_tr"+(parseInt(r)+1);
            }
            this.clear=function(){
                if(raisebeforechange(0)){return}
                var r,c,fd,reff;
                proploadon=true;
                propcount=0;
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
            }
            this.selinvert=function(){
                return propselinvert;
            }
            this.selengage=function(back, noselection){
                var k=propobj.checked();
                if(k=="" && !propselinvert){
                    if(propindex>0)
                        k=propindex.toString();
                }
                if(k!="" || propselinvert)
                    propobj.solveid(k, back, propselinvert);
                else if(noselection!=missing)
                    noselection();
            }
            this.solveid=function(ind, back, invert){
                if(solvetimeout!==false){
                    clearTimeout(solvetimeout);
                    solvetimeout=false;
                }
                if(invert==missing){
                    invert=0;
                }
                solvetimeout=setTimeout(
                    function(){
                        solvetimeout=false;
                        if(ind=="@" && propcount>0){
                            // SE VIENE PASSATO @ INTENDO SELEZIONARE TUTTI GLI INDICI
                            ind="1";
                            for(var i=2; i<=propcount; i++){
                                ind+="|"+i;
                            }
                        }
                        $.post(propfolderryque+"ryq_solve.php", {"reqid":propreqid,"index":ind,"invert":_bool(invert)},
                            function(d) {
                                if(back==missing){
                                    if(settings.solveid!=missing){settings.solveid(propobj,d)}
                                }
                                else{
                                    back(propobj,d);
                                }
                            }
                        )
                        .fail(
                            function(){
                                ryqueFail("solveid");
                                setTimeout(function(){propobj.solveid(ind, back, invert)}, 100);
                            }
                        );
                    }
                    , 200
                );
            }
            this.selbyid=function(ids, sing, done){
                if(ids!=""){
                    if(raisebeforechange(0)){return}
                    $.post(propfolderryque+"ryq_selbyid.php", {"reqid":propreqid,"listid":ids},
                        function(d){
                            propindex=0;
                            propselinvert=false;
                            propsels={};
                            if(d!=""){
                                if(sing==missing)
                                    sing=true;
                                var u=d.split("|");
                                if(u.length==1 && sing){
                                    propobj.index(parseInt(u[0]));
                                }
                                else{
                                    for(var i in u)
                                        propsels[parseInt(u[i])]=true;
                                }
                            }
                            propobj.decrefresh(true);
                            propobj.selrefresh();
                            if(done!=missing){
                                done();
                            }
                        }
                    )
                    .fail(
                        function(){
                            ryqueFail("selbyid");
                            setTimeout(function(){propobj.selbyid(ids, sing, done)}, 100);
                        }
                    );
                }
                else{
                    propselinvert=false;
                    propsels={};
                    propobj.decrefresh(true);
                    propobj.selrefresh();
                    if(done!=missing){
                        done();
                    }
                }
            }
            this.setchecked=function(list){
                propselinvert=false;
                propsels={};
                if(list!=""){
                    var u=list.split("|");
                    for(var i in u)
                        propsels[parseInt(u[i])]=true;
                }
                propobj.decrefresh(true);
                propobj.selrefresh();
                if(settings.selchange!=missing){settings.selchange(propobj, 0)}
            }
            this.checked=function(){
                var i,r="";
                for(i in propsels){
                    if(r!="")
                        r+="|";
                    r+=i;
                }
                return r;
            }
            this.ischecked=function(){
                return _bool( _objectlength(propsels)>0 || propselinvert );
            }
            this.isselected=function(){
                return _bool( _objectlength(propsels)>0 || propselinvert || propindex>0);
            }
            this.checkall=function(f){
                if(f==missing){f=true}
                propsels={};
                propselinvert=(f ? true : false);
                for(r=1;r<=proprows;r++)
                    propobj.rowdecor(r,true);
                propobj.selrefresh();
                // Gestione eventi e callback
                if(settings.selchange!=missing){settings.selchange(propobj, 0)}
            }
            this.dispose=function(done){
                if(propreqid!=""&&propreqprivate==true){
                    $.post(propfolderryque+"ryq_close.php", {"reqid":propreqid},
                        function(d){
                            propreqid="";
                            if(done!=missing){
                                setTimeout(function(){done()});
                            }
                        }
                    )
                    .fail(
                        function(){
                            ryqueFail("dispose");
                            setTimeout(function(){propobj.dispose(done)}, 100);
                        }
                    );
                    if(done==missing){
                        _pause(100);
                    }
                }
                else{
                    if(done!=missing){setTimeout(function(){done()})}
                }
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
                    }
                    else{
                        propobj.decrefresh(true);
                        propobj.indrefresh();
                    }
                }
            }
            this.rowright=function(){
                propleftcol+=50;
                if(propleftcol>propgridwidth-propwinwidth)
                    propleftcol=propgridwidth-propwinwidth;
                $("#"+propname+"_grid")
                    .css({"left":-propleftcol});
                propobj.hscrefresh();
            }
            this.rowleft=function(){
                propleftcol-=50;
                if(propleftcol<0)
                    propleftcol=0;
                $("#"+propname+"_grid")
                    .css({"left":-propleftcol});
                propobj.hscrefresh();
            }
            this.rowhome=function(){
                propleftcol=0;
                $("#"+propname+"_grid")
                    .css({"left":-propleftcol});
                propobj.hscrefresh();
            }
            this.rowend=function(){
                propleftcol=propgridwidth-propwinwidth;
                if(propleftcol<0)
                    propleftcol=0;
                $("#"+propname+"_grid")
                    .css({"left":-propleftcol});
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
                    if(_mobiledetected){
                        mobilestate(0, "block");
                    }
                }
                else{
                    t.css({"top":0,"visibility":"hidden"});
                    if(_mobiledetected){
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
                    if(_mobiledetected){
                        mobilestate(1, "block");
                    }
                }
                else{
                    propleftcol=0;
                    $("#"+propname+"_grid").css({"position":"absolute","left":-propleftcol});
                    t.css({"left":0,"visibility":"hidden"});
                    if(_mobiledetected){
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
            }
            this.gotofirst=function(){
                proptoprow=1;
                setfocusable();
                propobj.dataload();
            }
            this.gotolast=function(){
                proptoprow=propmaxtoprow;
                setfocusable();
                propobj.dataload();
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
                if(propfirstcol){
                    var fd="#"+propname+"_zr"+gr;
                    var s=false;
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
                $(fd).removeClass("ryque-row-even ryque-row-odd ryque-row-selected ryque-row-checked");
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
            this.selrefresh=function(){
                if(propcheckable){
                    var fd="#"+propname+"_0_0";
                    var s=$("#"+propname+"_selicon");
                    var icon="check.gif";
                    var sels=_objectlength(propsels);
                    if(sels>0){
                        if(sels<propcount){
                            if(propselinvert)
                                icon="almostcheck.gif";
                            else
                                icon="almostuncheck.gif";
                        }
                        else{
                            if(!propselinvert)
                                icon="uncheck.gif";
                        }
                    }        
                    else{
                        if(propselinvert)
                            icon="uncheck.gif";
                    }
                    s.css({"background":"transparent url("+propfolderryque+"images/"+icon+") no-repeat"});
                }
            }
            this.seltoggle=function(reff){
                if(reff==0)
                    reff=propindex;
                var r=reff-proptoprow+1;
                var fd="#"+propname+"_zr"+r;
                if(reff<=propcount){
                    if(reff in propsels)
                        delete propsels[reff];
                    else
                        propsels[reff]=true;
                    propobj.rowdecor(r,true);
                    propobj.selrefresh();
		            // Gestione eventi e callback
		            if(settings.selchange!=missing){settings.selchange(propobj,reff)}
                }
            }
            this.name=function(){
                return propname;
            }
            this.count=function(){
                return propcount;
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
                        propobj.dataload(
                            function(){
                                propobj.raisechangerow();
                            }
                        );
                    }
                }
                return propindex;
            }
            this.numbered=function(f){
                if(f!=missing)
                    setnumbered(f);
                return propnumbered;
            }
            this.checkable=function(f){
                if(f!=missing)
                    setcheckable(f);
                return propcheckable;
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
            this.selection=function(){
                return propselection;
            }
            this.reqid=function(id){
                if(id!=missing){
                    propreqid=id;
                }
                return propreqid;
            }
            this.tipactivate=function(){
                $("#"+propname+"_tooltip").css({"visibility":"visible","left":-2,"top":4});
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
                var t=$("#"+propname+"_tooltip");
                t.html("");
                t.css({"visibility":"hidden"});
            }
            this.fitcolumns=function(cc,m){
                var x=0,w,dw,dl,uc=propcols.length;
                for(c=1;c<=uc;c++){
                    if(c==cc){
                        if(propmods[c-1]==0)
                            propmods[c-1]=m;
                        else
                            propmods[c-1]=0;
                    }
                    switch(propmods[c-1]){
                        case 1:w=10;break;
                        case 2:w=500;break;
                        default:w=propdims[c-1];
                    }
                    if(c==uc){
                        if(x+w+1<propwinwidth){  // x+w+1 sara' propgridwidth
                            w=propwinwidth-x-1;
                        }                
                    }
                    if($.isNumeric(proptyps[c-1])){k="right";dw=8;dl=2;}
                    else if(proptyps[c-1]=='?'){k="center";dw=8;dl=4;}
                    else{k="left";dw=8;dl=4;}
                    
                    if(w>0){
                        $("#"+propname+" .column_"+c)
                            .width(w-dw)
                            .height(proprowh)
                            .css({"position":"absolute","left":(x+dl),"overflow":"hidden","text-align":k,"white-space":"nowrap","padding":0,"margin":0});
            
                        $("#"+propname+"_sep"+c)
                            .width(4)
                            .height(proprowh)
                            .css({"position":"absolute","left":(x+w-3),"overflow":"hidden","background":"transparent url("+propfolderryque+"images/colsep.gif) no-repeat right","cursor":"col-resize"});
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
                    .css({"position":"absolute","left":0,"color":"#000000","cursor":"pointer","background":"transparent url("+propfolderryque+"images/faded.gif) repeat-x"});
                propobj.hscrefresh();
                propobj.decrefresh(true);
            }
			this.babelcode=function(k){
				return propcodes[k-1];
			}
			this.caption=function(k,c){
				proptits[k-1]=c;
                $("#"+propname+"_0_"+k).html(c);
			}
            this.raisechangerow=function(){
                setfocusable();
                if(propwhere!="#"){ // Qualcosa deve essere stato fatto prima
                    if(settings.changerow!=missing){settings.changerow(propobj,propindex)}
                }
            }
			this.enabled=function(v){
				if(v==missing){
					return propenabled;
				}
				else{
					propenabled=_bool(v);
				}
                return propenabled;
			}
			this.search=function(criteria, action){
                $.post(propfolderryque+"ryq_search.php", {"reqid":propreqid,"criteria":criteria},
                    function(d) {
                        try{
                            action(d);
                        }
                        catch(e){
                            alert(d);
                        }
                    }
                )
                .fail(
                    function(){
                        ryqueFail("search");
                        setTimeout(function(){propobj.search(criteria, action)}, 100);
                    }
                );
			}
			this.splice=function(start, length, adding, done){
                if(start==0)
                    start=propcount+1;
                $.post(propfolderryque+"ryq_splice.php", {"reqid":propreqid, "start":start, "length":length, "adding":adding},
                    function(d){
                        propcount+=(adding.split("|").length-length);
                        propmaxtoprow=propcount-proprows+1;
                        if(propmaxtoprow<1)
                            propmaxtoprow=1;
                        propsels={};
                        propselinvert=false;
                        propobj.index(start);
                        if(proptoprow<=propindex && propindex<=proptoprow+proprows-1){
                            propobj.selrefresh();
                            propobj.dataload(done);
                        }
                        else if(done!=missing){
                            done();
                        }
                    }
                )
                .fail(
                    function(){
                        ryqueFail("splice");
                        setTimeout(function(){propobj.splice(start, length, adding, done)}, 100);
                    }
                );
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
            this.provider=function(){
                return propprovider;
            }
            this.lastorderby=function(){
                return proplastorderby;
            }
            this.getprotocol=function(back){
                if(propreqid==""){
                    $.post(propfolderryque+"ryq_request.php", {"env":propenviron,"sessionid":_sessionid},
                        function(d) {
                            try{
                                var v=$.parseJSON(d);
                                if(v["success"]){
                                    propreqid=v["reqid"];
                                    propprovider=v["provider"];
                                    if(back!=missing){back()}
                                    // Gestione eventi e callback
                                    if(settings.initialized!=missing){settings.initialized(propobj)}
                                    if(propwhere!="#"){
                                        propobj.query();
                                    }
                                    else{
                                        // Gestione eventi e callback
                                        if(settings.ready!=missing){settings.ready(propobj,false)}
                                    }
                                }
                                else{
                                    alert(v["description"]);
                                }
                            }
                            catch(e){
                                alert(d);
                            }
                        }
                    )
                    .fail(
                        function(){
                            ryqueFail("getprotocol");
                            setTimeout(function(){propobj.getprotocol(back)}, 100);   
                        }
                    );
                }
                else{
                    if(back!=missing){back()}
                    // Gestione eventi e callback
                    if(settings.initialized!=missing){settings.initialized(propobj)}
                    if(propwhere!="#"){
                        propobj.query();
                    }
                    else{
                        // Gestione eventi e callback
                        if(settings.ready!=missing){settings.ready(propobj,false)}
                    }
                }
            }
            this.extract=function(params){
                var args="";
                if(params.args!=missing){args=params.args}
                if(params.sql!=missing){
                    $.post(propfolderryque+"ryq_query.php", {"reqid":propreqid,"sql":params.sql,"args":args},
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(params.ready!=missing){
                                    params.ready(v);
                                }
                            }
                            catch(e){
                                alert(d);
                            }
                        }
                    ).fail(
                        function(){
                            ryqueFail("extract");
                            setTimeout(function(){propobj.extract(params)}, 100);   
                        }
                    );
                }
            }
            this.sheet=function(params){
                if($("#winz-iframe").length>0){
                    startloading();
                    var columns={
                        "id":propcols,
                        "caption":proptits,
                        "dim":propdims,
                        "type":proptyps
                    };
                    $.post(propfolderryque+"ryq_export.php", 
                        {
                            "reqid":propreqid,
                            "columns":columns,
                            "clause":propclause,
                            "checked":propobj.checked(),
                            "invert":_bool(propobj.selinvert())
                        }, 
                        function(d){
                            try{
                                var s=_getinteger(d.substr(0,1));
                                var f=d.substr(1);
                                if(s>0){
                                    var h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+f;
                                    $("#winz-iframe").prop("src", h);
                                    // GESTIONE FILE OBSOLETI
                                    try{
                                        if(qv_handletemp!==false)
                                            clearTimeout(qv_handletemp);
                                        qv_handletemp=setTimeout("qv_managetemp()", 10000);
                                    }catch(e){}
                                }
                            }
                            catch(e){
                                alert(d);
                            }
                            stoploading();
                        }
                    )
                    .fail(
                        function(){
                            ryqueFail("sheet");
                            setTimeout(function(){propobj.sheet(params)}, 100);
                        }
                    );
                }
            }
            // CHIAMATA ALLA GENERAZIONE EFFETTIVA
            try{this.create();}catch(e){}
            try{if(RYBOX){RYBOX.addobject(propobj);}}catch(e){}  // Lo aggiungo a RYBOX per il multilingua
            _ryquerequests.push(this.getprotocol);
            _ryquebusy=false;
            ryque_requests();
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
                            t+="<div id='"+propname+"_sep0'></div>";
                        }
                        else{
                            t+="<div id='"+propname+"_"+r+"_0' class='column_0' style='top:3px;'></div>";
                        }
                        t+="</div>";
                    }
                    
                    t+="</div>";
                }
                propwinwidth=propwidth-propzerowidth-propscrollsize-1;
                return t;
            }
            function creategrid(){
                var t,r,c,cl,tt;
                t="<div id='"+propname+"_grid'>"; // Griglia
                for (r=0;r<=proprows;r++){
                    if (r==0)
                        cl="ryque-head";
                    else
                        cl="ryque-row";
                    t+="<div id='"+propname+"_tr"+r+"' class='"+cl+"' style='top:"+(proprowh*r)+"px;'>";  // Riga
                    for (c=1;c<=propcols.length;c++){
                        if(r==0)
                            tt=proptits[c-1];
                        else
                            tt="&nbsp;";
                        t+="<div id='"+propname+"_"+r+"_"+c+"' class='column_"+c+"' style='top:3px;'>"+tt+"</div><div id='"+propname+"_sep"+c+"' class='ryque-colsep'></div>";  //Colonna
                    }
                    t+="</div>";
                }
                t+="</div>"; // Fine griglia
                return t;
            }
            function setstyle(){
                $("#"+propname)
                    .addClass("ryque")
                    .width(propwidth)
                    .height(proprowh*(proprows+1)+propscrollsize+2)
                    .css({"position":"absolute","left":propleft,"top":proptop,"font-family":"verdana","font-size":"13px"});
                    
                $("#"+propname+" .column_0")
                    .width(propzerowidth-8)
                    .height(proprowh)
                    .css({"position":"absolute","left":4,"overflow":"hidden","text-align":"right","white-space":"nowrap","padding":0,"margin":0});
        
                $("#"+propname+"_grid")
                    .css({"position":"absolute","left":-propleftcol});
                    
                $("#"+propname+"_outgrid")
                    .css({"position":"absolute","left":propzerowidth,"width":propwinwidth,"height":(proprowh*(proprows+1)+propscrollsize+2),"overflow":"hidden"});
        
                $("#"+propname+"_sep0")
                    .width(4)
                    .height(proprowh)
                    .css({"position":"absolute","left":propzerowidth-4,"overflow":"hidden","background":"transparent url("+propfolderryque+"images/colsep.gif) no-repeat right","cursor":"default"});
                
                propobj.fitcolumns(0,0);
                
                $("#"+propname+" .ryque-zrow")
                    .height(proprowh)
                    .width(propzerowidth)
                    .css({"position":"absolute"});
                    
                $("#"+propname+" .ryque-zhead")
                    .height(proprowh)
                    .width(propzerowidth)
                    .css({"position":"absolute","text-align":"center","color":"#000000","background":"transparent url("+propfolderryque+"images/faded.gif) repeat-x"});
        
                $("#"+propname+"_zero")
                    .width(propzerowidth)
                    .height(proprowh*(proprows+1))
                    .css({"position":"absolute","left":0,"top":0});
                $("#"+propname+"_anchor").css({"position":"absolute","left":-2,top:0,"width":2,"height":proprowh,"cursor":"default","text-decoration":"none","background-color":"transparent"});
                $("#"+propname+"_vscroll").css({"position":"absolute","background-color":"#E0E0E0","top":proprowh,"left":propwidth-propscrollsize,"width":propscrollsize,"height":proprowh*proprows+1});
                $("#"+propname+"_tooltip").css({"position":"absolute","visibility":"hidden","top":0,"left":0,"border":"1px solid silver","background-color":"#F5DEB3","white-space":"nowrap"});
                $("#"+propname+"_vtrack").css({"position":"absolute","visibility":"hidden","background":"transparent url("+propfolderryque+"images/vtrack.gif) no-repeat","height":proptracksize,"width":propscrollsize,"top":0,"left":0,"cursor":"pointer"});
                if(_mobiledetected){
                    var semih=(proprowh*proprows+1)/2;
                    $("#"+propname+"_mobivertback").css({"position":"absolute","background-color":"#A0A0A0","top":proprowh,"left":propwidth-propscrollsize,"width":propscrollsize,"height":semih});
                    $("#"+propname+"_mobivertfore").css({"position":"absolute","background-color":"#C0C0C0","top":proprowh+semih,"left":propwidth-propscrollsize,"width":propscrollsize,"height":semih});
                }
                
                var iecorrection=0;
                //if($.browser.msie)
                //    iecorrection=1;
                    
                $("#"+propname+"_hscroll").css({"position":"absolute","background-color":"#E0E0E0","left":2,"width":propwidth-propscrollsize-2,"height":propscrollsize,"top":(proprowh*(proprows+1)+1)});
                $("#"+propname+"_htrack").css({"position":"absolute","visibility":"hidden","background":"transparent url("+propfolderryque+"images/htrack.gif) no-repeat","left":0,"top":0,"width":proptracksize,"height":15,"cursor":"pointer"});
                if(_mobiledetected){
                    var semiw=(propwidth-propscrollsize-2)/2;
                    $("#"+propname+"_mobihoriback").css({"position":"absolute","background-color":"#A0A0A0","left":2,"width":semiw,"height":propscrollsize,"top":(proprowh*(proprows+1)+1)});
                    $("#"+propname+"_mobihorifore").css({"position":"absolute","background-color":"#C0C0C0","left":2+semiw,"width":semiw,"height":propscrollsize,"top":(proprowh*(proprows+1)+1)});
                }
                
                $("#"+propname+"_rect").css({"position":"absolute","left":propwidth-propscrollsize-1,"top":0,"width":propscrollsize+1,"height":proprowh,"background":"transparent url("+propfolderryque+"images/faded.gif) repeat-x"});
                $("#"+propname+"_quad").css({"position":"absolute","background-color":"#E0E0E0","left":propwidth-propscrollsize,"top":(proprowh*(proprows+1)+1)-iecorrection,"width":propscrollsize,"height":propscrollsize});
                $("#"+propname+"_lborder").css({"position":"absolute","left":0,"top":proprowh,"width":2,"height":proprowh*proprows+propscrollsize+1});
                $("#"+propname+"_lborder").addClass("ryque-focusout");
                
                if(propcheckable){
                    var l=4;
                    $("#"+propname+" .ryque-zhead").css({"cursor":"pointer"});
                    if(propnumbered)
                        l=20;
                    $("#"+propname+"_selicon").css({"position":"absolute","left":l,"top":2,"width":20,"height":20,"background":"transparent url("+propfolderryque+"images/check.gif) no-repeat"});
                }
                propobj.selrefresh();
            }
            function addcolumn(params){
                var l=propcols.length;
                var colid="",tit="",dim=100,typ="",form="",code="";
                if(params.id!=missing){colid=params.id}
                if(params.caption!=missing){tit=params.caption}
                if(params.width!=missing){dim=params.width}
                if(params.type!=missing){typ=params.type}
                if(params.formula!=missing){form=params.formula}
                if(params.code!=missing){code=params.code}
                if (0<dim && dim<10)
                    dim=10;
                propcols[l]=colid;
                proptits[l]=tit;
                proptyps[l]=typ;
                propdims[l]=dim;
                propfrms[l]=form;
                propcodes[l]=code;
                if(propselection=="*")
                    propselection="";
                else
                    propselection+=",";
                if(form!="")
                    propselection+="("+form+") AS "+colid;
                else
                    propselection+=colid;
                propmods[l]=0;
                return dim;
            }
            function queryaux(params){
                if(propreqid==""){
                    ryqueUnready("non pronto");
                    setTimeout(function(){queryaux(params)}, 200);
                }
                else{
                    callquery(params);
                }
            }
            function callquery(params){
                if(raisebeforechange(0)){return}
                startloading();
                if(propwhere=="#")
                    propwhere="";
                var whe=propwhere;
                var ord=proporderby;
                var selpreserve=false;
                var prei=0;
                var pres="";
                var args=propargs;
                var lim=proplimit;
                if(params==missing){
                    // I parametri non sono passati: riutilizzo gli ultimi
                    if(propusedparams!==false){
                        params=propusedparams;
                        params.index=propindex;
                        delete params.sels;
                    }
                }
                if(params!=missing){
                    if(params.where!=missing){whe=params.where}
                    if(params.orderby!=missing){ord=params.orderby}
                    if(params.selpreserve!=missing){selpreserve=params.selpreserve}
                    if(params.index!=missing){prei=params.index}
                    if(params.args!=missing){args=params.args}
                    if(params.limit!=missing){lim=params.limit}
                }
                previndex=-1;
                if(selpreserve){
                    var i;
                    prei=propindex;
                    for(i in propsels){
                        if(pres!="")
                            pres+="|";
                        pres+=i; 
                    }
                    propsels={};
                }
                else{
                    proptoprow=1;
                    propindex=0;
                    propsels={};
                    propselinvert=false;
                    propordcol=0;
                    propordlast="";
                    propordsave="";
                    proporddesc=false;
                    propobj.rowhome();
                }
                if(settings.beforequery!=missing){
                    var subparams={orderby:ord};
                    settings.beforequery(subparams)
                    ord=subparams.orderby;
                }
                proplastorderby=ord;
                propusedparams={"reqid":propreqid,"select":propselection,"from":propfrom,"where":whe,"orderby":ord,"index":prei,"sels":pres,"args":args,"limit":lim};
                if(window.console&&_sessioninfo.debugmode){console.log(propusedparams)}
                $.post(propfolderryque+"ryq_index.php", propusedparams,
                    function(d) {
                        try{
                            var v=$.parseJSON(d);
                            var sels=v.sels;
                            var ind=parseInt(v.index);
                            
                            propcount=v["count"];
                            propmaxtoprow=propcount-proprows+1;
                            if(propmaxtoprow<1)
                                propmaxtoprow=1;
                            if(ind>0){ // Gestione nuovo index
                                propindex=ind;
                                proptoprow=propindex-Math.floor(proprows/2)+2;
                                propobj.fittoprow();
                            }
                            if(propindex>propcount){ // Controllo di sicurezza qualora righe vengano cancellate
                                propindex=propcount;
                                proptoprow=propindex-Math.floor(proprows/2)+2;
                                propobj.fittoprow();
                            }
                            if(sels!=""){ // Gestione nuove righe selezionate
                                var u=sels.split("|");
                                var i;
                                for(i in u)
                                    propsels[u[i]]=true;
                            }
                            propobj.selrefresh();
                            propready=true;
                            propobj.dataload();
                            stoploading();
                            // Gestione eventi e callback
                            if(settings.ready!=missing){settings.ready(propobj,true)}
                            if(params!=missing){
                                if(params.ready!=missing){
                                    params.ready(propobj,true)
                                }
                            }
                            if(ind==0){
                                propobj.raisechangerow();
                            }
                            if(!selpreserve){
                                if(settings.selchange!=missing){settings.selchange(propobj, 0)}
                            }
                        }
                        catch(e){
                            if(window.console){console.log(e.message)}
                            stoploading();
                            alert(d);
                        }
                    }
                )
                .fail(
                    function(){
                        ryqueFail("callquery");
                        setTimeout(function(){callquery(params)}, 100);
                    }
                );
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
            function selectrow(reff, ev){
                if(1<=reff && reff<=propcount){
                    if(propselinvert)
                        delete propsels[reff];
                    else
                        propsels[reff]=true;
                    if(ev){
                        propobj.rowdecor(reff-proptoprow+1,true);
                        propobj.selrefresh();
                        // Gestione eventi e callback
                        if(settings.selchange!=missing){settings.selchange(propobj,propindex)}
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
                        r=proprows;
                }
                $("#"+propname+"_anchor").css({top:proprowh*r});
            }
			return this;
		}
	});
})(jQuery);
		
function ryQue(missing){
    var propfolderryque=_cambusaURL+"ryque/";
    var propenviron="";
    var propprovider="";
    var proplenid=12;
    var propreqid="";
    var propobj=this;
    this.request=function(params){
       var env=propenviron;
       if(params.environ!=missing){env=params.environ}
       $.post(propfolderryque+"ryq_request.php", {"env":env,"sessionid":_sessionid},
            function(d){
                try{
                    var v=$.parseJSON(d);
                    propreqid=v["reqid"];
                    propprovider=v["provider"];
                    proplenid=v["lenid"];
                    if(params.ready!=missing){params.ready(propreqid,propprovider,proplenid)}
                }
                catch(e){
                    alert(d);
                }
            }
        )
        .fail(
            function(){
                ryqueFail("request");
                setTimeout(function(){propobj.request(params)}, 100);
            }
        );
    }
    this.query=function(params){
        var args="";
        if(params.args!=missing){args=params.args}
        if(params.sql!=missing){
            $.post(propfolderryque+"ryq_query.php", {"reqid":propreqid,"sql":params.sql,"args":args},
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(params.ready!=missing){
                            params.ready(v);
                        }
                    }
                    catch(e){
                        alert(d);
                    }
                }
            ).fail(
                function(){
                    ryqueFail("query");
                    setTimeout(function(){propobj.query(params)}, 100);   
                }
            );
        }
    }
    this.dispose=function(done){
        if(propreqid!=""){
            $.post(propfolderryque+"ryq_close.php", {"reqid":propreqid}, 
                function(d){
                    if(done!=missing){
                        setTimeout(function(){done()});
                    }
                }
            )
            .fail(
                function(){
                    ryqueFail("dispose");
                    setTimeout(function(){propobj.dispose(done)}, 100);
                }
            );
            if(done==missing){_pause(100)}
        }
        else{
            if(done!=missing){setTimeout(function(){done()})}
        }
    }
    this.lenid=function(){
        return proplenid;
    }
    this.provider=function(){
        return propprovider;
    }
    this.formatid=function(baseid){
        // 35 (base 36 - primo carattere) - 12 (minima lunghezza SYSID) ===> 23
        baseid+="00000000000000000000000";
        return baseid.substr(0,proplenid);
    }
    this.reqid=function(id){
        if(id!=missing)
            propreqid=id;
        return propreqid;
    }
    this.clean=function(done){
        $.post(propfolderryque+"ryq_clean.php", {},
            function(d){
                if(done!=missing){done()}
            }
        )
        .fail(
            function(){
                ryqueFail("clean");
                setTimeout(function(){propobj.clean(done)}, 100);
            }
        );
    }
}
function ryque_requests(){
    if(!_ryquebusy){
        for(var i in _ryquerequests){
            _ryquebusy=true;
            var f=_ryquerequests[i];
            delete _ryquerequests[i];
            f(
                function(){
                    if(_ryquerequests.length>0){
                        _ryquebusy=false;
                        setTimeout(function(){ryque_requests()});
                    }
                }
            );
            break;
        }
    }
}
function ryqueFail(nomefunct){
    if(window.console&&_sessioninfo.debugmode){console.log("Fallita "+nomefunct+": verr"+_utf8("a")+" effettuato un nuovo tentativo...")}
}
function ryqueUnready(status){
    if(window.console&&_sessioninfo.debugmode){console.log("Grid "+status+": verr"+_utf8("a")+" effettuato un nuovo tentativo...")}
}
var RYQUE=new ryQue();
