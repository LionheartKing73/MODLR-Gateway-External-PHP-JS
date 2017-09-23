<?php
 include_once("../lib/lib.php");
 
if(session("client_id") == 1) {
    if(isset($_POST['updatedContent'])) {
       $content = $_POST['updatedContent'];
       $id = $_POST['id'];
       $db = new db_helper();
       $db->CommandText("UPDATE documents SET document_body = '%s' WHERE document_id = '%s';");
       $db->Parameters($content);
       $db->Parameters($id);
       $db->Execute();
       header('Content-type: text/json');
       echo '{"result":true,"content":'.json_encode($content).'}';
       return;
   }

   if(isset($_POST['parent_id'])) {
       $parent = $_POST['parent_id'];
       $name = $_POST['entry_name'];
       $content = $_POST['data'];
       $db = new db_helper();
       $db->CommandText("SELECT document_category FROM documents WHERE document_id = '%s';");
       $db->Parameters($parent);
       $db->Execute();
       $group = null;
       if ($db->Rows_Count() > 0) {
           while( $r = $db->Rows() ) {
               $group = $r['document_category'];
           }
       }

       $db = new db_helper();
       $db->CommandText("INSERT INTO documents (document_name, document_parent, document_category, document_body) VALUES('%s', '%s', '%s', '%s');");
       $db->Parameters($name);
       $db->Parameters($parent);
       $db->Parameters($group);
       $db->Parameters($content);
       $db->Execute();
       header('Content-type: text/json');
       echo '{"result":true}';
       return;
   }
}