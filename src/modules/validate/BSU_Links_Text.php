<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validation of text within A tags. Checks for bad practice.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Links_Text extends BSU_Base_Module {







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

		$a_tags = $this->dom->getElementsByTagName( 'a' );

		foreach ( $a_tags as $a ) {

			$this->check_for_full_urls( $a );
			$this->check_for_click_here( $a );

		}
	}







	/**
	 * Determine if the link text is a full URL (e.g. https://blah.com/the/page/) instead of
	 * incorporating it in the URL.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMElement $a The DOMElement object of the tag.
	 */
	public function check_for_full_urls( DOMElement $a ) {

		// Do this because a trailing space could be rendered as a non-standard space.
		$trim_chars = ' \n\r\t\v\x00\\' . chr( 0xc2 ) . chr( 0xa0 );
		$trimmed_node_value = trim( $a->textContent, $trim_chars );

		$text_is_url = filter_var( $trimmed_node_value, FILTER_VALIDATE_URL );
		if ( false !== $text_is_url ) {

			$this->error_stacker(
				'The URL is not descriptive',
				'The linked text <strong>"' . $a->textContent . '"</strong> is not descriptive. Links should indicate relevant information about the link target. <a target="_blank" href="https://www.w3.org/WAI/tips/writing/#make-link-text-meaningful">More about making meaningful links</a>.',
				3,
			);
		}
	}







	/**
	 * Determine if the link text says "click here"
	 *
	 * @since 1.0.0
	 *
	 * @param DOMElement $a The DOMElement object of the tag.
	 */
	public function check_for_click_here( DOMElement $a ) {

		// Try to prevent vague links that say click here/this or just the text here/this.
		$text_contains_click_here = preg_match( '/\bclick(ing)?\b|( )?(this|here|link)[[:punct:]]?$/i', $a->textContent );

		if ( ! empty( $text_contains_click_here ) ) {

			$this->error_stacker(
				'A link says "click here"',
				'The linked text <strong>"' . $a->textContent . '"</strong> is too vague. Use link text that is helpful to your reader. Helpful link text indicates relevant information about the link target. <a target="_blank" href="https://www.w3.org/WAI/tips/writing/#make-link-text-meaningful">How to make link text meaningful</a>.',
				3,
			);
		}
	}
}
