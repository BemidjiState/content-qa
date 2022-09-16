<?php
/**
 * Checks raw HTML for common problems or misuse in HTML. This function is meant to be usable
 * outside of WordPress so only builtin PHP functions are used.
 *
 * @package bsu2021
 * @since 1.0.0
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Linting standards for WordPress are used here but we are expecting to not have WordPress
 * available. So here are some linter exclusions.
 */
// phpcs:disable WordPress.WP.AlternativeFunctions
// phpcs:disable WordPress.WP.GlobalVariablesOverride
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
// phpcs:disable WordPress.WhiteSpace.ControlStructureSpacing.BlankLineAfterEnd



/**
 * Validates HTML content for ADA compliance and some HTML validation.
 *
 * @uses DOMDocument
 *
 * @since 1.0.0
 */
class BSU_Images extends BSU_Base_Module {







	/**
	 * Validates the input HTML and sets an array of errors within the object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $dom A DOMDocument object.
	 * @param array  $args High level args passed to the class.
	 *     $args = [
	 *         'headings_start' => (int) The first number accepted for heading depth (e.g. 2 for an H2).
	 *         'headings_end'   => (int) The last number accepted for heading depth (e.g. 6 for an H6).
	 *     ].
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
					'The <strong>alt</strong> value must be descriptive enough to use as an appropriate replacement for the image. The alt attribute is missing for <strong>' . $img_name . '</strong>. <a target="_blank" href="https://www.w3.org/WAI/tutorials/images/">More about using the alternate text attribute</a>.',
					3,
				);
			}
		}
	}
}
