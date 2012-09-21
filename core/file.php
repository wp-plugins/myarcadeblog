<?php
/*
Module:       This modul contains MyArcadePlugin file handle functions
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/

defined('MYARCADE_VERSION') or die();

if (!function_exists('file_put_contents')) {
  /**
   * Alternative file_put_contents function
   *
   * @param <type> $filename
   * @param <type> $data
   * @return <type>
   */
  function file_put_contents($filename, $data) {
    $f = @fopen($filename, 'w');
    if (!$f) {
      return false;
    } else {
      $bytes = fwrite($f, $data);
      fclose($f);
      return $bytes;
    }
  }
}


/**
 * Deletes a given file from the hard drive
 *
 * @param <type> $dir_p
 * @param <type> $file_p
 */
function myarcade_del_file($dir_p, $file_p) {
  if ( file_exists($dir_p.$file_p) && is_writable($dir_p) ) {
    @unlink($dir_p.$file_p);
  }
}

/**
 * Checks if a game is deleteable
 * @param type $gametype
 * @return boolean 
 */
function myarcade_is_game_deleteable($gametype) {
  switch ($gametype) {
    case 'mochi':
    case 'heyzap':
    case 'ibparcade':
    case 'kongregate':
    case 'playtomic':
    case 'fgd':
    case 'fog':
    case 'spilgames':
    {
      $result = true;
    } break;
    
    default: 
    {
      $result = false;
    } break;
  }
  
  return $result;
}


/**
 * Delete game files when deleting a post
 *
 * @global <type> $wpdb
 * @param <type> $post_ID
 */
function myarcade_delete_game($post_ID) {
  global $wpdb;

  // Get myarcadeplugin settings
  $general = get_option('myarcade_general');  

  // Should game files be deleted
  if ( $general['delete'] ) {
    // Delete game thumb if exists
    $thumburl = get_post_meta($post_ID, "mabp_thumbnail_url", true);

    if ($thumburl) {
      myarcade_del_file(ABSPATH.MYARCADE_THUMBS_DIR, basename($thumburl));
    }

    // Delete game screenshots if exists
    for ($i = 1; $i <= 4; $i++) {
      $screenshot = get_post_meta($post_ID, "mabp_screen".$i."_url", true);

      if ($screenshot) {
        myarcade_del_file(ABSPATH.MYARCADE_THUMBS_DIR, basename($screenshot));
      }
    } // END for screens

    // Delete game swf if exists
    $gameurl  = get_post_meta($post_ID, "mabp_swf_url", true);
    $gametype = get_post_meta($post_ID, "mabp_game_type", true);
    
    if ( myarcade_is_game_deleteable($gametype) && $gameurl ) {
      myarcade_del_file(ABSPATH.MYARCADE_GAMES_DIR, basename($gameurl));
    }
  } // END if delete files

  // Set game status to deleted
  $query = "UPDATE `".MYARCADE_GAME_TABLE."` SET
           `status` = 'deleted',
           `postid` = ''
           WHERE `postid` = '$post_ID'";

  $wpdb->query($query);
  
  return true;
}


/**
 * Downloads a given file using WordPress HTTP function. After the download
 * the file content will be returned. On error the function will return false.
 *
 * @param <type> $url
 * @return file
 */
function myarcade_get_file($url) {
  
  $output = array ( 'response' => null, 'error' => null );
  
  $args = array('timeout' => '300');
  $response = wp_remote_get($url, $args);
 
  // Check for error
  if ( is_wp_error($response) ) {
    $output['error'] = $response->get_error_message();
  }
  else {
    // Check if the server sent a 404 code
    if (wp_remote_retrieve_response_code($response) == 404) {
      $output['error'] = __("File not found", MYARCADE_TEXT_DOMAIN);
    }    
  }
  
  $output['response'] = wp_remote_retrieve_body($response);
  
  return $output;
}
?>