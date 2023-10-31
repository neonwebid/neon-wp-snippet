<?php

class QueryBuilder {

	private $db;

	private $query;

	private $query_string;

	public static function instance(): QueryBuilder {
		return new self();
	}

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	public function select( $columns ): QueryBuilder {
		$this->query['select'] = is_array( $columns ) ? implode( ', ', $columns ) : $columns;

		return $this;
	}

	public function from( $table ): QueryBuilder {
		$this->query['from'] = $this->db->prefix . $table;

		return $this;
	}

	public function join( $table, $condition, $type = 'INNER' ): QueryBuilder {
		$table = $this->db->prefix.$table;
		$this->query['join'][] = "{$type} JOIN {$table} ON {$condition}";

		return $this;
	}

	public function where( $condition ): QueryBuilder {
		$this->query['where'] = $condition;

		return $this;
	}

	public function groupBy( $columns ): QueryBuilder {
		$this->query['group_by'] = is_array( $columns ) ? implode( ', ', $columns ) : $columns;

		return $this;
	}

	public function orderBy( $columns, $direction = 'ASC' ): QueryBuilder {
		$this->query['order_by']        = is_array( $columns ) ? implode( ', ', $columns ) : $columns;
		$this->query['order_direction'] = $direction;

		return $this;
	}

	public function limit( $limit ): QueryBuilder {
		$this->query['limit'] = $limit;

		return $this;
	}

	public function offset( $offset ): QueryBuilder {
		$this->query['offset'] = $offset;

		return $this;
	}

	private function build() {

		$select = '*';

		if ( $this->query['select'] ) {
			$select = $this->query['select'];
		}

		$sql = "SELECT {$select} FROM {$this->query['from']}";

		if ( isset( $this->query['join'] ) ) {
			$sql .= ' ' . implode( ' ', $this->query['join'] );
		}

		if ( isset( $this->query['where'] ) ) {
			$sql .= " WHERE {$this->query['where']}";
		}

		if ( isset( $this->query['group_by'] ) ) {
			$sql .= " GROUP BY {$this->query['group_by']}";
		}

		if ( isset( $this->query['order_by'] ) ) {
			$sql .= " ORDER BY {$this->query['order_by']} {$this->query['order_direction']}";
		}

		if ( isset( $this->query['limit'] ) ) {
			$sql .= " LIMIT {$this->query['limit']}";
		}

		if ( isset( $this->query['offset'] ) ) {
			$sql .= " OFFSET {$this->query['offset']}";
		}

		$this->query_string = $sql;
	}

	public function rawSQL( $sql ) {
		$this->query_string = $sql;
	}

	public function getResults() {
		$this->build();
		return $this->db->get_results( $this->query_string );
	}

	public function getRow() {
		$this->build();
		return $this->db->get_row( $this->query_string );
	}

	public function getVar(): ?string {
		$this->build();

		return $this->db->get_var( $this->query_string );
	}

	public function showError() {
		$this->db->show_errors();
		$this->db->print_error();
	}

	public function clearCache() {
		$this->db->flush();
	}

	public function printSQL() {
		return $this->query_string;
	}
}
