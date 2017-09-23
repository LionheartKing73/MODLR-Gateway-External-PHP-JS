<?php

ini_set('memory_limit', '-1');

function outputModelToolbar($id, $name) {
	global $results;
?>
				<div class="row" style="height: 60px;">
					<!--navigation start-->
					<nav class="navbar navbar-inverse" role="navigation" style='border-radius: 0px;top:-15px;position:relative;'>
						<!-- Brand and toggle get grouped for better mobile display -->
						<div class="navbar-header">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<a class="navbar-brand" href="/model/?id=<? echo $id;?>"><? echo $name;?></a>
						</div>

						<!-- Collect the nav links, forms, and other content for toggling -->
						<div class="collapse navbar-collapse navbar-ex1-collapse">
							<ul class="nav navbar-nav">
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Other Models: <b class="caret"></b></a>
									<ul class="dropdown-menu">
<?
	
		$contents = $results->results[0]->models;
		for($i=0;$i<count($contents);$i++) {
			$model = $contents[$i];
			echo '<li><a href="/model/?id='.$model->id.'">'.$model->name.'</a></li>';
		}
?>
									</ul>
								</li>
							</ul>
					
							<ul class="nav navbar-nav navbar-right">
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
									<ul class="dropdown-menu">
										<li><a href="/workview/create/?id=<? echo $id;?>">New Workview (Report)</a></li>
										<li><a href="/cube/create/?id=<? echo $id;?>">New Cube</a></li>
										<li><a href="/dimension/create/?id=<? echo $id;?>">New Dimension</a></li>
										<li><a href="/table/?id=<? echo $id;?>">New Table</a></li>
										<li><a href="/process/create/?id=<? echo $id;?>">New Data Import (Process)</a></li>
										<li><a href="javascript:createWorkflowPrompt();">New Workflow (Guide)</a></li>
										<li><a href="/model/manage/?id=<? echo $id;?>">Manage Model</a></li>
									</ul>
								</li>
							</ul>
						</div><!-- /.navbar-collapse -->
					</nav>
					<!--navigation end-->
				</div>
<?
}

function ModlrRedoConfig($server_id) {
	
	include_once('phpseclib0.3.5/Math/BigInteger.php');
	include_once('phpseclib0.3.5/Crypt/RSA.php');
	include_once('phpseclib0.3.5/Crypt/AES.php');
	include_once('phpseclib0.3.5/Crypt/RC4.php');
	include_once('phpseclib0.3.5/Net/SSH2.php');
	
	$db = new db_helper();
	$db->CommandText("SELECT server_ip, client_id, server_memory FROM modlr.servers WHERE server_id='%s';");
	$db->Parameters($server_id);
	$db->Execute();
	$r = $db->Rows();
	
	$server_ip = $r['server_ip'];
	$client_id = $r['client_id'];
	$server_memory = $r['server_memory'];

	$db2 = new db_helper();
	$db2->CommandText("SELECT hash FROM clients WHERE client_id='%s';");
	$db2->Parameters($client_id);
	$db2->Execute();
	$r2 = $db2->Rows();
	$hash = $r2['hash'];
	
	
	$ssh = new Net_SSH2($server_ip);
	$ssh->setTimeout(false);
	
	$key = new Crypt_RSA();
	$key->loadKey(file_get_contents("/var/go/cron/id_rsa"));
	if ($ssh->login("root", $key)) {
		
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
		
		$line = "echo 'client_id=".$hash."' >> '/daemon/config.cfg'";
		SSHExec($ssh,$line);
		
		$line = "echo 'backupservice=\"http://223.252.24.131/upload\"' >> '/daemon/config.cfg'";
		SSHExec($ssh,$line);
			
		$line = "wget -O'/root/modlr.sh' 'http://go.modlr.co/files/modlr".$server_memory."GB.sh'";
		SSHExec($ssh,$line);
			
		return true;
	}
	
	return false;
}

function PageChangesLogEx($new_str, $server_id, $model_id, $activity_id, $page_id, $user_id) {
	
	$sql = "INSERT INTO users_development_logs (change_user_id,server_id,model_id,activity_id,page_id,document) VALUES ('%s','%s','%s','%s','%s','%s');";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($user_id);
	$db->Parameters($server_id);
	$db->Parameters($model_id);
	$db->Parameters($activity_id);
	$db->Parameters($page_id);
	$db->Parameters($new_str);
	
	$db->Execute();
	return $db->Last_Insert_ID();
}

function PageChangesLog($new_str, $model_id, $activity_id, $page_id) {
	return PageChangesLogEx($new_str, session("active_server_id"), $model_id, $activity_id, $page_id, session("uid"));
}

