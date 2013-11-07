<?php
/**
 * Manage Games Module
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2013, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Manage
 */

defined('MYARCADE_VERSION') or die();

/* Add some Manage Games Ajax Handler */
add_action('wp_ajax_myarcade_handler', 'myarcade_handler');

/**
 * Shows a game with relevant information and insertes needed buttons
 *
 * @param <type> $game
 */
function myarcade_show_game($game) {

  $contest = '';

  if ($game->leaderboard_enabled) $leader = 'enabled'; else $leader = 'disabled';
  if ($game->coins_enabled) $coins = 'enabled'; else $coins = 'disabled';

  $play_url = MYARCADE_CORE_URL.'/playgame.php?gameid='.$game->id;

  // Buttons
  $publish     = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."',{action:'myarcade_handler',gameid:'$game->id',func:'publish'},function(data){jQuery('#gstatus_$game->id').html(data);});\">".__("Publish", MYARCADE_TEXT_DOMAIN)."</button>&nbsp;";
  $delete      = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."',{action:'myarcade_handler',gameid:'$game->id',func:'delete'},function(data){jQuery('#gstatus_$game->id').html(data);});\">".__("Delete", MYARCADE_TEXT_DOMAIN)."</button>&nbsp;";
  $delgame     = "<div class=\"myhelp\"><img style=\"cursor: pointer;border:none;padding:0;\" src='".MYARCADE_CORE_URL."/images/delete.png' alt=\"Remove game from the database\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."',{action:'myarcade_handler',gameid:'$game->id',func:'remove'},function(){jQuery('#gamebox_$game->id').fadeOut('slow');});\" />
                <span class=\"myinfo\">".__("Remove this game from the database", MYARCADE_TEXT_DOMAIN)."</span></div>
 ";

  // Chek game dimensions
  if ( empty($game->height) ) $game->height = '600';
  if ( empty($game->width)  ) $game->width = '480';

  $edit ='<a href="#" onclick="alert(\'If you want to edit games please consider updating to MyArcadePlugin Pro\');return false;" class="button-secondary edit" title="'.__("Edit", MYARCADE_TEXT_DOMAIN).'">'.__("Edit", MYARCADE_TEXT_DOMAIN).'</a>&nbsp;';

  if ($game->status == 'published') {
    $edit_post = '<a href="post.php?post='.$game->postid.'&action=edit" class="button-secondary" target="_blank">Edit Post</a>&nbsp;';

    // contest button
    if ($game->leaderboard_enabled) {
    $contest = '<a class="button" href="post-new.php?post_type=contest&gameid='.$game->postid.'">'.__( 'New Contest', MYARCADE_TEXT_DOMAIN).'</a>';
    }
  } else {
   $edit_post = '';
  }

  // Generate content for the game box
  if ($game->status == 'published') {
    $name = get_the_title($game->postid);
    $thumb_url = get_post_meta($game->postid, 'mabp_thumbnail_url', true);
    $game_post = get_post($game->postid);
    $description = strip_tags($game_post->post_content);
    $description = substr( mysql_real_escape_string($description), 0, 320)."..";

    $categs = wp_get_post_categories($game->postid);
    $categories = false;
    if ( $categs ) {
      $count = count($categs);
      for ($i=0; $i<$count; $i++) {
        $c = get_category($categs[$i]);
        $categories .= $c->name;
        if ($i < ($count - 1) ) {
         $categories .= ', ';
        }
      }
    }

    if ($categories) {
      $categories = '<div style="margin-top:6px;"><strong>Categories:</strong> '.$categories."</div>";
    }
  } else {

    $game_categs = false;

    if ( isset($game->categs) ) {
      $game_categs = $game->categs;
    }
    elseif ( isset($game->categories) ) {
      $game_categs = $game->categories;
    }


    if ( is_array($game_categs) ) {
      $categories = '';
      $count = count($game_categs);
      for ($i=0; $i<$count; $i++) {
        $categories .= $game_categs[$i];
        if ($i < ($count - 1) ) {
         $categories .= ', ';
        }
      }
    } else {
      $categories = $game_categs;
    }

    $name      = $game->name;
    $thumb_url = $game->thumbnail_url;
    $description = str_replace(array("\r", "\r\n", "\n"), '', $game->description);
    $description = substr( mysql_real_escape_string($description), 0, 280)."..";
    if ( isset($categories) )
      $categories = '<div style="margin-top:6px;"><strong>Categories:</strong> '.$categories."</div>";
    else
      $categories = '';
  }

  ?>
    <div class="show_game" id="gamebox_<?php echo $game->id;?>">
      <div class="block">
        <table class="optiontable" width="100%">
          <tr valign="top">
            <td width="110" align="center">
              <img src="<?php echo $thumb_url; ?>" width="100" height="100" alt="" />
              <div class="g-features">
                <span class="lb_<?php echo $leader; ?>" title="Leaderboards <?php echo ucfirst($leader); ?>"></span>
                <span class="poi_<?php echo $coins; ?>" title="Coins <?php echo ucfirst($coins); ?>"></span>
              </div>
            </td>
            <td colspan="2">
              <table>
                <tr valign="top">
                  <td width="520">
                    <strong><div id="gname_<?php echo $game->id;?>"><?php echo $name; ?></div></strong>
                  </td>
                  <td>
                    <?php
                    if ( isset($game->game_type) ) {
                      $type = $game->game_type;
                    } elseif ( isset($game->type) ) {
                      $type = $game->type;
                    } else {
                      $type = '';
                    }

                    echo ucfirst($type);
                    ?>
                  </td>
                </tr>
                <tr>
                  <td>
                    <?php echo $description; ?>
                    <br />
                    <?php echo $categories; ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center">
              <p style="margin-top:3px"><a class="thickbox button-primary" title="<?php echo $name; ?>" href="<?php echo $play_url; ?>&keepThis=true&TB_iframe=true&height=<?php echo $game->height; ?>&width=<?php echo $game->width; ?>"><?php _e("Play", MYARCADE_TEXT_DOMAIN)?></a></p>
            </td>
            <td>
              <?php echo $delgame; ?>

              <?php
                switch ($game->status) {
                  case 'ignored':
                  case 'new':         echo $delete; echo $edit; echo $publish;       break;
                  case 'published':   echo $delete; echo $edit_post; echo $contest; break;
                  case 'deleted':     echo $edit; echo $publish;                    break;
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
 * Shows the "Manage Games" panel
 *
 * @global <type> $wpdb
 */
function myarcade_manage_games() {
  global $wpdb, $myarcade_distributors, $pagenow;

  myarcade_header();

  ?>
  <div id="icon-options-general" class="icon32"><br /></div>
  <h2><?php _e("Manage Games", MYARCADE_TEXT_DOMAIN); ?></h2>
  <br />
  <script type="text/javascript">
    function checkSeachForm() {
      if ( document.searchForm.q.value == "") {
        alert("<?php _e("Search term was not entered!", MYARCADE_TEXT_DOMAIN); ?>");
        document.searchForm.q.focus();
        return false;
      }
    }
  </script>
  <?php

  $feedcategories = get_option('myarcade_categories');

  // Init needed vars
  $action = $results = $keyword = '';

  $game_type    = isset($_POST['distr']) ? $_POST['distr'] : 'all';
  $leaderboard  = isset($_POST['leaderboard']) ? $_POST['leaderboard'] : false;
  $coins        = isset($_POST['coins']) ? $_POST['coins'] : false;
  $status       = isset($_POST['status']) ? $_POST['status'] : 'all';
  $search       = empty($_POST['q']) ? false : $_POST['q'];
  $order        = isset($_POST['order']) ? $_POST['order'] : 'ASC';
  $orderby      = isset($_POST['orderby']) ? $_POST['orderby'] : 'id';
  $cat          = isset($_POST['category']) ? $_POST['category'] : 'all';
  $games        = isset($_POST['games']) ? $_POST['games'] : '50';
  $offset       = isset($_POST['offset']) ? $_POST['offset'] : '0';

  if ( isset($_POST['action']) ) {
    $action = $_POST['action'];
  }

  if ( ($action == 'search') /*&& $search*/) {

    $keyword = mysql_real_escape_string($search);

    $query_array = array();

    if ($search) $query_array[] = "(name LIKE '%".$search."%' OR description LIKE '%".$search."%')";
    if ( $game_type != 'all' ) $query_array[] = "game_type = '".$game_type."'";
    if ( $leaderboard ) $query_array[] = "leaderboard_enabled = '1'";
    if ( $coins ) $query_array[] = "coins_enabled = '1'";
    if ( $status != 'all' ) $query_array[] = "status = '".$status."'";
    if ( $cat != 'all' ) {
      foreach ($feedcategories as $category) {
        if ($category['Slug'] == $cat) {
          $query_array[] = "categories LIKE '%".$category['Name']."%'";
          break;
        }
      }
    }

    $count = count($query_array);
    $query_string = '';

    if ( $count > 1) {
      for($i=0; $i < $count; $i++) {
        $query_string .= $query_array[$i];
        if ( $i < ($count - 1) ) {
          $query_string .= ' AND ';
        }
      }
    } else {
      $query_string = $query_array[0];
    }

    if ( !empty($query_string) ) $query_string = " WHERE ".$query_string;

    // Generate the query
    $query = "SELECT * FROM ".MYARCADE_GAME_TABLE.$query_string." ORDER BY ".$orderby." ".$order." limit ".$offset.",".$games;

    $query_count = $wpdb->get_var("SELECT COUNT(*) FROM ".MYARCADE_GAME_TABLE.$query_string);

    $results = $wpdb->get_results($query);

    if (!$results) {
      echo '<div class="mabp_error" style="width:685px">'.__("Nothing found!", MYARCADE_TEXT_DOMAIN).'</strong></div>';
    }
  }

  // Search form
  ?>
  <form method="post" action="" class="myarcade_form" name="searchForm">
    <input type="hidden" name="action" value="search" />
    <div class="myarcade_border grey" style="width:680px">
      <label><?php _e("Search for", MYARCADE_TEXT_DOMAIN); ?></label>
      <input type="text" size="40" name="q" value="<?php echo $keyword; ?>" />

      <p class="myarcade_hr">&nbsp;</p>

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

      <div class="myarcade_border white" style="width:300px;float:left;margin-left:20px;height:25px;padding-top: 15px">
        <label><?php _e('Leaderboard Enabled', MYARCADE_TEXT_DOMAIN); ?>: </label>
        <input type="checkbox" name="leaderboard" value="1" <?php myarcade_checked($leaderboard, '1'); ?> />&nbsp;&nbsp;&nbsp;
        <label><?php _e('Coins Enabled', MYARCADE_TEXT_DOMAIN); ?>: </label>
        <input type="checkbox" name="coins" value="1" <?php myarcade_checked($coins, '1'); ?> />
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <label for="status"><?php _e("Game Status", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="status" id="status">
          <option value="all" <?php myarcade_selected($status, 'all'); ?>>All</option>
          <option value="new" <?php myarcade_selected($status, 'new'); ?>>New</option>
          <option value="published" <?php myarcade_selected($status, 'published'); ?>>Published</option>
          <option value="deleted" <?php myarcade_selected($status, 'deleted'); ?>>Deleted</option>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <label><?php _e("Order", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="order" id="order">
          <option value="ASC" <?php myarcade_selected($order, 'ASC'); ?>>ASC</option>
          <option value="DESC" <?php myarcade_selected($order, 'DESC'); ?>>DESC</option>
        </select>
        <label><?php _e("by", MYARCADE_TEXT_DOMAIN);?>: </label>
        <select name="orderby" id="orderby">
          <option value="id" <?php myarcade_selected($orderby, 'id'); ?>>ID</option>
          <option value="name" <?php myarcade_selected($orderby, 'name'); ?>>Name</option>
          <option value="slug" <?php myarcade_selected($orderby, 'slug'); ?>>Slug</option>
          <option value="game_type" <?php myarcade_selected($orderby, 'game_type'); ?>>Game Type</option>
          <option value="status" <?php myarcade_selected($orderby, 'status'); ?>>Status</option>
        </select>
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <label for="category"><?php _e("Game Category", MYARCADE_TEXT_DOMAIN); ?>: </label>
        <select name="category" id="category">
          <option value="all" <?php myarcade_selected($cat, 'all'); ?>>All</option>
          <?php
            foreach ( $feedcategories as $category) {
              ?><option value="<?php echo $category['Slug']; ?>" <?php myarcade_selected($cat, $category['Slug']); ?>><?php echo $category['Name']; ?></option><?php
            }
          ?>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <label><?php _e("Display", MYARCADE_TEXT_DOMAIN); ?></label>
        <input type="text" size="5" name="games" value="<?php echo $games; ?>" />
        <label><?php _e("games from offset", MYARCADE_TEXT_DOMAIN); ?></label>
        <input type="text" size="5" name="offset" value="<?php echo $offset; ?>" />
      </div>

      <div class="clear"> </div>

      <input class="button-primary" id="submit" type="submit" name="submit" value="Search" />
    </div>
  </form>
 <?php

  if ($results) {
    echo '<div class="mabp_info" style="width:685px">'.sprintf(__("Results found: <strong>%s</strong>. Displaying results from <strong>%s</strong> to <strong>%s</strong>.", MYARCADE_TEXT_DOMAIN), $query_count, $offset, $games + $offset).'</div>';

    foreach ($results as $game) {
      myarcade_show_game($game);
    }
  }
  else {
      /* Begin Pagination */
      $count = $wpdb->get_var("SELECT COUNT(*) FROM ".MYARCADE_GAME_TABLE);

      if ( $count ) {

        // This is the number of results displayed per page
        $page_rows = 50;

        // This tells us the page number of our last page
        $last = ceil($count / $page_rows);

        // This makes sure the page number isn't below one, or more than our maximum pages
        $pagenum = 1;
        if ( isset($_GET['pagenum']) ) {
          $pagenum = $_GET['pagenum'];
        }

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
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=1'>First</a>
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo $previous; ?>'>Previous</a>
              <?php endif; ?>
              <span class='page-numbers current'><?php echo $pagenum; ?></span>
              <?php if ($pagenum != $last) : ?>
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo $next; ?>'>Next</a>
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo $last; ?>'>Last</a>
              <?php endif; ?>
            </div>
          </div>
        <?php
        foreach ($results as $game) {
          myarcade_show_game($game);
        }
   ?>
          <!-- Print pagination -->
          <div class="tablenav" style="float: left;">
            <div class="tablenav-pages">
              <span class="displaying-num">Displaying <?php echo $from_to; ?> of <?php echo $count; ?></span>
            <?php if ($pagenum > 1) : ?>
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=1'>First</a>
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo $previous; ?>'>Previous</a>
              <?php endif; ?>
              <span class='page-numbers current'><?php echo $pagenum; ?></span>
              <?php if ($pagenum != $last) : ?>
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo $next; ?>'>Next</a>
                <a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo $last; ?>'>Last</a>
              <?php endif; ?>
            </div>
          </div>
          <div style="clear:both;"></div>
        <?php

      }
    }
    else {
      _e("No games found", MYARCADE_TEXT_DOMAIN);
    }

    $results = $wpdb->get_results("SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE status = 'deleted' ORDER BY created DESC limit 10");

    if ($results) {
      echo '<h3>'.__("10 Last Deleted Games", MYARCADE_TEXT_DOMAIN).'</h3>';
      foreach ($results as $game) {
        myarcade_show_game($game);
      }
      ?><div style="clear:both;"></div><?php
    }
  }

  ?>
  <script type="text/javascript">
    function thickboxResize() {

      var boundHeight = 800; // minimum height
      //var boundWidth = 750; // minimum width

      //var viewportWidth = (self.innerWidth || (document.documentElement.clientWidth || (document.body.clientWidth || 0)))
      var viewportHeight =(self.innerHeight || (document.documentElement.clientHeight || (document.body.clientHeight || 0)))

      jQuery('a.thickbox').each(function(){
        var text = jQuery(this).attr("href");

        if ( viewportHeight < boundHeight  /*|| viewportHeight < boundWidth*/)
        {
          // adjust the height
          text = text.replace(/height=[0-9]*/,'height=' + Math.round(viewportHeight * .8));
          // adjust the width
          //text = text.replace(/width=[0-9]*/,'width=' + Math.round(viewportWidth * .8));
        }
        else
        {
          // constrain the height by defined bounds
          text = text.replace(/height=[0-9]*/,'height=' + boundHeight);
          // constrain the width by defined bounds
          //text = text.replace(/width=[0-9]*/,'width=' + boundWidth);
        }

        jQuery(this).attr("href", text);
      });
    }

    jQuery(window).bind('load', thickboxResize );
    jQuery(window).bind('resize', thickboxResize );
  </script>
  <?php

  myarcade_footer();
}

/**
 * Manage Scores Panel
 */
function myarcade_manage_scores() {
  global $wpdb;

  myarcade_header();
    ?>
    <h2><?php _e("Manage Scores", MYARCADE_TEXT_DOMAIN); ?></h2>
    <br />
    <table class="widefat fixed">
      <thead>
      <tr>
        <th scope="col" width="100">Image</th>
        <th scope="col">Game</th>
        <th scope="col">User</th>
        <th scope="col">Date</th>
        <th scope="col">Score</th>
        <th scope="col">Action</th>
      </tr>
      </thead>
      <tbody>
        <tr id="scorerow_">
          <td colspan="6">
            <div class="mabp_info">
              This is a premium feature. Please upgrade to MyArcadePlugin Pro if you want to collect and manage user scores!
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <?php
  myarcade_footer();
}


/**
 * Ajax handler the show game buttons
 *
 * @global  $wpdb
 */
function myarcade_handler() {
  global $wpdb;

  // Check if the current user has permissions to do that...
  if ( current_user_can('manage_options') == false ) {
    wp_die('You do not have permissions access this site!');
  }

  if ( isset( $_POST['gameid']) ) {
    $gameID = $_POST['gameid'];
  }

  switch ($_POST['func']) {
    /* Manage Games */
    case "publish":
    {
      if ( !isset($gameID) || empty($gameID) ) { echo "No Game ID!"; die(); }

      // Publish this game
      myarcade_add_games_to_blog( array('game_id' => $gameID, 'echo' => false) );

      // Get game status
      $status = $wpdb->get_var("SELECT status FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'");
      echo $status;
    }
    break;

    case "delete":
    {
      if ( !isset($gameID) || empty($gameID) ) { echo "No Game ID!"; die(); }

      // Check if game is published
      $game = $wpdb->get_row("SELECT postid, name FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'", ARRAY_A);
      $postid = $game['postid'];

      if (!$postid)  {
        // Alternative check for older versions of MyArcadePlugin
        $name = $game['name'];
        $postid = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '$name'");
      }

      if ($postid) {
        myarcade_delete_game($postid);
        // Delete wordpress post
        wp_delete_post($postid);
      }
      //else {
        // Can't find game post...

        // Update game status
        $query = "UPDATE ".MYARCADE_GAME_TABLE." SET status = 'deleted', postid = '' WHERE id = $gameID";
        $wpdb->query($query);
      //}

      // Get game status
      $status = $wpdb->get_var("SELECT status FROM ".MYARCADE_GAME_TABLE." WHERE id = '$gameID'");
      echo $status;
    }
    break;

    case "remove":
      {
        if ( !isset($gameID) || empty($gameID) ) { echo "No Game ID!"; die(); }

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

    case "remgames":
    {
      $wpdb->query("DELETE FROM ".MYARCADE_GAME_TABLE." WHERE status = 'deleted'");
      ?>
      <script type="text/javascript">
        alert('Games marked as "deleted" where removed from the database!');
      </script>
      <?php
    }
    break;

    case "dircheck": {
      if ( isset( $_POST['directory'] ) ) {
        if ( $_POST['directory'] == 'games' ) {
          if (!is_writable(ABSPATH.MYARCADE_GAMES_DIR)) {
            echo '<p class="mabp_error mabp_680">'.sprintf(__("The games directory '%s' must be writeable (chmod 777) in order to download games.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_GAMES_DIR).'</p>';
          }
        } else {
          if (!is_writable(ABSPATH.MYARCADE_THUMBS_DIR)) {
            echo '<p class="mabp_error mabp_680">'.sprintf(__("The thumbs directory '%s' must be writeable (chmod 777) in order to download thumbnails or screenshots.", MYARCADE_TEXT_DOMAIN), ABSPATH.MYARCADE_THUMBS_DIR).'</p>';
          }
        }
      }
    } break;
  }

  die();
}