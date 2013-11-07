<?php
/**
 * MyArcadePlugin Template Functions
 *
 * Author:       Daniel Bakovic
 * Author URI:   http://myarcadeplugin.com
 */

defined('MYARCADE_VERSION') or die();

add_action( 'wp_head', 'myarcade_generator' );
add_action( 'wp_footer', 'myarcade_comment' );

function myarcade_generator() {
	echo "\n" . '<!-- MyArcadePlugin Lite Version -->' . "\n" . '<meta name="generator" content="MyArcadePlugin Lite ' . MYARCADE_VERSION . '" />' . "\n";
}

function myarcade_comment() {
  echo "\n"."<!-- Powered by MyArcadePlugin Lite - http://myarcadeplugin.com -->"."\n";
}