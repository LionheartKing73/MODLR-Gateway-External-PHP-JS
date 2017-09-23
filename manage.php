<?
include_once("lib/lib.php");
include_once("lib/server_functions.php");

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$msg = ""; 
$msgType = "";

$action = form("action");
if( $action == "" )
	$action = querystring("action");
	
if( $action == "images" ) {

	echo "<!-- ";
	print_r(digital_ocean_return_all_images());
	echo "-->";
}
	
if( $action == "test_do" ) {
	
	//$var1 = digital_ocean_return_all_images();
	//$var2 = digital_ocean_return_all_keys();
	//$var3 = digital_ocean_return_all_regions();
	//$var4 = digital_ocean_return_all_sizes();
	//$var5 = digital_ocean_return_all_droplets();
	
}

	
if( $action == "datastore_reset_account_password" ) {
	
	$username = querystring("username");
	$password = randomPassword(12);
	
	
	$server_address = session("server_address");
	$values = explode(":",$server_address);
	$server_address = $values[0];
	
	$passwordDatabase = getPasswordForServer(session("active_server_id"));

	$dbRemote = new db_helper();
	$dbRemote->Host = $server_address;
	$dbRemote->User = C_DB_USER_HOST;
	$dbRemote->Password = $passwordDatabase;
	$dbRemote->Database = C_DB_NAME_HOST;
	$dbRemote->Close();
	$dbRemote->connect();
	
	$sql = "SET PASSWORD FOR '".$username."'@'%' = PASSWORD('".$password."');";
	$dbRemote->CommandText($sql);
	$dbRemote->Execute();
	
	
	$sql = "GRANT ALL PRIVILEGES ON datastore TO '".$username."'@'%';";
	$dbRemote->CommandText($sql);
	$dbRemote->Execute();
	
	$sql = "GRANT SELECT, UPDATE,INSERT, DROP, CREATE, ALTER, DELETE ON * . *  TO '".$username."'@'%';";
	$dbRemote->CommandText($sql);
	$dbRemote->Execute();
	
	$db = new db_helper();
	$sql = "UPDATE servers_accounts SET password='%s' WHERE server_id='%s' AND username='%s';";
	$db->CommandText($sql);
	$db->Parameters($password);
	$db->Parameters(session("active_server_id"));
	$db->Parameters($username);
	$db->Execute();

	
	header("Location: /manage/?action=datastore_reset_ok");

} else if( $action == "datastore_reset_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Oh joy!</strong> The account has been updated.";
	
	
} else if( $action == "active_client_set" ) {
	
	$client_id = form("updated_client_id");
	
	$db = new db_helper();
	$db->CommandText("SELECT client_id FROM users_clients WHERE user_id='%s' AND  role='MODELLER' AND client_id = '%s';");
	$db->Parameters(session('uid'));
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$sql = "UPDATE users SET client_id = '%s' WHERE id = '%s';";
		$db = new db_helper();
		$db->CommandText($sql);
		$db->Parameters($client_id);
		$db->Parameters(session("uid"));
		$db->Execute();
		
		$_SESSION["client_id"] = $client_id;
		
		$msgType = "alert alert-success fade in";
		$msg = "<strong>Oh joy!</strong> The account settings have been updated.";
	} else {
			
		$msgType = "alert alert-block alert-danger fade in";
		$msg = "<strong>Uh Oh!</strong> You do not appear to be part of that account.";
		
		
	}
	
	
} else if( $action == "datastore_add_account" ) {

	$username = form("username");
	$password = randomPassword(12);
	
	$sql = "CREATE USER '".$username."'@'%' IDENTIFIED BY '".$password."';";
	
	$server_address = session("server_address");
	$values = explode(":",$server_address);
	$server_address = $values[0];
	
	$passwordDatabase = getPasswordForServer(session("active_server_id"));
	
	$dbRemote = new db_helper();
	$dbRemote->Host = $server_address;
	$dbRemote->User = C_DB_USER_HOST;
	$dbRemote->Password = $passwordDatabase;
	$dbRemote->Database = C_DB_NAME_HOST;
	$dbRemote->Close();
	$dbRemote->connect();
	$dbRemote->CommandText($sql);
	$dbRemote->Execute();
	
	$sql = "GRANT ALL PRIVILEGES ON datastore TO '".$username."'@'%';";
	$dbRemote->CommandText($sql);
	$dbRemote->Execute();
	
	$sql = "GRANT SELECT, UPDATE,INSERT,DROP, CREATE, ALTER,  DELETE ON * . *  TO '".$username."'@'%';";
	$dbRemote->CommandText($sql);
	$dbRemote->Execute();
	
	
	if( $dbRemote->HasErrors() ) {
		
		
		header("Location: /manage/?action=datastore_create_error&error=" + $dbRemote->Errors());
		
	} else {
	
		$sql = "INSERT INTO servers_accounts (server_id,username,password) VALUES ('%s','%s','%s');";
		$db = new db_helper();
		$db->CommandText($sql);
		$db->Parameters(session("active_server_id"));
		$db->Parameters($username);
		$db->Parameters($password);
		$db->Execute();
	
		header("Location: /manage/?action=datastore_create_ok");
		
	}

	

} else if( $action == "datastore_create_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Oh joy!</strong> The account has been created.";
	

} else if( $action == "datastore_create_error" ) {

	$msgType = "alert alert-block alert-danger fade in";
	$msg = "<strong>Uh Oh!</strong> The account could not be created.<br/>".querystring("error");
	
} else if( $action == "provision" ) {

	$server_memory = form("server_memory");
	$server_region = form("server_region");
	$server_name   = form("server_name");

	
	
	$result = digital_ocean_provision_server($server_name, $server_memory, $server_region);
	
	if( !is_object($result) ) {
		$msgType = "alert alert-block alert-danger fade in";
		$msg = "<strong>Oh snap!</strong>".$result;
		
		echo "<!-- ";
		print_r($result);
		echo "-->";
		
		header("Location: /manage/?action=cloud_provision_error&error=".$result);
		
	} else if( !property_exists( $result, 'droplet' ) ) {
		$msgType = "alert alert-block alert-danger fade in";
		
		echo "<!-- ";
		print_r($result);
		echo "-->";
		
		header("Location: /manage/?action=cloud_provision_error&error=".$result);
		
	} else {
		//if( $result->status == "OK" ) {
			$msgType = "alert alert-success fade in";
			$droplet_id = $result->droplet->id;
		
			$memory = digital_ocean_memory_name($server_memory);
			$region = digital_ocean_region_name($server_region);
		
			$sql = "INSERT INTO servers (client_id,server_codename,server_memory,server_type,server_region,droplet_id) VALUES ('%s','%s','%s','%s','%s','%s');";
			$db = new db_helper();
			$db->CommandText($sql);
			$db->Parameters(session("client_id"));
			$db->Parameters($server_name);
			$db->Parameters($memory);
			$db->Parameters("DROPLET");
			$db->Parameters($region);
			$db->Parameters($droplet_id);
			$db->Execute();
		
			sendProvisionNotification(session("username"), $server_name, $memory, $region);
		
			$servers = userServers() ;
			if( count($servers) == 1 ) {
				userActivateServer($servers[0], session('client_id'));
			}
		
			header("Location: /manage/?action=cloud_provision_ok");
		/*
		} else {
			
			
			header("Location: /manage/?action=cloud_provision_error&error=Provider returned a status other than successful");
		}*/
	}
	
	

} else if( $action == "cloud_provision_error" ) {

	$msgType = "alert alert-block alert-danger fade in";
	$msg = "<strong>Oh snap!</strong> The server could not be provisioned.<br/>More details: ".querystring("error");
	
} else if( $action == "cloud_provision_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Oh joy!</strong> You server is presently being provisioned. The provisioning process typically takes 5-10 minutes before your server is ready. Once your server is ready you need to select it in the application servers table and set it to the active server in order to use it.";
	
} else if( $action == "start" ) {
	$server_id = querystring("server_id");
	$sql = "SELECT server_ip,server_port FROM modlr.servers WHERE servers.client_id='%s' AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session('client_id'));
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		
	
		ModlrStart($server_id);
		header("Location: /manage/?action=service_start_ok");
		
	}
		
} else if( $action == "rebuild_config" ) {
	$server_id = querystring("server_id");
	$sql = "SELECT server_ip,server_port FROM modlr.servers WHERE servers.client_id='%s' AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session('client_id'));
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		
	
		ModlrRedoConfig($server_id);
		header("Location: /manage/?action=service_start_ok");
		
	}
} else if( $action == "service_start_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Oh joy!</strong> You server has been started. It may take some time to become active depending on how big your models are.";
	
} else if( $action == "update" ) {
	$server_id = querystring("server_id");
	$sql = "SELECT server_ip,server_port FROM modlr.servers WHERE servers.client_id IN (SELECT client_id FROM users_clients WHERE user_id='%s') AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session('uid'));
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		
		$service = "server.service";
		$json = "{\"tasks\": [{\"task\": \"server.shutdown\"}]}";
		api_short($service,$json);
		
		ModlrUpdateStart($server_id);
		$msgType = "alert alert-success fade in";
		$msg = "<strong>Oh joy!</strong> You server has been restarted. It may take some time to become active depending on how big your models are.";
	
		header("Location: /manage/?action=service_update_ok");
	}
	
} else if( $action == "service_update_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Oh joy!</strong> You server has been updated and rebooted. It may take some time to become active depending on how big your models are.";
	
} else if( $action == "stop" ) {
	$server_id = querystring("server_id");
	$sql = "SELECT server_ip,server_port FROM modlr.servers WHERE servers.client_id IN (SELECT client_id FROM users_clients WHERE user_id='%s') AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session("uid"));
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		
		$service = "server.service";
		$json = "{\"tasks\": [{\"task\": \"server.shutdown\"}]}";
		api_short($service,$json);
		
		header("Location: /manage/?action=service_stop_ok");
	}
	
} else if( $action == "service_stop_ok" ) {

	$msgType = "alert alert-success fade in";
		$msg = "<strong>Oh joy!</strong> You server has been stopped. It will not respond again until you manually start it.";
	
} else if( $action == "restart" ) {
	$server_id = querystring("server_id");
	
	userActivateServer($server_id, session('client_id'));
	
	$sql = "SELECT server_ip,server_port FROM modlr.servers WHERE servers.client_id IN (SELECT client_id FROM users_clients WHERE user_id='%s') AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session("uid"));
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		
		
		$service = "server.service";
		$json = "{\"tasks\": [{\"task\": \"server.shutdown\"}]}";
		api_short_efficient($service,$json,$server_id);
		
		$result = ModlrStart($server_id);
		if( $result) {
			header("Location: /manage/?action=service_restart_ok");
		} else {
			header("Location: /manage/?action=service_restart_failed");
		}
	}
	
} else if( $action == "service_restart_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Oh joy!</strong> You server has been restarted. It may take some time to become active depending on how big your models are.";
	
} else if( $action == "service_restart_failed" ) {

	$msgType = "alert alert-danger fade in";
	$msg = "<strong>Uh Oh!</strong> You server has failed to restart. Please contact support for assistance in starting your server.";
	
} else if( $action == "remove_user" ) {
	$userid = querystring("userid");
	
	$db = new db_helper();
	$db->CommandText("SELECT * FROM modlr.clients WHERE client_id=%s AND contact_id=%s;");
	$db->Parameters(session('client_id'));
	$db->Parameters($userid);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
	
		$msgType = "alert alert-warning fade in";
		$msg = "<strong>Warning</strong> The primary account holder cannot be removed from the account.";
		
			
	} else {
		if( intval($userid) == intval(session("uid")) ) {
		
			$msgType = "alert alert-warning fade in";
			$msg = "<strong>Warning</strong> You cannot disable your own account.";
		
		} else {
	
			$db->CommandText("UPDATE users SET account_disabled ='1' WHERE client_id=%s AND id='%s';");
			$db->Parameters(session("client_id"));
			$db->Parameters($userid);
			$db->Execute();
			header("Location: /manage/?action=remove_user_ok");
		}
	}
						
	
} else if( $action == "remove_user_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Oh joy!</strong> The selected account has been disabled.";


} else if( $action == "remove_server" ) {
	$serverid = querystring("serverid");
	
	$db = new db_helper();
	$db->CommandText("SELECT droplet_id FROM modlr.servers WHERE servers.client_id IN (SELECT client_id FROM users_clients WHERE user_id='%s') AND server_id=%s;");
	$db->Parameters(session('uid'));
	$db->Parameters($serverid);
	$db->Execute();
	if ($db->Rows_Count() == 0) {
	
		$msgType = "alert alert-warning fade in";
		$msg = "<strong>Warning</strong> Can not find the server specified.";
		
	} else {
		
		$r = $db->Rows();
		$droplet_id = $r["droplet_id"];
		
		digital_ocean_destroy_server($droplet_id);
		
		$db->CommandText("UPDATE servers SET server_is_deleted ='1' WHERE servers.client_id IN (SELECT client_id FROM users_clients WHERE user_id='%s') AND server_id=%s;");
		$db->Parameters(session('uid'));
		$db->Parameters($serverid);
		$db->Execute();
		
		$servers = userServers();
		if( count($servers) > 0 ) {
			userActivateServer($servers[0], session('client_id'));
		} else {
			$_SESSION['active_server_id'] = 0;
			$_SESSION['server_address'] = "";
		}
		
		header("Location: /manage/?action=remove_server_ok");
	
	}
						
	
} else if( $action == "remove_server_ok" ) {

	$msgType = "alert alert-success fade in";
	$msg = "<strong>Success</strong> The selected server has been removed from your account.";
	
	
} else if( $action == "restart-debug" ) {
	$server_id = querystring("server_id");
	$sql = "SELECT server_ip,server_port FROM modlr.servers WHERE servers.client_id='%s' AND servers.server_id='%s';";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session('client_id'));
	$db->Parameters($server_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		
		$service = "server.service";
		$json = "{\"tasks\": [{\"task\": \"server.save\"},{\"task\": \"server.shutdown\"}]}";
		api_short($service,$json);
		
		ModlrStartDebug($server_id);
		$msgType = "alert alert-success fade in";
		$msg = "<strong>Oh joy!</strong> You server has been restarted. It may take some time to become active depending on how big your models are.";
	
	}
} else if( $action == "activate" ) {
	$server_id = querystring("server_id");
	
	userActivateServerCustomer($server_id, session('uid'));
	
	header("Location: /home/");
}

