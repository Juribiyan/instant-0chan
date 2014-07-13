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
 
function rainbow ($ip, $threadno)
{
  $size=16;
  $steps=2;
  $step=$size/$steps;
  
  $string = $ip . $threadno;
  
  $image = image_create_alpha($size, $size);
  
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

  imagepng ($image);
  $image_data = ob_get_contents (); 

	ob_end_clean (); 

return base64_encode ($image_data);

} 

?>