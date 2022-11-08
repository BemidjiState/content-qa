<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validation of links (<A> tags).
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Basic_Cleanup extends BSU_Base_Module {







	/**
	 * The constructor for this class.
	 *
	 * @param DOMDocument $dom The DOMDocument object to inspect.
	 * @param array       $args Args that may be passed to the class.
	 */
	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		$this->remove_all_empty_tags();

	}







	/**
	 * Summary of the function.
	 *
	 * @since 1.0.0
	 *
	 * @link Void elements https://www.w3.org/TR/2011/WD-html-markup-20110113/syntax.html#syntax-elements
	 *
	 * @return void No error output. Quietly remove empty tags for the user.
	 */
	public function remove_all_empty_tags() {

		$xpath = new DOMXPath( $this->dom );

		// Ignored tags that will always be empty (aka void elements).
		$ignored_tags = array(
			'area',
			'base',
			'br',
			'col',
			'hr',
			'img',
			'input',
			'link',
			'meta',
			'param',
			'command',
			'keygen',
			'source',
			'td', // Allow this since some cells may be empty for structure.
		);

		// Create the string of ignored tags for the XPath query.
		$ignored_tags_query = '';
		foreach ( $ignored_tags as $tag ) {
			$ignored_tags_query .= ' and not(name()="' . $tag . '")';
		}

		// Get a list of nodes that contain only spaces and do not have child nodes.
		$each_node = $xpath->query( '//*[not(*) and not(text()[normalize-space()])' . $ignored_tags_query . ']' );

		// Loop through the found nodes. Remove all except the ignored tag listed in the array.
		foreach ( $each_node as $n ) {

			// No lenght means there are no children.
			if ( property_exists( $n, 'length' ) && 0 >= $n->length ) {
				$n->parentNode->removeChild( $n );
			}
		}
	}
}
