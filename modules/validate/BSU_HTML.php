<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates HTML content for ADA compliance and some HTML validation.
 *
 * @uses DOMDocument
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_HTML extends BSU_Base_Module {







	/**
	 * Validates the input HTML and sets an array of errors within the object.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMDocument $dom The HTML to validate.
	 * @param array       $args High level args passed to the class.
	 *     $args = [
	 *         'headings_start' => (int) The first number accepted for heading depth (e.g. 2 for an H2).
	 *         'headings_end'   => (int) The last number accepted for heading depth (e.g. 6 for an H6).
	 *     ].
	 */
	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		$this->identify_duplicate_ids();
	}







	/**
	 * Summary of the function.
	 *
	 * Optional expanded description of the function, can include uses or formatting information.
	 *
	 * @since 1.0.0
	 */
	public function identify_duplicate_ids() {

		// Look for IDs that are used more than once.

	}
}
