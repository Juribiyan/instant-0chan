<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * +------------------------------------------------------------------------------+
 * kusaba - http://www.kusaba.org/
 * Written by Trevor "tj9991" Slocum
 * http://www.tj9991.com/
 * tslocum@gmail.com
 * +------------------------------------------------------------------------------+
 */
/**
 * Board operations which available to all users
 *
 * This file serves the purpose of providing functionality for all users of the
 * boards. This includes: posting, reporting posts, and deleting posts.
 *
 * @package kusaba
 */

// Fake email field check
if (isset($_POST['email']) && !empty($_POST['email'])) {
	exitWithErrorPage('Spam bot detected');
}
if (isset($_POST['sagebtn']) && $_POST['sagebtn'] == 1)
	$_POST['em'] = 'sage';	// dirty deeds done dirt cheap

// Start the session
session_start();

// Require the configuration file, functions file, board and post class, bans class, and posting class
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';
require KU_ROOTDIR . 'inc/classes/bans.class.php';
require KU_ROOTDIR . 'inc/classes/posting.class.php';
require KU_ROOTDIR . 'inc/classes/parse.class.php';
require KU_ROOTDIR . 'inc/classes/cloud20.class.php';

$bans_class = new Bans();
$parse_class = new Parse();

// $timer = new TimingReporter();

function notify($room, $data=array()) {
	if (!KU_LIVEUPD_ENA) return;
	if (!KU_LIVEUPD_SITENAME || !KU_LOCAL_LIVEUPD_API) {
		error_log('Error during Notify: please fill the required fields in Config.php');
		return;
	}

	$data_c = array(
		'srvtoken' => KU_LIVEUPD_SRVTOKEN, 
		'room' => $room,
		'token' => $_POST['token'], 
		'timestamp' => time()
	);
	$data_string = json_encode(array_merge($data_c, $data));

	$ch = curl_init(KU_LOCAL_LIVEUPD_API.'/qr/'.KU_LIVEUPD_SITENAME);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_PROXY, "");
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, KU_LIVEUPD_DEBUG_MODE ? 500 : 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
	);
	curl_exec($ch);
	if(KU_LIVEUPD_DEBUG_MODE && curl_errno($ch)) 
		error_log('Curl error during Notify: ' . curl_error($ch).' (Error code: '.curl_errno($ch).')');
}

class PolymorphicReporter {
	function __construct($itemtype, $id, $is_ajax) {
		$this->itemtype = $itemtype;
		$this->id = $id;
		$this->is_ajax = $is_ajax;
	}

	function fail($msg="", $special_error=false) {
		$this->special_error = $special_error;
		if ($this->is_ajax) {
			$this->success = false;
			$this->message = $msg;
		}
		else
			echo $this->itemtype . ' #' . $this->id . ': ' . $msg . '<br />';
	}

	function succ($msg="") {
		if ($this->is_ajax) {
			$this->success = true;
			$this->message = $msg;
		}
		else
			echo $this->itemtype . ' #' . $this->id . ': ' . $msg . '<br />';
	}

	function report() {
		return array(
			'id' => $this->id,
			'itemtype' => $this->itemtype,
			'action' => $this->action,
			'success' => $this->success,
			'message' => $this->message,
			'special_error' => isset($this->special_error) ? $this->special_error : null
		);
	}
}

function error_redirect($url, $message) {
	if ($_POST['AJAX']) {
		exit(json_encode(array(
			'error' => $message
		)));
	}
	else {
		do_redirect($url);
	}
}

modules_load_all();

// Delete posts whose time is up
collect_dead();

// In some cases, the board value is sent through post, others get
if (isset($_POST['board']) || isset($_GET['board'])) $_POST['board'] = (isset($_GET['board'])) ? $_GET['board'] : $_POST['board'];

