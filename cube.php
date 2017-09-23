<?
include_once("lib/lib.php");


$id = querystring("id");
if( $id != "" ) {
	echo "<!-- model id provided -->\r\n";
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);

	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- model id found in server -->\r\n";
		
		$contents = $results->results[0]->model;
		$name = $contents->name;

	} else {
		header("Location: /home/");
	}
} else {
	header("Location: /home/");
}

include_once("lib/header.php");
?>
		<title><? echo C_APP_NAME;?> <? echo C_APP_VERSION_FORMAT;?> - Home</title>
<?

include_once("lib/body_start.php");
?>

				<div class="row">
				
					<div class="col-lg-6">
						<div class="module">
							<div class="module-header"><h4>Add a Model</h4></div>
							<div class="module-content">

								<form action="/model/" method='post' class="form-horizontal">

									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Name:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="txtNewModel" name="txtNewModel" value="<? echo $name;?>" placeholder="New Datasource" />
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											<button class="btn btn-primary" type'submit'>Save</button>
											<span class="btn btn-primary" onclick="window.location='/home/';">Cancel</span>
										</div>
									</div>

								</form>

							</div>
						</div>
					</div>
					<!-- /Basic forms -->

					<div class="col-lg-6">
						<div class="module module-purple">
							<div class="module-header"><h4>Model Development</h4></div>
							<div class="module-content">

								<h4>Getting Started</h4>
								<p>It's recommended that you approach developing a model by beginning with 
								actual data you have from executing business. This may be:
								<ul>
								<li>Ledger balances or journals from a ERP Solution.</li>
								<li>Sales transactions or balances from a POS systems or data warehouse.</li>
								<li>This could simply be various files which various divisions of the business submit you.</li>
								</ul>
								</p>
								
								<p class="text-muted">Note: After naming this model you will be asked to create your first cube.</p>
								
							</div>
						</div>
					</div>
					
				</div>
	
<?
include_once("lib/body_end.php");
?>
<?
include_once("lib/footer.php");
?>