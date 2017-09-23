<?php
function LoadSettings() {
	if ($site_name = get_option('site_name')) {
		define('SITE_NAME', $site_name);
	} else {
		define('SITE_NAME', 'Just A Forum');
	}

	if ($site_meta_desc = get_option('site_meta_desc')) {
		define('SITE_META_DESCRIPTION', $site_meta_desc);
	}

	if ($use_sef_urls = get_option('use_sef_urls')) {
		if ($use_sef_urls == 'y') {
			define('SEO_HUMAN_FRIENDLY_URLS', true);
		} else {
			define('SEO_HUMAN_FRIENDLY_URLS', false);
		}
	} else {
		define('SEO_HUMAN_FRIENDLY_URLS', false);
	}

	if ($make_forum_private = get_option('make_forum_private')) {
		if ($make_forum_private == 'y') {
			define('PRIVATE_FORUM', true);
		} else {
			define('PRIVATE_FORUM', false);
		}
	} else {
		define('PRIVATE_FORUM', false);
	}

	if ($classic_home_page = get_option('classic_home_page')) {
		if ($classic_home_page == 'y') {
			define('CLASSIC_HOME_PAGE', true);
		} else {
			define('CLASSIC_HOME_PAGE', false);
		}
	} else {
		define('CLASSIC_HOME_PAGE', false);
	}

	if ($theme = get_option('theme')) {
		define('THEME', $theme);
	} else {
		define('THEME', 'default');
	}

	if ($time_zone = get_option('time_zone')) {
		define('TIME_ZONE', $time_zone);
	} else {
		define('TIME_ZONE', 'UTC');
	}

	if ($rows_per_page = get_option('rows_per_page')) {
		define('ROWS_PER_PAGE', $rows_per_page);
	} else {
		define('ROWS_PER_PAGE', 15);
	}

	if ($base_path = get_option('base_path')) {
		define('BASE_PATH', $base_path . '/');
	} else {
		define('BASE_PATH', '');
	}

	if ($forum_url = get_option('forum_url')) {
		define('FORUM_URL', $forum_url . '/');
	} else {
		define('FORUM_URL', "https://go.modlr.co/forum/");
	}

	if ($site_url = get_option('site_url')) {
		define('SITE_URL', $site_url . '/');
	} else {
		define('SITE_URL', FORUM_URL);
	}

	if ($recaptcha_public_key = get_option('recaptcha_public_key')) {
		define('RECAPTCHA_PUBLIC_KEY', $recaptcha_public_key);
	} else {
		define('RECAPTCHA_PUBLIC_KEY', '');
	}

	if ($recaptcha_private_key = get_option('recaptcha_private_key')) {
		define('RECAPTCHA_PRIVATE_KEY', $recaptcha_private_key);
	} else {
		define('RECAPTCHA_PRIVATE_KEY', '');
	}

	if ($facebook_app_id = get_option('facebook_app_id')) {
		define('FACEBOOK_APP_ID', $facebook_app_id);
	} else {
		define('FACEBOOK_APP_ID', '');
	}

	if ($facebook_app_secret = get_option('facebook_app_secret')) {
		define('FACEBOOK_APP_SECRET', $facebook_app_secret);
	} else {
		define('FACEBOOK_APP_SECRET', '');
	}

	if ($admin_approve_posts = get_option('admin_approve_posts')) {
		if ($admin_approve_posts == 'y') {
			define('ADMIN_APPROVE_POSTS', true);
		} else {
			define('ADMIN_APPROVE_POSTS', false);
		}
	} else {
		define('ADMIN_APPROVE_POSTS', false);
	}

	if ($email_admin_on_flag = get_option('email_admin_on_flag')) {
		if ($email_admin_on_flag == 'y') {
			define('EMAIL_ADMIN_ON_FLAG', true);
		} else {
			define('EMAIL_ADMIN_ON_FLAG', false);
		}
	} else {
		define('EMAIL_ADMIN_ON_FLAG', false);
	}

	if ($email_admin_on_signup = get_option('email_admin_on_signup')) {
		if ($email_admin_on_signup == 'y') {
			define('EMAIL_ADMIN_ON_SIGNUP', true);
		} else {
			define('EMAIL_ADMIN_ON_SIGNUP', false);
		}
	} else {
		define('EMAIL_ADMIN_ON_SIGNUP', false);
	}

	if ($email_admin_on_start_thread = get_option('email_admin_on_start_thread')) {
		if ($email_admin_on_start_thread == 'y') {
			define('EMAIL_ADMIN_ON_START_THREAD', true);
		} else {
			define('EMAIL_ADMIN_ON_START_THREAD', false);
		}
	} else {
		define('EMAIL_ADMIN_ON_START_THREAD', false);
	}

	if ($multi_lingual = get_option('multi_lingual')) {
		if ($multi_lingual == 'y') {
			define('MULTI_LANG', true);
		} else {
			define('MULTI_LANG', false);
		}
	} else {
		define('MULTI_LANG', false);
	}

	if ($primary_language = get_option('primary_language')) {
		define('PRIMARY_LANG', $primary_language);
	} else {
		define('PRIMARY_LANG', '');
	}

	if ($other_languages = get_option('other_languages')) {
		define('OTHER_LANG', $other_languages);
	} else {
		define('OTHER_LANG', '');
	}
}

