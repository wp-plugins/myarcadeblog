<?php
/*
Module:       This modul contains MyArcadePlugin game fetching functions
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/

defined('MYARCADE_VERSION') or die();

/**
 * @brief Increases the memory limit and disables time out 
 */
function myarcade_prepare_environment($echo = true) {  
  
  if( !ini_get('safe_mode') ) {
    @ini_set("max_execution_time", 600);
    @ini_set("memory_limit", "128M");
    @set_time_limit(600);
  }
  else {
    // save mode is set
    if ($echo)
      echo '<p class="mabp_error"><strong>'.__("WARNING!", MYARCADE_TEXT_DOMAIN).'</strong> '.__("Can't make needed settins, because you have Safe Mode active.", MYARCADE_TEXT_DOMAIN).'</p>';
  }
}

/**
 * @brief Check for json support
 */
function myarcade_check_json($echo) {

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
 * Fetchs and encodes games from the given URL
 *
 * @param string $url
 * @param string $service (mochi, heyzap, ...)
 * @param bolean $echo true = print errors and messages
 * @return mixed fetched games
 */
function myarcade_fetch_games( $args = array() ) {

  $defaults = array(
    'url'     => '', 
    'service' => '', 
    'echo'    => true
  );
  
  $r = wp_parse_args( $args, $defaults );
  extract($r);  
  
  $games = false;

  switch ($service) {
    /** JSON FEEDS **/
    case 'mochi':
    {
      // Check if json_decode exisits
      if ( !myarcade_check_json($echo) ) {
        // Json not found..
        return false;
      }
  
      if ($echo) {
        ?>
        <p class="mabp_info">
          <?php echo __("Your Feed URL", MYARCADE_TEXT_DOMAIN).": <a href='".$url."'>".$url."</a>"; ?>
        </p>

        <p class="mabp_info">
          <?php
          echo __("Downloading feed", MYARCADE_TEXT_DOMAIN).': ';
      }

      //====================================
      // DOWNLOAD FEED
      $feed = myarcade_get_file($url);
      
      if ( !empty($feed['error']) ) {
        if ($echo) {
         echo '<font style="color:red;">'.__("ERROR", MYARCADE_TEXT_DOMAIN).': '.$feed['error'].'</font></p>'; 
        }
        return false;
      }

      // Check, if we got an Error-Page ( Only mochi)
      if ($service == 'mochi') {
        if ( !strncmp($feed['response'], "<!DOCTYPE", 9) ) {
          if ($echo) {
            echo '<font style="color:red;">'.__("Feed not found. Please check Mochi Feed URL and your Publisher ID!", MYARCADE_TEXT_DOMAIN).'</font></p>';
            myarcade_footer();
          }
          return false;
        }
      }

      // Check if have downloaded a file that can be decoded...
      if ($feed['response']) {
        if ($echo) { echo '<font style="color:green;">'.__("OK", MYARCADE_TEXT_DOMAIN).'</font></p>'; }
      }
      else {
        if ($echo) {
          echo '<font style="color:red;">'.__("Can't download feed!", MYARCADE_TEXT_DOMAIN).'</font></p>';
          myarcade_footer();
        }

        return false;
      }
      
      //====================================
      // DECODE DOWNLOADED FEED
      if ($echo) {
        ?><p class="mabp_info"><?php
        echo __("Decode feed", MYARCADE_TEXT_DOMAIN).": ";
      }

      // Decode the downloaded json feed
      $games = json_decode($feed['response']);
      
      // Check if the decode was successfull
      if ($games) {
        if ($echo) { 
          echo ' <font style="color:green;">'.__("OK", MYARCADE_TEXT_DOMAIN).'</font></p>'; 
        }
      }
      else {
        if ($echo) {
          echo ' <font style="color:red;">'.__("Failed to decode the downloaded feed!", MYARCADE_TEXT_DOMAIN).'</font></p>';
          myarcade_footer();
        }

        return false;
      }
    } break;

    default:
    {
      // ERROR
    } break;
  
  } // end switch

  return $games;
}


/**
 * @brief Gets a feed from mochiads and adds new games into the games table 
 */
function myarcade_feed_games($MochiGameTag = '', $cronfeeding = false, $settings = array()) {
  global $wpdb;

  $new_games = 0;
  $add_game = false;
  $tag = '';
  
  $mochi  = get_option('myarcade_mochi');
  $feedcategories = get_option('myarcade_categories');
  
  // Check if we should print out messages...
  if ( !empty($MochiGameTag) || ($cronfeeding == true) ) { 
    $echo = false; 
  } else { 
    $echo = true; 
  }
  
  myarcade_header($echo);
  myarcade_prepare_environment($echo);
  
  // Init settings var's
  if ( empty($settings) ) {
    $settings = $mochi;
    $settings['method'] = 'latest';
    $settings['feedversion'] = 'old';
  }

  /**
   * Generate the Feed URL
   */
  $feed_format ='?format=json';

  // Check if there is a feed limit. If not, feed all games
  if ($settings['limit'] > 0) {
    $limit = '&limit='.$settings['limit'];
  } else $limit = '';

  // Check if this is a auto post game...
  if ( !empty($MochiGameTag) ) {
    // Here commes the game_tag...
    $settings['special'] = $MochiGameTag;
    $limit    = '';
  }
  
  // Check if this is a old or new way of feeding
  if ( $settings['feedversion'] == 'old' ) {
    // Overwrite limit if this is a cron feeding
    if ($cronfeeding == true) { $limit = '&limit=1'; }

    // Check if this is offset-feeding..
    $offset = '';
    if ( $settings['method'] == 'offset' ) {
      $offset = '&offset='.intval($settings['offset']);
    }

    if ( !empty($settings['tag']) ) {
      $tag = '&tag='.$settings['tag'];
    }

    if ( !empty($settings['special']) ) {
      $settings['special'] = '/'. $settings['special'].'/';
    }

    // Generate the Mochi Feed URL
    $mochi_feed = trim($settings['feed'])
                . trim($settings['publisher_id'])
                . $settings['special']
                . $feed_format
                . $limit
                . $offset
                . $tag;
  
  }
  else {
    if ( !empty($settings['feed_save']) ) {
      $mochi_feed = $settings['feed_save'];
    }
    else {
      if ($echo)
        echo '<p class="mabp_error">'.__("No Feed URL provided!", MYARCADE_TEXT_DOMAIN).'</p>'; 
    }    
  }
  
  $args = array(
    'url'     => $mochi_feed, 
    'service' => 'mochi', 
    'echo'    => $echo
  );
    
  // Fetch Mochi games
  $json_games = myarcade_fetch_games($args);
        
  //====================================
  if ( !empty($json_games) )
  foreach ($json_games->games as $game) {
    
    // Check, if this game is present in the games tabl
    $duplicate_game = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '".$game->uuid."' OR game_tag = '".$game->game_tag."' OR name = '".mysql_real_escape_string($game->name)."'");

    if (!$duplicate_game) {
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
      myarcade_insert_game($game);

      // Get game id
      $game->id = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid' LIMIT 1");

      // Check if this is an automated game fetching
      if ( !empty($MochiGameTag) || ($cronfeeding == true) ) {
        // Check if the added game should be published
        if ( $mochi['status'] != 'add') {
          // We have to create a new game post...
          myarcade_add_games_to_blog( array(
                'game_id' => $game->id, 
                'post_status' => $mochi['status'],
                'echo' => false
              ));
        }
      }

      if ($echo) { myarcade_show_game($game); }
    }
  }

  if ($echo) {
    if ($new_games > 0) {
      echo '<p class="mabp_info"><strong>'.sprintf(__("Found %s new game(s).", MYARCADE_TEXT_DOMAIN), $new_games).'</strong></p>';
      echo '<p class="mabp_info">'.__("Now, you can publish new games on your site.", MYARCADE_TEXT_DOMAIN).'</p>';
    }
    else {
      echo '<p class="mabp_error">'.__("No new games found!", MYARCADE_TEXT_DOMAIN).'<br />'.__("Try to increase the number of 'Feed Games' at the settings page or wait until Mochi updates the feed.", MYARCADE_TEXT_DOMAIN).'</p>';
    }
  }
} // END - mochi_feed_games
?>