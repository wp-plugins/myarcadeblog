<?php
/**
 * Helper Functions - Game
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2014, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Game/Helper
 */

defined('MYARCADE_VERSION') or die();

/* Add Content Filter For Auto Embed Flash */
add_filter('the_content', 'myarcade_embed_handler', 99);

if ( !function_exists('is_game') ) {
  /**
   * Checks if the displayed post is a game post
   *
   * @global $post
   * @return boolean
   */
  function is_game() {
    global $post;

    if ( isset($post->ID) && get_post_meta($post->ID, "mabp_swf_url", true) )
      return true;
    else
      return false;
  }
}

/**
 * Embeds the flash code to the post content if activated
 *
 * @global  $wpdb
 * @global  $post
 * @param <type> $content
 * @return string
 */
function myarcade_embed_handler($content) {
  global $post;

  // Do this only on single posts ...
  if( is_single() && !is_feed() ) {

    $general  = get_option('myarcade_general');
    $game_url = get_post_meta($post->ID, "mabp_swf_url", true);

    // Check if this option is enabled and if this is a game
    if ( ($general['embed'] != 'manually') && !empty($game_url) ) {

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
 * Checks if global scores are enabled
 *
 * @global <type> $wpdb
 * @return boolean
 */
function enabled_global_scores() {
  return false;
}


/**
 * Check the game width. If the game is larger as defined max. width
 * return true, otherwise false.
 *
 * @global  $wpdb
 * @global  $post
 * @param integer $postid
 * @return boolean
 */
function myarcade_check_width($postid) {
  $result = false;

  $general = get_option('myarcade_general');

  $maxwidth   = intval($general['max_width']);
  $gamewidth  = intval(get_post_meta($postid, "mabp_width", true));

  if ($gamewidth > $maxwidth) {
    $result = true;
  }

  return $result;
}