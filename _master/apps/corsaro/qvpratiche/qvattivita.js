/****************************************************************************
* Name:            qvattivita.js                                            *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvattivita(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var prefix="#"+formid;
    var flagsuspend=false;
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            oper_refresh.engage()
        }
    });
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:590,
        top:offsety,
        width:120,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=_likeescapize(txf_search.value());

            q+="CONSISTENCY=0";
            q+=" AND STATUS=0";
            q+=" AND AVAILABILITY<2";
            q+=" AND PRATICASTATUS=0";
            q+=" AND TARGETUTENTEEGO='"+_sessioninfo.userid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";

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
    
    offsety+=35;
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:offsety,
        width:700,
        height:300,
        numbered:true,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_ATTIVITAPRATICA",
        orderby:"TARGETTIME,SYSID",
        columns:[
            {id:"IMPORTANZA", caption:"", width:20},
            {id:"DESCRIPTION", caption:"Descrizione", width:190},
            {id:"BOW", caption:"Richiedente", width:190},
            {id:"TARGETTIME", caption:"Scadenza", width:90, type:"/"}
        ],
        changerow:function(o,i){
            oper_open.enabled(0);
            oper_print.enabled(o.isselected());
            if(i>0){
                o.solveid(i);
            }
        },
        selchange:function(o, i){
            oper_print.enabled(o.isselected());
        },
        solveid:function(o, d){
            oper_open.enabled(1);
            oper_print.enabled(1);
        },
        enter:function(o){
            oper_open.engage();
        },
        before:function(o, d){
            for(var i in d){
                switch(d[i]["IMPORTANZA"]){
                case "0":
                    d[i]["IMPORTANZA"]=_iconLow();
                    break;
                case "1":
                    d[i]["IMPORTANZA"]="";
                    break;
                case "2":
                    d[i]["IMPORTANZA"]=_iconHigh()
                    break;
                }
            }
        }
    });
    offsety=410;

    var oper_open=$(prefix+"oper_open").rylabel({
        left:20,
        top:offsety,
        width:120,
        caption:"Apri",
        button:true,
        click:function(o){
            objgridsel.solveid(objgridsel.index(),
                function(g,id){
                    _openingparams="({environ:\""+_appname+"_"+_sessioninfo.role+"\",root:\""+_sessioninfo.roledescr+"\",attivita:\""+id+"\",form:\""+formid+"\"})";
                    RYWINZ.newform({
                        name:"qvinterazioni",
                        path:_cambusaURL+"../apps/corsaro/qvpratiche/",
                        title:"Interazioni"
                    });
                }
            );
        }
    });

    var oper_print=$(prefix+"oper_print").rylabel({
        left:590,
        top:offsety,
        width:120,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_attivita.php")
        }
    });

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
        tabs:[
            {title:"Selezione"}
        ],
        select:function(i,p){
            if(!flagsuspend){
                switch(i){
                case 1:
                    setTimeout(
                        function(){
                            oper_refresh.engage();
                        }, 1000
                    );
                    break;
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(1);
    
    // INIZIALIZZAZIONE FORM
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
    this._timer=function(){
        if(objtabs.currtab()==1){  
            oper_refresh.engage();
        }
    }
}

