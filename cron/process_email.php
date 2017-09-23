<?
ini_set('max_execution_time', 30000);

include_once("../lib/config.php");
include_once("../lib/dbfunctions.php");
include_once("../lib/miscfunctions.php");
include_once("../lib/server_functions.php");
include_once("../lib/includes/exception.php");
include_once("../lib/includes/audit.php");
include_once("../lib/includes/email.php");
include_once("../lib/includes/sms.php");
include_once("../lib/includes/notice.php");
include_once("../lib/includes/validation.php");

include_once("../lib/class/user.php");
include_once("../lib/class/db.php");

DBopen();
ini_set('display_errors', 'On');
error_reporting(E_ALL);

echo "<b>Email Process:</b><br/>";


//####################### CRON LOG - STARTING
$db = new db_helper();
$db->CommandText("INSERT INTO cron_logs (page_name, status) VALUES ('%s','%s');");
$db->Parameters($_SERVER['PHP_SELF']);
$db->Parameters("Started.");
$db->Execute();
$cron_id = $db->Last_Insert_ID();
//####################### CRON LOG - STARTING

function closeEmail($email_id) {
	
	$db = new db_helper();
	$db->CommandText("UPDATE email_queue SET email_sent=CURRENT_TIMESTAMP WHERE email_id='%s';");
	$db->Parameters($email_id);
	$db->Execute();
	
}
function closeSMS($sms_id) {
	
	$db = new db_helper();
	$db->CommandText("UPDATE sms_queue SET sms_sent=CURRENT_TIMESTAMP WHERE sms_id='%s';");
	$db->Parameters($sms_id);
	$db->Execute();
	
}

//###### Execute the cron content.
$server_count = 0;
$db->CommandText("SELECT email_id,email_from,email_to,email_subject,email_html FROM modlr.email_queue WHERE email_sent IS NULL ;");
$db->Execute();
while( $r = $db->Rows() ) {
	$email_from = $r['email_from'];
	$email_to = $r['email_to'];
	$email_subject = $r['email_subject'];
	$email_html = $r['email_html'];
	$email_id = $r['email_id'];
	
	$res = sendMail($email_to,$email_from,$email_subject,$email_html);
	closeEmail($email_id);
	
	$server_count++;	
}



//###### Execute the cron content.
$sms_count = 0;
$db->CommandText("SELECT sms_id,sms_to,sms_body FROM modlr.sms_queue WHERE sms_sent IS NULL ;");
$db->Execute();
while( $r = $db->Rows() ) {
	$sms_id = $r['sms_id'];
	$sms_to = $r['sms_to'];
	$sms_body = $r['sms_body'];
	
	$res = send_sms( $sms_to, $sms_body );
	closeSMS($sms_id);
	
	echo "<br/>";
	echo "<br/>";
	print_r($res);
	echo "<br/>";
	echo "<br/>";
	
	$sms_count++;	
}


$status = "Finished. Processed ".$server_count." Emails and ".$sms_count." SMS.";

echo $status;

//####################### CRON LOG - FINISHED
$db->CommandText("UPDATE cron_logs SET date_finished=CURRENT_TIMESTAMP, status='%s' WHERE cron_exec_id = '%s';");
$db->Parameters($status);
$db->Parameters($cron_id);
$db->Execute();
//####################### CRON LOG - FINISHED


?>