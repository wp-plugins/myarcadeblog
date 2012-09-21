<?php defined('MYARCADE_VERSION') or die(); ?>
<?php // UPLOAD Game  ?>
<div id="importswfdcr"> 
 <h2><?php _e("Upload / Grab SWF or DCR Games", MYARCADE_TEXT_DOMAIN); ?></h2>
 <h2 class="box"><?php _e("Game Files", MYARCADE_TEXT_DOMAIN); ?></h2>
    
<form action="<?php echo MYARCADE_CORE_URL; ?>/import_handler.php" method="post" enctype="multipart/form-data" id="uploadFormSWF">  
  <input type="hidden" name="upload" value="swf" />

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Game File", MYARCADE_TEXT_DOMAIN); ?> <small>(<?php _e("required", MYARCADE_TEXT_DOMAIN); ?>)</small></h3></td>
        </tr>
        <tr>
            <td><p style="margin-bottom:10px"><?php _e("Important: A game file must be added prior to completing the other steps.", MYARCADE_TEXT_DOMAIN); ?></p></td>
        </tr>
        <tr>
          <td>
          <p style="font-style:italic;margin:5px 0;"><?php _e("Select a game file from your local computer (swf or dcr).", MYARCADE_TEXT_DOMAIN); ?></p>
           <?php _e("Local File:", MYARCADE_TEXT_DOMAIN); ?> <input type="file" size="50" id="gamefile" name="gamefile" />
          </td>
        </tr>
        <tr>
          <td>
             <p style="font-style:italic;margin:5px 0;"><?php _e("<strong>OR</strong> paste a URL to import a game file from the internet( swf or dcr).", MYARCADE_TEXT_DOMAIN); ?></p>
            <?php _e("URL:", MYARCADE_TEXT_DOMAIN); ?> <input name="premium" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong>
          </td>
        </tr>
        <tr>
        <td>
          <p>
            <input type="submit" id="swfupload" name="swfupload" value="<?php _e('Add File', MYARCADE_TEXT_DOMAIN); ?>" />
          </p>
          <img id="loadimgswf" src="<?php echo MYARCADE_CORE_URL?>/images/loading.gif" style="display:none;" />
          <div id="filename"></div>
        </td>
        </tr>
      </table>
    </div>
  </div>
</form>
</div>

<?php 
/**
 * TAR IMPORT
 */
 ?>
<div id="importibparcade">
  <?php // UPLOAD TAR Game  ?>
  <h2><?php _e("Upload / Grab IBPArcade Games", MYARCADE_TEXT_DOMAIN); ?></h2>
  <h2 class="box"><?php _e("Game Files", MYARCADE_TEXT_DOMAIN); ?></h2>
  <div class="container">
    <p class="mabp_info" style="padding:5px"><?php echo MYARCADE_LOCKED_IMG; ?> IBPArcade Import is a Premium Feature!</p>
  </div>
</div>

<?php 
/**
 * PHPBB IMPORT
 */
 ?>
<div id="importphpbb">
  <?php // UPLOAD TAR Game  ?>
     <h2><?php _e("Upload / Grab PHPBB Games", MYARCADE_TEXT_DOMAIN); ?></h2>
     <h2 class="box"><?php _e("Game Files", MYARCADE_TEXT_DOMAIN); ?></h2>
      <div class="container">
        <p class="mabp_info" style="padding:5px"><?php echo MYARCADE_LOCKED_IMG; ?> PHPBB / ZIP Import is a Premium Feature!</p>
      </div>
  </div>


<?php // IMPORT EMBED / IFRAME GAME ?>
<div id="importembedif">
<h2><?php _e("Embed / Iframe Games", MYARCADE_TEXT_DOMAIN); ?></h2>
<h2 class="box"><?php _e("Game Files", MYARCADE_TEXT_DOMAIN); ?></h2>   
  <input type="hidden" name="upload" value="emif" />
  <div id="importembedif">
    <div class="container">
      <p class="mabp_info" style="padding:5px"><?php echo MYARCADE_LOCKED_IMG; ?> Embed / Iframe Import is a Premium Feature!</p>
    </div>
  </div>
</div>


