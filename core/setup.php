<?php
/*
Module:       This modul contains MyArcadePlugin setup functions
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/

defined('MYARCADE_VERSION') or die();

/**
 * @brief Plugin installation. Adds needed tables
 */
function myarcade_install() {
  global $wpdb, $wp_version;
  
  $collate = '';

  if (version_compare($wp_version, '3.5', '>=') ) {
    if ( $wpdb->has_cap( 'collation' ) ) {
      if( ! empty($wpdb->charset ) )
        $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
      if( ! empty($wpdb->collate ) )
        $collate .= " COLLATE $wpdb->collate";
    }
  }
  else {
    if( $wpdb->supports_collation() ) {
      if( !empty($wpdb->charset) )
        $collate = "DEFAULT CHARACTER SET ".$wpdb->charset;
      if( !empty($wpdb->collate) )
        $collate.= " COLLATE ".$wpdb->collate;
    }
  }

  // Check if games table exisits
  if ($wpdb->get_var("show tables like '".MYARCADE_GAME_TABLE."'") != MYARCADE_GAME_TABLE) {

    // Create new games table
    $sql = "CREATE TABLE `".MYARCADE_GAME_TABLE."` (
      `id` int(11) NOT NULL auto_increment,
      `postid` text collate utf8_unicode_ci NOT NULL,
      `uuid` text collate utf8_unicode_ci NOT NULL,
      `game_tag` text collate utf8_unicode_ci NOT NULL,
      `game_type` text collate utf8_unicode_ci NOT NULL,
      `name` text collate utf8_unicode_ci NOT NULL,
      `slug` text collate utf8_unicode_ci NOT NULL,
      `categories` text collate utf8_unicode_ci NOT NULL,
      `description` text collate utf8_unicode_ci NOT NULL,
      `tags` text collate utf8_unicode_ci NOT NULL,
      `instructions` text collate utf8_unicode_ci NOT NULL,
      `controls` text collate utf8_unicode_ci NOT NULL,
      `rating` text collate utf8_unicode_ci NOT NULL,
      `height` text collate utf8_unicode_ci NOT NULL,
      `width` text collate utf8_unicode_ci NOT NULL,
      `thumbnail_url` text collate utf8_unicode_ci NOT NULL,
      `swf_url` text collate utf8_unicode_ci NOT NULL,
      `screen1_url` text collate utf8_unicode_ci NOT NULL,
      `screen2_url` text collate utf8_unicode_ci NOT NULL,
      `screen3_url` text collate utf8_unicode_ci NOT NULL,
      `screen4_url` text collate utf8_unicode_ci NOT NULL,
      `created` text collate utf8_unicode_ci NOT NULL,
      `leaderboard_enabled` text collate utf8_unicode_ci NOT NULL,
      `coins_enabled` text collate utf8_unicode_ci NOT NULL,
      `status` text collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`id`)
    ) $collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  // Check if the table needs to be upgraded..
  myarcade_upgrade_games_table();
  
  // Include the feed game categories
  $catfile = MYARCADE_CORE_DIR.'/feedcats.php';
  if ( file_exists($catfile) ) {
    global $feedcategories;
    @require_once($catfile);
  }
  else {
    wp_die('Required configuration file not found!', 'Error: MyArcadePlugin Activation');
  }
  
  $default_settings = MYARCADE_CORE_DIR.'/settings.php';
  if ( file_exists($default_settings) ) {
    global $myarcade_general_default, $myarcade_mochi_default;
    @require_once($default_settings);
  }
  else {
    wp_die('Required configuration file not found!', 'Error: MyArcadePlugin Activation');
  }
  
  // General Settings
  $myarcade_general = get_option('myarcade_general');
  
  if ( !$myarcade_general ) {
    update_option('myarcade_general', $myarcade_general_default);
  }
  else {
    // Upgrade General Settings if needed
    foreach ($myarcade_general_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_general) ) {
        $myarcade_general[$setting] = $val;
      }
    }
    update_option('myarcade_general', $myarcade_general);
  }  
  
  // Mochi Settings
  $myarcade_mochi = get_option('myarcade_mochi');
  
  if ( !$myarcade_mochi ) {
    update_option('myarcade_mochi', $myarcade_mochi_default);
  }
  else {
    // Upgrade General Settings if needed
    foreach ($myarcade_mochi_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_mochi) ) {
        $myarcade_mochi[$setting] = $val;
      }
    }    
    update_option('myarcade_mochi', $myarcade_mochi);
  }
  
  // Categories
  $myarcade_categories = get_option('myarcade_categories');
    
  if ( empty($myarcade_categories) ) {
    update_option('myarcade_categories', $feedcategories, '', 'no');
  }
  else {
    // Upgrade Categories if needed    
    for ($i = 0; $i < count($feedcategories); $i++) {
      foreach ($myarcade_categories as $old_cat) {
        if ($old_cat['Name'] == $feedcategories[$i]['Name']) {
          // Save Category Status and Mapping to the new array
          $feedcategories[$i]['Status']  = $old_cat['Status'];
          $feedcategories[$i]['Mapping'] = $old_cat['Mapping'];
          // Go to the next category
          break;
        }
      }
    }  
    
    update_option('myarcade_categories', $feedcategories);
  }
  
  // Check for upgrade to the new settings structure
  if ( !get_option('myarcade_version') ) {
    if ($wpdb->get_var("show tables like '".MYARCADE_SETTINGS_TABLE."'") == MYARCADE_SETTINGS_TABLE) {
      
      // An Old settings table exists..      
      $myarcade_settings  = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
  
      // Upgrade Mochi settings
      $myarcade_mochi = get_option('myarcade_mochi');
      $myarcade_mochi['publisher_id'] = $myarcade_settings->mochiads_id;
      $myarcade_mochi['secret_key']   = $myarcade_settings->mochi_skey;
      $myarcade_mochi['limit']        = intval($myarcade_settings->feed_games);
      $myarcade_mochi['tag']          = $myarcade_settings->tag;
      if ( $myarcade_settings->cron_active == 'Yes') { $myarcade_mochi['cron'] = true; } else { $myarcade_mochi['cron'] = false; }
      
      switch ($myarcade_settings->feed_cat) {
        case 'Premium':     $myarcade_mochi['special'] = 'premium_games'; break;
        case 'Coins':       $myarcade_mochi['special'] = 'coins_enabled'; break;
        case 'Featured':    $myarcade_mochi['special'] = 'featured_games'; break;
        case 'Leaderboard': $myarcade_mochi['special'] = 'leaderboard_enabled'; break;
        case 'All':         
        default:            $myarcade_mochi['special'] = ''; break;
      }
      
      if ( $myarcade_settings->global_scores == 'Yes') { $myarcade_mochi['global_score'] = true; } else { $myarcade_mochi['global_score'] = false; }
      
      update_option('myarcade_mochi', $myarcade_mochi);
      
      
      // Upgrade General Settings
      $myarcade_general = get_option('myarcade_general');
      if ($myarcade_settings->leaderboard_active == 'Yes') { $myarcade_general['scores'] = true; } else { $myarcade_general['scores'] = false; }
      if ($myarcade_settings->onlyhighscores == 'Yes') { $myarcade_general['highscores'] = true; } else { $myarcade_general['highscores'] = false; }
      $myarcade_general['posts'] = intval($myarcade_settings->publish_games);
      if ($myarcade_settings->publish_status == 'scheduled') $myarcade_settings->publish_status = 'future';
      $myarcade_general['status'] = $myarcade_settings->publish_status;
      $myarcade_general['schedule'] = $myarcade_settings->cron_interval;
      if ($myarcade_settings->download_thumbs == 'Yes') { $myarcade_general['down_thumbs'] = true; } else { $myarcade_general['down_thumbs'] = false; }
      if ($myarcade_settings->download_games == 'Yes') { $myarcade_general['down_games'] = true; } else { $myarcade_general['down_games'] = false; }
      if ($myarcade_settings->download_screens == 'Yes') { $myarcade_general['down_screens'] = true; } else { $myarcade_general['down_screens'] = false; }
      if ($myarcade_settings->delete_files == 'Yes') { $myarcade_general['delete'] = true; } else { $myarcade_general['delete'] = false; }
      if ($myarcade_settings->create_categories == 'Yes') { $myarcade_general['create_cats'] = true; } else { $myarcade_general['create_cats'] = false; }
      if ($myarcade_settings->first_cat == 'Yes') { $myarcade_general['firstcat'] = true; } else { $myarcade_general['firstcat'] = false; }
      
      $myarcade_general['interval'] = intval($myarcade_settings->schedule);      
      $myarcade_general['parent'] = $myarcade_settings->parent_category;
      if ($myarcade_settings->single_publish == 'Yes') { $myarcade_general['single'] = true; } else { $myarcade_general['single'] = false; }
      $myarcade_general['singlecat'] = intval($myarcade_settings->single_catid);
      $myarcade_general['max_width'] = intval($myarcade_settings->maxwidth);
      $myarcade_general['embed'] = $myarcade_settings->embed_flashcode;
      $myarcade_general['template'] = $myarcade_settings->template;
      if ($myarcade_settings->use_template == 'Yes') { $myarcade_general['use_template'] = true; } else { $myarcade_general['use_template'] = false; }
      if ($myarcade_settings->allow_user_post == 'Yes') { $myarcade_general['allow_user'] = true; } else { $myarcade_general['allow_user'] = false; }
      
      update_option('myarcade_general', $myarcade_general);
      
      // Upgrade Categories and Mapping
      $old_cat_settings = unserialize($myarcade_settings->game_categories);
      $feedcategories = get_option('myarcade_categories');
  
      for ($i = 0; $i < count($feedcategories); $i++) {
        foreach ($old_cat_settings as $old_cat) {
          if ($old_cat['Name'] == $feedcategories[$i]['Name']) {
            // Save Category Status and Mapping to the new array
            $feedcategories[$i]['Status']  = $old_cat['Status'];
            $feedcategories[$i]['Mapping'] = $old_cat['Mapping'];
            // Go to the next category
            break;
          }
        }
      }
            
      update_option('myarcade_categories', $feedcategories);
        
      // Remove the settings table after successfull upgrade
      $wpdb->query("DROP TABLE ".MYARCADE_SETTINGS_TABLE);
    }
    
    update_option('myarcade_version', MYARCADE_VERSION, '', 'no');    
  }  
  else {
    update_option('myarcade_version', MYARCADE_VERSION, '', 'no');
  }

  // Make also an update of post meta
  myarcade_upgrade_post_metas();      
}


