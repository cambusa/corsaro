/****************************************************************************
* Name:            qvstatistiche.js                                         *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvstatistiche(settings,missing){
    var formid=RYWINZ.addform(this);
    winzProgress(formid);

    var prefix="#"+formid;
    var currsiteid="";
    
    // DEFINIZIONE TAB STATISTICHE
    
    offsety=80;
    $(prefix+"LB_SITEID").rylabel({left:20, top:offsety, caption:"Sito"});
    var tx_siteid=$(prefix+"SITEID").ryhelper({
        left:60, top:offsety, width:160, formid:formid, table:"QW_WEBSITES", title:"Scelta sito",
        open:function(o){
            o.where("");
        },
        assigned:function(o){
            currsiteid=o.value();
            oper_refresh.enabled(1);
            oper_reset.enabled(1);
            setTimeout(function(){oper_refresh.engage()}, 100);
        },
        clear:function(){
            currsiteid="";
            oper_refresh.enabled(0);
            oper_reset.enabled(0);
            $(prefix+"view_users").html("").css({"display":"none"});
            $(prefix+"view_pages").html("").css({"display":"none"});
            $(prefix+"view_files").html("").css({"display":"none"});
        }
    });
    $(prefix+"LB_ANNO").rylabel({left:250, top:offsety, caption:"Anno 20"});
    var y=(new Date()).getFullYear()-2000;
    var tx_anno=$(prefix+"ANNO").rynumber({left:310, top:offsety, width:45, numdec:0, minvalue:14, maxvalue:y,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    });
    tx_anno.value(y);

    $(prefix+"LB_MESE").rylabel({left:390, top:offsety, caption:"Mese"});
    var tx_mese=$(prefix+"MESE").rylist({left:430, top:offsety, width:100,
        assigned:function(){
            setTimeout(function(){oper_refresh.engage()}, 100);
        }
    })
    .additem({caption:"Gennaio", key:0})
    .additem({caption:"Febbraio", key:1})
    .additem({caption:"Marzo", key:2})
    .additem({caption:"Aprile", key:3})
    .additem({caption:"Maggio", key:4})
    .additem({caption:"Giugno", key:5})
    .additem({caption:"Luglio", key:6})
    .additem({caption:"Agosto", key:7})
    .additem({caption:"Settembre", key:8})
    .additem({caption:"Ottobre", key:9})
    .additem({caption:"Novembre", key:10})
    .additem({caption:"Dicembre", key:11});
    tx_mese.setkey((new Date).getMonth());
    
    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:580,
        top:offsety,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var y=2000+parseInt(tx_anno.value());
            var m=parseInt(tx_mese.key());
            var dend=new Date(y, m+1, 0);
            sin=y+strRight("00"+(m+1), 2)+"01";
            var gg=dend.getDate();
            sfi=y+strRight("00"+(m+1), 2)+strRight("00"+gg, 2);
            RYQUE.query({
                sql:"SELECT COUNT(*) AS CNT,AUXTIME FROM QW_WEBSTATISTICS WHERE SITEID='"+currsiteid+"' AND AUXTIME>=[:DATE("+sin+")] AND AUXTIME<=[:DATE("+sfi+")] AND USERID<>'' AND USERID<>'@' GROUP BY AUXTIME,USERID",
                ready:function(v){
                    tracciautenti(v, gg);
                    RYQUE.query({
                        sql:"SELECT {AS:TOP 100} COUNT(*) AS CNT,QVARROWS.DESCRIPTION AS DESCRIPTION FROM QW_WEBSTATISTICS INNER JOIN QVARROWS ON QVARROWS.SYSID=QW_WEBSTATISTICS.CONTENTID WHERE QW_WEBSTATISTICS.SITEID='"+currsiteid+"' AND QW_WEBSTATISTICS.AUXTIME>=[:DATE("+sin+")] AND QW_WEBSTATISTICS.AUXTIME<=[:DATE("+sfi+")] AND QW_WEBSTATISTICS.CONTENTID<>'' GROUP BY QW_WEBSTATISTICS.CONTENTID {O: AND ROWNUM=100} ORDER BY CNT DESC {LM:LIMIT 100}{D:FETCH FIRST 100 ROWS ONLY}",
                        ready:function(v){
                            tracciapagine(v, 0);
                            RYQUE.query({
                                sql:"SELECT {AS:TOP 100} COUNT(*) AS CNT,QVFILES.DESCRIPTION AS DESCRIPTION FROM QW_WEBSTATISTICS INNER JOIN QVFILES ON QVFILES.SYSID=QW_WEBSTATISTICS.FILEID WHERE QW_WEBSTATISTICS.SITEID='"+currsiteid+"' AND QW_WEBSTATISTICS.AUXTIME>=[:DATE("+sin+")] AND QW_WEBSTATISTICS.AUXTIME<=[:DATE("+sfi+")] AND QW_WEBSTATISTICS.FILEID<>'' GROUP BY QW_WEBSTATISTICS.FILEID {O: AND ROWNUM=100} ORDER BY CNT DESC {LM:LIMIT 100}{D:FETCH FIRST 100 ROWS ONLY}",
                                ready:function(v){
                                    tracciapagine(v, 1);
                                }
                            });
                        }
                    });
                }
            });
        }
    });
    oper_refresh.enabled(0);
    
    var oper_reset=$(prefix+"oper_reset").rylabel({
        left:680,
        top:offsety,
        caption:"Reset",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare le statistiche del sito selezionato?<br>Confermando non saranno pi&ugrave; disponibili i dati per il reporting!",
                confirm:function(){
                    winzProgress(formid);
                    $.post(_cambusaURL+"ryquiver/quiver.php", 
                        {
                            "sessionid":_sessionid,
                            "env":_sessioninfo.environ,
                            "function":"statistics_reset",
                            "data":{
                                "SITEID":currsiteid
                            }
                        }, 
                        function(d){
                            try{
                                var v=$.parseJSON(d);
                                if(v.success>0){
                                    oper_refresh.engage();
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
    oper_reset.enabled(0);
    
    $(prefix+"view_users").css({"position":"absolute", "left":20, "top":160, "display":"none"});
    $(prefix+"view_pages").css({"position":"absolute", "left":20, "top":400, "display":"none"});
    $(prefix+"view_files").css({"position":"absolute", "left":600, "top":400, "display":"none"});
    
    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
        tabs:[
            {title:"Statistiche"}
        ]
    });
    objtabs.currtab(1);

    function tracciautenti(v, gg){
        var users=[];
        var days=[];
        for(var i=0;i<gg;i++){
            days[i]=(i+1);
            users[i]=0;
        }
        for(var g in v){
            var d=parseInt(v[g]["AUXTIME"].replace(/[- ]/g,"").substr(6,2));
            users[d-1]+=1;
        }
        $(prefix+"view_users").css({"display":"block"});
        $(prefix+"view_users").rygram({
            left:20,
            top:160,
            width:0,
            height:200,
            barwidth:20,
            values:users,
            captions:days,
            title:"Lettori",
            captionx:"Tempo",
            captiony:"Valutazione"
        });
    }
    function tracciapagine(v, rep){
        var pages=v.length;
        var h="";
        if(rep==0){
            sez="view_pages";
            tit="Pagine";
            descr="visite";
        }
        else{
            sez="view_files";
            tit="Download";
            descr="download";
        }
        if(pages>0){
            var tot=0;
            for(var i=0;i<pages;i++){
                tot+=_getinteger(v[i]["CNT"]);
            }
            h+="<table>";
            h+="  <tr style='border-bottom:1px dashed silver;'>";
            h+="  <th><div style='font-size:16px;padding-right:20px;'>"+tit+"</div></th><th><div style='font-size:16px;width:100px;text-align:right;white-space:nowrap;'>"+tot+" "+descr+"</div></th>";
            h+="  </tr>";
            for(var i=0;i<pages;i++){
                var n=parseInt(v[i]["CNT"]);
                var d=v[i]["DESCRIPTION"];
                if(d.length>40){
                   d=d.substr(0, 40)+"...";
                }
                h+="  <tr style='border-bottom:1px dashed silver;'>";
                h+="  <td><div style='padding-right:20px;'>"+d+"</div></td><td><div style='text-align:right;'>"+n+"</div></td>";
                h+="  </tr>";
            }
            h+="</table>";
        }
        else{
            if(rep==0)
                h="(nessuna visita)";
            else
                h="(nessun download)";
                
        }
        h+="<br/><br/><br/>";
        $(prefix+sez).html(h).css({"display":"block"});
    }
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            winzClearMess(formid);
        }
    );
}

