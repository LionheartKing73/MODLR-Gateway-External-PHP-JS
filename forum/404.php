<?php

require 'includes.php';

$layout = GetPage('404', '{{ST:error_404}}');
$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:error_404}}</li>');
$layout->RenderViewAndExit();
