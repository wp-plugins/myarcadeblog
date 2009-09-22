<?php
/* 
Plugin Name:  MyArcadeBlog
Plugin URI:   http://netreview.de/wordpress/create-your-own-wordpress-arcade-blog-like-fungames24net
Description:  Turn your wordpress blog into an arcade game portal.
Version:      1.8
Author:       Daniel B.
Author URI:   http://netreview.de
*/

/*
  Copyright 2009  NetReview.de (Daniel B.)  (email : kontakt@netreview.de)

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
define (MYARCADE_VERSION, '1.8');

// You need at least PHP Version 5.2.0+ to run this plugin
define (MYARCADE_PHP_VERSION, '5.2.0');


/**
 *******************************************************************************
 *   H O O K
 *******************************************************************************
 */
add_action( 'admin_menu', 'myarcade_admin_menu' );
register_activation_hook( __FILE__, 'myarcade_install' );

if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins' );


/**
 * @brief Shows the admin menu
 */
function myarcade_admin_menu() {

  add_menu_page('My Arcade', 'My Arcade', 8, __FILE__, 'myarcade_show_stats');
  add_submenu_page(__FILE__, 'Feed Mochi Games',  'Feed Mochi Games', 8, 'myarcade-feed-games',    'myarcade_feed_games');
  add_submenu_page(__FILE__, 'Games To Blog', 'Games To Blog', 8, 'myarcade-add-games-to-blog', 'myarcade_add_games_to_blog');
  add_submenu_page(__FILE__, 'Import Games','Import Games', 8, 'myarcade-import-games',    'myarcade_import_games');
  add_submenu_page(__FILE__, 'Settings',    'Settings',   8, 'myarcade-edit-settings',  'myarcade_edit_settings');
}


/**
 * @brief 
 */
function myarcade_header() {
  
  add_cssstyle();
    
  echo '<div class="wrap">';
  echo '<h2>My Arcade Blog</h2>';
}


/**
 * @brief
 */
function myarcade_footer() {

  $dollar = WP_PLUGIN_URL.'/myarcadeblog/paypal-dollar.png';
  $euro   = WP_PLUGIN_URL.'/myarcadeblog/paypal-euro.png';
  $pound  = WP_PLUGIN_URL.'/myarcadeblog/paypal-pound.png';
  
  ?>
    <table class='form-table'>
    <tr><td>
    <p>
      <div class="mg_paypal">
        <form style="display:inline;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_new"><input name="cmd" value="_s-xclick" type="hidden"><input name="hosted_button_id" value="6513813" type="hidden"><input name="item_name" value="MyArcadeBlog Plugin Donation" type="hidden"><input name="no_note" value="1" type="hidden"><input name="currency_code" value="USD" type="hidden"><input name="tax" value="0" type="hidden"><input name="bn" value="PP-DonationsBF" type="hidden"><input src="<?php echo $dollar; ?>" name="submit" alt="Donation via PayPal : fast, simple and secure!" border="0" type="image"></form>
        <form style="display:inline" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_new"><input name="cmd" value="_s-xclick" type="hidden"><input name="hosted_button_id" value="6513268" type="hidden"><input name="item_name" value="MyArcadeBlog Plugin Donation" type="hidden"><input name="no_note" value="1" type="hidden"><input name="currency_code" value="EUR" type="hidden"><input name="tax" value="0" type="hidden"><input name="bn" value="PP-DonationsBF" type="hidden"><input src="<?php echo $euro; ?>" name="submit" alt="Donation via PayPal : fast, simple and secure!" border="0" type="image"></form>
        <form style="display:inline" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_new"><input name="cmd" value="_s-xclick" type="hidden"><input name="hosted_button_id" value="6513917" type="hidden"><input name="item_name" value="MyArcadeBlog Plugin Donation" type="hidden"><input name="no_note" value="1" type="hidden"><input name="currency_code" value="GBP" type="hidden"><input name="tax" value="0" type="hidden"><input name="bn" value="PP-DonationsBF" type="hidden"><input src="<?php echo $pound; ?>" name="submit" alt="Donation via PayPal : fast, simple and secure!" border="0" type="image"></form>
      </div>
      Does this plugin make you happy? Do you find it useful? 
      <br />If you think this plugin helps you, please consider donating. 
      <br /><strong>Thank you for your support!</strong>
    </p>
    </td></tr>
    <tr><td>
      <strong>MyArcadeBlog v<?php echo MYARCADE_VERSION;?></strong> | <strong><a href="http://netreview.de" target="_blank">NetReview.de</a> </strong> 
    </td></tr>    
    
    </table>
    </div>     
  <?php   
}


/**
 * @brief Shows the overview page in WordPress backend
 */
function myarcade_show_stats() {
  global $wpdb;
  
  myarcade_header();
  
  $new_games = 0;
  
  $game_table      = $wpdb->prefix . "myarcadegames";
  $settings_table  = $wpdb->prefix . "myarcadesettings";
  
  $unpublished_games  = $wpdb->get_var("SELECT COUNT(*) FROM ".$game_table." WHERE status = 'new'");
  $myarcade_settings  = $wpdb->get_row("SELECT * FROM $settings_table");
  

  if ($unpublished_games > 0) {    
      $publish_games = 'Add Games to Blog';   
      $my_message =  '<p class="button"><a href="?page=myarcade-add-games-to-blog">'.$publish_games.'</a></p>';
  }
  else {
    $unpublished_games = 0;
    $my_message  =  '<p class="myerror">You have <strong>NO</strong> unpublished games! ';
    $my_message .=  '<p class="button"><a href="?page=myarcade-feed-games">Feed games</a></p>';
  }
  
  
  ?>
    <h3>Overview</h3>
    <table class="widefat fixed" cellspacing="0">
      <thead>
        <tr> 
          <th scope="col" class="manage-column column-title">Mochiads ID</th>
          <th scope="col" class="manage-column column-title">Unpublished Games</th>
          <th scope="col" class="manage-column column-title">Feed Games</th>
          <th scope="col" class="manage-column column-title">Publish Games</th>
          <th scope="col" class="manage-column column-title">Publish Status</th>
          <th scope="col" class="manage-column column-title">Publish Interval (min.)</th>
          <th scope="col" class="manage-column column-title">Download Thumbnails</th>
          <th scope="col" class="manage-column column-title">Download Games</th>
        </tr>
      </thead>
      <tr>
        <td scope="col"><?php echo $myarcade_settings->mochiads_id;?></td>
        <td scope="col"><?php echo $unpublished_games; ?></td>
        <td scope="col"><?php echo $myarcade_settings->feed_games;?></td>
        <td scope="col"><?php echo $myarcade_settings->publish_games;?></td>
        <td scope="col"><?php echo $myarcade_settings->publish_status;?></td>
        <td scope="col"><?php echo $myarcade_settings->schedule;?></td>
        <td scope="col"><?php echo $myarcade_settings->download_thumbs;?></td>
        <td scope="col"><?php echo $myarcade_settings->download_games;?></td>
    </tr>
    </table>
  <?php
  
  echo $my_message;
    
  myarcade_footer();

}


