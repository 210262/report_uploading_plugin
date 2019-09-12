<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Report_Tab_Csv_Uploader
 * @subpackage Report_Tab_Csv_Uploader/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Report_Tab_Csv_Uploader
 * @subpackage Report_Tab_Csv_Uploader/includes
 * @author     Shiwani
 */
class Report_Tab_Csv_Uploader_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'report-tab-csv-uploader',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