include_once("lib/header.php");
?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	
		<title>MODLR » Manage Account</title>
		<style>
.dataTables_wrapper {
	position: relative;
	clear: both;
	zoom: 1;
}
.dataTables_length {
	width: 40%;
	float: left;
}
th.header-email {
	width: 250px;
}
th.header-name {
	width: 150px;
}
th.header-actions {
	width: 60px;
}
th.header-phone {
	width: 100px;
}
th.header-login {
	width: 130px;
}
		</style>
<?
include_once("lib/body_start.php");

updateUtilisation(session('client_id'));
?>

        <div class="row">
            
            <div class="col-md-12">
<?
if( $msgType != "" ) { 
	echo '<div class="'.$msgType.'">
				<button data-dismiss="alert" class="close close-sm" type="button">
					<i class="fa fa-times"></i>
				</button>
				'.$msg.'
			</div>';
}
?>          
                <section class="panel">
                    <header class="panel-heading tab-bg-dark-navy-blue">
                        <ul class="nav nav-tabs nav-justified ">
                            <li class="active tabHeading" data-index='0' id='Haccount'>
                                <a data-toggle="tab" href="#account">
                                    Account Users
                                </a>
                            </li>
                            <li class='tabHeading' data-index='1' id='Hservers'>
                                <a data-toggle="tab" href="#servers">
                                    Application Servers
                                </a>
                            </li>
                            <li class='tabHeading' data-index='2' id='Hbilling'>
                                <a data-toggle="tab" href="#billing">
                                    Billing Information
                                </a>
                            </li>
                            <li class='tabHeading' data-index='3' id='Hsettings'>
                                <a data-toggle="tab" href="#settings">
                                    Settings
                                </a>
                            </li>
                        </ul>
                    </header>
                    <div class="panel-body">
                        <div class="tab-content tasi-tab">
                            <div id="account" class="tab-pane active">
                                <div class="row">
                                    <div class="col-md-12">

                                        
                                        <div class="prf-contacts">
                                            <h2> <span><i class="fa fa-male"></i></span> Modellers - Named Users</h2>
                                            <div class="location-info">
												Named Users with the ability to create analytical modelling environments.
                                            </div>
                                        </div><br/><br/>
                                        
                                        <table  class="display table table-bordered table-striped" id="modeller-table">
										<thead>
										<tr>
											<th class='header-email'>Email</th>
											<th class='header-name'>Name</th>
											<th class="hidden-phone header-phone">Phone</th>
											<th class="hidden-phone header-login">Last Access</th>
											<th class='header-actions' width='32'></th>
										</tr>
										</thead>
										<tbody>
