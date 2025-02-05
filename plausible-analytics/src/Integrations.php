<?php

/**
 * Plausible Analytics | Integrations
 * @since      2.1.0
 * @package    WordPress
 * @subpackage Plausible Analytics
 */

namespace Plausible\Analytics\WP;

class Integrations {
	const SCRIPT_WRAPPER = '<script defer id="plausible-analytics-integration-tracking">document.addEventListener("DOMContentLoaded", () => { %s });</script>';

	/**
	 * Build class.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Run available integrations.
	 * @return void
	 */
	private function init() {
		// WooCommerce
		if ( self::is_wc_active() ) {
			new Integrations\WooCommerce();
		}

		// Easy Digital Downloads
		if ( self::is_edd_active() ) {
			// new Integrations\EDD();
		}

		if ( self::is_form_submit_active() ) {
			new Integrations\FormSubmit();
		}
	}

	/**
	 * Checks if WooCommerce is installed and activated.
	 * @return bool
	 */
	public static function is_wc_active() {
		return apply_filters( 'plausible_analytics_integrations_woocommerce', function_exists( 'WC' ) && Helpers::is_enhanced_measurement_enabled( 'revenue' ) );
	}

	/**
	 * Checks if Easy Digital Downloads is installed and activated.
	 * @return bool
	 */
	public static function is_edd_active() {
		return apply_filters( 'plausible_analytics_integrations_edd', function_exists( 'EDD' ) && Helpers::is_enhanced_measurement_enabled( 'revenue' ) );
	}

	/**
	 * Check if Form Submissions option is enabled in Enhanced Measurements.
	 * @return mixed|null
	 */
	public static function is_form_submit_active() {
		return apply_filters(
			'plausible_analytics_integrations_form_submit',
			Helpers::is_enhanced_measurement_enabled( 'form-completions' )
		);
	}
}