// If the script was called using a board name:
if (isset($_POST['board'])) {
	if ($_POST['board'] == I0_OVERBOARD_DIR)
		$is_overboard = true;
	else {
		$board_name = $tc_db->GetOne("SELECT `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
		if ($board_name !== false) {
			$board_class = new Board($board_name);
			if (!empty($board_class->board['locale'])) {
				changeLocale($board_class->board['locale']);
			}
		} else {
			error_redirect(KU_WEBPATH, _gettext('No board provided'));
		}
	}
}

// Must be declared after board class
$posting_class = new Posting();

// Expired ban removal, and then existing ban check on the current user
if (isset($board_class)) {
	checkBan();
}

function checkBan() {
	global $bans_class, $posting_class, $board_class;

	$ban_result = $bans_class->BanCheck($posting_class->user_id, $board_class->board['name']);
	if ($ban_result && is_array($ban_result) && $_POST['AJAX']) {
		exit(json_encode(array(
			'error' => _gettext('YOU ARE BANNED'),
			'error_type' => 'ban'
		)));
	}
}

/* Ensure that UTF-8 is used on some of the post variables */
$posting_class->UTF8Strings();

// $timer->mark('01_start_post');

if (isset($_POST['makepost'])) { // A more evident way to identify post action, as actual validity will be checked later anyway
	if (!isset($board_class))
		error_redirect($is_overboard ? KU_BOARDSPATH . '/' . I0_OVERBOARD_DIR . '/' : KU_WEBPATH, _gettext('No board provided'));
	$tc_db->Execute("START TRANSACTION");
	$posting_class->CheckReplyTime();
	$post_isreply = $posting_class->CheckIsReply();
	if(! $post_isreply) $posting_class->CheckNewThreadTime();

	require_once KU_ROOTDIR . 'inc/classes/upload.class.php';
	$upload_class = new Upload($post_isreply);
	$upload_class->UnifyAttachments();
	$posting_class->CheckValidPost($post_isreply);
	$posting_class->CheckMessageLength();
	$posting_class->CheckCaptcha();
	if ($posting_class->CheckBannedHash()) {
		$tc_db->Execute("COMMIT");
		checkBan();
	}

	list($thread_replies, $thread_locked, $thread_replyto) = $post_isreply
		? $posting_class->GetThreadInfo($_POST['replythread'])
		: array(0,0,0);
	list($post_name, $post_email, $post_subject, $post_del_timestamp) = $posting_class->GetFields();
	$post_password = isset($_POST['postpassword']) ? $_POST['postpassword'] : '';

	// $posting_class->CheckNotDuplicateSubject($post_subject);

	list($user_authority, $flags) = $posting_class->GetUserAuthority();

	$post_autosticky = false;
	$post_autolock = false;
	$post_displaystaffstatus = false;

	$results = $tc_db->GetAll("SELECT id
		FROM `" . KU_DBPREFIX . "posts`
		WHERE `boardid` = " . $board_class->board['id'] . "
		ORDER BY id DESC
		LIMIT 1");
	if (count($results) > 0)
		$nextid = $results[0]['id'] + 1;
	else
		$nextid = 1;
	$parse_class->id = $nextid;
	$ua = ($board_class->board['useragent']) ? htmlspecialchars($_SERVER['HTTP_USER_AGENT']) : false;
	$dice = ($board_class->board['dice']) ? true : false;
	// If they are just a normal user, or vip...
	if ($user_authority <= 0) {
		// If the thread is locked
		if ($thread_locked == 1) {
			// Don't let the user post
			exitWithErrorPage(_gettext('Sorry, this thread is locked and can not be replied to.'));
		}

		$post_message = $parse_class->ParsePost($_POST['message'], $board_class->board['name'], $thread_replyto, $board_class->board['id'], $ua, $dice, $posting_class->user_id_md5);
	// Or, if they are a moderator/administrator...
	} 
	else {
		// $timer->mark('02_authority_check');
		// If they checked the D checkbox, set the variable to tell the script to display their staff status (Admin/Mod) on the post during insertion
		if (isset($_POST['displaystaffstatus'])) {
			$post_displaystaffstatus = true;
		}

		// If they checked the RH checkbox, set the variable to tell the script to insert the post as-is... (admin only)
		if (isset($_POST['rawhtml']) && $user_authority==1) {
			$post_message = $_POST['message'];
		// Otherwise, parse it as usual...
		} 
		else {
			$post_message = $parse_class->ParsePost($_POST['message'], $board_class->board['name'], $thread_replyto, $board_class->board['id'], $ua, $dice, $posting_class->user_id_md5);
			// (Moved) check against blacklist and detect flood
		}
		// $timer->mark('03_parsed');

		// If they checked the L checkbox, set the variable to tell the script to lock the post after insertion
		if (isset($_POST['lockonpost'])) {
			$post_autolock = true;
		}

		// If they checked the S checkbox, set the variable to tell the script to sticky the post after insertion
		if (isset($_POST['stickyonpost'])) {
			$post_autosticky = true;
		}
		if (isset($_POST['usestaffname'])) {
			$_POST['name'] = md5_decrypt($_POST['modpassword'], KU_RANDOMSEED);
			$post_name = md5_decrypt($_POST['modpassword'], KU_RANDOMSEED);
		}
	}

	$posting_class->postParseCheckText($post_message, $board_class->board['name'], $board_class->board['id']);

	$posting_class->CheckBadUnicode($post_name, $post_email, $post_subject, $post_message);

	if ($board_class->board['locked'] == 0 || ($user_authority > 0 && $user_authority != 3)) {
		if ($post_isreply) {
			$upload_class->isreply = true;
		}

		$upload_class->HandleUpload();
		// $timer->mark('04_upload');

		if ($board_class->board['forcedanon'] == '1') {
			if ($user_authority == 0 || $user_authority == 3) {
				$post_name = '';
			}
		}

		$nameandtripcode = calculateNameAndTripcode($post_name);
		if (is_array($nameandtripcode)) {
			$name = $nameandtripcode[0];
			$tripcode = $nameandtripcode[1];
		} else {
			$name = $post_name;
			$tripcode = '';
		}
		$post_passwordmd5 = (I0_DELPASS_SALTING || $post_password == '') ? $post_password : '-'.md5($post_password . KU_RANDOMSEED);

		if ($post_autosticky == true) {
			if ($thread_replyto == 0) {
				$sticky = 1;
			} else {
				$result = $tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts`
					SET `stickied` = '1'
					WHERE `boardid` = " . $board_class->board['id'] . "
					AND `id` = '" . $thread_replyto . "'");
				$sticky = 0;
			}
		} else {
			$sticky = 0;
		}

		if ($post_autolock == true) {
			if ($thread_replyto == 0) {
				$lock = 1;
			} else {
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts`
					SET `locked` = '1'
					WHERE `boardid` = " . $board_class->board['id'] . "
					AND `id` = '" . $thread_replyto . "'");
				$lock = 0;
			}
		} else {
			$lock = 0;
		}

		if (!$post_displaystaffstatus && $user_authority > 0/* && $user_authority != 3*/) {
			$user_authority_display = 0;
		} elseif ($user_authority > 0) {
			$user_authority_display = $user_authority;
		} else {
			$user_authority_display = 0;
		}

		$emoji_candidates = array();

		foreach($upload_class->attachments as $attachment) {
			if ($attachment['attachmenttype'] == 'file') {
				$thumbfiletype = ($attachment['filetype_withoutdot'] == 'webm' || $attachment['filetype_withoutdot'] == 'mp4')	? '.jpg' : $attachment['file_type'];
				if (isset($attachment['emoji_candidate']) && $attachment['emoji_candidate']) {
					$emoji_candidates []= $attachment;
				}
				if (
					!file_exists(KU_BOARDSDIR . $board_class->board['name'] . '/src/' . $attachment['file_name'] . $attachment['file_type'])
					||
					(
						(
							!isset($attachment['file_is_special'])
							||
							!$attachment['file_is_special']
						)
						&&
						!(
							file_exists(KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $attachment['file_name'] . 's' . $thumbfiletype)
							&&
							file_exists(KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $attachment['file_name'] . 'c' . $thumbfiletype)
						)
					)
				) exitWithErrorPage(_gettext('Could not copy uploaded image.'));
			}
		}
		// $timer->mark('05_emojis_1');

		$post = array();
		if (KU_GEOIPMODE == 'flare') {
			$post['country'] = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? strtolower($_SERVER["HTTP_CF_IPCOUNTRY"]) : 'xx';
		} elseif (KU_GEOIPMODE == 'php-geoip') {
			if (geoip_db_avail(GEOIP_COUNTRY_EDITION)) {
				if ($country_code = @geoip_country_code_by_name($_SERVER['REMOTE_ADDR'])) {
					$post['country'] = isset($country_code) ? strtolower($country_code) : 'xx';
				}
			} else {
				$post['country'] = 'xx';
			}
		} else {
			$post['country'] = 'xx';
		}
		$post['board'] = $board_class->board['name'];
		$post['name'] = mb_substr($name, 0, KU_MAXNAMELENGTH);
		$post['name_save'] = true;
		$post['tripcode'] = $tripcode;
		$post['email'] = mb_substr($post_email, 0, KU_MAXEMAILLENGTH);
		// First array is the converted form of the japanese characters meaning sage, second meaning age
		$ords_email = unistr_to_ords($post_email);
		if (isset($_POST['em']) && strtolower($_POST['em']) != 'sage' && $ords_email != array(19979, 12370) && strtolower($_POST['em']) != 'age' && $ords_email != array(19978, 12370) && $_POST['em'] != 'return' && $_POST['em'] != 'noko') {
			$post['email_save'] = true;
		} else {
			$post['email_save'] = false;
		}
		$post['subject'] = mb_substr($post_subject, 0, KU_MAXSUBJLENGTH);
		$post['message'] = $post_message;

		$post = hook_process('posting', $post);

		// I never knew this weird shit exists in Kusaba
		if ($thread_replyto != '0') {
			if ($post['message'] == '' && KU_NOMESSAGEREPLY != '') {
				$post['message'] = KU_NOMESSAGEREPLY;
			}
		} else {
			if ($post['message'] == '' && KU_NOMESSAGETHREAD != '') {
				$post['message'] = KU_NOMESSAGETHREAD;
			}
		}

		// Emoji registration
		if (I0_USERSMILES_ENABLED && $emoji_candidates) {
			$any_new = false;
			preg_match_all('/\/reg(?:emoji|smiley?) :?([0-9a-z_]{3,20}):?/i', $post['message'], $re_match);
			foreach($re_match[1] as $i=>$emoji) {
				// Check if corresponding file is present
				if ($emoji_candidates[$i]) {
					$emoji_file = $emoji_candidates[$i];
					$emoji_name = strtolower($emoji);
					// Check if emoji exists
					$exists = false;
					foreach(array('.gif','.png') as $extension) {
						if(file_exists(KU_ROOTDIR.I0_SMILEDIR.$emoji.$extension))   {
							$exists = true;
							break;
						}
					}
					if (!$exists) {
						$src = KU_BOARDSDIR . $board_class->board['name'] .
						(($emoji_file['imgWidth'] <= 50 && $emoji_file['imgHeight'] <= 50)
							? ('/src/' . $emoji_file['file_name'])
							: ('/thumb/' . $emoji_file['file_name'] . 'c')
						) . $emoji_file['file_type'];
						copy($src, KU_ROOTDIR.I0_SMILEDIR.$emoji_name.$emoji_file['file_type']);
						$any_new = true;
					}
				}
			}
			// Reparse post
			if ($any_new)
				$post['message'] = $parse_class->Smileys($post['message']);
		}
		// ← Emoji registration
		// $timer->mark('06_emojis_2');

		// $upload_class->file_name, $upload_class->original_file_name, $filetype_withoutdot, $upload_class->file_md5, $upload_class->imgWidth, $upload_class->imgHeight, $upload_class->file_size, $upload_class->imgWidth_thumb, $upload_class->imgHeight_thumb
		$post_time = time();
		// $timer->mark('07_post_time');
		$post_class = new Post(0, $board_class->board['name'], $board_class->board['id'], true);
		$post_id = $post_class->Insert($thread_replyto, $post['name'], $post['tripcode'], $post['email'], $post['subject'], addslashes($post['message']), $upload_class->attachments, $post_passwordmd5, $post_time, $post_time, $posting_class->user_id, $user_authority_display, $sticky, $lock, $board_class->board['id'], $post['country'], $posting_class->is_new_user, $post_del_timestamp);

		// Update user activity stats in full anonymity mode
		if (I0_FULL_ANONYMITY_MODE) {
			$fields = "`idmd5`, `latest_post`, `post_count`".($post_isreply ? "" : ", `latest_thread`");
			$values = array($posting_class->user_id_md5_salted, $post_time, 1);
			if (!$post_isreply) {
				$values [] = $post_time;
				$lt = ",`latest_thread`=?";
			}
			$values []= $post_time;
			if (!$post_isreply) {
				$values []= $post_time;
			}
			$qms = "?,?,?".(!$post_isreply ? ",?" : '');
			$tc_db->Execute("INSERT INTO `user_activity` (".$fields.") VALUES (".$qms.")
				ON DUPLICATE KEY UPDATE `latest_post`=?,`post_count`=`post_count`+1".$lt, $values);
		}

		if ($user_authority > 0 && $user_authority != 3) {
			$modpost_message = 'Modposted #<a href="' . KU_BOARDSFOLDER . $board_class->board['name'] . '/res/';
			if ($post_isreply) {
				$modpost_message .= $thread_replyto;
			} else {
				$modpost_message .= $post_id;
			}
			$modpost_message .= '.html#' . $post_id . '">' . $post_id . '</a> with flags: ' . $flags . '.';
			management_addlogentry($modpost_message, 12, $board_class->board['name']);
		}
		// $timer->mark('08_modpost');

		// Give persistent cookie
		if ($posting_class->ipless_mode && $posting_class->need_cookie)
			setcookie('I0_persistent_id', $posting_class->user_id, time() + 31556926, '/'/*, KU_DOMAIN*/);

		if ($post['name_save'] && isset($_POST['name'])) {
			setcookie('name', $_POST['name'], time() + 31556926, '/', KU_DOMAIN);
		}

		if ($post['email_save']) {
			setcookie('email', $post['email'], time() + 31556926, '/', KU_DOMAIN);
		}

		setcookie('postpassword', $_POST['postpassword'], time() + 31556926, '/');
		// $timer->mark('09_cookies_set');

		$page_from = -1; $page_to = INF;

		if ($thread_replyto != '0') { // If it's a reply...
			$page_to = $board_class->GetPageNumber($thread_replyto)['page'];
			if (
				isset($_POST['em'])
				&&
				(
					strtolower($_POST['em']) == 'sage' // normal sage 
					||
					unistr_to_ords($_POST['em']) == array(19979, 12370) // japs' sage, if it even works
				)
				||
				$thread_replies > $board_class->board['maxreplies'] // the number of replies already in the thread are less than the maximum thread replies before perma-sage
			) { //...in case of sage, only one page needs to be regenerated
				$page_from = $page_to;
			}
			else { //...bump the threda
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `bumped` = '" . time() . "' WHERE `boardid` = " . $board_class->board['id'] . " AND `id` = '" . $thread_replyto . "'");
				// $timer->mark('10_bump_set');
			}
		}

		$tc_db->Execute("COMMIT");
		// $timer->mark('11_commit_sudoku');

		// Trim any threads which have been pushed past the limit, or exceed the maximum age limit
		TrimToPageLimit($board_class->board);
		// $timer->mark('12_trim_to_page_limit');

		// Regenerate board pages
		$board_class->RegeneratePages($page_from, $page_to);
		// $timer->mark('13_pages_regenerated');

		// Regeneate JSON
		$cl20 = new Cloud20();
		$cl20->rebuild();
		// $timer->mark('14_cloud_rebuilt');

		$need_overboard = I0_OVERBOARD_ENABLED && $board_class->board['section'] != '0' && $board_class->board['hidden'] == '0';

		if ($thread_replyto == '0') {
			// Regenerate the thread
			$board_class->RegenerateThreads($post_id);
			notify($board_class->board['name'].':threads', array(
				'action' => 'new_thread', 
				'new_thread_id' => $post_id));
			if ($need_overboard)
				notify(I0_OVERBOARD_DIR.':threads', array(
					'action' => 'new_thread', 
					'new_thread_id' => $post_id, 
					'board' => $board_class->board['name'],
					'board_desc' => $board_class->board['desc']));
		} else {
			// Regenerate the thread
			$board_class->RegenerateThreads($thread_replyto);
			notify($board_class->board['name'].':'.$thread_replyto, array('action' => 'new_reply', 'reply_id' => $post_id));
		}
		// $timer->mark('15_regenerated');

		// Regenerate overboard if it makes sense
		if ($need_overboard) {
			RegenerateOverboard($board_class->board['boardlist']);
			// $timer->mark('16_regen_overboard');
		}
	} 
	else {
		exitWithErrorPage(_gettext('Sorry, this board is locked and can not be posted in.'));
	}
}
elseif (
	(
		(
			isset($_POST['deletepost'])
			||
			isset($_POST['moddelete']) // required to allow mod deleting multiple posts without AJAX
			||
			isset($_POST['reportpost'])
			||
			isset($_POST['cancel_timer'])
		)
		&&
		isset($_POST['post'])
	)
	|| isset($_POST['delete-file'])
) {
	if ($_POST['AJAX'])
		$items_affected = array();

	$threads_to_regenerate = array(); // to prevent possible repeated regeneration
	$notifications_del = array();
	$notifications_deltimer = array();
	$pages_to_regenerate = array(); // single pages to regenerate
	$captcha_ok = false;

	// Check rights
	$pass = (isset($_POST['postpassword']) && $_POST['postpassword']!="") ? $_POST['postpassword'] : null;
	$ismod = !!$_POST['moddelete'];
	if ($ismod) {
		require_once KU_ROOTDIR . 'inc/classes/manage.class.php';
		$_POST['deletepost'] = true; // required to allow mod deleting multiple posts without AJAX
	}
	$isop = ( 
		$_POST['opdelete']
		&&
		$board_class->board['opmod']=='1'
	);

	// Post-related actions
	if (isset($_POST['post'])) foreach ($_POST['post'] as $val) {
		$xpl = explode(':', $val);
		if (count($xpl)==2) {
			list($post_id, $post_brd) = $xpl;
		}
		else {
			$post_id = $val;
			$post_brd = $_POST['board'];
		}
		$post_action = new PolymorphicReporter('post', $post_id, (boolean)$_POST['AJAX']);
		if ((isset($is_overboard) && $is_overboard) || ($post_brd!==NULL && $board_class->board['name'] != $post_brd)) { // is external board
			if (!isset($external_boards[$post_brd])) {
				$external_boards[$post_brd] = new Board($post_brd);
			}
			$b_class = $external_boards[$post_brd];
		}
		elseif (!isset($board_class))
			$post_action->fail(_gettext('No board provided'));
		else {
			$b_class = $board_class;
			$ismod = $ismod && Manage::CurrentUserIsModeratorOfBoard($b_class->board['name'], $_SESSION['manageusername']);
		}
		if (!isset($pages_to_regenerate[$b_class->board['name']]))
			$pages_to_regenerate[$b_class->board['name']] = array();
		if (!isset($pages_from[$b_class->board['name']]))
			$pages_from[$b_class->board['name']] = INF;
		if (!isset($pages_to[$b_class->board['name']]))
			$pages_to[$b_class->board['name']] = -1;

		$post_class = new Post($post_id, $b_class->board['name'], $b_class->board['id']);
		// Post reporting
		if (isset($_POST['reportpost'])) {
			$post_action->action = 'report';
			if ($b_class->board['enablereporting'] == 1) {
				$post_reported = $post_class->post['isreported'];
				if ($post_reported === 'cleared') {
					$post_action->fail(_gettext('That post has been cleared as not requiring any deletion.'));
				} elseif ($post_reported) {
					$post_action->fail(_gettext('That post is already in the report list.'));
				} else {
					if ($post_class->Report()) {
						$post_action->succ(_gettext('Post successfully reported'));
					} else {
						$post_action->fail(_gettext('Unable to report post. Please go back and try again.'));
					}
				}
			} else {
				$post_action->fail(_gettext('This board does not allow post reporting.'));
			}
		}
		else { // Actions that require password
			if ($isop && $post_class->post['parentid'] != '0') {
				$op_post_class = new Post($post_class->post['parentid'], $b_class->board['name'], $b_class->board['id']);
				// TODO: check if not deleted, check if not empty
				$pwd_ref_post = $op_post_class;
			}
			else {
				$isop = false;
				$pwd_ref_post = $post_class;
			}
			// Determine if post is locked due to many access attempts
			$locked = !$ismod && $post_class->CheckAccessLocked();
			$unlocked = !$locked;
			if ($locked) {
				if ($captcha_ok === false) {
					if ($_POST['captcha']) {
						$captcha_ok = $posting_class->CheckCaptcha(true);
						if (!$captcha_ok) {
							$post_action->fail(_gettext('Incorrect captcha entered.'), 'captchalocked');
						}
						else {
							$unlocked = true;
						}
					}
					else {
						$post_action->fail(_gettext('Insert captcha.'), 'captchalocked');
					}
				}
				else {
					$unlocked = $captcha_ok;
				}
			}
			if ($pass && $unlocked) {
				$passtype = $pwd_ref_post->post['password'] [0];
				if ($passtype == '+') { // modern hash with salt: +md5(password+post_id+boardid+randomseed)
					$pass_for_this_post = '+'.md5($pass . $pwd_ref_post->post['id'] . $b_class->board['id'] . KU_RANDOMSEED);
				}
				elseif ($passtype == '-') { // modern hash w/o salt: -md5(password+randomseed)
					if (!$passmd5_new)
						$passmd5_new = '-'.md5($pass . KU_RANDOMSEED);
					$pass_for_this_post = $passmd5_new;
				}
				else { // legacy hash: md5(password)
					if (!$passmd5_old)
						$passmd5_old = md5($pass);
					$pass_for_this_post = $passmd5_old;
				}
			}
			$granted = $unlocked && ($ismod || ($pass && $pass_for_this_post == $pwd_ref_post->post['password']));
			if ($granted) {
				$thread_id = $post_class->post['parentid'] != '0' ? $post_class->post['parentid'] : $post_class->post['id'];
				$room_id = $b_class->board['name'].':'.$thread_id;
				$origin_page = $b_class->GetPageNumber($thread_id);
				// Timer cancelling
				if (isset($_POST['cancel_timer'])) {
					$post_action->action = 'cancel_timer';
					$cancelres = $post_class->CancelTimer();
					if ($cancelres) {
						if ($cancelres === true) {
							if (! isset($notifications_deltimer[$room_id]))
								$notifications_deltimer[$room_id] = array();
							$notifications_deltimer[$room_id] []= array(
								'action' => 'cancel_timer',
								'id' => $post_class->post['id'],
								'thread_id' => $thread_id,
								'by_mod' => $ismod,
								'by_op' => $isop
							);
							if ($locked) {
								$post_class->Unlock();
							}
							if (! in_array($room_id, $threads_to_regenerate)) {
								$threads_to_regenerate []= $room_id;
							}
							if (!in_array($origin_page['page'], $pages_to_regenerate[$b_class->board['name']])) {
								$pages_to_regenerate[$b_class->board['name']] []= $origin_page['page'];
							}
							$post_action->succ(_gettext('Timer removed'));
						}
						else {
							$post_action->succ(_gettext($cancelres)); // ← $cancelres must contain message text
						}
					}
					else {
						$post_action->fail(_gettext('There was an error in trying to remove a timer from your post')); // will never occur
					}
				}
				// Post deleting
				if (isset($_POST['deletepost'])) {
					$post_action->action = 'delete';
					$isownpost = !$ismod && !$isop;
					$delres = $post_class->Delete(false, $isownpost && I0_ERASE_DELETED, $ismod);
					if ($delres) {
						if ($delres !== 'already_deleted') { // Skip the unneeded rebuild if the post is already deleted
							if (! isset($notifications_del[$room_id]))
								$notifications_del[$room_id] = array();
							$notifications_del[$room_id] []= array(
								'action' => 'delete_post',
								'id' => $post_class->post['id'],
								'thread_id' => $thread_id,
								'by_mod' => $ismod,
								'by_op' => $isop
							);
							if ($post_class->post['parentid'] != '0') { // Deleting a reply
								if (! in_array($room_id, $threads_to_regenerate)) {
									$threads_to_regenerate []= $room_id;
								}
								if ($ismod) {
									management_addlogentry(_gettext('Deleted post') . ' #<a href="?action=viewthread&thread='. $thread_id . '&board='. $b_class->board['name'] . '#'. $post_id . '">'. $post_id . '</a>', 7, $b_class->board['name']);
								}
								if ($delres == 'unbumped') { // thread may move down some pages
									$destination_page = $b_class->GetPageNumber($thread_id);
									if ($origin_page['page'] == $destination_page['page']) { // (or maybe not)
										if (!in_array($destination_page['page'], $pages_to_regenerate[$b_class->board['name']])) {
											$pages_to_regenerate[$b_class->board['name']] []= $destination_page['page'];
										}
									}
									else {
										if ($pages_from[$b_class->board['name']]===false || $origin_page['page'] < $pages_from[$b_class->board['name']]) {
											$pages_from[$b_class->board['name']] = $origin_page['page'];
										}
										if ($pages_to[$b_class->board['name']]===false || $destination_page['page'] > $pages_to[$b_class->board['name']]) {
											$pages_to[$b_class->board['name']] = $destination_page['page'];
										}
									}
								}
								else { // If there were no changes in threda order, only one page needs to be regenerated
									if (!in_array($origin_page['page'], $pages_to_regenerate[$b_class->board['name']])) {
										$pages_to_regenerate[$b_class->board['name']] []= $origin_page['page'];
									}
								}
								$post_action->succ(_gettext('Post successfully deleted.')
									.($ismod ? ' '._gettext('(By mod)') : '')
									.($isop ? ' '._gettext('(By OP)') : ''));
							}
							else { // Deleting a threda
								$pages_to[$b_class->board['name']] = INF;
								$destination_page = $b_class->GetPageNumber($thread_id);
								if ($origin_page['n_pages'] == $destination_page['n_pages']) {
									if ($pages_from[$b_class->board['name']]===false || $origin_page['page'] < $pages_from[$b_class->board['name']]) {
										$pages_from[$b_class->board['name']] = $origin_page['page'];
									}
								}
								else {
									$pages_from[$b_class->board['name']] = -1;
								}
								$room_id = $b_class->board['name'].':threads';
								if (! isset($notifications_del[$room_id]))
									$notifications_del[$room_id] = array();
								$notifications_del[$room_id] []= array(
									'action' => 'delete_thread',
									'id' => $thread_id,
									'by_mod' => $ismod,
									'by_op' => $isop
								);
								if ($ismod) {
									management_addlogentry(_gettext('Deleted thread') . ' #<a href="?action=viewthread&thread='. $thread_id . '&board='. $b_class->board['name'] . '">'. $post_id . '</a> ('. ($delres-1) . ' replies)', 7, $b_class->board['name']);
								}
								$post_action->succ(_gettext('Thread successfully deleted.')
									.($ismod ? ' '._gettext('(By mod)') : '')
									.($isop ? ' '._gettext('(By OP)') : '')
									.' '.($delres-1)._gettext('replies deleted').'.');
							}
							if (I0_FULL_ANONYMITY_MODE && $isownpost) {
								$latest_thread = $post_class->post['parentid'] == '0' ? ", `latest_thread`=1" : "";
								$tc_db->Execute("UPDATE `user_activity` SET 
									`latest_post`=1" .
									$latest_thread .
									", `post_count`=IF(`post_count`>0, `post_count`-1, 0)
									WHERE `idmd5`=?", array($posting_class->user_id_md5_salted)	);
							}
						}
						else {
							$post_action->succ(_gettext('Post is already deleted.'));
						}
					}
					else {
						$post_action->fail(_gettext('There was an error in trying to delete your post'));
					}
				}
			}
			elseif ($post_action->special_error !== 'captchalocked') {
				$post_action->fail(_gettext('Incorrect password.'));
			}
		}
		$items_affected []= $post_action->report();
	}
	// File deleting
	if (isset($_POST['delete-file'])) foreach($_POST['delete-file'] as $val) {
		list($file, $post_brd) = explode(':', $val);
		$file_action = new PolymorphicReporter('file', $file, (boolean)$_POST['AJAX']);
		$file_action->action = 'delete-file';
		if ($is_overboard || ($post_brd!==NULL && $board_class->board['name'] != $post_brd)) { // is external board
			if (!isset($external_boards[$post_brd])) {
				$external_boards[$post_brd] = new Board($post_brd);
			}
			$b_class = $external_boards[$post_brd];
		}
		elseif (!isset($board_class))
			$post_action->fail(_gettext('No board provided'));
		else {
			$b_class = $board_class;
			$ismod = $ismod && Manage::CurrentUserIsModeratorOfBoard($b_class->board['name'], $_SESSION['manageusername']);
		}
		if (!isset($pages_from[$b_class->board['name']]))
			$pages_from[$b_class->board['name']] = INF;
		if (!isset($pages_to[$b_class->board['name']]))
			$pages_to[$b_class->board['name']] = -1;

		$fdres = $b_class->DeleteFile($file, $pass, $ismod);
		if ($fdres['error'])
			$file_action->fail($fdres['error']);
		else {
			$room_id = $b_class->board['name'].':'.$fdres['parentid'];
			if (! isset($notifications_del[$room_id]))
				$notifications_del[$room_id] = array();
			$notifications_del[$room_id] []= array(
				'action' => 'delete_file',
				'id' => $file
			);
			$file_action->succ(_gettext('Image successfully deleted from your post.'));
			if (! $fdres['already_deleted']) {
				if (! in_array($room_id, $threads_to_regenerate)) {
					$threads_to_regenerate []= $room_id;
				}
				$page = $b_class->GetPageNumber($fdres['parentid'])['page'];
				if (
					!is_array($pages_to_regenerate[$b_class->board['name']]) 
					|| 
					!in_array($page, $pages_to_regenerate[$b_class->board['name']])
				) {
					$pages_to_regenerate[$b_class->board['name']] []= $page;
				}
			}
		}
		$items_affected []= $file_action->report();
	}
	// Regeneration
	$need_overboard = false;
	foreach($threads_to_regenerate as $room_id) {
		list($brd, $thread_id) = explode(':', $room_id);
		if ((isset($is_overboard) && $is_overboard) || $board_class->board['name'] != $brd) {
			$external_boards[$brd]->RegenerateThreads($thread_id);
			if (I0_OVERBOARD_ENABLED && !$need_overboard && $external_boards[$brd]->board['section'] != '0' && $external_boards[$brd]->board['hidden'] == '0') {
				$need_overboard = true;
				$over_boardlist = $external_boards[$brd]->board['boardlist'];
			}
		}
		else {
			$board_class->RegenerateThreads($thread_id);
			if (I0_OVERBOARD_ENABLED && !$need_overboard && $board_class->board['section'] != '0' && $board_class->board['hidden'] == '0') {
				$need_overboard = true;
				$over_boardlist = $board_class->board['boardlist'];
			}
		}
	}
	foreach($pages_to_regenerate as $brd=>$pages) {
		$out_of_range = array();
		foreach($pages as $page) {
			if ($page < $pages_from[$brd] || $page > $pages_to[$brd]) {
				$out_of_range []= $page;
			}
		}
		if (
			($pages_from[$brd] < INF && $pages_to[$brd] > $pages_from[$brd]) 
			|| 
			count($out_of_range) != 0
		) {
			if ((isset($is_overboard) && $is_overboard) || $board_class->board['name'] != $brd) {
				$external_boards[$brd]->RegeneratePages($pages_from[$brd], $pages_to[$brd], $out_of_range);
				if (I0_OVERBOARD_ENABLED && !$need_overboard && $external_boards[$brd]->board['section'] != '0' && $external_boards[$brd]->board['hidden'] == '0') {
					$need_overboard = true;
					$over_boardlist = $external_boards[$brd]->board['boardlist'];
				}
			}
			else {
				$board_class->RegeneratePages($pages_from[$brd], $pages_to[$brd], $out_of_range);
				if (I0_OVERBOARD_ENABLED && !$need_overboard && $board_class->board['section'] != '0' && $board_class->board['hidden'] == '0') {
					$need_overboard = true;
					$over_boardlist = $board_class->board['boardlist'];
				}
			}
		}
	}
	// Regenerate overboard if it makes sense
	if ($need_overboard) {
		RegenerateOverboard($over_boardlist);
	}
	
	// Finish
	foreach ($notifications_del as $room=>$data) {
		notify($room, array('action' => 'delete', 'items' => $data));
	}
	foreach ($notifications_deltimer as $room=>$data) {
		notify($room, array('action' => 'cancel_timer', 'items' => $data));
	}
	if ($_POST['AJAX'])
		exit(json_encode(array(
			'action' => 'multi_post_action',
			'data' => $items_affected
		)));
	else
		do_redirect(KU_BOARDSPATH . '/' . ($is_overboard ? I0_OVERBOARD_DIR : $board_class->board['name']) . '/');
	die();
}
else {
	error_redirect(KU_BOARDSPATH . '/' . ($is_overboard ? I0_OVERBOARD_DIR : $board_class->board['name']) . '/', _gettext('Unspecified action'));
}

if (KU_RSS) {
	require_once KU_ROOTDIR . 'inc/classes/rss.class.php';
	$rss_class = new RSS();

	print_page(KU_BOARDSDIR.$_POST['board'].'/rss.xml',$rss_class->GenerateRSS($_POST['board'], $board_class->board['id']),$_POST['board']);
	// $timer->mark('17_rss', true); // fin
}

if(
	(
		isset($_POST['redirecttothread']) 
		&& 
		$_POST['redirecttothread'] == 1
	) 
	|| 
	(
		isset($_POST['em'])
		&&
		(
			$_POST['em'] == 'return' 
			|| 
			$_POST['em'] == 'noko'
		)
	)
) {
	setcookie('tothread', 'on', time() + 31556926, '/');
	if (! $_POST['AJAX']) {
		if ($thread_replyto == "0") {
			do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/res/' . $post_id . '.html', true);
		} else {
			do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/res/' . $thread_replyto . '.html', true);
		}
	}
} else {
	setcookie('tothread', 'off', time() + 31556926, '/');
	if (! $_POST['AJAX'])
		do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/', true);
}

if ($_POST['AJAX']) {
	exit(json_encode(array(
		'error' => false,
		'action' => 'post',
		'thread_replyto' => $thread_replyto,
		'post_id' => $post_id,
		'board' => $board_class->board['name']
		// ,'timings' => // $timer->marks
	)));
}
