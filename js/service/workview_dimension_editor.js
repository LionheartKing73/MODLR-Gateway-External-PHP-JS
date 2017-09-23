
var dimension_editor_json = {
		'name': 'New Dimension',
		'type': "N",
		'position': "title",
		'elements': []
};

var bRequiresSaving = false;
var backup_positioning = null;

function savePositioning(bCommit) {
    if( !bCommit ){
        bCommit = true;
    }
    
    var sortable1 = document.getElementById('sortable1');
    var sortable2 = document.getElementById('sortable2');
    var sortable3 = document.getElementById('sortable3');
    var sortable4 = document.getElementById('sortable4');
    if( workview_definition['positioning'] ) {
            backup_positioning = JSON.parse( JSON.stringify( workview_definition['positioning'] ));
    }
    translatePosition(sortable1,"titles");
    translatePosition(sortable2,"rows");
    translatePosition(sortable3,"columns");
    translatePosition(sortable4,"hidden");

    if( bCommit ) {
	workviewSave();
    }
}


function tabPageByNameText(findTabName ) {
	var par = window.myRibbon.boundingBox[0].childNodes[0].childNodes[2].childNodes[0].childNodes[0];
	for(var i = 0;i < par.childNodes.length ; i++ ) {
		var tab = par.childNodes[i];
		var tabName = tab.innerText.trim();
		if( findTabName == tabName ) {
			return tab;
		}
	}
	return null;
}

function findHeaderInCollection(col,headerid) {
	for(var k=0;k<col.length;k++) {
		if( col[k]['headerid'] ) {
			if(  parseInt(col[k]['headerid']) == parseInt(headerid) ) {
				return col[k];
			}
		}
	}
	return null;
}

function translatePosition(position, placementField) {
	if( ! workview_definition['positioning'] )
		workview_definition['positioning'] = new Object();
	
	
	workview_definition['positioning'][placementField] = [];
	var arr = workview_definition['positioning'][placementField];
	
	for(var i = 0;i < position.childNodes.length ; i++) {
		var obj = position.childNodes[i];
		var dimName = getDataset(obj,"dimension");
		
		if( dimName != "" ) {
			if( dimName == dimAdd ) {
			
			} else if( dimName == dimHeader ) {
				
				var headerid = getDataset(obj,"headerid");
				if( !headerid || headerid == "undefined" ) {
					workview_definition['header_counter']++;
					headerid = workview_definition['header_counter'];
					
					arr[arr.length] = {
						'type' : 'header',
						'headerid' : headerid
					};
				} else {
					var bFound = false;
					var existing = findHeaderInCollection(backup_positioning['rows'], headerid);
					if( existing == null ) 
						existing = findHeaderInCollection(backup_positioning['columns'], headerid);
					if( existing == null ) 
						existing = findHeaderInCollection(backup_positioning['titles'], headerid);
					if( existing == null ) 
						existing = findHeaderInCollection(backup_positioning['hidden'], headerid);
					
					if( existing == null ) {
						arr[arr.length] = {
							'type' : 'header',
							'headerid' : headerid
						};
					} else {
						arr[arr.length] = existing;
					}
				}
				
				
			} else {
				//is a dimension position
			
				var dim = dimByName(dimName); 
			
				if( !workview_definition['dimensions'] )
					workview_definition['dimensions'] = {};
				if( !workview_definition['dimensions'][dim.id] )
					workview_definition['dimensions'][dim.id] = {};
			
				workview_definition['dimensions'][dim.id]['positioning'] = placementField;
				
				//check if the dimension is not already in this position.
				
				arr[arr.length] = {
					'type' : 'dimension',
					'id' : dim['id']
				};
				
				//if this dimension doesnt have any sets on it
				if( typeof workview_definition['dimensions'][dim.id]['set'] === "undefined" ) {
					buildDefaultSet(dim);
				}
			}
		}
	}
}

