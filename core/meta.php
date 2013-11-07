<?php
/*
 * Module:       Meta Boxes
 * Author:       Daniel Bakovic
 * Author URI:   http://myarcadeplugin.com
 */

defined('MYARCADE_VERSION') or die();

/**
 * Add MyArcade Meta Box
 */
function myarcade_meta_boxes() {
  add_meta_box('myarcade-game-data', __('MyArcadePlugin Game Details', MYARCADE_TEXT_DOMAIN), 'myarcade_game_data_box', 'post', 'normal', 'high');
}
add_action('add_meta_boxes', 'myarcade_meta_boxes');


function myarcade_game_data_box() {
  global $post, $postID, $myarcade_distributors, $myarcade_game_type_custom;

  $postID = $post->ID;

  // Check if this post is a game
  $check_type = get_post_meta($postID, 'mabp_game_type', true);
  if( !empty($check_type) ) {

 $distributors = array_merge($myarcade_distributors, $myarcade_game_type_custom);

  wp_nonce_field( 'myarcade_save_data', 'myarcade_meta_nonce' );
  ?>
  <div class="panel-wrap myarcade_game_data">
    <ul class="myarcade_data_tabs tabs" style="display:none;">
      <li class="active"><a href="#myarcade_game_data"><?php _e('Game Details', MYARCADE_TEXT_DOMAIN); ?></a></li>
      <li class="files_tab"><a href="#myarcade_game_files"><?php _e('Game Files', MYARCADE_TEXT_DOMAIN); ?></a></li>
    </ul>

    <?php // Display game details ?>
    <div id="myarcade_game_data" class="panel myarcade_game_panel">
      <div class="options_group">
        <?php
        myarcade_wp_textarea_input ( array (
            'id' => 'mabp_description',
            'label' => __('Game Description', MYARCADE_TEXT_DOMAIN)
        ));

        myarcade_wp_textarea_input ( array (
            'id' => 'mabp_instructions',
            'label' => __('Game Instructions', MYARCADE_TEXT_DOMAIN)
        ));

        myarcade_wp_text_input( array(
            'id' => 'mabp_height',
            'label' => __('Height', MYARCADE_TEXT_DOMAIN),
            'description' => __('Game height in pixel (px)', MYARCADE_TEXT_DOMAIN)
        ));

        myarcade_wp_text_input( array(
            'id' => 'mabp_width',
            'label' => __('Width', MYARCADE_TEXT_DOMAIN),
            'description' => __('Game width in pixel (px)', MYARCADE_TEXT_DOMAIN)
        ));

        myarcade_wp_select( array(
            'id' => 'mabp_game_type',
            'label' => __('Game Type', MYARCADE_TEXT_DOMAIN),
            'options' => $distributors
        ));

        myarcade_wp_text_input( array(
            'id' => 'mabp_game_tag',
            'label' => __('Mochi Game Tag', MYARCADE_TEXT_DOMAIN),
            'description' => __('Game Tag is only important for Mochi games.', MYARCADE_TEXT_DOMAIN)
        ));

        myarcade_wp_select( array(
            'id' => 'mabp_leaderboard',
            'label' => __('Score Support', MYARCADE_TEXT_DOMAIN),
            'description' => __('Select if this game supports score submitting (Only Mochi or IBPArcade games).'),
            'options' => array( '' => 'No', '1' => 'Yes')
        ));

        myarcade_wp_select( array(
            'id' => 'mabp_score_order',
            'label' => __('Score Order', MYARCADE_TEXT_DOMAIN),
            'description' => __('How should MyArcadePlugin order scores for this game.'),
            'options' => array( 'DESC' => 'DESC (High to Low)', 'ASC' => 'ASC (Low to High)')
        ));
        ?>
      </div>
    </div>

    <?php // Display game files ?>
    <div id="myarcade_game_files" class="panel myarcade_game_panel">
      <div class="options_group">
        <?php
        // Game File
				$file_path = get_post_meta($post->ID, 'mabp_swf_url', true);
        $game_type = get_post_meta($post->ID, 'mabp_game_type', true);
        if ( $game_type == 'embed') {
          $field = array( 'id' => 'mabp_swf_url', 'label' => __('Embed Code', MYARCADE_TEXT_DOMAIN) );
          echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
				  <textarea name="'.$field['id'].'" id="'.$field['id'].'">'.$file_path.'</textarea>
				</p>';
                } else {
				$field = array( 'id' => 'mabp_swf_url', 'label' => __('Game File', MYARCADE_TEXT_DOMAIN) );
				echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="game_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL / Embed Code', MYARCADE_TEXT_DOMAIN).'" />
            <input type="button"  class="upload_game_button button" value="'.__('Upload a file', MYARCADE_TEXT_DOMAIN).'" />
				</p>';
               }

        $file_path = get_post_meta($post->ID, 'mabp_thumbnail_url', true);
				$field = array( 'id' => 'mabp_thumbnail_url', 'label' => __('Game Thumbnail', MYARCADE_TEXT_DOMAIN) );
				echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="thumbnail_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', MYARCADE_TEXT_DOMAIN).'" />
					<input type="button"  class="upload_thumbnail_button button" value="'.__('Upload a file', MYARCADE_TEXT_DOMAIN).'" />
				</p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen1_url', true);
				$field = array( 'id' => 'mabp_screen1_url', 'label' => __('Game Screenshot No. 1', MYARCADE_TEXT_DOMAIN) );
				echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="screen1_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', MYARCADE_TEXT_DOMAIN).'" />
					<input type="button"  class="upload_screen1_button button" value="'.__('Upload a file', MYARCADE_TEXT_DOMAIN).'" />
				</p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen2_url', true);
				$field = array( 'id' => 'mabp_screen2_url', 'label' => __('Game Screenshot No. 2', MYARCADE_TEXT_DOMAIN) );
				echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="screen1_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', MYARCADE_TEXT_DOMAIN).'" />
					<input type="button"  class="upload_screen2_button button" value="'.__('Upload a file', MYARCADE_TEXT_DOMAIN).'" />
				</p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen3_url', true);
				$field = array( 'id' => 'mabp_screen3_url', 'label' => __('Game Screenshot No. 3', MYARCADE_TEXT_DOMAIN) );
				echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="screen3_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', MYARCADE_TEXT_DOMAIN).'" />
					<input type="button"  class="upload_screen3_button button" value="'.__('Upload a file', MYARCADE_TEXT_DOMAIN).'" />
				</p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen4_url', true);
				$field = array( 'id' => 'mabp_screen4_url', 'label' => __('Game Screenshot No. 4', MYARCADE_TEXT_DOMAIN) );
				echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="screen4_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', MYARCADE_TEXT_DOMAIN).'" />
					<input type="button"  class="upload_screen4_button button" value="'.__('Upload a file', MYARCADE_TEXT_DOMAIN).'" />
				</p>';
        ?>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    // Uploading files
    var file_path_field;
    window.send_to_editor_default = window.send_to_editor;

    jQuery('.upload_thumbnail_button').live('click', function(){
      file_path_field = jQuery(this).parent().find('.thumbnail_path');
      formfield = jQuery(file_path_field).attr('name');
      window.send_to_editor = window.send_to_download_url;
      tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=myarcade_image&amp;from=wc01&amp;TB_iframe=true');
      return false;
    });

    jQuery('.upload_game_button').live('click', function(){
      file_path_field = jQuery(this).parent().find('.game_path');
      formfield = jQuery(file_path_field).attr('name');
      window.send_to_editor = window.send_to_download_url;
      tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=myarcade_game&amp;from=wc01&amp;TB_iframe=true');
      return false;
    });

    jQuery('.upload_screen1_button').live('click', function(){
      file_path_field = jQuery(this).parent().find('.screen1_path');
      formfield = jQuery(file_path_field).attr('name');
      window.send_to_editor = window.send_to_download_url;
      tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=myarcade_image&amp;from=wc01&amp;TB_iframe=true');
      return false;
    });

    jQuery('.upload_screen2_button').live('click', function(){
      file_path_field = jQuery(this).parent().find('.screen2_path');
      formfield = jQuery(file_path_field).attr('name');
      window.send_to_editor = window.send_to_download_url;
      tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=myarcade_image&amp;from=wc01&amp;TB_iframe=true');
      return false;
    });

    jQuery('.upload_screen3_button').live('click', function(){
      file_path_field = jQuery(this).parent().find('.screen3_path');
      formfield = jQuery(file_path_field).attr('name');
      window.send_to_editor = window.send_to_download_url;
      tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=myarcade_image&amp;from=wc01&amp;TB_iframe=true');
      return false;
    });

    jQuery('.upload_screen4_button').live('click', function(){
      file_path_field = jQuery(this).parent().find('.screen4_path');
      formfield = jQuery(file_path_field).attr('name');
      window.send_to_editor = window.send_to_download_url;
      tb_show('', 'media-upload.php?post_id=<?php echo $post->ID; ?>&amp;type=myarcade_image&amp;from=wc01&amp;TB_iframe=true');
      return false;
    });

    window.send_to_download_url = function(html) {
      file_url = jQuery(html).attr('href');
      if (file_url) {
        jQuery(file_path_field).val(file_url);
      }
      tb_remove();
      window.send_to_editor = window.send_to_editor_default;
    }
  </script>
  <?php
  }
  else {
    ?>
    <p>
      <?php _e("This post is not a game.", MYARCADE_TEXT_DOMAIN); ?>
    </p>
    <?php
  }
}


