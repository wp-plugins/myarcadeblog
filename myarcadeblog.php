<?php
/**
 * Plugin Name:  MyArcadePlugin Lite
 * Plugin URI:   http://myarcadeplugin.com
 * Description:  Turn your wordpress blog into an arcade game portal.
 * Version:      3.0.0
 * Author:       Daniel Bakovic
 * Author URI:   http://netreview.de
 */

/**
 * Copyright 2009-2012  @ Daniel Bakovic (email : contact@myarcadeplugin.com)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 */


/**
 *******************************************************************************
 *   G L O B A L S
 *******************************************************************************
 */
define('MYARCADE_VERSION', '3.0.0');

// You need at least PHP Version 5.2.0+ to run this plugin
define('MYARCADE_PHP_VERSION', '5.2.0');

// Download Folders
define('MYARCADE_GAMES_DIR',  'wp-content/games/');
define('MYARCADE_THUMBS_DIR', 'wp-content/thumbs/');

// Define needed table constants
global $wpdb;
define('MYARCADE_GAME_TABLE',      $wpdb->prefix.'myarcadegames');
define('MYARCADE_SETTINGS_TABLE',  $wpdb->prefix.'myarcadesettings');

// Define the translation text domain
define('MYARCADE_TEXT_DOMAIN', 'myarcadeplugin');

// Define the plugins abs path
$dirname = basename( dirname( __FILE__ ) );
define('MYARCADE_DIR',        WP_PLUGIN_DIR     . '/' . $dirname );
define('MYARCADE_CORE_DIR',   MYARCADE_DIR      . '/core');
define('MYARCADE_JS_DIR',     MYARCADE_CORE_DIR . '/js');
define('MYARCADE_URL',        WP_PLUGIN_URL     . '/' . $dirname );
define('MYARCADE_CORE_URL',   MYARCADE_URL      . '/core');
define('MYARCADE_MODULE_URL', MYARCADE_URL      . '/modules');
define('MYARCADE_JS_URL',     MYARCADE_CORE_URL . '/js');

define('MYARCADE_PLUGIN_SLUG', basename( dirname( __FILE__ ) ) );

// DO THIS ONLY IN BACKEND
if ( is_admin() ) {
  require_once 'core/myarcade_setup.php';
  /* Register install function */
  register_activation_hook( __FILE__, 'myarcade_install' );
  /* Register uninstall function */
  register_deactivation_hook( __FILE__, 'myarcade_uninstall' );

  require_once 'core/myarcade_admin.php';
  require_once 'core/myarcade_manage.php';
  require_once 'core/import.php';
  require_once 'core/meta.php';

  // WP File Upload handling
  add_filter('upload_dir', 'myarcade_downloads_upload_dir');
  add_filter('upload_mimes', 'myarcade_upload_mimes');
  add_action('media_upload_myarcade_image', 'myarcade_media_upload_game_files');
  add_action('media_upload_myarcade_game', 'myarcade_media_upload_game_files');

  add_action('wp_loaded', 'myarcade_plugin_update');

  // Check if we should display the settings update notice
  if ( get_transient('myarcade_settings_update_notice') ) {
    add_action('admin_notices', 'myarcade_plugin_update_notice', 99);
  }

  // publish games handler used by publish games panel
  add_action('wp_ajax_myarcade_ajax_publish', 'myarcade_ajax_publish');
  add_action('wp_ajax_myarcade_get_filelist', 'myarcade_get_filelist');
}

// Do this on the backend and on cron triggers
if ( is_admin() ||  defined('MYARCADE_DOING_ACTION') || defined('DOING_CRON') ) {
  require_once 'core/feedback.php';
  require_once 'core/schedule.php';
  require_once 'core/addgames.php';
  require_once 'core/file.php';
  require_once 'core/fetch.php';
}

require_once 'core/template.php';
require_once 'core/game.php';
require_once 'core/output.php';
require_once 'core/user.php';

//______________________________________________________________________________
// FUNCTIONS

function myarcade_plugin_update() {
  if ( get_option('myarcade_version') && (get_option('myarcade_version') != MYARCADE_VERSION ) ) {
    myarcade_install();
  }
}

function myarcade_init() {
  global $myarcade_distributors, $myarcade_game_type_custom;

  // Load the language file
  load_plugin_textdomain( MYARCADE_TEXT_DOMAIN, false, dirname( plugin_basename(__FILE__) ) . '/lang');

  $default_distributors = array(
      'myarcadefeed'  => 'MyArcadeFeed',
      'mochi'         => 'Mochi Media',
      'spilgames'     => 'Spil Games',
      'bigfish'       => '- PRO - Big Fish Games',
      'fgd'           => '- PRO - FlashGameDistribution',
      'fog'           => '- PRO - FreeOnlineGames',
      'gamefeed'      => '- PRO - GameFeed',
      'kongregate'    => '- PRO - Kongregate',
      'scirra'        => '- PRO - Scirra'
      );

  $myarcade_distributors = apply_filters('myarcade_game_distributors', $default_distributors );

  $myarcade_game_type_custom = array(
    'custom'    => 'SWF Game',
    'embed'     => 'Embed / Iframe Game',
    'dcr'       => '- PRO - DCR Game',
    'ibparcade' => '- PRO - IBPArcade Game',
    'phpbb'     => '- PRO - PHPBB Game',
    'unity'     => '- PRO - Unity'
  );
}
add_action('init', 'myarcade_init');

/**
 * Helper function for selects
 */
function myarcade_selected( $selected, $current) {
  if ( $selected === $current) {
    echo ' selected';
  }
}

/**
 * Helper function for checkboxes
 */
function myarcade_checked( $var, $value) {
  if ( $var === $value) {
    echo ' checked';
  }
}

function myarcade_checked_array($var, $value) {
  if ( is_array($var) ) {
    foreach ($var as $element) {
      if ( $element === $value) {
        echo ' checked';
        break;
      }
    }
  }
}

function myarcade_import_handler() {
  require_once( MYARCADE_CORE_DIR . '/import_handler.php');
}
add_action('wp_ajax_myarcade_import_handler', 'myarcade_import_handler');

function myarcade_frontend_scripts() {
  if ( is_admin() ) return;
  if ( ! is_single() ) return;
  if ( ! is_game() ) return;

  $general = get_option( 'myarcade_general' );

  if ( isset( $general['swfobject']) && $general['swfobject'] ) {
    wp_enqueue_script( 'swfobject' );
  }
}
add_action( 'wp_print_scripts', 'myarcade_frontend_scripts' );


function myarcade_premium_img() {
  echo '<img src="'.MYARCADE_URL.'/images/locked.png" alt="Pro Version Only!" />';
}
function myarcade_premium_message() {
  ?>
  <div class="mabp_info">
    <?php myarcade_premium_img() ?> Please consider upgrading to <a href="htpp://myarcadeplugin.com" title="Upgrade">MyArcadePlugin Pro</a> if you want to use this feature.
  </div>
  <?php
}