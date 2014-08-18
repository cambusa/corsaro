<?php
/****************************************************************************
* Name:            graphrect.php                                            *
* Project:         Cambusa/ryDraw                                           *
* Version:         1.00                                                     *
* Description:     Graphic functions                                        *
* Copyright (C):   2012-2013  Rodolfo Calzetti                              *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
header ("Content-type: image/gif");

if(isset($_GET["x"]))
    $delta_x=abs((integer)$_GET["x"]);
else
    $delta_x=200;
    
if(isset($_GET["y"]))
    $delta_y=abs((integer)$_GET["y"]);
else
    $delta_y=200;
    
if ($delta_x>20){
    $width=$delta_x;
}
else{
    $width=8;
    $delta_x=8;
}

if ($delta_y>20){
    $height=$delta_y;
}
else{
    $height=8;
    $delta_y=8;
}

$b_r=245;
$b_g=245;
$b_b=245;
$f_r=51;
$f_g=51;
$f_b=51;

if(isset($_GET["c"])){
    $rgb=$_GET["c"];
    if ($rgb!=""){
        $rgb=hexdec($rgb);
        $b_r=($rgb >> 16) & 0xFF;
        $b_g=($rgb >> 8) & 0xFF;
        $b_b=$rgb & 0xFF;
        $f_r=255;
        $f_g=255;
        $f_b=255;
    }
}

if(isset($_GET["f"])){
    $rgb=$_GET["f"];
    if ($rgb!=""){
        $rgb=hexdec($rgb);
        $f_r=($rgb >> 16) & 0xFF;
        $f_g=($rgb >> 8) & 0xFF;
        $f_b=$rgb & 0xFF;
    }
}

if(isset($_GET["t"])) 
    $tipo=(integer)$_GET["t"];
else
    $tipo=0;        // 0 - rettangolo; 1 - arrotondato; 2 - slanted; 3 - tagliato; 4 - cilindro;

if($width>0 && $width<=1000 && $height>0 && $height<=1000){
    $im=imagecreatetruecolor($width, $height);
    $background_color=imagecolorallocate ($im, 255, 255, 255);
    imagecolortransparent($im, $background_color);
    imagefilledrectangle($im, 0, 0, $width-1, $height-1, $background_color);
    $draw_color=imagecolorallocate($im, $f_r, $f_g, $f_b);
    $fill_color=imagecolorallocate($im, $b_r, $b_g, $b_b);
        
    imageantialias($im, true);
    
    $padding=3;
    
    switch($tipo){
        case 0:
            imagefilledrectangle($im, $padding, $padding, $width-2-$padding, $height-2-$padding, $fill_color);
            imagerectangle($im, $padding, $padding, $width-2-$padding, $height-2-$padding, $draw_color);
            break;
    
        case 1:
            imagefilledrectangle($im, 15+$padding, $padding, $width-17-$padding, $height-2-$padding, $fill_color);
            imagefilledrectangle($im, $padding, 15+$padding, $width-2-$padding, $height-17-$padding, $fill_color);
            imagefilledarc($im, 15+$padding, 15+$padding, 30, 30, 180, 270, $fill_color,IMG_ARC_PIE);
            imagefilledarc($im, $width-17-$padding, 15+$padding, 30, 30, 270, 360, $fill_color,IMG_ARC_PIE);
            imagefilledarc($im, $width-17-$padding, $height-17-$padding, 30, 30, 0, 90, $fill_color,IMG_ARC_PIE);
            imagefilledarc($im, 15+$padding, $height-17-$padding, 30, 30, 90, 180, $fill_color,IMG_ARC_PIE);
            
            imageline($im, 15+$padding, $padding, $width-17-$padding, $padding, $draw_color);
            imageline($im, $width-2-$padding, 15+$padding, $width-2-$padding, $height-17-$padding, $draw_color);
            imageline($im, 15+$padding, $height-2-$padding, $width-17-$padding, $height-2-$padding, $draw_color);
            imageline($im, $padding, 15+$padding, $padding, $height-17-$padding, $draw_color);

            imagearc($im, 15+$padding, 15+$padding, 30, 30, 180, 270, $draw_color);
            imagearc($im, $width-17-$padding, 15+$padding, 30, 30, 270, 360, $draw_color);
            imagearc($im, $width-17-$padding, $height-17-$padding, 30, 30, 0, 90, $draw_color);
            imagearc($im, 15+$padding, $height-17-$padding, 30, 30, 90, 180, $draw_color);
            break;
            
        case 2:
            $s=Array();
            $s[0]=round($height/3);
            $s[1]=$padding;
            $s[2]=$width-2-$padding;
            $s[3]=$padding;
            $s[4]=round($width-1-$height/3);
            $s[5]=$height-2-$padding;
            $s[6]=$padding;
            $s[7]=$height-2-$padding;
            
            imagefilledpolygon($im, $s, 4, $fill_color);
            imagepolygon($im, $s, 4, $draw_color);
            break;

        case 3:
            imageline($im, $padding, $padding, $width-2-$padding, $padding, $draw_color);
            imageline($im, $padding, $padding, $padding, $height-2-$padding, $draw_color);
            imageline($im, $width-2-$padding, $padding, $width-2-$padding, round(2*$height/3), $draw_color);

            $ax=$padding;
            $ay=$height-2-$padding;
            $px=round(3*$width/5);
            $py=$height-2-$padding;
            $qx=round(2*$width/5);
            $qy=round(2*$height/3);
            $bx=$width-2-$padding;
            $by=round(2*$height/3);

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
        
            imagefill($im,2+$padding,2+$padding,$fill_color);
            
            break;
            
        case 4:
            $cx=floor($width/2)-1;
            $cyinf=round(6*$height/7-2-$padding);
            $cysup=round($height/7+1+$padding);
            $h=round(2*$height/7);
            $w=round($width-2-2*$padding);
            //$dx=floor($width-2-$padding);
            $dx=round($cx+$width/2-1-$padding);
            
            imagefilledellipse($im, $cx, $cyinf, $w, $h, $fill_color);
            imageellipse ($im, $cx, $cyinf, $w, $h, $draw_color);

            imagefilledrectangle($im, $padding, $cysup, $dx, $cyinf, $fill_color);
            
            imageline($im, $padding, $cysup, $padding, $cyinf, $draw_color);
            imageline($im, $dx, $cysup, $dx, $cyinf, $draw_color);
            
            imagefilledellipse($im, $cx, $cysup, $w, $h, $fill_color);
            imageellipse ($im, $cx, $cysup, $w, $h, $draw_color);
    }

    imagegif($im);
    imagedestroy($im);
}


