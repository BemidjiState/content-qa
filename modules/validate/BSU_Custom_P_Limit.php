<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A custom BSU rule for consistancy in content. This is not ADA. It is specifically to validate
 * content is entered as expected.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Custom_P_Limit extends BSU_Base_Module {







	/**
	 * Validates the input HTML and sets an array of errors within the object.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMDocument $dom A DOMDocument object.
	 * @param array       $args High level args passed to the class.
	 *     $args = [
	 *         'par_limit' => (int) The paragraph limit to check against.
	 *     ].
	 */
	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		if ( $args['par_limit'] > 0 ) {

			$this->limit = intval( $args['par_limit'] );
			$this->check_p_tags_count( intval( $args['par_limit'] ) );

		}
	}







	/**
	 * Check the number of P tags found.
	 *
	 * @global class $bsu_theme_settings A class for settings used in the BSU2021 theme.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit The maximum number of paragraph tags to allow.
	 */
	public function check_p_tags_count( int $limit ) {

		$p_tags = $this->dom->getElementsByTagName( 'p' );
		if ( $p_tags->length > $limit ) {

			$count_text = sprintf(
				ngettext( 'was %x paragraph', 'were %x paragraphs', $p_tags->length ),
				$p_tags->length
			);

			$limit_text = sprintf(
				ngettext( '%x paragraph is', '%x paragraphs are', $limit ),
				$limit
			);

			$this->error_stacker(
				'Too many paragraphs',
				"Only ${limit_text} allowed. There ${count_text} found in the content.",
				3,
			);
		}
	}
}
