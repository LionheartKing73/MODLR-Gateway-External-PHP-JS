<?php

$your_client_id = "70b219ed916fb33644c7079b7391031e";
$your_api_key = "9f43c665b7c01b281bbae2391163e906";
$token = "744516d7d967a67f38a49e297c1e0fab9e293e72323273b821a4495417d9179f";

function digital_ocean_ssh_id() {
	$db = new db_helper();
	$db->CommandText("SELECT ssh_id FROM ssh LIMIT 0,1;");
	$db->Execute();
	if( $db->Rows_Affected() > 0 ) {
		$r = $db->Rows();
		
		return $r['ssh_id'];
	}
	return null;
}
function digital_ocean_ssh_private_key() {
	$db = new db_helper();
	$db->CommandText("SELECT ssh_private_key FROM ssh LIMIT 0,1;");
	$db->Execute();
	if( $db->Rows_Affected() > 0 ) {
		$r = $db->Rows();
		
		return $r['ssh_private_key'];
	}
	return null;
}



function digital_ocean_region_name($region_id) {
	$db = new db_helper();
	$db->CommandText("SELECT region_name FROM digitalocean_regions WHERE region_id='%s';");
	$db->Parameters($region_id);
	$db->Execute();
	if( $db->Rows_Affected() > 0 ) {
		$r = $db->Rows();
		
		return $r['region_name'];
	}
	return '';
}


function digital_ocean_memory_name($size_id) {
	$db = new db_helper();
	$db->CommandText("SELECT size_number FROM digitalocean_sizes WHERE size_id='%s';");
	$db->Parameters($size_id);
	$db->Execute();
	if( $db->Rows_Affected() > 0 ) {
		$r = $db->Rows();
		
		return $r['size_number'];
	}
	return 0;
}

function SSHExec($ssh,$line) {
	$task_result = "";
	try {
		$task_result = $ssh->exec($line);
		//scriptLog(-1,-1,-1,$line,$task_result);
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "<br/>";
		//scriptLog(-1,-1,-1,"ERROR",$e->getMessage());
	}
	
	//echo $line . "<br/>";
	if( count($ssh->getErrors()) > 0  )
		print_r($ssh->getErrors());
	return $task_result;
}


