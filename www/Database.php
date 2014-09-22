<?php
/**
* Database - a singleton pattern database connection class
* to estblish exactly one database connection object to 
* utilize system resources efficiently
*
* @author Peter Anselmo
* @copyright 2008 Studio66
*/
class Database {
	
	protected $server = DB_HOST;
	protected $username = DB_USER;
	protected $password = DB_PASS;
	protected $database = DB_NAME;

	protected $con;
	protected $query_count;
	static $instance;

	/**
	* Database class constructor - marked private to ensure it is only
	* 	called by itsetlf
	*/
	protected function __construct() {
		
		try {
			$this->con = mysqli_connect($this->server, $this->username, $this->password, $this->database);
			$this->query_count = 0;

			if( !$this->con ) {
				die("Could not connect to database!");
			}
		} catch ( LoggedException $e ) {
			die ($e->getMessage());
		}
	}
	
	public static function getInstance() {
		if( !(self::$instance instanceof self)){
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	* function only exists to close a small PHP loophole where
	* the _clone method may instaciate a class
	*/
	public function _clone() {}

	public function prepare($sql) {
		
		if( DEBUGGING) {
			echo $sql . '<br />';
			$result = mysqli_prepare( $this->con, $sql) or die( mysqli_error( $this->con) );
		} else {
			$result = @mysqli_prepare($this->con, $sql);
		}
		return $result;
	}

	public function execute($params, $statement_name = "") {
		
		if( DEBUGGING) {
			var_dump($params) . '<br />';
			$result = mysqli_execute( $this->con, $statement_name, $params) or die( mysqli_last_error( $this->con) );
		} else {
			$result = @mysqli_execute($this->con, $statement_name, $params);
		}
		++$this->query_count;
		return $result;
	}
	
	public function query($sql) {
		
		if( DEBUGGING) {
			echo $sql . '<br />';
			$result = mysqli_query( $this->con, $sql) or die( mysqli_last_error( $this->con) );
		} else {
			$result = @mysqli_query($this->con, $sql);
		}
		++$this->query_count;
		return $result;
	}
	
	public function GetQueryCount(){
		return $this->query_count;
	}

	
}