function myarcade_meta_box_save($post_id, $post) {

  if ( !isset($_POST) ) return $post_id;
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
  if ( !isset($_POST['myarcade_meta_nonce']) || (isset($_POST['myarcade_meta_nonce']) && !wp_verify_nonce( $_POST['myarcade_meta_nonce'], 'myarcade_save_data' ))) return $post_id;
  if ( !current_user_can( 'edit_post', $post_id )) return $post_id;

  $game_tag = (isset($_POST['mabp_game_tag'])) ? $_POST['mabp_game_tag'] : '';
  $game_height = (isset($_POST['mabp_height'])) ? $_POST['mabp_height'] : '';
  $game_width = (isset($_POST['mabp_width'])) ? $_POST['mabp_width'] : '';
  $game_description = (isset($_POST['mabp_description'])) ? $_POST['mabp_description'] : '';
  $game_instruction = (isset($_POST['mabp_instructions'])) ? $_POST['mabp_instructions'] : '';
  $game_scores = (isset($_POST['mabp_leaderboard'])) ? $_POST['mabp_leaderboard'] : '';


  update_post_meta($post_id, 'mabp_game_tag', $game_tag);
  update_post_meta($post_id, 'mabp_game_type',$_POST['mabp_game_type']);
  update_post_meta($post_id, 'mabp_height', $game_height);
  update_post_meta($post_id, 'mabp_width', $game_width);
  update_post_meta($post_id, 'mabp_description',  $game_description);
  update_post_meta($post_id, 'mabp_instructions', $game_instruction);
  update_post_meta($post_id, 'mabp_leaderboard', $game_scores);
  update_post_meta($post_id, 'mabp_score_order', $_POST['mabp_score_order'] );

  $thumb = (isset($_POST['mabp_thumbnail_url'])) ? $_POST['mabp_thumbnail_url'] : '';
  $game = (isset($_POST['mabp_swf_url'])) ? $_POST['mabp_swf_url'] : '';
  $screen1 = (isset($_POST['mabp_screen1_url'])) ? $_POST['mabp_screen1_url'] : '';
  $screen2 = (isset($_POST['mabp_screen2_url'])) ? $_POST['mabp_screen2_url'] : '';
  $screen3 = (isset($_POST['mabp_screen3_url'])) ? $_POST['mabp_screen3_url'] : '';
  $screen4 = (isset($_POST['mabp_screen4_url'])) ? $_POST['mabp_screen4_url'] : '';

  update_post_meta($post_id, 'mabp_thumbnail_url', $thumb);
  update_post_meta($post_id, 'mabp_swf_url', $game);
  update_post_meta($post_id, 'mabp_screen1_url', $screen1);
  update_post_meta($post_id, 'mabp_screen2_url', $screen2);
  update_post_meta($post_id, 'mabp_screen3_url', $screen3);
  update_post_meta($post_id, 'mabp_screen4_url', $screen4);
}
add_action('save_post', 'myarcade_meta_box_save', 1, 2);