<?
	$sql = "SELECT users.id,users.email,users.name,users.phone,users.login_date,users.joined_date,users_clients.role FROM modlr.users_clients LEFT JOIN users ON users.id=users_clients.user_id WHERE  users_clients.role='MODELLER' AND users_clients.client_id='%s' AND users.email IS NOT NULL AND users.account_disabled = 0 ;";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session('client_id'));

	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$count = 0;
		while( $r = $db->Rows() ) {

			$email = $r['email'];
			$name = $r['name'];
			$phone = $r['phone'];
			$userid = $r['id'];
			
			$login_date = $r['login_date'];
			$joined_date = $r['joined_date'];
			$role = $r['role'];
			
			$date_login = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $login_date)));
			$dateStr = time2str($date_login);
			if( $date_login == "0000-00-00 00:00:00" ) {
				$dateStr = "Never";
			}
			
			$classes = "";
			if( $count / 2 == intval($count/2) ) 
				$classes = "odd";
			
			echo "<tr class='".$classes."'>";
				echo "<td>".$email."</td>";
				echo "<td>".$name."</td>";
				echo "<td class='center hidden-phone'>".$phone."</td>";
				echo "<td class='center hidden-phone'>".$dateStr."</td>";
				echo "<td>";
				echo '&nbsp;<button type="button" class="btn btn-info btn-xs " title="Remove User" onclick="remove_user(\''.$userid.'\', \''.$email.'\')"><i class="fa  fa-times-circle"></i></button>';
				echo "</td>";
			echo "</tr>";
			
			$count++;
			
		}
	}
	
	
