<?php
/**
 * Plausible Analytics | Upgrades
 *
 * @since      1.3.0
 * @package    WordPress
 * @subpackage Plausible Analytics
 */

namespace Plausible\Analytics\WP\Admin;

use Exception;
use Plausible\Analytics\WP\Admin\Provisioning\Integrations;
use Plausible\Analytics\WP\Client;
use Plausible\Analytics\WP\Helpers;
use Plausible\Analytics\WP\Setup;

/**
 * Class Upgrades
 *
 * @since 1.3.0
 */
class Upgrades {
	/**
	 * Constructor for Upgrades.
	 *
	 * @since  1.3.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'run' ] );
	}

	/**
	 * Register routines for upgrades.
	 * This is intended for automatic upgrade routines having less resource intensive tasks.
	 *
	 * @since  1.3.0
	 * @access public
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @codeCoverageIgnore
	 */
	public function run() {
		$plausible_analytics_version = get_option( 'plausible_analytics_version' );

		// If version doesn't exist, then consider it `1.0.0`.
		if ( ! $plausible_analytics_version ) {
			$plausible_analytics_version = '1.0.0';
		}

		if ( version_compare( $plausible_analytics_version, '1.2.5', '<' ) ) {
			$this->upgrade_to_125();
		}

		if ( version_compare( $plausible_analytics_version, '1.2.6', '<' ) ) {
			$this->upgrade_to_126();
		}

		if ( version_compare( $plausible_analytics_version, '1.3.2', '<' ) ) {
			$this->upgrade_to_132();
		}

		if ( version_compare( $plausible_analytics_version, '2.0.0', '<' ) ) {
			$this->upgrade_to_200();
		}

		if ( version_compare( $plausible_analytics_version, '2.0.3', '<' ) ) {
			$this->upgrade_to_203();
		}

		if ( version_compare( $plausible_analytics_version, '2.1.0', '<' ) ) {
			$this->upgrade_to_210();
		}

		if ( version_compare( $plausible_analytics_version, '2.3.0', '<' ) ) {
			$this->upgrade_to_230();
		}

		if ( version_compare( $plausible_analytics_version, '2.3.1', '<' ) ) {
			$this->upgrade_to_231();
		}

		// Add required upgrade routines for future versions here.
	}

	/**
	 * Upgrade routine for 1.2.5
	 * Cleans Custom Domain related options from database, as it was removed in this version.
	 *
	 * @since  1.2.5
	 * @access public
	 * @return void
	 * @codeCoverageIgnore
	 */
	public function upgrade_to_125() {
		$old_settings = Helpers::get_settings();
		$new_settings = $old_settings;

		if ( isset( $old_settings[ 'custom_domain_prefix' ] ) ) {
			unset( $new_settings[ 'custom_domain_prefix' ] );
		}

		if ( isset( $old_settings[ 'custom_domain' ] ) ) {
			unset( $new_settings[ 'custom_domain' ] );
		}

		if ( isset( $old_settings[ 'is_custom_domain' ] ) ) {
			unset( $new_settings[ 'is_custom_domain' ] );
		}

		if ( ! empty( $old_settings[ 'track_administrator' ] ) && $old_settings[ 'track_administrator' ] === 'true' ) {
			$new_settings[ 'tracked_user_roles' ] = [ 'administrator' ];
		}

		update_option( 'plausible_analytics_settings', $new_settings );

		update_option( 'plausible_analytics_version', '1.2.5' );
	}

	/**
	 * Get rid of the previous "example.com" default for self_hosted_domain.
	 *
	 * @since 1.2.6
	 * @return void
	 * @codeCoverageIgnore
	 */
	public function upgrade_to_126() {
		$old_settings = Helpers::get_settings();
		$new_settings = $old_settings;

		if ( ! empty( $old_settings[ 'self_hosted_domain' ] ) && strpos( $old_settings[ 'self_hosted_domain' ], 'example.com' ) !== false ) {
			$new_settings[ 'self_hosted_domain' ] = '';
		}

		update_option( 'plausible_analytics_settings', $new_settings );

		update_option( 'plausible_analytics_version', '1.2.6' );
	}

