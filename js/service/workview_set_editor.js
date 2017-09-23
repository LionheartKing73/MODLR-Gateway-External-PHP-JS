

var setMacros = [];
setMacros["blank"] = "Add Blank Space";
setMacros["expand"] = "Expand";
setMacros["expand-all"] = "Expand All";
setMacros["expand-next"] = "Expand using the Next Instruction";
setMacros["drill-down"] = "Enable Drill Down";
//setMacros["multiple-selection"] = "Enable Multiple Selection";
setMacros["indent"] = "Indent prior set";
setMacros["reset-indents"] = "Reset prior set Indents";
setMacros["remove"] = "Remove Items using the Next Instruction";
setMacros["remove-all-consolidations"] = "Remove Consolidations";
setMacros["sort-by-name"] = "Sort by Name";
setMacros["suppress-zeros"] = "Suppress Empty Elements";
setMacros["suppress-zeros-partial"] = "Suppress Empty Elements (Except One)";
setMacros["hide-dimension"] = "Hide Dimension Headers";
setMacros["expand-above"] = "Reverse (Subtotals at the Bottom)";
setMacros["format-2dp"] = "Format 0,000.00";
setMacros["format-1dp"] = "Format 0,000.0";
setMacros["format-0dp"] = "Format 0,000";
setMacros["format-percent-3dp"] = "Format 0.000%";
setMacros["format-percent-2dp"] = "Format 0.00%";
setMacros["format-percent-1dp"] = "Format 0.0%";
setMacros["format-percent-0dp"] = "Format 0%";
setMacros["conditional-format"] = "Conditional Format";
setMacros["format-break"] = "Ignore New Formatting on Prior Members";

var setMacroCategories = [
	{"name" : "Set Formatting", "items" : ["blank","indent","reset-indents","expand-above","drill-down"]},
	{"name" : "Set Manipulation", "items" : ["expand","expand-next","expand-all","remove","remove-all-consolidations","sort-by-name"]},
	{"name" : "Data Formatting", "items" : ["format-2dp","format-1dp","format-0dp","format-percent-3dp","format-percent-2dp","format-percent-1dp","format-percent-0dp","format-break"]},
	{"name" : "Advanced Functions", "items" : ["hide-dimension","suppress-zeros","conditional-format"]}
	
	
];



function updateAdvancedFunctionList() {
	
	$('#instruction-function option').remove();
	$('#instruction-function option').empty();
	$('#instruction-function optgroup').remove();
	$('#instruction-function optgroup').empty();
	
	for(var i=0;i<setMacroCategories.length;i++) {
		var cat = setMacroCategories[i];
		var optGroup = $('<optgroup>').attr('label', cat['name']);
		$('#instruction-function').append(optGroup);
		
		for(var k=0;k<cat['items'].length;k++) {
			var item = cat['items'][k];
			$(optGroup).append(new Option(setMacros[item], item));
		}
	}
	
	/*
	var keys = [];
	for (var key in setMacros) {
  		$('#instruction-function').append(new Option(setMacros[key], key));
	}
	*/
	
	if( dimSelected.aliases.length > 0 ) {
		var optGroup = $('<optgroup>').attr('label', "Element Formatting");
		$('#instruction-function').append(optGroup);
		for(var i=0;i<dimSelected.aliases.length;i++){
			$(optGroup).append(new Option("Show Alias: " + dimSelected.aliases[i].name, "use-alias:" + dimSelected.aliases[i].name));
		}
	}
	
	if( dimSelected.hierarchies.length > 0 ) {
		var optGroup = $('<optgroup>').attr('label', "Sequential Functions");
		$('#instruction-function').append(optGroup);
		
		for(var i=0;i<dimSelected.hierarchies.length;i++){
			$(optGroup).append(new Option("Insert Previous Member from: " + dimSelected.hierarchies[i].name, "insert-previous:" + dimSelected.hierarchies[i].name));
			$(optGroup).append(new Option("Insert Next Member from: " + dimSelected.hierarchies[i].name, "insert-next:" + dimSelected.hierarchies[i].name));
		}
	}
	
	if( dimSelected.type == "time" ) {
		var optGroup = $('<optgroup>').attr('label', "Time Functions");
		$('#instruction-function').append(optGroup);
		
		$(optGroup).append(new Option("Enable Range Selection", "time-range"));
	}
	
	if( workview_metadata.variables.length > 0 ) {
		var optGroup = $('<optgroup>').attr('label', "Model Variables");
		$('#instruction-function').append(optGroup);
		
		for(var i=0;i<workview_metadata.variables.length;i++){
			var variable_key = workview_metadata.variables[i].key;
			if( variable_key.trim().toLowerCase().indexOf(dimSelected.name.toLowerCase()) > -1 ) {
				$(optGroup).append(new Option("Insert Variable: " + variable_key, "insert-variable:" + variable_key));
			}
		}
	}
	
	if( workview_metadata.styles.length > 0 ) {
		var optGroup = $('<optgroup>').attr('label', "Model Styles");
		$('#instruction-function').append(optGroup);
		
		for(var i=0;i<model_detail.styles.length;i++){
			$(optGroup).append(new Option("Style Cells: " + model_detail.styles[i].name, "insert-cell-style:" + model_detail.styles[i].name));
		}
		for(var i=0;i<model_detail.styles.length;i++){
			$(optGroup).append(new Option("Style Headings: " + model_detail.styles[i].name, "insert-heading-style:" + model_detail.styles[i].name));
		}
	}
	
}




