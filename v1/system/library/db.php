<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

use DB\QueryObject;
use DB\QueryResult;

/**
* DB class
*/
class DB {
	private $adaptor;
	private static array $loaded = array();

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param	string	$adaptor
	 * @param	string	$hostname
	 * @param	string	$username
	 * @param	string	$password
	 * @param	string	$database
	 * @param	int		$port
	 *
	*/
	public function __construct($adaptor, $hostname, $username, $password, $database, $port = NULL, ?UOW &$uow = null, $persist = true) {
		$class = 'DB\\' . $adaptor;

		if (class_exists($class)) {
			$key = md5($adaptor . $hostname . $username . $password . $database . $port . ($uow ? 'uow' : 'no_uow') . ($persist ? 'yes' : 'no'));

			if(key_exists($key, self::$loaded)) {
				$this->adaptor = self::$loaded[$key];
			}
			else {
				$this->adaptor = new $class($hostname, $username, $password, $database, $port, $persist);
			
				if($uow)
					$uow->add($this->adaptor);

				$key = md5($adaptor . $hostname . $username . $password . $database . $port . ($uow ? 'uow' : 'no_uow') . ($persist ? 'yes' : 'no'));

				self::$loaded[$key] = $this->adaptor;
			}
		} else {
			throw new \Exception('Error: Could not load database adaptor ' . $adaptor . '!');
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * 
	 *
	 * @param	string	$sql
	 * 
	 * @return	object
	 */
	public function query($sql, $params = array()) {
		return $this->adaptor->query($sql, $params);
	}

	// --------------------------------------------------------------------

	/**
	 * @param array $where if array item is another array:
	 *      table: chat
	 *      where: [
	 *           'id' => [
	 *               'relation' => [
	 *                   'table' => 'chatter',
	 *                   'foreign_key' => 'chat_id',
	 *                   'field' => 'relation_id',
	 *                   'value' => '1'
	 *               ]
	 *           ]
	 *      ]
	 *
	 *      Look for a chat where id is chatter's chat_id where chatter's relation_id is 1
	 * @param QueryInclude[] $include
	 */
	public function get(string $table, array $where = array(), array $include = array()) : QueryResult {
		return $this->adaptor->get($table, $where, $include);
	}

	// --------------------------------------------------------------------

	/**
	 * @param string $table table name of SELECT statement up to WHERE statement
	 * @param array|string $where if array item is another array:
	 *      table: chat
	 *      where: [
	 *           'id' => [
	 *               'relation' => [
	 *                   'table' => 'chatter',
	 *                   'foreign_key' => 'chat_id',
	 *                   'field' => 'relation_id',
	 *                   'value' => '1'
	 *               ]
	 *           ]
	 *      ]
	 *
	 *      Look for a chat where id is chatter's chat_id where chatter's relation_id is 1
	 * 
	 * 		Make sure you sanitize the where statement before passing it in if it's a string
	 */
	public function fetch(string $table, array|string $where = array(), ?QueryObject $options = null) : QueryResult {
		return $this->adaptor->fetch($table, $where, $options);
	}

	// --------------------------------------------------------------------

	/**
	 * 
	 *
	 * @param  mixed $table
	 * @param  \model\common\QueryObject $query_object
	 * 
	 * @return object
	 */
	public function filter($table_or_query, $where = array(), $query_object = null) {
		return $this->adaptor->filter($table_or_query, $where, $query_object);
	}

	// --------------------------------------------------------------------

	/**
	 * calculates which rows should be added and removed
	 *
	 * @param  mixed $table
	 * @param  mixed $where
	 * @param  string $select column to select
	 * @param  array $new new values
	 * @param  \model\common\QueryObject $query_object
	 * 
	 * @return array
	 */
	// public function diff($table, $where = array(), $select, $new, $ret_col) {
	// 	return $this->adaptor->diff($table, $where, $select, $new, $ret_col);
	// }

	// --------------------------------------------------------------------

	/**
	 * 
	 *
	 * @param  mixed $table
	 * @param  \model\common\QueryObject $query_object
	 * 
	 * @return int
	 */
	public function count($table, $where = array(), $query_object = array()) : int {
		return $this->adaptor->count($table, $where, $query_object);
	}

	// --------------------------------------------------------------------

	/**
	 * 
	 *
	 * @param	string	$sql
	 * 
	 * @return	array
	 */
	public function command($sql, $params = array()) {
		return $this->adaptor->command($sql, $params);
	}

	// --------------------------------------------------------------------

	/**
	 * 
	 * 
	 * @return	int
	 */
	public function countAffected() {
		return $this->adaptor->countAffected();
	}

	// --------------------------------------------------------------------

	/**
	 * 
	 * 
	 * @return	int
	 */
	public function getLastId() {
		return $this->adaptor->getLastId();
	}

	// --------------------------------------------------------------------

	/**
	 * 
	 * 
	 * @return	bool
	 */	
	public function isConnected() {
		return $this->adaptor->isConnected();
	}

	// --------------------------------------------------------------------

	/**
	 * Generate an insert string
	 *
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @return	string
	 */
	public function insert($table, $data) {
		return $this->adaptor->insert($table, $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate an update string
	 *
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @param	mixed	the "where" statement
	 */
	public function update($table, $data, $where) {
		$this->adaptor->update($table, $data, $where);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param	mixed	the table(s) to delete from. String or array
	 * @param	mixed	the where clause
	 */
	public function delete($table, $where) {
		$this->adaptor->delete($table, $where);
	}

	// --------------------------------------------------------------------

	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function escape($str) {
		return $this->adaptor->escape($str);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param	mixed
	 * @return	mixed
	 */
	public function escape_identifiers($item) {
		return $this->adaptor->escape_identifiers($item);
	}
}