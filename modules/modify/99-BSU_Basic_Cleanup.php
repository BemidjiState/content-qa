<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

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
		$this->remove_multiple_spaces();
	}







	/**
	 * Check for empty HTML tags and remove them if they have no children or text content.
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

			// No length means there are no children.
			// property_exists( $n, 'length' ) was tried but doesn't seem to do anything and it does cause the condition not to work.
			if ( is_object( $n ) && 0 >= $n->length ) {

				$n->parentNode->removeChild( $n );
			}
		}
	}







	/**
	 * Removes any multiple spaces in the content being evaluated.
	 *
	 * @since 1.0.0
	 *
	 * @link Void elements https://www.w3.org/TR/2011/WD-html-markup-20110113/syntax.html#syntax-elements
	 *
	 * @return void No error output. Quietly remove multiple spaces between text for the user.
	 */
	public function remove_multiple_spaces() {

		$xpath = new DOMXPath( $this->dom );

		// Ignored tags that will always be empty (aka void elements).
		$ignored_tags = array(
			'area',
			'base',
			'br',
			'hr',
			'img',
			'input',
			'link',
			'meta',
			'param',
			'command',
			'keygen',
			'source',
		);

		// Create the string of ignored tags for the XPath query.
		$ignored_tags_query = '';
		foreach ( $ignored_tags as $tag ) {

			$ignored_tags_query .= ' and not(name()="' . $tag . '")';
		}

		// Get a list of nodes that contain only spaces and do not have child nodes.
		$each_node = $xpath->query( '//*[not(*)' . $ignored_tags_query . '] | //text()' );

		foreach ( $each_node as $n ) {

			if ( is_object( $n ) && property_exists( $n, 'nodeValue' ) ) {

				$str = $n->nodeValue;
				$str = preg_replace( '/\h+/u', ' ', $str );
              
              	/**
                 * Note that if you are doing anything with encoding some issues have been seen with ampersands and greater/less than. Avoid doing
                 * anything with encoding in Content QA. Instead handle that outside of the class. Something like htmlspecialchars() may provide
                 * the needed results.
                 */
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$n->nodeValue = $str;
			}
		}
	}
}
