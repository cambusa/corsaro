<?php
/****************************************************************************
* Name:            graphcircle.php                                          *
* Project:         Cambusa/ryDraw                                           *
* Version:         1.69                                                     *
* Description:     Graphic functions                                        *
* Copyright (C):   2012-2013  Rodolfo Calzetti                              *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
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

if($width>0 && $width<=1000 && $height>0 && $height<=1000){
    $im=imagecreatetruecolor($width, $height);
    $background_color=imagecolorallocate ($im, 255, 255, 255);
    imagecolortransparent($im, $background_color);
    imagefilledrectangle($im, 0, 0, $width-1, $height-1, $background_color);
    $draw_color=imagecolorallocate($im, $f_r, $f_g, $f_b);
    
    if ($delta_x==8)
        $fill_color=imagecolorallocate($im, $f_r, $f_r, $f_r);
    else
        $fill_color=imagecolorallocate($im, $b_r, $b_g, $b_b);
        
    imageantialias($im, true);

    imagefilledellipse($im, $delta_x/2-2, $delta_y/2-1, $delta_x-6, $delta_y-6, $fill_color);
    imageellipse ($im, $delta_x/2-2, $delta_y/2-1, $delta_x-4, $delta_y-4, $draw_color);

    imagegif($im);
    imagedestroy($im);
}

