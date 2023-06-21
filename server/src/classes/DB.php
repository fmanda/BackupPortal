<?php

	class DB{
		public function connect($dbase){

			if (isset($dbase)){
				$dbhost = $dbase->server;
				$dbname = "master";
				$dbuser = $dbase->user;
				$port = 1433;
				$dbpassword = $dbase->password;
			}else{
				$config = parse_ini_file("../src/config.ini");
				$dbhost = $config["server"];
				$dbname = $config['database'];
				$dbuser = $config['user'];
				$port = $config['port'];
				$dbpassword = $config['password'];
			}
			$conStr = sprintf("sqlsrv: Server=%s; Database=%s", $dbhost,$dbname);
			$conn = new \PDO($conStr, $dbuser, $dbpassword);
			$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			return $conn;
		}

		public function showConfig(){
			print_r(parse_ini_file("../src/config.ini"));
		}

		public static function openQuery($sql, $dbase){
			try{
				$db = new DB();
				$db = $db->connect($dbase);
				$stmt = $db->query($sql);
				$rows = $stmt->fetchAll(PDO::FETCH_OBJ);
				$db = null;
				return $rows;
			}catch(PDOException $e){
				echo '{"error":{"text": '. $e->getMessage() . '; sql : '.$sql.'}}' ;
				throw $e;
			}
		}

		public static function executeSQL($sql, $dbase){
			$db = new DB();
			$db = $db->connect($dbase);
			$db->beginTransaction();
			try {
				$int = $db->prepare($sql)->execute();
				$db->commit();
			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

		public static function GUID(){

	        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = substr($charid, 0, 8).$hyphen
	            .substr($charid, 8, 4).$hyphen
	            .substr($charid,12, 4).$hyphen
	            .substr($charid,16, 4).$hyphen
	            .substr($charid,20,12);
	        return $uuid;
		}

		public static function paginateSQL($sql, $limit = 10, $page = 1) {
		    if ( $limit == 0 ) {
		        return $sql;
		    } else {
		        $sql = $sql . " LIMIT " . ( ( $page - 1 ) * $limit ) . ", $limit";
				return $sql;
		    }
		}

		public static function getRecordCount($sql){
			$sql = "select count(*) as total from (" . $sql . ") as t";
			$data = DB::openQuery($sql);
			return $data[0]->total;
		}

		public static function paginateQuery($sql, $limit = 10, $page = 1, $dbase){

			$obj = new stdClass();
			$obj->totalrecord = static::getRecordCount($sql);

			$sql = static::paginateSQL($sql, $limit, $page, $dbase);
			$obj->data = DB::openQuery($sql);

			return $obj;
		}


	}

	
