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
	 * Delete a row identified by the primary key.
	 *
	 * @return bool
	 */
	public function delete() {
		global $wpdb;

		if ( $this->id <= 0 ) {
			return false;
		}

		return (bool) $wpdb->query( $wpdb->prepare( "DELETE FROM " . $this->definition['table'] ." WHERE " . $this->definition['primary'] . "= %d", $this->id ) );
	}

	/**
	 * Saves current object to database (add or update).
	 *
	 * @return bool Insertion result
	 */
	public function save() {
		return (int) $this->id > 0 ? $this->update() : $this->add();
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
	public function update() {
		global $wpdb;

		if ( $this->id <= 0 ) {
			return false;
		}

		return (bool) $wpdb->update(
			$this->definition['table'],
			$this->get_fields(),
			array( $this->definition['primary'] => $this->id ),
			$this->get_fields_type(),
			'%d'
		);
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
