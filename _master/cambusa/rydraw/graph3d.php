<?php 
/****************************************************************************
* Name:            graph3d.php                                              *
* Project:         Cambusa/ryDraw                                           *
* Version:         1.69                                                     *
* Description:     Graphic functions                                        *
* Copyright (C):   2012-2013  Rodolfo Calzetti                              *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
class StructPuntoSchermoDouble{
    var $x;
    var $y;
    function __construct(){
        $this->x=0;
        $this->y=0;
    }
}

function Omografia($im, $par){
    global $width,$height,$draw_color;
    
    // CARICAMENTO STRUTTURA
    $pt=Array(new StructPuntoSchermoDouble,new StructPuntoSchermoDouble,new StructPuntoSchermoDouble,new StructPuntoSchermoDouble);
    $pt[0]->x=(integer)$par[0];
    $pt[0]->y=(integer)$par[1];
    $pt[1]->x=(integer)$par[2];
    $pt[1]->y=(integer)$par[3];
    $pt[2]->x=(integer)$par[4];
    $pt[2]->y=(integer)$par[5];
    $pt[3]->x=(integer)$par[6];
    $pt[3]->y=(integer)$par[7];
    $f=$par[8];
    list($srgw, $srgh, $t, $a) = getimagesize($f);
    $path_parts=pathinfo($f);
    $ext=strtolower($path_parts["extension"]);
    switch($ext){
    case "gif":$aux_im=imagecreatefromgif($f);break;
    case "jpg":case "jpeg":$aux_im=imagecreatefromjpeg($f);break;
    case "png":$aux_im=imagecreatefrompng($f);break;
    }

    // DETERMINAZIONE DEL VERTICE CON ASCISSA MINIMA
    
    $x_min=$width-1;
    $x_max=0;

    for($i=0;$i<=3;$i++){
        if($pt[$i]->x < $x_min)
            $x_min=$pt[$i]->x;
            
        if($pt[$i]->x > $x_max)
            $x_max=$pt[$i]->x;
    }
    
    $y_min=$height-1;
    $y_max=0;

    for($i=0;$i<=3;$i++){
        if($pt[$i]->y < $y_min)
            $y_min=$pt[$i]->y;
            
        if($pt[$i]->y > $y_max)
            $y_max=$pt[$i]->y;
    }
    
    // DETERMINAZIONE DEI PARAMETRI INTERMEDI

    $matr=Array();
    
    $matr[1]=Array();
    $matr[1][1]=$pt[1]->x;
    $matr[1][2]=$pt[3]->x;
    $matr[1][3]=-$pt[0]->x;
    
    $matr[2]=Array();
    $matr[2][1]=$pt[1]->y;
    $matr[2][2]=$pt[3]->y;
    $matr[2][3]=-$pt[0]->y;
    
    $matr[3]=Array();
    $matr[3][1]=1;
    $matr[3][2]=1;
    $matr[3][3]=-1;
    
    $matrinv=MatriceInversa($matr);

    $mu=$matrinv[1][1]*$pt[2]->x + $matrinv[1][2]*$pt[2]->y + $matrinv[1][3];
    $nu=$matrinv[2][1]*$pt[2]->x + $matrinv[2][2]*$pt[2]->y + $matrinv[2][3];
    $lam=$matrinv[3][1]*$pt[2]->x + $matrinv[3][2]*$pt[2]->y + $matrinv[3][3];
    
    // DETERMINAZIONE DELL'OMOGRAFIA
    
    $matr=Array();
    
    $matr[1]=Array();
    $matr[1][1]=($mu*$pt[1]->x-$lam*$pt[0]->x)/($srgw-1);
    $matr[1][2]=($nu*$pt[3]->x-$lam*$pt[0]->x)/($srgh-1);
    $matr[1][3]=$lam*$pt[0]->x;
    
    $matr[2]=Array();
    $matr[2][1]=($mu*$pt[1]->y-$lam*$pt[0]->y)/($srgw-1);
    $matr[2][2]=($nu*$pt[3]->y-$lam*$pt[0]->y)/($srgh-1);
    $matr[2][3]=$lam*$pt[0]->y;
    
    $matr[3]=Array();
    $matr[3][1]=($mu-$lam)/($srgw-1);
    $matr[3][2]=($nu-$lam)/($srgh-1);
    $matr[3][3]=$lam;
    
    $matrinv=MatriceInversa($matr);

    for ($x=$x_min; $x<=$x_max; $x++){
    
        for ($y=$y_min; $y<=$y_max; $y++){
    
            /*--------------------------------------------
            | Determino il punto del piano della texture |
            --------------------------------------------*/
            
            $sorgz = $matrinv[3][1]*$x + $matrinv[3][2]*$y + $matrinv[3][3];
            $sorgx = round(  ($matrinv[1][1]*$x + $matrinv[1][2]*$y + $matrinv[1][3]) / $sorgz );
            $sorgy = round(  ($matrinv[2][1]*$x + $matrinv[2][2]*$y + $matrinv[2][3]) / $sorgz );
            
            if($sorgx>=0 && $sorgx<$srgw && $sorgy>=0 && $sorgy<$srgh){
            
                /*-----------------------------------------------
                | Leggo il colore della texture in questo punto |
                -----------------------------------------------*/
                
                $colore=imagecolorat($aux_im, $sorgx, $sorgy);
                
                /*---------------------------------
                | Assegno il colore dello schermo |
                ---------------------------------*/
                
                imagesetpixel($im, $x, $y, $colore);
            }
        }
    }
        
    // DISTRUZIONE DEGLI OGGETTI
    imagedestroy($aux_im);
    unset($pts);
    unset($ptt);
}

function MatriceInversa($matr){

    $minori=Array();
    
    $minori[1]=Array();
    $minori[1][1]= ($matr[2][2]*$matr[3][3]-$matr[2][3]*$matr[3][2]);
    $minori[1][2]=-($matr[2][1]*$matr[3][3]-$matr[2][3]*$matr[3][1]);
    $minori[1][3]= ($matr[2][1]*$matr[3][2]-$matr[2][2]*$matr[3][1]);
    
    $minori[2]=Array();
    $minori[2][1]=-($matr[1][2]*$matr[3][3]-$matr[1][3]*$matr[3][2]);
    $minori[2][2]= ($matr[1][1]*$matr[3][3]-$matr[1][3]*$matr[3][1]);
    $minori[2][3]=-($matr[1][1]*$matr[3][2]-$matr[1][2]*$matr[3][1]);
    
    $minori[3]=Array();
    $minori[3][1]= ($matr[1][2]*$matr[2][3]-$matr[1][3]*$matr[2][2]);
    $minori[3][2]=-($matr[1][1]*$matr[2][3]-$matr[1][3]*$matr[2][1]);
    $minori[3][3]= ($matr[1][1]*$matr[2][2]-$matr[1][2]*$matr[2][1]);

    $det=$matr[1][1]*$minori[1][1]+$matr[1][2]*$minori[1][2]+$matr[1][3]*$minori[1][3];
    
    $matrinv=Array();
    $matrinv[1]=Array();
    $matrinv[2]=Array();
    $matrinv[3]=Array();
    
    for($r=1;$r<=3;$r++)
        for($c=1;$c<=3;$c++)
            $matrinv[$r][$c]=$minori[$c][$r]/$det;
    
    return $matrinv;
}
?>
