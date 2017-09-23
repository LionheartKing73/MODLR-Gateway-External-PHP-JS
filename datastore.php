<?
include_once("lib/lib.php");

include_once("lib/header.php");

?>
<style type='text/css'>
.read-only {
	background-color:#f9f9f9;
}
.table {
	font-size: 12px;
	cursor: pointer;
}
</style>
<?

$tables = array();

$server_address = session("server_address");
$values = explode(":",$server_address);
$server_address = $values[0];
$password = getPasswordForServer(session("active_server_id"));

/*
//ensure that the internal datastore is setup as a datasource in all models.
$setupDatasource = '{"tasks":[{"task":"datasource.create","name":"Internal Datastore","username":"root","password":"'.$password.'","url":"jdbc:mysql://localhost:3306/datastore","type":"JDBC","driver":"com.mysql.jdbc.Driver","id":""}]}';
$results = api_short(SERVICE_DATASOURCE, $setupDatasource);
*/

$db = new db_helper();
try {
	$db->Host = $server_address;
	$db->User = C_DB_USER_HOST;
	$db->Password = $password;
	$db->Database = C_DB_NAME_HOST;
	$db->Close();
	$db->connect();
}
catch(Exception $ex) {
	header("Location: /500-datastore?serverid=".session("active_server_id"));
	die();
}


/*
$result = mysql_query("show tables", $db->Link_ID());
while($table = mysql_fetch_array($result)) { 
    $tables[] = $table[0];
}
*/

$res = mysql_query("SHOW DATABASES", $db->Link_ID());
while ($row = mysql_fetch_assoc($res)) {
	$database = $row['Database'];
	if( $database != "information_schema" && 
		$database != "mysql" && 
		$database != "performance_schema" ) {
		
		mysql_select_db($database, $db->Link_ID());
		$schema = mysql_query("show tables", $db->Link_ID());
		while($table = mysql_fetch_array($schema)) { 
			$tables[] = $database.".".$table[0];
		}
	}
}


$table = "";
if( count($tables) > 0 ) {
	$table = $tables[0];
}
if( querystring("table") != "" ) {
	$table = querystring("table");
}
		echo "<script type='text/javascript'>var table = '".$table."';</script>";
		echo '<script src="/js/md5.min.js"></script>';
?>
    	<title>MODLR » Datastore » <? echo $table;?></title>
<?
include_once("lib/body_start.php");
?>
	
        <div class="row">
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
                    <a class="navbar-brand" href="#">Table: <? echo $table;?></a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse navbar-ex1-collapse">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Other Tables <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                            	<?
                            		for($i=0;$i<count($tables);$i++) {
                            			echo '<li><a href="/datastore/?table='.$tables[$i].'">'.$tables[$i].'</a></li>';
                            		}
                            	?>
                            </ul>
                        </li>
                    </ul>
                    
                    <ul class="nav navbar-nav navbar-right">
                        <li><a data-toggle="modal" href="#uploadForm">Upload into new Table</a></li>
                        <li><a data-toggle="modal" href="#uploadForm">Upload into this Table</a></li>
                        
                    </ul>
                </div><!-- /.navbar-collapse -->
            </nav>
            <!--navigation end-->
        </div>

        <div class="row">


			<div class="col-md-12">
				<div class="panel" style="margin-bottom: 0px;">
					<div class="panel-heading">
						<? echo $table;?>
					  <span class="tools pull-right">
							<a class="fa fa-chevron-down" href="javascript:;"></a>
						</span>
					</div>
					<div class="panel-body">
					
