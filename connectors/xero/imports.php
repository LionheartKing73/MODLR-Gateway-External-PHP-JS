<?php

if (isset($_REQUEST)){
	if (!isset($_REQUEST['where'])) $_REQUEST['where'] = "";
}

if(isset($_REQUEST['refresh'])) {
    $response = $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['oauth_session_handle']);
    if ($XeroOAuth->response['code'] == 200) {
        $session = persistSession($response);
        $oauthSession = retrieveSession();
    } else {
        outputError($XeroOAuth);
        if ($XeroOAuth->response['helper'] == "TokenExpired") $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['session_handle']);
    }

}

function dimensionGetId($modelid, $nameOrId) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"dimension.get\", \"id\" : \"".$modelid."\", \"dimensionid\" : \"".$nameOrId."\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		return "";
	}
	return $results->results[0]->id;
}
function cubeGetId($modelid, $nameOrId) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"cube.get\", \"id\" : \"".$modelid."\", \"cubeid\" : \"".$nameOrId."\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		return "";
	}
	return $results->results[0]->id;
}
function cubeCreate($modelid, $name) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"cube.create\", \"id\" : \"".$modelid."\", \"name\" : \"".$name."\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		return "";
	}
	return $results->results[0]->id;
}
function cubeDimensionAdd($modelid, $cube, $dimid) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"cube.dimensionadd\", \"id\" : \"".$modelid."\", \"cubeid\" : \"".$cube."\", \"dimensionid\" : \"".$dimid."\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		return "";
	}
	return;
}
function dimensionCreate($modelid, $name, $type) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"dimension.create\", \"id\" : \"".$modelid."\", \"name\" : \"".$name."\", \"type\" : \"".$type."\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		logLine("Error",$results->results[0]->message);
		return "";
	}
	return $results->results[0]->id;
}
function dimensionAliasCreate($modelid, $dimension, $alias) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"dimension.alias.create\", \"id\" : \"".$modelid."\", \"dimension\" : \"".$dimension."\", \"alias\" : \"".$alias."\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		logLine("Error",$results->results[0]->message);
		return "";
	}
	return $results->results[0]->result;
}
function dimensionUpdateHierarchy($modelid, $dimensionid, $definition) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"dimension.update.hierarchy\", \"id\" : \"".$modelid."\", \"dimensionid\" : \"".$dimensionid."\", \"definition\" : ".$definition." }";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		logLine("Error",$results->results[0]->message);
		return false;
	}
	return true;
}
function cubeUpdate($modelid, $cubeid, $definition) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"cube.update\", \"id\" : \"".$modelid."\", \"cubeid\" : \"".$cubeid."\", \"definition\" : {\"updates\" : ".$definition."} }";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	
	//print_r($results);
	
	if( property_exists( $results->results[0]  , 'message' ) ) { 
		logLine("Error",$results->results[0]->message);
		return false;
	}
	return true;
}


