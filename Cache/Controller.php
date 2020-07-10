<?php

namespace WP_Rocket\Engine\DB\Cache;

use WP_Rocket\Admin\Options_Data;

/**
 * WP Rocket Cache Controller.
 *
 * @since 1.0.0
 */
class Controller {
	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Schema constructor.
	 *
	 * @param Options_Data $options     WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Invalidate full cache (set expiry to past date).
	 */
	public function invalidate_full_cache() {
		Model::invalidate_full_cache();
	}

	/**
	 * Get expired cache based on expiry field.
	 *
	 * @return array
	 */
	public function get_expired_cache() {
		$expired_cache_count = Model::get_expired_cache( true );
		$expired_cache_list  = Model::get_expired_cache( false, 0, 20 );

		return $expired_cache_list;
	}

	/**
	 * Insert / Update cache row into DB.
	 *
	 * @param  array $record Data to be saved.
	 * @return void
	 */
	public function update( array $record ) {
		$cache_exists = Model::get_by_path( $record['path'] );
		$cache        = new Model( (object) $record );

		if ( isset( $cache_exists ) ) {
			$cache->set( 'id', $cache_exists->get( 'id' ) );
		}

		$cache_lifespan = $this->get_cache_lifespan();
		$cache->set( 'expiry', date( "Y-m-d H:i:s", ( strtotime( date( "Y-m-d H:i:s" ) ) + $cache_lifespan ) ) );
		$cache->save();
	}

	/**
	 * Delete cache from DB based on cache ID.
	 *
	 * @param integer $id Cache Id.
	 * @return void
	 */
	public function delete( $id ) {
		$model = new Model( (object) [ 'id' => $id ] );
		$model->delete();
	}

	/**
	 * Get the cache lifespan in seconds.
	 * If no value is filled in the settings, return 0. It means the purge is disabled.
	 * If the value from the settings is filled but invalid, fallback to the initial value (10 hours).
	 *
	 * @return int The cache lifespan in seconds.
	 */
	private function get_cache_lifespan() {
		$lifespan = $this->options->get( 'purge_cron_interval' );

		if ( ! $lifespan ) {
			return 0;
		}

		$unit = $this->options->get( 'purge_cron_unit' );

		if ( $lifespan < 0 || ! $unit || ! rocket_has_constant( $unit ) ) {
			return 10 * HOUR_IN_SECONDS;
		}

		return $lifespan * rocket_get_constant( $unit );
	}
}
