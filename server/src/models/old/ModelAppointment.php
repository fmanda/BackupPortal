<?php
	require_once '../src/models/BaseModel.php';
	require_once '../src/models/ModelVisitor.php';

	class ModelAppointment extends BaseModel{
		public static function getFields(){
			return array(
				"visitor_id", "calldate", "planningdate", "dept_id", "person_to_meet", "reason"
			);
		}


		public static function retrieve($id){
			$obj = parent::retrieve($id);
			if (isset($obj->visitor_id)) $obj->visitor = ModelVisitor::retrieve($obj->visitor_id);
			if (isset($obj->dept_id)) $obj->department = ModelDepartment::retrieve($obj->dept_id);

			return $obj;
		}

		public static function retrieveList($filter=''){
			$sql = 'select a.*, b.visitorname, b.company, b.address, b.idcardno, b.phone , c."name" as deptname from appointment a
							left join visitor b on a.visitor_id = b.id
							left join jsection c on a.dept_id = c.section_id
							where 1 = 1 ';
			if ($filter<>''){
				$sql = $sql .' and '. $filter;
			}

			$obj = DB::openQuery($sql);
			return $obj ;
		}

		private static function isNewVisitor($obj){
			$isNew = true;
			if (isset($obj->visitor_id)) {
				if($obj->visitor_id > 0) {
					$isNew = false;
				}
			}
			return $isNew;
		}

		public static function retrieveOnGoing($filter=''){
			$sql = 'select a.*, b.visitorname, b.company, b.address, b.idcardno, b.phone , c."name" as deptname from appointment a
							left join visitor b on a.visitor_id = b.id
							left join jsection c on a.dept_id = c.section_id
							left join visit d on d.appointment_id = a.id
							where d.id is null ';
			if ($filter<>''){
				$sql = $sql .' and '. $filter;
			}

			$obj = DB::openQuery($sql);
			return $obj ;
		}


		public static function saveToDB($obj){
			$db = new DB();
			$db = $db->connect();
			$db->beginTransaction();
			$isnew = static::isNewTransaction($obj);
			try {
				if ( !isset($obj->calldate)) {

					date_default_timezone_set('Asia/Jakarta');
					$date = new \DateTime();
					$obj->calldate = $date->format('Y-m-d H:i:s');
				}

				if (ModelAppointment::isNewVisitor($obj)){
					if (!isset($obj->visitor)) throw new Exception('Visitor Object empty');
					ModelVisitor::saveToDB($obj->visitor);
					$obj->visitor_id = $obj->visitor->id;
				}
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
				// $str = $str . ModelTestDetail::generateSQLDelete('header_id = '. $id);
				DB::executeSQL($str);
			} catch (Exception $e) {
				$db->rollback();
				throw $e;
			}
		}

	}
