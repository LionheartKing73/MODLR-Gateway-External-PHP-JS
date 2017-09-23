
var workview_definition = {
    target_workview: "",
    target_cube: ""
};

if( workview_definition_loaded != null ) {
	workview_definition = workview_definition_loaded;
	
}

function updateDefinition(key, value) {
	workview_definition[key] = value;
}
function getDefinition(key) {
	return workview_definition[key];
}

function textUpdate(text) {
	var value = text.value;
	var key = text.getAttribute('id');
	updateDefinition(key,value);
}

function checkFieldUpdate(text) {
	//needs to read the field name then update the setting
	var value = text.checked;
	var key = text.getAttribute('id');
	var tr = text.parentNode.parentNode;
	var fieldName = tr.childNodes[0].innerText;
	
	var val = "0";
	if( value )
		val = "1";
	
	updateDefinitionColumn(fieldName,key,val);
	
}

function methodChange() {
	var method = listValue(document.getElementById('list_action'));
	if( method != "Create a new workview on an existing cube" ) {
		document.getElementById('cubeBlock').style.display = 'block';
		document.getElementById('cubeBlockNotes').style.display = 'block';
		
		document.getElementById('cubeExistingBlock').style.display = 'none';
	} else {
		document.getElementById('cubeBlock').style.display = 'none';	
		document.getElementById('cubeBlockNotes').style.display = 'none';
		
		document.getElementById('cubeExistingBlock').style.display = 'block';
	}
	
	//cubeExistingBlock
}

function cubeChange() {

	var target_existing_cubeList = document.getElementById('target_existing_cube');
	var target_existing_cube = "";
	if( target_existing_cubeList.selectedIndex > -1 ) {
		target_existing_cube = target_existing_cubeList.options[target_existing_cubeList.selectedIndex].value;
	}
	
	updateDefinition("target_cube", target_existing_cube);
	
	
}

function createWorkView() {
	var cube = getDefinition("target_cube");
	var workview = getDefinition("target_workview");
	
	if( cube == "" ) {
		alert("You must specify a cube name to create a workview.");
		return;
	}
	if( workview == "" ) {
		alert("You must specify a workview name to create a workview.");
		return;
	}
	
	
	
	var method = listValue(document.getElementById('list_action'));
	
	var bExists = false;
	for(var i=0;i<model_detail.cubes.length;i++) {
		if( model_detail.cubes[i].id == cube || model_detail.cubes[i].name == cube ) {
			bExists = true;
		}
	}
	
	if( method == "Create a new workview on an existing cube" ) {
		//Check that the cube exists then proceed
		if( bExists ) {
			//build and save the workview
			saveWorkView();
			
		} else {
			alert("This cube appears to have been removed since this webpage was loaded. The page will be refreshed.");
			window.location = window.location;
		}
	} else {
		//Check if the cube exists, provide confirm prompt on overwrite
		var actionStr = "";
	
		if( bExists ) {
			//confirm overwrite
			
			actionStr += "<a href='#' class='list-group-item'>";
			actionStr += "<h4 class='list-group-item-heading' style='font-size: 14px;'>Cube Deletion</h4>";
			actionStr += "<p class='list-group-item-text'>The existing cube '"+cube+"' will be deleted.</p>";
			actionStr += "</a>";
			
			
		} else {
			//build cube then save the workview
			
		}
		
		actionStr += "<a href='#' class='list-group-item'>";
		actionStr += "<h4 class='list-group-item-heading' style='font-size: 14px;'>Cube Creation</h4>";
		actionStr += "<p class='list-group-item-text'>A new cube named '"+cube+"' will be created with standard dimensionality.</p>";
		actionStr += "</a>";
		
		
		var process_actions = document.getElementById('process_actions');
		process_actions.innerHTML = actionStr;
		
		$( "#dialog-confirm-overwrite" ).dialog({
		  resizable: true,
		  height:320,
		  width:320,
		  modal: true,
		  buttons: {
			"Build and Save": function() {
				continueBuild();
			  	$( this ).dialog( "close" );
			},
			Cancel: function() {
			  	$( this ).dialog( "close" );
			}
		  }
		});
	}
}

function continueBuild() {
	var cube = getDefinition("target_cube");
	var cube_id = "";
	
	var bExists = false;
	for(var i=0;i<model_detail.cubes.length;i++) {
		if( model_detail.cubes[i].id == cube || model_detail.cubes[i].name == cube ) {
			bExists = true;
			cube_id = model_detail.cubes[i].id;
		}
	}
	
	//definition
	if( cube_id != "" ) {
		var tasks = {"tasks": [
			{"task": "cube.delete", "id": model_detail.id, "cubeid": cube_id },
			{"task": "cube.create", "id": model_detail.id, "name": cube }
		]};
	} else {
		var tasks = {"tasks": [
			{"task": "cube.create", "id":model_detail.id, "name": cube }
		]};
	}
	
	query(serviceModelName,tasks,continueBuild_callback);
		
}

var cube_id = "";

function continueBuild_callback(data) {
	
	var results = JSON.parse(data);
	cube_id = results['results'][0]['id'];
	
	if( cube_id == null ) {
		cube_id = results['results'][1]['id'];
	}
	
	updateDefinition("target_cube", cube_id);
	
	saveWorkView();
	
}

function saveWorkView() {
	var workview = getDefinition("target_workview");
	var cube = getDefinition("target_cube");
	
	var method = listValue(document.getElementById('list_action'));
	if( method != "Create a new workview on an existing cube" ) {
		cube = cube_id;
	}
	
	var tasks = {"tasks": [
		{"task": "workview.create", "id":model_detail.id, "name": workview, "cube":cube }
	]};
	query(serviceModelName,tasks,saveWorkView_callback);
		
		
}

function saveWorkView_callback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		window.location = '/workview/editor/?id=' + model_detail.id + '&workview=' + results['results'][0]['id'];
	} else {
		alert(results['results'][0]['message']);
	}
}

methodChange();
cubeChange();
