<?php
/****************************************************************************
* Name:            egoform_loginbody.php                                    *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<div style="height:300px;">

<div id="lbalias" babelcode="EGO_LOGIN_USER"></div>
<div id="txalias"></div>
<div id="lbpwd" babelcode="EGO_LOGIN_PWD"></div>
<div id="txpwd"></div>
<div id="lblogin" babelcode="EGO_LOGIN_LOGIN"></div>
<?php 
    if($appname!=""){ 
?>
<div id="lbsetup" babelcode="EGO_LOGIN_SETUP"></div>
<div id="chksetup"></div>
<?php 
    }
    try{
        $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME='emailreset'";
        maestro_query($maestro, $sql, $v);
        if(count($v)==1)
            $emailreset=intval($v[0]["VALUE"]);
        else
            $emailreset=0;
    }
    catch(Exception $e){
        $emailreset=0;
    }
    
    if($emailreset){
?>
<div id="lbreset" babelcode="EGO_LOGIN_RESET"></div>
<?php 
    }
?>
<!-- MESSAGES FOR BABEL -->
<div style="position:absolute;display:none;">
<div id="lbauthenticationservice" babelcode="EGO_AUTHENTICATION_SERVICE"></div>
<div id="lbsendpwd" babelcode="EGO_CONFIRMSENDPWD"></div>
<div id="lbmandatoryuser" babelcode="EGO_MSG_MANDATORYUSERALIAS"></div>
</div>

</div>
