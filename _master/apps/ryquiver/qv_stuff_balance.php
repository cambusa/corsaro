<?php 
/****************************************************************************
* Name:            qv_stuff_balance.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_stuff_balance($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $balance=array();
        
        if(isset($data["SYSID"])){
            $buff=ryqEscapize($data["SYSID"]);
            $recs=explode("|", $buff);
        }
        else{
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare i record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if(isset($data["EVENTS"])){
            $buff=ryqEscapize($data["EVENTS"]);
            $events=explode("|", $buff);
        }
        
        foreach($recs as $i => $id){
            if($id!=""){
                $balance[$id]=array("GIACENZA" => array(), "DISPO" => array());
                foreach($events as $j => $time){
                    $time=qv_strtime($time);
                    if($time>"19000101000000"){
                        // GIACENZA
                        maestro_query($maestro, "SELECT * FROM QWCBALANCES WHERE SYSID='$id' AND EVENTTIME<=[:TIME($time)] ORDER BY EVENTTIME DESC", $r);
                        if(count($r)>0)
                            $balance[$id]["GIACENZA"][]=$r[0]["BALANCE"];
                        else
                            $balance[$id]["GIACENZA"][]=0;
                        
                        // DISPONIBILITA'
                        maestro_query($maestro, "SELECT * FROM QWYBALANCES WHERE SYSID='$id' AND EVENTTIME<=[:TIME($time)] ORDER BY EVENTTIME DESC", $r);
                        if(count($r)>0)
                            $balance[$id]["DISPO"][]=$r[0]["BALANCE"];
                        else
                            $balance[$id]["DISPO"][]=0;
                    }
                    else{
                        $balance[$id]["GIACENZA"][]=0;
                        $balance[$id]["DISPO"][]=0;
                    }
                }
            }
        }
        // VARIABILI DI RITORNO
        $babelparams["BALANCE"]=$balance;
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