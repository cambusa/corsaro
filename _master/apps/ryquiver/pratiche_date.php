<?php 
/****************************************************************************
* Name:            pratiche_date.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."rygeneral/datetime.php";
function pratiche_sommagiorni($begin, $days, $method){
    $next=$begin;
    $begin=substr($begin,0,4)."-".substr($begin,4,2)."-".substr($begin,6,2);
    switch($method){
    case 1: // Solare
        $b=date_create($begin);
        $next=date_format(ry_dateadd($b, $days), "Ymd");
        break;
    case 2: // Lavorativo
        $b=date_create($begin);
        $next=date_format(ry_businessadd($b, $days), "Ymd");
        break;
    }
    return $next;
}
function pratiche_sommaore($begin, $hours){
    return substr($begin,0,8) . substr("00".$hours,-2) . "0000";
}
function pratiche_sommamesi($begin, $months, $method){
    if($months>0){
        $y=intval(substr($begin, 0, 4));
        $m=intval(substr($begin, 4, 2));
        $d=intval(substr($begin, 6, 2));
        $ret=date("Ymd", mktime(0, 0, 0, $m+$months, $d, $y));
    }
    else{
        $ret=$begin;
    }
    if($method==2){
        // MI SPOSTO SUL PRIMO LAVORATIVO
        $ret=pratiche_sommagiorni($ret, 0, 2);
    }
    return $ret;
}
function pratiche_iniziosettimana($begin){
    $y=intval(substr($begin, 0, 4));
    $m=intval(substr($begin, 4, 2));
    $d=intval(substr($begin, 6, 2));
    $week=date("w", mktime(0, 0, 0, $m, $d, $y));
    return date("Ymd", mktime(0, 0, 0, $m, $d-$week, $y));
}
function pratiche_finemese($begin){
    $y=intval(substr($begin, 0, 4));
    $m=intval(substr($begin, 4, 2));
    return date("Ymd", mktime(0, 0, 0, $m+1, 0, $y));
}
function motivo_calcolodata($maestro, $INIZIO, $motive, &$GENREID, &$AMOUNT, &$BOWTIME, &$TARGETTIME){
    /*
    0 - Giorno creazione
    1 - Inizio settimana
    2 - Inizio mese
    3 - Inizio anno
    */
    $MOTIVE_RIFERIMENTOINIZIO=intval($motive["RIFERIMENTOINIZIO"]);
    /*
    0 - Data inizio
    1 - Inizio settimana
    2 - Inizio mese
    3 - Inizio anno
    */
    $MOTIVE_RIFERIMENTOFINE=intval($motive["RIFERIMENTOFINE"]);
    $MOTIVE_MESEINIZIO=intval($motive["MESEINIZIO"]);
    $MOTIVE_MESEFINE=intval($motive["MESEFINE"]);
    $MOTIVE_GIORNOINIZIO=intval($motive["GIORNOINIZIO"]);
    $MOTIVE_GIORNOFINE=intval($motive["GIORNOFINE"]);
    $MOTIVE_ORAINIZIO=intval($motive["ORAINIZIO"]);
    $MOTIVE_ORAFINE=intval($motive["ORAFINE"]);
    $MOTIVE_CALCOLO=intval($motive["CALCOLO"]);
    
    if($MOTIVE_CALCOLO==0){
        $MOTIVE_CALCOLO=2;
        $MOTIVE_RIFERIMENTOINIZIO=0;
        $MOTIVE_RIFERIMENTOFINE=0;
        $MOTIVE_MESEINIZIO=0;
        $MOTIVE_MESEFINE=0;
        $MOTIVE_GIORNOINIZIO=0;
        $MOTIVE_GIORNOFINE=1;
        $MOTIVE_ORAINIZIO=0;
        $MOTIVE_ORAFINE=0;
    }

    switch($MOTIVE_RIFERIMENTOINIZIO){
    case 0: // Data creazione
        // DATA DI OGGI
        $BOWTIME=date("Ymd");

        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEINIZIO>0){
            $BOWTIME=pratiche_sommamesi($BOWTIME, $MOTIVE_MESEINIZIO, 1);
        }
        // SOMMO I GIORNI
        $BOWTIME=pratiche_sommagiorni($BOWTIME, $MOTIVE_GIORNOINIZIO, $MOTIVE_CALCOLO);
        break;
    case 1: // Inizio settimana
        // DATA DI OGGI
        $BOWTIME=date("Ymd");

        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEINIZIO>0){
            $BOWTIME=pratiche_sommamesi($BOWTIME, $MOTIVE_MESEINIZIO, 1);
        }
        // MI RIPORTO ALLA DOMENICA
        $BOWTIME=pratiche_iniziosettimana($BOWTIME);
        
        // SOMMO I GIORNI
        $BOWTIME=pratiche_sommagiorni($BOWTIME, $MOTIVE_GIORNOINIZIO, $MOTIVE_CALCOLO);
        break;
    case 2: // Inizio mese
        // PORTO LA DATA A INIZIO MESE
        $BOWTIME=date("Ym")."01";

        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEINIZIO>0){
            $BOWTIME=pratiche_sommamesi($BOWTIME, $MOTIVE_MESEINIZIO, 1);
        }
        if($MOTIVE_GIORNOINIZIO==31){
            // FINE MESE
            $BOWTIME=pratiche_finemese($BOWTIME);
        }
        elseif($MOTIVE_GIORNOINIZIO>0){
            // SOMMO I GIORNI
            $BOWTIME=pratiche_sommagiorni($BOWTIME, $MOTIVE_GIORNOINIZIO-1, $MOTIVE_CALCOLO);
        }
        break;
    case 3: // Inizio anno
        // PORTO LA DATA A CAPO D'ANNO
        $BOWTIME=date("Y")."0101";

        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEINIZIO>0){
            $BOWTIME=pratiche_sommamesi($BOWTIME, $MOTIVE_MESEINIZIO-1, 1);
        }
        // EVENTUALMENTE SOMMO I GIORNI
        if($MOTIVE_GIORNOINIZIO>0){
            $BOWTIME=pratiche_sommagiorni($BOWTIME, $MOTIVE_GIORNOINIZIO-1, $MOTIVE_CALCOLO);
        }
        break;
    default:
        $babelcode="QVERR_RIFERIMENTOINIZIO";
        $b_params=array();
        $b_pattern="Valore non previsto per RIFERIMENTOINIZIO";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    // SOMMO LE ORE (COMUNQUE, AGGIUNGO A ZERO LA PARTE "TIME")
    $BOWTIME=pratiche_sommaore($BOWTIME, $MOTIVE_ORAINIZIO);

    switch($MOTIVE_RIFERIMENTOFINE){
    case 0: // Data inizio
        // RIPRENDO LA DATA INIZIO
        $TARGETTIME=$BOWTIME;

        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEFINE>0){
            $TARGETTIME=pratiche_sommamesi($TARGETTIME, $MOTIVE_MESEFINE, 1);
        }
        // SOMMO I GIORNI
        $TARGETTIME=pratiche_sommagiorni($TARGETTIME, $MOTIVE_GIORNOFINE, $MOTIVE_CALCOLO);
        break;
    case 1: // Inizio settimana (della data inizio)
        // RIPRENDO LA DATA INIZIO
        $TARGETTIME=$BOWTIME;
        
        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEFINE>0){
            $TARGETTIME=pratiche_sommamesi($TARGETTIME, $MOTIVE_MESEFINE, 1);
        }
        // MI RIPORTO ALLA DOMENICA
        $TARGETTIME=pratiche_iniziosettimana($TARGETTIME);
        
        // SOMMO I GIORNI
        $TARGETTIME=pratiche_sommagiorni($TARGETTIME, $MOTIVE_GIORNOFINE, $MOTIVE_CALCOLO);
        
        // SE LA DATA INIZIO SUPERA LA DATA FINE, PORTO LA DATA FINE AVANTI DI UNA SETTIMANA
        if($TARGETTIME<substr($BOWTIME, 0, 8)){
            $TARGETTIME=pratiche_sommagiorni($TARGETTIME, 7, $MOTIVE_CALCOLO);
        }
        break;
    case 2: // Inizio mese (della data inizio)
        // PORTO LA DATA A INIZIO MESE
        $TARGETTIME=substr($BOWTIME, 0, 6)."01";
        
        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEFINE>0){
            $TARGETTIME=pratiche_sommamesi($TARGETTIME, $MOTIVE_MESEFINE, 1);
        }
        if($MOTIVE_GIORNOFINE==31){
            // FINE MESE
            $TARGETTIME=pratiche_finemese($TARGETTIME);
        }
        elseif($MOTIVE_GIORNOFINE>0){
            // SOMMO I GIORNI
            $TARGETTIME=pratiche_sommagiorni($TARGETTIME, $MOTIVE_GIORNOFINE-1, $MOTIVE_CALCOLO);
        }
        // SE LA DATA INIZIO SUPERA LA DATA FINE, PORTO LA DATA FINE AVANTI DI UN MESE
        if($TARGETTIME<substr($BOWTIME, 0, 8)){
            $TARGETTIME=pratiche_sommamesi($TARGETTIME, 1, $MOTIVE_CALCOLO);
        }
        break;
    case 3: // Inizio anno
        // PORTO LA DATA A CAPO D'ANNO
        $TARGETTIME=substr($BOWTIME, 0, 4)."0101";

        // EVENTUALMENTE SOMMO I MESI
        if($MOTIVE_MESEFINE>0){
            $TARGETTIME=pratiche_sommamesi($TARGETTIME, $MOTIVE_MESEFINE-1, 1);
        }
        // EVENTUALMENTE SOMMO I GIORNI
        if($MOTIVE_GIORNOFINE>0){
            $TARGETTIME=pratiche_sommagiorni($TARGETTIME, $MOTIVE_GIORNOFINE-1, $MOTIVE_CALCOLO);
        }
        // SE LA DATA INIZIO SUPERA LA DATA FINE, PORTO LA DATA FINE AVANTI DI UN ANNO
        if($TARGETTIME<substr($BOWTIME, 0, 8)){
            $TARGETTIME=pratiche_sommamesi($TARGETTIME, 12, $MOTIVE_CALCOLO);
        }
        break;
    default:
        $babelcode="QVERR_RIFERIMENTOFINE";
        $b_params=array();
        $b_pattern="Valore non previsto per RIFERIMENTOFINE";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    // SOMMO LE ORE (COMUNQUE, AGGIUNGO A ZERO LA PARTE "TIME")
    $TARGETTIME=pratiche_sommaore($TARGETTIME, $MOTIVE_ORAFINE);
    
    // DETERMINAZIONE GENREID E AMOUNT
    if($BOWTIME!=$TARGETTIME){
        $GENREID=qv_actualid($maestro, "0TIMEDAYS000");
        $d1=date_create( substr($BOWTIME,0,4)."-".substr($BOWTIME,4,2)."-".substr($BOWTIME,6,2) );
        $d2=date_create( substr($TARGETTIME,0,4)."-".substr($TARGETTIME,4,2)."-".substr($TARGETTIME,6,2) );
        $AMOUNT=ry_datediff($d1, $d2);
    }
    else{
        $GENREID=qv_actualid($maestro, "0TIMEHOURS00");
        if($MOTIVE_ORAFINE<$MOTIVE_ORAINIZIO){
            $MOTIVE_ORAFINE=$MOTIVE_ORAINIZIO;
        }
        $AMOUNT=$MOTIVE_ORAFINE-$MOTIVE_ORAINIZIO;
    }
    if($AMOUNT<0){
        $AMOUNT=0;
    }
}
?>