function get_option($key) {
	global $db;
	$value = $db -> get_var("SELECT option_value FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key . "'");
	if ($value) {
		return $value;
	} else {
		return "";
	}
}

function set_option($key, $value = null) {
	global $db;

	if ($value == null) {
		$db -> query("DELETE FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key . "'");
		return true;
	}

	$values = array('option_value' => $value);
	if ($db -> get_row("SELECT * FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key . "' ORDER BY id DESC LIMIT 0,1")) {
		$db -> update(TABLES_PREFIX . "options", $values, array('option_key' => $key), array("%s"));
	} else {
		$values['option_key'] = $key;
		$db -> insert(TABLES_PREFIX . "options", $values, array("%s"));
	}
	return true;
}

function filterbadwords($text) {
	if ($bad_words = get_option('bad_words')) {

		$badwordsarray = explode(',', $bad_words);
		foreach ($badwordsarray as $badword) {

			$text = str_ireplace(trim($badword), str_repeat("*", strlen(trim($badword))), $text);
		}
	}
	return ($text);
}

function Clean($str) {
	if (is_array($str)) {
		$return = array();
		foreach ($str as $k => $v) {
			$return[Clean($k)] = Clean($v);
		}
		return $return;
	} else {
		$str = @trim($str);
		if (get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}
}

function CleanXSS($str) {
	if (is_array($str)) {
		$return = array();
		foreach ($str as $k => $v) {
			$return[CleanXSS($k)] = CleanXSS($v);
		}
		return $return;
	} else {
		$str = @trim($str);
		$str = preg_replace('#<script(.*?)>(.*?)</script(.*?)>#is', '', $str);
		$str = preg_replace('#<style(.*?)>(.*?)</style(.*?)>#is', '', $str);
		if (get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}
}

function NotifySubscribers($question, $answer) {

	$db = new Db;
	$strings = new StringResource('str/');

	$question_title = $db -> get_var("SELECT title FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($question));
	$answers_body = $db -> get_var("SELECT body FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($answer));

	$subject = '[' . $_SERVER['SERVER_NAME'] . ']' . SITE_NAME . ' ' . $question_title;
	$from = WEBMASTER_EMAIL;
	$to = array();
	$body = $strings -> Get('question_title') . ': ' . $question_title . '

' . $answers_body . '

' . FORUM_URL . 'thread.php?id=' . $question;

	$users = $db -> get_results("SELECT * FROM " . TABLES_PREFIX . "posts_following WHERE post_id = " . intval($question) . "");

	if ($users) {
		foreach ($users as $user) {
			if (intval($user -> user_id) != Users_CurrentUserId()) {
				$details = Users_GetUserDetails($user -> user_id);
				if ($details['email']) {
					$to[] = $details['email'];
				}
			}
		}

	}

	if ($to) {
		$headers = "From: $from";
		ini_set("sendmail_from", $from);
		foreach ($to as $t) {
			mail($t, $subject, $body, $headers);
		}

	}
}

function Slug($str) {
	$str = strtolower(trim($str));
	$str = preg_replace('/[^a-z0-9-]/', '-', $str);
	$str = preg_replace('/-+/', "-", $str);
	return $str;
}

function remove_accent($str) {
	$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
	$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
	return str_replace($a, $b, $str);
}

function UrlText($str) {
	return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), remove_accent($str)));
}

function GetPage($content_html_file = '', $title = '') {
	$db = new Db;
	$layout = new Layout('html/', 'str/');
	$layout -> SetContentView('base');

	if (defined('THEME') AND THEME != '') {
		$layout -> AddContentById('theme', THEME . '.css');
	} else {
		$layout -> AddContentById('theme', 'default.css');
	}

	$layout -> AddContentById('content', $layout -> GetContent($content_html_file));
	$layout -> AddContentById('title', $title);
	$layout -> AddContentById('current_year', date('Y'));
	$layout -> AddContentById('site_url', SITE_URL);
	if (Users_IsUserLoggedIn()) {
		$layout -> AddContentById('my_profile_link', '<p class="navbar-text pull-right">{{ST:logged_in_as}} <a href="' . Users_ChangeProfilePageUrlCurrentUser() . '">' . Users_CurrentUserUsername() . '</a> | <a href="' . Users_SignOutPageUrl() . '">{{ST:signout}}</a></p>');

		$layout -> AddContentById('sidebar', $layout -> GetContent('sidebar-signin'));

		$messages = count($db -> get_results("SELECT * FROM " . TABLES_PREFIX . "messages WHERE receiver = " . Users_CurrentUserId() . " AND seen = 'n'"));

		if ($messages > 0) {
			$layout -> AddContentById('unread_msg_count', '(' . $messages . ')');
		}

		$timecheck = date('Y-m-d H:i:s', time() - (60 * 5));
		$online = count($db -> get_results("SELECT * FROM " . TABLES_PREFIX . "users WHERE id != " . Users_CurrentUserId() . " AND last_seen > '$timecheck'"));
		$layout -> AddContentById('online_count', '(' . $online . ')');

		if (Users_IsUserAdmin(Users_CurrentUserId())) {
			$layout -> AddContentById('admin_tab', '<li><a href="#admin_tab" data-toggle="tab">{{ST:admin}}</a></li>');
			$layout -> AddContentById('admin_tab_content', $layout -> GetContent('sidebar-admin'));
		} elseif (Users_IsUserModerator(Users_CurrentUserId())) {
			$layout -> AddContentById('admin_tab', '<li><a href="#admin_tab" data-toggle="tab">{{ST:admin}}</a></li>');
			$layout -> AddContentById('admin_tab_content', $layout -> GetContent('sidebar-moderator'));
		} else {
			$layout -> AddContentById('admin-sidebar', '');
		}
	} else {
		$layout -> AddContentById('sidebar', $layout -> GetContent('sidebar-signout'));

		$layout -> AddContentById('signin_url', Users_SignInPageUrl());
		$layout -> AddContentById('signup_url', Users_RegistrationPageUrl());
		$layout -> AddContentById('forgot_url', Users_ForgotPasswordPageUrl());
		if (!FACEBOOK_APP_ID OR !FACEBOOK_APP_SECRET) {

			$layout -> AddContentById('facebook_hide', ' style="display: none;"');
		} else {
			$layout -> AddContentById('facebook_script', $layout -> GetContent('facebook_script'));
		}
	}

	$categories = $db -> get_results("SELECT * FROM " . TABLES_PREFIX . "categories WHERE parent = 0 ORDER BY order_by ASC, name ASC");

	$rows_html = '';
	$count = 1;
	if ($categories) {
		foreach ($categories as $category) {
			if (defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true) {
				$rows_html .= '<li><a href="{{ID:base_url}}forum/' . UrlText($category -> name) . '/' . $category -> id . '/"><i class="fa fa-file-text-o"></i> ' . $category -> name . '</a></li>';
			} else {
				$rows_html .= '<li><a href="{{ID:base_url}}index.php?id=' . $category -> id . '"><i class="fa fa-file-text-o"></i> '.  $category -> name . '</a></li>';
			}
			$count++;
		}
	}
	$layout -> AddContentById('sidebar_forums', $rows_html);

	global $lang;
	if (defined('MULTI_LANG') AND MULTI_LANG == true) {
		$lang_form = new Layout('html/', 'str/');
		$lang_form -> SetContentView('language-form');
		$languages = array();
		if (defined('OTHER_LANG') AND OTHER_LANG != '') {
			$languages = explode(',', OTHER_LANG);
		}

		if (defined('PRIMARY_LANG') AND PRIMARY_LANG != '') {
			array_unshift($languages, PRIMARY_LANG);
		}
		$lang_form_options = '';
		foreach ($languages as $l) {
			$l = strtolower($l);
			if ($l == $lang) {
				$lang_form_options .= '<option value="' . $l . '" selected>{{ST:' . $l . '}}</option>';
			} else {
				$lang_form_options .= '<option value="' . $l . '">{{ST:' . $l . '}}</option>';
			}
		}

		$lang_form -> AddContentById('rows', $lang_form_options);
		$layout -> AddContentById('language_form', $lang_form -> ReturnView());
	}

	$layout -> AddContentById('base_url', FORUM_URL);
	$layout -> AddContentById('current_url', CurrentUrl());
	return $layout;
}

function Paginate($url, $page, $total_pages, $already_has_query_str = false, $adjacents = 3) {

	$prevlabel = "&larr;";
	$nextlabel = "&rarr;";

	if ($already_has_query_str == true) {
		$start_with = '&';
	} else {
		$start_with = '?';
	}
	
	$out = '<ul class="unstyled inbox-pagination" style="list-style-type: none;">';

	// previous
	if ($page == 1) {
		//$out .= '<li class="disabled"><a href="#">&larr;</a></li>';
	} else {
		$out .= '<li><a class="np-btn" href="' . $url . $start_with . 'page=' . ($page - 1) . '"><i class="fa fa-angle-left  pagination-left"></i></a></li>';
	}

	// first
	if ($page > ($adjacents + 1)) {
		$out .= '<li><a class="np-btn" href="' . $url . $start_with . 'page=' . 1 . '">1</a></li>';
	}

	// interval
	if ($page > ($adjacents + 2)) {
		//$out .= '<li class="disabled"><a href="#">...</a></li>';
		$out .= '<li><a class="np-btn" href="#">...</a></li>';
	}

	// pages
	$pmin = ($page > $adjacents) ? ($page - $adjacents) : 1;
	$pmax = ($page < ($total_pages - $adjacents)) ? ($page + $adjacents) : $total_pages;
	for ($i = $pmin; $i <= $pmax; $i++) {
		if ($i == $page) {
			//$out .= '<li class="disabled"><a href="#">' . $i . '</a></li>';
			$out .= '<li><a class="np-btn" href="#">' . $i . '</a></li>';
		} else {
			//$out .= '<li><a href="' . $url . $start_with . 'page=' . $i . '">' . $i . '</a></li>';
			$out .= '<li><a class="np-btn" href="' . $url . $start_with . 'page=' . $i . '">' . $i . '</a></li>';
		}
	}

	// interval
	if ($page < ($total_pages - $adjacents - 1)) {
		//$out .= '<li class="disabled"><a href="#">...</a></li>';
		$out .= '<li><a class="np-btn" href="#">...</a></li>';
	}

	// last
	if ($page < ($total_pages - $adjacents)) {
		//$out .= '<li><a href="' . $url . $start_with . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
		$out .= '<li><a class="np-btn" href="' . $url . $start_with . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
	}

	// next
	if ($page < $total_pages) {
		$out .= '<li><a class="np-btn" href="' . $url . $start_with . 'page=' . ($page + 1) . '"><i class="fa fa-angle-right pagination-right"></i></a></li>';
	} else {
		//$out .= '<li class="disabled"><a href="#">&rarr;</a></li>';
	}

	$out .= '</ul>';

	return $out;
}

function ValidateEmail($email) {
	if (preg_match("/[\\000-\\037]/", $email)) {
		return false;
	}
	$pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
	if (!preg_match($pattern, $email)) {
		return false;
	}
	return true;
}

function encode_password($password) {

	return md5($password);
}

function GeneratePassword($len = 8) {
	if (!$len)
		$len = 8;
	$pool = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$str = '';
	for ($i = 0; $i < $len; $i++) {
		$str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
	}
	return $str;
}

function NiceNumber($n) {
	$n = (0 + str_replace(",", "", $n));
	if (!is_numeric($n))
		return false;
	if ($n > 1000000000000)
		return round(($n / 1000000000000), 1) . 'Tri';
	else if ($n > 1000000000)
		return round(($n / 1000000000), 1) . ' Bil';
	else if ($n > 1000000)
		return round(($n / 1000000), 1) . 'Mil';
	else if ($n > 1000)
		return round(($n / 1000), 1) . 'K';
	return number_format($n);
}

function plural($num) {
	if ($num != 1)
		return "s";
}

function getRelativeTime($date) {
	$diff = time() - strtotime($date);
	if ($diff < 60)
		return $diff . " second" . plural($diff) . " ago";
	$diff = round($diff / 60);
	if ($diff < 60)
		return $diff . " minute" . plural($diff) . " ago";
	$diff = round($diff / 60);
	if ($diff < 24)
		return $diff . " hour" . plural($diff) . " ago";
	$diff = round($diff / 24);
	if ($diff < 7)
		return $diff . " day" . plural($diff) . " ago";
	$diff = round($diff / 7);
	if ($diff < 4)
		return $diff . " week" . plural($diff) . " ago";
	return "on " . date("F j, Y", strtotime($date));
}

function TrimText($input, $length) {
	$input = strip_tags($input);
	if (strlen($input) <= $length) {
		return $input;
	}
	$last_space = strrpos(substr($input, 0, $length), ' ');
	$trimmed_text = substr($input, 0, $last_space);

	$trimmed_text .= '&hellip;';

	return $trimmed_text;
}

function Leave($url) {
	header("Location: $url");
	exit ;
}

if (!function_exists('upload_helper')) {
	function upload_helper($field, $multiple = false) {
		$upload = new Upload;

		$return = array();
		$return["status"] = 0;
		$return["names"] = array();
		$return["error"] = "";
		if ($multiple) {
			$files = $_FILES;
			$cpt = count($_FILES[$field]['name']);
			for ($i = 0; $i < $cpt; $i++) {

				$_FILES[$field]['name'] = $files[$field]['name'][$i];
				$_FILES[$field]['type'] = $files[$field]['type'][$i];
				$_FILES[$field]['tmp_name'] = $files[$field]['tmp_name'][$i];
				$_FILES[$field]['error'] = $files[$field]['error'][$i];
				$_FILES[$field]['size'] = $files[$field]['size'][$i];

				$upload -> initialize(set_upload_options(random_file_name($_FILES[$field]['name'])));
				if ($upload -> do_upload($field)) {
					$upload_data = $upload -> data();
					$return["status"] = 1;
					$return["names"][] = $upload_data["file_name"];
				} else {
					$return["status"] = 0;
					$return["error"] .= $upload -> display_errors() . " ";
				}

			}
		} else {
			$upload -> initialize(set_upload_options(random_file_name($_FILES[$field]['name'])));
			if ($upload -> do_upload($field)) {
				$upload_data = $upload -> data();
				$return["status"] = 1;
				$return["names"][] = $upload_data["file_name"];
			} else {
				$return["status"] = 0;
				$return["error"] = $upload -> display_errors();
			}
		}

		return $return;
	}

}

if (!function_exists('set_upload_options')) {
	function random_file_name($name) {
		return "FILE-" . date("Ymd") . "-" . generateRandomNumber(12) . "." . mi_get_extension($name);
	}

}

if (!function_exists('set_upload_options')) {
	function set_upload_options($new_name = "") {
		$config = array();
		$config['upload_path'] = 'uploads/';
		$config['allowed_types'] = ALLOWED_FILE_TYPES;
		if ($new_name) {
			$config['file_name'] = $new_name;
		}
		return $config;
	}

}

if (!function_exists('mi_get_extension')) {
	function mi_get_extension($filename) {
		$x = explode('.', $filename);
		return end($x);
	}

}

if (!function_exists('generateRandomNumber')) {
	function generateRandomNumber($len) {
		$al = '123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
		$date = date("Hs");
		$password = "$date";
		for ($index = 1; $index <= $len; $index++) {
			$randomNumber = rand(1, strlen($al));
			$password .= substr($al, $randomNumber - 1, 1);
		}
		return $password;
	}

}

function is_image_file($name) {
	$img_extensions = 'gif|png|jpg|jpeg|jpe';
	return valid_file_extension($name, $img_extensions);
}

function valid_file_extension($name, $allowed_extensions) {
	$allowed_extensions = explode('|', $allowed_extensions);
	$extension = strtolower(get_extension($name));
	if (in_array($extension, $allowed_extensions, TRUE)) {
		return true;
	} else {
		return false;
	}
	return true;
}

function get_extension($filename) {
	$x = explode('.', $filename);
	return end($x);
}

function clean_file_name($filename) {
	$invalid = array("<!--", "-->", "'", "<", ">", '"', '&', '$', '=', ';', '?', '/', "%20", "%22", "%3c", "%253c", "%3e", "%0e", "%28", "%29", "%2528", "%26", "%24", "%3f", "%3b", "%3d");
	$filename = str_replace($invalid, '', $filename);
	$filename = preg_replace("/\s+/", "_", $filename);
	return stripslashes($filename);
}

function set_filename($path, $filename) {
	$file_ext = get_extension($filename);
	if (!file_exists($path . $filename)) {
		return $filename;
	}
	$new_filename = str_replace('.' . $file_ext, '', $filename);
	for ($i = 1; $i < 300; $i++) {
		if (!file_exists($path . $new_filename . '_' . $i . '.' . $file_ext)) {
			$new_filename .= '_' . $i . '.' . $file_ext;
			break;
		}
	}
	return $new_filename;
}

function UploadError($code) {
	$response = '';
	switch ($code) {
		case UPLOAD_ERR_INI_SIZE :
			$response = '{{ST:UPLOAD_ERR_INI_SIZE}}';
			break;
		case UPLOAD_ERR_FORM_SIZE :
			$response = '{{ST:UPLOAD_ERR_FORM_SIZE}}';
			break;
		case UPLOAD_ERR_PARTIAL :
			$response = '{{ST:UPLOAD_ERR_PARTIAL}}';
			break;
		case UPLOAD_ERR_NO_FILE :
			$response = '{{ST:UPLOAD_ERR_NO_FILE}}';
			break;
		case UPLOAD_ERR_NO_TMP_DIR :
			$response = '{{ST:UPLOAD_ERR_NO_TMP_DIR}}';
			break;
		case UPLOAD_ERR_CANT_WRITE :
			$response = '{{ST:UPLOAD_ERR_CANT_WRITE}}';
			break;
		case UPLOAD_ERR_EXTENSION :
			$response = '{{ST:UPLOAD_ERR_EXTENSION}}';
			break;
		default :
			$response = '{{ST:Unknown_error_file_error}}';
			break;
	}

	return $response;
}

function CreateSearchQuery($where, $columns) {
	$terms = SearchSplitTerms($where);
	$terms_db = SearchDbEscapeTerms($terms);

	$sql_query = array();
	foreach ($terms_db as $key => $value) {
		$column_list = $columns;
		$keywords = $value;
		$sql = array();
		for ($i = 0; $i < count($column_list); $i++) {
			$sql[] = '' . $column_list[$i] . ' RLIKE "' . $keywords . '"';
		}
		$sql_query = array_merge($sql_query, $sql);

	}
	return $sql_query = implode(' OR ', $sql_query);
}

function SearchSplitTerms($terms) {

	$terms = preg_replace("/\"(.*?)\"/e", "SearchTransformTerm('\$1')", $terms);
	$terms = preg_split("/\s+|,/", $terms);

	$out = array();

	foreach ($terms as $term) {

		$term = preg_replace("/\{WHITESPACE-([0-9]+)\}/e", "chr(\$1)", $term);
		$term = preg_replace("/\{COMMA\}/", ",", $term);

		$out[] = $term;
	}

	return $out;
}

function SearchTransformTerm($term) {
	$term = preg_replace("/(\s)/e", "'{WHITESPACE-'.ord('\$1').'}'", $term);
	$term = preg_replace("/,/", "{COMMA}", $term);
	return $term;
}

function SearchEscapeRlike($string) {
	return preg_replace("/([.\[\]*^\$])/", '\\\$1', $string);
}

function SearchDbEscapeTerms($terms) {
	$out = array();
	foreach ($terms as $term) {
		$out[] = '[[:<:]]' . AddSlashes(SearchEscapeRlike($term)) . '[[:>:]]';
	}
	return $out;
}

function CurrentUrl() {
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$protocol = "https";
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
	return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}

function get_nested_categories($offset = null, $rows = null) {
	global $db;
	$categories = $db -> get_results("SELECT * FROM " . TABLES_PREFIX . "categories ORDER BY order_by ASC, name ASC");

	$new_return = array();
	$i = 0;
	while ($i < count($categories)) {
		if (isset($categories[$i] -> parent) AND intval($categories[$i] -> parent) == 0) {
			$new_return[] = $categories[$i];
			$children = parent_children($categories, $categories[$i] -> id, "&hellip;&nbsp;");
			$results = $children['new'];

			if (count($children['children']) > 0) {
				$new_return = array_merge($new_return, $children['children']);

			}
		}
		$i = $i + 1;
	}

	if ($offset != null AND $rows != null) {
		$new_return = array_slice($new_return, $offset, $rows);
	}

	return $new_return;
}

function parent_children($categories, $parent, $level = '') {
	$new_return = array();
	$i = 0;
	while ($i < count($categories)) {

		if (isset($categories[$i] -> parent) AND $categories[$i] -> parent == $parent) {
			$categories[$i] -> name = $level . " " . $categories[$i] -> name;
			$new_return[] = $categories[$i];
			$children = parent_children($categories, $categories[$i] -> id, $level . "&hellip;&nbsp;");
			$categories = $children['new'];

			if (count($children['children']) > 0) {
				$new_return = array_merge($new_return, $children['children']);
			}

		}
		$i = $i + 1;
	}

	return array('children' => $new_return, 'new' => $categories);
}

function get_category_children_id($parent) {
	global $db;
	$categories = $db -> get_results("SELECT * FROM " . TABLES_PREFIX . "categories ORDER BY order_by ASC, name ASC");

	$new_return = array();
	$i = 0;
	while ($i < count($categories)) {
		if (isset($categories[$i] -> parent) AND intval($categories[$i] -> parent) == $parent) {
			$new_return[] = $categories[$i];
			$children = parent_children_ids($categories, $categories[$i] -> id);
			$results = $children['new'];

			if (count($children['children']) > 0) {
				$new_return = array_merge($new_return, $children['children']);

			}
		}
		$i = $i + 1;
	}

	$return = array();
	foreach ($new_return as $n) {
		$return[] = $n -> id;
	}

	return $return;
}

function parent_children_ids($categories, $parent) {
	$new_return = array();
	$i = 0;
	while ($i < count($categories)) {

		if (isset($categories[$i] -> parent) AND $categories[$i] -> parent == $parent) {
			$new_return[] = $categories[$i];
			$children = parent_children($categories, $categories[$i] -> id);
			$categories = $children['new'];

			if (count($children['children']) > 0) {
				$new_return = array_merge($new_return, $children['children']);

			}

		}
		$i = $i + 1;
	}

	return array('children' => $new_return, 'new' => $categories);
}

function get_nested_categories_post($offset = null, $rows = null) {
	global $db;
	$categories = $db -> get_results("SELECT * FROM " . TABLES_PREFIX . "categories ORDER BY order_by ASC, name ASC");

	$new_return = array();
	$i = 0;
	while ($i < count($categories)) {
		if (isset($categories[$i] -> parent) AND intval($categories[$i] -> parent) == 0) {

			if (Users_IsUserAdminOrModerator(Users_CurrentUserId()) OR $categories[$i] -> locked != 'y') {

				$new_return[] = $categories[$i];
				$children = parent_children_post($categories, $categories[$i] -> id, "---");
				$results = $children['new'];

				if (count($children['children']) > 0) {
					$new_return = array_merge($new_return, $children['children']);

				}

			}
		}
		$i = $i + 1;
	}

	if ($offset != null AND $rows != null) {
		$new_return = array_slice($new_return, $offset, $rows);
	}

	return $new_return;
}

function parent_children_post($categories, $parent, $level = '') {
	$new_return = array();
	$i = 0;
	while ($i < count($categories)) {

		if (isset($categories[$i] -> parent) AND $categories[$i] -> parent == $parent) {
			if (Users_IsUserAdminOrModerator(Users_CurrentUserId()) OR $categories[$i] -> locked != 'y') {
				$categories[$i] -> name = $level . " " . $categories[$i] -> name;
				$new_return[] = $categories[$i];
				$children = parent_children_post($categories, $categories[$i] -> id, $level . " ---");
				$categories = $children['new'];

				if (count($children['children']) > 0) {
					$new_return = array_merge($new_return, $children['children']);
				}
			}
		}
		$i = $i + 1;
	}

	return array('children' => $new_return, 'new' => $categories);
}
