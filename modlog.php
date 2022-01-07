<?php 
require 'config.php';

if (!I0_ENABLE_PUBLIC_MODLOG)
	exit(_gettext('ModLog is disabled.'));

require_once KU_ROOTDIR . 'lib/dwoo.php';
$dwoo = new Dwoo();
$dwoo_data = new Dwoo_Data();

// Determine the User ID
if ((I0_IPLESS_MODE==true || (I0_IPLESS_MODE=='auto' && $_SERVER['REMOTE_ADDR']=='127.0.0.1'))) {
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

$dwoo_data->assign('modlog_entries', $entries);
$dwoo_data->assign('title', _gettext('ModLog'));
$dwoo_data->assign('locale', KU_LOCALE);
$dwoo_data->assign('standalone', true);
$dwoo_data->assign('cwebpath', KU_WEBPATH);
$dwoo_data->assign('my_id', compress_md5(md5($user_id . KU_RANDOMSEED)));

$global_header   = $dwoo->get(KU_TEMPLATEDIR . '/global_board_header.tpl', $dwoo_data);
$modlog_contents = $dwoo->get(KU_TEMPLATEDIR . '/modlog.tpl', $dwoo_data);
echo $global_header . $modlog_contents;

// https://stackoverflow.com/a/3314322/1561204
function compress_md5($md5_hash_str) {
  // (we start with 32-char $md5_hash_str eg "a7d2cd9e0e09bebb6a520af48205ced1")
  $md5_bin_str = "";
  foreach (str_split($md5_hash_str, 2) as $byte_str) { // ("a7", "d2", ...)
      $md5_bin_str .= chr(hexdec($byte_str));
  }
  // ($md5_bin_str is now a 16-byte string equivalent to $md5_hash_str)
  $md5_b64_str = base64_encode($md5_bin_str);
  // (now it's a 24-char string version of $md5_hash_str eg "VUDNng4JvrtqUgr0QwXOIg==")
  $md5_b64_str = substr($md5_b64_str, 0, 22);
  // (but we know the last two chars will be ==, so drop them eg "VUDNng4JvrtqUgr0QwXOIg")
  $url_safe_str = str_replace(array("+", "/"), array("-", "_"), $md5_b64_str);
  // (Base64 includes two non-URL safe chars, so we replace them with safe ones)
  return $url_safe_str;
}
