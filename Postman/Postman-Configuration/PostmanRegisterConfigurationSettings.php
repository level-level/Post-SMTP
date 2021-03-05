<?php
class PostmanSettingsRegistry {

	private $options;

	public function __construct() {
		$this->options = PostmanOptions::getInstance();
	}

	/**
	 * 	 * Fires on the admin_init method
	 *
	 * @return void
	 */
	public function on_admin_init(): void {
				$this->registerSettings();
	}

	/**
	 * 	 * Register and add settings
	 *
	 * @return void
	 */
	private function registerSettings(): void {

		// only administrators should be able to trigger this
		if ( PostmanUtils::isAdmin() ) {
			$sanitizer = new PostmanInputSanitizer();
			register_setting( PostmanAdminController::SETTINGS_GROUP_NAME, PostmanOptions::POSTMAN_OPTIONS, function ($input) use ($sanitizer) {
				return $sanitizer->sanitize($input);
			} );

			// Sanitize
			add_settings_section( 'transport_section', __( 'Transport', 'post-smtp' ), function () : void {
				$this->printTransportSectionInfo();
			}, 'transport_options' );

			add_settings_field( PostmanOptions::TRANSPORT_TYPE, _x( 'Type', '(i.e.) What kind is it?', 'post-smtp' ), function () : void {
				$this->transport_type_callback();
			}, 'transport_options', 'transport_section' );

            add_settings_field( 'smtp_mailers', __( 'Mailer Type', 'post-smtp' ), function () : void {
													$this->smtp_mailer_callback();
												}, 'transport_options', 'transport_section'  );

			// the Message From section
			add_settings_section( PostmanAdminController::MESSAGE_FROM_SECTION, _x( 'From Address', 'The Message Sender Email Address', 'post-smtp' ), function () : void {
				$this->printMessageFromSectionInfo();
			}, PostmanAdminController::MESSAGE_FROM_OPTIONS );

			add_settings_field( PostmanOptions::MESSAGE_SENDER_EMAIL, __( 'Email Address', 'post-smtp' ), function () : void {
				$this->from_email_callback();
			}, PostmanAdminController::MESSAGE_FROM_OPTIONS, PostmanAdminController::MESSAGE_FROM_SECTION );

			add_settings_field( PostmanOptions::PREVENT_MESSAGE_SENDER_EMAIL_OVERRIDE, '', function () : void {
				$this->prevent_from_email_override_callback();
			}, PostmanAdminController::MESSAGE_FROM_OPTIONS, PostmanAdminController::MESSAGE_FROM_SECTION );

			add_settings_field( PostmanOptions::MESSAGE_SENDER_NAME, __( 'Name', 'post-smtp' ), function () : void {
				$this->sender_name_callback();
			}, PostmanAdminController::MESSAGE_FROM_OPTIONS, PostmanAdminController::MESSAGE_FROM_SECTION );

			add_settings_field( PostmanOptions::PREVENT_MESSAGE_SENDER_NAME_OVERRIDE, '', function () : void {
				$this->prevent_from_name_override_callback();
			}, PostmanAdminController::MESSAGE_FROM_OPTIONS, PostmanAdminController::MESSAGE_FROM_SECTION );

			// the Additional Addresses section
			add_settings_section( PostmanAdminController::MESSAGE_SECTION, __( 'Additional Email Addresses', 'post-smtp' ), function () : void {
				$this->printMessageSectionInfo();
			}, PostmanAdminController::MESSAGE_OPTIONS );

			add_settings_field( PostmanOptions::REPLY_TO, __( 'Reply-To', 'post-smtp' ), function () : void {
				$this->reply_to_callback();
			}, PostmanAdminController::MESSAGE_OPTIONS, PostmanAdminController::MESSAGE_SECTION );

			add_settings_field( PostmanOptions::FORCED_TO_RECIPIENTS, __( 'To Recipient(s)', 'post-smtp' ), function () : void {
				$this->to_callback();
			}, PostmanAdminController::MESSAGE_OPTIONS, PostmanAdminController::MESSAGE_SECTION );

			add_settings_field( PostmanOptions::FORCED_CC_RECIPIENTS, __( 'Carbon Copy Recipient(s)', 'post-smtp' ), function () : void {
				$this->cc_callback();
			}, PostmanAdminController::MESSAGE_OPTIONS, PostmanAdminController::MESSAGE_SECTION );

			add_settings_field( PostmanOptions::FORCED_BCC_RECIPIENTS, __( 'Blind Carbon Copy Recipient(s)', 'post-smtp' ), function () : void {
				$this->bcc_callback();
			}, PostmanAdminController::MESSAGE_OPTIONS, PostmanAdminController::MESSAGE_SECTION );

			// the Additional Headers section
			add_settings_section( PostmanAdminController::MESSAGE_HEADERS_SECTION, __( 'Additional Headers', 'post-smtp' ), function () : void {
				$this->printAdditionalHeadersSectionInfo();
			}, PostmanAdminController::MESSAGE_HEADERS_OPTIONS );

			add_settings_field( PostmanOptions::ADDITIONAL_HEADERS, __( 'Custom Headers', 'post-smtp' ), function () : void {
				$this->headers_callback();
			}, PostmanAdminController::MESSAGE_HEADERS_OPTIONS, PostmanAdminController::MESSAGE_HEADERS_SECTION );

			// Fallback

			// the Email Validation section
			add_settings_section( PostmanAdminController::EMAIL_VALIDATION_SECTION, __( 'Validation', 'post-smtp' ), function () : void {
				$this->printEmailValidationSectionInfo();
			}, PostmanAdminController::EMAIL_VALIDATION_OPTIONS );

			add_settings_field( PostmanOptions::ENVELOPE_SENDER, __( 'Email Address', 'post-smtp' ), function () : void {
				$this->disable_email_validation_callback();
			}, PostmanAdminController::EMAIL_VALIDATION_OPTIONS, PostmanAdminController::EMAIL_VALIDATION_SECTION );

			// the Logging section
			add_settings_section( PostmanAdminController::LOGGING_SECTION, __( 'Email Log Settings', 'post-smtp' ), function () : void {
				$this->printLoggingSectionInfo();
			}, PostmanAdminController::LOGGING_OPTIONS );

			add_settings_field( 'logging_status', __( 'Enable Logging', 'post-smtp' ), function () : void {
				$this->loggingStatusInputField();
			}, PostmanAdminController::LOGGING_OPTIONS, PostmanAdminController::LOGGING_SECTION );

			add_settings_field( 'logging_max_entries', __( 'Maximum Log Entries', 'post-smtp' ), function () : void {
				$this->loggingMaxEntriesInputField();
			}, PostmanAdminController::LOGGING_OPTIONS, PostmanAdminController::LOGGING_SECTION );

			add_settings_field( PostmanOptions::TRANSCRIPT_SIZE, __( 'Maximum Transcript Size', 'post-smtp' ), function () : void {
				$this->transcriptSizeInputField();
			}, PostmanAdminController::LOGGING_OPTIONS, PostmanAdminController::LOGGING_SECTION );

			// the Network section
			add_settings_section( PostmanAdminController::NETWORK_SECTION, __( 'Network Settings', 'post-smtp' ), function () : void {
				$this->printNetworkSectionInfo();
			}, PostmanAdminController::NETWORK_OPTIONS );

			add_settings_field( 'connection_timeout', _x( 'TCP Connection Timeout (sec)', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->connection_timeout_callback();
			}, PostmanAdminController::NETWORK_OPTIONS, PostmanAdminController::NETWORK_SECTION );

			add_settings_field( 'read_timeout', _x( 'TCP Read Timeout (sec)', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->read_timeout_callback();
			}, PostmanAdminController::NETWORK_OPTIONS, PostmanAdminController::NETWORK_SECTION );

			// the Advanced section
			add_settings_section( PostmanAdminController::ADVANCED_SECTION, _x( 'Miscellaneous Settings', 'Configuration Section Title', 'post-smtp' ), function () : void {
				$this->printAdvancedSectionInfo();
			}, PostmanAdminController::ADVANCED_OPTIONS );

			add_settings_field( PostmanOptions::LOG_LEVEL, _x( 'PHP Log Level', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->log_level_callback();
			}, PostmanAdminController::ADVANCED_OPTIONS, PostmanAdminController::ADVANCED_SECTION );

			add_settings_field( PostmanOptions::RUN_MODE, _x( 'Delivery Mode', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->runModeCallback();
			}, PostmanAdminController::ADVANCED_OPTIONS, PostmanAdminController::ADVANCED_SECTION );

			add_settings_field( PostmanOptions::STEALTH_MODE, _x( 'Stealth Mode', 'This mode removes the Postman X-Mailer signature from emails', 'post-smtp' ), function () : void {
				$this->stealthModeCallback();
			}, PostmanAdminController::ADVANCED_OPTIONS, PostmanAdminController::ADVANCED_SECTION );

			add_settings_field( PostmanOptions::TEMPORARY_DIRECTORY, __( 'Temporary Directory', 'post-smtp' ), function () : void {
				$this->temporaryDirectoryCallback();
			}, PostmanAdminController::ADVANCED_OPTIONS, PostmanAdminController::ADVANCED_SECTION );

			// Notifications
			add_settings_section( PostmanAdminController::NOTIFICATIONS_SECTION, _x( 'Notifications Settings', 'Configuration Section Title', 'post-smtp' ), function () : void {
				$this->printNotificationsSectionInfo();
			}, PostmanAdminController::NOTIFICATIONS_OPTIONS );

			add_settings_field( PostmanOptions::NOTIFICATION_SERVICE, _x( 'Notification Service', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->notification_service_callback();
			}, PostmanAdminController::NOTIFICATIONS_OPTIONS, PostmanAdminController::NOTIFICATIONS_SECTION );

			// Pushover
			add_settings_section( 'pushover_credentials', _x( 'Pushover Credentials', 'Configuration Section Title', 'post-smtp' ), function () : void {
				$this->printNotificationsSectionInfo();
			}, PostmanAdminController::NOTIFICATIONS_PUSHOVER_CRED );

			add_settings_field( PostmanOptions::PUSHOVER_USER, _x( 'Pushover User Key', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->pushover_user_callback();
			}, PostmanAdminController::NOTIFICATIONS_PUSHOVER_CRED, 'pushover_credentials' );

			add_settings_field( PostmanOptions::PUSHOVER_TOKEN, _x( 'Pushover App Token', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->pushover_token_callback();
			}, PostmanAdminController::NOTIFICATIONS_PUSHOVER_CRED, 'pushover_credentials' );

			// Slack
			add_settings_section( 'slack_credentials', _x( 'Slack Credentials', 'Configuration Section Title', 'post-smtp' ), function () : void {
				$this->printNotificationsSectionInfo();
			}, PostmanAdminController::NOTIFICATIONS_SLACK_CRED );

			add_settings_field( PostmanOptions::SLACK_TOKEN, _x( 'Slack Webhook', 'Configuration Input Field', 'post-smtp' ), function () : void {
				$this->slack_token_callback();
			}, PostmanAdminController::NOTIFICATIONS_SLACK_CRED, 'slack_credentials' );

            add_settings_field( PostmanOptions::NOTIFICATION_USE_CHROME, _x( 'Push to chrome extension', 'Configuration Input Field', 'post-smtp' ), function () : void {
													$this->notification_use_chrome_callback();
												}, PostmanAdminController::NOTIFICATIONS_OPTIONS, PostmanAdminController::NOTIFICATIONS_SECTION );

            add_settings_field( PostmanOptions::NOTIFICATION_CHROME_UID, _x( 'Chrome Extension UID', 'Configuration Input Field', 'post-smtp' ), function () : void {
													$this->notification_chrome_uid_callback();
												}, PostmanAdminController::NOTIFICATIONS_OPTIONS, PostmanAdminController::NOTIFICATIONS_SECTION );

		}
	}

