<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Report_Tab_Csv_Uploader
 * @subpackage Report_Tab_Csv_Uploader/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Report_Tab_Csv_Uploader
 * @subpackage Report_Tab_Csv_Uploader/admin
 * @author     Shiwani
 */
class Report_Tab_Csv_Uploader_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Report_Tab_Csv_Uploader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Report_Tab_Csv_Uploader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/report-tab-csv-uploader-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Report_Tab_Csv_Uploader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Report_Tab_Csv_Uploader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/report-tab-csv-uploader-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_menu_page() {
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Report Tab import post', 'report-tab-import-post' ),
			__( 'Report Tab import post', 'report-tab-import-post' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_admin_page' )
		);
	}


	public function add_category_metabox() {
//		add_meta_box('category_images', // meta box ID
//			'Images', // meta box title
//			function () {
//
//			}, // callback function that prints the meta box HTML
//			null, // post type where to add it
//			'normal', // priority
//			'high' ); // position
		add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
		add_action( 'category_edit_form_fields', array( $this, 'category_edit_form_fields' ) );
		add_action( 'category_add_form_fields', array( $this, 'category_edit_form_fields' ) );
		add_action( 'edited_category', array( $this, 'rt_save_category_fields' ), 10, 2 );
		add_action( 'create_category', array( $this, 'rt_save_category_fields' ), 10, 2 );

	}

	function rt_save_category_fields( $term_id ) {
		if ( isset( $_POST['rt_category_image_ids'] ) ) {
			update_term_meta( $term_id, 'rt_category_image_ids', esc_attr( $_POST['rt_category_image_ids'] ) );
		}
	}

	function category_edit_form_fields( $term ) {
		if ( ! empty( $term->term_id ) ) {
			$ids = get_term_meta( $term->term_id, 'rt_category_image_ids', true );
		}
		?>
        <tr class="form-field">
            <img src="" id="image-tag">
            <input type="hidden" value="<?php echo $ids ?>" id="rt_category_image_ids" name="rt_category_image_ids"/>
            <th valign="top" scope="row">
                <label><?php _e( 'Images', '' ); ?></label>
            </th>
            <td>
                <input id="category_add_images" type="button" value="Open" >
                <br><br><br>
            </td>

        </tr>

        <script>
            jQuery(document).ready(function () {
                var addButton = document.getElementById('category_add_images');
                //var deleteButton = document.getElementById('fjern-bilde');
                var img = document.getElementById('image-tag');
                var hidden = document.getElementById('rt_category_image_ids');
                var imageUploader = wp.media({
                    title: 'Images',
                    button: {
                        text: 'Add Images'
                    },
                    multiple: 'add'
                });

                addButton.addEventListener('click', function () {
                    if (imageUploader) {
                        imageUploader.open();
                    }
                });

                imageUploader.on('open', function () {
                    var selection = imageUploader.state().get('selection');
                    ids = jQuery('#rt_category_image_ids').val().split(',');
                    ids.forEach(function (id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                });

                imageUploader.on('close', function () {
                    //var attachment = imageUploader.state().get('selection').first().toJSON();
                    var attachment = imageUploader.state().get('selection');
                    var ids = attachment.map(function (attachment) {
                        return attachment.id;
                    });
                    hidden.setAttribute('value', ids.join(','));
                });

                imageUploader.on('select', function () {
                    //var attachment = imageUploader.state().get('selection').first().toJSON();
                    var attachment = imageUploader.state().get('selection');
                    var ids = attachment.map(function (attachment) {
                        return attachment.id;
                    });
                    hidden.setAttribute('value', ids.join(','));
                });
            });
        </script>
		<?php
	}

	public function display_admin_page() {
		include_once 'partials/report-tab-csv-uploader-admin-display.php';
	}

}
