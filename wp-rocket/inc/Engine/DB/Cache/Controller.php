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
	 * Insert / Update cache row into DB.
	 *
	 * @param  array $record Data to be saved.
	 * @return void
	 */
	public function update( array $record ) {
		$model      = new Model( (object) $record );
		$cache_path = $model->get( 'path' );
		if ( ! empty( $cache_path ) ) {
			$cache_row = $model->get_by( 'path', $cache_path );
			if ( ! empty( $cache_row ) ) {
				$model->set( 'id', $cache_row->id );
			}
		}

		$cache_lifespan = $this->get_cache_lifespan();
		$model->set( 'expiry', date( "Y-m-d H:i:s", ( strtotime( date( "Y-m-d H:i:s" ) ) + $cache_lifespan ) ) );
		$model->save();
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
	public function get_cache_lifespan() {
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
