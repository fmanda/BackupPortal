<?php
	require_once '../src/models/BaseModel.php';

	class ModelVisitor extends BaseModel{
		public static function getFields(){
			return array(
				"visitorname", "address", "idcardno", "lastvisit_id", "company", "phone"
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

	}
