<?php
/**
 * MyArcadePlugin default settings
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright (c) 2014, Daniel Bakovic
 * @license http://myarcadeplugin.com
 * @package MyArcadePlugin/Core/Settings
 */

// No direct access
if( !defined( 'ABSPATH' ) ) {
  die();
}

$default_theme = esc_sql('<p><div style="float:left;margin-right: 10px; margin-bottom: 10px;">%THUMB%</div>%DESCRIPTION% %INSTRUCTIONS%</p>');

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
    'cron_publish_limit' => 1,
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
    /* Translation Settings */
    'translation'   => 'none',
    'bingid'        => '',
    'bingsecret'    => '',
    'translate_to'  => 'en',
    'google_id'    => '',
    'google_translate_to' => 'en',
    'translate_fields' => array('description', 'instructions', 'tags'),
    'translate_games'  => array('mochi', 'fgd', 'fog', 'spilgames', 'kongregate', 'ibparcade')
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
    'language'      => 'en',
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
    'status'        => 'publish'
);

$myarcade_spilgames_default = array (
    'feed'          => 'http://publishers.spilgames.com/rss-3',
    'limit'         => '20',
    'thumbsize'     => '1',
    'player_api'    => false,
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
    'status'        => 'publish'
);

$myarcade_myarcadefeed_default = array (
    'feed1'          => 'http://games.myarcadeplugin.com/game_feed.xml',
    'feed2'          => 'http://www.2pg.com/myarcadeplugin_feed.xml',
    'feed3'          => '',
    'feed4'          => '',
    'feed5'          => '',
    'all_categories' => false,
);

$myarcade_bigfish_default = array(
    'username'        => '',
    'affiliate_code'  => '',
    'locale'          => 'en',
    'gametype'        => 'og',
    'template'        => '%DESCRIPTION% %BULLET_POINTS% %BUY_GAME% %SYSREQUIREMENTS%',
    'thumbnail'       => 'medium',
    'create_cats'     => true,
    'cron_publish'    => false,
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

$myarcade_unityfeeds_default = array (
    'feed'          => 'http://unityfeeds.com/feed/',
    'category'      => 'all',
    'thumbnail'     => '100x100',
    'screenshot'    => '300x300',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
);
?>