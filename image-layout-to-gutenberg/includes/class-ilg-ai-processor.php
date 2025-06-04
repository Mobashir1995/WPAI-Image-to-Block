<?php
/**
 * ILG_AI_Processor class.
 *
 * Handles interaction with the AI service for layout conversion.
 *
 * @package Image_Layout_To_Gutenberg
 * @since 0.1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ILG_AI_Processor
 */
class ILG_AI_Processor {

	/**
	 * The path to the uploaded image file.
	 *
	 * @var string
	 */
	private $image_path;

	/**
	 * Plugin asset URL.
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Constructor.
	 *
	 * @param string $image_path Path to the uploaded image file.
	 * @param string $plugin_url Base URL for the plugin assets.
	 */
	public function __construct( $image_path, $plugin_url ) {
		$this->image_path = $image_path;
		$this->plugin_url = $plugin_url;
	}

	/**
	 * Processes the image with the AI service and returns Gutenberg blocks.
	 *
	 * This is currently a stub and returns sample block data.
	 *
	 * @return array An array of Gutenberg block structures (as strings or arrays).
	 */
	public function get_gutenberg_blocks() {
		// Simulate AI processing based on image path or content.
		// In a real scenario, this would involve:
		// 1. Reading the image file.
		// 2. Sending it to an AI API endpoint.
		// 3. Receiving and parsing the API response.
		// 4. Formatting the response into Gutenberg block syntax.

		$file_name = basename( $this->image_path );

		// Example: Return different blocks based on a keyword in the filename
		if ( strpos( $file_name, 'profile' ) !== false ) {
			return $this->get_profile_blocks();
		} elseif ( strpos( $file_name, 'gallery' ) !== false ) {
			return $this->get_gallery_blocks();
		} else {
			return $this->get_default_blocks();
		}
	}

	/**
	 * Returns sample profile layout blocks.
	 *
	 * @return array
	 */
	private function get_profile_blocks() {
		return array(
			'<!-- wp:columns -->
			<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
			<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:image {"align":"center","id":null,"sizeSlug":"large","linkDestination":"none"} -->
			<figure class="wp-block-image aligncenter size-large"><img src="' . esc_url( $this->plugin_url . 'assets/placeholder-profile.png' ) . '" alt="Placeholder Profile"/></figure>
			<!-- /wp:image --></div>
			<!-- /wp:column -->

			<!-- wp:column {"width":"66.67%"} -->
			<div class="wp-block-column" style="flex-basis:66.67%"><!-- wp:heading -->
			<h2>Contact Name</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>This is a paragraph describing the contact. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
			<!-- /wp:paragraph -->

			<!-- wp:social-links -->
			<ul class="wp-block-social-links"><!-- wp:social-link {"service":"twitter"} /-->
			<!-- wp:social-link {"service":"linkedin"} /-->
			<!-- wp:social-link {"service":"instagram"} /--></ul>
			<!-- /wp:social-links --></div>
			<!-- /wp:column --></div>
			<!-- /wp:columns -->',
		);
	}

	/**
	 * Returns sample gallery layout blocks.
	 *
	 * @return array
	 */
	private function get_gallery_blocks() {
		// For a real gallery, you'd need image IDs from the media library.
		// These would typically be uploaded first, or chosen by the AI.
		return array(
			'<!-- wp:gallery {"linkTo":"none"} -->
			<figure class="wp-block-gallery has-nested-images columns-default is-cropped"><!-- wp:image {"id":null,"linkDestination":"none"} -->
			<figure class="wp-block-image size-large"><img src="' . esc_url( $this->plugin_url . 'assets/placeholder-image.png' ) . '" alt="Placeholder 1"/></figure>
			<!-- /wp:image -->

			<!-- wp:image {"id":null,"linkDestination":"none"} -->
			<figure class="wp-block-image size-large"><img src="' . esc_url( $this->plugin_url . 'assets/placeholder-image.png' ) . '" alt="Placeholder 2"/></figure>
			<!-- /wp:image -->

			<!-- wp:image {"id":null,"linkDestination":"none"} -->
			<figure class="wp-block-image size-large"><img src="' . esc_url( $this->plugin_url . 'assets/placeholder-image.png' ) . '" alt="Placeholder 3"/></figure>
			<!-- /wp:image --></figure>
			<!-- /wp:gallery -->',
		);
	}

	/**
	 * Returns default sample blocks.
	 *
	 * @return array
	 */
	private function get_default_blocks() {
		return array(
			'<!-- wp:paragraph -->
			<p>AI processing complete (stubbed). This is a default block generated for "' . esc_html( basename( $this->image_path ) ) . '".</p>
			<!-- /wp:paragraph -->',
			'<!-- wp:image {"align":"center","id":null,"sizeSlug":"large","linkDestination":"none"} -->
			<figure class="wp-block-image aligncenter size-large"><img src="' . esc_url( $this->plugin_url . 'assets/placeholder-image.png' ) . '" alt="Processed Image Placeholder"/></figure>
			<!-- /wp:image -->',
		);
	}
}
