<?php

namespace WP_Rocket\Engine\DB\Cache;

use WP_Rocket\Engine\DB\Base\Model as BaseModel;

/**
 * WP Rocket Cache Model class.
 *
 * @since 1.0.0
 */
class Model extends BaseModel {
	/**
	 * Cache id.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Cache url.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Cache path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * User id or NULL.
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Cache expiration date.
	 *
	 * @var date
	 */
	protected $expiry;

	/**
	 * Cache last modified date.
	 *
	 * @var date
	 */
	protected $date_upd;

	/**
	 * Database table name without WP prefix.
	 *
	 * @var string
	 */
	public static $table = 'wpr_cache';

	/**
	 * Database object definition.
	 *
	 * @see ObjectModel::$definition
	 */
	protected $definition = [
		'table'   => 'wpr_cache',
		'primary' => 'id',
		'fields'  => [
			'url'      => [ 'type' => self::TYPE_STRING, 'validate' => 'isURL', 'required' => true ],
			'path'     => [ 'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true ],
			'user_id'  => [ 'type' => self::TYPE_INT, 'validate' => 'isInt', 'default' => 0 ],
			'expiry'   => [ 'type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true ],
			'date_upd' => [ 'type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true ],
		],
	];

	/**
	 * Constructor mapping the Cache DB values.
	 *
	 * @param stdClass $record Value from DB.
	 */
	public function __construct( $record ) {
		$this->id       = isset( $record->id ) ? $record->id : null;
		$this->url      = isset( $record->url ) ? $record->url : null;
		$this->path     = isset( $record->path ) ? $record->path : null;
		$this->user_id  = isset( $record->user_id ) ? $record->user_id : null;
		$this->expiry   = isset( $record->expiry ) ? $record->expiry : false;
		$this->date_upd = isset( $record->date_upd ) ? $record->date_upd : null;
	}

	/**
	 * Get expired cache based on expiry field.
	 *
	 * @return mixed Array of Cache Model.
	 */
	public static function get_expired_cache( $count = false, $p = 0, $n = 10 ) {
		$result = self::get_all_by( self::$table, 'expiry', '%s', 'NOW()', '<=', $count, $p, $n, 'expiry' );
		if ( $count ) {
			return (int) $result;
		}

		$expired_cache = [];
		foreach ( $result as $row ) {
			$expired_cache[] = new self( $row );
		}
		return $result;
	}

	/**
	 * Get Cache Model By Path.
	 *
	 * @return Model
	 */
	public static function get_by_path( $path ) {
		$result = self::get_by( self::$table, 'path', '%s', $path );
		if ( ! empty( $result ) ) {
			return new self( $result );
		}
		return null;
	}

	/**
	 * Invalidate full cache. Set expiry column to now - 1 hour.
	 *
	 * @return bool
	 */
	public static function invalidate_full_cache() {
		return self::update_all_column_values( self::$table, 'expiry', date( "Y-m-d H:i:s", ( strtotime( date( "Y-m-d H:i:s" ) ) - 3600 ) ) );
	}
}