function importData($modelid, $type, $month) {
	global $XeroOAuth;
	global $oauthSession;
	
    $XeroOAuth->config['access_token']  = $oauthSession['oauth_token'];
    $XeroOAuth->config['access_token_secret'] = $oauthSession['oauth_token_secret'];
    $XeroOAuth->config['session_handle'] = $oauthSession['oauth_session_handle'];
	

	if( $type == "ProfitAndLoss" ) {
		logLine("Accounts","Building the Account Dimension.");
		
		
		$response = $XeroOAuth->request('GET', $XeroOAuth->url('Accounts', 'core'), array('Where' => $_REQUEST['where']));
		if ($XeroOAuth->response['code'] == 200) {
			
			
			$dimension = "Time";
			$dimensionId = dimensionGetId($modelid, $dimension);
			if( $dimensionId == "" ) {
				$dimensionId = dimensionCreate($modelid, $dimension, "time");
			}
			
			$accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
			logLine("Accounts",count($accounts->Accounts[0]) . " Account's Found.");
			$dimension = "Account";
			$dimensionId = dimensionGetId($modelid, $dimension);
			if( $dimensionId == "" ) {
				$dimensionId = dimensionCreate($modelid, $dimension, "standard");
			}
			dimensionAliasCreate($modelid, $dimension, "No - Name");
			
			//hierarchy
			$definition = "{\"name\": \"All Accounts\" ,\"root\" : [";
			foreach($accounts->Accounts[0]->Account as $account) {
				if( $account->ReportingCode != "ASS" ) {
					
					//$account->Name
					$definition .= "{\"name\" : \"".$account->Name."\"},";
					//pr($account);
				}
			}
			$definition = substr($definition,0,strlen($definition)-1) . "]}";
			
			dimensionUpdateHierarchy($modelid, $dimensionId, $definition);
			
			
			
			
			logLine("Accounts","Accounts Saved.");
			
			
			$organisationName = "";
			
			$response = $XeroOAuth->request('GET', $XeroOAuth->url('Organisation', 'core'), array('page' => 0));
		    if ($XeroOAuth->response['code'] == 200) {
			   	$organisation = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
			   	
			   	$organisationName = $organisation->Organisations[0]->Organisation->Name;
			   	
		    } else {
			    outputError($XeroOAuth);
		    }
		    
			
			$dimensionCompany = "Company";
			$dimensionIdCompany = dimensionGetId($modelid, $dimensionCompany);
			if( $dimensionIdCompany == "" ) {
				$dimensionIdCompany = dimensionCreate($modelid, $dimensionCompany, "standard");
			}
			$definition = "{\"name\": \"".$organisationName."\" ,\"root\" : [{\"name\" : \"".$organisationName."\"}]}";
			dimensionUpdateHierarchy($modelid, $dimensionIdCompany, $definition);
			
			
			$cube = "Profit and Loss";
			$cubeId = cubeGetId($modelid,$cube);
			if( $cubeId == "" ) {
				$cubeId = cubeCreate($modelid,$cube);
				//add dimensions as required
				cubeDimensionAdd($modelid,$cubeId,$dimensionId);
				
				//add dimensions as required
				cubeDimensionAdd($modelid,$cubeId,$dimensionIdCompany);
				
				
				logLine("Profit and Loss","Build the Profit and Loss Cube.");
			} else {
				
				logLine("Profit and Loss","Updating the Profit and Loss Cube.");
			}
			//cube_create
			
			
			
			
			$query_date = $month;
			$query_date_system = date('Y - M', strtotime($query_date));
			$query_date_start 	= date('Y-m-01', 	strtotime($query_date));
			$query_date_end 	= date('Y-m-t', 	strtotime($query_date));
			
			$updates = "[";
			$elementPath = $query_date_system . "|Actual|";
			//{"updates":[{"value":"4","elements":["FY2014»2014 - Oct","Actual","No Hierarchy»482","Default»Amount"]},{"value":"45","elements":["FY2014»2014 - Nov","Actual","No Hierarchy»482","Default»Amount"]},{"value":"456","elements":["FY2014»2014 - Dec","Actual","No Hierarchy»482","Default»Amount"]}]}
			
			$response = $XeroOAuth->request('GET', $XeroOAuth->url('Reports/ProfitAndLoss', 'core'), array('page' => 0,'fromDate' => $query_date_start,'toDate' => $query_date_end));
			if ($XeroOAuth->response['code'] == 200) {
				$report = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
				
				$rep = $report->Reports[0]->Report;
				$rows = $rep->Rows;
				
				foreach($rows->Row as $row) {
					//echo "Top Row<br/>";
					if( $row->RowType == "Section" ) {
						//echo "Section<br/>";
						if( property_exists( $row , "Rows" ) ) {
							foreach( $row->Rows->Row as $datarow ) {
								//echo "Rows<br/>";
								if( $datarow->RowType == "Row" ) {
									//echo "Row<br/>";
									$cellHeader = $datarow->Cells->Cell[0]->Value;
									$cellValue = $datarow->Cells->Cell[1]->Value;
								
									//echo $cellHeader . "->". $cellValue . "<br/>" ;
									
									$elms = "[\"".$query_date_system."\",\"Actual\",\"".$cellHeader."\",\"".$organisationName."\",\"Amount\"]";
									$updates .= "{\"value\":\"".$cellValue."\", \"elements\":".$elms."},";
									
								}
							}
						}
					}
				}
				
				$updates = substr($updates,0,strlen($updates)-1) . "]";
				
				cubeUpdate($modelid, $cubeId, $updates);
				logLine("Profit and Loss","Saved Data for ".$query_date_system);
			} else {
				outputError($XeroOAuth);
			}
			
			
			
			$cube = "Balance Sheet";
			$cubeId = cubeGetId($modelid,$cube);
			if( $cubeId == "" ) {
				$cubeId = cubeCreate($modelid,$cube);
				//add dimensions as required
				cubeDimensionAdd($modelid,$cubeId,$dimensionId);
				
				//add dimensions as required
				cubeDimensionAdd($modelid,$cubeId,$dimensionIdCompany);
				
				
				logLine("Profit and Loss","Build the Balance Sheet Cube.");
			} else {
				
				logLine("Profit and Loss","Updating the Balance Sheet Cube.");
			}
			//cube_create
			
			$updates = "[";
			$response = $XeroOAuth->request('GET', $XeroOAuth->url('Reports/BalanceSheet', 'core'), array('page' => 0,'date' => $query_date_end));
			if ($XeroOAuth->response['code'] == 200) {
				$report = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
				
				$rep = $report->Reports[0]->Report;
				$rows = $rep->Rows;
				
				foreach($rows->Row as $row) {
					//echo "Top Row<br/>";
					if( $row->RowType == "Section" ) {
						//echo "Section<br/>";
						if( property_exists( $row , "Rows" ) ) {
							foreach( $row->Rows->Row as $datarow ) {
								//echo "Rows<br/>";
								if( $datarow->RowType == "Row" ) {
									//echo "Row<br/>";
									$cellHeader = $datarow->Cells->Cell[0]->Value;
									$cellValue = $datarow->Cells->Cell[1]->Value;
								
									//echo $cellHeader . "->". $cellValue . "<br/>" ;
									
									$elms = "[\"".$query_date_system."\",\"Actual\",\"".$cellHeader."\",\"".$organisationName."\",\"Amount\"]";
									$updates .= "{\"value\":\"".$cellValue."\", \"elements\":".$elms."},";
									
								}
							}
						}
					}
				}
				
				$updates = substr($updates,0,strlen($updates)-1) . "]";
				
				cubeUpdate($modelid, $cubeId, $updates);
				logLine("Profit and Loss","Saved Data for ".$query_date_system);
			} else {
				outputError($XeroOAuth);
			}
			
			
			
		} else {
			//outputError($XeroOAuth);
			
			logLine("Accounts","The account load process failed.");
			
			echo "<script type=\"text/javascript\">\r\nwindow.location=\"/connectors/xero/public.php?authenticate=1\";</script>";
		}
	
	
	
	} else if( $type == "Payroll" ) {
		//https://api.xero.com/api.xro/2.0/Accounts
		//https://api.xero.com/payroll.xro/1.0/Employees
		$response = $XeroOAuth->request('GET', $XeroOAuth->url('Employees', 'payroll'), array('Where' => $_REQUEST['where']));
		if ($XeroOAuth->response['code'] == 200) {
		
			$payroll = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
			logLine("Employees",count($accounts->Accounts[0]) . " Employee's Found.");
			$dimension = "Employee";
			$dimensionId = dimensionGetId($modelid, $dimension);
			if( $dimensionId == "" ) {
				$dimensionId = dimensionCreate($modelid, $dimension, "standard");
			}
			
			
			
		}
		
	}
}

function logLine($area, $message) { 
	
	echo '<div class="msg-time-chat"><div class="message-body msg-in"><span class="arrow"></span><div class="text">
<div class="first">'.$area.'</div><div class="second bg-blue">'.$message.'</div></div></div></div>';
	
	
}

