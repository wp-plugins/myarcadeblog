<?php
/*
Module:       Default Settings
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/
 
$default_theme = mysql_escape_string('<p><div style="float:left;margin-right: 10px; margin-bottom: 10px;">%THUMB%</div>%DESCRIPTION% %INSTRUCTIONS%</p>');

$myarcade_general_default = array (
    'posts'         => '20',
    'status'        => 'publish',
    'schedule'      => '60',
    'down_thumbs'   => false,
    'down_games'    => false,
    'down_screens'  => false,
    'delete'        => false,
    'create_cats'   => true,
    'parent'        => '',
    'firstcat'      => false,
    'single'        => false,
    'singlecat'     => '',
    'max_width'     => '',
    'embed'         => 'manually',
    'template'      => $default_theme,
    'use_template'  => false,
    'post_type'     => 'post',
    'custom_category' => '',
    'custom_tags'   => '',
    'featured_image' => false
); 

$myarcade_mochi_default = array (
    'feed'          => 'http://www.mochimedia.com/feeds/games/',
    'feed_save'     => '',
    'default_feed'  => 'old',
    'publisher_id'  => '',
    'secret_key'    => '',
    'limit'         => '200',
    'tag'           => '',
    'special'       => '',
    'global_score'  => false,
    'status'        => 'publish'
);
?>