	/**
	 * 	 * Print the Transport section info
	 *
	 * @return void
	 */
	public function printTransportSectionInfo(): void {
		print __( 'Choose SMTP or a vendor-specific API:', 'post-smtp' );
	}
	public function printLoggingSectionInfo(): void {
		print __( 'Configure the delivery audit log:', 'post-smtp' );
	}

	/**
	 * 	 * Print the Section text
	 *
	 * @return void
	 */
	public function printMessageFromSectionInfo(): void {
		print sprintf( __( 'This address, like the <b>letterhead</b> printed on a letter, identifies the sender to the recipient. Change this when you are sending on behalf of someone else, for example to use Google\'s <a href="%s">Send Mail As</a> feature. Other plugins, especially Contact Forms, may override this field to be your visitor\'s address.', 'post-smtp' ), 'https://support.google.com/mail/answer/22370?hl=en' );
	}

	/**
	 * 	 * Print the Section text
	 *
	 * @return void
	 */
	public function printMessageSectionInfo(): void {
		print __( 'Separate multiple <b>to</b>/<b>cc</b>/<b>bcc</b> recipients with commas.', 'post-smtp' );
	}

	/**
	 * 	 * Print the Section text
	 *
	 * @return void
	 */
	public function printNetworkSectionInfo(): void {
		print __( 'Increase the timeouts if your host is intermittenly failing to send mail. Be careful, this also correlates to how long your user must wait if the mail server is unreachable.', 'post-smtp' );
	}

