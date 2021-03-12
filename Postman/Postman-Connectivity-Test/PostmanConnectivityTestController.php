<?php

class PostmanConnectivityTestController {

		const PORT_TEST_SLUG = 'postman/port_test';

	// logging
	private $logger;

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

		PostmanUtils::registerAdminMenu( $this, 'addPortTestSubmenu' );

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
		new PostmanPortTestAjaxController();
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
		// register the stylesheet and javascript external resources
		$pluginData = apply_filters( 'postman_get_plugin_metadata', null );
		wp_register_script( 'postman_port_test_script', plugins_url( 'Postman/Postman-Connectivity-Test/postman_port_test.js', $this->rootPluginFilenameAndPath ), array(
				PostmanViewController::JQUERY_SCRIPT,
				PostmanViewController::POSTMAN_SCRIPT,
		), $pluginData ['version'] );
	}

	/**
	 * 	 * Register the Email Test screen
	 */
	public function addPortTestSubmenu(): void {
		$page = add_submenu_page( PostmanViewController::POSTMAN_MENU_SLUG, sprintf( __( '%s Setup', 'post-smtp' ), __( 'Postman SMTP', 'post-smtp' ) ), __( 'Connectivity test', 'post-smtp' ), Postman::MANAGE_POSTMAN_CAPABILITY_NAME, PostmanConnectivityTestController::PORT_TEST_SLUG, function () : void {
			$this->outputPortTestContent();
		} );
		// When the plugin options page is loaded, also load the stylesheet
		add_action( 'admin_print_styles-' . $page, function () : void {
			$this->enqueuePortTestResources();
		} );
	}

	function enqueuePortTestResources(): void {
		wp_enqueue_style( PostmanViewController::POSTMAN_STYLE );
		wp_enqueue_script( 'postman_port_test_script' );
		__( 'Warning', 'post-smtp' );
		wp_localize_script( PostmanViewController::POSTMAN_SCRIPT, 'postman_data',array('host_element_name' => '#input_' . PostmanOptions::HOSTNAME ));
		PostmanConnectivityTestController::addLocalizeScriptForPortTest();
	}
	static function addLocalizeScriptForPortTest(): void {
		wp_localize_script( PostmanViewController::POSTMAN_SCRIPT, 'postman_port_test', array(
				'in_progress' => _x( 'Checking..', 'The "please wait" message', 'post-smtp' ),
				'open' => _x( 'Open', 'The port is open', 'post-smtp' ),
				'closed' => _x( 'Closed', 'The port is closed', 'post-smtp' ),
				'yes' => __( 'Yes', 'post-smtp' ),
				'no' => __( 'No', 'post-smtp' ),
			/* translators: where %d is a port number */
			'blocked' => __( 'No outbound route between this site and the Internet on Port %d.', 'post-smtp' ),
			/* translators: where %d is a port number and %s is a hostname */
			'try_dif_smtp' => __( 'Port %d is open, but not to %s.', 'post-smtp' ),
			/* translators: where %d is the port number and %s is the hostname */
			'success' => __( 'Port %d can be used for SMTP to %s.', 'post-smtp' ),
				'mitm' => sprintf( '%s: %s', __( 'Warning', 'post-smtp' ), __( 'connected to %1$s instead of %2$s.', 'post-smtp' ) ),
			/* translators: where %d is a port number and %s is the URL for the Postman Gmail Extension */
			'https_success' => __( 'Port %d can be used with the %s.', 'post-smtp' ),
		) );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 */
	public function port_test_hostname_callback(): void {
		$hostname = PostmanTransportRegistry::getInstance()->getSelectedTransport()->getHostname();
		if ( empty( $hostname ) ) {
			$hostname = PostmanTransportRegistry::getInstance()->getActiveTransport()->getHostname();
		}
		printf( '<input type="text" id="input_hostname" name="postman_options[hostname]" value="%s" size="40" class="required"/>', $hostname );
	}

	public function outputPortTestContent(): void {
		print '<div class="wrap">';

		PostmanViewController::outputChildPageHeader( __( 'Connectivity Test', 'post-smtp' ) );

		print '<p>';
		print __( 'This test determines which well-known ports are available for Postman to use.', 'post-smtp' );
		print '<form id="port_test_form_id" method="post">';
		printf( '<label for="hostname">%s</label>', __( 'Outgoing Mail Server Hostname', 'post-smtp' ) );
		$this->port_test_hostname_callback();
		submit_button( _x( 'Begin Test', 'Button Label', 'post-smtp' ), 'primary', 'begin-port-test', true );
		print '</form>';
		print '<table id="connectivity_test_table">';
		print sprintf( '<tr><th rowspan="2">%s</th><th rowspan="2">%s</th><th rowspan="2">%s</th><th rowspan="2">%s</th><th rowspan="2">%s</th><th colspan="5">%s</th></tr>', __( 'Transport', 'post-smtp' ), _x( 'Socket', 'A socket is the network term for host and port together', 'post-smtp' ), __( 'Status', 'post-smtp' ) . '<sup>*</sup>', __( 'Service Available', 'post-smtp' ), __( 'Server ID', 'post-smtp' ), __( 'Authentication', 'post-smtp' ) );
		print sprintf( '<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>', 'None', 'Login', 'Plain', 'CRAM-MD5', 'OAuth 2.0' );
		$sockets = PostmanTransportRegistry::getInstance()->getSocketsForSetupWizardToProbe();
		foreach ( $sockets as $socket ) {
			if ( $socket ['smtp'] ) {
				print sprintf( '<tr id="%s"><th class="name">%s</th><td class="socket">%s:%s</td><td class="firewall resettable">-</td><td class="service resettable">-</td><td class="reported_id resettable">-</td><td class="auth_none resettable">-</td><td class="auth_login resettable">-</td><td class="auth_plain resettable">-</td><td class="auth_crammd5 resettable">-</td><td class="auth_xoauth2 resettable">-</td></tr>', $socket ['id'], $socket ['transport_name'], $socket ['host'], $socket ['port'] );
			} else {
				print sprintf( '<tr id="%s"><th class="name">%s</th><td class="socket">%s:%s</td><td class="firewall resettable">-</td><td class="service resettable">-</td><td class="reported_id resettable">-</td><td colspan="5">%s</td></tr>', $socket ['id'], $socket ['transport_name'], $socket ['host'], $socket ['port'], __( 'n/a', 'post-smtp' ) );
			}
		}
		print '</table>';
		/* Translators: Where %s is the name of the service providing Internet connectivity test */
		printf( '<p class="portquiz" style="display:none; font-size:0.8em">* %s</p>', sprintf( __( 'According to %s', 'post-smtp' ), '<a target="_blank" href="https://downor.me/portquiz.net">portquiz.net</a>' ) );
		printf( '<p class="ajax-loader" style="display:none"><img src="%s"/></p>', plugins_url( 'post-smtp/style/ajax-loader.gif' ) );
		print '<section id="conclusion" style="display:none">';
		print sprintf( '<h3>%s:</h3>', __( 'Summary', 'post-smtp' ) );
		print '<ol class="conclusion">';
		print '</ol>';
		print '</section>';
		print '<section id="blocked-port-help" style="display:none">';
		print sprintf( '<p><b>%s</b></p>', __( 'A test with <span style="color:red">"No"</span> Service Available indicates one or more of these issues:', 'post-smtp' ) );
		print '<ol>';
		printf( '<li>%s</li>', __( 'Your web host has placed a firewall between this site and the Internet', 'post-smtp' ) );
		printf( '<li>%s</li>', __( 'The SMTP hostname is wrong or the mail server does not provide service on this port', 'post-smtp' ) );
		/* translators: where (1) is the URL and (2) is the system */
		$systemBlockMessage = __( 'Your <a href="%1$s">%2$s configuration</a> is preventing outbound connections', 'post-smtp' );
		printf( '<li>%s</li>', sprintf( $systemBlockMessage, 'http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen', 'PHP' ) );
		printf( '<li>%s</li>', sprintf( $systemBlockMessage, 'http://wp-mix.com/disable-external-url-requests/', 'WordPress' ) );
		print '</ol></p>';
		print sprintf( '<p><b>%s</b></p>', __( 'If the issues above can not be resolved, your last option is to configure Postman to use an email account managed by your web host with an SMTP server managed by your web host.', 'post-smtp' ) );
		print '</section>';
		print '</div>';
	}
}
