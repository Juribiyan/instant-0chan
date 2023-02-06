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
 */
/**
 * Board and Post classes
 *
 * @package kusaba
 */
/**
 * Board class
 *
 * Contains all board configurations.  This class handles all board page
 * rendering, using the templates
 *
 * @package kusaba
 *
 * TODO: replace repetitive code blocks with functions.
 */

class Board {
	/* Declare the public variables */
	/**
	 * Array to hold the boards settings
	 */
	var $board = array();
	/**
	 * Archive directory, set when archiving is enabled
	 *
	 * @var string Archive directory
	 */
	var $archive_dir;
	/**
	 * Smarty class
	 *
	 * @var class Smarty
	 */
	var $smarty;
	/**
	 * Load balancer class
	 *
	 * @var class Load balancer
	 */
	var $loadbalancer;

	/**
	 * Initialization function for the Board class, which is called when a new
	 * instance of this class is created. Takes a board directory as an
	 * argument
	 *
	 * @param string $board Board name/directory
	 * @param boolean $extra grab additional data for page generation purposes. Only false if all that's needed is the board info.
	 * @return class
	 */
	function __construct($board, $extra = true, $for_overboard = false) {
		global $tc_db, $CURRENTLOCALE;

		// If the instance was created with the board argument present, get all of the board info and configuration values and save it inside of the class
		if ($board!='') {
			$query = "SELECT * FROM `".KU_DBPREFIX."boards` WHERE `name` = ".$tc_db->qstr($board)." LIMIT 1";
			$results = $tc_db->GetAll($query);
			foreach ($results[0] as $key=>$line) {
				if (!is_numeric($key)) {
					$this->board[$key] = $line;
				}
			}
			if ($extra) {
				if (!$for_overboard) {
					// Boardlist
					$this->board['boardlist'] = $this->DisplayBoardList();

					// Get the unique posts for this board
					$this->board['uniqueposts']   = $tc_db->GetOne("SELECT COUNT(DISTINCT `ipmd5`) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id']. " AND  `IS_DELETED` = 0");
					
					if ($this->board['locale'] && $this->board['locale'] != KU_LOCALE) {
						changeLocale($this->board['locale']);
					}
				}
				// Make a combined array of allowed filetypes
				$filetypes_allowed = $tc_db->GetAll("SELECT ".KU_DBPREFIX."filetypes.filetype
					FROM ".KU_DBPREFIX."boards, ".KU_DBPREFIX."filetypes, ".KU_DBPREFIX."board_filetypes
					WHERE ".KU_DBPREFIX."boards.id = " . $this->board['id'] . "
					AND ".KU_DBPREFIX."board_filetypes.boardid = " . $this->board['id'] . "
					AND ".KU_DBPREFIX."board_filetypes.typeid = ".KU_DBPREFIX."filetypes.id
					ORDER BY ".KU_DBPREFIX."filetypes.filetype ASC;");
				$embeds_allowed = array_filter(explode(',', $this->board['embeds_allowed']));
				$this->board['embeds_allowed'] = array();
				$this->board['embeds_allowed_assoc'] = array();
				if ($embeds_allowed) {
					$tc_db->SetFetchMode(ADODB_FETCH_ASSOC);
					$embeds = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "embeds`
						WHERE `filetype` IN ('" . implode("','", $embeds_allowed) . "')" );
					if ($embeds) {
						foreach($embeds as $embed) {
							$this->board['embeds_allowed_assoc'][$embed['filetype']] = $embed;
							$this->board['embeds_allowed'] []= $embed;
						}
					}
				}
				foreach($filetypes_allowed as $filetype) {
					if ($filetype['filetype']=="*") {
						$this->board['any_filetype'] = true;
						$this->board['filetypes_allowed'] = array();
					}
					else
						$this->board['filetypes_allowed'] []= $filetype['filetype'];
				}
				$ftypes = $tc_db->GetAll("SELECT `filetype` FROM `" . KU_DBPREFIX . "embeds`");
				$this->board['filetypes'] = array();
				foreach ($ftypes as $line) {
					$this->board['filetypes'] []= $line['filetype'];
				}
			} // ← /extra
			$this->board['loadbalanceurl_formatted'] = ($this->board['loadbalanceurl'] != '') ? substr($this->board['loadbalanceurl'], 0, strrpos($this->board['loadbalanceurl'], '/')) : '';

			if ($this->board['loadbalanceurl'] != '' && $this->board['loadbalancepassword'] != '') {
				require_once KU_ROOTDIR . 'inc/classes/loadbalancer.class.php';
				$this->loadbalancer = new Load_Balancer;

				$this->loadbalancer->url = $this->board['loadbalanceurl'];
				$this->loadbalancer->password = $this->board['loadbalancepassword'];
			}
		}
	}

	function __destruct() {
		changeLocale(KU_LOCALE);
	}

	/**
	 * Regenerate all board and thread pages
	 */
	function RegenerateAll($except_boardlist=false) {
		$this->RegeneratePages();
		if (I0_DEFERRED_RENDER) {
			RemoveFiles(KU_BOARDSDIR.$this->board['name'].'/res/*.html');
		}
		else {
			$this->RegenerateThreads();
		}
		if (I0_OVERBOARD_ENABLED && !$except_boardlist && $this->board['section'] != '0' && $this->board['hidden'] == '0') {
			RegenerateOverboard($this->board['boardlist']);
		}
	}

	/**
	 * Determine the page the thread is on, and the total number of pages
	 */
	function GetPageNumber($threadid) {
		global $tc_db;
		$tc_db->SetFetchMode(ADODB_FETCH_ASSOC);

		$threads = $tc_db->GetAll("SELECT 
			(`id`='".$threadid."') AS `the_one`
			FROM `".KU_DBPREFIX."posts` 
			WHERE
			 `boardid`='".$this->board['id']."'
			 AND
			 `IS_DELETED`=0
			 AND
			 `parentid`=0
			ORDER BY `bumped` DESC");
		$i = 0; 
		$found = false;
		$count = count($threads);
		for ($i=0; $i < $count; $i++) { 
			if ($threads[$i]['the_one']==1) {
				$found = floor($i / KU_THREADS);
				break;
			}
		}
		return array(
			'page' => $found,
			'n_pages' => ceil($count / KU_THREADS)
		);
	}

	/**
	 * Regenerate pages ($from #page $to #page, including pages from $singles)
	 */
	function RegeneratePages($from=-1, $to=INF, $singles=array(), $on_demand=false) {
		global $tc_db, $CURRENTLOCALE;

		if ($from == -1)
			$from = 0;

		// In deferred rendering mode simply delete the pages
		if (I0_DEFERRED_RENDER && !$on_demand) {
			$dir = KU_BOARDSDIR.$this->board['name'];
			@unlink($dir."/catalog.json");
			@unlink($dir."/catalog.html");
			if (is_infinite($to)) {
				RemoveFiles($dir."/*.html");
			}
			else {
				for ($i=$from; $i <= $to; $i++) { 
					@unlink($dir.'/'.($i==0 ? 'index' : $i).'.html');
				}
				foreach ($singles as &$i) {
					@unlink($dir.'/'.($i==0 ? 'index' : $i).'.html');
				}
			}
			return;
		}

		$tc_db->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->InitializeSmarty();

		$skip_catalog = $this->board['enablecatalog'] == 0 || (I0_DEFERRED_RENDER && $on_demand!='catalog');

		// get thread list
		$threads = $tc_db->GetAll("SELECT *
			FROM `" . KU_DBPREFIX . "postembeds`
			WHERE `boardid` = " . $this->board['id'] . "
			AND `parentid` = 0
			AND `IS_DELETED` = 0
			ORDER BY `stickied` DESC, `bumped` DESC");
		$threads = group_embeds($threads, true);
		$total_threads = count($threads);

		// split threads into pages →
		$pages = array();
		for ($i=0; $i < $total_threads; $i++) {
			$page = floor($i / KU_THREADS);
			$thread =& $threads[$i];
			// fill thread stats →
			if (!$skip_catalog || ($page >= $from && $page <= $to) || in_array($page, $singles)) {
				$thread['page'] = $page;
				$stats = $tc_db->GetAll("SELECT
					COUNT(DISTINCT `id`) `reply_count`,
					SUM(CASE WHEN `file_md5` != '' THEN 1 ELSE 0 END) `images`".
					(!$skip_catalog ? 
						", MAX(`timestamp`) `replied`,
						MAX(`id`) `last_reply`" : "")
				." FROM `".KU_DBPREFIX."postembeds`
				WHERE `boardid` = '". $this->board['id'] ." '
					AND `IS_DELETED` = 0
					AND `parentid` = '". $thread['id'] ."'");
				$stats = $stats[0];
				$thread['reply_count'] = $stats['reply_count'];
				$thread['images']      = $stats['images'];
				if (!$skip_catalog) {
					$thread['replied']     = $stats['replied'];
					$thread['last_reply']  = $stats['last_reply'];
				}
				foreach($thread['embeds'] as $embed) {
					if ($embed != '') {
						$thread['images']++;
					}
				}
			} // ← fill thread stats
			$pages[$page] []=& $thread;
		} // ← split thread into pages
		$totalpages = count($pages);

		// rebuild pages needing to be rebuilt →
		if ($on_demand != 'catalog') {
			$page = 0;
			if ($totalpages==0) {
				$pages []= array();
			}
			$this->smarty->assign('numpages', $totalpages-1);
			$rebuilt = 0;
			foreach ($pages as $pagethreads) {
				if (($page >= $from && $page <= $to) || in_array($page, $singles)) {
					$rebuilt++;
					// page must be rebuilt
					$executiontime_start_page = microtime_float();
					$newposts = array();
					$this->smarty->assign('thispage', $page);
					foreach ($pagethreads as &$thread) {
						// Get last posts to render →
						$posts = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."postembeds`
							JOIN (
								SELECT DISTINCT `id`
								FROM `".KU_DBPREFIX."postembeds`
								WHERE `boardid` = '". $this->board['id'] ." '
									AND `parentid` = ".$thread['id']." 
									AND `IS_DELETED` = 0
								ORDER BY `id` DESC
								LIMIT ".(($thread['stickied'] == 1) ? (KU_REPLIESSTICKY) : (KU_REPLIES))."
							) `uniq_id` 
							ON `".KU_DBPREFIX."postembeds`.`id` = `uniq_id`.`id`
							WHERE `boardid` = '". $this->board['id'] ." '
							ORDER BY `uniq_id`.`id` desc");

						$posts = group_embeds($posts, true);

						$images_shown = 0;
						foreach ($posts as &$post) {
							foreach($post['embeds'] as $embed) {
								if ($embed['file_md5'] != '') {
									$images_shown++;
								}
							}
							$post = $this->BuildPost($post, true);
						}
						$posts = array_reverse($posts);
						// ← Get last posts to render

						// Calculate omitted posts and images →
						$omitted_replies = $thread['reply_count'] - count($posts);
						if ($omitted_replies < 0) $omitted_replies = 0;
						foreach($thread['embeds'] as $embed) {
							if ($embed['file_md5'] != '') {
								$images_shown++;
							}
						}
						$omitted_images = $thread['images'] - $images_shown;
						if ($omitted_images < 0) $omitted_images = 0;
						// ← Calculate omitted posts and images

						$thread = $this->BuildPost($thread, true);

						$thread['replies'] = $omitted_replies;
						$thread['images'] = $omitted_images;

						array_unshift($posts, $thread);
						$newposts[] = $posts;
					}
					if (!isset($embeds)) {
						$embeds = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "embeds`");
					}
					if (!isset($header)){
						$header = $this->PageHeader();
						$header = str_replace("<!sm_threadid>", 0, $header);
					}
					if (!isset($postbox)) {
						$postbox = $this->Postbox();
						$postbox = str_replace("<!sm_threadid>", 0, $postbox);
					}
					$this->smarty->assign('posts', $newposts);
					$this->smarty->assign('file_path', getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], ''));

					$content = $this->smarty->fetch('board_main_loop.tpl');
					$footer = $this->Footer(false, (microtime_float() - $executiontime_start_page), false);
					$content = $header.$postbox.$content.$footer;

					$content = str_replace("\t", '',$content);
					$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

					$filename = KU_BOARDSDIR.$this->board['name'].'/'.($page==0 ? KU_FIRSTPAGE : '/'.$page.'.html');
					$this->PrintPage($filename, $content, $this->board['name']);
				}
				$page++;
			} // ← rebuild pages needing to be rebuilt

			if ($on_demand) return $rebuilt;
		} // ← rebuild pages needing to be rebuilt

		// rebuild catalog →
		if (!$skip_catalog) {
			$executiontime_start_catalog = microtime_float();
			$catalog_head = $this->PageHeader(0,0,-1,1).
			'<script src="'.KU_BOARDSFOLDER.'lib/javascript/lodash.min.js"></script>'.
			'<script> is_catalog=true; </script>'.
			/*'&#91;<a href="' . KU_BOARDSFOLDER . $this->board['name'] . '/">'._gettext('Return').'</a>&#93; '.
			'&#91;<a href="#" id="refresh_catalog">'._gettext('Refresh').'</a>&#93;'.*/
			'<div class="catalogmode">'.
			_gettext('Catalog Mode').'<div id="catalog-controls"></div></div>' . "\n".
			'<div id="catalog-contents"></div>';

			$catalog_nojs = '<table border="1" align="center">' . "\n" . '<tr>' . "\n";

			// Fields to go into JSON file
			$json_fields = array('id' , 'subject' , 'message', 'timestamp', 'stickied', 'locked', 'bumped', 'name', 'tripcode', 'posterauthority', 'deleted_timestamp', 'page', 'reply_count', 'replied', 'last_reply', 'images');
			$img_fields = array('file' , 'file_type', 'image_w', 'image_h', 'thumb_w', 'thumb_h');
			$catalog_json = array();

			if ($total_threads > 0) {
				$celnum = 0;
				$trbreak = 0;
				$row = 1;
				// Calculate the number of rows we will actually output
				$maxrows = max(1, (($total_threads - ($total_threads % 12)) / 12));
				foreach ($threads as &$thread) {
					// populate JSON object along the way →
					unset($thread_json);
					foreach ($json_fields as $field) {
						$thread_json[$field] = $thread[$field];
					}
					if (count($thread['embeds'])) {
						$thread_json['embeds'] = array();
						foreach($thread['embeds'] as $embed) {
							$embed_json = array();
							foreach($img_fields as $field) {
								$embed_json[$field] = $embed[$field];
							}
							$thread_json['embeds'] []= $embed_json;
						}
					}
					$catalog_json []= $thread_json;
					// ← populate JSON object along the way

					$celnum++;
					$trbreak++;
					if ($trbreak == 13 && $celnum != $total_threads) {
						$catalog_nojs .= '</tr>' . "\n" . '<tr>' . "\n";
						$row++;
						$trbreak = 1;
					}
					if ($row <= $maxrows) {
						$catalog_nojs .= '<td valign="middle">' . "\n" .
						'<a class="catalog-entry" href="' . KU_BOARDSFOLDER . $this->board['name'] . '/res/' . $thread['id'] . '.html"';
						if ($thread['subject'] != '') {
							$catalog_nojs .= ' title="' . $thread['subject'] . '"';
						}
						$catalog_nojs .= '>';

						$file_found = false;
						foreach ($thread['embeds'] as $embed) {
							if ($embed['file'] != 'removed') {
								if (in_array($embed['file_type'], array('jpg', 'png', 'gif', 'webm', 'mp4'))) {
									$file_found = $embed;
									break;
								}
								elseif (!$file_found) {
									$file_found = $embed;
								}
							}
							elseif (!$file_found) {
								$file_found = $embed;
							}
						}

						if ($file_found) {
							if ($file_found['file'] !== 'removed') {
								if ($file_found['file_type'] == 'webm' || $file_found['file_type'] == 'mp4')
									$file_found['file_type'] = 'jpg';
								if (in_array($file_found['file_type'], array('jpg', 'png', 'gif'))) {
									$file_path = getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], $this->archive_dir);
									$catalog_nojs .= '<img src="' . $file_path . '/thumb/' . $file_found['file'] . 'c.' . $file_found['file_type'] . '" alt="' . $thread['id'] . '" border="0" />';
								}
								else {
									$catalog_nojs .= _gettext('File');
								}
							}
							else {
								$catalog_nojs .= 'Rem.';
							}
						}
						else {
							$catalog_nojs .= _gettext('None');
						}
						$catalog_nojs .= '</a><br />' . "\n" . '<small>' . $thread['reply_count'] . '</small>' . "\n" . '</td>' . "\n";
					}
				}
			}
			else {
				$catalog_nojs .= '<td>' . "\n" . _gettext('No threads.') . "\n" . '</td>' . "\n";
			}
			$catalog_nojs .= '</tr>' . "\n" . '</table><br /><hr />';
			$catalog_foot = $this->Footer(false, (microtime_float()-$executiontime_start_catalog));
			$catalog_html = $catalog_head . '<noscript>'.$catalog_nojs.'</noscript>' . $catalog_foot;
			$this->PrintPage(KU_BOARDSDIR . $this->board['name'] . '/catalog.html', $catalog_html, $this->board['name']);
			$this->PrintPage(KU_BOARDSDIR . $this->board['name'] . '/catalog.json', json_encode($catalog_json), $this->board['name']);
			
			if ($on_demand == 'catalog')
				return true;
		} // ← rebuild catalog

		$this->DeleteOldPages($totalpages-1);
	}

	function DeleteOldPages($totalpages) {
		$dir = KU_BOARDSDIR.$this->board['name'];
		$files = glob ("$dir/*.html");
		if (is_array($files)) {
			foreach ($files as $htmlfile) {
				if (
					preg_match("/[0-9+].html/", $htmlfile)
					&&
					substr(basename($htmlfile), 0, strpos(basename($htmlfile), '.html')) > $totalpages
				)
				unlink($htmlfile);
			}
		}
	}

	/**
	 * Generate HTML for a thread on overboard
	 */
	function GenerateOverboardThreadFragment($op_id) {
		global $tc_db;
		$tc_db->SetFetchMode(ADODB_FETCH_ASSOC);

		$debug_str = "#".$op_id." from /".$this->board['name']."/ (".$this->board['desc']."): ";

		// fill thread stats →
		$stats = $tc_db->GetAll("SELECT
			COUNT(DISTINCT `id`) `reply_count`,
			SUM(CASE WHEN `file_md5` != '' THEN 1 ELSE 0 END) `images`
		FROM `".KU_DBPREFIX."postembeds`
		WHERE `boardid` = '". $this->board['id'] ." '
			AND `IS_DELETED` = 0
			AND `parentid` = '". $op_id ."'");
		$stats = $stats[0];
		// ← fill thread stats

		// Get OP + last posts to render →
		$posts = $tc_db->GetAll(
			"SELECT * FROM `".KU_DBPREFIX."postembeds`
			JOIN (
				SELECT 
					`id`, 
					(CASE WHEN `parentid`='0' THEN 1 ELSE 0 END) `is_op`
				FROM `".KU_DBPREFIX."posts`
				WHERE 
					`boardid` = '". $this->board['id'] ."' 
					AND (`id`='". $op_id ."' OR `parentid` = '". $op_id ."')
					AND `IS_DELETED` = 0
				ORDER BY `is_op` DESC, `id` DESC
				LIMIT ".(KU_REPLIES+1)."
			) `uniq_id` 
			ON `postembeds`.`id` = `uniq_id`.`id`
			WHERE `boardid` = '". $this->board['id'] ."'
			ORDER BY `uniq_id`.`id` ASC");
		$posts = group_embeds($posts, true);
		// ← Get OP + last posts to render

		$posts[0]['reply_count'] = $stats['reply_count'];
		$posts[0]['images']      = $stats['images'];

		// Calculate omitted posts and images →
		$images_shown = 0;
		$i = 0; foreach ($posts as &$post) {
			if ($i == 0) { // OP

			}
			else { // reply
				foreach($post['embeds'] as $embed) {
					if ($embed['file_md5'] != '') {
						$images_shown++;
					}
				}
			}
			$post = $this->BuildPost($post, true);
		$i++; }unset($post);
		$omitted_replies = $posts[0]['reply_count'] - (count($posts) - 1);
		if ($omitted_replies < 0) $omitted_replies = 0;
		$omitted_images = $posts[0]['images'] - $images_shown;
		if ($omitted_images < 0) $omitted_images = 0;

		$posts[0]['replies'] = $omitted_replies;
		$posts[0]['images'] = $omitted_images;
		// ← Calculate omitted posts and images

		$this->smarty->assign('locale', KU_LOCALE); // No idea why it can't be done in board.php 
		$this->smarty->assign('posts', array($posts));
		$this->smarty->assign('for_overboard', 1); // Indicate that this is overboard, for board_main_loop
		$this->smarty->assign('file_path', getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], ''));
		
		$content = $this->smarty->fetch('board_main_loop.tpl');
		
		$content = str_replace("\t", '',$content);
		$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

		return $content;
	}

	/**
	 * Regenerate each thread's corresponding html file, starting with the most recently bumped
	 */
	function RegenerateThreads($id = 0, $on_demand=false) {
		global $tc_db, $CURRENTLOCALE;
		// In deferred render mode, simply invalidate html cache
		if (I0_DEFERRED_RENDER && !$on_demand) {
			$dir = KU_BOARDSDIR.$this->board['name'].'/res/';
			if ($id==0) {
				RemoveFiles($dir."*.html");
			}
			else {
				@unlink($dir.$id.'.html');
			}
			return;
		}
		$this->InitializeSmarty();
		$numimages = 0;
		$embeds = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "embeds`");
		foreach ($embeds as $embed) {
			$this->board['filetypes'][] .= $embed['filetype'];
		}
		$this->smarty->assign('filetypes', $this->board['filetypes']);
		if ($id == 0) {
			// Build every thread
			$header = $this->PageHeader(1);
			$postbox = $this->Postbox(1);
			$threads = $tc_db->GetAll("SELECT `id` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0 ORDER BY `id` DESC");
			if (count($threads) > 0) {
				foreach($threads as $thread) {
					$this->BuildThread($thread['id'], $header, $postbox);
				}
			}
		}
		else {
			return $this->BuildThread($id);
		}
	}

