/****************************************************************************
* Name:            qvsiti.js                                                *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvsiti(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currcontainerid="";
    var typesiteid=RYQUE.formatid("0WEBSITES000");
    var typecontainerid=RYQUE.formatid("0WEBCONTAIN0");
    var currexported="";
    var context="";
    var bbl_context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var flagfocus=false;
    var loadedsysidC="";
    var loadedsysidD="";
    var currfileid="";
    var dirattachments="";
    var urlattachments="";
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_WEBSITES",
        orderby:"DESCRIPTION,SYSID",
        columns:[
            {id:"SYSID", caption:"Codice", width:130, code:"CODE"},
            {id:"DESCRIPTION", caption:"Descrizione", width:200, code:"DESCRIPTION"}
        ],
        changerow:function(o,i){
            currsysid="";
            currexported="";
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            objtabs.enabled(4,false);
            oper_clone.enabled(0);
            oper_export.enabled(0);
            oper_download.visible(0);
            oper_delete.enabled(o.isselected());
            context="";
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            currsysid=d;
            objtabs.enabled(2,true);
            objtabs.enabled(3,true);
            objtabs.enabled(4,true);
            oper_clone.enabled(1);
            oper_export.enabled(1);
            oper_delete.enabled(1);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(2);
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    var offsety=80;
    var lbf_search=$(prefix+"lbf_search").rylabel({left:430, top:offsety, caption:"Ricerca"});
    offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()},100);
        }
    });
    offsety+=30;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:430,
        top:offsety,
        width:70,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=_likeescapize(txf_search.value());
            
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                },
                ready:function(){
                    if(done!=missing){done()}
                }
            });
        }
    });
    
    var oper_new=$(prefix+"oper_new").rylabel({
        left:430,
        top:210,
        width:70,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data={};
            data["DESCRIPTION"]="(nuovo sito)";
            data["TYPOLOGYID"]=typesiteid;
            data["NORMALWIDTH"]=1000;
            data["NARROWWIDTH"]=1;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_insert",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.SYSID;
                            flagopen=true;
                            objgridsel.splice(0, 0, newid);
                        }
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });

    var oper_clone=$(prefix+"oper_clone").rylabel({
        left:430,
        top:260,
        width:70,
        caption:"Clona&nbsp;",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["SITEID"]=currsysid;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"sites_clone",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.params["SITEID"];
                            flagopen=true;
                            objgridsel.splice(0, 0, newid);
                        }
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });
    
    var oper_export=$(prefix+"oper_export").rylabel({
        left:520,
        top:260,
        width:70,
        caption:"Esporta",
        button:true,
        click:function(o){
            winzProgress(formid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"sites_export",
                    "data":{
                        "SITEID":currsysid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            currexported=v.params["EXPORTED"];
                            oper_download.visible(1);
                        }
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });
    
    var oper_download=$(prefix+"oper_download").rylabel({
        left:660,
        top:260,
        width:70,
        caption:"Download",
        button:true,
        click:function(o){
            var h=_cambusaURL+"rysource/source_download.php?sessionid="+_sessionid+"&file="+_customizeURL+currexported;
            $("#winz-iframe").prop("src", h);
        }
    });
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:340,
        width:160,
        caption:"Elimina sito selezionato",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare il sito selezionato?",
                ok:"Elimina",
                confirm:function(){
                    winzProgress(formid);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"sites_delete",
                            "data":{
                                "SITEID":currsysid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){ 
                                    objgridsel.refresh();
                                }
                                winzTimeoutMess(formid, v.success, v.message);
                            }
                            catch(e){
                                winzClearMess(formid);
                                alert(d);
                            }
                        }
                    );
                }
            });
        }
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Identific."});
    var txname=$(prefix+"NAME").rytext({left:90, top:offsety, width:350, datum:"C", tag:"NAME"});
    offsety+=30;

    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:90, top:offsety, width:350, maxlen:100, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;
    
    $(prefix+"LB_HOMEPAGEID").rylabel({left:20, top:offsety, caption:"Radice"});
    $(prefix+"HOMEPAGEID").ryhelper({
        left:90, top:offsety, width:250, datum:"C", tag:"HOMEPAGEID", formid:formid, table:"QW_WEBCONTAINERS", titlecode:"HLP_SELPRIMARYCONTAINER", 
        open:function(o){
            o.where("SITEID='"+currsysid+"' AND REFOBJECTID=''");
        }
    });
    
    $(prefix+"LB_DEFAULTID").rylabel({left:380, top:offsety, caption:"Predefinita"});
    $(prefix+"DEFAULTID").ryhelper({
        left:470, top:offsety, width:250, datum:"C", tag:"DEFAULTID", formid:formid, table:"QW_WEBCONTENTS", titlecode:"HLP_SELDEFAULTPAGE", 
        open:function(o){
            o.where("(SITEID='"+currsysid+"' OR SITEID='')");
        }
    });
    
    offsety+=30;
    $(prefix+"LB_NORMALWIDTH").rylabel({left:20, top:offsety, caption:"Normale"});
    $(prefix+"NORMALWIDTH").rynumber({left:90, top:offsety, width:80, numdec:0, datum:"C", tag:"NORMALWIDTH"});

    $(prefix+"LB_NARROWWIDTH").rylabel({left:200, top:offsety, caption:"Stretta"});
    $(prefix+"NARROWWIDTH").rynumber({left:260, top:offsety, width:80, numdec:0, datum:"C", tag:"NARROWWIDTH"});

    $(prefix+"LB_LOGSTATISTICS").rylabel({left:470, top:offsety, caption:"Statistiche"});
    $(prefix+"LOGSTATISTICS").rycheck({left:560, top:offsety, datum:"C", tag:"LOGSTATISTICS"});
    
    $(prefix+"LB_PROTECTED").rylabel({left:620, top:offsety, caption:"Protezione"});
    $(prefix+"PROTECTED").rycheck({left:702, top:offsety, datum:"C", tag:"PROTECTED"});
    
    offsety+=30;
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:90, top:offsety, width:630, datum:"C", tag:"TAG"});
    
    offsety+=30;
    $(prefix+"LB_GLOBALSTYLE").rylabel({left:20, top:offsety, caption:"Stile globale (CSS)"});offsety+=24;
    $(prefix+"GLOBALSTYLE").ryedit({left:20, top:offsety, width:694, height:300, flat:true, datum:"C", tag:"GLOBALSTYLE"});

    offsety+=320;
    $(prefix+"LB_GLOBALSCRIPT").rylabel({left:20, top:offsety, caption:"Script globale"});offsety+=24;
    $(prefix+"GLOBALSCRIPT").ryedit({left:20, top:offsety, width:694, height:300, flat:true, datum:"C", tag:"GLOBALSCRIPT"});
    
    offsety+=320;
    $(prefix+"LB_GLOBALHEAD").rylabel({left:20, top:offsety, caption:"Head aggiuntivo"});offsety+=24;
    $(prefix+"GLOBALHEAD").ryedit({left:20, top:offsety, width:694, height:300, flat:true, datum:"C", tag:"GLOBALHEAD"});
    
    var oper_indicize=$(prefix+"oper_indicize").rylabel({
        left:470,
        top:60,
        width:120,
        caption:"Indicizza pagine",
        button:true,
        click:function(o){
            winzProgress(formid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pages_indicize",
                    "data":{
                        "SITEID":currsysid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });

    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:640,
        top:60,
        width:70,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=txdescr.value();
            var data=qv_mask2object(formid, "C", currsysid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ RYWINZ.modified(formid, 0) }
                        objgridsel.dataload();
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    if(done!=missing){done()}
                }
            );
        }
    });

    offsety+=320;
    var operc_top=$(prefix+"operc_top").rylabel({
        left:20,
        top:offsety,
        caption:"Torna all'inizio",
        button:true,
        click:function(o){
            $("#window_"+formid+" .window_content").animate({ scrollTop:0}, "slow");
        }
    });
    offsety+=50;
    $(prefix+"contextbottom").css({"position":"absolute", "left":0, "top":offsety});

    
    // DEFINIZIONE TAB CONTENITORI
    offsety=90;
    var lb_frames_context=$(prefix+"frames_context").rylabel({left:20, top:50, caption:""});

    var operf_refresh=$(prefix+"operf_refresh").rylabel({
        left:640,
        top:80,
        width:70,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            gridframes.where("SITEID='"+currsysid+"'");
            gridframes.query();
        }
    });
    offsety+=35;
    
    gridframes=$(prefix+"gridframes").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_WEBCONTAINERSJOIN",
        orderby:"ORDINATORE,DESCRIPTION",
        columns:[
            {id:"ORDINATORE", caption:"", width:30, type:"0"},
            {id:"DESCRIPTION", caption:"Descrizione",width:300, code:"DESCRIPTION"},
            {id:"CONTENT", caption:"Contenuto",width:300, code:"CONTENT"}
        ],
        beforechange:function(o, i, n){
            if(qv_changerowmanagement(formid, o, n)){return false;}
        },
        changerow:function(o,i){
            currcontainerid="";
            qv_maskclear(formid, "F");
            qv_maskenabled(formid, "F", 0);
            operf_update.enabled(0);
            operf_unsaved.visible(0);
            operf_remove.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o,d){
            currcontainerid=d;
            operf_remove.enabled(1);
            RYQUE.query({
                sql:"SELECT * FROM QW_WEBCONTAINERS WHERE SYSID='"+currcontainerid+"'",
                ready:function(v){
                    // ABILITAZIONE TAB CONTENITORI
                    qv_maskenabled(formid, "F", 1);
                    operf_update.enabled(1);
                    // CARICAMENTO TAB CONTENITORI
                    qv_object2mask(formid, "F", v[0]);
                    operf_unsaved.visible(0);
                    loadedsysidD=currsysid;
                    if(flagfocus){
                        flagfocus=false;
                        castFocus(prefix+"F_DESCRIPTION");
                    }
                }
            });
        },
        enter:function(){
            $("#window_"+formid+" .window_content").animate({ scrollTop: $(document).height() }, "slow");
            castFocus(prefix+"operf_top");
        }
    });
    offsety=410;

    var operf_add=$(prefix+"operf_add").rylabel({
        left:20,
        top:offsety,
        caption:"Aggiungi contenitore",
        width:140,
        button:true,
        click:function(o){
            winzProgress(formid);
            var stats=[];
            var istr=0;
            if(RYWINZ.modified(formid)){
                // ISTRUZIONE DI SALVATAGGIO DEL CONTENITORE MODIFICATO
                var datasave=qv_mask2object(formid, "F", currcontainerid);
                stats[istr++]={
                    "function":"objects_update",
                    "data":datasave
                };
            }
            // ISTRUZIONE DI INSERIMENTO NUOVO CONTENITORE
            var data = new Object();
            data["DESCRIPTION"]="(nuovo contenitore)";
            data["TYPOLOGYID"]=typecontainerid;
            data["SITEID"]=currsysid;
            stats[istr++]={
                "function":"objects_insert",
                "data":data
            };
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "program":stats
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            // FORZO LA RILETTURA DEI VINCOLI
                            var newid=v.SYSID;
                            RYWINZ.modified(formid, 0);
                            flagfocus=true;
                            gridframes.splice(0, 0, newid);
                        }
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });

    var operf_unsaved=$(prefix+"operf_unsaved").rylabel({left:300, top:offsety, caption:"<span style='color:red;'>Modificato - Non salvato<span>"});
    operf_unsaved.visible(0);
    
    var operf_remove=$(prefix+"operf_remove").rylabel({
        left:570,
        top:offsety,
        width:140,
        caption:"Rimuovi contenitore",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:RYBOX.babels("MSG_DELETECONTAINER"),
                confirm:function(){
                    winzProgress(formid);
                    RYWINZ.modified(formid, 0);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"objects_delete",
                            "data":{
                                "SYSID":currcontainerid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    gridframes.refresh();
                                }
                                winzTimeoutMess(formid, v.success, v.message);
                            }
                            catch(e){
                                winzClearMess(formid);
                                alert(d);
                            }
                        }
                    );
                }
            });
        }
    });
    offsety+=40;

    var operf_update=$(prefix+"operf_update").rylabel({
        left:20,
        top:offsety,
        width:140,
        caption:"Salva contenitore",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            var data=qv_mask2object(formid, "F", currcontainerid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            gridframes.dataload();
                            operf_unsaved.visible(0);
                        }
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    if(done!=missing){done()}
                }
            );
        }
    });
    
    offsety+=40;
    $(prefix+"LBF_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    $(prefix+"F_DESCRIPTION").rytext({left:100, top:offsety, width:335, datum:"F", tag:"DESCRIPTION",
        changed:function(){
            operf_unsaved.visible(1);
        }
    });
    $(prefix+"LBF_ORDINATORE").rylabel({left:470, top:offsety, caption:"Ordinatore"});
    $(prefix+"F_ORDINATORE").rynumber({left:545, top:offsety, width:60, numdec:0, datum:"F", tag:"ORDINATORE",
        changed:function(){
            operf_unsaved.visible(1);
        }
    });
    $(prefix+"LBF_CURRENTPAGE").rylabel({left:634, top:offsety, caption:"Corrente"});
    $(prefix+"F_CURRENTPAGE").rycheck({left:701, top:offsety, datum:"F", tag:"CURRENTPAGE",
        assigned:function(){
            operf_unsaved.visible(1);
        }
    });
    
    offsety+=30;
    $(prefix+"LBF_FUNCTIONNAME").rylabel({left:20, top:offsety, caption:"Funzione"});
    $(prefix+"F_FUNCTIONNAME").rytext({left:100, top:offsety, width:180, datum:"F", tag:"FUNCTIONNAME",
        changed:function(){
            operf_unsaved.visible(1);
        }
    });
    $(prefix+"LBF_CLASSES").rylabel({left:395, top:offsety, caption:"Classi"});
    $(prefix+"F_CLASSES").rytext({left:469, top:offsety, width:250, datum:"F", tag:"CLASSES",
        changed:function(){
            operf_unsaved.visible(1);
        }
    });

    offsety+=30;
    $(prefix+"LBF_REFOBJECTID").rylabel({left:20, top:offsety, caption:"Genitore"});
    $(prefix+"F_REFOBJECTID").ryhelper({
        left:100, top:offsety, width:250, datum:"F", tag:"REFOBJECTID", formid:formid, table:"QW_WEBCONTAINERS", title:"Contenitore padre",
        open:function(o){
            o.where("SITEID='"+currsysid+"'");
        },
        assigned:function(){
            operf_unsaved.visible(1);
        }
    });

    $(prefix+"LBF_CONTENTID").rylabel({left:395, top:offsety, caption:"Contenuto"});
    $(prefix+"F_CONTENTID").ryhelper({
        left:469, top:offsety, width:250, datum:"F", tag:"CONTENTID", formid:formid, table:"QW_WEBCONTENTS", title:"Scelta contenuto",
        open:function(o){
            o.where("SYSID<>'"+currcontainerid+"' AND (SITEID='"+currsysid+"' OR SITEID='')");
        },
        assigned:function(){
            operf_unsaved.visible(1);
        }
    });

    offsety+=30;
    $(prefix+"LBF_FRAMESTYLE").rylabel({left:20, top:offsety, caption:"Stile (JSON)"});offsety+=24;
    $(prefix+"F_FRAMESTYLE").ryedit({left:20, top:offsety, width:692, height:300, flat:true, datum:"F", tag:"FRAMESTYLE",
        changed:function(){
            operf_unsaved.visible(1);
        }
    });

    offsety+=320;
    $(prefix+"LBF_FRAMESCRIPT").rylabel({left:20, top:offsety, caption:"Script"});offsety+=24;
    $(prefix+"F_FRAMESCRIPT").ryedit({left:20, top:offsety, width:692, height:300, flat:true, datum:"F", tag:"FRAMESCRIPT",
        changed:function(){
            operf_unsaved.visible(1);
        }
    });
    
    offsety+=320;
    var operf_top=$(prefix+"operf_top").rylabel({
        left:20,
        top:offsety,
        caption:"Torna all'elenco",
        button:true,
        click:function(o){
            $("#window_"+formid+" .window_content").animate({ scrollTop:0}, "slow");
            castFocus(prefix+"gridframes");
        }
    });
    offsety+=50;
    $(prefix+"framesbottom").css({"position":"absolute", "left":0, "top":offsety});

    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS",
        {
            changerow:function(){
                currfileid="";
                oper_favicon.enabled(0);
                $(prefix+"PREVIEW").css({display:"none"});
                $(prefix+"PREVIEW").html("");
            },
            solveid:function(id, d){
                currfileid=id;
                var exten=_getextension(d["IMPORTNAME"]);
                var p=dirattachments+d["SUBPATH"]+d["FILEID"]+"."+exten;
                if(exten.toLowerCase().match(/(jpg|jpeg|gif|png|svg)/)){
                    oper_favicon.enabled(1);
                    $(prefix+"PREVIEW").html("<img src='"+_cambusaURL+"/phpthumb/phpThumb.php?h=80&src="+p+"' style='border:1px solid silver;'>");
                    $(prefix+"PREVIEW").css({display:"block"});
                }
            }
        }
    );
    
    $(prefix+"filemanager").append("<div id='"+formid+"oper_favicon' babelcode='SITE_USEASFAVICON'></div>");
    $(prefix+"filemanager").append("<div id='"+formid+"oper_removefavicon' babelcode='SITE_REMOVEFAVICON'></div>");
    $(prefix+"filemanager").append("<div id='"+formid+"PREVIEW'></div>");
    
    // USA COME FAVICON
    var oper_favicon=$(prefix+"oper_favicon").rylabel({
        left:20,
        top:380,
        caption:"Usa come favicon",
        formid:formid,
        button:true,
        click:function(o){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pages_iconize",
                    "data":{
                        "OPER":"+f",
                        "SYSID":currsysid,
                        "FILEID":currfileid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });
    oper_favicon.enabled(0);
    
    // RIMUOVI FAVICON
    var oper_removefavicon=$(prefix+"oper_removefavicon").rylabel({
        left:310,
        top:380,
        caption:"Rimuovi favicon",
        formid:formid,
        button:true,
        click:function(o){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pages_iconize",
                    "data":{
                        "OPER":"-f",
                        "SYSID":currsysid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                }
            );
        }
    });
    
    $(prefix+"PREVIEW").css({position:"absolute", left:20, top:420, display:"none"});
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
        tabs:[
            {title:"Selezione", code:"SELECTION"},
            {title:"Contesto", code:"CONTEXT"},
            {title:"Contenitori", code:"CONTAINERS"},
            {title:"Documenti", code:"DOCUMENTS"}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedsysidC="";
                    }
                });
            }
            else if(p==3){
                // PROVENGO DAI DETTAGLI
                flagsuspend=qv_changemanagement(formid, objtabs, operf_update, {
                    abandon:function(){
                        loadedsysidD="";
                    }
                });
            }
            if(i==1){
                loadedsysidC="";
                loadedsysidD="";
            }
            else if(i==2){
                if(currsysid==loadedsysidC){
                    flagsuspend=true;
                }
            }
            else if(i==3){
                if(currsysid==loadedsysidD){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 1:
                    objgridsel.dataload();
                    break;
                case 2:
                    // CARICAMENTO DEL CONTESTO
                    if(window.console&&_sessioninfo.debugmode){console.log("Caricamento contesto: "+currsysid)}
                    // RESET MASCHERA
                    qv_maskclear(formid, "C");
                    RYQUE.query({
                        sql:"SELECT * FROM QW_WEBSITES WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            qv_object2mask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysidC=currsysid;
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO CONTENITORI
                    lb_frames_context.caption(bbl_context.replace("{1}", context));
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            lb_frames_context.caption(bbl_context.replace("{1}", context));
                            loadedsysidC=currsysid;
                            operf_refresh.engage();
                        }
                    });
                    break;
                case 4:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, bbl_context.replace("{1}", context), typesiteid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            filemanager.caption(bbl_context.replace("{1}", context));
                        }
                    });
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    objtabs.enabled(4,false);
    
    // INIZIALIZZAZIONE FORM
    RYBOX.babels({
        "HLP_SELPRIMARYCONTAINER":"Selezione contenitore principale",
        "HLP_SELDEFAULTPAGE":"Selezione pagina predefinita",
        "MSG_DELETECONTAINER":"Eliminare il contenitore selezionato?"
    });
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            bbl_context=RYBOX.babels("BABEL_CONTEXT");
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"files_info",
                    "data":{}
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        dirattachments=v.params["DIRATTACH"];
                        urlattachments=v.params["URLATTACH"];
                    }catch(e){}
                    txf_search.focus();
                    setTimeout( 
                        function(){ 
                            oper_refresh.engage(
                                function(){
                                    winzClearMess(formid);
                                    txf_search.focus();
                                }
                            ) 
                        }, 100
                    );
                }
            );
        }
    );
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xfocus:"NOME", xengage:oper_contextengage, files:3} );
}

