<?php
/****************************************************************************
* Name:            library.php                                              *
* Project:         Corsaro                                                  *
* Version:         1.69                                                     *
* Description:     Arrows-oriented application                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$cacheversion=22;
CambusaLibrary("ryUpload");
CambusaLibrary("ryQuiver");
CambusaLibrary("GoogleMaps");
CambusaLibrary("CKEditor");
CambusaLibrary("Geography");
CambusaLibraryAdd("corsaro.js", "<script type='text/javascript' src='".$url_applications."corsaro/_javascript/corsaro.js?ver=$cacheversion' ></script>");
?>
