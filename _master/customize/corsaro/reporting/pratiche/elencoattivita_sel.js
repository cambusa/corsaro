/****************************************************************************
* Name:            elencoattivita_sel.js                                    *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_elencoattivita_sel(settings,missing){
    var formid=RYWINZ.addform(this);
    var prefix="#"+formid;
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:480, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    offsety+=30;

    $(prefix+"lbf_processi").rylabel({left:20, top:offsety, caption:"Processi"});
    var txf_processi=$(prefix+"txf_processi").ryhelper({left:100, top:offsety, width:180, 
        formid:formid, table:"QW_PROCESSI", title:"Scelta processi", multiple:true,
        open:function(o){
            o.where("");
        },
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    
    $(prefix+"lbf_stato").rylabel({left:330, top:offsety, caption:"Stato"});
    var txf_stato=$(prefix+"txf_stato").rylist({left:400, top:offsety, width:180,
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    txf_stato
    .additem({caption:"Tutte", key:-1})
    .additem({caption:"Pendenti", key:0})
    .additem({caption:"Complete", key:1});
    offsety+=30;

    $(prefix+"lbf_disponibilita").rylabel({left:330, top:offsety, caption:"Disponib."});
    var txf_disponibilita=$(prefix+"txf_disponibilita").rylist({left:400, top:offsety, width:180,
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    txf_disponibilita
    .additem({caption:"Non archiviate", key:0})
    .additem({caption:"Archiviate", key:2})
    .additem({caption:"Tutte", key:-1});
    offsety+=30;

    $(prefix+"lbf_richiedenti").rylabel({left:20, top:offsety, caption:"Richiedenti"});
    var txf_richiedenti=$(prefix+"txf_richiedenti").ryhelper({left:100, top:offsety, width:180, 
        formid:formid, table:"QW_ATTORI", title:"Scelta richiedenti", multiple:true,
        open:function(o){
            o.where("");
        },
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    
    $(prefix+"lbf_esecutori").rylabel({left:330, top:offsety, caption:"Esecutori"});
    var txf_esecutori=$(prefix+"txf_esecutori").ryhelper({left:400, top:offsety, width:180, 
        formid:formid, table:"QW_ATTORI", title:"Scelta esecutori", multiple:true,
        open:function(o){
            o.where("");
        },
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_datamin").rylabel({left:20, top:offsety, caption:"Data min"});
    var txf_datamin=$(prefix+"txf_datamin").rydate({left:100, top:offsety, 
        assigned:function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    
    $(prefix+"lbf_datamax").rylabel({left:330, top:offsety, caption:"Data max"});
    var txf_datamax=$(prefix+"txf_datamax").rydate({left:400, top:offsety,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    offsety+=30;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:650,
        top:80,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="";
            var t=txf_search.value().toUpperCase();
            t=t.replace(" ", "%");
            
            var processiid=txf_processi.value();
            var stato=txf_stato.key();
            var disponibilita=txf_disponibilita.key();
            var richiedentiid=txf_richiedenti.value();
            var esecutoriid=txf_esecutori.value();
            var datamin=txf_datamin.text();
            var datamax=txf_datamax.text();

            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            if(processiid!=""){
                if(q!=""){q+=" AND "}
                q+="QW_ATTIVITABROWSER.SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID IN (SELECT QW_PRATICHE.SYSID FROM QW_PRATICHE WHERE PROCESSOID IN ('"+processiid.replace("|", "','")+"')))";
            }
            if(stato>=0){
                if(q!=""){q+=" AND "}
                q+="STATUS="+stato;
            }
            if(disponibilita>=0){
                if(q!=""){q+=" AND "}
                q+="AVAILABILITY="+disponibilita;
            }
            if(richiedentiid!=""){
                if(q!=""){q+=" AND "}
                q+="BOWID IN ('"+richiedentiid.replace("|", "','")+"')";
            }
            if(esecutoriid!=""){
                if(q!=""){q+=" AND "}
                q+="TARGETID IN ('"+esecutoriid.replace("|", "','")+"')";
            }
            if(datamin!=""){
                if(q!=""){q+=" AND "}
                q+="(BOWTIME<=[:TIME("+datamin+"235959)] AND TARGETTIME>=[:TIME("+datamin+"000000)])";
            }
            if(datamax!=""){
                if(q!=""){q+=" AND "}
                q+="(BOWTIME<=[:TIME("+datamax+"235959)] AND TARGETTIME>=[:TIME("+datamax+"000000)])";
            }

            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "DESCRIPTION":_ajaxescapize( t ),
                    "TAG":_ajaxescapize( t )
                }
            });
        }
    });
    offsety+=20;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_ATTIVITABROWSER",
        orderby:"TARGETTIME DESC",
        columns:[
            {id:"DESCRIPTION", caption:"Descrizione", width:190},
            {id:"BOWTIME", caption:"Inizio", width:90, type:"/"},
            {id:"BOW", caption:"Richiedente", width:150},
            {id:"TARGETTIME", caption:"Scadenza", width:90, type:"/"},
            {id:"TARGET", caption:"Esecutore", width:150},
            {id:"STATUS", caption:"St.", width:40 , type:"?"}
        ]
    });
    offsety+=310;
    
    var oper_print=$(prefix+"oper_print").rylabel({
        left:20,
        top:offsety,
        caption:"Stampa",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "pratiche/elencoattivita_rep.php", {checkall:true})
        }
    });

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
        tabs:[
            {title:"Selezione"}
        ]
    });
    objtabs.currtab(1);
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid);
}

