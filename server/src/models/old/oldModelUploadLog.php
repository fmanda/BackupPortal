<?php
	require_once '../src/models/BaseModel.php';

	class ModelUploadLog extends BaseModel{
		public static function getTableName(){
			return 'upload_log';
		}
		public static function getFields(){
			return array(
				"id", "user_id", "filename", "directory", "period", "department_id",
				"ml_subarea", "kpi_subarea", "level_id", "filepath"
			);
		}

		public static function saveToDB($obj){
			$db = new DB();
			$db = $db->connect();
			$db->beginTransaction();
			try {
				if (!isset($obj->id)){
					$obj->id = 0;
				}
				$sql = 'delete from upload_log where filepath = '. $db->quote($obj->filepath)
						. ' and id <> '. $db->quote($obj->id)
						. ' and level_id = '. $db->quote($obj->level_id);
				$db->prepare($sql)->execute();

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
	}