/**
 * @brief Shows the settings page and handels all setting changes 
 */
function myarcade_edit_settings() {
  global $wpdb;
    
  myarcade_header();
    
  // Directory Locations
  $games_dir  = ABSPATH .'wp-content/games/';
  $thumbs_dir = ABSPATH .'wp-content/thumbs/';
  
  
  $publishposts       = '';
  $pendingreview      = '';
  $scheduled          = '';
  $downloadthumbs_yes = '';
  $downloadthumbs_no  = '';
  $downloadgames_yes  = '';
  $downloadgames_no   = '';
  $categories_str     = '';
  $cat_Action         = '';
  $cat_Adventure      = '';
  $cat_BoardGames     = '';
  $cat_Casino         = '';
  $cat_Customize      = '';
  $cat_DressUp        = '';
  $cat_Driving        = '';
  $cat_Education	  = '';       // Since 1.8
  $cat_Fighting       = '';
  //$cat_HighScores     = '';     // Removed since 1.8
  $cat_Other          = '';
  $cat_Puzzles        = '';
  $cat_Rhythm		  = '';       // Since 1.8
  $cat_Shooting       = '';
  $cat_Sports         = '';
  $cat_Strategy       = '';       // Since 1.8
  
  $game_table     = $wpdb->prefix . "myarcadegames";
  $settings_table = $wpdb->prefix . "myarcadesettings";
    
  $action = $_POST['feedaction'];
  
  if ($action == 'save') {
  
    // Get POST data
    $mochiurl         = trim($_POST['mochiurl']);
    $mochiid          = trim($_POST['mochiid']);
    $feedcount        = trim($_POST['feed_count']);
    $gamecount        = trim($_POST['game_count']);
    $publishstatus    = $_POST['publishstatus'];
    $schedtime        = trim($_POST['schedtime']);
    $downloadthumbs   = $_POST['downloadthumbs'];
    $downloadgames    = $_POST['downloadgames'];
    $categories_post  = $_POST['gamecats'];
    $create_game_cats = $_POST['createcats'];
    $maxwidth         = trim($_POST['maxwidth']);
                    
    // Correct categorie names
    $cat_count = count($categories_post);
    
    for ($x = 0; $x < $cat_count; $x++) {
      switch ($categories_post[$x]) {
        case 'BoardGames':
          $categories_post[$x] = 'Board Games';
          break;
        case 'DressUp':
          $categories_post[$x] = 'Dress-Up';
          break;
      }
    }
    
    if ($cat_count > 0) {
      $categories_str = implode(', ', $categories_post);
    }
    else {
      echo '<p class="myerror">You haven\'t selected any category!</p>';
    }    
    
    // Create categories if checked
    if (($create_game_cats == 'Yes') && ($cat_count > 0)) {
      
      $blog_categories = get_categories();
      
      foreach ($categories_post as $game_cat) {
        
        $category_present = false;
        
        foreach ($blog_categories as $blog_cat) {
          if ($game_cat == $blog_cat->name) {
            $category_present = true;
            break;
          }
        }
        
        if ($category_present == false) {
          $create_cat = array("cat_name" => $game_cat, 
                              "category_description" => "Flash $game_cat Games"
                              );
          $cat_id = wp_insert_category($create_cat);
        
          if (!$cat_id) {
              echo '<p class="myerror">Failed to create category: '.$game_cat.'</p>';            
          }
        }
      }    
    }        
    
    // Save settings
    $wpdb->query("UPDATE $settings_table SET 
          mochiads_url      ='$mochiurl',
          mochiads_id       ='$mochiid',
          feed_games        ='$feedcount',
          publish_games     ='$gamecount',
          publish_status    ='$publishstatus',
          download_thumbs   ='$downloadthumbs',
          download_games    ='$downloadgames',
          schedule          ='$schedtime',
          game_categories   ='$categories_str',
          create_categories = '$create_game_cats',
          maxwidth          = '$maxwidth'
    ");
    
    echo '<p class="noerror">Your settings have been updated!</p>';
    
  } // END - if action
  else {
    $action = $_POST['resettab'];
    
    if ($action == 'reset') {
      $validate = $_POST['validreset'];
      
      if ($validate == 'sure') {
        // Reset Game Table
        $wpdb->query("TRUNCATE TABLE $game_table");
        echo '<p class="noerror">All feeded games have been deleted.</p>';
      }
      else {
        echo '<p class="myerror">If you want to delete all feeded games, you have to check "Yes, I\'m sure.."</p>';
      }
    }
  }
  
  $myarcade_settings  = $wpdb->get_row("SELECT * FROM $settings_table");
  
  // Check Radio-Buttons
  if ($myarcade_settings->download_games == 'Yes') {
    if (!is_writable($games_dir)) {
      echo '<p class="myerror">The games directory "' . $games_dir . '" must be writeable (chmod 777) in order to download the games.</p>';
    }
      
    $downloadgames_yes = 'checked';
  }
  else {
    $downloadgames_no = 'checked';
  }
    
  if ($myarcade_settings->download_thumbs == 'Yes') {
    if (!is_writable($thumbs_dir)) {
      echo '<p class="myerror">The thumbails directory "' . $thumbs_dir . '" must be writeable (chmod 777) in order to download the thumbnails.</p>';
    }
    $downloadthumbs_yes = 'checked';
  }
  else {
    $downloadthumbs_no = 'checked';
  }
  
  switch ($myarcade_settings->publish_status) {
    case 'Publish':
      $publishposts = 'checked';
      break;
    case 'PendingReview':
      $pendingreview = 'checked';
      break;
    case 'Scheduled':
      $scheduled = 'checked';
      break;
    default:
      $publishposts = 'checked';
      break;
  }
  
  
  if ($myarcade_settings->game_categories) {
     
    $categories = explode(', ', $myarcade_settings->game_categories); 
    
    // Which categories have been selected..
    foreach ($categories as $cat) {
      switch ($cat) {
        case 'Action':
          $cat_Action = 'checked';
          break;
        case 'Adventure':
          $cat_Adventure = 'checked';
          break;
        case 'Board Games':
          $cat_BoardGames = 'checked';
          break;
        case 'Casino':
          $cat_Casino = 'checked';
          break;
        case 'Customize':
          $cat_Customize = 'checked';
          break;
        case 'Dress-Up':
          $cat_DressUp = 'checked';
          break;
        case 'Driving':
          $cat_Driving = 'checked';
          break;
        case 'Education':
          $cat_Education = 'checked';
          break;          
        case 'Fighting':
          $cat_Fighting = 'checked';
          break;
        // Outdated
        /*
        case 'HighScores':
          $cat_HighScores = 'checked';
          break;
        */
        case 'Other':
          $cat_Other = 'checked';
          break;
        case 'Puzzles':
          $cat_Puzzles = 'checked';
          break;
        case 'Rhythm':
          $cat_Rhythm = 'checked';
          break;          
        case 'Shooting':
          $cat_Shooting = 'checked';
          break;
        case 'Sports':
          $cat_Sports = 'checked';
          break;
        case 'Strategy':
          $cat_Strategy = 'checked';
          break;          
      }
    }
  }
  
  if ($myarcade_settings->create_categories == 'Yes') {
    $create_cats = 'checked';
  } else {
    $create_cats = '';
  }

  ?>
    <h3>Settings</h3>
    <form method="post" name="editsettings">
      <input type="hidden" name="feedaction" value="save">
      
      <table cellspacing="15">
      <tr>
        <td>Mochiad Feed URL: </td>
        <td>   
          <input type="text"  name="mochiurl" value="<?php echo $myarcade_settings->mochiads_url; ?>">
        </td>
        <td>Edit this field only if Mochiads Feed URL has been changed!!</td>
      </tr>
      <tr>
        <td>Mochiad ID: </td>
        <td>   
          <input type="text"  name="mochiid" value="<?php echo $myarcade_settings->mochiads_id; ?>">
        </td>
        <td>Put your mochiads id here.</td>
      </tr>
      <tr>
        <td width="150px">Feed Games: </td>
        <td width="250px">
          <input type="text"  name="feed_count" value="<?php echo $myarcade_settings->feed_games; ?>">
        </td>
        <td>How many games should be feeded from the mochi feed? Leave blank if you want to feed all games.</td>
      </tr>      
      <tr>
        <td width="150px">Publish Games: </td>
        <td width="250px">
          <input type="text"  name="game_count" value="<?php echo $myarcade_settings->publish_games; ?>">
        </td>
        <td>How many games should be published at the same time?</td>
      </tr>
      <tr valign="top" height="150px">
        <td>Publish Status:</td>
        <td>
          <input type="radio" name="publishstatus" value="Publish"        <?php echo $publishposts; ?>>&nbsp;Publish<br />
          <input type="radio" name="publishstatus" value="Scheduled"      <?php echo $scheduled; ?>>&nbsp;Scheduled<br />
            <br />
            Time between posts in minutes<br /> (only if Scheduled is checked):<br /><br />
              <input type="text" name="schedtime" value="<?php echo $myarcade_settings->schedule; ?>">
        </td>
        <td>Choose how games should be added as new posts.</td>
      </tr>
      <tr valign="top">
        <td>Download Thumbnails:</td>
        <td>
          <input type="radio" name="downloadthumbs" value="Yes" <?php echo $downloadthumbs_yes; ?>>&nbsp;Yes<br />
          <input type="radio" name="downloadthumbs" value="No"  <?php echo $downloadthumbs_no; ?>>&nbsp;No
        </td>
        <td>Should game thumnails be downloaded to your web server?</td>
      </tr>      
      <tr valign="top">
        <td>Download Games:</td>
        <td>
          <input type="radio" name="downloadgames" value="Yes"  <?php echo $downloadgames_yes; ?>>&nbsp;Yes<br />
          <input type="radio" name="downloadgames" value="No"   <?php echo $downloadgames_no; ?>>&nbsp;No
        </td>
        <td>Should games be downloaded to your web server?</td>
      </tr>    
      <tr valign="top">
        <td>Games Categories:</td>
        <td>
          <input type="checkbox" name="gamecats[]" value="Action"     <?php echo $cat_Action; ?>>&nbsp;Action<br />
          <input type="checkbox" name="gamecats[]" value="Adventure"  <?php echo $cat_Adventure; ?>>&nbsp;Adventure<br />
          <input type="checkbox" name="gamecats[]" value="BoardGames"  <?php echo $cat_BoardGames; ?>>&nbsp;Board Games<br />
          <input type="checkbox" name="gamecats[]" value="Casino"     <?php echo $cat_Casino; ?>>&nbsp;Casino<br />
          <input type="checkbox" name="gamecats[]" value="Customize"  <?php echo $cat_Customize; ?>>&nbsp;Customize<br />
          <input type="checkbox" name="gamecats[]" value="DressUp"    <?php echo $cat_DressUp; ?>>&nbsp;Dress-Up<br />
          <input type="checkbox" name="gamecats[]" value="Driving"    <?php echo $cat_Driving; ?>>&nbsp;Driving<br />
          <input type="checkbox" name="gamecats[]" value="Fighting"   <?php echo $cat_Fighting; ?>>&nbsp;Fighting<br />
          <input type="checkbox" name="gamecats[]" value="Education"   <?php echo $cat_Education; ?>>&nbsp;Education<br />
     <!-- <input type="checkbox" name="gamecats[]" value="HighScores" <?php //echo $cat_HighScores; ?>>&nbsp;High Scores<br /> -->
          <input type="checkbox" name="gamecats[]" value="Other"      <?php echo $cat_Other; ?>>&nbsp;Other<br />
          <input type="checkbox" name="gamecats[]" value="Puzzles"    <?php echo $cat_Puzzles; ?>>&nbsp;Puzzles<br />
          <input type="checkbox" name="gamecats[]" value="Rhythm"     <?php echo $cat_Rhythm; ?>>&nbsp;Rhythm<br />
          <input type="checkbox" name="gamecats[]" value="Shooting"   <?php echo $cat_Shooting; ?>>&nbsp;Shooting<br />
          <input type="checkbox" name="gamecats[]" value="Sports"     <?php echo $cat_Sports; ?>>&nbsp;Sports<br />
          <input type="checkbox" name="gamecats[]" value="Strategy"   <?php echo $cat_Strategy; ?>>&nbsp;Strategy
        </td>
        <td>
          Choose mochiads game categories which should be published.
        </td>
      </tr>
      <tr>
        <td>Create Categories:</td>
        <td>
          <input type="checkbox" name="createcats" value="Yes" <?php echo $create_cats; ?>>&nbsp;Yes
        </td>
        <td>Check this if you want to create selected mochiads categories in your blog.</td>
      </tr>
      <tr>
        <td>Max. Game Width: </td>
        <td>   
          <input type="text"  name="maxwidth" value="<?php echo $myarcade_settings->maxwidth; ?>">
        </td>
        <td>Max. allowed game width in px. (optional)</td>
      </tr>
      <tr>
        <td colspan="3">
          <input class="button-primary" type="submit" name="submit" value="Save Settings">
        </td>
      </tr>
      </table>
    </form>
    
    <div class="reset_table">
      <form method="post" name="editsettings">
      <input type="hidden" name="resettab" value="reset"><input type="checkbox" name="validreset" value="sure"> Yes, I'm sure.. <input class="button-secondary" type="submit" name="submit" value="Reset all feeded games">    
      </form>
      <span class="info">Attention! All feeded games will be deleted! Published posts will not be touched..</span>
    </div>
    <div class="clear"></div>
    
  <?php
   
  myarcade_footer();
}


/**
 * @brief This function is for alternative download using cURL instead of  
 *        file_get_contents 
 */
function myarcade_get_file_curl($url, $binary = false) {
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
function myarcade_get_file($url, $binary = false) {
        
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
    $file_data = myarcade_get_file_curl($url, $binary);
  }
  
  return $file_data;
}


/**
 * @brief Gets a feed from mochiads and adds new games into the games table 
 */
function myarcade_feed_games() {
  global $wpdb;

  $new_games = 0;
  $add_game = false;

  $home = get_option('home');

  myarcade_header();

  myarcade_prepare_environment();

  $game_table     = $wpdb->prefix . "myarcadegames";
  $settings_table = $wpdb->prefix . "myarcadesettings";

  $myarcade_settings = $wpdb->get_row("SELECT * FROM $settings_table");

  $myarcade_categories = explode (', ', $myarcade_settings->game_categories);

  $feed_format ='?format=json';

  // Check if there is a feed limit. If not, feed all games
  if ($myarcade_settings->feed_games > 0) {
    $limit = '&limit='.$myarcade_settings->feed_games;
  }
  else {
    $limit = '';
  }

  // Creat the Mochisads Feed URL
  $mochi_feed = trim($myarcade_settings->mochiads_url)
              . trim($myarcade_settings->mochiads_id) 
              . $feed_format 
              . $limit;

  echo '<h3>Feed Games</h3>';
    
  //====================================
  // Check if json_decode exisits
  if (!function_exists(json_decode)) {   
     $phpversion = phpversion();
    
    if($phpversion < MYARCADE_PHP_VERSION) {
      echo '<font style="color:red;">You need at least PHP 5.2.0 to run this plugin.<br />You have '.$phpversion.' installed.<br />Contact your administrator to update PHP.</font><br /><br />';
    }
    else {
     echo '<font style="color:red;">JSON Support is disabeld in your PHP configuration.<br />Please contact your administrator to activate JSON Support.</font><br /><br />';
    }

    // Show Footer 
    myarcade_footer();
      
    return;
  }  

  //====================================
  // Show the Feed URL
  echo "Your Feed URL: <a href='".$mochi_feed."'>".$mochi_feed."</a><br /><br />";
  
  echo "Downloading feed.. ";
  
  $feed = myarcade_get_file($mochi_feed, false);
  
  // Check, if we got a Error-Page  
  if (!strncmp($feed, "<!DOCTYPE", 9)) {    
    echo '<font style="color:red;">Feed not found. Please check Mochiad Feed URL and MochiadsID!</font><br /><br />';
    
    myarcade_footer();
    
    return;
  }
  
  if ($feed) {
    echo '<font style="color:green;">OK</font><br />';
  }
  else {
    echo '<font style="color:red;">Can\'t download Feed from Mochiads!</font><br />';
    
    myarcade_footer();
    
    return;
  }

  //====================================
  echo "Decode feed.. ";
  
  $json_games = json_decode($feed);

  if ($json_games) {
    echo '<font style="color:green;">OK</font><br /><br />';
  }
  else {
    echo '<font style="color:red;">Can\'t decode Json Feed!</font><br /><br />';
    
    myarcade_footer();
    
    return;
  }
  
  echo '<ul id="gamelist">';

  //====================================
  foreach ($json_games->games as $game) {
    
    // Check, if this game is present in the games table
    $game_uuid = $wpdb->get_var("SELECT uuid FROM ".$game_table." WHERE uuid = '$game->uuid'");
    $game_tag  = $wpdb->get_var("SELECT game_tag FROM ".$game_table." WHERE game_tag = '$game->game_tag'");

    if (!$game_uuid && !$game_tag) {
      // Check game categories and add game if it's category has been selected
      $add_game   = false;
      $categories = '';
      
      // Category-Check
      foreach($game->categories as $gamecat) {
        // Fix: Board Games to Board Game
        if ( !strcmp($gamecat,'Board Game') ) {
          $gamecat = 'Board Games';
        }
                
        foreach ($myarcade_categories as $cat) {
          if ($cat == $gamecat) {
            $add_game = true;
            break;
          }
        }

        if ($add_game == true) break;
      } // END - Category-Check

      if ($add_game == true) {
        $categories = implode(",", $game->categories);
      }
      else {
        continue;
      }

      $tags = implode(",", $game->tags);

      // Controls
      $game_control = '';
      foreach ($game->controls as $control) {
        $game_control .= implode(" = ", $control) . ";";
      }

      /*
      $game->name         = str_replace("'", "\\'",  $game->name);
      $game->description  = str_replace("'", "\\'",  $game->description);
      $game->instructions = str_replace("'", "\\'",  $game->instructions);
      $game->rating       = str_replace("'", "\\'",  $game->rating);
      $game->thumbnail_url = str_replace("'", "\\'", $game->thumbnail_url);
      $game->swf_url      = str_replace("'", "\\'",  $game->swf_url);
      $tags               = str_replace("'", "\\'",  $tags);
      */
      $game->name         = mysql_escape_string($game->name);
      $game->description  = mysql_escape_string($game->description);
      $game->instructions = mysql_escape_string($game->instructions);
      $game->rating       = mysql_escape_string($game->rating);
      $game->thumbnail_url = mysql_escape_string($game->thumbnail_url);
      $game->swf_url      = mysql_escape_string($game->swf_url);
      $tags               = mysql_escape_string($tags);      

      // Put this game into games table
      $query = "INSERT INTO ".$game_table." (
                uuid,
                game_tag,
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
                created,
                leaderboard_enabled,
                status
              ) values (
                '$game->uuid',
                '$game->game_tag',
                '$game->name',
                '$game->slug',
                '$categories',
                '$game->description',
                '$tags',
                '$game->instructions',
                '$game_control',
                '$game->rating',
                '$game->height',
                '$game->width',
                '$game->thumbnail_url',
                '$game->swf_url',
                '$game->created',
                '$game->leaderboard_enabled',
                'new')";

      $wpdb->query($query);

      $new_games++;

      echo '<ol>'.$new_games.': '.$game->name.'</ol>';

    }
  }

  if ($new_games > 0) {
    echo '<p><strong>'.$new_games.' new games were found.</strong></p>';
    echo '<p class="noerror">Now, you can add new games to your blog.</p>';
  }
  else {
    echo '<p class="myerror"><strong>No new games found!<br />You can try to increase the number of "Feed Games" at the settings page or wait until Mochiads updates the feed.</strong></p>';
  }

  myarcade_footer();

} // END - mochi_feed_games


