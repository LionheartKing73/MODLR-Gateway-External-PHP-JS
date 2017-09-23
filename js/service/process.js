
var action_build_dimension = "Build a hierarchy from data";
window.onbeforeunload = function() {
  return "Are you sure you want to navigate away?";
}

var data_types = {
    'VARCHAR': 'String',
    'TEXT': 'String',
    'INT': 'Number',
    'INT UNSIGNED': 'Number',
    'TINYINT': 'Number',
    'TINYINT UNSIGNED': 'Number',
    'BIGINT': 'Number',
    'BIGINT UNSIGNED': 'Number',
    'DOUBLE': 'Number',
    'DOUBLE UNSIGNED': 'Number',
    'FLOAT': 'Number',
    'FLOAT UNSIGNED': 'Number',
    'TIMESTAMP': 'Time'
};

var build_types_normal = new Array(
    "Ignore",
    "Element Alias",
    "Element",
    "Element (Time)",
    "Element (Scenario)",
    "Grouping",
    "Data",
    "Filter (skip if field = 1)",
    "Filter (skip if field = 0)",
    "Filter (skip if field = NULL)"
);
var build_types_single_column = new Array(
    "Ignore",
    "Element Alias",
    "Element",
    "Element (Time)",
    "Element (Scenario)",
    "Element (Measure)",
    "Grouping",
    "Data",
    "Filter (skip if field = 1)",
    "Filter (skip if field = 0)",
    "Filter (skip if field = NULL)"
);
var build_types_hierarchy = new Array(
    "Ignore",
    "Element Alias",
    "Element",
    "Element (Time)",
    "Element (Scenario)",
    "Element (Measure)",
    "Grouping",
    "Filter (skip if field = 1)",
    "Filter (skip if field = 0)",
    "Filter (skip if field = NULL)"
);


function updateDimension(list) {
	textUpdate(list);
	if( getAction() == action_build_dimension ) {
		$("input.dimension").prop('value', $("#target_dimension").val());
		for(var i=0;i<$("input.dimension").length;i++) {
			textFieldUpdate($("input.dimension")[i]);
		}
	}
}

function dataTickChanged() {
	updateColumnOptions("");
	queryPreview();
	
	var chkOneColumn = document.getElementById('chkOneColumn').checked;
	if( chkOneColumn ) {
		updateDefinition("datasource_contains_measures", "1");
	} else {
		updateDefinition("datasource_contains_measures", "0");
	}
}

function getAction() {
	var actionList = document.getElementById('action');
	var action = "";
	if( actionList.selectedIndex > -1 ) 
		action = actionList.options[actionList.selectedIndex].value;
	return action;
}



var template_javascript = "function pre() {\r\n\t//this function is called once before the processes is executed.\r\n\t//Use this to setup prompts.\r\n\tscript.log('process pre-execution parameters parsed.');\r\n}\r\n\r\nfunction begin() {\r\n\t//this function is called once at the start of the process\r\n\tscript.log('process execution started.');\r\n}\r\n\r\nfunction data(record) {\r\n\t//this function is called once for each line of data on the second cycle\r\n\t//use this to build dimensions and push data into cubes\r\n\t\r\n}\r\n\r\nfunction end() {\r\n\t//this function is called once at the end of the process\r\n\tscript.log('process execution finished.');\r\n}\r\n\r\n";

var template_r = "#record variable is defined for each data() function call.\r\n\r\npre <- function(){\r\n\t#this function is called once before the processes is executed.\r\n\t#Use this to setup prompts.\r\n\tscript$log(\"process pre-execution parameters parsed.\")\r\n}\r\n\r\nbegin <- function(){\r\n\t#this function is called once at the start of the process\r\n\tscript$log(\"process execution started.\")\r\n}\r\n\r\ndata <- function(record){\r\n\t#this function is called once for each line of data on the second cycle\r\n\t#use this to build dimensions and push data into cubes\r\n\t\r\n}\r\n\r\nend <- function(){\r\n\t#this function is called once at the end of the process\r\n\tscript$log(\"process execution finished.\")\r\n}\r\n\r\n";


