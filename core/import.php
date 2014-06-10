<?php
/**
 * Module:       This modul contains MyArcadePlugin import games functions
 * Author:       Daniel Bakovic
 * Author URI:   http://myarcadeplugin.com
 */

defined('MYARCADE_VERSION') or die();

/**
 * Shows the import form and handles imported games
 *
 * @global <type> $wpdb
 */
function myarcade_import_games() {

  myarcade_header();

  // Crete an empty game class
  $game = new stdClass();

  if ( isset($_POST['impcostgame']) && ($_POST['impcostgame'] == 'import') ) {
    if ( $_POST['importtype'] == 'embed' || $_POST['importtype'] == 'iframe' ) {
      $game->swf_url = esc_sql(urldecode($_POST['importgame']));
    }
    else {
      $game->swf_url = $_POST['importgame'];
    }

    $game->width = !empty($_POST['gamewidth']) ? $_POST['gamewidth'] : '';
    $game->height = !empty($_POST['gameheight']) ? $_POST['gameheight'] : '';

    if ( ($_POST['importtype'] == 'ibparcade') OR ($_POST['importtype'] == 'phpbb') ) {
      $game->slug = $_POST['slug'];
    }
    else {
      $game->slug           = preg_replace("/[^a-zA-Z0-9 ]/", "", strtolower($_POST['gamename']));
      $game->slug           = str_replace(" ", "-", $game->slug);
    }

    $game->name           = $_POST['gamename'];
    $game->type           = $_POST['importtype'];
    $game->uuid           = md5($game->name.'import');
    $game->game_tag       = ( !empty($_POST['importgametag'])) ? $_POST['importgametag'] : crc32($game->uuid);
    $game->thumbnail_url  = $_POST['importthumb'];
    $game->description    = addslashes($_POST['gamedescr']);
    $game->instructions   = addslashes($_POST['gameinstr']);
    $game->control        = '';
    $game->rating         = '';
    $game->tags           = $_POST['gametags'];
    $game->categs         = ( isset($_POST['gamecategs']) ) ? implode(",", $_POST['gamecategs']) : 'Other';
    $game->created        = gmdate( 'Y-m-d H:i:s', ( time() + (get_option( 'gmt_offset' ) * 3600 ) ) );
    $game->leaderboard_enabled = filter_input( INPUT_POST, 'lbenabled' );

if ( ! empty( $_POST['highscoretype'] ) && 'low' == $_POST['highscoretype'] ) {
      $game->highscore_type = 'ASC';
    }
    else {
      $game->highscore_type = 'DESC';
    }
    $game->coins_enabled  = '';
    $game->status         = 'new';
    $game->screen1_url    = $_POST['importscreen1'];
    $game->screen2_url    = $_POST['importscreen2'];
    $game->screen3_url    = $_POST['importscreen3'];
    $game->screen4_url    = $_POST['importscreen4'];
    $game->video_url      = '';

    // Add game to table
    myarcade_insert_game($game);

    // Add the game as blog post
    if ($_POST['publishstatus'] != 'add') {
      global $wpdb;
      $gameID = $wpdb->get_var("SELECT id FROM ".MYARCADE_GAME_TABLE." WHERE uuid = '$game->uuid'");
      if ( !empty($gameID) ) {
        myarcade_add_games_to_blog( array('game_id' => $gameID, 'post_status' => $_POST['publishstatus'], 'echo' => false) );

        echo '<div class="mabp_info mabp_680"><p>'.sprintf(__("Import of '%s' was succsessful.", MYARCADE_TEXT_DOMAIN), $game->name).'</p></div>';
      }
      else  {
        echo '<div class="mabp_error mabp_680"><p>'.__("Can't import that game...", MYARCADE_TEXT_DOMAIN).'</p></div>';
      }
    }
    else {
      echo '<div class="mabp_info mabp_680"><p>'. sprintf(__("Game added successfully: %s", MYARCADE_TEXT_DOMAIN), $game->name).'</p></div>';
    }
  }

  $categs = get_all_category_ids();
?>
<?php @include_once('myarcadeplugin_js.php'); ?>
<div id="myabp_import">
  <h2><?php _e("Import Individual Games", MYARCADE_TEXT_DOMAIN); ?></h2>

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Import Method", MYARCADE_TEXT_DOMAIN); ?></h3></td>
        </tr>
        <tr>
          <td>
            <select size="1" name="importmethod" id="importmethod">
              <option value="importswfdcr"><?php _e("Upload / Grab SWF or DCR game", MYARCADE_TEXT_DOMAIN); ?>&nbsp;</option>
              <option value="importembedif"><?php _e("Import Embed / Iframe game", MYARCADE_TEXT_DOMAIN); ?></option>
              <option value="importibparcade"><?php _e("- PRO - Upload IBPArcade game", MYARCADE_TEXT_DOMAIN); ?></option>
              <option value="importphpbb"><?php _e("- PRO - Upload ZIP File / PHPBB / Mochi", MYARCADE_TEXT_DOMAIN); ?></option>
              <option value="importunity"><?php _e("- PRO - Import Unity game", MYARCADE_TEXT_DOMAIN); ?></option>
            </select>
            <br />
            <i><?php _e("Choose a desired import method.", MYARCADE_TEXT_DOMAIN); ?></i>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <?php myarcade_get_max_post_size_message(); ?>

  <?php @include_once('form-swfdcr.php'); ?>
</div><?php // end #myabp_import ?>
<div class="clear"></div>

 <?php

  myarcade_footer();
} // END - Import Games