function install_server($server_ip,$server_type,$server_username, $server_password) {

	$ssh = new Net_SSH2($server_ip);
	echo "<b>Connecting to '".$server_ip."' type '".$server_type."'</b><br/>";
	
	$ssh->setTimeout(false);
	echo "<b>Timout set to inf</b><br/>";
	flush();
	 
	$bConnected = false;
	if( $server_type == "VPS" ) {
		$key = new Crypt_RSA();
		$key->loadKey(file_get_contents("../cron/id_rsa"));
		if ($ssh->login($server_username, $key)) {
			$bConnected = true;
		}
	} else {
		if ($ssh->login($server_username, $server_password)) {
			$bConnected = true;
		}
	}
	
	
	if( !$bConnected ) {
		echo "<b>Login Failed to '".$server_ip."' type: '".$server_type."'</b><br/>";
	} else {
	
		echo "<b>Login Succeeded to '".$server_ip."' type: '".$server_type."'</b><br/>";
		//write to a text file the ssh script which downloads the game server, 
		//unpacks the game server and copies it across all game server folders
		
		
		$line = 'echo \'mkdir "/daemon/"\' > /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'mkdir "/daemon/data/"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'mkdir "/daemon/logs/"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'mkdir "/daemon/server pack/"\' /daemon/update.sh';
		SSHExec($ssh,$line);
		
		
		$line = 'echo \'killall -9 java\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'killall -9 starbound_server\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		
		$line = 'echo \'rm -f "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "port=8090" >> "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "directoryData=\"/daemon/data/\"" >> "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "directoryLogs=\"/daemon/logs/\"" >> "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "gateway=\"http://www.starbound-hosting.co/api/\"" >> "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "directoryServer=\"/daemon/server pack/\"" >> "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "directoryRelativeToLauncher=\"linux64/\"" >> "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get install -y -qq unzip\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get install -y -qq psmisc\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get install -y -qq libvorbis-dev\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		
		$line = 'echo \'apt-get install sun-java6-jre\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'sudo apt-get update\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'export DEBIAN_FRONTEND=noninteractive\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "deb http://us.ec2.archive.ubuntu.com/ubuntu/ karmic multiverse" | sudo -E tee -a /etc/apt/sources.list\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "deb-src http://us.ec2.archive.ubuntu.com/ubuntu/ karmic multiverse" | sudo -E tee -a /etc/apt/sources.list\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "deb http://us.ec2.archive.ubuntu.com/ubuntu/ karmic-updates multiverse" | sudo -E tee -a /etc/apt/sources.list\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "deb-src http://us.ec2.archive.ubuntu.com/ubuntu/ karmic-updates multiverse" | sudo -E tee -a /etc/apt/sources.list\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get update	-y\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get upgrade -y\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "sun-java6-jdk shared/accepted-sun-dlj-v1-1 boolean true" | sudo -E debconf-set-selections\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get -q -y -qq --force-yes install software-properties-common\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get -q -y -qq --force-yes install default-jdk\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'apt-get -q -y -qq --force-yes install screen\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo "export JAVA_HOME=/usr/lib/jvm/java-6-sun" | sudo -E tee -a ~/.bashrc\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'echo net.ipv6.conf.all.disable_ipv6=1 > /etc/sysctl.d/disableipv6.conf\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'killall -9 java\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'rm -f "/daemon/daemon.zip"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'wget -O"/daemon/daemon.zip" "http://www.starbound-hosting.co/files/daemon_v1.zip"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'unzip -o "/daemon/daemon.zip" -d "/daemon/"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'chmod -R 777 "/daemon/server pack/"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		
		
		$line = 'echo "echo \'updating the server now\'" >> /daemon/update.sh';
		SSHExec($ssh,$line);
	
		$line = 'echo "rm -f \"/daemon/server pack.zip\"" >> /daemon/update.sh';
		SSHExec($ssh,$line);
	
		$line = "echo \"wget -O'/daemon/server pack.zip' 'http://www.starbound-hosting.co/files/starbound_v1.zip'\" >> /daemon/update.sh";
		SSHExec($ssh,$line);
	
		$line = "echo \"unzip -o '/daemon/server pack.zip' -d '/daemon/server pack/'\" >> /daemon/update.sh";
		SSHExec($ssh,$line);
		
		
		$line = 'echo \'killall -9 java\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'killall -9 starbound_server\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		
		
		$line = 'echo \'iptables -F\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -X\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -t nat -F\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -t nat -X\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -t mangle -F\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -t mangle -X\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -P INPUT ACCEPT\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -P FORWARD ACCEPT\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -P OUTPUT ACCEPT\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		$line = 'echo \'iptables -save\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		
		$line = 'echo \'java -Xms16M -Xmx16M -jar "/daemon/daemon.jar" "/daemon/config.cfg"\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		
		$line = "chmod 777 /daemon/update.sh";
		SSHExec($ssh,$line);
		
		$line = "/usr/bin/screen -d -m /daemon/update.sh";
		SSHExec($ssh,$line);
		
	}
}


