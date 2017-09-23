<?
ini_set('post_max_size',"8M");

include_once("../lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$action = querystring("action");
$id = querystring("id");
$dimension_id = querystring("dimension_id");
$model = null;
$usersHidden = array();
$modified_hierarchy_name = ""; //in case a new hierarchy or existing was modified, capture the name and pass it on initial query for hierarchy
if (isset($_POST['newHierarchyName'])){
	$modified_hierarchy_name = $_POST['newHierarchyName'];
}

$dimension_name = "";
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
		$dimensions = $model_contents->dimensions;
		
		foreach ($dimensions as $dim){
			if ($dim->id == $dimension_id){
				$dimension_name = $dim->name;
				break;
			}
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

echo "<script>";
echo "  var server_id = 0;\r\n";
echo "  var server_url = '" . session("server_address") . "';\r\n";
echo "</script>";

?>		
		<script src="/js/jquery-1.11.1.js"></script>
		<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>

		<script src="/js/service/lib.js?v=2"></script>	
		

	<style>
			.cell {
				border:0px;
				padding: 5px;
				min-width: 80px;
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
			
			
			table.object-table {
				font-size:11px;
			/*width: 99%;*/
			}
			.object-row {
				padding: 4px !important;
				height:20px;
				padding-left:6px;
			}

			.modal-body .form-horizontal .col-sm-2,
			.modal-body .form-horizontal .col-sm-10 {
					width: 100%
			}

			.modal-body .form-horizontal .control-label {
				text-align: left;
			}

			.collapsibleList li{
			list-style-image : url('/images/button.png');
			cursor           : auto;
			}

			li.collapsibleListOpen{
			list-style-image : url('/images/button-open.png');
			cursor           : pointer;
			}

			li.collapsibleListClosed{
			list-style-image : url('/images/button-closed.png');
			cursor           : pointer;
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
	</style>
<?
	
	
	
include_once("../lib/body_start.php");

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
                <a class="navbar-brand" href="/model/?id=<? echo $id;?>"><? echo $name;?> » <? echo $dimension_name;?></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav">
                    <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Other Dimensions: <b class="caret"></b></a>
                        <ul class="dropdown-menu">
<?

foreach ($dimensions as $dim){
    echo '<li><a href="/dimension/manage/?id='.$id.'&dimension_id='.$dim->id.'">'.$dim->name.'</a></li>';
}

$contents = $results->results[0]->models;
?>
                        </ul>
                    </li>
                </ul>
        
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#" onclick="initCreateNewHierarchy();" >New Hierarchy</a></li>
                            <li><a href="#" onclick="initCreateNewAlias();" >New Alias</a></li>
                        </ul>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </nav>
        <!--navigation end-->
    </div>
<?



$model_name = $name;

?>

				<div class="row">
					<div class="col-lg-12">
							<div id = 'error_message' class = 'alert alert-danger' style = 'display: none;'>
							</div>
							<div id = 'success_message' class = 'alert alert-success' style = 'display: none;'>
							</div>
					</div>
							
					<!-- Basic forms -->
					<div class="col-lg-3">
					
						<section class="panel">
							<header class="panel-heading">
								<strong><?php echo $dimension_name;?> Hierarchies</strong>
							</header>
							<div class="panel-body"  style="padding-top:0px;">

								<form class = 'form-horizontal' method = 'POST' onsubmit='return false;'>

									<div class="form-group" style = ''>
										<div id = "listOfHierarchies" style = ''>
										</div>
									</div>
								</form>

							</div>		
						</section>	

						<section class="panel">
							<header class="panel-heading">
								<strong><?php echo $dimension_name;?> Aliases</strong>
							</header>
							<div class="panel-body"  style="padding-top:0px;">

							<div id = "hierarchy-aliases" style=''>
								<form class = 'form-horizontal' method = 'POST' onsubmit='return false;'>
									<div class="form-group" style = ''>

										<div id = "listOfExistingAliases"></div>
									</div>
								</form>
							</div>		
						</section>		
					</div>

					<div class="col-lg-9">
						<section class="panel">
							<header class="panel-heading">
								Edit Hierarchy 
							</header>
							<div class="panel-body">
								<div class = "form-group">

									<p id = 'selectedHierarchyName' style='font-weight: bold'></p>
									<form method='POST' id="form_hierarchy" name="form_hierarchy" >

										<div id = "newHierarchyDiv" style = "display: none;" class = "form-group">
											<label for="newHierarchyName" class="col-lg-2 control-label">Name</label>
											<input type = "text" id = "newHierarchyName"  name = "newHierarchyName" placeholder='Hierarchy Name' class='form-control' style='width:250px;vertical-align: top;margin-bottom:10px;'/>
										</div>
										<textarea class='form-control' id = "dimensions_text" rows="20" cols="50"></textarea>
										<div class="form-check" style = 'margin-top:10px;'>
											<label class="form-check-label">
												<input class="form-check-input" type="checkbox" value="1" id = "hierarchy_wipe_dimension" name="hierarchy_wipe_dimension">
													Wipe Dimension contents using this hierarchy
											</label>
										</div>

										<div class = "pull-right">
											<button style='vertical-align: top; margin-top:10px;' type = "button" class = "btn btn-info btn-sm" id = "previewHierarchyButton" onclick = "previewHierarchy();">Preview</button>
											<button style='vertical-align: top; margin-top:10px;' type = "button" class = "btn btn-primary btn-sm" id = "newHierarchyButton" onclick = "createNewHierarchy();">Save</button>
										</div>
										<input type = "submit" style='display:none'/>
										<select id = "hierarchy-to-edit" style='display:none' class = "form-group"></select>
										<input  id = "hierarchy-id" type = "input" style='display:none' value=''/>
									</form>
								</div>

								
							</div>
						</section>
					</div>
					
				</div>
	
	<div id="dlgDeleteItem" title="Delete Object" style='display:none;'>
		<span id='confirmParagraph'></span>
	</div>

	<div id="dialog-loading" title="Loading..." style='display:none;'>

		<p><br/>
			<center><img src='/img/loader.gif'/><br/><br/>
			<span id='txtLoading'>Communicating with the analytics server.<span></center>
			
		</p>
	</div>	
	<div id="dialog-preview-hierarchy" title="Preview Hierarchy" style='display:none;'>
			
	</div>
	<div id="dialog-alias" style='display:none;'>

	</div>
	<div id="dialog-alias-new-name" title="Create Alias" style='display:none;overflow-y: auto;'>
			<div class = "modal-body">
				<form class="form-horizontal" onsubmit='return false;'>
					<div class = "form-group">
						<label for="temp_alias_name" class="col-lg-2 control-label">Name</label>
						<div class = "col-sm-10">
							<input type = "input" class = "form-control" id = "temp_alias_name" name="temp_alias_name" placeholder="Enter name for the new Alias"/>
							<?
								//this field acts as a temporary alias name, the actual field is called alias_name (populated in populateAliasData)
							?>
						</div>
					</div>
				</form>
			</div>

				
	</div>
	</div>
	</div>
<?
include_once("../lib/body_end.php");
?>
<script>


var tableHeadingAlias = "<table id='listOfExistingAliasesTable' class='table object-table'><thead><tr><td class='cellHeading'>Name</td><td class='cellHeading text-right' style='width:116px;'>Action</td></tr></thead>";
			

$(function(){
	enableTab("dimensions_text");

	var tasks = {"tasks": [
		{"task": "dimension.get", "id":"<? echo $id; ?>",  "dimensionid": "<? echo $dimension_id;?>" }
	]};
	query("model.service",tasks,getInitialiseHierarchy);
		
	$('#hierarchy-to-edit').change( function() { 
			updateDimensionEditHierarchy(); 
			$("#selectedHierarchyName").html($('#hierarchy-to-edit option:selected').text());
			$("#selectedHierarchyName").css('display', 'block');

	}); 
	
})

//sendReq();


var resultData = "";
var hierarchyToEdit = "";
function retrieveHierarchy(hierarchies, h_name){
	var request = {action:"retrieve_hierarchy", h_name:h_name, hierarchies: JSON.stringify(hierarchies)};

	$.ajax({
			type: "POST",
			url: "/json/dimension.php",			
			data: request,    
			cache: false,
			success: function(data) {
				$("#dimensions_text").html(decodeURI(data));
			},
			error: function(xhr, textStatus, thrownError, data) {
			}
		});
}

function initCreateNewHierarchy(){
		$("#newHierarchyName").css('border-color', '');
		$("#hierarchy-to-edit").css('display', 'none');
		$("#newHierarchyDiv").css('display', 'block');
		$("#newHierarchyName").focus();
		$("#dimensions_text").empty().append("Sample Parent\n\tSample Child 1\n\tSample Child 2");
		$("#selectedHierarchyName").css('display', 'none');

}

function createNewHierarchy(){
	if (document.getElementById("newHierarchyName").value.length == 0){
		$("#newHierarchyName").css('border-color', 'red');
		$("#dimensions_text").css('border-color', '');

	} else if (document.getElementById("dimensions_text").value.trim().length == 0){
		$("#dimensions_text").css('border-color', 'red');
		$("#newHierarchyName").css('border-color', '');


	} else {
		$("#newHierarchyName").css('border-color', '');
		$("#dimensions_text").css('border-color', '');

		var elmStr = document.getElementById('dimensions_text').value;
		var elmArray = elmStr.split("\n");
		var hierarchyToEdit = $("#hierarchy-to-edit option:selected").text();
		if (document.getElementById('newHierarchyName').value != "")
			hierarchyToEdit = document.getElementById('newHierarchyName').value;

		var defaultHier = {"root" : [], "name" : hierarchyToEdit };
		var root = buildElementTree(elmArray);
		var elements = [];
		for(var i=0;i<root.length;i++) {
			elements = dimensionSetAddNsToArray(root[i],elements);
		}
		defaultHier['root'] = root;
		
		var hierarchies = [];
		hierarchies[hierarchies.length] = defaultHier;
		var clean = "0";
		if ($("#hierarchy_wipe_dimension").is(":checked"))
			clean = "1";
		var tasks = {"tasks": [
			{"task": "dimension.update.hierarchy", "id":"<? echo $id;?>", "dimensionid":"<? echo $dimension_id;?>", "definition": defaultHier, "wipedimension" : clean }
		]};
		query("model.service",tasks, hierarchyCallBack);
	}
		
}

function hierarchyCallBack(data){
	$("#form_hierarchy").submit();
}
function dimensionSetAddNsToArray(node, arr) {
	var children = node['children'];
	if( children.length == 0 ) {
		arr[arr.length] = node;
	} else {
		for(var i=0;i<children.length;i++) {
			arr = dimensionSetAddNsToArray(children[i],arr);
		}
	}	
	return arr;
}

//initial request for hierarchy - gets the first hierarchy and sets up a dropdown
function getInitialiseHierarchy(data){
	if (resultData == "") 
		resultData = data;

	var results = JSON.parse(resultData);
	hierarchiesData = results;
	
	var str = "";
	strHierarchies = "";
	if( results['results'][0]['hierarchies'] ) {
		var hierarchies = results['results'][0]['hierarchies'];
		if (hierarchies.length > 0){
			hierarchyToEdit = hierarchies[0]['name'];

			retrieveHierarchy(hierarchies, "<? echo $modified_hierarchy_name;?>");
			var tableData = "<table id='listOfExistingHierTable' class='table object-table'><thead><tr><td class='cellHeading'>Name</td><td class='cellHeading text-right' style='width:116px;'>Action</td></tr></thead>";
			for(var i=0;i<hierarchies.length;i++) {
				var hier = hierarchies[i];
				strHierarchies += "<option>" + hier['name'] + "</option>";
				tableData += "<tr data-hier-name='" + hier['name'] + "'><td class = 'cell'>" + hier['name'] + "</td><td class = 'text-right workview-name object-row' style=''><button  type = 'button' class = 'btn btn-xs btn-info' onclick='changeSelectedHierarchy("+ "\"" + hier['name']+"\""+ ","+ "\"" + hier['id']+"\""+ ");'>Edit</button>&nbsp;<button type = 'button' class = 'btn btn-xs btn-danger' onclick='hierarchyRemove("+ "\"" + hier['name']+"\""+ ");'>Delete</button></td></tr>";
			}
			tableData += "</table>";
			$("#hierarchy-to-edit").html(strHierarchies);
			$("#listOfHierarchies").html(tableData);
			$("#selectedHierarchyName").html($("#hierarchy-to-edit").find("option:first-child").text());
			$("#hierarchy-id").val(hierarchies[0]['id']);
			$("#newHierarchyName").val(hierarchies[0]['name']);

			if (results['results'][0]['aliases'].length > 0){
				//console.log(results['results'][0]['aliases']);
				tableData = tableHeadingAlias;
			
				for (var i=0; i < results['results'][0]['aliases'].length; i++){
					var alias = results['results'][0]['aliases'][i];
					tableData += "<tr data-alias-name='" + alias['name'] + "'><td class = 'cell'>" + alias['name'] + "</td><td  class = 'text-right workview-name object-row' style=''><button  type = 'button' class = 'btn btn-xs btn-info' onclick='editAlias("+ "\"" + alias['name']+"\"" + ");'>Edit</button>&nbsp;<button type = 'button' class = 'btn btn-xs btn-danger' onclick='aliasRemove("+ "\"" + alias['name']+"\""+ ");'>Delete</button></td></tr>";
				}

				tableData += "</table>";

				$("#listOfExistingAliases").html(tableData);
				$("#hierarchy-aliases").css('display', 'block');

			} else {
				$("#listOfExistingAliases").html("<p class='text-center' style='margin-top:10px;'> There are no aliases present in this dimension yet.</p>");

			}

		}  else {
			$("#listOfHierarchies").html("<p class='text-center' style='margin-top:10px;'> There are no hierarchies present in this dimension yet.</p>");
			initCreateNewHierarchy();
		}
		if( hierarchyToEdit == "" ) {
			hierarchyToEdit = "Default";
		}
		
	}
}


function changeSelectedHierarchy(value, hier_id){
	
	$('#hierarchy-to-edit').val(value).change();
	$('#hierarchy-id').val(hier_id);
	$('#newHierarchyDiv').css('display', 'none');
	$('#newHierarchyName').val(value);
}

function hierarchyRemove(hierName) {
	var removeButton = "Delete Hierarchy " + hierName + "?";
	
	document.getElementById('confirmParagraph').innerHTML = "Are you sure you would like to permanently delete the '<strong>" + hierName + "</strong>' hierarchy?";
	
	$( "#dlgDeleteItem" ).dialog({
		resizable: false,
		height:140,
		width:400,
		modal: true,
		title: removeButton,
		buttons: {
			"Delete Object" : function() {
					var tasks = {"tasks": [
						{"task": "dimension.delete.hierarchy", "id": "<? echo $id;?>", "dimensionid": "<? echo $dimension_id;?>" , "hierarchyname": hierName  }
					]};
					query("model.service",tasks,function(){
						$("#listOfExistingHierTable tr[data-hier-name='"+hierName+"']").remove();

					});
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});	
	
}

function aliasRemove(alias_name){
	var removeButton = "Delete Alias " + alias_name + "?";
	
	document.getElementById('confirmParagraph').innerHTML = "Are you sure you would like to permanently delete the '<strong>" + alias_name + "</strong>' alias?";
	
	$( "#dlgDeleteItem" ).dialog({
		resizable: false,
		height:140,
		width:400,
		modal: true,
		title: removeButton,
		buttons: {
			"Delete Object" : function() {
					var tasks = {"tasks": [
						{"task": "dimension.alias.delete", "id": "<? echo $id;?>", "dimension": "<? echo $dimension_name;?>" , "alias": alias_name  }
					]};
					query("model.service",tasks, function(){
						$("#listOfExistingAliasesTable tr[data-alias-name='"+alias_name+"']").remove();

					});
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});	

}




function updateDimensionEditHierarchy() {
	hierarchyToEdit =  $('#hierarchy-to-edit option:selected').text();
	
	if( hierarchiesData['results'][0]['hierarchies'] ) {
		retrieveHierarchy(hierarchiesData['results'][0]['hierarchies'], hierarchyToEdit);
	}
	
}


function buildElementTree(elmArray) {
	var root = [];
	var parents = [];
	for(var i=0;i<elmArray.length;i++) {
		var elm = elmArray[i];
		
		var type = "N";
		if( elm.substr(elm.length-3,3).toLowerCase() == "[s]" ) {
			type = "S";
			elm = elm.substr(0,elm.length-3);
		}
		if( elm.substr(0,3).toLowerCase() == "[s]" ) {
			type = "S";
			elm = elm.substr(3,elm.length-3);
		}
		
		var node = { "name": elm.trim(), "children" : [], "type": type };
		
		if( elm.length > 0 ) {

			var tabs = elm.split('\t');
			while( tabs[tabs.length-1] == "") {	//trim off the trailing tabs
				tabs.splice(tabs.length-1,1);
			}

			if( tabs.length > 1 ) {

				var children = parents[tabs.length-2]['children'];
				children[children.length] = node;
				parents[tabs.length-2]['children'] = children;
			} else {
				//add this element to the root array.
				root[root.length] = node;

			}
			parents[tabs.length-1] = node;

		}
	}
	return root;
}

function initCreateNewAlias(){
	var lines = $("#dimensions_text").html().split('\n');
	var principal = [];
	$("#dialog-alias-new-name").dialog({
			resizable: false,
			height:225,
			width:400,
			modal: true,
			title: 'Create Alias',
			buttons: {
				"Proceed" : function() {
					checkIfAliasExists();
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
	});
	


}
function checkIfAliasCreated(data){
	data = JSON.parse(data);
	if (data['results'][0]['result'] == 1){
        if( $('#listOfExistingAliasesTable').length == 0 ) {
            $("#listOfExistingAliases").html(tableHeadingAlias +  "</table>");
        }
        
		$('#listOfExistingAliasesTable').append("<tr><td>" + data['results'][0]['tag']+"</td><td  class = 'text-right workview-name object-row' style=''><button  type = 'button' class = 'btn btn-xs btn-info' onclick='editAlias("+ "\"" + data['results'][0]['tag'] +"\"" + ");'>Edit</button>&nbsp;<button type = 'button' class = 'btn btn-xs btn-danger' onclick='aliasRemove("+ "\"" + data['results'][0]['tag']+"\""+ ");'>Delete</button></td></tr></tr>");
	}

}

function createNewAliasQuery(type){
	var alias_name = $("input[name=alias_name]").val();
	var tasks = { "tasks":[] };

	var i =0;
	$("input[name=alias_input]").each(function() {
		tasks["tasks"][i++] = {task: "dimension.alias.set", id:"<? echo $id;?>", dimension:"<? echo $dimension_id;?>", alias :  alias_name, element: $(this).attr('data-principal').trim(), value :  $(this).val()};
	});
	query("model.service",tasks, createNewAliasCallback);
}
function createNewAliasCallback(data){
	setSuccessMessage("Alias Data has been successfully saved");
}

function checkIfAliasExists(){
	var alias_name = $("#temp_alias_name").val();
	var tasks = {"tasks": [
				{"task": "dimension.alias.exists", "id":"<? echo $id;?>", "dimension":"<? echo $dimension_id;?>", "alias": alias_name}
	]};		
	query("model.service",tasks, checkIfAliasExistsCallback);

}

function checkIfAliasExistsCallback(data){
	data = JSON.parse(data);
	if (data['results'][0]['exists'] == "false"){
		$("#error_message").css("display", "none");

		var alias_name = $("#temp_alias_name").val();
					var tasks	= {"tasks":[
						{"task": "dimension.alias.create", "id": "<? echo $id;?>", "dimension": "<? echo $dimension_id; ?>", "alias": alias_name, "tag":alias_name}
					]};
		query("model.service", tasks, checkIfAliasCreated);
	} else {
		setErrorMessage("Alias name already exists.");
	}
}

function editAlias(alias_name){
	
	var tasks = {"tasks": [
		{"task": "dimension.get", "id":"<? echo $id;?>", "dimensionid":"<? echo $dimension_id;?>", "alias" : alias_name, "tag":alias_name }
	]};

	isEditAlias = true;
	query("model.service",tasks, editAliasCallback);
}

function editAliasCallback(alias_data, alias_name){
	var str = "";
	alias_data = JSON.parse(alias_data);
	var root = alias_data['results'][0]['elements'];
	for (var i = 0; i < root.length; i++){
		str += recursiveExtractHierarchyAlias(root[i]);
	}
	var splitLines = str.split('\n');
	var principal = [];
	var element = [];
	for (var i = 0; i < splitLines.length; i++){
		if (splitLines[i].length > 0){
			principal.push(splitLines[i].split('|')[0].trim());
			element.push(splitLines[i].split('|')[1].trim());
		}
	}	
	populateAliasData(element, principal,alias_data['results'][0]['tag']);
	$("input[name=alias_name]").attr("disabled", true);

}

function recursiveExtractHierarchyAlias(node) {
	var str =  node['principal'] + "|" + node['name'] + '\n';
	var children = node['children'];
	for(var i=0;i<children.length;i++) {
		str += recursiveExtractHierarchyAlias(children[i]);
	}	
	return str;
}

function populateAliasData(element, principal, alias_name){
	var html = "<table class = 'table table-striped object-table'>";
		html += "<thead>";
			html += "<tr>";
			html += "<td>Element</td>";
			html += "<td>Alias</td>";
			html += "</tr>";
		html += "</thead>";
		html += "<tbody>";
		html += "<input  type = 'hidden' data-principal = '" + (principal[0] || element[0].trim()) + "' class = 'form-control' name='alias_name' value = '" + ( alias_name) + "'/>";

	for(var i = 0;i < element.length;i++){
		if (element[i].length > 0){
			html += "<tr>";
			html += "<td class = 'cell'>" + (principal[i] || element[i]) + "</td>";
			html += "<td class = 'cell'>";
				html += "<input type = 'input' data-principal = '" + (principal[i] || element[i].trim()) + "' class = 'form-control' name='alias_input' value = '" + (element[i].trim()) + "'/>";
			html += "</td>";
			html += "</tr>";
		}
	}
	html += "</tbody>";
	html += "</table>";
	$("#dialog-alias").empty().append(html);
	$("#dialog-alias").dialog({
			resizable: false,
			height:600,
			width:600,
			modal: true,
			title: (typeof principal[0] !== "undefined" ? 'Edit Alias' : 'Create Alias') + ": " +  alias_name,
			buttons: {
				"Save" : function() {
					createNewAliasQuery("create");
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
}

function previewHierarchy(){
	var newLineCount = 1;
	var lines = document.getElementById('dimensions_text').value.split('\n');
	var html = "<div class='modal-content'>";
	html += "<div class='modal-body'>";
	html += "<ul class = 'collapsibleList' id = 'preview'>";
	var nextTag = "";
	var isULOpen = false;
	for (var i = 0; i < lines.length; i++){
		var line_trimmed = lines[i].trim();
		if (line_trimmed != ""){
			var currentNumberOfTabs = lines[i].split('\t').length;
			var nextNumberOfTabs = lines[i+1] != null ? lines[i+1].split('\t').length : currentNumberOfTabs;
			if (currentNumberOfTabs < nextNumberOfTabs){
				html += "<li>" + line_trimmed;
				nextTag = "<ul>";
				isULOpen = true;
			} else if (currentNumberOfTabs == nextNumberOfTabs){
				html += nextTag + "<li>" + line_trimmed + "</li>";
				nextTag = "";
			} else {
				
				html += nextTag + "<li>" + line_trimmed  + "</li>";
				nextTag = "";
				while (currentNumberOfTabs > nextNumberOfTabs){
					currentNumberOfTabs--;
					html += "</ul>";
					isULOpen = false;
				}
				if (isULOpen){
					html += "</ul>";
					isULOpen = false;
				}

			}
		}


	}

	html += "</div>";
	html += "</div>";

	$("#dialog-preview-hierarchy").empty().append(html);
	$("#dialog-preview-hierarchy").dialog({
			resizable: false,
			height:600,
			width:600,
			modal: true,
			buttons: {
				"Close" : function() {
					$( this ).dialog( "close" );
				}
			}
		});
		CollapsibleLists.applyTo(document.getElementById('preview'));

}


function enableTab(id) {
    var el = document.getElementById(id);
    el.onkeydown = function(e) {
        if (e.keyCode === 9) { // tab was pressed

            // get caret position/selection
            var val = this.value,
                start = this.selectionStart,
                end = this.selectionEnd;

            // set textarea value to: text before caret + tab + text after caret
            this.value = val.substring(0, start) + '\t' + val.substring(end);

            // put caret at right position again
            this.selectionStart = this.selectionEnd = start + 1;

            // prevent the focus lose
            return false;

        } else if(e.keyCode == 13){

			// assuming 'this' is textarea

			var cursorPos = this.selectionStart;
			var curentLine = this.value.substr(0, this.selectionStart).split("\n").pop();
			var indent = curentLine.match(/^\s*/)[0];
			var value = this.value;
			var textBefore = value.substring(0,  cursorPos );
			var textAfter  = value.substring( cursorPos, value.length );

			e.preventDefault(); // avoid creating a new line since we do it ourself
			this.value = textBefore + "\n" + indent + textAfter;
			setCaretPosition(this, cursorPos + indent.length + 1); // +1 is for the \n
		}
    };
}

function setCaretPosition(ctrl, pos)
{
    if(ctrl.setSelectionRange)
    {
        ctrl.focus();
        ctrl.setSelectionRange(pos,pos);
    }
    else if (ctrl.createTextRange) {
        var range = ctrl.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}

function setErrorMessage(message){
	$("#error_message").css("display", "block");
	$("#success_message").css("display", "none");
	$("#error_message").html('<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>&nbsp;' + message);
	$("html,body").animate({scrollTop: 0});

}

function setSuccessMessage(message){
	$("#success_message").css("display", "block");
	$("#error_message").css("display", "none");
	$("#success_message").html('<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp;' + message);
	$("html,body").animate({scrollTop: 0});
}
</script>
<?
include_once("../lib/footer.php");
?>