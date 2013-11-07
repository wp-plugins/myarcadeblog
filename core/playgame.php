<?php
/**
 * Game Preview Module for MyArcadePlugin Pro
 *
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2013, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Game
 */

$root = dirname( dirname( dirname( dirname( dirname(__FILE__)))));
if ( file_exists($root . '/wp-load.php') ) {
  define('MYARCADE_DOING_ACTION', true);
  require_once($root . '/wp-load.php');
}

// Check user privilege
if ( function_exists('current_user_can') ) {
  if ( !current_user_can('manage_options') ) {
    die();
  }
}
else {
  // Can't check user rights..
  die();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  </head>
  <body style = "margin:0px !important;text-align:center;background-color: #222222;">
    <?php
    if ( isset($_GET['gameid']) ) {
      echo get_game($_GET['gameid'], false, true);
    }
    ?>
  </body>
</html>