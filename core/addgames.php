<?php
/**
 * Publish Games, Create Game Posts
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2013, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Posts
 */

defined('MYARCADE_VERSION') or die();

/**
 * Shows the plugin header
 */
function myarcade_header($echo = true) {
  if (!$echo) return;
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery(".toggle_container").hide();
      jQuery("h2.trigger").click(function(){
        jQuery(this).toggleClass("active").next().slideToggle("slow");
      });
    });
</script>

<?php
  echo '<div class="wrap">';
  ?><p style="margin-top: 10px"><img src="<?php echo MYARCADE_URL . '/images/logo.png'; ?>" alt="MyArcadePlugin Lite" /></p><?php
}


/**
 * Shows the plugin footer
 */
function myarcade_footer($echo = true) {
  if (!$echo) return;

  echo '</div>';
}

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
      NULL,
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
 * Creates a wordpress posts with the given game and returns the post id
 *
 * @global $wpdb
 * @param array $game
 * @return int $post_id
 */
function myarcade_add_game_post($game) {
  global $wpdb;

  // Get settings
  $general    = get_option('myarcade_general');

  // Single publishing active?
  if ( $general['single'] ) {
    // Clear categories and replace with the single one
    $game->categories = array();
    $game->categories[0] = $general['singlecat'];
  }

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

  } else {
    $post_content = '<img src="' . $game->thumb . '" alt="' . $game->name . '" style="float:left;margin-right:5px;">' . $game->description.' '.$game->instructions;
  }

  //====================================
  // Create a WordPress post
  $post = array();
  $post['post_title']   = $game->name;
  $post['post_content'] = $post_content;
  $post['post_status']  = $game->publish_status;
  $post['post_author']  = apply_filters( 'myarcade_filter_post_author', $game->user, $game);

  if ( $general['post_type'] != 'post' && post_type_exists($general['post_type']) ) {
    $post['post_type'] = $general['post_type'];
  }
  else {
    $post['post_type'] = 'post';
    $post['post_category'] = apply_filters( 'myarcade_filter_category', $game->categories, $game ); // Category IDs - ARRAY

    if ( !isset($general['disable_game_tags']) || $general['disable_game_tags'] == false ) {
      $post['tags_input'] = apply_filters( 'myarcade_filter_tags', $game->tags, $game );
    }
  }

  $post['post_date'] = $game->date;

  $post_id = wp_insert_post($post);

  // Required fields
  add_post_meta($post_id, 'mabp_game_type',     $game->type);
  add_post_meta($post_id, 'mabp_description',   $game->description);
  if ( $game->instructions ) add_post_meta($post_id, 'mabp_instructions',  mysql_real_escape_string( $game->instructions ));
  add_post_meta($post_id, 'mabp_swf_url',       $game->file);
  add_post_meta($post_id, 'mabp_thumbnail_url', $game->thumb);
  add_post_meta($post_id, 'mabp_game_tag',      $game->game_tag);
  add_post_meta($post_id, 'mabp_game_slug',     $game->slug);

  // Optional fields
  if ( $game->height )      add_post_meta($post_id, 'mabp_height', $game->height);
  if ( $game->width )       add_post_meta($post_id, 'mabp_width', $game->width);
  if ( $game->rating )      add_post_meta($post_id, 'mabp_rating', $game->rating);
  if ( $game->screen1_url ) add_post_meta($post_id, 'mabp_screen1_url', $game->screen1_url);
  if ( $game->screen2_url ) add_post_meta($post_id, 'mabp_screen2_url', $game->screen2_url);
  if ( $game->screen3_url ) add_post_meta($post_id, 'mabp_screen3_url', $game->screen3_url);
  if ( $game->screen4_url ) add_post_meta($post_id, 'mabp_screen4_url', $game->screen4_url);
  if ( $game->video_url )   add_post_meta($post_id, 'mabp_video_url', $game->video_url);
  if ( $game->leaderboard_enabled ) add_post_meta($post_id, 'mabp_leaderboard', $game->leaderboard_enabled);

  // Generate Featured Image id activated
  if ( $general['featured_image'] ) {
    myaracade_set_featured_image( $post_id, $game->thumb );
  }

  // Add custom taxonomies
  if ( !isset($general['disable_game_tags']) || $general['disable_game_tags'] == false ) {
    if ( $general['post_type'] != 'post' && post_type_exists($general['post_type']) ) {
      if ( !empty($general['custom_category']) && taxonomy_exists($general['custom_category']) ) {
        $categories = apply_filters( 'myarcade_filter_category', $game->categories, $game );
        wp_set_object_terms($post_id, $categories, $general['custom_category']);
      }
      if ( !empty($general['custom_tags']) && taxonomy_exists($general['custom_tags']) ) {
        $tags = apply_filters( 'myarcade_filter_tags', $game->tags, $game );
        wp_set_post_terms($post_id, $tags, $general['custom_tags']);
      }
    }
  }

  // Update postID
  $query = "UPDATE " . MYARCADE_GAME_TABLE . " SET postid = '$post_id' WHERE id = $game->id";
  $wpdb->query($query);

  return $post_id;
}

