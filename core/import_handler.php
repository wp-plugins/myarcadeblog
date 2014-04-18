<?php
/**
 * Import Handler
 * Handles file uploads for each game type
 *
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2014, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Import
 */

// Check user
if ( function_exists('current_user_can') && !current_user_can('edit_posts') ) die();

require_once(ABSPATH . 'wp-admin/includes/file.php');

// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
$upload_error_strings = array( false,
  __( "The uploaded file exceeds the upload_max_filesize directive in php.ini." ),
  __( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form." ),
  __( "The uploaded file was only partially uploaded." ),
  __( "No file was uploaded." ),
  '',
  __( "Missing a temporary folder." ),
  __( "Failed to write file to disk." ),
  __( "File upload stopped by extension." ));

$home           = get_option('siteurl');
$games_dir_abs  = ABSPATH.MYARCADE_GAMES_DIR;
$thumbs_dir_abs = ABSPATH.MYARCADE_THUMBS_DIR;

$game = new stdClass();
$game->info_dim = '';
$game->error = '';

$result = false;

// Check the submission
switch ( $_POST['upload'] ) {

  // Upload SWF / DCR File
  case 'swf':
  {
    if ( !empty($_FILES['gamefile']['name']) ) {
      // Error check
      if ( !empty($_FILES['gamefile']['error']) ) {
        $game->error = $upload_error_strings[$_FILES['gamefile']['error']];
      }
      else {
        $file_temp = $_FILES['gamefile']['tmp_name'];
        $file_info = pathinfo($_FILES['gamefile']['name']);
        // generate new file name
        $file_abs_array = myarcade_get_folder_path($file_info['filename'], 'custom');
        $file_abs = ABSPATH . $file_abs_array['game'];
        $file_name = wp_unique_filename($file_abs, $file_info['basename']);
        $result = move_uploaded_file($file_temp, $file_abs . $file_name);
        // Delete temp file
        @unlink($_FILES['gamefile']['tmp_name']);
      }
    }
    else {
      $result = false;
    }

    if ( empty($game->error) ) {

      if ($result == true) {
        //$file_info = pathinfo($file_abs);

        // Get the file extension
        if ( strtolower( $file_info['extension'] ) == 'dcr') {
          $game->type = 'dcr';
        }
        else {
          $game->type = 'custom';
        }

        $game->name = ucfirst($file_info['filename']);
        $game->location_abs = $file_abs . $file_name;
        $game->location_url = $home. '/' . $file_abs_array['game'] . $file_name;

        // try to detect dimensions
        $game_dimensions = @getimagesize($game->location_abs);
        $game->width    = intval($game_dimensions[0]);
        $game->height   = intval($game_dimensions[1]);
        $game->info_dim = 'Game dimensions: '.$game->width.'x'.$game->height;

        if ( empty($game->width) || empty($game->height) ) {
          $game->width  = 0;
          $game->height = 0;
          $game->info_dim = 'Can not detect game dimensions';
        }

        // Try to get the game name
        $name = explode('.', $game->name);
        $game->realname = ucfirst( str_replace('_', ' ', $name[0]) );
      }
      else {
        $game->error = __("Can not upload file!", MYARCADE_TEXT_DOMAIN);
      }
    }
  }
  break;

  // Upload Game Thumb
  case 'thumb':
  {
    if ( !empty($_FILES['thumbfile']['name']) ) {
      // Error check
      if ( !empty($_FILES['gamefile']['error']) ) {
        $game->error = $upload_error_strings[$_FILES['gamefile']['error']];
      }
      else {
        $file_temp = $_FILES['thumbfile']['tmp_name'];
        $file_info = pathinfo($_FILES['thumbfile']['name']);
        // generate new file name
        $file_abs_array = myarcade_get_folder_path($file_info['filename'], 'custom');
        $file_abs = ABSPATH . $file_abs_array['image'];
        $file_name = wp_unique_filename($file_abs, $file_info['basename']);
        $result = move_uploaded_file($file_temp, $file_abs . $file_name);
        // Delete temp file
        @unlink($_FILES['thumbfile']['tmp_name']);
      }
    }


    if ( empty($game->error) ) {
      if ($result == true) {
        $game->thumb_name = $file_name;
        $game->thumb_abs  = $file_abs;
        $game->thumb_url  = $home . '/' . $file_abs_array['image'] . $file_name;
      }
      else {
        $game->error = 'Can not upload thumbnail!';
      }
    }
  }
  break;

  // Upload Game Screenshots
  case 'screen':
  {
    for ($i = 0; $i <= 3; $i++) {
      $screen = 'screen'.$i;
      $result = false;

      if ( !empty($_FILES[$screen]['name']) ) {
        // Error check
        if ( !empty($_FILES[$screen]['error']) ) {
          $game->error = $upload_error_strings[$_FILES[$screen]['error']];
        }
        else {
          // There is a screen to upload
          $file_temp = $_FILES[$screen]['tmp_name'];
          $file_info = pathinfo($_FILES[$screen]['name']);
          $file_abs_array = myarcade_get_folder_path($file_info['filename'], 'custom');
          $file_abs = ABSPATH . $file_abs_array['image'];
          $file_name = wp_unique_filename($file_abs, $file_info['basename']);
          $result = move_uploaded_file($file_temp, $file_abs . $file_name);
          // Delete temp file
          @unlink($_FILES[$screen]['tmp_name']);
        }
      }

      if ($result == true) {
        $game->screen_abs[$i] = $file_abs;
        $game->screen_url[$i] = $home . '/' . $file_abs_array['image'] . $file_name;
        $game->screen_name[$i]= $file_name;
        $game->screen_error[$i] = 'OK';
      }
      else {
        $game->screen_error[$i] = 'Upload Failed For Screen No. '.($i+1).' '.$game->error;
        $game->screen_abs[$i] = '';
        $game->screen_url[$i] = '';
        $game->screen_name[$i]= '';
      }
    }
  }
  break;

  // Import Embed / Iframe Code
  case 'emif':
  {
    if ( !empty($_POST['embedcode']) ) {
      $game->type = 'embed';
      $game->importgame = urlencode(str_replace('"', '\'', $_POST['embedcode']));
      $game->result = 'OK';
    }
    else {
      $game->error = 'No embed code entered!';
    }
  }
  break;

  // What to import??
  default:
  {
    $game->error = 'Unknown Import Method';
  }
  break;

} // end swtich

//
// Prepare the output
//
$json = json_encode($game);

wp_die( $json );