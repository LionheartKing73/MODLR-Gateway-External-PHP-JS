<?
include_once("../lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$action = querystring("action");
$id = querystring("id");
$model = null;
$usersHidden = array();

if( $id != "" ) {
	
	echo "<!-- model id provided -->\r\n";

	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- model id found in server -->\r\n";
		
		$model_contents = $results->results[0]->model;
		$model = $model_contents;
		$name = $model_contents->name;
		
		if( property_exists( $model_contents, 'hide' ) ) {
			$usersHidden = $model_contents->hide;
		}
		
		$mode = "edit";
		if( form("delete") == "ok" ) {
			
			$json = "{\"tasks\": [";
			$json .= "{\"task\": \"model.delete\", \"id\":\"" . $id . "\"}";
			$json .= "]}";

			$results = api_short(SERVICE_MODEL, $json);
			
			redirectToPage ("/home/");
			die();
		}
		
		
	} else {
		//model not found
		
		echo "<!-- ".$json." -->";
		redirectToPage ("/home/");
		die();
	}
} else {
	redirectToPage ("/home/");
	die();
}

include_once("../lib/header.php");

echo "<title>MODLR » Manage » ".$name."</title>";

?>
	<style>
		.cell {
			border:0px;
			padding: 5px;
		}
		.cellHeading {
			padding: 5px;
            background-color: #FAFAFA;
            font-weight: bold;
            border: 0px solid #333;
		}
		.c {
			min-width: 10px;
			max-width: 380px;
			font-size: 12px;
			border:1px solid #EEE;
			padding:4px;
			cursor:pointer;
			text-align: right;
			/* background-color: #fff; */
		}
        
        .table {
            border-bottom: 2px solid #DDDDDD;
        }
	</style>
	<script>
<?
		echo "	var model_detail = ".json_encode($model_contents).";\r\n";
