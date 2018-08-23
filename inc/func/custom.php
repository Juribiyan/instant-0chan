<?php
/* Place any functions you create here. Note that this is not meant for module functions, which should be placed in the module's php file. */

 function image_create_alpha ($width, $height)
{
  // Create a normal image and apply required settings
  $img = imagecreatetruecolor($width, $height);
  imagealphablending($img, false);
  imagesavealpha($img, true);

  // Apply the transparent background
  $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
  for ($x = 0; $x < $width; $x++)
  {
    for ($y = 0; $y < $height; $y++)
    {
      imagesetpixel($img, $x, $y, $trans);
    }
  }

  return $img;
}

function rainbow ($string)
{
  $size=16;
  $steps=2;
  $step=$size/$steps;

  $image = image_create_alpha($size, $size);
  imagecolortransparent($image, 127<<24);

  $n = 0;
  $prev = 0;

  $len = strlen($string);
  $sum = 0;
  for ($i=0;$i<$len;$i++) $sum += ord($string[$i]);

  for ($i=0;$i<$steps;$i++) {
    for ($j=0;$j<$steps;$j++) {
      $letter = $string[$n++ % $len];

      $u = ($n % (ord($letter)+$sum)) + ($prev % (ord($letter)+$len)) + (($sum-1) % ord($letter));
      $color = imagecolorallocate($image, pow($u*$prev+$u+$prev+5,2)%256, pow($u*$prev+$u+$prev+3,2)%256, pow($u*$prev+$u+$prev+1,2)%256);
      if (($u%2)==0)
        imagefilledpolygon($image, array($i*$step, $j*$step, $i*$step+$step, $j*$step, $i*$step, $j*$step+$step), 3, $color);
      $prev = $u;

      $u = ($n % (ord($letter)+$len)) + ($prev % (ord($letter)+$sum)) + (($sum-1) % ord($letter));
      if (($u%2)==0)
        imagefilledpolygon($image, array($i*$step, $j*$step+$step, $i*$step+$step, $j*$step+$step, $i*$step+$step, $j*$step), 3, $color);
      $prev = $u;

    }
  }

  ob_start ();

  imagegif($image);
  $image_data = ob_get_contents ();

	ob_end_clean ();

  return base64_encode ($image_data);
}

function omitted_syntax($posts, $images) {
  $pd = declense($posts); $id = declense($images);
  if($pd == 0) $pw = 'постов';
  elseif($pd == 1) $pw = 'пост';
  else $pw = 'поста';
  $s = $posts.' '.$pw;
  $omit = ' пропущено.';
  if($images) {
    if($id == 0) $iw = 'изображений';
    elseif($id == 1) $iw = 'изображение';
    else $iw = 'изображения';
    $s .= ' и '.$images.' '.$iw;
  }
  elseif($posts == 1) $omit = ' пропущен.';
  return $s.$omit;
}

function declense($num) {
  if($num >= 11 && $num <= 20) return 0;
  $lastnum = $num % 10;
  if($lastnum == 0 || $lastnum >= 5) return 0;
  elseif($lastnum == 1) return 1;
  else return 2;
}

function set_max_filename_width($thumb_w, $has_title) {
  $width = $thumb_w - ($has_title ? 25 : 50);
  return ($width >= 30) ? ' style="max-width:'.$width.'px"' : '';
}

function get_embed_filename($embed) {
  if ($embed['file_original'] == '/hidden')
    return $embed['file'];
  if (isset($embed['id3']['comments_html'])) {
    $r = '';
    if ($embed['id3']['comments_html']['artist'][0])
      $r .= $embed['id3']['comments_html']['artist'][0].' — ';
    if ($embed['id3']['comments_html']['title'][0]) {
      $r .= $embed['id3']['comments_html']['title'][0];
      return $r;
    }
    else
      return $embed['file_original'];
  }
  else
    return $embed['file_original'];
}

?>