/**
 * @brief Upgrades the games table
 */
function myarcade_upgrade_games_table() {
  global $wpdb;

  $gametable_cols = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_GAME_TABLE);

  if (!in_array('rating', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `rating` text collate utf8_unicode_ci NOT NULL
      AFTER `controls`
    ");
  }

  if (!in_array('game_tag', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `game_tag` text collate utf8_unicode_ci NOT NULL
      AFTER `uuid`
    ");
  }

  $gametable_cols = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_GAME_TABLE);

  if (!in_array('postid', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `postid` text collate utf8_unicode_ci NOT NULL
      AFTER `id`
    ");
  }

  if (!in_array('screen1_url', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `screen1_url` text collate utf8_unicode_ci NOT NULL
      AFTER `swf_url`
    ");
  }

  if (!in_array('screen2_url', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `screen2_url` text collate utf8_unicode_ci NOT NULL
      AFTER `screen1_url`
    ");
  }

  if (!in_array('screen3_url', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `screen3_url` text collate utf8_unicode_ci NOT NULL
      AFTER `screen2_url`
    ");
  }

  if (!in_array('screen4_url', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `screen4_url` text collate utf8_unicode_ci NOT NULL
      AFTER `screen3_url`
    ");
  }

  if (!in_array('coins_enabled', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `coins_enabled` text collate utf8_unicode_ci NOT NULL
      AFTER `leaderboard_enabled`
    ");
  }

  $gametable_cols = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_GAME_TABLE);

  if (!in_array('game_type', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `game_type` text collate utf8_unicode_ci NOT NULL
      AFTER `game_tag`
    ");
  }

  $gametable_cols = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_GAME_TABLE);

  if (!in_array('video_url', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `video_url` text collate utf8_unicode_ci NOT NULL
      AFTER `screen4_url`
    ");
  }

  if (!in_array('highscore_type', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `highscore_type` text collate utf8_unicode_ci NOT NULL
      AFTER `leaderboard_enabled`
    ");
  }
}


/**
 * @brief Updates the posts meta to avoid conflicts with All In One Seo
 *        and maybe other plugins
 */
function myarcade_upgrade_post_metas() {
  global $wpdb;
  
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_description'  WHERE meta_key = 'description'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_instructions' WHERE meta_key = 'instructions'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_height'       WHERE meta_key = 'height'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_width'        WHERE meta_key = 'width'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_swf_url'      WHERE meta_key = 'swf_url'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_thumbnail_url' WHERE meta_key = 'thumbnail_url'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_rating'       WHERE meta_key = 'rating'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_screen1_url'  WHERE meta_key = 'screen1_url'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_screen2_url'  WHERE meta_key = 'screen2_url'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_screen3_url'  WHERE meta_key = 'screen3_url'");
  $wpdb->query("UPDATE $wpdb->postmeta SET meta_key = 'mabp_screen4_url'  WHERE meta_key = 'screen4_url'");
}


function myarcade_plugin_update() {
  if ( get_option('myarcade_version') != MYARCADE_VERSION ) {
    myarcade_install();
  }
}
?>