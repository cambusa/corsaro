<!DOCTYPE html>

<html>
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Diagnostics</title>
</head>

<style>
body{font-family:sans-serif;font-size:14px;}
a{color: maroon;text-decoration:none;font-weight:bold;}
a:hover{text-decoration:underline;}
table{font-family:sans-serif;font-size:14px;}
#title{position:absolute;left:50px;top:40px;font-size:24px;}
td.item{width:130px;height:20px;font-weight:bold;padding-left:10px;}
td.value{font-family:courier;}
.attention{color:red;}
.skip{height:10px;}
.section{font-size:18px;padding:5px;background:#F0F0F0;}
</style>

<body>

<?php 

include_once "../sysconfig.php";
include_once $path_cambusa."rywinz/rywinclude.php";
if(is_file($path_customize."_apps.php")){
    include $path_customize."_apps.php";
}

// SQLITE
if(extension_loaded('pdo_sqlite'))
    $sqlite="Enabled";
else
    $sqlite="<span class=\"attention\"><b>SQLite</b> is not enabled</span>: add <b>php_pdo_sqlite.dll</b> extension in <b>php.ini</b>!";

// MYSQL
if(extension_loaded('pdo_mysql'))
    $mysql="Enabled";
else
    $mysql="<span class=\"attention\"><b>MySQL</b> is not enabled</span>: add <b>php_mysqli.dll</b> extension in <b>php.ini</b>!";

// ORACLE
if(extension_loaded('oci8'))
    $oracle="Enabled";
else
    $oracle="<span class=\"attention\"><b>ORACLE</b> is not enabled</span>: add <b>php_oci8.dll</b> extension in <b>php.ini</b>!";

// LDAP
if(extension_loaded('ldap'))
    $ldap="Enabled";
else
    $ldap="<span class=\"attention\"><b>LDAP</b> is not enabled</span>: add <b>php_ldap.dll</b> extension in <b>php.ini</b>!";

// IMAP
if(extension_loaded('imap'))
    $imap="Enabled";
else
    $imap="<span class=\"attention\"><b>LDAP</b> is not enabled</span>: add <b>php_imap.dll</b> extension in <b>php.ini</b>!";

// OPENSSL
if(extension_loaded('openssl'))
    $openssl="Enabled";
else
    $openssl="<span class=\"attention\"><b>Open SSL</b> is not enabled</span>: add <b>php_openssl.dll</b> extension in <b>php.ini</b>!";

?>

<div style="margin:30px 30px;">

    <div class="section">Versions</div>
    <div class="skip"></div>
    <table>
        <tr><td class='item'>PHP </td><td class='value'><?php print PHP_VERSION ?></td></tr>
        <tr><td class='item'>Cambusa </td><td class='value'><?php print $cambusa_version ?></td></tr>
    </table>
    
    <div class="skip"></div>
    <div class="skip"></div>
    <div class="section">Extensions</div>
    <div class="skip"></div>
    <table>
        <tr><td class='item'>SQLite     </td><td class='value'><?php print $sqlite    ?></td></tr>
        <tr><td class='item'>MySQL      </td><td class='value'><?php print $mysql     ?></td></tr>
        <tr><td class='item'>Oracle     </td><td class='value'><?php print $oracle    ?></td></tr>
        <tr><td class='item'>LDAP       </td><td class='value'><?php print $ldap      ?></td></tr>
        <tr><td class='item'>IMAP       </td><td class='value'><?php print $imap      ?></td></tr>
        <tr><td class='item'>Open SSL   </td><td class='value'><?php print $openssl   ?></td></tr>
    </table>
    
    <div class="skip"></div>
    <div class="skip"></div>
    <div class="section">Paths</div>
    <div class="skip"></div>
    <table>
        <tr><td class='item'>Root       </td><td class='value'><?php print $path_root         ?></td></tr>
        <tr><td class='item'>Cambusa    </td><td class='value'><?php print $path_cambusa      ?></td></tr>
        <tr><td class='item'>Apps       </td><td class='value'><?php print $path_applications ?></td></tr>
        <tr><td class='item'>Customize  </td><td class='value'><?php print $path_customize    ?></td></tr>
        <tr><td class='item'>Databases  </td><td class='value'><?php print $path_databases    ?></td></tr>
    </table>
    
    <div class="skip"></div>
    <div class="skip"></div>
    <div class="section">URLs</div>
    <div class="skip"></div>
    <table>
        <tr><td class='item'>Root       </td><td class='value'><?php print $url_base         ?></td></tr>
        <tr><td class='item'>Cambusa    </td><td class='value'><?php print $url_cambusa      ?></td></tr>
        <tr><td class='item'>Apps       </td><td class='value'><?php print $url_applications ?></td></tr>
        <tr><td class='item'>Customize  </td><td class='value'><?php print $url_customize    ?></td></tr>
    </table>

</div>

</body>
</html>
