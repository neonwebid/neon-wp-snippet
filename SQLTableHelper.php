<?php
namespace NeonWooAffiliate\Includes\Helpers;

class SQLTableHelper {

	private static array $column_attributes = [
		'type'           => '',
		'default'        => '',
		'attributes'     => '',
		'not_null'       => false,
		'comment'       => '',
		'primary_key'    => false,
		'auto_increment' => false,
	];

	private static array $type_increment_allowed = [
		'BIGINT',
		'INT',
		'SMALLINT',
		'TINYINT'
	];

	public static function create( $table_name, array $columns, $charset_collate = '' ): string {
		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (";


		foreach ( $columns as $column_name => $column_attributes ) {
			$sql_column = [];
			$column = array_merge( self::$column_attributes, $column_attributes );

			if ( empty( $column['type'] ) ) {
				continue;
			}

			$sql_column[] = $column_name;

			$sql_column[] = $column['type'];

			if ( $column['attributes'] ) {
				$sql_column[] = strtoupper( $column['attributes'] );
			}

			$sql_column[] = $column['not_null'] ? 'NOT NULL' : 'NULL';

			if ( $column['default'] ) {
				$sql_column[] = "DEFAULT {$column['default']}";
			}

			if ( $column['comment'] ) {
				$sql_column[] =  "COMMENT '{$column['comment']}'";
			}

			if (
				$column['auto_increment']
				&& in_array( strtoupper( $column['type'] ), self::$type_increment_allowed )
			) {
				$sql_column[] = 'AUTO_INCREMENT';
			}

			if ( $column['primary_key'] ) {
				$sql_column[] = 'PRIMARY KEY';
			}


			$sql .= sprintf("\n%s,", implode(' ', $sql_column));
		}

		$sql  = rtrim($sql, ', ');
		$sql .= "\n) {$charset_collate};";

		return $sql;

	}

	public static function dropTable($table_name): string {
		return "DROP TABLE {$table_name}";
	}

	public static function truncate($table_name): string {
		return "TRUNCATE TABLE {$table_name}";
	}
}

// example
//echo SQLTableHelper::create('bambang', [
//	'id' => [
//		'type' => 'BIGINT',
//		'attributes' => 'UNSIGNED',
//		'not_null' => true,
//		'auto_increment' => true,
//		'primary_key' => true,
//	],
//	'user_id' => [
//		'type' => 'BIGINT',
//		'attributes' => 'UNSIGNED',
//		'not_null' => true,
//	],
//	'order_id' => [
//		'type' => 'BIGINT',
//		'attributes' => 'UNSIGNED',
//		'default' => 0,
//	],
//	'product_id' => [
//		'type' => 'BIGINT',
//		'attributes' => 'UNSIGNED',
//		'default' => 0,
//	],
//	'amount' => [
//		'type' => 'DECIMAL(10, 2)',
//		'not_null' => true,
//	],
//	'description' => [
//		'type' => 'TEXT',
//	],
//	'transaction_type' => [
//		'type' => "ENUM('affiliate', 'withdrawal')",
//		'not_null' => true,
//		'comment' => 'affiliate, withdrawal',
//	],
//	'transaction_status' => [
//		'type' => 'TINYINT',
//		'attributes' => 'UNSIGNED',
//		'not_null' => true,
//		'comment' => '0:pending 1:under review 2:completed 3:decline',
//	],
//	'created_at' => [
//		'type' => 'DATETIME',
//		'not_null' => true,
//	],
//	'updated_at' => [
//		'type' => 'DATETIME',
//		'not_null' => true,
//	],
//], 'utf8mb4_unicode_520_ci');
