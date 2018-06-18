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

function notify($id="???", $newthreadid = '') {
	$cl20 = new Cloud20();
	$cl20->rebuild();

	if(KU_LIVEUPD_ENA) {
		$data_string = json_encode(array('srvtoken' => KU_LIVEUPD_SRVTOKEN, 'room' => $id, 'clitoken' => $_POST['token'], 'timestamp' => time(), 'newthreadid' => $newthreadid ));
		$suckTo = KU_LIVEUPD_SITENAME ? KU_LOCAL_LIVEUPD_API.'/qr/'.KU_LIVEUPD_SITENAME : KU_LOCAL_LIVEUPD_API;
		$ch = curl_init($suckTo);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_PROXY, "");
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.5);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);
		curl_exec($ch);
		if(curl_errno($ch)) error_log('Curl error during Notify: ' . curl_error($ch).' (Error code: '.curl_errno($ch).')');
	}
}

class PolymorphicReporter {
  function __construct($itemtype, $id, $is_ajax) {
  	$this->itemtype = $itemtype;
    $this->id = $id;
    $this->is_ajax = $is_ajax;
  }

  function fail($msg="") {
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
      'message' => $this->message
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

// In some cases, the board value is sent through post, others get
if (isset($_POST['board']) || isset($_GET['board'])) $_POST['board'] = (isset($_GET['board'])) ? $_GET['board'] : $_POST['board'];

// If the script was called using a board name:
if (isset($_POST['board'])) {
	$board_name = $tc_db->GetOne("SELECT `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
	if ($board_name !== false) {
		$board_class = new Board($board_name);
		if (!empty($board_class->board['locale'])) {
			changeLocale($board_class->board['locale']);
		}
	} else {
		error_redirect(KU_WEBPATH, _gettext('No board provided'));
	}
} else {
	// A board being supplied is required for this script to function
	error_redirect(KU_WEBPATH, _gettext('No board provided'));
}
// Must be declared after board class
$posting_class = new Posting();

// Expired ban removal, and then existing ban check on the current user
$ban_result = $bans_class->BanCheck($posting_class->user_id, $board_class->board['name']);
if ($ban_result && is_array($ban_result) && $_POST['AJAX']) {
	exit(json_encode(array(
	  'error' => _gettext('YOU ARE BANNED'),
	  'error_type' => 'ban'
	)));
}

/* Ensure that UTF-8 is used on some of the post variables */
$posting_class->UTF8Strings();

if (isset($_POST['makepost'])) { // A more evident way to identify post action, as actual validity will be checked later anyway
	$tc_db->Execute("START TRANSACTION");
	$posting_class->CheckReplyTime();
	$post_isreply = $posting_class->CheckIsReply();
	if(! $post_isreply) $posting_class->CheckNewThreadTime();

	require_once KU_ROOTDIR . 'inc/classes/upload.class.php';
	$upload_class = new Upload();

	$upload_class->UnifyAttachments();
	$posting_class->CheckValidPost($post_isreply);
	$posting_class->CheckMessageLength();
	$posting_class->CheckCaptcha();
	$posting_class->CheckBannedHash();
	list($thread_replies, $thread_locked, $thread_replyto) = $post_isreply
		? $posting_class->GetThreadInfo($_POST['replythread'])
		: array(0,0,0);
	list($post_name, $post_email, $post_subject, $post_tag) = $posting_class->GetFields();
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

		$post_message = $parse_class->ParsePost($_POST['message'], $board_class->board['name'], $thread_replyto, $board_class->board['id'], false, $ua, $dice, $posting_class->user_id_md5);
	// Or, if they are a moderator/administrator...
	} 
	else {
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
			$post_message = $parse_class->ParsePost($_POST['message'], $board_class->board['name'], $thread_replyto, $board_class->board['id'], false, $ua, $dice, $posting_class->user_id_md5);
			// (Moved) check against blacklist and detect flood
		}

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
		$post_passwordmd5 = ($post_password == '') ? '' : md5($post_password);

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
				$thumbfiletype = ($attachment['filetype_withoutdot'] == 'webm')	? '.jpg' : $attachment['file_type'];
				if ($attachment['emoji_candidate']) {
					$emoji_candidates []= $attachment;
				}
				if (
					!file_exists(KU_BOARDSDIR . $board_class->board['name'] . '/src/' . $attachment['file_name'] . $attachment['file_type'])
					||
					(
						!$attachment['file_is_special']
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

		$post = array();
		$post['country'] = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? strtolower($_SERVER["HTTP_CF_IPCOUNTRY"]) : 'xx';
		$post['board'] = $board_class->board['name'];
		$post['name'] = mb_substr($name, 0, KU_MAXNAMELENGTH);
		$post['name_save'] = true;
		$post['tripcode'] = $tripcode;
		$post['email'] = mb_substr($post_email, 0, KU_MAXEMAILLENGTH);
		// First array is the converted form of the japanese characters meaning sage, second meaning age
		$ords_email = unistr_to_ords($post_email);
		if (strtolower($_POST['em']) != 'sage' && $ords_email != array(19979, 12370) && strtolower($_POST['em']) != 'age' && $ords_email != array(19978, 12370) && $_POST['em'] != 'return' && $_POST['em'] != 'noko') {
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

		// $upload_class->file_name, $upload_class->original_file_name, $filetype_withoutdot, $upload_class->file_md5, $upload_class->imgWidth, $upload_class->imgHeight, $upload_class->file_size, $upload_class->imgWidth_thumb, $upload_class->imgHeight_thumb
		$post_class = new Post(0, $board_class->board['name'], $board_class->board['id'], true);
		$post_id = $post_class->Insert($thread_replyto, $post['name'], $post['tripcode'], $post['email'], $post['subject'], addslashes($post['message']), $upload_class->attachments, $post_passwordmd5, time(), time(), $posting_class->user_id, $user_authority_display, $sticky, $lock, $board_class->board['id'], $post['country'], $posting_class->is_new_user);

		if ($user_authority > 0 && $user_authority != 3) {
		  $modpost_message = 'Modposted #<a href="' . KU_BOARDSFOLDER . $board_class->board['name'] . '/res/';
		  if ($post_isreply) {
		    $modpost_message .= $thread_replyto;
		  } else {
		    $modpost_message .= $post_id;
		  }
		  $modpost_message .= '.html#' . $post_id . '">' . $post_id . '</a> in /'.$_POST['board'].'/ with flags: ' . $flags . '.';
		  management_addlogentry($modpost_message, 1, md5_decrypt($_POST['modpassword'], KU_RANDOMSEED));
		}

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

		// Determine the page from which post is getting bumbed →
		$startpage = -1;
		if ($thread_replyto != '0') {
			$threads = $tc_db->GetAll("SELECT `id` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0 ORDER BY `stickied` DESC, `bumped` DESC");
			$total_threads = count($threads);
			for ($i=0; $i < $total_threads; $i++) {
				$current_page = floor($i / KU_THREADS);
				if ($threads[$i]['id'] == $thread_replyto) {
					$startpage = $current_page;
				}
			}
		} // ← Determine the page from which post is getting bumbed

		// If the user replied to a thread, and they weren't sage-ing it and if the number of replies already in the thread are less than the maximum thread replies before perma-sage, Bump the thread
		if ($thread_replyto != '0' && strtolower($_POST['em']) != 'sage' && unistr_to_ords($_POST['em']) != array(19979, 12370) && $thread_replies <= $board_class->board['maxreplies']) {
			$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `bumped` = '" . time() . "' WHERE `boardid` = " . $board_class->board['id'] . " AND `id` = '" . $thread_replyto . "'");
		}

		// If the user replied to a thread he is watching, update it so it doesn't count his reply as unread
		if (KU_WATCHTHREADS && $thread_replyto != '0') {
			$viewing_thread_is_watched = $tc_db->GetOne("SELECT COUNT(*) FROM `" . KU_DBPREFIX . "watchedthreads` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' AND `board` = '" . $board_class->board['name'] . "' AND `threadid` = '" . $thread_replyto . "'");
			if ($viewing_thread_is_watched > 0) {
				$newestreplyid = $tc_db->GetOne('SELECT `id` FROM `'.KU_DBPREFIX.'posts` WHERE `boardid` = ' . $board_class->board['id'] . ' AND `IS_DELETED` = 0 AND `parentid` = '.$thread_replyto.' ORDER BY `id` DESC LIMIT 1');

				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "watchedthreads` SET `lastsawreplyid` = " . $newestreplyid . " WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' AND `board` = '" . $board_class->board['name'] . "' AND `threadid` = '" . $thread_replyto . "'");
			}
		}

		$tc_db->Execute("COMMIT");

		// Trim any threads which have been pushed past the limit, or exceed the maximum age limit
		TrimToPageLimit($board_class->board);

		// Regenerate board pages
		$board_class->RegeneratePages($startpage, strtolower($_POST['em']) != 'sage' ? 'up' : 'single');

		if ($thread_replyto == '0') {
			// Regenerate the thread
			$board_class->RegenerateThreads($post_id);
			notify($board_class->board['name'].':newthreads', $post_id);
		} else {
			// Regenerate the thread
			$board_class->RegenerateThreads($thread_replyto);
			notify($board_class->board['name'].':'.$thread_replyto);
		}
	} else {
		exitWithErrorPage(_gettext('Sorry, this board is locked and can not be posted in.'));
	}
}
elseif (
	(
		(
			isset($_POST['deletepost'])
			||
			isset($_POST['reportpost'])
		)
		&&
		isset($_POST['post'])
	)
	|| isset($_POST['delete-file'])
) {
	$ismod = false;

	if ($_POST['AJAX'])
		$items_affected = array();

	$threads_to_regenerate = array(); // to prevent possible repeated regeneration
	// $pages_to_regenerate = array(); // whether or not pages must be regenerated in the end
	$regenerate_all_pages = false;

	// Check rights
	$pass =( isset($_POST['postpassword']) && $_POST['postpassword']!="") ? md5($_POST['postpassword']) : null;
	$ismod = (
		$_POST['moddelete']
		&&
		(require_once KU_ROOTDIR . 'inc/classes/manage.class.php')
		&&
		Manage::CurrentUserIsModeratorOfBoard($board_class->board['name'], $_SESSION['manageusername'])
	);

	// Post-related actions
	if (isset($_POST['post']))
		foreach ($_POST['post'] as $val) {
			$post_class = new Post($val, $board_class->board['name'], $board_class->board['id']);
			$post_action = new PolymorphicReporter('post', $val, (boolean)$_POST['AJAX']);
			// Post reporting
			if (isset($_POST['reportpost'])) {
				$post_action->action = 'report';
				if ($board_class->board['enablereporting'] == 1) {
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
			// Post deleting
			if (isset($_POST['deletepost'])) {
				$post_action->action = 'delete';
				$isownpost = ($pass && $pass == $post_class->post['password']);
				if ($isownpost || $ismod) {
					$delres = $post_class->Delete();
					if ($delres) {
						if ($delres !== 'already_deleted') { // Skip the unneeded rebuild if the post is already deleted
							if ($post_class->post['parentid'] != '0') {
								$threads_to_regenerate []= $post_class->post['parentid'];
								if (! $isownpost) {
									management_addlogentry(_gettext('Deleted post') . ' #<a href="?action=viewthread&thread='. $post_class->post['parentid'] . '&board='. $board_class->board['name'] . '#'. $val . '">'. $val . '</a> - /'. $board_class->board['name'] . '/', 7);
								}
								$post_action->succ(_gettext('Post successfully deleted.').(!$isownpost ? _gettext('(By mod)') : ''));
							}
							else {
								if (! $isownpost) {
									management_addlogentry(_gettext('Deleted thread') . ' #<a href="?action=viewthread&thread='. $val . '&board='. $board_class->board['name'] . '">'. $val . '</a> ('. ($delres-1) . ' replies) - /'. $board_class->board['name'] . '/', 7);
								}
								$post_action->succ(_gettext('Thread successfully deleted.').(!$isownpost ? _gettext('(By mod)') : '').' '.($delres-1)._gettext('replies deleted').'.');
							}
							$regenerate_all_pages = true; // TODO: possibly optimize
						}
						else {
							$post_action->succ(_gettext('Post is already deleted.'));
						}
					}
					else {
						$post_action->fail(_gettext('There was an error in trying to delete your post'));
					}
				}
				else {
					$post_action->fail(_gettext('Incorrect password.'));
				}
			}
			$items_affected []= $post_action->report();
		}
	// File deleting
	if (isset($_POST['delete-file']))
		foreach($_POST['delete-file'] as $file) {
			$file_action = new PolymorphicReporter('file', $file, (boolean)$_POST['AJAX']);
			$file_action->action = 'delete-file';
			$fdres = $board_class->DeleteFile($file, $pass, $ismod, $board_class->board['name']);
			if ($fdres['error'])
				$file_action->fail($fdres['error']);
			else {
				$file_action->succ(_gettext('Image successfully deleted from your post.'));
				if (! $fdres['already_deleted']) {
					$threads_to_regenerate []= $fdres['parentid'];
					$regenerate_all_pages = true;//TODO: optimize.
				}
			}
			$items_affected []= $file_action->report();
		}
	// Regeneration
	$regenerated_threads = array();
	foreach($threads_to_regenerate as $id) {
		if (! in_array($id, $regenerated_threads)) {
			$board_class->RegenerateThreads($id);
			$regenerated_threads []= $id;
		}
	}
	if ($regenerate_all_pages) {
		$board_class->RegeneratePages();
	}
	/*else {
		$regenerated_pages = array();
		foreach($pages_to_regenerate as $page) {
			if (! in_array($page, $regenerated_pages)) {
				$board_class->RegeneratePages($page, 'single');
				$regenerated_pages []= $page;
			}
		}
	}*/
	// Finish
	if ($_POST['AJAX'])
		exit(json_encode(array(
		  'action' => 'multi_post_action',
		  'data' => $items_affected
		)));
	else
		do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/');
	die();
}
else {
	error_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/', _gettext('Unspecified action'));
}

if (KU_RSS) {
	require_once KU_ROOTDIR . 'inc/classes/rss.class.php';
	$rss_class = new RSS();

	print_page(KU_BOARDSDIR.$_POST['board'].'/rss.xml',$rss_class->GenerateRSS($_POST['board'], $board_class->board['id']),$_POST['board']);
}

if( $_POST['redirecttothread'] == 1 || $_POST['em'] == 'return' || $_POST['em'] == 'noko') {
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
	)));
}