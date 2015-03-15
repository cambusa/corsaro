<?php 
/****************************************************************************
* Name:            qv_pratiche_scadenze.php                                 *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_arrows_delete.php";
include_once $path_cambusa."ryquiver/qv_sendmail.php";
include_once $path_cambusa."rygeneral/datetime.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
function qv_pratiche_scadenze($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $TODAY=date("Ymd");
        $messaggi=array();
        $fasi=array();
        
        // SCANDISCO TUTTE LE ATTIVITA' PENDENTI
        $sql="SELECT SYSID,DESCRIPTION,REGISTRY,PRATICAID,MOTIVEID,TARGETID,TARGETTIME,PHASE FROM QW_ATTIVITAJOIN WHERE STATUS=0 AND CONSISTENCY=0 AND PHASE<2";
        $res=maestro_unbuffered($maestro, $sql);
        while($row=maestro_fetch($maestro, $res)){
            $ATTIVITAID=$row["SYSID"];
            $DESCRIPTION=$row["DESCRIPTION"];
            $REGISTRY=$row["REGISTRY"];
            $PRATICAID=$row["PRATICAID"];
            $MOTIVEID=$row["MOTIVEID"];
            $TARGETID=$row["TARGETID"];
            $TARGETTIME=substr(qv_strtime($row["TARGETTIME"]), 0, 8);
            $PHASE=intval($row["PHASE"]);
            $NEWPHASE=0;
            
            // LEGGO LA PRATICA
            $sql="";
            $sql.="SELECT ";
            $sql.="QW_PRATICHE.DESCRIPTION AS DESCRIPTION, ";
            $sql.="QW_PRATICHE.STATUS AS STATUS, ";
            $sql.="QW_PROCSTATI.ATTOREID AS ATTOREID ";
            $sql.="FROM QW_PRATICHE ";
            $sql.="INNER JOIN QW_PROCSTATI ";
            $sql.="ON QW_PROCSTATI.SYSID=QW_PRATICHE.STATOID ";
            $sql.="WHERE QW_PRATICHE.SYSID='$PRATICAID'";

            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                $ATTOREID=$r[0]["ATTOREID"];
                $PRATICADESCR=$r[0]["DESCRIPTION"];
                $PRATSTATUS=intval($r[0]["STATUS"]);

                if($PRATSTATUS==0){
                    // LEGGO IL MOTIVO
                    $sql="SELECT * FROM QW_MOTIVIATTIVITA WHERE SYSID='$MOTIVEID'";
                    maestro_query($maestro, $sql, $s);
                    if(count($s)==1){
                        $PREAVVISO=intval($s[0]["PREAVVISO"]);
                        $INVIOEMAIL=intval($s[0]["INVIOEMAIL"]);
                    
                        if($INVIOEMAIL){
                            if($TARGETTIME<=$TODAY){
                                $NEWPHASE=2;
                            }
                            elseif($PHASE==0){  // Non è stato dato il preavviso
                                if($PREAVVISO>0){
                                    // DETERMINO LA DATA DI PREAVVISO
                                    $d=substr($TARGETTIME,0,4)."-".substr($TARGETTIME,4,2)."-".substr($TARGETTIME,6,2);
                                    $d=date_create($d);
                                    $DATAPREAVV=date_format(ry_dateadd($d, -$PREAVVISO), "Ymd");
                                    if($DATAPREAVV<=$TODAY){
                                        $NEWPHASE=1;
                                    }
                                }
                            }
                            if($NEWPHASE>0){
                                // PREPARO LA SCHEDA ELATIVA ALLA SCADENZA
                                $SCHEDA="";
                                $ds=substr($TARGETTIME,6,2)."/".substr($TARGETTIME,4,2)."/".substr($TARGETTIME,0,4);
                                if($NEWPHASE==1)
                                    $tipo="prossima";
                                elseif($TARGETTIME==$TODAY)
                                    $tipo="odierna";
                                else
                                    $tipo="avvenuta";
                                
                                $SCHEDA.="Scadenza $tipo ($ds - $PRATICADESCR)<br>";
                                $SCHEDA.="<b>$DESCRIPTION</b><br><br>";
                                
                                // LA CONCATENO AL RAPPORTINO DEL DESTINATARIO
                                if(isset($messaggi[$TARGETID]))
                                    $messaggi[$TARGETID].=$SCHEDA;
                                else
                                    $messaggi[$TARGETID]=$SCHEDA;

                                // SE DIVERSO DAL DESTINATARIO
                                // LA CONCATENO AL RAPPORTINO DEL PROPRIETARIO DELLA PRATICA
                                if($ATTOREID!=$TARGETID){
                                    if(isset($messaggi[$ATTOREID]))
                                        $messaggi[$ATTOREID].=$SCHEDA;
                                    else
                                        $messaggi[$ATTOREID]=$SCHEDA;
                                }
                                
                                // MEMORIZZO IL CAMBIO FASE DELL'ATTIVITA'
                                $fasi[$ATTIVITAID]=$NEWPHASE;
                            }
                        }
                    }
                }
            }
        }
        maestro_free($maestro, $res);

        foreach($messaggi as $ATTOREID => $REGISTRY){
            // CREO UN DOCUMENTO DI SUPPORTO PER L'EMAIL
            $datax=array();
            $datax["DESCRIPTION"]="Avviso di scadenza attività";
            $datax["REGISTRY"]=$REGISTRY;
            $datax["TYPOLOGYID"]=qv_actualid($maestro, "0DOCUMENTI00");
            $datax["GENREID"]=qv_actualid($maestro, "0TIMEDAYS000");
            $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTANNOT");
            $datax["CONSISTENCY"]=2;
            $datax["DELETING"]=0;
            $jret=qv_arrows_insert($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            // PIPE
            $DOCUMENTOID=$jret["SYSID"];
            
            // INVIO L'EMAIL
            $datax=array();
            $datax["TABLE"]="QVARROWS";
            $datax["SYSID"]=$DOCUMENTOID;
            $datax["MAILTABLE"]="QW_ATTORI";
            $datax["RECIPIENTS"]=$ATTOREID;
            $jret=qv_sendmail($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            
            // CANCELLO IL DOCUMENTO DI SUPPORTO
            $datax=array();
            $datax["SYSID"]=$DOCUMENTOID;
            $jret=qv_arrows_delete($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        
        // CAMBIO PHASE PER SEGNARE CHE GLI AVVISI SONO STATI DATI
        foreach($fasi as $ATTIVITAID => $PHASE){
            maestro_execute($maestro, "UPDATE QVARROWS SET PHASE=$PHASE WHERE SYSID='$ATTIVITAID'");
        }
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