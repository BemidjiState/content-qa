<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates IMG tags, checking for ADA compliance.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Images extends BSU_Base_Module {







	/**
	 * Validates the input HTML and sets an array of errors within the object.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMDocument $dom A DOMDocument object.
	 * @param array       $args High level args passed to the class.
	 */
	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		$this->validate_image_structure();
	}







	/**
	 * Validates the IMG tag. Checks for alt tag.
	 *
	 * @since 1.0.0
	 */
	public function validate_image_structure() {

		// Check that IMG tags all have an alt property that is not empty.
		$img_tags = $this->dom->getElementsByTagName( 'img' );
		foreach ( $img_tags as $img ) {

			$alt_attr = $img->getAttribute( 'alt' );
			if ( empty( $alt_attr ) ) {

				/* More info on alt attr: https://html.spec.whatwg.org/multipage/images.html#alt */

				$img_name = basename( $img->getAttribute( 'src' ) );
				$this->error_stacker(
					'Alt tag is missing',
					'The alt attribute is missing for <strong>' . $img_name . '</strong>. The <strong>alt</strong> value must be descriptive enough to use as an appropriate replacement for the image. <a target="_blank" href="https://www.w3.org/WAI/tutorials/images/">Learn about using the alternate text attribute</a>.',
					3,
				);
			}
		}
	}
}
