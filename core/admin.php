<?php
/*
Module:       This modul contains MyArcadePlugin admin panel functions
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/

defined('MYARCADE_VERSION') or die();


/**
* Display MyArcadePlugin header on admin pages
* 
*/
function myarcade_header($echo = true) {
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
  if ( !$echo) return;
  echo '<div class="wrap">';
  ?>
  <p style="margin-top: 10px">
    <img src="<?php echo MYARCADE_CORE_URL . '/images/logo.png'; ?>" alt="MyArcadePlugin Lite" />
  </p>
  <?php
}


/**
* Display MyArcadePlugin footer on admin pages
* 
*/
function myarcade_footer($echo = true) {
  if (!$echo) return;
  ?>
  <div class="clear"></div>
  <p class="mabp_info" style="padding:5px;width:790px"><?php echo MYARCADE_LOCKED_IMG; ?> 
  MyArcadePlugin Lite is a fully functional but limited version of our <a href='http://myarcadeplugin.com' title='MyArcadePlugin Pro'>MyArcadePlugin Pro</a> plugin. Upgrade to enable all the premium features, to get support and a lot of bonuses at our support forum.</p>
  </div>
  <?php
}

function myarcade_admin_menu() {

  add_menu_page('MyArcade', 'MyArcade', 'edit_posts' , __FILE__, 'myarcade_show_stats', MYARCADE_CORE_URL . '/images/arcade.png');
  add_submenu_page(__FILE__, __('Dashboard', MYARCADE_TEXT_DOMAIN), __('Dashboard', MYARCADE_TEXT_DOMAIN), 'edit_posts', __FILE__, 'myarcade_show_stats');
             
  add_submenu_page( __FILE__,
                    __("Fetch Games", MYARCADE_TEXT_DOMAIN),
                    __("Fetch Games", MYARCADE_TEXT_DOMAIN),
                    'manage_options', 'arcadelite-feed-games', 'myarcade_feed_games');
                    
  add_submenu_page( __FILE__,
                    __("Publish Games", MYARCADE_TEXT_DOMAIN),
                    __("Publish Games", MYARCADE_TEXT_DOMAIN),
                    'manage_options', 'arcadelite-add-games-to-blog',  'myarcade_add_games_to_blog');
                    
  $hookname = add_submenu_page( __FILE__,
                    __("Import Games", MYARCADE_TEXT_DOMAIN),
                    __("Import Games", MYARCADE_TEXT_DOMAIN),
                    'edit_posts', 'arcadelite-import-games', 'myarcade_import_games');
  
  add_action('load-'.$hookname, 'myarcade_import_scripts');

    
  add_submenu_page( __FILE__,
                    __("Manage Games", MYARCADE_TEXT_DOMAIN),
                    __("Manage Games", MYARCADE_TEXT_DOMAIN),
                    'manage_options', 'arcadelite-manage-games', 'myarcade_manage_games');
   
                    
  add_submenu_page( __FILE__,
                    __("Settings"),
                    __("Settings"),
                    'manage_options', 'arcadelite-edit-settings', 'myarcade_edit_settings');
}

function myarcade_load_ajax() {
  // jQuery
  wp_enqueue_script('jquery');

  // Thickbox
  wp_enqueue_script('thickbox');
  $thickcss = get_option('siteurl')."/".WPINC."/js/thickbox/thickbox.css";
  wp_enqueue_style('thickbox_css', $thickcss, false, false, 'screen');

  // Add MyArcade CSS
  $css = MYARCADE_CORE_URL."/myarcadeplugin.css";
  wp_enqueue_style('myarcade_css', $css, false, false, 'screen');
}

function myarcade_import_scripts() {
  wp_enqueue_script('myarcade_ajax_forms', 
      MYARCADE_JS_URL.'/jquery.form.js', 
      '', 
      '',
      false);
}

/**
 * @brief Shows the overview page in WordPress backend
 */
