<?php
require 'config.php';
$initial = !$_GET['show'];
$shorten_life = KU_CAPTCHALIFE - 0.5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Instant 0chan® NoJS Captcha™ for iframe (patent pending)</title>
  <link rel="stylesheet" href="/css/captcha.css">
  <style>
    .rotting-indicator {
      -webkit-animation-duration: <?php echo $shorten_life?>s;
           -o-animation-duration: <?php echo $shorten_life?>s;
              animation-duration: <?php echo $shorten_life?>s;
    }
    img, .rotten-msg {
      -webkit-animation-delay: <?php echo $shorten_life?>s;
           -o-animation-delay: <?php echo $shorten_life?>s;
              animation-delay: <?php echo $shorten_life?>s;
    }
  </style>
</head>
<body>
  <form action="">
    <input type="hidden" name="show" value="1">
    <button type="submit">    
    <?php
    if ($initial) 
      echo 
      '<div class="msg">'.(KU_LOCALE=='ru' ? 'Показать капчу' : 'Show captcha').'</div>';
    else 
      echo 
      '<img alt="Captcha image" src="'.KU_WEBFOLDER.'captcha.php?'.(float)rand()/(float)getrandmax().'&color=77,77,77">
      <div class="rotting-indicator"></div>
      <div class="rotten-msg msg">'.(KU_LOCALE=='ru' ? 'Капча-протухла' : 'Captcha has expired').'</div> ';
    ?>
    </button>
  </form>
</body>
</html>