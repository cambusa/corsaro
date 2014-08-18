<?php
/****************************************************************************
* Name:            ego_crypt.php                                            *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function decryptString($s, $privatekey){
    $rsa=new Crypt_RSA();
    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
    $rsa->loadKey($privatekey);
    $r=$rsa->decrypt( base64_decode($s) );
    unset($rsa);
    return $r;
}
?>