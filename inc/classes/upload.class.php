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
 * Upload class
 * +------------------------------------------------------------------------------+
 * Used for image/misc file upload through the post form on board/thread pages
 * +------------------------------------------------------------------------------+
 */

class Upload {
	function __construct($isreply) {
		$this->isreply = $isreply;
	}
	
	function exitWithUploadErrorPage($errormsg, $attype, $i, $literal_name='') {
		if ($_POST['AJAX']) {
			exitWithErrorPage($errormsg, '', 'upload_error', array(
				'attachmenttype' => $attype,
				'position' => $i
			));
		}
		else {
			$verbose = $literal_name ? '('._gettext('Error in '.$attype).' "'.$literal_name.'")' : '';
			exitWithErrorPage($errormsg, $verbose);
		}
	}

	function UnifyAttachments() {
		global $board_class;

		if ($_POST['legacy-posting']) { // no-js file input (implemented first)

		  $attachments = array();

		  // 1) Collect uploaded files
		  $file_hashes = array();
		  foreach($_FILES['imagefile']['name'] as $i => $filename) {
		  	if ($_FILES['imagefile']['error'][$i] != UPLOAD_ERR_NO_FILE) {
		  		switch ($_FILES['imagefile']['error'][$i]) {
		  			case UPLOAD_ERR_OK:
		  				break;
		  			case UPLOAD_ERR_INI_SIZE:
		  				$this->exitWithUploadErrorPage(sprintf(_gettext('The uploaded file exceeds the upload_max_filesize directive (%s) in php.ini.'), ini_get('upload_max_filesize')),
		  					$atype, $i, $filename);
		  				break;
		  			case UPLOAD_ERR_FORM_SIZE:
		  				$this->exitWithUploadErrorPage(sprintf(_gettext('Please make sure your file is smaller than %dB'), $board_class->board['maximagesize']),
		  					$atype, $i, $filename);
		  				break;
		  			case UPLOAD_ERR_PARTIAL:
		  				$this->exitWithUploadErrorPage(_gettext('The uploaded file was only partially uploaded.'),
		  					$atype, $i, $filename);
		  				break;
		  			case UPLOAD_ERR_NO_TMP_DIR:
		  				$this->exitWithUploadErrorPage(_gettext('Missing a temporary folder.'),
		  					$atype, $i, $filename);
		  				break;
		  			case UPLOAD_ERR_CANT_WRITE:
		  				$this->exitWithUploadErrorPage(_gettext('Failed to write file to disk'),
		  					$atype, $i, $filename);
		  				break;
		  			default:
		  				$this->exitWithUploadErrorPage(_gettext('Unknown File Error'),
		  					$atype, $i, $filename);
		  		}
		  		$file_type = strtolower(preg_replace('/.*(\..+)/','\1', $filename));
		  		if ($file_type == '.jpeg') {
		  			// Fix for the rarely used 4-char format
		  			$file_type = '.jpg';
		  		}
		  		$filetype_withoutdot = substr($file_type, 1);
		  		if (in_array($filetype_withoutdot, $board_class->board['filetypes_allowed'])) {
		  			$file_md5 = md5_file($_FILES['imagefile']['tmp_name'][$i]);
		  			if (in_array($file_md5, $file_hashes)) {
		  				$this->exitWithUploadErrorPage(_gettext('Duplicate file entry detected.'),
		  					$atype, $i, $filename);
		  			}
		  			else {
		  				$file_hashes []= $file_md5;
		  			}
		  			$file_entry = array(
		  				'attachmenttype' => 'file',
		  				'spoiler' => $_POST['spoiler-'.$i] || '0',
		  				'file_original' => $_POST['hidename-'.$i]==1 ? '/hidden' : preg_replace('/(.*)\..+/','\1', $filename),
		  				'tmp_name' => $_FILES['imagefile']['tmp_name'][$i],
		  				'type' => $_FILES['imagefile']['type'][$i],
		  				'size' => $_FILES['imagefile']['size'][$i],
		  				'file_type' => $file_type,
		  				'filetype_withoutdot' => $filetype_withoutdot,
		  				'file_md5' => $file_md5
		  			);
		  			if (in_array($file_entry['file_type'], array('.png', '.gif'))) {
		  				$file_entry['emoji_candidate'] = true;
		  			}
		  			$attachments []= $file_entry;
		  		}
		  		else $this->exitWithUploadErrorPage(_gettext('Sorry, that filetype is not allowed on this board.'),
		  					$atype, $i, $filename);
		  	}
		  }

		  // 2) Collect embeds
		  $embed_hashes = array();
		  if (is_array($_POST['embed']) || is_object($_POST['embed'])) {
				foreach($_POST['embed'] as $i => $code) {
			  	if ($code != '') {
			  		if (array_key_exists($_POST['embedtype'][$i], $board_class->board['embeds_allowed_assoc'])) {
			  			$embed_filetype = $_POST['embedtype'][$i];
			  			$hash = md5($embed_filetype.'/'.$code);
			  			if (in_array($hash, $embed_hashes)) {
			  				$this->exitWithUploadErrorPage(_gettext('Duplicate embed entry detected.'),
			  					$atype, $i, $embed_filetype . '/' . $code);
			  			}
			  			else {
			  				$embed_hashes []= $hash;
			  			}
			  			$attachments []= array(
			  				'attachmenttype' => 'embed',
			  				'spoiler' => $_POST['embed-spoiler-'.$i] || '0',
			  				'embedtype' => $embed_filetype,
			  				'embed' => $code,
			  				'filetype_withoutdot' => $embed_filetype,
			  				'file_md5' => $hash
			  			);
			  		}
			  		else $this->exitWithUploadErrorPage(_gettext('Sorry, that filetype is not allowed on this board.'),
			  					$atype, $i, $_POST['embedtype'][$i] . '/' . $code);
					}
				}
			}
		}

		/*else { // Fancy embeds (not yet implemented)	}*/

		if (count($attachments) > $board_class->board['maxfiles']) {
			exitWithErrorPage(_gettext('Attachments number limit reached.'), _gettext('Maximum number of files + embeds per post is').' '.$board_class->board['maxfiles'].'.', 'upload_error');
		}
		if (!$this->isreply && count($attachments) == 0 && !$board_class->board['enablenofile']) {
			exitWithErrorPage(_gettext('A file or embed ID is required for a new thread.'));
		}
		$this->attachments = $attachments;
	}

