<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove all inner HTML elements from inside heading tags.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Heading_Inner_HTML extends BSU_Base_Module {







	/**
	 * The constructor for this class.
	 *
	 * @param DOMDocument $dom The DOMDocument object to inspect.
	 * @param array       $args Args that may be passed to the class.
	 */
	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		$this->remove_heading_attributes( $args['headings_start'], $args['headings_end'] );
	}







	/**
	 * Validate heading tags and generate error messages for any errors.
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
	public function remove_heading_attributes( int $headings_start, int $headings_end ) {

		// Create an array of valid HTML headings.
		$html_headings_range = $this->get_headings_range( $headings_start, $headings_end );

		// Produce 'self::h2 or self::h3' and so on.
		$headings_xpath_query = '//*[self::' . implode( ' or self::', $html_headings_range ) . ']';

		// Get all of the H tags from the content.
		$xpath  = new DOMXPath( $this->dom );
		$h_tags = $xpath->query( $headings_xpath_query );

		foreach ( $h_tags as $h ) {

			$this->remove_node_inner_html( $h );

		}
	}
}
