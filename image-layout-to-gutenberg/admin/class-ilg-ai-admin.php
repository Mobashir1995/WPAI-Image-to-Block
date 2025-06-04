<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      0.1.0
 *
 * @package    Image_Layout_To_Gutenberg
 * @subpackage Image_Layout_To_Gutenberg/admin
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for managing the admin area.
 */
class ILG_AI_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The base URL for plugin assets.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_url    Base URL for plugin assets.
	 */
	private $plugin_url;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 * @param    string    $plugin_url The plugin assets URL.
	 */
	public function __construct( $plugin_name, $version, $plugin_url ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_url = $plugin_url; // Store plugin URL
	}

	/**
	 * Register the admin menu for the plugin.
	 *
	 * @since    0.1.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Image Layout to Gutenberg AI', 'image-layout-to-gutenberg' ),
			__( 'AI Layout Converter', 'image-layout-to-gutenberg' ),
			'manage_options',
			$this->plugin_name . '-admin', // menu slug should be based on plugin_name
			array( $this, 'display_admin_page' ),
			'dashicons-images-alt2',
			75
		);
	}

	/**
	 * Render the admin page content.
	 *
	 * @since    0.1.0
	 */
	public function display_admin_page() {
		// Handle file upload first if form is submitted
		if ( isset( $_POST['ilg_ai_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ilg_ai_nonce_field'] ) ), 'ilg_ai_upload_layout' ) ) {
			if ( ! empty( $_FILES['ilg_ai_image_upload']['name'] ) ) {
				$this->handle_layout_upload();
			} else {
				add_settings_error(
					$this->plugin_name . '-notices',
					'no_file_uploaded',
					__( 'No file was uploaded. Please select an image.', 'image-layout-to-gutenberg' ),
					'error'
				);
			}
		}

		settings_errors( $this->plugin_name . '-notices' ); // Display notices

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Upload an image of a layout to convert it into Gutenberg blocks using AI.', 'image-layout-to-gutenberg' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->plugin_name . '-admin' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'ilg_ai_upload_layout', 'ilg_ai_nonce_field' ); ?>

				<div>
					<label for="ilg_ai_image_upload"><?php esc_html_e( 'Layout Image:', 'image-layout-to-gutenberg' ); ?></label>
					<input type="file" id="ilg_ai_image_upload" name="ilg_ai_image_upload" accept="image/*" required />
				</div>
				<?php submit_button( __( 'Upload and Convert to Blocks', 'image-layout-to-gutenberg' ) ); ?>
			</form>

			<?php
			// Notices (including generated blocks from handle_layout_upload) are displayed by settings_errors() above.
			// No need for session-based display here if not using redirects.
			?>
		</div>
		<?php
	}

	/**
	 * Handle the layout image upload and processing.
	 *
	 * @since    0.1.0
	 */
	private function handle_layout_upload() {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$uploaded_file = $_FILES['ilg_ai_image_upload'];
		$upload_overrides = array( 'test_form' => false );

		$allowed_mime_types = array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
		);
		$file_info = wp_check_filetype( basename( $uploaded_file['name'] ), $allowed_mime_types );

		if ( ! $file_info['type'] ) {
			add_settings_error( $this->plugin_name . '-notices', 'invalid_file_type', __( 'Invalid file type. Please upload a JPG, PNG, or GIF image.', 'image-layout-to-gutenberg' ), 'error' );
			return;
		}
        if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
            add_settings_error( $this->plugin_name . '-notices', 'upload_error', __( 'File upload error: ', 'image-layout-to-gutenberg' ) . $uploaded_file['error'], 'error' );
            return;
        }

		$movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			require_once ILG_AI_PLUGIN_PATH . 'includes/class-ilg-ai-processor.php'; // Ensure this path is correct

			// ILG_AI_PLUGIN_URL is defined in the main plugin file.
			// We need to pass it or make it accessible here if ILG_AI_Processor needs it.
			// For now, assuming ILG_AI_Processor can get it if needed, or it's passed.
			$ai_processor = new ILG_AI_Processor( $movefile['file'], $this->plugin_url );
			$generated_blocks = $ai_processor->get_gutenberg_blocks();
			$blocks_html = implode( "\n\n", $generated_blocks );

			// Store blocks in session to display after redirect (or just display if no redirect)
			// Using add_settings_error is also an option if we don't want to use sessions.
			// For simplicity, let's try to display it on the same page load without sessions first.
			// The current structure displays errors/notices via settings_errors() at the top.
			// We can add the generated blocks to the success message.

			$success_message = sprintf(
				'%s <br/>- %s: %s <br/>- %s: %s <br/><br/><strong>%s:</strong><pre>%s</pre>',
				__( 'File uploaded successfully. AI Processing complete (stubbed).', 'image-layout-to-gutenberg' ),
				__( 'Path', 'image-layout-to-gutenberg' ), esc_html( $movefile['file'] ),
				__( 'URL', 'image-layout-to-gutenberg' ), esc_url( $movefile['url'] ),
				__( 'Generated Blocks (stubbed)', 'image-layout-to-gutenberg' ),
				esc_textarea( $blocks_html )
			);
			add_settings_error( $this->plugin_name . '-notices', 'file_uploaded_successfully', $success_message, 'success' );

		} else {
			add_settings_error( $this->plugin_name . '-notices', 'upload_failed', __( 'File upload failed: ', 'image-layout-to-gutenberg' ) . ( isset( $movefile['error'] ) ? $movefile['error'] : 'Unknown error' ), 'error' );
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {
		// Check if we are on our plugin's admin page.
		// The hook_suffix for a top-level page is 'toplevel_page_{menu_slug}'.
		if ( 'toplevel_page_' . $this->plugin_name . '-admin' === $hook_suffix ) {
			wp_enqueue_style(
				$this->plugin_name . '-admin-styles',
				$this->plugin_url . 'admin/css/ilg-ai-admin.css', // Use $this->plugin_url
				array(),
				$this->version,
				'all'
			);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		// Check if we are on our plugin's admin page.
		if ( 'toplevel_page_' . $this->plugin_name . '-admin' === $hook_suffix ) {
			wp_enqueue_script(
				$this->plugin_name . '-admin-scripts',
				$this->plugin_url . 'admin/js/ilg-ai-admin.js', // Use $this->plugin_url
				array( 'jquery' ),
				$this->version,
				true // Load in footer
			);
		}
	}
}
