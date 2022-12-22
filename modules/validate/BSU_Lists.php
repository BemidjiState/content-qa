<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validation of lists with a single item or empty items. Checks for bad practice.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Lists extends BSU_Base_Module {







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

		$this->check_list_item_count();

		$this->check_list_item_content();
	}







	/**
	 * Determine if a list contains a single list item
	 *
	 * @since 1.0.0
	 */
	public function check_list_item_count() {

		$xpath = new DOMXPath( $this->dom );

		// Get the number of lists containing just a single list item.
		$single_item_list_count = $xpath->query( '(//ul|//ol|//dl)[count(./li|./dd|./dt) <= 1]' );

		if(count($single_item_list_count) > 0){
			$this->error_stacker(
				'List Item Count Error',
				'There is a list containing a single item. Single item lists are not allowed. Consider using a new paragraph instead.',
				3,
			);
		}
	}







	/**
	 * Determine if a list contains an empty list item
	 *
	 * @since 1.0.0
	 */
	public function check_list_item_content() {

		$xpath = new DOMXPath( $this->dom );

		// Get the number of lists that contain list items without content.
		$lists_with_empty_list_item_count = $xpath->query( '(//ul|//ol|//dl)[./li[string-length()=0]|./dd[string-length()=0]|./dt[string-length()=0]]' );

		if(count($lists_with_empty_list_item_count) > 0){
			$this->error_stacker(
				'List Item Count Error',
				'There is a list containing one or more items with no content.',
				3,
			);
		}
	}
}
