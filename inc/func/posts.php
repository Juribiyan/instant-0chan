<?php

/**
 * Check if the supplied md5 file hash is currently recorded inside of the database, attached to a non-deleted post
 */
function checkMd5($md5, $board, $boardid) {
	global $tc_db;
	$matches = $tc_db->GetAll("SELECT `id`, `parentid`, `file`, `file_size`, `file_size_formatted`, `image_w`, `image_h`, `thumb_w`, `thumb_h`, `file_original`
		FROM `".KU_DBPREFIX."postembeds` 
		WHERE `boardid` = " . $boardid . " 
		AND `IS_DELETED` = 0
		AND `file` != 'removed' 
		AND `file_md5` = ".$tc_db->qstr($md5)." LIMIT 1");
	if (count($matches) > 0) {
		$r = $matches[0];
		if ($r['parentid'] == 0)
			$r['parentid'] = $r['id'];
		$real_parentid = ($matches[0]['parentid'] == 0) ? $matches[0]['id'] : $matches[0]['parentid'];
		return $r;
	}

	return false;
}

/* Image handling */
/**
 * Create a thumbnail
 *
 * @param string $name File to be thumbnailed
 * @param string $filename Path to place the thumbnail
 * @param integer $new_w Maximum width
 * @param integer $new_h Maximum height
 * @return boolean Success/fail
 */
function createThumbnail($name, $filename, $new_w, $new_h) {
	if (KU_THUMBMETHOD == 'imagemagick') {
		$convert = 'convert ' . escapeshellarg($name);
		if (!KU_ANIMATEDTHUMBS) {
			$convert .= '[0] ';
		}
		$convert .= ' -resize ' . $new_w . 'x' . $new_h . ' -quality ';
		if (substr(strrchr($filename,'.'),1) != 'gif') {
			$convert .= '70';
		} else {
			$convert .= '90';
		}
		$convert .= ' ' . escapeshellarg($filename);
		exec($convert);

    if (KU_USEOPTIPNG) {
      if (substr(strrchr($filename,'.'),1) == 'png') {
        $opti =  'optipng -o' . escapeshellarg(KU_OPTIPNGLV);
        $opti .= ' ' . escapeshellarg($filename);
        exec($opti);
      }
    }

		if (is_file($filename)) {
			return true;
		} else {
			return false;
		}
	}
  elseif (KU_THUMBMETHOD == 'ffmpeg') {
    $imagewidth = exec('ffprobe -v quiet -show_entries stream=width -of default=noprint_wrappers=1:nokey=1 '. escapeshellarg($name));
    $imageheight = exec('ffprobe -v quiet -show_entries stream=height -of default=noprint_wrappers=1:nokey=1 '. escapeshellarg($name));
    $convert = 'ffmpeg -i ' . escapeshellarg($name);
    if (!KU_ANIMATEDTHUMBS) {
      $convert .= ' -vframes 1';
    }
    if ($imagewidth > $imageheight) {
      $convert .= ' -vf scale="' . $new_w . ':-1" -quality ';
    } else {
      $convert .= ' -vf scale="-1:' . $new_h . '" -quality ';
    } 
    if (substr(strrchr($filename,'.'),1) != 'gif') {
      $convert .= '70';
    } else {
      $convert .= '90';
    }
    $convert .= ' ' . escapeshellarg($filename);
    exec($convert);

    if (is_file($filename)) {
      return true;
    } else {
      return false;
    }
  }
	elseif (KU_THUMBMETHOD == 'gd') {
		$system=explode(".", $filename);
		$system = array_reverse($system);
		if (preg_match("/jpg|jpeg/", $system[0])) {
			$src_img=imagecreatefromjpeg($name);
		} else if (preg_match("/png/", $system[0])) {
			$src_img=imagecreatefrompng($name);
		} else if (preg_match("/gif/", $system[0])) {
			$src_img=imagecreatefromgif($name);
		} else {
			return false;
		}

		if (!$src_img) {
			exitWithErrorPage(_gettext('Unable to read uploaded file during thumbnailing.'), _gettext('A common cause for this is an incorrect extension when the file is actually of a different type.'));
		}
		$old_x = imageSX($src_img);
		$old_y = imageSY($src_img);
		if ($old_x > $old_y) {
			$percent = $new_w / $old_x;
		} else {
			$percent = $new_h / $old_y;
		}
		$thumb_w = round($old_x * $percent);
		$thumb_h = round($old_y * $percent);

		$dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
		fastImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y, $system);

		if (preg_match("/png/", $system[0])) {
			if (!imagepng($dst_img,$filename,0,PNG_ALL_FILTERS) ) {
				exitWithErrorPage('unable to imagepng.');
				return false;
			}
		} else if (preg_match("/jpg|jpeg/", $system[0])) {
			if (!imagejpeg($dst_img, $filename, 70)) {
				exitWithErrorPage('unable to imagejpg.');
				return false;
			}
		} else if (preg_match("/gif/", $system[0])) {
			if (!imagegif($dst_img, $filename)) {
				exitWithErrorPage('unable to imagegif.');
				return false;
			}
		}

		imagedestroy($dst_img);
		imagedestroy($src_img);

		return true;
	}

	return false;
}

/* Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable. */
/**
 * Faster method than only calling imagecopyresampled()
 *
 * @return boolean Success/fail
 */
function fastImageCopyResampled(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $system, $quality = 3) {
	/*
	Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5.
	1 = Up to 600 times faster. Poor results, just uses imagecopyresized but removes black edges.
	2 = Up to 95 times faster. Images may appear too sharp, some people may prefer it.
	3 = Up to 60 times faster. Will give high quality smooth results very close to imagecopyresampled.
	4 = Up to 25 times faster. Almost identical to imagecopyresampled for most images.
	5 = No speedup. Just uses imagecopyresampled, highest quality but no advantage over imagecopyresampled.
	*/

	if (empty($src_image) || empty($dst_image) || $quality <= 0) { return false; }

	if (preg_match("/png/", $system[0]) || preg_match("/gif/", $system[0])) {
		$colorcount = imagecolorstotal($src_image);
		if ($colorcount <= 256 && $colorcount != 0) {
			imagetruecolortopalette($dst_image,true,$colorcount);
			imagepalettecopy($dst_image,$src_image);
			$transparentcolor = imagecolortransparent($src_image);
			imagefill($dst_image,0,0,$transparentcolor);
			imagecolortransparent($dst_image,$transparentcolor);
		}
		else {
			imageAlphaBlending($dst_image, false);
			imageSaveAlpha($dst_image, true); //If the image has Alpha blending, lets save it
		}
	}

	if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		$temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
		if (preg_match("/png/", $system[0])) {
			$background = imagecolorallocate($temp, 0, 0, 0);
			ImageColorTransparent($temp, $background); // make the new temp image all transparent
			imagealphablending($temp, false); // turn off the alpha blending to keep the alpha channel
		}
		imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
		imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
		imagedestroy ($temp);
	}

	else imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

	return true;
}

/**
 * Fetch information about the video from hosting's API
 *
 * @param string $site Website name (you=Youtube, vim=Vimeo, cob=Coub, scl=Soundcloud)
 * @param string $code Video code
 * @param integer $maxwidth Maximum thumbnail width in pixels
 * @return array(
	   	string $file Temp file URL
	   	string $title Video title
	   	string $duration Video duration ([hh:]mm:ss)
	   	integer $width Thumbnail width in pixels
	   	resource $tmpfile Temp file descriptor
 	 )
 */
