<?php

namespace WP_Rocket\Engine\DB;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * WP Rocket DB Schema class.
 *
 * @since 1.0.0
 */
class Schema {
	/**
	 * Charset Collate.
	 *
	 * @var string
	 */
	protected $charset_collate;

	/**
	 * Configuration array containing database table name and structure.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * WP Rocket Options_API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Schema constructor.
	 *
	 * @param Options_Data $options     WP Rocket Options instance.
	 * @param Options      $options_api Options instance.
	 * @param array        $config      Configuration array containing database table name and structure.
	 */
	public function __construct( Options_Data $options, Options $options_api, $config ) {
		$this->options     = $options;
		$this->options_api = $options_api;
		$this->config      = $config;
		$this->init_properties();
	}

	/**
	 * Maybe create/upgrade the table in the database.
	 *
	 * @since 1.0.0
	 */
	public function maybe_upgrade_schema() {
		if ( ! $this->has_version_changed() ) {
			return;
		}

		// Create the tables.
		$this->create_schemas();
	}

	/**
	 * Check the db version - it does a hard check as well
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_version_changed() {
		$version = $this->options->get( $this->config['option_name'], 0 );
		return $version !== $this->config['version'];
	}

	/**
	 * Initialize the properties.
	 *
	 * @since 1.0.0
	 */
	protected function init_properties() {
		global $wpdb;

		$this->charset_collate = $wpdb->get_charset_collate();
	}

	/**
	 * Create/Upgrade the table in the database.
	 *
	 * @since  1.0.0
	 */
	protected function create_schemas() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $this->config['db_tables'] as $table_name => $table_info ) {
			$this->append_charset_collate( $table_name );
			dbDelta( $this->config['db_tables'][ $table_name ]['schema_query'] );
		}

		$this->options->set( $this->config['option_name'], $this->config['version'] );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Append the Charset Collate string to each SQL item.
	 *
	 * @param string $table_name Database table name.
	 *
	 * @since 1.0.0
	 */
	protected function append_charset_collate( $table_name ) {
		$this->config['db_tables'][ $table_name ]['schema_query'] .= $this->charset_collate . ';';
	}

	/**
	 * Tell if the given table exists.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $table_name Full name of the table (with DB prefix).
	 * @return bool
	 */
	public function table_exists( $table_name ) {
		global $wpdb;

		$escaped_table = $this->esc_like( $table_name );
		$result        = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $escaped_table ) );

		return $result === $table_name;
	}

	/**
	 * First half of escaping for LIKE special characters % and _ before preparing for MySQL.
	 * Use this only before wpdb::prepare() or esc_sql(). Reversing the order is very bad for security.
	 *
	 * Example Prepared Statement:
	 *     $wild = '%';
	 *     $find = 'only 43% of planets';
	 *     $like = $wild . $wpdb->esc_like( $find ) . $wild;
	 *     $sql  = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_content LIKE %s", $like );
	 *
	 * Example Escape Chain:
	 *     $sql  = esc_sql( $wpdb->esc_like( $input ) );
	 *
	 * @since  1.0.0
	 *
	 * @param  string $text The raw text to be escaped. The input typed by the user should have no extra or deleted slashes.
	 * @return string       Text in the form of a LIKE phrase. The output is not SQL safe. Call $wpdb::prepare() or real_escape next.
	 */
	public function esc_like( $text ) {
		global $wpdb;

		if ( method_exists( $wpdb, 'esc_like' ) ) {
			// Introduced in WP 4.0.0.
			return $wpdb->esc_like( $text );
		}

		return addcslashes( $text, '_%\\' );
	}
}
