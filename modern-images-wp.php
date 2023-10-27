<?php
/**
 * Modern Images WP.
 *
 * Enables activating alternate image formats as the default for image sub-sizes
 * created when uploading images and used for front end display.
 *
 * @wordpress-plugin
 * Plugin Name:       Modern Images WP
 * Plugin URI:        https://plugins.wordpress.org/modern-images-wp
 * Description:       Choose a default format for subsized images. Choose WebP, JPGXL or AVIF when your server image library supports them.
 * Version:           1.2.0
 * Requires at least: 5.8
 * Requires PHP:      5.6
 * Author:            adamsilverstein
 * Author URI:        https://github.com/adamsilverstein
 * License:           Apache License 2.0
 * License URI:       https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain:       modern-images-wp
 */

/* This file must be parseable by PHP 5.2. */

/**
 * Loads the plugin.
 *
 * @since 1.0.0
 */
function modern_images_wp_load() {
	$src_dir = plugin_dir_path( __FILE__ ) . 'src/';

	require_once $src_dir . 'Plugin.php';
	require_once $src_dir . 'Setting.php';

	call_user_func( array( 'Modern_Images_WP\Plugin', 'load' ), __FILE__ );
}

add_action( 'plugins_loaded', 'modern_images_wp_load' );
