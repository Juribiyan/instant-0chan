<?php
$GLOBALS['skipdb'] = true;
require 'config.php';

if ($_GET['style']) {
	setcookie('kustyle', $_GET['style'], time() + 31556926, '/');
	if (isset($_GET['site']))
		setcookie('kustyle_site', $_GET['style'], time() + 31556926, '/');
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
?>