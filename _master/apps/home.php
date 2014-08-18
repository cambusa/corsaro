<?php
if(isset($_GET["project"]))
    $progetto=strtoupper($_GET["project"]);
else
    $progetto="";
?>
<!DOCTYPE html>

<html>
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
    <meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
	<title>Portale Progetto <?php print $progetto ?></title>
</head>

<style>
body{font-family:sans-serif;font-size:12px;}
a{color: maroon;text-decoration:none;font-weight:bold;}
a:hover{text-decoration:underline;}
table{font-family:sans-serif;font-size:12px;}
#title{position:absolute;left:50px;top:40px;font-size:24px;}
#frame{position:absolute;left:50px;top:120px;font-size:18px;}
</style>

<body>

<div id="title">
Portale Progetto <?php print $progetto ?>
</div>

<div id="frame">

<b>Passo 1)</b> Inizializzazione sistema:
<a href="../../cambusa/sysinstall/sysinstall.php/?project=<?php print strtolower($progetto) ?>" target="_blank">Monad, Ego, Pulse e dizionari</a><br>
<blockquote>
<span style="font-size:14px;">
Il database di <i>Ego</i> viene inizializzato con un utente amministratore:<br>
&nbsp;&nbsp;&nbsp;utente <i>demiurge</i><br>
&nbsp;&nbsp;&nbsp;password <i>sonoio</i><br>
La password deve essere cambiata al primo utilizzo.
</span>
</blockquote>
<br>

<b>Passo 2)</b> Utenti e autorizzazioni:
<a href="../../cambusa/ryego/ryego.php" target="_blank">Ego</a><br>
<blockquote>
<span style="font-size:14px;">
Si cambi la password e si inseriscano utenti e autorizzazioni.
</span>
</blockquote>
<br>

<b>Passo 3)</b> Strutturazione database:
<a href="../../cambusa/rymaestro/rymaestro.php" target="_blank">Maestro</a><br>
<blockquote>
<span style="font-size:14px;">
Sotto la sezione <i>Aggiornamento</i>
si aggiorni il database <i><?php print strtolower($progetto) ?></i>.
</span>
</blockquote>
<br>

<b>Passo 4)</b> Utilizzo del software:
<a href="../corsaro/corsaro.php" target="_blank">Corsaro</a><br>
<blockquote>
<span style="font-size:14px;">
Al primo utilizzo o dopo un aggiornamento del database, si apra<br>
&nbsp;&nbsp;&nbsp;<i>Manutenzione (cartella) -> Opzioni (funzione) -> Manutenzione (tab)</i><br>
e si lanci<br>
&nbsp;&nbsp;&nbsp;<i>Aggiornamento di tutte le viste</i>.
</span>
</blockquote>
<br>

</div>

</body>
</html>
