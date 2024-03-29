<?php
function changeLocale($newlocale) {
	global $CURRENTLOCALE, $EMULATEGETTEXT, $text_domains;
	$CURRENTLOCALE = $newlocale;
	$EMULATEGETTEXT = 1;
	_textdomain('kusaba');
	_setlocale(LC_ALL, $newlocale);
	_bindtextdomain('kusaba', KU_ROOTDIR . 'inc/lang', $newlocale);
	_bind_textdomain_codeset('kusaba', KU_CHARSET);

}

function array_in_array($some, $all) {
	if(count(array_intersect($some, $all)) == count($some)) return true;
	else return false;
}

function exitWithErrorPage($errormsg, $extended = '', $error_type=null, $error_data=null) {
	global $smarty, $board_class;

  if (@$_POST['AJAX']) {
    $resp = array(
      'error' => $errormsg,
      'error_verbose' => $extended
    );
    if ($error_type) {
      $resp['error_type'] = $error_type;
      if ($error_data) {
        $resp['error_data'] = $error_data;
      }
    }
    exit(json_encode($resp));
  }

  else {
    if (!isset($smarty)) {
      $GLOBALS['skipdb'] = true;
      require 'config.php';
      $smarty = new _Smarty();
    }
    if (!isset($board_class)) {
      require_once KU_ROOTDIR . 'inc/classes/board-post.class.php';
      $board_class = new Board('');
    }

    $smarty->assign('styles', explode(':', KU_MENUSTYLES));
    $smarty->assign('errormsg', $errormsg);

    if ($extended != '') {
      $smarty->assign('errormsgext', '<br /><div style="text-align: center;font-size: 1.25em;">' . $extended . '</div>');
    } else {
      $smarty->assign('errormsgext', $extended);
    }


    echo $smarty->fetch('error.tpl');

    die();
  }
}

function exitWithSuccessJSON($data=array()) {
  $data_type = gettype($data);
  if ($data_type == 'string')
    $data = array('message' => $data);
  elseif ($data_type != 'array')
    $data = array('data' => $data);
  $data['error'] = false;
  exit(json_encode($data));
}

/**
 * Add an entry to the modlog
 *
 * @param string $entry Entry text
 * @param integer $category Category to file under. 
  0 - No category, 
  1 - Login, 
  2 - Cleanup/rebuild boards and html files, 
 +3 - Board adding/deleting, 
 +4 - Board updates, 
 +5 - Locking/stickying, 
 +6 - Staff changes, 
 +7 - Thread deletion/post deletion, 
 +8 - Bans, 
 +9 - News, 
 +10 - Global changes, 
 +11 - Wordfilter,
 +12 - Modposting
 +13 - Misc.
 (entries marked with "+" will be displayed in a public modlog)
 * @param string $forceusername Username to force as the entry username
 */
function management_addlogentry($entry, $category = 0, $boards = array(), $user_id = '', $forceusername = '') {
	global $tc_db;

  if (is_array($boards))
    $boards = implode(',', $boards);
	$username = ($forceusername == '') ? $_SESSION['manageusername'] : $forceusername;
	if ($entry != '') {
		$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "modlog` ( `entry` , `user` , `category` , `timestamp`, `boards`, `id` ) VALUES ( " . $tc_db->qstr($entry) . " , '" . $username . "' , " . $tc_db->qstr($category) . " , '" . time() . "' , " . $tc_db->qstr($boards) . " , " . $tc_db->qstr($user_id) . " )");
	}
  // Delete old entries
  if (KU_MODLOGDAYS) {
    $tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "modlog` WHERE `timestamp` < '" . (time() - KU_MODLOGDAYS * 86400) . "'");
  }
	if (KU_RSS) {
		require_once(KU_ROOTDIR . 'inc/classes/rss.class.php');
		$rss_class = new RSS();

		print_page(KU_BOARDSDIR . 'modlogrss.xml', $rss_class->GenerateModLogRSS($entry), '');
	}
}

function sendStaffMail($subject, $message) {
	$emails = split(':', KU_APPEAL);
	$expires = ($line['until'] > 0) ? date("F j, Y, g:i a", $line['until']) : 'never';
	foreach ($emails as $email) {
		@mail($email, $subject, $message, 'From: "' . KU_NAME . '" <kusaba@noreply' . KU_DOMAIN . '>' . "\r\n" . 'Reply-To: kusaba@noreply' . KU_DOMAIN . "\r\n" . 'X-Mailer: kusaba' . KU_VERSION . '/PHP' . phpversion());
	}
}

/* Depending on the configuration, use either a meta refresh or a direct header */
function do_redirect($url, $useheader=false) {
  if ($useheader) {
    header('Location: ' . $url);
    exit();
  } else {
    die('<meta http-equiv="refresh" content="1;url=' . $url . '">');
  }
}

function ajax_error($errmsg) {
  exit(json_encode(array(
    'error' => $errmsg
  )));
}

function ajax_success($data) {
  exit(json_encode(array(
    'error' => false,
    'data' => $data
  )));
}

function check_css($css) {
  if(preg_match('/expression\s*\(/', $css))
    return _gettext("expression()'s are not allowed in stylesheets");

  $matched = array();
  preg_match_all("/((?:(?:https?:)?\\/\\/|ftp:\\/\\/|irc:\\/\\/)[^\\s<>()\"]+?(?:\\([^\\s<>()\"]*?\\)[^\\s<>()\"]*?)*)((?:\\s|<|>|\"|\\.|\\]|!|\\?|,|&\\#44;|&quot;)*(?:[\\s<>()\"]|$))/im", $css, $matched);

  $allowed_offsite_urls = explode(" ", KU_ALLOWED_OFFSITE_URLS);

  if (isset($matched[0])) {
    foreach ($matched[0] as $match) {
      $match_okay = false;
      foreach ($allowed_offsite_urls as $allowed_url) {
        if (strpos($match, $allowed_url) === 0) {
          $match_okay = true;
        }
      }
      if ($match_okay !== true) {
        return sprintf(_gettext("Off-site link %s is not allowed in stylesheets"), $match);
      }
    }
  }

  return false;
}

/*
 * For parsing ISO8601 duration codes. Use regex below:
 * '/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/'
 */
function ISO8601_callback($matches) {
  $h = @(int)$matches[1] ?? 0;
  $m = @(int)$matches[2] ?? 0;
  $s = @(int)$matches[3] ?? 0;
  return 60*(60*$h + $m) + $s;
}

// For debugging
function console_log($ret = false) {
  $args = func_get_args();
  echo call_user_func_array('get_console_log', $args);
}
function get_console_log() {
  $args = func_get_args();
  foreach ($args as &$arg) {
    $arg = json_encode($arg);
  }
  $r = '<script>console.log('.implode(',', $args).')</script>';
  return $r;
}

class TimingReporter {
  function __construct() {
    $this->time = microtime(true);
    $this->start = $this->time;
    $this->marks = array();
  }

  function mark($key, $fin=false) {
    $now = microtime(true);
    $this->marks []= array($key => $now - $this->time);
    $this->time = $now;
    if ($fin) {
      $this->marks []= array('TOTAL' => $now - $this->start);
    }
  }
}

function RemoveFiles($path) {
  $files = glob($path);
  foreach($files as $file){
    if(is_file($file)) {
      unlink($file);
    }
  }
}

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