function backup_universe($server_ip,$server_type,$server_username,$server_password,$service_id,$app_code ,$backup_description) {
	
	$ssh = new Net_SSH2($server_ip);
	echo "<b>Connecting to '".$server_ip."' type '".$server_type."'</b><br/>";
	
	$ssh->setTimeout(false);
	echo "<b>Timout set to inf</b><br/>";
	flush();
	
	$bConnected = false;
	if( $server_type == "VPS" ) {
		$key = new Crypt_RSA();
		$key->loadKey(file_get_contents("../cron/id_rsa"));
		if ($ssh->login($server_username, $key)) {
			$bConnected = true;
		}
	} else {
		if ($ssh->login($server_username, $server_password)) {
			$bConnected = true;
		}
	}
	
	$final_filename = date('Y_m_d_H_i') . '_universe.zip';
	$url = "/backup/9d090f04acc10035aa8d3076f0cf17d4/" . $final_filename ;
	
	
	$db = new db_helper();
	$db->CommandText("INSERT INTO services_backup (service_id,backup_description,backup_url) VALUES ('%s','%s','%s');");
	$db->Parameters($service_id);
	$db->Parameters($backup_description);
	$db->Parameters($url);
	$db->Execute();
	
	$line = "apt-get install -y -qq zip";
	SSHExec($ssh,$line);
	
	$line = "cd /daemon/data/servers/" . $app_code . "/linux64/universe/";
	SSHExec($ssh,$line);
	
	$line = 'zip -r -j /daemon/data/backups/' . $app_code . '.zip /daemon/data/servers/' . $app_code . '/linux64/universe/';
	SSHExec($ssh,$line);
	
	//$line = 'zip -r /daemon/data/backups/' . $app_code . '.zip /daemon/data/servers/' . $app_code . '/linux64/universe';
	//SSHExec($ssh,$line);
	
	//zip -r /daemon/data/backups/9d090f04acc10035aa8d3076f0cf17d4.zip /daemon/data/servers/9d090f04acc10035aa8d3076f0cf17d4/linux64/universe/

	$line = 'cd /daemon/data/backups/';
	SSHExec($ssh,$line);

	$line = 'echo "cd /daemon/data/backups/" > /daemon/backup.sh';
	SSHExec($ssh,$line);

	$line = 'echo "ftp -n www.starbound-hosting.co <<END_SCRIPT" > /daemon/backup.sh';
	SSHExec($ssh,$line);

	$line = 'echo "quote USER starbound-hostin" >> /daemon/backup.sh';
	SSHExec($ssh,$line);

	$line = 'echo "quote PASS Rock11Rock11" >> /daemon/backup.sh';
	SSHExec($ssh,$line);



	$line = 'echo "mkdir /backup/' . $app_code . '" >> /daemon/backup.sh';
	SSHExec($ssh,$line);


	$line = 'echo "cd /backup/' . $app_code . '/" >> /daemon/backup.sh';
	SSHExec($ssh,$line);



	$line = 'echo "binary" >> /daemon/backup.sh';
	SSHExec($ssh,$line);

	$line = 'echo "put /daemon/data/backups/' . $app_code . '.zip '.$final_filename.'" >> /daemon/backup.sh';
	SSHExec($ssh,$line);


	$line = 'echo "quit" >> /daemon/backup.sh';
	SSHExec($ssh,$line);

	$line = 'echo "END_SCRIPT" >> /daemon/backup.sh';
	SSHExec($ssh,$line);

	$line = 'echo "exit 0" >> /daemon/backup.sh';
	SSHExec($ssh,$line);


	$line = "chmod 777 /daemon/backup.sh";
	SSHExec($ssh,$line);
	$line = "chmod -R 777 /daemon/data/backups/";
	SSHExec($ssh,$line);
		
	$line = "/daemon/backup.sh";
	SSHExec($ssh,$line);
	
	
	
	
}

function upgrade_server($server_ip,$server_type,$server_username, $server_password) {

	$ssh = new Net_SSH2($server_ip);
	echo "<b>Connecting to '".$server_ip."' type '".$server_type."'</b><br/>";
	
	$ssh->setTimeout(false);
	echo "<b>Timout set to inf</b><br/>";
	flush();
	
	$bConnected = false;
	if( $server_type == "VPS" ) {
		$key = new Crypt_RSA();
		$key->loadKey(file_get_contents("../cron/id_rsa"));
		if ($ssh->login($server_username, $key)) {
			$bConnected = true;
		}
	} else {
		if ($ssh->login($server_username, $server_password)) {
			$bConnected = true;
		}
	}
	
	$json = '{"tasks": [{"task": "servers.list"}]}';
	$results = api_short($server_ip . ":8090", SERVICE_SERVER, $json);
	
	if( $results == null ) {
		echo "Daemon is not running, server is possibly being setup. Skipping server:".$server_ip." <br/>";
		return "";
	}
	
	if( !$bConnected ) {
		echo "<b>Login Failed to '".$server_ip."' type: '".$server_type."'</b><br/>";
	} else {
	
		echo "<b>Login Succeeded to '".$server_ip."' type: '".$server_type."'</b><br/>";
		//write to a text file the ssh script which downloads the game server, 
		//unpacks the game server and copies it across all game server folders
		
			
		$line = 'echo "echo \'updating the server now\'" > /daemon/update.sh';
		SSHExec($ssh,$line);
	
		$line = 'echo "rm -f \"/daemon/server pack.zip\"" >> /daemon/update.sh';
		SSHExec($ssh,$line);
	
		$line = "echo \"wget -O'/daemon/server pack.zip' 'http://www.starbound-hosting.co/files/starbound_v1.zip'\" >> /daemon/update.sh";
		SSHExec($ssh,$line);
	
		$line = "echo \"unzip -o '/daemon/server pack.zip' -d '/daemon/server pack/'\" >> /daemon/update.sh";
		SSHExec($ssh,$line);
		
		$line = 'echo \'killall -9 java\' >> /daemon/update.sh';
		SSHExec($ssh,$line);
		
		
		$list = $results->results[0]->list;
		for($i=0;$i<count($list);$i++) {
			$app_code = $list[$i]->id;
			$line = 'echo "ls -1 | grep -v \"^default_configuration.config$\" | xargs -n 1 -\"/daemon/server pack/\" cp -R \"/daemon/data/servers/'.$app_code.'/\"" >> /daemon/update.sh';
			SSHExec($ssh,$line);
		}
	
		
		$line = "chmod 777 /daemon/update.sh";
		SSHExec($ssh,$line);
		
		$line = "/usr/bin/screen -d -m /daemon/update.sh";
		SSHExec($ssh,$line);
		
	}
}