/**
 * @brief Inserts a game as a wordpress post
 */
function myarcade_add_game_post($game) {
    global $wpdb; 
  
      //====================================
      // Create a WordPress post
      $post = array();
      $post['post_title']     = $game['name'];
      $post['post_content']   = '<img src="'.$game['thumb'].'" style="float:left;margin-right:5px;">'.$game['description'];
      $post['post_status']    = 'publish';
      $post['post_author']    = 1;
      $post['post_type']      = 'post';
      $post['post_category']  = $game['categories']; // Category IDs
      $post['post_date']      = $game['date'];
      $post['tags_input']     = $game['tags'];
      
      $post_id = wp_insert_post($post); 

      add_post_meta($post_id, 'description',    $game['description']);
      add_post_meta($post_id, 'instructions',   $game['instructions']);
      add_post_meta($post_id, 'height',         $game['height']);
      add_post_meta($post_id, 'width',          $game['width']);
      add_post_meta($post_id, 'swf_url',        $game['file']);
      add_post_meta($post_id, 'thumbnail_url',  $game['thumb']);  
      add_post_meta($post_id, 'rating',         $game['rating']);
}


/**
 * @brief Adds feeded games to the blog as posts
 */
function myarcade_add_games_to_blog() {
  global $wpdb;

  myarcade_header();

  myarcade_prepare_environment();

  $home = get_option('home');

  // Directory Locations
  $games_dir  = ABSPATH .'wp-content/games/';
  $thumbs_dir = ABSPATH .'wp-content/thumbs/';

  $post_interval = 0;
  $new_games = false;

  $game_table = $wpdb->prefix . "myarcadegames";
  $settings_table   = $wpdb->prefix . "myarcadesettings";

  // Get Settings
  $myarcade_settings = $wpdb->get_row("SELECT * FROM $settings_table");
  
  // Get Game Kategories
  $myarcade_categories = explode (', ', $myarcade_settings->game_categories);  
  
  $unpublished_games  = $wpdb->get_var("SELECT COUNT(*) FROM ".$game_table." WHERE status = 'new'");

  if (intval($myarcade_settings->publish_games) <= $unpublished_games) {
    $game_limit = $myarcade_settings->publish_games;
  } else {
    $game_limit = $unpublished_games;
  }

  // Check Download Directories
  $download_games = false;
  if ($myarcade_settings->download_games == 'Yes') {
    if (!is_writable($games_dir)) {
      echo '<p class="myerror">The games directory "' . $games_dir . '" must be writeable (chmod 777) in order to download games.</p>';
    } else {
      $download_games = true;
    }
  }

  $download_thumbs = false;
  if ($myarcade_settings->download_thumbs == 'Yes') {
    if (!is_writable($thumbs_dir)) {
      echo '<p class="myerror">The thumbails directory "' . $thumbs_dir . '" must be writeable (chmod 777) in order to download thumbnails.</p>';
    } else {
      $download_thumbs = true;
    }
  }
  
  // Disable Error Reporting
  $error_lvl = ini_get('error_reporting');
  ini_set('error_reporting', 0);

  //====================================
  echo "<h3>Games To Blog</h3>";
  echo "<ul>";

  // Publish Games
  for($i = 1; $i <= $game_limit; $i++) {

    // Get a game
    $game = $wpdb->get_row("SELECT * FROM ".$game_table." WHERE status = 'new' order by created asc");

    if ($game) {
      $new_games = true;

      $cat_id = array();
      
      
      if (($i % 2) == 0)
        $bg_color = 'style="background-color: #EFEFEF;"';
      else
        $bg_color = '';
        
      ?>
        <li <?php echo $bg_color; ?>>
          <div>
            <div style="float:left;margin-right:5px">
              <img src="<?php echo $game->thumbnail_url; ?>" alt="">      
            </div>
            <div style="float:left">
            <strong><?php echo $game->name; ?></strong><br /><br />
            <strong>Categories:</strong> <?php echo $game->categories; ?><br />
            
      <?php 

      // Check game categories..
      $categs = explode(",", $game->categories);

      foreach ($categs as $feed_game_cat) {
        // Fix: Board Games to Board Game
        if ( !strcmp($feed_game_cat,'Board Game') ) {
          $feed_game_cat = 'Board Games';  
        }        
        
        foreach ($myarcade_categories as $settings_cat) {
          if ($settings_cat == $feed_game_cat) {
            // Get the blog cat ID
            array_push ($cat_id, get_cat_id($feed_game_cat));
          }            
        }        
      }

      // Download Thumbs?
      if ($download_thumbs == true) {
        $thumb = '';

        $thumb = myarcade_get_file($game->thumbnail_url, true);

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
            $result = file_put_contents($thumbs_dir.$file_name, $thumb);
          }
      
          // Error-Check
          if ($result == false) {
            echo "Thumbnail download <strong>failed</strong>! Using mochiads thumbnail file..<br />";
          }
          else {
            echo "Thumbnail download <strong>OK</strong>!<br />";
            $game->thumbnail_url = $home.'/wp-content/thumbs/'.$file_name;
          }
        } else {
          echo "Thumbnail download <strong>failed</strong>! Using mochiads thumbnail file..<br />";
        }
      }

      // Download Games?
      if ($download_games == true) {
        $game_swf = '';
        
        $game_swf = myarcade_get_file($game->swf_url, true);

        // We got a file
        if ($game_swf) {
          $file_name  = urldecode(basename($game->swf_url));
          
          // Check, if we got a Error-Page  
          if (!strncmp($game_swf, "<!DOCTYPE", 9)) {
              $result = false;  
          }
          else {
            // Save the game to the games directory
            $result = file_put_contents($games_dir.$file_name, $game_swf);
          }

          // Error-Check 
          if ($result == false) {
            echo '<p class="myerror">Game download <strong>failed</strong>! Ignore this game..</p>';
            // Set status to ignored
            $query = "UPDATE ".$game_table." SET status = 'ignored' where id = $game->id";
            $wpdb->query($query);
            echo '</div></div><div style="clear:both;"></div></li>';
            continue;
          } else {
            echo "Game download <strong>OK</strong>!<br />";
            $game->swf_url = $home. '/wp-content/games/'.$file_name;
          }
        } else {
          echo '<p class="myerror">Game download <strong>failed</strong>! Ignore this game..</p>';
          // Set status to ignored
          $query = "UPDATE ".$game_table." SET status = 'ignored' where id = $game->id";
          $wpdb->query($query);
          echo '</div></div><div style="clear:both;"></div></li>';
          continue;
        }
      }

      echo '</div></div><div style="clear:both;"></div></li>';

      if ($myarcade_settings->publish_status == 'Scheduled') {
        $post_interval = $post_interval + $myarcade_settings->schedule;
      }
      else if ($myarcade_settings->publish_status == 'Publish') {
        $post_interval = 0;
      }

      $publish_date = gmdate( 'Y-m-d H:i:s', ( time() + ($post_interval*60) + (get_option( 'gmt_offset' ) * 3600 ) ) );


      //====================================
      // Create a WordPress post
      $mochi_game = array();
      $mochi_game['name']           = $game->name;       
      $mochi_game['file']           = $game->swf_url;
      $mochi_game['width']          = $game->width;
      $mochi_game['height']         = $game->height;
      $mochi_game['thumb']          = $game->thumbnail_url;
      $mochi_game['description']    = $game->description;
      $mochi_game['instructions']   = $game->instructions;
      $mochi_game['tags']           = $game->tags;
      $mochi_game['rating']         = $game->rating;
      $mochi_game['categories']     = $cat_id;   
      $mochi_game['date']           = $publish_date;
      
      // Add game as a post
      myarcade_add_game_post($mochi_game);

      // Mochi-Table: Set post status to poblished
      $query = "update ".$game_table." set status = 'published' where id = $game->id";

      $wpdb->query($query); 
    }

  } // END - for games

  //====================================
  if(!$new_games) {
    echo '<li><p class="myerror">No new games to add. Feed Games first!</p></li>';
  }

  echo "</ul>";
    
  myarcade_footer();
  
  // Restore Error Reporting
  ini_set('error_reporting', $error_lvl);

} // END - Games To Blog

