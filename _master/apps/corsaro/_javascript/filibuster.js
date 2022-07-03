/****************************************************************************
* Name:            filibuster.js                                            *
* Project:         Corsaro                                                  *
* Module:          Filibuster                                               *
* Version:         1.70                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var _containers={};
var _loading={};
var _scripting={};
var _countreq=0;
var flaghierarchy=false;
var _flagstats=false;
// GESTIONE VOICE
var _currVoice=false;
$.browser.chrome=(navigator.userAgent.match(/Chrom(e|ium)/i)!==null);
// OGGETTO PUBBLICO
var FLB={};
FLB.actualid=_actualid;
FLB.supports={};
FLB.supports.svg=true;
FLB.supports.unicode=true;
FLB.metrics={};
FLB.metrics.width=$(window).width()-8;
FLB.metrics.density=96;
FLB.detected={};
FLB.detected.mobile=(navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Mini|Mobile/i)!==null);
FLB.containers=function(id){
    if(_containers[id])
        return _containers[id];
    else
        return {};
}
FLB.refresh=function(){
    containers_locate();
}
FLB.gallery=function(options){
    flb_gallery(options);
}
FLB.dropdown=function(options){
    flb_dropdown(options);
}
// GESTIONE FORUM
FLB.pause=function(millis){
    var date=new Date();
    var curDate=null;
    do{curDate=new Date();}
    while(curDate-date<millis);
};
FLB.gotoPage=function(pageid){
    var h=location.href;
    h=h.replace(/site=[^&]+/, "site="+_site);
    h=h.replace(/id=[^&]+/, "id="+pageid);
    location.href=h;
};
FLB.forum={
    formid:"", 
    userid:"", 
    username:"",
    postid:"",
    parentid:"",
    action:"",
    header:false,
    putInfo:function(info, missing){
        if(info.formid!=missing){FLB.forum.formid=info.formid}
        if(info.userid!=missing){FLB.forum.userid=info.userid}
        if(info.username!=missing){FLB.forum.username=info.username}
    },
    getInfo:function(obj){
        var ret={};
        ret.frame=$(obj).parents(".filibuster-forum");
        ret.post=$(obj).parents(".filibuster-forum-post");
        ret.corsaro=ret.frame.find(".filibuster-forum-iframe")[0];
        ret.iframe=$(ret.corsaro).find("iframe")[0];
        ret.postid="";
        ret.parentid="";
        if(ret.post.length>0){
            // SYSID DEL POST E DEL PARENT
            ret.postid=ret.post.attr("_sysid");
            ret.parentid=ret.post.attr("_parentid");
            // TOP RELATIVO DEL POST
            ret.relTop=ret.post.position().top;
            // LEFT ASSOLUTO DEL POST
            ret.absLeft=ret.post.offset().left;
            // TOP ASSOLUTO DEL POST
            ret.absTop=ret.post.offset().top;
            // MARGINE TOP DEL POST
            ret.margTop=parseInt($(ret.post).css("margin-top"));
            // DATI TOOLS
            ret.tools=$(ret.post).find(".filibuster-forum-tools")[0];
            ret.toolsTop=$(ret.tools).position().top; 
            ret.toolsHeight=$(ret.tools).height();
            // TOP E BOTTOM ASSOLUTI DEI MARGINI VISIBILI DELLA FINESTRA
            ret.scrollTop=$(window).scrollTop();
            ret.scrollBottom=ret.scrollTop+$(window).height();
            // DATI FINESTRA IMMERSA
            ret.corsaroWidth=$(ret.corsaro).width();
            ret.corsaroHeight=$(ret.corsaro).height();
            // LEFT OTTIMA
            ret.fitLeft=ret.absLeft;
            if(ret.fitLeft>ret.corsaroWidth)
                ret.fitLeft=ret.corsaroWidth-50;
            else
                ret.fitLeft-=50;
            ret.fitLeft=-ret.fitLeft;
            // TOP OTTIMO
            ret.fitTop=ret.relTop+ret.toolsTop+ret.toolsHeight+ret.margTop+5;
            if(ret.fitTop<ret.scrollTop-ret.absTop+30){ // controllo se il margine superiore del post � nascosto per via dello scroll
                ret.fitTop=ret.scrollTop-ret.absTop+30;
            }
            if(ret.absTop-ret.relTop+ret.fitTop+ret.corsaroHeight>ret.scrollBottom){
                ret.fitTop=ret.scrollBottom-ret.absTop+ret.relTop-ret.corsaroHeight
            }
            if(ret.fitTop<ret.scrollTop+30){
                ret.fitTop=ret.scrollTop+30;
            }
        }
        return ret;
    },
    showLogout:function(){
        $(FLB.forum.header).find("span").html("Welcome <b>"+FLB.forum.username+"</b>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;");
        $(FLB.forum.header).find("span,a").hide();
        $(FLB.forum.header).find("span,.filibuster-forum-logout").show();
        $(".filibuster-forum a[_userid="+FLB.forum.userid+"]").each(
            function(index){
                $(this).removeClass("filibuster-forum-disabled");
            }
        );
    },
    showLogin:function(){
        $(FLB.forum.header).find("span").html("");
        $(FLB.forum.header).find("span,a").hide();
        $(FLB.forum.header).find("span,.filibuster-forum-login").show();
        $(".filibuster-forum a[_userid="+FLB.forum.userid+"]").each(
            function(index){
                $(this).addClass("filibuster-forum-disabled");
            }
        );
    }
};
function flb_initialize(){
    supportsSVG();
    supportsUnicode();
    detectResolution();
    solvehierarchy();
    containers_locate();
    setTimeout(function(){
        solvecontent();
    }, 100);
}
function flb_finalize(){
    $("#filibuster-food4bot").remove();
    var scripting="";
    for(var id in _scripting){
        if(flb_isset(_scripting[id])){
            scripting=_scripting[id];
            delete _scripting[id];
        }
        if(scripting!=""){
            try{eval(scripting)}catch(e){if(window.console){console.log(scripting)}}
        }
    }
    containers_locate();
    setTimeout(
        function(){
            var p=location.href.indexOf("#");
            if(p>0){
                try{
                    if($.browser.chrome || $.browser.safari)
                        $(document.body).scrollTop($(location.href.substr(p)).offset().top);
                    else
                        location.hash=location.href.substr(p);
                }catch(e){}
            }
            flb_statistics();
        }, 1000
    );
}
function flb_isset(v){
    return (typeof v!=="undefined" && v!==null);
}
function containers_locate(options, missing){
    if(!flaghierarchy){
        return false;
    }
    var currparent="";
    var lastparents={};
    var parentheights={};
    var narrowmode;
    if(sheet_width_narrow<10)
        sheet_width_narrow=FLB.metrics.width;
    if(FLB.detected.mobile){
        sheet_width=sheet_width_narrow;
        narrowmode=true;
    }
    else if(FLB.metrics.width<sheet_width_orig){
        sheet_width=sheet_width_narrow;
        narrowmode=true;
    }
    else{
        sheet_width=sheet_width_orig;
        narrowmode=false;
    }
    var sheet_left=Math.round((FLB.metrics.width-sheet_width)/2, 0);
    for(var id in _containers){
        var styouter={};
        var styborder={};
        var styinner={};
        var styback=[];
        var flb_padding=5;
        var flb_topafter=false;
        var flb_topaftervalue="";
        var flb_leftafter=false;
        var flb_leftaftervalue="";
        var flb_width=0;
        var flb_height=0;
        var flb_gradient_x="";
        var flb_gradient_y="";
        var flb_radius=false;
        var flb_thick=1;
        var flb_bordercolor=false
        var flb_backcolor="";
        var flb_backimage="";
        var flb_background="";
        var std_background="";
        var flb_shadow=false;
        currparent=_containers[id]["flb-parent"];
        styouter["top"]=0;
        styouter["left"]=0;
        styinner["overflow"]="visible";
        if(FLB.detected.mobile){
            $("body").css({"font-size":"110%"});
            $(".filibuster-skip").css({"font-size":"100%"});
            $(".filibuster-item-icon img").css({"height":"6em"});
            $(".filibuster-item-icon").css({"text-align":"center"});
        }
        for(var attr in _containers[id]){
            var v=_containers[id][attr];
            switch(attr.toLowerCase()){
            case "flb-align":
                if(v=="[!RIGHT]"){
                    styinner["text-align"]="right"
                }
                break;
            case "flb-backcolor":
                flb_backcolor=v;
                break;
            case "flb-background":
                flb_background=v;
                break;
            case "flb-backimage":
                flb_backimage=v;
                break;
            case "flb-bordercolor":
                flb_bordercolor=v;
                break;
            case "flb-font":
                if(flb_isset(styinner["font-family"]))
                    styinner["font-family"]+=","+v.replace(/[+]/g, " ");
                else
                    styinner["font-family"]=v.replace(/[+]/g, " ");
                break;
            case "flb-embedfont":
                var f=v.replace(/ /g, "+");
                var h="_fonts/"+f+"/"+f+".css";
                if($("link[href='"+h+"']").length==0){
                    $("head").append("<link href='"+h+"' rel='stylesheet' type='text/css'>");
                }
                if(flb_isset(styinner["font-family"]))
                    styinner["font-family"]+=","+v.replace(/[+]/g, " ");
                else
                    styinner["font-family"]=v.replace(/[+]/g, " ");
                break;
            case "flb-googlefont":
                var h="http://fonts.googleapis.com/css?family="+v.replace(/ /g, "+");
                if($("link[href='"+h+"']").length==0){
                    $("head").append("<link href='"+h+"' rel='stylesheet' type='text/css'>");
                }
                if(flb_isset(styinner["font-family"]))
                    styinner["font-family"]+=","+v.replace(/[+]/g, " ");
                else
                    styinner["font-family"]=v.replace(/[+]/g, " ");
                break;
            case "flb-gradient-x":
                flb_gradient_x=v;
                break;
            case "flb-gradient-y":
                flb_gradient_y=v;
                break;
            case "flb-height":
                flb_height=parseInt(v);
                styinner["overflow"]="hidden";
                break;
            case "flb-left":
                var la=null;
                var sl=null;
                if(typeof(v)=="string"){
                    la=v.match(/\[!LEFTAFTER\((.+)\)\]/);
                    sl=v.match(/\[!SHEETLEFT\]/);
                }
                if(la){
                    if(_containers[la[1]]){
                        flb_leftafter=la[1];
                        flb_leftaftervalue=v;
                    }
                    else{
                        if(window.console){console.log("Container "+la[1]+" doesn't exist!")}
                    }
                }
                else if(sl){
                    var formula=v.replace(/\[!SHEETLEFT\]/, sheet_left);
                    styouter["left"]=eval(formula);
                }
                else{
                    styouter["left"]=parseInt(v);
                }
                break;
            case "flb-opacity":
                styouter["opacity"]=v;
                styborder["opacity"]=v;
                styinner["opacity"]=v;
                break;
            case "flb-overflow-x":
                styinner["overflow-x"]=v;
                break;
            case "flb-overflow-y":
                styinner["overflow-y"]=v;
                break;
            case "flb-overflow":
                styinner["overflow"]=v;
                break;
            case "flb-padding":
                flb_padding=v;
                break;
            case "flb-padding-top":
                styinner["padding-top"]=v;
                break;
            case "flb-padding-right":
                styinner["padding-right"]=v;
                break;
            case "flb-padding-bottom":
                styinner["padding-bottom"]=v;
                break;
            case "flb-padding-left":
                styinner["padding-left"]=v;
                break;
            case "flb-parent":
                break;
            case "flb-radius":
                flb_radius=v;
                break;
            case "flb-shadow":
                // ALTERNATIVA ELEGANTE DI box-shadow:1px 1px 3px 0px rgba(0, 0, 0, 0.2);
                flb_shadow=v;
                break;
            case "flb-thick":
                flb_thick=v;
                break;
            case "flb-top":
                var ta=null;
                if(typeof(v)=="string"){
                    ta=v.match(/\[!TOPAFTER\((.+)\)\]/);
                }
                if(ta){
                    if(_containers[ta[1]]){
                        flb_topafter=ta[1];
                        flb_topaftervalue=v;
                    }
                    else{
                        if(window.console){console.log("Container "+ta[1]+" doesn't exist!")}
                    }
                }
                else{
                    styouter["top"]=parseInt(v);
                }
                break;
            case "flb-width":
                if(typeof(v)=="string"){
                    if(v.match(/\[!SHEETWIDTH\]/)){
                        var w=sheet_width;
                        var formula=v.replace(/\[!SHEETWIDTH\]/, w);
                        flb_width=eval(formula);
                    }
                    else if(v.match(/\[!PARENTWIDTH\]/)){
                        var w=$("#"+id).parent().width();
                        var formula=v.replace(/\[!PARENTWIDTH\]/, w);
                        flb_width=eval(formula);
                    }
                    else if(v.match(/\[!PAGEWIDTH\]/)){
                        var w=FLB.metrics.width;
                        var formula=v.replace(/\[!PAGEWIDTH\]/, w);
                        flb_width=eval(formula);
                    }
                    else{
                        flb_width=parseInt(v);
                    }
                }
                else{
                    flb_width=v;
                }
                break;
            case "background":
                std_background=v;
                break;
            case "height":
                flb_height=v;
                styinner["overflow"]="hidden";
                break;
            case "width":
                flb_width=v;
                break;
            default:
                styouter[attr]=v;
            }
        }
        if(narrowmode){
            if(flb_isset(lastparents[currparent])){
                flb_topafter=lastparents[currparent];
                flb_topaftervalue="[!TOPAFTER("+lastparents[currparent]+")]+30";
            }
            else{
                styouter["top"]=0;
            }
            flb_leftafter=false;
            flb_leftaftervalue="";
            //if(sheet_width>1)
                //flb_width=sheet_width-4;
            //else
                //flb_width="99%";
           
            flb_width=sheet_width-4;
            styouter["left"]=2;
        }
        if(FLB.detected.mobile){
            flb_radius=false;
            flb_padding=3;
            flb_thick=0;
            flb_height=0;
        }
        // SPESSORE TOTALE
        if(flb_radius!==false){
            flb_padding+=flb_radius;
        }
        var spessore=flb_thick+flb_padding;
        
        if(typeof(flb_width)=="string"){
            if(flb_width.indexOf("%")!==false){
                $("#"+id).width(flb_width);
                flb_width=$("#"+id).width();
            }
        }
        if(flb_width>0){
            styouter["width"]=flb_width;
            styborder["width"]=flb_width-2*flb_thick;
            styinner["width"]=flb_width-2*spessore;
            // PREASSEGNO LA LARGHEZZA PER AVERE UNA ALTEZZA CORRETTA
            $("#"+id+"_inner").width(flb_width-2*spessore);
        }
        
        if(typeof(flb_height)=="string"){
            if(flb_height.indexOf("%")!==false){
                $("#"+id).height(flb_height);
                flb_height=$("#"+id).height();
            }
        }
        if(flb_height>0){
            styouter["height"]=flb_height;
            styborder["height"]=flb_height-2*flb_thick;
            styinner["height"]=flb_height-2*spessore;
        }
        else{
            var h=$("#"+id+"_inner").height();
            styouter["height"]=h+2*spessore;
            styborder["height"]=h+2*flb_padding;
        }
        
        // COLORI
        if(FLB.detected.mobile){
            styouter["background"]="white";
            styborder["background"]="transparent";
            styinner["background"]="transparent";
        }
        else{
            if(flb_gradient_x!=""){
                var g=flb_gradient_x.split("-");
                styouter["background-color"]=g[0];
                styback[0]="-webkit-gradient(linear, top left, top right, from("+g[0]+"), to("+g[1]+"))";
                styback[1]="-webkit-linear-gradient(left, "+g[0]+", "+g[1]+")";
                styback[2]="-moz-linear-gradient(left, "+g[0]+", "+g[1]+")";
                styback[3]="-o-linear-gradient(left, "+g[0]+", "+g[1]+")";
                styback[4]="linear-gradient(to right, "+g[0]+", "+g[1]+")";
                styouter["background"]="-ms-linear-gradient(left, "+g[0]+" 0%, "+g[1]+" 100%)";
                styouter["filter"]="progid:DXImageTransform.Microsoft.gradient( startColorstr='"+g[0]+"', endColorstr='"+g[1]+"',GradientType=1 )";
                styborder["background"]="transparent";
                styinner["background"]="transparent";
            }
            else if(flb_gradient_y!=""){
                var g=flb_gradient_y.split("-");
                styouter["background-color"]=g[0];
                styback[0]="-webkit-gradient(linear, left top, left bottom, from("+g[0]+"), to("+g[1]+"))";
                styback[1]="-webkit-linear-gradient(top, "+g[0]+", "+g[1]+")";
                styback[2]="-moz-linear-gradient(top, "+g[0]+", "+g[1]+")";
                styback[3]="-o-linear-gradient(top, "+g[0]+", "+g[1]+")";
                styback[4]="linear-gradient(to bottom, "+g[0]+", "+g[1]+")";
                styouter["background"]="-ms-linear-gradient(top, "+g[0]+" 0%, "+g[1]+" 100%)";
                styouter["filter"]="progid:DXImageTransform.Microsoft.gradient( startColorstr='"+g[0]+"', endColorstr='"+g[1]+"',GradientType=0 )";
                styborder["background"]="transparent";
                styinner["background"]="transparent";
            }
            else{
                // BACKCOLOR
                if(flb_backcolor!=""){
                    styouter["background"]=flb_backcolor;
                    styborder["background"]=flb_backcolor;
                    styinner["background"]=flb_backcolor;
                }
                // BACKGROUND STANDARD
                if(std_background!=""){
                    styouter["background"]=std_background;
                }
                // BORDERCOLOR E BACKIMAGE
                if(flb_bordercolor!==false){
                    styouter["background"]=flb_bordercolor;
                    if(flb_backimage){
                        styborder["background"]="url(_images/txt-"+flb_backimage+".png) repeat scroll 0% 0% "+flb_backcolor;
                        styinner["background"]="transparent";
                    }
                }
                else{ 
                    if(flb_backimage){
                        styouter["background"]="url(_images/txt-"+flb_backimage+".png) repeat scroll 0% 0% "+flb_backcolor;
                        styborder["background"]="transparent";
                        styinner["background"]="transparent";
                    }
                }
            }
        }

        // ARROTONDAMENTO
        if(flb_radius!==false){
            styouter["-moz-border-radius"]=flb_radius;
            styouter["-webkit-border-radius"]=flb_radius;
            styouter["-khtml-border-radius"]=flb_radius;
            styouter["border-radius"]=flb_radius;
            styborder["-moz-border-radius"]=flb_radius-flb_thick;
            styborder["-webkit-border-radius"]=flb_radius-flb_thick;
            styborder["-khtml-border-radius"]=flb_radius-flb_thick;
            styborder["border-radius"]=flb_radius-flb_thick;
        }
        
        // LEFT
        if(flb_leftafter!==false){
            var x=$("#"+flb_leftafter).position().left+$("#"+flb_leftafter+"_inner").width()+2*$("#"+flb_leftafter+"_border").position().left+2*$("#"+flb_leftafter+"_inner").position().left;
            var formula=flb_leftaftervalue.replace(/\[!LEFTAFTER\((.+)\)\]/, x)
            styouter["left"]=eval(formula);
        }
        styborder["left"]=flb_thick;
        styinner["left"]=flb_padding;
        
        // TOP
        if(flb_topafter!==false){
            var y=$("#"+flb_topafter).position().top+$("#"+flb_topafter+"_inner").height()+2*$("#"+flb_topafter+"_border").position().top+2*$("#"+flb_topafter+"_inner").position().top;
            var formula=flb_topaftervalue.replace(/\[!TOPAFTER\((.+)\)\]/, y)
            styouter["top"]=eval(formula);
        }
        styborder["top"]=flb_thick;
        styinner["top"]=flb_padding;
        
        if(flb_shadow!==false){
            if(narrowmode){
                $("#"+id+"_svg").html("");
            }
            else if(FLB.supports.svg && !$.browser.safari){
                var t="";
                var rad=0;
                if(flb_radius!==false){
                    rad=flb_radius;
                }
                var rect_l=styouter["left"]+rad;
                var rect_t=styouter["top"]+rad;
                var rect_w=styborder["width"]-rad-1;
                var rect_h=styborder["height"]-rad-1;
                var dev=Math.round(flb_shadow*0.4)+1;
                t+="<svg versione='1.1' class='filibuster-svg' width='"+(rect_w+6*flb_shadow)+"' height='"+(rect_h+6*flb_shadow)+"'>";
                t+="<defs>";
                t+="<filter id='"+id+"_filter' x='0' y='0' width='200%' height='200%'>";
                t+="<feOffset result='offOut' in='SourceAlpha' dx='"+flb_shadow+"' dy='"+flb_shadow+"' />";
                t+="<feGaussianBlur result='blurOut' in='offOut' stdDeviation='"+dev+"' />";
                t+="<feBlend in='SourceGraphic' in2='blurOut' mode='normal' />";
                t+="</filter>";
                t+="</defs>";
                t+="<rect width='"+rect_w+"' height='"+rect_h+"' stroke='transparent' stroke-width='0' fill='silver' filter='url(#"+id+"_filter)' />";
                t+="</svg>";
                $("#"+id+"_svg").html(t);
                $("#"+id+"_svg").css({"left":rect_l,"top":rect_t,"width":rect_w+6*flb_shadow, "height":rect_h+6*flb_shadow});
            }
            else{
                styouter["box-shadow"]=flb_shadow+"px "+flb_shadow+"px "+flb_shadow+"px 0px rgba(0, 0, 0, 0.4)";
            }
        }
        // AGGIUSTAMENTO HEIGHT A CAUSA DEI FRAME CONTENUTI
        if(currparent!=""){
            var test_h=styouter["top"]+styouter["height"];
            if(!flb_isset(parentheights[currparent])){
                parentheights[currparent]=test_h;
            }
            else{
                if(parentheights[currparent]<test_h){
                    parentheights[currparent]=test_h;
                }
            }
        }
        if(flb_isset(parentheights[id])){
            if(styouter["height"]<parentheights[id]){
                styouter["height"]=parentheights[id];
                styborder["height"]=styouter["height"]-2*flb_thick;
                styinner["height"]=styouter["height"]-2*spessore;
            }
        }
        
        // ASSEGNO LE PROPRIETA' DI OUTER
        $("#"+id).css(styouter);
        for(var b in styback){
            $("#"+id).css("background-image", styback[b]);
        }
        
        // ASSEGNO LE PROPRIETA' DI BORDER
        $("#"+id+"_border").css(styborder);
        
        // ASSEGNO LE PROPRIETA' DI INNER
        $("#"+id+"_inner").css(styinner);
        
        // MEMORIZZO L'ULTIMO CONTAINER PER MODALITA' STRETTA
        lastparents[currparent]=id;
    }
    if(options!=missing){
        if(options.mathjax!=missing){
            if(options.mathjax){
                try{MathJax.Hub.Queue(["Typeset", MathJax.Hub])}catch(e){}
            }
        }
    }
}
function create_container(id, parent, contentid, classes, style, scrpt){
    style["flb-parent"]=parent;
    _containers[id]=style;
    if(parent!=""){
        $("#"+parent).append("<div class='filibuster-div filibuster-divsvg' id='"+id+"_svg'></div><div class='filibuster-div filibuster-frame "+classes+"' id='"+id+"'><div class='filibuster-div' id='"+id+"_border'><div class='filibuster-div' id='"+id+"_inner'></div></div></div>");
    }
    if(contentid!=""){
        $("#"+id+"_inner").html("<img src='_images/loading.gif' border='0'/>");
        _loading[id]=contentid;
        if(scrpt!=""){
            _scripting[id]=scrpt;
        }
    }
    else{
        if(scrpt!=""){
            try{eval(scrpt)}catch(e){}
        }
    }
}
async function solvecontent(){
    var v=[];
    for(var id in _loading){
        v.push(flb_fetch(id));
    }
    _countreq=objectcount(_loading);
    await Promise.all(v);
}
function flb_fetch(id){
    var contentid=_loading[id];
    var w=$("#"+id+"_inner").width();
    $.ajax({
        type:_ajaxmethod,
        url:_requestContainer,
        data:{
            "host":_gethost,
            "env":_environ,
            "site":_site,
            "id":contentid,
            "pageid":_pageid,
            "width":w
        },
    })
    .done(function(d){
        try{
            var typefood=d.substr(0,3);
            var buff=d.substr(3);
            delete _loading[id];
            if(typefood=="[C]"){
                $("#"+id+"_inner").html(buff);
                // RISOLUZIONE LINK INTERNI
                $("#"+id+"_inner a").each(
                    function(index){
                        var href=$(this).attr("href");
                        if(flb_isset(href)){
                            if(href.length==_lenid){
                                if(href.substr!="h"){
                                    $(this).attr("href", "filibuster.php?env="+_environ+"&site="+_site+"&id="+href);
                                }
                            }
                        }
                    }
                );
                // AGGIUSTAMENTO OGGETTI A TUTTA LARGHEZZA
                $("#"+id+"_inner .filibuster-resizable").each(
                    function(index){
                        try{
                            var old_w=$(this).width();
                            var old_h=$(this).height();
                            var ratio=old_h/old_w;
                            var new_w=Math.round($("#"+id+"_inner").width());
                            $(this).width(new_w);
                            $(this).height(Math.round(new_w*ratio));
                        }catch(e){
                            if(window.console){console.log(buff)}
                        }
                    }
                );
                // AGGIUSTAMENTO IFRAME A TUTTA LUNGHEZZA
                $("#"+id+"_inner .filibuster-fittable").each(
                    function(index){
                        try{
                            var objframe=$(this);
                            objframe.load(
                                function(){
                                    try{
                                        objframe.height(objframe.contents().height()+20);
                                    }catch(e){}
                                }
                            );
                        }catch(e){
                            if(window.console){console.log(buff)}
                        }
                    }
                );
                // AGGIUSTAMENTO EMBED A TUTTA LARGHEZZA
                $("#"+id+"_inner .filibuster-stretchable").each(
                    function(index){
                        try{
                            var new_w=Math.round($("#"+id+"_inner").width());
                            $(this).width(new_w);
                        }catch(e){
                            if(window.console){console.log(buff)}
                        }
                    }
                );
                // ATTIVAZIONE MARQUEE
                $("#"+id+"_inner .filibuster-marquee").each(
                    function(index){
                        new objMarqee(this);
                    }
                );
                // ATTIVAZIONE RICERCA
                $("#"+id+"_inner .filibuster-search").each(
                    function(index){
                        if(!FLB.supports.unicode){
                            $("#"+id+"_inner .filibuster-search-button a").html("<img class='filibuster-surrogate' src='_images/search.png' border='0'/>");
                            $("#"+id+"_inner .filibuster-voice-button a").html("<img class='filibuster-surrogate' src='_images/voice.png' border='0'/>");
                            $("#"+id+"_inner .filibuster-print-button a").html("<img class='filibuster-surrogate' src='_images/print.png' border='0'/>");
                        }
                        this.contentid=contentid;
                        new objSearch(this);
                    }
                );
                // ATTIVAZIONE VOCE
                if(_voicelang!=""){
                    $("#"+id+"_inner .filibuster-voice").each(
                        function(index){
                            _currVoice=new objVoice(this);
                        }
                    );
                }
                else{
                    $("#"+id+"_inner .filibuster-voice").css("display","none");
                }
                // ATTIVAZIONE STAMPA
                $("#"+id+"_inner .filibuster-print").each(
                    function(index){
                        new objPrint(this);
                    }
                );
                // ATTIVAZIONE MAIL
                $("#"+id+"_inner .filibuster-mailus").each(
                    function(index){
                        this.contentid=contentid;
                        new objMailus(this);
                    }
                );
                // ATTIVAZIONE SOCIAL
                $("#"+id+"_inner .filibuster-social").each(
                    function(index){
                        $(this).share({
                          networks:[
                            'facebook', 
                            'twitter',
                            'googleplus', 
                            'pinterest', 
                            'linkedin',
                            'tumblr'
                          ]
                        });                            
                    }
                );
                // LOGOUT FORUM
                $("#"+id+"_inner .filibuster-forum").each(
                    function(index){
                        FLB.forum.header=$(this).find(".filibuster-forum-header")[0];
                        $("#"+id+"_inner .filibuster-forum-iframe").draggable({
                            "start":function(){
                                $("#"+id+"_inner .filibuster-forum-iframe").css({"background":"silver"});
                                $("#"+id+"_inner .filibuster-forum-iframe iframe").css({"visibility":"hidden"});
                            },
                            "stop":function(){
                                $("#"+id+"_inner .filibuster-forum-iframe").css({"background":"#315B7E"});
                                $("#"+id+"_inner .filibuster-forum-iframe iframe").css({"visibility":"visible"});
                            }
                        });
                    }
                );
                // ATTIVAZIONE NAVIGAZIONE
                $("#"+id+"_inner .filibuster-navigator-tool").each(
                    function(index){
                        $("body").keydown(
                            function(k){
                                if(k.ctrlKey){
                                    try{
                                        var href;
                                        switch(k.which){ // left
                                        case 37:    // left
                                            flb_navigator_back();
                                            break;
                                        case 39:    // right
                                            flb_navigator_forward();
                                            break;
                                        case 36:    // first
                                            href=$(".filibuster-navigator-first").attr("href");
                                            if(flb_isset(href))
                                                window.location.href=href;
                                            break;
                                        case 35:    // last
                                            href=$(".filibuster-navigator-last").attr("href");
                                            if(flb_isset(href))
                                                window.location.href=href;
                                            break;
                                        case 60:    // <
                                            // Gestione Voice
                                            if(_currVoice!==false){_currVoice.click()}
                                            break;
                                        }
                                    }catch(e){}
                                }
                            }
                        );
                        if(!FLB.supports.unicode){
                            $("#"+id+"_inner .filibuster-navigator-back").html("&nbsp;<img class='filibuster-surrogate' src='_images/left.png' border='0'/>&nbsp;");
                            $("#"+id+"_inner .filibuster-navigator-forward").html("&nbsp;<img class='filibuster-surrogate' src='_images/right.png' border='0'/>&nbsp;");
                        }
                        if(FLB.detected.mobile){
                            if($(".filibuster-changepage").length==0){
                                $("body").append("<div class='filibuster-changepage filibuster-prevpage' style='left:-1.5em' onclick='flb_navigator_back()'>&nbsp;</div>");
                                $("body").append("<div class='filibuster-changepage filibuster-nextpage' style='right:-1.5em' onclick='flb_navigator_forward()'>&nbsp;</div>");
                            }
                        }
                    }
                );
                if(FLB.detected.mobile){
                    // FITTING IMMAGINI
                    $("#"+id+"_inner img").each(
                        function(index){
                            try{
                                var old_w=$(this).width();
                                if(old_w>FLB.metrics.width){
                                    var old_h=$(this).height();
                                    var ratio=old_h/old_w;
                                    var new_w=Math.round($("#"+id+"_inner").width()*0.96);
                                    $(this).width(new_w);
                                    $(this).height(Math.round(new_w*ratio));
                                }
                            }catch(e){
                                if(window.console){console.log(buff)}
                            }
                        }
                    );
                }
                var bMathjax=false;
                // CONTENUTI SPECIALI: MATEMATICA
                if($("#"+id+"_inner .filibuster-specials-math").length>0){
                    if(_mathurl!=""){
                        bMathjax=true;
                    }
                }
                containers_locate({"mathjax":bMathjax});
            }
            else{
                try{
                    // Elimino il messaggio di avanzamento
                    $("#"+id+"_inner").html("");
                    // Interpreto il documento JSON e lo scandisco
                    v=$.parseJSON(buff);
                    for(var i in v){
                        var sty=v[i]["style"];
                        sty=sty.replace(/&quot;/g, "\"");
                        try{
                            sty=$.parseJSON(sty);
                        }catch(e){
                            if(window.console){console.log(sty)}
                            sty={};
                        }
                        var scrpt=v[i]["script"];
                        scrpt=scrpt.replace(/&quot;/g, "\"");
                        create_container(v[i]["containerid"], id, v[i]["contentid"], v[i]["classes"], sty, scrpt);
                    }
                    solvehierarchy();
                }catch(e){
                    if(window.console){console.log(buff)}
                }
            }
            _countreq-=1;
            if(objectcount(_loading)>0){
                if(_countreq==0){
                    setTimeout(function(){
                        solvecontent();
                    }, 200);
                }
            }
            else{
                setTimeout(function(){
                    flb_finalize();
                }, 200);
            }
        }
        catch(e){
            if(window.console){console.log(buff)}
        }
    });
}
function solvehierarchy(){
    var _hierarchy={};
    var rel=[];
    var rels=0;
    for(var id in _containers){
        var sty=_containers[id];
        var parentid=sty["flb-parent"];
        var leftid="";
        var topid="";
        if(flb_isset(sty["flb-left"])){
            var attr=sty["flb-left"]
            if(typeof(attr)=="string"){
                var ref=attr.match(/\[!LEFTAFTER\((.+)\)\]/);
                if(ref){
                    if(_containers[ref[1]])
                        leftid=ref[1];
                    else
                        if(window.console){console.log("Container "+ref[1]+" doesn't exist!")}
                }
            }
        }
        if(flb_isset(sty["flb-top"])){
            var attr=sty["flb-top"]
            if(typeof(attr)=="string"){
                var ref=attr.match(/\[!TOPAFTER\((.+)\)\]/);
                if(ref){
                    if(_containers[ref[1]])
                        topid=ref[1];
                    else
                        if(window.console){console.log("Container "+ref[1]+" doesn't exist!")}
                }
            }
        }
        if(parentid!=""){
            rel[rels++]=[id, parentid];
        }
        if(leftid!=""){
            rel[rels++]=[leftid, id];
        }
        if(topid!=""){
            rel[rels++]=[topid, id];
        }
    }
    do{
        for(var id in _containers){
            if(!flb_isset(_hierarchy[id])){
                var e=false;
                for(i in rel){
                    if(rel[i][1]==id){
                        e=true;
                        break;
                    }
                }
                if(!e){
                    _hierarchy[id]=_containers[id];
                    for(i in rel){
                        if(rel[i][0]==id){
                            rel[i][1]="";
                        }
                    }
                }
            }
        }
    }while(objectcount(_hierarchy)<objectcount(_containers)-1);
    _hierarchy[_filibusterbody]=_containers[_filibusterbody];
    delete _containers;
    _containers=_hierarchy;
    flaghierarchy=true;
}
function objectcount(o){
    //try{
    //    return Object.keys(o).length;
    //}
    //catch(e){
        var c=0;
        for(i in o){
            c+=1;
        }
        return c;
    //}
}
function objMarqee(obj){
    var refid=$(obj).attr("id").substr(8);
    var h=$("#MARQUEE1_"+refid).height();
    obj.prevtime=(new Date()).getTime();
    obj.slowness=2500;
    obj.base=1;
    obj.flagmarq=true;
    obj.subheight=h+30;
    obj.refid=refid;
    if(FLB.detected.mobile){
        $(obj).height(obj.subheight);
        $("#MARQUEE1_"+refid).css({"position":"absolute","top":obj.base});
        $("#MARQUEE2_"+refid).remove();
    }
    else{
        $(obj).height(2*obj.subheight);
        $(obj).mouseover(
            function(){
                obj.flagmarq=false;
            }
        );
        $(obj).mouseout(
            function(){
                obj.flagmarq=true;
            }
        );
        setInterval(
            function(){
                var milly=(new Date()).getTime();
                if(milly-obj.prevtime>obj.slowness){
                    obj.prevtime=milly;
                    if(obj.flagmarq){
                        obj.base=obj.base-1;
                        if(obj.base+obj.subheight<0){
                            obj.base=0;
                            if(obj.fase==0)
                                obj.fase=1;
                            else
                                obj.fase=0;
                        }
                        if(obj.fase==0){
                            $("#MARQUEE1_"+obj.refid).css({"position":"absolute","top":obj.base});
                            $("#MARQUEE2_"+obj.refid).css({"position":"absolute","top":obj.base+obj.subheight});
                        }else{
                            $("#MARQUEE2_"+obj.refid).css({"position":"absolute","top":obj.base});
                            $("#MARQUEE1_"+obj.refid).css({"position":"absolute","top":obj.base+obj.subheight});
                        }
                    }
                    if(obj.slowness>10){
                        switch(obj.slowness){
                            case 2500: obj.slowness=60; break;
                            case 60: obj.slowness=40; break;
                            case 40: obj.slowness=30; break;
                            case 30: obj.slowness=25; break;
                            case 25: obj.slowness=20; break;
                            case 20: obj.slowness=15; break;
                            case 15: obj.slowness=10; break;
                        }
                    }
                }
            }, 10
        );
    }
}
function objSearch(obj){
    var refid=$(obj).attr("id");
    var contentid=obj.contentid;
    $("#"+refid+" input").keydown(
        function(k){
            if(k.which==13){
                flb_search(contentid, $(this).val());
            }
        }
    );
    $("#"+refid+" input").dblclick(
        function(){
            flb_search(contentid, $("#"+refid+" input").val());
        }
    );
    $("#"+refid+" div").click(
        function(){
            flb_search(contentid, $("#"+refid+" input").val());
        }
    );
}
function flb_search(toolid, t){
    if(t.length>0 && _currentpage!=""){
        var w=$("#"+_currentpage+"_inner").width();
        $("#"+_currentpage+" .filibuster-divsvg").remove();
        $("#"+_currentpage+" .filibuster-frame").remove();
        $("#"+_currentpage+"_inner").html("<img src='_images/loading.gif' border='0'/>");
        // GESTIONE CONTENUTO INVISIBILE
        if($("#"+_currentpage).css("display")=="none"){
            $("#"+_currentpage).css("display", "block");
        }
        for(var id in _containers){
            if($("#"+id).length==0){
                delete _containers[id];
            }
        }
        solvehierarchy();
        $.ajax({
            type:_ajaxmethod,
            url:_requestSearch, 
            data:{
                "host":_gethost,
                "env":_environ,
                "site":_site,
                "toolid":toolid,
                "pageid":_pageid,
                "width":w,
                "search":t
            }
        })
        .done(function(d){
            try{
                var inn=$("#"+_currentpage+"_inner");
                var sty=inn.attr("style");
                sty=sty.replace(/height *:[^;]*(;|$)/, "");
                inn.attr("style", sty);
                inn.html(d);
                setTimeout(
                    function(){
                        containers_locate();
                        // GESTIONE CONTENUTO INVISIBILE
                        if($("#"+_currentpage).css("display")=="none"){
                            $("#"+_currentpage).css("display", "block");
                        }
                        setTimeout(
                            function(){
                                $(window).scrollTop(0);
                            }, 1000
                        );
                    }, 1
                );
            }
            catch(e){}
        });
    }
}
function objVoice(obj){
    // Gestione Voice
    var propobj=this;
    var refid=$(obj).attr("id");
    $("#"+refid+" div").click(
        function(){
            propobj.click();
        }
    );
    this.click=function(){
        if(_voicelang!=""){
            var jbutt=$("#"+refid+" .filibuster-voice-button a");
            var prevsymbol=jbutt.html();
            jbutt.html("<img class='filibuster-surrogate' src='_images/loading.gif' border='0'/>");
            if($("#filibuster-player").length>0){
                var objplayer=$("#filibuster-player").get(0);
                if(flb_isset(objplayer.play)){
                    if(objplayer.paused)
                        objplayer.play();
                    else
                        objplayer.pause();
                }
                else{
                    alert("Audio non supportato dal browser");
                }
                setTimeout(function(){jbutt.html(prevsymbol)}, 500);
            }
            else{
                $.ajax({
                    type:_ajaxmethod,
                    url:_requestVoice, 
                    data:{
                        "host":_gethost,
                        "env":_environ,
                        "site":_site,
                        "id":_actualid
                    }
                })
                .done(function(d){
                    try{
                        v=$.parseJSON(d);
                        if(parseInt(v.success)){
                            var h="";
                            h+="<audio id='filibuster-player'>";
                            h+="<source id='filibuster-player-source' src='"+v.url+"?v="+(new Date).valueOf()+"' type='audio/mpeg' >";
                            h+="</audio>";
                            $("body").append(h);
                            var objplayer=$("#filibuster-player").get(0);
                            if(flb_isset(objplayer.play))
                                propobj.tryplay(objplayer);
                            else
                                alert("Audio not supported");
                        }
                        else{
                            if(window.console){console.log(d)}
                            alert("Service not available");
                        }
                    }catch(e){
                        if(window.console){console.log(e.message)}
                        if(window.console){console.log(d)}
                        alert("Service not available");
                    }
                    setTimeout(function(){jbutt.html(prevsymbol)}, 500);
                });
            }
        }
    }
    this.tryplay=function(p){
        setTimeout(
            function(){
                try{
                    p.play();
                }catch(e){
                    propobj.tryplay(p);
                }
            }, 1000
        );
    }
}
function objPrint(obj){
    var refid=$(obj).attr("id");
    $("#"+refid+" div").click(
        function(){
            if(_currentpage!=""){
                var htext=$("#"+_currentpage+"_inner").html();
                if(htext==""){
                    htext=$("#"+_currentpage).html();
                    // TOLGO BORDER E INNER VUOTI MA CHE OCCUPANO SPAZIO
                    htext=htext.replace(/<div .+?<\/div><\/div><div .+?<\/div>/i,"");
                }
                $("#filibuster-printing").html(htext);
                $("#filibuster-printing").printThis({importCSS: true, printContainer: false, removeInline: false});
            }
        }
    );
}
function objMailus(obj){
    var refid=$(obj).attr("id");
    var contentid=obj.contentid;
    var objmess=$("#"+refid+" .filibuster-mailus-message");
    $("#"+refid+" .filibuster-mailus-button").click(
        function(){
            var email=$("#"+refid+" input").val();
            var text=$("#"+refid+" textarea").val();
            if(email.match(/@/) && text!=""){
                objmess.html("<i>Invio in corso...</i>");
                $.ajax({
                    type:_ajaxmethod,
                    url:_requestMail, 
                    data:{
                        "host":_gethost,
                        "env":_environ,
                        "site":_site,
                        "toolid":contentid,
                        "email":email,
                        "text":text
                    }
                })
                .done(function(d){
                    try{
                        objmess.html(d.substr(3));
                        if(d.substr(0,3)=="[1]"){
                            objmess.css({"color":"green"});
                            $("#"+refid+" input").val("");
                            $("#"+refid+" textarea").val("");
                        }
                        else{
                            objmess.css({"color":"red"});
                        }
                        setTimeout(function(){objmess.html("&nbsp;");}, 3000);
                    }
                    catch(e){}
                });
            }
            else{
                objmess.html("Inserire un indirizzo email corretto e un messaggio!");
                setTimeout(function(){objmess.html("&nbsp;");}, 3000);
            }
        }
    );
}
function flb_statistics(){
    if(supportsCookies()){
        if(!$.cookie("FLBCOOKIE_"+_environ+"_"+_site)){
            $("body").append("<div id='filibuster-privacycookie'>Questo sito fa uso di cookie tecnici non finalizzati alla raccolta di dati personali. Puoi approfondire leggendo la <a href='flb_privacy.php' target='_blank'>policy sui cookie</a>. <span onclick='removePrivacyCookie()'>Ho letto</span></div>");
        }
        if(!_flagstats){
            _flagstats=true;
            var userid=$.cookie("FLBUSER");
            if(!userid){
                userid="";
            }
            // GESTIONE BROWSER
            var browser="unknown";
            if($.browser.msie){browser="msie"}
            if($.browser.safari){browser="safari"}
            if($.browser.chrome){browser="chrome"}
            if($.browser.mozilla){browser="firefox"}
            if($.browser.opera){browser="opera"}
            browser+="("+FLB.metrics.density+")";
            $.ajax({
                type:_ajaxmethod,
                url:_requestStatistics, 
                data:{
                    "host":_gethost,
                    "env":_environ,
                    "site":_site,
                    "id":_pageid,
                    "user":userid,
                    "browser":browser
                }
            })
            .done(function(d){
                $.cookie("FLBUSER", d, { expires : 10000 });
            });
        }
    }
}
function removePrivacyCookie(){
    $("#filibuster-privacycookie").remove();
    $.cookie("FLBCOOKIE_"+_environ+"_"+_site, 1, { expires : 10000 });
}
function supportsSVG(){
    // Preso da Modernizr
    FLB.supports.svg=!!document.createElementNS && !!document.createElementNS('http://www.w3.org/2000/svg', "svg").createSVGRect;
}
function supportsUnicode(){
    var e=false;
    if(!$.browser.chrome){
        $("#filibuster-chartest").html("<span id='filibuster-wide'></span><span id='filibuster-narrow'></span>");
        $("#filibuster-wide").html("&#x1f50d;");
        $("#filibuster-narrow").html("&#x1f4fd;");
        e=$("#filibuster-wide").width()!=$("#filibuster-narrow").width();
        $("#filibuster-chartest").remove();
    }
    FLB.supports.unicode=e;
}
function supportsCookies(){
    $.cookie("FLBTEST", "DUMMY");
    return ($.cookie("FLBTEST")=="DUMMY");
}
function detectResolution(){
    var r=96;
    var f=1;
    r=$("#filibuster-resoltest").width();
    $("#filibuster-resoltest").remove();
    if(flb_isset(window.devicePixelRatio))
        f=window.devicePixelRatio;
    FLB.metrics.density=r/f;
}
function flb_navigator_back(){
    try{
        var href=$(".filibuster-navigator-back").attr("href");
        if(flb_isset(href)){
            window.location.href=href;
        }
        else{
            href=$(".filibuster-navigator-last").attr("href");
            if(flb_isset(href))
                window.location.href=href;
        }
    }catch(e){}
}
function flb_navigator_forward(){
    var href=$(".filibuster-navigator-forward").attr("href");
    if(flb_isset(href)){
        window.location.href=href;
    }
    else{
        href=$(".filibuster-navigator-first").attr("href");
        if(flb_isset(href))
            window.location.href=href;
    }
}
function flb_gallery(options, missing){
    if($(".filibuster-gallery").length==0)
        return;
    var spacing=0;
    var outer=0;
    var inner=0;
    var frame="black";
    var border="silver";
    var perspective=0;
    var rotateX=0;
    var rotateY=0;
    var scaleX=0;
    var scaleY=1;
    var origin="90% 10%";
    if(options.spacing!=missing){spacing=options.spacing}
    if(options.outer!=missing){outer=options.outer}
    if(options.inner!=missing){inner=options.inner}
    if(options.frame!=missing){frame=options.frame}
    if(options.border!=missing){border=options.border}
    if(options.perspective!=missing){perspective=options.perspective}
    if(options.rotateX!=missing){rotateX=options.rotateX}
    if(options.rotateY!=missing){rotateY=options.rotateY}
    if(options.scaleX!=missing){scaleX=options.scaleX}
    if(options.scaleY!=missing){scaleY=options.scaleY}
    if(options.origin!=missing){origin=options.origin}
    if(spacing>0 || outer>0 || inner>0){
        var minusx=spacing+6*inner;
        var deltax=Math.round(minusx/3);
        var residuox=minusx-3*deltax;
        $(".filibuster-image-left .filibuster-image-inner").each(
            function(index){
                var o=$(this);
                var w=o.width();
                var h=o.height();
                o.css({"left":spacing+2, "top":spacing, "width":w-spacing-deltax, "height":h-spacing-2*inner});
            }
        );
        $(".filibuster-image-center .filibuster-image-inner").each(
            function(index){
                var o=$(this);
                var w=o.width();
                var h=o.height();
                o.css({"left":spacing-deltax+2*inner+2, "top":spacing, "width":w-spacing-deltax-residuox, "height":h-spacing-2*inner});
            }
        );
        $(".filibuster-image-right .filibuster-image-inner").each(
            function(index){
                var o=$(this);
                var w=o.width();
                var h=o.height();
                o.css({"left":spacing-2*deltax-residuox+4*inner+2, "top":spacing, "width":w-spacing-deltax, "height":h-spacing-2*inner});
            }
        );
        // ADEGUO LE DIMENSIONI DI GALLERIA
        $(".filibuster-transform").each(
            function(index){
                var o=$(this);
                var w=o.width();
                var h=o.height()+spacing;
                o.css({"border":outer+"px solid "+frame, "width":w, "height":h});
            }
        );
        if(inner>0){
            $(".filibuster-gallery .filibuster-image-inner").css("border", inner+"px solid "+border);
        }
    }
    if(!FLB.detected.mobile){
        if(rotateX>0 || rotateY>0){
            if(scaleX==0){
                scaleX=Math.cos(3.14*rotateY/180);
            }
            if(perspective==0){
                perspective=1.2*$(".filibuster-gallery").width();
            }
            $(".filibuster-gallery").css(
                {
                    "-webkit-perspective":perspective+"px", "-webkit-perspective-origin":origin,
                    "-moz-perspective":perspective+"px", "-moz-perspective-origin":origin,
                    "-ms-perspective":perspective+"px", "-ms-perspective-origin":origin,
                    "-o-perspective":perspective+"px", "-o-perspective-origin":origin,
                    "perspective":perspective+"px", "perspective-origin":origin
                }
            );
            $(".filibuster-transform").css(
                {
                    "-webkit-transform":"rotateX("+rotateX+"deg) rotateY("+rotateY+"deg) scaleX("+scaleX+") scaleY("+scaleY+")",
                    "-moz-transform":"rotateX("+rotateX+"deg) rotateY("+rotateY+"deg) scaleX("+scaleX+") scaleY("+scaleY+")",
                    "-ms-transform":"rotateX("+rotateX+"deg) rotateY("+rotateY+"deg) scaleX("+scaleX+") scaleY("+scaleY+")",
                    "-o-transform":"rotateX("+rotateX+"deg) rotateY("+rotateY+"deg) scaleX("+scaleX+") scaleY("+scaleY+")",
                    "transform":"rotateX("+rotateX+"deg) rotateY("+rotateY+"deg) scaleX("+scaleX+") scaleY("+scaleY+")"
                }
            );
            // RIPOSIZIONO L'IMMAGINE DISTORTA ALL'ORIGINE
            var p=$(".filibuster-transform").position();
            var h=$(".filibuster-transform").height();
            $(".filibuster-gallery").css({"left":-p.left, "top":-p.top, "height":h-p.top+100});
        }
    }
    if(FLB.detected.mobile){
        var l=$(".filibuster-transform").position().left;
        $(".filibuster-transform").css({"left":l+2});
    }
}
function flb_dropdown(options, missing){
    if(options.root!=missing && options.tree!=missing ){
        var root=$("#"+options.root);
        var tree=options.tree;
        var width=170;
        var padding=5;
        var skip=0;
        if(options.width!=missing){width=options.width}
        if(options.padding!=missing){padding=options.padding}
        if(options.skip!=missing){skip=options.skip}
        root.addClass("navigation");
        root.html("<ul></ul>")
        _xdevelop(root, tree, 0);
        $(".navigation > ul > li > a").css({"padding": (padding+3)+"px "+(padding+12)+"px"});
        $(".navigation ul ul li a").css({"padding": padding+"px 0"});
        $(".navigation > ul > li + li").css({"margin-left":skip});
    }
    function _xdevelop(r, t, l){
        for(var i in t){
            if(typeof(t[i][1])=="object"){
                var s=r.children("ul"), u;
                //var c=objectcount(t[i][1]);
                //var uh=c*50;
                if(l==0)
                    u=$(s[0]).append("<li style='width:"+width+"px;'><a href='#'>"+t[i][0]+"</a><ul></ul></li>");
                else
                    u=$(s[0]).append("<li style='width:"+width+"px;'><ul style='left:"+width+"px;'></ul><a href='#' class='arrow'>"+t[i][0]+"</a></li>");
                var b=$(u.children("li")[i]);
                _xdevelop(b, t[i][1], 1);
            }
            else{
                var s=r.children("ul");
                var h=t[i][1];
                var d="target='_blank'";
                if(h.indexOf(":")<0){
                    h="filibuster.php?env="+_environ+"&site="+_site+"&id="+h;
                    d="";
                }
                var u=$(s[0]).append("<li style='width:"+width+"px;'><a href='"+h+"' "+d+">"+t[i][0]+"</a></li>");
            }
        }
    }
}
function flb_forumComment(obj){
    try{
        var info=FLB.forum.getInfo(obj);
        setTimeout(
            function(){
                if(flb_isset(info.iframe.contentWindow._globalforms && info.iframe.contentWindow._globalforms[FLB.forum.formid])){
                    info.iframe.contentWindow._globalforms[FLB.forum.formid]._forumInsert(info.postid);
                }
                else{
                    FLB.forum.postid=info.postid;
                    FLB.forum.action="insert";
                }
                $(info.corsaro).css({"left":info.fitLeft, "top":info.fitTop, "visibility":"visible"});
                $(info.iframe).css({"visibility":"visible"});
            },100
        );
    }
    catch(e){}
}
function flb_forumEdit(obj){
    if(!$(obj).hasClass("filibuster-forum-disabled")){
        try{
            var info=FLB.forum.getInfo(obj);
            setTimeout(
                function(){
                    if(flb_isset(info.iframe.contentWindow._globalforms)){
                        info.iframe.contentWindow._globalforms[FLB.forum.formid]._forumEdit(info.postid);
                    }
                    else{
                        FLB.forum.postid=info.postid;
                        FLB.forum.action="update";
                    }
                    $(info.corsaro).css({"left":info.fitLeft, "top":info.fitTop, "visibility":"visible"});
                    $(info.iframe).css({"visibility":"visible"});
                },100
            );
        }
        catch(e){}
    }
}
function flb_forumDelete(obj){
    if(!$(obj).hasClass("filibuster-forum-disabled")){
        try{
            var info=FLB.forum.getInfo(obj);
            setTimeout(
                function(){
                    if(confirm("Eliminare il post selezionato?")){
                        info.iframe.contentWindow._globalforms[FLB.forum.formid]._forumDelete(info.postid, info.parentid);
                    }
                },100
            );
        }
        catch(e){
            if(window.console){console.log(e.message)}
        }
    }
}
function flb_forumCancel(){
    $(".filibuster-forum-iframe").css({"visibility":"hidden"});
    $(".filibuster-forum-iframe iframe").css({"visibility":"hidden"});
}
function flb_forumLogin(obj){
    try{
        var info=FLB.forum.getInfo(obj);
        FLB.forum.action="login";
        setTimeout(
            function(){
                $(info.corsaro).css({"left":10, "top":50, "visibility":"visible"});
                $(info.iframe).css({"visibility":"visible"});
            },100
        );
    }
    catch(e){
        if(window.console){console.log(e.message)}
    }
}
function flb_forumLogout(obj){
    try{
        var info=FLB.forum.getInfo(obj);
        setTimeout(
            function(){
                try{
                    info.iframe.contentWindow._forumLogout()
                }
                catch(e){
                    if(window.console){console.log(e.message)}
                }
            },100
        );
    }
    catch(e){
        if(window.console){console.log(e.message)}
    }
}