<?php // UPLOAD THUMB ?>
<div id="thumbform">
<form action="<?php echo MYARCADE_CORE_URL; ?>/import_handler.php" method="post" enctype="multipart/form-data" id="uploadFormTHUMB">  
  <input type="hidden" name="upload" value="thumb" />

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Game Thumbnail", MYARCADE_TEXT_DOMAIN); ?> <small>(<?php _e("required", MYARCADE_TEXT_DOMAIN); ?>)</small></h3></td>
        </tr>
        <tr>
          <td>
          <p style="font-style:italic;margin:5px 0;"><?php _e("Select a thumbnail from your local computer.", MYARCADE_TEXT_DOMAIN); ?></p>
           <?php _e("Local File:", MYARCADE_TEXT_DOMAIN); ?> <input type="file" size="50" name="thumbfile" />
          </td>
        </tr>
        <tr>
          <td>
             <p style="font-style:italic;margin:5px 0;"><?php _e("<strong>OR</strong> paste a URL to import a thumbnail from the internet.", MYARCADE_TEXT_DOMAIN); ?></p>
            <?php _e("URL:", MYARCADE_TEXT_DOMAIN); ?> <input name="premium" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong>
          </td>
        </tr>
        <tr>
        <td>
          <p>
            <input type="submit" id="thumbupload" name="thumbupload" value="<?php _e('Add File', MYARCADE_TEXT_DOMAIN); ?>" />
          </p>
          <img id="loadimgthumb" src="<?php echo MYARCADE_CORE_URL?>/images/loading.gif" style="display:none;" />
          <div id="filenamethumb"></div>
        </td>
        </tr>
      </table>
    </div>
  </div>
</form> 
</div> 



<?php // UPLOAD SCREENSHOTS ?>
  <form action="<?php echo MYARCADE_CORE_URL; ?>/import_handler.php" method="post" enctype="multipart/form-data" id="uploadFormSCREEN">
    <input type="hidden" name="upload" value="screen" />

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td colspan="2"><h3><?php _e("Game Screenshots", MYARCADE_TEXT_DOMAIN); ?></h3></td>
        </tr>
        <tr>
          <td colspan="2">
            <p style="font-style:italic;margin:5px 0;"><?php _e("Select image files from your local computer", MYARCADE_TEXT_DOMAIN); ?></p></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot", MYARCADE_TEXT_DOMAIN); ?> 1
          </td>
          <td><input name="screen0" type="file" size="50" /></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot", MYARCADE_TEXT_DOMAIN); ?> 2
          </td>
          <td><input name="screen1" type="file" size="50" /></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot", MYARCADE_TEXT_DOMAIN); ?> 3
          </td>
          <td><input name="screen2" type="file" size="50" /></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot", MYARCADE_TEXT_DOMAIN); ?> 4
          </td>
          <td><input name="screen3" type="file" size="50" /></td>
        </tr>
        <tr>
          <td colspan="2">
            <p style="font-style:italic;margin:5px 0;"><?php _e("<strong>OR</strong> paste URL's to import screenshots from the internet.", MYARCADE_TEXT_DOMAIN); ?></p></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 1
          </td>
          <td><input name="premium" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 2
          </td>
          <td><input name="premium" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 3
          </td>
          <td><input name="premium" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
        </tr>
        <tr>
          <td>
            <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 4
          </td>
          <td><input name="premium" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
        </tr>
        <tr>
          <td colspan="2">
            <p>
              <input type="submit" id="screenupload" name="screenupload" value="<?php _e('Add File(s)', MYARCADE_TEXT_DOMAIN); ?>" />
            </p>
             <img id="loadimgscreen" src="<?php echo MYARCADE_CORE_URL?>/images/loading.gif" style="display:none;" />
            <div id="filenamescreen"></div>
          </td>
        </tr>
      </table>
    </div>
  </div>
  </form>


  <h2 class="box"><?php _e("Game Information", MYARCADE_TEXT_DOMAIN); ?></h2>
