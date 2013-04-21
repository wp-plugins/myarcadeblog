<?php
/* 
Plugin Name:  MyArcadePlugin Lite
Plugin URI:   http://myarcadeplugin.com
Description:  Turn your wordpress blog into an arcade game portal.
Version:      2.60
Author:       Daniel Bakovic
Author URI:   http://netreview.de
*/

/**
Copyright 2009-2012  @ Daniel Bakovic (email : contact@myarcadeplugin.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
*/


/**
 *******************************************************************************
 *   G L O B A L S
 *******************************************************************************
 */
define('MYARCADE_VERSION', '2.60');

// You need at least PHP Version 5.2.0+ to run this plugin
define('MYARCADE_PHP_VERSION', '5.2.0');

// Define needed table constants
global $wpdb;
define('MYARCADE_GAME_TABLE',      $wpdb->prefix.'myarcadegames');
define('MYARCADE_SETTINGS_TABLE',  $wpdb->prefix.'myarcadesettings');

// Direcories
define('MYARCADE_GAMES_DIR',  'wp-content/games/');
define('MYARCADE_THUMBS_DIR', 'wp-content/thumbs/');

// Define the plugins abs paths and urls
define('MYARCADE_DIR',       WP_PLUGIN_DIR.'/myarcadeblog');
define('MYARCADE_URL',       WP_PLUGIN_URL.'/myarcadeblog');

define('MYARCADE_CORE_DIR',  MYARCADE_DIR.'/core');
define('MYARCADE_CORE_URL',  MYARCADE_URL.'/core');
define('MYARCADE_JS_URL', MYARCADE_CORE_URL.'/js');

// Define the translation text domain
define('MYARCADE_TEXT_DOMAIN', 'myarcadeblog');

define('MYARCADE_LOCKED_IMG', '<img src="'.MYARCADE_CORE_URL.'/images/locked.png" alt="Pro Version Only!"> </a>');

// Do this only on WordPress backend
if ( is_admin() ) {
  
  require_once 'core/admin.php';
  
  // Add the main menu to blog
  add_action('admin_menu', 'myarcade_admin_menu', 9);
  // Add some Ajax features
  add_action('admin_menu', 'myarcade_load_ajax', 1);
  
  require_once 'core/setup.php';
  
  // Register install function
  register_activation_hook( __FILE__, 'myarcade_install' );
  if ( is_admin() && !defined('DOING_AJAX') ) myarcade_plugin_update();
  
  require_once 'core/manage.php';
  require_once 'core/import.php';
  
  // Add some Manage Games Ajax Handler
  add_action('wp_ajax_myarcade_handler', 'myarcade_handler');
  
  require_once 'core/addgames.php';
  require_once 'core/fetch.php';
  
  require_once 'core/file.php';
  // Add game delete function
  add_action('before_delete_post', 'myarcade_delete_game');
  
} // end is admin


require_once 'core/output.php';
// Add Content Filter For Auto Embed Flash
add_filter('the_content', 'myarcade_embed_handler', 99);

require_once 'core/user.php';
// Extend the WP Bar
add_action( 'admin_bar_menu', 'myarcade_bar_menu', 1000 );

load_plugin_textdomain(MYARCADE_TEXT_DOMAIN, WP_PLUGIN_DIR.'/myarcadeblog/lang', '/myarcadeblog/lang'); 

function myarcade_init() {
  global $myarcade_distributors, $myarcade_game_type_custom;  
  
  $default_distributors = array(
      'bigfish'       => 'Big Fish Games',
      'fgd'           => 'FlashGameDistribution', 
      'fog'           => 'FreeGamesForYourWebsite',
      'kongregate'    => 'Kongregate', 
      'mochi'         => 'Mochi Media', 
      'myarcadefeed'  => 'MyArcadeFeed',
      'playtomic'     => 'Playtomic', 
      'scirra'        => 'Scirra',
      'spilgames'     => 'Spil Games'
      );
  
  $myarcade_distributors = apply_filters('myarcade_game_distributors', $default_distributors );  
  
  $myarcade_game_type_custom = array(
    'dcr'       => 'DCR Game',
    'custom'    => 'SWF Game',
    'ibparcade' => 'IBPArcade Game',
    'phpbb'     => 'PHPBB Game',
    'embed'     => 'Embed / Iframe Game'
  );  
}
add_action('init', 'myarcade_init');
?>