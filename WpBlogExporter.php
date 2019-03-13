<?php
/*
Plugin Name:  WpBlogExporter
Plugin URI:   https://gerard1987.github.io/
Description:  Creates a blog post with the user input, inserts the post in the local and target site database. 
              Appends or creates category for the post, creates user for the author, or appends the post to existing user.
Version:      0.1
Author:       Gerard de Way
Author URI:   https://gerard1987.github.io/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
Shortcode Syntax: Default:[textload] || [textload type="textone"] || [textload type="texttwo"] || [telnr] || [pagename] [pagename type="location"]
*/

// Plugin updater
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/gerard1987/WpBlogExporter.git',
	__FILE__,
	'WpBlogExporter'
);

// Plugin registration
register_activation_hook( __FILE__, array( 'WpBlogExporter', 'install' ) );

// Define global constants
$plugin_dir = plugin_dir_path( __FILE__ );


// Includes
include_once $plugin_dir . 'includes/options_page.php';
include_once $plugin_dir . 'includes/check_site.php';
include_once $plugin_dir . 'includes/insert_post.php';
include_once $plugin_dir . 'includes/db_write_post_data.php';
include_once $plugin_dir . 'includes/category_list.php';

require_once( wp_normalize_path(ABSPATH).'wp-load.php');

/**
 * Init functionality if user is logged in
 */
class wp_blog_exporter
{
    private  $is_user_logged_in;

    // Plugin install
    static function install() {
        // do not generate any output here
    }

    public function __construct(){

        $options_page = new options_page();

        // Check if user is logged in
        add_action('init', function(){
            $this-> is_user_logged_in = is_user_logged_in();
            
            $insert_post = new insert_post();
            $data = $_POST;
            $create_post = $insert_post->create_post($data);
        });
    }
} // end of class

// create Instance's
$wp_blog_exporter = new wp_blog_exporter();


?>