/****************************************************************************
* Name:            qvpagine.js                                              *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvpagine(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currsysid="";
    var currsiteid="";
    var currparentset="";
    var currtypologyid=RYQUE.formatid("0WEBCONTENTS");
    var currsetframes="";
    var currframeid="";
    var currsetrelated="";
    var currselectedid="";
    var context="";
    var bbl_context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysidX="";
    var loadedsysidR="";
    var currfileid="";
    var dirattachments="";
    var urlattachments="";
    var urlapplications="";
    var sitename="";
    var fsitename="";
    var currbrowser;
    var currparentid="";
    var sospendirefresh=false;
   
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:80,
        width:400,
        height:400,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_WEBCONTENTSBROWSER",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:250, code:"DESCRIPTION"},
            {id:"SITE", caption:"Sito", width:200, code:"SITE"},
            {id:"SYSID", caption:"Codice", width:200, code:"CODE"}
        ],
        changerow:function(o,i){
            currsysid="";
            currsetframes="";
            currsetrelated="";
            objtabs.enabled(2,false);
            objtabs.enabled(3,false);
            objtabs.enabled(4,false);
            oper_delete.enabled(o.isselected());
            context="";
            $(prefix+"previewinner").html("");
            $(prefix+"pagepreview").hide();
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
            oper_delete.enabled(1);
            if(flagopen){
                flagopen=false;
                objtabs.currtab(2);
            }
            else{
                refreshpreview();
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
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_site").rylabel({left:430, top:offsety, caption:"Sito"});
    offsety+=20;
    var txf_site=$(prefix+"txf_site").ryhelper({left:430, top:offsety, width:300, 
        formid:formid, table:"QW_WEBSITES", titlecode:"HLP_SELSITE", multiple:false,
        open:function(o){
            o.where("");
        },
        select:"NAME",
        onselect:function(o, d){
            currsiteid=d["SYSID"];
            fsitename=d["NAME"];
            refreshselection();
        },
        clear:function(){
            currsiteid="";
            fsitename="";
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_parent").rylabel({left:430, top:offsety, caption:"Genitore"});
    offsety+=20;
    var txf_parent=$(prefix+"txf_parent").ryhelper({left:430, top:offsety, width:300, 
        formid:formid, table:"QW_WEBCONTENTS", titlecode:"HLP_SELPARENT", multiple:false,
        open:function(o){
            o.where("SYSID<>'"+currsysid+"' AND SETRELATED IN (SELECT PARENTID FROM QVSELECTIONS)");
        },
        select:"SETRELATED",
        onselect:function(o, d){
            currparentset=d["SETRELATED"];
            refreshselection();
        },
        clear:function(){
            currparentset="";
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_classe").rylabel({left:430, top:offsety, caption:"Classe"});
    offsety+=20;
    var txf_classe=$(prefix+"txf_classe").ryhelper({left:430, top:offsety, width:300, 
        formid:formid, table:"QW_CLASSICONTENUTO", titlecode:"HLP_SELCLASS", multiple:false,
        open:function(o){
            o.where("");
        },
        onselect:function(){
            refreshselection();
        },
        clear:function(){
            refreshselection();
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
            var classeid=txf_classe.value();

            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' OR SYSID='[=SYSID]')";
            }
            if(currsiteid!=""){
                if(q!=""){q+=" AND "}
                q+="(SITEID='"+currsiteid+"' OR SITEID='')";
            }
            if(currparentset!=""){
                if(q!=""){q+=" AND "}
                q+="SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='"+currparentset+"')";
            }
            if(classeid!=""){
                if(q!=""){q+=" AND "}
                q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+classeid+"')";
            }
            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t,
                    "SYSID":t
                },
                ready:function(){
                    if(done!=missing){done()}
                }
            });
        }
    });
    var oper_reset=$(prefix+"oper_reset").rylabel({
        left:650,
        top:offsety,
        caption:"Pulisci",
        width:70,
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_site.clear();
            txf_parent.clear();
            txf_classe.clear();
            sospendirefresh=false;
            refreshselection();
        }
    });
    
    offsety+=50;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:430,
        top:offsety,
        width:70,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pages_insert",
                    "data":{
                        "SITEID":currsiteid
                    }
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
    
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:430,
        top:450,
        width:120,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "arrows");
        }
    });
    
    $(prefix+"previewinner").addClass("winz-zoom75");
    $(prefix+"pagepreview").css({"position":"absolute", "left":740, "top":70, "width":600, "border-left":"1px solid red", "padding-left":8, "display":"none"});

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Identific."});
    var txname=$(prefix+"NAME").rytext({left:90, top:offsety, width:350, datum:"C", tag:"NAME"});
    
    $(prefix+"LB_SITEID").rylabel({left:470, top:offsety, caption:"Sito"});
    var tx_siteid=$(prefix+"SITEID").ryhelper({
        left:500, top:offsety, width:140, datum:"C", tag:"SITEID", formid:formid, table:"QW_WEBSITES", titlecode:"HLP_SELSITE",
        open:function(o){
            o.where("");
        },
        select:"NAME",
        onselect:function(o, d){
            sitename=d["NAME"];
        },
        clear:function(){
            sitename="";
        }
    });
    
    offsety+=30;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Titolo"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:90, top:offsety, width:350, maxlen:200, datum:"C", tag:"DESCRIPTION"});

    offsety+=30;
    $(prefix+"LB_ABSTRACT").rylabel({left:20, top:offsety, caption:"Abstract"});offsety+=24;
    $(prefix+"ABSTRACT").ryedit({left:20, top:offsety, width:415, height:116, flat:true, datum:"C", tag:"ABSTRACT"});
    offsety+=140;
    
    var objclassi=$(prefix+"CLASSI").ryselections({"left":470, "top":111, "height":140, 
        "title":"Classi di appartenenza",
        "titlecode":"BELONGING_CLASS",
        "formid":formid, 
        "subid":"C",
        "table":"QW_CLASSICONTENUTO", 
        "where":"",
        "upward":1,
        "parenttable":"QVARROWS", 
        "parentfield":"SYSID",
        "selectedtable":"QVOBJECTS"
    });
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:90, top:offsety, width:350, datum:"C", tag:"TAG"});
    $(prefix+"LB_AUXTIME").rylabel({left:470, top:offsety, caption:"Data"});
    $(prefix+"AUXTIME").rydate({left:520, top:offsety, width:100, datum:"C", tag:"AUXTIME"});
    $(prefix+"LB_SCOPE").rylabel({left:640, top:offsety, caption:"Visibile"});
    var chk_scope=$(prefix+"SCOPE").rycheck({left:700, top:offsety, datum:"C"});
    offsety+=30;
    
    $(prefix+"LB_CONTENTTYPE").rylabel({left:20, top:offsety, caption:"Tipo"});
    var tx_contenttype=$(prefix+"CONTENTTYPE").rylist({left:90, top:offsety, width:120, datum:"C", tag:"CONTENTTYPE",
        assigned:function(o){
            for(var i=1; i<=o.count(); i++){
                $(prefix+"type"+o.key(i)).hide();
            }
            $(prefix+"type"+o.key()).show();
        }
    });
    tx_contenttype
    .additem({caption:"WYSIWYG", key:"wysiwyg"})
    .additem({caption:"HTML", key:"html"})
    .additem({caption:"Multimedia", key:"multimedia"})
    .additem({caption:"Wikipedia", key:"wikipedia"})
    .additem({caption:"Attachment", key:"attachment"})
    .additem({caption:"Gallery", key:"gallery"})
    .additem({caption:"Frames", key:"frames"})
    .additem({caption:"URL", key:"url"})
    .additem({caption:"Embedding", key:"embedding"})
    .additem({caption:"Marquee", key:"marquee"})
    .additem({caption:"Tools", key:"tools"})
    .additem({caption:"Homelink", key:"homelink"})
    .additem({caption:"Summary", key:"summary"})
    .additem({caption:"Navigator", key:"navigator"})
    .additem({caption:"Mailus", key:"mailus"})
    .additem({caption:"Include", key:"include"})
    .additem({caption:"Forum", key:"forum"})
    .additem({caption:"Copyright", key:"copyright"});

    $(prefix+"LB_LANGUAGE").rylabel({left:240, top:offsety, caption:"Voce"});
    var tx_language=$(prefix+"LANGUAGE").rylist({left:280, top:offsety, width:100, datum:"C",
        assigned:function(o){
            for(var i=1; i<=o.count(); i++){
                $(prefix+"type"+o.key(i)).hide();
            }
            $(prefix+"type"+o.key()).show();
        }
    });
    tx_language
    .additem({caption:"(nessuna)", key:"##"})
    .additem({caption:"Italiano", key:"it"})
    .additem({caption:"English", key:"en"})
    .additem({caption:"Espanol", key:"es"});
    
    var tx_gender=$(prefix+"GENDER").rylist({left:390, top:offsety, width:50, datum:"C",
        assigned:function(o){
            for(var i=1; i<=o.count(); i++){
                $(prefix+"type"+o.key(i)).hide();
            }
            $(prefix+"type"+o.key()).show();
        }
    });
    tx_gender
    .additem({caption:"F", key:"fm"})
    .additem({caption:"M", key:"ml"});
    
    $(prefix+"LB_SYSTEMID").rylabel({left:470, top:offsety, caption:"Pagina"});
    var pageid=$(prefix+"SYSTEMID").rytext({left:520, top:offsety, width:200});
    
    offsety+=30;

    // WYSIWYG
    var tx_wysiwyg=$(prefix+"WYSIWYG").ryedit({left:20, top:0, width:700, height:450, datum:"C"});

    // HTML
    offsety=0;
    $(prefix+"LB_HTMLDETAILS").rylabel({left:20, top:offsety, caption:"Dettagli"});
    var chk_htmldetails=$(prefix+"HTMLDETAILS").rycheck({left:90, top:offsety, datum:"C"});
    
    offsety+=30;
    var tx_html=$(prefix+"HTML").ryedit({left:20, top:offsety, width:700, height:450, flat:true, datum:"C"});

    // MULTIMEDIA
    offsety=0;
    $(prefix+"LB_VIDEO").rylabel({left:20, top:offsety, caption:"URL"});
    var tx_video=$(prefix+"VIDEO").rytext({left:90, top:offsety, width:630, datum:"C"});
    
    offsety+=30;
    var tx_videowysiwyg=$(prefix+"VIDEO_WYSIWYG").ryedit({left:20, top:offsety, width:700, height:450, datum:"C"});

    // WIKIPEDIA
    var tx_wikipedia=$(prefix+"WIKIPEDIA").rytext({left:90, top:0, width:630, datum:"C"});

    // ATTACHMENT
    offsety=0;
    $(prefix+"LB_ATTDETAILS").rylabel({left:20, top:offsety, caption:"Dettagli"});
    var chk_attdetails=$(prefix+"ATTDETAILS").rycheck({left:90, top:offsety, datum:"C"});

    offsety+=30;
    var tx_attachwysiwyg=$(prefix+"ATTACH_WYSIWYG").ryedit({left:20, top:offsety, width:700, height:450, datum:"C"});

    // FRAMES
    var objframes=$(prefix+"FRAMES").ryselections({"left":20, "top":20, "width":500, "height":300, datum:"C", 
        "title":"Contenitori",
        "titlecode":"PAGE_CONTAINERS",
        "formid":formid, 
        "subid":"F",
        "table":"QW_WEBCONTAINERSJOIN",
        "helptable":"QW_WEBCONTAINERS",
        "where":"",
        "orderby":"SORTER",
        "parenttable":"QW_WEBCONTENTS", 
        "parentfield":"SETFRAMES",
        "selectedtable":"QVOBJECTS",
        "changerow":function(){
            abilitaspostaf(0);
        },
        "solveid":function(id){
            currframeid=id;
            abilitaspostaf(1);
        }
    });

    var operf_first=$(prefix+"operf_first").rylabel({
        left:530,
        top:60,
        width:100,
        caption:"Porta all'inizio",
        button:true,
        click:function(o){
            abilitaspostaf(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"FIRST",
                        "PARENTID":currsetframes,
                        "SELECTEDID":currframeid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objframes.parentid(currsetframes,
                            function(){
                                objframes.setid(currframeid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostaf(1);
                }
            );
        }
    });
    
    var operf_up=$(prefix+"operf_up").rylabel({
        left:530,
        top:90,
        width:100,
        caption:"Sposta sopra",
        button:true,
        click:function(o){
            abilitaspostaf(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"BACK",
                        "PARENTID":currsetframes,
                        "SELECTEDID":currframeid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objframes.parentid(currsetframes,
                            function(){
                                objframes.setid(currframeid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostaf(1);
                }
            );
        }
    });
    
    var operf_down=$(prefix+"operf_down").rylabel({
        left:530,
        top:120,
        width:100,
        caption:"Sposta sotto",
        button:true,
        click:function(o){
            abilitaspostaf(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"FORWARD",
                        "PARENTID":currsetframes,
                        "SELECTEDID":currframeid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objframes.parentid(currsetframes,
                            function(){
                                objframes.setid(currframeid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostaf(1);
                }
            );
        }
    });
    
    var operf_last=$(prefix+"operf_last").rylabel({
        left:530,
        top:150,
        width:100,
        caption:"Porta in fondo",
        button:true,
        click:function(o){
            abilitaspostaf(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"LAST",
                        "PARENTID":currsetframes,
                        "SELECTEDID":currframeid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objframes.parentid(currsetframes,
                            function(){
                                objframes.setid(currframeid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostaf(1);
                }
            );
        }
    });
    
    // URL
    var tx_contenturl=$(prefix+"CONTENTURL").rytext({left:90, top:0, width:630, datum:"C"});

    // EMBEDDING
    offsety=10;
    $(prefix+"LB_EMBEDHOST").rylabel({left:20, top:offsety, caption:"Host"});
    var tx_embedhost=$(prefix+"EMBEDHOST").rytext({left:90, top:offsety, width:300, datum:"C"});

    offsety+=30;
    $(prefix+"LB_EMBEDENV").rylabel({left:20, top:offsety, caption:"Ambiente"});
    var tx_embedenv=$(prefix+"EMBEDENV").rytext({left:90, top:offsety, width:300, datum:"C"});

    offsety+=30;
    $(prefix+"LB_EMBEDID").rylabel({left:20, top:offsety, caption:"Codice"});
    var tx_embedid=$(prefix+"EMBEDID").rytext({left:90, top:offsety, width:300, datum:"C"});
    
    // MARQUEE
    offsety=0;
    var tx_marqueetype=$(prefix+"MARQUEETYPE").rylist({left:90, top:0, width:200, datum:"C",
        assigned:function(o){
            tx_recents.enabled(o.value()==1);
        }
    });
    tx_marqueetype.additem({caption:"Recenti", key:0, code:"PAGE_RECENTS"});
    tx_marqueetype.additem({caption:"Correlati", key:1, code:"PAGE_RELATED"});
    
    offsety+=30;
    $(prefix+"LB_RECENTS").rylabel({left:20, top:offsety, caption:"Num. item"});
    var tx_recents=$(prefix+"RECENTS").rynumber({left:90, top:offsety,  width:200, numdec:0, datum:"C"});
    
    offsety+=30;
    $(prefix+"LB_MARDETAILS").rylabel({left:20, top:offsety, caption:"Dettagli"});
    var chk_mardetails=$(prefix+"MARDETAILS").rycheck({left:90, top:offsety, datum:"C"});
    
    // TOOLS
    offsety=0;
    $(prefix+"LB_SEARCHITEMS").rylabel({left:20, top:offsety, caption:"Num. item"});
    var tx_seritems=$(prefix+"SEARCHITEMS").rynumber({left:90, top:offsety,  width:200, numdec:0, datum:"C"});

    offsety+=30;
    $(prefix+"LB_SERDETAILS").rylabel({left:20, top:offsety, caption:"Dettagli"});
    var chk_serdetails=$(prefix+"SERDETAILS").rycheck({left:90, top:offsety, datum:"C"});
    
    // SUMMARY
    offsety=0;
    $(prefix+"LB_PARENTID").rylabel({left:20, top:offsety, caption:"Genitore"});
    var tx_parentid=$(prefix+"PARENTID").ryhelper({
        left:90, top:offsety, width:200, datum:"C", formid:formid, table:"QW_WEBCONTENTS", titlecode:"HLP_SELPARENT", 
        open:function(o){
            o.where("");
        }
    });
    
    offsety+=30;
    $(prefix+"LB_SUMDETAILS").rylabel({left:20, top:offsety, caption:"Dettagli"});
    var chk_sumdetails=$(prefix+"SUMDETAILS").rycheck({left:90, top:offsety, datum:"C"});
    
    //NAVIGATOR
    offsety=0;
    $(prefix+"LB_NAVDETAILS").rylabel({left:20, top:offsety, caption:"Dettagli"});
    var chk_navdetails=$(prefix+"NAVDETAILS").rycheck({left:90, top:offsety, datum:"C"});

    offsety+=30;
    $(prefix+"LB_NAVTOOL").rylabel({left:20, top:offsety, caption:"Tool"});
    var chk_navtool=$(prefix+"NAVTOOL").rycheck({left:90, top:offsety, datum:"C"});
    
    $(prefix+"LB_NAVSORTING").rylabel({left:190, top:offsety, caption:"Ordinamento"});
    var tx_navsorting=$(prefix+"NAVSORTING").rylist({left:280, top:offsety, width:160, datum:"C"});
    tx_navsorting.additem({caption:"Data", key:0, code:"PAGE_DATE"});
    tx_navsorting.additem({caption:"Descrizione", key:1, code:"DESCRIPTION"});
    tx_navsorting.additem({caption:"Marca", key:2, code:"TAG"});
    tx_navsorting.additem({caption:"Relazionale", key:3, code:"PAGE_RELATIONAL"});
    
    offsety+=30;
    $(prefix+"LB_NAVHOME").rylabel({left:20, top:offsety, caption:"Home"});
    var chk_navhome=$(prefix+"NAVHOME").rycheck({left:90, top:offsety, datum:"C"});
    
    offsety+=30;
    $(prefix+"LB_NAVPRIMARY").rylabel({left:20, top:offsety, caption:"Principali"});
    var chk_navprimary=$(prefix+"NAVPRIMARY").rycheck({left:90, top:offsety, datum:"C"});
    
    offsety+=30;
    $(prefix+"LB_NAVPARENTS").rylabel({left:20, top:offsety, caption:"Genitori"});
    var chk_navparents=$(prefix+"NAVPARENTS").rycheck({left:90, top:offsety, datum:"C"});
    
    offsety+=30;
    $(prefix+"LB_NAVSIBLINGS").rylabel({left:20, top:offsety, caption:"Fratelli"});
    var chk_navsiblings=$(prefix+"NAVSIBLINGS").rycheck({left:90, top:offsety, datum:"C"});
    
    offsety+=30;
    $(prefix+"LB_NAVRELATED").rylabel({left:20, top:offsety, caption:"Correlati"});
    var chk_navrelated=$(prefix+"NAVRELATED").rycheck({left:90, top:offsety, datum:"C"});
    
    // MAILUS
    offsety=10;
    $(prefix+"LB_EMAIL").rylabel({left:20, top:0, caption:"Email"});
    var tx_email=$(prefix+"EMAIL").rytext({left:90, top:0, width:630, maxlen:50, datum:"C"});

    offsety+=30;
    var tx_emailwysiwyg=$(prefix+"EMAIL_WYSIWYG").ryedit({left:20, top:offsety, width:700, height:450, datum:"C"});

    // INCLUDE
    $(prefix+"LB_INCLUDEFILE").rylabel({left:20, top:0, caption:"Sorgente"});
    var tx_include=$(prefix+"INCLUDEFILE").rytext({left:90, top:0, width:630, datum:"C"});
    
    // COPYRIGHT
    offsety=0;
    $(prefix+"LB_DEALER").rylabel({left:20, top:offsety, caption:"Distribuz."});
    var tx_dealer=$(prefix+"DEALER").rytext({left:90, top:offsety, maxlen:100, width:350, datum:"C"});
    
    offsety+=30;
    $(prefix+"LB_AUTHOR").rylabel({left:20, top:offsety, caption:"Autore"});
    var tx_author=$(prefix+"AUTHOR").rytext({left:90, top:offsety, maxlen:100, width:350, datum:"C"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
        width:80,
        caption:"Salva",
        button:true,
        click:function(o, done){
            winzProgress(formid);
            context=_strip_tags(txdescr.value());
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            data["REGISTRY"]="";
            data["CONTENTURL"]="";
            data["STATUS"]="0";
            data["CONSISTENCY"]="0";
            data["REFERENCE"]=tx_gender.key()+tx_language.key();
            if(chk_scope.value())
                data["SCOPE"]="0";
            else
                data["SCOPE"]="2";
            switch(tx_contenttype.key()){
            case "wysiwyg":
                var t=tx_wysiwyg.value();
                data["REGISTRY"]=t;
                if(t.match(/[\\][\(\[][^\x00]+[\\][\)\]]/))
                    data["SPECIALS"]="math";
                else
                    data["SPECIALS"]="";
                break;
            case "html":
                data["ITEMDETAILS"]=chk_htmldetails.value();
                var t=tx_html.value();
                data["REGISTRY"]=t;
                if(t.match(/<script>[^\x00]+Snap\(/))
                    data["SPECIALS"]="svg";
                else
                    data["SPECIALS"]="";
                break;
            case "multimedia":
                data["CONTENTURL"]=tx_video.value();
                data["REGISTRY"]=tx_videowysiwyg.value();
                break;
            case "wikipedia":
                data["CONTENTURL"]=tx_wikipedia.value();
                break;
            case "attachment":
                data["ITEMDETAILS"]=chk_attdetails.value();
                data["REGISTRY"]=tx_attachwysiwyg.value();
                break;
            case "url":
                data["CONTENTURL"]=tx_contenturl.value();
                break;
            case "embedding":
                data["CONTENTURL"]=tx_embedhost.value();
                data["ENVIRON"]=tx_embedenv.value();
                data["EMBEDID"]=tx_embedid.value();
                break;
            case "marquee":
                if(tx_marqueetype.key()==0)
                    data["MARQUEETYPE"]=tx_recents.value();
                else
                    data["MARQUEETYPE"]=0;
                data["ITEMDETAILS"]=chk_mardetails.value();
                break;
            case "tools":
                data["SEARCHITEMS"]=tx_seritems.value();
                data["ITEMDETAILS"]=chk_serdetails.value();
                break;
            case "summary":
                data["PARENTID"]=tx_parentid.value();
                data["ITEMDETAILS"]=chk_sumdetails.value();
                break;
            case "navigator":
                data["ITEMDETAILS"]=chk_navdetails.value();
                data["NAVHOME"]=chk_navhome.value();
                data["NAVPRIMARY"]=chk_navprimary.value();
                data["NAVPARENTS"]=chk_navparents.value();
                data["NAVSIBLINGS"]=chk_navsiblings.value();
                data["NAVRELATED"]=chk_navrelated.value();
                data["NAVTOOL"]=chk_navtool.value();
                data["NAVSORTING"]=tx_navsorting.key();
                break;
            case "mailus":
                data["EMAIL"]=tx_email.value();
                data["REGISTRY"]=tx_emailwysiwyg.value();
                break;
            case "include":
                data["INCLUDEFILE"]=tx_include.value();
                break;
            case "copyright":
                data["DEALER"]=tx_dealer.value();
                data["AUTHOR"]=tx_author.value();
                break;
            }
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"arrows_update",
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

    var oper_browser=$(prefix+"oper_browser").rylabel({
        left:780,
        top:60,
        width:80,
        caption:"Visualizza",
        button:true,
        click:function(o){
            var s=(sitename!="" ? sitename : fsitename);
            if(s!=""){
                if(_isset(currbrowser)){
                    try{
                        currbrowser.close();
                        _pause(500);
                    }catch(e){}
                }
                currbrowser=window.open("filibuster.php?env="+_sessioninfo.environ+"&site="+s+"&id="+currsysid, "filibuster_browser");
            }
            else{
                winzMessageBox(formid, RYBOX.babels("PAGE_SELSITE"));
            }
        }
    });
    
    $(prefix+"typewysiwyg").show();

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVARROWS",
        {
            changerow:function(){
                currfileid="";
                oper_icon.enabled(0);
                tx_copy.clear();
                tx_copy.enabled(0);
                tx_download.clear();
                tx_download.enabled(0);
                $(prefix+"PREVIEW").css({display:"none"});
                $(prefix+"PREVIEW").html("");
            },
            solveid:function(id, d){
                currfileid=id;
                tx_copy.enabled(1);
                tx_download.enabled(1);
                var exten=_getextension(d["IMPORTNAME"]);
                var p=dirattachments+d["SUBPATH"]+d["FILEID"]+"."+exten;
                var u=urlattachments+d["SUBPATH"]+d["FILEID"]+"."+exten;
                var w=urlapplications+"ryquiver/food4download.php?env="+_sessioninfo.environ+"&site="+sitename+"&id="+d["FILEID"];
                tx_copy.value(u);
                tx_download.value(w);
                if(exten.toLowerCase().match(/(jpg|jpeg|gif|png|svg)/)){
                    oper_icon.enabled(1);
                    $(prefix+"PREVIEW").html("<img src='"+_cambusaURL+"/phpthumb/phpThumb.php?h=80&src="+p+"' style='border:1px solid silver;'>");
                    $(prefix+"PREVIEW").css({display:"block"});
                }
            }
        }
    );
    
    $(prefix+"filemanager").append("<div id='"+formid+"oper_icon' babelcode='PAGE_USEASICON'></div>");
    $(prefix+"filemanager").append("<div id='"+formid+"oper_removeicon' babelcode='PAGE_REMOVEICON'></div>");
    $(prefix+"filemanager").append("<div id='"+formid+"COPY'></div>");
    $(prefix+"filemanager").append("<div id='"+formid+"DOWNLOAD'></div>");
    $(prefix+"filemanager").append("<div id='"+formid+"PREVIEW'></div>");

    offsety=370;
    
    // USA COME ICONA
    var oper_icon=$(prefix+"oper_icon").rylabel({
        left:20,
        top:offsety,
        width:100,
        caption:"Usa come icona",
        formid:formid,
        button:true,
        click:function(o){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pages_iconize",
                    "data":{
                        "OPER":"+i",
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
    oper_icon.enabled(0);
    
    // RIMUOVI ICONA
    var oper_removeicon=$(prefix+"oper_removeicon").rylabel({
        left:310,
        top:offsety,
        width:100,
        caption:"Rimuovi icona",
        formid:formid,
        button:true,
        click:function(o){
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"pages_iconize",
                    "data":{
                        "OPER":"-i",
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
    
    offsety+=50;
    var tx_copy=$(prefix+"COPY").rytext({left:20, top:offsety, width:730, formid:formid});
    tx_copy.enabled(0);
    
    offsety+=30;
    var tx_download=$(prefix+"DOWNLOAD").rytext({left:20, top:offsety, width:730, formid:formid});
    tx_download.enabled(0);
    
    offsety+=30;
    $(prefix+"PREVIEW").css({position:"absolute", left:20, top:offsety, display:"none"});
    
    // DEFINIZIONE TAB CORRELAZIONI
    offsety=90;
    var lb_correlati_context=$(prefix+"correlati_context").rylabel({left:20, top:50, caption:""});

    // CORRELATI A MONTE
    var operp_add=$(prefix+"operp_add").rylabel({
        left:40,
        top:offsety,
        width:60,
        caption:"Aggiungi",
        button:true,
        click:function(o){
            qv_idrequest(formid, {
                table:"QW_WEBCONTENTS", 
                classtable:"QW_CLASSICONTENUTO", 
                select:"SETRELATED",
                where:"SYSID<>'"+currsysid+"' AND (SITEID='' OR SITEID='"+currsiteid+"' OR SITEID='"+tx_siteid.value()+"') AND SETRELATED NOT IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+currsysid+"')",
                title:"Scelta genitore",
                multiple:false,
                onselect:function(d){
                    var parentid=d["SETRELATED"];
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_add",
                            "data":{
                                "PARENTTABLE":"QW_WEBCONTENTS",
                                "PARENTFIELD":"SETRELATED",
                                "PARENTID":parentid,
                                "SELECTEDTABLE":"QVARROWS",
                                "SELECTION":currsysid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    operp_refresh.engage();
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

    var operp_remove=$(prefix+"operp_remove").rylabel({
        left:120,
        top:offsety,
        width:60,
        caption:"Rimuovi",
        button:true,
        click:function(o){
            RYQUE.query({
                sql:"SELECT SETRELATED FROM QW_WEBCONTENTS WHERE SYSID='"+currparentid+"'",
                ready:function(v){
                    var relateid=v[0]["SETRELATED"];
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"selections_remove",
                            "data":{
                                "PARENTID":relateid,
                                "SELECTION":currsysid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    operp_refresh.engage();
                                }
                                winzTimeoutMess(propformid, v.success, v.message);
                            }
                            catch(e){
                                winzClearMess(propformid);
                                alert(d);
                            }
                        }
                    );
                }
            });
        }
    });

    var operp_refresh=$(prefix+"operp_refresh").rylabel({
        left:530,
        top:offsety+30,
        width:70,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="SETRELATED IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+currsysid+"')";
            gridparent.where(q);
            gridparent.query();
        }
    });
    
    offsety+=25;
    var gridparent=$(prefix+"gridparent").ryque({
        left:20,
        top:offsety,
        width:500,
        height:150,
        numbered:false,
        checkable:false,
        environ:_sessioninfo.environ,
        from:"QW_WEBCONTENTS",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION", caption:"Correlati a monte", width:2000, code:"UPSTREAM_RELATED"}
        ],
        changerow:function(o,i){
            currparentid="";
            operp_remove.enabled(o.isselected());
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            operp_remove.enabled(o.isselected());
        },
        solveid:function(o, d){
            currparentid=d;
            operp_remove.enabled(1);
        }
    });
    
    offsety=290;
    
    var operr_refresh=$(prefix+"operr_refresh").rylabel({
        left:530,
        top:offsety+30,
        width:70,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            objrelated.clause({"PARENTID":currsetrelated});
            objrelated.parentid(currsetrelated);
        }
    });
    
    var objrelated=$(prefix+"RELATED").ryselections({"left":20, "top":offsety, "width":500, "height":300, 
        "title":"Correlati a valle",
        "titlecode":"DOWNSTREAM_RELATED",
        "formid":formid, 
        "subid":"R",
        "table":"QW_WEBCONTENTSJOIN",
        "helptable":"QW_WEBCONTENTS",
        "where":"",
        "orderby":"SORTER",
        "parenttable":"QW_WEBCONTENTS", 
        "parentfield":"SETRELATED",
        "selectedtable":"QVARROWS",
        "classtable":"QW_CLASSICONTENUTO", 
        "changerow":function(){
            abilitaspostar(0);
        },
        "solveid":function(id){
            currselectedid=id;
            abilitaspostar(1);
        }
    });
    
    offsety+=320;

    var operr_first=$(prefix+"operr_first").rylabel({
        left:20,
        top:offsety,
        width:100,
        caption:"Porta all'inizio",
        button:true,
        click:function(o){
            abilitaspostar(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"FIRST",
                        "PARENTID":currsetrelated,
                        "SELECTEDID":currselectedid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objrelated.parentid(currsetrelated,
                            function(){
                                objrelated.setid(currselectedid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostar(1);
                }
            );
        }
    });
    
    var operr_up=$(prefix+"operr_up").rylabel({
        left:150,
        top:offsety,
        width:100,
        caption:"Sposta sopra",
        button:true,
        click:function(o){
            abilitaspostaf(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"BACK",
                        "PARENTID":currsetrelated,
                        "SELECTEDID":currselectedid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objrelated.parentid(currsetrelated,
                            function(){
                                objrelated.setid(currselectedid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostar(1);
                }
            );
        }
    });
    
    var operr_down=$(prefix+"operr_down").rylabel({
        left:280,
        top:offsety,
        width:100,
        caption:"Sposta sotto",
        button:true,
        click:function(o){
            abilitaspostar(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"FORWARD",
                        "PARENTID":currsetrelated,
                        "SELECTEDID":currselectedid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objrelated.parentid(currsetrelated,
                            function(){
                                objrelated.setid(currselectedid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostar(1);
                }
            );
        }
    });
    
    var operr_last=$(prefix+"operr_last").rylabel({
        left:410,
        top:offsety,
        width:100,
        caption:"Porta in fondo",
        button:true,
        click:function(o){
            abilitaspostar(0);
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"selections_arrange",
                    "data":{
                        "POSITION":"LAST",
                        "PARENTID":currsetrelated,
                        "SELECTEDID":currselectedid
                    }
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        objrelated.parentid(currsetrelated,
                            function(){
                                objrelated.setid(currselectedid);
                            }
                        );
                        winzTimeoutMess(formid, v.success, v.message);
                    }
                    catch(e){
                        winzClearMess(formid);
                        alert(d);
                    }
                    abilitaspostar(1);
                }
            );
        }
    });
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione", code:"SELECTION"},
            {title:"Contesto", code:"CONTEXT"},
            {title:"Documenti", code:"DOCUMENTS"},
            {title:"Correlazioni", code:"PAGE_RELATIONS"}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedsysidX="";
                    }
                });
            }
            if(i==1){
                loadedsysidX="";
                loadedsysidR="";
                if(currsysid!=""){
                    $(prefix+"pagepreview").show();
                }
            }
            else if(i==2){
                if(currsysid==loadedsysidX){
                    flagsuspend=true;
                }
                $(prefix+"pagepreview").hide();
            }
            else if(i==3){
                $(prefix+"pagepreview").hide();
            }
            else if(i==4){
                if(currsysid==loadedsysidR){
                    flagsuspend=true;
                }
            }
            if(!flagsuspend){
                switch(i){
                case 1:
                    objgridsel.dataload(
                        function(){
                            refreshpreview();
                        }
                    );
                    break;
                case 2:
                    // CARICAMENTO DEL CONTESTO
                    if(window.console&&_sessioninfo.debugmode){console.log("Loading context: "+currsysid)}
                    // RESET MASCHERA
                    RYWINZ.MaskClear(formid, "C");
                    pageid.clear();
                    objclassi.clear();
                    objframes.clear();
                    tx_language.value(2);
                    tx_gender.value(1);
                    tx_wysiwyg.clear();
                    chk_htmldetails.value(1);
                    tx_html.clear();
                    tx_video.clear();
                    tx_videowysiwyg.clear();
                    tx_wikipedia.clear();
                    chk_attdetails.value(1);
                    tx_attachwysiwyg.clear();
                    tx_contenturl.clear();
                    tx_embedhost.clear();
                    tx_embedenv.clear();
                    tx_embedid.clear();
                    tx_marqueetype.value(1);
                    chk_mardetails.value(1);
                    tx_recents.value(20);
                    tx_seritems.value(100);
                    chk_serdetails.value(1);
                    chk_navdetails.value(1);
                    chk_navhome.value(1);
                    chk_navparents.value(1);
                    chk_navprimary.value(1);
                    chk_navrelated.value(1);
                    chk_navsiblings.value(1);
                    chk_navtool.value(1);
                    tx_navsorting.value(1);
                    chk_sumdetails.value(1);
                    tx_email.clear();
                    tx_emailwysiwyg.clear();
                    tx_include.clear();
                    RYQUE.query({
                        sql:"SELECT * FROM QW_WEBCONTENTS WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=_strip_tags(v[0]["DESCRIPTION"]);
                            loadedsysidX=currsysid;
                            currsetframes=v[0]["SETFRAMES"];
                            currsetrelated=v[0]["SETRELATED"];
                            pageid.value(v[0]["SYSID"]);
                            tx_contenttype.raiseassigned();
                            if(_getinteger(v[0]["SCOPE"]))
                                chk_scope.value(0);
                            else
                                chk_scope.value(1);

                            var genderlang=v[0]["REFERENCE"];
                            if(genderlang.length>=4){
                                tx_gender.setkey(genderlang.substr(0,2));
                                tx_language.setkey(genderlang.substr(2,2));
                            }

                            switch(tx_contenttype.key()){
                            case "wysiwyg":
                                tx_wysiwyg.value(v[0]["REGISTRY"]);
                                break;
                            case "html":
                                chk_htmldetails.value(v[0]["ITEMDETAILS"]);
                                tx_html.value(v[0]["REGISTRY"]);
                                break;
                            case "multimedia":
                                tx_video.value(v[0]["CONTENTURL"]);
                                tx_videowysiwyg.value(v[0]["REGISTRY"]);
                                break;
                            case "wikipedia":
                                tx_wikipedia.value(v[0]["CONTENTURL"]);
                                break;
                            case "attachment":
                                chk_attdetails.value(v[0]["ITEMDETAILS"]);
                                tx_attachwysiwyg.value(v[0]["REGISTRY"]);
                                break;
                            case "url":
                                tx_contenturl.value(v[0]["CONTENTURL"]);
                                break;
                            case "embedding":
                                tx_embedhost.value(v[0]["CONTENTURL"]);
                                tx_embedenv.value(v[0]["ENVIRON"]);
                                tx_embedid.value(v[0]["EMBEDID"]);
                                break;
                            case "marquee":
                                var recs=_getinteger(v[0]["MARQUEETYPE"]);
                                if(recs>0){
                                    tx_marqueetype.setkey(0);
                                    tx_recents.enabled(1);
                                    tx_recents.value(recs);
                                }
                                else{
                                    tx_marqueetype.setkey(1);
                                    tx_recents.enabled(0);
                                    tx_recents.value(20);
                                }
                                chk_mardetails.value(v[0]["ITEMDETAILS"]);
                                break;
                            case "tools":
                                tx_seritems.value(v[0]["SEARCHITEMS"]);
                                chk_serdetails.value(v[0]["ITEMDETAILS"]);
                                break;
                            case "summary":
                                tx_parentid.value(v[0]["PARENTID"]);
                                chk_sumdetails.value(v[0]["ITEMDETAILS"]);
                                break;
                            case "navigator":
                                chk_navdetails.value(v[0]["ITEMDETAILS"]);
                                chk_navhome.value(v[0]["NAVHOME"]);
                                chk_navprimary.value(v[0]["NAVPRIMARY"]);
                                chk_navparents.value(v[0]["NAVPARENTS"]);
                                chk_navsiblings.value(v[0]["NAVSIBLINGS"]);
                                chk_navrelated.value(v[0]["NAVRELATED"]);
                                chk_navtool.value(v[0]["NAVTOOL"]);
                                tx_navsorting.setkey(v[0]["NAVSORTING"]);
                                break;
                            case "mailus":
                                tx_email.value(v[0]["EMAIL"]);
                                tx_emailwysiwyg.value(v[0]["REGISTRY"]);
                                break;
                            case "include":
                                tx_include.value(v[0]["INCLUDEFILE"]);
                                break;
                            case "copyright":
                                tx_dealer.value(v[0]["DEALER"]);
                                tx_author.value(v[0]["AUTHOR"]);
                                break;
                            }
                            RYWINZ.modified(formid, 0);
                            objclassi.parentid(currsysid,
                                function(){
                                    objframes.clause({"PARENTID":currsetframes});
                                    objframes.parentid(currsetframes,
                                        function(){
                                            castFocus(prefix+"DESCRIPTION");
                                        }
                                    );
                                }
                            );
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, bbl_context.replace("{1}", context), currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVARROWS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=_strip_tags(d);
                            filemanager.caption(bbl_context.replace("{1}", context));
                        }
                    });
                    break;
                case 4:
                    // CARICAMENTO CORRELAZIONI
                    lb_correlati_context.caption(bbl_context.replace("{1}", context));
                    gridparent.clear()
                    objrelated.clear();
                    RYQUE.query({
                        sql:"SELECT DESCRIPTION,SETRELATED FROM QW_WEBCONTENTS WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            context=_strip_tags(v[0]["DESCRIPTION"]);
                            lb_correlati_context.caption(bbl_context.replace("{1}", context));
                            loadedsysidR=currsysid;
                            currsetrelated=v[0]["SETRELATED"];
                            objrelated.where("(SITEID='' OR SITEID='"+currsiteid+"' OR SITEID='"+tx_siteid.value()+"')");
                            objrelated.clause({"PARENTID":currsetrelated});
                            objrelated.parentid(currsetrelated,
                                function(){
                                    operp_refresh.engage();
                                }
                            );
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
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.babels({
        "PAGE_SELSITE":"Selezionare un sito!",
        "HLP_SELSITE":"Selezione sito",
        "HLP_SELPARENT":"Selezione genitore",
        "HLP_SELCLASS":"Selezione classe"
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
                        urlapplications=v.params["URLAPPS"];
                    }catch(e){}
                    refreshselection(
                        function(){
                            winzClearMess(formid);
                            txf_search.focus();
                        }
                    );
                }
            );
        }
    );
    function abilitaspostaf(f){
        operf_first.enabled(f);
        operf_up.enabled(f);
        operf_down.enabled(f);
        operf_last.enabled(f);
    }
    function abilitaspostar(f){
        operr_first.enabled(f);
        operr_up.enabled(f);
        operr_down.enabled(f);
        operr_last.enabled(f);
    }
    function refreshselection(after){
        if(!sospendirefresh){
            setTimeout(
                function(){
                    oper_refresh.engage(after);
                }
            , 100);
        }
    }
    function refreshpreview(){
        RYQUE.query({
            sql:"SELECT DESCRIPTION,ABSTRACT,REGISTRY FROM QW_WEBCONTENTS WHERE SYSID='"+currsysid+"'",
            ready:function(v){
                var h="";
                h+="<div style='margin-bottom:4px'>";
                h+="<h2>"+v[0]["DESCRIPTION"]+"</h2>";
                h+="</div>";
                h+="<div style='margin-bottom:10px'>";
                h+="<i>"+v[0]["ABSTRACT"]+"</i>";
                h+="</div>";
                h+="<div>";
                h+=v[0]["REGISTRY"];
                h+="</div>";
                h=h.replace(/<script[^\x00]+<\/script>/ig, "");
                h=h.replace(/<iframe[^\x00]+<\/iframe>/ig, "");
                $(prefix+"previewinner").html(h);
                $(prefix+"pagepreview").show();
            }
        });
    }
}