function digital_ocean_get_ip($droplet_id) {
	
	$url_path = "/v2/droplets/".$droplet_id;
	return digital_ocean_hosting_api($url_path,"")->droplet->networks->v4[0]->ip_address;
}


function digital_ocean_reboot($droplet_id) {
	$url_path = "/v2/droplets/".$droplet_id."/actions";
	return digital_ocean_hosting_api($url_path,'{"type":"reboot"}');
}

function digital_ocean_provision_server($droplet_name, $size_id, $region_id) {

	//always use debian 7.
	//image_name Debian 7.0 x64
	$image_id = 10322059;
	$images = digital_ocean_return_all_images()->images;
	for($i=0;$i<count($images);$i++) {
		$image = $images[$i];
		if( $image->slug == "debian-7-0-x64" ) {
			$image_id = $image->id;
		}
	}
	
	$request = '{"name":"'.$droplet_name.'","region":"'.$region_id.'","size":"'.$size_id.'","image":"debian-7-0-x64","ssh_keys":["'.digital_ocean_ssh_id().'"],"backups":false,"ipv6":false,"user_data":null,"private_networking":null}';
	
	$url_path = "/v2/droplets";
	return digital_ocean_hosting_api($url_path,$request);
}


function digital_ocean_destroy_server($droplet_id) {
	$url_path = "/v2/droplets/".$droplet_id."";
	return digital_ocean_hosting_api($url_path,"");
}

function digital_ocean_return_all_images() {
	$url_path = "/v2/images";
	return digital_ocean_hosting_api($url_path,"");
}

function digital_ocean_return_all_keys() {
	$url_path = "/v2/account/keys";
	return digital_ocean_hosting_api($url_path,"");
}

function digital_ocean_return_all_regions() {
	$url_path = "/v2/regions";
	return digital_ocean_hosting_api($url_path,"");
}

function digital_ocean_return_all_sizes() {
	$url_path = "/v2/sizes";
	return digital_ocean_hosting_api($url_path,"");
}


function digital_ocean_return_all_droplets() {
	$url_path = "/v2/droplets";
	return digital_ocean_hosting_api($url_path,"");
}

function digital_ocean_hosting_api($url_service, $url_query) {
	global $your_client_id, $your_api_key, $token;
	//$url_service = "/droplets"
	$url = "https://api.digitalocean.com".$url_service."/";
	//echo "<!-- ".$url."-->";
	
	
	
	// Setup cURL
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, true);

	curl_setopt_array($ch, array(
		CURLOPT_POST => FALSE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer ' .$token
		)
	));	
	
	curl_setopt($ch,CURLOPT_TIMEOUT,90);
	
	//$url_query
	if( $url_query != "" ) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS,$url_query);
	}
	
	// Send the request
	$response = curl_exec($ch);

	// Check for errors
	if($response === FALSE){
		//todo: die gracefully'
		echo "<!-- CURL ERROR: ".$url." -->\r\n";
		
		return null;
	}

	//echo "<!-- ".$response." -->\r\n";
	$responseSplit = explode("\r\n\r\n",$response);

	//session management
	$headers = explode("\r\n",$responseSplit[0]);
	for($i=0;$i<count($headers);$i++) {
		$header = explode(":", $headers[$i]);
		if( trim(strtolower($header[0])) == "set-cookie") {
			$_SESSION['user_cookie'] = $header[1];
		}
	}

	for($i=0;$i<count($responseSplit);$i++) {
		if( substr($responseSplit[$i],0,1) == "{" ) {
			return json_decode($responseSplit[$i]);
		}
	}
	
	
	return null;
	
}

