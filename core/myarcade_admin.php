<?php
/**
 * Admin Functions
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Admin
 */

defined('MYARCADE_VERSION') or die();

/* Add the main menu to blog */
add_action('admin_menu', 'myarcade_admin_menu', 9);
/* Add some Ajax features */
add_action('admin_menu', 'myarcade_load_ajax', 1);

add_action('admin_enqueue_scripts', 'myarcade_admin_scripts');

function myarcade_premium_settings() {
  ?><span style="float:right;font-size:14px;font-style:italic;margin-top:10px"><?php myarcade_premium_img() ?> Premium Feature</span><?php
}

/**
 * Shows the admin menu
 */
function myarcade_admin_menu() {

  $permisssion = 'manage_options';

  add_menu_page('MyArcade', 'MyArcade', $permisssion , basename(__FILE__), 'myarcade_show_stats', MYARCADE_CORE_URL . '/images/arcade.png', 55);
  add_submenu_page(basename(__FILE__), __('Dashboard', MYARCADE_TEXT_DOMAIN), __('Dashboard', MYARCADE_TEXT_DOMAIN), $permisssion, basename(__FILE__), 'myarcade_show_stats');

  add_submenu_page( basename(__FILE__),
                    __("Fetch Games", MYARCADE_TEXT_DOMAIN),
                    __("Fetch Games", MYARCADE_TEXT_DOMAIN),
                    'manage_options', 'myarcade-fetch', 'myarcade_fetch');

  $hookname = add_submenu_page( basename(__FILE__),
                    __("Import Games", MYARCADE_TEXT_DOMAIN),
                    __("Import Games", MYARCADE_TEXT_DOMAIN),
                    $permisssion, 'myarcade-import-games', 'myarcade_import_games');

  add_action('load-'.$hookname, 'myarcade_import_scripts');

  add_submenu_page( basename(__FILE__),
                    __("Publish Games", MYARCADE_TEXT_DOMAIN),
                    __("Publish Games", MYARCADE_TEXT_DOMAIN),
                    'manage_options', 'myarcade-publish-games',  'myarcade_publish_games');

  add_submenu_page( basename(__FILE__),
                    __("Manage Games", MYARCADE_TEXT_DOMAIN),
                    __("Manage Games", MYARCADE_TEXT_DOMAIN),
                    'manage_options', 'myarcade-manage-games', 'myarcade_manage_games');

  add_submenu_page( basename(__FILE__),
                    __("Manage Scores", MYARCADE_TEXT_DOMAIN),
                    __("Manage Scores", MYARCADE_TEXT_DOMAIN),
                    'manage_options', 'myarcade-manage-scores', 'myarcade_manage_scores');

  add_submenu_page( basename(__FILE__),
                    __("Settings"),
                    __("Settings"),
                    'manage_options', 'myarcade-edit-settings', 'myarcade_edit_settings');
}

function myarcade_import_scripts() {
  wp_enqueue_script('myarcade_ajax_forms',
      MYARCADE_JS_URL.'/jquery.form.js',
      '',
      '',
      false);
}

