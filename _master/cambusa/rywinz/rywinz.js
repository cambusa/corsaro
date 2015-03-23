/****************************************************************************
* Name:            rywinz.js                                                *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function ryWinz(missing){
    var propicontop=40;
    var objscripts={};
    var objmodules={};
    // La addform viene lanciata dopo la newform
    // e serve a popolare le collezioni degli oggetti caricati
    this.addform=function(o){
        var relid,href;
        var formid=_openingid;
        var name=_openingname;
        o.controls=new Object();
        o.classform=name;
        o.jqxhr=false;
        o.timeid=false;
        o.opens=0;
        // EVENTO DI STOP DELLE RICHIESTE
        $("#stop_"+formid).click(
            function(){
                winzAbort(formid);
            }
        );
        _globalforms[formid]=o;
        objscripts[name]=true;
        $("#hanger_"+formid+" div").each(function(i){
            relid=$(this).attr("relid");
            if (typeof relid !== "undefined"){ 
                $(this).attr({"id":formid+relid}).prop("parentid",formid);
                o.controls[formid+relid]=relid;
            }
        });
        if(window.console&&_sessioninfo.debugmode)console.log("Oggetti in apertura: "+_objectlength(globalobjs));
        $("#hanger_"+formid+" a").each(function(i){
            href=$(this).attr("href");
            if (typeof href !== "undefined"){ 
                if(href.substr(0,1)=="#"){
                    $(this).attr({"href":"#"+formid+href.substr(1)});
                }
            }
        });
        return formid;
    }
    this.removeform=function(id, done){
        try{
            winzAbort(id);
            var grids=[];
            // ELIMINO TUTTI I CONTROLLI TRANNE I GRID CHE MEMORIZZO
            var vK=_globalforms[id].controls;
            for(var v in vK){
                if(_isset(globalobjs[v])){
                    if(globalobjs[v].type=="grid")
                        grids.push(v);
                    else
                        delete globalobjs[v];
                }
                else{
                    delete globalobjs[v];
                }
            }
            delete vK;
            // RIMOZIONE DEI GRID CON DISPOSE
            winzDisposeGrid(grids,
                function(){
                    if(window.console&&_sessioninfo.debugmode)console.log("Closing objects: "+_objectlength(globalobjs));
                    delete _globalforms[id];
                    if(done!=missing){
                        setTimeout(function(){done()});
                    }
                }
            );
        }
        catch(e){
            if(window.console)console.log(e.message);
            setTimeout(function(){done()});
        }
    }
    this.forms=function(n){
        return _globalforms[n];
    }
    this.modified=function(n,v){
        if(v==missing){
            return $("#window_"+n).prop("modified");
        }
        else{
            v=_bool(v);
            $("#window_"+n).prop("modified", v);
            if(v==0){ // Resetto lo stato di modifica dei singoli controlli
                var o=_globalforms[n];
                for(var k in o.controls){
                    var datum=$("#"+k).prop("datum");
                    if( !_ismissing(datum) ){
                        $("#"+k).prop("modified", 0);
                    }
                }
            }
        }
    }
    this.busy=function(n,v){
        if(v==missing)
            return $("#window_"+n).prop("busy");
        else
            $("#window_"+n).prop("busy",_bool(v));
    }
    this.newform=function(settings){
        var propid=createid();
        var propname="";
        var proppath="";
        var proptitle="";
        var propdesk=false;
        var propicon="_images/default";
        
        if(settings.name!=missing){propname=settings.name}
        if(settings.id!=missing){propid=settings.id}
        if(propname==""){
            propname=propid;
        }
        if(settings.path!=missing){
            proppath=settings.path;
            proppath=proppath.replace(/@cambusa\//gi,_cambusaURL);
            proppath=proppath.replace(/@customize\//gi,_customizeURL);
        }
        if(settings.title!=missing){proptitle=settings.title}
        if(settings.desk!=missing){propdesk=settings.desk}
        if(settings.icon!=missing){
            propicon=settings.icon;
            propicon=propicon.replace(/@cambusa\//gi,_cambusaURL);
            propicon=propicon.replace(/@customize\//gi,_customizeURL);
        }
        
        proptitle=proptitle.replace(/[']/gi, "&acute;");
        
        if(propname!="" && propid!=""){
            var t,cn;
            if(propdesk){
                t="<a id='icon_desk_"+propid+"' class='abs icon' style='left:30px;top:"+propicontop+"px;' href='#icon_dock_"+propid+"'><img src='"+propicon+"_32.png' />"+proptitle+"</a>";
                $("#desktop").append(t);
                propicontop+=80;
            }
            t="";
            t+="<div id='window_"+propid+"' class='abs window'>";
            t+="    <div class='abs window_inner'>";
            t+="        <div id='top:"+propid+"' class='window_top'>";
            t+="            <span class='float_left'><img src='"+propicon+"_16.png' />"+proptitle+"</span>";
            t+="            <span class='float_right'><a href='#' class='window_min'></a><a id='resize:"+propid+"' href='#' class='window_resize'></a><a href='#icon_dock_"+propid+"' class='window_close'></a></span>";
            t+="        </div>";
            t+="        <div class='abs window_content'>";
            t+="            <div id='main_"+propid+"' class='window_main'>";
            t+="                <div id='hanger_"+propid+"' class='window_hanger'></div>";
            t+="            </div>";
            t+="        </div>";
            t+="        <div id='dither_"+propid+"' class='winz_dither'></div>";
            t+="        <div id='message_"+propid+"' class='abs window_bottom'></div>";
            t+="        <a id='stop_"+propid+"' class='winz_stop' title='Stop'></a>";
            t+="    </div>";
            t+="    <span class='abs ui-resizable-handle ui-resizable-se'></span>";
            t+="</div>";
            $("#desktop").append(t);

            t="";
            t+="<li id='icon_dock_"+propid+"'>";
            t+="    <a href='#window_"+propid+"'>";
            t+="        <img src='"+propicon+"_22.png' />";
            t+="        "+proptitle;
            t+="    </a>";
            t+="</li>";
            $("#dock").append(t);
            
            $("#window_"+propid).prop("modified",0).prop("busy",0);
            
            // APERTURA FORM
            var x="#icon_dock_"+propid;
            var y="#window_"+propid;
            
            // Show the taskbar button.
            if ($(x).is(':hidden')) {
                $(x).remove().appendTo('#dock');
                $(x).show('fast');
            }

            // Bring window to front.
            JQD.util.window_flat();
            $(y).addClass('window_stack').show();

            if(y=="#window_postman"){
                // LO SPOSTO UN PO'
                $(y).css({left:60, top:80});
            }
            
            if( (y!="#window_rudder" && y!="#window_postman") || _mobiledetected )
                JQD.util.window_resize(y);  // Massimizzo
            JQD.util.clear_active();
            
            // GESTIONE EVENTI
            $("#window_"+propid).resize(
            	function(){
                    setTimeout(function(){raiseResize(propid)});
                }
            );
            // CARICAMENTO SCHELETRO
            $.post(proppath+propname+".php", {id:propid,name:propname},
                function(d){
                    try{
                        $("#hanger_"+propid).html(d);
                        // CARICAMENTO CODICE
                        if(_ismissing(objscripts[propname])){
                            _openingid=propid;
                            _openingname=propname;
                            $.getScript(proppath+propname+".js")
                                .done(function(){
                                    if(window.console&&_sessioninfo.debugmode)console.log(_openingparams);
                                    eval("new class_"+propname+"("+_openingparams+")");
                                    if(settings.initialize!=missing){
                                        settings.initialize();
                                    }
                                    // SCATENO LA LOAD
                                    raiseLoad(propid);
                                    // SCATENO LA PRIMA RESIZE
                                    setTimeout(function(){raiseResize(propid)});
                                    _openingid="";
                                    _openingname="";
                                    _openingparams="({})";
                                })
                                .fail(function(jqxhr, settings, exception){
                                    _openingid="";
                                    _openingname="";
                                    alert(exception);
                                });
                        }
                        else{
                            if(window.console&&_sessioninfo.debugmode)console.log(propname+".js already loaded.");
                            _openingid=propid;
                            _openingname=propname;
                            if(window.console&&_sessioninfo.debugmode)console.log(_openingparams);
                            eval("new class_"+propname+"("+_openingparams+")");
                            if(settings.initialize!=missing){
                                settings.initialize();
                            }
                            // SCATENO LA LOAD
                            raiseLoad(propid);
                            // SCATENO LA PRIMA RESIZE
                            setTimeout(function(){raiseResize(propid)});
                            _openingid="";
                            _openingname="";
                            _openingparams="({})";
                        }
                    }
                    catch(e){}
                }
            );            
        }
        else{
            if(window.console)console.log("Unresolved form");
        }
    }
    this.loadmodule=function(id, path, ready){
        if(_ismissing(objmodules[id])){
            objmodules[id]=0;
            $.getScript(path)
                .done(function(){
                    if(window.console&&_sessioninfo.debugmode)console.log("'"+id+"' loaded");
                    objmodules[id]=1;
                    if(ready){
                        ready();
                    }
                })
                .fail(function(jqxhr, settings, exception){
                    alert(exception);
                });

        }
        else if(ready){
            ready();
        }
    }
    this.shell=function(params){
        try{
            _openingparams=_stringify(params);
            if(params.controls!=missing){
                if(!params.controls){
                    params.initialize=function(){
                        $("#window_rudder div.window_top").css({"display":"none"});
                        $("#message_rudder").css({"display":"none"});
                        $("#window_rudder>span.ui-resizable-handle").css({"right":"-50px"});
                        $("#window_rudder div.window_content").css({"top":0, "bottom":0});
                    }
                }
            }
            RYWINZ.newform(params);
            if(params.maximize!=missing){
                if(params.maximize){
                    JQD.util.window_resize("#window_rudder");
                }
            }
        }
        catch(e){}
    }
    function createid(){
        _winzprogrid++;
        return "_form"+(_winzprogrid)+"_";
    }
}
