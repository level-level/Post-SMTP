<?php

class PostmanWoocommerce {

	private $options;

	public function __construct() {
		$this->set_vars();
		$this->hooks();
	}

	public function set_vars(): void {
		$this->options = PostmanOptions::getInstance ();
	}

	public function hooks(): void {
		add_filter( 'option_woocommerce_email_from_address', function ($from_address, $WC_Email) {
			return $this->set_postman_from_address($from_address, $WC_Email);
		}, 10, 2 );
		add_filter( 'woocommerce_email_from_address', function ($from_address, $WC_Email) {
			return $this->set_postman_from_address($from_address, $WC_Email);
		}, 10, 2 );
		add_filter( 'woocommerce_get_settings_email', function ($settings) : array {
			return $this->overide_email_settings($settings);
		} );
	}

	public function set_postman_from_address( $from_address, $WC_Email ) {
		return $this->options->getMessageSenderEmail();
	}

	/**
	 * @return (bool|mixed|string|string[])[][]
	 *
	 * @psalm-return array{0: array{title: mixed, desc: mixed, type: string, id: string}, 1: array{type: string}, 2: array{type: string, id: string}, 3: array{type: string, id: string}, 4: array{title: mixed, type: string, desc: string, id: string}, 5: array{title: mixed, desc: mixed, id: string, type: string, css: string, default: mixed, autoload: false, desc_tip: true}, 6: array{title: mixed, desc: mixed, id: string, type: string, custom_attributes: array{multiple: string, disabled: string}, css: string, default: mixed, autoload: false, desc_tip: true}, 7: array{type: string, id: string}, 8: array{title: mixed, type: string, desc: string, id: string}, 9: array{title: mixed, desc: mixed, id: string, type: string, css: string, placeholder: mixed, default: string, autoload: false, desc_tip: true}, 10: array{title: mixed, desc: mixed, id: string, css: string, placeholder: mixed, type: string, default: mixed, autoload: false, desc_tip: true}, 11: array{title: mixed, desc: string, id: string, type: string, css: string, default: string, autoload: false, desc_tip: true}, 12: array{title: mixed, desc: string, id: string, type: string, css: string, default: string, autoload: false, desc_tip: true}, 13: array{title: mixed, desc: string, id: string, type: string, css: string, default: string, autoload: false, desc_tip: true}, 14: array{title: mixed, desc: string, id: string, type: string, css: string, default: string, autoload: false, desc_tip: true}, 15: array{type: string, id: string}}
	 */
	public function overide_email_settings( $settings ): array {

		return array(

			array( 'title' => __( 'Email notifications', 'post-smtp' ),  'desc' => __( 'Email notifications sent from WooCommerce are listed below. Click on an email to configure it.', 'post-smtp' ), 'type' => 'title', 'id' => 'email_notification_settings' ),

			array( 'type' => 'email_notification' ),

			array( 'type' => 'sectionend', 'id' => 'email_notification_settings' ),

			array( 'type' => 'sectionend', 'id' => 'email_recipient_options' ),

			array( 'title' => __( 'Email sender options', 'post-smtp' ), 'type' => 'title', 'desc' => '', 'id' => 'email_options' ),

			array(
				'title'    => __( '"From" name', 'post-smtp' ),
				'desc'     => __( 'How the sender name appears in outgoing WooCommerce emails.', 'post-smtp' ),
				'id'       => 'woocommerce_email_from_name',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
				'default'  => esc_attr( get_bloginfo( 'name', 'display' ) ),
				'autoload' => false,
				'desc_tip' => true,
			),

			array(
				'title'             => __( '"From" address', 'post-smtp' ),
				'desc'              => __( 'This is overided by the account configured on Post SMTP plugin configuration.', 'post-smtp' ),
				'id'                => 'woocommerce_email_from_address',
				'type'              => 'email',
				'custom_attributes' => array(
					'multiple' => 'multiple',
					'disabled' => 'true',
				),
				'css'               => 'min-width:300px;',
				'default'           => $this->options->getMessageSenderEmail(),
				'autoload'          => false,
				'desc_tip'          => true,
			),

			array( 'type' => 'sectionend', 'id' => 'email_options' ),

			array( 'title' => __( 'Email template', 'post-smtp' ), 'type' => 'title', 'desc' => sprintf( __( 'This section lets you customize the WooCommerce emails. <a href="%s" target="_blank">Click here to preview your email template</a>.', 'post-smtp' ), wp_nonce_url( admin_url( '?preview_woocommerce_mail=true' ), 'preview-mail' ) ), 'id' => 'email_template_options' ),

			array(
				'title'       => __( 'Header image', 'post-smtp' ),
				'desc'        => __( 'URL to an image you want to show in the email header. Upload images using the media uploader (Admin > Media).', 'post-smtp' ),
				'id'          => 'woocommerce_email_header_image',
				'type'        => 'text',
				'css'         => 'min-width:300px;',
				'placeholder' => __( 'N/A', 'post-smtp' ),
				'default'     => '',
				'autoload'    => false,
				'desc_tip'    => true,
			),

			array(
				'title'       => __( 'Footer text', 'post-smtp' ),
				'desc'        => __( 'The text to appear in the footer of WooCommerce emails.', 'post-smtp' ),
				'id'          => 'woocommerce_email_footer_text',
				'css'         => 'width:300px; height: 75px;',
				'placeholder' => __( 'N/A', 'post-smtp' ),
				'type'        => 'textarea',
				/* translators: %s: site name */
				'default'     => get_bloginfo( 'name', 'display' ),
				'autoload'    => false,
				'desc_tip'    => true,
			),

			array(
				'title'    => __( 'Base color', 'post-smtp' ),
				/* translators: %s: default color */
				'desc'     => sprintf( __( 'The base color for WooCommerce email templates. Default %s.', 'post-smtp' ), '<code>#96588a</code>' ),
				'id'       => 'woocommerce_email_base_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#96588a',
				'autoload' => false,
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Background color', 'post-smtp' ),
				/* translators: %s: default color */
				'desc'     => sprintf( __( 'The background color for WooCommerce email templates. Default %s.', 'post-smtp' ), '<code>#f7f7f7</code>' ),
				'id'       => 'woocommerce_email_background_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#f7f7f7',
				'autoload' => false,
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Body background color', 'post-smtp' ),
				/* translators: %s: default color */
				'desc'     => sprintf( __( 'The main body background color. Default %s.', 'post-smtp' ), '<code>#ffffff</code>' ),
				'id'       => 'woocommerce_email_body_background_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#ffffff',
				'autoload' => false,
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Body text color', 'post-smtp' ),
				/* translators: %s: default color */
				'desc'     => sprintf( __( 'The main body text color. Default %s.', 'post-smtp' ), '<code>#3c3c3c</code>' ),
				'id'       => 'woocommerce_email_text_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#3c3c3c',
				'autoload' => false,
				'desc_tip' => true,
			),

			array( 'type' => 'sectionend', 'id' => 'email_template_options' ),

		);
	}
}
