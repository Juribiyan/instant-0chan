<?php 
require 'config.php';
require 'inc/func/misc.php';

if (!I0_ENABLE_PUBLIC_MODLOG)
	exit(_gettext('ModLog is disabled.'));

$smarty = new _Smarty();

// Determine the User ID
if ((I0_IPLESS_MODE=='true' || (I0_IPLESS_MODE=='auto' && $_SERVER['REMOTE_ADDR']=='127.0.0.1'))) {
	if (isset($_COOKIE['I0_persistent_id'])) {
		$user_id = $_COOKIE['I0_persistent_id'];
	}
	else {
		$user_id = session_id();
	}
	$user_id = substr($user_id, 0, 45);
}
else {
	$user_id = $_SERVER['REMOTE_ADDR'];
}

$entries = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "modlog` WHERE `category`>=3 ORDER BY `timestamp` DESC");
foreach ($entries as &$entry) {
  if ($entry['id'] != '')
    $entry['id'] = compress_md5(md5($entry['id'] . KU_RANDOMSEED));
}

$smarty->assign('modlog_entries', $entries);
$smarty->assign('title', _gettext('ModLog'));
$smarty->assign('locale', KU_LOCALE);
$smarty->assign('standalone', true);
$smarty->assign('cwebpath', KU_WEBPATH);
$smarty->assign('my_id', compress_md5(md5($user_id . KU_RANDOMSEED)));

$global_header   = $smarty->fetch('global_board_header.tpl');
$modlog_contents = $smarty->fetch('modlog.tpl');
echo $global_header . $modlog_contents;