<form method="post" name="FormCustomGame" onsubmit="return myarcade_chkImportCustom()">
  <input type="hidden" name="impcostgame"   value="import" />
  <input type="hidden" name="importgame"    id="importgame" />
  <input type="hidden" name="importtype"    id="importtype" />
  <input type="hidden" name="importthumb"   id="importthumb" />
  <input type="hidden" name="importscreen1" id="importscreen1" />
  <input type="hidden" name="importscreen2" id="importscreen2" />
  <input type="hidden" name="importscreen3" id="importscreen3" />
  <input type="hidden" name="importscreen4" id="importscreen4" />
  <input type="hidden" name="highscoretype" id="highscoretype" />
  <input type="hidden" name="lbenabled"     id="lbenabled" />
  <input type="hidden" name="slug"          id="slug" />

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Name", MYARCADE_TEXT_DOMAIN); ?> <small>(<?php _e("required", MYARCADE_TEXT_DOMAIN); ?>)</small></h3></td>
        </tr>
        <tr>
          <td>
            <input name="gamename" id="gamename" type="text" size="50" />
            <br />
            <i><?php _e("Enter the name of the imported game.", MYARCADE_TEXT_DOMAIN); ?></i>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td colspan="2"><h3><?php _e("Game Dimensions", MYARCADE_TEXT_DOMAIN); ?></h3></td>
        </tr>
        <tr>
          <td>
            <?php _e("Game width (px)", MYARCADE_TEXT_DOMAIN); ?>: <input id="gamewidth" name="gamewidth" type="text" size="20" />
          </td>
          <td>
            <?php _e("Game height (px)", MYARCADE_TEXT_DOMAIN); ?>: <input id="gameheight" name="gameheight" type="text" size="20" />
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <br />
            <i><?php _e("If MyArcadePlugin is unable to detect dimensions for the flash files automatically, the dimensions should be indicated manually.", MYARCADE_TEXT_DOMAIN);?>
            </i>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Game Description", MYARCADE_TEXT_DOMAIN); ?> <small>(<?php _e("required", MYARCADE_TEXT_DOMAIN); ?>)</small></h3></td>
        </tr>
        <tr>
          <td>
            <textarea rows="6" cols="80" name="gamedescr" id="gamedescr"></textarea>
            <br />
            <i><?php _e("Enter description of the game (a unique description can help improve search engine ranking).", MYARCADE_TEXT_DOMAIN); ?></i>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Game Instructions", MYARCADE_TEXT_DOMAIN); ?></h3></td>
        </tr>
        <tr>
          <td>
            <textarea rows="6" cols="80" name="gameinstr" id="gameinstr"></textarea>
            <br />
            <i><?php _e("Write brief instructions on how to play the game.", MYARCADE_TEXT_DOMAIN); ?></i>
          </td>
        </tr>
      </table>
    </div>
  </div>

<div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Tags", MYARCADE_TEXT_DOMAIN); ?></h3></td>
        </tr>
        <tr>
          <td>
            <input name="gametags" type="text" size="50" />
            <br />
            <i><?php _e("Enter description tags. Separate the tags with commas (,).", MYARCADE_TEXT_DOMAIN); ?></i>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Post Status", MYARCADE_TEXT_DOMAIN); ?></h3></td>
        </tr>
        <tr>
          <td>
            <input type="radio" name="publishstatus" value="publish" checked>&nbsp;<?php _e("Publish", MYARCADE_TEXT_DOMAIN); ?>
            <br />
            <input type="radio" name="publishstatus" value="draft">&nbsp;<?php _e("Save as draft", MYARCADE_TEXT_DOMAIN); ?>
            <br />
            <input type="radio" name="publishstatus" value="add">&nbsp;<?php _e("Add to the games database (don't add as a blog post)", MYARCADE_TEXT_DOMAIN); ?>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="container">
    <div class="block">
      <table class="optiontable" width="100%">
        <tr>
          <td><h3><?php _e("Category", MYARCADE_TEXT_DOMAIN); ?> <small>(<?php _e("required", MYARCADE_TEXT_DOMAIN); ?>)</small></h3></td>
        </tr>
        <tr>
          <td>
          <?php
            // Get all categories
            $i = count($categs);
            foreach ($categs as $cat_id)
            {
              $i--;
              $br = '';
              if ($i > 0) $br = '<br />';
              echo '<input type="checkbox" name="gamecategs[]" value="'.get_cat_name($cat_id).'" />&nbsp;'.get_cat_name($cat_id).$br;
            }
          ?>
            <br /><br />
            <i><?php _e("Select one or more categories for this game.", MYARCADE_TEXT_DOMAIN); ?></i>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="container">
    <div class="block">
       <input class="button-secondary" id="submit" type="submit" name="submit" value="<?php _e("Import Game", MYARCADE_TEXT_DOMAIN); ?>" />
    </div>
  </div>
</form> 