function fetch_video_data($site, $code, $maxwidth, $thumb_tmpfile) {
  if (!in_array($site, array('you', 'vim', 'cob', 'scl')))
    return array('error' => 'unsupported_site');

  // Pre-setup
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
  if (I0_CURL_PROXY)
    curl_setopt($ch, CURLOPT_PROXY, I0_CURL_PROXY);

  // Getting a URL
  if ($site == 'cob')
    $url = "http://coub.com/api/v2/coubs/".$code.".json";
  if ($site == 'vim')
    $url = 'http://vimeo.com/api/v2/video/'.$code.'.json';
  if ($site == 'you')
    $url = 'https://www.googleapis.com/youtube/v3/videos?part=contentDetails%2Csnippet&id='.$code.'&key='.KU_YOUTUBE_APIKEY;
  if ($site == 'scl')
    $url = 'https://soundcloud.com/oembed?url=https%3A%2F%2F' . urlencode($code) . '&format=json';
  curl_setopt($ch, CURLOPT_URL,$url);

  // Fetching data
  $result = curl_exec($ch);
  switch (curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
    case 404: return array('error' => _gettext('Unable to connect to')); break;
    case 303: return array('error' => _gettext('Invalid video ID.')); break;
    case 302: break;
    case 301: break;
    case 200: break;
    default:  return array('error' => _gettext('Invalid response code ').' (JSON)'); break;
  }
  curl_close($ch);
  $data = json_decode($result, true);
  if ($data == NULL)
    return array('error' => _gettext('API returned invalid data.'));

  // Find needed thumbnail width
  switch ($site) {
    case 'cob':
       widths_available = array(
          'micro' => 70,
          'tiny' => 112,
          'small' => 400,
          'med' => 640,
          'big' => 1280
      );
    case 'vim':
      $widths_available = array(
          'small' => 100,
          'medium' => 200,
          'large' => 640
      );
    case 'you':
      $widths_available = array(
          'default' => 120,
          'medium' => 320,
          'high' => 480,
          'standard' => 640,
          'maxres' => 1280
      );
    case 'scl':
      $widths_available = array(
          'default' => 500
      );
  }
  $i = 0; 
  $options_available = count($widths_available);
  foreach ($widths_available as $preset => $width) {
    $i++;
    if ($width >= $maxwidth || $options_available == $i) {
      $chosen_preset = $preset;
      $thumbwidth = $width;
      break;
    }
  }

  // Get thumbnail URL
  switch ($site) {
    case 'cob':
      $thumb_url = preg_replace('/%{version}/', $chosen_preset, $data['image_versions']['template']);
    case 'vim':
      $thumb_url = preg_replace('/\.webp/', '.jpg', $data[0]['thumbnail_'.$chosen_preset]);
    case 'you':
      $thumb_url = $data['items'][0]['snippet']['thumbnails'][$chosen_preset]['url'];
    case 'scl':
      $thumb_url = $data['thumbnail_url'];
    default:
      return array('error' => _gettext('No thumb URL specified.'));
  }
  if (!$thumb_url)
    return array('error' => _gettext('API returned invalid data.'));

  // Download thumbnail to temporary directory
  $ch = curl_init($thumb_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_FILE, $thumb_tmpfile);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  switch (curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
    case 404: return array('error' => _gettext('Unable to retrieve thumbnail')); break;
    case 303: return array('error' => _gettext('Unable to retrieve thumbnail')); break;
    case 302: break;
    case 301: break;
    case 200: break;
    default:  return array('error' => _gettext('Invalid response code ').' (Thumb)'); break;
  }
  curl_close($ch);

  // Get the rest of the data
  $r = array('width' => $thumbwidth);
  switch ($site) {
    case 'cob':
      $r['width'] = (int)$data['dimensions']['big'][0];
      $r['height'] = (int)$data['dimensions']['big'][1];
      $r['title'] = $data['title'];
      $duration = $data['duration'];
    case 'vim':
      $r['width'] = (int)$data[0]['width'];
      $r['height'] = (int)$data[0]['height'];
      $r['title'] = $data[0]['title'];
      $duration = $data[0]['duration'];
    case 'you':
      $r['width'] = 1920;
      $r['height'] = 1080;
      $r['title'] = $data['items'][0]['snippet']['title'];
      $duration = preg_replace_callback('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', 'ISO8601_callback', $data['items'][0]['contentDetails']['duration']);
    case 'scl':
      $r['width'] = 500;
      $r['height'] = 500;
      $r['title'] = $data['title'];
    default:
      return array('error' => _gettext('API returned invalid data.'));
  }
  if ($r['width'] <= 0 || $r['height'] <= 0) {
    // var_dump($r);
  	return array('error' => _gettext('API returned invalid data.'));
  }
  // Convert duration into readable string
  $r['duration'] = isset($duration) ? preg_replace('/^00:/m', '', gmdate("H:i:s", round($duration, 0))) : 0;
  $r['error'] = false;
  return $r;
}

