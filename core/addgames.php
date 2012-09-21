<?php
/*
  Module:       This modul contains MyArcadePlugin database handle functions
  Author:       Daniel Bakovic
  Author URI:   http://myarcadeplugin.com
*/

defined('MYARCADE_VERSION') or die();

/**
 * Inserts a fetched game to the games table
 *
 * @global $wpdb $wpdb
 * @param <type> $game
 */
function myarcade_insert_game($game) {
  global $wpdb;

  // Put this game into games table
  $query = "INSERT INTO " . MYARCADE_GAME_TABLE . " (
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
 * Creates a wordpress post with the given game and returns the post id
 *
 * @global $wpdb
 * @param array $game
 * @return int $post_id
 */
function myarcade_add_game_post($game) {
  global $wpdb; 
  
  // Get settings
  $general    = get_option('myarcade_general');
  
  // Generate the content
  if ($general['use_template'] ) {
    $post_content = $general['template'];
    $post_content = str_replace("%THUMB_URL%", $game->thumb, $post_content);
    $post_content = str_replace("%THUMB%", '<img src="' . $game->thumb . '" alt="' . $game->name . '" />', $post_content);
    $post_content = str_replace("%TITLE%", $game->name, $post_content);
    $post_content = str_replace("%DESCRIPTION%", $game->description, $post_content);
    $post_content = str_replace("%INSTRUCTIONS%", $game->instructions, $post_content);
    $post_content = str_replace("%SWF_URL%", $game->file, $post_content);
    $post_content = str_replace("%WIDTH%", $game->width, $post_content);
    $post_content = str_replace("%HEIGHT%", $game->height, $post_content);
    
    // Prepare tags for the content
    $tags_array   = explode(',', $game->tags);
    $tags_string  = '';
    foreach ($tags_array as $tag) {
     $tags_string .= trim($tag).', ';
    }
    // Remove last ', '
    $tags_string = substr($tags_string, 0, strlen($tags_string) - 2);
    
    // Insert Tags to the post content
    $post_content = str_replace("%TAGS%", $tags_string, $post_content);    
  }
  else {
    $post_content = '<img src="'.$game->thumb. '" alt="' . $game->name . '" style="float:left;margin-right:5px;">' . $game->description.' '.$game->instructions;
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
  
  if ( !empty($game->leaderboard_enabled) ) {
    add_post_meta($post_id, 'mabp_leaderboard', $game->leaderboard_enabled);
  } else {
    add_post_meta($post_id, 'mabp_leaderboard', '');
  }
  
  add_post_meta($post_id, 'mabp_game_tag', $game->game_tag);
  
  // Generate Featured Image id activated
  if ( $general['featured_image'] ) {
    myaracade_set_featured_image( $post_id, $game->thumb );
  }
    
  
  // Update postID 
  $query = "UPDATE " . MYARCADE_GAME_TABLE . " SET postid = '$post_id' WHERE id = $game->id";
  $wpdb->query($query);
  
  return $post_id;
}


/**
 * Adds fetched games to the blog
 *
 * @global <type> $wpdb
 */
function myarcade_add_games_to_blog(  $args = array() ) {
  global $wpdb, $user_ID;
  
  $general = get_option('myarcade_general');
  
  $defaults = array(
    'game_id'          => false,
    'post_status'      => $general['status'],
    'download_games'   => $general['down_games'],
    'download_thumbs'  => $general['down_thumbs'],
    'echo'             => true
  );
    
  $r = wp_parse_args( $args, $defaults );
  
  extract($r);
  
  myarcade_header($echo);
  myarcade_prepare_environment($echo);
  
  // Get settings
  $feedcategories = get_option('myarcade_categories');

  $post_interval = 0;
  $new_games = false;
  
  // Create new object
  $game_to_add = new StdClass();  

  // Check Download Directories
  if ($download_games) {
    if (!is_writable(ABSPATH.MYARCADE_GAMES_DIR)) {
      $download_games = false;
      if ($echo == true)
        echo '<p class="mabp_error">'.sprintf(__("The games directory '%s' must be writeable (chmod 777) in order to download games.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_GAMES_DIR).'</p>';
    }
  }
 
  if ($download_thumbs) {
    if (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR)) {
      $download_thumbs = false;
      if ($echo == true)
        echo '<p class="mabp_error">'.sprintf(__("The thumbails directory '%s' must be writeable (chmod 777) in order to download thumbnails.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
    }
  }
  
  // Check if a single game should be inserted...
  if (!$game_id) {
    $unpublished_games  = $wpdb->get_var("SELECT COUNT(*) FROM ".MYARCADE_GAME_TABLE." WHERE status = 'new'");
    
    if (intval($general['posts']) <= $unpublished_games) {
      $game_limit = $general['posts'];
    } else {
      $game_limit = $unpublished_games;
    }
  } 
  else {
    $game_limit = 1;
  }

  //====================================
  if ($echo == true) {
    echo '<h3>'.__("Publish Games", MYARCADE_TEXT_DOMAIN).'</h3>';
    echo "<ul>";
  }  

  // Publish Games
  // Go trough all games and add this to the blog
  for($i = 1; $i <= $game_limit; $i++) {
   
    // Get the next game
    if (!$game_id) {
      $game = $wpdb->get_row("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE status = 'new' order by created asc limit 1");
    }
    else {
      $game = $wpdb->get_row("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE id = '$game_id' limit 1");
    }

    if (!$game) {
      if ($echo == true)
        echo '<p class="mabp_error">'.__("Hmmm, there are no games that can be added to your blog...", MYARCADE_TEXT_DOMAIN).'</p>';
      break;
    }
    
    // Check if this is a import game..
    // If is an imported game don't download the files again...
    if (md5($game->name.'import') == $game->uuid) {
      $download_games   = false;
      $download_thumbs  = false;
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
        if ($echo == true)
          _e("Downloading Thumbnail..", MYARCADE_TEXT_DOMAIN);
          
        $file = myarcade_get_file($game->thumbnail_url);

        if ( empty($file['error']) ) {
          $path_parts = pathinfo($game->thumbnail_url);
          $extension  = $path_parts['extension'];
          $file_name  = $game->slug.'.'.$extension;
          
          // Check, if we got a Error-Page
          if (!strncmp($file['response'], "<!DOCTYPE", 9)) {
            $result = false;  
          }
          else {
            // Save the thumbnail to the thumbs folder
            $result = file_put_contents(ABSPATH.MYARCADE_THUMBS_DIR.$file_name, $file['response']);
          }
      
          // Error-Check
          if ($result == false) {
            if ($echo == true)
              echo " <strong>".__("Failed", MYARCADE_TEXT_DOMAIN)."</strong>! ".__("Use URL provided by the game distributor.", MYARCADE_TEXT_DOMAIN)."<br />";
          } else {
              $game->thumbnail_url = get_option('siteurl').'/'.MYARCADE_THUMBS_DIR.$file_name;
              if ($echo == true)
                echo " <strong>".__("OK", MYARCADE_TEXT_DOMAIN)."</strong>!<br />";
          }
        } else {
            if ($echo == true)
              echo " <strong>".__("Failed", MYARCADE_TEXT_DOMAIN)."</strong>! ".__("Use URL provided by the game distributor.", MYARCADE_TEXT_DOMAIN)."<br />";
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
        
        $file = myarcade_get_file($game->swf_url);

        // We got a file
        if ( empty($file['error']) ) {
          $path_parts = pathinfo($game->swf_url);
          $extension  = $path_parts['extension'];
          $file_name  = $game->slug.'.'.$extension;
          
          // Check, if we got a Error-Page  
          if (!strncmp($file['response'], "<!DOCTYPE", 9)) {
              $result = false;
          }
          else {
            // Save the game to the games directory
            $result = file_put_contents(ABSPATH.MYARCADE_GAMES_DIR.$file_name, $file['response']);
          }

          // Error-Check 
          if ($result == false) {
            if ($echo == true)
              echo '<p class="mabp_error">'.__("Game download:", MYARCADE_TEXT_DOMAIN).' <strong>'.__("Failed", MYARCADE_TEXT_DOMAIN).'</strong>! '.__("Use URL provided by the game distributor.", MYARCADE_TEXT_DOMAIN).'</p>';
          } 
          else {
            if ($echo == true)
              echo __("Game download:", MYARCADE_TEXT_DOMAIN)." <strong>".__("OK", MYARCADE_TEXT_DOMAIN)."</strong>!<br />";
              
            $game->swf_url = get_option('siteurl'). '/'.MYARCADE_GAMES_DIR.$file_name;
          }
        } 
        else {
          if ($echo == true)
            echo '<p class="mabp_error">'.__("Game download:", MYARCADE_TEXT_DOMAIN).' <strong>'.__("Failed", MYARCADE_TEXT_DOMAIN).'</strong>! '.__("Use URL provided by the game distributor.", MYARCADE_TEXT_DOMAIN).'</p>';
        }
      } // END - if download games
      
      if ($echo == true) { echo '</div></div><div style="clear:both;"></div></li>'; }        
        
      //====================================
      // Create a WordPress post
      
      // Get user info's 
      get_currentuserinfo();
      
      $game_to_add->user = (isset($user_ID)) ? $user_ID : 1;
        
      if (!$game_id) {
        if (strtolower($general['status']) == 'scheduled') {    
          $post_interval = $post_interval + $general['schedule'];
          $post_status = 'future';
        }
      }
      
      $game_to_add->id             = $game->id;
      $game_to_add->name           = $game->name;
      $game_to_add->slug           = $game->slug;
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
      $game_to_add->publish_status = $post_status;
      $game_to_add->leaderboard_enabled = $game->leaderboard_enabled;
      $game_to_add->game_tag       = $game->game_tag;
            
      // Add game as a post
      $post_id = myarcade_add_game_post($game_to_add);
      
      if ($post_id) {        
        // Game-Table: Set post status to poblished
        $query = "update " . MYARCADE_GAME_TABLE . " set status = 'published' where id = ".$game->id;
        $wpdb->query($query);
      }

  } // END - for games

  //====================================
  if ($echo == true) {
    if(!$new_games) {
      echo '<li><p class="mabp_error">'.__("No new games to add. Feed Games first!", MYARCADE_TEXT_DOMAIN).'</p></li>';
    }

    echo "</ul>";
    
    myarcade_footer($echo);
  }
}

/**
 * Set featured image on a post
 * 
 * @param type $post_id
 * @param type $filename
 * @return type 
 */
function myaracade_set_featured_image ($post_id, $filename) {
  
  $wp_filetype = wp_check_filetype( basename($filename), null );
  
  require_once(ABSPATH . 'wp-admin/includes/file.php');
  require_once(ABSPATH . 'wp-admin/includes/media.php');
  
  // Download file to temp location
  $tmp = download_url( $filename );
  
  // Set variables for storage
  // fix file filename for query strings
  preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $filename, $matches);
  
  //if ( empty($slug) ) $slug = basename($matches[0]);
  $file_array['name'] = basename($filename);
  $file_array['tmp_name'] = $tmp;
  $file_array['type'] = $wp_filetype['type'];

  // If error storing temporarily, unlink
  if ( is_wp_error( $tmp ) ) {
    @unlink($file_array['tmp_name']);
    $file_array['tmp_name'] = '';
    return false;
  }

  // do the validation and storage stuff
  $thumbid = media_handle_sideload($file_array, $post_id);
  
  // If error storing permanently, unlink
  if ( is_wp_error($thumbid) ) {
    @unlink($file_array['tmp_name']);
    return $thumbid;
  }

  set_post_thumbnail($post_id, $thumbid);
}
?>