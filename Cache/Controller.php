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
	public function insert( array $record ) {
		$model = new Model( (object) $record );
		$model->save();
	}

	/**
	 * Delete cache from DB based on cache ID.
	 *
	 * @param integer $id Cache Id.
	 * @return void
	 */
	public function delete( int $id ) {
		$model = new Model( (object) [ 'id' => $id ] );
		$model->delete();
	}
}
