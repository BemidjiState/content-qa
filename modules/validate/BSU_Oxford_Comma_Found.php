<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- The filename is used by autoloader.
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validation of Oxford comma rule.
 *
 * @package content-qa
 * @since 1.0.0
 */
class BSU_Oxford_Comma_Found extends BSU_Base_Module {







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

		$this->check_for_existing_oxford_commas();
	}







	/**
	 * Removes comma before and if one exists in a series of three or more terms
	 *
	 * @since 1.0.0
	 */
	public function check_for_existing_oxford_commas() {

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
		$each_node = $xpath->query( '(//dl|//li|//p|//h1|//h2|//h3|//h4|//h5|//h6|//td|//th|//div|//cite|//figure|//caption|//span|//a|//legend)' );

		$default_oxford_comma_error = 'It looks like this content may contain Oxford (Serial) commas. To keep content standardized it is best to avoid using Oxford commas. Please remove any commas placed immediately after the last term in a series of three or more terms.
			<p>
				For example, "My usual breakfast is coffee, bacon, and eggs" should be "My usual breakfast is coffee, bacon and eggs".
			<br />
				<a href="https://en.wikipedia.org/wiki/Serial_comma" target="_blank">For more info on Oxford (Serial) commas read this Wikipedia article</a>.
			</p>';

		$oxford_comma_text_arr = array();

		// Loop through the found nodes. Remove all except the ignored tag listed in the array.
		foreach ( $each_node as $n ) {

			$str = $n->nodeValue;

			$matches = array();
			if ( preg_match( '/([0-9a-z ]+, ?+){2,}(and|or) +[0-9a-z]+/mi', $str, $matches ) ) {

				foreach ( $matches as $match ) {

					array_push( $oxford_comma_text_arr, $str );
				}
			}
		}

		// Remove any duplicate strings found through the xpath query and added to this array as a result.
		$oxford_comma_text_arr = array_unique( $oxford_comma_text_arr );

		// If a string was added to the array "oxford_comma_text_arr", display an error.
		if ( count( $oxford_comma_text_arr ) >= 1 ) {

			$this->error_stacker( 'Oxford Comma Error', $default_oxford_comma_error, 2 );

			foreach ( $oxford_comma_text_arr as $oxford_comma_text ) {

				// highlight where the Oxford comma might exists.
				$oxford_comma_text = str_replace( ', and', '<span class="bsu-highlight-color">, and</span>', $oxford_comma_text );
				$oxford_comma_text = str_replace( ',and', '<span class="bsu-highlight-color">,and</span>', $oxford_comma_text );

				$this->error_stacker( 'Oxford Comma Error', 'An Oxford comma may exist in "<strong>' . $oxford_comma_text . '</strong>" and should possibly be removed.', 2 );
			}
		}
	}
}
