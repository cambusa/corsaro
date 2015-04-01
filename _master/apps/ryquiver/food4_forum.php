<?php 
/****************************************************************************
* Name:            food4_forum.php                                          *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $tocambusa."ryquiver/quiversex.php";
function forumtree($maestro, $REFID){
    global $SITEID, $site, $env;

    $food="";
    
    $food.="<div class='filibuster-forum'>";
    
    // INTESTAZIONE
    $food.="<div class='filibuster-forum-header'>";
    $food.="<span class='filibuster-forum-name'></span>&nbsp;";
    $food.="<a class='filibuster-forum-login' href='javascript:' onclick='flb_forumLogin(this)'>Login</a>";
    $food.="<a class='filibuster-forum-logout' href='javascript:' onclick='flb_forumLogout(this)'>Logout</a>";
    $food.="</div>";
    
    $PARENTID="";
    $GRANPARENTID="";
    
    // PARENT
    maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTFIELD='SETRELATED' AND SELECTEDID='$REFID' ORDER BY SORTER", $r);
    if(count($r)>0){
        $PARENTSET=$r[0]["PARENTID"];
        maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SETRELATED='$PARENTSET' AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID')", $r);
        if(count($r)>0){
            $PARENTID=$r[0]["SYSID"];
            // GRANPARENT
            maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTFIELD='SETRELATED' AND SELECTEDID='$PARENTID' ORDER BY SORTER", $g);
            if(count($g)>0){
                $GRANPARENTSET=$g[0]["PARENTID"];
                maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SETRELATED='$GRANPARENTSET' AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID')", $g);
                if(count($g)>0){
                    $GRANPARENTID=$g[0]["SYSID"];
                }
            }
            $food.=_postappend($r[0], "filibuster-forum-parent", $GRANPARENTID);
        }
    }
    
    // CORRENTE
    maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SYSID='$REFID'", $r);
    if(count($r)==1){
        $food.=_postappend($r[0], "filibuster-forum-current", $PARENTID);
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
                $food.=_postappend($r[0], "filibuster-forum-children", $REFID);
            }
        }
    }

    $food.="<div class='filibuster-forum-iframe'>";
    $food.="<div class='filibuster-forum-cancel'><a href='javascript:' onclick='flb_forumCancel()'>×</a></div>";
    $food.="<iframe src='../corsaro/flb_forum.php?environ=$env&sitename=$site&pageid=$REFID' style='width:750px;height:620px;border:1px solid gray;' frameborder='0'></iframe>";
    $food.="</div>";
    
    $food.="</div>";
    
    return $food;
}

function _postappend($r, $cl, $PARENTID){
    global $specials, $elementid;

    $food="";
    
    $SYSID=$r["SYSID"];
    $DESCRIPTION=$r["DESCRIPTION"];
    $ABSTRACT=$r["ABSTRACT"];
    $REGISTRY=$r["REGISTRY"];
    $AUXTIME=flb_formatdate($r["AUXTIME"]);
    $CONTENTTYPE=strtolower($r["CONTENTTYPE"]);
    $USERINSERTID=$r["USERINSERTID"];

    $food.="<div class='filibuster-wysiwyg filibuster-forum-post $cl $specials' id='$elementid-$SYSID' _sysid='$SYSID' _parentid='$PARENTID'>";

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
    
    $food.="<div class='filibuster-forum-tools'>";
    if(substr($r["NAME"], 0, 2)=="__"){
        $food.="<a class='filibuster-forum-comment' href='javascript:' onclick='flb_forumComment(this)'>Comment&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>";
        $food.="<a class='filibuster-forum-edit filibuster-forum-disabled' href='javascript:' onclick='flb_forumEdit(this)' _userid='$USERINSERTID'>Edit&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>";
        $food.="<a class='filibuster-forum-delete filibuster-forum-disabled' href='javascript:' onclick='flb_forumDelete(this)' _userid='$USERINSERTID'>Delete&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>";
    }
    else{
        $food.="<span class='filibuster-forum-comment filibuster-forum-disabled'>Comment&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
        $food.="<span class='filibuster-forum-edit filibuster-forum-disabled'>Edit&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
        $food.="<span class='filibuster-forum-delete filibuster-forum-disabled'>Delete&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
    }
    $food.="</div>";

    $food.="</div>";
    
    return $food;
}
?>