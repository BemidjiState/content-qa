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
class BSU_List_Items extends BSU_Base_Module {







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

		$this->check_list_item_count2();
	}







	/**
	 * Determine if a list contains a single list item
	 *
	 * @since 1.0.0
	 */
	public function check_list_item_count2() {

		$xpath = new DOMXPath( $this->dom );

		$throw_list_item_count_error = false;
		foreach ($xpath->query( '//*[name()="ul" or name()="ol"]' ) as $n){
			$list_item_count = 0;
			foreach($n->childNodes as $list_item){
				if($list_item->nodeName == 'li'){
					$list_item_count++;
				}
			}

			if($list_item_count<=1){
				$throw_list_item_count_error = true;
			}
		}

		if($throw_list_item_count_error){
			$this->error_stacker(
				'List Item Count Error ',
				'There is a list containing a single item. Single item lists are not allowed. Consider using a new paragraph instead.',
				3,
			);
		}
	}
}
