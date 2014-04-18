<?php
/**
 * File handle functions
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2014, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/File
 */

/*
 * Copyright @ Daniel Bakovic - kontakt@netreview.de
 * Do not modify! Do not sell! Do not distribute! -
 * Check our license Terms!
 */

// No direct access
if( !defined( 'ABSPATH' ) ) {
  die();
}

defined('MYARCADE_VERSION') or die();

/* Add game delete function */
add_action('before_delete_post', 'myarcade_delete_game');


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
function myarcade_del_file( $file_abs ) {
  if ( file_exists( $file_abs ) ) {
      @unlink( $file_abs );
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
    case 'gamefeed':
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
 * Get the abspath of the given URL
 * base_dir = MYARCADE_GAMES_DIR or MYARCADE_THUMBS_DIR
 * return string abspath or boolean false on fail
 */
function myarcade_get_abs_path( $base_dir, $url )  {

  $path_parts = pathinfo($url);

  if ( empty($path_parts['dirname']) || empty($path_parts['basename']) ) {
    return false;
  }

  preg_match("@".$base_dir."(.*)@", $path_parts['dirname'], $match);

  if ( isset($match[1]) && !empty($match[1]) ) {
    if ( strpos($match[1], -1) !== '/' ) {
      $match[1] .= '/';
    }

    return ABSPATH . $base_dir . $match[1] . $path_parts['basename'];
  }

  return false;
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
      $thumb_abs = myarcade_get_abs_path(MYARCADE_THUMBS_DIR, $thumburl);
      if ($thumb_abs)
        myarcade_del_file($thumb_abs);
    }

    // Delete game screenshots if exists
    for ($i = 1; $i <= 4; $i++) {
      $screenshot = get_post_meta($post_ID, "mabp_screen".$i."_url", true);

      if ($screenshot) {
        $screen_abs = myarcade_get_abs_path(MYARCADE_THUMBS_DIR, $screenshot);
        if ($screen_abs)
          myarcade_del_file($screen_abs);
      }
    } // END for screens

    // Delete game swf if exists
    $gameurl  = get_post_meta($post_ID, "mabp_swf_url", true);
    $gametype = get_post_meta($post_ID, "mabp_game_type", true);

    if ( myarcade_is_game_deleteable($gametype) && $gameurl ) {
      $game_abs = myarcade_get_abs_path(MYARCADE_GAMES_DIR, $gameurl);
      if ($game_abs)
        myarcade_del_file($game_abs);
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

  $args = array('timeout' => '300', 'sslverify' => false);
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

/**
 * Determinate the file folder depended on the game type, file type and file name
 * @param string $name - name of the file
 * @param string $type - game type
 * @return array folder paths
 */
function myarcade_get_folder_path($name = '', $type = '') {
  global $myarcade_feedback;

  // Initialize the base folder
  $base_folder = array(
    'game'  => MYARCADE_GAMES_DIR,
    'image' => MYARCADE_THUMBS_DIR
  );

  if ( empty($name) || empty($type) ) {
    $myarcade_feedback->add_message("Missing parameters on the create folder function!");
    return $base_folder;
  }

  $general = get_option('myarcade_general');
  $general['folder_structure'] = trim($general['folder_structure']);

  // If not folder structure is set then return false. Check if user has entered just a slash ("/")
  if ( empty($general['folder_structure']) || (strlen($general['folder_structure']) <= 1) ) {
    return $base_folder;
  }

  // Init folder vars
  $folder = false;
  $sub_folder = "0-9";

  // Check the first char of the game name
  if ( ctype_alnum($name[0]) && !is_numeric($name[0]) ) {
    // Use alphabetic folder
    $sub_folder = ucfirst($name[0]);
  }

  // Replace placeholders with the defined folder structure
  $folder = str_replace("%game_type%", $type, $general['folder_structure']);
  $folder = str_replace("%alphabetical%", $sub_folder, $folder);
  // Clean up the folder string
  $folder = str_replace( '//', '/', $folder );
  // Check if the path ends with "/". If not, add one.
  if ( substr($folder, -1) !== '/' ) $folder .= '/';

  $folder_array = array(
    'game'  => $base_folder['game']  . $folder,
    'image' => $base_folder['image'] . $folder
  );

  // Check if the folder exists and create if needed
  if ( !wp_mkdir_p(ABSPATH . $folder_array['game']) ) {
    // Folder creation failed
    $myarcade_feedback->add_message("Can't create folder: ".$folder_array['game']);
    // Set base folder
    $folder_array['game'] = $base_folder['game'];
  }

  if ( !wp_mkdir_p(ABSPATH . $folder_array['image']) ) {
    // Folder creation failed
    $myarcade_feedback->add_message("Can't create folder: ".$folder_array['image']);
    // Set base folder
    $folder_array['image'] = $base_folder['image'];
  }

  return $folder_array;
}


/**
 * Display a max post size message
 *
 */
function myarcade_get_max_post_size_message() {

  $post_max_size = ini_get( 'post_max_size' ) . 'B';

  if ( $post_max_size ) {
    ?>
    <div class="mabp_info mabp_680">
      <?php echo sprintf( __("Your server settings allow you to upload files up to %s.", MYARCADE_TEXT_DOMAIN), $post_max_size ); ?>
    </div>
    <?php
  }
}

/**
 * Returns the max post size in bytes
 *
 * @return int
 */
function myarcade_get_max_post_size_bytes() {

  $post_max_size = ini_get( 'post_max_size' );

  switch (substr ($post_max_size, -1)) {
    case 'M': case 'm': return (int)$post_max_size * 1048576;
    case 'K': case 'k': return (int)$post_max_size * 1024;
    case 'G': case 'g': return (int)$post_max_size * 1073741824;
    default: return $post_max_size;
  }
}

/**
 * Output file list
 */
function myarcade_get_filelist() {

  if ( empty( $_POST['type'] ) ) {
    exit;
  }

  $type = $_POST['type'];
  $dir =  ABSPATH . MYARCADE_GAMES_DIR . 'uploads/' . $type;

  $files_array = array();

  // Open directory
  $handle = opendir( $dir );

  if ( $handle ) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != ".DS_Store") {
        $files_array[] = $file;
      }
    }
    closedir($handle);

    natcasesort($files_array);
  }

  if ( !empty( $files_array ) ) {
    echo '<select name="fileselect'.$type.'" id="fileselect'.$type.'">';
    foreach ($files_array as $file_name) {
      echo '<option value="'.$file_name.'">'.$file_name.'</option>';
    }
    echo '</select>';

  }
  else {
   echo '<span id="fileselect'.$type.'">'. __("No files found!", MYARCADE_TEXT_DOMAIN) . '</span>';
  }

  exit;
}


/**
 * Creates needed directories
 */
function myarcade_create_directories() {

  // Game folders
  $upload_dir   = wp_upload_dir();
  $image_url    = $upload_dir['basedir'] . '/thumbs';
  $games_url    = $upload_dir['basedir'] . '/games';
  @wp_mkdir_p($image_url);
  @wp_mkdir_p($games_url);

  @wp_mkdir_p( ABSPATH . MYARCADE_GAMES_DIR );
  @wp_mkdir_p( ABSPATH . MYARCADE_GAMES_DIR . 'uploads/swf' );
  @wp_mkdir_p( ABSPATH . MYARCADE_GAMES_DIR . 'uploads/ibparcade' );
  @wp_mkdir_p( ABSPATH . MYARCADE_GAMES_DIR . 'uploads/phpbb' );
  @wp_mkdir_p( ABSPATH . MYARCADE_GAMES_DIR . 'uploads/unity' );
  @wp_mkdir_p( ABSPATH . MYARCADE_THUMBS_DIR );
}