function ModlrStart($server_id) {
	
	include_once('phpseclib0.3.5/Math/BigInteger.php');
	include_once('phpseclib0.3.5/Crypt/RSA.php');
	include_once('phpseclib0.3.5/Crypt/AES.php');
	include_once('phpseclib0.3.5/Crypt/RC4.php');
	include_once('phpseclib0.3.5/Net/SSH2.php');
	
	$db = new db_helper();
	$db->CommandText("SELECT server_ip, server_type, server_user, server_password FROM modlr.servers WHERE server_id='%s';");
	$db->Parameters($server_id);
	$db->Execute();
	$r = $db->Rows();
	
	$server_ip = $r['server_ip'];
	$server_user = $r['server_user'];
	$server_password = $r['server_password'];
	$server_type = $r['server_type'];
	
	$ssh = new Net_SSH2($server_ip);
	$ssh->setTimeout(false);
	
	if( !file_exists(C_CERT_ROOT) ) {
		echo "<b>Error: </b> SSH Login certificate not found at configured path.";
		die();
	}
	
	
	if( $server_type == "DROPLET" ) {
		$key = new Crypt_RSA();
		$key->loadKey(file_get_contents(C_CERT_ROOT));
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
	
	
	
	if ( $bConnected ) {
		
		$line = "killall -9 java";
		SSHExec($ssh,$line);
		
		$line = "/root/modlr.sh";
		SSHExec($ssh,$line);
		return true;
	}
	
	return false;
}



function ModlrUpdateStart($server_id) {
	
	include_once('phpseclib0.3.5/Math/BigInteger.php');
	include_once('phpseclib0.3.5/Crypt/RSA.php');
	include_once('phpseclib0.3.5/Crypt/AES.php');
	include_once('phpseclib0.3.5/Crypt/RC4.php');
	include_once('phpseclib0.3.5/Net/SSH2.php');
	
	$db = new db_helper();
	$db->CommandText("SELECT server_ip FROM modlr.servers WHERE server_id='%s';");
	$db->Parameters($server_id);
	$db->Execute();
	$r = $db->Rows();
	
	$server_ip = $r['server_ip'];
	
	$ssh = new Net_SSH2($server_ip);
	$ssh->setTimeout(false);
	
	$key = new Crypt_RSA();
	$key->loadKey(file_get_contents("/var/go/cron/id_rsa"));
	if ($ssh->login("root", $key)) {

		$line = "wget -O'/daemon/modlr.jar' 'http://go.modlr.co/files/modlr.jar'";
		SSHExec($ssh,$line);
				
		
		$line = 'killall -9 java';
		SSHExec($ssh,$line);
				
		$line = "/root/modlr.sh";
		SSHExec($ssh,$line);
		return true;
	}
	
	return false;
}

function ModlrStartDebug($server_id) {
	
	include_once('phpseclib0.3.5/Math/BigInteger.php');
	include_once('phpseclib0.3.5/Crypt/RSA.php');
	include_once('phpseclib0.3.5/Crypt/AES.php');
	include_once('phpseclib0.3.5/Crypt/RC4.php');
	include_once('phpseclib0.3.5/Net/SSH2.php');
	
	$db = new db_helper();
	$db->CommandText("SELECT server_ip FROM modlr.servers WHERE server_id='%s';");
	$db->Parameters($server_id);
	$db->Execute();
	$r = $db->Rows();
	
	$server_ip = $r['server_ip'];
	
	$ssh = new Net_SSH2($server_ip);
	$ssh->setTimeout(false);
	
	$key = new Crypt_RSA();
	$key->loadKey(file_get_contents("/var/go/cron/id_rsa"));
	if ($ssh->login("root", $key)) {
		
		$line = "/root/modlr-debug.sh";
		SSHExec($ssh,$line);
		return true;
	}
	
	return false;
}



function api_login($server_address = null,$username, $password) {
	$cookie = "";
	$service = "server.service";
	if( $server_address == null )
		$server_address = session("server_address");
		
	$json = "{\"tasks\": [{\"task\": \"login\", \"username\": \"".$username."\", \"password\": \"".$password."\"}]}";
	$result = api($server_address, $service, $json, $cookie);
	$login_result = json_decode($result);
	
	$ret = 0;
	if( isset($login_result->results) ) {
		if( count($login_result->results) > 0 ) {
			if( isset($login_result->results[0]->result) ) {
				$ret = $login_result->results[0]->result;
			}
		}
	}
	
	if( $ret == 1 ) {
		return true;
	} else {
		return false;
	}
	
	return true;
}

$api_debug = false;
$api_timeout = 0;

function getHeadersAsJSON() {
	return json_encode(getallheaders());
}

function api_short($service, $json) {
	global $api_debug;
	$server_address = session("server_address");
	$cookie = session("user_cookie");
	
	if( $api_debug ) 
		echo "<!-- api_short -->";
	
	
	return json_decode(api($server_address, $service, $json, $cookie));
}


function api_short_efficient($service, $json, $server_id) {
	global $_SESSION;
	$server_address = getAddressForServer($server_id);
	$cookie = "";
	if( !isset($_SESSION['user_cookie_'.cleanAddress($server_address)])  ) {
		api_login($server_address, $_SESSION['username'], $_SESSION['password']);
	} else {
		$cookie = $_SESSION['user_cookie_'.cleanAddress($server_address)];
	}
	
	return  json_decode(api($server_address, $service, $json, $cookie));
}

function api_short_efficient2($service, $json, $server_id) {
	global $_SESSION;
	global $api_debug;
	global $api_timeout;
	
	$api_timeout = 3;
	
	if( $api_debug ) 
		echo "<!-- api_short_efficient -->";
	
	$server_key = "server_address_".$server_id;
	$cookie_key = "user_cookie_".$server_id;
	
	$current_server = $_SESSION["server_address"];
	$current_cookie = "";
	if( isset($_SESSION["user_cookie"]) ) {
		$current_cookie = $_SESSION["user_cookie"];
	}
	
	
	$new_server = getAddressForServer($server_id);
	
	
	
	
	//is this the server which is presently selected?
	if( $new_server == $current_server ) {
		if( $api_debug ) 
			echo "<!-- api_short_efficient - server is the active server. -->";
		
		$result = json_decode(api($current_server, $service, $json, $current_cookie));
		$api_timeout = 0;
		return $result;
	} else {
		//different server, check if previously connected during this session.
		if( !isset($_SESSION[$cookie_key]) ) {
			
			if( $api_debug ) 
				echo "<!-- api_short_efficient - no cookie stored for this server during this session. -->";
			
			//cookie etc is not set, potentially unauthenticated.
			$_SESSION["server_address"] = $new_server;
			$_SESSION[$server_key] = $new_server;
			$_SESSION["user_cookie"] = "";
			
			if( api_login($new_server,$_SESSION['username'], $_SESSION['password']) ) {
				$_SESSION[$cookie_key] = $_SESSION["user_cookie"];
			} else {
				$_SESSION[$server_key] = "";
			}
			
			$result =  json_decode(api($_SESSION["server_address"], $service, $json, $_SESSION["user_cookie"]));
			
			$_SESSION[$cookie_key] = $_SESSION["user_cookie"];
			$_SESSION["user_cookie"] = $current_cookie;
			$_SESSION["server_address"] = $current_server;
			
			$api_timeout = 0;
			return $result;
		} else {
		
		
			if( $api_debug ) 
				echo "<!-- api_short_efficient - cookie stored for this server. -->";
				
			//cookied etc has been set, possibly timed out though
			$_SESSION["server_address"] = $new_server;
			$_SESSION[$server_key] = $new_server;
			$_SESSION["user_cookie"] = $_SESSION[$cookie_key];
			
			$result =  json_decode(api($_SESSION["server_address"], $service, $json, $_SESSION["user_cookie"]));
			
			$_SESSION[$cookie_key] = $_SESSION["user_cookie"];
			$_SESSION["user_cookie"] = $current_cookie;
			$_SESSION["server_address"] = $current_server;
			
			$api_timeout = 0;
			return $result;
		}
	}
}

function cleanAddress($server_address) {
	$server_address = str_replace('.', '_', $server_address);
	$server_address = str_replace(':', '_', $server_address);
	return $server_address;
}

function api($server_address, $service, $json, $cookie) {
	global $_SESSION;
	global $api_debug;
	global $requestCounter;
	global $api_timeout;
	
	
	if( $api_debug ) 
		echo "<!-- api: ".$server_address." and cookie: ".$cookie." and json:".$json." \r\n\r\n-->";
	
		
	$url = 'https://' . $server_address . '/' . $service;
	
	// Setup cURL
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, true);

	curl_setopt_array($ch, array(
		CURLOPT_POST => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT        => $api_timeout,
		CURLOPT_MAXREDIRS      => 10,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Cookie: '.$cookie
		),
		CURLOPT_POSTFIELDS => $json,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 0
	));	
	

	// Send the request
	$response = curl_exec($ch);

	// Check for errors
	if($response === FALSE){
		//todo: die gracefully'
		
		echo "<!-- CURL ERROR: ".$url."  -->\r\n";
		$str = '{"results":[{"error" : "the server is not responding"}]}';
		return $str;
	}

	
	$responseSplit = explode("\r\n\r\n",$response);

	$headers = "";
	$body = "";
	for($i=0;$i<count($responseSplit);$i++) {
		$block = $responseSplit[$i];
		if( strpos($block,"HTTP/1.1 200 OK")  !== false ) {
			$headers = $block;
			$body = $responseSplit[$i+1];
			break;
		}
	}
	
	
	//session management
	$headers = explode("\r\n",$headers);
	for($i=0;$i<count($headers);$i++) {
		$header = explode(":", $headers[$i]);
		if( trim(strtolower($header[0])) == "set-cookie") {
			$_SESSION['user_cookie'] = $header[1];
			$_SESSION['user_cookie_'.cleanAddress($server_address)] = $header[1];
			
			
			if( $api_debug ) 
				echo "<!-- api: cookie updated ".$_SESSION['user_cookie']." \r\n\r\n-->";
		}
	}

	
	$data = gzdecode($body);
	
	if( $data === false ) {
		echo "<!--FULLDATA:".$response."-->";
		return "";
	}
	
	$result = json_decode($data);
	if( isset( $result->results) ) {
		if( isset( $result->results[0]->error) ) {
			if( $result->results[0]->error == "timeout" ) {
				//only perform 5 re-attempts.
				$checkAttempts = incSessionConnectAttemptCount($server_address);
				if( $checkAttempts) {
					$service_ = $service;
					$json_ = $json;
					if( api_login($server_address, $_SESSION['username'], $_SESSION['password']) ) {
						$service = $service_;
						$json = $json_;
						return api($server_address, $service, $json, $_SESSION['user_cookie']);
					} else {
						return "{}";
					}
				} else {
					return "{}";
				}
			} else if( $result->results[0]->error == "timeout" ) {
				return "{}";
			}
		}
	}
	
	
	return $data;
}

