<?php

namespace WP_Rocket\Engine\DB\Cache;

/**
 * WP Rocket Cache Controller.
 *
 * @since 1.0.0
 */
class Controller {
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
}
