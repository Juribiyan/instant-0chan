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
 * Manage Class
 * +------------------------------------------------------------------------------+
 * Manage functions, along with the pages available
 * +------------------------------------------------------------------------------+
 */
class Manage {

	/* Show the header of the manage page */
	function Header() {
		global $smarty, $tpl_page;

		if (is_file(KU_ROOTDIR . 'inc/pages/modheader.html')) {
			$tpl_includeheader = file_get_contents(KU_ROOTDIR . 'inc/pages/modheader.html');
		} else {
			$tpl_includeheader = '';
		}

		$smarty->assign('includeheader', $tpl_includeheader);
	}

	/* Show the footer of the manage page */
	function Footer() {
		global $smarty, $tpl_page;

		$smarty->assign('page', $tpl_page);

		$board_class = new Board('');
		// if(!isset($smarty->footer)) {
		// 	$smarty->footer = '';
		// }
		$smarty->display('manage.tpl');
	}

	// Creates a salt to be used for passwords
	function CreateSalt() {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$salt = '';

		for ($i = 0; $i < 3; ++$i) {
			$salt .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $salt;
	}

	/* Validate the current session */
	function ValidateSession($is_menu = false) {
		global $tc_db, $tpl_page;

		if (isset($_SESSION['manageusername']) && isset($_SESSION['managepassword'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `username` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . " AND `password` = " . $tc_db->qstr($_SESSION['managepassword']) . " LIMIT 1");
			if (count($results) == 0) {
				session_destroy();
				exitWithErrorPage(_gettext('Invalid session.'), '<a href="manage_page.php">'. _gettext('Log in again.') . '</a>');
			}

			$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `lastactive` = " . time() . " WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']));

			return true;
		}
		else {
			if (!$is_menu) {
				if (isset($_POST['AJAX']) && $_POST['AJAX'])
					exitWithErrorPage(_gettext('Invalid session.'), '<a href="manage_page.php">'. _gettext('Log in again.') . '</a>');
				$this->LoginForm();
				die($tpl_page);
			}
			else {
				return false;
			}
		}
	}

	/* Show the login form and halt execution */
	function LoginForm() {
		global $tc_db, $tpl_page;

		if (file_exists(KU_ROOTDIR . 'inc/pages/manage_login.html')) {
			$tpl_page .= file_get_contents(KU_ROOTDIR . 'inc/pages/manage_login.html');
		}
	}

	/* Check login names and create session if user/pass is correct */
	function CheckLogin() {
		global $tc_db, $action;

		$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "loginattempts` WHERE `timestamp` < '" . (time() - 1200) . "'");
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `ip` FROM `" . KU_DBPREFIX . "loginattempts` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' LIMIT 6");
		if (count($results) > 5) {
			exitWithErrorPage(_gettext('System lockout'), _gettext('Sorry, because of your numerous failed logins, you have been locked out from logging in for 20 minutes. Please wait and then try again.'));
		} else {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `username`, `password`, `salt` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_POST['username']) . " LIMIT 1");
			if (count($results) > 0) {
				if (empty($results[0]['salt'])) {
					if (md5($_POST['password']) == $results[0]['password']) {
						$salt = $this->CreateSalt();
						$tc_db->Execute("UPDATE `" .KU_DBPREFIX. "staff` SET salt = '" .$salt. "' WHERE username = " .$tc_db->qstr($_POST['username']));
						$newpass = md5($_POST['password'] . $salt);
						$tc_db->Execute("UPDATE `" .KU_DBPREFIX. "staff` SET password = '" .$newpass. "' WHERE username = " .$tc_db->qstr($_POST['username']));
						$_SESSION['manageusername'] = $_POST['username'];
						$_SESSION['managepassword'] = $newpass;
            			$_SESSION['token'] = md5($_SESSION['manageusername'] . $_SESSION['managepassword'] . rand(0,100));
						$this->SetModerationCookies();
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "loginattempts` WHERE `ip` < '" . $_SERVER['REMOTE_ADDR'] . "'");
						$action = 'posting_rates';
						management_addlogentry(_gettext('Logged in'), 1);
						die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
					} else {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
						exitWithErrorPage(_gettext('Incorrect username/password.'));
					}
				} else {
					if (md5($_POST['password'] . $results[0]['salt']) == $results[0]['password']) {
						$_SESSION['manageusername'] = $_POST['username'];
						$_SESSION['managepassword'] = md5($_POST['password'] . $results[0]['salt']);
            $_SESSION['token'] = md5($_SESSION['manageusername'] . $_SESSION['managepassword'] . rand(0,100));
						$this->SetModerationCookies();
						$action = 'posting_rates';
						management_addlogentry(_gettext('Logged in'), 1);
						die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
					} else {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
						exitWithErrorPage(_gettext('Incorrect username/password.'));
					}
				}
			} else {
				$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
				exitWithErrorPage(_gettext('Incorrect username/password.'));
			}
		}
	}

	/* Set mod cookies for boards */
	function SetModerationCookies() {
		global $tc_db, $tpl_page;
		if (isset($_SESSION['manageusername'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . " LIMIT 1");
			if ($this->CurrentUserIsAdministrator() || $results[0][0] == 'allboards') {
				setcookie("kumod", "allboards", time() + 3600, KU_BOARDSFOLDER, KU_DOMAIN);
			} else {
				if ($results[0][0] != '') {
					setcookie("kumod", $results[0][0], time() + 3600, KU_BOARDSFOLDER, KU_DOMAIN);
				}
			}
		}
	}

	function GetToken() {
		if ($this->CurrentUserIsAdministrator()) {
			echo $_SESSION['token'];
			exit;
		} else {
			echo "false";
			exit;
		}
	}

  function CheckToken($posttoken) {
    if ($posttoken != $_SESSION['token']) {
      // Something is strange
      session_destroy();
      exitWithErrorPage(_gettext('Invalid Token'));
    }
  }

	/* Log current user out */
	function Logout() {
		global $tc_db, $tpl_page;

		setcookie('kumod', '', time() - 3600, KU_BOARDSFOLDER, KU_DOMAIN);

		session_destroy();
		unset($_SESSION['manageusername']);
		unset($_SESSION['managepassword']);
    unset($_SESSION['token']);
		die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
	}

		/* If the user logged in isn't an admin, kill the script */
	function AdministratorsOnly() {
		global $tc_db, $tpl_page;

		if (!$this->CurrentUserIsAdministrator()) {
			exitWithErrorPage('That page is for admins only.');
		}
	}

	/* If the user logged in isn't an moderator or higher, kill the script */
	function ModeratorsOnly() {
		global $tc_db, $tpl_page;

		if ($this->CurrentUserIsAdministrator()) {
			return true;
		} else {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
			foreach ($results as $line) {
				if ($line['type'] != 2 && $line['type'] != 3) {
					exitWithErrorPage(_gettext('That page is for moderators and administrators only.'));
				}
			}
		}
	}

	function BoardOwnersOnly() {
		global $tc_db, $tpl_page;

		if ($this->CurrentUserIsAdministrator()) {
			return true;
		} else {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
			foreach ($results as $line) {
				if ($line['type'] != 3) {
					exitWithErrorPage(_gettext('That page is for custom board owners only.'));
				}
			}
		}
	}

	/* See if the user logged in is an admin */
	function CurrentUserIsAdministrator() {
		global $tc_db, $tpl_page;

		if ($_SESSION['manageusername'] == '' || $_SESSION['managepassword'] == '' || $_SESSION['token'] == '') {
			$_SESSION['manageusername'] = '';
			$_SESSION['managepassword'] = '';
			$_SESSION['token'] = '';
			return false;
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
		foreach ($results as $line) {
			if ($line['type'] == 1) {
				return true;
			} else {
				return false;
			}
		}

		/* If the function reaches this point, something is fishy. Kill their session */
		session_destroy();
		exitWithErrorPage(_gettext('Invalid session, please log in again.'));
	}

	/* See if the user logged in is a moderator */
	function CurrentUserIsModerator() {
		global $tc_db, $tpl_page;

		if ($_SESSION['manageusername'] == '' || $_SESSION['managepassword'] == '' || $_SESSION['token'] == '') {
			$_SESSION['manageusername'] = '';
			$_SESSION['managepassword'] = '';
			$_SESSION['token'] = '';
			return false;
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
		foreach ($results as $line) {
			if ($line['type'] == 2) {
				return true;
			} else {
				return false;
			}
		}

		/* If the function reaches this point, something is fishy. Kill their session */
		session_destroy();
		exitWithErrorPage(_gettext('Invalid session, please log in again.'));
	}

	function CurrentUserIsBoardOwner() {
		global $tc_db, $tpl_page;

		if ($_SESSION['manageusername'] == '' || $_SESSION['managepassword'] == '' || $_SESSION['token'] == '') {
			$_SESSION['manageusername'] = '';
			$_SESSION['managepassword'] = '';
			$_SESSION['token'] = '';
			return false;
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
		foreach ($results as $line) {
			if ($line['type'] == 3) {
				return true;
			} else {
				return false;
			}
		}

		/* If the function reaches this point, something is fishy. Kill their session */
		session_destroy();
		exitWithErrorPage(_gettext('Invalid session, please log in again.'));
	}

	/* See if the user logged in is a moderator of a specified board */
	public static function CurrentUserIsModeratorOfBoard($board, $username) {
		global $tc_db, $tpl_page;

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type`, `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $username . "' LIMIT 1");
		if (count($results) > 0) {
			foreach ($results as $line) {
				if ($line['boards'] == 'allboards') {
					return true;
				} else {
					if ($line['type'] == '1') {
						return true;
					} else {
						$array_boards = explode('|', $line['boards']);
						if (in_array($board, $array_boards)) {
							return true;
						} else {
							return false;
						}
					}
				}
			}
		} else {
			return false;
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Manage pages
	* +------------------------------------------------------------------------------+
	*/


	/*
	* +------------------------------------------------------------------------------+
	* Home Pages
	* +------------------------------------------------------------------------------+
	*/

	/* View Announcements */
	function announcements() {
		global $tc_db, $tpl_page;
		$this->ModeratorsOnly();

		$tpl_page .= '<h1><center>'. _gettext('Announcements') .'</center></h1>'. "\n";

		$entries = 0;
		/* Get all of the announcements, ordered with the newest one placed on top */
		$results = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."announcements` ORDER BY `postedat` DESC");
		foreach($results AS $line) {
			$entries++;
			$tpl_page .= '<h2>'.stripslashes($line['subject']).' '. _gettext('by') .' ';
			$tpl_page .= stripslashes($line['postedby']);
			$tpl_page .= ' - '.date("n/j/y @ g:iA T", $line['postedat']);
			$tpl_page .= '</h2>' .
						'<p>'. stripslashes($line['message']) . '</p>';
		}
	}

	function posting_rates() {
		global $tc_db, $tpl_page;

		$tpl_page .= '<h2>'. _gettext('Posting rates (past hour)') . '</h2><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table border="1" cellspacing="2" cellpadding="2" width="100%"><tr><th>'. _gettext('Board') . '</th><th>'. _gettext('Threads') . '</th><th>'. _gettext('Replies') . '</th><th>'. _gettext('Posts') . '</th></tr>';
			foreach ($results as $line) {
				$rows_threads = $tc_db->GetOne("SELECT HIGH_PRIORITY count(id) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line['id'] . " AND `parentid` = 0 AND `timestamp` >= " . (time() - 3600));
				$rows_replies = $tc_db->GetOne("SELECT HIGH_PRIORITY count(id) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line['id'] . " AND `parentid` != 0 AND `timestamp` >= " . (time() - 3600));
				$rows_posts = $rows_threads + $rows_replies;
				$threads_perminute = $rows_threads;
				$replies_perminute = $rows_replies;
				$posts_perminute = $rows_posts;
				$tpl_page .= '<tr><td><strong><a href="'. KU_WEBFOLDER . $line['name'] . '">'. $line['name'] . '</a></strong></td><td>'. $threads_perminute . '</td><td>'. $replies_perminute . '</td><td>'. $posts_perminute . '</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= _gettext('No boards');
		}
	}

	function statistics() {
		global $tc_db, $tpl_page;

		$tpl_page .= '<h2>'. _gettext('Statistics') .'</h2><br />';
		$tpl_page .= '<img src="manage_page.php?graph&type=day" />
		<img src="manage_page.php?graph&type=week" />
		<img src="manage_page.php?graph&type=postnum" />
		<img src="manage_page.php?graph&type=unique" />
		<img src="manage_page.php?graph&type=posttime" />';
	}

	function changepwd() {
		global $tc_db, $tpl_page;

		$tpl_page .= '<h2>'. _gettext('Change account password') . '</h2><br />';
		if (isset($_POST['oldpwd']) && isset($_POST['newpwd']) && isset($_POST['newpwd2'])) {
      $this->CheckToken($_POST['token']);
			if ($_POST['oldpwd'] != '' && $_POST['newpwd'] != '' && $_POST['newpwd2'] != '') {
				if ($_POST['newpwd'] == $_POST['newpwd2']) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . "");
					foreach ($results as $line) {
						$staff_passwordenc = $line['password'];
						$staff_salt = $line['salt'];
					}
					if (md5($_POST['oldpwd'].$staff_salt) == $staff_passwordenc) {
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `password` = '" . md5($_POST['newpwd'].$staff_salt) . "' WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . "");
						$_SESSION['managepassword'] = md5($_POST['newpwd'].$staff_salt);
						$tpl_page .= _gettext('Password successfully changed.');
					} else {
						$tpl_page .= _gettext('The old password you provided did not match the current one.');
					}
				} else {
					$tpl_page .= _gettext('The second password did not match the first.');
				}
			} else {
				$tpl_page .= _gettext('Please fill in all required fields.');
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= '<form action="manage_page.php?action=changepwd" method="post">
    <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<label for="oldpwd">'. _gettext('Old password') . ':</label>
		<input type="password" name="oldpwd" /><br />

		<label for="newpwd">'. _gettext('New password') . ':</label>
		<input type="password" name="newpwd" /><br />

		<label for="newpwd2">'. _gettext('New password again') . ':</label>
		<input type="password" name="newpwd2" /><br />

		<input type="submit" value="' ._gettext('Change account password') . '" />

		</form>';
	}

	/*
	* +------------------------------------------------------------------------------+
	* Site Administration Pages
	* +------------------------------------------------------------------------------+
	*/

	function addannouncement() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$disptable = true; $formval = 'add'; $title = _gettext('Announcement Management');
		if(isset($_GET['act'])) {
			if ($_GET['act'] == 'edit') {
				if (isset($_POST['announcement'])) {
          $this->CheckToken($_POST['token']);
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "announcements` SET `subject` = " . $tc_db->qstr($_POST['subject']) . ", `message` = " . $tc_db->qstr($_POST['announcement']) . " WHERE `id` = " . $tc_db->qstr($_GET['id']));
					$tpl_page .= '<hr /><h3>'. _gettext('Announcement edited') .'</h3><hr />';
					management_addlogentry(_gettext('Edited an announcement'), 9);
				}
				$formval = 'edit&amp;id='. $_GET['id']; $title .= ' - Edit';
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "announcements` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$values = $results[0]; $disptable = false;
			} elseif ($_GET['act'] == 'del') {
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "announcements` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$tpl_page .= '<hr /><h3>'. _gettext('Announcement successfully deleted') .'</h3><hr />';
				management_addlogentry(_gettext('Deleted an announcement'), 9);
			} elseif ($_GET['act'] == 'add' && isset($_POST['announcement']) && isset($_POST['subject'])) {
				if (!empty($_POST['announcement']) && !empty($_POST['subject'])) {
					$tpl_page .= '<hr />';
					$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "announcements` ( `subject` , `message` , `postedat` , `postedby` ) VALUES ( " . $tc_db->qstr($_POST['subject']) . " , " . $tc_db->qstr($_POST['announcement']) . " , '" . time() . "' , " . $tc_db->qstr($_SESSION['manageusername']) . " )");
					$tpl_page .= '<h3>'. _gettext('Announcement successfully added.') . '</h3>';
					management_addlogentry(_gettext('Added an announcement'), 9);
					$tpl_page .= '<hr />';
				} else {
					$tpl_page .= '<hr />'. _gettext('You must enter a subject as well as a post.') .'<hr />';
				}
			}
		}
		$tpl_page .= '<h2>'. $title . '</h2><br />
			<form method="post" action="?action=addannouncement&amp;act='. $formval . '">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="subject">'. _gettext('Subject') . ':</label>
			<input type="text" id="subject" name="subject" value="'. (isset($values['subject']) ? $values['subject'] : '') . '" />
			<div class="desc">'. _gettext('Can not be left blank') . '</div><br />
			<label for="announcement">'. _gettext('Post') . ':</label>
			<textarea id="announcement" name="announcement" rows="25" cols="80">' . (isset($values['message']) ? htmlspecialchars($values['message']) : '')  . '</textarea><br />
			<input type="submit" value="'. _gettext('Add') . '" />
			</form>';
		if ($disptable) {
			$tpl_page .= '<br /><hr /><h1>'. _gettext('Edit/Delete announcement') .'</h1>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "announcements` ORDER BY `id` DESC");
			if (count($results) > 0) {
				$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Date Added') .'</th><th>'. _gettext('Subject') .'</th><th>'. _gettext('Message') .'</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>'. date('F j, Y, g:i a', $line['postedat']) . '</td><td>'. $line['subject'] . '</td><td>'. $line['message'] . '</td><td>[<a href="?action=addannouncement&amp;act=edit&amp;id='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=addannouncement&amp;act=del&amp;id='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
				}
				$tpl_page .= '</table>';
			} else {
				$tpl_page .= _gettext('No announcements yet.');
			}
		}
	}

	/* Edit Smarty templates */
	function templates() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$files = array();

		$tpl_page .= '<h2>'. _gettext('Template editor') .'</h2><br />';
		if ($dh = opendir(KU_TEMPLATEDIR)) {
			while (($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..')
				$files[] = $file;
			}
			closedir($dh);
		}
		sort($files);

		if(isset($_POST['templatedata']) && isset($_POST['template'])) {
      $this->CheckToken($_POST['token']);
			$file = basename($_POST['template']);
			if (in_array($file, $files)) {
				if(file_exists(KU_TEMPLATEDIR . '/'. $file)) {
					file_put_contents(KU_TEMPLATEDIR . '/'. $file, $_POST['templatedata']);
					$tpl_page .= '<hr /><h3>'. _gettext('Template edited') .'</h3><hr />';
					if (isset($_POST['rebuild'])) {
						$this->rebuildall(true);
					}
					unset($_POST['template']);
					unset($_POST['templatedata']);
				}
			}
		}

		if(!isset($_POST['templatedata']) && !isset($_POST['template'])) {
			$tpl_page .= '<form method="post" action="?action=templates">
			<label for="template">' ._gettext('Template'). ':</label>
			<select name="template" id="template">';
			foreach($files as $template) {
				$tpl_page .='<option name="'. $template .'">'. $template . '</option>';
			}
			$tpl_page .= '</select>';

		}

			if(!isset($_POST['templatedata']) && isset($_POST['template'])) {
			$file = basename($_POST['template']);
			if (in_array($file, $files)) {
				if(file_exists(KU_TEMPLATEDIR . '/'. $file)) {
								$tpl_page .= '<form method="post" action="?action=templates">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<input type="hidden" name="template" value="'. $file .'" />
					<textarea wrap=off rows=40 cols=100 name="templatedata">'. htmlspecialchars(file_get_contents(KU_TEMPLATEDIR . '/'. $file)) . '</textarea>
					<label for="rebuild">'. _gettext('Rebuild HTML after edit?') .'</label>
					<input type="checkbox" name="rebuild" /><br /><br />
					<div class="desc">'. _gettext('Visit <a href="https://smarty-php.github.io/smarty/4.x/">https://smarty-php.github.io/smarty/4.x/</a> for syntax information.') . '</div>
					<div class="desc">'. sprintf(_gettext('To access Kusaba variables, use {$smarty.const.KU_VARNAME}, for example {$smarty.const.KU_BOARDSPATH} would be replaced with %s'), KU_BOARDSPATH) . '</div>
					<div class="desc">'. _gettext('Enclose text in {t}{/t} blocks to allow them to be translated for different languages.') . '</div><br /><br />';
				}
			}
				}

		$tpl_page .= '<input type="submit" value="' ._gettext('Edit') . '" /></form>';
	}

	/* Add, edit, delete, and view news entries */
	function news() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$disptable = true; $formval = 'add'; $title = _gettext('News Management');
		if(isset($_GET['act'])) {
			if ($_GET['act'] == 'edit') {
				if (isset($_POST['news'])) {
          $this->CheckToken($_POST['token']);
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "front` SET `subject` = " . $tc_db->qstr($_POST['subject']) . ", `message` = " . $tc_db->qstr($_POST['news']) . ", `email` = " . $tc_db->qstr($_POST['email']) . " WHERE `id` = " . $tc_db->qstr($_GET['id']) . " AND `page` = 0");
					$tpl_page .= '<hr /><h3>'. _gettext('News post edited') .'</h3><hr />';
					management_addlogentry(_gettext('Edited a news entry'), 9);
				}
				$formval = 'edit&amp;id='. $_GET['id']; $title .= ' - Edit';
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "front` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$values = $results[0];
				$disptable = false;
			} elseif ($_GET['act'] == 'del') {
				$results = $tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "front` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$tpl_page .= '<hr /><h3>'. _gettext('News post deleted') .'</h3><hr />';
				management_addlogentry(_gettext('Deleted a news entry'), 9);
			} elseif ($_GET['act'] == 'add') {
				if (isset($_POST['news']) && isset($_POST['subject']) && isset($_POST['email'])) {
					if (!empty($_POST['news']) || !empty($_POST['subject'])) {
            $this->CheckToken($_POST['token']);
						$tpl_page .= '<hr />';
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "front` ( `page`, `subject` , `message` , `timestamp` , `poster` , `email` ) VALUES ( '0', " . $tc_db->qstr($_POST['subject']) . " , " . $tc_db->qstr($_POST['news']) . " , '" . time() . "' , " . $tc_db->qstr($_SESSION['manageusername']) . " , " . $tc_db->qstr($_POST['email']) . " )");
						$tpl_page .= '<h3>'. _gettext('News entry successfully added.') . '</h3>';
						management_addlogentry(_gettext('Added a news entry'), 9);
						$tpl_page .= '<hr />';
					} else {
						$tpl_page .= '<hr />'. _gettext('You must enter a subject as well as a post.') .'<hr />';
					}
				}
			}
		}
		$tpl_page .= '<h2>'. $title . '</h2><br />
			<form method="post" action="?action=news&amp;act='. $formval . '">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="subject">'. _gettext('Subject') . ':</label>
			<input type="text" id="subject" name="subject" value="'. (isset($values['subject']) ? $values['subject'] : '') . '" />
			<div class="desc">'. _gettext('Can not be left blank.') . '</div><br />
			<label for="news"> '. _gettext('Post') . ':</label>
			<textarea id="news" name="news" rows="25" cols="80">' . (isset($values['message']) ? htmlspecialchars($values['message']) : '') . '</textarea><br /><br />
			<label for="email">'. _gettext('E-mail') . ':</label>
			<input type="text" id="email" name="email" value="'. (isset($values['postedemail']) ? $values['postedemail'] : '') . '" />
			<div class="desc">'. _gettext('Can be left blank.') . '</div><br />
			<input type="submit" value="'. _gettext('Add') . '" />
			</form>';
		if ($disptable) {
			$tpl_page .= '<br /><hr /><h1>'. _gettext('Edit/Delete News') .'</h1>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "front` WHERE `page` = 0 ORDER BY `timestamp` DESC");
			if (count($results) > 0) {
				$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Date Added') .'</th><th>'. _gettext('Subject') .'</th><th>'. _gettext('Message') .'</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>'. date('F j, Y, g:i a', $line['timestamp']) . '</td><td>'. $line['subject'] . '</td><td>'. $line['message'] . '</td><td>[<a href="?action=news&amp;act=edit&amp;id='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=news&amp;act=del&amp;id='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
				}
				$tpl_page .= '</table>';
			} else {
				$tpl_page .= _gettext('No news posts yet.');
			}
		}
	}

	/* Add, edit, or delete FAQ entries */
	function faq() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$disptable = true; $formval = 'add'; $title = _gettext('FAQ Management');
		if(isset($_GET['act'])) {
			if ($_GET['act'] == 'edit') {
				if (isset($_POST['faq'])) {
          $this->CheckToken($_POST['token']);
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "front` SET `subject` = " . $tc_db->qstr($_POST['heading']) . ", `message` = " . $tc_db->qstr($_POST['faq']) . ", `order` = " . intval($_POST['order']) . " WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
					$tpl_page .= '<hr /><h3>'. _gettext('FAQ entry edited') .'</h3><hr />';
					management_addlogentry(_gettext('Edited a FAQ entry'), 9);
				}
				$formval = 'edit&amp;id='. $_GET['id']; $title .= ' - Edit';
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "front` WHERE `id` = " . $tc_db->qstr($_GET['id']));
				$values = $results[0];
				$disptable = false;
			} elseif ($_GET['act'] == 'del') {
				$results = $tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "front` WHERE `id` = " . $tc_db->qstr($_GET['id']));
				$tpl_page .= '<hr /><h3>'. _gettext('FAQ entry deleted') .'</h3><hr />';
				management_addlogentry(_gettext('Deleted a FAQ entry'), 9);
			} elseif ($_GET['act'] == 'add') {
				if (isset($_POST['faq']) && isset($_POST['heading']) && isset($_POST['order'])) {
					if (!empty($_POST['faq']) || !empty($_POST['heading'])) {
            $this->CheckToken($_POST['token']);
						$tpl_page .= '<hr />';
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "front` ( `page`, `subject` , `message` , `order` ) VALUES ( '1', " . $tc_db->qstr($_POST['heading']) . " , " . intval($_POST['faq']) . " , " . intval($_POST['order']) . " )");
						$tpl_page .= '<h3>'. _gettext('FAQ entry successfully added.') . '</h3>';
						management_addlogentry(_gettext('Added a FAQ entry'), 9);
						$tpl_page .= '<hr />';
					} else {
						$tpl_page .= '<hr />'. _gettext('You must enter a heading as well as a post.') .'<hr />';
					}
				}
			}
		}
		$tpl_page .= '<h2>'. $title . '</h2><br />
			<form method="post" action="?action=faq&amp;act='. $formval . '">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="heading">'. _gettext('Heading') . ':</label>
			<input type="text" id="heading" name="heading" value="'. (isset($values['subject']) ? $values['subject'] : '') . '" />
			<div class="desc">'. _gettext('Can not be left blank.') . '</div><br />
			<label for="faq"> '. _gettext('Post') . ':</label>
			<textarea id="faq" name="faq" rows="25" cols="80">' . (isset($values['message']) ? htmlspecialchars($values['message']) : '') . '</textarea><br /><br />
			<label for="order">'. _gettext('Order') . ':</label>
			<input type="text" id="order" name="order" value="'	. (isset($values['order']) ? $values['order'] : '') . '" />
			<div class="desc">'. _gettext('This can be left blank, however it will appear at the very top of the list') . '</div><br />
			<input type="submit" value="'. _gettext('Add') . '" />
			</form>';
		if ($disptable) {
			$tpl_page .= '<br /><hr /><h1>'. _gettext('Edit/Delete FAQ Entries') .'</h1>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "front` WHERE `page` = 1 ORDER BY `order` ASC");
			if (count($results) > 0) {
				$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Order') .'</th><th>'. _gettext('Heading') .'</th><th>'. _gettext('Message') .'</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>'. $line['order'] . '</td><td>'. $line['subject'] . '</td><td>'. $line['message'] . '</td><td>[<a href="?action=faq&amp;act=edit&amp;id='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=faq&amp;act=del&id='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
				}
				$tpl_page .= '</table>';
			} else {
				$tpl_page .= _gettext('No FAQ entries yet.');
			}
		}
	}

	/* Add, edit, or delete Rules Entries */
	function rules() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$disptable = true; $formval = 'add'; $title = _gettext('Rules Management');
		if(isset($_GET['act'])) {
			if ($_GET['act'] == 'edit') {
				if (isset($_POST['rules'])) {
          $this->CheckToken($_POST['token']);
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "front` SET `subject` = " . $tc_db->qstr($_POST['heading']) . ", `message` = " . $tc_db->qstr($_POST['rules']) . ", `order` = " . intval($_POST['order']) . " WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
					$tpl_page .= '<hr /><h3>'. _gettext('Rules entry edited') .'</h3><hr />';
					management_addlogentry(_gettext('Edited a Rule entry'), 9);
				}
				$formval = 'edit&amp;id='. $_GET['id']; $title .= ' - Edit';
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "front` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$values = $results[0];
				$disptable = false;
			} elseif ($_GET['act'] == 'del') {
				$results = $tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "front` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$tpl_page .= '<hr /><h3>'. _gettext('Rule entry deleted') .'</h3><hr />';
				management_addlogentry(_gettext('Deleted a Rules entry'), 9);
			} elseif ($_GET['act'] == 'add') {
				if (isset($_POST['rules']) && isset($_POST['heading']) && isset($_POST['order'])) {
					if (!empty($_POST['rules']) || !empty($_POST['heading'])) {
            $this->CheckToken($_POST['token']);
						$tpl_page .= '<hr />';
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "front` ( `page`, `subject` , `message` , `order` ) VALUES ( '2', " . $tc_db->qstr($_POST['heading']) . " , " . $tc_db->qstr($_POST['rules']) . " , " . intval($_POST['order']) . " )");
						$tpl_page .= '<h3>'. _gettext('Rules entry successfully added.') . '</h3>';
						management_addlogentry(_gettext('Added a Rule entry'), 9);
						$tpl_page .= '<hr />';
					} else {
						$tpl_page .= '<hr />'. _gettext('You must enter a heading as well as a post.') .'<hr />';
					}
				}
			}
		}
		$tpl_page .= '<h2>'. $title . '</h2><br />
			<form method="post" action="?action=rules&amp;act='. $formval . '">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="heading">'. _gettext('Heading') . ':</label>
			<input type="text" id="heading" name="heading" value="'. (isset($values['subject']) ? $values['subject'] : '') . '" />
			<div class="desc">'. _gettext('Can not be left blank.') . '</div><br />
			<label for="rules"> '. _gettext('Post') . ':</label>
			<textarea id="rules" name="rules" rows="25" cols="80">' . (isset($values['message']) ? htmlspecialchars($values['message']) : '') . '</textarea><br /><br />
			<label for="order">'. _gettext('Order') . ':</label>
			<input type="text" id="order" name="order" value="'	. (isset($values['order']) ? $values['order'] : '') . '" />
			<div class="desc">'. _gettext('This can be left blank, however it will appear at the very top of the list') . '</div><br />
			<input type="submit" value="' . _gettext('Submit') . '" />
			</form>';
		if ($disptable) {
			$tpl_page .= '<br /><hr /><h1>'. _gettext('Edit/Delete Rule Entries') .'</h1>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "front` WHERE `page` = 2 ORDER BY `order` ASC");
			if (count($results) > 0) {
				$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Order') .'</th><th>'. _gettext('Heading') .'</th><th>'. _gettext('Message') .'</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>'. $line['order'] . '</td><td>'. $line['subject'] . '</td><td>'. $line['message'] . '</td><td>[<a href="?action=rules&amp;act=edit&amp;id='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=rules&amp;act=del&id='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
				}
				$tpl_page .= '</table>';
			} else {
				$tpl_page .= _gettext('No Rule entries yet.');
			}
		}
	}

	function blotter() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		if (!KU_BLOTTER) exitWithErrorPage(_gettext('Blotter is disabled'));
		$tpl_page .= '<h2>' ._gettext('Blotter'). '</h2><br />';
		$act = 'add'; $values = array();
		if (isset($_GET['act'])) {
			switch($_GET['act']) {
				case 'add':
					if (isset($_POST['message'])) {
            $this->CheckToken($_POST['token']);
						$important = (isset($_POST['important'])) ? 1 : 0;
						$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "blotter` (`at`, `message`, `important`) VALUES ('" . time() . "', " . $tc_db->qstr($_POST['message']) . ", '" . $important . "')");
						$tpl_page .= '<h3>'. _gettext('Blotter entry added.') . '</h3>';
						clearBlotterCache();
					}
					break;
				case 'del':
					if (is_numeric($_GET['id'])) {
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "blotter` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
						$tpl_page .= '<hr /><h3>'. _gettext('Blotter entry deleted.') . '</h3><hr />';
						clearBlotterCache();
					} else {
						exitWithErrorPage(_gettext('Invalid ID'));
					}
					break;
				case 'edit':
					if (is_numeric($_GET['id'])) {
						$act = 'edit&amp;id=' .$_GET['id'];
						if (isset($_POST['message'])) {
              $this->CheckToken($_POST['token']);
							$important = (isset($_POST['important'])) ? 1 : 0;
							$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "blotter` SET `message` = " . $tc_db->qstr($_POST['message']) . ", `important` = '" . $important . "' WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
							$tpl_page .= '<h3>'. _gettext('Blotter entry updated.') . '</h3>';
							clearBlotterCache();
						}
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "blotter` WHERE `id` = " . $tc_db->qstr($_GET['id']) . " LIMIT 1");
						$values = $results[0];
					} else {
						exitWithErrorPage(_gettext('Invalid ID'));
					}
					break;
				default:
					exitWithErrorPage(_gettext('Invalid value for \'act\''));
					break;
			}
		}

		$tpl_page .= '<form action="?action=blotter&amp;act=' .$act. '" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="message">' ._gettext('Message'). ':</label>
					<input type="text" id="message" name="message" value="' .(isset($values['message']) ? $values['message'] : ''). '" size="75" /><br />
					<label for="important">' ._gettext('Important'). ':</label>
					<input type="checkbox" id="important" name="important" ';
		if (isset($values['important']) && $values['important'] == 1) $tpl_page .= 'checked="checked" ';
		$tpl_page .= '/><br />
					<input type="submit" value="' ._gettext('Submit'). '" /><br />
					</form><br /><br />';

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "blotter` ORDER BY `id` DESC");
		if (count($results) > 0) {
			$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('At') . '</th><th>'. _gettext('Message') . '</th><th>'. _gettext('Important') . '</th><th>&nbsp;</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. date('m/d/y', $line['at']) . '</td><td>'. $line['message'] . '</td><td>';
				$tpl_page .= ($line['important'] == 1) ? _gettext('Yes') : _gettext('No');
				$tpl_page .= '</td><td>[<a href="?action=blotter&amp;act=edit&amp;id='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=blotter&amp;act=del&amp;id='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= _gettext('No blotter entries');
		}
	}

	/* Display disk space used per board, and finally total in a large table */
	function spaceused() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Disk space used') . '</h2><br />';
		$spaceused_res = 0;
		$spaceused_src = 0;
		$spaceused_thumb = 0;
		$spaceused_total = 0;
		$files_res = 0;
		$files_src = 0;
		$files_thumb = 0;
		$files_total = 0;
		$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Board') .'</th><th>'. _gettext('Area') .'</th><th>'. _gettext('Files') .'</th><th>'. _gettext('Space Used') .'</th></tr>';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `name` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		foreach ($results as $line) {
			list($spaceused_board_res, $files_board_res) = recursive_directory_size(KU_BOARDSDIR . $line['name'] . '/res');
			list($spaceused_board_src, $files_board_src) = recursive_directory_size(KU_BOARDSDIR . $line['name'] . '/src');
			list($spaceused_board_thumb, $files_board_thumb) = recursive_directory_size(KU_BOARDSDIR . $line['name'] . '/thumb');

			$spaceused_board_total = $spaceused_board_res + $spaceused_board_src + $spaceused_board_thumb;
			$files_board_total = $files_board_res + $files_board_src + $files_board_thumb;

			$spaceused_res += $spaceused_board_res;
			$files_res += $files_board_res;

			$spaceused_src += $spaceused_board_src;
			$files_src += $files_board_src;

			$spaceused_thumb += $spaceused_board_thumb;
			$files_thumb += $files_board_thumb;

			$spaceused_total += $spaceused_board_total;
			$files_total += $files_board_total;

			$tpl_page .= '<tr><td rowspan="4">/'.$line['name'].'/</td><td>res/</td><td>'. number_format($files_board_res) . '</td><td>'. ConvertBytes($spaceused_board_res) . '</td></tr>';
			$tpl_page .= '<tr><td>src/</td><td>'. number_format($files_board_src) . '</td><td>'. ConvertBytes($spaceused_board_src) . '</td></tr>';
			$tpl_page .= '<tr><td>thumb/</td><td>'. number_format($files_board_thumb) . '</td><td>'. ConvertBytes($spaceused_board_thumb) . '</td></tr>';
			$tpl_page .= '<tr><td><strong>'. _gettext('Total') .'</strong></td><td>'. number_format($files_board_total) . '</td><td>'. ConvertBytes($spaceused_board_total) . '</td></tr>';
		}
		$tpl_page .= '<tr><td rowspan="4"><strong>'. _gettext('All boards') .'</strong></td><td>res/</td><td>'. number_format($files_res) . '</td><td>'. ConvertBytes($spaceused_res) . '</td></tr>';
		$tpl_page .= '<tr><td>src/</td><td>'. number_format($files_src) . '</td><td>'. ConvertBytes($spaceused_src) . '</td></tr>';
		$tpl_page .= '<tr><td>thumb/</td><td>'. number_format($files_thumb) . '</td><td>'. ConvertBytes($spaceused_thumb) . '</td></tr>';
		$tpl_page .= '<tr><td><strong>'. _gettext('Total') .'</strong></td><td>'. number_format($files_total) . '</td><td>'. ConvertBytes($spaceused_total) . '</td></tr>';
		$tpl_page .= '</table>';
		management_addlogentry(_gettext('Viewed disk space used'), 0);
	}

	function staff() { //183 lines (and??)
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>' ._gettext('Staff'). '</h2><br />';
		if (isset($_GET['add']) && !empty($_POST['username']) && !empty($_POST['password'])) {
			if ($_POST['username']!='SERVER') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" .KU_DBPREFIX. "staff` WHERE `username` = " .$tc_db->qstr($_POST['username']));
				if (count($results) == 0) {
					if ($_POST['type'] <= 3 && $_POST['type'] >= 0) {
				    $this->CheckToken($_POST['token']);
						$salt = $this->CreateSalt();
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" .KU_DBPREFIX. "staff` ( `username` , `password` , `salt` , `type` , `addedon` ) VALUES (" .$tc_db->qstr($_POST['username']). " , '" .md5($_POST['password'] . $salt). "' , '" .$salt. "' , '" .$_POST['type']. "' , '" .time(). "' )");
						$log = _gettext('Added'). ' ';
						switch ($_POST['type']) {
							case 0:
								$log .= _gettext('Janitor');
								break;
							case 1:
								$log .= _gettext('Administrator');
								break;
							case 2:
								$log .= _gettext('Moderator');
								break;
							case 3:
								$log .= _gettext('Userboards Owner');
								break;
						}
						$log .= ' '. $_POST['username'];
						management_addlogentry($log, 6);
						$tpl_page .= _gettext('Staff member successfully added.');
					} else {
						exitWithErrorPage('Invalid type');
					}
				} else {
					$tpl_page .= _gettext('A staff member with that ID already exists.');
				}
			}
			else {
				$tpl_page .= _gettext('Illegal username.');
			}
		} elseif (isset($_GET['del']) && $_GET['del'] > 0) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['del']) . "");
			if (count($results) > 0) {
				$username = $results[0]['username'];
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['del']) . "");
				$tpl_page .= _gettext('Staff successfully deleted') . '<hr />';
				management_addlogentry(_gettext('Deleted staff member') . ': '. $username, 6);
			} else {
				$tpl_page .= _gettext('Invalid staff ID.');
			}
		} elseif (isset($_GET['edit']) && $_GET['edit'] > 0) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['edit']) . "");
			if (count($results) > 0) {
				if (isset($_POST['submitting'])) {
          			$this->CheckToken($_POST['token']);
					$username = $results[0]['username'];
					$type	= $results[0]['type'];
					$boards	= array();
					if (isset($_POST['modsallboards'])) {
						$newboards = array('allboards');
					} else {
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY name FROM `" . KU_DBPREFIX . "boards`");
						foreach ($results as $line) {
							$boards = array_merge($boards, array($line['name']));
						}
						$changed_boards = array();
						$newboards = array();
						while (list($postkey, $postvalue) = each($_POST)) {
							if (substr($postkey, 0, 8) == "moderate") {
								$changed_boards = array_merge($changed_boards, array(substr($postkey, 8)));
							}
						}
						while (list(, $thisboard_name) = each($boards)) {
							if (in_array($thisboard_name, $changed_boards)) {
								$newboards = array_merge($newboards, array($thisboard_name));
							}
						}
					}
					$logentry = _gettext('Updated staff member') . ' - ';
					if ($_POST['type'] == '1') {
						$logentry .= _gettext('Administrator');
					} elseif ($_POST['type'] == '2') {
						$logentry .= _gettext('Moderator');
					} elseif ($_POST['type'] == '0') {
						$logentry .= _gettext('Janitor');
					} elseif ($_POST['type'] == '3') {
						$logentry .= _gettext('Userboards Owner');
					} else {
						exitWithErrorPage('Something went wrong.');
					}
					$logentry .= ': '. htmlentities($username);
					if ($_POST['type'] != '1') {
						$logentry .= ' - '. _gettext('Moderates') . ': ';
						if (isset($_POST['modsallboards'])) {
							$logentry .= strtolower(_gettext('All boards'));
						} else {
							$logentry .= '/'. implode('/, /', $newboards) . '/';
						}
					}
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `boards` = " . $tc_db->qstr(implode('|', $newboards)) . " , `type` = " .$tc_db->qstr($_POST['type']). " WHERE `id` = " . $tc_db->qstr($_GET['edit']) . "");
					management_addlogentry($logentry, 6);
					$tpl_page .= _gettext('Staff successfully updated') . '<hr />';
				}
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = '" . $_GET['edit'] . "'");
				$username = $results[0]['username'];
				$type	= $results[0]['type'];
				$boards	= explode('|', $results[0]['boards']);

				$tpl_page .= '<form action="manage_page.php?action=staff&edit=' .$_GET['edit']. '" method="post">
              <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="username">' ._gettext('Username'). ':</label>
							<input type="text" id="username" name="username" value="' .$username. '" disabled="disabled" /><br />
							<label for="type">' ._gettext('Type'). ':</label>
							<select id="type" name="type">';
				$tpl_page .= ($type==1) ? '<option value="1" selected="selected">' ._gettext('Administrator'). '</option>' : '<option value="1">' ._gettext('Administrator'). '</option>';
				$tpl_page .= ($type==2) ? '<option value="2" selected="selected">' ._gettext('Moderator'). '</option>' : '<option value="2">' ._gettext('Moderator'). '</option>';
				$tpl_page .= ($type==0) ? '<option value="0" selected="selected">' ._gettext('Janitor'). '</option>' : '<option value="0">' ._gettext('Janitor'). '</option>';
				$tpl_page .= ($type==3) ? '<option value="3" selected="selected">' ._gettext('Userboards Owner'). '</option>' : '<option value="3">' ._gettext('Userboards Owner'). '</option>';
				$tpl_page .= '</select><br /><br />';

				$tpl_page .= _gettext('Moderates') . '<br />
							<label for="modsallboards"><strong>' ._gettext('All boards'). '</strong></label>'. "\n";
				$tpl_page .= ($boards==array('allboards')) ? '<input type="checkbox" name="modsallboards" checked="checked" />' : '<input type="checkbox" name="modsallboards" />';
				$tpl_page .= '<br />' ._gettext('or'). '<br />';
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards`");
				foreach ($results as $line) {
					$tpl_page .= '<label for="moderate'. $line['name'] . '">'. $line['name'] . '</label><input type="checkbox" name="moderate'. $line['name'] . '" ';
					if (in_array($line['name'], $boards)) {
						$tpl_page .= 'checked="checked" ';
					}
					$tpl_page .= '/><br />';
				}
				$tpl_page .= '<input type="submit" value="'. _gettext('Modify staff member') . '" name="submitting" />
							</form><br />';
			}
		}

		$tpl_page .= '<form action="manage_page.php?action=staff&add" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="username">' ._gettext('Username'). ':</label>
					<input type="text" id="username" name="username" /><br />
					<label for="password">' ._gettext('Password'). ':</label>
					<input type="text" id="password" name="password" /><br />
					<label for="type">' ._gettext('Type'). ':</label>
					<select id="type" name="type">
						<option value="1">' ._gettext('Administrator'). '</option>
						<option value="2">' ._gettext('Moderator'). '</option>
						<option value="0">' ._gettext('Janitor'). '</option>
						<option value="3">' ._gettext('Userboards Owner'). '</option>
					</select><br />

					<input type="submit" value="' ._gettext('Add staff member'). '" />
					</form>
					<hr /><br />';

		$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Username') . '</th><th>'. _gettext('Added on') . '</th><th>'. _gettext('Last active') . '</th><th>'. _gettext('Moderating boards') . '</th><th>&nbsp;</th></tr>'. "\n";
		$i = 1;
		while($i <= 4) {
			if ($i == 1) {
				$stafftype = 'Administrator';
				$numtype = 1;
			} elseif ($i == 2) {
				$stafftype = 'Moderator';
				$numtype = 2;
			} elseif ($i == 3) {
				$stafftype = 'Janitor';
				$numtype = 0;
			} elseif ($i == 4) {
				$stafftype = 'Userboards Owner';
				$numtype = 3;
			}
			$tpl_page .= '<tr><td align="center" colspan="5"><font size="+1"><strong>'. _gettext($stafftype) . '</strong></font></td></tr>'. "\n";
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `type` = '" .$numtype. "' ORDER BY `username` ASC");
			if (count($results) > 0) {
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>' .$line['username']. '</td><td>' .date("y/m/d(D)H:i", $line['addedon']). '</td><td>';
					if ($line['lastactive'] == 0) {
						$tpl_page .= _gettext('Never');
					} elseif ((time() - $line['lastactive']) > 300) {
						$tpl_page .= timeDiff($line['lastactive'], false);
					} else {
						$tpl_page .= _gettext('Online now');
					}
					$tpl_page .= '</td><td>';
					if ($line['boards'] != '' || $line['type'] == 1) {
						if ($line['boards'] == 'allboards' || $line['type'] == 1) {
							$tpl_page .=  _gettext('All boards') ;
						} else {
							$tpl_page .= '<strong>/'. implode('/</strong>, <strong>/', explode('|', $line['boards'])) . '/</strong>';
						}
					} else {
						$tpl_page .= _gettext('No boards');
					}
					$tpl_page .= '</td><td>[<a href="?action=staff&edit='. $line['id'] . '">'. _gettext('Edit') . '</a>] [<a href="?action=staff&del='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>'. "\n";
				}
			} else {
				$tpl_page .= '<tr><td colspan="5">'. _gettext('None') . '</td></tr>'. "\n";
			}
			$i++;
		}
		$tpl_page .= '</table>';
	}

	/* Display moderators and administrators actions which were logged */
	function modlog() {
		global $tc_db, $tpl_page, $smarty;
		$this->AdministratorsOnly();

		$entries = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "modlog` ORDER BY `timestamp` DESC");
		$smarty->assign('modlog_entries', $entries);
		$tpl_page .= $smarty->fetch('modlog.tpl');
	}

	function proxyban() {
		global $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Ban proxy list') . '</h2><br />';
		if (isset($_FILES['imagefile'])) {
			$bans_class = new Bans;
			$ips = 0;
			$successful = 0;
			$proxies = file($_FILES['imagefile']['tmp_name']);
			foreach($proxies as $proxy) {
				if (preg_match('/.[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+.*/', $proxy)) {
					$proxy = trim($proxy);
					$ips++;
					if ($bans_class->BanUser(preg_replace('/:.*/', '', $proxy), 'SERVER', 1, 0, '', 'IP from proxylist automatically banned', '', 0, 0, 1, true)) {
						$successful++;
					}
				}
			}
			management_addlogentry(sprintf(_gettext('Banned %d IP addresses using an IP address list.'), $successful), 8);
			$tpl_page .= $successful . ' of '. $ips . ' IP addresses banned.';
		} else {
			$tpl_page .= '<form id="postform" action="'. KU_CGIPATH . '/manage_page.php?action=proxyban" method="post" enctype="multipart/form-data"> '. _gettext('Proxy list') .'<input type="file" name="imagefile" size="35" accesskey="f" /><br />
			<input type="submit" value="'. _gettext('Submit') .'" />
			<br />'. _gettext('The proxy list is assumed to be in plaintext *.*.*.*:port or *.*.*.* format, one IP per line.') .'<br /><br /><hr />';
		}
	}

	function sql() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('SQL query') . '</h2><br />';
		if (isset($_POST['query'])) {
      $this->CheckToken($_POST['token']);
			$tpl_page .= '<hr />';
			$result = $tc_db->Execute($_POST['query']);
			if ($result) {
				$tpl_page .= _gettext('Query executed successfully');
			} else {
				$tpl_page .= 'Error: '. $tc_db->ErrorMsg();
			}
			$tpl_page .= '<hr />';
			management_addlogentry(_gettext('Inserted SQL'), 10);
		}
		$tpl_page .= '<form method="post" action="?action=sql">
    <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<textarea name="query" rows="20" cols="60"></textarea>
		<br /><br />
		<input type="submit" value="'. _gettext('Inject') . '" />

		</form>';
	}

	function cleanup() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$tpl_page .= '<h2>'. _gettext('Cleanup') . '</h2><br />';

		if (isset($_POST['run'])) {
			$tpl_page .= '<hr />'. _gettext('Deleting non-deleted replies which belong to deleted threads.') .'<hr />';
			$this->delorphanreplies(true);
			$tpl_page .= '<hr />'. _gettext('Deleting unused images.') .'<hr />';
			$this->delunusedimages(true);
			$tpl_page .= '<hr />'. _gettext('Removing posts deleted more than one week ago from the database.') .'<hr />';
			$results = $tc_db->GetAll("SELECT `name`, `type`, `id` FROM `" . KU_DBPREFIX . "boards`");
			foreach ($results AS $line) {
				if ($line['type'] != 1) {
					$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line['id'] . " AND `IS_DELETED` = 1 AND `deleted_timestamp` < " . (time() - 604800) . "");
				}
			}
			$tpl_page .= _gettext('Optimizing all tables in database.') .'<hr />';
			if (KU_DBTYPE == 'mysql' || KU_DBTYPE == 'mysqli') {
				$results = $tc_db->GetAll("SHOW TABLES");
							foreach ($results AS $line) {
									$tc_db->Execute("OPTIMIZE TABLE `" . $line[0] . "`");
							}
			}
			if (KU_DBTYPE == 'postgres7' || KU_DBTYPE == 'postgres8' || KU_DBTYPE == 'postgres') {
								$results = $tc_db->GetAll("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'");
								foreach ($results AS $line) {
										$tc_db->Execute("VACUUM ANALYZE `" . $line[0] . "`");
								}
			}
			$tpl_page .= _gettext('Cleanup finished.');
			management_addlogentry(_gettext('Ran cleanup'), 2);
		} else {
			$tpl_page .= '<form action="manage_page.php?action=cleanup" method="post">'. "\n" .
						'	<input name="run" id="run" type="submit" value="'. _gettext('Run Cleanup') . '" />'. "\n" .
						'</form>';
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Boards Administration Pages
	* +------------------------------------------------------------------------------+
	*/

	function adddelboard() {
		global $tc_db, $tpl_page, $board_class;
		$this->AdministratorsOnly();

		if (isset($_POST['directory'])) {
      $this->CheckToken($_POST['token']);
			if (isset($_POST['add'])) {
				$tpl_page .= $this->addBoard($_POST['directory'], $_POST['desc']);
			} elseif (isset($_POST['del'])) {
				if (isset($_POST['confirmation'])) {
					$tpl_page .= $this->delBoard($_POST['directory'], $_POST['confirmation']);
				} else {
					$tpl_page .= $this->delBoard($_POST['directory']);
				}
			}
		}
		$tpl_page .= '<h2>'. _gettext('Add board') . '</h2><br />
		<form action="manage_page.php?action=adddelboard" method="post">
    	<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input type="hidden" name="add" id="add" value="add" />
		<label for="directory">'. _gettext('Directory') . ':</label>
		<input type="text" name="directory" id="directory" />
		<div class="desc">'. _gettext('The directory of the board.') . ' <strong>'. _gettext('Only put in the letter(s) of the board directory, no slashes!') . '</strong></div><br />

		<label for="desc">'. _gettext('Description') . ':</label>
		<input type="text" name="desc" id="desc" />
		<div class="desc">'. _gettext('The name of the board.') . '</div><br />

		<label for="firstpostid">'. _gettext('First Post ID') . ':</label>
		<input type="text" name="firstpostid" id="firstpostid" value="1" />
		<div class="desc">'. _gettext('The first post of this board will recieve this ID.') . '</div><br />

		<input type="submit" value="'. _gettext('Add Board') .'" />

		</form><br /><hr />

		<h2>'. _gettext('Delete board') .'</h2><br />

		<form action="manage_page.php?action=adddelboard" method="post">
    <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input type="hidden" name="del" id="del" value="del" />
		<label for="directory">'. _gettext('Directory') .':</label>' .
		$this->MakeBoardListDropdown('directory', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<input type="submit" value="'. _gettext('Delete board') .'" />

		</form>';
	}

	function sregister() {
		global $tc_db, $tpl_page;
		mb_internal_encoding("UTF-8");
		if(isset($_POST['username']) && isset($_POST['pass1']) && isset($_POST['pass2']) && $_POST['pass1'] == $_POST['pass2'] && $_POST['username'] != 'SERVER')  {
			if(ctype_alnum($_POST['username']) && ctype_alnum($_POST['pass1'])) {
				if(strlen($_POST['username']) <= KU_20MAXLOGINPASS && strlen($_POST['pass1']) <= KU_20MAXLOGINPASS) {
					$submit_time = time();
					if($submit_time - $_SESSION['captchatime'] <= KU_CAPTCHALIFE) {
						if(!empty($_SESSION['security_code']) && $_SESSION['security_code'] == mb_strtoupper($_POST['captcha'])) {
							$existing = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" .KU_DBPREFIX. "staff` WHERE `username` = " .$tc_db->qstr($_POST['username']));
							if(count($existing) == 0) {
								$salt = $this->CreateSalt();
								$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" .KU_DBPREFIX. "staff` ( `username` , `password` , `salt` , `type` , `addedon` ) VALUES (" .$tc_db->qstr($_POST['username']). " , '" .md5($_POST['pass1'] . $salt). "' , '" .$salt. "' , '3' , '" .time(). "' )");
								management_addlogentry('New user '.$_POST['username'].' has joined 2.0chan', 6, '', '', '2.0 service');
								$tpl_page = _gettext('Successfully registered new user. Now you can log in.');
								$this->LoginForm();
							}
							else {
								$tpl_page .= _gettext('A staff member with that ID already exists.');
							}
						}
						else {
							$tpl_page .= _gettext('Sorry, but you are not a human.');
						}
					}
					else {
						$tpl_page .=  _gettext('Captcha has expired.');
					}
				}
				else {
					$tpl_page .= sprintf(_gettext('Maximum username and password length is %d characters.'), KU_20MAXLOGINPASS);
				}
			}
			else {
				$tpl_page .= _gettext('Login and password must be alphanumeric.');
			}
		}
		else {
			$tpl_page .= _gettext('Wrong input');
		}
	}

	function adddelboard_mod() {
		global $tc_db, $tpl_page, $board_class;
		$this->BoardOwnersOnly();

		if (isset($_POST['directory'])) {
      $this->CheckToken($_POST['token']);
			if (isset($_POST['add'])) {
				$tpl_page .= $this->addBoard_mod($_POST['directory'], $_POST['desc'], isset($_POST['hidden']) ? '1' : '0');
			} 
			elseif (isset($_POST['del'])) {
				if (isset($_POST['confirmation'])) {
					$tpl_page .= $this->delBoard_mod($_POST['directory'], $_POST['confirmation']);
				} 
				else {
					$tpl_page .= $this->delBoard_mod($_POST['directory']);
				}
			}
		}

		$tpl_page .= '<h2>'. _gettext('Add board') . '</h2><br />
		<form action="manage_page.php?action=adddelboard_mod" method="post">
    	<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input type="hidden" name="add" id="add" value="add" />
		<label for="directory">'. _gettext('Directory') . ': /_</label>
		<input type="text" name="directory" id="directory" />
		<div class="desc">'. _gettext('The directory of the board.') . ' <strong>'. _gettext('Only put in the letter(s) of the board directory, no slashes!') . '</strong></div><br />

		<label for="desc">'. _gettext('Description') . ':</label>
		<input type="text" name="desc" id="desc" />
		<div class="desc">'. _gettext('The name of the board.') . '</div><br />

		<label for="hidden">'. _gettext('Hidden') .':</label>
		<input type="checkbox" name="hidden">
		<div class="desc">'. _gettext('If enabled, this board will not appear in the menu') .'</div><br>

		<input type="submit" value="'. _gettext('Add Board') .'" />

		</form><br /><hr />

		<h2>'. _gettext('Delete board') .'</h2><br />

		<form action="manage_page.php?action=adddelboard_mod" method="post">
    	<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input type="hidden" name="del" id="del" value="del" />
		<label for="directory">'. _gettext('Directory') .':</label>' .
		$this->MakeBoardListDropdown('directory', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<input type="submit" value="'. _gettext('Delete board') .'" />

		</form>';
	}

	function addBoard($dir, $desc) {
		global $tc_db;
		$this->AdministratorsOnly();

		$desc = htmlspecialchars($desc);

		$output = '';
		$output .= '<h2>'. _gettext('Add Results') .'</h2><br />';
		if (checkBoardDir($dir)) {
			if ($dir != '' && $desc != '') {
				if (strtolower($dir) != 'allboards') {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($dir) . "");
					if (count($results) == 0) {
						if (mkdir(KU_BOARDSDIR . $dir, 0777) && mkdir(KU_BOARDSDIR . $dir . '/res', 0777) && mkdir(KU_BOARDSDIR . $dir . '/src', 0777) && mkdir(KU_BOARDSDIR . $dir . '/thumb', 0777)) {
							file_put_contents(KU_BOARDSDIR . $dir . '/index.php', '<?php header(\'Location: 0.html\'); exit();');
							file_put_contents(KU_BOARDSDIR . $dir . '/.htaccess', 'DirectoryIndex '. KU_FIRSTPAGE . '');
							file_put_contents(KU_BOARDSDIR . $dir . '/src/.htaccess', 'AddType text/plain .ASM .C .CPP .JAVA .JS .LSP .PHP .PL .PY .RAR .SCM .TXT'. "\n" . 'SetHandler default-handler');
							if ($_POST['firstpostid'] < 1) {
								$_POST['firstpostid'] = 1;
							}
							$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "boards` ( `name` , `desc` , `createdon`, `start`, `image`, `includeheader` ) VALUES ( " . $tc_db->qstr($dir) . " , " . $tc_db->qstr($desc) . " , '" . time() . "', " . $_POST['firstpostid'] . ", '', '' )");
							$boardid = $tc_db->Insert_Id();
							$filetypes = $tc_db->GetAll("SELECT " . KU_DBPREFIX . "filetypes.id FROM " . KU_DBPREFIX . "filetypes WHERE " . KU_DBPREFIX . "filetypes.filetype = 'JPG' OR " . KU_DBPREFIX . "filetypes.filetype = 'GIF' OR " . KU_DBPREFIX . "filetypes.filetype = 'PNG';");
							foreach ($filetypes AS $filetype) {
								$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "board_filetypes` ( `boardid` , `typeid` ) VALUES ( " . $boardid . " , " . $filetype['id'] . " );");
							}
							$this->rebuild20json();
							$board_class = new Board($dir);
							$board_class->RegenerateAll();
							unset($board_class);
							$output .= _gettext('Board successfully added.') . '<br /><br /><a href="'. KU_BOARDSPATH . '/'. $dir . '/">/'. $dir . '/</a>!<br />';
							$output .= '<form action="?action=boardopts" method="post"><input type="hidden" name="board" value="'. $dir . '" /><input type="submit" style="border: 1px solid; background: none; text-align: center;" value="'. _gettext('Click to edit board options') .'" /><br /><hr /></form>';
							management_addlogentry(_gettext('Added board'), 3, $dir);
						}
						else {
							$output .= '<br />'. _gettext('Unable to create directories.');
						}
					}
					else {
						$output .= _gettext('A board with that name already exists.');
					}
				}
				else {
					$output .= _gettext('That name is for internal use. Please pick another.');
				}
			}
			else {
				$output .= _gettext('Please fill in all required fields.');
			}
		}
		else {
			$output .= _gettext('Invalid directory name.');
		}

		return $output;
	}

	function addBoard_mod($dir, $desc, $hidden=0) {
		global $tc_db;
		$this->BoardOwnersOnly();

		$desc = htmlspecialchars($desc);

		$output = '';
		$output .= '<h2>'. _gettext('Add Results') .'</h2><br />';

		if (checkBoardDir($dir)) {
			$dir = '_'.$dir;
			if ($dir != '' && $desc != '') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($dir) . "");
				if (count($results) == 0) {
					$moderating = array_filter(explode('|', $tc_db->GetOne("SELECT HIGH_PRIORITY `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "'")));
					$existingboards = $tc_db->GetAll("SELECT HIGH_PRIORITY `name` from `" . KU_DBPREFIX . "boards`");
					foreach($existingboards as &$exb) {
						$xba[] = $exb['name'];
					}
					$moderating = array_intersect($moderating, $xba);
					unset($exb);
					if(count($moderating) < KU_20_BOARDSLIMIT) {
						if (mkdir(KU_BOARDSDIR . $dir, 0777) && mkdir(KU_BOARDSDIR . $dir . '/res', 0777) && mkdir(KU_BOARDSDIR . $dir . '/src', 0777) && mkdir(KU_BOARDSDIR . $dir . '/thumb', 0777)) {
							/* Add mod rights */
							$moderating[] = $dir;
							$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `boards` = " . $tc_db->qstr(implode('|',$moderating)) . " WHERE `username` = '" . $_SESSION['manageusername'] . "'");
							file_put_contents(KU_BOARDSDIR . $dir . '/index.php', '<?php header(\'Location: 0.html\'); exit();');
							file_put_contents(KU_BOARDSDIR . $dir . '/.htaccess', 'DirectoryIndex '. KU_FIRSTPAGE . '');
							file_put_contents(KU_BOARDSDIR . $dir . '/src/.htaccess', 'AddType text/plain .ASM .C .CPP .CSS .JAVA .JS .LSP .PHP .PL .PY .RAR .SCM .TXT'. "\n" . 'SetHandler default-handler');
							$_POST['firstpostid'] = 1;
							$sect20 = $tc_db->GetOne('SELECT `id` FROM `'. KU_DBPREFIX .'sections` WHERE `abbreviation`="20"');
							$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "boards` ( `name` , `desc` , `createdon`, `start`, `image`, `includeheader`,`section`,`hidden`) VALUES ( " . $tc_db->qstr($dir) . " , " . $tc_db->qstr($desc) . " , '" . time() . "', " . $_POST['firstpostid'] . ", '', '', " . $sect20 . ", " . $tc_db->qstr($hidden) . " )");
							$boardid = $tc_db->Insert_Id();
							$filetypes = $tc_db->GetAll("SELECT " . KU_DBPREFIX . "filetypes.id FROM " . KU_DBPREFIX . "filetypes WHERE " . KU_DBPREFIX . "filetypes.filetype = 'JPG' OR " . KU_DBPREFIX . "filetypes.filetype = 'GIF' OR " . KU_DBPREFIX . "filetypes.filetype = 'PNG';");
							foreach ($filetypes AS $filetype) {
								$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "board_filetypes` ( `boardid` , `typeid` ) VALUES ( " . $boardid . " , " . $filetype['id'] . " );");
							}
							$board_class = new Board($dir);
							$board_class->RegenerateAll();
							unset($board_class);
							$output .= _gettext('Board successfully added.') . '<br /><br /><a href="'. KU_BOARDSPATH . '/'. $dir . '/">/'. $dir . '/</a>!<br />';
							$output .= '<form action="?action=boardopts_mod" method="post"><input type="hidden" name="board" value="'. $dir . '" /><input type="submit" style="border: 1px solid; background: none; text-align: center;" value="'. _gettext('Click to edit board options') .'" /><br /><hr /></form>';
							management_addlogentry(_gettext('Added board'), 3, $dir);
							$this->rebuild20json();
						}
						else {
							$output .= '<br />'. _gettext('Unable to create directories.');
						}
					} else {
						$output .= '<br />'. _gettext('Board limit exceeded. Delete the unused boards.');
					}
				} else {
					$output .= _gettext('A board with that name already exists.');
				}
			} else {
				$output .= _gettext('Please fill in all required fields.');
			}
		}
		else {
			$output .= _gettext('Invalid directory name.');
		}

		return $output;
	}

	function delboard($dir, $confirm = '') {
		global $tc_db;
		$this->AdministratorsOnly();

		$output = '';
		$output .= '<h2>'. _gettext('Delete Results') .'</h2><br />';
		if (!empty($dir)) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($dir) . "");
			foreach ($results as $line) {
				$board_id = $line['id'];
				$board_dir = $line['name'];
			}
			if (count($results) > 0) {
				if (!empty($confirm)) {
					if (removeBoard($board_dir)) {
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = '" . $board_id . "'");
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "boards` WHERE `id` = '" . $board_id . "'");
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $board_id . "'");

						/*     Remove rights for all mods     */
						$staffresults = $tc_db->GetAll("SELECT `id`, `boards` FROM `" .KU_DBPREFIX. "staff` WHERE `boards` != 'allboards'");
						foreach($staffresults as $moder) {
							$set = explode('|', $moder['boards']);
							$moderating = array_diff(array_filter($set), array($dir));
							if(count($set) != count($moderating))
								$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `boards` = " . $tc_db->qstr(implode('|',$moderating)) . " WHERE `id` = '" . $moder['id'] . "'");
						}
						/*     Remove board entries from wordfilter     */
						$wfresults = $tc_db->GetAll("SELECT `id`, `boards` FROM `" .KU_DBPREFIX. "wordfilter`");
						if(count($wfresults) > 0) {
							foreach($wfresults as $line) {
								$set = explode('|', $line['boards']);
								$filtered = array_diff(array_filter($set), array($dir));
								if(count($set) != count($filtered))
									$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "wordfilter` SET `boards` = " . $tc_db->qstr(implode('|',$filtered)) . " WHERE `id` = '" . $line['id'] . "'");
							}
						}
						/*     Remove board banners from bannerlist     */
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='0' AND `link`='".$dir."'");
						$this->updateBannersJSON();
						$this->rebuild20json();

						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$menu_class = new Menu();
						$menu_class->Generate();
						$output .= _gettext('Board successfully deleted.');
						management_addlogentry(_gettext('Deleted board'), 3, $dir);
					} else {
						// Error
						$output .= _gettext('Unable to delete board.');
					}
				} else {
					$output .= sprintf(_gettext('Are you absolutely sure you want to delete %s?'),'/'. $board_dir . '/') .
					'<br />
					<form action="manage_page.php?action=adddelboard" method="post">
          			<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<input type="hidden" name="del" id="del" value="del" />
					<input type="hidden" name="directory" id="directory" value="'. $dir . '" />
					<input type="hidden" name="confirmation" id="confirmation" value="yes" />

					<input type="submit" value="'. _gettext('Continue') .'" />

					</form><br />';
				}
			} else {
				$output .= _gettext('A board with that name does not exist.');
			}
		}
		$output .= '<br />';

		return $output;
	}

	function delboard_mod($dir, $confirm = '') {
		global $tc_db, $tpl_page;
		$this->BoardOwnersOnly();

		$output = '';
		$output .= '<h2>'. _gettext('Delete Results') .'</h2><br />';
		if (!empty($dir)) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($dir) . "");
			foreach ($results as $line) {
				$board_id = $line['id'];
				$board_dir = $line['name'];
			}
			if (count($results) > 0) {
				if (!empty($confirm)) {
					if (!$this->CurrentUserIsModeratorOfBoard($dir, $_SESSION['manageusername'])) {
						exitWithErrorPage(_gettext('You are not a moderator of this board.'));
					}
					if (removeBoard($board_dir)) {
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = '" . $board_id . "'");
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "boards` WHERE `id` = '" . $board_id . "'");
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $board_id . "'");

						/*     Remove rights for all mods     */
						$staffresults = $tc_db->GetAll("SELECT `id`, `boards` FROM `" .KU_DBPREFIX. "staff` WHERE `boards` != 'allboards'");
						foreach($staffresults as $moder) {
							$set = explode('|', $moder['boards']);
							$moderating = array_diff(array_filter($set), array($dir));
							if(count($set) != count($moderating))
								$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `boards` = " . $tc_db->qstr(implode('|',$moderating)) . " WHERE `id` = '" . $moder['id'] . "'");
						}
						/*     Remove board entries from wordfilter     */
						$wfresults = $tc_db->GetAll("SELECT `id`, `boards` FROM `" .KU_DBPREFIX. "wordfilter`");
						if(count($wfresults) > 0) {
							foreach($wfresults as $line) {
								$set = explode('|', $line['boards']);
								$filtered = array_diff(array_filter($set), array($dir));
								if(count($set) != count($filtered))
									$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "wordfilter` SET `boards` = " . $tc_db->qstr(implode('|',$filtered)) . " WHERE `id` = '" . $line['id'] . "'");
							}
						}
						/*     Remove board banners from bannerlist     */
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='0' AND `link`='".$dir."'");
						$this->updateBannersJSON();

						unset($moder);
						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$menu_class = new Menu();
						$menu_class->Generate();
						$output .= _gettext('Board successfully deleted.');
						management_addlogentry(_gettext('Deleted board'), 3, $dir);
						$this->rebuild20json();
					} else {
						// Error
						$output .= _gettext('Unable to delete board.');
					}
				} else {
					$output .= sprintf(_gettext('Are you absolutely sure you want to delete %s?'),'/'. $board_dir . '/') .
					'<br />
					<form action="manage_page.php?action=adddelboard_mod" method="post">
          			<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<input type="hidden" name="del" id="del" value="del" />
					<input type="hidden" name="directory" id="directory" value="'. $dir . '" />
					<input type="hidden" name="confirmation" id="confirmation" value="yes" />

					<input type="submit" value="'. _gettext('Continue') .'" />

					</form><br />';
				}
			} else {
				$output .= _gettext('A board with that name does not exist.');
			}
		}
		$output .= '<br />';

		return $output;
	}

	/* Replace words in posts with something else */
	function wordfilter() {
		global $tc_db, $tpl_page;
		$this->BoardOwnersOnly();

		$tpl_page .= '<h2>'. _gettext('Wordfilter') . '</h2><br />';

		$boardlist = $this->BoardList($_SESSION['manageusername']);
		// $bl_redux = [];
			foreach($boardlist as $brd) {
				$bl_redux[]= $brd['name'];
			}

		if (isset($_POST['word'])) {
      		$this->CheckToken($_POST['token']);
			if ($_POST['word'] != '' && $_POST['replacedby'] != '' && is_array($_POST['wordfilter'])) {
				// $boards_toinsert = [];
				foreach ($_POST['wordfilter'] as $board) {
					if(in_array($board, $bl_redux)) $boards_toinsert[] = $board;
				}
				if(count($boards_toinsert) > 0) {
					$wf_all = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter`");
					// $wf_mywords = [];
					foreach($wf_all as $line) {
						$belong = true;
						foreach(explode('|', $line['boards']) as $lb) {
							if(!in_array($lb, $bl_redux)) $belong = false;
						}
						if($belong) $wf_mywords[]= $line['word'];
					}
					if(!in_array($_POST['word'], $wf_mywords)) {
						$is_regex = (isset($_POST['regex'])) ? '1' : '0';
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "wordfilter` ( `word` , `replacedby` , `boards` , `time` , `regex` ) VALUES ( " . $tc_db->qstr($_POST['word']) . " , " . $tc_db->qstr($_POST['replacedby']) . " , " . $tc_db->qstr(implode('|', $boards_toinsert)) . " , '" . time() . "' , '" . $is_regex . "' )");
						$tpl_page .= _gettext('Word successfully added.');
					}
					else {
						$tpl_page .= _gettext('That word already exists.');
					}
				}
				else {
					$tpl_page .= _gettext('No boards belong to you.');
				}
			} else {
				$tpl_page .= _gettext('Please fill in all required fields.');
			}
			$tpl_page .= '<hr />';
		} elseif (isset($_GET['delword'])) {
			if ($_GET['delword'] > 0) {
				$brds = $tc_db->GetOne("SELECT HIGH_PRIORITY `boards` FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['delword']) . "");
				$brds = explode("|", $results);
				if(count($results) > 0) {
					if(array_in_array($brds, $bl_redux)) {
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['delword']) . "");
						$tpl_page .= _gettext('Word successfully removed.');
						management_addlogentry(_gettext('Removed word from wordfilter') . ': '. htmlentities($del_word), 11);
					}
					else {

					}

				} else {
					$tpl_page .= _gettext('That ID does not exist.');
				}
				$tpl_page .= '<hr />';
			}
		} elseif (isset($_GET['editword'])) {
			if ($_GET['editword'] > 0) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['editword']) . "");
				if (count($results) > 0) {
					if (!isset($_POST['replacedby'])) {
						foreach ($results as $line) {
							$tpl_page .= '<form action="manage_page.php?action=wordfilter&editword='.$_GET['editword'].'" method="post">
              				<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="word">'. _gettext('Word') .':</label>
							<input type="text" name="word" value="'.$line['word'].'" disabled /><br />

							<label for="replacedby">'. _gettext('Replace by') .':</label>
							<input type="text" name="replacedby" value="'.$line['replacedby'].'" /><br />

							<label for="regex">'. _gettext('Regular expression') .':</label>
							<input type="checkbox" name="regex"';
							if ($line['regex'] == '1') {
								$tpl_page .= ' checked';
							}
							$tpl_page .= ' /><br />

							<label>'. _gettext('Boards') .':</label><br />';

							$array_boards = array();
							$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards`");
							foreach ($resultsboard as $lineboard) {
								$array_boards = array_merge($array_boards, array($lineboard['name']));
							}
							foreach ($array_boards as $this_board_name) {
								$tpl_page .= '<label for="wordfilter[]">'. $this_board_name . '</label><input type="checkbox" name="wordfilter[]" value="'.$this_board_name.'"';
								if (in_array($this_board_name, explode("|", $line['boards'])) && explode("|", $line['boards']) != '') {
									$tpl_page .= 'checked ';
								}
								$tpl_page .= ' /><br />';
							}
							$tpl_page .= '<br />

							<input type="submit" value="'. _gettext('Edit word') .'" />

							</form>';
						}
					} else {
            $this->CheckToken($_POST['token']);
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['editword']) . "");
						if (count($results) > 0) {
							foreach ($results as $line) {
								$wordfilter_word = $line['word'];
							}
							$wordfilter_boards = array();
							$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards`");

							if (isset($_POST['wordfilter'])){
								foreach ($_POST['wordfilter'] as $board) {
									$check = $tc_db->GetOne("SELECT `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($board));
									if (!empty($check)) {
										$wordfilter_boards[] = $board;
									}
								}
							}

							$is_regex = (isset($_POST['regex'])) ? '1' : '0';

							$tc_db->Execute("UPDATE `". KU_DBPREFIX ."wordfilter` SET `replacedby` = " . $tc_db->qstr($_POST['replacedby']) . " , `boards` = " . $tc_db->qstr(implode('|', $wordfilter_boards)) . " , `regex` = '" . $is_regex . "' WHERE `id` = " . $tc_db->qstr($_GET['editword']) . "");

							$tpl_page .= _gettext('Word successfully updated.');
							management_addlogentry(_gettext('Updated word on wordfilter') . ': '. htmlentities($wordfilter_word), 11);
						} else {
							$tpl_page .= _gettext('Unable to locate that word.');
						}
					}
				} else {
					$tpl_page .= _gettext('That ID does not exist.');
				}
				$tpl_page .= '<hr />';
			}
		} else {
			$tpl_page .= '<form action="manage_page.php?action=wordfilter" method="post">
      		<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="word">'. _gettext('Word') .':</label>
			<input type="text" name="word" /><br />

			<label for="replacedby">'. _gettext('Replace by') .':</label>
			<input type="text" name="replacedby" /><br />

			<label for="regex">'. _gettext('Regular expression') .':</label>
			<input type="checkbox" name="regex" /><br />

			<label>'. _gettext('Boards') .':</label><br />';

			$array_boards = array();
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY name FROM `" . KU_DBPREFIX . "boards`");
			$array_boards = array_merge($array_boards, $resultsboard);
			$tpl_page .= $this->MakeBoardListCheckboxes('wordfilter', $this->BoardList($_SESSION['manageusername'])) .
			'<br />
			<input type="submit" value="'. _gettext('Add word') .'" />
			</form>
			<hr />';
		}
		$tpl_page .= '<br />';

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter`");
		if ($results > 0 && count($boardlist) > 0) {

			$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Word') . '</th><th>'. _gettext('Replacement') . '</th><th>'. _gettext('Boards') . '</th><th>&nbsp;</th></tr>'. "\n";
			foreach($results as $line) {
				$belong = true;
				foreach(explode('|', $line['boards']) as $lb) {
					if(!in_array($lb, $bl_redux)) $belong = false;
				}
				if($belong) {
					$tpl_page .= '<tr><td>'. $line['word'] . '</td><td>'. $line['replacedby'] . '</td><td>'.
						'<strong>/'. implode('/</strong>, <strong>/', explode('|', $line['boards'])) . '/</strong>&nbsp;'.
						'</td><td>[<a href="manage_page.php?action=wordfilter&editword='. $line['id'] . '">'. _gettext('Edit') . '</a>] [<a href="manage_page.php?action=wordfilter&delword='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>'. "\n";
				}
			}
			$tpl_page .= '</table>';
		}
	}

	/* Ad Management */
	function ads() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Ad Management') .'</h2><span style="font-size: 100%; font-weight: 600;">'. _gettext('Anything can go here, such as banners, links, etc. It doesn\'t have to be just ads.') .'</span>'. "\n";

		if (isset($_GET['edit']) && ($_GET['edit'] == 1 || $_GET['edit'] == 2)) {
			if (isset($_POST['code']) and !empty($_POST['code'])) {
        $this->CheckToken($_POST['token']);
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "ads` SET `disp` = " . $tc_db->qstr($_POST['disp']) . ", `code` = " . $tc_db->qstr($_POST['code']) . " WHERE `id` = " . $tc_db->qstr($_GET['edit']) . "");
				$tpl_page .= '<hr /><h3>'. _gettext('Ad Edited.') .'</h3><p style="text-align: center;">'.sprintf(_gettext('Click %shere%s to return to Ad Management.'),'<a href="?action=ads">','</a>') .'</p><hr />'. "\n";
				management_addlogentry(_gettext('Edited an ad'), 9);
			}
			$results = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "ads` WHERE `id` = '" . $_GET['edit'] . "'");
			foreach ($results as $ad) {
				$tpl_page .= '<form action="?action=ads&edit='. $_GET['edit'] . '" method="post">'. "\n" .
              '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />' . "\n" .
							'<label for="pos">'. _gettext('Position') .':</label>'. "\n" .
							'<input type="text" disabled="disabled" name="pos" value="'. $ad['position'] . '" /><br />'. "\n" .
							'<label for="code">'. _gettext('Code') .':</label>'. "\n" .
							'<textarea name="code" rows="10" cols="25">' . htmlspecialchars($ad['code']) . '</textarea>' . "\n" .
							'<label for="disp">'. _gettext('Display') .':</label>'. "\n" .
							'<input type="text" maxlength="1" name="disp" value="'. $ad['disp'] . '" /><div class="desc">'. _gettext('Put <strong>0</strong> for no display, <strong>1</strong> to display.') .'</div><br />'. "\n" .
							'<input type="submit" value="'. _gettext('Edit') . '" /><br />'. "\n";
			}
		} else {
			$results = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "ads`");
			if (count($results) > 0) {
				$tpl_page .= '<table border="1">'. "\n";
				$tpl_page .= '<tr><th>'. _gettext('Position') .'</th><th>'. _gettext('Display') .'</th><th>'. _gettext('Code') .'</th><th>&nbsp;</th></tr>'. "\n";
				foreach ($results as $line) {
					$find = array('<', '>');
					$replace = array ('&lt;', '&gt;');
					if ($line['disp'] == 0) {
						$disp = 'Not Displayed';
					} elseif ($line['disp'] == 1) {
						$disp = _gettext('Displayed');
					}
					$tpl_page .= '<tr><td>'. $line['position'] . '</td><td>'. $disp . '</td><td>'. str_replace($find, $replace, $line['code']) . '</td><td><a href="?action=ads&edit='. $line['id'] . '">'. _gettext('Edit') .'</a></td></tr>'. "\n";
				}
				$tpl_page .= '</table>'. "\n";
			} else {
				$tpl_page .= _gettext('There was an error during install, and the ads table didn\'t get populated.');
			}
		}
	}

	/* Add or delete Embed Entries */
	function embeds() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$disptable = true; $formval = 'add'; $title = _gettext('Embed Management');
		if(isset($_GET['act'])) {
			if ($_GET['act'] == 'edit') {
				if (isset($_POST['embeds'])) {
          $this->CheckToken($_POST['token']);
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "embeds` SET `filetype` = " . $tc_db->qstr(trim($_POST['filetype'])) . ", `videourl` = " . $tc_db->qstr(trim($_POST['videourl'])) . ", `name` = " . $tc_db->qstr(trim($_POST['name'])) . ", `width` = " . $tc_db->qstr(trim($_POST['width'])) . ", `height` = " . $tc_db->qstr(trim($_POST['height'])) . ", `code` = " . $tc_db->qstr(trim($_POST['embeds'])) . " WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
					$tpl_page .= '<hr /><h3>'. _gettext('Embed Edited') .'</h3><hr />';
					management_addlogentry(_gettext('Edited an embed'), 13);
				}
				$formval = 'edit&amp;id='. $_GET['id']; $title .= ' - Edit';
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "embeds` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$values = $results[0];
				$disptable = false;
			} elseif ($_GET['act'] == 'del') {
				$results = $tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "embeds` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$tpl_page .= '<hr /><h3>'. _gettext('Embed deleted') .'</h3><hr />';
				management_addlogentry(_gettext('Deleted an Embed'), 13);
			} elseif ($_GET['act'] == 'add') {
				if (isset($_POST['embeds']) && isset($_POST['name']) && isset($_POST['filetype']) && isset($_POST['videourl'])) {
					if ($_POST['embeds'] != '') {
            $this->CheckToken($_POST['token']);
						$width = ($_POST['width'] != '') ? $_POST['width'] : KU_YOUTUBEWIDTH;
						$height = ($_POST['height'] != '') ? $_POST['height'] : KU_YOUTUBEHEIGHT;

						$tpl_page .= '<hr />';
						if ($_POST['embeds'] != '') {
							$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "embeds` ( `name` , `filetype` , `videourl` , `width` , `height` , `code` ) VALUES ( " . $tc_db->qstr(trim($_POST['name'])) . " , " . $tc_db->qstr(trim($_POST['filetype'])) . " , " . $tc_db->qstr(trim($_POST['videourl'])) . " , " . $tc_db->qstr(trim($width)) . " , " . $tc_db->qstr(trim($height)) . " , " . $tc_db->qstr(trim($_POST['embeds'])) . " )");
							$tpl_page .= '<h3>'. _gettext('Embed successfully added.') . '</h3>';
							management_addlogentry(_gettext('Added an Embed'), 13);
						} else {
							$tpl_page .= _gettext('You must enter code.');
						}
						$tpl_page .= '<hr />';
					}
				}
			}
		}
		$tpl_page .= '<h2>'. $title . '</h2><br />
			<form method="post" action="?action=embeds&amp;act='. $formval . '">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="name">'. _gettext('Site Name') . ':</label>
			<input type="text" id="name" name="name" value="'. (isset($values['name']) ? $values['name'] : ''). '" />
			<div class="desc">'. _gettext('Can not be left blank.') . '</div><br />

			<label for="filetype">'. _gettext('Filetype') . ':</label>
			<input type="text" id="filetype" name="filetype" maxlength="3" value="'. (isset($values['filetype']) ? $values['filetype'] : '') . '" />
			<div class="desc">'. _gettext('Can not be left blank, or longer than 3 characters') . '</div><br />

			<label for="videourl">'. _gettext('Video URL Start') . ':</label>
			<input type="text" id="videourl" name="videourl" value="'. (isset($values['videourl']) ? $values['videourl'] : '') . '" />
			<div class="desc">'. _gettext('Can not be left blank. This is what comes before the embed ID. Example: \'http://www.youtube.com/watch?v=\'') . '</div><br />

			<label for="embeds">'. _gettext('Code') . ':</label>
			<textarea id="embeds" name="embeds" rows="25" cols="80">' . (isset($values['code']) ? htmlspecialchars($values['code']) : '') . '</textarea><br />

			<label for="width">'. _gettext('Width') . ':</label>
			<input type="text" id="width" name="width" value="'. (isset($values['width']) ? $values['width'] : '') . '" />
			<div class="desc">'. _gettext('This can be left blank. It will be reset with the default width set in config.php') . '</div><br />

			<label for="height">'. _gettext('Height') . ':</label>
			<input type="text" id="height" name="height" value="'. (isset($values['height']) ? $values['height'] : '') . '" />
			<div class="desc">'. _gettext('This can be left blank. It will be reset with the default height set in config.php') . '</div><br />

			<div class="desc">'. _gettext('When adding an embed, please check <a href="http://www.kusabax.org/wiki/embedding">http://www.kusabax.org/wiki/embedding</a> and check if there is a tutorial image for the site you are adding. Put this image in the /inc/embedhelp/ folder, or create your own in this folder if one does not exist. The image must be the same as the site name in all lowercase.') . '</div><br />
			<input type="submit" value="'. _gettext('Edit') .'" />
			</form>';
		if ($disptable) {
			$tpl_page .= '<br /><hr /><h1>'. _gettext('Edit/Delete Embeds') .'</h1>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "embeds` ORDER BY `id` ASC");
			if (count($results) > 0) {
				$find = array('<', '>');
				$replace = array ('&lt;', '&gt;');
				$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Site Name') .'</th><th>'. _gettext('Filetype') .'</th><th>'. _gettext('Video URL Start') .'</th><th>'. _gettext('Width') .'</th><th>'. _gettext('Height') .'</th><th>'. _gettext('Code') .'</th></tr>';
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>'. $line['name'] . '</td><td>'. $line['filetype'] . '</td><td>'. $line['videourl'] . '</td><td>'. $line['width'] . '</td><td>'. $line['height'] . '</td><td>'. str_replace($find, $replace, $line['code']) . '</td><td>[<a href="?action=embeds&amp;act=edit&amp;id='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=embeds&amp;act=del&amp;id='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
				}
				$tpl_page .= '</table>';
			} else {
				$tpl_page .= _gettext('No Embeds yet.');
		}
		}
	}


	function movethread() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Move thread') . '</h2><br />';

		if (isset($_GET['id']) && isset($_GET['board_from']) && isset($_GET['board_to'])) {
      $this->CheckToken($_GET['token']);

      // Validation
      if (! preg_match('/^[0-9]+$/', $_GET['id']))
        exitWithErrorPage(_gettext('Invalid thread ID.'));
      if (!checkBoardDir($_GET['board_from']) || !checkBoardDir($_GET['board_to']) || $_GET['board_from']==$_GET['board_to'])
        exitWithErrorPage(_gettext('Invalid directory name.'));

			// Get the IDs for the from and to boards
			$board_from_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board_from']));
			$board_to_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board_to']));
			$board_from = $_GET['board_from'];
			$board_to = $_GET['board_to'];
      $thread_from = $_GET['id'];

      $boards_to_regenerate = array($board_from, $board_to);

      // Delete old HTMLs
			$from_html = KU_BOARDSDIR . $board_from . '/res/'. $thread_from . '.html';
			$from_html_50 = KU_BOARDSDIR . $board_from . '/res/'. $thread_from . '+50.html';
			$from_html_100 = KU_BOARDSDIR . $board_from . '/res/'. $thread_from . '-100.html';
			@unlink($from_html);
			@unlink($from_html_50);
			@unlink($from_html_100);

      $tc_db->SetFetchMode(ADODB_FETCH_ASSOC);
      $tc_db->Execute("START TRANSACTION");
      $postembeds = $tc_db->GetAll("SELECT `id`, `file_id`, `file`, `file_type`, `file_size_formatted`
        FROM `" . KU_DBPREFIX . "postembeds`
        WHERE
          `boardid`=$board_from_id
          AND
          (`id`=$thread_from OR `parentid`=$thread_from)
        ORDER BY `parentid` ASC, `id` ASC");

      $posts = group_embeds($postembeds);
      $id_map = array();
      foreach($posts as $post) {
        $id = $post['id'];
        $new_id = $tc_db->GetOne("SELECT COALESCE(MAX(id),0) + 1 FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = '$board_to_id'");
        if ($id == $thread_from) {
          $thread_to = $new_id;
        }
        $tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts`
          SET
            `id` = $new_id,
            `boardid` = $board_to_id".
            ($id == $thread_from ? ' ' : ", `parentid` = $thread_to ").
          "WHERE
            `boardid` = $board_from_id
            AND
            `id` = $id");
        foreach($post['embeds'] as $embed) {
          if ($embed['file'] != 'removed') {
            $files = GetFileAndThumbs($embed);
            // move file physically
            foreach($files as $f) {
              @rename(KU_BOARDSDIR.$board_from.$f, KU_BOARDSDIR.$board_to.$f);
              @unlink(KU_BOARDSDIR.$board_from.$f);
            }
          }
          $tc_db->Execute("UPDATE `" . KU_DBPREFIX . "files`
            SET
              `post_id` = $new_id,
              `boardid` = $board_to_id
            WHERE
              `file_id` = ".$embed['file_id']);
        }
        $id_map []= array('before'=>$id, 'after'=>$new_id);
      }

      // Replace ref links across the whole site
      $all_refs = $tc_db->GetAll("SELECT `id`, `boardid`, `message` FROM `" . KU_DBPREFIX . "posts` WHERE  `message` LIKE '%\"ref|$board_from|$thread_from%'");
      $board_ids = array();
      foreach($all_refs as &$ref) {
        foreach($id_map as $idm) {
          $ref_boardid = $ref['boardid'];
          // Add the board id where reference was found to the array of boards which need regeneration
          if ($ref_boardid != $board_from_id && $ref_boardid != $board_to_id && !in_array($ref_boardid, $board_ids))
            $board_ids []= $ref_boardid;

          $id_before = $idm['before'];
          $id_after = $idm['after'];

          // Replace message contents
          $exp = '/<a href=\\\\"\/(' . $board_from. ')\/res\/' . $thread_from. '\.html#' . $id_before. '\\\\"( onclick=\\\\"return highlight\(\')?(' . $id_before. ')?(\', true\);\\\\")? class=\\\\"ref\|' . $board_from. '\|' . $thread_from. '\|' . $id_before. '(.+?)>(?:(&gt;&gt;)(?:(?:\/' . $board_from. '\/)?' . $id_before. ')|(##(?:OP|ОП)##)?)/';
          $ref['message'] = preg_replace_callback($exp, function($matches) use ($board_from, $thread_from, $id_before, $board_to, $thread_to, $id_after, $ref_boardid, $board_to_id) {
            $res = "<a href=\\\"/$board_to/res/$thread_to.html#$id_after\\\"";
            $res.= $matches[2];
            $res.= $matches[3] ? $id_after : '';
            $res.= $matches[4];
            $res.= " class=\\\"ref|$board_to|$thread_to|$id_after";
            $res.= $matches[5].'>';
            if ($matches[6]) {
              $res.= $matches[6];
              if ($ref_boardid != $board_to_id) // Reference should stay/become external
                $res.= "/$board_to/";
              $res.= $id_after;
            }
            else // In case it's a prooflabel
              $res.= $matches[7];
            return $res;
          }, $ref['message']);
        }
        $tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `message` = " . $tc_db->qstr($ref['message']) . " WHERE `boardid` = " . $ref_boardid . " AND `id` = " . $ref['id']);
      }
      $tc_db->Execute("COMMIT");

      //Regenerate pages
      foreach($board_ids as $b_id) {
        $boards_to_regenerate []= boardid_to_dir($b_id);
      }
      foreach($boards_to_regenerate as $b) {
        $board_class = new Board($b);
        $board_class->RegenerateAll();
        unset($b);
      }

			$msg = _gettext('Move complete.');

			if ($_POST['AJAX'])
				exitWithSuccessJSON($msg);
			else
				$tpl_page .= $msg . ' <br /><hr />';
		}

		$tpl_page .= '<form action="?action=movethread" method="get">
		  <input type="hidden" name="action" value="movethread" />
    	<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />

		<label for="id">'. _gettext('ID') . ':</label>
		<input type="text" name="id" />
		<br />

		<label for="board_from">'. _gettext('From') . ':</label>' .
		$this->MakeBoardListDropdown('board_from', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<label for="board_to">' ._gettext('To') . ':</label>' .
		$this->MakeBoardListDropdown('board_to', $this->BoardList($_SESSION['manageusername'])) .
		'<br />
		<input type="submit" value="'. _gettext('Move thread') . '" />';
	}

	/* Search for posts by IP */
	function ipsearch() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('IP Address Search') .'</h2><br />'. "\n";

		if (isset($_GET['ip']) && !empty($_GET['board'])) {
			if ($_GET['board'] == 'all') {
				$queryextra = "";
			} else {
				$queryextra = "`boardid` IN (" . $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "") . ") AND";
			}

			$results = $tc_db->GetAll("SELECT 
			  `" . KU_DBPREFIX . "postembeds`.`id` AS id, 
			  `" . KU_DBPREFIX . "postembeds`.`parentid` AS parentid, 
			  `" . KU_DBPREFIX . "postembeds`.`ip` AS ip, 
			  `" . KU_DBPREFIX . "postembeds`.`message` AS message, 
			  `" . KU_DBPREFIX . "postembeds`.`file` AS file, 
			  `" . KU_DBPREFIX . "postembeds`.`file_type` AS file_type, 
			  `" . KU_DBPREFIX . "boards`.`name` AS boardname 
			FROM `" . KU_DBPREFIX . "postembeds`, `" . KU_DBPREFIX . "boards` 
			WHERE 
			  ".$queryextra." `ipmd5` = '" . md5($_GET['ip']) . "' 
			  AND `IS_DELETED` = 0 
			  AND `" . KU_DBPREFIX . "boards`.`id` = `" . KU_DBPREFIX . "postembeds`.`boardid` 
			ORDER BY `boardid`");
			
			if (count($results) > 0) {
				foreach ($results as $line) {
					$tpl_page .= '<table border="1" width="100%">'. "\n" .
					'<tr><th width="10%">'. _gettext('Post Number') .'</th><th width="10%">'. _gettext('File') .'</th><th width="70%">'. _gettext('Message') .'</th><th width=10%">'. _gettext('IP Address') .'</th></tr>'. "\n";

					$real_parentid = ($line['parentid'] == 0) ? $line['id'] : $line['parentid'];

					$tpl_page .= '<tr><td><a href="'. KU_BOARDSPATH . '/'. $line['boardname'] . '/res/'. $real_parentid . '.html#'. $line['id'] . '">/'. $line['boardname'] . '/'. $line['id'] . '</td><td>'. (($line['file_type'] == 'jpg' || $line['file_type'] == 'gif' || $line['file_type'] == 'png') ? ('<a href="'. KU_WEBPATH .'/'. $line['boardname'] . '/src/'. $line['file'] . '.'. $line['file_type'] . '"><img border=0 src="'. KU_WEBPATH .'/'. $line['boardname'] . '/thumb/'. $line['file'] . 's.'. $line['file_type'] . '"></a>') : ('')) . '</td><td>'. $line['message'] . '</td><td>'. md5_decrypt($line['ip'], KU_RANDOMSEED) . '</tr>';
				}
				$tpl_page .= '</table>'. "\n";
			} else {
			$tpl_page .= _gettext('No results found for') .' '. $_GET['ip'] . '<br />'. "\n";
			}
		} else {
			$tpl_page .= '<form action="?" method="get">'. "\n" .
						'<input type="hidden" name="action" value="ipsearch" />'. "\n" .
						'<label for="board">'. _gettext('Board') . ':</label>'. "\n" .
						$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername']), true) . '<br />'. "\n" .
						'<label for="ip">'. _gettext('IP') . ':</label>'. "\n" .
						'<input type="text" name="ip" value="'. (isset($_GET['ip']) ? $_GET['ip'] : ''). '" /><br />'. "\n" .
						'<input type="submit" value="'. _gettext('IP Search') . '" />'. "\n";
		}
	}

	/* Search for text in posts */
	function search() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		if (isset($_GET['query'])) {
			$search_query = $_GET['query'];
			if (isset($_GET['s'])) {
				$s = $_GET['s'];
			} else {
				$s = 0;
			}
			$search_query_array = explode('KUSABA_AND', $search_query);
			$trimmed = trim($search_query);
			$limit = 10;
			if ($trimmed == '') {
				$tpl_page .= _gettext('Please enter a search query.');
				exit;
			}
			$boardlist = $this->BoardList($_SESSION['manageusername']);
			$likequery = '';
			foreach ($search_query_array as $search_split) {
				$likequery .= "`message` LIKE " . $tc_db->qstr(str_replace('_', '\_', $search_split)) . " AND ";
			}
			$likequery = substr($likequery, 0, -4);
			$query = '';
			$query .= "SELECT `" . KU_DBPREFIX . "posts`.`id` AS id, `" . KU_DBPREFIX . "posts`.`parentid` AS parentid, `" . KU_DBPREFIX . "posts`.`message` AS message, `" . KU_DBPREFIX . "boards`.`name` AS boardname FROM `" . KU_DBPREFIX . "posts`, `" . KU_DBPREFIX . "boards` WHERE `IS_DELETED` = 0 AND " . $likequery . " AND `" . KU_DBPREFIX . "boards`.`id` = `" . KU_DBPREFIX . "posts`.`boardid` ORDER BY `timestamp` DESC";

			$numresults = $tc_db->GetAll($query);
			$numrows = count($numresults);
			if ($numrows == 0) {
				$tpl_page .= '<h4>'. _gettext('Results') . '</h4>';
				$tpl_page .= '<p>'. _gettext('Sorry, your search returned zero results.') . '</p>';
			} else {
				$query .= " LIMIT $limit OFFSET $s";
				$results = $tc_db->GetAll($query);
				$tpl_page .= '<p style="font-size: 1.5em;">'. _gettext('Results for') .': <strong>'. $search_query . '</strong></p>';
				$count = 1 + $s;
				foreach ($results as $line) {
					$tpl_page .= '<span style="font-size: 1.5em;">'. $count . '.</span> <span style="font-size: 1.3em;">'. _gettext('Board') .': /'. $line['boardname'] . '/, <a href="'.KU_BOARDSPATH . '/'. $line['boardname'] . '/res/';
					if ($line['parentid'] == 0) {
						$tpl_page .= $line['id'] . '.html">';
					} else {
						$tpl_page .= $line['parentid'] . '.html#'. $line['id'] . '">';
					}

					if ($line['parentid'] == 0) {
						$tpl_page .= _gettext('Thread') .' #'. $line['id'];
					} else {
						$tpl_page .= _gettext('Thread') .' #'. $line['parentid'] . ', Post #'. $line['id'];
					}
					$tpl_page .= '</a></span>';

					$regexp = '/(';
					foreach ($search_query_array as $search_word) {
						$regexp .= preg_quote($search_word) . '|';
					}
					$regexp = substr($regexp, 0, -1) . ')/';
					//$line['message'] = preg_replace_callback($regexp, array(&$this, 'search_callback'), stripslashes($line['message']));
					$line['message'] = stripslashes($line['message']);
					$tpl_page .= '<fieldset>'. $line['message'] . '</fieldset><br />';
					$count++;
				}
				$currPage = (($s / $limit) + 1);
				$tpl_page .= '<br />';
				if ($s >= 1) {
					$prevs = ($s - $limit);
					$tpl_page .= "&nbsp;<a href=\"?action=search&s=$prevs&query=" . urlencode($search_query) . "\">&lt;&lt; "._gettext('Prev')." 10</a>&nbsp&nbsp;";
				}
				$pages = intval($numrows / $limit);
				if ($numrows % $limit) {
					$pages++;
				}
				if (!((($s + $limit) / $limit) == $pages) && $pages != 1) {
					$news = $s + $limit;
					$tpl_page .= "&nbsp;<a href=\"?action=search&s=$news&query=" . urlencode($search_query) . "\">"._gettext('Next')." 10 &gt;&gt;</a>";
				}

				$a = $s + ($limit);
				if ($a > $numrows) {
					$a = $numrows;
				}
				$b = $s + 1;

				$tpl_page .= $this->search_results_display($a, $b, $numrows);
			}
		}

		$tpl_page .= '<form action="?" method="get">
		<input type="hidden" name="action" value="search" />
		<input type="hidden" name="s" value="0" />

		<strong>'. _gettext('Query') .'</strong>:<br /><input type="text" name="query" ';
		if (isset($_GET['query'])) {
			$tpl_page .= 'value="'. $_GET['query'] . '" ';
		}
		$tpl_page .= 'size="52" /><br />

		<input type="submit" value="'. _gettext('Search') .'" /><br /><br />

		<h1>'. _gettext('Search Help') .'</h1>

		'. _gettext('Separate search terms with the word <strong>KUSABA_AND</strong>') .' <br /><br />

		'. _gettext('To find a single phrase anywhere in a post\'s message, use:') .'<br />
		%'. _gettext('some phrase here') .'%<br /><br />

		'. _gettext('To find a phrase at the beginning of a post\'s message:') .'<br />
		'. _gettext('some phrase here') .'%<br /><br />

		'. _gettext('To find a phrase at the end of a post\'s message:') .'<br />
		%'. _gettext('some phrase here') .'<br /><br />

		'. _gettext('To find two phrases anywhere in a post\'s message, use:') .'<br />
		%'. _gettext('first phrase here') .'%KUSABA_AND%'. _gettext('second phrase here') .'%<br /><br />

		</form>';
	}
	function search_callback($matches) {
		print_r($matches);
		return '<strong>'. $matches[0] . '</strong>';
	}

	function search_results_display($a, $b, $numrows) {
		return '<p>'. _gettext('Results') . ' <strong>'. $b . '</strong> to <strong>'. $a . '</strong> of <strong>'. $numrows . '</strong></p>'. "\n" .
		'<hr />';
	}
	// Credits to Eman for this code
	function viewthread() {
		global $tc_db, $tpl_page;
		$tpl_page .= '<h2>'. _gettext('View Threads (including deleted)') .'</h2>';
		$board = isset($_GET['board']) ? $_GET['board'] : '';
		$thread = isset($_GET['thread']) ? $_GET['thread'] : '';
		if (!$thread ) {
			$thread = "0";
		}
		if (!$board) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `name`, `id` FROM `". KU_DBPREFIX . "boards` ORDER BY `name` ASC");
			$tpl_page .= "
				<style type=\"text/css\">
				input {
					display: inline !important;
					width: auto !important;
					float: none !important;
					margin-bottom: 0px !important;
				}
				th,td {
					font-size: 14px !important;
				}
				</style>";
			$tpl_page .= '<form method="get" action=""><input type="hidden" name="action" value="viewthread" />'. _gettext('Select Board') .': <select name="board">';
			foreach ($results as $line) {
				$name = $line['name'];
				$id =	$line['id'];
				$tpl_page .= "<option value=\"$name\">/$name/</option>";
			}
			$tpl_page .= '</select>&nbsp;<input type=submit value="'. _gettext('Go') .'" />';
		} else {
			$board_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `". KU_DBPREFIX . "boards` WHERE `name` = ".$tc_db->qstr($board));
			$tpl_page .= "
				<style type=\"text/css\">
				input {
					display: inline !important;
					width: auto !important;
					float: none !important;
					margin-bottom: 0px !important;
				}
				th,td {
					font-size: 14px !important;
				}
				</style>
				<form method=\"get\" action=\"\">
				<input type=\"hidden\" name=\"action\" value=\"viewthread\" />
				<input type=\"hidden\" name=\"board\" value=\"$board\" />";
			if ($thread == "0" ) {
				$tpl_page .= "<h2>". sprintf(_gettext('All threads on /%s/'), $board) ."</h2>";
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = $board_id AND (`id` = ".$tc_db->qstr($thread)." OR `parentid` = ".$tc_db->qstr($thread).") ORDER BY `id` DESC LIMIT 500");
			} else {
				$tpl_page .= "<h2>". sprintf(_gettext('Thread %s on /%s/'), $thread, $board) ."</h2>";
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = $board_id AND (`id` = ".$tc_db->qstr($thread)." OR `parentid` = ".$tc_db->qstr($thread).") ORDER BY `id` ASC");
			}
			$time = round(microtime(), 5);
			$first = "1";
			foreach ($results as $line) {
				$bans = "";
				$id = $line['id'];
				$ip = md5_decrypt($line['ip'], KU_RANDOMSEED);
				$filename = $line['file'];
				$file_original = $line['file_original'];
				$filetype = $line['file_type'];
				$filesize_formatted = $line['file_size_formatted'];
				$image_w = $line['image_w'];
				$image_h = $line['image_h'];
				$message = $line['message'];
				$name = $line['name'];
				$tripcode = $line['tripcode'];
				$timestamp = date(r, $line['timestamp']);
				$subject = $line['subject'];
				$posterauthority = $line['posterauthority'];
				$deleted = isset($line['IS_DELETED']) ? $line['IS_DELETED'] : $line['is_deleted'] ;
				if ($thread == "0") {
					$view = "<a href=\"?action=viewthread&board=$board&thread=$id\">[". _gettext('View') ."]</a>";
				} else {
					$view = "";
				}
				if ($name == "") {
					$name = _gettext('Anonymous');
				} else {
					$name = "<font color=\"blue\">$name</font>";
				}
				if ($tripcode != "") {
					$tripcode = "<font color=\"green\">!$tripcode</font>";
				}
				if ($subject != "") {
					$subject = "<font color=\"red\">$subject</font> - ";
				}
				if ($posterauthority == "1") {
					$posterauthority = "<font color=\"purple\"><strong>##Admin##</strong></font>";
				} elseif ($posterauthority == "2") {
					$posterauthority = "<font color=\"red\"><strong>##Mod##</strong></font>";
				} else {
					$posterauthority = "";
				}
				if ($deleted == "1") {
					$deleted = "<font color=green><blink><strong>". _gettext('DELETED') ."</strong></blink></font> - ";
				} else {
					$deleted = "";
					if ($first == "1") {
						$bans = "</td><td width=80px style=\"text-align: right; vertical-align: top;\">[<a href=\"manage_page.php?action=&boarddir=$board&delthreadid=$id\">D</a> <a href=\"manage_page.php?action=delposts&boarddir=$board&delthreadid=$id&postid=$id\">&amp;</a> <a href=\"manage_page.php?action=bans&banboard=$board&banpost=$id\">B</a>]";
					} else {
						$bans = "</td><td width=80px style=\"text-align: right; vertical-align: top;\">[<a href=\"manage_page.php?action=delposts&boarddir=$board&delpostid=$id\">D</a> <a href=\"manage_page.php?action=delposts&boarddir=$board&delpostid=$id&postid=$id\">&amp;</a> <a href=\"manage_page.php?action=bans&banboard=$board&banpost=$id\">B</a>]";
					}
				}


				if ($bans == "") {
				$bans = "</td><td>&nbsp;</td>";
				}
				$tpl_page .= "
					<table style=\"text-align: left; width: 100%;\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">
						<tbody>
							<tr>
								<td style=\"vertical-align: top;\">$deleted$subject$name$tripcode $posterauthority $timestamp ". _gettext('No.') ." $id ". _gettext('IP') .": $ip $view $bans
								</td>
							</tr>
				";
				if ($filename != "") {
					$tpl_page .= "
						<tr>
							<td colspan=\"2\" style=\"vertical-align: top;\">". _gettext('File') .": <a href=\"". KU_WEBPATH ."/$board/src/$filename.$filetype\" target=\"_blank\">$filename.$filetype</a> -( $filesize_formatted, {$image_w}x{$image_h}, $file_original.$filetype )</td>
						</tr>
					";
				}
				$tpl_page .= "
				</tbody></table>
								<table style=\"text-align: left; width: 100%;\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\">
						<tbody>
				<tr>";


				if ($filename != "") {
					$tpl_page .= "
						<td style=\"vertical-align: top; width: 200px;\"><center><a href=\"". KU_WEBPATH ."/$board/src/$filename.$filetype\" target=\"_blank\"><img src=\"". KU_WEBPATH ."/$board/thumb/{$filename}s.$filetype\" border=\"0\"></a></center></td>
					";
				}
				if ($message == "") {
					$message = "&nbsp;";
				}
				$tpl_page .= "<td style=\"vertical-align: top; height: 100%;\">$message</td></tr></tbody></table><br />";
				$first = "0";
			}
			$time2 = round(microtime(), 5);
			$generation = $time2 - $time;
			$generation = abs($generation);
			$tpl_page .= '
			'. _gettext('Render Time') .':'. $generation .' '._gettext('Seconds').'
			<!--<h2>'. _gettext('Ban') .'</h2>
			'. _gettext('Reason') .': <input type="text" name="banreason" value="'. _gettext('You Are Banned') .'" />&nbsp;&nbsp;
			'. _gettext('Duration') .': <input type="text" name="banduration" value="0" />&nbsp;&nbsp;
			'. _gettext('Appeal') .': <input type="text" name="banappeal" value="0" />&nbsp;&nbsp;
			<input type="submit" value="'. _gettext('Submit') .'" />-->
			</form>';
		}
	}

	/* View a thread marked as deleted */
	function viewdeletedthread() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('View deleted thread') . '</h2><br />'. "\n";

		if (isset($_GET['board']) && isset($_GET['threadid']) && $_GET['threadid'] > 0) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
			foreach ($results as $line) {
				$board_id = $line['id'];
				$board_dir = $line['name'];
			}
			if (count($results) > 0) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts_" . $board_dir . "` WHERE `id` = " . $tc_db->qstr($_GET['threadid']) . "");
				foreach ($results as $line) {
					$thread_isdeleted = $line['IS_DELETED'];
					$thread_parentid = $line['parentid'];
				}
				if ($thread_isdeleted == 1 && $thread_parentid == 0) {
					foreach ($results as $line) {
						if ($line['name'] != '') {
							$name = $line['name'];
						} else {
							$name =  _gettext('Anonymous') .' ';
						}
						$tpl_page .= '<div style="width: 75%; border: 1px solid #CCC; padding: 5px;">'. "\n";
						$tpl_page .= $name . $line['tripcode'] . formatDate($line['postedat']) . ' | '. _gettext('No.') .' '. $line['id'] . ' | '. _gettext('IP') .': '. md5_decrypt($line['ip'], KU_RANDOMSEED) . '<br />'. "\n";
						if (isset($line['filename'])) {
							$tpl_page .= '<a href="'. KU_WEBPATH . '/'. $_GET['board'] . '/src/'. $line['filename'] . '.'. $line['filetype'] . '" target="_blank">'. $line['filename'] . '.'. $line['filetype'] . '</a><br />'. "\n" .
										'<a href="'. KU_WEBPATH . '/'. $_GET['board'] . '/src/'. $line['filename'] . '.'. $line['filetype'] . '" target="_blank"><img src="'. KU_WEBPATH . '/'. $_GET['board'] . '/thumb/'. $line['filename'] . 's.'. $line['filetype'] . '" border="0" alt="'. $line['filename'] . '.'. $line['filetype'] . '" /></a>'. "\n";
						}
						$tpl_page .= $line['message'];
						$tpl_page .= '</div><br />';
					}
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts_" . $board_dir . "` WHERE `parentid` = " . $tc_db->qstr($_GET['threadid']) . "");
					foreach ($results as $line) {
						if ($line['name'] != '') {
							$name = $line['name'] . ' ';
						} else {
							$name =  _gettext('Anonymous') .' ';
						}
						$tpl_page .= '<div style="width: 75%; border: 1px solid #CCC; padding: 5px;">'. "\n";
						$tpl_page .= $name . $line['tripcode'] . formatDate($line['postedat']) . ' | No. '. $line['id'] . ' | IP: '. md5_decrypt($line['ip'], KU_RANDOMSEED) . '<br />'. "\n";
						if ($line['filename'] != '') {
							$tpl_page .= '<a href="'. KU_WEBPATH . '/'. $_GET['board'] . '/src/'. $line['filename'] . '.'. $line['filetype'] . '" target="_blank">'. $line['filename'] . '.'. $line['filetype'] . '</a><br />'. "\n" .
										'<a href="'. KU_WEBPATH . '/'. $_GET['board'] . '/src/'. $line['filename'] . '.'. $line['filetype'] . '" target="_blank"><img src="'. KU_WEBPATH . '/'. $_GET['board'] . '/thumb/'. $line['filename'] . 's.'. $line['filetype'] . '" border="0" alt="'. $line['filename'] . '.'. $line['filetype'] . '" /></a>'. "\n";
						}
						$tpl_page .= $line['message'];
						$tpl_page .= '</div><br />';
					}
				} else {
					$tpl_page .=  _gettext('That\'s either not a thread, or it\'s not deleted.') ;
				}
			}
		} else {

		$tpl_page .= '<form method="get" action="?">'. "\n" .
					'<input type="hidden" name="action" value="viewthread" />'. "\n" .
					'<label for="board">'. _gettext('Board') . ':</label>'. "\n" .
					$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) . "\n" .
					'<br />'. "\n" .
					'<label for="threadid">'. _gettext('Thread') . ':</label>'. "\n" .
					'<input type="text" name="threadid" /><br />'. "\n" .
					'<input type="submit" value="'. _gettext('View deleted thread') . '" />'. "\n" .
					'</form>';
		}
	}

	/* Add, view, and delete filetypes */
	function editfiletypes() {
		global $tc_db, $tpl_page, $yac;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Edit filetypes') . '</h2><br />';
		if (isset($_GET['do'])) {
			if ($_GET['do'] == 'addfiletype') {
				if (isset($_POST['filetype']) || isset($_POST['image'])) {
          $this->CheckToken($_POST['token']);
					if ($_POST['filetype'] != '' && $_POST['image'] != '') {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "filetypes` ( `filetype` , `mime` , `image` , `image_w` , `image_h` ) VALUES ( " . $tc_db->qstr($_POST['filetype']) . " , " . $tc_db->qstr($_POST['mime']) . " , " . $tc_db->qstr($_POST['image']) . " , " . $tc_db->qstr($_POST['image_w']) . " , " . $tc_db->qstr($_POST['image_h']) . " )");
						$tpl_page .= _gettext('Filetype added.');
					}
				} else {
					$tpl_page .= '<form action="?action=editfiletypes&do=addfiletype" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="filetype">'. _gettext('Filetype') .':</label>
					<input type="text" name="filetype" />
					<div class="desc">'. _gettext('The extension this will be applied to. <strong>Must be lowercase</strong>') .'</div><br />

					<label for="mime">'. _gettext('MIME type') .':</label>
					<input type="text" name="mime" />
					<div class="desc">'. _gettext('The MIME type which must be present with an image uploaded in this type. Leave blank to disable.') .'</div><br />

					<label for="image">Image:</label>
					<input type="text" name="image" value="generic.png" />
					<div class="desc">'. _gettext('The image which will be used, found in inc/filetypes.') .'</div><br />

					<label for="image_w">'. _gettext('Image width') .':</label>
					<input type="text" name="image_w" value="48" />
					<div class="desc">'. _gettext('The width of the image. Needs to be set to prevent the page from jumping around while images load.') .'</div><br />

					<label for="image_h">'. _gettext('Image height') .':</label>
					<input type="text" name="image_h" value="48" />
					<div class="desc">'. _gettext('The height of the image. Needs to be set to prevent the page from jumping around while images load.') .'.</div><br />

					<input type="submit" value="'. _gettext('Add') .'" />

					</form>';
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'editfiletype' && $_GET['filetypeid'] > 0) {
				if (isset($_POST['filetype'])) {
					if ($_POST['filetype'] != '' && $_POST['image'] != '') {
            $this->CheckToken($_POST['token']);
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "filetypes` SET `filetype` = " . $tc_db->qstr($_POST['filetype']) . " , `mime` = " . $tc_db->qstr($_POST['mime']) . " , `image` = " . $tc_db->qstr($_POST['image']) . " , `image_w` = " . $tc_db->qstr($_POST['image_w']) . " , `image_h` = " . $tc_db->qstr($_POST['image_h']) . " WHERE `id` = " . $tc_db->qstr($_GET['filetypeid']) . "");
						if (I0_YAC) {
							$yac->delete('filetype|'. $_POST['filetype']);
						}
						$tpl_page .= _gettext('Filetype updated.');
					}
				} else {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "filetypes` WHERE `id` = " . $tc_db->qstr($_GET['filetypeid']) . "");
					if (count($results) > 0) {
						foreach ($results as $line) {
							$tpl_page .= '<form action="?action=editfiletypes&do=editfiletype&filetypeid='. $_GET['filetypeid'] . '" method="post">
              <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="filetype">'. _gettext('Filetype') .':</label>
							<input type="text" name="filetype" value="'. $line['filetype'] . '" />
							<div class="desc">'. _gettext('The extension this will be applied to. <strong>Must be lowercase</strong>') .'</div><br />

							<label for="mime">'. _gettext('MIME type') .':</label>
							<input type="text" name="mime" value="'. $line['mime'] . '" />
							<div class="desc">'. _gettext('The MIME type which must be present with an image uploaded in this type. Leave blank to disable.') .'</div><br />

							<label for="image">'. _gettext('Image') .':</label>
							<input type="text" name="image" value="'. $line['image'] . '" />
							<div class="desc">'. _gettext('The image which will be used, found in inc/filetypes.') .'</div><br />

							<label for="image_w">'. _gettext('Image width') .':</label>
							<input type="text" name="image_w" value="'. $line['image_w'] . '" />
							<div class="desc">'. _gettext('The width of the image. Needs to be set to prevent the page from jumping around while images load.') .'</div><br />

							<label for="image_h">'. _gettext('Image height') .':</label>
							<input type="text" name="image_h" value="'. $line['image_h'] . '" />
							<div class="desc">'. _gettext('The height of the image. Needs to be set to prevent the page from jumping around while images load.') .'.</div><br />

							<input type="submit" value="'. _gettext('Edit') .'" />

							</form>';
						}
					} else {
						$tpl_page .= _gettext('Unable to locate a filetype with that ID.');
					}
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'deletefiletype' && $_GET['filetypeid'] > 0) {
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "filetypes` WHERE `id` = " . $tc_db->qstr($_GET['filetypeid']) . "");
				$tpl_page .= _gettext('Filetype deleted.');
				$tpl_page .= '<br /><hr />';
			}
		}
		$tpl_page .= '<a href="?action=editfiletypes&do=addfiletype">'. _gettext('Add filetype') .'</a><br /><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "filetypes` ORDER BY `filetype` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('ID') .'</th><th>'. _gettext('Filetype') .'</th><th>'. _gettext('Image') .'</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. $line['id'] . '</td><td>'. $line['filetype'] . '</td><td>'. $line['image'] . '</td><td>[<a href="?action=editfiletypes&do=editfiletype&filetypeid='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=editfiletypes&do=deletefiletype&filetypeid='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= _gettext('There are currently no filetypes.');
		}
	}

	/* Add, view, and delete sections */
	function editsections() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Edit sections') . '</h2><br />';
		if (isset($_GET['do'])) {
			if ($_GET['do'] == 'addsection') {
				if (isset($_POST['name'])) {
					if ($_POST['name'] != '' && $_POST['abbreviation'] != '') {
            $this->CheckToken($_POST['token']);
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "sections` ( `name` , `abbreviation` , `order` , `hidden` ) VALUES ( " . $tc_db->qstr($_POST['name']) . " , " . $tc_db->qstr($_POST['abbreviation']) . " , " . $tc_db->qstr($_POST['order']) . " , '" . (isset($_POST['hidden']) ? '1' : '0') . "' )");
						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$menu_class = new Menu();
						$menu_class->Generate();
						$tpl_page .= _gettext('Section added.');
					}
				} else {
					$tpl_page .= '<form action="?action=editsections&do=addsection" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="name">'. _gettext('Name') .':</label><input type="text" name="name" /><div class="desc">'. _gettext('The name of the section') .'</div><br />
					<label for="abbreviation">'. _gettext('Abbreviation') .':</label><input type="text" name="abbreviation" /><div class="desc">'. _gettext('Abbreviation (less then 10 characters)') .'</div><br />
					<label for="order">'. _gettext('Order') .':</label><input type="text" name="order" /><div class="desc">'. _gettext('Order to show this section with others, in ascending order') .'</div><br />
					<label for="hidden">'. _gettext('Hidden') .':</label><input type="checkbox" name="hidden" /><div class="desc">'. _gettext('If checked, this section will be collapsed by default when a user visits the site.') .'</div><br />
					<input type="submit" value="'. _gettext('Add') .'" />
					</form>';
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'editsection' && $_GET['sectionid'] > 0) {
				if (isset($_POST['name'])) {
					if ($_POST['name'] != '' && $_POST['abbreviation'] != '') {
            $this->CheckToken($_POST['token']);
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "sections` SET `name` = " . $tc_db->qstr($_POST['name']) . " , `abbreviation` = " . $tc_db->qstr($_POST['abbreviation']) . " , `order` = " . $tc_db->qstr($_POST['order']) . " , `hidden` = '" . (isset($_POST['hidden']) ? '1' : '0') . "' WHERE `id` = '" . $_GET['sectionid'] . "'");
						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$menu_class = new Menu();
						$menu_class->Generate();
						$tpl_page .= _gettext('Section updated.');
					}
				} else {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "sections` WHERE `id` = " . $tc_db->qstr($_GET['sectionid']) . "");
					if (count($results) > 0) {
						foreach ($results as $line) {
							$tpl_page .= '<form action="?action=editsections&do=editsection&sectionid='. $_GET['sectionid'] . '" method="post">
              <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<input type="hidden" name="id" value="'. $_GET['sectionid'] . '" />

							<label for="name">'. _gettext('Name') .':</label>
							<input type="text" name="name" value="'. $line['name'] . '" />
							<div class="desc">'. _gettext('The name of the section') .'</div><br />

							<label for="abbreviation">'. _gettext('Abbreviation') .':</label>
							<input type="text" name="abbreviation" value="'. $line['abbreviation'] . '" />
							<div class="desc">'. _gettext('Abbreviation (less then 10 characters)') .'</div><br />

							<label for="order">'. _gettext('Order') .':</label>
							<input type="text" name="order" value="'. $line['order'] . '" />
							<div class="desc">'. _gettext('Order to show this section with others, in ascending order') .'</div><br />

							<label for="hidden">'. _gettext('Hidden') .':</label>
							<input type="checkbox" name="hidden" '. ($line['hidden'] == 0 ? '' : 'checked') . ' />
							<div class="desc">'. _gettext('If checked, this section will be collapsed by default when a user visits the site.') .'</div><br />

							<input type="submit" value="'. _gettext('Edit') .'" />

							</form>';
						}
					} else {
						$tpl_page .= _gettext('Unable to locate a section with that ID.');
					}
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'deletesection' && isset($_GET['sectionid'])) {
				if ($_GET['sectionid'] > 0) {
					$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "sections` WHERE `id` = " . $tc_db->qstr($_GET['sectionid']) . "");
					require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
					$menu_class = new Menu();
					$menu_class->Generate();
					$tpl_page .= _gettext('Section deleted.') . '<br /><hr />';
				}
			}
		}
		$tpl_page .= '<a href="?action=editsections&do=addsection">'. _gettext('Add section') .'</a><br /><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "sections` ORDER BY `order` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table border="1" width="100%"><tr><th>'.('ID') .'</th><th>'.('Order') .'</th><th>'. _gettext('Abbreviation') .'</th><th>'. _gettext('Name') .'</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. $line['id'] . '</td><td>'. $line['order'] . '</td><td>'. $line['abbreviation'] . '</td><td>'. $line['name'] . '</td><td>[<a href="?action=editsections&do=editsection&sectionid='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=editsections&do=deletesection&sectionid='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= _gettext('There are currently no sections.');
		}
	}

	function rebuild20json() {
		$this->BoardOwnersOnly();

		$cl20 = new Cloud20();
		$cl20->rebuild();
	}

	/* Rebuild all boards */
	function rebuildall($do=false) {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Rebuild all HTML files') . '</h2><br />';
		if (!$do && !isset($_GET['do'])) {
			$tpl_page .= '<form action="?action=rebuildall&do=rebuildall" method="post">
				<input type="submit" value="'. _gettext('Execute') .'" /></form>';
		}
		else {
			$time_start = time();
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards`");
			$need_overboard = false;
			foreach ($results as $line) {
				$time_current = time();
				$board_class = new Board($line['name']);
				$board_class->RegenerateAll(true); // (true = Except boardlist)
				if (I0_OVERBOARD_ENABLED && !$need_overboard && $board_class->board['section'] != '0' && $board_class->board['hidden'] == '0') {
					$need_overboard = true;
					$over_boardlist = $board_class->board['boardlist'];
				}
				$tpl_page .= sprintf(_gettext('Regenerated %s'), '/'. $line['name'] . '/ ') . sprintf(_gettext('in %s seconds.'), time() - $time_current) .'<br />';
				unset($board_class);
				flush();
			}
			if ($need_overboard) {
				$time_current = time();
				RegenerateOverboard($over_boardlist);
				$tpl_page .= sprintf(_gettext('Regenerated overboard') . ' ' . _gettext('in %s seconds.'), time() - $time_current) .'<br />';
			}
			require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
			$menu_class = new Menu();
			$menu_class->Generate();
			$tpl_page .=  _gettext('Regenerated menu pages') .'<br />';
			$tpl_page .= sprintf(_gettext('Rebuild complete. Took <strong>%d</strong> seconds.'), time() - $time_start);
			$this->rebuild20json();
			management_addlogentry(_gettext('Rebuilt all boards and threads'), 2);
			unset($board_class);
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Boards Pages
	* +------------------------------------------------------------------------------+
	*/

	function gentimestamp() {
		return preg_replace('/[.\ ]/', '', microtime());
	}

	function styles() {
		global $tc_db, $tpl_page;
		$this->BoardOwnersOnly();
		$tokeninput = '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />';
		$interrupt_return = false;
		if(isset($_POST['stylename']) && strlen($_POST['stylename'])) {
			if($this->ctype_alnum_($_POST['stylename'])) {
				$location = KU_ROOTDIR.'css/custom/' . $_POST['stylename'] . '.css';
				if(file_exists($location)) {
					$this->CheckToken($_POST['token']);
					$owner = $tc_db->GetOne("SELECT `owner` FROM `" . KU_DBPREFIX . "customstyles` WHERE `name` = '".$_POST['stylename']."'");
					if($owner == $_SESSION['manageusername']) {
						if(isset($_GET['editstyle']) && (isset($_POST['newstyletxt']) || isset($_FILES['newstylefile']))) {
							// user wants to edit style
							$edit_successful = false;
							if(isset($_POST['newstyletxt'])) {
								$txt_failed = false;
								$newstyle = trim($_POST['newstyletxt']);
								$style_size = strlen($newstyle);
								if($style_size) {
									if($style_size < KU_MAX_CSS_SIZE) {
										$edit_successful = 'text';
									}
									else $txt_failed = sprintf(_gettext("Uploaded CSS is too big"), KU_MAX_CSS_SIZE);
								}
								else $txt_failed = _gettext('Style must not be empty');
							}
							if($txt_failed) {
								if(isset($_FILES['newstylefile']) && filesize($_FILES['newstylefile']['tmp_name'])) {
									if($_FILES["newstylefile"]["type"] == 'text/css' && end(explode(".", $_FILES["newstylefile"]["name"])) == 'css') {
										if(filesize($_FILES['newstylefile']['tmp_name']) < KU_MAX_CSS_SIZE) {
											$edit_successful = 'file';
										}
										else $tpl_page .= _gettext('Uploaded CSS is too big');
									}
									else $tpl_page .= _gettext('Wrong file type');
								}
								else $tpl_page .= $txt_failed;
							}
							if($edit_successful) {
								if($edit_successful == 'file') {
									$css = file_get_contents($_FILES['newstylefile']['tmp_name']);
								}
								else {
									$css = $newstyle;
								}
								$css_error = check_css($css);
								if(!$css_error) {
									if($edit_successful == 'file') {
										move_uploaded_file($_FILES["newstylefile"]["tmp_name"], $location);
									}
									else {
										file_put_contents($location, $newstyle);
									}
									$interrupt_return = false;
									//update version to force recache
									$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "customstyles` SET `version`=`version`+1 WHERE `name` = '". $_POST['stylename'] ."'");
									$tpl_page .= _gettext('Style successfully updated.');
								}
								else
									$tpl_page .= $css_error;
							}
						}
						elseif(isset($_GET['setname'])) {
							//user wants to set name for fresh tempfile (stylename == tempfile)
							if($this->ctype_alnum_($_POST['newstylename'])) {
								$taken_name = $tc_db->GetOne("SELECT COUNT(1) FROM `customstyles` WHERE `name` = '".$_POST['newstylename']."'");
								if($taken_name == 0) {
									$interrupt_return = false;
									rename($location, KU_ROOTDIR.'css/custom/' . $_POST['newstylename'] . '.css');
									$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "customstyles` SET `name` = '".$_POST['newstylename']."', `temporary` = '0' WHERE `name` = '". $_POST['stylename'] ."'");
									$tpl_page .= _gettext('New style').' '.$_POST['newstylename'].'.css '._gettext('has been created.').' '._gettext('Now you can use set it as default for you boards in Board Options.');
								}
								else {
									$tpl_page .= _gettext('This name is already taken.').' '._gettext('Set a unique name for your new style').'<br />
										<form method="post" action="?action=styles&setname">'.$tokeninput.
										'<input type="hidden" name="stylename" value="'.$_POST['stylename'].'" />
										<input type="text" name="newstylename" />.css
										<br><input type="submit"></form>';
								}
							}
							else $tpl_page .= _gettext('Style name must be alphanumeric.').' '._gettext('Set a unique name for your new style').'<br />
								<form method="post" action="?action=styles&setname">'.$tokeninput.
								'<input type="hidden" name="stylename" value="'.$_POST['stylename'].'" />
								<input type="text" name="newstylename" />.css
								<br><input type="submit"></form>';
						}
						elseif(isset($_GET['deletestyle'])) {
							$interrupt_return = false;
							//user wants to delete the style
							$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "customstyles` WHERE `name` = '".$_POST['stylename']."'");
							unlink($location);
							$tpl_page .= _gettext('Style has been deleted.');
						}
						else $tpl_page .= _gettext('Wrong request.');
						// ^ user specified the style but hasn't specified any action
					}
					else $tpl_page .= _gettext('You have no rights to manipulate this stylesheet.');
				}
				else {
					$tpl_page .= _gettext('For some reason the file is not present on the server');
					$zombie_entry = $tc_db->GetAll("SELECT COUNT(1) FROM `" . KU_DBPREFIX . "customstyles` WHERE `name` = '".$_POST['stylename']."'");
					if(count($zombie_entry) > 0) {
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "customstyles` WHERE `name` = '".$_POST['stylename']."'");
						$tpl_page .= ' '._gettext('and now it has been deleted from the database');
					}
				}
			}
			else $tpl_page .= _gettext('Style name must be alphanumeric.');
		}
		elseif(isset($_POST['newstyletxt']) || isset($_FILES['newstylefile'])) {
			$this->CheckToken($_POST['token']);
			$interrupt_return = true;
			$upload_successful = false;
			$tempname = 'temp'.($this->gentimestamp());
			//user uploaded OR pasted new style
			if(isset($_POST['newstyletxt'])) {
				$txt_failed = false;
				$newstyle = trim($_POST['newstyletxt']);
				$style_size = strlen($newstyle);
				if($style_size) {
				  if($style_size < KU_MAX_CSS_SIZE) {
				    $upload_successful = 'text';
				  }
				  else $txt_failed = sprintf(_gettext("Uploaded CSS is too big"), KU_MAX_CSS_SIZE);
				}
				else $txt_failed = _gettext('Style must not be empty');
			}
			if($txt_failed) {
				if(isset($_FILES['newstylefile']) && filesize($_FILES['newstylefile']['tmp_name'])) {
				  if($_FILES["newstylefile"]["type"] == 'text/css' && end(explode(".", $_FILES["newstylefile"]["name"])) == 'css') {
				    if(filesize($_FILES['newstylefile']['tmp_name']) < KU_MAX_CSS_SIZE) {
				      $upload_successful = 'file';
				    }
				    else $err .= _gettext('Uploaded CSS is too big');
				  }
				  else $err .= _gettext('Wrong file type');
				}
				else $err .= $txt_failed;

				if($_FILES["newstylefile"]["type"] == 'text/css' && end(explode(".", $_FILES["newstylefile"]["name"])) == 'css'){
					move_uploaded_file($_FILES["newstylefile"]["tmp_name"], KU_ROOTDIR.'css/custom/' . $tempname . '.css');
					$upload_successful = true;
				}
				else {
					$err .= _gettext('Wrong file type');
				}
			}
			if($upload_successful) {
				if($upload_successful == 'file') {
				  $css = file_get_contents($_FILES['newstylefile']['tmp_name']);
				}
				else {
				  $css = $newstyle;
				}
				$css_error = check_css($css);
				if(!$css_error) {
				  if($upload_successful == 'file') {
				    move_uploaded_file($_FILES["newstylefile"]["tmp_name"], KU_ROOTDIR.'css/custom/' . $tempname . '.css');
				  }
				  else {
				    file_put_contents(KU_ROOTDIR.'css/custom/'.$tempname.'.css', $newstyle);
				  }
				  $tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "customstyles` (`name`, `owner`, `version`) VALUES ('".$tempname."', '".$_SESSION['manageusername']."', '0')");
				  //now display style renaming form
				  $tpl_page .= _gettext('Set a unique name for your new style').'<br />
				  	<form method="post" action="?action=styles&setname">'.$tokeninput.
				  	'<input type="hidden" name="stylename" value="'.$tempname.'" />
				  	<input type="text" name="newstylename" />.css
				  	<br><input type="submit"></form>';
				}
				else
				  $tpl_page .= $css_error;
			}
			else $tpl_page .= $err;
		}
		else {
			if(isset($_GET['editstyle'])) {
				$interrupt_return = true;
				// display editstyle page
				if($this->ctype_alnum_($_GET['editstyle'])) {
					$cssfile = file_get_contents(KU_ROOTDIR . 'css/custom/' . $_GET['editstyle'] .'.css');
					if($cssfile) {
						$tpl_page .= '<h2>'. _gettext('Edit') .' '. $_GET['editstyle'] .'.css</h2><br />
							<form method="post" action="?action=styles&editstyle" enctype="multipart/form-data">'.$tokeninput.
							'<textarea name="newstyletxt" rows="25" cols="80">'.htmlspecialchars($cssfile).'</textarea><br />
							<input type="hidden" name="stylename" value="'.$_GET['editstyle'].'" />'
							._gettext('Or upload from file').':<br />
							<input type="file" name="newstylefile" size="35" /><br />
							<input type="submit"></form>  <a href="?action=styles"><button>'._gettext('Cancel').'</button></a><br /><br />';
					}
				}
				else $tpl_page .= _gettext('Style name must be alphanumeric.');
			}
			elseif(isset($_GET['deletestyle'])) {
				$interrupt_return = true;
				// display delete style approval form
				if($this->ctype_alnum_($_GET['deletestyle'])) {
					$style_used = $tc_db->GetAll("SELECT `name` FROM `" . KU_DBPREFIX . "boards` WHERE `defaultstyle` = '".$_GET['deletestyle']."'");
					if(count($style_used) > 0) {
						// Inform user that the style he wants to delete is currently used on some boards
						$tpl_page .= _gettext('Style you want to delete').' <b>('.$_GET['deletestyle'].'.css)</b> '._gettext('is currently used on').' <b>';
						foreach($style_used as $brd) {
							$brdlist[] = $brd['name'];
						}
						$tpl_page .= implode(', ', $brdlist).'</b><br />'._gettext('Delete anyway?').'<br />';
					}
					else {
						$tpl_page .= _gettext('Are you sure you want to delete').' <b>'.$_GET['deletestyle'].'.css</b>? '.'<br />';
					}
					$tpl_page .= '<form action="manage_page.php?action=styles&deletestyle" method="post">'.$tokeninput.
		          		'<input type="hidden" name="stylename" value="'.$_GET['deletestyle'].'" />
						<input type="submit" value="'. _gettext('Continue') .'" />
						</form><br />';
				}
				else $tpl_page .= _gettext('Style name must be alphanumeric.');
			}
			elseif(isset($_GET['newstyle'])) {
				$interrupt_return = true;
				// display new style creation page
				$tpl_page .= '<h2>'. _gettext('Create new style') . '</h2><br />
					<form method="post" action="?action=styles" enctype="multipart/form-data">'.$tokeninput.
					'<textarea name="newstyletxt" rows="25" cols="80"></textarea><br />'
					._gettext('Or upload from file').':<br />
					<input type="file" name="newstylefile" size="35" /><br />
					<input type="submit">';
			}
			elseif(isset($_GET['setname'])) {
				$interrupt_return = true;
				$tpl_page .= _gettext('Set a unique name for your new style').'<br />
					<form method="post" action="?action=styles&setname">'.$tokeninput.
					'<input type="hidden" name="stylename" value="'.$_GET['setname'].'" />
					<input type="text" name="newstylename" />.css
					<br><input type="submit"></form>';
			}
		}
		if(!$interrupt_return) {
			$tpl_page .= '<h2>'. _gettext('Styles management') . '</h2><br />';
			$tpl_page .= '<a href="?action=styles&newstyle"><button>'._gettext('New style').'</button></a><br /><br />';
			$fetus_styles = $tc_db->GetAll("SELECT `name` FROM `" . KU_DBPREFIX . "customstyles` WHERE `owner` = '".$_SESSION['manageusername']."' AND `temporary` = 1");
			if(count($fetus_styles) > 0) {
				$tpl_page .= _gettext('Found styles that are not yet named').': <br /><table>';
				foreach($fetus_styles as $style) {
					$tpl_page .= '<tr><td>'.ucfirst($style['name']).'.css</td>
						<td>[<a href="?action=styles&setname='.$style['name'].'">'._gettext('Set name').'</a>]</td>
						<td>[<a href="?action=styles&deletestyle='.$style['name'].'">'._gettext('Delete').'</a>]</td>';
				}
				$tpl_page .= '</table><br>';
			}
			$mystyles = $tc_db->GetAll("SELECT `name` FROM `" . KU_DBPREFIX . "customstyles` WHERE `owner` = '".$_SESSION['manageusername']."' AND `temporary` != 1");
			if(count($mystyles) > 0) {
				$tpl_page .= _gettext('Your styles').': <br /><table>';
				foreach($mystyles as $style) {
					$tpl_page .= '<tr><td>'.ucfirst($style['name']).'.css</td>
						<td>[<a href="?action=styles&editstyle='.$style['name'].'">'._gettext('Edit').'</a>]</td>
						<td>[<a href="?action=styles&deletestyle='.$style['name'].'">'._gettext('Delete').'</a>]</td>';
				}
				$tpl_page .= '</table>';
			}
			else {
				$tpl_page .= _gettext('You have not uploaded any styles yet');
			}
		}
	}

	function boardopts() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		if(isset($_POST['desc']))
			$_POST['desc'] = htmlspecialchars($_POST['desc']);

		$tpl_page .= '<h2>'. _gettext('Board options') . '</h2><br />';
		if (
			isset($_GET['updateboard']) 
			&& 
			isset($_POST['order']) 
			&& 
			isset($_POST['maxpages']) 
			&& 
			isset($_POST['maxage']) 
			&& 
			isset($_POST['messagelength'])
		) {
      $this->CheckToken($_POST['token']);
			if (!$this->CurrentUserIsModeratorOfBoard($_GET['updateboard'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('You are not a moderator of this board.'));
			}
			$boardid = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['updateboard']) . " LIMIT 1");
			if ($boardid != '') {
				$customstyles = $tc_db->GetAll("SELECT `name` FROM `" . KU_DBPREFIX . "customstyles` WHERE `temporary` != '1'");
				$available_styles = explode(':', KU_STYLES);
				if(count($customstyles) > 0) {
					foreach($customstyles as $stylesheet) {
						$available_styles[] = $stylesheet['name'];
					}
				}
				if (
					$_POST['order'] >= 0
					&&
					$_POST['maxpages'] >= 0
					&&
					$_POST['maxfiles'] >= 0
					&&
					$_POST['markpage'] >= 0
					&&
					$_POST['maxage'] >= 0
					&&
					$_POST['messagelength'] >= 0
				) {
					if (
						$_POST['defaultstyle'] == ''
						||
						in_array($_POST['defaultstyle'], $available_styles)
					) {
						$filetypes = array();
						foreach($_POST as $postkey => $postvalue) {
							if (substr($postkey, 0, 9) == 'filetype_') {
								$filetypes []= substr($postkey, 9);
							}
						}
						$set = array();
						foreach (array( // boolean properties
							'enablecatalog', 
							'enablenofile', 
							'redirecttothread',
							'enablereporting',
							'forcedanon',
							'trial',
							'popular',
							'enablearchiving',
							'showid',
							'compactlist',
							'locked',
							'balls',
							'dice',
							'useragent',
							'duplication',
							'opmod',
							'hidden'
						) as $prop) {
							$set []= "`$prop` = ".$tc_db->qstr(isset($_POST[$prop]) ? '1' : '0');
						}
						if(in_array($_POST['locale'], explode('|', KU_SUPPORTED_LOCALES))) {
							if (!$_POST['allowedembeds'] || !count($_POST['allowedembeds']))
								$_POST['allowedembeds'] = array(); // prevent warnings
							$allowedembeds = array();
							$embeds = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype`, `name` FROM `" . KU_DBPREFIX . "embeds` ORDER BY `filetype` ASC");
							foreach ($embeds as $e) {
								if(in_array($e['filetype'], $_POST['allowedembeds'])) {
									$allowedembeds []= $e['filetype'];
								}
							}
							$set []= "`embeds_allowed` = ".$tc_db->qstr(implode(',', $allowedembeds));
							foreach(array( // string properties
								'desc',
								'locale',
								'maximagesize',
								'maxfiles',
								'messagelength',
								'maxpages',
								'maxage',
								'markpage',
								'maxreplies',
								'image',
								'includeheader',
								'anonymous',
								'defaultstyle',
								'loadbalanceurl',
								'loadbalancepassword'
							) as $prop) {
								$set []= "`$prop` = ".$tc_db->qstr($_POST[$prop]);
							}
							foreach(array( // numeric properties
								'order',
								'section',
								'enablecaptcha'
							) as $prop) {
								$set []= "`$prop` = ".$tc_db->qstr(intval($_POST[$prop]));
							}
							$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "boards` 
								SET ". implode(',', $set) ."
								WHERE `name` = " . $tc_db->qstr($_GET['updateboard'])
							);
							$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $boardid . "'");
							$vals = array();
							foreach ($filetypes as $filetype) {
								$vals [] = "( '" . $boardid . "', " . $tc_db->qstr($filetype) . " )";
							}
							$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "board_filetypes` (`boardid`, `typeid`) VALUES " . implode(',', $vals));
							require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
							$this->rebuild20json();
							$menu_class = new Menu();
							$menu_class->Generate();
							if (isset($_POST['submit_regenerate'])) {
								$board_class = new Board($_GET['updateboard']);
								$board_class->RegenerateAll();
							}
							$tpl_page .= _gettext('Update successful.');
							management_addlogentry(_gettext('Updated board configuration'), 4, $_GET['updateboard']);
						}
						else $tpl_page .= _gettext('Locale unsupported.');
					}
					else
						$tpl_page .= _gettext('Style not found') . ': ' . $_POST['defaultstyle'];
				}
				else
					$tpl_page .= _gettext('Integer values must be entered correctly.');
			}
			else
				$tpl_page .= _gettext('Unable to locate a board named') . ' <strong>'. $_GET['updateboard'] . '</strong>.';
		}
		elseif (isset($_POST['board'])) {
			if (!$this->CurrentUserIsModeratorOfBoard($_POST['board'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('You are not a moderator of this board.'));
			}
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
			if (count($resultsboard) > 0) {
				foreach ($resultsboard as $lineboard) {
					$tpl_page .= '<div class="container">
					<form action="?action=boardopts&updateboard='.urlencode($_POST['board']).'" method="post">
          			<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />';
					/* Directory */
					$tpl_page .= '<label for="board">'. _gettext('Directory') .':</label>
					<input type="text" name="board" value="'.$_POST['board'].'" disabled />
					<div class="desc">'. _gettext('The directory of the board.') .'</div><br />';

					/* Description */
					$tpl_page .= '<label for="desc">'. _gettext('Description') .':</label>
					<input type="text" name="desc" value="'.$lineboard['desc'].'" />
					<div class="desc">'. _gettext('The name of the board.') .'</div><br />';

					/* Order */
					$tpl_page .= '<label for="order">'. _gettext('Order') .':</label>
					<input type="text" name="order" value="'.$lineboard['order'].'" />
					<div class="desc">'. _gettext('Order to show board in menu list, in ascending order.') .' '. _gettext('Default') .': <strong>0</strong></div><br />';

					/* Section */
					$tpl_page .= '<label for="section">'. _gettext('Section') .':</label>' .
					$this->MakeSectionListDropdown('section', $lineboard['section']) .
					'<div class="desc">'. _gettext('The section the board is in. This is used for displaying the list of boards on the top and bottom of pages.') .'<br />'. _gettext('If this is set to <em>Select a Board</em>, <strong>it will not be shown in the menu</strong>.') .'</div><br />';

					/* Load balancer URL */
					$tpl_page .= '<label for="loadbalanceurl">'. _gettext('Load balance URL') .':</label>
					<input type="text" name="loadbalanceurl" value="'.$lineboard['loadbalanceurl'].'" />
					<div class="desc">'. _gettext('The full http:// URL to the load balance script for this board. The script will handle file uploads, and creation of thumbnails. Only one script per board can be used, and there must be a src and thumb dir in the same folder as the script. Set to nothing to disable.') .'</div><br />';

					/* Load balancer password */
					$tpl_page .= '<label for="loadbalancepassword">'. _gettext('Load balance password') .':</label>
					<input type="text" name="loadbalancepassword" value="'.$lineboard['loadbalancepassword'].'" />
					<div class="desc">'. _gettext('The password which will be passed to the script above. The script must have this same password entered at the top, in the configuration area.') .'</div><br />';

					/* Allowed filetypes */
					$tpl_page .= '<label>'. _gettext('Allowed filetypes') .':</label>
					<div class="desc">'. _gettext('What filetypes users are allowed to upload.') .'</div><br />';
					$filetypes = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype` FROM `" . KU_DBPREFIX . "filetypes` ORDER BY `filetype` ASC");
					foreach ($filetypes as $filetype) {
						$is_any = $filetype['filetype']=="*";
						$ftype_name = $is_any ? "<b>"._gettext('Any file type')."</b>" : strtoupper($filetype['filetype']);
						$tpl_page .= '<label for="filetype_'. $filetype['id'] . '">'. $ftype_name . '</label><input type="checkbox" name="filetype_'. $filetype['id'] . '"';
						$filetype_isenabled = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $lineboard['id'] . "' AND `typeid` = '" . $filetype['id'] . "' LIMIT 1");
						if ($filetype_isenabled > 0) {
							$tpl_page .= ' checked';
						}
						$tpl_page .= ' /><br />';
						if ($is_any) {
							$tpl_page .= "<script>
								document.querySelector('input[name=filetype_".$filetype['id']."]').onchange = function() { 
									document.querySelectorAll('input[name^=filetype]').forEach(i => {
										if (i != this) {
											if (this.checked) 
												i.setAttribute('disabled', true)
											else
												i.removeAttribute('disabled')
										}
									})
								}
							</script>";
						}
					}

					/* Allowed embeds */
					$tpl_page .= '<label>'. _gettext('Allowed embeds') .':</label>
					<div class="desc">'. _gettext('What embed sites are allowed on this board. Only useful on board with embedding enabled.') .'</div><br />';
					$embeds = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype`, `name` FROM `" . KU_DBPREFIX . "embeds` ORDER BY `filetype` ASC");
					foreach ($embeds as $embed) {
						$tpl_page .= '<label for="allowedembeds[]">'. $embed['name'] . '</label><input type="checkbox" name="allowedembeds[]" value="'. $embed['filetype'] . '"';
						if (in_array($embed['filetype'], explode(',', $lineboard['embeds_allowed']))) {
							$tpl_page .= ' checked';
						}
						$tpl_page .= ' /><br />';
					}

					/* Maximum file count */
					$tpl_page .= '<label for="maxfiles">'. _gettext('Maximum file/embed count') .':</label>
					<input type="number" name="maxfiles" value="'.$lineboard['maxfiles'].'" />
					<div class="desc">'. _gettext('Maxmimum number or files or embeds per post.') . ' '. _gettext('Default') .': <strong>4</strong></div><br />';

					/* Maximum image size */
					$tpl_page .= '<label for="maximagesize">'. _gettext('Maximum image size') .':</label>
					<input type="text" name="maximagesize" value="'.$lineboard['maximagesize'].'" />
					<div class="desc">'. _gettext('Maxmimum size of uploaded images, in <strong>bytes</strong>.') . ' '. _gettext('Default') .': <strong>1024000</strong></div><br />';

					/* Maximum message length */
					$tpl_page .= '<label for="messagelength">'. _gettext('Maximum message length') .':</label>
					<input type="text" name="messagelength" value="'.$lineboard['messagelength'].'" />
					<div class="desc">'. _gettext('Default') .': <strong>8192</strong></div><br />';

					/* Maximum board pages */
					$tpl_page .= '<label for="maxpages">'. _gettext('Maximum board pages') .':</label>
					<input type="text" name="maxpages" value="'.$lineboard['maxpages'].'" />
					<div class="desc">'. _gettext('Default') .': <strong>11</strong></div><br />';

					/* Maximum thread age */
					$tpl_page .= '<label for="maxage">'. _gettext('Maximum thread age (Hours)') .':</label>
					<input type="text" name="maxage" value="'.$lineboard['maxage'].'" />
					<div class="desc">'. _gettext('Default') .': <strong>0</strong></div><br />';

					/* Mark page */
					$tpl_page .= '<label for="maxage">'. _gettext('Mark page') .':</label>
					<input type="text" name="markpage" value="'.$lineboard['markpage'].'" />
					<div class="desc">'. _gettext('Threads which reach this page or further will be marked to be deleted in two hours.') .' '. _gettext('Default') .': <strong>9</strong></div><br />';

					/* Maximum thread replies */
					$tpl_page .= '<label for="maxreplies">'. _gettext('Maximum thread replies') .':</label>
					<input type="text" name="maxreplies" value="'.$lineboard['maxreplies'].'" />
					<div class="desc">'. _gettext('The number of replies a thread can have before autosaging to the back of the board.') . ' '. _gettext('Default') .': <strong>200</strong></div><br />';

					/* Header image */
					$tpl_page .= '<label for="image">'. _gettext('Header image') .':</label>
					<input type="text" name="image" value="'.$lineboard['image'].'" />
					<div class="desc">'. _gettext('Overrides the header set in the config file. Leave blank to use configured global header image. Needs to be a full url including http://. Set to none to show no header image.') .'</div><br />';

					/* Include header */
					$tpl_page .= '<label for="includeheader">'. _gettext('Include header') .':</label>
					<textarea name="includeheader" rows="12" cols="80">'.htmlspecialchars($lineboard['includeheader']).'</textarea>
					<div class="desc">'. _gettext('Raw HTML which will be inserted at the top of each page of the board.') .'</div><br />';

					/* Anonymous */
					$tpl_page .= '<label for="anonymous">'. _gettext('Anonymous') .':</label>
					<input type="text" name="anonymous" value="'. $lineboard['anonymous'] . '" />
					<div class="desc">'. _gettext('Name to display when a name is not attached to a post.') . ' '. _gettext('Default') .': <strong>'. _gettext('Anonymous') .'</strong></div><br />';
					
					/* Hidden */
					$tpl_page .= '<label for="hidden">'. _gettext('Hidden') .':</label>
					<input type="checkbox" name="hidden" ';
					if ($lineboard['hidden'] == '1') {
						$tpl_page .= 'checked ';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If enabled, this board will not appear in the menu') .'</div><br />';

					/* Locked */
					$tpl_page .= '<label for="locked">'. _gettext('Locked') .':</label>
					<input type="checkbox" name="locked" ';
					if ($lineboard['locked'] == '1') {
						$tpl_page .= 'checked ';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('Only moderators of the board and admins can make new posts/replies') .'</div><br />';

					/* Show ID */
					$tpl_page .= '<label for="showid">'. _gettext('Show ID') .':</label>
					<input type="checkbox" name="showid" ';
					if ($lineboard['showid'] == '1') {
						$tpl_page .= 'checked ';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If enabled, each post will display the poster\'s ID, which is a representation of their IP address.') .'</div><br />';

					/* Show ID */
					$tpl_page .= '<label for="compactlist">'. _gettext('Compact list') .':</label>
					<input type="checkbox" name="compactlist" ';
					if ($lineboard['compactlist'] == '1') {
						$tpl_page .= 'checked ';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('Text boards only. If enabled, the list of threads displayed on the front page will be formatted differently to be compact.') . '</div><br />';

					/* Enable reporting */
					$tpl_page .= '<label for="enablereporting">'. _gettext('Enable reporting') .':</label>
					<input type="checkbox" name="enablereporting"';
					if ($lineboard['enablereporting'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Reporting allows users to report posts, adding the post to the report list.') .' '. _gettext('Default') .': <strong>'. _gettext('Yes') .'</strong></div><br />';

					/* Locale */;
					$locales = explode('|', KU_SUPPORTED_LOCALES);
					$tpl_page .= '<label for="locale">'. _gettext('Locale') .':</label><select name="locale">';
					foreach($locales as $loc) {
						$tpl_page .= '<option value="'.$loc.'"';
						if($loc == $lineboard['locale']) $tpl_page .= ' selected="selected"';
						$tpl_page .= '>'.$loc.'</option>';
					}
					$tpl_page .= '</select><br />';

					/* Countryballs */
					$tpl_page .= '<label for="balls">'. _gettext('Countryballs') .':</label>
					<input type="checkbox" name="balls"';
					if ($lineboard['balls'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Add countryballs to posts corresponding to poster\'s country (provided by Cloudflare).') .'</div><br />';

					/* Dice */
					$tpl_page .= '<label for="dice">'. _gettext('Dice') .':</label>
					<input type="checkbox" name="dice"';
					if ($lineboard['dice'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Add ##dice## feature.') .'</div><br />';

					/* Useragent */
					$tpl_page .= '<label for="useragent">'. _gettext('Useragent') .':</label>
					<input type="checkbox" name="useragent"';
					if ($lineboard['useragent'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Add ##Useragent## feature') .'</div><br />';

					/* Duplication */
					$tpl_page .= '<label for="duplication">'. _gettext('Duplication') .':</label>
					<input type="checkbox" name="duplication"';
					if ($lineboard['duplication'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Enable file and embed duplication') .'</div><br />';

					/* OP moderation */
					$tpl_page .= '<label for="opmod">'. _gettext('OP moderation') .':</label>
					<input type="checkbox" name="opmod"';
					if ($lineboard['opmod'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Allow thread starters to delete replies') .'</div><br />';

					/* Select captcha */
					$tpl_page .= '<label for="enablecaptcha">'. _gettext('Captcha') .':</label>
					<select name="enablecaptcha">
						<option value="1"'.(($lineboard['enablecaptcha'] == '1') ? ' selected="selected"' : '').'>Default</option>
						<option value="2"'.(($lineboard['enablecaptcha'] == '2') ? ' selected="selected"' : '').'>Hcaptcha</option>
						<option value="0"'.(($lineboard['enablecaptcha'] == '0') ? ' selected="selected"' : '').'>Off</option>
					</select>
					<div class="desc">'. _gettext('Enable/disable captcha system for this board. If captcha is enabled, in order for a user to post, they must first correctly enter the text on an image.') .' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Enable archiving */
					$tpl_page .= '<label for="enablearchiving">'. _gettext('Enable archiving') .':</label>
					<input type="checkbox" name="enablearchiving"';
					if ($lineboard['enablearchiving'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('Enable/disable thread archiving for this board (not available if load balancer is used). If enabled, when a thread is pruned or deleted through this panel with the archive checkbox checked, the thread and its images will be moved into the arch directory, found in the same directory as the board. To function properly, you must create and set proper permissions to /boardname/arch, /boardname/arch/res, /boardname/arch/src, and /boardname/arch/thumb') .' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Enable catalog */
					$tpl_page .= '<label for="enablecatalog">'. _gettext('Enable catalog') .':</label>
					<input type="checkbox" name="enablecatalog"';
					if ($lineboard['enablecatalog'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, a catalog.html file will be built with the other files, displaying the original picture of every thread in a box. This will only work on normal imageboards.') .' '. _gettext('Default') .': <strong>'. _gettext('Yes') .'</strong></div><br />';

					/* Enable "no file" posting */
					$tpl_page .= '<label for="enablenofile">'. _gettext('Enable \'no file\' posting') .':</label>
					<input type="checkbox" name="enablenofile"';
					if ($lineboard['enablenofile'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, new threads will not require an image to be posted.') . ' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Redirect to thread */
					$tpl_page .= '<label for="redirecttothread">'. _gettext('Redirect to thread') .':</label>
					<input type="checkbox" name="redirecttothread"';
					if ($lineboard['redirecttothread'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, users will be redirected to the thread they replied to/posted after posting. If set to no, users will be redirected to the first page of the board.') . ' '. _gettext('Default') .': <strong>'.('No') .'</strong></div><br />';

					/* Forced anonymous */
					$tpl_page .= '<label for="forcedanon">'. _gettext('Forced anonymous') .':</label>
					<input type="checkbox" name="forcedanon"';
					if ($lineboard['forcedanon'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, users will not be allowed to enter a name, making everyone appear as Anonymous') . ' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Trial */
					$tpl_page .= '<label for="trial">'. _gettext('Trial') .':</label>
					<input type="checkbox" name="trial"';
					if ($lineboard['trial'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, this board will appear in italics in the menu') . ' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Popular */
					$tpl_page .= '<label for="popular">'. _gettext('Popular') .':</label>
					<input type="checkbox" name="popular"';
					if ($lineboard['popular'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, this board will appear in bold in the menu') . ' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Default style */
					$tpl_page .= '<label for="defaultstyle">'. _gettext('Default style') .':</label>
					<select name="defaultstyle">

					<option value=""';
					$tpl_page .= ($lineboard['defaultstyle'] == '') ? ' selected="selected"' : '';
					$tpl_page .= '>'._gettext('Use Default').'</option>';

					$styles = explode(':', KU_STYLES);
					$tpl_page .= '<optgroup label="'._gettext('Default Styles').'">';
					foreach ($styles as $stylesheet) {
						$tpl_page .= '<option value="'. $stylesheet . '"';
						$tpl_page .= ($lineboard['defaultstyle'] == $stylesheet) ? ' selected="selected"' : '';
						$tpl_page .= '>'. ucfirst($stylesheet) . '</option>';
					}
					$tpl_page .= '</optgroup>';

					$customstyles = $tc_db->GetAll("SELECT `name` FROM `" . KU_DBPREFIX . "customstyles` WHERE `temporary` != '1'");
					if(count($customstyles)) {
						$tpl_page .= '<optgroup label="'._gettext('Custom Styles').'">';
						foreach($customstyles as $stylesheet) {
							$tpl_page .= '<option value="'. $stylesheet['name'] . '"';
							$tpl_page .= ($lineboard['defaultstyle'] == $stylesheet['name']) ? ' selected="selected"' : '';
							$tpl_page .= '>'. ucfirst($stylesheet['name']) . '</option>';
						}
						$tpl_page .= '</optgroup>';
					}

					$tpl_page .= '</select>
					<div class="desc">'. _gettext('The style which will be set when the user first visits the board.') .' '. _gettext('Default') .': <strong>'. _gettext('Use Default') .'</strong></div><br />';

					/* Submit form */
					$tpl_page .= '<input type="submit" name="submit_regenerate" value="'. _gettext('Update and regenerate board') .'" /><br /><input type="submit" name="submit_noregenerate" value="'. _gettext('Update without regenerating board') .'" />

					</form>
					</div><br />';
				}
			} else {
				$tpl_page .= _gettext('Unable to locate a board named') . ' <strong>'. $_POST['board'] . '</strong>.';
			}
		}
		else {
			$tpl_page .= '<form action="?action=boardopts" method="post">
			<label for="board">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
			'<input type="submit" value="'. _gettext('Go') .'" />
			</form>';
		}
	}

	function boardopts_mod() {
		global $tc_db, $tpl_page;
		$this->BoardOwnersOnly();

		$_POST['desc'] = htmlspecialchars($_POST['desc']);

		$tpl_page .= '<h2>'. _gettext('Board options') . '</h2><br />';
		if (
			isset($_GET['updateboard']) 
		) {
      $this->CheckToken($_POST['token']);
			if (!$this->CurrentUserIsModeratorOfBoard($_GET['updateboard'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('You are not a moderator of this board.'));
			}
			$boardid = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['updateboard']) . " LIMIT 1");
			if ($boardid != '') {
				$customstyles = $tc_db->GetAll("SELECT `name` FROM `" . KU_DBPREFIX . "customstyles` WHERE `temporary` != '1'");
				$available_styles = explode(':', KU_STYLES);
				if(count($customstyles) > 0) {
					foreach($customstyles as $stylesheet) {
						$available_styles[] = $stylesheet['name'];
					}
				}
				if (
					$_POST['defaultstyle'] == ''
					||
					in_array($_POST['defaultstyle'], $available_styles)
				) {
					$filetypes = array();
					foreach($_POST as $postkey => $postvalue) {
						if (substr($postkey, 0, 9) == 'filetype_') {
							$filetypes []= substr($postkey, 9);
						}
					}
					$set = array();
					foreach (array( // boolean properties
						'enablenofile', 
						'forcedanon',
						'showid',
						'locked',
						'balls',
						'dice',
						'useragent',
						'duplication',
						'opmod',
						'hidden'
					) as $prop) {
						$set []= "`$prop` = ".$tc_db->qstr(isset($_POST[$prop]) ? '1' : '0');
					}
					if(in_array($_POST['locale'], explode('|', KU_SUPPORTED_LOCALES))) {
						if (!$_POST['allowedembeds'] || !count($_POST['allowedembeds']))
							$_POST['allowedembeds'] = array();
						$allowedembeds = array();
						$embeds = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype`, `name` FROM `" . KU_DBPREFIX . "embeds` ORDER BY `filetype` ASC");
						foreach ($embeds as $e) {
							if(in_array($e['filetype'], $_POST['allowedembeds'])) {
								$allowedembeds []= $e['filetype'];
							}
						}
						$set []= "`embeds_allowed` = ".$tc_db->qstr(implode(',', $allowedembeds));
						foreach(array( // string properties
							'desc',
							'locale',
							'anonymous',
							'defaultstyle'
						) as $prop) {
							$set []= "`$prop` = ".$tc_db->qstr($_POST[$prop]);
						}
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "boards` 
							SET ". implode(',', $set) ."
							WHERE `name` = " . $tc_db->qstr($_GET['updateboard'])
						);
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $boardid . "'");
						$vals = array();
						foreach ($filetypes as $filetype) {
							$vals [] = "( '" . $boardid . "', " . $tc_db->qstr($filetype) . " )";
						}
						$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "board_filetypes` (`boardid`, `typeid`) VALUES " . implode(',', $vals));
						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$this->rebuild20json();
						$menu_class = new Menu();
						$menu_class->Generate();
						if (isset($_POST['submit_regenerate'])) {
							$board_class = new Board($_GET['updateboard']);
							$board_class->RegenerateAll();
						}
						$tpl_page .= _gettext('Update successful.');
						management_addlogentry(_gettext('Updated board configuration'), 4, $_GET['updateboard']);
					}
					else $tpl_page .= _gettext('Locale unsupported.');
				}
				else
					$tpl_page .= _gettext('Style not found') . ': ' . $_POST['defaultstyle'];
			}
			else
				$tpl_page .= _gettext('Unable to locate a board named') . ' <strong>'. $_GET['updateboard'] . '</strong>.';
		}
		elseif (isset($_POST['board'])) {
			if (!$this->CurrentUserIsModeratorOfBoard($_POST['board'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('You are not a moderator of this board.'));
			}
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
			if (count($resultsboard) > 0) {
				foreach ($resultsboard as $lineboard) {
					$tpl_page .= '<div class="container">
					<form action="?action=boardopts_mod&updateboard='.urlencode($_POST['board']).'" method="post">
          			<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />';
					/* Directory */
					$tpl_page .= '<label for="board">'. _gettext('Directory') .':</label>
					<input type="text" name="board" value="'.$_POST['board'].'" disabled />
					<div class="desc">'. _gettext('The directory of the board.') .'</div><br />';

					/* Description */
					$tpl_page .= '<label for="desc">'. _gettext('Description') .':</label>
					<input type="text" name="desc" value="'.$lineboard['desc'].'" />
					<div class="desc">'. _gettext('The name of the board.') .'</div><br />';

					/* Allowed filetypes */
					$tpl_page .= '<label>'. _gettext('Allowed filetypes') .':</label>
					<div class="desc">'. _gettext('What filetypes users are allowed to upload.') .'</div><br />';
					$filetypes = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype` FROM `" . KU_DBPREFIX . "filetypes` ORDER BY `filetype` ASC");
					foreach ($filetypes as $filetype) {
						$tpl_page .= '<label for="filetype_'. $filetype['id'] . '">'. strtoupper($filetype['filetype']) . '</label><input type="checkbox" name="filetype_'. $filetype['id'] . '"';
						$filetype_isenabled = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $lineboard['id'] . "' AND `typeid` = '" . $filetype['id'] . "' LIMIT 1");
						if ($filetype_isenabled > 0) {
							$tpl_page .= ' checked';
						}
						$tpl_page .= ' /><br />';
					}

					/* Allowed embeds */
					$tpl_page .= '<label>'. _gettext('Allowed embeds') .':</label>
					<div class="desc">'. _gettext('What embed sites are allowed on this board. Only useful on board with embedding enabled.') .'</div><br />';
					$embeds = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype`, `name` FROM `" . KU_DBPREFIX . "embeds` ORDER BY `filetype` ASC");
					foreach ($embeds as $embed) {
						$tpl_page .= '<label for="allowedembeds[]">'. $embed['name'] . '</label><input type="checkbox" name="allowedembeds[]" value="'. $embed['filetype'] . '"';
						if (in_array($embed['filetype'], explode(',', $lineboard['embeds_allowed']))) {
							$tpl_page .= ' checked';
						}
						$tpl_page .= ' /><br />';
					}

					/* Anonymous */
					$tpl_page .= '<label for="anonymous">'. _gettext('Anonymous') .':</label>
					<input type="text" name="anonymous" value="'. $lineboard['anonymous'] . '" />
					<div class="desc">'. _gettext('Name to display when a name is not attached to a post.') . ' '. _gettext('Default') .': <strong>'. _gettext('Anonymous') .'</strong></div><br />';
					
					/* Hidden */
					$tpl_page .= '<label for="hidden">'. _gettext('Hidden') .':</label>
					<input type="checkbox" name="hidden" ';
					if ($lineboard['hidden'] == '1') {
						$tpl_page .= 'checked ';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If enabled, this board will not appear in the menu') .'</div><br />';

					/* Locked */
					$tpl_page .= '<label for="locked">'. _gettext('Locked') .':</label>
					<input type="checkbox" name="locked" ';
					if ($lineboard['locked'] == '1') {
						$tpl_page .= 'checked ';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('Only moderators of the board and admins can make new posts/replies') .'</div><br />';

					/* Show ID */
					$tpl_page .= '<label for="showid">'. _gettext('Show ID') .':</label>
					<input type="checkbox" name="showid" ';
					if ($lineboard['showid'] == '1') {
						$tpl_page .= 'checked ';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If enabled, each post will display the poster\'s ID, which is a representation of their IP address.') .'</div><br />';

					/* Enable reporting */
					$tpl_page .= '<label for="enablereporting">'. _gettext('Enable reporting') .':</label>
					<input type="checkbox" name="enablereporting"';
					if ($lineboard['enablereporting'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Reporting allows users to report posts, adding the post to the report list.') .' '. _gettext('Default') .': <strong>'. _gettext('Yes') .'</strong></div><br />';

					/* Locale */;
					$locales = explode('|', KU_SUPPORTED_LOCALES);
					$tpl_page .= '<label for="locale">'. _gettext('Locale') .':</label><select name="locale">';
					foreach($locales as $loc) {
						$tpl_page .= '<option value="'.$loc.'"';
						if($loc == $lineboard['locale']) $tpl_page .= ' selected="selected"';
						$tpl_page .= '>'.$loc.'</option>';
					}
					$tpl_page .= '</select><br />';

					/* Countryballs */
					$tpl_page .= '<label for="balls">'. _gettext('Countryballs') .':</label>
					<input type="checkbox" name="balls"';
					if ($lineboard['balls'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Add countryballs to posts corresponding to poster\'s country (provided by Cloudflare).') .'</div><br />';

					/* Dice */
					$tpl_page .= '<label for="dice">'. _gettext('Dice') .':</label>
					<input type="checkbox" name="dice"';
					if ($lineboard['dice'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Add ##dice## feature.') .'</div><br />';

					/* Useragent */
					$tpl_page .= '<label for="useragent">'. _gettext('Useragent') .':</label>
					<input type="checkbox" name="useragent"';
					if ($lineboard['useragent'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Add ##Useragent## feature') .'</div><br />';

					/* Duplication */
					$tpl_page .= '<label for="duplication">'. _gettext('Duplication') .':</label>
					<input type="checkbox" name="duplication"';
					if ($lineboard['duplication'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Enable file and embed duplication') .'</div><br />';

					/* OP moderation */
					$tpl_page .= '<label for="opmod">'. _gettext('OP moderation') .':</label>
					<input type="checkbox" name="opmod"';
					if ($lineboard['opmod'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />'. "\n" .
					'<div class="desc">'. _gettext('Allow thread starters to delete replies') .'</div><br />';

					/* Enable "no file" posting */
					$tpl_page .= '<label for="enablenofile">'. _gettext('Enable \'no file\' posting') .':</label>
					<input type="checkbox" name="enablenofile"';
					if ($lineboard['enablenofile'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, new threads will not require an image to be posted.') . ' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Forced anonymous */
					$tpl_page .= '<label for="forcedanon">'. _gettext('Forced anonymous') .':</label>
					<input type="checkbox" name="forcedanon"';
					if ($lineboard['forcedanon'] == '1') {
						$tpl_page .= ' checked';
					}
					$tpl_page .= ' />
					<div class="desc">'. _gettext('If set to yes, users will not be allowed to enter a name, making everyone appear as Anonymous') . ' '. _gettext('Default') .': <strong>'. _gettext('No') .'</strong></div><br />';

					/* Default style */
					$tpl_page .= '<label for="defaultstyle">'. _gettext('Default style') .':</label>
					<select name="defaultstyle">

					<option value=""';
					$tpl_page .= ($lineboard['defaultstyle'] == '') ? ' selected="selected"' : '';
					$tpl_page .= '>'._gettext('Use Default').'</option>';

					$styles = explode(':', KU_STYLES);
					$tpl_page .= '<optgroup label="'._gettext('Default Styles').'">';
					foreach ($styles as $stylesheet) {
						$tpl_page .= '<option value="'. $stylesheet . '"';
						$tpl_page .= ($lineboard['defaultstyle'] == $stylesheet) ? ' selected="selected"' : '';
						$tpl_page .= '>'. ucfirst($stylesheet) . '</option>';
					}
					$tpl_page .= '</optgroup>';

					$customstyles = $tc_db->GetAll("SELECT `name` FROM `" . KU_DBPREFIX . "customstyles` WHERE `temporary` != '1'");
					if(count($customstyles)) {
						$tpl_page .= '<optgroup label="'._gettext('Custom Styles').'">';
						foreach($customstyles as $stylesheet) {
							$tpl_page .= '<option value="'. $stylesheet['name'] . '"';
							$tpl_page .= ($lineboard['defaultstyle'] == $stylesheet['name']) ? ' selected="selected"' : '';
							$tpl_page .= '>'. ucfirst($stylesheet['name']) . '</option>';
						}
						$tpl_page .= '</optgroup>';
					}

					$tpl_page .= '</select>
					<div class="desc">'. _gettext('The style which will be set when the user first visits the board.') .' '. _gettext('Default') .': <strong>'. _gettext('Use Default') .'</strong></div><br />';

					/* Submit form */
					$tpl_page .= '<input type="submit" name="submit_regenerate" value="'. _gettext('Update and regenerate board') .'" /><br /><input type="submit" name="submit_noregenerate" value="'. _gettext('Update without regenerating board') .'" />

					</form>
					</div><br />';
				}
			} else {
				$tpl_page .= _gettext('Unable to locate a board named') . ' <strong>'. $_POST['board'] . '</strong>.';
			}
		}
		else {
			$tpl_page .= '<form action="?action=boardopts_mod" method="post">
			<label for="board">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
			'<input type="submit" value="'. _gettext('Go') .'" />
			</form>';
		}
	}

	//Checks if file is 100x300 gif png or jpg
	function CheckValidBanner($file) {
		//first we check the file extension
		if($file['error'] === UPLOAD_ERR_OK) {
			$path_parts = pathinfo($file["name"]);
			$extension = strtolower($path_parts['extension']);
			if(in_array($extension, array('jpg', 'jpeg', 'gif', 'png'))) {
				//then we check actual type
				$size = getimagesize($file['tmp_name']);
				if($size[0] == 300 && $size[1] == 100) {
					if(($size[2]==IMAGETYPE_PNG && $extension == 'png') || ($size[2]==IMAGETYPE_GIF && $extension == 'gif') || ($size[2]==IMAGETYPE_JPEG && ($extension == 'jpg' || $extension == 'jpeg'))) {
						return array('status'=>'success', 'extension'=>'.'.$extension);
					}
					else return array('status'=>'error', 'msg'=>_gettext('A common cause for this is an incorrect extension when the file is actually of a different type.'));
				}
				else return array('status'=>'error', 'msg'=>_gettext('Wrong file dimensions. Must be ').'300 x 100');
			}
			else return array('status'=>'error', 'msg'=>_gettext('Wrong file type. Allowed file types').': JPG, PNG, GIF.');
		}
		else return array('status'=>'error', 'msg'=>_gettext('Internal error.'));
	}

	function validurl($url) {
		return preg_match('#(http:\/\/|https:\/\/|ftp:\/\/|gopher:\/\/)([^\s]+(([^\s\pP]|\/|\]|\pP(?=(\R|$))))+)#u',$url);
	}

	function updateBannersJSON() {
		global $tc_db;

		$jsonfile = KU_ROOTDIR.'bnrs.json';
		$final_results = $tc_db->GetAll("SELECT `link`, `path`, `version` FROM `" . KU_DBPREFIX . "banners`");
		file_put_contents($jsonfile, json_encode($final_results), LOCK_EX);
	}

	function banners() {
		global $tc_db, $tpl_page;
		$this->BoardOwnersOnly();
		$tokeninput = '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />';
		$pathprefix = KU_WEBPATH.'/images/bnrs/';
		$internal_pathprefix = KU_ROOTDIR.'images/bnrs/';
		$now = $this->gentimestamp();

		// ------------------------------------------- PREVALIDATION -------------------------------------------
		$valid_error = '';
		if(!(isset($_POST['edit']) || isset($_POST['delete']) || isset($_POST['new']))) {
			$valid_error = 'noerror';
		}
		else {
			$this->CheckToken($_POST['token']);
			if(isset($_POST['new'])) $action = 'new';
			elseif(isset($_POST['edit'])) $action = 'edit';
			else $action = 'delete';

			if(isset($_FILES['banner']) && $_FILES['banner']['error'] == UPLOAD_ERR_OK) {
				$extension = $this->CheckValidBanner($_FILES['banner']);
				if($extension['status'] == 'success') {
					$file = $_FILES['banner'];
					$extension = $extension['extension'];
				}
				else $valid_error .= _gettext('File upload error').': '.$extension['msg'].' ';
			}
			if(isset($_POST['link'])) {
				if($this->validurl($_POST['link'])) {
					if(strlen($_POST['link']) <= 200) {
						$link = $_POST['link'];
					}
					else $valid_error .= _gettext('Too long URL.').' ';
				}
				else $valid_error .= _gettext('Wrong URL.').' ';
			}
			if(isset($_GET['board'])) {
				if($this->ctype_alnum_($_GET['board']) || $action=='new') {
					if($this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) $board = $_GET['board'];
					else $valid_error .= _gettext('You are not a moderator of this board.').' ';
				}
				else $valid_error .= _gettext('Wrong board name.').' ';
			}
			if(isset($_GET['custom'])) {
				if(ctype_digit($_GET['custom']) || $action=='new') $custom = $_GET['custom'];
				else $valid_error .= _gettext('Wrong banner ID.').' ';
			}
		}

		// ------------------------------------------- PROCESSING DATA -------------------------------------------
		if(!$valid_error) {
			if(isset($board)) {
				$existing = $tc_db->GetOne("SELECT `path` FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='0' AND `link` = '".$board."'");
				if($action == 'new') {
					if(!$existing) {
						if(isset($file)) {
							$path = 'boards-'.$board.$extension;
							$moved = move_uploaded_file($file['tmp_name'], $internal_pathprefix.$path);
							if($moved) {
								$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "banners` (`link`, `path`, `custom`) VALUES('".$board."', '".$path."', '0')");
								$msg = array('type'=>'success', 'msg'=>_gettext('Added new banner for board').' (&#47;'.$board.'&#47;)');
							}
							else $msg = array('type'=>'error', 'msg'=>_gettext('File transfer failure.'));
						}
						else $msg = array('type'=>'error', 'msg'=>_gettext('File is not specified.'));
					}
					else $msg = array('type'=>'error', 'msg'=>_gettext('This banner doesn\'t seem to exist.'));
				}
				elseif($action == 'edit') {
					if($existing) {
						if(isset($file)) {
							unlink($internal_pathprefix.$existing);
							$path = 'boards-'.$board.$extension;
							$moved = move_uploaded_file($file['tmp_name'], $internal_pathprefix.$path);
							if($moved) {
								$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "banners` SET `path`='".$path."', `version`=`version`+1 WHERE `custom`='0' AND `link`='".$board."'");
								$msg = array('type'=>'success', 'msg'=>_gettext('Updated banner for board').' (&#47;'.$board.'&#47;)');
							}
							else $msg = array('type'=>'error', 'msg'=>_gettext('File transfer failure.'));
						}
						else $msg = array('type'=>'error', 'msg'=>_gettext('File is not specified.'));
					}
					else $msg = array('type'=>'error', 'msg'=>_gettext('This banner doesn\'t seem to exist.'));
				}
				else { /* delete */
					if($existing) {
						unlink($internal_pathprefix.$existing);
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='0' AND `link`='".$board."'");
						$msg = array('type'=>'success', 'msg'=>_gettext('Deleted banner for board').' (&#47;'.$board.'&#47;)');
					}
					else $msg = array('type'=>'error', 'msg'=>_gettext('This banner doesn\'t seem to exist.'));
				}
			}
			elseif(isset($custom)) {
				if($action == 'new') {
					if(isset($file) && isset($link)) {
						$path = 'custom-'.$now.$extension;
						$moved = move_uploaded_file($file['tmp_name'], $internal_pathprefix.$path);
						if($moved) {
							$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "banners` (`link`, `path`, `custom`) VALUES('".$link."', '".$path."', '1')");
							$msg = array('type'=>'success', 'msg'=>_gettext('Added new custom banner'));
						}
						else $msg = array('type'=>'error', 'msg'=>_gettext('File transfer failure.'));
					}
					else $msg = array('type'=>'error', 'msg'=>_gettext('Set file and link.'));
				}
				else {
					$existing = $tc_db->GetOne("SELECT `path` FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='1' AND `id` = '".$custom."'");
					if($existing) {
						if($action == 'edit') {
							if(isset($link)) {
								$filequery = '';
								if(isset($file)) {
									unlink($internal_pathprefix.$existing);
									$path = 'custom-'.$now.$extension;
									$moved = move_uploaded_file($file['tmp_name'], $internal_pathprefix.$path);
									if($moved) $filequery = "`path`='".$path."', ";
									else $err = array('type'=>'error', 'msg'=>_gettext('File transfer failure.'));
								}
								if(!isset($err)) {
									$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "banners` SET ".$filequery."`version`=`version`+1, `link`='".$link."' WHERE `custom`='1' AND `id`='".$custom."'");
									$msg = array('type'=>'success', 'msg'=>_gettext('Updated custom banner').' #'.$custom);
								}
								else $msg = $err;
							}
							else $msg = array('type'=>'error', 'msg'=>_gettext('Link must be set.'));
						}
						else { /*delete*/
							unlink($internal_pathprefix.$existing);
							$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='1' AND `id`='".$custom."'");
							$msg = array('type'=>'success', 'msg'=>_gettext('Deleted custom banner').' #'.$custom);
						}
					}
					else $msg = array('type'=>'error', 'msg'=>_gettext('This banner doesn\'t seem to exist.'));
				}
			}
			else $msg = array('type'=>'error', 'msg'=>_gettext('Wrong request.'));
		}
		elseif($valid_error != 'noerror') $msg = array('type'=>'error','msg'=> _gettext('Validation error(s)').': '.$valid_error);

		// ------------------------------------------- DISPLAYING AFTERMSG -------------------------------------------
		if(isset($msg)) {
			if($msg['type'] == 'success') {
				$this->updateBannersJSON();
				$tpl_page .= '<div class="aftermsg success">'.$msg['msg'].'</div>';
			}
			else $tpl_page .= '<div class="aftermsg failure">'.$msg['msg'].'</div>';
		}

		// ------------------------------------------- DISPLAYING BANNERS -------------------------------------------
		//Fetch boards
		$boards = $this->BoardList($_SESSION['manageusername']);
		//Fetch board banners
		$board_banners = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='0'");
		//Fetch cistom banners
		if($this->CurrentUserIsAdministrator()) {
			$custom_banners = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "banners` WHERE `custom`='1'");
			$l_b = _gettext('Link');
		}
		else {
			$custom_banners = 'deny';
			$l_b = _gettext('Board');
		}
		if(count($boards) > 0 || $custom_banners != 'deny') {
			//table header
			if(count($boards) > 0) {
				$tpl_page .= '<h1>'._gettext('Banners of boards').'</h1><br />';
				//transform board_banners into 'key'=>value array "$bb_kv"
				foreach($board_banners as $banner) {
					$bb_kv[$banner['link']]= array('path'=>$banner['path'], 'version'=>$banner['version']);
				}
				//Now display the board banners subtable.
				foreach($boards as $board) {
					$brd = $board['name'];
					//first column: board name * start form;
					$tpl_page .= '<div class="banner-entry be-normal"><form id="bbf_'.$brd.'" method="post" action="?action=banners&board='.$brd.'" enctype="multipart/form-data">'
						.$tokeninput.'<div class="_td boardname">&#47;'.$brd.'&#47;</div>';
					//second column:  * banner+fileinput
					$tpl_page .= '<div class="_td bannerimg">';
					if(isset($bb_kv[$brd])) $tpl_page .= '<img src="'.$pathprefix.$bb_kv[$brd]['path'].'?v='.$bb_kv[$brd]['version'].'" class="oldbanner"/><input class="newbanner" name="banner" type="file" style="display:none" />';
					else $tpl_page .= '<input class="newbanner" name="banner" type="file" />';
					$tpl_page .= '</div>';
					//third column: actions
					$tpl_page .= '<div class="_td actions">';
					if(isset($bb_kv[$brd])) {
						//transform buttons
						$tpl_page .= '<div class="normal-buttons">
							<input type="button" value="'._gettext('Edit').'" class="toedit"/><br />
							<input type="button" value="'._gettext('Delete').'" class="todelete"/>
							</div>
							<div class="transformed deleteconfirm" style="display:none">
							<input type="submit" name="delete" value="'._gettext('Confirm').'" /><br />
							<input type="button" value="'._gettext('Cancel').'" class="undelete" />
							</div>
							<div class="transformed editconfirm" style="display:none">
							<input type="button" value="'._gettext('Cancel').'" class="unedit" /><br />
							<input type="submit" name="edit"/></div><br />';
					}
					else {
						//submit new banner if no banner is set
						$tpl_page .= '<input type="submit" value="'._gettext('Add').'" name="new"/>';
					}
					$tpl_page .= '</div></form></div>';
				}
			}
			//custom banner adder
			if($custom_banners != 'deny') $tpl_page .= '<br /><br /><h1>'._gettext('Custom banners').'</h1><br />
				<div title="'._gettext('Add custom banner').'" class="banner-entry de-custom new-custom"><form id="cbf_new" method="post" action="?action=banners&custom" enctype="multipart/form-data">'.$tokeninput.'
				<div class="_td custom-link"><input type="text" name="link" class="newlink" /></div>
				<div class="_td bannerimg"><input type="file" name="banner" class="newbanner" /></div>
				<div class="_td actions"><input type="submit" value="'._gettext('Add').'" name="new" class="newbanner" /></form></div></div>';
			if($custom_banners != 'deny' && count($custom_banners) > 0) {
				foreach($custom_banners as $banner) {
					//first column: link + form start
					$tpl_page .= '<div class="banner-entry de-custom"><form id="cbf_'.$banner['id'].'" method="post" action="?action=banners&custom='.$banner['id'].'" enctype="multipart/form-data">'.$tokeninput.'
						<div class="_td custom-link"><a class="custom-link-a" href="'.$banner['link'].'">'.preg_replace('#(http:\/\/(www\.)?)#','',$banner['link']).'</a>
						<input class="custom-link-entry" type="text" value="'.$banner['link'].'" name="link" class="newlink" style="display:none" /></div>';
					//second column: banner+fileinput
					$tpl_page .= '<div class="_td bannerimg"><img class="oldbanner" src="'.$pathprefix.$banner['path'].'?v='.$banner['version'].'">
						<input class="newbanner" type="file" name="banner" style="display:none" /></div>';
					//third column: actions
					$tpl_page .= '<div class="_td actions"><div class="normal-buttons">
						<input type="button" value="'._gettext('Edit').'" class="toedit"/><br />
						<input type="button" value="'._gettext('Delete').'" class="todelete"/>
						</div>
						<div class="transformed deleteconfirm" style="display:none">
						<input type="submit" name="delete" value="'._gettext('Confirm').'" /><br />
						<input type="button" value="'._gettext('Cancel').'" class="undelete" />
						</div>
						<div class="transformed editconfirm" style="display:none">
						<input type="button" value="'._gettext('Cancel').'" class="unedit" /><br />
						<input type="submit" name="edit"/></div></div></div></form></div>';
				}
			}
		}
		else {
			$tpl_page .= _gettext('You have no boards.');
		}
	}

	function unstickypost() {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Manage stickies') . '</h2><br />';
		if (
		  isset($_GET['postid'])
		  &&
		  isset($_GET['board'])
		  &&
		  $_GET['postid'] > 0
		  &&
		  $_GET['board'] != ''
		) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
			if (count($results) > 0) {
				if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
					exitWithErrorPage(_gettext('You are not a moderator of this board.'));
				}
				foreach ($results as $line) {
					$sticky_board_name = $line['name'];
					$sticky_board_id = $line['id'];
				}
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $sticky_board_id ." AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
				if (count($results) > 0) {
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `stickied` = '0' WHERE `boardid` = " . $sticky_board_id ." AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					$board_class = new Board($sticky_board_name);
					$board_class->RegenerateAll();
					unset($board_class);
					$msg = _gettext('Thread successfully un-stickied');
					management_addlogentry(_gettext('Unstickied thread') . ' #' . intval($_GET['postid']), 5, $sticky_board_name);
					if ($_POST['AJAX'])
					  exitWithSuccessJSON($msg);
					else
					  $tpl_page .= $msg;
				}
				else {
					$msg = _gettext('Invalid thread ID. This may have been caused by the thread recently being deleted.');
					if ($_POST['AJAX'])
					  exitWithErrorPage($msg);
					else
					  $tpl_page .= $msg;
				}
			}
			else {
				$msg = _gettext('Invalid board directory.');
				if ($_POST['AJAX'])
				  exitWithErrorPage($msg);
				else
				  $tpl_page .= $msg;
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= $this->stickyforms();
	}

	function ctype_alnum_($string) {
		return ctype_alnum(str_replace('_', '', $string));
	}

	function stickypost() {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Manage stickies') . '</h2><br />';
		if (
			isset($_GET['postid'])
			&&
			isset($_GET['board'])
			&&
			$_GET['postid'] > 0
			&&
			$_GET['board'] != ''
		) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
			if (count($results) > 0) {
				if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
					exitWithErrorPage(_gettext('You are not a moderator of this board.'));
				}
				foreach ($results as $line) {
					$sticky_board_name = $line['name'];
					$sticky_board_id = $line['id'];
				}
				$result = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $sticky_board_id . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
				if ($result > 0) {
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `stickied` = '1' WHERE `boardid` = " . $sticky_board_id . " AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					$board_class = new Board($sticky_board_name);
					$board_class->RegenerateAll();
					unset($board_class);
					$msg = _gettext('Thread successfully stickied.');
					management_addlogentry(_gettext('Stickied thread') . ' #' . intval($_GET['postid']), 5, $sticky_board_name);
					if ($_POST['AJAX'])
						exitWithSuccessJSON($msg);
					else
						$tpl_page .= $msg;
				} else {
					$msg = _gettext('Invalid thread ID. This may have been caused by the thread recently being deleted.');
					if ($_POST['AJAX'])
						exitWithErrorPage($msg);
					else
						$tpl_page .= $msg;
				}
			} else {
				$msg .= _gettext('Invalid board directory.');
				if ($_POST['AJAX'])
					exitWithErrorPage($msg);
				else
					$tpl_page .= $msg;
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= $this->stickyforms();
	}

	/* Create forms for stickying a post */
	function stickyforms() {
		global $tc_db;

		$output = '<table width="100%" border="0">
		<tr><td width="50%"><h1>'. _gettext('Sticky') . '</h1></td><td width="50%"><h1>'. _gettext('Unsticky') . '</h1></td></tr>
		<tr><td style="vertical-align:top;"><br />

		<form action="manage_page.php" method="get"><input type="hidden" name="action" value="stickypost" />
		<label for="board">'. _gettext('Board') .':</label>' .
		$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<label for="postid">'. _gettext('Thread') .':</label>
		<input type="text" name="postid" /><br />

		<label for="submit">&nbsp;</label>
		<input name="submit" type="submit" value="'. _gettext('Sticky') .'" />
		</form>
		</td><td>';
		$results_boards = $tc_db->GetAll("SELECT HIGH_PRIORITY `name`, `id` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		foreach ($results_boards as $line_board) {
			$output .= '<h2>/'. $line_board['name'] . '/</h2>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line_board['id'] . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `stickied` = '1'");
			if (count($results) > 0) {
				foreach ($results as $line) {
					$output .= '<a href="?action=unstickypost&board='. $line_board['name'] . '&postid='. $line['id'] . '">#'. $line['id'] . '</a><br />';
				}
			} else {
				$output .= 'No stickied threads.<br />';
			}
		}
		$output .= '</td></tr></table>';

		return $output;
	}

	function lockpost() {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Manage locked threads') . '</h2><br />';
		if (
		  isset($_GET['postid'])
		  &&
		  isset($_GET['board'])
		  &&
		  $_GET['postid'] > 0
		  &&
		  $_GET['board'] != ''
		) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
			if (count($results) > 0) {
				if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
					exitWithErrorPage(_gettext('You are not a moderator of this board.'));
				}
				foreach ($results as $line) {
					$lock_board_name = $line['name'];
					$lock_board_id = $line['id'];
				}
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lock_board_id . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
				if (count($results) > 0) {
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `locked` = '1' WHERE `boardid` = " . $lock_board_id . " AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					$board_class = new Board($lock_board_name);
					$board_class->RegenerateAll();
					unset($board_class);
					$msg = _gettext('Thread successfully locked.');
					if ($_POST['AJAX'])
					  exitWithSuccessJSON($msg);
					else
					  $tpl_page .= $msg;
					management_addlogentry(_gettext('Locked thread') . ' #'. intval($_GET['postid']), 5, intval($_GET['board']));
				} else {
					$msg = _gettext('Invalid thread ID. This may have been caused by the thread recently being deleted.');
					if ($_POST['AJAX'])
					  exitWithErrorPage($msg);
					else
					  $tpl_page .= $msg;
				}
			} else {
				$msg = _gettext('Invalid board directory.');
				if ($_POST['AJAX'])
				  exitWithErrorPage($msg);
				else
				  $tpl_page .= $msg;
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= $this->lockforms();
	}

	function unlockpost() {
		global $tc_db, $tpl_page, $board_class;

		$tpl_page .= '<h2>'. _gettext('Manage locked threads') . '</h2><br />';
		if (
		  isset($_GET['postid'])
		  &&
		  isset($_GET['board'])
		  &&
		  $_GET['postid'] > 0
		  &&
		  $_GET['board'] != ''
		) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
			if (count($results) > 0) {
				if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
					exitWithErrorPage(_gettext('You are not a moderator of this board.'));
				}
				foreach ($results as $line) {
					$lock_board_name = $line['name'];
					$lock_board_id = $line['id'];
				}
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lock_board_id . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
				if (count($results) > 0) {
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `locked` = '0' WHERE `boardid` = " . $lock_board_id . " AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					$board_class = new Board($lock_board_name);
					$board_class->RegenerateAll();
					unset($board_class);
					$msg = _gettext('Thread successfully unlocked.');
					if ($_POST['AJAX'])
					  exitWithSuccessJSON($msg);
					else
					  $tpl_page .= $msg;
					management_addlogentry(_gettext('Unlocked thread') . ' #'. intval($_GET['postid']), 5, intval($_GET['board']));
				} else {
					$msg = _gettext('Invalid thread ID. This may have been caused by the thread recently being deleted.');
					if ($_POST['AJAX'])
					  exitWithErrorPage($msg);
					else
					  $tpl_page .= $msg;
				}
			} else {
				$msg = _gettext('Invalid board directory.');
				if ($_POST['AJAX'])
				  exitWithErrorPage($msg);
				else
				  $tpl_page .= $msg;
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= $this->lockforms();
	}

	function lockforms() {
		global $tc_db;

		$output = '<table width="100%" border="0">
		<tr><td width="50%"><h1>'. _gettext('Lock') . '</h1></td><td width="50%"><h1>'. _gettext('Unlock') . '</h1></td></tr>
		<tr><td><br />

		<form action="manage_page.php" method="get"><input type="hidden" name="action" value="lockpost" />
		<label for="board">'. _gettext('Board') .':</label>' .
		$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<label for="postid">'. _gettext('Thread') .':</label>
		<input type="text" name="postid" /><br />

		<label for="submit">&nbsp;</label>
		<input name="submit" type="submit" value="'. _gettext('Lock') .'" />
		</form>
		</td><td>';
		$results_boards = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		foreach ($results_boards as $line_board) {
			$output .= '<h2>/'. $line_board['name'] . '/</h2>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line_board['id'] . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `locked` = '1'");
			if (count($results) > 0) {
				foreach ($results as $line) {
					$output .= '<a href="?action=unlockpost&board='. $line_board['name'] . '&postid='. $line['id'] . '">#'. $line['id'] . '</a><br />';
				}
			} else {
				$output .= 'No locked threads.<br />';
			}
		}
		$output .= '</td></tr></table>';

		return $output;
	}

	/* Delete a post, or multiple posts */
	function delposts($multidel=false) {
		global $tc_db, $tpl_page, $board_class;

    $isquickdel = false;
    if (isset($_POST['boarddir']) || isset($_GET['boarddir'])) {
      if (!isset($_GET['boarddir']) && isset($_POST['boarddir'])) {
        $this->CheckToken($_POST['token']);
      }
      if (isset($_GET['boarddir'])) {
				$isquickdel = true;
				$_POST['boarddir'] = $_GET['boarddir'];
				if (isset($_GET['delthreadid'])) {
					$_POST['delthreadid'] = $_GET['delthreadid'];
				}
				if (isset($_GET['delpostid'])) {
					$_POST['delpostid'] = $_GET['delpostid'];
				}
			}
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['boarddir']) . "");
			if (count($results) > 0) {
				if (!$this->CurrentUserIsModeratorOfBoard($_POST['boarddir'], $_SESSION['manageusername'])) {
					exitWithErrorPage(_gettext('You are not a moderator of this board.'));
				}
				foreach ($results as $line) {
					$board_id = $line['id'];
					$board_dir = $line['name'];
				}
				if (isset($_GET['cp'])) {
					$cp = '&amp;cp=y&amp;instant=y';
				}
				if (isset($_POST['delthreadid'])) {
					if ($_POST['delthreadid'] > 0) {
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `IS_DELETED` = '0' AND `id` = " . $tc_db->qstr($_POST['delthreadid']) . " AND `parentid` = '0'");
						if (count($results) > 0) {
							if (isset($_POST['fileonly'])) {
								foreach ($results as $line) {
									if (!empty($line['file'])) {
										$del = unlink(KU_ROOTDIR . $_POST['boarddir'] . '/src/'. $line['file'] . '.'. $line['file_type']);
										if ($del) {
											@unlink(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 's.'. $line['file_type']);
											@unlink(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 'c.'. $line['file_type']);
											$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `file` = 'removed', `file_md5` = '' WHERE `boardid` = " . $board_id . " AND `id` = ".$_POST['delthreadid']." LIMIT 1");
											$tpl_page .= '<hr />File successfully deleted<hr />';
										} else {
											$tpl_page .= '<hr />That file has already been deleted.<hr />';
										}
									} else {
										$tpl_page .= '<hr />Error: That thread doesn\'t have a file associated with it.<hr />';
									}
								}
							}
							else {
								foreach ($results as $line) {
									$delthread_id = $line['id'];
								}
								$post_class = new Post($delthread_id, $board_dir, $board_id);
								if (isset($_POST['archive'])) {
									$numposts_deleted = $post_class->Delete(true);
								}
								else {
									$numposts_deleted = $post_class->Delete();
								}
								$board_class = new Board($board_dir);
								$board_class->RegenerateAll();
								unset($board_class);
								unset($post_class);
								$tpl_page .= _gettext('Thread '.$delthread_id.' successfully deleted.');
								management_addlogentry(_gettext('Deleted thread') . ' #<a href="?action=viewthread&thread='. $delthread_id . '&board='. $_POST['boarddir'] . '">'. $delthread_id . '</a> ('. $numposts_deleted . ' replies)', 7, $_POST['boarddir']);
								if (!empty($_GET['postid'])) {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . $cp . '"><a href="'. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . $cp . '">'. _gettext('Redirecting') . '</a> to ban page...';
								}
								elseif ($isquickdel) {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/"><a href="'. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/">'. _gettext('Redirecting') . '</a> back to board...';
								}
							}
						}
						else {
							$tpl_page .= _gettext('Invalid thread ID '.$delpost_id.'. This may have been caused by the thread recently being deleted.');
						}
					}
				}
				elseif (isset($_POST['delpostid'])) {
					if ($_POST['delpostid'] > 0) {
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `IS_DELETED` = '0' AND `id` = " . $tc_db->qstr($_POST['delpostid']) . "");
						if (count($results) > 0) {
							if (isset($_POST['fileonly'])) {
								foreach ($results as $line) {
									if (!empty($line['file'])) {
										$del = @unlink(KU_ROOTDIR . $_POST['boarddir'] . '/src/'. $line['file'] . '.'. $line['file_type']);
										if ($del) {
											@unlink(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 's.'. $line['file_type']);
											@unlink(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 'c.'. $line['file_type']);
											$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `file` = 'removed', `file_md5` = '' WHERE `boardid` = " . $board_id . " AND `id` = ".$_POST['delpostid']." LIMIT 1");
											$tpl_page .= '<hr />File successfully deleted<hr />';
										} else {
											$tpl_page .= '<hr />That file has already been deleted.<hr />';
										}
									} else {
										$tpl_page .= '<hr />Error: That thread doesn\'t have a file associated with it.<hr />';
									}
								}
							} else {
								foreach ($results as $line) {
									$delpost_id = $line['id'];
									$delpost_parentid = $line['parentid'];
								}
								$post_class = new Post($delpost_id, $board_dir, $board_id);
								$post_class->Delete();
								$board_class = new Board($board_dir);
								$board_class->RegenerateThreads($delpost_parentid);
								$board_class->RegeneratePages();
								unset($board_class);
								unset($post_class);
								$tpl_page .= _gettext('Post '.$delpost_id.' successfully deleted.');
								management_addlogentry(_gettext('Deleted post') . ' #<a href="?action=viewthread&thread='. $delpost_parentid . '&board='. $_POST['boarddir'] . '#'. $delpost_id . '">'. $delpost_id . '</a>', 7, $_POST['boarddir']);
								if ($_GET['postid'] != '') {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . $cp . '"><a href="'. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . '">'. _gettext('Redirecting') . '</a> to ban page...';
								} elseif ($isquickdel) {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/res/'. $delpost_parentid . '.html"><a href="'. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/res/'. $delpost_parentid . '.html">'. _gettext('Redirecting') . '</a> back to thread...';
								}
							}
						} else {
							$tpl_page .= _gettext('Invalid thread ID '.$delpost_id.'. This may have been caused by the thread recently being deleted.');
						}
					}
				}
			}
			else {
				$tpl_page .= _gettext('Invalid board directory.');
			}
		}
		$tpl_page .= '<h2><BLINK style="color: #b30000">CURRENTLY BROKEN. PLEASE DO NOT USE THIS FORM!</BLINK><br>'. _gettext('Delete thread/post') . '</h2><br />'; //FIXME
		if (!$multidel) {
			$tpl_page .= '<form action="manage_page.php?action=delposts" method="post">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="boarddir">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('boarddir', $this->BoardList($_SESSION['manageusername'])) .
			'<br />

			<label for="delthreadid">'. _gettext('Thread') .':</label>
			<input type="text" name="delthreadid" /><br />

			<label for="fileonly">'. _gettext('File Only') .':</label>
			<input type="checkbox" id="fileonly" name="fileonly" /><br />

			<input type="submit" value="'. _gettext('Delete thread') .'" />

			</form>
			<br /><hr />

			<form action="manage_page.php?action=delposts" method="post">
			<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="boarddir">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('boarddir', $this->BoardList($_SESSION['manageusername'])) .
			'<br />

			<label for="delpostid">'. _gettext('Post') .':</label>
			<input type="text" name="delpostid" /><br />

			<label for="archive">'. _gettext('Archive') .':</label>
			<input type="checkbox" id="archive" name="archive" /><br />

			<label for="fileonly">'. _gettext('File Only') .':</label>
			<input type="checkbox" id="fileonly" name="fileonly" /><br />

			<input type="submit" value="'. _gettext('Delete post') .'" />

			</form>';
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Moderation Pages
	* +------------------------------------------------------------------------------+
	*/

		/* View and delete reports */
	function reports() {
		global $tc_db, $tpl_page;
		$this->ModeratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Reports') . '</h2><br />';
		if (isset($_GET['clear'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "reports` WHERE `id` = " . $tc_db->qstr($_GET['clear']) . " LIMIT 1");
			if (count($results) > 0) {
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "reports` SET `cleared` = '1' WHERE `id` = " . $tc_db->qstr($_GET['clear']));
				$tpl_page .= 'Report successfully cleared.<hr />';
			}
		}
		$query = "SELECT * FROM `" . KU_DBPREFIX . "reports` WHERE `cleared` = 0";
		if (!$this->CurrentUserIsAdministrator()) {
			$boardlist = $this->BoardList($_SESSION['manageusername']);
			if (!empty($boardlist)) {
				$query .= ' AND (';
				foreach ($boardlist as $board) {
					$query .= ' `board` = \''. $board['name'] .'\' OR';
				}
				$query = substr($query, 0, -3) . ')';
			} else {
				$tpl_page .= _gettext('You do not moderate any boards.');
			}
		}
		$resultsreport = $tc_db->GetAll($query);
		if (count($resultsreport) > 0) {
			$tpl_page .= '<table border="1" width="100%"><tr><th>Board</th><th>Post</th><th>File</th><th>Message</th><th>Reason</th><th>Reporter IP</th><th>Action</th></tr>';
			foreach ($resultsreport as $linereport) {
				$reportboardid = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($linereport['board']) . "");
				$results = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $reportboardid . " AND `id` = " . $tc_db->qstr($linereport['postid']) . "");
				foreach ($results as $line) {
					if ($line['IS_DELETED'] == 0) {
						$tpl_page .= '<tr><td>/'. $linereport['board'] . '/</td><td><a href="'. KU_BOARDSPATH . '/'. $linereport['board'] . '/res/';
						if ($line['parentid'] == '0') {
							$tpl_page .= $linereport['postid'];
							$post_threadorpost = 'thread';
						} else {
							$tpl_page .= $line['parentid'];
							$post_threadorpost = 'post';
						}
						$tpl_page .= '.html#'. $linereport['postid'] . '">'. $line['id'] . '</a></td><td>';
						if ($line['file'] == 'removed') {
							$tpl_page .= 'removed';
						} elseif ($line['file'] == '') {
							$tpl_page .= 'none';
						} elseif ($line['file_type'] == 'jpg' || $line['file_type'] == 'gif' || $line['file_type'] == 'png') {
							$tpl_page .= '<a href="'. KU_BOARDSPATH . '/'. $linereport['board'] . '/src/'. $line['file'] . '.'. $line['file_type'] . '"><img src="'. KU_BOARDSPATH . '/'. $linereport['board'] . '/thumb/'. $line['file'] . 's.'. $line['file_type'] . '" border="0"></a>';
						} else {
							$tpl_page .= '<a href="'. KU_BOARDSPATH . '/'. $linereport['board'] . '/src/'. $line['file'] . '.'. $line['file_type'] . '">File</a>';
						}
						$tpl_page .= '</td><td>';
						if ($line['message'] != '') {
							$tpl_page .= stripslashes($line['message']);
						} else {
							$tpl_page .= '&nbsp;';
						}
						$tpl_page .= '</td><td>';
						if ($linereport['reason'] != '') {
							$tpl_page .= htmlspecialchars(stripslashes($linereport['reason']));
						} else {
							$tpl_page .= '&nbsp;';
						}
						$tpl_page .= '</td><td>'. md5_decrypt($linereport['ip'], KU_RANDOMSEED) . '</td><td><a href="?action=reports&clear='. $linereport['id'] . '">Clear</a>&nbsp;&#91;<a href="?action=delposts&boarddir='. $linereport['board'] . '&del'. $post_threadorpost . 'id='. $line['id'] . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this thread/post?\');">D</a>&nbsp;<a href="'. KU_CGIPATH . '/manage_page.php?action=delposts&boarddir='. $linereport['board'] . '&del'. $post_threadorpost . 'id='. $line['id'] . '&postid='. $line['id'] . '" title="Delete &amp; Ban" onclick="return confirm(\'Are you sure you want to delete and ban this poster?\');">&amp;</a>&nbsp;<a href="?action=bans&banboard='. $linereport['board'] . '&banpost='. $line['id'] . '" title="Ban">B</a>&#93;</td></tr>';
					} else {
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "reports` SET `cleared` = 1 WHERE id = " . $linereport['id'] . "");
					}
				}
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= 'No reports to show.';
		}
	}

	/* Addition, modification, deletion, and viewing of bans */
	function bans() {
		global $tc_db, $tpl_page, $bans_class;

		$this->ModeratorsOnly();
		$reason = KU_BANREASON;
		$ban_ip = ''; $ban_hash = null; $ban_parentid = 0; $ban_ip_encrypted = false; $ban_board = null; $ban_ip_hash_compressed = null; $isquickban = false; $file_name = null;

		$limited_rights = $this->CurrentUserIsBoardOwner();

		// Retrieving ban information from the provided post and board ID
		if (isset($_GET['banboard']) && isset($_GET['banpost'])) {
			$ban_board_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['banboard']) . "");
			$ban_board = $_GET['banboard'];
			$ban_post_id = $_GET['banpost'];
			if (!empty($ban_board_id)) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = '" . $ban_board_id . "' AND `id` = " . $tc_db->qstr($_GET['banpost']) . "");
				if (count($results) > 0) {
					$ban_ip = $results[0]['ip']; // md5 encrypted IP
					$ban_ip_decrypted = md5_decrypt($ban_ip, KU_RANDOMSEED);
					if ($limited_rights) {
						$ban_ip_encrypted = $ban_ip;
						$ban_ip_hash_compressed = compress_md5(md5($ban_ip_decrypted . KU_RANDOMSEED));
					}
					else {
						$ban_ip = $ban_ip_decrypted;
					}
					$ban_parentid = $results[0]['parentid'];
				} else {
					$tpl_page.= _gettext('A post with that ID does not exist.') . '<hr />';
				}
			}
		}
		if (!$limited_rights && isset($_GET['banfile'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `file_md5`, `file`, `file_original`, `file_type` FROM `" . KU_DBPREFIX . "files` WHERE `file_id` = " . $tc_db->qstr($_GET['banfile']));
			if (count($results) > 0) {
				$ban_hash = $results[0]['file_md5'];
				$file_name = ($results[0]['file_original']!='/hidden' 
					? htmlentities($results[0]['file_original']) 
					: $results[0]['file']) 
				.".". $results[0]['file_type'];
			}
		}

		$tpl_page .= '<h2>'. _gettext('Bans') . '</h2><br />';

		// ======================================================= MAKING ACTIONS =======================================================

		// Setting the ban
		if (isset($_POST['ip']) && isset($_POST['seconds']) && !empty($_POST['ip']))  {
			// prevent repeated actions if the user refreshes the page and submits query again (seriously fuck this old way of doing shit but I'm too lazy to rewrite eveything)
			if ($_SESSION['ban_timectl'] != $_POST['timestamp']) {
				header('Location: '.$_SERVER['PHP_SELF'].'?action=bans');
				exit();
			}

			if ($_POST['seconds'] >= 0) {
				$banning_boards = array();
				$ban_boards = '';
				// Ban from all boards
				if (isset($_POST['banfromall'])) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `name` FROM `" . KU_DBPREFIX . "boards`");
					foreach ($results as $line) {
						if (!$this->CurrentUserIsModeratorOfBoard($line['name'], $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $line['name'] . '/: '. _gettext('You can only make bans applying to boards you moderate.'));
						}
					}
				}

				// Ban from particular boards
				else {
					if (empty($_POST['bannedfrom'])) {
						exitWithErrorPage(_gettext('Please select a board.'));
					}
					if(isset($_POST['deleteposts'])) {
						$_POST['deletefrom'] = $_POST['bannedfrom'];
					}
					foreach($_POST['bannedfrom'] as $board) {
						if (!$this->CurrentUserIsModeratorOfBoard($board, $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $board . '/: '. _gettext('You can only make bans applying to boards you moderate.'));
						}
					}
					$ban_boards = implode('|', $_POST['bannedfrom']);
				}
				
				// Ban options available only for admins
				if(!$this->CurrentUserIsAdministrator() && (isset($_POST['banfromall']) || isset($_POST['allowread']) || isset($_POST['type'])) )
					exitWithErrorPage(_gettext('Illegal action.'));
				else {
					if($this->CurrentUserIsAdministrator()) {
						$ban_globalban = (isset($_POST['banfromall'])) ? 1 : 0;
						$ban_allowread = ($_POST['allowread'] == 0) ? 0 : 1;
						$ban_type = ($_POST['type'] <= 2 && $_POST['type'] >= 0) ? $_POST['type'] : 0;
					}
					else {
						$ban_globalban = 0;
						$ban_allowread = 1;
						$ban_type = 0;
					}
				}

				// Quick ban
				if (isset($_POST['quickbanboardid'])) {
					$ban_board_id = $_POST['quickbanboardid'];
				}
				if(isset($_POST['quickbanboard'])) {
					$ban_board = $_POST['quickbanboard'];
				}
				if(isset($_POST['quickbanpostid'])) {
					$ban_post_id = $_POST['quickbanpostid'];
				}

				// Ban variables
				$ban_ip = isset($_POST['ban_ip_encrypted'])
					? md5_decrypt($_POST['ban_ip_encrypted'], KU_RANDOMSEED)
					: $_POST['ip'];
				$ban_duration = ($_POST['seconds'] == 0 ) ? 0 : $_POST['seconds'];
				$ban_reason = $_POST['reason'];
				$ban_note = $_POST['staffnote'];
				if (! $this->CurrentUserIsAdministrator()) {
					$ban_reason = htmlspecialchars($ban_reason);
					$ban_note = htmlspecialchars($ban_note);
				}
				$ban_appealat = 0;
				if (KU_APPEAL != '') {
					$ban_appealat = intval($_POST['appealdays'] * 86400);
					if ($ban_appealat > 0) $ban_appealat += time();
				}

				$ban_msg = '';
				$whitelist = $tc_db->GetAll("SELECT `ipmd5` FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = 2");
				if (in_array(md5($ban_ip), $whitelist)) {
					exitWithErrorPage(_gettext('That IP is on the whitelist'));
				}

				// Set ban in DB
				if ($bans_class->BanUser($ban_ip, $_SESSION['manageusername'], $ban_globalban, $ban_duration, $ban_boards, $ban_reason, $ban_note, $ban_appealat, $ban_type, $ban_allowread)) {
					$regenerate = null;
					// Place the ban message
					if (
						(
							KU_BANMSG != '' 
							|| 
							$_POST['banmsg'] != ''
						) 
						&& 
						isset($_POST['addbanmsg']) 
						&& 
						isset($ban_post_id) 
					) {
						$ban_msg = (KU_BANMSG == $_POST['banmsg'] || empty($_POST['banmsg'])) 
							? KU_BANMSG 
							: $_POST['banmsg'];
						if (! $this->CurrentUserIsAdministrator()) {
							$ban_msg = '<br /><font color="#FF0000"><b>'.htmlspecialchars($ban_msg).'</b></font>';
						}
						$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `message` = CONCAT(`message`, " . $tc_db->qstr($ban_msg) . ") WHERE `boardid` = " . $tc_db->qstr($ban_board_id) . " AND `id` = ".$tc_db->qstr($ban_post_id)." LIMIT 1");
						
						// Get the thread ID to regenerate
						$parentid = $tc_db->GetOne("SELECT `parentid` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $tc_db->qstr($ban_board_id) . " AND `id` = ".$tc_db->qstr($ban_post_id)." LIMIT 1");
						$thread_id = $parentid==0 ? $ban_post_id : $parentid;
						$regenerate = $thread_id;

						clearPostCache($ban_post_id, $ban_board_id);
					}
					$tpl_page .= _gettext('Ban successfully placed.')."<br />";
				} 
				else {
					exitWithErrorPage(_gettext('Sorry, a generic error has occurred.'));
				}

				// Regenerate pages 
				if (!is_null($regenerate)) {
					$board_class = new Board($ban_board);
					$board_class->RegenerateThreads($regenerate);
					$board_class->RegeneratePages();
					unset($board_class);
				}

				// Make a log record
				$logentry = _gettext('Banned')
				. (($ban_globalban == 1) ? ' ' . _gettext('from all all boards') : '')
				. (($ban_duration == 0)
					? ' '. _gettext('without expiration')
					: ' '. _gettext('until') . ' '. date('F j, Y, g:i a', time() + $ban_duration) )
				. ($ban_type==1 ? ' ('._gettext('range ban').')' : '')
				. ($ban_reason ? ' - '. _gettext('Reason') . ': '. htmlentities($ban_reason) : '')
				. ($ban_note ? ' ('.htmlentities($ban_note).')' : '');
				management_addlogentry($logentry, 8, explode('|', $ban_boards), $ban_ip);

				// Delete posts by IP
				if(isset($_POST['deleteposts'])) {
					$tpl_page .= '<br />';
					$this->deletepostsbyip(true, $ban_ip);
				}
			} 
			else {
				$tpl_page .= _gettext('Please enter a positive amount of seconds, or zero for a permanent ban.');
			}
			$tpl_page .= '<hr />';
		}

		// Banning hashes
		if (
			isset($_POST['banhashtime']) 
			&& 
			$_POST['banhashtime'] !== '' 
			&& 
			$_POST['hash'] !== ''
		) {
			if ($limited_rights) {
				$tpl_page .= _gettext('You cannot set this ban');
			}
			else {
				$banhash = $_POST['hash'];
				$results = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `".KU_DBPREFIX."bannedhashes` WHERE `md5` = ".$tc_db->qstr($banhash)." LIMIT 1");
				if ($results == 0) {
					$time = $_POST['banhashtime']==0 ? "''" : $tc_db->qstr($_POST['banhashtime']);
					$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."bannedhashes` ( `md5` , `bantime` , `description` ) VALUES ( ".$tc_db->qstr($banhash)." , ".$time." , ".$tc_db->qstr($_POST['banhashdesc'])." )");
					management_addlogentry('Banned md5 hash '. $banhash 
						. ($_POST['banhashdesc']!='' ? ' with a description of '. htmlentities($_POST['banhashdesc']): ''), 8 );
					$tpl_page .= _gettext('Hash successfully banned.')."<br />";
				}
				// Delete all instances of the file
				if (!$_POST['deleteposts'] && isset($_POST['deletefile'])) {
					$instances = $tc_db->GetAll("SELECT `_files`.`file_id`, `_boards`.`name` FROM 
						(SELECT `file_id`, `boardid` FROM `".KU_DBPREFIX."files` WHERE `file`!='removed' AND `file_md5`=".$tc_db->qstr($banhash).") `_files`
						JOIN (SELECT `id`, `name` FROM `".KU_DBPREFIX."boards` WHERE 1) `_boards`
						ON `_files`.`boardid`=`_boards`.`id`");
					if (count($instances)) {
						$boards = array();
						foreach($instances as &$instance) {
							$exists = array_key_exists($instance['board'], $boards);
							$board_class = $exists ? $boards[$instance['board']] : new Board($instance['board']);
							if (!$exists) {
								$boards[$instance['board']] = $board_class;
							}
							$board_class->DeleteFile($instance['file_id'], '', true);
						}
						foreach($boards as &$board_class) {
							$board_class->RegenerateAll();
						}
					}
				}
			}
		}

		// Redirect on quickban
		if (!empty($_POST['quickbanboard']) && !empty($_POST['quickbanthreadid'])) {
			$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_BOARDSPATH . '/'. $_POST['quickbanboard'] . '/';
			if ($_POST['quickbanthreadid'] != '0') $tpl_page .= 'res/'. $_POST['quickbanthreadid'] . '.html';
			$tpl_page .= '"><a href="'. KU_BOARDSPATH . '/'. $_POST['quickbanboard'] . '/';
			if ($_POST['quickbanthreadid'] != '0') $tpl_page .= 'res/'. $_POST['quickbanthreadid'] . '.html';
			$tpl_page .= '">'. _gettext('Redirecting') . '</a>...';
		}

		// Deleting ban
		elseif (isset($_GET['delban']) && $_GET['delban'] > 0) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['delban']) . "");
			if (count($results) > 0) {
				// Check is the user have all rights to unset this ban
				$has_rights = $this->CheckBanBoards($results[0]);
				if ($has_rights) {
					$unban_ip = md5_decrypt($results[0]['ip'], KU_RANDOMSEED);
					$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['delban']) . "");
					$bans_class->UpdateHtaccess();
					$tpl_page .= _gettext('Ban successfully removed.');
					$boards = explode('|', $results[0]['boards']);
					management_addlogentry(_gettext('Unbanned'), 8, $boards, $unban_ip);
				}
				else {
					$tpl_page .= _gettext('You cannot unset this ban');
				}				
			} 
			else {
				$tpl_page .= _gettext('Invalid ban ID');
			}
			$tpl_page .= '<br /><hr />';
		} 

		// Deleting banned hash
		elseif (isset($_GET['delhashid'])) {
			if ($limited_rights) {
				$tpl_page .= _gettext('You cannot unset this ban');
			}
			else {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "bannedhashes` WHERE `id` = " . $tc_db->qstr($_GET['delhashid']) . "");
				if (count($results) > 0) {
					$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "bannedhashes` WHERE `id` = " . $tc_db->qstr($_GET['delhashid']) . "");
					$tpl_page .= _gettext('Hash removed from ban list.') . '<br /><hr />';
				}
			}
		}

		flush();


		// ====================================================== BUILDING THE PAGE ======================================================

		$tpl_page .= '<form action="manage_page.php?action=bans" method="post" name="banform">';
		$now = microtime();
		$_SESSION['ban_timectl'] = $now;
		$tpl_page .=  '<input type="hidden" name="timestamp" value="'.$now.'" />';

		if (
			!empty($ban_ip) 
			&& 
			isset($_GET['banboard']) 
			&& 
			isset($_GET['banpost'])
		) {
			$isquickban = true;
			$tpl_page .= '<input type="hidden" name="quickbanboard" value="'. (isset($_GET['banboard']) ? $_GET['banboard'] : $_POST['board']) . '" />
			<input type="hidden" name="quickbanboardid" value="'. $ban_board_id . '" /><input type="hidden" name="quickbanthreadid" value="'. $ban_parentid . '" /><input type="hidden" name="quickbanpostid" value="'. $_GET['banpost'] . '" />'
			. ($ban_ip_encrypted ? '<input type="hidden" name="ban_ip_encrypted" value="'.$ban_ip_encrypted.'" />' : '');
		} 
		elseif (isset($_GET['ip'])) {
			$ban_ip = $_GET['ip'];
		}

		// File hash related fields
		if (!$limited_rights && isset($ban_hash)) {
			$tpl_page .= '<fieldset>

			<legend>'. _gettext('Ban file') . ' <i>"' . $file_name . '"</i></legend>
			<input name="hash" type="hidden" value="'. $ban_hash . '" />

			<br /><label for="deletefile">'. _gettext('Delete file') . ':</label>
			<input type="checkbox" name="deletefile" id="deletefile" checked /><br>

			<label for="banhashtime">'. _gettext('Ban duration for posting this file') . ':</label>
			<input type="text" name="banhashtime" id="banhashtime" />'
			. $this->makeBanTimePresets('banform.banhashtime')

			. '<label for="banhashdesc">'. _gettext('Ban file hash description') . ':</label>
			<input type="text" name="banhashdesc" id="banhashdesc" />
			<div class=desc">'. _gettext('The description of the image being banned. Not applicable if the above box is blank.') . '</div>
			</fieldset>';
		}

		// IP related fields
		$tpl_page .= '<fieldset>
		<legend>'. _gettext('IP address and ban type') . '</legend>
		<label for="ip">'. _gettext('IP') . ':</label>
		<input type="text" name="ip" id="ip" value="'. ($ban_ip_encrypted ? $ban_ip_hash_compressed : $ban_ip) . '"' 
		. ($ban_ip_encrypted ? 'readonly="readonly" style="opacity: .5" title ="' . _gettext('IP is encrypted') . '"' : '') . ' />
		<br /><label for="deleteposts">'. _gettext('Delete all posts by this IP') . ':</label>
		<input type="checkbox" name="deleteposts" id="deleteposts" />';

		if($this->CurrentUserIsAdministrator()) {
			$banmsg = '<br /><font color="#FF0000"><b>'.KU_BANMSG.'</b></font>';
			$tpl_page .= (KU_ALLOWREAD
				? '<br /><label for="allowread">'. _gettext('Allow read') . ':</label>
					<select name="allowread" id="allowread"><option value="1">'._gettext('Yes').'</option><option value="0">'._gettext('No').'</option></select>
					<div class="desc">'. _gettext('Whether or not the user(s) affected by this ban will be allowed to read the boards.') . '<br /><strong>'. _gettext('Warning') . ':</strong> '. _gettext('Selecting "No" will prevent any reading of any page on the level of the boards on the server. It will also act as a global ban.') . '</div>'
				: '<input name="allowread" type="hidden" value="1" />')

			. '<br /><label for="type">'. _gettext('Type') . ':</label>'
			// Ban type select
			. '<select name="type" id="type">'
			. '<option value="0">'. _gettext('Single IP') . '</option>'
			. (I0_IPLESS_MODE != 'true' ? '<option value="1">'. _gettext('IP Range') . '</option>' : '')
			. '<option value="2">'. _gettext('Whitelist') . '</option>'
			. '</select>'
			. '<div class="desc">'. _gettext('The type of ban. A single IP can be banned by providing the full address. A whitelist ban prevents that IP from being banned')
			. (I0_IPLESS_MODE != 'true' ? _gettext('An IP range can be banned by providing the IP range you would like to ban, in this format: 123.123.12') : '')
			.  '</div>';
		}
		else {
			$banmsg = KU_BANMSG;
		}

		if ($isquickban && KU_BANMSG != '') {
			$tpl_page .= '<br /><label for="addbanmsg">'. _gettext('Add ban message') . ':</label>
			<input type="checkbox" name="addbanmsg" id="addbanmsg" checked="checked" />
			<div class="desc">'. _gettext('If checked, the configured ban message will be added to the end of the post.') . '</div><br />
			<label for="banmsg">'. _gettext('Ban message') . ':</label>
			<input type="text" name="banmsg" id="banmsg" value="'. htmlspecialchars($banmsg) . '" size='. strlen($banmsg) . '" />';
		}
		$tpl_page .= '</fieldset>';

		// Boards
		$tpl_page .= '<fieldset>
		<legend> '. _gettext('Ban from') . '</legend>';
		if($this->CurrentUserIsAdministrator()) {
			$tpl_page .= '<label for="banfromall"><strong>'. _gettext('All boards') . '</strong></label>
			<input type="checkbox" name="banfromall" id="banfromall" /><br /><hr /><br />';
		}
		$tpl_page .= $this->MakeBoardListCheckboxes('bannedfrom', $this->BoardList($_SESSION['manageusername']))
		. '</fieldset>';

		// Ban duration etc
		$tpl_page .= '<fieldset>
		<legend>'. _gettext('Ban duration, reason, and appeal information') . '</legend>
		<label for="seconds">'. _gettext('Seconds') . ':</label>
		<input type="text" name="seconds" id="seconds" />'
		. $this->makeBanTimePresets('banform.seconds')

		. '<label for="reason">'. _gettext('Reason') . ':</label>
		<input type="text" name="reason" id="reason" value="'. $reason . '" />
		<div class="desc">'. _gettext('Presets') .':&nbsp;<a href="#" onclick="document.banform.reason.value=\''. _gettext('Child Pornography') .'\';return false;">CP</a>&nbsp;<a href="#" onclick="document.banform.reason.value=\''. _gettext('Proxy') .'\';return false;">'. _gettext('Proxy') .'</a></div><br />

		<label for="staffnote">'. _gettext('Staff Note') . '</label>
		<input type="text" name="staffnote" id="staffnote" />
		<div class="desc">'. _gettext('Presets') . ':&nbsp;<a href="#" onclick="document.banform.staffnote.value=\''. _gettext('Child Pornography') .'\';return false;">CP</a> || '. _gettext('This message will be shown only on this page and only to staff, not to the user.') .'</div><br />';

		if (KU_APPEAL != '') {
			$tpl_page .= '<label for="appealdays">'. _gettext('Appeal (days)') . ':</label>
			<input type="text" name="appealdays" id="appealdays" value="5" />
			<div class="desc">'. _gettext('Presets') . ':&nbsp;<a href="#" onclick="document.banform.appealdays.value=\'0\';return false;">'. _gettext('No Appeal') .'</a>&nbsp;<a href="#" onclick="document.banform.appealdays.value=\'5\';return false;">5 '. _gettext('days') .'</a>&nbsp;<a href="#" onclick="document.banform.appealdays.value=\'10\';return false;">10 '. _gettext('days') .'</a>&nbsp;<a href="#" onclick="document.banform.appealdays.value=\'30\';return false;">30 '. _gettext('days') .'</a></div><br />';
		}

		$tpl_page .= '</fieldset>

		<input type="submit" value="'. _gettext('Add ban') . '" /><img src="clear.gif" />

		</form>
		<hr /><br />';

		$by = $limited_rights ? " AND `by`= ".$tc_db->qstr($_SESSION['manageusername']) : "";

		for ($i = $limited_rights ? 0 : 2; $i >= 0; $i--) {
			switch ($i) {
				case 2:
					$tpl_page .= '<strong>'. _gettext('Whitelisted IPs') . ':</strong><br />';
					break;
				case 1:
					$tpl_page .= '<br /><strong>'. _gettext('IP Range Bans') . ':</strong><br />';
					break;
				case 0:
					if (!empty($ban_ip))
						$tpl_page .= '<br /><strong>'. _gettext('Previous bans on this IP') . ':</strong><br />';
					else
						$tpl_page .= '<br /><strong>'. _gettext('Single IP Bans') . ':</strong><br />';
					break;
			}
			$results = array();
			if (isset($_GET['allbans'])) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "'". $by." ORDER BY `id` DESC");
				$hiddenbans = 0;
			} 
			elseif (isset($_GET['limit'])) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' ORDER BY `id` DESC LIMIT ".intval($_GET['limit']));
				$results = $this->FilterBans($results);
				$hiddenbans = 0;
			} 
			else {
				if (!empty($ban_ip) && $i == 0) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `ipmd5` = '" . md5($ban_ip) . "' AND `type` = '" . $i . "'". $by." ORDER BY `id` DESC");
				} 
				else {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "'". $by." ORDER BY `id` DESC LIMIT 15");
					// Get the number of bans in the database of this type
					$hiddenbans = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "'");
					$hiddenbans = count($this->FilterBans($hiddenbans));
					if ($hiddenbans) {
						$hiddenbans -= 15; // Subtract 15 from the count, since we only want the number not shown
					}
				}
			}				
			if (count($results) > 0) {
				$tpl_page .= '<table border="1" width="100%"><tr><th>';
				$tpl_page .= ($i == 1) ? _gettext('IP Range') : _gettext('IP Address');
				$tpl_page .= '</th><th>'. _gettext('Boards') . '</th><th>'. _gettext('Reason') . '</th><th>'. _gettext('Staff Note') . '</th><th>'. _gettext('Date added') . '</th><th>'. _gettext('Expires/Expired') . '</th><th>'. _gettext('Added By') . '</th><th>&nbsp;</th></tr>';
				foreach ($results as $line) {
					$ip = md5_decrypt($line['ip'], KU_RANDOMSEED);
					$ip = $limited_rights
						? compress_md5(md5($ip . KU_RANDOMSEED)) 
						: '<a href="?action=bans&ip='. $ip . '">'. $ip . '</a>';
					$tpl_page .= '<tr><td>'.$ip.'</td><td>';
					if ($line['globalban'] == 1) {
						$tpl_page .= '<strong>'. _gettext('All boards') . '</strong>';
					} elseif (!empty($line['boards'])) {
						$tpl_page .= '<strong>/'. implode('/</strong>, <strong>/', explode('|', $line['boards'])) . '/</strong>&nbsp;';
					}
					$tpl_page .= '</td><td>';
					$tpl_page .= (!empty($line['reason'])) ? htmlentities(stripslashes($line['reason'])) : '&nbsp;';
					$tpl_page .= '</td><td>';
					$tpl_page .= (!empty($line['staffnote'])) ? htmlentities(stripslashes($line['staffnote'])) : '&nbsp;';
					$tpl_page .= '</td><td>'. date("F j, Y, g:i a", $line['at']) . '</td><td>';
					$tpl_page .= ($line['until'] == 0) ? '<strong>'. _gettext('Does not expire') . '</strong>' : date("F j, Y, g:i a", $line['until']);
					$tpl_page .= '</td><td>'. $line['by'] . '</td><td>[<a href="manage_page.php?action=bans&delban='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
				}
				$tpl_page .= '</table>';
				if ($hiddenbans > 0) {
					$tpl_page .= sprintf(_gettext('%s bans not shown.'), $hiddenbans) .
					' <a href="?action=bans&allbans=1">'. _gettext('View all bans') . '</a>'.' <a href="?action=bans&limit=100">View last 100 bans</a>';
				}
			} else {
				$tpl_page .= _gettext('There are currently no bans');
			}
		}
		if (!$limited_rights) {
			$tpl_page .= '<br /><br /><strong>'. _gettext('File hash bans') . ':</strong><br /><table border="1" width="100%"><tr><th>'. _gettext('Hash') . '</th><th>'. _gettext('Description') . '</th><th>'. _gettext('Ban time') . '</th><th>&nbsp;</th></tr>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `".KU_DBPREFIX."bannedhashes` "
				. ((!isset($_GET['allbans'])) ? (" LIMIT 5") : ("")) );
			if (count($results) == 0) {
				$tpl_page .= '<tr><td colspan="4">'. _gettext('None') . '</td></tr>';
			} 
			else {
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>'. $line['md5'] . '</td><td>'. $line['description'] . '</td><td>';
					$tpl_page .= ($line['bantime'] == 0) ? '<strong>'. _gettext('Does not expire') . '</strong>' : $line['bantime'] . ' seconds';
					$tpl_page .= '</td><td>[<a href="?action=bans&delhashid='. $line['id'] . '">x</a>]</td></tr>';
				}
			}
			$tpl_page .= '</table>';
		}
	}

	function FilterBans($bans) {
		if (!is_array($bans) || !count($bans))
			return array();
		return array_filter($bans, array(&$this, 'CheckBanBoards'));
	}
	// Check is a user has rights to manage a ban record
	function CheckBanBoards($ban_entry) {
		$checkboards = ($ban_entry['boards'] === '') ? array('allboards') : explode('|', $ban_entry['boards']);
		$all_rights = true;
		foreach($checkboards as $board_to_check) {
			if (!($this->CurrentUserIsModeratorOfBoard($board_to_check, $_SESSION['manageusername']))) {
				return false;
				$tpl_page .= _gettext('You cannot unset this ban');
				$all_rights = false;
				break;
			}
		}
		return true;
	}

	function appeals() {
		global $tc_db, $tpl_page, $bans_class;
		$this->ModeratorsOnly();
		$tpl_page .= '<h2>'. _gettext('Appeals') . '</h2><br />';
		$ban_ip = '';
		if (isset($_GET['accept'])) {
			if ($_GET['accept'] > 0) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['accept']) . "");
				if (count($results) > 0) {
					foreach ($results as $line) {
						$unban_ip = md5_decrypt($line['ip'], KU_RANDOMSEED);
					}
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "banlist` SET `expired` = 1, `appealat` = -4 WHERE `id` = " . $tc_db->qstr($_GET['accept']) . "");
					$bans_class->UpdateHtaccess();
					$tpl_page .= _gettext('Ban successfully removed.');
					management_addlogentry(_gettext('Appeal accepted'), 8, array(), $unban_ip);
				} else {
					$tpl_page .= _gettext('Invalid ID');
				}
				$tpl_page .= '<hr />';
			}
		} elseif (isset($_GET['deny'])) {
			if ($_GET['deny'] > 0) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['deny']) . "");
				if (count($results) > 0) {
					foreach ($results as $line) {
						$unban_ip = md5_decrypt($line['ip'], KU_RANDOMSEED);
					}
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "banlist` SET `appealat` = -2 WHERE `id` = " . $tc_db->qstr($_GET['deny']) . "");
					$bans_class->UpdateHtaccess();
					$tpl_page .= _gettext('Appeal successfully denied.');
					management_addlogentry(_gettext('Appeal denied'), 8, array(), $unban_ip);
				} else {
					$tpl_page .= _gettext('Invalid ID');
				}
				$tpl_page .= '<hr />';
			}
		}
		flush();

		for ($i = 1; $i >= 0; $i--) {
			if ($i == 1) {
				$tpl_page .= '<strong>'. _gettext('IP Range bans') . ':</strong><br />';
			} else {
				$tpl_page .= '<br /><strong>'. _gettext('Single IP Bans') . ':</strong><br />';
			}

			if ($ban_ip != '') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `ipmd5` = '" . md5($ban_ip) . "' AND `type` = '" . $i . "' AND `expired` = 0 ORDER BY `id` DESC");
			} else {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' AND `appealat` = -1 AND `expired` = 0 ORDER BY `id` DESC");
			}
			if (count($results) > 0) {
				$tpl_page .= '<table border="1" width="100%"><tr><th>';
				if ($i == 1) {
					$tpl_page .= 'IP Range';
				} else {
					$tpl_page .= 'IP Address';
				}
				$tpl_page .= '</th><th>Boards</th><th>Reason</th><th>Staff Note</th><th>Date Added</th><th>Expires</th><th>Added By</th><th>Appeal Message</th><th>Deny</th><th>Accept</th></tr>';
				foreach ($results as $line) {
					$tpl_page .= '<tr>';
					$tpl_page .= '<td><a href="?action=bans&ip='. md5_decrypt($line['ip'], KU_RANDOMSEED) . '">'. md5_decrypt($line['ip'], KU_RANDOMSEED) . '</a></td><td>';
					if ($line['globalban'] == '1') {
						$tpl_page .= '<strong>'. _gettext('All boards') . '</strong>';
					} else {
						if ($line['boards'] != '') {
							$tpl_page .= '<strong>/'. implode('/</strong>, <strong>/', explode('|', $line['boards'])) . '/</strong>&nbsp;';
						}
					}
					$tpl_page .= '</td><td>';
					if ($line['reason'] != '') {
						$tpl_page .= htmlentities(stripslashes($line['reason']));
					} else {
						$tpl_page .= '&nbsp;';
					}
					$tpl_page .= '</td><td>';
					if ($line['staffnote'] != '') {
						$tpl_page .= htmlentities(stripslashes($line['staffnote']));
					} else {
						$tpl_page .= '&nbsp;';
					}
					$tpl_page .= '</td><td>'. date("F j, Y, g:i a", $line['at']) . '</td><td>';
					if ($line['until'] == '0') {
						$tpl_page .= '<strong>'. _gettext('Does not expire') . '</strong>';
					} else {
						$tpl_page .= date("F j, Y, g:i a", $line['until']);
					}
					$tpl_page .= '</td><td>'. $line['by'] . '</td>
					<td>'.htmlentities($line['appeal']).'</td>
					<td><a href="manage_page.php?action=appeals&deny='. $line['id'] . '">:(</a></td>
					<td><a href="manage_page.php?action=appeals&accept='. $line['id'] . '">:)</a></td>';
					$tpl_page .= '</tr>';
				}
				$tpl_page .= '</table>';
				if ($hiddenbans>0) {
					$tpl_page .= sprintf(_gettext('%s bans not shown.'), $hiddenbans) .
					' <a href="?action=bans&allbans=1">'. _gettext('View all bans') . '</a>'.' <a href="?action=bans&limit=100">View last 100 bans</a>';
				}
			} else {
				$tpl_page .= _gettext('There are currently no bans.');
			}
		}
	}

	/* Search for all posts by a selected IP address and delete them */
	function deletepostsbyip($from_ban = false, $ip = null) {
		global $tc_db, $tpl_page, $board_class;
		$this->AdministratorsOnly();
		if (is_null($ip)) $ip = $_POST['ip']; // Setting this as default value produces an error for some reason
		if (!$from_ban) {
			$tpl_page .= '<h2>'. _gettext('Delete all posts by IP') . '</h2><br />';
		}
		if (isset($ip)) {
			if ($ip != '') {
        if (!$from_ban) {
          $this->CheckToken($_POST['token']);
        }
				$deletion_boards = array();
				$deletion_new_boards = array();
				$board_ids = '';
				if (isset($_POST['banfromall'])) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards`");
					foreach ($results as $line) {
						if (!$this->CurrentUserIsModeratorOfBoard($line['name'], $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $line['name'] . '/: '. _gettext('You can only delete posts from boards you moderate.'));
						}
						$delete_boards[$line['id']] = $line['name'];
						$board_ids .= $line['id'] . ',';
					}
				} else {
					if (empty($_POST['deletefrom'])) {
						exitWithErrorPage(_gettext('Please select a board.'));
					}
					foreach($_POST['deletefrom'] as $board) {
						if (!$this->CurrentUserIsModeratorOfBoard($board, $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $board . '/: '. _gettext('You can only delete posts from boards you moderate.'));
						}
						$id = $tc_db->GetOne("SELECT `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($board));
						$board_ids .= $tc_db->qstr($id) . ',';
						$delete_boards[$id] = $board;
					}
				}
				$board_ids = substr($board_ids, 0, -1);

				$i = 0; $boards_deleted = array();
				$post_list = $tc_db->GetAll("SELECT `id`, `boardid` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` IN (" . $board_ids . ") AND `IS_DELETED` = '0' AND `ipmd5` = '" . md5($ip) . "'");
				if (count($post_list) > 0) {
					foreach ($post_list as $post) {
						$i++;
						$post_class = new Post($post['id'], $delete_boards[$post['boardid']], $post['boardid']);
						$post_class->Delete();
						$boards_deleted[$post['boardid']] = $delete_boards[$post['boardid']];
						unset($post_class);
					}

					$tpl_page .= _gettext('All threads/posts by that IP in selected boards successfully deleted.') . '<br /><strong>'. $i . '</strong> posts were removed.<br />';
					management_addlogentry(_gettext('Deleted posts by ip'), 7, array(), $ip);
				}
				else {
					$tpl_page .= _gettext('No posts for that IP found');
				}
				if (count($boards_deleted)) {
					foreach ($boards_deleted as $board) {
						$board_class = new Board($board);
						$board_class->RegenerateAll();
						unset($board_class);
					}
				}
				$tpl_page .= '<hr />';
			}
		}
		if (!$from_ban) {
			$tpl_page .= '<form action="?action=deletepostsbyip" method="post">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<fieldset><legend>IP</legend>
			<label for="ip">'. _gettext('IP') .':</label>
			<input type="text" id="ip" name="ip"';
			if (isset($_GET['ip'])) {
				$tpl_page .= ' value="'. $_GET['ip'] . '"';
			}
			$tpl_page .= ' /></fieldset><br /><fieldset>
			<legend>'. _gettext('Boards') .'</legend>

			<label for="banfromall"><strong>'. _gettext('All boards') .'</strong></label>
			<input type="checkbox" id="banfromall" name="banfromall" /><br /><hr /><br />' .
			$this->MakeBoardListCheckboxes('deletefrom', $this->BoardList($_SESSION['manageusername'])) .
			'<br /></fieldset>

			<input type="submit" value="'. _gettext('Delete posts') .'" />

			</form>';
		}
	}

	/* View recently uploaded images */
	function recentimages() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		if (!isset($_SESSION['imagesperpage'])) {
			$_SESSION['imagesperpage'] = 50;
		}

		if (isset($_GET['show'])) {
			if ($_GET['show'] == '25' || $_GET['show'] == '50' || $_GET['show'] == '75' || $_GET['show'] == '100') {
				$_SESSION['imagesperpage'] = $_GET['show'];
			}
		}

		$tpl_page .= '<h2>'. _gettext('Recently uploaded images') . '</h2><br />
		'._gettext('Number of images to show per page').': <a href="?action=recentimages&show=25">25</a>, <a href="?action=recentimages&show=50">50</a>, <a href="?action=recentimages&show=75">75</a>, <a href="?action=recentimages&show=100">100</a> '._gettext('(note that this is a rough limit, more may be shown)').'<br />';
		if (isset($_POST['clear'])) {
			if ($_POST['clear'] != '') {
				$clear_decrypted = md5_decrypt($_POST['clear'], KU_RANDOMSEED);
				if ($clear_decrypted != '') {
					$clear_unserialized = unserialize($clear_decrypted);

					foreach ($clear_unserialized as $clear_sql) {
						$tc_db->Execute($clear_sql);
					}
					$tpl_page .= _gettext('Successfully marked previous images as reviewed.').'<hr />';
				}
			}
		}

		$dayago = (time() - 86400);
		$imagesshown = 0;
		$reviewsql_array = array();

		if ($imagesshown <= $_SESSION['imagesperpage']) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `" . KU_DBPREFIX . "boards`.`name` AS `boardname`, `" . KU_DBPREFIX . "posts`.`boardid` AS boardid, `" . KU_DBPREFIX . "posts`.`id` AS id, `" . KU_DBPREFIX . "posts`.`parentid` AS parentid, `" . KU_DBPREFIX . "posts`.`file` AS file, `" . KU_DBPREFIX . "posts`.`file_type` AS file_type, `" . KU_DBPREFIX . "posts`.`thumb_w` AS thumb_w, `" . KU_DBPREFIX . "posts`.`thumb_h` AS thumb_h FROM `" . KU_DBPREFIX . "posts`, `" . KU_DBPREFIX ."boards` WHERE (`file_type` = 'jpg' OR `file_type` = 'gif' OR `file_type` = 'png') AND `reviewed` = 0 AND `IS_DELETED` = 0 AND `" . KU_DBPREFIX . "boards`.`id` = `" . KU_DBPREFIX . "posts`.`boardid` ORDER BY `timestamp` DESC LIMIT " . intval($_SESSION['imagesperpage']));
			if (count($results) > 0) {
				$reviewsql = "UPDATE `" . KU_DBPREFIX . "posts` SET `reviewed` = 1 WHERE ";
				$tpl_page .= '<table border="1">'. "\n";
				foreach ($results as $line) {
					$reviewsql .= '(`boardid` = '.$line['boardid'] .' AND `id` = '. $line['id'] . ') OR ';
					$real_parentid = ($line['parentid'] == 0) ? $line['id'] : $line['parentid'];
					$tpl_page .= '<tr><td><a href="'. KU_BOARDSPATH . '/'. $line['boardname'] . '/res/'. $real_parentid . '.html#'. $line['id'] . '">/'. $line['boardname'] . '/'. $line['id'] . '</td><td><a href="'. KU_BOARDSPATH . '/'. $line['boardname'] . '/res/'. $real_parentid . '.html#'. $line['id'] . '"><img src="'. KU_BOARDSPATH . '/'. $line['boardname'] . '/thumb/'. $line['file'] . 's.'. $line['file_type'] . '" width="'. $line['thumb_w'] . '" height="'. $line['thumb_h'] . '" border="0"></a></td></tr>';
				}
				$tpl_page .= '</table>';

				$reviewsql = substr($reviewsql, 0, -3);
				$reviewsql_array[] = $reviewsql;
				$imagesshown += count($results);
			}
		}

		if ($imagesshown > 0) {
			$tpl_page .= '<br /><br />'. sprintf(_gettext('%s images shown.'), $imagesshown). '<br />';
			$tpl_page .= '<form action="?action=recentimages" method="post">
			<input type="hidden" name="clear" value="'. md5_encrypt(serialize($reviewsql_array), KU_RANDOMSEED) . '" />
			<input type="submit" value="'. _gettext('Clear All On Page As Reviewed') .'" />
			</form><br />';
		} else {
			$tpl_page .= '<br /><br />'. _gettext('No recent images currently need review.') ;
		}
	}

	/* View recently posted posts */
	function recentposts() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		if (!isset($_SESSION['postsperpage'])) {
			$_SESSION['postsperpage'] = 50;
		}

		if (isset($_GET['show'])) {
			if ($_GET['show'] == '25' || $_GET['show'] == '50' || $_GET['show'] == '75' || $_GET['show'] == '100') {
				$_SESSION['postsperpage'] = $_GET['show'];
			}
		}

		$tpl_page .= '<h2>'. _gettext('Recent posts') . '</h2><br />
		'._gettext('Number of posts to show per page').': <a href="?action=recentposts&show=25">25</a>, <a href="?action=recentposts&show=50">50</a>, <a href="?action=recentposts&show=75">75</a>, <a href="?action=recentposts&show=100">100</a> '._gettext('(note that this is a rough limit, more may be shown)').'<br />';
		if (isset($_POST['clear'])) {
			if ($_POST['clear'] != '') {
				$clear_decrypted = md5_decrypt($_POST['clear'], KU_RANDOMSEED);
				if ($clear_decrypted != '') {
					$clear_unserialized = unserialize($clear_decrypted);

					foreach ($clear_unserialized as $clear_sql) {
						$tc_db->Execute($clear_sql);
					}
					$tpl_page .= _gettext('Successfully marked previous posts as reviewed.').'<hr />';
				}
			}
		}

		$dayago = (time() - 86400);
		$postsshown = 0;
		$reviewsql_array = array();

		$boardlist = $this->BoardList($_SESSION['manageusername']);
		if ($postsshown <= $_SESSION['postsperpage']) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `" . KU_DBPREFIX . "boards`.`name` AS `boardname`, `" . KU_DBPREFIX . "posts`.`boardid` AS boardid, `" . KU_DBPREFIX . "posts`.`id` AS id, `" . KU_DBPREFIX . "posts`.`parentid` AS parentid, `" . KU_DBPREFIX . "posts`.`message` AS message, `" . KU_DBPREFIX . "posts`.`ip` AS ip FROM `" . KU_DBPREFIX . "posts`, `" . KU_DBPREFIX ."boards` WHERE `" . KU_DBPREFIX . "posts`.`timestamp` > " . $dayago . " AND `reviewed` = 0 AND `IS_DELETED` = 0 AND `" . KU_DBPREFIX . "boards`.`id` = `" . KU_DBPREFIX . "posts`.`boardid` ORDER BY `timestamp` DESC LIMIT " . intval($_SESSION['postsperpage']));
			if (count($results) > 0) {
				$reviewsql = "UPDATE `" . KU_DBPREFIX . "posts` SET `reviewed` = 1 WHERE ";
				$tpl_page .= '<table border="1" width="1005%">'. "\n";
				$tpl_page .= '<tr><th width="75px">'._gettext('Post Number').'</th><th>'._gettext('Post Message').'</th><th width="100px">'._gettext('Poster IP').'</th></tr>'. "\n";
				foreach ($results as $line) {
					$reviewsql .= '(`boardid` = '.$line['boardid'] .' AND `id` = '. $line['id'] . ') OR ';
					$real_parentid = ($line['parentid'] == 0) ? $line['id'] : $line['parentid'];
					$tpl_page .= '<tr><td><a href="'. KU_BOARDSPATH . '/'. $line['boardname'] . '/res/'. $real_parentid . '.html#'. $line['id'] . '">/'. $line['boardname'] . '/'. $line['id'] . '</td><td>'. stripslashes($line['message']) . '</td><td>'. md5_decrypt($line['ip'], KU_RANDOMSEED) . '</tr>';
				}
				$tpl_page .= '</table>';

				$reviewsql = substr($reviewsql, 0, -3) . ' LIMIT '. count($results);
				$reviewsql_array[] = $reviewsql;
				$postsshown += count($results);
			}
		}

		if ($postsshown > 0) {
			$tpl_page .= '<br /><br />'. sprintf(_gettext('%s posts shown.'), $postsshown) .'<br />
			<form action="?action=recentposts" method="post">
			<input type="hidden" name="clear" value="'. md5_encrypt(serialize($reviewsql_array), KU_RANDOMSEED) . '" />
			<input type="submit" value="'. _gettext('Clear All On Page As Reviewed') .'" />
			</form><br />';
		} else {
			$tpl_page .= '<br /><br />'. _gettext('No recent posts currently need review.') ;
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Misc Functions
	* +------------------------------------------------------------------------------+
	*/

	/* Show YAC info */
	function yac() {
		global $tpl_page, $yac;

		if (I0_YAC) {
			$yac_info = $yac->info();
			if (is_array($yac_info)) {
				$tpl_page .= '<table>';
				foreach($yac_info as $k=>$v) {
					$tpl_page .=  '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
				}
				$tpl_page .= '</table>';
			}
			$tpl_page .= '</ul><br /><br /><a href="?action=clearcache">Clear YAC cache</a>';
		} else {
			$tpl_page .= 'YAC isn\'t enabled!';
		}
	}

	/* Clear the YAC cache */
	function clearcache() {
		global $tpl_page, $yac;

		if (I0_YAC) {
			$yac->flush();
			$tpl_page .= 'YAC cache cleared.';
			management_addlogentry(_gettext('Cleared YAC cache'), 0);
		} else {
			$tpl_page .= 'YAC isn\'t enabled!';
		}
	}

	/* Generate a list of boards a moderator controls */
	function BoardList($username) {
		global $tc_db, $tpl_page;

		$staff_boardsmoderated = array();
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $username . "' LIMIT 1");
		if ($this->CurrentUserIsAdministrator() || $results[0][0] == 'allboards') {
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
			foreach ($resultsboard as $lineboard) {
					$staff_boardsmoderated = array_merge($staff_boardsmoderated, array(array( 'name' => $lineboard['name'], 'id' => $lineboard['id'])));
			}
		} else {
			if ($results[0][0] != '') {
				foreach ($results as $line) {
					$array_boards = explode('|', $line['boards']);
				}
				foreach ($array_boards as $this_board_name) {
					$this_board_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($this_board_name) . "");
					$staff_boardsmoderated = array_merge($staff_boardsmoderated, array(array('name' => $this_board_name, 'id' => $this_board_id)));
				}
			}
		}

		return $staff_boardsmoderated;
	}

	/* Generate a list of boards in query format */
	function sqlboardlist() {
		global $tc_db, $tpl_page;

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		$sqlboards = '';
		foreach ($results as $line) {
			$sqlboards .= 'posts_'. $line['name'] . ', ';
		}

		return substr($sqlboards, 0, -2);
	}

	/* Generate a dropdown box from a supplied array of boards */
	function MakeBoardListDropdown($name, $boards, $all = false) {
		$output = '<select name="'. $name . '"><option value="">'. _gettext('Select a Board') .'</option>';
		if (!empty($boards)) {
			if ($all) {
				$output .= '<option value="all">'. _gettext('All Boards') .'</option>';
			}
			foreach ($boards as $board) {
				$output .= '<option value="'. $board['name'] . '">/'. $board['name'] . '/</option>';
			}
		}
		$output .= '</select>';

		return $output;
	}

	/* Generate a series of checkboxes from a supplied array of boards */
	function MakeBoardListCheckboxes($boxname, $boards) {
		$output = '';

		if (!empty($boards)) {
			$single = (count($boards) == 1);
			foreach ($boards as $board) {
				$checked = (
					(
						isset($_GET['banboard']) 
						&& 
						($board["name"]==$_GET['banboard'])
					)
					||
					$single
				) ? 'checked' : '';
				$output .= '<label for="'. $boxname .'" >'. $board['name'] . '</label><input type="checkbox" name="'. $boxname . '[]" value="'. $board['name'] . '" '.$checked.' /> '."<br>";
			}
		}

		return $output;
	}

	function makeBanTimePresets($field='banform.seconds') {
		return '<div class="desc">'. _gettext('Presets') . ':&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'3600\';return false;">1hr</a>&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'86400\';return false;">1d</a>&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'259200\';return false;">3d</a>&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'604800\';return false;">1w</a>&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'1209600\';return false;">2w</a>&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'2592000\';return false;">30d</a>&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'31536000\';return false;">1yr</a>&nbsp;'
		.'<a href="#" onclick="document.'.$field.'.value=\'0\';return false;">'. _gettext('never') 
		.'</a></div><br>';
	}

	/* Generate a dropdown box for all sections */
	function MakeSectionListDropDown($name, $selected) {
		global $tc_db;

		$output = '<select name="'. $name . '"><option value="">'. _gettext('Select a Section') .'</option>'. "\n";
		$results = $tc_db->GetAll("SELECT `id`, `name` FROM `" . KU_DBPREFIX . "sections` ORDER BY `order` ASC");
		if(count($results) > 0) {
			foreach ($results as $section) {
				if ($section['id'] == $selected) {
					$select = ' selected="selected"';
				} else {
					$select = '';
				}
				$output .= '<option value="'. $section['id'] . '"'. $select . '>'. $section['name'] . '</option>'. "\n";
			}
		}
		$output .= '</select><br />'. "\n";

		return $output;
	}

	/* Delete files without their md5 stored in the database */
	function delunusedimages($verbose = false) {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards`");
		foreach ($resultsboard as $lineboard) {
			if ($verbose) {
				$tpl_page .= '<strong>'. _gettext('Looking for unused images in') .' /'. $lineboard['name'] . '/</strong><br />';
			}
			$file_md5list = array();
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `file_md5` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lineboard['id'] . " AND `IS_DELETED` = 0 AND `file` != '' AND `file` != 'removed' AND `file_md5` != ''");
			foreach ($results as $line) {
				$file_md5list[] = $line['file_md5'];
			}
			$dir = './'. $lineboard['name'] . '/src';
			$files = glob("$dir/{*.jpg, *.png, *.gif, *.swf}", GLOB_BRACE);
			if (is_array($files)) {
				foreach ($files as $file) {
					if (in_array(md5_file(KU_BOARDSDIR . $lineboard['name'] . '/src/'. basename($file)), $file_md5list) == false) {
						if (time() - filemtime(KU_BOARDSDIR . $lineboard['name'] . '/src/'. basename($file)) > 120) {
							if ($verbose == true) {
								$tpl_page .= sprintf(_gettext('A live record for %s was not found; the file has been removed.'), $file).'<br />';
							}
							unlink(KU_BOARDSDIR . $lineboard['name'] . '/src/'. basename($file));
							@unlink(KU_BOARDSDIR . $lineboard['name'] . '/thumb/'. substr(basename($file), 0, -4) . 's'. substr(basename($file), strlen(basename($file)) - 4));
							@unlink(KU_BOARDSDIR . $lineboard['name'] . '/thumb/'. substr(basename($file), 0, -4) . 'c'. substr(basename($file), strlen(basename($file)) - 4));
						}
					}
				}
			}
		}

		return true;
	}

	/* Delete replies currently not marked as deleted who belong to a thread which is marked as deleted */
	function delorphanreplies($verbose = false) {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards`");
		foreach ($resultsboard as $lineboard) {
			if ($verbose) {
				$tpl_page .= '<strong>'. _gettext('Looking for orphans in') .' /'. $lineboard['name'] . '/</strong><br />';
			}
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `parentid` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lineboard['id'] . " AND `parentid` != '0' AND `IS_DELETED` = 0");
			foreach ($results as $line) {
				$exists_rows = $tc_db->GetAll("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lineboard['id'] . " AND `id` = '" . $line['parentid'] . "' AND `IS_DELETED` = 0", 1);
				if ($exists_rows[0] == 0) {
					$post_class = new Post($line['id'], $lineboard['name'], $lineboard['id']);
					$post_class->Delete;
					unset($post_class);

					if ($verbose) {
						$tpl_page .= sprintf(_gettext('Reply #%1$s\'s thread (#%2$s) does not exist! It has been deleted.'),$line['id'],$line['parentid']).'<br />';
					}
				}
			}
		}

		return true;
	}

	function spam() {
		$this->BoardOwnersOnly();
		global $tc_db, $tpl_page;
		$tpl_page .= '<h2>'. _gettext('Spam.txt Management') .'</h2> <br />';

		if (isset($_GET['updateboard'])) {
      	    $this->CheckToken($_POST['token']);
			if (!$this->CurrentUserIsModeratorOfBoard($_GET['updateboard'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('You are not a moderator of this board.'));
			}
			$boardid = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['updateboard']) . " LIMIT 1");
			if ($boardid != '') {
				file_put_contents(KU_ROOTDIR . $_GET['updateboard']. '/spam.txt', $_POST['spam']);
				management_addlogentry(_gettext('Updated spam list'), 11, $_GET['updateboard']);
				$tpl_page .= '<hr />'. _gettext('Spam.txt successfully edited.') .'<hr />';
			}
			else {
				$tpl_page .= _gettext('Unable to locate a board named') . ' <strong>'. $_GET['updateboard'] . '</strong>.';
			}
		}
		elseif (isset($_POST['board'])) {
			if (!$this->CurrentUserIsModeratorOfBoard($_POST['board'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('You are not a moderator of this board.'));
			}
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
			if (count($resultsboard) > 0) {
				$spamfile = KU_ROOTDIR . $_POST['board']. '/spam.txt';
				$content = file_exists($spamfile) ? htmlspecialchars(file_get_contents($spamfile)) : '';
				$tpl_page .= '<p>Доска: '. $_POST['board'] . '</p>' . "\n" .
					'<p><b>PROTIP: </b>'._gettext("You don't have to include all variations of the word since it's done autamatically.").'</p>'.
					'<form action="?action=spam&updateboard=' . $_POST['board'] . '" method="post">'. "\n" .
          			'<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />' . "\n" .
          			'<input type="hidden" name="updateboard" value="' . $_POST['board'] . '" />' . "\n" .
					'<textarea name="spam" rows="25" cols="80">' . $content . '</textarea><br />' . "\n" .
					'<input type="submit" value="'. _gettext('Submit') .'" />'. "\n" .
					'</form>'. "\n";
			} else {
				$tpl_page .= _gettext('Unable to locate a board named') . ' <strong>'. $_POST['board'] . '</strong>.';
			}
		} else {
			$tpl_page .= '<p>'._gettext('You can create separate spam filters for any of your boards.').'</p>'
				.'<form action="?action=spam" method="post">
			<label for="board">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
			'<input type="submit" value="'. _gettext('Go') .'" />
			</form>';
		}
	}

	/* Gets the IP address of a post */
	function getip() {
		global $tc_db, $smarty, $tpl_page;
		if(!$this->CurrentUserIsModerator() && !$this->CurrentUserIsAdministrator()) {
			die();
		}
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['boarddir']));
		if (count($results) > 0) {
			if (!$this->CurrentUserIsModeratorOfBoard($_GET['boarddir'], $_SESSION['manageusername'])) {
				die();
			}
			$ip = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $tc_db->qstr($results[0]['id']) . " AND `id` = " . $tc_db->qstr($_GET['id']));
			die("dnb-".$_GET['boarddir']."-".$_GET['id']."-".(($ip[0]['parentid'] == 0) ? ("y") : ("n"))."=".md5_decrypt($ip[0]['ip'], KU_RANDOMSEED));
		}
		die();
	}
}
?>
