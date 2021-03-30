<?php
/**
 * WordPres Modern Images.
 *
 * Enables activating alternate image formats as the default for image sub-sizes
 * created when uploading images and used for front end display.
 *
 * @wordpress-plugin
 * Plugin Name: WordPress Modern Images
 * Plugin URI:  https://plugins.wordpress.org/wordpress-modern-images
 * Description: Choose a default modern image format.
 * Version:     1.0.0
 * Author:      adamsilverstein
 * License:     Apache License 2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: wordpress-modern-images
 */

/* This file must be parseable by PHP 5.2. */

/**
 * Loads the plugin.
 *
 * @since 1.0.0
 */
function wordpress_modern_images_load() {
	$src_dir = plugin_dir_path( __FILE__ ) . 'src/';

	require_once $src_dir . 'Plugin.php';
	require_once $src_dir . 'Setting.php';

	call_user_func( array( 'WordPress_Modern_Images\Plugin', 'load' ), __FILE__ );
}


add_action( 'plugins_loaded', 'wordpress_modern_images_load' );
