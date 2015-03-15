<?php 
/****************************************************************************
* Name:            graphics.php                                             *
* Project:         Cambusa/ryDraw                                           *
* Version:         1.69                                                     *
* Description:     Graphic functions                                        *
* Copyright (C):   2012-2013  Rodolfo Calzetti                              *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
header ("Content-type: image/gif");

set_time_limit(10);

if(isset($_GET["x"]))
    $width=abs((integer)$_GET["x"]);
else
    $width=200;

if(isset($_GET["y"]))
    $height=abs((integer)$_GET["y"]);
else
    $height=200;

if(isset($_GET["p"]))
    $params=$_GET["p"];
else
    $params="";

$b_r=255;
$b_g=255;
$b_b=255;
$f_r=51;
$f_g=51;
$f_b=51;
$alpha=0;
$filled=0;
$txfont=3;
$thick=1;

$factor=200;
$inverted=false;
$gradient=false;
$corrector=0;
$zoom=1;
$lowthreshold=0;
$highthreshold=0;
$boundx1=0;
$boundy1=0;
$boundx2=$width-1;
$boundy2=$height-1;

if($width>0 && $width<=1000 && $height>0 && $height<=1000){
    $im=imagecreatetruecolor($width, $height);
    $background_color=imagecolorallocate ($im, 255, 255, 255);
    imagecolortransparent($im, $background_color);
    imagefilledrectangle($im, 0, 0, $width-1, $height-1, $background_color);
    $draw_color=imagecolorallocate($im, $f_r, $f_g, $f_b);
    $save_draw_color=$draw_color;
    $fill_color=imagecolorallocatealpha($im, $b_r, $b_g, $b_b, $alpha);
    imagesetthickness($im,$thick);

    preg_match_all("/([a-z]+):([^;]*)/i", $params, $m);

    for($i=0;$i<count($m[0]);$i++){
        $cmd=$m[1][$i];
        $s=explode(",", $m[2][$i]);

        switch($cmd){
            case "circle":case "cr":
                if(count($s)==3){
                    list($cx,$cy,$r)=$s;
                    if($filled)
                        imagefilledellipse($im, $cx, $cy, 2*$r, 2*$r, $fill_color);
                    else
                        imageellipse($im, $cx, $cy, 2*$r, 2*$r, $draw_color);
                }
                break;

            case "ellipse":case "el":
                if(count($s)==4){
                    list($cx,$cy,$a,$b)=$s;
                    if($filled)
                        imagefilledellipse($im, $cx, $cy, $a, $b, $fill_color);
                    else
                        imageellipse($im, $cx, $cy, $a, $b, $draw_color);
                }
                break;

            case "back":case "bk":
                switch(count($s)){
                    case 1:
                        $rgb=$s[0];
                        if ($rgb!=""){
                            $rgb=hexdec($rgb);
                            $b_r=($rgb >> 16) & 0xFF;
                            $b_g=($rgb >> 8) & 0xFF;
                            $b_b=$rgb & 0xFF;
                            $fill_color=imagecolorallocatealpha($im, $b_r, $b_g, $b_b, $alpha);
                        }
                        break;

                    case 3:
                        $b_r=(integer)$s[0];
                        $b_g=(integer)$s[1];
                        $b_b=(integer)$s[2];
                        $fill_color=imagecolorallocatealpha($im, $b_r, $b_g, $b_b, $alpha);
                        break;
                }
                break;

            case "fore":case "fr":
                switch(count($s)){
                    case 1:
                        $rgb=$s[0];
                        if ($rgb!=""){
                            $rgb=hexdec($rgb);
                            $f_r=($rgb >> 16) & 0xFF;
                            $f_g=($rgb >> 8) & 0xFF;
                            $f_b=$rgb & 0xFF;
                            $draw_color=imagecolorallocate($im, $f_r, $f_g, $f_b);
                        }
                        break;

                    case 3:
                        $f_r=(integer)$s[0];
                        $f_g=(integer)$s[1];
                        $f_b=(integer)$s[2];
                        $draw_color=imagecolorallocate($im, $f_r, $f_g, $f_b);
                        break;
                }
                break;

            case "alpha":case "ph":
                if(count($s)==1){
                    $alpha=(integer)$s[0];
                    $fill_color=imagecolorallocatealpha($im, $b_r, $b_g, $b_b, $alpha);
                }
                break;

            case "fill":case "fl":
                if(count($s)==1)
                    $filled=(integer)$s[0]!=0;
                break;

            case "point":case "pt":
                if(count($s)==2)
                    imagesetpixel($im, (integer)$s[0], (integer)$s[1], $draw_color);
                break;

            case "line":case "ln":
                if(count($s)==4){
                    list($x1, $y1, $x2, $y2)=$s;
                    imageline($im, $x1, $y1, $x2, $y2, $draw_color);
                }
                break;

            case "rarrow":case "rw":
                if(count($s)==3){
                    $x=(integer)$s[0];
                    $y=(integer)$s[1];
                    $a=atan((float)$s[2]);
                    $po=Array();
                    $po[]=$x;
                    $po[]=$y;
                    $po[]=round($x+(-6*cos($a)-3*sin($a)));
                    $po[]=round($y+(-6*sin($a)+3*cos($a)));
                    $po[]=round($x+(-6*cos($a)+3*sin($a)));
                    $po[]=round($y+(-6*sin($a)-3*cos($a)));
                    imagefilledpolygon($im, $po, count($po)/2, $draw_color);
                }
                break;

            case "larrow":case "lw":
                if(count($s)==3){
                    $x=(integer)$s[0];
                    $y=(integer)$s[1];
                    $a=-atan((float)$s[2]);
                    $po=Array();
                    $po[]=$x;
                    $po[]=$y;
                    $po[]=round($x+(6*cos($a)-3*sin($a)));
                    $po[]=round($y+(6*sin($a)+3*cos($a)));
                    $po[]=round($x+(6*cos($a)+3*sin($a)));
                    $po[]=round($y+(6*sin($a)-3*cos($a)));
                    imagefilledpolygon($im, $po, count($po)/2, $draw_color);
                }
                break;

            case "uarrow":case "uw":
                if(count($s)==2){
                    $x=(integer)$s[0];
                    $y=(integer)$s[1];
                    $po=Array();
                    $po[]=$x;
                    $po[]=$y;
                    $po[]=$x+3;
                    $po[]=$y+6;
                    $po[]=$x-3;
                    $po[]=$y+6;
                    imagefilledpolygon($im, $po, count($po)/2, $draw_color);
                }
                break;

            case "darrow":case "dw":
                if(count($s)==2){
                    $x=(integer)$s[0];
                    $y=(integer)$s[1];
                    $po=Array();
                    $po[]=$x;
                    $po[]=$y;
                    $po[]=$x+3;
                    $po[]=$y-6;
                    $po[]=$x-3;
                    $po[]=$y-6;
                    imagefilledpolygon($im, $po, count($po)/2, $draw_color);
                }
                break;

            case "arc":case "ar":
                if(count($s)==6){
                    list($x,$y,$w,$h,$a,$b)=$s;
                    if($filled)
                        imagefilledarc($im,$x,$y,$w,$h,$a,$b, $fill_color,IMG_ARC_PIE);
                    else
                        imagearc($im,$x,$y,$w,$h,$a,$b, $draw_color);
                }
                break;

            case "text":case "tx":
                if(count($s)==3){
                    list($x,$y,$t)=$s;
                    $sost=Array();
                    $sost["{comma}"]=",";
                    $sost["{semicolon}"]=";";
                    $sost["$"]="+";
                    $sost["_"]=" ";
                    $t=strtr($t,$sost);
                    unset($sost);
                    imagestring($im,$txfont,$x,$y,$t,$draw_color);
                }
                break;

            case "vtext":case "vx":
                if(count($s)==3){
                    list($x,$y,$t)=$s;
                    $sost=Array();
                    $sost["{comma}"]=",";
                    $sost["{semicolon}"]=";";
                    $sost["$"]="+";
                    $sost["_"]=" ";
                    $t=strtr($t,$sost);
                    unset($sost);
                    imagestringup($im,$txfont,$x,$y,$t,$draw_color);
                }
                break;

            case "fillin":case "in":
                if(count($s)==2){
                    $x=(integer)$s[0];
                    $y=(integer)$s[1];
                    imagefill($im,$x,$y,$fill_color);
                }
                break;

            case "font":case "ft":
                if(count($s)==1)
                    $txfont=$s[0];
                break;
                
            case "size":case "sz":
                if(count($s)==1){
                    $thick=(integer)$s[0];
                    imagesetthickness($im,$thick);
                }
                break;
                
            case "style": case "sy":
                if(count($s)>1){
                    $st=Array();
                    for($j=0;$j<count($s);$j++){
                        if($s[$j]!=0)
                            $st[]=$draw_color;
                        else
                            $st[]=$background_color;
                    }
                    imagesetstyle($im, $st);
                    $save_draw_color=$draw_color;
                    $draw_color=IMG_COLOR_STYLED;
                }
                else
                    $draw_color=$save_draw_color;
                break;

            case "rect":case "re":
                if(count($s)==4){
                    list($x, $y, $w, $h)=$s;
                    if($filled)
                        imagefilledrectangle($im, $x, $y, $w, $h, $fill_color);
                    else
                        imagerectangle($im, $x, $y, $w, $h, $draw_color);
                }
                break;

            case "poly":case "py":
                if($filled)
                    imagefilledpolygon($im, $s, (integer)(count($s)/2), $fill_color);
                else
                    imagepolygon($im, $s, (integer)(count($s)/2), $draw_color);
                break;

            case "regular":case "rg":
                if(count($s)==4){
                    $x=(integer)$s[0];
                    $y=(integer)$s[1];
                    $r=(integer)$s[2];
                    $n=(integer)$s[3];
                    if(($n%2)==0)
                        $arcin=0;
                    else
                        $arcin=pi()/4;
                    $arcdelta=2*pi()/$n;
                    $po=Array();
                    for($j=0;$j<$n;$j++){
                        $a=$arcin+$j*$arcdelta;
                        $po[]=round($x+($r*cos($a)-$r*sin($a)));
                        $po[]=round($y-($r*sin($a)+$r*cos($a)));
                    }
                    if($filled)
                        imagefilledpolygon($im, $po, $n, $fill_color);
                    else
                        imagepolygon($im, $po, $n, $draw_color);
                }
                break;

            case "merge":case "mg":
                if(count($s)==4){
                    $x=(integer)$s[0];
                    $y=(integer)$s[1];
                    $perc=(float)$s[2];
                    $f=$s[3];
                    list($w, $h, $t, $a) = getimagesize($f);
                    $aux_im=LoadPicture($f);
                    imagecopyresampled($im,$aux_im,$x,$y,0,0,floor($w*$perc),floor($h*$perc),$w,$h);
                    imagedestroy($aux_im);
                }
                break;

            case "tile":case "tl":
                if(count($s)==2){
                    $perc=$s[0];
                    $f=$s[1];
                }
                else{
                    $perc=1;
                    $f=$s[0];
                }
                if($f!=""){
                    if($perc!=1){
                        list($w, $h, $t, $a)=getimagesize($f);
                        $rw=floor($w*$perc);
                        $rh=floor($h*$perc);
                        $tile_im=imagecreatetruecolor($rw, $rh);
                        $aux_im=LoadPicture($f);
                        imagecopyresampled($tile_im,$aux_im,0,0,0,0,$rw,$rh,$w,$h);
                        imagedestroy($aux_im);
                    }
                    else
                        $tile_im=LoadPicture($f);
                        
                    imagesettile($im,$tile_im);
                    $savedfill_color=$fill_color;
                    $fill_color=IMG_COLOR_TILED;
                }
                else{
                    @imagedestroy($tile_im);
                    $fill_color=$savedfill_color;
                }
                break;

            case "factor":case "fc":
                if(count($s)==1){$factor=(float)$s[0];}break;

            case "zoom":case "zo":
                if(count($s)==1){$zoom=(float)$s[0];}break;

            case "inverted":case "vr":
                if(count($s)==1){$inverted=(integer)$s[0]!=0;}break;

            case "gradient":case "gr":
                if(count($s)==1)$gradient=(integer)$s[0]!=0;break;

            case "corrector":case "ct":
                if(count($s)==1)$corrector=(float)$s[0];break;

            case "lowthreshold":case "lt":
                if(count($s)==1)$lowthreshold=(float)$s[0];break;

            case "highthreshold":case "ht":
                if(count($s)==1)$highthreshold=(float)$s[0];break;

            case "bound":case "bd":
                if(count($s)==4){
                    $boundx1=(integer)$s[0];
                    $boundy1=(integer)$s[1];
                    $boundx2=(integer)$s[2];
                    $boundy2=(integer)$s[3];
                }
                break;

            case "equation":case "eq":
                if(count($s)==1){
                    $t=$s[0];
                    $cx=round($width/2);
                    $cy=round($height/2);

                    $treq=Array();

                    if($zoom==1){
                        $treq["x"]="(\$x-\$cx)";
                        $treq["y"]="(\$cy-\$y)";
                    }
                    else{
                        $treq["x"]="((\$x-\$cx)/\$zoom)";
                        $treq["y"]="((\$cy-\$y)/\$zoom)";
                    }

                    $treq["z"]="\$z";
                    $treq["%20"]="";
                    $treq["$"]="+";
                    $treq["@"]="+";
                    $treq["%2B"]="+";

                    $t=strtr($t,$treq);
                    $t="\$z=(".$t.");";
                    unset($treq);

                    $z=0;

                    for($y=$boundy1;$y<=$boundy2;$y++){
                        for($x=$boundx1;$x<=$boundx2;$x++){
                            eval($t);

                            if(!$gradient)
                                $z=abs($z);

                            if($corrector>0){
                                if($zoom==1)
                                    $divisore=$factor*(1+pow(($x-$cx)/$corrector,2)+pow(($y-$cy)/$corrector,2));
                                else
                                    $divisore=$factor*(1+pow(($x-$cx)/($zoom*$corrector),2)+pow(($y-$cy)/($zoom*$corrector),2));
                            }
                            else
                                $divisore=$factor;

                            $z=$z/$divisore;

                            if($z<0)
                                $z=0;
                            elseif($z>1)
                                $z=1;

                            if($inverted)
                                $z=1-$z;

                            if( !($lowthreshold>0 && $z<$lowthreshold) && !($highthreshold>0 && $z>$highthreshold) ){
                                $perc_r=floor($f_r*(1-$z)+$b_r*$z);
                                $perc_g=floor($f_g*(1-$z)+$b_g*$z);
                                $perc_b=floor($f_b*(1-$z)+$b_g*$z);
                                imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, $perc_r, $perc_g, $perc_b,$alpha));
                            }
                        }
                    }
                }
                break;

            case "bezier":case "bz":
                switch(count($s)){
                    case 6:
                        $ax=(float)$s[0];
                        $ay=(float)$s[1];
                        $px=(float)$s[2];
                        $py=(float)$s[3];
                        $bx=(float)$s[4];
                        $by=(float)$s[5];
                        
                        $vx=abs($px-$ax)+abs($bx-$px);
                        $vy=abs($py-$ay)+abs($by-$py);

                        if($vx>$vy)
                            $n=$vx;
                        else
                            $n=$vy;

                        $x1=0;
                        $y1=0;

                        for($v=0;$v<=$n;$v++){
                            $t=$v/$n;
                            $x2=round(pow((1-$t),2)*$ax+2*$t*(1-$t)*$px+pow($t,2)*$bx);
                            $y2=round(pow((1-$t),2)*$ay+2*$t*(1-$t)*$py+pow($t,2)*$by);

                            if($v>0){
                                if (abs($x2-$x1)>10 || abs($y2-$y1)>10 || $v+1>$n){
                                    imageline($im, $x1, $y1, $x2, $y2, $draw_color);
                                    $x1=$x2;
                                    $y1=$y2;
                                }
                            }
                            else{
                                $x1=$x2;
                                $y1=$y2;
                            }
                        }
                        break;

                    case 8:
                        $ax=(float)$s[0];
                        $ay=(float)$s[1];
                        $px=(float)$s[2];
                        $py=(float)$s[3];
                        $qx=(float)$s[4];
                        $qy=(float)$s[5];
                        $bx=(float)$s[6];
                        $by=(float)$s[7];

                        $vx=abs($px-$ax)+abs($qx-$px)+abs($bx-$qx);
                        $vy=abs($py-$ay)+abs($qy-$py)+abs($by-$qy);

                        if($vx>$vy)
                            $n=$vx;
                        else
                            $n=$vy;

                        $x1=0;
                        $y1=0;

                        for($v=0;$v<=$n;$v++){
                            $t=$v/$n;
                            $x2=round(pow((1-$t),3)*$ax+3*$t*pow((1-$t),2)*$px+3*pow($t,2)*(1-$t)*$qx+pow($t,3)*$bx);
                            $y2=round(pow((1-$t),3)*$ay+3*$t*pow((1-$t),2)*$py+3*pow($t,2)*(1-$t)*$qy+pow($t,3)*$by);

                            if($v>0){
                                if (abs($x2-$x1)>10 || abs($y2-$y1)>10 || $v+1>$n){
                                    imageline($im, $x1, $y1, $x2, $y2, $draw_color);
                                    $x1=$x2;
                                    $y1=$y2;
                                }
                            }
                            else{
                                $x1=$x2;
                                $y1=$y2;
                            }
                        }
                        break;
                }
                break;
            
            case "homography":case "ho":
                if(count($s)==9){
                    include_once("graph3d.php");
                    Omografia($im,$s);
                }
                break;
                
            default:
                break;
        }
    }
    imagegif($im);
    imagedestroy($im);
}

function LoadPicture($path){
    $path_parts=pathinfo($path);
    $ext=strtolower($path_parts["extension"]);
    switch($ext){
    case "gif":$aux_im=imagecreatefromgif($path);break;
    case "jpg":case "jpeg":$aux_im=imagecreatefromjpeg($path);break;
    case "png":$aux_im=imagecreatefrompng($path);break;
    }
    return $aux_im;
}


?>
