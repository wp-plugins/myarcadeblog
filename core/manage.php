<?php

/**
 * @brief Manages games from the admin pannel
 */
function myarcade_manage_games() {
  global $wpdb;
    
  myarcade_header();
  
  echo "<h3>".__("Manage Games", MYARCADE_TEXT_DOMAIN)."</h3>";
  
  if ( isset($_POST['action']) ) $action = $_POST['action']; else $action = '';
  
  $keyword = '';
  
  if ($action == 'search') {
    $keyword = mysql_escape_string($_POST['q']);
    
    switch ($keyword) {
      case 'new':
      case 'published':
      case 'deleted':
        {
          $query = "SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE
             status LIKE '$keyword' ORDER BY name ASC";
        }
        break;
        
      default:
        {
          $query = "SELECT * FROM ".MYARCADE_GAME_TABLE." WHERE
             name LIKE '%$keyword%'
             OR description LIKE '%$keyword%'
             OR game_type LIKE '%$keyword%'
             ORDER BY name ASC";
        }
        break;          
    }
  
    $results = $wpdb->get_results($query);
   
    if (!$results) {
      echo '<div class="mabp_error">'.sprintf(__("Nothing found for %s", MYARCADE_TEXT_DOMAIN), $keyword).'</strong></div>';
    }
  }

  // Search form
  ?>
  <form method="post">
    <input type="hidden" name="action" value="search" />
    <table>
      <tr>
        <td><div class="help"><span class="info"><?php _e("Search for a game name, keyword or status (new, published, deleted)...", MYARCADE_TEXT_DOMAIN); ?></span></div></td>
      </tr>
      <tr>
        <td><input type="text" name="q" value="<?php echo $keyword; ?>"  size="40" /> <input class="button-primary" type="submit" id="submit" value="<?php _e("Search", MYARCADE_TEXT_DOMAIN); ?>" /></td>
      <tr>
    </table>
  </form>
  <div class="clear"></div>
  
 <?php
  
  if ( isset($results) ) {
    echo '<h3>'.sprintf(__("Search results for %s", MYARCADE_TEXT_DOMAIN), $keyword).'"</h3>';
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
      if (isset($_GET['pagenum']) ) $pagenum = $_GET['pagenum']; else $pagenum = 1;

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
    } 
    else {
      $results = false;
    }

    if ($results) {
      echo '<h3>'.__("Browser Your Game Catalog", MYARCADE_TEXT_DOMAIN).'</h3>';
      ?>
        <!-- Print pagination -->
        <div class="tablenav" style="float: left;">
        	<div class="tablenav-pages">
        		<span class="displaying-num">Displaying <?php echo $from_to; ?> of <?php echo $count; ?></span>
    			<?php if ($pagenum > 1) : ?>
	          	<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=1'>First</a>
        			<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $previous; ?>'>Previous</a>
        		<?php endif; ?>
          	<span class='page-numbers current'><?php echo $pagenum; ?></span>
          	<?php if ($pagenum != $last) : ?>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $next; ?>'>Next</a>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $last; ?>'>Last</a>
          	<?php endif; ?>
        	</div>
        </div>
        <div class="clear"></div>
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
	          	<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=1'>First</a>
        			<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $previous; ?>'>Previous</a>
        		<?php endif; ?>
          	<span class='page-numbers current'><?php echo $pagenum; ?></span>
          	<?php if ($pagenum != $last) : ?>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $next; ?>'>Next</a>
          		<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=arcadelite-manage-games&pagenum=<?php echo $last; ?>'>Last</a>
          	<?php endif; ?>
        	</div>
        </div>
        <div style="clear:both;"></div>
      <?php
    } else {
      ?>
      <div class="mabp_800 mabp_info">
        <p>No Games Found!</p>
      </div>
      <?php
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
    
  myarcade_footer();
}


/**
 *  @brief Ajax handler for managing games
 */
function myarcade_handler() {
  global $wpdb;
   
  // Check if the current user has permissions to do that...
  if ( current_user_can('manage_options') == false ) {
    wp_die('You do not have permissions access this site!');
  }
    
  $gameID = ( isset($_POST['gameid']) ) ? $_POST['gameid'] : false;
    
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
                
      // Update game status
      $query = "UPDATE ".MYARCADE_GAME_TABLE." SET status = 'deleted', postid = '' WHERE id = $gameID";
      $wpdb->query($query);
     
      
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
  }
  
  die();
}


