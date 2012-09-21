<?php
/*
Module:       This modul contains MyArcadePlugin output functions
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/

if ( !function_exists('is_game') ) {
  /**
   * Checks if the displayed post is a game post
   * 
   * @global $post
   * @return boolean
   */
  function is_game() {
    global $post;
    
    if ( get_post_meta($post->ID, "mabp_swf_url", true) ) 
      return true;
    else 
      return false;
  }
}

/**
 * Check the game width. If the game is larger as defined max. width
 * return true, otherwise false.
 *
 * @param integer $postid
 * @return boolean
 */
function myarcade_check_width($postid) {
  
  $general = get_option('myarcade_general');
  $maxwidth   = intval($general['max_width']);
  $gamewidth  = intval(get_post_meta($postid, "mabp_width", true));
  
  return ($gamewidth > $maxwidth) ? true : false;
}


/**
 * @brief Embed the flash code if activated
 */
function myarcade_embed_handler($content) {
  global $post;
  
  // Do this only on single posts ...
  if ( is_single() && !is_feed() ) {
  
    $general  = get_option('myarcade_general');
    
    // Check if this option is enabled and if this is a game
    if ( ($general['embed'] != 'manually') && is_game($post->ID) ) {
      
      // Get the embed code of the game
      $embed_code = get_game($post->ID);
      
      // Add the embed code to the content
      if ( $general['embed'] == 'top' ) {
        $embed_code = '<div style="margin: 10px 0;text-align:center;">'.$embed_code.'</div>';
        $content = $embed_code.$content;
      }
      else {
        $embed_code = '<div style="clear:both;margin: 10px 0;text-align:center;">'.$embed_code.'</div>';
        $content = $content.$embed_code;
      }
    }
  }

  return $content;
}


/**
 * Shows a game depended on the game type.
 *
 * @global  $wpdb
 * @global <type> $post
 * @param integer $gameID Post ID or Game ID (game table)
 * @param bool $fullsize
 * @param bool $preview
 * @param bool $fullscreen
 * @return string
 */
function get_game($gameID, $fullsize = false, $preview = false, $fullscreen = false) {
  global $wpdb, $mypostid;
  
  $mypostid = $gameID;

  if ($preview == false) {
    if ( $fullscreen == false ) {
      $gamewidth  = intval(get_post_meta($gameID, "mabp_width", true));
      $gameheight = intval(get_post_meta($gameID, "mabp_height", true));      
    }
    else {
      $gamewidth = '93%';
      $gameheight = '93%';      
    }
    
    $game_url   = apply_filters( 'myarcade_swf_url', get_post_meta($gameID, "mabp_swf_url", true) );
    $game_variant = get_post_meta($gameID, "mabp_game_type", true);
  }
  else {
    $game = $wpdb->get_row("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'");
    $game_url = $game->swf_url;
    $game_variant =  $game->game_type;
    $gamewidth  = intval($game->width);
    $gameheight = intval($game->height);
  }

  $general  = get_option('myarcade_general');
  $mochi    = get_option('myarcade_mochi');
  
  $maxwidth   = intval($general['max_width']);

  // Check if we have a Mochimedia ID
  if ( !empty( $mochi['publisher_id'] ) && ($game_variant == 'mochi')) {
    $game_url .= '?affiliate_id='.$mochi['publisher_id'];
  }

  // Should the game be resized..
  if ( ($fullsize == false) && $maxwidth )  {
    if ($gamewidth > $maxwidth) {
      // Adjust the game dimensions
      $ratio      = $maxwidth / $gamewidth;
      $gamewidth  = $maxwidth;
      $gameheight = $gameheight * $ratio;
    }
  }
  
  // Embed game code
  switch ($game_variant) {
    case 'embed': {
      // Embed or Iframe code
      $code = stripcslashes($game_url);
    } break;
  
    default: {
      $code = '<embed src="'.$game_url.'" wmode="direct" menu="false" quality="high" width="'.$gamewidth.'" height="'.$gameheight.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
    } break;      
  }

  // Show the game
  return $code;
} // END - get_game


function myarcade_comment() {
  echo "\n"."<!-- Powered by MyArcadePlugin Pro - http://myarcadeplugin.com -->"."\n";
}

function myarcade_meta_output() {
  echo "\n"."<meta ... />"."\n";
}

// Dummy functions to make MyArcadePlugin Lite compatible with premium Themes
function myarcade_get_leaderboard_code() {
  return false;
}
function enabled_global_scores() {
  return false;
}
?>