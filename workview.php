<?
include_once("lib/lib.php");
include_once("lib/header.php");
?>
		<title><? echo C_APP_NAME;?> <? echo C_APP_VERSION_FORMAT;?> - API Sandbox</title>
<?
include_once("lib/body_start.php");

$id = querystring("id");

$url = "";
$driver = "";
$username = "";
$password = "";

$mode = "add";

if( $id != "" ) {
	echo "<!-- datasource id provided -->\r\n";

	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"datasource.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";

	$results = api_short(SERVICE_DATASOURCE, $json);
	
	
	
	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- datasource id found in server -->\r\n";
		
		$contents = $results->results[0]->datasource;
	
		$name = $contents->name;
		$url = $contents->url;
		$driver = $contents->driver;
		$username = $contents->username;
		//$password = $contents->password;
		
		$mode = "edit";
		
		
		if( form("delete") == "ok" ) {
			
			$json = "{\"tasks\": [";
			$json .= "{\"task\": \"datasource.delete\", \"id\":\"" . $id . "\"}";
			$json .= "]}";

			$results = api_short(SERVICE_DATASOURCE, $json);
			
			redirectToPage ("/home/");
			die();
		}
		
		
	} else {
		//datasource not found
	}
	
}





?>

				<div class="row">
					
					<!-- Basic forms -->
					<div class="col-lg-6">
						<div class="module">
							<div class="module-header"><h4>Add a Datasource</h4></div>
							<div class="module-content">

								<form action="#" class="form-horizontal">

									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Name:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="name" value="<? echo $name;?>" placeholder="New Datasource" />
										</div>
									</div>
									
									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">JDBC URL:</label>
										<div class="col-lg-10">
											<input type="id" class="form-control" id="url" value="<? echo $url;?>" placeholder="jdbc:mysql://localhost:3306/schema" />
										</div>
									</div>
									
									<div class="form-group">
										<label for="select1" class="col-lg-2 control-label">Driver</label>
										<div class="col-lg-10">
											<select class="form-control" id="driver">
<?
	$drivers = array("com.mysql.jdbc.Driver",
				"COM.ibm.db2.jdbc.app.DB2Driver",
				"weblogic.jdbc.mssqlserver4.Driver",
				"COM.cloudscape.core.JDBCDriver",
				"com.informix.jdbc.IfxDriver",
				"org.postgresql.Driver",
				"com.microsoft.sqlserver.jdbc.SQLServerDriver");
				
	sort($drivers , SORT_STRING);
	
	for($i=0;$i<count($drivers);$i++) {
		$selected = "";
		if( strToLower($drivers[$i]) == strToLower($driver) ) {
			$selected = " selected";
		}
		echo "<option value='".$drivers[$i]."'".$selected.">".$drivers[$i]."</option>";
	}

?>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label for="input2" class="col-lg-2 control-label">User:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" value="<? echo $username;?>" id="username"  placeholder="root"/>
										</div>
									</div>

									<div class="form-group">
										<label for="input2" class="col-lg-2 control-label">Password:</label>
										<div class="col-lg-10">
											<input type="password" class="form-control" value="<? echo $password;?>" id="password"  placeholder=""/>
										</div>
									</div>



									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											<span class="btn btn-primary" onclick="test();">Test</span>
											<span class="btn btn-default" onclick="save();" id='btnSave'>Save</span>
											<span class="btn btn-primary" onclick="window.location='/home/';">Cancel</span>
											
											
											<? if( $mode == "edit" ) { ?>
											<span class="btn btn-danger" onclick="func_delete();" id='btnDelete'>Delete</span>
											<? } ?>
										</div>
									</div>


									<div class="form-group" id='testBox' style='display:none;'>
										<label for="input2" class="col-lg-2 control-label">Results:</label>
										<div class="col-lg-10" id='testResult'>
											<p></p>
										</div>
									</div>


								</form>

							</div>
						</div>
					</div>
					<!-- /Basic forms -->

					<div class="col-lg-6">
						<div class="module">
							<div class="module-header"><h4>Overview</h4></div>
							<div class="module-content">

								<h4>Datasources</h4>
								<p>Datasources refer to connections to databases accessible by the <? echo C_APP_NAME;?>
								application server. From here you can browse the available tables within the selected schema.</p>
								
								<p class="text-muted">Note: to manage how these tables are used you will need to access these through the new model developer interface.</p>
								
								<? if( $mode == "edit" ) { ?>
								<p class="text-muted">Note: The password field is omitted intentionally for security reasons, to change this datasource you will need to re-enter the datasource password.</p>
								<? } ?>
							</div>
						</div>
					</div>

					
					
				</div>
				
				<form action='/datasource/?id=<? echo $id;?>' method='post' id='formDelete'>
					<input type='hidden' name='delete' id='delete' value='ok'/>
				</form>
				
