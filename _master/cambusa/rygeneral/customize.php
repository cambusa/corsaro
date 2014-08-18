<?php 
/****************************************************************************
* Name:            customize.php                                            *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
try{
    $tocambusa="../";
    include_once $tocambusa."ryquiver/quiversex.php";
    include_once $tocambusa."tbs_us/tbs_class.php";
    include_once $tocambusa."tbs_us/plugins/tbs_plugin_opentbs.php";
    include_once $tocambusa."odsgeneration/classes/OpenOfficeSpreadsheet.class.php";
    include_once $tocambusa."mpdf/mpdf.php";
    include_once $tocambusa."rypaper/report.php";
    include_once $tocambusa."rygeneral/format.php";
    include_once $tocambusa."rygeneral/datetime.php";
    include_once $tocambusa."ryvlad/ryvlad.php";

    if(isset($_POST["sessionid"]))
        $sessionid=$_POST["sessionid"];
    else
        $sessionid="";

    if(isset($_POST["env"]))
        $env=$_POST["env"];
    else
        $env="";

    if(isset($_POST["path"]))
        $path=$_POST["path"];
    else
        $path="";
    
    if(isset($_POST["funct"]))
        $funct=$_POST["funct"];
    else
        $funct="custMain";
    
    if(isset($_POST["data"]))
        $data=$_POST["data"];
    else
        $data=array();
    
    // APRO IL DATABASE
    $maestro=maestro_opendb($env, false);

    // VERIFICO IL BUON ESITO DELL'APERTURA
    if($maestro->conn!==false){
        // VALIDAZIONE CODICE DI SESSIONE
        if(qv_validatesession($maestro, $sessionid)){
            if(is_file($path_customize.$path)){
                include_once $path_customize.$path;
                if(function_exists($funct)){
                    $babelcode="QVERR_USERDEFINED";
                    $failure="";
                    $jret=$funct($maestro, $data);
                }
                else{
                    $babelcode="QVERR_NOFUNCTION";
                    $b_params=array("FUNCTION" => $funct);
                    $b_pattern="Funzione [{1}] inesistente";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            else{
                $babelcode="QVERR_NOFILE";
                $b_params=array("FUNCTION" => $path);
                $b_pattern="File [{1}] inesistente";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_NOSESSION";
            $b_params=array();
            $b_pattern="Sessione non valida";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    else{
        $babelcode="QVERR_UNKNOWN";
        $b_params=array();
        $b_pattern=$maestro->errdescr;
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
    
    // STRUTTURA DI RITORNO
    array_walk_recursive($jret, "maestro_escapize");
    print json_encode($jret);
}
catch(Exception $e){
    $jret=array();
    $jret["success"]=0;
    $jret["code"]=$babelcode;
    $jret["params"]=array();
    $jret["message"]=$e->getMessage();
    $jret["infos"]=array();
    array_walk_recursive($jret, "maestro_escapize");
    print json_encode($jret);
}
?>