function buildDefaultSet(dim) {
	var defHierarchy = hierarchyMetaByName(dim,"Default");
	if( defHierarchy == null ) {	//if we have a default hierarchy
		//get the first hierarchy
		/*
		//cant as the server only provides metadata for the default hierarchy.
		if( dim.hierarchies.length > 0 ) {
			defHierarchy = dim.hierarchies[0];
		}
		*/
	}
	
	if( !workview_definition['dimensions'][dim.id]['set'] )
		workview_definition['dimensions'][dim.id]['set'] = [];
		
	var setNo = 0;
	if( !workview_definition['dimensions'][dim.id]['set'][setNo] )
		workview_definition['dimensions'][dim.id]['set'][setNo] = {};

	if( dim.type == "measure") {
		//check that this dimension has a default hierarchy.
		var setInstructions = [];
		var list = [];
		for(var k=0;k<dim.elements.length;k++) {
			var elm = dim.elements[k].name;
			list[list.length] = {"name" : elm, "level" : "0" };
			if( dim.elements[k].childrenCount > 0 ) {
				break;	//only add the first element if it is a parent.
			} else {
				if( k > 9 ) {
					break;
				}
			}
		} 
		setInstructions[0] = {"action" : "set", "set" : list};
		
		if( dim.aliases.length > 0 ) {
			setInstructions[1] = {"action" : "use-alias:" + dim.aliases[0].name};
		}
		
		
		workview_definition['dimensions'][dim.id]['set'][setNo]['hierarchy'] = "";
		workview_definition['dimensions'][dim.id]['set'][setNo]['instructions'] = setInstructions;
		workview_definition['dimensions'][dim.id]['set'][setNo]['element'] = null;
	} else if( defHierarchy != null ) {
		//check that this dimension has a default hierarchy.
		var setInstructions = [];
		var list = [];
		for(var k=0;k<defHierarchy.root.length;k++) {
			var elm = defHierarchy.root[k].name;
			list[list.length] = {"name" : elm, "level" : "0" };
			if( defHierarchy.root[k].childrenCount > 0 ) {
				break;	//only add the first element if it is a parent.
			} else {
				if( k > 9 ) {
					break;
				}
			}
		} 
        if( defHierarchy.root.length == 0 )
            list[list.length] = {"name" : "", "level" : "0" };
		setInstructions[0] = {"action" : "set", "set" : list};
		setInstructions[1] = {"action" : "drill-down"};
		
		if( dim.aliases.length > 0 ) {
			setInstructions[2] = {"action" : "use-alias:" + dim.aliases[0].name};
		}
		
		
		workview_definition['dimensions'][dim.id]['set'][setNo]['hierarchy'] = "Default";
		workview_definition['dimensions'][dim.id]['set'][setNo]['instructions'] = setInstructions;
		workview_definition['dimensions'][dim.id]['set'][setNo]['element'] = null;
	}

}