<?
include_once("lib/body_end.php");
?>
<script>

var tested = false;
var mode = "<? echo $mode;?>";
var serviceName = "<? echo SERVICE_DATASOURCE;?>";

function func_delete() {
	var r=confirm("Are you sure you want to delete this datasource?");
	if (r==true)
	{
	  	document.getElementById('formDelete').submit();
	}
}

function save() {
	if( tested ) {
	
		var url =  document.getElementById('url').value;
		var name =  document.getElementById('name').value;
		var lst = document.getElementById('driver');
		var driver = escape( lst.options[lst.selectedIndex].value);
		var username = escape(document.getElementById('username').value);
		var password = escape(document.getElementById('password').value);
	
		var task = "datasource.create";
		if(mode == "edit" )
			task = "datasource.update";
			
		var datasourceId = "<? echo $id;?>";
		
		var tasks = {"tasks": [
			{"task": task, "name": name, "username": username, "password": password, "url": url , "driver": driver , "id": datasourceId }
		]};
	
		if( name.length > 2 ) {
			query(serviceName,tasks,save_callback);
		} else {
			document.getElementById('testResult').innerHTML = '<b>Save failed, the specified name was too short.</b><br/>' + str;
		}
	
		
	} else {
		//do nothing
	}
}

function save_callback(data) {
	var results = JSON.parse(data);
	
	if( parseInt(results['results'][0]['result']) == 1 ) {
		
		if(mode == "edit" ) {
			document.getElementById('testResult').innerHTML = '<b>Datasource Updated.</b>';
		} else {
			document.getElementById('testResult').innerHTML = '<b>Datasource Created.</b>';
		}
			
	} else {
		document.getElementById('testResult').innerHTML = '<b>' + results['results'][0]['message'] + '</b>';
	}
}

function test() {

	
	
	document.getElementById('testBox').style.display = 'block';	
	document.getElementById('testResult').innerHTML = 'Testing...';
	
	var url =  document.getElementById('url').value;
	var lst = document.getElementById('driver');
	var driver = escape( lst.options[lst.selectedIndex].value);
	var username = escape(document.getElementById('username').value);
	var password = escape(document.getElementById('password').value);
	
	var tasks = {"tasks": [
        {"task": "datasource.test", "username": username, "password": password, "url": url , "driver": driver }
    ]};
	
	query(serviceName,tasks,test_callback);
}

function test_callback(data) {
	var results = JSON.parse(data);
	
	var str = "";
	
	var logs = results['results'][0]['log'];
	for(var i=0;i<logs.length;i++) {
		str += logs[i]['message']  + "<br/>"; 
	}
	
	if( parseInt(results['results'][0]['result']) == 1 ) {
		
		document.getElementById('testResult').innerHTML = '<b>Connection Successful:</b><br/>' + str;
		
		tested = true;
		document.getElementById('btnSave').setAttribute('class','btn btn-primary');
		
	} else {
		
		
		document.getElementById('testResult').innerHTML = '<b>Connection Failed:</b><br/>' + str;
	}
}

</script>
<?
include_once("lib/footer.php");
?>
