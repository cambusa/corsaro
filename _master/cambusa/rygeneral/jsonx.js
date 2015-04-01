/****************************************************************************
* Name:            jsonx.js                                                 *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
*                  JSON <=> XML                                             *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function xtoj(xml, tag, doublequote){
    try{
        var v=[];
        var row=0,r,s,exc;
        var exb=new RegExp(" *$", "gm");
        var exr=new RegExp("<"+tag+"(>| [^>]*)", "gm");
        if(doublequote)
            exc=/(\w+)="([^"]*)"/gm;
        else
            exc=/(\w+)='([^']*)'/gm;

        var t=xml.match(exr);
        for(r in t){
            v[row]={};
            while(s=exc.exec(t[r])){
                v[row][s[1].toUpperCase()]=s[2].replace(exb, "");
            }
            row+=1;
        }
    }
    catch(e){
        if(window.console){console.log(e.message)}
        var v=[];
    }
    return v;
}

function xxtoj(xml, tag, sub, doublequote){
    try{
        var v=[];
        var row=0,s,t,exc;
        var exb=new RegExp(" *$", "gm");
        var exr=new RegExp("<"+tag+"(>| [^>]*)(.+?)</"+tag+">", "gm");
        if(doublequote)
            exc=/(\w+)="([^"]*)"/gm;
        else
            exc=/(\w+)='([^']*)'/gm;
        while(t=exr.exec(xml)){
            v[row]={};
            while(s=exc.exec(t[1])){
                v[row][s[1].toUpperCase()]=s[2].replace(exb, "");
            }
            v[row]["__DATA__"]=xtoj(t[2], sub, doublequote);
            row+=1;
        }
    }
    catch(e){
        if(window.console){console.log(e.message)}
        var v=[];
    }
    return v;
}

function jtox(json, opt, missing){
    try{
        var root="xml";
        if(opt==missing){opt={}}
        if(opt.root!=missing){root=opt.root}
        
        // CREO UN XML VUOTO
        var objx=$.parseXML("<"+root+"></"+root+">");
        
        // LO CARICO RICORSIVAMENTE CON I DATI DEL DOCUMENTO JSON
        subjtox(objx, objx.firstChild, json);
        
        // RESTITUISCO IL DOCUMENTO XML
        return objx.firstChild.outerHTML;
    }
    catch(e){
        if(window.console){console.log(e.message)}
        return "";
    }
    function subjtox(objDoc, x, j){
        for(var i in j){
            if( isNaN(i) ){
                if(typeof(j[i])=="object"){
                    var e=objDoc.createElement(i);
                    subjtox(objDoc, e, j[i]);
                    x.appendChild(e);
                }
                else{
                    x.setAttribute(i, j[i]);
                }
            }
            else{
                if(typeof(j[i])=="object"){
                    var e=objDoc.createElement("elem");
                    subjtox(objDoc, e, j[i]);
                    x.appendChild(e);
                }
            }
        }
    }
}