?>
										</tbody>
										</table>


                                        
                                        <div class="prf-contacts">
                                            <h2> <span><i class="fa fa-male"></i></span> Collaborators - Named Users</h2>
                                            <div class="location-info">
												Named Users with the ability to collaborate in planning environments.
                                            </div>
                                        </div><br/><br/>
<?
$sql = "SELECT users.id,users.email,users.name,users.phone,users.password_temp,users.login_date,users.joined_date,users_clients.role FROM modlr.users_clients LEFT JOIN users ON users.id=users_clients.user_id WHERE  users_clients.role='PLANNER' AND users_clients.client_id='%s' AND users.name IS NOT NULL AND users.account_disabled = 0  ";
$db = new db_helper();
$db->CommandText($sql);
$db->Parameters(session('client_id'));

$db->Execute();
if ($db->Rows_Count() == 0) {
	
	echo "<div class='activity-desk'><h2 class='red'>You do not have anyone to Collaborate with yet.</h2></div>";
	echo "<!-- client id: ".session('client_id')." -->";
	
} else {
?>
                                        
                                        <table  class="display table table-bordered table-striped" id="collaborators-table">
										<thead>
										<tr>
											<th class='header-email'>Email</th>
											<th class='header-name'>Name</th>
											<th class="hidden-phone header-phone">Initial Password</th>
											<th class="hidden-phone header-login">Last Access</th>
											<th class='header-actions' width='32'></th>
										</tr>
										</thead>
										<tbody>
<?
$count = 0;
while( $r = $db->Rows() ) {

	$email = $r['email'];
	$name = $r['name'];
	$phone = $r['phone'];
	$userid = $r['id'];
	
	$password_temp = $r['password_temp'];
	
	$login_date = $r['login_date'];
	$joined_date = $r['joined_date'];
	$role = $r['role'];
	
	$date_login = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $login_date)));
	$dateStr = time2str($date_login);
	
	if( $login_date == "0000-00-00 00:00:00" ) {
		$dateStr = "Never";
	}
	
	$classes = "";
	if( $count / 2 == intval($count/2) ) 
		$classes = "odd";
	
	echo "<tr class='".$classes."'>";
		echo "<td>".$email."</td>";
		echo "<td>".$name."</td>";
		echo "<td class='center hidden-phone'>";
		echo '&nbsp;<button type="button" class="btn btn-info btn-xs " title="Remove User" onclick="reveal_passcode(this,\''.$password_temp.'\')"><i class="fa  fa-search"></i></button>';
		echo "</td>";
		echo "<td class='center hidden-phone'>".$dateStr."</td>";
		echo "<td>";
		echo '&nbsp;<button type="button" class="btn btn-info btn-xs " title="Remove User" onclick="remove_user(\''.$userid.'\', \''.$email.'\')"><i class="fa  fa-times-circle"></i></button>';
		echo "</td>";
	echo "</tr>";
	
	$count++;
	
}
?>
										</tbody>
										<tfoot>
										<tr>
											<th>Email</th>
											<th>Name</th>
											<th class="hidden-phone">Phone</th>
											<th class="hidden-phone">Last Access</th>
											<th width='32'></th>
										</tr>
										</tfoot>
										</table>
