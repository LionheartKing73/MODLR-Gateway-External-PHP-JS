
function change_title_on_page(page_id, title_id, value) {

	var page = document.getElementById(page_id);
	if( page ) {
		if (typeof( page.contentWindow.titleUpdate ) === "function") {
			return page.contentWindow.titleUpdate(title_id,value);
		}
	}

}


function title_from_page(page_id, dimension_id) {
	//if( page_id.substr(0,1) == "P" ) {
	//	page_id = page_id.substr(1,page_id.length-1);
	//}
	
	var idStr = "";
	if( page_0 == page_id ) {
		idStr = "page0";
	}
	if( page_1 == page_id ) {
		idStr = "page1";
	}
	if( page_2 == page_id ) {
		idStr = "page2";
	}
	
	
	var page = document.getElementById(idStr);
	if( page ) {
		if (typeof( page.contentWindow.titleElementByDimensionId ) === "function") {
			return page.contentWindow.titleElementByDimensionId(dimension_id);
		}
	}
	return "";
}

function refreshItem(idStr) {
	var page = document.getElementById(idStr);
	if( page ) {
		if (typeof( page.contentWindow.workviewSave ) === "function") {
			page.contentWindow.workviewSave();
		}
		if (typeof( page.contentWindow.refresh ) === "function") {
			page.contentWindow.refresh();
		}
	}		
}

function refreshOthers(page_loaded) {
	
	
	if( page_0 != "W_" + page_loaded &&  page_0 != "P_" + page_loaded ) {
		refreshItem("page0");
	}
	if( page_1 != "W_" + page_loaded &&  page_1 != "P_" + page_loaded ) {
		refreshItem("page1");
	}
	if( page_2 != "W_" +  page_loaded &&  page_2 != "P_" + page_loaded ) {
		refreshItem("page2");
	}
}

function refreshAll() {
	refreshItem("page0");
	refreshItem("page1");
	refreshItem("page2");
	
	
}

function toggleMenu(menu) {
	var display = document.getElementById(menu).style.display;
	if( display == "none" )
		display = "";

	if( display == "" ) {
		document.getElementById(menu).style.display = "block";
		$(".dropdown-menu").css("display","block");
	} else {
		document.getElementById(menu).style.display = "none";
		$(".dropdown-menu").css("display","none");
	}
}

$(document).ready(function () {
	$(window).on('resize', function(){
	 //this = window
	 resizeWindow();
	});
	resizeWindow();
}); 

function enableExport() {
	$("#btnExport").css("color","#fff");
}

function btnExport() {
	var page = document.getElementById("page0");
	if( page ) {
		if (typeof( page.contentWindow.btnExport ) === "function") {
			page.contentWindow.btnExport();
		}
	}
}