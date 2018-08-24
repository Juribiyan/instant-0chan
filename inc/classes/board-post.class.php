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
	 * Dwoo class
	 *
	 * @var class Dwoo
	 */
	var $dwoo;
	/**
	 * Dwoo data class
	 *
	 * @var class Dwoo
	 */
	var $dwoo_data;
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
	function __construct($board, $extra = true) {
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
				// Boardlist
				$this->board['boardlist'] = $this->DisplayBoardList();

				// Get the unique posts for this board
				$this->board['uniqueposts']   = $tc_db->GetOne("SELECT COUNT(DISTINCT `ipmd5`) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id']. " AND  `IS_DELETED` = 0");
		
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
          $this->board['filetypes_allowed'] []= $filetype['filetype'];
        }
			
				if ($this->board['locale'] && $this->board['locale'] != KU_LOCALE) {
					changeLocale($this->board['locale']);
				}
			}
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
	function RegenerateAll() {
		$this->RegeneratePages();
		$this->RegenerateThreads();
	}

	/**
	 * Regenerate pages
	 */
	function RegeneratePages($startpage=-1, $direction="all") {
    global $tc_db, $CURRENTLOCALE;
    $tc_db->SetFetchMode(ADODB_FETCH_ASSOC);
    $this->InitializeDwoo();
    $do_all = ($startpage==-1 || $direction=="all");

    $ftypes = $tc_db->GetAll("SELECT `filetype` FROM `" . KU_DBPREFIX . "embeds`");
    foreach ($ftypes as $line) {
      $this->board['filetypes'][] .= $line['filetype'];
    }
    $this->dwoo_data->assign('filetypes', $this->board['filetypes']);
   
    $maxpages = $this->board['maxpages'];

    $threads = $tc_db->GetAll("SELECT *
      FROM `" . KU_DBPREFIX . "postembeds`
      WHERE `boardid` = " . $this->board['id'] . "
      AND `parentid` = 0
      AND `IS_DELETED` = 0
      ORDER BY `stickied` DESC, `bumped` DESC");
    $threads = group_embeds($threads, true);
    $total_threads = count($threads);

    $pages = array();

    // split threads into pages →
    for ($i=0; $i < $total_threads; $i++) {
      $current_page = floor($i / KU_THREADS);

      // fill thread stats → //TODO: check if it works correctly with multifile
      $threads[$i]['page'] = $current_page;
      $stats = $tc_db->GetAll("SELECT
        COUNT(DISTINCT `id`) `reply_count`,
        MAX(`timestamp`) `replied`,
        MAX(`id`) `last_reply`,
        SUM(CASE WHEN `file_md5` != '' THEN 1 ELSE 0 END) `images`
      FROM `".KU_DBPREFIX."postembeds`
      WHERE `boardid` = '". $this->board['id'] ." '
        AND `IS_DELETED` = 0
        AND `parentid` = '". $threads[$i]['id'] ."'");
      $stats = $stats[0];
      $threads[$i]['reply_count'] = $stats['reply_count'];
      $threads[$i]['replied'] = $stats['replied'];
      $threads[$i]['last_reply'] = $stats['last_reply'];
      $threads[$i]['images'] = $stats['images'];
      foreach($threads[$i]['embeds'] as $embed) {
        if ($embed != '') {
          $threads[$i]['images']++;
        }
      }
      // ← fill thread stats

      $pages[$current_page] []= $threads[$i];
    } // ← split thread into pages

    // rebuild pages needing to be rebuilt →
    $page = 0;
    $starter_page_passed = false;
    $totalpages = count($pages);
    if (!$totalpages) {
      $pages []= array();
    }
    $this->dwoo_data->assign('numpages', $totalpages-1);

    foreach ($pages as $pagethreads) {
      $is_starter_page = ($page == $startpage);
      if ($is_starter_page) {
        $starter_page_passed = true;
      }
      if ($do_all || $is_starter_page || ($direction=="down" && $starter_page_passed) || ($direction=="up" && !$starter_page_passed)) {
        // page must be rebuilt
        $executiontime_start_page = microtime_float();
        $newposts = array();
        $this->dwoo_data->assign('thispage', $page);
        foreach ($pagethreads as $thread) {
          // If the thread is on the page set to mark, && hasn't been marked yet, mark it →
          if ($thread['deleted_timestamp'] == 0 && $this->board['markpage'] > 0 && $page >= $this->board['markpage']) {
            $tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `deleted_timestamp` = '" . (time() + 7200) . "' WHERE `boardid` = " . $tc_db->qstr($this->board['id'])." AND `id` = '" . $thread['id'] . "'");
            clearPostCache($thread['id'], $this->board['name']);
            $this->RegenerateThreads($thread['id']);
            $this->dwoo_data->assign('replythread', 0);
          } // ← If the thread is on the page set to mark, && hasn't been marked yet, mark it

          // If the thread is back on safe page, unmark it →
          if ($this->board['markpage'] == 0 || $thread['deleted_timestamp'] != 0 && $page < $this->board['markpage']) {
            $tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `deleted_timestamp` = '0' WHERE `boardid` = " . $tc_db->qstr($this->board['id'])." AND `id` = '" . $thread['id'] . "'");
            $thread['deleted_timestamp'] = 0;
          } // ← If the thread is back on safe page, unmark it

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

          $this->dwoo_data->assign('debug_timestring', $timestr);

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
        $this->dwoo_data->assign('posts', $newposts);
        $this->dwoo_data->assign('file_path', getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], ''));

        $content = $this->dwoo->get(KU_TEMPLATEDIR . '/board_main_loop.tpl', $this->dwoo_data);
        $footer = $this->Footer(false, (microtime_float() - $executiontime_start_page), false);
        $content = $header.$postbox.$content.$footer;

        $content = str_replace("\t", '',$content);
        $content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

        $filename = KU_BOARDSDIR.$this->board['name'].'/'.($page==0 ? KU_FIRSTPAGE : '/'.$page.'.html');
        $this->PrintPage($filename, $content, $this->board['name']);
      }
      $page++;
    } // ← rebuild pages needing to be rebuilt

    // build catalog →
    if ($this->board['enablecatalog'] == 1) {
      $executiontime_start_catalog = microtime_float();
      $catalog_head = $this->PageHeader().
      '<script src="'.KU_BOARDSFOLDER.'lib/javascript/lodash.min.js"></script>'.
      '<script> is_catalog=true; </script>'.
      '&#91;<a href="' . KU_BOARDSFOLDER . $this->board['name'] . '/">'._gettext('Return').'</a>&#93; '.
      '&#91;<a href="#" id="refresh_catalog">'._gettext('Refresh').'</a>&#93;'.
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
        foreach ($threads as $thread) {
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
                if (in_array($embed['file_type'], array('jpg', 'png', 'gif', 'webm'))) {
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
                if ($file_found['file_type'] == 'webm')
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
    } // ← build catalog

    // Delete old pages  →
    $dir = KU_BOARDSDIR.$this->board['name'];
    $files = glob ("$dir/*.html");
    if (is_array($files)) {
      foreach ($files as $htmlfile) {
        if (preg_match("/[0-9+].html/", $htmlfile)) {
          if (substr(basename($htmlfile), 0, strpos(basename($htmlfile), '.html')) > $totalpages) {
            unlink($htmlfile);
          }
        }
      }
    } // ← Delete old pages
  }

	/**
	 * Regenerate each thread's corresponding html file, starting with the most recently bumped
	 */
	function RegenerateThreads($id = 0) {
		global $tc_db, $CURRENTLOCALE;
		require_once(KU_ROOTDIR."lib/dwoo.php");
		if (!isset($this->dwoo)) { $this->dwoo = New Dwoo(); $this->dwoo_data = new Dwoo_Data(); $this->InitializeDwoo(); }
		// $embeds = Array();
		$numimages = 0;
		$embeds = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "embeds`");
    // $this->dwoo_data->assign('embeds', $embeds); //TODO: remove
    foreach ($embeds as $embed) {
      $this->board['filetypes'][] .= $embed['filetype'];
    }
    $this->dwoo_data->assign('filetypes', $this->board['filetypes']);
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
      $this->BuildThread($id);
		}
	}

  function BuildThread($id, $header=null, $postbox=null) {
    global $tc_db, $CURRENTLOCALE;

    $numimages = 0;
    $executiontime_start_thread = microtime_float();
    $posts = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "postembeds` WHERE `boardid` = " . $this->board['id'] . " AND (`id` = " . $id . " OR `parentid` = " . $id . ") AND `IS_DELETED` = 0 ORDER BY `id` ASC");
    // There might be a chance that the post was deleted during another RegenerateThreads() session, if there are no posts, move on to the next thread.
    if (count($posts) > 0) {
      $posts = group_embeds($posts, true);
      foreach ($posts as $key=>$post) {
        foreach($post['embeds'] as $embed) {
          if (($embed['file_type'] == 'jpg' || $embed['file_type'] == 'gif' || $embed['file_type'] == 'png') && $embed['parentid'] != 0) {
            $numimages++;
          }
        }
        $posts[$key] = $this->BuildPost($post, false);
      }

      $header_replaced = $header ? str_replace("<!sm_threadid>", $id, $header) : $this->PageHeader($id);
      $this->dwoo_data->assign('numimages', $numimages);
      $this->dwoo_data->assign('isthread', true);
      $this->dwoo_data->assign('posts', array($posts)); // Wrap the posts into array to keep unified structure with board page
      $this->dwoo_data->assign('file_path', getCLBoardPath($this->board['name'], $this->board['loadbalanceurl_formatted'], ''));
     
      if (!$postbox) {
        $postbox = $this->Postbox($id);
      }
      $postbox_replaced = str_replace("<!sm_threadid>", $id, $postbox);
      $reply   = $this->dwoo->get(KU_TEMPLATEDIR . '/board_reply_header.tpl', $this->dwoo_data);
      $content = $this->dwoo->get(KU_TEMPLATEDIR . '/board_main_loop.tpl', $this->dwoo_data);
      if (!isset($footer)) $footer = $this->Footer(false, (microtime_float() - $executiontime_start_thread), false);
      $content = $header_replaced.$reply.$postbox_replaced.$content.$footer;

      $content = str_replace("\t", '',$content);
      $content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

      $this->PrintPage(KU_BOARDSDIR . $this->board['name'] . $this->archive_dir . '/res/' . $id . '.html', $content, $this->board['name']);
      /*if (KU_FIRSTLAST) {

        $replycount = (count($posts)-1);
        if ($replycount > 50) {
          $this->dwoo_data->assign('replycount', $replycount);
          $this->dwoo_data->assign('modifier', "last50");

          // Grab the last 50 replies
          $posts50 = array_slice($posts, -50, 50);

          // Add on the OP
          array_unshift($posts50, $posts[0]);
         
          $this->dwoo_data->assign('posts', $posts50);

          $content = $this->dwoo->get(KU_TEMPLATEDIR . '/img_thread.tpl', $this->dwoo_data);
          $content = $header_replaced.$reply.$postbox_replaced.$content.$footer;
          $content = str_replace("\t", '',$content);
          $content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

          unset($posts50);

          $this->PrintPage(KU_BOARDSDIR . $this->board['name'] . $this->archive_dir . '/res/' . $id . '+50.html', $content, $this->board['name']);
          if ($replycount > 100) {
            $this->dwoo_data->assign('modifier', "first100");

            // Grab the first 100 posts
            $posts100 = array_slice($posts, 0, 100);

            $this->dwoo_data->assign('posts', $posts100);

            $content = $this->dwoo->get(KU_TEMPLATEDIR . '/img_thread.tpl', $this->dwoo_data);
            $content = $header_replaced.$reply.$postbox_replaced.$content.$footer;
            $content = str_replace("\t", '',$content);
            $content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

            unset($posts100);
           
            $this->PrintPage(KU_BOARDSDIR . $this->board['name'] . $this->archive_dir . '/res/' . $id . '-100.html', $content, $this->board['name']);
          }
          $this->dwoo_data->assign('modifier', "");
        } //TODO: add support for firstlast
      }*/
    }
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
        !in_array($embed['file_type'], $this->board['filetypes'])
      ) {
        if(!isset($filetype_info[$embed['file_type']]))
          $filetype_info[$embed['file_type']] = getfiletypeinfo($embed['file_type']);
        $embed['nonstandard_file'] = KU_WEBPATH . '/inc/filetypes/' . $filetype_info[$embed['file_type']][0];
        if($embed['thumb_w']!=0&&$embed['thumb_h']!=0) {
          if(file_exists(KU_BOARDSDIR.$this->board['name'].'/thumb/'.$embed['file'].'s.jpg'))
            $embed['nonstandard_file'] = KU_WEBPATH . '/' .$this->board['name'].'/thumb/'.$embed['file'].'s.jpg';
          elseif(file_exists(KU_BOARDSDIR.$this->board['name'].'/thumb/'.$embed['file'].'s.png'))
            $embed['nonstandard_file'] = KU_WEBPATH . '/' .$this->board['name'].'/thumb/'.$embed['file'].'s.png';
          elseif(file_exists(KU_BOARDSDIR.$this->board['name'].'/thumb/'.$embed['file'].'s.gif'))
            $embed['nonstandard_file'] = KU_WEBPATH . '/' .$this->board['name'].'/thumb/'.$embed['file'].'s.gif';
          else {
            $embed['generic_icon'] = true;
            $embed['thumb_w'] = $filetype_info[$embed['file_type']][1];
            $embed['thumb_h'] = $filetype_info[$embed['file_type']][2];
          }
        }
        else {
          $embed['generic_icon'] = true;
          $embed['thumb_w'] = $filetype_info[$embed['file_type']][1];
          $embed['thumb_h'] = $filetype_info[$embed['file_type']][2];
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
	function PageHeader($replythread = '0', $liststart = '0', $liststooutput = '-1') {
		global $tc_db, $CURRENTLOCALE;

		$tpl = Array();

		$tpl['htmloptions'] = ((KU_LOCALE == 'he' && empty($this->board['locale'])) || $this->board['locale'] == 'he') ? ' dir="rtl"' : '' ;

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
		$this->dwoo_data->assign('title', $tpl['title']);
		$this->dwoo_data->assign('htmloptions', $tpl['htmloptions']);
		$this->dwoo_data->assign('locale', $CURRENTLOCALE);
		$this->dwoo_data->assign('ad_top', $ad_top);
		$this->dwoo_data->assign('ad_right', $ad_right);
		$this->dwoo_data->assign('board', $this->board);
		$this->dwoo_data->assign('replythread', $replythread);
		$topads = $tc_db->GetOne("SELECT code FROM `" . KU_DBPREFIX . "ads` WHERE `position` = 'top' AND `disp` = '1'");
    $this->dwoo_data->assign('topads', $topads);
    // #snivystuff include alien style
    $styles =  explode(':', KU_STYLES);
    $defaultstyle = $this->board['defaultstyle'];
    if(!empty($defaultstyle)) {
      if(!in_array($defaultstyle, $styles)) {
        $custom_style_version = $tc_db->GetOne("SELECT `version` FROM `customstyles` WHERE `name` = '".$defaultstyle."'");
        if(count($custom_style_version) > 0) {
          $styles[]= $defaultstyle;
          $this->dwoo_data->assign('customstyle', $defaultstyle);
          $this->dwoo_data->assign('csver', $custom_style_version);
        }
      }
      else { $this->dwoo_data->assign('customstyle', false); }
    }
    else $defaultstyle = KU_DEFAULTSTYLE;
    $this->dwoo_data->assign('ku_styles', $styles);
    $this->dwoo_data->assign('ku_defaultstyle', $defaultstyle);
		$this->dwoo_data->assign('boardlist', $this->board['boardlist']);
    $this->PrebuildBoardlist();
    $this->dwoo_data->assign('boardlist_prebuilt', $this->board['boardlist_prebuilt']);

		$global_header = $this->dwoo->get(KU_TEMPLATEDIR . '/global_board_header.tpl', $this->dwoo_data);

		$header = $this->dwoo->get(KU_TEMPLATEDIR . '/board_header.tpl', $this->dwoo_data);

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
			$this->dwoo_data->assign('blotter', getBlotter());
			$this->dwoo_data->assign('blotter_updated', getBlotterLastUpdated());
		}
		$postbox = '';
		$formbody .= $this->dwoo->get(KU_TEMPLATEDIR . '/board_post_box.tpl', $this->dwoo_data);
    $postbox = $this->dwoo->get(KU_TEMPLATEDIR . '/board_post_box_wrapper.tpl', $this->dwoo_data);
    $postbox = str_replace("<!-- formbody -->", $formbody, $postbox);
		return $postbox;
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
				$results2 = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "boards` WHERE `section` = '" . $line['id'] . "' ORDER BY `order` ASC, `name` ASC");
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
      $this->board['boardlist_prebuilt'] = $this->dwoo->get(KU_TEMPLATEDIR . '/boardlist.tpl', $this->dwoo_data);
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
		global $tc_db, $dwoo, $dwoo_data;

		$footer = '';

		if ($hide_extra || $noboardlist) $this->dwoo_data->assign('boardlist', '');

		if ($executiontime != '') $this->dwoo_data->assign('executiontime', round($executiontime, 2));
	
		$botads = $tc_db->GetOne("SELECT code FROM `" . KU_DBPREFIX . "ads` WHERE `position` = 'bot' AND `disp` = '1'");
		$this->dwoo_data->assign('botads', $botads);
		$footer = $this->dwoo->get(KU_TEMPLATEDIR . '/board_footer.tpl', $this->dwoo_data);
	
		$footer .= $this->dwoo->get(KU_TEMPLATEDIR . '/global_board_footer.tpl', $this->dwoo_data);

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
		} else {
			echo $contents;
		}
	}

	/**
	 * Initialize the instance of smary which will be used for generating pages
	 */
	function InitializeDwoo() {

		require_once KU_ROOTDIR . 'lib/dwoo.php';
		$this->dwoo = new Dwoo();
		$this->dwoo_data = new Dwoo_Data();

		$this->dwoo_data->assign('cwebpath', getCWebpath());
		$this->dwoo_data->assign('boardpath', getCLBoardPath());
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

  function DeleteFile($file_id, $pass, $ismod, $boardname) {
    global $tc_db;
    if (! is_numeric($file_id))
      return array('error' => _gettext('Invalid file id'));
    $postfile = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."postembeds` WHERE `file_id`=".$tc_db->qstr($file_id));
    if (!$postfile)
      return array('error' => _gettext('File does not exist.'));
    $postfile = $postfile[0];
    if ($postfile['file'] == 'removed')
      return array(
        'error' => false,
        'already_deleted' => true
      );
    if (!$ismod && $postfile['password'] != $pass) {
      return array('error' => _gettext('Incorrect password.'));
    }
    clearPostCache($postfile['id'], $this->board['name']);
    $tc_db->Execute("UPDATE `".KU_DBPREFIX."files` SET `file`='removed' WHERE `file_id`=".$tc_db->qstr($file_id));
    $this->EraseFileAndThumbs($postfile);
    if ($ismod) {
      $parentid = $postfile['parentid']=='0' ? $postfile['id'] : $postfile['parentid'];
      management_addlogentry(_gettext('Deleted file') .
        ' "' . $postfile['file_original'] . '.' . $postfile['file_type'] .'" ' .
        _gettext('from') . ' #<a href="/'.$boardname.'/res/'.$parentid.'.html#'. $postfile['id'] . '">'. $postfile['id'] . '</a> - /'. $boardname . '/', 7);
    }
    return array(
      'error' => false,
      'parentid' => $postfile['parentid']==0 ? $postfile['id'] : $postfile['parentid']
    );
  }
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

	function Delete($allow_archive = false) {
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
          `file`='removed'
         WHERE
          `boardid` = '" . $boardid . "'
          AND
          `file_id` IN (".implode($file_ids, ',').")");
      // Mark posts as deleted
      $tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
       SET
        `IS_DELETED` = 1 ,
        `deleted_timestamp` = '" . time() . "'
       WHERE
        `boardid` = '" . $boardid . "'
        AND
        `id` IN (".implode($post_ids, ',').")");
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
        `id` IN (".implode($post_ids, ',').")");

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
        `deleted_timestamp` = '" . time() . "'
       WHERE
        `boardid` = '" . $boardid . "'
        AND
        `id` = ".$tc_db->qstr($postid));
      if ($this->post['embeds']) {
        // Mark files as removed in db
        $tc_db->Execute("UPDATE `".KU_DBPREFIX."files`
         SET
          `file`='removed'
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
      $tc_db->Execute('UPDATE
       `'.KU_DBPREFIX.'posts` AS t1,
       (SELECT
         `timestamp`
        FROM
         `'.KU_DBPREFIX.'posts`
        WHERE
         (`id`=?
          OR (
           `parentid`=?
           AND
           `email`!="sage"
          )
         )
         AND
         `IS_DELETED`="0"
         AND
         `boardid`=? 
        ORDER BY TIMESTAMP DESC
        LIMIT 1
        ) AS t2
       SET
        t1.`bumped` = t2.`timestamp`
       WHERE
        t1.`id`=?
        AND
        `boardid`=?', array(
        $this->post['parentid'],
        $this->post['parentid'],
        $boardid,
        $this->post['parentid'],
        $boardid));

      clearPostCache($postid, $boardname);

      return 1;
    }
  }

	function Insert($parentid, $name, $tripcode, $email, $subject, $message, $attachments, $password, $timestamp, $bumped, $ip, $posterauthority, $stickied, $locked, $boardid, $country, $is_new_user, $deleted_timestamp) {
		global $tc_db;
    $post_fields = array(
      $parentid,
      $boardid,
      $name,
      $tripcode,
      $mail,
      $subject,
      $message,
      $password,
      $timestamp,
      $bumped,
      md5_encrypt($ip, KU_RANDOMSEED),
      md5($ip),
      $posterauthority,
      $stickied,
      $locked,
      $country,
      $is_new_user,
      $deleted_timestamp
    );
    foreach($post_fields as &$pf) {
      $pf = $tc_db->qstr($pf);
    }
		$query = "INSERT INTO `".KU_DBPREFIX."posts` (
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
		$id = $tc_db->Insert_Id();
    $sqlerr = $tc_db->ErrorNo();
    if ($sqlerr)
      exitWithErrorPage('SQL error #'.$sqlerr);
		if(!$id || KU_DBTYPE == 'sqlite') {
			// Non-mysql installs don't return the insert ID after insertion, we need to manually get it.
			$id = $tc_db->GetOne("SELECT `id`
				FROM `".KU_DBPREFIX."posts`
				WHERE `boardid` = ".$tc_db->qstr($boardid)."
				AND timestamp = ".$tc_db->qstr($timestamp)."
				AND `ipmd5` = '".md5($ip)."'
				LIMIT 1");
		}
    if ($id == 1 && $this->board['start'] > 1) {
      $id = $this->board['start'];
      $tc_db->Execute("UPDATE `".KU_DBPREFIX."posts`
        SET `id` = '".$id."'
        WHERE `boardid` = ".$boardid);
    }

    // Insert files
    if (!$attachments) return $id;
    foreach($attachments as $attachment) {
      $is_embed = ($attachment['attachmenttype'] == 'embed');
      $fields = array(
        //post ID
        $id,
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
        ($is_embed ? null : $attachment['file_size']),
        //file_size_formatted
        (($is_embed || $attachment['is_duplicate']) ? $attachment['file_size_formatted'] : ConvertBytes($attachment['size'])),
        //thumb_w
        intval($attachment['imgWidth_thumb']),
        //thumb_h
        intval($attachment['imgHeight_thumb'])
      );
      foreach($fields as &$field) {
        $field = $tc_db->qstr($field);
      }
      $row_inserts []= '('. implode(', ', $fields) . ')';
    }
    $fquery = "INSERT INTO `".KU_DBPREFIX."files`
    (`post_id`, `boardid`, `file` , `file_original`, `file_type` , `file_md5` , `image_w` , `image_h` , `file_size` , `file_size_formatted` , `thumb_w` , `thumb_h`)
    VALUES " . implode(',', $row_inserts);
    $tc_db->Execute($fquery);
    $sqlerr = $tc_db->ErrorNo();
    if ($sqlerr)
      exitWithErrorPage('SQL error #'.$sqlerr);
		return $id;
	}

	function Report() {
		global $tc_db;

		return $tc_db->Execute("INSERT INTO `".KU_DBPREFIX."reports` ( `board` , `postid` , `when` , `ip`, `reason` ) VALUES ( " . $tc_db->qstr($this->board['name']) . " , " . $tc_db->qstr($this->post['id']) . " , ".time()." , '" . md5_encrypt($_SERVER['REMOTE_ADDR'], KU_RANDOMSEED) . "', " . $tc_db->qstr($_POST['reportreason']) . " )");
	}
}

?>
