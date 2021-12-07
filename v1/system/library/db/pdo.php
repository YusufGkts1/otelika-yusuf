<?php

namespace DB;

use \DB\exception\UniqueConstraintException;

use uow\UOWAware;

class PDO implements UOWAware
{
	protected $connection;
	protected $statement;

	protected $hostname;
	protected $username;
	protected $password;
	protected $database;
	protected $port;

	/**
	 * Protect identifiers flag
	 *
	 * @var	bool
	 */
	protected $_protect_identifiers		= TRUE;

	/**
	 * List of reserved identifiers
	 *
	 * Identifiers that must NOT be escaped.
	 *
	 * @var	string[]
	 */
	protected $_reserved_identifiers	= array('*');

	/**
	 * Identifier escape character
	 *
	 * @var	string
	 */
	protected $_escape_char = '`';

	public function __construct($hostname, $username, $password, $database, $port = '43060', $persist = true)
	{
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->port = $port;

		try {
			if ('gis' == $database) {
				$this->connection = @new \PDO("pgsql:host=" . $hostname . ";port=" . $port . ";dbname=" . $database, $username, $password, array(\PDO::ATTR_PERSISTENT => $persist));
				$this->_escape_char = '"';
			} else
				$this->connection = @new \PDO("mysql:host=" . $hostname . ";port=" . $port . ";dbname=" . $database, $username, $password, array(\PDO::ATTR_PERSISTENT => $persist));
			$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname . '!');
		}