function incSessionConnectAttemptCount($server_address) {
	global $_SESSION;
	$sessionKey = str_replace(":","_",$server_address);
	$sessionKey = str_replace("http://","",$server_address);
	
	if(isset($_SESSION[$sessionKey])) {
		$_SESSION[$sessionKey]++;
	} else {
		$_SESSION[$sessionKey] = 1;
	}
	
	if( $_SESSION[$sessionKey] > 50 ) {
		$_SESSION[$sessionKey] = 0;
		//return false;  -temp disabled
	}
	return true;
}

function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
function redirectToPage($url) {
    if ($url != "") echo "<script type='text/javascript'>window.location = '".$url."';</script>";
}




function querystring($key) {
	if( isset($_GET[$key] ) ) {
		$value = $_GET[$key] ;
		checkfield($value);
	} else {
		$value = "";
	}
	return $value;
}

function session($key) {
    $value = "";
    if (isset($_SESSION[$key])) {
        $value = $_SESSION[$key];
    }
    return trim($value);
}

function form($key) {
	if( isset($_POST[$key] ) ) {
		$value = $_POST[$key] ;
		checkfield($value);
	} else {
		$value = "";
	}
	return $value;
}

function left($string, $count){
    return substr($string, 0, $count);
}
function right($value, $count){
    $value = substr($value, (strlen($value) - $count), strlen($value));
    return $value;
}