/**
 * 
 * @brief Imports other games than Mochiads games
 */
function myarcade_import_games() {
  
  myarcade_header();
  
  $home = get_option('home');
  
  // Directory Locations
  $game_dir       = 'wp-content/games/';
  $games_dir_abs  = ABSPATH . $game_dir;
  $thumbs_dir     = 'wp-content/thumbs/';
  $thumbs_dir_abs = ABSPATH . $thumbs_dir;  
   
  echo "<h3>Import Game To Blog</h3>";
  
  $action = $_POST['impcostgame'];
  
  if ($action == 'import') {
    // We have a custom game to import
    
    $game = array();
    $game['name']         = $_POST['gamename']; 
    $game['file']         = $home. '/' .$game_dir .$_FILES['gamefile']['name'];
    $game['width']        = intval($_POST['gamewidth']);
    $game['height']       = intval($_POST['gameheight']);
    $game['thumb']        = $home. '/' .$thumbs_dir .$_FILES['thumbfile']['name'];
    $game['description']  = $_POST['gamedescr'];
    $game['instructions'] = $_POST['gameinstr'];
    $game['tags']         = $_POST['gametags'];    
    $game['categories']   = $_POST['gamecategs'];    
    $game['date']         = gmdate( 'Y-m-d H:i:s', ( time() + (get_option( 'gmt_offset' ) * 3600 ) ) );
    
    $game_file_abs   =  $games_dir_abs  . $_FILES['gamefile']['name'];
    $thumbs_file_abs = $thumbs_dir_abs . $_FILES['thumbfile']['name'];
        
    if (move_uploaded_file($_FILES['gamefile']['tmp_name'], $game_file_abs)) {
      echo 'Game Upload Successful<br />';
      
      if (move_uploaded_file($_FILES['thumbfile']['tmp_name'], $thumbs_file_abs)) {
        echo 'Thumb Upload Successful';
        
        // Add the game to blog
        myarcade_add_game_post($game);
        
        echo '<p class="noerror">Import of '.$game['name'].' was succsessfull.</p>';         
      }
      else {
        echo '<p class="myerror">Can\'t upload game thumb.</p>';
      }
    }
    else {
     echo '<p class="myerror">Can\'t upload game file.</p>';
    }    
  }
  
  $categs = get_all_category_ids();
  
  ?>
  <script type="text/javascript">
  
  function myarcade_selectmethod() {
	  if (document.FormImportMethod.importmethod.value == 'Custom')
	   document.getElementById("customg").style.display  = "inline";
	  else 			  
	   document.getElementById("customg").style.display  = "none";
  } // END - myarcade_selectmethod

  function myarcade_chkImportCustom() {

	   if (document.FormCustomGame.gamename.value == "") {
		      alert("Set a name first..");
		      document.FormCustomGame.gamename.focus();
		      return false;
		    }
	 if (document.FormCustomGame.gamefile.value == "") {
	    alert("Select a game file first..");
	    document.FormCustomGame.gamefile.focus();
	    return false;
	  }
   if (document.FormCustomGame.gamewidth.value == "") {
	      alert("Set the game width!");
	      document.FormCustomGame.gamewidth.focus();
	      return false;
	    }	  
   if (document.FormCustomGame.gameheight.value == "") {
       alert("Set the game height!");
       document.FormCustomGame.gameheight.focus();
       return false;
     }  	  
   if (document.FormCustomGame.thumbfile.value == "") {
       alert("Select a thumbnail for this game!");
       document.FormCustomGame.thumbfile.focus();
       return false;
     }
   if (document.FormCustomGame.gamedescr.value == "") {
       alert("There is no game description!");
       document.FormCustomGame.gamedescr.focus();
       return false;
     }

   var categs = false;
   for(var i = 0; i < document.FormCustomGame.elements.length - 1; i++) {
	   if( (document.FormCustomGame.elements[i].type == "checkbox") && (document.FormCustomGame.elements[i].checked == true)) {
      categs = true;
      break;
     }
   }
   
   if (categs == false) {
	   alert("Select at least one category!");
	   return false;
   }
	} // END - myarcade_chkImportCustom
  </script>
  
  <div class="importmethod">
    <form name="FormImportMethod">
      Select an import method:
      <select name="importmethod" onchange='myarcade_selectmethod()'>
        <option value="Custom" >Custom&nbsp;</option>
        <!-- <option value="IBPArcade" >IBPArcade </option>  --> <?php // Comming soon :)?>
      </select>
    </form>
  </div>

<div id="customg">  
  <strong>Import a custom game</strong> 
  <br /><br />
  <form enctype="multipart/form-data" method="post" name="FormCustomGame" onsubmit="return myarcade_chkImportCustom(this.FormCustomGame)">
  <input type="hidden" name="impcostgame" value="import">
  <table>
    <tr>
      <td>Name:</td>
      <td><input name="gamename" type="text" /></td>
      <td><strong>(required)</strong></td>
    </tr>  
    <tr>
      <td>Select a game file:</td>
      <td><input name="gamefile" type="file" /></td>
      <td><strong>(required)</strong></td>
    </tr>
    <tr>
      <td>Game width:</td>
      <td><input name="gamewidth" type="text"  size="5" /> px</td>
      <td><strong>(required)</strong></td>
    </tr>
    <tr>
      <td>Game height:</td>
      <td><input name="gameheight" type="text" size="5" /> px</td>
      <td><strong>(required)</strong></td>
    </tr>  
    <tr>
      <td>Select a thumbnail:</td>
      <td><input name="thumbfile" type="file" /></td>
      <td><strong>(required)</strong></td>
    </tr>  
    <tr valign="top">
      <td>Game description:</td>
      <td><textarea rows="4" cols="30" name="gamedescr"></textarea></td>
      <td><strong>(required)</strong></td>
    </tr>
    <tr valign="top">
      <td>Instructions:</td>
      <td><textarea rows="4" cols="30" name="gameinstr"></textarea></td>
      <td>(optional)</td>
    </tr>  
    <tr>
      <td>Tags (Comma separated):</td>
      <td><input type="text" name="gametags" size="30"></td>
      <td>(optional)</td>
    </tr>
    <tr valign="top">
      <td valign="top">Categories:</td>
      <td> 
      <?php 
        foreach ($categs as $cat_id)
        {
          echo '<input type="checkbox" name="gamecategs[]" value="'.$cat_id.'">&nbsp;'.get_cat_name($cat_id).'<br />';
        }
      ?>
      </td>
      <td><strong>(required)</strong></td>
    </tr>
  </table>
  
  <input class="button-primary"  type="submit" value="Import">
</form>
</div>

 <?php  
  
  myarcade_footer();
  
} // END - Import Games


