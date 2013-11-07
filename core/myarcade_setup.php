<?php
/**
 * Install / Uninstall / Update Plugin
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2013, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Setup
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
      `postid` int(11),
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
    @require_once($catfile);
  }
  else {
    wp_die('Required configuration file not found!', 'Error: MyArcadePlugin Activation');
  }

  $default_settings = MYARCADE_CORE_DIR.'/settings.php';
  if ( file_exists($default_settings) ) {
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
    $myarcade_mochi['feed'] = $myarcade_mochi_default['feed'];
    update_option('myarcade_mochi', $myarcade_mochi);
  }

  // Kongregate Settings
  $myarcade_kongregate = get_option('myarcade_kongregate');

  if ( !$myarcade_kongregate ) {
    update_option('myarcade_kongregate', $myarcade_kongregate_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_kongregate_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_kongregate) ) {
        $myarcade_kongregate[$setting] = $val;
      }
    }
    update_option('myarcade_kongregate', $myarcade_kongregate);
  }

  // FlashGameDistribution Settings
  $myarcade_fgd = get_option('myarcade_fgd');

  if ( !$myarcade_fgd ) {
    update_option('myarcade_fgd', $myarcade_fgd_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_fgd_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_fgd) ) {
        $myarcade_fgd[$setting] = $val;
      }
    }
    update_option('myarcade_fgd', $myarcade_fgd);
  }

  // FreeGamesForYourWebsite Settings
  $myarcade_fog = get_option('myarcade_fog');

  if ( !$myarcade_fog ) {
    update_option('myarcade_fog', $myarcade_fog_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_fog_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_fog) ) {
        $myarcade_fog[$setting] = $val;
      }
    }
    update_option('myarcade_fog', $myarcade_fog);
  }

  // spilgames Settings
  $myarcade_spilgames = get_option('myarcade_spilgames');

  if ( empty($myarcade_spilgames) ) {
    update_option('myarcade_spilgames', $myarcade_spilgames_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_spilgames_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_spilgames) ) {
        $myarcade_spilgames[$setting] = $val;
      }
    }
    update_option('myarcade_spilgames', $myarcade_spilgames);
  }

  // MyArcadeFeed Settings
  $myarcade_myarcadefeed = get_option('myarcade_myarcadefeed');

  if ( empty($myarcade_myarcadefeed) ) {
    update_option('myarcade_myarcadefeed', $myarcade_myarcadefeed_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_myarcadefeed_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_myarcadefeed) ) {
        $myarcade_myarcadefeed[$setting] = $val;
      }
    }

    // Insert MyArcadePlugin Feed
    $empty_key = FALSE;
    $myarcade_feed_found = FALSE;
    foreach ( $myarcade_myarcadefeed as $key => $val ) {
      // Find the first empty key
      if ( !$empty_key && empty($val) ) {
        $empty_key = $key;
      }
      if ( strpos( $val, 'games.myarcadeplugin.com') !== FALSE ) {
        $myarcade_feed_found = TRUE;
      }
    }

    if ( ! $myarcade_feed_found && $empty_key ) {
      $myarcade_myarcadefeed[ $empty_key ] = $myarcade_myarcadefeed_default['feed1'];
    }

    // Update MyArcadeFeed Settings
    update_option('myarcade_myarcadefeed', $myarcade_myarcadefeed);
  }

  // Big Fish Games Settings
  $myarcade_bigfish = get_option('myarcade_bigfish');

  if ( !$myarcade_bigfish ) {
    update_option('myarcade_bigfish', $myarcade_bigfish_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_bigfish_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_bigfish) ) {
        $myarcade_bigfish[$setting] = $val;
      }
    }
    update_option('myarcade_bigfish', $myarcade_bigfish);
  }

  // Scirra Settings
  $myarcade_scirra = get_option('myarcade_scirra');

  if ( !$myarcade_scirra ) {
    update_option('myarcade_scirra', $myarcade_scirra_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_scirra_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_scirra) ) {
        $myarcade_scirra[$setting] = $val;
      }
    }
    update_option('myarcade_scirra', $myarcade_scirra);
  }

  // GameFeed Settings
  $myarcade_gamefeed = get_option('myarcade_gamefeed');

  if ( !$myarcade_gamefeed ) {
    update_option('myarcade_gamefeed', $myarcade_gamefeed_default, '', 'no');
  }
  else {
    // Upgrade Settings if needed
    foreach ($myarcade_gamefeed_default as $setting => $val) {
      if ( !array_key_exists($setting, $myarcade_gamefeed) ) {
        $myarcade_gamefeed[$setting] = $val;
      }
    }
    update_option('myarcade_gamefeed', $myarcade_gamefeed);
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
    // version information exists.. regular upgrade
    if ( get_option('myarcade_version') != MYARCADE_VERSION ) {
      set_transient('myarcade_settings_update_notice', true, 60*60*24*30 ); // 30 days
    }
    update_option('myarcade_version', MYARCADE_VERSION);
  }

   // Make also an update of post meta
  myarcade_upgrade_post_metas();

  myarcade_create_directories();
}

/**
 * @brief Upgrades the games table
 */
