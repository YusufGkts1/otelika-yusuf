<?php 

namespace DB;

use MongoDB\Client;

final class Mongo {

	private Client $client;
	private string $database;

	public function __construct($hostname, $username, $password, $database, $port = '27017') {
		$this->database = $database;
		$this->client = new Client('mongodb://' . $username . ":" . $password . '@' . $hostname . ':' . $port);
	}

	public function insert($collection, $data) {
		$db = $this->database;

		$collection = $this->client->$db->$collection;
		
		$insert_one_result = $collection->insertOne($this->escape($data));
		
		return $insert_one_result->getInsertedId(); 
	}

	public function query() {
	}

	public function escape($data) {
		return mongo_sanitize($data);
	}

}

?>