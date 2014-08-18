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

<div id="lbalias"></div>
<div id="txalias"></div>
<div id="lbpwd"></div>
<div id="txpwd"></div>
<div id="lblogin"></div>
<?php 
    if($appname!=""){ 
?>
<div id="lbsetup"></div>
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
<div id="lbreset"></div>
<?php 
    }
?>
</div>
