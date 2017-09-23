<?
include_once("lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$modelid = form("id");
if( $modelid == "" ) {
	$modelid = querystring("id");
}

if( $modelid == "" ) {
	redirectToPage ("/home/");
	die();
}

$msg = "";

$tableid = form("tableid");
if( $tableid == "" ) {
	$tableid = querystring("tableid");
}

$action = form("action");
if( $action == "" ) {
	$action = querystring("action");
}

if( $action == "create" ) {
	$name = form("txt_table");
	$datasourceid = form("list_datasource");
	$table = form("txt_system_table");
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"table.create\", \"name\":\"" . $name . "\", \"datasourceid\":\"" . $datasourceid . "\", \"table\":\"" . $table. "\", \"id\":\"" . $modelid  . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	if( intval($results->results[0]->result) == 1 ) { 
		$tableid = $results->results[0]->tableid;
		redirectToPage ("/table/?id=" . $modelid . "&tableid=" . $tableid);
		die();
	} else {
		if( property_exists($results->results[0], "message") ) {
			$msg = $results->results[0]->message;
		}
		if( property_exists($results->results[0], "error") ) {
			$msg = $results->results[0]->error;
		}
		
		
		
	}
}

include_once("lib/header.php");

echo "<title>MODLR » Manage Table</title>";

?>
<style type='text/css'>
div.field_heading {
	background-color:#EAEAEA !important;
}

div.field_definition {
	margin: 3px;
	padding:10px;
	border: 0px;
	background-color:#FAFAFA;
	border-radius: 5px;
}

td.field_system_name {
	font-size:11px;
}

td.field_type {
	font-size:11px;
}
</style>
<?
	
include_once("lib/body_start.php");

//table creation screen.
if( $tableid == "" ) {

	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"datasource.list\"}";
	$json .= "]}";

	$results = api_short(SERVICE_DATASOURCE, $json);

	$datasources = $results->results[0]->datasources;

	if( $modelid != "" ) {
		
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"model.get\", \"id\":\"" . $modelid  . "\"}";
		$json .= "]}";
		
		$results = api_short(SERVICE_MODEL, $json);
		$modelname = $results->results[0]->model->name;
		
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"home.directory\"}";
		$json .= "]}";
		
		$results = api_short(SERVICE_SERVER, $json);
		
		outputModelToolbar($modelid, $modelname);
	}
?>	

			
				<div class="row">
				
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Create a new Table
							</header>
							<div class="panel-body">
				
									<form action="/table/" method='post' class="form-horizontal">
										<input type="hidden"  id="action"   name="action"  value="create"/>
										<input type="hidden"  id="id"   name="id"  value="<? echo $modelid;?>"/>
									
										<?
										if( $msg != "" ) {
											echo "<p style='color:red;'>".$msg."</p>";
										}
										?>
									
										<div class="form-group">
											<label for="input1" class="col-lg-2 control-label">Table Name:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="txt_table" name="txt_table" value="" placeholder="Table Name" onkeyup="update_system_name('txt_table','txt_system_table')" onchange="update_system_name('txt_table','txt_system_table')"/>
												<span class="help-block">Tip: Name the table using one word (if possible) which describes the type of information which will be stored using a singular term (for example: Account, Department, Project, Employee). </span>
											</div>
										</div>
										
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">System Table Name:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="txt_system_table" name="txt_system_table" value="" placeholder="table_name" disabled/>
												<span class="help-block">This is the underlying table name, when seen when via the "Manage Data" page. </span>
											</div>
										</div>
										
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">Target Datasource:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_datasource' name='list_datasource'>	
<?
for($i=0;$i<count($datasources);$i++) {
	$ds = $datasources[$i];
	
	if( strtolower($ds->driver) == "com.mysql.jdbc.driver" ) {
		echo "<option value='".$ds->id."'>".$ds->name."</option>";
	}
}
?>												
												</select>
											</div>
										</div>
										
										<div class="form-group">
											<div class="col-lg-offset-2 col-lg-10">
												<button class="btn btn-primary" type='submit'>Save</button>
												<span class="btn btn-primary" onclick="window.location='/model/?id=<? echo $modelid;?>';">Cancel</span>
											</div>
										</div>

									</form>

							</div>
						</section>
					</div>
					<!-- /Basic forms -->

					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Getting Started
								<span class="tools pull-right">
									<a class="fa fa-chevron-up" href="javascript:;"></a>
								</span>
							</header>
							<div class="panel-body" style="">
								
								<p>A Table is used to store data which is either manually entered by a user, or populated via a Model Process with data from other data sources. </p>
								<p>When building a new table a default numeric identifier will be added. This field is called the Primary Key and is used to identify each record within the table.</p>
								
								
							</div>
						</section>
					</div>
					
					
				</div>
<?
} else if( $tableid != "" ) { 
//display the editor for an existing table.

	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"table.get\", \"tableid\":\"" . $tableid . "\", \"id\":\"" . $modelid  . "\"},";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $modelid  . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	if( intval($results->results[0]->result) == 0 ) { 
		redirectToPage ("/home/");
		die();
	}
	
	$tablename = $results->results[0]->table->name;
	$modelname = $results->results[1]->model->name;
	
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
							<a class="navbar-brand" href="/model/?id=<? echo $modelid;?>"><? echo $modelname;?> » </a>
							<a class="navbar-brand" href="/table/?id=<? echo $modelid;?>&tableid=<? echo $tableid;?>">Table: <? echo $tablename;?></a>
						</div>

						<!-- Collect the nav links, forms, and other content for toggling -->
						<div class="collapse navbar-collapse navbar-ex1-collapse">
							<ul class="nav navbar-nav">
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Other Tables: <b class="caret"></b></a>
									<ul class="dropdown-menu">
<?
	
		$contents = $results->results[1]->model->tables;
		for($i=0;$i<count($contents);$i++) {
			$table = $contents[$i];
			echo '<li><a href="/table/?id='.$modelid.'&tableid='.$table->id.'">'.$table->name.'</a></li>';
		}
?>
									</ul>
								</li>
							</ul>
					
							<ul class="nav navbar-nav navbar-right">
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
									<ul class="dropdown-menu">
										<li><a href="/table/?id=<? echo $modelid;?>">New Table</a></li>
										<li><a href="#" onclick="field_new();">New Field</a></li>
										<li><a href="#" onclick="table_delete();">Delete Table</a></li>
									</ul>
								</li>
							</ul>
						</div><!-- /.navbar-collapse -->
					</nav>
					<!--navigation end-->
				</div>


				<div class="row">
				
					<div class="col-lg-7">
						<section class="panel">
							<header class="panel-heading">
								Table Definition
							</header>
							<div class="panel-body" style="" id="table_definition">
								
								<br/><center><img src='/img/loader.gif'/><br/><br/>
								<span id='txtLoading'>Communicating with the analytics server.<span></center>
								
							</div>
						</section>
					</div>

					<div class="col-lg-5" id='form_new_field'>
						<section class="panel">
							<header class="panel-heading" id='heading_field'>
								Add a New Field
							</header>
							<div class="panel-body" style="">
								
								<form action="/table/" method='post' class="form-horizontal">
										<input type="hidden"  id="action"   name="action"  value="create"/>
										<input type="hidden"  id="id"   name="id"  value="<? echo $modelid;?>"/>
									
										<p style='color:red;display:none;' id='warning_newfield'></p>
									
										<div class="form-group">
											<label for="input1" class="col-lg-2 control-label">Field Name:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="txt_new_field" name="txt_new_field" value="" placeholder="Field Name" onkeyup="update_system_name('txt_new_field','txt_system_field');" onchange="update_system_name('txt_new_field','txt_system_field');"/>
											</div>
										</div>
										
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">System Name:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="txt_system_field" name="txt_system_field" value="" placeholder="field_name" disabled/>
												<span class="help-block">This is the underlying table name, when seen when via the "Manage Data" page. </span>
											</div>
										</div>
										
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">Field Type:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_type' name='list_type' onchange='list_type_change();'>	
													<option value="CALCULATED NUMERIC">Calculated Numeric</option>
													<option value="CALCULATED TEXT">Calculated Text</option>
													<option value="DATE">Date</option>
													<option value="DATE TIME">Date Time</option>
													<option value="DATE TIME CREATED">Date Time Created</option>
													<option value="DATE TIME LAST UPDATED">Date Time Last Updated</option>
													<option value="FILE ATTACHMENT">File Attachment</option>
													<option value="NUMERIC">Numeric</option>
													<option value="REMOTE IDENTIFIER">Remote Identifier</option>
													<option value="TEXT [25,000]">Text [25,000]</option>
													<option value="TEXT FORMATTED [25,000]">Text Formatted [25,000]</option>
													<option value="TEXT [512]" SELECTED>Text [512]</option>
													<option value="TEXT [55]">Text [55]</option>
													<option value="TEXT [LIST]">Text [List]</option>
													<option value="TIME">Time</option>
													<!-- <option value="UNIQUE IDENTIFIER">Unique Identifier</option> -->
													<option value="USER ID CREATOR">User Id Creator</option>
													<option value="USER ID LAST MODIFIED">User Id Last Modified</option>
													<option value="USER ID">User Id</option>
													<option value="YES OR NO">Yes Or No</option>

												</select>
											</div>
										</div>
										
										<div class="form-group" id='field_format'>
											<label for="txt_format" class="col-lg-2 control-label">Display Format:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="txt_format" name="txt_format" value="" placeholder="#,#00.00" />
											</div>
										</div>
										
										
										<div class="form-group" id='field_options'>
											<label for="txt_options" class="col-lg-2 control-label">List Options:</label>
											<div class="col-lg-10">
												<textarea type="input" class="form-control" rows='4' id="txt_options" name="txt_options" value="" placeholder="Option A" ></textarea>
											</div>
										</div>
										
										<div class="form-group" id='field_formula'>
											<label for="txt_formula" class="col-lg-2 control-label">Formula:</label>
											<div class="col-lg-10">
												<textarea type="input" class="form-control" rows='2' id="txt_formula" name="txt_formula" value="" placeholder="Formula" ></textarea>
												<span class="help-block">Tip: Use field system names and SQL syntax formula.</span>
											</div>
										</div>
										
										<div class="form-group" id='field_remote_table'>
											<label for="select1" class="col-lg-2 control-label">Remote Table:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_table' name='list_table'>	
<?
	
		$contents = $results->results[1]->model->tables;
		for($i=0;$i<count($contents);$i++) {
			$table = $contents[$i];
			echo '<option value="'.$table->id.'">'.$table->name.'</option>';
		}
?>
												</select>
											</div>
										</div>
										
										<div class="form-group" id='field_file_type'>
											<label for="select1" class="col-lg-2 control-label">File Type:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_file_type' name='list_file_type'>	
													<option value='pdf'>PDF</option>
													<option value='generic document'>Generic Document (xlsx, docx, pdf)</option>
													<option value='spreadsheet'>Spreadsheet (xlsx, xls)</option>
													<option value='word'>Document (docx, doc)</option>
													<option value='image'>Image (jpg, png, gif)</option>
													<option value='any' selected>Any Type</option>
												</select>
											</div>
										</div>
										
										<div class="form-group">
											<div class="col-lg-offset-2 col-lg-10">
												<button class="btn btn-primary" type='button' onclick='field_add()'>Add</button>&nbsp;
												<button class="btn btn-primary" type='button' onclick='field_new()'>Cancel</button>
											</div>
										</div>

										
									</form>
								
							</div>
						</section>
					</div>
					
					<div class="col-lg-6" id='form_edit_field' style='display:none;'>
						<section class="panel">
							<header class="panel-heading">
								Field: 
							</header>
							<div class="panel-body" style="">
								
								<p>A Table is used to store data which is either manually entered by a user, or populated via a Model Process with data from other data sources. </p>
								
								
							</div>
						</section>
					</div>
					
				</div>
				
				<script type='text/javascript'>
					var table_id = "<? echo $tableid;?>";
					var table_name = "<? echo $tablename;?>";
					var model_id = "<? echo $modelid;?>";
					var model_name = "<? echo $modelname;?>";
					var server_id = <? echo session("active_server_id");?>;
				</script>
				
<?
}

?>

				<script type='text/javascript'>
				
				//note that this is also computed on the server side in a more rigorous process
				function update_system_name(input_id, output_id) {
					var txt_table = $("#" + input_id);
					
					var str = txt_table.val();
					str = str.replace(/ /gi,"_");
					//remove invalid system table characters
					str = str.replace(/\t/gi, "").replace(/\./gi, "").replace(/\,/gi, "").replace(/\-/gi, "").replace(/\r/gi, "").replace(/\n/gi, "").replace(/\:/gi, "").replace(/\[/gi, "").replace(/\]/gi, "").replace(/\)/gi, "").replace(/\(/gi, "").replace(/\}/gi, "").replace(/\{/gi, "").toLowerCase();
					//remove excess underscores
					str = str.replace(/__/gi,"_").replace(/__/gi,"_");
					
					var txt_system_table = $("#" + output_id);
					txt_system_table.val(str);
				}
				
				</script>
<?
include_once("lib/body_end.php");
?>

<script src="/js/service/table.js"></script>
<?
include_once("lib/footer.php");
?>
