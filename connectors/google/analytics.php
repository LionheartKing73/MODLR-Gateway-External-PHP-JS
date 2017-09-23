<?
include_once("../../lib/lib.php");
include_once("../../lib/header.php");
?>
    	<title>MODLR » Google Analytics Connector</title>
<?
include_once("../../lib/body_start.php");

$id = querystring("id");

$app = "";
$secrets = "";

$mode = "add";
$name = "";


?>

				<div class="row">
					
					
					<div class="col-md-6">
						<div class="panel">
							<div class="panel-heading">
								Add a Google Analytics Datasource
							  <span class="tools pull-right">
									<a class="fa fa-chevron-down" href="javascript:;"></a>
								</span>
							</div>
							<div class="panel-body">
					
								<form action="#" class="form-horizontal">

									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Name:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="name" value="<? echo $name;?>" placeholder="New Datasource" />
										</div>
									</div>
									
									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Service Account Email:</label>
										<div class="col-lg-10">
											<input type="id" class="form-control" id="app" value="<? echo $app;?>" placeholder="something.long@developer.gserviceaccount.com" />
										</div>
									</div>
									
									<div class="form-group">
										<label for="input2" class="col-lg-2 control-label">P12 Key Filename:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" value="<? echo $secrets;?>" id="secrets"  placeholder="crazy long hash."/>
										</div>
									</div>


									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											<span class="btn btn-primary" onclick="test();">Test</span>
											<span class="btn btn-default" onclick="save();" id='btnSave'>Save</span>
											<span class="btn btn-primary" onclick="window.location='/home/';">Cancel</span>
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


					
					<div class="col-md-6">
						<div class="panel">
							<div class="panel-heading">
								Google Analytics as a Datasource
							  <span class="tools pull-right">
									<a class="fa fa-chevron-down" href="javascript:;"></a>
								</span>
							</div>
							<div class="panel-body">


								<h4>Datasources</h4>
								<p>Datasources refer to connections to systems which can be used in developing MODLR Processes.</p>
								
								
							</div>
						</div>
					</div>

					
					
				</div>
				
				
				
				
<?
include_once("../../lib/body_end.php");
?>
<script>

var tested = false;
var mode = "<? echo $mode;?>";
var serviceName = "<? echo SERVICE_DATASOURCE;?>";


function save() {
	if( tested ) {
	
		var app =  document.getElementById('app').value;
		var secrets = document.getElementById('secrets').value;
		var name =  document.getElementById('name').value;
	
		var task = "datasource.create";
		if(mode == "edit" )
			task = "datasource.update";
			
		var tasks = {"tasks": [
			{"task": task, "name": name,"type":"Google Analytics", "account": app, "p12": secrets }
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

	var app =  document.getElementById('app').value;
	var secrets = document.getElementById('secrets').value;
	var name =  document.getElementById('name').value;

	
	var tasks = {"tasks": [
        {"task": "datasource.test", "type":"Google Analytics", "account": app, "p12": secrets}
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
<? echo "var server_id = 0;";?>
</script>
<?
include_once("../../lib/footer.php");
?>
