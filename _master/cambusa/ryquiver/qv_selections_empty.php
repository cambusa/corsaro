<?php 
/****************************************************************************
* Name:            qv_selections_empty.php                                  *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../rymaestro/maestro_querylib.php";
function qv_selections_empty($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $LASTID="";
        $LIMIT=100;
        
        do{
            $arrsel=array();
            maestro_query($maestro,"SELECT {AS:TOP $LIMIT} * FROM QVSELECTIONS WHERE SYSID>'$LASTID' {O: AND ROWNUM=$LIMIT} ORDER BY SYSID {LM:LIMIT $LIMIT}{D:FETCH FIRST $LIMIT ROWS ONLY}", $b);
            $cnt=count($b);
            for($i=0;$i<$cnt;$i++){
                $LASTID=$b[$i]["SYSID"];
                $PARENTTABLE=$b[$i]["PARENTTABLE"];
                $PARENTFIELD=$b[$i]["PARENTFIELD"];
                if($PARENTFIELD==""){
                    $PARENTFIELD="SYSID";
                }
                $PARENTID=$b[$i]["PARENTID"];
                $SELECTEDTABLE=$b[$i]["SELECTEDTABLE"];
                $SELECTEDID=$b[$i]["SELECTEDID"];

                // CONTROLLO CHE ESISTANO PARENTID E SELECTEDID
                $cond=0;
                maestro_query($maestro, "SELECT SYSID FROM $PARENTTABLE WHERE $PARENTFIELD='$PARENTID'", $r);
                if(count($r)>0){
                    $cond+=1;
                }
                maestro_query($maestro, "SELECT SYSID FROM $SELECTEDTABLE WHERE SYSID='$SELECTEDID'", $r);
                if(count($r)>0){
                    $cond+=1;
                }
                if($cond<2){
                    // AGGIUNGO IL RECORD ALLA LISTA DEI CANCELLANDI
                    $arrsel[]=$LASTID;
                }
            }
            foreach($arrsel as $SYSID){
                $sql="DELETE FROM QVSELECTIONS WHERE SYSID='$SYSID'";
                maestro_execute($maestro, $sql, true);
            }

            // COMMIT TRANSACTION
            maestro_commit($maestro);
            
            // BEGIN TRANSACTION
            maestro_begin($maestro);

        }while($cnt==$LIMIT);
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