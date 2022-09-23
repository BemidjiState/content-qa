<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks for valid heading structure and tries to identify misuse of headings.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Heading_Structure extends BSU_Base_Module {







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

		$this->validate_heading_structure( $args['headings_start'], $args['headings_end'] );

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
	public function validate_heading_structure( int $headings_start, int $headings_end ) {

		$headings_range      = $this->get_headings_range( $headings_start, $headings_end );
		$html_headings_range = $this->get_headings_range( 1, 6 ); // Valid HTML range.

		/* Produce 'self::h2 or self::h3' and so on. */
		$headings_xpath_query = '//*[self::' . implode( ' or self::', $html_headings_range ) . ']';

		/* Get all of the H tags from the content. */
		$xpath          = new DOMXPath( $this->dom );
		$found_headings = $xpath->query( $headings_xpath_query );

		/**
		 * Check for any errors for each H tag that was found in the document. These headigs are
		 * oredered in the the order they appear in the text.
		 */
		foreach ( $found_headings as $h ) {

			/* Get just the level of the heading for comparison. (e.g. H2 becomes 2). */
			$current_h_num = intval( preg_replace( '/\D/', '', $h->nodeName ) );

			if ( empty( $previous_h_num ) ) {

				/**
				 * We are in the first iteration of the loop. So there is no previous heading.
				 */

				if ( $current_h_num !== $headings_start ) {

					$this->error_stacker(
						'Incorrect first heading',
						'Headings must start at H' . $headings_start . '. The first heading <strong>' . $h->nodeValue . '</strong> is using <strong>' . strtoupper( $h->tagName ) . '</strong>. <a target="_blank" href="https://www.w3.org/WAI/tutorials/page-structure/headings/">More about using headings</a>.',
						3
					);
				}
			} else {

				/**
				 * The previous heading is a parent of the current heading. Make sure the headings
				 * are only incremented by one depth (e.g. H2 to H3).
				 */
				$next_valid_child_h_num = ( $previous_h_num + 1 );
				if ( $previous_h_num < $current_h_num && $next_valid_child_h_num !== $current_h_num ) {

					$this->error_stacker(
						'Heading heirarchy is incorrect',
						'Headings are used to define content structure. The heading <strong>' . $h->nodeValue . '</strong> is an H' . $current_h_num . ' following an H' . $previous_h_num . '. <a target="_blank" href="https://www.w3.org/WAI/tutorials/page-structure/headings/">More about using headings.</a>.',
						3
					);
				}
			}

			/**
			 * Check the content following the heading in this loop. Make sure there is actual text
			 * content between headings. The loop below will itterate over each sibling of the
			 * current heading in the loop until it finds valid content, another heading, or it runs
			 * out of siblings to look for.
			 */
			unset( $sibling ); // Reset from previous itteration of the loop.
			$valid_section_content_found = false;
			$no_more_siblings            = false;
			while ( false === $valid_section_content_found && false === $no_more_siblings ) {

				/* Setup for comparing the next sibling. */
				if ( empty( $sibling ) ) {

					/**
					 * This is the first itteration of the loop. Sibling gets set when the loop
					 * runs. Get the first sibling of the heading tag.
					 */
					$sibling = $h->nextSibling;

				} elseif ( isset( $sibling->nextSibling ) ) {

					/* The sibling of the element that was checked in the last itteration. */
					$sibling = $sibling->nextSibling;

				}

				$accepted_as_content = array(
					'#text', // Text not inside an HTML element.
					'p',
					'ol',
					'ul',
				);

				/* Check if we have acceptable content after the heading. */
				if ( ! empty( $sibling ) && in_array( $sibling->nodeName, $accepted_as_content, true ) ) {

					if ( ! empty( trim( $sibling->nodeValue ) ) ) {
						$valid_section_content_found = true;
					}
				} elseif ( empty( $sibling ) ) {

					/**
					 * No sibling is found. This will usually happen when there is just a single
					 * heading and no content.
					 */
					$this->error_stacker(
						'No content found in a section',
						'No content was found after <strong>' . $h->nodeValue . '</strong>. Each section should have text content.',
						3
					);

				} elseif ( 'hr' === $sibling->nodeName ||
					( array_search( $sibling->nodeName, $headings_range, true ) && $h->nodeName >= $sibling->nodeName ) ) {

					/**
					 * If there is no sibling or another heading is found, then we can stop looking
					 * since this section has been completely looped throug. We haven't found any
					 * content and this is an empty section. It is a heading for nothing.
					 */
					$this->error_stacker(
						'No content found in a section',
						'No content was found between <strong>' . $h->nodeValue . '</strong> and the next <strong>' . strtoupper( $sibling->nodeName ) . '</strong>. Each section should have text content.',
						3
					);
				}

				/* There are no more siblings left to look at. */
				if ( empty( $sibling->nextSibling ) ) {
					$no_more_siblings = true;
				}
			}

			/* Set the previous heading number for the next itteration. */
			$previous_h_num = $current_h_num;
		}
	}
}
