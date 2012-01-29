=== MyArcadePlugin Lite - WordPress Arcade ===
Contributors: MyArcadePlugin
Donate link: http://myarcadeplugin.com/
Tags: WordPress Arcade, WP Arcade, WP Arcade Plugin, WPArcade, WPArcade Plugin, Flash Games, Mochi Media, Autoblog, Auto post, Games, Arcade Script, Arcade Plugin, Game Script, Arcade, Playtomic, Kongregate, FGD, FlashGameDistribution, HeyZap
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 2.40

Transform a boring WordPress Blog into an amazing WP Arcade Site using Mochi Media and other free flash games: http://myarcadeplugin.com/showcase/

== Description ==

MyArcadePlugin is able to transform a WordPress Blog into an Online Games Portal or just to enchance your existing Blog wiht an Acade Section. MyArcadePlugin brings you all the features an Arcade Script needs. This WP Arcade Plugin will embed games automatically, without theme editing! In addition, it is able to generate unique game descriptions automatically!

Add thousands of free flash games provided by Mochi Media with just TWO clicks:

1. Fetch Games
2. Publish Games

[youtube http://www.youtube.com/watch?v=d_6IFfI6V1Y]

= Do you want a free WP Arcade Theme? =
Here are some WP Arcade Themes that you can use to start right now to build your WordPress Arcade Portal: 

* <a href='http://myarcadeplugin.com/free-wp-arcade-theme-gallerygames/' title='WP Arcade Theme - GallerGane'>WP Arcade Theme - GalleryGames</a>
* <a href='http://myarcadeplugin.com/free-wp-arcade-theme-triqui//' title='WP Arcade Theme - Triqui'>WP Arcade Theme - Triqui</a>

FunGames Theme in Action

[youtube http://www.youtube.com/watch?v=wVBKTB-F_1A]
   
= MyArcadePlugin Features =
* Embed Flash Code into posts without code editing
* One-Click Game Feeding
* One-Click Game Publishing
* Publish games immediately
* Publish games time shifted - scheduled with individual time interval!
* Game Management (search, publish, delete) with pagination
* Option to embed games automatically
* Post templates with several placeholders to generate unique game descriptions
* Download Mochi Games automatically to your server
* Download Mochi Thumbs automatically to your server
* Delete game files when deleting a game post
* Import Flash Games (SWF Format)
* Option to reset the games database
* Multilingual Support - Easy translation to every language
* Compatible with Premium <a href='myarcadeblogthemes.com' title='WP Arcade Themes'>WP Arcade Themes</a>

= Additional MyArcadePlugin Pro Features: =
MyArcadePlugin Lite is a fully functional but limited version of <a href='http://exells.com/' title='WordPress Arcade Plugin'>MyArcadePlugin Pro</a>. Here are some of the features you will get with the premium version:

* Fetch Games from Playtomic
* Fetch Games from Kongregate
* Fetch Games from FlashGameDistribution
* Automated Game Fetching (WP Cron)
* Import IBPArcade Games 
* Import PHPBB Games
* Import ZIP Games
* Import SWF Games
* Import DCR Games
* Import Embed Codes Games
* Import Iframe Games
* Detect Game Dimensions Automatically (SWF, IBPArcade) 
* Grab Game Files From URL
* Edit Games Before Publishing 
* Save Mochi Scores 
* Save IBPArcade Scores 
* Leaderboard
* Save Highest Score Only 
* Ajaxed Category-Mapping 
* Save Games as drafts
* Allow Users To Import Games 
* Single Category Publishing 
* Automated Game Translation in 36 Languages
* CubePoints Support
* and a lot of more features..

MyArcadePlugin Pro is able to download and publish flash games from Mochi Media, Playtomich, Kongregate and FlashGameDistribution automatically. You do not need to lift a finger. There are over 40.000 games available that you can add to your site instantly!

<a href='http://myarcadeplugin.com/' title='WordPress Arcade Plugin'>Upgrade to the full version now »</a>

== Installation ==
= To Install: =

1. Download MyArcadePlugin 
2. Unzip the file into a folder on your hard drive
3. Upload `/myarcadeblog/` folder to the `/wp-content/plugins/` folder on your site
4. Visit your WordPress admin panel -> Plugins and activate MyArcadePlugin
5. Click on MyArcade -> Settings and setup the plugin

= Downloading Files =
- If you want to download games to your server, create a `games` folder: `/wp-content/games/`
  * CHMOD directory `/wp-content/games/` to 777
- If you want to download thumbnails to your server, then create a `thumbs` folder: `/wp-content/thumbs/`
  * CHMOD directory `/wp-content/thumbs/` to 777 

= Usage =
After the installation you have to setup the plugin. You can find a <a href='http://myarcadeplugin.com/documentation/' title='WP Arcade Plugin Documentation'>MyArcadePlugin setup and usage instructions with screenshots and video here »</a>

== Upgrade Notice == 

1. Deactivate MyArcadePlugin
2. Upload the version
3. Activate MyArcadePlugin

== Frequently Asked Questions ==

= Will MyArcadePlugin work with a 'standard' WordPress theme? =
Yes, the plugin doesn't require any theme changes when you use a standard coded theme.

= How MyArcadePlugin saves game data? =
The plugin adds the following custom fields to each game post:
<ul>
<li>mabp_description - holds the description of a game</li>
<li>mabp_instructions - holds game instructions</li>
<li>mabp_thumbnail_url - holds the complete url of the game thumb</li>
<li>mabp_swf_url - holds the url of the game embed file</li>
<li>mabp_screen1_url - screenshot 1 url</li>
<li>mabp_screen2_url - screenshot 2 url</li>
<li>mabp_screen3_url - screenshot 3 url</li>
<li>mabp_screen4_url - screenshot 4 url</li>
</ul>

= How can I display game stored game data? =
The most game data are handles automatically by the plugin but you are also able to display them separately to fit your theme. For example, to display the game description within the loop use:

<code>
<?php echo get_post_meta($post->ID, "mabp_description", true); ?>
</code>


= How can I embed the game code manually? =
To embed the flash code of a game manually, you can use this inside your WordPress loop:

<code>
<?php echo get_game($post->ID); ?>
</code>


= Can I use MyArcadePlugin on an existing WordPress Blog? =
Yes, but only with the Pro version. MyArcadePlugin Pro is able to add a gaming section on your existing blog within minutes. Take a look to our demonstration video:

[youtube http://www.youtube.com/watch?v=lN1Cb7VrS_o]

= Where can I buy Arcade Themes for MyArcadePlugin? =
Check <a href='http://exells.com'>MyArcadePlugin Theme Directory</a>

= Is MyArcadePlugin Lite compatible with MyArcadePlugin Pro? =
Yes, you can upgrade to the PRO version without any modifications! 
<a href='http://myarcadeplugin.com/' title='WordPress Arcade Plugin'>** Upgrade to the full version now » **</a>

== Screenshots ==

1. MyArcadePlugin Settings Panel
2. Mochi Media Settings
3. General Settings
4. Game Management
5. Game Import Form
6. Example Site

<a href='http://myarcadeplugin.com/' title='WordPress Arcade Plugin'>** Upgrade to the full version now » **</a>

== CHANGELOG ==

= Version 2.40 - 2011-12-21 =
  * Fix: Admin bar fix for WP 3.3
  * Fix: News url

= Version 2.30 - 2011-11-13 =
  * New: Dashboard
  * New: Admin Bar Menu
  * Removed HeyZap menu because HeyZap closed down his game distribution API

= Version 2.20 - 2011-11-01 =
 * New: Menu structure updated
 * Fix: Compatibility with Arcade Themes from MyArcadeBlogThemes.com
 * Checked: WP 3.3 compatibility

= Version: 2.11 - 2011-07-12 =
* Fixed: PHP Notices when WP is in debug mode
* Checked: WP 3.2 compatibility

= Version: 2.10 - 2011-06-23 =
* Fixed: Can't create categories
* Fixed: PHP Notices in WP Debug mode
* Fixed: Reset Feeded Games button
* Fixed: Can't add game screenshots

= Version: 2.00 - 2011-05-28 =
* New: Plugin renamed to MyArcadePlugin Lite
* New: Mochi Publisher ID will be added to the game code to get credits
* New: New look & feel of the settings page. Now, the setup will be more comfortable.
* New: Ajaxed game import module
* New: Pagination on manage games. Ability to browse the entire game catalog
* New: Option to embed flash code automatically (Makes the plugin compatible with all standard WordPress themes)
* New: Template to style the game posts and to generate unique content
* New: Alternative file_put_contents function
* New: Memory limit check before trying to change the value
* New: Gettext support to make translations easy
* New: Delete downloaded game files when deleting a game post 
* New: Game management system (delete, publish, destroy)
* Fixed: Renamed post meta's to avoid conflicts with other plugins (All In One Seo)
  
= Version: 1.8.2 - 2010-04-11 =
* Added Safe Mod check before changing settings
  
= Version: 1.8.1 - 2009-12-08 =
* Added second check for allow_url_open setting
* Fixed Mochimedia Feed URL (mochiads -> mochimedia)
  
= Version: 1.8 - 2009-09-22 =
* Added new categories: Education, Rhythm, Strategy
* Removed category: Highscore
* Added new check for duplicate games
* Added new custom field for content rating ("rating")
  
= Version: 1.7.1 - 2009-08-09 =
* Fixed Board Games and Dress-Up problem

= Version: 1.7 - 2009-07-26 =
* Added option to reset feeded games
* Added an import function for custom/individual games

= Version: 1.6 - 2009-07-15 =
* Bug fixing 
* Added max. allowed game width
  
= Version: v1.5 - 2009-07-01 =
* Game thumbnails will be shown when adding games to blog
* Check, if Feed has been downloaded successfully
* SWF file names will be decoded before storing (%20-problem)
* White spaces will be automatically removed from MochiadsURL and MochiadsID

= Version: 1.4 - 2009-06-20 =
* Added routines to check PHP Version and PHP Settings (JSON Support)

= Version: 1.3 - 2009-06-17 =
* Fixes

= Version: 1.2 - 2009-06-16 =
* Fixed some error messages.
  
= Version: v1.1 - 2009-06-15 =
* Added new download method for feed, games and thumbs. When allow_url_fopen is set to 0 the plugin will use cURL for downloading files.
    
= Version: 1.0 - 2009-06-14 =
* Initial Release