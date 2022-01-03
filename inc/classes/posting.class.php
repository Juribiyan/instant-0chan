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
 * Posting class
 * +------------------------------------------------------------------------------+
 */
class Posting {

	function __construct() {
		global $tc_db, $board_class;

		$this->ipless_mode = (I0_IPLESS_MODE==true || (I0_IPLESS_MODE=='auto' && $_SERVER['REMOTE_ADDR']=='127.0.0.1'));

		// Set user ID
		$this->is_new_user = false;
		$this->need_cookie = false;
		if ($this->ipless_mode) {
			if (isset($_COOKIE['I0_persistent_id'])) {
				$user_id = $_COOKIE['I0_persistent_id'];
			}
			else {
				$user_id = session_id();
				$this->need_cookie = true;
				$this->is_new_user = true;
			}
			$user_id_trunc = substr($user_id, 0, 45);
			if ($user_id_trunc != $user_id) {
				$this->need_cookie = true;
				$this->is_new_user = true;
			}
			$this->user_id = $user_id_trunc;
		}
		else {
			$this->user_id = $_SERVER['REMOTE_ADDR'];
		}
		$this->user_id_md5 = md5($this->user_id);
		if (I0_FULL_ANONYMITY_MODE) {
			$this->user_id_md5_salted = md5($this->user_id . $board_class->board['id'] . KU_RANDOMSEED);
			$user_stats = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "user_activity` WHERE `idmd5`=?", array($this->user_id_md5_salted));
			if (count($user_stats)) {
				$this->user_stats = $user_stats[0];
			}
			else
				$this->is_new_user = true;
		}
		if (I0_GLOBAL_NEWTHREADDELAY > 0 && !$this->is_new_user) {
			if (I0_FULL_ANONYMITY_MODE) {
				$this->is_new_user = !(
					$this->user_stats['latest_thread'] > 0
					||
					(
						I0_REPLIES_TO_RECOGNIZE
						&&
						$this->user_stats['post_count'] >= I0_REPLIES_TO_RECOGNIZE
					)
				);
			}
			else {
				// Check if the user has created any threads on this board
				$any_threads = $tc_db->GetOne("SELECT COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid`=? AND `parentid`='0' AND `ipmd5`=? LIMIT 1",
					array($board_class->board['id'], $this->user_id_md5));
				if ($any_threads)
					$this->is_new_user = false;
				elseif (I0_REPLIES_TO_RECOGNIZE) { // Check if the user has created a sufficient number of posts on this board
					$post_count = $tc_db->GetOne("SELECT COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid`=? AND `ipmd5`=? LIMIT ?",
						array($board_class->board['id'], $this->user_id_md5, I0_REPLIES_TO_RECOGNIZE));
					$this->is_new_user = ($post_count < I0_REPLIES_TO_RECOGNIZE);
				}
				else
					$this->is_new_user = true;
			}
		}
		$this->now = time();
	}

	function CheckReplyTime() {
		global $tc_db, $board_class;
		$delta = 0;
		if (I0_FULL_ANONYMITY_MODE) {
			$delta = KU_REPLYDELAY - ($this->now - $this->user_stats['latest_post']);
		}
		else {
			/* Get the timestamp of the last time a reply was made by this IP address */
			$result = $tc_db->GetOne("SELECT MAX(timestamp) 
				FROM `" . KU_DBPREFIX . "posts` 
				WHERE 
					`boardid` = " . $board_class->board['id'] . " 
					AND 
					`ipmd5` = '" . $this->user_id_md5 . "' 
					AND
					NOT `IS_DELETED`
					AND 
					`timestamp` > " . ($this->now - KU_REPLYDELAY));
			if ($result) {
				$delta = KU_REPLYDELAY - ($this->now - $result);
			}
		}
		if ($delta > 0) {
			exitWithErrorPage(sprintf(_gettext('Please wait %d s before posting again.'), $delta), 
				_gettext('You are currently posting faster than the configured minimum post delay allows.'));
		}
	}

	function CheckNewThreadTime() {
		global $tc_db, $board_class;

		/* Check the global new thread delay */
		if (I0_GLOBAL_NEWTHREADDELAY > 0 && $this->is_new_user) {
			$result = $tc_db->GetOne("SELECT MAX(timestamp) 
				FROM `" . KU_DBPREFIX . "posts` 
				WHERE 
					`boardid` = " . $board_class->board['id'] . " 
					AND 
					`parentid` = 0 
					AND 
					`by_new_user` = '1'
					AND
					NOT `IS_DELETED`
					AND 
					`timestamp` > " . ($this->now - I0_GLOBAL_NEWTHREADDELAY));
			if ($result) {
				exitWithErrorPage(sprintf(_gettext('Please wait %d s before posting again.'), I0_GLOBAL_NEWTHREADDELAY - ($this->now - $result)), 
					_gettext('Global new thread delay for new users is imposed.'));
			}
		}
		else {
			$delta = 0;
			/* Get the timestamp of the last time a new thread was made by this IP address */
			if (I0_FULL_ANONYMITY_MODE) {
				$delta = KU_NEWTHREADDELAY - ($this->now - $this->user_stats['latest_thread']);
			}
			else {
				$result = $tc_db->GetOne("SELECT MAX(timestamp) 
					FROM `" . KU_DBPREFIX . "posts` 
					WHERE 
						`boardid` = " . $board_class->board['id'] . " 
						AND 
						`parentid` = 0 
						AND 
						`ipmd5` = '" . $this->user_id_md5 . "' 
						AND
						NOT `IS_DELETED`
						AND 
						`timestamp` > " . ($this->now - KU_NEWTHREADDELAY));
				if ($result) {
					$delta = KU_NEWTHREADDELAY - ($this->now - $result);
				}
			}
			if ($delta > 0) {
				exitWithErrorPage(sprintf(_gettext('Please wait %d s before posting again.'), $delta), 
					_gettext('You are currently posting faster than the configured minimum post delay allows.'));
			}
		}
	}

	function UTF8Strings() {
		if (function_exists('mb_convert_encoding') && function_exists('mb_check_encoding')) {
			if (isset($_POST['name']) && !mb_check_encoding($_POST['name'], 'UTF-8')) {
				$_POST['name'] = mb_convert_encoding($_POST['name'], 'UTF-8');
			}
			if (isset($_POST['em']) && !mb_check_encoding($_POST['em'], 'UTF-8')) {
				$_POST['em'] = mb_convert_encoding($_POST['em'], 'UTF-8');
			}
			if (isset($_POST['subject']) && !mb_check_encoding($_POST['subject'], 'UTF-8')) {
				$_POST['subject'] = mb_convert_encoding($_POST['subject'], 'UTF-8');
			}
			if (isset($_POST['message']) && !mb_check_encoding($_POST['message'], 'UTF-8')) {
				$_POST['message'] = mb_convert_encoding($_POST['message'], 'UTF-8');
			}
		}
	}

	function CheckValidPost($post_isreply) {
		global $tc_db, $board_class, $upload_class;

		if (!count($upload_class->attachments) && !preg_match('/\S/', $_POST['message'])) {
			if ($post_isreply) {
				exitWithErrorPage(_gettext('An image, video, or message, is required for a reply.'));
			}
			elseif ($board_class->board['enablenofile'] == true) {
				exitWithErrorPage('A message is required to post without a file.');
			}
			else {
				exitWithErrorPage(_gettext('A file is required for a new thread. If embedding is allowed, either a file or embed ID is required.'));
			}
		}

		return true;
	}

	function CheckMessageLength() {
		global $board_class;

		/* If the length of the message is greater than the board's maximum message length... */
		if (strlen($_POST['message']) > $board_class->board['messagelength']) {
			/* Kill the script, stopping the posting process */
			exitWithErrorPage(sprintf(_gettext('Sorry, your message is too long. Message length: %d, maximum allowed length: %d'), strlen($_POST['message']), $board_class->board['messagelength']));
		}
	}

	// universal captcha interface
	function CheckCaptcha($for_access=false) {
		global $board_class;
		mb_internal_encoding("UTF-8");
		if ($for_access || $board_class->board['enablecaptcha'] != 0) {
			$result = $board_class->board['enablecaptcha']==2 ? $this->CheckHcaptcha() : $this->CheckDefaultCaptcha();
			if ($for_access) {
				return ($result == 'ok');
			}
			if ($result == 'expired') {
				exitWithErrorPage(_gettext('Captcha has expired.'));
			}
			if ($result == 'incorrect') {
				exitWithErrorPage(_gettext('Incorrect captcha entered.'));
			}
		}
	}

	function CheckDefaultCaptcha() {
		$code = $_SESSION['security_code'];
		unset($_SESSION['security_code']);
		$submit_time = time();
		if($submit_time - $_SESSION['captchatime'] > KU_CAPTCHALIFE) {
			return 'expired';
		}
		if ($code != mb_strtoupper($_POST['captcha']) || empty($code)) {
			return 'incorrect';
		}
		return 'ok';
	}

	function CheckHcaptcha() {
		if ($_POST['h-captcha-response']) {
			$data = array(
			  'secret' => I0_HCAPTCHA_SECRET,
			  'response' => $_POST['h-captcha-response']
			);
			$verify = curl_init();
			curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
			curl_setopt($verify, CURLOPT_POST, true);
			curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($verify);
			$responseData = json_decode($response);
			return ($responseData->success == true) ? 'ok' : 'incorrect';
		}
		return 'incorrect';
	}

	// Check duplicate files and embeds, check banned files
	function CheckBannedHash() {
		global $tc_db, $board_class, $bans_class, $upload_class;

		foreach($upload_class->attachments as &$attachment) {
			$results = $tc_db->GetAll("SELECT `bantime` , `description` 
				FROM `" . KU_DBPREFIX . "bannedhashes` 
				WHERE `md5` = " . $tc_db->qstr($attachment['file_md5']) . " 
				LIMIT 1");
			if (count($results) > 0) {
				$bans_class->BanUser($this->user_id, 'SERVER', '1', $results[0]['bantime'], '', 'Posting a banned file.<br />' . $results[0]['description'], 0, 0, 1);
				$bans_class->BanCheck($this->user_id, $board_class->board['name']);
				die();
				// TODO: AJAX response
			}
		}
	}

	function CheckIsReply() {
		global $tc_db, $board_class;

		/* If it appears this is a reply to a thread, and not a new thread... */
		if (isset($_POST['replythread'])) {
			if ($_POST['replythread'] != '0') {
				/* Check if the thread id supplied really exists */
				$results = $tc_db->GetOne("SELECT COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id'] . " AND `IS_DELETED` = '0' AND `id` = " . $tc_db->qstr($_POST['replythread']) . " AND `parentid` = '0' LIMIT 1");
				/* If it does... */
				if ($results > 0) {
					return true;
				/* If it doesn't... */
				} else {
					/* Kill the script, stopping the posting process */
					exitWithErrorPage(_gettext('Invalid thread ID.'), _gettext('That thread may have been recently deleted.'));
				}
			}
		}

		return false;
	}

/*	function CheckNotDuplicateSubject($subject) {
		global $tc_db, $board_class;

		$result = $tc_db->GetOne("SELECT COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id'] . " AND `IS_DELETED` = '0' AND `subject` = " . $tc_db->qstr($subject) . " AND `parentid` = '0'");
		if ($result > 0) {
			exitWithErrorPage(_gettext('Duplicate thread subject'), _gettext('This board may have only one thread with a unique subject. Please pick another.'));
		}
	} */

	function GetThreadInfo($id) {
		global $tc_db, $board_class;

		/* Check if the thread id supplied really exists and if it is locked */
		$results = $tc_db->GetAll("SELECT `id`,`locked` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id'] . " AND `IS_DELETED` = '0' AND `id` = " . $tc_db->qstr($id) . " AND `parentid` = '0'");
		/* If it does... */
		if (count($results) > 0) {
			/* Get the thread's info */
			$thread_locked = $results[0]['locked'];
			$thread_replyto = $results[0]['id'];
			/* Get the number of replies */
			$result = $tc_db->GetOne("SELECT COUNT(id) FROM `" . KU_DBPREFIX ."posts` WHERE `boardid` = " . $board_class->board['id'] . " AND `IS_DELETED` = '0' AND `parentid` = " . $tc_db->qstr($id) . "");
			$thread_replies = $result;

			return array($thread_replies, $thread_locked, $thread_replyto);
		} else {
			/* If it doesn't, kill the script, stopping the posting process */
			exitWithErrorPage(_gettext('Invalid thread ID.'), _gettext('That thread may have been recently deleted.'));
		}
	}

	function GetFields() {
		/* Fetch and process the name, email, and subject fields from the post data */
		$post_name = isset($_POST['name']) && (!isset($_POST['disable_name']) || $_POST['disable_name'] != 1) ? htmlspecialchars($_POST['name'], ENT_QUOTES) : '';
		$post_email = isset($_POST['em']) ? str_replace('"', '', strip_tags($_POST['em'])) : '';
		/* If the user used a software function, don't store it in the database */
		if ($post_email == 'return' || $post_email == 'noko') $post_email = '';
		$post_subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject'], ENT_QUOTES) : '';
		/* Calculate the post's deleted timestamp */
		$ttl = (isset($_POST['ttl-enable']) && $_POST['ttl-enable']) ? round(floatval($_POST['ttl'])) : 0;
		$post_del_timestamp = ($ttl<1) ? 0 : time() + $ttl*3600;
		return array($post_name, $post_email, $post_subject, $post_del_timestamp);
	}

	function GetUserAuthority() {
		global $tc_db, $board_class;

		$user_authority = 0;
		$flags = '';

		if (isset($_POST['modpassword'])) {

			$results = $tc_db->GetAll("SELECT `type`, `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . md5_decrypt($_POST['modpassword'], KU_RANDOMSEED) . "' LIMIT 1");
			if (count($results) > 0) {
				$entry = $results[0];
				if ($entry['type'] == 1) {
					$user_authority = 1; // admin
				} 
				elseif (
					$entry['type'] == 2 
					&& 
					(
						in_array($board_class->board['name'], explode('|', $entry['boards']))
						||
						$entry['boards'] == 'allboards'
					)
				) {
					$user_authority = 2; // mod
				}
				elseif (
					$entry['type'] == 3
					&&
					in_array($board_class->board['name'], explode('|', $entry['boards']))
				) {
					$user_authority = 3; // 2.0 board owner
				}
				if ($user_authority < 3) { /* set posting flags for mods and admins */
					if (isset($_POST['displaystaffstatus'])) $flags .= 'D';
					if (isset($_POST['lockonpost'])) $flags .= 'L';
					if (isset($_POST['stickyonpost'])) $flags .= 'S';
					if (isset($_POST['rawhtml'])) $flags .= 'RH';
					if (isset($_POST['usestaffname'])) $flags .= 'N';
				}
			}
		}

		return array($user_authority, $flags);
	}

	function CheckBadUnicode($post_name, $post_email, $post_subject, $post_message) {
		global $upload_class;
		/* Check for bad characters which can cause the page to deform (right-to-left markers, etc) */
		$bad_ords = array(8235, 8238);

		$ords_name = unistr_to_ords($post_name);
		$ords_email = unistr_to_ords($post_email);
		$ords_subject = unistr_to_ords($post_subject);
		$ords_message = unistr_to_ords($post_message);
		$ords_attachment = array();
		foreach ($upload_class->attachments as $attachment) {
			$ords_attachment []= unistr_to_ords(($attachment['attachmenttype'] == 'file') 
				? $attachment['file_original']
				: $attachment['embed'] );
		}
		foreach ($bad_ords as $bad_ord) {
			if ($ords_name != '') {
				if (in_array($bad_ord, $ords_name)) {
					exitWithErrorPage(_gettext('Your post contains one or more illegal characters.'));
				}
			}
			if ($ords_email != '') {
				if (in_array($bad_ord, $ords_email)) {
					exitWithErrorPage(_gettext('Your post contains one or more illegal characters.'));
				}
			}
			if ($ords_subject != '') {
				if (in_array($bad_ord, $ords_subject)) {
					exitWithErrorPage(_gettext('Your post contains one or more illegal characters.'));
				}
			}
			if ($ords_message != '') {
				if (in_array($bad_ord, $ords_message)) {
					exitWithErrorPage(_gettext('Your post contains one or more illegal characters.'));
				}
			}
			foreach($ords_attachment as $ord) {
				if (in_array($bad_ord, $ord)) {
					exitWithErrorPage(_gettext('Your post contains one or more illegal characters.'));
				}
			}
		}
	}

	function CheckBlacklistedText() { // legacy & unused function, but let it be.
		global $bans_class, $tc_db;

		$badlinks = array_map('rtrim', file(KU_ROOTDIR . 'spam.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

		foreach ($badlinks as $badlink) {
			if (stripos($_POST['message'], $badlink) !== false) {
				/* They included a blacklisted link in their post. Ban them for an hour */
				$bans_class->BanUser($this->user_id, 'board.php', 1, 3600, '', _gettext('Posting a blacklisted link.') . ' (' . $badlink . ')', $_POST['message']);
				exitWithErrorPage(sprintf(_gettext('Blacklisted link ( %s ) detected.'), $badlink));
			}
		}
	}

	//YOBA blacklist and flood detection
	function postParseCheckText($msg, $board, $boardid) {
		global $bans_class, $tc_db;

		// Check if message html does not exceed field value
		$maxlength = (int)($tc_db->GetOne("SELECT character_maximum_length 
			FROM   information_schema.columns 
			WHERE  table_name = '".KU_DBPREFIX."posts' AND	column_name = 'message'"));
		$msglength = strlen($msg);
		if ($msglength > $maxlength) {
			/* Kill the script, stopping the posting process */
			exitWithErrorPage(sprintf(_gettext('Sorry, the resulting HTML of your message is too long. HTML length: %d, maximum allowed length: %d'), $msglength, $maxlength));
		}

		$cyr = array('А', 'а', 'В', 'Е', 'е', 'К', 'М', 'Н', 'О', 'о', 'Р', 'р', 'С', 'с', 'Т', 'у', 'Х', 'х');
		$lat = array('A', 'a', 'B', 'E', 'e', 'K', 'M', 'H', 'O', 'o', 'P', 'p', 'C', 'c', 'T', 'y', 'X', 'x');

		$msg = mb_strtolower(strip_tags(str_replace($cyr, $lat, $msg)));

		if(!strlen($msg)) return;

		$lastmsg = $tc_db->GetAll("SELECT `message` FROM `".KU_DBPREFIX."posts` WHERE  `ipmd5` = '" . $this->user_id_md5 . "' AND `boardid`='".$boardid."' ORDER BY `timestamp` DESC LIMIT 1");
		if (count($lastmsg)) {
			$lastmsg = mb_strtolower(strip_tags(str_replace($cyr, $lat, $lastmsg[0]['message'])));
			if($msg == $lastmsg) exitWithErrorPage(_gettext('Flood Detected'), _gettext('You are posting the same message again.'));
		}

		$sturl = KU_BOARDSDIR . $board . '/spam.txt';

		$glsturl = KU_BOARDSDIR . '/spam.txt';

		if (!file_exists($sturl) && !file_exists($glsturl)) {
			return;
		} elseif (file_exists($sturl) && !file_exists($glsturl)) {
			$badlinks = array_map('rtrim', file($sturl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		} elseif (!file_exists($sturl) && file_exists($glsturl)) {
			$badlinks = array_map('rtrim', file($glsturl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		} else {
			$badlinks = array_map('rtrim', file($sturl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), file($glsturl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		}

		foreach ($badlinks as $badlink) {
			if (stripos($msg, mb_strtolower(str_replace($cyr, $lat, $badlink))) !== false) {
				/* They included a blacklisted link in their post. Ban them for an hour */
				$bans_class->BanUser($this->user_id, 'board.php', 0, 3600, $board, _gettext('Posting a blacklisted link.') . ' (' . $badlink . ')', $_POST['message']);
				exitWithErrorPage(sprintf(_gettext('Blacklisted link ( %s ) detected.'), $badlink));
			}
		}
	}
}

?>
