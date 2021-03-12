<?php
class PostmanSendTestEmailController {
	const EMAIL_TEST_SLUG = 'postman/email_test';
	const RECIPIENT_EMAIL_FIELD_NAME = 'postman_recipient_email';

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

		PostmanUtils::registerAdminMenu( $this, 'addEmailTestSubmenu' );

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
		new PostmanSendTestEmailAjaxController();
	}

	/**
	 * 	 * Fires on the admin_init method
	 */
	public function on_admin_init(): void {
				$this->registerStylesAndScripts();
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 *
	 */
	public function test_email_callback(): string {
		return sprintf( '<input type="text" id="%s" name="postman_test_options[test_email]" value="%s" class="required email" size="40"/>', self::RECIPIENT_EMAIL_FIELD_NAME, wp_get_current_user()->user_email );
	}

	/**
	 * 	 * Register and add settings
	 */
	private function registerStylesAndScripts(): void {
		if ( $this->logger->isTrace() ) {
			$this->logger->trace( 'registerStylesAndScripts()' );
		}

				$pluginData = apply_filters( 'postman_get_plugin_metadata', null );

		// register the stylesheet resource
		wp_register_style( 
			'postman_send_test_email', 
			plugins_url( 'Postman/Postman-Send-Test-Email/postman_send_test_email.css', $this->rootPluginFilenameAndPath ), 
			array( PostmanViewController::POSTMAN_STYLE ), 
			$pluginData ['version'] 
		);

		// register the javascript resource
		wp_register_script( 'postman_test_email_wizard_script', plugins_url( 'Postman/Postman-Send-Test-Email/postman_send_test_email.js', $this->rootPluginFilenameAndPath ), array(
				PostmanViewController::JQUERY_SCRIPT,
				PostmanViewController::POSTMAN_SCRIPT,
		), $pluginData ['version'] );
	}

	/**
	 * 	 * Register the Email Test screen
	 */
	public function addEmailTestSubmenu(): void {
		$page = add_submenu_page( PostmanViewController::POSTMAN_MENU_SLUG, sprintf( __( '%s Setup', 'post-smtp' ), __( 'Postman SMTP', 'post-smtp' ) ), __( 'Email test', 'post-smtp' ), Postman::MANAGE_POSTMAN_CAPABILITY_NAME, PostmanSendTestEmailController::EMAIL_TEST_SLUG, function () : void {
			$this->outputTestEmailContent();
		} );
		// When the plugin options page is loaded, also load the stylesheet
		add_action( 'admin_print_styles-' . $page, function () : void {
			$this->enqueueEmailTestResources();
		} );
	}

	function enqueueEmailTestResources(): void {
		wp_enqueue_style( PostmanViewController::POSTMAN_STYLE );
		wp_enqueue_style( 'postman_send_test_email' );
		wp_enqueue_script( 'postman_test_email_wizard_script' );
		wp_localize_script( PostmanViewController::POSTMAN_SCRIPT, 'postman_email_test', array(
				'recipient' => '#' . self::RECIPIENT_EMAIL_FIELD_NAME,
				'not_started' => _x( 'In Outbox', 'Email Test Status', 'post-smtp' ),
				'sending' => _x( 'Sending...', 'Email Test Status', 'post-smtp' ),
				'success' => _x( 'Success', 'Email Test Status', 'post-smtp' ),
				'failed' => _x( 'Failed', 'Email Test Status', 'post-smtp' ),
				'ajax_error' => __( 'Ajax Error', 'post-smtp' ),
		) );
	}

	public function outputTestEmailContent(): void {
		print '<div class="wrap">';

		PostmanViewController::outputChildPageHeader( __( 'Send a Test Email', 'post-smtp' ) );

		printf( '<form id="postman_test_email_wizard" method="post" action="%s">', PostmanUtils::getSettingsPageUrl() );

		// Step 1
		printf( '<h5>%s</h5>', __( 'Specify the Recipient', 'post-smtp' ) );
		print '<fieldset>';
		printf( '<legend>%s</legend>', __( 'Who is this message going to?', 'post-smtp' ) );
		printf( '<p>%s', __( 'This utility allows you to send an email message for testing.', 'post-smtp' ) );
		print ' ';
		/* translators: where %d is an amount of time, in seconds */
		printf( '%s</p>', sprintf( _n( 'If there is a problem, Postman will give up after %d second.', 'If there is a problem, Postman will give up after %d seconds.', $this->options->getReadTimeout(), 'post-smtp' ), $this->options->getReadTimeout() ) );
		printf( '<label for="postman_test_options[test_email]">%s</label>', _x( 'Recipient Email Address', 'Configuration Input Field', 'post-smtp' ) );
		print $this->test_email_callback();
		print '</fieldset>';

		// Step 2
		printf( '<h5>%s</h5>', __( 'Send The Message', 'post-smtp' ) );
		print '<fieldset>';
		print '<legend>';
		print __( 'Sending the message:', 'post-smtp' );
		printf( ' <span id="postman_test_message_status">%s</span>', _x( 'In Outbox', 'Email Test Status', 'post-smtp' ) );
		print '</legend>';
		print '<section>';
		printf( '<p><label>%s</label></p>', __( 'Status', 'post-smtp' ) );
		print '<textarea id="postman_test_message_error_message" readonly="readonly" cols="65" rows="4"></textarea>';
		print '</section>';
		print '</fieldset>';

		// Step 3
		printf( '<h5>%s</h5>', __( 'Session Transcript', 'post-smtp' ) );
		print '<fieldset>';
		printf( '<legend>%s</legend>', __( 'Examine the Session Transcript if you need to.', 'post-smtp' ) );
		printf( '<p>%s</p>', __( 'This is the conversation between Postman and the mail server. It can be useful for diagnosing problems. <b>DO NOT</b> post it on-line, it may contain your account password.', 'post-smtp' ) );
		print '<section>';
		printf( '<p><label for="postman_test_message_transcript">%s</label></p>', __( 'Session Transcript', 'post-smtp' ) );
		print '<textarea readonly="readonly" id="postman_test_message_transcript" cols="65" rows="8"></textarea>';
		print '</section>';
		print '</fieldset>';

		print '</form>';
		print '</div>';
	}
}