		$this->connection->exec("SET NAMES 'utf8'");
		if ('gis' != $database) {
			$this->connection->exec("SET CHARACTER SET utf8");
			$this->connection->exec("SET CHARACTER_SET_CONNECTION=utf8");
			$this->connection->exec("SET SQL_MODE = ''");
		}
	}

	public function begin()
	{
		if (false == $this->connection->inTransaction())
			$this->connection->beginTransaction();
	}

	public function commit()
	{
		$this->connection->commit();
	}

	public function rollBack()
	{
		$this->connection->rollBack();
	}

	public function objection(): ?string
	{
		return null;
	}

	public function execute()
	{
		try {
			if ($this->statement && $this->statement->execute()) {
				$data = array();

				while ($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0])) ? $data[0] : array();
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();
			}
		} catch (\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode());
		}
	}

	public function total($sql, $params)
	{
		$this->statement = $this->connection->prepare($sql);
	}

	public function query($sql, $params = array(), $query_object = array())
	{
		// e20('sql: ' . $sql);
		// e00('params:');
		// e02(json_encode($params));

		$this->statement = $this->connection->prepare($sql);

		$result = false;

		try {
			if ($this->statement && $this->statement->execute($params)) {
				$data = array();

				while ($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0]) ? $data[0] : array());
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();
			}
		} catch (\PDOException $e) {
			// if($e->errorInfo[1] == 1062) // violated unique constraint
			// 	throw new UniqueConstraintException($e->errorInfo[2]);

			throw $e;

			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
		}

		if ($result) {
			return $result;
		} else {
			$result = new \stdClass();
			$result->row = array();
			$result->rows = array();
			$result->num_rows = 0;

			return $result;
		}
	}

	public function get(string $table, array $where = array(), array $include = array()): QueryResult
	{
		$sql = 'SELECT * FROM ' . $this->escape_identifiers($table);

		$params = array();

		$where_stmt = '';

		foreach ($where as $k => $v) {
			if ($where_stmt)
				$where_stmt .= ' AND ';
			else
				$where_stmt .= ' WHERE ';

			/**
			 *
			 * table: chat
			 * where: [
			 *      'id' => [
			 *          'relation' => [
			 *              'table' => 'chatter',
			 *              'foreign_key' => 'chat_id',
			 *              'field' => 'relation_id',
			 *              'value' => '1'
			 *          ]
			 *      ]
			 * ]
			 *
			 * Look for a chat where id is chatter's chat_id where chatter's relation_id is 1
			 */
			if (is_array($v)) {
				$relation = $v['relation'];

				foreach ($relation as $i => $r) {
					if ($i > 0)
						$where_stmt .= ' AND ';

					$where_stmt .= ' ' . $this->escape_identifiers($r['key']) . ' = (SELECT ' . $this->escape_identifiers($r['foreign_key']) . ' FROM ' . $this->escape_identifiers($r['table']) . ' WHERE ' . $this->escape_identifiers($r['field']) . ' = :' . $this->escape($r['field']) . ')';
					$params[':' . $r['field']] = $r['value'];
				}
			} else {
				$where_stmt .= ' ' . $this->escape_identifiers($k) . ' = :' . $this->escape($k);
				$params[':' . $k] = $v;
			}
		}

		$sql .= $where_stmt;

		$result = $this->query($sql, $params);

		# if any include is specified
		if ($include && $result->row) {
			foreach ($include as $i) {
				$this->_put_relations($result->row, $i);
			}
		}

		return new QueryResult($result->row, null);
	}

	/**
	 * @param string $table table name of SELECT statement up to WHERE statement
	 * @param array|string $where
	 */
	public function fetch(string $table, array|string $where = array(), ?QueryObject $options = null)
	{
		if (str_starts_with($table, 'SELECT') && strpos($table, 'FROM') !== false)
			$sql = $table . ' ';
		else
			$sql = "SELECT * FROM " . $this->escape_identifiers($table);

		$params = array();

		$where_stmt = '';

		if (is_array($where)) {
			foreach ($where as $k => $v) {
				if ($where_stmt)
					$where_stmt .= ' AND ';
				else
					$where_stmt .= ' WHERE ';

				/**
				 *
				 * table: chat
				 * where: [
				 *      'id' => [
				 *          'relation' => [
				 *              [
				 *                  'table' => 'chatter',
				 *                  'foreign_key' => 'chat_id',
				 *                  'field' => 'relation_id',
				 *                  'value' => '1'
				 *              ],
				 *              [
				 *                  'table' => 'chatter',
				 *                  'foreign_key' => 'chat_id',
				 *                  'field' => 'relation',
				 *                  'value' => 'applicant'
				 *              ]
				 *          ]
				 *      ]
				 * ]
				 *
				 * Look for a chat where id is chatter's chat_id where chatter's relation_id is 1
				 */
				if (is_array($v)) {
					$relation = $v['relation'];

					foreach ($relation as $i => $r) {
						if ($i > 0)
							$where_stmt .= ' AND ';

						$where_stmt .= ' ' . $this->escape_identifiers($r['key']) . ' IN (SELECT ' . $this->escape_identifiers($r['foreign_key']) . ' FROM ' . $this->escape_identifiers($r['table']) . ' WHERE ' . $this->escape_identifiers($r['field']) . ' = :' . $this->escape($r['field']) . ')';
						$params[':' . $r['field']] = $r['value'];
					}
				} else if (null == $v) {
					$where_stmt .= ' ' . $this->escape_identifiers($k) . ' IS NULL';
				} else {
					$where_stmt .= ' ' . $this->escape_identifiers($k) . ' = :' . $this->escape($k);
					$params[':' . $k] = $v;
				}
			}
		} else
			$sql .= ' WHERE ' . $where;

		$sql .= $where_stmt;

		if (null != $options) {
			// # if any include is specified
			// if($options->includes())
			// 	$sql .= $this->_compile_incl($options->includes());

			# if any filter is specified
			if ($options->filters()) {
				if (null == $where_stmt)
					$sql .= ' WHERE ';
				else
					$sql .= ' AND (';

				$filters = $options->filters();

				foreach ($filters as $index => $filter) {
					if ($index > 0) {
						if ($options->andFilters())
							$sql .= ' AND ';
						else
							$sql .= ' OR ';
					}

					if ($filter->operator() == 'LIKE') {
						$sql .= '(:' . $this->escape($filter->field()) . '_nosym ' . $filter->operator() . " CONCAT('%', " . $this->escape($filter->field()) . ", '%') OR " . $this->escape($filter->field()) . ' ' . $filter->operator() . ' :' . $this->escape($filter->field()) . ')';
						/**
						 * Ornek:
						 * SELECT * FROM personnel WHERE (:firstname LIKE CONCAT('%', firstname, '%') OR firstname LIKE :firstname)
						 * SELECT * FROM personnel WHERE ("est" LIKE %firstname% OR firstname LIKE %est%)
						 */

						$params[':' . $filter->field() . "_nosym"] = $filter->value(false);
						$params[':' . $filter->field()] = $filter->value();
					} else {
						$sql .= $this->escape_identifiers($filter->field()) . ' ' . $filter->operator() . ' :' . $this->escape($filter->field());

						$params[':' . $filter->field()] = $filter->value();
					}
				}

				if (null != $where_stmt)
					$sql .= ')';
			}

			if ($options->orderBy()) {
				$sql .= ' ORDER BY ';
				$order_by_arr = $options->orderBy();

				foreach ($order_by_arr as $index => $order_by) {
					if ($index > 0)
						$sql .= ', ';

					$sql .= $this->escape_identifiers(key($order_by)) . ' ' . $this->escape($order_by[key($order_by)]);
				}
			}

			if ($options->paginationIsEnabled()) {
				$count_sql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);

				$total = $this->query($count_sql, $params)->row['total'];

				$sql .= " LIMIT " . $options->limit();
				$sql .= " OFFSET " . $options->offset();
			}
		}

		// echo PHP_EOL;
		// echo PHP_EOL . 'sql: ' . $sql;
		// echo PHP_EOL . 'params: ' . json_encode($params);
		// echo PHP_EOL . PHP_EOL;

		$result = $this->query($sql, $params);

		// echo PHP_EOL . PHP_EOL . 'result: ' . json_encode($result) . PHP_EOL . PHP_EOL;

		if (null != $options && $options->paginationIsEnabled()) {
			if ($total < 1)
				$current_page = 0;
			else
				$current_page = ((int)($options->offset() / $options->limit())) + 1;

			$total_pages = ceil($total / $options->limit());
			$total_rows_without_pagination = $total;

			$meta = new QueryMeta(
				$current_page,
				$total_pages,
				$total_rows_without_pagination
			);
		} else
			$meta = null;

		# if any include is specified
		if (null != $options && $options->includes()) {
			foreach ($result->rows as &$row) {
				foreach ($options->includes() as $include) {
					$this->_put_relations($row, $include);
				}
			}
		}

		return new QueryResult($result->rows, $meta);
	}

	private function _put_relations(&$entity, QueryInclude $include)
	{
		$result = $this->query("
			SELECT 
				* 
			FROM 
				" . $this->escape_identifiers($include->tableRight()) . "
			WHERE
				" . $this->escape_identifiers($include->fieldRight()) . " = :value
		", array(
			':value' => $entity[$include->fieldLeft()]
		));

		if ($result->num_rows == 0)
			$entity['_include'][$include->tableRight()] = null;
		else {
			$rel_obj_arr = $result->rows;

			if ($include->hasSubInclude()) {
				foreach ($rel_obj_arr as &$rel_obj)
					$this->_put_relations($rel_obj, $include->include());
			}

			$entity['_include'][$include->tableRight()] = $rel_obj_arr;
		}
	}

	/**
	 * 
	 *
	 * @param  mixed $table_or_query
	 * @param  \model\common\QueryObject $query_object
	 * 
	 * @return object
	 */
	public function filter($table_or_query, $where = array(), $query_object = null)
	{
		$is_table = 1 == count(explode(' ', $table_or_query));

		if ($is_table)
			$sql = "SELECT * FROM " . $this->escape_identifiers($table_or_query);
		else
			$sql = $table_or_query;

		$params = array();

		$sql .= $this->_compile_wh($where);

		if ($query_object->filters()) {
			if (null == $where)
				$sql .= ' WHERE ';
			else
				$sql .= ' AND (';

			$filters = $query_object->filters();

			$operator = $query_object->andFilters() ? 'AND' : 'OR';

			foreach ($filters as $index => $filter) {
				if ($index > 0)
					$sql .= ' ' . $operator . ' ';

				if ($filter->operator() == 'LIKE') {
					$sql .= '(:' . $filter->field() . '_nosym ' . $filter->operator() . " CONCAT('%', " . $filter->field() . ", '%') OR " . $filter->field() . ' ' . $filter->operator() . ' :' . $filter->field() . ')';
					/**
					 * Ornek:
					 * SELECT * FROM personnel WHERE (:firstname LIKE CONCAT('%', firstname, '%') OR firstname LIKE :firstname)
					 * SELECT * FROM personnel WHERE ("est" LIKE %firstname% OR firstname LIKE %est%)
					 */

					$params[':' . $filter->field() . "_nosym"] = $filter->value(false);
					$params[':' . $filter->field()] = $filter->value();
				} else {
					$sql .= $filter->field() . ' ' . $filter->operator() . ' :' . $filter->field();

					$params[':' . $filter->field()] = $filter->value();
				}
			}

			if (null != $where)
				$sql .= ')';
		}

		if ($query_object->orderBy()) {
			$sql .= ' ORDER BY ';
			$order_by_arr = $query_object->orderBy();

			foreach ($order_by_arr as $index => $order_by) {
				if ($index > 0)
					$sql .= ', ';

				$sql .= $this->escape_identifiers(key($order_by)) . ' ' . $order_by[key($order_by)];
			}
		}

		if ($query_object->limit())
			$sql .= " LIMIT " . $query_object->limit();

		if ($query_object->offset())
			$sql .= " OFFSET " . $query_object->offset();

		// echo PHP_EOL . "SQL: " . $sql . PHP_EOL;
		// echo PHP_EOL . 'PARAMS: ' . json_encode($params) . PHP_EOL;

		return $this->query($sql, $params);
	}

	public function diff($table, $where, $select, $new, $ret_col)
	{
		$sql = "SELECT * FROM " . $this->escape_identifiers($table) . $this->_compile_wh($where);

		$current = $this->query($sql)->rows;

		$remove = array();
		$add = array();
		$update = array();

		foreach ($current as $c) {
			if (false == in_array($c[$select], $new))
				$remove[] = $c[$ret_col];
			else
				$update[] = $c[$ret_col];
		}

		$current_select_arr = array_map(function ($c) use ($select) {
			return $c[$select];
		}, $current,);

		foreach ($new as $n) {
			if (false == in_array($n, $current_select_arr))
				$add[] = $n;
		}

		return array(
			'add' => $add,
			'update' => $update,
			'remove' => $remove
		);
	}

	/**
	 * 
	 *
	 * @param  mixed $table_or_query
	 * @param  \model\common\QueryObject $query_object
	 * 
	 * @return int
	 */
	public function count($table_or_query, $where = array(), $query_object = array()): int
	{
		$is_table = 1 == count(explode(' ', $table_or_query));

		if ($is_table)
			$sql = "SELECT * FROM " . $this->escape_identifiers($table_or_query);
		else
			$sql = $table_or_query;

		$params = array();

		$sql .= $this->_compile_wh($where);

		if ($query_object->filters()) {
			if (null == $where)
				$sql .= ' WHERE ';
			else
				$sql .= ' AND (';

			$filters = $query_object->filters();

			$operator = $query_object->andFilters() ? 'AND' : 'OR';

			foreach ($filters as $index => $filter) {
				if ($index > 0)
					$sql .= ' ' . $operator . ' ';

				if ($filter->operator() == 'LIKE') {
					$sql .= '(:' . $filter->field() . '_nosym ' . $filter->operator() . " CONCAT('%', " . $filter->field() . ", '%') OR " . $filter->field() . ' ' . $filter->operator() . ' :' . $filter->field() . ')';
					/**
					 * Ornek:
					 * SELECT * FROM personnel WHERE (:firstname LIKE CONCAT('%', firstname, '%') OR firstname LIKE :firstname)
					 * SELECT * FROM personnel WHERE ("est" LIKE %firstname% OR firstname LIKE %est%)
					 */

					$params[':' . $filter->field() . "_nosym"] = $filter->value(false);
					$params[':' . $filter->field()] = $filter->value();
				} else {
					$sql .= $filter->field() . ' ' . $filter->operator() . ' :' . $filter->field();

					$params[':' . $filter->field()] = $filter->value();
				}
			}

			if (null != $where)
				$sql .= ')';
		}

		// echo PHP_EOL . "SQL: " . $sql . PHP_EOL;
		// echo PHP_EOL . 'PARAMS: ' . json_encode($params) . PHP_EOL;

		return $this->query($sql, $params)->num_rows;
	}

	public function command($sql, $params = array())
	{
		$this->statement = $this->connection->prepare($sql);

		try {
			$this->statement->execute($params);
		} catch (\PDOException $e) {
			if ($e->errorInfo[1] == 1062) // violated unique constraint
				throw new UniqueConstraintException($e->errorInfo[2]);

			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
		}

		return true;
	}

	/**
	 * Generate an insert string
	 *
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @return	string
	 */
	public function insert($table, $data)
	{
		$fields = $values = array();

		foreach ($data as $key => $val) {
			$fields[] = $this->escape_identifiers($key);
			$values[] = $this->escape($val);
		}

		return $this->_insert($this->protect_identifiers($table, NULL, FALSE), $fields, $values);
	}

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 */
	protected function _insert($table, $keys, $values)
	{
		$selectors = array();
		$params = array();

		foreach ($keys as $key)
			$selectors[] = str_replace($this->_escape_char, '', substr_replace($key, ':', 1, 0));

		foreach ($selectors as $index => $selector)
			$params[$selector] = $values[$index];

		$query = "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $selectors) . ")";

		// echo PHP_EOL . "QUERY: " . $query . PHP_EOL;

		return $this->command($query, $params);

		// return $this->query('INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')');
	}

	/**
	 * Generate an update string
	 *
	 * @param	string	the table upon which the query will be performed
	 * @param	array	an associative array data of key/values
	 * @param	mixed	the "where" statement
	 */
	public function update($table, $data, $where)
	{
		if (empty($where)) {
			return FALSE;
		}

		$fields = array();
		foreach ($data as $key => $val) {
			$fields[$this->protect_identifiers($key)] = $this->escape($val);
		}

		return $this->_update($this->protect_identifiers($table, NULL, FALSE), $fields, $where);
	}

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	mixed	the where clause
	 */
	protected function _update($table, $values, $where)
	{

		foreach ($values as $key => $val) {
			$selector = str_replace('`', '', substr_replace($key, ':', 1, 0));
			$valstr[] = $key . ' = ' . $selector;
			$valarr[$selector] = $val;
		}

		$query = "UPDATE " . $table . " SET " . implode(', ', $valstr) . $this->_compile_wh($where) . PHP_EOL;

		return $this->command($query, $valarr);

		// return $this->query('UPDATE '.$table.' SET '.implode(', ', $valstr) . $this->_compile_wh($where));
	}

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param	mixed	the table(s) to delete from. String or array
	 * @param	mixed	the where clause
	 */
	public function delete($table, $where)
	{
		if (empty($where)) {
			return FALSE;
		}

		if (is_array($table)) {
			foreach ($table as $single_table) {
				$this->delete($single_table, $where);
			}

			return;
		} else {
			$table = $this->protect_identifiers($table, NULL, FALSE);
		}

		return $this->_delete($table, $where);
	}

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	mixed	the where clause
	 */
	protected function _delete($table, $where)
	{
		return $this->command('DELETE FROM ' . $table . $this->_compile_wh($where));
	}

	public function prepare($sql)
	{
		$this->statement = $this->connection->prepare($sql);
	}

	public function bindParam($parameter, $variable, $data_type = \PDO::PARAM_STR, $length = 0)
	{
		if ($length) {
			$this->statement->bindParam($parameter, $variable, $data_type, $length);
		} else {
			$this->statement->bindParam($parameter, $variable, $data_type);
		}
	}

	public function escape($str)
	{
		if (is_array($str)) {
			$str = array_map(array(&$this, 'escape'), $str);
			return $str;
		} elseif (is_bool($str)) {
			return ($str === FALSE) ? 0 : 1;
		}

		return $str;
	}

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @param	mixed
	 * @return	mixed
	 */
	public function escape_identifiers($item)
	{
		if ($this->_escape_char === '' or empty($item) or in_array($item, $this->_reserved_identifiers)) {
			return $item;
		} elseif (is_array($item)) {
			foreach ($item as $key => $value) {
				$item[$key] = $this->escape_identifiers($value);
			}

			return $item;
		} elseif (ctype_digit($item) or $item[0] === "'" or ($this->_escape_char !== '"' && $item[0] === '"') or strpos($item, '(') !== FALSE) {
			return $item;
		}

		static $preg_ec = array();

		if (empty($preg_ec)) {
			if (is_array($this->_escape_char)) {
				$preg_ec = array(
					preg_quote($this->_escape_char[0], '/'),
					preg_quote($this->_escape_char[1], '/'),
					$this->_escape_char[0],
					$this->_escape_char[1]
				);
			} else {
				$preg_ec[0] = $preg_ec[1] = preg_quote($this->_escape_char, '/');
				$preg_ec[2] = $preg_ec[3] = $this->_escape_char;
			}
		}

		foreach ($this->_reserved_identifiers as $id) {
			if (strpos($item, '.' . $id) !== FALSE) {
				return preg_replace('/' . $preg_ec[0] . '?([^' . $preg_ec[1] . '\.]+)' . $preg_ec[1] . '?\./i', $preg_ec[2] . '$1' . $preg_ec[3] . '.', $item);
			}
		}

		return preg_replace('/' . $preg_ec[0] . '?([^' . $preg_ec[1] . '\.]+)' . $preg_ec[1] . '?(\.)?/i', $preg_ec[2] . '$1' . $preg_ec[3] . '$2', $item);
	}

	/**
	 * Protect Identifiers
	 *
	 * It takes a column or table name (optionally with an alias) and inserts
	 * the table prefix onto it. Some logic is necessary in order to deal with
	 * column names that include the path. Consider a query like this:
	 *
	 * SELECT hostname.database.table.column AS c FROM hostname.database.table
	 *
	 * Or a query with aliasing:
	 *
	 * SELECT m.member_id, m.member_name FROM members AS m
	 *
	 * Since the column name can include up to four segments (host, DB, table, column)
	 * or also have an alias prefix, we need to do a bit of work to figure this out and
	 * insert the table prefix (if it exists) in the proper position, and escape only
	 * the correct identifiers.
	 *
	 * @param	string
	 * @param	bool
	 * @param	mixed
	 * @param	bool
	 * @return	string
	 */
	public function protect_identifiers($item, $protect_identifiers = NULL, $field_exists = TRUE)
	{
		if (!is_bool($protect_identifiers)) {
			$protect_identifiers = $this->_protect_identifiers;
		}

		if (is_array($item)) {
			$escaped_array = array();
			foreach ($item as $k => $v) {
				$escaped_array[$this->protect_identifiers($k)] = $this->protect_identifiers($v, $protect_identifiers, $field_exists);
			}

			return $escaped_array;
		}

		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix. There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		//
		// Added exception for single quotes as well, we don't want to alter
		// literal strings. -- Narf
		if (strcspn($item, "()'") !== strlen($item)) {
			return $item;
		}

		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace('/\s+/', ' ', trim($item));

		// If the item has an alias declaration we remove it and set it aside.
		// Note: strripos() is used in order to support spaces in table names
		if ($offset = strripos($item, ' AS ')) {
			$alias = ($protect_identifiers)
				? substr($item, $offset, 4) . $this->escape_identifiers(substr($item, $offset + 4))
				: substr($item, $offset);
			$item = substr($item, 0, $offset);
		} elseif ($offset = strrpos($item, ' ')) {
			$alias = ($protect_identifiers)
				? ' ' . $this->escape_identifiers(substr($item, $offset + 1))
				: substr($item, $offset);
			$item = substr($item, 0, $offset);
		} else {
			$alias = '';
		}

		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos($item, '.') !== FALSE) {
			$parts = explode('.', $item);

			// Does the first segment of the exploded item match
			// one of the aliases previously identified? If so,
			// we have nothing more to do other than escape the item
			//
			// NOTE: The ! empty() condition prevents this method
			//       from breaking when QB isn't enabled.
			if (!empty($this->qb_aliased_tables) && in_array($parts[0], $this->qb_aliased_tables)) {
				if ($protect_identifiers === TRUE) {
					foreach ($parts as $key => $val) {
						if (!in_array($val, $this->_reserved_identifiers)) {
							$parts[$key] = $this->escape_identifiers($val);
						}
					}

					$item = implode('.', $parts);
				}

				return $item . $alias;
			}

			if ($protect_identifiers === TRUE) {
				$item = $this->escape_identifiers($item);
			}

			return $item . $alias;
		}

		if ($protect_identifiers === TRUE && !in_array($item, $this->_reserved_identifiers)) {
			$item = $this->escape_identifiers($item);
		}

		return $item . $alias;
	}

	// --------------------------------------------------------------------

	/**
	 * Compile WHERE statements
	 *
	 * Escapes identifiers in WHERE statements
	 *
	 *
	 * @param	array	$where
	 * @return	string	SQL statement
	 */
	protected function _compile_wh($where)
	{
		if (is_array($where) && count($where) > 0) {
			$statements = array();
			foreach ($where as $key => $val) {
				if (null === $val) {
					$statements[] = $this->escape_identifiers($this->_clear_operator($key)) . ' IS NULL';
				} else if (null == $val) {
					$statements[] = $this->escape_identifiers($this->_clear_operator($key)) . ' = \'\'';
				} else if ($this->_has_operator($key)) {
					$statements[] = $this->escape_identifiers($this->_clear_operator($key)) . ' ' . $this->_get_operator($key) . ' ' . $this->escape($val);
				} else {
					$statements[] = $this->escape_identifiers($this->_clear_operator($key)) . ' = ' . $this->escape($val);
				}
			}

			$query = ' WHERE ' .  implode(' AND ', $statements);

			return $query;
		} elseif (is_string($where)) {
			return ' WHERE ' . '(' . $where . ')';
		}

		return '';
	}

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @param	string
	 * @return	bool
	 */
	protected function _has_operator($str)
	{
		return (bool) preg_match('/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', trim($str));
	}

	/**
	 * Tests whether the string has an SQL operator
	 *
	 * @param	string
	 * @return	bool
	 */
	protected function _clear_operator($str)
	{
		return preg_replace('/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', '', $str);
	}

	/**
	 * Returns the SQL string operator
	 *
	 * @param	string
	 * @return	string
	 */
	protected function _get_operator($str)
	{
		static $_operators;

		if (empty($_operators)) {
			$_operators = array(
				'\s*(?:<|>|!)?=\s*',             // =, <=, >=, !=
				'\s*<>?\s*',                     // <, <>
				'\s*>\s*',                       // >
				'\s+IS NULL',                    // IS NULL
				'\s+IS NOT NULL',                // IS NOT NULL
				'\s+EXISTS\s*\(.*\)',            // EXISTS(sql)
				'\s+NOT EXISTS\s*\(.*\)',        // NOT EXISTS(sql)
				'\s+BETWEEN\s+',                 // BETWEEN value AND value
				'\s+IN\s*\(.*\)',                // IN(list)
				'\s+NOT IN\s*\(.*\)',            // NOT IN (list)
			);
		}

		return preg_match('/' . implode('|', $_operators) . '/i', $str, $match) ? $match[0] : FALSE;
	}

	public function countAffected()
	{
		if ($this->statement) {
			return $this->statement->rowCount();
		} else {
			return 0;
		}
	}

	public function getLastId()
	{
		return $this->connection->lastInsertId();
	}

	public function isConnected()
	{
		if ($this->connection) {
			return true;
		} else {
			return false;
		}
	}

	public function __destruct()
	{
		$this->connection = null;
	}
}
