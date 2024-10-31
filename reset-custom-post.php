<?php
/*
Plugin Name: Reset Custom Post
Plugin URI: https://wordpress.org/plugins/reset-custom-post
Description: The Reset custom post plugin deletes all data from a defined custom post, with the option of deleting images attached to the custom post type.
Version: 1.0.0
Author: MELIOZ.dev
Author URI: https://moezbettoumi.fr
License: GPLv2 or later
Text Domain: reset-custom-post
Domain Path: /languages
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'MLZRCP_VERSION', '1.0.0' );

function mlzrcp_enqueue_custom_scripts() {
    wp_enqueue_style('reset-custom-post-css', plugins_url('assets/css/styles.css', __FILE__), array(), MLZRCP_VERSION );
    wp_enqueue_script('reset-custom-post-js', plugins_url('assets/js/scripts.js', __FILE__), array('jquery'), MLZRCP_VERSION, true);
}
add_action('admin_enqueue_scripts', 'mlzrcp_enqueue_custom_scripts');

function mlzrcp_reset_custom_post_load_textdomain() {
    load_plugin_textdomain('reset-custom-post', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'mlzrcp_reset_custom_post_load_textdomain');

require_once(plugin_dir_path(__FILE__) . 'inc/admin-page.php');
require_once(plugin_dir_path(__FILE__) . 'inc/cleanup-functions.php');


