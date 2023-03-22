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
 * @param boolean $crop_yts Crop a 16×9 rectangle from the source of the image (for YouTube Shorts)
 * @return boolean Success/fail
 */
function createThumbnail($name, $filename, $new_w, $new_h, $crop_yts=false) {
	if (KU_THUMBMETHOD == 'imagemagick') {
		$convert = 'magick ' . escapeshellarg($name);
		if (!KU_ANIMATEDTHUMBS) {
			$convert .= '[0] ';
		}
    if ($crop_yts) {
      $convert .= '-crop "%[fx:2*floor(h*(9/16)/2)]x%[fx:h]+%[fx:(w-2*floor(h*(9/16)/2))/2]+0" ';
    }
		$convert .= '-resize ' . $new_w . 'x' . $new_h . ' -quality ';
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
    if ($crop_yts) {
      $convert .= ' -vf crop="2*floor(ih*(9/16)/2):ih"';
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
			$src_img=@imagecreatefromjpeg($name);
		} else if (preg_match("/png/", $system[0])) {
			$src_img=@imagecreatefrompng($name);
		} else if (preg_match("/gif/", $system[0])) {
			$src_img=@imagecreatefromgif($name);
		} else {
			return false;
		}

    $actual_type = null;
		if (!$src_img) {
    	// Check for the case if the image is actually wrong type
      list($src_img, $actual_type)=imagecreatefromwhatever($name);
    	if (!$src_img) 
				exitWithErrorPage(_gettext('Unable to read uploaded file during thumbnailing.'), _gettext('A common cause for this is an incorrect extension when the file is actually of a different type.'));
		}
		$src_w = imageSX($src_img);
		$src_h = imageSY($src_img);
    if ($crop_yts) {
      $crop_w = 2*floor(($src_h*(9/16))/2);
      $src_x = round(($src_w-$crop_w)/2);
      $src_w = $crop_w;
    }
    else {
      $src_x = 0;
    }
		if ($src_w > $src_h) {
			$percent = $new_w / $src_w;
		} else {
			$percent = $new_h / $src_h;
		}
		$thumb_w = round($src_w * $percent);
		$thumb_h = round($src_h * $percent);

		$dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
		fastImageCopyResampled($dst_img, $src_img, 0, 0, $src_x, 0, $thumb_w, $thumb_h, $src_w, $src_h, $system);

		if ($actual_type) {
			if (($actual_type == 'jpg' || $actual_type == 'webp') && !imagejpeg($dst_img, $filename, 70)) {
				exitWithErrorPage('unable to imagejpg.');
				return false;
			} else if ($actual_type == 'png' && !imagepng($dst_img,$filename,0,PNG_ALL_FILTERS)) {
				exitWithErrorPage('unable to imagepng.');
				return false;
			} else if ($actual_type == 'gif' && !imagegif($dst_img, $filename)) {
				exitWithErrorPage('unable to imagegif.');
				return false;
			}
		}	else if (preg_match("/png/", $system[0])) {
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

// Sometimes the image is of wrong type, particularly thumbnails from Youtube
function imagecreatefromwhatever($fname) {
	if ($img = @imagecreatefromjpeg($fname)) {
		$type = 'jpg';
		return array($img, $type);
	}
	if ($img = @imagecreatefromwebp($fname)) {
		$type = 'webp';
		return array($img, $type);
	}
	if ($img = @imagecreatefrompng($fname)) {
		$type = 'png';
		return array($img, $type);
	}
	if ($img = @imagecreatefromgif($fname)) {
		$type = 'gif';
		return array($img, $type);
	}
	return array(false, false);
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
  if (!in_array($site, array('you', 'vim', 'cob', 'scl', 'yts')))
    return array('error' => 'unsupported_site');

  $use_youtube_dl = I0_YOUTUBE_DL_PATH !== '' && ($site === 'you' || $site === 'yts');

  // Pre-setup
  if ($use_youtube_dl) {
    putenv('PATH=' . I0_YOUTUBE_DL_PATH . PATH_SEPARATOR . getenv('PATH'));
  }
  else {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
    if (I0_CURL_PROXY)
      curl_setopt($ch, CURLOPT_PROXY, I0_CURL_PROXY);
  }
  
  // Fetching the data
  if ($use_youtube_dl) {
    exec('youtube-dl --dump-json https://www.youtube.com/watch?v='.$code, $json, $er);
    if (!$er) {
      $ydl_info = json_decode($json[0]);
    }
    else {
      return array('error' => _gettext('API returned invalid data.'));
    }
  }
  else {
    // Getting a URL
    switch ($site) {
      case 'cob':
        $url = "https://coub.com/api/v2/coubs/".$code.".json";
        break;
      case 'vim':
        $url = 'https://vimeo.com/api/v2/video/'.$code.'.json';
        break;
      case 'you': case 'yts':
        $url = 'https://www.googleapis.com/youtube/v3/videos?part=contentDetails%2Csnippet&id='.$code.'&key='.KU_YOUTUBE_APIKEY;
        break;
      case 'scl':
        $url = 'https://soundcloud.com/oembed?url=https%3A%2F%2Fsoundcloud.com%2F' . urlencode($code) . '&format=json';
        break;
      default:
        return array('error' => _gettext('No JSON URL specified.'));
    }
    curl_setopt($ch, CURLOPT_URL, $url);

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
  }

  // Find needed thumbnail width
  if ($use_youtube_dl) {
    $options_available = count($ydl_info->thumbnails);
    $i = 0; 
    foreach($ydl_info->thumbnails as &$thumb) {
      $i++;
      $width_corrected = ($site=='yts')
        ? 2*floor(($thumb->height)/(16/9)/2)
        : $thumb->width;
      if ($width_corrected >= $maxwidth || $options_available == $i) {
        $thumb_url = $thumb->url;
        $thumbwidth = $width_corrected;
        $thumbheight = $thumb->height;
        break;
      }
    }
  }
  else {
    switch ($site) {
      case 'cob':
        $widths_available = array(
          'micro' => 70,
          'tiny' => 112,
          'small' => 400,
          'med' => 640,
          'big' => 1280
        );
        break;
      case 'vim':
        $widths_available = array(
          'small' => 100,
          'medium' => 200,
          'large' => 640
        );
        break;
      case 'you': case 'yts':
        $widths_available = array(
          'default' => [120, 90],
          'medium' => [320, 180],
          'high' => [480, 360],
          'standard' => [640, 480],
          'maxres' => [1280, 720]
        );
        break;
      case 'scl':
        $widths_available = array(
          'default' => 250
        );
        break;
      default:
        return array('error' => _gettext('No thumbnails available.'));
    }
    $i = 0; 
    $options_available = count($widths_available);
    foreach ($widths_available as $preset => $wh) {
      $i++;
      if ($site=='you' || $site=='yts') {
        list($width, $height) = $wh;
        if ($site=='yts') {
          $width = 2*floor($height/(16/9)/2);
        }
      }
      else {
        $width = $wh;
      }
      if ($width >= $maxwidth || $options_available == $i) {
        $chosen_preset = $preset;
        $thumbwidth = $width;
        $thumbheight = @$height;
        break;
      }
    }
  }
  
  // Get thumbnail URL
  switch ($site) {
    case 'cob':
      $thumb_url = preg_replace('/%{version}/', $chosen_preset, $data['image_versions']['template']);
      break;
    case 'vim':
      $thumb_url = preg_replace('/\.webp/', '.jpg', $data[0]['thumbnail_'.$chosen_preset]);
      break;
    case 'you': case 'yts':
      if (!$use_youtube_dl) {
        $thumb_url = $data['items'][0]['snippet']['thumbnails'][$chosen_preset]['url'];
      }
      break;
    case 'scl':
      $thumb_url = $data['thumbnail_url'];
      break;
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
  if (I0_CURL_PROXY)
    curl_setopt($ch, CURLOPT_PROXY, I0_CURL_PROXY);
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
  $r = array(/*'width' => $thumbwidth*/);
  switch ($site) {
    case 'cob':
      $r['width'] = (int)$data['dimensions']['big'][0];
      $r['height'] = (int)$data['dimensions']['big'][1];
      $r['title'] = $data['title'];
      $duration = $data['duration'];
      break;
    case 'vim':
      $r['width'] = (int)$data[0]['width'];
      $r['height'] = (int)$data[0]['height'];
      $r['title'] = $data[0]['title'];
      $duration = $data[0]['duration'];
      break;
    case 'you': case 'yts':
      $r['height'] = $thumbheight;
      $r['width'] = $thumbwidth;
      $r['height'] = $thumbheight;
      if ($use_youtube_dl) {
        $r['title'] = $ydl_info->title;
        $duration = $ydl_info->duration;
      }
      else {
        $r['title'] = $data['items'][0]['snippet']['title'];
        $duration = preg_replace_callback('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', 'ISO8601_callback', $data['items'][0]['contentDetails']['duration']);
      }
      break;
    case 'scl':
      $r['width'] = 500;
      $r['height'] = 500;
      $r['title'] = $data['title'];
      preg_match('/tracks\/([0-9]+)&/m', urldecode($data['html']), $matches);
      if ($matches) {
        $r['code'] = $matches[1];
      }
      break;
    default:
      return array('error' => _gettext('API returned invalid data.'));
  }
  
  if ($r['width'] <= 0 || $r['height'] <= 0) {
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
  if (isset($deathlist) && $deathlist) {
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
}

?>