function languageChange() {

	var languageList = document.getElementById('language');
	var language = "";
	if( languageList.selectedIndex > -1 ) 
		language = languageList.options[languageList.selectedIndex].value;
	
	
	var lang = null;
	var template = null;
	
	if( language == "r" ) {
		lang = {
			name: "r",
			scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
					   mode: null},
					   {matches: /\/text|\/x-rsrc/i,
					   mode: "r"}]
		};
		template = template_r;
	} else {
		lang = {
			name: "javascript",
			scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
					   mode: null},
					  {matches: /(text|application)\/(x-)?vb(a|script)/i,
					   mode: "vbscript"}]
		};
		template = template_javascript;
	}
	
	
	
	if( editor == null ) {
		var te = document.getElementById("page_contents");
		
		
		/*
		editor = CodeMirror.fromTextArea(te, {
			lineNumbers: true,
			smartIndent: false,
			mode: lang,
			height: '800px'
		});
		*/
		var mixedMode = {
        name: "javascript",
        scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
					   mode: null},
					  {matches: /(text|application)\/(x-)?vb(a|script)/i,
					   mode: "vbscript"},
					   {matches: /\/text|\/x-rsrc/i,
					   mode: "r"}]
    };
		
		editor = CodeMirror.fromTextArea(te, {
			lineNumbers: true,
			mode: mixedMode,
			viewportMargin: 20,
      extraKeys: {
        "F11": function(cm) {
					var newToggle = !cm.getOption("fullScreen");
					goFullToggle(newToggle);
					cm.setOption("fullScreen", newToggle);
        },
        "Esc": function(cm) {
					goFullToggle(false);
					if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
        }
      }
    });
		
	}
	
	if( process_definition != null ) {
		if( process_definition['script'] != null ) {
			if( process_definition['script'] == template_r || process_definition['script'] == template_javascript ) {
				editor.setValue(template);
			}
		} else {
			editor.setValue(template);
		}
	} else {
		editor.setValue(template);
	}
	
	process_definition['language'] = language;
	
	editor.setOption("mode", lang);
    CodeMirror.autoLoadMode(editor, lang);
}

function actionChange() {

	var actionList = document.getElementById('action');
	var action = "";
	if( actionList.selectedIndex > -1 ) 
		action = actionList.options[actionList.selectedIndex].value;
	
	var cubeBlock = document.getElementById('cubeBlock');
	var dimBlock = document.getElementById('dimBlock');
	var hierarchyBlock = document.getElementById('hierarchyBlock');
	var languageBlock = document.getElementById('languageBlock');
	
	
	var build_actions = document.getElementById('build_actions');
	var custom_scripting = document.getElementById('custom_scripting');
	
	
	updateDefinition("action",action);
	
	if( action == "Custom" ) {
		cubeBlock.style.display = 'none';
		dimBlock.style.display = 'none';
		hierarchyBlock.style.display = 'none';
		build_actions.style.display = 'none';
		custom_scripting.style.display = 'block';
		languageBlock.style.display = 'block';
		
		languageChange();
	} else if( action == "Build a cube from data" ) {
		cubeBlock.style.display = 'block';
		dimBlock.style.display = 'none';
		hierarchyBlock.style.display = 'none';
		languageBlock.style.display = 'none';
		build_actions.style.display = 'block';
		custom_scripting.style.display = 'none';
	} else {
		cubeBlock.style.display = 'none';
		dimBlock.style.display = 'block';
		hierarchyBlock.style.display = 'block';
		build_actions.style.display = 'block';
		custom_scripting.style.display = 'none';
	}
	
	if( getAction() == action_build_dimension ) {
		$("input.dimension").prop('value', $("#target_dimension").val());
		$("input.dimension").prop('disabled', true);
	} else {
		$("input.dimension").prop('disabled', false);
	}
	
}

