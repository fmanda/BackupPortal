<?php
	require_once '../src/models/BaseModel.php';

	class ModelTestHeader extends BaseModel{
		public static function getFields(){
			return array(
				"code"
			);
		}

		//override
		public static function retrieve($id){
			$obj = parent::retrieve($id);
			if (isset($obj)) $obj->items = ModelTestDetail::retrieveList('header_id = '. $obj->id);
			//additional property :
			// foreach($obj->items as $item){
			// 	if (isset($item->material_id)) $item->material =  ModelMaterial::retrieve($item->material_id);
			// }
			return $obj;
		}


		public static function deleteFromDB($id){
			try{
				$obj = parent::retrieve($id);
				$str = static::generateSQLDelete("id=". $id);
				$str = $str . ModelTestDetail::generateSQLDelete('header_id = '. $id);
				DB::executeSQL($str);
			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

		public static function saveToDB($obj){
			$db = new DB();
			$db = $db->connect();
			$db->beginTransaction();
			try {
				if (!static::isNewTransaction($obj)){
					$sql = ModelTestDetail::generateSQLDelete('header_id = '. $obj->id);
					$db->prepare($sql)->execute();
				}
				static::saveObjToDB($obj, $db);
				foreach($obj->items as $item){
					$item->header_id = $obj->id;
					ModelTestDetail::saveObjToDB($item, $db);
				}
				$db->commit();
				$db = null;

			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

	}

	class ModelTestDetail extends BaseModel{
		public static function getFields(){
			return array(
				"header_id", "detailcode"
			);
		}
	}
