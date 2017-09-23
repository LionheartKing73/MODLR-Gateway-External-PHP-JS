<?
include_once("../lib/lib.php");

$model_contents = null;
$existing_cubes = array();
$id = querystring("id");
$error_message = "";
if( $id != "" ) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);

	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- model id found in server -->\r\n";
		
		$model_contents = $results->results[0]->model;
		$dimensions = $model_contents->dimensions;

		$cubes = $model_contents->cubes;
		$measure_name = $measure_dimension_id = "";
		foreach ($cubes as $c){
			array_push($existing_cubes, strtolower($c->name));
		}
		
	} else {
		header("Location: /home/");
	}
} else {
	header("Location: /home/");
}


if (isset($_POST['cube_name'])){
	//server side validation
	$cube_name= $_POST['cube_name'];
	//check if the measures dimensions exists already
	$measure_name_text = $_POST['measure_name_text'];
	$measure_dimension_id = "";
	foreach ($dimensions as $dim){
		if ($dim->type == "measure"){
			if (strtolower($dim->name) == strtolower(trim($measure_name_text))){
				$measure_dimension_id = $dim->id;
				break;
			}
		}
	}
	if ($measure_dimension_id == ""){
		//wasn't found, create a new dimension and assign the id
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"dimension.create\", \"id\" : \"".$id."\", \"name\" : \"".$measure_name_text."\", \"type\" : \"measure\", \"elements\":[{\"name\":\"Amount\", \"type\":\"N\"}] }";
		$json .= "]}";

		$results = api_short(SERVICE_MODEL, $json);
		$measure_dimension_id = $results->results[0]->id;
	}
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"cube.create\",  \"id\":\"" . $id . "\", \"name\":\"" . $cube_name . "\", \"dimensions\": [";
	foreach ($_POST['selected_dimensions'] as $dim){
		$json .= "\"$dim"."\",";
	}
	$json .= "\"$measure_dimension_id"."\",";
	$json = rtrim($json, ",");
	$json .= "]}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	if (intval($results->results[0]->result) == 0){
		$error_message = $results->results[0]->message;
	}
    else {
		header("Location: /model/?id=".$id);
	}
	

	
}

include_once("../lib/header.php");
?>
		<title>MODLR » Create a new Cube</title>
<?

include_once("../lib/body_start.php");