	function BuildThread($id, $header=null, $postbox=null) {
		global $tc_db, $CURRENTLOCALE;
		$numimages = 0;
		$executiontime_start_thread = microtime_float();
		$is_thread = $tc_db->GetOne("SELECT `parentid` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid`='".$this->board['id']."' AND `id`='".$id."' AND `IS_DELETED` = 0");
		if ($is_thread === "0") {
			$posts = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "postembeds` WHERE `boardid` = " . $this->board['id'] . " AND (`id` = " . $id . " OR `parentid` = " . $id . ") AND `IS_DELETED` = 0 ORDER BY `id` ASC");

			$posts = group_embeds($posts, true);
			foreach ($posts as $key=>$post) {
				foreach($post['embeds'] as $embed) {
					if (($embed['file_type'] == 'jpg' || $embed['file_type'] == 'gif' || $embed['file_type'] == 'png') && (isset($embed['parentid']) && $embed['parentid'] != 0)) {
						$numimages++;
					}
				}
				$posts[$key] = $this->BuildPost($post, false);
			}

			$header_replaced = $header ? str_replace("<!sm_threadid>", $id, $header) : $this->PageHeader($id);
			$this->smarty->assign('numimages', $numimages);
			$this->smarty->assign('isthread', true);
			$this->smarty->assign('posts', array($posts)); // Wrap the posts into array to keep unified structure with board page
			$this->smarty->assign('file_path', getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], ''));
		 
			if (!$postbox) {
				$postbox = $this->Postbox($id);
			}
			$postbox_replaced = str_replace("<!sm_threadid>", $id, $postbox);
			$reply   = $this->smarty->fetch('board_reply_header.tpl');
			$content = $this->smarty->fetch('board_main_loop.tpl');
			if (!isset($footer)) $footer = $this->Footer(false, (microtime_float() - $executiontime_start_thread), false);
			$content = $header_replaced.$reply.$postbox_replaced.$content.$footer;

			$content = str_replace("\t", '',$content);
			$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);
			$this->PrintPage(KU_BOARDSDIR . $this->board['name'] . $this->archive_dir . '/res/' . $id . '.html', $content, $this->board['name']);
			return 1;
		}
		return 0;
	}

	function BuildPost($post, $page) {
		global $CURRENTLOCALE;

		$dateEmail = (empty($this->board['anonymous'])) ? $post['email'] : 0;
		//by Snivy
		if(KU_CUTPOSTS) {
			$post['message'] = stripslashes(formatLongMessage($post['message'], $this->board['name'], (($post['parentid'] == 0) ? ($post['id']) : ($post['parentid'])), $page));
		}
		else {
			$post['message'] = stripslashes($post['message']);
		}
		$post['timestamp_formatted'] = formatDate($post['timestamp'], 'post', $CURRENTLOCALE, $dateEmail);
		$post['reflink'] = formatReflink($this->board['name'], (($post['parentid'] == 0) ? ($post['id']) : ($post['parentid'])), $post['id'], $CURRENTLOCALE);
		
		$post['deleted_timestamp_formatted'] = formatDate($post['deleted_timestamp'], 'post', $CURRENTLOCALE, $dateEmail);
		$post_ttl = $post['deleted_timestamp'] > time() ? ($post['deleted_timestamp'] - time())/3600 : 0;
		$post['ttl'] = $post_ttl ? sprintf('%02d:%02d', (int)$post_ttl, round(fmod($post_ttl, 1) * 60)) : 0;

		$post['hash_id'] = md5($post['ipmd5'].((KU_IMGHASH_UNIQUENESS=='board'||KU_IMGHASH_UNIQUENESS=='thread') ? '_'.$this->board['name'] : '').(KU_IMGHASH_UNIQUENESS=='thread' ? '_'.($post['parentid']=='0' ? $post['id'] : $post['parentid']) : ''));
		
		foreach ($post['embeds'] as &$embed) {
			if (array_key_exists($embed['file_type'], $this->board['embeds_allowed_assoc'])) {
				$embed['is_embed'] = true;
				$embed_site = $this->board['embeds_allowed_assoc'][$embed['file_type']];
				$embed['thumbnail'] = $embed['file_type'].'-'.$embed['file'].'-s.jpg';
				$embed['site_name'] = $embed_site['name'];
				$embed['videourl'] = $embed_site['videourl'].$embed['file'];
				if ($embed['file_size'] > 0) {
					$h = floor($embed['file_size'] / 3600);
					if ($h) $time .= $h.'h';
					$m = floor(($embed['file_size'] / 60) % 60);
					if ($m) $time .= $m.'m';
					$s = $embed['file_size'] % 60;
					if ($s) $time .= $s.'s';
					$embed['start'] = $time;
					$embed['videourl'] .= $embed_site['timeprefix'].$time;
				}
			}
			if ($embed['file_type'] == 'mp3' && $this->board['loadbalanceurl'] == '') {
				require_once(KU_ROOTDIR . 'lib/getid3/getid3.php');
				$getID3 = new getID3;
				$embed['id3'] = $getID3->analyze(KU_BOARDSDIR.$this->board['name'].'/src/'.$embed['file'].'.mp3');
				getid3_lib::CopyTagsToComments($embed['id3']);
			}
			if (
				$embed['file_type']!='jpg'
				&&
				$embed['file_type']!='gif'
				&&
				$embed['file_type']!='png'
				&&
				$embed['file_type']!=''
				&&
				!in_array($embed['file_type'], $this->board['filetypes']) // FIXME
			) {
				if(!isset($filetype_info[$embed['file_type']]))
					$filetype_info[$embed['file_type']] = getfiletypeinfo($embed['file_type']);
				if ($filetype_info[$embed['file_type']][0] == "*") {
					$embed['generic_icon'] = 2;
					$embed['nonstandard_file'] = true;
				}
				else {
					$embed['nonstandard_file'] = KU_WEBPATH . '/inc/filetypes/' . $filetype_info[$embed['file_type']][0];
					if($embed['thumb_w']!=0&&$embed['thumb_h']!=0) {
						if(file_exists(KU_BOARDSDIR.$this->board['name'].'/thumb/'.$embed['file'].'s.jpg'))
							$embed['nonstandard_file'] = KU_WEBPATH . '/' .$this->board['name'].'/thumb/'.$embed['file'].'s.jpg';
						elseif(file_exists(KU_BOARDSDIR.$this->board['name'].'/thumb/'.$embed['file'].'s.png'))
							$embed['nonstandard_file'] = KU_WEBPATH . '/' .$this->board['name'].'/thumb/'.$embed['file'].'s.png';
						elseif(file_exists(KU_BOARDSDIR.$this->board['name'].'/thumb/'.$embed['file'].'s.gif'))
							$embed['nonstandard_file'] = KU_WEBPATH . '/' .$this->board['name'].'/thumb/'.$embed['file'].'s.gif';
						else {
							$embed['generic_icon'] = 1;
							$embed['thumb_w'] = $filetype_info[$embed['file_type']][1];
							$embed['thumb_h'] = $filetype_info[$embed['file_type']][2];
						}
					}
					else {
						$embed['generic_icon'] = 1;
						$embed['thumb_w'] = $filetype_info[$embed['file_type']][1];
						$embed['thumb_h'] = $filetype_info[$embed['file_type']][2];
					}
				}
			}
		}
	
		return $post;
	}

	/**
	 * Build the page header
	 *
	 * @param integer $replythread The ID of the thread the header is being build for.  0 if it is for a board page
	 * @param integer $liststart The number which the thread list starts on (text boards only)
	 * @param integer $liststooutput The number of list pages which will be generated (text boards only)
	 * @return string The built header
	 */
	function PageHeader($replythread = '0', $liststart = '0', $liststooutput = '-1', $is_catalog = '0', $debug=false) {
		global $tc_db, $CURRENTLOCALE;

		$tpl = Array();

		$tpl['htmloptions'] = ((KU_LOCALE == 'he' && (!isset($this->board['locale']) && empty($this->board['locale']))) || (isset($this->board['locale']) && $this->board['locale'] == 'he')) ? ' dir="rtl"' : '' ;

		$tpl['title'] = '';

		if (KU_DIRTITLE) {
			$tpl['title'] .= '/' . $this->board['name'] . '/ - ';
		}
		$tpl['title'] .= $this->board['desc'];

		$ad_top = 185;
		$ad_right = 25;
		if ($replythread!=0) {
			$ad_top += 50;
		}
		$this->smarty->assign('title', $tpl['title']);
		$this->smarty->assign('htmloptions', $tpl['htmloptions']);
		$this->smarty->assign('locale', $CURRENTLOCALE);
		$this->smarty->assign('ad_top', $ad_top);
		$this->smarty->assign('ad_right', $ad_right);
		$this->smarty->assign('board', $this->board);
		$this->smarty->assign('replythread', $replythread);
		$this->smarty->assign('is_catalog', $is_catalog);
		$this->smarty->assign('filetypes', isset($this->board['filetypes']) ? $this->board['filetypes'] : array());
		$topads = $tc_db->GetOne("SELECT code FROM `" . KU_DBPREFIX . "ads` WHERE `position` = 'top' AND `disp` = '1'");
		$this->smarty->assign('topads', $topads);
		// #snivystuff include alien style
		$styles =  explode(':', KU_STYLES);
		$defaultstyle = isset($this->board['defaultstyle']) ? $this->board['defaultstyle'] : null;
		if(!empty($defaultstyle)) {
			if(!in_array($defaultstyle, $styles)) {
				$custom_style_version = $tc_db->GetOne("SELECT `version` FROM `customstyles` WHERE `name` = '".$defaultstyle."'");
				if($custom_style_version > 0) {
					$styles[]= $defaultstyle;
					$this->smarty->assign('customstyle', $defaultstyle);
					$this->smarty->assign('csver', $custom_style_version);
				}
			}
			else { $this->smarty->assign('customstyle', false); }
		}
		else $defaultstyle = KU_DEFAULTSTYLE;
		$this->smarty->assign('ku_styles', $styles);
		$this->smarty->assign('ku_defaultstyle', $defaultstyle);
		$this->smarty->assign('boardlist', $this->board['boardlist']);
		$this->PrebuildBoardlist();
		$this->smarty->assign('boardlist_prebuilt', $this->board['boardlist_prebuilt']);

		$global_header = $this->smarty->fetch('global_board_header.tpl');

		$header = $this->smarty->fetch('board_header.tpl');

		return $global_header.$header;
	}

	/**
	 * Generate the postbox area
	 *
	 * @param integer $replythread The ID of the thread being replied to.  0 if not replying
	 * @param string $postboxnotice The postbox notice
	 * @return string The generated postbox
	 */
	function Postbox($replythread = 0) {
		global $tc_db;
		if (KU_BLOTTER) {
			$this->smarty->assign('blotter', getBlotter());
			$this->smarty->assign('blotter_updated', getBlotterLastUpdated());
		}
		return $this->smarty->fetch('board_post_box.tpl');
	}

	/**
	 * Display the user-defined list of boards found in boards.html
	 * * Snivy added section description for better header
	 * @param boolean $is_textboard If the board this is being displayed for is a text board
	 * @return string The board list
	 */
	function DisplayBoardList($is_textboard = false) {
		if (KU_GENERATEBOARDLIST) {
			global $tc_db;
			$output = '';
			$results = $tc_db->GetAll("SELECT 
				`id`,`name`,`abbreviation`, if(`abbreviation`='20', 1, 0) as `is_20` 
				FROM `" . KU_DBPREFIX . "sections` 
				ORDER BY `is_20` ASC, `order` ASC");
			$boards = array();
			foreach($results AS $line) {
				$boards[$line['id']]['is_20'] = $line['is_20'];
				$boards[$line['id']]['nick'] = htmlspecialchars($line['name']);
				$boards[$line['id']]['abbreviation'] = htmlspecialchars($line['abbreviation']);
				$results2 = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "boards` WHERE `section` = '" . $line['id'] . "' AND `hidden` != 1 ORDER BY `order` ASC, `name` ASC");
				foreach($results2 AS $line2) {
					$boards[$line['id']][$line2['id']]['name'] = htmlspecialchars($line2['name']);
					$boards[$line['id']][$line2['id']]['desc'] = htmlspecialchars($line2['desc']);
				}
			}
		} else {
			$boards = KU_ROOTDIR . 'boards.html';
		}

		return $boards;
	}

	function PrebuildBoardlist() {
		if (!isset($this->board['boardlist_prebuilt']))
			$this->board['boardlist_prebuilt'] = $this->smarty->fetch('boardlist.tpl');
	}

	/**
	 * Display the page footer
	 *
	 * @param boolean $noboardlist Force the board list to not be displayed
	 * @param string $executiontime The time it took the page to be created
	 * @param boolean $hide_extra Hide extra footer information, and display the manage link
	 * @return string The generated footer
	 */
	function Footer($noboardlist = false, $executiontime = '', $hide_extra = false) {
		global $tc_db;

		$footer = '';

		if ($hide_extra || $noboardlist) $this->smarty->assign('boardlist', '');

		if ($executiontime != '') $this->smarty->assign('executiontime', $executiontime);
	
		$botads = $tc_db->GetOne("SELECT code FROM `" . KU_DBPREFIX . "ads` WHERE `position` = 'bot' AND `disp` = '1'");
		$this->smarty->assign('botads', $botads);
		$footer = $this->smarty->fetch('board_footer.tpl');
	
		$footer .= $this->smarty->fetch('global_board_footer.tpl');

		return $footer;
	}

	/**
	 * Finalize the page and print it to the specified filename
	 *
	 * @param string $filename File to print the page to
	 * @param string $contents Page contents
	 * @param string $board Board which the file is being generated for
	 * @return string The page contents, if requested
	 */
	function PrintPage($filename, $contents, $board) {
		if ($board !== true) {
			print_page($filename, $contents, $board);
		} 
		else {
			echo $contents;
		}
	}

	/**
	 * Initialize the instance of smary which will be used for generating pages
	 */
	function InitializeSmarty() {
		if (isset($this->smarty)) return;

		$this->smarty = new _Smarty();

		$this->smarty->assign('cwebpath', getCWebpath());
		$this->smarty->assign('boardpath', getCLBoardPath());
	}

	/**
	 * Enable/disable archive mode
	 *
	 * @param boolean $mode True/false for enabling/disabling archive mode
	 */
	function ArchiveMode($mode) {
		$this->archive_dir = ($mode && $this->board['enablearchiving'] == 1) ? '/arch' : '';
	}

	function EraseFileAndThumbs($file) {
		global $tc_db;

		$dups_exist = $tc_db->GetOne("SELECT COUNT(*) FROM `".KU_DBPREFIX."postembeds`
			WHERE `file_md5`= ? 
			AND `boardid` = ?
			AND `IS_DELETED` = 0
			AND `file` != 'removed'", array($file['file_md5'], $this->board['id']));
		if ($dups_exist)
			return;

		$files = GetFileAndThumbs($file);
		$boardname = $this->board['name'];
		foreach($files as $f) {
			@unlink(KU_BOARDSDIR.$boardname.$f);
		}
	}

	function DeleteFile($file_id, $pass, $ismod) {
		global $tc_db;
		if (! is_numeric($file_id))
			return array('error' => _gettext('Invalid file id'));
		$boardname = $this->board['name'];
		$postfile = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."postembeds` WHERE `file_id`=".$tc_db->qstr($file_id));
		if (!$postfile)
			return array('error' => _gettext('File does not exist.'));
		$postfile = $postfile[0];
		if ($postfile['file'] == 'removed')
			return array(
				'error' => false,
				'already_deleted' => true
			);
		
		if ($ismod) {
			$pass = $postfile['password'];
		}
		else {
			// Delpass hashing
			$passtype = $postfile['password'] [0];
			if ($passtype == '+') {
				$pass = $passtype . md5($pass . $postfile['id'] . $postfile['boardid'] . KU_RANDOMSEED);
			}
			elseif ($passtype == '-') {
				$pass = $passtype . md5($pass . KU_RANDOMSEED);
			}
			else {
				$pass = md5($pass);
			}
		}

		if ($postfile['password'] != $pass) {
			return array('error' => _gettext('Incorrect password.'));
		}
		$erase = !$ismod && I0_ERASE_DELETED;
		if (!$boardname || $boardname != $this->board['name']) {
			$boardname = $tc_db->GetOne("SELECT `name` FROM `boards` WHERE `id`=".$tc_db->qstr($postfile['boardid']));
		}
		clearPostCache($postfile['id'], $boardname);
		$tc_db->Execute("UPDATE `".KU_DBPREFIX."files` SET `file`='removed'".
			($erase ? $this::FILE_ERASE : '').
			" WHERE `file_id`=".$tc_db->qstr($file_id));
		$this->EraseFileAndThumbs($postfile);
		$parentid = $postfile['parentid']=='0' ? $postfile['id'] : $postfile['parentid'];
		if ($ismod) {
			management_addlogentry(_gettext('Deleted file') .
				' "' . $postfile['file_original'] . '.' . $postfile['file_type'] .'" ' .
				_gettext('from') . ' #<a href="/'.$boardname.'/res/'.$parentid.'.html#'. $postfile['id'] . '">'. $postfile['id'] . '</a> - /'. $boardname . '/', 7);
		}
		return array(
			'error' => false,
			'parentid' => $parentid
		);
	}

	const FILE_ERASE = ",
		`file_md5` = '',
		`file_original` = '',
		`file_size` = 0,
		`file_size_formatted` = '',
		`image_w` = 0,
		`image_h` = 0,
		`thumb_w` = 0,
		`thumb_h` = 0,
		`spoiler` = 0";
}

