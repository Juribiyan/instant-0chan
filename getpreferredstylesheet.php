<?php
$_GLOBALS['skipdb'] = true;
require 'config.php';

$style = $_COOKIE['kustyle'];
if (!$style)
	$style = KU_DEFAULTSTYLE;

redirect(KU_WEBPATH.KU_BOARDSFOLDER.'css/'.strtolower($style).'.css?v='.KU_CSSVER);

function redirect($url, $statusCode = 303) {
	header('Location: ' . $url, true, $statusCode);
	die();
}
?>