/****************************************************************************
* Name:            rygeneral.js                                             *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
/*******************
| LIBRERIE CAMBUSA |
*******************/
var RYBOX;
var RYQUE;
var RYQUEAUX;
var RYQUIVER;
var RYWINZ;
var RYJAX;

var _systeminfo={
    relative:{
        apps:"../../apps/",
        cambusa:"../../cambusa/",
        customize:"../../customize/"
    },
    web:{
        root:"/",
        apps:"../../apps/",
        cambusa:"../../cambusa/",
        customize:"../../customize/",
        temporary:"../../customize/temporary/"
    },
    maps:{
        zoom:16,
        lat:45.550084,
        lng:9.180665
    },
    activities:0
}

var _sessioninfo={
    userid:"",
    sessionid:"",
    temporary:"temporary",
    debugmode:1,
    dateformat:0
};

/***************
| SERIE STRING |
***************/
// stringDate
String.prototype.stringDate=function(){
    var d=this.valueOf().replace(/[^\d]/gi, "").substr(0,8)
    return d.length==8 ? d : "";
}
Number.prototype.stringDate=function(){
    var t=new Date(this.valueOf());
    return t.getFullYear() + ("00"+(t.getMonth()+1)).subright(2) + ("00"+t.getDate()).subright(2);
}
Date.prototype.stringDate=function(){
    return this.getFullYear() + ("00"+(this.getMonth()+1)).subright(2) + ("00"+this.getDate()).subright(2);
}
// stringToday
Date.stringToday=function(){
    return (new Date()).stringDate();
}
// stringTime
String.prototype.stringTime=function(){
    var d=(this.valueOf()+"000000").replace(/[^\d]/gi, "").substr(0,14);
    return d.length==14 ? d : "";
}
Number.prototype.stringTime=function(){
    var t=new Date(this.valueOf());
    return t.stringTime();
}
Date.prototype.stringTime=function(){
    return this.getFullYear() + 
           ("00"+(this.getMonth()+1)).subright(2) + 
           ("00"+this.getDate()).subright(2) + 
           ("00"+this.getHours()).subright(2) +
           ("00"+this.getMinutes()).subright(2) +
           ("00"+this.getSeconds()).subright(2);
}
// stringNow
Date.stringNow=function(){
    return (new Date()).stringTime();
}
// stringNumber
String.prototype.stringNumber=function(){
    var f=parseFloat(this.valueOf());
    return isNaN(f) ? "0" : f.toString();
}
Number.prototype.stringNumber=function(){
    return this.toString();
}
Boolean.prototype.stringNumber=function(){
    return this.valueOf() ? "1" : "0";
}
// stringBoolean
String.prototype.stringBoolean=function(){
    return parseInt(this.valueOf()) ? "1" : "0";
}
Number.prototype.stringBoolean=function(){
    return this.valueOf() ? "1" : "0";
}
Boolean.prototype.stringBoolean=function(){
    return this.valueOf() ? "1" : "0";
}

/***************
| SERIE ACTUAL |
***************/
// actualNumber
String.prototype.actualNumber=function(){
    return parseFloat(this.valueOf())||0;
}
Number.prototype.actualNumber=function(){
    return this.valueOf();
}
Date.prototype.actualNumber=function(){
    return this.getTime();
}
Boolean.prototype.actualNumber=function(){
    return this.valueOf() ? 1 : 0;
}
// actualInteger
String.prototype.actualInteger=function(){
    return parseInt(this.valueOf())||0;
}
Number.prototype.actualInteger=function(){
    return Math.floor(this.valueOf());
}
Date.prototype.actualInteger=function(){
    return Math.floor(this.getTime()/86400000);
}
Boolean.prototype.actualInteger=function(){
    return this.valueOf() ? 1 : 0;
}
// actualDate
String.prototype.actualDate=function(){
    var d=this.valueOf().replace(/[^\d]/gi, "");
    if(d.length>=8)
        d=new Date(Date.UTC(parseInt(d.substr(0,4)), parseInt(d.substr(4,2))-1, parseInt(d.substr(6,2)), 0, 0, 0, 0));
    else
        d=new Date(Date.UTC(1900, 0, 1, 0, 0, 0));
    return d;
}
// actualTime
String.prototype.actualTime=function(){
    var d=(this.valueOf()+"000000").replace(/[^\d]/gi, "");
    if(d.length>=14)
        d=new Date(Date.UTC(parseInt(d.substr(0,4)), parseInt(d.substr(4,2))-1, parseInt(d.substr(6,2)), parseInt(d.substr(8,2)), parseInt(d.substr(10,2)), parseInt(d.substr(12,2)), 0));
    else
        d=new Date(Date.UTC(1900, 0, 1, 0, 0, 0));
    return d;
}
// actualBoolean
String.prototype.actualBoolean=function(){
    return parseInt(this.valueOf()) ? true : false;
}
Number.prototype.actualBoolean=function(){
    return this.valueOf() ? true : false;
}
Boolean.prototype.actualBoolean=function(){
    return this.valueOf();
}

