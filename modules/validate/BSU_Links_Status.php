<?php
/**
 * Validation of links (<A> tags).
 *
 * @package bsu2021
 * @since 1.0.0
 */

/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Linting standards for WordPress are used here but we are expecting to not have WordPress
 * available. So here are some linter exclusions.
 */
// phpcs:disable WordPress.WP.AlternativeFunctions
// phpcs:disable WordPress.WP.GlobalVariablesOverride
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
// phpcs:disable WordPress.WhiteSpace.ControlStructureSpacing.BlankLineAfterEnd

class BSU_Links_Status extends BSU_Base_Module {

	public function __construct( DOMDocument $dom, $args ) {

		parent::__construct( $dom, $args );

		$this->validate_links_status();

	}







	/**
	 * Checks headers of all HTTP URLs to make sure the links are valid.
	 *
	 * @since 1.0.0
	 *
	 * @return void Sets errors in the the object.
	 */
	public function validate_links_status() {

		// Acceptable HTTP status codes to consider valid.
		$valid_status = array(
			200, // OK.
			301, // Moved Permanently.
			302, // Found.
			303, // See Other.
			307, // Temporary Redirect.
			308, // Permanent Redirect.
		);

		$a_tags = $this->dom->getElementsByTagName( 'a' );
		foreach ( $a_tags as $a ) {

			$url = $a->getAttribute( 'href' );
			$ch  = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_HEADER, true ); // Include the headers.
			curl_setopt( $ch, CURLOPT_NOBODY, true ); // No need to load the body.
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 15 ); // Any more than 10 is suspicious.
			curl_setopt( $ch, CURLOPT_TIMEOUT, 14 ); // Seconds.
			curl_setopt( $ch, CURLOPT_USERAGENT, 'BSU Content Validator' );

			$response = curl_exec( $ch );

			$curl_http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE ); /* Will be 0 on fail. */
			if ( ! in_array( $curl_http_code, $valid_status, true ) ) {

				/* The URL is invalid/broken. */
				$this->error_stacker(
					'Broken link',
					'The link to <strong>' . $a->nodeValue . '</strong> may be broken: ' . $url . ' (HTTP code: ' . $curl_http_code . ')',
					2, // Setting this at 2 until we know that we can more reliably determine broken links.
				);
			}

			curl_close( $ch );
		}
	}
}
