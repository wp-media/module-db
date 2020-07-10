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
}
