<?php
/* 
Plugin Name:  MyArcadePlugin Lite
Plugin URI:   http://myarcadeplugin.com
Description:  Turn your wordpress blog into an arcade game portal.
Version:      2.20
Author:       Daniel Bakovic
Author URI:   http://netreview.de
*/

/*
  Copyright 2009-2011  @ Daniel Bakovic (email : contact@myarcadeplugin.com)

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
define('MYARCADE_VERSION', '2.20');

// You need at least PHP Version 5.2.0+ to run this plugin
define('MYARCADE_PHP_VERSION', '5.2.0');

if ( ! defined( 'WP_PLUGIN_URL' ) ) {
  define( 'WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins' );
}

// Define needed table constants
global $wpdb;
define('MYARCADE_GAME_TABLE',      $wpdb->prefix.'myarcadegames');
define('MYARCADE_SETTINGS_TABLE',  $wpdb->prefix.'myarcadesettings');

// Direcories
define('MYARCADE_GAMES_DIR',  'wp-content/games/');
define('MYARCADE_THUMBS_DIR', 'wp-content/thumbs/');

// Define the translation text domain
define('MYARCADE_TEXT_DOMAIN', 'myarcadeblog');

define('MYARCADE_LOCKED_IMG', '<img src="'.WP_CONTENT_URL.'/plugins/myarcadeblog/images/locked.png" alt="Pro Version Only!"> </a>');

/**
 *******************************************************************************
 *   H O O K S
 *******************************************************************************
 */
  /* Add the main menu to blog */
add_action('admin_menu', 'arcadelite_admin_menu');
  /* Add game delete function */
add_action('delete_post', 'arcadelite_delete_game');
  /* Add some Ajax features */
add_action('admin_menu', 'arcadelite_load_ajax', 1);
add_action('wp_ajax_arcadelite_handler', 'arcadelite_handler');
  /* Add Content Filter For Auto Embed Flash */
add_filter('the_content', 'arcadelite_embed_handler', 99);
//add_action('wp_head', 'arcadelite_meta');
  /* Extend the WP Bar */
//add_action( 'admin_bar_menu', 'arcadelite_bar_menu', 1000 );
  
  
  /* Register install function */
register_activation_hook( __FILE__, 'arcadelite_install' ); 


/**
@todo - bug !
*/
/*
function arcadelite_bar_menu() {
  global $wp_admin_bar, $wpdb;

  if ( !is_super_admin() || !is_admin_bar_showing() ) {
    return;
  }
  
  $id = 'arcadelite_bar';
  $url =  get_option('siteurl').'/wp-admin/admin.php?page=';

  // Add the main siteadmin menu item
  $wp_admin_bar->add_menu( array( 'id'      => $id, 'title' => 'MyArcade',            'href' => FALSE ) );
  $wp_admin_bar->add_menu( array( 'parent'  => $id, 'title' => 'Fetch Mochi Games',    'href' => $url.'arcadelite-feed-games' ) );
  $wp_admin_bar->add_menu( array( 'parent'  => $id, 'title' => 'Fetch HeyZap Games',   'href' => $url.'arcadelite-feed-heyzap' ) );
  $wp_admin_bar->add_menu( array( 'parent'  => $id, 'title' => 'Publish Games',       'href' => $url.'arcadelite-add-games-to-blog' ) );
  $wp_admin_bar->add_menu( array( 'parent'  => $id, 'title' => 'Import Games',        'href' => $url.'arcadelite-import-games' ) );
  $wp_admin_bar->add_menu( array( 'parent'  => $id, 'title' => 'Manage Games',        'href' => $url.'arcadelite-manage-games' ) );
  $wp_admin_bar->add_menu( array( 'parent'  => $id, 'title' => 'Settings',            'href' => $url.'arcadelite-edit-settings' ) );  
}
*/

function myarcade_get_leaderboard_code() {
  return false;
}


if (function_exists('load_plugin_textdomain')) {
  load_plugin_textdomain(MYARCADE_TEXT_DOMAIN, WP_PLUGIN_DIR.'/myarcadeblog/lang', '/myarcadeblog/lang');
}

if (!function_exists('file_put_contents')) {
  function file_put_contents($filename, $data) {
    $f = @fopen($filename, 'w');
    if (!$f) {
      return false;
    } else {
      $bytes = fwrite($f, $data);
      fclose($f);
      return $bytes;
    }
  }
}

if (!function_exists('check_user_privilegs')) {
  function check_user_privilegs() {
    if (function_exists('current_user_can')) {
      if (current_user_can('manage_options')) {
        return true;
      }
    } else {
      global $user_level;
      
      get_currentuserinfo();
      
      if ($user_level >= 8) {
        return true;
      }
    }
    
    return false;
  }
}

function arcadelite_admin_menu() {

    add_menu_page('MyArcade', 'MyArcade', 'edit_posts' , __FILE__, 'arcadelite_show_stats', WP_CONTENT_URL . '/plugins/myarcadeblog/images/arcade.png');
               
    add_submenu_page( __FILE__,
                      __("Fetch Mochi Games", MYARCADE_TEXT_DOMAIN),
                      __("Fetch Mochi Games", MYARCADE_TEXT_DOMAIN),
                      'manage_options', 'arcadelite-feed-games', 'arcadelite_feed_games');
                      
    add_submenu_page( __FILE__,
                      __("Fetch HeyZap Games"),
                      __("Fetch HeyZap Games"),
                      'manage_options', 'arcadelite-feed-heyzap', 'arcadelite_feed_heyzap');
                      
    add_submenu_page( __FILE__,
                      __("Publish Games", MYARCADE_TEXT_DOMAIN),
                      __("Publish Games", MYARCADE_TEXT_DOMAIN),
                      'manage_options', 'arcadelite-add-games-to-blog',  'arcadelite_add_games_to_blog');
                      
    add_submenu_page( __FILE__,
                      __("Import Games", MYARCADE_TEXT_DOMAIN),
                      __("Import Games", MYARCADE_TEXT_DOMAIN),
                      'edit_posts', 'arcadelite-import-games', 'arcadelite_import_games');

      
    add_submenu_page( __FILE__,
                      __("Manage Games", MYARCADE_TEXT_DOMAIN),
                      __("Manage Games", MYARCADE_TEXT_DOMAIN),
                      'manage_options', 'arcadelite-manage-games', 'arcadelite_manage_games');
     
                      
    add_submenu_page( __FILE__,
                      __("Settings"),
                      __("Settings"),
                      'manage_options', 'arcadelite-edit-settings', 'arcadelite_edit_settings');
}

function arcadelite_load_ajax() {
  // jQuery
  wp_enqueue_script('jquery');

  // Thickbox
  wp_enqueue_script('thickbox');
  $thickcss = get_option('siteurl')."/".WPINC."/js/thickbox/thickbox.css";
  wp_enqueue_style('thickbox_css', $thickcss, false, false, 'screen');

  // Add MyArcade CSS
  $css = WP_PLUGIN_URL."/myarcadeblog/myarcadeblog.css";
  wp_enqueue_style('arcadelite_css', $css, false, false, 'screen');
}

function arcadelite_del_file($dir_p, $file_p) {
  if ( file_exists($dir_p.$file_p) && is_writable($dir_p) ) {
      @unlink($dir_p.$file_p);
  }
}

function arcadelite_delete_game($post_ID) {
  global $wpdb;
   
  // Get post
  $post = get_post($post_ID);
  
  // Get settings
  $arcadelite_settings  = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
  
  // Should game files be deleted
  if ($arcadelite_settings->delete_files == 'Yes') {
    // Delete game thumb if exists
    $thumburl = get_post_meta($post_ID, "mabp_thumbnail_url", true);
    
    if ($thumburl) {
      arcadelite_del_file(ABSPATH.MYARCADE_THUMBS_DIR, basename($thumburl));
    }
        
    // Delete game swf if exists
    $gameurl = get_post_meta($post_ID, "mabp_swf_url", true);
    
    if ($gameurl) {
      arcadelite_del_file(ABSPATH.MYARCADE_GAMES_DIR, basename($gameurl));
    }
  } // END if delete files
  
  
  // Set game status to deleted
  $query = "UPDATE `".MYARCADE_GAME_TABLE."` SET
           `status` = 'deleted',
           `postid` = ''
           WHERE `postid` = '$post_ID'";
           
  $wpdb->query($query);
}

function arcadelite_header() {
?>  
  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery("#advanced_settings").hide();
      jQuery("h2.trigger").click(function(){
        jQuery(this).toggleClass("active").next().slideToggle("slow");
      });
    });
  </script>  

<?php
  echo '<div class="wrap">';
  ?><p style="margin-top: 10px"><img src="<?php echo WP_PLUGIN_URL . '/myarcadeblog/images/logo.png'; ?>" alt="MyArcadePlugin Lite" /></p><?php
}

function arcadelite_footer() {
  ?>
  <div class="clear"></div>
  <p class="mabp_info" style="padding:5px;width:790px"><?php echo MYARCADE_LOCKED_IMG; ?> 
  MyArcadePlugin Lite is a fully functional but limited version of our <a href='http://myarcadeplugin.com' title='MyArcadePlugin Pro'>MyArcadePlugin Pro</a> plugin. Upgrade to enable all the premium features, to get support and a lot of bonuses at our support forum.</p>
  </div>
  <?php
}


/**
 * @brief Shows the overview page in WordPress backend
 */