<?
}
?>
                                        
                                    </div>
                                    
                                </div>
                            </div>
                            <div id="servers" class="tab-pane ">
                                <div class="row">
                                    <div class="col-md-12">
                                        

                                        <div class="prf-contacts">
                                            <h2> <span><i class="fa fa-cloud"></i></span> Application Servers</h2>
                                            <div class="location-info">
												Analytical modelling environments running the MODLR Engine.
                                            </div>
                                        </div><br/><br/>
<?
$sql = "SELECT clients.client_name ,server_id,server_codename,server_memory,server_date_added,server_type,server_region,server_ip,server_port,server_version FROM modlr.servers LEFT JOIN modlr.clients ON clients.client_id=servers.client_id  WHERE servers.client_id='%s' AND server_is_deleted=0 ORDER BY clients.client_name, server_codename ASC;";
$db = new db_helper();
$db->CommandText($sql);
$db->Parameters(session('client_id'));

$action = "";

$db->Execute();
if ($db->Rows_Count() == 0) {
	echo "<div class='activity-desk'><h2 class='red'>Sorry, we could not find any application servers under your account.</h2></div>";
} else {
?>
                                        <table  class="display table table-bordered table-striped" id="modeller-table">
										<thead>
										<tr>
											<th class='header-email'>Identifier</th>
											<th class='header-name'>Account</th>
											<th class='header-name'>Status</th>
											<th class='header-name'>IP</th>
											<th class='header-name' style='max-width:100px;'>Memory</th>
											<th class='header-name'>Setup Date</th>
											<th class='header-name'>Location</th>
											<th class='header-name' style='max-width:100px;'>Build</th>
											<th class='header-actions' style='width:100px;'>Actions</th>
										</tr>
										</thead>
										<tbody>
<?
		$count = 0;
		while( $r = $db->Rows() ) {

		
			$client_name = $r['client_name'];
		
			$server_id = $r['server_id'];
			$server_codename = $r['server_codename'];
			$server_memory = $r['server_memory'];
			$server_date_added = $r['server_date_added'];
			$server_type = $r['server_type'];
			$server_region = $r['server_region'];
			
			$server_ip = $r['server_ip'];
			$server_port = $r['server_port'];
			
			$server_version = $r['server_version'];
			
			$action = '<button type="button" class="btn btn-info btn-xs " title="Set as Active Server" onclick="window.location=\'/manage/?action=activate&server_id='.$server_id.'\';"><i class="fa fa-check-square"></i></button>';
			if( intval(session('active_server_id')) == $server_id ) {
				$action = '<button type="button" class="btn btn-primary btn-xs ">Active</button>';
			}
			//if( $server_type == "DROPLET" ) {
				$action .= '&nbsp;<button type="button" class="btn btn-info btn-xs " title="Restart Server" onclick="restart_server(\''.$server_id.'\', \''.$server_codename.'\');"><i class="fa fa-rotate-right"></i></button>';
				//$action .= '&nbsp;<button type="button" class="btn btn-info btn-xs " title="Delete Server" onclick="remove_server(\''.$server_id.'\', \''.$server_codename.'\');"><i class="fa  fa-times-circle"></i></button>';
			//}
			
			$server_date_added = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $server_date_added)));
			$dateStr = time2str($server_date_added);
			
			$minutes_since = round(abs( strtotime(str_replace('-', '/', $server_date_added)) - time()) / 60,0);
			
			$classes = "";
			if( $count / 2 == intval($count/2) ) 
				$classes = "odd";
			
			if( $server_type == "SELF-MANAGED" ) {
				//$server_type
			} else if( $server_type == "DROPLET" ) {
				
			}
			
			$server_status = "";
			if( $server_ip == "" || is_null($server_ip) ) {
				$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-warning btn-xs">Waiting for IP Address</button>';
				$action = "";
			} else {
					if( $minutes_since < 10 ) {
						if( server_is_online_on_port($server_ip,$server_port) ) {
							$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs">Setup</button>';
						} else {
							$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-warning btn-xs">Activating</button>';
							$action = "";
						}
					} else {
						$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs">Setup</button>';
						
						//$action = "";
					}
			}
			
			echo "<tr class='".$classes."'>";
				echo "<td>".$server_codename."</td>";
				echo "<td>".$client_name."</td>";
				echo "<td>".$server_status."</td>";
				echo "<td>".$server_ip."</td>";
				echo "<td>".$server_memory." GB</td>";
				echo "<td>".$dateStr."</td>";
				echo "<td>".$server_region."</td>";
				echo "<td align='center'>".$server_version."</td>";
				
				
				echo "<td align='center'>".$action."</td>";
			echo "</tr>";
			
			$count++;
			
		}
