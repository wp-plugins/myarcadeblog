<?php
/**
 * Fetch Games
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2014, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Fetch
 */

defined('MYARCADE_VERSION') or die();


/**
 * Prepares the environment for MyArcadePlugin
 */
function myarcade_prepare_environment($echo = true) {

  $max_execution_time_l     = 600;  // 10 min
  $memory_limit_l           = "128"; // Should be enough
  $set_time_limit_l         = 600;  // 10 min

  // Check for safe mode
  if( !ini_get('safe_mode') ) {
    // Check max_execution_time
    @ini_set("max_execution_time", $max_execution_time_l);
    // Check memory limit
    $limit = ini_get("memory_limit");
    $limit = substr( $limit, 0, 1 );
    if ( $limit < $memory_limit_l ) {
      @ini_set("memory_limit", $memory_limit_l."M");
    }

    @set_time_limit($set_time_limit_l);
  }
  else {
    // save mode is set
    if ($echo)
      echo '<p class="mabp_error"><strong>'.__("WARNING!", MYARCADE_TEXT_DOMAIN).'</strong> '.__("Can't make needed settins, because you have Safe Mode active.", MYARCADE_TEXT_DOMAIN).'</p>';
  }
}


/**
 * Checks if json functions are available on the server
 *
 * @param boolean $echo true show messages, false hide messages
 * @return boolean
 */
