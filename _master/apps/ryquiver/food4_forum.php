<?php 
/****************************************************************************
* Name:            food4_forum.php                                          *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function forumtree($maestro, $REFID){
    global $SITEID;
    
    $food="";

    // PARENT
    maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTFIELD='SETRELATED' AND SELECTEDID='$REFID' ORDER BY SORTER", $r);
    if(count($r)>0){
        $PARENTID=$r[0]["PARENTID"];
        maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SETRELATED='$PARENTID' AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID')", $r);
        if(count($r)>0){
            $food.=_postappend($r[0], "filibuster-forum-parent");
        }
    }
    
    // CORRENTE
    maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SYSID='$REFID'", $r);
    if(count($r)==1){
        $food.=_postappend($r[0], "filibuster-forum-post");
    }
    
    // CORRELATI
    maestro_query($maestro, "SELECT SETRELATED FROM QW_WEBCONTENTS WHERE SYSID='$REFID'", $r);
    if(count($r)==1){
        $SETRELATED=$r[0]["SETRELATED"];
        // DETERMINO LE CHIAVI DEI CORRELATI
        maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTFIELD='SETRELATED' AND PARENTID='$SETRELATED' ORDER BY SORTER", $d);
        for($i=0; $i<count($d); $i++){
            $CHILDID=$d[$i]["SELECTEDID"];
            maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SYSID='$CHILDID' AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID')", $r);
            if(count($r)>0){
                $food.=_postappend($r[0], "filibuster-forum-children");
            }
        }
    }
    return $food;
}

function _postappend($r, $cl){
    global $specials, $elementid;

    $food="";
    
    $SYSID=$r["SYSID"];
    $DESCRIPTION=$r["DESCRIPTION"];
    $ABSTRACT=$r["ABSTRACT"];
    $REGISTRY=$r["REGISTRY"];
    $AUXTIME=flb_formatdate($r["AUXTIME"]);
    $CONTENTTYPE=strtolower($r["CONTENTTYPE"]);

    $food.="<div class='filibuster-wysiwyg filibuster-forum $cl $specials' id='$elementid'>";

    $food.="<div class='filibuster-forum-description'>";
    $food.="<a href='$SYSID'>";
    $food.=$DESCRIPTION;
    $food.="</a>";
    $food.="</div>";

    if($ABSTRACT==""){
        $ABSTRACT="&nbsp;";
    }
    $food.="<div class='filibuster-forum-abstract'>";
    $food.=$ABSTRACT;
    $food.="</div>";

    if($CONTENTTYPE!="wysiwyg"){
        $REGISTRY="&nbsp;";
    }
    $food.="<div class='filibuster-forum-content'>";
    $food.=$REGISTRY;
    $food.="</div>";

    $food.="<div class='filibuster-forum-sysid'>$SYSID</div>";
    
    $food.="</div>";
    
    return $food;
}
?>