?>
										</tbody>
										</table>
<?
}

$sql = "SELECT clients.client_name ,server_id,server_codename,server_memory,server_date_added,server_type,server_region,server_ip,server_port,server_version FROM modlr.servers LEFT JOIN modlr.clients ON clients.client_id=servers.client_id WHERE  servers.client_id<>'%s' AND servers.client_id IN (SELECT client_id FROM modlr.users_clients WHERE user_id='%s') AND  server_is_deleted=0 ORDER BY clients.client_name, server_codename ASC;";
$db = new db_helper();
$db->CommandText($sql);
$db->Parameters(session('client_id'));
$db->Parameters(session('uid'));
$action = "";

$db->Execute();
if ($db->Rows_Count() == 0) {
	
} else {
?>
										<b>Customer Accounts:</b>
                                        <table  class="display table table-bordered table-striped" id="modeller-table">
										<thead>
										<tr>
											<th class='header-email'>Identifier</th>
											<th class='header-name'>Account</th>
											<th class='header-name'>Status</th>
											<th class='header-name'>IP</th>
											<th class='header-name' style='max-width:100px;'>Memory</th>
											<th class='header-name'>Setup Date</th>
											<th class='header-name'>Location</th>
											<th class='header-name' style='max-width:100px;'>Build</th>
											<th class='header-actions' style='width:100px;'>Actions</th>
										</tr>
										</thead>
										<tbody>
<?
		$count = 0;
		while( $r = $db->Rows() ) {

		
			$client_name = $r['client_name'];
		
			$server_id = $r['server_id'];
			$server_codename = $r['server_codename'];
			$server_memory = $r['server_memory'];
			$server_date_added = $r['server_date_added'];
			$server_type = $r['server_type'];
			$server_region = $r['server_region'];
			
			$server_ip = $r['server_ip'];
			$server_port = $r['server_port'];
			
			$server_version = $r['server_version'];
			
			$action = '<button type="button" class="btn btn-info btn-xs " title="Set as Active Server" onclick="window.location=\'/manage/?action=activate&server_id='.$server_id.'\';"><i class="fa fa-check-square"></i></button>';
			if( intval(session('active_server_id')) == $server_id ) {
				$action = '<button type="button" class="btn btn-primary btn-xs ">Active</button>';
			}
			//if( $server_type == "DROPLET" ) {
				$action .= '&nbsp;<button type="button" class="btn btn-info btn-xs " title="Restart Server" onclick="restart_server(\''.$server_id.'\', \''.$server_codename.'\');"><i class="fa fa-rotate-right"></i></button>';
				//$action .= '&nbsp;<button type="button" class="btn btn-info btn-xs " title="Delete Server" onclick="remove_server(\''.$server_id.'\', \''.$server_codename.'\');"><i class="fa  fa-times-circle"></i></button>';
			//}
			
			$server_date_added = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $server_date_added)));
			$dateStr = time2str($server_date_added);
			
			$minutes_since = round(abs( strtotime(str_replace('-', '/', $server_date_added)) - time()) / 60,0);
			
			$classes = "";
			if( $count / 2 == intval($count/2) ) 
				$classes = "odd";
			
			if( $server_type == "SELF-MANAGED" ) {
				//$server_type
			} else if( $server_type == "DROPLET" ) {
				
			}
			
			$server_status = "";
			if( $server_ip == "" || is_null($server_ip) ) {
				$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-warning btn-xs">Waiting for IP Address</button>';
				$action = "";
			} else {
				if( $minutes_since < 10 ) {
					if( server_is_online_on_port($server_ip,$server_port) ) {
						$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs">Setup</button>';
					} else {
						$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-warning btn-xs">Activating</button>';
						$action = "";
					}
				} else {
					$server_status .= '&nbsp;&nbsp;<button type="button" class="btn btn-success btn-xs">Setup</button>';
					
					//$action = "";
				}
			}
			
			echo "<tr class='".$classes."'>";
			
				echo "<td>".$server_codename."</td>";
				echo "<td>".$client_name."</td>";
				
				echo "<td>".$server_status."</td>";
				echo "<td>".$server_ip."</td>";
				echo "<td>".$server_memory." GB</td>";
				echo "<td>".$dateStr."</td>";
				echo "<td>".$server_region."</td>";
				echo "<td align='center'>".$server_version."</td>";
				echo "<td align='center'>".$action."</td>";
			echo "</tr>";
			
			$count++;
			
		}
