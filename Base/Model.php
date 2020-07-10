<?php

namespace WP_Rocket\Engine\DB\Base;

/**
 * WP Rocket Base Model class.
 *
 * @since 1.0.0
 */
abstract class Model {

	/**
	 * ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Date updated.
	 *
	 * @var date
	 */
	protected $date_upd;

	/**
	 * List of field types.
	 */
	const TYPE_INT    = '%d';
	const TYPE_BOOL   = '%d';
	const TYPE_STRING = '%s';
	const TYPE_FLOAT  = '%d';
	const TYPE_DATE   = '%s';

	/**
	 * Database object definition.
	 *
	 * @var array Contains object definition
	 */
	protected $definition = [];

	/**
	 * Contructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Getters.
	 *
	 * @param  string $column Column to get the value.
	 * @return mixed
	 */
	public function get( $column ) {
		if ( ! property_exists( $this, $column ) ) {
			return null;
		}
		return $this->$column;
	}

	/**
	 * Setters.
	 *
	 * @param  string $column Column to set the value.
	 * @param  mixed  $value  Value to be set on the column.
	 * @return mixed
	 */
	public function set( $column, $value ) {
		if ( ! property_exists( $this, $column ) ) {
			return null;
		}
		$this->$column = $value;
		return $this->$column;
	}

	/**
	 * Retrieve a row by specific column value.
	 *
	 * @param  string $table       Database table.
	 * @param  string $column      Database column.
	 * @param  string $column_type Database column type.
	 * @param  string $value       Column value.
	 * @return array
	 */
	public static function get_by( $table, $column, $column_type, $value ) {
		global $wpdb;

		$column = esc_sql( $column );
		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $table . " WHERE $column = $column_type LIMIT 1;", $value ), OBJECT );

		return $result;
	}


	/**
	 * Retrieve all rows by specific column value with operator, limit and order by.
	 *
	 * @param  string $table       Database table.
	 * @param  string $column      Database column.
	 * @param  string $column_type Database column type.
	 * @param  string $value       Database column value.
	 * @param  string $operator    Where condition operator.
	 * @param  bool   $count       Select count.
	 * @param  int    $page        SQL Limit page no.
	 * @param  int    $limit       SQL Limit value .
	 * @param  string $order_by    SQL Order by.
	 * @param  string $order_value SQL Order value.
	 * @return array
	 */
	public static function get_all_by( $table, $column, $column_type, $value, $operator = '=', $count = false, $page = 0, $limit = 10, $order_by = null, $order_value = 'asc' ) {
		global $wpdb;

		$column      = esc_sql( $column );
		$operator    = esc_sql( $operator );

		if ( ! empty( $order_by ) ) {
			$order_by_value = 'ORDER BY ' . esc_sql( $order_by ) . ' ' . $order_value;
		}

		$limit = 'LIMIT ' . (int) $page . ', ' . (int) $limit;

		if ( $count ) {
			$result = $wpdb->get_row( $wpdb->prepare( 'SELECT count(*) as total FROM ' . $wpdb->prefix . $table . " WHERE $column $operator $column_type $order_by_value $limit;", $value ), OBJECT );
			return $result->total;
		}

		$result = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $table . " WHERE $column $operator $column_type $order_by_value $limit;", $value ), OBJECT );
		return $result;
	}

	/**
	 * Delete a row identified by the primary key.
	 *
	 * @return bool
	 */
	public function delete() {
		global $wpdb;

		if ( $this->id <= 0 ) {
			return false;
		}

		return (bool) $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . $this->definition['table'] ." WHERE " . $this->definition['primary'] . "= %d", $this->id ) );
	}

	/**
	 * Saves current object to database (add or update).
	 *
	 * @return bool Insertion result
	 */
	public function save() {
		return (int) $this->id > 0 ? $this->update_by_id() : $this->add();
	}

	/**
	 * Adds current object to the database.
	 *
	 * @return int Insertion id.
	 *
	 */
	public function add() {
		global $wpdb;

		if ( isset( $this->id ) ) {
			unset( $this->id );
		}

		$this->date_upd = date( 'Y-m-d H:i:s' );

		$wpdb->insert(
			$wpdb->prefix . $this->definition['table'],
			$this->get_fields(),
			$this->get_fields_type()
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update a row.
	 *
	 * @return bool
	 */
	public function update_by_id() {
		global $wpdb;

		if ( $this->id <= 0 ) {
			return false;
		}

		return (bool) $wpdb->update(
			$wpdb->prefix . $this->definition['table'],
			$this->get_fields(),
			array( $this->definition['primary'] => $this->id ),
			$this->get_fields_type(),
			'%d'
		);
	}

	/**
	 * Update all columns in the table.
	 *
	 * @return bool
	 */
	public static function update_all_column_values( $table, $column = null, $value = null ) {
		global $wpdb;

		if ( empty( $column ) ) {
			return false;
		}

		return (bool) $wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . $table ." SET $column = %s WHERE 1 ", $value ) );
	}

	/**
	 * Prepare fields for ObjectModel class (add, update)
	 *
	 * @return array All object fields
	 */
	public function get_fields() {
		$fields = [];

		// Set primary key in fields
		if ( isset( $this->id ) ) {
			$fields[ $this->definition['primary'] ] = $this->id;
		}

		foreach ( $this->definition['fields'] as $field => $data ) {
			$value            = $this->$field;
			$fields[ $field ] = $value;
		}

		return $fields;
	}

	/**
	 * Prepare fields types for ObjectModel class (add, update)
	 *
	 * @return array All object fields
	 */
	public function get_fields_type() {
		$fields_type = [];

		// Set primary key in fields
		if ( isset( $this->id ) ) {
			$fields_type[] = '%d';
		}

		foreach ( $this->definition['fields'] as $field => $data ) {
			$fields_type[] = $data['type'];
		}

		return $fields_type;
	}
}
