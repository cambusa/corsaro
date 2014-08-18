<?php 
/****************************************************************************
* Name:            qv_pages_indicize.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_pages_indicize($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL SITO
        $site=qv_solverecord($maestro, $data, "QW_WEBSITES", "SITEID", "", $SITEID, "*");
        if($SITEID==""){
            $babelcode="QVERR_SITEID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il sito";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $DEFAULTID=$site["DEFAULTID"];

        // RESETTO L'INDICIZZAZIONE CORRENTE
        $sql="UPDATE ARROWS_WEBCONTENTS SET SORTER=0 WHERE (SITEID='' OR SITEID='$SITEID')";
        maestro_execute($maestro, $sql, false);
        
        // LEGGO LA PAGINA PRINCIPALE
        $sql="SELECT SETRELATED FROM ARROWS_WEBCONTENTS WHERE SYSID='$DEFAULTID'";
        maestro_query($maestro, $sql, $p);
        if(count($p)==1){
            $SETRELATED=$p[0]["SETRELATED"];
            $indice=0;
            pages_indicize_node($maestro, $SITEID, $DEFAULTID, $SETRELATED, $indice);
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
function pages_indicize_node($maestro, $SITEID, $PARENTID, $SETRELATED, &$indice){
    // AGGIORNO IL SORTER DEL GENITORE
    $indice+=1;
    $sql="UPDATE ARROWS_WEBCONTENTS SET SORTER=$indice WHERE SYSID='$PARENTID'";
    maestro_execute($maestro, $sql, false);

    // CERCO I CORRELATI
    $sql="SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='$SETRELATED' ORDER BY SORTER";
    maestro_query($maestro, $sql, $s);
    for($i=0; $i<count($s); $i++){
        $SELECTEDID=$s[$i]["SELECTEDID"];
        // LEGGO IL CORRELATO
        $sql="SELECT SETRELATED FROM ARROWS_WEBCONTENTS WHERE SYSID='$SELECTEDID' AND SORTER=0 AND (SITEID='' OR SITEID='$SITEID')";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $SETRELATED=$r[0]["SETRELATED"];
            pages_indicize_node($maestro, $SITEID, $SELECTEDID, $SETRELATED, $indice);
        }
    }
}
?>