var buildOptions = "";

function updateColumnOptions(columnBuild) {
	var chkOneColumn = document.getElementById('chkOneColumn');
	
	var actionList = document.getElementById('action');
	var action = "";
	if( actionList.selectedIndex > -1 ) 
		action = actionList.options[actionList.selectedIndex].value;
	
	
	buildOptions = "<select onChange='changeBuild(this);'>";
	
	
	var arr = null;
	if( action == "Build a cube from data" ) {
		if( chkOneColumn.checked ) {
			arr = build_types_single_column;
		} else {
			arr = build_types_normal;
		}
	} else {
		arr = build_types_hierarchy;
	}
	for(var i=0;i<arr.length;i++) {
		var selected = "";
		if( columnBuild == arr[i] ) {
			selected = " selected";
		} 
		buildOptions += "<option" + selected + ">" + arr[i] + "</option>";
	}
	
	buildOptions += "</select>";
	
}

updateColumnOptions("");

var dataPtr = null;
var dataCursor = 0;

var definition_target = 'target';
var definition_target_method = 'target_method';

var definition_datasource = 'datasource';
var definition_datasource_method = 'datasource_method';
var definition_datasource_table = 'datasource_table';
var definition_datasource_sql = 'datasource_sql';

var definition_datasource_columns = 'datasource_columns';

var process_definition = {
    target: "",
    target_method: "",
    datasource: "",
    datasource_sql: "",
    datasource_method: "",
    datasource_table: "",
    datasource_columns: {}
};

if( process_definition_loaded != null ) {
	process_definition = process_definition_loaded;
	
}

function updateDefinition(key, value) {
	process_definition[key] = value;
}
function getDefinition(key) {
	return process_definition[key];
}

function updateDefinitionColumn(field, key, value) {
	if( ! process_definition[definition_datasource_columns] )
		process_definition[definition_datasource_columns] = new Object();
		
	if( ! process_definition[definition_datasource_columns][field] )
		process_definition[definition_datasource_columns][field] = new Object();
		
	process_definition[definition_datasource_columns][field][key] = value;
}

