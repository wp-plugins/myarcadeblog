<?php

$root = dirname( dirname( dirname( dirname( dirname(__FILE__)))));
if ( file_exists($root . '/wp-load.php') ) {
  require_once($root . '/wp-load.php'); 
}

// Check user
if ( function_exists('current_user_can') && !current_user_can('edit_posts') ) die();

//define('EVENT_DEBUG', false);

// Debug
/*if (EVENT_DEBUG) {
  $fh = fopen('debug.txt','a');
  fwrite($fh, "\n");
  fwrite($fh, "\n"."--- EVENT DEBUG ---"."\n");
  fwrite($fh, "POST: ".serialize($_POST)."\n\n");
  fwrite($fh, "FILES: ".serialize($_FILES)."\n");
}*/

$home           = get_option('home');
$games_dir_abs  = ABSPATH.MYARCADE_GAMES_DIR;
$thumbs_dir_abs = ABSPATH.MYARCADE_THUMBS_DIR;

$result = false;

$game->info_dim = '';
$game->error = 'none'; 

// Check the submission form
switch ($_POST['upload']) {
  case 'swf':      
    if ( !empty($_FILES['gamefile']['tmp_name']) ){
      $file_temp = $_FILES['gamefile']['tmp_name'];
      $file_name = $_FILES['gamefile']['name'];
      $file_abs   = $games_dir_abs .$file_name;    
      
      $result = move_uploaded_file($file_temp, $file_abs);
    }
    
    if ($result == true) {
      $file_info = pathinfo($file_abs);
      
      $game->type = 'custom';      
      
      $game->name = $file_name;
      $game->location_abs = $file_abs;
      $game->location_url = $home. '/' .MYARCADE_GAMES_DIR.$file_name;
      
      // try to detect dimensions
      //$game_dimensions = @getimagesize($file_abs);
      //$game->width    = intval($game_dimensions[0]);
      //$game->height   = intval($game_dimensions[1]);
      //$game->info_dim = 'Game dimensions: '.$game->width.'x'.$game->height;
      
      //if ( empty($game->width) || empty($game->height) ) {
        $game->width  = 0;
        $game->height = 0;
        $game->info_dim = 'Can not detect game dimensions';
      //}
      
      // Try to get the game name 
      $name = explode('.', $game->name);
      $game->realname = str_replace('_', ' ', $name[0]);
    }
    else {
      $game->error = 'Can not upload file!';  
    }
    break;
    
  case 'thumb':    
    if ( !empty($_FILES['thumbfile']['tmp_name']) ){
      $file_temp = $_FILES['thumbfile']['tmp_name'];
      $file_name = $_FILES['thumbfile']['name'];
      $file_abs   = $thumbs_dir_abs .$file_name;    
      
      $result = move_uploaded_file($file_temp, $file_abs);
    }

    if ($result == true) {
      $game->thumb_name = $file_name;
      $game->thumb_abs  = $file_abs;
      $game->thumb_url  = $home. '/' .MYARCADE_THUMBS_DIR.$file_name;      
    }   
    else {
      $game->error = 'Can not upload file!';  
    }
    break;
      
    default:
      $game->error = 'Unknown Import Method';
      break;
}

// Prepare the output
$json = json_encode($game);

echo '<div id="result_'.$_POST['upload'].'">'.$json.'</div>';

/*if (EVENT_DEBUG) {
  fwrite($fh, "\n"."JSON: ".$json);
  fwrite($fh, "\n"."--- END EVENT DEBUG ---"."\n");
  fclose($fh);
}*/
 
die();
?>