<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates HTML content for word count limits.
 *
 * @package content-qa
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
	 *         'word_limit' => (int) The word limit to check against.
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

		$word_count = $this->word_count( $this->dom->textContent );

		if ( $word_count > $limit ) {

			$this->error_stacker(
				'Too many words',
				'The maximum number of words allowed in the content of this page is ' . $limit . '. There were ' . $word_count . ' words found.',
				3,
			);
		}
	}
}