function myarcade_admin_scripts() {
  global $pagenow;

  if ($pagenow == 'post.php') {
    wp_register_script( 'myarcade_writepanel', MYARCADE_JS_URL . '/writepanel.js', array('jquery') );
    wp_enqueue_script('myarcade_writepanel');
  }

  if ( $pagenow == 'admin.php' ) {
    if ( isset($_GET['page']) && ($_GET['page'] == 'myarcade-publish-games') ) {
      wp_enqueue_script( 'jquery-ui-progressbar', MYARCADE_JS_URL . '/jquery.ui.progressbar.min.js', array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.8.6' );
      wp_enqueue_style( 'jquery-ui-myarcadeplugin', MYARCADE_JS_URL . '/jquery-ui-1.7.2.custom.css', array(), '1.7.2' );
    }
  }
}


function myarcade_downloads_upload_dir($upload) {
  if ( isset($_POST['type'] ) ) {
    switch ( $_POST['type'] ) {
      case 'myarcade_image': {
        $upload['subdir'] = '/thumbs';
        $upload['path'] =  $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
      } break;

      case 'myarcade_game': {
        $upload['subdir'] = '/games';
        $upload['path'] =  $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
      }
    }
  }

  return $upload;
}

function myarcade_media_upload_game_files() {
  do_action('media_upload_file');
}

function myarcade_upload_mimes( $existing_mimes=array() ) {
  // Allow DCR file upload
  $existing_mimes['dcr'] = 'mime/type';

  return $existing_mimes;
}


/**
 * Adds some ajax functionalities
 */
function myarcade_load_ajax() {
  global $pagenow;

  if ( $pagenow == 'admin.php' ) {
    // jQuery
    wp_enqueue_script('jquery');

    if ( isset($_GET['page']) && ( ($_GET['page'] == 'myarcade-manage-games') || ($_GET['page'] == 'myarcade-manage-scores') || ($_GET['page'] == 'myarcade-fetch') ) ) {
      // Thickbox
      wp_enqueue_script('thickbox');
      $thickcss = get_option('siteurl')."/".WPINC."/js/thickbox/thickbox.css";
      wp_enqueue_style('thickbox_css', $thickcss, false, false, 'screen');
    }
  }

  if ( $pagenow == 'admin.php' || $pagenow == 'post.php' || ( isset($_GET['page']) && $_GET['page'] == 'myarcade_admin.php') ) {
    // Add MyArcade CSS
    $css = MYARCADE_CORE_URL."/myarcadeplugin.css";
    wp_enqueue_style('myarcade_css', $css, false, false, 'screen');
  }
}

/**
 * Shows the overview page in WordPress backend
 *
 * @global  $wpdb
 */
function myarcade_show_stats() {
  global $wpdb;

  myarcade_header();

  ?>
  <div id="icon-index" class="icon32"><br /></div>
  <h2><?php _e("Dashboard"); ?></h2>
  <?php
  // Get Settings
  $general = get_option('myarcade_general');

  $unpublished_games  = intval($wpdb->get_var("SELECT COUNT(*) FROM ".MYARCADE_GAME_TABLE." WHERE status = 'new'"));

  // Calculate next cron execution
  $next_fetch_cron = wp_next_scheduled('cron_fetching');
  $next_publish_cron = wp_next_scheduled('cron_publishing');
  $datetime = get_option('date_format') . ' @ ' . get_option('time_format');

  if ( !empty($next_fetch_cron) ) {
    $next_fetch_execution = gmdate($datetime, $next_fetch_cron + (get_option('gmt_offset') * 3600));
  }

  if ( !empty($next_publish_cron) ) {
    $next_publish_execution = gmdate($datetime, $next_publish_cron + (get_option('gmt_offset') * 3600));
  }

  // Get published posts
  $total_posts = wp_count_posts();
  ?>

    <div class="dash-left metabox-holder">
      <div class="postbox">
        <div class="statsico"></div>
        <h3 class="hndle"><span><?php _e('MyArcadePlugin Info', MYARCADE_TEXT_DOMAIN) ?></span></h3>
        <div class="preloader-container">
          <div class="insider" id="boxy">

            <ul>
              <li><div class="mabp_info" style="padding:10px;margin-bottom: 10px;">
                  <strong>MyArcadePlugin Lite</strong> is a fully functional but limited version of <a href='http://myarcadeplugin.com' title='MyArcadePlugin Pro' target="_blank">MyArcadePlugin Pro</a>. Consider upgrading to get access to all premium features, premium support and premium bonuses.</div></li>
              <li><?php _e('Total Live Games / Posts', MYARCADE_TEXT_DOMAIN); ?>: <a href="edit.php?post_status=publish&post_type=post"><strong><?php echo $total_posts->publish; ?></strong></a></li>
              <li><?php _e('Total Scheduled Games / Posts', MYARCADE_TEXT_DOMAIN); ?>: <a href="edit.php?post_status=pending&post_type=post"><strong><?php echo $total_posts->future; ?></strong></a></li>
              <li><?php _e('Total Draft Games / Posts', MYARCADE_TEXT_DOMAIN); ?>: <a href="edit.php?post_status=draft&post_type=post"><strong><?php echo $total_posts->draft; ?></strong></a></li>

              <li>&nbsp;</li>

              <li><?php _e('Unpublished Games', MYARCADE_TEXT_DOMAIN); ?>: <strong><?php echo $unpublished_games; ?></strong></li>
              <li>
                <?php _e('Post Status', MYARCADE_TEXT_DOMAIN); ?>: <strong><?php echo $general['status']; ?></strong>
                <?php if ( $general['status'] == 'future') : ?>
                 , <strong><?php echo $general['schedule']; ?></strong> <?php _e('minutes schedule', MYARCADE_TEXT_DOMAIN); ?>.
                <?php endif; ?>
              </li>

              <li>
              <br />
                <?php _e('Cron Fetching', MYARCADE_TEXT_DOMAIN); ?>:
                <?php if ( $general['automated_fetching'] ) : ?>
                  <?php _e('Next schedule on', MYARCADE_TEXT_DOMAIN); ?> <strong><?php echo $next_fetch_execution; ?></strong>
                <?php else: ?>
                  <strong><?php _e('inactive', MYARCADE_TEXT_DOMAIN); ?></strong>
                <?php endif; ?>
              </li>
              <li>
                <?php _e('Cron Publishing', MYARCADE_TEXT_DOMAIN); ?>:
                <?php if ( $general['automated_publishing'] ) : ?>
                  <?php _e('Next schedule on', MYARCADE_TEXT_DOMAIN); ?> <strong><?php echo $next_publish_execution; ?></strong>
                <?php else: ?>
                  <strong><?php _e('inactive', MYARCADE_TEXT_DOMAIN); ?></strong>
                <?php endif; ?>
              </li>

              <li>&nbsp;</li>

              <li><?php _e('Download Games', MYARCADE_TEXT_DOMAIN); ?>: <strong><?php if ($general['down_games']) _e('Yes', MYARCADE_TEXT_DOMAIN); else _e('No', MYARCADE_TEXT_DOMAIN);  ?></strong></li>
              <li><?php _e('Download Thumbnails', MYARCADE_TEXT_DOMAIN); ?>: <strong><?php if ($general['down_thumbs']) _e('Yes', MYARCADE_TEXT_DOMAIN); else _e('No', MYARCADE_TEXT_DOMAIN);  ?></strong></li>
              <li><?php _e('Download Screenshots', MYARCADE_TEXT_DOMAIN); ?>: <strong><?php if ($general['down_screens']) _e('Yes', MYARCADE_TEXT_DOMAIN); else _e('No', MYARCADE_TEXT_DOMAIN);  ?></strong></li>

              <li>&nbsp;</li>

              <li><?php _e('Product Support', MYARCADE_TEXT_DOMAIN); ?>:  <a href="http://myarcadeplugin.com/support/" target="_new"><?php _e('Forum', MYARCADE_TEXT_DOMAIN); ?></a><li>
            </ul>

            <div class="clear"> </div>
          </div>
        </div>
      </div><!-- postbox end -->

      <div class="postbox">
        <div class="joystickico"></div>
          <h3 class="hndle" id="poststuff"><span><?php _e('Premium Arcade Themes', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
              <p>
              <?php
              $rss = fetch_feed('http://exells.com/special-offer/feed/?withoutcomments=1');
              if ( is_wp_error( $rss ) ) {
                echo '<p>'; _e('Sorry, can not download the feed', MYARCADE_TEXT_DOMAIN); echo '</p>';
              } else {
                $rss_item = $rss->get_item(0);
                echo $rss_item->get_content();
              }
              ?>
              </p>
              <div class="clear">&nbsp;</div>
            </div> <!-- inside end -->
          </div>
        </div> <!-- postbox end -->

      <div class="postbox">
        <div class="statsico"></div>
          <!-- <a target="_new" href="#"><div class="joystickico"></div></a> -->
          <h3 class="hndle" id="poststuff"><span><?php _e('MyArcade Traffic Exchange Network', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
              <p>Join our Banner / Traffic Exchange Network to boost your traffic and to increase the popularity of your site. You will receive 10.000 banner impressions on register for FREE!</p>
               <center><a href="http://exchange.myarcadeplugin.com" target="_blank" title="MyArcade Traffic Exchange Network"> MyArcade Traffic / Banner Exchange Network</a></center>
            </div> <!-- inside end -->
          </div>
        </div> <!-- postbox end -->


    </div><!-- end dash-left -->

    <div class="dash-right metabox-holder">
      <div class="postbox">
        <div class="dollarico"></div>
          <h3 class="hndle" id="poststuff"><span><?php _e('Make Extra Money', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
               <p>With MyArcadePlugin Pro affiliate program you can be a part of our success.</p><p>You will earn up to <strong>30%</strong> commission on any sale you refer! <a href="http://myarcadeplugin.com/affiliate-program/" title="MyArcadePlugin Affiliate Programm">Join our affiliate program</a>, promote MyArcadePlugin Pro and earn extra money!</p>
            </div> <!-- inside end -->
          </div>
        </div> <!-- postbox end -->

      <div class="postbox">
        <div class="newsico"></div>
          <h3 class="hndle" id="poststuff"><span><?php _e('Lastest MyArcadePlugin News', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
            <?php
               wp_widget_rss_output('http://myarcadeplugin.com/feed', array('items' => 5, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 0));
            ?>
            </div> <!-- inside end -->
          </div>
        </div> <!-- postbox end -->

      <div class="postbox">
        <div class="newsico"></div>
          <h3 class="hndle" id="poststuff"><span><?php _e('Lastest exells.com News', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
            <?php
               wp_widget_rss_output('http://exells.com/feed/', array('items' => 5, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 0));
            ?>
            </div> <!-- inside end -->
          </div>
        </div> <!-- postbox end -->

      <div class="postbox">
        <div class="facebookico"></div>
          <h3 class="hndle" id="poststuff"><span><?php _e('Be Our Friend!', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
              <p style="text-align:center"><strong><?php _e('If you like MyArcadePlugin, become our friend on Facebook', MYARCADE_TEXT_DOMAIN); ?></strong></p>
              <p style="text-align:center;">
                <iframe src="http://www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FMyArcadePlugin%2F178161832232562&amp;width=300&amp;colorscheme=light&amp;show_faces=true&amp;stream=false&amp;header=false&amp;height=400" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:400px;" allowTransparency="true"></iframe>
              </p>
            </div> <!-- inside end -->
          </div>
        </div> <!-- postbox end -->
    </div><!-- end dash-right -->

    <div class="clear"></div>
     <strong>MyArcadePlugin Lite v<?php echo MYARCADE_VERSION;?></strong> | <strong><a href="http://myarcadeplugin.com/" target="_blank">MyArcadePlugin.com</a> </strong>


  <?php
  myarcade_footer();
}

/**
 * Shows the settings page and handels all setting changes
 *
 * @global <type> $wpdb
 */
function myarcade_edit_settings() {
  global $myarcade_distributors;

  myarcade_header();

  ?>
  <div id="icon-tools" class="icon32"><br /></div>
  <h2><?php _e("Settings"); ?></h2>
  <?php

  $action = isset($_POST['feedaction']) ? $_POST['feedaction'] : '';

  if ($action == 'save') {

    // Remove the settings update notice if set
    if ( get_transient('myarcade_settings_update_notice') == true  ) {
      delete_transient('myarcade_settings_update_notice');
    }

    $general = array();
    if ( isset($_POST['leaderboardenable'])) $general['scores'] = true; else $general['scores'] = false;
    if ( isset($_POST['onlyhighscores'])) $general['highscores'] = true; else $general['highscores'] = false;
    if ( isset($_POST['game_count'])) $general['posts'] = intval($_POST['game_count']); else $general['posts'] = '';
    if ( isset($_POST['publishstatus'])) $general['status'] = $_POST['publishstatus']; else $general['status'] = 'publish';
    if ( isset($_POST['schedtime'])) $general['schedule'] = intval( $_POST['schedtime']); else $general['schedule'] = 0;
    if ( isset($_POST['downloadthumbs'])) $general['down_thumbs'] = true; else $general['down_thumbs'] = false;
    if ( isset($_POST['downloadgames'])) $general['down_games'] = true; else $general['down_games'] = false;
    if ( isset($_POST['downscreens'])) $general['down_screens'] = true; else $general['down_screens'] = false;
    if ( isset($_POST['deletefiles'])) $general['delete'] = true; else $general['delete'] = false;

    $general['folder_structure'] = (isset($_POST['folder_structure'])) ? $_POST['folder_structure'] : false;
    $general['automated_fetching']  = (isset($_POST['automated_fetching'])) ? true : false;
    $general['interval_fetching']   = $_POST['interval_fetching'];
    $general['automated_publishing']  = (isset($_POST['automated_publishing'])) ? true : false;
    $general['interval_publishing']   = $_POST['interval_publishing'];
    $general['swfobject'] = isset( $_POST['swfobject'] ) ? true : false;

    if ( isset($_POST['createcats'])) $general['create_cats'] = true; else $general['create_cats'] = false;
    if ( isset($_POST['parentcatid'])) $general['parent'] = $_POST['parentcatid']; else $general['parent'] = '';
    if ( isset($_POST['firstcat'])) $general['firstcat'] = true; else $general['firstcat'] = false;
    if ( isset($_POST['maxwidth'])) $general['max_width'] = intval($_POST['maxwidth']); else $general['max_width'] = '';
    if ( isset($_POST['singlecat'])) $general['single'] = true; else $general['single'] = false;
    if ( isset($_POST['singlecatid'])) $general['singlecat'] = $_POST['singlecatid']; else $general['singlecat'] = '';
    if ( isset($_POST['embedflashcode'])) $general['embed'] = $_POST['embedflashcode']; else $general['embed'] = 'manually';
    if ( isset($_POST['usetemplate'])) $general['use_template'] = true; else $general['use_template'] = false;
    if ( isset($_POST['post_template'])) $general['template'] = stripslashes($_POST['post_template']); else $general['template'] = '';
    if ( isset($_POST['allow_user'])) $general['allow_user'] = true; else $general['allow_user'] = false;
    if ( isset($_POST['limitplays'])) $general['limit_plays'] = intval($_POST['limitplays']); else $general['limit_plays'] = 0;
    if ( isset($_POST['limitmessage'])) $general['limit_message'] = stripslashes($_POST['limitmessage']); else $general['limit_message'] = '';
    if ( isset($_POST['posttype'])) $general['post_type'] = $_POST['posttype']; else $general['post_type'] = 'post';
    if ( isset($_POST['featured_image'])) $general['featured_image'] = true; else $general['featured_image'] = false;

    $general['play_delay'] = isset($_POST['play_delay']) ? $_POST['play_delay'] : '30';
    $general['translation'] = $_POST['translation'];
    $general['bingid'] = isset($_POST['bingid']) ? sanitize_text_field($_POST['bingid']) : '';
    $general['bingsecret'] = isset($_POST['bingsecret']) ? sanitize_text_field($_POST['bingsecret']) : '';
    $general['translate_to'] = isset($_POST['translate_to']) ? $_POST['translate_to'] : 'en';
    $general['translate_fields'] = isset($_POST['translate_fields']) ? $_POST['translate_fields'] : array();
    $general['translate_games'] = isset($_POST['translate_games']) ? $_POST['translate_games'] : array();
    $general['google_id'] = isset($_POST['google_id']) ? sanitize_text_field($_POST['google_id']) : '';
    $general['google_translate_to'] = $_POST['google_translate_to'];

    // Custom taxonomies
    $general['custom_category'] =  isset($_POST['customtaxcat']) ? $_POST['customtaxcat'] : '';
    $general['custom_tags'] = isset($_POST['customtaxtag']) ? $_POST['customtaxtag'] : '';
    $general['disable_game_tags'] = isset( $_POST['disable_game_tags'] ) ? true : false;

    // Update Settings
    update_option('myarcade_general', $general);

    // Kongeregate Settings
    $kongregate = array();
    if ( isset($_POST['kongurl'])) $kongregate['feed'] = esc_url_raw($_POST['kongurl']); else $kongregate['feed'] = '';

    $kongregate['cron_publish']        = (isset($_POST['kong_cron_publish']) ) ? true : false;
    $kongregate['cron_publish_limit']  = (isset($_POST['kong_cron_publish_limit']) ) ? intval($_POST['kong_cron_publish_limit']) : 1;

      // Update Settings
      update_option('myarcade_kongregate', $kongregate);

    $fgd = array();
    if ( isset($_POST['fgdurl'])) $fgd['feed'] = esc_url_raw($_POST['fgdurl']); else $fgd['feed'] = '';
    if ( isset($_POST['fgdlimit'])) $fgd['limit'] = intval($_POST['fgdlimit']); else $fgd['limit'] = '50';

    $fgd['cron_fetch']          = (isset($_POST['fgd_cron_fetch'])) ? true : false;
    $fgd['cron_fetch_limit']    = (isset($_POST['fgd_cron_fetch_limit']) ) ? intval($_POST['fgd_cron_fetch_limit']) : 1;
    $fgd['cron_publish']        = (isset($_POST['fgd_cron_publish']) ) ? true : false;
    $fgd['cron_publish_limit']  = (isset($_POST['fgd_cron_publish_limit']) ) ? intval($_POST['fgd_cron_publish_limit']) : 1;
      // Update Settings
      update_option('myarcade_fgd', $fgd);

    // FreeGamesForYourSite Settings
    $fog = array();
    if ( isset($_POST['fogurl'])) $fog['feed'] = esc_url_raw($_POST['fogurl']); else $fog['feed'] = '';
    if ( isset($_POST['foglimit'])) $fog['limit'] = sanitize_text_field($_POST['foglimit']); else $fog['limit'] = '20';
    if ( isset($_POST['fogthumbsize'])) $fog['thumbsize'] = trim($_POST['fogthumbsize']); else $fog['thumbsize'] = 'small';
    if ( isset($_POST['fogscreen'])) $fog['screenshot'] = true; else $fog['screenshot'] = false;
    if ( isset($_POST['fogtag'])) $fog['tag'] = sanitize_text_field($_POST['fogtag']); else $fog['tag'] = 'all';

    $fog['cron_fetch']          = (isset($_POST['fog_cron_fetch'])) ? true : false;
    $fog['cron_fetch_limit']    = (isset($_POST['fog_cron_fetch_limit']) ) ? intval($_POST['fog_cron_fetch_limit']) : 1;
    $fog['cron_publish']        = (isset($_POST['fog_cron_publish']) ) ? true : false;
    $fog['cron_publish_limit']  = (isset($_POST['fog_cron_publish_limit']) ) ? intval($_POST['fog_cron_publish_limit']) : 1;
    $fog['language']            = (isset($_POST['foglanguage']) ) ? $_POST['foglanguage'] : 'en';
      // Update Settings
      update_option('myarcade_fog', $fog);

    // Spil Games Settings
    $spilgames = array();
    if ( isset($_POST['spilgamesurl'])) $spilgames['feed'] = esc_url_raw($_POST['spilgamesurl']); else $spilgames['feed'] = '';
    if ( isset($_POST['spilgameslimit'])) $spilgames['limit'] = sanitize_text_field($_POST['spilgameslimit']); else $spilgames['limit'] = '20';
    if ( isset($_POST['spilgamesthumbsize'])) $spilgames['thumbsize'] = trim($_POST['spilgamesthumbsize']); else $spilgames['thumbsize'] = 'small';
    if ( isset($_POST['spilgameslanguage'])) $spilgames['language'] = trim($_POST['spilgameslanguage']); else $spilgames['language'] = 'default';
    $spilgames['cron_fetch']          = (isset($_POST['spilgames_cron_fetch'])) ? true : false;
    $spilgames['cron_fetch_limit']    = (isset($_POST['spilgames_cron_fetch_limit']) ) ? intval($_POST['spilgames_cron_fetch_limit']) : 1;
    $spilgames['cron_publish']        = (isset($_POST['spilgames_cron_publish']) ) ? true : false;
    $spilgames['cron_publish_limit']  = (isset($_POST['spilgames_cron_publish_limit']) ) ? intval($_POST['spilgames_cron_publish_limit']) : 1;
      // Update Settings
      update_option('myarcade_spilgames', $spilgames);

    // MyArcadeFeed
    $myarcadefeed = array();
    $myarcadefeed['feed1'] = (isset($_POST['myarcadefeed1'])) ? esc_url_raw($_POST['myarcadefeed1']) : '';
    $myarcadefeed['feed2'] = (isset($_POST['myarcadefeed2'])) ? esc_url_raw($_POST['myarcadefeed2']) : '';
    $myarcadefeed['feed3'] = (isset($_POST['myarcadefeed3'])) ? esc_url_raw($_POST['myarcadefeed3']) : '';
    $myarcadefeed['feed4'] = (isset($_POST['myarcadefeed4'])) ? esc_url_raw($_POST['myarcadefeed4']) : '';
    $myarcadefeed['feed5'] = (isset($_POST['myarcadefeed5'])) ? esc_url_raw($_POST['myarcadefeed5']) : '';
    $myarcadefeed['all_categories'] = (isset($_POST['myarcadefeed_all_categories'])) ? true : false;
      // Update Settings
      update_option('myarcade_myarcadefeed', $myarcadefeed);

    $bigfish = array();
    $bigfish['username'] = (isset($_POST['big_username'])) ? sanitize_text_field($_POST['big_username']) : '';
    $bigfish['affiliate_code'] = (isset($_POST['big_affiliate_code'])) ? sanitize_text_field($_POST['big_affiliate_code']) : '';
    $bigfish['locale'] = (isset($_POST['big_locale'])) ? $_POST['big_locale'] : 'en';
    $bigfish['gametype'] = (isset($_POST['big_gametype'])) ? $_POST['big_gametype'] : 'og';
    $bigfish['template'] = (isset($_POST['big_template'])) ? esc_textarea($_POST['big_template']) : '';
    $bigfish['thumbnail'] = (isset($_POST['big_thumbnail'])) ? $_POST['big_thumbnail'] : 'medium';

    $bigfish['cron_publish']        = (isset($_POST['big_cron_publish']) ) ? true : false;
    $bigfish['cron_publish_limit']  = (isset($_POST['big_cron_publish_limit']) ) ? intval($_POST['big_cron_publish_limit']) : 1;
      // Update Settings
      update_option('myarcade_bigfish', $bigfish);

    // Scirra Settings
    $scirra = array();
    if ( isset($_POST['scirra_url'])) $scirra['feed'] = esc_url_raw($_POST['scirra_url']); else $scirra['feed'] = '';
    if ( isset($_POST['scirra_thumbnail'])) $scirra['thumbnail'] = trim($_POST['scirra_thumbnail']); else $scirra['thumbnail'] = 'medium';

    $scirra['cron_publish']        = (isset($_POST['scirra_cron_publish']) ) ? true : false;
    $scirra['cron_publish_limit']  = (isset($_POST['scirra_cron_publish_limit']) ) ? intval($_POST['scirra_cron_publish_limit']) : 1;
      // Update Settings
      update_option('myarcade_scirra', $scirra);

    // GameFeed Settings
    $gamefeed = array();
    $gamefeed['status'] = $_POST['gamefeed_status'];
    $gamefeed['cron_publish']        = (isset($_POST['gamefeed_cron_publish']) ) ? true : false;
    $gamefeed['cron_publish_limit']  = (isset($_POST['gamefeed_cron_publish_limit']) ) ? intval($_POST['gamefeed_cron_publish_limit']) : 1;
      // Update Settings
      update_option( 'myarcade_gamefeed', $gamefeed );

    // UnityFeeds
    $unityfeeds = array();
    $unityfeeds['feed'] = (isset($_POST['unityfeeds_url'])) ? esc_url_raw($_POST['unityfeeds_url']) : '';
    $unityfeeds['category'] = (isset($_POST['unityfeeds_category'])) ? esc_sql($_POST['unityfeeds_category']) : 'all';
    $unityfeeds['thumbnail'] = (isset($_POST['unityfeeds_thumbnail'])) ? esc_sql($_POST['unityfeeds_thumbnail']) : '100x100';
    $unityfeeds['screenshot'] = (isset($_POST['unityfeeds_screenshot'])) ? esc_sql($_POST['unityfeeds_screenshot']) : '300x300';
    $unityfeeds['cron_publish']        = (isset($_POST['unityfeeds_cron_publish']) ) ? true : false;
    $unityfeeds['cron_publish_limit']  = (isset($_POST['unityfeeds_cron_publish_limit']) ) ? intval($_POST['unityfeeds_cron_publish_limit']) : 1;
      // Update Settings
      update_option('myarcade_unityfeeds', $unityfeeds);

    // END Settings Updates
    //_________________________________________________________________________

    //
    // Create Game Categories
    //
    if ( isset($_POST['gamecats'])) $categories_post = $_POST['gamecats']; else $categories_post = array();

    // Get current settings
    $feedcategories = get_option('myarcade_categories');

    // count checked categories
    $cat_count = count($categories_post);
    $feedcat_count = count($feedcategories);

    if ($cat_count > 0) {
      for ($i = 0; $i < $feedcat_count; $i++) {
        foreach ($categories_post as $selected_cat) {
          if ( $feedcategories[$i]['Slug'] == $selected_cat) {
            $feedcategories[$i]['Status'] = 'checked';

            if ($general['create_cats'] == true) {

              if ($general['post_type'] == 'post') {

                // Get Cat ID
                $game_catID = get_cat_ID(htmlspecialchars($feedcategories[$i]['Name']));

                if ($game_catID == 0) {
                  // Create Category
                  $create_cat = array('cat_name' => $feedcategories[$i]['Name'],
                                      'category_description' => $feedcategories[$i]['Name'],
                                      'category_nicename' => $feedcategories[$i]['Slug'],
                                      'category_parent' =>  $general['parent']
                                );

                  if ( !wp_insert_category($create_cat) ) {
                    echo '<p class="mabp_error mabp_800">'.__("Failed to create category:", MYARCADE_TEXT_DOMAIN).' '.$feedcategories[$i][Name].'</p>';
                  }
                }
              }
              else {
                // We have a custom post type... Check if custom taxonomy is selected
                if ( post_type_exists($general['post_type']) ) {
                  if ( !empty($general['custom_category']) && taxonomy_exists($general['custom_category']) ) {
                    if ( !term_exists($feedcategories[$i]['Name'], $general['custom_category']) ) {
                      // Add custom taxonomy
                      $insert_result = wp_insert_term (
                              $feedcategories[$i]['Name'],
                              $general['custom_category'],
                              array (
                                  'description' => $feedcategories[$i]['Name'],
                                  'slug' => $feedcategories[$i]['Slug']
                                  )
                              );

                      if ( is_wp_error($insert_result) ) {
                        echo '<p class="mabp_error mabp_800">'.__("Failed to create category:", MYARCADE_TEXT_DOMAIN).' '.$insert_result->get_error_message().'</p>';
                      }
                    }
                  }
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

      // Update categories
      update_option('myarcade_categories', $feedcategories);
    }
    else {
      echo '<p class="mabp_error mabp_800">'.__("You have to check at least one feed category!", MYARCADE_TEXT_DOMAIN).'</p>';
    }

    echo '<p class="mabp_info mabp_800">'.__("Your settings have been updated!", MYARCADE_TEXT_DOMAIN).'</p>';
  } // END - if action

  if ( isset($_POST['loaddefaults']) && isset($_POST['checkdefaults']) && $_POST['checkdefaults'] == 'yes' ) {
    myarcade_load_default_settings();
    echo '<p class="mabp_info mabp_800">'.__("Default settings have been restored!", MYARCADE_TEXT_DOMAIN).'</p>';
  }


  // Get settings
  $general    = get_option('myarcade_general');
  $fgd        = get_option('myarcade_fgd');
  $categories = get_option('myarcade_categories');
  $kongregate = get_option('myarcade_kongregate');
  $fog        = get_option('myarcade_fog');
  $spilgames  = get_option('myarcade_spilgames');
  $myarcadefeed = get_option('myarcade_myarcadefeed');
  $bigfish    = get_option('myarcade_bigfish');
  $scirra     = get_option('myarcade_scirra');
  $gamefeed   = get_option('myarcade_gamefeed');
  $unityfeeds = get_option('myarcade_unityfeeds');

  if ( $general['down_games'] ) {
    if ( !file_exists(ABSPATH.MYARCADE_GAMES_DIR) ) {
      @mkdir(ABSPATH.MYARCADE_GAMES_DIR, 0777);
    }

    if (!is_writable(ABSPATH.MYARCADE_GAMES_DIR)) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The games directory '%s' must be writable (chmod 777) in order to download games.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_GAMES_DIR).'</p>';
    }
  }

  if ( $general['down_thumbs'] ) {
    if (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR)) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The thumbails directory '%s' must be writable (chmod 777) in order to download thumbnails.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
    }
  }


  if ( $general['down_screens'] ) {
    if ( !file_exists(ABSPATH.MYARCADE_THUMBS_DIR) ) {
      @mkdir(ABSPATH.MYARCADE_THUMBS_DIR, 0777);
    }

    if (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR)) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The thumbails directory '%s' must be writable (chmod 777) in order to download game screenshots.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
    }
  }

  // Check Application ID for Bing Translator
  if ( ($general['translation'] == 'bing') && empty( $general['bingid'] ) ) {
    echo '<p class="mabp_error mabp_800">'.__("You have activated the Bing Translator but not entered your Application ID. In this case the translator will not work!", MYARCADE_TEXT_DOMAIN).'</p>';
  }
  if ( ($general['translation'] == 'google') && empty( $general['google_id'] ) ) {
    echo '<p class="mabp_error mabp_800">'.__("You have activated the Google Translator but not entered your Google API Key. In this case the translator will not work!", MYARCADE_TEXT_DOMAIN).'</p>';
  }

  // Get all categories
  if ( $general['post_type'] == 'post') {
    $categs_ids_tmp = get_all_category_ids();
    $categs_tmp = array();

    foreach ($categs_ids_tmp as $categ_id_tmp) {
      $categs_tmp[$categ_id_tmp] = get_cat_name($categ_id_tmp);
    }
  }
  else {
    $categs_tmp = array();

    if (taxonomy_exists($general['custom_category']) ) {
      $taxonomies = get_terms($general['custom_category'], array('hide_empty' => false));

      foreach ($taxonomies as $taxonomy) {
        $categs_tmp[$taxonomy->term_id] = $taxonomy->name;
      }
    }
  }

  // Create an array with all available cron intervals
  global $myarcade_cron_intervals;

  $default_crones = array(
    'hourly'    => array('display' => __('Hourly')),
    'twicedaily'=> array('display' => __('Twice Daily')),
    'daily'     => array('display' => __('Daily')),
  );

  $crons =array_merge($myarcade_cron_intervals,$default_crones);
  ?>
    <br />

    <div id="myarcade_settings">
      <div class="mabp_info" style="padding:10px;margin-bottom: 10px;">
                  <strong>MyArcadePlugin Lite</strong> is a fully functional but limited version of <a href='http://myarcadeplugin.com' title='MyArcadePlugin Pro' target="_blank">MyArcadePlugin Pro</a>. Consider upgrading to get access to all premium features, premium support and premium bonuses.</div>
      <form method="post" name="editsettings">
        <input type="hidden" name="feedaction" value="save">

        <?php
        //----------------------------------------------------------------------
        // General Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("General Settings", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%">

              <tr><td colspan="2"><h3><?php _e("Save User Scores", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="leaderboardenable" value="true" <?php myarcade_checked($general['scores'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to collect user scores. Only scores from Mochi and IBPArcade games will be collected.", MYARCADE_TEXT_DOMAIN); ?></i><br /></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Save Only Highscores", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="onlyhighscores" value="true" <?php myarcade_checked($general['highscores'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to only save a user's highest score. Otherwise all submitted scores are saved.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="game_count" value="<?php echo $general['posts']; ?>" />
                </td>
                <td><i><?php _e('How many games should be published when clicking on "Publish Games"?', MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Post Status", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="radio" name="publishstatus" value="publish" <?php myarcade_checked($general['status'], 'publish'); ?> /><label class="opt">&nbsp;<?php _e("Publish", MYARCADE_TEXT_DOMAIN); ?></label>
                  <input type="radio" name="publishstatus" value="future" <?php myarcade_checked($general['status'], 'future'); ?> /><label class="opt">&nbsp;<?php _e("Scheduled", MYARCADE_TEXT_DOMAIN); ?></label>
                  <input type="radio" name="publishstatus" value="draft" <?php myarcade_checked($general['status'], 'draft'); ?> /><label class="opt">&nbsp;<?php _e("Draft", MYARCADE_TEXT_DOMAIN); ?></label>
                  <br /><br />
                  <?php _e("Schedule Time", MYARCADE_TEXT_DOMAIN); ?>: <input type="text" size="10" name="schedtime" value="<?php echo $general['schedule']; ?>">
                </td>
                <td><i><?php _e("Choose the post status for new game publication. If you whish to schedule new game publication, indicate an interval between publications in minutes.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Download Thumbnails", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="downloadthumbs" value="true" <?php myarcade_checked($general['down_thumbs'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Should the game thumbnails be imported and saved on your web server? For this to work properly, the thumb directory (wp-content/thumbs/) must be writable.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Download Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="downloadgames" value="true"  <?php myarcade_checked($general['down_games'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Should the game be imported and saved on your web server? For this to work properly, the game directory (wp-content/games/) must be writable.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Download Screenshots", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="downscreens" value="true"  <?php myarcade_checked($general['down_screens'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Should the game screenshots be imported and stored on your web server? For this to work properly, the thumb directory (wp-content/thumbs/) must be  writable.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Delete Game Files", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="deletefiles" value="true" <?php myarcade_checked($general['delete'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("This option will delete the associated game files from your server after deleting the post from your blog. Warning - deleted games cannot be re-published! For this to work properly, the games and thumbs directories must be writable.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Folder Organization Structure", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="folder_structure" value="<?php echo $general['folder_structure']; ?>" />
                </td>
                <td><i><?php _e('Define the folder structure for file downloads. Available variables are %game_type% and %alphabetical%. You can combine those variables like this: %game_type%/%alphabetical%/.', MYARCADE_TEXT_DOMAIN); ?><br />
                    <?php _e('That means, for each game type a new folder will be created and files will be organized in sub folders. Example: "/games/fog/A/awesome_game.swf.', MYARCADE_TEXT_DOMAIN); ?><br />
                    <?php _e('Leave blank if you want to save all files in a single folder."', MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>


              <tr><td colspan="2"><h3><?php _e("Automation / Cron Settings", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3></td></tr>
              <tr><td colspan="2"><p><?php _e("Global automation settings allows you to enable and setup automated fetching and publishing globally. You can enable/disable automated fetching and publishing for each game distributor separately when you click on distributors settings.", MYARCADE_TEXT_DOMAIN); ?></p></td></tr>

              <tr><td colspan="2"><h4><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="automated_fetching" value="true" <?php myarcade_checked($general['automated_fetching'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("This option will activate automated game fetching globally. If activated the cron job will be triggered by WordPress.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Game Fetching Interval", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <select size="1" name="interval_fetching" id="interval_fetching">
                    <?php
                    foreach($crons as $cron => $val) {
                      ?>
                      <option value="<?php echo $cron; ?>" <?php myarcade_selected($general['interval_fetching'], $cron); ?> ><?php echo $val['display']; ?></option>
                      <?php
                    }
                    ?>
                  </select>
                </td>
                <td><i><?php _e("Select a frequency for fetching new games. Games are fetched per the scheduled frequency, pending a user visiting your site (which triggers the function).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="automated_publishing" value="true" <?php myarcade_checked($general['automated_publishing'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("This option will activate automated game publishing globally. If activated the cron job will be triggered by WordPress.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Game Publishing Interval", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <select size="1" name="interval_publishing" id="interval_publishing">
                    <?php
                    foreach($crons as $cron => $val) {
                      ?>
                      <option value="<?php echo $cron; ?>" <?php myarcade_selected($general['interval_publishing'], $cron); ?> ><?php echo $val['display']; ?></option>
                      <?php
                    }
                    ?>
                  </select>
                </td>
                <td><i><?php _e("Select a frequency for publishing new games. Games are published per the scheduled frequency, pending a user visiting your site (which triggers the function).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>



              <tr><td colspan="2"><h3><?php _e("Game Categories To Feed", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                <?php
                  foreach ($categories as $feedcat) {
                    echo '<input type="checkbox" name="gamecats[]" value="'.$feedcat['Slug'].'" '.$feedcat['Status'].' /><label class="opt">&nbsp;'.$feedcat['Name'].' '.$feedcat['Info'].'</label><br />';
                  }
                ?>
                </td>
                <td><i><?php _e("Select game categories that should be fetched.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Create Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="createcats" value="true" <?php myarcade_checked($general['create_cats'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to create selected categories on your blog.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>


              <tr><td colspan="2"><h3><?php _e("Parent Category", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
                <tr>
                  <td>
                    <select size="1" name="parentcatid" id="parentcatid">
                    <option value=''>--- <?php _e("No Parent Category", MYARCADE_TEXT_DOMAIN); ?> ---</option>
                    <?php
                      // Get selected category
                      foreach ($categs_tmp as $cat_tmp_id => $cat_tmp_name) {
                        if ($cat_tmp_id == $general['parent']) { $cat_selected = 'selected'; } else { $cat_selected = ''; }
                        echo '<option value="'.$cat_tmp_id.'" '.$cat_selected.'>'.$cat_tmp_name.'</option>';
                      }
                    ?>
                    </select>
                  </td>
                  <td><i><?php _e("This option will create game categories as subcategories in the selected category.", MYARCADE_TEXT_DOMAIN); ?> <?php _e(" This option is useful if you have a mixed site and not only a pure arcade site.", MYARCADE_TEXT_DOMAIN); ?></i></td>
                </tr>

                <?php // Use only the first category ?>
                <tr>
                  <td colspan="2">
                    <h3><?php _e("Use Only The First Category", MYARCADE_TEXT_DOMAIN); ?></h3>
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="checkbox" name="firstcat" value="true" <?php myarcade_checked($general['firstcat'], true); ?> />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?>
                  </td>
                  <td><i><?php _e("Many game developers tag their games to more than one category to get more downloads. Thereby the gamess will be added to several categories. Activate this option to avoid game publishing in more than one category.", MYARCADE_TEXT_DOMAIN); ?></i></td>
                </tr>

              <tr><td colspan="2"><h3><?php _e("Max. Game Width (optional)", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40" name="maxwidth" value="<?php echo $general['max_width']; ?>" />
                </td>
                <td><i><?php _e("Maximum allowed game width in px. If set, the get_game function will create output code with adjusted game dimensions.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Publish In A Single Category", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="singlecat" value="true" <?php myarcade_checked($general['single'], true); ?> />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?>
                  <select size="1" name="singlecatid" id="singlecatid">
                  <?php
                    // Get selected category
                    foreach ($categs_tmp as $cat_tmp_id => $cat_tmp_name) {
                      if ($cat_tmp_id == $general['singlecat']) { $cat_selected = 'selected'; } else { $cat_selected = ''; }
                      echo '<option value="'.$cat_tmp_id.'" '.$cat_selected.'/>'.$cat_tmp_name.'</option>';
                    }
                  ?>
                  </select>
                </td>
                <td><i><?php _e("This option will publish all games only in the selected category.", MYARCADE_TEXT_DOMAIN); ?> <?php _e("This option is useful if you have a mixed site and not only a pure arcade site.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Embed Flash Code", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                <select size="1" name="embedflashcode" id="embedflashcode">
                  <option value="manually" <?php myarcade_selected($general['embed'], 'manually'); ?> ><?php _e("Manually", MYARCADE_TEXT_DOMAIN); ?></option>
                  <option value="top" <?php myarcade_selected($general['embed'], 'top'); ?> ><?php _e("At The Top Of A Game Post", MYARCADE_TEXT_DOMAIN); ?></option>
                  <option value="bottom" <?php myarcade_selected($general['embed'], 'bottom'); ?> ><?php _e("At The Bottom Of A Game Post", MYARCADE_TEXT_DOMAIN); ?></option>
                </select>
                </td>
                <td><i><?php _e("Select if MyArcadePlugin Pro should auto embed the flash code in your game posts (only if you don't use FunGames theme).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Use SWFObject", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="swfobject" value="true" <?php myarcade_checked( $general['swfobject'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Activate this if you want to use SWFObject to embed Flash games.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Game Post Template", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="usetemplate" value="true" <?php myarcade_checked($general['use_template'], true); ?> /><label class="opt">&nbsp;<?php _e("Activate Post Template", MYARCADE_TEXT_DOMAIN); ?></label>
                  <br /><br />
                  <textarea rows="12" cols="40" id="post_template" name="post_template"><?php echo htmlspecialchars(stripslashes($general['template'])); ?></textarea>
                </td>
                <td><i>
                    <?php _e("Use this template to style the output of MyArcadePlugin Pro when creating game posts.", MYARCADE_TEXT_DOMAIN); ?>
                    <br />
                     <strong><?php _e("Available Variables", MYARCADE_TEXT_DOMAIN); ?>:</strong><br />
                    %TITLE% - <?php _e("Show the game title", MYARCADE_TEXT_DOMAIN); ?><br />
                    %DESCRIPTION% - <?php _e("Show game description", MYARCADE_TEXT_DOMAIN); ?><br />
                    %INSTRUCTIONS% - <?php _e("Show game instructions if available", MYARCADE_TEXT_DOMAIN); ?><br />
                    %TAGS% - <?php _e("Show all game tags", MYARCADE_TEXT_DOMAIN); ?><br />
                    %THUMB% - <?php _e("Show the game thumbnail", MYARCADE_TEXT_DOMAIN); ?><br />
                    %THUMB_URL% - <?php _e("Show game thumbnail URL", MYARCADE_TEXT_DOMAIN); ?><br />
                    %SWF_URL% - <?php _e("Show game SWF URL / Embed Code", MYARCADE_TEXT_DOMAIN); ?><br />
                    %WIDTH% - <?php _e("Show game width", MYARCADE_TEXT_DOMAIN); ?><br />
                    %HEIGHT% - <?php _e("Show game height", MYARCADE_TEXT_DOMAIN); ?><br />
                  </i></td>
              </tr>

              <?php // Disable game tags ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Disable Game Tags", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="checkbox" name="disable_game_tags" value="true" <?php myarcade_checked($general['disable_game_tags'], true); ?> />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?>
                </td>
                <td><i><?php _e("Check this if you want to prevent MyArcadePlugin from adding tags to WordPress posts (not recommended).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Allow users to post games?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Allow Users To Post Games", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="checkbox" name="allow_user" value="true" <?php myarcade_checked($general['allow_user'], true); ?> />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?>
                </td>
                <td><i><?php _e("Activate this if you want to give your users access to import games. WordPress supports following user roles: Contributor, Author and Editor. Games added by Contributors will be saved as drafts! Authors and Editors will be able to publish games.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Force guests to register after x plays ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Guest Plays", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="text" size="40" name="limitplays" value="<?php echo $general['limit_plays']; ?>" />
                </td>
                <td><i><?php _e("Set how many games a guest can play before he/she needs to register. Set to 0 to deactivate the game play check.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Message ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Guest Message", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <textarea rows="12" cols="40" id="limitmessage" name="limitmessage"><?php echo htmlspecialchars(stripslashes($general['limit_message'])); ?></textarea>
                </td>
                <td><i><?php _e("Enter the message here that you want a guest to see after 'X' number of plays (HTML allowed)", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Game play delay ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Game Play Delay", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="text" size="40" name="play_delay" value="<?php echo $general['play_delay']; ?>" />
                </td>
                <td><i><?php _e("Game play delay is responsible for play, CubePoints and contest counter of a user. MyArcadePlugin will only count game plays when the delay time between two game plays is expired. Default value: 30 [time in seconds].", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Post Type ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Post Type", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <?php
                  $types = get_post_types();
                  $exclude = array('attachment', 'revision', 'nav_menu_item', 'page');
                  $types = array_diff($types, $exclude);
                  ?>
                  <select size="1" name="posttype" id="posttype">
                    <?php
                    foreach($types as $type) {
                      ?>
                      <option value="<?php echo $type; ?>" <?php myarcade_selected($general['post_type'], $type); ?>>
                        <?php echo $type; ?>
                      </option>
                    <?php } ?>
                  </select>
                </td>
                <td><i><?php _e("Select a post type you want to use with MyArcadePlugin. If you want to use a custom post type then you will need to create it before you can make a selection. The easiest way to create a custom post type is to use a plugin like 'Custom Post Type UI'.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr>
                <td colspan="2">
                  <h3><?php _e("Custom Taxonomies", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <?php
                  $custom_taxonomies = get_taxonomies(array('public'   => true,'_builtin' => false));
                  if ( !is_array($custom_taxonomies) || empty($custom_taxonomies)) {
                    ?>
                    <i><?php _e('No custom taxonomies found..', MYARCADE_TEXT_DOMAIN); ?></i>
                    <?php
                  } else {
                    ?>
                    <table>
                      <tr>
                      <td>Game Categories:</td>
                      <td>
                    <?php if ( is_array($custom_taxonomies) && !empty($custom_taxonomies)) : ?>
                      <select size="1" name="customtaxcat" id="customtaxcat">
                        <option value="">-- select a taxonomy --</option>
                        <?php foreach( $custom_taxonomies as $taxonomy) : ?>
                        <option value="<?php echo $taxonomy; ?>" <?php myarcade_selected($taxonomy , $general['custom_category']); ?>><?php echo $taxonomy; ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php endif; ?>
                      </td>
                      <td><?php _e("Select a custom taxonomy that should be used for game categories.", MYARCADE_TEXT_DOMAIN); ?></td>
                      </tr>
                      <tr>
                      <td>Game Tags:</td>
                      <td>
                    <?php if ( is_array($custom_taxonomies) && !empty($custom_taxonomies)) : ?>
                      <select size="1" name="customtaxtag" id="customtaxtag">
                        <option value="">-- select a taxonomy --</option>
                        <?php foreach( $custom_taxonomies as $taxonomy) : ?>
                        <option value="<?php echo $taxonomy; ?>" <?php myarcade_selected($taxonomy , $general['custom_tags']); ?>><?php echo $taxonomy; ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php endif; ?>
                      </td>
                      <td><?php _e("Select a custom taxonomy that should be used for game tags.", MYARCADE_TEXT_DOMAIN); ?></td>
                      </tr>
                    </table>
                    <?php
                  }
                  ?>
                </td>
              </tr>


              <?php // Featured Image ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Featured Image", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="checkbox" name="featured_image" value="true" <?php myarcade_checked($general['featured_image'], true); ?> />&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?>
                </td>
                <td><i><?php _e("Activate this option if you want MyArcadePlugin to attach game thumbnails to the created post as featured images. Use this only if you don't use a pure Arcade Theme.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>


        <?php
        //----------------------------------------------------------------------
        // MyArcadeFeed Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("MyArcadeFeed", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("Add up to five Feeds generated with MyArcadeFeed Plugin.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://exells.com/shop/products/myarcadefeed">here</a> to learn more about MyArcadeFeed.
                  </i>
                  <br /><br />
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 1", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed1" value="<?php echo $myarcadefeed['feed1']; ?>" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 1 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 2", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed2" value="<?php echo $myarcadefeed['feed2']; ?>" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 2 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 3", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed3" value="<?php echo $myarcadefeed['feed3']; ?>" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 3 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 4", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed4" value="<?php echo $myarcadefeed['feed4']; ?>" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 4 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 5", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed5" value="<?php echo $myarcadefeed['feed5']; ?>" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 5 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Fetch All Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="myarcadefeed_all_categories" value="true" <?php myarcade_checked($myarcadefeed['all_categories'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Activate this if you want to fetch all games independent of your activated categories.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // Spil Games Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("Spil Games", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("Spil Games provides a game feed with over 1500 games.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://publishers.spilgames.com/">here</a> to visit the Spil Games site.
                  </i>
                  <br /><br />
                  <p class="mabp_info" style="padding:10px">
                    <?php _e("Some 'Spil Games' games have a domain lock. That means that they will not work if you host game files on your server. Therby it is recommended to deactivate Game Download Feature when publishing these games.", MYARCADE_TEXT_DOMAIN); ?>
                  </p>
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Spil Games Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="spilgamesurl" value="<?php echo $spilgames['feed']; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if Spil Games Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="spilgameslimit" value="<?php echo $spilgames['limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be fetched at once. Enter 'all' (without quotes) if you want to fetch all games. Otherwise enter an integer.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Thumbnail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="spilgamesthumbsize" id="spilgamesthumbsize">
                    <option value="1" <?php myarcade_selected($spilgames['thumbsize'], '1'); ?> ><?php _e("Small (100x75)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="2" <?php myarcade_selected($spilgames['thumbsize'], '2'); ?> ><?php _e("Medium (120x90)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="3" <?php myarcade_selected($spilgames['thumbsize'], '3'); ?> ><?php _e("Large (200x120)", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the size of the thumbnails that should be used for games from Spil Games. Default size is small (100x75).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Language", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="spilgameslanguage" id="spilgameslanguage">
                    <option value="default" <?php myarcade_selected($spilgames['language'], 'default'); ?>>Default</option>
                    <option value="AR" <?php myarcade_selected($spilgames['language'], 'AR'); ?>>AR</option>
                    <option value="de-DE" <?php myarcade_selected($spilgames['language'], 'de-DE'); ?>>de-DE</option>
                    <option value="en-GB" <?php myarcade_selected($spilgames['language'], 'en-GB'); ?>>en-GB</option>
                    <option value="en-ID" <?php myarcade_selected($spilgames['language'], 'en-ID'); ?>>en-ID</option>
                    <option value="en-US" <?php myarcade_selected($spilgames['language'], 'en-US'); ?>>en-US</option>
                    <option value="es-ES" <?php myarcade_selected($spilgames['language'], 'es-ES'); ?>>es-ES</option>
                    <option value="fr-FR" <?php myarcade_selected($spilgames['language'], 'fr-FR'); ?>>fr-FR</option>
                    <option value="it-IT" <?php myarcade_selected($spilgames['language'], 'it-IT'); ?>>it-IT</option>
                    <option value="jp-JP" <?php myarcade_selected($spilgames['language'], 'jp-JP'); ?>>jp-JP</option>
                    <option value="ms-MY" <?php myarcade_selected($spilgames['language'], 'ms-MY'); ?>>ms-MY</option>
                    <option value="nl-NL" <?php myarcade_selected($spilgames['language'], 'nl-NL'); ?>>nl-NL</option>
                    <option value="pl-PL" <?php myarcade_selected($spilgames['language'], 'pl-PL'); ?>>pl-PL</option>
                    <option value="pt-BR" <?php myarcade_selected($spilgames['language'], 'pt-BR'); ?>>pt-BR</option>
                    <option value="pt-PT" <?php myarcade_selected($spilgames['language'], 'pt-PT'); ?>>pt-PT</option>
                    <option value="ru-RU" <?php myarcade_selected($spilgames['language'], 'ru-RU'); ?>>ru-RU</option>
                    <option value="sv-SE" <?php myarcade_selected($spilgames['language'], 'sv-SE'); ?>>sv-SE</option>
                    <option value="tr-TR" <?php myarcade_selected($spilgames['language'], 'tr-TR'); ?>>tr-TR</option>
                  </select>
                </td>
                <td><i><?php _e("Select a game language that you would like to fetch from Spil Games.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="spilgames_cron_fetch" value="true" <?php myarcade_checked($spilgames['cron_fetch'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch Spil Games games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="spilgames_cron_fetch_limit" value="<?php echo $spilgames['cron_fetch_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="spilgames_cron_publish" value="true" <?php myarcade_checked($spilgames['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Spil Games games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="spilgames_cron_publish_limit" value="<?php echo $spilgames['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // UnityFeeds Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("UnityFeeds Games", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("UnityFeeds provides a game feed with exclusive Unity3D games.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://gamefeeds.com/">here</a> to visit the UnityFeeds site.
                  </i>
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("UnityFeeds Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="unityfeeds_url" value="<?php echo $unityfeeds['feed']; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if UnityFeeds Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Category", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="unityfeeds_category" id="unityfeeds_category">
                    <option value="all" <?php myarcade_selected($unityfeeds['category'], 'all'); ?> ><?php _e("All Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="8" <?php myarcade_selected($unityfeeds['category'], '8'); ?> ><?php _e("Action Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="9" <?php myarcade_selected($unityfeeds['category'], '9'); ?> ><?php _e("Arcade Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="7" <?php myarcade_selected($unityfeeds['category'], '7'); ?> ><?php _e("Driving Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="11" <?php myarcade_selected($unityfeeds['category'], '11'); ?> ><?php _e("Flying Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="6" <?php myarcade_selected($unityfeeds['category'], '6'); ?> ><?php _e("Girls Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="10" <?php myarcade_selected($unityfeeds['category'], '10'); ?> ><?php _e("Puzzle Games", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select which games you would like to fetch.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Thumbnail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="unityfeeds_thumbnail" id="unityfeeds_thumbnail">
                    <option value="100x100" <?php myarcade_selected($unityfeeds['thumbnail'], '100x100'); ?> ><?php _e("100x100", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="120x90" <?php myarcade_selected($unityfeeds['thumbnail'], '120x90'); ?> ><?php _e("120x90", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="160x160" <?php myarcade_selected($unityfeeds['thumbnail'], '160x160'); ?> ><?php _e("160x160", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="180x135" <?php myarcade_selected($unityfeeds['thumbnail'], '180x135'); ?> ><?php _e("180x135", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="300x250" <?php myarcade_selected($unityfeeds['thumbnail'], '300x250'); ?> ><?php _e("300x250", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="300x300" <?php myarcade_selected($unityfeeds['thumbnail'], '300x300'); ?> ><?php _e("300x300", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a thumbnail size.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Thumbnail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="unityfeeds_screenshot" id="unityfeeds_screenshot">
                    <option value="100x100" <?php myarcade_selected($unityfeeds['screenshot'], '100x100'); ?> ><?php _e("100x100", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="120x90" <?php myarcade_selected($unityfeeds['screenshot'], '120x90'); ?> ><?php _e("120x90", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="160x160" <?php myarcade_selected($unityfeeds['screenshot'], '160x160'); ?> ><?php _e("160x160", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="180x135" <?php myarcade_selected($unityfeeds['screenshot'], '180x135'); ?> ><?php _e("180x135", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="300x250" <?php myarcade_selected($unityfeeds['screenshot'], '300x250'); ?> ><?php _e("300x250", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="300x300" <?php myarcade_selected($unityfeeds['screenshot'], '300x300'); ?> ><?php _e("300x300", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a screenshot size.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?><?php myarcade_premium_settings() ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="unityfeeds_cron_publish" value="true" <?php myarcade_checked($unityfeeds['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="unityfeeds_cron_publish_limit" value="<?php echo $unityfeeds['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

       <?php
        //----------------------------------------------------------------------
        // Translation Settings
        //----------------------------------------------------------------------
        ?>

        <?php include_once(MYARCADE_CORE_DIR.'/languages.php'); ?>

        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("Translation Settings", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("Translate games automatically to your language using the Microsoft Translator or Google Translate v2 (payed service). The translation is triggered when you click on 'Publish Games' or 'Publish'.", MYARCADE_TEXT_DOMAIN); ?>
                  </i>
                </td>
              </tr>

              <?php // Enable Translator ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Select Translation Service", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <select name="translation">
                    <option value="none" <?php myarcade_selected($general['translation'], 'none'); ?>><?php _e("Disable Translations", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="microsoft" <?php myarcade_selected($general['translation'], 'microsoft'); ?>><?php _e("Microsoft Translator", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="google" <?php myarcade_selected($general['translation'], 'google'); ?>><?php _e("Google Translator", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Check this if you want to enable the translator.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Fields to translate ?>
              <tr><td colspan="2"><h3><?php _e("Game Fields To Translate", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="translate_fields[]" value="name" <?php myarcade_checked_array($general['translate_fields'], 'name'); ?> />&nbsp;<?php _e("Name", MYARCADE_TEXT_DOMAIN); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="description" <?php myarcade_checked_array($general['translate_fields'], 'description'); ?> />&nbsp;<?php _e("Description", MYARCADE_TEXT_DOMAIN); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="instructions" <?php myarcade_checked_array($general['translate_fields'], 'instructions'); ?> />&nbsp;<?php _e("Instructions", MYARCADE_TEXT_DOMAIN); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="tags" <?php myarcade_checked_array($general['translate_fields'], 'tags'); ?> />&nbsp;<?php _e("Tags", MYARCADE_TEXT_DOMAIN); ?>
                </td>
                <td><i><?php _e("Select game fields that you want to translate.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Games to translate ?>
              <tr><td colspan="2"><h3><?php _e("Game Types To Translate", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <?php foreach ( $myarcade_distributors as $distr_slug => $distr_name) : ?>
                  <input type="checkbox" name="translate_games[]" value="<?php echo $distr_slug;?>" <?php myarcade_checked_array($general['translate_games'], $distr_slug); ?> />&nbsp;<?php echo $distr_name; ?><br />
                  <?php endforeach; ?>
                </td>
                <td><i><?php _e("Select game types you want to translate.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Microsoft Translator API ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Microsoft Translator Settings", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>
              <tr><td colspan="2"><i><?php _e("To be able to use Microsoft Translator you will need to register on Windows Azure Marketplace and sign up on the <a href='https://datamarket.azure.com' target='_blank'>Microsoft Translator</a>.", MYARCADE_TEXT_DOMAIN); ?></i></td></tr>

              <tr><td colspan="2"><h4><?php _e("Client ID", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <input type="text" size="40" name="bingid" value="<?php echo $general['bingid']; ?>" />
                </td>
                <td><i><?php _e("Enter your Windows Azure Marketplace Client ID.", MYARCADE_TEXT_DOMAIN);?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Client Secret Key", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <input type="text" size="40" name="bingsecret" value="<?php echo $general['bingsecret']; ?>" />
                </td>
                <td><i><?php _e("Enter your Windows Azure Marketplace Client Secret Key.", MYARCADE_TEXT_DOMAIN);?></i></td>
              </tr>

              <?php // Target Language ?>
              <tr><td colspan="2"><h4><?php _e("Target Language", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <?php
                  if (isset($languages_bing) ) {
                    ?><select size="1" name="translate_to" id="translate_to"><?php
                    foreach ($languages_bing as $code => $lang) {
                      ?><option value="<?php echo $code; ?>" <?php myarcade_selected($general['translate_to'], $code); ?>><?php echo $lang; ?></option><?php
                    }
                    ?></select><?php
                  }
                  else {
                    _e("ERROR: Can't find bing language file!", MYARCADE_TEXT_DOMAIN);
                  }
                  ?>
                </td>
                <td><i><?php _e("Select the target language.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>


              <?php // Google Translator API ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Google Translator Settings", MYARCADE_TEXT_DOMAIN); ?></h3>
                </td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("API Key", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40" name="google_id" value="<?php echo $general['google_id']; ?>" />
                </td>
                <td><i><?php _e('To be able to use Google Translation API v2 you will need to enter your API Key. Google Translator API is a payed service: <a href="#" target="_blank">Google Translate API</a>', MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <?php // Target Language ?>
              <tr><td colspan="2"><h4><?php _e("Target Language", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <?php
                  if (isset($languages_google) ) {
                    ?><select size="1" name="google_translate_to" id="google_translate_to"><?php
                    foreach ($languages_google as $code => $lang) {
                      ?><option value="<?php echo $code; ?>" <?php myarcade_selected($general['google_translate_to'], $code); ?>><?php echo $lang; ?></option><?php
                    }
                    ?></select><?php
                  }
                  else {
                    _e("ERROR: Can't find google language file!", MYARCADE_TEXT_DOMAIN);
                  }
                  ?>
                </td>
                <td><i><?php _e("Select the target language.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // Big Fish Games Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("Big Fish Games", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                   <?php _e("Big Fish Games offers an affiliate programm with 70% commisions for each sale you generate.", MYARCADE_TEXT_DOMAIN); ?>
                   <?php _e('Click <a href="https://affiliates.bigfishgames.com/" title="Big Fish Affiliates" target=_blank"">here</a> to sign up on Big Fish Games Affiliate program.', MYARCADE_TEXT_DOMAIN); ?>
                  </i>
                  <br /><br />
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Username", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="big_username" value="<?php echo $bigfish['username']; ?>" />
                </td>
                <td><i><?php _e("Enter your Big Fish Games user name.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Affiliate Code", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="big_affiliate_code" value="<?php echo $bigfish['affiliate_code']; ?>" />
                </td>
                <td><i><?php _e("Enter your Affiliate Code.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Default Game Type", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="big_gametype">
                    <option value="pc" <?php myarcade_selected($bigfish['gametype'], "pc"); ?>><?php _e("PC Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="mac" <?php myarcade_selected($bigfish['gametype'], "mac"); ?>><?php _e("Mac Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="og" <?php myarcade_selected($bigfish['gametype'], "og"); ?>><?php _e("Online Games", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the your preferred Game Type.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Language", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="big_locale">
                    <option value="en" <?php myarcade_selected($bigfish['locale'], "en"); ?>>English</li>
                    <option value="da" <?php myarcade_selected($bigfish['locale'], "da"); ?>>Dansk</li>
                    <option value="fr" <?php myarcade_selected($bigfish['locale'], "fr"); ?>>French</li>
                    <option value="de" <?php myarcade_selected($bigfish['locale'], "de"); ?>>German</li>
                    <option value="it" <?php myarcade_selected($bigfish['locale'], "it"); ?>>Italiano</li>
                    <option value="jp" <?php myarcade_selected($bigfish['locale'], "jp"); ?>>Japanese</li>
                    <option value="nl" <?php myarcade_selected($bigfish['locale'], "nl"); ?>>Nederlands</li>
                    <option value="pt" <?php myarcade_selected($bigfish['locale'], "pt"); ?>>Portugues</li>
                    <option value="es" <?php myarcade_selected($bigfish['locale'], "es"); ?>>Spanish</li>
                    <option value="sv" <?php myarcade_selected($bigfish['locale'], "sv"); ?>>Svenska</li>
                  </select>
                </td>
                <td><i><?php _e("Select the preferred language for Big Fish Games.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Game Thumbail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="big_thumbnail">
                    <option value="small" <?php myarcade_selected($bigfish['thumbnail'], "small"); ?>>Small (60x40)</li>
                    <option value="medium" <?php myarcade_selected($bigfish['thumbnail'], "medium"); ?>>Medium (80x80)</li>
                    <option value="feature" <?php myarcade_selected($bigfish['thumbnail'], "feature"); ?>>Feature Image (175x150)</li>
                    <option value="subfeature" <?php myarcade_selected($bigfish['thumbnail'], "subfeature"); ?>>Sub-feature Image (175x150)</li>
                  </select>
                </td>
                <td><i><?php _e("Select the preferred game thumbnail size. Default: Medium.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Game Description Template", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <textarea name="big_template" cols="40" rows="12"><?php echo $bigfish['template']; ?></textarea>
                </td>
                <td>
                  <p><i><?php _e("Set how Big Fish Games description should be generated.", MYARCADE_TEXT_DOMAIN); ?></i></p>
                  <br />
                  <strong><?php _e("Available Placeholders", MYARCADE_TEXT_DOMAIN); ?>:</strong><br />
                  %DESCRIPTION% - <?php _e("Game description", MYARCADE_TEXT_DOMAIN); ?><br />
                  %BULLET_POINTS% - <?php _e("Game key feature list", MYARCADE_TEXT_DOMAIN); ?><br />
                  %SYSREQUIREMENTS% - <?php _e("System requirements for PC and MAC games", MYARCADE_TEXT_DOMAIN); ?><br />
                  %BUY_URL% - <?php _e("Purchase game link", MYARCADE_TEXT_DOMAIN); ?>
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="big_cron_publish" value="true" <?php myarcade_checked($bigfish['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Big Fish Games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="big_cron_publish_limit" value="<?php echo $bigfish['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // Flashgamedistribution Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("FlashGameDistribution (FGD)", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("FlashGameDistribution has over 10.000 games that you can add to your site with ease.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://flashgamedistribution.com">here</a> to visit the FlashGameDistribution site.
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("FlashGameDistribution Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgdurl" value="<?php echo $fgd['feed']; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if FlashGameDistribution Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgdlimit" value="<?php echo $fgd['limit']; ?>" />
                </td>
                <td><i><?php _e("How many FlashGameDistribution games should be fetched at once.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fgd_cron_fetch" value="true" <?php myarcade_checked($fgd['cron_fetch'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch FGD games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgd_cron_fetch_limit" value="<?php echo $fgd['cron_fetch_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fgd_cron_publish" value="true" <?php myarcade_checked($fgd['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish FGD games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgd_cron_publish_limit" value="<?php echo $fgd['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // FreeGamesForYourWebsite Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("FreeGamesForYourWebsite (FOG)", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <p>
                    <i>
                      <?php _e("FreeGamesForYourWebsite provides a game feed with hand picked quality games from several sources.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://www.freegamesforyourwebsite.com">here</a> to visit the FreeGamesForYourWebsite site.
                    </i>
                  </p>
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("FreeGamesForYourWebsite Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="fogurl" value="<?php echo $fog['feed']; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if FreeGamesForYourWebsite Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="foglimit" value="<?php echo $fog['limit']; ?>" />
                </td>
                <td><i><?php _e("How many FreeGamesForYourWebsite games should be fetched at once. Enter 'all' (without quotes) if you want to fetch all games. Otherwise enter an integer.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Thumbnail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="fogthumbsize" id="fogthumbsize">
                    <option value="small" <?php myarcade_selected($fog['thumbsize'], 'small'); ?> ><?php _e("Small (100x100)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="medium" <?php myarcade_selected($fog['thumbsize'], 'medium'); ?> ><?php _e("Medium (180x135)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="large" <?php myarcade_selected($fog['thumbsize'], 'large'); ?> ><?php _e("Large (300x300)", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the size of the thumbnails that should be used for games from FreeGamesForYourWebsite. Default size is small (100x100).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Use Large Thumbnails as Screenshots", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fogscreen" value="true" <?php myarcade_checked($fog['screenshot'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to use large thumbnails (300x300px) from the feed as game screenshots", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Game Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="fogtag" id="fogtag">
                    <option value="all" <?php myarcade_selected($fog['tag'], 'all'); ?>>All Categories</option>
                    <option value="Shooting" <?php myarcade_selected($fog['tag'], 'Shooting'); ?>><?php _e('Shooting Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="Puzzle" <?php myarcade_selected($fog['tag'], 'Puzzle'); ?>><?php _e('Puzzle Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="Driving" <?php myarcade_selected($fog['tag'], 'Driving'); ?>><?php _e('Driving Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="Sports" <?php myarcade_selected($fog['tag'], 'Sports'); ?>><?php _e('Sports Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="Defense" <?php myarcade_selected($fog['tag'], 'Defense'); ?>><?php _e('Defense Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="Multiplayer" <?php myarcade_selected($fog['tag'], 'Multiplayer'); ?>><?php _e('Multiplayer Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="Adventure" <?php myarcade_selected($fog['tag'], 'Adventure'); ?>><?php _e('Adventure Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="Flying" <?php myarcade_selected($fog['tag'], 'Flying'); ?>><?php _e('Flying Games', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="3D" <?php myarcade_selected($fog['tag'], '3D'); ?>><?php _e('3D Games', MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a game category that you would like to fetch from FreeGamesForYourWebsite.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Language", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="foglanguage" id="foglanguage">
                    <option value="ar" <?php myarcade_selected($fog['language'], 'ar'); ?>><?php _e('Arabic', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="en" <?php myarcade_selected($fog['language'], 'en'); ?>><?php _e('English', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="fr" <?php myarcade_selected($fog['language'], 'fr'); ?>><?php _e('French', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="de" <?php myarcade_selected($fog['language'], 'de'); ?>><?php _e('German', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="el" <?php myarcade_selected($fog['language'], 'el'); ?>><?php _e('Greek', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="ro" <?php myarcade_selected($fog['language'], 'ro'); ?>><?php _e('Romanian', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="es" <?php myarcade_selected($fog['language'], 'es'); ?>><?php _e('Spanish', MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="ur" <?php myarcade_selected($fog['language'], 'ur'); ?>><?php _e('Urdu', MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a game language.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fog_cron_fetch" value="true" <?php myarcade_checked($fog['cron_fetch'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch FOG games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fog_cron_fetch_limit" value="<?php echo $fog['cron_fetch_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fog_cron_publish" value="true" <?php myarcade_checked($fog['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish FOG games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fog_cron_publish_limit" value="<?php echo $fog['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // GameFeed Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("GameFeed by TalkArcades", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php echo sprintf( __("You need a free account on TalkArcades to be able to use the GameFeed AutoPublisher. Click %shere%s to create a new account.", MYARCADE_TEXT_DOMAIN), '<a href="http://www.talkarcades.com" target="_blank">', '</a>'); ?>
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("GameFeed AutoPublisher", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="gamefeed_status" id="gamefeed_status">
                    <option value="publish" <?php myarcade_selected($gamefeed['status'], 'publish'); ?> ><?php _e("Publish", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="draft" <?php myarcade_selected($gamefeed['status'], 'draft'); ?> ><?php _e("Draft", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="add" <?php myarcade_selected($gamefeed['status'], 'add'); ?> ><?php _e("Add To Database (don't publish)", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a status for games added through AutoPublish from TalkArcades website.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="gamefeed_cron_publish" value="true" <?php myarcade_checked($gamefeed['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish GameFeed games automatically. Go to 'General Settings' to select a cron interval. This will only work if you have unpublished TalkArcades Games in your database.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="gamefeed_cron_publish_limit" value="<?php echo $gamefeed['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // Kongregrate Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("Kongregate", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("Kongegrate provides sponsored game XML feed.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://www.kongregate.com/games_for_your_site">here</a> to visit the Kongregrate site.
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Kongregate Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="kongurl" value="<?php echo $kongregate['feed']; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if Kongregate Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="kong_cron_publish" value="true" <?php myarcade_checked($kongregate['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Kongregate games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="kong_cron_publish_limit" value="<?php echo $kongregate['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

       <?php
        //----------------------------------------------------------------------
        // Scirra Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("Scirra", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("Scirra provides sponsored game XML feed.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://www.scirra.com/arcade/free-games-for-your-website" target="_blank">here</a> to visit the Scirra site.
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Scirra Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="scirra_url" value="<?php echo $scirra['feed']; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if Scirra Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Thumbail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="scirra_thumbnail">
                    <option value="small" <?php myarcade_selected($scirra['thumbnail'], "small"); ?>>Small (72x60)</li>
                    <option value="medium" <?php myarcade_selected($scirra['thumbnail'], "medium"); ?>>Medium (120x100)</li>
                    <option value="big" <?php myarcade_selected($scirra['thumbnail'], "big"); ?>>Big (280x233)</li>
                  </select>
                </td>
                <td><i><?php _e("Select the preferred game thumbnail size. Default: Medium.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="scirra_cron_publish" value="true" <?php myarcade_checked($scirra['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Scirra games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="scirra_cron_publish_limit" value="<?php echo $scirra['cron_publish_limit']; ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // Category Mapping
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><font color="yellow">- PRO -</font> <?php _e("Category Mapping", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message() ?>
            <table class="optiontable" width="100%">
              <tr>
                <td colspan="4">
                  <i>
                    <?php _e("Map default categories to your own category names. This feature allows you to publish games in translated or summarized categories instead of using the predefined category names. (optional)", MYARCADE_TEXT_DOMAIN); ?>
                  </i>
                  <br /><br />
                </td>
              </tr>
             <tr>
              <td width="20%"><a name="mapcats"></a><strong><?php _e("Feed Category", MYARCADE_TEXT_DOMAIN); ?></strong></td>
              <td width="20%"><strong><?php _e("Category", MYARCADE_TEXT_DOMAIN); ?></strong></td>
              <td width="20%"><strong><?php _e("Add Mapping", MYARCADE_TEXT_DOMAIN); ?></strong></td>
              <td><strong><?php _e("Current Mappings", MYARCADE_TEXT_DOMAIN); ?></strong></td>
             </tr>

              <script type="text/javascript">
                function myabp_add_map(slug) {
                  alert("Please consider upgrading to MyArcadePlugin Pro if you want to use this feature.");
                }
             </script>

            <?php
              foreach ($categories as $feedcat) :
            ?>
              <tr>
                <td><?php echo $feedcat['Name']; ?></td>
                <td>
                  <?php
                  $output  = '<select id="bcat_'.$feedcat['Slug'].'">';
                  $output .=  '<option value="0">---Select---</option>';
                  foreach ($categs_tmp as $cat_tmp_id => $cat_tmp_val) {
                    $output .= '<option value="'.$cat_tmp_id.'" />'.$cat_tmp_val.'</option>';
                  }
                  $output .= '</select>';
                  echo $output;
                  ?>
                </td>
                <td>
                  <div style="width:100px">
                  <div class="button-secondary" style="float:left;width:60px;text-align:center;" onclick="myabp_add_map('<?php echo $feedcat['Slug']; ?>');">
                    Add
                  </div>
                  <div style="float:right;" id="load_<?php echo $feedcat['Slug']; ?>"> </div>
                  </div>
                </td>
                <td>

                  <?php
                    if ( !empty($feedcat['Mapping']) ) {
                    ?><div class="tagchecklist" id="map_<?php echo $feedcat['Slug']; ?>"><?php
                      $map_cat_ids = explode(',', $feedcat['Mapping']);
                      foreach ($map_cat_ids as $map_cat_id) {
                    ?>


                        <span id="delmap_<?php echo $map_cat_id; ?>_<?php echo $feedcat['Slug']; ?>" class="remove_map">
                          <img style="float:left;top:4px;position:relative;" src="<?php echo MYARCADE_CORE_URL; ?>/images/remove.png" alt="UnMap" onclick="myabp_del_map('<?php echo $map_cat_id; ?>', '<?php echo $feedcat['Slug']; ?>');" />&nbsp;<?php echo get_cat_name($map_cat_id); ?>
                        </span>
                    <?php
                     }
                     ?></div><?php
                    }
                    else {
                      ?>
                      <div class="tagchecklist" id="map_<?php echo $feedcat['Slug']; ?>">
                      </div>
                      <?php
                    }
                  ?>
                </td>
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

        <?php
        //----------------------------------------------------------------------
        // Advanced Features
        //----------------------------------------------------------------------
        ?>
        <script type="text/javascript">
          /* <![CDATA[ */
          function confirmDeleteGames() {
            if ( confirm("Are you sure you want to delete all fetched games?") ) {
              jQuery('#del_response').html('<div class=\'gload\'> </div>');
              jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler',func:'delgames'},function(data){jQuery('#del_response').html(data);});
            }
          }
          function confirmDeleteScores() {
            if ( confirm("Are you sure you want to delete all scores?") ) {
              jQuery('#score_response').html('<div class=\'gload\'> </div>');
              jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler',func:'delscores'},function(data){jQuery('#score_response').html(data);});
            }
          }
          /* ]]> */
        </script>

        <h2 class="trigger"><?php _e("Advanced Features", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container" id="advanced_settings">
          <div class="block">
            <table class="optiontable" width="100%">
             <tr>
              <td colspan="3">
                <p class="mabp_error" style="padding:10px">
                  <?php _e("Please, use this only if you know what you do!", MYARCADE_TEXT_DOMAIN); ?>
                </p>
                <br />
              </td>
            </tr>

              <tr><td colspan="3"><h3><?php _e("Delete All Feeded Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td width="160">
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="return confirmDeleteGames();">
                    <?php _e("Reset Feeded Games", MYARCADE_TEXT_DOMAIN); ?>
                  </div>
                </td>
                <td width="30"><div id="del_response"></div></td>
                <td><i><?php _e("Attention! All feeded or imported games will be deleted from the games database! Published posts will not be touched. After this score submitting of publiished games will stop working!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Remove Games Marked as 'deleted'", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="jQuery('#remove_response').html('<div class=\'gload\'> </div>');jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler',func:'remgames'},function(data){jQuery('#remove_response').html(data);});">
                    <?php _e("Remove 'deleted' Games", MYARCADE_TEXT_DOMAIN); ?>
                  </div>
                </td>
                <td width="30"><div id="remove_response"></div></td>
                <td><i><?php _e("Attention! All games marked as 'deleted' will be removed from the database!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>


              <tr><td colspan="3"><h3><?php _e("Delete Blank / Zero Scores", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="jQuery('#zero_response').html('<div class=\'gload\'> </div>');jQuery.post('<?php echo admin_url('admin-ajax.php');  ?>', {action:'myarcade_handler',func:'zeroscores'},function(data){jQuery('#zero_response').html(data);});">
                    <?php _e("Delete Zero Scores", MYARCADE_TEXT_DOMAIN); ?>
                  </div>
                </td>
                <td width="30"><div id="zero_response"></div></td>
                <td><i><?php _e("Clean your scores table. This will delete all zero and empty scores if present in your database.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Delete All Scores", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="return confirmDeleteScores();">
                    <?php _e("Delete All Scores", MYARCADE_TEXT_DOMAIN); ?>
                  </div>
                </td>
                <td width="30"><div id="score_response"></div></td>
                <td><i><?php _e("Attention! All saved scores will be deleted!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Load Default Settings", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td colspan="3">
                  <p class="mabp_info" style="padding:10px"><?php _e("Attention! All setting will be reset!", MYARCADE_TEXT_DOMAIN); ?></p>
                  <form method="post" name="defaultsettings">
                    <input type="hidden" name="loaddefaults" id="loaddefaults" value="yes" />
                    <input type="checkbox" name="checkdefaults" id="checkdefaults" value="yes" /> Yes, I want to load default settings <input id="submitdefaults" type="submit" name="submitdefaults" class="button-secondary" value="<?php _e("Load Default Settings", MYARCADE_TEXT_DOMAIN); ?>" disabled />
                  </form>
                  <script type="text/javascript">
                    /* <![CDATA[ */
                    jQuery("#checkdefaults").click(function() {;
                      var checked_status = this.checked;
                      if (checked_status === true) {
                        jQuery("#submitdefaults").removeAttr("disabled");
                      } else {
                        jQuery("#submitdefaults").attr("disabled", "disabled");
                      }
                    });
                    /* ]]> */
                  </script>
                  <br />
                </td>
              </tr>

            </table>
          </div>
        </div>

      <div style="clear:both"></div>
    </div><?php // end id myarcade_settings ?>

   <div class="clear"></div>
  <?php

  myarcade_footer();
}