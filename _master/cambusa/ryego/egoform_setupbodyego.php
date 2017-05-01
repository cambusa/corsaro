<?php
/****************************************************************************
* Name:            egoform_setupbodyego.php                                 *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.70                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2016  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<div style="height:470px;">

<div class="toolfunction" id="settings">
<span class="form-title">OPZIONI</span>
<div id="lboptduration" babelcode="EGO_OPT_DURATION"></div><div id="txoptduration"></div>
<div id="lboptwarning" babelcode="EGO_OPT_WARNING"></div><div id="txoptwarning"></div>
<div id="lboptminlen" babelcode="EGO_OPT_MINLEN"></div><div id="txoptminlen"></div>
<div id="lboptdefault" babelcode="EGO_OPT_DEFAULT"></div><div id="txoptdefault"></div>
<div id="lboptupperlower" babelcode="EGO_OPT_UPPERLOWER"></div><div id="txoptupperlower"></div>
<div id="lboptletterdigit" babelcode="EGO_OPT_LETTERDIGIT"></div><div id="txoptletterdigit"></div>
<div id="lboptsaveuser" babelcode="EGO_OPT_SAVEUSER"></div><div id="txoptsaveuser"></div>
<div id="lboptemailreset" babelcode="EGO_OPT_EMAILRESET"></div><div id="txoptemailreset"></div>
<div id="lboptvalidator" babelcode="EGO_OPT_VALIDATOR"></div><div id="txoptvalidator"></div>
</div>

<div class="toolfunction" id="users">
<span class="form-title">UTENTI</span>
<div id="gridusers"></div>
<div id="lbusr_filter" babelcode="EGO_USR_FILTER"></div><div id="txusr_filter"></div>
<div id="lbusr_only" babelcode="EGO_USR_ONLY"></div><div id="chkusr_only"></div>
<div id="lbusr_refresh" babelcode="EGO_USR_REFRESH"></div>
<div id="lbusr_use" babelcode="EGO_USR_USE"></div><div id="lbusr_user"></div>
<div id="lbusr_alias" babelcode="EGO_USR_ALIAS"></div><div id="txusr_alias"></div>
<div id="lbusr_email" babelcode="EGO_USR_EMAIL"></div><div id="txusr_email"></div>
<div id="lbusr_registry" babelcode="EGO_USR_REGISTRY"></div><div id="txusr_registry"></div>
<div id="lbusr_demiurge" babelcode="EGO_USR_DEMIURGE"></div><div id="chkusr_demiurge"></div>
<div id="lbusr_admin" babelcode="EGO_USR_ADMIN"></div><div id="chkusr_admin"></div>
<div id="lbusr_as" babelcode="EGO_USR_AS"></div>
<div id="lbusr_action_newuser" babelcode="EGO_USR_NEWUSER"></div>
<div id="lbusr_action_newalias" babelcode="EGO_USR_NEWALIAS"></div>
<div id="lbusr_action_updateuser" babelcode="EGO_USR_UPDATEUSER"></div>
<div id="lbusr_or" babelcode="EGO_USR_OR"></div>
<div id="lbusr_action_reset" babelcode="EGO_USR_RESET"></div>
<div id="lbusr_action_activate" babelcode="EGO_USR_ACTIVATE"></div>
<div id="lbusr_action_deletealias" babelcode="EGO_USR_DELETEALIAS"></div>
<div id="lbusr_action_deleteuser" babelcode="EGO_USR_DELETEUSER"></div>
<div id="lbusr_action_deleteall" babelcode="EGO_USR_DELETEALL"></div>
</div>

<div class="toolfunction" id="applications">
<span class="form-title">APPLICAZIONI</span><BR/>
<BR/>
<div id="tabs" class="tabs-bottom" style="position:absolute;width:630px;height:340px;">
    <ul>
        <li><a id="tabapplications" href="#tabs-1">Applicazioni</a></li>
        <li><a id="tabenvirons" href="#tabs-2">Ambienti</a></li>
        <li><a id="tabenvusers" href="#tabs-3">Ambiente/Utenti</a></li>
        <li><a id="tabroles" href="#tabs-4">Ruoli</a></li>
        <li><a id="tabroleusers" href="#tabs-5">Ruolo/Utenti</a></li>
    </ul>
    <div class="tabs-spacer"></div>
    <div id="tabs-1" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridapplications"></div>
        <div id="lbapp_refresh" babelcode="EGO_APP_REFRESH"></div>
        <div id="lbapp_use" babelcode="EGO_APP_USE"></div>
        <div id="lbapp_name" babelcode="EGO_APP_NAME"></div><div id="txapp_name"></div>
        <div id="lbapp_descr" babelcode="EGO_APP_DESCR"></div><div id="txapp_descr"></div>
        <div id="lbapp_as" babelcode="EGO_APP_AS"></div>
        <div id="lbapp_action_insert" babelcode="EGO_APP_INSERT"></div>
        <div id="lbapp_action_update" babelcode="EGO_APP_UPDATE"></div>
        <div id="lbapp_or" babelcode="EGO_APP_OR"></div>
        <div id="lbapp_action_delete" babelcode="EGO_APP_DELETE"></div>        
    </div>
    <div id="tabs-2" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridenvirons"></div>
        <div id="lbenv_refresh" babelcode="EGO_ENV_REFRESH"></div>
        <div id="lbenv_use" babelcode="EGO_ENV_USE"></div>
        <div id="lbenv_name" babelcode="EGO_ENV__NAME"></div><div id="txenv_name"></div>
        <div id="lbenv_descr" babelcode="EGO_ENV_DESCR"></div><div id="txenv_descr"></div>
        <div id="lbenv_as" babelcode="EGO_ENV_AS"></div>
        <div id="lbenv_action_insert" babelcode="EGO_ENV_INSERT"></div>
        <div id="lbenv_action_update" babelcode="EGO_ENV_UPDATE"></div>
        <div id="lbenv_or" babelcode="EGO_ENV_OR"></div>
        <div id="lbenv_action_delete" babelcode="EGO_ENV_DELETE"></div>        
    </div>
    <div id="tabs-3" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridenvuser"></div>
        <div id="gridenvusersel"></div>
        <div id="lbenvusr_refresh" babelcode="EGO_ENVUSR_REFRESH"></div>
        <div id="lbenvusr_filter"></div>
        <div id="lbenvusr_action_add"></div>
        <div id="lbenvusr_action_remove"></div>
    </div>
    <div id="tabs-4" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridroles"></div>
        <div id="lbrole_refresh" babelcode="EGO_ROLE_REFRESH"></div>
        <div id="lbrole_use" babelcode="EGO_ROLE_USE"></div>
        <div id="lbrole_name" babelcode="EGO_ROLE_NAME"></div><div id="txrole_name"></div>
        <div id="lbrole_descr" babelcode="EGO_ROLE_DESCR"></div><div id="txrole_descr"></div>
        <div id="lbrole_as" babelcode="EGO_ROLE_AS"></div>
        <div id="lbrole_action_insert" babelcode="EGO_ROLE_INSERT"></div>
        <div id="lbrole_action_update" babelcode="EGO_ROLE_UPDATE"></div>
        <div id="lbrole_or" babelcode="EGO_ROLE_OR"></div>
        <div id="lbrole_action_delete" babelcode="EGO_ROLE_DELETE"></div>        
    </div>
    <div id="tabs-5" style="height:300;width:630;padding:0px;margin:0px;">
        <div id="gridroleuser"></div>
        <div id="gridroleusersel"></div>
        <div id="lbroleusr_refresh" babelcode="EGO_ROLEUSR_REFRESH"></div>
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
<div id="lblng_refresh" babelcode="EGO_LNG_REFRESH"></div>
<div id="lblng_use" babelcode="EGO_LNG_USE"></div>
<div id="lblng_lang" babelcode="EGO_LNG_NAME"></div><div id="txlng_lang"></div>
<div id="lblng_descr" babelcode="EGO_LNG_DESCR"></div><div id="txlng_descr"></div>
<div id="lblng_as" babelcode="EGO_LNG_AS"></div>
<div id="lblng_action_insert" babelcode="EGO_LNG_INSERT"></div>
<div id="lblng_action_update" babelcode="EGO_LNG_UPDATE"></div>
<div id="lblng_action_apply" babelcode="EGO_LNG_APPLY"></div>
<div id="lblng_or" babelcode="EGO_LNG_OR"></div>
<div id="lblng_action_delete" babelcode="EGO_LNG_DELETE"></div>
</div>

<div class="toolfunction" id="sessions">
<span class="form-title">SESSIONI</span>
<div id="gridsessions"></div>
<div id="lbses_only" babelcode="EGO_SES_ONLY"></div><div id="chkses_only"></div>
<div id="lbses_refresh" babelcode="EGO_SES_REFRESH"></div>
<div id="lbses_filter"></div>
<div id="lbses_action_close" babelcode="EGO_SES_CLOSE"></div>
<div id="lbses_action_deleteall" babelcode="EGO_SES_DELETEALL"></div>
</div>

<div class="toolfunction" id="changepassword">
<span class="form-title">CAMBIO PASSWORD</span>
<div id="lbalias" babelcode=""></div><div id="txalias"></div>
<div id="lbcurrpwd" babelcode="EGO_PWD_CURRENT"></div><div id="txcurrpwd"></div>
<div id="lbnewpwd" babelcode="EGO_PWD_NEW"></div><div id="txnewpwd"></div>
<div id="lbrepeatpwd" babelcode="EGO_PWD_REPEAT"></div><div id="txrepeatpwd"></div>
<div id="actionPassword" babelcode="EGO_PWD_CONFIRM"></div>
</div>

<!-- MESSAGES FOR BABEL -->
<div style="position:absolute;display:none;">
<div id="lbselectsession" babelcode="EGO_MSG_SELECTSESSION"></div>
<div id="lbconfirmdelsessions" babelcode="EGO_CONFIRMDELSESSIONS"></div>
<div id="lbconfirmresetpwd" babelcode="EGO_CONFIRMRESETPWD"></div>
<div id="lbconfirmdelalias" babelcode="EGO_CONFIRMDELALIAS"></div>
<div id="lbconfirmdeluser" babelcode="EGO_CONFIRMDELUSER"></div>
<div id="lbconfirmdelusers" babelcode="EGO_CONFIRMDELUSERS"></div>
<div id="lbconfirmdelapp" babelcode="EGO_CONFIRMDELAPP"></div>
<div id="lbconfirmdelenviron" babelcode="EGO_CONFIRMDELENVIRON"></div>
<div id="lbconfirmdelrole" babelcode="EGO_CONFIRMDELROLE"></div>
<div id="lbconfirmdellang" babelcode="EGO_CONFIRMDELLANG"></div>
<div id="lbside_settings" babelcode="EGO_TITLE_SETTINGS"></div>
<div id="lbside_users" babelcode="EGO_TITLE_USERS"></div>
<div id="lbside_applications" babelcode="EGO_TITLE_APPLICATIONS"></div>
<div id="lbside_languages" babelcode="EGO_TITLE_LANGUAGES"></div>
<div id="lbside_sessions" babelcode="EGO_TITLE_SESSIONS"></div>
<div id="lbside_changepassword" babelcode="EGO_TITLE_CHANGEPASSWORD"></div>
<div id="lbauthenticationservice" babelcode="EGO_AUTHENTICATION_SERVICE"></div>
<div id="lbtabapplications" babelcode="EGO_TAB_APPLICATIONS"></div>
<div id="lbtabenvirons" babelcode="EGO_TAB_ENVIRONS"></div>
<div id="lbtabenvusers" babelcode="EGO_TAB_ENVUSERS"></div>
<div id="lbtabroles" babelcode="EGO_TAB_ROLES"></div>
<div id="lbtabroleusers" babelcode="EGO_TAB_ROLEUSERS"></div>
</div>

</div>
