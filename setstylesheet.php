<?php
require 'config.php';

if ($_GET['style']) {
	setcookie('kustyle', $_GET['style'], time() + 31556926, '/'/*, KU_DOMAIN*/);
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
?>