<?php
function getBlotter($all = false) {
	global $tc_db, $yac;

	if (I0_YAC) {
		if ($all) {
			$cache_blotter = $yac->get('blotter|all');
		} else {
			$cache_blotter = $yac->get('blotter|last4');
		}
		if ($cache_blotter !== false) {
			return $cache_blotter;
		}
	}
	$output = '';

	if ($all) {
		$limit = '';
	} else {
		$limit = ' LIMIT 4';
	}
	$results = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "blotter` ORDER BY `id` DESC" . $limit);
	if (count($results) > 0) {
		if ($all) {
			$output .= '<pre>';
		}
		foreach ($results as $line) {
			if ($all && $line['important'] == 1) {
				$output .= '<font style="color: red;">';
			} elseif (!$all) {
				$output .= '<li class="blotterentry" style="display: none;">' . "\n";
				if ($line['important'] == 1) {
					$output .= '  <span style="color: red;">' . "\n" . '  ';
				}
				$output .= '  ';
			}
			$output .= date('m/d/y', $line['at']) . ' - ' . $line['message'];
			if ($all && $line['important'] == 1) {
				$output .= '</font>' . "\n";
			} elseif (!$all) {
				$output .= "\n";
				if ($line['important'] == 1) {
					$output .= '  </span>' . "\n";
				}
				$output .= '</li>';
			} else {
				$output .= "\n";
			}
			$output .= "\n";
		}
		if ($all) {
			$output .= '</pre>';
		}
	}

	if (I0_YAC) {
		if ($all) {
			$yac->set('blotter|all', $output);
		} else {
			$yac->set('blotter|last4', $output);
		}
	}

	return $output;
}

function getBlotterLastUpdated() {
	global $tc_db;

	return $tc_db->GetOne("SELECT `at` FROM `" . KU_DBPREFIX . "blotter` ORDER BY `id` DESC LIMIT 1");
}

/**
 * Gets information about the filetype provided, which is specified in the manage panel
 *
 * @param string $filetype Filetype
 * @return array Filetype image, width, and height
 */
function getfiletypeinfo($filetype) {
	global $tc_db;

	$key = 'filetype|' . $filetype;

	if ($r = cache_get($key)) return $r;

	$qr = $tc_db->GetAll("SELECT `image`, `image_w`, `image_h` FROM `" . KU_DBPREFIX . "filetypes` WHERE `filetype` = " . $tc_db->qstr($filetype) . " LIMIT 1");
	var_dump($qr);
	$r = (count($qr) > 0)
		? array($r[0]['image'],$r[0]['image_w'],$r[0]['image_h'])
		: array('*',0,0); /* No info was found, return the generic icon */
	cache_set($key, $r);
	return $r;
}

/**
 * Groups multiple embeds into one post
 *
 * @param array $r Postembeds view fetch result
 * @return array Posts with array Embeds[] attached
 */
function group_embeds($r, $group_deleted_files = false) {
	global $tc_db;

	$rg = array();
	$i = -1;
	$current_id = 0;

	foreach($r as $pe) {
		$id = (int)$pe['id'];
		if ($id !== $current_id) {
			$rg []= $pe;
			$i++;
			$rg[$i]['embeds'] = array();
			unset($rg[$i]['file']);
			unset($rg[$i]['file_id']);
			unset($rg[$i]['file_md5']);
			unset($rg[$i]['file_type']);
			unset($rg[$i]['file_original']);
			unset($rg[$i]['file_size']);
			unset($rg[$i]['file_size_formatted']);
			unset($rg[$i]['image_w']);
			unset($rg[$i]['image_h']);
			unset($rg[$i]['thumb_w']);
			unset($rg[$i]['thumb_h']);
			unset($rg[$i]['spoiler']);
			$current_id = $id;
		}
		if (@$pe['file']) {
			if ($group_deleted_files) {
				if ($pe['file'] == 'removed') {
					if (@$rg[$i]['deleted_files']) {
						$rg[$i]['deleted_files']++;
					}
					else {
						$rg[$i]['deleted_files'] = 1;
					}
				}
			}
			$rg[$i]['embeds'] []= array(
				'file' => $pe['file'],
				'file_id' => $pe['file_id'],
				'file_md5' => $pe['file_md5'],
				'file_type' => $pe['file_type'],
				'file_original' => $pe['file_original'],
				'file_size' => $pe['file_size'],
				'file_size_formatted' => $pe['file_size_formatted'],
				'image_w' => $pe['image_w'],
				'image_h' => $pe['image_h'],
				'thumb_w' => $pe['thumb_w'],
				'thumb_h' => $pe['thumb_h'],
				'spoiler' => $pe['spoiler']
			);
		}
	}
	return $rg;
}

function GetFileAndThumbs($file) {
	$res = array();
	if (strpos($file['file_size_formatted'], ':') == false) {
		$res []= '/src/'.$file['file'].'.'.$file['file_type'];
		$res []= '/thumb/'.$file['file'].'s.'.$file['file_type'];
		$res []= '/thumb/'.$file['file'].'c.'.$file['file_type'];
		if ($file['file_type'] == 'mp3') {
			$res []= '/thumb/'.$file['file'].'s.jpg';
			$res []= '/thumb/'.$file['file'].'s.png';
			$res []= '/thumb/'.$file['file'].'s.gif';
			$res []= '/thumb/'.$file['file'].'c.jpg';
			$res []= '/thumb/'.$file['file'].'c.png';
			$res []= '/thumb/'.$file['file'].'c.gif';
		}
		if ($file['file_type'] == 'webm' || $file['file_type'] == 'mp4') {
			$res []= '/thumb/'.$file['file'].'s.jpg';
			$res []= '/thumb/'.$file['file'].'c.jpg';
		}
	}
	else { //if embed
		$res []= '/thumb/'.$file['file_type'].'-'.$file['file'].'-'.'s.jpg';
		$res []= '/thumb/'.$file['file_type'].'-'.$file['file'].'-'.'c.jpg';
	}
	return $res;
}
?>