var bLoadingSetEditor = false;

function getDimensionsSelected() {
	var sel = $(".ui-selected");
	var dims = [];
	
	for(var i=0;i<sel.length;i++) {
		var selection = sel[i];
		
		if( getDataset(selection, "id") == "undefined" ) {
		
		} else {
			dims[dims.length] = dimById(getDataset(selection, "id"));
		}
	}
	
	var uniqueDims = [];
	$.each(dims, function(i, el){
    	if($.inArray(el, uniqueDims) === -1) uniqueDims.push(el);
	});
	
	return uniqueDims;
}

var bAdvancedEditor = false;
var dimSelected = null;

var setInstructions = [];
function displayDimensionElementPicker(dimension_id) {
	if( bLoadingSetEditor )
		return;
	
	
	bLoadingSetEditor = true;
	
	hierarchiesData = null;
	bAdvancedEditor = false;
	setInstructions = [];
	
	$('#hierarchy-select option').remove();
	$('#hierarchy-select option').empty();
	
	$("#sortable-instructions").html("");
	
	var dim = null;
	
	if( dimension_id ) {
		dim = dimById(dimension_id);
	} else {
		var dims = getDimensionsSelected();
		if( dims.length != 1 ) {
			bLoadingSetEditor = false;
			return;
		}
		dim = dims[0];	
	}
	dimSelected = dim;
	
	var hiers = [];
	for(var i=0;i<dim['hierarchies'].length;i++) {
		var hier = dim['hierarchies'][i];
		hiers[hiers.length] = hier['name'];
		
	}
	hiers.sort();
	
	$('#hierarchy-select').append('<option>No Hierarchy</option>');
	for(var i=0;i<hiers.length;i++) {
		var hier = hiers[i];
		if( hier == "Default" ) {
			$('#hierarchy-select').append('<option selected>' + hier + '</option>');
			$('#hierarchy-select').val(hier).trigger('chosen:updated');
		} else {
			$('#hierarchy-select').append('<option>' + hier + '</option>');
		}
	}
	
	//dimension selector
    $('#hierarchy-select').chosen({
    	width: '324px;',
    	height: '180px;'
    }).trigger("chosen:updated");
	
	$("#tdAdvancedEditor").css('display','none');
    
    $('#hierarchy-select').change( function() { 
        updateDimensionSelectionHierarchy(); 

    }); 
    
	
	updateAdvancedFunctionList();
	updateDimensionSelectionHierarchy();
	document.activeElement.blur();
	
	
}


