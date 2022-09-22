<?php
/**
 * Plugin Name: Ngrok Bedrock
 * Description: Use relative links to expose local bedrock to the web using ngrok.
 * Version: 0.0.1
 * Author: Mathieu Zwygart
 * Author URI: https://zwygart.net
 * License: GPLv2+
 */

use Env\Env;

class Ngrok_Bedrock {

	private $site_url;

	public function __construct(){

		$this->site_url = env('WP_HOME');

		// Bail if environment is staging or production
		if( env('WP_ENV') != 'development') {
			return false;
		}

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	public function template_redirect() {
		// Use URL parameter wp_ngrok_autoload to avoid infinite loop
		if ( ! isset( $_GET['wp_ngrok_autoload'] ) ) {
			// Determine protocol
			$protocol = is_ssl() ? 'https://' : 'http://';
			$site_url_with_protocol = str_replace( array( 'http://', 'https://' ), $protocol, $this->site_url );

			// Get current page markup
			$html = file_get_contents( add_query_arg( 'wp_ngrok_autoload', 1, $protocol . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ) );

			// Make links relatives
			$html = str_replace( $site_url_with_protocol, wp_make_link_relative( $site_url_with_protocol ), $html );

			// Fix "home" empty href
			$html = str_replace( 'href=""', 'href="/"', $html );

			echo $html;
			exit;
		}
	}
}

new Ngrok_Bedrock;
