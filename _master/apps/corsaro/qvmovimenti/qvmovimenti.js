/****************************************************************************
* Name:            qvmovimenti.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvmovimenti(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var currtitolareid="";
    var currbancaid="";
    var currsysid="";
    var currtypologyid=RYQUE.formatid("0MOVIMENTI00");
    var currgenretypeid=RYQUE.formatid("0MONEY000000");
    var curreuroid=RYQUE.formatid("0MONEYEURO0000");
    var currmotivetypeid=RYQUE.formatid("0CAUSALI0000");
    var currcontoid="";
    var currrounding=2;
    var currgenreid="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    var sospendirefresh=false;

    // FUORI TABS
    $(prefix+"lbf_titolare").rylabel({left:20, top:10, caption:"Titolare"});
    $(prefix+"txf_titolare").ryhelper({left:100, top:10, width:145, 
        formid:formid, table:"QW_ATTORI", title:"Titolari", multiple:false,
        open:function(o){
            o.where("");
        },
        assigned: function(o){
            currtitolareid=o.value();
        },
        clear:function(){
            currtitolareid="";
        }
    });
    $(prefix+"lbf_banca").rylabel({left:300, top:10, caption:"Banca"});
    var txf_banca=$(prefix+"txf_banca").ryhelper({left:350, top:10, width:145, 
        formid:formid, table:"QW_AZIENDE", title:"Banche", multiple:false,
        open:function(o){
            o.where("BANCA=1");
        },
        assigned: function(o){
            currbancaid=o.value();
        },
        clear:function(){
            currbancaid="";
        }
    });

    // DEFINIZIONE TAB SELEZIONE
    var offsety=80;
    
    // RICERCA MOVIMENTI
    $(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:offsety, width:450, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_genre").rylabel({left:20, top:offsety, caption:"Divisa*"});
    var txf_genre=$(prefix+"txf_genre").ryhelper({left:100, top:offsety, width:150, 
        formid:formid, table:"QVGENRES", title:"Divise", multiple:false,
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        assigned: function(){
            currgenreid=txf_genre.value();
            refreshselection();
        }
    });
    $(prefix+"lbf_motives").rylabel({left:300, top:offsety, caption:"Causali*"});
    var txf_motives=$(prefix+"txf_motives").ryhelper({left:400, top:offsety, width:150, 
        formid:formid, table:"QW_CAUSALI", title:"Causali", multiple:true,
        open:function(o){
            o.orderby("SOTTOTIPO,DESCRIPTION");
            o.where("");
        },
        assigned: function(){
            refreshselection();
        }
    });offsety+=30;
    
    $(prefix+"lbf_conto").rylabel({left:20, top:offsety, caption:"Conto"});
    var txf_conto=$(prefix+"txf_conto").ryhelper({left:100, top:offsety, width:150, 
        formid:formid, table:"QW_CONTI", title:"Conti", multiple:false,
        open:function(o){
            var q="";
            if(currtitolareid!=""){
                q="TITOLAREID='"+currtitolareid+"'";
            }
            if(currbancaid!=""){
                if(q!="")
                    q+=" AND ";
                q+="BANCAID='"+currbancaid+"'";
            }
            o.where(q);
        },
        assigned: function(o){
            currcontoid=o.value();
            refreshselection();
        }
    });
    $(prefix+"lbf_amount").rylabel({left:300, top:offsety, caption:"Importo &plusmn;5%"});
    var txf_amount=$(prefix+"txf_amount").rynumber({left:400, top:offsety,  width:150, numdec:0, 
        assigned:function(){
            refreshselection();
        }
    });
    offsety+=30;
    
    $(prefix+"lbf_datemin").rylabel({left:20, top:offsety, caption:"Data min"});
    var txf_datemin=$(prefix+"txf_datemin").rydate({left:100, top:offsety,  width:150, 
        assigned:function(){
            refreshselection();
        }
    });
    $(prefix+"lbf_datemax").rylabel({left:300, top:offsety, caption:"Data max"});
    var txf_datemax=$(prefix+"txf_datemax").rydate({left:400, top:offsety,  width:150, 
        assigned:function(){
            refreshselection();
        }
    });
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:630,
        top:80,
        width:80,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            objgridsel.clear()
            if(currbancaid!="" && currcontoid==""){
                return false;
            }
            var q="";
            var t=_likeescapize(txf_search.value());
            var genreid=currgenreid;
            var motiveid=txf_motives.value();
            var contoid=txf_conto.value();
            var datamin=txf_datemin.text();
            var datamax=txf_datemax.text();
            var amount=txf_amount.value();

            oper_new.enabled( genreid!="" && motiveid!="" );
            
            q="TYPOLOGYID='"+currtypologyid+"'";
            if(t!="")
                q+=" AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' )";
            if(genreid!="")
                q+=" AND GENREID='"+genreid+"'";
            if(motiveid!="")
                q+=" AND MOTIVEID IN ('"+motiveid.replace("|", "','")+"')";
            if(contoid!="")
                q+=" AND (BOWID='"+contoid+"' OR TARGETID='"+contoid+"')";
            if(datamin!="")
                q+=" AND AUXTIME>=[:TIME("+datamin+"000000)]";
            if(datamax!="")
                q+=" AND AUXTIME<=[:TIME("+datamax+"235959)]";
            if(amount>0)
                q+=" AND (AMOUNT>="+(amount*0.95)+" AND AMOUNT<="+(amount*1.05)+")";

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
        top:170,
        width:80,
        caption:"Pulisci",
        button:true,
        click:function(o){
            sospendirefresh=true;
            txf_search.clear();
            txf_genre.clear();
            txf_motives.clear();
            txf_conto.clear();
            txf_datemin.clear();
            txf_datemax.clear();
            txf_amount.clear();
            sospendirefresh=false;
            refreshselection();
        }
    });
    offsety+=35;
    
    var offsetx=20;
    
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
        from:"QW_MOVIMENTIJOIN",
        orderby:"AUXTIME DESC",
        limit:10000,
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:168},
            {id:"MOTIVE",caption:"Causale",width:150},
            {id:"AUXTIME",caption:"Data Reg.",width:90,type:"/"},
            {id:"BOWTIME",caption:"Data Valuta",width:90,type:"/"},
            {id:"TARGETTIME",caption:"",width:0,type:"/"},
            {id:"BOWID",caption:"",width:0},
            {id:"AMOUNT",caption:"Importo",width:120,type:"2"},
            {id:"GENRE",caption:"Divisa",width:90},
            {id:"STATUS",caption:"Stato",width:50, type:"?", formula:"[:BOOL(STATUS>0)]"},
            {id:"PHASE",caption:"Fase",width:50, type:"?", formula:"[:BOOL(PHASE>0)]"}
        ],
        changerow:function(o,i){
            if(i>0){
                o.solveid(i);
            }
            else{
                if(currsysid!=""){
                    objtabs.enabled(2,false);
                    objtabs.enabled(3,false);
                }
                currsysid="";
                oper_print.enabled(o.isselected());
                oper_delete.enabled(o.isselected());
            }
            context="";
        },
        selchange:function(o, i){
            oper_print.enabled(o.isselected());
            oper_delete.enabled(o.isselected());
        },
        solveid:function(o,d){
            oper_print.enabled(1);
            oper_delete.enabled(1);
            if(currsysid==""){
                currsysid=d;
                objtabs.enabled(2,true);
                objtabs.enabled(3,true);
            }
            else{
                currsysid=d;
            }
            if(flagopen){
                flagopen=false;
                objtabs.currtab(2);
            }
        },
        before:function(o,d){
            if(currcontoid!=""){
                for(var i in d){
                    if(d[i]["BOWID"]==currcontoid)
                        d[i]["AMOUNT"]="-"+d[i]["AMOUNT"];
                    else
                        d[i]["BOWTIME"]=d[i]["TARGETTIME"];
                }
            }
        },
        enter:function(){
            objtabs.currtab(2);
        }
    });
    
    offsety=500;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        width:120,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var motivoid=txf_motives.value().substr(0, RYQUE.lenid());
            var data = new Object();
            data["DESCRIPTION"]="(nuovo movimento)";
            data["TYPOLOGYID"]=currtypologyid;
            data["GENREID"]=currgenreid;
            data["MOTIVEID"]=motivoid;
            data["STATUS"]=-1;
            $.post(_cambusaURL+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessionid,
                    "env":_sessioninfo.environ,
                    "function":"arrows_insert",
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
    var oper_print=$(prefix+"oper_print").rylabel({
        left:220,
        top:offsety,
        width:120,
        caption:"Stampa selezione",
        button:true,
        click:function(o){
            qv_printselected(formid, objgridsel, "rep_arrows.php")
        }
    });
    var oper_delete=$(prefix+"oper_delete").rylabel({
        left:420,
        top:offsety,
        width:120,
        caption:"Elimina selezione",
        button:true,
        click:function(o){
            qv_bulkdelete(formid, objgridsel, "arrows");
        }
    });
    offsety+=40;

    $(prefix+"lb_warning").rylabel({left:20, top:offsety, caption:"* Campi obbligatori per abilitare l'inserimento"});

    // DEFINIZIONE TAB CONTESTO
    offsetx=340;
    offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Descrizione"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:100, datum:"C", tag:"DESCRIPTION"});
    offsety+=50;

    $(prefix+"LB_BOW").rylabel({left:120, top:offsety, caption:"<b>Dare</b>"});
    $(prefix+"LB_TARGET").rylabel({left:offsetx+100, top:offsety, caption:"<b>Avere</b>"});
    offsety+=30;
    
    $(prefix+"LB_OBJECTID").rylabel({left:20, top:offsety, caption:"Conto"});
    var txbow=$(prefix+"BOWID").ryhelper({
        left:120, top:offsety, width:180, datum:"C", tag:"BOWID", formid:formid, table:"QW_CONTI", title:"Conti",
        open:function(o){
            var q="REFGENREID='"+currgenreid+"'";
            if(currtitolareid!=""){
                q+=" AND TITOLAREID='"+currtitolareid+"'";
            }
            if(currbancaid!=""){
                q+=" AND BANCAID='"+currbancaid+"'";
            }
            o.where(q);
        }
    });
    var txtarget=$(prefix+"TARGETID").ryhelper({
        left:offsetx+100, top:offsety, width:180, datum:"C", tag:"TARGETID", formid:formid, table:"QW_CONTI", title:"Conti",
        open:function(o){
            var q="REFGENREID='"+currgenreid+"'";
            if(currtitolareid!=""){
                q+=" AND TITOLAREID='"+currtitolareid+"'";
            }
            if(currbancaid!=""){
                q+=" AND BANCAID='"+currbancaid+"'";
            }
            o.where(q);
        }
    });
    offsety+=30;
    
    $(prefix+"LB_TIME").rylabel({left:20, top:offsety, caption:"Data Valuta"});
    $(prefix+"BOWTIME").rydate({left:120, top:offsety, datum:"C", tag:"BOWTIME"});
    $(prefix+"TARGETTIME").rydate({left:offsetx+100, top:offsety, datum:"C", tag:"TARGETTIME"});
    offsety+=50;

    $(prefix+"LB_AUXTIME").rylabel({left:20, top:offsety, caption:"Data Reg."});
    $(prefix+"AUXTIME").rydate({left:120, top:offsety, datum:"C", tag:"AUXTIME"});
    offsety+=30;
    
    $(prefix+"LB_MOTIVEID").rylabel({left:20, top:offsety, caption:"Causale"});
    $(prefix+"MOTIVEID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"MOTIVEID", formid:formid, table:"QW_CAUSALI", title:"Causali",
        open:function(o){
            //o.where("SOTTOTIPO=0");
            o.orderby("SOTTOTIPO,DESCRIPTION");
            o.where("");
        }
    });offsety+=30;
    
    $(prefix+"LB_GENREID").rylabel({left:20, top:offsety, caption:"Divisa"});
    var tx_genreid=$(prefix+"GENREID").ryhelper({
        left:120, top:offsety, width:200, datum:"C", tag:"GENREID", formid:formid, table:"QVGENRES", title:"Divise",
        open:function(o){
            o.where("TYPOLOGYID='[=TYPOLOGYID]'");
            o.args({"TYPOLOGYID":currgenretypeid});
        },
        select:"ROUNDING",
        onselect:function(o, d){
            tx_amount.numdec( parseInt(d["ROUNDING"]) );
        }
    });
    tx_genreid.enabled(0);
    offsety+=30;
    
    $(prefix+"LB_AMOUNT").rylabel({left:20, top:offsety, caption:"Importo"});
    var tx_amount=$(prefix+"AMOUNT").rynumber({left:120, top:offsety, width:200, numdec:2, minvalue:0, datum:"C", tag:"AMOUNT"});offsety+=30;
    
    $(prefix+"LB_REFERENCE").rylabel({left:20, top:offsety, caption:"Riferimento"});
    $(prefix+"REFERENCE").rytext({left:120, top:offsety, width:200, datum:"C", tag:"REFERENCE"});offsety+=30;
    
    $(prefix+"LB_CONSISTENCY").rylabel({left:20, top:offsety, caption:"Concretezza"});
    $(prefix+"CONSISTENCY").rylist({left:120, top:offsety, width:200, datum:"C", tag:"CONSISTENCY"})
        .additem({caption:"", key:""})
        .additem({caption:"Effettiva", key:0})
        .additem({caption:"Equivalente", key:1})
        .additem({caption:"Simulata", key:2})
        .additem({caption:"Astratta", key:3});offsety+=30;

    $(prefix+"LB_STATUS").rylabel({left:20, top:offsety, caption:"Stato"});
    $(prefix+"STATUS").rylist({left:120, top:offsety, width:200, datum:"C", tag:"STATUS"})
        .additem({caption:"", key:""})
        .additem({caption:"Provvisorio", key:0})
        .additem({caption:"Completo", key:1})
        .additem({caption:"Verificato", key:2})
        .additem({caption:"Processato", key:3});

    $(prefix+"LB_STATUSTIME").rylabel({left:offsetx+20, top:offsety, caption:"Data"});
    $(prefix+"STATUSTIME").rydate({left:offsetx+100, top:offsety, datum:"C", tag:"STATUSTIME"});
    offsety+=30;

    $(prefix+"LB_PHASE").rylabel({left:20, top:offsety, caption:"Fase"}).enabled(false);
    $(prefix+"PHASE").rylist({left:120, top:offsety, width:200})
        .additem({caption:"Non inviato", key:0})
        .additem({caption:"Inviato", key:1})
        .additem({caption:"Accettato", key:2})
        .additem({caption:"Rifiutato", key:3})
        .enabled(false);
    $(prefix+"LB_PHASENOTE").rylabel({left:offsetx+20, top:offsety, caption:"Note"}).enabled(false);
    $(prefix+"PHASENOTE").rytext({left:offsetx+100, top:offsety, width:300}).enabled(false);
    offsety+=30;

    $(prefix+"LB_PROVIDER").rylabel({left:20, top:offsety, caption:"Origine"}).enabled(false);
    $(prefix+"PROVIDER").rytext({left:120, top:offsety, width:200}).enabled(false);
    $(prefix+"LB_PARCEL").rylabel({left:offsetx+20, top:offsety, caption:"Lotto"}).enabled(false);
    $(prefix+"PARCEL").rytext({left:offsetx+100, top:offsety, width:200}).enabled(false);
    offsety+=30;
    
    $(prefix+"LB_TAG").rylabel({left:20, top:offsety, caption:"Marche"});
    $(prefix+"TAG").rytext({left:120, top:offsety, width:300, datum:"C", tag:"TAG"});
    offsety+=30;
    
    $(prefix+"LB_REGISTRY").rylabel({left:20, top:offsety, caption:"Note"});offsety+=30;
    $(prefix+"REGISTRY").ryedit({left:20, top:offsety, width:700, height:400, datum:"C", tag:"REGISTRY"});
    
    var oper_contextengage=$(prefix+"oper_contextengage").rylabel({
        left:680,
        top:60,
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
                    "function":"arrows_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            if(_isset(v.params["STATUSTIME"])){
                                globalobjs[formid+"STATUSTIME"].value(v.params["STATUSTIME"]);
                            }
                            if(_isset(v.params["STATUS"])){
                                globalobjs[formid+"STATUS"].setkey(v.params["STATUS"]);
                            }
                            RYWINZ.modified(formid, 0);
                        }
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

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVARROWS", "QW_MOVIMENTI");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:40,position:"relative",
        tabs:[
            {title:"Selezione"},
            {title:"Contesto"},
            {title:"Documenti"}
        ],
        select:function(i,p){
            if(p==2){
                // PROVENGO DAI DATI
                flagsuspend=qv_changemanagement(formid, objtabs, oper_contextengage, {
                    abandon:function(){
                        loadedsysid="";
                    }
                });
            }
            if(i==1){
                loadedsysid="";
            }
            else if(i==2){
                if(currsysid==loadedsysid){
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
                    qv_maskclear(formid, "C");
                    RYQUE.query({
                        sql:"SELECT * FROM QW_MOVIMENTI WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            qv_object2mask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            currgenreid=v[0]["GENREID"];
                            loadedsysid=currsysid;
                            castFocus(prefix+"DESCRIPTION");
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVARROWS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
                        done:function(d){
                            context=d;
                            filemanager.caption("Contesto: "+context);
                        }
                    });
                }
            }
            flagsuspend=false;
        }
    });
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            txf_genre.value(curreuroid, true);
            winzClearMess(formid);
            txf_search.focus();
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
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new, xfocus:"DESCRIPTION", xengage:oper_contextengage, files:3} );
}