function OutputNotification($sMsg) {
	if( $sMsg != '' ) {
		echo "<div style='background-color:#666666;padding:3px; border:1px solid #000000;text-align:center;'><b><font color='white'>".$sMsg."</font></b></div><br/>";
	}
}

function checkfield($value){
	$value = str_replace($value,"'","''");

	if( strpos(strtolower($value)," like ") > -1 ) { exit; }
	if( strpos(strtolower($value)," or ") > -1 ) { exit; }
	if( strpos(strtolower($value)," and ") > -1 ) { exit; }
	if( strpos(strtolower($value)," union ") > -1 ) { exit; }
	if( strpos(strtolower($value)," like ") > -1 ) { exit; }
	if( strpos(strtolower($value)," delete ") > -1 ) { exit; }
	if( strpos(strtolower($value)," drop ") > -1 ) { exit; }
	if( strpos(strtolower($value)," % ") > -1 ) { exit; }
	if( strpos(strtolower($value)," * ") > -1 ) { exit; }

	return $value;
}

function clean_xml_from($sData) {
  return "<![CDATA[" . $sData . "]]>";
}

function serverSetupCol() {
?>
	<script type='text/javascript'>
	function onChangeServerCode(txt) {
		var val = txt.value;
		val = val.replace(/ /gi,"-");
		txt.value = val;
		
	}
	</script>
	<div class="col-md-6">
		<div class="prf-contacts">
			<h2> <span><i class="fa fa-plus-circle"></i></span> Setup a New Server</h2>
			<div class="location-info">
	
				<p>You can setup a new server in minutes using the form below.</p>
				
				<form class="form-horizontal" action="/manage/" method='post'>
					<input type="hidden" id='action' name='action' value='provision'>
					<div class="form-group">
						<label class="col-lg-6 control-label">Name: (an identifier for you)</label>
						<div class="col-sm-11">
							<input type="text" id='server_name' name='server_name' onchange='onChangeServerCode(this);' class="form-control">
						</div>
					</div>
					<p>Note: no spaces or underscores are allowed. Sample Names: ProductionServer or Production-Server</p>
					<div class="form-group">
						<label class="col-lg-2 col-sm-2 control-label">Location:</label>
						<div class="col-lg-6">
							<select class="form-control" style="width: 300px" id='server_region' name='server_region'>
<?
$regions = digital_ocean_return_all_regions();
for($i=0;$i<count($regions->regions);$i++) {
	$region = $regions->regions[$i];
	
	$regionStr = $region->name;
	if( strpos($regionStr,"1") !== false ) {
		$regionStr = substr($regionStr,0, strlen($regionStr)-2);
		echo "<option value='".$region->slug."'>".$regionStr."</option>";
	}
}
?>
							
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-2 col-sm-2 control-label">Memory:</label>
						<div class="col-lg-6">
							<select class="form-control" style="width: 300px" id='server_memory' name='server_memory'>
<?


$db = new db_helper();
$db->CommandText("SELECT size_id, size_name FROM digitalocean_sizes ORDER BY size_order ASC;");
$db->Execute();
if ($db->Rows_Count() > 0) {
	while( $r = $db->Rows() ) {
		echo "<option value='".$r['size_id']."'>".$r['size_name']."</option>";
	}
}


?>
							
							</select>
						</div>
					</div>
					<p><b>Note:</b> On occasion there is an inability to provide instances of particular sizes in specific regions. You will be notified if this is the case.</p>
				
					<button type="submit" class="btn btn-info">Provision Server</button>
				</form>
				
				
				<br><br>
			</div>
			
		</div>
	</div>
<?
}


