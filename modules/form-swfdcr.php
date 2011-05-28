  <?php // UPLOAD Game  ?>
  <div id="importswfdcr"> 
   <h2><?php _e("Upload SWF Games", MYARCADE_TEXT_DOMAIN); ?></h2>
   <h2 class="box"><?php _e("Game Files", MYARCADE_TEXT_DOMAIN); ?></h2> 
  <form action="<?php echo WP_PLUGIN_URL; ?>/myarcadeblog/modules/import_handler.php" method="post" enctype="multipart/form-data" id="uploadFormSWF">  
    <input type="hidden" name="upload" value="swf" />
    
    <div class="container">
      <div class="block">
        <table class="optiontable" width="100%">
          <tr>
            <td><h3><?php _e("Game File", MYARCADE_TEXT_DOMAIN); ?> <small>(<?php _e("required", MYARCADE_TEXT_DOMAIN); ?>)</small></h3></td>
          </tr>
          <tr>
              <td><p style="margin-bottom:10px"><?php _e("Important: add a game file first before you do anything else.", MYARCADE_TEXT_DOMAIN); ?></p></td>
          </tr>
          <tr>
            <td>
            <p style="font-style:italic;margin:5px 0;"><?php _e("Select a game file from your local computer (swf).", MYARCADE_TEXT_DOMAIN); ?></p>
          	 <?php _e("Local File:", MYARCADE_TEXT_DOMAIN); ?> <input type="file" size="50" name="gamefile" />
            </td>
          </tr>
          <tr>
            <td>
               <p style="font-style:italic;margin:5px 0;"><?php _e("<strong>OR</strong> paste an url to a game file that should be grabbed from the net (swf).", MYARCADE_TEXT_DOMAIN); ?></p>
              <?php _e("URL:", MYARCADE_TEXT_DOMAIN); ?> <input name="premium" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong>
            </td>
          </tr>
          <tr>
          <td>
            <p>
              <input type="button" id="swfupload" name="swfupload" value="<?php _e('Start Upload', MYARCADE_TEXT_DOMAIN); ?>" />
            </p>
            <div id="loader"></div>  
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
    <br />
    <div class="container">
    <p class="mabp_info" style="padding:5px"><?php echo MYARCADE_LOCKED_IMG; ?> IBPArcade Import is a Premium Feature!</p>
    </div>
  </div>
  
  
  <?php // IMPORT EMBED / IFRAME GAME ?>
  <div id="importembedif">
    <h2><?php _e("Embed / Iframe Games", MYARCADE_TEXT_DOMAIN); ?></h2>
    <br />
    <div class="container">
    <p class="mabp_info" style="padding:5px"><?php echo MYARCADE_LOCKED_IMG; ?> Embed / Iframe Import is a Premium Feature!</p>
    </div>
  </div>
  
  
  <?php // UPLOAD THUMB ?>
  <div id="thumbform">
  <form action="<?php echo WP_PLUGIN_URL; ?>/myarcadeblog/modules/import_handler.php" method="post" enctype="multipart/form-data" id="uploadFormTHUMB">  
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
               <p style="font-style:italic;margin:5px 0;"><?php _e("<strong>OR</strong> paste an url to a thumbnail that should be grabbed from the net.", MYARCADE_TEXT_DOMAIN); ?></p>
              <?php _e("URL:", MYARCADE_TEXT_DOMAIN); ?> <input name="premium3" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong>
            </td>
          </tr>
          <tr>
          <td>
            <p>
              <input type="button" id="thumbupload" name="thumbupload" value="<?php _e('Start Upload', MYARCADE_TEXT_DOMAIN); ?>" />
            </p>
            <div id="loaderthumb"></div>  
            <div id="filenamethumb"></div>
          </td>
          </tr>          
        </table>            
      </div>
    </div>
  </form> 
  </div> 
  
  
  <?php // UPLOAD SCREENSHOTS ?>
    <form action="<?php echo WP_PLUGIN_URL; ?>/myarcadeblog/modules/import_handler.php" method="post" enctype="multipart/form-data" id="uploadFormSCREEN"> 
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
              <p style="font-style:italic;margin:5px 0;"><?php _e("<strong>OR</strong> paste image URL's that should be grabbed from the net.", MYARCADE_TEXT_DOMAIN); ?></p> 
              </td>
              
          </tr>
          <tr>
            <td>
              <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 1
            </td>
            <td><input name="premium5" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
          </tr>
          <tr>
            <td>
              <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 2
            </td>
            <td><input name="premium6" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
          </tr>  
          <tr>
            <td>
              <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 3
            </td>
            <td><input name="premium7" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
          </tr>  
          <tr>
            <td>
              <?php _e("Screenshot URL", MYARCADE_TEXT_DOMAIN); ?> 4
            </td>
            <td><input name="premium8" type="text" size="50" disabled /> <strong><?php echo MYARCADE_LOCKED_IMG; ?> Premium Feature</strong></td>
          </tr>          
          <tr>
            <td colspan="2">
              <p>
                <input type="button" id="screenupload" name="screenupload" value="<?php _e('Start Upload', MYARCADE_TEXT_DOMAIN); ?>" />
              </p>
              <div id="loaderscreen"></div>  
              <div id="filenamescreen"></div>
            </td>
          </tr>                                       
        </table>            
      </div>
    </div> 
    </form>      
   
   
    <h2 class="box"><?php _e("Game Informations", MYARCADE_TEXT_DOMAIN); ?></h2> 
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
              <i><?php _e("Enter the name of a game that should be imported!", MYARCADE_TEXT_DOMAIN); ?></i>
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
              <i><?php echo MYARCADE_LOCKED_IMG; ?> <?php _e("MyArcadePlugin Pro is able to detect dimensions automatically for many flash files...", MYARCADE_TEXT_DOMAIN);?></i>
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
              <i><?php _e("Describe this game with your own words. An unique description can improve your ranking on search engines.", MYARCADE_TEXT_DOMAIN); ?></i>
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
              <i><?php _e("Write a short how to play this game.", MYARCADE_TEXT_DOMAIN); ?></i>
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
              <i><?php _e("Enter some describing tags. Separate the tags with comma (,).", MYARCADE_TEXT_DOMAIN); ?></i>
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
              <input type="radio" name="publishstatus" value="add">&nbsp;<?php _e("Add to the games database (don't publish)", MYARCADE_TEXT_DOMAIN); ?>
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