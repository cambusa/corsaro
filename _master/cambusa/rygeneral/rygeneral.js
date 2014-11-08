/****************************************************************************
* Name:            rygeneral.js                                             *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
var _baseURL="/";
var _cambusaURL="../../cambusa/";
var _appsURL="../../apps/";
var _customizeURL="../../customize/";
var _tempenviron="temporary";
var _temporaryURL=_customizeURL+"temporary/";
var _sessionid="";
var _sessioninfo={
    debugmode:1,
    dateformat:0
};
var _googleZoom=16;
var _googleLat=45.550084;
var _googleLng=9.180665;
var _mobiledetected=(navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Mini|Mobile/i)!==null);
$.browser.chrome=(navigator.userAgent.match(/Chrom(e|ium)/i)!==null);
function _ismissing(v){
    return (typeof v==="undefined" || v===null);
}
function _isset(v){
    return (typeof v!=="undefined" && v!==null);
}
function _nformat(s,d){
    var f,p,i;
    var g="";
    if(s.substr(0,1)=="-"){
        g="-";
        s=s.substr(1);
    }
    if(s.substr(0,1)=="."){
        s="0"+s;
    }
    if(d>0){
        f=parseFloat(s).toFixed(d);
        p=f.indexOf(".");
        f=f.replace(/\./,",");
    }
    else{
        f=parseInt(s).toString();
        p=f.length;
    }
    for (i=p-3;i>0;i-=3)
        f=f.substr(0,i)+"&#x02D9;"+f.substr(i);
    return g+f;
}
function _isobject(v){
    return (typeof v=='object');
}
function _bool(v){
    if((typeof v)=="string")
        v=parseInt(v);
    return v ? 1 : 0;
}
function _pause(millis){
    var date=new Date();
    var curDate=null;
    do{curDate=new Date();}
    while(curDate-date<millis);
}
function _jsonp(url) {   // Per richieste cross domain
	var head = document.getElementsByTagName("head")[0]; 
	var script = document.createElement("SCRIPT"); 
	script.type = "text/javascript"; 
	script.src = url;
	head.appendChild(script); 
}
function _utf8(k){
    switch(k){
        case "a":return "à";
        case "A":return "à".toUpperCase();
        case "e":return "è";
        case "E":return "è".toUpperCase();
        case "e'":return "é";
        case "E'":return "é".toUpperCase();
        case "i":return "ì";
        case "I":return "ì".toUpperCase();
        case "o":return "ò";
        case "O":return "ò".toUpperCase();
        case "u":return "ù";
        case "U":return "ù".toUpperCase();
    }
}
function _ajaxescapize(t){
    return t.replace(/'/g,"\'").replace(/\\/g,"\\\\");
}
function _likeescapize(t){
    return t.toUpperCase().replace(/ /g,"%").replace(/[^A-Z0-9]/g,"%");
}
function _stringify(obj){
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
                else if (t == "object" && v !== null) v = _stringify(v);
                json.push((arr ? "" : '"' + n + '":') + String(v));
            }
            return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
        }
    }
}
function _HTML5(){
    try{return (document.doctype.publicId=="");}
    catch(er){return true;}
}
function _today(){
    var t=new Date();
    return t.getFullYear() + strRight("00"+(t.getMonth()+1),2) + strRight("00"+t.getDate(),2);
}
function _time(){
    var t=new Date();
    return t.getFullYear() + 
           strRight("00"+(t.getMonth()+1),2) + 
           strRight("00"+t.getDate(),2) + 
           strRight("00"+t.getHours(),2) +
           strRight("00"+t.getMinutes(),2) +
           strRight("00"+t.getSeconds(),2);
}
function _getinteger(s){
    if((typeof s)==="undefined")
        return 0;
    if(s==null)
        return 0;
    if((typeof s)==="string" ){
        // Opera e Safari, se c'è 0 davanti, si comportano male
        s=s.replace(/^0+/, "");
        s=s.replace(/ /g, "");
        if(s=="")
            return 0;
        if(s.toLowerCase()=="null")
            return 0;
        if(s.substr(0,1)==".")
            s="0"+s;
        return parseInt(s);
    }
    else{
        return s;
    }
}
function _getfloat(s){
    if((typeof s)==="undefined")
        return 0;
    if(s==null)
        return 0;
    if((typeof s)==="string" ){
        // Opera e Safari, se c'è 0 davanti, si comportano male
        s=s.replace(/^0+/, "");
        if(s=="")
            return 0;
        if(s.toLowerCase()=="null")
            return 0;
        if(s.substr(0,1)==".")
            s="0"+s;
        s=parseFloat(s);
        if(s==NaN || _ismissing(s))
            s=0;
        return s;
    }
    else{
        return s;
    }
}
function _fittingvalue(v){
    if( (typeof v)==="undefined")
        return "";
    if( (typeof v)==="string" ){
        if(v.toLowerCase()=="null"){
            return "";
        }
    }
    if(v===null)
        return "";
    return v;
}
function _dformat(d, e, missing){
    d=d.replace(/[^0-9]/, "");
    if(d!=""){
        dy=d.substr(0,4);
        dm=d.substr(4,2);
        dd=d.substr(6,2);
        return dd+"/"+dm+"/"+dy;
    }
    else{
        if(e!=missing)
            return e;
        else
            return "01/01/1900";
    }
}
function _strip_tags(s){
    try{
        s=s.replace(/<[bh]r *\/?>/gi," ");
        s=s.replace(/<[^<>]*>/gi,"");
    }catch(er){}
    return s;
}
function _decodehtml(s){
    try{
        var txt=document.createElement("textarea");
        $(txt).html(s);
        return $(txt).val();
    }
    catch(e){
        return s;
    }
}
function _objectlength(o){
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
}
function _getextension(f){
    var p=f.lastIndexOf(".");
    if(p>=0)
        return f.substr(p+1);
    else
        return "";
}
function _iconAttachment(){
    return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAQCAYAAAAmlE46AAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KGAklIGElCSAAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAkJJREFUKM+lk1tIUwEYx/9nN3ex6aZOnS3bMUujGdMSKZY+dDVLMAYh0ovQ6EaQ9RBELwU9CEG9NIgiu8AKibDWyxJLEzQX28TLmrbW3NpB3RmeedzZ3FwvBV00o76n7+H7fXz8+X3A/5bxztyFJnM4feR2IL3p4LnG1eb5AND6Kt0XsPe10j7XgILUpgs2G0zJBI8OT/RPAogtB/IAYJFleUKJgErMO50pbkGajDFshbHtpr7FfBlA3orgAhX1SXNUnHJ9g8H79qkZkC6xM1Nc8Y56U+35rnYAucuCiXiqNslJZHyhRCHLKts/br1+UZRfKmZnQ8jWVjbUnHryEID6N9B+90Q1L0OStxjniKxiXYlIXmLqvrSzWa7eImYCXl6hbm/d9pMdLwHk/BROlPJEU4spW6HuwNnIx6Eva7ftI1NcsizosFwt0O1qCgx12sna4+VKjb7FP/jYAoDlf98Q8Q4EZPmldO7G6mbKafug1jeSYrlqNzcXXhrpvNIbZzyWCmObkQlRWyO+98/5P95NDVtHczYYMpTaqoago8s77ekepcf7hxnK/W56rN9Sc/rMHsrhXzfttnXzfglrftB89Brtdz0o0h8qF0sLI0HXi/ZkjHkEgOJm15CRoCsBQEGsIEZW5THzM4VGVyfKVk9KczWhOEORn3vvF0303LrB0VNW4g9WKQoq6u9lqsjDApEoHYvSzIz7TcfCzKcxAK+JVZTMBFAFQANACIAFiBEg7Sb+wn8ZAMG3ngAwDyD5z9/0Fadn58kX+80CAAAAAElFTkSuQmCC' />";
}
function _iconHigh(){
    return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAUCAYAAAC58NwRAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KDhIvJheoAE0AAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAD9JREFUKM9jYKAG2CAt/X+DtPR/bHKM2BQj8wOePmXEqQGXqciaGAkpRtfESIxiXM4jytNMpIbgqAaaaBiRAAA5XBepft1O7wAAAABJRU5ErkJggg==' />";
}
function _iconLow(){
    return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAUCAYAAAC58NwRAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KDhIpBeOV1rkAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAEBJREFUKM9jYKAGUNct+K+uW/AfmxwTqYaNaqCJBkZYRBGj+OblCYyMyLFLSDHcBkKaYIoxNGDThKyYrMQ3IgEAqGAXIXm6H2oAAAAASUVORK5CYII=' />";
}
function _iconPencil(){
    return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAUCAYAAABSx2cSAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KFwkCJOTubwoAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAg9JREFUOMvtkE1I0wEAxd9/7us/JA8uRTtEUIRWjhilECUOiqIuEXUIJaIPyE4RUjEGRkHYcRA4tCAFo1z2oczY3JZlaBv1d0patukIWxvK3ED24dxeJyHIrQ4de9fHj/fjAX+R6Kv+bXNtbQzZHMySJGkFANmfwPD8tzpvJDXj7bYyZDJhxtiK1UwCBWGSpYuh8O2P4yOjWr2etZ0WYVkmYun5ABLS+AYAkOeDA1Pemln/hDG5GEaFLiRIQR1e761C42FDrqR236G8qrOffLUvn3bQ1dWSS0Z20uOo4q2W85Skye8A8MU1KKyrHY3GLrptj8aKOc+GA++F6Q8l6OvZhB26XV5NunMzAGw3HOHvr0YDTeabZ2l/eJ3Z4ClOuJp54bSeHlt3uOCr8bnP9a7euxwwHyOlPQwON7H1ygn63EMRAJCcPcL6z67ELk867nDYUp3jaDmnrQbeM52k68V9NwD43E4h72pmzrMQ6NWS7yrpt25h+w0Dx970SSSFgrrZeObS135jOvWkjku2MvabD3LB9/bHWr/MeFE+Vma3Pz5HVZkyWtSAJfFaend987ONuv0V8amRtdXSvMuNZ47T6ehiov1qBbORyl+71UymoLZg6XiwUlNdpVDIlFBo5FAWa1CsFinKlYPa8rKjBWGSWyOhmFGlRk4uKofUouhVCDI/AKSTKUElqon/+Tf5CTJhAFJv0ZlwAAAAAElFTkSuQmCC' />";
}
function _iconAnswer(){
    return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAUCAYAAAC9BQwsAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KFwkPL8aSyM8AAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAARpJREFUOMvtkz2KwnAQxX/zJ5VFII0Qxc7eixgL2xS5gF2KgCfY2ntYSa6Qxt5CxMZK8wHaJBCT2UatVlntFvbBMLyBxwzvMaKqfALDh/hDQgtARN5yaLPZgDFG5/O5qiq/qbquARRAVZXhcKi3wctSVUajkcqNyDun9no9te5kNptpHMfYtk3TNIgIxhjatsUYw/V6JU1TTqeT9Pt9HhuPx+NXkiRRt9ulbVuyLGO73eK6LoPBgKZpKMsSz/NERNRaLpeIiPq+j23bnM9nLpcLYRgSRREAnufhOA6O4zCZTHQ8HmOm06ms12uKoiDPczqdDlVVEQQBt6gefb/fs1gsWK1W8tT2u4uHw+HHqF4Kd7vd03zl/62e4xvaIuiakyUzWwAAAABJRU5ErkJggg==' />";
}
function _iconReplied(){
    return "<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAUCAYAAABSx2cSAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90KGAkcFTkSQLkAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAA3JJREFUOMuFlEtoXHUUxn//m3mbNHPTvKY05NGUMBloSBZCB2vIRAxNqRbjA62LqkFiqFgKGoQirtyIi4JdiFrcFNyUVjRVSi1qoE20TNUwSUubzKQ2uXk4zs1kZu7/ztw710VU0kr1W32Hcz7OOfCdI/gfnDpybHcwPf2cHBgyXzk6+v7WnGtrkHjjoIic/NK5OvrEcq7Sv17ZtJfU0lqw865WP3F90gIeLP7p4pKIv9pX5saEI9xOw8bUBTyiEi82nsId6/6pxN9k9vjrbbn01KyzNucu4xYbCIqWG3eFRfOzLzHx3fVSZlttr1qouqpqcTKxIZSVozHx2VcnXRXmz4lAbtWj+KqFgk3AKiJNncjh49S/8C6Db77lrstlJqbX3B1RdGT8ymbnxdGYI1cXnFK5LPIYWLagkC1Q9+LbtL88xkrWxMqDdvtXZs+8R01Te1VmJp5TAG51PcVa7W58ooCnWE3ZsAkdGaPl8DH0fAnbdrCkg3fXHnb0POnMz6c2gq+d71cAitH9h9ZGPhBzLY8ji+vUHBym8ekRTMVL0XTQsxbXcjaLdyWr4UdEfte+8jbBLQHwxc2FHbYnsGiaDunJH3i0bx8FV4CLk+vUubMU/DWMfTzNMwMdDLaXqMiv2vu7q10KgN/lFW7LQbG34+8aAu92LiU2eOf0b/yS+J07eReurElFpkzONFGKJhlaUQCcdHo1u5L9vOiYOIaOYVp01m/jxHALg7EOSqaBLClYbpuyqKDo8SINNsUDD0dKjUr2+fVUYhqhoEuH5sYAe7uqqWp0YQkXqidN+uY1fvzm63Il2PeY5PT3t0VD00NO6oZ2eWdDQ1+5qGPbbtaX5kj9kWNxZv5cT6Qj8FjvnpHFspqSywtCbLXbR5cSojbS5mxMTh3SknOlGlXFcQwinWHPVDp0LpNMcmCom7OXNU6NJ+/19s62GkcakqqO3vNWcy8hnyTa6uPEhSSfns3QKjWisTBJQwV9dnPnf6AbIH1kpASpI+Ev7ieERjgE4AOZ/PdVHehp45NvZ/CHWjEkSECVEOsO0h2O0uqTSEASRErtvs7AcH8nmeQsAIaEDBBSfYSCgE+Cb7Mu6NMRD/ogH47HUUNhwiFJxgApQWpJkssaV2Y0fPp/iAHOjMcxpIau6QTVINLQUdUgjaEQ/dEe/gRI2pWNS4KICQAAAABJRU5ErkJggg==' />";
}