/****************
| SERIE BOOLEAN |
****************/
// booleanNumber
String.prototype.booleanNumber=function(){
    return parseInt(this.valueOf()) ? 1 : 0;
}
Number.prototype.booleanNumber=function(){
    return this.valueOf() ? 1 : 0;
}
Boolean.prototype.booleanNumber=function(){
    return this.valueOf() ? 1 : 0;
}

/***************
| SERIE FORMAT |
***************/
function __formatNumber(s,d){
    var f,p,i,g="";
    f=parseFloat(s).toFixed(d);
    if(isNaN(f)){
        f=parseFloat("0").toFixed(d);
    }
    if(f.substr(0,1)=="-"){
        g="-";
        f=f.substr(1);
    }
    if(d>0){
        p=f.indexOf(".");
        f=f.replace(/\./, ",");
    }
    else{
        p=f.length;
    }
    for (i=p-3;i>0;i-=3)
        f=f.substr(0,i)+"&#x02D9;"+f.substr(i);
    return g+f;
}
// formatNumber
String.prototype.formatNumber=function(d){
    return __formatNumber(this.valueOf(), d);
}
Number.prototype.formatNumber=function(d){
    return __formatNumber(this.toString(), d);
}
Boolean.prototype.formatNumber=function(d){
    return this.valueOf() ? "&#x2612;" : "&#x2610;";
}
// formatDate
String.prototype.formatDate=function(e, missing){
    var d=this.valueOf().replace(/[^\d]/gi, "");
    if(d.length<8){
        if(e!=missing)
            return e;
        else
            return "";
    }
    else if(_sessioninfo.dateformat==1)
        return d.substr(4,2)+"/"+d.substr(6,2)+"/"+d.substr(0,4);
    else
        return d.substr(6,2)+"/"+d.substr(4,2)+"/"+d.substr(0,4);
}
// formatTime
String.prototype.formatTime=function(){
    var d=(this.valueOf()+"000000").replace(/[^\d]/gi, "");
    if(d.length<14)
        return "";
    else if(_sessioninfo.dateformat==1)
        return d.substr(4,2)+"/"+d.substr(6,2)+"/"+d.substr(0,4)+" "+d.substr(8,2)+":"+d.substr(10,2);
    else
        return d.substr(6,2)+"/"+d.substr(4,2)+"/"+d.substr(0,4)+" "+d.substr(8,2)+":"+d.substr(10,2);
}
// formatBoolean
String.prototype.formatBoolean=function(){
    return parseInt(v) ? "&#x2612;" : "&#x2610;";
}
Number.prototype.formatBoolean=function(){
    return this.valueOf() ? "&#x2612;" : "&#x2610;";
}
Boolean.prototype.formatBoolean=function(d){
    return this.valueOf() ? "&#x2612;" : "&#x2610;";
}