	function HandleUpload() {
		global $tc_db, $board_class;

		if (KU_FILESIZE_METHOD == 'sum') {
			$sum = 0;
			foreach($this->attachments as $i => &$attachment) {
				if ($attachment['attachmenttype'] == 'file') {
					$sum += $attachment['size'];
				}
				if ($sum > $board_class->board['maximagesize']) {
					$this->exitWithUploadErrorPage(sprintf(_gettext('Please make sure that the total size of all your files does not exceed %d KB'), round($board_class->board['maximagesize']) / 1024), $atype, $i);
				}
			}
		}

		foreach($this->attachments as $i => &$attachment) {
			$atype = $attachment['attachmenttype'];
			$filename = $atype == 'file'
				? $attachment['file_original'].$attachment['file_type']
				: $attachment['embedtype'] . '/' . $attachment['embed'];
			// Check if attachment already posted somewhere else
			$existing = checkMd5($attachment['file_md5'], $board_class->board['name'], $board_class->board['id']);
			if ($existing) {
				if ($board_class->board['duplication']) { // If file duplication is allowed on this board just copy all the properties from the prototype
					$attachment[$attachment['attachmenttype']=='file' ? 'file_name' : 'embed'] = $existing['file'];
					$attachment['imgWidth'] = $existing['image_w'];
					$attachment['imgHeight'] = $existing['image_h'];
					$attachment['file_size'] = $existing['file_size'];
					$attachment['file_size_formatted'] = $existing['file_size_formatted'];
					$attachment['imgWidth_thumb'] = $existing['thumb_w'];
					$attachment['imgHeight_thumb'] = $existing['thumb_h'];
					$attachment['is_duplicate'] = true;
					if ($attachment['attachmenttype']=='embed')
						$attachment['file_original'] = $existing['file_original'];
					break;
				}
				else {
					$exists_url = KU_BOARDSPATH . '/' . $board_class->board['name'] . '/res/' . $existing['parentid'] . '.html#' . $existing['id'];
					$this->exitWithUploadErrorPage(_gettext('Duplicate file entry detected.') .
						sprintf(_gettext('Already posted %shere%s.'),'<a target="_blank" href="' . $exists_url . '">','</a>'), $atype, $i, $filename);
				}
			}
			// Handle File
			if ($attachment['attachmenttype'] == 'file') {
				if (KU_FILESIZE_METHOD == 'single') {
					if ($attachment['size'] > $board_class->board['maximagesize']) {
						$this->exitWithUploadErrorPage(sprintf(_gettext('Please make sure your file is smaller than %d KB'), round($board_class->board['maximagesize']) / 1024), $atype, $i, $filename);
					}
				}

				$pass = true;
				if (!is_file($attachment['tmp_name']) || !is_readable($attachment['tmp_name'])) {
					$pass = false;
				}
				else {
					if(in_array($attachment['file_type'], array('.jpg', '.gif', '.png'))) {
						if (!@getimagesize($attachment['tmp_name'])) {
							$pass = false;
						}
					}
					elseif($attachment['file_type'] == '.webm') {
						$pass = $this->webmCheck($attachment['tmp_name']);
					}
				}
				if (!$pass) {
					$this->exitWithUploadErrorPage(_gettext('File transfer failure. Please go back and try again.'), $atype, $i, $filename);
				}

				if($attachment['file_type'] == '.css') {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$mime = finfo_file($finfo, $attachment['tmp_name']);
					if(!$mime)
						$this->exitWithUploadErrorPage("Unable to get MIME type of CSS", $atype, $i, $filename);
					if(!preg_match("/^text/i", $mime)) {
						$this->exitWithUploadErrorPage(sprintf(_gettext("Uploaded CSS is non-text (\"%s\")"), $mime), $atype, $i, $filename);
					}
					finfo_close($finfo);
					if(filesize($attachment['tmp_name']) >= KU_MAX_CSS_SIZE)
						$this->exitWithUploadErrorPage(sprintf(_gettext("Uploaded CSS is too big"), KU_MAX_CSS_SIZE), $atype, $i, $filename);
					$css = file_get_contents($attachment['tmp_name']);
					$css_error = check_css($css);
					if($css_error)
						$this->exitWithUploadErrorPage($css_error, $atype, $i, $filename);
				}

				$attachment['file_name'] = time() . mt_rand(1, 99);

				if ($attachment['file_type'] == 'svg') {
					require_once 'svg.class.php';
					$svg = new Svg($attachment['tmp_name']);
					$attachment['imgWidth'] = $svg->width;
					$attachment['imgHeight'] = $svg->height;
				}
				elseif($attachment['file_type'] == '.webm') {
					$webminfo = $pass;
					$attachment['imgWidth'] = $webminfo['width'];
					$attachment['imgHeight'] = $webminfo['height'];
				}
				else {
					$imageDim = getimagesize($attachment['tmp_name']);
					$attachment['imgWidth'] = $imageDim[0];
					$attachment['imgHeight'] = $imageDim[1];
				}

				$attachment['file_size'] = $attachment['size'];

				$filetype_forcethumb = $tc_db->GetOne("SELECT " . KU_DBPREFIX . "filetypes.force_thumb
					FROM " . KU_DBPREFIX . "boards, " . KU_DBPREFIX . "filetypes, " . KU_DBPREFIX . "board_filetypes
					WHERE " . KU_DBPREFIX . "boards.id = " . KU_DBPREFIX . "board_filetypes.boardid
					AND " . KU_DBPREFIX . "filetypes.id = " . KU_DBPREFIX . "board_filetypes.typeid
					AND " . KU_DBPREFIX . "boards.name = '" . $board_class->board['name'] . "'
					AND " . KU_DBPREFIX . "filetypes.filetype = '" . $attachment['filetype_withoutdot'] . "';");
				if ($filetype_forcethumb != '') {
					if ($filetype_forcethumb == 0) {

						/* If this board has a load balance url and password configured for it, attempt to use it */
						if ($board_class->board['loadbalanceurl'] != '' && $board_class->board['loadbalancepassword'] != '') {
							require_once KU_ROOTDIR . 'inc/classes/loadbalancer.class.php';
							$loadbalancer = new Load_Balancer;

							$loadbalancer->url = $board_class->board['loadbalanceurl'];
							$loadbalancer->password = $board_class->board['loadbalancepassword'];

							$response = $loadbalancer->Send('thumbnail', base64_encode(file_get_contents($attachment['tmp_name'])), 'src/' . $attachment['file_name'] . $attachment['file_type'], 'thumb/' . $attachment['file_name'] . 's' . $attachment['file_type'], 'thumb/' . $attachment['file_name'] . 'c' . $attachment['file_type'], '', $this->isreply, true);

							if ($response != 'failure' && $response != '') {
								$response_unserialized = unserialize($response);

								$attachment['imgWidth_thumb'] = $response_unserialized['imgw_thumb'];
								$attachment['imgHeight_thumb'] = $response_unserialized['imgh_thumb'];
							} else {
								$this->exitWithUploadErrorPage(_gettext('File was not properly thumbnailed').': ' . $response, $atype, $i, $filename);
							}
						/* Otherwise, use this script alone */
						} else {
							$attachment['file_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/src/' . $attachment['file_name'] . $attachment['file_type'];

							if($attachment['file_type'] == '.webm') {
								$thumbs = $this->webmThumb($attachment['tmp_name'], KU_BOARDSDIR . $board_class->board['name'] . '/thumb/', $attachment['file_name'], $webminfo['midtime']);
								if($thumbs) {
									$attachment['imgWidth_thumb'] = $thumbs['thumbwidth'];
									$attachment['imgHeight_thumb'] = $thumbs['thumbheight'];
									$move_result = move_uploaded_file($attachment['tmp_name'], $attachment['file_location']);
									if (!$move_result) {
										$this->exitWithUploadErrorPage(_gettext('Could not copy uploaded image.'), $atype, $i, $filename);
									}
									chmod($attachment['file_location'], 0644);
									if ($attachment['size'] != filesize($attachment['file_location']))
										$this->exitWithUploadErrorPage(_gettext('File was not fully uploaded. Please go back and try again.'), $atype, $i, $filename);
								}
								else $this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
							}
							else {
								$attachment['file_thumb_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $attachment['file_name'] . 's' . $attachment['file_type'];
								$attachment['file_thumb_cat_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $attachment['file_name'] . 'c' . $attachment['file_type'];

								if (!move_uploaded_file($attachment['tmp_name'], $attachment['file_location'])) {
									$this->exitWithUploadErrorPage(_gettext('Could not copy uploaded image.'), $atype, $i, $filename);
								}
								chmod($attachment['file_location'], 0644);

								if ($attachment['size'] == filesize($attachment['file_location'])) {
									if ((!$this->isreply && ($attachment['imgWidth'] > KU_THUMBWIDTH || $attachment['imgHeight'] > KU_THUMBHEIGHT)) || ($this->isreply && ($attachment['imgWidth'] > KU_REPLYTHUMBWIDTH || $attachment['imgHeight'] > KU_REPLYTHUMBHEIGHT))) {
										if (!$this->isreply) {
											if (!createThumbnail($attachment['file_location'], $attachment['file_thumb_location'], KU_THUMBWIDTH, KU_THUMBHEIGHT)) {
												$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
											}
										} else {
											if (!createThumbnail($attachment['file_location'], $attachment['file_thumb_location'], KU_REPLYTHUMBWIDTH, KU_REPLYTHUMBHEIGHT)) {
												$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
											}
										}
									} else {
										if (!createThumbnail($attachment['file_location'], $attachment['file_thumb_location'], $attachment['imgWidth'], $attachment['imgHeight'])) {
											$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
										}
									}
									if (!createThumbnail($attachment['file_location'], $attachment['file_thumb_cat_location'], KU_CATTHUMBWIDTH, KU_CATTHUMBHEIGHT)) {
										$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
									}
									$imageDim_thumb = getimagesize($attachment['file_thumb_location']);
									$attachment['imgWidth_thumb'] = $imageDim_thumb[0];
									$attachment['imgHeight_thumb'] = $imageDim_thumb[1];
								} else {
									$this->exitWithUploadErrorPage(_gettext('File was not fully uploaded. Please go back and try again.'), $atype, $i, $filename);
								}
							}
						}
					} else {
						/* Fetch the mime requirement for this special filetype */
						$filetype_required_mime = $tc_db->GetOne("SELECT `mime`
							FROM `" . KU_DBPREFIX . "filetypes`
							WHERE `filetype` = " . $tc_db->qstr($attachment['filetype_withoutdot']));

						/* If this board has a load balance url and password configured for it, attempt to use it */
						if ($board_class->board['loadbalanceurl'] != '' && $board_class->board['loadbalancepassword'] != '') {
							require_once KU_ROOTDIR . 'inc/classes/loadbalancer.class.php';
							$loadbalancer = new Load_Balancer;

							$loadbalancer->url = $board_class->board['loadbalanceurl'];
							$loadbalancer->password = $board_class->board['loadbalancepassword'];

							if ($filetype_required_mime != '') {
								$checkmime = $filetype_required_mime;
							} else {
								$checkmime = '';
							}

							$response = $loadbalancer->Send('direct', $attachment['tmp_name'], 'src/' . $attachment['file_name'] . $attachment['file_type'], '', '', $checkmime, false, true);

							$attachment['file_is_special'] = true;
						/* Otherwise, use this script alone */
						} else {
							$attachment['file_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/src/' . $attachment['file_name'] . $attachment['file_type'];

							if (file_exists($attachment['file_location'])) {
								$this->exitWithUploadErrorPage(_gettext('A file by that name already exists'), $atype, $i, $filename);
								die();
							}

							if($attachment['file_type'] == '.mp3' || $attachment['file_type'] == '.ogg') {
								require_once(KU_ROOTDIR . 'lib/getid3/getid3.php');

								$getID3 = new getID3;
								$getID3->analyze($attachment['tmp_name']);
								if (isset($getID3->info['id3v2']['APIC'][0]['data']) && isset($getID3->info['id3v2']['APIC'][0]['image_mime'])) {
									$source_data = $getID3->info['id3v2']['APIC'][0]['data'];
									$mime = $getID3->info['id3v2']['APIC'][0]['image_mime'];
								}
								elseif (isset($getID3->info['id3v2']['PIC'][0]['data']) && isset($getID3->info['id3v2']['PIC'][0]['image_mime'])) {
									$source_data = $getID3->info['id3v2']['PIC'][0]['data'];
									$mime = $getID3->info['id3v2']['PIC'][0]['image_mime'];
								}

								if($source_data) {
									$im = imagecreatefromstring($source_data);
									if (preg_match("/png/", $mime)) {
										$ext = ".png";
										imagepng($im,$attachment['file_location'].".tmp",0,PNG_ALL_FILTERS);
									} else if (preg_match("/jpg|jpeg/", $mime)) {
										$ext = ".jpg";
										imagejpeg($im, $attachment['file_location'].".tmp");
									} else if (preg_match("/gif/", $mime)) {
										$ext = ".gif";
										imagegif($im, $attachment['file_location'].".tmp");
									}
									$attachment['file_thumb_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $attachment['file_name'] .'s'. $ext;
									$attachment['file_thumb_cat_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $attachment['file_name'] .'c'. $ext;
									if (!$this->isreply) {
										if (!createThumbnail($attachment['file_location'].".tmp", $attachment['file_thumb_location'], KU_THUMBWIDTH, KU_THUMBHEIGHT)) {
											$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
										}
									}
									else {
										if (!createThumbnail($attachment['file_location'].".tmp", $attachment['file_thumb_location'], KU_REPLYTHUMBWIDTH, KU_REPLYTHUMBHEIGHT)) {
											$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
										}
									}
									if (!createThumbnail($attachment['file_location'].".tmp", $attachment['file_thumb_cat_location'], KU_CATTHUMBWIDTH, KU_CATTHUMBHEIGHT)) {
										$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
									}
									$imageDim_thumb = getimagesize($attachment['file_thumb_location']);
									$attachment['imgWidth_thumb'] = $imageDim_thumb[0];
									$attachment['imgHeight_thumb'] = $imageDim_thumb[1];
									unlink($attachment['file_location'].".tmp");
								}
							}

							/* Move the file from the post data to the server */
							if (!move_uploaded_file($attachment['tmp_name'], $attachment['file_location'])) {
								$this->exitWithUploadErrorPage(_gettext('Could not copy uploaded image.'), $atype, $i, $filename);
							}

							/* Check if the filetype provided comes with a MIME restriction */
							if ($filetype_required_mime != '') {
								/* Check if the MIMEs don't match up */
								if (finfo_file( finfo_open( FILEINFO_MIME_TYPE ), $attachment['file_location']) != $filetype_required_mime) {
									/* Delete the file we just uploaded and kill the script */
									unlink($attachment['file_location']);
									$this->exitWithUploadErrorPage(_gettext('Invalid MIME type for this filetype.'), $atype, $i, $filename);
								}
								finfo_close($finfo);
							}

							/* Make sure the entire file was uploaded */
							if ($attachment['size'] != filesize($attachment['file_location']))
								$this->exitWithUploadErrorPage(_gettext('File transfer failure. Please go back and try again.'), $atype, $i, $filename);

							/* Flag that the file used isn't an internally supported type */
							$attachment['file_is_special'] = true;
						}
					}
				} else {
					$this->exitWithUploadErrorPage(_gettext('Sorry, that filetype is not allowed on this board.'), $atype, $i, $filename);
				}
			}
			// Handle Embed
			else {
				$attachment['embed'] = strip_tags(substr($attachment['embed'], 0, 20));
				$video_id = $attachment['embed'];
				$attachment['file_name'] = $video_id;

				if (strpos($video_id, '@') == false && strpos($video_id, '&') == false) {
					$result = $tc_db->GetOne("SELECT HIGH_PRIORITY `videourl`
						FROM `" . KU_DBPREFIX . "embeds`
						WHERE `filetype`= ".$tc_db->qstr($attachment['embedtype']));
					if ($result) {
						$videourl_start = $result;
					}
					else {
						$this->exitWithUploadErrorPage(_gettext('Invalid video type.'), $atype, $i, $filename);
					}

					$attachment['file_type'] = '.' . $attachment['embedtype'];

					$results = $tc_db->GetOne("SELECT COUNT(*)
						FROM `" . KU_DBPREFIX . "postembeds`
						WHERE `boardid` = " . $board_class->board['id'] . "
						AND `file` = " . $tc_db->qstr($video_id) . "
						AND `IS_DELETED` = 0");
					if ($results == 0) {
						/*$thumbw = $this->isreply ? KU_REPLYTHUMBWIDTH : KU_THUMBWIDTH;
						$thumbh = $this->isreply ? KU_REPLYTHUMBHEIGHT : KU_THUMBHEIGHT;*/
						$thumb_tmpfile = tmpfile();
						$video_data = fetch_video_data($attachment['embedtype'], $attachment['embed'], KU_VIDEOTHUMBWIDTH, $thumb_tmpfile);
						if ($video_data['error'])
							$this->exitWithUploadErrorPage($video_data['error'], $atype, $i, $filename);
						$embed_filename = $attachment['embedtype'].'-'.$attachment['embed'].'-';
						$attachment['file_thumb_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $embed_filename . 's.jpg';
						$attachment['file_thumb_cat_location'] = KU_BOARDSDIR . $board_class->board['name'] . '/thumb/' . $embed_filename . 'c.jpg';
						// Copy or create thumbnail
						$metaData = stream_get_meta_data($thumb_tmpfile);
						$thumbfile = $metaData['uri'];
						if ($video_data['width'] <= KU_VIDEOTHUMBWIDTH) {
							$thumbnailed = copy($thumbfile, $attachment['file_thumb_location']);
						}
						else {
							$thumbnailed = createThumbnail($thumbfile, $attachment['file_thumb_location'], KU_VIDEOTHUMBWIDTH, KU_VIDEOTHUMBWIDTH);
						}
						if (!$thumbnailed) {
							$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
						}
						// Copy or create catalog thumbnail
						if ($video_data['width'] <= KU_CATTHUMBWIDTH) {
							$thumbnailed = copy($thumbfile, $attachment['file_thumb_cat_location']);
						}
						else {
							$thumbnailed = createThumbnail($thumbfile, $attachment['file_thumb_cat_location'], KU_CATTHUMBWIDTH, KU_CATTHUMBHEIGHT);
						}
						if (!$thumbnailed) {
							$this->exitWithUploadErrorPage(_gettext('Could not create thumbnail.'), $atype, $i, $filename);
						}
						fclose($thumb_tmpfile);
						// Fill the rest of the data
						$imageDim_thumb = getimagesize($attachment['file_thumb_location']);
						$attachment['imgWidth_thumb'] = $imageDim_thumb[0];
						$attachment['imgHeight_thumb'] = $imageDim_thumb[1];
						$attachment['imgWidth'] = $video_data['width'];
						$attachment['imgHeight'] = $video_data['height'];
						$attachment['file_original'] = $video_data['title'];
						$attachment['file_size_formatted'] = $video_data['duration'];
					}
					else {
						$results = $tc_db->GetAll("SELECT `id`,`parentid`
							FROM `" . KU_DBPREFIX . "postembeds`
							WHERE `boardid` = " . $board_class->board['id'] . "
							AND `file` = " . $tc_db->qstr($video_id) . "
							AND `IS_DELETED` = 0
							LIMIT 1");
						foreach ($results as $line) {
							$real_threadid = ($line[1] == 0) ? $line[0] : $line[1];
							$this->exitWithUploadErrorPage(sprintf(_gettext('That video ID has already been posted %shere%s.', $atype, $i, $filename),
								'<a href="' . KU_BOARDSFOLDER . '/' . $board_class->board['name'] . '/res/' . $real_threadid . '.html#' . $line[1] . '">','</a>'));
						}
					}
				}
				else {
					$this->exitWithUploadErrorPage(_gettext('Invalid ID'), $atype, $i, $filename);
				}
			}
		} unset($i);
	}

	function webmCheck($filepath) {
    if(KU_FFMPEGPATH) putenv('PATH=' . KU_FFMPEGPATH . PATH_SEPARATOR . getenv('PATH'));
		exec("ffprobe -i ".$filepath." 2>&1", $finfo, $x);
    if($x !== 0) return false;
    $finfo = implode('<br>', $finfo);
		preg_match('/Duration: (\d\d\:\d\d\:\d\d\.\d\d)/', $finfo, $duration);
		preg_match('/(\d+)x(\d+)/', $finfo, $dimensions);
		$hhmmss = explode(':', $duration[1]);
		if(count($duration) == 2 && count($dimensions) == 3) return array(
			'width' => $dimensions[1],
			'height' => $dimensions[2],
			'midtime' => gmdate("H:i:s", ($hhmmss[0]*3600 + $hhmmss[1]*60+ round($hhmmss[2]))/2)
		);
		else return false;
	}

	function webmThumb($filepath, $thumbpath, $filename, $midtime) {
    if(KU_FFMPEGPATH) putenv('PATH=' . KU_FFMPEGPATH . PATH_SEPARATOR . getenv('PATH'));
		$scale = "w=".KU_THUMBWIDTH.":h=".KU_THUMBHEIGHT;
		$scalecat = "w=".KU_CATTHUMBWIDTH.":h=".KU_CATTHUMBHEIGHT;
		$foar = ':force_original_aspect_ratio=decrease';
		$rawdur = shell_exec("ffmpeg -i ".$filepath." 2>&1");
		$common = ' -ss '.$midtime.' -vframes 1 -filter:v scale=';
		$newfn = $thumbpath.$filename;
		exec('ffmpeg -i '.$filepath.$common.$scale.$foar.' '.$newfn.'s.jpg'.$common.$scalecat.$foar.' '.$newfn.'c.jpg 2>&1', $result, $x);
    if($x !== 0) return false;
		preg_match('/Output[\s\S]+?(\d+)x(\d+)[\s\S]+?(\d+)x(\d+)/m', implode('<br>', $result), $ths);
		if(count($ths) == 5) return array(
			'thumbwidth' => $ths[1],
			'thumbheight' => $ths[2],
			'catthumbwidth' => $ths[3],
			'catthumbheight' => $ths[4]
		);
		else return false;
	}
}
?>
