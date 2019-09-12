<?php
/**
 * Created by PhpStorm.
 * Date: 23/08/17
 * Time: 12:55 PM
 * Description : Self updater
 */

/**
 * Usage :
 * $updateConfig = [
 * 'currentVersion' => '1.0.1',
 * 'pluginMetaJsonUrl' => '---.json',
 * 'pluginFileName' => '---'
 * ];
 * require('self_update.php');
 */

define( 'THAV_PLUGIN_VERSION', $updateConfig['currentVersion'] );
define( 'THAV_PLUGIN_META_JSON_URL', $updateConfig['pluginMetaJsonUrl'] );
define( 'THAV_PLUGIN_FILE_NAME', $updateConfig['pluginFileName'] );

//Scheduler
register_activation_hook( __FILE__, 'thav_my_activation_helper' );

if ( ! function_exists( 'thav_my_activation_helper' ) ) {
	function thav_my_activation_helper() {
		if ( ! wp_next_scheduled( 'plugin_version_check_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'plugin_version_check_event' );
		}
	}
}
add_action( 'plugin_version_check_event', 'thav_check_plugin_version' );

if ( ! function_exists( 'thav_check_plugin_version' ) ) {
	function thav_check_plugin_version() {
		// do something every hour
		try {
			$file               = @file_get_contents( THAV_PLUGIN_META_JSON_URL );
			$latest_plugin_data = json_decode( $file, true );
			$plugin_dir_path    = plugin_dir_path( __FILE__ );
			$plugin_path        = $plugin_dir_path . THAV_PLUGIN_FILE_NAME . '.php';
			if ( $latest_plugin_data['version'] > THAV_PLUGIN_VERSION ) {
				// Download, extract zip, and activate.
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
				$new_file = "tmp_" . THAV_PLUGIN_FILE_NAME . "zip";
				if ( copy( $latest_plugin_data['download_url'], $new_file ) ) {
					$res = unzip_file( $new_file, dirname( $plugin_dir_path ) );
					if ( $res instanceof WP_Error ) {
						if ( ! is_plugin_active( $plugin_path ) ) {
							activate_plugin( $plugin_path );
						}
					}
					unlink( $new_file );
				} else {
					echo "Error while copying file...";
				}
			}
		} catch ( \Exception $e ) {
			echo "Failed to activate plugin" . $e->getMessage();
		}
	}
}

register_deactivation_hook( __FILE__, 'thav_my_deactivation' );

if ( ! function_exists( 'thav_my_deactivation' ) ) {
	function thav_my_deactivation() {
		wp_clear_scheduled_hook( 'plugin_version_check_event' );
	}
}