<?

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



//###### CHECK FOR UNPROVISIONED SERVERS
$server_count = 0;
$db->CommandText("SELECT droplet_id, server_ip, client_id,server_memory FROM modlr.servers WHERE NOT server_ip IS NULL AND server_type = 'DROPLET' AND server_is_deleted=0;");
$db->Execute();
while( $r = $db->Rows() ) {
	$droplet_id = $r['droplet_id'];
	$client_id = $r['client_id'];
	$server_memory = $r['server_memory'];
	$server_ip = $r['server_ip'];
	
	$droplet_ip = $server_ip;
	
	echo "Server IP: ".$droplet_ip."<br/>";
	if( $droplet_ip ) {
	
		$ssh = new Net_SSH2($droplet_ip);
		echo "<b>Connecting to '".$droplet_ip."'</b><br/>";
	
		$ssh->setTimeout(false);
		echo "<b>Timout set to inf</b><br/>";
		
		
		$bConnected = false;
		$key = new Crypt_RSA();
		$key->loadKey(file_get_contents("id_rsa"));
		if ($ssh->login("root", $key)) {
			$bConnected = true;
		}
		
		if( $bConnected ) {
			
			
			$line = "keytool -delete -noprompt -alias selfsigned -keystore /daemon/modlr.jks -storepass modlrpassword";
			SSHExec($ssh,$line);
			
			$ip_str = str_replace(".","_",$droplet_ip);
			
			$line = "keytool -genkey -keyalg RSA -alias selfsigned -keystore /daemon/modlr.jks -keypass modlrpassword -storepass modlrpassword -validity 7300 -keysize 2048 -dname \"CN=".$droplet_ip.", OU=".$ip_str.".MODLR.co, O=MODLR, L=Sydney, ST=NSW, C=AU\"";
			SSHExec($ssh,$line);

			
			if( count($ssh->getErrors()) > 0 ) {
				echo $error;
			}
			
		} else {
			echo "<b>Failed to Connect.</b><br/>"; 
			echo $ssh->getLog();
			print_r($ssh->getErrors());
		}
	}
	
	$server_count++;	
}



$status = "Finished. Updated Installation for ".$server_count." Servers.";

//####################### CRON LOG - FINISHED
$db->CommandText("UPDATE cron_logs SET date_finished=CURRENT_TIMESTAMP, status='%s' WHERE cron_exec_id = '%s';");
$db->Parameters($status);
$db->Parameters($cron_id);
$db->Execute();
//####################### CRON LOG - FINISHED


?>