/****************************************************************************
* Name:            proiezionesaldi_sel.js                                   *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_proiezionesaldi_sel(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    var currgenretypeid=RYQUE.formatid("0MONEY000000");
    var prefix="#"+formid;
    
    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    // DIVISE E TIPO SALDI
    $(prefix+"lbf_divise").rylabel({left:20, top:offsety, caption:"Divise"});
    var txf_divise=$(prefix+"txf_divise").ryhelper({left:100, top:offsety, width:120, 
        formid:formid, table:"QVGENRES", title:"Scelta divise", multiple:true,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        assigned: function(){
            setTimeout(function(){oper_refreshconti.engage();}, 100);
        }
    });

    $(prefix+"lbf_tiposaldi").rylabel({left:20, top:offsety+30, caption:"Tipo saldi"});
    var txf_tiposaldi=$(prefix+"txf_tiposaldi").rylist({left:100, top:offsety+30, width:120});
    txf_tiposaldi
        .additem({caption:"Provvisori", key:"P"})
        .additem({caption:"Certi", key:"C"})
        .additem({caption:"Verificati", key:"E"});

    // DATE INIZIO E FINE
    $(prefix+"lbf_inizio").rylabel({left:260, top:offsety, caption:"Inizio"});
    var txf_inizio=$(prefix+"txf_inizio").rydate({left:310, top:offsety, width:110, 
        assigned:function(o){
            var fine=o.value();
            fine.setMonth(fine.getMonth()+1);
            txf_fine.value(fine);
        }
    });
    setTimeout(
        function(){
            txf_inizio.value(new Date(), true);
        }
    );
        
    $(prefix+"lbf_fine").rylabel({left:260, top:offsety+30, caption:"Fine"});
    var txf_fine=$(prefix+"txf_fine").rydate({left:310, top:offsety+30, width:110});
        
    // TITOLARI E BANCHE
    $(prefix+"lbf_titolari").rylabel({left:520, top:offsety, caption:"Titolari"});
    var txf_titolari=$(prefix+"txf_titolari").ryhelper({left:580, top:offsety, width:150, 
        formid:formid, table:"QW_ATTORI", title:"Titolari", multiple:true,
        open:function(o){
            o.where("");
        },
        assigned: function(){
            setTimeout(function(){oper_refreshconti.engage();}, 100);
        }
    });
    $(prefix+"lbf_banche").rylabel({left:520, top:offsety+30, caption:"Banche"});
    var txf_banche=$(prefix+"txf_banche").ryhelper({left:580, top:offsety+30, width:150, 
        formid:formid, table:"QW_AZIENDE", title:"Banche", multiple:true,
        open:function(o){
            o.where("BANCA=1");
        },
        assigned: function(){
            setTimeout(function(){oper_refreshconti.engage();}, 100);
        }
    });

    // ELENCO CONTI
    offsety+=70;
    var gridconti=$(prefix+"gridconti").ryque({
        left:20,
        top:offsety,
        width:400,
        height:200,
        numbered:false,
        checkable:true,
        environ:_sessioninfo.environ,
        from:"QW_CONTI",
        orderby:"DESCRIPTION",
        columns:[
            {id:"DESCRIPTION",caption:"Conti",width:200}
        ]
    });

    $(prefix+"lbf_searchconti").rylabel({left:430, top:offsety, caption:"Ricerca"});
    offsety+=20;
    var txf_searchconti=$(prefix+"txf_searchconti").rytext({left:430, top:offsety, width:300, 
        assigned:function(){
            setTimeout(function(){oper_refreshconti.engage()},100);
        }
    });
    
    offsety+=30;
    $(prefix+"lbf_classiconto").rylabel({left:430, top:offsety, caption:"Classi"});
    offsety+=20;
    var txf_classiconto=$(prefix+"txf_classiconto").ryhelper({left:430, top:offsety, width:300, 
        formid:formid, table:"QW_CLASSICONTO", title:"Classi conto", multiple:true,
        open:function(o){
            o.where("");
        },
        assigned:function(){
            setTimeout(function(){oper_refreshconti.engage()},100);
        },
        clear:function(){
            setTimeout(function(){oper_refreshconti.engage()},100);
        }
    });
    
    offsety+=40;
    var oper_refreshconti=$(prefix+"oper_refreshconti").rylabel({
        left:430,
        top:offsety,
        width:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_searchconti.value());
            var divise=txf_divise.value();
            if(divise!=""){
                divise="'"+divise.replace(/\|/g, "','")+"'";
            }
            var titolari=txf_titolari.value();
            if(titolari!=""){
                titolari="'"+titolari.replace(/\|/g, "','")+"'";
            }
            var banche=txf_banche.value();
            if(banche!=""){
                banche="'"+banche.replace(/\|/g, "','")+"'";
            }
            var classiconto=txf_classiconto.value();
            if(classiconto!=""){
                classiconto="'"+classiconto.replace(/\|/g, "','")+"'";
            }
            if(t!=""){
                if(q!=""){q+=" AND "}
                q+="( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            }
            if(divise!=""){
                if(q!=""){q+=" AND "}
                q+="REFGENREID IN ("+divise+")";
            }
            if(titolari!=""){
                if(q!=""){q+=" AND "}
                q+="TITOLAREID IN ("+titolari+")";
            }
            if(banche!=""){
                if(q!=""){q+=" AND "}
                q+="BANCAID IN ("+banche+")";
            }
            if(classiconto!=""){
                if(q!=""){q+=" AND "}
                q+="SYSID IN (SELECT PARENTID FROM QVSELECTIONS WHERE SELECTEDID IN ("+classiconto+"))";
            }
            gridconti.where(q);
            gridconti.query({
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                },
                ready:function(){
                    if(done!=missing){
                        done();
                    }
                }
            });
        }
    });
    
    var oper_resetconti=$(prefix+"oper_resetconti").rylabel({
        left:640,
        top:offsety,
        width:80,
        caption:"Pulisci",
        button:true,
        click:function(o){
            txf_searchconti.value("");
            txf_titolari.value("");
            txf_banche.value("");
            txf_classiconto.value("");
            oper_refreshconti.engage();
        }
    });

    offsety=360;
    var oper_print=$(prefix+"oper_print").rylabel({
        left:20,
        top:offsety,
        width:80,
        caption:"Estrazione",
        button:true,
        click:function(o){
            preparazioneparametri(
                function(params){
                    winzProgress(formid);
                    RYWINZ.Post(_systeminfo.relative.cambusa+"rygeneral/customize.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "path":"corsaro/reporting/saldi/proiezionesaldi_rep.php",
                            "data":params
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    var env=v.params["ENVIRON"];
                                    var f=v.params["PATHNAME"];
                                    if(window.console){console.log("Risposta da backoffice: "+env+"/"+f)}
                                    var h=_systeminfo.relative.cambusa+"rysource/source_download.php?env="+env+"&sessionid="+_sessioninfo.sessionid+"&file="+f;
                                    $("#winz-iframe").prop("src", h);
                                    // GESTIONE FILE OBSOLETI
                                    RYQUIVER.ManageTemp();
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
            );
        }
    });

    var oper_view=$(prefix+"oper_view").rylabel({
        left:120,
        top:offsety,
        width:80,
        caption:"Grafico",
        button:true,
        click:function(o){
            preparazioneparametri(
                function(params){
                    winzProgress(formid);
                    RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessioninfo.sessionid,
                            "env":_sessioninfo.environ,
                            "function":"saldi_proiezione",
                            "data":params
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    impaginagrafico(v.params["SALDICONTI"]);
                                    objtabs.currtab(2);
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
            );
        }
    });

    // DEFINIZIONE TAB GRAFICO
    offsety=80;
    $(prefix+"viewhandle").css({"position":"absolute", left:10, top:offsety});

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
        tabs:[
            {title:"Selezione"},
            {title:"Grafico"}
        ]
    });
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            oper_refreshconti.engage();
        }
    );
    function preparazioneparametri(azione){
        var divise=txf_divise.value();
        var tiposaldi=txf_tiposaldi.key();
        var inizio=txf_inizio.text();
        var fine=txf_fine.text();
        var titolari=txf_titolari.value();
        var banche=txf_banche.value();
        var conti="";
        if(inizio!="" && fine!=""){
            if(!gridconti.ischecked()){
                gridconti.checkall();
            }
            gridconti.selengage(
                function(o, conti){
                    var params={
                        "DIVISE":divise,
                        "TIPOSALDI":tiposaldi,
                        "INIZIO":inizio,
                        "FINE":fine,
                        "TITOLARI":titolari,
                        "BANCHE":banche,
                        "CONTI":conti
                    };
                    azione(params);
                }
            );
        }
        else if(inizio==""){
            winzMessageBox(formid, {
                message:"Specificare la data inizio",
                close:function(){
                    castFocus(prefix+"txf_inizio");
                }
            });
        }
        else if(fine==""){
            winzMessageBox(formid, {
                message:"Specificare la data fine",
                close:function(){
                    castFocus(prefix+"txf_fine");
                }
            });
        }
    }
    function impaginagrafico(saldiconto){
        var y=0;
        $(prefix+"viewhandle").html("");
        for(var conto in saldiconto){
            var graphid=formid+conto;
            var contodescr=saldiconto[conto]["DESCRIPTION"];
            var sviluppo=saldiconto[conto]["SALDI"];
            var i=0;
            var days=[];
            var saldi=[];
            for(var dt in sviluppo){
                days[i]=dt.substr(6,2)+"/"+dt.substr(4,2);
                saldi[i]=parseFloat(sviluppo[dt]);
                i+=1;
            }
            $(prefix+"viewhandle").append("<div id='"+graphid+"'></div>");
            $("#"+graphid).rygram({
                left:20,
                top:y,
                width:0,
                height:200,
                barwidth:15,
                barskip:5,
                values:saldi,
                captions:days,
                title:contodescr,
                captionx:"Tempo",
                captionrate:5,
                captiony:"Saldo"
            });
            y+=230;
        }
    }
}

