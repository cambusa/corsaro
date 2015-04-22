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
RYJAX={
    xtoj:function(xml, options, missing){
        try{
            if(options==missing)options={};
            if(options.tags==missing)options.tags=[];
            if(options.doublequote==missing)options.doublequote=true;
            var v=[];
            var exb=new RegExp(" *$", "gm");
            var exc;
            if(options.doublequote)
                exc=/(\w+)="([^"]*)"/gm;
            else
                exc=/(\w+)='([^']*)'/gm;
            var exr=[];
            if(options.tags.length>0){
                for(var i=0; i<options.tags.length; i++){
                    if(i<options.tags.length-1)
                        exr[i]=new RegExp("<"+options.tags[i]+"(>| [^>]*)(.+?)</"+options.tags[i]+">", "gm");
                    else
                        exr[i]=new RegExp("<"+options.tags[i]+"(>| [^>]*)", "gm");
                }
                subxtoj(xml, 0, v);
            }
        }
        catch(e){
            if(window.console){console.log(e.message)}
            var v=[];
        }
        return v;
        function subxtoj(subxml, level, subarr){
            var row=0,s,t;
            while(t=exr[level].exec(subxml)){
                subarr[row]={};
                while(s=exc.exec(t[1])){
                    subarr[row][s[1].toUpperCase()]=s[2].replace(exb, "");
                }
                if(level<options.tags.length-1){
                    subarr[row]["__DATA__"]=[];
                    subxtoj(t[2], level+1, subarr[row]["__DATA__"]);
                }
                row+=1;
            }
        }
    },
    jtox:function(json, options, missing){
        try{
            if(options==missing){options={}}
            if(options.root==missing){options.root="xml"}
            
            // CREO UN XML VUOTO
            var objx=$.parseXML("<"+options.root+"></"+options.root+">");
            
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
                    if(j[i] instanceof Array){
                        var e=objDoc.createElement(i);
                        for(var k in j[i]){
                            if( isNaN(k) ){
                                var f=objDoc.createElement("elem");
                                f.setAttribute(k, j[i][k]);
                                e.appendChild(f);
                            }
                            else if(typeof(j[i])=="object"){
                                var f=objDoc.createElement("elem");
                                subjtox(objDoc, f, j[i][k]);
                                e.appendChild(f);
                            }
                        }
                        x.appendChild(e);
                    }
                    else if(typeof(j[i])=="object"){
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
}
