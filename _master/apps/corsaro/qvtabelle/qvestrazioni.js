/****************************************************************************
* Name:            qvestrazioni.js                                          *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvestrazioni(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var prefix="#"+formid;
    var sospendirefresh=false;
    var param_from="QWOBJECTS";
    var param_where="";
    var param_orderby="DESCRIPTION";
    var param_classtable="";
    var param_columns=[{id:"DESCRIPTION",caption:"Descrizione",width:250}];
    
    if(settings.from!=missing){param_from=settings.from}
    if(settings.where!=missing){param_where=settings.where.replace(/[|]/gi, "'");}
    if(settings.orderby!=missing){param_orderby=settings.orderby}
    if(settings.classtable!=missing){param_classtable=settings.classtable}
    if(settings.columns!=missing){param_columns=settings.columns}
    
    if(window.console){console.log(settings)}
    
    // DEFINIZIONE TAB SELEZIONE

    var offsety=80;
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    if(param_classtable!=""){
        $(prefix+"lbf_classe").rylabel({left:20, top:offsety, caption:"Classe"});
        var txf_classe=$(prefix+"txf_classe").ryhelper({left:100, top:offsety, width:200, 
            formid:formid, table:param_classtable, title:"Classi", multiple:false,
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
    }
    else{
        $(prefix+"lbf_classe").remove();
    }
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:630,
        top:80,
        width:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q=param_where;
            var t=_likeescapize(txf_search.value());
            var classeid="";

            if(param_classtable!=""){
                classeid=txf_classe.value();
            }

            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            if(classeid!=""){
                if(q!=""){q+=" AND "}
                q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID='"+classeid+"')";
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
    var oper_reset=$(prefix+"oper_reset").rylabel({
        left:630,
        top:110,
        width:80,
        caption:"Pulisci",
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_classe.clear();
            sospendirefresh=false;
            refreshselection();
        }
    });
    offsety+=35;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        maxwidth:-1,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:param_from,
        orderby:param_orderby,
        columns:param_columns
    });

    offsety=440;
    var oper_print=$(prefix+"oper_print").rylabel({
        left:20,
        top:offsety,
        width:120,
        caption:"Estrazione",
        button:true,
        click:function(o){
            objgridsel.sheet();
        }
    });

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"}
        ]
    });
    objtabs.currtab(1);
    
    // INIZIALIZZAZIONE FORM
    RYWINZ.KeyTools(formid, objtabs);
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
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
    function refreshselection(){
        if(!sospendirefresh){
            setTimeout(
                function(){
                    oper_refresh.engage();
                }
            , 100);
        }
    }
}