function myarcade_wp_text_input( $field ) {
	global $postID, $post;

	if (!$postID) $postID = $post->ID;
	if (!isset($field['placeholder'])) $field['placeholder'] = '';
	if (!isset($field['class'])) $field['class'] = 'short';
	if (!isset($field['value'])) $field['value'] = get_post_meta($postID, $field['id'], true);

	echo '<p class="form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><input type="text" class="'.$field['class'].'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.esc_attr( $field['value'] ).'" placeholder="'.$field['placeholder'].'" /> ';

	if (isset($field['description']))
    echo '<span class="description">' .$field['description'] . '</span>';

	echo '</p>';
}


function myarcade_wp_textarea_input( $field ) {
	global $postID, $post;

	if (!$postID) $postID = $post->ID;
	if (!isset($field['placeholder'])) $field['placeholder'] = '';
	if (!isset($field['class'])) $field['class'] = 'short';
	if (!isset($field['value'])) $field['value'] = get_post_meta($postID, $field['id'], true);

	echo '<p class="form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><textarea class="'.$field['class'].'" name="'.$field['id'].'" id="'.$field['id'].'" placeholder="'.$field['placeholder'].'" rows="2" cols="20">'.esc_textarea( $field['value'] ).'</textarea> ';

	if ( isset( $field['description'] ) && $field['description'] ) {
		  echo '<span class="description">' . $field['description'] . '</span>';
	}
	echo '</p>';
}


function myarcade_wp_select( $field ) {
	global $postID, $post;

	if (!$postID) $postID = $post->ID;
	if (!isset($field['class'])) $field['class'] = 'select short';
	if (!isset($field['value'])) $field['value'] = get_post_meta($postID, $field['id'], true);

	echo '<p class="form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><select id="'.$field['id'].'" name="'.$field['id'].'" class="'.$field['class'].'">';

	foreach ($field['options'] as $key => $value) {
		echo '<option value="'.$key.'" ';
		selected($field['value'], $key);
		echo '>'.$value.'</option>';
  }

	echo '</select> ';

	if ( isset( $field['description'] ) && $field['description'] ) {
    echo '<span class="description">' . $field['description'] . '</span>';
	}

	echo '</p>';
}