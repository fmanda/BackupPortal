<?php
	require_once '../src/models/BaseModel.php';

	class ModelUsers extends BaseModel{
		public static function getTableName(){
			return 'users';
		}
		public static function getFields(){
			return array(
				"username", "password",
				"fullname", "department_id",
			);
		}

		public static function saveToDB($obj){
			$db = new DB();
			$db = $db->connect();
			$db->beginTransaction();
			try {
				static::saveObjToDB($obj, $db);
				$db->commit();
				$db = null;
			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

		public static function deleteFromDB($id){
			try{
				$obj = parent::retrieve($id);
				$str = static::generateSQLDelete("id=". $id);
				DB::executeSQL($str);
			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

		public static function retrieveLogin($username, $password){
			$obj = DB::openQuery("select id, username, fullname, department_id from ".static::getTableName()
				." where username = '" . $username . "'"
				." and password = '" . $password . "'"
			);
			if (isset($obj[0])) return $obj[0];
		}
	}