/*************************
| ALTRE FUNZIONI STRINGA |
*************************/
String.prototype.subright=function(n){
    return this.valueOf().substr(this.length-n,n);
}
String.prototype.reverse=function(){
    return this.valueOf().split("").reverse().join("");
}
String.prototype.htmlDecod=function(){
    try{
        var txt=document.createElement("textarea");
        $(txt).html(this.valueOf());
        return $(txt).val();
    }
    catch(e){
        return this.valueOf();
    }
}
String.prototype.getExtension=function(){
    var f=this.valueOf();
    var p=f.lastIndexOf(".");
    if(p>=0)
        return f.substr(p+1);
    else
        return "";
}
String.prototype.stripTags=function(){
    try{
        var s=this.valueOf();
        s=s.replace(/<[bh]r *\/?>/gi," ");
        s=s.replace(/<[^<>]*>/gi,"");
    }catch(er){}
    return s;
}

/********************************************
| RISOLUZIONE DELLE VARIABILI NON ASSEGNATE |
********************************************/
function __(v){
   return v||"";
}
function _$(v,e){
   return v||e;
}

/***********************
| ARRICCHIMENTO JQUERY |
***********************/
$.extend({
    isset:function(v){
        return (typeof v!=="undefined" && v!==null);
    },
    pause:function(millis){
        var date=new Date();
        var curDate=null;
        do{curDate=new Date();}
        while(curDate-date<millis);
    },
    objectsize:function(o){
        try{
            return Object.keys(o).length;
        }
        catch(e){
            var c=0;
            for(i in o){
                c+=1;
            }
            return c;
        }
    },
    stringify:function(obj){
        try{
            return JSON.stringify(obj);
        }
        catch(er){
            var t = typeof (obj);
            if (t != "object" || obj === null) {
                // simple data type
                if (t == "string") obj = '"'+obj+'"';
                return String(obj);
            }
            else {
                // recurse array or object
                var n, v, json = [], arr = (obj && obj.constructor == Array);
                for (n in obj) {
                    v = obj[n]; t = typeof(v);
                    if (t == "string") v = '"'+v+'"';
                    else if (t == "object" && v !== null) v = $.stringify(v);
                    json.push((arr ? "" : '"' + n + '":') + String(v));
                }
                return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
            }
        }
    },
    engage:function(url, data, issue, options){
        _systeminfo.activities+=1;
        var hprogress=false;
        // LETTURA DELLE OPZIONI
        if(typeof(options)=="object"){
            if(typeof(options.progress)!="undefined"){
                data.progressid=_sessioninfo.userid+Date.stringNow()
            }
        }
        var jqxhr=$.post(url, data)
        .done(function(d){
            _systeminfo.activities-=1;
            if(hprogress!==false){ clearInterval(hprogress) }
            issue(d);
        })
        .fail(function(d){
            _systeminfo.activities-=1;
            if(hprogress!==false){ clearInterval(hprogress) }
            var m, docfail;
            if(typeof(d.statusText)!="undefined"){
                m=d.statusText;
                if(m=="abort")
                    m="Procedure aborted!";
            }
            else{
                m="Procedure failed!";
            }
            if(typeof(options)=="object"){
                switch(typeof(options.failure)){
                case "object":
                    docfail=options.failure;
                    docfail.message=m;
                    break;
                case "string":
                    docfail=options.failure;
                }
            }
            if(typeof(docfail)=="undefined")
                docfail={success:0, message:m};
            issue($.stringify(docfail));
        });
        if($.isset(data.progressid)){
            hprogress=setInterval(function(){
                $.get(_systeminfo.web.customize+"temporary/"+data.progressid+".txt")
                .done(function(d){
                    options.progress(d);
                });
            }, 1000);
        }
        return jqxhr;
    }
});

/***************************
| INFORMAZIONI SUL BROWSER |
***************************/
$.browser.mobile=(navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Mini|Mobile/i)!==null);
$.browser.chrome=(navigator.userAgent.match(/Chrom(e|ium)/i)!==null);
$.browser.HTML5=(function(){
    try{return (document.doctype.publicId=="");}
    catch(er){return true;}
})();

