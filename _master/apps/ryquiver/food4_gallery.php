<?php 
/****************************************************************************
* Name:            food4_gallery.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function solvegallery($maestro, $CONTENTID){
    global $site, $container_width;
    global $SITEID;
    $food="";
    $url=food4containerCorsaro()."filibuster.php";
    $heightcols=array(0, 0, 0);
    $maxheight=200;
    $food.="<div class='filibuster-gallery' style='height:######px;'>";
    $food.="<div class='filibuster-transform'>";
    $prevwidth=round($container_width/3);
    // DETERMINO IL PARENT DEI CORRELATI
    maestro_query($maestro, "SELECT SETRELATED FROM QW_WEBCONTENTS WHERE SYSID='$CONTENTID'", $c);
    if(count($c)==1){
        flb_dirattachment($maestro, $dirattachment, $urlattachment);
        $SETRELATED=$c[0]["SETRELATED"];
        $BULK=array();

        // DETERMINO I CORRELATI
        maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTID='$SETRELATED' ORDER BY SORTER", $r);
        for($i=0; $i<count($r); $i++){
            $SELECTEDID=$r[$i]["SELECTEDID"];
            // DETERMINO LA DESCRIZIONE DELLA GALLERIA
            maestro_query($maestro, "SELECT DESCRIPTION,ABSTRACT FROM QW_WEBCONTENTS WHERE SYSID='$SELECTEDID' AND SCOPE=0 AND (SITEID='' OR SITEID='$SITEID')", $g);
            if(count($g)==1){
                $title=flb_titleformat($g[0]["DESCRIPTION"], $g[0]["ABSTRACT"]);
                // DETERMINO GLI ALLEGATI
                maestro_query($maestro, "SELECT * FROM QWFILES WHERE RECORDID='$SELECTEDID' ORDER BY SORTER,AUXTIME DESC,FILEID DESC", $f);
                for($j=0; $j<count($f); $j++){
                    $SYSID=$f[$j]["FILEID"];
                    $SUBPATH=$f[$j]["SUBPATH"];
                    $IMPORTNAME=$f[$j]["IMPORTNAME"];
                    $path_parts=pathinfo($IMPORTNAME);
                    if(isset($path_parts["extension"]))
                        $ext="." . $path_parts["extension"];
                    else
                        $ext="";
                    $pathfile=$dirattachment.$SUBPATH.$SYSID.$ext;
                    $urltfile=$urlattachment.$SUBPATH.$SYSID.$ext;
                    
                    switch(strtolower($ext)){
                    case ".gif":
                    case ".jpg":
                    case ".jpeg":
                    case ".png":
                    case ".svg":
                        list($w, $h, $t, $a)=getimagesize($pathfile);
                        if($w>=200){
                            $col=0;
                            $left=0;
                            $top=$heightcols[0];
                            for($t=1;$t<3;$t++){
                                if($heightcols[$t]<$top){
                                    $col=$t;
                                    $left=$t*$prevwidth;
                                    $top=$heightcols[$t];
                                }
                            }
                            
                            $width=$w;
                            $height=$h;
                            $height=round($prevwidth*$h/$w);
                            $width=$prevwidth;

                            $heightcols[$col]+=$height;
                            
                            $BULK[]=array(
                                "SELECTEDID" => $SELECTEDID,
                                "FILEID" => $SYSID,
                                "URLFILE" => $urltfile,
                                "WIDTH" => $width,
                                "HEIGHT" => $height,
                                "ORIGINALH" => $height,
                                "TITLE" => $title,
                                "COLUMN" => $col
                            );
                        }
                    }
                }
            }
        }
        // OTTIMIZZAZIONE INCOLONNAMENTO
        do{
            $found=false;
            $maxheight=flb_galleryheight($BULK, $heightcols);
            for($i=0;$i<count($BULK);$i++){
                for($j=$i+1;$j<count($BULK);$j++){
                    if($BULK[$i]["COLUMN"]!=$BULK[$j]["COLUMN"]){
                        $TEST=$BULK;
                        $TEST[$i]["COLUMN"]=$BULK[$j]["COLUMN"];
                        $TEST[$j]["COLUMN"]=$BULK[$i]["COLUMN"];
                        $testh=flb_galleryheight($TEST, $testc);
                        if($testh<$maxheight){
                            $BULK=$TEST;
                            $maxheight=$testh;
                            $heightcols=$testc;
                            $found=true;
                        }
                    }
                }
            }
            if(!$found){
                break;
            }
        }while(true);
        
        // ADEGUAMENTO ALTEZZE
        $schiacciamento=0.95;
        $newmaxheight=round($schiacciamento*$maxheight)+1;
        for($i=0;$i<3;$i++){
            $y=0;
            if($heightcols[$i]>0){
                if($heightcols[$i]<$maxheight){
                    $ratio=$schiacciamento*$maxheight/$heightcols[$i];
                    if($ratio<1.5){
                        for($j=0;$j<count($BULK);$j++){
                            if($BULK[$j]["COLUMN"]==$i){
                                $BULK[$j]["HEIGHT"]=round($BULK[$j]["HEIGHT"]*$ratio);
                                $y+=$BULK[$j]["HEIGHT"];
                                if(abs($newmaxheight-$y)<10){
                                    $BULK[$j]["HEIGHT"]+=($newmaxheight-$y);
                                    $BULK[$j]["BOTTOM"]=true;
                                }
                            }
                        }
                    }
                }
                else{
                    for($j=0;$j<count($BULK);$j++){
                        if($BULK[$j]["COLUMN"]==$i){
                            $BULK[$j]["HEIGHT"]=round($BULK[$j]["HEIGHT"]*$schiacciamento);
                            $y+=$BULK[$j]["HEIGHT"];
                            if(abs($newmaxheight-$y)<10){
                                $BULK[$j]["HEIGHT"]+=($newmaxheight-$y);
                                $BULK[$j]["BOTTOM"]=true;
                            }
                        }
                    }
                }
            }
        }
        $maxheight=$newmaxheight;
        
        // USCITA
        $offsety=array(0, 0, 0);
        foreach($BULK as $IMG){
            $col=$IMG["COLUMN"];
            $left=$col*$prevwidth;
            $top=$offsety[$col];
            $offsety[$col]+=$IMG["HEIGHT"];
            
            $SELECTEDID=$IMG["SELECTEDID"];
            $FILEID=$IMG["FILEID"];
            $urltfile=$IMG["URLFILE"];
            $width=$IMG["WIDTH"];
            $height=$IMG["HEIGHT"];
            $originalh=$IMG["ORIGINALH"];
            $title=$IMG["TITLE"];
            
            $classtop="";
            $classmiddle="filibuster-image-middle";
            $classbottom="";
            if($top==0){
                $classtop="filibuster-image-top";
                $classmiddle="";
            }
            if(isset($IMG["BOTTOM"])){
                $classbottom="filibuster-image-bottom";
                $classmiddle="";
            }
            
            switch($col){
            case 0:
                $classcol="filibuster-image-left";
                break;
            case 1:
                $classcol="filibuster-image-center";
                break;
            case 2:
                $classcol="filibuster-image-right";
                break;
            }
            if($originalh<=$height)
                $bsize="auto 110%";
            else
                $bsize="110% auto";
            $food.="<div class='filibuster-image $classcol $classtop $classmiddle $classbottom' style='left:".$left."px;top:".$top."px;width:".$width."px;height:".$height."px;'>";
            $food.="<a href='$url?env=".$maestro->environ."&site=$site&id=$SELECTEDID#anchor_$FILEID' title='$title'>";
            $food.="<div class='filibuster-image-inner' style='background-image:url($urltfile);background-size:$bsize;'>";
            $food.="</div>";
            $food.="</a>";
            $food.="</div>";
        }
    }
    $food.="</div>";
    $food.="</div>";

    $food=str_replace("######", $maxheight, $food);
    return $food;
}
function flb_galleryheight($BULK, &$heightcols){
    $heightcols=array(0, 0, 0);
    foreach($BULK as $IMG){
        $heightcols[$IMG["COLUMN"]]+=$IMG["HEIGHT"];
    }
    $maxh=0;
    for($i=0;$i<3;$i++){
        if($maxh<$heightcols[$i]){
            $maxh=$heightcols[$i];
        }
    }
    return $maxh;
}
?>