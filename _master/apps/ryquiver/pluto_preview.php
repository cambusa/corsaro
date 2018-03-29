<?php 
/****************************************************************************
* Name:            pluto_preview.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function pluto_preview($DEVELOPER){
    $HTML="";
    $HTML.="<table>";
    $HTML.="<tr>";
    $HTML.="<td><div class='winz-cell-first winz-header-left'>Data</div></td>";
    if($DEVELOPER->swap){
        $HTML.="<td><div class='winz-header-right'>Nominale</div></td>";
        $HTML.="<td><div class='winz-header-right'>Int. inc.</div></td>";
        $HTML.="<td><div class='winz-header-right'>Tasso inc.</div></td>";
        $HTML.="<td><div class='winz-header-right'>Comm. inc.</div></td>";
        $HTML.="<td><div class='winz-header-right'>Int. pag.</div></td>";
        $HTML.="<td><div class='winz-header-right'>Tasso pag.</div></td>";
        $HTML.="<td><div class='winz-header-right'>Comm. pag.</div></td>";
        $HTML.="<td><div class='winz-header-right'>Totale</div></td>";
    }
    else{
        $HTML.="<td><div class='winz-header-right'>Capitale</div></td>";
        $HTML.="<td><div class='winz-header-right'>Interessi</div></td>";
        $HTML.="<td><div class='winz-header-right'>Tasso</div></td>";
        $HTML.="<td><div class='winz-header-right'>Commissioni</div></td>";
        $HTML.="<td><div class='winz-header-right'>Totale</div></td>";
    }
    $HTML.="</tr>";
    $init=false;
    $tasso=0;
    $spread=0;
    $tassoinc=0;
    $spreadinc=0;
    $tassopag=0;
    $spreadpag=0;
    $totale=0;
    $totalestar=0;
    $totaleinvest=0;
    $mindate=false;
    $maxdate=false;
    foreach($DEVELOPER->sviluppo as $flusso){
        $DATA=$flusso["DATA"];
        $d=date_create(substr($DATA,0,4)."-".substr($DATA,4,2)."-".substr($DATA,6,2));
        if($maxdate===false){
            $maxdate=$d;
        }
        elseif($maxdate<$d){
            $maxdate=$d;
        }
    }
    foreach($DEVELOPER->sviluppo as $flusso){
        $DATA=$flusso["DATA"];
        $DATAFLUSSO=substr($DATA,6,2)."/".substr($DATA,4,2)."/".substr($DATA,0,4);
        $HTML.="<tr>";
        $HTML.="<td><div class='winz-cell-first winz-cell-left'>".$DATAFLUSSO."</div></td>";
        if($DEVELOPER->swap){
            if(!$init){
                $init=true;
                if($flusso["_TASSOINC"])
                    $tassoinc=$flusso["TASSOINC"];
                if($flusso["_SPREADINC"])
                    $spreadinc=$flusso["SPREADINC"];
                if($flusso["_TASSOPAG"])
                    $tassopag=$flusso["TASSOPAG"];
                if($flusso["_SPREADPAG"])
                    $spreadpag=$flusso["SPREADPAG"];
                $DEVELOPER->anticipati=($flusso["INTINC"]!=0 || $flusso["INTPAG"]!=0);
            }
            
            if($DEVELOPER->anticipati){
                // DETERMINO I TASSI DEL PERIODO SUCCESSIVO
                if($flusso["_TASSOINC"])
                    $tassoinc=$flusso["TASSOINC"];
                if($flusso["_SPREADINC"])
                    $spreadinc=$flusso["SPREADINC"];
                if($flusso["_TASSOPAG"])
                    $tassopag=$flusso["TASSOPAG"];
                if($flusso["_SPREADPAG"])
                    $spreadpag=$flusso["SPREADPAG"];
            }
            
            $TOT=$flusso["INTINC"]+$flusso["COMMINC"]-$flusso["INTPAG"]-$flusso["COMMPAG"];
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["NOMINALE"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["INTINC"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($tassoinc+$spreadinc, 4)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["COMMINC"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["INTPAG"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($tassopag+$spreadpag, 4)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["COMMPAG"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($TOT, 2)."</div></td>";
            
            if(!$DEVELOPER->anticipati){
                // DETERMINO I TASSI DEL PERIODO SUCCESSIVO
                if($flusso["_TASSOINC"])
                    $tassoinc=$flusso["TASSOINC"];
                if($flusso["_SPREADINC"])
                    $spreadinc=$flusso["SPREADINC"];
                if($flusso["_TASSOPAG"])
                    $tassopag=$flusso["TASSOPAG"];
                if($flusso["_SPREADPAG"])
                    $spreadpag=$flusso["SPREADPAG"];
            }
        }
        else{
            if(!$init){
                $init=true;
                if($flusso["_TASSO"])
                    $tasso=$flusso["TASSO"];
                if($flusso["_SPREAD"])
                    $spread=$flusso["SPREAD"];
                $DEVELOPER->anticipati=($flusso["INTERESSI"]!=0);
            }
            
            if($DEVELOPER->anticipati){
                // DETERMINO IL TASSO DEL PERIODO SUCCESSIVO
                if($flusso["_TASSO"])
                    $tasso=$flusso["TASSO"];
                if($flusso["_SPREAD"])
                    $spread=$flusso["SPREAD"];
            }

            $TOT=$flusso["CAPITALE"]+$flusso["INTERESSI"]+$flusso["COMMISSIONI"];
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["CAPITALE"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["INTERESSI"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($tasso+$spread, 4)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($flusso["COMMISSIONI"], 2)."</div></td>";
            $HTML.="<td><div class='winz-cell-right'>".pluto_numero($TOT, 2)."</div></td>";
            
            if(!$DEVELOPER->anticipati){
                // DETERMINO IL TASSO DEL PERIODO SUCCESSIVO
                if($flusso["_TASSO"])
                    $tasso=$flusso["TASSO"];
                if($flusso["_SPREAD"])
                    $spread=$flusso["SPREAD"];
            }
        }
        $HTML.="</tr>";
        $d=date_create(substr($DATA,0,4)."-".substr($DATA,4,2)."-".substr($DATA,6,2));
        if($mindate===false){
            $mindate=$d;
        }
        $totale+=$TOT/pow(1+$DEVELOPER->attualizzazione/100, ry_datediff365($mindate, $d)/365 );
        if($TOT>0)
            $totalestar+=$TOT*pow(1+$DEVELOPER->reinvestimento/100, ry_datediff($d, $maxdate)/365 );
        elseif($TOT<0)
            $totaleinvest+=$TOT/pow(1+$DEVELOPER->attualizzazione/100, ry_datediff($mindate, $d)/365 );

    }
    $HTML.="<tr>";
    
    if($mindate!==false && $maxdate!==false)
        $totalestar=$totalestar/pow(1+$DEVELOPER->attualizzazione/100, ry_datediff365($mindate, $maxdate)/365 );
    $totalestar+=$totaleinvest;    
    /*
    if($DEVELOPER->swap)
        $HTML.="<td colspan='7'></td>";
    else
        $HTML.="<td colspan='4'></td>";
    $HTML.="<td><div class='winz-cell-right'>&nbsp;</div></td>";
    $HTML.="<td><div class='winz-cell-right' style='color:maroon'>".pluto_numero($totale, 2)."</div></td>";
    $HTML.="</tr>";
    */
    
    $HTML.="</table>";
    $HTML.="<br/>";
    if(!$DEVELOPER->swap){
        // CALCOLO DEL TAN
        $f=array();
        $d=array();
        $i=0;
        foreach($DEVELOPER->sviluppo as $flusso){
            $f[$i]=$flusso["CAPITALE"]+$flusso["INTERESSI"];
            $dt=$flusso["DATA"];
            $d[$i]=date_create(substr($dt,0,4)."-".substr($dt,4,2)."-".substr($dt,6,2));
            $i+=1;
        }
        ryfIRR($TAN, $f, $d);
        
        // CALCOLO DEL TAEG
        $f=array();
        $d=array();
        $i=0;
        foreach($DEVELOPER->sviluppo as $flusso){
            $f[$i]=$flusso["CAPITALE"]+$flusso["INTERESSI"]+$flusso["COMMISSIONI"];
            $dt=$flusso["DATA"];
            $d[$i]=date_create(substr($dt,0,4)."-".substr($dt,4,2)."-".substr($dt,6,2));
            $i+=1;
        }
        ryfIRR($TAEG, $f, $d);
        $HTML.="<table>";
        $HTML.="<tr><td>TAN:&nbsp;</td><td><div style='text-align:right;'>";
        $HTML.=pluto_numero(100*$TAN, 4);
        $HTML.="</div></td></tr>";
        $HTML.="<tr><td>TAEG:&nbsp;</td><td><div style='text-align:right;'>";
        $HTML.=pluto_numero(100*$TAEG, 4);
        $HTML.="</div></td></tr>";
        $HTML.="<tr><td>VAN:&nbsp;</td><td><div style='text-align:right;'>";
        $HTML.=pluto_numero($totale, 2);
        $HTML.="</div></td></tr>";
        $HTML.="<tr><td>VAN*:&nbsp;</td><td><div style='text-align:right;'>";
        $HTML.=pluto_numero($totalestar, 2);
        $HTML.="</div></td></tr>";
        $HTML.="</table>";
    }
    return $HTML;
}
function pluto_numero($VALUE, $NUMDEC){
    $VALUE=strval($VALUE);
    if($VALUE!="0"){
        if(strpos($VALUE, "-")!==false){
            $SIGNUM="-";
            $VALUE=str_replace("-", "", $VALUE);
        }
        else{
            $SIGNUM="";
        }
            
        $p=strpos($VALUE, ".");
        if($p!==false){
            $INT=substr($VALUE, 0, $p);
            $DEC=substr($VALUE, $p+1);
        }
        else{
            $INT=$VALUE;
            $DEC="";
            $p=strlen($INT);
        }
        if($INT==""){
            $INT="0";
        }
        for($i=$p-3;$i>0;$i-=3){
            $INT=substr($INT, 0, $i)."&#x02D9;".substr($INT, $i);
        }
        if($NUMDEC==0){
            $VALUE=$SIGNUM.$INT;
        }
        else{
            $DEC=substr($DEC."0000000", 0, $NUMDEC);
            $VALUE=$SIGNUM.$INT.",".$DEC;
        }
    }
    else{
        $VALUE="&nbsp;";
    }
    return $VALUE;
}
?>