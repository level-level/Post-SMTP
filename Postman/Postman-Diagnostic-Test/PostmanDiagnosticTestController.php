<?php
class PostmanDiagnosticTestController {
	const DIAGNOSTICS_SLUG = 'postman/diagnostics';

	// logging
	private $logger;
	private $options;

	// Holds the values to be used in the fields callbacks
	private $rootPluginFilenameAndPath;

	/**
	 * Constructor
	 *
	 * @param mixed $rootPluginFilenameAndPath
	 */
	public function __construct( $rootPluginFilenameAndPath ) {
		assert( ! empty( $rootPluginFilenameAndPath ) );
		assert( PostmanUtils::isAdmin() );
		assert( is_admin() );

		$this->logger = new PostmanLogger( get_class( $this ) );
		$this->rootPluginFilenameAndPath = $rootPluginFilenameAndPath;
		$this->options = PostmanOptions::getInstance();

		// register the admin menu
		PostmanUtils::registerAdminMenu( $this, 'addDiagnosticsSubmenu' );

		// hook on the init event
		add_action( 'init', function () : void {
			$this->on_init();
		} );

		// initialize the scripts, stylesheets and form fields
		add_action( 'admin_init', function () : void {
			$this->on_admin_init();
		} );
	}

	/**
	 * 	 * Functions to execute on the init event
	 * 	 *
	 * 	 * "Typically used by plugins to initialize. The current user is already authenticated by this time."
	 * 	 * ref: http://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_a_Typical_Request
	 */
	public function on_init(): void {
		// register Ajax handlers
		new PostmanGetDiagnosticsViaAjax();
	}

	/**
	 * 	 * Fires on the admin_init method
	 */
	public function on_admin_init(): void {
				$this->registerStylesAndScripts();
	}

	/**
	 * 	 * Register and add settings
	 */
	private function registerStylesAndScripts(): void {
		if ( $this->logger->isTrace() ) {
			$this->logger->trace( 'registerStylesAndScripts()' );
		}

		// register the javascript resource
		$pluginData = apply_filters( 'postman_get_plugin_metadata', null );
		wp_register_script( 'postman_diagnostics_script', plugins_url( 'Postman/Postman-Diagnostic-Test/postman_diagnostics.js', $this->rootPluginFilenameAndPath ), array(
				PostmanViewController::JQUERY_SCRIPT,
				PostmanViewController::POSTMAN_SCRIPT,
		), $pluginData ['version'] );
	}

	/**
	 * 	 * Register the Diagnostics screen
	 */
	public function addDiagnosticsSubmenu(): void {
		$page = add_submenu_page( PostmanViewController::POSTMAN_MENU_SLUG, sprintf( __( '%s Setup', 'post-smtp' ), __( 'Postman SMTP', 'post-smtp' ) ), __( 'Diagnostic', 'post-smtp' ), Postman::MANAGE_POSTMAN_CAPABILITY_NAME, PostmanDiagnosticTestController::DIAGNOSTICS_SLUG, function () : void {
			$this->outputDiagnosticsContent();
		} );
		// When the plugin options page is loaded, also load the stylesheet
		add_action( 'admin_print_styles-' . $page, function () : void {
			$this->enqueueDiagnosticsScreenStylesheet();
		} );
	}
	function enqueueDiagnosticsScreenStylesheet(): void {
		wp_enqueue_style( PostmanViewController::POSTMAN_STYLE );
		wp_enqueue_script( 'postman_diagnostics_script' );
	}

	public function outputDiagnosticsContent(): void {
		// test features
		print '<div class="wrap">';

		PostmanViewController::outputChildPageHeader( __( 'Diagnostic Test', 'post-smtp' ) );

		printf( '<h4>%s</h4>', __( 'Are you having issues with Postman?', 'post-smtp' ) );
		/* translators: where %1$s and %2$s are the URLs to the Troubleshooting and Support Forums on WordPress.org */
		printf( '<p style="margin:0 10px">%s</p>', sprintf( __( 'Please check the <a href="%1$s">troubleshooting and error messages</a> page and the <a href="%2$s">support forum</a>.', 'post-smtp' ), 'https://wordpress.org/plugins/post-smtp/other_notes/', 'https://wordpress.org/support/plugin/post-smtp' ) );
		printf( '<h4>%s</h4>', __( 'Diagnostic Test', 'post-smtp' ) );
		printf( '<p style="margin:0 10px">%s</p><br/>', sprintf( __( 'If you write for help, please include the following:', 'post-smtp' ), 'https://wordpress.org/plugins/post-smtp/other_notes/', 'https://wordpress.org/support/plugin/post-smtp' ) );
		printf( '<textarea readonly="readonly" id="diagnostic-text" cols="80" rows="15">%s</textarea>', _x( 'Checking..', 'The "please wait" message', 'post-smtp' ) );
		print '</div>';
	}
}