function outputServerPricing() {
?>
<table class="table table-bordered table-striped table-condensed">
	<thead>
	<tr>
		<th style='text-align:left;vertical-align:top;'>Memory</th>
		<th style='text-align:center;vertical-align:top;'>Cost per Month<br/>(Australian Hosting)</th>
		<th style='text-align:center;vertical-align:top;'>Cost per Month<br/>(Remote Hosting)</th>
	</tr>
	</thead>
	<tbody>
<?
$db = new db_helper();
$db->CommandText("SELECT product_name_short, product_price, product_price_remote FROM modlr.product_pricing WHERE product_hidden=0 AND product_type='SERVER' ORDER BY product_id ASC;");
$db->Execute();
if ($db->Rows_Count() > 0) {
	while( $r = $db->Rows() ) {
		echo "<tr><td>".$r['product_name_short']."</td><td class='numeric' style='text-align:right;'>$".$r['product_price'].".00</td><td class='numeric' style='text-align:right;'>$".$r['product_price_remote'].".00</td></tr>";
	}
}

?>

	</tbody>
</table>
<p><b>Note:</b> Billing is monthly based on daily utilisation. The minimal usage period is a single day.
<br/><br/><b>Note:</b> There is a limit of ten 1GB servers per customer.</p>
<?
}

function userActivateServerCustomer($server_id, $user_id) {

	$sql = "SELECT server_ip,server_port,servers.client_id FROM modlr.servers WHERE servers.client_id IN (SELECT client_id FROM users_clients WHERE user_id='%s')  AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($user_id);
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$_SESSION['active_server_id'] = $server_id;
		$_SESSION['active_client_id'] = $r['client_id'];
		$_SESSION['server_address'] = getAddressForServer($_SESSION['active_server_id']);
		
		$sql = "UPDATE modlr.users SET active_server_id='%s' WHERE id = '%s';";
		$db = new db_helper();
		$db->CommandText($sql);
		$db->Parameters($server_id);
		$db->Parameters(session('uid'));
		$db->Execute();
	}
}

function getClientForServerId($server_id) {
	$sql = "SELECT client_id FROM modlr.servers WHERE servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r['client_id'];
	}
	return 0;
}

function userActivateServer($server_id, $client_id) {

	$sql = "SELECT server_ip,server_port FROM modlr.servers WHERE servers.client_id='%s' AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($client_id);
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$_SESSION['active_server_id'] = $server_id;
		$_SESSION['server_address'] = getAddressForServer($_SESSION['active_server_id']);
		$_SESSION['active_client_id'] = $client_id;
		
		
		$sql = "UPDATE modlr.users SET active_server_id='%s' WHERE id = '%s';";
		$db = new db_helper();
		$db->CommandText($sql);
		$db->Parameters($server_id);
		$db->Parameters(session('uid'));
		$db->Execute();
	}
}

function thisUsersServers() {
	$servers = array();
	
	$sql = "SELECT server_id FROM servers  WHERE client_id IN (SELECT client_id FROM users_clients WHERE user_id='%s')  AND server_is_deleted=0;";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session('uid'));
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			array_push($servers, $r['server_id']);
		}
	}
	return $servers;
}



function userServers($client_id = null) {
	$servers = array();
	
	if( $client_id == null )
		$client_id = session("client_id");
	
	$sql = "SELECT server_id FROM servers  WHERE client_id IN (SELECT client_id FROM users_clients WHERE client_id='%s')  AND server_is_deleted=0;";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			array_push($servers, $r['server_id']);
		}
	}
	return $servers;
}


function userHasLiveServers() {
	$clientid = session("client_id");
	//filers out cd drives
	$sql = "SELECT server_id, server_name,	server_domain,auth_key,IF(clients_servers.date_updated<>'0000-00-00 00:00:00',1,0) AS is_live,server_comments,clients_servers.date_updated  FROM clients_servers
			LEFT JOIN clients ON clients.client_id = clients_servers.client_id ";

	if( intval($clientid) != 0 ) {
		$sql = $sql . "  WHERE clients_servers.client_id='". $clientid ."' AND server_is_deleted=0";
	}
	$sql = $sql . "  ORDER BY server_name ASC";

	$db = new db_helper();
	$db->CommandText($sql);

	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			$is_live = $r['is_live'];
			if( $is_live == 1 ) {
				return true;
			}
		}
	}
	return false;
}

function randomPassword($len) {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $len; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function GetTemplate($template) {
	$db = new db_helper();
	$db->CommandText("SELECT email_body,email_body_plain,email_subject FROM email_templates WHERE email_code='%s';");
	$db->Parameters($template);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();

		$values = array(
			"body" => $r['email_body'],
			"body_plain" => $r['email_body_plain'],
			"subject" => $r['email_subject']
		);


		return $values ;
	}
	return null;
}

function checkUserPassword($user_id, $password_md5) {
	$db = new db_helper();
	$db->CommandText("SELECT email FROM users WHERE id='%s' AND password='%s';");
	$db->Parameters($user_id);
	$db->Parameters($password_md5);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return true;
	}
	return false;
}
function getUserEmail() {
	$db = new db_helper();
	$db->CommandText("SELECT email FROM users WHERE id='%s';");
	$db->Parameters(session("uid"));
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r['email'];
	}
	return "";
}
function getUserEmailById($uid) {
	$db = new db_helper();
	$db->CommandText("SELECT email FROM users WHERE id='%s';");
	$db->Parameters($uid);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r['email'];
	}
	return "";
}
function getUserClientAdminEmail() {
	if( session("client_id") == 0 ) {
		return "ben.hill@modlr.co";
	}

	$db = new db_helper();
	$db->CommandText("SELECT contact_id FROM clients WHERE client_id='%s';");
	$db->Parameters(session("client_id"));
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$contact_id =  $r['contact_id'];

		$db->CommandText("SELECT email FROM users WHERE id='%s';");
		$db->Parameters($contact_id);
		$db->Execute();
		if ($db->Rows_Count() > 0) {
			$r = $db->Rows();
			return $r['email'];
		}
	}
	return "";
}

