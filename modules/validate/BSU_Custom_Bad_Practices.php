<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates HTML content, attempting to find common bad practices.
 *
 * @uses DOMDocument, DOMXPath
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Custom_Bad_Practices extends BSU_Base_Module {







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

		$this->detect_fake_headings();
	}







	/**
	 * Attempt to find the use of fake headings (e.g. a P tag with bold text).
	 *
	 * @since 1.0.3
	 */
	public function detect_fake_headings() {

		// Check all P tags that are on their own and have short word counts and bold text.
		$p_tags = $this->dom->getElementsByTagName( 'p' );

		foreach ( $p_tags as $tag ) {

			if ( property_exists( $tag->childNodes, 'length' ) && 1 === $tag->childNodes->length &&
				'strong' === $tag->childNodes->item( 0 )->nodeName &&
				12 > $this->word_count( $tag->textContent ) ) {

				$this->error_stacker(
					'Detected bold text as headings',
					'It looks like you may be trying to use the text <strong>"' . $tag->textContent . '"</strong> as a heading but it is formatted as a Paragraph. If this text is meant to be a heading, it needs to be formatted as a Heading. <a target="_blank" href="https://www.w3.org/WAI/tutorials/page-structure/headings/">Learn about using headings.</a>.',
					2
				);
			}
		}
	}
}