function myarcade_show_stats() {

  myarcade_header();
  
  ?>
  <div id="icon-index" class="icon32"><br /></div>
  <h2><?php _e("Dashboard"); ?></h2>
  <?php  
          
  ?>
  
    <div class="dash-left metabox-holder">
      <div class="postbox">
        <div class="newsico"></div>
          <h3 class="hndle" id="poststuff"><span><?php _e('Lastest MyArcadePlugin News', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
            <?php
               wp_widget_rss_output('http://myarcadeplugin.com/feed', array('items' => 10, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 0));
            ?>
            </div> <!-- inside end -->
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
          <h3 class="hndle" id="poststuff"><span><?php _e('Lastest exells.com News', MYARCADE_TEXT_DOMAIN) ?></span></h3>
          <div class="preloader-container">
            <div class="insider" id="boxy">
            <?php
               wp_widget_rss_output('http://exells.com/feed', array('items' => 5, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 0));
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
     <strong>MyArcadePlugin Pro v<?php echo MYARCADE_VERSION;?></strong> | <strong><a href="http://myarcadeplugin.com/" target="_blank">MyArcadePlugin.com</a> </strong>
    
  <?php 
  myarcade_footer();
}


/**
 * Helper function for selects
 */
function myarcade_selected( $selected, $current) {
  if ( $selected === $current) {
    echo ' selected';
  }
}


/**
 * Helper function for checkboxes
 */
function myarcade_checked( $var, $value) {
  if ( $var === $value) {
    echo ' checked';
  }
}

function myarcade_checked_array($var, $value) {
  if ( is_array($var) ) {
    foreach ($var as $element) {
      if ( $element === $value) {
        echo ' checked';
        break;
      }
    }
  } 
}



/**
 * @brief Shows the settings page and handels all setting changes 
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

    $general = array();
    if ( isset($_POST['leaderboardenable'])) $general['scores'] = true; else $general['scores'] = false;
    if ( isset($_POST['onlyhighscores'])) $general['highscores'] = true; else $general['highscores'] = false;
    if ( isset($_POST['game_count'])) $general['posts'] = trim($_POST['game_count']); else $general['posts'] = '';
    if ( isset($_POST['publishstatus'])) $general['status'] = $_POST['publishstatus']; else $general['status'] = 'publish';
    if ( isset($_POST['schedtime'])) $general['schedule'] = trim($_POST['schedtime']); else $general['schedule'] = '';
    if ( isset($_POST['downloadthumbs'])) $general['down_thumbs'] = true; else $general['down_thumbs'] = false;
    if ( isset($_POST['downloadgames'])) $general['down_games'] = true; else $general['down_games'] = false;
    $general['down_screens'] = false;
    if ( isset($_POST['deletefiles'])) $general['delete'] = true; else $general['delete'] = false;    
    
    if ( isset($_POST['createcats'])) $general['create_cats'] = true; else $general['create_cats'] = false;
    if ( isset($_POST['parentcatid'])) $general['parent'] = $_POST['parentcatid']; else $general['parent'] = '';
    if ( isset($_POST['firstcat'])) $general['firstcat'] = true; else $general['firstcat'] = false;
    if ( isset($_POST['maxwidth'])) $general['max_width'] = trim($_POST['maxwidth']); else $general['max_width'] = '';
    if ( isset($_POST['singlecat'])) $general['single'] = true; else $general['single'] = false;
    if ( isset($_POST['singlecatid'])) $general['singlecat'] = $_POST['singlecatid']; else $general['singlecat'] = '';
    if ( isset($_POST['embedflashcode'])) $general['embed'] = $_POST['embedflashcode']; else $general['embed'] = 'manually';
    if ( isset($_POST['usetemplate'])) $general['use_template'] = true; else $general['use_template'] = false;
    if ( isset($_POST['post_template'])) $general['template'] = stripslashes($_POST['post_template']); else $general['template'] = '';
    if ( isset($_POST['featured_image'])) $general['featured_image'] = true; else $general['featured_image'] = false;    
    
    $general['play_delay'] = isset($_POST['play_delay']) ? $_POST['play_delay'] : '30';
    
    // Update Settings
    update_option('myarcade_general', $general);

    // Mochi
    $mochi = array();
    if ( isset($_POST['mochiurl'])) $mochi['feed'] = trim($_POST['mochiurl']); else $mochi['feed'] = '';
    //if ( isset($_POST['feed_save'])) $mochi['feed_save'] = trim($_POST['feed_save']); else $mochi['feed_save'] = '';
    $mochi['feed_save'] = '';
    
    //$mochi['default_feed'] = $_POST['default_feed'];
    $mochi['default_feed'] = 'old';
    
    if ( isset($_POST['mochiid'])) $mochi['publisher_id'] = trim($_POST['mochiid']); else $mochi['publisher_id'] = '';
    if ( isset($_POST['mochiskey'])) $mochi['secret_key'] = trim($_POST['mochiskey']); else $mochi['secret_key'] = '';
    if ( isset($_POST['feed_count'])) $mochi['limit'] = intval(trim($_POST['feed_count'])); else $mochi['limit'] = '';   
    if ( isset($_POST['feedcat'])) $mochi['special'] = trim($_POST['feedcat']); else $mochi['special'] = '';
    
    if ( isset($_POST['tag'])) $mochi['tag'] = trim($_POST['tag']); else $mochi['tag'] = '';
    if ( isset($_POST['mochi_status'])) $mochi['status'] = $_POST['mochi_status']; else $mochi['status'] = 'publish';
      // Update Settings
      update_option('myarcade_mochi', $mochi); 
      
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
    
  // Get settings
  $general    = get_option('myarcade_general');
  $mochi      = get_option('myarcade_mochi');
  $categories = get_option('myarcade_categories');
  
  //print_r($general);
  //print_r($mochi);
    
  if ( $general['down_games'] ) {
    if ( !file_exists(ABSPATH.MYARCADE_GAMES_DIR) ) {
      @mkdir(ABSPATH.MYARCADE_GAMES_DIR, 0777);
    }

    if (!is_writable(ABSPATH.MYARCADE_GAMES_DIR)) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The games directory '%s' must be writable (chmod 777) in order to download games.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_GAMES_DIR).'</p>';
    }
  }

  if ( $general['down_thumbs'] ) {
    if ( !file_exists(ABSPATH.MYARCADE_THUMBS_DIR) ) {
      @mkdir(ABSPATH.MYARCADE_THUMBS_DIR, 0777);
    }
    
    if (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR)) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The thumbails directory '%s' must be writable (chmod 777) in order to download thumbnails.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
    }
  }
  
  // Check ID
  if ( !empty($mochi['special']) && empty($mochi['publisher_id']) ) {
    echo '<p class="mabp_error mabp_800">'.__("You have selected a special category but not entered your Mochi Publisher ID!", MYARCADE_TEXT_DOMAIN).'</p>';
  } 
  
  // Get all categories
  $categs_ids_tmp = get_all_category_ids();
  $categs_tmp = array();
  
  foreach ($categs_ids_tmp as $categ_id_tmp) {
    $categs_tmp[$categ_id_tmp] = get_cat_name($categ_id_tmp);
  }
  ?>
  <br />
   <div id="myarcade_settings">
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

              <tr><td colspan="2"><h3><?php _e("Delete Game Files", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="deletefiles" value="true" <?php myarcade_checked($general['delete'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("This option will delete the associated game files from your server after deleting the post from your blog. Warning - deleted games cannot be re-published! For this to work properly, the games and thumbs directories must be writable.", MYARCADE_TEXT_DOMAIN); ?></i></td>
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
                    %INSTRUCTIONS% - <?php _e("Show game instructions if available", MYARCADE_TEXT_DOMAIN); ?>
                    %TAGS% - <?php _e("Show all game tags", MYARCADE_TEXT_DOMAIN); ?><br />
                    %THUMB% - <?php _e("Show the game thumbnail", MYARCADE_TEXT_DOMAIN); ?><br />
                    %THUMB_URL% - <?php _e("Show game thumbnail URL", MYARCADE_TEXT_DOMAIN); ?><br />
                    %SWF_URL% - <?php _e("Show game SWF URL / Embed Code", MYARCADE_TEXT_DOMAIN); ?><br />
                    %WIDTH% - <?php _e("Show game width", MYARCADE_TEXT_DOMAIN); ?><br />
                    %HEIGHT% - <?php _e("Show game height", MYARCADE_TEXT_DOMAIN); ?><br />
                  </i></td>
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
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>
        
        <?php
        //----------------------------------------------------------------------
        // Mochi Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("Mochi Media", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e("A Mochi account is required to utilize the Mochi Media features.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="https://www.mochimedia.com/r/23f4b6b9ad680165" title="Register On Mochi">here</a> to create a new account.
                  </i>
                  <br /><br />
                </td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Mochi Media Feeds", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr><td colspan="2"><h4><?php _e("Regular Feed URL", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="mochiurl" value="<?php echo $mochi['feed']; ?>" />
                </td>
                <td><i><?php _e("Edit this field only if Mochi Media Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <!--
              <tr><td colspan="2"><h4><?php _e("Game Catalog 2.0 Feed URL", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="feed_save" value="<?php echo $mochi['feed_save']; ?>" />
                </td>
                <td><i><?php _e("Optionally, you can enter a Mochi feed url (JSON FORMAT) for Game Catalog 2.0. Create this URL from the Mochi's website.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Default Feed", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <select name="default_feed">
                    <option value="old" <?php myarcade_selected($mochi['default_feed'], 'old'); ?>><?php _e("Regular Feed", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="new" <?php myarcade_selected($mochi['default_feed'], 'new'); ?>><?php _e("Game Catelog 2.0 Feed", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select which feed URL do you want to use.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>                            
              -->

              <tr><td colspan="2"><h3><?php _e("Mochi Publisher ID", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="mochiid" value="<?php echo $mochi['publisher_id']; ?>" />
                </td>
                <td><i><?php _e("Paste your Mochi 'Publisher ID' here; the 'Publisher ID' may be found under 'Settings' on the Mochi site.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Publisher Secret Key", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="mochiskey" value="<?php echo $mochi['secret_key'];  ?>" />
                </td>
                <td><i><?php _e("Paste your Mochi Publisher Secret Key here. This is required if the leaderboard feature is to be used. You can find your Secret Key under 'Settings' on the Mochi site.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="feed_count" value="<?php echo $mochi['limit']; ?>" />
                </td>
                <td><i><?php _e("How many Mochi games should be fetched when clicking 'Fetch Games'? Leave blank if you want to fetch all games (not recommended). This option only affects the manual game fetching. It is recommended to use values between 100 and 2000 to avoid server overloads.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Filter by Tag", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="tag" value="<?php echo $mochi['tag']; ?>" />
                </td>
                <td><i><?php _e("You may choose to include games that include or exclude a tag. To exclude a tag, prefix it with a minus(-) sign. For example '-zh-cn' will exclude all Chinese games or 'snow' will include only games tagged with snow. Add tags without quotes ('').", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Special Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="feedcat" id="feedcat">
                    <option value="" <?php myarcade_selected($mochi['special'], ''); ?> ><?php _e("All Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="premium_games" <?php myarcade_selected($mochi['special'], 'premium_games'); ?> ><?php _e("Premium Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="coins_enabled" <?php myarcade_selected($mochi['special'], 'coins_enabled'); ?> ><?php _e("Coin Enabled Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="featured_games" <?php myarcade_selected($mochi['special'], 'featured_games'); ?> ><?php _e("Featured Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="leaderboard_enabled" <?php myarcade_selected($mochi['special'], 'leaderboard_enabled'); ?> ><?php _e("Leaderboard Games", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a special category if you want to fetch only featured, coin enabled, leaderboard or premium games. To use this feature a valid Publisher ID needs to be entered.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Mochi Auto Post", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="mochi_status" id="mochi_status">
                    <option value="publish" <?php myarcade_selected($mochi['status'], 'publish'); ?> ><?php _e("Publish", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="draft" <?php myarcade_selected($mochi['status'], 'draft'); ?> ><?php _e("Draft", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="add" <?php myarcade_selected($mochi['status'], 'add'); ?> ><?php _e("Add To Database (don't publish)", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a status for games added through 'Post game to my site' link from Mochi's website.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><div class="mabp_info"><p>Automated game fetching and publishing are available on the premium version.</p></div></td></tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="cron_fetch" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch Mochi Media games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="cron_fetch_limit" value="1" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Mochi Media games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="cron_publish_limit" value="1" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>                

        <?php
        //----------------------------------------------------------------------
        // Big Fish Games Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("Big Fish Games<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
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
                  <input type="text" size="40"  name="big_username" value="" />
                </td>
                <td><i><?php _e("Enter your Big Fish Games user name.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Affiliate Code", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="big_affiliate_code" value="" />
                </td>
                <td><i><?php _e("Enter your Affiliate Code.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Default Game Type", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="big_gametype">
                    <option value="pc"><?php _e("PC Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="mac"><?php _e("Mac Games", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="og"><?php _e("Online Games", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the your preferred Game Type.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Language", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="big_locale">
                    <option value="en">English</li>
                    <option value="da">Dansk</li>
                    <option value="fr">French</li>
                    <option value="de">German</li>
                    <option value="it">Italiano</li>
                    <option value="jp">Japanese</li>
                    <option value="nl">Nederlands</li>
                    <option value="pt">Portugues</li>
                    <option value="es">Spanish</li>
                    <option value="sv">Svenska</li>
                  </select>
                </td>
                <td><i><?php _e("Select the preferred language for Big Fish Games.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Game Thumbail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="big_thumbnail">
                    <option value="small">Small (60x40)</li>
                    <option value="medium">Medium (80x80)</li>
                    <option value="feature">Feature Image (175x150)</li>
                    <option value="subfeature">Sub-feature Image (175x150)</li>
                  </select>
                </td>
                <td><i><?php _e("Select the preferred game thumbnail size. Default: Medium.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Game Description Template", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <textarea name="big_template" cols="40" rows="12"></textarea>
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
                  <input type="checkbox" name="big_cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Big Fish Games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="big_cron_publish_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>
        
        
        <?php
        //----------------------------------------------------------------------
        // Flashgamedistribution Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("FlashGameDistribution (FGD)<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
                  <i>
                    <?php _e("FlashGameDistribution has over 10.000 games that you can add to your site with ease.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://flashgamedistribution.com">here</a> to visit the FlashGameDistribution site.
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("FlashGameDistribution Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgdurl" value="" />
                </td>
                <td><i><?php _e("Edit this field only if FlashGameDistribution Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgdlimit" value="" />
                </td>
                <td><i><?php _e("How many FlashGameDistribution games should be fetched at once.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fgd_cron_fetch" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch FGD games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgd_cron_fetch_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="fgd_cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish FGD games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fgd_cron_publish_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>  
        
        
        <?php
        //----------------------------------------------------------------------
        // FreeGamesForYourWebsite Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("FreeGamesForYourWebsite (FOG)<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
                  <i>
                    <?php _e("FreeGamesForYourWebsite provides a game feed with hand picked quality games from several sources.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://www.freegamesforyourwebsite.com">here</a> to visit the FreeGamesForYourWebsite site.
                  </i>
                  <br /><br />
                  <p class="mabp_info" style="padding:10px">
                    <?php _e("The feed from FreeGamesForYourWebsite contains over 200 tags / categories. Thereby it is not possible to sort all the games to default arcade categories that are defined at 'General Settings'. It is recommended to adjust game cagegories manually before a game gets published.", MYARCADE_TEXT_DOMAIN); ?>
                  </p>                  
                </td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("FreeGamesForYourWebsite Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="fogurl" value="" />
                </td>
                <td><i><?php _e("Edit this field only if FreeGamesForYourWebsite Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="foglimit" value="" />
                </td>
                <td><i><?php _e("How many FreeGamesForYourWebsite games should be fetched at once. Enter 'all' (without quotes) if you want to fetch all games. Otherwise enter an integer.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Thumbnail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="fogthumbsize" id="fogthumbsize">
                    <option value="small"><?php _e("Small (100x100)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="medium"><?php _e("Medium (180x135)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="large"><?php _e("Large (300x300)", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the size of the thumbnails that should be used for games from FreeGamesForYourWebsite. Default size is small (100x100).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Use Large Thumbnails as Screenshots", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fogscreen" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to use large thumbnails from the feed as game screenshots", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Game Tags / Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <?php
                  include_once(MYARCADE_CORE_DIR.'/fog_tags.php');
                  if (isset($fog_tags) ) {
                    ?><select size="1" name="fogtag" id="fogtag"><?php
                    foreach ($fog_tags as $tag) {
                      ?><option value="<?php echo $tag['slug']; ?>"><?php echo $tag['name']; ?></option><?php 
                    }
                    ?></select><?php
                  }
                  else {
                    _e("ERROR: Can't find the game tag file!", MYARCADE_TEXT_DOMAIN);
                  }
                  ?>                
                </td>
                <td><i><?php _e("Select a game category that you would like to fetch from FreeGamesForYourWebsite.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Create Categories", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fogcategory" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to create for each 'Game Tag' a category. <strong>ATTENTION:</strong> This will create over 200 categories! Activate this option only if you know what are you doing!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="fog_cron_fetch" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch FOG games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fog_cron_fetch_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="fog_cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish FOG games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="fog_cron_publish_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>
        
        
        <?php
        //----------------------------------------------------------------------
        // Kongregrate Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("Kongregate<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
                  <i>
                    <?php _e("Kongegrate provides sponsored game XML feed.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://www.kongregate.com/games_for_your_site">here</a> to visit the Kongregrate site.
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Kongregate Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="kongurl" value="" />
                </td>
                <td><i><?php _e("Edit this field only if Kongregate Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="kong_cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Kongregate games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="kong_cron_publish_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>                
        
        <?php
        //----------------------------------------------------------------------
        // MyArcadeFeed Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("MyArcadeFeed<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
                  <i>
                    <?php _e("Add up to five Feeds generated with MyArcadeFeed Plugin.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://exells.com/shop/products/myarcadefeed">here</a> to learn more about MyArcadeFeed.
                  </i>
                  <br /><br />
                </td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 1", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed1" value="" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 1 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 2", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed2" value="" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 2 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 3", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed3" value="" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 3 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 4", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed4" value="" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 4 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("MyArcadeFeed URL 5", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="myarcadefeed5" value="" />
                </td>
                <td><i><?php _e("Paste your MyArcadeFeed URL No. 5 here.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>        

        
        <?php
        //----------------------------------------------------------------------
        // Playtomic Settings
        //----------------------------------------------------------------------
        ?>    
        <h2 class="trigger"><?php _e("Playtomic<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
                  <i>
                    <?php _e("Playtomic offers you a lot of high quality games for your site.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://playtomic.com" title="Playtomic Website">here</a> to visit the Playtomic site.
                  </i>
                  <br /><br />
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Playtomic Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="tomicurl" value="" />
                </td>
                <td><i><?php _e("Edit this field only if Playtomic Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Languages", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <table border="0" cellpadding="2" cellspacing="5">
                    <tr>
                      <td><input type="checkbox" name="tomiclang[]" value="1" /><label class="opt">&nbsp;English</label></td>
                      <td><input type="checkbox" name="tomiclang[]" value="2" /><label class="opt">&nbsp;Spanish</label></td>
                    </tr>
                    <tr>                      
                      <td><input type="checkbox" name="tomiclang[]" value="3" /><label class="opt">&nbsp;Dutch</label></td>
                      <td><input type="checkbox" name="tomiclang[]" value="4" /><label class="opt">&nbsp;German</label></td>
                    </tr>
                    <tr>                      
                      <td><input type="checkbox" name="tomiclang[]" value="5" /><label class="opt">&nbsp;French</label></td>
                      <td><input type="checkbox" name="tomiclang[]" value="6" /><label class="opt">&nbsp;Italian</label></td>
                    </tr>
                    <tr>                      
                      <td><input type="checkbox" name="tomiclang[]" value="7" /><label class="opt">&nbsp;Polish</label></td>
                      <td><input type="checkbox" name="tomiclang[]" value="8" /><label class="opt">&nbsp;Swedish</label></td>
                    </tr>
                    <tr>                      
                      <td><input type="checkbox" name="tomiclang[]" value="9" /><label class="opt">&nbsp;Portuguese</label></td>                      
                      <td><input type="checkbox" name="tomiclang[]" value="10" /><label class="opt">&nbsp;Russian</label></td>
                    </tr>
                    <tr>                      
                      <td><input type="checkbox" name="tomiclang[]" value="11" /><label class="opt">&nbsp;Chinese</label></td>
                      <td><input type="checkbox" name="tomiclang[]" value="12" /><label class="opt">&nbsp;Japanese</label></td>
                    </tr>
                  </table>
                  </div>
                </td>
                <td><i><?php _e("Select the languages for the games to be fetched from Playtonic.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Audience", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="tomicaudience[]" value="0" /><label class="opt">&nbsp;Everyone</label>
                  <input type="checkbox" name="tomicaudience[]" value="1" /><label class="opt">&nbsp;Teens</label>
                  <input type="checkbox" name="tomicaudience[]" value="2" /><label class="opt">&nbsp;Mature</label>
                </td>
                <td><i><?php _e("To limit the games fetched from Playtonic to a particular audience rating, select the intended audience for your site.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e('Advertisement', MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="tomicads" id="tomicads">
                    <option value="99"><?php _e("Any", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="0"><?php _e("No Ads", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="1"><?php _e("Mochi Ads", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="2"><?php _e("CPMStar Ads", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the kind of adverstisement the fetched games should have. If you want games without ads then select 'No Ads'.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Leaderboards", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="tomicleader" id="tomicleader">
                    <option value="99"><?php _e("Any", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="0"><?php _e("None", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="1"><?php _e("Playtomic", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="2"><?php _e("Gamersafe", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="3"><?php _e("Mochi Media", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the leaderboard type. (Mochi Media is recommended.)", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e('Microtransactions', MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="tomicmtx" id="tomicmtx">
                    <option value="99"><?php _e("Any", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="0"><?php _e("None", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="1"><?php _e("Gamersafe", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="2"><?php _e("MochiCoins", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the kind of microtransactions.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              

              <tr><td colspan="2"><h3><?php _e("Minimum Rating", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="tomicrating" value="" />
                </td>
                <td><i><?php _e("Playtomic calculates an engagement rating for all games which is a score out of 100. This shows how fun the game is according to actual player data. A rating of 90 or more is an excellent game, but a 90 will also result in fewer games from Playtonic listed. 70 or more is a great game. 50 or more is a good game. Below 50 is generally a poor game.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="tomiclimit" value="" />
                </td>
                <td><i><?php _e("How many Playtomic games should be fetched at once. It is recommended to use values between 100 and 2000 to avoid server overload.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="tomic_cron_fetch" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch Playtomic games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="tomic_cron_fetch_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="tomic_cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Playtomic games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="tomic_cron_publish_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              

            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div> 


       <?php
        //----------------------------------------------------------------------
        // Scirra Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("Scirra<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
                  <i>
                    <?php _e("Scirra provides sponsored game XML feed.", MYARCADE_TEXT_DOMAIN); ?> Click <a href="http://www.scirra.com/arcade/free-games-for-your-website" target="_blank">here</a> to visit the Scirra site.
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Scirra Feed URL", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="scirra_url" value="" />
                </td>
                <td><i><?php _e("Edit this field only if Scirra Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              <tr><td colspan="2"><h3><?php _e("Thumbail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <select name="scirra_thumbnail">
                    <option value="small">Small (72x60)</li>
                    <option value="medium">Medium (120x100)</li>
                    <option value="big">Big (280x233)</li>
                  </select>
                </td>
                <td><i><?php _e("Select the preferred game thumbnail size. Default: Medium.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="scirra_cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Scirra games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="scirra_cron_publish_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>
        
        
        <?php
        //----------------------------------------------------------------------
        // Spil Games Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("Spil Games<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
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
                  <input type="text" size="40"  name="spilgamesurl" value="" />
                </td>
                <td><i><?php _e("Edit this field only if Spil Games Feed URL has been changed!", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="text" size="40"  name="spilgameslimit" value="" />
                </td>
                <td><i><?php _e("How many games should be fetched at once. Enter 'all' (without quotes) if you want to fetch all games. Otherwise enter an integer.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Thumbnail Size", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="spilgamesthumbsize" id="spilgamesthumbsize">
                    <option value="1"><?php _e("Small (100x75)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="2"><?php _e("Medium (120x90)", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="3"><?php _e("Large (200x120)", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select the size of the thumbnails that should be used for games from Spil Games. Default size is small (100x75).", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Language", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="spilgameslanguage" id="spilgameslanguage">
                    <option value="default">Default</option>
                    <option value="AR">AR</option>
                    <option value="de-DE">de-DE</option>
                    <option value="en-GB">en-GB</option>
                    <option value="en-ID">en-ID</option>                    
                    <option value="en-US">en-US</option>
                    <option value="es-ES">es-ES</option>
                    <option value="fr-FR">fr-FR</option>
                    <option value="it-IT">it-IT</option>
                    <option value="jp-JP">jp-JP</option>
                    <option value="ms-MY">ms-MY</option>
                    <option value="nl-NL">nl-NL</option>
                    <option value="pl-PL">pl-PL</option>
                    <option value="pt-BR">pt-BR</option>
                    <option value="pt-PT">pt-PT</option>
                    <option value="ru-RU">ru-RU</option>
                    <option value="sv-SE">sv-SE</option>
                    <option value="tr-TR">tr-TR</option>
                  </select>
                </td>
                <td><i><?php _e("Select a game language that you would like to fetch from Spil Games.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="spilgames_cron_fetch" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to fetch Spil Games games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h4><?php _e("Fetch Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="spilgames_cron_fetch_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be fetched on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              
              <tr>
                <td>
                  <input type="checkbox" name="spilgames_cron_publish" value="true" /><label class="opt">&nbsp;<?php _e("Yes", MYARCADE_TEXT_DOMAIN); ?></label>
                </td>
                <td><i><?php _e("Enable this if you want to publish Spil Games games automatically. Go to 'General Settings' to select a cron interval.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
              <tr><td colspan="2"><h4><?php _e("Publish Games", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="text" size="40"  name="spilgames_cron_publish_limit" value="" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger?", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
              
            </table>
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>        
       

               <?php
        //----------------------------------------------------------------------
        // Translation Settings
        //----------------------------------------------------------------------
        ?>
        
        <?php include_once(MYARCADE_CORE_DIR.'/languages.php'); ?>
        
        <h2 class="trigger"><?php _e("Translation Settings<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
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
                    <option value="none"><?php _e("Disable Translations", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="microsoft"><?php _e("Microsoft Translator", MYARCADE_TEXT_DOMAIN); ?></option>
                    <option value="google"><?php _e("Google Translator", MYARCADE_TEXT_DOMAIN); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Check this if you want to enable the translator.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>
              
              <?php // Fields to translate ?>
              <tr><td colspan="2"><h3><?php _e("Game Fields To Translate", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="translate_fields[]" value="name" />&nbsp;<?php _e("Name", MYARCADE_TEXT_DOMAIN); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="description" />&nbsp;<?php _e("Description", MYARCADE_TEXT_DOMAIN); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="instructions" />&nbsp;<?php _e("Instructions", MYARCADE_TEXT_DOMAIN); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="tags" />&nbsp;<?php _e("Tags", MYARCADE_TEXT_DOMAIN); ?>
                </td>
                <td><i><?php _e("Select game fields that you want to translate.", MYARCADE_TEXT_DOMAIN); ?></i></td>
              </tr>              
             
              <?php // Games to translate ?>
              <tr><td colspan="2"><h3><?php _e("Game Types To Translate", MYARCADE_TEXT_DOMAIN); ?></h3></td></tr>
              <tr>
                <td>
                  <?php foreach ( $myarcade_distributors as $distr_slug => $distr_name) : ?>
                  <input type="checkbox" name="translate_games[]" value="<?php echo $distr_slug;?>" />&nbsp;<?php echo $distr_name; ?><br />
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
                  <input type="text" size="40" name="bingid" value="" />
                </td>
                <td><i><?php _e("Enter your Windows Azure Marketplace Client ID.", MYARCADE_TEXT_DOMAIN);?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Client Secret Key", MYARCADE_TEXT_DOMAIN); ?></h4></td></tr>
              <tr>
                <td>
                  <input type="text" size="40" name="bingsecret" value="" />
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
                      ?><option value="<?php echo $code; ?>"><?php echo $lang; ?></option><?php 
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
                  <input type="text" size="40" name="google_id" value="" />
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
                      ?><option value="<?php echo $code; ?>"><?php echo $lang; ?></option><?php 
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
            <input id="submit" type="submit" name="submit" value="<?php _e("Save Settings", MYARCADE_TEXT_DOMAIN); ?>" />
          </div>
        </div>
        
          
        <?php
        //----------------------------------------------------------------------
        // Category Mapping
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("Category Mapping<font color=\"yellow\">*</font>", MYARCADE_TEXT_DOMAIN); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%">
              <tr>
                <td colspan="4">
                  <div class="mabp_info"><p>This feature is only available on the premium version.</p></div>
                  <br />
                  
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
                  var selection = jQuery('#bcat_'+slug).val();
                  jQuery('#load_'+slug).html('<div class=\'gload\'> </div>');
                  jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action:'myarcade_handler',
                    func:'addmap',
                    feedcat:slug,
                    mapcat:selection
                  },

                    function(data) {
                      jQuery('#map_'+slug).append(data);
                      jQuery('#load_'+slug).html('');
                    }
                  );
                }

                function myabp_del_map(mapid, slug) {
                  jQuery('#load_'+slug).html('<div class=\'gload\'> </div>');
                  jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action:'myarcade_handler',
                    func:'delmap',
                    feedcat:slug,
                    mapcat:mapid
                  },

                    function(data){
                      jQuery('#delmap_'+mapid+'_'+slug).fadeOut('slow',
                        function() {
                          jQuery('#delmap_'+mapid+'_'+slug).remove();
                        }
                      );
                      jQuery('#load_'+slug).html('');
                    }
                  );
                }
             </script>

            <?php
              //$blog_category_ids = get_all_category_ids();
              //$feedcategories = unserialize($myarcade_settings->game_categories);

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
                  <div class="button-secondary" style="float:left;width:60px;text-align:center;" onclick="myabp_add_map('<?php echo $feedcat['Slug']; ?>')">
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
                          <img style="float:left;top:4px;position:relative;" src="<?php echo MYARCADE_CORE_URL; ?>/images/remove.png" alt="UnMap" onclick="myabp_del_map('<?php echo $map_cat_id; ?>', '<?php echo $feedcat['Slug']; ?>')" />&nbsp;<?php echo get_cat_name($map_cat_id); ?>
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
                      if (checked_status == true) {
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
?>