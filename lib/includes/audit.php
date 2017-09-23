<?php

function addLogEntry($logTable, $logCommand, $affectedId, $user_id = "", $page = "") {    
    if ($page == "") {
        $page = $_SERVER['PHP_SELF'];
    }    
    $logCommand = "(".$logCommand.")";
    
    // Descriptions
    $logDescription = "";    
    if (strpos($logCommand, "banned = 0")) {
        $logDescription .= "Unbanned User > ";
    }
    if (strpos($logCommand, "isAdmin = 0")) {
        $logDescription .= "Unset Administrator Rights > ";
    }
    if (strpos($logCommand, "banned = 1")) {
        $logDescription .= "Banned User > ";
    }
    if (strpos($logCommand, "isAdmin = 1")) {
        $logDescription .= "Set Administrator Rights > ";
    }
    if (strpos($logCommand, "password = ")) {
        $logDescription .= "Changed Password > ";
    }                
    if (strpos($logCommand, "DELETE FROM ".$logTable) > -1) {
        $logDescription = "Delete: ";
    }        
    if (strpos($logCommand, "INSERT INTO ".$logTable) > -1) {
        $logDescription = "Add: ";
    }
    if (strpos($logCommand, "UPDATE")) {
        $logDescription .= "Changed Values: ";
    }
    // dont audit views
    if (strpos($logCommand, "SELECT")) {
        $logDescription = "Viewed: ";
    }
    else {
        $logDescription .= $logTable.".id ".$affectedId;
    
        // dont audit when on test environment        
        if ($_SERVER['HTTP_HOST'] != "test.monsterranch.com") {
            $sql_exec = "INSERT INTO audit_log (userid, actionPage, actionTable, actionCommand, actionDescription) VALUES (%s, '%s', '%s', 'EXEC: %s', '%s')";        
            $db = new db_helper();
            $db->CommandText($sql_exec);
            $db->Parameters(($user_id == "" ? session("uid") : $user_id));
            $db->Parameters($page);
            $db->Parameters($logTable);
            $db->Parameters($logCommand);
            $db->Parameters($logDescription);
            $db->Execute();            
        }    
    }    
}
    
?>