function server_run_script($server_id,$script_id) {
	
	if( $server_id != "" && $script_id != "" ) {
	
		echo "<!-- Attempting to execute script -->\r\n";
	
		$db = new db_helper();
		$db->CommandText("SELECT server_ip,server_type,server_id,server_username,server_password,server_hostname FROM servers WHERE server_id='%s';");
		$db->Parameters($server_id);
		$db->Execute();
		if( $db->Rows_Affected() > 0 ) {
			$r = $db->Rows();
		
			$server_ip = $r['server_ip'];
			$server_username = $r['server_username'];
			$server_password = $r['server_password'];
			$server_hostname = $r['server_hostname'];
			$server_type = $r['server_type'];
		
			echo "<b>Found the selected server '".$server_hostname."' ('".$server_ip."').</b><br/>";
		
			$db = new db_helper();
			$db->CommandText("SELECT script_lines,script_name FROM scripts WHERE script_id='%s';");
			$db->Parameters($script_id);
			$db->Execute();
			if( $db->Rows_Affected() > 0 ) {
				$r = $db->Rows();
		
				$script_lines = $r['script_lines'];
				$script_name = $r['script_name'];
			
				echo "<b>Found the selected script '".$script_name."'</b><br/>";
		
			
				echo "<b>Executing '".$script_name."'</b><br/>";
		
				$ssh = new Net_SSH2($server_ip);
				echo "<b>Connecting to '".$server_ip."' type '".$server_type."'</b><br/>";

				$ssh->setTimeout(false);
				echo "<b>Timout set to inf</b><br/>";
				flush();

				$bConnected = false;
				if( $server_type == "VPS" ) {
					$key = new Crypt_RSA();
					$key->loadKey(file_get_contents("../cron/id_rsa"));
					if ($ssh->login($server_username, $key)) {
						$bConnected = true;
					}
				} else {
					if ($ssh->login($server_username, $server_password)) {
						$bConnected = true;
					}
				}
		
				if (  $bConnected == false) {
					echo "<b>Login Failed to '".$server_ip."' as '".$server_username."'</b><br/>";
				
					scriptLog($server_id,$script_id,-1,"LOGIN","LOGIN FAILED.");
				
				} else {
					echo "<b>Login Succeeded to '".$server_ip."' as '".$server_username."'</b><br/>";
				
					scriptLog($server_id,$script_id,-1,"LOGIN","LOGIN SUCCEEDED.");
				
					flush();
				
					$lines = explode( "\n" , $script_lines );
					for($i=0;$i<sizeof($lines) ; $i++) {
						try {
							$line = $lines[$i];
							$line = str_replace("\r","",$line);
						
							$task_result = $ssh->exec($line);
							echo $task_result ."<br/>";
						
							scriptLog($server_id,$script_id,$i,$line,$task_result);
						} catch (Exception $e) {
							echo 'Caught exception: ',  $e->getMessage(), "<br/>";
						
							scriptLog($server_id,$script_id,$i,$line,"ERROR:" .$e->getMessage());
						}
						flush();
					}
				
					scriptLog($server_id,$script_id,-1,"FINISHED","SCRIPT FINISHED.");
				
				}
			}
	
		}
	}
}


function server_run_script_code($ssh,$script_id) {
	
	$db = new db_helper();
	$db->CommandText("SELECT script_lines,script_name FROM scripts WHERE script_id='%s';");
	$db->Parameters($script_id);
	$db->Execute();
	if( $db->Rows_Affected() > 0 ) {
		$r = $db->Rows();

		$script_lines = $r['script_lines'];
		$script_name = $r['script_name'];
	
		echo "<b>Found the selected script '".$script_name."'</b><br/>";
		echo "<b>Executing '".$script_name."'</b><br/>";
	
		$lines = explode( "\n" , $script_lines );
		for($i=0;$i<sizeof($lines) ; $i++) {
			try {
				$line = $lines[$i];
				$line = str_replace("\r","",$line);
			
				$task_result = $ssh->exec($line);
				echo $task_result ."<br/>";
			
				scriptLog($server_id,$script_id,$i,$line,$task_result);
			} catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "<br/>";
			
				scriptLog($server_id,$script_id,$i,$line,"ERROR:" .$e->getMessage());
			}
			flush();
		}
	
		scriptLog($server_id,$script_id,-1,"FINISHED","SCRIPT FINISHED.");
	
	
	}
	
}


function scriptLog($server_id,$script_id,$task_line,$task_string,$task_result) {
	//scripts_logs
	
	$db = new db_helper();
	$db->CommandText("INSERT INTO scripts_logs (server_id,script_id,task_line,task_string,task_result) VALUES ('%s','%s','%s','%s','%s');");
	$db->Parameters($server_id);
	$db->Parameters($script_id);
	$db->Parameters($task_line);
	$db->Parameters($task_string);
	$db->Parameters($task_result);
	$db->Execute();
	
}



?>
