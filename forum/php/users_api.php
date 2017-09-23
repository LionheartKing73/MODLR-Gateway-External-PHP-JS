<?php



function checkCreateAccount() {
	global $db;
	$user_account = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = ".$_SESSION['uid'].";");
	if($user_account){
	
	} else {
		$page = $_SERVER["REQUEST_URI"];
		if( $page != "/forum/setup/" ) {
			Header("Location: /forum/setup/");
			die();
		}
	}
}

checkCreateAccount();


//Check if there is an active session
//Return boolean
function Users_IsUserLoggedIn(){
	$db = new Db;
	
	if( $_SESSION["uid"] != "" ) {
		return true;
	}
	return false;
	
}


//Checks if the user with the $id is an administrator
//Return boolean
function Users_IsUserAdmin($id){

	if(!$id)
		return false;
		
	if( intval($id) == 9 ) {
		return true;
	}	
	return false;
	
}

//Checks if the user with the $id is a moderator
//Return boolean
function Users_IsUserModerator($id){
	if(!$id)
		return false;
		
	if( $id == 0 ) {
		return true;
	}	
	return false;
	
}

function Users_IsUserAdminOrModerator($id){
	if(!$id)
		return false;
	if( $id == 9 ) {
		return true;
	}	
	return false;
	
	
}

//Check if the current user is able to start threads and reply to them
//Return boolean
function Users_CanCurrentUserPost(){
	
	if(!Users_CurrentUserId())
		return false;
		
	return true;
	
}

//Gets the current user's ID
//Return an integer
function Users_CurrentUserId(){
	if(isset($_SESSION["uid"])){
		return intval($_SESSION["uid"]);
	}else{
		return null;
	}
}

//Gets the current user's username
//Return an string
function Users_CurrentUserUsername(){
	return $_SESSION['name'];
}

//Gets the details of the user with the $id
//path_to_photo and bio are optional. Everything else is required.
//Return an array: array('id'=>[int],'username'=>[string],'email'=>[string],'path_to_photo'=>[string],'is_admin'=>[bool],'bio'=>[string])
function Users_GetUserDetails($id){
	$return = array('id'=>null,'username'=>'','email'=>'','path_to_photo'=>'','is_admin'=>false,'bio'=>'','signature'=>'');
	$db = new Db;
	$user = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	if($user){
		$return['id'] = $user->id;
		$return['username'] = $user->username;
		$return['email'] = $user->email;
		if($user->role == 'admin'){
			$return['is_admin'] = true;
		}else{
			$return['is_admin'] = false;
		}
		if($user->role == 'moderator'){
			$return['is_moderator'] = true;
		}else{
			$return['is_moderator'] = false;
		}
		$return['path_to_photo'] = '/images/logo_square-300x300.png';
		
		$return['bio'] = $user->bio;
		$return['signature'] = $user->signature;
		$return['path_to_profile'] = FORUM_URL . 'profiles.php?id='.$user->id;
	}
	return $return;
}

//Get the html for displaying user badges/achievements
function Users_GetUserBadges($id){
	$db = new Db;
	$badges_html = '';
	$all_posts_count = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE user_id =".intval($id) ));
	$started_threads_count = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE is_question = 'y' AND user_id =".intval($id) ));
	$replies_count = $all_posts_count - $started_threads_count;
	$months_query = $db->get_var("SELECT added_on FROM " . TABLES_PREFIX . "users WHERE id = " . intval($id));
	if($months_query){
		$months_count = intval(floor(abs(time() - strtotime($months_query))/(60*60*24*30)));
	}else{
		$months_count = 0;
	}
	
	$achievements_query = $db->get_var("SELECT achievements FROM " . TABLES_PREFIX . "users WHERE id = " . intval($id));
	if($achievements_query){
		$achievements_array =  unserialize($achievements_query);
		$manual_achievements = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements WHERE id IN ( ". implode(",", $achievements_array) .")");
		if($manual_achievements){
			foreach($manual_achievements as $b){
				$badges_html .= '<img src="'.FORUM_URL.$b->icon.'" title="' . $b->name . '"/>&nbsp;';
			}
		}
	}
	
	
	$all_posts_badge = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements WHERE type = 'all_posts' AND start_from <= $all_posts_count AND end_at >= $all_posts_count");
	$started_threads_badge = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements WHERE type = 'started_threads' AND start_from <= $started_threads_count AND end_at >= $started_threads_count");
	$replies_badge = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements WHERE type = 'replies' AND start_from <= $replies_count AND end_at >= $replies_count");
	
	$months_badge = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements WHERE type = 'membership_time' AND start_from <= $months_count AND end_at >= $months_count");
	
	if($all_posts_badge){
		foreach($all_posts_badge as $b){
			$badges_html .= '<img src="'.FORUM_URL.$b->icon.'" title="' . $b->name . '"/>&nbsp;';
		}
	}
	
	if($started_threads_badge){
		foreach($started_threads_badge as $b){
			$badges_html .= '<img src="'.FORUM_URL.$b->icon.'" title="' . $b->name . '"/>&nbsp;';
		}
	}
	
	if($replies_badge){
		foreach($replies_badge as $b){
			$badges_html .= '<img src="'.FORUM_URL.$b->icon.'" title="' . $b->name . '"/>&nbsp;';
		}
	}
	if($months_badge){
		foreach($months_badge as $b){
			$badges_html .= '<img src="'.FORUM_URL.$b->icon.'" title="' . $b->name . '"/>&nbsp;';
		}
	}
	return $badges_html;
}

//The page to direct users to sign in
//Return an string
function Users_SignInPageUrl(){
	return '/';
}

//The page to direct users to sign pout
//Return an string
function Users_SignOutPageUrl(){
	if(isset($_SESSION[TABLES_PREFIX.'sforum_logout_link']) AND $_SESSION[TABLES_PREFIX.'sforum_logout_link'] != ''){
		return $_SESSION[TABLES_PREFIX.'sforum_logout_link'];
	}else{
		return '/?logout=T';
	}
}

//The page to direct users to sign up
//Return an string
function Users_RegistrationPageUrl(){
	return FORUM_URL . 'signup.php';
}

//The page to direct users when they have forgotten their password
//Return an string
function Users_ForgotPasswordPageUrl(){
	return FORUM_URL . 'forgot.php';
}

//The page to direct users to change their details
//Return an string
function Users_ChangeProfilePageUrlCurrentUser(){
	return FORUM_URL . 'profile.php';
}
