<?php
/**
 * The base class to be extended for creating new modules. These classes should be used within a
 * BSU_Content_QA class. They can be autoloaded as they are used by the class.
 *
 * @uses DOMDocument
 *
 * @package content-qa
 * @since 1.0.0
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates HTML content for ADA compliance and some HTML validation.
 *
 * @uses DOMDocument
 *
 * @since 1.0.0
 */
class BSU_Base_Module {

	/**
	 * The DOMDocument handed over to inspect.
	 *
	 * @var object
	 */
	public $dom;

	/**
	 * The number of errors.
	 *
	 * @var int
	 */
	public $errors_count = 0;

	/**
	 * The errors and error messages relating to problems found in the HTML.
	 *
	 * @var array $errors = [
	 *     'title'   => (string) The title/header of the error message.
	 *     'message' => (string) A message to be displayed to the user.
	 *     'level'   => (int) 1 (info), 2 (warning), 3 (danger).
	 * ]
	 */
	public $errors = array();







	/**
	 * The constructor for a basic module.
	 *
	 * Optional expanded description of the function, can include uses or formatting information.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMDocument $dom A DOMDocument object that will be inspected.
	 * @param array       $args The args to use within the class.
	 *     $args = [
	 *         'headings_start' => (string) The fir
	 *         'headings_end'   => (string)
	 *         'par_limit'      => (int) The paragraph count limit to check against.
	 *         'word_limit'     => (int) The word count limit to check against.
	 *     ].
	 */
	public function __construct( DOMDocument $dom, $args ) {

		$this->dom = $dom;

	}







	/**
	 * Place errors in to the error array to create the list of all errors found.
	 *
	 * This is being done as a method in case we want to change how errors are built at some point.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title The title. Can be used as header text or a class.
	 * @param string $message The messsage to be displayed to the user.
	 * @param int    $level 1 (info), 2 (warning), 3 (danger).
	 */
	protected function error_stacker( string $title, string $message, int $level ) {

		$this->errors[] = array(
			'title'   => $title,
			'message' => $message,
			'level'   => $level,
		);

		$this->errors_count = count( $this->errors );
	}







	/**
	 * Get an array of valid headings based on the start and end properties.
	 *
	 * Creates an associative array of NUM => TAG (i.e. 1 => H1).
	 *
	 * @since 1.0.0
	 *
	 * @param int $headings_start The first valid heading.
	 * @param int $headings_end The last valid heading.
	 *
	 * @return array $valid_headings Array of the range of heading provided.
	 */
	public function get_headings_range( $headings_start, $headings_end ) {

		/* Cast to int so calculations can be made with the array key. */
		$headings_start = (int) $headings_start;
		$headings_end   = (int) $headings_end;

		/**
		 * Create an array like array(2 => 'h2', 3 => 'h3') for using just numbers and just tag
		 * names.
		 */
		foreach ( range( $headings_start, $headings_end ) as $num ) {
			$valid_headings[ $num ] = 'h' . $num;
		}

		return $valid_headings;
	}







	/**
	 * Removes all attributes of an HTML element/node.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMElement $node The DOMElement node object of an HTML tag.
	 *
	 * @return object $node The modified node.
	 */
	public function remove_node_attributes( DOMElement $node ) {

		/* Check for HTML attributes. */
		if ( true === $node->hasAttributes() ) {

			/* Indiscriminately remove all attributes. It ensures the original styling is preserved. */
			while ( true === $node->hasAttributes() ) {
				$node->removeAttributeNode( $node->attributes->item( 0 ) );
			}

			$this->error_stacker(
				'Attributes removed',
				'The <strong>' . strtoupper( $node->nodeName ) . '</strong> with the text of <strong>' . $node->nodeValue . '</strong> contains HTML attributes (e.g. id, class, style). Attributes cannot be used on this element and are removed when the content is updated.',
				1
			);
		}

		return $node;
	}







	/**
	 * Removes all inner HTML elements, leaving just the text value of the element being checked.
	 *
	 * @since 1.0.0
	 *
	 * @param DOMElement $node The DOMElement node object of an HTML tag.
	 *
	 * @return object $node The modified node.
	 */
	public function remove_node_inner_html( DOMElement $node ) {

		/* Check for inner HTML. */
		if ( true === $node->hasChildNodes() ) {

			$has_nodes_to_remove = false;
			foreach ( $node->childNodes as $child ) {

				// Search until we find a non text node.
				if ( 3 !== $child->nodeType ) {
					$has_nodes_to_remove = true;
					break;
				}
			}

			if ( true === $has_nodes_to_remove ) {

				$node_name = strtoupper( $node->nodeName );
				$node_text = $node->nodeValue;

				// Remove all child nodes.
				while ( 0 < $node->childNodes->length ) {
					$node->removeChild( $node->firstChild );
				}

				// Insert a new child node of node type #text.
				$new_text_node = $this->dom->createTextNode( $node_text );
				$node->appendChild( $new_text_node );
				unset( $new_text_node ); // Cleanup.

				$this->error_stacker(
					'Inner HTML removed',
					'The <strong>' . $node_name . '</strong> with the text of <strong>' . $node_text . '</strong> contains HTML elements. Inner HTML elements are removed when the content is updated.',
					1
				);
			}
		}

		return $node;
	}
}