/**********************
| GESTIONE DELLE CODE |
**********************/
var TAIL={
    busy:false,
    enqueue:function(eng){
        if(eng instanceof Function){
            var i,args=[];
            for (i=1; i<arguments.length; i++){
                args.push(arguments[i]);
            }
            TAIL.buffer.push({engage:eng, args:args});
        }
    },
    dequeue:function(){
        if(TAIL.buffer.length>0)
            return eng=TAIL.buffer.shift();
        else
            return false;
    },
    free:function(){
        TAIL.busy=false;
        setTimeout(TAIL.wriggle);
    },
    wriggle:function(){
        if(!TAIL.busy){
            TAIL.busy=true;
            var eng=TAIL.dequeue();
            if(eng){
                setTimeout(
                    function(){
                        try{
                            var ret=false;
                            switch(eng.args.length){
                            case 0:
                                ret=eng.engage();
                                break;
                            case 1:
                                ret=eng.engage(eng.args[0]);
                                break;
                            case 2:
                                ret=eng.engage(eng.args[0], eng.args[1]);
                                break;
                            case 3:
                                ret=eng.engage(eng.args[0], eng.args[1], eng.args[2]);
                                break;
                            case 4:
                                ret=eng.engage(eng.args[0], eng.args[1], eng.args[2], eng.args[3]);
                                break;
                            }
                            if(ret===false){
                                TAIL.abort();
                            }
                        }
                        catch(e){
                            if(window.console){console.log(e.message)}
                            TAIL.abort();
                        }
                    }
                );
            }
            else{
                TAIL.busy=false;
            }
        }
    },
    abort:function(){
        TAIL.buffer=[];
        TAIL.busy=false;
    },
    buffer:[]
};

