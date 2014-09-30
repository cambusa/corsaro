/****************************************************************************
* Name:            rydraw.js                                                *
* Project:         Cambusa/rydraw                                           *
* Version:         1.00                                                     *
* Description:     Graphic Features                                         *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
		rygram:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=0;
			var propheight=400;
            var propmargin=5;
            var propbarwidth=20;
            var propbarskip=10;
            var proptitleheight=40;
            var propcapwidth=15;
            var propstrokewidth=1;
            var propstyle="default";
            var propvalues=[100, 200, -150, 170,80];
            var propcaptions=false;
            var proptitle="";
            var propcaptionx="";
            var propcaptiony="";
            var propcaptionrate=1;
            var propmaxvalue=-999999999;
            var propminvalue=999999999;
            var propratio=1;
            var proporigy=0;
			var propobj=this;
			var propname=$(this).attr("id");
			
			if(settings.left!=missing){propleft=settings.left;}
			if(settings.top!=missing){proptop=settings.top;}
			if(settings.width!=missing){propwidth=settings.width;}
            if(settings.height!=missing){propheight=settings.height;}
            if(settings.values!=missing){propvalues=settings.values;}
            if(settings.captions!=missing){propcaptions=settings.captions;}
            if(settings.captionrate!=missing){propcaptionrate=settings.captionrate;}
            if(settings.title!=missing){proptitle=settings.title;}
            if(settings.captionx!=missing){propcaptionx=settings.captionx;}
            if(settings.captiony!=missing){propcaptiony=settings.captiony;}
            if(settings.barwidth!=missing){propbarwidth=settings.barwidth;}
            if(settings.barskip!=missing){propbarskip=settings.barskip;}
            
            // Determino minimo e massimo dei valori
            for(var i in propvalues){
                if(propmaxvalue<propvalues[i])
                    propmaxvalue=propvalues[i];
                if(propvalues[i]<propminvalue)
                    propminvalue=propvalues[i];
            }
            
            if(propcaptions===false){
                propcaptions=new Array();
                for(var i=0;i<propvalues.length;i++){
                    propcaptions[i]=(i+1);
                }
            }
            else{
                // Completo le eventuali caption mancanti
                for(var i=propcaptions.length;i<propvalues.length;i++){
                    propcaptions[i]="";
                }
            }
            
            var mincap=(propminvalue<0);

            if(propmaxvalue>0 && propminvalue>0){
                propminvalue=-propmaxvalue/20;
            }
            else if(propmaxvalue<=0 && propminvalue<=0){
                propmaxvalue=-propminvalue/20;
            }
            if(propminvalue>-0.5){
                propminvalue=-0.5;
            }
            if(propmaxvalue<0.5){
                propmaxvalue=0.5;
            }
            
            var rangey=propmaxvalue-propminvalue;
            var rangeabs=propmaxvalue;
            if(rangeabs<-propminvalue){
                rangeabs=-propminvalue;
            }

            propratio=(propheight-2*propmargin-proptitleheight-propstrokewidth)/rangey;
            proporigy=propmaxvalue*propratio;
            if(proporigy+propmargin+proptitleheight>propheight-15){
                proporigy=propheight-propmargin-proptitleheight-15;
            }
            
            var deltax=propbarwidth+propbarskip;
            
            $("#"+propname).addClass("rygram");
            $("#"+propname).css({position:"absolute",left:propleft,top:proptop,"background-color":"#F2F9FF","border":"1px solid silver"});
            
            var titw=0;
            var capxw=0;
            var capyw=0;

            if(proptitle!=""){
                // Determino la larghezza del titolo
                $("body").append("<span id='__rydraw' style='visibility:hidden;font-family:arial;font-size:18px;'>"+proptitle+"<span>");
                titw=$("#__rydraw").width();
                $("#__rydraw").remove();
            }
            
            if(propcaptionx!=""){
                // Determino la larghezza della caption orizzontale
                $("body").append("<span id='__rydraw' style='visibility:hidden;font-family:arial;font-size:10px;'>"+propcaptionx+"<span>");
                capxw=$("#__rydraw").width();
                $("#__rydraw").remove();
            }            
            
            if(propcaptiony!=""){
                // Determino la larghezza della caption verticale
                $("body").append("<span id='__rydraw' style='visibility:hidden;font-family:arial;font-size:10px;'>"+propcaptiony+"<span>");
                capyw=$("#__rydraw").width();
                $("#__rydraw").remove();
            }            

            $("#"+propname).html("");

            // Gestione larghezza
            if(propwidth==0){
                propwidth=(propcaptions.length+1)*deltax+capxw+propcapwidth+propstrokewidth+2*propmargin+50;
            }
            
            // Istanzio paper
            var paper=Raphael(propname, propwidth, propheight);
            
            // Traccio la griglia
            var l=Math.floor(0.5*rangeabs).toString().length-1;
            var p=Math.pow(10,l);
            var d=propratio*p;
            var y=proporigy+propmargin+proptitleheight-propstrokewidth;
            var n=0;
            while(y-d>propmargin+proptitleheight-10*propstrokewidth){
                y-=d;
                n+=1;
                paper.path("M "+(propcapwidth+propstrokewidth+propmargin)+" "+y+" L "+(propwidth-2*propmargin)+" "+y)
                .attr({"fill": "#ddd", "stroke":"#ddd","stroke-width":1});
            }
            // Riferimento massimo
            if(n*p>=1){
                var tx=paper.text((deltax*propvalues.length)+propbarskip+2*propcapwidth+propstrokewidth+propmargin+15, y-5, _nformat( (n*p).toString(),0).replace(/&#x02D9;/g,"."));
                tx.attr({"font-size":"10px","fill":"gray"});
                $("tspan", tx.node).attr("dy", 3);
            }
            
            y=proporigy+propmargin+proptitleheight+propstrokewidth;
            n=0;
            while(y+d<propheight-propmargin+3*propstrokewidth){
                y+=d;
                n+=1;
                paper.path("M "+(propcapwidth+propstrokewidth+propmargin)+" "+y+" L "+(propwidth-2*propmargin)+" "+y)
                .attr({"fill": "#ddd", "stroke":"#ddd","stroke-width":1});
            }
            // Riferimento minimo
            if(n*p>=1 && mincap){
                var tx=paper.text((deltax*propvalues.length)+2*propbarskip+propcapwidth+propstrokewidth+propmargin+15, y-5, _nformat( (-n*p).toString(),0).replace(/&#x02D9;/g,"."));
                tx.attr({"font-size":"10px","fill":"gray"});
                $("tspan", tx.node).attr("dy", 3);
            }
            
            // Traccio le barre
            var coordx=propbarskip+propcapwidth+propstrokewidth+propmargin;
            for(var i=0;i<propvalues.length;i++){
                var v=propvalues[i]*propratio;
                var tip=propcaptions[i]+" => "+_nformat(propvalues[i].toString(),0).replace(/&#x02D9;/g,".");
                if(v>=0){
                    paper.rect(coordx, proporigy-v+propmargin+proptitleheight-propstrokewidth, propbarwidth, v)
                        .attr({"gradient":"0-#08f-#048", "fill":"0-#08f-#048", "stroke-width":0, "title":tip});
                    if(i%propcaptionrate==0){
                        var tx=paper.text(coordx+propbarwidth/2, proporigy+propmargin+proptitleheight+5, propcaptions[i] );
                        tx.attr({"font-size":"10px","fill":"gray"});
                        $("tspan", tx.node).attr("dy", 4);
                    }
                }
                else{
                    paper.rect(coordx, proporigy+propmargin+proptitleheight+propstrokewidth, propbarwidth, -v)
                        .attr({"gradient":"0-#f00-#800", "fill":"0-#f00-#800", "stroke-width":0, "title":tip});
                    if(i%propcaptionrate==0){
                        var tx=paper.text(coordx+propbarwidth/2, proporigy+propmargin+proptitleheight-5, propcaptions[i] );
                        tx.attr({"font-size":"10px","fill":"gray"});
                        $("tspan", tx.node).attr("dy", 3);
                    }
                }
                coordx+=deltax;
            }
            
            if(proporigy>=0){
                paper.path("M "+(propcapwidth+propstrokewidth+propmargin)+" "+(proporigy+propmargin+proptitleheight)+" L "+(propwidth-2*propmargin)+" "+(proporigy+propmargin+proptitleheight))
                    .attr("arrow-end", "classic-wide-long")
                    .attr({"fill": "gray", "stroke":"gray","stroke-width":propstrokewidth});
            }
            paper.path("M "+(propcapwidth+propstrokewidth+propmargin)+" "+(propheight-propmargin)+" L "+(propcapwidth+propstrokewidth+propmargin)+" "+(propmargin) )
                .attr("arrow-end", "classic-wide-long")
                .attr({"fill": "gray", "stroke":"gray","stroke-width":propstrokewidth});
            
            if(proptitle!=""){
                var tx=paper.text(propwidth-propmargin-5-titw/2, propmargin+5, proptitle);
                tx.attr({"font-size":"18px","fill":"gray"});
                $("tspan", tx.node).attr("dy", 7);
            }
            
            if(propcaptionx!=""){
                var tx=paper.text(propwidth-propmargin-20-capxw/2, proporigy+propmargin+proptitleheight+propstrokewidth+5, propcaptionx);
                tx.attr({"font-size":"10px","fill":"gray"});
                $("tspan", tx.node).attr("dy", 3);
            }
            
            if(propcaptiony!=""){
                var tx=paper.text(0, 0, propcaptiony);
                tx.attr({"font-size":"10px","fill":"gray"})
                tx.transform("t13,"+(propmargin+20+capyw/2)+"r-90");
                $("tspan", tx.node).attr("dy", 4);
            }
            
			this.name=function(){
				return propname;
			}
			return this;
		}
	});
})(jQuery);
