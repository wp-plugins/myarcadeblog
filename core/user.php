<?php
/*
Module:       This modul contains MyArcadePlugin user functions
Author:       Daniel Bakovic
Author URI:   http://myarcadeplugin.com
*/

defined('MYARCADE_VERSION') or die();

/**
 * Shows MyArcade menu on the admin bar (Only for WP 3.1 and above)
 * @global <type> $wp_admin_bar
 * @global $wpdb $wpdb
 * @return <type>
 */
function myarcade_bar_menu() {
  global $wp_admin_bar;

  if ( !function_exists('is_admin_bar_showing') ) 
    return;
  
  if ( !is_super_admin() || !is_admin_bar_showing() ) 
    return;
  
  $id = 'myarcade_bar';
  
  /* Add the main siteadmin menu item */
  $wp_admin_bar->add_menu( array('id' => $id, 'title' => 'MyArcade',      'href' => admin_url( 'admin.php?page=arcadelite-dashboard') ) );
  $wp_admin_bar->add_menu( array('id' => 'fetch-games',  'parent'  => $id, 'title' => 'Fetch Games',   'href' => admin_url('admin.php?page=arcadelite-feed-games') ) );
  $wp_admin_bar->add_menu( array('id' => 'import-games', 'parent'  => $id, 'title' => 'Import Games',  'href' => admin_url('admin.php?page=arcadelite-import-games') ) );
  $wp_admin_bar->add_menu( array('id' => 'publish-games', 'parent'  => $id, 'title' => 'Publish Games', 'href' => admin_url('admin.php?page=arcadelite-add-games-to-blog') ) );    
  $wp_admin_bar->add_menu( array('id' => 'manage-games', 'parent'  => $id, 'title' => 'Manage Games',  'href' => admin_url('admin.php?page=arcadelite-manage-games') ) );
  $wp_admin_bar->add_menu( array('id' => 'myarcade-settings', 'parent'  => $id, 'title' => 'Settings',      'href' => admin_url('admin.php?page=arcadelite-edit-settings') ) );
}
?>