<?
function echoToolbars($occurance) {
?>
						<div style='margin-bottom:5px;margin-top:5px;'>
							<div class="btn-group">
								<button class="btn btn-white" type="button">Actions</button>
								<button data-toggle="dropdown" class="btn btn-white dropdown-toggle" type="button"><span class="caret"></span></button>
								<ul role="menu" class="dropdown-menu">
									<li><a href="#" onclick='select_all();'>Select All (Visible)</a></li>
									<li><a href="#" onclick='select_none();'>Select None</a></li>
									<li class="divider"></li>
									<li><a href="#" onclick='remove_selected();'>Remove Selected</a></li>
									<li><a href="#" onclick='remove_all();'>Remove All Rows (Empty Table)</a></li>
									<li class="divider"></li>
									<li><a href="#" onclick='downloadSample();'>Download Bulk Load Template (Excel)</a></li>
									<li class="divider"></li>
									<li><a data-toggle="modal" href="#renameForm">Rename Table</a></li>
									<li><a href="#" onclick='delete_table();'>Delete Table</a></li>
								</ul>
							</div><!-- /btn-group -->
							
							<button type="button" class="btn btn-info" style='margin-left:20px;' onclick='addData();'>Add Row</button>
							
							<span class='pageMarker' style='margin-left:25px; font-size:14px;'>Page 1</span>
							
							<div style='float:right;'>
								<div class="btn-group custom_pagination_<? echo $occurance;?>" style='height:34px;'>
                                    <button class="btn btn-info active" type="button">1</button>
                                </div>
							</div>
							
							<div style="" class='filter-view' style='margin-right:5px;'>
								
							</div>
							
						</div>
<?
}
echoToolbars(1);
?>
						
						<div id='table-data' style='overflow-x:scroll;overflow-y:scroll;height:500px;border:1px solid #ccc;'></div>
<?
echoToolbars(2);
?>
					</div>
				</div>
			</div>

        </div>

		<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="uploadForm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title">Upload Data File</h4>
					</div>
					<div class="modal-body">
						
						<form role="form">
							<div class="form-group">
								<label for="lstTables">Import Action</label>
								<select class="form-control" id="lstTables">
									<option value=''>Create a new table from this file</option>
<?
for($i=0;$i<count($tables);$i++) {
	echo "<option value='".$tables[$i]."'>import into the ".$tables[$i]." table</option>";
}
?>
								</select>
							</div>
							<div class="form-group">
								<label for="fileUpload">Data File:</label>
								<input type="file" id="fileUpload">
								<p class="help-block">Presently the only supported file format is '.csv'.</p>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" id='wipeAllCheck'> Wipe all table data before loading this file.
								</label>
							</div>
							<button type="button" data-dismiss="modal" onclick='upload_file();' class="btn btn-default">Submit</button>
						</form>
					</div>
				</div>
			</div>
		</div>



		<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="renameForm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title">Rename Table</h4>
					</div>
					<div class="modal-body">
						
						<form role="form">
							<div class="form-group">
								<label for="exampleInputEmail1">New Table Name</label>
								<input type="email" class="form-control" id="txtNewTableName" placeholder="table name">
							</div>
							
							<button type="button" data-dismiss="modal" onclick='rename_table();' class="btn btn-default">Submit</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div aria-hidden="true" aria-labelledby="myModalLabelFilter" role="dialog" tabindex="-1" id="filterForm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title" id='filter-modal-heading'>Filter Dataset</h4>
					</div>
					<div class="modal-body">
						
						<form role="form" id='filterForm' name='filterForm'>
							<div class="form-group">
								<label for="filterTypeLabel">Filter Type:</label>
								<select class="form-control" id="lstFilterType">
									<option value='begins'>Begins with</option>
									<option value='contains'>Contains</option>
									<option value='ends'>Ends with</option>
									<option value='greater'>Is Greater than</option>
									<option value='less'>Is Less than</option>
									<option value='equals'>Is Equal to</option>
									<option value='notequals'>Is Not Equal to</option>
								</select>
							</div>
							
							<div class="form-group">
								<label for="exampleInputEmail1">Filter Value</label>
								<input type="text" class="form-control" id="txtFilterExpression" placeholder="xyz">
							</div>
							
							<input type="hidden" class="form-control" id="dataType">
							<button type="button" data-dismiss="modal" onclick='add_filter();' id='btnFilter' class="btn btn-default">Add Filter</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		
		
<?
include_once("lib/body_end.php");
?>
<script src="/js/service/lib.js"></script>
<script src="/js/service/datastore.js"></script>
<script>
$('#filterForm').submit(function() {
	$("#btnFilter").click();
	return false;
});
</script>
<?
include_once("lib/footer.php");
?>
