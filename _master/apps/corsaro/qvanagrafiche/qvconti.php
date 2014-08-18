<?php
/****************************************************************************
* Name:            qvconti.php                                              *
* Project:         Corsaro                                                  *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
?>
<div relid="tabs" >
    <div>
        <div relid="gridsel"></div>
        <div relid="lbf_search"></div><div relid="txf_search"></div>
        <div relid="lbf_classe"></div><div relid="txf_classe"></div>
        <div relid="oper_refresh"></div>
        <div relid="oper_new"></div>
        <div relid="oper_print"></div>
        <div relid="oper_delete"></div>
    </div>
    <div>
        <div relid="LB_DESCRIPTION"></div><div relid="DESCRIPTION"></div>
        <div relid="LB_NUMCONTO"></div><div relid="NUMCONTO"></div>
        <div relid="LB_REFERENCE"></div><div relid="REFERENCE"></div>       <!-- CO.GE. -->
        <div relid="LB_REFOBJECTID"></div><div relid="REFOBJECTID"></div>   <!-- Conto padre -->
        <div relid="LB_TITOLAREID"></div><div relid="TITOLAREID"></div>     <!-- Attore di appartenenza -->
        <div relid="LB_BEGINTIME"></div><div relid="BEGINTIME"></div>       <!-- Inizio rapporto -->
        <div relid="LB_ENDTIME"></div><div relid="ENDTIME"></div>           <!-- Fine rapporto -->
        <div relid="LB_REFGENREID"></div><div relid="REFGENREID"></div>     <!-- Divisa -->
        
        <!-- Data saldo e Saldo vengono gestiti con movimenti di bilancio
        <div relid="LB_AUXTIME"></div><div relid="AUXTIME"></div>
        <div relid="LB_AUXAMOUNT"></div><div relid="AUXAMOUNT"></div>
        -->
        
        <div relid="LB_BANCAID"></div><div relid="BANCAID"></div>           <!-- Banca di appartenenza -->
        <div relid="FRAME_BANCA" style="display:none;">
            <div relid="LB_CIN"></div><div relid="CIN"></div>
            <div relid="LB_EUROCIN"></div><div relid="EUROCIN"></div>
            <div relid="LB_BIC"></div><div relid="BIC"></div>
            <div relid="LB_IBAN"></div><div relid="IBAN"></div>
            <div relid="LB_BBAN"></div><div relid="BBAN"></div>
        </div>
        <div relid="FRAME_VUOTO" style="position:absolute;left:20px;top:300px;width:400px;height:110px;background-color:#DDDDDD;display:block;"></div>
        <div relid="LB_TAG"></div><div relid="TAG"></div>
        <div relid="LB_REGISTRY"></div><div relid="REGISTRY"></div> <!-- NOTE -->
        <div relid="CLASSI"></div>
        <div relid="oper_contextengage"></div>
    </div>
    <div relid="filemanager"></div>
</div>
