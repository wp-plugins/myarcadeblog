<?php
/* 
Plugin Name:  My Arcade Blog for Mochiads
Plugin URI:   http://netreview.de/wordpress/create-your-own-wordpress-arcade-blog-like-fungames24net
Description:  Turn your wordpress blog into a mochiads game portal.
Version:      1.5
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
define (MYARCADE_VERSION, '1.5');

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

      
/*
 * @brief Shows the admin menu
 */
function myarcade_admin_menu() {

  add_menu_page('My Arcade', 'My Arcade', 8, __FILE__, 'myarcade_show_stats');
  add_submenu_page(__FILE__, 'Settings',    'Settings',   8, 'myarcade-edit-feed',  'myarcade_edit_feed');
  add_submenu_page(__FILE__, 'Feed Games',  'Feed Games', 8, 'myarcade-feed-games',    'myarcade_feed_games');
  add_submenu_page(__FILE__, 'Games To Blog', 'Games To Blog', 8, 'myarcade-add-games-to-blog', 'myarcade_add_games_to_blog');
}


/*
 * @brief 
 */
function myarcade_header() {
  
  add_cssstyle();
    
  echo '<div class="wrap">';
  echo '<h2>My Arcade Blog</h2>';
}


/*
 * @brief
 */
function myarcade_footer() {
  global $myarcade_version;

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


/*
 * @brief Shows the ovewview page in WordPress backend
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

/*
 * @brief Shows the settings page and handels all settings changes 
 */
function myarcade_edit_feed() {
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
  $cat_Action = '';
  $cat_Adventure = '';
  $cat_BoardGames = '';
  $cat_Casino = '';
  $cat_Customize = '';
  $cat_DressUp = '';
  $cat_Driving = '';
  $cat_Fighting = '';
  $cat_HighScores = '';
  $cat_Other = '';
  $cat_Puzzles = '';
  $cat_Shooting = '';
  $cat_Sports = '';
  
  $settings_table     = $wpdb->prefix . "myarcadesettings";
    
  $action = $_POST['feedaction'];
  
  if ($action == 'save') {
  
    // Get POST data
    $mochiurl       = $_POST['mochiurl'];
    $mochiid        = $_POST['mochiid'];
    $feedcount      = $_POST['feed_count'];
    $gamecount      = $_POST['game_count'];
    $publishstatus  = $_POST['publishstatus'];
    $schedtime      = $_POST['schedtime'];
    $downloadthumbs = $_POST['downloadthumbs'];
    $downloadgames  = $_POST['downloadgames'];
    $categories_post = $_POST['gamecats'];
    $create_game_cats = $_POST['createcats'];
                
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
          mochiads_url    ='$mochiurl',
          mochiads_id     ='$mochiid',
          feed_games      ='$feedcount',
          publish_games   ='$gamecount',
          publish_status  ='$publishstatus',
          download_thumbs ='$downloadthumbs',
          download_games  ='$downloadgames',
          schedule        ='$schedtime',
          game_categories ='$categories_str',
          create_categories = '$create_game_cats'
    ");

    echo '<p class="noerror">Your settings have been updated!</p>';
    
  } // END - if action
  

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
        case 'Fighting':
          $cat_Fighting = 'checked';
          break;
        case 'HighScores':
          $cat_HighScores = 'checked';
          break;
        case 'Other':
          $cat_Other = 'checked';
          break;
        case 'Puzzles':
          $cat_Puzzles = 'checked';
          break;
        case 'Shooting':
          $cat_Shooting = 'checked';
          break;
        case 'Sports':
          $cat_Sports = 'checked';
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
    <form method="post" name="editfeed">
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
          <input type="checkbox" name="gamecats[]" value="HighScores" <?php echo $cat_HighScores; ?>>&nbsp;High Scores<br />
          <input type="checkbox" name="gamecats[]" value="Other"      <?php echo $cat_Other; ?>>&nbsp;Other<br />
          <input type="checkbox" name="gamecats[]" value="Puzzles"    <?php echo $cat_Puzzles; ?>>&nbsp;Puzzles<br />
          <input type="checkbox" name="gamecats[]" value="Shooting"   <?php echo $cat_Shooting; ?>>&nbsp;Shooting<br />
          <input type="checkbox" name="gamecats[]" value="Sports"     <?php echo $cat_Sports; ?>>&nbsp;Sports
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
        <td colspan="3">
          <input type="submit" name="submit" value="Save Settings">
        </td>
      </tr>
      </table>
    </form>
    
  <?php
   
  myarcade_footer();
}


/*
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


/*
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

/*
 * @brief Gets a feed from mochiads and adds new games into the games table 
 */
function myarcade_feed_games() {
  global $wpdb;

  $new_games = 0;
  $add_game = false;

  $home = get_option('home');

  myarcade_header();

  myarcade_prepare_environment();

  $game_table = $wpdb->prefix . "myarcadegames";
  $settings_table   = $wpdb->prefix . "myarcadesettings";

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
    echo '<font style="color:red;">Feed not found. Please check or remove your MochiadsID!</font><br /><br />';
    
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

    if (!$game_uuid) {

      // Check game categories and add game if it's category has been selected
      $add_game   = false;
      $categories = '';
      foreach($game->categories as $gamecat) {
        foreach ($myarcade_categories as $cat) {
          if ($cat == $gamecat) {
            $add_game = true;
            break;
          }
        }

        if ($add_game == true) break;
      }

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

      $game->name         = str_replace("'", "\\'", $game->name);
      $game->description  = str_replace("'", "\\'", $game->description);
      $game->instructions = str_replace("'", "\\'", $game->instructions);
      $game->thumbnail_url = str_replace("'", "\\'", $game->thumbnail_url);
      $game->swf_url      = str_replace("'", "\\'", $game->swf_url);
      $tags               = str_replace("'", "\\'", $tags);

      // Put this game into games table
      $query = "INSERT INTO ".$game_table." (
                uuid,
                name,
                slug,
                categories,
                description,
                tags,
                instructions,
                controls,
                height,
                width,
                thumbnail_url,
                swf_url,
                created,
                leaderboard_enabled,
                status
              ) values (
                '$game->uuid',
                '$game->name',
                '$game->slug',
                '$categories',
                '$game->description',
                '$tags',
                '$game->instructions',
                '$game_control',
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

/*
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

      $categs = explode(",",$game->categories);

      for($x=0; $x < count($categs); $x++) {
        $categs[$x] = str_replace("-"," ",$categs[$x]);
        array_push ($cat_id, get_cat_id($categs[$x])); 
      }

      // Download Thumbs?
      if ($download_thumbs == true) {
        $thumb = '';

        $thumb = myarcade_get_file($game->thumbnail_url, true);

        if ($thumb) {
          $path_parts = pathinfo($game->thumbnail_url);
          $extension  = $path_parts['extension'];
          $file_name  = $game->slug.'.'.$extension;
          
          $result = file_put_contents($thumbs_dir.$file_name, $thumb);
          
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

        if ($game_swf) {
          $file_name  = urldecode(basename($game->swf_url));
          $result     = file_put_contents($games_dir.$file_name, $game_swf);

          if ($result == false) {
            echo '<p class="myerror">Game download <strong>failed</strong>! Ignore this game..</p>';
            // Set status to ignored
            $query = "UPDATE ".$game_table." SET status = 'ignored' where id = $game->id";
            $wpdb->query($query);
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
      $post = array();
      $post['post_title']     = $game->name;
      $post['post_content']   = '<img src="'.$game->thumbnail_url.'" style="float:left;margin-right:5px;">'.$game->description;
      $post['post_status']    = 'publish';
      $post['post_author']    = 1;
      $post['post_type']      = 'post';
      $post['post_category']  = $cat_id;
      $post['post_date']      = $publish_date;
      $post['tags_input']     = $game->tags;

      $post_id = wp_insert_post($post); 

      add_post_meta($post_id, 'description',    $game->description);
      add_post_meta($post_id, 'instructions',   $game->instructions);
      add_post_meta($post_id, 'height',         $game->height);
      add_post_meta($post_id, 'width',          $game->width);
      add_post_meta($post_id, 'swf_url',        $game->swf_url);  
      add_post_meta($post_id, 'thumbnail_url',  $game->thumbnail_url);

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

} // END - Games To Blog


/*******************************************************************************
 * S E T U P  F U N C T I O N S
 ******************************************************************************/

/*
 * @brief Increases the memory limit and disables time out 
 */
function myarcade_prepare_environment() {

  $cant     = '<p class="error">ERROR! Can\'t set value for ';
  $contact  = '. Please contact your administrator!</p>';

  if ( !(ini_set("max_execution_time", 0)) ) 
    echo $cant .'max_execution_time'. $contact;

  if ( !(ini_set("default_socket_timeout", 480)) )
    echo $cant .'default_socket_timeout'. $contact;

  if ( !(ini_set("memory_limit", "128M")) )
    if ( !(ini_set("memory_limit", "64M")) )
      echo $cant .'memory_limit'. $contact;

  if ( !(set_time_limit(0)) )
    echo $cant .'set_time_limit'. $contact;

} // END - myarcade_prepare_environment


/*
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
      `name` text collate utf8_unicode_ci NOT NULL,
      `slug` text collate utf8_unicode_ci NOT NULL,
      `categories` text collate utf8_unicode_ci NOT NULL,
      `description` text collate utf8_unicode_ci NOT NULL,
      `tags` text collate utf8_unicode_ci NOT NULL,
      `instructions` text collate utf8_unicode_ci NOT NULL,
      `controls` text collate utf8_unicode_ci NOT NULL,
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


  $settings_table = $wpdb->prefix . "myarcadesettings";

 if ($wpdb->get_var("show tables like '$settings_table'") != $settings_table) {

    $sql = "CREATE TABLE `$settings_table` (
      `ID` int(11) NOT NULL auto_increment,
      `mochiads_url`    text collate utf8_unicode_ci NOT NULL,
      `mochiads_id`     text collate utf8_unicode_ci NOT NULL,
      `feed_games`      text collate utf8_unicode_ci NOT NULL,
      `publish_games`   text collate utf8_unicode_ci NOT NULL,
      `publish_status`  text collate utf8_unicode_ci NOT NULL,
      `download_thumbs` text collate utf8_unicode_ci NOT NULL,
      `download_games`  text collate utf8_unicode_ci NOT NULL,
      `schedule`        text collate utf8_unicode_ci NOT NULL,
      `game_categories` text collate utf8_unicode_ci NOT NULL,
      `create_categories` text collate utf8_unicode_ci NOT NULL,
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
              `create_categories`
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
                  ''
              )";

     $wpdb->query($query);
  }
}


/*******************************************************************************
 * S T Y L E S  F U N C T I O N S
 ******************************************************************************/

/*
 * @brief Includes CSS-Styles
 */
function add_cssstyle() {
?>
<style type="text/css">

.myerror {
  color: red;
  font-weight: bold;
  background-color: #ffebe8;
  border: 2px solid #c00;
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

</style>
<?php
} // END - add_cssstyle


/*******************************************************************************
 * G A M E  O U T P U T  F U N C T I O N S
 ******************************************************************************/

/*
 * @brief Shows a game swf
 */
function get_game($postid) {
  global $post;

  $game_url = get_post_meta($postid, "swf_url", true); 

  // Show the game
  $code = '<embed src="'.$game_url.'" menu="false" quality="high" width="'.get_post_meta($postid, "width", true).'" height="'.get_post_meta($postid, "height", true).'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';

  return $code;
} // END - get_game

?>
