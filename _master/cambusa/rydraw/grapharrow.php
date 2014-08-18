<?php
/****************************************************************************
* Name:            grapharrow.php                                            *
* Project:         Cambusa/ryDraw                                           *
* Version:         1.00                                                     *
* Description:     Graphic functions                                        *
* Copyright (C):   2012-2013  Rodolfo Calzetti                              *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
header ("Content-type: image/gif");

$delta_x=200;
$delta_y=200;
$direz=0;
$punte=true;
$width=12;
$height=12;
$f_r=51;
$f_g=51;
$f_b=51;

if(isset($_GET["x"])) $delta_x=abs((integer)$_GET["x"]);
if(isset($_GET["y"])) $delta_y=abs((integer)$_GET["y"]);
if(isset($_GET["d"])) $direz=(integer)$_GET["d"];
if(isset($_GET["p"])) $punte=((integer)$_GET["p"])!=0;
if($delta_x>12){$width=$delta_x;$delta_x-=6;}
if($delta_y>12){$height=$delta_y;$delta_y-=6;}

if(isset($_GET["c"])){
    $rgb=$_GET["c"];
    if ($rgb!=""){
        $rgb=hexdec($rgb);
        $f_r=($rgb >> 16) & 0xFF;
        $f_g=($rgb >> 8) & 0xFF;
        $f_b=$rgb & 0xFF;
    }
}
elseif(isset($_GET["f"])){
    $rgb=$_GET["f"];
    if ($rgb!=""){
        $rgb=hexdec($rgb);
        $f_r=($rgb >> 16) & 0xFF;
        $f_g=($rgb >> 8) & 0xFF;
        $f_b=$rgb & 0xFF;
    }
}

if($width>0 && $width<=1000 && $height>0 && $height<=1000){
    $im=@imagecreate($width, $height);
    $background_color=imagecolorallocate ($im, 255, 255, 255);
    imagecolortransparent($im, $background_color);
    $draw_color=imagecolorallocate($im, $f_r, $f_g, $f_b);
    imageantialias($im, true);

    $alpha=pi()/12;
    $s=sin($alpha);
    $c=cos($alpha);
    
    // Prima ala in coordinate assolute
    $ax1=-10*$c;
    $ay1=10*$s;

    // Seconda ala in coordinate assolute
    $ax2=-10*$c;
    $ay2=-10*$s;
    
    if( $delta_x==0 )
        $corr_x=6;
    else
        $corr_x=3;

    if( $delta_y==0 )
        $corr_y=6;
    else
        $corr_y=3;
    
    switch($direz){
    
        case 0:

            // Inizio freccia
            $x1=0;
            $y1=0;

            // Fine freccia
            $x2=$delta_x;
            $y2=$delta_y;
            
            if( $delta_x==0 ){
                
                // Prima ala in coordinate relative
                $fx1=$delta_x - $ay1;
                $fy1=$delta_y + $ax1;
                
                // Seconda ala in coordinate relative
                $fx2=$delta_x - $ay2;
                $fy2=$delta_y + $ax2;
            }
            elseif( $delta_y==0 ){
                
                // Prima ala in coordinate relative
                $fx1=$delta_x + $ax1;
                $fy1=$delta_y + $ay1;
                
                // Seconda ala in coordinate relative
                $fx2=$delta_x + $ax2;
                $fy2=$delta_y + $ay2;
            }
            else{
                $theta=atan($delta_y/$delta_x);
                $sth=sin($theta);
                $cth=cos($theta);
            
                // Prima ala in coordinate relative
                $fx1=$delta_x + ($ax1*$cth - $ay1*$sth);
                $fy1=$delta_y + ($ax1*$sth + $ay1*$cth);
                
                // Seconda ala in coordinate relative
                $fx2=$delta_x + ($ax2*$cth - $ay2*$sth);
                $fy2=$delta_y + ($ax2*$sth + $ay2*$cth);
            }
            
            break;
            
        case 1:
        
            // Inizio freccia
            $x1=0;
            $y1=$delta_y;
            
            // Fine freccia
            $x2=$delta_x;
            $y2=0;
            
            if( $delta_x==0 ){
                
                // Prima ala in coordinate relative
                $fx1=$delta_x + $ay1;
                $fy1=         - $ax1;
                
                // Seconda ala in coordinate relative
                $fx2=$delta_x + $ay2;
                $fy2=         - $ax2;
            }
            elseif( $delta_y==0 ){
                
                // Prima ala in coordinate relative
                $fx1=$delta_x + $ax1;
                $fy1=           $ay1;
                
                // Seconda ala in coordinate relative
                $fx2=$delta_x + $ax2;
                $fy2=           $ay2;
            }
            else{
                $theta=-atan($delta_y/$delta_x);
                $sth=sin($theta);
                $cth=cos($theta);
                
                $fx1=$delta_x + ($ax1*$cth - $ay1*$sth);
                $fy1=           ($ax1*$sth + $ay1*$cth);
                
                $fx2=$delta_x + ($ax2*$cth - $ay2*$sth);
                $fy2=           ($ax2*$sth + $ay2*$cth);
            }
            
            break;
            
        case 2:
        
            // Inizio freccia
            $x1=$delta_x;
            $y1=$delta_y;
            
            // Fine freccia
            $x2=0;
            $y2=0;
            
            if( $delta_x==0 ){
                
                // Prima ala in coordinate relative
                $fx1=$ay1;
                $fy1=-$ax1;
                
                // Seconda ala in coordinate relative
                $fx2=$ay2;
                $fy2=-$ax2;
            }
            elseif( $delta_y==0 ){
                
                // Prima ala in coordinate relative
                $fx1=-$ax1;
                $fy1=$ay1;
                
                // Seconda ala in coordinate relative
                $fx2=-$ax2;
                $fy2=$ay2;
            }
            else{
                $theta=pi()+atan($delta_y/$delta_x);
                $sth=sin($theta);
                $cth=cos($theta);
                
                $fx1=($ax1*$cth - $ay1*$sth);
                $fy1=($ax1*$sth + $ay1*$cth);
                
                $fx2=($ax2*$cth - $ay2*$sth);
                $fy2=($ax2*$sth + $ay2*$cth);
            }
            
            break;
            
        case 3:
        
            // Inizio freccia
            $x1=$delta_x;
            $y1=0;
            
            // Fine freccia
            $x2=0;
            $y2=$delta_y;
            
            if( $delta_x==0 ){
                
                // Prima ala in coordinate relative
                $fx1=           $ay1;
                $fy1=$delta_y + $ax1;
                
                // Seconda ala in coordinate relative
                $fx2=           $ay2;
                $fy2=$delta_y + $ax2;
            }
            elseif( $delta_y==0 ){
                
                // Prima ala in coordinate relative
                $fx1=         - $ax1;
                $fy1=$delta_y + $ay1;
                
                // Seconda ala in coordinate relative
                $fx2=         - $ax2;
                $fy2=$delta_y + $ay2;
            }
            else{
                $theta=pi()-atan($delta_y/$delta_x);
                $sth=sin($theta);
                $cth=cos($theta);
                
                $fx1=           ($ax1*$cth - $ay1*$sth);
                $fy1=$delta_y + ($ax1*$sth + $ay1*$cth);
                
                $fx2=           ($ax2*$cth - $ay2*$sth);
                $fy2=$delta_y + ($ax2*$sth + $ay2*$cth);
            }
            
            break;
    }
    
    imagesetthickness($im,2);
    
    // Corpo della freccia
    imageline($im, round($x1+$corr_x), round($y1+$corr_y), round($x2+$corr_x), round($y2+$corr_y), $draw_color);

    if ($punte){
        $s=Array();
        $s[]=round($x2+$corr_x);
        $s[]=round($y2+$corr_y);
        $s[]=round($fx1+$corr_x);
        $s[]=round($fy1+$corr_y);
        $s[]=round($fx2+$corr_x);
        $s[]=round($fy2+$corr_y);
        imagefilledpolygon($im, $s, 3, $draw_color);
    }

    imagegif($im);
    imagedestroy($im);
}
