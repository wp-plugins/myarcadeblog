<?php
/*
 * Module:       Default Settings
 * Author:       Daniel Bakovic
 * Author URI:   http://myarcadeplugin.com
 */
$default_theme = mysql_real_escape_string('<p><div style="float:left;margin-right: 10px; margin-bottom: 10px;">%THUMB%</div>%DESCRIPTION% %INSTRUCTIONS%</p>');

$myarcade_general_default = array (
    'scores'        => false,
    'highscores'    => false,
    'posts'         => '20',
    'status'        => 'publish',
    'schedule'      => '60',
    'down_thumbs'   => false,
    'down_games'    => false,
    'down_screens'  => false,
    'delete'        => false,
    'folder_structure' => '%game_type%/%alphabetical%/',
    'automated_fetching' => false,
    'interval_fetching' => 'hourly',
    'automated_publishing' => false,
    'interval_publishing' => 'daily',
    'create_cats'   => true,
    'parent'        => '',
    'firstcat'      => false,
    'single'        => false,
    'singlecat'     => '',
    'max_width'     => '',
    'embed'         => 'manually',
    'template'      => $default_theme,
    'use_template'  => false,
    'allow_user'    => false,
    'limit_plays'   => '3',
    'limit_message' => 'Please register to play more games!',
    'play_delay'    => '30',
    'post_type'     => 'post',
    'custom_category' => '',
    'custom_tags'   => '',
    'featured_image' => false,
    'disable_game_tags' => false,
    'swfobject' => false,
    'translation'   => 'none',
    'bingid'        => '',
    'bingsecret'    => '',
    'translate_to'  => 'en',
    'google_id'    => '',
    'google_translate_to' => 'en',
    'translate_fields' => array('description', 'instructions', 'tags'),
    'translate_games'  => array('mochi', 'fgd', 'fog', 'spilgames', 'kongregate', 'ibparcade')
);

$myarcade_mochi_default = array (
    'feed'          => 'http://feedmonger.mochimedia.com/feeds/query/',
    'feed_save'     => '',
    'default_feed'  => 'old',
    'publisher_id'  => '',
    'secret_key'    => '',
    'limit'         => '200',
    'tag'           => '',
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
    'special'       => '',
    'global_score'  => false,
    'status'        => 'publish'
);

$myarcade_kongregate_default = array (
    'feed'          => 'http://www.kongregate.com/games_for_your_site.xml',
    'cron_publish'  => false,
    'cron_publish_limit' => '1'
);

$myarcade_fgd_default = array (
    'feed'          => 'http://flashgamedistribution.com/feed',
    'cid'           => '',
    'hash'          => '',
    'autopost'      => false,
    'limit'         => '50',
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
    'status'        => 'publish'
);

$myarcade_fog_default = array (
    'feed'          => 'http://www.freegamesforyourwebsite.com/feeds/games/',
    'limit'         => '20',
    'thumbsize'     => 'small',
    'screenshot'    => true,
    'tag'           => 'all',
    /*'create_cat'    => false,*/
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
    'status'        => 'publish'
);

$myarcade_spilgames_default = array (
    'feed'          => 'http://publishers.spilgames.com/rss-2',
    'limit'         => '20',
    'thumbsize'     => '1',
    'language'      => 'default',
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
    'status'        => 'publish'
);

$myarcade_myarcadefeed_default = array (
    'feed1'          => 'http://games.myarcadeplugin.com/game_feed.xml',
    'feed2'          => '',
    'feed3'          => '',
    'feed4'          => '',
    'feed5'          => ''
);

$myarcade_bigfish_default = array(
    'username'        => '',
    'affiliate_code'  => '',
    'locale'          => 'en',
    'gametype'        => 'og',
    'template'        => '%DESCRIPTION% %BULLET_POINTS% %BUY_GAME% %SYSREQUIREMENTS%',
    'thumbnail'       => 'medium',
    'cron_publish'  => false,
    'cron_publish_limit' => '1'
);

$myarcade_scirra_default = array(
    'feed'            =>  'http://www.scirra.com/arcade/game-list.xml',
    'thumbnail'       =>  'medium',
    'cron_publish'  => false,
    'cron_publish_limit' => '1'
);

$myarcade_gamefeed_default = array (
    'status'        => 'publish',
    'cron_publish'  => false,
    'cron_publish_limit' => '1'
);