<?php
$root = dirname( dirname( dirname( dirname( dirname(__FILE__)))));
if ( file_exists($root . '/wp-load.php') ) {
  require_once($root . '/wp-load.php'); 
}

// Check user
if ( function_exists('current_user_can') && !current_user_can('edit_posts') ) die();

require_once(ABSPATH . 'wp-admin/includes/file.php');

// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
$upload_error_strings = array( false,
  __( "The uploaded file exceeds the upload_max_filesize directive in php.ini." ),
  __( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form." ),
  __( "The uploaded file was only partially uploaded." ),
  __( "No file was uploaded." ),
  '',
  __( "Missing a temporary folder." ),
  __( "Failed to write file to disk." ),
  __( "File upload stopped by extension." ));

$home           = get_option('siteurl');
$games_dir_abs  = ABSPATH.MYARCADE_GAMES_DIR;
$thumbs_dir_abs = ABSPATH.MYARCADE_THUMBS_DIR;

$game = new stdClass();
$game->info_dim = '';
$game->error = ''; 

$result = false;

/**
 * Generate a random string
 * 
 * @return string 
 */
function myarcade_generate_random() { return time(); }

// Check the submission
switch ( $_POST['upload'] ) {
  
  // Upload SWF / DCR File
  case 'swf':  
  {
    if ( !empty($_FILES['gamefile']['name']) ) {      
      // Error check
      if ( !empty($_FILES['gamefile']['error']) ) {
        $game->error = $upload_error_strings[$_FILES['gamefile']['error']];
      }
      else {
        $file_temp = $_FILES['gamefile']['tmp_name'];
        $file_info = pathinfo($_FILES['gamefile']['name']);
        // generate new file name
        $file_name = str_replace(" ", "_", $file_info['filename']).'_'.myarcade_generate_random().'.'.$file_info['extension'];
        $file_abs   = $games_dir_abs .$file_name;    
        $result = move_uploaded_file($file_temp, $file_abs);
        // Delete temp file
        @unlink($_FILES['gamefile']['tmp_name']);
      }
    }
    else if ( !empty($_POST['gameurl']) ) {
      // grab from net?
      $file_temp = myarcade_get_file($_POST['gameurl']);
      
      if ( !empty($file_temp['error']) ) {
        // Get error message
        $game->error = $file_temp['error'];
      }
      else {  
        $file_info = pathinfo($_POST['gameurl']);
        $file_name = str_replace(" ", "_", $file_info['filename']).'_'.myarcade_generate_random().'.'.$file_info['extension'];
        $file_abs  = $games_dir_abs.$file_name;
        $result    = file_put_contents($file_abs, $file_temp['response']);       
      }
    }
    
    if ( empty($game->error) ) {
      
      if ($result == true) {
        //$file_info = pathinfo($file_abs);

        // Get the file extension
        if ( strtolower( $file_info['extension'] ) == 'dcr') {
          $game->type = 'dcr';
        }
        else {
          $game->type = 'custom';      
        }

        $game->name = $file_info['filename'];
        $game->location_abs = $file_abs;
        $game->location_url = $home. '/' .MYARCADE_GAMES_DIR.$file_name;

        // try to detect dimensions
        $game_dimensions = @getimagesize($file_abs);
        $game->width    = intval($game_dimensions[0]);
        $game->height   = intval($game_dimensions[1]);
        $game->info_dim = 'Game dimensions: '.$game->width.'x'.$game->height;

        if ( empty($game->width) || empty($game->height) ) {
          $game->width  = 0;
          $game->height = 0;
          $game->info_dim = 'Can not detect game dimensions';
        }

        // Try to get the game name 
        $name = explode('.', $game->name);
        $game->realname = str_replace('_', ' ', $name[0]);
      }
      else {
        $game->error = 'Can not upload file!';  
      }
    } 
  }
  break;
   
  // Upload Game Thumb
  case 'thumb':    
  {	
    if ( !empty($_FILES['thumbfile']['name']) ) {
      // Error check
      if ( !empty($_FILES['gamefile']['error']) ) {
        $game->error = $upload_error_strings[$_FILES['gamefile']['error']];
      }
      else {
        $file_temp = $_FILES['thumbfile']['tmp_name'];
        $file_info = pathinfo($_FILES['thumbfile']['name']);
        // generate new file name
        $file_name = str_replace(" ", "_", $file_info['filename']).'_'.myarcade_generate_random().'.'.$file_info['extension'];
        $file_abs  = $thumbs_dir_abs . $file_name;    
        $result = move_uploaded_file($file_temp, $file_abs);
        // Delete temp file
        @unlink($_FILES['thumbfile']['tmp_name']);        
      }
    }
    else if  ( !empty($_POST['thumburl']) ) {
      // grab from net?
      $file_temp = myarcade_get_file($_POST['thumburl']);
                  
      if ( !empty($file_temp['error']) ) {
        // Get error message
        $game->error = $file_temp['error'];
      }
      else {       
        $file_info = pathinfo($_POST['thumburl']);
        $file_name = str_replace(" ", "_", $file_info['filename']).'_'.myarcade_generate_random().'.'.$file_info['extension'];
        $file_abs  = $thumbs_dir_abs.$file_name;            
        $result = file_put_contents($file_abs, $file_temp['response']);       
      }
    }
    
    if ( empty($game->error) ) {
      if ($result == true) {
        $game->thumb_name = $file_name;
        $game->thumb_abs  = $file_abs;
        $game->thumb_url  = $home. '/' .MYARCADE_THUMBS_DIR.$file_name;      
      }   
      else {
        $game->error = 'Can not upload thumbnail!';  
      }
    }
  }
  break;
   
  // Upload Game Screenshots
  case 'screen':
  {
    for ($i = 0; $i <= 3; $i++) {
      $screen = 'screen'.$i;
      $screen_url = $screen.'_url';   
      
      $result = false;   
      
      if ( !empty($_FILES[$screen]['name']) ) {
        // Error check
        if ( !empty($_FILES[$screen]['error']) ) {
          $game->error = $upload_error_strings[$_FILES[$screen]['error']];
        }
        else {        
          // There is a screen to upload
          $file_temp = $_FILES[$screen]['tmp_name'];
          $file_info = pathinfo($_FILES[$screen]['name']);
          // generate new file name
          $file_name = str_replace(" ", "_", $file_info['filename']).'_'.(myarcade_generate_random()+$i).'.'.$file_info['extension'];
          //$file_name = $_FILES[$screen]['name'].'_'.myarcade_generate_random();
          $file_abs   = $thumbs_dir_abs .$file_name;
          $result = move_uploaded_file($file_temp, $file_abs);
          // Delete temp file
          @unlink($_FILES[$screen]['tmp_name']); 
        }
      }
      else if  ( !empty($_POST[$screen.'url']) ) {
         // There is a screen to grab
        $file_temp = myarcade_get_file($_POST[$screen.'url']);
        
        if ( !empty($file_temp['error']) ) {
          // Get error message
          $game->error = $file_temp['error'];
        }
        else {       
          $file_info = pathinfo($_POST[$screen.'url']);
          $file_name = str_replace(" ", "_", $file_info['filename']).'_'.(myarcade_generate_random()+$i).'.'.$file_info['extension'];
          $file_abs  = $thumbs_dir_abs.$file_name;            
          $result = file_put_contents($file_abs, $file_temp['response']);       
        }    
      }
      
      if ($result == true) {
        $game->screen_abs[$i] = $file_abs;
        $game->screen_url[$i] = $home. '/' .MYARCADE_THUMBS_DIR.$file_name;
        $game->screen_name[$i]= $file_name;
        $game->screen_error[$i] = 'OK';      
      }
      else {
        $game->screen_error[$i] = 'Upload Failed For Screen No. '.($i+1).' '.$game->error;
        $game->screen_abs[$i] = '';
        $game->screen_url[$i] = '';
        $game->screen_name[$i]= '';
      }      
    } 
  }
  break;
    
  // Upload IBPArcade Game
  case 'tar':
  {
    // Include the tar handler class
    require_once('tar.php');
    
    if ( class_exists('tar') ) {
      
      if ( !empty($_FILES['tarfile']['name']) ) {
        // Error check
        if ( !empty($_FILES['tarfile']['error']) ) {
          $game->error = $upload_error_strings[$_FILES['tarfile']['error']];
        }
        else {        
          $file_temp = $_FILES['tarfile']['tmp_name'];
          $tarname = $_FILES['tarfile']['name'];
          $file_abs   = $games_dir_abs .$tarname;   
          // Put the uploaded file into the working directory
            // @ - unterdrücke Fehlermeldungen
          $result = @rename($file_temp, $games_dir_abs.$tarname);
        }
      }
      elseif  ( !empty($_POST['tarurl']) ) {
        // grab from net?
        $file_temp = myarcade_get_file($_POST['tarurl']);
                
        if ( !empty($file_temp['error']) ) {
          // Get error message
          $game->error = $file_temp['error'];
        }
        else { 
          $tarname = basename($_POST['tarurl']);
          $file_abs  = $games_dir_abs.$tarname;
          $result = file_put_contents($file_abs, $file_temp['response']);
        }
      }
      else {
        $result = false;
      }       
    
      if ( empty($game->error) ) {
        
        if ($result == true) {
          $tar_handle = new tar(); 
          $tar_handle->new_tar($games_dir_abs, $tarname);
          $tar_filelist = $tar_handle->list_files();

          // Get the config file
          foreach ($tar_filelist as $filename) {
            if( preg_match('/(.*)(.php)$/i' , $filename , $filematch) ) {
              break;            
            }          
          }

          if ( !empty($filematch) ) {
            $configfile   = $filematch[0];

            // Extract all files into the working directory  
            $tar_handle->extract_files($games_dir_abs);

            // Include the game config file
            if ( file_exists($games_dir_abs.$configfile) ) {
              require_once($games_dir_abs.$configfile);

              $swf_file    = stripslashes($config['gname']).'.swf';
              @chmod($games_dir_abs.$swf_file , 0777 );
              $thumb_file  = stripslashes($config['gname']).'1.gif';
              $thumb_file2 = stripslashes($config['gname']).'2.gif';

              $rand_thumb = stripslashes($config['gname']).'_'.myarcade_generate_random().'.gif';

              // Move the thumbnail to the right directory
              @chmod($games_dir_abs.$thumb_file , 0777 );
              @rename($games_dir_abs.$thumb_file, $thumbs_dir_abs.$rand_thumb);

              // Delete the second thumb
              @chmod($games_dir_abs.$thumb_file2 , 0777 );
              @unlink($games_dir_abs.$thumb_file2);
              // Delete the uploaded tar file
              @chmod($games_dir_abs.$tarname , 0777 );
              @unlink($games_dir_abs.$tarname);

              $game->type = 'ibparcade';
              $game->name = $tarname;
              $game->location_url = $home. '/' .MYARCADE_GAMES_DIR.$swf_file;
              $game->thumbnail_url= $home. '/' .MYARCADE_THUMBS_DIR.$rand_thumb;

              // try to detect dimensions
              $game->width        = stripslashes($config['gwidth']);
              $game->height       = stripslashes($config['gheight']);
              $game->info_dim = 'Game dimensions: '.$game->width.'x'.$game->height;

              if ( empty($game->width) || empty($game->height) ) {
                $game->width  = 0;
                $game->height = 0;
                $game->info_dim = 'Can not detect game dimensions';
              }

              // Try to get the game name 
              $game->realname = stripslashes($config['gtitle']);
              $game->slug = $config['gname'];        
              $game->description  = stripslashes($config['gwords'].' '.$config['object']);
              $game->instructions = stripslashes($config['gkeys']);
              $game->highscore_type = stripslashes($config['highscore_type']);


              if ( !empty($game->highscore_type) ) {
                $game->leaderboard_enabled = '1';
              }

              // Delete the config file
              @chmod($games_dir_abs.$configfile , 0777 );
              @unlink($games_dir_abs.$configfile);
            }
            else {
              $game->error = "Config file not found..";
            }        
          }
          else {
            $game->error = "Can not get the config file...";
          } 
        }
        else {
          $game->error = 'Can not upload file!';  
        }
      }
    }
    else {
      $game->error = 'Can not include the tar class.';
    }
  }
  break;
   

  // Upload PHPBB Game
  case 'phpbb':
  {
    if ( !empty($_FILES['zipfile']['name']) ) {
      // Error check
      if ( !empty($_FILES['zipfile']['error']) ) {
        $game->error = $upload_error_strings[$_FILES['zipfile']['error']];
      }
      else {      
        $file_temp = $_FILES['zipfile']['tmp_name'];
        $zipname = $_FILES['zipfile']['name'];
        $file_abs   = $games_dir_abs.$zipname;   
        // Put the uploaded file into the working directory
        $result = @rename($file_temp, $games_dir_abs.$zipname);
      }
    }
    elseif  ( !empty($_POST['zipurl']) ) {
      // grab from net?
      $file_temp = myarcade_get_file($_POST['zipurl']);

      if ( !empty($file_temp['error']) ) {
        // Get error message
        $game->error = $file_temp['error'];
      }
      else { 
        $zipname = basename($_POST['zipurl']);
        $file_abs  = $games_dir_abs.$zipname;
        $result = file_put_contents($file_abs, $file_temp['response']);
      }
    }
    else {
      $result = false;
    }

    if ($result == true) {
      // Extract the zip file
      require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');  
      $archive= new PclZip($file_abs);
      $contents = $archive->listContent();       
      $images = array('png', 'jpg', 'gif', 'bmp');

      // find needed files
      foreach ($contents as $content) {
        if ( $content['folder'] == false ) {
          $ext = pathinfo($content['filename'], PATHINFO_EXTENSION);

          // Get the thumbnail
          if ( in_array($ext, $images) ) {
            $thumb_file = $content['filename'];
          }
          elseif ( $ext == 'swf') {
            $swf_file = $content['filename'];
          }
        }
      }

      if ( isset($thumb_file) && isset($swf_file) ) {
        // Proceed with the import
        
        
        // Extract files
        if ( $archive->extract(PCLZIP_OPT_PATH, ABSPATH.MYARCADE_GAMES_DIR) ) {
          
          $game->type = 'phpbb';
          $game->name = ucfirst( pathinfo($swf_file, PATHINFO_FILENAME) );
          $game->location_url = $home. '/' .MYARCADE_GAMES_DIR.$swf_file;
          $game->thumbnail_url= $home. '/' .MYARCADE_GAMES_DIR.$thumb_file;

          // try to detect dimensions
          $game_dimensions = @getimagesize(ABSPATH.MYARCADE_GAMES_DIR.$swf_file);
          $game->width    = intval($game_dimensions[0]);
          $game->height   = intval($game_dimensions[1]);
          $game->info_dim = 'Game dimensions: '.$game->width.'x'.$game->height;

          if ( empty($game->width) || empty($game->height) ) {
            $game->width  = 0;
            $game->height = 0;
            $game->info_dim = 'Can not detect game dimensions';
          }

          // Try to get the game name 
          $game->realname = $game->name;
          $game->slug = strtolower($game->name);
        }
        else {
          // Error
          $game->error = $archive->errorInfo();
        }        
      }
      else {
        // Thumbnail and/or Swf file not found
        $game->error = 'Invalid ZIP file';
      }

      // Remove the zip file
      @chmod($file_abs , 0777 );
      @unlink($file_abs);        
    }
    else {
      //$game->error = 'Can not upload file!';  
    }
  }
  break;    
    
  // Import Embed / Iframe Code
  case 'emif':
  {
    if ( !empty($_POST['embedcode']) ) {
      $game->type = 'embed';
      $game->importgame = urlencode(str_replace('"', '\'', $_POST['embedcode']));  
      $game->result = 'OK';
    }
    else {
      $game->error = 'No embed code entered!';        
    }
  }      
  break;
    
  // What to import??
  default:
  {
    $game->error = 'Unknown Import Method';
  }
  break;
      
} // end swtich


//
// Prepare the output
//
$json = json_encode($game);

echo $json;
 
die();
?>