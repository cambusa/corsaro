<?php
/****************************************************************************
* Name:            egoform_setupbodyapp.php                                 *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<div style="height:370px;">

<div class="toolfunction" id="settings">
<span class="form-title">OPZIONI</span>
<div id="lbenviron" babelcode="EGO_SET_ENVIRON"></div><div id="lstenviron"></div>
<div id="lbrole" babelcode="EGO_SET_ROLE"></div><div id="lstrole"></div>
<div id="lblanguage" babelcode="EGO_SET_LANGUAGE"></div><div id="lstlanguage"></div>
<div id="lbcountry" babelcode="EGO_SET_COUNTRY"></div><div id="lstcountry"></div>
<div id="lbdebugmode" babelcode="EGO_SET_MODE"></div><div id="lstdebugmode"></div>
<div id="lbemail" babelcode="EGO_SET_EMAIL"></div><div id="txemail"></div>
</div>

<div class="toolfunction" id="changepassword">
<span class="form-title">CAMBIO PASSWORD</span>
<div id="lbcurrpwd" babelcode="EGO_PWD_CURRENT"></div><div id="txcurrpwd"></div>
<div id="lbnewpwd" babelcode="EGO_PWD_NEW"></div><div id="txnewpwd"></div>
<div id="lbrepeatpwd" babelcode="EGO_PWD_REPEAT"></div><div id="txrepeatpwd"></div>
<div id="actionPassword" babelcode="EGO_PWD_CONFIRM"></div>
</div>

<div class="toolfunction" id="deactivation">
<span class="form-title">DISATTIVAZIONE</span>
<div id="lbdeactivation">
La disattivazione dell'account comporta l'immediata uscita dal sistema 
e l'impossibilità a rientrarvi senza l'intervento di un amministratore.<br/>
I dati relativi all'utente saranno conservati, ma non sarà più possibile accedervi
e usarli per una nuova registrazione.
</div>
<div id="actionDeactivation" babelcode="EGO_DEACTIVATION"></div>
</div>

<div id="lbgo2app" babelcode="EGO_GOTOAPP"></div>

<!-- MESSAGES FOR BABEL -->
<div style="position:absolute;display:none;">
<div id="lbexpiredpwd" babelcode="EGO_MSG_EXPIREDPWD"></div>
<div id="lbexpiringpwd" babelcode="EGO_MSG_EXPIRINGPWD"></div>
<div id="lbside_settings" babelcode="EGO_TITLE_SETTINGS"></div>
<div id="lbside_changepassword" babelcode="EGO_TITLE_CHANGEPASSWORD"></div>
<div id="lbside_deactivation" babelcode="EGO_TITLE_DEACTIVATION"></div>
<div id="lbauthenticationservice" babelcode="EGO_AUTHENTICATION_SERVICE"></div>
<div id="lbconfirmdeactivate" babelcode="EGO_CONFIRMDEACTIVATE"></div>
</div>

</div>
