/****************************************************************************
* Name:            primanota_sel.js                                         *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_primanota_sel(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    var currgenretypeid=RYQUE.formatid("0MONEY000000");
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

    $(prefix+"lbf_divise").rylabel({left:20, top:offsety, caption:"Divise"});
    var txf_divise=$(prefix+"txf_divise").ryhelper({left:100, top:offsety, width:180, 
        formid:formid, table:"QVGENRES", title:"Scelta divise", multiple:true,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    
    
    offsety+=30;
    
    $(prefix+"lbf_riferimenti").rylabel({left:20, top:offsety, caption:"Conti"});
    var txf_riferimenti=$(prefix+"txf_riferimenti").ryhelper({left:100, top:offsety, width:180, 
        formid:formid, table:"QW_CONTI", title:"Scelta conti di riferimento", multiple:true,
        open:function(o){
            o.where("");
        },
        assigned: function(){
            setTimeout(function(){oper_refresh.engage();}, 100);
        }
    });
    
    $(prefix+"lbf_controparti").rylabel({left:320, top:offsety, caption:"Controparti"});
    var txf_controparti=$(prefix+"txf_controparti").ryhelper({left:400, top:offsety, width:180, 
        formid:formid, table:"QW_CONTI", title:"Scelta controparti", multiple:true,
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
    
    $(prefix+"lbf_datamax").rylabel({left:320, top:offsety, caption:"Data max"});
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
            objgridsel.clear();
            if(txf_riferimenti.value()!=""){
                var q="";
                var t=txf_search.value().toUpperCase();
                t=t.replace(" ", "%");
                
                var diviseid=txf_divise.value();
                var riferimentiid=txf_riferimenti.value();
                var contropartiid=txf_controparti.value();
                var datamin=txf_datamin.text();
                var datamax=txf_datamax.text();

                q="STATUS>=1";
                if(t!=""){
                    if(q!=""){q+=" AND "}
                    q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
                }
                if(diviseid!=""){
                    if(q!=""){q+=" AND "}
                    q+="GENREID IN ('"+diviseid.replace("|", "','")+"')";
                }
                if(riferimentiid!=""){
                    if(q!=""){q+=" AND "}
                    q+="(BOWID IN ('"+riferimentiid.replace("|", "','")+"') OR TARGETID IN ('"+riferimentiid.replace("|", "','")+"'))";
                }
                if(contropartiid!=""){
                    if(q!=""){q+=" AND "}
                    q+="(BOWID IN ('"+contropartiid.replace("|", "','")+"') OR TARGETID IN ('"+contropartiid.replace("|", "','")+"'))";
                }
                if(datamin!=""){
                    if(q!=""){q+=" AND "}
                    q+="AUXTIME>=[:TIME("+datamin+"000000)]";
                }
                if(datamax!=""){
                    if(q!=""){q+=" AND "}
                    q+="AUXTIME<=[:TIME("+datamax+"235959)]";
                }

                objgridsel.where(q);
                objgridsel.query({
                    args:{
                        "DESCRIPTION":t,
                        "TAG":t
                    }
                });
            }
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
        from:"QW_MOVIMENTIJOIN",
        orderby:"AUXTIME",
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:180},
            {id:"MOTIVE",caption:"Causale",width:140},
            {id:"AUXTIME",caption:"Data Reg.",width:90,type:"/"},
            {id:"AMOUNT",caption:"Importo",width:120,type:"2"},
            {id:"GENRE",caption:"Divisa",width:90}
        ]
    });
    offsety+=310;
    
    var oper_print=$(prefix+"oper_print").rylabel({
        left:20,
        top:offsety,
        caption:"Stampa",
        button:true,
        click:function(o){
            var riferimentiid=txf_riferimenti.value();
            qv_printselected(formid, objgridsel, "movimenti/primanota_rep.php", 
                {
                    checkall:true, 
                    params:{
                        "riferimenti":riferimentiid,
                        "selezione":"da "+txf_datamin.text().formatDate("01/01/1900")+" a "+txf_datamax.text().formatDate("31/12/9999")
                    }
                }
            );
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
    RYBOX.localize(_sessioninfo.language, formid);
}

