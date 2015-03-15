<?php 
/****************************************************************************
* Name:            quiverlck.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_solvealloc($maestro, $data, &$SYSID, &$TABLENAME, &$RECORDID){
    global $babelcode, $babelparams;
    $SYSID="";
    $TABLENAME="";
    $RECORDID="";
    // INDIVIDUAZIONE DELL'ALLOCAZIONE (MEDIANTE SYSID OPPURE TABLENAME+RECORDID)
    if(isset($data["SYSID"])){
        // INDIVIDUAZIONE TRAMITE SYSID
        $SYSID=ryqEscapize($data["SYSID"]);
    }
    else{
        // INDIVIDUAZIONE TRAMITE TABLE E RECORD
        
        // DETERMINO TABLENAME
        if(isset($data["TABLENAME"])){
            $TABLENAME=ryqEscapize($data["TABLENAME"]);
            if($TABLENAME==""){
                $babelcode="QVERR_TABLENAME";
                $b_params=array();
                $b_pattern="Tabella non specificata";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_TABLENAME";
            $b_params=array();
            $b_pattern="Tabella non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO RECORDID
        qv_solverecord($maestro, $data, $TABLENAME, "RECORDID", "RECORDNAME", $RECORDID);
        if($RECORDID==""){
            $babelcode="QVERR_RECORDID";
            $b_params=array();
            $b_pattern="Identificatore di record non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
}

?>