function loadSet() {
	$("#sortable-instructions").html("");
	var sel = $(".ui-selected");
	sel.splice(0,0,$(".titleTdIcon-selected")[0]);
	for(var i=0;i<sel.length;i++) {
		if( dimSelected.id == getDataset(sel[i], "id") ) { //only read the dimension and set which has been selected
			if( !workview_definition['dimensions'][dimSelected.id]['set'] )
				workview_definition['dimensions'][dimSelected.id]['set'] = [];
				
			var setNo = parseInt(getDataset(sel[i], "set"));
			
			if( !workview_definition['dimensions'][dimSelected.id]['set'][setNo] )
				workview_definition['dimensions'][dimSelected.id]['set'][setNo] = {};
			
			if( workview_definition['dimensions'][dimSelected.id]['set'][setNo]['instructions'] ) {
				showAdvancedEditor();
				for(var k=0;k<workview_definition['dimensions'][dimSelected.id]['set'][setNo]['instructions'].length;k++) {
					var inst = workview_definition['dimensions'][dimSelected.id]['set'][setNo]['instructions'][k];
				
					var html = "";
				
					if( inst['action'] == "set" ) {
						var instructionStr = "Set - ";
					
						for(var i=0;i<inst['set'].length;i++){
							var leaf = inst['set'][i];
							var name = leaf["name"];
							instructionStr += "[" + name + "], ";
						}
						instructionStr = instructionStr.substr(0,instructionStr.length-2);
						if( instructionStr.length > 35 ) {
							if( instructionStr.indexOf(", ",30) > -1 ) {
								instructionStr = instructionStr.substr(0, instructionStr.indexOf(", ",30) ) + "...";
							}
						}
						instructionStr += " (x" + inst['set'].length + ")";
					
						html = '<li class="clearfix set-instruction" data-instruction="set" data-set=\'' + JSON.stringify(inst['set']) + '\'><span class="drag-marker"><i></i></span><p class="instruction-title">' + instructionStr + '</p><div class="instruction-actionlist pull-right clearfix"><a href="#" class="instruction-remove" onclick="removeInstruction(this);"><i class="fa fa-times"></i></a></div></li>';
					} else {
						var text = setMacros[inst['action']];
						if( !text ) {
							if( inst['action'].indexOf("use-alias") > -1 ) {
								text = "Show Alias: " + inst['action'].substr(10, inst['action'].length-9);
							}
							if( inst['action'].indexOf("insert-previous") > -1 ) {
								text = "Insert Previous Member from: " + inst['action'].substr(16, inst['action'].length-15);
							}
							if( inst['action'].indexOf("insert-next") > -1 ) {
								text = "Insert Next Member from: " + inst['action'].substr(12, inst['action'].length-11);
							}
							if( inst['action'].indexOf("insert-variable") > -1 ) {
								text = "Insert Variable: " + inst['action'].substr(16, inst['action'].length-15);
							}
							if( inst['action'].indexOf("insert-heading-style") > -1 ) {
								text = "Style Headings: " + inst['action'].substr(21, inst['action'].length-20);
							}
							if( inst['action'].indexOf("insert-cell-style") > -1 ) {
								text = "Style Cells: " + inst['action'].substr(18, inst['action'].length-17);
							}
							if( inst['action'].indexOf("time-range") > -1 ) {
								text = "Enable Range Selection";
							}
							
							if( inst['action'].indexOf("conditional-format") > -1 ) {
								var items = inst['action'].split(":");
								var styleId = items[1];
								var styleLower = items[2];
								var styleUpper = items[3];
								
								var style = styleById(styleId);
								text = "Conditional Format: " + style['name'] + " between " + styleLower + " and " + styleUpper;
							}
							
						}
						
						html = '<li class="clearfix function set-instruction" data-instruction="' + inst['action'] + '"><span class="drag-marker"><i></i></span><p class="instruction-title">' + text + '</p><div class="instruction-actionlist pull-right clearfix"><a href="#" class="instruction-remove" onclick="removeInstruction(this);"><i class="fa fa-times"></i></a></div></li>';
					}
					$("#sortable-instructions").html($("#sortable-instructions").html()+html);
				
				
	
					$(".leaf.ui-selected").removeClass("ui-selected");
				
				}
			}
			
		}
	}
	
	$("#sortable-instructions").sortable({filter:"li"});
	$("#element-tree-view").selectable({ filter: "span.leaf"})
	
}