function getHashForClient($client_id) {
	$db = new db_helper();
	$db->CommandText("SELECT server_address,hash FROM clients WHERE client_id = %s;");
	$db->Parameters($client_id);
	$db->Execute();
	$r = $db->Rows();
	return $r["hash"];
}
function getAddressForServer($server_id) {
	$db = new db_helper();
	$db->CommandText("SELECT server_ip,server_port FROM servers WHERE server_id = %s;");
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r["server_ip"] . ":" . $r["server_port"];
	}
	return "";
}
function getPasswordForServer($server_id) {
	$db = new db_helper();
	$db->CommandText("SELECT server_datastore_password FROM servers WHERE server_id = %s;");
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r["server_datastore_password"];
	}
	return "";
}

function server_is_online($server_ip) {
	$online = false;
	if ( $socket = @fsockopen($server_ip, 22, $errno, $errstr, 5) )
	{
		$online = true;
		fclose($socket);
	}
	return $online;
}


function server_is_online_on_port($server_ip,$server_port) {
	$online = false;
	if ( $socket = @fsockopen($server_ip, $server_port, $errno, $errstr, 5) )
	{
		$online = true;
		fclose($socket);
	}
	return $online;
}

function getUserAdminEmailFromClient($client_id) {
	
	$db = new db_helper();
	$db->CommandText("SELECT contact_id FROM clients WHERE client_id='%s';");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$contact_id =  $r['contact_id'];

		$db->CommandText("SELECT email FROM users WHERE id='%s';");
		$db->Parameters($contact_id);
		$db->Execute();
		if ($db->Rows_Count() > 0) {
			$r = $db->Rows();
			return $r['email'];
		}
	}
	return "";
}


function time2str($ts)
{
    if(!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return 'now';
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
    }
    else
    {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 120) return 'in a minute';
            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if($diff < 7200) return 'in an hour';
            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if($day_diff == 1) return 'Tomorrow';
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return 'next week';
        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if(date('n', $ts) == date('n') + 1) return 'next month';
        return date('F Y', $ts);
    }
}

function RelativeTime($time, $now = false)
{
    $time = (int) $time;
    $curr = $now ? $now : time();
    $shift = $curr - $time;

    if ($shift < 45):
        $diff = $shift;
        $term = "second";
    elseif ($shift < 3600):
        $diff = round($shift / 60);
        $term = "minute";
    elseif ($shift < 86400):
        $diff = round($shift / 60 / 60);
        $term = "hour";
    else:
        $diff = round($shift / 60 / 60 / 24);
        $term = "day";
    endif;

    if ($diff != 1) $term .= "s";
    return "$diff $term";
}

function printf_array($format, $arr)
{
    return call_user_func_array('printf', array_merge((array)$format, $arr));
}

function sprintf_array($format, $arr)
{
    return call_user_func_array('sprintf', array_merge((array)$format, $arr));
}

