<?php
class PostmanConfigurationController {
	const CONFIGURATION_SLUG = 'postman/configuration';
	const CONFIGURATION_WIZARD_SLUG = 'postman/configuration_wizard';

	// logging
	private $logger;
	private $options;
	private $settingsRegistry;

	// Holds the values to be used in the fields callbacks
	private $rootPluginFilenameAndPath;

	private $importableConfiguration;

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
		$this->settingsRegistry = new PostmanSettingsRegistry();

		PostmanUtils::registerAdminMenu( $this, 'addConfigurationSubmenu' );
		PostmanUtils::registerAdminMenu( $this, 'addSetupWizardSubmenu' );

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
		new PostmanGetHostnameByEmailAjaxController();
		new PostmanManageConfigurationAjaxHandler();
	}

	/**
	 * 	 * Fires on the admin_init method
	 */
	public function on_admin_init(): void {
				$this->registerStylesAndScripts();
		$this->settingsRegistry->on_admin_init();
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

		wp_register_script( 'postman_manual_config_script', plugins_url( 'Postman/Postman-Configuration/postman_manual_config.js', $this->rootPluginFilenameAndPath ), array(
				PostmanViewController::JQUERY_SCRIPT,
				'jquery-ui-core',
				'jquery-ui-tabs',
				PostmanViewController::POSTMAN_SCRIPT,
		), $pluginData ['version'] );

		wp_register_script( 'postman_wizard_script', plugins_url( 'Postman/Postman-Configuration/postman_wizard.js', $this->rootPluginFilenameAndPath ), array(
				PostmanViewController::JQUERY_SCRIPT,
				PostmanViewController::POSTMAN_SCRIPT,
		), $pluginData ['version'] );
	}

	private function addLocalizeScriptsToPage(): void {
		__( 'Warning', 'post-smtp' );
		// user input
		wp_localize_script( PostmanViewController::POSTMAN_SCRIPT, 'postman_data', array(
			'host_element_name'=>'#input_' . PostmanOptions::HOSTNAME,
			'input_sender_email' => '#input_' . PostmanOptions::MESSAGE_SENDER_EMAIL,
			'input_sender_name' => '#input_' . PostmanOptions::MESSAGE_SENDER_NAME,
			'port_element_name' => '#input_' . PostmanOptions::PORT,
			'enc_for_password_el' => '#input_enc_type_password',
			'input_basic_username' => '#input_' . PostmanOptions::BASIC_AUTH_USERNAME,
			'input_basic_password' => '#input_' . PostmanOptions::BASIC_AUTH_PASSWORD,
			'redirect_url_el' => '#input_oauth_redirect_url',
			'input_auth_type' => '#input_' . PostmanOptions::AUTHENTICATION_TYPE,
			'wizard_bad_redirect_url' => __( 'You are about to configure OAuth 2.0 with an IP address instead of a domain name. This is not permitted. Either assign a real domain name to your site or add a fake one in your local host file.', 'post-smtp' ),
		));

		// the transport modules scripts
		foreach ( PostmanTransportRegistry::getInstance()->getTransports() as $transport ) {
			$transport->enqueueScript();
		}

		// we need data from port test
		PostmanConnectivityTestController::addLocalizeScriptForPortTest();

	}

	/**
	 * 	 * Register the Configuration screen
	 */
	public function addConfigurationSubmenu(): void {
		$page = add_submenu_page( PostmanViewController::POSTMAN_MENU_SLUG, sprintf( __( '%s Setup', 'post-smtp' ), __( 'Postman SMTP', 'post-smtp' ) ), __( 'Configuration', 'post-smtp' ), Postman::MANAGE_POSTMAN_CAPABILITY_NAME, PostmanConfigurationController::CONFIGURATION_SLUG, function () : void {
			$this->outputManualConfigurationContent();
		} 
		);
		// When the plugin options page is loaded, also load the stylesheet
		add_action( 'admin_print_styles-' . $page, function () : void {
			$this->enqueueConfigurationResources();
		} );
	}

	function enqueueConfigurationResources(): void {
		$this->addLocalizeScriptsToPage();
		wp_enqueue_style( PostmanViewController::POSTMAN_STYLE );
		wp_enqueue_script( 'postman_manual_config_script' );
	}

	/**
	 * 	 * Register the Setup Wizard screen
	 */
	public function addSetupWizardSubmenu(): void {
		$page = add_submenu_page( PostmanViewController::POSTMAN_MENU_SLUG, sprintf( __( '%s Setup', 'post-smtp' ), __( 'Postman SMTP', 'post-smtp' ) ), __( 'Setup wizard', 'post-smtp' ), Postman::MANAGE_POSTMAN_CAPABILITY_NAME, PostmanConfigurationController::CONFIGURATION_WIZARD_SLUG, function () : void {
			$this->outputWizardContent();
		} );
		// When the plugin options page is loaded, also load the stylesheet
		add_action( 'admin_print_styles-' . $page, function () : void {
			$this->enqueueWizardResources();
		} );
	}

	function enqueueWizardResources(): void {
		$this->addLocalizeScriptsToPage();
		$this->importableConfiguration = new PostmanImportableConfiguration();
		$startPage = 1;
		if ( $this->importableConfiguration->isImportAvailable() ) {
			$startPage = 0;
		}
		wp_localize_script( PostmanViewController::POSTMAN_SCRIPT, 'postman_setup_wizard', array(
				'start_page' => $startPage,
		) );
		wp_enqueue_style( PostmanViewController::POSTMAN_STYLE );
		wp_enqueue_script( 'postman_wizard_script' );
		$shortLocale = substr( get_locale(), 0, 2 );
		if ( $shortLocale != 'en' ) {
			$url = plugins_url( sprintf( 'script/jquery-validate/localization/messages_%s.js', $shortLocale ), $this->rootPluginFilenameAndPath );
			wp_enqueue_script( sprintf( 'jquery-validation-locale-%s', $shortLocale ), $url );
		}
	}

	public function outputManualConfigurationContent(): void {
		print '<div class="wrap">';

		PostmanViewController::outputChildPageHeader( __( 'Settings', 'post-smtp' ), 'advanced_config' );
		print '<div id="config_tabs"><ul>';
		print sprintf( '<li><a href="#account_config">%s</a></li>', __( 'Account', 'post-smtp' ) );
		print sprintf( '<li><a href="#fallback">%s</a></li>', __( 'Fallback', 'post-smtp' ) );
		print sprintf( '<li><a href="#message_config">%s</a></li>', __( 'Message', 'post-smtp' ) );
		print sprintf( '<li><a href="#logging_config">%s</a></li>', __( 'Logging', 'post-smtp' ) );
		print sprintf( '<li><a href="#advanced_options_config">%s</a></li>', __( 'Advanced', 'post-smtp' ) );
		print sprintf( '<li><a href="#notifications">%s</a></li>', __( 'Notifications', 'post-smtp' ) );
		print '</ul>';

		print '<form method="post" action="options.php">';
		// This prints out all hidden setting fields
		settings_fields( PostmanAdminController::SETTINGS_GROUP_NAME );

		// account_config
		print '<section id="account_config">';
		if ( count( PostmanTransportRegistry::getInstance()->getTransports() ) > 1 ) {
			do_settings_sections( 'transport_options' );
		} else {
			printf( '<input id="input_%2$s" type="hidden" name="%1$s[%2$s]" value="%3$s"/>', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TRANSPORT_TYPE, PostmanSmtpModuleTransport::SLUG );
		}
		print '<div id="smtp_config" class="transport_setting">';
		do_settings_sections( PostmanAdminController::SMTP_OPTIONS );
		print '</div>';
		print '<div id="password_settings" class="authentication_setting non-oauth2">';
		do_settings_sections( PostmanAdminController::BASIC_AUTH_OPTIONS );
		print '</div>';
		print '<div id="oauth_settings" class="authentication_setting non-basic">';
		do_settings_sections( PostmanAdminController::OAUTH_AUTH_OPTIONS );
		print '</div>';
		print '<div id="mandrill_settings" class="authentication_setting non-basic non-oauth2">';
		do_settings_sections( PostmanMandrillTransport::MANDRILL_AUTH_OPTIONS );
		print '</div>';
		print '<div id="sendgrid_settings" class="authentication_setting non-basic non-oauth2">';
		do_settings_sections( PostmanSendGridTransport::SENDGRID_AUTH_OPTIONS );
		print '</div>';
		print '<div id="mailgun_settings" class="authentication_setting non-basic non-oauth2">';
		do_settings_sections( PostmanMailgunTransport::MAILGUN_AUTH_OPTIONS );
		print '</div>';
		print '</section>';
        // end account config
		?>

        <!-- Fallback Start -->
        <section id="fallback">
            <h2><?php esc_html_e( 'Failed emails fallback', 'post-smtp' ); ?></h2>
            <p><?php esc_html_e( 'By enable this option, if your email is fail to send Post SMTP will try to use the SMTP service you define here.', 'post-smtp' ); ?></p>
            <table class="form-table">
                <tr valign="">
                    <th scope="row"><?php _e( 'Use Fallback?', 'post-smtp' ); ?></th>
                    <td>
                        <label>
                            <input name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_ENABLED; ?>]" type="radio"
                                   value="no"<?php echo checked( $this->options->getFallbackIsEnabled(), 'no' ); ?>>
                            <?php _e( 'No', 'post-smtp' ); ?>
                        </label>
                        &nbsp;
                        <label>
                            <?php checked( $this->options->getFallbackIsEnabled(), 'yes', false ); ?>
                            <input name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_ENABLED; ?>]" type="radio"
                                   value="yes"<?php echo checked( $this->options->getFallbackIsEnabled(), 'yes' ); ?>>
                            <?php _e( 'Yes', 'post-smtp' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Outgoing Mail Server', 'post-smtp' ); ?></th>
                    <?php $host = $this->options->getFallbackHostname(); ?>
                    <td>
                        <input type="text" id="fallback-smtp-host" name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_HOSTNAME; ?>]"
                               value="<?php echo $host; ?>" placeholder="Example: smtp.host.com">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Mail Server Port', 'post-smtp' ); ?></th>
                    <?php $port = $this->options->getFallbackPort(); ?>
                    <td>
                        <input type="number" id="fallback-smtp-port" name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_PORT; ?>]"
                               value="<?php echo $port; ?>" placeholder="Example: 587">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Security', 'post-smtp' ); ?></th>
                    <?php
                    $security_options = array(
                            'none' => __( 'None', 'post-smtp' ),
                            'ssl' => __( 'SSL', 'post-smtp' ),
                            'tls' => __( 'TLS', 'post-smtp' ),
                    );
                    ?>
                    <td>
                        <select id="fallback-smtp-security" name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_SECURITY; ?>]">
                            <?php
                            foreach ( $security_options as $key => $label ) {
                                $selected = selected( $this->options->getFallbackSecurity(), $key,false );
                                ?>
                                <option value="<?php echo $key; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>

				<tr>
					<th scope="row"><?php _e('From Email', 'post-smtp' ); ?></th>
					<td>
						<input type="email" id="fallback-smtp-from-email"
							   value="<?php echo $this->options->getFallbackFromEmail(); ?>"
							   name="postman_options[<?php echo PostmanOptions::FALLBACK_FROM_EMAIL; ?>]"
						>
						<br>
						<small><?php _e( "Use allowed email, for example: If you are using Gmail, type your Gmail adress.", 'post-smtp' ); ?></small>
					</td>
				</tr>

                <tr valign="">
                    <th scope="row"><?php _e( 'Use SMTP Authentication?', 'post-smtp' ); ?></th>
                    <td>
                        <label>
                            <input name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_USE_AUTH; ?>]"
                                   type="radio" value="none"<?php checked( $this->options->getFallbackAuth(), 'none' ); ?>>
                            <?php _e( 'No', 'post-smtp' ); ?>
                        </label>
                        &nbsp;
                        <label>
                            <input name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_USE_AUTH; ?>]"
                                   type="radio" value="login"<?php checked( $this->options->getFallbackAuth(), 'login' ); ?>>
                            <?php _e( 'Yes', 'post-smtp' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('User name', 'post-smtp' ); ?></th>
                    <td>
                        <input type="text" id="fallback-smtp-username"
                               value="<?php echo $this->options->getFallbackUsername(); ?>"
                               name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_USERNAME; ?>]"
                        >
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Password', 'post-smtp' ); ?></th>
                    <td>
                        <input type="password" id="fallback-smtp-password"
                               value="<?php echo PostmanUtils::obfuscatePassword( $this->options->getFallbackPassword() ); ?>"
                               name="postman_options[<?php echo PostmanOptions::FALLBACK_SMTP_PASSWORD; ?>]"
                        >
                    </td>
                </tr>

            </table>
        </section>
        <!-- Fallback End -->

        <?php
		print '<section id="message_config">';
		do_settings_sections( PostmanAdminController::MESSAGE_SENDER_OPTIONS );
		do_settings_sections( PostmanAdminController::MESSAGE_FROM_OPTIONS );
		do_settings_sections( PostmanAdminController::EMAIL_VALIDATION_OPTIONS );
		do_settings_sections( PostmanAdminController::MESSAGE_OPTIONS );
		do_settings_sections( PostmanAdminController::MESSAGE_HEADERS_OPTIONS );
		print '</section>';
		print '<section id="logging_config">';
		do_settings_sections( PostmanAdminController::LOGGING_OPTIONS );
		print '</section>';
		/*
		 * print '<section id="logging_config">';
		 * do_settings_sections ( PostmanAdminController::MULTISITE_OPTIONS );
		 * print '</section>';
		 */
		print '<section id="advanced_options_config">';
		do_settings_sections( PostmanAdminController::NETWORK_OPTIONS );
		do_settings_sections( PostmanAdminController::ADVANCED_OPTIONS );
		print '</section>';

		print '<section id="notifications">';
		do_settings_sections( PostmanAdminController::NOTIFICATIONS_OPTIONS );

			$currentKey = $this->options->getNotificationService();
			$pushover = $currentKey == 'pushover' ? 'block' : 'none';
			$slack = $currentKey == 'slack' ? 'block' : 'none';

			echo '<div id="pushover_cred" style="display: ' . $pushover . ';">';
			do_settings_sections( PostmanAdminController::NOTIFICATIONS_PUSHOVER_CRED );
			echo '</div>';

			echo '<div id="slack_cred" style="display: ' . $slack . ';">';
			do_settings_sections( PostmanAdminController::NOTIFICATIONS_SLACK_CRED );
			echo '</div>';

		print '</section>';

		submit_button();
		print '</form>';
		print '</div>';
		print '</div>';
	}

	public function outputWizardContent(): void {
		// Set default values for input fields
		$this->options->setMessageSenderEmailIfEmpty( wp_get_current_user()->user_email );
		$this->options->setMessageSenderNameIfEmpty( wp_get_current_user()->display_name );

		// construct Wizard
		print '<div class="wrap">';

		PostmanViewController::outputChildPageHeader( __( 'Setup Wizard', 'post-smtp' ) );

		print '<form id="postman_wizard" method="post" action="options.php">';

		// account tab
		// message tab
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PREVENT_MESSAGE_SENDER_EMAIL_OVERRIDE, $this->options->isPluginSenderEmailEnforced() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PREVENT_MESSAGE_SENDER_NAME_OVERRIDE, $this->options->isPluginSenderNameEnforced() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::REPLY_TO, $this->options->getReplyTo() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_TO_RECIPIENTS, $this->options->getForcedToRecipients() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_CC_RECIPIENTS, $this->options->getForcedCcRecipients() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_BCC_RECIPIENTS, $this->options->getForcedBccRecipients() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::ADDITIONAL_HEADERS, $this->options->getAdditionalHeaders() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::DISABLE_EMAIL_VALIDAITON, $this->options->isEmailValidationDisabled() );

		// logging tab
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::MAIL_LOG_ENABLED_OPTION, $this->options->getMailLoggingEnabled() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::MAIL_LOG_MAX_ENTRIES, $this->options->getMailLoggingMaxEntries() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TRANSCRIPT_SIZE, $this->options->getTranscriptSize() );

		// advanced tab
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::CONNECTION_TIMEOUT, $this->options->getConnectionTimeout() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::READ_TIMEOUT, $this->options->getReadTimeout() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::LOG_LEVEL, $this->options->getLogLevel() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::RUN_MODE, $this->options->getRunMode() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::STEALTH_MODE, $this->options->isStealthModeEnabled() );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TEMPORARY_DIRECTORY, $this->options->getTempDirectory() );

		// display the setting text
		settings_fields( PostmanAdminController::SETTINGS_GROUP_NAME );

		// Wizard Step 1
		printf( '<h5>%s</h5>', _x( 'Sender Details', 'Wizard Step Title', 'post-smtp' ) );
		print '<fieldset>';
		printf( '<legend>%s</legend>', _x( 'Who is the mail coming from?', 'Wizard Step Title', 'post-smtp' ) );
		printf( '<p>%s</p>', __( 'Enter the email address and name you\'d like to send mail as.', 'post-smtp' ) );
		printf( '<p>%s</p>', __( 'Please note that to prevent abuse, many email services will <em>not</em> let you send from an email address other than the one you authenticate with.', 'post-smtp' ) );
		printf( '<label for="postman_options[sender_email]">%s</label>', __( 'Email Address', 'post-smtp' ) );
		$this->settingsRegistry->from_email_callback();
		print '<br/>';
		printf( '<label for="postman_options[sender_name]">%s</label>', __( 'Name', 'post-smtp' ) );
		$this->settingsRegistry->sender_name_callback();
		print '</fieldset>';

		// Wizard Step 2
		printf( '<h5>%s</h5>', __( 'Outgoing Mail Server Hostname', 'post-smtp' ) );
		print '<fieldset>';
		foreach ( PostmanTransportRegistry::getInstance()->getTransports() as $transport ) {
			$transport->printWizardMailServerHostnameStep();
		}
		print '</fieldset>';

		// Wizard Step 3
		printf( '<h5>%s</h5>', __( 'Connectivity Test', 'post-smtp' ) );
		print '<fieldset>';
		printf( '<legend>%s</legend>', __( 'How will the connection to the mail server be established?', 'post-smtp' ) );
		printf( '<p>%s</p>', __( 'Your connection settings depend on what your email service provider offers, and what your WordPress host allows.', 'post-smtp' ) );
		printf( '<p id="connectivity_test_status">%s: <span id="port_test_status">%s</span></p>', __( 'Connectivity Test', 'post-smtp' ), _x( 'Ready', 'TCP Port Test Status', 'post-smtp' ) );
		printf( '<p class="ajax-loader" style="display:none"><img src="%s"/></p>', plugins_url( 'post-smtp/style/ajax-loader.gif' ) );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TRANSPORT_TYPE );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PORT );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::SECURITY_TYPE );
		printf( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::AUTHENTICATION_TYPE );
		print '<p id="wizard_recommendation"></p>';
		/* Translators: Where %1$s is the socket identifier and %2$s is the authentication type */
		printf( '<p class="user_override" style="display:none"><label><span>%s:</span></label> <table id="user_socket_override" class="user_override"></table></p>', _x( 'Socket', 'A socket is the network term for host and port together', 'post-smtp' ) );
		printf( '<p class="user_override" style="display:none"><label><span>%s:</span></label> <table id="user_auth_override" class="user_override"></table></p>', __( 'Authentication', 'post-smtp' ) );
		print ('<p><span id="smtp_mitm" style="display:none; background-color:yellow"></span></p>') ;
		$warning = __( 'Warning', 'post-smtp' );
		$clearCredentialsWarning = __( 'This configuration option will send your authorization credentials in the clear.', 'post-smtp' );
		printf( '<p id="smtp_not_secure" style="display:none"><span style="background-color:yellow">%s: %s</span></p>', $warning, $clearCredentialsWarning );
		print '</fieldset>';

		// Wizard Step 4
		printf( '<h5>%s</h5>', __( 'Authentication', 'post-smtp' ) );
		print '<fieldset>';
		printf( '<legend>%s</legend>', __( 'How will you prove your identity to the mail server?', 'post-smtp' ) );
		foreach ( PostmanTransportRegistry::getInstance()->getTransports() as $transport ) {
			$transport->printWizardAuthenticationStep();
		}
		print '</fieldset>';

		// Wizard Step 5 - Notificiations
		printf( '<h5>%s</h5>', __( 'Notifications', 'post-smtp' ) );
		print '<fieldset>';
		printf( '<legend>%s</legend>', __( 'Select a notify service to notify you when an email is failed to delivered.', 'post-smtp' ) );

		?>
		<select id="input_notification_service" class="input_notification_service" name="postman_options[notification_service]">
			<option value="default">Email</option>
			<option value="pushover">Pushover</option>
			<option value="slack">Slack</option>
		</select>
		<div id="pushover_cred" style="display: none;">
			<h2><?php _e( 'Pushover Credentials', 'post-smtp' ); ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Pushover User Key', 'post-smtp' ); ?></th>
						<td>
							<input type="password" id="pushover_user" name="postman_options[pushover_user]" value="">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Pushover App Token', 'post-smtp' ); ?></th>
						<td>
							<input type="password" id="pushover_token" name="postman_options[pushover_token]" value="">
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="slack_cred" style="display: none;">
			<h2><?php _e( 'Slack Credentials', 'post-smtp' ); ?></h2>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><?php _e( 'Slack webhook', 'post-smtp' ); ?></th>
					<td>
						<input type="password" id="slack_token" name="postman_options[slack_token]" value="">
						<a target="_blank" class="" href="https://slack.postmansmtp.com/">
							<?php _e( 'Get your webhook URL here.', 'post-smtp' ); ?>
						</a>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

        <div id="use-chrome-extension">
            <h2><?php _e( 'Push To Chrome Extension', 'post-smtp' ); ?></h2>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><?php _e( 'This is an extra notification to the selection above', 'post-smtp' ); ?></th>
                    <td>
                        <input type="checkbox" id="notification_use_chrome" name="postman_options[notification_use_chrome]">
                        <a target="_blank" class="" href="https://chrome.google.com/webstore/detail/npklmbkpbknkmbohdbpikeidiaekjoch">
                            <?php _e( 'You can download the chrome extension here.', 'post-smtp' ); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Your UID as you see in the extension.', 'post-smtp' ); ?></th>
                    <td>
                        <input type="password" id="notification_chrome_uid" name="postman_options[notification_chrome_uid]" value="">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

		<?php
		print '</fieldset>';

		// Wizard Step 6
		printf( '<h5>%s</h5>', _x( 'Finish', 'The final step of the Wizard', 'post-smtp' ) );
		print '<fieldset>';
		printf( '<legend>%s</legend>', _x( 'You\'re Done!', 'Wizard Step Title', 'post-smtp' ) );
		print '<section>';
		printf( '<p>%s</p>', __( 'Click Finish to save these settings, then:', 'post-smtp' ) );
		print '<ul style="margin-left: 20px">';
		printf( '<li class="wizard-auth-oauth2">%s</li>', __( 'Grant permission with the Email Provider for Postman to send email and', 'post-smtp' ) );
		printf( '<li>%s</li>', __( 'Send yourself a Test Email to make sure everything is working!', 'post-smtp' ) );
		print '</ul>';

		// Get PHPmailer recommendation
		Postman::getMailerTypeRecommend();

		print '</section>';
		print '</fieldset>';
		print '</form>';
		print '</div>';
	}
}