function showAdvancedEditor() {
	
	if( $("#tdAdvancedEditor").css('display') != 'block' ) {
	
		$("#tdAdvancedEditor").css('display','block');
	
		$( "#dialog-dimension-element-select" ).dialog({
			resizable: false,
			width: 700,
			height: 525,
			modal: true,
			title: "Dimension: " + dimSelected['name'],
			autoOpen: true,
			buttons: {
				Save: function() {
					saveDimensionElementAdvancedSelection();
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
	
	}
}

function addSetInstruction() {

	var leaves = $(".leaf.ui-selected");
	if( leaves.length == 0 ) {
		return;
	}
	
	showAdvancedEditor();
	
	var instructionStr = "Set - ";
	var list = [];
	
	var hier = $('#hierarchy-select option:selected').text();
	
	for(var i=0;i<leaves.length;i++){
		var leaf = leaves[i];
		var name = getDataset(leaf, "name");
		
		instructionStr += "[" + name + "], ";
		
		list[list.length] = {"name" : escapeHtml(name), "level" : getDataset(leaf,"level"), "children" : getDataset(leaf,"children"), "hierarchy" : hier };
	}
	instructionStr = instructionStr.substr(0,instructionStr.length-2);
	if( instructionStr.length > 35 ) {
		if( instructionStr.indexOf(", ",30) > -1 ) {
			instructionStr = instructionStr.substr(0, instructionStr.indexOf(", ",30) ) + "...";
		
		}
	}
	instructionStr += " (x" + leaves.length + ")";
	
	//setInstructions[0] = {"action" : "set", "set" : list};
	
	
	var html = '<li class="clearfix set-instruction" data-instruction="set" data-set=\'' + JSON.stringify(list) + '\'><span class="drag-marker"><i></i></span><p class="instruction-title">' + instructionStr + '</p><div class="instruction-actionlist pull-right clearfix"><a href="#" class="instruction-remove" onclick="removeInstruction(this);"><i class="fa fa-times"></i></a></div></li>';
	$("#sortable-instructions").html($("#sortable-instructions").html()+html);
	
	$(".leaf.ui-selected").removeClass("ui-selected");
	$("#sortable-instructions").sortable();
	
	
}

function addInstructionFunction() { 
	//
	var func = $('#instruction-function option:selected')[0].value;
	var funcText = $('#instruction-function option:selected').text();
	
	var text = setMacros[func];
	if( !text ) {
		text = funcText;
	}
	
	if( func == "conditional-format" ) {
		//open the conditional format dialog
		displayConditionalInstructionForm();
		return;
	}
	
	var html = '<li class="clearfix function set-instruction" data-instruction="' + func + '"><span class="drag-marker"><i></i></span><p class="instruction-title">' + text + '</p><div class="instruction-actionlist pull-right clearfix"><a href="#" class="instruction-remove" onclick="removeInstruction(this);"><i class="fa fa-times"></i></a></div></li>';
	$("#sortable-instructions").html($("#sortable-instructions").html()+html);
	
	$("#sortable-instructions").sortable();
}

function removeInstruction(obj) {
	$(obj.parentNode.parentNode).remove();
}

function saveDimensionElementAdvancedSelection() {
	bAdvancedEditor = true;
	
	if( $("li.set-instruction").length == 0 ) {
		bAdvancedEditor = false;
	}
	
	saveDimensionElementSelection();
}

function saveDimensionElementSelection() {
	var dim = dimSelected;
	var setInstructions = [];
	var hier = $('#hierarchy-select option:selected').text();
	
	if( bAdvancedEditor ) {
		
		var instructions = $("li.set-instruction");
		for(var i=0;i<instructions.length;i++) { 
			var inst = instructions[i];
			var action = getDataset(inst,"instruction");
			if( action == "set" ) {
				var setList = JSON.parse(getDataset(inst,"set"));
				setInstructions[setInstructions.length] = {"action" : "set", "set" : setList};
			} else {
				setInstructions[setInstructions.length] = {"action" : action};
			}
		
		
		}
		
	} else {
		var list = [];
		
		
		//this is a basic set, nothing more than that.
		var selElements = $( "span.ui-selected" );
		for(var i=0;i<selElements.length;i++) {
			var elm = selElements[i].innerText;
			list[list.length] = {"name" : elm, "level" : getDataset(selElements[i],"level") };
		}
		if( list.length == 0 ) {
			//user selected nothing, drill through tree returning non-hidden branches.
			var parentUnorderedList = document.getElementById('element-tree-view').childNodes[0];
			list = recurseListVisbileElements(parentUnorderedList, list);
		}
		setInstructions[0] = {"action" : "set", "set" : list};
	}
	
	var sel = $(".ui-selected");
	sel.splice(0,0,$(".titleTdIcon-selected")[0]);
	for(var i=0;i<sel.length;i++) {
		if( dim.id == getDataset(sel[i], "id") ) { //only update the dimension which has been selected, no others or spacers
			if( !workview_definition['dimensions'][dim.id]['set'] )
				workview_definition['dimensions'][dim.id]['set'] = [];
			
			if( !workview_definition['dimensions'][dim.id]['set'][parseInt(getDataset(sel[i], "set"))] )
				workview_definition['dimensions'][dim.id]['set'][parseInt(getDataset(sel[i], "set"))] = {};
			
			
			workview_definition['dimensions'][dim.id]['set'][parseInt(getDataset(sel[i], "set"))]['hierarchy'] = hier;
			workview_definition['dimensions'][dim.id]['set'][parseInt(getDataset(sel[i], "set"))]['instructions'] = setInstructions;
			workview_definition['dimensions'][dim.id]['set'][parseInt(getDataset(sel[i], "set"))]['element'] = null;
		}
	}
	
	workviewSave();
}

function recurseListVisbileElements(ul, list) {
	
	for(var i=0;i<ul.childNodes.length;i++) {
		var li = ul.childNodes[i];
		var span = li.childNodes[0];
		var elmSpan = span.childNodes[1];
		
		list[list.length] = {"name" : elmSpan.innerHTML, "level" : getDataset(span,"level"), "children" : getDataset(span,"children") };
		
		if( li.childNodes.length > 1 ) {
			var subUl = li.childNodes[1];
			if( subUl.style.display == 'block' )
				list = recurseListVisbileElements(subUl, list);
		}
	}
	
	return list;
}

function elementSelect(elm) {
	/* var dims = getDimensionsSelected();
	if( dims.length != 1 )
		return;
	var dim = dims[0]; */
	var dim = dimSelected;
	
	var sel = $(".ui-selected");
	$( "#dialog-dimension-element-select" ).dialog( "close" );
	
	for(var i=0;i<sel.length;i++) {
		if( dim.id == getDataset(sel[i], "id") ) { //only update the dimension which has been selected, no others or spacers
			setDataset(sel[i], "element", elm);
			sel[i].innerHTML = elm;
			workview_definition['dimensions'][dim.id]['set'][parseInt(getDataset(sel[i], "set"))]['element'] = elm;
		}
	}
	
}

function updateDimensionSelectionHierarchy() {
	/* var dims = getDimensionsSelected();
	if( dims.length != 1 )
		return;
	var dim = dims[0]; */
	var dim = dimSelected;
	
	var hier = $('#hierarchy-select option:selected').text();
	var html = "<ul class='tree' style='padding-left:0px;'>";
	
	if( hier == "No Hierarchy" ) {
		for(var i=0;i<dim['elements'].length;i++) {
			var elm = dim['elements'][i];
			
			html += nodeLi( elm['name'] ,0) + "</li>";
		}
		showDimensionWindow();
		showAdvancedEditor();
	} else {
		if( hierarchiesData ) {
			updatePickerWithHierarchy();
			showDimensionWindow();
			showAdvancedEditor();
			return;
		} else {
			// hier
			var tasks = {"tasks": [
				{"task": "dimension.get", "id":model_detail.id,  "dimensionid": dim['id'] }
			]};
			query("model.service",tasks,updateDimensionSelectionHierarchyCallback);
		}
	}
	//
	html += "</ul>";
	document.getElementById('element-tree-view').innerHTML = html;
}


function recursiveExtractHierarchyLi(node, indent) {
	//var str = "<li><a href='#' rel='nicetree-ajax'><img src='/img/icons/16/tick-button.png' onclick='elementSelect(\"" + node['name'] + "\");' style='margin-right:5px;top:3px;position:relative;'/>" + node['name'] + "</a>";
	
	var children = node['children'];
	var str = nodeLi( node['name'] , indent, children.length);
	str += '<ul class="tree" style="display:none;">';
	for(var i=0;i<children.length;i++) {
		str += recursiveExtractHierarchyLi(children[i],indent+1);// + '</ul>';
	}	
	str += '</ul>';
	str += '</li>';
	return str;
}
/*
function toggleSelection(span) {
	if( span.getAttribute('class') == "leaf" ) {
		span.setAttribute('class', "leaf selected");
	} else {
		span.setAttribute('class', "leaf");
	}
}
*/

var imgRoot = '/img/icons/16/';
function nodeLi(name, level, childrenCount) {
	var icon = "small-white.png";
	if( childrenCount > 0 ) 
		icon = "plus-small-white.png";
	if( !childrenCount) 
		childrenCount = 0;
	
	
	
	return "<li class='leaf'><span class='leaf' data-level='" + level + "'  data-name='" + escapeHtml(name) + "' data-children='" + childrenCount + "'  ><img onclick='return toggleNode(event, this);' ontouchend='return toggleNode(event, this);' style='padding-right:4px;' src='" + imgRoot + icon + "'/><span>" + name + "</span></span>";
	
	
	/* old */
	return "<li class='leaf'><span class='leaf' data-level='" + level + "'  data-name='" + escapeHtml(name) + "' data-children='" + childrenCount + "'  ><img onclick='return toggleNode(event, this);' ontouchend='return toggleNode(event, this);' style='padding-right:4px;' src='" + imgRoot + icon + "'/><span onclick='toggleSelection(this.parentNode);'>" + name + "</span></span>";
}




function toggleNode(event, img) { 
	if( getDataset(img.parentNode,"children") > 0 ) {
		//expand or collapse
		var ul = img.parentNode.nextSibling;
		if( ul.style.display == '' || ul.style.display == 'none' ) {
			ul.style.display = 'block';
			img.src = imgRoot + "minus-small-white.png";
		} else {
			ul.style.display = 'none';
			img.src = imgRoot + "plus-small-white.png";
		}
		return false;
	} else {
		//do nothing.
	}
}

function updatePickerWithHierarchy() {
	var hierName = $('#hierarchy-select option:selected').text();
	var html = "<ul class='tree' style='padding-left:0px;'>";
	
	if( typeof hierarchiesData.results === "undefined" ) {
		updateDimensionSelectionHierarchy();
		return;
	}
	
	//hierarchiesData
	var str = "";
	if( hierarchiesData['results'][0]['hierarchies'] ) {
		var hierarchies = hierarchiesData['results'][0]['hierarchies'];
		if( hierarchies.length > 0 ) {
			for(var k=0;k<hierarchies.length;k++) {
				var hier = hierarchies[k];
				if( hierName == hier['name'] ) {
					if( hier.root.length > 0 ) {
						for(var i=0;i<hier.root.length;i++) {
							str += recursiveExtractHierarchyLi(hier.root[i],0);
						}
					}
				}
			}
		}
	}
	
	html += str + "</ul>";
	document.getElementById('element-tree-view').innerHTML = html;
	//$("#tree").niceTree();
	//$("#element-tree-view").jstree();
	
}

function showDimensionWindow() {
	if( $("#tdAdvancedEditor").css('display') != 'block' ) {
		$( "#dialog-dimension-element-select" ).dialog({
			resizable: false,
			width: 347,
			height: 525,
			modal: true,
			title: "Dimension: " + dimSelected['name'],
			autoOpen: true,
			buttons: {
				Save: function() {
			
					saveDimensionElementSelection();
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
		loadSet();
	}
	bLoadingSetEditor = false;
}

//this function is called when the user selects a hierarchy in the drop list
function updateDimensionSelectionHierarchyCallback(data) {
	var results = JSON.parse(data);
	hierarchiesData = results;
	
	updatePickerWithHierarchy();
	
	if( $("#tdAdvancedEditor").css('display') != 'block' ) {
		showDimensionWindow();
	}
	
	//load set
	loadSet();
	
}

function saveConditionalFormat() {
	
	var func = $('#conditional-styles option:selected')[0].value;
	var funcText = $('#conditional-styles option:selected').text();
	
	var txtLower = $('#txtLower')[0].value;
	var txtUpper = $('#txtUpper')[0].value;
	 
	var nLower = parseFloat(txtLower);
	var nUpper = parseFloat(txtUpper);
	
	if( nLower > nUpper ) {
		var l = nLower;
		nLower = nUpper;
		nUpper = l;
	}
	
	text = "Conditional Format: " + funcText + " between " + nLower + " and " + nUpper;
	
	var html = '<li class="clearfix function set-instruction" data-instruction="conditional-format:' + func + ':' + nLower + ':' + nUpper + '"><span class="drag-marker"><i></i></span><p class="instruction-title">' + text + '</p><div class="instruction-actionlist pull-right clearfix"><a href="#" class="instruction-remove" onclick="removeInstruction(this);"><i class="fa fa-times"></i></a></div></li>';
	$("#sortable-instructions").html($("#sortable-instructions").html()+html);
	
	$("#sortable-instructions").sortable();
	
}

function displayConditionalInstructionForm() {
	//if the header is on columns then save per "set-element" otherwise save for the z class item
	
	$('#conditional-styles option').remove();
	$('#conditional-styles option').empty();
	
	for(var k=0;k<model_detail['styles'].length;k++) {
		var item = model_detail['styles'][k];
		$('#conditional-styles').append(new Option(item['name'], item['id']));
	}
	
	$( "#dlgConditionalFormatEditor" ).dialog({
      resizable: false,
      height:200,
      width:400,
      autoOpen: true,
      modal: true,
      buttons: {
			Save: function() {
				saveConditionalFormat();
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
     });
	 
	 /*
	 //bad formatting displays inside the form
	 $('#conditional-styles').chosen({
    	width: '280px;',
    	height: '100px;'
    });
	
	 */
}