/*******************************************************************************
 * S E T U P  F U N C T I O N S
 ******************************************************************************/

/**
 * @brief Increases the memory limit and disables time out 
 */
function myarcade_prepare_environment() {
  
  $max_execution_time_l     = 60*5;   // 5 min
  $default_socket_timeout_l = 60*5;   // 5 min
  $memory_limit_l           = "64M";  // Should be enough
  $set_time_limit_l         = 60*5;   // 5 min

  $cant       = '<p class="error"><strong>WARNING!</strong> Can\'t set value for ';
  $contact_1  = '. If MyArcadeBlog doesn\'t work properly please contact your administrator to increase the value of ';
  $contact_2  = ' to ';
  $contact_3  = '</p>';


  // Check max_execution_time
  if ( !(ini_set("max_execution_time", $max_execution_time_l)) )
    echo $cant.'max_execution_time'.$contact_1.'max_execution_time'.$contact_2.$max_execution_time_l.$contact_3;
  
  // Check default_socket_timeout
  //if ( !(ini_set("default_socket_timeout", $default_socket_timeout_l)) )
    //echo $cant.'memory_limit'.$contact_1.'memory_limit'.$contact_2.$default_socket_timeout_l.$contact_3;

  // Check memory limit
    if ( !(ini_set("memory_limit", $memory_limit_l)) )
      echo $cant.'memory_limit'.$contact_1.'memory_limit'.$contact_2.$memory_limit_l.$contact_3;

  if ( !(set_time_limit($set_time_limit_l)) )
    echo $cant.'time_limit'.$contact_1.'time_limit'.$contact_2.$set_time_limit_l.$contact_3;

} // END - myarcade_prepare_environment


