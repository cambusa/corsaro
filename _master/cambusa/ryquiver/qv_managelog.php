<?php 
/****************************************************************************
* Name:            qv_managelog.php                                        *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_managelog($maestro, $data){
    global $babelcode, $babelparams, $path_databases;
    global $global_lastadmin;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // GESTIONE AMMINISTRATORE
        if($global_lastadmin==0){
            $babelcode="QVERR_FORBIDDEN";
            $b_params=array();
            $b_pattern="Autorizzazioni insufficienti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if(isset($_POST["env"]))
            $env=$_POST["env"];
        else
            $env="";

        $pathtoday=$path_databases . "_syslog/" . $env . "-" . date("Y-m-d") . ".log";
        $pathdir=$path_databases . "_syslog/" . $env . "-*.log";
        $d=glob($pathdir);
        foreach($d as $filename){
            try{
                if($filename!=$pathtoday){
                    @unlink($filename);
                }
            }
            catch(Exception $e){}
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