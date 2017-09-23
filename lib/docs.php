<?php
include_once("lib.php");

$document_id = querystring("id");

$db = new db_helper();
$db->CommandText("SELECT document_body FROM documents WHERE document_id = '%s' LIMIT 1");
$db->Parameters($document_id);
$db->Execute();
if ($db->Rows_Count() > 0) {
	$r = $db->Rows();
	echo $r['document_body'];
}

?>