function myarcade_upgrade_games_table() {
  global $wpdb;

  // Upgrade to 1.8
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

  // Update to 2.0
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

  // Upgrade to 2.60
  $gametable_cols = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_GAME_TABLE);

  if (!in_array('game_type', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_GAME_TABLE."`
      ADD `game_type` text collate utf8_unicode_ci NOT NULL
      AFTER `game_tag`
    ");
  }

  // Upgrade to 4.00
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

  // Upgrade to 5.80
  $wpdb->query("ALTER TABLE `".MYARCADE_GAME_TABLE."` CHANGE  `postid`  `postid` INT( 11 )");
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


/**
 * Load default settings
 */
function myarcade_load_default_settings() {

  $default_settings = MYARCADE_CORE_DIR.'/settings.php';
  if ( file_exists($default_settings) ) {
    @include_once($default_settings);
  }
  else {
    wp_die('Required configuration file not found!', 'Error: MyArcadePlugin Activation');
  }

  update_option('myarcade_general', $myarcade_general_default);
  update_option('myarcade_mochi', $myarcade_mochi_default);
  update_option('myarcade_kongregate', $myarcade_kongregate_default);
  update_option('myarcade_fgd', $myarcade_fgd_default);
  update_option('myarcade_fog', $myarcade_fog_default);
  update_option('myarcade_spilgames', $myarcade_spilgames_default);
  update_option('myarcade_myarcadefeed', $myarcade_myarcadefeed_default);

  // Include the feed game categories
  $catfile = MYARCADE_CORE_DIR.'/feedcats.php';
  if ( file_exists($catfile) ) {
    @include_once($catfile);
  }
  else {
    wp_die('Required configuration file not found!', 'Error: MyArcadePlugin Activation');
  }

  update_option('myarcade_categories', $feedcategories, '', 'no');
}


/**
 * Uninstall MyArcadePlugin.
 *
 * @global <type> $wpdb
 */
function myarcade_uninstall() {}

/**
 * Display settings update notice
 */
function myarcade_plugin_update_notice() {
  // Avoid message displaying when settings have been saved
  if ( isset($_POST['feedaction']) && $_POST['feedaction'] == 'save' ) {
    return;
  }
  ?>
  <div style="border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;background:#FEB1B1;border:1px solid #FE9090;color:#820101;font-size:14px;font-weight:bold;height:auto;margin:30px 15px 15px 0px;overflow:hidden;padding:4px 10px 6px;line-height:30px;">
    MyArcadePlugin was just updated / installed - Please visit the <a href="admin.php?page=myarcade-edit-settings">Plugin Options Page</a> and setup the plugin!
  </div>
  <?php
}