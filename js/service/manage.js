(function(){

//$('#modeller-table').dataTable();
$('#collaborators-table').dataTable();

})()


// Capture current tab index.  Remember tab index starts at 0 for tab 1.
$('li.tabHeading').click(function(e) {
	curTabIndex = getDataset(this,"index");
	localStorage.setItem("Manage.Tab.Active", curTabIndex);
});

var activeTabOnLoad = localStorage.getItem("Manage.Tab.Active");
if( !activeTabOnLoad ) 
	activeTabOnLoad = 0;
else
	activeTabOnLoad = parseInt(activeTabOnLoad);

// Set active tab on page load
if( activeTabOnLoad > 0 ) {
	$(".tabHeading").removeClass('active');
	$(".tab-pane").removeClass('active');
	
	var tab = "";
	if( activeTabOnLoad == 0 ) {
		tab = "account";
	} else if( activeTabOnLoad == 1 ) {
		tab = "servers";
	} else if( activeTabOnLoad == 2 ) {
		tab = "billing";
	} else if( activeTabOnLoad == 3 ) {
		tab = "settings";
	}
	$("#" + tab).addClass('active');
	$("#H" + tab).addClass('active');
}



function restart_server(server_id, server_identifier) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to restart this server "' + server_identifier + '"?</b></div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
                window.location='/manage/?action=restart&server_id='+server_id;
	
                $(this).dialog("close");
            },
            No: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
}

function remove_server(server_id, server_identifier) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove "' + server_identifier + '" from this account.</b><br/>Note: This action is not reversable.</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
                
				window.location='/manage/?action=remove_server&serverid='+server_id;
	
                $(this).dialog("close");
            },
            No: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
}

function remove_user(userid, username) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove "' + username + '" from this account.</b><br/>Note: This will remove this user from all activities.</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
                window.location='/manage/?action=remove_user&userid='+userid;
	
                $(this).dialog("close");
            },
            No: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
}
