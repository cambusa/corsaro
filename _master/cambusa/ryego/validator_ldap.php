<?php
/****************************************************************************
* Name:            validator_ldap.php                                       *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.70                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2016  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function egovalidator($user, $barepwd){
    global $path_databases;
    
    $ret=false;
    
    // ABILITAZIONE DELL'ESTENSIONE LDAP
    if(function_exists("ldap_connect")){
    
        // PARAMETRI DI CONFIGURAZIONE
        $ldap_host="";
        $ldap_port=0;
        $ldapconn=false;
        $domainuser=$user;
        $fileconfig=$path_databases."_configs/ldap.php";
        if(file_exists($fileconfig)){
            include($fileconfig);
        }
    
        // APERTURA LDAP
        if($ldap_host==""){
            $ldapconn=ldap_connect();
        }
        else{
            $domainuser.="@$ldap_host";
            if($ldap_port==0){
                $ldapconn=ldap_connect($ldap_host);
            }
            else{
                $ldapconn=ldap_connect($ldap_host, $ldap_port);
                $domainuser.=":$ldap_port";
            }
        }
        
        if($ldapconn){
            // AUTENTICAZIONE LDAP
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            $ldapbind=ldap_bind($ldapconn, $domainuser, $barepwd);
            
            if($ldapbind){
                $ret=true;
            }
            else{
                writelog(ldap_error($ldapconn));
            }
            ldap_unbind($ldapconn);
        }
        else{
            writelog("Unable to contact the LDAP server [ $ldap_host ]");
        }
    }
    return $ret;
}
?>