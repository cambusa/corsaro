/****************************************************************************
* Name:            qv_m_banche.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qv_m_banche(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    winzProgress(formid);

    var currsysid="";
    var currtypologyid="";
    var context="";
    var prefix="#"+formid;
    var flagopen=false;
    var flagsuspend=false;
    var loadedsysid="";
    
    // DEFINIZIONE TAB SELEZIONE
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").rytable({
        top:140,
        numbered:true,
        checkable:true,
        columns:[
            {id:"DESCRIPTION",caption:"Descrizione",width:200},
			{id:"TIMEINSERT",caption:"Tempo",width:200,type:"/"},
			{id:"NUMERO",caption:"Numero",width:200, type:"2"}
			
        ],
        changerow:function(o,i){
			objtabs.enabled(2, false);
			objtabs.enabled(3, false);
            currsysid="";
            context="";
        },
        enter:function(o, r){
			currsysid=o.cells(r, "SYSID");
			//context=o.cells(r, "SYSID");
			objtabs.enabled(2, true);
			objtabs.enabled(3, true);
            objtabs.currtab(2);
        }
    });
    var offsety=40;
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:offsety, caption:"Ricerca"});offsety+=20;
    var txf_search=$(prefix+"txf_search").rytext({left:20, top:offsety, width:300, 
        assigned:function(){
            oper_refresh.engage()
        }
    });offsety+=30;
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:20,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o, done){
            var q="";
            var t=qv_forlikeclause(txf_search.value());

            currtypologyid=RYQUE.formatid("0AZIENDE0000");

            q="SELECT * FROM QW_AZIENDE WHERE TYPOLOGYID='"+currtypologyid+"' AND BANCA=1 ";
            if(t!="")
                q+="AND ( [:UPPER(DESCRIPTION)] LIKE '%[=DESCRIPTION]%' OR [:UPPER(TAG)] LIKE '%[=TAG]%' ) ";
			q+="ORDER BY DESCRIPTION"
			
            RYQUE.query({
				sql:q,
                args:{
                    "DESCRIPTION":t,
                    "TAG":t
                },
                ready:function(v){
					for(var i in v)
						v[i]["NUMERO"]=10000*Math.random();
					objgridsel.setmatrix(v);
                    if(done!=missing){done()}
                }
            });
        }
    });

    // DEFINIZIONE TAB CONTESTO
    var offsety=60;
    $(prefix+"LB_DESCRIPTION").rylabel({left:20, top:offsety, caption:"Rag. Sociale"});
    var txdescr=$(prefix+"DESCRIPTION").rytext({left:120, top:offsety, width:300, maxlen:200, datum:"C", tag:"DESCRIPTION"});
    offsety+=30;
    
    $(prefix+"LB_INDIRIZZO").rylabel({left:20, top:offsety, caption:"Indirizzo"});
    $(prefix+"INDIRIZZO").rytext({left:120, top:offsety, width:240, maxlen:100, datum:"C", tag:"INDIRIZZO"});
    $(prefix+"CIVICO").rytext({left:380, top:offsety, width:40, datum:"C", tag:"CIVICO"});
    offsety+=30;
    
    $(prefix+"LB_CAP").rylabel({left:20, top:offsety, caption:"C.A.P."});
    $(prefix+"CAP").rytext({left:120, top:offsety, width:60, maxlen:20, datum:"C", tag:"CAP"});
    
    $(prefix+"LB_CITTA").rylabel({left:200, top:offsety, caption:"Citt&agrave;"});
    $(prefix+"CITTA").rytext({left:240, top:offsety, width:250, maxlen:50, datum:"C", tag:"CITTA"});
    
    $(prefix+"LB_PROVINCIA").rylabel({left:520, top:offsety, caption:"Provincia"});
    $(prefix+"PROVINCIA").rytext({left:590, top:offsety, width:40, maxlen:30, datum:"C", tag:"PROVINCIA"});
    
    $(prefix+"oper_cerca").rylabel({
        left:640,
        top:offsety,
        width:70,
        caption:"Cerca...",
        button:true,
        click:function(o){
            winzGeography(formid,
                {
                    "type":"comuni",
                    "onselect":function(d){
                        globalobjs[formid+"CAP"].value(d["CAP"], true);
                        globalobjs[formid+"CITTA"].value(d["DESCRIPTION"], true);
                        globalobjs[formid+"PROVINCIA"].value(d["SIGLA"], true);
                    }
                }
            );
        }
    });

    offsety+=30;
    $(prefix+"LB_NAZIONE").rylabel({left:20, top:offsety, caption:"Nazione"});
    $(prefix+"NAZIONE").rytext({left:120, top:offsety, width:200, datum:"C", tag:"NAZIONE"});
    offsety+=30;

    $(prefix+"LB_ABI").rylabel({left:20, top:offsety, caption:"A.B.I."});
    $(prefix+"ABI").rytext({left:120, top:offsety, width:300, maxlen:5, datum:"C", tag:"ABI"});
    offsety+=30;
    
    $(prefix+"LB_CAB").rylabel({left:20, top:offsety, caption:"C.A.B."});
    $(prefix+"CAB").rytext({left:120, top:offsety, width:300, maxlen:5, datum:"C", tag:"CAB"});
    offsety+=30;
    
    $(prefix+"LB_TELEFONO").rylabel({left:20, top:offsety, caption:"Telefono"});
    $(prefix+"TELEFONO").rytext({left:120, top:offsety, width:200, maxlen:30, datum:"C", tag:"TELEFONO"});
    $(prefix+"LB_FAX").rylabel({left:385, top:offsety, caption:"Fax"});
    $(prefix+"FAX").rytext({left:430, top:offsety, width:200, maxlen:30, datum:"C", tag:"FAX"});
    offsety+=30;
    
    $(prefix+"LB_EMAIL").rylabel({left:20, top:offsety, caption:"Email"});
    $(prefix+"EMAIL").rytext({left:120, top:offsety, width:200, maxlen:50, datum:"C", tag:"EMAIL"});
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
            var data=RYWINZ.ToObject(formid, "C", currsysid);
            RYWINZ.Post(_systeminfo.relative.cambusa+"ryquiver/quiver.php", 
                {
                    "sessionid":_sessioninfo.sessionid,
                    "env":_sessioninfo.environ,
                    "function":"objects_update",
                    "data":data
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            RYWINZ.modified(formid, 0);
                            if(done!=missing){done()}
                        }
                        objgridsel.dataload();
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

    // DEFINIZIONE TAB DOCUMENTI
    var filemanager=new qv_filemanager(this, formid, "QVOBJECTS", "QW_AZIENDE");

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:0,
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
                    RYWINZ.MaskClear(formid, "C");
                    RYQUE.query({
                        sql:"SELECT * FROM QW_AZIENDE WHERE SYSID='"+currsysid+"'",
                        ready:function(v){
                            RYWINZ.ToMask(formid, "C", v[0]);
                            context=v[0]["DESCRIPTION"];
                            loadedsysid=currsysid;
                            castFocus(prefix+"DESCRIPTION");
                        }
                    });
                    break;
                case 3:
                    // CARICAMENTO DOCUMENTI
                    filemanager.initialize(currsysid, "Contesto: "+context, currtypologyid);
                    qv_contextmanagement(context, {sysid:currsysid, table:"QVOBJECTS", select:"DESCRIPTION", formula:"[=DESCRIPTION]",
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
    qv_titlebar(objtabs, settings);
    objtabs.currtab(1);
    objtabs.enabled(2,false);
    objtabs.enabled(3,false);
    
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
}