?>
	</script>
	<style type='text/css'>
		.ui-selecting { background: #6dba89 !important; }
		.ui-selected { background-color: #6dba89 !important; color:#FFF !important; }
		#displayVariableValue {word-break: break-all;}
			</style>
<?
	
	
	
include_once("../lib/body_start.php");

outputModelToolbar($id, $name);
$model_name = $name;

?>


				<div class="row">
					
					<!-- Basic forms -->
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Manage <? echo $name;?> Model
							</header>
							<div class="panel-body">

								<form action="/model/?action=manage&id=<? echo $id;?>" method='post' class="form-horizontal">
									
									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Name:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="name" value="<? echo $name;?>" placeholder="Model" />
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											Hide model from users<br/>
											<div style='margin-left:15px;'>
											
									
<?
	
	
	function idIsInArray($id, $ary) {
		for($i=0;$i<count($ary);$i++) {
			if( intval($id) == intval($ary[$i]) ) {
				return true;
			}
		}
		return false;
	}
	
	$sql = "SELECT users.name,users.id FROM modlr.users_clients LEFT JOIN users ON users.id=users_clients.user_id WHERE  users_clients.role='MODELLER' AND users_clients.client_id='%s' AND users.account_disabled=0;";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters(session('client_id'));

	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$count = 0;
		while( $r = $db->Rows() ) {
			$name = $r['name'];
			$userid = $r['id'];
			
			$strAdd = "";
			if( idIsInArray($userid,$usersHidden) ) {
				$strAdd = " checked";
			}
			
			echo '<input type="checkbox" name="user'.$userid.'" value="'.$userid.'"'.$strAdd.'/> '.$name.'<br/>';
			
			$count++;
		}
	}
?>
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											<span class="btn btn-primary" onclick="save();" id='btnSave'>Save</span>
											<span class="btn btn-warning" onclick="window.location='/model/?id=<? echo $id;?>';">Close</span>
											<span class="btn btn-danger" onclick="deleteItem('model','<? echo $id;?>','<? echo $model_name;?>');" id='btnDelete'>Delete</span>
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
						</section>
					</div>

					
					<!-- Basic forms -->
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Schedule a Process
							</header>
							<div class="panel-body" style="padding-bottom: 0px;">

								<form action="/model/manage/?action=manage&id=<? echo $id;?>" method='post' class="form-horizontal">
									
									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Process:</label>
										<div class="col-lg-10">
											<select class="form-control" id='list_process'>
	<?
	for($i=0;$i<count($model_contents->processes);$i++) {
		$process = $model_contents->processes[$i];
		echo "<option value='".$process->processid."'>".$process->name."</option>";
	}
	
	?>
											</select>
										</div>
									</div>
									
									
									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Schedule:</label>
										<div class="col-lg-10">
											<select class="form-control" id='list_schedule' onChange="scheduleChange();">
												<option value='monthly'>Run Monthly on a Day at a Time</option>
												<option value='weekly'>Run Weekly on a Day at a Time</option>
												<option value='daily'>Run Daily at a Time</option>
											</select>
										</div>
									</div>
									
									
									<div class="form-group" id='daily' style="display:none;">
										<label for="input1" class="col-lg-2 control-label">Daily Schedule:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="daily_schedule" value="" placeholder="00:00" />
										</div>
									</div>
									
									
									<div class="form-group" id='weekly' style="display:none;">
										<label for="input1" class="col-lg-2 control-label">Weekly Schedule:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="weekly_schedule" value="" placeholder="Mon at 00:00" />
										</div>
									</div>
									
									
									<div class="form-group" id='monthly'>
										<label for="input1" class="col-lg-2 control-label">Monthly Schedule:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="monthly_schedule" value="" placeholder="1st at 00:00" />
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											<span class="btn btn-primary" onclick="scheduleAdd();" id='btnSave'>Add Schedule</span>
										</div>
									</div>

									<div class="form-group" style="margin-bottom: 0px;">
										<div class="">
											<div>
<?
if( count($model_contents->schedules) == 0 ) {
	echo "<div style='padding:10px;'>This model does not have any scheduled processes.</div>";
} else {
?>				
	<table class='table' style="margin-bottom: 0px;">
        <tbody>
            <tr>
                <td class='cellHeading'>Pattern</td>
                <td class='cellHeading'>Process</td>
                <td class='cellHeading'>Action</td>
                </tr>
										
<?
	for($i=0;$i<count($model_contents->schedules);$i++) {
		$var = $model_contents->schedules[$i];
		
		$pro = null;
		for($k=0;$k<count($model_contents->processes);$k++) {
			$process = $model_contents->processes[$k];
			if( $process->processid == $var->processid ) {
				$pro = $process;
				break;
			}
		}
		
		echo "<tr><td class = 'cell'>". $var->pattern . "</td><td class = 'cell'>".$pro->name."</td><td class = 'cell'><button type='button' class='btn btn-info btn-xs' style='text-align:right;' title='Delete Schedule' onclick=\"delete_schedule('".$var->scheduleid."');\"><i class='fa fa-times'></i></button></td></tr>";
	}
	
?>
        </tbody>
	</table>
<?
}
?>	
<br/>
	
											</div>
										</div>
									</div>
									
									
								</form>
								
							</div>
							
							
						</section>
					</div>

					
					
					<!-- Basic forms -->
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Model Variables
							</header>
							<div class="panel-body" style="padding-top:0px;">
								<form action="/model/?action=manage&id=<? echo $id;?>" method='post' class="form-horizontal">
									
									<div class="form-group" style=''>
										<div class="">
											
											
	<table class='table'>
	<thead>
	<tr>
	<td class='cellHeading' style='min-width:80px;'>Name</td>
	<td class='cellHeading' style='min-width:140px;'>Value</td>
	<td class='cellHeading' style='min-width:32px;max-width:50px;text-align:right;'>Actions</td>
	</tr>


										
<?
	for($i=0;$i<count($model_contents->variables);$i++) {
		$var = $model_contents->variables[$i];
		echo "<tr><td class = 'cell'>". $var->key . "</td>";
		if (strlen($var->value) < 100){
			echo "<td class = 'cell'>".$var->value."</td>";
		} else {
			echo "<td class = 'cell'><button type = 'button' onclick = 'view_variable_value(\"".$var->key."\", \"".stripslashes($var->value)."\");' class = 'btn btn-info btn-xs'>Show value</button></td>";
		}

		echo "<td  style='text-align:right;'><button type='button' class='btn btn-info btn-xs' title='Update Variable' onclick=\"update_variable('".$var->key."', '".$var->value."');\"><i class='fa fa-pencil'></i></button>&nbsp;<button type='button' class='btn btn-info btn-xs' title='Delete Variable' onclick=\"delete_variable('".$var->key."');\"><i class='fa fa-times'></i></button></td></tr>";
	}
	
?>
	</table>
	<br/>
	<div style='margin-left:18px;'>Create / Update Variable: &nbsp;
	<input type='text' placeholder="Variable Name" class="form-control" id='txtVariable' style='width:200px;' value=''/>
    <table style='margin-top: 10px;margin-bottom: 5px;'>
        <tr>
            <td>
                <input type='text' style='width:200px;' class="form-control" placeholder="Value" id='txtValue' value=''/>
            </td>
            <td>
                &nbsp;or upload file: 
                <label class="btn btn-info btn-sm">Choose file	
                    <input type="file" class = "hidden" name="variable_image" id = "variable_image" onchange="variable_image_encode(this);"><span class = "glyphicon glyphicon-open-file"/>
                </label>
            </td>
        </tr>
    </table>
	<span class="btn btn-primary btn-sm" style = 'margin-top:5px;' onclick="btnSaveVariable();" id='btnSave'>Save Variable</span></div>
											
										</div>
									</div>
					
								</form>
							</div>
						</section>
					</div>
					
					
					<!-- Basic forms -->
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Custom and Conditional Formatting Styles
							</header>
							<div class="panel-body" style="padding-top:0px;">
								<form action="/model/?action=manage&id=<? echo $id;?>" method='post' class="form-horizontal">
									
									<div class="form-group" style=''>
										<div class="">
											
	<table class='table' width='100%' >
        <thead>
		<tr>
			<td class='cellHeading' style='width:120px;'>Name</td>
			<td class='cellHeading' style="width:140px;">Appearance</td>
			<td class='cellHeading'>CSS</td>
			<td class='cellHeading' style="width:100px;">Actions</td>
		</tr>
        </thead>
        <tbody>
										
<?
	if( property_exists($model_contents,"styles") ) {
	
		for($i=0;$i<count($model_contents->styles);$i++) {
			$style = $model_contents->styles[$i];
			echo "<tr><td class='cell'>". $style->name . "</td><td class='cell'><div class='c' style='".$style->css."'>Hello World</div></td><td>".addslashes(htmlspecialchars($style->css))."</td><td class='cell' style='width:100px;'><button type='button' class='btn btn-info btn-xs' title='Edit Style' onclick=\"edit_style('".$style->id."','".$style->name."','".addslashes(htmlspecialchars($style->css))."');\"><i class='fa fa-pencil'></i></button>&nbsp;<button type='button' class='btn btn-info btn-xs' title='Edit Style' onclick=\"delete_style('".$style->id."');\"><i class='fa fa-times'></i></button></td></tr>";
			
		}
		
	} else {
		echo "<tr><td class='cell' colspan='4'>No Custom Styles Found.</td></tr>";
	}
?>
        </tbody>
	</table>
	<br/>
	<input type='hidden' id='txtStyleId' value=''/>
	<div style='margin-left:18px;vertical-align: top;'>Create / Update Style: <br/>
        <input type='text' class='form-control' placeholder="Style Name" id='txtStyle' style='width:200px;vertical-align: top;margin-bottom:10px;' value=''/>
        <textarea type='text' class='form-control'  style='min-width:400px;max-width:700px;height:140px;margin-bottom:10px;'  placeholder="Value" id='txtCss'></textarea>
        <span class="btn btn-primary btn-sm" onclick="btnSaveStyle();" id='btnSave' style='vertical-align: top; margin-top:0px;margin-bottom: 10px;'>Save Style</span>
        <p style='vertical-align: top;'>Note: style names can not contain spaces.</p>
    </div>
											
										</div>
									</div>
					
								</form>
							</div>
						</section>
					</div>
					
					
					
					
					
					
					
					
					
					
					
				</div>
				
				
				
				<form action='/model/manage/?id=<? echo $id;?>' method='post' id='formDelete'>
					<input type='hidden' name='delete' id='delete' value='ok'/>
				</form>

				<input type = 'hidden' id = 'previous_variable_value' name = 'previous_variable_value' value =''/>
				

<?
include_once("../lib/body_end.php");
?>

<script>
			
	var serviceName = "<? echo SERVICE_MODEL;?>";
	var modelId = "<? echo $id;?>";
	
	function scheduleChange() {
		
		document.getElementById('daily').style.display = 'none';
		document.getElementById('weekly').style.display = 'none';
		document.getElementById('monthly').style.display = 'none';
		
		var method = listValue(document.getElementById('list_schedule'));
		document.getElementById(method).style.display = 'block';
		
	}
	
	function delete_style(styleId) {
		var tasks = {"tasks": [
			{"task": "model.style.delete", "id": modelId, "styleid": styleId }
		]};
		
		query(serviceName,tasks,btnSaveVariableCallback);
	}
	
	function edit_style(styleId, styleName, styleCSS) {
		document.getElementById('txtStyleId').value = styleId;
		document.getElementById('txtStyle').value = styleName;
		document.getElementById('txtCss').value = styleCSS;
		$("#txtCss").focus();
		$("#txtStyle").focus();
	}
	
	function btnSaveStyle() {
		var name = document.getElementById('txtStyle').value;
		var css = document.getElementById('txtCss').value;
		var styleId = document.getElementById('txtStyleId').value;
		
		var tasks = {"tasks": [
			{"task": "model.style.set", "id": modelId, "styleid": styleId, "name": name, "css" : css }
		]};
		
		query(serviceName,tasks,btnSaveStyleCallback);
	}
	
	function btnSaveStyleCallback(data) {
		var results = JSON.parse(data);
		if( results['results'][0]['result'] == 1 ) {
			window.location.reload();
		} else {
			alert(results['results'][0]['error']);
		}
	}
	
	function btnSaveVariable() {
		var key = document.getElementById('txtVariable').value;
		var value = document.getElementById('txtValue').value;
		var previous_variable_value = document.getElementById('previous_variable_value').value;

		var tasks = {"tasks": [
			{"task": "model.variable.set", "id": modelId, "key": key, "value" : value }
		]};
		if (previous_variable_value.length > 0 && previous_variable_value != key){
			tasks["tasks"].push({"task": "model.variable.delete", "id": modelId, "key": previous_variable_value });
		}
		query(serviceName,tasks,btnSaveVariableCallback);
	}

	
	
	function btnSaveVariableCallback(result) {
		
		window.location.reload();
	}
	
	
	function scheduleAdd() {
		
		var method = listValue(document.getElementById('list_schedule'));
		var process = listValue(document.getElementById('list_process'));
		var value = document.getElementById(method + '_schedule').value;
		var tasks = null;
		
		var month = "*";
		var day = "*";
		var weekday = "*";
		var hour = "*";
		var min = "*";
				
		
		if( method == "daily" ) {
			//split the value by colon and return the hour and minute
			var time = value.split(":");
			if( time.length != 2 ) {
				alert("The provided time value was not in the correct format (00:00).");
				return;
			} else {
				hour = parseInt(time[0]) + "";
				min = parseInt(time[1]) + "";
			}
		} else if( method == "weekly" ) {
			value = value.replace(/ at /gi," ");
			weekday = value.substr(0,3).toLowerCase();
			value = value.substr(4,5);
			var time = value.split(":");
			if( time.length != 2 ) {
				alert("The provided time value was not in the correct format (00:00).");
				return;
			} else {
				hour = parseInt(time[0]) + "";
				min = parseInt(time[1]) + "";
			}
		} else if( method == "monthly" ) {
			value = value.replace(/ at /gi," ");
			value = value.replace(/st/gi,"");
			value = value.replace(/rd/gi,"");
			value = value.replace(/nd/gi,"");
			value = value.replace(/th/gi,"");
			day = value.substr(0,2).toLowerCase().trim();
			value = value.substr(2,5);
			var time = value.split(":");
			if( time.length != 2 ) {
				alert("The provided time value was not in the correct format (00:00).");
				return;
			} else {
				hour = parseInt(time[0]) + "";
				min = parseInt(time[1]) + "";
			}
		}
		
		tasks = {"tasks": [
			{"task": "schedule.create", "id": modelId, "processid": process, "months": month, "days": day, "hours": hour, "minutes": min, "weekdays": weekday}
		]};
		
		query(serviceName,tasks,function() { 
			window.location = "/model/manage/?id=" + modelId + "&action=add_ok";
		});
		
	}
	
	function save() {
	
		document.getElementById('testBox').style.display = 'block';	
		document.getElementById('testResult').innerHTML = 'Saving...';

		var name =  document.getElementById('name').value;
		task = "model.update";
		
		
		var hiddenFrom = [];
		$('input[type=checkbox]').each(function () {
           if (this.checked) {
               hiddenFrom[hiddenFrom.length] = $(this).val(); 
           }
		});
	
		var tasks = {"tasks": [
			{"task": task, "name": name, "id": modelId, "hide" : hiddenFrom }
		]};

		if( name.length > 2 ) {
			query(serviceName,tasks,save_callback);
		} else {
			document.getElementById('testResult').innerHTML = '<b>Save failed, the specified name was too short.</b><br/>' + str;
		}
	}

	function save_callback(data) {
		var results = JSON.parse(data);
	
		if( parseInt(results['results'][0]['result']) == 1 ) {
			document.getElementById('testResult').innerHTML = '<b>Model Updated.</b>';
		} else {
			document.getElementById('testResult').innerHTML = '<b>' + results['results'][0]['message'] + '</b>';
		}
	}
	
	var server_id = 0;
	
	
	function deleteItem(type,id,name) {
		var removeButton = "Delete " + type + ": " + name;
	
		document.getElementById('confirmParagraph').innerHTML = "Are you sure you would like to permanently delete the '" + name + "' " + type.toLowerCase() + "?";
	
		$( "#dlgDeleteItem" ).dialog({
			resizable: false,
			height:140,
			width:400,
			modal: true,
			title: removeButton,
			buttons: {
				"Delete Object" : function() {
					document.getElementById('formDelete').submit();
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	
	function delete_schedule(scheduleId) {
		var removeButton = "Delete Schedule";
	
		document.getElementById('confirmParagraph').innerHTML = "Are you sure you would like to permanently delete the schedule?";
		$( "#dlgDeleteItem" ).dialog({
			resizable: false,
			height:140,
			width:400,
			modal: true,
			title: removeButton,
			buttons: {
				"Delete Object" : function() {
					var tasks = {"tasks": [
									{"task": "schedule.delete", "id": modelId, "scheduleid": scheduleId }
								]};
					query(serviceName,tasks,function() { 
						window.location = "/model/manage/?id=" + modelId + "&action=delete_schedule_ok";
					});
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
		
	}

	function delete_variable(variableKey) {
		var removeButton = "Delete Variable";
	
		document.getElementById('confirmParagraph').innerHTML = "Are you sure you would like to permanently delete the variable <strong>" +variableKey+"</strong>?" ;
		$( "#dlgDeleteItem" ).dialog({
			resizable: false,
			height:140,
			width:400,
			modal: true,
			title: removeButton,
			buttons: {
				"Delete Object" : function() {
					var tasks = {"tasks": [
									{"task": "model.variable.delete", "id": modelId, "key": variableKey }
								]};
					query(serviceName,tasks,function() { 
						window.location = "/model/manage/?id=" + modelId + "&action=delete_schedule_ok";
					});
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
		
	}

	function update_variable(variableKey, variableValue){
		document.getElementById('txtVariable').value = variableKey;
		document.getElementById('txtValue').value = variableValue;
		document.getElementById('previous_variable_value').value = variableKey; 
		$("#txtValue").focus();
		$("#txtVariable").focus();
	}
	
	function view_variable_value(variableKey, variableValue){
		document.getElementById('displayVariableValue').innerHTML = variableValue;
		$( "#dlgVariableValue" ).dialog({
			resizable: false,
			height:280,
			width:600,
			modal: true,
			title: "Value of " + variableKey
		});
	}

	function variable_image_encode(element) {

	  var file = element.files[0];
	  var reader = new FileReader();
	  reader.onloadend = function() {
	    document.getElementById("txtValue").value = reader.result;
		$("#txtValue").focus();
		$("#txtValue").attr("disabled", true);
		$("#txtVariable").focus();

	  }
	  reader.readAsDataURL(file);

	}
</script>


<div id="dlgVariableValue" title="Variable" style='display:none;'>
	<span id='displayVariableValue'></span>
</div>

<div id="dlgDeleteItem" title="Delete Object" style='display:none;'>
	<span id='confirmParagraph'></span>
</div>

<?
include_once("../lib/footer.php");
?>