/**
 * Post class
 *
 * Used for post insertion, deletion, and reporting.
 *
 * @package kusaba
 */
class Post extends Board {
	// Declare the public variables
	var $post = Array();

	function __construct($postid, $board, $boardid, $is_inserting = false) {
		global $tc_db;

		$results = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."postembeds` WHERE `boardid` = '" . $boardid . "' AND `id` = ".$tc_db->qstr($postid));
		$results = group_embeds($results);

		if (count($results)==0&&!$is_inserting) {
			exitWithErrorPage('Invalid post ID.');
		} elseif ($is_inserting) {
			parent::__construct($board, false);
		} else {
			foreach ($results[0] as $key=>$line) {
				if (!is_numeric($key)) $this->post[$key] = $line;
			}
			$results = $tc_db->GetAll("SELECT `cleared` FROM `".KU_DBPREFIX."reports` WHERE `postid` = ".$tc_db->qstr($this->post['id'])." LIMIT 1");
			if (count($results)>0) {
				foreach($results AS $line) {
					$this->post['isreported'] = ($line['cleared'] == 0) ? true : 'cleared';
				}
			} else {
				$this->post['isreported'] = false;
			}
			$this->post['isthread'] = ($this->post['parentid'] == 0) ? true : false;
			if (empty($this->board) || $this->board['name'] != $board) {
				parent::__construct($board, false);
			}
		}
	}

	const POST_ERASE = ",
		`name` = '',
		`tripcode` = '',
		`email` = '',
		`subject` = '',
		`message` = '',
		`country` = '',
		`password` = ''";

	function CheckAccessLocked() {
		global $tc_db;

		$attempts = $tc_db->GetOne("SELECT `attempts`
		 FROM `".KU_DBPREFIX."posts`
		 WHERE 
			`id` = ".$this->post['id']." AND
			`boardid` = ".$this->board['id']);
		if ((int)$attempts >= I0_MAX_ACCESS_ATTEMPTS) {
			return true;
		}
		else {
			$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
			 SET `attempts` = `attempts`+1
			 WHERE 
				`id` = ".$this->post['id']." AND
				`boardid` = ".$this->board['id']);
			return false;
		}
	}

	function Unlock() {
		global $tc_db;
		$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
		 SET `attempts` = 0
		 WHERE 
			`id` = ".$this->post['id']." AND
			`boardid` = ".$this->board['id']);
	}

	function Delete($allow_archive = false, $erase = false) {
		global $tc_db;
		if ($this->post['IS_DELETED'])
			return 'already_deleted';
		$boardid = $this->board['id'];
		$postid = $this->post['id'];
		$boardname = $this->board['name'];
		if ($this->post['isthread'] == true) {
			$files = $tc_db->GetAll("SELECT *
			 FROM
				`".KU_DBPREFIX."postembeds`
			 WHERE
				`boardid` = '" . $boardid . "'
				AND (
				 `id` = ".$tc_db->qstr($postid)."
				 OR
				 `parentid` = ".$tc_db->qstr($postid)."
				)");

			//Archiving. Probably does not work lol
			if ($allow_archive && $this->board['enablearchiving'] == 1 && $this->board['loadbalanceurl'] == '') {
				$this->ArchiveMode(true);
				$this->RegenerateThreads($postid);
				foreach($files as $file) {
					if ($file['file'] != 'removed' && $file['file_size'] > 0) {
						@copy(KU_BOARDSDIR . $boardname . '/src/' . $file['file'] . '.' . $file['filetype'], KU_BOARDSDIR . $boardname . $this->archive_dir . '/src/' . $file['file'] . '.' . $file['filetype']);
						@copy(KU_BOARDSDIR . $boardname . '/thumb/' . $file['file'] . 's.' . $file['filetype'], KU_BOARDSDIR . $boardname . $this->archive_dir . '/thumb/' . $file['file'] . 's.' . $file['filetype']);
					}
				}
			}
			if ($allow_archive && $this->board['enablearchiving'] == 1) {
				$this->ArchiveMode(false);
			}

			// Delete HTML pages
			@unlink(KU_BOARDSDIR.$boardname.'/res/'.$postid.'.html');
			@unlink(KU_BOARDSDIR.$boardname.'/res/'.$postid.'-100.html');
			@unlink(KU_BOARDSDIR.$boardname.'/res/'.$postid.'+50.html');

			// Collect ID's
			$file_ids = array(); $post_ids = array();
			foreach($files as $file) {
				$post_id = "'".$file['id']."'";
				if (!in_array($post_id, $post_ids)) {
					$post_ids []= $post_id;
					clearPostCache($post_id, $boardname, true);
				}
				$file_ids []= "'".$file['file_id']."'";
			}
			// Mark files as removed in db
			if (!empty($file_ids))
				$tc_db->Execute("UPDATE `".KU_DBPREFIX."files`
				 SET
					`file`='removed'".
					($erase ? $this::FILE_ERASE : '')."
				 WHERE
					`boardid` = '" . $boardid . "'
					AND
					`file_id` IN (".implode(',', $file_ids).")");
			// Mark posts as deleted
			$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
			 SET
				`IS_DELETED` = 1 ,
				`deleted_timestamp` = '" . time() . "'".
				($erase ? $this::POST_ERASE : '')."
			 WHERE
				`boardid` = '" . $boardid . "'
				AND
				`id` IN (".implode(',', $post_ids).")");
			// Physically delete all files
			foreach($files as $file) {
				if ($file['file'] != 'removed' && $file['file_size'] > 0)
					$this->EraseFileAndThumbs($file);
			}
			// Clear reports
			$tc_db->Execute("DELETE FROM `".KU_DBPREFIX."reports`
			 WHERE
				`boardid` = '" . $boardid . "'
				AND
				`id` IN (".implode(',', $post_ids).")");

			return (count($post_ids)+1).' '; // huh?
		}
		else {
			// Collect ID's
			$file_ids = array();
			foreach($this->post['embeds'] as $embed) {
				$file_ids []= "'".$embed['file_id']."'";
			}
			// Mark post as deleted
			$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
			 SET
				`IS_DELETED` = 1 ,
				`deleted_timestamp` = '" . time() . "'".
				($erase ? $this::POST_ERASE : '')."
			 WHERE
				`boardid` = '" . $boardid . "'
				AND
				`id` = ".$tc_db->qstr($postid));
			if ($this->post['embeds']) {
				// Mark files as removed in db
				$tc_db->Execute("UPDATE `".KU_DBPREFIX."files`
				 SET
					`file`='removed'".
					($erase ? $this::FILE_ERASE : '')."
				 WHERE
					`boardid` = '" . $boardid . "'
					AND
					`file_id` IN (".implode($file_ids, ',').")");
				// Physically delete all files
				foreach($this->post['embeds'] as $embed) {
					$this->EraseFileAndThumbs($embed);
				}
			}
			// Un-bump threda
			$bumped = $tc_db->GetOne("SELECT `bumped`
				FROM `".KU_DBPREFIX."posts`
				WHERE
				 `boardid`=".$boardid." 
				 AND
				 `id`=".$this->post['parentid']);
			$bump = $tc_db->GetOne("SELECT `timestamp`
				FROM `".KU_DBPREFIX."posts`
				WHERE
				 `boardid`=".$boardid." 
				 AND
				 (`id`=".$this->post['parentid']." 
					OR 
					`parentid`=".$this->post['parentid'].")
				 AND
				 `email`!='sage' 
				 AND
				 `IS_DELETED`=0
				ORDER BY `timestamp` DESC");
			$unbumped = 1;
			if ($bumped != $bump) {
				$unbumped = 'unbumped';
				$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
				 SET `bumped`=?
				 WHERE
					`boardid`=? 
					AND
					`id`=?",
				array($bump, $boardid, $this->post['parentid']) );
			}
			
			clearPostCache($postid, $boardname);

			return $unbumped;
		}
	}

	function Insert($parentid, $name, $tripcode, $email, $subject, $message, $attachments, $password, $timestamp, $bumped, $ip, $posterauthority, $stickied, $locked, $boardid, $country, $is_new_user, $deleted_timestamp) {
		global $tc_db;
		// Get the ID for manual auto-increment ()
		$new_id = $tc_db->GetOne("SELECT `id` FROM `posts` WHERE `boardid`= ".$tc_db->qstr($boardid)." ORDER BY `id` DESC LIMIT 1");
		if (!$new_id) {
			if ($this->board['start'] > 1) {
				$new_id = $this->board['start'];
			}
			else {
				$new_id = 1;
			}
		}
		else {
			$new_id++;
		}
		$post_fields = array(
			$new_id,
			$parentid,
			$boardid,
			$name,
			$tripcode,
			$email,
			$subject,
			$message,
			$password,
			$timestamp,
			$bumped,
			I0_FULL_ANONYMITY_MODE ? '' : md5_encrypt($ip, KU_RANDOMSEED),
			I0_FULL_ANONYMITY_MODE ? '' : md5($ip),
			$posterauthority,
			$stickied,
			$locked,
			$country,
			$is_new_user ? 1 : 0,
			$deleted_timestamp
		);
		foreach($post_fields as &$pf) {
			$pf = $tc_db->qstr($pf);
		}
		$query = "INSERT INTO `".KU_DBPREFIX."posts` (
			`id`,
			`parentid`, 
			`boardid`, 
			`name` , 
			`tripcode` , 
			`email` , 
			`subject` , 
			`message` , 
			`password` , 
			`timestamp` , 
			`bumped` , 
			`ip` , 
			`ipmd5` , 
			`posterauthority` , 
			`stickied` , 
			`locked`, 
			`country`, 
			`by_new_user`,
			`deleted_timestamp` )
			VALUES ( ".implode(', ', $post_fields)." )";
		$tc_db->Execute($query);
		// $id = $tc_db->Insert_Id();
		$sqlerr = $tc_db->ErrorNo();
		if ($sqlerr)
			exitWithErrorPage('SQL error #'.$sqlerr);
		/*if(!$id || KU_DBTYPE == 'sqlite') {
			// Non-mysql installs don't return the insert ID after insertion, we need to manually get it.
			$id = $tc_db->GetOne("SELECT `id`
				FROM `".KU_DBPREFIX."posts`
				WHERE `boardid` = ".$tc_db->qstr($boardid)."
				AND timestamp = ".$tc_db->qstr($timestamp)."
				AND `ipmd5` = '".md5($ip)."'
				LIMIT 1");
		}*/
		/*if ($id == 1 && $this->board['start'] > 1) {
			$id = $this->board['start'];
			$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
				SET `id` = '".$id."'
				WHERE `boardid` = ".$boardid);
		}*/

		// Hash the delpass with id as a salt
		if (I0_DELPASS_SALTING && $password != '') {
			$passwordmd5salted = '+'.md5($password . $new_id . $boardid . KU_RANDOMSEED);
			$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `password`=? WHERE `boardid`=? AND `id`=?", array($passwordmd5salted, $boardid, $new_id));
		}

		// Insert files
		if ($attachments) {
			foreach($attachments as $attachment) {
				$is_embed = ($attachment['attachmenttype'] == 'embed');
				$fields = array(
					//post ID
					$new_id,
					//board ID
					$boardid,
					//file
					($is_embed ? $attachment['embed'] : $attachment['file_name']),
					//file_original
					$attachment['file_original'],
					//file_type
					$attachment['filetype_withoutdot'],
					//file_md5
					$attachment['file_md5'],
					//image_w
					intval($attachment['imgWidth']),
					//image_h
					intval($attachment['imgHeight']),
					//file_size
					($is_embed ? $attachment['start'] : $attachment['file_size']),
					//file_size_formatted
					(($is_embed || (isset($attachment['is_duplicate']) && $attachment['is_duplicate'])) ? $attachment['file_size_formatted'] : ConvertBytes($attachment['size'])),
					//thumb_w
					intval($attachment['imgWidth_thumb']),
					//thumb_h
					intval($attachment['imgHeight_thumb']),
					//spoiler
					$attachment['spoiler'] ? '1' : '0'
				);
				foreach($fields as &$field) {
					$field = $tc_db->qstr($field);
				}
				$row_inserts []= '('. implode(', ', $fields) . ')';
			}
			$fquery = "INSERT INTO `".KU_DBPREFIX."files`
			(`post_id`, `boardid`, `file` , `file_original`, `file_type` , `file_md5` , `image_w` , `image_h` , `file_size` , `file_size_formatted` , `thumb_w` , `thumb_h`, `spoiler`)
			VALUES " . implode(',', $row_inserts);
			$tc_db->Execute($fquery);
			$sqlerr = $tc_db->ErrorNo();
			if ($sqlerr)
				exitWithErrorPage('SQL error #'.$sqlerr);
		}
		
		return $new_id;
	}

	function Report() {
		global $tc_db;

		return $tc_db->Execute("INSERT INTO `".KU_DBPREFIX."reports` ( `board` , `postid` , `when` , `ip`, `reason` ) VALUES ( " . $tc_db->qstr($this->board['name']) . " , " . $tc_db->qstr($this->post['id']) . " , ".time()." , '" . md5_encrypt($_SERVER['REMOTE_ADDR'], KU_RANDOMSEED) . "', " . $tc_db->qstr($_POST['reportreason']) . " )");
	}

	function CancelTimer() {
		global $tc_db;
		$boardid = $tc_db->qstr($this->board['id']);
		$postid = $tc_db->qstr($this->post['id']);

		$times = $tc_db->GetAll("SELECT `timestamp`, `deleted_timestamp`, `IS_DELETED`
			FROM `".KU_DBPREFIX."posts`
			WHERE 
			 `boardid` = ".$boardid." AND 
			 `id` = ".$postid);
		if (!$times) return false;
		if ($times[0]['IS_DELETED'] == 1) {
			return 'Post is already deleted.';
		}
		if ($times[0]['deleted_timestamp'] == 0) {
			return 'Post has no timer.';
		}
		$result = $tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
			SET
			 `deleted_timestamp`=0
			WHERE
			 `boardid` = ".$boardid." AND 
			 `id` = ".$postid);
		return $result==false ? false : true;
	}
}

?>
