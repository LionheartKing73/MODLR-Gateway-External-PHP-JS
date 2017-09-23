

var table_pages = [];

function installTableInDiv(pageid, page_definition, div_id) {
	var pageid = page_definition.pageid;
	var bExists = false;
	
	for(var i=0;i<table_pages.length;i++) {
		if( table_pages[i].page.pageid == pageid ) {
			//table already exists. Dont add it again.
			bExists = true;
		}
	}
	
	if( !bExists ) {
		//create the table prioer to refreshing it. 
		table_pages[table_pages.length] = {"page":page_definition, "elementid" :div_id };
	}
	
	tableRender(pageid);
}

function tableRender(pageid) {
	//incase multiple of the same tables are in a single dashboard with different prompts.
	
	var elementid = "";
	var page = null;
	for(var i=0;i<table_pages.length;i++) {
		if( table_pages[i].page.pageid == pageid ) {
			elementid = table_pages[i].elementid;
			page = table_pages[i].page;
		}
	}
	
	
	var html = "";
	var fieldCount = 0;
	
	html += '<table class="table table-striped table-bordered"><thead><tr>';
	for(var i=0;i<page.fields.length;i++) {
		var field = page.fields[i];
		html += '<th>' + field.display + '</th>';
	}
	
	fieldCount = page.fields.length;
	if( page.actions.length > 0 ) {
		html += '<th>Actions</th>';
		fieldCount++;
	}
	
	html += '</tr></thead>';
	html += '<tbody>';
	html += '<tr>';
	html += '<td colspan="'+fieldCount+'" style="background-color: #fff;"><center><img src="/img/loader.gif"><br><br><span>Requesting Dataset.</span></center></td>';
	html += '</tr>';
	html += '</tbody>';
	
	html += '</table>';
	
	$("#"+elementid).html(html);
	
	
}

