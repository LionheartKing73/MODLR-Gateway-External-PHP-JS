
var queue = [];

function serverside(pageId, postData, callback) {
	var activity_id = getParameterByName("activityid");
	var identifier = "abc" + Math.random();
	
	var tasks = {"tasks": [
		{"task": "page.evaluate", "activityid" : activity_id,"pageid" : pageId, "post" : JSON.stringify(postData),"tag" : identifier}
	]};
	
	queue[queue.length] = {"identifier" : identifier,"callback":callback};
	
	queryObj("collaborator.service",tasks,serverside_callback);	
}

function serverside_callback(data) {
	var identifier = data.results[0].tag;
	for(var i=0;i<queue.length;i++) {
		if( queue[i].identifier == identifier ) {
			queue[i].callback(JSON.parse(data.results[0].output));
			queue.splice(i,1);
			return;
		}
		
	}
}


function execute_workviews(workview_ids, titles_collection, options, callback) {
	var model_id = getParameterByName("id");
	var tasks = {"tasks": [
		
	]};
	
	for(var i=0;i<workview_ids.length;i++) {
		tasks['tasks'][tasks['tasks'].length] = {"task": "workview.execute", "id" : model_id, "activityid" : activityid,"workviewid" : workview_ids[i], "titles" : titles_collection, "options" : options};
	}
	
	queryObj("collaborator.service",tasks,callback);	
}

function execute_workview(workview_id, titles_collection, options, callback) {
	var model_id = getParameterByName("id");
	var tasks = {"tasks": [
		{"task": "workview.execute", "id" : model_id, "activityid" : activityid, "workviewid" : workview_id, "titles" : titles_collection, "options" : options}
		
	]};
	queryObj("collaborator.service",tasks,callback);	
}

function keypair_by_columns(workview_execute_results) {
	//returns data in label/value format for chart consumption
	
	var execution = workview_execute_results['results'][0];
	var values = [];
	for(var x=0;x<execution['columns'][0].length;x++) {
		var label = execution['columns'][0][x]['name'];
		label = label.replace(/\"/g, "");
		
		var address = "c" + x + "r0";
		var value = "";
		if( execution[address].value.number!==undefined ) {
			value = execution[address].value.number;
		} else {
			value = execution[address].value.string;
			value = value.replace(/\"/g, "");
		}
		
		values[values.length] = {"label" : label, "value" : value};
			
	}
	return values;
}


function keyvalues_by_columns(workview_execute_results) {
	//returns data in label/value format for chart consumption
	
	var execution = workview_execute_results['results'][0];
	var values = [];
	for(var x=0;x<execution['columns'][0].length;x++) {
		var label = execution['columns'][0][x]['name'];
		label = label.replace(/\"/g, "");
		
		var inner_values = [];
		for(var y=0;y<execution['rows'][0].length;y++) {
			
			var rowLabel = "";
			for(var yy=0;yy<execution['rows'].length;yy++) {
				rowLabel += execution['rows'][yy][y]['name'] + " - ";
				rowLabel = rowLabel.replace(/\"/g, "");
			}
			rowLabel = rowLabel.substr(0,rowLabel.length-3);
			
			var address = "c" + x + "r" + y;
			var value = "";
			if( execution[address].value.number!==undefined ) {
				value = execution[address].value.number;
			} else {
				value = execution[address].value.string;
				value = value.replace(/\"/g, "");
			}
		
			inner_values[inner_values.length] = [rowLabel, value];
		}
		
		values[values.length] = {"key" : label, "values" : inner_values};
			
	}
	return values;
}


function workview_results_to_csv(workview_execute_results, line_endings) {
	line_endings = line_endings || '\n';
	var execution = workview_execute_results['results'][0];
	
	var csvStr = "";
	
	if( execution['rows'].length == 0 ||  execution['columns'].length == 0) {
		return "Incomplete Workview";
	} 
	
	
	//loop through column dimensions
	for(var x=0;x<execution['columns'].length;x++) {
	
		//loop through row dimensions
		for(var y=0;y<execution['rows'].length;y++) {
			csvStr += "\"\","
		}
		
		for(var xx=0;xx<execution['columns'][x].length;xx++) {
			var value = execution['columns'][x][xx]['name'];
			value = value.replace(/\"/g, "");
			csvStr += "\"" + value + "\","
		}
		
		csvStr = csvStr.substr(0,csvStr.length-1) + line_endings;
	}
	
	for(var y=0;y<execution['rows'][0].length;y++) {
	
		for(var yy=0;yy<execution['rows'].length;yy++) {
			var value = execution['rows'][y][yy]['name'];
			value = value.replace(/\"/g, "");
			csvStr += "\"" + value + "\","
		}
		
		for(var x=0;x<execution['columns'][0].length;x++) {
			var address = "c" + x + "r" + y;
			var value = "";
			if( execution[address].value.number!==undefined ) {
				value = execution[address].value.number;
				csvStr += value + ","
			} else {
				value = execution[address].value.string;
				value = value.replace(/\"/g, "");
				csvStr += "\"" + value + "\","
			}
		}
		
		csvStr = csvStr.substr(0,csvStr.length-1) + line_endings;
	}
	
	csvStr = csvStr.substr(0,csvStr.length-line_endings.length);
	
	return csvStr;
}

//Big Thanks to Dominic Tobias on stackoverflow
function csv_to_array( strData, strDelimiter ){
	// Check to see if the delimiter is defined. If not,
	// then default to comma.
	strDelimiter = (strDelimiter || ",");

	// Create a regular expression to parse the CSV values.
	var objPattern = new RegExp(
		(
			// Delimiters.
			"(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

			// Quoted fields.
			"(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

			// Standard fields.
			"([^\"\\" + strDelimiter + "\\r\\n]*))"
		),
		"gi"
		);


	// Create an array to hold our data. Give the array
	// a default empty first row.
	var arrData = [[]];

	// Create an array to hold our individual pattern
	// matching groups.
	var arrMatches = null;


	// Keep looping over the regular expression matches
	// until we can no longer find a match.
	while (arrMatches = objPattern.exec( strData )){

		// Get the delimiter that was found.
		var strMatchedDelimiter = arrMatches[ 1 ];

		// Check to see if the given delimiter has a length
		// (is not the start of string) and if it matches
		// field delimiter. If id does not, then we know
		// that this delimiter is a row delimiter.
		if (
			strMatchedDelimiter.length &&
			strMatchedDelimiter !== strDelimiter
			){

			// Since we have reached a new row of data,
			// add an empty row to our data array.
			arrData.push( [] );

		}

		var strMatchedValue;

		// Now that we have our delimiter out of the way,
		// let's check to see which kind of value we
		// captured (quoted or unquoted).
		if (arrMatches[ 2 ]){

			// We found a quoted value. When we capture
			// this value, unescape any double quotes.
			strMatchedValue = arrMatches[ 2 ].replace(
				new RegExp( "\"\"", "g" ),
				"\""
				);

		} else {

			// We found a non-quoted value.
			strMatchedValue = arrMatches[ 3 ];

		}


		// Now that we have our value string, let's add
		// it to the data array.
		arrData[ arrData.length - 1 ].push( strMatchedValue );
	}

	// Return the parsed data.
	return( arrData );
}
