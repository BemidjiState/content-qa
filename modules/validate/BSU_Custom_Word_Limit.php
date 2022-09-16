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
 * Validates HTML content for word count limits.
 *
 * @uses DOMDocument
 *
 * @since 1.0.0
 */
class BSU_Custom_Word_Limit extends BSU_Base_Module {







	/**
	 * Validates the input HTML and sets an array of errors within the object.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMDocument $dom A DOMDocument object.
	 * @param array       $args High level args passed to the class.
	 *     $args = [
	 *         'headings_start' => (int) The first number accepted for heading depth (e.g. 2 for an H2).
	 *         'headings_end'   => (int) The last number accepted for heading depth (e.g. 6 for an H6).
	 *         'word_limit'     => (int) The word limit to check against.
	 *         'par_limit'      => (int) The paragraph limit to check against.
	 *     ].
	 */
	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		if ( array_key_exists( 'word_limit', $args ) && $args['word_limit'] > 0 ) {

			$this->check_word_count( intval( $args['word_limit'] ) );

		}
	}







	/**
	 * Checks the text content from the DOM object for word count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit The maximum number of words to allow.
	 */
	public function check_word_count( int $limit ) {

		// Trying to allow acceptions to match the WP word count on the classic editor.
		$word_count = str_word_count( $this->dom->textContent, 0, "'’0123456789" );

		if ( $word_count > $limit ) {

			$this->error_stacker(
				'Too many words',
				'The maximum number of words that can be used in the content of this page is ' . $limit . '. There were ' . $word_count . ' words found.',
				3,
			);
		}
	}
}
