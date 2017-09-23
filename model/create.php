<?
include_once("../lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$model_name = form("txtNewModel");
if( $model_name != "" ) {
	
	$list_granularity = form("list_granularity");
	$list_financialyear = form("list_financialyear");
	$list_monthbuild = form("list_monthbuild");
	
	//"timegranularity","timemonthbuild","timefinancialyear"
	
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.create\", \"name\":\"" . $model_name . "\", \"timegranularity\":\"" . $list_granularity . "\", \"timemonthbuild\":\"" . $list_monthbuild. "\", \"timefinancialyear\":\"" . $list_financialyear  . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	
	if( intval($results->results[0]->result) == 1 ) { 
		$id = $results->results[0]->id;
		redirectToPage ("/model/?id=" . $id);
		die();
	} else {
		
		
		
	}
	
}

include_once("../lib/header.php");

echo "<title>MODLR » Create a Model</title>";
	
include_once("../lib/body_start.php");

?>	

				<div class="row">
				
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Create a new Model
							</header>
							<div class="panel-body">
				
									<form action="/model/" method='post' class="form-horizontal">

										<div class="form-group">
											<label for="input1" class="col-lg-2 control-label">Model Name:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="txtNewModel" name="txtNewModel" value="" placeholder="New Model Name" />
												<span class="help-block">Example: Corporate Budget, Business Plan, Acquisitions Planning</span>
											</div>
										</div>
									
									
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">Time Granularity:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_granularity' name='list_granularity'>
													<option>Time</option>
													<option>Day</option>		
													<option>Week</option>		
													<option selected>Month</option>		
													<option>Quarter</option>		
													<option>Year</option>										
												</select>
											</div>
										</div>
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">Financial Year:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_financialyear' name='list_financialyear'>
													<option>Jan to Dec</option>	
													<option>Feb to Jan</option>	
													<option>Mar to Feb</option>
													<option>Apr to Mar</option>
													<option>May to Apr</option>
													<option>Jun to May</option>
													<option selected>Jul to Jun</option>
													<option>Aug to Jul</option>
													<option>Sep to Aug</option>
													<option>Oct to Sep</option>
													<option>Nov to Oct</option>
													<option>Dec to Nov</option>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">Month Build:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_monthbuild' name='list_monthbuild'>
													<option>Calendar Days</option>
													<option>Weeks 445</option>
													<option>Weeks 454</option>
													<option>Weeks 544</option>
												</select>
												<span class="help-block">Note: Week based date frames assume a Jan start.</span>
											</div>
										</div>
									
									
									
									
										<div class="form-group">
											<div class="col-lg-offset-2 col-lg-10">
												<button class="btn btn-primary" type='submit'>Save</button>
												<span class="btn btn-primary" onclick="window.location='/home/';">Cancel</span>
											</div>
										</div>

									</form>

							</div>
						</section>
					</div>
					<!-- /Basic forms -->

					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Getting Started
								<span class="tools pull-right">
									<a class="fa fa-chevron-up" href="javascript:;"></a>
								</span>
							</header>
							<div class="panel-body" style="display: none;">
								
								<p>A Model contains the data cubes, reports, business logic and data load processes associated with a business activity.  </p>
								<p>When building a new model it is recommended that you begin with loading information about the company's current performance where possible. 
								This may be:
								<ul>
								<li>Last Months Profit & Loss, Balance Sheet, Sales by Product, Customer and Vendor</li>
								<li>Ledger balances or journals from your Accounting Solution</li>
								<li>Sales transactions or balances from your payment processing system or data warehouse</li>
								<li>This could simply be various files which various divisions of the business submit you</li>
								</ul>
								</p>
								
								<p class="text-muted">Note: Although it is recommended, it is not actually necessary to load external data into a model, you can create the model and copy/paste (or write) information and assumptions into the workviews.</p>
								
							</div>
						</section>
					</div>
					
					

					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Consideration
								<span class="tools pull-right">
									<a class="fa fa-chevron-up" href="javascript:;"></a>
								</span>
							</header>
							<div class="panel-body" style="display: none;" >

								<b>What are the various ways that we structure data for reporting?</b><br/>
								<p>MODLR supports the use of multiple structures to consolidate a single set of numbers. 
								For example you may have a set of accounts from your ERP solution, from this you may 
								produce a Profit & Loss report which consolidates accounts in a structure resulting in a 'Net Profit after Tax' 
								top-most element.</p>
								<p>From the same financial amounts against these accounts you might also have an alternate structure 
								which consolidates the numbers in a different way as requested by a specific division.</p>
								
								<p class="text-muted">Note: You have utilise multiple hierarchies on any dimension.</p>
								
								<b>Planning: At what level do we want to enter the budget?</b><br/>
								<p>Each company manages the budgeting process differently compiling the targets at 
								different levels of their ledger. You might require a low level of granularity for your budgets whereby you plan for each account and then provide more detail for specific accounts such as travel and payroll.</p>
								<p>By contrast you might plan at a theoretical planning level in which each planning account equates to a number of actual ledger accounts.</p>
								
								<p class="text-muted">Note: MODLR models support both of these examples.</p>
							</div>
						</section>
					</div>
					
					
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								What is in a Model?
								<span class="tools pull-right">
									<a class="fa fa-chevron-up" href="javascript:;"></a>
								</span>
							</header>
							<div class="panel-body" style="display: none;">
								<p><? echo C_APP_NAME_SHORT;?> models are built from only five components.</p>
								
								<table class="table table-striped">
									<thead>
										<tr>
											<th>Component</th>
											<th>Description</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Cubes</td>
											<td>Cubes are the storage component for data in MODLR. The data (numbers and comments) in cubes are stored at the intersection of items within the dimensions from which that cube is made up. For instance a Financials cube might have the following dimensions:
												<ul>
													<li>Time</li>
													<li>Scenario (Actual, Forecast or Budget)</li>
													<li>Department</li>
													<li>Account</li>
													<li>Measures (Amount)</li>
												</ul>
												<p>Within this sample cube you could get the total sales for the year at:</p>
												<ul>
													<li>2014 (from the Time Dimension)</li>
													<li>Actual (from the Scenario Dimension)</li>
													<li>All Departments (from the Department Dimension)</li>
													<li>600000 - Total Sales (from the Account Dimension)</li>
													<li>Amount (from the Measures Dimension)</li>
												</ul>
												<p>Note: Formulae are applied to Cubes and not Workviews. This means there will likely be a single consistant definition of business logic in all Workviews (Reports).</p>
											</td>
										</tr>
										<tr>
											<td>Workviews</td>
											<td>Workviews are reports and data entry templates which reflect a slice of a specific cube. When creating a Workview you can chose to use an existing cube or create a new cube. Often you will have many Workviews which refer to a single cube providing different ways of reporting on the same information. For example you might have the following Workviews of a single Financial cube:
												<ul>
													<li>Profit & Loss Report</li>
													<li>Budget Entry Workview</li>
													<li>Comparative Analysis (Act vs. Bud, This Month & YTD)</li>
													<li>Exception Commentary Report</li>
													<li>Departmental Profit & Loss Report</li>
													<li>Operating Expenditure vs. LY</li>
												</ul>
											
											</td>
										</tr>
										
										<tr>
											<td>Dimensions</td>
											<td>Dimensions are a way of defining information in a cube. Dimensions often focus on a single classification of information. For example a Department Dimension might look like this:
												<ul>
													<li>All Departments</li>
													<ul>
														<li>Finance</li>
														<li>Marketing</li>
														<li>Legal</li>
														<li>Human Resources</li>
														<li>Sales</li>
														<ul>
															<li>Sales - Online</li>
															<li>Sales - Channel</li>
															<li>Sales - Retail</li>
														</ul>
														<li>Information Technology</li>
													</ul>
												</ul>
												<p>When you report on an item in the tree which has children MODLR will automatically consolidate the numbers below this item in real-time to give you the total value.</p>
											</td>
										</tr>
										<tr>
											<td>Processes</td>
											<td>Processes load information into MODLR Models creating and updating Cubes and Dimensions. Processes can use a variety of data sources such as:
												<ul>
													<li>Microsoft SQL Server Databases</li>
													<li>MySQL Databases</li>
													<li>IBM DB2 Databases</li>
													<li>Any JDBC Datasource</li>
												</ul>
											</td>
											<p>Note: Most financial systems are developed on top of standard databases such as Microsoft SQL Server which work with MODLR.</p>
										</tr>
										<tr>
											<td>Activities</td>
											<td>Activities are the structure of collaborative planning within a Model. :
												<ul>
													<li>Custom Corporate Reporting & Analysis</li>
													<li>Taking data from one cube to another</li>
												</ul>
											</td>
										</tr>
									</tbody>
								</table>


							</div>
						</section>
					</div>
					
				</div>
	
<?
include_once("../lib/body_end.php");

include_once("../lib/footer.php");
?>
