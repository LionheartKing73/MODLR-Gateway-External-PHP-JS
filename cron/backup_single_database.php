<?
ini_set('max_execution_time', 30000);

include_once("../lib/config.php");
include_once("../lib/dbfunctions.php");
include_once("../lib/miscfunctions.php");
include_once("../lib/server_functions.php");
include_once("../lib/includes/exception.php");
include_once("../lib/includes/audit.php");
include_once("../lib/includes/email.php");
include_once("../lib/includes/notice.php");
include_once("../lib/includes/validation.php");

include_once("../lib/class/user.php");
include_once("../lib/class/db.php");

DBopen();
ini_set('display_errors', 'On');
error_reporting(E_ALL);

include_once('../lib/phpseclib0.3.5/Math/BigInteger.php');
include_once('../lib/phpseclib0.3.5/Crypt/RSA.php');
include_once('../lib/phpseclib0.3.5/Crypt/AES.php');
include_once('../lib/phpseclib0.3.5/Crypt/RC4.php');
include_once('../lib/phpseclib0.3.5/Net/SSH2.php');

//define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);
//define('NET_SSH2_LOGGING', NET_SSH2_LOG_SIMPLE);

echo "<b>Application Server Initiaited:</b>";

//####################### CRON LOG - STARTING
$db = new db_helper();
$db->CommandText("INSERT INTO cron_logs (page_name, status) VALUES ('%s','%s');");
$db->Parameters($_SERVER['PHP_SELF']);
$db->Parameters("Started.");
$db->Execute();
$cron_id = $db->Last_Insert_ID();
//####################### CRON LOG - STARTING

echo "<br/>";

$server_id = querystring("server_id");
if( $server_id == "" ) {
	echo "No Server Specified";
	die();
}

//###### CHECK FOR UNPROVISIONED SERVERS
$server_count = 0;
$db->CommandText("SELECT droplet_id, server_ip,server_codename, client_id,server_memory,server_password,server_user,server_type FROM modlr.servers WHERE server_id = '%s' AND NOT server_ip IS NULL AND server_is_deleted=0 AND exclude_from_backups=0;");
$db->Parameters($server_id);
$db->Execute();
while( $r = $db->Rows() ) {
	$droplet_id = $r['droplet_id'];
	$client_id = $r['client_id'];
	$server_memory = $r['server_memory'];
	$server_ip = $r['server_ip'];
	
	$server_user = $r['server_user'];
	$server_password = $r['server_password'];
	
	$server_type = $r['server_type'];
	
	$droplet_ip = $server_ip;
	
	$server_codename = $r['server_codename'];
	echo "Server: ".$server_codename." IP: ".$droplet_ip." &nbsp;";
	if( $droplet_ip ) {
	
		$ssh = new Net_SSH2($droplet_ip);
		//echo "<b>Connecting to '".$droplet_ip." (".$server_codename." - ".$server_ip.")'</b>&nbsp;";
	
		$ssh->setTimeout(false);
		
		
		
		$bConnected = false;
		
		if( $server_type == "DROPLET" ) {
			$key = new Crypt_RSA();
			$key->loadKey(file_get_contents("id_rsa"));
			if ($ssh->login("root", $key)) {
				$bConnected = true;
			}
		} else {
			if( !is_null($server_user) ) {
				if ($ssh->login($server_user, $server_password)) {
					$bConnected = true;
				}
			}
		}
		
		if( $bConnected ) {
		
			$line = "mysqldump -u root -p'aui1zraj!hrtAU' datastore > /daemon/logs/datastore_$(date +%Y%m%d_%H%M%S).sql";
			SSHExec($ssh,$line);
			
			if( count($ssh->getErrors()) > 0 ) {
				echo $error;
			}
			
			echo " - Backed up database."; 
		} else {
			echo " - Failed to Connect."; 
			echo $ssh->getLog();
			print_r($ssh->getErrors());
		}
		
		$server_count++;	
		echo "<br/><br/>";
	}
	
}



$status = "Finished. Updated Installation for ".$server_count." Servers.";

//####################### CRON LOG - FINISHED
$db->CommandText("UPDATE cron_logs SET date_finished=CURRENT_TIMESTAMP, status='%s' WHERE cron_exec_id = '%s';");
$db->Parameters($status);
$db->Parameters($cron_id);
$db->Execute();
//####################### CRON LOG - FINISHED


?>