var dimensionToRemove = "";
function removeDimension(e) {
	dimensionToRemove = getDataset( e.parentNode.parentNode,"dimension");

	document.getElementById('spanRemovalDim').innerHTML = dimensionToRemove;
	
	var removeButtonText = "Remove " + dimensionToRemove + " Dimension";
	
	$( "#dialog-confirm-dimension-removal" ).dialog({
		resizable: false,
		height:230,
		modal: true,
        autoOpen: true,
		buttons: {
			"Remove Dimension" : function() {
				dimenisonRemove(dimensionToRemove);
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
}

function removeHeader(e) {
	e.parentNode.parentNode.removeChild(e.parentNode);
	
	savePositioning();
}

function populatePosition(position, placementField) {
	
	if( !workview_definition['positioning'][placementField] )
		workview_definition['positioning'][placementField] = [];
	
	var arr = workview_definition['positioning'][placementField];
	for(var i = 0;i < arr.length ; i++) {
		var obj = arr[i];
		if( obj.type == "header" ) {
			var elm = document.createElement("li");
			elm.setAttribute('class','draggable');	
			elm.setAttribute('style','border:1px solid #333;background:null;background-color:#6d89ba;color:#fff;');
			setDataset(elm,"headerid",obj['headerid']);
			elm.innerHTML = dimHeader + "<img src='/img/icons/16/cross-button.png' style='float:right;bottom: 1px;position:relative;' onclick='removeHeader(this)'/>";
			position.appendChild(elm);
		} else if( obj.type == "dimension" ) {
			var dim = dimById(obj.id);
			if( dim == null ) {
				//the dimension no longer exists within the cube.
				//alert("The underlying cube structure has changed since this workview was last opened. The workview has been amended to cater for the changes.");
				bRequiresSaving = true;
			} else {
				var elm = document.createElement("li");
				elm.setAttribute('class','ui-state-default draggable');	
				elm.innerHTML = "<span style='float:right;bottom: 1px;position:relative;'><img src='/img/icons/16/block--pencil.png' onclick='editDimension(this)'/><img src='/img/icons/16/cross-button.png' onclick='removeDimension(this)'/></span>" + dim.name;
				position.appendChild(elm);
				setDataset(elm,"dimension",dim.name);
				
				workview_definition['dimensions'][dim.id]['positioning'] = placementField;
			}
		}
	}
}


function dimensionExistsInSet(sortable, dimensionName) {
	for(var i = 0;i < sortable.childNodes.length ; i++) {
		var obj = sortable.childNodes[i];
		var dimName = getDataset( obj,"dimension");
		
		if( dimName == dimensionName ) {
			return true;
		}
	}
	return false;
}


function addNewDimensionsToExistingSchema() {
	var sortable1 = document.getElementById('sortable1');
	var sortable2 = document.getElementById('sortable2');
	var sortable3 = document.getElementById('sortable3');
	var sortable4 = document.getElementById('sortable4');
	
	for(var i=0;i<workview_metadata['dimensions'].length;i++) {
		
		var dim = workview_metadata['dimensions'][i];
		
		var bFound = dimensionExistsInSet(sortable1,dim['name']);
		if( !bFound ) 
			bFound = dimensionExistsInSet(sortable2,dim['name']);
		if( !bFound ) 
			bFound = dimensionExistsInSet(sortable3,dim['name']);
		if( !bFound ) 
			bFound = dimensionExistsInSet(sortable4,dim['name']);
		if( !bFound ) {
			
			addDimensionAtPosition(dim['name'],sortable1);
			bRequiresSaving = true;
			
		}
	}
}

var bDisplayingLeftPanel = false;
function displayManageDimensions() {
	if( bDisplayingLeftPanel ) {
		$('#left-sidebar').stop(true).animate({width:0});
		$('#workview').stop(true).animate({marginLeft:0});
		
		setTimeout(function() {
			$('#left-sidebar').css("overflow-y","hidden");
		}, 1000);
	} else {
		$('#left-sidebar').stop(true).animate({width:240});
		$('#workview').stop(true).animate({marginLeft:240});
		
		setTimeout(function() {
			$('#left-sidebar').css("overflow-y","scroll");
		}, 1000);
	}
	bDisplayingLeftPanel = !bDisplayingLeftPanel;
}

function updateManageDimensions() {
	bRequiresSaving = false;
	
	var sortable1 = document.getElementById('sortable1');
	var sortable2 = document.getElementById('sortable2');
	var sortable3 = document.getElementById('sortable3');
	var sortable4 = document.getElementById('sortable4');
	
	sortable1.innerHTML = '';
	sortable2.innerHTML = '';
	sortable3.innerHTML = '';
	sortable4.innerHTML = '';
	
	if( getDefinitionPosition('positioning','rows') != null ) {
		
		//these will populate but also filter out dimensions which have been deleted since the last load.
		populatePosition(sortable1, 'titles');
		populatePosition(sortable2, 'rows');
		populatePosition(sortable3, 'columns');
		populatePosition(sortable4, 'hidden');
		
		//add any late addition dimensions
		addNewDimensionsToExistingSchema();
		
		if( bRequiresSaving ) 
			savePositioning();
	} else {
		//autoposition
		var dim = null;
		
		var timeDims = getDimensionsOfType("time");
		if( timeDims.length > 0 ) {
			dim = timeDims[0];
			addDimensionAtPosition(dim['name'],sortable1);
			for(var i=1;i<timeDims.length;i++) {
				dim = timeDims[i];
				addDimensionAtPosition(dim['name'],sortable1);
			}
		}
		
		var scenarioDims = getDimensionsOfType("scenario");
		if( scenarioDims.length > 0 ) {
			dim = scenarioDims[0];
			addDimensionAtPosition(dim['name'],sortable1);
			for(var i=1;i<scenarioDims.length;i++) {
				dim = scenarioDims[i];
				addDimensionAtPosition(dim['name'],sortable1);
			}
		}
					
		var measuresDims = getDimensionsOfType("measure");
		if( measuresDims.length > 0 ) {
			dim = measuresDims[0];
			addDimensionAtPosition(dim['name'],sortable3);
			for(var i=1;i<measuresDims.length;i++) {
				dim = measuresDims[i];
				addDimensionAtPosition(dim['name'],sortable1);
			}
		}
					
		var geographyDims = getDimensionsOfType("geography");
		if( geographyDims.length > 0 ) {
			dim = geographyDims[0];
			addDimensionAtPosition(dim['name'],sortable1);
			for(var i=1;i<geographyDims.length;i++) {
				dim = geographyDims[i];
				addDimensionAtPosition(dim['name'],sortable1);
			}
		}
		
		var standardDims = getDimensionsOfType("standard");
		if( standardDims.length > 0 ) {
			dim = standardDims[standardDims.length-1];
			addDimensionAtPosition(dim['name'],sortable2);
			for(var i=0;i<standardDims.length-1;i++) {
				dim = standardDims[i];
				addDimensionAtPosition(dim['name'],sortable1);
			}
		}
		savePositioning();
	}
	
}


function addDimensionAtPosition(name, position) {
    
    var elm = document.createElement("li");
    elm.setAttribute('class','ui-state-default draggable');	
    elm.innerHTML = "<span style='float:right;bottom: 1px;position:relative;'><img src='/img/icons/16/block--pencil.png' onclick='editDimension(this)'/><img src='/img/icons/16/cross-button.png' onclick='removeDimension(this)'/></span>" + name;
    position.appendChild(elm);

    setDataset(elm,"dimension",name);
}

function dimenisonAddSelectedExisting() {
    var existingDim =  $('#dimension-select option:selected').text();
    dimension_editor_json['name'] = existingDim;
    dimensionAddExistingByName(existingDim);
	
}


function displayDimensionPicker(type, position) {
    dimension_editor_json['position'] = position;
    
    //get up-to-date dimension listing
    var tasks = {"tasks": [
            {"task": "model.get", "id" : model_detail.id }
    ]};
    query("model.service",tasks,displayDimensionPickerCallback);

	
}

function displayDimensionPickerCallback(data) {
	
	var results = JSON.parse(data);
	model_detail = results['results'][0]['model'];
	
	
	
	var dims = [];
	for(var i=0;i<model_detail['dimensions'].length;i++) {
		var dim = model_detail['dimensions'][i];
		dims[dims.length] = dim['name'];
		
	}
	dims.sort();
	
	$('#dimension-select option').remove();
	for(var i=0;i<dims.length;i++) {
		var dim = dims[i];
		var dimDef = dimByName(dim);
		if( dimDef == null ) {
			$('#dimension-select').append('<option>' + dim + '</option>');
		}
	}

	//dimension selector
    $('#dimension-select').chosen({
    	width: '280px;',
    	height: '180px;'
    }).trigger("chosen:updated");
    

	$( "#dialog-add-existing-dimension" ).dialog({
		resizable: false,
		height:380,
		modal: true,
        autoOpen: true,
		buttons: {
			"Add Dimension" : function() {
				dimenisonAddSelectedExisting();
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
}

function displayDimensionEditor(type, position) {
	dimension_editor_json = {
		'name': 'New Dimension',
		'type': type,
		'position': position,
		'elements' : []
	};
	
	
	document.getElementById('dimEditorMessage').innerHTML = "";
	document.getElementById('divUpdateOptions').style.display = 'none';
	document.getElementById('divDimName').style.display = 'block';
	document.getElementById('dimension-elements').value = "Parent Element\n\tChild Element";
	$( "#dlgDimensionEditor" ).dialog({
      resizable: false,
      height:500,
      width:800,
      modal: true,
      autoOpen: true,
      buttons: {
        	'Save': function(event) {
        		dimensionSave();
       	 	},
       	 	'Cancel': function(event) {
            	$( this ).dialog( "close" );
       	 	}
    	}
    });
    
    
}


var dimensionToEdit = "";
var hierarchyToEdit = "";
var bNewHier = false;
function editDimension(e) {
	bNewHier = false;
	hierarchyToEdit = "";
	dimensionToEdit = getDataset( e.parentNode.parentNode,"dimension");
	var dimDef = dimByName(dimensionToEdit);
	
	
	document.getElementById('spanDimName').innerHTML = dimensionToEdit;
	
	document.getElementById('dimEditorMessage').innerHTML = "";
	//document.getElementById('chkDimClean').checked = false;
	
	//displayDimensionEditorLoad
	
	var tasks = {"tasks": [
		{"task": "dimension.get", "id":model_detail.id,  "dimensionid": dimDef['id'] }
	]};
	query("model.service",tasks,displayDimensionEditorLoad);
}



function recursiveExtractHierarchy(node, indent) {
	var type = "";
	if( node['type'] == "S" ) 
		type = "[S]";
	
	var str = indent + type + node['name'] + '\n';
	var children = node['children'];
	for(var i=0;i<children.length;i++) {
		str += recursiveExtractHierarchy(children[i],indent + '\t');
	}	
	return str;
}


var hierarchiesData = null;
var strHierarchies = "";
function displayDimensionEditorLoad(data) {
	var results = JSON.parse(data);
	hierarchiesData = results;
	
	var str = "";
	strHierarchies = "";
	if( results['results'][0]['hierarchies'] ) {
		var hierarchies = results['results'][0]['hierarchies'];
		if( hierarchies.length > 0 ) {
			var hier = hierarchies[0];
			hierarchyToEdit = hier['name'];
			if( hier.root.length > 0 ) {
				
				for(var i=0;i<hier.root.length;i++) {
					str += recursiveExtractHierarchy(hier.root[i],'');
				}
				
				
			}
			
			for(var i=0;i<hierarchies.length;i++) {
				var hier = hierarchies[i];
				strHierarchies += "<option>" + hier['name'] + "</option>";
			}
		}
	}
	
	if( hierarchyToEdit == "" ) {
		hierarchyToEdit = "Default";
	}
	
	
	dimension_editor_json = {
		'name': dimensionToEdit,
		'elements' : []
	};
	
	
	document.getElementById('spanHierName').innerHTML = "<select id='hierarchy-to-edit'>" + strHierarchies + "</select>";
	
	$('#hierarchy-to-edit').chosen({
    	width: '210px;',
    	height: '180px;'
    });
    
    $('#hierarchy-to-edit').change( function() { 
        updateDimensionEditHierarchy(); 
    }); 
	
	document.getElementById('divDimName').style.display = 'none';
	document.getElementById('divUpdateOptions').style.display = 'block';
	
	
	document.getElementById('dimension-elements').value = str;
	$( "#dlgDimensionEditor" ).dialog({
      resizable: false,
      height:500,
      width:800,
      autoOpen: true,
      modal: true,
      buttons: {
        	'Save': function(event) {
        		dimensionSaveHierarchy();
       	 	},
       	 	'Cancel': function(event) {
            	$( this ).dialog( "close" );
       	 	}
    	}
    });
    
	
}

function btnAddHierarchy() {
	bNewHier = true;
	document.getElementById('spanHierName').innerHTML = "<input type='text'  style='width:210px;' id='new-hierarchy-name' value='New Hierarchy' />";
}

function btnRemoveHierarchy() {
	if( bNewHier ) {
		bNewHier = false;
		//if adding a new hier, clicking remove actually restores the selection list
		document.getElementById('spanHierName').innerHTML = "<select id='hierarchy-to-edit'>" + strHierarchies + "</select>";
	
		$('#hierarchy-to-edit').chosen({
			width: '210px;',
			height: '180px;'
		});
	
		$('#hierarchy-to-edit').change( function() { 
			updateDimensionEditHierarchy(); 
		}); 
		
		updateDimensionEditHierarchy(); 
		return;
	}
	
	hierarchyToEdit =  $('#hierarchy-to-edit option:selected').text();
	document.getElementById('spanRemovalHier').innerHTML = hierarchyToEdit;
	
	var removeButtonText = "Remove " + hierarchyToEdit + " Dimension";
	
	$( "#dialog-confirm-hierarchy-removal" ).dialog({
		resizable: false,
		height:230,
        autoOpen: true,
		modal: true,
		buttons: {
			"Remove Hierarchy" : function() {
				hierarchyRemove(dimensionToEdit,hierarchyToEdit);
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
}


function hierarchyRemove(dimName, hierName ) {

	$( "#dlgDimensionEditor" ).dialog( "close" );
	
	var dimDef = dimByName(dimName);
	
	var tasks = {"tasks": [
		{"task": "dimension.delete.hierarchy", "id":model_detail.id, "dimensionid": dimDef['id'] , "hierarchyname":hierName  }
	]};
	query("model.service",tasks,hierarchyRemoveCallback);	
	
}

function hierarchyRemoveCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		
		updateMetadata(updateManageDimensions);
	} else {
		var error = results['results'][0]['message'];
		if( !error )
			error = results['results'][0]['error'];
		
		alert(error);
	}
}

function updateDimensionEditHierarchy() {
	hierarchyToEdit =  $('#hierarchy-to-edit option:selected').text();
	
	var str = "";
	if( hierarchiesData['results'][0]['hierarchies'] ) {
		var hierarchies = hierarchiesData['results'][0]['hierarchies'];
		if( hierarchies.length > 0 ) {
			for(var k=0;k<hierarchies.length;k++) {
				var hier = hierarchies[k];
				if( hierarchyToEdit == hier['name'] ) {
					if( hier.root.length > 0 ) {
						for(var i=0;i<hier.root.length;i++) {
							str += recursiveExtractHierarchy(hier.root[i],'');
						}
					}
				}
			}
		}
	}
	
	document.getElementById('dimension-elements').value = str;
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

function dimensionSaveHierarchy() {
	
	if( bNewHier ) {
		hierarchyToEdit = document.getElementById('new-hierarchy-name').value;
	}	
	
	//update the definition with the textareas elements
	var elmStr = document.getElementById('dimension-elements').value;
	var elmArray = elmStr.split("\n");

	//create the root hierarchy definition
	var defaultHier = {"root" : [], "name" : hierarchyToEdit };
	var root = buildElementTree(elmArray);
	
	defaultHier['root'] = root;
	
	//extract N level into the standard set
	var elements = [];
	for(var i=0;i<root.length;i++) {
		elements = dimensionSetAddNsToArray(root[i],elements);
	}
	
	dimension_editor_json['elements'] = elements;
	var dimDef = dimByName(dimensionToEdit);
	
	
	var hierarchies = [];
	hierarchies[hierarchies.length] = defaultHier;
	dimension_editor_json['hierarchies'] = hierarchies;
	
	//var chkCleanUpDimension = document.getElementById('chkDimClean').checked;
	var clean = "0";
	//if( chkCleanUpDimension ) 
	//	clean = "1";
	 
	//create the task and update the dimension.
	var tasks = {"tasks": [
		{"task": "dimension.update.hierarchy", "id":model_detail.id, "dimensionid":dimDef['id'], "definition": defaultHier, "wipedimension" : clean }
	]};
	query("model.service",tasks,dimensionSaveCallback);	
}

function dimenisonRemove(dimName ) {
	var dimDef = dimByName(dimName);
	
	var tasks = {"tasks": [
		{"task": "cube.dimensionremove", "id":model_detail.id, "cubeid":getDefinition('cube'), "dimensionid": dimDef['id'] }
	]};
	query("model.service",tasks,dimenisonRemoveCallback);		
}

function dimenisonRemoveCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		//TODO: remove the dimension from the positioning
		
		updateMetadata(updateManageDimensions);
	} else {
		var error = results['results'][0]['message'];
		if( !error )
			error = results['results'][0]['error'];
		
		alert(error);
	}
}

function dimensionAddExistingByName(dimName) {
	//work out id from name
	var dimid = '';
	for(var i=0;i<model_detail['dimensions'].length;i++) {
		var dim = model_detail['dimensions'][i];
		
		if( dim['name'] == dimName )
			dimId = dim['id'];
	}
	
	var tasks = {"tasks": [
		{"task": "cube.dimensionadd", "id":model_detail.id, "cubeid":getDefinition('cube'), "dimensionid": dimId }
	]};
	query("model.service",tasks,dimensionAddExistingByNameCallback);	
}

function dimensionAddExistingByNameCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		//$( "#dlgDimensionEditor" ).dialog( "close" );
		
		var position = dimension_editor_json['position'];
		var posId = 'sortable1';
		if( position == "rows" ) 
			posId = 'sortable2';
		if( position == "columns" ) 
			posId = 'sortable3';
		if( position == "hidden" ) 
			posId = 'sortable4';
	
		var sortable = document.getElementById(posId);
		var elm = document.createElement("li");
		elm.setAttribute('class','ui-state-default draggable');	
		elm.innerHTML = dimension_editor_json['name'];
		sortable.appendChild(elm);
		setDataset(elm,"dimension",dimension_editor_json['name']);
		
		updateMetadata(savePositioning);
                //savePositioning(false);
		
	} else {
		var error = results['results'][0]['message'];
		if( !error )
			error = results['results'][0]['error'];
		
		alert(error);
	}
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

function dimensionSave() {

	//update the definition with the textareas elements
	var elmStr = document.getElementById('dimension-elements').value;
	var elmArray = elmStr.split("\n");

	//create the root hierarchy definition
	var defaultHier = {"root" : [], "name" : "Default" };
	var root = buildElementTree(elmArray);
	
	defaultHier['root'] = root;
	
	//extract N level into the standard set
	var elements = [];
	for(var i=0;i<root.length;i++) {
		elements = dimensionSetAddNsToArray(root[i],elements);
	}
	
	dimension_editor_json['elements'] = elements;
	dimension_editor_json['name'] = document.getElementById('txtDim').value;
	
	
	var hierarchies = [];
	hierarchies[hierarchies.length] = defaultHier;
	dimension_editor_json['hierarchies'] = hierarchies;
	
	//create the task and update the dimension.
	var tasks = {"tasks": [
		{"task": "cube.dimensionaddbydefinition", "id":model_detail.id, "cubeid":getDefinition('cube'), "definition": dimension_editor_json }
	]};
	query("model.service",tasks,dimensionSaveCallback);	
}

function dimensionSaveCallback(data) {
	var results = JSON.parse(data);
	if( results['results'][0]['result'] == 1 ) {
		$( "#dlgDimensionEditor" ).dialog( "close" );
		
		if( results['results'][0].task == "dimension.update.hierarchy" ) {
			
			
		} else {
			var position = dimension_editor_json['position'];
			var posId = 'sortable1';
			if( position == "rows" ) 
				posId = 'sortable2';
			if( position == "columns" ) 
				posId = 'sortable3';
			if( position == "hidden" ) 
				posId = 'sortable4';
		
			var sortable = document.getElementById(posId);
			var elm = document.createElement("li");
			elm.setAttribute('class','ui-state-default draggable');	
			elm.innerHTML = dimension_editor_json['name'];
			sortable.appendChild(elm);
			setDataset(elm,"dimension",dimension_editor_json['name']);
		}
		updateMetadata(savePositioning);
	} else {
		document.getElementById('dimEditorMessage').style.display  = 'block';
		document.getElementById('dimEditorMessage').style.color  = 'red';
		document.getElementById('dimEditorMessage').style.fontSize = '14px';
		
		var error = results['results'][0]['message'];
		if( !error )
			error = results['results'][0]['error'];
		
		document.getElementById('dimEditorMessage').innerHTML = "<b>Error:</b> " + error;
	}
}

//document load object setups
$(function() {
	
	$( "#txtDim" ).change(function() {
		 dimension_editor_json['name'] = this.value;
	});
	
	//dimension editor
    $( "#sortable_elements" ).sortable({
      revert: true,
      dropOnEmpty: true,
      connectWith: "#delete_elements"
    });
    
    $( "#delete_elements" ).sortable({
    	revert: false,
    	dropOnEmpty: true,
      	connectWith: "#sortable_elements",
      	receive: function( event, ui ) {
			
			var elements = dimension_editor_json['elements'];
			for(var i=0;i<elements.length;i++) {
				if( elements[i].name.trim().toLowerCase() == ui.item[0].innerText.trim().toLowerCase() ) {
					elements.splice(i,1);
					break;
				}
			}
			dimension_editor_json['elements'] = elements;
        	document.getElementById('delete_elements').innerHTML = '';
		}
    });
    
    
    
    
	//dimension Manager
    $( "#dimTabs" ).tabs({ selected: 1 });
    
    /*
    var txt = document.getElementById('txtElm');
    txt.addEventListener("keypress", function() {
    
		if (event.keyCode == 13) dimensionElementAdd();
	});
	*/
	
	enableTab('dimension-elements');
	

	
	//workview editor
    $( "#sortable1" ).sortable({
      revert: true,
      connectWith: "#sortable2,#sortable3,#sortable4",
      dropOnEmpty: true,
      stop: function( event, ui ) {
      	if( getDataset(ui.item[0],"dimension") == dimAdd ) {
      		
      		ui.item[0].parentNode.removeChild(ui.item[0]);
        	displayDimensionEditor("add","titles");
      		
      	} else {
      		if( getDataset(ui.item[0],"dimension") == dimAddExisting ) {
      			ui.item[0].parentNode.removeChild(ui.item[0]);
				displayDimensionPicker("add","titles");
      			
      		}
      	}
      	savePositioning();
      }
    });
    $( "#sortable2" ).sortable({
      revert: true,
      connectWith: "#sortable1,#sortable3,#sortable4",
      dropOnEmpty: true,
      stop: function( event, ui ) {
      	if( getDataset(ui.item[0],"dimension") == dimAdd ) {
      		ui.item[0].parentNode.removeChild(ui.item[0]);
        	displayDimensionEditor("add","rows");
      	} else {
      		if( getDataset(ui.item[0],"dimension") == dimAddExisting ) {
      			ui.item[0].parentNode.removeChild(ui.item[0]);
				displayDimensionPicker("add","rows");
      		}
      	}
      	savePositioning();
      }
    });
    $( "#sortable3" ).sortable({
      revert: true,
      connectWith: "#sortable1,#sortable2,#sortable4",
      dropOnEmpty: true,
      stop: function( event, ui ) {
      	if( getDataset(ui.item[0],"dimension") == dimAdd ) {
      		ui.item[0].parentNode.removeChild(ui.item[0]);
        	displayDimensionEditor("add","columns");
        	
      	} else {
      		if( getDataset(ui.item[0],"dimension") == dimAddExisting ) {
      			ui.item[0].parentNode.removeChild(ui.item[0]);
				displayDimensionPicker("add","columns");
      		}
      	}
      	savePositioning();
      }
    });
    $( "#sortable4" ).sortable({
      revert: true,
      connectWith: "#sortable1,#sortable2,#sortable3",
      dropOnEmpty: true,
      stop: function( event, ui ) {
      	if( getDataset(ui.item[0],"dimension") == dimAdd ) {
      		ui.item[0].parentNode.removeChild(ui.item[0]);
        	displayDimensionEditor("add","hidden");
        	
      	} else {
      		if( getDataset(ui.item[0],"dimension") == dimAddExisting ) {
      			ui.item[0].parentNode.removeChild(ui.item[0]);
				displayDimensionPicker("add","hidden");
      		}
      	}
      	savePositioning();
      }
    });
    
    
    $( "#draggable_header" ).draggable({
      connectToSortable: "#sortable1,#sortable2,#sortable3,#sortable4",
      helper: "clone",
      //revert: "invalid",
      stop: function( event, ui ) {
        	savePositioning();
      }
    });
    
    $( "#draggable_dimension" ).draggable({
      connectToSortable: "#sortable1,#sortable2,#sortable3,#sortable4",
      helper: "clone",
      //revert: "invalid",
      stop: function( event, ui ) {
        	//event.toElement.remove();
      }
    });
    
    $( "#draggable_dimension_existing" ).draggable({
      connectToSortable: "#sortable1,#sortable2,#sortable3,#sortable4",
      helper: "clone",
      //revert: "invalid",
      stop: function( event, ui ) {
        	//event.toElement.remove();
      }
    });
    
    $( "ul, li" ).disableSelection();
});

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