	/**
	 * 	 * Print the Section text
	 *
	 * @return void
	 */
	public function printAdvancedSectionInfo(): void {
	}

	/**
	 * 	 * Print the Section text
	 *
	 * @return void
	 */
	public function printNotificationsSectionInfo(): void {
	}

	/**
	 * 	 * Print the Section text
	 *
	 * @return void
	 */
	public function printAdditionalHeadersSectionInfo(): void {
		print __( 'Specify custom headers (e.g. <code>X-MC-Tags: wordpress-site-A</code>), one per line. Use custom headers with caution as they can negatively affect your Spam score.', 'post-smtp' );
	}

	/**
	 * 	 * Print the Email Validation Description
	 *
	 * @return void
	 */
	public function printEmailValidationSectionInfo(): void {
		print __( 'E-mail addresses can be validated before sending e-mail, however this may fail with some newer domains.', 'post-smtp' );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function transport_type_callback(): void {
		$transportType = $this->options->getTransportType();
		printf( '<select id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TRANSPORT_TYPE );
		foreach ( PostmanTransportRegistry::getInstance()->getTransports() as $transport ) {
			printf( '<option class="input_tx_type_%1$s" value="%1$s" %3$s>%2$s</option>', $transport->getSlug(), $transport->getName(), $transportType == $transport->getSlug() ? 'selected="selected"' : '' );
		}
		print '</select>';
	}

    /**
     * Get the settings option array and print one of its values
     *
     * @return void
     */
    public function smtp_mailer_callback(): void {
        $smtp_mailers = PostmanOptions::SMTP_MAILERS;
        $current_smtp_mailer = $this->options->getSmtpMailer();
        printf( '<select id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, 'smtp_mailers' );
        foreach ( $smtp_mailers as $key => $smtp_mailer ) {
            printf( '<option class="input_tx_type_%1$s" value="%1$s" %3$s>%2$s</option>', $key, $smtp_mailer, $current_smtp_mailer == $key ? 'selected="selected"' : '' );
        }
        print '</select>';
        ?>
        <p class="description" id="mailer-type-description"><?php _e( 'Beta Feature: Change this to <strong>PHPMailer</strong> only if you see <code>wp_mail</code> conflict message, conflicts when another plugin is activated, and <strong><u>sometimes</u></strong> spam issues.', 'post-smtp' ); ?></p>
        <?php
    }

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function sender_name_callback(): void {
		printf( '<input type="text" id="input_sender_name" name="postman_options[sender_name]" value="%s" size="40" />', null !== $this->options->getMessageSenderName() ? esc_attr( $this->options->getMessageSenderName() ) : '' );
	}

	/**
	 * @return void
	 */
	public function prevent_from_name_override_callback(): void {
		$enforced = $this->options->isPluginSenderNameEnforced();
		printf( '<input type="checkbox" id="input_prevent_sender_name_override" name="postman_options[prevent_sender_name_override]" %s /> %s', $enforced ? 'checked="checked"' : '', __( 'Prevent <b>plugins</b> and <b>themes</b> from changing this', 'post-smtp' ) );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function from_email_callback(): void {
		printf( '<input type="email" id="input_sender_email" name="postman_options[sender_email]" value="%s" size="40" class="required" placeholder="%s"/>', null !== $this->options->getMessageSenderEmail() ? esc_attr( $this->options->getMessageSenderEmail() ) : '', __( 'Required', 'post-smtp' ) );
	}

	/**
	 * 	 * Print the Section text
	 *
	 * @return void
	 */
	public function printMessageSenderSectionInfo(): void {
		print sprintf( __( 'This address, like the <b>return address</b> printed on an envelope, identifies the account owner to the SMTP server.', 'post-smtp' ), 'https://support.google.com/mail/answer/22370?hl=en' );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function prevent_from_email_override_callback(): void {
		$enforced = $this->options->isPluginSenderEmailEnforced();
		printf( '<input type="checkbox" id="input_prevent_sender_email_override" name="postman_options[prevent_sender_email_override]" %s /> %s', $enforced ? 'checked="checked"' : '', __( 'Prevent <b>plugins</b> and <b>themes</b> from changing this', 'post-smtp' ) );
	}

	/**
	 * 	 * Shows the Mail Logging enable/disabled option
	 *
	 * @return void
	 */
	public function loggingStatusInputField(): void {
		// isMailLoggingAllowed
		$disabled = '';
		if ( ! $this->options->isMailLoggingAllowed() ) {
			$disabled = 'disabled="disabled" ';
		}
		printf( '<select ' . $disabled . 'id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::MAIL_LOG_ENABLED_OPTION );
		printf( '<option value="%s" %s>%s</option>', PostmanOptions::MAIL_LOG_ENABLED_OPTION_YES, $this->options->isMailLoggingEnabled() ? 'selected="selected"' : '', __( 'Yes', 'post-smtp' ) );
		printf( '<option value="%s" %s>%s</option>', PostmanOptions::MAIL_LOG_ENABLED_OPTION_NO, $this->options->isMailLoggingEnabled() ? '' : 'selected="selected"', __( 'No', 'post-smtp' ) );
		printf( '</select>' );
	}
	public function loggingMaxEntriesInputField(): void {
		printf( '<input type="text" id="input_logging_max_entries" name="postman_options[%s]" value="%s"/>', PostmanOptions::MAIL_LOG_MAX_ENTRIES, $this->options->getMailLoggingMaxEntries() );
	}
	public function transcriptSizeInputField(): void {
		$inputOptionsSlug = PostmanOptions::POSTMAN_OPTIONS;
		$inputTranscriptSlug = PostmanOptions::TRANSCRIPT_SIZE;
		$inputValue = $this->options->getTranscriptSize();
		$inputDescription = __( 'Change this value if you can\'t see the beginning of the transcript because your messages are too big.', 'post-smtp' );
		printf( '<input type="text" id="input%2$s" name="%1$s[%2$s]" value="%3$s"/><br/><span class="postman_input_description">%4$s</span>', $inputOptionsSlug, $inputTranscriptSlug, $inputValue, $inputDescription );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function reply_to_callback(): void {
		printf( '<input type="text" id="input_reply_to" name="%s[%s]" value="%s" size="40" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::REPLY_TO, null !== $this->options->getReplyTo() ? esc_attr( $this->options->getReplyTo() ) : '' );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function to_callback(): void {
		printf( '<input type="text" id="input_to" name="%s[%s]" value="%s" size="60" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_TO_RECIPIENTS, null !== $this->options->getForcedToRecipients() ? esc_attr( $this->options->getForcedToRecipients() ) : '' );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function cc_callback(): void {
		printf( '<input type="text" id="input_cc" name="%s[%s]" value="%s" size="60" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_CC_RECIPIENTS, null !== $this->options->getForcedCcRecipients() ? esc_attr( $this->options->getForcedCcRecipients() ) : '' );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function bcc_callback(): void {
		printf( '<input type="text" id="input_bcc" name="%s[%s]" value="%s" size="60" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_BCC_RECIPIENTS, null !== $this->options->getForcedBccRecipients() ? esc_attr( $this->options->getForcedBccRecipients() ) : '' );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function headers_callback(): void {
		printf( '<textarea id="input_headers" name="%s[%s]" cols="60" rows="5" >%s</textarea>', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::ADDITIONAL_HEADERS, null !== $this->options->getAdditionalHeaders() ? esc_attr( $this->options->getAdditionalHeaders() ) : '' );
	}

	/**
	 * @return void
	 */
	public function disable_email_validation_callback(): void {
		$disabled = $this->options->isEmailValidationDisabled();
		printf( '<input type="checkbox" id="%2$s" name="%1$s[%2$s]" %3$s /> %4$s', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::DISABLE_EMAIL_VALIDAITON, $disabled ? 'checked="checked"' : '', __( 'Disable e-mail validation', 'post-smtp' ) );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function log_level_callback(): void {
		$inputDescription = sprintf( __( 'Log Level specifies the level of detail written to the <a target="_blank" href="%s">WordPress Debug log</a> - view the log with <a target-"_new" href="%s">Debug</a>.', 'post-smtp' ), 'https://codex.wordpress.org/Debugging_in_WordPress', 'https://wordpress.org/plugins/debug/' );
		printf( '<select id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::LOG_LEVEL );
		$currentKey = $this->options->getLogLevel();
		$this->printSelectOption( __( 'Off', 'post-smtp' ), PostmanLogger::OFF_INT, $currentKey );
		$this->printSelectOption( __( 'Trace', 'post-smtp' ), PostmanLogger::TRACE_INT, $currentKey );
		$this->printSelectOption( __( 'Debug', 'post-smtp' ), PostmanLogger::DEBUG_INT, $currentKey );
		$this->printSelectOption( __( 'Info', 'post-smtp' ), PostmanLogger::INFO_INT, $currentKey );
		$this->printSelectOption( __( 'Warning', 'post-smtp' ), PostmanLogger::WARN_INT, $currentKey );
		$this->printSelectOption( __( 'Error', 'post-smtp' ), PostmanLogger::ERROR_INT, $currentKey );
		printf( '</select><br/><span class="postman_input_description">%s</span>', $inputDescription );
	}

	public function notification_service_callback(): void {
		$inputDescription =  __( 'Select the notification service you want to recieve alerts about failed emails.' );
		printf( '<select id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::NOTIFICATION_SERVICE );
		$currentKey = $this->options->getNotificationService();

		$this->printSelectOption( __( 'Email', 'post-smtp' ), 'default', $currentKey );
		$this->printSelectOption( __( 'Pushover', 'post-smtp' ), 'pushover', $currentKey );
		$this->printSelectOption( __( 'Slack', 'post-smtp' ), 'slack', $currentKey );
		printf( '</select><br/><span class="postman_input_description">%s</span>', $inputDescription );
	}

	public function notification_use_chrome_callback(): void {
        $value = $this->options->useChromeExtension();
        printf( '<input type="checkbox" id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]" %3$s />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::NOTIFICATION_USE_CHROME, $value ? 'checked="checked"' : '' );
    }

    public function notification_chrome_uid_callback(): void {
        printf( '<input type="password" id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::NOTIFICATION_CHROME_UID, PostmanUtils::obfuscatePassword( $this->options->getNotificationChromeUid() ) );
    }

	public function pushover_user_callback(): void {
		printf( '<input type="password" id="pushover_user" name="%s[%s]" value="%s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PUSHOVER_USER, $this->options->getPushoverUser() );
	}

	public function pushover_token_callback(): void {
		printf( '<input type="password" id="pushover_token" name="%s[%s]" value="%s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PUSHOVER_TOKEN, $this->options->getPushoverToken() );
	}

	public function slack_token_callback(): void {
		printf( '<input type="password" id="slack_token" name="%s[%s]" value="%s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::SLACK_TOKEN, $this->options->getSlackToken() );
		echo '<a target="_blank" href="https://slack.postmansmtp.com/">' . __( 'Get your webhook URL here', 'post-smtp' ) . '</a>';

	}

	/**
	 * @param int|string $optionKey
	 */
	private function printSelectOption( $label, $optionKey, $currentKey ): void {
		$optionPattern = '<option value="%1$s" %2$s>%3$s</option>';
		printf( $optionPattern, $optionKey, $optionKey == $currentKey ? 'selected="selected"' : '', $label );
	}
	public function runModeCallback(): void {
		$inputDescription = __( 'Delivery mode offers options useful for developing or testing.', 'post-smtp' );
		printf( '<select id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::RUN_MODE );
		$currentKey = $this->options->getRunMode();
		$this->printSelectOption( _x( 'Log Email and Send', 'When the server is online to the public, this is "Production" mode', 'post-smtp' ), PostmanOptions::RUN_MODE_PRODUCTION, $currentKey );
		$this->printSelectOption( __( 'Log Email and Delete', 'post-smtp' ), PostmanOptions::RUN_MODE_LOG_ONLY, $currentKey );
		$this->printSelectOption( __( 'Delete All Emails', 'post-smtp' ), PostmanOptions::RUN_MODE_IGNORE, $currentKey );
		printf( '</select><br/><span class="postman_input_description">%s</span>', $inputDescription );
	}

	public function stealthModeCallback(): void {
		printf( '<input type="checkbox" id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]" %3$s /> %4$s', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::STEALTH_MODE, $this->options->isStealthModeEnabled() ? 'checked="checked"' : '', __( 'Remove the Postman X-Header signature from messages', 'post-smtp' ) );
	}

	public function temporaryDirectoryCallback(): void {
		$inputDescription = __( 'Lockfiles are written here to prevent users from triggering an OAuth 2.0 token refresh at the same time.' );
		printf( '<input type="text" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TEMPORARY_DIRECTORY, $this->options->getTempDirectory() );
		if ( PostmanState::getInstance()->isFileLockingEnabled() ) {
			printf( ' <span style="color:green">%s</span></br><span class="postman_input_description">%s</span>', __( 'Valid', 'post-smtp' ), $inputDescription );
		} else {
			printf( ' <span style="color:red">%s</span></br><span class="postman_input_description">%s</span>', __( 'Invalid', 'post-smtp' ), $inputDescription );
		}
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function connection_timeout_callback(): void {
		printf( '<input type="text" id="input_connection_timeout" name="%s[%s]" value="%s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::CONNECTION_TIMEOUT, $this->options->getConnectionTimeout() );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function read_timeout_callback(): void {
		printf( '<input type="text" id="input_read_timeout" name="%s[%s]" value="%s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::READ_TIMEOUT, $this->options->getReadTimeout() );
	}

	/**
	 * 	 * Get the settings option array and print one of its values
	 *
	 * @return void
	 */
	public function port_callback( $args ): void {
		printf( '<input type="text" id="input_port" name="postman_options[port]" value="%s" %s placeholder="%s"/>', null !== $this->options->getPort() ? esc_attr( $this->options->getPort() ) : '', isset( $args ['style'] ) ? $args ['style'] : '', __( 'Required', 'post-smtp' ) );
	}
}