/**
 * Generate slug for a given string
 *
 * @param type $string
 * @return string
 */
function myarcade_make_slug($string) {
  $string = sanitize_title($string);
  $slug = strtolower( str_replace(" ", "-", $string ) );
  $slug = preg_replace("[^A-Za-z0-9-]", "", $slug);
  return $slug;
}


/**
 * Adds fetched games to the blog
 *
 * @global <type> $wpdb
 */
function myarcade_add_games_to_blog( $args = array() ) {
  global $wpdb, $user_ID, $myarcade_feedback;

  $general = get_option('myarcade_general');

  $defaults = array(
    'game_id'          => false,
    'post_status'      => 'publish',
    'post_date'        => gmdate('Y-m-d H:i:s', ( time() + (get_option('gmt_offset') * 3600 ))),
    'download_games'   => $general['down_games'],
    'download_thumbs'  => $general['down_thumbs'],
    'download_screens' => $general['down_screens'],
    'echo'             => true
  );

  $r = wp_parse_args( $args, $defaults );
  extract($r);

  if ($echo)
    $echo_feedback = "echo";
  else
    $echo_feedback = "return";

  $myarcade_feedback_args  = array( 'output' => $echo );

  if ( !$game_id ) {
    $myarcade_feedback->add_error( __("Game ID not provided.", MYARCADE_TEXT_DOMAIN) );
    $myarcade_feedback->get_errors( $myarcade_feedback_args );
    return false;
  }

  // Create new object
  $game_to_add = new StdClass();

  myarcade_header($echo);
  myarcade_prepare_environment($echo);

  // Get settings
  $feedcategories = get_option('myarcade_categories');

  // Initialize the var for custom post type
  $use_custom_tax = false;
  if ( ($general['post_type'] != 'post') && post_type_exists($general['post_type']) ) {
    if ( !empty($general['custom_category']) && taxonomy_exists($general['custom_category']) ) {
      $use_custom_tax = true;
    }
  }

  // Get the game
  $game = $wpdb->get_row("SELECT * FROM " . MYARCADE_GAME_TABLE . " WHERE id = '".$game_id."' limit 1");

  if ( !$game ) {
    $myarcade_feedback->add_error( __("Can't find the game in the games database table.", MYARCADE_TEXT_DOMAIN) );
    $myarcade_feedback->get_errors( $myarcade_feedback_args );
    return false;
  }

  // Check if this is a import game..
  // If it is an imported game don't download the files again...
  if (md5($game->name . 'import') == $game->uuid) {
    $download_games   = false;
    $download_thumbs  = false;
    $download_screens = false;
  }

  // Disable game download for Big Fish Games and Scirra Games
  if ( ($game->game_type == 'bigfish') || ($game->game_type == 'scirra') ) {
    $download_games   = false;
  }

  // Initialise category array
  $cat_id = array();
  // Check game categories..
  $categs = explode(",", $game->categories);

  if ( $general['firstcat'] == true ) {
    $tempcateg = $categs[0];
    unset($categs);
    $categs = array();
    $categs[0] = $tempcateg;
  }

    foreach ($categs as $game_cat) {
      $cat_found = false;
      foreach ($feedcategories as $feedcat) {
        if ($feedcat['Name'] == $game_cat) {
          $cat_found = true;
            // Check for custom taxonomies
            if ($use_custom_tax) {
              if ( term_exists($game_cat, $general['custom_category']) ) {
                array_push($cat_id, $game_cat);
              }
            } else {
              // post_type = post
              array_push($cat_id, get_cat_id($game_cat));
            }

          break;
        }
      }

      if ($cat_found == false) {
        array_push($cat_id, get_cat_id($game_cat));
      }
    }

  $download_message = array(
    'url'       => __("Use URL provided by the game distributor.", MYARCADE_TEXT_DOMAIN),
    'thumbnail' => __("Download Thumbnail", MYARCADE_TEXT_DOMAIN),
    'screen'    => __("Download Screenshot", MYARCADE_TEXT_DOMAIN),
    'game'      => __("Download Game", MYARCADE_TEXT_DOMAIN),
    'failed'    => __("FAILED", MYARCADE_TEXT_DOMAIN),
    'ok'        => __("OK", MYARCADE_TEXT_DOMAIN)
  );

  // Get download folders
  if ($download_games || $download_thumbs || $download_screens )
    $folder = myarcade_get_folder_path($game->slug, $game->game_type);
  else
    $folder = array();

  // ----------------------------------------------
  // Download Thumbs?
  // ----------------------------------------------
  if ($download_thumbs == true) {

    $file = myarcade_get_file($game->thumbnail_url, true);

    if ( empty($file['error']) ) {
      $path_parts = pathinfo($game->thumbnail_url);
      $extension = $path_parts['extension'];
      $file_name = $game->slug . '.' . $extension;

      // Check, if we got a Error-Page
      if (!strncmp($file['response'], "<!DOCTYPE", 9)) {
        $result = false;
      } else {
        // Save the thumbnail to the thumbs folder
        $result = file_put_contents(ABSPATH . $folder['image'] . $file_name, $file['response']);
      }

      // Error-Check
      if ($result == false) {
        $myarcade_feedback->add_message( $download_message['thumbnail'] . ': ' . $download_message['failed'] . ' - ' . $download_message['url'] );
      } else {
        $game->thumbnail_url = get_option('siteurl') . '/' . $folder['image'] . $file_name;
        $myarcade_feedback->add_message( $download_message['thumbnail'] . ': ' . $download_message['ok'] );
      }
    } else {
      $myarcade_feedback->add_message( $download_message['thumbnail'] . ': ' . $download_message['failed'] . ' - ' . $file['error'] . ' - ' . $download_message['url'] );
    }
  }

  // ----------------------------------------------
  // Download Screens?
  // ----------------------------------------------
  for ($screenNr = 1; $screenNr <= 4; $screenNr++) {
    $screenshot_url = 'screen' . $screenNr . "_url";

    if (($download_screens == true) && ($game->$screenshot_url)) {
      // Download screenshot
      $file = myarcade_get_file($game->$screenshot_url, true);

      $message_screen = sprintf( __("Downloading Screenshot No. %s", MYARCADE_TEXT_DOMAIN), $screenNr);

      if ( empty($file['error']) ) {
        $path_parts = pathinfo($game->$screenshot_url);
        $extension = $path_parts['extension'];
        $file_name = $game->slug . '_img' . $screenNr . '.' . $extension;

        // Check, if we got a Error-Page
        if (!strncmp($file['response'], "<!DOCTYPE", 9)) {
          $result = false;
        } else {
          // Save the screenshot to the thumbs folder
          $result = file_put_contents(ABSPATH . $folder['image'] . $file_name, $file['response']);
        }

        // Error-Check
        if ($result) {
          $game->$screenshot_url = get_option('siteurl') . '/' . $folder['image'] . $file_name;
          $myarcade_feedback->add_message( $message_screen . ': ' . $download_message['ok'] );
        }
        else {
          $myarcade_feedback->add_message( $message_screen . ': ' . $download_message['failed'] . ' - ' . $download_message['url'] );
        }
      } // END - if screens
      else {
        $myarcade_feedback->add_message( $message_screen . ': '  . $download_message['failed'] . ' - ' . $file['error'] . ' - ' . $download_message['url'] );
      }
    } // END - downlaod screens

    // Put the screen urls into the post array
    $game_to_add->$screenshot_url = $game->$screenshot_url;
  } // END for - screens


  // ----------------------------------------------
  // Download Games?
  // ----------------------------------------------
  if ($download_games == true) {

    // Clean up the swf url before try to downloaad
    $game->swf_url = strtok($game->swf_url, '?');

    $file = myarcade_get_file($game->swf_url, true);

    // We got a file
    if ( empty($file['error']) ) {
      $path_parts = pathinfo($game->swf_url);
      $extension = $path_parts['extension'];
      $file_name = $game->slug . '.' . $extension;

      // Check, if we got a Error-Page
      if (!strncmp($file['response'], "<!DOCTYPE", 9)) {
        $result = false;
      } else {
        // Save the game to the games directory
        $result = file_put_contents(ABSPATH . $folder['game'] . $file_name, $file['response']);
      }

      // Error-Check
      if ($result == false) {
        $myarcade_feedback->add_message( $download_message['game'] . ': ' . $download_message['failed'] . ' - ' . $download_message['url']);
      }
      else {
        $myarcade_feedback->add_message( $download_message['game'] . ': ' . $download_message['ok'] );
        // Overwrite the game url
        $game->swf_url = get_option('siteurl') . '/' . $folder['game'] . $file_name;
      }
    }
    else {
      $myarcade_feedback->add_message( $download_message['game'] . ': '  . $download_message['failed'] . ' - ' . $file['error'] . ' - ' . $download_message['url'] );
    }
  } // END - if download games

  // Display messages
  if ($echo) $myarcade_feedback->get_messages( array('output' => 'echo') );

  // ----------------------------------------------
  // Create a WordPress post
  // ----------------------------------------------

  // Get user info's
  get_currentuserinfo();

  $game_to_add->user = ( !empty($user_ID) ) ? $user_ID : 1;

  // Overwrite the post status if user has not sufficient rights
  if ( $user_ID  && !current_user_can('publish_posts') ) {
    $post_status = 'draft';
  }

  if ( $post_date )
    $game_to_add->date = $post_date;
  else
    $game_to_add->date = gmdate('Y-m-d H:i:s', ( time() + (get_option('gmt_offset') * 3600 )));

  $game_to_add->id = $game->id;
  $game_to_add->name = $game->name;
  $game_to_add->slug = $game->slug;
  $game_to_add->file = $game->swf_url;
  $game_to_add->width = $game->width;
  $game_to_add->height = $game->height;
  $game_to_add->thumb = $game->thumbnail_url;
  $game_to_add->description = $game->description;
  $game_to_add->instructions = $game->instructions;
  $game_to_add->video_url = $game->video_url;
  $game_to_add->tags = $game->tags;
  $game_to_add->rating = $game->rating;
  $game_to_add->categories = $cat_id;
  $game_to_add->type = $game->game_type;
  $game_to_add->publish_status = $post_status;
  // v5
  $game_to_add->leaderboard_enabled = $game->leaderboard_enabled;
  $game_to_add->game_tag = $game->game_tag;

  // Add game as a post
  $post_id = myarcade_add_game_post($game_to_add);

  if ( $post_id ) {
    // Game-Table: Set post status to published
    $query = "update " . MYARCADE_GAME_TABLE . " set status = 'published' where id = ".$game->id;
    $wpdb->query($query);

    return $post_id;
  }

  return false;
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

  require_once(ABSPATH . 'wp-admin/includes/image.php');
  require_once(ABSPATH . 'wp-admin/includes/file.php');
  require_once(ABSPATH . 'wp-admin/includes/media.php');

  // Download file to temp location
  $tmp = download_url( $filename );

  // Set variables for storage
  // fix file filename for query strings
  preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $filename, $matches);

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


