<?php
/*
Module:       This modul defines MyArcadePlugin cron intervals
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/

/*
 * Copyright @ Daniel Bakovic - kontakt@netreview.de
 * Do not modify! Do not sell! Do not distribute! -
 * Check our license Terms!
 */

defined('MYARCADE_VERSION') or die();

global $myarcade_cron_intervals;

$myarcade_cron_intervals = apply_filters('myarcade_cron_intervals', array(
  '5minutes'  => array( 'interval' => 300, 'display' => __('5 Minutes',  MYARCADE_TEXT_DOMAIN) ),
  '10minutes' => array( 'interval' => 600, 'display' => __('10 Minutes', MYARCADE_TEXT_DOMAIN) ),
  '15minutes' => array( 'interval' => 900, 'display' => __('15 Minutes', MYARCADE_TEXT_DOMAIN) ),
  '30minutes' => array( 'interval' => 1800,'display' => __('30 Minutes', MYARCADE_TEXT_DOMAIN) ),
  'weekly'    => array( 'interval' => 604800, 'display' => __('Once Weekly', MYARCADE_TEXT_DOMAIN) )
));


/**
 * Exstends the WP cron function
 *
 * @param <type> $schedules
 * @return int
 */
function myarcade_extend_cron($schedules) {
  global $myarcade_cron_intervals;

  // Add MyArcadePlugin cron intervals
  foreach( $myarcade_cron_intervals as $key => $value ) {
    $schedules[$key] = $value;
  }

  return $schedules;
}

// Add the cron extend hook
add_filter('cron_schedules', 'myarcade_extend_cron');