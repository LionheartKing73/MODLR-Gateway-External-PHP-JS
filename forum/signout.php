<?php

require 'includes.php';


unset($_SESSION[TABLES_PREFIX.'sforum_logged_in']);
unset($_SESSION[TABLES_PREFIX.'sforum_user_id']);
unset($_SESSION[TABLES_PREFIX.'sforum_user_role']);
unset($_SESSION[TABLES_PREFIX.'sforum_user_username']);

setcookie(TABLES_PREFIX . COOKIE_NAME, "", time() - 3600);

header('Location: '.FORUM_URL);
exit;
