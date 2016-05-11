/****************************************************************************
* Name:            rydraw.js                                                *
* Project:         Cambusa/rydraw                                           *
* Version:         1.69                                                     *
* Description:     Graphic Features                                         *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
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
            var propvalues=[];
            var propcaptions=false;
            var propitems=false;
            var proptitle="";
            var propbackground="transparent";
            var propcaptionx="";
            var propcaptiony="";
            var propcaptionrate=1;
            var propmaxvalue=-999999999;
            var propminvalue=999999999;
            var propratio=1;
            var proporigy=0;
			var propobj=this;
			var propname=$(this).attr("id");
			
			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.values!=missing){propvalues=settings.values}
            if(settings.captions!=missing){propcaptions=settings.captions}
            if(settings.items!=missing){propitems=settings.items}
            if(settings.captionrate!=missing){propcaptionrate=settings.captionrate}
            if(settings.title!=missing){proptitle=settings.title}
            if(settings.background!=missing){propbackground=settings.background}
            if(settings.captionx!=missing){propcaptionx=settings.captionx}
            if(settings.captiony!=missing){propcaptiony=settings.captiony}
            if(settings.barwidth!=missing){propbarwidth=settings.barwidth}
            if(settings.barskip!=missing){propbarskip=settings.barskip}
            
            if(propitems){
                propvalues=[];
                propcaptions=[];
                for(var i in propitems){
                    propvalues.push(propitems[i].value);
                    if(propitems[i].caption)
                        propcaptions.push(propitems[i].caption);
                    else
                        propcaptions.push( (i+1).toString() );
                }
            }
            
            // Determino minimo e massimo dei valori
            for(var i in propvalues){
                if(propmaxvalue<propvalues[i])
                    propmaxvalue=propvalues[i];
                if(propvalues[i]<propminvalue)
                    propminvalue=propvalues[i];
            }
            
            if(propcaptions===false){
                propcaptions=[];
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
            $("#"+propname).css({position:"absolute",left:propleft,top:proptop, "background-color":propbackground, "border":"1px solid silver"});
            
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
                var tx=paper.text((deltax*propvalues.length)+propbarskip+2*propcapwidth+propstrokewidth+propmargin+15, y-5, __formatNumber( (n*p).toString(),0).replace(/&#x02D9;/g,"."));
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
                var tx=paper.text((deltax*propvalues.length)+2*propbarskip+propcapwidth+propstrokewidth+propmargin+15, y-5, __formatNumber( (-n*p).toString(),0).replace(/&#x02D9;/g,"."));
                tx.attr({"font-size":"10px","fill":"gray"});
                $("tspan", tx.node).attr("dy", 3);
            }
            
            // Traccio le barre
            var coordx=propbarskip+propcapwidth+propstrokewidth+propmargin;
            for(var i=0;i<propvalues.length;i++){
                var v=propvalues[i]*propratio;
                var tip=propcaptions[i]+" => "+__formatNumber(propvalues[i].toString(),0).replace(/&#x02D9;/g,".");
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
		},
		rypie:function(settings){
			var propleft=20;
			var proptop=20;
			var propwidth=0;
			var propheight=500;
            var propradius=500;
            var propmargin=50;
            var propitems=[];
            var propnormalization=100;
            var propprecision=0;
            var proptitle="";
            var propbackground="transparent";
			var propobj=this;
			var propname=$(this).attr("id");
			
			if(settings.left!=missing){propleft=settings.left}
			if(settings.top!=missing){proptop=settings.top}
			if(settings.width!=missing){propwidth=settings.width}
            if(settings.height!=missing){propheight=settings.height}
            if(settings.radius!=missing){propradius=settings.radius}
            if(settings.items!=missing){propitems=settings.items}
            if(settings.normalization!=missing){propnormalization=settings.normalization}
            if(settings.precision!=missing){propprecision=settings.precision}
            if(settings.title!=missing){proptitle=settings.title}
            if(settings.background!=missing){propbackground=settings.background}
            
            // Normalizzo i valori e li memorizzo in un nuovo oggetto
            var objitems=[];
            
            // Totalizzazione
            var tot=0;
            for(var i in propitems){
                if(propitems[i].value>=0){
                    // Totalizzazione
                    tot+=propitems[i].value;
                    objitems.push(propitems[i]);
                }
            }

            // Generazione colori
            var colorvalues=[];
            var l=objitems.length;
            for(var i=0; i<l; i++){
                colorvalues.push("hsl(" + 360*i/l + "," + 90 + "," + (40+20*(i%2)) + ")")
            }
            
            for(var i in objitems){
                // Gestione etichetta
                if(!$.isset(objitems[i].caption))
                    objitems[i].caption="<VALUE>";
                var vl=(propnormalization*objitems[i].value/tot).toFixed(propprecision).toString();
                if(propnormalization==100)
                    vl+="%";
                objitems[i].caption=objitems[i].caption.replace(/<VALUE>/g, vl);
                
                // Gestione colore
                if(!$.isset(objitems[i].color)){
                    objitems[i].color=colorvalues[i];
                }
            }
            
            var cx=propmargin+propradius;
            var cy=propmargin+propradius;
            var rad = Math.PI / 180;
            var startAngle=0;
            var minleft=cx, maxright=cx;
            var mintop=cy, maxbottom=cy;
            
            $("#"+propname).addClass("rypie");
            $("#"+propname).css({position:"absolute",left:propleft,top:proptop,"background-color":propbackground, "border":"1px solid silver"});

            $("#"+propname).html("");

            // Calcolo la larghezza delle etichette
            // Creo uno span temporaneo
            $("body").append("<span id='__rydraw' style='visibility:hidden;font-family:sans-serif;font-size:18px;'><span>");
            for(var i in objitems){
                $("#__rydraw").html( objitems[i].caption );
                // Memorizzo nella struttura la larghezza dell'etichetta
                objitems[i].width=$("#__rydraw").width();
            }
            // Distruggo lo span temporaneo
            $("#__rydraw").remove();
            
            // Traccio il diagramma in prova con un raggio pivot
            var limits=drawing(true);
            minleft=limits.left;
            maxright=limits.right;
            mintop=limits.top;
            maxbottom=limits.bottom;
            
            if(propwidth>0){
                while(maxright-minleft>propwidth-propmargin || maxbottom-mintop>propheight-2*propmargin){
                    propradius-=25;
                    if(propradius>50){
                        limits=drawing(true);
                        minleft=limits.left;
                        maxright=limits.right;
                        mintop=limits.top;
                        maxbottom=limits.bottom;
                    }
                    else
                        break;
                }
            }
            else if(propheight>0){
                while(maxbottom-mintop>propheight-2*propmargin){
                    propradius-=25;
                    if(propradius>50){
                        limits=drawing(true);
                        minleft=limits.left;
                        maxright=limits.right;
                        mintop=limits.top;
                        maxbottom=limits.bottom;
                    }
                    else
                        break;
                }
                propwidth=maxright-minleft+2*propmargin;
            }
            else{
                propwidth=maxright-minleft+2*propmargin;
                propheight=maxbottom-mintop+4*propmargin;
            }
            
            // Riposiziono il centro
            cx=propmargin/2+limits.sizex+propradius*1.1;
            cy=propmargin+limits.sizey+propradius*1.1;

            // Istanzio paper
            var paper=Raphael(propname, propwidth, propheight);
            
            // Traccio il cerchio
            paper.circle(cx, cy, propradius);
            
            if(tot>0.0001){
                drawing(false);
            }
            if(proptitle!=""){
                var tx=paper.text(10, 15, proptitle);
                tx.attr({"font-size":"18px", "font-family":"sans-serif", "fill":"gray"});
                $("tspan", tx.node).attr({"dy":7,"text-anchor":"start"});
            }
            
			this.name=function(){
				return propname;
			}
            
            function drawing(test){
                var endAngle,capangle,x1,x2,y1,y2,tx,prevy=false,prevs=1;
                var minleft=cx-propradius-propmargin/2, maxright=cx+propradius+propmargin;
                var mintop=cy-propradius-propmargin/2, maxbottom=cy+propradius+propmargin;
                var sizex=0, sizey=0;
                startAngle=90;
                for(var i in objitems){
                    if(objitems.length>1){
                        endAngle=startAngle+360*objitems[i].value/tot;
                        x1 = cx + propradius * Math.cos(-startAngle * rad);
                        y1 = cy + propradius * Math.sin(-startAngle * rad);
                        x2 = cx + propradius * Math.cos(-endAngle * rad);
                        y2 = cy + propradius * Math.sin(-endAngle * rad);
                        if(!test)
                            paper.path(["M", cx, cy, "L", x1, y1, "A", propradius, propradius, 0, +(endAngle - startAngle > 180), 0, x2, y2, "z"]).attr({"stroke-width":0, "fill": objitems[i].color})
                    }
                    else{
                        endAngle=450;
                        if(!test)
                            paper.circle(cx, cy, propradius).attr({"stroke-width":0, "fill":objitems[i].color});
                    }
                    startAngle=endAngle
                }
                startAngle=90;
                for(var i in objitems){
                    if(objitems.length>1){
                        endAngle=startAngle+360*objitems[i].value/tot;
                        capangle=startAngle+360*objitems[i].value/tot/2;
                    }
                    else{
                        endAngle=450;
                        capangle=430;
                    }

                    x1 = cx + propradius * Math.cos(-capangle * rad) / 2;
                    y1 = cy + propradius * Math.sin(-capangle * rad) / 2;
                    x2 = cx + propradius * Math.cos(-capangle * rad) * 1.1;
                    y2 = cy + propradius * Math.sin(-capangle * rad) * 1.1;
                    
                    var deltay=0;
                    var deltax=0;
                    if(prevy!==false && signum(x2-x1)==prevs){
                        if(Math.cos(-capangle * rad)>0){
                            if(prevy-y2<24) // dx
                                deltay=prevy-y2-24;
                        }
                        else{
                            if(y2-prevy<24) // sx
                                deltay=24-y2+prevy;
                        }
                    }
                    prevy=y2+deltay;
                    prevs=signum(x2-x1);
                    if(prevs==0)
                        prevs=-1;
                    
                    var d=Math.pow(x2-cx, 2) + Math.pow(y2+deltay-cy, 2) - Math.pow(propradius, 2);
                    if(x1<x2){
                        deltax=30; // dx
                        if(d<0)
                            deltax+=Math.floor(propradius/3);
                    }
                    else{
                        deltax=-30; // sx
                        if(d<0)
                            deltax-=Math.floor(propradius/3);
                    }
                    
                    if(!test){
                        // linea obliqua
                        paper.path("M"+x1+","+y1+" L"+x2+","+y2).attr({"stroke-width":1, "stroke":"silver"});
                        
                        // lineetta orizzontale ed etichetta
                        if(x1<x2){
                            paper.path("M"+x2+","+y2+" L"+(x2+deltax)+","+(y2+deltay)).attr({"stroke-width":1, "stroke":"silver"});
                        
                            tx=paper.text(x2+deltax+3, (y2+deltay), objitems[i].caption);
                            tx.attr({"font-size":"18px", "font-family":"sans-serif", "fill":"navy"});
                            $("tspan", tx.node).attr({"dy":7, "text-anchor":"start"});
                        }
                        else{
                            paper.path("M"+x2+","+y2+" L"+(x2+deltax)+","+(y2+deltay)).attr({"stroke-width":1, "stroke":"silver"});

                            tx=paper.text(x2+deltax-3, (y2+deltay), objitems[i].caption);
                            tx.attr({"font-size":"18px", "font-family":"sans-serif", "fill":"navy"});
                            $("tspan", tx.node).attr({"dy":7, "text-anchor":"end"});
                        }
                    }
                    if(x1<x2)
                        x2=x2+deltax+3+objitems[i].width;
                    else
                        x2=x2+deltax-3-objitems[i].width;
                    if(minleft>x2){
                        minleft=x2;
                        sizex=Math.abs(deltax)+3+objitems[i].width;
                    }

                    if(maxright<x2)
                        maxright=x2;

                    y2=y2+deltay;
                    if(mintop>y2-24){
                        mintop=y2-24;
                        sizey=Math.abs(deltay)+24;
                    }

                    if(maxbottom<y2+24)
                        maxbottom=y2+24;
                    
                    startAngle=endAngle
                }
                return {left:minleft, right:maxright, top:mintop , bottom:maxbottom, sizex:sizex, sizey:sizey};
            }
            function signum(x){
                return (x<=0) ? -1 : 1;
            }
			return this;
		}
	});
})(jQuery);