function arcadelite_show_stats() {
  global $wpdb, $menu;

  arcadelite_header();
  

  $new_games = 0;

  $unpublished_games  = $wpdb->get_var("SELECT COUNT(*) FROM ".MYARCADE_GAME_TABLE." WHERE status = 'new'");
  $arcadelite_settings  = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
  
  if ($unpublished_games > 0) {
    $publish_games = __("Add Games to Blog", MYARCADE_TEXT_DOMAIN);
    $my_message =  '<br /><a href="?page=arcadelite-add-games-to-blog" class="button-primary">'.$publish_games.'</a>';
  }
  else {
    $unpublished_games = 0;
    $my_message  =  '<p class="mabp_error">'.__("You have <strong>NO</strong> unpublished games!", MYARCADE_TEXT_DOMAIN).'</p>';
    $my_message .=  '<a href="?page=arcadelite-feed-games" title='.__("Feed games", MYARCADE_TEXT_DOMAIN).'" class="button-primary">'.__("Feed games", MYARCADE_TEXT_DOMAIN).'</a></p>';
  }
  
  ?>
    
    <h2><?php _e("Overview", MYARCADE_TEXT_DOMAIN); ?></h2>  
                 
      <table class="widefat">
      <thead>
        <tr> 
          <th scope="col" class="manage-column column-title"><?php _e("Unpublished Games", MYARCADE_TEXT_DOMAIN); ?></th>
          <th scope="col" class="manage-column column-title"><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></th>
          <th scope="col" class="manage-column column-title"><?php _e("Publish Status", MYARCADE_TEXT_DOMAIN); ?></th>
          <th scope="col" class="manage-column column-title"><?php _e("Download Thumbs", MYARCADE_TEXT_DOMAIN); ?></th>
          <th scope="col" class="manage-column column-title"><?php _e("Download Games", MYARCADE_TEXT_DOMAIN); ?></th>
          <th scope="col" class="manage-column column-title"><?php _e("Download Screens", MYARCADE_TEXT_DOMAIN); ?></th>
          <th scope="col" class="manage-column column-title"><?php _e("Cron Active", MYARCADE_TEXT_DOMAIN); ?></th>
          <th scope="col" class="manage-column column-title"><?php _e("Leaderboard Active", MYARCADE_TEXT_DOMAIN); ?></th>
        </tr>
      </thead>
      <tr>
        <td scope="col"><?php echo $unpublished_games; ?></td>
        <td scope="col"><?php echo $arcadelite_settings->publish_games;?></td>
        <td scope="col"><?php echo $arcadelite_settings->publish_status;?></td>
        <td scope="col"><?php echo $arcadelite_settings->download_thumbs;?></td>
        <td scope="col"><?php echo $arcadelite_settings->download_games;?></td>
        <td scope="col"><?php echo MYARCADE_LOCKED_IMG; ?></td>
        <td scope="col"><?php echo MYARCADE_LOCKED_IMG; ?></td>
        <td scope="col"><?php echo MYARCADE_LOCKED_IMG; ?></td>
    </tr>
     
    </table>
  <?php
  echo $my_message;
  ?>
    <br /><br /><br />
    <h2 class="box"><?php _e("Additional Informations", MYARCADE_TEXT_DOMAIN); ?></h2>  
    <div id="myabp_import">
    <div class="container">
      <div class="block">
        <table class="optiontable" width="100%">
          <tr>
            <td colspan="2">
              <h3>MyArcadePlugin Pro Affiliate Program</h3>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              With MyArcadePlugin Pro affiliate program you can be a part of our success. You will earn up to <strong>30%</strong> commission on any sale you refer!<br /><br /><a href="https://www.e-junkie.com/affiliates/?cl=110247&ev=8e59cd720b" title="MyArcadePlugin Addiliate Programm">Join the MyArcadePlugin affiliate program</a>, promote MyArcadePlugin Pro and earn extra money!
            </td>
          </tr> 
                 
          <tr>
            <td colspan="2"><h3 style="margin-top: 15px">Support Us</h3></td>
          </tr>
          <tr>
            <td colspan="2">If you like MyArcadePlugin, please check the Facebook like button: 
            <iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FMyArcadePlugin%2F178161832232562&amp;layout=button_count&amp;show_faces=false&amp;width=200&amp;action=like&amp;font=verdana&amp;colorscheme=dark&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:200px; height:21px;" allowTransparency="true"></iframe></td>
          </tr>
          
          <tr>
            <td colspan="2"><h3 style="margin-top: 15px">Support</h3></td>
          </tr>
          <tr>
            <td colspan="2">For support and feature request please visit our support forums: <a href="http://myarcadeplugin.com/support/" title="Support Forum">Support Forums</a></td>
          </tr>          
          
          <tr>
            <td colspan="2">
              <h3 style="margin-top: 15px">Premium Arcade Themes</h3>
            </td>
          </tr>
          <tr>
          <td>
            <img src="<?php echo WP_PLUGIN_URL; ?>/myarcadeblog/images/myarcadeblogthemes.png"" alt="MyArcadeBlogThemes" />
          </td>
          <td>
            Need more themes for your Arcade Site? Check <a href="http://myarcadeblogthemes.com" target="_blank" title="Themes For MyArcadePlugin Pro">MyArcadeBlogThemes</a> by <a href="http://powerfusion.net" title="Powerfusion Internet Services">Powerfusion</a> to get premium arcade themes for MyArcadePlugin Pro. 
            </td>
          </tr>
          
          <tr>
            <td colspan="2">
              <h3 style="margin-top: 15px">MyArcade Traffic Exchange Network</h3>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              Join our Banner / Traffic Exchange Network to boost your traffic and to increase the popularity of your site. You will receive 10.000 banner impressions on register for FREE!  
              <br /><br />
              <center><a href="http://exchange.myarcadeplugin.com" target="_blank" title="MyArcade Traffic Exchange Network"> MyArcade Traffic / Banner Exchange Network</a></center> 
            </td>
          </tr>          
        </table>            
      </div>
    </div>  
    </div>
    
  <?php 
  arcadelite_footer();
}

/**
 * @brief Shows the settings page and handels all setting changes 
 */
