<?php

require 'includes.php';

$facebook = new Facebook(array('appId'=>FACEBOOK_APP_ID,'secret'=>FACEBOOK_APP_SECRET));
$user = $facebook->getUser();
if($user){
	try{
		$user_profile = $facebook->api('/me');
		$user_details = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE facebook_id = '" . $user_profile['id'] . "' ORDER BY id DESC LIMIT 0,1");
		if($user_details){
			$_SESSION[TABLES_PREFIX.'sforum_logged_in'] = true;
			$_SESSION[TABLES_PREFIX.'sforum_user_id'] = $user_details->id;
			$_SESSION[TABLES_PREFIX.'sforum_user_role'] = $user_details->role;
			$_SESSION[TABLES_PREFIX.'sforum_user_username'] = $user_details->username;
			$_SESSION[TABLES_PREFIX.'sforum_profile_link'] = $user_profile['link'];
			$_SESSION[TABLES_PREFIX.'sforum_logout_link'] = $facebook->getLogoutUrl(array( 'next' => FORUM_URL . 'signout.php'));
		}else{
			$values = array();
			$format = array();
			
			$values['email'] = $user_profile['email'];
			$format[] = "%s";
			
			$values['facebook_id'] = $user_profile['id'];
			$format[] = "%s";
			
			$values['username'] = $user_profile['username'];
			$format[] = "%s";
			
			$values['role'] = 'user';
			$format[] = "%s";
			
			$values['status'] = 'active';
			$format[] = "%s";
			
			$values['added_on'] = date('Y-m-d H:i:s');
			$format[] = "%s";
			
			$values['likes'] = 0;
			$format[] = "%d";
			
			$db->insert(TABLES_PREFIX . "users", $values, $format);
			
			$_SESSION[TABLES_PREFIX.'sforum_logged_in'] = true;
			$_SESSION[TABLES_PREFIX.'sforum_user_id'] = $db->insert_id;
			$_SESSION[TABLES_PREFIX.'sforum_user_role'] = 'user';
			$_SESSION[TABLES_PREFIX.'sforum_user_username'] = $user_profile['username'];
			$_SESSION[TABLES_PREFIX.'sforum_profile_link'] = '';
			$_SESSION[TABLES_PREFIX.'sforum_logout_link'] = $facebook->getLogoutUrl(array( 'next' => FORUM_URL . 'signout.php'));
		}
	}catch(FacebookApiException $e){
		error_log($e);
		$user = NULL;
	}
}
	
if(empty($user)){
	$loginurl = $facebook->getLoginUrl(array('scope'=> 'email','redirect_uri'=> FORUM_URL.'facebook_login.php','display'=>'popup'));
	header('Location: '.$loginurl);
}
?>
<!-- after authentication close the popup -->
<script type="text/javascript">
window.close();
</script>
