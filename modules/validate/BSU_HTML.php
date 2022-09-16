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
class BSU_HTML extends BSU_Base_Module {







	/**
	 * Validates the input HTML and sets an array of errors within the object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html The HTML to validate.
	 * @param array  $args High level args passed to the class.
	 *     $args = [
	 *         'headings_start' => (int) The first number accepted for heading depth (e.g. 2 for an H2).
	 *         'headings_end'   => (int) The last number accepted for heading depth (e.g. 6 for an H6).
	 *     ].
	 */
	public function __construct( $dom, $args ) {

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