function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
{

    /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
    (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33".
                 The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    s - Number of full seconds (default)
    */

    if (!$using_timestamps) {
        $datefrom = strtotime($datefrom, 0);
        $dateto = strtotime($dateto, 0);
    }
    $difference = $dateto - $datefrom; // Difference in seconds

    switch($interval) {
        case 'yyyy': // Number of full years
        $years_difference = floor($difference / 31536000);
        if (mktime(date("H", $datefrom),
                              date("i", $datefrom),
                              date("s", $datefrom),
                              date("n", $datefrom),
                              date("j", $datefrom),
                              date("Y", $datefrom)+$years_difference) > $dateto) {

        $years_difference--;
        }
        if (mktime(date("H", $dateto),
                              date("i", $dateto),
                              date("s", $dateto),
                              date("n", $dateto),
                              date("j", $dateto),
                              date("Y", $dateto)-($years_difference+1)) > $datefrom) {

        $years_difference++;
        }
        $datediff = $years_difference;
        break;

        case "q": // Number of full quarters
        $quarters_difference = floor($difference / 8035200);
        while (mktime(date("H", $datefrom),
                                   date("i", $datefrom),
                                   date("s", $datefrom),
                                   date("n", $datefrom)+($quarters_difference*3),
                                   date("j", $dateto),
                                   date("Y", $datefrom)) < $dateto) {

        $months_difference++;
        }
        $quarters_difference--;
        $datediff = $quarters_difference;
        break;

        case "m": // Number of full months
        $months_difference = floor($difference / 2678400);
        while (mktime(date("H", $datefrom),
                                   date("i", $datefrom),
                                   date("s", $datefrom),
                                   date("n", $datefrom)+($months_difference),
                                   date("j", $dateto), date("Y", $datefrom)) ==  7)
                        { // Sunday
        $days_remainder--;
        }
        if ($odd_days > 6) { // Saturday
        $days_remainder--;
        }
        $datediff = ($weeks_difference * 5) + $days_remainder;
        break;

        case "ww": // Number of full weeks
        $datediff = floor($difference / 604800);
        break;

        case "h": // Number of full hours
        $datediff = floor($difference / 3600);
        break;

        case "n": // Number of full minutes
        $datediff = floor($difference / 60);
        break;

        default: // Number of full seconds (default)
        $datediff = $difference;
        break;
    }

    return $datediff;
}

function RoleGet() {
    if (!validator::isEmpty(session("client_id"))) {
        if (intval(session("client_id")) == 0) return "ADMIN-SUPER";
        else {
            $db = new db_helper();
            $db->CommandText("SELECT COUNT(*) FROM users_clients WHERE user_id = %s;");
            $db->Parameters(intval(session("uid")));
            if ($db->ExecuteScalar() > 1) return "CONSULTANT";
            else {
                if (intval(session("readonly")) == 0) return "ADMIN-TM1";
                else return "READ-ONLY";
            }
        }
    }
    return "";
}

function IsTestMode() {
    return ((str_replace("localhost", "", $_SERVER["HTTP_HOST"]) != $_SERVER["HTTP_HOST"]) || (str_replace("dev", "", $_SERVER["HTTP_HOST"]) != $_SERVER["HTTP_HOST"]));
}

function getProductPrice($product_code) {
	$sql = "SELECT product_price FROM modlr.product_pricing WHERE product_code='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($product_code);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r['product_price'];
	}
	return "ERROR - Product Code not Found.";
}
function getProductId($product_code) {
	$sql = "SELECT product_id FROM modlr.product_pricing WHERE product_code='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($product_code);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r['product_id'];
	}
	return "ERROR - Product Code not Found.";
}

function createNewInvoice($client_id, $billing_period) {
	$db = new db_helper();
	$db->CommandText("INSERT INTO clients_invoices (client_id, billing_period) VALUES ('%s','%s');");
	$db->Parameters($client_id);
	$db->Parameters($billing_period);
	$db->Execute();
	return $db->Last_Insert_ID();
}
function updateInvoice($invoice_id, $amount_services) {
	$db = new db_helper();
	$db->CommandText("UPDATE clients_invoices SET amount_services='%s',amount_tax='%s',amount_total='%s',invoice_paid='%s' WHERE invoice_id='%s';");
	$db->Parameters($amount_services);
	$db->Parameters($amount_services * 0.1);
	$db->Parameters($amount_services + ($amount_services * 0.1));
	
	if( $amount_services == 0 )
		$db->Parameters(1);
	else
		$db->Parameters(0);
	
	$db->Parameters($invoice_id);
	$db->Execute();
	return;
}
function createNewInvoiceLine($invoice_id, $product_id,$units,$price,$month_days) {
	$db = new db_helper();
	$db->CommandText("INSERT INTO clients_invoices_lines (invoice_id, product_id, units, price, revenue) VALUES ('%s','%s','%s','%s','%s');");
	$db->Parameters($invoice_id);
	$db->Parameters($product_id);
	$db->Parameters($units);
	$db->Parameters($price);
	$db->Parameters(number_format($units/$month_days * $price,2));
	$db->Execute();
	return $db->Last_Insert_ID();
}

function days_in_month($timestamp = NULL) {
    if(is_null($timestamp))
        $timestamp = time();
    return date('t', $timestamp);
}

