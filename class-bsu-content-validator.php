<?php
/**
 * Prepares the HTML for validation done within child classes. Also provides a way to return an
 * array of error messages.
 *
 * Note: You may need to use apply_filters( 'the_content', $html ) in WordPress. This way all of the
 * tags that get added to the content will be added (e.g. <p>) and can be checked.
 *
 * Right now this is just included with the theme. Track this separately once there is a need for it
 * outside of the theme.
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
class BSU_Content_QA {

	/**
	 * The type of modules to be run. Can be validation, modification, or all.
	 *
	 * @var string
	 */
	public $mode;

	/**
	 * A temporary ID that gets applied to a temporary HTML element for wrapping HTML that does not
	 * have an <html> or <body> tag.
	 *
	 * @var string
	 */
	public $temp_wrapper_id;

	/**
	 * The heading level to start at.
	 *
	 * @var int
	 */
	protected $headings_start;

	/**
	 * The heading level to end at.
	 *
	 * @var int
	 */
	protected $headings_end;

	/**
	 * All modules found in the 'modify' and 'validate' directories.
	 *
	 * @var array
	 */
	private $modules_discovered;

	/**
	 * All modules names to skip.
	 *
	 * @var array
	 */
	private $modules_disabled = array();

	/**
	 * The DOMDocument that can possibly be modified.
	 *
	 * @var object
	 */
	protected $dom;

	/**
	 * The originally loaded DOMDocument.
	 *
	 * @var object
	 */
	protected $dom_original;

	/**
	 * The number of errors.
	 *
	 * @var int
	 */
	public $errors_count;

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
	 * Any errors from when the HTML is loaded by DOMDocument. Output is from libxml_get_errors().
	 *
	 * @var array
	 */
	public $libxml_errors;







	/**
	 * Construct the BSU_Content_validador class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html The HTML to validate.
	 * @param array  $args High level args passed to the class.
	 *     $args = [
	 *         'headings_start'   => (int) The first number accepted for heading depth (e.g. 2 for an H2).
	 *         'headings_end'     => (int) The last number accepted for heading depth (e.g. 6 for an H6).
	 *         'par_limit'        => (int) The maximum allowed paragraphs when checking paragraph count.
	 *         'word_limit'       => (int) The maximum allowed words when checking word count.
	 *         'modules_disabled' => (array) Modules/class names to ignore.
	 *         'modules'          => [ (array) The names and paths of modules to be used within the class.
	 *             'modify'   => (array) Modules that modify content.
	 *                 'Module_Class_Name' (string) The class name of modules to be used.
	 *             'validate' =>
	 *                 'Module_Class_Name' (string) The class name of modules to be used.
	 *              ]
	 *         ]
	 *     ].
	 *
	 * @throws Exception If a DOMDocument object cannot be created.
	 */
	public function __construct( string $html, array $args = array() ) {

		$this->mode               = $this->get_mode( $args );
		$this->modules_discovered = $this->discover_modules();

		if ( array_key_exists( 'modules_disabled', $args ) && is_array( $args['modules_disabled'] ) ) {
			$this->modules_disabled = $args['modules_disabled'];
		}

		// Set default args and validate any passed args.
		$this->headings_start = $this->get_headings_start( $args );
		$this->headings_end   = $this->get_headings_end( $args );

		// Load the HTML and create a DOMDocument object.
		$html_encoded = $this->encode_html( $html );

		// Attempt to create the DOMDocument object if we have a string to deal with.
		if ( false !== $html_encoded ) {

			// This DOMDocument can be modified.
			$this->dom = $this->create_dom_object( $html_encoded );

			// This DOMDocument will remain untouched.
			$this->dom_original = $this->create_dom_object( $html_encoded );
		}

		// Ensure we have an object from loading the DOM.
		if ( ! is_object( $this->dom ) ) {

			// DOMDocument::loadHTML() failed or it returned an empty object.
			$this->errors = array(
				'title'   => 'Unable to create DOMDocument object',
				'message' => 'Unable to create a DOMDocument object to be validated. Possibly no content was passed for validation.',
				'level'   => 1,
			);

		} else {

			// Autoload any modules as they are used.
			spl_autoload_register( array( $this, 'module_auto_loader' ) );

			// Check if args were passed for modules. Otherwise default to all modules by default.
			if ( array_key_exists( 'modules', $args ) && is_array( $args['modules'] ) ) {
				$modules_to_run = $args['modules'];
			} else {
				$modules_to_run = $this->modules_discovered;
			}

			// Check for disabled modules before trying to run the list.
			$modules_to_run = $this->clean_modules_to_run_list( $modules_to_run, $this->modules_disabled );

			// Args to pass along to each individual module.
			$module_args = $this->compile_module_args( $args );

			$module_output = $this->run_modules( $modules_to_run, $module_args );

			// Update properties after all modules have run.
			$this->errors       = $module_output['errors'];
			$this->errors_count = count( $this->errors );
			if ( array_key_exists( 'dom_modified', $module_output ) ) {
				$this->dom = $module_output['dom_modified'];
			}
		}
	}







	/**
	 * Summary of the function.
	 *
	 * Optional expanded description of the function, can include uses or formatting information.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args All of the args passed to the class.
	 *
	 * @return array $module_args The selected args that we want to pass to all modules.
	 */
	private function compile_module_args( $args ) {

		// Set these all even though not all args may be passed. The missing ones will render as null.
		$module_args = array(
			'headings_start' => $this->headings_start,
			'headings_end'   => $this->headings_end,
			'par_limit'      => ( isset( $args['par_limit'] ) ? $args['par_limit'] : null ),
			'word_limit'     => ( isset( $args['word_limit'] ) ? $args['word_limit'] : null ),
		);

		return $module_args;
	}







	/**
	 * A callback for spl_autoload_register to load modules on an as needed basis.
	 *
	 * @see https://www.php.net/manual/en/function.spl-autoload-register.php
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_name The class of the submodule being called.
	 */
	private function module_auto_loader( string $module_name ) {

		// Only load existing modules for the validator.
		foreach ( $this->modules_discovered as $module_group => $modules ) {

			if ( array_key_exists( $module_name, $modules ) ) {

				// Set the module path from the discovered modules.
				$module_path = $modules[ $module_name ];
				break;
			}
		}

		// All new classes will be passed here first, so check if the file exists.
		if ( isset( $module_path ) && file_exists( $module_path ) ) {
			require_once $module_path;
		}
	}







	/**
	 * Re-encodes the HTML as needed for use within the DOMDocument.
	 *
	 * If only $html is provided it will default to encoding UTF-8 text to HTML-ENTITIES. So special
	 * characters are converted to HTML entities (e.g. &mdash;).
	 *
	 * @since 1.0.0
	 *
	 * @param string $html The HTML to inspect.
	 * @param string $to_encoding The type of encoding that $html is being converted to.
	 * @param string $from_encoding Is specified by character code names before conversion.
	 *
	 * @return string $encoded_html The HTML converted to specified encoding.
	 */
	protected function encode_html( string $html, $to_encoding = 'HTML-ENTITIES', $from_encoding = 'UTF-8' ) {

		$trimmed_html = trim( $html );
		$encoded_html = mb_convert_encoding( $trimmed_html, $to_encoding, $from_encoding );

		if ( empty( $encoded_html ) ) {

			$encoded_html = false;

		}

		return $encoded_html;
	}






	/**
	 * Create a DOMDocument object with the HTML to be inspected.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html The entire HTML document or section of HTML to check.
	 *
	 * @return object $dom_object A DOMDocument object with the HTML loaded in to it.
	 */
	protected function create_dom_object( $html ) {

		$dom_object = new DOMDocument();

		/**
		 * This is because tags like FIGCAPTION will return errors even though it is valid HTML. If
		 * we handle errors on our own it will still move forward with creating the DOM object.
		 */
		libxml_use_internal_errors( true );

		/**
		 * Generate a random ID for a temporary wrapper. This temporary wrapper is used since
		 * DOMDocument object decides to use the first HTML tag to wrap the entire HTML. By
		 * giving it a temporary wrapper, unexpected results from saveHTML() are avoided.
		 */
		$found_wrapper = preg_match( '/(<html>)|(<body>)/', $html );
		if ( empty( $found_wrapper ) ) {
			$rand_id = uniqid( 'bsu_validator_tmp_wrapper_' );
			$html    = '<div id="' . $rand_id . '">' . $html . '</div>';
		}

		// Load the HTML without creating doctype or html/body tags if they are missing.
		$html_loaded = $dom_object->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		if ( empty( $html_loaded ) ) {
			// Something went wrong if we are in here.
			$dom_object = false;
		}

		if ( isset( $rand_id ) ) {
			// Share the temp wrapper id with the new DOMDocument object for later use.
			$dom_object->temp_wrapper_id = $rand_id;
		}

		// Get any errors from when the HTML was loaded.
		$this->libxml_errors = libxml_get_errors();
		libxml_clear_errors();

		return $dom_object;
	}







	/**
	 * Set the value for checking valid heading start within the class.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception When an invalid heading value is provided.
	 *
	 * @param array $args The args array.
	 *
	 * @return int $headings_start The heading number where headings can start.
	 */
	protected function get_headings_start( $args ) {

		if ( array_key_exists( 'headings_start', $args ) ) {

			if ( $args['headings_start'] < 1 || $args['headings_start'] > 6 ) {
				throw new Exception( 'An int between 1 and 6 was expected for $args[headings_start].' );
			} else {
				$headings_start = (int) $args['headings_start'];
			}
		} else {

			// Starting at H2 since in most cases the H1 is already set programatically.
			$headings_start = 2;

		}

		return $headings_start;
	}







	/**
	 * Set the value for checking valid heading end within the class.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception When an invalid heading value is provided.
	 *
	 * @param array $args The args array.
	 *
	 * @return int $headings_end The heading number where headings can end.
	 */
	protected function get_headings_end( $args ) {

		if ( array_key_exists( 'headings_end', $args ) ) {

			if ( $args['headings_end'] < 1 || $args['headings_end'] > 6 ) {
				throw new Exception( 'An int between 1 and 6 was expected for $args[headings_end].' );
			} elseif ( $args['headings_end'] < $args['headings_start'] ) {
				throw new Exception( '$args[headings_end] cannot be less than $args[heading_start]' );
			} else {
				$headings_end = (int) $args['headings_end'];
			}
		} else {

			$headings_end = 6;

		}

		return $headings_end;
	}







	/**
	 * Detect all modules in the modules directory and create an array of all module names.
	 *
	 * @since 1.0.0
	 *
	 * @return array $all_modules All modules found in the modules directory.
	 */
	protected function discover_modules() {

		// Get all PHP file names from the module directory.
		$module_files_modify = glob( __DIR__ . '/modules/modify/*.php' );

		/**
		 * Loop through all file names and convert them to the respective class module name (e.g.
		 * BSU_Module_Name). Then add them to the array of modules to run for modification.
		 */
		foreach ( $module_files_modify as $key => $file_path ) {
			$module_filename = basename( $file_path );
			$module_name     = $this->filename_to_module_name( $module_filename );

			// Store the information for the current module in the loop.
			$all_modules['modify'][ $module_name ] = $file_path;
		}

		$module_files_validate = glob( __DIR__ . '/modules/validate/*.php' );

		/**
		 * Loop through all file names and convert them to the respective class module name (e.g.
		 * BSU_Module_Name). Then add them to the array of modules to run for validation.
		 */
		foreach ( $module_files_validate as $key => $file_path ) {
			$module_filename = basename( $file_path );
			$module_name     = $this->filename_to_module_name( $module_filename );

			// Store the information for the current module in the loop.
			$all_modules['validate'][ $module_name ] = $file_path;
		}

		return $all_modules;
	}







	/**
	 * Run the list of modules provided as an array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $modules The modules to run.
	 * @param array $args Args to pass along to each module.
	 */
	protected function run_modules( array $modules, array $args = array() ) {

		require_once __DIR__ . '/modules/class-bsu-base-module.php';

		// Init empty arrays to allow for easier merging when no errors are found.
		$module_reporting = array(
			'errors' => array(),
		);

		if ( 'only_validate' === $this->mode ) {

			// Do not run the modifying modules.
			unset( $modules['modify'] );

		} elseif ( 'only_modify' === $this->mode ) {

			// Do not run validation modules. Only run modules to cleanup.
			unset( $modules['validate'] );

		} elseif ( array_key_exists( 'modify', $modules ) ) {

			// Ensure that modules that modify are at the start of the array before looping.
			$modules_modify = array(
				'modify' => $modules['modify'],
			);
			unset( $modules['modify'] );

			// Place the modules array at the beginning of the array.
			$modules = array_merge( $modules_modify, $modules );

		}

		foreach ( $modules as $module_group ) {

			foreach ( $module_group as $module => $module_path ) {

				// $shared_dom is used so we can pass along the same DOMDocument to each module.
				if ( ! isset( $shared_dom ) ) {
					$shared_dom = $this->dom;
				}

				$m = new $module( $shared_dom, $args );

				if ( $m->errors_count > 0 ) {

					// Add the errors from the module to the main object.
					foreach ( $m->errors as $error ) {
						$module_reporting['errors'][] = $error;
					}
				}
			}
		}

		/**
		 * Store the final shared DOM only after all modules have run. It is possible that this is
		 * never changes. If modifications are made to the DOMDocument then this will be passed on
		 * to the property.
		 */
		$module_reporting['dom_modified'] = $shared_dom;

		return $module_reporting;
	}







	/**
	 * Convert a provided filename in to the expected class name of the module.
	 *
	 * The filename BSU_Some_Module.php would become BSU_Some_Module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename The filename of a PHP module.
	 *
	 * @return string $module_name The class name of a module.
	 */
	protected function filename_to_module_name( string $filename ) {

		$patterns = array(
			'/^class\-/', // The class- prefix.
			'/\.php$/', // The PHP file extension.
			'/[0-9]*\-/',
			'/-/', // Underscores to become dashes.
		);

		$replaces = array(
			'', // Replace class- prefix.
			'', // Replace .php extension.
			'', // Replace 00- priority prefix.
			'_', // Replace dashes.
		);

		$module_name = preg_replace( $patterns, $replaces, $filename );

		return $module_name;
	}







	/**
	 * Get the HTML string from the DOMDocument.
	 *
	 * Note: DOMDocument may add extra HTML tags in the output of saveHTML().
	 *
	 * @since 1.0.0
	 *
	 * @param bool $get_original_dom Set to TRUE to get original HTML.
	 *
	 * @return string $html The HTML string from the DOMDocument.
	 */
	public function get_html( $get_original_dom = false ) {

		$html = null;

		// Check if a flag to get the original, unmodified DOM was passed.
		if ( true === $get_original_dom && is_object( $this->dom_original ) ) {
			$dom_to_use = $this->dom_original;
		} elseif ( is_object( $this->dom ) ) {
			$dom_to_use = $this->dom;
		}

		if ( isset( $dom_to_use ) && is_object( $dom_to_use ) ) {

			if ( isset( $dom_to_use->temp_wrapper_id ) ) {

				$temp_wrapper = $dom_to_use->getElementById( $dom_to_use->temp_wrapper_id );

				if ( $temp_wrapper->hasChildNodes() ) {

					foreach ( $temp_wrapper->childNodes as $node ) {
						$html .= $dom_to_use->saveHTML( $node );
					}
				}
			} else {

				/**
				 * This should be an unmodified DOMDocument or one that has a wrapper (i.e. <html> or
				 * <body>).
				 */
				$html = $dom_to_use->saveHTML();
			}
		}

		return $html;
	}







	/**
	 * Summary of the function.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $get_original_dom Set to TRUE to get original DOM.
	 *
	 * @return object $dom_version The DOMDocument object.
	 */
	public function get_dom( $get_original_dom = false ) {

		if ( true === $get_original_dom && is_object( $this->dom_original ) ) {
			$dom_version = $this->dom_original;
		} elseif ( is_object( $this->dom ) ) {
			$dom_version = $this->dom;
		} else {
			$dom_version = null;
		}

		return $dom_version;
	}







	/**
	 * Return the mode that the validator is currently running in.
	 *
	 * Optional expanded description of the function, can include uses or formatting information.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The args array.
	 *
	 * @return string $mode The mode that the class is running in.
	 */
	public function get_mode( $args ) {
		if ( array_key_exists( 'only_validate', $args ) && true === $args['only_validate'] ) {
			$mode = 'only_validate';
		} elseif ( array_key_exists( 'only_modify', $args ) && true === $args['only_modify'] ) {
			$mode = 'only_modify';
		} else {
			$mode = 'all_modules';
		}

		return $mode;
	}







	/**
	 * Checks for disabled modules in the provided array of modules to run and returns a clean list
	 * with disabled modules removed from the array.
	 *
	 * The $modules_list arg expects the same structure as the $modules_discovered property. So an
	 * associative array with 'modify' and 'validate' sub arrays.
	 *
	 * @since 1.0.0
	 *
	 * @param array $modules_list The main list of modules to check.
	 * @param array $modules_disabled The list of individual modules to be disabled.
	 *
	 * @return array $modules_list The filtered modules list after checking for disabled modules.
	 */
	private function clean_modules_to_run_list( array $modules_list, array $modules_disabled ) {

		foreach ( $modules_list as $module_group => $module_group_modules ) {

			foreach ( $modules_disabled as $disabled_module ) {

				if ( array_key_exists( $disabled_module, $modules_list[ $module_group ] ) ) {

					unset( $modules_list[ $module_group ][ $disabled_module ] );

				}
			}
		}

		return $modules_list;
	}
}
