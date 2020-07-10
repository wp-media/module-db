<?php
namespace  WP_Rocket\Engine\DB;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket DB
 *
 * @since 1.0.0
 */
class AdminServiceProvider extends AbstractServiceProvider {
	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'schema',
		'schema_subscriber',
	];

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register() {
		global $wpdb;
		$schema_config = [
			'version'     => '1.0.0',
			'option_name' => 'wpr_cache_db_version',
			'db_tables'   => [
				'wpr_cache' => [
					'table_name'   => $wpdb->prefix . 'wpr_cache',
					'schema_query' => "CREATE TABLE `{$wpdb->prefix}wpr_cache` (
						`id`       INT(6) UNSIGNED AUTO_INCREMENT,
						`url`      VARCHAR(2500) NOT NULL,
						`path`     VARCHAR(2500) NOT NULL,
						`user_id`  INT(6) UNSIGNED NOT NULL DEFAULT 0,
						`expiry`   TIMESTAMP,
						`date_upd` TIMESTAMP,
						PRIMARY KEY  (id),
						INDEX path (`path`),
						INDEX url_path (`url`, `path`),
						INDEX expired (`expiry`, `date_upd`)
						)",
				],
				'wpr_critical_css' => [
					'table_name'   => $wpdb->prefix . 'wpr_critical_css',
					'schema_query' => "CREATE TABLE {$wpdb->prefix}wpr_critical_css (
						id       INT(6) UNSIGNED AUTO_INCREMENT,
						post_id  INT(6) UNSIGNED,
						path     VARCHAR(2500) NOT NULL,
						date_upd TIMESTAMP,
						PRIMARY KEY  (id)
						)",
				],
			],
		];

		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'schema', 'WP_Rocket\Engine\DB\Schema' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'options_api' ) )
			->withArgument( $schema_config );
		$this->getContainer()->share( 'schema_subscriber', 'WP_Rocket\Engine\DB\Subscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'schema' ) );
	}
}