function getDefinitionColumn(field, key) {
	if( process_definition[definition_datasource_columns] ) {
		if( process_definition[definition_datasource_columns][field] ) {
			if( process_definition[definition_datasource_columns][field][key] ) {
				return process_definition[definition_datasource_columns][field][key];
			}
		}
	}
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


function textFieldUpdate(text) {
	//needs to read the field name then update the setting
	var value = text.value;
	var key = text.getAttribute('id');
	var tr = text.parentNode.parentNode;
	var fieldName = tr.childNodes[0].innerText;
	
	updateDefinitionColumn(fieldName,key,value);
	
}


function dropFieldUpdate(text) {
	//needs to read the field name then update the setting
	
	var value = "";
	if( text.selectedIndex > -1 ) 
		value = text.options[text.selectedIndex].value;
	
	var key = text.getAttribute('id');
	var tr = text.parentNode.parentNode;
	var fieldName = tr.childNodes[0].innerText;
	
	updateDefinitionColumn(fieldName,key,value);
}




function mapTypeToString(typeStr) {
	var typeString = data_types[typeStr.toUpperCase()];
	if( typeString ) {
		return typeString;
	} 
	return "String";
}

function datasourceChange() {

	var datasourceList = document.getElementById('datasource');
	var datasourceListMethod = document.getElementById('datasource_method');
	var methodBlock = document.getElementById('methodBlock');
	var tablesBlock = document.getElementById('tablesBlock');
	var queryBlock = document.getElementById('queryBlock');
	var miscBlock = document.getElementById('miscBlock');
	
	var tablesLabel = document.getElementById('tablesLabel'); 
	
	var review_data = document.getElementById('review_data');
	var build_actions = document.getElementById('build_actions');
	
	var txtQuery = document.getElementById('txtQuery');
	
	var datasource = "";
	if( datasourceList.selectedIndex > -1 ) {
		datasource = datasourceList.options[datasourceList.selectedIndex].value;
	}
	
	if( datasource == "NONE" ) {
		methodBlock.style.display = 'none';
		queryBlock.style.display = 'none';
		tablesBlock.style.display = 'none';
		miscBlock.style.display = 'none';
		review_data.style.display = 'none';
		return;
	} else {
		methodBlock.style.display = 'block';
		miscBlock.style.display = 'block';
		review_data.style.display = 'block';
	}
	
	var datasourceMethod = "";
	if( datasourceListMethod.selectedIndex > -1 ) {
		datasourceMethod = datasourceListMethod.options[datasourceListMethod.selectedIndex].value;
	}
	
	
	updateDefinition(definition_datasource, datasource);
	updateDefinition(definition_datasource_method, datasourceMethod);
	
	
	var dsDetail = datasourceForId(datasource);
	var datasourceType = dsDetail.type;
	
	
	if( datasourceType == "Google Analytics" ) {
		tablesBlock.style.display = 'block';
		queryBlock.style.display = 'block';
		methodBlock.style.display = 'none';
		tablesLabel.innerHTML = "Profile:";
		txtQuery.placeholder = "Start Date:\r\nDimensions:\r\nMetrics:\r\n";
		if( txtQuery.innerHTML == "" ) 
			txtQuery.innerHTML = "Start Date=2015-01-01\r\nEnd Date=2015-01-14\r\nMetrics=ga:users\r\nDimensions=ga:date,ga:country\r\nSort=ga:date\r\nFilters=";
	} else {
		tablesLabel.innerHTML = "Table:";
		txtQuery.placeholder = "SELECT * FROM tablename;";
		//txtQuery.innerHTML = "";
		//display the appropriate fields
		if( datasourceMethod == "Select a Table") {
			tablesBlock.style.display = 'block';
			queryBlock.style.display = 'none';
			
		} else {
			tablesBlock.style.display = 'none';
			queryBlock.style.display = 'block';
			
		}
	}
	
	//regardless we should populate the datasource tables.
	if( datasource != null && datasource != "" ) {
		var tasks = {"tasks": [
			{"task": "datasource.tables", "id": datasource }
		]};

		query(serviceDataName,tasks,datasourceChangeCallback);
	}
	
}

function datasourceForId(id) {
	for(var i=0;i<datasourceList.length;i++) {
		if(datasourceList[i].id == id ) {
			return datasourceList[i];
		}
	}
	return null;
}

function datasourceChangeCallback(data) {
	if( data == "{}" )
		return;
	var results = JSON.parse(data);
	
	
	$("#tables").empty()
	
	var tables = results['results'][0]['tables'];
	if( tables ) {
		for(var i=0;i<tables.length;i++) {
			if( tables[i]['name'] == tableName ) {
				var opt = new Option(tables[i]['name'], tables[i]['name'], true, true);
				$("#tables")[0].options.add(opt);
			} else {
				var opt = new Option(tables[i]['name'], tables[i]['name'], false, false);
				$("#tables")[0].options.add(opt);
			}
		}
	}
	
	queryPreview();
}

function queryPreview() {
	//preview
	var datasourceList = document.getElementById('datasource');
	var datasource = datasourceList.options[datasourceList.selectedIndex].value;
	
	var datasourceList = document.getElementById('datasource_method');
	var datasourceMethod = datasourceList.options[datasourceList.selectedIndex].value;
	
	if( datasource == "NONE" ) {
		return;
	}
	
	var dsDetail = datasourceForId(datasource);
	var datasourceType = dsDetail.type;
	
	var txtQuery = document.getElementById('txtQuery');
	
	var tasks = null;
	
	if( datasourceType == "Google Analytics" ) {
		
		var lstTables = document.getElementById('tables');
		if( lstTables ) {
			if( lstTables.selectedIndex == -1 ) 
				return;
			lstTables = lstTables.options[lstTables.selectedIndex].value;
		}
		
		process_definition['profile'] = lstTables;
		process_definition['query'] = txtQuery.value;
		
		tasks = {"tasks": [
			{"task": "datasource.query.sample", "id": datasource, "profile": lstTables, "query": txtQuery.value , "modelid" : getParameterByName("id")}
		]};
	} else {
	
		var sql = "";
		if( datasourceMethod == "Select a Table") {
			
			
			var lstTables = document.getElementById('tables');
			
			if( lstTables ) {
				if( lstTables.selectedIndex == -1 ) 
					return;
				lstTables = lstTables.options[lstTables.selectedIndex].value;
			}
			
			sql = "SELECT * FROM " + lstTables;
			updateDefinition(definition_datasource_method, datasourceMethod);
			updateDefinition(definition_datasource_table, lstTables);
			updateDefinition(definition_datasource_sql, sql);
			
		} else {
			sql = txtQuery.value;
			updateDefinition(definition_datasource_method, datasourceMethod);
			updateDefinition(definition_datasource_table, "");
			updateDefinition(definition_datasource_sql, txtQuery.value);
			
		}
		
		tasks = {"tasks": [
			{"task": "datasource.query.sample", "id": datasource, "sql": sql }
		]};
	}
	
	

	query(serviceDataName,tasks,queryPreviewCallback);
	
}

var fields = null;
function queryPreviewCallback(data) {
	var results = JSON.parse(data);
	dataCursor = 0;
	
	if( !results['results'][0]['query'] ) {
		return;
	}
	
	var result = results['results'][0]['query']['result'];
	var message = results['results'][0]['query']['message'];
	fields = results['results'][0]['query']['fields'];
	var rows = results['results'][0]['query']['rows'];
	
	dataPtr = results['results'][0]['query'];
	
	if( result == 0 ) {
		var oldTable = document.getElementById('preview'),
    	newTable = document.createElement('table');
    	oldTable.parentNode.replaceChild(newTable, oldTable);
    	newTable.setAttribute('id','preview');
    	
    	if( message ) 
    		document.getElementById('previewText').innerText = message;
    	else
    		document.getElementById('previewText').innerText = "The query failed with a sql error.";
		return;
	}
    document.getElementById('previewText').innerText = "";
	
	var oldTable = document.getElementById('preview');
    newTable = document.createElement('table');
    newTable.setAttribute('class','table table-striped');
    
    var thead = document.createElement('thead');
    var tr = document.createElement('tr');
    for(var k = 0; k < fields.length; k++){
    	var td = document.createElement('td');
		td.appendChild(document.createTextNode( fields[k]['name'] ));
		tr.appendChild(td);
    }
    tr.style.borderTop =  '1px solid #59636d';
    tr.style.borderLeft =  '1px solid #59636d';
    tr.style.borderRight =  '1px solid #59636d';
    tr.style.backgroundColor =  '#59636d';
    tr.style.color =  '#FFF';
    thead.appendChild(tr);
    newTable.appendChild(thead);
    
    
    var tbody = document.createElement('tbody');
    newTable.appendChild(tbody);
	for(var i = 0; i < rows.length; i++){
		var tr = document.createElement('tr');
		for(var k = 0; k < fields.length; k++){
			var td = document.createElement('td');
			if( k < rows[i].length ) {
				var value = rows[i][k]['value'];
				td.appendChild(document.createTextNode( value ));
			}
			tr.appendChild(td);
		}
		tbody.appendChild(tr);
	}

	oldTable.parentNode.replaceChild(newTable, oldTable);
    newTable.setAttribute('id','preview');
	
	
	
	//buildsBody - remove older fields (except calculations)
	tbody = document.getElementById('buildsBody');
	for(var k =  tbody.children.length-1; k >= 0; k--){
		var tr = tbody.children[k];
		if( tr ) {
			if( tr.children.length > 1 ) {
				var typeTd = tr.children[1].innerText;
		
				if( typeTd != "Calculation" ) {
					tbody.removeChild(tr);
				}
			}
		}
	}
	
	//add new fields
	for(var k = 0; k < fields.length; k++){
		var tr = document.createElement('tr');
		
		//name
    	var td = document.createElement('td');
    	var field = fields[k]['name'];
    	field = fieldClean(field);
		td.appendChild(document.createTextNode(field));
		tr.appendChild(td);
		
		updateDefinitionColumn(field, "data_type",mapTypeToString( fields[k]['type']));
		
		//type
		td = document.createElement('td');
		var str = mapTypeToString(fields[k]['type']);
		td.appendChild(document.createTextNode(str));
		tr.appendChild(td);
		
		//preview
		td = document.createElement('td');
		td.appendChild(document.createTextNode(''));
		tr.appendChild(td);
		
		//build action
		var build = getDefinitionColumn(field, "build");
		updateColumnOptions(build);
		
		td = document.createElement('td');
		td.innerHTML = buildOptions;
		tr.appendChild(td);
		var buildTd = td;
		
		//target
		td = document.createElement('td');
		td.appendChild(document.createTextNode(''));
		tr.appendChild(td);
		
		
		tbody.appendChild(tr);
		
		changeBuild(buildTd.firstChild);
    }
	
	loadDataSample(0);
}

function incPreview() {
	loadDataSample(dataCursor+1);
}

function decPreview() {
	loadDataSample(dataCursor-1);
}

function loadDataSample(cur) {
	var fields = dataPtr['fields'];
	var rows = dataPtr['rows'];
	
	if( cur >=  rows.length )
		cur = 0;
	if( cur < 0 ) 
		cur = rows.length-1;
		
	if( rows.length == 0 )
		return;
		
	dataCursor = cur;
	var row = rows[dataCursor];
	
	//update the preview field
	tbody = document.getElementById('buildsBody');
	for(var k =  tbody.children.length-1; k >= 0; k--){
		var tr = tbody.children[k];
		if( tr ) {
			if( tr.children.length > 1 ) {
				tr.children[2].innerText = row[k]['value'];
			}
		}
	}
}

function fieldClean(field) {
	return field.replace(/ /g,"_").toLowerCase();
}

function changeBuild(lst) {
	var build = lst.options[lst.selectedIndex].value;
	var tr = lst.parentNode.parentNode;
	var fieldName = tr.childNodes[0].innerText;
	fieldName = fieldClean(fieldName);
	var targetTd = tr.childNodes[4];
	
	var dimension = getDefinitionColumn(fieldName, "dimension");
	var hierarchy = getDefinitionColumn(fieldName, "hierarchy");
	
	var alias = getDefinitionColumn(fieldName, "alias");
	var attribute = getDefinitionColumn(fieldName, "attribute");
	var element = getDefinitionColumn(fieldName, "element");
	var element_name = getDefinitionColumn(fieldName, "element_name");
	var measure_column = getDefinitionColumn(fieldName, "measure column");
	var consolidate_by_average = getDefinitionColumn(fieldName, "consolidate_by_average");
	var total_hierarchy = getDefinitionColumn(fieldName, "total_hierarchy");
	
	var child = getDefinitionColumn(fieldName, "child");
	
	var clearing_variable = getDefinitionColumn(fieldName, "clearing_variable");
	if( typeof clearing_variable === 'undefined' ) {
		clearing_variable = "";
	}
	
	var format = getDefinitionColumn(fieldName, "format");
	var prompt = getDefinitionColumn(fieldName, "prompt");
	
	
	if( consolidate_by_average == "1" ) {
		consolidate_by_average = " checked";
	} else {
		consolidate_by_average = "";
	}
	
	if( total_hierarchy == "1" ) {
		total_hierarchy = " checked";
	} else {
		total_hierarchy = "";
	}
	
	if( prompt == "1" ) {
		prompt = " checked";
	} else {
		prompt = "";
	}
	
	
	
	if( build == "Ignore" ) {
		targetTd.innerHTML = "";
	} else if( build == "Element Alias" ) {
		targetTd.innerHTML = "Dimension: <input type='text' name='dimension' class='dimension' id='dimension' value='" + dimension + "' placeholder='Dimension' onChange='textFieldUpdate(this);'/> Alias:<input type='text' name='alias' id='alias' value='" + alias + "' placeholder='Alias Name' onChange='textFieldUpdate(this);'/> Element Field: <select type='text' name='element' id='element' class='" + fieldName + "_element element' onChange='dropFieldUpdate(this);'></select>";
	} else if( build == "Element" ) {
		targetTd.innerHTML = "Dimension: <input type='text' name='dimension' class='dimension' id='dimension' value='" + dimension + "' placeholder='Dimension' onChange='textFieldUpdate(this);'/><br/><input type='checkbox' id='total_hierarchy' onChange='checkFieldUpdate(this);'" + total_hierarchy + "> Create 'All' Hierarchy. <input type='checkbox' id='prompt' onChange='checkFieldUpdate(this);'" + prompt + "> Prompt for filtering.";
	} else if( build == "Element (Time)" ) {
		targetTd.innerHTML = "Dimension: <input type='text' name='dimension' class='dimension' id='dimension' value='" + dimension + "' placeholder='Dimension' onChange='textFieldUpdate(this);'/>. Format: <input type='text' name='format' class='format' id='format' value='" + format + "' placeholder='dd/MM/YYYY' onChange='textFieldUpdate(this);'/>.<br/>Clearing Variable: <input type='text' name='clearing_variable' class='format' id='clearing_variable' value='" + clearing_variable + "' placeholder='' onChange='textFieldUpdate(this);'/><br/><input type='checkbox' id='prompt' onChange='checkFieldUpdate(this);'" + prompt + "> Prompt for filtering.";
	} else if( build == "Element (Scenario)" ) {
		targetTd.innerHTML = "Dimension: <input type='text' name='dimension' class='dimension' id='dimension' value='" + dimension + "' placeholder='Dimension' onChange='textFieldUpdate(this);'/><br/><input type='checkbox' id='prompt' onChange='checkFieldUpdate(this);'" + prompt + "> Prompt for filtering.";
	} else if( build == "Grouping" ) {
		targetTd.innerHTML = "Dimension: <input type='text' name='dimension' class='dimension' id='dimension' value='" + dimension + "' placeholder='Dimension' onChange='textFieldUpdate(this);'/> Hierarchy: <input type='text' name='hierarchy' id='hierarchy' value='" + hierarchy + "' placeholder='Hierarchy' onChange='textFieldUpdate(this);'/> Child Field: <select type='text' name='child' id='child' class='" + fieldName + "_child child' onChange='dropFieldUpdate(this);'></select><br/><input type='checkbox' id='total_hierarchy' onChange='checkFieldUpdate(this);'" + total_hierarchy + "> Create 'All' Hierarchy. ";
	} else if( build == "Element (Measure)" ) {
		targetTd.innerHTML = "";
	} else if( build == "Data" ) {
		
		var chkOneColumn = document.getElementById('chkOneColumn');
		if( chkOneColumn.checked  ) {
			//should prepopulate the measure column name here.
			targetTd.innerHTML = "Measure Field: <input type='text' name='measure column' id='measure column' value='" + measure_column + "' placeholder='Measure Column' onChange='textFieldUpdate(this);'/>";
		} else {
			targetTd.innerHTML = "Measure Element: <input type='text' name='element_name' id='element_name' value='" + element_name + "' placeholder='Measure Element' onChange='textFieldUpdate(this);'/> <input type='checkbox' id='consolidate_by_average' onChange='checkFieldUpdate(this);'" + consolidate_by_average + "> Average instead of Sum.";
		}
			
	} 
	
	if( getAction() == action_build_dimension ) {
		$("input.dimension").prop('value',$("#target_dimension").val());
		for(var i=0;i<$("input.dimension").length;i++) {
			textFieldUpdate($("input.dimension")[i]);
		}
		
		$("input.dimension").prop('disabled', true);
	} else {
		$("input.dimension").prop('disabled', false);
	}
	
	updateDefinitionColumn(fieldName, "build", build);
	
	updateDroplists(fieldName,'element',element);
	updateDroplists(fieldName,'child',child);
	
	var items = $("select.element");
	for(var i=0;i<items.length;i++) {
		dropFieldUpdate(items[i]);
	}
	items = $("select.child");
	for(var i=0;i<items.length;i++) {
		dropFieldUpdate(items[i]);
	}
	
	
}

function addCalculation() {
	
}

function updateDroplists(field, argument, value) {
	if( argument == "element" || argument == "child" ) {
		
		var list = $("." + field + "_" + argument);
		if( list.length > 0 ) {
			list.empty();
			for(var k = 0; k < fields.length; k++){
				var fieldName = fields[k]['name'];
				var fieldCode = fieldClean(fieldName);
				var selected = false;
				if( value == fieldCode )
					selected = true;
				
				var opt = new Option(fieldCode, fieldCode, selected, selected);
				list[0].options.add(opt);
			}
		}
	}
}




function executeThisProcess() {
	saveExecutor(executeThisProcessPostSave);
}

function executeThisProcessPostSave(data) {
	save_callback(data);
	
	var processId = getParameterByName("processid");
	if( processId == "" )
		processId = getDefinition("processid");
	
	if( typeof processId === "undefined" ) {
		alert("You must save the process before you can execute it.");
	} else {
		var modelId = getParameterByName("id");
		window.onbeforeunload = null;
		executeProcess(modelId, processId);
	}
}

function saveExecutor(callback) {
	
	
	var task = "process.create.update";
	var modelId = getParameterByName("id");
	var processId = getParameterByName("processid");
	if( processId == "" )
		processId = getDefinition("processid");
	
	//should clean up the field definitions here as definitions from other tables / queries could have an adverse affect on the system.
	
	if( editor != null ) {
		process_definition['script'] = editor.getValue();
	} else {
		process_definition['script'] = template_javascript;
	}
	
	var tasks = {"tasks": [
		{"task": task, "definition": process_definition, "id": modelId, "processid": processId }
	]};
	
	document.getElementById('verityResults').innerHTML = '<b>Saving Process</b>';
	query(serviceModelName,tasks,callback);
}

function save() {
	saveExecutor(save_callback);
}

function save_callback(data) {
	var results = JSON.parse(data);
	
	updateDefinition("processid", results['results'][0]['processid']);
	
	var str = "";
	
	var logs = results['results'][0]['log'];
	for(var i=0;i<logs.length;i++) {
		str += logs[i]['message']  + "<br/>"; 
	}
	
	if( parseInt(results['results'][0]['result']) == 1 ) {
		
		document.getElementById('verityResults').innerHTML = '<b>Process Validation Succeeded:</b><br/>' + str;
		
	} else {
		
		document.getElementById('verityResults').innerHTML = '<b>Process Validation Failed:</b><br/>' + str;
	}
}

var editor = null;
$(function(){
	actionChange();
	datasourceChange();
});

/*
var editor = null;
$(function(){
	var te = document.getElementById("page_contents");

	var mixedMode = {
		name: "javascript",
		scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
					   mode: null},
					  {matches: /(text|application)\/(x-)?vb(a|script)/i,
					   mode: "vbscript"},
					   {matches: /\/text|\/x-rsrc/i,
					   mode: "r"}]
	};
	editor = CodeMirror.fromTextArea(te, {
		lineNumbers: true,
		smartIndent: false,
		mode: mixedMode,
		height: '800px'
	});
});
*/