/**
 * @brief Shows a game with relevant informations
 */
function myarcade_show_game($game) {

 if ($game->leaderboard_enabled)
  $leader = '<div class="myhelp"><img src="'.MYARCADE_CORE_URL.'/images/trophy.png" alt="'.__("Leaderboard enabled", MYARCADE_TEXT_DOMAIN).'">
             <span class="myinfo">'.__("Leaderboard enabled", MYARCADE_TEXT_DOMAIN).'</span></div>';
 else $leader = '';
 
 if ($game->coins_enabled)
  $coins = '<div class="myhelp"><img src="'.MYARCADE_CORE_URL.'/images/coins.png" alt="'.__("Coin revenue share enabled", MYARCADE_TEXT_DOMAIN).'">
            <span class="myinfo">'.__("Coin revenue share enabled", MYARCADE_TEXT_DOMAIN).'</span></div>';
 else $coins = ''; 
 
 $play_url = MYARCADE_URL.'/modules/playgame.php?gameid='.$game->id;
 
 // Buttons
 $publish     = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."/admin-ajax.php',{action:'myarcade_handler',gameid:'$game->id',func:'publish'},function(data){jQuery('#gstatus_$game->id').html(data);});\">".__("Publish", MYARCADE_TEXT_DOMAIN)."</button>&nbsp;";
 $delete      = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."/admin-ajax.php',{action:'myarcade_handler',gameid:'$game->id',func:'delete'},function(data){jQuery('#gstatus_$game->id').html(data);});\">".__("Delete", MYARCADE_TEXT_DOMAIN)."</button>&nbsp;";
 $delgame     = "<div class=\"myhelp\"><img style=\"cursor: pointer;\" src='".MYARCADE_CORE_URL."/images/delete.png' alt=\"Remove game from the database\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('".admin_url('admin-ajax.php')."/admin-ajax.php',{action:'myarcade_handler',gameid:'$game->id',func:'remove'},function(){jQuery('#gamebox_$game->id').fadeOut('slow');});\" />
                <span class=\"myinfo\">".__("Remove this game from the database", MYARCADE_TEXT_DOMAIN)."</span></div> 
 ";

 // Chek game dimensions
 if ( empty($game->height) ) $game->height = '800';
 if ( empty($game->width)  ) $game->width = '600';
 
 $edit ='<a href="#" class="button-secondary" title="'.__("Edit", MYARCADE_TEXT_DOMAIN).'" onclick="alert(\'This is a MyArcadePlugin Pro feature!\');return false;">'.__("Edit", MYARCADE_TEXT_DOMAIN).'</a>&nbsp;';

?>
 <div class="show_game" id="gamebox_<?php echo $game->id;?>">
      <div class="block">
        <table class="optiontable" width="100%">
          <tr valign="top">
            <td width="110" align="center">
              <img style="border:1px solid #d6d6d6;padding:5px;" src="<?php echo $game->thumbnail_url; ?>" width="100" height="100" alt="" />
            </td>
            <td colspan="2">
              <table>
                <tr valign="top">
                  <td width="520">
                    <strong>
                      <div id="gname_<?php echo $game->id;?>">
                        <?php 
                          if (strlen($game->name) > 25) {
                            echo substr($game->name, 0, 23).'..';
                          }
                          else {
                            echo $game->name; 
                          }                        
                        ?>
                      </div>
                    </strong>
                  </td>
                  <td><?php echo $leader; echo $coins; ?></td>
                </tr>
                <tr>
                  <td><?php echo substr(mysql_escape_string($game->description), 0, 300)."..";  ?></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center">
              <p style="margin-top:3px;"><a class="thickbox button-primary" title="<?php echo $game->name; ?>" href="<?php echo $play_url; ?>&keepThis=true&TB_iframe=true&height=<?php echo $game->height; ?>&width=<?php echo $game->width; ?>"><?php _e("Play", MYARCADE_TEXT_DOMAIN)?></a></p>
            </td>
            <td>
              <?php echo $delgame; ?>
              
              <?php
                switch ($game->status) {
                  case 'ignored':
                  case 'new':         echo $delete; echo $edit; echo $publish;       break;
                  case 'published':   echo $delete;               break;
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
?>