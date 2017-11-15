<?php

class PostmanPluginFeedback {
	function __construct() {
		add_filter( 'plugin_action_links_' . plugin_basename( POST_BASE ), array( $this, 'insert_deactivate_link_id' ) );
		add_action( 'wp_ajax_post_user_feedback', array( $this, 'post_user_feedback' ) );

		global $pagenow;
		if ( 'plugins.php' === $pagenow ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_footer', array( $this, 'add_deactivation_dialog' ) );
		}
	}

	function load_scripts() {
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_register_script( 'post-feedback', plugins_url( 'script/feedback/feedback.js', POST_BASE ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ), fasle, true );
		wp_localize_script( 'post-feedback', 'post_feedback', array( 'admin_ajax' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( 'post-feedback' );
	}

	function post_user_feedback() {
		if ( ! check_ajax_referer() ) {
			die( 'security error' );
		}

		$payload = array(
			'reason' => sanitize_text_field( $_POST['reason'] ),
			'other_input' => isset( $_POST['other_input'] ) ? sanitize_textarea_field( $_POST['other_input'] ) : '',
		);

		if ( isset( $_POST['support'] ) ) {
			$payload['support']['email'] = sanitize_email( $_POST['support']['email'] );
			$payload['support']['title'] = sanitize_email( $_POST['support']['title'] );
			$payload['support']['text'] = sanitize_textarea_field( $_POST['support']['text'] );
		}

		$args = array(
			'body' => $payload,
		);
		$result = wp_remote_post( 'https://postmansmtp.com/feedback', $args );
		die();
	}

	function admin_head() {
		?>
		<style type="text/css">
			.postman-feedback-dialog-form .ui-dialog-buttonset {
				float: none !important;
			}

			#postman-feedback-dialog-skip {
				float: left;
			}

			#postman-feedback-dialog-go, #postman-feedback-dialog-cancel {
				float: right;
			}

			#postman-feedback-dialog-content p {
				font-size: 1.1em;
			}

			#postman-deactivate-reasons textarea {
				margin-top: 10px;
				width: 100%;
				height: 150px;
			}
		</style>
	<?php
	}

	function insert_deactivate_link_id( $links ) {
		$links['deactivate'] = str_replace( '<a', '<a id="postman-plugin-disbale-link"', $links['deactivate'] );

		return $links;
	}

	function add_deactivation_dialog() {
		?>
		<div id="postman-feedback-dialog-content" style="display: none;">
			<p>
				I feel bad to see anyone stop using Post SMTP.<br>
				I would love to get a small feedback from you.
			</p>
			<form>
				<?php wp_nonce_field(); ?>
				<ul id="postman-deactivate-reasons">
					<li class="postman-reason postman-custom-input">
						<label>
							<span><input value="Found a better plugin" type="radio" name="reason" /></span>
							<span><?php _e( 'Found a better plugin', 'postman' ); ?></span>
						</label>
						<div class="postman-reason-input" style="display: none;">
							<textarea name="other_input"></textarea>
						</div>					
					</li>
					<li class="postman-reason">
						<label>
							<span><input value="The plugin didn't work" type="radio" name="reason" /></span>
							<span><?php _e( 'The plugin didn\'t work', 'postman' ); ?></span>
						</label>
					</li>
					<li class="postman-reason postman-custom-input">
						<label>
							<span><input value="Other Reason" type="radio" name="reason" /></span>
							<span><?php _e( 'Other Reason', 'postman' ); ?></span>
						</label>
						<div class="postman-reason-input" style="display: none;">
							<textarea name="other_input"></textarea>
						</div>
					</li>
					<li class="postman-reason postman-custom-input">
						<label>
							<span><input value="Support Ticket" type="radio" name="reason" /></span>
							<span><?php _e( 'Open A support ticket for me', 'postman' ); ?></span>
						</label>
						<div class="postman-reason-input" style="display: none;">
							<input type="email" name="support[email]" placeholder="Your Email Address" required>
							<input type="text" name="support[title]" placeholder="The Title" required>
							<textarea name="support[text]" placeholder="Describe the issue" required></textarea>
						</div>
					</li>														
				</ul>
			</form>
		</div>
	<?php
	}
}
new PostmanPluginFeedback;