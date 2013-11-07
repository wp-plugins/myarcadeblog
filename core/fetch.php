<?php
/**
 * Fetch Games
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2013, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Fetch
 */

defined('MYARCADE_VERSION') or die();


/**
 * Prepares the environment for MyArcadePlugin
 */
function myarcade_prepare_environment($echo = true) {

  $max_execution_time_l     = 600;  // 10 min
  $memory_limit_l           = "256M"; // Should be enough
  $set_time_limit_l         = 600;  // 10 min

  // Check for safe mode
  if( !ini_get('safe_mode') ) {
    // Check max_execution_time
    @ini_set("max_execution_time", $max_execution_time_l);

    // Check memory limit
    @ini_set("memory_limit", $memory_limit_l);

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
  $mochi      = get_option('myarcade_mochi');
  $spilgames  = get_option('myarcade_spilgames');
  $myarcadefeed = get_option('myarcade_myarcadefeed');

  $mochi['method'] = 'latest';
  $mochi['offset'] = 0;
  $spilgames['search'] = '';

  $distributor = 'myarcadefeed';

  if ( isset($_POST['fetch']) && $_POST['fetch'] == 'start' ) {
    $distributor = $_POST['distr'];
    // Mochi fields
    $mochi['special'] = $_POST['mochicat'];
    $mochi['tag'] = $_POST['tag'];
    $mochi['method'] = $_POST['fetchmethod'];
    $mochi['limit'] = $_POST['limit'];
    $mochi['offset'] = $_POST['offset'];
    $mochi['default_feed'] = $_POST['feedversion'];
    $mochi['feed_save'] = $_POST['feed_save'];
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

    function js_myarcade_mochi_feed_version() {
      if (jQuery("input:radio:checked[name='feedversion']").val() === 'old') {
        jQuery("#feedversion").fadeOut("fast");
      } else if (jQuery("input:radio:checked[name='feedversion']").val() === 'new') {
        jQuery("#feedversion").fadeIn("fast");
      }
    }

    jQuery(document).ready(function() {

      <?php if ( isset($_POST['fetch']) && $_POST['fetch'] == 'start' ) : ?>
      jQuery(document).ready(function() {
        js_myarcade_offset();
        js_myarcade_mochi_feed_version();
      });
      <?php endif; ?>


      jQuery(this).find("input:radio[name='fetchmethod']").click(function() {
       js_myarcade_offset();
      });

      jQuery(this).find("input:radio[name='feedversion']").click(function() {
        js_myarcade_mochi_feed_version();
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
      js_myarcade_mochi_feed_version();
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
          if ( $slug == 'gamefeed' ) continue;
          ?>
          <option value="<?php echo $slug; ?>" <?php myarcade_selected($distributor, $slug); ?>><?php echo $name; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php
      //________________________________________________________________________
      // Mochi Media
      ?>
      <div class="myarcade_border white" id="mochi">
        <label><?php _e("Special Categories", MYARCADE_TEXT_DOMAIN); ?></label>
        <select name="mochicat" id="mochicat" style="margin-right:5px;" >
          <option value="" <?php myarcade_selected($mochi['special'], ''); ?> ><?php _e("All Games", MYARCADE_TEXT_DOMAIN); ?></option>
          <option value="coins_enabled" <?php myarcade_selected($mochi['special'], 'coins_enabled'); ?> ><?php _e("Coin Enabled Games", MYARCADE_TEXT_DOMAIN); ?></option>
          <option value="featured_games" <?php myarcade_selected($mochi['special'], 'featured_games'); ?> ><?php _e("Featured Games", MYARCADE_TEXT_DOMAIN); ?></option>
          <option value="leaderboard_enabled" <?php myarcade_selected($mochi['special'], 'leaderboard_enabled'); ?> ><?php _e("Leaderboard Games", MYARCADE_TEXT_DOMAIN); ?></option>
        </select>

        <label><?php _e("Filter by Tag", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <input type="text" size="40"  name="tag" value="<?php echo $mochi['tag']; ?>" />

        <p class="myarcade_hr">&nbsp;</p>

        <div style="float:left;margin-right:50px;">
          <input type="radio" name="fetchmethod" value="latest" <?php myarcade_checked($mochi['method'], 'latest');?>>
        <label><?php _e("Latest Games", MYARCADE_TEXT_DOMAIN); ?></label>
        <br />
        <input type="radio" name="fetchmethod" value="offset" <?php myarcade_checked($mochi['method'], 'offset');?>>
        <label><?php _e("Use Offset", MYARCADE_TEXT_DOMAIN); ?></label>
        </div>
        <div class="myarcade_border" style="float:left;padding-top: 5px;background-color: #F9F9F9">
        Fetch <input type="text" name="limit" size="6" value="<?php echo $mochi['limit']; ?>" /> games <span id="offs" class="hide">from offset <input id="radiooffs" type="text" name="offset" size="4" value="<?php echo $mochi['offset']; ?>" /> </span>
        </div>
        <div class="clear"></div>

        <p class="myarcade_hr">&nbsp;</p>

        <div style="float:left;margin-right:20px;">
          <input type="radio" name="feedversion" value="old" <?php myarcade_checked($mochi['default_feed'], 'old');?>>
          <label><?php _e("Regular Mochi Feed", MYARCADE_TEXT_DOMAIN); ?></label>
          <br />
          <input type="radio" name="feedversion" value="new" <?php myarcade_checked($mochi['default_feed'], 'new');?>>
          <label><?php _e("Game Catalog 2.0", MYARCADE_TEXT_DOMAIN); ?></label>
        </div>
        <div id="feedversion" class="myarcade_border hide" style="float:left;padding-top: 5px;background-color: #F9F9F9">
          Game Catalog 2.0 (JSON FEED URL)
          <br />
          <input type="text" name="feed_save" size="80" value="<?php echo $mochi['feed_save']; ?>" />
        </div>

        <div class="clear"></div>

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
    case 'mochi':
    case 'spilgames':
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

      // Check, if we got an Error-Page ( Only mochi)
      if ( !strncmp($feed['response'], "<!DOCTYPE", 9) ) {
        if ($echo) {
          echo '<font style="color:red;">'.__("Feed not found. Please check Kongregate Feed URL!", MYARCADE_TEXT_DOMAIN).'</font></p>';
            myarcade_footer();
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
      $duplicate_game = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '".$game->uuid."' OR game_tag = '".$game->game_tag."' OR name = '".mysql_real_escape_string($game->name)."'");

      if ( !$duplicate_game ) {
        // Check game categories and add game if it's category has been selected

        $add_game   = false;

        $categories = explode( ',', $game->category );

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

        $game->slug           = myarcade_make_slug($game->name);
        $game->categs         = $game->category;
        $game->control        = ''; // MyArcadeFeed doesn't provide controls...
        $game->thumbnail_url  = mysql_real_escape_string($game->thumbnail);
        $game->swf_url        = urldecode($game->gamecode);
        $game->screen1_url    = !empty($game->screenshot_1) ? $game->screenshot_1 : '';
        $game->screen2_url    = !empty($game->screenshot_2) ? $game->screenshot_2 : '';
        $game->screen3_url    = !empty($game->screenshot_3) ? $game->screenshot_3 : '';
        $game->screen4_url    = !empty($game->screenshot_4) ? $game->screenshot_4 : '';
        $game->video_url      = '';
        $game->leaderboard_enabled = '';
        $game->highscore_type = '';
        $game->coins_enabled  = '';
        $game->tags           = ( !empty($game->tags) ) ? $game->tags : '';
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
    foreach ($json_games->entries as $game) {

      $game->uuid     = $game->id . '_spilgames';
      // Generate a game tag for this game
      $game->game_tag = md5($game->id.'spilgames');

      // Check, if this game is present in the games table
      $duplicate_game = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '".$game->uuid."' OR game_tag = '".$game->game_tag."' OR name = '".mysql_real_escape_string($game->title)."'");

      if ( !$duplicate_game ) {
        // Check game categories and add game if it's category has been selected

        $add_game   = false;

        // Continue on games without category
        if ( empty($game->category) ) {
          $game->category = 'Other';
        }

        // Transform some categories
        $categories = explode(',', $game->category);
        $html5_game = FALSE;

        foreach($categories as $gamecat) {
          $gamecat = htmlspecialchars_decode ( trim($gamecat) );

          if ( $gamecat == 'HTML5 Games') {
            $html5_game = TRUE;
          }

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

        switch ($spilgames['thumbsize']) {
          case '3': $thumbnail_url = $game->thumbnails['2']->url; break;
          case '2': $thumbnail_url = $game->thumbnails['1']->url; break;
          case '1': default: $thumbnail_url = $game->thumbnails['0']->url; break;
        }

        $thumb_info = pathinfo($thumbnail_url);
        if ( empty($thumb_info['filename']) ) $thumbnail_url = MYARCADE_URL . "/images/noimage.png";

        // Check if this is a HTML5 game. If so, then change game type and generate an iframe code
        if ( $html5_game ) {
          $game->type          = 'embed';
          $game->swf_url       = '<iframe src="'.$game->player->url.'" width="'.$game->player->width.'" height="'.$game->player->height.'" frameborder="0" scrolling="0" marginwidith="0" marginheight="0"></iframe>';
        }
        else {
          $game->type          = 'spilgames';
          $game->swf_url       = mysql_real_escape_string($game->player->url);
        }

        $game->name          = mysql_real_escape_string($game->title);
        $game->slug          = myarcade_make_slug($game->title);
        $game->created       = $game->published;
        $game->description   = mysql_real_escape_string($game->description);
        $game->instructions  = '';
        $game->rating        = '';
        $game->categs        = $categories_string;
        $game->control       = '';
        $game->thumbnail_url = mysql_real_escape_string($thumbnail_url);
        $game->screen1_url   = '';
        $game->screen2_url   = '';
        $game->screen3_url   = '';
        $game->screen4_url   = '';
        $game->video_url     = '';
        $game->leaderboard_enabled =  '';
        $game->highscore_type = '';
        $game->coins_enabled = '';
        $game->tags          = '';
        $game->width         = $game->player->width;
        $game->height        = $game->player->height;
        $game->status        = 'new';

        $new_games++;

        // remove not needed fields
        unset($game->summary);
        unset($game->thumbnails);
        unset($game->player);

        // Insert the game to the table
        myarcade_insert_game($game);

        // Get game id
        $game->id = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid' LIMIT 1");

        if ($echo) { myarcade_show_game($game); }
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
 * Gets a feed from mochi and adds new games into the games table
 *
 * @param array $args
 */
function myarcade_feed_mochi( $args = array() ) {
  global $wpdb, $myarcade_feedback;

  $defaults = array(
    'game_tag' => false,
    'echo'     => false,
    'settings' => array()
  );

  $r = wp_parse_args( $args, $defaults );

  extract($r);

  $new_games = 0;
  $add_game = false;
  $tag = '';

  $mochi  = get_option('myarcade_mochi');
  $feedcategories = get_option('myarcade_categories');

  // Init settings var's
  if ( !empty($settings) )
    $settings = array_merge($mochi, $settings);
  else
    $settings = $mochi;

  if ( !isset($settings['method']) ) $settings['method'] = 'latest';

  /**
   * Generate the Feed URL
   */

  // Check if this is a old or new way of feeding
  if ( $settings['default_feed'] == 'old' ) {
    // Add Feed Format JSON
    $feed = add_query_arg( 'format', 'json', trim( $settings['feed'] ) );
    // Add the Publisher ID
    if ( !empty( $settings['publisher_id']) )
      $feed = add_query_arg ( 'partner_id' , $settings['publisher_id'], $feed );
    // Add game_tag for auto posts from Mochi
    if ( $game_tag ) {
      $feed = add_query_arg ( 'q', rawurlencode('(game:'. $game_tag .')'), $feed);
    } else {
      $query_array = array();
      // Special Category (featured games , leaderboard games)
      if ( !empty($settings['special']) ) {
        if ( $settings['special'] == 'featured_games')
          $query_array[] = '(recommendation:>=4)';
        else
         $query_array[] = '(' . $settings['special'] .')';
      }
      // Add tag filter
      if ( !empty( $settings['tag'] ) ) {
        $settings['tag'] = str_replace( " ", ",", $settings['tag'] );
        $tags = explode( ',', $settings['tag'] );
        $tag_count = count($tags);
        $tag_query = '(';
        foreach ( $tags as $tag ) {
          $tag_count--;
          if ( $tag[0] == '-' ) {
            // not tags
            $tag_query .= "not tags:".substr($tag, 1);
          }
          else {
            // tags
            $tag_query .= "tags:".$tag;
          }

          // add and
          if ( $tag_count > 0 )
            $tag_query .= " and ";
        }

        if ( $tag_query )
          $query_array[] = $tag_query . ")";
      }

      // Generate the query string
      if ( !empty( $query_array ) ) {
        $query_string = implode(" and ", $query_array);
        $query_string = rawurlencode( $query_string );
        $feed .= '&q='.$query_string;
      }

      // Limit only when this is not a game_tag fetching
      if ( intval( $settings['limit'] ) > 0 )
        $feed = add_query_arg ( 'limit', $settings['limit'], $feed);
      // Offset
      if ( $settings['method'] == 'offset' )
      $feed = add_query_arg ( 'offset', intval($settings['offset']), $feed );
    }
  }
  else {
    if ( !empty($settings['feed_save']) ) {
      $feed = $settings['feed_save'];
    }
    else {
     $myarcade_feedback->add_error( __("No Feed URL provided!", MYARCADE_TEXT_DOMAIN) );
     return $myarcade_feedback->get_error( array('output' => $echo ) );
    }
  }

  // Fetch Mochi games
  $json_games = myarcade_fetch_games( array('url' => $feed, 'service' => 'mochi', 'echo' => $echo) );

  //====================================
  if ( !empty($json_games) ) {
    foreach ($json_games->games as $game) {

      // Check, if this game is present in the games table
      $duplicate_game = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '".$game->uuid."' OR game_tag = '".$game->game_tag."' OR name = '".mysql_real_escape_string($game->name)."'");

      if ( !$duplicate_game ) {
        // Check game categories and add game if it's category has been selected
        $add_game   = false;
        $categories = '';

        // Category-Check
        $game_category_array = array();
        if ( !empty( $game->category ) ) {
          $game_category_array[] = $game->category;
        }
        else {
          $game_category_array = $game->categories;
        }

        foreach($game_category_array as $gamecat) {
          foreach ($feedcategories as $feedcat) {
            if ( ($feedcat['Name'] == $gamecat) && ($feedcat['Status'] == 'checked') ) {
              $add_game = true;
              break;
            }
          }

          if ($add_game == true) break;
        } // END - Category-Check

        if ($add_game == true) {
          $categories = implode(",", $game_category_array);
        } else continue;

        // Tags
        $tags = implode(",", $game->tags);

        // Controls
        $game_control = '';
        foreach ($game->controls as $control) {
          $game_control .= implode(" = ", $control) . ";";
        }

        $game->type          = 'mochi';
        $game->name          = mysql_real_escape_string($game->name);
        $game->description   = mysql_real_escape_string($game->description);
        $game->instructions  = mysql_real_escape_string($game->instructions);
        $game->rating        = mysql_real_escape_string($game->rating);
        $game->categs        = $categories;
        $game->control       = $game_control;
        $game->thumbnail_url = mysql_real_escape_string($game->thumbnail_url);
        $game->swf_url       = mysql_real_escape_string($game->swf_url);
        $game->screen1_url   = mysql_real_escape_string($game->screen1_url);
        $game->screen2_url   = mysql_real_escape_string($game->screen2_url);
        $game->screen3_url   = mysql_real_escape_string($game->screen3_url);
        $game->screen4_url   = mysql_real_escape_string($game->screen4_url);
        $game->video_url     = mysql_real_escape_string($game->video_url);
        $game->leaderboard_enabled =  mysql_real_escape_string($game->leaderboard_enabled);
        $game->highscore_type = '';
        $game->coins_enabled = mysql_real_escape_string($game->coins_enabled);
        $game->tags          = mysql_real_escape_string($tags);
        $game->status        = 'new';

        $new_games++;

        // Insert the game to the table
        myarcade_insert_game($game);

        // Get game id
        $game->id = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid' LIMIT 1");

        // Check if this is an automated game fetching
        if ( $game_tag ) {
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

        if ($echo)
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
      echo '<p class="mabp_error">'.__("No new games found!", MYARCADE_TEXT_DOMAIN).'<br />'.__("Try to increase the number of 'Feed Games' at the settings page or wait until Mochi updates the feed.", MYARCADE_TEXT_DOMAIN).'</p>';
    }
  }
}