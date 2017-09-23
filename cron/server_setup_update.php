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
$db->CommandText("SELECT droplet_id, client_id,server_memory FROM modlr.servers WHERE server_ip IS NULL AND server_type = 'DROPLET';");
$db->Execute();
while( $r = $db->Rows() ) {
	$droplet_id = $r['droplet_id'];
	$client_id = $r['client_id'];
	$server_memory = $r['server_memory'];
	
	
	$droplet_ip = digital_ocean_get_ip($droplet_id);
	
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
			
			
			$pass = randomPassword(24);
			$pass = str_replace("\"","",$pass);
			
			$db2 = new db_helper();
			$db2->CommandText("SELECT hash FROM clients WHERE client_id='%s';");
			$db2->Parameters($client_id);
			$db2->Execute();
			$r2 = $db2->Rows();
			$hash = $r2['hash'];
			
			echo "<b>Connected.</b><br/>";
			
			
			$db2 = new db_helper();
			$db2->CommandText("UPDATE modlr.servers SET server_ip='%s',server_port='8090',server_datastore_password='%s' WHERE droplet_id='%s';");
			$db2->Parameters($droplet_ip);
			$db2->Parameters($pass);
			$db2->Parameters($droplet_id);
			$db2->Execute();
			
			$line = 'mkdir "/daemon/"';
			SSHExec($ssh,$line);
			
			//create the MODLR config file
			$line = "rm -f '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			$line = "echo 'port=8090' >> \"/daemon/config.cfg\"";
			SSHExec($ssh,$line);
			
			$line = "echo 'directoryData=\"/daemon/data/\"' >> '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			$line = "echo 'directoryLogs=\"/daemon/logs/\"' >> '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			$line = "echo 'directoryWeb=\"/daemon/www/\"' >> '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			$line = "echo 'gateway=\"https://go.modlr.co/auth/\"' >> '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			
			$line = "echo 'backupservice=\"http://223.252.24.131/upload\"' >> '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			$line = "echo 'internalDatastorePassword=\"".$pass."\"' >> '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			
			$line = "echo 'client_id=".$hash."' >> '/daemon/config.cfg'";
			SSHExec($ssh,$line);
			
			//download the setup script and run script
			$line = "wget -O'/root/setup.sh' 'http://go.modlr.co/files/setup.sh'";
			SSHExec($ssh,$line);
			
			$line = 'chmod -R 777 "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = "wget -O'/root/modlr.sh' 'http://go.modlr.co/files/modlr".$server_memory."GB.sh'";
			SSHExec($ssh,$line);
			
			$line = 'chmod -R 777 "/root/modlr.sh"';
			SSHExec($ssh,$line);
			
			
			//modify the setup.sh for the specific password used.
			
			$line = 'echo "mysql -u root mysql -e \'CREATE SCHEMA datastore;\'" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = 'echo "mysql -u root mysql -e \'UPDATE user SET Password=PASSWORD(\\"'.$pass.'\\") where USER=\\"root\\";\'" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = 'echo "mysql -u root mysql -e \'UPDATE user SET Password=PASSWORD(\\"'.$pass.'\\") where USER=\\"anonymous\\";\'" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = 'echo "mysqladmin -u root password \''.$pass.'\'" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = 'echo "mysqladmin -u anonymous password \''.$pass.'\'" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = 'echo "mysql -u root mysql -e \'FLUSH PRIVILEGES;\'" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = 'echo "sudo service mysql restart" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			$line = 'echo "mysql -u root -p"'.$pass.'" mysql -e \\"GRANT ALL ON *.* TO \'root\'@\'%\' IDENTIFIED BY \''.$pass.'\' WITH GRANT OPTION;\\"" >> "/root/setup.sh"';
			SSHExec($ssh,$line);
			
			
			//execute the setup.sh
			$line = "/root/setup.sh";
			SSHExec($ssh,$line);
			
			$line = "keytool -genkey -keyalg RSA -alias selfsigned -keystore /daemon/modlr.jks -keypass modlrpassword -storepass modlrpassword -validity 7300 -keysize 2048 -dname \"CN=".$droplet_ip.", OU=MODLR.co, O=MODLR, L=Sydney, ST=NSW, C=AU\"";
			SSHExec($ssh,$line);
			
			$line = "/root/modlr.sh";
			SSHExec($ssh,$line);
			
			
			
			if( count($ssh->getErrors()) > 0 ) {
				echo $error;
				
				$db2 = new db_helper();
				$db2->CommandText("UPDATE modlr.servers SET server_ip=NULL WHERE droplet_id='%s';");
				$db2->Parameters($droplet_id);
				$db2->Execute();
			}
			
		} else {
			echo "<b>Failed to Connect.</b><br/>";
			echo $ssh->getLog();
			print_r($ssh->getErrors());
		}
	
		//print_r($ssh->getLog());
	}
	//
	
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