/**
 * Publish games panel
 */
function myarcade_publish_games() {
  global $wpdb, $myarcade_distributors;

  myarcade_header();

  $general = get_option('myarcade_general');

  $feedcategories = get_option('myarcade_categories');

  // Init some needed vars
  if ( isset($_POST) && isset($_POST['action']) && ($_POST['action'] == 'publish') ) {
    $game_type        = $_POST['distr'];
    $leaderboard      = (isset($_POST['leaderboard'])) ? '1' : '0';
    $coins            = (isset($_POST['coins'])) ? '1' : '0';
    $status           = $_POST['status'];
    $schedule         = (isset($_POST['scheduletime'])) ? intval($_POST['scheduletime']) : $general['schedule'];
    $order            = ($_POST['order'] == 'ASC') ? 'ASC' : 'DESC';
    $cat              = $_POST['category'];
    $posts            = (isset($_POST['games'])) ? intval($_POST['games']) : false;
    $download_thumbs  = (isset($_POST['downloadthumbs'])) ? true : false;
    $download_screens = (isset($_POST['downloadscreens'])) ? true : false;
    $download_games   = (isset($_POST['downloadgames'])) ? true : false;

    // Generate the query
    $query_array = array();
    $query_string = '';

    $query_array[] = "status = 'new'";
    if ( $game_type != 'all') $query_array[] = "game_type = '".$game_type."'";
    if ( $leaderboard == '1') $query_array[] = "leaderboard_enabled = '1'";
    if ( $coins == '1')       $query_array[] = "coins_enabled = '1'";
    if ( $cat != 'all')       $query_array[] = "categories LIKE '%".$feedcategories[ (int) $cat ]['Name']."%'";

    if ( $posts )
      $limit = " limit ".$posts;
    else
      $limit = '';

    $count = count($query_array);
    if ( $count > 1 ) {
      for($i=0; $i < count($query_array); $i++) {
        $query_string .= $query_array[$i];
        if ( $i < ($count - 1) ) {
          $query_string .= ' AND ';
        }
      }
    } elseif ($count == 1) {
      $query_string = $query_array[0];
    }

    if ( !empty($query_string) ) $query_string = " WHERE ".$query_string;

    $query = "SELECT id FROM ".MYARCADE_GAME_TABLE.$query_string." ORDER BY id ".$order.$limit;
    $games = $wpdb->get_results($query);

    // Generate a string with all game IDs
    if ( !empty($games) ) {
      foreach( $games as $id )
        $ids[] = $id->id;

      $ids = implode(',', $ids);
      $start_publishing = 'yes';
    } else {
      $ids = '';
      $start_publishing = 'no';
    }
  } else {
    $game_type        = 'all';
    $leaderboard      = '0';
    $coins            = '0';
    $status           = $general['status'];
    $schedule         = $general['schedule'];
    $order            = 'ASC';
    $cat              = 'all';
    $posts            = $general['posts'];
    $download_thumbs  = $general['down_thumbs'];
    $download_screens = $general['down_screens'];
    $download_games   = $general['down_games'];

    $start_publishing = 'init';
  }
  ?>
  <div id="icon-options-general" class="icon32"><br /></div>
  <h2><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h2>
  <br />

  <form method="post" action="" class="myarcade_form" name="searchForm">
    <input type="hidden" name="action" value="publish" />
    <div class="myarcade_border grey" style="width:680px">
      <div class="myarcade_border white" style="width:300px;float:left;height:30px;">
        <label for="distr"><?php _e("Game Type", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="distr" id="distr">
          <option value="all" <?php myarcade_selected($game_type, 'all'); ?>>All</option>
          <?php foreach ($myarcade_distributors as $slug => $name) : ?>
          <option value="<?php echo $slug; ?>" <?php myarcade_selected($game_type, $slug); ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
          <option value="custom" <?php myarcade_selected($game_type, 'custom'); ?>>Custom SWF</option>
          <option value="embed" <?php myarcade_selected($game_type, 'embed'); ?>>Embed / Iframe</option>
          <option value="ibparcade" <?php myarcade_selected($game_type, 'ibparcade'); ?>>- PRO - IBPArcade</option>
          <option value="phpbb" <?php myarcade_selected($game_type, 'phpbb'); ?>>- PRO - PHPBB / ZIP</option>
          <option value="dcr" <?php myarcade_selected($game_type, 'dcr'); ?>>- PRO - DCR</option>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;float:left;margin-left:20px;height:30px;padding: 10px 5px 10px 10px;">
        <input type="checkbox" name="leaderboard" value="1" <?php myarcade_checked($leaderboard, '1'); ?> /> <label><?php _e('Only Leaderboard Games', MYARCADE_TEXT_DOMAIN); ?></label><br />
        <input type="checkbox" name="coins" value="1" <?php myarcade_checked($coins, '1'); ?> /> <label><?php _e('Only Coins Enabled Games', MYARCADE_TEXT_DOMAIN); ?></label>
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <label for="status"><?php _e("Post Status", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="status" id="status">
          <option value="publish" <?php myarcade_selected($status, 'publish'); ?>>Publish</option>
          <option value="draft" <?php myarcade_selected($status, 'draft'); ?>>Draft</option>
          <option value="future" <?php myarcade_selected($status, 'future'); ?>>Scheduled</option>
        </select>
        time <input type="text" name="scheduletime" value="<?php echo $schedule; ?>" size="5" /> min.
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <label><?php _e("Order", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="order" id="order">
          <option value="ASC" <?php myarcade_selected($order, 'ASC'); ?>>Older Games First (ASC)</option>
          <option value="DESC" <?php myarcade_selected($order, 'DESC'); ?>>Newer Games First (DESC)</option>
        </select>
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <label for="category"><?php _e("Game Categories", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="category" id="category">
          <option value="all" <?php myarcade_selected($cat, 'all'); ?>>All Activated</option>
          <?php
            for ($x=0; $x<count($feedcategories); $x++) {
              if ( $feedcategories[$x]['Status'] == 'checked' ) {
                ?>
                <option value="<?php echo $x; ?>" <?php myarcade_selected($cat,  $x); ?>>
                  <?php echo  $feedcategories[$x]['Name']; ?>
                </option>
                <?php
              }
            }
          ?>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <label><?php _e("Create", MYARCADE_TEXT_DOMAIN); ?></label>
        <input type="text" size="5" name="games" value="<?php echo $posts; ?>" />
        <label><?php _e("game posts", MYARCADE_TEXT_DOMAIN); ?></label>
      </div>

      <div class="myarcade_border white" style="width:300px;height:50px;float:left;">
        <input type="checkbox" value="1" id="downloadthumbs" name="downloadthumbs" <?php myarcade_checked($download_thumbs, true); ?> /> Download Thumbnails<br />
        <input type="checkbox" value="1" id="downloadscreens" name="downloadscreens" <?php myarcade_checked($download_screens, true); ?>/> Download Screenshots<br />
        <input type="checkbox" value="1" id="downloadgames" name="downloadgames" <?php myarcade_checked($download_games, true); ?>/> Download Games
      </div>

      <div class="clear"> </div>

      <input class="button-primary" id="submit" type="submit" name="submit" value="Create Posts" />
    </div>
  </form>

  <script type="text/javascript">
    function myarcade_check_dir(dir) {
      jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler', func:'dircheck', directory: dir},
        function(data) {
          jQuery('#down_' + dir).html(data);
        });
    }

    jQuery(document).ready(function($){
      $("#downloadgames").change(function() {
        if ( $('#downloadgames').attr('checked') ) {
          myarcade_check_dir('games');
        } else {
          $('#down_games').html("");
        }
      });
      $("#downloadthumbs").change(function() {
        if ( $('#downloadthumbs').attr('checked') || $('#downloadscreens').attr('checked') ) {
          myarcade_check_dir('thumbs');
        } else {
          $('#down_thumbs').html("");
        }
      });
      $("#downloadscreens").change(function() {
        if ( $('#downloadscreens').attr('checked') || $('#downloadthumbs').attr('checked') ) {
          myarcade_check_dir('thumbs');
        } else {
          $('#down_thumbs').html("");
        }
      });
    });
  </script>

  <div id="down_thumbs">
    <?php if ( ($download_thumbs || $download_screens) && (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR) ) ) {
      echo '<p class="mabp_error mabp_680">'.sprintf(__("The thumbs directory '%s' must be writeable (chmod 777) in order to download thumbnails or screenshots.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
    }
    ?>
  </div>
  <div id="down_games">
    <?php if ($download_games && (!is_writable(ABSPATH.MYARCADE_GAMES_DIR) ) ) {
      echo '<p class="mabp_error mabp_680">'.sprintf(__("The games directory '%s' must be writeable (chmod 777) in order to download games.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_GAMES_DIR).'</p>';
    }
    ?>
  </div>

  <?php if ( $start_publishing == 'yes' ) : ?>

  <p class="mabp_info mabp_680">
    <?php _e("Please be patient while games are published. This can take a while if your server is slow or if there are a lot of games. Do not navigate away from this page until MyArcadePlugin is done or the games will not be published.", MYARCADE_TEXT_DOMAIN); ?>
  </p>

  <?php
  // Get user ID
  global $user_ID;
  get_currentuserinfo();
  $userid = (isset($user_ID)) ? $user_ID : 1;
  $text_failures = sprintf('All done! %1$s game(s) where successfully published in %2$s second(s) and there were %3$s failures.', "' + myarcade_successes + '", "' + myarcade_totaltime + '", "' + myarcade_errors + '");
  $text_nofailures = sprintf('All done! %1$s game(s) where successfully published in %2$s second(s) and there were 0 failures.', "' + myarcade_successes + '", "' + myarcade_totaltime + '");
  ?>

  <noscript>
    <p>
      <em>
        <?php _e( 'You must enable Javascript in order to proceed!', MYARCADE_TEXT_DOMAIN) ?>
      </em>
    </p>
  </noscript>

	<div id="myarcade-bar" style="position:relative;height:25px;width:700px;">
		<div id="myarcade-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

  <p><input type="button" class="button hide-if-no-js" name="myarcade-stop" id="myarcade-stop" value="<?php _e( 'Abort Game Publishing', MYARCADE_TEXT_DOMAIN ); ?>" /></p>

  <div id="message" class="mabp_info mabp_680" style="display:none"></div>

	<ul id="myarcade-gamelist">
		<li style="display:none"></li>
	</ul>

	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var myarcade_games = [<?php echo $ids; ?>];
			var myarcade_total = myarcade_games.length;
			var myarcade_count = 1;
			var myarcade_percent = 0;
			var myarcade_successes = 0;
			var myarcade_errors = 0;
			var myarcade_failedlist = '';
			var myarcade_resulttext = '';
			var myarcade_timestart = new Date().getTime();
			var myarcade_timeend = 0;
			var myarcade_totaltime = 0;
			var myarcade_continue = true;

			// Create the progress bar
			$("#myarcade-bar").progressbar();
			$("#myarcade-bar-percent").html( "0%" );

			// Stop button
			$("#myarcade-stop").click(function() {
				myarcade_continue = false;
				$('#myarcade-stop').val("<?php _e('Stopping...', MYARCADE_TEXT_DOMAIN ); ?>");
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$("#myarcade-gamelist li").remove();

			// Called after each resize. Updates debug information and the progress bar.
			function myarcadeUpdateStatus( id, success, response ) {
				$("#myarcade-bar").progressbar( "value", ( myarcade_count / myarcade_total ) * 100 );
				$("#myarcade-bar-percent").html( Math.round( ( myarcade_count / myarcade_total ) * 1000 ) / 10 + "%" );
				myarcade_count = myarcade_count + 1;

				if ( success ) {
					myarcade_successes = myarcade_successes + 1;
					$("#myarcade-debug-successcount").html(myarcade_successes);
					$("#myarcade-gamelist").prepend("<li>" + response.success + "</li>");
				}
				else {
					myarcade_errors = myarcade_errors + 1;
					myarcade_failedlist = myarcade_failedlist + ',' + id;
					$("#myarcade-debug-failurecount").html(myarcade_errors);
					$("#myarcade-gamelist").prepend("<li>" + response.error + "</li>");
				}
			}

			// Called when all images have been processed. Shows the results and cleans up.
			function myarcadeFinishUp() {
				myarcade_timeend = new Date().getTime();
				myarcade_totaltime = Math.round( ( myarcade_timeend - myarcade_timestart ) / 1000 );

				$('#myarcade-stop').hide();

				if ( myarcade_errors > 0 ) {
					myarcade_resulttext = '<?php echo $text_failures; ?>';
				} else {
					myarcade_resulttext = '<?php echo $text_nofailures; ?>';
				}

				$("#message").html("<strong>" + myarcade_resulttext + "</strong>");
				$("#message").show();
			}

			// Publish a specified game via AJAX
			function myarcade( id ) {
              $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: { action: "myarcade_ajax_publish",
                  id: id,
                  status: '<?php echo $status; ?>',
                  schedule: '<?php echo $schedule; ?>',
                  count: myarcade_count,
                  download_thumbs: '<?php echo $download_thumbs; ?>',
                  download_screens: '<?php echo $download_screens; ?>',
                  download_games: '<?php echo $download_games; ?>'
                },
                success: function( response ) {
                  if ( response !== Object( response ) || ( typeof response.success === "undefined" && typeof response.error === "undefined" ) ) {
                    response = new Object;
                    response.success = false;
                    response.error = "<?php printf( esc_js( __( 'Game publishing request was abnormally terminated (ID %s). This is likely due to the game exceeding available memory or some other type of fatal error.', MYARCADE_TEXT_DOMAIN ) ), '" + id + "' ); ?>";
                  }
                  if ( response.success ) {
                    myarcadeUpdateStatus( id, true, response );
                  }
                  else {
                    myarcadeUpdateStatus( id, false, response );
                  }
                  if ( myarcade_games.length && myarcade_continue ) {
                    myarcade( myarcade_games.shift() );
                  }
                  else {
                    myarcadeFinishUp();
                  }
                },
                error: function( response ) {
                  myarcadeUpdateStatus( id, false, response );
                  if ( myarcade_games.length && myarcade_continue ) {
                    myarcade( myarcade_games.shift() );
                  }
                  else {
                    myarcadeFinishUp();
                  }
                }
              });
			}

			myarcade( myarcade_games.shift() );
		});
	// ]]>
	</script>
  <?php elseif ( $start_publishing == 'no') : ?>
  <p class="mabp_info mabp_680">
    <?php _e("No games found for your search criteria!", MYARCADE_TEXT_DOMAIN); ?>
  </p>
  <?php endif; ?>


  <?php
  myarcade_footer();
}

/**
* Ajax publish handler. Used by Publish Games panel
*/
function myarcade_ajax_publish() {
  global $myarcade_feedback;

  // Don't break the JSON result
  @error_reporting( 0 );

  header( 'Content-type: application/json' );

  $id       = (int) $_REQUEST['id'];
  $status   = $_REQUEST['status'];
  $schedule = (int) $_REQUEST['schedule'];
  $count    = (int) $_REQUEST['count'];
  $download_thumbs = ($_REQUEST['download_thumbs'] == '1') ? true : false;
  $download_screens = ($_REQUEST['download_screens'] == '1') ? true : false;
  $download_games = ($_REQUEST['download_games'] == '1') ? true : false;

  if ( $status == 'future') {
    $post_interval = ($count - 1) * $schedule;
  }
  else {
    $post_interval = 0;
  }

  $args = array(
    'game_id'          => $id,
    'post_status'      => $status,
    'post_date'        => gmdate('Y-m-d H:i:s', ( time() + ($post_interval * 60) + (get_option('gmt_offset') * 3600 ))),
    'download_games'   => $download_games,
    'download_thumbs'  => $download_thumbs,
    'download_screens' => $download_screens,
    'echo'             => false
  );

  $post_id = myarcade_add_games_to_blog($args);

  $errors = '';
  $messages = '';
  if ( is_myarcade_feedback($myarcade_feedback) ) {
    if ( $myarcade_feedback->has_errors() ) {
      $errors = $myarcade_feedback->get_errors(array('output' => 'string'));
    }
    if ( $myarcade_feedback->has_messages() ) {
      $messages = $myarcade_feedback->get_messages(array('output' => 'string'));
    }
  }

  if ( $post_id ) {

    if ( $status == 'publish' ) {
      $post_link = '<a href="'.get_permalink($post_id).'" class="button-secondary" target="_blank">View Post</a>';
    } else {
      $post_link = '<a href="'.add_query_arg( 'preview', 'true', get_permalink($post_id) ).'" class="button-secondary" target="_blank">Preview Post</a>';
    }

    $categories = get_the_category($post_id);
    $cat_string = '';
    if ( !empty($categories) ) {
      $count = count($categories);

      for($i=0; $i<$count; $i++) {
        if ( ($count - $i) > 1) {
          $cat_string .= $categories[$i]->cat_name . ', ';
        } else {
          $cat_string .= $categories[$i]->cat_name;
        }
      }
    }

    die(
      json_encode(
        array( 'success' => '<strong>'.esc_html( get_the_title($post_id) ).'</strong><br />
          <div>
            <div style="float:left;margin-right:5px">
              <img src="'. get_post_meta($post_id, 'mabp_thumbnail_url', true).'" width="80" height="80" alt="">
            </div>
            <div style="float:left">
            <table border="0">
            <tr valign="top">
              <td width="200"><strong>Categories:</strong> '.$cat_string.'<br />'.$errors.'</td>
              <td width="350">'.$messages.'</td>
            </tr>
            </table>
             <p><a href="'.get_edit_post_link( $post_id ).'" class="button-secondary" target="_blank">Edit Post</a> '.$post_link.'</p>
            </div>
          </div>
          <div style="clear:both;"></div>'
             )
      )
    );
  }
  else {
    // Error while creating game post
    die(json_encode(array('error' => __("Error: Post can not be created!", MYARCADE_TEXT_DOMAIN) .' - ' . $messages )));
  }
}