/*****************
| GALLERIA ICONE |
*****************/
var GALLERY={
    Attachment:function(y){
        y=_$(y, 0);
        return "<img style='margin-top:"+y+"px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAQCAYAAAAmlE46AAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KGAklIGElCSAAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAkJJREFUKM+lk1tIUwEYx/9nN3ex6aZOnS3bMUujGdMSKZY+dDVLMAYh0ovQ6EaQ9RBELwU9CEG9NIgiu8AKibDWyxJLEzQX28TLmrbW3NpB3RmeedzZ3FwvBV00o76n7+H7fXz8+X3A/5bxztyFJnM4feR2IL3p4LnG1eb5AND6Kt0XsPe10j7XgILUpgs2G0zJBI8OT/RPAogtB/IAYJFleUKJgErMO50pbkGajDFshbHtpr7FfBlA3orgAhX1SXNUnHJ9g8H79qkZkC6xM1Nc8Y56U+35rnYAucuCiXiqNslJZHyhRCHLKts/br1+UZRfKmZnQ8jWVjbUnHryEID6N9B+90Q1L0OStxjniKxiXYlIXmLqvrSzWa7eImYCXl6hbm/d9pMdLwHk/BROlPJEU4spW6HuwNnIx6Eva7ftI1NcsizosFwt0O1qCgx12sna4+VKjb7FP/jYAoDlf98Q8Q4EZPmldO7G6mbKafug1jeSYrlqNzcXXhrpvNIbZzyWCmObkQlRWyO+98/5P95NDVtHczYYMpTaqoago8s77ekepcf7hxnK/W56rN9Sc/rMHsrhXzfttnXzfglrftB89Brtdz0o0h8qF0sLI0HXi/ZkjHkEgOJm15CRoCsBQEGsIEZW5THzM4VGVyfKVk9KczWhOEORn3vvF0303LrB0VNW4g9WKQoq6u9lqsjDApEoHYvSzIz7TcfCzKcxAK+JVZTMBFAFQANACIAFiBEg7Sb+wn8ZAMG3ngAwDyD5z9/0Fadn58kX+80CAAAAAElFTkSuQmCC' />";
    },
    High:function(y){
        y=_$(y, 3);
        return "<svg style='margin-top:"+y+"px' width='18' height='18' xmlns='http://www.w3.org/2000/svg'><g><circle cx='8' cy='8' r='5' stroke-width='0' fill='#B22222'/></g></svg>";
    },
    Medium:function(y){
        y=_$(y, 3);
        return "<svg style='margin-top:"+y+"px' width='18' height='18' xmlns='http://www.w3.org/2000/svg'><g><circle cx='8' cy='8' r='5' stroke-width='0' fill='#E69400'/></g></svg>";
    },
    Low:function(y){
        y=_$(y, 3);
        return "<svg style='margin-top:"+y+"px' width='18' height='18' xmlns='http://www.w3.org/2000/svg'><g><circle cx='8' cy='8' r='5' stroke-width='0' fill='#4DA64D'/></g></svg>";
    },
    Pencil:function(y){
        y=_$(y, 1);
        return "<img style='margin-top:"+y+"px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAUCAYAAABSx2cSAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KFwkCJOTubwoAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAg9JREFUOMvtkE1I0wEAxd9/7us/JA8uRTtEUIRWjhilECUOiqIuEXUIJaIPyE4RUjEGRkHYcRA4tCAFo1z2oczY3JZlaBv1d0patukIWxvK3ED24dxeJyHIrQ4de9fHj/fjAX+R6Kv+bXNtbQzZHMySJGkFANmfwPD8tzpvJDXj7bYyZDJhxtiK1UwCBWGSpYuh8O2P4yOjWr2etZ0WYVkmYun5ABLS+AYAkOeDA1Pemln/hDG5GEaFLiRIQR1e761C42FDrqR236G8qrOffLUvn3bQ1dWSS0Z20uOo4q2W85Skye8A8MU1KKyrHY3GLrptj8aKOc+GA++F6Q8l6OvZhB26XV5NunMzAGw3HOHvr0YDTeabZ2l/eJ3Z4ClOuJp54bSeHlt3uOCr8bnP9a7euxwwHyOlPQwON7H1ygn63EMRAJCcPcL6z67ELk867nDYUp3jaDmnrQbeM52k68V9NwD43E4h72pmzrMQ6NWS7yrpt25h+w0Dx970SSSFgrrZeObS135jOvWkjku2MvabD3LB9/bHWr/MeFE+Vma3Pz5HVZkyWtSAJfFaend987ONuv0V8amRtdXSvMuNZ47T6ehiov1qBbORyl+71UymoLZg6XiwUlNdpVDIlFBo5FAWa1CsFinKlYPa8rKjBWGSWyOhmFGlRk4uKofUouhVCDI/AKSTKUElqon/+Tf5CTJhAFJv0ZlwAAAAAElFTkSuQmCC' />";
    },
    Answer:function(y){
        y=_$(y, 0);
        return "<img style='margin-top:"+y+"px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAUCAYAAAC9BQwsAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KFwkPL8aSyM8AAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAARpJREFUOMvtkz2KwnAQxX/zJ5VFII0Qxc7eixgL2xS5gF2KgCfY2ntYSa6Qxt5CxMZK8wHaJBCT2UatVlntFvbBMLyBxwzvMaKqfALDh/hDQgtARN5yaLPZgDFG5/O5qiq/qbquARRAVZXhcKi3wctSVUajkcqNyDun9no9te5kNptpHMfYtk3TNIgIxhjatsUYw/V6JU1TTqeT9Pt9HhuPx+NXkiRRt9ulbVuyLGO73eK6LoPBgKZpKMsSz/NERNRaLpeIiPq+j23bnM9nLpcLYRgSRREAnufhOA6O4zCZTHQ8HmOm06ms12uKoiDPczqdDlVVEQQBt6gefb/fs1gsWK1W8tT2u4uHw+HHqF4Kd7vd03zl/62e4xvaIuiakyUzWwAAAABJRU5ErkJggg==' />";
    },
    Replied:function(y){
        y=_$(y, 2);
        return "<img style='margin-top:"+y+"px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAUCAYAAABSx2cSAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KGAkcFTkSQLkAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAA3JJREFUOMuFlEtoXHUUxn//m3mbNHPTvKY05NGUMBloSBZCB2vIRAxNqRbjA62LqkFiqFgKGoQirtyIi4JdiFrcFNyUVjRVSi1qoE20TNUwSUubzKQ2uXk4zs1kZu7/ztw710VU0kr1W32Hcz7OOfCdI/gfnDpybHcwPf2cHBgyXzk6+v7WnGtrkHjjoIic/NK5OvrEcq7Sv17ZtJfU0lqw865WP3F90gIeLP7p4pKIv9pX5saEI9xOw8bUBTyiEi82nsId6/6pxN9k9vjrbbn01KyzNucu4xYbCIqWG3eFRfOzLzHx3fVSZlttr1qouqpqcTKxIZSVozHx2VcnXRXmz4lAbtWj+KqFgk3AKiJNncjh49S/8C6Db77lrstlJqbX3B1RdGT8ymbnxdGYI1cXnFK5LPIYWLagkC1Q9+LbtL88xkrWxMqDdvtXZs+8R01Te1VmJp5TAG51PcVa7W58ooCnWE3ZsAkdGaPl8DH0fAnbdrCkg3fXHnb0POnMz6c2gq+d71cAitH9h9ZGPhBzLY8ji+vUHBym8ekRTMVL0XTQsxbXcjaLdyWr4UdEfte+8jbBLQHwxc2FHbYnsGiaDunJH3i0bx8FV4CLk+vUubMU/DWMfTzNMwMdDLaXqMiv2vu7q10KgN/lFW7LQbG34+8aAu92LiU2eOf0b/yS+J07eReurElFpkzONFGKJhlaUQCcdHo1u5L9vOiYOIaOYVp01m/jxHALg7EOSqaBLClYbpuyqKDo8SINNsUDD0dKjUr2+fVUYhqhoEuH5sYAe7uqqWp0YQkXqidN+uY1fvzm63Il2PeY5PT3t0VD00NO6oZ2eWdDQ1+5qGPbbtaX5kj9kWNxZv5cT6Qj8FjvnpHFspqSywtCbLXbR5cSojbS5mxMTh3SknOlGlXFcQwinWHPVDp0LpNMcmCom7OXNU6NJ+/19s62GkcakqqO3vNWcy8hnyTa6uPEhSSfns3QKjWisTBJQwV9dnPnf6AbIH1kpASpI+Ev7ieERjgE4AOZ/PdVHehp45NvZ/CHWjEkSECVEOsO0h2O0uqTSEASRErtvs7AcH8nmeQsAIaEDBBSfYSCgE+Cb7Mu6NMRD/ogH47HUUNhwiFJxgApQWpJkssaV2Y0fPp/iAHOjMcxpIau6QTVINLQUdUgjaEQ/dEe/gRI2pWNS4KICQAAAABJRU5ErkJggg==' />";
    },
    Action:function(y){
        y=_$(y, 1);
        return "<img style='margin-top:"+y+"px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAUCAYAAABiS3YzAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB94MFQYMOMbtFnMAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAm5JREFUOMullU9IVUEUxn9nnk/tmZIuQnMRJUWFFtKqTdI/27ht07aFYDsXLaNlJUSLNkW0CylpEVhRLkoIwpAyMSGoRULPJOgv6Xu+mTktbnO7776rLjowiznzzXfPOXO+c0VVAfDeIyKICADBLyKoauwP5pwjl8vF+4AHMADWWpxb5dPCu+NJMu89zrlMwrQFvKpinHMYY8jnG/n28vpEiCz+qjFVe+997M8yEYnC9t7jvWfh/dz0m9uDGnyqmrnWO1NVahyLIw36cXZS1yLaiFBVqcmh1P+Q/IM+rF2pSltEmHl8U9P1zTKTvKiqbO/uE4AvV1o01FBV+TB1T9tnhpl/9VzXI/TeR5E65+JXFpPj654L4C2zd4fVGIOI0DR5Bu3sYnX+Gvq3Y9JmrY3aLzxSuBx6cvGiKMDyiXHKb+/Q+mMKthWgtMxK7xg79vZIshQho1wuF0WabOIALDYPAFCYGKBtaRyaCxGgoZ7y3EhN7xpjYh4TDtOgA4NjsUMLHVAwoFHKW9wMlXIJyC6vWavgdXWNfN/UjeRaoakegF8tp0A95GHxyZCCZBKbpPaTpqpsPnIVbWiFRs9S/iS7+88LO4eg4smvvsZ7R0Rc3e9GROJXS8uts+eYsHUfmDbqd52+pKp0HDwrdBwFMSw9PadZ2sdai7U2UynWWorTt3Tq2aNYYc45KhVL8f5hLY51q3flKrz3njVll5RlWFV+LVEc3a/FF5drZsWGOl5vef+TKNqVKlLDf5hIM/Te4PPsaNVMMOkHytJyuisSO9q7Dgl+FdXf//4goV5pZYWZICJVAznUL41XreBchP0DRfVJ/XPEv/UAAAAASUVORK5CYII=' />";
    },
    PencilAttachment:function(y){
        y=_$(y, 0);
        return "<img style='margin-top:"+y+"px' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QMNCBIDS+iwWwAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADYElEQVQ4y4WR3U+bZRjGr+f9aN9+rbzQD0oLbO3awWabQbEao5mkzjETgtmCCXHqDG7LTHCabBr9AzDxAPVg6pKJG8qyg2lcDEsI4BidY7qxACsgo0MctJTR9t1aoB+0fTzYOqeJ8Xd0n1y/O/d9ETyk+ev7x3Lr2U8Im4T/x46Xp3s6zuNfHL82KDT6A8m1QBDlbYehMOrBAkBrP/UtXPe1xubGrorWTbR063OHshkmFp35JQAgCQArK9Gq7MTdxZETp6lm+DKJ37gBVZ3zgcC599hba9Ewnwj7Bossru35TJqaXPVNcs1mYaMycnNqcrz1tyuXTgl6i9L5ShPJR+KInO+hoqeGsABgr2/zsnLGxHIWU8jf951Y7vYkpRAtsW2vy2vtZRXZ8fejwYDS4ZgAQ6rQOXqTmvd4SbHBsI0BgEw6tyObUqhYXiGqtFUNUz0dH8qMdmE1sgj9Jte+U8MpbN3SC1E4h+Pt+7HTu5NsaWxu2PhS4yQDANc7D3sYuUK/nk4RbaXTJttgOxT/4e2jG8qeEO7NB5Cz7kH7QBM+/ZinukoXdLrkfnulpRfAgx8kwrcSufVcn8m5+4h0+1rIUrfLlooEX6yInGUS5nqogt+AOo5gTjKQF2xii7expbvQDFsYpNmrC+pSu6RzeFrIHwMsb21gllGCTDyC+K9nYJPdAb/jnfzJEye10tzITwDSAMA8VjN9UhjtflZ7C+sGD2igC7rlTiinz0BfzOIpR3WLUBIbV4mbqwE4CqHHBfiq/YNFd/ZdWosu5KyvIZMxosbOoe3AwbZXD7x+NhXRWKXgWAaAWMiQR+spfSb482e9WukLdVYRwsEL7yFWtBvqElOIEytup+Nh659DXeaZi19+norN9wDo+4dgctjXKbt3+c3ixCjC+TTuK+vwRvvIxbzaXM/JZDSZiCUi00OnV+/OTgAYBDD9SHDpgm/fqL//2+drK6BZyS6V1T49Ky937SWEJAC4AZQD4AGsAsQP0N8B5AGAm5yYkQ8M9e8am51DtbsGknFb89LSapEmOeW9E5yXR8PLtMZd+/3fJ9OVQhgAiM83LKTS6Y84XqYwG01HeY6AsvQK4dlFljC9AsuvGUqN3fgPyMMHEgBceCGmlSkIWDm3BkJyRWpNGv/DX1roWoW3ud2+AAAAAElFTkSuQmCCAA==' />";
    }
}

