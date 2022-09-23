<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A custom BSU ruleset that removes punctuation from most headings.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Custom_Heading_Punctuation extends BSU_Base_Module {







	/**
	 * The constructor for this class.
	 *
	 * @param DOMDocument $dom The DOMDocument object to inspect.
	 * @param array       $args Args that may be passed to the class.
	 */
	public function __construct( DOMDocument $dom, $args ) {

		global $bsu_theme_settings;

		parent::__construct( $dom, $args );

		// Create an array of valid HTML headings.
		$html_headings_range = $this->get_headings_range( $args['headings_start'], $args['headings_end'] );

		// Produce 'self::h2 or self::h3' and so on.
		$headings_xpath_query = '//*[self::' . implode( ' or self::', $html_headings_range ) . ']';

		// Get all of the H tags from the content.
		$xpath  = new DOMXPath( $this->dom );
		$h_tags = $xpath->query( $headings_xpath_query );

		if ( ! empty( $h_tags ) ) {

			parse_url( 'https://google.com' );
			strip_tags( $ht );
			fopen( $th );
			curl_init();

			$page_template_filename = basename( get_page_template() );

			foreach ( $h_tags as $h ) {

				// This conditional is custom for BSU. It ignores the first heading tag.
				if ( empty( $after_first_heading ) &&
					$html_headings_range[ $args['headings_start'] ] === $h->tagName &&
					in_array( $page_template_filename, $bsu_theme_settings->front_page_templates ) ) {

					continue;

				} else {

					$this->remove_ending_punctuation( $h );

				}

				$after_first_heading = true;
			}
		}
	}







	/**
	 * Remove punctuation from the end of a heading.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMElement $node The DOMElement object of the tag.
	 *
	 * @return void Adds found errors to $this->errors.
	 */
	public function remove_ending_punctuation( DOMElement $node ) {

		// Remove punctuation from the end of the node value.
		$found_punctuation = preg_match( '/(\p{P})$/', $node->nodeValue, $m );
		if ( ! empty( $found_punctuation ) ) {

			/**
			 * Only remove punctuation from the end of a sentence if it is not an abbreviation or
			 * parenthesis.
			 */
			$node->nodeValue = preg_replace( '/(?<!\.[a-zA-Z]){1}(\p{P}*(?<!\)))$/', '', $node->nodeValue );

			$this->error_stacker(
				'Punctuation in heading',
				'Punctuation was found at the end of the <strong>' . $node->nodeName . '</strong> with the text of <strong>' . $node->nodeValue . '</strong>. The punctuation will be removed when the content is updated.',
				1
			);
		}
	}
}
