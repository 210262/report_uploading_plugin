<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://
 * @since             1.0.0
 * @package           Report_Tab_Csv_Uploader
 *
 * @wordpress-plugin
 * Plugin Name:       ReportTabCSVUploader
 * Plugin URI:        http://
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Test
 * Author URI:        http://
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       report-tab-csv-uploader
 * Domain Path:       /languages
 */


$updateConfig = [
	'currentVersion' => '1.0.2',
	'pluginMetaJsonUrl' => '',
	'pluginFileName' => 'report-tab-csv-uploader'
];
require('self_update.php');



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-report-tab-csv-uploader-activator.php
 */
function activate_report_tab_csv_uploader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-report-tab-csv-uploader-activator.php';
	Report_Tab_Csv_Uploader_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-report-tab-csv-uploader-deactivator.php
 */
function deactivate_report_tab_csv_uploader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-report-tab-csv-uploader-deactivator.php';
	Report_Tab_Csv_Uploader_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_report_tab_csv_uploader' );
register_deactivation_hook( __FILE__, 'deactivate_report_tab_csv_uploader' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-report-tab-csv-uploader.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_report_tab_csv_uploader() {

	$plugin = new Report_Tab_Csv_Uploader();
	$plugin->run();

}
run_report_tab_csv_uploader();
