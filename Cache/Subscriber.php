<?php
namespace WP_Rocket\Engine\DB\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the Cache DB feature
 *
 * @since 1.0
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Cache Controller instance
	 *
	 * @var Controller
	 */
	private $cache_controller;

	/**
	 * Constructor
	 *
	 * @param Controller $cache_controller Cache Controller instance.
	 */
	public function __construct( Controller $cache_controller ) {
		$this->cache_controller = $cache_controller;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_cache_db' => [ 'save_cache_db', 10, 1 ],
		];
	}

	/**
	 * Save Cache file / url into db.
	 *
	 * @since  1.0.0
	 *
	 */
	public function save_cache_db( $values ) {
		return $this->cache_controller->update( $values );
	}
}