	/**
	 * Upgrade to 1.3.2
	 * - Updates the Proxy Resource, Cache URL to be protocol relative.
	 *
	 * @return void
	 * @throws Exception
	 * @codeCoverageIgnore
	 */
	private function upgrade_to_132() {
		$proxy_resources = Helpers::get_proxy_resources();

		$proxy_resources[ 'cache_url' ] = str_replace( [ 'https:', 'http:' ], '', $proxy_resources[ 'cache_url' ] );

		update_option( 'plausible_analytics_proxy_resources', $proxy_resources );

		update_option( 'plausible_analytics_version', '1.3.2' );
	}

	/**
	 * Cleans the settings of the old, unneeded sub-arrays for settings.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	private function upgrade_to_200() {
		$settings     = Helpers::get_settings();
		$toggle_lists = [
			'enhanced_measurements',
			'tracked_user_roles',
			'expand_dashboard_access',
		];

		foreach ( $settings as $option_name => $option_value ) {
			if ( ! is_array( $option_value ) ) {
				continue;
			}

			// For toggle lists, we only need to clean out the no longer needed zero values.
			if ( in_array( $option_name, $toggle_lists ) ) {
				$settings[ $option_name ] = array_filter( $option_value );

				continue;
			}

			// Single toggle.
			$clean_value = array_filter( $option_value );

			// Disabled options are now stored as (more sensible) empty strings instead of empty arrays.
			if ( empty( $clean_value ) ) {
				$settings[ $option_name ] = '';

				continue;
			}

			// Any other value will now default to 'on'.
			$settings[ $option_name ] = 'on';
		}

		/**
		 * Migrate the shared link option for self hosters who use it.
		 */
		if ( ! empty( $settings[ 'self_hosted_domain' ] ) && ! empty( $settings[ 'shared_link' ] ) ) {
			$settings[ 'self_hosted_shared_link' ] = $settings[ 'shared_link' ];
			$settings[ 'shared_link' ]             = '';
		}

		update_option( 'plausible_analytics_settings', $settings );

		update_option( 'plausible_analytics_version', '2.0.0' );

		// No longer need this db entry.
		delete_option( 'plausible_analytics_is_default_settings_saved' );

		// We no longer need to store transient to keep notices dismissed.
		delete_transient( 'plausible_analytics_module_install_failed_notice_dismissed' );
		delete_transient( 'plausible_analytics_proxy_test_failed_notice_dismissed' );
		delete_transient( 'plausible_analytics_notice' );
	}

	/**
	 * Makes sure the View Stats option is enabled for users that previously set a shared link.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	private function upgrade_to_203() {
		$settings = Helpers::get_settings();

		if ( ! empty( $settings[ 'shared_link' ] ) ) {
			$settings[ 'enable_analytics_dashboard' ] = 'on';
		}

		update_option( 'plausible_analytics_settings', $settings );

		update_option( 'plausible_analytics_version', '2.0.3' );
	}

	/**
	 * v2.0.8 and older contained a bug that caused the Enhanced Measurement option to not be an array in some cases.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public function upgrade_to_210() {
		$settings = Helpers::get_settings();

		if ( ! is_array( $settings[ 'enhanced_measurements' ] ) ) {
			$settings[ 'enhanced_measurements' ] = [];
		}

		update_option( 'plausible_analytics_settings', $settings );

		update_option( 'plausible_analytics_version', '2.1.0' );
	}

	/**
	 * If EDD is active and Ecommerce is enabled, create goals after updating the plugin.
	 *
	 * @since              v2.3.0
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore because all we'd be doing is testing the Plugins API.
	 */
	public function upgrade_to_230() {
		$settings = Helpers::get_settings();

		if ( Helpers::is_enhanced_measurement_enabled( 'revenue' ) ) {
			$edd_provisioning = new Provisioning\Integrations\EDD( new Integrations() );
			$provisioning     = new Provisioning();

			// No token entered.
			if ( ! $provisioning->client instanceof Client ) {
				return;
			}

			$provisioning->maybe_create_custom_properties( [], $settings );
			$edd_provisioning->maybe_create_edd_funnel( [], $settings );
		}

		update_option( 'plausible_analytics_version', '2.3.0' );
	}

	/**
	 * Make sure the cron event is scheduled. If it's already scheduled or the Proxy isn't enabled, it'll bail.
	 *
	 * @return void
	 */
	public function upgrade_to_231() {
		$setup = new Setup();

		$setup->activate_cron();

		update_option( 'plausible_analytics_version', '2.3.1' );
	}
}
