<?php
if(!isset($tocambusa))
    $tocambusa="../";
include_once "$tocambusa/tbs_us/tbs_class.php";
include_once "$tocambusa/tbs_us/plugins/tbs_plugin_opentbs.php";

$TBS = new clsTinyButStrong;
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

$TBS->LoadTemplate('esempio.odt');

$data=array();
$data["nome"]="Gian";
$data["cognome"]="Giulippo";

$TBS->MergeField('blk', $data);

$TBS->Show();

//$TBS->Show(TBS_NOTHING); // terminate the merging without leaving the script nor to display the result
//$result = $TBS->Source;
?>