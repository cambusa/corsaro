<?php
/****************************************************************************
* Name:            ryatecoload.php                                          *
* Project:         Cambusa/sysInstall                                       *
* Version:         1.69                                                     *
* Description:     Cambusa installer                                        *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_upgradelib.php";

set_time_limit(0);

// SE IL DATABASE E' SQLITE EVENTUALMENTE LO CREO
maestro_checklite("ryateco");

// APERTURA DATABASE
$maestro=maestro_opendb("ryateco");

if($maestro->conn!==false){
    if(!maestro_istable($maestro, "ATECOCODICI")){
        maestro_upgrade($maestro);
        ateco_load($maestro);
    }
}

// CHIUSURA DATABASE
maestro_closedb($maestro);

function ateco_load($maestro){

    $currdir=realpath(dirname(__FILE__));
    
    $tr=array();
    $tr[" "]="_";
    $tr["'"]="_";
    $tr["-"]="_";
    $tr["ù"]="u";
    $tr["é"]="e";
    $tr["è"]="e";
    $tr["à"]="a";
    $tr["ì"]="i";
    $tr["ò"]="o";
    $tr[" "]="";
    $tr["\r"]="";
    $tr["\n"]="";
    
    log_open(log_unique("ateco"));
    
    $x=simplexml_load_file("ateco.xml");
    
    foreach($x->sezione as $sez){
        $SYSID=qv_createsysid($maestro);
        $DESCRIPTION=ryqEscapize($sez->titolo);
        $REGISTRY=ryqEscapize($sez->descrizione);
        $SEZIONE=$sez->codice;
        $DIVISIONE="";
        $GRUPPO="";
        $CLASSE="";
        $CATEGORIA="";
        $SOTTOCATEGORIA="";
        $NAME="ATECO_".$SEZIONE;
        $CODICE="";
        $sql="INSERT INTO ATECOCODICI(SYSID,NAME,DESCRIPTION,REGISTRY,SEZIONE,CODICE,DIVISIONE,GRUPPO,CLASSE,CATEGORIA,SOTTOCATEGORIA) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '$REGISTRY', '$SEZIONE', '$CODICE', '$DIVISIONE', '$GRUPPO', '$CLASSE', '$CATEGORIA', '$SOTTOCATEGORIA')";
        maestro_execute($maestro, $sql, false);

        foreach($sez->divisione as $div){
            $SYSID=qv_createsysid($maestro);
            $DESCRIPTION=ryqEscapize($div->titolo);
            $REGISTRY=ryqEscapize($div->descrizione);
            $DIVISIONE=$div->codice;
            $GRUPPO="";
            $CLASSE="";
            $CATEGORIA="";
            $SOTTOCATEGORIA="";
            $NAME="ATECO_" . $SEZIONE . "_" . $DIVISIONE;
            $CODICE=$DIVISIONE;
            $sql="INSERT INTO ATECOCODICI(SYSID,NAME,DESCRIPTION,REGISTRY,SEZIONE,CODICE,DIVISIONE,GRUPPO,CLASSE,CATEGORIA,SOTTOCATEGORIA) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '$REGISTRY', '$SEZIONE', '$CODICE', '$DIVISIONE', '$GRUPPO', '$CLASSE', '$CATEGORIA', '$SOTTOCATEGORIA')";
            maestro_execute($maestro, $sql, false);

            foreach($div->gruppo as $gr){
                $SYSID=qv_createsysid($maestro);
                $DESCRIPTION=ryqEscapize($gr->titolo);
                $REGISTRY=ryqEscapize($gr->descrizione);
                $GRUPPO=$gr->codice;
                $CLASSE="";
                $CATEGORIA="";
                $SOTTOCATEGORIA="";
                $NAME="ATECO_" . $SEZIONE . "_" . $DIVISIONE . "_" . $GRUPPO;
                $CODICE=$DIVISIONE . "." . $GRUPPO;
                $sql="INSERT INTO ATECOCODICI(SYSID,NAME,DESCRIPTION,REGISTRY,SEZIONE,CODICE,DIVISIONE,GRUPPO,CLASSE,CATEGORIA,SOTTOCATEGORIA) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '$REGISTRY', '$SEZIONE', '$CODICE', '$DIVISIONE', '$GRUPPO', '$CLASSE', '$CATEGORIA', '$SOTTOCATEGORIA')";
                maestro_execute($maestro, $sql, false);
                
                foreach($gr->classe as $cl){
                    $SYSID=qv_createsysid($maestro);
                    $DESCRIPTION=ryqEscapize($cl->titolo);
                    $REGISTRY=ryqEscapize($cl->descrizione);
                    $CLASSE=$cl->codice;
                    $CATEGORIA="";
                    $SOTTOCATEGORIA="";
                    $NAME="ATECO_" . $SEZIONE . "_" . $DIVISIONE . "_" . $GRUPPO . $CLASSE;
                    $CODICE=$DIVISIONE . "." . $GRUPPO . $CLASSE;
                    $sql="INSERT INTO ATECOCODICI(SYSID,NAME,DESCRIPTION,REGISTRY,SEZIONE,CODICE,DIVISIONE,GRUPPO,CLASSE,CATEGORIA,SOTTOCATEGORIA) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '$REGISTRY', '$SEZIONE', '$CODICE', '$DIVISIONE', '$GRUPPO', '$CLASSE', '$CATEGORIA', '$SOTTOCATEGORIA')";
                    maestro_execute($maestro, $sql, false);
                    
                    foreach($cl->categoria as $cat){
                        $SYSID=qv_createsysid($maestro);
                        $DESCRIPTION=ryqEscapize($cat->titolo);
                        $REGISTRY=ryqEscapize($cat->descrizione);
                        $CATEGORIA=$cat->codice;
                        $SOTTOCATEGORIA="";
                        $NAME="ATECO_" . $SEZIONE . "_" . $DIVISIONE . "_" . $GRUPPO . $CLASSE . "_" . $CATEGORIA;
                        $CODICE=$DIVISIONE . "." . $GRUPPO . $CLASSE . "." . $CATEGORIA;
                        $sql="INSERT INTO ATECOCODICI(SYSID,NAME,DESCRIPTION,REGISTRY,SEZIONE,CODICE,DIVISIONE,GRUPPO,CLASSE,CATEGORIA,SOTTOCATEGORIA) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '$REGISTRY', '$SEZIONE', '$CODICE', '$DIVISIONE', '$GRUPPO', '$CLASSE', '$CATEGORIA', '$SOTTOCATEGORIA')";
                        maestro_execute($maestro, $sql, false);
                        
                        foreach($cat->sottocategoria as $sub){
                            $SYSID=qv_createsysid($maestro);
                            $DESCRIPTION=ryqEscapize($sub->titolo);
                            $REGISTRY=ryqEscapize($sub->descrizione);
                            $SOTTOCATEGORIA=$sub->codice;
                            $NAME="ATECO_" . $SEZIONE . "_" . $DIVISIONE . "_" . $GRUPPO . $CLASSE . "_" . $CATEGORIA . $SOTTOCATEGORIA;
                            $CODICE=$DIVISIONE . "." . $GRUPPO . $CLASSE . "." . $CATEGORIA . $SOTTOCATEGORIA;
                            $sql="INSERT INTO ATECOCODICI(SYSID,NAME,DESCRIPTION,REGISTRY,SEZIONE,CODICE,DIVISIONE,GRUPPO,CLASSE,CATEGORIA,SOTTOCATEGORIA) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '$REGISTRY', '$SEZIONE', '$CODICE', '$DIVISIONE', '$GRUPPO', '$CLASSE', '$CATEGORIA', '$SOTTOCATEGORIA')";
                            maestro_execute($maestro, $sql, false);
                        }
                    }
                }
            }
        }
    }
    log_close();
}
?>