//outputModelToolbar($id, $name);
?>


				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								Create a new Cube
							</header>
							<div class="panel-body">
								<div class="position-center">
									<form action="#" class="form-horizontal" id = "create_cube_form" method = "POST">
										<div id = 'error_message' class = 'alert alert-danger' style = "display: <? echo ($error_message != "" ? "block" : "none");?>">
										<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
										<?
											echo $error_message;
										?>
										</div>
										<div id = 'warning_message' style='display:none;'>
										</div>
										<div class="form-group" id='cubeBlock'>
											<label for="input1" class="col-lg-2 control-label">Cube Name:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="cube_name" name="cube_name"  value="New Cube" placeholder="New Cube" required="true"  />
											</div>
										</div>
                                        
										<div class="form-group" id='cubeBlockNotes'>
											<label for="input1" class="col-lg-2 control-label">Note:</label>
											<div class="col-lg-10">
												<span class="help-block">In MODLR Cubes must have a Measures Dimension. An ideal naming convention for this is the cube name followed by " Measures".</span>
											</div>
										</div>
                                        
										<div class="form-group" id='measureBlock'>
											<label for="input1" class="col-lg-2 control-label">Measure Dimension:</label>
											<div class="col-lg-6">
											<select class="form-control" name = "measure_name_select" id="measure_name_select">
												<option value ="Create a new Measures Dimension">Create a new Measures Dimension</option>
												<?
													for($i=0;$i<count($dimensions);$i++) {
														$dim = $dimensions[$i];
														if ($dim->type == "measure")
															echo "<option value='".$dim->id."'>".$dim->name."</option>";
													}
												?>
												</select>
											</div>
											<div class="col-lg-4">

													<input type="input" class="form-control" id="measure_name_text" name="measure_name_text"  placeholder = "New Cube Measures Dimension"  />
											</div>
											
										</div>
									
									
                                            
                                        <div class="form-group" id='cubeDimensions'>
                                            <label for="input1" class="col-lg-2 control-label">Cube Dimensions: &nbsp;</label>
                                            
                                            <div class = 'col-md-10'>
                                                <div class = 'row'>
                                                
                                                
                                                    <div class = 'col-md-5'>
                                                        <label for="input1" class="control-label">Available Dimensions &nbsp;</label>
                                                        <div class="">
                                                            <select class="form-control" name = "existing_dimensions" id="existing_dimensions" size = "10">
                                                                <?
                                                                    for($i=0;$i<count($dimensions);$i++) {
                                                                        $dim = $dimensions[$i];
                                                                        if ($dim->type != "measure")
                                                                            echo "<option value='".$dim->id."'>".$dim->name."</option>";
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class='col-md-2' style='text-align:center;'>
                                                        <div class="control-label">&nbsp;</div>
                                                        <div class="btn-group-vertical"  role="group">

                                                            <button type="button" class = "btn btn-primary btn-sm" onclick = 'moveDimensionFromSelected();' id = "dimension_rtl"><span class="glyphicon glyphicon-backward"></span></button>
                                                            &nbsp;
                                                            <button type="button" class = "btn btn-primary btn-sm" onclick = 'moveDimensionToSelected();' id = "dimension_ltr"><span class="glyphicon glyphicon-forward"></span></button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class = 'col-md-5'>
                                                        <label for="input1" class="control-label">Selected Dimensions &nbsp;</label>
                                                        <div class="">
                                                            <select class="form-control" id="selected_dimensions" multiple="multiple" name = "selected_dimensions[]" size = "10">
                                                            
                                                            </select>
                                                        </div>
                                                    </div>
                                                
                                                </div>
                                            </div>
                                        </div>
                                                
                                                
										<div class="form-group">
											<div class="col-lg-offset-2 col-lg-10">
												<button class="btn btn-primary" type='button' onclick="createCube();">Create</button>
												<span class="btn btn-primary" onclick="window.location='/model/?id=<? echo $id;?>';">Cancel</span>
												<input type = "submit" name = "create_cube_btn" id ="create_cube_btn" style = "display: none;"/>
											</div>
										</div>
									
                                        <!--
										<div class="form-group" id='cubeBlockNotes'>
											<label for="input1" class="col-lg-2 control-label">Note:</label>
											<div class="col-lg-10">
												<span class="help-block">
													Workviews are effectively windows through to a specific slice of a cube. When configuring a workview, the underlying cube will only be effected if you perform one of the following actions:
													<ul>
														<li>Add or Remove a dimension from the target cube.</li>
														<li>Add items into a dimension at the bottom most level.</li>
													</ul>
													In both of these cases the editor will alert you that your change will affect the structure of the underlying cube long with the resulting implications.
												</span>
											</div>
										</div>
                                        -->
									</form>
								
								

								</div>
							</div>
						</section>
					</div>
					<!-- /Basic forms -->

					
				</div>
<?
include_once("../lib/body_end.php");
?>
<script>
function moveDimensionToSelected(){
	if ($('#existing_dimensions option:selected').length > 0){
		$('#selected_dimensions').append($('<option>', {
			value: $('#existing_dimensions option:selected').val(),
			text: $('#existing_dimensions option:selected').text()
		}));

		$('#existing_dimensions option:selected').remove();


	}
}

function moveDimensionFromSelected(){
		if ($('#selected_dimensions option:selected').length > 0){
			$('#existing_dimensions').append($('<option>', {
				value: $('#selected_dimensions option:selected').val(),
				text: $('#selected_dimensions option:selected').text()
			}));

			$('#selected_dimensions option:selected').remove();


		}
	
}

function createCube(){
	var existing_cubes = <? echo json_encode($existing_cubes); ?>;
	var cube_name = $("#cube_name").val().toLowerCase();
	var is_cube_name_existing = true;
	if (jQuery.inArray(cube_name, existing_cubes) == -1 && cube_name.length > 0){
		is_cube_name_existing = false;
		$("#cube_name").css('border-color', '');
		$("#warning_message").css('display', 'none');
		$("#selected_dimensions").find('option').each(function(){
			$(this).prop('selected', true); //everything else..
			$(this).attr('selected', true); //safari
		});
		$("#create_cube_form").submit();
		
	}
	if (is_cube_name_existing || cube_name.length == 0 ) {
		$("#warning_message").css('display', 'block');
		$("#warning_message").addClass('alert alert-danger');
		$("#warning_message").html('<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Please pick another cube name');		
		$("#cube_name").css('border-color', 'red');
	}
}

$(function(){
	$("#existing_dimensions").dblclick(function(){
		moveDimensionToSelected();
	});
	$("#selected_dimensions").dblclick(function(){
		moveDimensionFromSelected();
	});
	$("#measure_name_select").change(function(){  
        var val = $("#measure_name_select").val();
        if( val == "Create a new Measures Dimension" ) {
            var def = ($("#cube_name").val() + " Measures").trim();
            $("#measure_name_text").css("display","block");
            $("#measure_name_text").val(def);
            $("#measure_name_text").focus();
        } else {
            $("#measure_name_text").css("display","none");
            $("#measure_name_text").val($("#measure_name_select option:selected").html());
        }
    
    });
    
    $("#cube_name").change(function(){  
        var val = $("#measure_name_select").val();
        if( val == "Create a new Measures Dimension" ) {
            var def = ($("#cube_name").val() + " Measures").trim();
            $("#measure_name_text").val(def);
        }
    });

	$("#existing_dimensions").click(function(){
		$("#dimension_rtl").attr("disabled", true);
		$("#dimension_ltr").removeAttr("disabled");

	});

	$("#selected_dimensions").click(function(){
		$("#dimension_ltr").attr("disabled", true);
		$("#dimension_rtl").removeAttr("disabled");

	});

});
</script>
<?
include_once("../lib/footer.php");
?>