=== Game Log ===
Contributors: lastsplash
Tags: games, gaming, video games, game tracking, 
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A  plugin for tracking video games you've played, are currently playing, or want to play.

== Description ==

Game Log is a powerful WordPress plugin designed for gamers who want to track and organize their video game collection. Whether you're a casual gamer or a serious collector, this plugin helps you keep track of games you've played, are currently playing, or want to play in the future.

= Key Features =

* **Custom Post Type**: Games are stored as a custom post type with detailed metadata
* **Game Status Taxonomy**: Organize games by status (Played, Playing, Backlog, Wishlist)
* **IGDB Integration**: Search and import games from the IGDB.com database with detailed information
* **Image Import**: Automatically download and set game cover images as featured images
* **Admin Interface**: Clean, intuitive admin interface following WordPress standards
* **Game Statistics Block**: Display your gaming statistics with a customizable Gutenberg block
* **Default Page Generation**: Automatically creates a beautiful game log page with block patterns

= Game Status Categories =

* **Played**: Games you have completed
* **Playing**: Games you are currently playing
* **Backlog**: Games you own but haven't started
* **Wishlist**: Games you want to play

= IGDB Integration =

The plugin integrates with the Internet Game Database (IGDB.com) to provide high-quality cover images. A free API key is required to use the plugin.
* Release dates, platforms, genres
* Developer and publisher information
* Game summaries and descriptions

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* IGDB API credentials (free registration required)

== Installation ==

1. Upload the `game-log` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Game Log' > 'Settings' to configure your IGDB API credentials
4. Start adding games to your collection!

= IGDB API Setup =

1. Visit [IGDB API](https://api.igdb.com/) to get your API credentials
2. Create a Twitch account and register your application
3. Get your Client ID and Client Secret
4. Enter these credentials in the Game Log settings page

== Frequently Asked Questions ==

= Do I need an IGDB API key? =

Yes, you need to register for a free IGDB API key to search and import games. The plugin uses IGDB's comprehensive database to provide detailed game information and cover images.

= Is there a limit to how many games I can track? =

No, there's no limit to the number of games you can track. The plugin uses WordPress's built-in post system, so it scales with your WordPress installation.

= Can I customize the game status categories? =

The plugin comes with four default status categories (Played, Playing, Backlog, Wishlist), but you can add custom statuses through the WordPress admin taxonomy interface.

= Does the plugin work with themes? =

Yes, the plugin is designed to work with any WordPress theme. It follows WordPress standards and uses blocks and patterns.

= Can I export my game data? =

The plugin stores games as WordPress posts, so you can use any WordPress export tool to backup your game data.

== Screenshots ==

== Changelog ==

= 1.0.0 =

* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Game Log plugin. No upgrade required.

== Support ==

For support, feature requests, or bug reports, please visit the plugin's support forum or contact the plugin author.

== Privacy Policy ==

This plugin does not collect, store, or transmit any personal data. Game data is stored locally in your WordPress database. The plugin only communicates with IGDB.com to fetch game information when you explicitly search for games.