function myarcade_check_json($echo) {

  $result = true;

  if (!function_exists('json_decode') ) {
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
* Generates the Fetch Games page
*/
function myarcade_fetch() {
  global $myarcade_distributors;

  myarcade_header();

  ?>
  <div class="icon32" id="icon-plugins"><br/></div>
  <h2><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h2>

  <?php
  // Get settings
  $spilgames  = get_option('myarcade_spilgames');
  $myarcadefeed = get_option('myarcade_myarcadefeed');

  $spilgames['search'] = '';

  $distributor = 'myarcadefeed';

  if ( isset($_POST['fetch']) && $_POST['fetch'] == 'start' ) {
    $distributor = $_POST['distr'];
    //Spilgames
    $spilgames['search']  = $_POST['searchspilgames'];
    $spilgames['limit']   = $_POST['limitspilgames'];
    //MyArcadeFeed
    $myarcadefeed['feed'] = isset($_POST['myarcadefeedselect']) ? $_POST['myarcadefeedselect'] : false;
  }
  ?>

  <script type="text/javascript">
    /* <![CDATA[ */
    function js_myarcade_offset() {
      if (jQuery("input:radio:checked[name='fetchmethod']").val() === 'latest') {
        jQuery("#offs").fadeOut("fast");
      } else if (jQuery("input:radio:checked[name='fetchmethod']").val() === 'offset') {
        jQuery("#offs").fadeIn("fast");
      }
    }


    jQuery(document).ready(function() {

      <?php if ( isset($_POST['fetch']) && $_POST['fetch'] == 'start' ) : ?>
      jQuery(document).ready(function() {
        js_myarcade_offset();
      });
      <?php endif; ?>


      jQuery(this).find("input:radio[name='fetchmethod']").click(function() {
       js_myarcade_offset();
      });

    });


    // new
    function js_myarcade_selection() {
      var selected = jQuery("#distr").find(":selected").val();
      jQuery("#"+selected).slideDown("fast");
      jQuery("#distr option").each(function() {
        var val = jQuery(this).val();
        if ( val !== selected ) {
          jQuery("#"+val).slideUp("fast");
        }
      });
    }

    jQuery(document).ready(function(){
      jQuery("#distr").change(function() {
        js_myarcade_selection();
      });

      // Call the function the first time when the site is loaded
      js_myarcade_selection();
    });
    /* ]]> */
  </script>

  <style type="text/css">
  .hide { display:none; }
  </style>

  <br />
  <form method="post" class="myarcade_form">
    <fieldset>
      <div class="myarcade_border grey">
        <label for="distr"><?php _e("Select a game distributor", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="distr" id="distr">
          <?php foreach ($myarcade_distributors as $slug => $name) : ?>
          <?php
          // Gamefeed hack
          if ( $slug == 'gamefeed' || $slug == 'mochi' ) continue;
          ?>
          <option value="<?php echo $slug; ?>" <?php myarcade_selected($distributor, $slug); ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php
      //________________________________________________________________________
      // Kongregate
      ?>
      <div class="myarcade_border white hide" id="kongregate">
        <p class="mabp_info">
        <?php _e("Fetching from this game distributor is available on MyArcadePlugin Pro.", MYARCADE_TEXT_DOMAIN);?>
        </p>
      </div><!-- end kongregate -->

      <?php
      //________________________________________________________________________
      // FlashGamesDistribution
      ?>
      <div class="myarcade_border white hide" id="fgd">
        <p class="mabp_info">
          <?php _e("Fetching from this game distributor is available on MyArcadePlugin Pro.", MYARCADE_TEXT_DOMAIN);?>
        </p>
      </div><!-- end fgd -->

      <?php
      //________________________________________________________________________
      // FreeGamesForYourWebsite (FOG)
      ?>
      <div class="myarcade_border white hide" id="fog">
        <p class="mabp_info">
          <?php _e("Fetching from this game distributor is available on MyArcadePlugin Pro.", MYARCADE_TEXT_DOMAIN);?>
        </p>

      </div><!-- end fog -->

      <?php
      //________________________________________________________________________
      // Spilgames
      ?>
      <div class="myarcade_border white hide" id="spilgames">
        <label><?php _e("Filter by search query", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <input type="text" size="40"  name="searchspilgames" value="<?php echo $spilgames['search']; ?>" />
        <p class="myarcade_hr">&nbsp;</p>
        Fetch <input type="text" name="limitspilgames" size="6" value="<?php echo $spilgames['limit']; ?>" /> games
        <div class="clear"></div>

      </div><!-- end spilgames -->

      <?php
      //________________________________________________________________________
      // MyArcadeFeed
      ?>
      <div class="myarcade_border white hide" id="myarcadefeed">
        <?php
        $myarcadefeed_array = array();
        for ($i=1;$i<5;$i++) {
          if ( !empty($myarcadefeed['feed'.$i])) {
            $myarcadefeed_array[$i] = $myarcadefeed['feed'.$i];
          }
        }
        if ( $myarcadefeed_array ) {
          _e("Select a Feed:", MYARCADE_TEXT_DOMAIN);
          ?>
          <select name="myarcadefeedselect" id="myarcadefeedselect">
            <?php
            foreach ($myarcadefeed_array as $key => $val) {
              echo '<option value="feed'.$key.'"> '.$val.' </option>';
            }
            ?>
          </select>
          <?php
        } else {
            ?>
            <p class="mabp_error">
              <?php _e("No MyArcadeFeed URLs found!", MYARCADE_TEXT_DOMAIN);?>
            </p>
            <?php
        }
        ?>
      </div><!-- end myarcadefeed -->

      <?php
      //________________________________________________________________________
      // Big Fish Games
      ?>
      <div class="myarcade_border white hide" id="bigfish">
        <p class="mabp_info">
          <?php _e("Fetching from this game distributor is available on MyArcadePlugin Pro.", MYARCADE_TEXT_DOMAIN);?>
        </p>
      </div><!-- end bigfish -->

      <?php
      //________________________________________________________________________
      // Scirra
      ?>
      <div class="myarcade_border white hide" id="scirra">
        <p class="mabp_info">
          <?php _e("Fetching from this game distributor is available on MyArcadePlugin Pro.", MYARCADE_TEXT_DOMAIN);?>
        </p>
      </div><!-- end scirra -->

      <?php
      //________________________________________________________________________
      // UnityFeeds
      ?>
      <div class="myarcade_border white hide" id="unityfeeds">
        <p class="mabp_info">
        <?php _e("There are no UnityFeeds specific settings available. Just fetch games :)", MYARCADE_TEXT_DOMAIN);?>
        </p>
      </div><!-- end unityfeeds -->

    </fieldset>

    <input type="hidden" name="fetch" value="start" />
    <input class="button-primary" id="submit" type="submit" name="submit" value="<?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?>" />
  </form>
  <br />
  <?php
  if ( isset($_POST['fetch']) && $_POST['fetch'] == 'start' ) {
    // Start fetching here...
    $func = 'myarcade_feed_'.$distributor;

    if ( $distributor && function_exists($func) ) {
      myarcade_prepare_environment();

      if ( !isset($$distributor) ) $$distributor = array();

      $args = array( 'echo' => true, 'settings' => $$distributor );
      $func($args);

    } else {
      ?>
      <p class="mabp_error">
        <?php _e("ERROR: Unkwnon game distributor!", MYARCADE_TEXT_DOMAIN); ?>
      </p>
      <?php
    }
  }

  myarcade_footer();
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

  // Allow users to modify or replace the URL
  $url = apply_filters( 'myarcade_fetch_url', $url, $service );

  switch ($service) {
    /** JSON FEEDS **/
    case 'spilgames':
    case 'unityfeeds':
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

    /** XML FEEDS **/
    case 'myarcadefeed':
    {
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

      // Decode the downloaded xml feed
      $games = simplexml_load_string($feed['response']);

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
 * Fetch and encode MyArcadeFeed games
 *
 * @global  $wpdb
 */
function myarcade_feed_myarcadefeed($args) {
 global $wpdb;

  $defaults = array(
    'echo'     => false,
    'settings' => array()
  );

  $r = wp_parse_args( $args, $defaults );
  extract($r);

  $new_games = 0;
  $add_game = false;

  $myarcadefeed  = get_option('myarcade_myarcadefeed');
  $feedcategories = get_option('myarcade_categories');
  $feed           = $settings['feed'];

  if ( empty($settings) ) {
    $settings = $myarcadefeed;
  }

  $games = myarcade_fetch_games( array(
      'url'     => $settings[$feed],
      'service' => 'myarcadefeed',
      'echo'    => true
    )
  );

  if ( !empty($games) && isset($games->gameset) ) {
    foreach ($games->gameset->game as $game) {

      $game->uuid     = $game->id;
      // Generate a game tag for this game
      $game->game_tag = md5($game->id.$game->name.'myarcadefeed');

      // Check, if this game is present in the games table
      $duplicate_game = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '".$game->uuid."' OR game_tag = '".$game->game_tag."' OR name = '".esc_sql($game->name)."'");

      if ( !$duplicate_game ) {
        // Check game categories and add game if it's category has been selected

        $categories = explode( ',', $game->category );
        if ( ! $settings['all_categories'] ) {
          $add_game = false;
          // Category-Check
          foreach ($feedcategories as $feedcat) {
            foreach ( $categories as $category ) {
              if ( ($feedcat['Name'] == $category) && ($feedcat['Status'] == 'checked') ) {
                $add_game = true;
                break;
              }
            }
            if ( $add_game ) {
              break;
            }
          }

          // Should we add this game?
          if ($add_game == false) { continue; }
        }

        // Decode URL
        $game->gamecode = urldecode($game->gamecode);

        // Check for file extension or embed code
        if ( strpos( $game->gamecode, 'src=') !== FALSE ) {
          // This is an embed code game
          $game->type = 'embed';
        }
        else {
          $extension = pathinfo( $game->gamecode , PATHINFO_EXTENSION );

          if ( $extension == 'dcr') {
            $game->type = $extension;
          }
          else {
            $game->type = 'myarcadefeed';
          }
        }

        $game->name           = esc_sql( $game->name );
        $game->slug           = myarcade_make_slug($game->name);

        $game->description    = esc_sql($game->description);
        $game->instructions    = esc_sql($game->instructions);
        $game->categs         = esc_sql($game->category);
        $game->control        = ''; // MyArcadeFeed doesn't provide controls...
        $game->thumbnail_url  = esc_sql($game->thumbnail);
        $game->swf_url        = esc_sql($game->gamecode);
        $game->screen1_url    = !empty($game->screenshot_1) ? $game->screenshot_1 : '';
        $game->screen2_url    = !empty($game->screenshot_2) ? $game->screenshot_2 : '';
        $game->screen3_url    = !empty($game->screenshot_3) ? $game->screenshot_3 : '';
        $game->screen4_url    = !empty($game->screenshot_4) ? $game->screenshot_4 : '';
        $game->video_url      = '';
        $game->leaderboard_enabled = '';
        $game->highscore_type = '';
        $game->coins_enabled  = '';
        $game->tags           = ( !empty($game->tags) ) ? esc_sql($game->tags) : '';
        $game->status         = 'new';

        $new_games++;

        // Insert the game to the table
        myarcade_insert_game($game);

        // Get game id
        $game->id = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid' LIMIT 1");

        myarcade_show_game($game);
      }
    }
  }

  if ($new_games > 0) {
    echo '<p class="mabp_info"><strong>'.sprintf(__("Found %s new game(s).", MYARCADE_TEXT_DOMAIN), $new_games).'</strong></p>';
    echo '<p class="mabp_info">'.__("Now, you can publish new games on your site.", MYARCADE_TEXT_DOMAIN).'</p>';
  }
  else {
    echo '<p class="mabp_error">'.__("No new games found!", MYARCADE_TEXT_DOMAIN).'<br />'.__("Please wait until the distributor updates the feed.", MYARCADE_TEXT_DOMAIN).'</p>';
  }
}

/**
 * Fetch and encode SpilGames games
 *
 * @param array args
 */
function myarcade_feed_spilgames( $args = array() ) {
  global $wpdb;

  $defaults = array(
    'echo'     => false,
    'settings' => array()
  );

  $r = wp_parse_args( $args, $defaults );

  extract($r);

  $new_games = 0;
  $add_game = false;

  $spilgames      = get_option('myarcade_spilgames');
  $feedcategories = get_option('myarcade_categories');

  // Init settings var's
  if ( !empty($settings) )
    $settings = array_merge($spilgames, $settings);
  else
    $settings = $spilgames;

  /**
   * Generate Feed URL
  */

  $feed_format ='?format=json';

  // Check if there is a feed limit. If not, feed all games
  if ( empty($settings['limit']) || ($settings['limit'] == 'all') ) {
    $limit = '';
  } else $limit = '&limit='.$settings['limit'];

  $thumb = '&tsize='.$spilgames['thumbsize'];

  if ( $spilgames['language'] == 'default' ) {
    $language = '';
  } else {
    $language = '&lang='.$spilgames['language'];
  }

  if ( empty($settings['search']) ) {
    $search = '';
  } else {
    $search = '&q='.$settings['search'];
  }

  // Generate the Mochi Feed URL
  $feed = trim($spilgames['feed']).$feed_format.$search.$limit.$thumb.$language;

  // Fetch Spilgames games
  $json_games = myarcade_fetch_games( array('url' => $feed, 'service' => 'spilgames', 'echo' => $echo) );

  //====================================
  if ( !empty($json_games) ) {

    $images = array('png', 'jpg', 'jpeg', 'gif', 'bmp');

    foreach ($json_games->entries as $game_obj) {

      $game = new stdClass();

      $game->uuid     = $game_obj->id . '_spilgames';
      // Generate a game tag for this game
      $game->game_tag = md5($game_obj->id.'spilgames');

      // Check, if this game is present in the games table
      $duplicate_game = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '".$game->uuid."' OR game_tag = '".$game->game_tag."' OR name = '".esc_sql( $game_obj->title )."'");

      if ( !$duplicate_game ) {
        // Check game categories and add game if it's category has been selected

        $add_game   = false;

        // Continue on games without category
        if ( empty($game_obj->category) ) {
          $game->category = 'Other';
        }

        // Check if this is a HTML5/EMBED game
        if ( strpos( $game_obj->category, "HTML5") !== false ) {
          $html5_game = TRUE;
        }
        else {
          $html5_game = FALSE;
        }

        // Transform some categories
        if ( ! empty($game_obj->category) ) {
          $categories = explode(',', $game_obj->category);
          $categories = array_map( 'trim', $categories );
        }
        else {
          $categories = array( 'Other' );
        }

        // Initialize the category string
        $categories_string = 'Other';

        foreach($categories as $gamecat) {
          $gamecat = htmlspecialchars_decode ( trim($gamecat) );

          foreach ($feedcategories as $feedcat) {
            if ( is_array( $feedcat['Spilgames'] ) ) {
              if ( $feedcat['Status'] == 'checked') {
                if ( strpos($feedcat['Spilgames']['name'], $gamecat) !== false ) {
                  $add_game = true;
                  $categories_string = $feedcat['Name'];
                  break;
                }
              }
            }
          }
          if ($add_game == true) break;
        } // END - Category-Check

        if (!$add_game) {
          continue;
        }

        switch ( $spilgames['thumbsize'] ) {
          case '1': {
            $thumbnail_url = $game_obj->thumbnails['0']->url;
            $ext = pathinfo( $thumbnail_url, PATHINFO_EXTENSION);
            if ( in_array( $ext, $images ) ) {
              break;
            }
          }
          case '2': {
            $thumbnail_url = $game_obj->thumbnails['1']->url;
            $ext = pathinfo( $thumbnail_url, PATHINFO_EXTENSION);
            if ( in_array( $ext, $images ) ) {
              break;
            }
          }
          case '3': {
            $thumbnail_url = $game_obj->thumbnails['2']->url;
            $ext = pathinfo( $thumbnail_url, PATHINFO_EXTENSION);
            if ( in_array( $ext, $images ) ) {
              break;
            }
          }
          default : {
            // We did not find a valid thumbnail image
            // Use default image
            $thumbnail_url = MYARCADE_URL . "/images/noimage.png";
          }
        }

        // Check if this is a HTML5 game. If so, then change game type and generate an iframe code
        if ( $html5_game ) {
          $game->type          = 'embed';
          $game->swf_url       = '<iframe src="'.$game_obj->player->url.'" width="'.$game_obj->player->width.'" height="'.$game_obj->player->height.'" frameborder="0" scrolling="0" marginwidith="0" marginheight="0"></iframe>';
        }
        else {
          $game->type          = 'spilgames';
          $game->swf_url       = esc_sql($game_obj->player->url);
        }

        $game->name          = esc_sql($game_obj->title);
        $game->slug          = myarcade_make_slug($game_obj->title);
        $game->created       = esc_sql($game_obj->published);
        $game->description   = esc_sql($game_obj->description);
        $game->instructions  = '';
        $game->rating        = '';
        $game->categs        = esc_sql($categories_string);
        $game->control       = '';
        $game->thumbnail_url = esc_sql($thumbnail_url);
        $game->screen1_url   = '';
        $game->screen2_url   = '';
        $game->screen3_url   = '';
        $game->screen4_url   = '';
        $game->video_url     = '';
        $game->leaderboard_enabled =  '';
        $game->highscore_type = '';
        $game->coins_enabled = '';
        $game->tags          = '';
        $game->width         = $game_obj->player->width;
        $game->height        = $game_obj->player->height;
        $game->status        = 'new';

        $new_games++;

        // Insert the game to the table
        myarcade_insert_game($game);

        // Get game id
        $game->id = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid' LIMIT 1");

        myarcade_show_game($game);
      }
    }
  }

  if ($echo) {
    if ($new_games > 0) {
      echo '<p class="mabp_info"><strong>'.sprintf(__("Found %s new game(s).", MYARCADE_TEXT_DOMAIN), $new_games).'</strong></p>';
      echo '<p class="mabp_info">'.__("Now, you can publish new games on your site.", MYARCADE_TEXT_DOMAIN).'</p>';
    }
    else {
      echo '<p class="mabp_error">'.__("No new games found!", MYARCADE_TEXT_DOMAIN).'</p>';
    }
  }
}

/**
 * Fetch and encode UnityFeeds games
 *
 * @param array args
 */
function myarcade_feed_unityfeeds( $args = array() ) {
  global $wpdb;

  $defaults = array(
    'echo'     => false,
    'settings' => array()
  );

  $r = wp_parse_args( $args, $defaults );

  extract($r);

  $new_games = 0;
  $add_game = false;

  $unityfeeds      = get_option('myarcade_unityfeeds');
  $feedcategories = get_option('myarcade_categories');

  // Init settings var's
  if ( !empty($settings) )
    $settings = array_merge($unityfeeds, $settings);
  else
    $settings = $unityfeeds;

  /**
   * Generate Feed URL
  */

  $feed_format ='?format=json';
  $category = ( $unityfeeds['category'] ) ? $unityfeeds['category'] : 'all';

  // Generate the Mochi Feed URL
  $feed = trim( $unityfeeds['feed'] ) . $feed_format . '&limit=all&category=' . $category;

  // Fetch Spilgames games
  $json_games = myarcade_fetch_games( array('url' => $feed, 'service' => 'unityfeeds', 'echo' => $echo) );

  //====================================
  if ( !empty($json_games) ) {

    foreach ($json_games as $game_obj) {

      $game = new stdClass();

      $game->uuid     = $game_obj->id . '_unityfeeds';
      // Generate a game tag for this game
      $game->game_tag = md5($game_obj->id.'unityfeeds');

      // Check, if this game is present in the games table
      $duplicate_game = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '".$game->uuid."' OR game_tag = '".$game->game_tag."' OR name = '".esc_sql( $game_obj->name )."'");

      if ( !$duplicate_game ) {
        // Check game categories and add game if it's category has been selected

        $add_game   = false;

        // Map UnityFeeds category names to our own names
        switch ($game_obj->category) {
          case 'Action Games':    $category = 'Action'; break;
          case 'Arcade Games':    $category = 'Arcade'; break;
          case 'Driving Games':   $category = 'Driving'; break;
          case 'Flying Games':    $category = 'Other'; break;
          case 'Girls Games':     $category = 'Dress-Up'; break;
          case 'Puzzle Games':    $category = 'Puzzles'; break;
          default: $category = 'Other'; break;
        }

        // Category-Check
        foreach ($feedcategories as $feedcat) {
          if ( ($feedcat['Name'] == $category) && ($feedcat['Status'] == 'checked') ) {
            $add_game = true;
            break;
          }
        }

        if (!$add_game) {
          continue;
        }

        $thumbnail_size = ( $unityfeeds['thumbnail'] ) ? $unityfeeds['thumbnail'] : '100x100';
        if ( ! empty( $game_obj->thumbnails->$thumbnail_size ) ) {
          $thumbnail_url = $game_obj->thumbnails->$thumbnail_size;
        }
        else {
          $thumbnail_url = MYARCADE_URL . "/images/noimage.png";
        }

        $screenshot_size = ( $unityfeeds['screenshot'] ) ? $unityfeeds['screenshot'] : '300x300';
        if ( !empty( $game_obj->thumbnails->$screenshot_size ) ) {
          $screenshot_url = $game_obj->thumbnails->$screenshot_size;
        }
        else {
          $screenshot_url = '';
        }
        
        $tags_string = '';
        $tags = (array) $game_obj->tags;
        if ( ! empty( $tags ) ) {
          foreach ( $tags as $key => $tag) {
            $tags_string .= $tag . ',';
          }

          $tags_string = rtrim( $tags_string, ',');
        }

        $game->type          = 'unityfeeds';
        $game->name          = esc_sql($game_obj->name);
        $game->slug          = myarcade_make_slug($game_obj->name);
        $game->created       = date('Y-m-d h:i:s',$game_obj->added);
        $game->description   = esc_sql($game_obj->description);
        $game->instructions  = esc_sql($game_obj->instructions);
        $game->rating        = '';
        $game->categs        = esc_sql($category);
        $game->control       = '';
        $game->swf_url       = esc_sql($game_obj->file);
        $game->thumbnail_url = esc_sql($thumbnail_url);
        $game->screen1_url   = esc_sql($screenshot_url);;
        $game->screen2_url   = '';
        $game->screen3_url   = '';
        $game->screen4_url   = '';
        $game->video_url     = '';
        $game->leaderboard_enabled =  '';
        $game->highscore_type = '';
        $game->coins_enabled = '';
        $game->tags          = esc_sql( $tags_string );
        $game->width         = esc_sql($game_obj->width);
        $game->height        = esc_sql($game_obj->height);
        $game->status        = 'new';

        $new_games++;

        // Insert the game to the table
        myarcade_insert_game($game);

        // Get game id
        $game->id = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid' LIMIT 1");

        myarcade_show_game($game);
      }
    }
  }

  if ($echo) {
    if ($new_games > 0) {
      echo '<p class="mabp_info"><strong>'.sprintf(__("Found %s new game(s).", MYARCADE_TEXT_DOMAIN), $new_games).'</strong></p>';
      echo '<p class="mabp_info">'.__("Now, you can publish new games on your site.", MYARCADE_TEXT_DOMAIN).'</p>';
    }
    else {
      echo '<p class="mabp_error">'.__("No new games found!", MYARCADE_TEXT_DOMAIN).'</p>';
    }
  }
}