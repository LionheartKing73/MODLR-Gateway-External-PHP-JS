<?
$authToken = "";
include_once("lib/lib.php");
include_once("lib/header.php");
?>
		<title>MODLR » API</title>
		<style>

pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
.string { color: green; }
.number { color: darkorange; }
.boolean { color: blue; }
.null { color: magenta; }
.key { color: red; }

		</style>
<?
include_once("lib/body_start.php");

$server_address = session("server_address");
$responseData = api_short("api.service", "{}");
?>
<script type='text/javascript'>
<? echo "var apiMap = ".json_encode($responseData).";\r\n"; ?>

function onServiceChange() {
	var lstService = document.getElementById('lstService');
	var item = lstService.options[lstService.selectedIndex].value;
	
	$("#lstTasks").empty()
	
	var services = apiMap['results'];
	for(var i=0;i<services.length;i++) {
		if( services[i]['name'] == item ) {
			var taskArray = services[i]['tasks'];
			for(var k=0;k<taskArray.length;k++) {
				var task = taskArray[k];
				var opt = new Option(task['name'], task['name'], false, false);
				$("#lstTasks")[0].options.add(opt);
			}
		}
	}
	
	onTaskChange();
}

function onTaskChange() {
	
	var lstService = document.getElementById('lstService');
	var item = lstService.options[lstService.selectedIndex].value;
	
	var lstTasks = document.getElementById('lstTasks');
	var itemTask = lstTasks.options[lstTasks.selectedIndex].value;
	
	
	var jsonStr = '{"tasks": [\r\n{"task": '; 
	
	var services = apiMap['results'];
	for(var i=0;i<services.length;i++) {
		if( services[i]['name'] == item ) {
			var taskArray = services[i]['tasks'];
			for(var k=0;k<taskArray.length;k++) {
				var task = taskArray[k];
				if( task['name'] == itemTask ) {
					var argArray = task['arguments'];
					
					jsonStr += '"' + task['name'] + '"';
					
					for(var p=0;p<argArray.length;p++) {
						jsonStr += ', "' + argArray[p]['name'] + '": ""';
					}
					
					jsonStr += '}';
					
					
					$("#lblDescription").html(task.description);
					
				}
			}
		}
	}
	
	jsonStr += '\r\n ]}';
	$('#txtInput').val(jsonStr);
}

function call() {
	var queryStr = document.getElementById('txtInput').value;
	var queryObj = jQuery.parseJSON(queryStr);
	
	var lstService = document.getElementById('lstService');
	var item = lstService.options[lstService.selectedIndex].value;
	item = item.substring(1,item.length);

	query(item,queryObj,call_cb);
	
}


function syntaxHighlight(json) {
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}


function call_cb(data) {
	
	var str = JSON.stringify(JSON.parse(data), undefined, 4);
	str = syntaxHighlight(str);
	str = str.replace(/(?:\r\n|\r|\n)/g, '<br/>');
	str = str.replace(/    /gi,'&nbsp;&nbsp;&nbsp;&nbsp;');
	document.getElementById('txtResponse').innerHTML = str;

    /*
	$( "#lstTasks" ).change(function() {
	  onTaskChange(); 
	});
    */
}



</script>

				<div class="row">
					
					
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								API Method - Input
							</header>
							<div class="panel-body">
					
								
								<form action="#" class="form-horizontal">

									<div class="form-group">
										<label for="lstService" class="col-lg-2 control-label">Service:</label>
										<div class="col-lg-10">
											<select id='lstService' class="form-control" onChange='onServiceChange();'>
<?
$services = $responseData->results;
for($i=0;$i<count($services);$i++) {
	echo "<option value='".$services[$i]->name."'>".$services[$i]->name."</option>";
}
?>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label for="lstTasks" class="col-lg-2 control-label">Task:</label>
										<div class="col-lg-7">
											<select id='lstTasks' class="form-control" onChange='onTaskChange();'>
											</select>
										</div>
									</div>
									

									<div class="form-group">
										<label for="lstTasks" class="col-lg-2 control-label">Description:</label>
										<div class="col-lg-7">
											<label id="lblDescription" class="control-label"></label>
										</div>
									</div>
									
									
									<div class="form-group">
										<label for="txtInput" class="col-lg-2 control-label">JSON call</label>
										<div class="col-lg-10">
											<textarea id='txtInput' class="form-control" rows="5"></textarea>
										</div>
									</div>
	
	
									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											<span class="btn btn-primary" onclick='call()'>Call</span>
										</div>
									</div>

								</form>
								
							</div>
						</section>
					</div>
					
					
				</div>



				<div class="row">

					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								API Method - Response
							</header>
							<div class="panel-body">
							
								
								<form action="#" class="form-horizontal">


									<div class="form-group">
										<label for="txtResponse" class="col-lg-2 control-label">Response Data</label>
										<div class="col-lg-10">
											<div id='txtResponse' class="form-control" style='width:100%;height:350px;overflow-y:scroll;'></div> 
										</div> 
									</div>
	
								</form>

							</div>
						</section>
					</div>
					
					
				</div>



<?
include_once("lib/body_end.php");
?>
<script>
var server_id = <? echo  session("active_server_id");?>;

$(function(){
  onServiceChange();
  onTaskChange();
});

$( "#lstTasks" ).change(function() {
  onTaskChange();
});

</script>
<?
include_once("lib/footer.php");
?>