function createInvoice($client_id, $billing_period) {
	//echo $client_id ." - ". $billing_period;
	
	$db = new db_helper();
	//before creating a new invoice we need to see if one has already been generated for this client.
	$db->CommandText("SELECT invoice_id FROM clients_invoices WHERE clients_invoices.client_id='%s' AND DATE(billing_period) = DATE('%s');");
	$db->Parameters($client_id);
	$db->Parameters($billing_period);
	$db->Execute();
	if ($db->Rows_Count() == 0) {
		
		
		$db = new db_helper();
		$db->CommandText("SELECT 
					SUM(units_modeller) AS units_modeller, 
					SUM(units_planner) AS units_planner,
					SUM(units_1gb) AS units_1gb, 
					SUM(units_2gb) AS units_2gb, 
					SUM(units_4gb) AS units_4gb,
					SUM(units_8gb) AS units_8gb, 
					SUM(units_12gb) AS units_12gb, 
					SUM(units_16gb) AS units_16gb,
					SUM(units_32gb) AS units_32gb
			 FROM modlr.clients_utilisation WHERE  
			date_added >= DATE('%s') and date_added < DATE('%s') + INTERVAL 1 MONTH
			AND client_id='%s';");


		$db->Parameters($billing_period);
		$db->Parameters($billing_period);
		$db->Parameters($client_id);
		$db->Execute();
		
		if ($db->Rows_Count() >= 0) {
		
			$invoice_id = createNewInvoice($client_id, $billing_period);
			
			$units_modeller = 0;
			$units_planner = 0;
			$units_1gb = 0;
			$units_2gb = 0;
			$units_4gb = 0;
			$units_8gb = 0;
			$units_12gb = 0;
			$units_16gb = 0;
			$units_32gb = 0;
			
			
			
			while( $r = $db->Rows() ) {
				
				$units_modeller += $r['units_modeller'];
				$units_planner += $r['units_planner'];
				$units_1gb += $r['units_1gb'];
				$units_2gb += $r['units_2gb'];
				$units_4gb += $r['units_4gb'];
				$units_8gb += $r['units_8gb'];
				$units_12gb += $r['units_12gb'];
				$units_16gb += $r['units_16gb'];
				$units_32gb += $r['units_32gb'];
				
			}
			
			$month_days = days_in_month(strtotime($billing_period));
			
			$services = 0;
			$units_modeller--; //one free
			if( $units_modeller > 0 ) {
				
				$product_code = "units_modeller";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_modeller;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			if( $units_planner > 0 ) {
				$product_code = "units_planner";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_planner;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			
			if( $units_1gb > 0 ) {
				$product_code = "units_1gb";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_1gb;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			if( $units_2gb > 0 ) {
				$product_code = "units_2gb";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_2gb;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			if( $units_4gb > 0 ) {
				$product_code = "units_4gb";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_4gb;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			if( $units_8gb > 0 ) {
				$product_code = "units_8gb";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_8gb;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			if( $units_12gb > 0 ) {
				$product_code = "units_12gb";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_12gb;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			if( $units_16gb > 0 ) {
				$product_code = "units_16gb";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_16gb;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			if( $units_32gb > 0 ) {
				$product_code = "units_32gb";
				$product_id = getProductId($product_code);
				$price = getProductPrice($product_code);
				$units = $units_32gb;
				$services += number_format($units/$month_days * $price,2);
				createNewInvoiceLine($invoice_id, $product_id, $units, $price,$month_days);
			}
			
			updateInvoice($invoice_id, $services);
			
			
			$email = getUserAdminEmailFromClient($client_id);
			
			sendInvoiceNotification($email,$invoice_id);
		} else {
			//no utilisation so no invoice.
		}
		
	} else { 
		//has invoice already 
	}
}


function updateUtilisation($client_id) {
	$db = new db_helper();
	
	$modeller_user_count = 0;
	$planner_user_count = 0;
	
	$sql = "SELECT users_clients.role,COUNT(users_clients.role) as user_count FROM modlr.users_clients  LEFT JOIN users ON users.id=users_clients.user_id  WHERE users_clients.client_id='%s'  AND users.email IS NOT NULL AND users.account_disabled = 0 GROUP BY role;";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			$role = $r['role'];
			$user_count = $r['user_count'];
			
			if( $role == "PLANNER" ) {
				$planner_user_count = $user_count;
			} else {
				$modeller_user_count = $user_count;
			}	
		}
	}

	$db->CommandText("SELECT utilisation_id FROM clients_utilisation WHERE clients_utilisation.client_id='%s' AND DATE(date_added) = CURDATE();");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() == 0) {
		$db = new db_helper();
		$db->CommandText("INSERT INTO clients_utilisation (client_id, units_modeller, units_planner) VALUES ('%s','%s','%s');");
		$db->Parameters($client_id);
		$db->Parameters($modeller_user_count);
		$db->Parameters($planner_user_count);
		$db->Execute();
	}
	
	$server_1gb = 0;
	$server_2gb = 0;
	$server_4gb = 0;
	$server_8gb = 0;
	$server_12gb = 0;
	$server_16gb = 0;
	$server_32gb = 0;
	
	$sql = "SELECT server_memory FROM modlr.servers WHERE servers.client_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			$server_memory = $r['server_memory'];
			if( $server_memory == 1 ) {
				$server_1gb++;
			} else if( $server_memory == 2 ) {
				$server_2gb++;
			} else if( $server_memory == 4 ) {
				$server_4gb++;
			} else if( $server_memory == 8 ) {
				$server_8gb++;
			} else if( $server_memory == 12 ) {
				$server_12gb++;
			} else if( $server_memory == 16 ) {
				$server_16gb++;
			} else if( $server_memory == 32 ) {
				$server_32gb++;
			}
		}
	}
	
	$db = new db_helper();
	$db->CommandText("UPDATE clients_utilisation SET 
				units_modeller = GREATEST(units_modeller,'%s'),
				units_planner = GREATEST(units_planner,'%s'),
				units_1gb = GREATEST(units_1gb,'%s'),
				units_2gb = GREATEST(units_2gb,'%s'),
				units_4gb = GREATEST(units_4gb,'%s'),
				units_8gb = GREATEST(units_8gb,'%s'),
				units_12gb = GREATEST(units_12gb,'%s'),
				units_16gb = GREATEST(units_16gb,'%s'),
				units_32gb = GREATEST(units_32gb,'%s'),
				last_updated = CURRENT_TIMESTAMP
				 WHERE client_id='%s' AND DATE(date_added) = CURDATE();");
	
	
	$db->Parameters($modeller_user_count);
	$db->Parameters($planner_user_count);
	
	$db->Parameters($server_1gb);
	$db->Parameters($server_2gb);
	$db->Parameters($server_4gb);
	$db->Parameters($server_8gb);
	$db->Parameters($server_12gb);
	$db->Parameters($server_16gb);
	$db->Parameters($server_32gb);
	
	$db->Parameters($client_id);
	$db->Execute();
	
	
}

?>
