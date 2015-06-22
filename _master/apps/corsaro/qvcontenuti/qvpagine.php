<?php
/****************************************************************************
* Name:            qvpagine.php                                             *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<div relid="tabs" >
    <div>
        <div relid="lbf_site" babelcode="SITE"></div><div relid="txf_site"></div>
        <div relid="modetabs" >
            <div>
                <div relid="lbf_search" babelcode="SEARCH"></div><div relid="txf_search"></div>
                <div relid="lbf_classe" babelcode="CLASS"></div><div relid="txf_classe"></div>
                <div relid="lbf_parent" babelcode="PARENT"></div><div relid="txf_parent"></div>
                <div relid="gridsel"></div>
                <div relid="oper_new" babelcode="BUTTON_NEW"></div>
                <div relid="oper_delete" babelcode="BUTTON_SELDELETE"></div>
            </div>
            <div>
                <div relid="treesel"></div>
            </div>
        </div>
        <div relid="pagepreview"><div relid="previewinner"></div></div>
    </div>
    <div>
        <div relid="LB_NAME" babelcode="NAME"></div><div relid="NAME"></div>
        <div relid="LB_SITEID" babelcode="SITE"></div><div relid="SITEID"></div>
        <div relid="LB_DESCRIPTION" babelcode="TITLE"></div><div relid="DESCRIPTION"></div>
        <div relid="LB_ABSTRACT" babelcode="ABSTRACT"></div><div relid="ABSTRACT"></div>
        <div relid="CLASSI"></div>
        <div relid="LB_TAG" babelcode="TAGS"></div><div relid="TAG"></div>
        <div relid="LB_AUXTIME" babelcode="PAGE_DATE"></div><div relid="AUXTIME"></div>
        <div relid="LB_SCOPE" babelcode="VISIBLE"></div><div relid="SCOPE"></div>
        <div relid="LB_CONTENTTYPE" babelcode="PAGE_TYPE"></div><div relid="CONTENTTYPE"></div>
        <div relid="LB_LANGUAGE" babelcode="PAGE_VOICE"></div><div relid="LANGUAGE"></div><div relid="GENDER"></div>
        <div relid="LB_SYSTEMID" babelcode="PAGE_ID"></div><div relid="SYSTEMID"></div>
        <div style="position:absolute;left:0px;top:350px;">
            <div relid="typewysiwyg" style="position:absolute;display:none;">
                <div relid="WYSIWYG"></div>
            </div>
            <div relid="typehtml" style="position:absolute;display:none;">
                <div relid="LB_HTMLDETAILS" babelcode="DETAILS"></div><div relid="HTMLDETAILS"></div>
                <div relid="HTML"></div>
            </div>
            <div relid="typemultimedia" style="position:absolute;display:none;">
                <div relid="LB_VIDEO" babelcode="URL"></div><div relid="VIDEO"></div>
                <div relid="VIDEO_WYSIWYG"></div>
            </div>
            <div relid="typewikipedia" style="position:absolute;display:none;">
                <div relid="WIKIPEDIA"></div>
            </div>
            <div relid="typeattachment" style="position:absolute;display:none;">
                <div relid="LB_ATTDETAILS" babelcode="DETAILS"></div><div relid="ATTDETAILS"></div>
                <div relid="ATTACH_WYSIWYG"></div>
            </div>
            <div relid="typegallery" style="position:absolute;display:none;"></div>
            <div relid="typeframes" style="position:absolute;display:none;">
                <div relid="FRAMES"></div>
                <div relid="operf_refresh" babelcode="REFRESH"></div>
                <div relid="operf_first" babelcode="BUTTON_FIRST"></div>
                <div relid="operf_up" babelcode="BUTTON_UP"></div>
                <div relid="operf_down" babelcode="BUTTON_DOWN"></div>
                <div relid="operf_last" babelcode="BUTTON_LAST"></div>
            </div>
            <div relid="typeurl" style="position:absolute;display:none;">
                <div relid="CONTENTURL"></div>
            </div>
            <div relid="typeembedding" style="position:absolute;display:none;">
                <div relid="LB_EMBEDHOST" babelcode="HOST"></div><div relid="EMBEDHOST"></div>
                <div relid="LB_EMBEDENV" babelcode="ENVIRON"></div><div relid="EMBEDENV"></div>
                <div relid="LB_EMBEDSITE" babelcode="SITE"></div><div relid="EMBEDSITE"></div>
                <div relid="LB_EMBEDID" babelcode="CODE"></div><div relid="EMBEDID"></div>
            </div>
            <div relid="typemarquee" style="position:absolute;display:none;">
                <div relid="MARQUEETYPE"></div>
                <div relid="LB_RECENTS" babelcode="PAGE_NUMITEM"></div><div relid="RECENTS"></div>
                <div relid="LB_MARDETAILS" babelcode="DETAILS"></div><div relid="MARDETAILS"></div>
            </div>
            <div relid="typetools" style="position:absolute;display:none;">
                <div relid="LB_SEARCHITEMS" babelcode="PAGE_NUMITEM"></div><div relid="SEARCHITEMS"></div>
                <div relid="LB_SERDETAILS" babelcode="DETAILS"></div><div relid="SERDETAILS"></div>
            </div>
            <div relid="typehomelink" style="position:absolute;display:none;"></div>
            <div relid="typesummary" style="position:absolute;display:none;">
                <div relid="LB_PARENTID" babelcode="PARENT"></div><div relid="PARENTID"></div>
                <div relid="LB_SUMDETAILS" babelcode="DETAILS"></div><div relid="SUMDETAILS"></div>
            </div>
            <div relid="typenavigator" style="position:absolute;display:none;">
                <div relid="LB_NAVDETAILS" babelcode="DETAILS"></div><div relid="NAVDETAILS"></div>
                <div relid="LB_NAVTOOL" babelcode="PAGE_NAVTOOL"></div><div relid="NAVTOOL"></div>
                <div relid="LB_NAVSORTING" babelcode="PAGE_NAVSORTING"></div><div relid="NAVSORTING"></div>
                <div relid="LB_NAVHOME" babelcode="PAGE_NAVHOME"></div><div relid="NAVHOME"></div>
                <div relid="LB_NAVPRIMARY" babelcode="PAGE_NAVPRIMARY"></div><div relid="NAVPRIMARY"></div>
                <div relid="LB_NAVPARENTS" babelcode="PAGE_NAVPARENTS"></div><div relid="NAVPARENTS"></div>
                <div relid="LB_NAVSIBLINGS" babelcode="PAGE_NAVSIBLINGS"></div><div relid="NAVSIBLINGS"></div>
                <div relid="LB_NAVRELATED" babelcode="PAGE_NAVRELATED"></div><div relid="NAVRELATED"></div>
            </div>
            <div relid="typemailus" style="position:absolute;display:none;">
                <div relid="LB_EMAIL" babelcode="EMAIL"></div><div relid="EMAIL"></div>
                <div relid="EMAIL_WYSIWYG"></div>
            </div>
            <div relid="typeinclude" style="position:absolute;display:none;">
                <div relid="LB_INCLUDEFILE" babelcode="PAGE_SOURCE"></div><div relid="INCLUDEFILE"></div>
            </div>
            <div relid="typeforum" style="position:absolute;display:none;"></div>
            <div relid="typecopyright" style="position:absolute;display:none;">
                <div relid="LB_DEALER" babelcode="PAGE_DEALER"></div><div relid="DEALER"></div>
                <div relid="LB_AUTHOR" babelcode="PAGE_AUTHOR"></div><div relid="AUTHOR"></div>
            </div>
        </div>
        <div relid="oper_contextengage" babelcode="SAVE"></div>
        <div relid="oper_browser" babelcode="PAGE_SHOW"></div>
    </div>
    <div relid="filemanager"></div>
    <div>
        <div relid="correlati_context"></div>
        <div relid="operp_add" babelcode="REL_ADD"></div>
        <div relid="operp_remove" babelcode="REL_REMOVE"></div>
        <div relid="operp_refresh" babelcode="REFRESH"></div>
        <div relid="gridparent"></div>
        <div relid="operr_refresh" babelcode="REFRESH"></div>
        <div relid="RELATED"></div>
        <div relid="operr_first" babelcode="BUTTON_FIRST"></div>
        <div relid="operr_up" babelcode="BUTTON_UP"></div>
        <div relid="operr_down" babelcode="BUTTON_DOWN"></div>
        <div relid="operr_last" babelcode="BUTTON_LAST"></div>
    </div>
</div>
