<?php
$c = strtolower($_COOKIE['kustyle']);
if ($c && in_array($c, explode(',', $_GET['allowed']))) {
	$is_custom = ($c == $_GET['custom']);
	$style = $_COOKIE['kustyle'];
}
elseif ($_GET['custom']) {
	$style = $_GET['custom'];
	$is_custom = true;
}
else {
	$style = $_GET['default'];
	$is_custom = false;
}

$url = '/css/'.
	($is_custom ? 'custom/' : '').
	strtolower($style).'.css?v='.
	($is_custom ? $_GET['cv'] : $_GET['v']);
header('Location: ' . $url, true, 303);
die();
?>