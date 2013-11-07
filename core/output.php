<?php
/**
 * Game Output Functions
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2013, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Posts
 */

/**
 * Shows a game depended on the game type.
 *
 * @global $wpdb
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
      $gamewidth  = apply_filters('myarcade_game_width', get_post_meta($gameID, "mabp_width", true) );
      $gameheight = apply_filters('myarcade_game_height', get_post_meta($gameID, "mabp_height", true) );
    }
    else {
      $gamewidth  = apply_filters('myarcade_fullscreen_width',  '93%');
      $gameheight = apply_filters('myarcade_fullscreen_height', '93%');
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
  if ( !$fullsize && $maxwidth && $gamewidth && $gameheight )  {
    if ($gamewidth > $maxwidth) {
      // Adjust the game dimensions
      $ratio      = $maxwidth / $gamewidth;
      $gamewidth  = $maxwidth;
      $gameheight = $gameheight * $ratio;
    }
  }

  switch ($game_variant) {
    case 'embed': {
      // Embed or Iframe code
      $code = stripcslashes($game_url);
    } break;

    case 'dcr': {
      // Premium
    } break;

    case 'bigfish': {
      // Premium
    } break;

    case 'scirra': {
      // Premium
    } break;

    case 'unity': {
      // Premium
    } break;

    default: {
      $general = get_option( 'myarcade_general' );

      if ( !$preview && isset( $general['swfobject'] ) && $general['swfobject'] ) {
        $flashvars = apply_filters('myarcade_swfobject_flashvars', array(), $game_variant, $gameID );
        $params = apply_filters( 'myarcade_swfobject_params', array( 'wmode' => 'direct' ), $game_variant, $gameID );
        $attributes = apply_filters( 'myarcade_swfobject_attributes', array(), $game_variant, $gameID );
        $code  = '<div id="myarcade_swfobject_content"></div>'."\n";
        $code .= "<script type=\"text/javascript\">swfobject.embedSWF( '".$game_url."', 'myarcade_swfobject_content', '".$gamewidth."', '".$gameheight."', '9.0.0', '', '".json_encode($flashvars)."', '".json_encode($params)."', '".json_encode($attributes)."');</script>";          }
      else {
      $embed_parameters = apply_filters( 'myarcade_embed_parameters', 'wmode="direct" menu="false" quality="high"', $gameID );
      $code = '<embed src="'.$game_url.'" '.$embed_parameters.' width="'.$gamewidth.'" height="'.$gameheight.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
      }
    } break;
  }

  // Show the game
  return $code;
} // END - get_game



function get_game_code( $post_id = false ) {
    global $post;

    if ( !$post_id && isset($post->ID) )
      $post_id = $post->ID;
    else
      return FALSE;

    $game_variant  = get_post_meta($post_id, "mabp_game_type", true);
    $gamewidth  = intval(get_post_meta($post_id, "mabp_width", true));
    $gameheight = intval(get_post_meta($post_id, "mabp_height", true));
    $game_url   = get_post_meta($post_id, "mabp_swf_url", true);

    switch ($game_variant) {
      case 'embed': {
        // Embed or Iframe code
        $code = stripcslashes($game_url);
      } break;

      case 'dcr': {
      } break;

      case 'bigfish': {
      } break;

      case 'scirra': {
      } break;

      default: {
        $embed_parameters = apply_filters( 'myarcade_embed_parameters', 'wmode="direct" menu="false" quality="high"', $gameID );
          $code = '<embed src="'.$game_url.'" '.$embed_parameters.' width="'.$gamewidth.'" height="'.$gameheight.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
      } break;
     }

    return $code;
}

/**
 * Generates the Mochi Leaderboard code
 *
 * @global  $wpdb
 * @global <type> $current_user
 * @global integer $user_ID
 * @global integer $mypostid
 * @global  $post
 * @return string
 */
function myarcade_get_leaderboard_code() {
  //premium
  return;
}