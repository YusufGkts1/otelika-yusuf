<?php
namespace DB;

class PDO_POSTGRE extends PDO {

	/**
	 * Identifier escape character
	 *
	 * @var	string
	 */
	protected $_escape_char = '"';

	// private $_pg_connection = null;

	// /**
	//  * Escape the SQL Identifiers
	//  *
	//  * This function escapes column and table names
	//  *
	//  * @param	mixed
	//  * @return	mixed
	//  */
	// public function escape_identifiers($item) {
	// 	if(null == $this->_pg_connection)
	// 		$this->_pg_connection = pg_connect('host=' . $this->hostname . ' port=' . $this->port . ' dbname=' . $this->database . ' user=' . $this->username . ' password=' . $this->password);

	// 	return pg_escape_identifier($this->_pg_connection, $item);
	// }
}

?>