?>
										</tbody>
										</table>
<?
}

?>
										<hr/>

										<div class="row">
											<?
											 serverSetupCol();
											?>
											<div class="col-md-6">
												<div class="prf-contacts">
													<h2> <span><i class="fa fa-money"></i></span> Pricing Information</h2>
													<div class="location-info" >
														
														<?
														outputServerPricing();
														?>
                            
															
													</div>
												</div>
									
											</div>
										</div>



                                        
                                        
                                    </div>
                                </div>
                            </div>
                            <div id="billing" class="tab-pane ">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="prf-contacts">
                                            <h2> <span><i class="fa fa-credit-card"></i></span> Account Billing Information</h2>
                                            <div class="location-info">
                                                
												<b>How your monthly invoice is calculated:</b><br/>
												The billing engine reviews your utilisation of the MODLR environment each day in the following areas:
												<ol>
													<li>Modellers - How many people show in the 'Modellers' table under the 'Account Users' Tab.</li>
													<li>Collaborators - How many people show in the 'Collaborators' table under the 'Account Users' Tab.</li>
													<li>Application Servers - How many application servers you are running of the various sizes.</li>
												</ol>    
											
											    <br/>
												<b>My monthly bill is for $0. Is this correct?</b><br/>
												The entry level MODLR Account provides a 1GB Application Server with a single Modeller for free. So if you have not yet requested contribution from anyone else this is likely the case.   
												<br/><br/>
                                            </div>
                                            <h2> <span><i class="fa fa-money"></i></span> Pricing Information</h2>
                                            <div class="location-info">
                                            		
                                            	<b>User Licences:</b><br/>
												<ol>
													<li>Modellers - Your first Modeller is free. Subsequent Modellers cost $<? echo getProductPrice('units_modeller');?>AUD/Month</li>
													<li>Collaborators - Collaborators cost $<? echo getProductPrice('units_planner');?>AUD/Month</li>
												</ol>    
												
                                            	<b>Application Server Instance and Licences:</b><br/>
												<ol>
													<li>See the Application Servers Tab.</li> <!-- 
													<li>2GB Application Server - Entry Level - $<? echo getProductPrice('units_2gb');?> per month</li>
													<li>4GB Application Server - Entry Level - $<? echo getProductPrice('units_4gb');?> per month</li>
													<li>8GB Application Server - Entry Level - $<? echo getProductPrice('units_8gb');?> per month</li>
													<li>16GB Application Server - Entry Level - $<? echo getProductPrice('units_16gb');?> per month</li> -->
												</ol>    
												
												
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="prf-contacts">
											<h2> <span><i class="fa fa-files-o"></i></span> My Invoices</h2>
											<div class="location-info" style="height: 400px;overflow-y: scroll;">
												
<?


