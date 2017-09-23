<?
include_once("../lib/lib.php");
require 'includes.php';

$username = form("username");
$action = form("action");

$msg = "";

if( $action == "setup" ) {
	$username = mysql_real_escape_string( $username );
	
	$user_account = $db->get_results("SELECT id FROM " . TABLES_PREFIX . "users WHERE username = '".$username."';");
	if($user_account){
		foreach($user_account as $user){
			if( $user->id == $_SESSION['uid'] ) {
				Header("Location: /forum/");
				die();
			}
		}
		$msg = "Sorry, The specified username has already been taken.";
	} else {
		$sql = "INSERT INTO " . TABLES_PREFIX . "users (id, username, email) VALUES (".$_SESSION['uid'].",'".$username."','".$_SESSION['username']."');";
		$db->get_results($sql);
		//echo $sql;
		Header("Location: /forum/");
		die();
	}
}

include_once("../lib/header.php");
?>
		<title>Modlr » Forum » Account Setup</title>
<?

include_once("../lib/body_start.php");

?>

		
				<div class="row">
				
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								Setup Forum Account
							</header>
							<div class="panel-body">
					
								<p>Before you can use the forums you will need to specify a unique Forum username: </p>
								<form name='account' id='account' action="/forum/setup/" method='post' class="form-horizontal">

									<div class="form-group" id='cubeBlock'>
										<label for="input1" class="col-lg-2 control-label">Forum Username:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="username" name="username" value="<? echo $username;?>" placeholder="New Username"/>
										</div>
									</div>
									
									<input type="hidden" class="form-control" id="action" name="action" value="setup"/>
								</form>
								
								<span class="help-block">Note: You may user your name however as your posts will be publicly viewable to all users on the Modlr forums it may not be advisable.</span>

								<span class="btn btn-primary pull-right" onclick="document.getElementById('account').submit();">Check Availability & Save</span>
							</div>
						</section>
					</div>

				</div>
<?
include_once("../lib/body_end.php");
?>
 
<?
include_once("../lib/footer.php");
?>