/**
 * @brief Plugin installation. Adds needed tables
 */
function myarcade_install() {
  global $wpdb;
  
  // Create needed tables
  $game_table = $wpdb->prefix . "myarcadegames";
  
  if ($wpdb->get_var("show tables like '$game_table'") != $game_table) {

    $sql = "CREATE TABLE `$game_table` (
      `id` int(11) NOT NULL auto_increment,
      `uuid` text collate utf8_unicode_ci NOT NULL,
      `game_tag` text collate utf8_unicode_ci NOT NULL,
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
      `created` text collate utf8_unicode_ci NOT NULL,
      `leaderboard_enabled` text collate utf8_unicode_ci NOT NULL,
      `status` text collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }


  // Check if settings table exisits
  $settings_table = $wpdb->prefix . "myarcadesettings";

  if ($wpdb->get_var("show tables like '$settings_table'") != $settings_table) {
    $sql = "CREATE TABLE `$settings_table` (
      `ID`                int(11) NOT NULL auto_increment,
      `mochiads_url`      text collate utf8_unicode_ci NOT NULL,
      `mochiads_id`       text collate utf8_unicode_ci NOT NULL,
      `feed_games`        text collate utf8_unicode_ci NOT NULL,
      `publish_games`     text collate utf8_unicode_ci NOT NULL,
      `publish_status`    text collate utf8_unicode_ci NOT NULL,
      `download_thumbs`   text collate utf8_unicode_ci NOT NULL,
      `download_games`    text collate utf8_unicode_ci NOT NULL,
      `schedule`          text collate utf8_unicode_ci NOT NULL,
      `game_categories`   text collate utf8_unicode_ci NOT NULL,
      `create_categories` text collate utf8_unicode_ci NOT NULL,
      `maxwidth`          text collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`ID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $query = "INSERT INTO $settings_table (
              `ID` ,
              `mochiads_url` ,
              `mochiads_id` ,
              `feed_games` ,
              `publish_games` ,
              `publish_status` ,
              `download_thumbs` ,
              `download_games` ,
              `schedule` ,
              `game_categories` ,
              `create_categories` ,
              `maxwidth`
              ) VALUES (
                  NULL , 
                  'http://www.mochiads.com/feeds/games/',
                  '',
                  '100',
                  '20',
                  'Publish',
                  'No',
                  'No',
                  '',
                  '',
                  '',
                  ''
              )";

     $wpdb->query($query);
  }
  else {
    // Table allready exisits..
    // Check if the table need to be upgraded
    myarcade_upgrade();
  }
}


/**
 * @brief Upgrades the MyArcadeBlog Tables
 */
function myarcade_upgrade() { 
  global $wpdb;
  
  $settings_table = $wpdb->prefix . "myarcadesettings";  
  $game_table =     $wpdb->prefix . "myarcadegames";
    
  $settings_cols  = $wpdb->get_col("SHOW COLUMNS FROM $settings_table");
  $gametable_cols = $wpdb->get_col("SHOW COLUMNS FROM $game_table");
  
  // Upgrade from 1.5 to 1.6
  if (!in_array('maxwidth', $settings_cols)) {
    $wpdb->query("
      ALTER TABLE `$settings_table`
      ADD `maxwidth` text collate utf8_unicode_ci NOT NULL
      AFTER `create_categories`
    ");
  }
  
  // Upgrade to 1.8
  if (!in_array('rating', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `$game_table`
      ADD `rating` text collate utf8_unicode_ci NOT NULL
      AFTER `controls`
    ");
  } 

  if (!in_array('game_tag', $gametable_cols)) {
    $wpdb->query("
      ALTER TABLE `$game_table`
      ADD `game_tag` text collate utf8_unicode_ci NOT NULL
      AFTER `uuid`
    ");
  }    
}


/*******************************************************************************
 * S T Y L E S  F U N C T I O N S
 ******************************************************************************/

/**
 * @brief Includes CSS-Styles
 */
function add_cssstyle() {
?>
<style type="text/css">

.myerror {
  color: red;
  font-weight: bold;
  background-color: #ffebe8;
  border: 2px dotted #c00;
  padding: 5px;
}

.noerror {
  color: green;
  font-weight: bold;
  padding: 5px;
}


#myfooter {
  margin: 20px 0px 20px 0px;
  padding: 10px;
  text-align: right;
}


.button {
  float: left;
  margin: 10px;
  padding: 10px;
  height: 20px;
}

.button a, a hover {
  text-decoration: none;
}

.mg_paypal {
  float:left;margin-right:10px;
}

.mg_paypal input {
  border:0px;
  background:white;
}

.reset_table {
  float: right;
  background-color: #FFE08F;
  border: 1px dashed #c00;
  padding: 3px;
}
.reset_table span.info{
  display:none;
}

.reset_table:hover span.info{
  display:block;
  position:absolute;
  margin-top: 5px; 
  margin-left: -3px;
  width:250px; 
  background-color: #ffebe8;
  border: 2px groove #c00;  
}

.importmethod {
  padding: 10px;
  border: 1px dashed #00D4FF;
}

.imp_custom {
  margin-top:3px;
  padding:10px;
  border: 1px dashed #FFB32F;
}

#customg {
  border: 1px dashed #FFB32F;
  margin-top:
  3px;padding:10px;
}

.clear {
  clear:both;
}

</style>
<?php
} // END - add_cssstyle


/*******************************************************************************
 * O U T P U T  F U N C T I O N S
 ******************************************************************************/

/**
 * @brief Shows a game swf
 */
function get_game($postid, $fullsize = false) {
  global $wpdb, $post;

  $settings_table  = $wpdb->prefix . "myarcadesettings";
  
  $game_url   = get_post_meta($postid, "swf_url", true);  
  $gamewidth  = intval(get_post_meta($postid, "width", true));
  $gameheight = intval(get_post_meta($postid, "height", true));
  $maxwidth   = intval($wpdb->get_var("SELECT maxwidth FROM ".$settings_table));
  
  // Should the game be resized..
  if (($fullsize == false) && ($maxwidth))  {
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
function myarcade_check_width($postid) {
  global $wpdb, $post;
  
  $result = false;
  $settings_table  = $wpdb->prefix . "myarcadesettings";
  
  $maxwidth   = intval($wpdb->get_var("SELECT maxwidth FROM ".$settings_table));
  $gamewidth  = intval(get_post_meta($postid, "width", true));
  
  if ($gamewidth > $maxwidth) {
    $result = true;
  }
  
  return $result;  
}

?>
