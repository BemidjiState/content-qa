<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks if Heading length is great than 100 characters
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Heading_Length extends BSU_Base_Module {







	/**
	 * The constructor for this class.
	 *
	 * @param DOMDocument $dom The DOMDocument object to inspect.
	 * @param array       $args Args that may be passed to the class.
	 */
	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		$this->check_heading_length( $args['headings_start'], $args['headings_end'] );
	}







	/**
	 * Validate heading length and generate level 2 error message if length is greater than 100 characters
	 * or a level 3 error message if length is greater than 150 characters.
	 *
	 * The default start is H2 since themes/tools should usually add the H1 automatically.
	 *
	 * @since 1.0.0
	 *
	 * @param int $headings_start The number to start at in checks (e.g. H2).
	 * @param int $headings_end The number to end at in checks (e.g. H6).
	 *
	 * @return void Adds found errors to $this->errors.
	 */
	public function check_heading_length( int $headings_start, int $headings_end ) {

		// Create an array of valid HTML headings.
		$html_headings_range = $this->get_headings_range( $headings_start, $headings_end );

		// Produce 'self::h2 or self::h3' and so on.
		$headings_xpath_query = '//*[self::' . implode( ' or self::', $html_headings_range ) . ']';

		// Get all of the H tags from the content.
		$xpath  = new DOMXPath( $this->dom );
		$h_tags = $xpath->query( $headings_xpath_query );

		foreach ( $h_tags as $h ) {

			if ( strlen( $h->nodeValue ) > 150 ) {

				$this->error_stacker(
					'Heading Length Error',
					'This content contains a Heading longer that 150 characters. Headings longer than 150 characters are not allowed and should be 100 characters or less. Consider shortening any Headings longer than 100 characters.',
					3,
				);
			} else if ( strlen( $h->nodeValue ) > 100 ) {

				$this->error_stacker(
					'Heading Length Error',
					'This content contains a very long Heading. Headings should not be longer than 100 characters in length. Consider shortening any Headings longer than 100 characters.',
					2,
				);
			}
		}
	}
}