$db = new db_helper();
$db->CommandText("SELECT invoice_id,billing_period,date_added,amount_total,invoice_paid,DATEDIFF(CURDATE(),date_added) as daysSinceBilled FROM clients_invoices WHERE clients_invoices.client_id='%s' ORDER BY billing_period DESC");
$db->Parameters(session("client_id"));
$db->Execute();
if ($db->Rows_Count() > 0) {
	while( $r = $db->Rows() ) {
		
		$invoice_id = $r['invoice_id'];
		$billing_period = $r['billing_period'];
		$amount_total = $r['amount_total'];
		$invoice_paid = $r['invoice_paid'];
		$date_added = $r['date_added'];
		$daysSinceBilled = $r['daysSinceBilled'];
		
		$time_str = date("Y-m-d", strtotime(str_replace('-', '/', $billing_period))  );
		$time_generated_str = time2str( strtotime(str_replace('-', '/', $date_added))  ); 
		
		$amount_total_str = number_format ( $amount_total , 2 );
		
		$alert_type = "alert-info";
		if( $invoice_paid == 1 ) {
			$alert_type = "alert-success";
		} else {
			if( $daysSinceBilled > 20 ) {
				$alert_type = "alert-warning";
			}
			if( $daysSinceBilled > 30 ) {
				$alert_type = "alert-danger";
			}
		}
		
		$paidStr = "";
		if( $invoice_paid == 1 ) { 
			$paidStr = " <b>- PAID</b>";
		}
		
		echo '<div class="alert '.$alert_type.' clearfix">
                <span class="alert-icon"><i class="fa fa-file-o"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender"><span><a href="/invoice/?id='.$invoice_id.'">INV'.$invoice_id.$paidStr.'</a></span></li>
                        <li class="pull-right notification-time">generated: '.$time_generated_str.'</li>
                    </ul>
                    <p style="padding-bottom:0px;">
                    	<b>Invoice for services in the month Beginning: '.$time_str.'</b><br/>
                        To guarantee continuation of service please pay your invoices within 30 days.
                    </p>
                </div>
            </div>';
		
		//echo "<li>Month Beginning: ".$time_str." (INV".$invoice_id.") for $".$amount_total_str." AUD. </li>";
		
	}
} else {
	echo "<p>You presently do not have any invoices. </p>";
}
?>
												
											</div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            <div id="settings" class="tab-pane ">
                                
                                
                                <div class="row">
                                	<div class="col-md-6">
                                        <div class="prf-contacts">
													<h2> <span><i class="fa fa-plus-circle"></i></span> Switch Active Accounts</h2>
													<div class="location-info">
													<?
													$db = new db_helper();
													$db->CommandText("SELECT clients.client_id,clients.client_name FROM users_clients LEFT JOIN clients ON clients.client_id = users_clients.client_id WHERE user_id='%s' AND  role='MODELLER' ORDER BY client_name DESC");
													$db->Parameters(session('uid'));
													$db->Execute();
													if ($db->Rows_Count() > 0) {
													?>
													<p>You can change your active account below. This will allow you to manage users from other accounts.</p>
												
													<form class="form-horizontal" action="/manage/" method='post'>
														<input type="hidden" id='action' name='action' value='active_client_set'>
														<div class="form-group">
															<label class="col-lg-6 control-label">Client:</label>
															<div class="col-sm-11">
																<select id='updated_client_id' name='updated_client_id' class="form-control">
																	<?
	while( $r = $db->Rows() ) {
		if( $r['client_id'] == session("client_id") ) {
			echo "<option value='".$r['client_id']."' selected>".$r['client_name']."</option>";
		} else {
			echo "<option value='".$r['client_id']."'>".$r['client_name']."</option>";
		}
	}
																	?>
																</select>
															</div>
														</div>
														<button type="submit" class="btn btn-info">Set Account</button>
													</form>
													
													<?
													} else {
													?>
													<p>Note: You have not been added to any other accounts. If you are a consultant you can have your client accounts added so that you can manage them from your primary account.</p>
													<?
													}
													?>
													</div>
                                            
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="prf-contacts">
                                            <h2> <span><i class="fa fa-cloud-upload"></i></span> Datastore Settings</h2>
                                            <div class="location-info">
                                                
												<b>Accessing the Datastore Remotely</b><br/>
												You can access the datastore to push information into the environment to automate a process.
												
<?

$db = new db_helper();
$db->CommandText("SELECT username, password, date_added FROM servers_accounts WHERE server_id='%s' ORDER BY username DESC");
$db->Parameters(session('active_server_id'));
$db->Execute();
if ($db->Rows_Count() > 0) {
	echo "<table class='table' width='100%'><tr><td>Username</td><td>Password</td><td>Data Added</td><td>Actions</td></tr>";
	
	while( $r = $db->Rows() ) {
		
		$username = $r['username'];
		$password = $r['password'];
		$date_added = $r['date_added'];
		
		$time_generated_str = time2str( strtotime(str_replace('-', '/', $date_added))  ); 
		
		
		echo '<tr><td>'.$username.'</td><td>'.$password.'</td><td>'.$time_generated_str.'</td><td></td></tr>';
	}
	echo "</table>";
} else {
	echo "<br/><br/><center>No accounts exist for direct connectivity to the datastore.</center>";
}

?>
											
											    
											    
                                            </div>
                                            
                                        </div>
                                        
										
										<div class="prf-contacts">
											<h2> <span><i class="fa fa-plus-circle"></i></span> Add Datastore User</h2>
											<div class="location-info">
											<?
											$servers = userServers();
											if( count($servers) > 0 ) { 
											?>
											
									
													<p>You can setup a new user on the datastore below.</p>
												
													<form class="form-horizontal" action="/manage/" method='post'>
														<input type="hidden" id='action' name='action' value='datastore_add_account'>
														<div class="form-group">
															<label class="col-lg-6 control-label">Username:</label>
															<div class="col-sm-11">
																<input type="text" id='username' name='username' class="form-control">
															</div>
														</div>
														<p>Note: no spaces or underscores are allowed. Sample Names: admin, externaluser</p>
														<button type="submit" class="btn btn-info">Add Account</button>
													</form>
												
												
													<br><br>
												
											
											<?
											} else {
											?>
											<p>Note: these settings are server specific. You will need to setup a server before you can configure options here.</p>
											<?
											}
											?>
										</div>
										
										</div>
                                    </div>
                                    
                                    
                                </div>
                                

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>



<?
include_once("lib/body_end.php");
?>
<script type="text/javascript" language="javascript" src="/js/advanced-datatable/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="/js/data-tables/DT_bootstrap.js"></script>
<script type="text/javascript" src="/js/service/manage.js"></script>
<script type="text/javascript">
function reveal_passcode(obj, pass) {
	obj.parentNode.innerHTML = pass;
}
</script>
<?
include_once("lib/footer.php");
?>
