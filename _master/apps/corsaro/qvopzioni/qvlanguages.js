/****************************************************************************
* Name:            qvlanguages.js                                           *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_qvlanguages(settings,missing){
    var formid=RYWINZ.addform(this);
    var prefix="#"+formid;
    var currsysid="";

    // DEFINIZIONE TAB SELEZIONE
    
    var lbf_search=$(prefix+"lbf_search").rylabel({left:20, top:80, caption:"Ricerca"});
    var txf_search=$(prefix+"txf_search").rytext({left:100, top:80, width:400, 
        assigned:function(){
            oper_refresh.engage()
        }
    });

    var oper_refresh=$(prefix+"oper_refresh").rylabel({
        left:600,
        top:80,
        width:100,
        caption:"Aggiorna",
        button:true,
        click:function(o){
            var q="";
            var t=_likeescapize(txf_search.value());

            if(t!="")
                q+="( [:UPPER(CAPTION)] LIKE '%[=CAPTION]%' OR [:UPPER(NAME)] LIKE '%[=NAME]%' )";

            objgridsel.where(q);
            objgridsel.query({
                args:{
                    "CAPTION":t,
                    "NAME":t
                }
            });
        }
    });
    
    // GRID DI SELEZIONE
    var objgridsel=$(prefix+"gridsel").ryque({
        left:20,
        top:110,
        width:700,
        maxwidth:-1,
        height:300,
        numbered:false,
        checkable:false,
        environ:"italiano",
        from:"BABELITEMS",
        orderby:"NAME",
        columns:[
            {id:"NAME", caption:"Name", width:300},
            {id:"CAPTION", caption:"Caption", width:800}
        ],
        changerow:function(o, i){
            currsysid="";
            enabledata(0);
            oper_engage.enabled(0);
            oper_delete.enabled(0);
            if(i>0){
                o.solveid(i);
            }
        },
        solveid:function(o, d){
            currsysid=d;
            $.post(_cambusaURL+"rybabel/babel_action.php", 
                {
                    "sessionid":_sessionid,
                    "action":"select",
                    "default":"italiano",
                    "SYSID":currsysid,
                    "languages":["italiano", "english"]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            enabledata(1);
                            oper_engage.enabled(1);
                            oper_delete.enabled(1);
                            txname.value(v["NAME"]);
                            txita.value(v["italiano"]);
                            txeng.value(v["english"]);
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

    var offsety=400;
    var oper_new=$(prefix+"oper_new").rylabel({
        left:20,
        top:offsety,
        width:100,
        caption:"Nuovo",
        button:true,
        click:function(o){
            winzProgress(formid);
            var data = new Object();
            data["DESCRIPTION"]="(nuova opzione)";
            $.post(_cambusaURL+"rybabel/babel_action.php", 
                {
                    "sessionid":_sessionid,
                    "action":"new",
                    "default":"italiano",
                    "languages":["italiano", "english"]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){
                            var newid=v.SYSID;
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
    
    var oper_engage=$(prefix+"oper_engage").rylabel({
        left:180,
        top:offsety,
        width:100,
        caption:"Salva",
        button:true,
        click:function(o){
            winzProgress(formid);
            $.post(_cambusaURL+"rybabel/babel_action.php", 
                {
                    "sessionid":_sessionid,
                    "action":"update",
                    "default":"italiano",
                    "SYSID":currsysid,
                    "NAME":txname.value(),
                    "italiano":txita.value(),
                    "english":txeng.value(),
                    "languages":["italiano", "english"]
                }, 
                function(d){
                    try{
                        var v=$.parseJSON(d);
                        if(v.success>0){ 
                            objgridsel.dataload();
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
        left:600,
        top:offsety,
        width:100,
        caption:"Elimina",
        button:true,
        click:function(o){
            winzMessageBox(formid, {
                message:"Eliminare la voce selezionata?",
                confirm:function(){
                    $.post(_cambusaURL+"rybabel/babel_action.php", 
                        {
                            "sessionid":_sessionid,
                            "action":"delete",
                            "default":"italiano",
                            "SYSID":currsysid,
                            "languages":["italiano", "english"]
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
    
    offsety+=50;
    $(prefix+"LB_NAME").rylabel({left:20, top:offsety, caption:"Nome"});
    var txname=$(prefix+"NAME").rytext({left:100, top:offsety, width:300});
    
    offsety+=30;
    $(prefix+"LB_ITALIANO").rylabel({left:20, top:offsety, caption:"Italiano"});
    var txita=$(prefix+"ITALIANO").rytext({left:100, top:offsety, width:700});
    
    offsety+=30;
    $(prefix+"LB_ENGLISH").rylabel({left:20, top:offsety, caption:"English"});
    var txeng=$(prefix+"ENGLISH").rytext({left:100, top:offsety, width:700});

    // INIZIALIZZO I TABS
    var objtabs=$( prefix+"tabs" ).rytabs({
        top:10,position:"relative",
        tabs:[
            {title:"Dizionario", code:"LANG_DICTIONARY"}
        ]
    });
    
    objtabs.currtab(1);
    txf_search.focus();
    
    // INIZIALIZZAZIONE FORM
    RYBOX.localize(_sessioninfo.language, formid,
        function(){
            objgridsel.where("");
            objgridsel.query();
        }
    );
    function enabledata(f){
        if(!f){
            txname.clear();
            txita.clear();
            txeng.clear();
        }
        txname.enabled(f);
        txita.enabled(f);
        txeng.enabled(f);
    }
    winzKeyTools(formid, objtabs, {sfocus:"gridsel", srefresh:oper_refresh, snew:oper_new} );
}

