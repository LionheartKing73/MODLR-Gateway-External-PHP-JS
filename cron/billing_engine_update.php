<?

include_once("../lib/config.php");
include_once("../lib/dbfunctions.php");
include_once("../lib/miscfunctions.php");
include_once("../lib/includes/exception.php");
include_once("../lib/includes/audit.php");
include_once("../lib/includes/email.php");
include_once("../lib/includes/notice.php");
include_once("../lib/includes/validation.php");

include_once("../lib/class/user.php");
include_once("../lib/class/db.php");

DBopen();

echo "<b>Billing Process Initiaited:</b>";

$force = querystring("force");

//####################### CRON LOG - STARTING
$db = new db_helper();
$db->CommandText("INSERT INTO cron_logs (page_name, status) VALUES ('%s','%s');");
$db->Parameters($_SERVER['PHP_SELF']);
$db->Parameters("Started.");
$db->Execute();
$cron_id = $db->Last_Insert_ID();
//####################### CRON LOG - STARTING


$client_ids = array();

//###### UPDATE CLIENT UTILISATION (ROUGHLY EVERY 4 HOURS)
$usilisation_count = 0;
$db->CommandText("SELECT client_id FROM modlr.clients WHERE autobill=1;");
$db->Execute();
while( $r = $db->Rows() ) {
	$client_id = $r['client_id'];
	updateUtilisation($client_id);
	$usilisation_count++;
	
	array_push($client_ids,$client_id);
}

//createInvoice(1, date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y"))));

//###### GENERATE CLIENT INVOICES
//invoicing on the first day of the month.
/*
$timestamp = time();
if(date('j', $timestamp) === '1' || $force != "")  {
	
	$billing_period = date("Y-m-d", mktime(0, 0, 0, date("m")-1, 1, date("Y")));
	
	//units_modeller
	for($i=0;$i<count($client_ids);$i++) {
		$client_id = $client_ids[$i];
		
		createInvoice($client_id, $billing_period);
	}
}
*/
$status = "Finished. Updated Utilisation for ".$usilisation_count." Clients.";

//####################### CRON LOG - FINISHED
$db->CommandText("UPDATE cron_logs SET date_finished=CURRENT_TIMESTAMP, status='%s' WHERE cron_exec_id = '%s';");
$db->Parameters($status);
$db->Parameters($cron_id);
$db->Execute();
//####################### CRON LOG - FINISHED


?>