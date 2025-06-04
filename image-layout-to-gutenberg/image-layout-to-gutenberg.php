<?php
/**
 * Plugin Name:       Image Layout to Gutenberg
 * Plugin URI:        https://example.com/plugins/image-layout-to-gutenberg/
 * Description:       Creates Gutenberg blocks from an uploaded image layout using AI.
 * Version:           0.1.0
 * Author:            Your Name or Company
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       image-layout-to-gutenberg
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ILG_AI_PLUGIN_VERSION', '0.1.0' );
define( 'ILG_AI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ILG_AI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ILG_AI_PLUGIN_MAIN_FILE', __FILE__ );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
class Image_Layout_To_Gutenberg_AI {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      ILG_AI_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	// protected $loader; // Will create a loader class later if needed.

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		$this->plugin_name = 'image-layout-to-gutenberg'; // Text domain
		$this->version = ILG_AI_PLUGIN_VERSION;

		$this->load_dependencies(); // Ensure dependencies are loaded
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_ajax_hooks(); // Define AJAX hooks
		// $this->define_public_hooks(); // If any public facing features
		add_action( "enqueue_block_editor_assets", array( $this, "enqueue_block_editor_assets" ) );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {
		// require_once ILG_AI_PLUGIN_PATH . 'includes/class-ilg-ai-processor.php'; // Now loaded by ILG_AI_Admin class
		require_once ILG_AI_PLUGIN_PATH . 'admin/class-ilg-ai-admin.php';
		require_once ILG_AI_PLUGIN_PATH . 'includes/image-upload-handler.php'; // Load the AJAX handler
		include_once ILG_AI_PLUGIN_PATH . 'includes/ai-analyzer-interface.php'; // Load the AI Analyzer Interface
		include_once ILG_AI_PLUGIN_PATH . 'includes/mock-ai-analyzer.php'; // Load the Mock AI Analyzer
		// require_once ILG_AI_PLUGIN_PATH . 'includes/class-ilg-ai-loader.php'; // For a potential loader class
		// $this->loader = new ILG_AI_Loader();
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @since 0.1.0
	 * @access private
	 */
	private function define_ajax_hooks() {
		add_action( "wp_ajax_image_layout_upload", "ilg_handle_image_layout_upload" );
		// If you need nopriv for logged-out users (unlikely for this type of feature):
		// add_action( "wp_ajax_nopriv_image_layout_upload", "ilg_handle_image_layout_upload" );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$admin_handler = new ILG_AI_Admin( $this->get_plugin_name(), $this->get_version(), ILG_AI_PLUGIN_URL );

		// Hook admin menu creation
		add_action( 'admin_menu', array( $admin_handler, 'add_admin_menu' ) );

		// Hook enqueueing of styles and scripts
		add_action( 'admin_enqueue_scripts', array( $admin_handler, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $admin_handler, 'enqueue_scripts' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			$this->plugin_name,
			false,
			dirname( plugin_basename( ILG_AI_PLUGIN_MAIN_FILE ) ) . '/languages/'
		);
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Main execution of the plugin.
	 * @since    0.1.0
	 */
	public function run() {
		// $this->loader->run(); // If using a loader class
	}

    public function enqueue_block_editor_assets() {
        $plugin_url = plugin_dir_url( __FILE__ ); // Corrected to use __FILE__ relative to the main plugin file

        wp_enqueue_script(
            "image-layout-to-gutenberg-editor-script",
            $plugin_url . "assets/js/editor.js",
            array( "wp-plugins", "wp-edit-post", "wp-element", "wp-components", "wp-i18n", "wp-api-fetch" ), // Added wp-api-fetch
            $this->version, // Use the class property for version
            true
        );

        // Localize script with ajax_url and nonce
        wp_localize_script(
            "image-layout-to-gutenberg-editor-script",
            "imageLayoutUpload",
            array(
                "ajax_url" => admin_url( "admin-ajax.php" ),
                "nonce"    => wp_create_nonce( "image_layout_upload_nonce" )
            )
        );

        wp_enqueue_style(
            "image-layout-to-gutenberg-editor-style",
            $plugin_url . "assets/css/editor.css",
            array(),
            $this->version // Use the class property for version
        );
    }
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_ilg_ai_plugin() {
	$plugin = new Image_Layout_To_Gutenberg_AI();
	// $plugin->run(); // Not needed if not using a loader that registers hooks
}
run_ilg_ai_plugin();
