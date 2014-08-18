<?php
/****************************************************************************
* Name:            egoform_setupbodyego.php                                 *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<div style="height:470px;">

<div class="toolfunction" id="settings">
<span class="form-title">OPZIONI</span>
<div id="lboptduration"></div><div id="txoptduration"></div>
<div id="lboptwarning"></div><div id="txoptwarning"></div>
<div id="lboptminlen"></div><div id="txoptminlen"></div>
<div id="lboptdefault"></div><div id="txoptdefault"></div>
<div id="lboptupperlower"></div><div id="txoptupperlower"></div>
<div id="lboptletterdigit"></div><div id="txoptletterdigit"></div>
<div id="lboptsaveuser"></div><div id="txoptsaveuser"></div>
<div id="lboptemailreset"></div><div id="txoptemailreset"></div>
</div>

<div class="toolfunction" id="users">
<span class="form-title">UTENTI</span>
<div id="gridusers"></div>
<div id="lbusr_filter"></div><div id="txusr_filter"></div>
<div id="lbusr_only"></div><div id="chkusr_only"></div>
<div id="lbusr_refresh"></div>
<div id="lbusr_use"></div><div id="lbusr_user"></div>
<div id="lbusr_alias"></div><div id="txusr_alias"></div>
<div id="lbusr_email"></div><div id="txusr_email"></div>
<div id="lbusr_demiurge"></div><div id="chkusr_demiurge"></div>
<div id="lbusr_admin"></div><div id="chkusr_admin"></div>
<div id="lbusr_as"></div>
<div id="lbusr_action_newuser"></div>
<div id="lbusr_action_newalias"></div>
<div id="lbusr_action_updateuser"></div>
<div id="lbusr_or"></div>
<div id="lbusr_action_reset"></div>
<div id="lbusr_action_activate"></div>
<div id="lbusr_action_deletealias"></div>
<div id="lbusr_action_deleteall"></div>
</div>

<div class="toolfunction" id="applications">
<span class="form-title">APPLICAZIONI</span><BR/>
<BR/>
<div id="tabs" class="tabs-bottom" style="position:absolute;width:630px;height:340px;">
    <ul>
        <li><a href="#tabs-1">Applicazioni</a></li>
        <li><a href="#tabs-2">Ambienti</a></li>
        <li><a href="#tabs-3">Ambiente/Utenti</a></li>
        <li><a href="#tabs-4">Ruoli</a></li>
        <li><a href="#tabs-5">Ruolo/Utenti</a></li>
    </ul>
    <div class="tabs-spacer"></div>
    <div id="tabs-1" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridapplications"></div>
        <div id="lbapp_refresh"></div>
        <div id="lbapp_use"></div>
        <div id="lbapp_name"></div><div id="txapp_name"></div>
        <div id="lbapp_descr"></div><div id="txapp_descr"></div>
        <div id="lbapp_as"></div>
        <div id="lbapp_action_insert"></div>
        <div id="lbapp_action_update"></div>
        <div id="lbapp_or"></div>
        <div id="lbapp_action_delete"></div>        
    </div>
    <div id="tabs-2" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridenvirons"></div>
        <div id="lbenv_refresh"></div>
        <div id="lbenv_use"></div>
        <div id="lbenv_name"></div><div id="txenv_name"></div>
        <div id="lbenv_descr"></div><div id="txenv_descr"></div>
        <div id="lbenv_as"></div>
        <div id="lbenv_action_insert"></div>
        <div id="lbenv_action_update"></div>
        <div id="lbenv_or"></div>
        <div id="lbenv_action_delete"></div>        
    </div>
    <div id="tabs-3" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridenvuser"></div>
        <div id="gridenvusersel"></div>
        <div id="lbenvusr_refresh"></div>
        <div id="lbenvusr_filter"></div>
        <div id="lbenvusr_action_add"></div>
        <div id="lbenvusr_action_remove"></div>
    </div>
    <div id="tabs-4" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridroles"></div>
        <div id="lbrole_refresh"></div>
        <div id="lbrole_use"></div>
        <div id="lbrole_name"></div><div id="txrole_name"></div>
        <div id="lbrole_descr"></div><div id="txrole_descr"></div>
        <div id="lbrole_as"></div>
        <div id="lbrole_action_insert"></div>
        <div id="lbrole_action_update"></div>
        <div id="lbrole_or"></div>
        <div id="lbrole_action_delete"></div>        
    </div>
    <div id="tabs-5" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridroleuser"></div>
        <div id="gridroleusersel"></div>
        <div id="lbroleusr_refresh"></div>
        <div id="lbroleusr_filter"></div>
        <div id="lbroleusr_action_add"></div>
        <div id="lbroleusr_action_remove"></div>
    </div>
</div>
<div id="lbapp_status"></div>
</div>

<div class="toolfunction" id="languages">
<span class="form-title">LINGUE</span>
<div id="gridlanguages"></div>
<div id="lblng_refresh"></div>
<div id="lblng_use"></div>
<div id="lblng_lang"></div><div id="txlng_lang"></div>
<div id="lblng_descr"></div><div id="txlng_descr"></div>
<div id="lblng_as"></div>
<div id="lblng_action_insert"></div>
<div id="lblng_action_update"></div>
<div id="lblng_or"></div>
<div id="lblng_action_delete"></div>
</div>

<div class="toolfunction" id="sessions">
<span class="form-title">SESSIONI</span>
<div id="gridsessions"></div>
<div id="lbses_only"></div><div id="chkses_only"></div>
<div id="lbses_refresh"></div>
<div id="lbses_filter"></div>
<div id="lbses_action_close"></div>
<div id="lbses_action_deleteall"></div>
</div>

<div class="toolfunction" id="changepassword">
<span class="form-title">CAMBIO PASSWORD</span>
<div id="lbalias"></div>
<div id="txalias"></div>
<div id="lbcurrpwd"></div>
<div id="txcurrpwd"></div>
<div id="lbnewpwd"></div>
<div id="txnewpwd"></div>
<div id="lbrepeatpwd"></div>
<div id="txrepeatpwd"></div>
<div id="actionPassword"></div>
</div>

</div>