/**
 * Trim the threads to the page limit and delete posts which are older than limited
 */
function TrimToPageLimit($board) {
	global $tc_db;

	if ($board['maxage'] != 0) {
		// If the maximum thread age setting is not zero (do not delete old threads), find posts which are older than the limit, and delete them
		$results = $tc_db->GetAll("SELECT `id`, `timestamp` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $board['id'] . " AND `IS_DELETED` = 0 AND `parentid` = 0 AND `stickied` = 0 AND ((`timestamp` + " . ($board['maxage']*3600) . ") < " . time() . ")");
		foreach($results AS $line) {
			// If it is older than the limit
			$post_class = new Post($line['id'], $board['name'], $board['id']);
			$post_class->Delete(true);
		}
	}
	if ($board['maxpages'] != 0) {
		// If the maximum pages setting is not zero (do not limit pages), find posts which are over the limit, and delete them
		$results = $tc_db->GetAll("SELECT `id`, `stickied` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $board['id'] . " AND `IS_DELETED` = 0 AND `parentid` = 0");
		$results_count = count($results);
		if (calculatenumpages($results_count) >= $board['maxpages']) {
			$board['maxthreads'] = ($board['maxpages'] * KU_THREADS);
			$numthreadsover = ($results_count - $board['maxthreads']);
			if ($numthreadsover > 0) {
				$resultspost = $tc_db->GetAll("SELECT `id`, `stickied` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $board['id'] . " AND `IS_DELETED` = 0 AND `parentid` = 0 AND `stickied` = 0 ORDER BY `bumped` ASC LIMIT " . $numthreadsover);
				foreach($resultspost AS $linepost) {
					$post_class = new Post($linepost['id'], $board['name'], $board['id']);
					$post_class->Delete(true);
				}
			}
		}
	}
	// If the thread was marked for deletion more than two hours ago, delete it
	$results = $tc_db->GetAll("SELECT `id` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $board['id'] . " AND `IS_DELETED` = 0 AND `parentid` = 0 AND `stickied` = 0 AND `deleted_timestamp` > 0 AND (`deleted_timestamp` <= " . time() . ")");
	foreach($results AS $line) {
		// If it is older than the limit
		$post_class = new Post($line['id'], $board['name'], $board['id']);
		$post_class->Delete(true);
	}
}

// Delete posts whose time is up
function collect_dead() {
  global $tc_db;
  
  $deathlist = $tc_db->GetAll("SELECT `id`, `".KU_DBPREFIX."boardid`, `parentid` 
    FROM `posts`
    WHERE 
      `IS_DELETED`='0' 
      AND
      `deleted_timestamp`>'0'
      AND 
      `deleted_timestamp`<=UNIX_TIMESTAMP(NOW())");
  $dl_struct = array();
  foreach ($deathlist as $post) {
    if (! $dl_struct[$post['boardid']]) {
      $dl_struct[$post['boardid']] = array();
    }
    $thread_id = $post['parentid'] == 0 ? $post['id'] : $post['parentid'];
    if (! $dl_struct[$post['boardid']][$thread_id]) {
      $dl_struct[$post['boardid']][$thread_id] = array();
    }
    $dl_struct[$post['boardid']][$thread_id] []= $post['id'];
  }
  foreach ($dl_struct as $boardid => $threads) {
    $board_name = $tc_db->GetOne("SELECT `name` FROM `".KU_DBPREFIX."boards` WHERE `id`='$boardid'");
    $board_class = new Board($board_name);
    $board_rebuild = false;
    foreach ($threads as $thread_id => $posts) {
      $thread_rebuild = false;
      foreach($posts as $post_id) {
        $post_class = new Post($post_id, $board_name, $boardid);
        $delres = $post_class->Delete(false, I0_ERASE_DELETED);
        if ($delres && $delres !== 'already_deleted') {
          $thread_rebuild = true;
          if ($post_id == $thread_id) {
            $thread_deleted = true;
          }
        }
      }
      if ($thread_rebuild) {
        $board_rebuild = true;
        if (! $thread_deleted) {
          $board_class->RegenerateThreads($thread_id);
        }
      }
    }
    if ($board_rebuild) {
      $board_class->RegeneratePages();
    }
  }
}

?>
