<?php
namespace WP_Rocket\Engine\DB;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the DB feature
 *
 * @since 1.0
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Schema instance
	 *
	 * @var Schema
	 */
	private $schema;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options, Schema $schema ) {
		$this->options = $options;
		$this->schema  = $schema;
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
			'admin_init' => 'maybe_upgrade_schema',
		];
	}

	/**
	 * Schema create / upgrade
	 *
	 * @since  1.0.0
	 *
	 */
	public function maybe_upgrade_schema() {
		$this->schema->maybe_upgrade_schema();
	}
}
