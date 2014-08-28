<?php 
/****************************************************************************
* Name:            qv_saldi_proiezione.php                                  *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_saldi_proiezione($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $SALDICONTI=array();
        
        if(isset($data["DIVISE"]))
            $DIVISE=$data["DIVISE"];
        else
            $DIVISE="";
        
        if(isset($data["TIPOSALDI"]))
            $TIPOSALDI=strtoupper($data["TIPOSALDI"]);
        else
            $TIPOSALDI="E";
            
        switch($TIPOSALDI){
        case "P":
            $BVIEW="QWPBALANCES";
            $BDESCR="SALDI PROVVISORI";
            break;
        case "C":
            $BVIEW="QWCBALANCES";
            $BDESCR="SALDI CERTI";
            break;
        case "E":
            $BVIEW="QWEBALANCES";
            $BDESCR="SALDI VERIFICATI";
            break;
        }
            
        if(isset($data["INIZIO"]))
            $INIZIO=$data["INIZIO"];
        else
            $INIZIO=date("Ymd");

        if(isset($data["FINE"]))
            $FINE=$data["FINE"];
        else
            $FINE=date("Ymd", mktime(0, 0, 0, intval(substr($INIZIO,4,2))+1, intval(substr($INIZIO,6,2)), intval(substr($INIZIO,0,4))));
            
        // SVILUPPO DATE
        $y=intval(substr($INIZIO, 0, 4));
        $m=intval(substr($INIZIO, 4, 2));
        $d=intval(substr($INIZIO, 6, 2));
        $SVILUPPODATE=array();
        $SVILUPPODATE[]=$INIZIO;
        $incr=0;
        do{
            $incr+=1;
            $curr=date("Ymd", mktime(0,0,0, $m, $d+$incr, $y));
            if($curr>$FINE){
                break;
            }
            $SVILUPPODATE[]=$curr;
        }while(true);
            
        if(isset($data["TITOLARI"]))
            $TITOLARI=$data["TITOLARI"];
        else
            $TITOLARI="";

        if(isset($data["BANCHE"]))
            $BANCHE=$data["BANCHE"];
        else
            $BANCHE="";

        if(isset($data["CONTI"]))
            $CONTI=$data["CONTI"];
        else
            $CONTI="";

        if($CONTI!=""){
            $FILTROCONTI="'".str_replace("|", "','", $CONTI)."'";
            $sql="SELECT SYSID,DESCRIPTION,REFGENREID FROM QW_CONTI WHERE SYSID IN ($FILTROCONTI)";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $CONTOID=$r[$i]["SYSID"];
                $REFGENREID=$r[$i]["REFGENREID"];
                $SALDICONTI[$CONTOID]=array();
                $SALDICONTI[$CONTOID]["DESCRIPTION"]=$r[$i]["DESCRIPTION"];
                $SALDICONTI[$CONTOID]["SALDI"]=array();

                // SALDO ALLA DATA INIZIO
                $sql="SELECT {AS:TOP 1} BALANCE FROM $BVIEW WHERE SYSID='$CONTOID' AND GENREID='$REFGENREID' AND EVENTTIME<=[:DATE($INIZIO)] {O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY} ORDER BY EVENTTIME DESC {LM:LIMIT 1}";
                maestro_query($maestro, $sql, $d);
                if(count($d)>0)
                    $SALDOINIZIO=floatval($d[0]["BALANCE"]);
                else
                    $SALDOINIZIO=0;
                $SALDICONTI[$CONTOID]["SALDI"][$INIZIO]=$SALDOINIZIO;
                
                $sql="SELECT EVENTTIME,BALANCE FROM $BVIEW WHERE SYSID='$CONTOID' AND GENREID='$REFGENREID' AND EVENTTIME>[:DATE($INIZIO)] AND EVENTTIME<=[:DATE($FINE)]";
                maestro_query($maestro, $sql, $d);
                for($j=0; $j<count($d); $j++){
                    $DATE=qv_strdate($d[$j]["EVENTTIME"]);
                    $SALDO=floatval($d[$j]["BALANCE"]);
                    $SALDICONTI[$CONTOID]["SALDI"][$DATE]=$SALDO;
                }

                // COMPLETO LO SVILUPPO
                $SALDO=$SALDOINIZIO;
                foreach($SVILUPPODATE as $DATE){
                    if(isset($SALDICONTI[$CONTOID]["SALDI"][$DATE]))
                        $SALDO=$SALDICONTI[$CONTOID]["SALDI"][$DATE];
                    else
                        $SALDICONTI[$CONTOID]["SALDI"][$DATE]=$SALDO;
                }
                ksort($SALDICONTI[$CONTOID]["SALDI"]);
            }
        }
        
        // VARIABILI DI RITORNO
        $babelparams["SALDICONTI"]=$SALDICONTI;
        $babelparams["TIPOSALDI"]=$BDESCR;
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
    }
    // USCITA JSON
    $j=array();
    $j["success"]=$success;
    $j["code"]=$babelcode;
    $j["params"]=$babelparams;
    $j["message"]=$message;
    $j["SYSID"]=$SYSID;
    return $j; //ritorno standard
}
?>