function arcadelite_edit_settings() {
  global $wpdb;
    
  arcadelite_header();
       
  // Init variables  
  $publishposts       = '';
  $pendingreview      = '';
  $scheduled          = '';
  $embed_manually     = '';
  $embed_top          = '';
  $embed_bottom       = '';

  
  if ( isset($_POST['feedaction']) ) $action = $_POST['feedaction']; else $action = '';

  if ($action == 'save') { 
    // Get POST data
    $mochiurl         = trim($_POST['mochiurl']);
    $mochiid          = trim($_POST['mochiid']);
    $mochi_secret     = trim($_POST['mochiskey']);
    $feedcount        = trim($_POST['feed_count']);
    $feed_cat         = trim($_POST['feedcat']);
    $gamecount        = trim($_POST['game_count']);
    $publishstatus    = $_POST['publishstatus'];
    $schedtime        = trim($_POST['schedtime']);
    $downloadthumbs   = $_POST['downloadthumbs'];
    $downloadgames    = $_POST['downloadgames'];
    $downloadscreens  = '';
    $categories_post  = $_POST['gamecats'];
    $create_game_cats = $_POST['createcats'];
    $delete_gamefiles = $_POST['deletefiles'];
    $maxwidth         = trim($_POST['maxwidth']);
		$cronenable       = '';
    $croninterval     = '';
    $leaderboardenable = '';
    $onlyhighscores   = '';
    $global_scores    = '';
    $guest_scores     = '';
    $guest_prefix     = '';
    $singlecat        = '';
    $singlecatid      = '';
    $embed_flashcode  = $_POST['embedflashcode'];
    $use_template     = $_POST['usetemplate'];
    $post_template    = mysql_escape_string($_POST['post_template']);
    $parent_cat       = '';
    $first_cat        = '';
    $tag              = '';
    $allow_user       = '';
    
    // HeyZap Settings
    $heyzap = array();
    $heyzap_settings      = serialize($heyzap);    
    
    // checkbox-check
    if (empty($downloadgames))      { $downloadgames      = 'No';     }
    if (empty($downloadthumbs))     { $downloadthumbs     = 'No';     }
    if (empty($downloadscreens))    { $downloadscreens    = 'No';     }
    if (empty($cronenable))         { $cronenable         = 'No';     }
    if (empty($leaderboardenable))  { $leaderboardenable  = 'No';     }
    if (empty($onlyhighscores))     { $onlyhighscores     = 'No';     }
    if (empty($use_template))       { $use_template       = 'No';     }
    if (empty($global_scores))      { $global_scores      = 'false';  }
    if (empty($global_scores))      { $guest_scores       = 'false';  }
    if (empty($singlecat))          { $singlecat          = 'false';  }
    if (empty($first_cat))          { $first_cat          = 'false';  }
    if (empty($allow_user))         { $allow_user         = 'No';     }
       
    /** Category Management **/ 
    // Get current settings
    $arcadelite_settings  = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
    
    $feedcategories = unserialize($arcadelite_settings->game_categories);
    
    // count checked categories
    $cat_count = count($categories_post);
    $feedcat_count = count($feedcategories);
    
    if ($cat_count > 0) {
      for ($i = 0; $i < $feedcat_count; $i++) {
        foreach ($categories_post as $selected_cat) {
          if ( $feedcategories[$i]['Slug'] == $selected_cat) {
            $feedcategories[$i]['Status'] = 'checked';
            
            if ($create_game_cats == 'Yes') {
              // Get Cat ID
              $game_catID = get_cat_ID(htmlspecialchars($feedcategories[$i]['Name']));

              if ($game_catID == 0) {
                if ( empty($parent_cat) ) { $parent_cat = ''; }
                // Create
                $create_cat = array('cat_name' => $feedcategories[$i]['Name'], 
                                    'category_description' => $feedcategories[$i]['Name'],
                                    'category_nicename' => $feedcategories[$i]['Slug'],
                                    'category_parent' =>  $parent_cat
                                );

                if ( !wp_insert_category($create_cat) ) {
                  echo '<p class="mabp_error mabp_800">'.__("Failed to create category:", MYARCADE_TEXT_DOMAIN).' '.$feedcategories[$i]['Name'].'</p>';
                }
              }
            }

            break;
          }
          else {
            $feedcategories[$i]['Status'] = '';
          } 
        }
      }
    }
    else {
      echo '<p class="mabp_error mabp_800">'.__("You did not select any game categories!", MYARCADE_TEXT_DOMAIN).'</p>';
    }

    // Create a string from category array
    $categories_str = serialize($feedcategories);

    /* Special Categories */
    if ( empty($feed_cat) ) { $feed_cat = 'All'; }

    // Save settings
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET
          mochiads_url      ='$mochiurl',
          mochiads_id       ='$mochiid',
          mochi_skey        ='$mochi_secret',
          feed_games        ='$feedcount',
          feed_cat          ='$feed_cat',
          single_publish    ='$singlecat',
          single_catid      ='$singlecatid',
          first_cat         ='$first_cat',
          publish_games     ='$gamecount',
          publish_status    ='$publishstatus',
          download_thumbs   ='$downloadthumbs',
          download_games    ='$downloadgames',
          download_screens  ='$downloadscreens',
          delete_files      ='$delete_gamefiles',
          schedule          ='$schedtime',
          game_categories   ='$categories_str',
          create_categories ='$create_game_cats',
          parent_category   = '$parent_cat',
          cron_active       ='$cronenable',
          cron_interval     ='$croninterval',
          maxwidth          ='$maxwidth',
          leaderboard_active = '$leaderboardenable',
          onlyhighscores    = '$onlyhighscores',
          global_scores     ='$global_scores',
          guest_scores      ='$guest_scores',
          guest_prefix      ='$guest_prefix',
          embed_flashcode   ='$embed_flashcode',
          use_template      ='$use_template',
          template          ='$post_template',
          heyzap            ='$heyzap_settings',
          tag               ='$tag',
          allow_user_post   ='$allow_user'
    ");
    
    echo '<p class="mabp_info mabp_800">'.__("Your settings have been updated!", MYARCADE_TEXT_DOMAIN).'</p>';    
  } // END - if action
    
  $arcadelite_settings  = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
    
  // Check Radio-Buttons
  $downloadgames = '';
  if ($arcadelite_settings->download_games == 'Yes') {  
    if ( !file_exists(ABSPATH.MYARCADE_GAMES_DIR) ) {
      @mkdir(ABSPATH.MYARCADE_GAMES_DIR, 0777);
    }
  
    if (!is_writable(ABSPATH.MYARCADE_GAMES_DIR)) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The games directory '%s' must be writeable (chmod 777) in order to download games.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_GAMES_DIR).'</p>';
    }

    $downloadgames = 'checked';
  }
    
  $downloadthumbs = '';
  if ($arcadelite_settings->download_thumbs == 'Yes') {
    if (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR)) {
      echo '<p class="mabp_error">'.sprintf(__("The thumbails directory '%s' must be writeable (chmod 777) in order to download thumbnails.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
    }
    
    $downloadthumbs = 'checked';
  }
  
  $downloadscreens = '';


  switch ($arcadelite_settings->publish_status) {
    case 'scheduled':       $scheduled      = 'checked';  break;
    case 'publish':         $publishposts   = 'checked';  break;
    default:                $publishposts   = 'checked';  break;
  }
    
  // Special Categs
  $checkID = TRUE;
  switch ($arcadelite_settings->feed_cat) {
    case 'All':         $feed_all       = 'selected'; $checkID = FALSE; break;
    default:            $feed_all       = 'selected'; $checkID = FALSE; break;
  }

  // Check ID
  if ( ($checkID == TRUE) && empty($arcadelite_settings->mochiads_id) ) {
    echo '<p class="mabp_error">'.__("You have selected a special category but not entered your Mochi Publisher ID!", MYARCADE_TEXT_DOMAIN).'</p>';
  }
  
  // Auto embed flash code
  switch ($arcadelite_settings->embed_flashcode) {
    case 'manually':  $embed_manually = 'selected'; break;
    case 'top':       $embed_top = 'selected';      break;
    case 'bottom':    $embed_bottom = 'selected';   break;  
    default:          $embed_manually = 'selected'; break;
  }

  // Get all categories
  $categs_tmp = get_all_category_ids();  

  ?>
    <br />
    
    <div id="myarcade_settings">
      <form method="post" name="editsettings">
        <input type="hidden" name="feedaction" value="save">
        <?php // Mochi Settings ?>
        <h2 class="trigger"><?php _e("Mochi Settings", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="3">
                  <i>
                    <?php _e("To be able to use all of the Mochi Media's features, a Mochi account is requred.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="https://www.mochimedia.com/r/23f4b6b9ad680165" title="Register On Mochi">here</a> to create a new account.
                  </i>
                  <br /><br />
                </td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Mochi Media Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="mochiurl" value="<?php echo $arcadelite_settings->mochiads_url; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if Mochi Media Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="3"><h3><?php _e("Mochi Publisher ID", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="text" size="40"  name="mochiid" value="<?php echo $arcadelite_settings->mochiads_id; ?>" />
                </td>
                <td><i><?php _e("Paste here your Mochi 'Publisher ID'. You will find the 'Publisher ID' on the Mochi's site under settings.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Publisher Secret Key", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="mochiskey" value="<?php echo $arcadelite_settings->mochi_skey; ?>" />
                </td>
                <td><i><?php _e("Paste here your Mochi's Publisher Secret Key. This is required if you want to use the leaderboard feature. You'll find your Secret Key on the Mochi's site under settings.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3>Feed Games</h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="feed_count" value="<?php echo $arcadelite_settings->feed_games; ?>" />
                </td>
                <td><i><?php _e("How many Mochi games should be fetched when clicking 'Feed Mochi Games'? Leave blank if you want to feed all games (not recommended). This option only affects the manual game fetching. It is recommended to use values between 100 and 2000 to avoid server overload.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="3"><h3><?php _e("Filter by Tag", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="text" size="40"  name="premium1" value="" disabled />
                </td>
                <td><i><?php _e("You may choose to include games that include or exclude a tag. To exclude a tag you must preface it with a minus sign (-). For example: '-zh-cn' will exclude all chinese games or 'snow' will include only games tagged to snow.", MYARCADE_TEXT_DOMAIN); ?></i> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>              

              <tr><td colspan="3"><h3><?php _e("Mochi Auto Game Feeding (Cron)", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="premium34" value="" disabled /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch Mochi Media games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Special Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="feedcat" id="feedcat">
                    <option value="All" <?php echo $feed_all; ?> ><?php _e("All Games", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a special category if you want to feed only featured, coin enabled, leaderboeard or premium games. If you use this feature, you have to enter a valid Publisher ID.", MYARCADE_TEXT_DOMAIN); ?></i> <?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>

              <?php // Leaderboard settings ?>
              <tr><td colspan="3"><h3><?php _e("Enable Global Mochi Scores", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="premium2" value="" disabled /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to display global scores of a game and not just those submitted to your site.", MYARCADE_TEXT_DOMAIN); ?></i> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>

            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php // HeyZap settings ?>
        <h2 class="trigger"><?php _e("HeyZap Settings", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%">
             <tr>
                <td colspan="3">
                  <i>
                    <?php _e("To be able to use all of the HeyZap's features, a HeyZap account is requred.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://www.heyzap.com/" title="Register On HeyZap">here</a> to create a new account.
                  </i>
                  <br /><br />
                  <p class="mabp_info" style="padding:5px"><?php echo MYARCADE_LOCKED_IMG; ?> HeyZap Features are available on MyArcadePlugin Pro</p>
                </td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Site Key", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="premium3" value="" disabled />
                </td>
                <td><i><?php _e("Enter your HeyZap 'Site Key' that will identify your site. This is required if you want to feed more than 10 games at once.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Secret Key", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="premium4" value="" disabled />
                </td>
                <td><i><?php _e("Enter your HeyZap 'Secret Key'. This is required if you want to feed more than 10 games at once.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Feed Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="premium5" value="" disabled />
                </td>
                <td><i><?php _e('How many HeyZap games should be fetched when clicking "Feed HeyZap Games"? Enter a value between 0 and 1000.', MYARCADE_TEXT_DOMAIN); ?></i></tr>
              
              <tr><td colspan="3"><h3><?php _e("Game Variants", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="premium6" id="premium6">
                    <option value="-">-- Premium --</option>
                  </select>
                </td>
                <td><i><?php _e("HeyZap offers two kinds of games that you can add to your site: SWF and Embed-Code games. Select what game kinds do you want to fetch.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              

              <tr><td colspan="3"><h3><?php _e("HeyZap Auto Game Feeding (Cron)", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="premium7" value="" disabled /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch HeyZap games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>          
            </table>
          </div>
        </div>

        <?php // General Settings ?>
        <h2 class="trigger"><?php _e("General Settings", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%">
            
              <tr><td colspan="3"><h3><?php _e("Save User Scores", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="premium8" value="" disabled /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to show and store user scores. Only scores from Mochi and IBPArcade games will be saved.", MYARCADE_TEXT_DOMAIN); ?></i><br /><strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>
              
              <tr><td colspan="3"><h3><?php _e("Save Only Highscores", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="premium9" value="" disabled /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to save only the highest score that an user has achieved per game. Otherwise all submitted scores will be saved.", MYARCADE_TEXT_DOMAIN); ?></i><br /><strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>                          

              <tr><td colspan="3"><h3><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="game_count" value="<?php echo $arcadelite_settings->publish_games; ?>" />
                </td>
                <td><i><?php _e('How many games should be published when clicking on "Add Games To Blog"?', MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Post Status", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="radio" name="publishstatus" value="publish" <?php echo $publishposts; ?> /><label class="opt">&nbsp;<?php _e("Publish", MYARCADE_TEXT_DOMAIN); ?></label>
                  <input type="radio" name="publishstatus" value="scheduled" <?php echo $scheduled; ?> /><label class="opt">&nbsp;<?php _e("Scheduled", MYARCADE_TEXT_DOMAIN); ?></label>
                  <br /><br />
                  <?php _e("Schedule Time", MYARCADE_TEXT_DOMAIN); ?>: <input type="text" size="10" name="schedtime" value="<?php echo $arcadelite_settings->schedule; ?>">
                </td>
                <td><i><?php _e("Choose how the games should be added as new posts. If you whish to schedule game publishing, then set a time between two posts in minutes.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="3"><h3><?php _e("Download Thumbnails", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="downloadthumbs" value="Yes" <?php echo $downloadthumbs; ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Should the game thumnails be downloaded to your web server? Make sure that the thumb directory (wp-content/thumbs/) is writeable.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Download Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="downloadgames" value="Yes"  <?php echo $downloadgames; ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Should the games be downloaded to your web server? Make sure that the games directory (wp-content/games/) is writeable.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Download Screenshots", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="premium10" value=""  disabled /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Should the game screenshots be downloaded to your web server? Make sure that the thumb directory (wp-content/thumbs/) is writeable.", MYARCADE_TEXT_DOMAIN); ?></i>
                <br />
                  <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Delete Game Files", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="deletefiles" value="Yes" <?php if ($arcadelite_settings->delete_files == 'Yes') { echo 'checked'; } ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("This option will delete downloaded game files after deleting a game post from your blog. Make sure that the games and thumbs directories are writeable! Keep in mind that deleted games can not be re-published!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Auto Game Feeding (Cron)", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                   <select size="1" name="cronint" id="cronint">
                    <option value="-"><?php _e("Hourly", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="-"><?php _e("Twice Daily", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="-"><?php _e("Daily", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Choose a time interval, how often new games should be fetched. The fetching will trigger when someone visits your site, if the scheduled time has passed. On each trigger MyArcadePlugin Pro will get the latest game (one game per trigger).", MYARCADE_TEXT_DOMAIN); ?></i> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Game Categories To Feed", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                <?php
                  $feedcategories = unserialize($arcadelite_settings->game_categories);
                  
                  foreach ($feedcategories as $feedcat) {
                    echo '<input type="checkbox" name="gamecats[]" value="'.$feedcat['Slug'].'" '.$feedcat['Status'].' /><label class="opt">&nbsp;'.$feedcat['Name'].' '.$feedcat['Info'].'</label><br />';
                  }
                
                ?>
                </td>
                <td><i><?php _e("Choose Mochi and HeyZap game categories that should be fetched.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Create Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="createcats" value="Yes" <?php if ($arcadelite_settings->create_categories == 'Yes') { echo 'checked'; } ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to create selected categories on your blog.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              
              <tr><td colspan="3"><h3><?php _e("Parent Category", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
                <tr>
                  <td>
                    <select size="1" name="premium11" id="premium11">
                    <option value=''>--- <?php _e("No Parent Category", MYARCADE_TEXT_DOMAIN); ?> ---</option>
                    </select>
                  </td>
                  <td><i><?php _e("This option will create game categories as subcategories in the selected category.", MYARCADE_TEXT_DOMAIN); ?> <?php _e(" This option is usefull if you have a mixed site and not only a pure arcade site.", MYARCADE_TEXT_DOMAIN); ?></i>
                  <br />
                  <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
                </tr>  
                
                <?php // Use only the first category ?>
                <tr>
                  <td colspan="3">
                    <h3><?php _e("Use Only The First Category", MYARCADE_TEXT_DOMAIN); ?></h3>
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="checkbox" name="premium12" value="" disabled />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?>
                  </td>
                  <td><i><?php _e("Many Mochi and HeyZap games are tagged to more than one category. Activate this option to avoid game publishing in more than one category.", MYARCADE_TEXT_DOMAIN); ?></i>
                  <br />
                  <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
                </tr>
                
              <tr><td colspan="3"><h3><?php _e("Max. Game Width (optional)", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40" name="maxwidth" value="<?php echo $arcadelite_settings->maxwidth; ?>" />
                </td>
                <td><i><?php _e("Max. allowed game width in px. If set the function get_game will create an output code with adjusted game dimensions.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Publish In A Single Category", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="premium13" value=""  disabled />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?> 
                  <select size="1" name="premium14" id="premium14">
                  <option value="-" />-- PREMIUM ONLY --</option>
                  </select>
                </td>
                <td><i><?php _e("This option will publish all games only in the selected category.", MYARCADE_TEXT_DOMAIN); ?> <?php _e("This option is usefull if you have a mixed site and not only a pure arcade site.", MYARCADE_TEXT_DOMAIN); ?></i>
                <br />
                  <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Embedd Flash Code", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                <select size="1" name="embedflashcode" id="embedflashcode">
                  <option value="manually" <?php echo $embed_manually; ?> ><?php _e("Manually", MYARCADE_TEXT_DOMAIN); ?></option>
                  <option value="top" <?php echo $embed_top; ?> ><?php _e("At The Top Of A Game Post", MYARCADE_TEXT_DOMAIN); ?></option>
                  <option value="bottom" <?php echo $embed_bottom; ?> ><?php _e("At The Bottom Of A Game Post", MYARCADE_TEXT_DOMAIN); ?></option>
                </select>
                </td>
                <td><i><?php _e("Select if MyArcadePlugin Pro should auto embed the flash code in your game posts (only if you don't use FunGames theme).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Game Post Template", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="usetemplate" value="Yes" <?php if ($arcadelite_settings->use_template == 'Yes') { echo 'checked'; } ?> /><label class="opt">&nbsp;<?php _e("Activate Post Template", MYARCADE_TEXT_DOMAIN); ?></label>
                  <br /><br />
                  <textarea rows="12" cols="40" id="post_template" name="post_template"><?php echo htmlspecialchars(stripslashes($arcadelite_settings->template)); ?></textarea>
                </td>
                <td><i>
                    <?php _e("Use this template to style the output of MyArcadePlugin Pro when creating game posts (only if you don't use FunGames theme).", MYARCADE_TEXT_DOMAIN); ?>
                    <br />
                     <strong><?php _e("Available Variables", MYARCADE_TEXT_DOMAIN); ?>:</strong><br />
                    %THUMB% - <?php _e("Show the game thumbnail", MYARCADE_TEXT_DOMAIN); ?><br />
                    %DESCRIPTION% - <?php _e("Show game description", MYARCADE_TEXT_DOMAIN); ?><br />
                    %INSTRUCTIONS% - <?php _e("Show game instructions if available", MYARCADE_TEXT_DOMAIN); ?>

                  </i></td>
              </tr>
              
              <?php // Allow users to post games?>
              <tr>
                <td colspan="3">
                  <h3><?php _e("Allow Users To Post Games", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="checkbox" name="premium15" value="" disabled />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?>                  
                </td>
                <td><i><?php _e("Activate this if you want to give your users access to import games. WordPress supports following user roles: Contributor, Author and Editor. Games added by Contributors will be saved as drafts! Authors and Editors will be able to publish games.", MYARCADE_TEXT_DOMAIN); ?></i><br />
                  <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>              

            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>
        
        <?php // Category Mapping ?>
        <h2 class="trigger"><?php _e("Category Mapping", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%">
              <tr>
                <td colspan="4">
                  <i>
                    <?php _e("Map Mochi or HeyZap categories to your own category names. This feature allows you to publish games in translated or summarized categories instead of using the predefined category names. (optional)", MYARCADE_TEXT_DOMAIN); ?>
                  </i>
                  <br /><br />
                  <p class="mabp_info" style="padding:5px"><?php echo MYARCADE_LOCKED_IMG; ?> Category Mapping is available on MyArcadePlugin Pro</p>
                  <br /><br />
                </td>
              </tr>
             <tr>
              <td width="20%"><a name="mapcats"></a><strong><?php _e("Feed Category", MYARCADE_TEXT_DOMAIN); ?></strong></td>
              <td width="20%"><strong><?php _e("Blog Category", MYARCADE_TEXT_DOMAIN); ?></strong></td>
              <td width="20%"><strong><?php _e("Add Mapping", MYARCADE_TEXT_DOMAIN); ?></strong></td>
              <td><strong><?php _e("Current Mappings", MYARCADE_TEXT_DOMAIN); ?></strong></td>
             </tr>
             
            <?php
              $blog_category_ids = get_all_category_ids();
              $feedcategories = unserialize($arcadelite_settings->game_categories);
            
              foreach ($feedcategories as $feedcat) : 
            ?>
              <tr>
                <td><?php echo $feedcat['Name']; ?></td>
                <td>
                  <?php
                  $output  = '<select id="bcat_'.$feedcat['Slug'].'">';
                  $output .=  '<option value="0">---Select---</option>';
                  foreach ($categs_tmp as $cat_tmp_id) {
                    $output .= '<option value="'.$cat_tmp_id.'" />'.get_cat_name($cat_tmp_id).'</option>';
                  }
                  $output .= '</select>';
                  echo $output;
                  ?>
                </td>
                <td>
                  <div style="width:100px">
                  <div class="button-secondary" style="float:left;width:60px;text-align:center;" onclick="alert('This is a MyArcadePlugin Pro feature!');return false;">
                    Add
                  </div>
                  </div>
                </td>
                <td>none</td>
              </tr>
              <tr><td colspan="4"><HR></td></tr>
              <?php endforeach; ?>
              <tr>
              <td colspan="4">
                <i>
                  <?php _e("The changes in this section are saved automatically.", MYARCADE_TEXT_DOMAIN); ?>
                </i>
                <br /><br />
              </td>
            </tr>
            </table> 
          </div>
        </div>
        
      </form>
        
        <?php // Advanced Features ?>
        <h2 class="trigger"><?php _e("Advanced Features", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container" id="advanced_settings">
          <div class="block">
            <table class="optiontable" width="100%">
             <tr>
                <td colspan="3">
                  <i>
                    <?php _e("Please, use this only if you know what you do!", MYARCADE_TEXT_DOMAIN); ?>
                  </i>
                  <br /><br />
                </td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Delete All Feeded Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td width="160">
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="jQuery('#del_response').html('<div class=\'gload\'> </div>');jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>/admin-ajax.php', {action:'arcadelite_handler',func:'delgames'},function(data){jQuery('#del_response').html(data);});">
                    <?php _e("Reset Feeded Games", MYARCADE_TEXT_DOMAIN); ?>
                  </div>
                </td>
                <td width="30"><div id="del_response"></div></td>
                <td><i><?php _e("Attention! All feeded or imported games will be deleted from the games database! Published posts will not be touched. After this score submitting of publiished games will stop working!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Delete Blank / Zero Scores", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="alert('This is a MyArcadePlugin Pro feature!');return false;" >                  
                    <?php _e("Delete Zero Scores", MYARCADE_TEXT_DOMAIN); ?>
                  </div> 
                </td>
                <td width="30"><div id="zero_response"></div></td>
                <td><i><?php _e("Clean your scores table. This will delete all zero and empty scores if present in your database.", MYARCADE_TEXT_DOMAIN); ?></i><br />
                  <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>
              
              <tr><td colspan="3"><h3><?php _e("Delete All Scores", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="alert('This is a MyArcadePlugin Pro feature!');return false;">
                    <?php _e("Delete All Scores", MYARCADE_TEXT_DOMAIN); ?>
                  </div> 
                </td>
                <td width="30"><div id="score_response"></div></td>
                <td><i><?php _e("Attention! All saved scores will be deleted!", MYARCADE_TEXT_DOMAIN); ?></i><br />
                  <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
              </tr>
            </table>
          </div>
        </div>
        
      <div style="clear:both"></div>
    </div><?php // end id arcadelite_settings ?>

   <div class="clear"></div>
  <?php

  arcadelite_footer();
}


/**
 * @brief This function is for alternative download using cURL instead of  
 *        file_get_contents 
 */
function arcadelite_get_file_curl($url, $binary = false) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, $binary);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);

  $result = curl_exec($ch);
  
  curl_close($ch);

  return $result;
}


/**
 * @brief Download a file
 */
function arcadelite_get_file($url, $binary = false) {
        
  // Check for allow_url_open
  if (ini_get('allow_url_fopen')) {
    // Using file_get_contents
    if ($binary == true) {
      $file_data = file_get_contents($url, FILE_BINARY);
    }
    else {
      $file_data = file_get_contents($url);
    }
  }
  else {
    // Using cURL
    $file_data = arcadelite_get_file_curl($url, $binary);
  }
  
  return $file_data;
}

/**
 * @brief This function inserts a game to the games table
 */
function arcadelite_insert_game($game) {
  global $wpdb;
  
  // Put this game into games table
  $query = "INSERT INTO ".MYARCADE_GAME_TABLE." (
      postid,
      uuid,
      game_tag,
      game_type,
      name,
      slug,
      categories,
      description,
      tags,
      instructions,
      controls,
      rating,
      height,
      width,
      thumbnail_url,
      swf_url,
      screen1_url,
      screen2_url,
      screen3_url,
      screen4_url,
      video_url,
      created,
      leaderboard_enabled,
      highscore_type,
      coins_enabled,
      status
    ) values (
      '',
      '$game->uuid',
      '$game->game_tag',
      '$game->type',
      '$game->name',
      '$game->slug',
      '$game->categs',
      '$game->description',
      '$game->tags',
      '$game->instructions',
      '$game->control',
      '$game->rating',
      '$game->height',
      '$game->width',
      '$game->thumbnail_url',
      '$game->swf_url',
      '$game->screen1_url',
      '$game->screen2_url',
      '$game->screen3_url',
      '$game->screen4_url',
      '$game->video_url',
      '$game->created',
      '$game->leaderboard_enabled',
      '$game->highscore_type',
      '$game->coins_enabled',
      '$game->status')";

      $wpdb->query($query);
}

/**
 * @brief Check for json support
 */
function arcadelite_check_json($echo) {

  $result = true;

  if (!function_exists('json_decode')) {
     $phpversion = phpversion();
    
    if ($echo) {
      if($phpversion < MYARCADE_PHP_VERSION) {
        echo '<font style="color:red;">
             '.sprintf(__("You need at least PHP %s to run this plugin.", MYARCADE_TEXT_DOMAIN), MYARCADE_PHP_VERSION).'
             <br />
             '.sprintf(__("You have %s installed.", MYARCADE_TEXT_DOMAIN), $phpversion).'
             <br />
             '.__("Contact your administrator to update your PHP version.", MYARCADE_TEXT_DOMAIN).'
             </font><br /><br />';
      }
      else {
        echo '<font style="color:red;">'.__("JSON Support is disabeld in your PHP configuration. Please contact your administrator to activate JSON Support.", MYARCADE_TEXT_DOMAIN).'</font><br /><br />';
      }
    }
    
    $result = false;
  }
  
  return $result;
}

/**
 * @brief Feeds HeyZap games
 */
function arcadelite_feed_heyzap($cronfeeding = false) {
    arcadelite_header();
    echo '<h3>'.__("Fetch HeyZap Games", MYARCADE_TEXT_DOMAIN).'</h3>';
    ?>
    <br />
    <br />
    <?php   
    arcadelite_footer();
} // END arcadelite_feed_heyzap


/**
 * @brief Gets a feed from mochiads and adds new games into the games table 
 */
function arcadelite_feed_games($MochiGameTag = '', $cronfeeding = false) {
  global $wpdb;

  $new_games = 0;
  $add_game = false;
  
  
  // Check if we should print out messages...
  if ( !empty($MochiGameTag) || ($cronfeeding == true) ) { 
    $echo = false; 
  } else { 
    $echo = true; 
  }
  
  if ($echo == true) {
    arcadelite_header();
    arcadelite_prepare_environment();
  }

  $arcadelite_settings = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);

  $feedcategories = unserialize($arcadelite_settings->game_categories);

  $feed_format ='?format=json';
    
  // Check if there is a feed limit. If not, feed all games
  if ($arcadelite_settings->feed_games > 0) {
    $limit = '&limit='.$arcadelite_settings->feed_games;
  } else $limit = '';
   
    
  if ($echo) { echo '<h3>'.__("Fetch Mochi Games", MYARCADE_TEXT_DOMAIN).'</h3>'; }
  
  
  // Show the form for offset feeding
  if ($echo == true) {
  ?>
    <div class="offset">
    <p>
        <form method="post">
      <p><strong>Offset Feeding: </strong> 
        Feed <input type="text" name="123" size="4" value="100" disabled /> games from offset <input type="text" name="321" size="4" value="100" disabled />. Filter by Tag: <input type="text" name="546" size="10" value="" disabled />. <?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature
      </p>
    </form>
    </p>    
    </div>
  <?php
  }

  // Generate the Mochi Feed URL
  $mochi_feed = trim($arcadelite_settings->mochiads_url)
              . trim($arcadelite_settings->mochiads_id)
              . $feed_format
              . $limit;
    
  //====================================
  // Check if json_decode exisits
  if ( !arcadelite_check_json($echo) ) {
    // Show Footer
    arcadelite_footer();
    
    return;
  }

  //====================================
  // Show the Feed URL
  if ($echo) {
   ?>
   <p class="mabp_info">
    <?php echo __("Your Feed URL:", MYARCADE_TEXT_DOMAIN)." <a href='".$mochi_feed."'>".$mochi_feed."</a>"; ?>
   </p>
   
    <p class="mabp_info">
    <?php 
    _e("Downloading feed: ", MYARCADE_TEXT_DOMAIN); 
  }
  
  $feed = arcadelite_get_file($mochi_feed, false);
  
  // Check, if we got a Error-Page  
  if (!strncmp($feed, "<!DOCTYPE", 9)) {
    if ($echo) {
      echo '<font style="color:red;">'.__("Feed not found. Please check Mochi Feed URL and your Publisher ID!", MYARCADE_TEXT_DOMAIN).'</font></p>';
      arcadelite_footer();
    }
    
    return;
  }
  
  if ($feed) {
    if ($echo)
      echo '<font style="color:green;">'.__("OK", MYARCADE_TEXT_DOMAIN).'</font></p>';
  }
  else {
    if ($echo) {
      echo '<font style="color:red;">'.__("Can't download feed!", MYARCADE_TEXT_DOMAIN).'</font></p>';    
      arcadelite_footer();
    }
    
    return;
  }

  //====================================
  if ($echo) { 
    ?><p class="mabp_info"><?php
    _e("Decode feed: ", MYARCADE_TEXT_DOMAIN); 
  }
  
  $json_games = json_decode($feed);

  if ($json_games) {
    if ($echo) { echo ' <font style="color:green;">'.__("OK", MYARCADE_TEXT_DOMAIN).'</font></p>'; }
  }
  else {
    if ($echo) {
      echo ' <font style="color:red;">'.__("Failed to decode the downloaded feed!", MYARCADE_TEXT_DOMAIN).'</font></p>';
      arcadelite_footer();
    }
    
    return;
  }
    
  //====================================
  foreach ($json_games->games as $game) {
        
    // Check, if this game is present in the games table
    $game_uuid = $wpdb->get_var("SELECT uuid FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid'");
    $game_tag  = $wpdb->get_var("SELECT game_tag FROM ".MYARCADE_GAME_TABLE." WHERE game_tag = '$game->game_tag'");

    if (!$game_uuid && !$game_tag) {
      // Check game categories and add game if it's category has been selected
      
      $add_game   = false;
      $categories = '';
      // Category-Check
      foreach($game->categories as $gamecat) {
        foreach ($feedcategories as $feedcat) {
          if ( ($feedcat['Name'] == $gamecat) && ($feedcat['Status'] == 'checked') ) {
            $add_game = true;
            break;
          }
        }

        if ($add_game == true) break;
      } // END - Category-Check

      if ($add_game == true) {
        $categories = implode(",", $game->categories);
      } else continue;

      // Tags
      $tags = implode(",", $game->tags);

      // Controls
      $game_control = '';
      foreach ($game->controls as $control) {
        $game_control .= implode(" = ", $control) . ";";
      }

      $game->type          = 'mochi';
      $game->name          = mysql_escape_string($game->name);
      $game->description   = mysql_escape_string($game->description);
      $game->instructions  = mysql_escape_string($game->instructions);
      $game->rating        = mysql_escape_string($game->rating);
      $game->categs        = $categories;
      $game->control       = $game_control;
      $game->thumbnail_url = mysql_escape_string($game->thumbnail_url);
      $game->swf_url       = mysql_escape_string($game->swf_url);
      $game->screen1_url   = mysql_escape_string($game->screen1_url);
      $game->screen2_url   = mysql_escape_string($game->screen2_url);
      $game->screen3_url   = mysql_escape_string($game->screen3_url);
      $game->screen4_url   = mysql_escape_string($game->screen4_url);
      $game->video_url     = mysql_escape_string($game->video_url);
      $game->leaderboard_enabled =  mysql_escape_string($game->leaderboard_enabled);
      $game->highscore_type = '';
      $game->coins_enabled = mysql_escape_string($game->coins_enabled);
      $game->tags          = mysql_escape_string($tags);   
      $game->status        = 'new';

      $new_games++;
      
      // Insert the game to the table
      arcadelite_insert_game($game);
      
      // Get game id
      $game->id = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid' LIMIT 1");
      
      // Check if this is an autopost 
      if ( !empty($MochiGameTag) || ($cronfeeding == true) ) {
        // We have to publish this game
        arcadelite_add_games_to_blog($game->id, 'publish', true);
      }

      if ($echo) { arcadelite_show_game($game); }
    }
  }

  if ($echo) {
    if ($new_games > 0) {
      echo '<p><strong>'.sprintf(__("Found %s new game(s).", MYARCADE_TEXT_DOMAIN), $new_games).'</strong></p>';
      echo '<p class="noerror">'.__("Now, you can add new games to your blog.", MYARCADE_TEXT_DOMAIN).'</p>';
    }
    else {
      echo '<p class="mabp_error"><strong>'.__("No new games found!", MYARCADE_TEXT_DOMAIN).'<br />'.__("You can try to increase the number of 'Feed Games' at the settings page or wait until Mochi updates the feed.", MYARCADE_TEXT_DOMAIN).'</strong></p>';
    }
    
    arcadelite_footer();
  }

} // END - mochi_feed_games


/**
 * @brief Inserts a game as a wordpress post
 */
function arcadelite_add_game_post($game) {
  global $wpdb; 
  
  // Get settings
  $arcadelite_settings = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
  
  // Generate the content
  if ($arcadelite_settings->use_template == 'Yes') {
    $post_content = $arcadelite_settings->template;
    $post_content = str_replace("%THUMB%", '<img src="'.$game->thumb.'" alt="'.$game->name.'" />', $post_content);
    $post_content = str_replace("%DESCRIPTION%",    $game->description,   $post_content);
    $post_content = str_replace("%INSTRUCTIONS%",  $game->instructions,  $post_content);
  }
  else {
    $post_content = '<img src="'.$game->thumb.'" style="float:left;margin-right:5px;">'.$game->description;
  }
      
  //====================================
  // Create a WordPress post    
  $post = array();
  $post['post_title']     = $game->name;
  $post['post_content']   = $post_content;
  $post['post_status']    = $game->publish_status;
  $post['post_author']    = $game->user;
  $post['post_type']      = 'post';
  $post['post_category']  = $game->categories; // Category IDs - ARRAY
  $post['post_date']      = $game->date;
  $post['tags_input']     = $game->tags;

  $post_id = wp_insert_post($post);


  add_post_meta($post_id, 'mabp_description',    $game->description);
  add_post_meta($post_id, 'mabp_instructions',   $game->instructions);
  add_post_meta($post_id, 'mabp_height',         $game->height);
  add_post_meta($post_id, 'mabp_width',          $game->width);
  add_post_meta($post_id, 'mabp_swf_url',        $game->file);
  add_post_meta($post_id, 'mabp_thumbnail_url',  $game->thumb);
  add_post_meta($post_id, 'mabp_rating',         $game->rating);
  add_post_meta($post_id, 'mabp_screen1_url',    $game->screen1_url);
  add_post_meta($post_id, 'mabp_screen2_url',    $game->screen2_url);
  add_post_meta($post_id, 'mabp_screen3_url',    $game->screen3_url);
  add_post_meta($post_id, 'mabp_screen4_url',    $game->screen4_url);
  add_post_meta($post_id, 'mabp_video_url',      $game->video_url);
  add_post_meta($post_id, 'mabp_game_type',      $game->type);
  
  // Update postID
  $query = "UPDATE ".MYARCADE_GAME_TABLE." SET postid = '$post_id' WHERE id = $game->id";
  $wpdb->query($query);
}


/**
 * @brief Adds feeded games to the blog as posts
 * @para $gameID
 * @para $publish_status
 * @para $cron
 */
function arcadelite_add_games_to_blog($gameID = 0, $publish_status = 'publish', $cron = false) {
  global $wpdb, $current_user, $user_ID;
  
  if ($gameID > 0) { $echo = false; } else { $echo = true; }

  if ($echo == true) {
    arcadelite_header();
    arcadelite_prepare_environment();
  }

  $post_interval = 0;
  $new_games = false;

  // Get settings
  $arcadelite_settings = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
  
  // Get game categories
  $feedcategories = unserialize($arcadelite_settings->game_categories);
    
  // Check Download Directories
  $download_games = false;
  if ($arcadelite_settings->download_games == 'Yes') {
    if (!is_writable(ABSPATH.MYARCADE_GAMES_DIR)) {
      if ($echo == true)
        echo '<p class="mabp_error">'.sprintf(__("The games directory '%s' must be writeable (chmod 777) in order to download games.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_GAMES_DIR).'</p>';
    } else {
      $download_games = true;
    }
  }
 
  $download_thumbs = false;
  if ($arcadelite_settings->download_thumbs == 'Yes') {
    if (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR)) {
      if ($echo == true)
        echo '<p class="mabp_error">'.sprintf(__("The thumbails directory '%s' must be writeable (chmod 777) in order to download thumbnails.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
    } else {
      $download_thumbs = true;
    }
  }
  
  // Check if a single game should be inserted...
  if (!$gameID) {
    $unpublished_games  = $wpdb->get_var("SELECT COUNT(*) FROM ".MYARCADE_GAME_TABLE." WHERE status = 'new'");
    
    if (intval($arcadelite_settings->publish_games) <= $unpublished_games) {
      $game_limit = $arcadelite_settings->publish_games;
    } else {
      $game_limit = $unpublished_games;
    }
  } 
  else {
    $game_limit = 1;
  } 
  

  //====================================
  if ($echo == true) {
    echo '<h3>'.__("Games To Blog", MYARCADE_TEXT_DOMAIN).'</h3>';
    echo "<ul>";
  }  

  // Publish Games
  // Go trought all games and add this to the blog
  for($i = 1; $i <= $game_limit; $i++) {
   
    // Get the next game
    if (!$gameID) {
      $game = $wpdb->get_row("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE status = 'new' order by created asc limit 1");
    }
    else {
      $game = $wpdb->get_row("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID' limit 1");
      
      // Check if this is a import game..
      // If is an imported game don't download the files again...
      if (md5($game->name.'import') == $game->uuid) {
        $download_games   = false;
        $download_thumbs  = false;
      }
    }
        
    if (!$game) {
      if ($echo == true)
        echo '<p class="mabp_error">'.__("Hmmm, there are no games that can be added to your blog...", MYARCADE_TEXT_DOMAIN).'</p>';
      break;
    }

    // Initialise some vars
    $new_games  = true;
    $cat_id     = array();

    // adjust the background color
    if ( !($i % 2) )
      $bg_color = 'style="background-color: #EFEFEF;"';
    else
      $bg_color = '';

      if ($echo == true) {
      ?> 
        <li <?php echo $bg_color; ?>>
          <strong><?php echo $game->name; ?></strong><br />
          <div>
            <div style="float:left;margin-right:5px">
              <img src="<?php echo $game->thumbnail_url; ?>" alt="">
            </div>
            <div style="float:left">
            <strong><?php _e("Categories: ", MYARCADE_TEXT_DOMAIN); ?></strong> <?php echo $game->categories; ?><br />
      <?php
      }

      // Check game categories..
      $categs = explode(",", $game->categories);
      
        foreach($categs as $game_cat) {
          $cat_found = false;
          foreach($feedcategories as $feedcat) {
            if ($feedcat['Name'] == $game_cat) {
              $cat_found = true;
              array_push ($cat_id, get_cat_id($game_cat));              
              break;
            }
          }
          
          if ($cat_found == false) {
            array_push ($cat_id, get_cat_id($game_cat));
          }
        }
     

      // Download Thumbs?
      if ($download_thumbs == true) {
        $thumb = '';
        
        if ($echo == true)
          _e("Downloading Thumbnail..", MYARCADE_TEXT_DOMAIN);

          
            $thumb = arcadelite_get_file($game->thumbnail_url, true);

        if ($thumb) {
          $path_parts = pathinfo($game->thumbnail_url);
          $extension  = $path_parts['extension'];
          $file_name  = $game->slug.'.'.$extension;
          
          // Check, if we got a Error-Page
          if (!strncmp($thumb, "<!DOCTYPE", 9)) {
            $result = false;  
          }
          else {
            // Save the thumbnail to the thumbs folder
            $result = file_put_contents(ABSPATH.MYARCADE_THUMBS_DIR.$file_name, $thumb);
          }
      
          // Error-Check
          if ($result == false) {
            if ($echo == true)
              echo " <strong>".__("Failed", MYARCADE_TEXT_DOMAIN)."</strong>! ".__("Using the thumbnail from the Mochi-URL..", MYARCADE_TEXT_DOMAIN)."<br />";
          } else {
              $game->thumbnail_url = get_option('siteurl').'/'.MYARCADE_THUMBS_DIR.$file_name;
              if ($echo == true)
                echo " <strong>".__("OK", MYARCADE_TEXT_DOMAIN)."</strong>!<br />";
          }
        } else {
            if ($echo == true)
              echo " <strong>".__("Failed", MYARCADE_TEXT_DOMAIN)."</strong>! ".__("Using the thumbnail from the Mochi-URL..", MYARCADE_TEXT_DOMAIN)."<br />";
        }
      }
      
      // Screens?
      for ($screenNr = 1; $screenNr <= 4; $screenNr++) {
        $screenshot_url = 'screen'.$screenNr."_url";

        // Put the screen urls into the post array
        $game_to_add->$screenshot_url = $game->$screenshot_url;
      } // END for - screens      

      // Download Games?
      if ($download_games == true) {
        //$game_swf = '';        
        $game_swf = arcadelite_get_file($game->swf_url, true);

        // We got a file
        if ($game_swf) {
          $path_parts = pathinfo($game->swf_url);
          $extension  = $path_parts['extension'];
          $file_name  = $game->slug.'.'.$extension;
          
          // Check, if we got a Error-Page  
          if (!strncmp($game_swf, "<!DOCTYPE", 9)) {
              $result = false;
          }
          else {
            // Save the game to the games directory
            $result = file_put_contents(ABSPATH.MYARCADE_GAMES_DIR.$file_name, $game_swf);
          }

          // Error-Check 
          if ($result == false) {
            if ($echo == true)
              echo '<p class="mabp_error">'.__("Game download:", MYARCADE_TEXT_DOMAIN).' <strong>'.__("Failed", MYARCADE_TEXT_DOMAIN).'</strong>! '.__("Ignore this game..", MYARCADE_TEXT_DOMAIN).'</p>';

            // Set status to ignored
            $query = "UPDATE ".MYARCADE_GAME_TABLE." SET status = 'ignored' where id = $game->id";
            $wpdb->query($query);
            if ($echo == true)
              echo '</div></div><div style="clear:both;"></div></li>';
              
            continue;
          } 
          else {
            if ($echo == true)
              echo __("Game download:", MYARCADE_TEXT_DOMAIN)." <strong>".__("OK", MYARCADE_TEXT_DOMAIN)."</strong>!<br />";
              
            $game->swf_url = get_option('siteurl'). '/'.MYARCADE_GAMES_DIR.$file_name;
          }
        } 
        else {
          if ($echo == true)
            echo '<p class="mabp_error">'.__("Game download:", MYARCADE_TEXT_DOMAIN).' <strong>'.__("Failed", MYARCADE_TEXT_DOMAIN).'</strong>! '.__("Ignore this game..", MYARCADE_TEXT_DOMAIN).'</p>';
          
          // Set status to ignored
          $query = "UPDATE ".MYARCADE_GAME_TABLE." SET status = 'ignored' where id = $game->id";
          $wpdb->query($query);

          if ($echo == true)
            echo '</div></div><div style="clear:both;"></div></li>';
            
          continue;
        }
      } // END - if download games
      
      if ($echo == true) { echo '</div></div><div style="clear:both;"></div></li>'; }        
        
      //====================================
      // Create a WordPress post
      
      // Get user info's 
        get_currentuserinfo();
        $game_to_add->user = $user_ID;
        
        if ( empty($game_to_add->user) ) $game_to_add->user = 1;
                          
        if ($gameID == 0) {
          if (strtolower($arcadelite_settings->publish_status) == 'scheduled') {    
            $post_interval = $post_interval + $arcadelite_settings->schedule;
            $publish_status = 'future';
          }
          else {          
            $publish_status = strtolower($arcadelite_settings->publish_status);
          }
        }
            
      $game_to_add->id             = $game->id;
      $game_to_add->name           = $game->name;
      $game_to_add->file           = $game->swf_url;
      $game_to_add->width          = $game->width;
      $game_to_add->height         = $game->height;
      $game_to_add->thumb          = $game->thumbnail_url;
      $game_to_add->description    = $game->description;
      $game_to_add->instructions   = $game->instructions;
      $game_to_add->video_url      = $game->video_url;
      $game_to_add->tags           = $game->tags;
      $game_to_add->rating         = $game->rating;
      $game_to_add->categories     = $cat_id;
      $game_to_add->date           = gmdate( 'Y-m-d H:i:s', ( time() + ($post_interval*60) + (get_option( 'gmt_offset' ) * 3600 ) ) );;
      $game_to_add->type           = $game->game_type;
      $game_to_add->publish_status = $publish_status;
            
      // Add game as a post
      arcadelite_add_game_post($game_to_add);

      // Mochi-Table: Set post status to poblished
      $query = "update ".MYARCADE_GAME_TABLE." set status = 'published' where id = $game->id";

      $wpdb->query($query); 
  } // END - for games

  //====================================
  if ($echo == true) {
    if(!$new_games) {
      echo '<li><p class="mabp_error">'.__("No new games to add. Feed Games first!", MYARCADE_TEXT_DOMAIN).'</p></li>';
    }

    echo "</ul>";
    
    arcadelite_footer();
  }
} // END - Games To Blog



/**
 *  @brief Ajax handler for managing games
 */
function arcadelite_handler() {
  global $wpdb;
   
  // Check if the current user has permissions to do that...
  if ( current_user_can('manage_options') == false ) {
    wp_die('You do not have permissions access this site!');
  }
      
  if ( isset($_POST['gameid']) ) $gameID = $_POST['gameid']; else $gameID = '';
    
  switch ($_POST['func']) {
    /* Manage Games */
    case "publish":
    {
      // Publish this game
      arcadelite_add_games_to_blog($gameID);
      
      // Get game status
      $status = $wpdb->get_var("SELECT status FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'");
      echo $status;
    }
    break;

    case "delete":
    {
      // Check if game is published
      $game = $wpdb->get_row("SELECT postid, name FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'", ARRAY_A);
      $postid = $game['postid'];
      
      if (!$postid)  {
        // Alternative check for older versions of MyArcadePlugin
        $postid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '".$game['name']."'");
      }
      
      if ($postid) {
        // Delete wordpress post
        wp_delete_post($postid);
      }

                
        // Update game status
        $query = "UPDATE ".MYARCADE_GAME_TABLE." SET status = 'deleted', postid = '' WHERE id = $gameID";
        $wpdb->query($query);
     
      
      // Get game status
      $status = $wpdb->get_var("SELECT status FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'");
      echo $status;         
    }
    break;
    
    case "remove":
      {
        // Remove this game from mysql database
        $query = "DELETE FROM ".MYARCADE_GAME_TABLE." WHERE id = $gameID LIMIT 1";
        $wpdb->query($query);     
        echo "removed";     
      }
      break;
      
    /* Database Actions */
    case "delgames":
    {      
      $wpdb->query("TRUNCATE TABLE ".MYARCADE_GAME_TABLE);
      ?>
      <script type="text/javascript">
        alert('All games deleted!');
      </script>
      <?php
    }
    break;
  }
  
  die();
}


/**
 * @brief Shows a game with relevant informations
 */
function arcadelite_show_game($game) {

 if ($game->leaderboard_enabled)
  $leader = '<div class="myhelp"><img src="'.WP_PLUGIN_URL.'/myarcadeblog/images/trophy.png" alt="'.__("Leaderboard enabled", MYARCADE_TEXT_DOMAIN).'">
             <span class="myinfo">'.__("Leaderboard enabled", MYARCADE_TEXT_DOMAIN).'</span></div>';
 else $leader = '';
 
 if ($game->coins_enabled)
  $coins = '<div class="myhelp"><img src="'.WP_PLUGIN_URL.'/myarcadeblog/images/coins.png" alt="'.__("Coin revenue share enabled", MYARCADE_TEXT_DOMAIN).'">
            <span class="myinfo">'.__("Coin revenue share enabled", MYARCADE_TEXT_DOMAIN).'</span></div>';
 else $coins = ''; 
 
 $play_url = WP_PLUGIN_URL.'/myarcadeblog/modules/playgame.php?gameid='.$game->id;
 $edit_url = WP_PLUGIN_URL.'/myarcadeblog/modules/editgame.php?gameid='.$game->id;
 
 // Buttons
 $publish     = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."/admin-ajax.php',{action:'arcadelite_handler',gameid:'$game->id',func:'publish'},function(data){jQuery('#gstatus_$game->id').html(data);});\">".__("Publish", MYARCADE_TEXT_DOMAIN)."</button>&nbsp;";
 $delete      = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."/admin-ajax.php',{action:'arcadelite_handler',gameid:'$game->id',func:'delete'},function(data){jQuery('#gstatus_$game->id').html(data);});\">".__("Delete", MYARCADE_TEXT_DOMAIN)."</button>&nbsp;";
 $delgame     = "<div class=\"myhelp\"><img style=\"cursor: pointer;\" src='".WP_PLUGIN_URL."/myarcadeblog/images/delete.png' alt=\"Remove game from the database\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."/admin-ajax.php',{action:'arcadelite_handler',gameid:'$game->id',func:'remove'},function(){jQuery('#gamebox_$game->id').fadeOut('slow');});\" />
                <span class=\"myinfo\">".__("Remove this game from the database", MYARCADE_TEXT_DOMAIN)."</span></div> 
 ";

 // Chek game dimensions
 if ( empty($game->height) ) $game->height = '800';
 if ( empty($game->width)  ) $game->width = '600';
 
 $edit ='<a href="#" class="button-secondary" title="'.__("Edit", MYARCADE_TEXT_DOMAIN).'" onclick="alert(\'This is a MyArcadePlugin Pro feature!\');return false;">'.__("Edit", MYARCADE_TEXT_DOMAIN).'</a>&nbsp;';

?>
 <div class="show_game" id="gamebox_<?php echo $game->id;?>">
      <div class="block">
        <table class="optiontable" width="100%">
          <tr valign="top">
            <td width="110" align="center">
              <img style="border:1px solid #d6d6d6;padding:5px;" src="<?php echo $game->thumbnail_url; ?>" width="100" height="100" alt="" />
            </td>
            <td colspan="2">
              <table>
                <tr valign="top">
                  <td width="520">
                    <strong>
                      <div id="gname_<?php echo $game->id;?>">
                        <?php 
                          if (strlen($game->name) > 25) {
                            echo substr($game->name, 0, 23).'..';
                          }
                          else {
                            echo $game->name; 
                          }                        
                        ?>
                      </div>
                    </strong>
                  </td>
                  <td><?php echo $leader; echo $coins; ?></td>
                </tr>
                <tr>
                  <td><?php echo substr(mysql_escape_string($game->description), 0, 300)."..";  ?></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center">
              <p style="margin-top:3px;"><a class="thickbox button-primary" title="<?php echo $game->name; ?>" href="<?php echo $play_url; ?>&keepThis=true&TB_iframe=true&height=<?php echo $game->height; ?>&width=<?php echo $game->width; ?>"><?php _e("Play", MYARCADE_TEXT_DOMAIN)?></a></p>
            </td>
            <td>
           

              <?php echo $delgame; ?>
              
              <?php
                switch ($game->status) {
                  case 'ignored':
                  case 'new':         echo $edit; echo $publish;  echo $delete;   break;
                  case 'published':   echo $edit; echo $delete;                   break;
                  case 'deleted':     echo $edit; echo $publish;                  break;
                }
              ?>

            </td>
            <td width="130">                
              <div id="gstatus_<?php echo $game->id;?>" style="margin: 0;font-weight:bold;float:right;">
                <?php echo $game->status; ?>
              </div>
            </td>
          </tr>

        </table>            
      </div>
    </div>
<?php
}


/**
 * @brief Manages games from the admin pannel
 */
function arcadelite_manage_games() {
  global $wpdb;
    
  arcadelite_header();
  
  echo "<h3>".__("Manage Games", MYARCADE_TEXT_DOMAIN)."</h3>";
  
  if ( isset($_POST['action']) ) $action = $_POST['action']; else $action = '';
  
  $keyword = '';
  
  if ($action == 'search') {
    $keyword = mysql_escape_string($_POST['q']);
    
    switch ($keyword) {
      case 'new':
      case 'published':
      case 'deleted':
        {
          $query = "SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE
             status LIKE '$keyword' ORDER BY name ASC";
        }
        break;
        
      default:
        {
          $query = "SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE
             name LIKE '%$keyword%'
             OR description LIKE '%$keyword%'
             OR game_type LIKE '%$keyword%'
             ORDER BY name ASC";
        }
        break;          
    }
  
    $results = $wpdb->get_results($query);
   
    if (!$results) {
      echo '<div class="mabp_error">'.sprintf(__("Nothing found for %s", MYARCADE_TEXT_DOMAIN), $keyword).'</strong></div>';
    }
  }

  // Search form
  ?>
  <form method="post">
    <input type="hidden" name="action" value="search" />
    <table>
      <tr>
        <td><div class="help"><span class="info"><?php _e("Search for a game name, keyword or status (new, published, deleted)...", MYARCADE_TEXT_DOMAIN); ?></span></div></td>
      </tr>
      <tr>
        <td><input type="text" name="q" value="<?php echo $keyword; ?>"  size="40" /> <input class="button-primary" type="submit" id="submit" value="<?php _e("Search", MYARCADE_TEXT_DOMAIN); ?>" /></td>
      <tr>
    </table>
  </form>
  <div class="clear"></div>
  
 <?php
  
  if ( isset($results) ) {
    echo '<h3>'.sprintf(__("Search results for %s", MYARCADE_TEXT_DOMAIN), $keyword).'"</h3>';
    foreach ($results as $game) {
      arcadelite_show_game($game);
    }
  }
  else {
    
      /* Begin Pagination */
      $count = $wpdb->get_var("SELECT COUNT(*) FROM ".MYARCADE_GAME_TABLE);
      
      // This is the number of results displayed per page  
      $page_rows = 50;
      
      // This tells us the page number of our last page  
      $last = ceil($count / $page_rows); 
      
      // This makes sure the page number isn't below one, or more than our maximum pages
      if (isset($_GET['pagenum']) ) $pagenum = $_GET['pagenum']; else $pagenum = 1;
      
      if ($pagenum < 1)  {
        $pagenum = 1;
      }  
      elseif ($pagenum > $last)  {
        $pagenum = $last;
      }
      
      // This sets the range to display in our query  
      $range = 'limit ' .($pagenum - 1) * $page_rows .',' .$page_rows; 
      
      // Calculate counts for next and previous
      if ( $pagenum != $last) { 
      	$next = $pagenum + 1; 
      }
      
      if ($pagenum > 1) {
      	$previous = $pagenum - 1;
      }
      
      // Generate from .. to
      $from = 1 + ($pagenum - 1) * $page_rows;
      if ($pagenum < $last) {
			$to = $from + $page_rows - 1;
      }
      else {
      	$to = $count;
      }
      
      $from_to = $from.' - '.$to;
      
      /* End Paginagion */


    // Last feeded games
    $results = $wpdb->get_results("SELECT * FROM ".MYARCADE_GAME_TABLE." ORDER BY ID DESC $range");

    if ($results) {
      echo '<h3>'.__("Browser Your Game Catalog", MYARCADE_TEXT_DOMAIN).'</h3>';
      ?>
        <!-- Print pagination -->
        <div class="tablenav" style="float: left;">
        	<div class="tablenav-pages">
        		<span class="displaying-num">Displaying <?php echo $from_to; ?> of <?php echo $count; ?></span>
    			<?php if ($pagenum > 1) : ?>
	          	<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=1'>First</a>
        			<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $previous; ?>'>Previous</a>
        		<?php endif; ?>
          	<span class='page-numbers current'><?php echo $pagenum; ?></span>
          	<?php if ($pagenum != $last) : ?>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $next; ?>'>Next</a>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $last; ?>'>Last</a>
          	<?php endif; ?>
        	</div>
        </div>
        <div class="clear"></div>
      <?php
      foreach ($results as $game) {
        arcadelite_show_game($game);
      }
      ?>
        <!-- Print pagination -->
        <div class="tablenav" style="float: left;">
        	<div class="tablenav-pages">
        		<span class="displaying-num">Displaying <?php echo $from_to; ?> of <?php echo $count; ?></span>
    			<?php if ($pagenum > 1) : ?>
	          	<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=1'>First</a>
        			<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $previous; ?>'>Previous</a>
        		<?php endif; ?>
          	<span class='page-numbers current'><?php echo $pagenum; ?></span>
          	<?php if ($pagenum != $last) : ?>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $next; ?>'>Next</a>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $last; ?>'>Last</a>
          	<?php endif; ?>
        	</div>
        </div>
        <div style="clear:both;"></div>
      <?php

    }
      
    $results = $wpdb->get_results("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE status = 'deleted' ORDER BY created DESC limit 10");

    if ($results) {
      echo '<h3>'.__("10 Last Deleted Games", MYARCADE_TEXT_DOMAIN).'</h3>';
      foreach ($results as $game) {
        arcadelite_show_game($game);
      }
      ?><div style="clear:both;"></div><?php
    }      
  }
    
  arcadelite_footer();
}


/**
 * @brief Imports other games than Mochi
 */
function arcadelite_import_games() {
    
  arcadelite_header();  
  
  if ( isset($_POST['impcostgame']) && $_POST['impcostgame'] == 'import') {
    $game->swf_url        = $_POST['importgame'];
    $game->width          = $_POST['gamewidth'];
    $game->height         = $_POST['gameheight'];
    $game->slug           = preg_replace("/[^a-zA-Z0-9 ]/", "", strtolower($_POST['gamename']));
    $game->slug           = str_replace(" ", "-", $game->slug);        
    $game->name           = $_POST['gamename'];
    $game->type           = $_POST['importtype'];
    $game->uuid           = md5($game->name.'import');
    $game->game_tag       = crc32($game->uuid);
    $game->thumbnail_url  = $_POST['importthumb'];
    $game->description    = $_POST['gamedescr'];
    $game->instructions   = $_POST['gameinstr'];
    $game->control        = '';
    $game->rating         = '';
    $game->tags           = $_POST['gametags'];
    $game->categs         = implode(",", $_POST['gamecategs']);
    $game->created        = gmdate( 'Y-m-d H:i:s', ( time() + (get_option( 'gmt_offset' ) * 3600 ) ) );
    $game->leaderboard_enabled = $_POST['lbenabled'];
    $game->highscore_type = $_POST['highscoretype'];
    $game->coins_enabled  = '';
    $game->status         = 'new';
    $game->video_url      = '';
    $game->screen1_url    = $_POST['importscreen1'];
    $game->screen2_url    = $_POST['importscreen2'];
    $game->screen3_url    = $_POST['importscreen3'];
    $game->screen4_url    = $_POST['importscreen4'];
    
    // Add game to table
    arcadelite_insert_game($game);
            
    // Add the game as blog post
    if ($_POST['publishstatus'] != 'add') {
      global $wpdb;
      $gameID = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid'");
      if ( !empty($gameID) ) {
        arcadelite_add_games_to_blog($gameID, $_POST['publishstatus']);
                
        echo '<p class="mabp_info">'.sprintf(__("Import of '%s' was succsessful.", MYARCADE_TEXT_DOMAIN), $game->name).'</p>';
      }
      else  {
        echo '<p class="mabp_error">'.__("Can't import that game...", MYARCADE_TEXT_DOMAIN).'</p>';
      }
    }
  }  

  $categs = get_all_category_ids();
?>
<?php @include_once('modules/myarcadeblog_js.php'); ?>
<div id="myabp_import">        
  <h2><?php _e("Import Individual Games", MYARCADE_TEXT_DOMAIN); ?></h2>    

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Import Method", MYARCADE_TEXT_DOMAIN); ?></h3></td>
        </tr>
        <tr>
          <td>
            <select size="1" name="importmethod" id="importmethod">
              <option value="importswfdcr"><?php _e("Upload SWF game", MYARCADE_TEXT_DOMAIN); ?>&nbsp;</option>
              <option value="importibparcade"><?php _e("Upload IBPArcade game", MYARCADE_TEXT_DOMAIN); ?></option>
              <option value="importembedif"><?php _e("Import Embed / Iframe game", MYARCADE_TEXT_DOMAIN); ?></option>
            </select>            
            <br />
            <i><?php _e("Choose a desired import method.", MYARCADE_TEXT_DOMAIN); ?></i>
          </td>
        </tr>
      </table>            
    </div>
  </div>
  
  <?php @include_once('modules/form-swfdcr.php'); ?>
</div><?php // end #myabp_import ?>
<div class="clear"></div>  

 <?php
  
  arcadelite_footer();
} // END - Import Games



/*******************************************************************************
 * S E T U P  F U N C T I O N S
 ******************************************************************************/

/**
 * @brief Increases the memory limit and disables time out 
 */
function arcadelite_prepare_environment() {
  
  $max_execution_time_l     = 60*10;  // 10 min
  $memory_limit_l           = "128M"; // Should be enough
  $set_time_limit_l         = 60*10;  // 10 min

  $cant       = '<p class="message_error">'.__("<strong>WARNING!</strong> Can't set value for: ", MYARCADE_TEXT_DOMAIN);
  $contact_1  = '. '.__("If MyArcadePlugin Pro doesn't work properly, please contact your administrator to increase the value of ", MYARCADE_TEXT_DOMAIN);
  $contact_2  = ' '.__("to", MYARCADE_TEXT_DOMAIN).' ';
  $contact_3  = '.</p>';
  
  // Check for safe mode
  if( !ini_get('safe_mode') ) {
    // Check max_execution_time
    if ( !(ini_set("max_execution_time", $max_execution_time_l)) )
      echo $cant.'max_execution_time'.$contact_1.'max_execution_time'.$contact_2.$max_execution_time_l.$contact_3;

    // Check memory limit
    if ( strcmp(ini_get("memory_limit"), $memory_limit_l) == 0 ) {
      if ( !(ini_set("memory_limit", $memory_limit_l)) )
        echo $cant.'memory_limit'.$contact_1.'memory_limit'.$contact_2.$memory_limit_l.$contact_3;
    }

    if ( !(set_time_limit($set_time_limit_l)) )
      echo $cant.'time_limit'.$contact_1.'time_limit'.$contact_2.$set_time_limit_l.$contact_3;
  }
  else {
    // save mode is set
    echo '<p class="mabp_error"><strong>'.__("WARNING!", MYARCADE_TEXT_DOMAIN).'</strong> '.__("Can't make needed settins, because you have Safe Mode active.", MYARCADE_TEXT_DOMAIN).'</p>';
  }
} // END - arcadelite_prepare_environment


/**
 * @brief Plugin installation. Adds needed tables
 */
function arcadelite_install() {
  global $wpdb;

  //
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
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
    
  // Check if the table needs to be upgraded..
  arcadelite_upgrade_games_table();


  //
  // Check if settings table exisits
  if ($wpdb->get_var("show tables like '".MYARCADE_SETTINGS_TABLE."'") != MYARCADE_SETTINGS_TABLE) {
    
    $default_theme = mysql_escape_string('<p>
  <div style=\"float:left;margin-right: 10px; margin-bottom: 10px;\">
    %THUMB%
  </div>
  <h2>Description</h2>
  <p>%DESCRIPTION%</p>
  <h2>Instructions</h2>
  <p>%INSTRUCTIONS%</p>
</p>');

    // HeyZap Settings
    $heyzap = array();
    $heyzap_settings      = serialize($heyzap);
    
    // Include the feed game categories
    @include('modules/myabp_feedcats.php');
    $ser_feedcategories = serialize($feedcategories);

    // Create new settings table
    $sql = "CREATE TABLE `".MYARCADE_SETTINGS_TABLE."` (
      `ID`                int(11) NOT NULL auto_increment,
      `mochiads_url`      text collate utf8_unicode_ci NOT NULL,
      `mochiads_id`       text collate utf8_unicode_ci NOT NULL,
      `mochi_skey`        text collate utf8_unicode_ci NOT NULL,
      `feed_games`        text collate utf8_unicode_ci NOT NULL,
      `feed_cat`          text collate utf8_unicode_ci NOT NULL,
      `single_publish`    text collate utf8_unicode_ci NOT NULL,
      `single_catid`      text collate utf8_unicode_ci NOT NULL,
      `publish_games`     text collate utf8_unicode_ci NOT NULL,
      `publish_status`    text collate utf8_unicode_ci NOT NULL,
      `download_thumbs`   text collate utf8_unicode_ci NOT NULL,
      `download_games`    text collate utf8_unicode_ci NOT NULL,
      `download_screens`  text collate utf8_unicode_ci NOT NULL,
      `delete_files`      text collate utf8_unicode_ci NOT NULL,
      `schedule`          text collate utf8_unicode_ci NOT NULL,
      `game_categories`   text collate utf8_unicode_ci NOT NULL,
      `create_categories` text collate utf8_unicode_ci NOT NULL,
      `cron_active`       text collate utf8_unicode_ci NOT NULL,
      `cron_interval`     text collate utf8_unicode_ci NOT NULL,
      `maxwidth`          text collate utf8_unicode_ci NOT NULL,
      `leaderboard_active` text collate utf8_unicode_ci NOT NULL,
      `global_scores`     text collate utf8_unicode_ci NOT NULL,
      `guest_scores`      text collate utf8_unicode_ci NOT NULL,
      `guest_prefix`      text collate utf8_unicode_ci NOT NULL,
      `embed_flashcode`   text collate utf8_unicode_ci NOT NULL,
      `use_template`      text collate utf8_unicode_ci NOT NULL,
      `template`          text collate utf8_unicode_ci NOT NULL,
      `heyzap`            text collate utf8_unicode_ci NOT NULL,
      `tag`               text collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`ID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Insert default values into the settins table
    $sql = "INSERT INTO ".MYARCADE_SETTINGS_TABLE." (
              `ID` ,
              `mochiads_url` ,
              `mochiads_id` ,
              `mochi_skey` ,
              `feed_games` ,
              `feed_cat` ,
              `single_publish`,
              `single_catid`,
              `publish_games` ,
              `publish_status` ,
              `download_thumbs` ,
              `download_games` ,
              `download_screens` ,
              `delete_files` ,
              `schedule` ,
              `game_categories` ,
              `create_categories` ,
              `cron_active`,
              `cron_interval`,
              `maxwidth`,
              `leaderboard_active`,
              `global_scores`,
              `guest_scores`,
              `guest_prefix`,
              `embed_flashcode`,
              `use_template`,
              `template`,
              `heyzap`,
              `tag`
              ) VALUES (
                  NULL , 
                  'http://www.mochimedia.com/feeds/games/',
                  '',
                  '',
                  '100',
                  'All',
                  'false',
                  '',
                  '20',
                  'Publish',
                  'No',
                  'No',
                  'No',
                  '',
                  '',
                  '$ser_feedcategories',
                  '',
                  'No',
                  'hourly',
                  '',
                  'No',
                  'false',
                  'false',
                  'guest_',
                  'manually',
                  'No',
                  '$default_theme',
                  '$heyzap_settings',
                  ''
              )";

     $wpdb->query($sql);
  }
   
  // Check if the table needs to be upgraded..
  arcadelite_upgrade_settings_table();
    
  // Make also an update of post meta
  arcadelite_upgrade_post_metas();      
}


/**
 * @brief Upgrades the games table
 */
function arcadelite_upgrade_games_table() {
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
 * @brief Upgrades the sttings table
 */
function arcadelite_upgrade_settings_table() {
  global $wpdb;
    
  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('maxwidth', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `maxwidth` text collate utf8_unicode_ci NOT NULL
      AFTER `create_categories`
    ");
  }
  
  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('delete_files', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `delete_files` text collate utf8_unicode_ci NOT NULL
      AFTER `download_games`
    ");
  }
  
  if (!in_array('feed_cat', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE ".MYARCADE_SETTINGS_TABLE."
      ADD `feed_cat` text collate utf8_unicode_ci NOT NULL
      AFTER `feed_games`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET feed_cat = 'All' WHERE ID = 1");
  }

  if (!in_array('download_screens', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `download_screens` text collate utf8_unicode_ci NOT NULL
      AFTER `download_games`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET download_screens = 'No' WHERE ID = 1");
  }

  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('cron_active', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `cron_active` text collate utf8_unicode_ci NOT NULL
      AFTER `create_categories`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET cron_active = 'No' WHERE ID = 1");
  }
  
  if (!in_array('cron_interval', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `cron_interval` text collate utf8_unicode_ci NOT NULL
      AFTER `cron_active`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET cron_interval = 'hourly' WHERE ID = 1");
  }

  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('mochi_skey', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `mochi_skey` text collate utf8_unicode_ci NOT NULL
      AFTER `mochiads_id`
    ");
  }

  if (!in_array('leaderboard_active', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `leaderboard_active` text collate utf8_unicode_ci NOT NULL
      AFTER `maxwidth`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET leaderboard_active = 'No' WHERE ID = 1");
  }
  
  if (!in_array('global_scores', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `global_scores` text collate utf8_unicode_ci NOT NULL
      AFTER `leaderboard_active`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET global_scores = 'false' WHERE ID = 1");
  }
   
  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('single_publish', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `single_publish` text collate utf8_unicode_ci NOT NULL
      AFTER `feed_cat`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET single_publish = 'false' WHERE ID = 1");
  }

  if (!in_array('single_catid', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `single_catid` text collate utf8_unicode_ci NOT NULL
      AFTER `single_publish`
    ");
  }

  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('guest_scores', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `guest_scores` text collate utf8_unicode_ci NOT NULL
      AFTER `global_scores`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET guest_scores = 'false' WHERE ID = 1");
  }
  
  if (!in_array('guest_prefix', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `guest_prefix` text collate utf8_unicode_ci NOT NULL
      AFTER `guest_scores`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET guest_prefix = 'guest_' WHERE ID = 1");
  }
  
  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('embed_flashcode', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `embed_flashcode` text collate utf8_unicode_ci NOT NULL
      AFTER `guest_prefix`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET embed_flashcode = 'manually' WHERE ID = 1");
  }
  
  if (!in_array('use_template', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `use_template` text collate utf8_unicode_ci NOT NULL
      AFTER `embed_flashcode`
    ");
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET use_template = 'No' WHERE ID = 1");
  } 

  if (!in_array('template', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `template` text collate utf8_unicode_ci NOT NULL
      AFTER `use_template`
    ");
    
        $default_theme = mysql_escape_string('<p>
  <div style=\"float:left;margin-right: 10px; margin-bottom: 10px;\">
    %THUMB%
  </div>
  <h2>Description</h2>
  <p>%DESCRIPTION%</p>
  <h2>Instructions</h2>
  <p>%INSTRUCTIONS%</p>
</p>');
    
    $wpdb->query("UPDATE ".MYARCADE_SETTINGS_TABLE." SET template = '$default_theme' WHERE ID = 1");
  }
  
  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM ".MYARCADE_SETTINGS_TABLE);
  
  if (!in_array('heyzap', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `".MYARCADE_SETTINGS_TABLE."`
      ADD `heyzap` text collate utf8_unicode_ci NOT NULL
      AFTER `template`
    ");
  }
  
  // Update Category settings
  $arcadelite_settings  = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
   
  if (is_serialized($arcadelite_settings->game_categories) === FALSE) {
    // Include the feed game categories
    include('modules/myabp_feedcats.php');
    
    $old_cats = explode(', ', $arcadelite_settings->game_categories);

    foreach ($old_cats as $cat) {
      for ($i=0; $i<count($feedcategories); $i++) {
        if ($feedcategories[$i]['Name'] == $cat) {
          $feedcategories[$i]['Status'] = 'checked';
          break;
        }
      }
    }
  
    // Add to Database
    $ser_feedcategories = serialize($feedcategories);
    $query =  "UPDATE `".MYARCADE_SETTINGS_TABLE."` SET
              `game_categories` = '$ser_feedcategories'";
           
    $wpdb->query($query);
  }  
  
  $wpdb->query("ALTER TABLE `".MYARCADE_SETTINGS_TABLE."` ADD `onlyhighscores` text collate utf8_unicode_ci NOT NULL AFTER `global_scores`");
  $wpdb->query("ALTER TABLE `".MYARCADE_SETTINGS_TABLE."` ADD `parent_category` text collate utf8_unicode_ci NOT NULL AFTER `create_categories`");
  $wpdb->query("ALTER TABLE `".MYARCADE_SETTINGS_TABLE."` ADD `tag` text collate utf8_unicode_ci NOT NULL AFTER `heyzap`");
  $wpdb->query("ALTER TABLE `".MYARCADE_SETTINGS_TABLE."` ADD `first_cat` text collate utf8_unicode_ci NOT NULL AFTER `single_catid`");
  $wpdb->query("ALTER TABLE `".MYARCADE_SETTINGS_TABLE."` ADD `first_cat` text collate utf8_unicode_ci NOT NULL AFTER `single_catid`");
  $wpdb->query("ALTER TABLE `".MYARCADE_SETTINGS_TABLE."` ADD `allow_user_post` text collate utf8_unicode_ci NOT NULL AFTER `tag`");
}

/**
 * @brief Updates the posts meta to avoid conflicts with All In One Seo
 *        and maybe other plugins
 */
function arcadelite_upgrade_post_metas() {
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


/*******************************************************************************
 * O U T P U T  F U N C T I O N S
 ******************************************************************************/

/*function arcadelite_meta() {
  echo '<meta name="myarcadeplugin" content="lite '.MYARCADE_VERSION. '" />'."\n";
}*/

/**
 * @brief Shows a game swf
 */
function get_game($gameID, $fullsize = false, $preview = false) {
  global $wpdb, $post;

  if ($preview == false) {
    $game_url   = get_post_meta($gameID, "mabp_swf_url", true);
    $gamewidth  = intval(get_post_meta($gameID, "mabp_width", true));
    $gameheight = intval(get_post_meta($gameID, "mabp_height", true));
    $game_variant = get_post_meta($gameID, "mabp_game_type", true);
  }
  else {
    $game = $wpdb->get_row("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'");
    $game_url = $game->swf_url;
    $game_variant =  $game->game_type;
    $gamewidth  = intval($game->width);
    $gameheight = intval($game->height);
  }
  
  $arcadelite_settings  = $wpdb->get_row("SELECT * FROM ".MYARCADE_SETTINGS_TABLE);
  $maxwidth   = intval($arcadelite_settings->maxwidth);
  
  $heyzap = array();
  $heyzap = unserialize($arcadelite_settings->heyzap);
  
      
  // Check if we have a Mochimedia ID
  if ( !empty($arcadelite_settings->mochiads_id ) && ( empty($game_variant) || ($game_variant == 'mochi') ) ) {
    $game_url .= '?affiliate_id='.$arcadelite_settings->mochiads_id;
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
  $code = '<embed src="'.$game_url.'" menu="false" quality="high" width="'.$gamewidth.'" height="'.$gameheight.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
  
  
  // Show the game
  return $code;
} // END - get_game


/**
 * @brief Check the game width. If the game is larger as defined max. width 
 *        return true, otherwise false.
 */
function arcadelite_check_width($postid) {
  global $wpdb, $post;
  
  $result = false;
  $settings_table  = $wpdb->prefix . "myarcadesettings";
  
  $maxwidth   = intval($wpdb->get_var("SELECT maxwidth FROM ".MYARCADE_SETTINGS_TABLE));
  $gamewidth  = intval(get_post_meta($postid, "mabp_width", true));
  
  if ($gamewidth > $maxwidth) {
    $result = true;
  }
  
  return $result;
}


/**
 * @brief Embed the flash code if activated
 */
function arcadelite_embed_handler($content) {
  global $wpdb, $post;
  
  // Do this only on single posts ...
  if ( is_single() ) {
    $embed      = $wpdb->get_var("SELECT embed_flashcode FROM ".MYARCADE_SETTINGS_TABLE);
    $game_url   = get_post_meta($post->ID, "mabp_swf_url", true);
  
    // Check if this option is enabled and if this is a game
    if ( ($embed != 'manually') && !empty($game_url) ) {
      
      // Get the embed code of the game
      $embed_code = get_game($post->ID);
      
      // Add the embed code to the content
      if ( $embed == 'top' ) {
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
?>