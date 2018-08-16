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
	global $dwoo, $dwoo_data, $board_class;

  if ($_POST['AJAX']) {
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
    if (!isset($dwoo)) {
      require_once KU_ROOTDIR . 'lib/dwoo.php';
    }
    if (!isset($board_class)) {
      require_once KU_ROOTDIR . 'inc/classes/board-post.class.php';
      $board_class = new Board('');
    }

    $dwoo_data->assign('styles', explode(':', KU_MENUSTYLES));
    $dwoo_data->assign('errormsg', $errormsg);

    if ($extended != '') {
      $dwoo_data->assign('errormsgext', '<br /><div style="text-align: center;font-size: 1.25em;">' . $extended . '</div>');
    } else {
      $dwoo_data->assign('errormsgext', $extended);
    }


    echo $dwoo->get(KU_TEMPLATEDIR . '/error.tpl', $dwoo_data);

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
 * @param integer $category Category to file under. 0 - No category, 1 - Login, 2 - Cleanup/rebuild boards and html files, 3 - Board adding/deleting, 4 - Board updates, 5 - Locking/stickying, 6 - Staff changes, 7 - Thread deletion/post deletion, 8 - Bans, 9 - News, 10 - Global changes, 11 - Wordfilter
 * @param string $forceusername Username to force as the entry username
 */
function management_addlogentry($entry, $category = 0, $forceusername = '') {
	global $tc_db;

	$username = ($forceusername == '') ? $_SESSION['manageusername'] : $forceusername;

	if ($entry != '') {
		$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "modlog` ( `entry` , `user` , `category` , `timestamp` ) VALUES ( " . $tc_db->qstr($entry) . " , '" . $username . "' , " . $tc_db->qstr($category) . " , '" . time() . "' )");
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
  return 60*(60*($matches[1]) + $matches[2]) + $matches[3];
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