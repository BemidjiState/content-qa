<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates HTML content based on W3C HTML specification.
 *
 * While DOMDocument can do validation with the $dom->validate() function, it requires an external
 * file to be loaded (e.g. https://www.w3.org/TR/REC-html40/loose.dtd) which is not always an
 * option. Reasons could be due to firewall or other config settings that prevent loading files from
 * outside of the server.
 *
 * @uses DOMDocument
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Standard_HTML extends BSU_Base_Module {







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

		// Look for any element that has an ID attribute and the ID is not empty.
		$ids_xpath_query   = '//*[@id!=""]';
		$xpath             = new DOMXPath( $this->dom );
		$elements_using_id = $xpath->query( $ids_xpath_query );
		$checked_ids       = array(); // Prevents an ID from being checked more than once.

		foreach ( $elements_using_id as $element ) {

			// An xpath query for all elements that use the current element's ID.
			$current_id_xpath_query    = '//*[@id="' . $element->getAttribute( 'id' ) . '"]';
			$elements_using_current_id = $xpath->query( $current_id_xpath_query );
			$already_checked_this_id   = in_array( $element->getAttribute( 'id' ), $checked_ids, true );

			// If there is more than one element found and we have not already looked at this ID.
			if ( 1 < $elements_using_current_id->length && false === $already_checked_this_id ) {

				$this->error_stacker(
					'An ID attribute was used more than once',
					'Multiple HTML elements were found using the same <code>id</code> attribute of <strong>"' . $element->getAttribute( 'id' ) . '"</strong>. The <code>id</code> attribute is used to specify a unique name for an HTML element. You cannot have more than one element with the same <code>id</code>.',
					3,
				);
			}

			$checked_ids[] = $element->getAttribute( 'id' );
		}
	}
}
