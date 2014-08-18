<?php 
/****************************************************************************
* Name:            food4_navigator.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function solvenavigator($maestro, $CONTENTID){
    global $site, $container_width;
    global $OPT_NAVHOME,$OPT_NAVPRIMARY,$OPT_NAVPARENTS,$OPT_NAVSIBLINGS,$OPT_NAVRELATED,$OPT_NAVTOOL,$OPT_NAVSORTING;
    global $DEFAULTID, $SITEID;

    $url=food4containerCorsaro()."filibuster.php";
    $food="<div class='filibuster-navigator'>";
    
    $index=array();
    $total=array();
    $flagrelated=false;
    
    // PRIMA PAGINA
    if($OPT_NAVHOME){
        maestro_query($maestro, "SELECT SYSID,DESCRIPTION,ABSTRACT,ICON FROM QW_WEBCONTENTS WHERE SYSID='$DEFAULTID'", $h);
        if(count($h)==1){
            $index[]=$DEFAULTID;
            $h[0]["CATEGORY"]=0;
            $total[]=$h[0];
            if($DEFAULTID==$CONTENTID){
                _navrelated($maestro, $CONTENTID, $index, $total, $flagrelated);
            }
            
            // VOCI PRINCIPALI
            if($OPT_NAVPRIMARY){
                $primary=_getrelated($maestro, $DEFAULTID);
                for($j=0;$j<count($primary);$j++){
                    $PRIMARYID=$primary[$j]["SYSID"];
                    $PRIMARYREL=$primary[$j]["SETRELATED"];
                    if(!in_array($PRIMARYID, $index)){
                        $index[]=$PRIMARYID;
                        $primary[$j]["CATEGORY"]=1;
                        $total[]=$primary[$j];
                        if($PRIMARYID==$CONTENTID){
                            _navrelated($maestro, $CONTENTID, $index, $total, $flagrelated, 1);
                        }
                        elseif(!in_array($CONTENTID, $index)){
                            maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTFIELD='SETRELATED' AND PARENTID='$PRIMARYREL' AND SELECTEDID='$CONTENTID'", $p);
                            if(count($p)>0){
                                // LA PAGINA CORRENTE E' FIGLIA DI UN PRIMARIO
                                $primary[$j]["CATEGORY"]=5;
                                $total[count($total)-1]["CATEGORY"]=5;
                                $parents=array($primary[$j]);
                                _navsiblings($maestro, $CONTENTID, $parents, $index, $total, $flagrelated);
                            }
                        }
                    }
                }
            }
        }
    }
    // TUTTI I PARENT
    $v=array();
    maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTFIELD='SETRELATED' AND SELECTEDID='$CONTENTID' ORDER BY SORTER", $p);
    for($i=0;$i<count($p);$i++){
        $v[]=$p[$i]["PARENTID"];
    }
    $PARENTLIST="'".implode($v, "','")."'";
    maestro_query($maestro, "SELECT SYSID,DESCRIPTION,ABSTRACT,ICON FROM QW_WEBCONTENTS WHERE SETRELATED IN ($PARENTLIST) AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID')", $parents);
    if($OPT_NAVPARENTS){
        for($i=0;$i<count($parents);$i++){
            if(!in_array($parents[$i]["SYSID"], $index)){
                $index[]=$parents[$i]["SYSID"];
                $parents[$i]["CATEGORY"]=2;
                $total[]=$parents[$i];
                if($parents[$i]["SYSID"]==$CONTENTID){
                    _navrelated($maestro, $CONTENTID, $index, $total, $flagrelated, 1);
                }
            }
        }
    }
    
    // TUTTI I FRATELLI
    _navsiblings($maestro, $CONTENTID, $parents, $index, $total, $flagrelated);

    // TUTTI I CORRELATI
    _navrelated($maestro, $CONTENTID, $index, $total, $flagrelated);

    if($OPT_NAVTOOL){
        switch($OPT_NAVSORTING){
        case 0:
            $SORTING="TARGETTIME";
            $CLAUSE="";
            break;
        case 1:
            $SORTING="[:UPPER(DESCRIPTION)]";
            $CLAUSE="AND SYSID<>'$DEFAULTID'";
            break;
        case 2:
            $SORTING="[:UPPER(TAG)]";
            $CLAUSE="AND TAG<>''";
            break;
        case 3:
            $SORTING="SORTER";
            $CLAUSE="AND SORTER>0";
            break;
        }
        $food.="<div class='filibuster-navigator-tool'>";

        // CLASSE DI DEFAULT
        $cfirst="filibuster-navigator-disabled";
        $cback="filibuster-navigator-disabled";
        $cforward="filibuster-navigator-disabled";
        $clast="filibuster-navigator-disabled";
        
        // COLLEGAMENTO DI DEFAULT
        $hfirst="";
        $hback="";
        $hforward="";
        $hlast="";

        // TITOLO DI DEFAULT
        $tfirst="";
        $tback="";
        $tforward="";
        $tlast="";

        // RICERCA DEL PRIMO
        maestro_query($maestro, "SELECT {AS:TOP 1} * FROM QW_WEBCONTENTS WHERE SCOPE=0 AND (SITEID='' OR SITEID='$SITEID') $CLAUSE {O: AND ROWNUM=1} ORDER BY $SORTING {LM:LIMIT 1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
        if(count($r)==1){
            $ITEMID=$r[0]["SYSID"];
            $ITEMDESCR=$r[0]["DESCRIPTION"];
            $ITEMABSTRACT=$r[0]["ABSTRACT"];
            if($ITEMID!=$CONTENTID){
                $tfirst=flb_titleformat($ITEMDESCR, $ITEMABSTRACT);
                $cfirst="filibuster-navigator-enabled";
                $hfirst="$url?env=".$maestro->environ."&site=$site&id=$ITEMID";
            }
        }

        maestro_query($maestro, "SELECT $SORTING AS FIELDSORTING FROM QW_WEBCONTENTS WHERE SYSID='$CONTENTID'", $p);
        if(count($p)==1){
            // RICERCA DEL PRECEDENTE
            switch($OPT_NAVSORTING){
            case 0:
                $COLLATION="[:TIME(".flb_strtime($p[0]["FIELDSORTING"]).")]";
                break;
            case 1:
                $COLLATION="'".strtoupper(ryqEscapize($p[0]["FIELDSORTING"]))."'";
                break;
            case 2:
                $COLLATION="'".strtoupper(ryqEscapize($p[0]["FIELDSORTING"]))."'";
                break;
            case 3:
                $COLLATION=intval($p[0]["FIELDSORTING"]);
                break;
            }
            maestro_query($maestro, "SELECT {AS:TOP 1} * FROM QW_WEBCONTENTS WHERE $SORTING<$COLLATION AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID') $CLAUSE {O: AND ROWNUM=1} ORDER BY $SORTING DESC {LM:LIMIT 1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
            if(count($r)==1){
                $ITEMID=$r[0]["SYSID"];
                $ITEMDESCR=$r[0]["DESCRIPTION"];
                $ITEMABSTRACT=$r[0]["ABSTRACT"];
                if($ITEMID!=$CONTENTID){
                    $tback=flb_titleformat($ITEMDESCR, $ITEMABSTRACT);
                    $cback="filibuster-navigator-enabled";
                    $hback="$url?env=".$maestro->environ."&site=$site&id=$ITEMID";
                }
            }
            
            // RICERCA DEL SUCCESSIVO
            maestro_query($maestro, "SELECT {AS:TOP 1} * FROM QW_WEBCONTENTS WHERE $SORTING>$COLLATION AND SCOPE=0  AND (SITEID='' OR SITEID='$SITEID') $CLAUSE {O: AND ROWNUM=1} ORDER BY $SORTING {LM:LIMIT 1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
            if(count($r)==1){
                $ITEMID=$r[0]["SYSID"];
                $ITEMDESCR=$r[0]["DESCRIPTION"];
                $ITEMABSTRACT=$r[0]["ABSTRACT"];
                if($ITEMID!=$CONTENTID){
                    $tforward=flb_titleformat($ITEMDESCR, $ITEMABSTRACT);
                    $cforward="filibuster-navigator-enabled";
                    $hforward="$url?env=".$maestro->environ."&site=$site&id=$ITEMID";
                }
            }
        }
        // RICERCA DELL'ULTIMO
        maestro_query($maestro, "SELECT {AS:TOP 1} * FROM QW_WEBCONTENTS WHERE SCOPE=0 AND (SITEID='' OR SITEID='$SITEID') $CLAUSE {O: AND ROWNUM=1} ORDER BY $SORTING DESC {LM:LIMIT 1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
        if(count($r)==1){
            $ITEMID=$r[0]["SYSID"];
            $ITEMDESCR=$r[0]["DESCRIPTION"];
            $ITEMABSTRACT=$r[0]["ABSTRACT"];
            if($ITEMID!=$CONTENTID){
                $tlast=flb_titleformat($ITEMDESCR, $ITEMABSTRACT);
                $clast="filibuster-navigator-enabled";
                $hlast="$url?env=".$maestro->environ."&site=$site&id=$ITEMID";
            }
        }
        
        // PARTI DEL TOOL
        $CHAR_BEGIN="&nbsp;&Alpha;&nbsp;";
        $CHAR_BACK="&nbsp;&#x21e6;&nbsp;";
        $CHAR_FORWARD="&nbsp;&#x21e8;&nbsp;";
        $CHAR_END="&nbsp;&Omega;&nbsp;";
        if($hfirst!="")
            $tfirst="<a class='filibuster-navigator-first $cfirst' href='$hfirst' title='$tfirst (CTRL-HOME)'>$CHAR_BEGIN</a>";
        else
            $tfirst="<span class='filibuster-navigator-first $cfirst'>$CHAR_BEGIN</span>";

        if($hback!="")
            $tback="<a class='filibuster-navigator-back $cback' href='$hback' title='$tback (CTRL-LEFT)'>$CHAR_BACK</a>";
        else
            $tback="<span class='filibuster-navigator-back $cback'>$CHAR_BACK</span>";

        if($hforward!="")
            $tforward="<a class='filibuster-navigator-forward $cforward' href='$hforward' title='$tforward (CTRL-RIGHT)'>$CHAR_FORWARD</a>";
        else
            $tforward="<span class='filibuster-navigator-forward $cforward'>$CHAR_FORWARD</span>";

        if($hlast)
            $tlast="<a class='filibuster-navigator-last $clast' href='$hlast' title='$tlast (CTRL-END)'>$CHAR_END</a>";
        else
            $tlast="<span class='filibuster-navigator-last $clast'>$CHAR_END</span>";
        
        // ACCODO
        $food.="$tfirst$tback$tforward$tlast";
        $food.="</div>";
    }
    
    // CREO GLI ITEM
    for($i=0; $i<count($total); $i++){
        $category="";
        $level="";
        switch($total[$i]["CATEGORY"]){
        case 0:
            $category="filibuster-home";
            break;
        case 1:
            $category="filibuster-primary";
            break;
        case 2:
            $category="filibuster-parents";
            break;
        case 3:
            $category="filibuster-siblings";
            $level="filibuster-levelA";
            break;
        case 4:
            $category="filibuster-related";
            switch($total[$i]["LEVEL"]){
            case 1:
                $level="filibuster-levelA";
                break;
            case 2:
                $level="filibuster-levelB";
                break;
            }
            break;
        case 5:
            $category="filibuster-primary filibuster-parents";
            break;
        }
        $food.=flb_createitem(
            $maestro, 
            $url, 
            $site, 
            $total[$i]["SYSID"], 
            $total[$i]["DESCRIPTION"], 
            $total[$i]["ICON"], 
            $total[$i]["ABSTRACT"],
            $CONTENTID,
            $category,
            $level
        );
    }
    
    $food.="</div>";
    return $food;
}

function _navsiblings($maestro, $CONTENTID, $parents, &$index, &$total, &$flagrelated){
    global $OPT_NAVSIBLINGS;
    if($OPT_NAVSIBLINGS){
        for($i=0;$i<count($parents);$i++){
            $siblings=_getrelated($maestro, $parents[$i]["SYSID"]);
            for($j=0;$j<count($siblings);$j++){
                if(!in_array($siblings[$j]["SYSID"], $index)){
                    $index[]=$siblings[$j]["SYSID"];
                    $siblings[$j]["CATEGORY"]=3;
                    $total[]=$siblings[$j];
                    if($siblings[$j]["SYSID"]==$CONTENTID){
                        _navrelated($maestro, $CONTENTID, $index, $total, $flagrelated, 2);
                    }
                }
            }
        }
    }
}

function _navrelated($maestro, $CONTENTID, &$index, &$total, &$flagrelated, $level=0){
    global $OPT_NAVRELATED;
    if($flagrelated==false){
        if($OPT_NAVRELATED){
            // TUTTI I CORRELATI
            $related=_getrelated($maestro, $CONTENTID);
            for($i=0;$i<count($related);$i++){
                if(!in_array($related[$i]["SYSID"], $index)){
                    $index[]=$related[$i]["SYSID"];
                    $related[$i]["CATEGORY"]=4;
                    $related[$i]["LEVEL"]=$level;
                    $total[]=$related[$i];
                }
            }
            $flagrelated=true;
        }
    }
}
?>