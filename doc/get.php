<?php
include_once("../lib/lib.php");

if(isset($_GET['getContentFromId'])) {
    $slug = $_GET['getContentFromId'];
    $db = new db_helper();
    $db->CommandText("SELECT document_body FROM documents WHERE document_id = '%s';");
    $db->Parameters($slug);
    $db->Execute();
    $content = '';
    if ($db->Rows_Count() > 0) {
        while( $r = $db->Rows() ) {
            $content = $r['document_body'];
        }
    }
    
    header('Content-type: text/json');
    echo '{"result":true,"content":'.json_encode($content).'}';
    return;
}